const ADRES_REJESTRACJI = 'https://app.taxeon.pl/#/rejestracja';

export function initTrial(root = document) {
	root.querySelectorAll(`a[href^="${ADRES_REJESTRACJI}"]`).forEach((link) => {
		link.addEventListener('click', () => {
			const parametry = new URLSearchParams({
				id: Date.now(),
				referrer: document.referrer,
			});

			link.href = `${ADRES_REJESTRACJI}?${parametry}`;
		});
	});
}
