<?php
require_once __DIR__ . '/../utils/pm_db.php';
require_once __DIR__ . '/../triggers/trigger_batchApprovalComplete.php';

try {
    $pdo = getDbConnection();

    // Fetch one pending vendor payment batch
    $stmt = $pdo->query("SELECT * FROM vendor_payments WHERE approval_status = 'PENDING' LIMIT 1");
    $batch = $stmt->fetch();

    if (!$batch) {
        echo "⚠️ No pending vendor batch found.\n";
        exit;
    }

    // Fetch vendor items linked to this batch
    $stmtItems = $pdo->prepare("SELECT * FROM vendor_batch_items WHERE batch_id = ?");
    $stmtItems->execute([$batch['id']]);
    $vendors = $stmtItems->fetchAll();

    // Build the case data as ProcessMaker would provide
    $caseData = [
        'BATCH_CASE_UID' => $batch['batch_case_uid'],
        'INITIATOR_UID'  => $batch['initiator_user_uid'],
        'vendors'        => $vendors
    ];

    // Run the trigger
    trigger_batchApprovalComplete($caseData);

    echo "✅ Batch approval simulation complete for batch ID {$batch['id']}.\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
