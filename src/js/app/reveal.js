export function initReveal(root = document) {
	const targets = root.querySelectorAll('.reveal:not(.in)');

	if (!targets.length) {
		return;
	}

	const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	if (prefersReduced || !('IntersectionObserver' in window)) {
		targets.forEach((el) => el.classList.add('in'));
		return;
	}

	const observer = new IntersectionObserver(
		(entries) => {
			entries.forEach((entry) => {
				if (!entry.isIntersecting) {
					return;
				}

				entry.target.classList.add('in');
				observer.unobserve(entry.target);
			});
		},
		{ threshold: 0.12, rootMargin: '0px 0px -40px 0px' }
	);

	targets.forEach((el, index) => {
		el.style.transitionDelay = `${(index % 4) * 60}ms`;
		observer.observe(el);
	});
}
