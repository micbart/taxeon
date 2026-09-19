export function initFilterPills(root = document) {
	root.querySelectorAll('[data-filter]').forEach(setupFilter);
}

function setupFilter(container) {
	const pills = Array.from(container.querySelectorAll('[data-filter-pill]'));
	const items = Array.from(container.querySelectorAll('[data-filter-item]'));

	if (!pills.length || !items.length) {
		return;
	}

	const empty = container.querySelector('[data-filter-empty]');

	const apply = (category) => {
		let visible = 0;

		items.forEach((item) => {
			const categories = (item.dataset.categories || '').split('|').filter(Boolean);
			const matches = category === '' || categories.includes(category);

			item.hidden = !matches;

			if (matches) {
				visible++;
			}
		});

		pills.forEach((pill) => {
			pill.dataset.active = String(pill.dataset.filterPill === category);
		});

		if (empty) {
			empty.hidden = visible > 0;
		}
	};

	pills.forEach((pill) => {
		pill.addEventListener('click', () => apply(pill.dataset.filterPill || ''));
	});
}
