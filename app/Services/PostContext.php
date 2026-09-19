<?php

namespace App\Services;

use Timber\Post;
use Timber\Timber;

class PostContext
{
	public function build(Post $post): array
	{
		$data = [
			'isPost' => true,
			'relatedPosts' => [],
		];

		$postCategories = wp_get_post_categories($post->ID, ['fields' => 'ids']);

		if (!empty($postCategories)) {
			$data['relatedPosts'] = Timber::get_posts([
				'post_type' => 'post',
				'posts_per_page' => 2,
				'post__not_in' => [$post->ID],
				'category__in' => $postCategories,
				'orderby' => 'date',
				'order' => 'DESC',
				'ignore_sticky_posts' => true,
				'no_found_rows' => true,
			]);
		}

		$data['shareLinks'] = $this->shareLinks($post);

		return $data;
	}

	private function shareLinks(Post $post): array
	{
		$url = rawurlencode((string) $post->link());
		$title = rawurlencode((string) $post->title());

		return [
			[
				'icon' => 'facebook',
				'label' => __('Udostępnij na Facebooku', 'taxeon'),
				'url' => "https://www.facebook.com/sharer/sharer.php?u={$url}",
			],
			[
				'icon' => 'linkedin',
				'label' => __('Udostępnij na LinkedIn', 'taxeon'),
				'url' => "https://www.linkedin.com/sharing/share-offsite/?url={$url}",
			],
			[
				'icon' => 'mail',
				'label' => __('Wyślij e-mailem', 'taxeon'),
				'url' => "mailto:?subject={$title}&body={$url}",
			],
		];
	}
}
