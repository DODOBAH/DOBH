<?php
/**
 * Admin notices partial.
 *
 * @package IADAL_Gestao_Ministerial
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$iadal_notice      = '';
$iadal_error       = '';
$iadal_message_key = filter_input( INPUT_GET, 'iadal_message', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

if ( $iadal_message_key ) {
	$iadal_message = get_transient( 'iadal_message_' . $iadal_message_key );

	if ( is_array( $iadal_message ) && (int) ( $iadal_message['current_user'] ?? 0 ) === get_current_user_id() ) {
		$iadal_notice = isset( $iadal_message['notice'] ) ? (string) $iadal_message['notice'] : '';
		$iadal_error  = isset( $iadal_message['error'] ) ? (string) $iadal_message['error'] : '';
		delete_transient( 'iadal_message_' . $iadal_message_key );
	}
}
?>

<?php if ( $iadal_notice ) : ?>
	<div class="notice notice-success is-dismissible">
		<p><?php echo esc_html( $iadal_notice ); ?></p>
	</div>
<?php endif; ?>

<?php if ( $iadal_error ) : ?>
	<div class="notice notice-error is-dismissible">
		<p><?php echo esc_html( $iadal_error ); ?></p>
	</div>
<?php endif; ?>
