<?php

namespace App\Providers;

class PostTypeSupports
{
	/** @var array<int, class-string<\App\PostTypes\AbstractTaxonomy>> */
	private const TAXONOMIES = [
		\App\PostTypes\KategoriaProduktu::class,
	];

	/** @var array<int, class-string<\App\PostTypes\AbstractPostType>> */
	private const POST_TYPES = [
		\App\PostTypes\Produkt::class,
	];

	public function __construct()
	{
		foreach (self::TAXONOMIES as $taxonomy) {
			new $taxonomy();
		}

		foreach (self::POST_TYPES as $postType) {
			new $postType();
		}
	}
}
