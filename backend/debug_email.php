<?php
require_once __DIR__ . '/config.php';
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $settings = $pdo->query("SELECT * FROM settings WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
    if (!$settings) {
        echo "ERROR: Settings row (id=1) not found in database! Creating it now...";
        $pdo->exec("INSERT INTO settings (id, notify_email) VALUES (1, '" . LEAD_EMAIL_TO . "')");
        echo " Created.";
    } else {
        echo "Settings found: " . print_r($settings, true);
    }
} catch (Exception $e) {
    echo "DB Error: " . $e->getMessage();
}
