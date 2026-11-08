<?php

namespace App\Services;

class LeadEvent
{
	public const META = '_taxeon_lead_page';

	public function __construct()
	{
		add_action('wp_head', [$this, 'print'], 1);
	}

	public function print(): void
	{
		if (!is_page() || !get_post_meta((int) get_queried_object_id(), self::META, true)) {
			return;
		}

		$script = <<<'JS'
(function () {
	if (window.top !== window.self) {
		try {
			window.top.location.replace(window.location.href);
			return;
		} catch (e) {}
	}
	try {
		if (sessionStorage.getItem('taxeon_lead_sent') === '1') {
			return;
		}
		sessionStorage.setItem('taxeon_lead_sent', '1');
	} catch (e) {}
	window.dataLayer = window.dataLayer || [];
	window.dataLayer.push({
		event: 'generate_lead',
		event_category: 'spotkanie',
		event_label: 'umow_spotkanie',
		value: 1
	});
})();
JS;

		wp_print_inline_script_tag($script);
	}
}
