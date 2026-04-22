<?php
require_once __DIR__ . '/../config.php';

// ===== HTTP BASIC AUTH (Fix for CGI/FastCGI) =====
if (!isset($_SERVER['PHP_AUTH_USER'])) {
    if (isset($_SERVER['HTTP_AUTHORIZATION']) && stripos($_SERVER['HTTP_AUTHORIZATION'], 'basic') === 0) {
        list($user, $pass) = explode(':', base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
        $_SERVER['PHP_AUTH_USER'] = $user;
        $_SERVER['PHP_AUTH_PW'] = $pass;
    } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION']) && stripos($_SERVER['REDIRECT_HTTP_AUTHORIZATION'], 'basic') === 0) {
        list($user, $pass) = explode(':', base64_decode(substr($_SERVER['REDIRECT_HTTP_AUTHORIZATION'], 6)));
        $_SERVER['PHP_AUTH_USER'] = $user;
        $_SERVER['PHP_AUTH_PW'] = $pass;
    }
}

if (!isset($_SERVER['PHP_AUTH_USER']) ||
    $_SERVER['PHP_AUTH_USER'] !== ADMIN_USER ||
    $_SERVER['PHP_AUTH_PW']   !== ADMIN_PASS) {
    header('WWW-Authenticate: Basic realm="HOABL Admin"');
    header('HTTP/1.0 401 Unauthorized');
    echo '<h3>Access Denied.</h3>';
    exit;
}

// ===== FETCH LEADS =====
$leads = [];
$error = '';
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    );
    // ----- DELETE LEAD LOGIC -----
    if (isset($_GET['delete'])) {
        $delId = (int)$_GET['delete'];
        $stmt = $pdo->prepare("DELETE FROM leads WHERE id = ?");
        $stmt->execute([$delId]);
        header("Location: index.php?msg=deleted");
        exit;
    }
    $leads = $pdo->query("SELECT * FROM leads ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = 'DB Error: ' . $e->getMessage();
    // Fallback to CSV
    $csvFile = __DIR__ . '/../leads.csv';
    if (file_exists($csvFile)) {
        $fp = fopen($csvFile, 'r');
        $headers = fgetcsv($fp);
        while (($row = fgetcsv($fp)) !== false) {
            $leads[] = array_combine($headers, $row);
        }
        fclose($fp);
        $leads = array_reverse($leads);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>HOABL Leads Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:'Inter',sans-serif;background:#050a14;color:#e8eaf0;min-height:100vh}
    .header{background:linear-gradient(135deg,#0a1628,#0f2244);padding:1.5rem 2rem;border-bottom:1px solid rgba(255,255,255,0.08);display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap}
    .header h1{font-size:1.4rem;color:#c9a84c;display:flex;align-items:center;gap:0.6rem}
    .header-meta{color:#8898b5;font-size:0.85rem}
    .container{max-width:1400px;margin:0 auto;padding:2rem}
    .stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;margin-bottom:2rem}
    .stat-card{background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);border-radius:14px;padding:1.2rem;text-align:center}
    .stat-card .num{font-size:1.8rem;font-weight:800;color:#c9a84c}
    .stat-card .lbl{font-size:0.78rem;color:#8898b5;text-transform:uppercase;letter-spacing:.08em;margin-top:.3rem}
    .actions{display:flex;gap:1rem;margin-bottom:1.5rem;flex-wrap:wrap;align-items:center}
    .btn-download{background:linear-gradient(135deg,#c9a84c,#e8943a);color:#050a14;font-weight:700;padding:.7rem 1.6rem;border-radius:50px;text-decoration:none;font-size:.9rem;transition:transform .2s,box-shadow .2s;border:none;cursor:pointer}
    .btn-download:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(201,168,76,.4)}
    .search{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);border-radius:10px;padding:.65rem 1rem;color:#e8eaf0;font-family:'Inter',sans-serif;font-size:.88rem;outline:none;flex:1;min-width:200px}
    .error{background:rgba(229,62,62,.1);border:1px solid rgba(229,62,62,.3);padding:.8rem 1.2rem;border-radius:10px;color:#fc8181;margin-bottom:1.5rem;font-size:.88rem}
    .table-wrap{overflow-x:auto;border-radius:16px;border:1px solid rgba(255,255,255,.08)}
    table{width:100%;border-collapse:collapse;min-width:900px}
    thead{background:rgba(10,22,40,.8)}
    th{padding:1rem .9rem;text-align:left;font-size:.72rem;font-weight:700;color:#c9a84c;text-transform:uppercase;letter-spacing:.1em;white-space:nowrap}
    tbody tr{border-bottom:1px solid rgba(255,255,255,.04);transition:background .15s}
    tbody tr:hover{background:rgba(255,255,255,.03)}
    td{padding:.85rem .9rem;font-size:.84rem;color:#c0cce0;max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
    td.project{color:#f0d080;font-weight:600;max-width:260px}
    td.phone a{color:#c9a84c;text-decoration:none}
    td.phone a:hover{text-decoration:underline}
    td.msg{color:#8898b5;font-style:italic}
    .badge{display:inline-block;padding:.2rem .6rem;border-radius:20px;font-size:.67rem;font-weight:700;background:rgba(0,200,132,.1);color:#00c884;border:1px solid rgba(0,200,132,.3)}
    .empty{text-align:center;padding:3rem;color:#8898b5}
    footer{text-align:center;padding:2rem;color:#4a5568;font-size:.78rem}
    @media(max-width:600px){.header{flex-direction:column;align-items:flex-start}}
  </style>
</head>
<body>
<div class="header">
  <h1>🏝️ HOABL Leads Admin</h1>
  <div>
    <span class="header-meta">Logged in as: <?= htmlspecialchars(ADMIN_USER) ?> &nbsp;|&nbsp; <?= date('d M Y, h:i A') ?></span>
  </div>
</div>

<div class="container">
  <?php if ($error): ?>
    <div class="error">⚠️ <?= htmlspecialchars($error) ?> (Showing CSV data if available)</div>
  <?php endif; ?>

  <?php
    $total = count($leads);
    $gulf  = count(array_filter($leads, fn($l) => stripos($l['project'] ?? '', 'gulf') !== false));
    $oneGoa= count(array_filter($leads, fn($l) => stripos($l['project'] ?? '', 'one goa') !== false));
    $today = count(array_filter($leads, fn($l) => date('Y-m-d', strtotime($l['created_at'] ?? '')) === date('Y-m-d')));
  ?>
  <div class="stats">
    <div class="stat-card"><div class="num"><?= $total ?></div><div class="lbl">Total Leads</div></div>
    <div class="stat-card"><div class="num"><?= $today ?></div><div class="lbl">Today's Leads</div></div>
    <div class="stat-card"><div class="num"><?= $gulf ?></div><div class="lbl">Gulf of Goa</div></div>
    <div class="stat-card"><div class="num"><?= $oneGoa ?></div><div class="lbl">One Goa</div></div>
  </div>

  <div class="actions">
    <a href="download.php" class="btn-download">⬇️ Download All Leads (CSV)</a>
    <input type="text" class="search" id="searchInput" placeholder="🔍 Search by name, phone, project..." oninput="filterTable()" />
  </div>

  <div class="table-wrap">
    <table id="leadsTable">
      <thead>
        <tr>
          <th>#</th>
          <th>Date &amp; Time</th>
          <th>Name</th>
          <th>Phone</th>
          <th>Email</th>
          <th>Project Interest</th>
          <th>Location</th>
          <th>Device</th>
          <th>Message</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
          <tr><td colspan="11" style="background:rgba(229,62,62,0.1); color:#fc8181; text-align:center; padding:0.5rem;">Lead deleted successfully.</td></tr>
        <?php endif; ?>
        <?php if (empty($leads)): ?>
          <tr><td colspan="11" class="empty">No leads yet. Share your website to start collecting enquiries!</td></tr>
        <?php else: ?>
          <?php foreach ($leads as $i => $lead): ?>
          <tr>
            <td><span class="badge"><?= $total - $i ?></span></td>
            <td><?= htmlspecialchars(date('d M Y', strtotime($lead['created_at'] ?? ''))) ?><br /><small style="color:#4a5568"><?= htmlspecialchars(date('h:i A', strtotime($lead['created_at'] ?? ''))) ?></small></td>
            <td><?= htmlspecialchars($lead['name'] ?? '') ?></td>
            <td class="phone"><a href="tel:<?= htmlspecialchars($lead['phone'] ?? '') ?>"><?= htmlspecialchars($lead['phone'] ?? '') ?></a></td>
            <td><small><?= htmlspecialchars($lead['email'] ?? '') ?></small></td>
            <td class="project" title="<?= htmlspecialchars($lead['project'] ?? '') ?>"><?= htmlspecialchars($lead['project'] ?? '') ?></td>
            <td>
                <small style="color:#c9a84c; font-weight:600;"><?= htmlspecialchars($lead['city'] ?? 'Unknown') ?></small><br/>
                <small style="color:#8898b5;"><?= htmlspecialchars($lead['country'] ?? 'Unknown') ?></small>
            </td>
            <td><small><?= htmlspecialchars($lead['device'] ?? 'Unknown') ?></small></td>
            <td class="msg" title="<?= htmlspecialchars($lead['message'] ?? '') ?>"><?= htmlspecialchars(substr($lead['message'] ?? '', 0, 40)) . (strlen($lead['message'] ?? '') > 40 ? '…' : '') ?></td>
            <td>
                <?php if (isset($lead['id'])): ?>
                <a href="?delete=<?= $lead['id'] ?>" onclick="return confirm('Are you sure you want to delete this lead?')" style="color:#fc8181; text-decoration:none; font-size:0.75rem; border:1px solid rgba(229,62,62,0.3); padding:2px 8px; border-radius:4px;">Delete</a>
                <?php else: ?>
                <span style="color:#4a5568; font-size:0.7rem;">(CSV)</span>
                <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<footer>HOABL Goa – Admin Panel &copy; <?= date('Y') ?></footer>

<script>
function filterTable() {
  const q = document.getElementById('searchInput').value.toLowerCase();
  document.querySelectorAll('#leadsTable tbody tr').forEach(row => {
    row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
}
</script>
</body>
</html>
