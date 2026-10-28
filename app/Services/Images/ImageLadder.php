<?php

namespace App\Services\Images;

final class ImageLadder
{
	/** @var int[] */
	public const STEPS = [240, 360, 540, 800, 1100, 1600, 2000, 2560];

	private const MARGIN_1X = 1.08;

	public static function snap(int $width): int
	{
		foreach (self::STEPS as $step) {
			if ($step >= $width) {
				return $step;
			}
		}

		return (int) end(self::STEPS);
	}

	public static function forSlot(int $slotCss): int
	{
		return self::snap((int) ceil($slotCss * self::MARGIN_1X));
	}

	public static function forSlotRetina(int $slotCss): int
	{
		return self::snap($slotCss * 2);
	}

	public static function clamp(int $width, int $originalWidth): int
	{
		if ($originalWidth <= 0 || $width <= $originalWidth) {
			return $width;
		}

		$fit = self::STEPS[0];

		foreach (self::STEPS as $step) {
			if ($step <= $originalWidth) {
				$fit = $step;
			}
		}

		return $originalWidth < self::STEPS[0] ? $originalWidth : $fit;
	}
}
