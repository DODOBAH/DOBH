<?php
/**
 * Departments table schema.
 *
 * @package IADAL_Gestao_Ministerial
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates and updates the departments schema.
 */
class IADAL_Departments_Schema {

	/**
	 * Creates department library, departments and department users tables.
	 *
	 * @return array
	 */
	public static function create(): array {
		global $wpdb;

		$library_table   = IADAL_Database::table( 'department_library' );
		$departments     = IADAL_Database::table( 'departments' );
		$department_users = IADAL_Database::table( 'department_users' );
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$library_table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			name varchar(190) NOT NULL,
			slug varchar(120) NOT NULL,
			type varchar(30) NOT NULL DEFAULT 'oficial',
			description text DEFAULT NULL,
			has_chat tinyint(1) unsigned NOT NULL DEFAULT 1,
			has_notices tinyint(1) unsigned NOT NULL DEFAULT 1,
			has_files tinyint(1) unsigned NOT NULL DEFAULT 1,
			has_birthdays tinyint(1) unsigned NOT NULL DEFAULT 1,
			has_members tinyint(1) unsigned NOT NULL DEFAULT 1,
			has_schedules tinyint(1) unsigned NOT NULL DEFAULT 0,
			has_confirmations tinyint(1) unsigned NOT NULL DEFAULT 0,
			has_swaps tinyint(1) unsigned NOT NULL DEFAULT 0,
			has_statistics tinyint(1) unsigned NOT NULL DEFAULT 1,
			status varchar(20) NOT NULL DEFAULT 'ativo',
			created_by bigint(20) unsigned DEFAULT NULL,
			updated_by bigint(20) unsigned DEFAULT NULL,
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			deleted_at datetime DEFAULT NULL,
			deleted_by bigint(20) unsigned DEFAULT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY slug (slug),
			KEY type (type),
			KEY status (status),
			KEY deleted_at (deleted_at)
		) {$charset_collate};";

		$sql .= "\nCREATE TABLE {$departments} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			church_id bigint(20) unsigned NOT NULL,
			library_id bigint(20) unsigned NOT NULL,
			name varchar(190) NOT NULL,
			slug varchar(120) NOT NULL,
			type varchar(30) NOT NULL DEFAULT 'oficial',
			leader_user_id bigint(20) unsigned DEFAULT NULL,
			leader_name varchar(190) DEFAULT NULL,
			leader_phone varchar(30) DEFAULT NULL,
			has_chat tinyint(1) unsigned NOT NULL DEFAULT 1,
			has_notices tinyint(1) unsigned NOT NULL DEFAULT 1,
			has_files tinyint(1) unsigned NOT NULL DEFAULT 1,
			has_birthdays tinyint(1) unsigned NOT NULL DEFAULT 1,
			has_members tinyint(1) unsigned NOT NULL DEFAULT 1,
			has_schedules tinyint(1) unsigned NOT NULL DEFAULT 0,
			has_confirmations tinyint(1) unsigned NOT NULL DEFAULT 0,
			has_swaps tinyint(1) unsigned NOT NULL DEFAULT 0,
			has_statistics tinyint(1) unsigned NOT NULL DEFAULT 1,
			status varchar(20) NOT NULL DEFAULT 'ativo',
			created_by bigint(20) unsigned DEFAULT NULL,
			updated_by bigint(20) unsigned DEFAULT NULL,
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			deleted_at datetime DEFAULT NULL,
			deleted_by bigint(20) unsigned DEFAULT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY church_library (church_id, library_id),
			KEY church_id (church_id),
			KEY library_id (library_id),
			KEY leader_user_id (leader_user_id),
			KEY status (status),
			KEY deleted_at (deleted_at)
		) {$charset_collate};";

		$sql .= "\nCREATE TABLE {$department_users} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			department_id bigint(20) unsigned NOT NULL,
			church_id bigint(20) unsigned NOT NULL,
			user_id bigint(20) unsigned DEFAULT NULL,
			member_id bigint(20) unsigned DEFAULT NULL,
			name varchar(190) NOT NULL,
			phone varchar(30) DEFAULT NULL,
			function_name varchar(120) DEFAULT NULL,
			is_leader tinyint(1) unsigned NOT NULL DEFAULT 0,
			status varchar(20) NOT NULL DEFAULT 'ativo',
			joined_at datetime DEFAULT NULL,
			created_by bigint(20) unsigned DEFAULT NULL,
			updated_by bigint(20) unsigned DEFAULT NULL,
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			deleted_at datetime DEFAULT NULL,
			deleted_by bigint(20) unsigned DEFAULT NULL,
			PRIMARY KEY  (id),
			KEY department_id (department_id),
			KEY church_id (church_id),
			KEY user_id (user_id),
			KEY member_id (member_id),
			KEY status (status),
			KEY deleted_at (deleted_at)
		) {$charset_collate};";

		return IADAL_Database::run_schema( $sql );
	}

	/**
	 * Seeds the official department library.
	 *
	 * @return void
	 */
	public static function seed_official_library(): void {
		global $wpdb;

		$table_name = IADAL_Database::table( 'department_library' );

		if ( ! IADAL_Database::table_exists( $table_name ) ) {
			return;
		}

		$official_departments = array(
			array( 'name' => 'EBD', 'slug' => 'ebd', 'has_schedules' => 0, 'has_confirmations' => 0, 'has_swaps' => 0 ),
			array( 'name' => 'Midia', 'slug' => 'midia', 'has_schedules' => 1, 'has_confirmations' => 1, 'has_swaps' => 1 ),
			array( 'name' => 'Louvor', 'slug' => 'louvor', 'has_schedules' => 1, 'has_confirmations' => 1, 'has_swaps' => 1 ),
			array( 'name' => 'Obreiros', 'slug' => 'obreiros', 'has_schedules' => 1, 'has_confirmations' => 1, 'has_swaps' => 1 ),
			array( 'name' => 'Jovens', 'slug' => 'jovens', 'has_schedules' => 0, 'has_confirmations' => 0, 'has_swaps' => 0 ),
			array( 'name' => 'Adolescentes', 'slug' => 'adolescentes', 'has_schedules' => 0, 'has_confirmations' => 0, 'has_swaps' => 0 ),
			array( 'name' => 'Irmas', 'slug' => 'irmas', 'has_schedules' => 0, 'has_confirmations' => 0, 'has_swaps' => 0 ),
			array( 'name' => 'Homens', 'slug' => 'homens', 'has_schedules' => 0, 'has_confirmations' => 0, 'has_swaps' => 0 ),
			array( 'name' => 'Kids', 'slug' => 'kids', 'has_schedules' => 0, 'has_confirmations' => 0, 'has_swaps' => 0 ),
			array( 'name' => 'Eventos', 'slug' => 'eventos', 'has_schedules' => 0, 'has_confirmations' => 0, 'has_swaps' => 0 ),
		);

		foreach ( $official_departments as $department ) {
			$existing_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table_name} WHERE slug = %s LIMIT 1", $department['slug'] ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is generated internally.
			$now         = IADAL_Database::now();
			$data        = array(
				'name'              => $department['name'],
				'slug'              => $department['slug'],
				'type'              => 'oficial',
				'description'       => '',
				'has_chat'          => 1,
				'has_notices'       => 1,
				'has_files'         => 1,
				'has_birthdays'     => 1,
				'has_members'       => 1,
				'has_schedules'     => (int) $department['has_schedules'],
				'has_confirmations' => (int) $department['has_confirmations'],
				'has_swaps'         => (int) $department['has_swaps'],
				'has_statistics'    => 1,
				'status'            => 'ativo',
				'updated_at'        => $now,
			);

			if ( $existing_id ) {
				$wpdb->update(
					$table_name,
					$data,
					array( 'id' => (int) $existing_id ),
					array( '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%s', '%s' ),
					array( '%d' )
				);
				continue;
			}

			$data['created_at'] = $now;
			$data['created_by'] = get_current_user_id();
			$data['updated_by'] = get_current_user_id();

			$wpdb->insert(
				$table_name,
				$data,
				array( '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%s', '%s', '%s', '%d', '%d' )
			);
		}
	}
}
