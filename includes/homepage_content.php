<?php
declare(strict_types=1);

/**
 * Homepage config.
 * Lucrările NU sunt incluse aici: sunt administrate separat.
 */
function homepageServices(): array
{
    return [
        'poligrafie' => [
            'section' => 'Serviciu — Poligrafie',
            'name' => 'Poligrafie',
            'title' => 'Print, stickere și materiale promoționale',
            'short' => 'Etichete, bannere, meniuri, fișe de preț și materiale tipărite.',
            'detail_title' => 'Materiale tipărite pentru prezentare, promovare și vânzare',
            'detail_text' => 'Poligrafia acoperă materialele tipărite folosite pentru prezentare, informare și promovare. Este potrivită atât pentru utilizarea zilnică într-un magazin sau restaurant, cât și pentru campanii, lansări și evenimente.',
            'examples' => "Etichete și stickere pentru produse\nMeniuri și fișe de preț\nBannere și materiale promoționale\nPrint personalizat pentru campanii",
            'images' => ['images/fise_pret.jpg', 'images/inghetata_uv.jpg', 'images/tipar_mare.jpg'],
        ],
        'volum' => [
            'section' => 'Serviciu — Litere & Standuri',
            'name' => 'Litere în volum & Standuri',
            'title' => 'Elemente vizuale pentru fațade și interior',
            'short' => 'Litere volumetrice, light box-uri, standuri și casete luminoase.',
            'detail_title' => 'Elemente vizuale care fac brandul ușor de observat',
            'detail_text' => 'Literele volumetrice și standurile transformă identitatea vizuală într-un element fizic ușor de remarcat. Sunt potrivite pentru fațade, recepții, spații comerciale, expoziții și zone de prezentare.',
            'examples' => "Litere volumetrice pentru fațadă\nLight box-uri și casete luminoase\nLogo-uri pentru interior\nStanduri și elemente de prezentare",
            'images' => ['images/BonjourLightBox.jpg', 'images/good-break.jpg', 'images/litere_volum1.jpg'],
        ],
        'posm' => [
            'section' => 'Serviciu — P.O.S.M.',
            'name' => 'P.O.S.M.',
            'title' => 'Materiale pentru retail și campanii',
            'short' => 'Display-uri, urne, suporturi, standuri și produse personalizate.',
            'detail_title' => 'Materiale pentru expunere, retail și campanii promoționale',
            'detail_text' => 'P.O.S.M. înseamnă materiale vizuale folosite direct în punctul de vânzare sau în campanii promoționale. Ele ajută la prezentarea produselor și evidențierea ofertelor.',
            'examples' => "Display-uri și suporturi\nUrne și box-uri personalizate\nStanduri promoționale\nElemente pentru campanii și retail",
            'images' => ['images/box.jpg', 'images/cub-sticla.jpg', 'images/stand sticla.jpg'],
        ],
        'auto' => [
            'section' => 'Serviciu — Branding Auto',
            'name' => 'Branding Auto',
            'title' => 'Promovare vizibilă pe mașini și flote',
            'short' => 'Colantări auto pentru transport comercial, microbuze și automobile.',
            'detail_title' => 'Transformă transportul companiei într-un suport vizibil de promovare',
            'detail_text' => 'Brandingul auto transformă automobilul sau flota companiei într-un suport vizual de promovare. Grafica poate integra logo-ul, datele de contact și identitatea vizuală.',
            'examples' => "Automobile pentru companii\nMicrobuze și transport comercial\nElemente grafice și date de contact\nBranding pentru mai multe unități din flotă",
            'images' => ['images/brand_auto1.jpg', 'images/brand_auto2.jpg', 'images/lux-tavene.jpg'],
        ],
        'laser' => [
            'section' => 'Serviciu — Laser / Plotter',
            'name' => 'Laser / Plotter',
            'title' => 'Tăiere, gravare și decupare personalizată',
            'short' => 'Servicii pentru proiecte promoționale, decorative și tehnice.',
            'detail_title' => 'Tăiere, gravare și decupare pentru proiecte personalizate',
            'detail_text' => 'Laserul și plotterul sunt folosite pentru proiecte în care contează forma, conturul și precizia decupării. Sunt potrivite pentru inscripții, stickere și elemente promoționale.',
            'examples' => "Stickere și elemente decupate\nForme și inscripții personalizate\nElemente promoționale și decorative\nProiecte care necesită tăiere sau gravare",
            'images' => ['images/taiere_laser.jpg', 'images/taiere_plotter.jpg', 'images/plotter-2.jpg'],
        ],
    ];
}

function homepageFields(): array
{
    $sections = [
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

    // Serviciile sunt generate dintr-o singură configurație.
    $serviceSections = [];
    foreach (homepageServices() as $key => $service) {
        $serviceSections[$service['section']] = [
            "{$key}_name" => ['Nume serviciu', 'text', $service['name']],
            "{$key}_title" => ['Titlu card', 'text', $service['title']],
            "{$key}_short" => ['Descriere card', 'textarea', $service['short']],
            "{$key}_detail_title" => ['Titlu detalii', 'text', $service['detail_title']],
            "{$key}_detail_text" => ['Text detalii', 'textarea', $service['detail_text']],
            "{$key}_examples" => ['Exemple — câte unul pe rând', 'textarea', $service['examples']],
            "{$key}_btn_examples" => ['Buton exemple', 'text', 'Vezi exemple'],
            "{$key}_btn_quote" => ['Buton ofertă', 'text', 'Solicită o ofertă'],
            "{$key}_image_1" => ['Imagine 1', 'image', $service['images'][0]],
            "{$key}_image_2" => ['Imagine 2', 'image', $service['images'][1]],
            "{$key}_image_3" => ['Imagine 3', 'image', $service['images'][2]],
        ];
    }

    // Serviciile rămân după introducere, înainte de CTA.
    $before = array_slice($sections, 0, 4, true);
    $after = array_slice($sections, 4, null, true);

    return $before + $serviceSections + $after;
}

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