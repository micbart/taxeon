<?php
/**
 * Przenosi ustawienia Complianz z RAKS-a i podmienia dane na taxeonowe.
 * Źródło: bin/dane-complianz.json (zrzut cmplz_options z instalacji RAKS).
 */

$plik = get_template_directory() . '/bin/dane-complianz.json';

if (!file_exists($plik)) {
    WP_CLI::error('Brak pliku bin/dane-complianz.json');
}

$wzor = json_decode((string) file_get_contents($plik), true);

if (!is_array($wzor)) {
    WP_CLI::error('Nie udało się odczytać ustawień wzorcowych.');
}

$taxeon = [
    'organisation_name' => 'TAXEON Sp. z o.o.',
    'address_company' => 'ul. Kamionkowska 51, 03-812 Warszawa',
    'country_company' => 'PL',
    'email_company' => 'kontakt@taxeon.pl',
    'telephone_company' => '515 033 505',
    'notifications_email_address' => 'kontakt@taxeon.pl',
    'cmplz_wsc_email' => 'kontakt@taxeon.pl',
    'gtm_code' => 'GTM-NXVZX4Q',
    'gtm_code_head' => 'yes',
    'enable_cookie_banner' => 'yes',
    'enable_cookie_blocker' => 'yes',
    'privacy-statement' => 'url',
    'cookie-statement' => 'generated',
];

$obecne = get_option('cmplz_options', []);
$nowe = array_merge(is_array($obecne) ? $obecne : [], $wzor, $taxeon);

// adres własnej polityki prywatności
$polityka = get_page_by_path('polityka-prywatnosci');

if ($polityka) {
    $nowe['privacy-statement-url'] = get_permalink($polityka);
}

update_option('cmplz_options', $nowe, false);
update_option('cmplz_detected_stats', ['google-tag-manager'], false);

WP_CLI::log('  organizacja: ' . $nowe['organisation_name']);
WP_CLI::log('  kontener GTM: ' . $nowe['gtm_code'] . ' (w <head>: ' . $nowe['gtm_code_head'] . ')');
WP_CLI::log('  region: ' . ($nowe['regions'] ?? '?') . ', bloker: ' . $nowe['enable_cookie_blocker']);
// baner zgód — Complianz trzyma go w osobnej tabeli
$baner = json_decode((string) file_get_contents(get_template_directory() . '/bin/dane-complianz-baner.json'), true);

if (is_array($baner)) {
    $polityka = get_page_by_path('polityka-prywatnosci');
    $cookies = get_page_by_path('polityka-cookies');

    foreach ($baner as $pole => $wartosc) {
        if (!is_string($wartosc)) {
            continue;
        }

        if ($polityka) {
            $baner[$pole] = preg_replace('#https?://[^"\']*pdf_polityka_prywatnosci\.pdf#', get_permalink($polityka), $baner[$pole]);
        }
    }

    global $wpdb;
    $tabela = $wpdb->prefix . 'cmplz_cookiebanners';
    unset($baner['ID']);

    $istnieje = (int) $wpdb->get_var("SELECT ID FROM {$tabela} LIMIT 1");

    if ($istnieje) {
        $wpdb->update($tabela, $baner, ['ID' => $istnieje]);
        WP_CLI::log('  baner zgód: zaktualizowany (#' . $istnieje . ')');
    } else {
        $wpdb->insert($tabela, $baner);
        WP_CLI::log('  baner zgód: utworzony (#' . $wpdb->insert_id . ')');
    }
}

// dokumenty prawne: Complianz trzyma typ w opcjach, a stronę w osobnym kluczu
$ustawienia = get_option('cmplz_options', []);

foreach (['privacy-statement' => 'polityka-prywatnosci', 'cookie-statement' => 'polityka-cookies'] as $typ => $slug) {
    $strona = get_page_by_path($slug);

    if (!$strona) {
        WP_CLI::warning("Brak strony {$slug} — Complianz nie będzie miał dokąd linkować.");

        continue;
    }

    $ustawienia[$typ] = 'custom';
    update_option('cmplz_' . $typ . '_custom_page', (int) $strona->ID, false);
    WP_CLI::log('  ' . $typ . ' → ' . get_permalink($strona));
}

update_option('cmplz_options', $ustawienia, false);

// wymuszenie przebudowy stylu banera
foreach (glob(WP_CONTENT_DIR . '/uploads/complianz/css/banner-*.css') ?: [] as $plik) {
    @unlink($plik);
}

update_option('cmplz_banner_version', (int) get_option('cmplz_banner_version') + 1, false);

// pamięć podręczna banera trzyma też tytuły dokumentów
global $wpdb;
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name = 'cmplz_transients'
    OR option_name LIKE '_transient_cmplz%' OR option_name LIKE '_transient_timeout_cmplz%'");
wp_cache_flush();
update_option('cmplz_wizard_completed_once', '1', false);
delete_transient('cmplz_cookiebanner_1');
wp_cache_flush();

WP_CLI::success('Ustawienia Complianz przeniesione z RAKS-a.');
