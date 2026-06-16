<?php
/**
 * Members admin controller.
 *
 * @package IADAL_Gestao_Ministerial
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles admin screens and actions for members.
 */
class IADAL_Members_Controller {

	/**
	 * Members repository.
	 *
	 * @var IADAL_Members_Repository
	 */
	private IADAL_Members_Repository $repository;

	/**
	 * Constructor.
	 *
	 * @param IADAL_Members_Repository $repository Members repository.
	 */
	public function __construct( IADAL_Members_Repository $repository ) {
		$this->repository = $repository;
	}

	/**
	 * Registers admin-post hooks.
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		add_action( 'admin_post_iadal_members_create', array( $this, 'handle_create' ) );
		add_action( 'admin_post_iadal_members_update', array( $this, 'handle_update' ) );
		add_action( 'admin_post_iadal_members_delete', array( $this, 'handle_delete' ) );
	}

	/**
	 * Renders the member listing page.
	 *
	 * @return void
	 */
	public function render_list_page(): void {
		$this->require_admin_access();

		$filters  = $this->get_filters();
		$page     = max( 1, $this->get_int_from_query( 'paged', 1 ) );
		$per_page = 20;
		$offset   = ( $page - 1 ) * $per_page;

		$query_args = array_merge(
			$filters,
			array(
				'limit'  => $per_page,
				'offset' => $offset,
			)
		);

		$members = $this->repository->all( $query_args );
		$total   = $this->repository->count( $filters );

		$this->include_template(
			'membros/list.php',
			array(
				'members'  => $members,
				'filters'  => $filters,
				'page'     => $page,
				'per_page' => $per_page,
				'total'    => $total,
			)
		);
	}

	/**
	 * Renders the create member page.
	 *
	 * @return void
	 */
	public function render_create_page(): void {
		$this->require_admin_access();

		$this->include_template(
			'membros/form-create.php',
			array(
				'member' => array(),
			)
		);
	}

	/**
	 * Renders the edit member page.
	 *
	 * @return void
	 */
	public function render_edit_page(): void {
		$this->require_admin_access();

		$member_id = $this->get_int_from_query( 'member_id', 0 );
		$member    = $this->repository->find( $member_id );

		if ( ! $member ) {
			wp_die( esc_html__( 'Membro nao encontrado.', 'iadal-gestao-ministerial' ) );
		}

		$this->include_template(
			'membros/form-edit.php',
			array(
				'member' => $member,
			)
		);
	}

	/**
	 * Renders the birthday report page.
	 *
	 * @return void
	 */
	public function render_birthdays_page(): void {
		$this->require_admin_access();

		$month = $this->get_int_from_query( 'birth_month', (int) gmdate( 'n' ) );
		$month = min( 12, max( 1, $month ) );

		$this->include_template(
			'membros/birthday-report.php',
			array(
				'members' => $this->repository->birthdays( $month ),
				'month'   => $month,
			)
		);
	}

	/**
	 * Handles member creation.
	 *
	 * @return void
	 */
	public function handle_create(): void {
		$this->require_admin_access();
		$this->verify_nonce( 'iadal_members_create', 'iadal_members_nonce' );

		$data   = $this->sanitize_member_data();
		$errors = $this->validate_member_data( $data );

		if ( $this->repository->cpf_exists( $data['cpf'] ) ) {
			$errors[] = __( 'Ja existe um membro cadastrado com este CPF.', 'iadal-gestao-ministerial' );
		}

		if ( $errors ) {
			$this->redirect(
				'iadal-members-create',
				array(
					'iadal_error' => implode( ' ', $errors ),
				)
			);
		}

		$upload_result = $this->attach_uploads_to_data( $data );

		if ( is_wp_error( $upload_result ) ) {
			$errors[] = $upload_result->get_error_message();
		}

		$this->apply_entry_type_rules( $data );

		if ( $errors ) {
			$this->redirect(
				'iadal-members-create',
				array(
					'iadal_error' => implode( ' ', $errors ),
				)
			);
		}

		$member_id = $this->repository->create( $data );

		if ( ! $member_id ) {
			$this->redirect(
				'iadal-members-create',
				array(
					'iadal_error' => __( 'Nao foi possivel cadastrar o membro.', 'iadal-gestao-ministerial' ),
				)
			);
		}

		$this->redirect(
			'iadal-members',
			array(
				'iadal_notice' => __( 'Membro cadastrado com sucesso.', 'iadal-gestao-ministerial' ),
			)
		);
	}

	/**
	 * Handles member update.
	 *
	 * @return void
	 */
	public function handle_update(): void {
		$this->require_admin_access();

		$member_id = $this->get_int_from_post( 'member_id', 0 );
		$this->verify_nonce( 'iadal_members_update_' . $member_id, 'iadal_members_nonce' );

		$existing = $this->repository->find( $member_id );

		if ( ! $existing ) {
			wp_die( esc_html__( 'Membro nao encontrado.', 'iadal-gestao-ministerial' ) );
		}

		$data   = $this->sanitize_member_data();
		$errors = $this->validate_member_data( $data );

		if ( $this->repository->cpf_exists( $data['cpf'], $member_id ) ) {
			$errors[] = __( 'Ja existe outro membro cadastrado com este CPF.', 'iadal-gestao-ministerial' );
		}

		if ( $errors ) {
			$this->redirect(
				'iadal-members-edit',
				array(
					'member_id'    => $member_id,
					'iadal_error' => implode( ' ', $errors ),
				)
			);
		}

		$upload_result = $this->attach_uploads_to_data( $data );

		if ( is_wp_error( $upload_result ) ) {
			$errors[] = $upload_result->get_error_message();
		}

		$this->apply_entry_type_rules( $data, $existing );

		if ( $errors ) {
			$this->redirect(
				'iadal-members-edit',
				array(
					'member_id'    => $member_id,
					'iadal_error' => implode( ' ', $errors ),
				)
			);
		}

		$updated = $this->repository->update( $member_id, $data );

		if ( ! $updated ) {
			$this->redirect(
				'iadal-members-edit',
				array(
					'member_id'    => $member_id,
					'iadal_error' => __( 'Nao foi possivel atualizar o membro.', 'iadal-gestao-ministerial' ),
				)
			);
		}

		$this->redirect(
			'iadal-members',
			array(
				'iadal_notice' => __( 'Membro atualizado com sucesso.', 'iadal-gestao-ministerial' ),
			)
		);
	}

	/**
	 * Handles member soft deletion.
	 *
	 * @return void
	 */
	public function handle_delete(): void {
		$this->require_admin_access();

		$member_id = $this->get_int_from_post( 'member_id', 0 );
		$this->verify_nonce( 'iadal_members_delete_' . $member_id, 'iadal_members_nonce' );

		$deleted = $this->repository->delete( $member_id );

		if ( ! $deleted ) {
			$this->redirect(
				'iadal-members',
				array(
					'iadal_error' => __( 'Nao foi possivel excluir o membro.', 'iadal-gestao-ministerial' ),
				)
			);
		}

		$this->redirect(
			'iadal-members',
			array(
				'iadal_notice' => __( 'Membro enviado para exclusao logica.', 'iadal-gestao-ministerial' ),
			)
		);
	}

	/**
	 * Formats a CPF for display.
	 *
	 * @param string $cpf CPF with digits only.
	 * @return string
	 */
	public static function format_cpf( string $cpf ): string {
		$digits = preg_replace( '/\D+/', '', $cpf );

		if ( 11 !== strlen( $digits ) ) {
			return $cpf;
		}

		return substr( $digits, 0, 3 ) . '.' . substr( $digits, 3, 3 ) . '.' . substr( $digits, 6, 3 ) . '-' . substr( $digits, 9, 2 );
	}

	/**
	 * Formats a date for display.
	 *
	 * @param string|null $date Date in Y-m-d format.
	 * @return string
	 */
	public static function format_date( ?string $date ): string {
		if ( empty( $date ) || '0000-00-00' === $date ) {
			return '-';
		}

		$timestamp = strtotime( $date );

		if ( false === $timestamp ) {
			return '-';
		}

		return date_i18n( 'd/m/Y', $timestamp );
	}

	/**
	 * Returns status options.
	 *
	 * @return array<string, string>
	 */
	public static function status_options(): array {
		return array(
			'ativo'   => __( 'Ativo', 'iadal-gestao-ministerial' ),
			'inativo' => __( 'Inativo', 'iadal-gestao-ministerial' ),
		);
	}

	/**
	 * Returns entry type options.
	 *
	 * @return array<string, string>
	 */
	public static function entry_type_options(): array {
		return array(
			'local'     => __( 'Membro local', 'iadal-gestao-ministerial' ),
			'mudanca'   => __( 'Carta de mudanca', 'iadal-gestao-ministerial' ),
			'aclamacao' => __( 'Carta de aclamacao', 'iadal-gestao-ministerial' ),
		);
	}

	/**
	 * Requires a WordPress administrator for the initial module version.
	 *
	 * @return void
	 */
	private function require_admin_access(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Voce nao tem permissao para acessar esta area.', 'iadal-gestao-ministerial' ) );
		}
	}

	/**
	 * Verifies a nonce field.
	 *
	 * @param string $action Nonce action.
	 * @param string $field Nonce field name.
	 * @return void
	 */
	private function verify_nonce( string $action, string $field ): void {
		$nonce = isset( $_POST[ $field ] ) ? sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, $action ) ) {
			wp_die( esc_html__( 'Falha de seguranca. Recarregue a pagina e tente novamente.', 'iadal-gestao-ministerial' ) );
		}
	}

	/**
	 * Gets list filters from query string.
	 *
	 * @return array<string, string>
	 */
	private function get_filters(): array {
		$search      = filter_input( INPUT_GET, 's', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$status      = filter_input( INPUT_GET, 'status', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$entry_type  = filter_input( INPUT_GET, 'entry_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$birth_month = filter_input( INPUT_GET, 'birth_month', FILTER_SANITIZE_NUMBER_INT );

		$status_options     = array_keys( self::status_options() );
		$entry_type_options = array_keys( self::entry_type_options() );

		$status     = in_array( $status, $status_options, true ) ? $status : '';
		$entry_type = in_array( $entry_type, $entry_type_options, true ) ? $entry_type : '';

		if ( '' !== $birth_month && null !== $birth_month ) {
			$birth_month = (string) min( 12, max( 1, (int) $birth_month ) );
		} else {
			$birth_month = '';
		}

		return array(
			'search'      => sanitize_text_field( (string) $search ),
			'status'      => $status,
			'entry_type'  => $entry_type,
			'birth_month' => $birth_month,
		);
	}

	/**
	 * Sanitizes member data from POST.
	 *
	 * @return array<string, mixed>
	 */
	private function sanitize_member_data(): array {
		$post = wp_unslash( $_POST );

		$status     = isset( $post['status'] ) ? sanitize_key( $post['status'] ) : 'ativo';
		$entry_type = isset( $post['entry_type'] ) ? sanitize_key( $post['entry_type'] ) : 'local';

		if ( ! array_key_exists( $status, self::status_options() ) ) {
			$status = 'ativo';
		}

		if ( ! array_key_exists( $entry_type, self::entry_type_options() ) ) {
			$entry_type = 'local';
		}

		return array(
			'full_name'      => isset( $post['full_name'] ) ? sanitize_text_field( $post['full_name'] ) : '',
			'cpf'            => $this->digits_only( isset( $post['cpf'] ) ? (string) $post['cpf'] : '' ),
			'phone'          => isset( $post['phone'] ) ? sanitize_text_field( $post['phone'] ) : '',
			'email'          => isset( $post['email'] ) ? sanitize_email( $post['email'] ) : '',
			'birth_date'     => $this->sanitize_date( isset( $post['birth_date'] ) ? (string) $post['birth_date'] : '' ),
			'gender'         => isset( $post['gender'] ) ? sanitize_text_field( $post['gender'] ) : '',
			'zip_code'       => isset( $post['zip_code'] ) ? sanitize_text_field( $post['zip_code'] ) : '',
			'address'        => isset( $post['address'] ) ? sanitize_text_field( $post['address'] ) : '',
			'address_number' => isset( $post['address_number'] ) ? sanitize_text_field( $post['address_number'] ) : '',
			'address_complement' => isset( $post['address_complement'] ) ? sanitize_text_field( $post['address_complement'] ) : '',
			'district'       => isset( $post['district'] ) ? sanitize_text_field( $post['district'] ) : '',
			'city'           => isset( $post['city'] ) ? sanitize_text_field( $post['city'] ) : '',
			'state'          => isset( $post['state'] ) ? strtoupper( substr( sanitize_text_field( $post['state'] ), 0, 2 ) ) : '',
			'marital_status' => isset( $post['marital_status'] ) ? sanitize_text_field( $post['marital_status'] ) : '',
			'spouse_name'    => isset( $post['spouse_name'] ) ? sanitize_text_field( $post['spouse_name'] ) : '',
			'entry_type'     => $entry_type,
			'status'         => $status,
			'notes'          => isset( $post['notes'] ) ? sanitize_textarea_field( $post['notes'] ) : '',
		);
	}

	/**
	 * Validates required member fields.
	 *
	 * @param array<string, mixed> $data Sanitized member data.
	 * @return array<int, string>
	 */
	private function validate_member_data( array $data ): array {
		$errors = array();

		if ( '' === $data['full_name'] ) {
			$errors[] = __( 'Informe o nome completo.', 'iadal-gestao-ministerial' );
		}

		if ( '' === $data['cpf'] ) {
			$errors[] = __( 'Informe o CPF.', 'iadal-gestao-ministerial' );
		} elseif ( 11 !== strlen( $data['cpf'] ) ) {
			$errors[] = __( 'O CPF deve conter 11 digitos.', 'iadal-gestao-ministerial' );
		}

		if ( ! empty( $data['email'] ) && ! is_email( $data['email'] ) ) {
			$errors[] = __( 'Informe um e-mail valido.', 'iadal-gestao-ministerial' );
		}

		return $errors;
	}

	/**
	 * Uploads files and attaches attachment IDs to member data.
	 *
	 * @param array<string, mixed> $data Member data.
	 * @return true|WP_Error
	 */
	private function attach_uploads_to_data( array &$data ) {
		$photo_id = $this->handle_upload(
			'photo',
			array(
				'jpg'  => 'image/jpeg',
				'jpeg' => 'image/jpeg',
				'png'  => 'image/png',
				'webp' => 'image/webp',
			)
		);

		if ( is_wp_error( $photo_id ) ) {
			return $photo_id;
		}

		if ( $photo_id > 0 ) {
			$data['photo_attachment_id'] = $photo_id;
		}

		$document_mimes = array(
			'pdf'  => 'application/pdf',
			'jpg'  => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'png'  => 'image/png',
			'webp' => 'image/webp',
		);

		$change_letter_id = $this->handle_upload( 'change_letter', $document_mimes );

		if ( is_wp_error( $change_letter_id ) ) {
			return $change_letter_id;
		}

		if ( $change_letter_id > 0 ) {
			$data['change_letter_attachment_id'] = $change_letter_id;
		}

		$acclamation_letter_id = $this->handle_upload( 'acclamation_letter', $document_mimes );

		if ( is_wp_error( $acclamation_letter_id ) ) {
			return $acclamation_letter_id;
		}

		if ( $acclamation_letter_id > 0 ) {
			$data['acclamation_letter_attachment_id'] = $acclamation_letter_id;
		}

		return true;
	}

	/**
	 * Handles one secure upload through WordPress media APIs.
	 *
	 * @param string               $field_name Upload field name.
	 * @param array<string,string> $allowed_mimes Allowed mime types.
	 * @return int|WP_Error
	 */
	private function handle_upload( string $field_name, array $allowed_mimes ) {
		if ( empty( $_FILES[ $field_name ]['name'] ) ) {
			return 0;
		}

		if ( ! current_user_can( 'upload_files' ) ) {
			return new WP_Error( 'iadal_upload_permission', __( 'Usuario sem permissao para enviar arquivos.', 'iadal-gestao-ministerial' ) );
		}

		$file_name = sanitize_file_name( wp_unslash( $_FILES[ $field_name ]['name'] ) );
		$tmp_name  = isset( $_FILES[ $field_name ]['tmp_name'] ) ? sanitize_text_field( wp_unslash( $_FILES[ $field_name ]['tmp_name'] ) ) : '';
		$file_type = wp_check_filetype_and_ext( $tmp_name, $file_name, $allowed_mimes );

		if ( empty( $file_type['ext'] ) || empty( $file_type['type'] ) ) {
			return new WP_Error( 'iadal_upload_type', __( 'Tipo de arquivo nao permitido.', 'iadal-gestao-ministerial' ) );
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		$attachment_id = media_handle_upload( $field_name, 0 );

		if ( is_wp_error( $attachment_id ) ) {
			return $attachment_id;
		}

		return (int) $attachment_id;
	}

	/**
	 * Applies business rules for entries by letter.
	 *
	 * @param array<string, mixed>      $data Member data.
	 * @param array<string, mixed>|null $existing Existing member data.
	 * @return void
	 */
	private function apply_entry_type_rules( array &$data, ?array $existing = null ): void {
		$has_change_letter = ! empty( $data['change_letter_attachment_id'] ) || ( $existing && ! empty( $existing['change_letter_attachment_id'] ) );
		$has_acclamation_letter = ! empty( $data['acclamation_letter_attachment_id'] ) || ( $existing && ! empty( $existing['acclamation_letter_attachment_id'] ) );

		if ( 'mudanca' === $data['entry_type'] && ! $has_change_letter ) {
			$data['status'] = 'inativo';
		}

		if ( 'aclamacao' === $data['entry_type'] && ! $has_acclamation_letter ) {
			$data['status'] = 'inativo';
		}
	}

	/**
	 * Includes a template with local variables.
	 *
	 * @param string               $template Template path relative to templates directory.
	 * @param array<string, mixed> $variables Variables available in the template.
	 * @return void
	 */
	private function include_template( string $template, array $variables = array() ): void {
		foreach ( $variables as $key => $value ) {
			${$key} = $value;
		}

		include IADAL_GESTAO_DIR . 'templates/' . $template;
	}

	/**
	 * Redirects to a module admin page.
	 *
	 * @param string               $page Page slug.
	 * @param array<string, mixed> $args Query arguments.
	 * @return never
	 */
	private function redirect( string $page, array $args = array() ) {
		$url = add_query_arg( array_merge( array( 'page' => $page ), $args ), admin_url( 'admin.php' ) );

		wp_safe_redirect( $url );
		exit;
	}

	/**
	 * Gets an integer query value.
	 *
	 * @param string $key Query key.
	 * @param int    $default Default value.
	 * @return int
	 */
	private function get_int_from_query( string $key, int $default ): int {
		$value = filter_input( INPUT_GET, $key, FILTER_SANITIZE_NUMBER_INT );

		if ( null === $value || false === $value || '' === $value ) {
			return $default;
		}

		return (int) $value;
	}

	/**
	 * Gets an integer POST value.
	 *
	 * @param string $key POST key.
	 * @param int    $default Default value.
	 * @return int
	 */
	private function get_int_from_post( string $key, int $default ): int {
		$post = wp_unslash( $_POST );

		if ( ! isset( $post[ $key ] ) ) {
			return $default;
		}

		return (int) $post[ $key ];
	}

	/**
	 * Keeps only digits.
	 *
	 * @param string $value Original value.
	 * @return string
	 */
	private function digits_only( string $value ): string {
		return preg_replace( '/\D+/', '', $value );
	}

	/**
	 * Sanitizes a date in Y-m-d format.
	 *
	 * @param string $date Date value.
	 * @return string
	 */
	private function sanitize_date( string $date ): string {
		$date = sanitize_text_field( $date );

		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
			return '';
		}

		return $date;
	}
}
