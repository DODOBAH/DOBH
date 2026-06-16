<?php
/**
 * Audit logging service.
 *
 * @package IADAL_Gestao_Ministerial
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Records important system actions.
 */
class IADAL_Audit {

	/**
	 * Writes an audit record.
	 *
	 * @param string                    $module Module name.
	 * @param string                    $action Action name.
	 * @param string                    $entity_type Entity type.
	 * @param int|null                  $entity_id Entity ID.
	 * @param array<string, mixed>|null $old_data Previous data.
	 * @param array<string, mixed>|null $new_data New data.
	 * @param int|null                  $church_id Church ID.
	 * @return void
	 */
	public static function log( string $module, string $action, string $entity_type, ?int $entity_id = null, ?array $old_data = null, ?array $new_data = null, ?int $church_id = null ): void {
		global $wpdb;

		$table_name = IADAL_Database::table( 'audit_logs' );

		if ( ! IADAL_Database::table_exists( $table_name ) ) {
			return;
		}

		$current_user = wp_get_current_user();
		$user_role    = ! empty( $current_user->roles ) ? implode( ',', array_map( 'sanitize_key', $current_user->roles ) ) : '';
		$ip_address   = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		$user_agent   = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_textarea_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';

		$wpdb->insert(
			$table_name,
			array(
				'user_id'     => get_current_user_id(),
				'user_role'   => $user_role,
				'church_id'   => $church_id,
				'module'      => sanitize_key( $module ),
				'action'      => sanitize_key( $action ),
				'entity_type' => sanitize_key( $entity_type ),
				'entity_id'   => $entity_id,
				'old_data'    => null === $old_data ? null : wp_json_encode( $old_data ),
				'new_data'    => null === $new_data ? null : wp_json_encode( $new_data ),
				'ip_address'  => $ip_address,
				'user_agent'  => $user_agent,
				'created_at'  => IADAL_Database::now(),
			),
			array( '%d', '%s', '%d', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s' )
		);
	}
}
