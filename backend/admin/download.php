<?php
require_once __DIR__ . '/../../backend/config.php';

// Auth
if (!isset($_SERVER['PHP_AUTH_USER']) ||
    $_SERVER['PHP_AUTH_USER'] !== ADMIN_USER ||
    $_SERVER['PHP_AUTH_PW']   !== ADMIN_PASS) {
    header('WWW-Authenticate: Basic realm="HOABL Admin"');
    header('HTTP/1.0 401 Unauthorized');
    exit;
}

$filename = 'HOABL_Leads_' . date('Y-m-d_H-i-s') . '.csv';
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

// UTF-8 BOM for Excel compatibility
echo "\xEF\xBB\xBF";

$out = fopen('php://output', 'w');
fputcsv($out, ['#', 'Date', 'Time', 'Name', 'Phone', 'Email', 'Project Interest', 'Message', 'IP Address']);

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $leads = $pdo->query("SELECT * FROM leads ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($leads as $i => $row) {
        fputcsv($out, [
            $i + 1,
            date('d/m/Y', strtotime($row['created_at'])),
            date('h:i A', strtotime($row['created_at'])),
            $row['name'], $row['phone'], $row['email'],
            $row['project'], $row['message'], $row['ip_address']
        ]);
    }
} catch (Exception $e) {
    // Fallback: read from CSV file
    $csvFile = __DIR__ . '/../../backend/leads.csv';
    if (file_exists($csvFile)) {
        $fp = fopen($csvFile, 'r');
        fgetcsv($fp); // skip header
        $i = 1;
        while (($row = fgetcsv($fp)) !== false) {
            fputcsv($out, array_merge([$i++], array_slice($row, 1)));
        }
        fclose($fp);
    } else {
        fputcsv($out, ['No data available', '', '', '', '', '', '', '', '']);
    }
}

fclose($out);
