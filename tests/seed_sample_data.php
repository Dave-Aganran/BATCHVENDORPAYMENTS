<?php
require_once realpath(__DIR__ . '/../utils/pm_db.php');

try {
    $pdo = getDbConnection();
    echo "✅ Connected to database.\n";

    $sqlFile = __DIR__ . '/../sql/seed_sample_data.sql';
    if (!file_exists($sqlFile)) {
        die("❌ SQL seed file not found: $sqlFile\n");
    }

    $sql = file_get_contents($sqlFile);
    $pdo->exec($sql);

    echo "✅ Seed data inserted successfully.\n";

} catch (Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
