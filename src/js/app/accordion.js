export function initAccordion(root = document) {
	root.querySelectorAll('[data-accordion]').forEach((accordion) => {
		const items = Array.from(accordion.querySelectorAll('[data-accordion-item]'));

		if (!items.length) {
			return;
		}

		const activate = (target) => {
			items.forEach((item) => {
				const isActive = item === target;
				item.classList.toggle('is-active', isActive);
				item.setAttribute('aria-expanded', String(isActive));
			});
		};

		items.forEach((item, index) => {
			item.setAttribute('role', 'button');
			item.setAttribute('tabindex', '0');
			item.setAttribute('aria-expanded', String(item.classList.contains('is-active')));

			item.addEventListener('click', () => activate(item));
			item.addEventListener('focus', () => activate(item));

			item.addEventListener('keydown', (event) => {
				if (event.key === 'Enter' || event.key === ' ') {
					event.preventDefault();
					activate(item);
					return;
				}

				const step = event.key === 'ArrowRight' || event.key === 'ArrowDown' ? 1 : event.key === 'ArrowLeft' || event.key === 'ArrowUp' ? -1 : 0;

				if (step === 0) {
					return;
				}

				event.preventDefault();
				const next = items[(index + step + items.length) % items.length];
				next.focus();
			});
		});
	});
}
