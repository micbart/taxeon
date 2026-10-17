<?php

namespace App\Services;

/**
 * Udostępnia /llms.txt — skrót serwisu dla modeli językowych
 * (markdown z listą stron i krótkim opisem oferty).
 */
class LlmsTxt
{
	public function __construct()
	{
		add_action('init', [$this, 'rejestrujRegule']);
		add_filter('query_vars', [$this, 'dodajZmienna']);
		add_action('template_redirect', [$this, 'wyswietl']);
	}

	public function rejestrujRegule(): void
	{
		add_rewrite_rule('^llms\.txt$', 'index.php?taxeon_llms=1', 'top');
	}

	/** @param array<int, string> $vars */
	public function dodajZmienna(array $vars): array
	{
		$vars[] = 'taxeon_llms';

		return $vars;
	}

	public function wyswietl(): void
	{
		if (!get_query_var('taxeon_llms')) {
			return;
		}

		header('Content-Type: text/plain; charset=utf-8');
		header('X-Robots-Tag: noindex');

		echo $this->tresc();
		exit;
	}

	private function tresc(): string
	{
		$wiersze = [
			'# ' . get_bloginfo('name'),
			'',
			'> ' . get_bloginfo('description'),
			'',
			'Biuro rachunkowe online dla jednoosobowych działalności. Ryczałt od 99 zł,',
			'KPiR od 169 zł, aplikacja do faktur w cenie abonamentu. Siedziba: Warszawa.',
			'',
			'## Oferta',
		];

		$strony = [
			'ryczalt' => 'Pełna obsługa ryczałtu od przychodów ewidencjonowanych',
			'kpir' => 'Książka Przychodów i Rozchodów z VAT i kosztami',
			'cennik' => 'Wszystkie pakiety, dopłaty i zakres każdego abonamentu',
			'aplikacja' => 'Program księgowy online — faktury, koszty, KSeF',
			'biura-rachunkowe' => 'Program dla biur rachunkowych, 12 zł za firmę',
			'faq' => 'Najczęstsze pytania o ceny, zakres i zmianę biura',
		];

		foreach ($strony as $slug => $opis) {
			$strona = get_page_by_path($slug);

			if ($strona) {
				$wiersze[] = sprintf('- [%s](%s): %s', get_the_title($strona), get_permalink($strona), $opis);
			}
		}

		$wiersze[] = '';
		$wiersze[] = '## Branże';

		foreach (['b2b', 'it', 'lekarze', 'prawnicy'] as $slug) {
			$strona = get_page_by_path($slug);

			if ($strona) {
				$wiersze[] = sprintf('- [%s](%s)', get_the_title($strona), get_permalink($strona));
			}
		}

		$blog = (int) get_option('page_for_posts');

		if ($blog) {
			$wiersze[] = '';
			$wiersze[] = '## Baza wiedzy';
			$wiersze[] = '- [' . get_the_title($blog) . '](' . get_permalink($blog) . '): artykuły o podatkach i księgowości';

			foreach (get_posts(['numberposts' => 10]) as $wpis) {
				$wiersze[] = sprintf('- [%s](%s)', $wpis->post_title, get_permalink($wpis));
			}
		}

		$wiersze[] = '';
		$wiersze[] = '## Kontakt';
		$wiersze[] = '- E-mail: kontakt@taxeon.pl';
		$wiersze[] = '- Telefon: +48 515 033 505';
		$wiersze[] = '- Adres: ul. Kamionkowska 51, 03-812 Warszawa';
		$wiersze[] = '- Rezerwacja rozmowy: https://taxeon.youcanbook.me/';
		$wiersze[] = '';

		return implode("\n", $wiersze);
	}
}
