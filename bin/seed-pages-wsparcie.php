<?php

if (!defined('WP_CLI') || !WP_CLI) {
    exit("Uruchom przez: wp eval-file wp-content/themes/raks/bin/seed-pages-wsparcie.php\n");
}

require_once __DIR__ . '/seed-helpers.php';

$data = require __DIR__ . '/pages-wsparcie-dane.php';

$hrefMap = [
    'index.html' => '', 'wyprobuj.html' => 'wyprobuj', 'prezentacja.html' => 'prezentacja',
    'kontakt.html' => 'kontakt', 'o-nas.html' => 'o-nas', 'szkolenia.html' => 'szkolenia',
    'aktualne-wersje.html' => 'aktualne-wersje',
];

$url = static function (string $href) use ($hrefMap): string {
    if ($href === '' || str_starts_with($href, 'http') || str_starts_with($href, '#')
        || str_starts_with($href, 'tel:') || str_starts_with($href, 'mailto:')) {
        return $href;
    }

    $slug = $hrefMap[$href] ?? '';

    if ($slug === '') {
        return home_url('/');
    }

    $p = get_page_by_path($slug);

    return $p ? (string) get_permalink($p) : home_url('/');
};

$t = static fn(string $text, string $tag = ''): array => array_filter(['text' => $text, 'tag' => $tag]);
$mk = static fn(array $pair): array => ['title' => $pair[0], 'url' => $url($pair[1]), 'target' => str_starts_with($pair[1], 'http') ? '_blank' : ''];
$btn = static fn(array $l, string $v = '', string $s = '', string $i = ''): array
    => array_filter(['link' => $l, 'variant' => $v, 'size' => $s, 'icon' => $i]);

$heroBlock = static function (array $h) use ($t, $btn, $mk): string {
    $fields = [
        'layout' => 'left',
        'show_breadcrumbs' => true,
        ...raks_seed_hero_video(),
        'text_width' => 'full',
        'badge_highlight' => $h['badge'][0],
        'badge_text' => $h['badge'][1],
        'title' => $t($h['title'], 'h1'),
        'subtitle' => $t($h['subtitle'], 'p'),
        'trust' => array_map(static fn(string $x): array => ['text' => $x], $h['trust']),
        'padding_top' => 'none',
        'padding_bottom' => 'none',
    ];

    if (isset($h['cta'][0])) {
        $fields['button_primary'] = $btn($mk($h['cta'][0]), 'on-navy', 'lg');
    }

    if (isset($h['cta'][1])) {
        $fields['button_secondary'] = $btn($mk($h['cta'][1]), 'ghost-navy', 'lg');
    }

    return raks_seed_block('hero', $fields);
};

$p = $data['prezentacja'];
$page = get_page_by_path('prezentacja');

if ($page) {
    $blocks = [
        $heroBlock($p['hero']),

        raks_seed_block('content-media', [
            'title' => $t($p['section']['title'], 'h2'),
            'text' => $t($p['section']['lead'], 'p'),
            'items' => array_map(
                static fn(array $r): array => [
                    'icon' => $r[0],
                    'title' => $t($r[1], 'h3'),
                    'text' => $r[2],
                ],
                $p['section']['points']
            ),
            'media_position' => 'right',
            'media_type' => 'embed',
            'embed_url' => $p['section']['embed'],
            'embed_height' => 720,
            'container' => 'wide',
            'background' => 'page',
        ]),
    ];

    wp_update_post(['ID' => $page->ID, 'post_content' => wp_slash(implode("\n\n", $blocks))]);
    WP_CLI::log('  Umów prezentację: ' . count($blocks) . ' bloków');
}

$w = $data['wersje'];
$page = get_page_by_path('aktualne-wersje');

if ($page) {
    $blocks = [
        $heroBlock($w['hero']),

        raks_seed_block('pricing', [
            'eyebrow' => $t($w['featured']['eyebrow'], 'span'),
            'title' => $t($w['featured']['title'], 'h2'),
            'text' => $t($w['featured']['lead'], 'p'),
            'columns' => '2',
            'background' => 'page',
            'cards' => array_map(static function (array $c) use ($t, $btn, $mk): array {
                $card = [
                    'featured' => $c['featured'],
                    'icon' => $c['icon'],
                    'tag' => $c['version'],
                    'title' => $t($c['title'], 'h3'),
                    'text' => $t($c['desc'], 'p'),
                    'links' => array_map(
                        static fn(array $l): array => ['icon' => $l[0], 'link' => $mk([$l[1], $l[2]])],
                        $c['links']
                    ),
                ];

                if (isset($c['buttons'][0])) {
                    $card['button'] = $btn($mk($c['buttons'][0]), $c['featured'] ? 'on-navy' : 'accent', 'lg', 'download');
                }

                if (isset($c['buttons'][1])) {
                    $card['button_secondary'] = $btn($mk($c['buttons'][1]), $c['featured'] ? 'ghost-navy' : 'ghost', 'lg');
                }

                return $card;
            }, $w['featured']['cards']),
        ]),

        raks_seed_block('table', [
            'eyebrow' => $t($w['table']['eyebrow'], 'span'),
            'title' => $t($w['table']['title'], 'h2'),
            'text' => $t($w['table']['lead'], 'p'),
            'mode' => 'tabs',
            'background' => 'soft',
            'head' => array_map(
                static fn(array $h): array => ['label' => $h[0], 'hide_mobile' => (bool) $h[1]],
                $w['table']['tabs'][0]['head']
            ),
            'tabs' => array_map(static fn(array $tab): array => [
                'label' => $tab['label'],
                'rows' => array_map(static fn(array $cells): array => [
                    'cells' => array_map(static fn(array $c): array => array_filter([
                        'text' => $c['link'] !== '' ? '' : $c['text'],
                        'icon' => $c['icon'],
                        'style' => $c['style'],
                        'link' => $c['link'] !== '' ? $mk([$c['linkLabel'], $c['link']]) : null,
                    ]), $cells),
                ], $tab['rows']),
            ], $w['table']['tabs']),
        ]),
    ];

    wp_update_post(['ID' => $page->ID, 'post_content' => wp_slash(implode("\n\n", $blocks))]);
    WP_CLI::log('  Aktualne wersje: ' . count($blocks) . ' bloków');
}

WP_CLI::success('Strony wsparcia wypełnione.');
