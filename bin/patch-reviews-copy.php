<?php
require get_template_directory() . '/bin/seed-helpers.php';

$id = (int) get_option('page_on_front');
$bloki = parse_blocks(get_post_field('post_content', $id));

foreach ($bloki as $i => $blok) {
    if (($blok['blockName'] ?? '') !== 'acf/reviews') {
        continue;
    }

    $bloki[$i]['attrs']['data'] = array_merge($blok['attrs']['data'] ?? [], taxeon_seed_data([
        'source' => 'google',
        'limit' => 4,
        'text' => [
            'text' => 'Średnia {ocena}/5 w Google z {liczba} opinii. Poniżej kilka z nich — pełną listę znajdziesz na naszej wizytówce.',
            'tag' => 'p',
        ],
    ], taxeon_seed_groups()['acf/reviews'] ?? []));

    WP_CLI::log('  zaktualizowano blok opinii');
}

wp_update_post(wp_slash(['ID' => $id, 'post_content' => serialize_blocks($bloki)]));
WP_CLI::success('gotowe');
