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
    'focus_x' => 50,
    'focus_y' => 50,
    'sort_order' => 0,
    'is_published' => 1,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['title'] = trim((string) ($_POST['title'] ?? ''));
    $data['service'] = trim((string) ($_POST['service'] ?? ''));
    $data['category'] = trim((string) ($_POST['category'] ?? ''));
    $data['description'] = trim((string) ($_POST['description'] ?? ''));
    $data['tags'] = normalizeTags((string) ($_POST['tags'] ?? ''));
    $data['focus_x'] = validateFocus((int) ($_POST['focus_x'] ?? 50));
    $data['focus_y'] = validateFocus((int) ($_POST['focus_y'] ?? 50));
    $data['sort_order'] = (int) ($_POST['sort_order'] ?? 0);
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
                    (title, service, category, description, focus_x, focus_y, tags, sort_order, is_published, published_at)
                 VALUES
                    (:title, :service, :category, :description, :focus_x, :focus_y, :tags, :sort_order, :is_published, :published_at)'
            );

            $stmt->execute([
                'title' => $data['title'],
                'service' => $data['service'],
                'category' => $data['category'],
                'description' => $data['description'],
                'focus_x' => $data['focus_x'],
                'focus_y' => $data['focus_y'],
                'tags' => $data['tags'],
                'sort_order' => $data['sort_order'],
                'is_published' => $data['is_published'],
                'published_at' => $data['is_published'] ? date('Y-m-d H:i:s') : null,
            ]);

            $projectId = (int) $pdo->lastInsertId();

            if (isset($_FILES['images'])) {
                saveUploadedProjectImages($_FILES['images'], $projectId, $pdo, 4);
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
            <p class="muted">Poți încărca maximum 4 imagini pentru fiecare proiect.</p>
        </div>
    </div>

    <?php if ($error !== ''): ?>
        <div class="error-box"><?= e($error) ?></div>
    <?php endif; ?>

    <form class="card form-card" method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

        <div class="form-grid">
            <div class="field">
                <label for="title">Titlu proiect *</label>
                <input id="title" name="title" value="<?= e((string) $data['title']) ?>" required>
            </div>

            <div class="field">
                <label for="service">Serviciu *</label>
                <input
                    id="service"
                    name="service"
                    value="<?= e((string) $data['service']) ?>"
                    placeholder="Ex: Litere în volum & Standuri"
                    required
                >
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
                <label for="sort_order">Ordine manuală</label>
                <input id="sort_order" name="sort_order" type="number" value="<?= (int) $data['sort_order'] ?>">
                <p class="help">Numărul mai mare apare înainte. Dacă lași 0, contează data publicării.</p>
            </div>

            <div class="field field-full">
                <label for="description">Descriere *</label>
                <textarea id="description" name="description" required><?= e((string) $data['description']) ?></textarea>
            </div>

            <div class="field field-full">
                <label for="tags">Taguri</label>
                <input
                    id="tags"
                    name="tags"
                    value="<?= e((string) $data['tags']) ?>"
                    placeholder="branding, exterior, lightbox"
                >
                <p class="help">Separă tagurile prin virgulă.</p>
            </div>

            <div class="field">
                <label for="focus_x">Focus imagine X</label>
                <input id="focus_x" name="focus_x" type="number" min="0" max="100" value="<?= (int) $data['focus_x'] ?>">
                <p class="help">0 = stânga, 50 = centru, 100 = dreapta.</p>
            </div>

            <div class="field">
                <label for="focus_y">Focus imagine Y</label>
                <input id="focus_y" name="focus_y" type="number" min="0" max="100" value="<?= (int) $data['focus_y'] ?>">
                <p class="help">0 = sus, 50 = centru, 100 = jos.</p>
            </div>

            <div class="field field-full">
                <label for="images">Imagini proiect</label>
                <input
                    id="images"
                    name="images[]"
                    type="file"
                    accept="image/jpeg,image/png,image/webp,image/gif"
                    multiple
                >
                <p class="help">Maximum 4 imagini. Maximum 8 MB per imagine. Prima devine principală.</p>
            </div>

            <div class="field field-full">
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
        </div>

        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Salvează proiectul</button>
            <a class="btn" href="proiecte.php">Anulează</a>
        </div>
    </form>
</main>
</body>
</html>