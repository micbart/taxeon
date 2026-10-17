<?php
require get_template_directory() . '/bin/seed-helpers.php';

$mapa = get_option('taxeon_mapa_wideo', []);
$telefon = '+48 515 033 505';
$telefonLink = 'tel:+48515033505';

global $wpdb;
$zmienione = 0;

foreach ($wpdb->get_results("SELECT ID, post_content FROM {$wpdb->posts} WHERE post_status = 'publish'") as $post) {
    $tresc = $post->post_content;
    $przed = $tresc;

    foreach ($mapa as $plik => $adres) {
        foreach ([home_url('/projekt/assets/' . $plik), str_replace('/', '\\/', home_url('/projekt/assets/' . $plik))] as $stary) {
            $tresc = str_replace($stary, str_contains($stary, '\\/') ? str_replace('/', '\\/', $adres) : $adres, $tresc);
        }
    }

    $tresc = str_replace(
        ['Zadzwoń: +48 XXX XXX XXX', '+48 XXX XXX XXX', 'tel:+48000000000', 'tel:+48 000 000 000'],
        ['Zadzwoń: ' . $telefon, $telefon, $telefonLink, $telefonLink],
        $tresc
    );

    if ($tresc === $przed) {
        continue;
    }

    wp_update_post(wp_slash(['ID' => $post->ID, 'post_content' => $tresc]));
    $zmienione++;
}

WP_CLI::success("Zaktualizowano stron: {$zmienione}");
