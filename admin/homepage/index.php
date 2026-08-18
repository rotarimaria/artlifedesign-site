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

// Se pregătește fișierul selectat din formular.
function uploadedFile(string $key): ?array
{
    $error = $_FILES['media']['error'][$key] ?? UPLOAD_ERR_NO_FILE;

    if ((int) $error === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    return [
        'name' => $_FILES['media']['name'][$key] ?? '',
        'type' => $_FILES['media']['type'][$key] ?? '',
        'tmp_name' => $_FILES['media']['tmp_name'][$key] ?? '',
        'error' => $error,
        'size' => $_FILES['media']['size'][$key] ?? 0,
    ];
}

// Se salvează textele și imaginile homepage-ului.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
        $error = 'Sesiunea a expirat. Reîncarcă pagina.';
    } else {
        try {
            $pdo->beginTransaction();

            foreach ($sections as $fields) {
                foreach ($fields as $key => $def) {
                    $type = $def[1];

                    if (!in_array($type, ['image', 'video'], true)) {
                        saveHomepageValue(
                            $pdo,
                            $key,
                            trim((string) ($_POST['content'][$key] ?? ''))
                        );
                        continue;
                    }

                    if ($file = uploadedFile($key)) {
                        saveHomepageValue(
                            $pdo,
                            $key,
                            saveHomepageUpload($file, $type)
                        );
                    }

                    if ($type === 'image') {
                        foreach (['crop_x', 'crop_y', 'zoom', 'rotation', 'fit'] as $setting) {
                            $metaKey = "{$key}_{$setting}";

                            if (isset($_POST['media_meta'][$metaKey])) {
                                saveHomepageValue(
                                    $pdo,
                                    $metaKey,
                                    trim((string) $_POST['media_meta'][$metaKey])
                                );
                            }
                        }
                    }
                }
            }

            $pdo->commit();

            header(
                'Location: index.php?success=' .
                rawurlencode('Pagina principală a fost actualizată.')
            );
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

// Se afișează cardul pentru imagine sau video.
function renderMediaCard(string $key, array $def, array $content): void
{
    [$label, $type] = $def;

    $value = (string) ($content[$key] ?? '');
    $src = $value !== '' ? '../../' . ltrim($value, '/') : '';
    $isImage = $type === 'image';

    $x = (string) ($content[$key . '_crop_x'] ?? '50');
    $y = (string) ($content[$key . '_crop_y'] ?? '50');
    $zoom = (string) ($content[$key . '_zoom'] ?? '1');
    $rotation = (string) ($content[$key . '_rotation'] ?? '0');
    $fit = (string) ($content[$key . '_fit'] ?? 'cover');
?>
<article class="media-card" data-media-card>
    <div class="media-card-head">
        <strong><?= h($label) ?></strong>

        <?php if ($isImage): ?>
            <button class="mini-btn" type="button" data-adjust-media>
                Ajustează
            </button>
        <?php endif; ?>
    </div>

    <div class="media-preview" data-media-preview>
        <?php if ($type === 'video'): ?>
            <video
                src="<?= h($src) ?>"
                muted
                loop
                autoplay
                playsinline
                controls
                data-preview-element
            ></video>
        <?php else: ?>
            <img
                src="<?= h($src) ?>"
                alt=""
                data-preview-element
                style="
                    --crop-x:<?= h($x) ?>%;
                    --crop-y:<?= h($y) ?>%;
                    --crop-zoom:<?= h($zoom) ?>;
                    --crop-rotation:<?= h($rotation) ?>deg;
                    --crop-fit:<?= h($fit) ?>;
                "
            >
        <?php endif; ?>
    </div>

    <div class="media-card-foot">
        <label class="upload-btn">
            Schimbă <?= $type === 'video' ? 'video' : 'imaginea' ?>

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
        <input type="hidden" name="media_meta[<?= h($key) ?>_crop_x]" value="<?= h($x) ?>" data-meta="crop_x">
        <input type="hidden" name="media_meta[<?= h($key) ?>_crop_y]" value="<?= h($y) ?>" data-meta="crop_y">
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
    <link rel="stylesheet" href="style.css?v=3">
</head>
<body>

<header class="topbar">
    <a class="brand" href="../index.php">ArtLife <span>Admin</span></a>

    <div class="top-actions">
        <a class="btn btn-ghost" href="../../index.html" target="_blank" rel="noopener">
            Vezi site-ul
        </a>
        <a class="btn btn-ghost" href="../index.php">Dashboard</a>
        <a class="btn btn-ghost" href="../logout.php">Ieșire</a>
    </div>
</header>

<main class="homepage-admin">
    <div class="page-head">
        <div>
            <span class="eyebrow">Homepage</span>
            <h1>Pagina principală</h1>
            <p>Editează textele și imaginile. Lucrările rămân administrate separat.</p>
        </div>

        <a class="btn btn-primary" href="services.php">
            Administrează serviciile
        </a>
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
                            ?>
                                <label class="field <?= $type === 'textarea' ? 'full' : '' ?>">
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
                            <div class="media-grid">
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
            <button class="btn btn-primary" type="submit">
                Salvează pagina principală
            </button>
        </div>
    </form>
</main>

<div class="media-editor" id="mediaEditor" hidden>
    <div class="editor-box">
        <div class="editor-head">
            <h2>Ajustează imaginea</h2>
            <button type="button" class="btn" data-editor-close>Închide</button>
        </div>

        <div class="editor-stage" id="editorStage">
            <img id="editorImage" alt="">
        </div>

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
            <button type="button" class="btn btn-primary" data-editor-save>
                Aplică și salvează
            </button>
        </div>
    </div>
</div>

<script src="homepage.js?v=3"></script>
</body>
</html>