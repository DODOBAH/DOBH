<?php
/**
 * Congregations table schema.
 *
 * @package IADAL_Gestao_Ministerial
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates and updates the congregations schema.
 */
class IADAL_Congregations_Schema {

	/**
	 * Creates the congregations and internal users tables.
	 *
	 * @return array
	 */
	public static function create(): array {
		global $wpdb;

		$churches_table  = IADAL_Database::table( 'churches' );
		$users_table     = IADAL_Database::table( 'users' );
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$churches_table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			name varchar(190) NOT NULL,
			type varchar(30) NOT NULL DEFAULT 'congregacao',
			pastor_user_id bigint(20) unsigned DEFAULT NULL,
			secretary_user_id bigint(20) unsigned DEFAULT NULL,
			pastor_name varchar(190) DEFAULT NULL,
			pastor_phone varchar(30) DEFAULT NULL,
			secretary_name varchar(190) DEFAULT NULL,
			secretary_phone varchar(30) DEFAULT NULL,
			zip_code varchar(20) DEFAULT NULL,
			address varchar(255) DEFAULT NULL,
			address_number varchar(30) DEFAULT NULL,
			address_complement varchar(120) DEFAULT NULL,
			district varchar(120) DEFAULT NULL,
			city varchar(120) DEFAULT NULL,
			state varchar(2) DEFAULT NULL,
			notes text DEFAULT NULL,
			status varchar(20) NOT NULL DEFAULT 'ativo',
			cleaning_requests_blocked tinyint(1) unsigned NOT NULL DEFAULT 0,
			created_by bigint(20) unsigned DEFAULT NULL,
			updated_by bigint(20) unsigned DEFAULT NULL,
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			deleted_at datetime DEFAULT NULL,
			deleted_by bigint(20) unsigned DEFAULT NULL,
			PRIMARY KEY  (id),
			KEY name (name),
			KEY type (type),
			KEY status (status),
			KEY pastor_user_id (pastor_user_id),
			KEY secretary_user_id (secretary_user_id),
			KEY deleted_at (deleted_at)
		) {$charset_collate};";

		$sql .= "\nCREATE TABLE {$users_table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			name varchar(190) NOT NULL,
			login varchar(120) NOT NULL,
			password_hash varchar(255) NOT NULL,
			initial_password_hash varchar(255) DEFAULT NULL,
			role varchar(60) NOT NULL,
			church_id bigint(20) unsigned DEFAULT NULL,
			department_id bigint(20) unsigned DEFAULT NULL,
			member_id bigint(20) unsigned DEFAULT NULL,
			email varchar(190) DEFAULT NULL,
			phone varchar(30) DEFAULT NULL,
			status varchar(20) NOT NULL DEFAULT 'ativo',
			must_change_password tinyint(1) unsigned NOT NULL DEFAULT 1,
			failed_login_attempts int(10) unsigned NOT NULL DEFAULT 0,
			locked_until datetime DEFAULT NULL,
			last_login_at datetime DEFAULT NULL,
			last_login_ip varchar(45) DEFAULT NULL,
			created_by bigint(20) unsigned DEFAULT NULL,
			updated_by bigint(20) unsigned DEFAULT NULL,
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			deleted_at datetime DEFAULT NULL,
			deleted_by bigint(20) unsigned DEFAULT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY login (login),
			KEY role (role),
			KEY church_id (church_id),
			KEY status (status),
			KEY deleted_at (deleted_at)
		) {$charset_collate};";

		return IADAL_Database::run_schema( $sql );
	}
}
