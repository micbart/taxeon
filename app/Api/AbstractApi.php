<?php

namespace App\Api;

use WP_REST_Request;
use WP_REST_Response;

abstract class AbstractApi
{
	public const NAMESPACE = 'raks/v1';

	protected string $route = '';
	protected string $methods = 'POST';

	public function __construct()
	{
		add_action('rest_api_init', [$this, 'register']);
	}

	public function register(): void
	{
		register_rest_route(self::NAMESPACE, $this->route, [
			'methods' => $this->methods,
			'callback' => [$this, 'handle'],
			'permission_callback' => [$this, 'permission'],
			'args' => $this->args(),
		]);
	}

	public function permission(WP_REST_Request $request): bool
	{
		return true;
	}

	protected function args(): array
	{
		return [];
	}

	abstract public function handle(WP_REST_Request $request): WP_REST_Response;

	protected function success(array $data = [], int $status = 200): WP_REST_Response
	{
		return new WP_REST_Response(['success' => true] + $data, $status);
	}

	protected function error(string $message, int $status = 400, array $data = []): WP_REST_Response
	{
		return new WP_REST_Response(['success' => false, 'message' => $message] + $data, $status);
	}

	protected function clientIp(): string
	{
		foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $header) {
			if (empty($_SERVER[$header])) {
				continue;
			}

			$ip = trim(explode(',', (string) $_SERVER[$header])[0]);

			if (filter_var($ip, FILTER_VALIDATE_IP)) {
				return $ip;
			}
		}

		return '';
	}

	protected function rateLimit(string $bucket, string $ip, int $max = 5, int $window = 600): bool
	{
		if ($ip === '') {
			return true;
		}

		$key = 'raks_rl_' . $bucket . '_' . md5($ip);
		$hits = (int) get_transient($key);

		if ($hits >= $max) {
			return false;
		}

		set_transient($key, $hits + 1, $window);

		return true;
	}
}
