<?php

namespace App\Services;

class ThemeCache
{
	private static int $ttl = 12 * HOUR_IN_SECONDS;

	public function __construct()
	{
		add_action('acf/save_post', function ($postId): void {
			if ($postId === 'options') {
				self::flush('options');
				self::flush('menus');
			}
		}, 20);

		add_action('wp_update_nav_menu', fn() => self::flush('menus'));
		add_action('wp_delete_nav_menu', fn() => self::flush('menus'));
		add_action('switch_theme', fn() => self::flush('menus'));
	}

	public static function remember(string $group, string $key, callable $callback, int $ttl = 0): mixed
	{
		if ($ttl <= 0) {
			$ttl = self::$ttl;
		}

		$transient = self::transientKey($group, $key);
		$cached = get_transient($transient);

		if ($cached !== false) {
			return $cached;
		}

		$value = $callback();
		set_transient($transient, $value, $ttl);

		return $value;
	}

	public static function flush(string $group): void
	{
		update_option(self::versionKey($group), self::version($group) + 1, true);
	}

	private const DEFAULTS = [
		'hero_video' => 'https://stream.mux.com/kimF2ha9zLrX64H00UgLGPflCzNtl1T0215MlAmeOztv8.m3u8',
	];

	public static function options(): array
	{
		$saved = (array) self::remember('options', 'all', function (): array {
			return function_exists('get_fields') ? ((array) get_fields('options') ?: []) : [];
		});

		return $saved + self::DEFAULTS;
	}

	public static function option(string $field): mixed
	{
		return self::options()[$field] ?? null;
	}

	private static function version(string $group): int
	{
		return (int) get_option(self::versionKey($group), 1);
	}

	private static function versionKey(string $group): string
	{
		return "raks_cache_ver_{$group}";
	}

	private static function transientKey(string $group, string $key): string
	{
		return 'raks_' . $group . '_' . self::version($group) . '_' . $key;
	}
}
