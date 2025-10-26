<?php
require_once __DIR__ . '/../utils/pm_db.php';
require_once __DIR__ . '/../utils/pm_logger.php';

/**
 * Trigger to simulate creation of child payment cases after Finance initiates execution
 *
 * @param string $batchCaseUid - UID of the approved vendor batch
 * @param string $executorUid - UID of the user (Finance/Ops) starting execution
 * @return array - Summary of created payment executions
 */
function trigger_createChildPaymentCase(string $batchCaseUid, string $executorUid): array
{
    $pdo = getDbConnection();
    $createdExecutions = [];

    try {
        // Fetch batch info
        $stmtBatch = $pdo->prepare("SELECT id FROM vendor_payments WHERE batch_case_uid = ?");
        $stmtBatch->execute([$batchCaseUid]);
        $batch = $stmtBatch->fetch();

        if (!$batch) {
            logMessage("⚠️ No batch found for UID: $batchCaseUid");
            return [];
        }

        $batchId = $batch['id'];

        // Fetch vendor items ready for execution
        $stmtVendors = $pdo->prepare("
            SELECT * FROM vendor_batch_items
            WHERE batch_id = ? AND payment_status = 'READY_FOR_EXECUTION'
        ");
        $stmtVendors->execute([$batchId]);
        $vendors = $stmtVendors->fetchAll();

        if (empty($vendors)) {
            logMessage("ℹ️ No vendor items ready for execution in batch $batchId.");
            return [];
        }

        $pdo->beginTransaction();

        $insertExec = $pdo->prepare("
            INSERT INTO payment_executions
                (batch_id, vendor_batch_item_id, ops_maker_uid, payment_status, created_at, updated_at, last_updatd_by)
            VALUES
                (:batch_id, :vendor_batch_item_id, :ops_maker_uid, 'IN_PROGRESS', NOW(), NOW(), :last_updatd_by)
            RETURNING id
        ");

        $updateVendor = $pdo->prepare("
            UPDATE vendor_batch_items
            SET payment_status = 'IN_PROGRESS',
                updated_at = NOW(),
                last_updatd_by = :executorUid
            WHERE id = :vendor_batch_item_id
        ");

        foreach ($vendors as $vendor) {
            $insertExec->execute([
                ':batch_id' => $batchId,
                ':vendor_batch_item_id' => $vendor['id'],
                ':ops_maker_uid' => $executorUid,
                ':last_updatd_by' => $executorUid
            ]);

            $execId = $insertExec->fetchColumn();

            $updateVendor->execute([
                ':executorUid' => $executorUid,
                ':vendor_batch_item_id' => $vendor['id']
            ]);

            $createdExecutions[] = [
                'payment_execution_id' => $execId,
                'vendor_id' => $vendor['vendor_id'],
                'vendor_name' => $vendor['vendor_name'],
                'amount' => $vendor['amount'],
            ];

            logMessage("🚀 Child payment case created for vendor {$vendor['vendor_name']} [Exec ID: {$execId}]");
        }

        $pdo->commit();

        logMessage("✅ trigger_createChildPaymentCase completed for batch $batchId by user $executorUid.");
        return $createdExecutions;

    } catch (Exception $e) {
        $pdo->rollBack();
        logMessage("❌ Error in trigger_createChildPaymentCase: " . $e->getMessage());
        return [];
    }
}
