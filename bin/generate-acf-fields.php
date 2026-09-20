<?php

$out = dirname(__DIR__) . '/acf-json';

const TAGS_HEADING = ['h1' => 'h1', 'h2' => 'h2', 'h3' => 'h3', 'h4' => 'h4', 'p' => 'p', 'span' => 'span', 'div' => 'div'];
const TAGS_TEXT = ['p' => 'p', 'div' => 'div', 'span' => 'span'];
const TAGS_EYEBROW = ['span' => 'span', 'p' => 'p', 'div' => 'div'];

const BUTTON_VARIANTS = [
    'primary' => 'Główny (pomarańczowy)',
    'accent' => 'Akcent (granatowy)',
    'ghost' => 'Obrys na jasnym',
    'on-navy' => 'Biały na ciemnym',
    'ghost-navy' => 'Obrys na ciemnym',
];

const BUTTON_SIZES = ['base' => 'Zwykły', 'lg' => 'Duży'];

function key_for(string $path): string
{
    return 'field_' . substr(md5('taxeon:' . $path), 0, 13);
}

function group_key(string $name): string
{
    return 'group_' . substr(md5('taxeon-group:' . $name), 0, 13);
}

function f(string $path, string $label, string $type, array $extra = []): array
{
    $pos = strrpos($path, '.');
    $name = $pos === false ? $path : substr($path, $pos + 1);

    $base = [
        'key' => key_for($path),
        'label' => $label,
        'name' => $name,
        'type' => $type,
        'instructions' => '',
        'required' => 0,
        'conditional_logic' => 0,
        'wrapper' => ['width' => '', 'class' => '', 'id' => ''],
    ];

    $defaults = [
        'text' => ['default_value' => '', 'placeholder' => '', 'prepend' => '', 'append' => '', 'maxlength' => ''],
        'textarea' => ['default_value' => '', 'placeholder' => '', 'maxlength' => '', 'rows' => 3, 'new_lines' => ''],
        'wysiwyg' => ['default_value' => '', 'tabs' => 'all', 'toolbar' => 'basic', 'media_upload' => 0, 'delay' => 0],
        'image' => ['return_format' => 'array', 'preview_size' => 'medium', 'library' => 'all', 'min_width' => '', 'min_height' => '', 'min_size' => '', 'max_width' => '', 'max_height' => '', 'max_size' => '', 'mime_types' => ''],
        'file' => ['return_format' => 'array', 'library' => 'all', 'min_size' => '', 'max_size' => '', 'mime_types' => 'mp4,webm'],
        'link' => ['return_format' => 'array'],
        'select' => ['choices' => [], 'default_value' => '', 'return_format' => 'value', 'multiple' => 0, 'allow_null' => 1, 'ui' => 0, 'ajax' => 0, 'placeholder' => ''],
        'true_false' => ['message' => '', 'default_value' => 0, 'ui' => 1, 'ui_on_text' => '', 'ui_off_text' => ''],
        'number' => ['default_value' => '', 'min' => '', 'max' => '', 'step' => '', 'placeholder' => '', 'prepend' => '', 'append' => ''],
        'date_picker' => ['display_format' => 'j.m.Y', 'return_format' => 'Y-m-d', 'first_day' => 1],
        'color_picker' => ['default_value' => '', 'enable_opacity' => 0, 'return_format' => 'string'],
        'taxonomy' => ['taxonomy' => 'category', 'field_type' => 'select', 'add_term' => 0, 'save_terms' => 0, 'load_terms' => 0, 'return_format' => 'id', 'multiple' => 0, 'allow_null' => 1],
        'relationship' => ['post_type' => ['post'], 'taxonomy' => '', 'filters' => ['search'], 'return_format' => 'id', 'min' => '', 'max' => '', 'elements' => ['featured_image']],
        'repeater' => ['collapsed' => '', 'min' => 0, 'max' => 0, 'layout' => 'block', 'button_label' => 'Dodaj', 'sub_fields' => []],
        'group' => ['layout' => 'block', 'sub_fields' => []],
        'tab' => ['placement' => 'top', 'endpoint' => 0],
    ];

    return array_merge($base, $defaults[$type] ?? [], $extra);
}

function textGroup(string $path, string $label, array $tags, string $defaultTag, string $type = 'text', array $extra = []): array
{
    return f($path, $label, 'group', array_merge([
        'layout' => 'row',
        'sub_fields' => [
            f($path . '.text', 'Treść', $type, [
                'wrapper' => ['width' => '75', 'class' => '', 'id' => ''],
            ] + ($type === 'textarea' ? ['rows' => 3] : [])),
            f($path . '.tag', 'Znacznik', 'select', [
                'choices' => $tags,
                'default_value' => $defaultTag,
                'allow_null' => 0,
                'wrapper' => ['width' => '25', 'class' => '', 'id' => ''],
            ]),
        ],
    ], $extra));
}

function buttonGroup(string $path, string $label, string $variant = 'primary', string $size = 'base', array $extra = []): array
{
    return f($path, $label, 'group', array_merge([
        'layout' => 'row',
        'sub_fields' => [
            f($path . '.link', 'Link', 'link', [
                'wrapper' => ['width' => '50', 'class' => '', 'id' => ''],
            ]),
            f($path . '.variant', 'Styl', 'select', [
                'choices' => BUTTON_VARIANTS,
                'default_value' => $variant,
                'allow_null' => 0,
                'wrapper' => ['width' => '30', 'class' => '', 'id' => ''],
            ]),
            f($path . '.size', 'Rozmiar', 'select', [
                'choices' => BUTTON_SIZES,
                'default_value' => $size,
                'allow_null' => 0,
                'wrapper' => ['width' => '20', 'class' => '', 'id' => ''],
            ]),
            icon($path . '.icon', 'Ikona', [
                'wrapper' => ['width' => '30', 'class' => '', 'id' => ''],
            ]),
        ],
    ], $extra));
}

function icon(string $path, string $label = 'Ikona', array $extra = []): array
{
    return f($path, $label, 'select', array_merge([
        'instructions' => 'Lista pochodzi z plików w src/svg.',
        'choices' => [],
    ], $extra));
}

function sectionSettings(string $prefix): array
{
    return [
        f($prefix . '.tab_section', 'Sekcja', 'tab'),
        f($prefix . '.background', 'Tło', 'select', [
            'choices' => [
                'page' => 'Białe',
                'soft' => 'Jasnoszare',
                'soft-2' => 'Jasnoszare chłodne',
                'navy' => 'Granatowe',
                'gradient' => 'Gradient niebieski',
            ],
            'default_value' => 'page',
            'allow_null' => 0,
            'wrapper' => ['width' => '40', 'class' => '', 'id' => ''],
        ]),
        f($prefix . '.container', 'Szerokość', 'select', [
            'choices' => [
                'page' => 'Standardowa (1200)',
                'narrow' => 'Wąska (860)',
                'wide' => 'Szeroka (1440)',
                'full' => 'Pełna',
            ],
            'default_value' => 'page',
            'allow_null' => 0,
            'wrapper' => ['width' => '60', 'class' => '', 'id' => ''],
        ]),
        f($prefix . '.padding_top', 'Odstęp u góry', 'select', [
            'choices' => ['default' => 'Standardowy (112)', 'medium' => 'Średni (104)', 'small' => 'Mały (72)', 'none' => 'Brak'],
            'default_value' => 'default',
            'allow_null' => 0,
            'wrapper' => ['width' => '50', 'class' => '', 'id' => ''],
        ]),
        f($prefix . '.padding_bottom', 'Odstęp u dołu', 'select', [
            'choices' => ['default' => 'Standardowy (112)', 'medium' => 'Średni (104)', 'small' => 'Mały (72)', 'none' => 'Brak'],
            'default_value' => 'default',
            'allow_null' => 0,
            'wrapper' => ['width' => '50', 'class' => '', 'id' => ''],
        ]),
    ];
}

function sectionHead(string $prefix, string $titleTag = 'h2'): array
{
    return [
        textGroup($prefix . '.eyebrow', 'Nadkreślenie', TAGS_EYEBROW, 'span'),
        textGroup($prefix . '.title', 'Tytuł', TAGS_HEADING, $titleTag),
        textGroup($prefix . '.text', 'Opis', TAGS_TEXT, 'p', 'textarea'),
    ];
}

$groups = [];

$groups['options'] = [
    'title' => 'Opcje motywu',
    'location' => [[['param' => 'options_page', 'operator' => '==', 'value' => 'theme-options']]],
    'fields' => [
        f('opt.tab_brand', 'Identyfikacja', 'tab'),
        f('opt.logo', 'Logo (ciemne)', 'image', ['instructions' => 'Puste = wbudowany znak SVG, który sam dobiera kolor.']),
        f('opt.logo_white', 'Logo (białe)', 'image', ['instructions' => 'Potrzebne tylko, gdy podmieniasz logo na własny plik.']),

        f('opt.tab_header', 'Nagłówek', 'tab'),

        f('opt.hero_video', 'Wideo w tle nagłówka', 'text', [
            'default_value' => 'https://stream.mux.com/kimF2ha9zLrX64H00UgLGPflCzNtl1T0215MlAmeOztv8.m3u8',
            'instructions' => 'Adres strumienia (HLS albo MP4). Używają go szablony bez bloku Hero — '
                . 'strona szkolenia i archiwum kategorii. Puste = sam gradient.',
        ]),
        f('opt.header_menu', 'Menu główne (mega-menu)', 'repeater', [
            'button_label' => 'Dodaj pozycję',
            'collapsed' => key_for('opt.header_menu.link'),
            'sub_fields' => [
                f('opt.header_menu.link', 'Link', 'link', ['required' => 1]),
                f('opt.header_menu.submenu_width', 'Szerokość panelu', 'select', [
                    'choices' => ['wide' => 'Szeroki (640px)', 'narrow' => 'Wąski (380px)'],
                    'default_value' => 'wide',
                    'allow_null' => 0,
                ]),
                f('opt.header_menu.submenu', 'Pozycje w rozwijanym panelu', 'repeater', [
                    'button_label' => 'Dodaj pozycję',
                    'collapsed' => key_for('opt.header_menu.submenu.link'),
                    'sub_fields' => [
                        f('opt.header_menu.submenu.link', 'Link', 'link', ['required' => 1]),
                        icon('opt.header_menu.submenu.icon'),
                        f('opt.header_menu.submenu.description', 'Opis', 'textarea', ['rows' => 2]),
                    ],
                ]),
            ],
        ]),
        f('opt.header_contact_link', 'Link kontaktowy', 'link'),
        f('opt.header_cta', 'Przycisk CTA', 'link'),

        f('opt.tab_footer', 'Stopka', 'tab'),
        f('opt.footer_description', 'Opis pod logo', 'textarea', ['rows' => 3]),
        f('opt.footer_menus', 'Kolumny z linkami', 'repeater', [
            'button_label' => 'Dodaj kolumnę',
            'instructions' => 'Nagłówek plus menu z Wygląd → Menu. Kolejność ustawiasz przeciąganiem.',
            'collapsed' => key_for('opt.footer_menus.title_menu'),
            'sub_fields' => [
                f('opt.footer_menus.title_menu', 'Nagłówek kolumny', 'text', [
                    'required' => 1,
                    'wrapper' => ['width' => '50', 'class' => '', 'id' => ''],
                ]),
                f('opt.footer_menus.select_menu', 'Menu', 'select', [
                    'required' => 1,
                    'allow_null' => 0,
                    'wrapper' => ['width' => '50', 'class' => '', 'id' => ''],
                ]),
            ],
        ]),
        f('opt.footer_contact_title', 'Kolumna kontaktu — nagłówek', 'text', ['default_value' => 'Kontakt']),
        f('opt.footer_company', 'Nazwa firmy', 'text'),
        f('opt.email', 'E-mail', 'text'),
        f('opt.footer_hours', 'Godziny pracy', 'text'),
        f('opt.footer_contact_link', 'Odnośnik pod kontaktem', 'link'),
        f('opt.copyrights', 'Prawa autorskie', 'text'),

        f('opt.tab_forms', 'Formularze', 'tab'),
        f('opt.contact_email', 'Adres do powiadomień', 'text', [
            'instructions' => 'Puste = e-mail administratora.',
        ]),
        f('opt.recaptcha_enabled', 'Włącz reCAPTCHA v3', 'true_false'),
        f('opt.recaptcha_site_key', 'Klucz witryny', 'text', [
            'conditional_logic' => [[['field' => key_for('opt.recaptcha_enabled'), 'operator' => '==', 'value' => '1']]],
        ]),
        f('opt.recaptcha_secret_key', 'Klucz tajny', 'text', [
            'conditional_logic' => [[['field' => key_for('opt.recaptcha_enabled'), 'operator' => '==', 'value' => '1']]],
        ]),
        f('opt.recaptcha_threshold', 'Próg akceptacji', 'number', [
            'default_value' => '0.5', 'min' => '0', 'max' => '1', 'step' => '0.1',
            'instructions' => 'Zgłoszenia z niższym wynikiem są odrzucane.',
            'conditional_logic' => [[['field' => key_for('opt.recaptcha_enabled'), 'operator' => '==', 'value' => '1']]],
        ]),

        f('opt.tab_scripts', 'Skrypty', 'tab'),
        f('opt.scripts_head', 'Skrypty w <head>', 'textarea', ['rows' => 6, 'new_lines' => '']),
        f('opt.scripts_body', 'Skrypty po <body>', 'textarea', ['rows' => 6, 'new_lines' => '']),
    ],
];

$groups['category'] = [
    'title' => 'Ustawienia kategorii',
    'location' => [
        [['param' => 'taxonomy', 'operator' => '==', 'value' => 'category']],
        [['param' => 'taxonomy', 'operator' => '==', 'value' => 'kategoria-produktu']],
    ],
    'fields' => [
        f('cat.color', 'Kolor', 'color_picker', [
            'instructions' => 'Kolor plakietki na kartach wpisów i szkoleń. Puste = kolor akcentu.',
        ]),
    ],
];

$groups['page'] = [
    'title' => 'Nagłówek strony',
    'location' => [[['param' => 'post_type', 'operator' => '==', 'value' => 'page']]],
    'fields' => [
        f('page.subtitle', 'Podtytuł', 'textarea', [
            'rows' => 2,
            'instructions' => 'Widoczny w ciemnym nagłówku, gdy strona nie zaczyna się blokiem Hero.',
        ]),
    ],
];

$groups['blog_page'] = [
    'title' => 'Archiwum bloga',
    'location' => [[['param' => 'page_type', 'operator' => '==', 'value' => 'posts_page']]],
    'fields' => [
        f('blog.archive_title', 'Nagłówek archiwum', 'text', [
            'instructions' => 'Puste = tytuł strony. Nagłówek bywa dłuższy niż nazwa w menu.',
        ]),
        f('blog.archive_description', 'Opis archiwum', 'wysiwyg', ['toolbar' => 'basic']),
    ],
];

$blocks = [];

$blocks['hero'] = [
    'title' => 'Blok: Hero',
    'fields' => [
        f('hero.badge', 'Plakietka nad tytułem', 'text'),
        textGroup('hero.title', 'Tytuł', TAGS_HEADING, 'h1', 'textarea'),
        f('hero.subtitle', 'Podtytuł', 'text'),
        f('hero.text', 'Opis', 'textarea', ['rows' => 3]),
        f('hero.show_breadcrumbs', 'Pokaż okruszki', 'true_false'),

        f('hero.tab_media', 'Tło', 'tab'),
        f('hero.video', 'Wideo w tle', 'text', [
            'instructions' => 'Adres pliku MP4 albo filmu na YouTube. Puste = sam granat.',
        ]),
        f('hero.poster', 'Plakat wideo', 'image', [
            'instructions' => 'Pierwsza klatka, widoczna zanim wideo się wczyta.',
        ]),

        f('hero.tab_note', 'Ramka pod kartami', 'tab'),
        f('hero.note_title', 'Tytuł', 'text'),
        f('hero.note_text', 'Opis', 'text'),
        f('hero.note_link', 'Odnośnik', 'link'),

        f('hero.tab_stats', 'Pasek liczb', 'tab'),
        f('hero.stats', 'Liczby', 'repeater', [
            'button_label' => 'Dodaj pozycję',
            'collapsed' => key_for('hero.stats.value'),
            'sub_fields' => [
                f('hero.stats.value', 'Wartość', 'text', [
                    'required' => 1,
                    'wrapper' => ['width' => '40', 'class' => '', 'id' => ''],
                ]),
                f('hero.stats.label', 'Podpis', 'text', [
                    'wrapper' => ['width' => '60', 'class' => '', 'id' => ''],
                ]),
                f('hero.stats.url', 'Adres', 'url'),
            ],
        ]),

        f('hero.tab_cards', 'Karty cennika', 'tab'),
        f('hero.cards', 'Karty', 'repeater', [
            'button_label' => 'Dodaj kartę',
            'collapsed' => key_for('hero.cards.label'),
            'sub_fields' => [
                f('hero.cards.label', 'Nazwa', 'text', [
                    'required' => 1,
                    'wrapper' => ['width' => '34', 'class' => '', 'id' => ''],
                ]),
                f('hero.cards.badge', 'Plakietka', 'text', [
                    'wrapper' => ['width' => '33', 'class' => '', 'id' => ''],
                ]),
                f('hero.cards.featured', 'Wyróżniona', 'true_false', [
                    'wrapper' => ['width' => '33', 'class' => '', 'id' => ''],
                ]),
                f('hero.cards.badge_style', 'Styl plakietki', 'select', [
                    'choices' => ['filled' => 'Wypełniona', 'outline' => 'Sama obwódka'],
                    'default_value' => 'filled',
                    'allow_null' => 0,
                    'wrapper' => ['width' => '34', 'class' => '', 'id' => ''],
                ]),
                f('hero.cards.price', 'Cena', 'text', [
                    'wrapper' => ['width' => '50', 'class' => '', 'id' => ''],
                ]),
                f('hero.cards.period', 'Okres', 'text', [
                    'default_value' => '/ mies.',
                    'wrapper' => ['width' => '50', 'class' => '', 'id' => ''],
                ]),
                f('hero.cards.text', 'Opis', 'textarea', ['rows' => 2]),
                f('hero.cards.items', 'Punkty', 'repeater', [
                    'button_label' => 'Dodaj punkt',
                    'collapsed' => key_for('hero.cards.items.text'),
                    'sub_fields' => [f('hero.cards.items.text', 'Treść', 'text', ['required' => 1])],
                ]),
                f('hero.cards.link', 'Przycisk', 'link'),
            ],
        ]),
    ],
];

$blocks['features'] = [
    'title' => 'Blok: Karty',
    'fields' => array_merge(sectionHead('features'), [
        f('features.columns', 'Kolumny', 'select', [
            'choices' => ['2' => 'Dwie', '3' => 'Trzy', '4' => 'Cztery'],
            'default_value' => '2',
            'allow_null' => 0,
        ]),
        f('features.items', 'Karty', 'repeater', [
            'button_label' => 'Dodaj kartę',
            'collapsed' => key_for('features.items.title'),
            'sub_fields' => [
                f('features.items.kicker', 'Nadkreślenie', 'text', [
                    'wrapper' => ['width' => '40', 'class' => '', 'id' => ''],
                ]),
                f('features.items.title', 'Tytuł', 'text', [
                    'required' => 1,
                    'wrapper' => ['width' => '60', 'class' => '', 'id' => ''],
                ]),
                f('features.items.text', 'Opis', 'textarea', ['rows' => 3]),
                f('features.items.link', 'Odnośnik', 'link'),
            ],
        ]),
    ]),
];

$blocks['latest-posts'] = [
    'title' => 'Blok: Baza wiedzy',
    'fields' => array_merge(sectionHead('latest_posts'), [
        f('latest_posts.count', 'Ile wpisów', 'number', ['default_value' => 6, 'min' => 1, 'max' => 12]),

        f('latest_posts.tab_banner', 'Baner', 'tab'),
        f('latest_posts.banner_title', 'Tytuł', 'text'),
        f('latest_posts.banner_text', 'Opis', 'textarea', ['rows' => 2]),
        f('latest_posts.banner_link', 'Przycisk', 'link'),
    ]),
];

$blocks['reviews'] = [
    'title' => 'Blok: Opinie',
    'fields' => array_merge(sectionHead('reviews'), [
        f('reviews.link', 'Odnośnik do wizytówki', 'link'),
        f('reviews.items', 'Opinie', 'repeater', [
            'button_label' => 'Dodaj opinię',
            'collapsed' => key_for('reviews.items.author'),
            'sub_fields' => [
                f('reviews.items.author', 'Autor', 'text', [
                    'required' => 1,
                    'wrapper' => ['width' => '40', 'class' => '', 'id' => ''],
                ]),
                f('reviews.items.when', 'Kiedy', 'text', [
                    'wrapper' => ['width' => '35', 'class' => '', 'id' => ''],
                ]),
                f('reviews.items.rating', 'Ocena', 'number', [
                    'default_value' => 5, 'min' => 1, 'max' => 5,
                    'wrapper' => ['width' => '25', 'class' => '', 'id' => ''],
                ]),
                f('reviews.items.text', 'Treść', 'textarea', ['required' => 1, 'rows' => 3]),
            ],
        ]),
    ]),
];

$blocks['cta'] = [
    'title' => 'Blok: Pas CTA',
    'fields' => [
        textGroup('cta.title', 'Tytuł', TAGS_HEADING, 'h2', 'textarea'),
        f('cta.text', 'Opis', 'textarea', ['rows' => 2]),
        f('cta.button_primary', 'Przycisk główny', 'link'),
        f('cta.button_secondary', 'Przycisk dodatkowy', 'link'),
    ],
];

$blocks['content-media'] = [
    'title' => 'Blok: Treść + media',
    'fields' => array_merge(sectionHead('media'), [
        f('media.media_position', 'Media po stronie', 'select', [
            'choices' => ['right' => 'Prawej', 'left' => 'Lewej'],
            'default_value' => 'right',
            'allow_null' => 0,
            'wrapper' => ['width' => '50', 'class' => '', 'id' => ''],
        ]),
        f('media.frame', 'Ramka', 'select', [
            'choices' => ['none' => 'Bez ramki', 'window' => 'Okno aplikacji'],
            'default_value' => 'none',
            'allow_null' => 0,
            'wrapper' => ['width' => '50', 'class' => '', 'id' => ''],
        ]),
        f('media.frame_label', 'Podpis w pasku okna', 'text', [
            'conditional_logic' => [[['field' => key_for('media.frame'), 'operator' => '==', 'value' => 'window']]],
        ]),
        f('media.media_type', 'Rodzaj mediów', 'select', [
            'choices' => ['image' => 'Zdjęcie', 'embed' => 'Osadzenie (iframe)'],
            'default_value' => 'image',
            'allow_null' => 0,
        ]),
        f('media.image', 'Zdjęcie', 'image', [
            'conditional_logic' => [[['field' => key_for('media.media_type'), 'operator' => '==', 'value' => 'image']]],
        ]),
        f('media.embed_url', 'Adres osadzenia', 'text', [
            'instructions' => 'Adres do iframe — np. kalendarz rezerwacji albo wideo.',
            'conditional_logic' => [[['field' => key_for('media.media_type'), 'operator' => '==', 'value' => 'embed']]],
        ]),
        f('media.embed_height', 'Wysokość osadzenia (px)', 'number', [
            'default_value' => 720, 'min' => 200,
            'conditional_logic' => [[['field' => key_for('media.media_type'), 'operator' => '==', 'value' => 'embed']]],
        ]),
        f('media.items', 'Lista punktów', 'repeater', [
            'button_label' => 'Dodaj punkt',
            'instructions' => 'Sam tekst daje zwykły punkt z ptaszkiem. Tytuł i ikona zamieniają go w pozycję z nagłówkiem.',
            'collapsed' => key_for('media.items.text'),
            'sub_fields' => [
                icon('media.items.icon', 'Ikona', ['wrapper' => ['width' => '30', 'class' => '', 'id' => '']]),
                textGroup('media.items.title', 'Tytuł', TAGS_HEADING, 'h3'),
                f('media.items.text', 'Tekst', 'text', ['required' => 1]),
            ],
        ]),
        f('media.align', 'Wyrównanie kolumn', 'select', [
            'choices' => ['center' => 'Do środka', 'start' => 'Do górnej krawędzi'],
            'default_value' => 'center',
            'allow_null' => 0,
            'instructions' => 'Przy wysokim osadzeniu (kalendarz) tekst wyśrodkowany wisi w połowie ramki.',
            'wrapper' => ['width' => '50', 'class' => '', 'id' => ''],
        ]),
        f('media.link', 'Link tekstowy', 'link'),
        buttonGroup('media.button', 'Przycisk', 'accent'),
        f('media.tab_footnote', 'Dopisek', 'tab'),
        f('media.footnote_label', 'Dopisek — wstęp', 'text', [
            'instructions' => 'Np. „Wolisz porozmawiać od razu?”. Pokazuje się nad odnośnikiem, oddzielony kreską.',
        ]),
        icon('media.footnote_icon', 'Dopisek — ikona', ['instructions' => 'Puste = słuchawka telefonu.']),
        f('media.footnote_link', 'Dopisek — odnośnik', 'link'),
    ]),
];

$blocks['timeline'] = [
    'title' => 'Blok: Kroki',
    'fields' => array_merge(sectionHead('timeline'), [
        f('timeline.items', 'Kroki', 'repeater', [
            'button_label' => 'Dodaj krok',
            'collapsed' => key_for('timeline.items.title'),
            'sub_fields' => [
                f('timeline.items.number', 'Numer', 'text', [
                    'wrapper' => ['width' => '20', 'class' => '', 'id' => ''],
                ]),
                f('timeline.items.title', 'Tytuł', 'text', [
                    'required' => 1,
                    'wrapper' => ['width' => '60', 'class' => '', 'id' => ''],
                ]),
                f('timeline.items.featured', 'Wyróżniony', 'true_false', [
                    'wrapper' => ['width' => '20', 'class' => '', 'id' => ''],
                ]),
                f('timeline.items.text', 'Opis', 'textarea', ['rows' => 3]),
            ],
        ]),

        f('timeline.tab_cta', 'Pasek na dole', 'tab'),
        f('timeline.cta_title', 'Tytuł', 'text'),
        f('timeline.cta_text', 'Opis', 'text'),
        f('timeline.cta_link', 'Przycisk', 'link'),
    ]),
];

$blocks['stats'] = [
    'title' => 'Blok: Liczby',
    'fields' => array_merge(sectionHead('stats'), [
        f('stats.columns', 'Kolumny', 'select', [
            'choices' => ['2' => '2', '3' => '3', '4' => '4'],
            'default_value' => '4',
            'allow_null' => 0,
        ]),
        f('stats.items', 'Liczby', 'repeater', [
            'button_label' => 'Dodaj liczbę',
            'layout' => 'table',
            'sub_fields' => [
                icon('stats.items.icon', 'Ikona', ['wrapper' => ['width' => '20', 'class' => '', 'id' => '']]),
                f('stats.items.value', 'Wartość', 'text', ['required' => 1, 'wrapper' => ['width' => '30', 'class' => '', 'id' => '']]),
                f('stats.items.label', 'Opis', 'text', ['wrapper' => ['width' => '50', 'class' => '', 'id' => '']]),
            ],
        ]),
    ]),
];

$blocks['pricing'] = [
    'title' => 'Blok: Cennik (karty)',
    'fields' => array_merge(sectionHead('pricing'), [
        f('pricing.variant', 'Odmiana', 'select', [
            'choices' => [
                'pricing' => 'Karty z ceną',
                'download' => 'Karty pobierania',
            ],
            'default_value' => 'pricing',
            'allow_null' => 0,
            'instructions' => 'W kartach pobierania ikona stoi w kafelku obok tytułu, a etykieta jest linią z wersją pod tytułem.',
        ]),
        f('pricing.columns', 'Kolumny', 'select', [
            'choices' => ['2' => '2', '3' => '3', '4' => '4'],
            'default_value' => '3',
            'allow_null' => 0,
        ]),
        f('pricing.cards', 'Karty', 'repeater', [
            'button_label' => 'Dodaj kartę',
            'collapsed' => key_for('pricing.cards.title'),
            'sub_fields' => [
                f('pricing.cards.featured', 'Wyróżniona (ciemna)', 'true_false', [
                    'wrapper' => ['width' => '35', 'class' => '', 'id' => ''],
                ]),
                f('pricing.cards.flag', 'Wstążka', 'text', [
                    'instructions' => 'np. „Najpopularniejszy”. Widoczna tylko na karcie wyróżnionej.',
                    'wrapper' => ['width' => '65', 'class' => '', 'id' => ''],
                ]),
                icon('pricing.cards.icon'),
                f('pricing.cards.tag', 'Etykieta', 'text'),
                textGroup('pricing.cards.title', 'Tytuł', TAGS_HEADING, 'h3'),
                textGroup('pricing.cards.text', 'Opis', TAGS_TEXT, 'p', 'textarea'),
                f('pricing.cards.price', 'Cena', 'text', [
                    'wrapper' => ['width' => '50', 'class' => '', 'id' => ''],
                ]),
                f('pricing.cards.price_suffix', 'Dopisek przy cenie', 'text', [
                    'instructions' => 'np. „/ moduł / mies.”',
                    'wrapper' => ['width' => '50', 'class' => '', 'id' => ''],
                ]),
                f('pricing.cards.note', 'Nota pod ceną', 'text'),
                f('pricing.cards.items', 'Lista', 'repeater', [
                    'button_label' => 'Dodaj punkt',
                    'layout' => 'table',
                    'sub_fields' => [f('pricing.cards.items.text', 'Tekst', 'text', ['required' => 1])],
                ]),
                buttonGroup('pricing.cards.button', 'Przycisk', 'accent', 'lg'),
                buttonGroup('pricing.cards.button_secondary', 'Przycisk drugi', 'ghost', 'lg'),
                f('pricing.cards.links', 'Linki dodatkowe', 'repeater', [
                    'button_label' => 'Dodaj link',
                    'layout' => 'table',
                    'sub_fields' => [
                        icon('pricing.cards.links.icon', 'Ikona', ['wrapper' => ['width' => '30', 'class' => '', 'id' => '']]),
                        f('pricing.cards.links.link', 'Link', 'link', ['wrapper' => ['width' => '70', 'class' => '', 'id' => '']]),
                    ],
                ]),
            ],
        ]),
    ]),
];

$blocks['table'] = [
    'title' => 'Blok: Porównanie',
    'fields' => array_merge(sectionHead('table'), [
        f('table.note', 'Ramka z uwagą', 'wysiwyg', [
            'instructions' => 'Pod opisem, z pionową kreską z lewej. Puste = bez ramki.',
        ]),

        f('table.tab_table', 'Tabela', 'tab'),
        f('table.head_label', 'Nagłówek kolumny opisów', 'text', [
            'default_value' => 'Co dostajesz',
            'wrapper' => ['width' => '34', 'class' => '', 'id' => ''],
        ]),
        f('table.columns', 'Kolumny', 'repeater', [
            'button_label' => 'Dodaj kolumnę',
            'max' => 3,
            'collapsed' => key_for('table.columns.label'),
            'sub_fields' => [
                f('table.columns.label', 'Nazwa', 'text', [
                    'required' => 1,
                    'wrapper' => ['width' => '40', 'class' => '', 'id' => ''],
                ]),
                f('table.columns.note', 'Dopisek', 'text', [
                    'wrapper' => ['width' => '40', 'class' => '', 'id' => ''],
                ]),
                f('table.columns.highlight', 'Wyróżniona', 'true_false', [
                    'wrapper' => ['width' => '20', 'class' => '', 'id' => ''],
                ]),
            ],
        ]),
        f('table.rows', 'Wiersze', 'repeater', [
            'button_label' => 'Dodaj wiersz',
            'collapsed' => key_for('table.rows.label'),
            'sub_fields' => [
                f('table.rows.label', 'Opis', 'text', ['required' => 1]),
                f('table.rows.values', 'Wartości', 'repeater', [
                    'button_label' => 'Dodaj wartość',
                    'max' => 3,
                    'instructions' => 'Po jednej na kolumnę, w tej samej kolejności.',
                    'sub_fields' => [
                        f('table.rows.values.on', 'W cenie', 'true_false'),
                    ],
                ]),
            ],
        ]),
    ]),
];

$blocks['contact'] = [
    'title' => 'Blok: Formularz kontaktowy',
    'fields' => array_merge(sectionHead('contact'), [
        f('contact.layout', 'Układ', 'select', [
            'choices' => [
                'split' => 'Tekst obok formularza',
                'card' => 'Sam formularz na środku',
            ],
            'default_value' => 'split',
            'allow_null' => 0,
            'instructions' => 'Karty informacyjne poniżej mają pierwszeństwo — gdy są wypełnione, formularz staje po lewej, a one po prawej.',
        ]),
        f('contact.consent_optional', 'Zgoda jest dobrowolna', 'true_false', [
            'instructions' => 'Domyślnie zgoda na kontakt jest wymagana i bez niej nie da się wysłać. '
                . 'Włączona: dobrowolna zgoda marketingowa, która trafia do zgłoszenia jako zwykłe pole.',
        ]),
        f('contact.consent_rodo', 'Druga zgoda (RODO)', 'textarea', [
            'rows' => 2,
            'instructions' => 'Puste = jedna zgoda. Wolno użyć znaczników a, strong i em.',
        ]),
        f('contact.subject_label', 'Etykieta listy tematów', 'text', ['default_value' => 'Temat']),
        f('contact.hide_message', 'Ukryj pole „Wiadomość”', 'true_false', [
            'instructions' => 'Formularz zapisu na demo albo test go nie potrzebuje.',
        ]),
        f('contact.details', 'Dane kontaktowe', 'repeater', [
            'button_label' => 'Dodaj pozycję',
            'collapsed' => key_for('contact.details.label'),
            'sub_fields' => [
                icon('contact.details.icon'),
                f('contact.details.label', 'Etykieta', 'text', ['required' => 1]),
                f('contact.details.value', 'Wartość', 'text'),
                f('contact.details.link', 'Link (opcjonalnie)', 'link'),
            ],
        ]),
        f('contact.tab_form', 'Formularz', 'tab'),
        f('contact.form_name', 'Nazwa formularza', 'text', [
            'default_value' => 'contact',
            'instructions' => 'Trafia do kolumny „Formularz” na ekranie Zgłoszenia.',
        ]),
        f('contact.subjects', 'Tematy do wyboru', 'repeater', [
            'button_label' => 'Dodaj temat',
            'layout' => 'table',
            'sub_fields' => [f('contact.subjects.label', 'Temat', 'text', ['required' => 1])],
        ]),
        f('contact.consent_text', 'Treść zgody', 'textarea', ['rows' => 3]),
        f('contact.submit_label', 'Etykieta przycisku', 'text', ['default_value' => 'Wyślij wiadomość']),
        f('contact.privacy_note', 'Nota pod formularzem', 'textarea', ['rows' => 2]),
        f('contact.hide_verify', 'Ukryj kafelek „Nie jestem robotem”', 'true_false', [
            'instructions' => 'Ochrona (reCAPTCHA v3, pułapka na boty) działa niezależnie od tego kafelka — to tylko jej widoczne potwierdzenie.',
        ]),
        f('contact.tab_aside', 'Kolumna boczna', 'tab'),
        f('contact.info_cards', 'Karty informacyjne', 'repeater', [
            'button_label' => 'Dodaj kartę',
            'collapsed' => key_for('contact.info_cards.title'),
            'sub_fields' => [
                textGroup('contact.info_cards.title', 'Tytuł', TAGS_HEADING, 'h3'),
                f('contact.info_cards.dark', 'Ciemna karta', 'true_false', [
                    'instructions' => 'Granatowe tło i jasny tekst — jak „Bezpośredni kontakt” w projekcie.',
                ]),
                f('contact.info_cards.items', 'Pozycje', 'repeater', [
                    'button_label' => 'Dodaj pozycję',
                    'collapsed' => key_for('contact.info_cards.items.label'),
                    'sub_fields' => [
                        icon('contact.info_cards.items.icon', 'Ikona', ['wrapper' => ['width' => '30', 'class' => '', 'id' => '']]),
                        f('contact.info_cards.items.label', 'Etykieta', 'text', ['wrapper' => ['width' => '70', 'class' => '', 'id' => '']]),
                        f('contact.info_cards.items.text', 'Treść', 'textarea', ['rows' => 2]),
                        f('contact.info_cards.items.link', 'Link', 'link'),
                    ],
                ]),
            ],
        ]),
        f('contact.registry_title', 'Dane rejestrowe — tytuł', 'text', ['default_value' => 'Dane rejestrowe']),
        f('contact.registry', 'Dane rejestrowe', 'repeater', [
            'button_label' => 'Dodaj wiersz',
            'layout' => 'table',
            'instructions' => 'Pary etykieta–wartość: NIP, adres siedziby, rachunek, KRS, kapitał zakładowy.',
            'sub_fields' => [
                f('contact.registry.label', 'Etykieta', 'text', ['required' => 1]),
                f('contact.registry.value', 'Wartość', 'textarea', ['rows' => 2, 'required' => 1]),
            ],
        ]),
        f('contact.map_embed', 'Mapa — kod osadzenia', 'textarea', [
            'rows' => 3,
            'instructions' => 'Adres z Google Maps (parametr src z <iframe>). Zostawiamy puste, jeśli mapa ma się nie pokazywać.',
        ]),
        f('contact.map_caption', 'Podpis pod mapą', 'text'),
    ]),
];

$blocks['faq'] = [
    'title' => 'Blok: FAQ',
    'fields' => array_merge(sectionHead('faq'), [
        f('faq.link', 'Odnośnik pod opisem', 'link'),
        f('faq.open_first', 'Rozwiń pierwsze pytanie', 'true_false'),
        f('faq.items', 'Pytania', 'repeater', [
            'button_label' => 'Dodaj pytanie',
            'collapsed' => key_for('faq.items.question'),
            'sub_fields' => [
                f('faq.items.question', 'Pytanie', 'text', ['required' => 1]),
                f('faq.items.answer', 'Odpowiedź', 'wysiwyg', ['required' => 1]),
            ],
        ]),
    ]),
];

foreach ($blocks as $slug => $block) {
    $prefix = str_replace('-', '_', $slug);

    $fields = $block['fields'];

    if (($fields[0]['type'] ?? '') !== 'tab') {
        array_unshift($fields, f($prefix . '.tab_content', 'Treść', 'tab'));
    }

    $groups['block-' . $slug] = [
        'title' => $block['title'],
        'location' => [[['param' => 'block', 'operator' => '==', 'value' => 'acf/' . $slug]]],
        'fields' => array_merge($fields, sectionSettings($prefix)),
    ];
}

foreach (glob($out . '/group_*.json') ?: [] as $stale) {
    unlink($stale);
}

$written = 0;

foreach ($groups as $name => $group) {
    $json = [
        'key' => group_key($name),
        'title' => $group['title'],
        'fields' => $group['fields'],
        'location' => $group['location'],
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => '',
        'show_in_rest' => 0,
        'modified' => 1754524800,
    ];

    file_put_contents(
        $out . '/' . group_key($name) . '.json',
        json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n"
    );

    $written++;
    echo "  {$group['title']}\n";
}

echo "\nZapisano grup: {$written}\n";
