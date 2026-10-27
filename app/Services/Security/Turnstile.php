<?php

namespace App\Services\Security;

use App\Support\RateLimiter;
use WP_Error;

class Turnstile
{
    const SCRIPT = 'https://challenges.cloudflare.com/turnstile/v0/api.js';
    const VERIFY = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
    const FIELD = 'cf-turnstile-response';

    private static ?bool $verified = null;

    public static function boot(): void
    {
        if (!self::enabled()) {
            return;
        }

        add_filter('authenticate', [self::class, 'checkLogin'], 25, 2);
        add_filter('woocommerce_process_registration_errors', [self::class, 'checkRegistration']);
        add_action('lostpassword_post', [self::class, 'checkLostPassword'], 1);
        add_action('login_form', [self::class, 'printWidget']);
        add_action('lostpassword_form', [self::class, 'printWidget']);
        add_filter('script_loader_tag', [self::class, 'deferScript'], 10, 2);
    }

    public static function enabled(): bool
    {
        return self::siteKey() !== '' && self::secret() !== '';
    }

    public static function widget(): string
    {
        if (!self::enabled()) {
            return '';
        }

        wp_enqueue_script('cf-turnstile', self::SCRIPT, [], null, true);

        return '<div class="cf-turnstile" data-sitekey="' . esc_attr(self::siteKey()) . '" data-language="' . esc_attr(substr(determine_locale(), 0, 2)) . '" data-size="flexible" data-theme="light"></div>';
    }

    public static function printWidget(): void
    {
        echo self::widget();
    }

    public static function deferScript($tag, $handle)
    {
        return $handle === 'cf-turnstile' && !str_contains((string) $tag, ' defer') ? str_replace(' src=', ' async defer src=', (string) $tag) : $tag;
    }

    public static function checkLogin($user, $username)
    {
        if (!self::isFormPost() || trim((string) $username) === '' || self::verify()) {
            return $user;
        }

        return new WP_Error('taxeon_turnstile', self::message());
    }

    public static function checkRegistration($errors)
    {
        if ($errors instanceof WP_Error && !self::verify()) {
            $errors->add('taxeon_turnstile', self::message());
        }

        return $errors;
    }

    public static function checkLostPassword($errors): void
    {
        if ($errors instanceof WP_Error && self::isFormPost() && !self::verify()) {
            $errors->add('taxeon_turnstile', self::message());
        }
    }

    public static function verify(): bool
    {
        if (self::$verified !== null) {
            return self::$verified;
        }

        $token = (string) wp_unslash($_POST[self::FIELD] ?? '');

        if ($token === '' || strlen($token) > 2048) {
            return self::$verified = false;
        }

        $response = wp_remote_post(self::VERIFY, [
            'timeout' => 8,
            'body' => [
                'secret' => self::secret(),
                'response' => $token,
                'remoteip' => RateLimiter::clientIp(),
            ],
        ]);

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) >= 500) {
            error_log('TAXEON Turnstile: verification service unavailable, request allowed.');

            return self::$verified = true;
        }

        $body = json_decode((string) wp_remote_retrieve_body($response), true);

        return self::$verified = !empty($body['success']);
    }

    private static function isFormPost(): bool
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            return false;
        }

        return ($GLOBALS['pagenow'] ?? '') === 'wp-login.php'
            || isset($_POST['woocommerce-login-nonce'])
            || isset($_POST['woocommerce-lost-password-nonce']);
    }

    private static function message(): string
    {
        return __('Please confirm that you are not a robot and try again.', 'taxeon');
    }

    private static function siteKey(): string
    {
        return defined('TAXEON_TURNSTILE_SITE_KEY') ? trim((string) TAXEON_TURNSTILE_SITE_KEY) : '';
    }

    private static function secret(): string
    {
        return defined('TAXEON_TURNSTILE_SECRET') ? trim((string) TAXEON_TURNSTILE_SECRET) : '';
    }
}
