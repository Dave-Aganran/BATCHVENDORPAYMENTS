<?php
require_once realpath(__DIR__ . '/../utils/pm_db.php');

try {
    $pdo = getDbConnection();
    echo "✅ Connected to database successfully.\n";

    $sqlFile = __DIR__ . '/../sql/create_tables.sql';
    if (!file_exists($sqlFile)) {
        die("❌ SQL file not found: $sqlFile\n");
    }

    $sql = file_get_contents($sqlFile);
    $statements = array_filter(array_map('trim', explode(';', $sql)));

    foreach ($statements as $stmt) {
        if (!empty($stmt)) {
            $pdo->exec($stmt);
        }
    }

    echo "✅ Tables created successfully.\n";

} catch (Throwable $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}
