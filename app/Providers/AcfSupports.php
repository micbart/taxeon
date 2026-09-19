<?php

namespace App\Providers;

use App\Blocks\AcfTimberBlocks;

class AcfSupports
{
	public function __construct()
	{
		if (!class_exists('ACF')) {
			$this->viewInfoAboutInstallPlugin();

			return;
		}

		$this->filters();
		$this->customRules();
		$this->pageOptions();

		new AcfTimberBlocks();
	}

	public function filters(): void
	{
		add_filter('acf/settings/save_json', [$this, 'acfJsonSave']);
		add_filter('acf/settings/load_json', [$this, 'acfJsonLoad']);
		add_filter('acf/load_field/name=select_menu', [$this, 'addListMenuToChoicesField']);
		add_filter('acf/load_field/name=icon', [$this, 'addIconsToChoicesField']);
	}

	public function viewInfoAboutInstallPlugin(): void
	{
		add_action('admin_notices', function (): void {
			echo '<div class="error"><p>Wtyczka ACF PRO nie jest zainstalowana lub aktywna.</p></div>';
		});

		add_filter('template_include', function (): string {
			return get_stylesheet_directory() . '/static/no-acf.html';
		});
	}

	public function acfJsonSave(): string
	{
		return get_stylesheet_directory() . '/acf-json';
	}

	public function acfJsonLoad($paths): mixed
	{
		unset($paths[0]);
		$paths[] = get_stylesheet_directory() . '/acf-json';

		return $paths;
	}

	public function customRules(): void
	{
		add_filter('acf/location/rule_types', function ($choices) {
			$choices['Custom']['no_location'] = 'No Location';

			return $choices;
		});

		add_filter('acf/location/rule_match/no_location', function () {
			return false;
		}, 10, 3);
	}

	public function pageOptions(): void
	{
		if (!function_exists('acf_add_options_page')) {
			return;
		}

		acf_add_options_page([
			'page_title' => __('Opcje motywu', 'raks'),
			'menu_title' => __('Opcje motywu', 'raks'),
			'menu_slug' => 'theme-options',
			'capability' => 'manage_options',
			'redirect' => false,
			'icon_url' => 'dashicons-admin-generic',
			'position' => 59,
		]);
	}

	public function addListMenuToChoicesField($field)
	{
		$field['choices'] = [];

		foreach (wp_get_nav_menus() as $menu) {
			$field['choices'][$menu->term_id] = $menu->name;
		}

		return $field;
	}

	public function addIconsToChoicesField($field)
	{
		if (($field['type'] ?? '') !== 'select') {
			return $field;
		}

		$dir = get_template_directory() . '/dist/svg';

		if (!is_dir($dir)) {
			$dir = get_template_directory() . '/src/svg';
		}

		$choices = ['' => __('— brak —', 'raks')];

		foreach (glob($dir . '/*.svg') ?: [] as $file) {
			$name = basename($file, '.svg');
			$choices[$name] = $name;
		}

		$field['choices'] = $choices;

		return $field;
	}
}
