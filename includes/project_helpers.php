<?php
declare(strict_types=1);

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function projectCategories(): array
{
    return [
        'poligrafie' => 'Poligrafie',
        'volum'      => 'Litere & Standuri',
        'posm'       => 'P.O.S.M.',
        'auto'       => 'Branding Auto',
        'laser'      => 'Laser / Plotter',
    ];
}

function projectUploadDir(): string
{
    return dirname(__DIR__) . '/uploads/projects';
}

function ensureProjectUploadDir(): void
{
    $dir = projectUploadDir();

    if (!is_dir($dir)) {
        if (!mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new RuntimeException('Nu s-a putut crea folderul pentru imagini.');
        }
    }
}

function normalizeTags(string $tags): string
{
    $items = array_filter(
        array_map(
            static fn (string $tag): string => trim($tag),
            preg_split('/[,;\n]+/u', $tags) ?: []
        ),
        static fn (string $tag): bool => $tag !== ''
    );

    return implode(', ', array_slice(array_values(array_unique($items)), 0, 20));
}

function validateFocus(int $value): int
{
    return max(0, min(100, $value));
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

    return $stmt->fetchAll();
}

function saveUploadedProjectImages(
    array $files,
    int $projectId,
    PDO $pdo,
    int $maxFiles = 4
): array {
    ensureProjectUploadDir();

    if (!isset($files['name']) || !is_array($files['name'])) {
        return [];
    }

    $countStmt = $pdo->prepare(
        'SELECT COUNT(*) FROM project_images WHERE project_id = :project_id'
    );
    $countStmt->execute(['project_id' => $projectId]);
    $existing = (int) $countStmt->fetchColumn();

    $remaining = max(0, $maxFiles - $existing);

    if ($remaining === 0) {
        return [];
    }

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $saved = [];

    for ($i = 0; $i < count($files['name']) && count($saved) < $remaining; $i++) {
        $error = (int) ($files['error'][$i] ?? UPLOAD_ERR_NO_FILE);

        if ($error === UPLOAD_ERR_NO_FILE) {
            continue;
        }

        if ($error !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Una dintre imagini nu s-a încărcat corect.');
        }

        $tmp = (string) ($files['tmp_name'][$i] ?? '');
        $size = (int) ($files['size'][$i] ?? 0);

        if ($size <= 0 || $size > 8 * 1024 * 1024) {
            throw new RuntimeException('Fiecare imagine trebuie să aibă maximum 8 MB.');
        }

        $mime = $finfo->file($tmp);

        if (!isset($allowed[$mime])) {
            throw new RuntimeException('Sunt acceptate doar JPG, PNG, WEBP și GIF.');
        }

        $filename = sprintf(
            'project-%d-%s.%s',
            $projectId,
            bin2hex(random_bytes(8)),
            $allowed[$mime]
        );

        $target = projectUploadDir() . '/' . $filename;

        if (!move_uploaded_file($tmp, $target)) {
            throw new RuntimeException('Imaginea nu a putut fi salvată.');
        }

        $orderStmt = $pdo->prepare(
            'SELECT COALESCE(MAX(sort_order), -1) + 1
             FROM project_images
             WHERE project_id = :project_id'
        );
        $orderStmt->execute(['project_id' => $projectId]);
        $sortOrder = (int) $orderStmt->fetchColumn();

        $primaryStmt = $pdo->prepare(
            'SELECT COUNT(*)
             FROM project_images
             WHERE project_id = :project_id AND is_primary = 1'
        );
        $primaryStmt->execute(['project_id' => $projectId]);
        $hasPrimary = (int) $primaryStmt->fetchColumn() > 0;

        $insert = $pdo->prepare(
            'INSERT INTO project_images
                (project_id, image_path, alt_text, sort_order, is_primary)
             VALUES
                (:project_id, :image_path, :alt_text, :sort_order, :is_primary)'
        );

        $insert->execute([
            'project_id' => $projectId,
            'image_path' => 'uploads/projects/' . $filename,
            'alt_text'   => null,
            'sort_order' => $sortOrder,
            'is_primary' => $hasPrimary ? 0 : 1,
        ]);

        $saved[] = $filename;
    }

    return $saved;
}

function deleteProjectImageFile(string $imagePath): void
{
    $fullPath = projectUploadDir() . '/' . basename($imagePath);

    if (is_file($fullPath)) {
        @unlink($fullPath);
    }
}

function deleteProjectImageById(int $imageId, int $projectId, PDO $pdo): void
{
    $stmt = $pdo->prepare(
        'SELECT id, image_path, is_primary
         FROM project_images
         WHERE id = :id AND project_id = :project_id
         LIMIT 1'
    );
    $stmt->execute([
        'id' => $imageId,
        'project_id' => $projectId,
    ]);

    $image = $stmt->fetch();

    if (!$image) {
        return;
    }

    deleteProjectImageFile((string) $image['image_path']);

    $delete = $pdo->prepare(
        'DELETE FROM project_images
         WHERE id = :id AND project_id = :project_id'
    );
    $delete->execute([
        'id' => $imageId,
        'project_id' => $projectId,
    ]);

    if ((int) $image['is_primary'] === 1) {
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
}

function setPrimaryProjectImage(int $imageId, int $projectId, PDO $pdo): void
{
    $check = $pdo->prepare(
        'SELECT id
         FROM project_images
         WHERE id = :id AND project_id = :project_id
         LIMIT 1'
    );
    $check->execute([
        'id' => $imageId,
        'project_id' => $projectId,
    ]);

    if (!$check->fetchColumn()) {
        return;
    }

    $pdo->prepare(
        'UPDATE project_images SET is_primary = 0 WHERE project_id = :project_id'
    )->execute(['project_id' => $projectId]);

    $pdo->prepare(
        'UPDATE project_images
         SET is_primary = 1
         WHERE id = :id AND project_id = :project_id'
    )->execute([
        'id' => $imageId,
        'project_id' => $projectId,
    ]);
}