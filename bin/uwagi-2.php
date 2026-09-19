<?php

if (!defined('WP_CLI') || !WP_CLI) {
    exit("Uruchom przez: wp eval-file wp-content/themes/raks/bin/uwagi-2.php\n");
}

require_once __DIR__ . '/seed-helpers.php';

uwagi_edit_blocks(uwagi_page('aktualne-wersje'), 'pricing', static function (array $data): array {
    $data['variant'] = 'download';

    for ($i = 0; $i < (int) ($data['cards'] ?? 0); $i++) {
        if (($data["cards_{$i}_button_link"]['title'] ?? '') === 'Załóż konto') {
            $data["cards_{$i}_button_icon"] = '';
        }
    }

    return $data;
});

WP_CLI::log('  Aktualne wersje: karty pobierania');

WP_CLI::success('Uwagi runda 2 naniesione.');
