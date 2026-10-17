<?php
require get_template_directory() . '/bin/seed-helpers.php';
$t = fn(string $x, string $tag = 'p'): array => ['text' => $x, 'tag' => $tag];
$l = fn(string $tytul, string $url): array => ['title' => $tytul, 'url' => str_starts_with($url, 'http') ? $url : home_url($url), 'target' => ''];
$pkt = fn(array $x): array => array_map(fn($y) => ['text' => $y], $x);
$pusty = ['title' => '', 'url' => '', 'target' => ''];

$hero = taxeon_seed_block('hero', [
    'layout' => 'center',
    'title_size' => '62', 'text_gap' => '24',
    'space_bottom' => '76',
    'badge' => 'Od ponad 14 lat wybierany przez biura rachunkowe w całej Polsce',
    'title' => $t('KPiR i Ryczałt dla biur rachunkowych za 12 zł mies. za firmę', 'h1'),
    'subtitle' => '',
    'text' => 'Rozliczaj więcej klientów w krótszym czasie. Ty ustalasz model współpracy, a Twoi klienci sami wystawiają faktury online.',
    'button_primary' => $l('Wypróbuj za darmo', 'https://app.taxeon.pl/'),
    'button_secondary' => $pusty,
    'checks' => $pkt(['Bez zobowiązań', 'Płacisz tylko za aktywne firmy',
        'Bezpłatne archiwum danych przez 6 lat', 'Bezpłatna wymiana danych z KSeF']),
    'trust' => [],
    'video' => taxeon_wideo('biura-hero.mp4'),
    'poster' => taxeon_seed_image('biura-hero-poster.jpg', 'Taxeon dla biur rachunkowych'),
    'cards' => [],
    'stats' => [
        ['value' => '14 lat', 'label' => 'na rynku', 'url' => ''],
        ['value' => '12 zł', 'label' => 'netto / firma / mies.', 'url' => '', 'highlight' => 1],
        ['value' => '6 lat', 'label' => 'bezpłatne archiwum', 'url' => ''],
        ['value' => '256-bit', 'label' => 'szyfrowanie danych', 'url' => ''],
    ],
]);

$filmy = taxeon_seed_block('videos', [
    'eyebrow' => $t('Zobacz Taxeon w akcji', 'span'),
    'title' => $t('Najważniejsze funkcje w kilka minut.', 'h2'),
    'text' => $t('Cztery krótkie filmy pokazujące, jak Taxeon usprawnia codzienną pracę biura.'),
    'head_width' => '60ch', 'columns' => '2', 'text_gap' => '16',
    'items' => array_map(fn($x) => ['url' => 'https://www.youtube.com/watch?v=' . $x[0], 'title' => $x[1], 'text' => $x[2]], [
        ['8_OEM_gmXPg', 'Dodaj nową firmę w jednym oknie', 'Dane pobierzesz z GUS i od razu skonfigurujesz program pod KPiR, ryczałt czy VAT.'],
        ['fuqM23eycGM', 'Klienci sami wystawiają faktury', 'Faktury klientów trafiają wprost do KPiR — Ty tylko weryfikujesz, czy wszystko się zgadza.'],
        ['-zKzDkocloY', 'Automatyczna wymiana dokumentów', 'Odbieraj dokumenty kosztowe od klientów w uporządkowanym systemie wymiany plików.'],
        ['2wGQLmJWGmo', 'Klient sam sprawdza podatki i ZUS', 'Zestawienia miesięczne, wydruki KPiR i wysokość podatku dostępne online — bez pytań do biura.'],
    ]),
    'background' => 'light', 'container' => 'reading',
    'padding_top' => 'medium-sm', 'padding_bottom' => 'medium-sm',
]);

$funkcje = taxeon_seed_block('features', [
    'anchor' => 'funkcje-biura',
    'eyebrow' => $t('Funkcje', 'span'),
    'title' => $t('Wszystko, czego potrzebuje biuro rachunkowe.', 'h2'),
    'head_size' => 'lg', 'title_width' => '24ch',
    'size' => 'sm', 'columns' => '3',
    'items' => array_map(fn($x) => ['kicker' => '', 'title' => $x[0], 'text' => $x[1], 'link' => $pusty], [
        ['Faktury', 'Wystawiaj faktury w kilka chwil, także w walutach obcych. Dane kontrahenta pobierzesz z bazy GUS.'],
        ['Ryczałt', 'Prowadzenie ryczałtu od przychodów ewidencjonowanych z automatycznym księgowaniem dokumentów.'],
        ['ZUS', 'Automatyczne rozliczenie i zapis składek ZUS, uwzględnione w rozliczeniu podatkowym.'],
        ['Rejestr VAT', 'Rejestry VAT zakupu i sprzedaży prowadzone automatycznie na podstawie dokumentów.'],
        ['Środki trwałe', 'Wygodna ewidencja środków trwałych — wszystkie dane w jednym miejscu.'],
        ['Pojazdy', 'Odliczysz VAT od kosztów związanych z pojazdem zgodnie ze schematem jego użytkowania.'],
        ['JPK V7M i V7K', 'Pełna zgodność z JPK V7 i automatyczna wysyłka z programu, z oznaczeniami procedur i GTU.'],
        ['Bezpieczeństwo', 'Automatyczne kopie danych i szyfrowanie 256-bit. Stały dostęp nawet po zakończeniu współpracy.'],
        ['Online', 'Pracuj gdzie i kiedy chcesz, z dowolnego komputera — Twój program księgowy jest zawsze pod ręką.'],
    ]),
    'background' => 'gradient', 'padding_top' => 'large', 'padding_bottom' => 'large',
]);

$cennik = taxeon_seed_block('pricing', [
    'anchor' => 'cennik-biura',
    'eyebrow' => $t('Cennik', 'span'),
    'title' => $t('Prosty cennik. Płacisz tylko za aktywne firmy.', 'h2'),
    'checks' => $pkt(['Bez abonamentu za konto', 'Bez opłat wstępnych', 'Bez limitu użytkowników w biurze']),
    'plan_label' => 'KPiR & Ryczałt',
    'currency' => 'zł',
    'periods' => [
        ['label' => 'Miesięcznie', 'price' => '12', 'note' => 'netto za każdą firmę / miesiąc'],
        ['label' => 'Rocznie', 'price' => '120', 'note' => 'netto za każdą firmę / rok'],
    ],
    'items' => $pkt(['Opłata tylko za aktywne firmy', 'KPiR, ryczałt, VAT, ZUS i fakturowanie',
        'Bezpłatne narzędzie do faktur dla klientów', 'Bezpłatna wymiana dokumentów z KSeF',
        'Bezpłatne archiwum danych przez 6 lat', 'Bezpłatna pomoc techniczna']),
    'link' => $l('Wypróbuj za darmo', 'https://app.taxeon.pl/'),
    'note' => 'Bez zobowiązań · bez karty płatniczej',
    'background' => 'light', 'container' => 'reading',
    'padding_top' => 'medium', 'padding_bottom' => 'medium',
]);

$gwarancja = taxeon_seed_block('list', [
    'anchor' => 'kontakt-biura',
    'eyebrow' => $t('Nasza gwarancja', 'span'),
    'title' => $t('Zero ryzyka na starcie.', 'h2'),
    'text' => $t('Wypróbuj Taxeon bez zobowiązań. Płacisz dopiero, gdy realnie rozliczasz firmy — a Twoje dane zawsze zostają pod Twoją kontrolą.'),
    'row_style' => 'icon', 'columns' => '1', 'text_gap' => '16',
    'link' => $l('Wypróbuj za darmo', 'https://app.taxeon.pl/'),
    'note' => 'Załóż konto w kilka minut · bez karty płatniczej',
    'items' => array_map(fn($x) => ['label' => $x[0], 'value' => '', 'tone' => 'default', 'text' => $x[1]], [
        ['Bez zobowiązań', 'Zaczynasz za darmo i płacisz dopiero za realnie rozliczane firmy. Rezygnujesz, kiedy chcesz.'],
        ['Twoje dane są bezpieczne', 'Codzienne kopie zapasowe i szyfrowanie 256-bit. Dostęp zachowujesz nawet po zakończeniu współpracy.'],
        ['Wsparcie bez dopłat', 'Pomożemy w obsłudze programu bez dodatkowych kosztów. Cała Polska, pn–pt 9:00–17:00.'],
    ]),
    'background' => 'navy', 'padding_top' => 'large', 'padding_bottom' => 'large',
]);

$istnieje = get_page_by_path('biura-rachunkowe');
$id = wp_insert_post(wp_slash([
    'ID' => $istnieje ? $istnieje->ID : 0,
    'post_type' => 'page', 'post_status' => 'publish',
    'post_title' => 'Program dla biur rachunkowych', 'post_name' => 'biura-rachunkowe',
    'post_content' => implode("\n\n", [$hero, $filmy, $funkcje, $cennik, $gwarancja]),
]));
echo "  strona dla biur #{$id}\n";
