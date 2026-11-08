<?php

namespace App\Services;

use App\Services\Recaptcha\RecaptchaService;

class ContactFormAssets
{
	public function __construct(private RecaptchaService $recaptcha)
	{
		add_filter('wpcf7_load_js', [$this, 'hasForm']);
		add_filter('wpcf7_load_css', [$this, 'hasForm']);
		add_action('wp_enqueue_scripts', [$this, 'dequeueRecaptcha'], 100);
		add_filter('wpcf7_autop_or_not', '__return_false');
	}

	public function hasForm(): bool
	{
		if (!is_singular()) {
			return false;
		}

		$post = get_post();

		return $post instanceof \WP_Post && has_shortcode($post->post_content, 'contact-form-7');
	}

	public function dequeueRecaptcha(): void
	{
		if ($this->hasForm()) {
			return;
		}

		wp_dequeue_script('wpcf7-recaptcha');

		if (!$this->recaptcha->isEnabled()) {
			wp_dequeue_script('google-recaptcha');
		}
	}
}
