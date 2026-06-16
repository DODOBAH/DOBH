<?php
/**
 * Create member admin screen.
 *
 * @package IADAL_Gestao_Ministerial
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$status_options     = IADAL_Members_Controller::status_options();
$entry_type_options = IADAL_Members_Controller::entry_type_options();
?>

<div class="wrap iadal-members-wrap">
	<h1><?php esc_html_e( 'Cadastrar Membro', 'iadal-gestao-ministerial' ); ?></h1>

	<?php include IADAL_GESTAO_DIR . 'templates/layouts/notices.php'; ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data" class="iadal-member-form">
		<input type="hidden" name="action" value="iadal_members_create" />
		<?php wp_nonce_field( 'iadal_members_create', 'iadal_members_nonce' ); ?>

		<div class="iadal-form-grid">
			<section class="iadal-card">
				<h2><?php esc_html_e( 'Dados principais', 'iadal-gestao-ministerial' ); ?></h2>

				<label class="iadal-field">
					<span><?php esc_html_e( 'Foto do membro', 'iadal-gestao-ministerial' ); ?></span>
					<input type="file" name="photo" accept="image/jpeg,image/png,image/webp" data-iadal-photo-input />
					<img class="iadal-photo-preview" data-iadal-photo-preview alt="" />
				</label>

				<label class="iadal-field">
					<span><?php esc_html_e( 'Nome completo', 'iadal-gestao-ministerial' ); ?> <strong>*</strong></span>
					<input type="text" name="full_name" required maxlength="190" />
				</label>

				<label class="iadal-field">
					<span><?php esc_html_e( 'CPF', 'iadal-gestao-ministerial' ); ?> <strong>*</strong></span>
					<input type="text" name="cpf" required maxlength="14" data-iadal-cpf />
					<small><?php esc_html_e( 'O CPF deve ser unico em todo o sistema.', 'iadal-gestao-ministerial' ); ?></small>
				</label>

				<label class="iadal-field">
					<span><?php esc_html_e( 'Telefone', 'iadal-gestao-ministerial' ); ?></span>
					<input type="text" name="phone" maxlength="30" />
				</label>

				<label class="iadal-field">
					<span><?php esc_html_e( 'E-mail', 'iadal-gestao-ministerial' ); ?></span>
					<input type="email" name="email" maxlength="190" />
				</label>

				<label class="iadal-field">
					<span><?php esc_html_e( 'Data de nascimento', 'iadal-gestao-ministerial' ); ?></span>
					<input type="date" name="birth_date" />
				</label>

				<label class="iadal-field">
					<span><?php esc_html_e( 'Sexo', 'iadal-gestao-ministerial' ); ?></span>
					<select name="gender">
						<option value=""><?php esc_html_e( 'Selecione', 'iadal-gestao-ministerial' ); ?></option>
						<option value="Masculino"><?php esc_html_e( 'Masculino', 'iadal-gestao-ministerial' ); ?></option>
						<option value="Feminino"><?php esc_html_e( 'Feminino', 'iadal-gestao-ministerial' ); ?></option>
					</select>
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

			<section class="iadal-card">
				<h2><?php esc_html_e( 'Situacao ministerial', 'iadal-gestao-ministerial' ); ?></h2>

				<label class="iadal-field">
					<span><?php esc_html_e( 'Estado civil', 'iadal-gestao-ministerial' ); ?></span>
					<input type="text" name="marital_status" maxlength="40" />
				</label>

				<label class="iadal-field">
					<span><?php esc_html_e( 'Conjuge', 'iadal-gestao-ministerial' ); ?></span>
					<input type="text" name="spouse_name" maxlength="190" />
				</label>

				<label class="iadal-field">
					<span><?php esc_html_e( 'Tipo de entrada', 'iadal-gestao-ministerial' ); ?></span>
					<select name="entry_type" data-iadal-entry-type>
						<?php foreach ( $entry_type_options as $value => $label ) : ?>
							<option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
					<small><?php esc_html_e( 'Entradas por carta sem anexo ficam inativas ate regularizacao.', 'iadal-gestao-ministerial' ); ?></small>
				</label>

				<label class="iadal-field">
					<span><?php esc_html_e( 'Status', 'iadal-gestao-ministerial' ); ?></span>
					<select name="status">
						<?php foreach ( $status_options as $value => $label ) : ?>
							<option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>

				<label class="iadal-field" data-iadal-change-letter>
					<span><?php esc_html_e( 'Carta de mudanca', 'iadal-gestao-ministerial' ); ?></span>
					<input type="file" name="change_letter" accept="application/pdf,image/jpeg,image/png,image/webp" />
				</label>

				<label class="iadal-field" data-iadal-acclamation-letter>
					<span><?php esc_html_e( 'Carta de aclamacao', 'iadal-gestao-ministerial' ); ?></span>
					<input type="file" name="acclamation_letter" accept="application/pdf,image/jpeg,image/png,image/webp" />
				</label>

				<label class="iadal-field iadal-field-wide">
					<span><?php esc_html_e( 'Observacoes', 'iadal-gestao-ministerial' ); ?></span>
					<textarea name="notes" rows="5"></textarea>
				</label>
			</section>
		</div>

		<div class="iadal-form-actions">
			<button type="submit" class="button button-primary button-large">
				<?php esc_html_e( 'Salvar membro', 'iadal-gestao-ministerial' ); ?>
			</button>
			<a class="button button-large" href="<?php echo esc_url( admin_url( 'admin.php?page=iadal-members' ) ); ?>">
				<?php esc_html_e( 'Cancelar', 'iadal-gestao-ministerial' ); ?>
			</a>
		</div>
	</form>
</div>
