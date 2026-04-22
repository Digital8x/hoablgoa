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

$name    = clean($_POST['name'] ?? '');
$phone   = clean($_POST['phone'] ?? '');
$email   = clean($_POST['email'] ?? '');
$project = clean($_POST['project'] ?? '');
$message = clean($_POST['message'] ?? '');

// Validation
$errors = [];
if (strlen($name) < 2)  $errors[] = 'Name is required.';
if (!preg_match('/^[\+]?[\d\s\-\(\)]{7,15}$/', $phone))  $errors[] = 'Valid phone number required.';
if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL))  $errors[] = 'Invalid email address.';
if (empty($project))  $errors[] = 'Please select a project.';

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

$ip        = $_SERVER['REMOTE_ADDR'] ?? '';
$userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500);

// ===== SAVE TO DATABASE =====
$saved = false;
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
    $stmt = $pdo->prepare(
        "INSERT INTO leads (name, phone, email, project, message, ip_address, user_agent, created_at)
         VALUES (:name, :phone, :email, :project, :message, :ip, :ua, NOW())"
    );
    $stmt->execute([
        ':name' => $name, ':phone' => $phone, ':email' => $email,
        ':project' => $project, ':message' => $message, ':ip' => $ip, ':ua' => $userAgent
    ]);
    $saved = true;
} catch (Exception $e) {
    // DB failed – still try to send email
    error_log('HOABL Lead DB Error: ' . $e->getMessage());
}

// ===== ALSO SAVE TO CSV (FALLBACK) =====
$csvFile = __DIR__ . '/leads.csv';
$csvExists = file_exists($csvFile);
$fp = fopen($csvFile, 'a');
if ($fp) {
    if (!$csvExists) fputcsv($fp, ['ID','Name','Phone','Email','Project','Message','IP','DateTime']);
    fputcsv($fp, ['', $name, $phone, $email, $project, $message, $ip, date('Y-m-d H:i:s')]);
    fclose($fp);
}

// ===== SEND EMAIL NOTIFICATION =====
$subject = "New Lead: $name – $project";
$emailBody = "
<html><body style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;background:#f5f5f5;'>
<div style='background:#050a14;padding:30px;text-align:center;'>
  <h2 style='color:#c9a84c;margin:0;'>🏝️ New HOABL Lead</h2>
  <p style='color:#8898b5;margin:8px 0 0;'>Someone is interested in your Goa project!</p>
</div>
<div style='background:#ffffff;padding:30px;'>
  <table style='width:100%;border-collapse:collapse;'>
    <tr><td style='padding:12px;border-bottom:1px solid #e2e8f0;font-weight:bold;color:#4a5568;width:140px;'>Name</td><td style='padding:12px;border-bottom:1px solid #e2e8f0;color:#2d3748;'>$name</td></tr>
    <tr><td style='padding:12px;border-bottom:1px solid #e2e8f0;font-weight:bold;color:#4a5568;'>Phone</td><td style='padding:12px;border-bottom:1px solid #e2e8f0;color:#2d3748;'><a href='tel:$phone' style='color:#c9a84c;'>$phone</a></td></tr>
    <tr><td style='padding:12px;border-bottom:1px solid #e2e8f0;font-weight:bold;color:#4a5568;'>Email</td><td style='padding:12px;border-bottom:1px solid #e2e8f0;color:#2d3748;'>$email</td></tr>
    <tr><td style='padding:12px;border-bottom:1px solid #e2e8f0;font-weight:bold;color:#4a5568;'>Project</td><td style='padding:12px;border-bottom:1px solid #e2e8f0;color:#2d3748;'><strong style='color:#c9a84c;'>$project</strong></td></tr>
    <tr><td style='padding:12px;font-weight:bold;color:#4a5568;'>Message</td><td style='padding:12px;color:#2d3748;'>" . (empty($message) ? '<em>No message</em>' : $message) . "</td></tr>
  </table>
  <div style='margin-top:24px;padding:16px;background:#f8f9fa;border-radius:8px;'>
    <small style='color:#718096;'>Lead received on " . date('d M Y, h:i A') . " | IP: $ip</small>
  </div>
  <div style='margin-top:20px;text-align:center;'>
    <a href='tel:$phone' style='background:#c9a84c;color:#050a14;padding:12px 28px;border-radius:50px;font-weight:bold;text-decoration:none;display:inline-block;'>📞 Call $name</a>
  </div>
</div>
<div style='background:#050a14;padding:16px;text-align:center;'>
  <p style='color:#4a5568;font-size:12px;margin:0;'>HOABL Goa – Authorized Channel Partner Website</p>
</div>
</body></html>
";

$headers  = "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
$headers .= "From: " . LEAD_EMAIL_NAME . " <" . LEAD_EMAIL_FROM . ">\r\n";
$headers .= "Reply-To: " . (empty($email) ? LEAD_EMAIL_FROM : $email) . "\r\n";

$emailSent = mail(LEAD_EMAIL_TO, $subject, $emailBody, $headers);

if (!$emailSent) {
    error_log("HOABL: Email failed to send to " . LEAD_EMAIL_TO);
}

echo json_encode([
    'success' => true,
    'message' => 'Thank you! Your enquiry has been submitted. We will contact you within 24 hours.',
    'saved'   => $saved,
    'emailed' => $emailSent
]);
