<?php
/**
 * Department components screen.
 *
 * @package IADAL_Gestao_Ministerial
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap iadal-members-wrap iadal-departments-wrap">
	<h1>
		<?php
		printf(
			/* translators: %s: department name. */
			esc_html__( 'Componentes: %s', 'iadal-gestao-ministerial' ),
			esc_html( $department['name'] ?? '' )
		);
		?>
	</h1>

	<?php include IADAL_GESTAO_DIR . 'templates/layouts/notices.php'; ?>

	<?php if ( ! empty( $credentials ) ) : ?>
		<div class="notice notice-warning">
			<p><strong><?php esc_html_e( 'Atencao:', 'iadal-gestao-ministerial' ); ?></strong> <?php esc_html_e( 'As credenciais provisorias abaixo serao exibidas somente agora.', 'iadal-gestao-ministerial' ); ?></p>
		</div>
		<div class="iadal-card">
			<h2><?php esc_html_e( 'Credenciais geradas', 'iadal-gestao-ministerial' ); ?></h2>
			<div class="iadal-credentials-grid">
				<?php foreach ( $credentials as $credential ) : ?>
					<div class="iadal-credential-card">
						<h3><?php echo esc_html( $credential['name'] ?? '' ); ?></h3>
						<p><strong><?php esc_html_e( 'Login:', 'iadal-gestao-ministerial' ); ?></strong> <code><?php echo esc_html( $credential['login'] ?? '' ); ?></code></p>
						<p><strong><?php esc_html_e( 'Senha:', 'iadal-gestao-ministerial' ); ?></strong> <code><?php echo esc_html( $credential['password'] ?? '' ); ?></code></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>

	<div class="iadal-card">
		<h2><?php esc_html_e( 'Adicionar componente', 'iadal-gestao-ministerial' ); ?></h2>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="iadal-filters">
			<input type="hidden" name="action" value="iadal_departments_component_create" />
			<input type="hidden" name="department_id" value="<?php echo esc_attr( (string) $department['id'] ); ?>" />
			<?php wp_nonce_field( 'iadal_departments_component_create_' . (int) $department['id'], 'iadal_departments_nonce' ); ?>

			<label>
				<span><?php esc_html_e( 'Nome', 'iadal-gestao-ministerial' ); ?> <strong>*</strong></span>
				<input type="text" name="name" required maxlength="190" />
			</label>

			<label>
				<span><?php esc_html_e( 'Telefone', 'iadal-gestao-ministerial' ); ?></span>
				<input type="text" name="phone" maxlength="30" />
			</label>

			<label>
				<span><?php esc_html_e( 'Funcao', 'iadal-gestao-ministerial' ); ?></span>
				<input type="text" name="function_name" maxlength="120" />
			</label>

			<div class="iadal-filter-actions">
				<button type="submit" class="button button-primary"><?php esc_html_e( 'Adicionar', 'iadal-gestao-ministerial' ); ?></button>
			</div>
		</form>
	</div>

	<div class="iadal-card">
		<h2><?php esc_html_e( 'Componentes cadastrados', 'iadal-gestao-ministerial' ); ?></h2>
		<table class="widefat fixed striped iadal-members-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Nome', 'iadal-gestao-ministerial' ); ?></th>
					<th><?php esc_html_e( 'Telefone', 'iadal-gestao-ministerial' ); ?></th>
					<th><?php esc_html_e( 'Funcao', 'iadal-gestao-ministerial' ); ?></th>
					<th><?php esc_html_e( 'Tipo', 'iadal-gestao-ministerial' ); ?></th>
					<th><?php esc_html_e( 'Status', 'iadal-gestao-ministerial' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $components ) ) : ?>
					<tr><td colspan="5"><?php esc_html_e( 'Nenhum componente cadastrado.', 'iadal-gestao-ministerial' ); ?></td></tr>
				<?php endif; ?>

				<?php foreach ( $components as $component ) : ?>
					<tr>
						<td data-label="<?php esc_attr_e( 'Nome', 'iadal-gestao-ministerial' ); ?>"><?php echo esc_html( $component['name'] ); ?></td>
						<td data-label="<?php esc_attr_e( 'Telefone', 'iadal-gestao-ministerial' ); ?>"><?php echo esc_html( $component['phone'] ?: '-' ); ?></td>
						<td data-label="<?php esc_attr_e( 'Funcao', 'iadal-gestao-ministerial' ); ?>"><?php echo esc_html( $component['function_name'] ?: '-' ); ?></td>
						<td data-label="<?php esc_attr_e( 'Tipo', 'iadal-gestao-ministerial' ); ?>"><?php echo ! empty( $component['is_leader'] ) ? esc_html__( 'Lider', 'iadal-gestao-ministerial' ) : esc_html__( 'Componente', 'iadal-gestao-ministerial' ); ?></td>
						<td data-label="<?php esc_attr_e( 'Status', 'iadal-gestao-ministerial' ); ?>"><span class="iadal-status iadal-status-<?php echo esc_attr( $component['status'] ); ?>"><?php echo esc_html( $component['status'] ); ?></span></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>

	<div class="iadal-form-actions">
		<a class="button button-large" href="<?php echo esc_url( admin_url( 'admin.php?page=iadal-departments' ) ); ?>"><?php esc_html_e( 'Voltar', 'iadal-gestao-ministerial' ); ?></a>
	</div>
</div>
