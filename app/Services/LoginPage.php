<?php

namespace App\Services;

class LoginPage
{
	public function __construct()
	{
		add_action('login_enqueue_scripts', [$this, 'styles']);
		add_filter('login_headerurl', fn() => home_url('/'));
		add_filter('login_headertext', fn() => get_bloginfo('name'));
		add_filter('login_errors', [$this, 'genericError']);
	}

	public function styles(): void
	{
		$logo = get_template_directory_uri() . '/images/logo.png';
		?>
		<style>
			:root {
				--raks-accent: #00053d;
				--raks-accent-strong: #161c66;
				--raks-cta: #e95420;
				--raks-ink: #15202c;
				--raks-line: #e7ebf0;
			}

			body.login {
				background: #f5f7fa;
				font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
			}

			.login h1 a {
				background-image: url('<?php echo esc_url($logo); ?>');
				background-size: contain;
				background-position: center;
				width: 200px;
				height: 56px;
			}

			.login form {
				border: 1px solid var(--raks-line);
				border-radius: 18px;
				box-shadow: 0 8px 30px rgba(21, 32, 44, .08);
				padding: 28px 24px;
			}

			.login input[type="text"],
			.login input[type="password"] {
				border-radius: 10px;
				border: 1.5px solid var(--raks-line);
				padding: 10px 12px;
			}

			.login input[type="text"]:focus,
			.login input[type="password"]:focus {
				border-color: var(--raks-accent);
				box-shadow: 0 0 0 3px rgba(0, 5, 61, .12);
			}

			.wp-core-ui .button-primary {
				background: var(--raks-cta);
				border-color: var(--raks-cta);
				border-radius: 10px;
				text-shadow: none;
				box-shadow: none;
				font-weight: 700;
			}

			.wp-core-ui .button-primary:hover,
			.wp-core-ui .button-primary:focus {
				background: #d2491a;
				border-color: #d2491a;
			}

			.login #nav a,
			.login #backtoblog a {
				color: var(--raks-ink);
			}

			.login #nav a:hover,
			.login #backtoblog a:hover {
				color: var(--raks-accent);
			}
		</style>
		<?php
	}

	public function genericError(): string
	{
		return __('Nieprawidłowe dane logowania.', 'raks');
	}
}
