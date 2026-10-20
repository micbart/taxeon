export function initReviews(root = document) {
	const bezAnimacji = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	root.querySelectorAll('[data-reviews]').forEach((karuzela) => {
		const tor = karuzela.querySelector('[data-reviews-track]');
		const nawigacja = karuzela.querySelector('[data-reviews-nav]');
		const wstecz = karuzela.querySelector('[data-reviews-prev]');
		const dalej = karuzela.querySelector('[data-reviews-next]');

		if (!tor || !wstecz || !dalej) {
			return;
		}

		const krok = () => {
			const karta = tor.firstElementChild;

			if (!karta) {
				return tor.clientWidth;
			}

			return karta.offsetWidth + parseFloat(getComputedStyle(tor).columnGap) || karta.offsetWidth;
		};

		const przewin = (kierunek) => tor.scrollBy({
			left: kierunek * krok(),
			behavior: bezAnimacji ? 'auto' : 'smooth',
		});

		const odswiez = () => {
			const zapas = tor.scrollWidth - tor.clientWidth;

			nawigacja.hidden = zapas < 2;
			wstecz.disabled = tor.scrollLeft < 2;
			dalej.disabled = tor.scrollLeft > zapas - 2;
		};

		wstecz.addEventListener('click', () => przewin(-1));
		dalej.addEventListener('click', () => przewin(1));
		tor.addEventListener('scroll', odswiez, { passive: true });
		window.addEventListener('resize', odswiez);

		odswiez();
	});
}
