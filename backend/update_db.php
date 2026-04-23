<?php
require_once __DIR__ . '/config.php';
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdo->exec("ALTER TABLE settings ADD COLUMN notify_email_cc VARCHAR(150) DEFAULT '' AFTER notify_email");
    echo "<h1>Success!</h1><p>The CC email column was successfully added to your database.</p><p><a href='admin/settings.php'>Go to Settings</a></p>";
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "<h1>Already Done</h1><p>The column already exists.</p><p><a href='admin/settings.php'>Go to Settings</a></p>";
    } else {
        echo "<h1>Error</h1><p>" . $e->getMessage() . "</p>";
    }
}
