<?php

namespace App\Providers;

use App\Services\BlogArchiveContext;
use App\Services\ThemeCache;

class BlogSupports
{
	public function __construct()
	{
		add_action('pre_get_posts', [BlogArchiveContext::class, 'adjustMainQuery']);

		add_action('edited_category', fn() => ThemeCache::flush('menus'));
		add_action('created_category', fn() => ThemeCache::flush('menus'));
		add_action('delete_category', fn() => ThemeCache::flush('menus'));
	}
}
