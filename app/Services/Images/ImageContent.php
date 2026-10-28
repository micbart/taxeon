<?php

namespace App\Services\Images;

final class ImageContent
{
	private const PRESET = 'inline';

	public function __construct()
	{
		add_filter('the_content', [self::class, 'rewrite'], 20);
		add_filter('acf/format_value/type=wysiwyg', [self::class, 'rewriteField'], 20);
	}

	public static function rewriteField(mixed $value): mixed
	{
		return is_string($value) ? self::rewrite($value) : $value;
	}

	public static function rewrite(string $html): string
	{
		if ($html === '' || !str_contains($html, '<img')) {
			return $html;
		}

		return (string) preg_replace_callback(
			'#<img\b[^>]*>#i',
			static function (array $match): string {
				$tag = $match[0];

				if (str_contains($tag, '<picture') || !preg_match('/wp-image-(\d+)/', $tag, $id)) {
					return $tag;
				}

				$dane = ImageRenderer::pictureData((int) $id[1], self::PRESET);

				if (!$dane || $dane['is_svg'] || $dane['webp'] === '') {
					return $tag;
				}

				$alt = preg_match('/\balt\s*=\s*"([^"]*)"/i', $tag, $m) ? $m[1] : $dane['alt'];
				$klasa = preg_match('/\bclass\s*=\s*"([^"]*)"/i', $tag, $m) ? $m[1] : '';

				return sprintf(
					'<picture><source type="image/webp" srcset="%s" sizes="%s">'
						. '<img src="%s" alt="%s" width="%d" height="%d" class="%s" loading="lazy" decoding="async"></picture>',
					esc_attr($dane['webp']),
					esc_attr($dane['sizes']),
					esc_url($dane['src']),
					esc_attr($alt),
					$dane['width'],
					$dane['height'],
					esc_attr($klasa)
				);
			},
			$html
		);
	}
}
