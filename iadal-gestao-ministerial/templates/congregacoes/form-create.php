<?php
/**
 * Create congregation admin screen.
 *
 * @package IADAL_Gestao_Ministerial
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$status_options = IADAL_Congregations_Controller::status_options();
?>

<div class="wrap iadal-members-wrap iadal-congregations-wrap">
	<h1><?php esc_html_e( 'Criar Congregacao', 'iadal-gestao-ministerial' ); ?></h1>

	<?php include IADAL_GESTAO_DIR . 'templates/layouts/notices.php'; ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="iadal-member-form iadal-congregation-form">
		<input type="hidden" name="action" value="iadal_congregations_create" />
		<?php wp_nonce_field( 'iadal_congregations_create', 'iadal_congregations_nonce' ); ?>

		<div class="iadal-form-grid">
			<section class="iadal-card">
				<h2><?php esc_html_e( 'Dados da congregacao', 'iadal-gestao-ministerial' ); ?></h2>

				<label class="iadal-field iadal-field-wide">
					<span><?php esc_html_e( 'Nome da congregacao', 'iadal-gestao-ministerial' ); ?> <strong>*</strong></span>
					<input type="text" name="name" required maxlength="190" />
				</label>

				<label class="iadal-field">
					<span><?php esc_html_e( 'Status inicial', 'iadal-gestao-ministerial' ); ?></span>
					<select name="status">
						<?php foreach ( $status_options as $value => $label ) : ?>
							<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, 'ativo' ); ?>>
								<?php echo esc_html( $label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</label>

				<label class="iadal-field">
					<span><?php esc_html_e( 'Bloquear pedidos de limpeza', 'iadal-gestao-ministerial' ); ?></span>
					<label class="iadal-checkbox">
						<input type="checkbox" name="cleaning_requests_blocked" value="1" />
						<?php esc_html_e( 'A sede nao enviara produtos de limpeza no momento.', 'iadal-gestao-ministerial' ); ?>
					</label>
				</label>

				<label class="iadal-field iadal-field-wide">
					<span><?php esc_html_e( 'Observacoes', 'iadal-gestao-ministerial' ); ?></span>
					<textarea name="notes" rows="5"></textarea>
				</label>
			</section>

			<section class="iadal-card">
				<h2><?php esc_html_e( 'Lideranca local', 'iadal-gestao-ministerial' ); ?></h2>

				<label class="iadal-field">
					<span><?php esc_html_e( 'Nome do Pastor Local', 'iadal-gestao-ministerial' ); ?> <strong>*</strong></span>
					<input type="text" name="pastor_name" required maxlength="190" />
				</label>

				<label class="iadal-field">
					<span><?php esc_html_e( 'Telefone do Pastor Local', 'iadal-gestao-ministerial' ); ?></span>
					<input type="text" name="pastor_phone" maxlength="30" />
				</label>

				<label class="iadal-field">
					<span><?php esc_html_e( 'Nome da Secretaria Local', 'iadal-gestao-ministerial' ); ?> <strong>*</strong></span>
					<input type="text" name="secretary_name" required maxlength="190" />
				</label>

				<label class="iadal-field">
					<span><?php esc_html_e( 'Telefone da Secretaria Local', 'iadal-gestao-ministerial' ); ?></span>
					<input type="text" name="secretary_phone" maxlength="30" />
				</label>
			</section>

			<section class="iadal-card">
				<h2><?php esc_html_e( 'Endereco', 'iadal-gestao-ministerial' ); ?></h2>

				<label class="iadal-field">
					<span><?php esc_html_e( 'CEP', 'iadal-gestao-ministerial' ); ?></span>
					<input type="text" name="zip_code" maxlength="20" />
				</label>

				<label class="iadal-field iadal-field-wide">
					<span><?php esc_html_e( 'Endereco', 'iadal-gestao-ministerial' ); ?></span>
					<input type="text" name="address" maxlength="255" />
				</label>

				<label class="iadal-field">
					<span><?php esc_html_e( 'Numero', 'iadal-gestao-ministerial' ); ?></span>
					<input type="text" name="address_number" maxlength="30" />
				</label>

				<label class="iadal-field">
					<span><?php esc_html_e( 'Complemento', 'iadal-gestao-ministerial' ); ?></span>
					<input type="text" name="address_complement" maxlength="120" />
				</label>

				<label class="iadal-field">
					<span><?php esc_html_e( 'Bairro', 'iadal-gestao-ministerial' ); ?></span>
					<input type="text" name="district" maxlength="120" />
				</label>

				<label class="iadal-field">
					<span><?php esc_html_e( 'Cidade', 'iadal-gestao-ministerial' ); ?></span>
					<input type="text" name="city" maxlength="120" />
				</label>

				<label class="iadal-field">
					<span><?php esc_html_e( 'UF', 'iadal-gestao-ministerial' ); ?></span>
					<input type="text" name="state" maxlength="2" />
				</label>
			</section>
		</div>

		<div class="iadal-form-actions">
			<button type="submit" class="button button-primary button-large">
				<?php esc_html_e( 'Salvar e gerar credenciais', 'iadal-gestao-ministerial' ); ?>
			</button>
			<a class="button button-large" href="<?php echo esc_url( admin_url( 'admin.php?page=iadal-congregations' ) ); ?>">
				<?php esc_html_e( 'Cancelar', 'iadal-gestao-ministerial' ); ?>
			</a>
		</div>
	</form>
</div>
