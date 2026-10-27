<?php

namespace App\Services\Security;

use App\Support\RateLimiter;
use WP_Error;
use WP_User;

class TwoFactor
{
    const ACTION = 'taxeon_2fa';
    const COOKIE = 'taxeon_2fa';
    const TRUST_COOKIE = 'taxeon_2fa_trust';
    const TRUST_META = '_taxeon_2fa_trusted';
    const PREFIX = 'taxeon_2fa_';
    const ROLES = ['administrator', 'shop_manager', 'editor'];
    const CODE_TTL = 10 * MINUTE_IN_SECONDS;
    const TRUST_DAYS = 7;
    const MAX_DEVICES = 10;
    const MAX_ATTEMPTS = 5;
    const MAX_SENDS = 3;
    const RESEND_DELAY = 30;

    public static function boot(): void
    {
        add_filter('authenticate', [self::class, 'challenge'], 100);
        add_action('login_form_' . self::ACTION, [self::class, 'screen']);
        add_filter('wp_is_application_passwords_available_for_user', [self::class, 'applicationPasswords'], 10, 2);
        add_action('login_init', [self::class, 'expiredNotice']);

        if (defined('WP_CLI') && WP_CLI) {
            \WP_CLI::add_command('taxeon 2fa forget-devices', [self::class, 'cliForgetDevices'], [
                'shortdesc' => 'Remove trusted devices of a user.',
                'synopsis' => [['type' => 'positional', 'name' => 'user', 'description' => 'User ID, login or email.']],
            ]);
        }
    }

    public static function enabled(): bool
    {
        return defined('TAXEON_2FA') ? (bool) TAXEON_2FA : true;
    }

    public static function requires($user): bool
    {
        if (!$user instanceof WP_User || !$user->exists()) {
            return false;
        }

        return array_intersect(self::ROLES, (array) $user->roles) !== []
            || user_can($user, 'manage_options')
            || user_can($user, 'manage_woocommerce')
            || user_can($user, 'edit_others_posts');
    }

    public static function challenge($user)
    {
        if (!self::enabled() || !self::requires($user) || self::trusted($user)) {
            return $user;
        }

        if (!self::isFormLogin()) {
            return new WP_Error('taxeon_2fa_required', __('This account requires a verification code. Log in through the login form.', 'taxeon'));
        }

        $token = wp_generate_password(40, false, false);
        $data = self::issueCode($user, [
            'user' => $user->ID,
            'remember' => !empty($_POST['rememberme']),
            'redirect' => self::requestedRedirect(),
            'sends' => 0,
        ]);

        set_transient(self::key($token), $data, self::CODE_TTL * 2);
        self::setCookie(self::COOKIE, $token, time() + self::CODE_TTL * 2);

        wp_safe_redirect(self::screenUrl());
        exit;
    }

    public static function screen(): void
    {
        nocache_headers();

        $token = (string) ($_COOKIE[self::COOKIE] ?? '');
        $data = $token !== '' ? get_transient(self::key($token)) : false;
        $user = is_array($data) ? get_userdata((int) $data['user']) : false;

        if (!$user) {
            self::clearCookie(self::COOKIE);
            wp_safe_redirect(add_query_arg(self::ACTION, 'expired', wp_login_url()));
            exit;
        }

        $errors = new WP_Error();
        $notice = '';

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            if (!wp_verify_nonce((string) ($_POST['_taxeon_2fa_nonce'] ?? ''), self::ACTION)) {
                $errors->add('taxeon_2fa_nonce', __('The form has expired. Please enter the code again.', 'taxeon'));
            } elseif (isset($_POST['taxeon_2fa_resend'])) {
                if ((int) $data['sends'] >= self::MAX_SENDS) {
                    $errors->add('taxeon_2fa_sends', __('The code limit has been reached. Log in again to get a new code.', 'taxeon'));
                } elseif (time() - (int) $data['sent_at'] < self::RESEND_DELAY) {
                    $errors->add('taxeon_2fa_wait', __('Please wait a moment before requesting another code.', 'taxeon'));
                } else {
                    $data = self::issueCode($user, $data);
                    set_transient(self::key($token), $data, self::CODE_TTL * 2);
                    $notice = __('We have sent a new code.', 'taxeon');
                }
            } else {
                $code = preg_replace('/\D/', '', (string) wp_unslash($_POST['taxeon_2fa_code'] ?? ''));

                if (time() > (int) $data['expires']) {
                    $errors->add('taxeon_2fa_expired', __('The code has expired. Send a new one.', 'taxeon'));
                } elseif ($code !== '' && hash_equals((string) $data['code'], self::hash($code))) {
                    self::complete($user, $data, $token);
                } else {
                    $data['attempts'] = (int) $data['attempts'] + 1;
                    LoginLimiter::recordFailure($user->user_login);

                    if ($data['attempts'] >= self::MAX_ATTEMPTS) {
                        delete_transient(self::key($token));
                        self::clearCookie(self::COOKIE);
                        wp_safe_redirect(add_query_arg(self::ACTION, 'locked', wp_login_url()));
                        exit;
                    }

                    set_transient(self::key($token), $data, self::CODE_TTL * 2);
                    $errors->add('taxeon_2fa_invalid', sprintf(
                        _n('Incorrect code. %d attempt left.', 'Incorrect code. %d attempts left.', self::MAX_ATTEMPTS - $data['attempts'], 'taxeon'),
                        self::MAX_ATTEMPTS - $data['attempts']
                    ));
                }
            }
        }

        if (!empty($data['mail_failed'])) {
            $errors->add('taxeon_2fa_mail', __('We could not send the code. Contact the site administrator.', 'taxeon'));
        }

        self::render($user, $errors, $notice);
        exit;
    }

    public static function expiredNotice(): void
    {
        $state = (string) ($_GET[self::ACTION] ?? '');

        if (!in_array($state, ['expired', 'locked'], true) || ($_REQUEST['action'] ?? 'login') !== 'login') {
            return;
        }

        add_filter('wp_login_errors', static function ($errors) use ($state) {
            $errors->add('taxeon_2fa_' . $state, $state === 'locked'
                ? __('Too many incorrect codes. Log in again to get a new code.', 'taxeon')
                : __('The verification session has expired. Log in again.', 'taxeon'));

            return $errors;
        });
    }

    public static function trusted(WP_User $user): bool
    {
        [$userId, $token] = array_pad(explode(':', (string) ($_COOKIE[self::TRUST_COOKIE] ?? ''), 2), 2, '');

        if ((int) $userId !== $user->ID || $token === '') {
            return false;
        }

        $devices = (array) get_user_meta($user->ID, self::TRUST_META, true);

        return (int) ($devices[self::hash($token)] ?? 0) > time();
    }

    public static function forgetDevices($user): void
    {
        $userId = $user instanceof WP_User ? $user->ID : (int) $user;

        if ($userId > 0) {
            delete_user_meta($userId, self::TRUST_META);
        }
    }

    public static function applicationPasswords($available, $user)
    {
        return self::requires($user) ? false : $available;
    }

    public static function cliForgetDevices(array $args): void
    {
        $value = (string) ($args[0] ?? '');
        $user = is_numeric($value) ? get_userdata((int) $value) : (get_user_by('login', $value) ?: get_user_by('email', $value));

        if (!$user) {
            \WP_CLI::error('User not found.');
        }

        self::forgetDevices($user);
        \WP_CLI::success('Trusted devices removed.');
    }

    private static function complete(WP_User $user, array $data, string $token): void
    {
        delete_transient(self::key($token));
        self::clearCookie(self::COOKIE);

        wp_set_auth_cookie($user->ID, !empty($data['remember']), is_ssl());

        if (!empty($_POST['taxeon_2fa_trust'])) {
            self::trustDevice($user);
        }

        wp_set_current_user($user->ID);
        do_action('wp_login', $user->user_login, $user);

        $requested = (string) ($data['redirect'] ?? '');
        $redirect = apply_filters('login_redirect', $requested !== '' ? $requested : admin_url(), $requested, $user);

        wp_safe_redirect($redirect ?: admin_url());
        exit;
    }

    private static function issueCode(WP_User $user, array $data): array
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $data['code'] = self::hash($code);
        $data['expires'] = time() + self::CODE_TTL;
        $data['attempts'] = 0;
        $data['sends'] = (int) ($data['sends'] ?? 0) + 1;
        $data['sent_at'] = time();
        $data['mail_failed'] = !self::mail($user, $code);

        return $data;
    }

    private static function mail(WP_User $user, string $code): bool
    {
        $minutes = (int) (self::CODE_TTL / MINUTE_IN_SECONDS);
        $html = SecurityMail::paragraph(sprintf(esc_html__('Use this code to finish logging in to the %s panel:', 'taxeon'), esc_html(get_bloginfo('name'))));
        $html .= SecurityMail::code($code);
        $html .= SecurityMail::paragraph(esc_html(sprintf(_n('The code is valid for %d minute.', 'The code is valid for %d minutes.', $minutes, 'taxeon'), $minutes)));
        $html .= SecurityMail::paragraph(esc_html(sprintf(__('Login attempt from IP %1$s on %2$s.', 'taxeon'), RateLimiter::clientIp() ?: '—', wp_date('d.m.Y H:i'))));
        $html .= SecurityMail::paragraph(esc_html__('If this was not you, change your password right away and let the administrator know.', 'taxeon'), '0');

        return SecurityMail::send($user->user_email, sprintf(__('Your %s login code', 'taxeon'), get_bloginfo('name')), __('Login code', 'taxeon'), $html);
    }

    private static function trustDevice(WP_User $user): void
    {
        $token = wp_generate_password(40, false, false);
        $expires = time() + self::TRUST_DAYS * DAY_IN_SECONDS;
        $devices = array_filter((array) get_user_meta($user->ID, self::TRUST_META, true), static fn($until) => (int) $until > time());
        $devices[self::hash($token)] = $expires;
        arsort($devices);

        update_user_meta($user->ID, self::TRUST_META, array_slice($devices, 0, self::MAX_DEVICES, true));
        self::setCookie(self::TRUST_COOKIE, $user->ID . ':' . $token, $expires);
    }

    private static function render(WP_User $user, WP_Error $errors, string $notice): void
    {
        $message = '<p class="message">' . esc_html(sprintf(
            __('We have sent a 6-digit code to %s. Enter it below to finish logging in.', 'taxeon'),
            self::maskEmail($user->user_email)
        )) . ($notice !== '' ? '<br><strong>' . esc_html($notice) . '</strong>' : '') . '</p>';

        login_header(__('Verification code', 'taxeon'), $message, $errors);

        $action = esc_url(self::screenUrl());
        $nonce = '<input type="hidden" name="_taxeon_2fa_nonce" value="' . esc_attr(wp_create_nonce(self::ACTION)) . '">';
        ?>
        <form name="taxeon2fa" id="loginform" action="<?php echo $action; ?>" method="post">
            <p>
                <label for="taxeon_2fa_code"><?php esc_html_e('Code from the e-mail', 'taxeon'); ?></label>
                <input type="text" name="taxeon_2fa_code" id="taxeon_2fa_code" class="input" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9 ]*" maxlength="7" required autofocus>
            </p>
            <p class="forgetmenot">
                <input type="checkbox" name="taxeon_2fa_trust" id="taxeon_2fa_trust" value="1">
                <label for="taxeon_2fa_trust"><?php echo esc_html(sprintf(_n('Trust this device for %d day', 'Trust this device for %d days', self::TRUST_DAYS, 'taxeon'), self::TRUST_DAYS)); ?></label>
            </p>
            <?php echo $nonce; ?>
            <p class="submit">
                <input type="submit" class="button button-primary button-large" value="<?php esc_attr_e('Log in', 'taxeon'); ?>">
            </p>
        </form>
        <form id="taxeon-2fa-resend" action="<?php echo $action; ?>" method="post">
            <?php echo $nonce; ?>
            <p id="nav">
                <button type="submit" name="taxeon_2fa_resend" value="1" class="button-link"><?php esc_html_e('Send the code again', 'taxeon'); ?></button>
                &nbsp;|&nbsp;
                <a href="<?php echo esc_url(wp_login_url()); ?>"><?php esc_html_e('Back to login', 'taxeon'); ?></a>
            </p>
        </form>
        <?php
        login_footer('taxeon_2fa_code');
    }

    private static function isFormLogin(): bool
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            return false;
        }

        return ($GLOBALS['pagenow'] ?? '') === 'wp-login.php' || isset($_POST['woocommerce-login-nonce']);
    }

    private static function requestedRedirect(): string
    {
        $raw = (string) wp_unslash($_REQUEST['redirect_to'] ?? $_POST['redirect'] ?? '');
        $redirect = $raw !== '' ? (string) wp_validate_redirect($raw, '') : '';

        if ($redirect === '' && isset($_POST['woocommerce-login-nonce']) && function_exists('wc_get_page_permalink')) {
            $redirect = (string) wc_get_page_permalink('myaccount');
        }

        return $redirect;
    }

    private static function screenUrl(): string
    {
        return add_query_arg('action', self::ACTION, wp_login_url());
    }

    private static function maskEmail(string $email): string
    {
        [$name, $domain] = array_pad(explode('@', $email, 2), 2, '');

        return mb_substr($name, 0, 2) . str_repeat('•', max(1, mb_strlen($name) - 2)) . '@' . $domain;
    }

    private static function key(string $token): string
    {
        return self::PREFIX . hash('sha256', $token);
    }

    private static function hash(string $value): string
    {
        return hash_hmac('sha256', $value, wp_salt('auth'));
    }

    private static function setCookie(string $name, string $value, int $expires): void
    {
        setcookie($name, $value, [
            'expires' => $expires,
            'path' => COOKIEPATH ?: '/',
            'domain' => COOKIE_DOMAIN ?: '',
            'secure' => is_ssl(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        $_COOKIE[$name] = $value;
    }

    private static function clearCookie(string $name): void
    {
        self::setCookie($name, '', time() - YEAR_IN_SECONDS);
        unset($_COOKIE[$name]);
    }
}
