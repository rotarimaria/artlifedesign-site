<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/project_helpers.php';

requireAdmin();

$categories = projectCategories();
$error = '';

$data = [
    'title' => '',
    'service' => '',
    'category' => 'poligrafie',
    'description' => '',
    'tags' => '',
    'is_published' => 1,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['title'] = trim((string) ($_POST['title'] ?? ''));
    $data['service'] = trim((string) ($_POST['service'] ?? ''));
    $data['category'] = trim((string) ($_POST['category'] ?? ''));
    $data['description'] = trim((string) ($_POST['description'] ?? ''));
    $data['tags'] = normalizeTags((string) ($_POST['tags'] ?? ''));
    $data['is_published'] = isset($_POST['is_published']) ? 1 : 0;

    if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
        $error = 'Sesiunea a expirat. Reîncarcă pagina și încearcă din nou.';
    } elseif ($data['title'] === '') {
        $error = 'Titlul proiectului este obligatoriu.';
    } elseif ($data['service'] === '') {
        $error = 'Serviciul este obligatoriu.';
    } elseif (!isset($categories[$data['category']])) {
        $error = 'Categoria selectată nu este validă.';
    } elseif ($data['description'] === '') {
        $error = 'Descrierea este obligatorie.';
    } else {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                'INSERT INTO projects
                    (
                        title,
                        service,
                        category,
                        description,
                        focus_x,
                        focus_y,
                        tags,
                        sort_order,
                        is_published,
                        published_at
                    )
                 VALUES
                    (
                        :title,
                        :service,
                        :category,
                        :description,
                        50,
                        50,
                        :tags,
                        0,
                        :is_published,
                        :published_at
                    )'
            );

            $stmt->execute([
                'title' => $data['title'],
                'service' => $data['service'],
                'category' => $data['category'],
                'description' => $data['description'],
                'tags' => $data['tags'],
                'is_published' => $data['is_published'],
                'published_at' => $data['is_published'] ? date('Y-m-d H:i:s') : null,
            ]);

            $projectId = (int) $pdo->lastInsertId();

            $displaySettings = [
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
                    $displaySettings,
                    4
                );
            }

            $pdo->commit();

            header(
                'Location: proiecte.php?success=' .
                rawurlencode('Proiectul a fost adăugat cu succes.')
            );
            exit;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $error = 'Proiectul nu a putut fi salvat: ' . $e->getMessage();
        }
    }
}
?>
<!doctype html>
<html lang="ro">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Proiect nou | ArtLife Admin</title>
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
            <span class="eyebrow">Proiect nou</span>
            <h1>Adaugă o lucrare</h1>
            <p class="muted">Ultimul proiect publicat va apărea automat primul.</p>
        </div>
    </div>

    <?php if ($error !== ''): ?>
        <div class="error-box"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="form-layout">
        <form class="card form-card" method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

            <div class="form-grid">
                <div class="field">
                    <label for="title">Titlu proiect *</label>
                    <input id="title" name="title" value="<?= e((string) $data['title']) ?>" required>
                </div>

                <div class="field">
                    <label for="service">Serviciu *</label>
                    <input id="service" name="service" value="<?= e((string) $data['service']) ?>" required>
                </div>

                <div class="field">
                    <label for="category">Categorie *</label>
                    <select id="category" name="category" required>
                        <?php foreach ($categories as $key => $label): ?>
                            <option value="<?= e($key) ?>" <?= $data['category'] === $key ? 'selected' : '' ?>>
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
                            <?= (int) $data['is_published'] === 1 ? 'checked' : '' ?>
                        >
                        Publică proiectul pe site
                    </label>
                </div>

                <div class="field field-full">
                    <label for="description">Descriere *</label>
                    <textarea id="description" name="description" required><?= e((string) $data['description']) ?></textarea>
                </div>

                <div class="field field-full">
                    <label for="tags">Taguri</label>
                    <input id="tags" name="tags" value="<?= e((string) $data['tags']) ?>" placeholder="branding, exterior, lightbox">
                </div>

                <div class="field field-full">
                    <div class="media-section-title">
                        <div>
                            <label>Imagini proiect</label>
                            <p>Apasă pe fiecare cartonaș și alege imaginea.</p>
                        </div>
                        <span class="muted">maximum 4</span>
                    </div>

                    <div class="upload-grid">
                        <?php for ($i = 0; $i < 4; $i++): ?>
                            <div class="upload-slot" data-crop-target>
                                <div class="slot-empty">
                                    <div>
                                        <strong>+ Imagine <?= $i + 1 ?></strong>
                                        Apasă pentru a încărca
                                    </div>
                                </div>

                                <input
                                    type="file"
                                    name="images[<?= $i ?>]"
                                    accept="image/jpeg,image/png,image/webp,image/gif"
                                    aria-label="Imagine <?= $i + 1 ?>"
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

                    <p class="help">
                        JPG, PNG, WEBP sau GIF. Maximum 8 MB per imagine.
                        Prima imagine încărcată devine imagine principală.
                    </p>
                </div>
            </div>

            <div class="form-actions">
                <button class="btn btn-primary" type="submit">Salvează proiectul</button>
                <a class="btn" href="proiecte.php">Anulează</a>
            </div>
        </form>

        <aside class="preview-panel">
            <h3>Previzualizare card</h3>
            <p>Așa va arăta aproximativ cartonașul în portofoliu.</p>

            <article class="live-card">
                <div class="live-card-media" id="liveMedia">
                    <div class="live-card-placeholder">Imaginea principală va apărea aici</div>
                </div>
                <div class="live-card-body">
                    <div class="live-card-kicker" id="liveService">Serviciu</div>
                    <h2 class="live-card-title" id="liveTitle">Titlul proiectului</h2>
                    <div class="live-card-link">Vezi exemple →</div>
                </div>
            </article>
        </aside>
    </div>
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