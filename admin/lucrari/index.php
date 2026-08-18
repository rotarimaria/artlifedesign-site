<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/works_page_content.php';

requireAdmin();

$content = getWorksPageContent($pdo);
$error = '';
$success = trim((string)($_GET['success'] ?? ''));

function e(string $v): string {
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
        $error = 'Sesiunea a expirat.';
    } else {
        try {
            saveWorksPageContent($pdo, $_POST['content'] ?? []);
            header('Location: index.php?success=' . rawurlencode('Pagina Lucrări a fost actualizată.'));
            exit;
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
    }
}

$content = getWorksPageContent($pdo);
$fields = [
    'back_text'=>'Link înapoi',
    'label'=>'Etichetă',
    'title'=>'Titlu pagină',
    'intro'=>'Descriere',
    'search_placeholder'=>'Placeholder căutare',
    'filter_all'=>'Text filtru Toate',
    'no_results'=>'Mesaj fără rezultate',
    'load_more'=>'Text Mai multe',
    'cta_label'=>'CTA — etichetă',
    'cta_title'=>'CTA — titlu',
    'cta_button'=>'CTA — buton',
];
?>
<!doctype html>
<html lang="ro">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Pagina Lucrări | ArtLife Admin</title>
<?php require __DIR__ . '/../_admin_styles.php'; ?>

<style>
.works-admin{width:min(950px,calc(100% - 32px));margin:auto;padding:34px 0 100px}
.panel{margin-top:18px;padding:20px;border:1px solid var(--border);border-radius:18px;background:var(--panel)}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.field{display:grid;gap:7px}
.field.full{grid-column:1/-1}
.field span{font-size:12px;font-weight:600}
.field textarea{min-height:100px}
.save{position:sticky;bottom:10px;margin-top:18px;padding:10px;display:flex;justify-content:flex-end;background:rgba(6,9,7,.94);border:1px solid var(--border);border-radius:13px}
.note{margin-top:16px;color:var(--muted);font-size:12px}
@media(max-width:700px){.grid{grid-template-columns:1fr}.field.full{grid-column:auto}}
</style>
</head>
<body>

<header class="topbar">
  <a class="brand" href="../index.php">ArtLife <span>Admin</span></a>
  <div class="top-actions">
    <a class="btn btn-ghost" href="../homepage/services.php">Servicii</a>
    <a class="btn btn-ghost" href="../index.php">Dashboard</a>
  </div>
</header>

<main class="works-admin">
  <span class="eyebrow">PORTOFOLIU</span>
  <h1>Pagina Lucrări</h1>
  <p>Textele paginii. Filtrele se generează automat din Servicii.</p>

  <?php if ($success): ?><div class="notice"><?= e($success) ?></div><?php endif; ?>
  <?php if ($error): ?><div class="error-box"><?= e($error) ?></div><?php endif; ?>

  <form method="post">
    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

    <section class="panel">
      <div class="grid">
        <?php foreach ($fields as $key=>$label): ?>
          <?php $long = in_array($key,['intro','cta_title'],true); ?>
          <label class="field <?= $long?'full':'' ?>">
            <span><?= e($label) ?></span>
            <?php if ($long): ?>
              <textarea name="content[<?= e($key) ?>]"><?= e($content[$key]) ?></textarea>
            <?php else: ?>
              <input name="content[<?= e($key) ?>]" value="<?= e($content[$key]) ?>">
            <?php endif; ?>
          </label>
        <?php endforeach; ?>
      </div>

      <p class="note">
        Serviciile și filtrele se administrează o singură dată din
        Pagina principală → Servicii.
      </p>
    </section>

    <div class="save">
      <button class="btn btn-primary" type="submit">Salvează pagina Lucrări</button>
    </div>
  </form>
</main>
</body>
</html>