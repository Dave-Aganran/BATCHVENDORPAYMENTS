<?php
require_once __DIR__ . '/../utils/pm_db.php';
require_once __DIR__ . '/../utils/pm_logger.php';

/**
 * Trigger executed after blanket vendor batch approval is completed.
 * It:
 *  - Marks the batch as approved.
 *  - Creates payment_executions entries for each vendor item.
 *  - Updates vendor_batch_items payment_status accordingly.
 *
 * @param array $caseData - Data passed from ProcessMaker or test harness.
 */
function trigger_batchApprovalComplete(array $caseData)
{
    $pdo = getDbConnection();

    $batchCaseUid = $caseData['BATCH_CASE_UID'] ?? null;
    $initiatorUid = $caseData['INITIATOR_UID'] ?? null;
    $vendors      = $caseData['vendors'] ?? [];

    if (!$batchCaseUid || !$initiatorUid) {
        logMessage("❌ Missing BATCH_CASE_UID or INITIATOR_UID in case data.");
        return;
    }

    try {
        $pdo->beginTransaction();

        // Fetch the target batch
        $stmt = $pdo->prepare("SELECT id FROM vendor_payments WHERE batch_case_uid = ?");
        $stmt->execute([$batchCaseUid]);
        $batch = $stmt->fetch();

        if (!$batch) {
            logMessage("⚠️ No batch found for case UID: $batchCaseUid");
            $pdo->rollBack();
            return;
        }

        $batchId = $batch['id'];

        // Update batch approval status
        $update = $pdo->prepare("
            UPDATE vendor_payments
            SET approval_status = 'APPROVED',
                approved_at = NOW(),
                updated_at = NOW()
            WHERE id = ?
        ");
        $update->execute([$batchId]);

        logMessage("✅ Batch {$batchId} marked APPROVED by {$initiatorUid}.");

        // Loop through vendors in this batch and create payment executions
        $insertPayment = $pdo->prepare("
            INSERT INTO payment_executions
                (batch_id, vendor_batch_item_id, ops_maker_uid, payment_status, created_at, updated_at, last_updatd_by)
            VALUES
                (:batch_id, :vendor_batch_item_id, NULL, 'PENDING', NOW(), NOW(), :last_updatd_by)
        ");

        $updateVendor = $pdo->prepare("
            UPDATE vendor_batch_items
            SET payment_status = 'READY_FOR_EXECUTION',
                updated_at = NOW(),
                last_updatd_by = :last_updatd_by
            WHERE id = :vendor_batch_item_id
        ");

        foreach ($vendors as $vendor) {
            $vendorItemId = $vendor['id'] ?? null;

            if (!$vendorItemId) {
                logMessage("⚠️ Skipping vendor without valid ID in batch {$batchId}");
                continue;
            }

            // Create payment execution record
            $insertPayment->execute([
                ':batch_id'            => $batchId,
                ':vendor_batch_item_id'=> $vendorItemId,
                ':last_updatd_by'      => $initiatorUid
            ]);

            // Update vendor batch item status
            $updateVendor->execute([
                ':vendor_batch_item_id'=> $vendorItemId,
                ':last_updatd_by'      => $initiatorUid
            ]);

            logMessage("💰 Vendor item {$vendorItemId} queued for payment execution.");
        }

        $pdo->commit();
        logMessage("🎯 Trigger batchApprovalComplete completed successfully for batch {$batchId}.");

    } catch (Exception $e) {
        $pdo->rollBack();
        logMessage("❌ Error in trigger_batchApprovalComplete: " . $e->getMessage());
    }
}
?>