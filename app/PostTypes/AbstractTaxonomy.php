<?php

namespace App\PostTypes;

abstract class AbstractTaxonomy
{
	protected string $taxonomy = '';
	protected string $taxonomySlug = '';

	/** @var array<int, string> */
	protected array $postTypes = [];

	protected string $singular = '';
	protected string $plural = '';
	protected bool $hierarchical = true;
	protected bool $public = true;
	protected bool $showInRest = true;
	protected bool $showAdminColumn = true;

	public function __construct()
	{
		add_action('init', [$this, 'registerTaxonomy']);
	}

	public function registerTaxonomy(): void
	{
		if ($this->taxonomy === '' || $this->postTypes === []) {
			return;
		}

		register_taxonomy($this->taxonomy, $this->postTypes, $this->args());
	}

	protected function args(): array
	{
		return [
			'labels' => $this->labels(),
			'public' => $this->public,
			'publicly_queryable' => $this->public,
			'show_ui' => true,
			'show_in_menu' => true,
			'show_in_nav_menus' => true,
			'show_in_rest' => $this->showInRest,
			'show_admin_column' => $this->showAdminColumn,
			'hierarchical' => $this->hierarchical,
			'rewrite' => [
				'slug' => $this->taxonomySlug !== '' ? $this->taxonomySlug : $this->taxonomy,
				'with_front' => false,
			],
		];
	}

	protected function labels(): array
	{
		$singular = $this->singular !== '' ? $this->singular : $this->taxonomy;
		$plural = $this->plural !== '' ? $this->plural : $singular;

		return [
			'name' => $plural,
			'singular_name' => $singular,
			'menu_name' => $plural,
			'all_items' => sprintf(__('Wszystkie: %s', 'taxeon'), $plural),
			'edit_item' => sprintf(__('Edytuj: %s', 'taxeon'), $singular),
			'view_item' => sprintf(__('Zobacz: %s', 'taxeon'), $singular),
			'update_item' => sprintf(__('Zapisz: %s', 'taxeon'), $singular),
			'add_new_item' => sprintf(__('Dodaj: %s', 'taxeon'), $singular),
			'new_item_name' => sprintf(__('Nazwa — %s', 'taxeon'), $singular),
			'search_items' => sprintf(__('Szukaj w: %s', 'taxeon'), $plural),
			'parent_item' => sprintf(__('Nadrzędna: %s', 'taxeon'), $singular),
			'parent_item_colon' => sprintf(__('Nadrzędna — %s:', 'taxeon'), $singular),
			'not_found' => __('Nie znaleziono.', 'taxeon'),
			'back_to_items' => sprintf(__('Wróć do: %s', 'taxeon'), $plural),
		];
	}
}
