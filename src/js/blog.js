import { apiGet } from './app/api.js';
import { initReveal } from './app/reveal.js';

document.addEventListener('DOMContentLoaded', () => {
	const filter = document.querySelector('[data-blog-filter]');
	const results = document.querySelector('[data-blog-results]');

	if (!filter || !results) {
		return;
	}

	const grid = results.querySelector('[data-blog-grid]');
	const empty = results.querySelector('[data-blog-empty]');
	const featured = document.querySelector('[data-blog-featured]');
	const pagination = document.querySelector('[data-blog-pagination]');
	const loadMore = document.querySelector('[data-blog-load-more]');
	const loadMoreButton = loadMore?.querySelector('button');
	const search = new URLSearchParams(window.location.search).get('s') ?? '';

	let currentCategory = filter.querySelector('[data-active="true"]')?.dataset.category ?? 'all';
	let currentPage = 1;
	let maxPages = Number(results.dataset.maxPages ?? 1);
	let request = 0;

	const render = async (category, page, { append = false, pushState = true } = {}) => {
		const ticket = ++request;

		results.dataset.loading = '';

		try {
			const data = await apiGet('posts', { category, page, s: search });

			if (ticket !== request || !data.success) {
				return;
			}

			if (append) {
				grid.insertAdjacentHTML('beforeend', data.html);
			} else {
				grid.innerHTML = data.html;
			}

			currentCategory = category;
			currentPage = data.page;
			maxPages = data.maxPages;

			if (empty) {
				empty.hidden = data.found > 0;
				empty.textContent = data.emptyMessage;
			}

			const isDefaultView = category === 'all' && data.page === 1;

			if (featured) {
				featured.hidden = !isDefaultView;
			}

			if (pagination) {
				pagination.hidden = true;
			}

			if (loadMore) {
				loadMore.hidden = data.page >= data.maxPages;
			}

			if (pushState) {
				updateUrl(category);
			}

			initReveal(grid);
		} finally {
			if (ticket === request) {
				delete results.dataset.loading;
			}
		}
	};

	filter.addEventListener('click', (event) => {
		const pill = event.target.closest('[data-category]');

		if (!pill) {
			return;
		}

		event.preventDefault();

		filter.querySelectorAll('[data-category]').forEach((node) => {
			node.dataset.active = String(node === pill);
		});

		render(pill.dataset.category, 1);
	});

	loadMoreButton?.addEventListener('click', () => {
		if (currentPage < maxPages) {
			render(currentCategory, currentPage + 1, { append: true, pushState: false });
		}
	});

	window.addEventListener('popstate', () => {
		const category = new URLSearchParams(window.location.search).get('kategoria') ?? 'all';

		filter.querySelectorAll('[data-category]').forEach((node) => {
			node.dataset.active = String(node.dataset.category === category);
		});

		render(category, 1, { pushState: false });
	});
});

function updateUrl(category) {
	const url = new URL(window.location.href);

	if (category === 'all') {
		url.searchParams.delete('kategoria');
	} else {
		url.searchParams.set('kategoria', category);
	}

	window.history.pushState({ category }, '', url);
}
