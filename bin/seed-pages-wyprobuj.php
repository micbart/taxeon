<?php

if (!defined('WP_CLI') || !WP_CLI) {
    exit("Uruchom przez: wp eval-file wp-content/themes/raks/bin/seed-pages-wyprobuj.php\n");
}

require_once __DIR__ . '/seed-helpers.php';

$data = require __DIR__ . '/pages-wyprobuj-dane.php';

$hrefMap = [
    'index.html' => '', 'prezentacja.html' => 'prezentacja', 'kontakt.html' => 'kontakt',
    'aktualne-wersje.html' => 'aktualne-wersje', 'o-nas.html' => 'o-nas',
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

    $page = get_page_by_path($slug);

    return $page ? (string) get_permalink($page) : home_url('/');
};

$t = static fn(string $text, string $tag = ''): array => array_filter(['text' => $text, 'tag' => $tag]);
$mk = static fn(string $label, string $href) => ['title' => $label, 'url' => $url($href), 'target' => str_starts_with($href, 'http') ? '_blank' : ''];
$btn = static fn(array $l, string $v = '', string $s = '', string $i = ''): array
    => array_filter(['link' => $l, 'variant' => $v, 'size' => $s, 'icon' => $i]);

$heroBlock = static function (array $h) use ($t): string {
    return raks_seed_block('hero', [
        'layout' => 'left',
        'show_breadcrumbs' => true,
        ...raks_seed_hero_video(),
        'text_width' => 'full',
        'badge_highlight' => $h['badge'][0],
        'badge_text' => ltrim($h['badge'][1], '· '),
        'title' => $t($h['title'], 'h1'),
        'subtitle' => $t($h['sub'], 'p'),
        'trust' => array_map(static fn(string $x): array => ['text' => $x], $h['trust'] ?? []),
        'padding_top' => 'none',
        'padding_bottom' => 'none',
    ]);
};

$w = $data['wizard'];
$page = get_page_by_path('wyprobuj');

if (!$page) {
    WP_CLI::error('Brak strony „wyprobuj" — uruchom najpierw seed-front-page.php.');
}

$blocks = [
    $heroBlock($w['hero']),

    raks_seed_block('trial-wizard', [
        'show_rail' => true,
        'rail_position' => 'left',
        'eyebrow' => $t($w['rail']['eyebrow'], 'span'),
        'title' => $t($w['rail']['title'], 'h2'),
        'text' => $t($w['rail']['text'], 'p'),
        'stats' => $w['stats'],
        'points' => array_map(static fn(string $x): array => ['text' => $x], $w['points']),
        'alt_icon' => $w['alt']['icon'],
        'alt_title' => $w['alt']['title'],
        'alt_text' => $w['alt']['text'],
        'alt_link' => $mk($w['alt']['link'][1], $w['alt']['link'][0]),
        'secure_icon' => $w['secure'][0],
        'secure_text' => $w['secure'][1],

        'step1_label' => $w['s1']['label'],
        'step1_kicker' => $w['s1']['kicker'],
        'step1_title' => $t($w['s1']['title'], 'h3'),
        'step1_text' => str_replace('rejestru GUS', 'rejestru VAT Ministerstwa Finansów', $w['s1']['text']),
        'step1_nip_label' => $w['s1']['nip_label'],
        'step1_check_label' => $w['s1']['check'],
        'step1_hint' => $w['s1']['hint'],
        'step1_company_label' => $w['s1']['company_label'],
        'step1_next_label' => $w['s1']['next'],

        'step2_label' => $w['s2']['label'],
        'step2_kicker' => $w['s2']['kicker'],
        'step2_title' => $t($w['s2']['title'], 'h3'),
        'step2_text' => $w['s2']['text'],
        'step2_consent' => $w['s2']['consent'],
        'step2_consent_more_label' => $w['s2']['more_label'],
        'step2_consent_more' => implode("\n\n", array_map(static fn(string $p): string => '<p>' . $p . '</p>', $w['s2']['more'])),
        'step2_back_label' => $w['s2']['back'],
        'step2_submit_label' => $w['s2']['submit'],

        'step3_label' => $w['s3']['label'],
        'step3_title' => $t($w['s3']['title'], 'h3'),
        'step3_text' => $w['s3']['text'],
        'downloads' => [[
            'button' => $btn($mk($w['s3']['download'][1], $w['s3']['download'][0]), 'primary', 'lg', 'download'),
            'note' => $w['s3']['download_note'],
        ]],
        'next_steps_title' => $w['s3']['next_title'],
        'next_steps' => array_map(
            static fn(array $s): array => ['title' => $s[0], 'text' => $s[1]],
            $w['s3']['next']
        ),
        'foot' => array_map(
            static fn(array $f): array => ['icon' => $f[0], 'text' => $f[1]],
            $w['foot']
        ),
        'form_name' => 'demo',
        'background' => 'soft',
        'container' => 'page',
    ]),
];

wp_update_post(['ID' => $page->ID, 'post_content' => wp_slash(implode("\n\n", $blocks))]);
WP_CLI::log('  Pobierz wersję demo: ' . count($blocks) . ' bloków');

$v = $data['v1'];
$page = get_page_by_path('wyprobuj-wariant');

if (!$page) {
    $id = wp_insert_post([
        'post_type' => 'page',
        'post_title' => 'Wypróbuj za darmo (wariant)',
        'post_name' => 'wyprobuj-wariant',
        'post_status' => 'publish',
    ]);

    $page = get_post($id);
    WP_CLI::log('  utworzono stronę: wyprobuj-wariant');
}

$blocks = [
    $heroBlock($v['hero']),

    raks_seed_block('features', [
        'eyebrow' => $t($v['features']['eyebrow'], 'span'),
        'title' => $t($v['features']['title'], 'h2'),
        'text' => $t($v['features']['lead'], 'p'),
        'layout' => 'list',
        'columns' => '2',
        'items' => array_map(static fn(array $i): array => [
            'icon' => $i[0],
            'title' => $t($i[1], 'h3'),
            'text' => $t($i[2], 'p'),
        ], $v['features']['items']),
    ]),

    raks_seed_block('contact', [
        'layout' => 'card',
        'hide_message' => true,
        'title' => $t($v['form']['title'], 'h2'),
        'text' => $t($v['form']['text'], 'p'),
        'subjects' => array_map(static fn(string $m): array => ['label' => $m], $v['form']['modules']),
        'consent_optional' => true,
        'consent_text' => $v['form']['consent'],
        'subject_label' => 'Interesujący moduł',
        'submit_label' => $v['form']['submit'],
        'form_name' => 'trial',
        'background' => 'soft',
    ]),

    raks_seed_block('faq', [
        'eyebrow' => $t('FAQ', 'span'),
        'title' => $t($v['faq']['title'], 'h2'),
        'align' => 'center',
        'columns' => '1',
        'single' => true,
        'icon' => 'plus',
        'items' => array_map(
            static fn(array $q): array => ['question' => $q[0], 'answer' => $q[1]],
            $v['faq']['items']
        ),
    ]),
];

wp_update_post(['ID' => $page->ID, 'post_content' => wp_slash(implode("\n\n", $blocks))]);
WP_CLI::log('  Wypróbuj za darmo (wariant): ' . count($blocks) . ' bloków');

\App\Services\ThemeCache::flush('menus');

WP_CLI::success('Strony „wypróbuj" wypełnione.');
