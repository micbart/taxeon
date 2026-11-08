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

		$data['relatedPosts'] = $this->related($post);

		$data['shareLinks'] = $this->shareLinks($post);

		$toc = [];
		$data['postContent'] = $this->withAnchors((string) $post->content(), $toc);
		$data['toc'] = $toc;

		return $data;
	}

	private function related(Post $post, int $count = 3): array
	{
		$base = [
			'post_type' => 'post',
			'orderby' => 'date',
			'order' => 'DESC',
			'ignore_sticky_posts' => true,
			'no_found_rows' => true,
			'fields' => 'ids',
		];

		$ids = [];
		$categories = wp_get_post_categories($post->ID, ['fields' => 'ids']);

		if ($categories) {
			$ids = get_posts($base + [
				'posts_per_page' => $count,
				'post__not_in' => [$post->ID],
				'category__in' => $categories,
			]);
		}

		if (count($ids) < $count) {
			$ids = array_merge($ids, get_posts($base + [
				'posts_per_page' => $count - count($ids),
				'post__not_in' => array_merge([$post->ID], $ids),
			]));
		}

		if (!$ids) {
			return [];
		}

		return iterator_to_array(Timber::get_posts([
			'post_type' => 'post',
			'post__in' => $ids,
			'orderby' => 'post__in',
			'posts_per_page' => $count,
		]));
	}

	/** @param array<int, array{id: string, title: string}> $toc */
	private function withAnchors(string $content, array &$toc): string
	{
		return (string) preg_replace_callback(
			'/<h2([^>]*)>(.*?)<\\/h2>/is',
			function (array $match) use (&$toc): string {
				$title = trim(wp_strip_all_tags($match[2]));
				$id = sanitize_title($title);

				if ($id === '' || str_contains($match[1], 'id=')) {
					return $match[0];
				}

				$toc[] = ['id' => $id, 'title' => $title];

				return '<h2' . $match[1] . ' id="' . esc_attr($id) . '">' . $match[2] . '</h2>';
			},
			$content
		);
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
