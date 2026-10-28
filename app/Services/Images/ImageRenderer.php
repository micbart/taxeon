<?php

namespace App\Services\Images;

use Timber\ImageHelper;

final class ImageRenderer
{
	public const WEBP_QUALITY = 88;

	public const JPEG_QUALITY = 90;

	private static ?string $redirectTo = null;

	private static bool $pierwszyWziety = false;

	public static function claimFirst(): bool
	{
		if (self::$pierwszyWziety) {
			return false;
		}

		return self::$pierwszyWziety = true;
	}

	public function __construct()
	{
		add_filter('wp_editor_set_quality', [$this, 'editorQuality'], 10, 2);
	}

	public function editorQuality(int $quality, string $mime = ''): int
	{
		return $mime === 'image/webp' ? self::WEBP_QUALITY : self::JPEG_QUALITY;
	}

	public static function redirectPath(string $path): string
	{
		return self::$redirectTo ?? $path;
	}

	public static function normalizeId(mixed $value): int
	{
		if (is_numeric($value)) {
			return (int) $value;
		}

		if ($value instanceof \Timber\Post || $value instanceof \WP_Post) {
			return (int) $value->ID;
		}

		if (is_object($value) && isset($value->ID)) {
			return (int) $value->ID;
		}

		if (is_object($value) && isset($value->id)) {
			return (int) $value->id;
		}

		if (is_array($value)) {
			return (int) ($value['ID'] ?? $value['id'] ?? 0);
		}

		return 0;
	}

	public static function pictureData(int $id, string $preset, string $altFallback = ''): ?array
	{
		$src = wp_get_attachment_url($id);

		if (!$src) {
			return null;
		}

		$meta = wp_get_attachment_metadata($id);
		$alt = (string) get_post_meta($id, '_wp_attachment_image_alt', true);
		$alt = $alt !== '' ? $alt : wp_strip_all_tags($altFallback);

		if (get_post_mime_type($id) === 'image/svg+xml') {
			$width = (int) ($meta['width'] ?? 0);
			$height = (int) ($meta['height'] ?? 0);

			if ($width <= 0 || $height <= 0) {
				[$width, $height] = self::svgDimensions($id);
			}

			return [
				'alt' => $alt,
				'width' => $width,
				'height' => $height,
				'is_svg' => true,
				'sizes' => '',
				'webp' => '',
				'src' => $src,
			];
		}

		$originalWidth = (int) ($meta['width'] ?? 0);
		$originalHeight = (int) ($meta['height'] ?? 0);

		$config = ImagePresets::get($preset);
		$widths = ImagePresets::widthsFor($preset, $originalWidth);
		$fallbackWidth = self::fallbackWidth($widths);

		$webp = [];
		$fallback = null;

		foreach ($widths as $width) {
			$height = self::targetHeight($width, $config);
			$isFallback = $width === $fallbackWidth;

			$webpPath = self::webpPathFor($src, $width, $height, $config['crop']);

			if (!$isFallback && $webpPath !== null && self::isIntact($webpPath)) {
				$webp[] = self::pathToUrl($webpPath) . ' ' . $width . 'w';

				continue;
			}

			$resized = self::resize($src, $width, $height, $config['crop']);

			if ($resized === null) {
				continue;
			}

			if ($isFallback) {
				$fallback = $resized;
			}

			$converted = self::toWebp($resized);

			if ($converted !== null) {
				$webp[] = $converted . ' ' . $width . 'w';

				if (!$isFallback) {
					self::discardIntermediate($resized);
				}
			} elseif (!$isFallback) {
				$webp[] = $resized . ' ' . $width . 'w';
			}
		}

		if ($fallback === null) {
			$fallback = self::resize($src, $fallbackWidth, self::targetHeight($fallbackWidth, $config), $config['crop']);
		}

		if ($fallback === null && $webp === []) {
			return null;
		}

		return [
			'alt' => $alt,
			'width' => $originalWidth,
			'height' => $originalHeight,
			'is_svg' => false,
			'sizes' => $config['sizes'],
			'webp' => implode(', ', $webp),
			'src' => $fallback ?? $src,
		];
	}

	private static function svgDimensions(int $id): array
	{
		static $cache = [];

		if (isset($cache[$id])) {
			return $cache[$id];
		}

		$file = get_attached_file($id);
		$markup = $file && is_readable($file) ? (string) file_get_contents($file, false, null, 0, 2048) : '';

		if ($markup === '' || !preg_match('/<svg\b[^>]*>/i', $markup, $tag)) {
			return $cache[$id] = [0, 0];
		}

		$width = preg_match('/\bwidth\s*=\s*["\']([\d.]+)/i', $tag[0], $m) ? (float) $m[1] : 0.0;
		$height = preg_match('/\bheight\s*=\s*["\']([\d.]+)/i', $tag[0], $m) ? (float) $m[1] : 0.0;

		if (($width <= 0 || $height <= 0) && preg_match('/\bviewBox\s*=\s*["\']\s*[\d.-]+[,\s]+[\d.-]+[,\s]+([\d.]+)[,\s]+([\d.]+)/i', $tag[0], $m)) {
			$width = (float) $m[1];
			$height = (float) $m[2];
		}

		return $cache[$id] = [(int) round($width), (int) round($height)];
	}

	private static function fallbackWidth(array $widths): int
	{
		$target = max($widths) <= 360 ? 240 : 800;
		$best = $widths[0];

		foreach ($widths as $width) {
			if (abs($width - $target) < abs($best - $target)) {
				$best = $width;
			}
		}

		return $best;
	}

	public static function webpPathFor(string $src, int $width, int $height, bool|string $crop): ?string
	{
		$path = ImageHelper::get_resize_file_path($src, $width, $height, self::cropArg($crop));

		if (!$path) {
			return null;
		}

		return preg_replace('/\.[a-zA-Z0-9]+$/', '.webp', $path) ?: null;
	}

	private static function discardIntermediate(string $url): void
	{
		$path = self::urlToPath($url);

		if ($path !== '' && is_file($path)) {
			@unlink($path);
		}
	}

	private static function targetHeight(int $width, array $config): int
	{
		if (!$config['crop'] || !$config['ratio']) {
			return 0;
		}

		return (int) round($width / $config['ratio']);
	}

	private static function cropArg(bool|string $crop): bool|string
	{
		return $crop === false ? false : ($crop === true ? 'default' : $crop);
	}

	public static function resize(string $src, int $width, int $height, bool|string $crop): ?string
	{
		$cropArg = self::cropArg($crop);
		$path = ImageHelper::get_resize_file_path($src, $width, $height, $cropArg);

		return self::ensure(
			$path,
			static fn () => ImageHelper::resize($src, $width, $height, $cropArg, true)
		);
	}

	public static function toWebp(string $src): ?string
	{
		$path = preg_replace('/\.[a-zA-Z0-9]+$/', '.webp', self::urlToPath($src));

		if (!$path) {
			return null;
		}

		return self::ensure(
			$path,
			static fn () => ImageHelper::img_to_webp($src, self::WEBP_QUALITY, true)
		);
	}

	private static function ensure(string $path, callable $generate): ?string
	{
		if (self::isIntact($path)) {
			return self::pathToUrl($path);
		}

		if (file_exists($path)) {
			@unlink($path);
		}

		$lock = self::acquireLock($path);

		if ($lock === null) {
			return self::isIntact($path) ? self::pathToUrl($path) : null;
		}

		try {
			if (self::isIntact($path)) {
				return self::pathToUrl($path);
			}

			$tmp = self::tempPath($path);

			self::$redirectTo = $tmp;
			add_filter('timber/image/new_path', [self::class, 'redirectPath'], 99);

			try {
				$generate();
			} finally {
				remove_filter('timber/image/new_path', [self::class, 'redirectPath'], 99);
				self::$redirectTo = null;
			}

			if (!self::isIntact($tmp) || !@rename($tmp, $path)) {
				@unlink($tmp);

				return null;
			}

			return self::pathToUrl($path);
		} finally {
			self::releaseLock($lock);
		}
	}

	private static function tempPath(string $path): string
	{
		$extension = pathinfo($path, PATHINFO_EXTENSION);
		$suffix = '.tmp-' . getmypid() . '-' . wp_generate_password(6, false);

		if ($extension === '') {
			return $path . $suffix;
		}

		return substr($path, 0, -(strlen($extension) + 1)) . $suffix . '.' . $extension;
	}

	private static function acquireLock(string $path): ?array
	{
		$lockPath = $path . '.lock';
		$dir = dirname($lockPath);

		if (!is_dir($dir) && !wp_mkdir_p($dir)) {
			return null;
		}

		$handle = @fopen($lockPath, 'c');

		if ($handle === false) {
			return null;
		}

		if (!flock($handle, LOCK_EX)) {
			fclose($handle);

			return null;
		}

		return [$handle, $lockPath];
	}

	private static function releaseLock(array $lock): void
	{
		[$handle, $lockPath] = $lock;

		flock($handle, LOCK_UN);
		fclose($handle);
		@unlink($lockPath);
	}

	public static function isIntact(string $path): bool
	{
		clearstatcache(true, $path);

		if (!is_file($path)) {
			return false;
		}

		$size = filesize($path);

		if ($size === false || $size < 100) {
			return false;
		}

		$info = @getimagesize($path);

		if ($info === false || empty($info[0]) || empty($info[1])) {
			return false;
		}

		$handle = @fopen($path, 'rb');

		if ($handle === false) {
			return false;
		}

		try {
			$header = fread($handle, 12);

			if ($header === false || strlen($header) < 12) {
				return false;
			}

			if (str_starts_with($header, "\xFF\xD8")) {
				fseek($handle, -2, SEEK_END);

				return fread($handle, 2) === "\xFF\xD9";
			}

			if (str_starts_with($header, "\x89PNG")) {
				fseek($handle, -8, SEEK_END);

				return str_contains((string) fread($handle, 8), 'IEND');
			}

			if (str_starts_with($header, 'RIFF') && substr($header, 8, 4) === 'WEBP') {
				$declared = unpack('V', substr($header, 4, 4));

				return is_array($declared) && ($declared[1] + 8) === $size;
			}

			return true;
		} finally {
			fclose($handle);
		}
	}

	public static function urlToPath(string $url): string
	{
		$uploads = wp_get_upload_dir();

		if (str_starts_with($url, $uploads['baseurl'])) {
			return $uploads['basedir'] . substr($url, strlen($uploads['baseurl']));
		}

		return ImageHelper::get_server_location($url) ?: $url;
	}

	public static function pathToUrl(string $path): string
	{
		$uploads = wp_get_upload_dir();

		if (str_starts_with($path, $uploads['basedir'])) {
			return $uploads['baseurl'] . substr($path, strlen($uploads['basedir']));
		}

		return $path;
	}
}
