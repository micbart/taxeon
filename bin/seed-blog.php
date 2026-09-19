<?php

if (!defined('WP_CLI') || !WP_CLI) {
    exit("Skrypt uruchamiamy przez: wp eval-file wp-content/themes/raks/bin/seed-blog.php\n");
}

function raks_blog_field_key(string $name): string
{
    static $map = null;

    if ($map === null) {
        $map = [];

        $collect = static function (array $fields) use (&$collect, &$map): void {
            foreach ($fields as $field) {
                $map[$field['name']] = $field['key'];

                if (!empty($field['sub_fields'])) {
                    $collect($field['sub_fields']);
                }
            }
        };

        foreach (glob(get_template_directory() . '/acf-json/*.json') ?: [] as $file) {
            $collect(json_decode((string) file_get_contents($file), true)['fields'] ?? []);
        }
    }

    if (!isset($map[$name])) {
        WP_CLI::error("Brak pola ACF „{$name}” w acf-json/.");
    }

    return $map[$name];
}

WP_CLI::log('Kategorie…');

$categories = [
    'zus' => ['name' => 'ZUS', 'color' => '#1d4ed8'],
    'ksef' => ['name' => 'KSeF', 'color' => '#0ea37f'],
    'nowosci' => ['name' => 'Nowości', 'color' => '#d97706'],
    'pit' => ['name' => 'PIT', 'color' => '#7c3aed'],
];

$termIds = [];

foreach ($categories as $slug => $data) {
    $term = get_term_by('slug', $slug, 'category');

    if (!$term) {
        $created = wp_insert_term($data['name'], 'category', ['slug' => $slug]);

        if (is_wp_error($created)) {
            WP_CLI::warning("Kategoria {$data['name']}: " . $created->get_error_message());
            continue;
        }

        $termIds[$slug] = (int) $created['term_id'];
        WP_CLI::log("  kategoria: {$data['name']}");
    } else {
        $termIds[$slug] = (int) $term->term_id;
    }

    update_field(raks_blog_field_key('color'), $data['color'], 'category_' . $termIds[$slug]);
}

$images = get_posts([
    'post_type' => 'attachment',
    'posts_per_page' => -1,
    'fields' => 'ids',
    'orderby' => 'ID',
    'order' => 'ASC',
    'meta_query' => [['key' => '_raks_seed_source', 'compare' => 'EXISTS']],
]);

if (!$images) {
    WP_CLI::warning('Brak obrazków w bibliotece — uruchom najpierw seed-front-page.php. Wpisy powstaną bez miniatur.');
}

WP_CLI::log('Wpisy…');

$posts = [
    [
        'title' => 'Jak dodać lub zmienić numer konta w PUE ZUS? Przewodnik krok po kroku',
        'slug' => 'numer-konta-pue-zus',
        'cat' => 'zus',
        'date' => '2026-05-18 09:00:00',
        'lead' => 'Prowadzisz jednoosobową działalność i czekasz na zwrot nadpłaty składki zdrowotnej? Upewnij się, że ZUS ma aktualny numer Twojego konta.',
    ],
    [
        'title' => 'Masz nadpłatę składki zdrowotnej? Sprawdź, jak odzyskać pieniądze z ZUS',
        'slug' => 'nadplata-skladki-zdrowotnej',
        'cat' => 'zus',
        'date' => '2026-05-12 09:00:00',
        'lead' => 'Roczne rozliczenie składki zdrowotnej to moment prawdy. Jeśli zapłaciłeś więcej, niż powinieneś – pokazujemy, jak odzyskać nadpłatę.',
    ],
    [
        'title' => 'Zbliża się termin rocznego rozliczenia składki zdrowotnej!',
        'slug' => 'termin-rocznego-rozliczenia-skladki',
        'cat' => 'zus',
        'date' => '2026-05-05 09:00:00',
        'lead' => 'Koniec roku podatkowego to nie koniec obowiązków. 20 maja 2026 to ostateczny dzień na złożenie w ZUS rocznego rozliczenia składki.',
    ],
    [
        'title' => 'ZUS w Twojej kieszeni? Poznaj aplikację mZUS dla Płatnika',
        'slug' => 'mzus-dla-platnika',
        'cat' => 'zus',
        'date' => '2026-04-20 09:00:00',
        'lead' => 'Logowanie do PUE ZUS na komputerze to nie jedyna droga. Aplikacja mZUS dla Płatnika upraszcza codzienne sprawy urzędowe.',
    ],
    [
        'title' => 'KSeF 2026: Lepiej zapobiegać niż korygować!',
        'slug' => 'ksef-2026-zapobiegac',
        'cat' => 'ksef',
        'date' => '2026-03-15 09:00:00',
        'lead' => 'Od 1 kwietnia 2026 Krajowy System e-Faktur staje się obowiązkowy dla większości przedsiębiorców. Przygotuj firmę z wyprzedzeniem.',
    ],
    [
        'title' => 'Ważna zmiana! Wydłużono terminy na przesyłanie ksiąg JPK_CIT',
        'slug' => 'terminy-jpk-cit',
        'cat' => 'nowosci',
        'date' => '2026-02-24 09:00:00',
        'lead' => 'Nowe rozporządzenie Ministra Finansów wydłuża terminy na dopełnienie obowiązków sprawozdawczych w zakresie podatku CIT.',
    ],
    [
        'title' => 'Samochód w leasingu operacyjnym – nowe limity kosztów od 2026',
        'slug' => 'leasing-operacyjny-limity-2026',
        'cat' => 'pit',
        'date' => '2026-02-17 09:00:00',
        'lead' => 'Leasing operacyjny to popularna forma finansowania auta w działalności. Sprawdź, jakie zmiany w limitach kosztów wchodzą w życie.',
    ],
    [
        'title' => 'Jak pobrać PIT-11 z e-Urzędu Skarbowego – poradnik krok po kroku',
        'slug' => 'pit-11-e-urzad-skarbowy',
        'cat' => 'pit',
        'date' => '2026-02-10 09:00:00',
        'lead' => 'Dzięki usłudze e-Urząd Skarbowy dostęp do formularza PIT-11 jest szybszy i łatwiejszy niż kiedykolwiek. Pokazujemy, jak to zrobić.',
    ],
    [
        'title' => 'Nowa składka zdrowotna 2026',
        'slug' => 'nowa-skladka-zdrowotna-2026',
        'cat' => 'zus',
        'date' => '2026-01-27 09:00:00',
        'lead' => 'Od lutego 2026 przedsiębiorców czekają wyższe stawki składki zdrowotnej. Podstawą jest nowe przeciętne wynagrodzenie za IV kwartał 2025.',
    ],
    [
        'title' => 'Rewolucja w zasiłkach i kontrolach ZUS – przewodnik 2026–2027',
        'slug' => 'rewolucja-w-zasilkach-zus',
        'cat' => 'zus',
        'date' => '2026-01-13 09:00:00',
        'lead' => 'Prezydent podpisał ustawę wprowadzającą fundamentalne zmiany w zwolnieniach lekarskich, ich kontroli oraz orzecznictwie ZUS.',
    ],
];

function raks_blog_content(): string
{
    return implode("\n\n", [
        '<!-- wp:heading --><h2>Co się zmienia</h2><!-- /wp:heading -->',
        '<!-- wp:paragraph --><p>Poniżej zebraliśmy najważniejsze punkty. To treść demonstracyjna, wygenerowana przez seeder motywu — zastąp ją właściwym artykułem.</p><!-- /wp:paragraph -->',
        '<!-- wp:list --><ul>'
            . '<li>Nowe terminy obowiązują od początku okresu rozliczeniowego.</li>'
            . '<li>Zmiana dotyczy zarówno firm, jak i biur rachunkowych.</li>'
            . '<li>RAKS aktualizuje się automatycznie — nie musisz nic instalować.</li>'
            . '</ul><!-- /wp:list -->',
        '<!-- wp:heading {"level":3} --><h3>Jak przygotować firmę</h3><!-- /wp:heading -->',
        '<!-- wp:list {"ordered":true} --><ol>'
            . '<li>Zweryfikuj dane firmy w programie.</li>'
            . '<li>Sprawdź, czy masz aktualną wersję modułu.</li>'
            . '<li>W razie wątpliwości skontaktuj się z naszym wsparciem.</li>'
            . '</ol><!-- /wp:list -->',
        '<!-- wp:quote --><blockquote class="wp-block-quote"><p>Jeśli coś jest niejasne, zadzwoń do nas — odpowiadamy średnio w ciągu 60 minut.</p></blockquote><!-- /wp:quote -->',
        '<!-- wp:paragraph --><p>Masz pytania? Nasi konsultanci znają polskie przepisy i Twój program.</p><!-- /wp:paragraph -->',
    ]);
}

$created = 0;
$updated = 0;

foreach ($posts as $index => $item) {
    $existing = get_page_by_path($item['slug'], OBJECT, 'post');

    $payload = [
        'post_type' => 'post',
        'post_title' => $item['title'],
        'post_name' => $item['slug'],
        'post_status' => 'publish',
        'post_date' => $item['date'],
        'post_excerpt' => $item['lead'],
        'post_content' => wp_slash(raks_blog_content()),
    ];

    if ($existing) {
        $payload['ID'] = (int) $existing->ID;
        $postId = (int) wp_update_post($payload);
        $updated++;
    } else {
        $postId = (int) wp_insert_post($payload);
        $created++;
    }

    if (!$postId) {
        continue;
    }

    if (isset($termIds[$item['cat']])) {
        wp_set_post_terms($postId, [$termIds[$item['cat']]], 'category');
    }

    if ($images) {
        set_post_thumbnail($postId, $images[$index % count($images)]);
    }
}

WP_CLI::log("  utworzono: {$created}, zaktualizowano: {$updated}");

$hello = get_page_by_path('witaj-swiecie', OBJECT, 'post') ?: get_post(1);

if ($hello && $hello->post_type === 'post' && str_contains($hello->post_content, 'WordPress')) {
    wp_trash_post($hello->ID);
    WP_CLI::log('  domyślny wpis WordPressa przeniesiony do kosza');
}

$blogPageId = (int) get_option('page_for_posts');

if ($blogPageId) {
    $set = static function (string $name, $value) use ($blogPageId): void {
        update_field(raks_blog_field_key($name), $value, $blogPageId);
    };

    $set('archive_title', 'Wiedza, porady i aktualności dla Twojej firmy');
    $set('archive_description', '<p>Praktyczne artykuły o księgowości, kadrach, podatkach, KSeF i ZUS – pisane '
        . 'prostym językiem przez praktyków dla praktyków. Bądź na bieżąco ze zmianami w przepisach.</p>');

    WP_CLI::log('  strona archiwum: nagłówek i opis');
}

\App\Services\ThemeCache::flush('menus');

WP_CLI::success('Blog wypełniony.');
