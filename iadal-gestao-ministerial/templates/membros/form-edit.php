<?php
/**
 * Edit member admin screen.
 *
 * @package IADAL_Gestao_Ministerial
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$status_options     = IADAL_Members_Controller::status_options();
$entry_type_options = IADAL_Members_Controller::entry_type_options();
$photo_url          = ! empty( $member['photo_attachment_id'] ) ? wp_get_attachment_image_url( (int) $member['photo_attachment_id'], 'medium' ) : '';
$change_letter_url  = ! empty( $change_letter['id'] ) ? wp_nonce_url(
	add_query_arg(
		array(
			'action'      => 'iadal_members_document_download',
			'document_id' => (int) $change_letter['id'],
		),
		admin_url( 'admin-post.php' )
	),
	'iadal_members_document_download_' . (int) $change_letter['id']
) : '';
$acclamation_url    = ! empty( $acclamation_letter['id'] ) ? wp_nonce_url(
	add_query_arg(
		array(
			'action'      => 'iadal_members_document_download',
			'document_id' => (int) $acclamation_letter['id'],
		),
		admin_url( 'admin-post.php' )
	),
	'iadal_members_document_download_' . (int) $acclamation_letter['id']
) : '';
?>

<div class="wrap iadal-members-wrap">
	<h1>
		<?php
		printf(
			/* translators: %s: member name. */
			esc_html__( 'Editar Membro: %s', 'iadal-gestao-ministerial' ),
			esc_html( $member['full_name'] )
		);
		?>
	</h1>

	<?php include IADAL_GESTAO_DIR . 'templates/layouts/notices.php'; ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data" class="iadal-member-form">
		<input type="hidden" name="action" value="iadal_members_update" />
		<input type="hidden" name="member_id" value="<?php echo esc_attr( (string) $member['id'] ); ?>" />
		<?php wp_nonce_field( 'iadal_members_update_' . (int) $member['id'], 'iadal_members_nonce' ); ?>

		<div class="iadal-form-grid">
			<section class="iadal-card">
				<h2><?php esc_html_e( 'Dados principais', 'iadal-gestao-ministerial' ); ?></h2>

				<label class="iadal-field">
					<span><?php esc_html_e( 'Foto do membro', 'iadal-gestao-ministerial' ); ?></span>
					<?php if ( $photo_url ) : ?>
						<img class="iadal-photo-preview is-visible" data-iadal-photo-preview src="<?php echo esc_url( $photo_url ); ?>" alt="<?php echo esc_attr( $member['full_name'] ); ?>" />
					<?php else : ?>
						<img class="iadal-photo-preview" data-iadal-photo-preview alt="" />
					<?php endif; ?>
					<input type="file" name="photo" accept="image/jpeg,image/png,image/webp" data-iadal-photo-input />
					<small><?php esc_html_e( 'Envie uma nova foto apenas se desejar substituir a atual.', 'iadal-gestao-ministerial' ); ?></small>
				</label>

				<label class="iadal-field">
					<span><?php esc_html_e( 'Nome completo', 'iadal-gestao-ministerial' ); ?> <strong>*</strong></span>
					<input type="text" name="full_name" required maxlength="190" value="<?php echo esc_attr( $member['full_name'] ?? '' ); ?>" />
				</label>

				<label class="iadal-field">
					<span><?php esc_html_e( 'CPF', 'iadal-gestao-ministerial' ); ?> <strong>*</strong></span>
					<input type="text" name="cpf" required maxlength="14" data-iadal-cpf value="<?php echo esc_attr( IADAL_Members_Controller::format_cpf( $member['cpf'] ) ); ?>" />
					<small><?php esc_html_e( 'O CPF deve permanecer unico em todo o sistema.', 'iadal-gestao-ministerial' ); ?></small>
				</label>

				<label class="iadal-field">
					<span><?php esc_html_e( 'Telefone', 'iadal-gestao-ministerial' ); ?></span>
					<input type="text" name="phone" maxlength="30" value="<?php echo esc_attr( $member['phone'] ?? '' ); ?>" />
				</label>

				<label class="iadal-field">
					<span><?php esc_html_e( 'E-mail', 'iadal-gestao-ministerial' ); ?></span>
					<input type="email" name="email" maxlength="190" value="<?php echo esc_attr( $member['email'] ?? '' ); ?>" />
				</label>

				<label class="iadal-field">
					<span><?php esc_html_e( 'Data de nascimento', 'iadal-gestao-ministerial' ); ?></span>
					<input type="date" name="birth_date" value="<?php echo esc_attr( $member['birth_date'] ?? '' ); ?>" />
				</label>

				<label class="iadal-field">
					<span><?php esc_html_e( 'Sexo', 'iadal-gestao-ministerial' ); ?></span>
					<select name="gender">
						<option value=""><?php esc_html_e( 'Selecione', 'iadal-gestao-ministerial' ); ?></option>
						<option value="Masculino" <?php selected( $member['gender'] ?? '', 'Masculino' ); ?>><?php esc_html_e( 'Masculino', 'iadal-gestao-ministerial' ); ?></option>
						<option value="Feminino" <?php selected( $member['gender'] ?? '', 'Feminino' ); ?>><?php esc_html_e( 'Feminino', 'iadal-gestao-ministerial' ); ?></option>
					</select>
				</label>
			</section>

			<section class="iadal-card">
				<h2><?php esc_html_e( 'Endereco', 'iadal-gestao-ministerial' ); ?></h2>

				<label class="iadal-field">
					<span><?php esc_html_e( 'CEP', 'iadal-gestao-ministerial' ); ?></span>
					<input type="text" name="zip_code" maxlength="20" value="<?php echo esc_attr( $member['zip_code'] ?? '' ); ?>" />
				</label>

				<label class="iadal-field iadal-field-wide">
					<span><?php esc_html_e( 'Endereco', 'iadal-gestao-ministerial' ); ?></span>
					<input type="text" name="address" maxlength="255" value="<?php echo esc_attr( $member['address'] ?? '' ); ?>" />
				</label>

				<label class="iadal-field">
					<span><?php esc_html_e( 'Numero', 'iadal-gestao-ministerial' ); ?></span>
					<input type="text" name="address_number" maxlength="30" value="<?php echo esc_attr( $member['address_number'] ?? '' ); ?>" />
				</label>

				<label class="iadal-field">
					<span><?php esc_html_e( 'Complemento', 'iadal-gestao-ministerial' ); ?></span>
					<input type="text" name="address_complement" maxlength="120" value="<?php echo esc_attr( $member['address_complement'] ?? '' ); ?>" />
				</label>

				<label class="iadal-field">
					<span><?php esc_html_e( 'Bairro', 'iadal-gestao-ministerial' ); ?></span>
					<input type="text" name="district" maxlength="120" value="<?php echo esc_attr( $member['district'] ?? '' ); ?>" />
				</label>

				<label class="iadal-field">
					<span><?php esc_html_e( 'Cidade', 'iadal-gestao-ministerial' ); ?></span>
					<input type="text" name="city" maxlength="120" value="<?php echo esc_attr( $member['city'] ?? '' ); ?>" />
				</label>

				<label class="iadal-field">
					<span><?php esc_html_e( 'UF', 'iadal-gestao-ministerial' ); ?></span>
					<input type="text" name="state" maxlength="2" value="<?php echo esc_attr( $member['state'] ?? '' ); ?>" />
				</label>
			</section>

			<section class="iadal-card">
				<h2><?php esc_html_e( 'Situacao ministerial', 'iadal-gestao-ministerial' ); ?></h2>

				<label class="iadal-field">
					<span><?php esc_html_e( 'Estado civil', 'iadal-gestao-ministerial' ); ?></span>
					<input type="text" name="marital_status" maxlength="40" value="<?php echo esc_attr( $member['marital_status'] ?? '' ); ?>" />
				</label>

				<label class="iadal-field">
					<span><?php esc_html_e( 'Conjuge', 'iadal-gestao-ministerial' ); ?></span>
					<input type="text" name="spouse_name" maxlength="190" value="<?php echo esc_attr( $member['spouse_name'] ?? '' ); ?>" />
				</label>

				<label class="iadal-field">
					<span><?php esc_html_e( 'Tipo de entrada', 'iadal-gestao-ministerial' ); ?></span>
					<select name="entry_type" data-iadal-entry-type>
						<?php foreach ( $entry_type_options as $value => $label ) : ?>
							<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $member['entry_type'] ?? '', $value ); ?>>
								<?php echo esc_html( $label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<small><?php esc_html_e( 'Entradas por carta sem anexo ficam inativas ate regularizacao.', 'iadal-gestao-ministerial' ); ?></small>
				</label>

				<label class="iadal-field">
					<span><?php esc_html_e( 'Status', 'iadal-gestao-ministerial' ); ?></span>
					<select name="status">
						<?php foreach ( $status_options as $value => $label ) : ?>
							<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $member['status'] ?? '', $value ); ?>>
								<?php echo esc_html( $label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</label>

				<label class="iadal-field" data-iadal-change-letter>
					<span><?php esc_html_e( 'Carta de mudanca', 'iadal-gestao-ministerial' ); ?></span>
					<?php if ( $change_letter_url ) : ?>
						<a href="<?php echo esc_url( $change_letter_url ); ?>" target="_blank" rel="noopener noreferrer">
							<?php esc_html_e( 'Ver carta atual', 'iadal-gestao-ministerial' ); ?>
						</a>
					<?php endif; ?>
					<input type="file" name="change_letter" accept="application/pdf,image/jpeg,image/png,image/webp" />
				</label>

				<label class="iadal-field" data-iadal-acclamation-letter>
					<span><?php esc_html_e( 'Carta de aclamacao', 'iadal-gestao-ministerial' ); ?></span>
					<?php if ( $acclamation_url ) : ?>
						<a href="<?php echo esc_url( $acclamation_url ); ?>" target="_blank" rel="noopener noreferrer">
							<?php esc_html_e( 'Ver carta atual', 'iadal-gestao-ministerial' ); ?>
						</a>
					<?php endif; ?>
					<input type="file" name="acclamation_letter" accept="application/pdf,image/jpeg,image/png,image/webp" />
				</label>

				<label class="iadal-field iadal-field-wide">
					<span><?php esc_html_e( 'Observacoes', 'iadal-gestao-ministerial' ); ?></span>
					<textarea name="notes" rows="5"><?php echo esc_textarea( $member['notes'] ?? '' ); ?></textarea>
				</label>
			</section>
		</div>

		<div class="iadal-form-actions">
			<button type="submit" class="button button-primary button-large">
				<?php esc_html_e( 'Atualizar membro', 'iadal-gestao-ministerial' ); ?>
			</button>
			<a class="button button-large" href="<?php echo esc_url( admin_url( 'admin.php?page=iadal-members' ) ); ?>">
				<?php esc_html_e( 'Voltar para listagem', 'iadal-gestao-ministerial' ); ?>
			</a>
		</div>
	</form>
</div>
