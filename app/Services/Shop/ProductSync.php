<?php

namespace App\Services\Shop;

class ProductSync
{
	public const POST_TYPE = 'produkt';
	public const TAXONOMY = 'kategoria-produktu';

	private const SCALAR_FIELDS = [
		'sku' => '_shop_sku',
		'type' => '_shop_type',
		'permalink' => '_shop_permalink',
		'price' => '_shop_price',
		'regular_price' => '_shop_regular_price',
		'sale_price' => '_shop_sale_price',
		'price_html' => '_shop_price_html',
		'regular_price_html' => '_shop_regular_price_html',
		'currency' => '_shop_currency',
		'purchasable' => '_shop_purchasable',
		'in_stock' => '_shop_in_stock',
		'virtual' => '_shop_virtual',
		'listed' => '_shop_listed',
		'training_date' => '_shop_training_date',
		'training_time' => '_shop_training_time',
		'training_duration' => '_shop_training_duration',
		'training_trainer' => '_shop_training_trainer',
		'is_training' => '_shop_is_training',
	];

	/**
	 * @param array<string, mixed> $payload
	 * @return array{action: string, post_id: int}
	 */
	public function apply(array $payload): array
	{
		$shopId = (int) ($payload['id'] ?? 0);

		if ($shopId <= 0) {
			throw new \InvalidArgumentException(__('Brak identyfikatora produktu.', 'raks'));
		}

		$existing = $this->findByShopId($shopId);
		$status = (string) ($payload['status'] ?? 'publish');

		if (in_array($status, ['trash', 'deleted'], true)) {
			if ($existing) {
				wp_trash_post($existing);

				return ['action' => 'trashed', 'post_id' => $existing];
			}

			return ['action' => 'skipped', 'post_id' => 0];
		}

		if (($payload['listed'] ?? 'yes') !== 'yes' && !$existing) {
			return ['action' => 'skipped', 'post_id' => 0];
		}

		$postData = [
			'post_type' => self::POST_TYPE,
			'post_title' => sanitize_text_field((string) ($payload['name'] ?? '')),
			'post_content' => wp_kses_post((string) ($payload['description'] ?? '')),
			'post_excerpt' => wp_kses_post((string) ($payload['short_description'] ?? '')),
			'post_status' => $status === 'publish' ? 'publish' : 'draft',
		];

		if (!$existing) {
			$postData['post_name'] = sanitize_title((string) ($payload['slug'] ?? ''));
		}

		if ($existing) {
			if (get_post_status($existing) === 'trash') {
				wp_untrash_post($existing);
			}

			$postData['ID'] = $existing;
			$postId = (int) wp_update_post(wp_slash($postData), true);
			$action = 'updated';
		} else {
			$postId = (int) wp_insert_post(wp_slash($postData), true);
			$action = 'created';
		}

		if (is_wp_error($postId) || $postId === 0) {
			throw new \RuntimeException(__('Nie udało się zapisać produktu.', 'raks'));
		}

		update_post_meta($postId, '_shop_id', $shopId);
		update_post_meta($postId, '_shop_synced_at', current_time('mysql'));

		foreach (self::SCALAR_FIELDS as $klucz => $meta) {
			$wartosc = $payload[$klucz] ?? '';
			update_post_meta($postId, $meta, is_scalar($wartosc) ? sanitize_text_field((string) $wartosc) : '');
		}

		$this->applyChecklist($postId, $payload['checklist'] ?? []);
		$this->applyCategories($postId, (array) ($payload['categories'] ?? []));
		$this->applyImage($postId, (string) ($payload['image'] ?? ''), (string) ($payload['permalink'] ?? ''));

		if (($payload['listed'] ?? 'yes') !== 'yes') {
			$action = 'unlisted';
		}

		return ['action' => $action, 'post_id' => $postId];
	}

	public function findByShopId(int $shopId): ?int
	{
		$found = get_posts([
			'post_type' => self::POST_TYPE,
			'post_status' => array_merge(array_keys(get_post_stati()), ['trash']),
			'posts_per_page' => 1,
			'fields' => 'ids',
			'meta_key' => '_shop_id',
			'meta_value' => $shopId,
		]);

		return $found ? (int) $found[0] : null;
	}

	/** @param array<string, mixed> $checklist */
	private function applyChecklist(int $postId, $checklist): void
	{
		$checklist = (array) $checklist;
		$items = array_values(array_filter(array_map(
			static fn($x): string => sanitize_text_field((string) $x),
			(array) ($checklist['items'] ?? [])
		)));

		update_post_meta($postId, '_shop_checklist_title', sanitize_text_field((string) ($checklist['title'] ?? '')));
		update_post_meta($postId, '_shop_checklist', $items);
	}

	/** @param array<int, array<string, string>> $categories */
	private function applyCategories(int $postId, array $categories): void
	{
		$termIds = [];

		foreach ($categories as $category) {
			$slug = sanitize_title((string) ($category['slug'] ?? ''));
			$name = sanitize_text_field((string) ($category['name'] ?? ''));

			if ($slug === '' || $name === '') {
				continue;
			}

			$term = get_term_by('slug', $slug, self::TAXONOMY);

			if (!$term) {
				$created = wp_insert_term($name, self::TAXONOMY, ['slug' => $slug]);

				if (is_wp_error($created)) {
					continue;
				}

				$termIds[] = (int) $created['term_id'];
				continue;
			}

			$termIds[] = (int) $term->term_id;
		}

		wp_set_object_terms($postId, $termIds, self::TAXONOMY);
	}

	private function applyImage(int $postId, string $imageUrl, string $permalink): void
	{
		if ($imageUrl === '' || !$this->sameHost($imageUrl, $permalink)) {
			return;
		}

		if (get_post_meta($postId, '_shop_image_source', true) === $imageUrl && has_post_thumbnail($postId)) {
			return;
		}

		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$attachmentId = media_sideload_image($imageUrl, $postId, null, 'id');

		if (is_wp_error($attachmentId)) {
			return;
		}

		set_post_thumbnail($postId, (int) $attachmentId);
		update_post_meta($postId, '_shop_image_source', esc_url_raw($imageUrl));
	}

	private function sameHost(string $imageUrl, string $permalink): bool
	{
		$image = wp_parse_url($imageUrl, PHP_URL_HOST);
		$shop = wp_parse_url($permalink !== '' ? $permalink : (string) $this->shopUrl(), PHP_URL_HOST);

		return $image !== null && $shop !== null && strcasecmp($image, $shop) === 0;
	}

	private function shopUrl(): string
	{
		return (string) apply_filters('raks_shop_url', (string) get_option('raks_shop_url', ''));
	}
}
