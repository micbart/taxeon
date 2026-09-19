const CHECK_DELAY = 650;

export function initVerifyBox() {
	document.querySelectorAll('[data-verify]').forEach(setupBox);
}

function setupBox(box) {
	const label = box.querySelector('[data-verify-label]');
	const input = box.querySelector('input[name="verified"]');

	const verify = () => {
		if (box.dataset.verified !== undefined || box.dataset.checking !== undefined) {
			return;
		}

		box.dataset.checking = '';
		box.setAttribute('aria-busy', 'true');

		if (label) {
			label.textContent = label.dataset.checkingLabel;
		}

		window.setTimeout(() => {
			delete box.dataset.checking;
			box.dataset.verified = '';
			box.setAttribute('aria-checked', 'true');
			box.removeAttribute('aria-busy');
			box.tabIndex = -1;

			if (label) {
				label.textContent = label.dataset.doneLabel;
			}

			if (input) {
				input.value = '1';
			}

			box.parentElement?.querySelector('[data-error-for="verified"]')?.replaceChildren();
		}, CHECK_DELAY);
	};

	box.addEventListener('click', verify);
	box.addEventListener('keydown', (event) => {
		if (event.key === ' ' || event.key === 'Enter') {
			event.preventDefault();
			verify();
		}
	});
}

export function isVerified(scope) {
	const box = scope?.querySelector('[data-verify]');

	if (!box) {
		return true;
	}

	if (box.dataset.verified !== undefined) {
		return true;
	}

	box.click();

	return false;
}
