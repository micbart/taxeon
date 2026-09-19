export function initStickyHeader() {
	const header = document.getElementById('site-header');

	if (!header) {
		return;
	}

	const update = () => {
		if (window.scrollY > 8) {
			header.dataset.scrolled = '';
		} else {
			delete header.dataset.scrolled;
		}
	};

	update();
	window.addEventListener('scroll', update, { passive: true });
}
