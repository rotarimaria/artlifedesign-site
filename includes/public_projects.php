<?php
declare(strict_types=1);

// Se pregătesc proiectele publicate pentru API și site.

require_once __DIR__ . '/../config/database.php';

// Se încarcă proiectele și toate imaginile/video într-un singur query.
function getPublicProjects(PDO $pdo): array
{
    $rows = $pdo->query(
        'SELECT
            p.id, p.title, p.service, p.category, p.description, p.tags,
            pi.id AS media_id, pi.image_path,
            COALESCE(pi.media_type, "image") AS media_type,
            COALESCE(pi.crop_x, 50) AS crop_x,
            COALESCE(pi.crop_y, 50) AS crop_y,
            COALESCE(pi.crop_zoom, 1) AS crop_zoom,
            COALESCE(pi.fit_mode, "cover") AS fit_mode,
            COALESCE(pi.rotation, 0) AS rotation
         FROM projects p
         LEFT JOIN project_images pi ON pi.project_id = p.id
         WHERE p.is_published = 1
         ORDER BY
            COALESCE(p.published_at, p.created_at) DESC,
            p.id DESC,
            pi.is_primary DESC,
            pi.sort_order ASC,
            pi.id ASC'
    )->fetchAll(PDO::FETCH_ASSOC);

    $projects = [];

    foreach ($rows as $row) {
        $id = (int) $row['id'];

        if (!isset($projects[$id])) {
            $title = trim((string) $row['title']);
            $service = trim((string) $row['service']);
            $category = trim((string) $row['category']);
            $description = trim((string) $row['description']);
            $tags = trim((string) ($row['tags'] ?? ''));

            $projects[$id] = [
                'id' => $id,
                'title' => $title,
                'service' => $service,
                'category' => $category,
                'desc' => $description,
                'tags' => $tags,
                'date' => '',
                'search' => mb_strtolower(
                    implode(' ', [$title, $service, $category, $tags, $description]),
                    'UTF-8'
                ),
                'media' => [],
            ];
        }

        if (!empty($row['media_id']) && !empty($row['image_path'])) {
            $projects[$id]['media'][] = [
                'src' => (string) $row['image_path'],
                'type' => (string) $row['media_type'],
                'cropX' => (float) $row['crop_x'],
                'cropY' => (float) $row['crop_y'],
                'zoom' => (float) $row['crop_zoom'],
                'fit' => (string) $row['fit_mode'],
                'rotation' => (int) $row['rotation'],
            ];
        }
    }

    $result = array_values($projects);

    // Se păstrează și câmpurile vechi folosite încă în JavaScript.
    foreach ($result as &$project) {
        $primary = $project['media'][0] ?? null;

        $project['image'] = $primary['src'] ?? '';
        $project['focus'] = $primary
            ? $primary['cropX'] . '% ' . $primary['cropY'] . '%'
            : '50% 50%';

        $project['images'] = array_column($project['media'], 'src');
    }
    unset($project);

    return $result;
}

function publicProjectsJson(array $projects): string
{
    return json_encode(
        $projects,
        JSON_UNESCAPED_UNICODE
        | JSON_UNESCAPED_SLASHES
        | JSON_HEX_TAG
        | JSON_HEX_AMP
        | JSON_HEX_APOS
        | JSON_HEX_QUOT
    ) ?: '[]';
}