<?php

require __DIR__ . '/seed-helpers.php';

$stara = new wpdb('root', 'root', 'taxeon_old', 'localhost:/Applications/MAMP/tmp/mysql/mysql.sock');
$stara->set_prefix('wp_');
$stara->set_charset($stara->dbh, 'utf8mb4');

if (!empty($stara->error)) {
    WP_CLI::error('Brak połączenia z bazą taxeon_old');
}

function lf_napraw_serializacje(string $wartosc): string
{
    return (string) preg_replace_callback(
        '/s:\d+:"(.*?)";(?=[sibadNO]:|\}|$)/s',
        fn($m) => 's:' . strlen($m[1]) . ':"' . $m[1] . '";',
        $wartosc
    );
}

function lf_przywroc_polpauzy(mixed $wartosc): mixed
{
    if (is_array($wartosc)) {
        return array_map('lf_przywroc_polpauzy', $wartosc);
    }

    return is_string($wartosc) ? str_replace(' ? ', ' – ', $wartosc) : $wartosc;
}

$formularze = [
    3134 => 'formularz-pit-28',
    3140 => 'formularz-pit-36',
    3137 => 'formularz-pit-36l',
    4119 => 'formularz-pit-28-zewnetrzny',
    4120 => 'formularz-pit-36-zewnetrzny',
    4121 => 'formularz-pit-36l-zewnetrzny',
];

$noweId = [];

foreach ($formularze as $stareId => $slugStrony) {
    $post = $stara->get_row($stara->prepare("SELECT * FROM {$stara->posts} WHERE ID = %d", $stareId));
    $meta = $stara->get_results($stara->prepare("SELECT meta_key, meta_value FROM {$stara->postmeta} WHERE post_id = %d", $stareId), OBJECT_K);

    $istniejacy = get_posts([
        'post_type' => 'wpcf7_contact_form',
        'title' => $post->post_title,
        'post_status' => 'any',
        'numberposts' => 1,
    ]);

    $dane = [
        'post_type' => 'wpcf7_contact_form',
        'post_status' => 'publish',
        'post_title' => $post->post_title,
        'post_content' => $post->post_content,
    ];

    if ($istniejacy) {
        $dane['ID'] = $istniejacy[0]->ID;
    }

    $id = wp_insert_post(wp_slash($dane));
    $noweId[$stareId] = $id;

    foreach (['_form', '_mail', '_mail_2', '_messages', '_additional_settings', '_locale', '_hash'] as $klucz) {
        if (!isset($meta[$klucz])) {
            continue;
        }

        $surowa = $meta[$klucz]->meta_value;
        $wartosc = maybe_unserialize(lf_napraw_serializacje($surowa));

        if (is_string($wartosc) && is_serialized($surowa)) {
            WP_CLI::warning("{$post->post_title}: nie udało się odczytać {$klucz}");
            continue;
        }

        if (in_array($klucz, ['_form', '_mail', '_mail_2', '_messages'], true)) {
            $wartosc = lf_przywroc_polpauzy($wartosc);
        }

        update_post_meta($id, $klucz, wp_slash($wartosc));
    }

    $mail = get_post_meta($id, '_mail', true);
    WP_CLI::log(sprintf('  %s → #%d | do: %s | temat: %s', $post->post_title, $id, $mail['recipient'] ?? '?', $mail['subject'] ?? '?'));
}

$opcja = $stara->get_var("SELECT option_value FROM {$stara->options} WHERE option_name = 'wpcf7'");
$wpcf7 = maybe_unserialize(lf_napraw_serializacje((string) $opcja));

if (is_array($wpcf7) && !empty($wpcf7['recaptcha'])) {
    $obecna = get_option('wpcf7', []);
    $obecna['recaptcha'] = $wpcf7['recaptcha'];
    update_option('wpcf7', $obecna);
    WP_CLI::log('  klucze reCAPTCHA v3 przeniesione');
}

function lf_strona(string $tytul, string $slug, string $tresc): int
{
    $istnieje = get_page_by_path($slug);

    return (int) wp_insert_post(wp_slash([
        'ID' => $istnieje ? $istnieje->ID : 0,
        'post_type' => 'page',
        'post_status' => 'publish',
        'post_title' => $tytul,
        'post_name' => $slug,
        'post_content' => $tresc,
    ]));
}

$tytuly = [
    'formularz-pit-28' => 'Formularz PIT-28',
    'formularz-pit-36' => 'Formularz PIT-36',
    'formularz-pit-36l' => 'Formularz PIT-36L',
    'formularz-pit-28-zewnetrzny' => 'Formularz PIT-28',
    'formularz-pit-36-zewnetrzny' => 'Formularz PIT-36',
    'formularz-pit-36l-zewnetrzny' => 'Formularz PIT-36L',
];

foreach ($formularze as $stareId => $slug) {
    $tytulFormularza = get_the_title($noweId[$stareId]);
    $id = lf_strona($tytuly[$slug], $slug, sprintf('[contact-form-7 id="%d" title="%s"]', $noweId[$stareId], esc_attr($tytulFormularza)));
    update_post_meta($id, 'subtitle', str_contains($slug, 'zewnetrzny') ? 'Dla osób, które nie są klientami Taxeon' : 'Dla klientów Taxeon');
    WP_CLI::log("  strona /{$slug}/ #{$id}");
}

$przyciski = function (string $kod) {
    $klient = home_url("/formularz-{$kod}/");
    $zewnetrzny = home_url("/formularz-{$kod}-zewnetrzny/");

    return '<p class="pit-przyciski">'
        . '<a class="pit-przycisk" href="' . esc_url($klient) . '">Jestem klientem Taxeon</a> '
        . '<a class="pit-przycisk pit-przycisk--obrys" href="' . esc_url($zewnetrzny) . '">Nie jestem klientem Taxeon</a>'
        . '</p>';
};

$hub = '<p>Wybierz formularz dla swojego zeznania rocznego. Klienci Taxeon i osoby spoza Taxeon wypełniają osobne formularze.</p>'
    . '<h2 id="pit28">PIT-28</h2>' . $przyciski('pit-28')
    . '<h2 id="pit36">PIT-36</h2>' . $przyciski('pit-36')
    . '<h2 id="pit36l">PIT-36L</h2>' . $przyciski('pit-36l');

$id = lf_strona('Rozliczenie PIT', 'pit-rozliczenie', $hub);
update_post_meta($id, 'subtitle', 'Zlecenie przygotowania zeznania rocznego');
WP_CLI::log("  strona /pit-rozliczenie/ #{$id}");

$id = lf_strona('Rezygnacja z mailingu', 'unsubscribe', '<p>Twój adres e-mail został usunięty. Nie będziesz otrzymywać więcej wiadomości.</p>');
update_post_meta($id, 'rank_math_robots', ['noindex', 'nofollow']);
WP_CLI::log("  strona /unsubscribe/ #{$id} (noindex)");

$t = fn(string $x, string $tag = 'p'): array => ['text' => $x, 'tag' => $tag];
$l = fn(string $tytul, string $url, string $target = ''): array => ['title' => $tytul, 'url' => $url, 'target' => $target];
$pusty = ['title' => '', 'url' => '', 'target' => ''];

$hero = taxeon_seed_block('hero', [
    'layout' => 'plain', 'tone' => 'cream', 'button_style' => 'light',
    'badge' => 'Program poleceń dla Użytkowników TAXEON',
    'title' => $t('Polecaj znajomym i zarabiaj', 'h1'),
    'subtitle' => 'Do 200 zł netto za każde polecenie.',
    'text' => 'Znasz kogoś, kto prowadzi działalność gospodarczą? Poleć mu TAXEON — za usługi księgowe albo aplikację księgową otrzymasz wynagrodzenie.',
    'button_primary' => $l('Jak to działa →', '#jak'),
    'button_secondary' => $pusty,
    'trust' => [], 'cards' => [], 'stats' => [], 'video' => '',
]);

$kwoty = taxeon_seed_block('stats', [
    'items' => [
        ['value' => '200 zł netto', 'title' => 'Usługi księgowe', 'text' => 'Za każde polecenie naszych usług księgowych.', 'dark' => 1],
        ['value' => '100 zł netto', 'title' => 'Aplikacja księgowa', 'text' => 'Za każde polecenie naszego programu do samodzielnej księgowości.', 'dark' => 0],
    ],
    'background' => 'soft', 'padding_top' => 'medium', 'padding_bottom' => 'medium',
]);

$kroki = taxeon_seed_block('timeline', [
    'anchor' => 'jak',
    'eyebrow' => $t('Zasady programu', 'span'),
    'title' => $t('Jak to działa?', 'h2'),
    'text' => $t(''),
    'items' => [
        ['number' => '1', 'title' => 'Poleć TAXEON', 'featured' => 0, 'text' => 'Poleć TAXEON znajomemu prowadzącemu działalność gospodarczą.'],
        ['number' => '2', 'title' => 'Wyślij NIP znajomego', 'featured' => 0, 'text' => 'Wyślij na adres kontakt@taxeon.pl NIP firmy znajomego. Abonament musi zostać wykupiony w ciągu 3 miesięcy od zgłoszenia.'],
        ['number' => '3', 'title' => 'Usługi księgowe: 200 zł', 'featured' => 0, 'text' => 'Gdy znajomy opłaci pierwszy miesiąc usług księgowych, otrzymasz 200 zł netto — wystawiasz fakturę na TAXEON, a my ją opłacamy.'],
        ['number' => '4', 'title' => 'Aplikacja: 100 zł', 'featured' => 0, 'text' => 'Gdy znajomy opłaci pierwszy miesiąc abonamentu na aplikację księgową, otrzymasz 100 zł netto na tych samych zasadach.'],
    ],
    'cta_title' => 'Regulamin promocji',
    'cta_text' => 'Szczegółowe zasady programu poleceń znajdziesz w bazie wiedzy.',
    'cta_link' => $l('Więcej informacji i regulamin →', 'https://taxeon.pl/wiedza/system-polecen-taxeon-od-16-06-2025-r/', '_blank'),
    'background' => 'gradient',
]);

$cta = taxeon_seed_block('cta', [
    'title_size' => 'sm',
    'title' => $t('Twój znajomy się zastanawia?', 'h2'),
    'text' => 'Podaj mu link do bezpłatnego spotkania. Opowiemy, jak korzystać z TAXEON, jakie obowiązki ma przedsiębiorca i w jaki sposób pomożemy.',
    'button_primary' => $l('Link do spotkania →', home_url('/umow-spotkanie/')),
    'button_secondary' => $pusty,
    'background' => 'navy',
]);

$id = lf_strona('Polecaj znajomym', 'polecaj-znajomym', implode("\n\n", [$hero, $kwoty, $kroki, $cta]));
update_post_meta($id, 'rank_math_description', 'Program poleceń TAXEON: 200 zł netto za polecenie usług księgowych i 100 zł netto za polecenie aplikacji księgowej.');
WP_CLI::log("  strona /polecaj-znajomym/ #{$id}");

require __DIR__ . '/pit-formularze-uklad.php';

WP_CLI::success('Stare funkcje przeniesione');
