<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/works_page_content.php';

requireAdmin();

$content = getWorksPageContent($pdo);
$error = '';
$success = trim((string) ($_GET['success'] ?? ''));

function e(string $v): string
{
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
        $error = 'Sesiunea a expirat. Reîncarcă pagina.';
    } else {
        try {
            $values = [];
            foreach (worksPageFields() as $key => $default) {
                if (str_starts_with($key, 'show_')) {
                    $values[$key] = isset($_POST[$key]) ? '1' : '0';
                } else {
                    $values[$key] = trim((string) ($_POST['content'][$key] ?? $default));
                }
            }

            saveWorksPageContent($pdo, $values);
            header('Location: index.php?success=' . rawurlencode('Pagina Lucrări a fost actualizată.'));
            exit;
        } catch (Throwable $e) {
            $error = 'Nu s-au putut salva modificările: ' . $e->getMessage();
        }
    }
}

$content = getWorksPageContent($pdo);

$textFields = [
    'back_text' => 'Link înapoi',
    'label' => 'Etichetă',
    'title' => 'Titlu pagină',
    'intro' => 'Descriere',
    'search_placeholder' => 'Placeholder căutare',
    'no_results' => 'Mesaj fără rezultate',
    'load_more' => 'Buton — Mai multe',
    'cta_label' => 'CTA — etichetă',
    'cta_title' => 'CTA — titlu',
    'cta_button' => 'CTA — buton',
];

$filters = [
    'poligrafie' => 'Poligrafie',
    'volum' => 'Litere & Standuri',
    'posm' => 'P.O.S.M.',
    'auto' => 'Branding Auto',
    'laser' => 'Laser / Plotter',
];
?>
<!doctype html>
<html lang="ro">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Pagina Lucrări | ArtLife Admin</title>

    <?php require __DIR__ . '/../_admin_styles.php'; ?>

    <style>
      .works-admin{width:min(1000px,calc(100% - 32px));margin:auto;padding:34px 0 100px}
      .works-admin h1{margin:7px 0 8px}
      .works-admin p{color:var(--muted)}
      .panel{margin-top:18px;padding:20px;border:1px solid var(--border);border-radius:18px;background:var(--panel)}
      .grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
      .field{display:grid;gap:7px}
      .field.full{grid-column:1/-1}
      .field span{font-size:12px;font-weight:600}
      textarea{min-height:100px;resize:vertical}
      .filters{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
      .filter-row{display:grid;grid-template-columns:auto 1fr;align-items:center;gap:10px;padding:10px;border:1px solid var(--border);border-radius:10px}
      .filter-row input[type="text"]{width:100%}
      .save{position:sticky;bottom:10px;margin-top:18px;padding:10px;display:flex;justify-content:flex-end;border:1px solid var(--border);border-radius:13px;background:rgba(6,9,7,.94)}
      @media(max-width:700px){.grid,.filters{grid-template-columns:1fr}.field.full{grid-column:auto}}
    </style>
</head>
<body>

<header class="topbar">
    <a class="brand" href="../index.php">ArtLife <span>Admin</span></a>
    <div class="top-actions">
        <a class="btn btn-ghost" href="../../lucrari.html" target="_blank" rel="noopener">Vezi pagina</a>
        <a class="btn btn-ghost" href="../index.php">Dashboard</a>
        <a class="btn btn-ghost" href="../logout.php">Ieșire</a>
    </div>
</header>

<main class="works-admin">
    <span class="eyebrow">PORTOFOLIU</span>
    <h1>Pagina Lucrări</h1>
    <p>Editează pagina. Proiectele/cardurile sunt administrate separat.</p>

    <?php if ($success): ?><div class="notice"><?= e($success) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="error-box"><?= e($error) ?></div><?php endif; ?>

    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

        <section class="panel">
            <div class="grid">
                <?php foreach ($textFields as $key => $label): ?>
                    <?php $long = in_array($key, ['intro','cta_title'], true); ?>
                    <label class="field <?= $long ? 'full' : '' ?>">
                        <span><?= e($label) ?></span>
                        <?php if ($long): ?>
                            <textarea name="content[<?= e($key) ?>]"><?= e($content[$key]) ?></textarea>
                        <?php else: ?>
                            <input type="text" name="content[<?= e($key) ?>]" value="<?= e($content[$key]) ?>">
                        <?php endif; ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="panel">
            <h2>Filtre</h2>
            <div class="filters">
                <label class="filter-row">
                    <input type="checkbox" checked disabled>
                    <input type="text" name="content[filter_all]" value="<?= e($content['filter_all']) ?>">
                </label>

                <?php foreach ($filters as $key => $default): ?>
                    <label class="filter-row">
                        <input
                            type="checkbox"
                            name="show_<?= e($key) ?>"
                            <?= $content['show_' . $key] === '1' ? 'checked' : '' ?>
                        >
                        <input
                            type="text"
                            name="content[filter_<?= e($key) ?>]"
                            value="<?= e($content['filter_' . $key]) ?>"
                        >
                    </label>
                <?php endforeach; ?>
            </div>
        </section>

        <div class="save">
            <button class="btn btn-primary" type="submit">Salvează pagina Lucrări</button>
        </div>
    </form>
</main>

</body>
</html>