<?php

namespace App\Services\Recaptcha;

class LeadsRepository
{
	public const DB_VERSION = '1.0.0';
	private const OPTION_VERSION = 'raks_leads_db_version';

	public function __construct()
	{
		add_action('after_switch_theme', [$this, 'createTable']);
		add_action('init', [$this, 'maybeUpgrade']);
	}

	public function tableName(): string
	{
		global $wpdb;

		return $wpdb->prefix . 'raks_leads';
	}

	public function maybeUpgrade(): void
	{
		if (get_option(self::OPTION_VERSION) === self::DB_VERSION) {
			return;
		}

		$this->createTable();
	}

	public function createTable(): void
	{
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table = $this->tableName();
		$charset = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			created_at DATETIME NOT NULL,
			form VARCHAR(64) NOT NULL DEFAULT 'contact',
			name VARCHAR(191) NOT NULL DEFAULT '',
			email VARCHAR(191) NOT NULL DEFAULT '',
			phone VARCHAR(64) NOT NULL DEFAULT '',
			company VARCHAR(191) NOT NULL DEFAULT '',
			subject VARCHAR(191) NOT NULL DEFAULT '',
			message LONGTEXT NULL,
			payload LONGTEXT NULL,
			source_url VARCHAR(255) NOT NULL DEFAULT '',
			ip VARCHAR(45) NOT NULL DEFAULT '',
			score DECIMAL(3,2) NOT NULL DEFAULT 0.00,
			status VARCHAR(20) NOT NULL DEFAULT 'new',
			PRIMARY KEY (id),
			KEY created_at (created_at),
			KEY form (form),
			KEY status (status)
		) {$charset};";

		dbDelta($sql);

		update_option(self::OPTION_VERSION, self::DB_VERSION, false);
	}

	public function insert(array $lead): int
	{
		global $wpdb;

		$wpdb->insert(
			$this->tableName(),
			[
				'created_at' => current_time('mysql'),
				'form' => (string) ($lead['form'] ?? 'contact'),
				'name' => (string) ($lead['name'] ?? ''),
				'email' => (string) ($lead['email'] ?? ''),
				'phone' => (string) ($lead['phone'] ?? ''),
				'company' => (string) ($lead['company'] ?? ''),
				'subject' => (string) ($lead['subject'] ?? ''),
				'message' => (string) ($lead['message'] ?? ''),
				'payload' => (string) json_encode($lead['payload'] ?? [], JSON_UNESCAPED_UNICODE),
				'source_url' => (string) ($lead['source_url'] ?? ''),
				'ip' => (string) ($lead['ip'] ?? ''),
				'score' => (float) ($lead['score'] ?? 0),
				'status' => 'new',
			],
			['%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%f', '%s']
		);

		return (int) $wpdb->insert_id;
	}

	public function find(int $id): ?array
	{
		global $wpdb;

		$row = $wpdb->get_row(
			$wpdb->prepare('SELECT * FROM ' . $this->tableName() . ' WHERE id = %d', $id),
			ARRAY_A
		);

		return $row ?: null;
	}

	/** @return array{items: array<int, array<string, mixed>>, total: int} */
	public function paginate(int $page = 1, int $perPage = 25, string $form = '', string $status = ''): array
	{
		global $wpdb;

		$table = $this->tableName();
		$where = ['1=1'];
		$params = [];

		if ($form !== '') {
			$where[] = 'form = %s';
			$params[] = $form;
		}

		if ($status !== '') {
			$where[] = 'status = %s';
			$params[] = $status;
		}

		$whereSql = implode(' AND ', $where);

		$countSql = "SELECT COUNT(*) FROM {$table} WHERE {$whereSql}";
		$total = (int) $wpdb->get_var($params ? $wpdb->prepare($countSql, ...$params) : $countSql);

		$offset = max(0, ($page - 1) * $perPage);
		$listSql = "SELECT * FROM {$table} WHERE {$whereSql} ORDER BY created_at DESC LIMIT %d OFFSET %d";
		$items = (array) $wpdb->get_results(
			$wpdb->prepare($listSql, ...array_merge($params, [$perPage, $offset])),
			ARRAY_A
		);

		return ['items' => $items, 'total' => $total];
	}

	public function updateStatus(int $id, string $status): void
	{
		global $wpdb;

		$wpdb->update($this->tableName(), ['status' => $status], ['id' => $id], ['%s'], ['%d']);
	}

	public function delete(int $id): void
	{
		global $wpdb;

		$wpdb->delete($this->tableName(), ['id' => $id], ['%d']);
	}

	public function forms(): array
	{
		global $wpdb;

		return (array) $wpdb->get_col('SELECT DISTINCT form FROM ' . $this->tableName() . ' ORDER BY form ASC');
	}
}
