<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/services.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

try {
    // Se pregătesc serviciile active pentru site.
    $services = array_map(static function (array $service): array {
        $images = [];

        for ($i = 1; $i <= 3; $i++) {
            $images[] = [
                'src' => (string) $service["image{$i}"],
                'cropX' => (float) $service["image{$i}_crop_x"],
                'cropY' => (float) $service["image{$i}_crop_y"],
                'zoom' => (float) $service["image{$i}_zoom"],
                'rotation' => (int) $service["image{$i}_rotation"],
                'fit' => (string) $service["image{$i}_fit"],
            ];
        }

        // Prima imagine are o ajustare separată pentru cardul mic.
        $cardImage = [
            'src' => (string) $service['image1'],
            'cropX' => (float) $service['image1_card_crop_x'],
            'cropY' => (float) $service['image1_card_crop_y'],
            'zoom' => (float) $service['image1_card_zoom'],
            'rotation' => (int) $service['image1_card_rotation'],
            'fit' => (string) $service['image1_card_fit'],
        ];

        return [
            'id' => (int) $service['id'],
            'slug' => (string) $service['slug'],
            'name' => (string) $service['name'],
            'cardTitle' => (string) $service['card_title'],
            'cardText' => (string) $service['card_text'],
            'detailTitle' => (string) $service['detail_title'],
            'detailText' => (string) $service['detail_text'],
            'examples' => array_values(array_filter(
                preg_split('/\r?\n/u', (string) $service['examples']) ?: [],
                static fn (string $value): bool => trim($value) !== ''
            )),
            'btnExamples' => (string) $service['btn_examples'],
            'btnQuote' => (string) $service['btn_quote'],
            'cardImage' => $cardImage,
            'images' => $images,
        ];
    }, getServices($pdo, true));

    echo json_encode(
        [
            'ok' => true,
            'services' => $services,
        ],
        JSON_UNESCAPED_UNICODE
        | JSON_UNESCAPED_SLASHES
        | JSON_INVALID_UTF8_SUBSTITUTE
    );
} catch (Throwable $e) {
    http_response_code(500);
    error_log('Services API: ' . $e->getMessage());

    echo json_encode(
        [
            'ok' => false,
            'services' => [],
            'message' => 'Serviciile nu au putut fi încărcate.',
        ],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
}