<?php

namespace App\Services;

class Prices
{
	public const FIELDS = [
		'cena_ryczalt' => '99',
		'cena_ryczalt_vat' => '149',
		'cena_kpir' => '169',
		'cena_aplikacja_start' => '19',
		'cena_aplikacja' => '43',
		'cena_biura' => '12',
		'cena_kadry' => '100',
		'cena_pit' => '89',
	];

	private static ?array $map = null;

	public function __construct()
	{
		add_filter('acf/format_value', [self::class, 'deep'], 20);
		add_filter('the_content', [self::class, 'apply'], 5);
		add_filter('the_title', [self::class, 'title']);
		add_filter('wp_setup_nav_menu_item', [self::class, 'menuItem']);
		add_filter('rank_math/frontend/title', [self::class, 'apply']);
		add_filter('rank_math/frontend/description', [self::class, 'apply']);
		add_filter('document_title_parts', [self::class, 'deep']);
	}

	public static function map(): array
	{
		if (self::$map !== null) {
			return self::$map;
		}

		self::$map = [];

		foreach (self::FIELDS as $field => $fallback) {
			$value = trim((string) get_option('options_' . $field, ''));
			self::$map['{' . $field . '}'] = $value !== '' ? $value : $fallback;
		}

		return self::$map;
	}

	public static function apply(mixed $value): mixed
	{
		if (!is_string($value) || !str_contains($value, '{cena_')) {
			return $value;
		}

		return strtr($value, self::map());
	}

	public static function title(mixed $value): mixed
	{
		return is_admin() && !wp_doing_ajax() ? $value : self::apply($value);
	}

	public static function deep(mixed $value): mixed
	{
		if (is_array($value)) {
			return array_map([self::class, 'deep'], $value);
		}

		return self::apply($value);
	}

	public static function menuItem(object $item): object
	{
		if (is_admin() || wp_doing_ajax() || is_customize_preview()) {
			return $item;
		}

		if (isset($item->title)) {
			$item->title = self::apply($item->title);
		}

		return $item;
	}
}
