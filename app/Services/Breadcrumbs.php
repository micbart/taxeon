<?php

namespace App\Services;

class Breadcrumbs
{
	public static function build(): array
	{
		if (is_front_page()) {
			return [];
		}

		$crumbs = [
			['url' => home_url('/'), 'title' => __('Strona główna', 'raks')],
		];

		$blogPageId = (int) get_option('page_for_posts');

		if (is_singular('post')) {
			if ($blogPageId) {
				$crumbs[] = ['url' => get_permalink($blogPageId), 'title' => get_the_title($blogPageId)];
			}

			$categories = get_the_category(get_queried_object_id());

			if (!empty($categories)) {
				$crumbs[] = [
					'url' => get_category_link($categories[0]->term_id),
					'title' => $categories[0]->name,
				];
			}

			$crumbs[] = ['url' => '', 'title' => get_the_title()];

			return $crumbs;
		}

		if (is_singular()) {
			$post = get_queried_object();

			foreach (array_reverse(get_post_ancestors($post)) as $ancestorId) {
				$crumbs[] = ['url' => get_permalink($ancestorId), 'title' => get_the_title($ancestorId)];
			}

			$crumbs[] = ['url' => '', 'title' => get_the_title($post)];

			return $crumbs;
		}

		if (is_home()) {
			$crumbs[] = [
				'url' => '',
				'title' => $blogPageId ? get_the_title($blogPageId) : __('Blog', 'raks'),
			];

			return $crumbs;
		}

		if (is_category() || is_tag() || is_tax()) {
			if ($blogPageId && (is_category() || is_tag())) {
				$crumbs[] = ['url' => get_permalink($blogPageId), 'title' => get_the_title($blogPageId)];
			}

			$crumbs[] = ['url' => '', 'title' => single_term_title('', false)];

			return $crumbs;
		}

		if (is_search()) {
			$crumbs[] = [
				'url' => '',
				'title' => sprintf(__('Wyniki wyszukiwania: %s', 'raks'), get_search_query()),
			];

			return $crumbs;
		}

		if (is_404()) {
			$crumbs[] = ['url' => '', 'title' => __('Nie znaleziono strony', 'raks')];

			return $crumbs;
		}

		$crumbs[] = ['url' => '', 'title' => wp_strip_all_tags(get_the_archive_title())];

		return $crumbs;
	}
}
