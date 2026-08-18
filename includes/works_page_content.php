<?php
declare(strict_types=1);

function worksPageFields(): array
{
    return [
        'back_text' => 'Înapoi la pagina principală',
        'label' => 'Portofoliu',
        'title' => 'Lucrări realizate',
        'intro' => 'Explorează proiectele Art Life Design și filtrează după tipul de lucrare.',
        'search_placeholder' => 'Caută în portofoliu...',
        'filter_all' => 'Toate',
        'filter_poligrafie' => 'Poligrafie',
        'filter_volum' => 'Litere & Standuri',
        'filter_posm' => 'P.O.S.M.',
        'filter_auto' => 'Branding Auto',
        'filter_laser' => 'Laser / Plotter',
        'show_poligrafie' => '1',
        'show_volum' => '1',
        'show_posm' => '1',
        'show_auto' => '1',
        'show_laser' => '1',
        'no_results' => 'Nu am găsit lucrări după această căutare.',
        'load_more' => 'Mai multe',
        'cta_label' => 'Ai găsit ceva apropiat de proiectul tău?',
        'cta_title' => 'Trimite-ne un mesaj și discutăm detaliile.',
        'cta_button' => 'Contact',
    ];
}

function getWorksPageContent(PDO $pdo): array
{
    $content = worksPageFields();
    $keys = array_keys($content);
    $marks = implode(',', array_fill(0, count($keys), '?'));

    $stmt = $pdo->prepare(
        "SELECT content_key, content_value
         FROM site_content
         WHERE content_key IN ($marks)"
    );
    $stmt->execute(array_map(fn($k) => 'works_' . $k, $keys));

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $key = preg_replace('/^works_/', '', (string) $row['content_key']);
        if (array_key_exists($key, $content)) {
            $content[$key] = (string) $row['content_value'];
        }
    }

    return $content;
}

function saveWorksPageContent(PDO $pdo, array $values): void
{
    $allowed = worksPageFields();

    $stmt = $pdo->prepare(
        'INSERT INTO site_content (content_key, content_value)
         VALUES (:k, :v)
         ON DUPLICATE KEY UPDATE
           content_value = VALUES(content_value),
           updated_at = CURRENT_TIMESTAMP'
    );

    foreach ($allowed as $key => $default) {
        $stmt->execute([
            'k' => 'works_' . $key,
            'v' => (string) ($values[$key] ?? $default),
        ]);
    }
}