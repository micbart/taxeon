<?php

namespace App\Setup;

class Vite
{
	private static ?array $manifest = null;
	private static array $moduleHandles = [];
	private static bool $filterRegistered = false;
	private static bool $clientEnqueued = false;

	private static ?bool $isDev = null;

	public static function isDev(): bool
	{
		if (self::$isDev !== null) {
			return self::$isDev;
		}

		if (!file_exists(get_template_directory() . '/dist/.hot')) {
			return self::$isDev = false;
		}

		$parts = parse_url(self::devServerUrl());
		$socket = @fsockopen($parts['host'] ?? 'localhost', $parts['port'] ?? 5173, $errno, $error, 0.1);

		if ($socket === false) {
			return self::$isDev = false;
		}

		fclose($socket);

		return self::$isDev = true;
	}

	public static function devServerUrl(): string
	{
		$url = (string) file_get_contents(get_template_directory() . '/dist/.hot');

		return rtrim(trim($url), '/');
	}

	public static function enqueue(string $entry, string $handle, array $deps = []): void
	{
		if (self::isDev()) {
			self::enqueueClient();
			wp_enqueue_script($handle, self::devServerUrl() . '/' . $entry, $deps, null, true);
			self::markAsModule($handle);
			return;
		}

		$asset = self::manifest()[$entry] ?? null;

		if (!$asset) {
			return;
		}

		wp_enqueue_script($handle, self::distUrl($asset['file']), $deps, null, true);
		self::markAsModule($handle);

		$seen = [];
		self::enqueueChunkCss($handle, $asset, $seen);
	}

	private static function enqueueChunkCss(string $handle, array $chunk, array &$seen): void
	{
		$chunkFile = $chunk['file'] ?? '';

		if (isset($seen[$chunkFile])) {
			return;
		}

		$seen[$chunkFile] = true;

		foreach ($chunk['css'] ?? [] as $cssFile) {
			$styleHandle = $handle . '-' . substr(md5($cssFile), 0, 8);
			wp_enqueue_style($styleHandle, self::distUrl($cssFile), [], null);
		}

		$manifest = self::manifest();

		foreach ($chunk['imports'] ?? [] as $importKey) {
			if (isset($manifest[$importKey])) {
				self::enqueueChunkCss($handle, $manifest[$importKey], $seen);
			}
		}
	}

	public static function enqueueStyle(string $entry, string $handle, array $deps = []): void
	{
		if (self::isDev()) {
			self::enqueueClient();
			wp_enqueue_script($handle, self::devServerUrl() . '/' . $entry, [], null, true);
			self::markAsModule($handle);
			return;
		}

		$asset = self::manifest()[$entry] ?? null;

		if (!$asset) {
			return;
		}

		wp_enqueue_style($handle, self::distUrl($asset['file']), $deps, null);
	}

	public static function enqueueEntryCss(string $entry, string $handle): void
	{
		if (self::isDev()) {
			self::enqueueClient();
			wp_enqueue_script($handle, self::devServerUrl() . '/' . $entry, [], null, true);
			self::markAsModule($handle);
			return;
		}

		$asset = self::manifest()[$entry] ?? null;

		if (!$asset) {
			return;
		}

		$seen = [];
		self::enqueueChunkCss($handle, $asset, $seen);
	}

	public static function url(string $entry): ?string
	{
		if (self::isDev()) {
			return self::devServerUrl() . '/' . $entry;
		}

		$asset = self::manifest()[$entry] ?? null;

		return $asset ? self::distUrl($asset['file']) : null;
	}

	private static function enqueueClient(): void
	{
		if (self::$clientEnqueued) {
			return;
		}

		self::$clientEnqueued = true;
		wp_enqueue_script('taxeon-vite-client', self::devServerUrl() . '/@vite/client', [], null, false);
		self::markAsModule('taxeon-vite-client');
	}

	private static function distUrl(string $file): string
	{
		return get_template_directory_uri() . '/dist/' . $file;
	}

	private static function manifest(): array
	{
		if (self::$manifest !== null) {
			return self::$manifest;
		}

		$path = get_template_directory() . '/dist/.vite/manifest.json';
		self::$manifest = file_exists($path)
			? (array) json_decode((string) file_get_contents($path), true)
			: [];

		return self::$manifest;
	}

	private static function markAsModule(string $handle): void
	{
		self::$moduleHandles[$handle] = true;

		if (self::$filterRegistered) {
			return;
		}

		self::$filterRegistered = true;

		add_filter('script_loader_tag', static function (string $tag, string $tagHandle): string {
			if (!isset(self::$moduleHandles[$tagHandle])) {
				return $tag;
			}

			$tag = str_replace('<script ', '<script type="module" crossorigin ', $tag);

			return str_replace(" type='text/javascript'", '', $tag);
		}, 10, 2);
	}
}
