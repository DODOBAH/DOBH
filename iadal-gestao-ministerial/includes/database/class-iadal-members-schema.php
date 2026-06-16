<?php
/**
 * Members table schema.
 *
 * @package IADAL_Gestao_Ministerial
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates and updates the members schema.
 */
class IADAL_Members_Schema {

	/**
	 * Creates the members table.
	 *
	 * @return array
	 */
	public static function create(): array {
		global $wpdb;

		$table_name      = IADAL_Database::table( 'members' );
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			photo_attachment_id bigint(20) unsigned DEFAULT NULL,
			full_name varchar(190) NOT NULL,
			cpf varchar(11) NOT NULL,
			phone varchar(30) DEFAULT NULL,
			email varchar(190) DEFAULT NULL,
			birth_date date DEFAULT NULL,
			birth_month tinyint(2) unsigned DEFAULT NULL,
			gender varchar(20) DEFAULT NULL,
			zip_code varchar(20) DEFAULT NULL,
			address varchar(255) DEFAULT NULL,
			address_number varchar(30) DEFAULT NULL,
			address_complement varchar(120) DEFAULT NULL,
			district varchar(120) DEFAULT NULL,
			city varchar(120) DEFAULT NULL,
			state varchar(2) DEFAULT NULL,
			marital_status varchar(40) DEFAULT NULL,
			spouse_name varchar(190) DEFAULT NULL,
			entry_type varchar(30) NOT NULL DEFAULT 'local',
			status varchar(20) NOT NULL DEFAULT 'ativo',
			notes text DEFAULT NULL,
			created_by bigint(20) unsigned DEFAULT NULL,
			updated_by bigint(20) unsigned DEFAULT NULL,
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			deleted_at datetime DEFAULT NULL,
			deleted_by bigint(20) unsigned DEFAULT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY cpf (cpf),
			KEY full_name (full_name),
			KEY status (status),
			KEY entry_type (entry_type),
			KEY birth_date (birth_date),
			KEY birth_month (birth_month),
			KEY deleted_at (deleted_at)
		) {$charset_collate};";

		$documents_table = IADAL_Database::table( 'member_documents' );

		$sql .= "\nCREATE TABLE {$documents_table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			member_id bigint(20) unsigned NOT NULL,
			document_type varchar(40) NOT NULL,
			title varchar(190) NOT NULL,
			file_name varchar(190) NOT NULL,
			file_path text NOT NULL,
			mime_type varchar(120) NOT NULL,
			file_size bigint(20) unsigned NOT NULL DEFAULT 0,
			notes text DEFAULT NULL,
			uploaded_by bigint(20) unsigned DEFAULT NULL,
			created_at datetime NOT NULL,
			deleted_at datetime DEFAULT NULL,
			deleted_by bigint(20) unsigned DEFAULT NULL,
			PRIMARY KEY  (id),
			KEY member_id (member_id),
			KEY document_type (document_type),
			KEY deleted_at (deleted_at)
		) {$charset_collate};";

		return IADAL_Database::run_schema( $sql );
	}
}
