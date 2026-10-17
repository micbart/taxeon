<?php
require get_template_directory() . '/bin/seed-helpers.php';

$ustawienia = [
    'acf/features' => ['head_size' => 'lg', 'head_width' => '62ch'],
    'acf/timeline' => ['head_size' => 'lg', 'title_width' => '24ch'],
    'acf/reviews' => ['head_size' => 'md', 'head_width' => '56ch'],
    'acf/latest-posts' => ['head_size' => 'md', 'head_width' => '56ch', 'text_gap' => '16'],
    'acf/faq' => ['head_size' => 'md', 'text_gap' => '16'],
];

$kotwice = [
    'acf/hero' => 'top',
    'acf/features' => 'branze',
    'acf/timeline' => 'wspolpraca',
    'acf/latest-posts' => 'wiedza',
    'acf/faq' => 'faq',
    'acf/cta' => 'kontakt',
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

foreach ($bloki as $i => $blok) {
    $nazwa = $blok['blockName'] ?? '';

    if (isset($kotwice[$nazwa]) && empty($blok['attrs']['anchor'])) {
        $bloki[$i]['attrs']['anchor'] = $kotwice[$nazwa];
        WP_CLI::log("  kotwica #{$kotwice[$nazwa]} dla {$nazwa}");
    }
}

foreach ($bloki as $i => $blok) {
    if (($blok['blockName'] ?? '') !== 'acf/features') {
        continue;
    }

    $dane = $blok['attrs']['data'] ?? [];
    $klucz = taxeon_seed_def(
        taxeon_seed_def(taxeon_seed_groups()['acf/features'], 'items')['sub_fields'],
        'highlight'
    )['key'];

    foreach ($dane as $pole => $wartosc) {
        if (!preg_match('/^items_(\\d+)_title$/', (string) $pole, $m) || $wartosc !== 'Kontrakt B2B') {
            continue;
        }

        $bloki[$i]['attrs']['data']["items_{$m[1]}_highlight"] = 1;
        $bloki[$i]['attrs']['data']["_items_{$m[1]}_highlight"] = $klucz;
        WP_CLI::log("  karta „Kontrakt B2B” oznaczona jako wyróżniona");
    }
}

wp_update_post(wp_slash(['ID' => $id, 'post_content' => serialize_blocks($bloki)]));
WP_CLI::success("Zaktualizowano bloków: {$zmienione}");
