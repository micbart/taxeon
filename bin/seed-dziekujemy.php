<?php

require get_template_directory() . '/bin/seed-helpers.php';

$t = fn(string $x, string $tag = 'p'): array => ['text' => $x, 'tag' => $tag];
$l = fn(string $tytul, string $url): array => ['title' => $tytul, 'url' => home_url($url), 'target' => ''];
$pusty = ['title' => '', 'url' => '', 'target' => ''];

$hero = taxeon_seed_block('hero', [
    'layout' => 'split', 'tone' => 'cream', 'button_style' => 'light',
    'badge' => 'Spotkanie zarezerwowane',
    'title' => $t('Dziękujemy, do zobaczenia na spotkaniu', 'h1'),
    'subtitle' => '',
    'text' => 'Twój termin został zapisany. Potwierdzenie i link do spotkania wyślemy na podany adres e-mail.',
    'button_primary' => $l('Wróć na stronę główną', '/'),
    'button_secondary' => $l('Zobacz, jak działa aplikacja →', '/aplikacja/'),
    'trust' => [],
    'cards' => [[
        'label' => '', 'badge' => '', 'featured' => 1, 'check_icon' => 1,
        'title' => 'Spotkanie online z księgową',
        'meta' => '30 min · wideorozmowa · po polsku',
        'items' => [
            ['text' => 'Bezpłatna, niezobowiązująca rozmowa'],
            ['text' => 'Link do spotkania dostaniesz e-mailem'],
        ],
        'link' => $pusty, 'note' => '',
    ]],
    'stats' => [], 'video' => '',
]);

$kroki = taxeon_seed_block('timeline', [
    'eyebrow' => $t('Co dalej', 'span'),
    'title' => $t('Trzy kroki do spotkania', 'h2'),
    'text' => $t(''),
    'items' => [
        ['number' => '1', 'title' => 'Sprawdź e-mail', 'featured' => 0, 'text' => 'Wysłaliśmy potwierdzenie rezerwacji z linkiem do spotkania online.'],
        ['number' => '2', 'title' => 'Przygotuj pytania', 'featured' => 0, 'text' => 'Zapisz, czego chcesz się dowiedzieć o księgowości, cenach i aplikacji. Odpowiemy na wszystko.'],
        ['number' => '3', 'title' => 'Dołącz do spotkania', 'featured' => 0, 'text' => 'W umówionym terminie kliknij link z wiadomości. Rozmowa trwa około 30 minut.'],
    ],
    'cta_title' => '', 'cta_text' => '', 'cta_link' => $pusty,
    'background' => 'gradient',
]);

$cta = taxeon_seed_block('cta', [
    'title_size' => 'sm',
    'title' => $t('Nie możesz się doczekać?', 'h2'),
    'text' => 'Sprawdź, ile kosztuje księgowość w Taxeon i jak wygląda praca w aplikacji.',
    'button_primary' => $l('Zobacz cennik', '/cennik/'),
    'button_secondary' => $l('Czytaj bloga', '/blog/'),
    'background' => 'soft', 'padding_top' => 'small-lg', 'padding_bottom' => 'small-lg',
]);

$istnieje = get_page_by_path('dziekujemy');
$id = wp_insert_post(wp_slash([
    'ID' => $istnieje ? $istnieje->ID : 0,
    'post_type' => 'page', 'post_status' => 'publish',
    'post_title' => 'Dziękujemy', 'post_name' => 'dziekujemy',
    'post_content' => implode("\n\n", [$hero, $kroki, $cta]),
]));

update_post_meta($id, 'rank_math_robots', ['noindex', 'nofollow']);
update_post_meta($id, '_taxeon_lead_page', '1');

WP_CLI::success("Strona Dziękujemy #{$id} (noindex, zdarzenie generate_lead)");
