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
    'category' => 'poligrafie',
    'description' => '',
    'tags' => '',
    'is_published' => 1,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['title'] = trim((string) ($_POST['title'] ?? ''));
    $data['category'] = trim((string) ($_POST['category'] ?? ''));
    $data['description'] = trim((string) ($_POST['description'] ?? ''));
    $data['tags'] = normalizeTags((string) ($_POST['tags'] ?? ''));
    $data['is_published'] = isset($_POST['is_published']) ? 1 : 0;

    if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
        $error = 'Sesiunea a expirat. Reîncarcă pagina.';
    } elseif ($data['title'] === '') {
        $error = 'Titlul proiectului este obligatoriu.';
    } elseif (!isset($categories[$data['category']])) {
        $error = 'Categoria selectată nu este validă.';
    } elseif ($data['description'] === '') {
        $error = 'Descrierea este obligatorie.';
    } else {
        try {
            $pdo->beginTransaction();

            $service = $categories[$data['category']];

            $stmt = $pdo->prepare(
                'INSERT INTO projects
                    (title, service, category, description, focus_x, focus_y, tags, sort_order, is_published, published_at)
                 VALUES
                    (:title, :service, :category, :description, 50, 50, :tags, 0, :is_published, :published_at)'
            );

            $stmt->execute([
                'title' => $data['title'],
                'service' => $service,
                'category' => $data['category'],
                'description' => $data['description'],
                'tags' => $data['tags'],
                'is_published' => $data['is_published'],
                'published_at' => $data['is_published'] ? date('Y-m-d H:i:s') : null,
            ]);

            $projectId = (int) $pdo->lastInsertId();

            $display = [
                'x' => $_POST['new_crop_x'] ?? [],
                'y' => $_POST['new_crop_y'] ?? [],
                'zoom' => $_POST['new_crop_zoom'] ?? [],
                'fit' => $_POST['new_fit_mode'] ?? [],
                'rotation' => $_POST['new_rotation'] ?? [],
            ];

            if (isset($_FILES['media'])) {
                saveUploadedProjectMedia($_FILES['media'], $projectId, $pdo, $display, 4);
            }

            $pdo->commit();

            header(
                'Location: project.php?success=' .
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
        <a class="btn btn-ghost" href="project.php">← Proiecte</a>
        <a class="btn btn-ghost" href="logout.php">Ieșire</a>
    </div>
</header>

<main class="container">
    <div class="page-head">
        <div>
            <span class="eyebrow">Proiect nou</span>
            <h1>Adaugă o lucrare</h1>
            <p class="muted">Cel mai nou proiect publicat apare automat primul.</p>
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
                    <input id="title" name="title" value="<?= e($data['title']) ?>" required>
                </div>

                <div class="field">
                    <label for="category">Categorie / Serviciu *</label>
                    <select id="category" name="category" required>
                        <?php foreach ($categories as $key => $label): ?>
                            <option value="<?= e($key) ?>" <?= $data['category'] === $key ? 'selected' : '' ?>>
                                <?= e($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field field-full">
                    <label for="description">Descriere *</label>
                    <textarea id="description" name="description" required><?= e($data['description']) ?></textarea>
                </div>

                <div class="field field-full">
                    <label>Cuvinte cheie</label>

                    <input
                        type="hidden"
                        id="tags"
                        name="tags"
                        value="<?= e($data['tags']) ?>"
                    >

                    <div class="tag-editor">
                        <div class="tag-list" id="tagList"></div>

                        <div class="tag-add-row">
                            <input
                                id="tagInput"
                                type="text"
                                placeholder="Ex: carte de vizită"
                                autocomplete="off"
                            >
                            <button class="btn" type="button" id="tagAdd">Adaugă</button>
                        </div>

                        <div class="tag-count" id="tagCount">0/14</div>
                    </div>

                    <p class="help">
                        Expresiile cu spații rămân întregi. De exemplu:
                        „carte de vizită” devine un singur tag.
                    </p>
                </div>

                <div class="field field-full">
                    <label class="checkbox">
                        <input type="checkbox" name="is_published" value="1" <?= $data['is_published'] ? 'checked' : '' ?>>
                        Publică proiectul pe site
                    </label>
                </div>

                <div class="field field-full">
                    <div class="media-title">
                        <div>
                            <label>Imagini / video</label>
                            <p>Maximum 4 fișiere. După încărcare, apasă „Ajustează”.</p>
                        </div>
                        <span class="muted">4 sloturi</span>
                    </div>

                    <div class="upload-grid">
                        <?php for ($i = 0; $i < 4; $i++): ?>
                            <div class="media-slot" data-media-slot>
                                <div class="slot-empty">
                                    <div>
                                        <strong>+ Fișier <?= $i + 1 ?></strong>
                                        Imagine sau video
                                    </div>
                                </div>

                                <input
                                    type="file"
                                    name="media[<?= $i ?>]"
                                    accept="image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm,video/quicktime"
                                >

                                <input type="hidden" name="new_crop_x[<?= $i ?>]" value="50" data-crop-x>
                                <input type="hidden" name="new_crop_y[<?= $i ?>]" value="50" data-crop-y>
                                <input type="hidden" name="new_crop_zoom[<?= $i ?>]" value="1" data-crop-zoom>
                                <input type="hidden" name="new_fit_mode[<?= $i ?>]" value="cover" data-crop-fit>
                                <input type="hidden" name="new_rotation[<?= $i ?>]" value="0" data-rotation>

                                <div class="slot-actions">
                                    <button type="button" class="js-adjust" style="display:none">Ajustează</button>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button class="btn btn-primary" type="submit">Salvează proiectul</button>
                <a class="btn" href="project.php">Anulează</a>
            </div>
        </form>

        <aside class="preview-panel">
            <section class="preview-box">
                <h3>Previzualizare card</h3>
                <p>Varianta compactă din listă.</p>

                <article class="preview-small">
                    <div class="preview-media" id="smallMedia">
                        <div class="preview-placeholder">Media principală va apărea aici</div>
                    </div>
                    <div class="preview-body">
                        <div class="preview-kicker" id="smallCategory">Categorie</div>
                        <h2 class="preview-title" id="smallTitle">Titlul proiectului</h2>
                        <div class="preview-link">Vezi exemple →</div>
                    </div>
                </article>
            </section>

            <section class="preview-box">
                <h3>Previzualizare modal real</h3>
                <p>Aproape identic cu modul de afișare de pe website.</p>

                <article class="site-modal-preview">
                    <div class="site-modal-left">
                        <div class="site-modal-main" id="siteMain">
                            <div class="preview-placeholder">Media principală va apărea aici</div>
                        </div>
                        <div class="site-modal-thumbs" id="siteThumbs"></div>
                    </div>

                    <aside class="site-modal-side">
                        <div class="site-modal-category" id="siteCategory">Categorie</div>
                        <h2 class="site-modal-title" id="siteTitle">Titlul proiectului</h2>
                        <p class="site-modal-description" id="siteDescription">
                            Descrierea proiectului va apărea aici.
                        </p>
                        <div class="site-modal-tags" id="siteTags"></div>

                        <div class="site-modal-cta">
                            Cere ofertă pentru un proiect similar ↗
                        </div>
                    </aside>
                </article>
            </section>
        </aside>
    </div>
</main>

<div class="crop-modal" id="cropModal">
    <div class="crop-box">
        <div class="crop-head">
            <h3>Ajustează media</h3>
            <button class="btn btn-ghost" type="button" id="cropCancel">Închide</button>
        </div>

        <div class="crop-stage" id="cropStage"></div>

        <p class="crop-tip">
            Trage direct imaginea/video-ul în cadru. Poți regla apoi zoom-ul și rotația.
        </p>

        <div class="crop-controls">
            <div class="fit-switch">
                <button type="button" data-fit="cover" class="active">Umple cadrul</button>
                <button type="button" data-fit="contain">Fișier întreg</button>
            </div>

            <div class="slider-row">
                <span>Zoom</span>
                <input id="cropZoom" type="range" min="1" max="3" step="0.01" value="1">
                <strong id="cropZoomValue">1.00×</strong>
            </div>

            <div class="slider-row">
                <span>Rotire</span>
                <input id="cropRotation" type="range" min="-180" max="180" step="1" value="0">
                <strong id="cropRotationValue">0°</strong>
            </div>
        </div>

        <div class="crop-foot">
            <button class="btn" type="button" id="cropReset">Reset</button>
            <button class="btn btn-primary" type="button" id="cropApply">Aplică</button>
        </div>
    </div>
</div>

<script src="project-form.js?v=5"></script>
</body>
</html>