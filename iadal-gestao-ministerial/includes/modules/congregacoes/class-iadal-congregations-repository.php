<?php
/**
 * Congregations repository.
 *
 * @package IADAL_Gestao_Ministerial
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles persistence operations for congregations.
 */
class IADAL_Congregations_Repository {

	/**
	 * Churches table name.
	 *
	 * @var string
	 */
	private string $churches_table;

	/**
	 * Internal users table name.
	 *
	 * @var string
	 */
	private string $users_table;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->churches_table = IADAL_Database::table( 'churches' );
		$this->users_table    = IADAL_Database::table( 'users' );
	}

	/**
	 * Lists congregations using filters.
	 *
	 * @param array<string, mixed> $args Query arguments.
	 * @return array<int, array<string, mixed>>
	 */
	public function all( array $args = array() ): array {
		global $wpdb;

		$args = wp_parse_args(
			$args,
			array(
				'search'          => '',
				'status'          => '',
				'include_deleted' => false,
				'limit'           => 20,
				'offset'          => 0,
			)
		);

		$where  = array( "type = 'congregacao'" );
		$values = array();

		if ( ! (bool) $args['include_deleted'] ) {
			$where[] = 'deleted_at IS NULL';
		}

		if ( '' !== $args['search'] ) {
			$where[]  = 'name LIKE %s';
			$values[] = '%' . $wpdb->esc_like( (string) $args['search'] ) . '%';
		}

		if ( '' !== $args['status'] ) {
			$where[]  = 'status = %s';
			$values[] = (string) $args['status'];
		}

		$where_sql = 'WHERE ' . implode( ' AND ', $where );
		$values[]  = max( 1, (int) $args['limit'] );
		$values[]  = max( 0, (int) $args['offset'] );
		$fields    = 'id, name, pastor_name, pastor_phone, secretary_name, secretary_phone, city, state, status, created_at';
		$sql       = "SELECT {$fields} FROM {$this->churches_table} {$where_sql} ORDER BY name ASC LIMIT %d OFFSET %d";

		return $wpdb->get_results( $wpdb->prepare( $sql, $values ), ARRAY_A );
	}

	/**
	 * Counts congregations using filters.
	 *
	 * @param array<string, mixed> $args Query arguments.
	 * @return int
	 */
	public function count( array $args = array() ): int {
		global $wpdb;

		$args = wp_parse_args(
			$args,
			array(
				'search'          => '',
				'status'          => '',
				'include_deleted' => false,
			)
		);

		$where  = array( "type = 'congregacao'" );
		$values = array();

		if ( ! (bool) $args['include_deleted'] ) {
			$where[] = 'deleted_at IS NULL';
		}

		if ( '' !== $args['search'] ) {
			$where[]  = 'name LIKE %s';
			$values[] = '%' . $wpdb->esc_like( (string) $args['search'] ) . '%';
		}

		if ( '' !== $args['status'] ) {
			$where[]  = 'status = %s';
			$values[] = (string) $args['status'];
		}

		$where_sql = 'WHERE ' . implode( ' AND ', $where );
		$sql       = "SELECT COUNT(*) FROM {$this->churches_table} {$where_sql}";

		if ( $values ) {
			return (int) $wpdb->get_var( $wpdb->prepare( $sql, $values ) );
		}

		return (int) $wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- No dynamic user value is present.
	}

	/**
	 * Finds one congregation by ID.
	 *
	 * @param int $id Congregation ID.
	 * @return array<string, mixed>|null
	 */
	public function find( int $id ): ?array {
		global $wpdb;

		$sql   = "SELECT * FROM {$this->churches_table} WHERE id = %d AND type = 'congregacao' AND deleted_at IS NULL LIMIT 1";
		$church = $wpdb->get_row( $wpdb->prepare( $sql, $id ), ARRAY_A );

		return $church ?: null;
	}

	/**
	 * Creates a congregation.
	 *
	 * @param array<string, mixed> $data Sanitized congregation data.
	 * @return int|false
	 */
	public function create( array $data ) {
		global $wpdb;

		$now                = IADAL_Database::now();
		$data['type']       = 'congregacao';
		$data['created_at'] = $now;
		$data['updated_at'] = $now;
		$data['created_by'] = get_current_user_id();
		$data['updated_by'] = get_current_user_id();

		$result = $wpdb->insert( $this->churches_table, $data, $this->church_formats( $data ) );

		if ( false === $result ) {
			return false;
		}

		return (int) $wpdb->insert_id;
	}

	/**
	 * Updates a congregation.
	 *
	 * @param int                  $id Congregation ID.
	 * @param array<string, mixed> $data Sanitized congregation data.
	 * @return bool
	 */
	public function update( int $id, array $data ): bool {
		global $wpdb;

		$data['updated_at'] = IADAL_Database::now();
		$data['updated_by'] = get_current_user_id();

		$result = $wpdb->update(
			$this->churches_table,
			$data,
			array( 'id' => $id ),
			$this->church_formats( $data ),
			array( '%d' )
		);

		return false !== $result;
	}

	/**
	 * Creates an internal user for a congregation.
	 *
	 * @param array<string, mixed> $data Sanitized user data.
	 * @return int|false
	 */
	public function create_user( array $data ) {
		global $wpdb;

		$now                = IADAL_Database::now();
		$data['created_at'] = $now;
		$data['updated_at'] = $now;
		$data['created_by'] = get_current_user_id();
		$data['updated_by'] = get_current_user_id();

		$result = $wpdb->insert( $this->users_table, $data, $this->user_formats( $data ) );

		if ( false === $result ) {
			return false;
		}

		return (int) $wpdb->insert_id;
	}

	/**
	 * Updates an internal user.
	 *
	 * @param int                  $id User ID.
	 * @param array<string, mixed> $data Sanitized user data.
	 * @return bool
	 */
	public function update_user( int $id, array $data ): bool {
		global $wpdb;

		$data['updated_at'] = IADAL_Database::now();
		$data['updated_by'] = get_current_user_id();

		$result = $wpdb->update(
			$this->users_table,
			$data,
			array( 'id' => $id ),
			$this->user_formats( $data ),
			array( '%d' )
		);

		return false !== $result;
	}

	/**
	 * Finds internal users for a congregation.
	 *
	 * @param int $church_id Congregation ID.
	 * @return array<int, array<string, mixed>>
	 */
	public function users_for_church( int $church_id ): array {
		global $wpdb;

		$fields = 'id, name, login, role, phone, status, must_change_password, created_at';
		$sql    = "SELECT {$fields} FROM {$this->users_table} WHERE church_id = %d AND deleted_at IS NULL ORDER BY role ASC";

		return $wpdb->get_results( $wpdb->prepare( $sql, $church_id ), ARRAY_A );
	}

	/**
	 * Checks if a login already exists.
	 *
	 * @param string $login Internal user login.
	 * @return bool
	 */
	public function login_exists( string $login ): bool {
		global $wpdb;

		$sql = "SELECT id FROM {$this->users_table} WHERE login = %s LIMIT 1";

		return null !== $wpdb->get_var( $wpdb->prepare( $sql, $login ) );
	}

	/**
	 * Changes congregation status and mirrors leadership user status.
	 *
	 * @param int    $church_id Congregation ID.
	 * @param string $status New status.
	 * @return bool
	 */
	public function set_status( int $church_id, string $status ): bool {
		global $wpdb;

		$updated = $this->update(
			$church_id,
			array(
				'status' => $status,
			)
		);

		if ( ! $updated ) {
			return false;
		}

		$user_status = 'bloqueado' === $status ? 'bloqueado' : 'ativo';
		$result      = $wpdb->update(
			$this->users_table,
			array(
				'status'     => $user_status,
				'updated_at' => IADAL_Database::now(),
				'updated_by' => get_current_user_id(),
			),
			array( 'church_id' => $church_id ),
			array( '%s', '%s', '%d' ),
			array( '%d' )
		);

		return false !== $result;
	}

	/**
	 * Soft deletes a congregation.
	 *
	 * @param int $church_id Congregation ID.
	 * @return bool
	 */
	public function delete( int $church_id ): bool {
		global $wpdb;

		$now = IADAL_Database::now();

		$church_deleted = $wpdb->update(
			$this->churches_table,
			array(
				'deleted_at' => $now,
				'deleted_by' => get_current_user_id(),
				'status'     => 'inativo',
			),
			array( 'id' => $church_id ),
			array( '%s', '%d', '%s' ),
			array( '%d' )
		);

		if ( false === $church_deleted ) {
			return false;
		}

		$users_deleted = $wpdb->update(
			$this->users_table,
			array(
				'deleted_at' => $now,
				'deleted_by' => get_current_user_id(),
				'status'     => 'inativo',
			),
			array( 'church_id' => $church_id ),
			array( '%s', '%d', '%s' ),
			array( '%d' )
		);

		return false !== $users_deleted;
	}

	/**
	 * Returns formats for church insert/update payloads.
	 *
	 * @param array<string, mixed> $data Data payload.
	 * @return array<int, string>
	 */
	private function church_formats( array $data ): array {
		$integer_fields = array(
			'pastor_user_id',
			'secretary_user_id',
			'cleaning_requests_blocked',
			'created_by',
			'updated_by',
			'deleted_by',
		);

		return $this->formats_for_fields( $data, $integer_fields );
	}

	/**
	 * Returns formats for user insert/update payloads.
	 *
	 * @param array<string, mixed> $data Data payload.
	 * @return array<int, string>
	 */
	private function user_formats( array $data ): array {
		$integer_fields = array(
			'church_id',
			'department_id',
			'member_id',
			'must_change_password',
			'failed_login_attempts',
			'created_by',
			'updated_by',
			'deleted_by',
		);

		return $this->formats_for_fields( $data, $integer_fields );
	}

	/**
	 * Returns formats for provided fields.
	 *
	 * @param array<string, mixed> $data Data payload.
	 * @param array<int, string>   $integer_fields Integer field names.
	 * @return array<int, string>
	 */
	private function formats_for_fields( array $data, array $integer_fields ): array {
		$formats = array();

		foreach ( array_keys( $data ) as $field ) {
			$formats[] = in_array( $field, $integer_fields, true ) ? '%d' : '%s';
		}

		return $formats;
	}
}
