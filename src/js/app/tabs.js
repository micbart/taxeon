export function initTabs(root = document) {
	root.querySelectorAll('[data-tabs]').forEach((wrapper) => {
		const buttons = Array.from(wrapper.querySelectorAll('[data-tab]'));
		const panels = Array.from(wrapper.querySelectorAll('[data-panel]'));

		if (!buttons.length) {
			return;
		}

		const activate = (id) => {
			buttons.forEach((button) => {
				const isActive = button.dataset.tab === id;
				button.dataset.active = String(isActive);
				button.setAttribute('aria-selected', String(isActive));
				button.tabIndex = isActive ? 0 : -1;
			});

			panels.forEach((panel) => {
				panel.hidden = panel.dataset.panel !== id;
			});
		};

		buttons.forEach((button, index) => {
			button.addEventListener('click', () => activate(button.dataset.tab));

			button.addEventListener('keydown', (event) => {
				const next = ['ArrowRight', 'ArrowDown'].includes(event.key);
				const prev = ['ArrowLeft', 'ArrowUp'].includes(event.key);
				const step = next ? 1 : prev ? -1 : 0;

				if (step === 0) {
					return;
				}

				event.preventDefault();
				const target = buttons[(index + step + buttons.length) % buttons.length];
				target.focus();
				activate(target.dataset.tab);
			});
		});

		const initial = buttons.find((button) => button.dataset.active === 'true') ?? buttons[0];
		activate(initial.dataset.tab);
	});
}
