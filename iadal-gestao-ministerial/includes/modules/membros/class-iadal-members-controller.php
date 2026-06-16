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
		add_action( 'admin_post_iadal_members_document_download', array( $this, 'handle_document_download' ) );
	}

	/**
	 * Renders the member listing page.
	 *
	 * @return void
	 */
	public function render_list_page(): void {
		$this->require_capability( 'iadal_view_members' );

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
		$this->require_capability( 'iadal_create_members' );

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
		$this->require_capability( 'iadal_edit_members' );

		$member_id = $this->get_int_from_query( 'member_id', 0 );
		$member    = $this->repository->find( $member_id );

		if ( ! $member ) {
			wp_die( esc_html__( 'Membro nao encontrado.', 'iadal-gestao-ministerial' ) );
		}

		$this->include_template(
			'membros/form-edit.php',
			array(
				'member'              => $member,
				'change_letter'       => $this->repository->latest_document( $member_id, 'change_letter' ),
				'acclamation_letter'  => $this->repository->latest_document( $member_id, 'acclamation_letter' ),
			)
		);
	}

	/**
	 * Renders the birthday report page.
	 *
	 * @return void
	 */
	public function render_birthdays_page(): void {
		$this->require_capability( 'iadal_view_members' );

		$month = $this->get_int_from_query( 'birth_month', (int) gmdate( 'n' ) );
		$month = min( 12, max( 1, $month ) );
		$page     = max( 1, $this->get_int_from_query( 'paged', 1 ) );
		$per_page = 50;
		$offset   = ( $page - 1 ) * $per_page;
		$filters  = array(
			'birth_month' => (string) $month,
			'status'      => 'ativo',
		);

		$this->include_template(
			'membros/birthday-report.php',
			array(
				'members'  => $this->repository->birthdays( $month, $per_page, $offset ),
				'month'    => $month,
				'page'     => $page,
				'per_page' => $per_page,
				'total'    => $this->repository->count( $filters ),
			)
		);
	}

	/**
	 * Handles member creation.
	 *
	 * @return void
	 */
	public function handle_create(): void {
		$this->require_capability( 'iadal_create_members' );
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

		$photo_id = $this->handle_photo_upload();

		if ( is_wp_error( $photo_id ) ) {
			$errors[] = $photo_id->get_error_message();
		} elseif ( $photo_id > 0 ) {
			$data['photo_attachment_id'] = $photo_id;
		}

		$documents = $this->collect_protected_documents();

		if ( is_wp_error( $documents ) ) {
			$errors[] = $documents->get_error_message();
		} else {
			$this->apply_entry_type_rules( $data, null, $documents );
		}

		if ( $errors ) {
			$this->cleanup_uploaded_files( $documents ?? array() );
			$this->cleanup_attachment( $photo_id ?? 0 );
			$this->redirect(
				'iadal-members-create',
				array(
					'iadal_error' => implode( ' ', $errors ),
				)
			);
		}

		$member_id = $this->repository->create( $data );

		if ( ! $member_id ) {
			$this->cleanup_uploaded_files( $documents );
			$this->cleanup_attachment( $photo_id );
			$this->redirect(
				'iadal-members-create',
				array(
					'iadal_error' => __( 'Nao foi possivel cadastrar o membro.', 'iadal-gestao-ministerial' ),
				)
			);
		}

		$documents_saved = $this->save_documents( $member_id, $documents );

		if ( is_wp_error( $documents_saved ) ) {
			$this->cleanup_uploaded_files( $documents );
			$this->cleanup_attachment( $photo_id );
			$this->repository->delete( (int) $member_id );
			$this->redirect(
				'iadal-members-create',
				array(
					'iadal_error' => $documents_saved->get_error_message(),
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
		$this->require_capability( 'iadal_edit_members' );

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

		$photo_id = $this->handle_photo_upload();

		if ( is_wp_error( $photo_id ) ) {
			$errors[] = $photo_id->get_error_message();
		} elseif ( $photo_id > 0 ) {
			$data['photo_attachment_id'] = $photo_id;
		}

		$documents = $this->collect_protected_documents();

		if ( is_wp_error( $documents ) ) {
			$errors[] = $documents->get_error_message();
		} else {
			$this->apply_entry_type_rules( $data, $existing, $documents );
		}

		if ( $errors ) {
			$this->cleanup_uploaded_files( $documents ?? array() );
			$this->cleanup_attachment( $photo_id ?? 0 );
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
			$this->cleanup_uploaded_files( $documents );
			$this->cleanup_attachment( $photo_id );
			$this->redirect(
				'iadal-members-edit',
				array(
					'member_id'    => $member_id,
					'iadal_error' => __( 'Nao foi possivel atualizar o membro.', 'iadal-gestao-ministerial' ),
				)
			);
		}

		$documents_saved = $this->save_documents( $member_id, $documents );

		if ( is_wp_error( $documents_saved ) ) {
			$this->cleanup_uploaded_files( $documents );
			$this->redirect(
				'iadal-members-edit',
				array(
					'member_id'    => $member_id,
					'iadal_error' => $documents_saved->get_error_message(),
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
		$this->require_capability( 'iadal_delete_members' );

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
	 * Streams a protected member document after permission and nonce checks.
	 *
	 * @return void
	 */
	public function handle_document_download(): void {
		$this->require_capability( 'iadal_view_members' );

		$document_id = $this->get_int_from_query( 'document_id', 0 );
		$nonce       = filter_input( INPUT_GET, '_wpnonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		if ( ! $document_id || ! wp_verify_nonce( (string) $nonce, 'iadal_members_document_download_' . $document_id ) ) {
			wp_die( esc_html__( 'Falha de seguranca. Recarregue a pagina e tente novamente.', 'iadal-gestao-ministerial' ) );
		}

		$document = $this->repository->find_document( $document_id );

		if ( ! $document ) {
			wp_die( esc_html__( 'Documento nao encontrado.', 'iadal-gestao-ministerial' ) );
		}

		$file_path      = (string) $document['file_path'];
		$protected_root = realpath( $this->protected_upload_root() );
		$real_file      = realpath( $file_path );

		if ( ! $protected_root || ! $real_file || 0 !== strpos( $real_file, $protected_root ) || ! is_readable( $real_file ) ) {
			wp_die( esc_html__( 'Arquivo protegido indisponivel.', 'iadal-gestao-ministerial' ) );
		}

		nocache_headers();
		header( 'Content-Type: ' . (string) $document['mime_type'] );
		header( 'Content-Length: ' . (string) filesize( $real_file ) );
		header( 'Content-Disposition: attachment; filename="' . basename( (string) $document['file_name'] ) . '"' );
		readfile( $real_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile
		exit;
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
			'ativo'              => __( 'Ativo', 'iadal-gestao-ministerial' ),
			'inativo'            => __( 'Inativo', 'iadal-gestao-ministerial' ),
			'em_mudanca'         => __( 'Em mudanca', 'iadal-gestao-ministerial' ),
			'pendente_documento' => __( 'Pendente de documento', 'iadal-gestao-ministerial' ),
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
	 * Requires one module capability.
	 *
	 * @param string $capability Required capability.
	 * @return void
	 */
	private function require_capability( string $capability ): void {
		if ( ! current_user_can( $capability ) && ! current_user_can( 'iadal_manage_members' ) ) {
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
		$gender     = isset( $post['gender'] ) ? sanitize_text_field( $post['gender'] ) : '';

		if ( ! array_key_exists( $status, self::status_options() ) ) {
			$status = 'ativo';
		}

		if ( ! array_key_exists( $entry_type, self::entry_type_options() ) ) {
			$entry_type = 'local';
		}

		if ( ! in_array( $gender, array( '', 'Masculino', 'Feminino' ), true ) ) {
			$gender = '';
		}

		return array(
			'full_name'      => isset( $post['full_name'] ) ? sanitize_text_field( $post['full_name'] ) : '',
			'cpf'            => $this->digits_only( isset( $post['cpf'] ) ? (string) $post['cpf'] : '' ),
			'phone'          => isset( $post['phone'] ) ? sanitize_text_field( $post['phone'] ) : '',
			'email'          => isset( $post['email'] ) ? sanitize_email( $post['email'] ) : '',
			'birth_date'     => $this->sanitize_date( isset( $post['birth_date'] ) ? (string) $post['birth_date'] : '' ),
			'gender'         => $gender,
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
		} elseif ( ! $this->is_valid_cpf( $data['cpf'] ) ) {
			$errors[] = __( 'Informe um CPF valido.', 'iadal-gestao-ministerial' );
		}

		if ( ! empty( $data['email'] ) && ! is_email( $data['email'] ) ) {
			$errors[] = __( 'Informe um e-mail valido.', 'iadal-gestao-ministerial' );
		}

		if ( ! empty( $data['birth_date'] ) && ! $this->is_valid_date( $data['birth_date'] ) ) {
			$errors[] = __( 'Informe uma data de nascimento valida.', 'iadal-gestao-ministerial' );
		}

		return $errors;
	}

	/**
	 * Handles photo upload through WordPress media APIs.
	 *
	 * @return int|WP_Error
	 */
	private function handle_photo_upload() {
		$field_name = 'photo';

		if ( ! $this->has_uploaded_file( $field_name ) ) {
			return 0;
		}

		if ( ! current_user_can( 'upload_files' ) ) {
			return new WP_Error( 'iadal_upload_permission', __( 'Usuario sem permissao para enviar arquivos.', 'iadal-gestao-ministerial' ) );
		}

		$max_size = 2 * MB_IN_BYTES;

		if ( (int) $_FILES[ $field_name ]['size'] > $max_size ) {
			return new WP_Error( 'iadal_upload_size', __( 'A foto deve ter no maximo 2 MB.', 'iadal-gestao-ministerial' ) );
		}

		$allowed_mimes = array(
			'jpg'  => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'png'  => 'image/png',
			'webp' => 'image/webp',
		);
		$file_name     = sanitize_file_name( wp_unslash( $_FILES[ $field_name ]['name'] ) );
		$tmp_name      = isset( $_FILES[ $field_name ]['tmp_name'] ) ? (string) $_FILES[ $field_name ]['tmp_name'] : '';
		$file_type     = wp_check_filetype_and_ext( $tmp_name, $file_name, $allowed_mimes );

		if ( empty( $file_type['ext'] ) || empty( $file_type['type'] ) ) {
			return new WP_Error( 'iadal_upload_type', __( 'Tipo de imagem nao permitido.', 'iadal-gestao-ministerial' ) );
		}

		if ( ! wp_get_image_mime( $tmp_name ) ) {
			return new WP_Error( 'iadal_upload_image', __( 'O arquivo enviado nao e uma imagem valida.', 'iadal-gestao-ministerial' ) );
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
	 * Collects protected document uploads for later database linking.
	 *
	 * @return array<int, array<string, mixed>>|WP_Error
	 */
	private function collect_protected_documents() {
		$documents = array();
		$fields    = array(
			'change_letter'       => __( 'Carta de mudanca', 'iadal-gestao-ministerial' ),
			'acclamation_letter'  => __( 'Carta de aclamacao', 'iadal-gestao-ministerial' ),
		);

		foreach ( $fields as $field_name => $title ) {
			if ( ! $this->has_uploaded_file( $field_name ) ) {
				continue;
			}

			$document = $this->handle_protected_document_upload( $field_name, $title );

			if ( is_wp_error( $document ) ) {
				$this->cleanup_uploaded_files( $documents );
				return $document;
			}

			$documents[] = $document;
		}

		return $documents;
	}

	/**
	 * Saves uploaded protected documents in the database.
	 *
	 * @param int                                 $member_id Member ID.
	 * @param array<int, array<string, mixed>>    $documents Uploaded documents.
	 * @return true|WP_Error
	 */
	private function save_documents( int $member_id, array $documents ) {
		foreach ( $documents as $document ) {
			$document_id = $this->repository->create_document( $member_id, $document );

			if ( ! $document_id ) {
				return new WP_Error( 'iadal_document_database', __( 'Nao foi possivel registrar o documento protegido.', 'iadal-gestao-ministerial' ) );
			}
		}

		return true;
	}

	/**
	 * Handles a protected document upload outside the public uploads directory.
	 *
	 * @param string $field_name Upload field name.
	 * @param string $title Document title.
	 * @return array<string, mixed>|WP_Error
	 */
	private function handle_protected_document_upload( string $field_name, string $title ) {
		$max_size = 10 * MB_IN_BYTES;

		if ( (int) $_FILES[ $field_name ]['size'] > $max_size ) {
			return new WP_Error( 'iadal_document_size', __( 'O documento deve ter no maximo 10 MB.', 'iadal-gestao-ministerial' ) );
		}

		$allowed_mimes = array(
			'pdf'  => 'application/pdf',
			'jpg'  => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'png'  => 'image/png',
			'webp' => 'image/webp',
		);
		$file_name     = sanitize_file_name( wp_unslash( $_FILES[ $field_name ]['name'] ) );
		$tmp_name      = isset( $_FILES[ $field_name ]['tmp_name'] ) ? (string) $_FILES[ $field_name ]['tmp_name'] : '';
		$file_type     = wp_check_filetype_and_ext( $tmp_name, $file_name, $allowed_mimes );

		if ( empty( $file_type['ext'] ) || empty( $file_type['type'] ) ) {
			return new WP_Error( 'iadal_document_type', __( 'Tipo de documento nao permitido.', 'iadal-gestao-ministerial' ) );
		}

		if ( 0 === strpos( (string) $file_type['type'], 'image/' ) && ! wp_get_image_mime( $tmp_name ) ) {
			return new WP_Error( 'iadal_document_image', __( 'A imagem do documento nao e valida.', 'iadal-gestao-ministerial' ) );
		}

		$target_dir = $this->protected_upload_root() . '/' . gmdate( 'Y/m' );

		if ( ! wp_mkdir_p( $target_dir ) ) {
			return new WP_Error( 'iadal_document_directory', __( 'Nao foi possivel criar o diretorio protegido.', 'iadal-gestao-ministerial' ) );
		}

		$this->protect_directory( $this->protected_upload_root() );

		$unique_name = wp_unique_filename( $target_dir, $file_name );
		$target_path = trailingslashit( $target_dir ) . $unique_name;

		if ( ! move_uploaded_file( $tmp_name, $target_path ) ) {
			return new WP_Error( 'iadal_document_move', __( 'Nao foi possivel salvar o documento protegido.', 'iadal-gestao-ministerial' ) );
		}

		return array(
			'document_type' => $field_name,
			'title'         => $title,
			'file_name'     => $unique_name,
			'file_path'     => $target_path,
			'mime_type'     => (string) $file_type['type'],
			'file_size'     => (int) filesize( $target_path ),
		);
	}

	/**
	 * Applies business rules for entries by letter.
	 *
	 * @param array<string, mixed>           $data Member data.
	 * @param array<string, mixed>|null      $existing Existing member data.
	 * @param array<int, array<string,mixed>> $documents Uploaded documents.
	 * @return void
	 */
	private function apply_entry_type_rules( array &$data, ?array $existing = null, array $documents = array() ): void {
		$member_id               = $existing && ! empty( $existing['id'] ) ? (int) $existing['id'] : 0;
		$has_change_letter       = $this->has_document_in_payload( $documents, 'change_letter' );
		$has_acclamation_letter  = $this->has_document_in_payload( $documents, 'acclamation_letter' );

		if ( $member_id > 0 ) {
			$has_change_letter      = $has_change_letter || $this->repository->member_has_document_type( $member_id, 'change_letter' );
			$has_acclamation_letter = $has_acclamation_letter || $this->repository->member_has_document_type( $member_id, 'acclamation_letter' );
		}

		if ( 'mudanca' === $data['entry_type'] && ! $has_change_letter ) {
			$data['status'] = 'pendente_documento';
		}

		if ( 'aclamacao' === $data['entry_type'] && ! $has_acclamation_letter ) {
			$data['status'] = 'pendente_documento';
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
		$message = array();

		if ( isset( $args['iadal_notice'] ) ) {
			$message['notice'] = (string) $args['iadal_notice'];
			unset( $args['iadal_notice'] );
		}

		if ( isset( $args['iadal_error'] ) ) {
			$message['error'] = (string) $args['iadal_error'];
			unset( $args['iadal_error'] );
		}

		if ( $message ) {
			$message_key              = get_current_user_id() . '_' . wp_generate_uuid4();
			$args['iadal_message']    = $message_key;
			$message['created_at']    = time();
			$message['current_user']  = get_current_user_id();

			set_transient( 'iadal_message_' . $message_key, $message, 5 * MINUTE_IN_SECONDS );
		}

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
	 * Checks if a file field contains an uploaded file.
	 *
	 * @param string $field_name Upload field name.
	 * @return bool
	 */
	private function has_uploaded_file( string $field_name ): bool {
		return isset( $_FILES[ $field_name ]['name'], $_FILES[ $field_name ]['tmp_name'] )
			&& '' !== $_FILES[ $field_name ]['name']
			&& is_uploaded_file( (string) $_FILES[ $field_name ]['tmp_name'] );
	}

	/**
	 * Returns the protected upload root for sensitive documents.
	 *
	 * @return string
	 */
	private function protected_upload_root(): string {
		return trailingslashit( WP_CONTENT_DIR ) . 'iadal-protected/member-documents';
	}

	/**
	 * Adds basic web-server protections to the protected directory.
	 *
	 * @param string $root Protected directory root.
	 * @return void
	 */
	private function protect_directory( string $root ): void {
		if ( ! is_dir( $root ) ) {
			return;
		}

		$htaccess = trailingslashit( $root ) . '.htaccess';
		$index    = trailingslashit( $root ) . 'index.php';

		if ( ! file_exists( $htaccess ) ) {
			file_put_contents( $htaccess, "Deny from all\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		}

		if ( ! file_exists( $index ) ) {
			file_put_contents( $index, "<?php\n// Silence is golden.\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		}
	}

	/**
	 * Removes uploaded protected files when a transaction-like flow fails.
	 *
	 * @param mixed $documents Uploaded document payload.
	 * @return void
	 */
	private function cleanup_uploaded_files( $documents ): void {
		if ( is_wp_error( $documents ) || ! is_array( $documents ) ) {
			return;
		}

		foreach ( $documents as $document ) {
			if ( empty( $document['file_path'] ) || ! is_string( $document['file_path'] ) ) {
				continue;
			}

			$file_path = $document['file_path'];

			if ( is_file( $file_path ) ) {
				wp_delete_file( $file_path );
			}
		}
	}

	/**
	 * Removes an attachment when a database operation fails.
	 *
	 * @param mixed $attachment_id Attachment ID.
	 * @return void
	 */
	private function cleanup_attachment( $attachment_id ): void {
		if ( is_wp_error( $attachment_id ) || empty( $attachment_id ) ) {
			return;
		}

		wp_delete_attachment( (int) $attachment_id, true );
	}

	/**
	 * Checks whether an uploaded document payload has a type.
	 *
	 * @param array<int, array<string, mixed>> $documents Uploaded documents.
	 * @param string                           $document_type Document type.
	 * @return bool
	 */
	private function has_document_in_payload( array $documents, string $document_type ): bool {
		foreach ( $documents as $document ) {
			if ( isset( $document['document_type'] ) && $document_type === $document['document_type'] ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Validates CPF check digits.
	 *
	 * @param string $cpf CPF digits.
	 * @return bool
	 */
	private function is_valid_cpf( string $cpf ): bool {
		$cpf = $this->digits_only( $cpf );

		if ( 11 !== strlen( $cpf ) || preg_match( '/^(\d)\1{10}$/', $cpf ) ) {
			return false;
		}

		for ( $digit_position = 9; $digit_position < 11; $digit_position++ ) {
			$sum = 0;

			for ( $index = 0; $index < $digit_position; $index++ ) {
				$sum += (int) $cpf[ $index ] * ( ( $digit_position + 1 ) - $index );
			}

			$calculated = ( ( 10 * $sum ) % 11 ) % 10;

			if ( (int) $cpf[ $digit_position ] !== $calculated ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Validates a date in Y-m-d format.
	 *
	 * @param string $date Date value.
	 * @return bool
	 */
	private function is_valid_date( string $date ): bool {
		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
			return false;
		}

		$parts = array_map( 'intval', explode( '-', $date ) );

		return checkdate( $parts[1], $parts[2], $parts[0] );
	}

	/**
	 * Sanitizes a date in Y-m-d format.
	 *
	 * @param string $date Date value.
	 * @return string
	 */
	private function sanitize_date( string $date ): string {
		$date = sanitize_text_field( $date );

		if ( '' === $date || ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
			return '';
		}

		return $date;
	}
}
