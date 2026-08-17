<?php
declare(strict_types=1);

/**
 * Definiția centrală a câmpurilor editabile din homepage.
 * Secțiunea Lucrări NU este inclusă aici intenționat.
 */
function homepageFields(): array
{
    return [
        'Navigație' => [
            'nav_about' => ['label' => 'Meniu — Despre noi', 'type' => 'text', 'default' => 'Despre noi'],
            'nav_services' => ['label' => 'Meniu — Servicii', 'type' => 'text', 'default' => 'Servicii'],
            'nav_works' => ['label' => 'Meniu — Lucrări', 'type' => 'text', 'default' => 'Lucrări'],
            'nav_contact' => ['label' => 'Meniu — Contact', 'type' => 'text', 'default' => 'Contact'],
            'nav_cta' => ['label' => 'Buton meniu', 'type' => 'text', 'default' => 'Solicită ofertă'],
            'header_logo' => ['label' => 'Logo header', 'type' => 'image', 'default' => 'images/LOGO-antracit-grey.svg'],
        ],

        'Hero' => [
            'hero_kicker' => ['label' => 'Text mic deasupra titlului', 'type' => 'text', 'default' => 'Producție publicitară · Chișinău'],
            'hero_title_main' => ['label' => 'Titlu — partea 1', 'type' => 'text', 'default' => 'ArtLife'],
            'hero_title_accent' => ['label' => 'Titlu — partea evidențiată', 'type' => 'text', 'default' => 'Design'],
            'hero_text' => ['label' => 'Descriere hero', 'type' => 'textarea', 'default' => 'Publicitate vizuală și producție personalizată pentru branduri care vor să fie remarcate.'],
            'hero_btn_works' => ['label' => 'Buton — Lucrări', 'type' => 'text', 'default' => 'Vezi lucrările'],
            'hero_btn_quote' => ['label' => 'Buton — Ofertă', 'type' => 'text', 'default' => 'Solicită o ofertă'],
            'hero_bottom_label' => ['label' => 'Text jos hero', 'type' => 'text', 'default' => 'Art Life Design'],
            'hero_video' => ['label' => 'Video fundal hero', 'type' => 'video', 'default' => 'images/homeslider.mp4'],
            'hero_poster' => ['label' => 'Imagine poster hero', 'type' => 'image', 'default' => 'images/fise_pret.jpg'],
        ],

        'Despre noi' => [
            'about_label' => ['label' => 'Etichetă', 'type' => 'text', 'default' => 'Despre noi'],
            'about_title' => ['label' => 'Titlu', 'type' => 'text', 'default' => 'Publicitate de orice fel, pentru orice buget.'],
            'about_lead' => ['label' => 'Paragraf principal', 'type' => 'textarea', 'default' => 'Art Life Design este o agenție de publicitate dinamică, orientată spre soluții vizuale clare, creative și eficiente pentru fiecare brand.'],
            'about_text' => ['label' => 'Paragraf secundar', 'type' => 'textarea', 'default' => 'Realizăm proiecte de publicitate vizuală, materiale promoționale și soluții personalizate pentru companii și afaceri locale.'],
            'about_btn_services' => ['label' => 'Buton servicii', 'type' => 'text', 'default' => 'Vezi serviciile'],
            'about_btn_portfolio' => ['label' => 'Buton portofoliu', 'type' => 'text', 'default' => 'Vezi portofoliul'],
            'about_image' => ['label' => 'Imagine', 'type' => 'image', 'default' => 'images/litere_volum1.jpg'],
            'about_image_note' => ['label' => 'Text peste imagine', 'type' => 'text', 'default' => 'Art Life Design · Proiecte realizate'],
        ],

        'Servicii — introducere' => [
            'services_label' => ['label' => 'Etichetă', 'type' => 'text', 'default' => 'Servicii'],
            'services_title' => ['label' => 'Titlu', 'type' => 'text', 'default' => 'Ce putem realiza'],
            'services_text' => ['label' => 'Descriere', 'type' => 'textarea', 'default' => 'Alege serviciul care te interesează sau explorează lucrările realizate.'],
        ],

        'Serviciu — Poligrafie' => [
            'poligrafie_name' => ['label' => 'Nume serviciu', 'type' => 'text', 'default' => 'Poligrafie'],
            'poligrafie_title' => ['label' => 'Titlu card', 'type' => 'text', 'default' => 'Print, stickere și materiale promoționale'],
            'poligrafie_short' => ['label' => 'Descriere card', 'type' => 'textarea', 'default' => 'Etichete, bannere, meniuri, fișe de preț și materiale tipărite.'],
            'poligrafie_detail_title' => ['label' => 'Titlu detalii', 'type' => 'text', 'default' => 'Materiale tipărite pentru prezentare, promovare și vânzare'],
            'poligrafie_detail_text' => ['label' => 'Text detalii', 'type' => 'textarea', 'default' => 'Poligrafia acoperă materialele tipărite folosite pentru prezentare, informare și promovare. Este potrivită atât pentru utilizarea zilnică într-un magazin sau restaurant, cât și pentru campanii, lansări și evenimente. Formatul și tipul lucrării se aleg în funcție de locul în care materialul va fi folosit.'],
            'poligrafie_examples' => ['label' => 'Exemple — câte unul pe rând', 'type' => 'textarea', 'default' => "Etichete și stickere pentru produse\nMeniuri și fișe de preț\nBannere și materiale promoționale\nPrint personalizat pentru campanii"],
            'poligrafie_btn_examples' => ['label' => 'Buton exemple', 'type' => 'text', 'default' => 'Vezi exemple'],
            'poligrafie_btn_quote' => ['label' => 'Buton ofertă', 'type' => 'text', 'default' => 'Solicită o ofertă'],
            'poligrafie_image_1' => ['label' => 'Imagine 1', 'type' => 'image', 'default' => 'images/fise_pret.jpg'],
            'poligrafie_image_2' => ['label' => 'Imagine 2', 'type' => 'image', 'default' => 'images/inghetata_uv.jpg'],
            'poligrafie_image_3' => ['label' => 'Imagine 3', 'type' => 'image', 'default' => 'images/tipar_mare.jpg'],
        ],

        'Serviciu — Litere & Standuri' => [
            'volum_name' => ['label' => 'Nume serviciu', 'type' => 'text', 'default' => 'Litere în volum & Standuri'],
            'volum_title' => ['label' => 'Titlu card', 'type' => 'text', 'default' => 'Elemente vizuale pentru fațade și interior'],
            'volum_short' => ['label' => 'Descriere card', 'type' => 'textarea', 'default' => 'Litere volumetrice, light box-uri, standuri și casete luminoase.'],
            'volum_detail_title' => ['label' => 'Titlu detalii', 'type' => 'text', 'default' => 'Elemente vizuale care fac brandul ușor de observat'],
            'volum_detail_text' => ['label' => 'Text detalii', 'type' => 'textarea', 'default' => 'Literele volumetrice și standurile transformă identitatea vizuală într-un element fizic ușor de remarcat. Sunt potrivite pentru fațade, recepții, spații comerciale, expoziții și zone de prezentare, pornind de la logo-ul și stilul vizual al brandului.'],
            'volum_examples' => ['label' => 'Exemple — câte unul pe rând', 'type' => 'textarea', 'default' => "Litere volumetrice pentru fațadă\nLight box-uri și casete luminoase\nLogo-uri pentru interior\nStanduri și elemente de prezentare"],
            'volum_btn_examples' => ['label' => 'Buton exemple', 'type' => 'text', 'default' => 'Vezi exemple'],
            'volum_btn_quote' => ['label' => 'Buton ofertă', 'type' => 'text', 'default' => 'Solicită o ofertă'],
            'volum_image_1' => ['label' => 'Imagine 1', 'type' => 'image', 'default' => 'images/BonjourLightBox.jpg'],
            'volum_image_2' => ['label' => 'Imagine 2', 'type' => 'image', 'default' => 'images/good-break.jpg'],
            'volum_image_3' => ['label' => 'Imagine 3', 'type' => 'image', 'default' => 'images/litere_volum1.jpg'],
        ],

        'Serviciu — P.O.S.M.' => [
            'posm_name' => ['label' => 'Nume serviciu', 'type' => 'text', 'default' => 'P.O.S.M.'],
            'posm_title' => ['label' => 'Titlu card', 'type' => 'text', 'default' => 'Materiale pentru retail și campanii'],
            'posm_short' => ['label' => 'Descriere card', 'type' => 'textarea', 'default' => 'Display-uri, urne, suporturi, standuri și produse personalizate.'],
            'posm_detail_title' => ['label' => 'Titlu detalii', 'type' => 'text', 'default' => 'Materiale pentru expunere, retail și campanii promoționale'],
            'posm_detail_text' => ['label' => 'Text detalii', 'type' => 'textarea', 'default' => 'P.O.S.M. înseamnă materiale vizuale folosite direct în punctul de vânzare sau în campanii promoționale. Ele ajută la prezentarea produselor, evidențierea ofertelor și organizarea mesajului într-un mod ușor de observat și de înțeles de către client.'],
            'posm_examples' => ['label' => 'Exemple — câte unul pe rând', 'type' => 'textarea', 'default' => "Display-uri și suporturi\nUrne și box-uri personalizate\nStanduri promoționale\nElemente pentru campanii și retail"],
            'posm_btn_examples' => ['label' => 'Buton exemple', 'type' => 'text', 'default' => 'Vezi exemple'],
            'posm_btn_quote' => ['label' => 'Buton ofertă', 'type' => 'text', 'default' => 'Solicită o ofertă'],
            'posm_image_1' => ['label' => 'Imagine 1', 'type' => 'image', 'default' => 'images/box.jpg'],
            'posm_image_2' => ['label' => 'Imagine 2', 'type' => 'image', 'default' => 'images/cub-sticla.jpg'],
            'posm_image_3' => ['label' => 'Imagine 3', 'type' => 'image', 'default' => 'images/stand sticla.jpg'],
        ],

        'Serviciu — Branding Auto' => [
            'auto_name' => ['label' => 'Nume serviciu', 'type' => 'text', 'default' => 'Branding Auto'],
            'auto_title' => ['label' => 'Titlu card', 'type' => 'text', 'default' => 'Promovare vizibilă pe mașini și flote'],
            'auto_short' => ['label' => 'Descriere card', 'type' => 'textarea', 'default' => 'Colantări auto pentru transport comercial, microbuze și automobile.'],
            'auto_detail_title' => ['label' => 'Titlu detalii', 'type' => 'text', 'default' => 'Transformă transportul companiei într-un suport vizibil de promovare'],
            'auto_detail_text' => ['label' => 'Text detalii', 'type' => 'textarea', 'default' => 'Brandingul auto transformă automobilul sau flota companiei într-un suport vizual de promovare. Grafica poate integra logo-ul, datele de contact și elementele principale ale identității vizuale, astfel încât marca să fie ușor de recunoscut în trafic și în deplasările zilnice.'],
            'auto_examples' => ['label' => 'Exemple — câte unul pe rând', 'type' => 'textarea', 'default' => "Automobile pentru companii\nMicrobuze și transport comercial\nElemente grafice și date de contact\nBranding pentru mai multe unități din flotă"],
            'auto_btn_examples' => ['label' => 'Buton exemple', 'type' => 'text', 'default' => 'Vezi exemple'],
            'auto_btn_quote' => ['label' => 'Buton ofertă', 'type' => 'text', 'default' => 'Solicită o ofertă'],
            'auto_image_1' => ['label' => 'Imagine 1', 'type' => 'image', 'default' => 'images/brand_auto1.jpg'],
            'auto_image_2' => ['label' => 'Imagine 2', 'type' => 'image', 'default' => 'images/brand_auto2.jpg'],
            'auto_image_3' => ['label' => 'Imagine 3', 'type' => 'image', 'default' => 'images/lux-tavene.jpg'],
        ],

        'Serviciu — Laser / Plotter' => [
            'laser_name' => ['label' => 'Nume serviciu', 'type' => 'text', 'default' => 'Laser / Plotter'],
            'laser_title' => ['label' => 'Titlu card', 'type' => 'text', 'default' => 'Tăiere, gravare și decupare personalizată'],
            'laser_short' => ['label' => 'Descriere card', 'type' => 'textarea', 'default' => 'Servicii pentru proiecte promoționale, decorative și tehnice.'],
            'laser_detail_title' => ['label' => 'Titlu detalii', 'type' => 'text', 'default' => 'Tăiere, gravare și decupare pentru proiecte personalizate'],
            'laser_detail_text' => ['label' => 'Text detalii', 'type' => 'textarea', 'default' => 'Laserul și plotterul sunt folosite pentru proiecte în care contează forma, conturul și precizia decupării. Sunt potrivite pentru inscripții, stickere, elemente promoționale, forme decorative și lucrări personalizate. Echipa verifică proiectul înainte de producție pentru alegerea soluției potrivite.'],
            'laser_examples' => ['label' => 'Exemple — câte unul pe rând', 'type' => 'textarea', 'default' => "Stickere și elemente decupate\nForme și inscripții personalizate\nElemente promoționale și decorative\nProiecte care necesită tăiere sau gravare"],
            'laser_btn_examples' => ['label' => 'Buton exemple', 'type' => 'text', 'default' => 'Vezi exemple'],
            'laser_btn_quote' => ['label' => 'Buton ofertă', 'type' => 'text', 'default' => 'Solicită o ofertă'],
            'laser_image_1' => ['label' => 'Imagine 1', 'type' => 'image', 'default' => 'images/taiere_laser.jpg'],
            'laser_image_2' => ['label' => 'Imagine 2', 'type' => 'image', 'default' => 'images/taiere_plotter.jpg'],
            'laser_image_3' => ['label' => 'Imagine 3', 'type' => 'image', 'default' => 'images/plotter-2.jpg'],
        ],

        'CTA proiect' => [
            'cta_label' => ['label' => 'Etichetă', 'type' => 'text', 'default' => 'Ai un proiect?'],
            'cta_title' => ['label' => 'Titlu', 'type' => 'text', 'default' => 'Spune-ne ce vrei să realizezi.'],
            'cta_text' => ['label' => 'Descriere', 'type' => 'textarea', 'default' => 'Trimite-ne o scurtă descriere, iar echipa Art Life Design revine cu detaliile necesare.'],
            'cta_button' => ['label' => 'Buton', 'type' => 'text', 'default' => 'Trimite o solicitare'],
        ],

        'Social' => [
            'social_label' => ['label' => 'Etichetă', 'type' => 'text', 'default' => 'Urmărește Art Life Design'],
            'social_title' => ['label' => 'Titlu', 'type' => 'text', 'default' => 'Proiecte noi, lucrări și noutăți.'],
            'instagram_name' => ['label' => 'Instagram — nume', 'type' => 'text', 'default' => 'Instagram'],
            'instagram_handle' => ['label' => 'Instagram — cont', 'type' => 'text', 'default' => '@design.artlife'],
            'instagram_url' => ['label' => 'Instagram — link', 'type' => 'url', 'default' => 'https://www.instagram.com/design.artlife/'],
            'tiktok_name' => ['label' => 'TikTok — nume', 'type' => 'text', 'default' => 'TikTok'],
            'tiktok_handle' => ['label' => 'TikTok — cont', 'type' => 'text', 'default' => '@art.life.design'],
            'tiktok_url' => ['label' => 'TikTok — link', 'type' => 'url', 'default' => 'https://www.tiktok.com/@art.life.design'],
            'facebook_name' => ['label' => 'Facebook — nume', 'type' => 'text', 'default' => 'Facebook'],
            'facebook_handle' => ['label' => 'Facebook — text', 'type' => 'text', 'default' => 'Art Life Design'],
            'facebook_url' => ['label' => 'Facebook — link', 'type' => 'url', 'default' => 'https://www.facebook.com/p/Art-Life-Design-100063504763244/'],
        ],

        'Contact' => [
            'contact_label' => ['label' => 'Etichetă', 'type' => 'text', 'default' => 'Contact'],
            'contact_title' => ['label' => 'Titlu', 'type' => 'text', 'default' => 'Ai un proiect în plan?'],
            'contact_text' => ['label' => 'Descriere', 'type' => 'textarea', 'default' => 'Trimite-ne un mesaj cu ceea ce vrei să realizezi. Echipa Art Life Design te va contacta pentru detalii.'],
            'contact_direct_label' => ['label' => 'Etichetă contact direct', 'type' => 'text', 'default' => 'Contact direct'],
            'contact_direct_title' => ['label' => 'Titlu contact direct', 'type' => 'text', 'default' => 'Art Life Design'],
            'contact_address' => ['label' => 'Adresă', 'type' => 'text', 'default' => 'str. Nicolae Milescu Spătaru, 30, Chișinău'],
            'contact_phone_1' => ['label' => 'Telefon 1', 'type' => 'text', 'default' => '+373 609 800 08'],
            'contact_phone_2' => ['label' => 'Telefon 2', 'type' => 'text', 'default' => '+373 608 000 13'],
            'contact_email_1' => ['label' => 'Email 1', 'type' => 'text', 'default' => 'info.artlifedesign@gmail.com'],
            'contact_email_2' => ['label' => 'Email 2', 'type' => 'text', 'default' => 'office.artlifedesign@gmail.com'],
            'contact_hours' => ['label' => 'Program', 'type' => 'text', 'default' => 'Luni - Vineri: 09:00 - 18:00'],
            'contact_map_text' => ['label' => 'Text Google Maps', 'type' => 'text', 'default' => 'Deschide locația în Google Maps'],
            'form_name_label' => ['label' => 'Formular — Nume', 'type' => 'text', 'default' => 'Nume și prenume*'],
            'form_name_placeholder' => ['label' => 'Placeholder nume', 'type' => 'text', 'default' => 'Numele tău'],
            'form_phone_label' => ['label' => 'Formular — Telefon', 'type' => 'text', 'default' => 'Telefon*'],
            'form_phone_placeholder' => ['label' => 'Placeholder telefon', 'type' => 'text', 'default' => '+373 ...'],
            'form_email_label' => ['label' => 'Formular — Email', 'type' => 'text', 'default' => 'Email*'],
            'form_email_placeholder' => ['label' => 'Placeholder email', 'type' => 'text', 'default' => 'email@exemplu.com'],
            'form_service_label' => ['label' => 'Formular — Serviciu', 'type' => 'text', 'default' => 'Serviciu'],
            'form_message_label' => ['label' => 'Formular — Mesaj', 'type' => 'text', 'default' => 'Mesaj*'],
            'form_message_placeholder' => ['label' => 'Placeholder mesaj', 'type' => 'textarea', 'default' => 'Descrie pe scurt proiectul sau rezultatul pe care îl dorești.'],
            'form_privacy' => ['label' => 'Text confidențialitate', 'type' => 'textarea', 'default' => '* Prin trimiterea formularului, ești de acord cu prelucrarea datelor personale în scopul contactării ulterioare. Informațiile vor fi păstrate confidențial.'],
            'form_submit' => ['label' => 'Buton formular', 'type' => 'text', 'default' => 'Trimite mesajul'],
        ],

        'Footer' => [
            'footer_logo' => ['label' => 'Logo footer', 'type' => 'image', 'default' => 'images/LOGO-antracit-grey.svg'],
            'footer_text' => ['label' => 'Descriere footer', 'type' => 'textarea', 'default' => 'Publicitate vizuală pentru branduri care vor să iasă în evidență.'],
            'footer_nav_title' => ['label' => 'Titlu Navigare', 'type' => 'text', 'default' => 'Navigare'],
            'footer_social_title' => ['label' => 'Titlu Social', 'type' => 'text', 'default' => 'Urmărește-ne'],
        ],
    ];
}

function homepageDefaults(): array
{
    $defaults = [];

    foreach (homepageFields() as $fields) {
        foreach ($fields as $key => $definition) {
            $defaults[$key] = (string) $definition['default'];
        }
    }

    return $defaults;
}

function getHomepageContent(PDO $pdo): array
{
    $content = homepageDefaults();

    $stmt = $pdo->query('SELECT content_key, content_value FROM site_content');

    foreach ($stmt->fetchAll() as $row) {
        $key = (string) $row['content_key'];

        if (array_key_exists($key, $content)) {
            $content[$key] = (string) $row['content_value'];
        }
    }

    return $content;
}

function saveHomepageValue(PDO $pdo, string $key, string $value): void
{
    if (!array_key_exists($key, homepageDefaults())) {
        return;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO site_content (content_key, content_value)
         VALUES (:content_key, :content_value)
         ON DUPLICATE KEY UPDATE
            content_value = VALUES(content_value),
            updated_at = CURRENT_TIMESTAMP'
    );

    $stmt->execute([
        'content_key' => $key,
        'content_value' => $value,
    ]);
}

function homepageUploadDir(): string
{
    return dirname(__DIR__) . '/uploads/homepage';
}

function ensureHomepageUploadDir(): void
{
    $dir = homepageUploadDir();

    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        throw new RuntimeException('Nu s-a putut crea folderul uploads/homepage.');
    }
}

function saveHomepageUpload(array $file, string $expectedType): string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        throw new RuntimeException('Nu a fost selectat niciun fișier.');
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Fișierul nu s-a încărcat corect.');
    }

    $tmp = (string) ($file['tmp_name'] ?? '');
    $size = (int) ($file['size'] ?? 0);

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = (string) $finfo->file($tmp);

    $allowedImages = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
        'image/svg+xml' => 'svg',
    ];

    $allowedVideos = [
        'video/mp4' => 'mp4',
        'video/webm' => 'webm',
        'video/quicktime' => 'mov',
    ];

    $allowed = $expectedType === 'video'
        ? $allowedVideos
        : $allowedImages;

    if (!isset($allowed[$mime])) {
        throw new RuntimeException(
            $expectedType === 'video'
                ? 'Format video neacceptat. Folosește MP4, WEBM sau MOV.'
                : 'Format imagine neacceptat. Folosește JPG, PNG, WEBP, GIF sau SVG.'
        );
    }

    $max = $expectedType === 'video'
        ? 80 * 1024 * 1024
        : 8 * 1024 * 1024;

    if ($size <= 0 || $size > $max) {
        throw new RuntimeException(
            $expectedType === 'video'
                ? 'Videoul trebuie să aibă maximum 80 MB.'
                : 'Imaginea trebuie să aibă maximum 8 MB.'
        );
    }

    ensureHomepageUploadDir();

    $filename =
        'home-' .
        date('Ymd-His') .
        '-' .
        bin2hex(random_bytes(5)) .
        '.' .
        $allowed[$mime];

    $target = homepageUploadDir() . '/' . $filename;

    if (!move_uploaded_file($tmp, $target)) {
        throw new RuntimeException('Fișierul nu a putut fi salvat.');
    }

    return 'uploads/homepage/' . $filename;
}