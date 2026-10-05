<?php
require get_template_directory() . '/bin/seed-helpers.php';

$ustawienia = [
    'acf/features' => ['head_size' => 'lg', 'head_width' => '62ch'],
    'acf/timeline' => ['head_size' => 'lg', 'title_width' => '24ch'],
    'acf/reviews' => ['head_size' => 'md', 'head_width' => '56ch'],
    'acf/latest-posts' => ['head_size' => 'md', 'head_width' => '56ch'],
    'acf/faq' => ['head_size' => 'sm'],
];

$id = (int) get_option('page_on_front');
$bloki = parse_blocks(get_post_field('post_content', $id));
$zmienione = 0;

foreach ($bloki as $i => $blok) {
    $nazwa = $blok['blockName'] ?? '';

    if (!isset($ustawienia[$nazwa])) {
        continue;
    }

    $slug = substr($nazwa, 4);
    $defs = taxeon_seed_groups()[$nazwa] ?? [];
    $bloki[$i]['attrs']['data'] = array_merge(
        $blok['attrs']['data'] ?? [],
        taxeon_seed_data($ustawienia[$nazwa], $defs)
    );
    $zmienione++;
    WP_CLI::log("  {$slug}: " . implode(', ', array_map(fn($k, $v) => "{$k}={$v}", array_keys($ustawienia[$nazwa]), $ustawienia[$nazwa])));
}

wp_update_post(wp_slash(['ID' => $id, 'post_content' => serialize_blocks($bloki)]));
WP_CLI::success("Zaktualizowano bloków: {$zmienione}");
