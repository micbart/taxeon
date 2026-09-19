import { apiPost, recaptchaToken, i18n } from './api.js';
import { isVerified } from './verifyBox.js';

export function initContactForm(root = document) {
	root.querySelectorAll('[data-contact-form]').forEach(setupForm);
}

function setupForm(form) {
	const status = form.querySelector('[data-form-status]');
	const submit = form.querySelector('[type="submit"]');
	const submitLabel = (submit?.querySelector('[data-submit-label]') ?? submit)?.textContent.trim() ?? '';

	form.addEventListener('submit', async (event) => {
		event.preventDefault();

		clearErrors(form);
		setStatus(status, '', '');

		const payload = Object.fromEntries(new FormData(form).entries());
		const consent = form.querySelector('[name="consent"]');

		if (consent) {
			payload.consent = consent.checked;
		}
		payload.form = form.dataset.contactForm || 'contact';
		payload.sourceUrl = window.location.href;

		const localErrors = validate(form, payload);

		if (!isVerified(form)) {
			localErrors.verified = i18n('form.verify', 'Potwierdź, że nie jesteś robotem.');
		}

		if (Object.keys(localErrors).length) {
			applyErrors(form, localErrors);
			setStatus(status, i18n('form.invalid', 'Popraw zaznaczone pola.'), 'error');
			return;
		}

		setLoading(submit, true, i18n('form.sending', 'Wysyłanie…'));

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

			form.reset();
			setStatus(status, data.message || i18n('form.sent', 'Dziękujemy!'), 'success');
			form.dispatchEvent(new CustomEvent('taxeon:contact-sent', { bubbles: true, detail: data }));
		} catch {
			setStatus(status, i18n('error', 'Coś poszło nie tak.'), 'error');
		} finally {
			setLoading(submit, false, submitLabel);
		}
	});
}

function validate(form, payload) {
	const errors = {};

	form.querySelectorAll('[required]').forEach((field) => {
		const name = field.name;

		if (field.type === 'checkbox') {
			if (!field.checked) {
				errors[name] = i18n('form.consent', 'Zaznacz wymaganą zgodę.');
			}

			return;
		}

		if (!String(payload[name] ?? '').trim()) {
			errors[name] = i18n('form.required', 'To pole jest wymagane.');
		}
	});

	const email = String(payload.email ?? '').trim();

	if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email)) {
		errors.email = i18n('form.invalidEmail', 'Podaj poprawny adres e-mail.');
	}

	return errors;
}

function applyErrors(form, errors) {
	Object.entries(errors).forEach(([name, message]) => {
		const field = form.querySelector(`[name="${name}"]`);

		if (!field) {
			return;
		}

		field.setAttribute('aria-invalid', 'true');

		const target = form.querySelector(`[data-error-for="${name}"]`);

		if (target) {
			target.textContent = message;
		}
	});

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

function setLoading(button, loading, label) {
	if (!button) {
		return;
	}

	button.disabled = loading;

	const target = button.querySelector('[data-submit-label]') ?? button;

	target.textContent = label;
}
