<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/project_helpers.php';

requireAdmin();

$categories = projectCategories();
$q = trim((string) ($_GET['q'] ?? ''));
$category = trim((string) ($_GET['category'] ?? ''));

$where = [];
$params = [];

if ($q !== '') {
    $where[] = '(p.title LIKE :q OR p.description LIKE :q)';
    $params['q'] = '%' . $q . '%';
}

if ($category !== '' && isset($categories[$category])) {
    $where[] = 'p.category = :category';
    $params['category'] = $category;
}

$sql = '
    SELECT
        p.*,
        (
            SELECT pi.image_path
            FROM project_images pi
            WHERE pi.project_id = p.id
            ORDER BY pi.is_primary DESC, pi.sort_order ASC, pi.id ASC
            LIMIT 1
        ) AS primary_media,
        (
            SELECT pi.media_type
            FROM project_images pi
            WHERE pi.project_id = p.id
            ORDER BY pi.is_primary DESC, pi.sort_order ASC, pi.id ASC
            LIMIT 1
        ) AS media_type,
        (
            SELECT pi.crop_x
            FROM project_images pi
            WHERE pi.project_id = p.id
            ORDER BY pi.is_primary DESC, pi.sort_order ASC, pi.id ASC
            LIMIT 1
        ) AS crop_x,
        (
            SELECT pi.crop_y
            FROM project_images pi
            WHERE pi.project_id = p.id
            ORDER BY pi.is_primary DESC, pi.sort_order ASC, pi.id ASC
            LIMIT 1
        ) AS crop_y,
        (
            SELECT pi.crop_zoom
            FROM project_images pi
            WHERE pi.project_id = p.id
            ORDER BY pi.is_primary DESC, pi.sort_order ASC, pi.id ASC
            LIMIT 1
        ) AS crop_zoom,
        (
            SELECT pi.fit_mode
            FROM project_images pi
            WHERE pi.project_id = p.id
            ORDER BY pi.is_primary DESC, pi.sort_order ASC, pi.id ASC
            LIMIT 1
        ) AS fit_mode,
        (
            SELECT pi.rotation
            FROM project_images pi
            WHERE pi.project_id = p.id
            ORDER BY pi.is_primary DESC, pi.sort_order ASC, pi.id ASC
            LIMIT 1
        ) AS rotation,
        (
            SELECT COUNT(*)
            FROM project_images pi2
            WHERE pi2.project_id = p.id
        ) AS media_count
    FROM projects p
';

if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}

$sql .= '
    ORDER BY COALESCE(p.published_at, p.created_at) DESC, p.id DESC
';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$projects = $stmt->fetchAll();

$success = trim((string) ($_GET['success'] ?? ''));
?>
<!doctype html>
<html lang="ro">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Proiecte | ArtLife Admin</title>
    <?php require __DIR__ . '/_admin_styles.php'; ?>
</head>
<body>
<header class="topbar">
    <a class="brand" href="index.php">ArtLife <span>Admin</span></a>
    <div class="top-actions">
        <small><?= e((string) ($_SESSION['admin_email'] ?? '')) ?></small>
        <a class="btn btn-ghost" href="index.php">Dashboard</a>
        <a class="btn btn-ghost" href="logout.php">Ieșire</a>
    </div>
</header>

<main class="container">
    <div class="page-head">
        <div>
            <span class="eyebrow">Portofoliu</span>
            <h1>Lucrări / Carduri</h1>
            <p class="muted">Ultimul proiect publicat apare automat primul.</p>
        </div>

        <a class="btn btn-primary" href="project-nou.php">+ Adaugă proiect</a>
    </div>

    <?php if ($success !== ''): ?>
        <div class="notice"><?= e($success) ?></div>
    <?php endif; ?>

    <div class="toolbar">
        <form method="get">
            <input class="search" type="search" name="q" value="<?= e($q) ?>" placeholder="Caută proiect...">

            <select name="category" style="width:auto;min-width:190px">
                <option value="">Toate categoriile</option>
                <?php foreach ($categories as $key => $label): ?>
                    <option value="<?= e($key) ?>" <?= $category === $key ? 'selected' : '' ?>>
                        <?= e($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button class="btn" type="submit">Filtrează</button>

            <?php if ($q !== '' || $category !== ''): ?>
                <a class="btn btn-ghost" href="project.php">Resetează</a>
            <?php endif; ?>
        </form>

        <span class="muted"><?= count($projects) ?> proiect(e)</span>
    </div>

    <section class="card table-wrap">
        <?php if (!$projects): ?>
            <div class="empty">Nu există proiecte.</div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Media</th>
                        <th>Proiect</th>
                        <th>Categorie / Serviciu</th>
                        <th>Fișiere</th>
                        <th>Status</th>
                        <th>Acțiuni</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($projects as $project): ?>
                    <tr>
                        <td>
                            <?php if (!empty($project['primary_media'])): ?>
                                <div class="table-media">
                                    <?php if (($project['media_type'] ?? 'image') === 'video'): ?>
                                        <video
                                            src="../<?= e((string) $project['primary_media']) ?>"
                                            muted
                                            loop
                                            autoplay
                                            playsinline
                                            style="
                                                --crop-x:<?= (float) ($project['crop_x'] ?? 50) ?>%;
                                                --crop-y:<?= (float) ($project['crop_y'] ?? 50) ?>%;
                                                --zoom:<?= (float) ($project['crop_zoom'] ?? 1) ?>;
                                                --fit:<?= e((string) ($project['fit_mode'] ?? 'cover')) ?>;
                                                --rotation:<?= (int) ($project['rotation'] ?? 0) ?>deg;
                                            "
                                        ></video>
                                    <?php else: ?>
                                        <img
                                            src="../<?= e((string) $project['primary_media']) ?>"
                                            alt=""
                                            style="
                                                --crop-x:<?= (float) ($project['crop_x'] ?? 50) ?>%;
                                                --crop-y:<?= (float) ($project['crop_y'] ?? 50) ?>%;
                                                --zoom:<?= (float) ($project['crop_zoom'] ?? 1) ?>;
                                                --fit:<?= e((string) ($project['fit_mode'] ?? 'cover')) ?>;
                                                --rotation:<?= (int) ($project['rotation'] ?? 0) ?>deg;
                                            "
                                        >
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="no-thumb">Fără media</div>
                            <?php endif; ?>
                        </td>

                        <td class="title-cell">
                            <strong><?= e((string) $project['title']) ?></strong>
                            <span><?= e((string) ($project['description'] ?? '')) ?></span>
                        </td>

                        <td><?= e($categories[$project['category']] ?? (string) $project['category']) ?></td>
                        <td><?= (int) $project['media_count'] ?>/4</td>

                        <td>
                            <?php if ((int) $project['is_published'] === 1): ?>
                                <span class="badge badge-green">Publicat</span>
                            <?php else: ?>
                                <span class="badge">Ciornă</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <div class="row-actions">
                                <a class="btn" href="project-edit.php?id=<?= (int) $project['id'] ?>">Editează</a>
                                <a class="btn btn-danger" href="project-sterge.php?id=<?= (int) $project['id'] ?>">Șterge</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
</main>
</body>
</html>