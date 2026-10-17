<?php

namespace App\Setup;

use App\Services\Breadcrumbs;
use App\Services\ThemeCache;
use Timber\Site as TimberSite;
use Timber\Timber;
use Twig\TwigFilter;
use Twig\TwigFunction;

class TimberTheme extends TimberSite
{
	public function __construct()
	{
		$this->initDisableFeed();
		add_action('after_setup_theme', [$this, 'themeSupports']);
		add_action('after_setup_theme', [$this, 'registerMenu']);
		add_filter('timber/twig', [$this, 'addTimberFilters']);
		add_filter('timber/context', [$this, 'addToContext']);
		add_action('wp_enqueue_scripts', [$this, 'enqueueAssets'], 1);
		add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);

		parent::__construct();
	}

	public function enqueueAssets(): void
	{
		Vite::enqueue('src/js/main.js', 'taxeon-main');

		if (is_home() || is_category() || is_tag() || is_author() || is_date() || is_search()) {
			Vite::enqueue('src/js/blog.js', 'taxeon-blog', ['taxeon-main']);
		}

		if (is_singular('post')) {
			Vite::enqueue('src/js/single-post.js', 'taxeon-single-post', ['taxeon-main']);
		}

		wp_dequeue_style('wp-block-library');
		wp_dequeue_style('wp-block-library-theme');

		wp_localize_script(
			'taxeon-main',
			'dataTheme',
			[
				'ajaxUrl' => admin_url('admin-ajax.php'),
				'homeUrl' => home_url('/'),
				'restUrl' => rest_url('taxeon/v1/'),
				'restNonce' => wp_create_nonce('wp_rest'),
				'recaptcha' => [
					'enabled' => function_exists('get_field')
						&& (bool) get_field('recaptcha_enabled', 'option')
						&& trim((string) get_field('recaptcha_site_key', 'option')) !== '',
					'siteKey' => function_exists('get_field') ? trim((string) get_field('recaptcha_site_key', 'option')) : '',
				],
				'i18n' => [
					'noPosts' => __('Nie znaleziono wpisów.', 'taxeon'),
					'loading' => __('Ładowanie…', 'taxeon'),
					'error' => __('Coś poszło nie tak. Spróbuj ponownie.', 'taxeon'),
					'form' => [
						'required' => __('To pole jest wymagane.', 'taxeon'),
						'invalid' => __('Popraw zaznaczone pola.', 'taxeon'),
						'invalidEmail' => __('Podaj poprawny adres e-mail.', 'taxeon'),
						'consent' => __('Zaznacz wymaganą zgodę.', 'taxeon'),
						'sending' => __('Wysyłanie…', 'taxeon'),
						'sent' => __('Dziękujemy, wiadomość została wysłana.', 'taxeon'),
					],
				],
			]
		);
	}

	public function enqueueAdminAssets(): void
	{
		Vite::enqueue('src/js/admin.js', 'taxeon-admin');
	}

	public function initDisableFeed(): void
	{
		add_action('do_feed', [$this, 'disableFeed'], 1);
		add_action('do_feed_rdf', [$this, 'disableFeed'], 1);
		add_action('do_feed_rss', [$this, 'disableFeed'], 1);
		add_action('do_feed_rss2', [$this, 'disableFeed'], 1);
		add_action('do_feed_atom', [$this, 'disableFeed'], 1);
		add_action('do_feed_rss2_comments', [$this, 'disableFeed'], 1);
		add_action('do_feed_atom_comments', [$this, 'disableFeed'], 1);
		remove_action('wp_head', 'wp_generator');
		add_filter('xmlrpc_enabled', '__return_false');
	}

	public function disableFeed(): void
	{
		wp_die(esc_html__('Kanał RSS jest wyłączony.', 'taxeon'));
	}

	public function addToContext($context): array
	{
		$optionsFields = ThemeCache::options();

		$context['site'] = $this;
		$context['themeOptions'] = $optionsFields;
		$context['breadcrumbs'] = Breadcrumbs::build();

		$context['viteIsDev'] = Vite::isDev();

		$menus = (array) ThemeCache::remember('menus', 'html', function () use ($optionsFields): array {
			return $this->buildMenus($optionsFields);
		});

		$currentUrl = self::currentUrl();

		$menus['headerMenuHtml'] = $menus['headerMenuItems']
			? Timber::compile('components/header/menu-header.twig', [
				'items' => $menus['headerMenuItems'],
				'currentUrl' => $currentUrl,
			])
			: '';

		$menus['mobileMenuHtml'] = $menus['headerMenuItems']
			? Timber::compile('components/header/menu-mobile.twig', [
				'items' => $menus['headerMenuItems'],
				'currentUrl' => $currentUrl,
				'contactLink' => $optionsFields['header_contact_link'] ?? [],
			])
			: '';

		unset($menus['headerMenuItems']);

		return $context + $menus;
	}

	private static function currentUrl(): string
	{
		if (is_front_page()) {
			return home_url('/');
		}

		if (is_home() || is_singular('post')) {
			$blogPageId = (int) get_option('page_for_posts');

			return $blogPageId ? (string) get_permalink($blogPageId) : '';
		}

		if (is_singular()) {
			return (string) get_permalink(get_queried_object_id());
		}

		if (is_category() || is_tag() || is_tax()) {
			$link = get_term_link(get_queried_object());

			return is_wp_error($link) ? '' : (string) $link;
		}

		if (is_post_type_archive()) {
			$link = get_post_type_archive_link((string) get_query_var('post_type'));

			return $link ?: '';
		}

		return '';
	}

	private function buildMenus(array $optionsFields): array
	{
		$menus = [
			'headerMenuItems' => [],
			'legalMenuHtml' => '',
			'footerMenuColumns' => [],
		];

		$headerItems = [];

		foreach ((array) ($optionsFields['header_menu'] ?? []) as $item) {
			$link = $item['link'] ?? [];

			if (empty($link['url'])) {
				continue;
			}

			$children = [];

			foreach ((array) ($item['submenu'] ?? []) as $child) {
				$childLink = $child['link'] ?? [];

				if (empty($childLink['url'])) {
					continue;
				}

				$children[] = [
					'url' => $childLink['url'],
					'title' => $childLink['title'] ?? '',
					'target' => $childLink['target'] ?? '',
					'icon' => $child['icon'] ?? '',
					'description' => $child['description'] ?? '',
				];
			}

			$headerItems[] = [
				'url' => $link['url'],
				'title' => $link['title'] ?? '',
				'target' => $link['target'] ?? '',
				'width' => $item['submenu_width'] ?? 'wide',
				'children' => $children,
			];
		}

		$menus['headerMenuItems'] = $headerItems;

		if (has_nav_menu('legalMenu')) {
			$menu = Timber::get_menu('legalMenu', ['depth' => 1]);

			if ($menu) {
				$menus['legalMenuHtml'] = Timber::compile('components/footer/menu-legal.twig', [
					'menu' => $menu,
				]);
			}
		}

		foreach ((array) ($optionsFields['footer_menus'] ?? []) as $column) {
			if (empty($column['select_menu'])) {
				continue;
			}

			$menu = Timber::get_menu((int) $column['select_menu'], ['depth' => 1]);

			if (!$menu) {
				continue;
			}

			$menus['footerMenuColumns'][] = [
				'title' => $column['title_menu'] ?? $menu->title,
				'html' => Timber::compile('components/footer/menu-footer.twig', [
					'menu' => $menu,
				]),
			];
		}

		return $menus;
	}

	public function themeSupports(): void
	{
		add_theme_support('title-tag');
		add_theme_support('post-thumbnails');
		add_theme_support('automatic-feed-links');
		add_theme_support(
			'html5',
			[
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'search-form',
				'style',
				'script',
			]
		);

		add_theme_support('menus');
		add_theme_support('responsive-embeds');
		add_theme_support('editor-styles');

		load_theme_textdomain('taxeon', get_template_directory() . '/languages');
	}

	public function registerMenu(): void
	{
		register_nav_menus([
			'legalMenu' => __('Stopka — dół (regulaminy)', 'taxeon'),
		]);
	}

	private function svgRelativePath(string $icon): string
	{
		if ($icon === '' || !preg_match('/^[a-z0-9-]+$/', $icon)) {
			return '';
		}

		$relative = '/dist/svg/' . $icon . '.svg';

		if (!file_exists(get_template_directory() . $relative)) {
			$relative = '/src/svg/' . $icon . '.svg';
		}

		return $relative;
	}

	public function addTimberFilters($twig): mixed
	{
		$twig->addFilter(
			new TwigFilter(
				'svg',
				function (string $iconName, $classNames = false): string {
					static $cache = [];

					$relative = $this->svgRelativePath($iconName);

					if ($relative === '') {
						return current_user_can('manage_options')
							? "<span style='color:#f00;font-size:14px;'>Niedozwolona nazwa ikony: " . esc_html($iconName) . '</span>'
							: '';
					}

					$filename = get_template_directory() . $relative;

					if (!isset($cache[$filename])) {
						$cache[$filename] = file_exists($filename) ? (string) file_get_contents($filename) : '';
					}

					$icon = $cache[$filename];

					if ($icon === '') {
						return current_user_can('manage_options')
							? "<span style='color:#f00;font-size:14px;'>Brak ikony: " . esc_html($iconName) . '</span>'
							: '';
					}

					$icon = str_replace('<svg', "<svg aria-hidden='true' focusable='false'", $icon);

					if ($classNames) {
						$icon = str_replace('<svg', "<svg class='icon " . esc_attr($classNames) . "'", $icon);
					}

					return $icon;
				}
			)
		);

		$twig->addFilter(
			new TwigFilter(
				'youtubeId',
				static function (string $url): string {
					if (preg_match('~(?:youtu\\.be/|youtube(?:-nocookie)?\\.com/(?:embed/|shorts/|live/|watch\\?v=|.*[?&]v=))([A-Za-z0-9_-]{11})~', $url, $trafienie)) {
						return $trafienie[1];
					}

					return '';
				}
			)
		);

		$twig->addFilter(
			new TwigFilter(
				'svgUrl',
				function (string $icon): string {
					return get_template_directory_uri() . $this->svgRelativePath($icon);
				}
			)
		);

		$twig->addFilter(
			new TwigFilter(
				'termLink',
				function ($id) {
					return get_term_link($id);
				}
			)
		);

		$twig->addFilter(
			new TwigFilter(
				'termColor',
				function ($term): string {
					$id = is_object($term) ? ($term->id ?? $term->term_id ?? 0) : (int) $term;

					if (!$id || !function_exists('get_field')) {
						return '';
					}

					$taxonomy = is_object($term) && !empty($term->taxonomy)
						? $term->taxonomy
						: (get_term($id)->taxonomy ?? 'category');

					return (string) get_field('color', $taxonomy . '_' . $id);
				}
			)
		);

		$twig->addFilter(
			new TwigFilter(
				'shopPrice',
				function ($value): string {
					$amount = (float) str_replace(',', '.', (string) $value);
					$decimals = fmod($amount, 1.0) === 0.0 ? 0 : 2;

					return number_format($amount, $decimals, ',', ' ') . ' zł';
				}
			)
		);

		$twig->addFilter(
			new TwigFilter(
				'wpDate',
				function ($value, string $format = 'j F Y'): string {
					if (!$value) {
						return '';
					}

					$timestamp = strtotime((string) $value);

					return $timestamp ? wp_date($format, $timestamp) : '';
				}
			)
		);

		$twig->addFilter(
			new TwigFilter(
				'inlineHtml',
				function ($text): string {
					return wp_kses((string) $text, [
						'a' => ['href' => [], 'target' => [], 'rel' => [], 'title' => []],
						'strong' => [],
						'b' => [],
						'em' => [],
						'br' => [],
					]);
				},
				['is_safe' => ['html']]
			)
		);

		$twig->addFilter(
			new TwigFilter(
				'readTime',
				function ($content): int {
					$words = str_word_count(wp_strip_all_tags((string) $content));

					return max(1, (int) ceil($words / 200));
				}
			)
		);

		$twig->addFilter(
			new TwigFilter(
				'arrayToJson',
				function ($data): string {
					return (string) json_encode($data, JSON_UNESCAPED_UNICODE);
				}
			)
		);

		$twig->addFilter(
			new TwigFilter(
				'jsonToArray',
				function ($data): array {
					return (array) json_decode((string) $data, true);
				}
			)
		);

		$twig->addFilter(
			new TwigFilter(
				'getPermalink',
				function (?int $id) {
					return $id ? get_permalink($id) : null;
				}
			)
		);

		$twig->addFunction(
			new TwigFunction(
				'getOption',
				function (string $option) {
					return get_option($option);
				}
			)
		);

		$twig->addFunction(
			new TwigFunction(
				'linkAttrs',
				function (?string $url, ?string $target = ''): string {
					$url = trim((string) $url);

					if ($url === '' || str_starts_with($url, 'mailto:') || str_starts_with($url, 'tel:')) {
						return '';
					}

					$host = parse_url($url, PHP_URL_HOST);
					$wlasny = parse_url(home_url(), PHP_URL_HOST);
					$nowaKarta = $target === '_blank'
						|| ($host && strcasecmp($host, (string) $wlasny) !== 0);

					return $nowaKarta ? ' target="_blank" rel="noopener"' : '';
				},
				['is_safe' => ['html']]
			)
		);

		$twig->addFunction(
			new TwigFunction(
				'googleReviews',
				function (array $selected = [], int $limit = 8): array {
					$store = \App\Services\GoogleReviews::stored();

					return [
						'rating' => $store['rating'] ?? null,
						'total' => $store['total'] ?? null,
						'mapsUrl' => $store['mapsUrl'] ?? '',
						'items' => \App\Services\GoogleReviews::selected($selected, $limit),
					];
				}
			)
		);

		$twig->addFunction(
			new TwigFunction(
				'block_index',
				function (string $blockName = 'default'): int {
					static $counters = [];
					$counters[$blockName] = ($counters[$blockName] ?? 0) + 1;

					return $counters[$blockName];
				}
			)
		);

		return $twig;
	}
}
