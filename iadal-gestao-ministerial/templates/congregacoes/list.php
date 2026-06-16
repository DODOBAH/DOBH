<?php
/**
 * Congregations list admin screen.
 *
 * @package IADAL_Gestao_Ministerial
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$status_options = IADAL_Congregations_Controller::status_options();
$total_pages    = max( 1, (int) ceil( $total / $per_page ) );
$base_url       = admin_url( 'admin.php?page=iadal-congregations' );
?>

<div class="wrap iadal-members-wrap iadal-congregations-wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Congregacoes', 'iadal-gestao-ministerial' ); ?></h1>
	<a class="page-title-action" href="<?php echo esc_url( admin_url( 'admin.php?page=iadal-congregations-create' ) ); ?>">
		<?php esc_html_e( 'Criar Congregacao', 'iadal-gestao-ministerial' ); ?>
	</a>

	<?php include IADAL_GESTAO_DIR . 'templates/layouts/notices.php'; ?>

	<div class="iadal-card">
		<form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" class="iadal-filters iadal-filters-compact">
			<input type="hidden" name="page" value="iadal-congregations" />

			<label>
				<span><?php esc_html_e( 'Buscar por nome', 'iadal-gestao-ministerial' ); ?></span>
				<input
					type="search"
					name="s"
					value="<?php echo esc_attr( $filters['search'] ); ?>"
					placeholder="<?php esc_attr_e( 'Digite o nome da congregacao', 'iadal-gestao-ministerial' ); ?>"
				/>
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
				<button type="submit" class="button button-primary">
					<?php esc_html_e( 'Filtrar', 'iadal-gestao-ministerial' ); ?>
				</button>
				<a class="button" href="<?php echo esc_url( $base_url ); ?>">
					<?php esc_html_e( 'Limpar', 'iadal-gestao-ministerial' ); ?>
				</a>
			</div>
		</form>
	</div>

	<div class="iadal-card">
		<div class="iadal-table-header">
			<strong>
				<?php
				printf(
					/* translators: %d: total congregations. */
					esc_html__( '%d congregacao(oes) encontrada(s)', 'iadal-gestao-ministerial' ),
					(int) $total
				);
				?>
			</strong>
		</div>

		<table class="widefat fixed striped iadal-members-table iadal-congregations-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Nome', 'iadal-gestao-ministerial' ); ?></th>
					<th><?php esc_html_e( 'Pastor Local', 'iadal-gestao-ministerial' ); ?></th>
					<th><?php esc_html_e( 'Secretaria Local', 'iadal-gestao-ministerial' ); ?></th>
					<th><?php esc_html_e( 'Cidade/UF', 'iadal-gestao-ministerial' ); ?></th>
					<th><?php esc_html_e( 'Status', 'iadal-gestao-ministerial' ); ?></th>
					<th><?php esc_html_e( 'Acoes', 'iadal-gestao-ministerial' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $congregations ) ) : ?>
					<tr>
						<td colspan="6"><?php esc_html_e( 'Nenhuma congregacao encontrada.', 'iadal-gestao-ministerial' ); ?></td>
					</tr>
				<?php endif; ?>

				<?php foreach ( $congregations as $congregation ) : ?>
					<?php
					$edit_url = add_query_arg(
						array(
							'page'            => 'iadal-congregations-edit',
							'congregation_id' => (int) $congregation['id'],
						),
						admin_url( 'admin.php' )
					);
					$credentials_url = add_query_arg(
						array(
							'page'            => 'iadal-congregations-credentials',
							'congregation_id' => (int) $congregation['id'],
						),
						admin_url( 'admin.php' )
					);
					$next_status = 'bloqueado' === $congregation['status'] ? 'ativo' : 'bloqueado';
					?>
					<tr>
						<td data-label="<?php esc_attr_e( 'Nome', 'iadal-gestao-ministerial' ); ?>">
							<strong><?php echo esc_html( $congregation['name'] ); ?></strong>
							<br /><small><?php echo esc_html( IADAL_Congregations_Controller::status_options()[ $congregation['status'] ] ?? $congregation['status'] ); ?></small>
						</td>
						<td data-label="<?php esc_attr_e( 'Pastor Local', 'iadal-gestao-ministerial' ); ?>">
							<?php echo esc_html( $congregation['pastor_name'] ?: '-' ); ?>
							<?php if ( ! empty( $congregation['pastor_phone'] ) ) : ?>
								<br /><small><?php echo esc_html( $congregation['pastor_phone'] ); ?></small>
							<?php endif; ?>
						</td>
						<td data-label="<?php esc_attr_e( 'Secretaria Local', 'iadal-gestao-ministerial' ); ?>">
							<?php echo esc_html( $congregation['secretary_name'] ?: '-' ); ?>
							<?php if ( ! empty( $congregation['secretary_phone'] ) ) : ?>
								<br /><small><?php echo esc_html( $congregation['secretary_phone'] ); ?></small>
							<?php endif; ?>
						</td>
						<td data-label="<?php esc_attr_e( 'Cidade/UF', 'iadal-gestao-ministerial' ); ?>">
							<?php echo esc_html( trim( (string) $congregation['city'] . '/' . (string) $congregation['state'], '/' ) ?: '-' ); ?>
						</td>
						<td data-label="<?php esc_attr_e( 'Status', 'iadal-gestao-ministerial' ); ?>">
							<span class="iadal-status iadal-status-<?php echo esc_attr( $congregation['status'] ); ?>">
								<?php echo esc_html( $status_options[ $congregation['status'] ] ?? $congregation['status'] ); ?>
							</span>
						</td>
						<td data-label="<?php esc_attr_e( 'Acoes', 'iadal-gestao-ministerial' ); ?>">
							<a class="button button-small" href="<?php echo esc_url( $edit_url ); ?>">
								<?php esc_html_e( 'Editar', 'iadal-gestao-ministerial' ); ?>
							</a>
							<a class="button button-small" href="<?php echo esc_url( $credentials_url ); ?>">
								<?php esc_html_e( 'Credenciais', 'iadal-gestao-ministerial' ); ?>
							</a>
							<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="iadal-inline-form iadal-status-form">
								<input type="hidden" name="action" value="iadal_congregations_status" />
								<input type="hidden" name="congregation_id" value="<?php echo esc_attr( (string) $congregation['id'] ); ?>" />
								<input type="hidden" name="status" value="<?php echo esc_attr( $next_status ); ?>" />
								<?php wp_nonce_field( 'iadal_congregations_status_' . (int) $congregation['id'], 'iadal_congregations_nonce' ); ?>
								<button type="submit" class="button button-small">
									<?php echo 'bloqueado' === $next_status ? esc_html__( 'Bloquear', 'iadal-gestao-ministerial' ) : esc_html__( 'Desbloquear', 'iadal-gestao-ministerial' ); ?>
								</button>
							</form>
							<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="iadal-inline-form iadal-delete-form">
								<input type="hidden" name="action" value="iadal_congregations_delete" />
								<input type="hidden" name="congregation_id" value="<?php echo esc_attr( (string) $congregation['id'] ); ?>" />
								<?php wp_nonce_field( 'iadal_congregations_delete_' . (int) $congregation['id'], 'iadal_congregations_nonce' ); ?>
								<button type="submit" class="button button-small button-link-delete">
									<?php esc_html_e( 'Excluir', 'iadal-gestao-ministerial' ); ?>
								</button>
							</form>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<?php if ( $total_pages > 1 ) : ?>
			<div class="tablenav">
				<div class="tablenav-pages">
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
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>
