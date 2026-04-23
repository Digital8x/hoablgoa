<?php
date_default_timezone_set('Asia/Kolkata');
// ===== DATABASE CONFIGURATION =====
define('DB_HOST', 'localhost');
define('DB_NAME', 'a1679hju_hoablgoa');
define('DB_USER', 'a1679hju_hoablgoa');       // Change to your MySQL username
define('DB_PASS', 'hoablgoa123');           // Change to your MySQL password

// ===== EMAIL CONFIGURATION =====
define('LEAD_EMAIL_TO', 'admin@digital8x.com');   // UPDATE THIS to your actual email for leads
define('LEAD_EMAIL_CC', 'secondemail@digital8x.com'); // UPDATE THIS to your secondary CC email
define('LEAD_EMAIL_FROM', 'leads@hoablgoa.com');
define('LEAD_EMAIL_NAME', 'HOABL Goa - Leads');
define('SITE_NAME', 'HOABL Goa Properties (Managed by Digital8X)');

// ===== ADMIN PANEL CREDENTIALS =====
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'hoabl@2026');   // Change this password!

// ===== SMTP (Optional - for Gmail/SMTP) =====
// If PHP mail() doesn't work on your server, use SMTP:
define('USE_SMTP', false);
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your@gmail.com');
define('SMTP_PASS', 'your-app-password');   // Use App Password, not Gmail password
