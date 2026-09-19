<?php

namespace App\Api;

use App\Services\BlogArchiveContext;
use Timber\Timber;
use WP_REST_Request;
use WP_REST_Response;

class ApiPosts extends AbstractApi
{
	protected string $route = '/posts';
	protected string $methods = 'GET';

	protected function args(): array
	{
		return [
			'category' => ['type' => 'string', 'default' => ''],
			'page' => ['type' => 'integer', 'default' => 1],
			's' => ['type' => 'string', 'default' => ''],
		];
	}

	public function handle(WP_REST_Request $request): WP_REST_Response
	{
		$category = sanitize_title((string) $request->get_param('category'));
		$page = max(1, (int) $request->get_param('page'));
		$search = sanitize_text_field((string) $request->get_param('s'));

		$args = [
			'post_type' => 'post',
			'post_status' => 'publish',
			'posts_per_page' => BlogArchiveContext::$gridPerPage,
			'paged' => $page,
			'ignore_sticky_posts' => true,
		];

		if ($category !== '' && $category !== 'all') {
			$args['category_name'] = $category;
		}

		if ($search !== '') {
			$args['s'] = $search;
		}

		$query = new \WP_Query($args);
		$posts = Timber::get_posts($query);

		$html = '';

		foreach ($posts as $index => $post) {
			$html .= Timber::compile('components/post-card.twig', [
				'post' => $post,
				'eagerFirst' => $index === 0 && $page === 1,
			]);
		}

		return $this->success([
			'html' => $html,
			'found' => (int) $query->found_posts,
			'page' => $page,
			'maxPages' => (int) $query->max_num_pages,
			'emptyMessage' => __('Nie znaleziono wpisów w tej kategorii.', 'taxeon'),
		]);
	}
}
