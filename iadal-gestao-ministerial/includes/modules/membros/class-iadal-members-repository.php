<?php
/**
 * Members repository.
 *
 * @package IADAL_Gestao_Ministerial
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles all persistence operations for members.
 */
class IADAL_Members_Repository {

	/**
	 * Members table name.
	 *
	 * @var string
	 */
	private string $table;

	/**
	 * Member documents table name.
	 *
	 * @var string
	 */
	private string $documents_table;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->table           = IADAL_Database::table( 'members' );
		$this->documents_table = IADAL_Database::table( 'member_documents' );
	}

	/**
	 * Lists members using filters.
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
				'entry_type'      => '',
				'birth_month'     => '',
				'include_deleted' => false,
				'limit'           => 50,
				'offset'          => 0,
			)
		);

		$where  = array();
		$values = array();

		if ( ! (bool) $args['include_deleted'] ) {
			$where[] = 'deleted_at IS NULL';
		}

		if ( '' !== $args['search'] ) {
			$where[]  = 'full_name LIKE %s';
			$values[] = '%' . $wpdb->esc_like( (string) $args['search'] ) . '%';
		}

		if ( '' !== $args['status'] ) {
			$where[]  = 'status = %s';
			$values[] = (string) $args['status'];
		}

		if ( '' !== $args['entry_type'] ) {
			$where[]  = 'entry_type = %s';
			$values[] = (string) $args['entry_type'];
		}

		if ( '' !== $args['birth_month'] ) {
			$where[]  = 'birth_month = %d';
			$values[] = (int) $args['birth_month'];
		}

		$where_sql = $where ? 'WHERE ' . implode( ' AND ', $where ) : '';
		$values[]  = max( 1, (int) $args['limit'] );
		$values[]  = max( 0, (int) $args['offset'] );

		$fields = 'id, photo_attachment_id, full_name, cpf, phone, email, birth_date, entry_type, status';
		$sql    = "SELECT {$fields} FROM {$this->table} {$where_sql} ORDER BY full_name ASC LIMIT %d OFFSET %d";

		return $wpdb->get_results( $wpdb->prepare( $sql, $values ), ARRAY_A );
	}

	/**
	 * Counts members using filters.
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
				'entry_type'      => '',
				'birth_month'     => '',
				'include_deleted' => false,
			)
		);

		$where  = array();
		$values = array();

		if ( ! (bool) $args['include_deleted'] ) {
			$where[] = 'deleted_at IS NULL';
		}

		if ( '' !== $args['search'] ) {
			$where[]  = 'full_name LIKE %s';
			$values[] = '%' . $wpdb->esc_like( (string) $args['search'] ) . '%';
		}

		if ( '' !== $args['status'] ) {
			$where[]  = 'status = %s';
			$values[] = (string) $args['status'];
		}

		if ( '' !== $args['entry_type'] ) {
			$where[]  = 'entry_type = %s';
			$values[] = (string) $args['entry_type'];
		}

		if ( '' !== $args['birth_month'] ) {
			$where[]  = 'birth_month = %d';
			$values[] = (int) $args['birth_month'];
		}

		$where_sql = $where ? 'WHERE ' . implode( ' AND ', $where ) : '';
		$sql       = "SELECT COUNT(*) FROM {$this->table} {$where_sql}";

		if ( $values ) {
			return (int) $wpdb->get_var( $wpdb->prepare( $sql, $values ) );
		}

		return (int) $wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- No dynamic user value is present.
	}

	/**
	 * Finds one member by ID.
	 *
	 * @param int $id Member ID.
	 * @return array<string, mixed>|null
	 */
	public function find( int $id ): ?array {
		global $wpdb;

		$sql    = "SELECT * FROM {$this->table} WHERE id = %d AND deleted_at IS NULL LIMIT 1";
		$member = $wpdb->get_row( $wpdb->prepare( $sql, $id ), ARRAY_A );

		return $member ?: null;
	}

	/**
	 * Creates a member.
	 *
	 * @param array<string, mixed> $data Sanitized member data.
	 * @return int|false
	 */
	public function create( array $data ) {
		global $wpdb;

		$now                    = IADAL_Database::now();
		$data['created_at']     = $now;
		$data['updated_at']     = $now;
		$data['created_by']     = get_current_user_id();
		$data['updated_by']     = get_current_user_id();
		$data['birth_month']    = $this->get_birth_month( $data['birth_date'] ?? '' );
		$data['photo_attachment_id'] = ! empty( $data['photo_attachment_id'] ) ? (int) $data['photo_attachment_id'] : null;

		$result = $wpdb->insert( $this->table, $data, $this->formats( $data ) );

		if ( false === $result ) {
			return false;
		}

		return (int) $wpdb->insert_id;
	}

	/**
	 * Updates a member.
	 *
	 * @param int                  $id Member ID.
	 * @param array<string, mixed> $data Sanitized member data.
	 * @return bool
	 */
	public function update( int $id, array $data ): bool {
		global $wpdb;

		$data['updated_at'] = IADAL_Database::now();
		$data['updated_by'] = get_current_user_id();
		$data['birth_month'] = $this->get_birth_month( $data['birth_date'] ?? '' );

		if ( array_key_exists( 'photo_attachment_id', $data ) ) {
			$data['photo_attachment_id'] = ! empty( $data['photo_attachment_id'] ) ? (int) $data['photo_attachment_id'] : null;
		}

		$result = $wpdb->update(
			$this->table,
			$data,
			array( 'id' => $id ),
			$this->formats( $data ),
			array( '%d' )
		);

		return false !== $result;
	}

	/**
	 * Soft deletes a member.
	 *
	 * @param int $id Member ID.
	 * @return bool
	 */
	public function delete( int $id ): bool {
		global $wpdb;

		$result = $wpdb->update(
			$this->table,
			array(
				'deleted_at' => IADAL_Database::now(),
				'deleted_by' => get_current_user_id(),
			),
			array( 'id' => $id ),
			array( '%s', '%d' ),
			array( '%d' )
		);

		return false !== $result;
	}

	/**
	 * Checks whether a CPF already exists.
	 *
	 * @param string $cpf CPF with digits only.
	 * @param int    $ignore_id Optional member ID to ignore.
	 * @return bool
	 */
	public function cpf_exists( string $cpf, int $ignore_id = 0 ): bool {
		global $wpdb;

		if ( $ignore_id > 0 ) {
			$sql = "SELECT id FROM {$this->table} WHERE cpf = %s AND id <> %d LIMIT 1";

			return null !== $wpdb->get_var( $wpdb->prepare( $sql, $cpf, $ignore_id ) );
		}

		$sql = "SELECT id FROM {$this->table} WHERE cpf = %s LIMIT 1";

		return null !== $wpdb->get_var( $wpdb->prepare( $sql, $cpf ) );
	}

	/**
	 * Lists birthday members for a month.
	 *
	 * @param int $month Month number.
	 * @param int $limit Results limit.
	 * @param int $offset Results offset.
	 * @return array<int, array<string, mixed>>
	 */
	public function birthdays( int $month, int $limit = 50, int $offset = 0 ): array {
		return $this->all(
			array(
				'birth_month' => $month,
				'status'      => 'ativo',
				'limit'       => $limit,
				'offset'      => $offset,
			)
		);
	}

	/**
	 * Creates a protected member document record.
	 *
	 * @param int                  $member_id Member ID.
	 * @param array<string, mixed> $document Sanitized document data.
	 * @return int|false
	 */
	public function create_document( int $member_id, array $document ) {
		global $wpdb;

		$data = array(
			'member_id'     => $member_id,
			'document_type' => (string) $document['document_type'],
			'title'         => (string) $document['title'],
			'file_name'     => (string) $document['file_name'],
			'file_path'     => (string) $document['file_path'],
			'mime_type'     => (string) $document['mime_type'],
			'file_size'     => (int) $document['file_size'],
			'notes'         => isset( $document['notes'] ) ? (string) $document['notes'] : '',
			'uploaded_by'   => get_current_user_id(),
			'created_at'    => IADAL_Database::now(),
		);

		$result = $wpdb->insert(
			$this->documents_table,
			$data,
			array( '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%d', '%s' )
		);

		if ( false === $result ) {
			return false;
		}

		return (int) $wpdb->insert_id;
	}

	/**
	 * Checks whether a member has a protected document type.
	 *
	 * @param int    $member_id Member ID.
	 * @param string $document_type Document type.
	 * @return bool
	 */
	public function member_has_document_type( int $member_id, string $document_type ): bool {
		global $wpdb;

		$sql = "SELECT id FROM {$this->documents_table} WHERE member_id = %d AND document_type = %s AND deleted_at IS NULL LIMIT 1";

		return null !== $wpdb->get_var( $wpdb->prepare( $sql, $member_id, $document_type ) );
	}

	/**
	 * Finds one protected document.
	 *
	 * @param int $document_id Document ID.
	 * @return array<string, mixed>|null
	 */
	public function find_document( int $document_id ): ?array {
		global $wpdb;

		$sql      = "SELECT * FROM {$this->documents_table} WHERE id = %d AND deleted_at IS NULL LIMIT 1";
		$document = $wpdb->get_row( $wpdb->prepare( $sql, $document_id ), ARRAY_A );

		return $document ?: null;
	}

	/**
	 * Returns the latest document for a member and type.
	 *
	 * @param int    $member_id Member ID.
	 * @param string $document_type Document type.
	 * @return array<string, mixed>|null
	 */
	public function latest_document( int $member_id, string $document_type ): ?array {
		global $wpdb;

		$sql = "SELECT * FROM {$this->documents_table} WHERE member_id = %d AND document_type = %s AND deleted_at IS NULL ORDER BY id DESC LIMIT 1";
		$document = $wpdb->get_row( $wpdb->prepare( $sql, $member_id, $document_type ), ARRAY_A );

		return $document ?: null;
	}

	/**
	 * Returns insert/update formats for a data payload.
	 *
	 * @param array<string, mixed> $data Data payload.
	 * @return array<int, string>
	 */
	private function formats( array $data ): array {
		$integer_fields = array(
			'photo_attachment_id',
			'created_by',
			'updated_by',
			'deleted_by',
			'birth_month',
		);

		$formats = array();

		foreach ( array_keys( $data ) as $field ) {
			$formats[] = in_array( $field, $integer_fields, true ) ? '%d' : '%s';
		}

		return $formats;
	}

	/**
	 * Gets the birth month for indexing reports.
	 *
	 * @param mixed $birth_date Date value.
	 * @return int|null
	 */
	private function get_birth_month( $birth_date ): ?int {
		if ( empty( $birth_date ) || ! is_string( $birth_date ) ) {
			return null;
		}

		$timestamp = strtotime( $birth_date );

		if ( false === $timestamp ) {
			return null;
		}

		return (int) gmdate( 'n', $timestamp );
	}
}
