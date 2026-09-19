<?php

if (!defined('WP_CLI') || !WP_CLI) {
    exit("Uruchom przez: wp eval-file wp-content/themes/raks/bin/seed-pages-firma.php\n");
}

require_once __DIR__ . '/seed-helpers.php';

$data = require __DIR__ . '/pages-firma-dane.php';

$hrefMap = [
    'index.html' => '', 'wyprobuj.html' => 'wyprobuj', 'prezentacja.html' => 'prezentacja',
    'kontakt.html' => 'kontakt', 'o-nas.html' => 'o-nas', 'szkolenia.html' => 'szkolenia',
];

$url = static function (string $href) use ($hrefMap): string {
    if (str_starts_with($href, 'tel:') || str_starts_with($href, 'mailto:') || str_starts_with($href, '#')) {
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
$mk = static fn(array $pair): array => ['title' => $pair[0], 'url' => $url($pair[1]), 'target' => ''];
$btn = static fn(array $l, string $v = '', string $s = '', string $i = ''): array
    => array_filter(['link' => $l, 'variant' => $v, 'size' => $s, 'icon' => $i]);

$o = $data['o_nas'];
$onas = get_page_by_path('o-nas');

if ($onas) {
    $blocks = [
        raks_seed_block('hero', [
            'layout' => 'left',
            'show_breadcrumbs' => true,
            ...raks_seed_hero_video(),
            'text_width' => 'full',
            'badge_highlight' => $o['hero']['badge'][0],
            'badge_text' => $o['hero']['badge'][1],
            'title' => $t($o['hero']['title'], 'h1'),
            'subtitle' => $t($o['hero']['subtitle'], 'p'),
            'trust' => array_map(static fn(string $x): array => ['text' => $x], $o['hero']['trust']),
            'padding_top' => 'none',
            'padding_bottom' => 'none',
        ]),

        raks_seed_block('content-media', [
            'eyebrow' => $t($o['split']['eyebrow'], 'span'),
            'title' => $t($o['split']['title'], 'h2'),
            'text' => $t($o['split']['text'], 'p'),
            'link' => $mk($o['split']['link']),
            'media_position' => 'right',
            'frame' => 'window',
            'frame_label' => $o['split']['frame_label'],
            'image' => raks_seed_image('dlaczego/zespol.jpg', 'Zespół RAKS w biurze'),
            'background' => 'page',
        ]),

        raks_seed_block('features', [
            'eyebrow' => $t($o['values']['eyebrow'], 'span'),
            'title' => $t($o['values']['title'], 'h2'),
            'layout' => 'list',
            'columns' => '2',
            'background' => 'soft',
            'items' => array_map(static fn(array $r): array => [
                'icon' => $r[0], 'title' => $t($r[1], 'h3'), 'text' => $t($r[2], 'p'),
            ], $o['values']['items']),
        ]),

        raks_seed_block('timeline', [
            'eyebrow' => $t($o['timeline']['eyebrow'], 'span'),
            'title' => $t($o['timeline']['title'], 'h2'),
            'background' => 'page',
            'items' => array_map(static fn(array $r): array => [
                'year' => $r[0], 'title' => $t($r[1], 'h3'), 'text' => $t($r[2], 'p'),
            ], $o['timeline']['items']),
        ]),

        raks_seed_block('stats', [
            'eyebrow' => $t($o['stats']['eyebrow'], 'span'),
            'title' => $t($o['stats']['title'], 'h2'),
            'columns' => (string) min(4, max(2, count($o['stats']['items']))),
            'background' => 'navy',
            'items' => array_map(static fn(array $r): array => ['value' => $r[0], 'label' => $r[1]], $o['stats']['items']),
        ]),

        raks_seed_block('cta', [
            'variant' => 'light',
            'icon' => 'award',
            'title' => $t($o['cta']['title'], 'h2'),
            'text' => $t($o['cta']['text'], 'p'),
            'button_primary' => $btn($mk($o['cta']['buttons'][0]), 'primary', 'lg'),
            'button_secondary' => isset($o['cta']['buttons'][1]) ? $btn($mk($o['cta']['buttons'][1]), 'ghost', 'lg') : [],
        ]),
    ];

    wp_update_post(['ID' => $onas->ID, 'post_content' => wp_slash(implode("\n\n", $blocks))]);
    WP_CLI::log('  O nas: ' . count($blocks) . ' bloków');
}

$k = $data['kontakt'];
$kontakt = get_page_by_path('kontakt');

if ($kontakt) {
    $card = $k['cards'][0] ?? ['title' => 'Bezpośredni kontakt', 'lines' => []];

    $blocks = [
        raks_seed_block('hero', [
            'layout' => 'left',
            'show_breadcrumbs' => true,
            ...raks_seed_hero_video(),
            'text_width' => 'full',
            'badge_highlight' => $k['hero']['badge'][0],
            'badge_text' => $k['hero']['badge'][1],
            'title' => $t($k['hero']['title'], 'h1'),
            'subtitle' => $t($k['hero']['subtitle'], 'p'),
            'padding_top' => 'none',
            'padding_bottom' => 'none',
        ]),

        raks_seed_block('contact', [
            'title' => $t($k['form']['title'], 'h2'),
            'text' => $t($k['form']['text'], 'p'),
            'form_name' => 'kontakt',
            'subjects' => array_map(static fn(string $x): array => ['label' => $x], $k['form']['subjects']),
            'submit_label' => $k['form']['submit'],
            'consent_text' => 'Wyrażam zgodę na kontakt w celu przedstawienia oferty RAKS.',
            'consent_rodo' => 'Zapoznałem się z <a href="' . esc_url(home_url('/polityka-prywatnosci/')) . '">'
                . 'informacją o przetwarzaniu danych osobowych (RODO)</a> i wyrażam zgodę na przetwarzanie '
                . 'moich danych przez RAKS Sp. z o.o.',
            'privacy_note' => 'Administratorem danych jest RAKS. Dane wykorzystujemy wyłącznie do odpowiedzi na zapytanie.',
            'info_cards' => [[
                'title' => $t($card['title'], 'h3'),
                'items' => [
                    ['icon' => 'phone', 'label' => 'Dział sprzedaży', 'text' => "sprzedaz@raks.pl\npon–pt 9:00–17:00", 'link' => ['title' => '885 885 000', 'url' => 'tel:885885000', 'target' => '']],
                    ['icon' => 'map-pin', 'label' => 'Adres biura', 'text' => "ul. Kamionkowska 51\n03-812 Warszawa"],
                    ['icon' => 'life-buoy', 'label' => 'Pomoc techniczna', 'text' => 'pon–pt 9:00–17:00'],
                    ['icon' => 'monitor-smartphone', 'label' => 'Pomoc zdalna', 'text' => 'Udostępnij pulpit konsultantowi i rozwiążcie problem razem.'],
                ],
            ]],
            'map_embed' => 'https://www.google.com/maps?q=ul.+Kamionkowska+51,+03-812+Warszawa&output=embed',
            'map_caption' => 'RAKS · ul. Kamionkowska 51, Warszawa',
            'background' => 'page',
        ]),
    ];

    $front = get_post((int) get_option('page_on_front'));

    if ($front) {
        foreach (parse_blocks($front->post_content) as $b) {
            if ($b['blockName'] !== 'acf/advisor') {
                continue;
            }

            $b['attrs']['data']['eyebrow_text'] = 'Umów się na prezentację';
            $b['attrs']['data']['title_text'] = 'Zobacz RAKS w akcji.';
            $b['attrs']['data']['text_text'] = 'Chcesz zobaczyć program na żywo? Umów się na bezpłatną, '
                . 'indywidualną prezentację online. Nasz konsultant pokaże Ci RAKS i odpowie na wszystkie pytania.';
            $b['attrs']['data']['benefits_0_text'] = 'Demo na żywo dopasowane do Twojej branży';
            $b['attrs']['data']['stats_0_icon'] = 'clock';
            $b['attrs']['data']['stats_0_value'] = '30 minut';
            $b['attrs']['data']['stats_0_label'] = 'tyle wystarczy, by poznać RAKS';

            $b['attrs']['data']['stats'] = 2;
            unset($b['attrs']['data']['stats_2_icon'], $b['attrs']['data']['stats_2_value'], $b['attrs']['data']['stats_2_label']);

            $blocks[] = trim(serialize_blocks([$b]));
        }
    }

    wp_update_post(['ID' => $kontakt->ID, 'post_content' => wp_slash(implode("\n\n", $blocks))]);
    WP_CLI::log('  Kontakt: ' . count($blocks) . ' bloków');
}

WP_CLI::success('Strony firmowe wypełnione.');
