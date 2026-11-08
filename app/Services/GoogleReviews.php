<?php

namespace App\Services;

class GoogleReviews
{
	public const MIN_RATING = 4;

	private const ENDPOINT = 'https://places.googleapis.com/v1/places/';
	private const STORE = 'taxeon_google_reviews';
	private const HOOK = 'taxeon_sync_google_reviews';
	private const RETENTION = 30 * DAY_IN_SECONDS;

	public function __construct()
	{
		add_action(self::HOOK, [self::class, 'sync']);
		add_action('init', [self::class, 'scheduleNightly']);
		add_action('admin_post_taxeon_sync_reviews', [self::class, 'handleManualSync']);
		add_filter('acf/load_field/name=google_items', [self::class, 'loadChoices']);
		add_filter('acf/load_field/name=google_reviews_sync', [self::class, 'loadSyncPanel']);
	}

	public static function scheduleNightly(): void
	{
		if (wp_next_scheduled(self::HOOK)) {
			return;
		}

		$noca = strtotime('tomorrow 03:30', current_time('timestamp'));
		wp_schedule_event($noca - (int) (get_option('gmt_offset') * HOUR_IN_SECONDS), 'daily', self::HOOK);
	}

	public static function unschedule(): void
	{
		$nastepne = wp_next_scheduled(self::HOOK);

		if ($nastepne) {
			wp_unschedule_event($nastepne, self::HOOK);
		}
	}

	/** @return array{added: int, total: int, pruned: int, error: string} */
	public static function sync(): array
	{
		$klucz = trim((string) self::option('google_api_key'));
		$miejsce = trim((string) self::option('google_place_id'));

		if ($klucz === '' || $miejsce === '') {
			return self::result(0, 0, 0, __('Brak klucza API albo identyfikatora wizytówki.', 'taxeon'));
		}

		$pobrane = self::request($klucz, $miejsce);

		if ($pobrane['error'] !== '') {
			return self::result(0, 0, 0, $pobrane['error']);
		}

		$magazyn = self::stored();
		$teraz = time();
		$nowe = 0;

		foreach ($pobrane['items'] as $opinia) {
			$id = $opinia['id'];

			if (!isset($magazyn['items'][$id])) {
				$nowe++;
			}

			$magazyn['items'][$id] = $opinia + ['seen' => $teraz];
		}

		$przed = count($magazyn['items']);
		$magazyn['items'] = array_filter(
			$magazyn['items'],
			static fn(array $opinia): bool => ($opinia['seen'] ?? 0) > $teraz - self::RETENTION
		);

		$magazyn['rating'] = $pobrane['rating'];
		$magazyn['total'] = $pobrane['total'];
		$magazyn['mapsUrl'] = $pobrane['mapsUrl'];
		$magazyn['updated'] = $teraz;

		update_option(self::STORE, $magazyn, false);

		return self::result($nowe, count($magazyn['items']), $przed - count($magazyn['items']), '');
	}

	public static function stored(): array
	{
		$magazyn = get_option(self::STORE, []);

		return is_array($magazyn) ? $magazyn + ['items' => [], 'updated' => 0] : ['items' => [], 'updated' => 0];
	}

	/** @param array<int, string> $wybrane */
	public static function selected(array $wybrane, int $limit = 8): array
	{
		$magazyn = self::stored();
		$opinie = [];

		foreach ($magazyn['items'] as $id => $opinia) {
			if ($wybrane && !in_array($id, $wybrane, true)) {
				continue;
			}

			if (!$wybrane && (int) ($opinia['rating'] ?? 5) < self::MIN_RATING) {
				continue;
			}

			$opinie[] = $opinia;
		}

		usort($opinie, static fn(array $a, array $b): int => ($b['time'] ?? 0) <=> ($a['time'] ?? 0));

		return array_slice($opinie, 0, max(1, $limit));
	}

	public static function loadChoices(array $pole): array
	{
		$pole['choices'] = [];

		foreach (self::stored()['items'] as $id => $opinia) {
			$pole['choices'][$id] = sprintf(
				'%s★ %s · %s — %s',
				$opinia['rating'] ?? 5,
				$opinia['author'] ?? '',
				$opinia['when'] ?? '',
				mb_strimwidth((string) ($opinia['text'] ?? ''), 0, 90, '…')
			);
		}

		if (!$pole['choices']) {
			$pole['instructions'] = __('Najpierw pobierz opinie w zakładce „Opinie Google” w opcjach motywu.', 'taxeon');
		}

		return $pole;
	}

	public static function loadSyncPanel(array $pole): array
	{
		$magazyn = self::stored();

		$kiedy = $magazyn['updated']
			? sprintf(
				__('Ostatnie pobranie: %s.', 'taxeon'),
				wp_date('j F Y, H:i', (int) $magazyn['updated'])
			)
			: __('Opinie nie były jeszcze pobierane.', 'taxeon');

		$nastepne = wp_next_scheduled(self::HOOK);
		$plan = $nastepne
			? sprintf(__('Następne automatyczne: %s.', 'taxeon'), wp_date('j F Y, H:i', $nastepne))
			: __('Automatyczne pobieranie nie jest zaplanowane.', 'taxeon');

		$url = wp_nonce_url(
			admin_url('admin-post.php?action=taxeon_sync_reviews'),
			'taxeon_sync_reviews'
		);

		$ocena = $magazyn['rating']
			? sprintf(
				__('Wizytówka: %1$s/5 z %2$d ocen.', 'taxeon'),
				number_format_i18n((float) $magazyn['rating'], 1),
				(int) ($magazyn['total'] ?? 0)
			)
			: '';

		$pole['message'] = sprintf(
			'<p>%s %s<br>%s %s</p><p><a class="button button-secondary" href="%s">%s</a></p>'
				. '<p class="description">%s</p>',
			esc_html($kiedy),
			esc_html(sprintf(__('W pamięci: %d opinii.', 'taxeon'), count($magazyn['items']))),
			esc_html($ocena),
			esc_html($plan),
			esc_url($url),
			esc_html__('Pobierz opinie teraz', 'taxeon'),
			esc_html__(
				'Google udostępnia przez API najwyżej pięć opinii z wizytówki i sam wybiera, '
					. 'które to będą — nie zawsze są to najnowsze. Treść opinii trzymamy przez 30 dni, '
					. 'tyle pozwalają warunki Google. Żeby pokazać więcej opinii, w bloku „Opinie” '
					. 'wybierz „Z Google i dopisane ręcznie” i dopisz pozostałe.',
				'taxeon'
			)
		);

		return $pole;
	}

	public static function handleManualSync(): void
	{
		if (!current_user_can('manage_options') || !check_admin_referer('taxeon_sync_reviews')) {
			wp_die(__('Brak uprawnień.', 'taxeon'));
		}

		$wynik = self::sync();

		wp_safe_redirect(add_query_arg(
			['taxeon_reviews' => $wynik['error'] !== '' ? 'error' : 'ok', 'taxeon_added' => $wynik['added']],
			wp_get_referer() ?: admin_url()
		));
		exit;
	}

	private static function request(string $klucz, string $miejsce): array
	{
		$url = self::ENDPOINT . rawurlencode($miejsce)
			. '?languageCode=pl&fields=rating,userRatingCount,googleMapsUri,reviews';

		$odpowiedz = wp_remote_get($url, [
			'timeout' => 10,
			'headers' => ['X-Goog-Api-Key' => $klucz],
		]);

		if (is_wp_error($odpowiedz)) {
			return ['error' => $odpowiedz->get_error_message()] + self::pusty();
		}

		$kod = wp_remote_retrieve_response_code($odpowiedz);
		$dane = json_decode((string) wp_remote_retrieve_body($odpowiedz), true);

		if ($kod !== 200 || !is_array($dane)) {
			$powod = $dane['error']['message'] ?? ('HTTP ' . $kod);

			return ['error' => (string) $powod] + self::pusty();
		}

		$opinie = [];

		foreach ($dane['reviews'] ?? [] as $opinia) {
			$autor = $opinia['authorAttribution'] ?? [];
			$tresc = trim((string) ($opinia['originalText']['text'] ?? $opinia['text']['text'] ?? ''));
			$nazwa = (string) ($autor['displayName'] ?? 'Klient Google');

			$opinie[] = [
				'id' => substr(md5($nazwa . '|' . $tresc), 0, 12),
				'author' => $nazwa,
				'authorUrl' => (string) ($autor['uri'] ?? ''),
				'photo' => (string) ($autor['photoUri'] ?? ''),
				'rating' => (int) ($opinia['rating'] ?? 5),
				'when' => (string) ($opinia['relativePublishTimeDescription'] ?? ''),
				'time' => strtotime((string) ($opinia['publishTime'] ?? '')) ?: 0,
				'text' => $tresc,
			];
		}

		return [
			'error' => '',
			'rating' => isset($dane['rating']) ? (float) $dane['rating'] : null,
			'total' => isset($dane['userRatingCount']) ? (int) $dane['userRatingCount'] : null,
			'mapsUrl' => (string) ($dane['googleMapsUri'] ?? ''),
			'items' => $opinie,
		];
	}

	private static function pusty(): array
	{
		return ['rating' => null, 'total' => null, 'mapsUrl' => '', 'items' => []];
	}

	private static function result(int $added, int $total, int $pruned, string $error): array
	{
		return ['added' => $added, 'total' => $total, 'pruned' => $pruned, 'error' => $error];
	}

	private static function option(string $name): mixed
	{
		return function_exists('get_field') ? get_field($name, 'option') : null;
	}
}
