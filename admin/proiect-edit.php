<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/project_helpers.php';

requireAdmin();

$categories = projectCategories();
$projectId = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

if ($projectId <= 0) {
    http_response_code(400);
    exit('ID proiect invalid.');
}

$stmt = $pdo->prepare('SELECT * FROM projects WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $projectId]);
$project = $stmt->fetch();

if (!$project) {
    http_response_code(404);
    exit('Proiectul nu a fost găsit.');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
        $error = 'Sesiunea a expirat. Reîncarcă pagina și încearcă din nou.';
    } else {
        $title = trim((string) ($_POST['title'] ?? ''));
        $service = trim((string) ($_POST['service'] ?? ''));
        $category = trim((string) ($_POST['category'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $tags = normalizeTags((string) ($_POST['tags'] ?? ''));
        $isPublished = isset($_POST['is_published']) ? 1 : 0;

        if ($title === '') {
            $error = 'Titlul proiectului este obligatoriu.';
        } elseif ($service === '') {
            $error = 'Serviciul este obligatoriu.';
        } elseif (!isset($categories[$category])) {
            $error = 'Categoria selectată nu este validă.';
        } elseif ($description === '') {
            $error = 'Descrierea este obligatorie.';
        } else {
            try {
                $pdo->beginTransaction();

                $publishedAt = $project['published_at'];

                if ($isPublished === 1 && empty($publishedAt)) {
                    $publishedAt = date('Y-m-d H:i:s');
                }

                if ($isPublished === 0) {
                    $publishedAt = null;
                }

                $update = $pdo->prepare(
                    'UPDATE projects
                     SET
                        title = :title,
                        service = :service,
                        category = :category,
                        description = :description,
                        tags = :tags,
                        is_published = :is_published,
                        published_at = :published_at
                     WHERE id = :id'
                );

                $update->execute([
                    'title' => $title,
                    'service' => $service,
                    'category' => $category,
                    'description' => $description,
                    'tags' => $tags,
                    'is_published' => $isPublished,
                    'published_at' => $publishedAt,
                    'id' => $projectId,
                ]);

                $existingDisplay = [
                    'x' => $_POST['existing_crop_x'] ?? [],
                    'y' => $_POST['existing_crop_y'] ?? [],
                    'zoom' => $_POST['existing_crop_zoom'] ?? [],
                    'fit' => $_POST['existing_fit_mode'] ?? [],
                ];
                updateExistingProjectImageDisplays(
                    $projectId,
                    $pdo,
                    $existingDisplay
                );

                $deleteIds = $_POST['delete_image'] ?? [];

                if (is_array($deleteIds)) {
                    foreach ($deleteIds as $deleteId) {
                        deleteProjectImageById(
                            (int) $deleteId,
                            $projectId,
                            $pdo
                        );
                    }
                }

                $primaryImageId = (int) ($_POST['primary_image_id'] ?? 0);

                if ($primaryImageId > 0) {
                    setPrimaryProjectImage(
                        $primaryImageId,
                        $projectId,
                        $pdo
                    );
                }

                $newDisplay = [
                    'x' => $_POST['new_crop_x'] ?? [],
                    'y' => $_POST['new_crop_y'] ?? [],
                    'zoom' => $_POST['new_crop_zoom'] ?? [],
                    'fit' => $_POST['new_fit_mode'] ?? [],
                ];

                if (isset($_FILES['images'])) {
                    saveUploadedProjectImages(
                        $_FILES['images'],
                        $projectId,
                        $pdo,
                        $newDisplay,
                        4
                    );
                }

                $pdo->commit();

                header(
                    'Location: proiect-edit.php?id=' .
                    $projectId .
                    '&success=' .
                    rawurlencode('Modificările au fost salvate.')
                );
                exit;
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                $error = 'Modificările nu au putut fi salvate: ' . $e->getMessage();
            }
        }
    }
}

$stmt->execute(['id' => $projectId]);
$project = $stmt->fetch();
$images = getProjectImages($projectId, $pdo);
$remainingSlots = max(0, 4 - count($images));

if (isset($_GET['success'])) {
    $success = trim((string) $_GET['success']);
}
?>
<!doctype html>
<html lang="ro">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Editare proiect | ArtLife Admin</title>
    <?php require __DIR__ . '/_admin_styles.php'; ?>
</head>
<body>
<header class="topbar">
    <a class="brand" href="index.php">ArtLife <span>Admin</span></a>
    <div class="top-actions">
        <a class="btn btn-ghost" href="proiecte.php">← Proiecte</a>
        <a class="btn btn-ghost" href="logout.php">Ieșire</a>
    </div>
</header>

<main class="container">
    <div class="page-head">
        <div>
            <span class="eyebrow">Editare proiect</span>
            <h1><?= e((string) $project['title']) ?></h1>
            <p class="muted">Modifică informațiile, imaginile și încadrarea lor.</p>
        </div>
    </div>

    <?php if ($success !== ''): ?>
        <div class="notice"><?= e($success) ?></div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="error-box"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="form-layout">
        <form class="card form-card" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $projectId ?>">
            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

            <div class="form-grid">
                <div class="field">
                    <label for="title">Titlu proiect *</label>
                    <input id="title" name="title" value="<?= e((string) $project['title']) ?>" required>
                </div>

                <div class="field">
                    <label for="service">Serviciu *</label>
                    <input id="service" name="service" value="<?= e((string) $project['service']) ?>" required>
                </div>

                <div class="field">
                    <label for="category">Categorie *</label>
                    <select id="category" name="category" required>
                        <?php foreach ($categories as $key => $label): ?>
                            <option value="<?= e($key) ?>" <?= $project['category'] === $key ? 'selected' : '' ?>>
                                <?= e($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field">
                    <label>Publicare</label>
                    <label class="checkbox">
                        <input
                            type="checkbox"
                            name="is_published"
                            value="1"
                            <?= (int) $project['is_published'] === 1 ? 'checked' : '' ?>
                        >
                        Publică proiectul pe site
                    </label>
                </div>

                <div class="field field-full">
                    <label for="description">Descriere *</label>
                    <textarea id="description" name="description" required><?= e((string) $project['description']) ?></textarea>
                </div>

                <div class="field field-full">
                    <label for="tags">Taguri</label>
                    <input id="tags" name="tags" value="<?= e((string) ($project['tags'] ?? '')) ?>">
                </div>

                <div class="field field-full">
                    <div class="media-section-title">
                        <div>
                            <label>Imagini proiect</label>
                            <p>Alege imaginea principală și ajustează fiecare imagine vizual.</p>
                        </div>
                        <span class="muted"><?= count($images) ?>/4</span>
                    </div>

                    <div class="upload-grid">
                        <?php foreach ($images as $image): ?>
                            <div class="existing-slot" data-crop-target>
                                <div class="slot-preview">
                                    <img
                                        src="../<?= e((string) $image['image_path']) ?>"
                                        alt=""
                                        data-crop-image
                                        style="
                                            --crop-x:<?= (float) ($image['crop_x'] ?? 50) ?>%;
                                            --crop-y:<?= (float) ($image['crop_y'] ?? 50) ?>%;
                                            --zoom:<?= (float) ($image['crop_zoom'] ?? 1) ?>;
                                            --fit:<?= e((string) ($image['fit_mode'] ?? 'cover')) ?>;
                                        "
                                    >
                                </div>

                                <label class="primary-wrap">
                                    <input
                                        type="radio"
                                        name="primary_image_id"
                                        value="<?= (int) $image['id'] ?>"
                                        <?= (int) $image['is_primary'] === 1 ? 'checked' : '' ?>
                                    >
                                    Principală
                                </label>

                                <label class="delete-wrap">
                                    <input
                                        type="checkbox"
                                        name="delete_image[]"
                                        value="<?= (int) $image['id'] ?>"
                                    >
                                    Șterge
                                </label>

                                <input
                                    type="hidden"
                                    name="existing_crop_x[<?= (int) $image['id'] ?>]"
                                    value="<?= e((string) ($image['crop_x'] ?? 50)) ?>"
                                    data-crop-x
                                >
                                <input
                                    type="hidden"
                                    name="existing_crop_y[<?= (int) $image['id'] ?>]"
                                    value="<?= e((string) ($image['crop_y'] ?? 50)) ?>"
                                    data-crop-y
                                >
                                <input
                                    type="hidden"
                                    name="existing_crop_zoom[<?= (int) $image['id'] ?>]"
                                    value="<?= e((string) ($image['crop_zoom'] ?? 1)) ?>"
                                    data-crop-zoom
                                >
                                <input
                                    type="hidden"
                                    name="existing_fit_mode[<?= (int) $image['id'] ?>]"
                                    value="<?= e((string) ($image['fit_mode'] ?? 'cover')) ?>"
                                    data-crop-fit
                                >

                                <div class="slot-actions">
                                    <button type="button" class="js-crop-open">Ajustează</button>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <?php for ($i = 0; $i < $remainingSlots; $i++): ?>
                            <div class="upload-slot" data-crop-target>
                                <div class="slot-empty">
                                    <div>
                                        <strong>+ Imagine nouă</strong>
                                        Apasă pentru a încărca
                                    </div>
                                </div>

                                <input
                                    type="file"
                                    name="images[<?= $i ?>]"
                                    accept="image/jpeg,image/png,image/webp,image/gif"
                                >

                                <input type="hidden" name="new_crop_x[<?= $i ?>]" value="50" data-crop-x>
                                <input type="hidden" name="new_crop_y[<?= $i ?>]" value="50" data-crop-y>
                                <input type="hidden" name="new_crop_zoom[<?= $i ?>]" value="1" data-crop-zoom>
                                <input type="hidden" name="new_fit_mode[<?= $i ?>]" value="cover" data-crop-fit>

                                <div class="slot-actions">
                                    <button
                                        type="button"
                                        class="js-crop-open"
                                        style="display:none"
                                    >
                                        Ajustează
                                    </button>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button class="btn btn-primary" type="submit">Salvează modificările</button>
                <a class="btn" href="proiecte.php">Înapoi</a>
            </div>
        </form>

        <aside class="preview-panel">
            <h3>Previzualizare card</h3>
            <p>Previzualizarea folosește imaginea principală selectată.</p>

            <article class="live-card">
                <div class="live-card-media" id="liveMedia">
                    <div class="live-card-placeholder">Imaginea principală va apărea aici</div>
                </div>
                <div class="live-card-body">
                    <div class="live-card-kicker" id="liveService"><?= e((string) $project['service']) ?></div>
                    <h2 class="live-card-title" id="liveTitle"><?= e((string) $project['title']) ?></h2>
                    <div class="live-card-link">Vezi exemple →</div>
                </div>
            </article>
        </aside>
    </div>

    <section class="danger-zone">
        <strong style="color:#ff9a9a">Ștergere proiect</strong>
        <p class="muted">Ștergerea elimină proiectul și toate fotografiile lui.</p>
        <a class="btn btn-danger" href="proiect-sterge.php?id=<?= $projectId ?>">Șterge proiectul</a>
    </section>
</main>

<div class="crop-modal" id="cropModal" aria-hidden="true">
    <div class="crop-box">
        <div class="crop-head">
            <h3>Ajustează imaginea în cartonaș</h3>
            <button class="btn btn-ghost" type="button" id="cropCancel">Închide</button>
        </div>

        <div class="crop-stage" id="cropStage">
            <img id="cropStageImg" alt="">
        </div>

        <p class="crop-tip">Trage imaginea în direcția dorită și reglează mărirea.</p>

        <div class="crop-controls">
            <div class="fit-switch">
                <button type="button" data-fit="cover" class="active">Umple cartonașul</button>
                <button type="button" data-fit="contain">Imagine întreagă</button>
            </div>

            <div class="zoom-row">
                <span>Zoom</span>
                <input id="cropZoom" type="range" min="1" max="2.5" step="0.01" value="1">
                <strong id="cropZoomValue">1.00×</strong>
            </div>
        </div>

        <div class="crop-foot">
            <button class="btn" type="button" id="cropReset">Reset</button>
            <button class="btn btn-primary" type="button" id="cropApply">Aplică</button>
        </div>
    </div>
</div>

<script src="project-form.js"></script>
</body>
</html>