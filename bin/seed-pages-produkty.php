<?php

if (!defined('WP_CLI') || !WP_CLI) {
    exit("Uruchom przez: wp eval-file wp-content/themes/raks/bin/seed-pages-produkty.php\n");
}

require_once __DIR__ . '/seed-helpers.php';

$data = require __DIR__ . '/pages-produkty-dane.php';

$urlOf = static function (string $slug): string {
    $p = get_page_by_path($slug);

    return $p ? (string) get_permalink($p) : home_url('/');
};

$link = static fn(string $slug, string $title): array => ['title' => $title, 'url' => $urlOf($slug), 'target' => ''];
$t = static fn(string $text, string $tag = ''): array => array_filter(['text' => $text, 'tag' => $tag]);
$btn = static fn(array $l, string $variant = '', string $size = '', string $icon = ''): array
    => array_filter(['link' => $l, 'variant' => $variant, 'size' => $size, 'icon' => $icon]);

$items = static function (array $rows) use ($t): array {
    return array_map(
        static fn(array $r): array => [
            'icon' => $r[0],
            'title' => $t($r[1], 'h3'),
            'text' => $t($r[2], 'p'),
        ],
        $rows
    );
};

$shared = [];
$front = get_post((int) get_option('page_on_front'));

if ($front) {
    foreach (parse_blocks($front->post_content) as $b) {
        if (in_array($b['blockName'], ['acf/choice', 'acf/support', 'acf/advisor'], true)) {
            $shared[] = trim(serialize_blocks([$b]));
        }
    }
}

$zespol = raks_seed_image('dlaczego/zespol.jpg', 'Zespół RAKS w biurze');
$done = 0;

foreach ($data as $key => $page) {
    $target = get_page_by_path($page['slug']);

    if (!$target) {
        WP_CLI::warning("Brak strony „{$page['slug']}” — pomijam.");
        continue;
    }

    $blocks = [];

    $blocks[] = raks_seed_block('hero', [
        'layout' => 'left',
        'show_breadcrumbs' => true,
        ...raks_seed_hero_video(),
        'text_width' => 'full',
        'badge_highlight' => $page['badge'][0],
        'badge_text' => $page['badge'][1],
        'title' => $t($page['title'], 'h1'),
        'subtitle' => $t($page['subtitle'], 'p'),
        'button_primary' => $btn($link('prezentacja', 'Umów prezentację'), 'on-navy', 'lg', 'play-circle'),
        'button_secondary' => $btn($link('wyprobuj', 'Wypróbuj za darmo'), 'ghost-navy', 'lg'),
        'trust' => array_map(static fn(string $x): array => ['text' => $x], $page['trust']),
        'padding_top' => 'none',
        'padding_bottom' => 'none',
    ]);

    $blocks[] = raks_seed_block('features', [
        'eyebrow' => $t($page['funkcje']['eyebrow'], 'span'),
        'title' => $t($page['funkcje']['title'], 'h2'),
        'text' => $t($page['funkcje']['lead'], 'p'),
        'layout' => 'list',
        'columns' => '2',
        'items' => $items($page['funkcje']['items']),
        'background' => 'page',
    ]);

    $blocks[] = raks_seed_block('features', [
        'eyebrow' => $t($page['dla_kogo']['eyebrow'], 'span'),
        'title' => $t($page['dla_kogo']['title'], 'h2'),
        'text' => $t($page['dla_kogo']['lead'], 'p'),
        'layout' => 'cards',
        'columns' => '3',
        'background_image' => $zespol,
        'items' => $items($page['dla_kogo']['items']),
        'background' => 'soft',
    ]);

    $blocks[] = raks_seed_block('features', [
        'eyebrow' => $t($page['porownanie']['eyebrow'], 'span'),
        'title' => $t($page['porownanie']['title'], 'h2'),
        'text' => $t($page['porownanie']['lead'], 'p'),
        'layout' => 'cards',
        'columns' => '4',
        'items' => $items($page['porownanie']['items']),
        'background' => 'soft',
    ]);

    $blocks = array_merge($blocks, $shared);

    wp_update_post([
        'ID' => $target->ID,
        'post_content' => wp_slash(implode("\n\n", $blocks)),
    ]);

    WP_CLI::log("  {$target->post_title}: " . count($blocks) . ' bloków');
    $done++;
}

WP_CLI::success("Strony produktowe wypełnione ({$done}).");
