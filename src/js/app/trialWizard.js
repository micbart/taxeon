import { apiGet, apiPost, recaptchaToken, i18n } from './api.js';
import { isVerified } from './verifyBox.js';

export function initTrialWizard(root = document) {
	root.querySelectorAll('[data-wizard]').forEach(setupWizard);
}

function setupWizard(wizard) {
	const form = wizard.querySelector('[data-wizard-form]');

	if (!form) {
		return;
	}

	const panels = Array.from(wizard.querySelectorAll('[data-wizard-panel]'));
	const status = wizard.querySelector('[data-wizard-status]');
	const submit = form.querySelector('[type="submit"]');
	const submitLabel = wizard.querySelector('[data-wizard-submit-label]');
	const defaultLabel = submitLabel?.textContent.trim() ?? '';

	const show = (step) => {
		panels.forEach((panel) => {
			panel.hidden = panel.dataset.wizardPanel !== String(step);
		});

		wizard.querySelectorAll('[data-wizard-step]').forEach((dot) => {
			const index = Number(dot.dataset.wizardStep);
			dot.dataset.state = index === step ? 'active' : index < step ? 'done' : 'todo';
		});

		wizard.querySelectorAll('[data-wizard-line]').forEach((line) => {
			line.dataset.done = String(Number(line.dataset.wizardLine) < step);
		});

		const foot = wizard.querySelector('[data-wizard-foot]');

		if (foot) {
			foot.hidden = step === panels.length;
		}

		wizard.scrollIntoView({ behavior: 'smooth', block: 'start' });
	};

	wizard.querySelectorAll('[data-wizard-next]').forEach((button) => {
		button.addEventListener('click', () => {
			const company = form.querySelector('[name="company"]');

			if (company?.offsetParent && !company.value.trim()) {
				setError(form, 'company', i18n('form.required', 'To pole jest wymagane.'));
				company.focus();
				return;
			}

			show(Number(button.dataset.wizardNext));
		});
	});

	wizard.querySelectorAll('[data-wizard-back]').forEach((button) => {
		button.addEventListener('click', () => show(Number(button.dataset.wizardBack)));
	});

	setupNipLookup(wizard, form);

	form.addEventListener('submit', async (event) => {
		event.preventDefault();

		clearErrors(form);
		setStatus(status, '', '');

		const payload = Object.fromEntries(new FormData(form).entries());
		const consent = form.querySelector('[name="consent"]');

		if (consent) {
			payload.consent = consent.checked;
		}
		payload.form = wizard.dataset.wizard || 'demo';
		payload.sourceUrl = window.location.href;

		const errors = validate(form, payload);

		if (!isVerified(form)) {
			errors.verified = i18n('form.verify', 'Potwierdź, że nie jesteś robotem.');
		}

		if (Object.keys(errors).length) {
			applyErrors(form, errors);
			setStatus(status, i18n('form.invalid', 'Popraw zaznaczone pola.'), 'error');
			return;
		}

		setLoading(submit, submitLabel, true, i18n('form.sending', 'Wysyłanie…'));

		try {
			payload.token = await recaptchaToken('contact');

			const { ok, data } = await apiPost('contact', payload);

			if (!ok || !data.success) {
				if (data.errors) {
					applyErrors(form, data.errors);
				}

				setStatus(status, data.message || i18n('error', 'Coś poszło nie tak.'), 'error');
				return;
			}

			fillDoneText(wizard, payload);
			show(panels.length);
		} catch {
			setStatus(status, i18n('error', 'Coś poszło nie tak.'), 'error');
		} finally {
			setLoading(submit, submitLabel, false, defaultLabel);
		}
	});
}

function setupNipLookup(wizard, form) {
	const button = wizard.querySelector('[data-wizard-check]');
	const nip = form.querySelector('[name="nip"]');
	const result = wizard.querySelector('[data-wizard-result]');

	if (!button || !nip || !result) {
		return;
	}

	const name = result.querySelector('[data-wizard-result-name]');
	const address = result.querySelector('[data-wizard-result-address]');
	const status = result.querySelector('[data-wizard-result-status]');
	const label = result.querySelector('[data-wizard-result-label]');
	const company = form.querySelector('[name="company"]');
	const defaultLabel = label?.textContent.trim() ?? '';

	const lookup = async () => {
		clearErrors(form);
		button.disabled = true;

		try {
			const data = await apiGet('nip', { nip: nip.value });

			if (!data.success) {
				setError(form, 'nip', data.message || i18n('error', 'Coś poszło nie tak.'));
				return;
			}

			if (label) {
				label.textContent = data.manual ? data.message : defaultLabel;
			}

			if (name) {
				name.textContent = data.name ?? '';
			}

			if (address) {
				address.textContent = data.address ?? '';
			}

			if (status) {
				status.textContent = data.status ?? '';
			}

			if (company && data.name) {
				company.value = data.name;
			}

			result.hidden = false;
			(data.name ? button : company)?.focus();
		} catch {
			result.hidden = false;
		} finally {
			button.disabled = false;
		}
	};

	button.addEventListener('click', lookup);

	nip.addEventListener('keydown', (event) => {
		if (event.key === 'Enter') {
			event.preventDefault();
			lookup();
		}
	});
}

function fillDoneText(wizard, payload) {
	const node = wizard.querySelector('[data-wizard-done-text]');

	if (!node) {
		return;
	}

	const firstName = String(payload.name ?? '').trim().split(' ')[0] ?? '';

	node.textContent = (node.dataset.template || '')
		.replace('{name}', firstName)
		.replace('{email}', String(payload.email ?? ''));
}

function validate(form, payload) {
	const errors = {};

	form.querySelectorAll('[required]').forEach((field) => {
		if (!field.offsetParent && field.type !== 'checkbox') {
			return;
		}

		if (field.type === 'checkbox') {
			if (!field.checked) {
				errors[field.name] = i18n('form.consent', 'Zaznacz wymaganą zgodę.');
			}

			return;
		}

		if (!String(payload[field.name] ?? '').trim()) {
			errors[field.name] = i18n('form.required', 'To pole jest wymagane.');
		}
	});

	const email = String(payload.email ?? '').trim();

	if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email)) {
		errors.email = i18n('form.invalidEmail', 'Podaj poprawny adres e-mail.');
	}

	return errors;
}

function setError(form, name, message) {
	const field = form.querySelector(`[name="${name}"]`);

	field?.setAttribute('aria-invalid', 'true');

	const target = form.querySelector(`[data-error-for="${name}"]`);

	if (target) {
		target.textContent = message;
	}
}

function applyErrors(form, errors) {
	Object.entries(errors).forEach(([name, message]) => setError(form, name, message));
	form.querySelector('[aria-invalid="true"]')?.focus();
}

function clearErrors(form) {
	form.querySelectorAll('[aria-invalid="true"]').forEach((field) => field.removeAttribute('aria-invalid'));
	form.querySelectorAll('[data-error-for]').forEach((node) => {
		node.textContent = '';
	});
}

function setStatus(node, message, type) {
	if (!node) {
		return;
	}

	node.textContent = message;

	if (type) {
		node.dataset.status = type;
		node.setAttribute('role', type === 'error' ? 'alert' : 'status');
	} else {
		delete node.dataset.status;
		node.removeAttribute('role');
	}
}

function setLoading(button, label, loading, text) {
	if (!button) {
		return;
	}

	button.disabled = loading;

	if (label) {
		label.textContent = text;
	}
}
