<?php
declare(strict_types=1);

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

$stmt = $pdo->prepare(
    'SELECT id FROM admin_users WHERE email = :email LIMIT 1'
);
$stmt->execute(['email' => $email]);

if ($stmt->fetch()) {
    exit("Există deja un administrator cu acest email.\n");
}

$passwordHash = password_hash($password, PASSWORD_DEFAULT);

$insert = $pdo->prepare(
    'INSERT INTO admin_users
        (name, email, password_hash, is_active)
     VALUES
        (:name, :email, :password_hash, 1)'
);

$insert->execute([
    'name' => $name !== '' ? $name : 'Administrator',
    'email' => $email,
    'password_hash' => $passwordHash,
]);

echo "Administrator creat cu succes.\n";
echo "Email: {$email}\n";
