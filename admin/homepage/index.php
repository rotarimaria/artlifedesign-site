<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/homepage_content.php';

requireAdmin();

$fieldsBySection = homepageFields();
$content = getHomepageContent($pdo);
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
        $error = 'Sesiunea a expirat. Reîncarcă pagina.';
    } else {
        try {
            $pdo->beginTransaction();

            foreach ($fieldsBySection as $fields) {
                foreach ($fields as $key => $definition) {
                    $type = (string) $definition['type'];

                    if (in_array($type, ['image', 'video'], true)) {
                        if (
                            isset($_FILES['media']['error'][$key]) &&
                            (int) $_FILES['media']['error'][$key] !== UPLOAD_ERR_NO_FILE
                        ) {
                            $file = [
                                'name' => $_FILES['media']['name'][$key] ?? '',
                                'type' => $_FILES['media']['type'][$key] ?? '',
                                'tmp_name' => $_FILES['media']['tmp_name'][$key] ?? '',
                                'error' => $_FILES['media']['error'][$key] ?? UPLOAD_ERR_NO_FILE,
                                'size' => $_FILES['media']['size'][$key] ?? 0,
                            ];

                            $path = saveHomepageUpload($file, $type);
                            saveHomepageValue($pdo, $key, $path);
                        }

                        continue;
                    }

                    $value = trim((string) ($_POST['content'][$key] ?? ''));
                    saveHomepageValue($pdo, $key, $value);
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
}

$content = getHomepageContent($pdo);

if (isset($_GET['success'])) {
    $success = trim((string) $_GET['success']);
}

function adminE(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="ro">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Pagina principală | ArtLife Admin</title>

    <?php require __DIR__ . '/../_admin_styles.php'; ?>

    <style>
        .home-admin-layout{
            width:min(1240px,calc(100% - 32px));
            margin:0 auto;
            padding:34px 0 80px;
        }
        .home-admin-head{
            display:flex;
            justify-content:space-between;
            align-items:flex-end;
            gap:20px;
            margin-bottom:26px;
        }
        .home-admin-head h1{
            margin:7px 0 8px;
        }
        .home-admin-head p{
            margin:0;
            color:var(--muted);
            max-width:700px;
        }
        .home-sections{
            display:grid;
            gap:16px;
        }
        .home-section{
            border:1px solid var(--border);
            border-radius:18px;
            background:var(--panel);
            overflow:hidden;
        }
        .home-section summary{
            cursor:pointer;
            list-style:none;
            padding:18px 20px;
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:12px;
            font-weight:500;
        }
        .home-section summary::-webkit-details-marker{
            display:none;
        }
        .home-section summary::after{
            content:"+";
            color:var(--green);
            font-size:20px;
            font-weight:400;
        }
        .home-section[open] summary::after{
            content:"−";
        }
        .home-section-body{
            border-top:1px solid var(--border);
            padding:20px;
        }
        .home-fields{
            display:grid;
            grid-template-columns:repeat(2,minmax(0,1fr));
            gap:16px;
        }
        .home-field{
            display:grid;
            gap:7px;
        }
        .home-field.full{
            grid-column:1/-1;
        }
        .home-field label{
            font-size:12px;
            font-weight:500;
        }
        .home-field textarea{
            min-height:110px;
        }
        .media-current{
            min-height:120px;
            border:1px solid var(--border);
            border-radius:12px;
            overflow:hidden;
            background:#050705;
            display:grid;
            place-items:center;
        }
        .media-current img,
        .media-current video{
            width:100%;
            height:180px;
            object-fit:contain;
            background:#000;
            display:block;
        }
        .media-path{
            padding:8px 10px;
            color:var(--muted);
            font-size:10px;
            overflow-wrap:anywhere;
        }
        .media-upload{
            position:relative;
            min-height:44px;
            display:flex;
            align-items:center;
            justify-content:center;
            padding:0 12px;
            border:1px dashed rgba(245,242,233,.25);
            border-radius:10px;
            color:var(--muted);
            font-size:12px;
        }
        .media-upload input{
            position:absolute;
            inset:0;
            opacity:0;
            cursor:pointer;
        }
        .save-bar{
            position:sticky;
            bottom:14px;
            z-index:30;
            margin-top:20px;
            display:flex;
            justify-content:flex-end;
            gap:10px;
            padding:12px;
            border:1px solid var(--border);
            border-radius:14px;
            background:rgba(8,11,9,.94);
            backdrop-filter:blur(14px);
        }
        .works-note{
            padding:14px 16px;
            border:1px solid rgba(146,255,34,.18);
            border-radius:12px;
            background:rgba(146,255,34,.045);
            color:#cfff9e;
            font-size:12px;
            line-height:1.55;
            margin-bottom:18px;
        }
        @media(max-width:760px){
            .home-admin-head{
                align-items:stretch;
                flex-direction:column;
            }
            .home-fields{
                grid-template-columns:1fr;
            }
            .home-field.full{
                grid-column:auto;
            }
        }
    </style>
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

<main class="home-admin-layout">

    <div class="home-admin-head">
        <div>
            <span class="eyebrow">Homepage</span>
            <h1>Pagina principală</h1>
            <p>
                Modifică textele, imaginile, video-ul și datele de contact.
                Secțiunea Lucrări este administrată separat din Lucrări / Carduri.
            </p>
        </div>
    </div>

    <div class="works-note">
        Secțiunea <strong>Lucrări</strong> nu este editată aici.
        Proiectele publicate sunt încărcate automat din baza de date.
    </div>

    <?php if ($success !== ''): ?>
        <div class="notice"><?= adminE($success) ?></div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="error-box"><?= adminE($error) ?></div>
    <?php endif; ?>

    <form
        method="post"
        enctype="multipart/form-data"
    >
        <input
            type="hidden"
            name="csrf_token"
            value="<?= adminE(csrfToken()) ?>"
        >

        <div class="home-sections">
            <?php foreach ($fieldsBySection as $sectionName => $fields): ?>
                <details class="home-section">
                    <summary><?= adminE($sectionName) ?></summary>

                    <div class="home-section-body">
                        <div class="home-fields">

                            <?php foreach ($fields as $key => $definition): ?>
                                <?php
                                    $type = (string) $definition['type'];
                                    $value = (string) ($content[$key] ?? '');
                                    $isLong = $type === 'textarea';
                                    $isMedia = in_array($type, ['image', 'video'], true);
                                ?>

                                <div class="home-field <?= ($isLong || $isMedia) ? 'full' : '' ?>">
                                    <label>
                                        <?= adminE((string) $definition['label']) ?>
                                    </label>

                                    <?php if ($type === 'textarea'): ?>
                                        <textarea
                                            name="content[<?= adminE($key) ?>]"
                                        ><?= adminE($value) ?></textarea>

                                    <?php elseif ($type === 'image'): ?>
                                        <div class="media-current">
                                            <?php if ($value !== ''): ?>
                                                <img
                                                    src="../<?= adminE($value) ?>"
                                                    alt=""
                                                >
                                            <?php endif; ?>
                                        </div>

                                        <div class="media-path">
                                            Curent: <?= adminE($value) ?>
                                        </div>

                                        <div class="media-upload">
                                            Încarcă o imagine nouă
                                            <input
                                                type="file"
                                                name="media[<?= adminE($key) ?>]"
                                                accept="image/jpeg,image/png,image/webp,image/gif,image/svg+xml"
                                            >
                                        </div>

                                    <?php elseif ($type === 'video'): ?>
                                        <div class="media-current">
                                            <?php if ($value !== ''): ?>
                                                <video
                                                    src="../<?= adminE($value) ?>"
                                                    muted
                                                    loop
                                                    autoplay
                                                    playsinline
                                                    controls
                                                ></video>
                                            <?php endif; ?>
                                        </div>

                                        <div class="media-path">
                                            Curent: <?= adminE($value) ?>
                                        </div>

                                        <div class="media-upload">
                                            Încarcă un video nou
                                            <input
                                                type="file"
                                                name="media[<?= adminE($key) ?>]"
                                                accept="video/mp4,video/webm,video/quicktime"
                                            >
                                        </div>

                                    <?php else: ?>
                                        <input
                                            type="<?= $type === 'url' ? 'url' : 'text' ?>"
                                            name="content[<?= adminE($key) ?>]"
                                            value="<?= adminE($value) ?>"
                                        >
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>

                        </div>
                    </div>
                </details>
            <?php endforeach; ?>
        </div>

        <div class="save-bar">
            <a class="btn" href="../../index.html" target="_blank" rel="noopener">
                Previzualizează
            </a>

            <button class="btn btn-primary" type="submit">
                Salvează pagina principală
            </button>
        </div>
    </form>

</main>

</body>
</html>