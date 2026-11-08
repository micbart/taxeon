const DURATION = 300;
const EASING = 'cubic-bezier(0.2, 0.7, 0.2, 1)';

function animateHeight(element, from, to, done) {
	const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	element.getAnimations().forEach((animation) => animation.cancel());

	if (reduced || !element.animate) {
		done?.();
		return;
	}

	const animation = element.animate(
		[
			{ height: `${from}px`, opacity: from === 0 ? 0 : 1 },
			{ height: `${to}px`, opacity: to === 0 ? 0 : 1 },
		],
		{ duration: DURATION, easing: EASING }
	);

	animation.onfinish = () => done?.();
}

function expand(item) {
	const body = item.querySelector('[data-faq-body]');

	item.open = true;
	item.dataset.expanded = 'true';
	animateHeight(body, 0, body.scrollHeight);
}

function collapse(item) {
	const body = item.querySelector('[data-faq-body]');

	if (!item.open) {
		return;
	}

	item.dataset.expanded = 'false';
	animateHeight(body, body.offsetHeight, 0, () => {
		if (item.dataset.expanded === 'false') {
			item.open = false;
		}
	});
}

export function initFaq(root = document) {
	root.querySelectorAll('[data-faq]').forEach((item) => {
		const summary = item.querySelector('summary');

		summary.addEventListener('click', (event) => {
			event.preventDefault();

			if (item.dataset.expanded === 'true') {
				collapse(item);
				return;
			}

			const group = item.closest('section') || document;

			group.querySelectorAll('[data-faq][data-expanded="true"]').forEach((other) => {
				if (other !== item) {
					collapse(other);
				}
			});

			expand(item);
		});
	});
}
