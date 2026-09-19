<?php

namespace App\Services;

class ThemeEditorLock
{
	public function __construct()
	{
		add_filter('map_meta_cap', [$this, 'denyThemeEditing'], 10, 2);
	}

	public function denyThemeEditing(array $caps, string $cap): array
	{
		return in_array($cap, ['edit_themes', 'edit_plugins'], true) ? ['do_not_allow'] : $caps;
	}
}
