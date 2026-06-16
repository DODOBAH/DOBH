<?php
/**
 * Birthday report admin screen.
 *
 * @package IADAL_Gestao_Ministerial
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap iadal-members-wrap">
	<h1><?php esc_html_e( 'Relatorio de Aniversariantes', 'iadal-gestao-ministerial' ); ?></h1>

	<?php include IADAL_GESTAO_DIR . 'templates/layouts/notices.php'; ?>

	<div class="iadal-card iadal-print-hidden">
		<form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" class="iadal-filters">
			<input type="hidden" name="page" value="iadal-members-birthdays" />

			<label>
				<span><?php esc_html_e( 'Mes', 'iadal-gestao-ministerial' ); ?></span>
				<select name="birth_month">
					<?php for ( $month_number = 1; $month_number <= 12; $month_number++ ) : ?>
						<option value="<?php echo esc_attr( (string) $month_number ); ?>" <?php selected( $month, $month_number ); ?>>
							<?php echo esc_html( date_i18n( 'F', mktime( 0, 0, 0, $month_number, 1 ) ) ); ?>
						</option>
					<?php endfor; ?>
				</select>
			</label>

			<div class="iadal-filter-actions">
				<button type="submit" class="button button-primary">
					<?php esc_html_e( 'Gerar relatorio', 'iadal-gestao-ministerial' ); ?>
				</button>
				<button type="button" class="button" data-iadal-print>
					<?php esc_html_e( 'Imprimir', 'iadal-gestao-ministerial' ); ?>
				</button>
				<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=iadal-members' ) ); ?>">
					<?php esc_html_e( 'Voltar', 'iadal-gestao-ministerial' ); ?>
				</a>
			</div>
		</form>
	</div>

	<div class="iadal-card iadal-report">
		<div class="iadal-report-header">
			<h2>
				<?php
				printf(
					/* translators: %s: month name. */
					esc_html__( 'Aniversariantes de %s', 'iadal-gestao-ministerial' ),
					esc_html( date_i18n( 'F', mktime( 0, 0, 0, $month, 1 ) ) )
				);
				?>
			</h2>
			<p><?php esc_html_e( 'Sistema Integrado IADAL Alto Lage', 'iadal-gestao-ministerial' ); ?></p>
		</div>

		<table class="widefat fixed striped iadal-members-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Nome', 'iadal-gestao-ministerial' ); ?></th>
					<th><?php esc_html_e( 'Data de nascimento', 'iadal-gestao-ministerial' ); ?></th>
					<th><?php esc_html_e( 'Telefone', 'iadal-gestao-ministerial' ); ?></th>
					<th><?php esc_html_e( 'Status', 'iadal-gestao-ministerial' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $members ) ) : ?>
					<tr>
						<td colspan="4"><?php esc_html_e( 'Nenhum aniversariante encontrado para este mes.', 'iadal-gestao-ministerial' ); ?></td>
					</tr>
				<?php endif; ?>

				<?php foreach ( $members as $member ) : ?>
					<tr>
						<td data-label="<?php esc_attr_e( 'Nome', 'iadal-gestao-ministerial' ); ?>">
							<?php echo esc_html( $member['full_name'] ); ?>
						</td>
						<td data-label="<?php esc_attr_e( 'Data de nascimento', 'iadal-gestao-ministerial' ); ?>">
							<?php echo esc_html( IADAL_Members_Controller::format_date( $member['birth_date'] ) ); ?>
						</td>
						<td data-label="<?php esc_attr_e( 'Telefone', 'iadal-gestao-ministerial' ); ?>">
							<?php echo esc_html( $member['phone'] ?: '-' ); ?>
						</td>
						<td data-label="<?php esc_attr_e( 'Status', 'iadal-gestao-ministerial' ); ?>">
							<span class="iadal-status iadal-status-<?php echo esc_attr( $member['status'] ); ?>">
								<?php echo esc_html( IADAL_Members_Controller::status_options()[ $member['status'] ] ?? $member['status'] ); ?>
							</span>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>
