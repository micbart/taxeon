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
	new Services\Images\ImageRenderer();
	new Services\Images\ImageWarm();
	new Services\Images\ImageContent();
	new Services\LoginPage();
	new Services\ThemeEditorLock();
	Services\Security\LoginUrl::boot();
	Services\Security\Hardening::boot();
	Services\Security\Turnstile::boot();
	Services\Security\LoginLimiter::boot();
	Services\Security\TwoFactor::boot();
	Services\Security\PasswordGuard::boot();
	new Services\GoogleReviews();
	new Services\LlmsTxt();
	new Services\Comments();
	new Services\Redirects();
	new Services\LeadEvent();
	new Services\Prices();
	new Services\DisableEmoji();

	new Providers\AcfSupports();
	new Providers\BlogSupports();
	new Providers\PostTypeSupports();

	$recaptcha = new Services\Recaptcha\RecaptchaService();
	$recaptcha->registerFrontend();
	new Services\ContactFormAssets($recaptcha);

	$leads = new Services\Recaptcha\LeadsRepository();
	new Services\Recaptcha\LeadsAdmin($leads);

	new Api\ApiContact($recaptcha, $leads);
	new Api\ApiPosts();
}

init();
