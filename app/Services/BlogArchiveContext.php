<?php

namespace App\Services;

use Timber\Timber;
use WP_Query;

class BlogArchiveContext
{
	public static int $gridPerPage = 9;

	private static array $featuredCache = [];

	public static function isBlogArchive(WP_Query $query): bool
	{
		return $query->is_home()
			|| $query->is_category()
			|| $query->is_tag()
			|| $query->is_author()
			|| $query->is_date();
	}

	public static function adjustMainQuery(WP_Query $query): void
	{
		if (is_admin() || !$query->is_main_query() || !self::isBlogArchive($query)) {
			return;
		}

		$query->set('posts_per_page', self::$gridPerPage);

		$featuredId = self::featuredId($query);

		if ($featuredId) {
			$query->set('post__not_in', [$featuredId]);
		}
	}

	public static function featuredId(WP_Query $query): ?int
	{
		$key = implode('|', [
			$query->is_home() ? 'home' : 'archive',
			(string) get_queried_object_id(),
			(string) $query->get('year'),
			(string) $query->get('monthnum'),
			(string) $query->get('day'),
		]);

		if (array_key_exists($key, self::$featuredCache)) {
			return self::$featuredCache[$key];
		}

		$vars = $query->query_vars;
		$vars['posts_per_page'] = 1;
		$vars['paged'] = 1;
		$vars['offset'] = 0;
		$vars['post__not_in'] = [];
		$vars['fields'] = 'ids';
		$vars['no_found_rows'] = true;
		$vars['ignore_sticky_posts'] = true;
		$vars['suppress_filters'] = true;

		$ids = get_posts($vars);

		return self::$featuredCache[$key] = !empty($ids) ? (int) $ids[0] : null;
	}

	public static function filterTerms(): array
	{
		return (array) ThemeCache::remember('menus', 'blog-filter-terms', function (): array {
			$terms = get_terms([
				'taxonomy' => 'category',
				'hide_empty' => true,
				'orderby' => 'count',
				'order' => 'DESC',
			]);

			if (is_wp_error($terms)) {
				return [];
			}

			return array_map(static function ($term): array {
				$color = function_exists('get_field')
					? (string) get_field('color', 'category_' . $term->term_id)
					: '';

				return [
					'id' => $term->term_id,
					'slug' => $term->slug,
					'name' => $term->name,
					'url' => get_term_link($term),
					'color' => $color,
					'count' => $term->count,
				];
			}, $terms);
		});
	}

	public function build(): array
	{
		$data = [];

		if (is_category()) {
			$data['archiveTitle'] = single_cat_title('', false);
			$data['archiveDescription'] = category_description();
			$data['archiveType'] = 'category';
			$data['archiveEyebrow'] = __('Kategoria', 'raks');
			$data['activeTerm'] = get_queried_object_id();
		} elseif (is_tag()) {
			$data['archiveTitle'] = single_tag_title('', false);
			$data['archiveDescription'] = tag_description();
			$data['archiveType'] = 'tag';
			$data['archiveEyebrow'] = __('Tag', 'raks');
			$data['activeTerm'] = get_queried_object_id();
		} elseif (is_author()) {
			$author = get_queried_object();
			$data['archiveTitle'] = $author ? $author->display_name : wp_strip_all_tags(get_the_archive_title());
			$data['archiveDescription'] = $author ? get_the_author_meta('description', $author->ID) : '';
			$data['archiveType'] = 'author';
			$data['archiveEyebrow'] = __('Autor', 'raks');
		} elseif (is_search()) {
			$data['archiveTitle'] = sprintf(__('Wyniki wyszukiwania: %s', 'raks'), get_search_query());
			$data['archiveDescription'] = '';
			$data['archiveType'] = 'search';
			$data['archiveEyebrow'] = __('Szukaj', 'raks');
		} elseif (is_home()) {
			$blogPageId = (int) get_option('page_for_posts');
			$archiveTitle = $blogPageId && function_exists('get_field')
				? trim((string) get_field('archive_title', $blogPageId))
				: '';

			$data['archiveTitle'] = $archiveTitle !== ''
				? $archiveTitle
				: ($blogPageId ? get_the_title($blogPageId) : __('Blog', 'raks'));
			$data['archiveDescription'] = $blogPageId && function_exists('get_field')
				? (string) get_field('archive_description', $blogPageId)
				: '';
			$data['archiveType'] = 'blog';
			$data['archiveEyebrow'] = __('Blog', 'raks');
			$data['isBlogHome'] = true;
		} else {
			$data['archiveTitle'] = wp_strip_all_tags(get_the_archive_title());
			$data['archiveDescription'] = get_the_archive_description();
			$data['archiveType'] = 'archive';
			$data['archiveEyebrow'] = __('Blog', 'raks');
		}

		$posts = Timber::get_posts();

		$data['posts'] = $posts;
		$data['isPaged'] = is_paged();
		$data['pagination'] = $posts->pagination(['mid_size' => 2]);
		$data['filterTerms'] = self::filterTerms();
		$data['blogUrl'] = ($id = (int) get_option('page_for_posts')) ? get_permalink($id) : home_url('/');
		$data['activeTerm'] = $data['activeTerm'] ?? 0;
		$data['maxPages'] = (int) ($GLOBALS['wp_query']->max_num_pages ?? 1);
		$data['currentPage'] = max(1, (int) get_query_var('paged'));

		$data['featuredPost'] = null;

		if (!is_paged() && !is_search()) {
			$featuredId = self::featuredId($GLOBALS['wp_query']);

			if ($featuredId) {
				$data['featuredPost'] = Timber::get_post($featuredId);
			}
		}

		return $data;
	}
}
