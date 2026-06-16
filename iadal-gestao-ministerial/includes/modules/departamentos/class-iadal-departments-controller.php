<?php
/**
 * Departments admin controller.
 *
 * @package IADAL_Gestao_Ministerial
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles admin screens and actions for departments.
 */
class IADAL_Departments_Controller {

	/**
	 * Departments repository.
	 *
	 * @var IADAL_Departments_Repository
	 */
	private IADAL_Departments_Repository $repository;

	/**
	 * Constructor.
	 *
	 * @param IADAL_Departments_Repository $repository Departments repository.
	 */
	public function __construct( IADAL_Departments_Repository $repository ) {
		$this->repository = $repository;
	}

	/**
	 * Registers admin-post hooks.
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		add_action( 'admin_post_iadal_departments_library_create', array( $this, 'handle_library_create' ) );
		add_action( 'admin_post_iadal_departments_library_update', array( $this, 'handle_library_update' ) );
		add_action( 'admin_post_iadal_departments_library_delete', array( $this, 'handle_library_delete' ) );
		add_action( 'admin_post_iadal_departments_activate', array( $this, 'handle_activate' ) );
		add_action( 'admin_post_iadal_departments_update', array( $this, 'handle_update' ) );
		add_action( 'admin_post_iadal_departments_delete', array( $this, 'handle_delete' ) );
		add_action( 'admin_post_iadal_departments_component_create', array( $this, 'handle_component_create' ) );
		add_action( 'admin_post_iadal_departments_component_update', array( $this, 'handle_component_update' ) );
		add_action( 'admin_post_iadal_departments_component_delete', array( $this, 'handle_component_delete' ) );
		add_action( 'admin_post_iadal_departments_component_reset_password', array( $this, 'handle_component_reset_password' ) );
	}

	/**
	 * Renders activated departments list.
	 *
	 * @return void
	 */
	public function render_list_page(): void {
		$this->require_capability( 'iadal_view_departments' );

		$filters  = $this->get_filters();

		if ( ! empty( $filters['church_id'] ) && ! $this->can_access_church( (int) $filters['church_id'] ) ) {
			wp_die( esc_html__( 'Voce nao tem permissao para acessar esta congregacao.', 'iadal-gestao-ministerial' ) );
		}

		$page     = max( 1, $this->get_int_from_query( 'paged', 1 ) );
		$per_page = 20;
		$offset   = ( $page - 1 ) * $per_page;
		$args     = array_merge(
			$filters,
			array(
				'limit'  => $per_page,
				'offset' => $offset,
			)
		);

		$this->include_template(
			'departamentos/list.php',
			array(
				'departments'   => $this->repository->departments( $args ),
				'filters'       => $filters,
				'congregations' => $this->get_congregations_for_select(),
				'status_options'=> self::status_options(),
				'page'          => $page,
				'per_page'      => $per_page,
				'total'         => $this->repository->count_departments( $filters ),
			)
		);
	}

	/**
	 * Renders department library.
	 *
	 * @return void
	 */
	public function render_library_page(): void {
		$this->require_capability( 'iadal_view_departments' );

		$this->include_template(
			'departamentos/library.php',
			array(
				'library' => $this->repository->library(),
			)
		);
	}

	/**
	 * Renders custom department creation page.
	 *
	 * @return void
	 */
	public function render_create_custom_page(): void {
		$this->require_capability( 'iadal_create_custom_departments' );

		$this->include_template( 'departamentos/form-custom.php' );
	}

	/**
	 * Renders custom department edit page.
	 *
	 * @return void
	 */
	public function render_edit_library_page(): void {
		$this->require_capability( 'iadal_create_custom_departments' );

		$library_id = $this->get_int_from_query( 'library_id', 0 );
		$library    = $this->repository->find_library( $library_id );

		if ( ! $library || 'personalizado' !== $library['type'] ) {
			wp_die( esc_html__( 'Departamento personalizado nao encontrado.', 'iadal-gestao-ministerial' ) );
		}

		$this->include_template(
			'departamentos/form-custom.php',
			array(
				'library_item' => $library,
				'is_edit'      => true,
			)
		);
	}

	/**
	 * Renders department activation page.
	 *
	 * @return void
	 */
	public function render_activate_page(): void {
		$this->require_capability( 'iadal_activate_departments' );

		$this->include_template(
			'departamentos/activate.php',
			array(
				'library'       => $this->repository->library( array( 'status' => 'ativo' ) ),
				'congregations' => $this->get_congregations_for_select(),
			)
		);
	}

	/**
	 * Renders department edit page.
	 *
	 * @return void
	 */
	public function render_edit_page(): void {
		$this->require_capability( 'iadal_edit_departments' );

		$department_id = $this->get_int_from_query( 'department_id', 0 );
		$department    = $this->repository->find_department( $department_id );

		if ( ! $department ) {
			wp_die( esc_html__( 'Departamento nao encontrado.', 'iadal-gestao-ministerial' ) );
		}

		if ( ! $this->can_access_department( $department ) ) {
			wp_die( esc_html__( 'Voce nao tem permissao para acessar este departamento.', 'iadal-gestao-ministerial' ) );
		}

		$this->include_template(
			'departamentos/form-edit.php',
			array(
				'department'     => $department,
				'status_options' => self::status_options(),
			)
		);
	}

	/**
	 * Renders department components page.
	 *
	 * @return void
	 */
	public function render_components_page(): void {
		$this->require_capability( 'iadal_manage_department_components' );

		$department_id = $this->get_int_from_query( 'department_id', 0 );
		$department    = $this->repository->find_department( $department_id );

		if ( ! $department ) {
			wp_die( esc_html__( 'Departamento nao encontrado.', 'iadal-gestao-ministerial' ) );
		}

		if ( ! $this->can_access_department( $department ) ) {
			wp_die( esc_html__( 'Voce nao tem permissao para acessar este departamento.', 'iadal-gestao-ministerial' ) );
		}

		$credential_key = filter_input( INPUT_GET, 'credential_key', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		$this->include_template(
			'departamentos/components.php',
			array(
				'department'  => $department,
				'components'  => $this->repository->components( $department_id ),
				'members'     => $this->get_members_for_church( (int) $department['church_id'] ),
				'credentials' => $this->get_credentials_transient( (string) $credential_key, $department_id ),
			)
		);
	}

	/**
	 * Handles custom department library creation.
	 *
	 * @return void
	 */
	public function handle_library_create(): void {
		$this->require_capability( 'iadal_create_custom_departments' );
		$this->verify_nonce( 'iadal_departments_library_create', 'iadal_departments_nonce' );

		$data   = $this->sanitize_library_data();
		$errors = $this->validate_library_data( $data );

		if ( $this->repository->library_slug_exists( $data['slug'] ) ) {
			$errors[] = __( 'Ja existe um departamento com este identificador.', 'iadal-gestao-ministerial' );
		}

		if ( $errors ) {
			$this->redirect( 'iadal-departments-custom', array( 'iadal_error' => implode( ' ', $errors ) ) );
		}

		$library_id = $this->repository->create_library( $data );

		if ( ! $library_id ) {
			$this->redirect( 'iadal-departments-custom', array( 'iadal_error' => __( 'Nao foi possivel criar o departamento personalizado.', 'iadal-gestao-ministerial' ) ) );
		}

		IADAL_Audit::log( 'departments', 'create_library', 'department_library', (int) $library_id, null, $data, null );

		$this->redirect( 'iadal-departments-library', array( 'iadal_notice' => __( 'Departamento personalizado criado com sucesso.', 'iadal-gestao-ministerial' ) ) );
	}

	/**
	 * Handles custom library department update.
	 *
	 * @return void
	 */
	public function handle_library_update(): void {
		$this->require_capability( 'iadal_create_custom_departments' );

		$library_id = $this->get_int_from_post( 'library_id', 0 );
		$this->verify_nonce( 'iadal_departments_library_update_' . $library_id, 'iadal_departments_nonce' );

		$existing = $this->repository->find_library( $library_id );

		if ( ! $existing || 'personalizado' !== $existing['type'] ) {
			wp_die( esc_html__( 'Departamento personalizado nao encontrado.', 'iadal-gestao-ministerial' ) );
		}

		$data   = $this->sanitize_library_data();
		$errors = $this->validate_library_data( $data );

		if ( $this->repository->library_slug_exists( $data['slug'], $library_id ) ) {
			$errors[] = __( 'Ja existe um departamento com este identificador.', 'iadal-gestao-ministerial' );
		}

		if ( $errors ) {
			$this->redirect( 'iadal-departments-library-edit', array( 'library_id' => $library_id, 'iadal_error' => implode( ' ', $errors ) ) );
		}

		$updated = $this->repository->update_library( $library_id, $data );

		if ( ! $updated ) {
			$this->redirect( 'iadal-departments-library-edit', array( 'library_id' => $library_id, 'iadal_error' => __( 'Nao foi possivel atualizar o departamento personalizado.', 'iadal-gestao-ministerial' ) ) );
		}

		IADAL_Audit::log( 'departments', 'update_library', 'department_library', $library_id, $existing, $data, null );

		$this->redirect( 'iadal-departments-library', array( 'iadal_notice' => __( 'Departamento personalizado atualizado com sucesso.', 'iadal-gestao-ministerial' ) ) );
	}

	/**
	 * Handles custom library department soft deletion.
	 *
	 * @return void
	 */
	public function handle_library_delete(): void {
		$this->require_capability( 'iadal_create_custom_departments' );

		$library_id = $this->get_int_from_post( 'library_id', 0 );
		$this->verify_nonce( 'iadal_departments_library_delete_' . $library_id, 'iadal_departments_nonce' );

		$existing = $this->repository->find_library( $library_id );

		if ( ! $existing || 'personalizado' !== $existing['type'] ) {
			wp_die( esc_html__( 'Departamento personalizado nao encontrado.', 'iadal-gestao-ministerial' ) );
		}

		$deleted = $this->repository->delete_library( $library_id );

		if ( ! $deleted ) {
			$this->redirect( 'iadal-departments-library', array( 'iadal_error' => __( 'Nao foi possivel inativar o departamento personalizado.', 'iadal-gestao-ministerial' ) ) );
		}

		IADAL_Audit::log( 'departments', 'delete_library', 'department_library', $library_id, $existing, array( 'deleted' => true ), null );

		$this->redirect( 'iadal-departments-library', array( 'iadal_notice' => __( 'Departamento personalizado inativado com sucesso.', 'iadal-gestao-ministerial' ) ) );
	}

	/**
	 * Handles department activation for a congregation.
	 *
	 * @return void
	 */
	public function handle_activate(): void {
		$this->require_capability( 'iadal_activate_departments' );
		$this->verify_nonce( 'iadal_departments_activate', 'iadal_departments_nonce' );

		$data   = $this->sanitize_activation_data();
		$errors = $this->validate_activation_data( $data );
		$library = $this->repository->find_library( (int) $data['library_id'] );
		$church  = $this->repository->find_church( (int) $data['church_id'] );

		if ( ! $library ) {
			$errors[] = __( 'Departamento da biblioteca nao encontrado.', 'iadal-gestao-ministerial' );
		}

		if ( $library && 'ativo' !== $library['status'] ) {
			$errors[] = __( 'Este departamento da biblioteca nao esta ativo.', 'iadal-gestao-ministerial' );
		}

		if ( ! $church || 'ativo' !== $church['status'] ) {
			$errors[] = __( 'A congregacao deve estar ativa para receber departamentos.', 'iadal-gestao-ministerial' );
		}

		if ( ! empty( $data['church_id'] ) && ! $this->can_access_church( (int) $data['church_id'] ) ) {
			$errors[] = __( 'Voce nao tem permissao para ativar departamento nesta congregacao.', 'iadal-gestao-ministerial' );
		}

		if ( $library && $this->repository->department_exists( (int) $data['church_id'], (int) $data['library_id'] ) ) {
			$errors[] = __( 'Esta congregacao ja ativou este departamento.', 'iadal-gestao-ministerial' );
		}

		if ( $errors ) {
			$this->redirect( 'iadal-departments-activate', array( 'iadal_error' => implode( ' ', $errors ) ) );
		}

		IADAL_Database::begin_transaction();

		$leader = $this->create_leader_user( (int) $data['church_id'], (string) $data['leader_name'], (string) $data['leader_phone'] );

		if ( is_wp_error( $leader ) ) {
			IADAL_Database::rollback();
			$this->redirect( 'iadal-departments-activate', array( 'iadal_error' => $leader->get_error_message() ) );
		}

		$department_data = $this->department_data_from_library( (int) $data['church_id'], $library, (int) $leader['user_id'], (string) $data['leader_name'], (string) $data['leader_phone'] );
		$department_id   = $this->repository->create_department( $department_data );

		if ( ! $department_id ) {
			IADAL_Database::rollback();
			$this->redirect( 'iadal-departments-activate', array( 'iadal_error' => __( 'Nao foi possivel ativar o departamento.', 'iadal-gestao-ministerial' ) ) );
		}

		$component_id = $this->repository->create_component(
			array(
				'department_id' => (int) $department_id,
				'church_id'     => (int) $data['church_id'],
				'user_id'       => (int) $leader['user_id'],
				'name'          => (string) $data['leader_name'],
				'phone'         => (string) $data['leader_phone'],
				'function_name' => __( 'Lider', 'iadal-gestao-ministerial' ),
				'is_leader'     => 1,
				'status'        => 'ativo',
			)
		);

		if ( ! $component_id ) {
			IADAL_Database::rollback();
			$this->redirect( 'iadal-departments-activate', array( 'iadal_error' => __( 'Nao foi possivel vincular o lider ao departamento.', 'iadal-gestao-ministerial' ) ) );
		}

		$leader_linked = $this->repository->update_internal_user( (int) $leader['user_id'], array( 'department_id' => (int) $department_id ) );

		if ( ! $leader_linked ) {
			IADAL_Database::rollback();
			$this->redirect( 'iadal-departments-activate', array( 'iadal_error' => __( 'Nao foi possivel vincular o usuario do lider ao departamento.', 'iadal-gestao-ministerial' ) ) );
		}

		$this->repository->update_church_module_status( (int) $data['church_id'], 'departamentos', 'ativo' );

		if ( 'ebd' === (string) $library['slug'] ) {
			$this->repository->update_church_module_status( (int) $data['church_id'], 'ebd', 'ativo' );
		}

		IADAL_Database::commit();

		IADAL_Audit::log( 'departments', 'activate', 'department', (int) $department_id, null, $department_data, (int) $data['church_id'] );

		$credential_key = $this->store_credentials_transient(
			(int) $department_id,
			array(
				array(
					'name'     => (string) $data['leader_name'],
					'role'     => 'lider_departamento',
					'login'    => (string) $leader['login'],
					'password' => (string) $leader['password'],
				),
			)
		);

		$this->redirect(
			'iadal-departments-components',
			array(
				'department_id'  => (int) $department_id,
				'credential_key' => $credential_key,
				'iadal_notice'   => __( 'Departamento ativado com sucesso. Guarde as credenciais do lider.', 'iadal-gestao-ministerial' ),
			)
		);
	}

	/**
	 * Handles department update.
	 *
	 * @return void
	 */
	public function handle_update(): void {
		$this->require_capability( 'iadal_edit_departments' );

		$department_id = $this->get_int_from_post( 'department_id', 0 );
		$this->verify_nonce( 'iadal_departments_update_' . $department_id, 'iadal_departments_nonce' );

		$department = $this->repository->find_department( $department_id );

		if ( ! $department ) {
			wp_die( esc_html__( 'Departamento nao encontrado.', 'iadal-gestao-ministerial' ) );
		}

		if ( ! $this->can_access_department( $department ) ) {
			wp_die( esc_html__( 'Voce nao tem permissao para alterar este departamento.', 'iadal-gestao-ministerial' ) );
		}

		$data   = $this->sanitize_department_update_data();
		$errors = $this->validate_department_update_data( $data );

		if ( $errors ) {
			$this->redirect( 'iadal-departments-edit', array( 'department_id' => $department_id, 'iadal_error' => implode( ' ', $errors ) ) );
		}

		$updated = $this->repository->update_department( $department_id, $data );

		if ( ! $updated ) {
			$this->redirect( 'iadal-departments-edit', array( 'department_id' => $department_id, 'iadal_error' => __( 'Nao foi possivel atualizar o departamento.', 'iadal-gestao-ministerial' ) ) );
		}

		IADAL_Audit::log( 'departments', 'update', 'department', $department_id, $department, $data, (int) $department['church_id'] );

		$this->redirect( 'iadal-departments', array( 'iadal_notice' => __( 'Departamento atualizado com sucesso.', 'iadal-gestao-ministerial' ) ) );
	}

	/**
	 * Handles department soft deletion.
	 *
	 * @return void
	 */
	public function handle_delete(): void {
		$this->require_capability( 'iadal_delete_departments' );

		$department_id = $this->get_int_from_post( 'department_id', 0 );
		$this->verify_nonce( 'iadal_departments_delete_' . $department_id, 'iadal_departments_nonce' );

		$department = $this->repository->find_department( $department_id );

		if ( ! $department ) {
			wp_die( esc_html__( 'Departamento nao encontrado.', 'iadal-gestao-ministerial' ) );
		}

		if ( ! $this->can_access_department( $department ) ) {
			wp_die( esc_html__( 'Voce nao tem permissao para excluir este departamento.', 'iadal-gestao-ministerial' ) );
		}

		$deleted = $this->repository->delete_department( $department_id );

		if ( ! $deleted ) {
			$this->redirect( 'iadal-departments', array( 'iadal_error' => __( 'Nao foi possivel excluir o departamento.', 'iadal-gestao-ministerial' ) ) );
		}

		if ( 'ebd' === (string) $department['slug'] ) {
			$this->repository->update_church_module_status( (int) $department['church_id'], 'ebd', 'disponivel' );
		}

		if ( 0 === $this->repository->count_departments( array( 'church_id' => (int) $department['church_id'], 'status' => 'ativo' ) ) ) {
			$this->repository->update_church_module_status( (int) $department['church_id'], 'departamentos', 'disponivel' );
		}

		IADAL_Audit::log( 'departments', 'delete', 'department', $department_id, $department, array( 'deleted' => true ), (int) $department['church_id'] );

		$this->redirect( 'iadal-departments', array( 'iadal_notice' => __( 'Departamento enviado para exclusao logica.', 'iadal-gestao-ministerial' ) ) );
	}

	/**
	 * Handles component creation.
	 *
	 * @return void
	 */
	public function handle_component_create(): void {
		$this->require_capability( 'iadal_manage_department_components' );

		$department_id = $this->get_int_from_post( 'department_id', 0 );
		$this->verify_nonce( 'iadal_departments_component_create_' . $department_id, 'iadal_departments_nonce' );

		$department = $this->repository->find_department( $department_id );

		if ( ! $department ) {
			wp_die( esc_html__( 'Departamento nao encontrado.', 'iadal-gestao-ministerial' ) );
		}

		if ( ! $this->can_access_department( $department ) ) {
			wp_die( esc_html__( 'Voce nao tem permissao para alterar componentes deste departamento.', 'iadal-gestao-ministerial' ) );
		}

		$data   = $this->sanitize_component_data( $department );
		$errors = $this->validate_component_data( $data );

		if ( $errors ) {
			$this->redirect( 'iadal-departments-components', array( 'department_id' => $department_id, 'iadal_error' => implode( ' ', $errors ) ) );
		}

		IADAL_Database::begin_transaction();

		$component_user = $this->create_component_user( $department, (string) $data['name'], (string) $data['phone'] );

		if ( is_wp_error( $component_user ) ) {
			IADAL_Database::rollback();
			$this->redirect( 'iadal-departments-components', array( 'department_id' => $department_id, 'iadal_error' => $component_user->get_error_message() ) );
		}

		$data['user_id'] = (int) $component_user['user_id'];
		$component_id    = $this->repository->create_component( $data );

		if ( ! $component_id ) {
			IADAL_Database::rollback();
			$this->redirect( 'iadal-departments-components', array( 'department_id' => $department_id, 'iadal_error' => __( 'Nao foi possivel adicionar o componente.', 'iadal-gestao-ministerial' ) ) );
		}

		IADAL_Database::commit();

		IADAL_Audit::log( 'departments', 'create_component', 'department_user', (int) $component_id, null, $data, (int) $department['church_id'] );

		$credential_key = $this->store_credentials_transient(
			$department_id,
			array(
				array(
					'name'     => (string) $data['name'],
					'role'     => 'componente_departamento',
					'login'    => (string) $component_user['login'],
					'password' => (string) $component_user['password'],
				),
			)
		);

		$this->redirect( 'iadal-departments-components', array( 'department_id' => $department_id, 'credential_key' => $credential_key, 'iadal_notice' => __( 'Componente adicionado com sucesso. Guarde as credenciais exibidas agora.', 'iadal-gestao-ministerial' ) ) );
	}

	/**
	 * Handles component update.
	 *
	 * @return void
	 */
	public function handle_component_update(): void {
		$this->require_capability( 'iadal_manage_department_components' );

		$component_id = $this->get_int_from_post( 'component_id', 0 );
		$this->verify_nonce( 'iadal_departments_component_update_' . $component_id, 'iadal_departments_nonce' );

		$component = $this->repository->find_component( $component_id );

		if ( ! $component ) {
			wp_die( esc_html__( 'Componente nao encontrado.', 'iadal-gestao-ministerial' ) );
		}

		$department = $this->repository->find_department( (int) $component['department_id'] );

		if ( ! $department || ! $this->can_access_department( $department ) ) {
			wp_die( esc_html__( 'Voce nao tem permissao para alterar este componente.', 'iadal-gestao-ministerial' ) );
		}

		$data   = $this->sanitize_component_update_data();
		$errors = $this->validate_component_data( array_merge( $component, $data ) );

		if ( $errors ) {
			$this->redirect( 'iadal-departments-components', array( 'department_id' => (int) $department['id'], 'iadal_error' => implode( ' ', $errors ) ) );
		}

		$updated = $this->repository->update_component( $component_id, $data );

		if ( ! $updated ) {
			$this->redirect( 'iadal-departments-components', array( 'department_id' => (int) $department['id'], 'iadal_error' => __( 'Nao foi possivel atualizar o componente.', 'iadal-gestao-ministerial' ) ) );
		}

		if ( ! empty( $component['user_id'] ) ) {
			$this->repository->update_internal_user(
				(int) $component['user_id'],
				array(
					'name'   => (string) $data['name'],
					'phone'  => (string) $data['phone'],
					'status' => (string) $data['status'],
				)
			);
		}

		IADAL_Audit::log( 'departments', 'update_component', 'department_user', $component_id, $component, $data, (int) $department['church_id'] );

		$this->redirect( 'iadal-departments-components', array( 'department_id' => (int) $department['id'], 'iadal_notice' => __( 'Componente atualizado com sucesso.', 'iadal-gestao-ministerial' ) ) );
	}

	/**
	 * Handles component delete.
	 *
	 * @return void
	 */
	public function handle_component_delete(): void {
		$this->require_capability( 'iadal_manage_department_components' );

		$component_id = $this->get_int_from_post( 'component_id', 0 );
		$this->verify_nonce( 'iadal_departments_component_delete_' . $component_id, 'iadal_departments_nonce' );

		$component = $this->repository->find_component( $component_id );

		if ( ! $component ) {
			wp_die( esc_html__( 'Componente nao encontrado.', 'iadal-gestao-ministerial' ) );
		}

		$department = $this->repository->find_department( (int) $component['department_id'] );

		if ( ! $department || ! $this->can_access_department( $department ) ) {
			wp_die( esc_html__( 'Voce nao tem permissao para excluir este componente.', 'iadal-gestao-ministerial' ) );
		}

		if ( ! empty( $component['is_leader'] ) ) {
			$this->redirect( 'iadal-departments-components', array( 'department_id' => (int) $department['id'], 'iadal_error' => __( 'O lider nao pode ser removido por esta tela.', 'iadal-gestao-ministerial' ) ) );
		}

		$deleted = $this->repository->delete_component( $component_id );

		if ( ! $deleted ) {
			$this->redirect( 'iadal-departments-components', array( 'department_id' => (int) $department['id'], 'iadal_error' => __( 'Nao foi possivel excluir o componente.', 'iadal-gestao-ministerial' ) ) );
		}

		IADAL_Audit::log( 'departments', 'delete_component', 'department_user', $component_id, $component, array( 'deleted' => true ), (int) $department['church_id'] );

		$this->redirect( 'iadal-departments-components', array( 'department_id' => (int) $department['id'], 'iadal_notice' => __( 'Componente excluido com sucesso.', 'iadal-gestao-ministerial' ) ) );
	}

	/**
	 * Handles component password reset.
	 *
	 * @return void
	 */
	public function handle_component_reset_password(): void {
		$this->require_capability( 'iadal_manage_department_components' );

		$component_id = $this->get_int_from_post( 'component_id', 0 );
		$this->verify_nonce( 'iadal_departments_component_reset_password_' . $component_id, 'iadal_departments_nonce' );

		$component = $this->repository->find_component( $component_id );

		if ( ! $component || empty( $component['user_id'] ) ) {
			wp_die( esc_html__( 'Usuario do componente nao encontrado.', 'iadal-gestao-ministerial' ) );
		}

		$department = $this->repository->find_department( (int) $component['department_id'] );

		if ( ! $department || ! $this->can_access_department( $department ) ) {
			wp_die( esc_html__( 'Voce nao tem permissao para redefinir a senha deste componente.', 'iadal-gestao-ministerial' ) );
		}

		$password = wp_generate_password( 14, true, true );
		$updated  = $this->repository->update_internal_user(
			(int) $component['user_id'],
			array(
				'password_hash'         => wp_hash_password( $password ),
				'password_generated_at' => IADAL_Database::now(),
				'must_change_password'  => 1,
				'status'                => (string) $component['status'],
			)
		);

		if ( ! $updated ) {
			$this->redirect( 'iadal-departments-components', array( 'department_id' => (int) $department['id'], 'iadal_error' => __( 'Nao foi possivel redefinir a senha.', 'iadal-gestao-ministerial' ) ) );
		}

		$credential_key = $this->store_credentials_transient(
			(int) $department['id'],
			array(
				array(
					'name'     => (string) $component['name'],
					'role'     => ! empty( $component['is_leader'] ) ? 'lider_departamento' : 'componente_departamento',
					'login'    => $this->get_component_login( (int) $component['user_id'] ),
					'password' => $password,
				),
			)
		);

		IADAL_Audit::log( 'departments', 'reset_component_password', 'department_user', $component_id, null, array( 'user_id' => (int) $component['user_id'] ), (int) $department['church_id'] );

		$this->redirect( 'iadal-departments-components', array( 'department_id' => (int) $department['id'], 'credential_key' => $credential_key, 'iadal_notice' => __( 'Senha redefinida com sucesso. Guarde as credenciais exibidas agora.', 'iadal-gestao-ministerial' ) ) );
	}

	/**
	 * Returns status options.
	 *
	 * @return array<string, string>
	 */
	public static function status_options(): array {
		return array(
			'ativo'     => __( 'Ativo', 'iadal-gestao-ministerial' ),
			'bloqueado' => __( 'Bloqueado', 'iadal-gestao-ministerial' ),
			'inativo'   => __( 'Inativo', 'iadal-gestao-ministerial' ),
		);
	}

	/**
	 * Requires one department capability.
	 *
	 * @param string $capability Required capability.
	 * @return void
	 */
	private function require_capability( string $capability ): void {
		if ( ! current_user_can( $capability ) && ! current_user_can( 'iadal_manage_departments' ) ) {
			wp_die( esc_html__( 'Voce nao tem permissao para acessar esta area.', 'iadal-gestao-ministerial' ) );
		}
	}

	/**
	 * Checks if current user can access a church scope.
	 *
	 * @param int $church_id Church ID.
	 * @return bool
	 */
	private function can_access_church( int $church_id ): bool {
		if ( $church_id <= 0 ) {
			return false;
		}

		if ( current_user_can( 'iadal_manage_departments' ) || current_user_can( 'iadal_view_congregations' ) ) {
			return null !== $this->repository->find_church( $church_id );
		}

		// Future plugin-auth users should be checked here against their church_id.
		return false;
	}

	/**
	 * Checks if current user can access a department.
	 *
	 * @param array<string, mixed> $department Department data.
	 * @return bool
	 */
	private function can_access_department( array $department ): bool {
		return ! empty( $department['church_id'] ) && $this->can_access_church( (int) $department['church_id'] );
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
	 * @return array<string, mixed>
	 */
	private function get_filters(): array {
		$search    = filter_input( INPUT_GET, 's', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$status    = filter_input( INPUT_GET, 'status', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$church_id = filter_input( INPUT_GET, 'church_id', FILTER_SANITIZE_NUMBER_INT );

		$status_options = array_keys( self::status_options() );
		$status         = in_array( $status, $status_options, true ) ? $status : '';

		return array(
			'search'    => sanitize_text_field( (string) $search ),
			'status'    => $status,
			'church_id' => null !== $church_id && false !== $church_id ? max( 0, (int) $church_id ) : 0,
		);
	}

	/**
	 * Sanitizes library data.
	 *
	 * @return array<string, mixed>
	 */
	private function sanitize_library_data(): array {
		$post = wp_unslash( $_POST );
		$name = isset( $post['name'] ) ? sanitize_text_field( $post['name'] ) : '';
		$slug = isset( $post['slug'] ) ? sanitize_title( $post['slug'] ) : sanitize_title( remove_accents( $name ) );

		return array_merge(
			array(
				'name'        => $name,
				'slug'        => $slug,
				'description' => isset( $post['description'] ) ? sanitize_textarea_field( $post['description'] ) : '',
				'status'      => 'ativo',
			),
			$this->sanitize_feature_flags()
		);
	}

	/**
	 * Sanitizes activation data.
	 *
	 * @return array<string, mixed>
	 */
	private function sanitize_activation_data(): array {
		$post = wp_unslash( $_POST );

		return array(
			'church_id'    => isset( $post['church_id'] ) ? max( 0, (int) $post['church_id'] ) : 0,
			'library_id'   => isset( $post['library_id'] ) ? max( 0, (int) $post['library_id'] ) : 0,
			'leader_name'  => isset( $post['leader_name'] ) ? sanitize_text_field( $post['leader_name'] ) : '',
			'leader_phone' => isset( $post['leader_phone'] ) ? sanitize_text_field( $post['leader_phone'] ) : '',
		);
	}

	/**
	 * Sanitizes department update data.
	 *
	 * @return array<string, mixed>
	 */
	private function sanitize_department_update_data(): array {
		$post   = wp_unslash( $_POST );
		$status = isset( $post['status'] ) ? sanitize_key( $post['status'] ) : 'ativo';

		if ( ! array_key_exists( $status, self::status_options() ) ) {
			$status = 'ativo';
		}

		return array_merge(
			array(
				'name'         => isset( $post['name'] ) ? sanitize_text_field( $post['name'] ) : '',
				'leader_name'  => isset( $post['leader_name'] ) ? sanitize_text_field( $post['leader_name'] ) : '',
				'leader_phone' => isset( $post['leader_phone'] ) ? sanitize_text_field( $post['leader_phone'] ) : '',
				'status'       => $status,
			),
			$this->sanitize_feature_flags()
		);
	}

	/**
	 * Sanitizes component data.
	 *
	 * @param array<string, mixed> $department Department.
	 * @return array<string, mixed>
	 */
	private function sanitize_component_data( array $department ): array {
		$post = wp_unslash( $_POST );
		$member_id = isset( $post['member_id'] ) ? max( 0, (int) $post['member_id'] ) : 0;
		$member    = $member_id > 0 ? $this->get_member_for_church( $member_id, (int) $department['church_id'] ) : null;

		return array(
			'department_id' => (int) $department['id'],
			'church_id'     => (int) $department['church_id'],
			'member_id'     => $member_id,
			'name'          => $member ? (string) $member['full_name'] : ( isset( $post['name'] ) ? sanitize_text_field( $post['name'] ) : '' ),
			'phone'         => $member ? (string) $member['phone'] : ( isset( $post['phone'] ) ? sanitize_text_field( $post['phone'] ) : '' ),
			'function_name' => isset( $post['function_name'] ) ? sanitize_text_field( $post['function_name'] ) : '',
			'is_leader'     => 0,
			'status'        => 'ativo',
		);
	}

	/**
	 * Sanitizes component update data.
	 *
	 * @return array<string, mixed>
	 */
	private function sanitize_component_update_data(): array {
		$post   = wp_unslash( $_POST );
		$status = isset( $post['status'] ) ? sanitize_key( $post['status'] ) : 'ativo';

		if ( ! array_key_exists( $status, self::status_options() ) ) {
			$status = 'ativo';
		}

		return array(
			'name'          => isset( $post['name'] ) ? sanitize_text_field( $post['name'] ) : '',
			'phone'         => isset( $post['phone'] ) ? sanitize_text_field( $post['phone'] ) : '',
			'function_name' => isset( $post['function_name'] ) ? sanitize_text_field( $post['function_name'] ) : '',
			'status'        => $status,
		);
	}

	/**
	 * Sanitizes department feature flags.
	 *
	 * @return array<string, int>
	 */
	private function sanitize_feature_flags(): array {
		$post = wp_unslash( $_POST );

		return array(
			'has_chat'          => isset( $post['has_chat'] ) ? 1 : 0,
			'has_notices'       => isset( $post['has_notices'] ) ? 1 : 0,
			'has_files'         => isset( $post['has_files'] ) ? 1 : 0,
			'has_birthdays'     => isset( $post['has_birthdays'] ) ? 1 : 0,
			'has_members'       => isset( $post['has_members'] ) ? 1 : 0,
			'has_schedules'     => isset( $post['has_schedules'] ) ? 1 : 0,
			'has_confirmations' => isset( $post['has_confirmations'] ) ? 1 : 0,
			'has_swaps'         => isset( $post['has_swaps'] ) ? 1 : 0,
			'has_statistics'    => isset( $post['has_statistics'] ) ? 1 : 0,
		);
	}

	/**
	 * Validates library data.
	 *
	 * @param array<string, mixed> $data Data.
	 * @return array<int, string>
	 */
	private function validate_library_data( array $data ): array {
		$errors = array();

		if ( '' === $data['name'] ) {
			$errors[] = __( 'Informe o nome do departamento.', 'iadal-gestao-ministerial' );
		}

		if ( '' === $data['slug'] ) {
			$errors[] = __( 'Informe o identificador do departamento.', 'iadal-gestao-ministerial' );
		}

		if ( strlen( (string) $data['name'] ) > 190 || strlen( (string) $data['slug'] ) > 120 ) {
			$errors[] = __( 'Nome ou identificador do departamento excede o tamanho permitido.', 'iadal-gestao-ministerial' );
		}

		return $errors;
	}

	/**
	 * Validates activation data.
	 *
	 * @param array<string, mixed> $data Data.
	 * @return array<int, string>
	 */
	private function validate_activation_data( array $data ): array {
		$errors = array();

		if ( empty( $data['church_id'] ) || ! $this->church_exists( (int) $data['church_id'] ) ) {
			$errors[] = __( 'Selecione uma congregacao valida.', 'iadal-gestao-ministerial' );
		}

		if ( empty( $data['library_id'] ) ) {
			$errors[] = __( 'Selecione um departamento da biblioteca.', 'iadal-gestao-ministerial' );
		}

		if ( '' === $data['leader_name'] ) {
			$errors[] = __( 'Informe o nome do lider.', 'iadal-gestao-ministerial' );
		}

		if ( strlen( (string) $data['leader_name'] ) > 190 || strlen( (string) $data['leader_phone'] ) > 30 ) {
			$errors[] = __( 'Dados do lider excedem o tamanho permitido.', 'iadal-gestao-ministerial' );
		}

		return $errors;
	}

	/**
	 * Validates department update data.
	 *
	 * @param array<string, mixed> $data Data.
	 * @return array<int, string>
	 */
	private function validate_department_update_data( array $data ): array {
		$errors = array();

		if ( '' === $data['name'] ) {
			$errors[] = __( 'Informe o nome do departamento.', 'iadal-gestao-ministerial' );
		}

		if ( strlen( (string) $data['name'] ) > 190 || strlen( (string) $data['leader_name'] ) > 190 || strlen( (string) $data['leader_phone'] ) > 30 ) {
			$errors[] = __( 'Dados do departamento excedem o tamanho permitido.', 'iadal-gestao-ministerial' );
		}

		return $errors;
	}

	/**
	 * Validates component data.
	 *
	 * @param array<string, mixed> $data Data.
	 * @return array<int, string>
	 */
	private function validate_component_data( array $data ): array {
		$errors = array();

		if ( '' === $data['name'] ) {
			$errors[] = __( 'Informe o nome do componente.', 'iadal-gestao-ministerial' );
		}

		if ( ! empty( $data['member_id'] ) && ! $this->get_member_for_church( (int) $data['member_id'], (int) $data['church_id'] ) ) {
			$errors[] = __( 'O membro selecionado nao pertence a congregacao do departamento.', 'iadal-gestao-ministerial' );
		}

		if ( strlen( (string) $data['name'] ) > 190 || strlen( (string) $data['phone'] ) > 30 || strlen( (string) $data['function_name'] ) > 120 ) {
			$errors[] = __( 'Dados do componente excedem o tamanho permitido.', 'iadal-gestao-ministerial' );
		}

		return $errors;
	}

	/**
	 * Creates a department leader internal user.
	 *
	 * @param int    $church_id Church ID.
	 * @param string $name Leader name.
	 * @param string $phone Leader phone.
	 * @return array<string, mixed>|WP_Error
	 */
	private function create_leader_user( int $church_id, string $name, string $phone ) {
		$password = wp_generate_password( 14, true, true );
		$login    = $this->generate_unique_login( $name, 'lider' );
		$user_id  = $this->repository->create_internal_user(
			array(
				'name'                  => $name,
				'login'                 => $login,
				'password_hash'         => wp_hash_password( $password ),
				'password_generated_at' => IADAL_Database::now(),
				'role'                  => 'lider_departamento',
				'church_id'             => $church_id,
				'phone'                 => $phone,
				'status'                => 'ativo',
				'must_change_password'  => 1,
			)
		);

		if ( ! $user_id ) {
			return new WP_Error( 'iadal_department_leader_user', __( 'Nao foi possivel criar o usuario do lider.', 'iadal-gestao-ministerial' ) );
		}

		return array(
			'user_id'  => (int) $user_id,
			'login'    => $login,
			'password' => $password,
		);
	}

	/**
	 * Creates a component internal user.
	 *
	 * @param array<string, mixed> $department Department data.
	 * @param string               $name Component name.
	 * @param string               $phone Component phone.
	 * @return array<string, mixed>|WP_Error
	 */
	private function create_component_user( array $department, string $name, string $phone ) {
		$password = wp_generate_password( 14, true, true );
		$login    = $this->generate_unique_login( $name, 'comp' );
		$user_id  = $this->repository->create_internal_user(
			array(
				'name'                  => $name,
				'login'                 => $login,
				'password_hash'         => wp_hash_password( $password ),
				'password_generated_at' => IADAL_Database::now(),
				'role'                  => 'componente_departamento',
				'church_id'             => (int) $department['church_id'],
				'department_id'         => (int) $department['id'],
				'phone'                 => $phone,
				'status'                => 'ativo',
				'must_change_password'  => 1,
			)
		);

		if ( ! $user_id ) {
			return new WP_Error( 'iadal_department_component_user', __( 'Nao foi possivel criar o usuario do componente.', 'iadal-gestao-ministerial' ) );
		}

		return array(
			'user_id'  => (int) $user_id,
			'login'    => $login,
			'password' => $password,
		);
	}

	/**
	 * Builds department instance data from library.
	 *
	 * @param int                  $church_id Church ID.
	 * @param array<string, mixed> $library Library item.
	 * @param int                  $leader_user_id Leader user ID.
	 * @param string               $leader_name Leader name.
	 * @param string               $leader_phone Leader phone.
	 * @return array<string, mixed>
	 */
	private function department_data_from_library( int $church_id, array $library, int $leader_user_id, string $leader_name, string $leader_phone ): array {
		$fields = array(
			'has_chat',
			'has_notices',
			'has_files',
			'has_birthdays',
			'has_members',
			'has_schedules',
			'has_confirmations',
			'has_swaps',
			'has_statistics',
		);
		$data = array(
			'church_id'       => $church_id,
			'library_id'      => (int) $library['id'],
			'name'            => (string) $library['name'],
			'slug'            => (string) $library['slug'],
			'type'            => (string) $library['type'],
			'leader_user_id'  => $leader_user_id,
			'leader_name'     => $leader_name,
			'leader_phone'    => $leader_phone,
			'status'          => 'ativo',
		);

		foreach ( $fields as $field ) {
			$data[ $field ] = (int) ( $library[ $field ] ?? 0 );
		}

		return $data;
	}

	/**
	 * Gets congregations for selects.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function get_congregations_for_select(): array {
		if ( ! class_exists( 'IADAL_Congregations_Repository' ) ) {
			return array();
		}

		$repository = new IADAL_Congregations_Repository();

		return $repository->all( array( 'limit' => 500 ) );
	}

	/**
	 * Gets members from one congregation for component linking.
	 *
	 * @param int $church_id Church ID.
	 * @return array<int, array<string, mixed>>
	 */
	private function get_members_for_church( int $church_id ): array {
		if ( ! class_exists( 'IADAL_Members_Repository' ) ) {
			return array();
		}

		$repository = new IADAL_Members_Repository();

		return $repository->all(
			array(
				'church_id' => $church_id,
				'status'    => 'ativo',
				'limit'     => 500,
			)
		);
	}

	/**
	 * Gets one member and validates church ownership.
	 *
	 * @param int $member_id Member ID.
	 * @param int $church_id Church ID.
	 * @return array<string, mixed>|null
	 */
	private function get_member_for_church( int $member_id, int $church_id ): ?array {
		if ( ! class_exists( 'IADAL_Members_Repository' ) ) {
			return null;
		}

		$repository = new IADAL_Members_Repository();
		$member     = $repository->find( $member_id );

		if ( ! $member || (int) ( $member['church_id'] ?? 0 ) !== $church_id ) {
			return null;
		}

		return $member;
	}

	/**
	 * Checks if a congregation exists.
	 *
	 * @param int $church_id Church ID.
	 * @return bool
	 */
	private function church_exists( int $church_id ): bool {
		foreach ( $this->get_congregations_for_select() as $congregation ) {
			if ( (int) $congregation['id'] === $church_id ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Generates a unique internal login.
	 *
	 * @param string $name Name.
	 * @param string $prefix Prefix.
	 * @return string
	 */
	private function generate_unique_login( string $name, string $prefix ): string {
		$base  = sanitize_title( remove_accents( $name ) );
		$base  = $base ? str_replace( '-', '.', $base ) : 'usuario';
		$base  = strtolower( $prefix . '.' . $base );
		$login = substr( $base, 0, 110 );
		$count = 1;

		while ( $this->repository->login_exists( $login ) ) {
			$count++;
			$login = substr( $base, 0, 105 ) . '.' . $count;
		}

		return $login;
	}

	/**
	 * Gets component user login.
	 *
	 * @param int $user_id User ID.
	 * @return string
	 */
	private function get_component_login( int $user_id ): string {
		return $this->repository->user_login( $user_id );
	}

	/**
	 * Stores temporary credentials for one-time display.
	 *
	 * @param int                               $department_id Department ID.
	 * @param array<int, array<string, string>> $credentials Credentials.
	 * @return string
	 */
	private function store_credentials_transient( int $department_id, array $credentials ): string {
		$key = get_current_user_id() . '_' . wp_generate_uuid4();

		set_transient(
			'iadal_department_credentials_' . $key,
			array(
				'current_user'  => get_current_user_id(),
				'department_id' => $department_id,
				'credentials'   => $this->encrypt_credentials( $credentials ),
			),
			2 * MINUTE_IN_SECONDS
		);

		return $key;
	}

	/**
	 * Gets one-time temporary credentials.
	 *
	 * @param string $key Credential key.
	 * @param int    $department_id Department ID.
	 * @return array<int, array<string, string>>
	 */
	private function get_credentials_transient( string $key, int $department_id ): array {
		if ( '' === $key ) {
			return array();
		}

		$payload = get_transient( 'iadal_department_credentials_' . $key );

		if (
			! is_array( $payload )
			|| (int) ( $payload['current_user'] ?? 0 ) !== get_current_user_id()
			|| (int) ( $payload['department_id'] ?? 0 ) !== $department_id
			|| empty( $payload['credentials'] )
		) {
			return array();
		}

		delete_transient( 'iadal_department_credentials_' . $key );

		return $this->decrypt_credentials( (string) $payload['credentials'] );
	}

	/**
	 * Encrypts temporary credentials for transient storage.
	 *
	 * @param array<int, array<string, string>> $credentials Credentials.
	 * @return string
	 */
	private function encrypt_credentials( array $credentials ): string {
		if ( ! function_exists( 'openssl_encrypt' ) ) {
			return '';
		}

		$iv     = random_bytes( 16 );
		$key    = hash( 'sha256', wp_salt( 'auth' ), true );
		$cipher = openssl_encrypt( wp_json_encode( $credentials ), 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv );

		if ( false === $cipher ) {
			return '';
		}

		return base64_encode( $iv . $cipher ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
	}

	/**
	 * Decrypts temporary credentials from transient storage.
	 *
	 * @param string $payload Encrypted payload.
	 * @return array<int, array<string, string>>
	 */
	private function decrypt_credentials( string $payload ): array {
		if ( ! function_exists( 'openssl_decrypt' ) ) {
			return array();
		}

		$raw = base64_decode( $payload, true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode

		if ( false === $raw || strlen( $raw ) <= 16 ) {
			return array();
		}

		$iv     = substr( $raw, 0, 16 );
		$cipher = substr( $raw, 16 );
		$key    = hash( 'sha256', wp_salt( 'auth' ), true );
		$json   = openssl_decrypt( $cipher, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv );

		if ( false === $json ) {
			return array();
		}

		$credentials = json_decode( $json, true );

		return is_array( $credentials ) ? $credentials : array();
	}

	/**
	 * Includes a template.
	 *
	 * @param string               $template Template path.
	 * @param array<string, mixed> $variables Variables.
	 * @return void
	 */
	private function include_template( string $template, array $variables = array() ): void {
		foreach ( $variables as $key => $value ) {
			${$key} = $value;
		}

		include IADAL_GESTAO_DIR . 'templates/' . $template;
	}

	/**
	 * Redirects with transient-backed notices.
	 *
	 * @param string               $page Page slug.
	 * @param array<string, mixed> $args Args.
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

		wp_safe_redirect( add_query_arg( array_merge( array( 'page' => $page ), $args ), admin_url( 'admin.php' ) ) );
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
