<?php
/**
 * Activate department admin screen.
 *
 * @package IADAL_Gestao_Ministerial
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap iadal-members-wrap iadal-departments-wrap">
	<h1><?php esc_html_e( 'Ativar Departamento', 'iadal-gestao-ministerial' ); ?></h1>

	<?php include IADAL_GESTAO_DIR . 'templates/layouts/notices.php'; ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="iadal-member-form">
		<input type="hidden" name="action" value="iadal_departments_activate" />
		<?php wp_nonce_field( 'iadal_departments_activate', 'iadal_departments_nonce' ); ?>

		<div class="iadal-form-grid">
			<section class="iadal-card">
				<h2><?php esc_html_e( 'Departamento e congregacao', 'iadal-gestao-ministerial' ); ?></h2>

				<label class="iadal-field">
					<span><?php esc_html_e( 'Congregacao', 'iadal-gestao-ministerial' ); ?> <strong>*</strong></span>
					<select name="church_id" required>
						<option value="0"><?php esc_html_e( 'Selecione', 'iadal-gestao-ministerial' ); ?></option>
						<?php foreach ( $congregations as $congregation ) : ?>
							<option value="<?php echo esc_attr( (string) $congregation['id'] ); ?>"><?php echo esc_html( $congregation['name'] ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>

				<label class="iadal-field">
					<span><?php esc_html_e( 'Departamento oficial/personalizado', 'iadal-gestao-ministerial' ); ?> <strong>*</strong></span>
					<select name="library_id" required>
						<option value="0"><?php esc_html_e( 'Selecione', 'iadal-gestao-ministerial' ); ?></option>
						<?php foreach ( $library as $item ) : ?>
							<option value="<?php echo esc_attr( (string) $item['id'] ); ?>">
								<?php echo esc_html( $item['name'] . ' - ' . $item['type'] ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</label>
			</section>

			<section class="iadal-card">
				<h2><?php esc_html_e( 'Lideranca', 'iadal-gestao-ministerial' ); ?></h2>

				<label class="iadal-field">
					<span><?php esc_html_e( 'Nome do lider', 'iadal-gestao-ministerial' ); ?> <strong>*</strong></span>
					<input type="text" name="leader_name" required maxlength="190" />
				</label>

				<label class="iadal-field">
					<span><?php esc_html_e( 'Telefone do lider', 'iadal-gestao-ministerial' ); ?></span>
					<input type="text" name="leader_phone" maxlength="30" />
				</label>
			</section>
		</div>

		<div class="iadal-form-actions">
			<button type="submit" class="button button-primary button-large"><?php esc_html_e( 'Ativar e gerar credenciais', 'iadal-gestao-ministerial' ); ?></button>
			<a class="button button-large" href="<?php echo esc_url( admin_url( 'admin.php?page=iadal-departments' ) ); ?>"><?php esc_html_e( 'Cancelar', 'iadal-gestao-ministerial' ); ?></a>
		</div>
	</form>
</div>
