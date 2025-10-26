<?php
require_once __DIR__ . '/../triggers/trigger_updatePaymentStatus.php';

echo "=== Simulating Payment Status Update ===\n";

$results = trigger_updatePaymentStatus();

if (empty($results)) {
    echo "⚠️ No 'IN_PROGRESS' payment executions found.\n";
} else {
    echo "✅ Payment results:\n";
    foreach ($results as $r) {
        echo "- {$r['vendor']}: ₦{$r['amount']} → {$r['status']}\n";
    }
}
