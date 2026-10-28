<?php

namespace App\Services\Security;

use App\Support\RateLimiter;
use WP_Error;
use WP_User;

class LoginLimiter
{
    const PREFIX = 'taxeon_login_fail_';
    const MEMORY = DAY_IN_SECONDS;
    const LIMIT = 3;
    const LOCKS = [3, 5, 30, 1440];
    const IP_LIMIT = 20;
    const IP_WINDOW = 15 * MINUTE_IN_SECONDS;
    const IP_LOCK = 60;
    const ALERT_LEVEL = 3;

    public static function boot(): void
    {
        add_filter('authenticate', [self::class, 'guard'], 99, 2);
        add_action('wp_login_failed', [self::class, 'recordFailure']);
        add_action('wp_login', [self::class, 'clearOnSuccess']);

        if (defined('WP_CLI') && WP_CLI) {
            \WP_CLI::add_command('taxeon login-lock clear', [self::class, 'cliClear'], [
                'shortdesc' => 'Clear failed login counters and locks.',
                'synopsis' => [
                    ['type' => 'assoc', 'name' => 'ip', 'optional' => true],
                    ['type' => 'assoc', 'name' => 'login', 'optional' => true],
                    ['type' => 'flag', 'name' => 'all', 'optional' => true],
                ],
            ]);
        }
    }

    public static function guard($user, $login)
    {
        $login = self::normalize((string) $login);

        if ($login === '') {
            return $user;
        }

        $remaining = self::lockedFor(RateLimiter::clientIp(), $login);

        if ($remaining <= 0) {
            return $user;
        }

        sleep(1);
        $minutes = (int) ceil($remaining / MINUTE_IN_SECONDS);

        return new WP_Error('taxeon_login_locked', sprintf(
            __('Zbyt wiele nieudanych prób logowania. Spróbuj ponownie za %d min.', 'taxeon'),
            $minutes
        ));
    }

    public static function recordFailure($login): void
    {
        $ip = RateLimiter::clientIp();
        $login = self::normalize((string) $login);

        if ($login === '' || self::lockedFor($ip, $login) > 0) {
            return;
        }

        $state = self::state(self::key($ip, $login));
        $state['fails']++;

        if ($state['fails'] >= self::LIMIT) {
            $state['level'] = min($state['level'] + 1, count(self::LOCKS));
            $state['until'] = time() + self::LOCKS[$state['level'] - 1] * MINUTE_IN_SECONDS;
            $state['fails'] = 0;
            $user = self::user($login);

            if ($user && TwoFactor::requires($user) && $state['level'] >= self::ALERT_LEVEL) {
                self::alert($user, $ip, $state['until']);
            }
        }

        set_transient(self::key($ip, $login), $state, self::MEMORY);

        $ipState = self::state(self::key($ip, ''));

        if ($ipState['until'] <= time()) {
            $ipState['fails']++;

            if ($ipState['fails'] >= self::IP_LIMIT) {
                $ipState['until'] = time() + self::IP_LOCK * MINUTE_IN_SECONDS;
                $ipState['fails'] = 0;
            }

            set_transient(self::key($ip, ''), $ipState, max(self::IP_WINDOW, self::IP_LOCK * MINUTE_IN_SECONDS));
        }
    }

    public static function clearOnSuccess($login): void
    {
        delete_transient(self::key(RateLimiter::clientIp(), self::normalize((string) $login)));
    }

    public static function lockedFor(string $ip, string $login): int
    {
        $until = max(self::state(self::key($ip, $login))['until'], self::state(self::key($ip, ''))['until']);

        return max(0, $until - time());
    }

    public static function cliClear(array $args, array $assoc): void
    {
        global $wpdb;

        if (!empty($assoc['all'])) {
            $rows = $wpdb->query($wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
                $wpdb->esc_like('_transient_' . self::PREFIX) . '%',
                $wpdb->esc_like('_transient_timeout_' . self::PREFIX) . '%'
            ));

            if (wp_using_ext_object_cache() && function_exists('wp_cache_flush_group') && wp_cache_supports('flush_group')) {
                wp_cache_flush_group('transient');
            }

            \WP_CLI::success(sprintf('Cleared %d rows.', (int) $rows));
            return;
        }

        $ip = (string) ($assoc['ip'] ?? '');

        if ($ip === '') {
            \WP_CLI::error('Pass --ip=<address> (optionally --login=<user>) or --all.');
        }

        delete_transient(self::key($ip, ''));

        if (isset($assoc['login'])) {
            delete_transient(self::key($ip, self::normalize((string) $assoc['login'])));
        }

        \WP_CLI::success('Cleared.');
    }

    private static function alert(WP_User $user, string $ip, int $until): void
    {
        $html = SecurityMail::paragraph(esc_html(sprintf(
            __('Twoje konto zostało tymczasowo zablokowane po kilku nieudanych próbach logowania z adresu IP %1$s. Blokada kończy się %2$s.', 'taxeon'),
            $ip ?: '—',
            wp_date('d.m.Y H:i', $until)
        )));
        $html .= SecurityMail::paragraph(esc_html__('Jeśli to nie Ty, ktoś może próbować odgadnąć Twoje hasło. Rozważ jego zmianę po wygaśnięciu blokady.', 'taxeon'), '0');

        SecurityMail::send($user->user_email, sprintf(__('%s — konto tymczasowo zablokowane', 'taxeon'), get_bloginfo('name')), __('Konto zablokowane', 'taxeon'), $html);
    }

    private static function state(string $key): array
    {
        $state = get_transient($key);

        if (!is_array($state)) {
            $state = ['fails' => (int) $state];
        }

        return [
            'fails' => (int) ($state['fails'] ?? 0),
            'level' => (int) ($state['level'] ?? 0),
            'until' => (int) ($state['until'] ?? 0),
        ];
    }

    private static function user(string $login): ?WP_User
    {
        $user = is_email($login) ? get_user_by('email', $login) : get_user_by('login', $login);

        return $user instanceof WP_User ? $user : null;
    }

    private static function key(string $ip, string $login): string
    {
        return self::PREFIX . md5($ip . '|' . $login . wp_salt('nonce'));
    }

    private static function normalize(string $login): string
    {
        return strtolower(trim(is_email(trim($login)) ? trim($login) : sanitize_user($login)));
    }
}
