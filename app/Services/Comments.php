<?php

namespace App\Services;

/**
 * Wyłącza komentarze w całym serwisie, zostawiając opinie o produktach
 * WooCommerce — te siedzą w tej samej tabeli, ale mają typ „review”.
 */
class Comments
{
	public function __construct()
	{
		add_action('init', [$this, 'odepnijWsparcie'], 100);

		add_filter('comments_open', [$this, 'czyOtwarte'], 20, 2);
		add_filter('pings_open', [$this, 'czyOtwarte'], 20, 2);
		add_filter('comments_array', [$this, 'ukryjIstniejace'], 20, 2);
		add_filter('preprocess_comment', [$this, 'odrzucWyslane'], 1);

		add_action('admin_menu', [$this, 'schowajMenu']);
		add_action('admin_init', [$this, 'przekierujEkran']);
		add_action('wp_before_admin_bar_render', [$this, 'schowajPasek']);

		add_filter('rest_endpoints', [$this, 'ograniczRest']);
		add_filter('xmlrpc_methods', [$this, 'ograniczXmlrpc']);
		add_filter('feed_links_show_comments_feed', '__return_false');
		add_action('template_redirect', [$this, 'zablokujKanal'], 1);
	}

	public function zablokujKanal(): void
	{
		if (!is_comment_feed() || $this->sklepDziala()) {
			return;
		}

		wp_safe_redirect(home_url('/feed/'), 301);
		exit;
	}

	public function odepnijWsparcie(): void
	{
		foreach (get_post_types() as $typ) {
			if ($this->toProdukt($typ)) {
				continue;
			}

			remove_post_type_support($typ, 'comments');
			remove_post_type_support($typ, 'trackbacks');
		}

		update_option('default_comment_status', 'closed');
		update_option('default_ping_status', 'closed');
	}

	/** @param int|\WP_Post|null $post */
	public function czyOtwarte(bool $otwarte, $post = null): bool
	{
		return $this->toProdukt(get_post_type($post)) ? $otwarte : false;
	}

	/**
	 * @param array<int, \WP_Comment> $komentarze
	 * @return array<int, \WP_Comment>
	 */
	public function ukryjIstniejace(array $komentarze, int $postId): array
	{
		return $this->toProdukt(get_post_type($postId)) ? $komentarze : [];
	}

	/**
	 * @param array<string, mixed> $dane
	 * @return array<string, mixed>
	 */
	public function odrzucWyslane(array $dane): array
	{
		$typ = get_post_type((int) ($dane['comment_post_ID'] ?? 0));

		if ($this->toProdukt($typ)) {
			return $dane;
		}

		wp_die(
			esc_html__('Komentarze są wyłączone.', 'taxeon'),
			esc_html__('Komentarze wyłączone', 'taxeon'),
			['response' => 403]
		);
	}

	public function schowajMenu(): void
	{
		if ($this->sklepDziala()) {
			return;
		}

		remove_menu_page('edit-comments.php');
		remove_submenu_page('options-general.php', 'options-discussion.php');
	}

	public function przekierujEkran(): void
	{
		global $pagenow;

		if ($this->sklepDziala()) {
			return;
		}

		if (in_array($pagenow, ['edit-comments.php', 'options-discussion.php'], true)) {
			wp_safe_redirect(admin_url(), 301);
			exit;
		}
	}

	public function schowajPasek(): void
	{
		if ($this->sklepDziala()) {
			return;
		}

		global $wp_admin_bar;
		$wp_admin_bar->remove_node('comments');
	}

	/**
	 * @param array<string, mixed> $endpointy
	 * @return array<string, mixed>
	 */
	public function ograniczRest(array $endpointy): array
	{
		if ($this->sklepDziala() || is_user_logged_in()) {
			return $endpointy;
		}

		unset($endpointy['/wp/v2/comments'], $endpointy['/wp/v2/comments/(?P<id>[\d]+)']);

		return $endpointy;
	}

	/**
	 * @param array<string, mixed> $metody
	 * @return array<string, mixed>
	 */
	public function ograniczXmlrpc(array $metody): array
	{
		unset($metody['wp.newComment'], $metody['wp.getComments'], $metody['pingback.ping']);

		return $metody;
	}

	private function toProdukt(?string $typ): bool
	{
		return $typ === 'product' && $this->sklepDziala();
	}

	private function sklepDziala(): bool
	{
		return class_exists('WooCommerce');
	}
}
