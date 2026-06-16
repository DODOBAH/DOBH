<?php
/**
 * Edit activated department.
 *
 * @package IADAL_Gestao_Ministerial
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$feature_labels = array(
	'has_chat'          => __( 'Chat', 'iadal-gestao-ministerial' ),
	'has_notices'       => __( 'Avisos', 'iadal-gestao-ministerial' ),
	'has_files'         => __( 'Arquivos', 'iadal-gestao-ministerial' ),
	'has_birthdays'     => __( 'Aniversariantes', 'iadal-gestao-ministerial' ),
	'has_members'       => __( 'Componentes', 'iadal-gestao-ministerial' ),
	'has_schedules'     => __( 'Escalas', 'iadal-gestao-ministerial' ),
	'has_confirmations' => __( 'Confirmacao', 'iadal-gestao-ministerial' ),
	'has_swaps'         => __( 'Trocas', 'iadal-gestao-ministerial' ),
	'has_statistics'    => __( 'Estatisticas', 'iadal-gestao-ministerial' ),
);
?>

<div class="wrap iadal-members-wrap iadal-departments-wrap">
	<h1>
		<?php
		printf(
			/* translators: %s: department name. */
			esc_html__( 'Editar Departamento: %s', 'iadal-gestao-ministerial' ),
			esc_html( $department['name'] ?? '' )
		);
		?>
	</h1>

	<?php include IADAL_GESTAO_DIR . 'templates/layouts/notices.php'; ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="iadal-member-form">
		<input type="hidden" name="action" value="iadal_departments_update" />
		<input type="hidden" name="department_id" value="<?php echo esc_attr( (string) $department['id'] ); ?>" />
		<?php wp_nonce_field( 'iadal_departments_update_' . (int) $department['id'], 'iadal_departments_nonce' ); ?>

		<div class="iadal-form-grid">
			<section class="iadal-card">
				<h2><?php esc_html_e( 'Dados principais', 'iadal-gestao-ministerial' ); ?></h2>

				<label class="iadal-field">
					<span><?php esc_html_e( 'Nome', 'iadal-gestao-ministerial' ); ?> <strong>*</strong></span>
					<input type="text" name="name" required maxlength="190" value="<?php echo esc_attr( $department['name'] ?? '' ); ?>" />
				</label>

				<label class="iadal-field">
					<span><?php esc_html_e( 'Status', 'iadal-gestao-ministerial' ); ?></span>
					<select name="status">
						<?php foreach ( $status_options as $value => $label ) : ?>
							<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $department['status'] ?? '', $value ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>

				<label class="iadal-field">
					<span><?php esc_html_e( 'Nome do lider', 'iadal-gestao-ministerial' ); ?></span>
					<input type="text" name="leader_name" maxlength="190" value="<?php echo esc_attr( $department['leader_name'] ?? '' ); ?>" />
				</label>

				<label class="iadal-field">
					<span><?php esc_html_e( 'Telefone do lider', 'iadal-gestao-ministerial' ); ?></span>
					<input type="text" name="leader_phone" maxlength="30" value="<?php echo esc_attr( $department['leader_phone'] ?? '' ); ?>" />
				</label>
			</section>

			<section class="iadal-card">
				<h2><?php esc_html_e( 'Funcionalidades', 'iadal-gestao-ministerial' ); ?></h2>
				<?php foreach ( $feature_labels as $field => $label ) : ?>
					<label class="iadal-checkbox">
						<input type="checkbox" name="<?php echo esc_attr( $field ); ?>" value="1" <?php checked( (int) ( $department[ $field ] ?? 0 ), 1 ); ?> />
						<?php echo esc_html( $label ); ?>
					</label>
				<?php endforeach; ?>
			</section>
		</div>

		<div class="iadal-form-actions">
			<button type="submit" class="button button-primary button-large"><?php esc_html_e( 'Atualizar departamento', 'iadal-gestao-ministerial' ); ?></button>
			<a class="button button-large" href="<?php echo esc_url( admin_url( 'admin.php?page=iadal-departments' ) ); ?>"><?php esc_html_e( 'Voltar', 'iadal-gestao-ministerial' ); ?></a>
		</div>
	</form>
</div>
