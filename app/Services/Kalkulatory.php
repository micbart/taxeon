<?php

namespace App\Services;

class Kalkulatory
{
	public const STAWKI_RYCZALTU = ['3', '5.5', '8.5', '10', '12', '12.5', '14', '15', '17'];

	private const MNOZNIK_NISKI = 0.6;
	private const MNOZNIK_WYSOKI = 1.8;

	/** @return array<string, mixed> */
	public static function parametry(): array
	{
		return [
			'rok' => (string) (self::opcja('kalk_rok') ?: '2026'),
			'skalaProg' => self::liczba('kalk_skala_prog', 120000),
			'skalaStawkaNizsza' => self::liczba('kalk_skala_stawka_nizsza', 12),
			'skalaStawkaWyzsza' => self::liczba('kalk_skala_stawka_wyzsza', 32),
			'kwotaZmniejszajaca' => self::liczba('kalk_kwota_zmniejszajaca', 3600),
			'liniowyStawka' => self::liczba('kalk_liniowy_stawka', 19),
			'zdrowotnaSkala' => self::liczba('kalk_zdrowotna_skala', 9),
			'zdrowotnaLiniowy' => self::liczba('kalk_zdrowotna_liniowy', 4.9),
			'zdrowotnaMinimalna' => self::liczba('kalk_zdrowotna_minimalna', 3499.5),
			'ryczaltPrzecietne' => self::liczba('kalk_ryczalt_przecietne', 8549.18),
			'ryczaltProgNizszy' => self::liczba('kalk_ryczalt_prog_nizszy', 60000),
			'ryczaltProgWyzszy' => self::liczba('kalk_ryczalt_prog_wyzszy', 300000),
			'ryczaltOdliczenie' => self::liczba('kalk_ryczalt_odliczenie', 50),
			'limitVat' => self::liczba('kalk_limit_vat', 240000),
			'limitKasy' => self::liczba('kalk_limit_kasy', 20000),
			'domyslnyPrzychod' => self::liczba('kalk_domyslny_przychod', 10500),
			'domyslneKoszty' => self::liczba('kalk_domyslne_koszty', 1000),
			'domyslneSpoleczne' => self::liczba('kalk_domyslne_spoleczne', 1518.98),
			'domyslnaStawka' => (string) (self::opcja('kalk_domyslna_stawka') ?: '12'),
			'stawki' => self::STAWKI_RYCZALTU,
		];
	}

	/** @return array<string, mixed> */
	public static function podatkowy(
		float $przychodMiesiecznie,
		float $kosztyMiesiecznie,
		float $spoleczneMiesiecznie,
		float $stawkaRyczaltu
	): array {
		$p = self::parametry();

		$przychod = $przychodMiesiecznie * 12;
		$koszty = $kosztyMiesiecznie * 12;
		$spoleczne = $spoleczneMiesiecznie * 12;

		$dochod = $przychod - $koszty - $spoleczne;
		$podstawa = round($dochod, 0);

		$nizsza = $p['skalaStawkaNizsza'] / 100;
		$wyzsza = $p['skalaStawkaWyzsza'] / 100;
		$podatekWProgu = $p['skalaProg'] * $nizsza - $p['kwotaZmniejszajaca'];

		$pdofSkala = max(0.0, $podstawa <= $p['skalaProg']
			? round($podstawa * $nizsza - $p['kwotaZmniejszajaca'], 0)
			: round(($podstawa - $p['skalaProg']) * $wyzsza + $podatekWProgu, 0));

		$minimalnaRoczna = round($p['zdrowotnaMinimalna'] * 12 * ($p['zdrowotnaSkala'] / 100), 2);

		$zdrowotnaSkala = $dochod / 12 >= $p['zdrowotnaMinimalna']
			? round($dochod * ($p['zdrowotnaSkala'] / 100), 2)
			: $minimalnaRoczna;

		$pdofLiniowy = max(0.0, round($podstawa * ($p['liniowyStawka'] / 100), 0));

		$zdrowotnaLiniowy = ($dochod / 12) * ($p['zdrowotnaLiniowy'] / 100) >= $p['zdrowotnaMinimalna'] * ($p['zdrowotnaSkala'] / 100)
			? round($dochod * ($p['zdrowotnaLiniowy'] / 100), 2)
			: $minimalnaRoczna;

		$podstawaZdrowotnej = $przychod - $spoleczne;
		$przecietne = $p['ryczaltPrzecietne'];
		$stawkaZdrowotnej = $p['zdrowotnaSkala'] / 100;

		if ($podstawaZdrowotnej <= $p['ryczaltProgNizszy']) {
			$zdrowotnaRyczalt = round(round($przecietne * self::MNOZNIK_NISKI * 12, 2) * $stawkaZdrowotnej, 2);
		} elseif ($podstawaZdrowotnej <= $p['ryczaltProgWyzszy']) {
			$zdrowotnaRyczalt = round($przecietne * $stawkaZdrowotnej * 12, 2);
		} else {
			$zdrowotnaRyczalt = round(round($przecietne * self::MNOZNIK_WYSOKI * 12, 2) * $stawkaZdrowotnej, 2);
		}

		$odliczenie = $spoleczne + $zdrowotnaRyczalt * ($p['ryczaltOdliczenie'] / 100);
		$podstawaRyczaltu = round($przychod - round($odliczenie, 2), 0);
		$pdofRyczalt = max(0.0, round(round($podstawaRyczaltu * ($stawkaRyczaltu / 100), 2), 0));

		$formy = [
			'skala' => self::forma($pdofSkala, $zdrowotnaSkala, $spoleczne, $przychod, $koszty),
			'liniowy' => self::forma($pdofLiniowy, $zdrowotnaLiniowy, $spoleczne, $przychod, $koszty),
			'ryczalt' => self::forma($pdofRyczalt, $zdrowotnaRyczalt, $spoleczne, $przychod, $koszty),
		];

		$najlepsza = 'skala';

		foreach ($formy as $klucz => $forma) {
			if ($forma['naReke'] > $formy[$najlepsza]['naReke']) {
				$najlepsza = $klucz;
			}
		}

		return [
			'przychod' => $przychod,
			'koszty' => $koszty,
			'spoleczne' => $spoleczne,
			'podstawa' => $podstawa,
			'formy' => $formy,
			'najlepsza' => $najlepsza,
		];
	}

	/** @return array{dni: int, dniWRoku: int, limit: float} */
	public static function limit(string $data, float $limitRoczny): array
	{
		$dzien = date_create_immutable($data) ?: date_create_immutable('today');
		$rok = (int) $dzien->format('Y');
		$koniec = $dzien->setDate($rok, 12, 31);
		$poczatek = $dzien->setDate($rok, 1, 1);

		$dni = (int) $dzien->diff($koniec)->days + 1;
		$dniWRoku = (int) $poczatek->diff($koniec)->days + 1;

		return [
			'dni' => $dni,
			'dniWRoku' => $dniWRoku,
			'limit' => $limitRoczny * $dni / $dniWRoku,
		];
	}

	/** @return array<int, array<string, mixed>> */
	public static function pakiety(): array
	{
		$strona = self::stronaCennika();

		if (!$strona) {
			return [];
		}

		foreach (parse_blocks($strona->post_content) as $blok) {
			if (($blok['blockName'] ?? '') !== 'acf/hero') {
				continue;
			}

			$dane = $blok['attrs']['data'] ?? [];
			$ile = (int) ($dane['cards'] ?? 0);

			if (!$ile) {
				continue;
			}

			$pakiety = [];

			for ($i = 0; $i < $ile; $i++) {
				$pakiety[] = [
					'label' => (string) Prices::apply((string) ($dane["cards_{$i}_label"] ?? '')),
					'badge' => (string) Prices::apply((string) ($dane["cards_{$i}_badge"] ?? '')),
					'price' => (string) Prices::apply((string) ($dane["cards_{$i}_price"] ?? '')),
					'period' => (string) Prices::apply((string) ($dane["cards_{$i}_period"] ?? '')),
					'text' => (string) Prices::apply((string) ($dane["cards_{$i}_text"] ?? '')),
					'link' => (array) ($dane["cards_{$i}_link"] ?? []),
				];
			}

			return $pakiety;
		}

		return [];
	}

	private static function stronaCennika(): ?\WP_Post
	{
		$wybrana = self::opcja('kalk_strona_cennika');

		if ($wybrana instanceof \WP_Post) {
			return $wybrana;
		}

		if (is_numeric($wybrana)) {
			return get_post((int) $wybrana);
		}

		return get_page_by_path('cennik');
	}

	/** @return array<string, float> */
	private static function forma(float $pdof, float $zdrowotna, float $spoleczne, float $przychod, float $koszty): array
	{
		$suma = $spoleczne + $pdof + $zdrowotna;

		return [
			'pdof' => $pdof,
			'zdrowotna' => $zdrowotna,
			'spoleczne' => $spoleczne,
			'suma' => $suma,
			'naReke' => $przychod - $suma - $koszty,
		];
	}

	private static function liczba(string $nazwa, float $domyslna): float
	{
		$wartosc = self::opcja($nazwa);

		return is_numeric($wartosc) ? (float) $wartosc : $domyslna;
	}

	private static function opcja(string $nazwa): mixed
	{
		return function_exists('get_field') ? get_field($nazwa, 'option') : null;
	}
}
