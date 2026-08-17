<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/project_helpers.php';

requireAdmin();

$projectId = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

if ($projectId <= 0) {
    http_response_code(400);
    exit('ID proiect invalid.');
}

$stmt = $pdo->prepare(
    'SELECT id, title FROM projects WHERE id = :id LIMIT 1'
);
$stmt->execute(['id' => $projectId]);
$project = $stmt->fetch();

if (!$project) {
    http_response_code(404);
    exit('Proiectul nu a fost găsit.');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
        $error = 'Sesiunea a expirat. Reîncarcă pagina.';
    } else {
        try {
            $pdo->beginTransaction();

            $images = getProjectImages($projectId, $pdo);

            foreach ($images as $image) {
                deleteProjectImageFile((string) $image['image_path']);
            }

            $delete = $pdo->prepare('DELETE FROM projects WHERE id = :id');
            $delete->execute(['id' => $projectId]);

            $pdo->commit();

            header(
                'Location: proiecte.php?success=' .
                rawurlencode('Proiectul a fost șters.')
            );
            exit;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $error = 'Proiectul nu a putut fi șters: ' . $e->getMessage();
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
    <title>Ștergere proiect | ArtLife Admin</title>
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
            <span class="eyebrow">Confirmare</span>
            <h1>Ștergi proiectul?</h1>
            <p class="muted"><?= e((string) $project['title']) ?></p>
        </div>
    </div>

    <?php if ($error !== ''): ?>
        <div class="error-box"><?= e($error) ?></div>
    <?php endif; ?>

    <section class="card form-card">
        <p style="margin-top:0;line-height:1.7;">
            Proiectul și fotografiile lui vor fi eliminate definitiv.
            Această acțiune nu poate fi anulată.
        </p>

        <form method="post">
            <input type="hidden" name="id" value="<?= $projectId ?>">
            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

            <div class="form-actions">
                <button class="btn btn-danger" type="submit">Da, șterge definitiv</button>
                <a class="btn" href="proiecte.php">Nu, înapoi</a>
            </div>
        </form>
    </section>
</main>
</body>
</html>