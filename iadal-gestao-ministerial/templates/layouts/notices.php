<?php
/**
 * Admin notices partial.
 *
 * @package IADAL_Gestao_Ministerial
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$iadal_notice = filter_input( INPUT_GET, 'iadal_notice', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
$iadal_error  = filter_input( INPUT_GET, 'iadal_error', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
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
