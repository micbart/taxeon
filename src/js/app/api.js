const config = () => window.dataTheme ?? {};

export const i18n = (path, fallback = '') => {
	const value = path.split('.').reduce((acc, key) => acc?.[key], config().i18n);

	return value ?? fallback;
};

export async function apiGet(endpoint, params = {}) {
	const url = new URL(config().restUrl + endpoint);

	Object.entries(params).forEach(([key, value]) => {
		if (value !== '' && value !== null && value !== undefined) {
			url.searchParams.set(key, value);
		}
	});

	const response = await fetch(url, {
		headers: { 'X-WP-Nonce': config().restNonce ?? '' },
	});

	return response.json();
}

export async function apiPost(endpoint, payload = {}) {
	const response = await fetch(config().restUrl + endpoint, {
		method: 'POST',
		headers: {
			'Content-Type': 'application/json',
			'X-WP-Nonce': config().restNonce ?? '',
		},
		body: JSON.stringify(payload),
	});

	const data = await response.json().catch(() => ({}));

	return { ok: response.ok, status: response.status, data };
}

export function recaptchaToken(action) {
	const { recaptcha } = config();

	if (!recaptcha?.enabled || !window.grecaptcha) {
		return Promise.resolve('');
	}

	return new Promise((resolve) => {
		window.grecaptcha.ready(() => {
			window.grecaptcha
				.execute(recaptcha.siteKey, { action })
				.then(resolve)
				.catch(() => resolve(''));
		});
	});
}
