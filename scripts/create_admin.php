<?php
declare(strict_types=1);

// Script pentru crearea unui cont nou de administrator.

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Acest script poate fi rulat doar din terminal.');
}

require_once __DIR__ . '/../config/database.php';

$email = trim((string) ($argv[1] ?? ''));
$password = (string) ($argv[2] ?? '');
$name = trim((string) ($argv[3] ?? 'Administrator'));

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    exit("Email invalid.\n");
}

if (strlen($password) < 12) {
    exit("Parola trebuie să aibă minimum 12 caractere.\n");
}

if ($name === '') {
    $name = 'Administrator';
}

// Se verifică dacă emailul este deja folosit.
$stmt = $pdo->prepare(
    'SELECT COUNT(*) FROM admin_users WHERE email = :email'
);
$stmt->execute(['email' => $email]);

if ((int) $stmt->fetchColumn() > 0) {
    exit("Există deja un administrator cu acest email.\n");
}

// Se salvează parola criptată și contul nou.
$stmt = $pdo->prepare(
    'INSERT INTO admin_users (name, email, password_hash, is_active)
     VALUES (:name, :email, :password_hash, 1)'
);

$stmt->execute([
    'name' => $name,
    'email' => $email,
    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
]);

echo "Administrator creat cu succes.\n";
echo "Email: {$email}\n";