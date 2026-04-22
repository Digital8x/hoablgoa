<?php
require_once __DIR__ . '/backend/config.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Create table if not exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS leads (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255),
        phone VARCHAR(50),
        email VARCHAR(255),
        project VARCHAR(255),
        message TEXT,
        ip_address VARCHAR(50),
        user_agent TEXT,
        device VARCHAR(50),
        country VARCHAR(100),
        city VARCHAR(100),
        created_at DATETIME
    )");

    // Add missing columns if they don't exist
    $columns = [
        'email' => "ALTER TABLE leads ADD COLUMN email VARCHAR(255) AFTER phone",
        'device' => "ALTER TABLE leads ADD COLUMN device VARCHAR(50) AFTER user_agent",
        'country' => "ALTER TABLE leads ADD COLUMN country VARCHAR(100) AFTER device",
        'city' => "ALTER TABLE leads ADD COLUMN city VARCHAR(100) AFTER country"
    ];

    foreach ($columns as $col => $sql) {
        $check = $pdo->query("SHOW COLUMNS FROM leads LIKE '$col'");
        if ($check->rowCount() == 0) {
            $pdo->exec($sql);
            echo "Added column: $col<br>";
        }
    }

    echo "<h3>Database Repair Complete!</h3><p>Your lead capture system is now ready.</p>";
    unlink(__FILE__); // Delete self after execution
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
