<?php
declare(strict_types=1);

// Funcții pentru autentificarea și protecția paginilor de admin.

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function adminIsLoggedIn(): bool
{
    return isset($_SESSION['admin_id']) && is_numeric($_SESSION['admin_id']);
}

// Se verifică accesul în admin și se trimite la login dacă sesiunea lipsește.
function requireAdmin(): void
{
    if (adminIsLoggedIn()) {
        return;
    }

    $script = (string) ($_SERVER['SCRIPT_NAME'] ?? '');
    $pos = strpos($script, '/admin/');
    $login = $pos === false
        ? 'login.php'
        : substr($script, 0, $pos) . '/admin/login.php';

    header('Location: ' . $login);
    exit;
}

function loginAdmin(array $user): void
{
    session_regenerate_id(true);

    $_SESSION['admin_id'] = (int) $user['id'];
    $_SESSION['admin_email'] = (string) $user['email'];
    $_SESSION['admin_name'] = (string) ($user['name'] ?? 'Administrator');
}

function logoutAdmin(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            time() - 42000,
            $p['path'],
            $p['domain'],
            $p['secure'],
            $p['httponly']
        );
    }

    session_destroy();
}

// Se generează tokenul folosit pentru protecția formularelor.
function csrfToken(): string
{
    if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verifyCsrf(?string $token): bool
{
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && is_string($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}