<?php
/**
 * Congregations admin controller.
 *
 * @package IADAL_Gestao_Ministerial
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles admin screens and actions for congregations.
 */
class IADAL_Congregations_Controller {

	/**
	 * Congregations repository.
	 *
	 * @var IADAL_Congregations_Repository
	 */
	private IADAL_Congregations_Repository $repository;

	/**
	 * Constructor.
	 *
	 * @param IADAL_Congregations_Repository $repository Congregations repository.
	 */
	public function __construct( IADAL_Congregations_Repository $repository ) {
		$this->repository = $repository;
	}

	/**
	 * Registers admin-post hooks.
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		add_action( 'admin_post_iadal_congregations_create', array( $this, 'handle_create' ) );
		add_action( 'admin_post_iadal_congregations_update', array( $this, 'handle_update' ) );
		add_action( 'admin_post_iadal_congregations_delete', array( $this, 'handle_delete' ) );
		add_action( 'admin_post_iadal_congregations_status', array( $this, 'handle_status' ) );
		add_action( 'admin_post_iadal_congregations_reset_password', array( $this, 'handle_reset_password' ) );
	}

	/**
	 * Renders the congregation list.
	 *
	 * @return void
	 */
	public function render_list_page(): void {
		$this->require_capability( 'iadal_view_congregations' );

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

		$this->include_template(
			'congregacoes/list.php',
			array(
				'congregations' => $this->repository->all( $query_args ),
				'filters'       => $filters,
				'page'          => $page,
				'per_page'      => $per_page,
				'total'         => $this->repository->count( $filters ),
			)
		);
	}

	/**
	 * Renders the create congregation page.
	 *
	 * @return void
	 */
	public function render_create_page(): void {
		$this->require_capability( 'iadal_create_congregations' );

		$this->include_template(
			'congregacoes/form-create.php',
			array(
				'congregation' => array(),
			)
		);
	}

	/**
	 * Renders the edit congregation page.
	 *
	 * @return void
	 */
	public function render_edit_page(): void {
		$this->require_capability( 'iadal_edit_congregations' );

		$congregation_id = $this->get_int_from_query( 'congregation_id', 0 );
		$congregation    = $this->repository->find( $congregation_id );

		if ( ! $congregation ) {
			wp_die( esc_html__( 'Congregacao nao encontrada.', 'iadal-gestao-ministerial' ) );
		}

		$this->include_template(
			'congregacoes/form-edit.php',
			array(
				'congregation' => $congregation,
			)
		);
	}

	/**
	 * Renders the credentials page.
	 *
	 * @return void
	 */
	public function render_credentials_page(): void {
		$this->require_capability( 'iadal_manage_congregation_credentials' );

		$congregation_id = $this->get_int_from_query( 'congregation_id', 0 );
		$congregation    = $this->repository->find( $congregation_id );

		if ( ! $congregation ) {
			wp_die( esc_html__( 'Congregacao nao encontrada.', 'iadal-gestao-ministerial' ) );
		}

		$credential_key = filter_input( INPUT_GET, 'credential_key', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$credentials    = $this->get_credentials_transient( (string) $credential_key, $congregation_id );

		$this->include_template(
			'congregacoes/credentials.php',
			array(
				'congregation' => $congregation,
				'users'        => $this->repository->users_for_church( $congregation_id ),
				'credentials'  => $credentials,
			)
		);
	}

	/**
	 * Handles congregation creation.
	 *
	 * @return void
	 */
	public function handle_create(): void {
		$this->require_capability( 'iadal_create_congregations' );
		$this->verify_nonce( 'iadal_congregations_create', 'iadal_congregations_nonce' );

		$data   = $this->sanitize_congregation_data();
		$errors = $this->validate_congregation_data( $data );

		if ( $errors ) {
			$this->redirect(
				'iadal-congregations-create',
				array(
					'iadal_error' => implode( ' ', $errors ),
				)
			);
		}

		$congregation_id = $this->repository->create( $data );

		if ( ! $congregation_id ) {
			$this->redirect(
				'iadal-congregations-create',
				array(
					'iadal_error' => __( 'Nao foi possivel criar a congregacao.', 'iadal-gestao-ministerial' ),
				)
			);
		}

		$credentials = $this->create_leadership_users( (int) $congregation_id, $data );

		if ( is_wp_error( $credentials ) ) {
			$this->repository->delete( (int) $congregation_id );
			$this->redirect(
				'iadal-congregations-create',
				array(
					'iadal_error' => $credentials->get_error_message(),
				)
			);
		}

		$credential_key = $this->store_credentials_transient( (int) $congregation_id, $credentials );

		$this->redirect(
			'iadal-congregations-credentials',
			array(
				'congregation_id' => (int) $congregation_id,
				'credential_key'  => $credential_key,
				'iadal_notice'    => __( 'Congregacao criada com sucesso. Guarde as senhas provisorias exibidas agora.', 'iadal-gestao-ministerial' ),
			)
		);
	}

	/**
	 * Handles congregation update.
	 *
	 * @return void
	 */
	public function handle_update(): void {
		$this->require_capability( 'iadal_edit_congregations' );

		$congregation_id = $this->get_int_from_post( 'congregation_id', 0 );
		$this->verify_nonce( 'iadal_congregations_update_' . $congregation_id, 'iadal_congregations_nonce' );

		$congregation = $this->repository->find( $congregation_id );

		if ( ! $congregation ) {
			wp_die( esc_html__( 'Congregacao nao encontrada.', 'iadal-gestao-ministerial' ) );
		}

		$data   = $this->sanitize_congregation_data();
		$errors = $this->validate_congregation_data( $data );

		if ( $errors ) {
			$this->redirect(
				'iadal-congregations-edit',
				array(
					'congregation_id' => $congregation_id,
					'iadal_error'     => implode( ' ', $errors ),
				)
			);
		}

		$updated = $this->repository->update( $congregation_id, $data );

		if ( ! $updated ) {
			$this->redirect(
				'iadal-congregations-edit',
				array(
					'congregation_id' => $congregation_id,
					'iadal_error'     => __( 'Nao foi possivel atualizar a congregacao.', 'iadal-gestao-ministerial' ),
				)
			);
		}

		$this->sync_leadership_users( $congregation, $data );

		$this->redirect(
			'iadal-congregations',
			array(
				'iadal_notice' => __( 'Congregacao atualizada com sucesso.', 'iadal-gestao-ministerial' ),
			)
		);
	}

	/**
	 * Handles congregation soft deletion.
	 *
	 * @return void
	 */
	public function handle_delete(): void {
		$this->require_capability( 'iadal_delete_congregations' );

		$congregation_id = $this->get_int_from_post( 'congregation_id', 0 );
		$this->verify_nonce( 'iadal_congregations_delete_' . $congregation_id, 'iadal_congregations_nonce' );

		$deleted = $this->repository->delete( $congregation_id );

		if ( ! $deleted ) {
			$this->redirect(
				'iadal-congregations',
				array(
					'iadal_error' => __( 'Nao foi possivel excluir a congregacao.', 'iadal-gestao-ministerial' ),
				)
			);
		}

		$this->redirect(
			'iadal-congregations',
			array(
				'iadal_notice' => __( 'Congregacao enviada para exclusao logica.', 'iadal-gestao-ministerial' ),
			)
		);
	}

	/**
	 * Handles congregation block/unblock.
	 *
	 * @return void
	 */
	public function handle_status(): void {
		$this->require_capability( 'iadal_block_congregations' );

		$congregation_id = $this->get_int_from_post( 'congregation_id', 0 );
		$status          = isset( $_POST['status'] ) ? sanitize_key( wp_unslash( $_POST['status'] ) ) : '';

		$this->verify_nonce( 'iadal_congregations_status_' . $congregation_id, 'iadal_congregations_nonce' );

		if ( ! in_array( $status, array( 'ativo', 'bloqueado' ), true ) ) {
			wp_die( esc_html__( 'Status invalido.', 'iadal-gestao-ministerial' ) );
		}

		$updated = $this->repository->set_status( $congregation_id, $status );

		if ( ! $updated ) {
			$this->redirect(
				'iadal-congregations',
				array(
					'iadal_error' => __( 'Nao foi possivel alterar o status da congregacao.', 'iadal-gestao-ministerial' ),
				)
			);
		}

		$this->redirect(
			'iadal-congregations',
			array(
				'iadal_notice' => __( 'Status da congregacao atualizado com sucesso.', 'iadal-gestao-ministerial' ),
			)
		);
	}

	/**
	 * Resets a leadership user temporary password.
	 *
	 * @return void
	 */
	public function handle_reset_password(): void {
		$this->require_capability( 'iadal_manage_congregation_credentials' );

		$congregation_id = $this->get_int_from_post( 'congregation_id', 0 );
		$user_id         = $this->get_int_from_post( 'user_id', 0 );

		$this->verify_nonce( 'iadal_congregations_reset_password_' . $user_id, 'iadal_congregations_nonce' );

		$congregation = $this->repository->find( $congregation_id );
		$users        = $this->repository->users_for_church( $congregation_id );
		$target_user  = null;

		foreach ( $users as $user ) {
			if ( (int) $user['id'] === $user_id ) {
				$target_user = $user;
				break;
			}
		}

		if ( ! $congregation || ! $target_user ) {
			wp_die( esc_html__( 'Usuario da congregacao nao encontrado.', 'iadal-gestao-ministerial' ) );
		}

		$password      = wp_generate_password( 14, true, true );
		$password_hash = wp_hash_password( $password );
		$updated       = $this->repository->update_user(
			$user_id,
			array(
				'password_hash'         => $password_hash,
				'initial_password_hash' => $password_hash,
				'must_change_password'  => 1,
				'status'                => 'ativo',
			)
		);

		if ( ! $updated ) {
			$this->redirect(
				'iadal-congregations-credentials',
				array(
					'congregation_id' => $congregation_id,
					'iadal_error'     => __( 'Nao foi possivel redefinir a senha.', 'iadal-gestao-ministerial' ),
				)
			);
		}

		$credential_key = $this->store_credentials_transient(
			$congregation_id,
			array(
				array(
					'name'     => (string) $target_user['name'],
					'role'     => (string) $target_user['role'],
					'login'    => (string) $target_user['login'],
					'password' => $password,
				),
			)
		);

		$this->redirect(
			'iadal-congregations-credentials',
			array(
				'congregation_id' => $congregation_id,
				'credential_key'  => $credential_key,
				'iadal_notice'    => __( 'Senha provisoria gerada com sucesso. Guarde a senha exibida agora.', 'iadal-gestao-ministerial' ),
			)
		);
	}

	/**
	 * Returns status options.
	 *
	 * @return array<string, string>
	 */
	public static function status_options(): array {
		return array(
			'ativo'     => __( 'Ativa', 'iadal-gestao-ministerial' ),
			'inativo'   => __( 'Inativa', 'iadal-gestao-ministerial' ),
			'bloqueado' => __( 'Bloqueada', 'iadal-gestao-ministerial' ),
		);
	}

	/**
	 * Returns role labels for internal leadership users.
	 *
	 * @return array<string, string>
	 */
	public static function role_labels(): array {
		return array(
			'pastor_local'     => __( 'Pastor Local', 'iadal-gestao-ministerial' ),
			'secretaria_local' => __( 'Secretaria Local', 'iadal-gestao-ministerial' ),
		);
	}

	/**
	 * Requires one congregation capability.
	 *
	 * @param string $capability Required capability.
	 * @return void
	 */
	private function require_capability( string $capability ): void {
		if ( ! current_user_can( $capability ) && ! current_user_can( 'iadal_manage_congregations' ) ) {
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
		$search = filter_input( INPUT_GET, 's', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$status = filter_input( INPUT_GET, 'status', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		$status_options = array_keys( self::status_options() );
		$status         = in_array( $status, $status_options, true ) ? $status : '';

		return array(
			'search' => sanitize_text_field( (string) $search ),
			'status' => $status,
		);
	}

	/**
	 * Sanitizes congregation data from POST.
	 *
	 * @return array<string, mixed>
	 */
	private function sanitize_congregation_data(): array {
		$post   = wp_unslash( $_POST );
		$status = isset( $post['status'] ) ? sanitize_key( $post['status'] ) : 'ativo';

		if ( ! array_key_exists( $status, self::status_options() ) ) {
			$status = 'ativo';
		}

		return array(
			'name'                      => isset( $post['name'] ) ? sanitize_text_field( $post['name'] ) : '',
			'pastor_name'               => isset( $post['pastor_name'] ) ? sanitize_text_field( $post['pastor_name'] ) : '',
			'pastor_phone'              => isset( $post['pastor_phone'] ) ? sanitize_text_field( $post['pastor_phone'] ) : '',
			'secretary_name'            => isset( $post['secretary_name'] ) ? sanitize_text_field( $post['secretary_name'] ) : '',
			'secretary_phone'           => isset( $post['secretary_phone'] ) ? sanitize_text_field( $post['secretary_phone'] ) : '',
			'zip_code'                  => isset( $post['zip_code'] ) ? sanitize_text_field( $post['zip_code'] ) : '',
			'address'                   => isset( $post['address'] ) ? sanitize_text_field( $post['address'] ) : '',
			'address_number'            => isset( $post['address_number'] ) ? sanitize_text_field( $post['address_number'] ) : '',
			'address_complement'        => isset( $post['address_complement'] ) ? sanitize_text_field( $post['address_complement'] ) : '',
			'district'                  => isset( $post['district'] ) ? sanitize_text_field( $post['district'] ) : '',
			'city'                      => isset( $post['city'] ) ? sanitize_text_field( $post['city'] ) : '',
			'state'                     => isset( $post['state'] ) ? strtoupper( substr( sanitize_text_field( $post['state'] ), 0, 2 ) ) : '',
			'notes'                     => isset( $post['notes'] ) ? sanitize_textarea_field( $post['notes'] ) : '',
			'status'                    => $status,
			'cleaning_requests_blocked' => isset( $post['cleaning_requests_blocked'] ) ? 1 : 0,
		);
	}

	/**
	 * Validates congregation data.
	 *
	 * @param array<string, mixed> $data Sanitized congregation data.
	 * @return array<int, string>
	 */
	private function validate_congregation_data( array $data ): array {
		$errors = array();

		if ( '' === $data['name'] ) {
			$errors[] = __( 'Informe o nome da congregacao.', 'iadal-gestao-ministerial' );
		}

		if ( '' === $data['pastor_name'] ) {
			$errors[] = __( 'Informe o nome do Pastor Local.', 'iadal-gestao-ministerial' );
		}

		if ( '' === $data['secretary_name'] ) {
			$errors[] = __( 'Informe o nome da Secretaria Local.', 'iadal-gestao-ministerial' );
		}

		return $errors;
	}

	/**
	 * Creates pastor and secretary users for a congregation.
	 *
	 * @param int                  $congregation_id Congregation ID.
	 * @param array<string, mixed> $data Congregation data.
	 * @return array<int, array<string, string>>|WP_Error
	 */
	private function create_leadership_users( int $congregation_id, array $data ) {
		$pastor_password    = wp_generate_password( 14, true, true );
		$secretary_password = wp_generate_password( 14, true, true );
		$pastor_login       = $this->generate_unique_login( $data['pastor_name'], 'pastor' );
		$secretary_login    = $this->generate_unique_login( $data['secretary_name'], 'secretaria' );

		$pastor_id = $this->repository->create_user(
			array(
				'name'                  => (string) $data['pastor_name'],
				'login'                 => $pastor_login,
				'password_hash'         => wp_hash_password( $pastor_password ),
				'initial_password_hash' => wp_hash_password( $pastor_password ),
				'role'                  => 'pastor_local',
				'church_id'             => $congregation_id,
				'phone'                 => (string) $data['pastor_phone'],
				'status'                => 'ativo',
				'must_change_password'  => 1,
			)
		);

		if ( ! $pastor_id ) {
			return new WP_Error( 'iadal_pastor_user', __( 'Nao foi possivel criar o usuario do Pastor Local.', 'iadal-gestao-ministerial' ) );
		}

		$secretary_id = $this->repository->create_user(
			array(
				'name'                  => (string) $data['secretary_name'],
				'login'                 => $secretary_login,
				'password_hash'         => wp_hash_password( $secretary_password ),
				'initial_password_hash' => wp_hash_password( $secretary_password ),
				'role'                  => 'secretaria_local',
				'church_id'             => $congregation_id,
				'phone'                 => (string) $data['secretary_phone'],
				'status'                => 'ativo',
				'must_change_password'  => 1,
			)
		);

		if ( ! $secretary_id ) {
			return new WP_Error( 'iadal_secretary_user', __( 'Nao foi possivel criar o usuario da Secretaria Local.', 'iadal-gestao-ministerial' ) );
		}

		$leadership_linked = $this->repository->update(
			$congregation_id,
			array(
				'pastor_user_id'    => (int) $pastor_id,
				'secretary_user_id' => (int) $secretary_id,
			)
		);

		if ( ! $leadership_linked ) {
			return new WP_Error( 'iadal_leadership_link', __( 'Nao foi possivel vincular a lideranca local a congregacao.', 'iadal-gestao-ministerial' ) );
		}

		return array(
			array(
				'name'     => (string) $data['pastor_name'],
				'role'     => 'pastor_local',
				'login'    => $pastor_login,
				'password' => $pastor_password,
			),
			array(
				'name'     => (string) $data['secretary_name'],
				'role'     => 'secretaria_local',
				'login'    => $secretary_login,
				'password' => $secretary_password,
			),
		);
	}

	/**
	 * Synchronizes existing leadership users after congregation edit.
	 *
	 * @param array<string, mixed> $congregation Existing congregation.
	 * @param array<string, mixed> $data New congregation data.
	 * @return void
	 */
	private function sync_leadership_users( array $congregation, array $data ): void {
		if ( ! empty( $congregation['pastor_user_id'] ) ) {
			$this->repository->update_user(
				(int) $congregation['pastor_user_id'],
				array(
					'name'   => (string) $data['pastor_name'],
					'phone'  => (string) $data['pastor_phone'],
					'status' => 'bloqueado' === $data['status'] ? 'bloqueado' : 'ativo',
				)
			);
		}

		if ( ! empty( $congregation['secretary_user_id'] ) ) {
			$this->repository->update_user(
				(int) $congregation['secretary_user_id'],
				array(
					'name'   => (string) $data['secretary_name'],
					'phone'  => (string) $data['secretary_phone'],
					'status' => 'bloqueado' === $data['status'] ? 'bloqueado' : 'ativo',
				)
			);
		}
	}

	/**
	 * Generates a unique login for an internal user.
	 *
	 * @param string $name User name.
	 * @param string $prefix Login prefix.
	 * @return string
	 */
	private function generate_unique_login( string $name, string $prefix ): string {
		$base = sanitize_title( remove_accents( $name ) );
		$base = $base ? str_replace( '-', '.', $base ) : 'usuario';
		$base = strtolower( $prefix . '.' . $base );
		$login = substr( $base, 0, 110 );
		$count = 1;

		while ( $this->repository->login_exists( $login ) ) {
			$count++;
			$login = substr( $base, 0, 105 ) . '.' . $count;
		}

		return $login;
	}

	/**
	 * Stores temporary credentials for one-time display.
	 *
	 * @param int                                  $congregation_id Congregation ID.
	 * @param array<int, array<string, string>>    $credentials Credentials.
	 * @return string
	 */
	private function store_credentials_transient( int $congregation_id, array $credentials ): string {
		$key = get_current_user_id() . '_' . wp_generate_uuid4();

		set_transient(
			'iadal_congregation_credentials_' . $key,
			array(
				'current_user'    => get_current_user_id(),
				'congregation_id' => $congregation_id,
				'credentials'     => $credentials,
			),
			15 * MINUTE_IN_SECONDS
		);

		return $key;
	}

	/**
	 * Gets one-time temporary credentials.
	 *
	 * @param string $key Credential transient key.
	 * @param int    $congregation_id Congregation ID.
	 * @return array<int, array<string, string>>
	 */
	private function get_credentials_transient( string $key, int $congregation_id ): array {
		if ( '' === $key ) {
			return array();
		}

		$payload = get_transient( 'iadal_congregation_credentials_' . $key );

		if (
			! is_array( $payload )
			|| (int) ( $payload['current_user'] ?? 0 ) !== get_current_user_id()
			|| (int) ( $payload['congregation_id'] ?? 0 ) !== $congregation_id
			|| ! isset( $payload['credentials'] )
			|| ! is_array( $payload['credentials'] )
		) {
			return array();
		}

		delete_transient( 'iadal_congregation_credentials_' . $key );

		return $payload['credentials'];
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
	 * Redirects to an admin page using transient-backed messages.
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
			$message_key             = get_current_user_id() . '_' . wp_generate_uuid4();
			$args['iadal_message']   = $message_key;
			$message['created_at']   = time();
			$message['current_user'] = get_current_user_id();

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
}
