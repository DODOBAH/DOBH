<?php
/**
 * Departments list admin screen.
 *
 * @package IADAL_Gestao_Ministerial
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$total_pages = max( 1, (int) ceil( $total / $per_page ) );
$base_url    = admin_url( 'admin.php?page=iadal-departments' );
?>

<div class="wrap iadal-members-wrap iadal-departments-wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Departamentos Ativados', 'iadal-gestao-ministerial' ); ?></h1>
	<a class="page-title-action" href="<?php echo esc_url( admin_url( 'admin.php?page=iadal-departments-activate' ) ); ?>">
		<?php esc_html_e( 'Ativar Departamento', 'iadal-gestao-ministerial' ); ?>
	</a>
	<a class="page-title-action" href="<?php echo esc_url( admin_url( 'admin.php?page=iadal-departments-library' ) ); ?>">
		<?php esc_html_e( 'Biblioteca', 'iadal-gestao-ministerial' ); ?>
	</a>

	<?php include IADAL_GESTAO_DIR . 'templates/layouts/notices.php'; ?>

	<div class="iadal-card">
		<form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" class="iadal-filters">
			<input type="hidden" name="page" value="iadal-departments" />

			<label>
				<span><?php esc_html_e( 'Buscar', 'iadal-gestao-ministerial' ); ?></span>
				<input type="search" name="s" value="<?php echo esc_attr( $filters['search'] ); ?>" />
			</label>

			<label>
				<span><?php esc_html_e( 'Congregacao', 'iadal-gestao-ministerial' ); ?></span>
				<select name="church_id">
					<option value="0"><?php esc_html_e( 'Todas', 'iadal-gestao-ministerial' ); ?></option>
					<?php foreach ( $congregations as $congregation ) : ?>
						<option value="<?php echo esc_attr( (string) $congregation['id'] ); ?>" <?php selected( (int) $filters['church_id'], (int) $congregation['id'] ); ?>>
							<?php echo esc_html( $congregation['name'] ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</label>

			<label>
				<span><?php esc_html_e( 'Status', 'iadal-gestao-ministerial' ); ?></span>
				<select name="status">
					<option value=""><?php esc_html_e( 'Todos', 'iadal-gestao-ministerial' ); ?></option>
					<?php foreach ( $status_options as $value => $label ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $filters['status'], $value ); ?>>
							<?php echo esc_html( $label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</label>

			<div class="iadal-filter-actions">
				<button type="submit" class="button button-primary"><?php esc_html_e( 'Filtrar', 'iadal-gestao-ministerial' ); ?></button>
				<a class="button" href="<?php echo esc_url( $base_url ); ?>"><?php esc_html_e( 'Limpar', 'iadal-gestao-ministerial' ); ?></a>
			</div>
		</form>
	</div>

	<div class="iadal-card">
		<div class="iadal-table-header">
			<strong>
				<?php
				printf(
					/* translators: %d: total departments. */
					esc_html__( '%d departamento(s) encontrado(s)', 'iadal-gestao-ministerial' ),
					(int) $total
				);
				?>
			</strong>
		</div>

		<table class="widefat fixed striped iadal-members-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Departamento', 'iadal-gestao-ministerial' ); ?></th>
					<th><?php esc_html_e( 'Congregacao', 'iadal-gestao-ministerial' ); ?></th>
					<th><?php esc_html_e( 'Lider', 'iadal-gestao-ministerial' ); ?></th>
					<th><?php esc_html_e( 'Funcionalidades', 'iadal-gestao-ministerial' ); ?></th>
					<th><?php esc_html_e( 'Status', 'iadal-gestao-ministerial' ); ?></th>
					<th><?php esc_html_e( 'Acoes', 'iadal-gestao-ministerial' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $departments ) ) : ?>
					<tr><td colspan="6"><?php esc_html_e( 'Nenhum departamento ativado.', 'iadal-gestao-ministerial' ); ?></td></tr>
				<?php endif; ?>

				<?php foreach ( $departments as $department ) : ?>
					<?php
					$feature_summary = implode(
						', ',
						array_filter(
							array(
								! empty( $department['has_chat'] ) ? 'Chat' : '',
								! empty( $department['has_notices'] ) ? 'Avisos' : '',
								! empty( $department['has_files'] ) ? 'Arquivos' : '',
								! empty( $department['has_schedules'] ) ? 'Escalas' : '',
							)
						)
					);
					$edit_url = add_query_arg(
						array(
							'page'          => 'iadal-departments-edit',
							'department_id' => (int) $department['id'],
						),
						admin_url( 'admin.php' )
					);
					$components_url = add_query_arg(
						array(
							'page'          => 'iadal-departments-components',
							'department_id' => (int) $department['id'],
						),
						admin_url( 'admin.php' )
					);
					?>
					<tr>
						<td data-label="<?php esc_attr_e( 'Departamento', 'iadal-gestao-ministerial' ); ?>">
							<strong><?php echo esc_html( $department['name'] ); ?></strong>
							<br /><small><?php echo esc_html( $department['type'] ); ?></small>
						</td>
						<td data-label="<?php esc_attr_e( 'Congregacao', 'iadal-gestao-ministerial' ); ?>">
							<?php echo esc_html( $department['church_name'] ?: '-' ); ?>
						</td>
						<td data-label="<?php esc_attr_e( 'Lider', 'iadal-gestao-ministerial' ); ?>">
							<?php echo esc_html( $department['leader_name'] ?: '-' ); ?>
						</td>
						<td data-label="<?php esc_attr_e( 'Funcionalidades', 'iadal-gestao-ministerial' ); ?>">
							<?php echo esc_html( $feature_summary ?: '-' ); ?>
						</td>
						<td data-label="<?php esc_attr_e( 'Status', 'iadal-gestao-ministerial' ); ?>">
							<span class="iadal-status iadal-status-<?php echo esc_attr( $department['status'] ); ?>">
								<?php echo esc_html( $status_options[ $department['status'] ] ?? $department['status'] ); ?>
							</span>
						</td>
						<td data-label="<?php esc_attr_e( 'Acoes', 'iadal-gestao-ministerial' ); ?>">
							<a class="button button-small" href="<?php echo esc_url( $edit_url ); ?>"><?php esc_html_e( 'Editar', 'iadal-gestao-ministerial' ); ?></a>
							<a class="button button-small" href="<?php echo esc_url( $components_url ); ?>"><?php esc_html_e( 'Componentes', 'iadal-gestao-ministerial' ); ?></a>
							<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="iadal-inline-form iadal-delete-form">
								<input type="hidden" name="action" value="iadal_departments_delete" />
								<input type="hidden" name="department_id" value="<?php echo esc_attr( (string) $department['id'] ); ?>" />
								<?php wp_nonce_field( 'iadal_departments_delete_' . (int) $department['id'], 'iadal_departments_nonce' ); ?>
								<button type="submit" class="button button-small button-link-delete"><?php esc_html_e( 'Excluir', 'iadal-gestao-ministerial' ); ?></button>
							</form>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<?php if ( $total_pages > 1 ) : ?>
			<div class="tablenav"><div class="tablenav-pages">
				<?php
				echo wp_kses_post(
					paginate_links(
						array(
							'base'      => add_query_arg( 'paged', '%#%' ),
							'format'    => '',
							'prev_text' => __( '&laquo;', 'iadal-gestao-ministerial' ),
							'next_text' => __( '&raquo;', 'iadal-gestao-ministerial' ),
							'total'     => $total_pages,
							'current'   => $page,
						)
					)
				);
				?>
			</div></div>
		<?php endif; ?>
	</div>
</div>
