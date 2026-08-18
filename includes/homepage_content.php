<?php
declare(strict_types=1);

// Câmpurile editabile pentru homepage. Lucrările sunt administrate separat.
function homepageFields(): array
{
    return [
        'Navigație' => [
            'nav_about' => ['Meniu — Despre noi', 'text', 'Despre noi'],
            'nav_services' => ['Meniu — Servicii', 'text', 'Servicii'],
            'nav_works' => ['Meniu — Lucrări', 'text', 'Lucrări'],
            'nav_contact' => ['Meniu — Contact', 'text', 'Contact'],
            'nav_cta' => ['Buton meniu', 'text', 'Solicită ofertă'],
            'header_logo' => ['Logo header', 'image', 'images/LOGO-antracit-grey.svg'],
        ],
        'Hero' => [
            'hero_kicker' => ['Text mic deasupra titlului', 'text', 'Producție publicitară · Chișinău'],
            'hero_title_main' => ['Titlu — partea 1', 'text', 'ArtLife'],
            'hero_title_accent' => ['Titlu — partea evidențiată', 'text', 'Design'],
            'hero_text' => ['Descriere hero', 'textarea', 'Publicitate vizuală și producție personalizată pentru branduri care vor să fie remarcate.'],
            'hero_btn_works' => ['Buton — Lucrări', 'text', 'Vezi lucrările'],
            'hero_btn_quote' => ['Buton — Ofertă', 'text', 'Solicită o ofertă'],
            'hero_bottom_label' => ['Text jos hero', 'text', 'Art Life Design'],
            'hero_video' => ['Video fundal hero', 'video', 'images/homeslider.mp4'],
            'hero_poster' => ['Imagine poster hero', 'image', 'images/fise_pret.jpg'],
        ],
        'Despre noi' => [
            'about_label' => ['Etichetă', 'text', 'Despre noi'],
            'about_title' => ['Titlu', 'text', 'Publicitate de orice fel, pentru orice buget.'],
            'about_lead' => ['Paragraf principal', 'textarea', 'Art Life Design este o agenție de publicitate dinamică, orientată spre soluții vizuale clare, creative și eficiente pentru fiecare brand.'],
            'about_text' => ['Paragraf secundar', 'textarea', 'Realizăm proiecte de publicitate vizuală, materiale promoționale și soluții personalizate pentru companii și afaceri locale.'],
            'about_btn_services' => ['Buton servicii', 'text', 'Vezi serviciile'],
            'about_btn_portfolio' => ['Buton portofoliu', 'text', 'Vezi portofoliul'],
            'about_image' => ['Imagine', 'image', 'images/litere_volum1.jpg'],
            'about_image_note' => ['Text peste imagine', 'text', 'Art Life Design · Proiecte realizate'],
        ],
        'Servicii — introducere' => [
            'services_label' => ['Etichetă', 'text', 'Servicii'],
            'services_title' => ['Titlu', 'text', 'Ce putem realiza'],
            'services_text' => ['Descriere', 'textarea', 'Alege serviciul care te interesează sau explorează lucrările realizate.'],
        ],
        'CTA proiect' => [
            'cta_label' => ['Etichetă', 'text', 'Ai un proiect?'],
            'cta_title' => ['Titlu', 'text', 'Spune-ne ce vrei să realizezi.'],
            'cta_text' => ['Descriere', 'textarea', 'Trimite-ne o scurtă descriere, iar echipa Art Life Design revine cu detaliile necesare.'],
            'cta_button' => ['Buton', 'text', 'Trimite o solicitare'],
        ],
        'Social' => [
            'social_label' => ['Etichetă', 'text', 'Urmărește Art Life Design'],
            'social_title' => ['Titlu', 'text', 'Proiecte noi, lucrări și noutăți.'],
            'instagram_name' => ['Instagram — nume', 'text', 'Instagram'],
            'instagram_handle' => ['Instagram — cont', 'text', '@design.artlife'],
            'instagram_url' => ['Instagram — link', 'url', 'https://www.instagram.com/design.artlife/'],
            'tiktok_name' => ['TikTok — nume', 'text', 'TikTok'],
            'tiktok_handle' => ['TikTok — cont', 'text', '@art.life.design'],
            'tiktok_url' => ['TikTok — link', 'url', 'https://www.tiktok.com/@art.life.design'],
            'facebook_name' => ['Facebook — nume', 'text', 'Facebook'],
            'facebook_handle' => ['Facebook — text', 'text', 'Art Life Design'],
            'facebook_url' => ['Facebook — link', 'url', 'https://www.facebook.com/p/Art-Life-Design-100063504763244/'],
        ],
        'Contact' => [
            'contact_label' => ['Etichetă', 'text', 'Contact'],
            'contact_title' => ['Titlu', 'text', 'Ai un proiect în plan?'],
            'contact_text' => ['Descriere', 'textarea', 'Trimite-ne un mesaj cu ceea ce vrei să realizezi. Echipa Art Life Design te va contacta pentru detalii.'],
            'contact_direct_label' => ['Etichetă contact direct', 'text', 'Contact direct'],
            'contact_direct_title' => ['Titlu contact direct', 'text', 'Art Life Design'],
            'contact_address' => ['Adresă', 'text', 'str. Nicolae Milescu Spătaru, 30, Chișinău'],
            'contact_phone_1' => ['Telefon 1', 'text', '+373 609 800 08'],
            'contact_phone_2' => ['Telefon 2', 'text', '+373 608 000 13'],
            'contact_email_1' => ['Email 1', 'text', 'info.artlifedesign@gmail.com'],
            'contact_email_2' => ['Email 2', 'text', 'office.artlifedesign@gmail.com'],
            'contact_hours' => ['Program', 'text', 'Luni - Vineri: 09:00 - 18:00'],
            'contact_map_text' => ['Text Google Maps', 'text', 'Deschide locația în Google Maps'],
            'form_name_label' => ['Formular — Nume', 'text', 'Nume și prenume*'],
            'form_name_placeholder' => ['Placeholder nume', 'text', 'Numele tău'],
            'form_phone_label' => ['Formular — Telefon', 'text', 'Telefon*'],
            'form_phone_placeholder' => ['Placeholder telefon', 'text', '+373 ...'],
            'form_email_label' => ['Formular — Email', 'text', 'Email*'],
            'form_email_placeholder' => ['Placeholder email', 'text', 'email@exemplu.com'],
            'form_service_label' => ['Formular — Serviciu', 'text', 'Serviciu'],
            'form_message_label' => ['Formular — Mesaj', 'text', 'Mesaj*'],
            'form_message_placeholder' => ['Placeholder mesaj', 'textarea', 'Descrie pe scurt proiectul sau rezultatul pe care îl dorești.'],
            'form_privacy' => ['Text confidențialitate', 'textarea', '* Prin trimiterea formularului, ești de acord cu prelucrarea datelor personale în scopul contactării ulterioare. Informațiile vor fi păstrate confidențial.'],
            'form_submit' => ['Buton formular', 'text', 'Trimite mesajul'],
        ],
        'Footer' => [
            'footer_logo' => ['Logo footer', 'image', 'images/LOGO-antracit-grey.svg'],
            'footer_text' => ['Descriere footer', 'textarea', 'Publicitate vizuală pentru branduri care vor să iasă în evidență.'],
            'footer_nav_title' => ['Titlu Navigare', 'text', 'Navigare'],
            'footer_social_title' => ['Titlu Social', 'text', 'Urmărește-ne'],
        ],
    ];
}

// Se găsesc câmpurile care folosesc imagini sau video.
function homepageMediaKeys(): array
{
    $keys = [];
    foreach (homepageFields() as $fields) {
        foreach ($fields as $key => $def) {
            if (in_array($def[1], ['image', 'video'], true)) {
                $keys[] = $key;
            }
        }
    }
    return $keys;
}

// Se pregătesc valorile implicite și ajustările imaginilor.
function homepageDefaults(): array
{
    $defaults = [];

    foreach (homepageFields() as $fields) {
        foreach ($fields as $key => $def) {
            $defaults[$key] = (string) $def[2];

            if ($def[1] === 'image') {
                $defaults[$key . '_crop_x'] = '50';
                $defaults[$key . '_crop_y'] = '50';
                $defaults[$key . '_zoom'] = '1';
                $defaults[$key . '_rotation'] = '0';
                $defaults[$key . '_fit'] = 'cover';
            }
        }
    }

    return $defaults;
}

// Se ia conținutul salvat din BD peste valorile implicite.
function getHomepageContent(PDO $pdo): array
{
    $content = homepageDefaults();
    $stmt = $pdo->query('SELECT content_key, content_value FROM site_content');

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $key = (string) $row['content_key'];
        if (array_key_exists($key, $content)) {
            $content[$key] = (string) $row['content_value'];
        }
    }

    return $content;
}

// Se salvează o valoare editată din admin.
function saveHomepageValue(PDO $pdo, string $key, string $value): void
{
    if (!array_key_exists($key, homepageDefaults())) {
        return;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO site_content (content_key, content_value)
         VALUES (:k, :v)
         ON DUPLICATE KEY UPDATE
           content_value = VALUES(content_value),
           updated_at = CURRENT_TIMESTAMP'
    );
    $stmt->execute(['k' => $key, 'v' => $value]);
}

function homepageUploadDir(): string
{
    return dirname(__DIR__) . '/uploads/homepage';
}

// Se verifică și se salvează imaginea sau videoul de pe homepage.
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
    $mime = (string) (new finfo(FILEINFO_MIME_TYPE))->file($tmp);

    $allowed = $expectedType === 'video'
        ? ['video/mp4'=>'mp4','video/webm'=>'webm','video/quicktime'=>'mov']
        : ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif','image/svg+xml'=>'svg'];

    $max = $expectedType === 'video' ? 80 * 1024 * 1024 : 8 * 1024 * 1024;

    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Format de fișier neacceptat.');
    }
    if ($size < 1 || $size > $max) {
        throw new RuntimeException($expectedType === 'video'
            ? 'Videoul trebuie să aibă maximum 80 MB.'
            : 'Imaginea trebuie să aibă maximum 8 MB.');
    }

    $dir = homepageUploadDir();
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        throw new RuntimeException('Nu s-a putut crea uploads/homepage.');
    }

    $name = 'home-' . date('Ymd-His') . '-' . bin2hex(random_bytes(5)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($tmp, $dir . '/' . $name)) {
        throw new RuntimeException('Fișierul nu a putut fi salvat.');
    }

    return 'uploads/homepage/' . $name;
}