<?php

namespace App\Services\Security;

class SecurityMail
{
	private const INK = '#061024';
	private const INK_3 = '#5b6a85';
	private const LINE = '#e3e9f4';
	private const PAGE = '#f7faff';
	private const ACCENT = '#0b63f6';
	private const FONT = "'IBM Plex Sans', 'Segoe UI', Arial, sans-serif";

	public static function send(string $to, string $subject, string $heading, string $content): bool
	{
		$nazwa = (string) get_bloginfo('name');
		$adres = (string) home_url('/');

		$html = '<!doctype html><html lang="' . esc_attr(substr(get_locale(), 0, 2)) . '"><head>'
			. '<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
			. '<title>' . esc_html($subject) . '</title></head>'
			. '<body style="margin:0;padding:32px 16px;background:' . self::PAGE . ';font-family:' . self::FONT . ';">'
			. '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;margin:0 auto;'
			. 'background:#ffffff;border:1px solid ' . self::LINE . ';border-radius:14px;"><tr><td style="padding:32px;">'
			. '<h1 style="margin:0 0 20px;font-size:20px;line-height:1.3;color:' . self::INK . ';">' . esc_html($heading) . '</h1>'
			. $content
			. '<hr style="margin:28px 0 20px;border:0;border-top:1px solid ' . self::LINE . ';">'
			. '<p style="margin:0 0 6px;font-size:13px;color:' . self::INK_3 . ';">'
			. esc_html__('To automatyczna wiadomość bezpieczeństwa. Nie odpowiadaj na nią.', 'taxeon') . '</p>'
			. '<p style="margin:0;font-size:13px;"><a href="' . esc_url($adres) . '" style="color:' . self::ACCENT . ';">'
			. esc_html($nazwa) . '</a></p>'
			. '</td></tr></table></body></html>';

		return (bool) wp_mail(
			$to,
			wp_specialchars_decode($subject, ENT_QUOTES),
			$html,
			['Content-Type: text/html; charset=UTF-8']
		);
	}

	public static function paragraph(string $html, string $margin = '0 0 20px'): string
	{
		return '<p style="margin:' . $margin . ';font-size:15px;line-height:1.6;color:' . self::INK . ';">' . $html . '</p>';
	}

	public static function code(string $code): string
	{
		return '<p style="margin:0 0 24px;padding:16px 0;font-size:34px;font-weight:700;letter-spacing:10px;'
			. 'line-height:1;text-align:center;color:' . self::INK . ';background:' . self::PAGE . ';'
			. 'border:1px solid ' . self::LINE . ';border-radius:10px;">' . esc_html($code) . '</p>';
	}
}
