<?php
/**
 * Custom department form.
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
	<h1><?php esc_html_e( 'Criar Departamento Personalizado', 'iadal-gestao-ministerial' ); ?></h1>

	<?php include IADAL_GESTAO_DIR . 'templates/layouts/notices.php'; ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="iadal-member-form">
		<input type="hidden" name="action" value="iadal_departments_library_create" />
		<?php wp_nonce_field( 'iadal_departments_library_create', 'iadal_departments_nonce' ); ?>

		<div class="iadal-form-grid">
			<section class="iadal-card">
				<h2><?php esc_html_e( 'Dados do departamento', 'iadal-gestao-ministerial' ); ?></h2>

				<label class="iadal-field">
					<span><?php esc_html_e( 'Nome', 'iadal-gestao-ministerial' ); ?> <strong>*</strong></span>
					<input type="text" name="name" required maxlength="190" />
				</label>

				<label class="iadal-field">
					<span><?php esc_html_e( 'Identificador', 'iadal-gestao-ministerial' ); ?></span>
					<input type="text" name="slug" maxlength="120" />
					<small><?php esc_html_e( 'Se ficar vazio, sera gerado pelo nome.', 'iadal-gestao-ministerial' ); ?></small>
				</label>

				<label class="iadal-field iadal-field-wide">
					<span><?php esc_html_e( 'Descricao', 'iadal-gestao-ministerial' ); ?></span>
					<textarea name="description" rows="4"></textarea>
				</label>
			</section>

			<section class="iadal-card">
				<h2><?php esc_html_e( 'Funcionalidades', 'iadal-gestao-ministerial' ); ?></h2>
				<?php foreach ( $feature_labels as $field => $label ) : ?>
					<label class="iadal-checkbox">
						<input type="checkbox" name="<?php echo esc_attr( $field ); ?>" value="1" checked />
						<?php echo esc_html( $label ); ?>
					</label>
				<?php endforeach; ?>
			</section>
		</div>

		<div class="iadal-form-actions">
			<button type="submit" class="button button-primary button-large"><?php esc_html_e( 'Salvar personalizado', 'iadal-gestao-ministerial' ); ?></button>
			<a class="button button-large" href="<?php echo esc_url( admin_url( 'admin.php?page=iadal-departments-library' ) ); ?>"><?php esc_html_e( 'Cancelar', 'iadal-gestao-ministerial' ); ?></a>
		</div>
	</form>
</div>
