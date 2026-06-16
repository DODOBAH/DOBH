<?php
/**
 * Plugin activation routines.
 *
 * @package IADAL_Gestao_Ministerial
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Runs activation tasks.
 */
class IADAL_Activator {

	/**
	 * Creates the database structure required by the plugin.
	 *
	 * @return void
	 */
	public static function activate(): void {
		require_once IADAL_GESTAO_DIR . 'includes/database/class-iadal-database.php';
		require_once IADAL_GESTAO_DIR . 'includes/database/class-iadal-members-schema.php';

		IADAL_Members_Schema::create();

		update_option( 'iadal_gestao_version', IADAL_GESTAO_VERSION );
	}
}
