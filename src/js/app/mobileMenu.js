export function initMobileMenu() {
	const burger = document.getElementById('menu-toggle');
	const menu = document.getElementById('mobile-menu');
	const header = document.getElementById('site-header');

	if (!burger || !menu) {
		return;
	}

	let headerWasScrolled = false;

	const isOpen = () => 'open' in menu.dataset;

	const alignToHeader = () => {
		if (header) {
			menu.style.top = `${Math.max(0, Math.round(header.getBoundingClientRect().bottom))}px`;
		}
	};

	const setState = (open) => {
		if (open) {
			if (header) {
				headerWasScrolled = 'scrolled' in header.dataset;
				header.dataset.scrolled = '';
			}

			alignToHeader();
			menu.dataset.open = '';
			burger.dataset.open = '';
		} else {
			delete menu.dataset.open;
			delete burger.dataset.open;

			if (header && !headerWasScrolled && window.scrollY <= 8) {
				delete header.dataset.scrolled;
			}
		}

		document.body.classList.toggle('overflow-hidden', open);
		burger.setAttribute('aria-expanded', String(open));
	};

	burger.addEventListener('click', () => {
		setState(!isOpen());
	});

	menu.querySelectorAll('[data-submenu-toggle]').forEach((toggle) => {
		const panel = toggle.nextElementSibling;

		toggle.addEventListener('click', () => {
			const expanded = toggle.getAttribute('aria-expanded') === 'true';

			toggle.setAttribute('aria-expanded', String(!expanded));

			if (panel) {
				panel.hidden = expanded;
			}
		});
	});

	menu.addEventListener('click', (event) => {
		if (event.target.closest('a')) {
			setState(false);
		}
	});

	window.addEventListener('resize', () => {
		if (isOpen()) {
			alignToHeader();
		}
	});

	document.addEventListener('keydown', (event) => {
		if (event.key === 'Escape' && isOpen()) {
			setState(false);
			burger.focus();
		}
	});
}
