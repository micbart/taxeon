<?php

namespace App\Api;

use App\Services\Shop\ProductSync;
use WP_REST_Request;
use WP_REST_Response;

class ApiShopProduct extends AbstractApi
{
	protected string $route = '/sklep/produkt';
	protected string $methods = 'POST';

	private const MAX_AGE = 300;

	private ProductSync $sync;

	public function __construct(ProductSync $sync)
	{
		$this->sync = $sync;

		parent::__construct();
	}

	public function permission(WP_REST_Request $request): bool
	{
		$secret = self::secret();

		if ($secret === '') {
			return false;
		}

		$timestamp = (int) $request->get_header('x-raks-timestamp');

		if ($timestamp === 0 || abs(time() - $timestamp) > self::MAX_AGE) {
			return false;
		}

		$signature = (string) $request->get_header('x-raks-signature');
		$expected = 'sha256=' . hash_hmac('sha256', $timestamp . '.' . $request->get_body(), $secret);

		return hash_equals($expected, $signature);
	}

	public function handle(WP_REST_Request $request): WP_REST_Response
	{
		$payload = $request->get_json_params();

		if (!is_array($payload)) {
			return $this->error(__('Nieprawidłowa treść żądania.', 'raks'), 400);
		}

		try {
			$result = $this->sync->apply($payload);
		} catch (\Throwable $e) {
			return $this->error($e->getMessage(), 422);
		}

		return $this->success($result);
	}

	public static function secret(): string
	{
		if (defined('RAKS_SYNC_SECRET') && RAKS_SYNC_SECRET !== '') {
			return (string) RAKS_SYNC_SECRET;
		}

		return (string) get_option('raks_sync_secret', '');
	}
}
