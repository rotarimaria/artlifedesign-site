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
        $focusX = validateFocus((int) ($_POST['focus_x'] ?? 50));
        $focusY = validateFocus((int) ($_POST['focus_y'] ?? 50));
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);
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
                        focus_x = :focus_x,
                        focus_y = :focus_y,
                        sort_order = :sort_order,
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
                    'focus_x' => $focusX,
                    'focus_y' => $focusY,
                    'sort_order' => $sortOrder,
                    'is_published' => $isPublished,
                    'published_at' => $publishedAt,
                    'id' => $projectId,
                ]);

                $deleteIds = $_POST['delete_image'] ?? [];

                if (is_array($deleteIds)) {
                    foreach ($deleteIds as $deleteId) {
                        deleteProjectImageById((int) $deleteId, $projectId, $pdo);
                    }
                }

                $primaryImageId = (int) ($_POST['primary_image_id'] ?? 0);

                if ($primaryImageId > 0) {
                    setPrimaryProjectImage($primaryImageId, $projectId, $pdo);
                }

                if (isset($_FILES['images'])) {
                    saveUploadedProjectImages($_FILES['images'], $projectId, $pdo, 4);
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
            <p class="muted">Modifică informațiile și fotografiile proiectului.</p>
        </div>
    </div>

    <?php if ($success !== ''): ?>
        <div class="notice"><?= e($success) ?></div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="error-box"><?= e($error) ?></div>
    <?php endif; ?>

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
                <label for="sort_order">Ordine manuală</label>
                <input id="sort_order" name="sort_order" type="number" value="<?= (int) $project['sort_order'] ?>">
                <p class="help">Număr mai mare = proiect afișat mai sus.</p>
            </div>

            <div class="field field-full">
                <label for="description">Descriere *</label>
                <textarea id="description" name="description" required><?= e((string) $project['description']) ?></textarea>
            </div>

            <div class="field field-full">
                <label for="tags">Taguri</label>
                <input id="tags" name="tags" value="<?= e((string) ($project['tags'] ?? '')) ?>">
            </div>

            <div class="field">
                <label for="focus_x">Focus imagine X</label>
                <input id="focus_x" name="focus_x" type="number" min="0" max="100" value="<?= (int) $project['focus_x'] ?>">
            </div>

            <div class="field">
                <label for="focus_y">Focus imagine Y</label>
                <input id="focus_y" name="focus_y" type="number" min="0" max="100" value="<?= (int) $project['focus_y'] ?>">
            </div>

            <div class="field field-full">
                <label>Imagini existente (<?= count($images) ?>/4)</label>

                <?php if ($images): ?>
                    <div class="image-grid">
                        <?php foreach ($images as $image): ?>
                            <div class="image-item">
                                <img src="../<?= e((string) $image['image_path']) ?>" alt="">

                                <div class="image-meta">
                                    <label>
                                        <input
                                            type="radio"
                                            name="primary_image_id"
                                            value="<?= (int) $image['id'] ?>"
                                            <?= (int) $image['is_primary'] === 1 ? 'checked' : '' ?>
                                        >
                                        Imagine principală
                                    </label>

                                    <label>
                                        <input
                                            type="checkbox"
                                            name="delete_image[]"
                                            value="<?= (int) $image['id'] ?>"
                                        >
                                        Șterge imaginea
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="help">Acest proiect nu are încă imagini.</p>
                <?php endif; ?>
            </div>

            <?php if (count($images) < 4): ?>
                <div class="field field-full">
                    <label for="images">Adaugă imagini noi</label>
                    <input
                        id="images"
                        name="images[]"
                        type="file"
                        accept="image/jpeg,image/png,image/webp,image/gif"
                        multiple
                    >
                    <p class="help">
                        Mai poți adăuga maximum <?= 4 - count($images) ?> imagine(i).
                        Maximum 8 MB per imagine.
                    </p>
                </div>
            <?php endif; ?>

            <div class="field field-full">
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
        </div>

        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Salvează modificările</button>
            <a class="btn" href="proiecte.php">Înapoi</a>
        </div>
    </form>

    <section class="danger-zone">
        <h3>Ștergere proiect</h3>
        <p>Ștergerea proiectului elimină și toate imaginile lui. Acțiunea nu poate fi anulată.</p>

        <a class="btn btn-danger" href="proiect-sterge.php?id=<?= $projectId ?>">
            Șterge proiectul
        </a>
    </section>
</main>
</body>
</html>