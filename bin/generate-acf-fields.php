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
        'checkbox' => ['choices' => [], 'default_value' => [], 'return_format' => 'value', 'allow_custom' => 0, 'layout' => 'vertical', 'toggle' => 1],
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
                'light' => 'Gradient jasny',
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
                'content' => 'Treściowa (1160)',
                'narrow' => 'Wąska (860)',
                'wide' => 'Szeroka (1440)',
                'full' => 'Pełna',
            ],
            'default_value' => 'page',
            'allow_null' => 0,
            'wrapper' => ['width' => '60', 'class' => '', 'id' => ''],
        ]),
        f($prefix . '.padding_top', 'Odstęp u góry', 'select', [
            'choices' => ['default' => 'Standardowy (112)', 'large' => 'Duży (108)', 'medium' => 'Średni (104)', 'medium-sm' => 'Średni mniejszy (100)', 'small-lg' => 'Mały większy (96)', 'small' => 'Mały (72)', 'none' => 'Brak'],
            'default_value' => 'default',
            'allow_null' => 0,
            'wrapper' => ['width' => '50', 'class' => '', 'id' => ''],
        ]),
        f($prefix . '.padding_bottom', 'Odstęp u dołu', 'select', [
            'choices' => ['default' => 'Standardowy (112)', 'large' => 'Duży (108)', 'medium' => 'Średni (104)', 'medium-sm' => 'Średni mniejszy (100)', 'small-lg' => 'Mały większy (96)', 'small' => 'Mały (72)', 'none' => 'Brak'],
            'default_value' => 'default',
            'allow_null' => 0,
            'wrapper' => ['width' => '50', 'class' => '', 'id' => ''],
        ]),
    ];
}

function headSizeFields(string $prefix): array
{
    return [
        f($prefix . '.head_size', 'Rozmiar nagłówka', 'select', [
            'choices' => [
                'md' => 'Standardowy (46 px)',
                'lg' => 'Duży (50 px)',
                'sm' => 'Mniejszy (42 px)',
            ],
            'default_value' => 'md',
            'allow_null' => 0,
            'wrapper' => ['width' => '34', 'class' => '', 'id' => ''],
        ]),
        f($prefix . '.head_width', 'Szerokość nagłówka', 'select', [
            'choices' => [
                '' => 'Cała kolumna',
                '56ch' => '56 znaków',
                '60ch' => '60 znaków',
                '62ch' => '62 znaki',
            ],
            'default_value' => '',
            'allow_null' => 0,
            'wrapper' => ['width' => '33', 'class' => '', 'id' => ''],
        ]),
        f($prefix . '.title_width', 'Szerokość tytułu', 'select', [
            'choices' => [
                '' => 'Cała kolumna',
                '18ch' => '18 znaków',
                '20ch' => '20 znaków',
                '22ch' => '22 znaki',
                '24ch' => '24 znaki',
            ],
            'default_value' => '',
            'allow_null' => 0,
            'wrapper' => ['width' => '33', 'class' => '', 'id' => ''],
        ]),
        f($prefix . '.text_gap', 'Odstęp nad opisem', 'select', [
            'choices' => ['' => 'Jak w rozmiarze', '16' => '16 px', '18' => '18 px', '20' => '20 px', '24' => '24 px', '26' => '26 px'],
            'default_value' => '',
            'allow_null' => 0,
            'wrapper' => ['width' => '25', 'class' => '', 'id' => ''],
        ]),
        f($prefix . '.text_width', 'Szerokość opisu', 'select', [
            'choices' => [
                '' => 'Cała kolumna',
                '46ch' => '46 znaków',
                '56ch' => '56 znaków',
                '60ch' => '60 znaków',
                '62ch' => '62 znaki',
            ],
            'default_value' => '',
            'allow_null' => 0,
            'wrapper' => ['width' => '33', 'class' => '', 'id' => ''],
        ]),
    ];
}

function sectionHead(string $prefix, string $titleTag = 'h2'): array
{
    return [
        textGroup($prefix . '.eyebrow', 'Nadkreślenie', TAGS_EYEBROW, 'span'),
        textGroup($prefix . '.title', 'Tytuł', TAGS_HEADING, $titleTag),
        textGroup($prefix . '.text', 'Opis', TAGS_TEXT, 'p', 'textarea'),
        ...headSizeFields($prefix),
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

        f('opt.booking_url', 'Adres rezerwacji (YouCanBookMe)', 'url', [
            'instructions' => 'Odnośniki prowadzące pod ten adres otwierają okno rezerwacji zamiast nowej karty.',
        ]),
        f('opt.booking_title', 'Tytuł okna rezerwacji', 'text'),

        f('opt.tab_reviews', 'Opinie Google', 'tab'),
        f('opt.google_api_key', 'Klucz API Google', 'text', [
            'instructions' => 'Klucz z włączonym „Places API (New)”. Używany tylko po stronie serwera.',
        ]),
        f('opt.google_place_id', 'Identyfikator wizytówki (Place ID)', 'text'),
        f('opt.google_reviews_sync', 'Pobieranie', 'message', [
            'message' => '',
            'new_lines' => '',
            'esc_html' => 0,
        ]),

        f('opt.tab_blog', 'Blog', 'tab'),
        f('opt.author_role', 'Podpis autora w nagłówku wpisu', 'text', [
            'instructions' => 'Dopisek po imieniu autora, np. „księgowa Taxeon”.',
        ]),
        f('opt.article_cta_title', 'Karta w artykule — tytuł', 'text'),
        f('opt.article_cta_text', 'Karta w artykule — opis', 'textarea', ['rows' => 2]),
        f('opt.article_cta_link', 'Karta w artykule — przycisk', 'link'),

        f('opt.tab_scripts', 'Skrypty', 'tab'),
        f('opt.scripts_head', 'Skrypty w <head>', 'textarea', ['rows' => 6, 'new_lines' => '']),
        f('opt.scripts_body', 'Skrypty po <body>', 'textarea', ['rows' => 6, 'new_lines' => '']),
    ],
];

$groups['category'] = [
    'title' => 'Ustawienia kategorii',
    'location' => [
        [['param' => 'taxonomy', 'operator' => '==', 'value' => 'category']],
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
        f('blog.archive_badge', 'Plakietka nad nagłówkiem', 'text'),
        f('blog.archive_cta_title', 'Pas na dole — tytuł', 'text'),
        f('blog.archive_cta_text', 'Pas na dole — opis', 'textarea', ['rows' => 2]),
        f('blog.archive_cta_link', 'Pas na dole — przycisk', 'link'),
    ],
];

$blocks = [];

$blocks['hero'] = [
    'title' => 'Blok: Hero',
    'fields' => [
        f('hero.layout', 'Układ', 'select', [
            'instructions' => 'Decyduje, co pokazujemy obok tekstu: karty, panel boczny, sam tekst albo układ wyśrodkowany.',
            'choices' => [
                'cards' => 'Karty pod tekstem (strona główna)',
                'split' => 'Dwie kolumny z panelem (podstrony)',
                'plain' => 'Sam tekst (strony branżowe)',
                'center' => 'Wyśrodkowany (strona dla biur)',
            ],
            'default_value' => 'cards',
            'allow_null' => 0,
        ]),
        f('hero.title_size', 'Rozmiar tytułu', 'select', [
            'choices' => ['' => 'Jak w układzie', '62' => '62 px', '64' => '64 px', '66' => '66 px'],
            'default_value' => '', 'allow_null' => 0,
            'wrapper' => ['width' => '25', 'class' => '', 'id' => ''],
        ]),
        f('hero.subtitle_size', 'Rozmiar podtytułu', 'select', [
            'conditional_logic' => [[['field' => key_for('hero.layout'), 'operator' => '==', 'value' => 'cards']]],
            'choices' => ['' => 'Jak w układzie', '26' => '26 px', '28' => '28 px'],
            'default_value' => '', 'allow_null' => 0,
            'wrapper' => ['width' => '25', 'class' => '', 'id' => ''],
        ]),
        f('hero.text_gap', 'Odstęp nad opisem', 'select', [
            'choices' => ['' => 'Jak w układzie', '22' => '22 px', '24' => '24 px', '26' => '26 px'],
            'default_value' => '', 'allow_null' => 0,
            'wrapper' => ['width' => '25', 'class' => '', 'id' => ''],
        ]),
        f('hero.text_width', 'Szerokość opisu', 'select', [
            'choices' => ['' => 'Jak w układzie', '56ch' => '56 znaków', '60ch' => '60 znaków', '62ch' => '62 znaki'],
            'default_value' => '', 'allow_null' => 0,
            'wrapper' => ['width' => '25', 'class' => '', 'id' => ''],
        ]),
        f('hero.text_tone', 'Jasność opisu', 'select', [
            'choices' => ['' => 'Jak w układzie', 'soft' => 'Przygaszony (76%)', 'strong' => 'Mocniejszy (78%)'],
            'default_value' => '', 'allow_null' => 0,
            'wrapper' => ['width' => '25', 'class' => '', 'id' => ''],
        ]),
        f('hero.badge', 'Plakietka nad tytułem', 'text', [
            'instructions' => 'Krótki dopisek nad tytułem, np. „Biuro rachunkowe online · 14 lat na rynku”.',
        ]),
        textGroup('hero.title', 'Tytuł', TAGS_HEADING, 'h1', 'textarea'),
        f('hero.subtitle', 'Podtytuł', 'text', [
            'instructions' => 'Jedno zdanie pod tytułem, większą czcionką.',
        ]),
        f('hero.text', 'Opis', 'textarea', ['rows' => 3]),
        f('hero.show_breadcrumbs', 'Pokaż okruszki', 'true_false', [
            'instructions' => 'Ścieżka nawigacji nad plakietką.',
        ]),

        f('hero.chips', 'Pigułki pod opisem', 'repeater', [
            'button_label' => 'Dodaj pigułkę',
            'instructions' => 'Odnośniki do kotwic na tej samej stronie.',
            'collapsed' => key_for('hero.chips.link'),
            'sub_fields' => [f('hero.chips.link', 'Odnośnik', 'link', ['required' => 1])],
        ]),
        f('hero.button_style', 'Styl przycisków', 'select', [
            'instructions' => 'Akcent to duży niebieski przycisk, jasny to biała pigułka.',
            'choices' => ['light' => 'Biały z obrysowanym obok', 'accent' => 'Jeden duży w akcencie'],
            'default_value' => 'light',
            'allow_null' => 0,
        ]),
        f('hero.button_primary', 'Przycisk główny', 'link'),
        f('hero.button_secondary', 'Przycisk dodatkowy', 'link'),
        f('hero.trust', 'Rząd zaufania', 'repeater', [
            'button_label' => 'Dodaj pozycję',
            'instructions' => 'Pod przyciskami, w układzie dwukolumnowym.',
            'collapsed' => key_for('hero.trust.value'),
            'sub_fields' => [
                f('hero.trust.value', 'Wyróżnienie', 'text', [
                    'required' => 1,
                    'wrapper' => ['width' => '30', 'class' => '', 'id' => ''],
                ]),
                f('hero.trust.label', 'Opis', 'text', [
                    'wrapper' => ['width' => '50', 'class' => '', 'id' => ''],
                ]),
                f('hero.trust.stars', 'Gwiazdki', 'true_false', [
                    'wrapper' => ['width' => '20', 'class' => '', 'id' => ''],
                ]),
                f('hero.trust.url', 'Adres', 'url'),
            ],
        ]),

        f('hero.tab_media', 'Tło', 'tab'),
        f('hero.video', 'Wideo w tle', 'text', [
            'instructions' => 'Adres pliku MP4 albo filmu na YouTube. Puste = sam granat.',
        ]),
        f('hero.poster', 'Plakat wideo', 'image', [
            'instructions' => 'Pierwsza klatka, widoczna zanim wideo się wczyta.',
        ]),

        f('hero.tab_note', 'Ramka pod kartami', 'tab'),
        f('hero.note_title', 'Tytuł', 'text', [
            'conditional_logic' => [[['field' => key_for('hero.layout'), 'operator' => '==', 'value' => 'cards']]],
        ]),
        f('hero.note_text', 'Opis', 'text', [
            'conditional_logic' => [[['field' => key_for('hero.layout'), 'operator' => '==', 'value' => 'cards']]],
        ]),
        f('hero.note_link', 'Odnośnik', 'link', [
            'conditional_logic' => [[['field' => key_for('hero.layout'), 'operator' => '==', 'value' => 'cards']]],
        ]),

        f('hero.tab_stats', 'Pasek liczb', 'tab'),
        f('hero.stats', 'Liczby', 'repeater', [
            'button_label' => 'Dodaj pozycję',
            'collapsed' => key_for('hero.stats.value'),
            'sub_fields' => [
                f('hero.stats.value', 'Wartość', 'text', [
                    'required' => 1,
                    'wrapper' => ['width' => '40', 'class' => '', 'id' => ''],
                ]),
                f('hero.stats.highlight', 'Wyróżniona', 'true_false', [
                    'wrapper' => ['width' => '20', 'class' => '', 'id' => ''],
                ]),
                f('hero.stats.label', 'Podpis', 'text', [
                    'wrapper' => ['width' => '60', 'class' => '', 'id' => ''],
                ]),
                f('hero.stats.url', 'Adres', 'url'),
            ],
        ]),

        f('hero.tab_cards', 'Karty cennika', 'tab'),
        f('hero.checks', 'Hasła z ptaszkiem', 'repeater', [
            'instructions' => 'Rząd haseł pod przyciskiem — używany w układzie wyśrodkowanym.',
            'conditional_logic' => [[['field' => key_for('hero.layout'), 'operator' => '==', 'value' => 'center']]],
            'layout' => 'table',
            'button_label' => 'Dodaj hasło',
            'sub_fields' => [f('hero.checks.text', 'Treść', 'text', ['required' => 1])],
        ]),
        f('hero.space_bottom', 'Odstęp u dołu', 'select', [
            'choices' => ['' => 'Zależnie od układu', '72' => '72 px', '76' => '76 px', '84' => '84 px', '88' => '88 px'],
            'default_value' => '',
            'allow_null' => 0,
            'wrapper' => ['width' => '50', 'class' => '', 'id' => ''],
        ]),
        f('hero.cards', 'Karty', 'repeater', [
            'instructions' => 'W układzie „Karty” leżą pod tekstem, w układzie „Panel z boku” — obok niego.',
            'conditional_logic' => [
                [['field' => key_for('hero.layout'), 'operator' => '==', 'value' => 'cards']],
                [['field' => key_for('hero.layout'), 'operator' => '==', 'value' => 'split']],
            ],
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
                f('hero.cards.price_compact', 'Mniejsza cena (52 px)', 'true_false', [
                    'wrapper' => ['width' => '25', 'class' => '', 'id' => ''],
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
        f('features.size', 'Rozmiar kart', 'select', [
            'instructions' => 'Duże karty mają tytuł 34 px, małe 20 px i kolorowy pasek.',
            'choices' => ['lg' => 'Duże (tytuł 34 px)', 'md' => 'Średnie (tytuł 26 px)', 'sm' => 'Małe (tytuł 20 px, z paskiem)'],
            'default_value' => 'lg',
            'allow_null' => 0,
            'wrapper' => ['width' => '50', 'class' => '', 'id' => ''],
        ]),
        f('features.columns', 'Kolumny', 'select', [
            'instructions' => 'Na ile kolumn układamy karty.',
            'choices' => ['2' => 'Dwie', '3' => 'Trzy', '4' => 'Cztery'],
            'default_value' => '2',
            'allow_null' => 0,
            'wrapper' => ['width' => '50', 'class' => '', 'id' => ''],
        ]),
        f('features.items', 'Karty', 'repeater', [
            'instructions' => 'Karty w siatce. Wyróżniona karta dostaje jaśniejszy akcent.',
            'button_label' => 'Dodaj kartę',
            'collapsed' => key_for('features.items.title'),
            'sub_fields' => [
                f('features.items.kicker', 'Nadkreślenie', 'text', [
                    'wrapper' => ['width' => '40', 'class' => '', 'id' => ''],
                ]),
                f('features.items.highlight', 'Karta wyróżniona', 'true_false', [
                    'instructions' => 'Jaśniejsze nadkreślenie, opis i odnośnik na ciemnym tle.',
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
        f('latest_posts.link', 'Odnośnik pod opisem', 'link', [
            'instructions' => 'Np. „Zobacz wszystkie wpisy →”. Pojawia się pod opisem, tak jak w bloku FAQ.',
        ]),
        f('latest_posts.count', 'Ile wpisów', 'number', [
            'instructions' => 'Ile wpisów pobrać z bazy wiedzy.','default_value' => 6, 'min' => 1, 'max' => 12]),

        f('latest_posts.tab_banner', 'Baner', 'tab'),
        f('latest_posts.banner_title', 'Tytuł', 'text'),
        f('latest_posts.banner_text', 'Opis', 'textarea', ['rows' => 2]),
        f('latest_posts.banner_link', 'Przycisk', 'link'),
    ]),
];

$blocks['list'] = [
    'title' => 'Blok: Lista z nagłówkiem',
    'fields' => array_merge(sectionHead('list'), [
        f('list.link', 'Odnośnik pod opisem', 'link', [
            'instructions' => 'Odnośnik pod opisem po lewej stronie.',
        ]),
        f('list.note', 'Dopisek pod przyciskiem', 'text', [
            'instructions' => 'Drobny dopisek pod przyciskiem.',
        ]),
        f('list.head_position', 'Położenie nagłówka', 'select', [
            'choices' => ['left' => 'Po lewej', 'above' => 'Nad listą'],
            'default_value' => 'left',
            'allow_null' => 0,
            'wrapper' => ['width' => '50', 'class' => '', 'id' => ''],
        ]),
        f('list.link_style', 'Styl odnośnika', 'select', [
            'choices' => ['text' => 'Tekstowy', 'button' => 'Przycisk'],
            'default_value' => 'text',
            'allow_null' => 0,
            'wrapper' => ['width' => '50', 'class' => '', 'id' => ''],
        ]),
        f('list.columns_ratio', 'Proporcje kolumn', 'select', [
            'choices' => [
                '' => 'Standardowe (0,85 / 1,15)',
                '1-0.9' => 'Szerszy opis (1 / 0,9)',
                '0.85-1.3' => 'Szersza lista (0,85 / 1,3)',
                '0.8-1.2' => 'Szersza lista (0,8 / 1,2)',
                '0.7-1.3' => 'Wąski opis (0,7 / 1,3)',
                '0.6-1.4' => 'Wąski opis (0,6 / 1,4)',
            ],
            'default_value' => '',
            'allow_null' => 0,
            'wrapper' => ['width' => '50', 'class' => '', 'id' => ''],
        ]),
        f('list.columns', 'Kolumny listy', 'select', [
            'instructions' => 'Na ile kolumn rozbić listę po prawej.',
            'choices' => ['1' => 'Jedna', '2' => 'Dwie', '3' => 'Trzy'],
            'default_value' => '1',
            'allow_null' => 0,
            'wrapper' => ['width' => '50', 'class' => '', 'id' => ''],
        ]),
        f('list.row_style', 'Układ wiersza', 'select', [
            'instructions' => 'Wygląd pojedynczego wiersza listy.',
            'choices' => [
                'inline' => 'Opis i wartość w jednej linii',
                'numbered' => 'Numer i treść',
                'stacked' => 'Tytuł nad opisem',
                'icon' => 'Kafelki z ikoną',
                'check' => 'Karty z ptaszkiem',
            ],
            'default_value' => 'inline',
            'allow_null' => 0,
            'wrapper' => ['width' => '50', 'class' => '', 'id' => ''],
        ]),
        f('list.row_size', 'Rozmiar wiersza', 'select', [
            'choices' => ['' => 'Standardowy', 'sm' => 'Ciaśniejszy (tytuł 19 px)'],
            'default_value' => '', 'allow_null' => 0,
            'wrapper' => ['width' => '50', 'class' => '', 'id' => ''],
        ]),
        f('list.items', 'Wiersze', 'repeater', [
            'instructions' => 'Wiersze listy. Pola „wartość” używamy w układzie z ceną po prawej.',
            'button_label' => 'Dodaj wiersz',
            'collapsed' => key_for('list.items.label'),
            'sub_fields' => [
                f('list.items.label', 'Opis', 'text', [
                    'required' => 1,
                    'wrapper' => ['width' => '70', 'class' => '', 'id' => ''],
                ]),
                f('list.items.value', 'Wartość albo numer', 'text', [
                    'wrapper' => ['width' => '30', 'class' => '', 'id' => ''],
                ]),
                f('list.items.tone', 'Kolor wartości', 'select', [
                    'choices' => ['default' => 'Zwykły', 'muted' => 'Przygaszony', 'free' => 'Wyróżniony'],
                    'default_value' => 'default',
                    'allow_null' => 0,
                ]),
                f('list.items.text', 'Opis pod tytułem', 'textarea', ['rows' => 2]),
            ],
        ]),
    ]),
];

$blocks['panels'] = [
    'title' => 'Blok: Panele',
    'fields' => array_merge(sectionHead('panels'), [
        f('panels.items', 'Panele', 'repeater', [
            'instructions' => 'Dwa lub trzy panele obok siebie. Każdy ma własny wariant kolorystyczny i listę.',
            'button_label' => 'Dodaj panel',
            'max' => 2,
            'collapsed' => key_for('panels.items.title'),
            'sub_fields' => [
                f('panels.items.title', 'Tytuł', 'text', [
                    'required' => 1,
                    'wrapper' => ['width' => '75', 'class' => '', 'id' => ''],
                ]),
                f('panels.items.variant', 'Wygląd', 'select', [
                    'choices' => [
                        '' => 'Dopasuj do tła sekcji',
                        'glass' => 'Szklany (na ciemnym)',
                        'muted' => 'Przyciemniony',
                        'light' => 'Biały',
                        'dark' => 'Granatowy',
                        'positive' => 'Biały z zielonym akcentem',
                        'negative' => 'Biały z czerwonym akcentem',
                    ],
                    'default_value' => '',
                    'allow_null' => 0,
                    'wrapper' => ['width' => '25', 'class' => '', 'id' => ''],
                ]),
                f('panels.items.list_style', 'Styl listy', 'select', [
                    'choices' => [
                        'check' => 'Ptaszki',
                        'check-lg' => 'Ptaszki większe (17 px)',
                        'dash' => 'Myślniki (wykluczenia)',
                        'rows' => 'Wiersze z kreską',
                    ],
                    'default_value' => 'check',
                    'allow_null' => 0,
                ]),
                f('panels.items.links', 'Przyciski na dole', 'repeater', [
                    'layout' => 'table',
                    'button_label' => 'Dodaj przycisk',
                    'max' => 2,
                    'sub_fields' => [f('panels.items.links.link', 'Odnośnik', 'link', ['required' => 1])],
                ]),
                f('panels.items.items', 'Punkty', 'repeater', [
                    'button_label' => 'Dodaj punkt',
                    'collapsed' => key_for('panels.items.items.text'),
                    'sub_fields' => [f('panels.items.items.text', 'Treść', 'text', ['required' => 1])],
                ]),
                f('panels.items.eyebrow', 'Nadkreślenie', 'text'),
                f('panels.items.text', 'Akapit pod tytułem', 'textarea', ['rows' => 3]),
                f('panels.items.note', 'Notka na dole', 'textarea', ['rows' => 2]),
            ],
        ]),
    ]),
];

$blocks['reviews'] = [
    'title' => 'Blok: Opinie',
    'fields' => array_merge(sectionHead('reviews'), [
        f('reviews.link', 'Odnośnik do wizytówki', 'link', [
            'instructions' => 'Przycisk obok nagłówka, prowadzi do wizytówki Google.',
        ]),
        f('reviews.source', 'Skąd opinie', 'select', [
            'instructions' => 'Google udostępnia najwyżej pięć opinii z wizytówki i sam wybiera, które to będą. '
                . 'Żeby pokazać więcej, wybierz „Z Google i dopisane ręcznie” i dopisz pozostałe poniżej.',
            'choices' => [
                'google' => 'Z profilu Google',
                'both' => 'Z Google i dopisane ręcznie',
                'manual' => 'Tylko wpisane ręcznie',
            ],
            'default_value' => 'google',
            'allow_null' => 0,
        ]),
        f('reviews.google_items', 'Które opinie z Google pokazać', 'checkbox', [
            'instructions' => 'Lista pochodzi z ostatniego pobrania. Bez zaznaczenia pokazujemy wszystkie.',
            'choices' => [],
            'conditional_logic' => [[['field' => key_for('reviews.source'), 'operator' => '!=', 'value' => 'manual']]],
        ]),
        f('reviews.limit', 'Maksymalna liczba kart z Google', 'number', [
            'instructions' => 'Ile opinii z Google wziąć do sekcji; dopisane ręcznie dochodzą do nich. '
                . 'Widoczne są zawsze cztery karty — powyżej czterech sekcja zamienia się w karuzelę ze strzałkami.',
            'default_value' => 8, 'min' => 1, 'max' => 12,
            'conditional_logic' => [[['field' => key_for('reviews.source'), 'operator' => '!=', 'value' => 'manual']]],
        ]),
        f('reviews.items', 'Opinie dopisane ręcznie', 'repeater', [
            'instructions' => 'Przepisz opinię z wizytówki albo wklej tę, którą klient przysłał mailem.',
            'conditional_logic' => [[['field' => key_for('reviews.source'), 'operator' => '!=', 'value' => 'google']]],
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

$blocks['tabs'] = [
    'title' => 'Blok: Zakładki',
    'fields' => array_merge(
        [f('tabs.tab_content', 'Treść', 'tab')],
        [
            textGroup('tabs.eyebrow', 'Nadkreślenie', TAGS_EYEBROW, 'span'),
            textGroup('tabs.title', 'Tytuł', TAGS_HEADING, 'h2'),
            ...headSizeFields('tabs'),
            f('tabs.items', 'Zakładki', 'repeater', [
            'instructions' => 'Każda zakładka to jeden krok: przycisk po lewej i opis po prawej.',
                'button_label' => 'Dodaj zakładkę',
                'collapsed' => key_for('tabs.items.title'),
                'sub_fields' => [
                    f('tabs.items.title', 'Tytuł', 'text', [
                        'required' => 1,
                        'wrapper' => ['width' => '50', 'class' => '', 'id' => ''],
                    ]),
                    f('tabs.items.hint', 'Podpis na przycisku', 'text', [
                        'wrapper' => ['width' => '50', 'class' => '', 'id' => ''],
                    ]),
                    f('tabs.items.step', 'Krok', 'text', [
                        'wrapper' => ['width' => '30', 'class' => '', 'id' => ''],
                    ]),
                    f('tabs.items.text', 'Opis', 'textarea', ['rows' => 4]),
                    f('tabs.items.points', 'Punkty', 'repeater', [
                        'layout' => 'table',
                        'button_label' => 'Dodaj punkt',
                        'sub_fields' => [f('tabs.items.points.text', 'Treść', 'text', ['required' => 1])],
                    ]),
                ],
            ]),
        ]
    ),
];

$blocks['videos'] = [
    'title' => 'Blok: Filmy',
    'fields' => array_merge(sectionHead('videos'), [
        f('videos.columns', 'Kolumny', 'select', [
            'instructions' => 'Dwie kolumny to układ z makiety.',
            'choices' => ['2' => 'Dwie', '3' => 'Trzy'],
            'default_value' => '2',
            'allow_null' => 0,
        ]),
        f('videos.items', 'Filmy', 'repeater', [
            'instructions' => 'Wklej zwykły odnośnik do filmu na YouTube — miniaturę pobierzemy automatycznie.',
            'button_label' => 'Dodaj film',
            'collapsed' => key_for('videos.items.title'),
            'sub_fields' => [
                f('videos.items.url', 'Odnośnik YouTube', 'text', ['required' => 1]),
                f('videos.items.title', 'Tytuł', 'text', ['required' => 1]),
                f('videos.items.text', 'Opis', 'textarea', ['rows' => 2]),
            ],
        ]),
    ]),
];

$blocks['cta'] = [
    'title' => 'Blok: Pas CTA',
    'fields' => [
        textGroup('cta.title', 'Tytuł', TAGS_HEADING, 'h2', 'textarea'),
        f('cta.title_size', 'Rozmiar tytułu', 'select', [
            'choices' => [
                'lg' => 'Duży (strona główna)',
                'md' => 'Standardowy (podstrony)',
                'sm' => 'Mniejszy (FAQ)',
                'xs' => 'Najmniejszy (54 px)',
            ],
            'default_value' => 'md',
            'allow_null' => 0,
        ]),
        f('cta.text', 'Opis', 'textarea', [
            'instructions' => 'Jedno zdanie pod tytułem.','rows' => 2]),
        f('cta.button_primary', 'Przycisk główny', 'link'),
        f('cta.button_secondary', 'Przycisk dodatkowy', 'link'),
    ],
];

$blocks['timeline'] = [
    'title' => 'Blok: Kroki',
    'fields' => array_merge(sectionHead('timeline'), [
        f('timeline.items', 'Kroki', 'repeater', [
            'instructions' => 'Kroki w kolejności, numery wpisujesz ręcznie.',
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
        f('stats.items', 'Karty', 'repeater', [
            'instructions' => 'Liczba i podpis pod nią.',
            'button_label' => 'Dodaj kartę',
            'max' => 3,
            'collapsed' => key_for('stats.items.title'),
            'sub_fields' => [
                f('stats.items.value', 'Liczba', 'text', [
                    'required' => 1,
                    'wrapper' => ['width' => '30', 'class' => '', 'id' => ''],
                ]),
                f('stats.items.title', 'Tytuł', 'text', [
                    'required' => 1,
                    'wrapper' => ['width' => '50', 'class' => '', 'id' => ''],
                ]),
                f('stats.items.dark', 'Granatowa', 'true_false', [
                    'wrapper' => ['width' => '20', 'class' => '', 'id' => ''],
                ]),
                f('stats.items.text', 'Opis', 'textarea', ['rows' => 3]),
            ],
        ]),
    ]),
];

$blocks['pricing'] = [
    'title' => 'Blok: Cennik (jeden plan)',
    'fields' => array_merge(sectionHead('pricing'), [
        f('pricing.checks', 'Hasła nad kartą', 'repeater', [
            'instructions' => 'Hasła nad kartą z ceną.',
            'layout' => 'table',
            'button_label' => 'Dodaj hasło',
            'sub_fields' => [f('pricing.checks.text', 'Treść', 'text', ['required' => 1])],
        ]),
        f('pricing.plan_label', 'Nazwa planu', 'text', [
            'instructions' => 'Nazwa planu nad ceną, np. „KPiR & Ryczałt”.',
        ]),
        f('pricing.currency', 'Waluta', 'text', ['default_value' => 'zł']),
        f('pricing.periods', 'Okresy rozliczenia', 'repeater', [
            'instructions' => 'Okresy rozliczenia przełączane nad kartą. Zostaw jeden, żeby ukryć przełącznik.',
            'layout' => 'table',
            'button_label' => 'Dodaj okres',
            'sub_fields' => [
                f('pricing.periods.label', 'Nazwa', 'text', ['required' => 1]),
                f('pricing.periods.price', 'Cena', 'text', ['required' => 1]),
                f('pricing.periods.note', 'Dopisek pod ceną', 'text'),
            ],
        ]),
        f('pricing.items', 'Punkty', 'repeater', [
            'instructions' => 'Lista z ptaszkami wewnątrz karty.',
            'layout' => 'table',
            'button_label' => 'Dodaj punkt',
            'sub_fields' => [f('pricing.items.text', 'Treść', 'text', ['required' => 1])],
        ]),
        f('pricing.link', 'Przycisk', 'link', [
            'instructions' => 'Przycisk na dole karty.',
        ]),
        f('pricing.note', 'Dopisek pod przyciskiem', 'text', [
            'instructions' => 'Dopisek pod przyciskiem.',
        ]),
    ]),
];

$blocks['table'] = [
    'title' => 'Blok: Porównanie',
    'fields' => array_merge(sectionHead('table'), [
        f('table.head_position', 'Położenie nagłówka', 'select', [
            'choices' => ['left' => 'Po lewej', 'above' => 'Nad tabelą'],
            'default_value' => 'left',
            'allow_null' => 0,
        ]),
        f('table.note', 'Ramka z uwagą', 'wysiwyg', [
            'instructions' => 'Pod opisem, z pionową kreską z lewej. Puste = bez ramki.',
        ]),

        f('table.tab_table', 'Tabela', 'tab'),
        f('table.cta_label', 'Opis wiersza z przyciskami', 'text', [
            'instructions' => 'Tekst w pierwszej kolumnie ostatniego wiersza, np. „Wybierz pakiet”.',
        ]),
        f('table.head_label', 'Nagłówek kolumny opisów', 'text', [
            'default_value' => 'Co dostajesz',
            'wrapper' => ['width' => '34', 'class' => '', 'id' => ''],
        ]),
        f('table.columns', 'Kolumny', 'repeater', [
            'button_label' => 'Dodaj kolumnę',
            'max' => 4,
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
                f('table.columns.cta', 'Przycisk pod tabelą', 'link', [
                    'instructions' => 'Pojawia się w ostatnim wierszu, w kolumnie tej usługi.',
                ]),
            ],
        ]),
        f('table.rows', 'Wiersze', 'repeater', [
            'button_label' => 'Dodaj wiersz',
            'collapsed' => key_for('table.rows.label'),
            'sub_fields' => [
                f('table.rows.label', 'Opis', 'text', ['required' => 1]),
                f('table.rows.values', 'Wartości', 'repeater', [
                    'layout' => 'table',
                    'button_label' => 'Dodaj wartość',
                    'max' => 4,
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
        f('faq.link', 'Odnośnik pod opisem', 'link', [
            'instructions' => 'Odnośnik pod opisem, np. do pełnej listy pytań.',
        ]),
        f('faq.open_first', 'Rozwiń pierwsze pytanie', 'true_false', [
            'instructions' => 'Pierwsze pytanie otwarte od razu — tak jest w makiecie.',
        ]),
        f('faq.items', 'Pytania', 'repeater', [
            'button_label' => 'Dodaj pytanie',
            'instructions' => 'Pojedyncza lista. Jeśli wypełnisz grupy niżej, ta lista jest pomijana.',
            'collapsed' => key_for('faq.items.question'),
            'sub_fields' => [
                f('faq.items.question', 'Pytanie', 'text', ['required' => 1]),
                f('faq.items.answer', 'Odpowiedź', 'wysiwyg', ['required' => 1]),
            ],
        ]),

        f('faq.tab_groups', 'Grupy', 'tab'),
        f('faq.groups', 'Grupy pytań', 'repeater', [
            'button_label' => 'Dodaj grupę',
            'collapsed' => key_for('faq.groups.title'),
            'sub_fields' => [
                f('faq.groups.anchor', 'Kotwica', 'text', [
                    'instructions' => 'Bez krzyżyka, np. cena.',
                    'wrapper' => ['width' => '30', 'class' => '', 'id' => ''],
                ]),
                f('faq.groups.title', 'Tytuł grupy', 'text', [
                    'required' => 1,
                    'wrapper' => ['width' => '45', 'class' => '', 'id' => ''],
                ]),
                f('faq.groups.eyebrow', 'Nadkreślenie', 'text', [
                    'default_value' => 'Kategoria',
                    'wrapper' => ['width' => '25', 'class' => '', 'id' => ''],
                ]),
                f('faq.groups.items', 'Pytania', 'repeater', [
                    'button_label' => 'Dodaj pytanie',
                    'collapsed' => key_for('faq.groups.items.question'),
                    'sub_fields' => [
                        f('faq.groups.items.question', 'Pytanie', 'text', ['required' => 1]),
                        f('faq.groups.items.answer', 'Odpowiedź', 'wysiwyg', ['required' => 1]),
                    ],
                ]),
            ],
        ]),
    ]),
];

const LOOK_FIELDS = [
    'head_size', 'head_width', 'title_width', 'text_width', 'text_gap',
    'title_size', 'subtitle_size', 'text_tone', 'space_bottom',
    'columns_ratio', 'head_position', 'link_style', 'row_size',
];

foreach ($blocks as $slug => $block) {
    $prefix = str_replace('-', '_', $slug);

    $fields = $block['fields'];

    if (($fields[0]['type'] ?? '') !== 'tab') {
        array_unshift($fields, f($prefix . '.tab_content', 'Treść', 'tab'));
    }

    // pola czysto wizualne idą do osobnej zakładki, żeby „Treść” została krótka
    $tresc = [];
    $wyglad = [];

    foreach ($fields as $pole) {
        if (in_array($pole['name'], LOOK_FIELDS, true)) {
            $wyglad[] = $pole;

            continue;
        }

        $tresc[] = $pole;
    }

    if ($wyglad) {
        $wyglad = array_merge([f($prefix . '.tab_look', 'Wygląd', 'tab', [
            'instructions' => '',
        ])], $wyglad);
    }

    $groups['block-' . $slug] = [
        'title' => $block['title'],
        'location' => [[['param' => 'block', 'operator' => '==', 'value' => 'acf/' . $slug]]],
        'fields' => array_merge($tresc, $wyglad, sectionSettings($prefix)),
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
