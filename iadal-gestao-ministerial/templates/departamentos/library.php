<?php
/**
 * Department library admin screen.
 *
 * @package IADAL_Gestao_Ministerial
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap iadal-members-wrap iadal-departments-wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Biblioteca de Departamentos', 'iadal-gestao-ministerial' ); ?></h1>
	<a class="page-title-action" href="<?php echo esc_url( admin_url( 'admin.php?page=iadal-departments-custom' ) ); ?>">
		<?php esc_html_e( 'Criar Personalizado', 'iadal-gestao-ministerial' ); ?>
	</a>
	<a class="page-title-action" href="<?php echo esc_url( admin_url( 'admin.php?page=iadal-departments-activate' ) ); ?>">
		<?php esc_html_e( 'Ativar Departamento', 'iadal-gestao-ministerial' ); ?>
	</a>

	<?php include IADAL_GESTAO_DIR . 'templates/layouts/notices.php'; ?>

	<div class="iadal-card">
		<table class="widefat fixed striped iadal-members-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Nome', 'iadal-gestao-ministerial' ); ?></th>
					<th><?php esc_html_e( 'Tipo', 'iadal-gestao-ministerial' ); ?></th>
					<th><?php esc_html_e( 'Identificador', 'iadal-gestao-ministerial' ); ?></th>
					<th><?php esc_html_e( 'Funcionalidades', 'iadal-gestao-ministerial' ); ?></th>
					<th><?php esc_html_e( 'Status', 'iadal-gestao-ministerial' ); ?></th>
					<th><?php esc_html_e( 'Acoes', 'iadal-gestao-ministerial' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $library ) ) : ?>
					<tr><td colspan="6"><?php esc_html_e( 'Nenhum departamento na biblioteca.', 'iadal-gestao-ministerial' ); ?></td></tr>
				<?php endif; ?>

				<?php foreach ( $library as $item ) : ?>
					<?php
					$feature_summary = implode(
						', ',
						array_filter(
							array(
								! empty( $item['has_chat'] ) ? 'Chat' : '',
								! empty( $item['has_notices'] ) ? 'Avisos' : '',
								! empty( $item['has_files'] ) ? 'Arquivos' : '',
								! empty( $item['has_schedules'] ) ? 'Escalas' : '',
								! empty( $item['has_confirmations'] ) ? 'Confirmacao' : '',
								! empty( $item['has_swaps'] ) ? 'Trocas' : '',
							)
						)
					);
					?>
					<tr>
						<td data-label="<?php esc_attr_e( 'Nome', 'iadal-gestao-ministerial' ); ?>"><strong><?php echo esc_html( $item['name'] ); ?></strong></td>
						<td data-label="<?php esc_attr_e( 'Tipo', 'iadal-gestao-ministerial' ); ?>"><?php echo esc_html( $item['type'] ); ?></td>
						<td data-label="<?php esc_attr_e( 'Identificador', 'iadal-gestao-ministerial' ); ?>"><code><?php echo esc_html( $item['slug'] ); ?></code></td>
						<td data-label="<?php esc_attr_e( 'Funcionalidades', 'iadal-gestao-ministerial' ); ?>">
							<?php echo esc_html( $feature_summary ?: '-' ); ?>
						</td>
						<td data-label="<?php esc_attr_e( 'Status', 'iadal-gestao-ministerial' ); ?>">
							<span class="iadal-status iadal-status-<?php echo esc_attr( $item['status'] ); ?>"><?php echo esc_html( $item['status'] ); ?></span>
						</td>
						<td data-label="<?php esc_attr_e( 'Acoes', 'iadal-gestao-ministerial' ); ?>">
							<?php if ( 'personalizado' === $item['type'] ) : ?>
								<a class="button button-small" href="<?php echo esc_url( add_query_arg( array( 'page' => 'iadal-departments-library-edit', 'library_id' => (int) $item['id'] ), admin_url( 'admin.php' ) ) ); ?>">
									<?php esc_html_e( 'Editar', 'iadal-gestao-ministerial' ); ?>
								</a>
								<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="iadal-inline-form iadal-delete-form">
									<input type="hidden" name="action" value="iadal_departments_library_delete" />
									<input type="hidden" name="library_id" value="<?php echo esc_attr( (string) $item['id'] ); ?>" />
									<?php wp_nonce_field( 'iadal_departments_library_delete_' . (int) $item['id'], 'iadal_departments_nonce' ); ?>
									<button type="submit" class="button button-small button-link-delete"><?php esc_html_e( 'Inativar', 'iadal-gestao-ministerial' ); ?></button>
								</form>
							<?php else : ?>
								<span class="description"><?php esc_html_e( 'Oficial', 'iadal-gestao-ministerial' ); ?></span>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>
