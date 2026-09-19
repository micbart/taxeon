<?php

namespace App\PostTypes;

abstract class AbstractPostType
{
	protected string $postType = '';
	protected string $postTypeSlug = '';
	protected string $singular = '';
	protected string $plural = '';
	protected string $menuIcon = 'dashicons-admin-post';
	protected int $menuPosition = 25;
	protected bool $hasArchive = false;
	protected bool $public = true;
	protected bool $showInRest = true;
	protected array $supports = ['title', 'editor', 'thumbnail', 'excerpt', 'revisions'];
	protected array $taxonomies = [];

	public function __construct()
	{
		add_action('init', [$this, 'registerPostType']);
	}

	public function registerPostType(): void
	{
		if ($this->postType === '') {
			return;
		}

		register_post_type($this->postType, $this->args());
	}

	protected function args(): array
	{
		return [
			'labels' => $this->labels(),
			'public' => $this->public,
			'show_ui' => true,
			'show_in_menu' => true,
			'show_in_nav_menus' => true,
			'show_in_admin_bar' => true,
			'show_in_rest' => $this->showInRest,
			'can_export' => true,
			'has_archive' => $this->hasArchive,
			'exclude_from_search' => false,
			'publicly_queryable' => $this->public,
			'capability_type' => 'post',
			'menu_icon' => $this->menuIcon,
			'menu_position' => $this->menuPosition,
			'supports' => $this->supports,
			'taxonomies' => $this->taxonomies,
			'rewrite' => [
				'slug' => $this->postTypeSlug !== '' ? $this->postTypeSlug : $this->postType,
				'with_front' => false,
			],
		];
	}

	protected function labels(): array
	{
		$singular = $this->singular !== '' ? $this->singular : $this->postType;
		$plural = $this->plural !== '' ? $this->plural : $singular;

		return [
			'name' => $plural,
			'singular_name' => $singular,
			'menu_name' => $plural,
			'all_items' => sprintf(__('Wszystkie: %s', 'taxeon'), $plural),
			'add_new' => __('Dodaj nowy', 'taxeon'),
			'add_new_item' => sprintf(__('Dodaj: %s', 'taxeon'), $singular),
			'edit_item' => sprintf(__('Edytuj: %s', 'taxeon'), $singular),
			'new_item' => sprintf(__('Nowy: %s', 'taxeon'), $singular),
			'view_item' => sprintf(__('Zobacz: %s', 'taxeon'), $singular),
			'view_items' => sprintf(__('Zobacz: %s', 'taxeon'), $plural),
			'search_items' => sprintf(__('Szukaj w: %s', 'taxeon'), $plural),
			'not_found' => __('Nie znaleziono.', 'taxeon'),
			'not_found_in_trash' => __('Nie znaleziono w koszu.', 'taxeon'),
			'parent_item_colon' => __('Rodzic:', 'taxeon'),
			'featured_image' => __('Obrazek wyróżniający', 'taxeon'),
			'set_featured_image' => __('Ustaw obrazek wyróżniający', 'taxeon'),
			'archives' => sprintf(__('Archiwum: %s', 'taxeon'), $plural),
		];
	}
}
