<?php
require_once __DIR__ . '/../utils/pm_db.php';
require_once __DIR__ . '/../triggers/trigger_createChildPaymentCase.php';
require_once __DIR__ . '/../utils/pm_logger.php';

echo "=== Simulating Payment Execution Phase ===\n";

try {
    $pdo = getDbConnection();

    // Step 1️⃣ — Fetch the most recent approved batch
    $stmt = $pdo->query("
        SELECT id, batch_case_uid, approval_status
        FROM vendor_payments
        WHERE approval_status = 'APPROVED'
        ORDER BY updated_at DESC
        LIMIT 1
    ");
    $batch = $stmt->fetch();

    if (!$batch) {
        echo "⚠️ No approved batch found. Please run batch approval simulation first.\n";
        exit(0);
    }

    $batchCaseUid = $batch['batch_case_uid'];
    $executorUid  = 'USR-FIN-' . rand(100,999);

    echo "✅ Found approved batch: {$batch['batch_name']} ({$batchCaseUid})\n";
    echo "👤 Executor UID: {$executorUid}\n\n";

    // Step 2️⃣ — Execute the trigger
    $result = trigger_createChildPaymentCase($batchCaseUid, $executorUid);

    // Step 3️⃣ — Output summary
    if (empty($result)) {
        echo "⚠️ No vendors ready for execution in this batch.\n";
    } else {
        echo "✅ Child payment cases created successfully:\n";
        foreach ($result as $r) {
            echo "- Vendor: {$r['vendor_name']} ({$r['vendor_id']}) → ₦{$r['amount']} [Exec ID: {$r['payment_execution_id']}]\n";
        }
    }

} catch (Exception $e) {
    echo "❌ Simulation failed: " . $e->getMessage() . "\n";
}
