<?php
session_start();
require_once __DIR__ . '/../config.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';

    if ($user === ADMIN_USER && $pass === ADMIN_PASS) {
        $_SESSION['hoabl_logged_in'] = true;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | HOABL Goa</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body { margin: 0; font-family: 'Inter', sans-serif; background: #030812; color: #e8eaf0; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .login-card { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); padding: 3rem; border-radius: 24px; width: 100%; max-width: 400px; text-align: center; backdrop-filter: blur(20px); box-shadow: 0 40px 100px rgba(0,0,0,0.6); }
        .logo { height: 50px; margin-bottom: 2rem; }
        h1 { font-size: 1.5rem; margin-bottom: 0.5rem; }
        p { color: #8898b5; font-size: 0.9rem; margin-bottom: 2rem; }
        .form-group { text-align: left; margin-bottom: 1.5rem; }
        label { display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 0.5rem; color: #c9a84c; text-transform: uppercase; letter-spacing: 0.05em; }
        input { width: 100%; box-sizing: border-box; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); padding: 1rem; border-radius: 12px; color: #fff; font-size: 1rem; outline: none; transition: 0.3s; }
        input:focus { border-color: #c9a84c; background: rgba(255,255,255,0.08); }
        .btn-login { width: 100%; background: linear-gradient(135deg, #c9a84c, #e8943a); color: #030812; border: none; padding: 1.1rem; border-radius: 50px; font-weight: 800; font-size: 1rem; cursor: pointer; transition: 0.3s; box-shadow: 0 10px 30px rgba(201,168,76,0.3); }
        .btn-login:hover { transform: translateY(-3px); box-shadow: 0 15px 40px rgba(201,168,76,0.5); }
        .error { color: #fc8181; background: rgba(229,62,62,0.1); padding: 0.8rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="login-card">
        <img src="../../images/logo-v2.png" alt="HOABL" class="logo">
        <h1>Backend Access</h1>
        <p>Enter your credentials to manage leads.</p>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required placeholder="admin">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="••••••••">
            </div>
            <button type="submit" class="btn-login">LOGIN TO DASHBOARD</button>
        </form>
    </div>
</body>
</html>
