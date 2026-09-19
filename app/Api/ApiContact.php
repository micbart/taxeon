<?php

namespace App\Api;

use App\Services\Recaptcha\LeadsRepository;
use App\Services\Recaptcha\RecaptchaService;
use WP_REST_Request;
use WP_REST_Response;

class ApiContact extends AbstractApi
{
	protected string $route = '/contact';
	protected string $methods = 'POST';

	private RecaptchaService $recaptcha;
	private LeadsRepository $leads;

	public function __construct(RecaptchaService $recaptcha, LeadsRepository $leads)
	{
		$this->recaptcha = $recaptcha;
		$this->leads = $leads;

		parent::__construct();
	}

	protected function args(): array
	{
		return [
			'name' => ['type' => 'string', 'required' => false],
			'email' => ['type' => 'string', 'required' => true],
			'message' => ['type' => 'string', 'required' => false],
		];
	}

	public function handle(WP_REST_Request $request): WP_REST_Response
	{
		$ip = $this->recaptcha->clientIp();

		if (!$this->rateLimit('contact', $ip)) {
			return $this->error(__('Zbyt wiele zgłoszeń. Spróbuj ponownie za kilka minut.', 'taxeon'), 429);
		}

		if (trim((string) $request->get_param('website')) !== '') {
			return $this->success(['message' => __('Dziękujemy, wiadomość została wysłana.', 'taxeon')]);
		}

		$name = sanitize_text_field((string) $request->get_param('name'));
		$email = sanitize_email((string) $request->get_param('email'));
		$phone = sanitize_text_field((string) $request->get_param('phone'));
		$company = sanitize_text_field((string) $request->get_param('company'));
		$subject = sanitize_text_field((string) $request->get_param('subject'));
		$message = sanitize_textarea_field((string) $request->get_param('message'));
		$consent = (bool) $request->get_param('consent');

		$errors = [];

		if ($request->has_param('name') && $name === '') {
			$errors['name'] = __('Podaj imię i nazwisko.', 'taxeon');
		}

		if ($email === '' || !is_email($email)) {
			$errors['email'] = __('Podaj poprawny adres e-mail.', 'taxeon');
		}

		if ($request->has_param('message') && $message === '') {
			$errors['message'] = __('Wpisz treść wiadomości.', 'taxeon');
		}

		if ($request->has_param('consent') && !$consent) {
			$errors['consent'] = __('Zaznacz wymaganą zgodę.', 'taxeon');
		}

		if ($request->has_param('consent_rodo') && !$request->get_param('consent_rodo')) {
			$errors['consent_rodo'] = __('Zaznacz wymaganą zgodę.', 'taxeon');
		}

		if ($errors) {
			return $this->error(__('Popraw zaznaczone pola.', 'taxeon'), 422, ['errors' => $errors]);
		}

		$verification = $this->recaptcha->verify((string) $request->get_param('token'), 'contact');

		if (!$verification['success']) {
			return $this->error($verification['message'], 403);
		}

		$sourceUrl = esc_url_raw((string) $request->get_param('sourceUrl'));

		$leadId = $this->leads->insert([
			'form' => sanitize_key((string) ($request->get_param('form') ?: 'contact')),
			'name' => $name,
			'email' => $email,
			'phone' => $phone,
			'company' => $company,
			'subject' => $subject,
			'message' => $message,
			'payload' => $this->extraFields($request),
			'source_url' => $sourceUrl,
			'ip' => $ip,
			'score' => $verification['score'],
		]);

		$this->notify($leadId, compact('name', 'email', 'phone', 'company', 'subject', 'message', 'sourceUrl'));

		do_action('taxeon_contact_submitted', $leadId, $request);

		return $this->success([
			'message' => __('Dziękujemy, wiadomość została wysłana.', 'taxeon'),
			'leadId' => $leadId,
		]);
	}

	private function extraFields(WP_REST_Request $request): array
	{
		$known = ['name', 'email', 'phone', 'company', 'subject', 'message', 'consent', 'token', 'form', 'sourceUrl', 'website'];
		$extra = [];

		foreach ((array) $request->get_params() as $key => $value) {
			if (in_array($key, $known, true)) {
				continue;
			}

			$extra[sanitize_key($key)] = is_scalar($value)
				? sanitize_text_field((string) $value)
				: array_map('sanitize_text_field', (array) $value);
		}

		return $extra;
	}

	private function notify(int $leadId, array $lead): void
	{
		$to = function_exists('get_field') ? trim((string) get_field('contact_email', 'option')) : '';

		if ($to === '' || !is_email($to)) {
			$to = get_option('admin_email');
		}

		$subject = sprintf(
			__('[%1$s] Nowe zgłoszenie: %2$s', 'taxeon'),
			get_bloginfo('name'),
			$lead['subject'] !== '' ? $lead['subject'] : __('formularz kontaktowy', 'taxeon')
		);

		$body = implode("\n", array_filter([
			sprintf(__('Imię i nazwisko: %s', 'taxeon'), $lead['name']),
			sprintf(__('E-mail: %s', 'taxeon'), $lead['email']),
			$lead['phone'] !== '' ? sprintf(__('Telefon: %s', 'taxeon'), $lead['phone']) : '',
			$lead['company'] !== '' ? sprintf(__('Firma: %s', 'taxeon'), $lead['company']) : '',
			'',
			__('Wiadomość:', 'taxeon'),
			$lead['message'],
			'',
			sprintf(__('Wysłano ze strony: %s', 'taxeon'), $lead['sourceUrl']),
			sprintf(__('Podgląd w panelu: %s', 'taxeon'), admin_url('admin.php?page=taxeon-leads')),
		]));

		wp_mail(
			$to,
			$subject,
			$body,
			[
				'Content-Type: text/plain; charset=UTF-8',
				'Reply-To: ' . $lead['name'] . ' <' . $lead['email'] . '>',
			]
		);
	}
}
