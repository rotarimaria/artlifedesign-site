<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

if (adminIsLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';

// Se verifică emailul și parola, apoi se pornește sesiunea de admin.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
        $error = 'Sesiunea a expirat. Reîncarcă pagina și încearcă din nou.';
    } else {
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            $error = 'Completează emailul și parola.';
        } else {
            $stmt = $pdo->prepare(
                'SELECT id, name, email, password_hash
                 FROM admin_users
                 WHERE email = :email AND is_active = 1
                 LIMIT 1'
            );
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, (string) $user['password_hash'])) {
                loginAdmin($user);

                $pdo->prepare(
                    'UPDATE admin_users SET last_login_at = NOW() WHERE id = :id'
                )->execute(['id' => $user['id']]);

                header('Location: index.php');
                exit;
            }

            $error = 'Datele de autentificare nu sunt corecte.';
        }
    }
}
?>
<!doctype html>
<html lang="ro">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Admin | Art Life Design</title>
<style>
:root{--green:#92ff22;--bg:#050706;--panel:#0d110e;--white:#f7f4eb;--muted:rgba(247,244,235,.62);--border:rgba(247,244,235,.12)}
*{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:24px;background:radial-gradient(circle at 20% 20%,rgba(146,255,34,.08),transparent 30%),var(--bg);color:var(--white);font-family:Inter,system-ui,sans-serif}
.login-card{width:min(430px,100%);padding:30px;border:1px solid var(--border);border-radius:22px;background:rgba(13,17,14,.96);box-shadow:0 30px 80px rgba(0,0,0,.35)}
.brand small{display:block;margin-bottom:8px;color:var(--green);font-size:12px;font-weight:800;letter-spacing:.14em;text-transform:uppercase}.brand h1{margin:0;font-size:28px}.brand p{margin:10px 0 28px;color:var(--muted);font-size:14px;line-height:1.55}
.field{display:grid;gap:7px;margin-bottom:16px}.field label{font-size:13px;font-weight:700}.field input{width:100%;min-height:48px;padding:0 14px;border:1px solid var(--border);border-radius:12px;outline:none;background:#080b09;color:var(--white);font:inherit}.field input:focus{border-color:rgba(146,255,34,.55);box-shadow:0 0 0 3px rgba(146,255,34,.08)}
.submit{width:100%;min-height:50px;border:0;border-radius:12px;background:var(--green);color:#071006;font:inherit;font-weight:900;cursor:pointer}.error{margin:0 0 18px;padding:12px 14px;border:1px solid rgba(255,90,90,.24);border-radius:12px;background:rgba(255,90,90,.07);color:#ffb3b3;font-size:13px}
</style>
</head>
<body>
<main class="login-card">
    <div class="brand">
        <small>Art Life Design</small>
        <h1>Administrare</h1>
        <p>Acces pentru administrator.</p>
    </div>

    <?php if ($error !== ''): ?>
        <div class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
        <div class="field">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" autocomplete="username" required autofocus>
        </div>
        <div class="field">
            <label for="password">Parolă</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required>
        </div>
        <button class="submit" type="submit">Intră în administrare</button>
    </form>
</main>
</body>
</html>