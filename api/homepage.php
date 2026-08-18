<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/homepage_content.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

// Se trimite răspunsul API în format JSON.
function homepageResponse(array $data, int $status = 200): never
{
    http_response_code($status);

    echo json_encode(
        $data,
        JSON_UNESCAPED_UNICODE
        | JSON_UNESCAPED_SLASHES
        | JSON_INVALID_UTF8_SUBSTITUTE
    ) ?: '{"ok":false,"content":{},"message":"Eroare la generarea JSON."}';

    exit;
}

try {
    // Se ia conținutul homepage-ului din BD.
    homepageResponse([
        'ok' => true,
        'content' => getHomepageContent($pdo),
    ]);
} catch (Throwable $e) {
    error_log('Homepage API: ' . $e->getMessage());

    homepageResponse([
        'ok' => false,
        'content' => new stdClass(),
        'message' => 'Conținutul homepage-ului nu a putut fi încărcat.',
    ], 500);
}