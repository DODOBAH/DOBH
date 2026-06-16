<?php
/**
 * Departments repository.
 *
 * @package IADAL_Gestao_Ministerial
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles persistence operations for departments.
 */
class IADAL_Departments_Repository {

	/**
	 * Department library table.
	 *
	 * @var string
	 */
	private string $library_table;

	/**
	 * Departments table.
	 *
	 * @var string
	 */
	private string $departments_table;

	/**
	 * Department users table.
	 *
	 * @var string
	 */
	private string $department_users_table;

	/**
	 * Internal users table.
	 *
	 * @var string
	 */
	private string $users_table;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->library_table          = IADAL_Database::table( 'department_library' );
		$this->departments_table      = IADAL_Database::table( 'departments' );
		$this->department_users_table = IADAL_Database::table( 'department_users' );
		$this->users_table            = IADAL_Database::table( 'users' );
	}

	/**
	 * Lists department library items.
	 *
	 * @param array<string, mixed> $args Query args.
	 * @return array<int, array<string, mixed>>
	 */
	public function library( array $args = array() ): array {
		global $wpdb;

		$args = wp_parse_args(
			$args,
			array(
				'search' => '',
				'type'   => '',
				'status' => '',
				'limit'  => 100,
				'offset' => 0,
			)
		);

		$where  = array( 'deleted_at IS NULL' );
		$values = array();

		if ( '' !== $args['search'] ) {
			$where[]  = 'name LIKE %s';
			$values[] = '%' . $wpdb->esc_like( (string) $args['search'] ) . '%';
		}

		if ( '' !== $args['type'] ) {
			$where[]  = 'type = %s';
			$values[] = (string) $args['type'];
		}

		if ( '' !== $args['status'] ) {
			$where[]  = 'status = %s';
			$values[] = (string) $args['status'];
		}

		$values[] = max( 1, (int) $args['limit'] );
		$values[] = max( 0, (int) $args['offset'] );
		$where_sql = implode( ' AND ', $where );
		$sql       = "SELECT * FROM {$this->library_table} WHERE {$where_sql} ORDER BY type ASC, name ASC LIMIT %d OFFSET %d";

		return $wpdb->get_results( $wpdb->prepare( $sql, $values ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Table name is generated internally.
	}

	/**
	 * Finds a library item.
	 *
	 * @param int $id Library item ID.
	 * @return array<string, mixed>|null
	 */
	public function find_library( int $id ): ?array {
		global $wpdb;

		$sql  = "SELECT * FROM {$this->library_table} WHERE id = %d AND deleted_at IS NULL LIMIT 1";
		$item = $wpdb->get_row( $wpdb->prepare( $sql, $id ), ARRAY_A );

		return $item ?: null;
	}

	/**
	 * Creates a custom library department.
	 *
	 * @param array<string, mixed> $data Data.
	 * @return int|false
	 */
	public function create_library( array $data ) {
		global $wpdb;

		$now                = IADAL_Database::now();
		$data['type']       = 'personalizado';
		$data['created_at'] = $now;
		$data['updated_at'] = $now;
		$data['created_by'] = get_current_user_id();
		$data['updated_by'] = get_current_user_id();

		$result = $wpdb->insert( $this->library_table, $data, $this->feature_formats( $data ) );

		if ( false === $result ) {
			return false;
		}

		return (int) $wpdb->insert_id;
	}

	/**
	 * Checks if a library slug exists.
	 *
	 * @param string $slug Slug.
	 * @param int    $ignore_id ID to ignore.
	 * @return bool
	 */
	public function library_slug_exists( string $slug, int $ignore_id = 0 ): bool {
		global $wpdb;

		if ( $ignore_id > 0 ) {
			$sql = "SELECT id FROM {$this->library_table} WHERE slug = %s AND id <> %d LIMIT 1";

			return null !== $wpdb->get_var( $wpdb->prepare( $sql, $slug, $ignore_id ) );
		}

		$sql = "SELECT id FROM {$this->library_table} WHERE slug = %s LIMIT 1";

		return null !== $wpdb->get_var( $wpdb->prepare( $sql, $slug ) );
	}

	/**
	 * Lists active department instances.
	 *
	 * @param array<string, mixed> $args Query args.
	 * @return array<int, array<string, mixed>>
	 */
	public function departments( array $args = array() ): array {
		global $wpdb;

		$args = wp_parse_args(
			$args,
			array(
				'church_id' => 0,
				'status'    => '',
				'search'    => '',
				'limit'     => 50,
				'offset'    => 0,
			)
		);

		$where  = array( 'd.deleted_at IS NULL' );
		$values = array();

		if ( ! empty( $args['church_id'] ) ) {
			$where[]  = 'd.church_id = %d';
			$values[] = (int) $args['church_id'];
		}

		if ( '' !== $args['status'] ) {
			$where[]  = 'd.status = %s';
			$values[] = (string) $args['status'];
		}

		if ( '' !== $args['search'] ) {
			$where[]  = 'd.name LIKE %s';
			$values[] = '%' . $wpdb->esc_like( (string) $args['search'] ) . '%';
		}

		$values[] = max( 1, (int) $args['limit'] );
		$values[] = max( 0, (int) $args['offset'] );
		$churches_table = IADAL_Database::table( 'churches' );
		$where_sql      = implode( ' AND ', $where );
		$sql            = "SELECT d.*, c.name AS church_name
			FROM {$this->departments_table} d
			LEFT JOIN {$churches_table} c ON c.id = d.church_id
			WHERE {$where_sql}
			ORDER BY c.name ASC, d.name ASC
			LIMIT %d OFFSET %d';

		return $wpdb->get_results( $wpdb->prepare( $sql, $values ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Table names are generated internally.
	}

	/**
	 * Counts active department instances.
	 *
	 * @param array<string, mixed> $args Query args.
	 * @return int
	 */
	public function count_departments( array $args = array() ): int {
		global $wpdb;

		$args = wp_parse_args(
			$args,
			array(
				'church_id' => 0,
				'status'    => '',
				'search'    => '',
			)
		);

		$where  = array( 'deleted_at IS NULL' );
		$values = array();

		if ( ! empty( $args['church_id'] ) ) {
			$where[]  = 'church_id = %d';
			$values[] = (int) $args['church_id'];
		}

		if ( '' !== $args['status'] ) {
			$where[]  = 'status = %s';
			$values[] = (string) $args['status'];
		}

		if ( '' !== $args['search'] ) {
			$where[]  = 'name LIKE %s';
			$values[] = '%' . $wpdb->esc_like( (string) $args['search'] ) . '%';
		}

		$where_sql = implode( ' AND ', $where );
		$sql       = "SELECT COUNT(*) FROM {$this->departments_table} WHERE {$where_sql}";

		if ( $values ) {
			return (int) $wpdb->get_var( $wpdb->prepare( $sql, $values ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Table name is generated internally.
		}

		return (int) $wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- No user value is present.
	}

	/**
	 * Finds a department instance.
	 *
	 * @param int $department_id Department ID.
	 * @return array<string, mixed>|null
	 */
	public function find_department( int $department_id ): ?array {
		global $wpdb;

		$sql        = "SELECT * FROM {$this->departments_table} WHERE id = %d AND deleted_at IS NULL LIMIT 1";
		$department = $wpdb->get_row( $wpdb->prepare( $sql, $department_id ), ARRAY_A );

		return $department ?: null;
	}

	/**
	 * Checks if a congregation already activated a library department.
	 *
	 * @param int $church_id Church ID.
	 * @param int $library_id Library ID.
	 * @return bool
	 */
	public function department_exists( int $church_id, int $library_id ): bool {
		global $wpdb;

		$sql = "SELECT id FROM {$this->departments_table} WHERE church_id = %d AND library_id = %d AND deleted_at IS NULL LIMIT 1";

		return null !== $wpdb->get_var( $wpdb->prepare( $sql, $church_id, $library_id ) );
	}

	/**
	 * Creates a department instance.
	 *
	 * @param array<string, mixed> $data Department data.
	 * @return int|false
	 */
	public function create_department( array $data ) {
		global $wpdb;

		$now                = IADAL_Database::now();
		$data['created_at'] = $now;
		$data['updated_at'] = $now;
		$data['created_by'] = get_current_user_id();
		$data['updated_by'] = get_current_user_id();

		$result = $wpdb->insert( $this->departments_table, $data, $this->department_formats( $data ) );

		if ( false === $result ) {
			return false;
		}

		return (int) $wpdb->insert_id;
	}

	/**
	 * Updates a department instance.
	 *
	 * @param int                  $department_id Department ID.
	 * @param array<string, mixed> $data Department data.
	 * @return bool
	 */
	public function update_department( int $department_id, array $data ): bool {
		global $wpdb;

		$data['updated_at'] = IADAL_Database::now();
		$data['updated_by'] = get_current_user_id();

		$result = $wpdb->update(
			$this->departments_table,
			$data,
			array( 'id' => $department_id ),
			$this->department_formats( $data ),
			array( '%d' )
		);

		return false !== $result;
	}

	/**
	 * Creates an internal department user.
	 *
	 * @param array<string, mixed> $data User data.
	 * @return int|false
	 */
	public function create_internal_user( array $data ) {
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
	 * @param int                  $user_id User ID.
	 * @param array<string, mixed> $data User data.
	 * @return bool
	 */
	public function update_internal_user( int $user_id, array $data ): bool {
		global $wpdb;

		$data['updated_at'] = IADAL_Database::now();
		$data['updated_by'] = get_current_user_id();

		$result = $wpdb->update(
			$this->users_table,
			$data,
			array( 'id' => $user_id ),
			$this->user_formats( $data ),
			array( '%d' )
		);

		return false !== $result;
	}

	/**
	 * Checks if an internal login already exists.
	 *
	 * @param string $login Login.
	 * @return bool
	 */
	public function login_exists( string $login ): bool {
		global $wpdb;

		$sql = "SELECT id FROM {$this->users_table} WHERE login = %s LIMIT 1";

		return null !== $wpdb->get_var( $wpdb->prepare( $sql, $login ) );
	}

	/**
	 * Adds a component to a department.
	 *
	 * @param array<string, mixed> $data Component data.
	 * @return int|false
	 */
	public function create_component( array $data ) {
		global $wpdb;

		$now                = IADAL_Database::now();
		$data['joined_at']  = $now;
		$data['created_at'] = $now;
		$data['updated_at'] = $now;
		$data['created_by'] = get_current_user_id();
		$data['updated_by'] = get_current_user_id();

		$result = $wpdb->insert( $this->department_users_table, $data, $this->component_formats( $data ) );

		if ( false === $result ) {
			return false;
		}

		return (int) $wpdb->insert_id;
	}

	/**
	 * Lists department components.
	 *
	 * @param int $department_id Department ID.
	 * @return array<int, array<string, mixed>>
	 */
	public function components( int $department_id ): array {
		global $wpdb;

		$fields = 'id, department_id, church_id, user_id, member_id, name, phone, function_name, is_leader, status, joined_at';
		$sql    = "SELECT {$fields} FROM {$this->department_users_table} WHERE department_id = %d AND deleted_at IS NULL ORDER BY is_leader DESC, name ASC";

		return $wpdb->get_results( $wpdb->prepare( $sql, $department_id ), ARRAY_A );
	}

	/**
	 * Soft deletes a department.
	 *
	 * @param int $department_id Department ID.
	 * @return bool
	 */
	public function delete_department( int $department_id ): bool {
		global $wpdb;

		$result = $wpdb->update(
			$this->departments_table,
			array(
				'status'     => 'inativo',
				'deleted_at' => IADAL_Database::now(),
				'deleted_by' => get_current_user_id(),
			),
			array( 'id' => $department_id ),
			array( '%s', '%s', '%d' ),
			array( '%d' )
		);

		return false !== $result;
	}

	/**
	 * Returns formats for fields with feature flags.
	 *
	 * @param array<string, mixed> $data Data.
	 * @return array<int, string>
	 */
	private function feature_formats( array $data ): array {
		$integer_fields = array(
			'has_chat',
			'has_notices',
			'has_files',
			'has_birthdays',
			'has_members',
			'has_schedules',
			'has_confirmations',
			'has_swaps',
			'has_statistics',
			'created_by',
			'updated_by',
			'deleted_by',
		);

		return $this->formats_for_fields( $data, $integer_fields );
	}

	/**
	 * Returns department formats.
	 *
	 * @param array<string, mixed> $data Data.
	 * @return array<int, string>
	 */
	private function department_formats( array $data ): array {
		$integer_fields = array(
			'church_id',
			'library_id',
			'leader_user_id',
			'has_chat',
			'has_notices',
			'has_files',
			'has_birthdays',
			'has_members',
			'has_schedules',
			'has_confirmations',
			'has_swaps',
			'has_statistics',
			'created_by',
			'updated_by',
			'deleted_by',
		);

		return $this->formats_for_fields( $data, $integer_fields );
	}

	/**
	 * Returns user formats.
	 *
	 * @param array<string, mixed> $data Data.
	 * @return array<int, string>
	 */
	private function user_formats( array $data ): array {
		$integer_fields = array(
			'church_id',
			'department_id',
			'member_id',
			'must_change_password',
			'blocked_by_church_status',
			'failed_login_attempts',
			'created_by',
			'updated_by',
			'deleted_by',
		);

		return $this->formats_for_fields( $data, $integer_fields );
	}

	/**
	 * Returns component formats.
	 *
	 * @param array<string, mixed> $data Data.
	 * @return array<int, string>
	 */
	private function component_formats( array $data ): array {
		$integer_fields = array(
			'department_id',
			'church_id',
			'user_id',
			'member_id',
			'is_leader',
			'created_by',
			'updated_by',
			'deleted_by',
		);

		return $this->formats_for_fields( $data, $integer_fields );
	}

	/**
	 * Returns formats for provided fields.
	 *
	 * @param array<string, mixed> $data Data.
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
