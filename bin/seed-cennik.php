<?php
require get_template_directory() . '/bin/seed-helpers.php';
$t = fn(string $x, string $tag = 'p'): array => ['text' => $x, 'tag' => $tag];
$l = fn(string $tytul, string $url): array => ['title' => $tytul, 'url' => str_starts_with($url, 'http') || str_starts_with($url, '#') ? $url : home_url($url), 'target' => ''];
$pkt = fn(array $x): array => array_map(fn($y) => ['text' => $y], $x);
$pusty = ['title' => '', 'url' => '', 'target' => ''];

$hero = taxeon_seed_block('hero', [
    'layout' => 'cards', 'button_style' => 'light',
    'badge' => 'Cennik usług księgowych · Ceny netto, bez umowy lojalnościowej',
    'title' => $t('Cały cennik na jednej stronie.', 'h1'),
    'subtitle' => 'Bez „od”, bez gwiazdek, bez ukrytych pozycji na fakturze.',
    'text' => 'To są nasze stałe ceny abonamentu, a nie ceny promocyjne. Płacisz z góry za dany miesiąc, najpóźniej do 5 dnia — fakturę dostajesz automatycznie w aplikacji. Rezygnujesz z miesiąca na miesiąc.',
    'button_primary' => $pusty, 'button_secondary' => $pusty, 'chips' => [], 'trust' => [], 'stats' => [],
    'video' => home_url('/projekt/assets/hero-main.mp4'),
    'cards' => [
        ['label' => 'Aplikacja', 'badge' => 'Samodzielnie', 'badge_style' => 'outline', 'featured' => 0,
         'price' => '19 zł', 'period' => '/ mies.', 'items' => [],
         'text' => 'Przez pierwsze 3 miesiące, potem 43 zł netto. Księgujesz sam — my dajemy narzędzie.',
         'link' => $l('Wypróbuj aplikację →', '/aplikacja/')],
        ['label' => 'Ryczałt', 'badge' => 'Najtańszy', 'badge_style' => 'filled', 'featured' => 0,
         'price' => '99 zł', 'period' => '/ mies.', 'items' => [],
         'text' => 'Pełna obsługa ryczałtu bez VAT. Dla wolnych zawodów, IT i B2B.',
         'link' => $l('Zamów ryczałt →', '/#kontakt')],
        ['label' => 'Ryczałt + VAT', 'badge' => '', 'badge_style' => 'filled', 'featured' => 0,
         'price' => '139 zł', 'period' => '/ mies.', 'items' => [],
         'text' => 'Ryczałt dla czynnych podatników VAT — JPK_V7, deklaracje, VAT-UE.',
         'link' => $l('Zamów ryczałt z VAT →', '/#kontakt')],
        ['label' => 'KPiR', 'badge' => 'Najpopularniejszy', 'badge_style' => 'filled', 'featured' => 1,
         'price' => '169 zł', 'period' => '/ mies.', 'items' => [],
         'text' => 'Księga Przychodów i Rozchodów z VAT, kosztami i środkami trwałymi.',
         'link' => $l('Zamów KPiR →', '/#kontakt')],
    ],
]);

$wiersze = [
    ['Wystawianie faktur w aplikacji', 1, 1, 1, 1],
    ['Integracja z KSeF', 1, 1, 1, 1],
    ['Przypomnienia o terminach i składkach', 1, 1, 1, 1],
    ['Prowadzenie ewidencji przychodów / KPiR', 1, 1, 1, 1],
    ['Obliczanie miesięcznych zaliczek na PIT', 1, 1, 1, 1],
    ['Rozliczenia ZUS — DRA, korekty, zmiany kodów', 1, 1, 1, 1],
    ['Dedykowana księgowa — Twój opiekun', 0, 1, 1, 1],
    ['Konsultacje mailowe z księgową', 0, 1, 1, 1],
    ['Pomoc przy zakładaniu firmy (CEIDG, ZUS)', 0, 1, 1, 1],
    ['Przeniesienie rozliczeń z innego biura', 0, 1, 1, 1],
    ['Rozliczenia VAT — JPK_V7, deklaracje, VAT-UE', 1, 0, 1, 1],
    ['Faktury zagraniczne, waluty i różnice kursowe', 1, 0, 1, 1],
    ['Rozliczanie kosztów i amortyzacji', 1, 0, 0, 1],
    ['Ewidencja środków trwałych', 1, 0, 0, 1],
    ['Rozliczenie kosztów pojazdu', 1, 0, 0, 1],
];

$tabela = taxeon_seed_block('table', [
    'head_position' => 'above', 'title_width' => '22ch', 'text_width' => '60ch',
    'eyebrow' => $t('Zakres funkcjonalny', 'span'),
    'title' => $t('Co dokładnie zawiera każdy abonament.', 'h2'), 'text' => $t(''),
    'note' => '', 'head_label' => 'Zakres',
    'columns' => [
        ['label' => 'Aplikacja', 'note' => '19 zł', 'highlight' => 0],
        ['label' => 'Ryczałt', 'note' => '99 zł', 'highlight' => 0],
        ['label' => 'Ryczałt + VAT', 'note' => '139 zł', 'highlight' => 0],
        ['label' => 'KPiR', 'note' => '169 zł', 'highlight' => 1],
    ],
    'rows' => array_map(fn($w) => [
        'label' => $w[0],
        'values' => [['on' => $w[1]], ['on' => $w[2]], ['on' => $w[3]], ['on' => $w[4]]],
    ], $wiersze),
    'background' => 'page', 'padding_top' => 'medium', 'padding_bottom' => 'medium',
]);

$dodatki = taxeon_seed_block('list', [
    'title_width' => '20ch', 'text_width' => '46ch', 'columns_ratio' => '0.8-1.2',
    'eyebrow' => $t('Cennik dodatkowy', 'span'),
    'title' => $t('Usługi poza abonamentem.', 'h2'),
    'text' => $t('Jesteśmy uczciwi: kilka rzeczy jest płatnych osobno. To pełna lista — nic poza nią nie pojawi się na Twojej fakturze.'),
    'link' => $pusty, 'row_style' => 'inline', 'columns' => '1',
    'items' => [
        ['label' => 'Kadry i płace — pełna obsługa, lista płac, ZUS, PIT-11', 'value' => '80 zł / os. / mies.', 'tone' => 'default', 'text' => ''],
        ['label' => 'Rozliczenie roczne PIT', 'value' => '69 zł', 'tone' => 'default', 'text' => ''],
        ['label' => 'Korekty dokumentów dostarczonych po terminie z winy klienta', 'value' => 'wycena indywidualna', 'tone' => 'muted', 'text' => ''],
        ['label' => 'Nietypowe rozliczenia transgraniczne', 'value' => 'wycena indywidualna', 'tone' => 'muted', 'text' => ''],
        ['label' => 'Założenie firmy — CEIDG, NIP, REGON, ZUS', 'value' => '0 zł', 'tone' => 'free', 'text' => ''],
        ['label' => 'Przeniesienie rozliczeń z dotychczasowego biura', 'value' => '0 zł', 'tone' => 'free', 'text' => ''],
        ['label' => 'Faktury zagraniczne i walutowe', 'value' => '0 zł', 'tone' => 'free', 'text' => ''],
    ],
    'background' => 'gradient',
]);

$zasady = taxeon_seed_block('list', [
    'head_size' => 'sm', 'columns_ratio' => '0.8-1.2',
    'eyebrow' => $t('Zasady rozliczeń', 'span'),
    'title' => $t('Trzy zdania, które warto znać przed startem.', 'h2'), 'text' => $t(''),
    'link' => $pusty, 'row_style' => 'stacked', 'columns' => '3',
    'items' => [
        ['label' => 'Ceny netto', 'value' => '', 'tone' => 'default', 'text' => 'Wszystkie kwoty są cenami netto, doliczamy do nich VAT 23%.'],
        ['label' => 'Płatność z góry', 'value' => '', 'tone' => 'default', 'text' => 'Abonament opłacasz do 5 dnia miesiąca. Faktura pojawia się automatycznie w aplikacji.'],
        ['label' => 'Bez lojalki', 'value' => '', 'tone' => 'default', 'text' => 'Możesz zrezygnować z miesiąca na miesiąc. Nie podpisujesz umowy na czas określony.'],
    ],
    'background' => 'soft-2', 'padding_top' => 'medium', 'padding_bottom' => 'medium',
]);

$cta = taxeon_seed_block('cta', [
    'title_size' => 'md',
    'title' => $t('Nie wiesz, który pakiet wybrać?', 'h2'),
    'text' => 'Napisz albo zadzwoń — dobierzemy abonament do Twojej sytuacji. Bezpłatnie, bez zobowiązań.',
    'button_primary' => $l('Umów rozmowę z księgową →', '/#kontakt'),
    'button_secondary' => $pusty,
    'background' => 'navy',
]);

$istnieje = get_page_by_path('cennik');
$id = wp_insert_post(wp_slash([
    'ID' => $istnieje ? $istnieje->ID : 0,
    'post_type' => 'page', 'post_status' => 'publish',
    'post_title' => 'Cennik', 'post_name' => 'cennik',
    'post_content' => implode("\n\n", [$hero, $tabela, $dodatki, $zasady, $cta]),
]));
echo "  strona cennik #{$id}\n";
