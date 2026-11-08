<?php

require get_template_directory() . '/bin/seed-helpers.php';

$t = fn(string $x, string $tag = 'p'): array => ['text' => $x, 'tag' => $tag];
$l = fn(string $tytul, string $url): array => ['title' => $tytul, 'url' => str_starts_with($url, 'http') || str_starts_with($url, '#') ? $url : home_url($url), 'target' => ''];
$pusty = ['title' => '', 'url' => '', 'target' => ''];
$kalendarz = 'https://taxeon.youcanbook.me/';
$mapy = 'https://www.google.pl/maps/search/TAXEON+-+Biuro+Rachunkowe+Warszawa.+Kamionkowska+51/';

$hero = taxeon_seed_block('hero', [
    'layout' => 'split', 'tone' => 'cream', 'button_style' => 'light',
    'badge' => 'Umów spotkanie',
    'title' => $t('Poznaj swoją księgową przed decyzją', 'h1'),
    'subtitle' => '',
    'text' => 'Umów bezpłatne spotkanie online. W 30 minut pokażemy, jak wygląda prosta księgowość w TAXEON — bez zobowiązań i bez sprzedażowego ciśnienia.',
    'checks' => [['text' => 'Za darmo'], ['text' => 'Bez zobowiązań'], ['text' => 'W 30 minut']],
    'button_primary' => $pusty,
    'button_secondary' => $l('Jak to działa →', '#jak'),
    'trust_style' => 'box',
    'trust' => [
        ['value' => '{ocena} / 5', 'label' => 'ocena w Google ({liczba} opinii)', 'stars' => 1, 'url' => $mapy, 'icon' => 'google'],
        ['value' => '149 tys.', 'label' => 'przedsiębiorców na naszym YouTube', 'stars' => 0, 'url' => '', 'icon' => 'youtube-red'],
        ['value' => 'Ryczałt {cena_ryczalt} zł', 'label' => 'netto / mies. za księgowość', 'stars' => 0, 'url' => '', 'icon' => ''],
        ['value' => 'KPiR {cena_kpir} zł', 'label' => 'netto / mies. za księgowość', 'stars' => 0, 'url' => '', 'icon' => ''],
    ],
    'cards' => [[
        'label' => '100% bezpłatnie', 'badge' => '', 'featured' => 1,
        'title' => 'Spotkanie online z księgową',
        'meta' => '30 min · wideorozmowa · po polsku',
        'items' => [
            ['text' => 'Bezpłatna, niezobowiązująca rozmowa'],
            ['text' => 'Poznasz swoją księgową twarzą w twarz'],
            ['text' => 'Wolne terminy jeszcze w tym tygodniu'],
        ],
        'link' => $l('Wybierz termin spotkania →', $kalendarz),
        'note' => 'Potwierdzenie i link do spotkania dostaniesz e-mailem',
    ]],
    'stats' => [], 'video' => '',
]);

$kroki = taxeon_seed_block('timeline', [
    'anchor' => 'jak',
    'eyebrow' => $t('Od spotkania do gotowej księgowości', 'span'),
    'title' => $t('Trzy kroki. Zero papierologii.', 'h2'),
    'text' => $t('Wszystko online — niezależnie od tego, gdzie mieszkasz. Przeniesienie z obecnego biura bierzemy na siebie.'),
    'items' => [
        ['number' => '1', 'title' => 'Spotkanie online', 'featured' => 0, 'text' => 'Poznajesz swoją księgową, dowiadujesz się, jak wygląda współpraca z naszym biurem, i otrzymujesz odpowiedzi na swoje pytania. Bez zobowiązań.'],
        ['number' => '2', 'title' => 'Przenosimy Twoje rozliczenia', 'featured' => 0, 'text' => 'Za darmo przenosimy dokumentację z Twojego obecnego biura. Weryfikację i uzupełnienie danych bierzemy na siebie.'],
        ['number' => '3', 'title' => 'Rozliczasz się bez stresu', 'featured' => 0, 'text' => 'Faktury wystawiasz w aplikacji, koszty pobieramy automatycznie z KSeF, informacje o rozliczeniu dostajesz mailem i SMS-em.'],
    ],
    'cta_title' => '', 'cta_text' => '', 'cta_link' => $pusty,
    'background' => 'gradient',
]);

$ryzyko = taxeon_seed_block('list', [
    'title' => $t('Zero ryzyka po Twojej stronie', 'h2'),
    'head_position' => 'above', 'columns' => '4', 'row_style' => 'icon',
    'items' => [
        ['label' => 'Bezpłatne przeniesienie', 'value' => '', 'text' => 'Przejęcie księgowości z innego biura nic nie kosztuje.'],
        ['label' => 'Bez zobowiązań', 'value' => '', 'text' => 'Spotkanie to rozmowa, nie umowa. Decydujesz w swoim tempie.'],
        ['label' => 'Wszystko online', 'value' => '', 'text' => 'Bez wożenia dokumentów — działasz z dowolnego miejsca.'],
        ['label' => 'Dedykowana księgowa', 'value' => '', 'text' => 'Masz stały, bezpośredni kontakt z konkretną osobą.'],
    ],
    'link' => $pusty,
    'background' => 'soft', 'padding_top' => 'medium', 'padding_bottom' => 'medium',
]);

$opinie = taxeon_seed_block('reviews', [
    'eyebrow' => $t('Opinie klientów', 'span'),
    'title' => $t('Przedsiębiorcy, którzy już śpią spokojnie', 'h2'),
    'text' => $t('Średnia {ocena}/5 w Google z {liczba} opinii.'),
    'link' => $l('Zobacz wszystkie opinie w Google', $mapy),
    'source' => 'both', 'limit' => 6, 'items' => [],
    'background' => 'gradient', 'padding_top' => 'medium', 'padding_bottom' => 'medium',
]);

$pytania = taxeon_seed_block('faq', [
    'title' => $t('Porozmawiajmy — to nic nie kosztuje', 'h2'),
    'text' => $t('Wybierz dogodny termin w kalendarzu. Nasza księgowa odpowie na Twoje pytania i omówi z Tobą zasady współpracy z naszym biurem.'),
    'link' => $l('Wybierz termin spotkania →', $kalendarz),
    'link_style' => 'button',
    'checks' => [
        ['text' => 'Odpowiemy na wszystkie Twoje pytania o księgowość'],
        ['text' => 'Omówimy aplikację i sposób pracy na żywo'],
        ['text' => 'Wyjaśnimy wszystkie zasady dotyczące współpracy'],
    ],
    'items_title' => 'Najczęstsze pytania',
    'open_first' => 1,
    'items' => [
        ['question' => 'Ile kosztuje spotkanie?', 'answer' => '<p>Spotkanie jest w 100% bezpłatne i nie zobowiązuje Cię do niczego. To rozmowa, podczas której poznasz nas i sprawdzisz, czy Taxeon pasuje do Twojej firmy.</p>'],
        ['question' => 'Jak długo trwa spotkanie?', 'answer' => '<p>Zwykle około 30 minut. Odbywa się online przez wideorozmowę — link otrzymasz e-mailem po wybraniu terminu.</p>'],
        ['question' => 'Prowadzę już firmę w innym biurze. Jak wygląda przeniesienie?', 'answer' => '<p>Przeniesienie jest bezpłatne i możesz je zrobić w dowolnym terminie. Ty dostarczasz dotychczasowe rozliczenia, a resztę formalności bierzemy na siebie.</p>'],
        ['question' => 'Czy muszę przyjeżdżać do biura?', 'answer' => '<p>Nie. Wszystko załatwisz online — dokumenty kosztowe przesyłasz jako zdjęcia lub skany, a faktury wystawiasz w aplikacji o dowolnej porze.</p>'],
        ['question' => 'Ile kosztuje księgowość w Taxeon?', 'answer' => '<p>Ryczałt już od {cena_ryczalt} zł netto miesięcznie, a KPiR od {cena_kpir} zł netto miesięcznie. Dokładną ofertę dopasujemy podczas spotkania.</p>'],
    ],
    'groups' => [],
    'background' => 'soft', 'padding_top' => 'medium', 'padding_bottom' => 'medium',
]);

$cta = taxeon_seed_block('cta', [
    'title_size' => 'md',
    'title' => $t('Czas na spokojną księgowość', 'h2'),
    'text' => 'Umów bezpłatne spotkanie już dziś. Bez zobowiązań — po prostu poznaj TAXEON.',
    'button_primary' => $l('Wybierz termin spotkania →', $kalendarz),
    'button_secondary' => $pusty,
    'background' => 'gradient',
]);

$istnieje = get_page_by_path('umow-spotkanie');
$id = wp_insert_post(wp_slash([
    'ID' => $istnieje ? $istnieje->ID : 0,
    'post_type' => 'page', 'post_status' => 'publish',
    'post_title' => 'Umów spotkanie', 'post_name' => 'umow-spotkanie',
    'post_content' => implode("\n\n", [$hero, $kroki, $ryzyko, $opinie, $pytania, $cta]),
]));

update_post_meta($id, 'rank_math_title', 'Umów bezpłatne spotkanie z księgową %sep% %sitename%');
update_post_meta($id, 'rank_math_description', 'Bezpłatne, 30-minutowe spotkanie online z księgową Taxeon. Poznasz zasady współpracy, ceny i aplikację — bez zobowiązań.');

WP_CLI::success("Strona Umów spotkanie #{$id}");
