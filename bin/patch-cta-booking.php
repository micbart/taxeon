<?php
require get_template_directory() . '/bin/seed-helpers.php';

$adres = taxeon_booking();

if (!str_starts_with($adres, 'http')) {
    WP_CLI::error('Brak adresu rezerwacji w opcjach motywu.');
}

global $wpdb;
$zmienione = 0;

foreach ($wpdb->get_results("SELECT ID, post_content FROM {$wpdb->posts} WHERE post_status = 'publish'") as $post) {
    $stare = $post->post_content;
    $nowe = str_replace(
        [home_url('/#kontakt'), esc_url(home_url('/#kontakt')), str_replace('/', '\\/', home_url('/#kontakt'))],
        [$adres, esc_url($adres), str_replace('/', '\\/', $adres)],
        $stare
    );

    if ($nowe === $stare) {
        continue;
    }

    wp_update_post(wp_slash(['ID' => $post->ID, 'post_content' => $nowe]));
    $zmienione++;
}

WP_CLI::success("Przyciski kierują do kalendarza na {$zmienione} stronach.");
