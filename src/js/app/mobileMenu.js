export function initMobileMenu() {
	const burger = document.getElementById('menu-toggle');
	const menu = document.getElementById('mobile-menu');

	if (!burger || !menu) {
		return;
	}

	const isOpen = () => 'open' in menu.dataset;

	const setState = (open) => {
		if (open) {
			menu.dataset.open = '';
			burger.dataset.open = '';
		} else {
			delete menu.dataset.open;
			delete burger.dataset.open;
		}

		document.body.classList.toggle('overflow-hidden', open);
		burger.setAttribute('aria-expanded', String(open));
	};

	burger.addEventListener('click', () => {
		setState(!isOpen());
	});

	menu.addEventListener('click', (event) => {
		if (event.target.closest('a')) {
			setState(false);
		}
	});

	document.addEventListener('keydown', (event) => {
		if (event.key === 'Escape' && isOpen()) {
			setState(false);
			burger.focus();
		}
	});
}
