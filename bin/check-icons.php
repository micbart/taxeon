<?php

if (!defined('WP_CLI') || !WP_CLI) {
    exit("Uruchom przez: wp eval-file wp-content/themes/taxeon/bin/check-icons.php\n");
}

$dir = get_template_directory() . '/src/svg/';
$missing = [];
$checked = 0;

$posts = get_posts([
    'post_type' => ['page', 'post'],
    'post_status' => 'any',
    'posts_per_page' => -1,
]);

foreach ($posts as $post) {
    foreach (parse_blocks($post->post_content) as $block) {
        foreach ((array) ($block['attrs']['data'] ?? []) as $key => $value) {
            if (!is_string($value) || $value === '') {
                continue;
            }

            if (str_starts_with((string) $key, '_') || !preg_match('/(^|_)icon$/', (string) $key)) {
                continue;
            }

            $checked++;

            if (!file_exists($dir . $value . '.svg')) {
                $missing[$value][] = $post->post_title;
            }
        }
    }
}

if (!$missing) {
    WP_CLI::success("Wszystkie ikony w treści istnieją (sprawdzono: {$checked}).");
    return;
}

foreach ($missing as $icon => $where) {
    WP_CLI::warning("Brak src/svg/{$icon}.svg — użyta w: " . implode(', ', array_unique($where)));
}

WP_CLI::error('Brakujących ikon: ' . count($missing));
