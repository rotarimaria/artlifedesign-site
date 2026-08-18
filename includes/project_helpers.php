<?php
declare(strict_types=1);

// Funcții comune pentru proiecte: categorii, taguri, upload și ajustarea imaginilor.

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// Se iau categoriile din serviciile active din BD.
function projectCategories(PDO $pdo): array
{
    require_once __DIR__ . '/services.php';

    try {
        $categories = serviceCategories($pdo);
        if ($categories) {
            return $categories;
        }
    } catch (Throwable) {
        // Se păstrează lista de rezervă dacă tabela services nu este disponibilă.
    }

    return [
        'poligrafie' => 'Poligrafie',
        'volum' => 'Litere & Standuri',
        'posm' => 'P.O.S.M.',
        'auto' => 'Branding Auto',
        'laser' => 'Laser / Plotter',
    ];
}

function projectUploadDir(): string
{
    return dirname(__DIR__) . '/uploads/projects';
}

function ensureProjectUploadDir(): void
{
    $dir = projectUploadDir();

    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        throw new RuntimeException('Nu s-a putut crea folderul pentru fișiere.');
    }
}

// Se curăță tagurile și se păstrează maximum 20.
function normalizeTags(string $tags): string
{
    $items = preg_split('/[,;\n]+/u', $tags) ?: [];
    $items = array_map('trim', $items);
    $items = array_filter($items, static fn(string $tag): bool => $tag !== '');
    $items = array_values(array_unique($items));

    return implode(', ', array_slice($items, 0, 20));
}

function clampFloat(float $value, float $min, float $max): float
{
    return max($min, min($max, $value));
}

function normalizeRotation(int $value): int
{
    $value %= 360;

    if ($value > 180) {
        $value -= 360;
    } elseif ($value < -180) {
        $value += 360;
    }

    return $value;
}

// Se normalizează poziția, zoomul, modul de afișare și rotirea.
function normalizeMediaDisplay(array $settings, int|string $key): array
{
    $fit = (string) ($settings['fit'][$key] ?? 'cover');
    $fit = in_array($fit, ['cover', 'contain'], true) ? $fit : 'cover';

    return [
        'crop_x' => clampFloat((float) ($settings['x'][$key] ?? 50), 0, 100),
        'crop_y' => clampFloat((float) ($settings['y'][$key] ?? 50), 0, 100),
        'crop_zoom' => clampFloat((float) ($settings['zoom'][$key] ?? 1), 1, 3),
        'fit_mode' => $fit,
        'rotation' => normalizeRotation((int) ($settings['rotation'][$key] ?? 0)),
    ];
}

function getProjectImages(int $projectId, PDO $pdo): array
{
    $stmt = $pdo->prepare(
        'SELECT *
         FROM project_images
         WHERE project_id = :project_id
         ORDER BY is_primary DESC, sort_order ASC, id ASC'
    );
    $stmt->execute(['project_id' => $projectId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Se verifică tipul fișierului și limita lui.
function detectUploadedMedia(string $tmpPath): array
{
    $mime = (string) (new finfo(FILEINFO_MIME_TYPE))->file($tmpPath);

    $allowed = [
        'image/jpeg' => ['type' => 'image', 'ext' => 'jpg', 'max' => 8 * 1024 * 1024],
        'image/png' => ['type' => 'image', 'ext' => 'png', 'max' => 8 * 1024 * 1024],
        'image/webp' => ['type' => 'image', 'ext' => 'webp', 'max' => 8 * 1024 * 1024],
        'image/gif' => ['type' => 'image', 'ext' => 'gif', 'max' => 8 * 1024 * 1024],
        'video/mp4' => ['type' => 'video', 'ext' => 'mp4', 'max' => 80 * 1024 * 1024],
        'video/webm' => ['type' => 'video', 'ext' => 'webm', 'max' => 80 * 1024 * 1024],
        'video/quicktime' => ['type' => 'video', 'ext' => 'mov', 'max' => 80 * 1024 * 1024],
    ];

    if (!isset($allowed[$mime])) {
        throw new RuntimeException(
            'Format neacceptat. Folosește JPG, PNG, WEBP, GIF, MP4, WEBM sau MOV.'
        );
    }

    return $allowed[$mime];
}

// Se salvează maximum 4 imagini/video pentru proiect.
function saveUploadedProjectMedia(
    array $files,
    int $projectId,
    PDO $pdo,
    array $displaySettings = [],
    int $maxFiles = 4
): array {
    ensureProjectUploadDir();

    if (!isset($files['name']) || !is_array($files['name'])) {
        return [];
    }

    $stmt = $pdo->prepare(
        'SELECT
            COUNT(*) AS total,
            COALESCE(MAX(sort_order), -1) + 1 AS next_order,
            MAX(is_primary) AS has_primary
         FROM project_images
         WHERE project_id = :project_id'
    );
    $stmt->execute(['project_id' => $projectId]);
    $info = $stmt->fetch(PDO::FETCH_ASSOC);

    $remaining = max(0, $maxFiles - (int) ($info['total'] ?? 0));
    $sortOrder = (int) ($info['next_order'] ?? 0);
    $hasPrimary = (int) ($info['has_primary'] ?? 0) === 1;

    if ($remaining === 0) {
        return [];
    }

    $insert = $pdo->prepare(
        'INSERT INTO project_images
            (project_id, image_path, alt_text, sort_order, is_primary,
             crop_x, crop_y, crop_zoom, fit_mode, media_type, rotation)
         VALUES
            (:project_id, :image_path, NULL, :sort_order, :is_primary,
             :crop_x, :crop_y, :crop_zoom, :fit_mode, :media_type, :rotation)'
    );

    $saved = [];

    foreach ($files['name'] as $i => $originalName) {
        if (count($saved) >= $remaining) {
            break;
        }

        $error = (int) ($files['error'][$i] ?? UPLOAD_ERR_NO_FILE);

        if ($error === UPLOAD_ERR_NO_FILE || trim((string) $originalName) === '') {
            continue;
        }
        if ($error !== UPLOAD_ERR_OK) {
            throw new RuntimeException(
                'Un fișier nu s-a încărcat corect. Verifică și limita PHP pentru upload.'
            );
        }

        $tmp = (string) ($files['tmp_name'][$i] ?? '');
        $size = (int) ($files['size'][$i] ?? 0);
        $media = detectUploadedMedia($tmp);

        if ($size <= 0 || $size > $media['max']) {
            $limit = $media['type'] === 'video' ? '80 MB' : '8 MB';
            throw new RuntimeException("Fișierul depășește limita de {$limit}.");
        }

        $filename = sprintf(
            'project-%d-%s.%s',
            $projectId,
            bin2hex(random_bytes(8)),
            $media['ext']
        );

        if (!move_uploaded_file($tmp, projectUploadDir() . '/' . $filename)) {
            throw new RuntimeException('Fișierul nu a putut fi salvat.');
        }

        $display = normalizeMediaDisplay($displaySettings, $i);
        $isPrimary = $hasPrimary ? 0 : 1;

        $insert->execute([
            'project_id' => $projectId,
            'image_path' => 'uploads/projects/' . $filename,
            'sort_order' => $sortOrder++,
            'is_primary' => $isPrimary,
            'crop_x' => $display['crop_x'],
            'crop_y' => $display['crop_y'],
            'crop_zoom' => $display['crop_zoom'],
            'fit_mode' => $display['fit_mode'],
            'media_type' => $media['type'],
            'rotation' => $display['rotation'],
        ]);

        $hasPrimary = true;
        $saved[(int) $i] = [
            'id' => (int) $pdo->lastInsertId(),
            'filename' => $filename,
        ];
    }

    return $saved;
}

// Se salvează ajustările imaginilor/video deja încărcate.
function updateExistingProjectMediaDisplays(
    int $projectId,
    PDO $pdo,
    array $displaySettings
): void {
    if (!isset($displaySettings['x']) || !is_array($displaySettings['x'])) {
        return;
    }

    $update = $pdo->prepare(
        'UPDATE project_images
         SET crop_x = :crop_x,
             crop_y = :crop_y,
             crop_zoom = :crop_zoom,
             fit_mode = :fit_mode,
             rotation = :rotation
         WHERE id = :id AND project_id = :project_id'
    );

    foreach ($displaySettings['x'] as $mediaId => $_) {
        $mediaId = (int) $mediaId;

        if ($mediaId <= 0) {
            continue;
        }

        $display = normalizeMediaDisplay($displaySettings, $mediaId);

        $update->execute([
            'crop_x' => $display['crop_x'],
            'crop_y' => $display['crop_y'],
            'crop_zoom' => $display['crop_zoom'],
            'fit_mode' => $display['fit_mode'],
            'rotation' => $display['rotation'],
            'id' => $mediaId,
            'project_id' => $projectId,
        ]);
    }
}


// Se înlocuiește fișierul, dar se păstrează același slot și aceleași ajustări.
function replaceProjectMedia(
    int $mediaId,
    int $projectId,
    array $file,
    PDO $pdo
): void {
    $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

    if ($error === UPLOAD_ERR_NO_FILE) {
        return;
    }
    if ($error !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Fișierul nou nu s-a încărcat corect.');
    }

    $stmt = $pdo->prepare(
        'SELECT image_path
         FROM project_images
         WHERE id = :id AND project_id = :project_id
         LIMIT 1'
    );
    $stmt->execute(['id' => $mediaId, 'project_id' => $projectId]);
    $oldPath = $stmt->fetchColumn();

    if (!$oldPath) {
        throw new RuntimeException('Fișierul de înlocuit nu a fost găsit.');
    }

    $tmp = (string) ($file['tmp_name'] ?? '');
    $size = (int) ($file['size'] ?? 0);
    $media = detectUploadedMedia($tmp);

    if ($size <= 0 || $size > $media['max']) {
        $limit = $media['type'] === 'video' ? '80 MB' : '8 MB';
        throw new RuntimeException("Fișierul depășește limita de {$limit}.");
    }

    ensureProjectUploadDir();

    $filename = sprintf(
        'project-%d-%s.%s',
        $projectId,
        bin2hex(random_bytes(8)),
        $media['ext']
    );
    $target = projectUploadDir() . '/' . $filename;

    if (!move_uploaded_file($tmp, $target)) {
        throw new RuntimeException('Fișierul nou nu a putut fi salvat.');
    }

    try {
        $pdo->prepare(
            'UPDATE project_images
             SET image_path = :path, media_type = :type
             WHERE id = :id AND project_id = :project_id'
        )->execute([
            'path' => 'uploads/projects/' . $filename,
            'type' => $media['type'],
            'id' => $mediaId,
            'project_id' => $projectId,
        ]);
    } catch (Throwable $e) {
        @unlink($target);
        throw $e;
    }

    deleteProjectImageFile((string) $oldPath);
}

function deleteProjectImageFile(string $mediaPath): void
{
    $file = projectUploadDir() . '/' . basename($mediaPath);

    if (is_file($file)) {
        @unlink($file);
    }
}

// Se șterge media din BD și fișierul din uploads.
function deleteProjectImageById(int $mediaId, int $projectId, PDO $pdo): void
{
    $stmt = $pdo->prepare(
        'SELECT image_path, is_primary
         FROM project_images
         WHERE id = :id AND project_id = :project_id
         LIMIT 1'
    );
    $stmt->execute(['id' => $mediaId, 'project_id' => $projectId]);
    $media = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$media) {
        return;
    }

    deleteProjectImageFile((string) $media['image_path']);

    $pdo->prepare(
        'DELETE FROM project_images
         WHERE id = :id AND project_id = :project_id'
    )->execute(['id' => $mediaId, 'project_id' => $projectId]);

    if ((int) $media['is_primary'] !== 1) {
        return;
    }

    $next = $pdo->prepare(
        'SELECT id
         FROM project_images
         WHERE project_id = :project_id
         ORDER BY sort_order ASC, id ASC
         LIMIT 1'
    );
    $next->execute(['project_id' => $projectId]);
    $nextId = $next->fetchColumn();

    if ($nextId) {
        $pdo->prepare(
            'UPDATE project_images SET is_primary = 1 WHERE id = :id'
        )->execute(['id' => $nextId]);
    }
}

// Se setează imaginea principală a proiectului.
function setPrimaryProjectImage(int $mediaId, int $projectId, PDO $pdo): void
{
    $check = $pdo->prepare(
        'SELECT 1
         FROM project_images
         WHERE id = :id AND project_id = :project_id
         LIMIT 1'
    );
    $check->execute(['id' => $mediaId, 'project_id' => $projectId]);

    if (!$check->fetchColumn()) {
        return;
    }

    $pdo->prepare(
        'UPDATE project_images
         SET is_primary = (id = :id)
         WHERE project_id = :project_id'
    )->execute([
        'id' => $mediaId,
        'project_id' => $projectId,
    ]);
}