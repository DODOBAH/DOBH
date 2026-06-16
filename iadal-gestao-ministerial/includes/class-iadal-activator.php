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
		self::add_capabilities();

		update_option( 'iadal_gestao_version', IADAL_GESTAO_VERSION );
	}

	/**
	 * Adds the first module capabilities to administrator users.
	 *
	 * @return void
	 */
	private static function add_capabilities(): void {
		$administrator = get_role( 'administrator' );

		if ( ! $administrator ) {
			return;
		}

		$capabilities = array(
			'iadal_view_members',
			'iadal_create_members',
			'iadal_edit_members',
			'iadal_delete_members',
			'iadal_manage_members',
		);

		foreach ( $capabilities as $capability ) {
			$administrator->add_cap( $capability );
		}
	}
}
