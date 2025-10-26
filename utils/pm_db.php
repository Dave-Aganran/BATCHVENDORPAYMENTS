<?php
// utils/db.php
// Simple PDO wrapper for PostgreSQL

function getDbConnection() {
    $host = '127.0.0.1';
    $port = '5432';
    $db   = 'vendor_batch_payments';
    $user = 'postgres';
    $pass = 'postgres';
    $dsn  = "pgsql:host=$host;port=$port;dbname=$db;";

    try {
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        // write to log and rethrow
        file_put_contents(__DIR__ . '/../logs/db_error.log', date('c') . ' | DB Conn Error: ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
        throw $e;
    }
}
