<?php

namespace App\Services;

class Hardening
{
	public function __construct()
	{
		add_action('send_headers', [$this, 'securityHeaders']);

		add_filter('xmlrpc_enabled', [$this, 'xmlrpcEnabled']);
		add_filter('wp_headers', [$this, 'stripPingbackHeader']);

		add_action('template_redirect', [$this, 'blockAuthorEnumeration'], 1);
		add_filter('rest_endpoints', [$this, 'restrictUserEndpoints']);
		add_filter('oembed_response_data', [$this, 'stripAuthorFromOembed']);
	}

	public function securityHeaders(): void
	{
		if (is_admin()) {
			return;
		}

		$headers = apply_filters('taxeon_security_headers', [
			'X-Content-Type-Options' => 'nosniff',
			'Referrer-Policy' => 'strict-origin-when-cross-origin',
			'X-Frame-Options' => 'SAMEORIGIN',
			'Permissions-Policy' => 'geolocation=(), microphone=(), camera=(), interest-cohort=()',
		]);

		foreach ($headers as $name => $value) {
			if ($value !== '' && !headers_sent()) {
				header($name . ': ' . $value);
			}
		}
	}

	public function xmlrpcEnabled(bool $enabled): bool
	{
		return (bool) apply_filters('taxeon_xmlrpc_enabled', false);
	}

	/**
	 * @param array<string, string> $headers
	 * @return array<string, string>
	 */
	public function stripPingbackHeader(array $headers): array
	{
		unset($headers['X-Pingback']);

		return $headers;
	}

	public function blockAuthorEnumeration(): void
	{
		if (is_user_logged_in() || is_admin()) {
			return;
		}

		if (!apply_filters('taxeon_block_author_enumeration', true)) {
			return;
		}

		if (!isset($_GET['author']) && !is_author()) {
			return;
		}

		wp_safe_redirect(home_url('/'), 301);
		exit;
	}

	/**
	 * oEmbed podaje autora wraz z adresem archiwum, a ten zdradza login.
	 *
	 * @param array<string, mixed> $data
	 * @return array<string, mixed>
	 */
	public function stripAuthorFromOembed(array $data): array
	{
		if (!apply_filters('taxeon_block_author_enumeration', true)) {
			return $data;
		}

		unset($data['author_name'], $data['author_url']);

		return $data;
	}

	/**
	 * @param array<string, mixed> $endpoints
	 * @return array<string, mixed>
	 */
	public function restrictUserEndpoints(array $endpoints): array
	{
		if (is_user_logged_in() || !apply_filters('taxeon_block_author_enumeration', true)) {
			return $endpoints;
		}

		unset($endpoints['/wp/v2/users'], $endpoints['/wp/v2/users/(?P<id>[\d]+)']);

		return $endpoints;
	}
}
