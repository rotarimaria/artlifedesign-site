<?php
declare(strict_types=1);

function serviceSlug(string $value): string
{
    $value = trim(mb_strtolower($value));
    $map = ['ă'=>'a','â'=>'a','î'=>'i','ș'=>'s','ş'=>'s','ț'=>'t','ţ'=>'t'];
    $value = strtr($value, $map);
    $value = preg_replace('/[^a-z0-9]+/u', '-', $value) ?: '';
    return trim($value, '-');
}

function getServices(PDO $pdo, bool $activeOnly = false): array
{
    $sql = 'SELECT * FROM services';
    if ($activeOnly) $sql .= ' WHERE is_active = 1';
    $sql .= ' ORDER BY sort_order ASC, id ASC';

    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

function serviceCategories(PDO $pdo): array
{
    $out = [];
    foreach (getServices($pdo, true) as $service) {
        $out[(string)$service['slug']] = (string)$service['name'];
    }
    return $out;
}

function saveServiceUpload(array $file): string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        throw new RuntimeException('Nu a fost selectată imaginea.');
    }
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Imaginea nu s-a încărcat corect.');
    }

    $tmp = (string)($file['tmp_name'] ?? '');
    $size = (int)($file['size'] ?? 0);
    $mime = (string)(new finfo(FILEINFO_MIME_TYPE))->file($tmp);

    $allowed = [
        'image/jpeg'=>'jpg',
        'image/png'=>'png',
        'image/webp'=>'webp',
        'image/gif'=>'gif',
    ];

    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Format neacceptat. Folosește JPG, PNG, WEBP sau GIF.');
    }
    if ($size < 1 || $size > 8 * 1024 * 1024) {
        throw new RuntimeException('Imaginea trebuie să aibă maximum 8 MB.');
    }

    $dir = dirname(__DIR__) . '/uploads/homepage';
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        throw new RuntimeException('Nu s-a putut crea uploads/homepage.');
    }

    $name = 'service-' . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];

    if (!move_uploaded_file($tmp, $dir . '/' . $name)) {
        throw new RuntimeException('Imaginea nu a putut fi salvată.');
    }

    return 'uploads/homepage/' . $name;
}