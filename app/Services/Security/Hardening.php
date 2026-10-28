<?php

namespace App\Services\Security;

use WP_Error;

class Hardening
{
    public static function boot(): void
    {
        add_filter('rest_pre_dispatch', [self::class, 'guardUsersRoute'], 10, 3);
        add_filter('rest_endpoints', [self::class, 'restrictUserEndpoints']);
        add_action('template_redirect', [self::class, 'blockAuthorEnumeration'], 1);
        add_filter('oembed_response_data', [self::class, 'stripOembedAuthor']);
        add_filter('the_author', [self::class, 'publicAuthorName'], 99);
        add_filter('get_the_author_display_name', [self::class, 'publicAuthorName'], 99, 2);
        add_filter('xmlrpc_enabled', '__return_false');
        add_action('init', [self::class, 'blockXmlrpc'], 0);
        add_filter('authenticate', [self::class, 'genericLoginError'], 99);
        add_action('lostpassword_post', [self::class, 'hideUnknownAccount'], 10, 2);
        add_filter('wp_redirect', [self::class, 'evenLostPasswordTiming'], 1);
        add_filter('get_avatar_url', [self::class, 'anonymousAvatar']);
        add_filter('xmlrpc_methods', static fn(): array => []);
        add_filter('wp_headers', [self::class, 'stripPingbackHeader']);
        add_filter('the_generator', '__return_empty_string');
        remove_action('wp_head', 'wp_generator');
        remove_action('wp_head', 'rsd_link');
        add_action('send_headers', [self::class, 'securityHeaders']);
        add_action('login_init', [self::class, 'securityHeaders']);
        add_action('init', [self::class, 'earlyHeaders'], 0);
    }

    public static function earlyHeaders(): void
    {
        if (PHP_SAPI !== 'cli') {
            self::securityHeaders();
        }
    }

    public static function anonymousAvatar(string $url): string
    {
        if (is_admin() || !str_contains($url, 'gravatar.com')) {
            return $url;
        }

        return get_template_directory_uri() . '/dist/svg/user-round.svg';
    }

    public static function securityHeaders(): void
    {
        if (headers_sent()) {
            return;
        }

        header_remove('X-Powered-By');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(), microphone=(), usb=()');
    }

    public static function blockXmlrpc(): void
    {
        if (!defined('XMLRPC_REQUEST') || !XMLRPC_REQUEST) {
            return;
        }

        status_header(403);
        header('Content-Type: text/plain; charset=utf-8');
        exit('XML-RPC is disabled.');
    }

    public static function genericLoginError($user)
    {
        if (!is_wp_error($user) || !array_intersect($user->get_error_codes(), ['invalid_username', 'invalid_email', 'incorrect_password'])) {
            return $user;
        }

        return new WP_Error('invalid_login', __('<strong>Błąd:</strong> nieprawidłowa nazwa użytkownika lub hasło.', 'taxeon'));
    }

    public static function hideUnknownAccount(WP_Error $errors, $userData): void
    {
        if ($userData || trim((string) wp_unslash($_POST['user_login'] ?? '')) === '') {
            return;
        }

        $errors->remove('invalid_email');

        if ($errors->has_errors()) {
            return;
        }

        if (!empty($_POST['wc_reset_password']) && function_exists('wc_get_account_endpoint_url')) {
            wp_safe_redirect(add_query_arg('reset-link-sent', 'true', wc_get_account_endpoint_url('lost-password')));
            exit;
        }

        wp_safe_redirect(!empty($_REQUEST['redirect_to']) ? wp_unslash($_REQUEST['redirect_to']) : 'wp-login.php?checkemail=confirm');
        exit;
    }

    public static function evenLostPasswordTiming($location)
    {
        $lostPassword = ($GLOBALS['pagenow'] ?? '') === 'wp-login.php' && in_array($_REQUEST['action'] ?? '', ['lostpassword', 'retrievepassword'], true);

        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !($lostPassword || !empty($_POST['wc_reset_password']))) {
            return $location;
        }

        $elapsed = microtime(true) - (float) ($_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true));
        $floor = (float) apply_filters('taxeon_lost_password_min_seconds', 1.5);

        if ($elapsed < $floor) {
            usleep((int) (($floor - $elapsed) * 1000000));
        }

        return $location;
    }

    public static function guardUsersRoute(mixed $result, \WP_REST_Server $server, \WP_REST_Request $request): mixed
    {
        if (is_user_logged_in() || !preg_match('#^/wp/v2/users(/|$)#', $request->get_route())) {
            return $result;
        }

        return new WP_Error('rest_no_route', __('Nie znaleziono trasy pasującej do adresu i metody żądania.', 'taxeon'), ['status' => 404]);
    }

    public static function restrictUserEndpoints(array $endpoints): array
    {
        if (is_user_logged_in()) {
            return $endpoints;
        }

        foreach (array_keys($endpoints) as $route) {
            if (str_starts_with($route, '/wp/v2/users')) {
                unset($endpoints[$route]);
            }
        }

        return $endpoints;
    }

    public static function blockAuthorEnumeration(): void
    {
        if (is_user_logged_in() || is_admin()) {
            return;
        }

        if (!isset($_GET['author']) && !is_author()) {
            return;
        }

        wp_safe_redirect(home_url('/'), 301);
        exit;
    }

    public static function stripOembedAuthor(array $data): array
    {
        unset($data['author_name'], $data['author_url']);

        return $data;
    }

    public static function stripPingbackHeader(array $headers): array
    {
        unset($headers['X-Pingback']);

        return $headers;
    }

    public static function publicAuthorName($name, $userId = 0)
    {
        if (is_admin() || wp_doing_ajax() || (defined('REST_REQUEST') && REST_REQUEST)) {
            return $name;
        }

        $post = get_post();
        $authorId = (int) ($userId ?: ($post->post_author ?? 0));

        if ($authorId && trim((string) get_the_author_meta('description', $authorId)) !== '') {
            return $name;
        }

        return (string) get_bloginfo('name');
    }
}
