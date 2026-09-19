<?php

if (!defined('WP_CLI') || !WP_CLI) {
    exit("Uruchom przez: wp eval-file wp-content/themes/raks/bin/seed-pages-sklep.php\n");
}

require_once __DIR__ . '/seed-helpers.php';

$t = static fn(string $text, string $tag = ''): array => array_filter(['text' => $text, 'tag' => $tag]);

$page = get_page_by_path('szkolenia');

if (!$page) {
    WP_CLI::error('Brak strony „szkolenia” — uruchom najpierw seed-front-page.php.');
}

$blocks = [
    raks_seed_block('hero', [
        'layout' => 'left',
        'show_breadcrumbs' => true,
        ...raks_seed_hero_video(),
        'text_width' => 'full',
        'badge_highlight' => 'Bezpłatne',
        'badge_text' => 'webinary online',
        'title' => $t('Szkolenia i webinary RAKS', 'h1'),
        'subtitle' => $t('Ucz się od praktyków, którzy znają program od podszewki.', 'p'),
        'padding_top' => 'none',
        'padding_bottom' => 'none',
    ]),

    raks_seed_block('products', [
        'eyebrow' => $t('Oferta', 'span'),
        'title' => $t('Wybierz szkolenie', 'h2'),
        'text' => $t('Zapisy prowadzimy w sklepie RAKS — klikasz i finalizujesz zakup tam.', 'p'),
        'source' => 'all',
        'columns' => '2',
        'show_filter' => true,
        'show_price' => true,
        'show_date' => true,
        'button_label' => 'Zapisz się',
    ]),
];

wp_update_post(['ID' => $page->ID, 'post_content' => wp_slash(implode("\n\n", $blocks))]);
WP_CLI::log('  Szkolenia: ' . count($blocks) . ' bloków');

\App\Services\ThemeCache::flush('menus');

WP_CLI::success('Strona ze sklepu wypełniona.');
