<?php

namespace App\Services;

class PageCacheFlush
{
	public const HOOK = 'raks_flush_page_cache';

	public function __construct()
	{
		add_action('init', [$this, 'scheduleFlush']);
		add_action(self::HOOK, [$this, 'flush']);
		add_action('switch_theme', [$this, 'unscheduleFlush']);
	}

	public function scheduleFlush(): void
	{
		if (!wp_next_scheduled(self::HOOK)) {
			wp_schedule_event(time() + HOUR_IN_SECONDS, 'daily', self::HOOK);
		}
	}

	public function unscheduleFlush(): void
	{
		$timestamp = wp_next_scheduled(self::HOOK);

		if ($timestamp) {
			wp_unschedule_event($timestamp, self::HOOK);
		}
	}

	public function flush(): void
	{
		$this->purgeW3TotalCache();
		$this->purgeLiteSpeed();

		do_action('raks_page_cache_flushed');
	}

	private function purgeW3TotalCache(): void
	{
		if (function_exists('w3tc_flush_posts')) {
			w3tc_flush_posts();
		}
	}

	private function purgeLiteSpeed(): void
	{
		if (has_action('litespeed_purge_all')) {
			do_action('litespeed_purge_all');
		}
	}
}
