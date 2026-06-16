<?php
/**
 * Audit table schema.
 *
 * @package IADAL_Gestao_Ministerial
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates and updates the audit schema.
 */
class IADAL_Audit_Schema {

	/**
	 * Creates the audit logs table.
	 *
	 * @return array
	 */
	public static function create(): array {
		global $wpdb;

		$table_name      = IADAL_Database::table( 'audit_logs' );
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			user_id bigint(20) unsigned DEFAULT NULL,
			user_role varchar(120) DEFAULT NULL,
			church_id bigint(20) unsigned DEFAULT NULL,
			department_id bigint(20) unsigned DEFAULT NULL,
			module varchar(80) NOT NULL,
			action varchar(120) NOT NULL,
			entity_type varchar(80) NOT NULL,
			entity_id bigint(20) unsigned DEFAULT NULL,
			old_data longtext DEFAULT NULL,
			new_data longtext DEFAULT NULL,
			ip_address varchar(45) DEFAULT NULL,
			user_agent text DEFAULT NULL,
			created_at datetime NOT NULL,
			PRIMARY KEY  (id),
			KEY user_id (user_id),
			KEY church_id (church_id),
			KEY module (module),
			KEY action (action),
			KEY entity_type (entity_type),
			KEY entity_id (entity_id),
			KEY created_at (created_at)
		) {$charset_collate};";

		return IADAL_Database::run_schema( $sql );
	}
}
