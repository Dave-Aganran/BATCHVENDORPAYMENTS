<?php
require_once __DIR__ . '/../utils/pm_db.php';
require_once __DIR__ . '/../utils/pm_logger.php';

function trigger_updatePaymentStatus()
{
    $pdo = getDbConnection();
    pm_log("Starting payment status update trigger...");

    // Step 1️⃣ — Fetch all in-progress payments
    $stmt = $pdo->query("
        SELECT id, batch_case_uid, vendor_id, vendor_name, amount
        FROM payment_executions
        WHERE status = 'IN_PROGRESS'
    ");
    $executions = $stmt->fetchAll();

    if (empty($executions)) {
        pm_log("No payments currently in progress.");
        return [];
    }

    $results = [];
    foreach ($executions as $exec) {
        // Randomly determine payment outcome (90% success rate)
        $isSuccess = (rand(1, 100) <= 90);
        $newStatus = $isSuccess ? 'SUCCESS' : 'FAILED';

        // Step 2️⃣ — Update individual payment record
        $updateStmt = $pdo->prepare("
            UPDATE payment_executions
            SET status = :status, updated_at = NOW()
            WHERE id = :id
        ");
        $updateStmt->execute([
            ':status' => $newStatus,
            ':id' => $exec['id']
        ]);

        $results[] = [
            'vendor' => $exec['vendor_name'],
            'amount' => $exec['amount'],
            'status' => $newStatus,
        ];

        pm_log("Vendor {$exec['vendor_name']} payment marked as {$newStatus}");
    }

    // Step 3️⃣ — Recompute batch summary status
    $batchStmt = $pdo->query("
        SELECT batch_case_uid,
               COUNT(*) AS total,
               SUM(CASE WHEN status='SUCCESS' THEN 1 ELSE 0 END) AS success_count,
               SUM(CASE WHEN status='FAILED' THEN 1 ELSE 0 END) AS failed_count
        FROM payment_executions
        GROUP BY batch_case_uid
    ");
    $batchStats = $batchStmt->fetchAll();

    foreach ($batchStats as $stat) {
        $overallStatus = ($stat['failed_count'] > 0)
            ? 'PARTIALLY_SUCCESSFUL'
            : 'COMPLETED';

        $pdo->prepare("
            UPDATE vendor_payments
            SET payment_status = :status, updated_at = NOW()
            WHERE batch_case_uid = :uid
        ")->execute([
            ':status' => $overallStatus,
            ':uid' => $stat['batch_case_uid']
        ]);

        pm_log("Batch {$stat['batch_case_uid']} updated to {$overallStatus}");
    }

    pm_log("Payment status update completed successfully.");
    return $results;
}
