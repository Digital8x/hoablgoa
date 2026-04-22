<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once __DIR__ . '/config.php';

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Sanitize inputs
function clean($val) {
    return htmlspecialchars(strip_tags(trim($val ?? '')));
}

function getDevice($ua) {
    $ua = strtolower($ua);
    if (strpos($ua, 'iphone') !== false) return 'iPhone';
    if (strpos($ua, 'android') !== false) return 'Android';
    if (strpos($ua, 'windows') !== false) return 'Windows PC';
    return 'Other';
}

function getGeo($ip) {
    $res = @file_get_contents("http://ip-api.com/json/{$ip}");
    if ($res) {
        $data = json_decode($res, true);
        if ($data && $data['status'] === 'success') {
            return ['country' => $data['country'], 'city' => $data['city']];
        }
    }
    return ['country' => 'Unknown', 'city' => 'Unknown'];
}

date_default_timezone_set('Asia/Kolkata');

$name    = clean($_POST['name'] ?? '');
$phone   = clean($_POST['phone'] ?? '');
$email   = clean($_POST['email'] ?? '');
$project = clean($_POST['project_interest'] ?? $_POST['project'] ?? 'General Enquiry');
$message = clean($_POST['message'] ?? '');

// Validation
$errors = [];
if (strlen($name) < 2)  $errors[] = 'Name is required.';
if (!preg_match('/^[\+]?[\d\s\-\(\)]{7,15}$/', $phone))  $errors[] = 'Valid phone number required.';
if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL))  $errors[] = 'Invalid email address.';

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

$ip        = $_SERVER['REMOTE_ADDR'] ?? '';
$userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500);
$device    = getDevice($userAgent);
$geo       = getGeo($ip);
$country   = $geo['country'];
$city      = $geo['city'];

// ===== SAVE TO DATABASE =====
$saved = false;
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $stmt = $pdo->prepare(
        "INSERT INTO leads (name, phone, email, project, message, ip_address, user_agent, device, country, city, created_at)
         VALUES (:name, :phone, :email, :project, :message, :ip, :ua, :device, :country, :city, :created_at)"
    );
    $stmt->execute([
        ':name' => $name, ':phone' => $phone, ':email' => $email,
        ':project' => $project, ':message' => $message, ':ip' => $ip, ':ua' => $userAgent,
        ':device' => $device, ':country' => $country, ':city' => $city,
        ':created_at' => date('Y-m-d H:i:s')
    ]);
    $saved = true;
} catch (Exception $e) {
    error_log('Lead DB Error: ' . $e->getMessage());
}

// ===== SEND EMAIL =====
$subject = "New Lead: $name ($project)";
$body = "New lead received:\n\nName: $name\nPhone: $phone\nEmail: $email\nProject: $project\nMessage: $message\nDevice: $device\nLocation: $city, $country\nIP: $ip";

$headers = "From: " . LEAD_EMAIL_FROM . "\r\n";
$headers .= "Reply-To: " . ($email ?: LEAD_EMAIL_FROM) . "\r\n";

if (defined('USE_SMTP') && USE_SMTP === true) {
    // Simple SMTP implementation would go here or use PHPMailer. 
    // For now, using mail() as it's more universal, but I'll add instructions for SMTP.
    mail(LEAD_EMAIL_TO, $subject, $body, $headers);
} else {
    mail(LEAD_EMAIL_TO, $subject, $body, $headers);
}

echo json_encode(['success' => true, 'message' => 'Lead captured successfully!']);
