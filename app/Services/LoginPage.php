<?php

namespace App\Services;

class LoginPage
{
	private const SUPPORT_EMAIL = 'bartczakmichal94@gmail.com';

	public function __construct()
	{
		add_action('login_enqueue_scripts', [$this, 'styles']);
		add_action('login_footer', [$this, 'info']);
		add_filter('login_headerurl', static fn(): string => home_url('/'));
		add_filter('login_headertext', static fn(): string => (string) get_bloginfo('name'));
		add_filter('login_display_language_dropdown', '__return_false');
	}

	public function styles(): void
	{
		$logo = esc_url(get_template_directory_uri() . '/images/logo-white.png');
		$title = addcslashes($this->title(), '"\\');
		$fonts = $this->fontFaces();
		$css = <<<CSS
{$fonts}
body.login{background:#030b1c;font-family:"IBM Plex Sans",system-ui,sans-serif;color:#061024;position:relative;min-height:100vh;margin:0;overflow-x:hidden}
body.login::before{content:"";position:absolute;width:1200px;height:1200px;left:-600px;top:-600px;background:radial-gradient(circle closest-side,rgba(11,99,246,.5) 0%,rgba(0,0,0,0) 100%)}
body.login::after{content:"";position:absolute;width:50%;aspect-ratio:1/1;left:29%;top:50%;transform:translate(-50%,-50%);background:url("{$logo}") center/58% no-repeat}
body.login #login{position:relative;z-index:1;display:flex;flex-direction:column;justify-content:center;box-sizing:border-box;width:42%;min-height:100vh;margin:0 0 0 auto;padding:60px;background:#fff;text-align:center}
body.login #login::before{content:"{$title}";display:block;margin-bottom:24px;font-family:Poppins,system-ui,sans-serif;font-size:28px;font-weight:700;letter-spacing:-.02em;color:#061024}
body.login #login h1{display:none}
body.login #login form{max-width:400px;width:100%;margin:0 auto;padding:0;border:0;box-shadow:none;background:transparent;text-align:left}
body.login #login .message,body.login #login .notice,body.login #login .success,body.login #login #login_error{max-width:400px;width:100%;box-sizing:border-box;margin:0 auto 20px;border-radius:10px;border-left-color:#061024;box-shadow:none;background:#f7faff;text-align:left}
body.login #login #login_error{border-left-color:#0b63f6}
body.login #login label{margin-bottom:8px;font-size:12px;color:#5b6a85}
body.login #login form .input,body.login #login input[type=password],body.login #login input[type=text],body.login #login input[type=email]{min-height:47px;padding:8px 16px;border:1px solid #e3e9f4;border-radius:10px;box-shadow:none;font-size:14px;color:#061024}
body.login #login form .input:focus,body.login #login input:focus{border-color:#0b63f6;box-shadow:0 0 0 1px #0b63f6;outline:0}
body.login #login .button.wp-hide-pw .dashicons{color:#061024}
body.login #login .button.wp-hide-pw:focus{border-color:#0b63f6;box-shadow:0 0 0 1px #0b63f6}
body.login #login .forgetmenot{float:none}
body.login #login .submit{float:none}
body.login #login .button-primary{float:none;width:100%;min-height:47px;margin-top:20px;padding:0 24px;border:0;border-radius:9999px;background:linear-gradient(90deg,#1268ff 0%,#0a4fc4 100%);box-shadow:none;text-shadow:none;text-align:center;font-family:Poppins,system-ui,sans-serif;font-weight:600;line-height:1;transition:background .2s,filter .2s}
body.login #login .button-primary:hover,body.login #login .button-primary:focus{filter:brightness(1.12)}
body.login #login .button-primary:focus-visible{outline:2px solid #0b63f6;outline-offset:2px}
body.login #login #nav,body.login #login #backtoblog{margin:16px 0 0;padding:0;text-align:center}
body.login #login #nav a,body.login #login #backtoblog a,body.login #login #nav .button-link{color:#061024}
body.login #login #nav .button-link{font-size:13px;text-decoration:underline;cursor:pointer}
body.login #login #nav a:hover,body.login #login #backtoblog a:hover,body.login #login #nav .button-link:hover{color:#0b63f6}
body.login #login .privacy-policy-page-link{display:none}
body.login #login #taxeon_2fa_code{font-size:24px;letter-spacing:.3em;text-align:center}
body.login #login .cf-turnstile{margin:0 0 16px}
body.login input[type=checkbox]{border-color:#e3e9f4}
body.login input[type=checkbox]:checked{background:#0b63f6;border-color:#0b63f6}
body.login input[type=checkbox]:checked::before{filter:brightness(0) invert(1)}
.login-page-info{position:absolute;z-index:1;left:0;bottom:40px;width:58%;box-sizing:border-box;padding:20px 40px}
.login-page-info p{margin:0 0 10px;color:#fff;font-size:14px}
.login-page-info a{color:#fff;font-size:14px;text-decoration:none;opacity:.55;transition:opacity .2s}
.login-page-info a:hover,.login-page-info a:focus{opacity:1}
@media (max-width:1200px){
body.login{padding:180px 20px 60px}
body.login::before{width:140%;height:140%;left:-20%;top:-20%}
body.login::after{width:240px;aspect-ratio:auto;height:60px;left:50%;top:70px;transform:translate(-50%,0);background-size:contain}
body.login #login{width:100%;max-width:600px;min-height:auto;margin:auto;padding:32px 24px;border-radius:16px}
body.login #login::before{margin-bottom:16px;font-size:22px}
.login-page-info{position:relative;left:auto;bottom:auto;width:100%;margin-top:40px;padding:0;text-align:center}
}
CSS;

		wp_register_style('taxeon-login', false, [], null);
		wp_enqueue_style('taxeon-login');
		wp_add_inline_style('taxeon-login', $css);
	}

	public function info(): void
	{
		printf(
			'<div class="login-page-info"><p>%s</p><a href="mailto:%2$s">%2$s</a></div>',
			esc_html__('Potrzebujesz pomocy?', 'taxeon'),
			esc_attr(self::SUPPORT_EMAIL)
		);
	}

	private function title(): string
	{
		$action = (string) ($_REQUEST['action'] ?? '');

		return match (true) {
			$action === 'taxeon_2fa' => __('Kod weryfikacyjny', 'taxeon'),
			$action === 'lostpassword' || $action === 'retrievepassword' => __('Odzyskiwanie hasła', 'taxeon'),
			$action === 'rp' || $action === 'resetpass' => __('Nowe hasło', 'taxeon'),
			default => __('Panel strony', 'taxeon'),
		};
	}

	private function fontFaces(): string
	{
		$base = get_template_directory_uri() . '/dist/fonts/';
		$css = '';

		foreach (self::FONTS as $rodzina => $plik) {
			$css .= '@font-face{font-family:"' . $rodzina . '";font-style:normal;font-weight:400 700;font-display:swap;'
				. 'src:url("' . esc_url($base . $plik) . '") format("woff2");}';
		}

		return $css;
	}

	private const FONTS = ['Poppins' => 'poppins-600-latin.woff2', 'IBM Plex Sans' => 'ibm-plex-sans-latin.woff2'];
}
