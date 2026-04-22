<?php
session_start();
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['hoabl_logged_in'])) {
    header('Location: login.php');
    exit;
}

$pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("UPDATE settings SET 
        notify_email = ?, use_smtp = ?, smtp_host = ?, smtp_port = ?, 
        smtp_user = ?, smtp_pass = ?, smtp_secure = ? WHERE id = 1");
    $stmt->execute([
        $_POST['notify_email'], isset($_POST['use_smtp']) ? 1 : 0, 
        $_POST['smtp_host'], (int)$_POST['smtp_port'],
        $_POST['smtp_user'], $_POST['smtp_pass'], $_POST['smtp_secure']
    ]);
    $msg = 'Settings updated successfully!';
}

$settings = $pdo->query("SELECT * FROM settings WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Settings | Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #050a14; color: #e8eaf0; margin: 0; padding: 2rem; }
        .container { max-width: 800px; margin: 0 auto; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); border-radius: 20px; padding: 3rem; }
        h1 { color: #c9a84c; margin-bottom: 2rem; display: flex; align-items: center; gap: 10px; }
        .form-group { margin-bottom: 1.5rem; }
        label { display: block; font-size: 0.85rem; color: #8898b5; margin-bottom: 0.5rem; }
        input, select { width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); padding: 0.8rem; border-radius: 10px; color: #fff; outline: none; }
        .btn-save { background: linear-gradient(135deg, #c9a84c, #e8943a); color: #050a14; border: none; padding: 1rem 2rem; border-radius: 50px; font-weight: 800; cursor: pointer; margin-top: 1rem; }
        .msg { background: rgba(0,200,132,0.1); color: #00c884; padding: 1rem; border-radius: 10px; margin-bottom: 2rem; border: 1px solid rgba(0,200,132,0.2); }
        .back-link { display: inline-block; margin-bottom: 1rem; color: #8898b5; text-decoration: none; font-size: 0.9rem; }
        .back-link:hover { color: #fff; }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-link">← Back to Dashboard</a>
        <h1>⚙️ Email & SMTP Settings</h1>
        
        <?php if ($msg): ?><div class="msg"><?= $msg ?></div><?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Receiver Email (Where you get lead notifications)</label>
                <input type="email" name="notify_email" value="<?= htmlspecialchars($settings['notify_email']) ?>" required>
            </div>

            <hr style="border: 0; border-top: 1px solid rgba(255,255,255,0.1); margin: 2rem 0;">

            <div class="form-group">
                <label><input type="checkbox" name="use_smtp" <?= $settings['use_smtp'] ? 'checked' : '' ?>> Enable SMTP for sending emails</label>
            </div>

            <div class="form-group">
                <label>SMTP Host</label>
                <input type="text" name="smtp_host" value="<?= htmlspecialchars($settings['smtp_host']) ?>">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="form-group">
                    <label>SMTP Port</label>
                    <input type="number" name="smtp_port" value="<?= htmlspecialchars($settings['smtp_port']) ?>">
                </div>
                <div class="form-group">
                    <label>Encryption</label>
                    <select name="smtp_secure">
                        <option value="tls" <?= $settings['smtp_secure'] == 'tls' ? 'selected' : '' ?>>TLS (Recommended)</option>
                        <option value="ssl" <?= $settings['smtp_secure'] == 'ssl' ? 'selected' : '' ?>>SSL</option>
                        <option value="none" <?= $settings['smtp_secure'] == 'none' ? 'selected' : '' ?>>None</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>SMTP Username</label>
                <input type="text" name="smtp_user" value="<?= htmlspecialchars($settings['smtp_user']) ?>">
            </div>

            <div class="form-group">
                <label>SMTP Password</label>
                <input type="password" name="smtp_pass" value="<?= htmlspecialchars($settings['smtp_pass']) ?>">
            </div>

            <button type="submit" class="btn-save">SAVE SETTINGS</button>
        </form>
    </div>
</body>
</html>
