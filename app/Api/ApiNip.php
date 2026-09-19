<?php

namespace App\Api;

use WP_REST_Request;
use WP_REST_Response;

class ApiNip extends AbstractApi
{
	protected string $route = '/nip';
	protected string $methods = 'GET';

	private const CACHE_TTL = DAY_IN_SECONDS;
	private const TIMEOUT = 5;

	protected function args(): array
	{
		return [
			'nip' => ['type' => 'string', 'required' => true],
		];
	}

	public function handle(WP_REST_Request $request): WP_REST_Response
	{
		$nip = preg_replace('/\D/', '', (string) $request->get_param('nip')) ?? '';

		if (!$this->isValidNip($nip)) {
			return $this->error(__('Wpisz poprawny, 10-cyfrowy numer NIP.', 'raks'), 422);
		}

		if (!$this->rateLimit('nip', $this->clientIp(), 20, 600)) {
			return $this->error(__('Zbyt wiele zapytań. Spróbuj ponownie za kilka minut.', 'raks'), 429);
		}

		$cacheKey = 'raks_nip_' . $nip;
		$cached = get_transient($cacheKey);

		if (is_array($cached)) {
			return $this->success($cached);
		}

		$company = $this->lookup($nip);

		if ($company === null) {
			return $this->success([
				'manual' => true,
				'message' => __('Nie udało się pobrać danych z rejestru. Wpisz nazwę firmy ręcznie.', 'raks'),
			]);
		}

		set_transient($cacheKey, $company, self::CACHE_TTL);

		return $this->success($company);
	}

	private function isValidNip(string $nip): bool
	{
		if (strlen($nip) !== 10 || $nip === '0000000000') {
			return false;
		}

		$weights = [6, 5, 7, 2, 3, 4, 5, 6, 7];
		$sum = 0;

		foreach ($weights as $index => $weight) {
			$sum += $weight * (int) $nip[$index];
		}

		$checksum = $sum % 11;

		return $checksum !== 10 && $checksum === (int) $nip[9];
	}

	/** @return array{name: string, address: string, status: string}|null */
	private function lookup(string $nip): ?array
	{
		$url = add_query_arg(
			['date' => wp_date('Y-m-d')],
			apply_filters('raks_nip_lookup_url', 'https://wl-api.mf.gov.pl/api/search/nip/' . $nip, $nip)
		);

		$response = wp_remote_get($url, [
			'timeout' => self::TIMEOUT,
			'headers' => ['Accept' => 'application/json'],
		]);

		if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
			return null;
		}

		$subject = json_decode(wp_remote_retrieve_body($response), true)['result']['subject'] ?? null;

		if (!is_array($subject) || empty($subject['name'])) {
			return null;
		}

		$statuses = [
			'Czynny' => __('Podatnik VAT czynny', 'raks'),
			'Zwolniony' => __('Podatnik VAT zwolniony', 'raks'),
		];

		$status = (string) ($subject['statusVat'] ?? '');

		return [
			'name' => sanitize_text_field((string) $subject['name']),
			'address' => sanitize_text_field((string) (($subject['workingAddress'] ?? '') ?: ($subject['residenceAddress'] ?? ''))),
			'status' => $statuses[$status] ?? $status,
		];
	}
}
