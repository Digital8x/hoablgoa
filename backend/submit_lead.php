<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
    // Tablets
    if (strpos($ua, 'ipad') !== false) return 'iPad (Apple iPad)';
    if (strpos($ua, 'android') !== false && strpos($ua, 'mobile') === false) return 'Android Tablet';
    
    // Mobile Phones
    if (strpos($ua, 'iphone') !== false) return 'iPhone (Apple iPhone)';
    if (strpos($ua, 'android') !== false) return 'Android Phone';
    
    // Desktop
    if (strpos($ua, 'windows') !== false) return 'Windows PC';
    if (strpos($ua, 'macintosh') !== false || strpos($ua, 'mac os x') !== false) return 'Mac (Apple MacBook / iMac)';
    if (strpos($ua, 'linux') !== false) return 'Linux PC';
    
    return 'Other Device';
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

// ===== DATABASE CONNECTION =====
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    // Save Lead
    $stmt = $pdo->prepare("INSERT INTO leads (name, phone, email, project, message, ip_address, user_agent, device, country, city, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$name, $phone, $email, $project, $message, $ip, $userAgent, $device, $country, $city, date('Y-m-d H:i:s')]);

    // Get Settings
    $settings = $pdo->query("SELECT * FROM settings WHERE id = 1")->fetch(PDO::FETCH_ASSOC);

    // ===== SEND EMAIL =====
    $mail = new PHPMailer(true);
    $toEmail = LEAD_EMAIL_TO;
    $ccEmail = defined('LEAD_EMAIL_CC') ? LEAD_EMAIL_CC : '';

    if ($settings) {
        $toEmail = !empty($settings['notify_email']) ? $settings['notify_email'] : $toEmail;
        $ccEmail = !empty($settings['notify_email_cc']) ? $settings['notify_email_cc'] : $ccEmail;
    }

    try {
        // Server settings
        $useSmtp = (USE_SMTP || (isset($settings['use_smtp']) && $settings['use_smtp'] == 1));
        
        if ($useSmtp) {
            $mail->isSMTP();
            // Use database settings if available, otherwise fallback to config constants
            $mail->Host       = (!empty($settings['smtp_host'])) ? $settings['smtp_host'] : SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = (!empty($settings['smtp_user'])) ? $settings['smtp_user'] : SMTP_USER;
            $mail->Password   = (!empty($settings['smtp_pass'])) ? $settings['smtp_pass'] : SMTP_PASS;
            
            $secure = (!empty($settings['smtp_secure'])) ? $settings['smtp_secure'] : 'tls';
            $mail->SMTPSecure = ($secure == 'ssl') ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = (!empty($settings['smtp_port'])) ? (int)$settings['smtp_port'] : SMTP_PORT;
        }

        // Recipients
        $mail->setFrom(LEAD_EMAIL_FROM, LEAD_EMAIL_NAME);
        $mail->addAddress($toEmail);
        
        if (!empty($ccEmail)) {
            $mail->addCC($ccEmail);
        }

        if (!empty($email)) {
            $mail->addReplyTo($email, $name);
        }

        // Content
        $mail->isHTML(false);
        $mail->Subject = "New Lead: $name ($project)";
        $mail->Body    = "New lead received:\n\nName: $name\nPhone: $phone\nEmail: $email\nProject: $project\nMessage: $message\nDevice: $device\nLocation: $city, $country\nIP: $ip\nTime: " . date('Y-m-d H:i:s');

        $mail->send();
    } catch (Exception $e) {
        // Fallback to mail() if PHPMailer fails
        error_log("PHPMailer Error: {$mail->ErrorInfo}");
        
        $headers = "From: " . LEAD_EMAIL_NAME . " <" . LEAD_EMAIL_FROM . ">\r\n";
        if (!empty($ccEmail)) {
            $headers .= "Cc: " . $ccEmail . "\r\n";
        }
        if (!empty($email)) {
            $headers .= "Reply-To: $name <$email>\r\n";
        }
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        mail($toEmail, "New Lead: $name ($project)", "New lead received:\n\nName: $name\nPhone: $phone\nEmail: $email\nProject: $project\nMessage: $message\nDevice: $device\nLocation: $city, $country\nIP: $ip\nTime: " . date('Y-m-d H:i:s'), $headers);
    }

    echo json_encode(['success' => true, 'message' => 'Lead captured successfully!']);
} catch (Exception $e) {
    error_log('Lead Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Processing error.']);
}
