<?php

namespace App\Services\Recaptcha;

class RecaptchaService
{
	private const ENDPOINT = 'https://www.google.com/recaptcha/api/siteverify';

	public function registerFrontend(): void
	{
		add_action('wp_enqueue_scripts', [$this, 'enqueueScript'], 20);
	}

	public function enqueueScript(): void
	{
		if (!$this->isEnabled()) {
			return;
		}

		wp_enqueue_script(
			'google-recaptcha',
			'https://www.google.com/recaptcha/api.js?render=' . rawurlencode($this->siteKey()),
			[],
			null,
			true
		);
	}

	public function isEnabled(): bool
	{
		if (!function_exists('get_field')) {
			return false;
		}

		return (bool) get_field('recaptcha_enabled', 'option')
			&& $this->siteKey() !== ''
			&& $this->secretKey() !== '';
	}

	public function siteKey(): string
	{
		return function_exists('get_field') ? trim((string) get_field('recaptcha_site_key', 'option')) : '';
	}

	private function secretKey(): string
	{
		return function_exists('get_field') ? trim((string) get_field('recaptcha_secret_key', 'option')) : '';
	}

	private function threshold(): float
	{
		$value = function_exists('get_field') ? (float) get_field('recaptcha_threshold', 'option') : 0.0;

		return $value > 0 ? $value : 0.5;
	}

	/** @return array{success: bool, score: float, message: string} */
	public function verify(string $token, string $expectedAction = ''): array
	{
		if (!$this->isEnabled()) {
			return ['success' => true, 'score' => 1.0, 'message' => ''];
		}

		if ($token === '') {
			return ['success' => false, 'score' => 0.0, 'message' => __('Brak tokenu weryfikacji.', 'taxeon')];
		}

		$response = wp_remote_post(self::ENDPOINT, [
			'timeout' => 8,
			'body' => [
				'secret' => $this->secretKey(),
				'response' => $token,
				'remoteip' => $this->clientIp(),
			],
		]);

		if (is_wp_error($response)) {
			return [
				'success' => false,
				'score' => 0.0,
				'message' => __('Nie udało się połączyć z usługą weryfikacji.', 'taxeon'),
			];
		}

		$body = (array) json_decode((string) wp_remote_retrieve_body($response), true);
		$score = (float) ($body['score'] ?? 0);

		if (empty($body['success'])) {
			return ['success' => false, 'score' => $score, 'message' => __('Weryfikacja nie powiodła się.', 'taxeon')];
		}

		if ($expectedAction !== '' && ($body['action'] ?? '') !== $expectedAction) {
			return ['success' => false, 'score' => $score, 'message' => __('Nieprawidłowa akcja weryfikacji.', 'taxeon')];
		}

		if ($score < $this->threshold()) {
			return [
				'success' => false,
				'score' => $score,
				'message' => __('Zgłoszenie zostało uznane za automatyczne.', 'taxeon'),
			];
		}

		return ['success' => true, 'score' => $score, 'message' => ''];
	}

	public function clientIp(): string
	{
		foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
			if (empty($_SERVER[$key])) {
				continue;
			}

			$candidate = trim(explode(',', (string) $_SERVER[$key])[0]);

			if (filter_var($candidate, FILTER_VALIDATE_IP)) {
				return $candidate;
			}
		}

		return '';
	}
}
