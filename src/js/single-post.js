document.addEventListener('DOMContentLoaded', () => {
	initCopyLink();
	initReadingProgress();
});

function initCopyLink() {
	const button = document.querySelector('[data-copy-link]');

	if (!button || !navigator.clipboard) {
		return;
	}

	const original = button.getAttribute('aria-label') ?? '';

	button.addEventListener('click', async (event) => {
		event.preventDefault();

		try {
			await navigator.clipboard.writeText(window.location.href);
			button.dataset.copied = '';
			button.setAttribute('aria-label', button.dataset.copiedLabel ?? 'Skopiowano');

			setTimeout(() => {
				delete button.dataset.copied;
				button.setAttribute('aria-label', original);
			}, 2000);
		} catch {
		}
	});
}

function initReadingProgress() {
	const bar = document.querySelector('[data-reading-progress]');
	const article = document.querySelector('[data-article-body]');

	if (!bar || !article) {
		return;
	}

	const update = () => {
		const start = article.offsetTop;
		const total = article.offsetHeight - window.innerHeight;
		const progress = total > 0 ? (window.scrollY - start) / total : 0;

		bar.style.transform = `scaleX(${Math.min(1, Math.max(0, progress))})`;
	};

	update();
	window.addEventListener('scroll', update, { passive: true });
	window.addEventListener('resize', update);
}
