export function initScrollToTop() {
	const button = document.querySelector('[data-scroll-top]');

	if (!button) {
		return;
	}

	const update = () => {
		if (window.scrollY > window.innerHeight) {
			button.dataset.visible = '';
		} else {
			delete button.dataset.visible;
		}
	};

	const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	button.addEventListener('click', () => {
		window.scrollTo({ top: 0, behavior: prefersReduced ? 'auto' : 'smooth' });
	});

	update();
	window.addEventListener('scroll', update, { passive: true });
}
