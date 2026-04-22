<?php
require_once __DIR__ . '/backend/config.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        id INT PRIMARY KEY,
        notify_email VARCHAR(255),
        use_smtp TINYINT(1) DEFAULT 0,
        smtp_host VARCHAR(255),
        smtp_port INT,
        smtp_user VARCHAR(255),
        smtp_pass VARCHAR(255),
        smtp_secure VARCHAR(10) DEFAULT 'tls'
    )");

    // Insert default row if not exists
    $check = $pdo->query("SELECT id FROM settings WHERE id = 1");
    if ($check->rowCount() == 0) {
        $pdo->exec("INSERT INTO settings (id, notify_email, use_smtp, smtp_host, smtp_port) 
                   VALUES (1, 'admin@digital8x.com', 0, 'smtp.gmail.com', 587)");
    }

    echo "<h3>Settings Table Ready!</h3><p>You can now use the Settings page in the admin panel.</p>";
    unlink(__FILE__);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
