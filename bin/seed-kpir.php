<?php
require get_template_directory() . '/bin/seed-helpers.php';
$t = fn(string $x, string $tag = 'p'): array => ['text' => $x, 'tag' => $tag];
$l = fn(string $tytul, string $sciezka): array => ['title' => $tytul, 'url' => home_url($sciezka), 'target' => ''];
$punkty = fn(array $x): array => array_map(fn($y) => ['text' => $y], $x);

$hero = taxeon_seed_block('hero', [
    'layout' => 'split',
    'badge' => 'Księgowość online · Książka Przychodów i Rozchodów',
    'title' => $t('KPiR za 169 zł.', 'h1'),
    'subtitle' => 'Koszty, VAT, środki trwałe — wszystko ogarniemy.',
    'text' => 'Książka Przychodów i Rozchodów to forma dla tych, którzy chcą odliczać koszty. Wybierasz ją, jeśli masz istotne wydatki — sprzęt, paliwo, biuro, szkolenia, podwykonawcy — i opłaca Ci się obniżać podatek przez koszty. Rozliczenie odbywa się na skali (12% i 32%) lub podatkiem liniowym (19%). Prowadzenie KPiR to więcej pracy niż ryczałt, ale i większe możliwości.',
    'button_primary' => $l('Zamów KPiR →', '/#kontakt'),
    'button_secondary' => $l('Porównaj z ryczałtem', '/ryczalt/'),
    'trust' => [
        ['value' => '4,9 / 5', 'label' => 'opinie w Google', 'stars' => 1, 'url' => 'https://www.google.pl/maps/search/TAXEON+-+Biuro+Rachunkowe+Warszawa.+Kamionkowska+51/'],
        ['value' => '137 tys.', 'label' => 'przedsiębiorców na naszym YouTube', 'stars' => 0, 'url' => ''],
        ['value' => '14 lat', 'label' => 'na rynku', 'stars' => 0, 'url' => ''],
    ],
    'video' => '',
    'cards' => [[
        'label' => 'Abonament', 'badge' => '', 'badge_style' => 'filled', 'featured' => 0,
        'price' => '169 zł', 'period' => '/ mies.',
        'text' => 'Pełna obsługa kosztów, VAT-u i środków trwałych.',
        'items' => $punkty(['Dedykowana księgowa', 'Amortyzacja i środki trwałe',
            'Rozliczenia ZUS w cenie', 'Rozliczenie pojazdów', 'Obsługa kosztów i VAT']),
        'link' => ['title' => '', 'url' => '', 'target' => ''],
    ]],
    'stats' => [],
]);

$dlaKogo = taxeon_seed_block('list', [
    'eyebrow' => $t('Dla kogo', 'span'),
    'title' => $t('Dla kogo KPiR jest najlepsza.', 'h2'),
    'text' => $t('Trzy typy działalności, w których koszty realnie obniżają podatek.'),
    'items' => array_map(fn($x) => ['label' => $x, 'value' => ''], [
        'Działalności z dużymi kosztami — handel, produkcja, usługi z podwykonawcami.',
        'Firmy inwestujące w sprzęt — studia fotograficzne, warsztaty, gabinety wymagające wyposażenia.',
        'Specjaliści z wysokimi kosztami pracy — najem biura, samochód, oprogramowanie.',
    ]),
    'background' => 'page', 'padding_top' => 'medium', 'padding_bottom' => 'medium',
]);

$wCenie = taxeon_seed_block('panels', [
    'eyebrow' => $t('', 'span'), 'title' => $t('', 'h2'), 'text' => $t(''),
    'items' => [[
        'title' => 'Co dostajesz w abonamencie.', 'variant' => 'glass', 'list_style' => 'check',
        'items' => $punkty(['Prowadzenie KPiR.', 'Ewidencja środków trwałych i amortyzacja.',
            'Rozliczenia VAT — JPK_V7, deklaracje, VAT-UE.', 'Rozliczenia ZUS, w tym DRA.',
            'Miesięczne zaliczki na PIT.', 'Konsultacje z księgową.',
            'Dostęp do aplikacji do wystawiania faktur.', 'Rozliczenie kosztów pojazdu.']),
        'note' => '',
    ]],
    'background' => 'gradient',
]);

$decyzja = taxeon_seed_block('panels', [
    'eyebrow' => $t('Krótka decyzja', 'span'),
    'title' => $t('Ryczałt czy KPiR?', 'h2'),
    'text' => $t(''),
    'items' => [
        ['title' => 'Wybierz ryczałt, jeśli…', 'variant' => 'light', 'list_style' => 'rows', 'note' => '',
         'link' => $l('Zobacz ryczałt za 99 zł →', '/ryczalt/'),
         'items' => $punkty(['Twoje koszty firmowe są niskie — poniżej 10% przychodu.',
            'Cenisz prostotę bez papierologii.',
            'Twoja branża ma niską stawkę ryczałtu: 8,5%, 12% lub 14%.'])],
        ['title' => 'Wybierz KPiR, jeśli…', 'variant' => 'dark', 'list_style' => 'rows', 'note' => '',
         'link' => $l('Zamów KPiR za 169 zł →', '/#kontakt'),
         'items' => $punkty(['Masz istotne koszty — sprzęt, biuro, podwykonawcy.',
            'Inwestujesz w środki trwałe: auto, sprzęt.',
            'Twój przychód przekracza 300–400 tys. zł rocznie.'])],
    ],
    'background' => 'page', 'padding_top' => 'medium', 'padding_bottom' => 'medium',
]);

$cta = taxeon_seed_block('cta', [
    'title_size' => 'md',
    'title' => $t('Zamów KPiR za 169 zł.', 'h2'),
    'text' => 'Albo umów krótką rozmowę — pomożemy wybrać między ryczałtem a KPiR.',
    'button_primary' => $l('Zamów KPiR →', '/#kontakt'),
    'button_secondary' => $l('Zobacz ryczałt za 99 zł', '/ryczalt/'),
    'background' => 'navy',
]);

$istnieje = get_page_by_path('kpir');
$id = wp_insert_post(wp_slash([
    'ID' => $istnieje ? $istnieje->ID : 0,
    'post_type' => 'page', 'post_status' => 'publish',
    'post_title' => 'KPiR — 169 zł', 'post_name' => 'kpir',
    'post_content' => implode("\n\n", [$hero, $dlaKogo, $wCenie, $decyzja, $cta]),
]));
echo "  strona KPiR #{$id}\n";
