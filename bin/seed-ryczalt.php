<?php
require get_template_directory() . '/bin/seed-helpers.php';
$t = fn(string $x, string $tag = 'p'): array => ['text' => $x, 'tag' => $tag];
$l = fn(string $tytul, string $sciezka): array => ['title' => $tytul, 'url' => home_url($sciezka), 'target' => ''];
$plakat = taxeon_seed_image('ryczalt-hero-poster.jpg', 'Ryczałt ewidencjonowany — Taxeon');

$hero = taxeon_seed_block('hero', [
    'layout' => 'split',
    'badge' => 'Księgowość online · Ryczałt ewidencjonowany',
    'title' => $t('Ryczałt — 99 zł.', 'h1'),
    'subtitle' => 'Najprostsza forma rozliczenia jednoosobowej działalności w Polsce.',
    'text' => 'Płacisz procent od przychodu — 8,5%, 12%, 14% czy 17%, zależnie od branży — nie odliczasz kosztów, papierologia minimalna. U nas pełna obsługa tej formy to 99 zł miesięcznie i tyle. Bez dopłat za roczne rozliczenie, bez dopłat za korekty, bez dopłat za konsultacje z księgową.',
    'button_primary' => $l('Zamów ryczałt za 99 zł →', taxeon_booking()),
    'button_secondary' => $l('Porównaj z KPiR', '/kpir/'),
    'trust' => [
        ['value' => '{ocena} / 5', 'label' => 'opinie w Google', 'stars' => 1, 'url' => 'https://www.google.pl/maps/search/TAXEON+-+Biuro+Rachunkowe+Warszawa.+Kamionkowska+51/'],
        ['value' => '137 tys.', 'label' => 'przedsiębiorców na naszym YouTube', 'stars' => 0, 'url' => ''],
        ['value' => '14 lat', 'label' => 'na rynku', 'stars' => 0, 'url' => ''],
    ],
    'video' => taxeon_wideo('ryczalt-hero.mp4'),
    'poster' => $plakat,
    'cards' => [[
        'label' => 'Abonament', 'badge' => '', 'badge_style' => 'filled', 'featured' => 0,
        'price' => '99 zł', 'period' => '/ mies.',
        'text' => 'Dla wolnych zawodów, IT, B2B i wszystkich na ryczałcie ewidencjonowanym.',
        'items' => array_map(fn($x) => ['text' => $x], [
            'Pełna obsługa księgowa', 'Rozliczenia ZUS i podatkowe',
            'Dedykowana księgowa', 'Dokumenty wysyłasz elektronicznie',
        ]),
        'link' => ['title' => '', 'url' => '', 'target' => ''],
    ]],
    'stats' => [],
]);

$dlaKogo = taxeon_seed_block('list', [
    'eyebrow' => $t('Dla kogo', 'span'),
    'title' => $t('Dla kogo ryczałt jest najlepszy.', 'h2'),
    'text' => $t('Zasada jest prosta: im niższe koszty i wyższy przychód, tym bardziej ryczałt się opłaca.'),
    'items' => [
        ['label' => 'Programiści i IT', 'value' => '12% lub 8,5%'],
        ['label' => 'Lekarze i dentyści', 'value' => '14%'],
        ['label' => 'Prawnicy', 'value' => '17%'],
        ['label' => 'Konsultanci, trenerzy, copywriterzy', 'value' => '8,5% lub 15%'],
        ['label' => 'Każdy, kto ma niskie koszty i wysoki przychód', 'value' => '—'],
    ],
    'background' => 'page',
    'padding_top' => 'medium', 'padding_bottom' => 'medium',
]);

$wCenie = taxeon_seed_block('panels', [
    'eyebrow' => $t('', 'span'), 'title' => $t('', 'h2'), 'text' => $t(''),
    'items' => [
        [
            'title' => 'Co dokładnie dostajesz za 99 zł', 'variant' => 'glass', 'list_style' => 'check',
            'items' => array_map(fn($x) => ['text' => $x], [
                'Prowadzenie ewidencji przychodów.',
                'Rozliczenie ZUS — DRA, korekty, zmiany kodów.',
                'Obliczanie miesięcznych zaliczek na PIT.',
                'Dostęp do aplikacji do wystawiania faktur.',
                'Dedykowana księgowa do kontaktu mailowego.',
                'Konsultacje bieżące w sprawach księgowych.',
            ]),
            'note' => '',
        ],
        [
            'title' => 'Co NIE jest w cenie', 'variant' => 'muted', 'list_style' => 'dash',
            'text' => 'Jesteśmy uczciwi — kilka rzeczy jest płatnych dodatkowo:',
            'items' => array_map(fn($x) => ['text' => $x], [
                'Kadry i płace, jeśli zatrudniasz pracownika — 80 zł/mies./os.',
                'Rozliczenie podatku VAT — inny abonament.',
                'Korekty dokumentów dostarczonych po terminie z winy klienta.',
                'Rozliczenie roczne PIT — 69 zł.',
            ]),
            'note' => 'Pełen cennik dodatkowy znajdziesz na <a href="' . home_url('/cennik/') . '">podstronie Cennik</a>.',
        ],
    ],
    'background' => 'gradient',
]);

$cta = taxeon_seed_block('cta', [
    'title' => $t('Zacznij prowadzić ryczałt taniej.', 'h2'),
    'text' => 'Bezpłatne spotkanie online z księgową. 30 minut, bez zobowiązań.',
    'button_primary' => $l('Zamów ryczałt za 99 zł →', taxeon_booking()),
    'button_secondary' => $l('Zobacz KPiR za 169 zł', '/kpir/'),
    'background' => 'navy',
]);

$istnieje = get_page_by_path('ryczalt');
$id = wp_insert_post(wp_slash([
    'ID' => $istnieje ? $istnieje->ID : 0,
    'post_type' => 'page', 'post_status' => 'publish',
    'post_title' => 'Ryczałt — 99 zł', 'post_name' => 'ryczalt',
    'post_content' => implode("\n\n", [$hero, $dlaKogo, $wCenie, $cta]),
]));
echo "  strona ryczałt #{$id}\n";
