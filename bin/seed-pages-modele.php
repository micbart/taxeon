<?php

if (!defined('WP_CLI') || !WP_CLI) {
    exit("Uruchom przez: wp eval-file wp-content/themes/raks/bin/seed-pages-modele.php\n");
}

require_once __DIR__ . '/seed-helpers.php';

$data = require __DIR__ . '/pages-modele-dane.php';

$slugs = ['chmura' => 'raks-w-chmurze', 'lokalnie' => 'raks-lokalnie'];

$hrefMap = [
    'index.html' => '', 'ksiegowy.html' => 'program-ksiegowy',
    'kadrowy.html' => 'program-kadrowo-placowy', 'sprzedaz.html' => 'sprzedaz-i-magazyn',
    'chmura.html' => 'raks-w-chmurze', 'lokalnie.html' => 'raks-lokalnie',
    'wyprobuj.html' => 'wyprobuj', 'prezentacja.html' => 'prezentacja',
    'kontakt.html' => 'kontakt', 'o-nas.html' => 'o-nas',
    'aktualne-wersje.html' => 'aktualne-wersje', 'szkolenia.html' => 'szkolenia',
];

$url = static function (string $href) use ($hrefMap): string {
    if (str_starts_with($href, '#')) {
        return $href;
    }

    $slug = $hrefMap[$href] ?? null;

    if ($slug === null) {
        return home_url('/');
    }

    if ($slug === '') {
        return home_url('/');
    }

    $p = get_page_by_path($slug);

    return $p ? (string) get_permalink($p) : home_url('/');
};

$t = static fn(string $text, string $tag = ''): array => array_filter(['text' => $text, 'tag' => $tag]);
$mkLink = static fn(array $pair): array => ['title' => $pair[0], 'url' => $url($pair[1]), 'target' => ''];
$btn = static fn(array $l, string $variant = '', string $size = '', string $icon = ''): array
    => array_filter(['link' => $l, 'variant' => $variant, 'size' => $size, 'icon' => $icon]);

$head = static fn(array $s) => [
    'eyebrow' => $t($s['eyebrow'], 'span'),
    'title' => $t($s['title'], 'h2'),
    'text' => $t($s['lead'], 'p'),
];

$sharedBlocks = [];
$front = get_post((int) get_option('page_on_front'));

if ($front) {
    foreach (parse_blocks($front->post_content) as $b) {
        if (in_array($b['blockName'], ['acf/support', 'acf/advisor'], true)) {
            $sharedBlocks[$b['blockName']] = $b;
        }
    }
}

/** @return array<int, string> */
$shared = static function (array $page) use ($sharedBlocks): array {
    $out = [];

    foreach ($sharedBlocks as $name => $block) {
        if ($name === 'acf/advisor') {
            foreach ($page['sections'] ?? [] as $section) {
                if (($section['kind'] ?? '') !== 'skip' || !str_contains(mb_strtolower($section['eyebrow'] ?? ''), 'doradca')) {
                    continue;
                }

                foreach (['eyebrow' => 'eyebrow', 'title' => 'title', 'lead' => 'text'] as $skad => $dokad) {
                    if (($section[$skad] ?? '') !== '') {
                        $block['attrs']['data'][$dokad . '_text'] = $section[$skad];
                    }
                }

                break;
            }
        }

        $out[] = trim(serialize_blocks([$block]));
    }

    return $out;
};

$done = 0;

foreach ($data as $key => $page) {
    $target = get_page_by_path($slugs[$key]);

    if (!$target) {
        WP_CLI::warning("Brak strony „{$slugs[$key]}” — pomijam.");
        continue;
    }

    $blocks = [raks_seed_block('hero', [
        'layout' => 'left',
        'show_breadcrumbs' => true,
        ...raks_seed_hero_video(),
        'text_width' => 'full',
        'badge_highlight' => $page['badge'][0],
        'badge_text' => $page['badge'][1],
        'title' => $t($page['title'], 'h1'),
        'subtitle' => $t($page['subtitle'], 'p'),
        'button_primary' => $btn($mkLink($page['cta'][0]), 'on-navy', 'lg'),
        'button_secondary' => $btn($mkLink($page['cta'][1]), 'ghost-navy', 'lg'),
        'trust' => array_map(static fn(string $x): array => ['text' => $x], $page['trust']),
        'padding_top' => 'none',
        'padding_bottom' => 'none',
    ])];

    $index = 0;

    foreach ($page['sections'] as $s) {
        $bg = $index % 2 === 0 ? 'page' : 'soft';
        $index++;

        if ($s['kind'] === 'pricing') {
            $blocks[] = raks_seed_block('pricing', $head($s) + [
                'columns' => (string) max(2, count($s['cards'])),
                'background' => $bg,
                'cards' => array_map(static fn(array $c): array => [
                    'featured' => (bool) $c['featured'],
                    'flag' => $c['flag'],
                    'icon' => $c['icon'],
                    'tag' => $c['tag'],
                    'title' => $t($c['title'], 'h3'),
                    'text' => $t($c['desc'], 'p'),
                    'price' => $c['price'],
                    'price_suffix' => $c['suffix'],
                    'note' => $c['note'],
                    'items' => array_map(static fn(string $x): array => ['text' => $x], $c['items']),
                    'button' => $btn($mkLink($c['btn']), $c['featured'] ? 'on-navy' : 'accent', 'lg'),
                ], $s['cards']),
            ]);
        } elseif ($s['kind'] === 'features') {
            $blocks[] = raks_seed_block('features', $head($s) + [
                'layout' => 'list',
                'columns' => '2',
                'background' => $bg,
                'items' => array_map(static fn(array $r): array => [
                    'icon' => $r[0],
                    'title' => $t($r[1], 'h3'),
                    'text' => $t($r[2], 'p'),
                ], $s['items']),
            ]);
        } elseif ($s['kind'] === 'choice') {
            $blocks[] = raks_seed_block('choice', $head($s) + [
                'columns' => (string) count($s['cards']),
                'background' => $bg,
                'cards' => array_map(static fn(array $c): array => [
                    'featured' => false,
                    'icon' => $c['icon'],
                    'title' => $t($c['title'], 'h3'),
                    'items' => array_map(static fn(string $x): array => ['text' => $x], $c['items']),
                ], $s['cards']),
            ]);
        } elseif ($s['kind'] === 'table') {
            $blocks[] = raks_seed_block('table', $head($s) + [
                'background' => $bg,
                'head' => array_map(static fn(array $h): array => [
                    'label' => $h[0],
                    'hide_mobile' => (bool) $h[1],
                ], $s['head']),
                'rows' => array_map(static fn(array $cells): array => [
                    'cells' => array_map(static fn(array $c): array => array_filter([
                        'text' => $c['text'],
                        'suffix' => $c['suffix'],
                        'icon' => $c['icon'],
                        'style' => $c['style'],
                        'link' => $c['link'] !== '' ? ['title' => 'Szczegóły', 'url' => $url($c['link']), 'target' => ''] : null,
                    ]), $cells),
                ], $s['rows']),
            ]);
        }
    }

    $blocks = array_merge($blocks, $shared($page));

    wp_update_post(['ID' => $target->ID, 'post_content' => wp_slash(implode("\n\n", $blocks))]);
    WP_CLI::log("  {$target->post_title}: " . count($blocks) . ' bloków');
    $done++;
}

WP_CLI::success("Strony modeli wypełnione ({$done}).");
