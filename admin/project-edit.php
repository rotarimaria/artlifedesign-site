<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/project_helpers.php';

requireAdmin();

$categories = projectCategories($pdo);
$projectId = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

if ($projectId <= 0) {
    http_response_code(400);
    exit('ID proiect invalid.');
}

$stmt = $pdo->prepare('SELECT * FROM projects WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $projectId]);
$project = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$project) {
    http_response_code(404);
    exit('Proiectul nu a fost găsit.');
}

$error = '';
$success = trim((string) ($_GET['success'] ?? ''));

// Se iau ajustările trimise de formular.
function postedDisplay(string $prefix): array
{
    return [
        'x' => $_POST["{$prefix}_crop_x"] ?? [],
        'y' => $_POST["{$prefix}_crop_y"] ?? [],
        'zoom' => $_POST["{$prefix}_crop_zoom"] ?? [],
        'fit' => $_POST["{$prefix}_fit_mode"] ?? [],
        'rotation' => $_POST["{$prefix}_rotation"] ?? [],
    ];
}

// Se validează și se salvează modificările proiectului.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
        $error = 'Sesiunea a expirat. Reîncarcă pagina.';
    } else {
        $title = trim((string) ($_POST['title'] ?? ''));
        $category = trim((string) ($_POST['category'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $tags = normalizeTags((string) ($_POST['tags'] ?? ''));
        $isPublished = isset($_POST['is_published']) ? 1 : 0;

        if ($title === '') {
            $error = 'Titlul proiectului este obligatoriu.';
        } elseif (!isset($categories[$category])) {
            $error = 'Categoria selectată nu este validă.';
        } elseif ($description === '') {
            $error = 'Descrierea este obligatorie.';
        } else {
            try {
                $pdo->beginTransaction();

                $publishedAt = $project['published_at'];

                if ($isPublished && !$publishedAt) {
                    $publishedAt = date('Y-m-d H:i:s');
                } elseif (!$isPublished) {
                    $publishedAt = null;
                }

                $pdo->prepare(
                    'UPDATE projects
                     SET title = :title,
                         service = :service,
                         category = :category,
                         description = :description,
                         tags = :tags,
                         is_published = :is_published,
                         published_at = :published_at
                     WHERE id = :id'
                )->execute([
                    'title' => $title,
                    'service' => $categories[$category],
                    'category' => $category,
                    'description' => $description,
                    'tags' => $tags,
                    'is_published' => $isPublished,
                    'published_at' => $publishedAt,
                    'id' => $projectId,
                ]);

                // Se șterg fișierele marcate.
                foreach ((array) ($_POST['delete_media'] ?? []) as $mediaId) {
                    $mediaId = (int) $mediaId;
                    if ($mediaId > 0) {
                        deleteProjectImageById($mediaId, $projectId, $pdo);
                    }
                }

                // Se salvează separat ajustările pentru modal și card.
                updateExistingProjectMediaDisplays(
                    $projectId,
                    $pdo,
                    postedDisplay('existing'),
                    postedDisplay('existing_card')
                );

                // Se înlocuiește fișierul în același slot.
                if (isset($_FILES['replace_media']['name']) && is_array($_FILES['replace_media']['name'])) {
                    foreach ($_FILES['replace_media']['name'] as $mediaId => $name) {
                        replaceProjectMedia(
                            (int) $mediaId,
                            $projectId,
                            [
                                'name' => $name,
                                'type' => $_FILES['replace_media']['type'][$mediaId] ?? '',
                                'tmp_name' => $_FILES['replace_media']['tmp_name'][$mediaId] ?? '',
                                'error' => $_FILES['replace_media']['error'][$mediaId] ?? UPLOAD_ERR_NO_FILE,
                                'size' => $_FILES['replace_media']['size'][$mediaId] ?? 0,
                            ],
                            $pdo
                        );
                    }
                }

                // Se setează media principală existentă.
                $primaryId = (int) ($_POST['primary_image_id'] ?? 0);

                if ($primaryId > 0) {
                    setPrimaryProjectImage($primaryId, $projectId, $pdo);
                }

                // Se salvează fișierele noi.
                $savedMedia = [];

                if (isset($_FILES['media'])) {
                    $savedMedia = saveUploadedProjectMedia(
                        $_FILES['media'],
                        $projectId,
                        $pdo,
                        postedDisplay('new'),
                        4,
                        postedDisplay('new_card')
                    );
                }

                $newPrimaryIndex = $_POST['new_primary_index'] ?? '';

                if ($newPrimaryIndex !== '' && isset($savedMedia[(int) $newPrimaryIndex]['id'])) {
                    setPrimaryProjectImage(
                        (int) $savedMedia[(int) $newPrimaryIndex]['id'],
                        $projectId,
                        $pdo
                    );
                }

                $pdo->commit();

                header(
                    'Location: project-edit.php?id=' . $projectId .
                    '&success=' . rawurlencode('Modificările au fost salvate.')
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
$project = $stmt->fetch(PDO::FETCH_ASSOC);
$mediaItems = getProjectImages($projectId, $pdo);
$remaining = max(0, 4 - count($mediaItems));
?>
<!doctype html>
<html lang="ro">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Editare proiect | ArtLife Admin</title>
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
            <span class="eyebrow">Editare proiect</span>
            <h1><?= e((string) $project['title']) ?></h1>
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
            <input type="hidden" name="new_primary_index" id="newPrimaryIndex" value="">

            <div class="form-grid">
                <div class="field">
                    <label for="title">Titlu proiect *</label>
                    <input id="title" name="title" value="<?= e((string) $project['title']) ?>" required>
                </div>

                <div class="field">
                    <label for="category">Categorie / Serviciu *</label>
                    <select id="category" name="category" required>
                        <?php foreach ($categories as $key => $label): ?>
                            <option value="<?= e($key) ?>" <?= $project['category'] === $key ? 'selected' : '' ?>>
                                <?= e($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field field-full">
                    <label for="description">Descriere *</label>
                    <textarea id="description" name="description" required><?= e((string) $project['description']) ?></textarea>
                </div>

                <div class="field field-full">
                    <label>Cuvinte cheie</label>
                    <input type="hidden" id="tags" name="tags" value="<?= e((string) ($project['tags'] ?? '')) ?>">

                    <div class="tag-editor">
                        <div class="tag-list" id="tagList"></div>

                        <div class="tag-add-row">
                            <input id="tagInput" type="text" placeholder="Ex: carte de vizită" autocomplete="off">
                            <button class="btn" type="button" id="tagAdd">Adaugă</button>
                        </div>

                        <div class="tag-count" id="tagCount">0/14</div>
                    </div>
                </div>

                <div class="field field-full">
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
                    <div class="media-title">
                        <label>Imagini / video</label>
                        <span class="muted"><?= count($mediaItems) ?>/4</span>
                    </div>

                    <div class="upload-grid">
                        <?php foreach ($mediaItems as $media): ?>
                            <?php
                            $id = (int) $media['id'];
                            $detailFit = (string) ($media['fit_mode'] ?? 'contain');
                            $cardX = $media['card_crop_x'] ?? $media['crop_x'] ?? 50;
                            $cardY = $media['card_crop_y'] ?? $media['crop_y'] ?? 50;
                            $cardZoom = $media['card_crop_zoom'] ?? $media['crop_zoom'] ?? 1;
                            $cardFit = $media['card_fit_mode'] ?? $detailFit;
                            $cardRotation = $media['card_rotation'] ?? $media['rotation'] ?? 0;
                            ?>
                            <div class="media-slot has-media" data-media-slot>
                                <div class="slot-preview">
                                    <?php if (($media['media_type'] ?? 'image') === 'video'): ?>
                                        <video
                                            src="../<?= e((string) $media['image_path']) ?>"
                                            muted loop autoplay playsinline
                                            data-media-preview
                                            style="
                                                --crop-x:<?= (float) ($media['crop_x'] ?? 50) ?>%;
                                                --crop-y:<?= (float) ($media['crop_y'] ?? 50) ?>%;
                                                --zoom:<?= (float) ($media['crop_zoom'] ?? 1) ?>;
                                                --fit:<?= e($detailFit) ?>;
                                                --rotation:<?= (int) ($media['rotation'] ?? 0) ?>deg;
                                            "
                                        ></video>
                                    <?php else: ?>
                                        <img
                                            src="../<?= e((string) $media['image_path']) ?>"
                                            alt=""
                                            data-media-preview
                                            style="
                                                --crop-x:<?= (float) ($media['crop_x'] ?? 50) ?>%;
                                                --crop-y:<?= (float) ($media['crop_y'] ?? 50) ?>%;
                                                --zoom:<?= (float) ($media['crop_zoom'] ?? 1) ?>;
                                                --fit:<?= e($detailFit) ?>;
                                                --rotation:<?= (int) ($media['rotation'] ?? 0) ?>deg;
                                            "
                                        >
                                    <?php endif; ?>
                                </div>

                                <label class="primary-wrap">
                                    <input
                                        type="radio"
                                        name="primary_image_id"
                                        value="<?= $id ?>"
                                        <?= (int) $media['is_primary'] === 1 ? 'checked' : '' ?>
                                    >
                                    Principal
                                </label>

                                <label class="replace-wrap">
                                    Schimbă imaginea
                                    <input
                                        type="file"
                                        name="replace_media[<?= $id ?>]"
                                        accept="image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm,video/quicktime"
                                        class="js-replace-media"
                                    >
                                </label>

                                <!-- Ajustarea pentru modalul mare -->
                                <input type="hidden" name="existing_crop_x[<?= $id ?>]" value="<?= e((string) ($media['crop_x'] ?? 50)) ?>" data-detail-x>
                                <input type="hidden" name="existing_crop_y[<?= $id ?>]" value="<?= e((string) ($media['crop_y'] ?? 50)) ?>" data-detail-y>
                                <input type="hidden" name="existing_crop_zoom[<?= $id ?>]" value="<?= e((string) ($media['crop_zoom'] ?? 1)) ?>" data-detail-zoom>
                                <input type="hidden" name="existing_fit_mode[<?= $id ?>]" value="<?= e($detailFit) ?>" data-detail-fit>
                                <input type="hidden" name="existing_rotation[<?= $id ?>]" value="<?= (int) ($media['rotation'] ?? 0) ?>" data-detail-rotation>

                                <!-- Ajustarea pentru cardul mic -->
                                <input type="hidden" name="existing_card_crop_x[<?= $id ?>]" value="<?= e((string) $cardX) ?>" data-card-x>
                                <input type="hidden" name="existing_card_crop_y[<?= $id ?>]" value="<?= e((string) $cardY) ?>" data-card-y>
                                <input type="hidden" name="existing_card_crop_zoom[<?= $id ?>]" value="<?= e((string) $cardZoom) ?>" data-card-zoom>
                                <input type="hidden" name="existing_card_fit_mode[<?= $id ?>]" value="<?= e((string) $cardFit) ?>" data-card-fit>
                                <input type="hidden" name="existing_card_rotation[<?= $id ?>]" value="<?= (int) $cardRotation ?>" data-card-rotation>

                                <input
                                    type="hidden"
                                    name="delete_media[]"
                                    value=""
                                    data-delete-existing
                                    data-media-id="<?= $id ?>"
                                >

                                <div class="delete-wrap">
                                    <button type="button" class="js-delete-existing">Șterge</button>
                                </div>

                                <div class="slot-actions">
                                    <button type="button" class="js-adjust" data-adjust-mode="card">Card mic</button>
                                    <button type="button" class="js-adjust" data-adjust-mode="detail">Modal mare</button>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <?php for ($i = 0; $i < $remaining; $i++): ?>
                            <div class="media-slot" data-media-slot>
                                <div class="slot-empty">
                                    <div>
                                        <strong>+ Fișier nou</strong>
                                        Imagine sau video
                                    </div>
                                </div>

                                <input
                                    type="file"
                                    name="media[<?= $i ?>]"
                                    accept="image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm,video/quicktime"
                                >

                                <!-- Ajustarea pentru modalul mare -->
                                <input type="hidden" name="new_crop_x[<?= $i ?>]" value="50" data-detail-x>
                                <input type="hidden" name="new_crop_y[<?= $i ?>]" value="50" data-detail-y>
                                <input type="hidden" name="new_crop_zoom[<?= $i ?>]" value="1" data-detail-zoom>
                                <input type="hidden" name="new_fit_mode[<?= $i ?>]" value="contain" data-detail-fit>
                                <input type="hidden" name="new_rotation[<?= $i ?>]" value="0" data-detail-rotation>

                                <!-- Ajustarea pentru cardul mic -->
                                <input type="hidden" name="new_card_crop_x[<?= $i ?>]" value="50" data-card-x>
                                <input type="hidden" name="new_card_crop_y[<?= $i ?>]" value="50" data-card-y>
                                <input type="hidden" name="new_card_crop_zoom[<?= $i ?>]" value="1" data-card-zoom>
                                <input type="hidden" name="new_card_fit_mode[<?= $i ?>]" value="contain" data-card-fit>
                                <input type="hidden" name="new_card_rotation[<?= $i ?>]" value="0" data-card-rotation>

                                <label class="primary-wrap new-primary-wrap new-media-control" style="display:none">
                                    <input type="radio" class="js-new-primary" value="<?= $i ?>">
                                    Principal
                                </label>

                                <div class="delete-wrap new-delete-wrap new-media-control" style="display:none">
                                    <button type="button" class="js-remove-new">Șterge</button>
                                </div>

                                <div class="slot-actions">
                                    <button type="button" class="js-adjust new-media-control" data-adjust-mode="card" style="display:none">
                                        Card mic
                                    </button>
                                    <button type="button" class="js-adjust new-media-control" data-adjust-mode="detail" style="display:none">
                                        Modal mare
                                    </button>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button class="btn btn-primary" type="submit">Salvează modificările</button>
                <a class="btn" href="project.php">Înapoi</a>
            </div>
        </form>

        <aside class="preview-panel">
            <section class="preview-box">
                <h3>Previzualizare card</h3>

                <article class="preview-small">
                    <div class="preview-media" id="smallMedia"></div>
                    <div class="preview-body">
                        <div class="preview-kicker" id="smallCategory"></div>
                        <h2 class="preview-title" id="smallTitle"></h2>
                        <div class="preview-link">Vezi exemple →</div>
                    </div>
                </article>
            </section>

            <section class="preview-box">
                <h3>Previzualizare modal real</h3>

                <article class="site-modal-preview">
                    <div class="site-modal-left">
                        <div class="site-modal-main" id="siteMain"></div>
                        <div class="site-modal-thumbs" id="siteThumbs"></div>
                    </div>

                    <aside class="site-modal-side">
                        <div class="site-modal-category" id="siteCategory"></div>
                        <h2 class="site-modal-title" id="siteTitle"></h2>
                        <p class="site-modal-description" id="siteDescription"></p>
                        <div class="site-modal-tags" id="siteTags"></div>
                        <div class="site-modal-cta">Cere ofertă pentru un proiect similar ↗</div>
                    </aside>
                </article>
            </section>
        </aside>
    </div>

    <section class="danger-zone">
        <h3 style="margin-top:0;color:#ff9a9a;font-weight:500;">Ștergere proiect</h3>
        <p class="muted">Ștergerea elimină proiectul și toate fișierele lui.</p>
        <a class="btn btn-danger" href="project-sterge.php?id=<?= $projectId ?>">Șterge proiectul</a>
    </section>
</main>

<div class="crop-modal" id="cropModal">
    <div class="crop-box">
        <div class="crop-head">
            <h3 id="cropTitle">Ajustează media</h3>
            <button class="btn btn-ghost" type="button" id="cropCancel">Închide</button>
        </div>

        <div class="crop-stage" id="cropStage"></div>

        <div class="crop-controls">
            <div class="fit-switch">
                <button type="button" data-fit="cover">Umple cadrul</button>
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
            <div class="crop-foot-actions">
                <button class="btn" type="button" id="cropReset">Reset</button>
                <button class="btn" type="button" id="cropApply">Aplică</button>
                <button class="btn btn-primary" type="button" id="cropSave">Aplică și salvează</button>
            </div>
        </div>
    </div>
</div>

<script src="project-form.js?v=7"></script>
</body>
</html>