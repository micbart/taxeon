<?php

require __DIR__ . '/patch-helpers.php';

$znaczniki = [
    '99' => '{cena_ryczalt}',
    '149' => '{cena_ryczalt_vat}',
    '169' => '{cena_kpir}',
    '19' => '{cena_aplikacja_start}',
    '43' => '{cena_aplikacja}',
    '12' => '{cena_biura}',
    '100' => '{cena_kadry}',
    '89' => '{cena_pit}',
];

$wzor = '/(?<![\d,.])(?<!\d[ \x{00A0}\x{202F}])(' . implode('|', array_keys($znaczniki)) . ')(?=[ \x{00A0}\x{202F}]?zł)/u';
$zamien = fn($v) => is_string($v) ? preg_replace_callback($wzor, fn($m) => $znaczniki[$m[1]], $v) : $v;
$glebiej = function ($v) use (&$glebiej, $zamien) {
    return is_array($v) ? array_map($glebiej, $v) : $zamien($v);
};

$strony = 0;

foreach (get_posts(['post_type' => 'page', 'post_status' => 'any', 'numberposts' => -1]) as $post) {
    $bloki = parse_blocks($post->post_content);

    foreach ($bloki as &$blok) {
        if (isset($blok['attrs']['data'])) {
            $blok['attrs']['data'] = $glebiej($blok['attrs']['data']);
        }
    }

    unset($blok);

    if (nc_save($post, $bloki)) {
        $strony++;
    }

    foreach (['rank_math_title', 'rank_math_description'] as $klucz) {
        $meta = get_post_meta($post->ID, $klucz, true);

        if (is_string($meta) && $meta !== '' && $zamien($meta) !== $meta) {
            update_post_meta($post->ID, $klucz, wp_slash($zamien($meta)));
        }
    }
}

$menu = 0;

foreach (get_posts(['post_type' => 'nav_menu_item', 'numberposts' => -1, 'post_status' => 'any']) as $pozycja) {
    $tytul = $zamien($pozycja->post_title);

    if ($tytul !== $pozycja->post_title) {
        wp_update_post(wp_slash(['ID' => $pozycja->ID, 'post_title' => $tytul]));
        $menu++;
    }
}

global $wpdb;
$opcje = 0;

foreach ($wpdb->get_col("SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE 'options\\_%' AND option_name NOT LIKE 'options\\_cena\\_%' AND option_name NOT LIKE 'options\\_kalk\\_%'") as $nazwa) {
    $wartosc = get_option($nazwa);
    $nowa = $glebiej($wartosc);

    if ($nowa !== $wartosc) {
        update_option($nazwa, $nowa);
        $opcje++;
    }
}

$cennik = get_page_by_path('cennik');

if ($cennik) {
    update_post_meta($cennik->ID, 'rank_math_title', 'Cennik księgowości online — {cena_aplikacja_start}, {cena_ryczalt}, {cena_ryczalt_vat} i {cena_kpir} zł %sep% %sitename%');
}

delete_transient('taxeon_cache_ver_options');
WP_CLI::success("Znaczniki cen: strony {$strony}, pozycje menu {$menu}, opcje {$opcje}");
