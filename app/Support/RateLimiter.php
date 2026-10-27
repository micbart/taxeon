<?php

namespace App\Support;

class RateLimiter
{
    public static function hit(string $bucket, int $limit, int $window, string $subject = ''): bool
    {
        $key = 'taxeon_' . $bucket . '_rl_' . md5(strtolower($subject !== '' ? $subject : self::clientIp()) . wp_salt('nonce'));
        $hits = (int) get_transient($key);

        if ($hits >= $limit) {
            return false;
        }

        set_transient($key, $hits + 1, $window);

        return true;
    }

    public static function clientIp(): string
    {
        $remote = self::validIp((string) ($_SERVER['REMOTE_ADDR'] ?? ''));

        if ($remote === '' || !self::trusted($remote)) {
            return $remote;
        }

        $forwarded = array_reverse(array_map('trim', explode(',', (string) ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? ''))));

        foreach ($forwarded as $candidate) {
            $ip = self::validIp($candidate);

            if ($ip !== '' && !self::trusted($ip)) {
                return $ip;
            }
        }

        return $remote;
    }

    private static function trusted(string $ip): bool
    {
        $proxies = defined('TAXEON_TRUSTED_PROXIES') ? TAXEON_TRUSTED_PROXIES : [];
        $proxies = (array) apply_filters('taxeon_trusted_proxies', is_string($proxies) ? explode(',', $proxies) : (array) $proxies);

        foreach ($proxies as $range) {
            if (self::inRange($ip, trim((string) $range))) {
                return true;
            }
        }

        return false;
    }

    private static function inRange(string $ip, string $range): bool
    {
        if ($range === '') {
            return false;
        }

        [$subnet, $bits] = array_pad(explode('/', $range, 2), 2, null);
        $ipBin = @inet_pton($ip);
        $subnetBin = @inet_pton((string) $subnet);

        if ($ipBin === false || $subnetBin === false || strlen($ipBin) !== strlen($subnetBin)) {
            return false;
        }

        $bits = $bits === null ? strlen($ipBin) * 8 : max(0, min(strlen($ipBin) * 8, (int) $bits));
        $bytes = intdiv($bits, 8);
        $rest = $bits % 8;

        if (strncmp($ipBin, $subnetBin, $bytes) !== 0) {
            return false;
        }

        if ($rest === 0) {
            return true;
        }

        $mask = (0xFF << (8 - $rest)) & 0xFF;

        return (ord($ipBin[$bytes]) & $mask) === (ord($subnetBin[$bytes]) & $mask);
    }

    private static function validIp(string $ip): string
    {
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '';
    }
}
