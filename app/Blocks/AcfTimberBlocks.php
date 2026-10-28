<?php

namespace App\Blocks;

use App\Setup\Vite;
use DirectoryIterator;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use Timber\Timber;

class AcfTimberBlocks
{
	private const DEFAULT_BLOCK_VERSION = 3;

	/** @var array<string, array{style: ?string, script: ?string, deps: array}> */
	private static array $blockAssets = [];

	private static int $blockPosition = 0;

	public function __construct()
	{
		add_action('acf/init', [__CLASS__, 'timberBlockInit'], 10, 0);
		add_action('enqueue_block_editor_assets', [__CLASS__, 'enqueueBlockEditorAssets']);
		add_action('enqueue_block_assets', [__CLASS__, 'enqueueEditorCanvasAssets']);
		add_filter('block_categories_all', [__CLASS__, 'registerBlockCategory'], 10, 1);
	}

	public static function registerBlockCategory(array $categories): array
	{
		array_unshift($categories, [
			'slug' => 'taxeon',
			'title' => __('Taxeon', 'taxeon'),
		]);

		return $categories;
	}

	public static function enqueueBlockEditorAssets(): void
	{
		if (function_exists('acf_enqueue_uploader')) {
			acf_enqueue_uploader();
		}
	}

	public static function enqueueEditorCanvasAssets(): void
	{
		if (!is_admin() || wp_should_load_block_editor_scripts_and_styles()) {
			return;
		}

		Vite::enqueueEntryCss('src/js/main.js', 'taxeon-canvas-main');
		Vite::enqueueStyle('src/scss/editor-canvas.scss', 'taxeon-editor-canvas');

		foreach (self::$blockAssets as $slug => $assets) {
			$editorEntry = "src/blocks/{$slug}/{$slug}-editor.scss";

			if (file_exists(get_template_directory() . '/' . $editorEntry)) {
				Vite::enqueueStyle($editorEntry, "taxeon-block-editor-{$slug}");
			}

			if ($assets['style'] && str_starts_with($assets['style'], 'src/')) {
				Vite::enqueueStyle($assets['style'], 'taxeon-block-' . $slug);
			} elseif ($assets['style']) {
				wp_enqueue_style('taxeon-block-' . $slug, $assets['style'], [], null);
			}
		}
	}

	/** @return array<string, mixed> */
	private static function exampleFields(string $slug): array
	{
		static $examples = null;

		if ($examples === null) {
			$path = get_template_directory() . '/app/Blocks/examples.php';
			$examples = file_exists($path) ? (array) require $path : [];
		}

		return (array) ($examples[$slug] ?? []);
	}

	public static function timberBlockInit(): void
	{
		$directories = self::timberBlockDirectoryGetter();

		foreach ($directories as $dir) {
			if (!file_exists(locate_template($dir))) {
				continue;
			}

			$templateDirectory = new DirectoryIterator(locate_template($dir));

			foreach ($templateDirectory as $template) {
				if ($template->isDot() || $template->isDir()) {
					continue;
				}

				$fileParts = pathinfo($template->getFilename());

				if (($fileParts['extension'] ?? '') !== 'twig') {
					continue;
				}

				$slug = $fileParts['filename'];
				$filePath = locate_template($dir . "/$slug.twig");

				$fileHeaders = get_file_data(
					$filePath,
					[
						'title' => 'Title',
						'description' => 'Description',
						'category' => 'Category',
						'icon' => 'Icon',
						'keywords' => 'Keywords',
						'mode' => 'Mode',
						'align' => 'Align',
						'post_types' => 'PostTypes',
						'supports_align' => 'SupportsAlign',
						'supports_align_content' => 'SupportsAlignContent',
						'supports_mode' => 'SupportsMode',
						'supports_multiple' => 'SupportsMultiple',
						'supports_anchor' => 'SupportsAnchor',
						'enqueue_style' => 'EnqueueStyle',
						'enqueue_script' => 'EnqueueScript',
						'enqueue_script_deps' => 'EnqueueScriptDeps',
						'supports_custom_class_name' => 'SupportsCustomClassName',
						'supports_reusable' => 'SupportsReusable',
						'supports_full_height' => 'SupportsFullHeight',
						'example' => 'Example',
						'parent' => 'Parent',
						'acf_block_version' => 'ACFBlockVersion',
						'hide_fields_in_sidebar' => 'HideFieldsInSidebar',
						'expanded_editor_buttons' => 'ExpandedEditorButtons',
						'expanded_editor_button_text' => 'ExpandedEditorButtonText',
					]
				);

				if (empty($fileHeaders['title']) || empty($fileHeaders['category'])) {
					continue;
				}

				$blockVersion = $fileHeaders['acf_block_version'] !== ''
					? (int) $fileHeaders['acf_block_version']
					: self::DEFAULT_BLOCK_VERSION;

				$data = [
					'name' => $slug,
					'title' => $fileHeaders['title'],
					'description' => $fileHeaders['description'],
					'category' => $fileHeaders['category'],
					'icon' => $fileHeaders['icon'],
					'keywords' => str_getcsv($fileHeaders['keywords'], ' ', '"'),
					'align' => $fileHeaders['align'],
					'render_callback' => [__CLASS__, 'timberBlocksCallback'],
				];

				if ($blockVersion < 3) {
					$data['mode'] = $fileHeaders['mode'];
				}

				$data = array_filter($data);
				$data['acf_block_version'] = $blockVersion;

				if ($blockVersion >= 3) {
					if (!empty($fileHeaders['hide_fields_in_sidebar'])) {
						$data['hide_fields_in_sidebar'] = $fileHeaders['hide_fields_in_sidebar'] === 'true';
					}

					if (!empty($fileHeaders['expanded_editor_buttons'])) {
						$data['expanded_editor_buttons'] = $fileHeaders['expanded_editor_buttons'] === 'true';
					}

					if (!empty($fileHeaders['expanded_editor_button_text'])) {
						$data['expanded_editor_button_text'] = $fileHeaders['expanded_editor_button_text'];
					}
				}

				if (!empty($fileHeaders['post_types'])) {
					$data['post_types'] = explode(' ', $fileHeaders['post_types']);
				}

				if (!empty($fileHeaders['supports_align'])) {
					$data['supports']['align'] = in_array($fileHeaders['supports_align'], ['true', 'false'], true)
						? filter_var($fileHeaders['supports_align'], FILTER_VALIDATE_BOOLEAN)
						: explode(' ', $fileHeaders['supports_align']);
				}

				if (!empty($fileHeaders['supports_align_content'])) {
					$data['supports']['alignContent'] = $fileHeaders['supports_align_content'] === 'true'
						? true
						: ($fileHeaders['supports_align_content'] === 'matrix' ? 'matrix' : false);
				}

				if ($blockVersion < 3 && !empty($fileHeaders['supports_mode'])) {
					$data['supports']['mode'] = $fileHeaders['supports_mode'] === 'true';
				}

				foreach (
					[
						'supports_multiple' => 'multiple',
						'supports_anchor' => 'anchor',
						'supports_custom_class_name' => 'customClassName',
						'supports_reusable' => 'reusable',
						'supports_full_height' => 'full_height',
					] as $header => $support
				) {
					if (!empty($fileHeaders[$header])) {
						$data['supports'][$support] = $fileHeaders[$header] === 'true';
					}
				}

				$blockStyle = $fileHeaders['enqueue_style'] ?: null;
				$blockScript = $fileHeaders['enqueue_script'] ?: null;
				$blockScriptDeps = [];

				if (!empty($fileHeaders['enqueue_script_deps'])) {
					$blockScriptDeps = array_map('trim', explode(',', $fileHeaders['enqueue_script_deps']));
				}

				self::$blockAssets[$slug] = [
					'style' => $blockStyle,
					'script' => $blockScript,
					'deps' => $blockScriptDeps,
				];

				if ($blockStyle || $blockScript) {
					$data['enqueue_assets'] = static function () use ($slug, $blockStyle, $blockScript, $blockScriptDeps): void {
						if ($blockStyle) {
							if (str_starts_with($blockStyle, 'src/')) {
								Vite::enqueueStyle($blockStyle, 'taxeon-block-' . $slug);
							} else {
								wp_enqueue_style('taxeon-block-' . $slug, $blockStyle, [], null);
							}
						}

						if ($blockScript) {
							if (str_starts_with($blockScript, 'src/')) {
								Vite::enqueue($blockScript, 'taxeon-block-js-' . $slug, $blockScriptDeps);
							} else {
								wp_enqueue_script('taxeon-block-js-' . $slug, $blockScript, $blockScriptDeps, null, true);
							}
						}
					};
				}

				$exampleData = !empty($fileHeaders['example'])
					? (json_decode($fileHeaders['example'], true) ?? [])
					: self::exampleFields($slug);

				if ($exampleData) {
					$exampleData['is_example'] = true;
					$data['example'] = [
						'attributes' => [
							'mode' => 'preview',
							'data' => $exampleData,
						],
					];
				}

				if (!empty($fileHeaders['parent'])) {
					$data['parent'] = str_getcsv($fileHeaders['parent'], ' ', '"');
				}

				acf_register_block_type($data);
			}
		}
	}

	public static function timberBlocksCallback($block, $content = '', $isPreview = false, $post_id = 0): void
	{
		$context = Timber::context();

		$slug = str_replace('acf/', '', $block['name']);

		$context['block'] = $block;
		$context['blockPosition'] = ++self::$blockPosition;
		$context['post_id'] = $post_id;
		$context['slug'] = $slug;
		$context['is_preview'] = $isPreview;
		$context['fields'] = self::blockFields($block['name']);

		$classes = array_merge(
			['block-' . $slug],
			isset($block['className']) ? [$block['className']] : [],
			$isPreview ? ['is-preview'] : [],
			!empty($block['align']) ? ['align' . $block['align']] : []
		);

		$context['classes'] = implode(' ', array_filter($classes));

		$isExample = false;

		if (!empty($block['data']['is_example'])) {
			$isExample = true;
			$context['fields'] = $block['data'];
		}

		$context = apply_filters('timber/acf-gutenberg-blocks-data', $context);
		$context = apply_filters('timber/acf-gutenberg-blocks-data/' . $slug, $context);
		$context = apply_filters('timber/acf-gutenberg-blocks-data/' . $block['id'], $context);

		Timber::render(self::timberAcfPathRender($slug, $isPreview, $isExample), $context);
	}

	/** @return array<string, mixed> */
	private static function blockFields(string $blockName): array
	{
		$fields = [];

		foreach (acf_get_field_groups(['block' => $blockName]) as $group) {
			foreach (acf_get_fields($group['key']) ?: [] as $field) {
				if ($field['type'] === 'tab') {
					continue;
				}

				$fields[$field['name']] = get_field($field['name']);
			}
		}

		return $fields;
	}

	public static function timberAcfPathRender($slug, $isPreview, $isExample): array
	{
		$directories = self::timberBlockDirectoryGetter();
		$ret = [];

		$exampleIdentifier = apply_filters('timber/acf-gutenberg-blocks-example-identifier', '-example');
		$previewIdentifier = apply_filters('timber/acf-gutenberg-blocks-preview-identifier', '-preview');

		foreach ($directories as $directory) {
			if ($isExample) {
				$ret[] = $directory . "/{$slug}{$exampleIdentifier}.twig";
			}

			if ($isPreview) {
				$ret[] = $directory . "/{$slug}{$previewIdentifier}.twig";
			}

			$ret[] = $directory . "/{$slug}.twig";
		}

		return $ret;
	}

	public static function timberBlocksSubdirectories($directories): array
	{
		$ret = [];

		foreach ($directories as $baseDirectory) {
			if (!file_exists(locate_template($baseDirectory))) {
				continue;
			}

			$templateDirectory = new RecursiveDirectoryIterator(
				locate_template($baseDirectory),
				FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::CURRENT_AS_SELF
			);

			foreach ($templateDirectory as $directory) {
				if ($directory->isDir() && !$directory->isDot()) {
					$ret[] = $baseDirectory . '/' . $directory->getFilename();
				}
			}
		}

		return $ret;
	}

	public static function timberBlockDirectoryGetter(): mixed
	{
		$directories = apply_filters('timber/acf-gutenberg-blocks-templates', ['views/blocks']);
		$subdirectories = self::timberBlocksSubdirectories($directories);

		if (!empty($subdirectories)) {
			$directories = array_merge($directories, $subdirectories);
		}

		return $directories;
	}
}
