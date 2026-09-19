<?php

use Timber\Timber;

$composerAutoload = __DIR__ . '/vendor/autoload.php';

if (!file_exists($composerAutoload)) {
	add_action('admin_notices', function (): void {
		echo '<div class="error"><p>Uruchom <code>composer install</code> w katalogu motywu Raks 2.0.</p></div>';
	});

	return;
}

require_once $composerAutoload;

Timber::init();

require_once __DIR__ . '/app/bootstrap.php';
