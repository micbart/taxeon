<?php

namespace App\Services\Recaptcha;

class LeadsAdmin
{
	private const CAPABILITY = 'manage_options';
	private const SLUG = 'raks-leads';
	private const PER_PAGE = 25;

	private LeadsRepository $repository;

	public function __construct(LeadsRepository $repository)
	{
		$this->repository = $repository;

		add_action('admin_menu', [$this, 'registerMenu']);
		add_action('admin_post_raks_lead_action', [$this, 'handleAction']);
		add_action('admin_post_raks_leads_export', [$this, 'exportCsv']);
	}

	public function registerMenu(): void
	{
		add_menu_page(
			__('Zgłoszenia', 'raks'),
			__('Zgłoszenia', 'raks'),
			self::CAPABILITY,
			self::SLUG,
			[$this, 'renderPage'],
			'dashicons-email-alt',
			26
		);
	}

	public function handleAction(): void
	{
		if (!current_user_can(self::CAPABILITY)) {
			wp_die(esc_html__('Brak uprawnień.', 'raks'));
		}

		$id = isset($_POST['lead_id']) ? (int) $_POST['lead_id'] : 0;
		$action = isset($_POST['lead_action']) ? sanitize_key((string) $_POST['lead_action']) : '';

		check_admin_referer('raks_lead_' . $id);

		if ($id > 0) {
			if ($action === 'delete') {
				$this->repository->delete($id);
			} elseif (in_array($action, ['new', 'handled', 'spam'], true)) {
				$this->repository->updateStatus($id, $action);
			}
		}

		wp_safe_redirect(wp_get_referer() ?: admin_url('admin.php?page=' . self::SLUG));
		exit;
	}

	public function exportCsv(): void
	{
		if (!current_user_can(self::CAPABILITY)) {
			wp_die(esc_html__('Brak uprawnień.', 'raks'));
		}

		check_admin_referer('raks_leads_export');

		$form = isset($_GET['form']) ? sanitize_text_field((string) $_GET['form']) : '';
		$result = $this->repository->paginate(1, 10000, $form);

		nocache_headers();
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename=zgloszenia-' . gmdate('Y-m-d') . '.csv');

		$out = fopen('php://output', 'w');
		fwrite($out, "\xEF\xBB\xBF");
		fputcsv($out, [
			'ID',
			'Data',
			'Formularz',
			'Imię i nazwisko',
			'E-mail',
			'Telefon',
			'Firma',
			'Temat',
			'Wiadomość',
			'Pola dodatkowe',
			'Adres strony',
			'IP',
			'Score',
			'Status',
		]);

		foreach ($result['items'] as $row) {
			fputcsv($out, array_map([$this, 'csvCell'], [
				$row['id'],
				$row['created_at'],
				$row['form'],
				$row['name'],
				$row['email'],
				$row['phone'],
				$row['company'],
				$row['subject'],
				$row['message'],
				$this->payloadText($row['payload'] ?? ''),
				$row['source_url'],
				$row['ip'],
				$row['score'],
				$row['status'],
			]));
		}

		fclose($out);
		exit;
	}

	/** @return array<string, string> */
	private function payloadFields($payload): array
	{
		$data = is_array($payload) ? $payload : json_decode((string) $payload, true);

		if (!is_array($data)) {
			return [];
		}

		$out = [];

		foreach ($data as $key => $value) {
			$flat = is_scalar($value) ? (string) $value : implode(', ', array_map('strval', (array) $value));

			if (trim($flat) !== '') {
				$out[(string) $key] = $flat;
			}
		}

		return $out;
	}

	private function payloadText($payload): string
	{
		$parts = [];

		foreach ($this->payloadFields($payload) as $key => $value) {
			$parts[] = $key . ': ' . $value;
		}

		return implode(' | ', $parts);
	}

	private function csvCell($value): string
	{
		$value = (string) $value;

		return $value !== '' && str_contains("=+-@\t\r", $value[0]) ? "'" . $value : $value;
	}

	public function renderPage(): void
	{
		if (!current_user_can(self::CAPABILITY)) {
			return;
		}

		$page = isset($_GET['paged']) ? max(1, (int) $_GET['paged']) : 1;
		$form = isset($_GET['form']) ? sanitize_text_field((string) $_GET['form']) : '';
		$status = isset($_GET['status']) ? sanitize_key((string) $_GET['status']) : '';

		$result = $this->repository->paginate($page, self::PER_PAGE, $form, $status);
		$totalPages = (int) ceil($result['total'] / self::PER_PAGE);

		$statuses = [
			'' => __('Wszystkie statusy', 'raks'),
			'new' => __('Nowe', 'raks'),
			'handled' => __('Obsłużone', 'raks'),
			'spam' => __('Spam', 'raks'),
		];

		echo '<div class="wrap">';
		echo '<h1 class="wp-heading-inline">' . esc_html__('Zgłoszenia', 'raks') . '</h1>';

		$exportUrl = wp_nonce_url(
			admin_url('admin-post.php?action=raks_leads_export&form=' . rawurlencode($form)),
			'raks_leads_export'
		);
		echo ' <a href="' . esc_url($exportUrl) . '" class="page-title-action">' . esc_html__('Eksport CSV', 'raks') . '</a>';

		echo '<form method="get" style="margin:16px 0;display:flex;gap:8px;align-items:center;">';
		echo '<input type="hidden" name="page" value="' . esc_attr(self::SLUG) . '">';

		echo '<select name="form"><option value="">' . esc_html__('Wszystkie formularze', 'raks') . '</option>';
		foreach ($this->repository->forms() as $formName) {
			printf(
				'<option value="%1$s"%2$s>%1$s</option>',
				esc_attr($formName),
				selected($form, $formName, false)
			);
		}
		echo '</select>';

		echo '<select name="status">';
		foreach ($statuses as $value => $label) {
			printf(
				'<option value="%s"%s>%s</option>',
				esc_attr($value),
				selected($status, $value, false),
				esc_html($label)
			);
		}
		echo '</select>';

		submit_button(__('Filtruj', 'raks'), 'secondary', '', false);
		echo '</form>';

		echo '<table class="wp-list-table widefat fixed striped">';
		echo '<thead><tr>';
		echo '<th style="width:140px">' . esc_html__('Data', 'raks') . '</th>';
		echo '<th style="width:110px">' . esc_html__('Formularz', 'raks') . '</th>';
		echo '<th style="width:170px">' . esc_html__('Kontakt', 'raks') . '</th>';
		echo '<th>' . esc_html__('Wiadomość', 'raks') . '</th>';
		echo '<th style="width:90px">' . esc_html__('Status', 'raks') . '</th>';
		echo '<th style="width:220px">' . esc_html__('Akcje', 'raks') . '</th>';
		echo '</tr></thead><tbody>';

		if (empty($result['items'])) {
			echo '<tr><td colspan="6">' . esc_html__('Brak zgłoszeń.', 'raks') . '</td></tr>';
		}

		foreach ($result['items'] as $row) {
			$id = (int) $row['id'];

			echo '<tr>';
			echo '<td>' . esc_html(mysql2date('d.m.Y H:i', (string) $row['created_at'])) . '</td>';
			echo '<td>' . esc_html((string) $row['form']) . '</td>';

			echo '<td><strong>' . esc_html((string) $row['name']) . '</strong><br>';
			if ($row['email'] !== '') {
				echo '<a href="mailto:' . esc_attr((string) $row['email']) . '">' . esc_html((string) $row['email']) . '</a><br>';
			}
			echo esc_html((string) $row['phone']) . '</td>';

			echo '<td>';
			if ($row['subject'] !== '') {
				echo '<strong>' . esc_html((string) $row['subject']) . '</strong><br>';
			}
			echo '<div style="white-space:pre-wrap">'
				. esc_html(wp_trim_words((string) $row['message'], 40))
				. '</div>';

			$extra = $this->payloadFields($row['payload'] ?? '');

			if ($extra) {
				echo '<div style="margin-top:6px;font-size:12px;color:#444">';
				foreach ($extra as $key => $value) {
					echo '<span style="display:inline-block;margin-right:10px">'
						. '<strong>' . esc_html($key) . ':</strong> ' . esc_html($value)
						. '</span>';
				}
				echo '</div>';
			}

			echo '<small style="color:#666">'
				. esc_html((string) $row['source_url'])
				. ' · IP ' . esc_html((string) $row['ip'])
				. ' · score ' . esc_html((string) $row['score'])
				. '</small>';
			echo '</td>';

			echo '<td>' . esc_html($statuses[$row['status']] ?? (string) $row['status']) . '</td>';

			echo '<td>';
			foreach (['handled' => __('Obsłużone', 'raks'), 'spam' => __('Spam', 'raks'), 'delete' => __('Usuń', 'raks')] as $value => $label) {
				echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" style="display:inline-block;margin-right:4px">';
				echo '<input type="hidden" name="action" value="raks_lead_action">';
				echo '<input type="hidden" name="lead_id" value="' . esc_attr((string) $id) . '">';
				echo '<input type="hidden" name="lead_action" value="' . esc_attr($value) . '">';
				wp_nonce_field('raks_lead_' . $id);
				$class = $value === 'delete' ? 'button button-small button-link-delete' : 'button button-small';
				echo '<button type="submit" class="' . esc_attr($class) . '">' . esc_html($label) . '</button>';
				echo '</form>';
			}
			echo '</td>';

			echo '</tr>';
		}

		echo '</tbody></table>';

		if ($totalPages > 1) {
			echo '<div class="tablenav"><div class="tablenav-pages">';
			echo paginate_links([
				'base' => add_query_arg('paged', '%#%'),
				'format' => '',
				'current' => $page,
				'total' => $totalPages,
			]);
			echo '</div></div>';
		}

		echo '</div>';
	}
}
