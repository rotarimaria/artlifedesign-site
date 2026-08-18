<?php
declare(strict_types=1);

// Textele editabile pentru pagina Lucrări.

function worksPageFields(): array
{
    return [
        'back_text' => 'Înapoi la pagina principală',
        'label' => 'Portofoliu',
        'title' => 'Lucrări realizate',
        'intro' => 'Explorează proiectele Art Life Design și filtrează după tipul de lucrare.',
        'search_placeholder' => 'Caută în portofoliu...',
        'filter_all' => 'Toate',
        'no_results' => 'Nu am găsit lucrări după această căutare.',
        'load_more' => 'Mai multe',
        'cta_label' => 'Ai găsit ceva apropiat de proiectul tău?',
        'cta_title' => 'Trimite-ne un mesaj și discutăm detaliile.',
        'cta_button' => 'Contact',
    ];
}

// Se iau valorile salvate din BD peste textele implicite.
function getWorksPageContent(PDO $pdo): array
{
    $content = worksPageFields();

    $rows = $pdo->query(
        "SELECT content_key, content_value
         FROM site_content
         WHERE content_key LIKE 'works_%'"
    )->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $row) {
        $key = substr((string) $row['content_key'], 6);

        if (array_key_exists($key, $content)) {
            $content[$key] = (string) $row['content_value'];
        }
    }

    return $content;
}

// Se salvează toate textele paginii Lucrări.
function saveWorksPageContent(PDO $pdo, array $values): void
{
    $stmt = $pdo->prepare(
        'INSERT INTO site_content (content_key, content_value)
         VALUES (:key, :value)
         ON DUPLICATE KEY UPDATE
            content_value = VALUES(content_value),
            updated_at = CURRENT_TIMESTAMP'
    );

    foreach (worksPageFields() as $key => $default) {
        $stmt->execute([
            'key' => 'works_' . $key,
            'value' => (string) ($values[$key] ?? $default),
        ]);
    }
}