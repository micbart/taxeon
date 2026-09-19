<?php

namespace App\Services\Shop;

class ProductQuery
{
	public const SORT_BY_DATE = 'raks_shop_sort_by_date';

	public function __construct()
	{
		add_action('pre_get_posts', [$this, 'filterArchive']);
		add_filter('posts_clauses', [$this, 'sortByTrainingDate'], 10, 2);
	}

	/**
	 * @param array<string, string> $clauses
	 * @return array<string, string>
	 */
	public function sortByTrainingDate(array $clauses, \WP_Query $query): array
	{
		if (!$query->get(self::SORT_BY_DATE)) {
			return $clauses;
		}

		global $wpdb;

		$clauses['join'] .= " LEFT JOIN {$wpdb->postmeta} AS raks_termin"
			. " ON (raks_termin.post_id = {$wpdb->posts}.ID"
			. " AND raks_termin.meta_key = '_shop_training_date')";

		$clauses['orderby'] = "COALESCE(NULLIF(raks_termin.meta_value, ''), '9999-12-31') ASC,"
			. " {$wpdb->posts}.post_title ASC";

		return $clauses;
	}

	/** @return array<string, mixed> */
	public static function availableMetaQuery(): array
	{
		$today = current_time('Y-m-d');

		return [
			'relation' => 'AND',
			[
				'relation' => 'OR',
				['key' => '_shop_training_date', 'compare' => 'NOT EXISTS'],
				['key' => '_shop_training_date', 'value' => '', 'compare' => '='],
				['key' => '_shop_training_date', 'value' => $today, 'compare' => '>=', 'type' => 'DATE'],
			],
			[
				'relation' => 'OR',
				['key' => '_shop_in_stock', 'compare' => 'NOT EXISTS'],
				['key' => '_shop_in_stock', 'value' => 'no', 'compare' => '!='],
			],
			[
				'relation' => 'OR',
				['key' => '_shop_listed', 'compare' => 'NOT EXISTS'],
				['key' => '_shop_listed', 'value' => 'no', 'compare' => '!='],
			],
		];
	}

	public function filterArchive(\WP_Query $query): void
	{
		if (is_admin() || !$query->is_main_query() || !$query->is_tax(ProductSync::TAXONOMY)) {
			return;
		}

		$query->set(self::SORT_BY_DATE, true);

		if (!apply_filters('raks_shop_only_available', true)) {
			return;
		}

		$existing = $query->get('meta_query') ?: [];
		$query->set('meta_query', $existing ? ['relation' => 'AND', $existing, self::availableMetaQuery()] : self::availableMetaQuery());
	}
}
