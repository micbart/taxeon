<?php

require __DIR__ . '/patch-helpers.php';

const NC_ZNACZNIK = 'taxeon_new_color_patch';

if (get_option(NC_ZNACZNIK) && !in_array('ponownie', $args ?? [], true)) {
    WP_CLI::error('Migracja już wykonana (' . get_option(NC_ZNACZNIK) . '). Dodaj argument „ponownie”, jeśli naprawdę chcesz ją powtórzyć.');
}

$spotkanie = home_url('/umow-spotkanie/');
$link = fn(string $title, string $url, string $target = '') => ['title' => $title, 'url' => $url, 'target' => $target];

WP_CLI::log('Teksty z nowej makiety');

nc_replace_everywhere([
    '137 tys.' => '149 tys.',
    '15 minut, bez zobowiązań' => '30 minut, bez zobowiązań',
    'W 15 minut powiemy' => 'W 30 minut powiemy',
    'Niska cena nie oznacza obciętej usługi.' => 'Niska cena nie oznacza ograniczonego zakresu naszych usług.',
    '<strong>Co dopłacasz osobno?</strong>' => '<strong>Za co dopłacasz osobno?</strong>',
    'Zamów Ryczałt →' => 'Poznaj zasady współpracy →',
    'Zamów ryczałt za 99 zł →' => 'Poznaj zasady współpracy →',
    'Zamów ryczałt →' => 'Poznaj zasady współpracy →',
    'Zamów KPiR →' => 'Poznaj zasady współpracy →',
    'Umów konsultację →' => 'Poznaj zasady współpracy →',
    'Landing branżowy · Start od zera' => 'B2B · Start od zera',
    'Landing branżowy · Ryczałt 12%' => 'Branża IT · Ryczałt 12%',
    'Landing branżowy · Ryczałt 14%' => 'Lekarze i Dentyści · Ryczałt 14%',
    'Landing branżowy · Ryczałt 17%' => 'Prawnicy · Ryczałt 15% lub 17%',
    'Tracisz na B2B' => 'Z czego rezygnujesz na B2B',
    'Zarobisz więcej, ale stracisz urlop' => 'Zarobisz więcej, ale możesz stracić urlop',
    'Bezpłatnie sprawdzimy, czy B2B Ci się opłaca. Jeśli tak — założymy firmę i poprowadzimy księgowość od 99 zł.'
        => 'Policz w kalkulatorze, czy B2B Ci się opłaca. Jeśli tak, poprowadzimy Twoją księgowość od 99 zł.',
    'Trzy waluty, trzy stawki VAT' => 'Trzy waluty, różne stawki VAT',
    'Płacisz z góry za dany miesiąc, najpóźniej do 5 dnia — fakturę dostajesz automatycznie w aplikacji. Rezygnujesz z miesiąca na miesiąc.'
        => 'Płacisz z góry za dany miesiąc — fakturę dostajesz automatycznie w aplikacji. Rezygnujesz w dowolnym momencie, bez okresu wypowiedzenia.',
    'Jeśli nie znajdziesz odpowiedzi — napisz lub zadzwoń, odpowiadamy' => 'Jeśli nie znajdziesz odpowiedzi — napisz, odpowiadamy',
    'Bezpłatna pomoc techniczna' => 'Bezpłatne wsparcie w bieżącej pracy',
    'Krótka decyzja' => 'Szybka decyzja',
    'Zamów KPiR za 169 zł.' => 'KPiR za 169 zł',
    'Albo umów krótką rozmowę — pomożemy' => 'Umów krótką rozmowę — pomożemy',
    'To znaczy, że nie tłumaczysz nam, czym jest ryczałt 14%, ani kiedy NFZ wypłaca środki. Wiemy.'
        => 'Nie tłumaczysz nam czym jest ryczałt 14% ani tego, że czasami musisz skorygować fakturę po jej wystawieniu np. ze względu na zmiany stawki czy ilości godzin.',
    'Ryczałt 17%, KPiR, pełna obsługa — od 99 zł miesięcznie.' => 'Ryczałt 15% lub 17%, KPiR, pełna obsługa — od 149 zł miesięcznie.',
    'radców prawnych, adwokatów, notariuszy i kancelarie spółkowe' => 'radców prawnych, adwokatów i notariuszy',
    'Kancelarie indywidualne i spółki partnerskie. Dyskrecja, doświadczenie w obsłudze zawodu zaufania publicznego, ryczałt 17%.'
        => 'Kancelarie indywidualne, współpraca B2B - dyskrecja, doświadczenie w obsłudze zawodu zaufania publicznego, ryczałt 15% lub 17%.',
    'Ryczałt 12%, faktury w euro i dolarach, rozliczenia VAT, kontrakty' => 'Ryczałt 12%, faktury w euro i dolarach, rozliczenia VAT, import usług, kontrakty',
    'Płacisz procent od przychodu — 8,5%, 12%, 14% czy 17%, zależnie od branży — nie odliczasz kosztów, papierologia minimalna. U nas pełna obsługa tej formy to 99 zł miesięcznie i tyle.'
        => 'Płacisz podatek od przychodu, według stawki zależnej od branży – 5,5%, 8,5%, 12%, 14% czy 17%, bez odliczania kosztów. U nas obsługa księgowa na ryczałcie bez VAT kosztuje 99 zł miesięcznie.',
]);

nc_replace_link('https://taxeon.youcanbook.me/', $spotkanie, ['umow-spotkanie']);

WP_CLI::log('Strona główna');

nc_page('strona-glowna', [
    0 => [
        'tone' => 'cream',
        'video' => '',
        'cards_0_text' => 'Pełna obsługa ryczałtu bez VAT. Dla wolnych zawodów, IT, B2B i wszystkich na ryczałcie.',
        'cards_0_items_0_text' => 'Ewidencja przychodów',
        'cards_0_link' => $link('Poznaj zasady współpracy →', $spotkanie),
        'cards_1_link' => $link('Poznaj zasady współpracy →', $spotkanie),
        'cards_2_items_0_text' => 'Wystawianie faktur i wysyłka do KSeF',
        'cards_2_items_1_text' => 'Pobieranie i rozksięgowanie kosztów z KSeF',
        'cards_2_items_2_text' => 'Wyliczenie podatków, ZUS i deklaracje',
        'stats_0_icon' => 'google',
        'stats_1_icon' => 'proof-calendar',
        'stats_2_icon' => 'proof-building',
        'stats_3_icon' => 'youtube-red',
    ],
    1 => ['background' => 'soft', 'items_3_highlight' => '1'],
    2 => function (array $d) {
        $zmiany = [
            'background' => 'navy-flat',
            'columns' => 3,
            'columns_0_label' => 'Ryczałt',
            'columns_0_note' => '99 zł',
            'columns_0_highlight' => '0',
            'columns_1_label' => 'Ryczałt + VAT',
            'columns_1_note' => '149 zł',
            'columns_1_highlight' => '0',
            'columns_2_label' => 'KPiR',
            'columns_2_note' => '169 zł',
            'columns_2_highlight' => '1',
            'columns_0_cta' => null,
            'columns_1_cta' => null,
            'columns_2_cta' => null,
            'cta_label' => null,
        ];

        if (($d['columns_1_label'] ?? '') === 'KPiR') {
            for ($r = 0; $r < (int) $d['rows']; $r++) {
                $ryczalt = $d["rows_{$r}_values_0_on"] ?? '0';
                $kpir = $d["rows_{$r}_values_1_on"] ?? '0';
                $vat = str_starts_with($d["rows_{$r}_label"], 'Rozliczenia VAT') ? '1' : $ryczalt;
                $zmiany["rows_{$r}_values"] = 3;
                $zmiany["rows_{$r}_values_0_on"] = $ryczalt;
                $zmiany["rows_{$r}_values_1_on"] = $vat;
                $zmiany["rows_{$r}_values_2_on"] = $kpir;
            }
        }

        return $zmiany;
    },
    3 => [
        'background' => 'light',
        'items_3_title' => 'Dokumenty',
        'items_3_text' => 'Dostępne w KSeF pobieramy samodzielnie. Pozostałe skanujesz, robisz zdjęcie telefonem i przesyłasz na maila. Resztę robimy my. Co miesiąc dostajesz raport i kompletne rozliczenie.',
    ],
    4 => ['background' => 'navy-flat'],
    5 => ['background' => 'light'],
    6 => ['background' => 'navy-flat'],
    7 => fn(array $d) => [
        'background' => 'soft',
        'title_text' => str_replace('.nBez', ".\nBez", $d['title_text'] ?? ''),
    ],
]);

WP_CLI::log('Podstrony: tła sekcji i hero');

$kremowe = ['tone' => 'cream', 'video' => ''];
$granatowe = ['tone' => 'dark', 'video' => ''];

nc_page('ryczalt', [0 => $kremowe, 1 => ['background' => 'gradient'], 2 => ['background' => 'light'], 3 => ['background' => 'navy']]);
nc_page('kpir', [0 => $kremowe, 1 => ['background' => 'gradient'], 2 => ['background' => 'light'], 3 => ['background' => 'light'], 4 => ['background' => 'navy']]);
nc_page('aplikacja', [0 => $kremowe, 1 => ['background' => 'gradient'], 2 => ['background' => 'light'], 3 => ['background' => 'soft'], 4 => ['background' => 'navy']]);
nc_page('cennik', [0 => $kremowe, 1 => ['background' => 'gradient'], 2 => ['background' => 'light'], 3 => ['background' => 'soft'], 4 => ['background' => 'navy']]);
nc_page('faq', [0 => $granatowe, 1 => ['background' => 'light'], 2 => ['background' => 'navy']]);
nc_page('biura-rachunkowe', [0 => $granatowe, 1 => ['background' => 'light'], 2 => ['background' => 'gradient'], 3 => ['background' => 'light'], 4 => ['background' => 'navy']]);
nc_page('b2b', [0 => $kremowe, 1 => ['background' => 'gradient'], 2 => ['background' => 'light'], 3 => ['background' => 'navy']]);
nc_page('it', [0 => $kremowe, 1 => ['background' => 'gradient'], 2 => ['background' => 'light'], 3 => ['background' => 'navy']]);
nc_page('lekarze', [0 => $kremowe, 1 => ['background' => 'gradient'], 2 => ['background' => 'light'], 3 => ['background' => 'soft'], 4 => ['background' => 'navy']]);
nc_page('prawnicy', [0 => $kremowe, 1 => ['background' => 'gradient'], 2 => ['background' => 'light'], 3 => ['background' => 'navy']]);

foreach (['polski-lad-kalkulator', 'kalkulator-uslug-ksiegowych', 'limit-uprawny', 'kasa-fiskalna', 'kalkulatory'] as $kalkulator) {
    nc_page($kalkulator, [0 => $kremowe + ['background' => null], 1 => ['background' => 'light'], 2 => ['background' => 'navy']]);
}

WP_CLI::log('Treści list z nowej makiety');

nc_replace_everywhere(json_decode(file_get_contents(__DIR__ . '/dane-tresci-new-color.json'), true));

$tak = '1';
$nie = '0';

nc_page('cennik', [
    0 => [
        'cards_0_link' => $link('Wypróbuj aplikację →', home_url('/aplikacja/')),
        'cards_1_link' => $link('Zasady współpracy →', $spotkanie),
        'cards_2_price' => '149 zł',
        'cards_2_link' => $link('Zasady współpracy →', $spotkanie),
        'cards_3_label' => 'KPiR + VAT',
        'cards_3_badge' => 'Popularny',
        'cards_3_link' => $link('Zasady współpracy →', $spotkanie),
    ],
    1 => function (array $d) use ($tak, $nie) {
        $wiersze = [
            ['Wystawianie faktur w aplikacji', 1, 1, 1, 1],
            ['Integracja z KSeF', 1, 1, 1, 1],
            ['Przypomnienia o terminach i składkach', 0, 1, 1, 1],
            ['Prowadzenie ewidencji przychodów / KPiR', 1, 1, 1, 1],
            ['Obliczanie miesięcznych zaliczek na PIT', 1, 1, 1, 1],
            ['Rozliczenia ZUS — DRA, korekty', 0, 1, 1, 1],
            ['Dedykowana księgowa — Twój opiekun', 0, 1, 1, 1],
            ['Wsparcie w obsłudze aplikacji', 1, 1, 1, 1],
            ['Pomoc przy zakładaniu firmy (CEIDG, ZUS)', 0, 1, 1, 1],
            ['Przeniesienie rozliczeń z innego biura', 0, 1, 1, 1],
            ['Rozliczenia VAT — JPK_V7, VAT-UE', 1, 0, 1, 1],
            ['Faktury zagraniczne, waluty i różnice kursowe', 1, 1, 1, 1],
            ['Rozliczanie kosztów i amortyzacji', 1, 0, 0, 1],
            ['Ewidencja środków trwałych', 1, 1, 1, 1],
            ['Rozliczenie kosztów pojazdu', 1, 0, 1, 1],
        ];
        $zmiany = ['columns_2_note' => '149 zł', 'columns_3_label' => 'KPiR + VAT'];

        foreach ($wiersze as $r => $w) {
            $zmiany["rows_{$r}_label"] = $w[0];
            $zmiany["rows_{$r}_values"] = 4;

            for ($k = 0; $k < 4; $k++) {
                $zmiany["rows_{$r}_values_{$k}_on"] = $w[$k + 1] ? $tak : $nie;
            }
        }

        return $zmiany;
    },
    2 => ['items_0_value' => '100 zł / os. / mies.', 'items_1_value' => '89 zł'],
]);

nc_page('ryczalt', [
    0 => ['text' => 'Płacisz podatek od przychodu, według stawki zależnej od branży – 5,5%, 8,5%, 12%, 14% czy 17%, bez odliczania kosztów. U nas obsługa księgowa na ryczałcie bez VAT kosztuje 99 zł miesięcznie. W cenie masz konsultacje z księgową, dostęp do aplikacji do fakturowania i obsługę KSeF — bez dodatkowych opłat.'],
    1 => ['items_2_value' => '15% lub 17%'],
]);

nc_page('aplikacja', [
    1 => fn(array $d) => array_merge(
        nc_list($d, 'items_0_points', array_map(fn($t) => ['text' => $t], [
            'Faktury krajowe, zagraniczne, w walutach obcych, w języku angielskim',
            'Faktury do paragonów, proforma',
            'Powtarzalne faktury z jednego szablonu',
            'Wysyłka do klienta mailem wprost z aplikacji',
            'Bezpośrednia wysyłka do KSeF',
        ])),
        nc_list($d, 'items_1_points', array_map(fn($t) => ['text' => $t], [
            'Jedno miejsce na wszystkie dokumenty',
            'Koszty pobrane z KSeF są gotowe do rozliczenia — wystarczy sprawdzić i zatwierdzić.',
            'Wygodnie rozliczysz środki trwałe i koszty pojazdów',
            'Archiwum dostępne przez 6 lat',
        ])),
        nc_list($d, 'items_2_points', array_map(fn($t) => ['text' => $t], [
            'Wyliczenia PIT, VAT i ZUS w jednym widoku',
            'Przypomnienie o terminie płatności',
            'Podgląd ewidencji i zestawień miesięcznych',
            'Szczegółowe informacje o naliczeniu ZUS i podatków po zamknięciu miesiąca',
            'Możliwość wygenerowania zestawień w pdf. lub xls. na potrzeby wniosku o kredyt, leasing',
        ])),
    ),
]);

$karty = fn(array $lista) => array_map(fn($w) => ['kicker' => '', 'title' => $w[0], 'text' => $w[1], 'link' => ['title' => '', 'url' => '', 'target' => '']], $lista);
$wiersze = fn(array $lista) => array_map(fn($w) => ['label' => $w[0], 'value' => '', 'text' => $w[1]], $lista);
$skladka = 'Uwzględniamy zmiany w składce ze względu na wysokość przychodów. Robimy rozliczenie roczne składki w cenie rozliczenia za kwiecień. Monitorujemy zmiany w przepisach. Ustalamy z Klientem jak chce ją naliczać - dwa sposoby do wyboru.';

nc_page('it', [
    1 => ['items_3_text' => $skladka],
    2 => fn(array $d) => nc_list($d, 'items', $wiersze([
        ['Cena', '99 zł miesięcznie to mniej niż jedna godzina Twojej stawki.'],
        ['Bez dopłat za faktury zagraniczne', 'U konkurencji za każdą fakturę spoza Polski często doliczają 20–50 zł. U nas — nie.'],
        ['Dedykowana księgowa', 'Nie czat z konsultantem, nie infolinia. Osoba, do której piszesz bezpośrednio.'],
        ['Zakupy usług od zagranicznych dostawców', 'Subskrypcje, licencje i oprogramowanie z zagranicy? Rozliczymy koszty, uwzględnimy ewentualne różnice kursowe oraz prawidłowo wykażemy transakcje w wymaganych deklaracjach i prześlemy je do urzędu.'],
    ])),
]);

nc_page('lekarze', [
    2 => fn(array $d) => nc_list($d, 'items', $karty([
        ['Ryczałt 14%', 'Prowadzimy ewidencję przychodów. Naliczamy podatek z ewentualnymi odliczeniami. Informujemy Cię o zmianach w przepisach.'],
        ['Składka zdrowotna', $skladka],
        ['Zwolnienie z VAT', 'Ustalamy rodzaj zwolnienia z VAT właściwy dla prowadzonej działalności, zarówno przedmiotowe (np. dla usług medycznych), jak i podmiotowe (do ustawowego limitu sprzedaży). Konfigurujemy aplikację księgową, w tym podstawy zwolnienia z VAT wykazywane na fakturach. Informujemy gdy przedsiębiorca zbliża się do przekroczenia limitu.'],
        ['Fakturowanie', 'Jeśli będziesz miał pytania możesz skonsultować z nami wystawienie faktury – jeśli wystawiasz ją w Taxeon sprawdzimy ją przed wysyłką do KSeF, aby uniknąć korekt.'],
        ['Sprzedaż dla osób fizycznych', 'Jeżeli masz kasę fiskalną rozliczymy Twoje przychody na podstawie raportu z kasy.'],
        ['Pomoc przy zakładaniu praktyki', 'Wpis do rejestru, NIP, ZUS, konto firmowe — przeprowadzimy Cię przez formalności.'],
    ])),
]);

nc_page('prawnicy', [
    1 => fn(array $d) => nc_list($d, 'items', $wiersze([
        ['Tajemnica zawodowa', 'Nie pytamy o szczegóły spraw, tylko o kwoty na fakturach. Procedury wewnętrzne mamy ustawione tak, żeby tego nie mieszać.'],
        ['Podatek VAT', 'Usługi prawne są w stawce podstawowej. Nie ma zwolnienia, nawet jeśli klient jest osobą fizyczną.'],
        ['Rozliczanie kosztów kancelarii', 'Ewidencjonujemy wydatki związane z prowadzoną działalnością prawniczą, w tym opłaty sądowe i skarbowe, koszty postępowań, składki członkowskie oraz rozliczenia kosztów zastępstwa procesowego. Rozróżniamy wydatki kancelarii od kwot ponoszonych w imieniu i na rzecz klientów.'],
        ['Sprzedaż dla osób fizycznych', 'Rozliczamy sprzedaż na podstawie raportów z kasy fiskalnej lub innych wymaganych ewidencji. W aplikacji wystawisz faktury do paragonów jeżeli tego oczekuje Twój Klient. Uwzględniamy obowiązujące zwolnienia z obowiązku stosowania kasy fiskalnej.'],
    ])),
    2 => [
        'items_0_text' => 'Większość kancelarii indywidualnych w Polsce jest na ryczałcie 17% — to najprostsza forma, jeśli koszty są niskie. Jeśli wynajmujesz lokal, zatrudniasz aplikanta albo masz inne istotne wydatki, KPiR z podatkiem liniowym może być korzystniejszy.',
        'items_0_links_0_link' => $link('Ryczałt 149 zł', home_url('/ryczalt/')),
    ],
]);

nc_page('faq', [
    1 => function (array $d) {
        if (($d['groups_2_items_2_question'] ?? '') === 'Czy ktoś mi przypomni o terminach?') {
            return [];
        }

        $zmiany = ['groups_2_items' => 5];

        for ($i = 4; $i >= 3; $i--) {
            $zmiany["groups_2_items_{$i}_question"] = $d['groups_2_items_' . ($i - 1) . '_question'];
            $zmiany["groups_2_items_{$i}_answer"] = $d['groups_2_items_' . ($i - 1) . '_answer'];
        }

        $zmiany['groups_2_items_2_question'] = 'Czy ktoś mi przypomni o terminach?';
        $zmiany['groups_2_items_2_answer'] = '<p>Tak. Przypominamy o terminach zapłaty podatków PIT, VAT i składek ZUS. Podajemy kwoty oraz przypominamy numer konta, na który trzeba dokonać wpłat.</p>';

        return $zmiany;
    },
]);

nc_page('b2b', [
    2 => [
        'items_1_text' => 'CEIDG, NIP, REGON, konto firmowe, ZUS — formalności załatwiasz samodzielnie, a my możemy zweryfikować Twoje dokumenty przed wysłaniem.',
        'items_2_text' => 'Pomagamy ją wystawić. W aplikacji Taxeon wystawisz fakturę zgodnie z wymogami KSeF.',
    ],
]);

nc_page('kpir', [
    0 => ['cards_0_badge' => 'Najpopularniejszy'],
    4 => ['button_secondary' => ['title' => '', 'url' => '', 'target' => '']],
]);

nc_page('aplikacja', [
    0 => fn(array $d) => array_merge(
        ['cards_0_text' => "Przez pierwsze 3 miesiące, potem 43 zł netto.\nKsięgujesz sam — my dajemy narzędzie."],
        nc_list($d, 'cards_0_items', array_map(fn($t) => ['text' => $t], [
            'Wystawianie faktur',
            'Ewidencje i wyliczenia podatku',
            'Naliczenie składek ZUS i ZUS DRA',
            'Pobieranie dokumentów kosztowych z KSeF',
            'Bez umowy lojalnościowej',
        ])),
    ),
]);

nc_page('cennik', [
    1 => [
        'surface' => 'glass',
        'cta_label' => null,
        'columns_0_cta' => null,
        'columns_1_cta' => null,
        'columns_2_cta' => null,
        'columns_3_cta' => null,
    ],
    2 => ['items_0_tone' => '', 'items_1_tone' => ''],
    4 => [
        'text' => 'Bezpłatne spotkanie online z księgową. 30 minut, bez zobowiązań.',
        'button_secondary' => $link('Zobacz FAQ o cenach', home_url('/faq/')),
    ],
]);

nc_page('biura-rachunkowe', [
    0 => fn(array $d) => ['title_text' => str_contains($d['title_text'], '[') ? $d['title_text'] : preg_replace('/12(\s|\x{00A0})zł/u', '[12$1zł]', $d['title_text'])],
    2 => ['eyebrow_text' => 'Funkcje programu', 'items_1_title' => 'Ryczałt i KPiR'],
    3 => ['plan_label' => 'KPiR & Ryczałt'],
]);

$bezPrzycisku = ['title' => '', 'url' => '', 'target' => ''];
$zasady = $link('Poznaj zasady współpracy →', $spotkanie);

nc_page('it', [0 => ['button_secondary' => $link('Poznaj zasady współpracy', $spotkanie)]]);
nc_page('lekarze', [0 => ['button_primary' => $zasady], 4 => ['button_primary' => $zasady, 'button_secondary' => $bezPrzycisku]]);
nc_page('prawnicy', [
    1 => ['head_position' => 'above', 'columns' => '2'],
    2 => [
        'items_1_title' => '',
        'items_1_eyebrow' => 'Pakiet dla kancelarii',
        'items_1_variant' => 'light',
        'items_1_list_style' => 'check',
    ],
    3 => ['button_secondary' => $bezPrzycisku],
]);

update_option(NC_ZNACZNIK, wp_date('Y-m-d H:i'), false);
WP_CLI::success('Gotowe');
