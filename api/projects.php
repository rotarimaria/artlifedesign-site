<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/public_projects.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

try {
    // Se încarcă proiectele publicate din funcția comună.
    echo json_encode(
        [
            'ok' => true,
            'projects' => getPublicProjects($pdo),
        ],
        JSON_UNESCAPED_UNICODE
        | JSON_UNESCAPED_SLASHES
        | JSON_INVALID_UTF8_SUBSTITUTE
    );
} catch (Throwable $e) {
    http_response_code(500);
    error_log('Projects API: ' . $e->getMessage());

    echo json_encode(
        [
            'ok' => false,
            'projects' => [],
            'message' => 'Proiectele nu au putut fi încărcate.',
        ],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
}