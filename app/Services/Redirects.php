<?php

namespace App\Services;

class Redirects
{
	private const MAPA = [
		'polski-lad-kalkulatory' => 'polski-lad-kalkulator',
	];

	public function __construct()
	{
		add_action('template_redirect', [self::class, 'przekieruj']);
	}

	public static function przekieruj(): void
	{
		if (!is_404()) {
			return;
		}

		$sciezka = trim((string) wp_parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH), '/');
		$sciezka = ltrim(substr($sciezka, strlen(trim((string) wp_parse_url(home_url(), PHP_URL_PATH), '/'))), '/');

		$mapa = apply_filters('taxeon_redirects', self::MAPA);

		if (!isset($mapa[$sciezka])) {
			return;
		}

		$cel = get_page_by_path($mapa[$sciezka]);

		if (!$cel) {
			return;
		}

		wp_safe_redirect(get_permalink($cel), 301);
		exit;
	}
}
