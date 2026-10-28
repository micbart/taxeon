<?php

namespace App\Services\Images;

use Timber\ImageHelper;

final class ImageWarm
{
	public function __construct()
	{
		add_action('wp_update_attachment_metadata', [self::class, 'forget'], 10, 2);
		add_action('delete_attachment', [self::class, 'forgetById']);

		if (defined('WP_CLI') && WP_CLI) {
			\WP_CLI::add_command('taxeon-images warm', [self::class, 'cliWarm'], [
				'shortdesc' => 'Pregeneruje warianty WebP dla wszystkich obrazów w bibliotece.',
				'synopsis' => [
					['type' => 'assoc', 'name' => 'preset', 'optional' => true],
					['type' => 'assoc', 'name' => 'id', 'optional' => true],
				],
			]);

			\WP_CLI::add_command('taxeon-images purge', [self::class, 'cliPurge'], [
				'shortdesc' => 'Kasuje wygenerowane warianty obrazów.',
				'synopsis' => [['type' => 'assoc', 'name' => 'id', 'optional' => true]],
			]);
		}
	}

	/**
	 * @param array<string, mixed> $metadata
	 * @return array<string, mixed>
	 */
	public static function forget(array $metadata, int $attachmentId = 0): array
	{
		if ($attachmentId > 0) {
			self::forgetById($attachmentId);
		}

		return $metadata;
	}

	public static function forgetById(int $attachmentId): void
	{
		$src = wp_get_attachment_url($attachmentId);

		if ($src) {
			ImageHelper::delete_generated_files($src);
		}
	}

	/**
	 * @param array<int, string> $args
	 * @param array<string, string> $options
	 */
	public static function cliWarm(array $args, array $options = []): void
	{
		$presety = isset($options['preset']) ? [$options['preset']] : ImagePresets::names();
		$obrazy = isset($options['id'])
			? [(int) $options['id']]
			: get_posts([
				'post_type' => 'attachment',
				'post_mime_type' => ['image/jpeg', 'image/png', 'image/webp'],
				'post_status' => 'inherit',
				'numberposts' => -1,
				'fields' => 'ids',
			]);

		$start = microtime(true);
		$wariantow = 0;

		foreach ($obrazy as $id) {
			foreach ($presety as $preset) {
				$dane = ImageRenderer::pictureData((int) $id, $preset);

				if ($dane && $dane['webp'] !== '') {
					$wariantow += count(explode(', ', $dane['webp']));
				}
			}
		}

		\WP_CLI::success(sprintf(
			'Obrazów: %d, presetów: %d, wariantów: %d, czas: %.1fs',
			count($obrazy),
			count($presety),
			$wariantow,
			microtime(true) - $start
		));
	}

	/**
	 * @param array<int, string> $args
	 * @param array<string, string> $options
	 */
	public static function cliPurge(array $args, array $options = []): void
	{
		$obrazy = isset($options['id'])
			? [(int) $options['id']]
			: get_posts([
				'post_type' => 'attachment',
				'post_mime_type' => ['image/jpeg', 'image/png', 'image/webp'],
				'post_status' => 'inherit',
				'numberposts' => -1,
				'fields' => 'ids',
			]);

		foreach ($obrazy as $id) {
			self::forgetById((int) $id);
		}

		\WP_CLI::success(sprintf('Wyczyszczono warianty dla %d obrazów.', count($obrazy)));
	}
}
