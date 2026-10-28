<?php

namespace App\Services\Images;

final class ImagePresets
{
	/**
	 * @var array<string, array{sizes: string, range: array{0: int, 1: int}, crop: bool|string, ratio?: float}>
	 */
	private const PRESETS = [
		'hero' => [
			'sizes' => '100vw',
			'range' => [800, 2560],
			'crop' => false,
		],

		'cover' => [
			'sizes' => '(max-width: 760px) 100vw, 60vw',
			'range' => [540, 1600],
			'crop' => false,
		],

		'media' => [
			'sizes' => '(max-width: 760px) 100vw, 50vw',
			'range' => [540, 1600],
			'crop' => false,
		],

		'tile' => [
			'sizes' => '(max-width: 900px) 100vw, 33vw',
			'range' => [360, 1100],
			'crop' => false,
		],

		'post-featured' => [
			'sizes' => '(max-width: 760px) 100vw, 55vw',
			'range' => [540, 1600],
			'crop' => false,
		],

		'post-card' => [
			'sizes' => '(max-width: 520px) 100vw, (max-width: 900px) 50vw, 33vw',
			'range' => [360, 1100],
			'crop' => false,
		],

		'post-cover' => [
			'sizes' => '(max-width: 760px) 100vw, 1080px',
			'range' => [540, 1600],
			'crop' => false,
		],

		'avatar' => [
			'sizes' => '44px',
			'range' => [240, 360],
			'crop' => false,
		],

		'inline' => [
			'sizes' => '(max-width: 760px) 100vw, 1080px',
			'range' => [360, 1600],
			'crop' => false,
		],

	];

	/**
	 * @return array{sizes: string, range: array{0: int, 1: int}, crop: bool|string, ratio: float|null}
	 */
	public static function get(string $name): array
	{
		$preset = self::PRESETS[$name] ?? null;

		if ($preset === null) {
			if (defined('WP_DEBUG') && WP_DEBUG) {
				error_log(sprintf('[taxeon] Nieznany preset obrazu: "%s" — użyto "inline".', $name));
			}

			$preset = self::PRESETS['inline'];
		}

		return [
			'sizes' => $preset['sizes'],
			'range' => $preset['range'],
			'crop' => $preset['crop'],
			'ratio' => $preset['ratio'] ?? null,
		];
	}

	public static function exists(string $name): bool
	{
		return isset(self::PRESETS[$name]);
	}

	/**
	 * @return string[]
	 */
	public static function names(): array
	{
		return array_keys(self::PRESETS);
	}

	/**
	 * @return int[]
	 */
	public static function widthsFor(string $name, int $originalWidth): array
	{
		$preset = self::get($name);
		[$min, $max] = $preset['range'];

		$widths = [];

		foreach (ImageLadder::STEPS as $step) {
			if ($step < $min || $step > $max) {
				continue;
			}

			$widths[] = ImageLadder::clamp($step, $originalWidth);
		}

		if ($widths === []) {
			$widths[] = ImageLadder::clamp($min, $originalWidth);
		}

		return array_values(array_unique($widths));
	}
}
