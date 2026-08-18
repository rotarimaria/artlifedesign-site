<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

try {
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../includes/services.php';

    $items = array_map(static function(array $s): array {
        $images = [];

        for ($i = 1; $i <= 3; $i++) {
            $images[] = [
                'src' => (string)$s["image{$i}"],
                'cropX' => (float)$s["image{$i}_crop_x"],
                'cropY' => (float)$s["image{$i}_crop_y"],
                'zoom' => (float)$s["image{$i}_zoom"],
                'rotation' => (int)$s["image{$i}_rotation"],
                'fit' => (string)$s["image{$i}_fit"],
            ];
        }

        return [
            'id' => (int)$s['id'],
            'slug' => (string)$s['slug'],
            'name' => (string)$s['name'],
            'cardTitle' => (string)$s['card_title'],
            'cardText' => (string)$s['card_text'],
            'detailTitle' => (string)$s['detail_title'],
            'detailText' => (string)$s['detail_text'],
            'examples' => preg_split('/\r?\n/u', (string)$s['examples']) ?: [],
            'btnExamples' => (string)$s['btn_examples'],
            'btnQuote' => (string)$s['btn_quote'],
            'images' => $images,
        ];
    }, getServices($pdo, true));

    echo json_encode(
        ['ok'=>true,'services'=>$items],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
    );
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(
        ['ok'=>false,'services'=>[],'message'=>$e->getMessage()],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
}