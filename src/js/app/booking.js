export function initBooking(root = document) {
	const modal = root.querySelector('[data-booking-modal]');

	if (!modal) {
		return;
	}

	const ramka = modal.querySelector('[data-booking-frame]');
	const wzorzec = new URL(modal.dataset.src, window.location.origin).hostname;

	const otworz = (event) => {
		event.preventDefault();

		if (!ramka.src) {
			ramka.src = modal.dataset.src;
		}

		modal.showModal();
		document.body.style.overflow = 'hidden';
	};

	root.querySelectorAll('a[href]').forEach((link) => {
		if (link.hostname === wzorzec || link.hasAttribute('data-booking')) {
			link.addEventListener('click', otworz);
		}
	});

	modal.querySelector('[data-booking-close]')?.addEventListener('click', () => modal.close());

	modal.addEventListener('click', (event) => {
		if (event.target === modal) {
			modal.close();
		}
	});

	modal.addEventListener('close', () => {
		document.body.style.overflow = '';
	});
}
