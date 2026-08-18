<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

function homepageJsonResponse(array $data, int $status = 200): never
{
    http_response_code($status);

    $json = json_encode(
        $data,
        JSON_UNESCAPED_UNICODE
        | JSON_UNESCAPED_SLASHES
        | JSON_INVALID_UTF8_SUBSTITUTE
    );

    if ($json === false) {
        http_response_code(500);
        echo '{"ok":false,"content":{},"message":"Eroare la generarea JSON."}';
        exit;
    }

    echo $json;
    exit;
}

try {
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../includes/homepage_content.php';

    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new RuntimeException(
            'Conexiunea PDO nu este disponibilă în config/database.php.'
        );
    }

    if (!function_exists('getHomepageContent')) {
        throw new RuntimeException(
            'Funcția getHomepageContent() nu există în includes/homepage_content.php.'
        );
    }

    $content = getHomepageContent($pdo);

    homepageJsonResponse([
        'ok' => true,
        'content' => $content,
    ]);
} catch (Throwable $e) {
    error_log(
        'Homepage API error: ' .
        $e->getMessage() .
        ' in ' .
        $e->getFile() .
        ':' .
        $e->getLine()
    );

    homepageJsonResponse([
        'ok' => false,
        'content' => new stdClass(),
        'message' => $e->getMessage(),
    ], 500);
}