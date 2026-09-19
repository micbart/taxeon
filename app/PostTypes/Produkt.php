<?php

namespace App\PostTypes;

class Produkt extends AbstractPostType
{
	protected string $postType = 'produkt';
	protected string $postTypeSlug = 'sklep';
	protected string $singular = 'Produkt';
	protected string $plural = 'Produkty ze sklepu';
	protected string $menuIcon = 'dashicons-cart';
	protected int $menuPosition = 23;
	protected bool $hasArchive = false;
	protected array $supports = ['title', 'thumbnail', 'excerpt'];
	protected array $taxonomies = ['kategoria-produktu'];
}
