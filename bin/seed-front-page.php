<?php

if (!defined('WP_CLI') || !WP_CLI) {
    exit("Uruchom przez: wp eval-file wp-content/themes/raks/bin/seed-front-page.php\n");
}

require_once __DIR__ . '/seed-helpers.php';

WP_CLI::log('Strony…');

$pages = [
    'ksiegowy' => raks_seed_page('Program Księgowy', 'program-ksiegowy', 'Pełna księgowość, KPiR, ryczałt, JPK, e-Sprawozdania i środki trwałe.'),
    'kadrowy' => raks_seed_page('Program Kadrowo-Płacowy', 'program-kadrowo-placowy', 'Kadry, płace, ZUS, PIT, PPK, umowy i pełna obsługa e-ZUS.'),
    'sprzedaz' => raks_seed_page('Sprzedaż i Magazyn', 'sprzedaz-i-magazyn', 'Fakturowanie, magazyn, zamówienia, WZ/PZ i kasy fiskalne.'),
    'chmura' => raks_seed_page('RAKS w Chmurze', 'raks-w-chmurze', 'Ten sam program dostępny przez przeglądarkę, z każdego miejsca.'),
    'lokalnie' => raks_seed_page('RAKS Lokalnie', 'raks-lokalnie', 'Wersja desktopowa z licencją wieczystą i pełną kontrolą nad danymi.'),
    'wersje' => raks_seed_page('Aktualne wersje', 'aktualne-wersje', 'Pobierz najnowsze wydania programów RAKS.'),
    'szkolenia' => raks_seed_page('Szkolenia', 'szkolenia', 'Bezpłatne webinary i szkolenia online z ekspertami RAKS.'),
    'o-nas' => raks_seed_page('O nas', 'o-nas', 'Polski producent oprogramowania dla firm i biur rachunkowych od 1991 roku.'),
    'kontakt' => raks_seed_page('Kontakt', 'kontakt', 'Napisz lub zadzwoń — odpowiadamy średnio w ciągu 60 minut.'),
    'wyprobuj' => raks_seed_page('Wypróbuj za darmo', 'wyprobuj', 'Pobierz bezpłatne demo albo załóż konto w chmurze.'),
    'prezentacja' => raks_seed_page('Umów prezentację', 'prezentacja', 'Trzydzieści minut online, bez zobowiązań.'),
];

$url = static fn(string $slug): string => get_permalink($pages[$slug]);
$link = static fn(string $slug, string $title): array => [
    'title' => $title,
    'url' => get_permalink($pages[$slug]),
    'target' => '',
];

$t = static fn(string $text, string $tag = ''): array => array_filter(['text' => $text, 'tag' => $tag]);

$btn = static fn(array $link, string $variant = '', string $size = '', string $icon = ''): array
    => array_filter(['link' => $link, 'variant' => $variant, 'size' => $size, 'icon' => $icon]);

WP_CLI::log('Media…');

$img = [
    'ksiegowy' => raks_seed_image('programy/ksiegowy.jpg', 'Księgowa pracująca przy biurku'),
    'kadrowy' => raks_seed_image('programy/kadrowy.png', 'Specjalistka kadrowa przy laptopie'),
    'sprzedaz' => raks_seed_image('programy/sprzedaz.jpg', 'Pracownice w magazynie podczas inwentaryzacji'),
    'zespol' => raks_seed_image('dlaczego/zespol.jpg', 'Zespół RAKS w biurze'),
    'konsultanci' => raks_seed_image('wsparcie/konsultanci.jpg', 'Konsultant wsparcia RAKS podczas rozmowy z klientem'),
    'umowy' => raks_seed_image('wsparcie/umowy.jpg', 'Specjalista RAKS analizujący umowę serwisową'),
    'alarmowe' => raks_seed_image('wsparcie/alarmowe.jpg', 'Kobieta w biurze podczas pilnej rozmowy telefonicznej'),
    'szkolenia' => raks_seed_image('wsparcie/szkolenia.jpg', 'Zespół podczas szkolenia w sali konferencyjnej'),
    'filmy' => raks_seed_image('wsparcie/filmy.jpg', 'Kobieta w słuchawkach oglądająca szkolenie online'),
    'baza' => raks_seed_image('wsparcie/baza-wiedzy.jpg', 'Kobieta z tabletem w bibliotece'),
];

WP_CLI::log('Bloki strony głównej…');

$blocks = [];

$blocks[] = raks_seed_block('hero', [
    'badge_highlight' => '35 lat',
    'badge_text' => 'na polskim rynku',
    'title' => $t('Księgowość, Kadry i Sprzedaż w jednym systemie', 'h1'),
    'subtitle' => $t('RAKS to oprogramowanie stworzone przez praktyków dla praktyków. Od 35 lat pomagamy firmom i biurom rachunkowym w codziennej pracy – prosto, szybko i bez komplikacji.', 'p'),
    ...raks_seed_hero_video(),
    'button_primary' => $btn($link('wyprobuj', 'Wypróbuj za darmo'), 'on-navy', 'lg'),
    'button_secondary' => $btn($link('prezentacja', 'Umów prezentację'), 'ghost-navy', 'lg', 'play-circle'),
    'trust' => [
        ['text' => 'Bezpłatne wsparcie'],
        ['text' => '16 000 firm i biur rachunkowych korzysta z RAKS'],
        ['text' => 'Chmura lub instalacja lokalna'],
    ],
]);

$blocks[] = raks_seed_block('choice', [
    'eyebrow' => $t('Chmura czy lokalnie', 'span'),
    'title' => $t('Ty decydujesz, jak pracujesz', 'h2'),
    'text' => $t('Pełna niezależność na własnym sprzęcie albo koniec z przywiązaniem do jednego komputera. RAKS daje obie drogi, a Ty wybierasz tę, która pasuje do Twojej firmy.', 'p'),
    'cards' => [
        [
            'featured' => true,
            'icon' => 'cloud',
            'tag' => 'RAKS w Chmurze',
            'title' => $t('Pracuj online, z każdego miejsca', 'h3'),
            'text' => $t('Ten sam sprawdzony program, ale dostępny przez przeglądarkę z dowolnego urządzenia, 24/7. Dane bezpieczne w centrum danych, aktualizacje instalują się automatycznie.', 'p'),
            'items' => [
                ['text' => 'Dostęp z przeglądarki, zero instalacji'],
                ['text' => 'Automatyczne aktualizacje przepisów'],
                ['text' => 'Codzienne kopie zapasowe w chmurze'],
                ['text' => 'Praca wielostanowiskowa i zdalna'],
            ],
        ],
        [
            'icon' => 'hard-drive',
            'tag' => 'RAKS Lokalnie',
            'title' => $t('Pełna kontrola na Twoim sprzęcie', 'h3'),
            'text' => $t('Sprawdzona wersja desktopowa z licencją wieczystą, bez obowiązkowych subskrypcji i z pełną kontrolą nad danymi. Idealne dla firm, które cenią niezależność.', 'p'),
            'items' => [
                ['text' => 'Licencja wieczysta – zapłać raz'],
                ['text' => 'Dane zostają u Ciebie, praca offline'],
                ['text' => 'Maksymalna wydajność'],
                ['text' => 'Praca wielostanowiskowa w sieci lokalnej'],
            ],
        ],
    ],
    'footnote' => 'Oba rozwiązania korzystają z <b>tych samych algorytmów</b> i zapewniają identyczną funkcjonalność. Różni je tylko sposób dostępu.',
]);

$blocks[] = raks_seed_block('programs', [
    'background' => 'soft',
    'eyebrow' => $t('Nasze programy', 'span'),
    'title' => $t('Program dopasowany do Twojej firmy', 'h2'),
    'text' => $t('Moduły, które płynnie współpracują ze sobą – dane przepływają automatycznie między księgowością, kadrami i sprzedażą.', 'p'),
    'programs' => [
        [
            'image' => $img['ksiegowy'],
            'title' => $t('Program Księgowy', 'h3'),
            'text' => $t('Pełna księgowość, księga przychodów i rozchodów, ryczałt, środki trwałe, deklaracje podatkowe i e-Sprawozdania. Automatyczna współpraca z KSeF. Zawsze zgodne z aktualnymi przepisami.', 'p'),
            'tags' => [
                ['label' => 'KPiR'], ['label' => 'Pełne księgi'], ['label' => 'JPK'],
                ['label' => 'KSeF'], ['label' => 'e-Sprawozdania'], ['label' => 'Środki trwałe'], ['label' => 'VAT'],
            ],
            'link' => $link('ksiegowy', 'Dowiedz się więcej'),
        ],
        [
            'image' => $img['kadrowy'],
            'title' => $t('Program Kadrowo-Płacowy', 'h3'),
            'text' => $t('Kadry, płace, ZUS, PIT, PPK, umowy cywilnoprawne i pełna obsługa e-ZUS. Automatyczne naliczanie wynagrodzeń – oszczędź godziny pracy każdego miesiąca.', 'p'),
            'tags' => [
                ['label' => 'Kadry'], ['label' => 'Płace'], ['label' => 'ZUS'], ['label' => 'PIT'],
                ['label' => 'PPK'], ['label' => 'e-Pracownik'], ['label' => 'e-Zwolnienia'],
            ],
            'link' => $link('kadrowy', 'Dowiedz się więcej'),
        ],
        [
            'image' => $img['sprzedaz'],
            'title' => $t('Sprzedaż i Magazyn', 'h3'),
            'text' => $t('Fakturowanie, magazyn, zamówienia, dokumenty WZ/PZ, obsługa drukarek fiskalnych. Pełna kontrola nad sprzedażą i stanami magazynowymi w jednym miejscu. Kompletacja i produkcja.', 'p'),
            'tags' => [
                ['label' => 'Faktury VAT'], ['label' => 'Magazyn'], ['label' => 'WZ/PZ'],
                ['label' => 'Produkcja'], ['label' => 'Drukarki fiskalne'],
            ],
            'link' => $link('sprzedaz', 'Dowiedz się więcej'),
        ],
    ],
]);

$blocks[] = raks_seed_block('features', [
    'background' => 'soft',
    'eyebrow' => $t('Dlaczego RAKS', 'span'),
    'title' => $t('Dlaczego ponad 16 000 firm i biur rachunkowych wybrało RAKS?', 'h2'),
    'text' => $t('U nas nie płacisz za funkcje, których nie używasz. RAKS to system, który dostosowujesz do siebie – nie odwrotnie. Oto, co nas wyróżnia.', 'p'),
    'background_image' => $img['zespol'],
    'items' => [
        [
            'icon' => 'cloud-cog',
            'title' => $t('Elastyczny wybór', 'h3'),
            'text' => $t('Ty decydujesz, jak pracujesz. RAKS działa online w chmurze – z dostępem z każdego miejsca – lub jako instalacja na Twoim komputerze. Zacznij w chmurze i przenieś się lokalnie, albo odwrotnie.', 'p'),
        ],
        [
            'icon' => 'tag',
            'title' => $t('Uczciwe ceny', 'h3'),
            'text' => $t('Przejrzyste ceny, brak ukrytych opłat i brak wymuszonych pakietów. Płacisz tylko za moduły, których potrzebujesz. Porównaj nasze ceny – różnica Cię zaskoczy.', 'p'),
        ],
        [
            'icon' => 'mouse-pointer-click',
            'title' => $t('Prostota obsługi', 'h3'),
            'text' => $t('Intuicyjny interfejs, który szybko opanujesz. Koniec z kosztownymi szkoleniami wdrożeniowymi i skomplikowanym menu. Najważniejsze funkcje są zawsze pod ręką.', 'p'),
        ],
        [
            'icon' => 'landmark',
            'title' => $t('35 lat na rynku', 'h3'),
            'text' => $t('Nie jesteśmy startupem, który jutro może zniknąć. RAKS działa od 1991 roku. Znamy polskie przepisy, polską księgowość i polskie realia biznesowe jak nikt inny.', 'p'),
        ],
    ],
]);

$blocks[] = raks_seed_block('support', [
    'background' => 'soft',
    'eyebrow' => $t('Wsparcie', 'span'),
    'title' => $t('Bezpłatne wsparcie, które naprawdę działa', 'h2'),
    'text' => $t('U nas rozmawiasz z człowiekiem, nie z botem. Nasi konsultanci to specjaliści z wieloletnim doświadczeniem w księgowości, kadrach i sprzedaży.', 'p'),
    'items' => [
        [
            'image' => $img['konsultanci'],
            'focal' => '58% 34%',
            'icon' => 'headset',
            'title' => $t('Żywi konsultanci', 'h3'),
            'text' => $t('Wyślij zgłoszenie – odpowiadamy średnio w ciągu 60 minut – albo zadzwoń i porozmawiaj z konsultantem, który zna Twój program. Bez kolejek i przekierowań.', 'p'),
            'link' => $link('kontakt', 'Dowiedz się więcej'),
        ],
        [
            'image' => $img['umowy'],
            'focal' => '60% 40%',
            'icon' => 'shield-check',
            'title' => $t('Umowy serwisowe', 'h3'),
            'text' => $t('Priorytetowe wsparcie techniczne i pomoc w sytuacjach krytycznych. Elastyczne pakiety dopasowane do Twojej firmy, bez długoterminowych zobowiązań.', 'p'),
            'link' => $link('kontakt', 'Dowiedz się więcej'),
        ],
        [
            'image' => $img['alarmowe'],
            'focal' => '62% 32%',
            'icon' => 'siren',
            'title' => $t('Połączenia alarmowe', 'h3'),
            'text' => $t('Zamknięcie roku, e-Sprawozdanie, termin ZUS, awaria przed wysłaniem JPK – gwarantujemy priorytetowy dostęp do konsultanta, gdy liczy się każda minuta.', 'p'),
            'link' => $link('kontakt', 'Dowiedz się więcej'),
        ],
        [
            'image' => $img['szkolenia'],
            'focal' => '60% 50%',
            'icon' => 'graduation-cap',
            'title' => $t('Bezpłatne szkolenia', 'h3'),
            'text' => $t('Regularne webinary i szkolenia online prowadzone przez ekspertów RAKS – bezpłatne dla wszystkich użytkowników z aktywną licencją.', 'p'),
            'link' => $link('szkolenia', 'Zobacz szkolenia'),
        ],
        [
            'image' => $img['filmy'],
            'focal' => '60% 35%',
            'icon' => 'play',
            'title' => $t('Filmy instruktażowe', 'h3'),
            'text' => $t('Biblioteka filmów krok po kroku – każda funkcja wyjaśniona na wideo. Ucz się we własnym tempie, od podstaw po zaawansowane funkcje.', 'p'),
            'link' => ['title' => 'Zobacz na YouTube', 'url' => RAKS_YOUTUBE, 'target' => '_blank'],
        ],
        [
            'image' => $img['baza'],
            'focal' => '55% 28%',
            'icon' => 'book-open',
            'title' => $t('Baza wiedzy i blog', 'h3'),
            'text' => $t('Rozbudowana baza artykułów, poradników i instrukcji dostępna 24/7 oraz blog z praktyczną wiedzą o księgowości, kadrach i podatkach.', 'p'),
            'link' => ['title' => 'Przejdź do bazy wiedzy', 'url' => RAKS_BAZA_WIEDZY, 'target' => '_blank'],
        ],
    ],
]);

$blocks[] = raks_seed_block('advisor', [
    'eyebrow' => $t('Twój doradca biznesowy', 'span'),
    'icon' => 'user-round',
    'title' => $t('Sprawdź RAKS w akcji.', 'h2'),
    'text' => $t('Umów się na 30-minutową rozmowę online. Bez presji — konkretna rozmowa o Twoich potrzebach i demo na żywo. A jeśli wolisz najpierw sprawdzić system samodzielnie, pobierz bezpłatne demo.', 'p'),
    'benefits' => [
        ['text' => 'Demo na żywo — widzisz działający system'],
        ['text' => 'Odpowiedzi na wszystkie pytania'],
        ['text' => 'Bez zobowiązań, zawsze bezpłatnie'],
    ],
    'button_primary' => $btn($link('prezentacja', 'Umów bezpłatną prezentację online'), 'on-navy', 'lg'),
    'button_secondary' => $btn($link('wyprobuj', 'Pobierz bezpłatne DEMO'), 'ghost-navy', 'lg', 'download'),
    'stats' => [
        ['icon' => 'award', 'value' => '35 lat', 'label' => 'na rynku polskim'],
        ['icon' => 'building-2', 'value' => '16 000', 'label' => 'firm i biur rachunkowych pracuje na RAKS'],
        ['icon' => 'youtube', 'value' => '75 000', 'label' => 'specjalistów śledzących nasz kanał na YouTube'],
    ],
]);

$blocks[] = raks_seed_block('latest-posts', [
    'background' => 'soft',
    'eyebrow' => $t('Z bloga RAKS', 'span'),
    'title' => $t('Najnowsze artykuły', 'h2'),
    'link' => [
        'title' => 'Zobacz wszystkie wpisy',
        'url' => get_permalink((int) get_option('page_for_posts')),
        'target' => '',
    ],
    'source' => 'latest',
    'count' => 3,
]);

$frontPageId = (int) get_option('page_on_front');

if (!$frontPageId) {
    $frontPageId = raks_seed_page('Strona główna', 'strona-glowna');
    update_option('show_on_front', 'page');
    update_option('page_on_front', $frontPageId);
}

wp_update_post([
    'ID' => $frontPageId,
    'post_title' => 'Strona główna',
    'post_content' => wp_slash(implode("\n\n", $blocks)),
    'post_status' => 'publish',
]);

WP_CLI::log('  strona główna zaktualizowana (ID ' . $frontPageId . ', bloków: ' . count($blocks) . ')');

WP_CLI::log('Opcje motywu…');

update_field(raks_seed_key('header_menu'), [
    [
        'link' => ['title' => 'Produkty', 'url' => $url('ksiegowy'), 'target' => ''],
        'submenu_width' => 'wide',
        'submenu' => [
            [
                'link' => $link('ksiegowy', 'Program Księgowy'),
                'icon' => 'calculator',
                'description' => 'Pełna księgowość, KPiR, ryczałt, JPK, e-Sprawozdania i środki trwałe.',
            ],
            [
                'link' => $link('kadrowy', 'Program Kadrowo-Płacowy'),
                'icon' => 'users',
                'description' => 'Kadry, płace, ZUS, PIT, PPK, umowy i pełna obsługa e-ZUS.',
            ],
            [
                'link' => $link('sprzedaz', 'Sprzedaż i Magazyn'),
                'icon' => 'shopping-cart',
                'description' => 'Fakturowanie, magazyn, zamówienia, WZ/PZ i kasy fiskalne.',
            ],
        ],
    ],
    [
        'link' => $link('chmura', 'RAKS w Chmurze'),
        'submenu_width' => 'wide',
        'submenu' => [],
    ],
    [
        'link' => $link('lokalnie', 'RAKS Lokalnie'),
        'submenu_width' => 'wide',
        'submenu' => [],
    ],
    [
        'link' => ['title' => 'Wsparcie', 'url' => $url('wersje'), 'target' => ''],
        'submenu_width' => 'narrow',
        'submenu' => [
            [
                'link' => $link('wersje', 'Aktualne wersje'),
                'icon' => 'refresh-cw',
                'description' => 'Pobierz najnowsze wydania programów RAKS.',
            ],
            [
                'link' => $link('szkolenia', 'Szkolenia'),
                'icon' => 'graduation-cap',
                'description' => 'Bezpłatne webinary i szkolenia online z ekspertami RAKS.',
            ],
            [
                'link' => ['title' => 'Baza wiedzy', 'url' => get_permalink((int) get_option('page_for_posts')), 'target' => ''],
                'icon' => 'book-open',
                'description' => 'Poradniki, instrukcje i odpowiedzi na pytania.',
            ],
            [
                'link' => ['title' => 'Filmy instruktażowe', 'url' => home_url('/#wsparcie'), 'target' => ''],
                'icon' => 'play-circle',
                'description' => 'Szkolenia wideo krok po kroku.',
            ],
            [
                'link' => $link('kontakt', 'Formularz zgłoszeniowy'),
                'icon' => 'file-text',
                'description' => 'Zgłoś pytanie lub problem do działu wsparcia.',
            ],
        ],
    ],
    [
        'link' => $link('o-nas', 'O nas'),
        'submenu_width' => 'wide',
        'submenu' => [],
    ],
], 'option');

update_field(raks_seed_key('header_contact_link'), $link('kontakt', 'Kontakt'), 'option');
update_field(raks_seed_key('header_cta'), $link('wyprobuj', 'Wypróbuj za darmo'), 'option');

update_field(raks_seed_key('footer_description'), 'Polski producent oprogramowania księgowego, kadrowego i sprzedażowego. Tworzymy programy, które naprawdę pomagają polskim firmom – od 1991 roku.', 'option');
update_field(raks_seed_key('phone_sales_label'), 'Dział sprzedaży', 'option');
update_field(raks_seed_key('phone_sales'), '885 885 000', 'option');
update_field(raks_seed_key('phone_support_label'), 'Pomoc techniczna', 'option');
update_field(raks_seed_key('phone_support'), '885 885 000', 'option');
update_field(raks_seed_key('phone_hours'), 'pon–pt 9:00–17:00', 'option');

update_field(raks_seed_key('support_link'), [
    'title' => 'Formularz zgłoszeniowy',
    'url' => RAKS_FORMULARZ,
    'target' => '_blank',
], 'option');

update_field(raks_seed_key('hero_video'), raks_seed_hero_video()['video_url'], 'option');
update_field(raks_seed_key('address'), 'ul. Kamionkowska 51, 03-812 Warszawa', 'option');
update_field(raks_seed_key('remote_help'), $link('kontakt', 'Pomoc zdalna'), 'option');
update_field(raks_seed_key('copyrights'), '© 1991–' . gmdate('Y') . ' RAKS. Wszelkie prawa zastrzeżone.', 'option');

WP_CLI::log('Menu…');

$blogUrl = get_permalink((int) get_option('page_for_posts'));

$footerColumns = [
    'Produkty' => [
        ['title' => 'Program Księgowy', 'url' => $url('ksiegowy')],
        ['title' => 'Kadrowo-Płacowy', 'url' => $url('kadrowy')],
        ['title' => 'Sprzedaż i Magazyn', 'url' => $url('sprzedaz')],
        ['title' => 'Wypróbuj za darmo', 'url' => $url('wyprobuj')],
    ],
    'Rozwiązania' => [
        ['title' => 'RAKS w Chmurze', 'url' => $url('chmura')],
        ['title' => 'RAKS Lokalnie', 'url' => $url('lokalnie')],
        ['title' => 'Aktualne wersje', 'url' => $url('wersje')],
    ],
    'Wsparcie' => [
        ['title' => 'Aktualne wersje', 'url' => $url('wersje')],
        ['title' => 'Baza wiedzy', 'url' => RAKS_BAZA_WIEDZY],
        ['title' => 'Szkolenia', 'url' => $url('szkolenia')],
        ['title' => 'Filmy instruktażowe', 'url' => RAKS_YOUTUBE],
        ['title' => 'Pomoc zdalna', 'url' => $url('kontakt')],
    ],
    'Firma' => [
        ['title' => 'O nas', 'url' => $url('o-nas')],
        ['title' => 'Blog', 'url' => $blogUrl],
        ['title' => 'Kontakt', 'url' => $url('kontakt')],
        ['title' => 'Umów prezentację', 'url' => $url('prezentacja')],
    ],
];

$footerRepeater = [];

foreach ($footerColumns as $title => $items) {
    $menuId = raks_seed_menu('Stopka: ' . $title, '', $items);

    if ($menuId) {
        $footerRepeater[] = ['title_menu' => $title, 'select_menu' => $menuId];
    }
}

update_field(raks_seed_key('footer_menus'), $footerRepeater, 'option');

raks_seed_menu('Stopka: Dokumenty', 'legalMenu', [
    ['title' => 'Polityka prywatności', 'url' => RAKS_POLITYKA_PRYWATNOSCI],
    ['title' => 'Strona główna', 'url' => home_url('/')],
]);

raks_seed_menu('Menu mobilne', 'mobileMenu', [
    ['title' => 'Program Księgowy', 'url' => $url('ksiegowy')],
    ['title' => 'Program Kadrowo-Płacowy', 'url' => $url('kadrowy')],
    ['title' => 'Sprzedaż i Magazyn', 'url' => $url('sprzedaz')],
    ['title' => 'RAKS w Chmurze', 'url' => $url('chmura')],
    ['title' => 'RAKS Lokalnie', 'url' => $url('lokalnie')],
    ['title' => 'Szkolenia', 'url' => $url('szkolenia')],
    ['title' => 'O nas', 'url' => $url('o-nas')],
    ['title' => 'Kontakt', 'url' => $url('kontakt')],
]);

\App\Services\ThemeCache::flush('options');
\App\Services\ThemeCache::flush('menus');

WP_CLI::success('Strona główna wypełniona. Cache motywu wyczyszczony.');
