<?php

namespace App\Services;

class Media
{
	private const SIZES = [
		'raks-card' => [640, 400, true],
		'raks-card-2x' => [1280, 800, true],
		'raks-wide' => [1120, 560, true],
		'raks-wide-2x' => [1920, 960, true],
		'raks-tile' => [760, 333, true],
		'raks-cover' => [1400, 1400, false],
	];

	public function __construct()
	{
		add_action('after_setup_theme', [$this, 'registerImageSizes']);
		add_filter('image_size_names_choose', [$this, 'labelImageSizes']);
		add_action('delete_attachment', [$this, 'deleteThumbnailsAndWebp']);
		add_filter('upload_mimes', [$this, 'uploadSvg']);
		add_filter('wp_check_filetype_and_ext', [$this, 'fixSvgFiletype'], 10, 4);
		add_action('admin_head', [$this, 'styleSvg']);
		add_filter('big_image_size_threshold', fn() => 2560);
		add_filter('wp_lazy_loading_enabled', [$this, 'disableCoreLazyLoading'], 10, 2);
	}

	public function registerImageSizes(): void
	{
		foreach (self::SIZES as $name => [$width, $height, $crop]) {
			add_image_size($name, $width, $height, $crop);
		}
	}

	public function labelImageSizes(array $sizes): array
	{
		return $sizes + [
			'raks-card' => __('Karta (16:10)', 'raks'),
			'raks-wide' => __('Szeroki (16:8)', 'raks'),
			'raks-tile' => __('Kafel (16:7)', 'raks'),
		];
	}

	public function disableCoreLazyLoading(bool $default, string $tagName): bool
	{
		return $tagName === 'img' ? false : $default;
	}

	public function deleteThumbnailsAndWebp($attachmentID): void
	{
		$filePath = get_attached_file($attachmentID);

		if (!$filePath) {
			return;
		}

		$fileDir = pathinfo($filePath, PATHINFO_DIRNAME);
		$fileName = pathinfo($filePath, PATHINFO_FILENAME);

		if (!is_dir($fileDir)) {
			return;
		}

		foreach ((array) scandir($fileDir) as $file) {
			if ($file === '.' || $file === '..') {
				continue;
			}

			if (strpos($file, $fileName) !== 0) {
				continue;
			}

			$fullFilePath = "$fileDir/$file";

			if (file_exists($fullFilePath) && !@unlink($fullFilePath)) {
				if (defined('WP_DEBUG') && WP_DEBUG) {
					error_log("[raks] Nie udało się usunąć pliku: $fullFilePath");
				}
			}
		}
	}

	public function uploadSvg(array $mimes): array
	{
		if (!current_user_can('manage_options')) {
			return $mimes;
		}

		$mimes['svg'] = 'image/svg+xml';
		$mimes['svgz'] = 'image/svg+xml';

		return $mimes;
	}

	public function fixSvgFiletype(array $data, $file, $filename, $mimes): array
	{
		if (!current_user_can('manage_options')) {
			return $data;
		}

		if (str_ends_with(strtolower((string) $filename), '.svg')) {
			$data['ext'] = 'svg';
			$data['type'] = 'image/svg+xml';
		}

		return $data;
	}

	public function styleSvg(): void
	{
		?>
		<style>
			td.media-icon img[src$=".svg"],
			img[src$=".svg"].attachment-post-thumbnail {
				width: 100% !important;
				height: auto !important;
			}
		</style>
		<?php
	}
}
