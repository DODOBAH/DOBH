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
	 * Checks if a database table exists.
	 *
	 * @param string $table_name Full table name.
	 * @return bool
	 */
	public static function table_exists( string $table_name ): bool {
		global $wpdb;

		$sql = 'SHOW TABLES LIKE %s';

		return $table_name === $wpdb->get_var( $wpdb->prepare( $sql, $table_name ) );
	}

	/**
	 * Checks if all provided tables exist.
	 *
	 * @param array<int, string> $table_names Full table names.
	 * @return bool
	 */
	public static function tables_exist( array $table_names ): bool {
		foreach ( $table_names as $table_name ) {
			if ( ! self::table_exists( $table_name ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Checks if a table column exists.
	 *
	 * @param string $table_name Full table name.
	 * @param string $column_name Column name.
	 * @return bool
	 */
	public static function column_exists( string $table_name, string $column_name ): bool {
		global $wpdb;

		$sql = "SHOW COLUMNS FROM {$table_name} LIKE %s";

		return null !== $wpdb->get_var( $wpdb->prepare( $sql, $column_name ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is generated internally.
	}

	/**
	 * Starts a database transaction.
	 *
	 * @return void
	 */
	public static function begin_transaction(): void {
		global $wpdb;

		$wpdb->query( 'START TRANSACTION' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}

	/**
	 * Commits the current database transaction.
	 *
	 * @return void
	 */
	public static function commit(): void {
		global $wpdb;

		$wpdb->query( 'COMMIT' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}

	/**
	 * Rolls back the current database transaction.
	 *
	 * @return void
	 */
	public static function rollback(): void {
		global $wpdb;

		$wpdb->query( 'ROLLBACK' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
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
