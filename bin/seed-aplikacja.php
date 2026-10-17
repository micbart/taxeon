<?php
require get_template_directory() . '/bin/seed-helpers.php';
$t = fn(string $x, string $tag = 'p'): array => ['text' => $x, 'tag' => $tag];
$l = fn(string $tytul, string $url): array => ['title' => $tytul, 'url' => str_starts_with($url, 'http') ? $url : home_url($url), 'target' => ''];
$pkt = fn(array $x): array => array_map(fn($y) => ['text' => $y], $x);

$hero = taxeon_seed_block('hero', [
    'space_bottom' => '88',
    'title_size' => '62', 'text_gap' => '24',
    'layout' => 'split',
    'badge' => 'Aplikacja Taxeon · w cenie abonamentu',
    'title' => $t('Program księgowy online', 'h1'),
    'subtitle' => 'Rozliczanie własnej firmy jeszcze nigdy nie było tak proste.',
    'text' => 'Wystawiaj faktury, raz w miesiącu poświęć 5 minut na wprowadzenie kosztów i jednym kliknięciem rozlicz podatki.',
    'button_primary' => ['title' => '', 'url' => '', 'target' => ''],
    'button_secondary' => ['title' => '', 'url' => '', 'target' => ''],
    'trust' => [
        ['value' => '4,9 / 5', 'label' => 'opinie w Google', 'stars' => 1, 'url' => 'https://www.google.pl/maps/search/TAXEON+-+Biuro+Rachunkowe+Warszawa.+Kamionkowska+51/'],
        ['value' => '137 tys.', 'label' => 'firm na YouTube', 'stars' => 0, 'url' => ''],
        ['value' => '14 lat', 'label' => 'na rynku', 'stars' => 0, 'url' => ''],
    ],
    'video' => home_url('/projekt/assets/hero-main.mp4'),
    'poster' => taxeon_seed_image('hero-main-poster.jpg', 'Aplikacja Taxeon'),
    'cards' => [[
        'label' => 'Aplikacja', 'badge' => 'Samodzielnie', 'badge_style' => 'filled', 'featured' => 1,
        'price' => '19 zł', 'price_compact' => 1, 'period' => '/ mies.',
        'text' => "Przez pierwsze 3 miesiące, potem 43 zł netto.\nKsięgujesz sam — my dajemy narzędzie.",
        'items' => $pkt(['Wystawianie faktur i KSeF', 'Ewidencje i wyliczenia podatku',
            'Przypomnienia o terminach', 'Bez umowy lojalnościowej']),
        'link' => $l('Wypróbuj za darmo', 'https://app.taxeon.pl/'),
    ]],
    'stats' => [],
]);

$kroki = taxeon_seed_block('tabs', [
    'eyebrow' => $t('Co robisz w aplikacji', 'span'),
    'title' => $t('Trzy rzeczy, na które potrzebujesz kilku minut.', 'h2'),
    'title_width' => '24ch',
    'items' => [
        [
            'title' => 'Wystawiasz fakturę',
            'hint' => 'Dane kontrahenta z GUS, gotowa w minutę',
            'step' => 'Krok 01',
            'text' => 'Wpisujesz NIP, resztę danych aplikacja pobiera z bazy GUS. Faktura trafia od razu do Twojej ewidencji i do księgowej — nie musisz jej nikomu przesyłać.',
            'points' => $pkt(['Faktury krajowe, zagraniczne i w walutach obcych',
                'Powtarzalne faktury z jednego szablonu',
                'Wysyłka do klienta mailem wprost z aplikacji',
                'Bezpośrednia wysyłka do KSeF']),
        ],
        [
            'title' => 'Wrzucasz koszty',
            'hint' => 'Automatycznie prosto z KSeF',
            'step' => 'Krok 02',
            'text' => 'Dokumenty kosztowe pobierasz bezpośrednio z KSeF. Prosto i szybko zaksięgujesz koszty swojej firmy — zajmie Ci to tylko kilka minut. Powtarzające się wydatki system automatycznie podpowie Ci w kolejnych miesiącach.',
            'points' => $pkt(['Jedno miejsce na wszystkie dokumenty',
                'Wygodnie rozliczysz środki trwałe i koszty pojazdów',
                'Archiwum dostępne przez 6 lat']),
        ],
        [
            'title' => 'Sprawdzasz podatek i ZUS',
            'hint' => 'Zawsze wiesz, ile i do kiedy',
            'step' => 'Krok 03',
            'text' => 'Po zamknięciu miesiąca w panelu widzisz kwotę podatku, składek ZUS i termin płatności. Bez dopytywania i bez maili w ostatnim tygodniu miesiąca.',
            'points' => $pkt(['Wyliczenia PIT, VAT i ZUS w jednym widoku',
                'Przypomnienie o terminie płatności',
                'Podgląd ewidencji i zestawień miesięcznych']),
        ],
    ],
    'background' => 'light', 'container' => 'content',
    'padding_top' => 'medium', 'padding_bottom' => 'medium',
]);

$funkcje = taxeon_seed_block('features', [
    'eyebrow' => $t('Funkcje', 'span'),
    'title' => $t('W cenie programu otrzymujesz', 'h2'),
    'head_size' => 'lg', 'title_width' => '24ch',
    'size' => 'sm', 'columns' => '3',
    'items' => array_map(fn($x) => ['kicker' => '', 'title' => $x[0], 'text' => $x[1], 'link' => ['title' => '', 'url' => '', 'target' => '']], [
        ['Faktury', 'Sprzedaż krajowa i zagraniczna, waluty obce, dane kontrahenta z bazy GUS.'],
        ['Koszty', 'Dokumenty kosztowe pobierasz bezpośrednio z KSeF. Prosto i szybko.'],
        ['KPiR i ryczałt', 'Ewidencja prowadzona na bieżąco, zależnie od Twojej formy opodatkowania.'],
        ['VAT i JPK', 'Rejestry VAT i wysyłka JPK_V7 realizowane po stronie księgowej.'],
        ['ZUS', 'Wyliczenie i zapis składek, uwzględnione w rozliczeniu podatkowym.'],
        ['KSeF', 'Wymiana faktur z Krajowym Systemem e-Faktur bez dodatkowych opłat.'],
        ['Środki trwałe', 'Ewidencja majątku i amortyzacja prowadzone w jednym widoku.'],
        ['Pojazdy', 'Rozliczenie kosztów pojazdu zgodnie ze schematem jego użytkowania.'],
        ['Bezpieczeństwo', 'Szyfrowanie 256-bit, codzienne kopie i dostęp do archiwum przez 6 lat.'],
    ]),
    'background' => 'gradient', 'padding_top' => 'large', 'padding_bottom' => 'large',
]);

$dostep = taxeon_seed_block('list', [
    'eyebrow' => $t('Dostęp', 'span'),
    'title' => $t('Przeglądarka, telefon, tablet.', 'h2'),
    'head_size' => 'sm',
    'text' => $t('Nic nie instalujesz i nie aktualizujesz. Logujesz się z dowolnego urządzenia, a dane są szyfrowane i kopiowane codziennie.'),
    'link' => $l('Wypróbuj za darmo', 'https://app.taxeon.pl/'),
    'row_style' => 'icon', 'columns' => '1',
    'items' => array_map(fn($x) => ['label' => $x[0], 'value' => '', 'tone' => 'default', 'text' => $x[1]], [
        ['Bez instalacji', 'Aplikacja działa w przeglądarce. Aktualizacje robimy po naszej stronie, Ty nic nie pobierasz.'],
        ['Wygodnie na telefonie', 'Fakturę wystawisz i koszt dodasz z telefonu — układ dostosowuje się do ekranu.'],
        ['Dane zostają Twoje', 'Zestawienia i dokumenty pobierzesz w każdej chwili, także po zakończeniu współpracy.'],
    ]),
    'background' => 'soft-2', 'container' => 'content',
    'padding_top' => 'medium-sm', 'padding_bottom' => 'medium-sm',
]);

$cta = taxeon_seed_block('cta', [
    'title_size' => 'xs',
    'title' => $t('Zobacz aplikację od środka.', 'h2'),
    'text' => 'Bez ukrytych kosztów i niespodzianek. Możesz zrezygnować w każdej chwili.',
    'button_primary' => $l('Wypróbuj za darmo', 'https://app.taxeon.pl/'),
    'button_secondary' => ['title' => '', 'url' => '', 'target' => ''],
    'background' => 'navy', 'padding_top' => 'medium', 'padding_bottom' => 'medium',
]);

$istnieje = get_page_by_path('aplikacja');
$id = wp_insert_post(wp_slash([
    'ID' => $istnieje ? $istnieje->ID : 0,
    'post_type' => 'page', 'post_status' => 'publish',
    'post_title' => 'Program księgowy online', 'post_name' => 'aplikacja',
    'post_content' => implode("\n\n", [$hero, $kroki, $funkcje, $dostep, $cta]),
]));
echo "  strona Aplikacja #{$id}\n";
