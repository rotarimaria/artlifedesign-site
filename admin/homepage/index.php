<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/homepage_content.php';

requireAdmin();

$sections = homepageFields();
$content = getHomepageContent($pdo);
$error = '';
$success = trim((string) ($_GET['success'] ?? ''));

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function uploadFromGroup(string $key): ?array
{
    if (!isset($_FILES['media']['error'][$key])) {
        return null;
    }

    if ((int) $_FILES['media']['error'][$key] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    return [
        'name' => $_FILES['media']['name'][$key] ?? '',
        'type' => $_FILES['media']['type'][$key] ?? '',
        'tmp_name' => $_FILES['media']['tmp_name'][$key] ?? '',
        'error' => $_FILES['media']['error'][$key] ?? UPLOAD_ERR_NO_FILE,
        'size' => $_FILES['media']['size'][$key] ?? 0,
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
        $error = 'Sesiunea a expirat. Reîncarcă pagina.';
    } else {
        try {
            $pdo->beginTransaction();

            foreach ($sections as $fields) {
                foreach ($fields as $key => $def) {
                    [$label, $type] = $def;

                    if (in_array($type, ['image', 'video'], true)) {
                        $file = uploadFromGroup($key);
                        if ($file) {
                            saveHomepageValue($pdo, $key, saveHomepageUpload($file, $type));
                        }

                        if ($type === 'image') {
                            foreach (['crop_x','crop_y','zoom','rotation','fit'] as $setting) {
                                $metaKey = $key . '_' . $setting;
                                if (isset($_POST['media_meta'][$metaKey])) {
                                    saveHomepageValue(
                                        $pdo,
                                        $metaKey,
                                        trim((string) $_POST['media_meta'][$metaKey])
                                    );
                                }
                            }
                        }
                        continue;
                    }

                    saveHomepageValue(
                        $pdo,
                        $key,
                        trim((string) ($_POST['content'][$key] ?? ''))
                    );
                }
            }

            $pdo->commit();
            header('Location: index.php?success=' . rawurlencode('Pagina principală a fost actualizată.'));
            exit;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = 'Nu s-au putut salva modificările: ' . $e->getMessage();
        }
    }

    $content = getHomepageContent($pdo);
}

function isServiceSection(string $name): bool
{
    return str_starts_with($name, 'Serviciu — ');
}

function renderMediaCard(string $key, array $def, array $content): void
{
    [$label, $type] = $def;
    $value = (string) ($content[$key] ?? '');
    $isImage = $type === 'image';
    $previewSrc = $value !== '' ? '../../' . ltrim($value, '/') : '';

    $cropX = (string) ($content[$key . '_crop_x'] ?? '50');
    $cropY = (string) ($content[$key . '_crop_y'] ?? '50');
    $zoom = (string) ($content[$key . '_zoom'] ?? '1');
    $rotation = (string) ($content[$key . '_rotation'] ?? '0');
    $fit = (string) ($content[$key . '_fit'] ?? 'cover');
    ?>
    <article
        class="media-card"
        data-media-card
        data-media-key="<?= h($key) ?>"
        data-media-type="<?= h($type) ?>"
    >
        <div class="media-card-head">
            <strong><?= h($label) ?></strong>
            <?php if ($isImage): ?>
                <button class="mini-btn" type="button" data-adjust-media>Ajustează</button>
            <?php endif; ?>
        </div>

        <div class="media-preview" data-media-preview>
            <?php if ($type === 'video'): ?>
                <video
                    src="<?= h($previewSrc) ?>"
                    muted loop autoplay playsinline controls
                    data-preview-element
                ></video>
            <?php else: ?>
                <img
                    src="<?= h($previewSrc) ?>"
                    alt=""
                    data-preview-element
                    style="
                        --crop-x:<?= h($cropX) ?>%;
                        --crop-y:<?= h($cropY) ?>%;
                        --crop-zoom:<?= h($zoom) ?>;
                        --crop-rotation:<?= h($rotation) ?>deg;
                        --crop-fit:<?= h($fit) ?>;
                    "
                >
            <?php endif; ?>
        </div>

        <div class="media-card-foot">
            <label class="upload-btn">
                <span>Schimbă <?= $type === 'video' ? 'video' : 'imaginea' ?></span>
                <input
                    type="file"
                    name="media[<?= h($key) ?>]"
                    <?= $type === 'video'
                        ? 'accept="video/mp4,video/webm,video/quicktime"'
                        : 'accept="image/jpeg,image/png,image/webp,image/gif,image/svg+xml"' ?>
                    data-media-input
                >
            </label>
            <small data-file-name><?= h(basename($value)) ?></small>
        </div>

        <?php if ($isImage): ?>
            <input type="hidden" name="media_meta[<?= h($key) ?>_crop_x]" value="<?= h($cropX) ?>" data-meta="crop_x">
            <input type="hidden" name="media_meta[<?= h($key) ?>_crop_y]" value="<?= h($cropY) ?>" data-meta="crop_y">
            <input type="hidden" name="media_meta[<?= h($key) ?>_zoom]" value="<?= h($zoom) ?>" data-meta="zoom">
            <input type="hidden" name="media_meta[<?= h($key) ?>_rotation]" value="<?= h($rotation) ?>" data-meta="rotation">
            <input type="hidden" name="media_meta[<?= h($key) ?>_fit]" value="<?= h($fit) ?>" data-meta="fit">
        <?php endif; ?>
    </article>
    <?php
}
?>
<!doctype html>
<html lang="ro">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Pagina principală | ArtLife Admin</title>

    <?php require __DIR__ . '/../_admin_styles.php'; ?>
    <link rel="stylesheet" href="style.css?v=2">
</head>
<body>

<header class="topbar">
    <a class="brand" href="../index.php">ArtLife <span>Admin</span></a>
    <div class="top-actions">
        <a class="btn btn-ghost" href="../../index.html" target="_blank" rel="noopener">Vezi site-ul</a>
        <a class="btn btn-ghost" href="../index.php">Dashboard</a>
        <a class="btn btn-ghost" href="../logout.php">Ieșire</a>
    </div>
</header>

<main class="homepage-admin">
    <div class="page-head">
        <div>
            <span class="eyebrow">HOMEPAGE</span>
            <h1>Pagina principală</h1>
            <p>Editează textele și imaginile. Lucrările rămân administrate separat.</p>
        </div>

        <a class="btn btn-primary" href="services.php">Administrează serviciile</a>
    </div>

    <?php if ($success !== ''): ?>
        <div class="notice"><?= h($success) ?></div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="error-box"><?= h($error) ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" id="homepageForm">
        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">

        <div class="section-list">
        <?php foreach ($sections as $sectionName => $fields): ?>
            <details class="edit-section">
                <summary>
                    <span><?= h($sectionName) ?></span>
                    <b>+</b>
                </summary>

                <div class="section-body">
                    <div class="field-grid">
                        <?php
                        $media = [];
                        foreach ($fields as $key => $def):
                            [$label, $type] = $def;

                            if (in_array($type, ['image', 'video'], true)) {
                                $media[$key] = $def;
                                continue;
                            }

                            $full = $type === 'textarea';
                        ?>
                            <label class="field <?= $full ? 'full' : '' ?>">
                                <span><?= h($label) ?></span>
                                <?php if ($type === 'textarea'): ?>
                                    <textarea name="content[<?= h($key) ?>]"><?= h((string) ($content[$key] ?? '')) ?></textarea>
                                <?php else: ?>
                                    <input
                                        type="<?= $type === 'url' ? 'url' : 'text' ?>"
                                        name="content[<?= h($key) ?>]"
                                        value="<?= h((string) ($content[$key] ?? '')) ?>"
                                    >
                                <?php endif; ?>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($media): ?>
                        <?php if (isServiceSection($sectionName)): ?>
                            <p class="media-layout-note">
                                Imaginea 1 este principală; imaginile 2 și 3 sunt secundare.
                            </p>
                        <?php endif; ?>

                        <div class="media-grid <?= isServiceSection($sectionName) ? 'service-media-grid' : '' ?>">
                            <?php foreach ($media as $key => $def): ?>
                                <?php renderMediaCard($key, $def, $content); ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </details>
        <?php endforeach; ?>
        </div>

        <div class="save-bar">
            <button class="btn btn-primary" type="submit">Salvează pagina principală</button>
        </div>
    </form>
</main>

<div class="media-editor" id="mediaEditor" hidden>
    <div class="editor-box">
        <div class="editor-head">
            <div>
                <small>AJUSTARE IMAGINE</small>
                <h2 id="editorTitle">Imagine</h2>
            </div>
            <button type="button" class="btn" data-editor-close>Închide</button>
        </div>

        <div class="editor-stage" id="editorStage">
            <img id="editorImage" alt="">
        </div>

        <p class="editor-help">Trage direct imaginea în orice direcție. Reglajele se aplică pe card.</p>

        <div class="fit-switch">
            <button type="button" data-fit="cover">Umple cadrul</button>
            <button type="button" data-fit="contain">Fișier întreg</button>
        </div>

        <label class="range-row">
            <span>Zoom</span>
            <input id="editorZoom" type="range" min="1" max="3" step="0.01">
            <output id="editorZoomValue"></output>
        </label>

        <label class="range-row">
            <span>Rotire</span>
            <input id="editorRotation" type="range" min="-180" max="180" step="1">
            <output id="editorRotationValue"></output>
        </label>

        <div class="editor-actions">
            <button type="button" class="btn" data-editor-reset>Reset</button>
            <button type="button" class="btn btn-primary" data-editor-apply>Aplică</button>
            <button type="button" class="btn btn-primary" data-editor-save>Aplică și salvează</button>
        </div>
    </div>
</div>

<script src="homepage.js?v=2"></script>
</body>
</html>