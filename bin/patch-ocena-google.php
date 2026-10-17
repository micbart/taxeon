<?php
require get_template_directory() . '/bin/seed-helpers.php';

$podmiany = [
    '4,9 / 5' => '{ocena} / 5',
    '4,9/5' => '{ocena}/5',
    'ponad 201 opinii' => 'ponad {liczba} opinii',
    '201+ opinii' => '{liczba} opinii',
];

global $wpdb;
$zmienione = 0;

foreach ($wpdb->get_results("SELECT ID, post_content FROM {$wpdb->posts} WHERE post_status = 'publish'") as $post) {
    $tresc = str_replace(array_keys($podmiany), array_values($podmiany), $post->post_content);

    if ($tresc === $post->post_content) {
        continue;
    }

    wp_update_post(wp_slash(['ID' => $post->ID, 'post_content' => $tresc]));
    $zmienione++;
}

WP_CLI::success("Ocena podstawiana z Google na {$zmienione} stronach.");
