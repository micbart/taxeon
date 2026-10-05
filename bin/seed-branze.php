<?php
require get_template_directory() . '/bin/seed-helpers.php';
$t = fn(string $x, string $tag = 'p'): array => ['text' => $x, 'tag' => $tag];
$l = fn(string $tytul, string $url): array => ['title' => $tytul, 'url' => str_starts_with($url, 'http') || str_starts_with($url, 'tel:') ? $url : home_url($url), 'target' => ''];
$pkt = fn(array $x): array => array_map(fn($y) => ['text' => $y], $x);
$pusty = ['title' => '', 'url' => '', 'target' => ''];

$strony = [];

$strony['b2b'] = ['Kontrakt B2B', [
    taxeon_seed_block('hero', [
        'layout' => 'plain', 'button_style' => 'accent',
        'badge' => 'Landing branżowy · Start od zera',
        'title' => $t('Przechodzisz na B2B? Najpierw policz, potem podpisuj.', 'h1'),
        'subtitle' => 'Kalkulator UoP vs B2B, założenie firmy, pierwsza faktura — wszystko w jednym miejscu. Księgowość od 99 zł.',
        'text' => 'Pracodawca zaproponował Ci B2B. Zarobisz więcej, ale stracisz urlop, L4 i emeryturę z ZUS. Czy się opłaca? Zależy. Najpierw policz, zanim podpiszesz. Mamy kalkulator, który porówna obie opcje na konkretnych liczbach Twojego wynagrodzenia.',
        'button_primary' => $l('Otwórz kalkulator UoP vs B2B →', '/#kontakt'),
        'button_secondary' => $pusty, 'trust' => [], 'cards' => [], 'stats' => [], 'video' => '',
    ]),
    taxeon_seed_block('panels', [
        'eyebrow' => $t('Bilans zmiany', 'span'),
        'title' => $t('Co tracisz, a co zyskujesz na B2B.', 'h2'), 'text' => $t(''),
        'items' => [
            ['title' => 'Zyskujesz na B2B', 'variant' => 'positive', 'list_style' => 'rows', 'note' => '', 'link' => $pusty,
             'items' => $pkt(['Wyższą kwotę netto na konto — zwykle 20–40%.',
                'Możliwość rozliczania kosztów: sprzęt, paliwo, szkolenia.',
                'Większą swobodę pracy z kilkoma klientami jednocześnie.',
                'Ulgi podatkowe — IP Box, ulga na start.'])],
            ['title' => 'Tracisz na B2B', 'variant' => 'negative', 'list_style' => 'rows', 'note' => '', 'link' => $pusty,
             'items' => $pkt(['Urlop wypoczynkowy i chorobowy.',
                'Stabilność etatu i okres wypowiedzenia.',
                'Składkę emerytalną z ZUS, chyba że płacisz pełen ZUS.',
                'Pewność dochodu z miesiąca na miesiąc.'])],
        ],
        'background' => 'page', 'padding_top' => 'medium', 'padding_bottom' => 'medium',
    ]),
    taxeon_seed_block('timeline', [
        'eyebrow' => $t('Zanim wystawisz pierwszą fakturę', 'span'),
        'title' => $t('Cztery rzeczy do zrobienia, zanim zaczniesz B2B.', 'h2'), 'text' => $t(''),
        'items' => [
            ['number' => '01', 'title' => 'Wybierz formę opodatkowania', 'featured' => 0, 'text' => 'Ryczałt, podatek liniowy czy skala? Wybór zależy od branży, przychodu i kosztów. Pomożemy.'],
            ['number' => '02', 'title' => 'Załóż firmę', 'featured' => 0, 'text' => 'CEIDG, NIP, REGON, konto firmowe, ZUS — robimy to za Ciebie w 1–2 dni. Zajmujesz się tylko podpisem przez profil zaufany.'],
            ['number' => '03', 'title' => 'Pierwsza faktura', 'featured' => 0, 'text' => 'Pomagamy ją wystawić i sprawdzamy umowę z kontrahentem pod kątem ryzyka ukrytego samozatrudnienia.'],
            ['number' => '04', 'title' => 'Pilnowanie ZUS i podatków', 'featured' => 0, 'text' => 'Od pierwszego miesiąca obsługujemy Twoje rozliczenia. Ulga na start i preferencyjny ZUS przez 24 miesiące — przypominamy w odpowiednim momencie.'],
        ],
        'cta_title' => '', 'cta_text' => '', 'cta_link' => $pusty,
        'background' => 'gradient',
    ]),
    taxeon_seed_block('cta', [
        'title_size' => 'md',
        'title' => $t('Zacznij od kalkulatora. Skończ pierwszą fakturą.', 'h2'),
        'text' => 'Bezpłatnie sprawdzimy, czy B2B Ci się opłaca. Jeśli tak — założymy firmę i poprowadzimy księgowość od 99 zł.',
        'button_primary' => $l('Otwórz kalkulator UoP vs B2B →', '/#kontakt'),
        'button_secondary' => $l('Zapytaj o wycenę', '/#kontakt'),
        'background' => 'navy',
    ]),
]];

$strony['it'] = ['Księgowość dla IT', [
    taxeon_seed_block('hero', [
        'layout' => 'plain', 'button_style' => 'light',
        'badge' => 'Landing branżowy · Ryczałt 12%',
        'title' => $t('Księgowość dla IT. Faktury w euro, ryczałt 12%, zero stresu.', 'h1'),
        'subtitle' => 'Od 99 zł miesięcznie. Bez dopłat za faktury zagraniczne.',
        'text' => 'Wystawiasz faktury polskiemu software house\'owi, niemieckiemu klientowi i amerykańskiemu startupowi — wszystko w jednym miesiącu. Trzy waluty, trzy stawki VAT, jedna deklaracja. Robimy to codziennie. Ryczałt 12% dla IT to nasz chleb powszedni — nie musisz nam tłumaczyć, czym jest PKWiU 62.01.11.',
        'button_primary' => $l('Otwórz kalkulator B2B →', '/b2b/'),
        'button_secondary' => $l('Umów rozmowę z księgową', '/#kontakt'),
        'trust' => [], 'cards' => [], 'stats' => [], 'video' => '',
    ]),
    taxeon_seed_block('features', [
        'eyebrow' => $t('Zakres obsługi', 'span'),
        'title' => $t('Co konkretnie robimy dla programisty.', 'h2'), 'text' => $t(''),
        'size' => 'md', 'columns' => '2',
        'items' => [
            ['kicker' => '', 'title' => 'Ryczałt 12%', 'link' => $pusty, 'text' => 'Stawka dla usług programistycznych. Sprawdzamy, czy Twoja praca kwalifikuje się do 12% — a może masz 8,5% albo mieszankę.'],
            ['kicker' => '', 'title' => 'Faktury w EUR, USD, GBP', 'link' => $pusty, 'text' => 'Przeliczamy po właściwym kursie NBP i rozliczamy różnice kursowe.'],
            ['kicker' => '', 'title' => 'Faktury reverse charge', 'link' => $pusty, 'text' => 'Dla klientów biznesowych w UE bez polskiego VAT-u. Pilnujemy VAT-UE i informacji podsumowującej.'],
            ['kicker' => '', 'title' => 'ZUS i składka zdrowotna', 'link' => $pusty, 'text' => 'Przy ryczałcie liczy się od przychodu w trzech progach — 60 tys., 300 tys. i powyżej. Pilnujemy progu, żebyś nie wpadł w wyższy bez planowania.'],
        ],
        'background' => 'gradient',
    ]),
    taxeon_seed_block('list', [
        'eyebrow' => $t('Pierwsze pytanie na B2B', 'span'),
        'title' => $t('Ryczałt 12% czy podatek liniowy?', 'h2'),
        'text' => $t('Krótka odpowiedź: do mniej więcej 200 tys. przychodu rocznie ryczałt 12% prawie zawsze wygrywa. Powyżej trzeba liczyć. Mamy kalkulator, który zrobi to za Ciebie.'),
        'link' => $l('Kalkulator ryczałt vs liniowy →', '/b2b/'),
        'row_style' => 'stacked',
        'items' => [
            ['label' => 'Cena', 'value' => '', 'text' => '99 zł miesięcznie to mniej niż jedna godzina Twojej stawki.'],
            ['label' => 'Bez dopłat za faktury zagraniczne', 'value' => '', 'text' => 'U konkurencji za każdą fakturę spoza Polski często doliczają 20–50 zł. U nas — nie.'],
            ['label' => 'Dedykowana księgowa', 'value' => '', 'text' => 'Nie czat z konsultantem, nie infolinia. Osoba, do której piszesz bezpośrednio.'],
        ],
        'background' => 'page', 'padding_top' => 'medium', 'padding_bottom' => 'medium',
    ]),
    taxeon_seed_block('cta', [
        'title_size' => 'md',
        'title' => $t('Sprawdź w kalkulatorze, ile faktycznie zarobisz na B2B.', 'h2'),
        'text' => 'Wpisz stawkę godzinową lub miesięczne wynagrodzenie. W 60 sekund pokażemy Ci kwotę netto na rękę przy każdej formie opodatkowania.',
        'button_primary' => $l('Otwórz kalkulator B2B →', '/b2b/'),
        'button_secondary' => $l('Zapytaj o wycenę', '/#kontakt'),
        'background' => 'navy',
    ]),
]];

foreach ($strony as $slug => [$tytul, $bloki]) {
    $istnieje = get_page_by_path($slug);
    $id = wp_insert_post(wp_slash([
        'ID' => $istnieje ? $istnieje->ID : 0,
        'post_type' => 'page', 'post_status' => 'publish',
        'post_title' => $tytul, 'post_name' => $slug,
        'post_content' => implode("\n\n", $bloki),
    ]));
    echo "  {$slug} #{$id}\n";
}
