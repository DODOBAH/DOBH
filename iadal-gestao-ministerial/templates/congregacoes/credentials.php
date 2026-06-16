<?php
/**
 * Congregation credentials admin screen.
 *
 * @package IADAL_Gestao_Ministerial
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$role_labels = IADAL_Congregations_Controller::role_labels();
?>

<div class="wrap iadal-members-wrap iadal-congregations-wrap">
	<h1>
		<?php
		printf(
			/* translators: %s: congregation name. */
			esc_html__( 'Credenciais: %s', 'iadal-gestao-ministerial' ),
			esc_html( $congregation['name'] ?? '' )
		);
		?>
	</h1>

	<?php include IADAL_GESTAO_DIR . 'templates/layouts/notices.php'; ?>

	<?php if ( ! empty( $credentials ) ) : ?>
		<div class="notice notice-warning">
			<p>
				<strong><?php esc_html_e( 'Atencao:', 'iadal-gestao-ministerial' ); ?></strong>
				<?php esc_html_e( 'As senhas provisorias abaixo serao exibidas somente agora. Copie e entregue com seguranca aos responsaveis.', 'iadal-gestao-ministerial' ); ?>
			</p>
		</div>

		<div class="iadal-card">
			<h2><?php esc_html_e( 'Senhas provisorias geradas', 'iadal-gestao-ministerial' ); ?></h2>
			<div class="iadal-credentials-grid">
				<?php foreach ( $credentials as $credential ) : ?>
					<div class="iadal-credential-card">
						<h3><?php echo esc_html( $credential['name'] ?? '' ); ?></h3>
						<p>
							<strong><?php esc_html_e( 'Perfil:', 'iadal-gestao-ministerial' ); ?></strong>
							<?php echo esc_html( $role_labels[ $credential['role'] ?? '' ] ?? ( $credential['role'] ?? '' ) ); ?>
						</p>
						<p>
							<strong><?php esc_html_e( 'Login:', 'iadal-gestao-ministerial' ); ?></strong>
							<code><?php echo esc_html( $credential['login'] ?? '' ); ?></code>
						</p>
						<p>
							<strong><?php esc_html_e( 'Senha provisoria:', 'iadal-gestao-ministerial' ); ?></strong>
							<code><?php echo esc_html( $credential['password'] ?? '' ); ?></code>
						</p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>

	<div class="iadal-card">
		<h2><?php esc_html_e( 'Usuarios da congregacao', 'iadal-gestao-ministerial' ); ?></h2>

		<table class="widefat fixed striped iadal-members-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Nome', 'iadal-gestao-ministerial' ); ?></th>
					<th><?php esc_html_e( 'Perfil', 'iadal-gestao-ministerial' ); ?></th>
					<th><?php esc_html_e( 'Login', 'iadal-gestao-ministerial' ); ?></th>
					<th><?php esc_html_e( 'Status', 'iadal-gestao-ministerial' ); ?></th>
					<th><?php esc_html_e( 'Acoes', 'iadal-gestao-ministerial' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $users ) ) : ?>
					<tr>
						<td colspan="5"><?php esc_html_e( 'Nenhum usuario encontrado para esta congregacao.', 'iadal-gestao-ministerial' ); ?></td>
					</tr>
				<?php endif; ?>

				<?php foreach ( $users as $user ) : ?>
					<tr>
						<td data-label="<?php esc_attr_e( 'Nome', 'iadal-gestao-ministerial' ); ?>">
							<?php echo esc_html( $user['name'] ?? '' ); ?>
							<?php if ( ! empty( $user['phone'] ) ) : ?>
								<br /><small><?php echo esc_html( $user['phone'] ); ?></small>
							<?php endif; ?>
						</td>
						<td data-label="<?php esc_attr_e( 'Perfil', 'iadal-gestao-ministerial' ); ?>">
							<?php echo esc_html( $role_labels[ $user['role'] ?? '' ] ?? ( $user['role'] ?? '' ) ); ?>
						</td>
						<td data-label="<?php esc_attr_e( 'Login', 'iadal-gestao-ministerial' ); ?>">
							<code><?php echo esc_html( $user['login'] ?? '' ); ?></code>
						</td>
						<td data-label="<?php esc_attr_e( 'Status', 'iadal-gestao-ministerial' ); ?>">
							<span class="iadal-status iadal-status-<?php echo esc_attr( $user['status'] ?? 'inativo' ); ?>">
								<?php echo esc_html( ucfirst( (string) ( $user['status'] ?? 'inativo' ) ) ); ?>
							</span>
							<?php if ( ! empty( $user['must_change_password'] ) ) : ?>
								<br /><small><?php esc_html_e( 'Troca de senha obrigatoria no primeiro acesso.', 'iadal-gestao-ministerial' ); ?></small>
							<?php endif; ?>
						</td>
						<td data-label="<?php esc_attr_e( 'Acoes', 'iadal-gestao-ministerial' ); ?>">
							<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="iadal-inline-form iadal-reset-password-form">
								<input type="hidden" name="action" value="iadal_congregations_reset_password" />
								<input type="hidden" name="congregation_id" value="<?php echo esc_attr( (string) $congregation['id'] ); ?>" />
								<input type="hidden" name="user_id" value="<?php echo esc_attr( (string) $user['id'] ); ?>" />
								<?php wp_nonce_field( 'iadal_congregations_reset_password_' . (int) $user['id'], 'iadal_congregations_nonce' ); ?>
								<button type="submit" class="button button-small">
									<?php esc_html_e( 'Gerar nova senha', 'iadal-gestao-ministerial' ); ?>
								</button>
							</form>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>

	<div class="iadal-form-actions">
		<a class="button button-large" href="<?php echo esc_url( admin_url( 'admin.php?page=iadal-congregations' ) ); ?>">
			<?php esc_html_e( 'Voltar para congregacoes', 'iadal-gestao-ministerial' ); ?>
		</a>
	</div>
</div>
