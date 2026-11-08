<?php

require __DIR__ . '/patch-helpers.php';

$branze = get_page_by_path('branze');

if ($branze && in_array('odbuduj', $args ?? [], true)) {
    wp_delete_post($branze->ID, true);
    $branze = null;
}

if (!$branze) {
    [, $glowna] = nc_blocks('strona-glowna');
    $bloki = array_values(array_filter($glowna, fn($b) => $b['blockName']));

    require_once __DIR__ . '/seed-helpers.php';

    $hero = parse_blocks(taxeon_seed_block('hero', [
        'layout' => 'plain',
        'tone' => 'cream',
        'button_style' => 'light',
        'badge' => 'Księgowość branżowa · 14 lat na rynku',
        'title' => ['text' => 'Księgowość pisana pod Twój zawód.', 'tag' => 'h1'],
        'subtitle' => 'Lekarze, prawnicy, programiści i kontraktorzy B2B.',
        'text' => 'Każda branża rozlicza się inaczej — inne stawki ryczałtu, inne zwolnienia z VAT, inne pułapki. Wybierz swoją i zobacz, co dokładnie dla Ciebie robimy.',
        'button_primary' => ['title' => 'Poznaj zasady współpracy →', 'url' => home_url('/umow-spotkanie/'), 'target' => ''],
        'button_secondary' => ['title' => '', 'url' => '', 'target' => ''],
        'trust' => [], 'cards' => [], 'stats' => [], 'video' => '',
    ]))[0];

    $zawartosc = serialize_blocks([$hero, $bloki[1], $bloki[count($bloki) - 1]]);

    $id = wp_insert_post(wp_slash([
        'post_type' => 'page',
        'post_status' => 'publish',
        'post_title' => 'Branże',
        'post_name' => 'branze',
        'post_content' => $zawartosc,
    ]));

    WP_CLI::log("  utworzono stronę Branże (#{$id})");
    $branze = get_post($id);
}

foreach (['it', 'lekarze', 'prawnicy'] as $slug) {
    $strona = get_page_by_path($slug) ?: get_page_by_path('branze/' . $slug);

    if ($strona && (int) $strona->post_parent !== (int) $branze->ID) {
        wp_update_post(['ID' => $strona->ID, 'post_parent' => $branze->ID]);
        WP_CLI::log("  {$slug} → branze/{$slug}");
    }
}

$biura = get_page_by_path('biura-rachunkowe');

if ($biura) {
    wp_update_post(['ID' => $biura->ID, 'post_name' => 'dla-biur-rachunkowych']);
    WP_CLI::log('  biura-rachunkowe → dla-biur-rachunkowych');
}

WP_CLI::success('Adresy przeniesione');
