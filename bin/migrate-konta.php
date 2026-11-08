<?php

global $wpdb;

$stara = new wpdb('root', 'root', 'taxeon_old', 'localhost:/Applications/MAMP/tmp/mysql/mysql.sock');
$stara->set_prefix('wp_');
$stara->set_charset($stara->dbh, 'utf8mb4');

if (!empty($stara->error)) {
    WP_CLI::error('Brak połączenia z bazą taxeon_old');
}

$role = [];

foreach ($stara->get_results("SELECT user_id, meta_value FROM wp_usermeta WHERE meta_key = 'wp_capabilities'") as $wiersz) {
    $role[(int) $wiersz->user_id] = maybe_unserialize($wiersz->meta_value);
}

$imiona = [];

foreach ($stara->get_results("SELECT user_id, meta_key, meta_value FROM wp_usermeta WHERE meta_key IN ('first_name','last_name','nickname')") as $wiersz) {
    $imiona[(int) $wiersz->user_id][$wiersz->meta_key] = $wiersz->meta_value;
}

$konta = $stara->get_results("SELECT * FROM wp_users ORDER BY ID");

foreach ($konta as $konto) {
    $id = (int) $konto->ID;
    $kolizja = get_user_by('email', $konto->user_email) ?: get_user_by('login', $konto->user_login);

    if ($kolizja && $kolizja->ID !== $id) {
        WP_CLI::error("{$konto->user_login}: ten e-mail lub login ma już konto #{$kolizja->ID}");
    }

    $istniejace = get_userdata($id);

    if ($istniejace && $istniejace->user_email !== $konto->user_email) {
        WP_CLI::error("ID {$id} zajęte przez {$istniejace->user_email}");
    }

    $wpdb->delete($wpdb->users, ['ID' => $id]);
    $wpdb->delete($wpdb->usermeta, ['user_id' => $id]);

    $wpdb->insert($wpdb->users, [
        'ID' => $id,
        'user_login' => $konto->user_login,
        'user_pass' => $konto->user_pass,
        'user_nicename' => $konto->user_nicename,
        'user_email' => $konto->user_email,
        'user_url' => '',
        'user_registered' => $konto->user_registered,
        'user_status' => 0,
        'display_name' => $konto->display_name,
    ]);

    $uprawnienia = $role[$id] ?? ['editor' => true];

    $meta = [
        'wp_capabilities' => $uprawnienia,
        'wp_user_level' => isset($uprawnienia['administrator']) ? 10 : 7,
        'rich_editing' => 'true',
        'admin_color' => 'fresh',
        'show_admin_bar_front' => 'true',
        'locale' => '',
    ] + ($imiona[$id] ?? []);

    foreach ($meta as $klucz => $wartosc) {
        add_user_meta($id, $klucz, $wartosc);
    }

    WP_CLI::log(sprintf('  konto #%d %s (%s)', $id, $konto->user_login, implode(',', array_keys($uprawnienia))));
}

wp_cache_flush();

wp_update_user(['ID' => 2, 'display_name' => 'S. Hynowski', 'nickname' => 'S. Hynowski', 'user_nicename' => 's-hynowski']);

$przepisane = $wpdb->query("UPDATE {$wpdb->posts} SET post_author = 2 WHERE post_type IN ('page', 'post') AND post_status NOT IN ('auto-draft', 'inherit')");
WP_CLI::log("  strony i wpisy ({$przepisane}) przypisane do S. Hynowski (#2)");

$lokalny = get_user_by('email', 'admin@taxeon.local');

if ($lokalny) {
    $wpdb->update($wpdb->posts, ['post_author' => 2], ['post_author' => $lokalny->ID]);
    $wpdb->delete($wpdb->users, ['ID' => $lokalny->ID]);
    $wpdb->delete($wpdb->usermeta, ['user_id' => $lokalny->ID]);
    WP_CLI::log('  lokalne konto admin@taxeon.local usunięte');
}

update_option('options_author_role', '');
wp_cache_flush();

WP_CLI::success(sprintf('Konta przeniesione: %d z produkcji', count($konta)));
