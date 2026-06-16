<?php
/**
 * Plugin uninstall handler.
 *
 * Data is preserved by default because this plugin stores sensitive church
 * records. Tables are removed only when the explicit option is enabled.
 *
 * @package IADAL_Gestao_Ministerial
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$delete_data = get_option( 'iadal_delete_data_on_uninstall', 'no' );

if ( 'yes' !== $delete_data ) {
	return;
}

global $wpdb;

$tables = array(
	$wpdb->prefix . 'iadal_audit_logs',
	$wpdb->prefix . 'iadal_department_users',
	$wpdb->prefix . 'iadal_departments',
	$wpdb->prefix . 'iadal_department_library',
	$wpdb->prefix . 'iadal_member_documents',
	$wpdb->prefix . 'iadal_members',
	$wpdb->prefix . 'iadal_users',
	$wpdb->prefix . 'iadal_church_modules',
	$wpdb->prefix . 'iadal_churches',
);

foreach ( $tables as $table ) {
	$wpdb->query( "DROP TABLE IF EXISTS {$table}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
}

delete_option( 'iadal_gestao_version' );
delete_option( 'iadal_delete_data_on_uninstall' );
delete_option( 'iadal_gestao_upgrade_error' );
delete_option( 'iadal_members_capabilities_version' );
delete_option( 'iadal_congregations_capabilities_version' );
delete_option( 'iadal_departments_capabilities_version' );

$protected_roots = array(
	trailingslashit( dirname( untrailingslashit( ABSPATH ) ) ) . 'iadal-protected/member-documents',
	trailingslashit( WP_CONTENT_DIR ) . 'iadal-protected/member-documents',
);

try {
	foreach ( $protected_roots as $protected_root ) {
		if ( ! is_dir( $protected_root ) ) {
			continue;
		}

		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $protected_root, FilesystemIterator::SKIP_DOTS ),
			RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach ( $iterator as $item ) {
			if ( $item->isDir() ) {
				rmdir( $item->getPathname() ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir
			} else {
				wp_delete_file( $item->getPathname() );
			}
		}

		rmdir( $protected_root ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir
	}
} catch ( Exception $exception ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
	// Preserve files if permissions prevent safe cleanup.
}
