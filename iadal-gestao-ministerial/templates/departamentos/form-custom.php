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
$is_edit      = ! empty( $is_edit );
$library_item = isset( $library_item ) && is_array( $library_item ) ? $library_item : array();
?>

<div class="wrap iadal-members-wrap iadal-departments-wrap">
	<h1><?php echo $is_edit ? esc_html__( 'Editar Departamento Personalizado', 'iadal-gestao-ministerial' ) : esc_html__( 'Criar Departamento Personalizado', 'iadal-gestao-ministerial' ); ?></h1>

	<?php include IADAL_GESTAO_DIR . 'templates/layouts/notices.php'; ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="iadal-member-form">
		<input type="hidden" name="action" value="<?php echo esc_attr( $is_edit ? 'iadal_departments_library_update' : 'iadal_departments_library_create' ); ?>" />
		<?php if ( $is_edit ) : ?>
			<input type="hidden" name="library_id" value="<?php echo esc_attr( (string) $library_item['id'] ); ?>" />
			<?php wp_nonce_field( 'iadal_departments_library_update_' . (int) $library_item['id'], 'iadal_departments_nonce' ); ?>
		<?php else : ?>
			<?php wp_nonce_field( 'iadal_departments_library_create', 'iadal_departments_nonce' ); ?>
		<?php endif; ?>

		<div class="iadal-form-grid">
			<section class="iadal-card">
				<h2><?php esc_html_e( 'Dados do departamento', 'iadal-gestao-ministerial' ); ?></h2>

				<label class="iadal-field">
					<span><?php esc_html_e( 'Nome', 'iadal-gestao-ministerial' ); ?> <strong>*</strong></span>
					<input type="text" name="name" required maxlength="190" value="<?php echo esc_attr( $library_item['name'] ?? '' ); ?>" />
				</label>

				<label class="iadal-field">
					<span><?php esc_html_e( 'Identificador', 'iadal-gestao-ministerial' ); ?></span>
					<input type="text" name="slug" maxlength="120" value="<?php echo esc_attr( $library_item['slug'] ?? '' ); ?>" />
					<small><?php esc_html_e( 'Se ficar vazio, sera gerado pelo nome.', 'iadal-gestao-ministerial' ); ?></small>
				</label>

				<label class="iadal-field iadal-field-wide">
					<span><?php esc_html_e( 'Descricao', 'iadal-gestao-ministerial' ); ?></span>
					<textarea name="description" rows="4"><?php echo esc_textarea( $library_item['description'] ?? '' ); ?></textarea>
				</label>
			</section>

			<section class="iadal-card">
				<h2><?php esc_html_e( 'Funcionalidades', 'iadal-gestao-ministerial' ); ?></h2>
				<?php foreach ( $feature_labels as $field => $label ) : ?>
					<label class="iadal-checkbox">
						<input type="checkbox" name="<?php echo esc_attr( $field ); ?>" value="1" <?php checked( $is_edit ? (int) ( $library_item[ $field ] ?? 0 ) : 1, 1 ); ?> />
						<?php echo esc_html( $label ); ?>
					</label>
				<?php endforeach; ?>
			</section>
		</div>

		<div class="iadal-form-actions">
			<button type="submit" class="button button-primary button-large"><?php echo $is_edit ? esc_html__( 'Atualizar personalizado', 'iadal-gestao-ministerial' ) : esc_html__( 'Salvar personalizado', 'iadal-gestao-ministerial' ); ?></button>
			<a class="button button-large" href="<?php echo esc_url( admin_url( 'admin.php?page=iadal-departments-library' ) ); ?>"><?php esc_html_e( 'Cancelar', 'iadal-gestao-ministerial' ); ?></a>
		</div>
	</form>
</div>
