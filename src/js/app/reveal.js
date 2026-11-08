export function initReveal(root = document) {
	const targets = root.querySelectorAll('.reveal:not(.in)');

	if (!targets.length) {
		return;
	}

	const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	if (reduced || !('IntersectionObserver' in window)) {
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

	const siblings = new Map();

	targets.forEach((el) => {
		const parent = el.parentElement;
		const index = siblings.get(parent) ?? 0;
		siblings.set(parent, index + 1);
		el.style.animationDelay = `${(index % 4) * 70}ms`;
		observer.observe(el);
	});
}
