<?php
/**
 * Database helpers for the IADAL plugin.
 *
 * @package IADAL_Gestao_Ministerial
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Centralizes database helpers.
 */
class IADAL_Database {

	/**
	 * Returns a plugin table name using the active WordPress prefix.
	 *
	 * @param string $name Logical table name without the iadal prefix.
	 * @return string
	 */
	public static function table( string $name ): string {
		global $wpdb;

		$safe_name = preg_replace( '/[^a-z0-9_]/', '', strtolower( $name ) );

		return $wpdb->prefix . 'iadal_' . $safe_name;
	}

	/**
	 * Runs dbDelta for one or more SQL create statements.
	 *
	 * @param string $sql SQL statement.
	 * @return array
	 */
	public static function run_schema( string $sql ): array {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		return dbDelta( $sql );
	}

	/**
	 * Returns the current MySQL date/time in WordPress time.
	 *
	 * @return string
	 */
	public static function now(): string {
		return current_time( 'mysql' );
	}
}
