<?php

namespace App\Services\Security;


class LoginUrl
{
    const SLUG = 'panel-57942e7e29';

    private static bool $loginRequest = false;
    private static bool $blockedRequest = false;

    public static function boot(): void
    {
        if (!defined('WP_CLI') || !WP_CLI) {
            self::classify();
            add_action('wp_loaded', [self::class, 'dispatch'], 1);
        }

        remove_action('template_redirect', 'wp_redirect_admin_locations', 1000);

        foreach (['site_url', 'network_site_url', 'wp_redirect', 'login_url', 'logout_url', 'lostpassword_url', 'register_url'] as $filter) {
            add_filter($filter, [self::class, 'rewrite'], 100);
        }

        add_filter('retrieve_password_message', [self::class, 'rewriteText'], 100);
        add_filter('wp_new_user_notification_email', [self::class, 'rewriteMail'], 100);
        add_filter('wp_new_user_notification_email_admin', [self::class, 'rewriteMail'], 100);
        add_filter('the_password_form', [self::class, 'rewriteText'], 100);
    }

    public static function slug(): string
    {
        $slug = defined('TAXEON_LOGIN_SLUG') ? (string) TAXEON_LOGIN_SLUG : self::SLUG;
        $slug = trim(sanitize_title($slug), '/');

        return $slug !== '' ? $slug : self::SLUG;
    }

    public static function url(string $query = ''): string
    {
        $url = home_url('/' . self::slug() . '/');

        return $query !== '' ? $url . '?' . ltrim($query, '?') : $url;
    }

    public static function isLoginRequest(): bool
    {
        return self::$loginRequest;
    }

    public static function rewrite($url)
    {
        if (!is_string($url) || !str_contains($url, 'wp-login.php')) {
            return $url;
        }

        [$base, $query] = array_pad(explode('?', $url, 2), 2, '');
        $base = (string) strtok($base, '#');

        if (!str_ends_with($base, 'wp-login.php')) {
            return $url;
        }

        return self::url($query);
    }

    public static function rewriteText($text)
    {
        if (!is_string($text) || !str_contains($text, 'wp-login.php')) {
            return $text;
        }

        return (string) preg_replace_callback(
            '#(?:https?://[^\s"\'<>]*?/)?wp-login\.php(?:\?[^\s"\'<>]*)?#',
            static fn(array $match): string => self::rewrite($match[0]),
            $text
        );
    }

    public static function rewriteMail($mail)
    {
        if (is_array($mail) && isset($mail['message'])) {
            $mail['message'] = self::rewriteText($mail['message']);
        }

        return $mail;
    }

    public static function dispatch(): void
    {
        if (self::$loginRequest) {
            self::serveLogin();
        }

        if (self::guestInAdmin()) {
            nocache_headers();
            nocache_headers();
            wp_safe_redirect(home_url('/404/'), 302);
            exit;
        }

        if (self::$blockedRequest && !is_admin()) {
            self::notFound();
        }
    }

    private static function classify(): void
    {
        $path = self::requestPath();
        $home = untrailingslashit((string) wp_parse_url(home_url('/'), PHP_URL_PATH));
        $relative = '/' . trim(substr($path, strlen($home)), '/');

        if (!str_starts_with($path, $home . '/') && $path !== $home) {
            return;
        }

        if ($relative === '/' . self::slug()) {
            self::$loginRequest = true;
            $GLOBALS['pagenow'] = 'wp-login.php';
            return;
        }

        if (in_array($relative, ['/wp-login.php', '/wp-login', '/login'], true) || str_ends_with($path, '/wp-login.php')) {
            self::$blockedRequest = true;
        }
    }

    private static function guestInAdmin(): bool
    {
        if (!is_admin() || wp_doing_ajax() || wp_doing_cron() || is_user_logged_in()) {
            return false;
        }

        if (wp_validate_auth_cookie('', 'logged_in') || wp_validate_auth_cookie('', is_ssl() ? 'secure_auth' : 'auth')) {
            return false;
        }

        return !in_array($GLOBALS['pagenow'] ?? '', ['admin-ajax.php', 'admin-post.php', 'async-upload.php'], true);
    }

    private static function serveLogin(): void
    {
        global $error, $interim_login, $action, $user_login;

        nocache_headers();
        nocache_headers();

        if (!headers_sent()) {
            header('X-Robots-Tag: noindex, nofollow', true);
        }

        $GLOBALS['pagenow'] = 'wp-login.php';

        require_once ABSPATH . 'wp-login.php';
        exit;
    }

    private static function notFound(): void
    {
        global $wp_query;

        nocache_headers();
        $GLOBALS['pagenow'] = 'index.php';
        $_SERVER['REQUEST_URI'] = home_url('/' . str_repeat('-/', 6), 'relative');
        $_GET = [];
        $_POST = [];
        $_REQUEST = [];

        if (!defined('WP_USE_THEMES')) {
            define('WP_USE_THEMES', true);
        }

        wp();
        $wp_query->set_404();
        status_header(404);
        nocache_headers();

        require_once ABSPATH . WPINC . '/template-loader.php';
        exit;
    }

    private static function requestPath(): string
    {
        $uri = (string) ($_SERVER['REQUEST_URI'] ?? '');
        $path = rawurldecode((string) strtok($uri, '?'));

        return '/' . ltrim((string) preg_replace('#/+#', '/', $path), '/');
    }
}
