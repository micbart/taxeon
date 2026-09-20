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
                'navy' => 'Granatowe',
                'gradient' => 'Gradient (jak hero)',
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
            'choices' => ['default' => 'Standardowy', 'small' => 'Mały', 'none' => 'Brak'],
            'default_value' => 'default',
            'allow_null' => 0,
            'wrapper' => ['width' => '50', 'class' => '', 'id' => ''],
        ]),
        f($prefix . '.padding_bottom', 'Odstęp u dołu', 'select', [
            'choices' => ['default' => 'Standardowy', 'small' => 'Mały', 'none' => 'Brak'],
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
        f('opt.footer_description', 'Opis firmy', 'textarea', ['rows' => 4]),
        f('opt.phone_sales_label', 'Etykieta — sprzedaż', 'text', ['default_value' => 'Dział sprzedaży']),
        f('opt.phone_sales', 'Telefon — sprzedaż', 'text'),
        f('opt.phone_support_label', 'Etykieta — wsparcie', 'text', ['default_value' => 'Pomoc techniczna']),
        f('opt.phone_support', 'Telefon — wsparcie', 'text'),
        f('opt.support_link', 'Wsparcie — odnośnik', 'link', [
            'instructions' => 'Makieta 1.1 kieruje wsparcie do formularza zamiast do numeru. '
                . 'Wypełniony zastępuje telefon w tym wierszu, pusty zostawia telefon powyżej.',
        ]),
        f('opt.phone_hours', 'Godziny pracy', 'text', [
            'instructions' => 'Dopisek po numerze w stopce, np. „pon–pt 9:00–17:00”. Puste = bez dopisku.',
        ]),
        f('opt.email', 'E-mail', 'text'),
        f('opt.address', 'Adres', 'textarea', [
            'rows' => 2,
            'instructions' => 'Każdy wiersz to osobna linia w stopce — makieta 1.1 łamie adres po ulicy.',
        ]),
        f('opt.remote_help', 'Przycisk „Pomoc zdalna”', 'link'),
        f('opt.social_label', 'Podpis nad ikonami', 'text', ['default_value' => 'Obserwuj nas']),
        f('opt.social', 'Profile społecznościowe', 'repeater', [
            'button_label' => 'Dodaj profil',
            'instructions' => 'Kolejność ustawiasz przeciąganiem. Pusty adres pomija ikonę.',
            'collapsed' => key_for('opt.social.network'),
            'sub_fields' => [
                f('opt.social.network', 'Serwis', 'select', [
                    'required' => 1,
                    'allow_null' => 0,
                    'choices' => ['youtube' => 'YouTube', 'facebook' => 'Facebook', 'x' => 'Twitter / X'],
                    'wrapper' => ['width' => '40', 'class' => '', 'id' => ''],
                ]),
                f('opt.social.url', 'Adres profilu', 'url', [
                    'required' => 1,
                    'wrapper' => ['width' => '60', 'class' => '', 'id' => ''],
                ]),
            ],
        ]),
        f('opt.copyrights', 'Prawa autorskie', 'text'),
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
        f('hero.layout', 'Układ', 'select', [
            'choices' => [
                'center' => 'Wyśrodkowany (strona główna)',
                'left' => 'Do lewej (podstrony)',
            ],
            'default_value' => 'center',
            'allow_null' => 0,
            'wrapper' => ['width' => '60', 'class' => '', 'id' => ''],
        ]),
        f('hero.show_breadcrumbs', 'Pokaż okruszki', 'true_false', [
            'wrapper' => ['width' => '40', 'class' => '', 'id' => ''],
        ]),
        f('hero.badge_highlight', 'Plakietka — wyróżnienie', 'text', ['wrapper' => ['width' => '30', 'class' => '', 'id' => '']]),
        f('hero.badge_text', 'Plakietka — tekst', 'text', ['wrapper' => ['width' => '70', 'class' => '', 'id' => '']]),
        textGroup('hero.title', 'Tytuł', TAGS_HEADING, 'h1', 'textarea'),
        textGroup('hero.subtitle', 'Podtytuł', TAGS_TEXT, 'p', 'textarea'),
        f('hero.text_width', 'Szerokość tekstu', 'select', [
            'choices' => [
                'narrow' => 'Ograniczona (wyśrodkowany hero)',
                'full' => 'Pełna szerokość kolumny',
            ],
            'default_value' => 'narrow',
            'allow_null' => 0,
            'wrapper' => ['width' => '50', 'class' => '', 'id' => ''],
        ]),
        f('hero.tab_media', 'Tło', 'tab'),
        f('hero.media_type', 'Rodzaj tła', 'select', [
            'choices' => [
                'none' => 'Sam gradient',
                'video' => 'Wideo',
                'image' => 'Zdjęcie',
                'animation' => 'Animacja',
            ],
            'default_value' => 'none',
            'allow_null' => 0,
            'instructions' => 'Wideo i zdjęcie dostają przyciemnienie, żeby tekst był czytelny. Gradient i animacja go nie potrzebują.',
            'wrapper' => ['width' => '50', 'class' => '', 'id' => ''],
        ]),
        f('hero.animation', 'Rodzaj animacji', 'select', [
            'choices' => [
                'bands' => 'Pasma światła',
                'fog' => 'Mgła',
                'clouds' => 'Chmury',
                'cells' => 'Komórki',
                'net' => 'Siatka punktów',
                'birds' => 'Ptaki',
            ],
            'default_value' => 'bands',
            'allow_null' => 0,
            'instructions' => 'Liczona w przeglądarce. Na wąskich ekranach, przy oszczędzaniu danych i wyłączonych animacjach zostaje sam gradient.',
            'conditional_logic' => [[['field' => key_for('hero.media_type'), 'operator' => '==', 'value' => 'animation']]],
        ]),
        f('hero.video', 'Wideo — plik', 'file', [
            'instructions' => 'MP4 lub WebM. Na mobile nie jest pobierane.',
            'conditional_logic' => [[['field' => key_for('hero.media_type'), 'operator' => '==', 'value' => 'video']]],
        ]),
        f('hero.video_url', 'Wideo — adres zewnętrzny', 'text', [
            'instructions' => 'Adres MP4/WebM albo strumienia HLS (.m3u8). Ma pierwszeństwo przed plikiem.',
            'conditional_logic' => [[['field' => key_for('hero.media_type'), 'operator' => '==', 'value' => 'video']]],
        ]),
        f('hero.poster', 'Plakat / zdjęcie tła', 'image', [
            'instructions' => 'Widoczny zanim wczyta się wideo oraz na urządzeniach mobilnych.',
        ]),
        buttonGroup('hero.button_primary', 'Przycisk główny', 'on-navy', 'lg'),
        buttonGroup('hero.button_secondary', 'Przycisk drugi', 'ghost-navy', 'lg'),
        f('hero.trust', 'Pasek zaufania', 'repeater', [
            'button_label' => 'Dodaj punkt',
            'layout' => 'table',
            'sub_fields' => [f('hero.trust.text', 'Tekst', 'text', ['required' => 1])],
        ]),
    ],
];

$blocks['features'] = [
    'title' => 'Blok: Wyróżniki',
    'fields' => array_merge(sectionHead('features'), [
        f('features.layout', 'Układ', 'select', [
            'choices' => [
                'cards' => 'Kafle w ramkach',
                'cards-inline' => 'Kafle w ramkach, ikona obok tekstu',
                'list' => 'Lista z ikonami',
            ],
            'default_value' => 'cards',
            'allow_null' => 0,
            'wrapper' => ['width' => '50', 'class' => '', 'id' => ''],
        ]),
        f('features.columns', 'Kolumny', 'select', [
            'choices' => ['1' => '1', '2' => '2', '3' => '3', '4' => '4'],
            'default_value' => '4',
            'allow_null' => 0,
            'wrapper' => ['width' => '50', 'class' => '', 'id' => ''],
        ]),
        f('features.background_image', 'Zdjęcie tła', 'image', [
            'instructions' => 'Opcjonalne. Gdy ustawione, nagłówek sekcji przechodzi na jasny.',
        ]),
        f('features.items', 'Kafle', 'repeater', [
            'button_label' => 'Dodaj kafel',
            'collapsed' => key_for('features.items.title'),
            'sub_fields' => [
                icon('features.items.icon'),
                textGroup('features.items.title', 'Tytuł', TAGS_HEADING, 'h3'),
                textGroup('features.items.text', 'Opis', TAGS_TEXT, 'p', 'textarea'),
            ],
        ]),
    ]),
];

$blocks['latest-posts'] = [
    'title' => 'Blok: Najnowsze wpisy',
    'fields' => array_merge(sectionHead('latest'), [
        f('latest.link', 'Link „zobacz wszystkie”', 'link'),
        f('latest.source', 'Źródło wpisów', 'select', [
            'choices' => ['latest' => 'Najnowsze', 'category' => 'Z kategorii', 'manual' => 'Wybrane ręcznie'],
            'default_value' => 'latest',
            'allow_null' => 0,
        ]),
        f('latest.category', 'Kategoria', 'taxonomy', [
            'conditional_logic' => [[['field' => key_for('latest.source'), 'operator' => '==', 'value' => 'category']]],
        ]),
        f('latest.posts', 'Wpisy', 'relationship', [
            'conditional_logic' => [[['field' => key_for('latest.source'), 'operator' => '==', 'value' => 'manual']]],
        ]),
        f('latest.count', 'Liczba wpisów', 'number', [
            'default_value' => 3, 'min' => 1, 'max' => 12,
            'conditional_logic' => [[['field' => key_for('latest.source'), 'operator' => '!=', 'value' => 'manual']]],
        ]),
    ]),
];

$blocks['cta'] = [
    'title' => 'Blok: Pas CTA',
    'fields' => [
        f('cta.variant', 'Wariant', 'select', [
            'choices' => ['light' => 'Jasny', 'dark' => 'Ciemny'],
            'default_value' => 'light',
            'allow_null' => 0,
        ]),
        icon('cta.icon', 'Ikona (wariant jasny)'),
        textGroup('cta.title', 'Tytuł', TAGS_HEADING, 'h2'),
        textGroup('cta.text', 'Opis', TAGS_TEXT, 'p', 'textarea'),
        buttonGroup('cta.button_primary', 'Przycisk główny', 'primary', 'lg'),
        buttonGroup('cta.button_secondary', 'Przycisk drugi', 'ghost', 'lg'),
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
    'title' => 'Blok: Oś czasu',
    'fields' => array_merge(sectionHead('timeline'), [
        f('timeline.items', 'Wydarzenia', 'repeater', [
            'button_label' => 'Dodaj wydarzenie',
            'collapsed' => key_for('timeline.items.year'),
            'sub_fields' => [
                f('timeline.items.year', 'Rok / okres', 'text', ['required' => 1]),
                textGroup('timeline.items.title', 'Tytuł', TAGS_HEADING, 'h3'),
                textGroup('timeline.items.text', 'Opis', TAGS_TEXT, 'p', 'textarea'),
            ],
        ]),
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
    'title' => 'Blok: Tabela',
    'fields' => array_merge(sectionHead('table'), [
        f('table.head', 'Nagłówki kolumn', 'repeater', [
            'button_label' => 'Dodaj kolumnę',
            'layout' => 'table',
            'instructions' => 'Kolejność i liczba kolumn muszą zgadzać się z komórkami w wierszach.',
            'sub_fields' => [
                f('table.head.label', 'Nagłówek', 'text', ['wrapper' => ['width' => '60', 'class' => '', 'id' => '']]),
                f('table.head.hide_mobile', 'Ukryj na mobile', 'true_false', ['wrapper' => ['width' => '40', 'class' => '', 'id' => '']]),
            ],
        ]),
        f('table.variant', 'Odmiana', 'select', [
            'choices' => [
                'compare' => 'Porównanie (nagłówek na środku)',
                'pricing' => 'Cennik modułów',
                'downloads' => 'Lista do pobrania',
            ],
            'default_value' => 'compare',
            'allow_null' => 0,
            'instructions' => 'Każda odmiana ma własną typografię i odstępy z projektu.',
        ]),
        f('table.mode', 'Tryb', 'select', [
            'choices' => ['single' => 'Jedna tabela', 'tabs' => 'Zakładki'],
            'default_value' => 'single',
            'allow_null' => 0,
            'instructions' => 'W trybie zakładek nagłówki kolumn są wspólne, a każda zakładka ma własne wiersze.',
        ]),
        f('table.tabs', 'Zakładki', 'repeater', [
            'button_label' => 'Dodaj zakładkę',
            'collapsed' => key_for('table.tabs.label'),
            'conditional_logic' => [[['field' => key_for('table.mode'), 'operator' => '==', 'value' => 'tabs']]],
            'sub_fields' => [
                f('table.tabs.label', 'Etykieta', 'text', ['required' => 1]),
                f('table.tabs.rows', 'Wiersze', 'repeater', [
                    'button_label' => 'Dodaj wiersz',
                    'collapsed' => key_for('table.tabs.rows.cells'),
                    'sub_fields' => [
                        f('table.tabs.rows.cells', 'Komórki', 'repeater', [
                            'button_label' => 'Dodaj komórkę',
                            'sub_fields' => [
                                f('table.tabs.rows.cells.text', 'Treść', 'text', ['wrapper' => ['width' => '45', 'class' => '', 'id' => '']]),
                                icon('table.tabs.rows.cells.icon', 'Ikona', ['wrapper' => ['width' => '25', 'class' => '', 'id' => '']]),
                                f('table.tabs.rows.cells.style', 'Styl', 'select', [
                                    'choices' => ['normal' => 'Zwykły', 'plain' => 'Zwykły, bez pogrubienia', 'strong' => 'Wyróżniony', 'muted' => 'Wyszarzony', 'price' => 'Cena'],
                                    'default_value' => 'normal', 'allow_null' => 0,
                                    'wrapper' => ['width' => '30', 'class' => '', 'id' => ''],
                                ]),
                                f('table.tabs.rows.cells.suffix', 'Dopisek', 'text', ['wrapper' => ['width' => '50', 'class' => '', 'id' => '']]),
                                f('table.tabs.rows.cells.link', 'Link', 'link', ['wrapper' => ['width' => '50', 'class' => '', 'id' => '']]),
                            ],
                        ]),
                    ],
                ]),
            ],
        ]),
        f('table.rows', 'Wiersze', 'repeater', [
            'conditional_logic' => [[['field' => key_for('table.mode'), 'operator' => '==', 'value' => 'single']]],
            'button_label' => 'Dodaj wiersz',
            'collapsed' => key_for('table.rows.cells'),
            'sub_fields' => [
                f('table.rows.cells', 'Komórki', 'repeater', [
                    'button_label' => 'Dodaj komórkę',
                    'layout' => 'block',
                    'sub_fields' => [
                        f('table.rows.cells.text', 'Treść', 'text', [
                            'wrapper' => ['width' => '45', 'class' => '', 'id' => ''],
                        ]),
                        icon('table.rows.cells.icon', 'Ikona', [
                            'wrapper' => ['width' => '25', 'class' => '', 'id' => ''],
                        ]),
                        f('table.rows.cells.style', 'Styl', 'select', [
                            'choices' => [
                                'normal' => 'Zwykły',
                                'plain' => 'Zwykły, bez pogrubienia',
                                'strong' => 'Wyróżniony',
                                'muted' => 'Wyszarzony',
                                'price' => 'Cena',
                            ],
                            'default_value' => 'normal',
                            'allow_null' => 0,
                            'wrapper' => ['width' => '30', 'class' => '', 'id' => ''],
                        ]),
                        f('table.rows.cells.suffix', 'Dopisek', 'text', [
                            'instructions' => 'Mniejszy tekst obok treści, np. „/ mies.”.',
                            'wrapper' => ['width' => '50', 'class' => '', 'id' => ''],
                        ]),
                        f('table.rows.cells.link', 'Link', 'link', [
                            'wrapper' => ['width' => '50', 'class' => '', 'id' => ''],
                        ]),
                    ],
                ]),
            ],
        ]),
        f('table.footnote', 'Dopisek pod tabelą', 'text', [
            'wrapper' => ['width' => '50', 'class' => '', 'id' => ''],
        ]),
        f('table.footnote_link', 'Link w dopisku', 'link', [
            'wrapper' => ['width' => '50', 'class' => '', 'id' => ''],
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
    'title' => 'Blok: Pytania i odpowiedzi',
    'fields' => array_merge(sectionHead('faq'), [
        f('faq.align', 'Nagłówek sekcji', 'select', [
            'choices' => ['left' => 'Do lewej', 'center' => 'Wyśrodkowany'],
            'default_value' => 'left',
            'allow_null' => 0,
            'wrapper' => ['width' => '34', 'class' => '', 'id' => ''],
        ]),
        f('faq.columns', 'Kolumny', 'select', [
            'choices' => ['1' => '1', '2' => '2'],
            'default_value' => '1',
            'allow_null' => 0,
            'wrapper' => ['width' => '33', 'class' => '', 'id' => ''],
        ]),
        f('faq.single', 'Tylko jedna odpowiedź naraz', 'true_false', [
            'default_value' => 1,
            'wrapper' => ['width' => '33', 'class' => '', 'id' => ''],
        ]),
        icon('faq.icon', 'Ikona przy pytaniu', ['instructions' => 'Puste = „plus”. Ikona obraca się o 45° po rozwinięciu.']),
        f('faq.items', 'Pytania', 'repeater', [
            'button_label' => 'Dodaj pytanie',
            'collapsed' => key_for('faq.items.question'),
            'sub_fields' => [
                f('faq.items.question', 'Pytanie', 'text', ['required' => 1]),
                f('faq.items.answer', 'Odpowiedź', 'textarea', ['rows' => 3]),
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
