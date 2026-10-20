<?php

namespace App;

use App\Api;
use App\Providers;
use App\Services;
use App\Setup\TimberTheme;

function init(): void
{
	new TimberTheme();

	new Services\ThemeCache();
	new Services\PageCacheFlush();
	new Services\Media();
	new Services\LoginPage();
	new Services\ThemeEditorLock();
	new Services\Hardening();
	new Services\GoogleReviews();
	new Services\LlmsTxt();
	new Services\Comments();
	new Services\Redirects();

	new Providers\AcfSupports();
	new Providers\BlogSupports();
	new Providers\PostTypeSupports();

	$recaptcha = new Services\Recaptcha\RecaptchaService();
	$recaptcha->registerFrontend();

	$leads = new Services\Recaptcha\LeadsRepository();
	new Services\Recaptcha\LeadsAdmin($leads);

	new Api\ApiContact($recaptcha, $leads);
	new Api\ApiPosts();
}

init();
