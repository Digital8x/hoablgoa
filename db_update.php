<?php
require_once 'backend/config.php';
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->exec("ALTER TABLE leads ADD COLUMN device VARCHAR(50) AFTER user_agent");
    $pdo->exec("ALTER TABLE leads ADD COLUMN country VARCHAR(100) AFTER device");
    $pdo->exec("ALTER TABLE leads ADD COLUMN city VARCHAR(100) AFTER country");
    echo "Columns added successfully!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
unlink(__FILE__);
