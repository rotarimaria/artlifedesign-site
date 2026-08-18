<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

// Se ia numărul proiectelor pentru sumarul din dashboard.
$stats = $pdo->query(
    'SELECT COUNT(*) AS total,
            SUM(is_published = 1) AS published
     FROM projects'
)->fetch(PDO::FETCH_ASSOC);

$projectCount = (int) ($stats['total'] ?? 0);
$publishedCount = (int) ($stats['published'] ?? 0);
?>
<!doctype html>
<html lang="ro">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Dashboard | Art Life Design</title>
    <style>
        :root{--green:#92ff22;--bg:#050706;--panel:#0d110e;--white:#f6f3ea;--muted:rgba(246,243,234,.66);--border:rgba(246,243,234,.13)}
        *{box-sizing:border-box}body{margin:0;background:var(--bg);color:var(--white);font-family:Inter,system-ui,sans-serif}a{color:inherit;text-decoration:none}
        .topbar{min-height:70px;display:flex;align-items:center;justify-content:space-between;gap:20px;padding:0 28px;border-bottom:1px solid var(--border)}
        .brand{font-weight:600}.brand span,.eyebrow,.admin-card small,.admin-card strong{color:var(--green)}.top-actions{display:flex;align-items:center;gap:12px}.top-actions small,.intro,.stat span,.admin-card p{color:var(--muted)}
        .logout{padding:9px 12px;border:1px solid var(--border);border-radius:10px;font-size:13px}.layout{width:min(1180px,calc(100% - 32px));margin:auto;padding:42px 0 70px}
        .eyebrow{font-size:12px;font-weight:600;letter-spacing:.12em;text-transform:uppercase}h1{margin:8px 0 10px;font-size:clamp(32px,5vw,56px);line-height:1}.intro{max-width:680px;margin:0;line-height:1.7}
        .stats,.sections{display:grid;gap:14px}.stats{grid-template-columns:repeat(2,1fr);margin-top:34px}.sections{grid-template-columns:repeat(3,1fr);margin-top:28px}
        .stat,.admin-card{border:1px solid var(--border);border-radius:18px;background:var(--panel)}.stat{padding:22px}.stat strong{display:block;margin-bottom:4px;font-size:30px}
        .admin-card{min-height:220px;padding:22px;display:flex;flex-direction:column}.admin-card small{font-size:11px;text-transform:uppercase}.admin-card h2{margin:10px 0 8px;font-size:22px}.admin-card p{margin:0;font-size:14px;line-height:1.6}.admin-card strong{margin-top:auto;padding-top:24px;font-size:13px}
        @media(max-width:760px){.topbar{padding:0 16px}.top-actions small{display:none}.stats,.sections{grid-template-columns:1fr}}
    </style>
</head>
<body>
<header class="topbar">
    <div class="brand">ArtLife <span>Admin</span></div>
    <div class="top-actions">
        <small><?= htmlspecialchars((string) ($_SESSION['admin_email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></small>
        <a class="logout" href="logout.php">Ieșire</a>
    </div>
</header>

<main class="layout">
    <span class="eyebrow">Panou administrativ</span>
    <h1>Bun venit.</h1>
    <p class="intro">De aici administrezi conținutul site-ului și proiectele.</p>

    <section class="stats">
        <div class="stat"><strong><?= $projectCount ?></strong><span>Proiecte în total</span></div>
        <div class="stat"><strong><?= $publishedCount ?></strong><span>Proiecte publicate</span></div>
    </section>

    <section class="sections">
        <a class="admin-card" href="homepage/">
            <small>Homepage</small><h2>Pagina principală</h2>
            <p>Texte, imagini, servicii și secțiuni.</p>
            <strong>Editează pagina →</strong>
        </a>
        <a class="admin-card" href="lucrari/">
            <small>Portofoliu</small><h2>Pagina Lucrări</h2>
            <p>Textele și opțiunile paginii de portofoliu.</p>
            <strong>Editează pagina →</strong>
        </a>
        <a class="admin-card" href="project.php">
            <small>Proiecte</small><h2>Lucrări / Carduri</h2>
            <p>Adaugă, editează și șterge proiecte, imagini și video.</p>
            <strong>Deschide proiectele →</strong>
        </a>
    </section>
</main>
</body>
</html>