<?php
require get_template_directory() . '/bin/seed-helpers.php';
$t = fn(string $x, string $tag = 'p'): array => ['text' => $x, 'tag' => $tag];
$l = fn(string $tytul, string $url): array => ['title' => $tytul, 'url' => str_starts_with($url, 'http') || str_starts_with($url, 'mailto:') || str_starts_with($url, '#') ? $url : home_url($url), 'target' => ''];
$pusty = ['title' => '', 'url' => '', 'target' => ''];

$grupy = json_decode(file_get_contents(get_template_directory() . '/bin/dane-faq.json'), true);

$hero = taxeon_seed_block('hero', [
    'layout' => 'plain', 'button_style' => 'light',
    'badge' => 'Pytania i odpowiedzi',
    'title' => $t('Najczęstsze pytania o księgowość Taxeon', 'h1'),
    'subtitle' => '',
    'text' => 'Wszystko, co najczęściej słyszymy od klientów. Jeśli nie znajdziesz odpowiedzi — napisz lub zadzwoń, odpowiadamy w ciągu jednego dnia roboczego.',
    'chips' => array_map(fn($g) => ['link' => $l($g['title'], '#' . $g['id'])], $grupy),
    'button_primary' => $pusty, 'button_secondary' => $pusty,
    'trust' => [], 'cards' => [], 'stats' => [], 'video' => '',
]);

$pytania = taxeon_seed_block('faq', [
    'eyebrow' => $t('', 'span'), 'title' => $t('', 'h2'), 'text' => $t(''),
    'link' => $pusty, 'open_first' => 1, 'items' => [],
    'groups' => array_map(fn($g) => [
        'anchor' => $g['id'],
        'title' => $g['title'],
        'eyebrow' => 'Kategoria',
        'items' => array_map(fn($p) => [
            'question' => $p['q'],
            'answer' => '<p>' . $p['a'] . '</p>',
        ], $g['items']),
    ], $grupy),
    'background' => 'page', 'padding_top' => 'medium', 'padding_bottom' => 'medium',
]);

$cta = taxeon_seed_block('cta', [
    'title_size' => 'sm',
    'title' => $t('Nie znalazłeś odpowiedzi?', 'h2'),
    'text' => 'Napisz do nas — odpowiadamy w ciągu jednego dnia roboczego.',
    'button_primary' => $l('Napisz do nas →', 'mailto:kontakt@taxeon.pl'),
    'button_secondary' => $pusty,
    'background' => 'navy',
]);

$istnieje = get_page_by_path('faq');
$id = wp_insert_post(wp_slash([
    'ID' => $istnieje ? $istnieje->ID : 0,
    'post_type' => 'page', 'post_status' => 'publish',
    'post_title' => 'FAQ', 'post_name' => 'faq',
    'post_content' => implode("\n\n", [$hero, $pytania, $cta]),
]));
echo "  strona FAQ #{$id}, grup: " . count($grupy) . ", pytań: " . array_sum(array_map(fn($g) => count($g['items']), $grupy)) . "\n";
