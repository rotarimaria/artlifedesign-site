<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/services.php';

requireAdmin();

$error = '';
$success = trim((string) ($_GET['success'] ?? ''));

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// Se pregătește imaginea selectată pentru slotul cerut.
function uploadedImage(int $slot): ?array
{
    $error = $_FILES['images']['error'][$slot] ?? UPLOAD_ERR_NO_FILE;

    if ((int) $error === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    return [
        'name' => $_FILES['images']['name'][$slot] ?? '',
        'type' => $_FILES['images']['type'][$slot] ?? '',
        'tmp_name' => $_FILES['images']['tmp_name'][$slot] ?? '',
        'error' => $error,
        'size' => $_FILES['images']['size'][$slot] ?? 0,
    ];
}

// Se șterge doar un fișier încărcat pentru servicii.
function deleteServiceUpload(string $path): void
{
    if ($path === '' || !str_starts_with($path, 'uploads/homepage/')) {
        return;
    }

    $file = dirname(__DIR__, 2) . '/' . $path;

    if (is_file($file)) {
        @unlink($file);
    }
}

// Se normalizează valorile editorului de imagine.
function mediaValues(string $prefix): array
{
    $fit = (string) ($_POST["{$prefix}_fit"] ?? 'cover');

    return [
        'crop_x' => max(0, min(100, (float) ($_POST["{$prefix}_crop_x"] ?? 50))),
        'crop_y' => max(0, min(100, (float) ($_POST["{$prefix}_crop_y"] ?? 50))),
        'zoom' => max(1, min(3, (float) ($_POST["{$prefix}_zoom"] ?? 1))),
        'rotation' => max(-180, min(180, (int) ($_POST["{$prefix}_rotation"] ?? 0))),
        'fit' => in_array($fit, ['cover', 'contain'], true) ? $fit : 'cover',
    ];
}

// Se salvează sau se actualizează serviciul în BD.
function saveService(PDO $pdo, int $id): void
{
    $name = trim((string) ($_POST['name'] ?? ''));
    $slug = $id > 0
        ? trim((string) ($_POST['slug'] ?? ''))
        : serviceSlug($name);

    if ($name === '' || $slug === '') {
        throw new RuntimeException('Numele serviciului este obligatoriu.');
    }

    $existing = null;

    if ($id > 0) {
        $stmt = $pdo->prepare('SELECT * FROM services WHERE id = ?');
        $stmt->execute([$id]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$existing) {
            throw new RuntimeException('Serviciul nu există.');
        }
    }

    $fields = [
        'slug' => $slug,
        'name' => $name,
        'card_title' => trim((string) ($_POST['card_title'] ?? '')),
        'card_text' => trim((string) ($_POST['card_text'] ?? '')),
        'detail_title' => trim((string) ($_POST['detail_title'] ?? '')),
        'detail_text' => trim((string) ($_POST['detail_text'] ?? '')),
        'examples' => trim((string) ($_POST['examples'] ?? '')),
        'btn_examples' => trim((string) ($_POST['btn_examples'] ?? 'Vezi exemple')),
        'btn_quote' => trim((string) ($_POST['btn_quote'] ?? 'Solicită o ofertă')),
        'sort_order' => (int) ($_POST['sort_order'] ?? 0),
        'is_active' => isset($_POST['is_active']) ? 1 : 0,
    ];

    $filesToDelete = [];
    $firstImageUploaded = false;

    for ($i = 1; $i <= 3; $i++) {
        $oldPath = (string) ($existing["image{$i}"] ?? '');
        $fields["image{$i}"] = $oldPath;

        $file = uploadedImage($i);
        $delete = (string) ($_POST['delete_image'][$i] ?? '') === '1';

        if ($file) {
            $newPath = saveServiceUpload($file);
            $fields["image{$i}"] = $newPath;

            if ($oldPath !== '' && $oldPath !== $newPath) {
                $filesToDelete[] = $oldPath;
            }

            if ($i === 1) {
                $firstImageUploaded = true;
            }
        } elseif ($delete) {
            $fields["image{$i}"] = '';

            if ($oldPath !== '') {
                $filesToDelete[] = $oldPath;
            }
        }

        $detail = mediaValues("image{$i}");

        if ($delete && !$file) {
            $detail = [
                'crop_x' => 50,
                'crop_y' => 50,
                'zoom' => 1,
                'rotation' => 0,
                'fit' => 'cover',
            ];
        }

        $fields["image{$i}_crop_x"] = $detail['crop_x'];
        $fields["image{$i}_crop_y"] = $detail['crop_y'];
        $fields["image{$i}_zoom"] = $detail['zoom'];
        $fields["image{$i}_rotation"] = $detail['rotation'];
        $fields["image{$i}_fit"] = $detail['fit'];
    }

    // Prima imagine are o ajustare separată pentru cardul mic.
    $card = mediaValues('image1_card');
    $deleteFirst = (string) ($_POST['delete_image'][1] ?? '') === '1';

    if ($deleteFirst && !$firstImageUploaded) {
        $card = [
            'crop_x' => 50,
            'crop_y' => 50,
            'zoom' => 1,
            'rotation' => 0,
            'fit' => 'cover',
        ];
    }

    $fields['image1_card_crop_x'] = $card['crop_x'];
    $fields['image1_card_crop_y'] = $card['crop_y'];
    $fields['image1_card_zoom'] = $card['zoom'];
    $fields['image1_card_rotation'] = $card['rotation'];
    $fields['image1_card_fit'] = $card['fit'];

    if ($id > 0) {
        $set = implode(', ', array_map(
            static fn (string $key): string => "{$key} = :{$key}",
            array_keys($fields)
        ));

        $fields['id'] = $id;
        $pdo->prepare("UPDATE services SET {$set} WHERE id = :id")->execute($fields);
    } else {
        $columns = implode(', ', array_keys($fields));
        $values = implode(', ', array_map(
            static fn (string $key): string => ":{$key}",
            array_keys($fields)
        ));

        $pdo->prepare("INSERT INTO services ({$columns}) VALUES ({$values})")
            ->execute($fields);
    }

    // Fișierele vechi se șterg doar după salvarea reușită în BD.
    foreach (array_unique($filesToDelete) as $path) {
        deleteServiceUpload($path);
    }
}

// Se elimină serviciul doar dacă nu are proiecte asociate.
function deleteService(PDO $pdo, int $id): void
{
    $stmt = $pdo->prepare('SELECT * FROM services WHERE id = ?');
    $stmt->execute([$id]);
    $service = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$service) {
        throw new RuntimeException('Serviciul nu există.');
    }

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM projects WHERE category = ?');
    $stmt->execute([(string) $service['slug']]);

    if ((int) $stmt->fetchColumn() > 0) {
        throw new RuntimeException(
            'Serviciul are proiecte asociate. Mută sau șterge proiectele mai întâi.'
        );
    }

    $pdo->prepare('DELETE FROM services WHERE id = ?')->execute([$id]);

    for ($i = 1; $i <= 3; $i++) {
        deleteServiceUpload((string) ($service["image{$i}"] ?? ''));
    }
}

// Se procesează formularul de servicii.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
        $error = 'Sesiunea a expirat.';
    } else {
        try {
            $id = (int) ($_POST['id'] ?? 0);
            $action = (string) ($_POST['action'] ?? 'save');

            if ($action === 'delete') {
                deleteService($pdo, $id);
                $message = 'Serviciul a fost eliminat.';
            } else {
                saveService($pdo, $id);
                $message = 'Serviciile au fost actualizate.';
            }

            header('Location: services.php?success=' . rawurlencode($message));
            exit;
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
    }
}

$services = getServices($pdo);

// Se afișează un slot pentru imaginea serviciului.
function mediaCard(array $service, int $index): void
{
    $src = (string) ($service["image{$index}"] ?? '');

    $detail = [
        'x' => (string) ($service["image{$index}_crop_x"] ?? '50'),
        'y' => (string) ($service["image{$index}_crop_y"] ?? '50'),
        'zoom' => (string) ($service["image{$index}_zoom"] ?? '1'),
        'rotation' => (string) ($service["image{$index}_rotation"] ?? '0'),
        'fit' => (string) ($service["image{$index}_fit"] ?? 'cover'),
    ];

    $card = $index === 1 ? [
        'x' => (string) ($service['image1_card_crop_x'] ?? $detail['x']),
        'y' => (string) ($service['image1_card_crop_y'] ?? $detail['y']),
        'zoom' => (string) ($service['image1_card_zoom'] ?? $detail['zoom']),
        'rotation' => (string) ($service['image1_card_rotation'] ?? $detail['rotation']),
        'fit' => (string) ($service['image1_card_fit'] ?? $detail['fit']),
    ] : null;
?>
<article class="media-card" data-media-card data-slot="<?= $index ?>">
    <div class="media-card-head">
        <strong>Imagine <?= $index ?></strong>

        <div class="media-card-buttons">
            <?php if ($index === 1): ?>
                <button class="mini-btn" type="button" data-adjust-media="card">Card mic</button>
                <button class="mini-btn" type="button" data-adjust-media="detail">Detalii</button>
            <?php else: ?>
                <button class="mini-btn" type="button" data-adjust-media="detail">Ajustează</button>
            <?php endif; ?>

            <button
                class="mini-btn media-delete-btn"
                type="button"
                data-delete-media
                <?= $src === '' ? 'hidden' : '' ?>
            >
                Șterge
            </button>
        </div>
    </div>

    <div class="media-preview" data-media-preview>
        <img
            <?= $src !== '' ? 'src="../../' . h($src) . '"' : '' ?>
            alt=""
            data-preview-element
            style="
                --crop-x:<?= h($detail['x']) ?>%;
                --crop-y:<?= h($detail['y']) ?>%;
                --crop-zoom:<?= h($detail['zoom']) ?>;
                --crop-rotation:<?= h($detail['rotation']) ?>deg;
                --crop-fit:<?= h($detail['fit']) ?>;
            "
        >
    </div>

    <div class="media-card-foot">
        <label class="upload-btn">
            Schimbă imaginea
            <input
                type="file"
                name="images[<?= $index ?>]"
                accept="image/*"
                data-media-input
            >
        </label>

        <small data-file-name><?= h(basename($src)) ?></small>
    </div>

    <input type="hidden" name="delete_image[<?= $index ?>]" value="0" data-delete-input>

    <input type="hidden" name="image<?= $index ?>_crop_x" value="<?= h($detail['x']) ?>" data-meta="detail_crop_x">
    <input type="hidden" name="image<?= $index ?>_crop_y" value="<?= h($detail['y']) ?>" data-meta="detail_crop_y">
    <input type="hidden" name="image<?= $index ?>_zoom" value="<?= h($detail['zoom']) ?>" data-meta="detail_zoom">
    <input type="hidden" name="image<?= $index ?>_rotation" value="<?= h($detail['rotation']) ?>" data-meta="detail_rotation">
    <input type="hidden" name="image<?= $index ?>_fit" value="<?= h($detail['fit']) ?>" data-meta="detail_fit">

    <?php if ($index === 1): ?>
        <input type="hidden" name="image1_card_crop_x" value="<?= h($card['x']) ?>" data-meta="card_crop_x">
        <input type="hidden" name="image1_card_crop_y" value="<?= h($card['y']) ?>" data-meta="card_crop_y">
        <input type="hidden" name="image1_card_zoom" value="<?= h($card['zoom']) ?>" data-meta="card_zoom">
        <input type="hidden" name="image1_card_rotation" value="<?= h($card['rotation']) ?>" data-meta="card_rotation">
        <input type="hidden" name="image1_card_fit" value="<?= h($card['fit']) ?>" data-meta="card_fit">
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
<title>Servicii | ArtLife Admin</title>

<?php require __DIR__ . '/../_admin_styles.php'; ?>
<link rel="stylesheet" href="style.css?v=3">

<style>
.services-admin{width:min(1180px,calc(100% - 32px));margin:auto;padding:32px 0 100px}
.service-form{margin-top:14px;padding:18px;border:1px solid var(--border);border-radius:16px;background:var(--panel)}
.service-form summary{cursor:pointer;font-weight:600}
.service-fields{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:16px}
.service-fields label{display:grid;gap:6px}
.service-fields label.full{grid-column:1/-1}
.service-fields textarea{min-height:90px}
.service-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:14px}
.add-service{margin-bottom:22px}

/* Butoanele imaginilor */
.media-card-buttons{display:flex;flex-wrap:wrap;gap:6px;justify-content:flex-end}
.media-delete-btn{color:#ff8c8c}
.media-delete-btn:hover{border-color:#ff8c8c!important;color:#fff}

@media(max-width:700px){
  .service-fields{grid-template-columns:1fr}
  .service-fields label.full{grid-column:auto}
}
</style>
</head>
<body>

<header class="topbar">
    <a class="brand" href="../index.php">ArtLife <span>Admin</span></a>

    <div class="top-actions">
        <a class="btn btn-ghost" href="index.php">Pagina principală</a>
        <a class="btn btn-ghost" href="../index.php">Dashboard</a>
    </div>
</header>

<main class="services-admin">
    <span class="eyebrow">Servicii</span>
    <h1>Servicii</h1>
    <p>Un serviciu adăugat aici apare automat în homepage, filtre și formularele de proiect.</p>

    <?php if ($success !== ''): ?>
        <div class="notice"><?= h($success) ?></div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="error-box"><?= h($error) ?></div>
    <?php endif; ?>

    <details class="service-form add-service">
        <summary>+ Adaugă serviciu nou</summary>

        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
            <input type="hidden" name="is_active" value="1">

            <div class="service-fields">
                <label>
                    <span>Nume serviciu</span>
                    <input name="name" required>
                </label>

                <label>
                    <span>Ordine</span>
                    <input type="number" name="sort_order" value="100">
                </label>

                <label class="full">
                    <span>Titlu card</span>
                    <input name="card_title">
                </label>

                <label class="full">
                    <span>Descriere card</span>
                    <textarea name="card_text"></textarea>
                </label>

                <label class="full">
                    <span>Titlu detalii</span>
                    <input name="detail_title">
                </label>

                <label class="full">
                    <span>Descriere detalii</span>
                    <textarea name="detail_text"></textarea>
                </label>

                <label class="full">
                    <span>Exemple — câte unul pe rând</span>
                    <textarea name="examples"></textarea>
                </label>
            </div>

            <div class="service-media-grid media-grid">
                <?php for ($i = 1; $i <= 3; $i++) mediaCard([], $i); ?>
            </div>

            <div class="service-actions">
                <button class="btn btn-primary" type="submit" name="action" value="save">
                    Adaugă serviciul
                </button>
            </div>
        </form>
    </details>

    <?php foreach ($services as $service): ?>
        <details class="service-form">
            <summary><?= h((string) $service['name']) ?></summary>

            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                <input type="hidden" name="id" value="<?= (int) $service['id'] ?>">
                <input type="hidden" name="slug" value="<?= h((string) $service['slug']) ?>">

                <div class="service-fields">
                    <label>
                        <span>Nume serviciu</span>
                        <input name="name" value="<?= h((string) $service['name']) ?>">
                    </label>

                    <label>
                        <span>Ordine</span>
                        <input type="number" name="sort_order" value="<?= (int) $service['sort_order'] ?>">
                    </label>

                    <label class="full">
                        <span>Titlu card</span>
                        <input name="card_title" value="<?= h((string) $service['card_title']) ?>">
                    </label>

                    <label class="full">
                        <span>Descriere card</span>
                        <textarea name="card_text"><?= h((string) $service['card_text']) ?></textarea>
                    </label>

                    <label class="full">
                        <span>Titlu detalii</span>
                        <input name="detail_title" value="<?= h((string) $service['detail_title']) ?>">
                    </label>

                    <label class="full">
                        <span>Descriere detalii</span>
                        <textarea name="detail_text"><?= h((string) $service['detail_text']) ?></textarea>
                    </label>

                    <label class="full">
                        <span>Exemple — câte unul pe rând</span>
                        <textarea name="examples"><?= h((string) $service['examples']) ?></textarea>
                    </label>

                    <label>
                        <span>Buton exemple</span>
                        <input name="btn_examples" value="<?= h((string) $service['btn_examples']) ?>">
                    </label>

                    <label>
                        <span>Buton ofertă</span>
                        <input name="btn_quote" value="<?= h((string) $service['btn_quote']) ?>">
                    </label>

                    <label>
                        <span>Activ</span>
                        <input
                            type="checkbox"
                            name="is_active"
                            <?= (int) $service['is_active'] === 1 ? 'checked' : '' ?>
                        >
                    </label>
                </div>

                <div class="service-media-grid media-grid">
                    <?php for ($i = 1; $i <= 3; $i++) mediaCard($service, $i); ?>
                </div>

                <div class="service-actions">
                    <button
                        class="btn btn-ghost"
                        type="submit"
                        name="action"
                        value="delete"
                        onclick="return confirm('Sigur vrei să elimini acest serviciu?')"
                    >
                        Elimină serviciul
                    </button>

                    <button class="btn btn-primary" type="submit" name="action" value="save">
                        Salvează serviciul
                    </button>
                </div>
            </form>
        </details>
    <?php endforeach; ?>
</main>

<div class="media-editor" id="mediaEditor" hidden>
    <div class="editor-box">
        <div class="editor-head">
            <h2 id="editorTitle">Ajustează imaginea</h2>
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
            <input id="editorZoom" type="range" min="1" max="3" step=".01">
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

<script src="homepage.js?v=3"></script>
</body>
</html>