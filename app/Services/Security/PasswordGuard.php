<?php

namespace App\Services\Security;

use App\Support\RateLimiter;
use WP_Error;
use WP_User;

class PasswordGuard
{
    const MIN_LENGTH = 8;
    const RESET_TTL = HOUR_IN_SECONDS;
    const REQUESTS_PER_IP = 10;
    const REQUESTS_PER_ACCOUNT = 3;
    const RESETS_PER_IP = 10;

    public static function boot(): void
    {
        add_filter('password_reset_expiration', static fn() => self::RESET_TTL);
        add_action('lostpassword_post', [self::class, 'limitRequests'], 5, 2);
        add_action('validate_password_reset', [self::class, 'limitResets'], 5, 2);
        add_action('validate_password_reset', [self::class, 'validateReset'], 10, 2);
        add_action('woocommerce_save_account_details_errors', [self::class, 'validateAccount']);
        add_filter('woocommerce_registration_errors', [self::class, 'validateRegistration']);
        add_action('user_profile_update_errors', [self::class, 'validateProfile'], 10, 3);
        add_action('after_password_reset', [self::class, 'afterReset']);
        add_action('woocommerce_customer_reset_password', [self::class, 'afterWooReset']);
        add_action('woocommerce_save_account_details', [self::class, 'afterAccountChange']);
        add_action('profile_update', [self::class, 'afterProfileChange'], 10, 2);
    }

    public static function problems(string $password): array
    {
        $problems = [];

        if (mb_strlen($password) < self::MIN_LENGTH) {
            $problems['weak_password_length'] = sprintf(__('The password must be at least %d characters long.', 'taxeon'), self::MIN_LENGTH);
        }

        if (!preg_match('/\p{L}/u', $password) || !preg_match('/\d/', $password) || !preg_match('/[^\p{L}\d]/u', $password)) {
            $problems['weak_password_format'] = __('The password must contain at least one letter, one digit and one special character.', 'taxeon');
        }

        return $problems;
    }

    public static function hint(): string
    {
        return sprintf(__('At least %d characters, including a letter, a digit and a special character.', 'taxeon'), self::MIN_LENGTH);
    }

    public static function limitRequests($errors, $userData = false): void
    {
        if (!$errors instanceof WP_Error) {
            return;
        }

        $subject = $userData instanceof WP_User
            ? 'user:' . $userData->ID
            : 'login:' . strtolower(trim((string) wp_unslash($_POST['user_login'] ?? '')));
        $ipAllowed = RateLimiter::hit('lostpw_ip', self::REQUESTS_PER_IP, HOUR_IN_SECONDS);
        $accountAllowed = RateLimiter::hit('lostpw_user', self::REQUESTS_PER_ACCOUNT, HOUR_IN_SECONDS, $subject);

        if (!$ipAllowed || !$accountAllowed) {
            $errors->add('taxeon_reset_limit', __('Too many password reset requests. Please try again in an hour.', 'taxeon'));
        }
    }

    public static function limitResets($errors, $user): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !$errors instanceof WP_Error) {
            return;
        }

        if (!RateLimiter::hit('resetpw_ip', self::RESETS_PER_IP, HOUR_IN_SECONDS)) {
            $errors->add('taxeon_reset_limit', __('Too many attempts. Please try again in an hour.', 'taxeon'));
        }
    }

    public static function validateReset($errors, $user): void
    {
        $password = $_POST['password_1'] ?? $_POST['pass1'] ?? '';

        if ($errors instanceof WP_Error && is_string($password) && $password !== '') {
            self::addProblems($errors, (string) wp_unslash($password));
        }
    }

    public static function validateAccount($errors): void
    {
        if ($errors instanceof WP_Error && !empty($_POST['password_1']) && is_string($_POST['password_1'])) {
            self::addProblems($errors, (string) wp_unslash($_POST['password_1']));
        }
    }

    public static function validateRegistration($errors)
    {
        if ($errors instanceof WP_Error && get_option('woocommerce_registration_generate_password') === 'no' && !empty($_POST['password']) && is_string($_POST['password'])) {
            self::addProblems($errors, (string) wp_unslash($_POST['password']));
        }

        return $errors;
    }

    public static function validateProfile($errors, $update, $user): void
    {
        if ($errors instanceof WP_Error && !empty($_POST['pass1']) && is_string($_POST['pass1'])) {
            self::addProblems($errors, (string) wp_unslash($_POST['pass1']));
        }
    }

    public static function afterReset($user): void
    {
        if (!$user instanceof WP_User) {
            return;
        }

        \WP_Session_Tokens::get_instance($user->ID)->destroy_all();
        TwoFactor::forgetDevices($user);
    }

    public static function afterWooReset($user): void
    {
        if (!$user instanceof WP_User) {
            return;
        }

        TwoFactor::forgetDevices($user);

        if (!TwoFactor::enabled() || !TwoFactor::requires($user)) {
            return;
        }

        \WP_Session_Tokens::get_instance($user->ID)->destroy_all();
        wp_clear_auth_cookie();
        wp_set_current_user(0);
    }

    public static function afterAccountChange($userId): void
    {
        if (empty($_POST['password_1'])) {
            return;
        }

        if ((int) $userId === get_current_user_id()) {
            wp_destroy_other_sessions();
        }

        TwoFactor::forgetDevices((int) $userId);
    }

    public static function afterProfileChange($userId, $oldUser): void
    {
        $user = get_userdata((int) $userId);

        if (!$user || !$oldUser instanceof WP_User || $user->user_pass === $oldUser->user_pass) {
            return;
        }

        if ((int) $userId === get_current_user_id()) {
            wp_destroy_other_sessions();
        } else {
            \WP_Session_Tokens::get_instance((int) $userId)->destroy_all();
        }

        TwoFactor::forgetDevices((int) $userId);
    }

    private static function addProblems(WP_Error $errors, string $password): void
    {
        foreach (self::problems($password) as $code => $message) {
            $errors->add($code, $message);
        }
    }
}
