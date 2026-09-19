<?php

if (!defined('WP_CLI') || !WP_CLI) {
    exit("Uruchom przez: wp eval-file wp-content/themes/raks/bin/uwagi-1.php\n");
}

require_once __DIR__ . '/seed-helpers.php';

$pages = [
    'home' => (int) get_option('page_on_front'),
    'ksiegowy' => uwagi_page('program-ksiegowy'),
    'kadrowy' => uwagi_page('program-kadrowo-placowy'),
    'sprzedaz' => uwagi_page('sprzedaz-i-magazyn'),
    'chmura' => uwagi_page('raks-w-chmurze'),
    'lokalnie' => uwagi_page('raks-lokalnie'),
    'wersje' => uwagi_page('aktualne-wersje'),
    'szkolenia' => uwagi_page('szkolenia'),
    'o-nas' => uwagi_page('o-nas'),
    'kontakt' => uwagi_page('kontakt'),
    'wyprobuj' => uwagi_page('wyprobuj'),
    'prezentacja' => uwagi_page('prezentacja'),
];

WP_CLI::log('Animacje w nagłówkach…');

$animacje = [
    'ksiegowy' => 'fog',
    'kadrowy' => 'fog',
    'sprzedaz' => 'fog',
    'chmura' => 'clouds',
    'lokalnie' => 'cells',
    'wersje' => 'net',
    'szkolenia' => 'net',
    'o-nas' => 'birds',
    'wyprobuj' => 'bands',
    'prezentacja' => 'bands',
];

foreach ($animacje as $slug => $effect) {
    $count = uwagi_edit_blocks($pages[$slug] ?? 0, 'hero', static function (array $data) use ($effect): array {
        $data['media_type'] = 'animation';
        $data['animation'] = $effect;

        return $data;
    });

    if ($count) {
        WP_CLI::log("  {$slug}: {$effect}");
    }
}

uwagi_edit_blocks($pages['kontakt'], 'hero', static function (array $data): array {
    $data['media_type'] = 'video';
    $data['video_url'] = $data['video_url'] ?? '' ?: 'https://stream.mux.com/kimF2ha9zLrX64H00UgLGPflCzNtl1T0215MlAmeOztv8.m3u8';

    return $data;
});

WP_CLI::log('Sekcja „Wsparcie”…');

$kadry = ['58% 34%', '60% 40%', '62% 32%', '60% 50%', '60% 35%', '55% 28%'];

foreach ($pages as $slug => $pageId) {
    uwagi_edit_blocks($pageId, 'support', static function (array $data) use ($kadry): array {
        foreach ($kadry as $i => $focal) {
            if (isset($data["items_{$i}_image"])) {
                $data["items_{$i}_focal"] = $focal;
            }
        }

        for ($i = 0; $i < (int) ($data['items'] ?? 0); $i++) {
            $title = (string) ($data["items_{$i}_title_text"] ?? '');

            if (str_contains($title, 'Filmy instruktażowe')) {
                $data["items_{$i}_link"] = [
                    'title' => 'Zobacz na YouTube',
                    'url' => RAKS_YOUTUBE,
                    'target' => '_blank',
                ];
            }

            if (str_contains($title, 'Baza wiedzy')) {
                $data["items_{$i}_link"] = [
                    'title' => 'Przejdź do bazy wiedzy',
                    'url' => RAKS_BAZA_WIEDZY,
                    'target' => '_blank',
                ];
            }
        }

        return $data;
    });
}

WP_CLI::log('Zdjęcie w tle sekcji z modułami…');

$zespol = raks_seed_image('dlaczego/zespol.jpg', 'Zespół RAKS w biurze');

foreach (['chmura', 'lokalnie'] as $slug) {
    $count = uwagi_edit_blocks($pages[$slug] ?? 0, 'choice', static function (array $data) use ($zespol): ?array {
        if ((int) ($data['cards'] ?? 0) < 3) {
            return null;
        }

        $data['background_image'] = $zespol;

        return $data;
    });

    if ($count) {
        WP_CLI::log("  {$slug}: tło ustawione");
    }
}

WP_CLI::log('Sekcja „Co nas wyróżnia”…');

uwagi_edit_blocks($pages['o-nas'], 'features', static function (array $data): ?array {
    if (!str_contains((string) ($data['title_text'] ?? ''), 'nie kupisz u konkurencji')) {
        return null;
    }

    $data['layout'] = 'cards-inline';

    return $data;
});

WP_CLI::log('Tabele…');

foreach (['chmura', 'lokalnie'] as $slug) {
    uwagi_edit_blocks($pages[$slug] ?? 0, 'table', static function (array $data): ?array {
        if (!str_contains((string) ($data['title_text'] ?? ''), 'Porównaj oba modele')) {
            return null;
        }

        foreach ($data as $key => $value) {
            if (preg_match('/^rows_\d+_cells_\d+_style$/', $key) && in_array($value, ['strong', 'muted'], true)) {
                $data[$key] = 'normal';
            }
        }

        return $data;
    });
}

uwagi_edit_blocks($pages['wersje'], 'table', static function (array $data): array {
    foreach ($data as $key => $value) {
        if (!preg_match('/^(tabs_\d+_)?rows_\d+_cells_(\d+)_style$/', $key, $m)) {
            continue;
        }

        $prefix = substr($key, 0, -strlen('style'));
        $column = (int) $m[2];

        if ($column === 0) {
            $data[$prefix . 'style'] = 'plain';
        } elseif ($column === 1) {
            $data[$prefix . 'style'] = 'strong';
        } elseif ($column === 3) {
            $data[$prefix . 'icon'] = 'download';
        } elseif ($column === 4) {
            $data[$prefix . 'icon'] = 'file-text';
            $data[$prefix . 'style'] = 'muted';
        }
    }

    return $data;
});

$kontaktUrl = (string) get_permalink($pages['kontakt']);

$odmianyTabel = [
    'chmura' => [
        'variant' => 'pricing',
        'footnote' => 'Ceny przykładowe — finalny cennik ustalamy indywidualnie do liczby stanowisk i klientów.',
        'footnote_link' => ['title' => 'Poproś o wycenę', 'url' => $kontaktUrl, 'target' => ''],
    ],
    'lokalnie' => [
        'variant' => 'compare',
        'footnote' => 'Nie wiesz, co wybrać?',
        'footnote_link' => ['title' => 'Porozmawiaj z doradcą', 'url' => $kontaktUrl, 'target' => ''],
    ],
    'wersje' => [
        'variant' => 'downloads',
    ],
];

foreach ($odmianyTabel as $slug => $ustawienia) {
    uwagi_edit_blocks($pages[$slug] ?? 0, 'table', static function (array $data) use ($ustawienia): array {
        $data = array_merge($data, $ustawienia);

        foreach ($data as $key => $value) {
            if (preg_match('/^rows_\d+_cells_\d+_icon$/', $key) && $value === 'wifi') {
                $data[substr($key, 0, -strlen('icon')) . 'style'] = 'muted';
            }
        }

        return $data;
    });

    WP_CLI::log("  {$slug}: {$ustawienia['variant']}");
}

uwagi_edit_blocks($pages['prezentacja'], 'hero', static function (array $data): array {
    $data['text_width'] = 'narrow';

    return $data;
});

WP_CLI::log('Podstrona „Umów prezentację”…');

uwagi_edit_blocks($pages['prezentacja'], 'content-media', static function (array $data): array {
    $data['align'] = 'start';
    $data['container'] = 'page';
    $data['embed_height'] = 1050;
    $data['footnote_label'] = 'Wolisz porozmawiać od razu?';
    $data['footnote_icon'] = 'phone';
    $data['footnote_link'] = [
        'title' => '885 885 000',
        'url' => 'tel:885885000',
        'target' => '',
    ];

    return $data;
});

WP_CLI::log('Podstrona „Kontakt”…');

uwagi_edit_blocks($pages['kontakt'], 'contact', static function (array $data): array {
    if ((int) ($data['info_cards'] ?? 0) > 0) {
        $data['info_cards_0_dark'] = true;
    }

    $wiersze = [
        'Dział sprzedaży' => [
            'link' => ['title' => '885 885 000', 'url' => 'tel:885885000', 'target' => ''],
            'text' => " · sprzedaz@raks.pl\npon–pt 9:00–17:00",
        ],
        'Adres biura' => [
            'link' => ['title' => '', 'url' => '', 'target' => ''],
            'text' => "ul. Kamionkowska 51\n03-812 Warszawa",
        ],
        'Pomoc techniczna' => [
            'link' => ['title' => 'Formularz zgłoszeniowy', 'url' => RAKS_FORMULARZ, 'target' => '_blank'],
            'text' => "\npon–pt 9:00–17:00",
        ],
        'Pomoc zdalna' => [
            'link' => [
                'title' => 'Udostępnij pulpit konsultantowi',
                'url' => 'https://get.teamviewer.com/3zzbuns',
                'target' => '_blank',
            ],
            'text' => ' i rozwiążcie problem razem.',
        ],
    ];

    for ($c = 0; $c < (int) ($data['info_cards'] ?? 0); $c++) {
        for ($i = 0; $i < (int) ($data["info_cards_{$c}_items"] ?? 0); $i++) {
            $label = (string) ($data["info_cards_{$c}_items_{$i}_label"] ?? '');

            if (!isset($wiersze[$label])) {
                continue;
            }

            $obecny = (array) ($data["info_cards_{$c}_items_{$i}_link"] ?? []);

            $data["info_cards_{$c}_items_{$i}_link"] = $wiersze[$label]['link'] + [
                'url' => $obecny['url'] ?? '',
                'target' => $obecny['target'] ?? '',
            ];
            $data["info_cards_{$c}_items_{$i}_text"] = $wiersze[$label]['text'];
        }
    }

    $data['map_embed'] = '';
    $data['map_caption'] = '';

    $registry = [
        ['NIP', '113-00-93-176'],
        ['Adres siedziby', 'ul. Kamionkowska 51, 03-812 Warszawa'],
        ['Rachunek bankowy', '36 1140 1010 0000 5142 3700 1011'],
        ['KRS', '0000099583 · Sąd Rejonowy dla m.st. Warszawy w Warszawie, XIII Wydział Gospodarczy KRS'],
        ['Kapitał zakładowy', '300 000 zł'],
    ];

    $data['registry_title'] = 'Dane rejestrowe';
    $data += uwagi_repeater('contact', 'registry', array_map(
        static fn(array $row): array => ['label' => $row[0], 'value' => $row[1]],
        $registry
    ));

    if ((int) ($data['subjects'] ?? 0) === 0) {
        $data += uwagi_repeater('contact', 'subjects', [
            ['label' => 'Pytanie o program'],
            ['label' => 'Wycena'],
            ['label' => 'Pomoc techniczna'],
            ['label' => 'Inne'],
        ]);
    }

    return $data;
});

uwagi_edit_blocks($pages['kontakt'], 'advisor', static function (array $data): ?array {
    if (($data['button_primary_link']['title'] ?? '') === 'Zobacz RAKS w akcji') {
        return null;
    }

    $data['button_primary_link'] = [
        'title' => 'Zobacz RAKS w akcji',
        'url' => $data['button_primary_link']['url'] ?? '',
        'target' => $data['button_primary_link']['target'] ?? '',
    ];

    return $data;
});

WP_CLI::log('Kolory kategorii szkoleń…');

$kolory = [
    'finanse-i-ksiegowosc' => '#1f4bd8',
    'kadry-i-place' => '#b3357a',
    'sprzedaz' => '#0f7a5a',
    'szkolenia' => '#5a5f7a',
];

$colorKey = raks_seed_key('color', 'category');

foreach ($kolory as $slug => $hex) {
    $term = get_term_by('slug', $slug, 'kategoria-produktu');

    if ($term) {
        update_field($colorKey, $hex, 'kategoria-produktu_' . $term->term_id);
        WP_CLI::log("  {$slug}: {$hex}");
    }
}

WP_CLI::log('Menu „Wsparcie”…');

$adresyWsparcia = [
    'Baza wiedzy' => RAKS_BAZA_WIEDZY,
    'Filmy instruktażowe' => RAKS_YOUTUBE,
    'Formularz zgłoszeniowy' => RAKS_FORMULARZ,
];

$headerMenu = (array) get_field('header_menu', 'option');

foreach ($headerMenu as $i => $item) {
    foreach ((array) ($item['submenu'] ?? []) as $j => $child) {
        $title = (string) ($child['link']['title'] ?? '');

        if (!isset($adresyWsparcia[$title])) {
            continue;
        }

        $headerMenu[$i]['submenu'][$j]['link'] = [
            'title' => $title,
            'url' => $adresyWsparcia[$title],
            'target' => '_blank',
        ];

        WP_CLI::log("  {$title} → {$adresyWsparcia[$title]}");
    }
}

update_field(raks_seed_key('header_menu'), $headerMenu, 'option');

WP_CLI::log('Stopka…');

update_field(raks_seed_key('support_link'), [
    'title' => 'formularz zgłoszeniowy',
    'url' => RAKS_FORMULARZ,
    'target' => '_blank',
], 'option');

raks_seed_menu('Stopka: Dokumenty', 'legalMenu', [
    ['title' => 'Polityka prywatności', 'url' => RAKS_POLITYKA_PRYWATNOSCI],
    ['title' => 'Strona główna', 'url' => home_url('/')],
]);

raks_seed_menu('Stopka: Wsparcie', '', [
    ['title' => 'Aktualne wersje', 'url' => get_permalink($pages['wersje'])],
    ['title' => 'Baza wiedzy', 'url' => RAKS_BAZA_WIEDZY],
    ['title' => 'Szkolenia', 'url' => get_permalink($pages['szkolenia'])],
    ['title' => 'Filmy instruktażowe', 'url' => RAKS_YOUTUBE],
    ['title' => 'Pomoc zdalna', 'url' => get_permalink($pages['kontakt'])],
]);

\App\Services\ThemeCache::flush('options');
\App\Services\ThemeCache::flush('menus');

WP_CLI::success('Uwagi runda 1 naniesione. Cache motywu wyczyszczony.');
