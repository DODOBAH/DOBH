<?php
/**
 * Members list admin screen.
 *
 * @package IADAL_Gestao_Ministerial
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$status_options     = IADAL_Members_Controller::status_options();
$entry_type_options = IADAL_Members_Controller::entry_type_options();
$total_pages        = max( 1, (int) ceil( $total / $per_page ) );
$base_url           = admin_url( 'admin.php?page=iadal-members' );
$congregation_names = array();

foreach ( $congregations as $congregation ) {
	$congregation_names[ (int) $congregation['id'] ] = (string) $congregation['name'];
}
?>

<div class="wrap iadal-members-wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Cadastro de Membros', 'iadal-gestao-ministerial' ); ?></h1>
	<a class="page-title-action" href="<?php echo esc_url( admin_url( 'admin.php?page=iadal-members-create' ) ); ?>">
		<?php esc_html_e( 'Cadastrar Membro', 'iadal-gestao-ministerial' ); ?>
	</a>
	<a class="page-title-action" href="<?php echo esc_url( admin_url( 'admin.php?page=iadal-members-birthdays' ) ); ?>">
		<?php esc_html_e( 'Aniversariantes', 'iadal-gestao-ministerial' ); ?>
	</a>

	<?php include IADAL_GESTAO_DIR . 'templates/layouts/notices.php'; ?>

	<div class="iadal-card">
		<form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" class="iadal-filters">
			<input type="hidden" name="page" value="iadal-members" />

			<label>
				<span><?php esc_html_e( 'Buscar por nome', 'iadal-gestao-ministerial' ); ?></span>
				<input
					type="search"
					name="s"
					value="<?php echo esc_attr( $filters['search'] ); ?>"
					placeholder="<?php esc_attr_e( 'Digite o nome do membro', 'iadal-gestao-ministerial' ); ?>"
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

			<label>
				<span><?php esc_html_e( 'Tipo de entrada', 'iadal-gestao-ministerial' ); ?></span>
				<select name="entry_type">
					<option value=""><?php esc_html_e( 'Todos', 'iadal-gestao-ministerial' ); ?></option>
					<?php foreach ( $entry_type_options as $value => $label ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $filters['entry_type'], $value ); ?>>
							<?php echo esc_html( $label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</label>

			<label>
				<span><?php esc_html_e( 'Mes de aniversario', 'iadal-gestao-ministerial' ); ?></span>
				<select name="birth_month">
					<option value=""><?php esc_html_e( 'Todos', 'iadal-gestao-ministerial' ); ?></option>
					<?php for ( $month = 1; $month <= 12; $month++ ) : ?>
						<option value="<?php echo esc_attr( (string) $month ); ?>" <?php selected( $filters['birth_month'], (string) $month ); ?>>
							<?php echo esc_html( date_i18n( 'F', mktime( 0, 0, 0, $month, 1 ) ) ); ?>
						</option>
					<?php endfor; ?>
				</select>
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
					/* translators: %d: total members. */
					esc_html__( '%d membro(s) encontrado(s)', 'iadal-gestao-ministerial' ),
					(int) $total
				);
				?>
			</strong>
		</div>

		<table class="widefat fixed striped iadal-members-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Foto', 'iadal-gestao-ministerial' ); ?></th>
					<th><?php esc_html_e( 'Nome', 'iadal-gestao-ministerial' ); ?></th>
					<th><?php esc_html_e( 'CPF', 'iadal-gestao-ministerial' ); ?></th>
					<th><?php esc_html_e( 'Telefone', 'iadal-gestao-ministerial' ); ?></th>
					<th><?php esc_html_e( 'Congregacao', 'iadal-gestao-ministerial' ); ?></th>
					<th><?php esc_html_e( 'Nascimento', 'iadal-gestao-ministerial' ); ?></th>
					<th><?php esc_html_e( 'Entrada', 'iadal-gestao-ministerial' ); ?></th>
					<th><?php esc_html_e( 'Status', 'iadal-gestao-ministerial' ); ?></th>
					<th><?php esc_html_e( 'Acoes', 'iadal-gestao-ministerial' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $members ) ) : ?>
					<tr>
						<td colspan="9"><?php esc_html_e( 'Nenhum membro encontrado.', 'iadal-gestao-ministerial' ); ?></td>
					</tr>
				<?php endif; ?>

				<?php foreach ( $members as $member ) : ?>
					<?php
					$photo_url = ! empty( $member['photo_attachment_id'] ) ? wp_get_attachment_image_url( (int) $member['photo_attachment_id'], 'thumbnail' ) : '';
					$edit_url  = add_query_arg(
						array(
							'page'      => 'iadal-members-edit',
							'member_id' => (int) $member['id'],
						),
						admin_url( 'admin.php' )
					);
					?>
					<tr>
						<td data-label="<?php esc_attr_e( 'Foto', 'iadal-gestao-ministerial' ); ?>">
							<?php if ( $photo_url ) : ?>
								<img class="iadal-member-avatar" src="<?php echo esc_url( $photo_url ); ?>" alt="<?php echo esc_attr( $member['full_name'] ); ?>" />
							<?php else : ?>
								<span class="iadal-member-avatar iadal-member-avatar-empty" aria-hidden="true"></span>
							<?php endif; ?>
						</td>
						<td data-label="<?php esc_attr_e( 'Nome', 'iadal-gestao-ministerial' ); ?>">
							<strong><?php echo esc_html( $member['full_name'] ); ?></strong>
							<?php if ( ! empty( $member['email'] ) ) : ?>
								<br /><small><?php echo esc_html( $member['email'] ); ?></small>
							<?php endif; ?>
						</td>
						<td data-label="<?php esc_attr_e( 'CPF', 'iadal-gestao-ministerial' ); ?>">
							<?php echo esc_html( IADAL_Members_Controller::format_cpf( $member['cpf'] ) ); ?>
						</td>
						<td data-label="<?php esc_attr_e( 'Telefone', 'iadal-gestao-ministerial' ); ?>">
							<?php echo esc_html( $member['phone'] ?: '-' ); ?>
						</td>
						<td data-label="<?php esc_attr_e( 'Congregacao', 'iadal-gestao-ministerial' ); ?>">
							<?php echo esc_html( $congregation_names[ (int) $member['church_id'] ] ?? '-' ); ?>
						</td>
						<td data-label="<?php esc_attr_e( 'Nascimento', 'iadal-gestao-ministerial' ); ?>">
							<?php echo esc_html( IADAL_Members_Controller::format_date( $member['birth_date'] ) ); ?>
						</td>
						<td data-label="<?php esc_attr_e( 'Entrada', 'iadal-gestao-ministerial' ); ?>">
							<?php echo esc_html( $entry_type_options[ $member['entry_type'] ] ?? $member['entry_type'] ); ?>
						</td>
						<td data-label="<?php esc_attr_e( 'Status', 'iadal-gestao-ministerial' ); ?>">
							<span class="iadal-status iadal-status-<?php echo esc_attr( $member['status'] ); ?>">
								<?php echo esc_html( $status_options[ $member['status'] ] ?? $member['status'] ); ?>
							</span>
						</td>
						<td data-label="<?php esc_attr_e( 'Acoes', 'iadal-gestao-ministerial' ); ?>">
							<a class="button button-small" href="<?php echo esc_url( $edit_url ); ?>">
								<?php esc_html_e( 'Editar', 'iadal-gestao-ministerial' ); ?>
							</a>
							<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="iadal-inline-form iadal-delete-form">
								<input type="hidden" name="action" value="iadal_members_delete" />
								<input type="hidden" name="member_id" value="<?php echo esc_attr( (string) $member['id'] ); ?>" />
								<?php wp_nonce_field( 'iadal_members_delete_' . (int) $member['id'], 'iadal_members_nonce' ); ?>
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
