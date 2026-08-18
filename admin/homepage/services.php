<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/services.php';

requireAdmin();

$error = '';
$success = trim((string)($_GET['success'] ?? ''));

// Escapez textele înainte să le afișez.
function h(string $v): string {
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}

// Pregătesc imaginea selectată.
function uploadAt(int $slot): ?array {
    if (!isset($_FILES['images']['error'][$slot])) return null;
    if ((int)$_FILES['images']['error'][$slot] === UPLOAD_ERR_NO_FILE) return null;

    return [
        'name'=>$_FILES['images']['name'][$slot] ?? '',
        'type'=>$_FILES['images']['type'][$slot] ?? '',
        'tmp_name'=>$_FILES['images']['tmp_name'][$slot] ?? '',
        'error'=>$_FILES['images']['error'][$slot] ?? UPLOAD_ERR_NO_FILE,
        'size'=>$_FILES['images']['size'][$slot] ?? 0,
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
        $error = 'Sesiunea a expirat.';
    } else {
        try {
            $id = (int)($_POST['id'] ?? 0);
            $action = (string)($_POST['action'] ?? 'save');

            // Șterg serviciul doar dacă nu este folosit de proiecte.
            if ($action === 'delete') {
                $stmt = $pdo->prepare('SELECT slug, name FROM services WHERE id = ?');
                $stmt->execute([$id]);
                $service = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$service) {
                    throw new RuntimeException('Serviciul nu există.');
                }

                $stmt = $pdo->prepare('SELECT COUNT(*) FROM projects WHERE category = ?');
                $stmt->execute([$service['slug']]);
                $used = (int)$stmt->fetchColumn();

                if ($used > 0) {
                    throw new RuntimeException(
                        'Nu pot elimina serviciul deoarece are ' . $used .
                        ' proiect(e). Mută sau șterge proiectele din această categorie mai întâi.'
                    );
                }

                $stmt = $pdo->prepare('DELETE FROM services WHERE id = ?');
                $stmt->execute([$id]);

                header('Location: services.php?success=' .
                    rawurlencode('Serviciul a fost eliminat.'));
                exit;
            }

            $name = trim((string)($_POST['name'] ?? ''));
            $slug = $id > 0
                ? trim((string)($_POST['slug'] ?? ''))
                : serviceSlug($name);

            if ($name === '' || $slug === '') {
                throw new RuntimeException('Numele serviciului este obligatoriu.');
            }

            $existing = null;
            if ($id > 0) {
                $stmt = $pdo->prepare('SELECT * FROM services WHERE id = ?');
                $stmt->execute([$id]);
                $existing = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$existing) throw new RuntimeException('Serviciul nu există.');
            }

            $images = [];
            for ($i=1; $i<=3; $i++) {
                $images[$i] = $existing["image{$i}"] ?? '';
                if ($file = uploadAt($i)) {
                    $images[$i] = saveServiceUpload($file);
                }
            }

            $fields = [
                'slug'=>$slug,
                'name'=>$name,
                'card_title'=>trim((string)($_POST['card_title'] ?? '')),
                'card_text'=>trim((string)($_POST['card_text'] ?? '')),
                'detail_title'=>trim((string)($_POST['detail_title'] ?? '')),
                'detail_text'=>trim((string)($_POST['detail_text'] ?? '')),
                'examples'=>trim((string)($_POST['examples'] ?? '')),
                'btn_examples'=>trim((string)($_POST['btn_examples'] ?? 'Vezi exemple')),
                'btn_quote'=>trim((string)($_POST['btn_quote'] ?? 'Solicită o ofertă')),
                'sort_order'=>(int)($_POST['sort_order'] ?? 0),
                'is_active'=>isset($_POST['is_active']) ? 1 : 0,
            ];

            for ($i=1; $i<=3; $i++) {
                $fields["image{$i}"] = $images[$i];
                foreach (['crop_x','crop_y','zoom','rotation','fit'] as $m) {
                    $fields["image{$i}_{$m}"] =
                        trim((string)($_POST["image{$i}_{$m}"] ?? (
                            $m === 'fit' ? 'cover' :
                            ($m === 'zoom' ? '1' :
                            ($m === 'rotation' ? '0' : '50'))
                        )));
                }
            }

            if ($id > 0) {
                $sets = implode(',', array_map(fn($k)=>"$k=:$k", array_keys($fields)));
                $fields['id'] = $id;
                $stmt = $pdo->prepare("UPDATE services SET $sets WHERE id=:id");
                $stmt->execute($fields);
            } else {
                $cols = implode(',', array_keys($fields));
                $vals = implode(',', array_map(fn($k)=>":$k", array_keys($fields)));
                $stmt = $pdo->prepare("INSERT INTO services ($cols) VALUES ($vals)");
                $stmt->execute($fields);
            }

            header('Location: services.php?success=' . rawurlencode('Serviciile au fost actualizate.'));
            exit;
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
    }
}

$services = getServices($pdo);

function mediaCard(array $s, int $i): void {
    $src = (string)($s["image{$i}"] ?? '');
    $x = (string)($s["image{$i}_crop_x"] ?? '50');
    $y = (string)($s["image{$i}_crop_y"] ?? '50');
    $z = (string)($s["image{$i}_zoom"] ?? '1');
    $r = (string)($s["image{$i}_rotation"] ?? '0');
    $fit = (string)($s["image{$i}_fit"] ?? 'cover');
?>
<article class="media-card" data-media-card>
  <div class="media-card-head">
    <strong>Imagine <?= $i ?></strong>
    <button class="mini-btn" type="button" data-adjust-media>Ajustează</button>
  </div>

  <div class="media-preview" data-media-preview>
    <img
      <?= $src !== '' ? 'src="../../' . h($src) . '"' : '' ?>
      alt=""
      data-preview-element
      style="
        --crop-x:<?= h($x) ?>%;
        --crop-y:<?= h($y) ?>%;
        --crop-zoom:<?= h($z) ?>;
        --crop-rotation:<?= h($r) ?>deg;
        --crop-fit:<?= h($fit) ?>;
      "
    >
  </div>

  <div class="media-card-foot">
    <label class="upload-btn">
      Schimbă imaginea
      <input type="file" name="images[<?= $i ?>]" accept="image/*" data-media-input>
    </label>
    <small data-file-name><?= h(basename($src)) ?></small>
  </div>

  <input type="hidden" name="image<?= $i ?>_crop_x" value="<?= h($x) ?>" data-meta="crop_x">
  <input type="hidden" name="image<?= $i ?>_crop_y" value="<?= h($y) ?>" data-meta="crop_y">
  <input type="hidden" name="image<?= $i ?>_zoom" value="<?= h($z) ?>" data-meta="zoom">
  <input type="hidden" name="image<?= $i ?>_rotation" value="<?= h($r) ?>" data-meta="rotation">
  <input type="hidden" name="image<?= $i ?>_fit" value="<?= h($fit) ?>" data-meta="fit">
</article>
<?php
}
?>
<!doctype html>
<html lang="ro">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Servicii | ArtLife Admin</title>

<?php require __DIR__ . '/../_admin_styles.php'; ?>
<link rel="stylesheet" href="style.css?v=services1">

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
@media(max-width:700px){.service-fields{grid-template-columns:1fr}.service-fields label.full{grid-column:auto}}
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
  <span class="eyebrow">SERVICII</span>
  <h1>Servicii</h1>
  <p>Un serviciu adăugat aici apare automat în homepage, filtre și formularele de proiect.</p>

  <?php if ($success): ?><div class="notice"><?= h($success) ?></div><?php endif; ?>
  <?php if ($error): ?><div class="error-box"><?= h($error) ?></div><?php endif; ?>

  <details class="service-form add-service">
    <summary>+ Adaugă serviciu nou</summary>

    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
      <input type="hidden" name="is_active" value="1">

      <div class="service-fields">
        <label><span>Nume serviciu</span><input name="name" required></label>
        <label><span>Ordine</span><input type="number" name="sort_order" value="100"></label>
        <label class="full"><span>Titlu card</span><input name="card_title"></label>
        <label class="full"><span>Descriere card</span><textarea name="card_text"></textarea></label>
        <label class="full"><span>Titlu detalii</span><input name="detail_title"></label>
        <label class="full"><span>Descriere detalii</span><textarea name="detail_text"></textarea></label>
        <label class="full"><span>Exemple — câte unul pe rând</span><textarea name="examples"></textarea></label>
      </div>

      <div class="service-media-grid media-grid">
        <?php
          $blank = [];
          for ($i=1;$i<=3;$i++) mediaCard($blank,$i);
        ?>
      </div>

      <div class="service-actions">
        <button class="btn btn-primary" type="submit" name="action" value="save">Adaugă serviciul</button>
      </div>
    </form>
  </details>

  <?php foreach ($services as $s): ?>
    <details class="service-form">
      <summary><?= h((string)$s['name']) ?></summary>

      <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
        <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
        <input type="hidden" name="slug" value="<?= h((string)$s['slug']) ?>">

        <div class="service-fields">
          <label><span>Nume serviciu</span><input name="name" value="<?= h((string)$s['name']) ?>"></label>
          <label><span>Ordine</span><input type="number" name="sort_order" value="<?= (int)$s['sort_order'] ?>"></label>
          <label class="full"><span>Titlu card</span><input name="card_title" value="<?= h((string)$s['card_title']) ?>"></label>
          <label class="full"><span>Descriere card</span><textarea name="card_text"><?= h((string)$s['card_text']) ?></textarea></label>
          <label class="full"><span>Titlu detalii</span><input name="detail_title" value="<?= h((string)$s['detail_title']) ?>"></label>
          <label class="full"><span>Descriere detalii</span><textarea name="detail_text"><?= h((string)$s['detail_text']) ?></textarea></label>
          <label class="full"><span>Exemple — câte unul pe rând</span><textarea name="examples"><?= h((string)$s['examples']) ?></textarea></label>
          <label><span>Buton exemple</span><input name="btn_examples" value="<?= h((string)$s['btn_examples']) ?>"></label>
          <label><span>Buton ofertă</span><input name="btn_quote" value="<?= h((string)$s['btn_quote']) ?>"></label>
          <label><span>Activ</span><input type="checkbox" name="is_active" <?= (int)$s['is_active']===1?'checked':'' ?>></label>
        </div>

        <div class="service-media-grid media-grid">
          <?php for ($i=1;$i<=3;$i++) mediaCard($s,$i); ?>
        </div>

        <div class="service-actions">
          <button class="btn btn-ghost" type="submit" name="action" value="delete"
          onclick="return confirm('Sigur vrei să elimini acest serviciu?')">
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

<script src="homepage.js?v=services1"></script>
</body>
</html>