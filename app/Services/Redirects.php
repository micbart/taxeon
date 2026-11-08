<?php

namespace App\Services;

class Redirects
{
	private const MAPA = [
		'polski-lad-kalkulatory' => 'kalkulatory',
		'it' => 'branze/it',
		'lekarze' => 'branze/lekarze',
		'prawnicy' => 'branze/prawnicy',
		'biura-rachunkowe' => 'dla-biur-rachunkowych',
	];

	private const DOKUMENTY = [
		'polityka-prywatnosci' => 'polityka-prywatnosci-pdf',
		'regulamin' => 'regulamin-serwisu-taxeon-pdf',
		'sygnalisci' => 'sygnalisci-pdf',
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

		$cel = self::dokument($sciezka) ?: self::strona($sciezka);

		if (!$cel) {
			return;
		}

		wp_safe_redirect($cel, 301);
		exit;
	}

	private static function strona(string $sciezka): string
	{
		$mapa = apply_filters('taxeon_redirects', self::MAPA);

		if (!isset($mapa[$sciezka])) {
			return '';
		}

		$strona = get_page_by_path($mapa[$sciezka]);

		return $strona ? (string) get_permalink($strona) : '';
	}

	private static function dokument(string $sciezka): string
	{
		if (!isset(self::DOKUMENTY[$sciezka])) {
			return '';
		}

		$zalacznik = get_posts([
			'post_type' => 'attachment',
			'name' => self::DOKUMENTY[$sciezka],
			'post_status' => 'inherit',
			'numberposts' => 1,
			'fields' => 'ids',
		]);

		return $zalacznik ? (string) wp_get_attachment_url($zalacznik[0]) : '';
	}
}
