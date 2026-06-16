<?php
/**
 * Departments module bootstrap.
 *
 * @package IADAL_Gestao_Ministerial
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers department module hooks.
 */
class IADAL_Departments_Module {

	/**
	 * Departments controller.
	 *
	 * @var IADAL_Departments_Controller
	 */
	private IADAL_Departments_Controller $controller;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$repository       = new IADAL_Departments_Repository();
		$this->controller = new IADAL_Departments_Controller( $repository );
	}

	/**
	 * Registers hooks.
	 *
	 * @return void
	 */
	public function run(): void {
		$this->ensure_capabilities();

		add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

		$this->controller->register_hooks();
	}

	/**
	 * Ensures administrators have department capabilities.
	 *
	 * @return void
	 */
	private function ensure_capabilities(): void {
		if ( IADAL_GESTAO_VERSION === get_option( 'iadal_departments_capabilities_version', '' ) ) {
			return;
		}

		$administrator = get_role( 'administrator' );

		if ( ! $administrator ) {
			return;
		}

		$capabilities = array(
			'iadal_view_departments',
			'iadal_activate_departments',
			'iadal_edit_departments',
			'iadal_delete_departments',
			'iadal_create_custom_departments',
			'iadal_manage_department_components',
			'iadal_manage_departments',
		);

		foreach ( $capabilities as $capability ) {
			$administrator->add_cap( $capability );
		}

		update_option( 'iadal_departments_capabilities_version', IADAL_GESTAO_VERSION );
	}

	/**
	 * Registers admin menu pages.
	 *
	 * @return void
	 */
	public function register_admin_menu(): void {
		add_menu_page(
			__( 'IADAL - Departamentos', 'iadal-gestao-ministerial' ),
			__( 'IADAL Departamentos', 'iadal-gestao-ministerial' ),
			'iadal_view_departments',
			'iadal-departments',
			array( $this->controller, 'render_list_page' ),
			'dashicons-networking',
			57
		);

		add_submenu_page(
			'iadal-departments',
			__( 'Biblioteca', 'iadal-gestao-ministerial' ),
			__( 'Biblioteca', 'iadal-gestao-ministerial' ),
			'iadal_view_departments',
			'iadal-departments-library',
			array( $this->controller, 'render_library_page' )
		);

		add_submenu_page(
			'iadal-departments',
			__( 'Ativar Departamento', 'iadal-gestao-ministerial' ),
			__( 'Ativar Departamento', 'iadal-gestao-ministerial' ),
			'iadal_activate_departments',
			'iadal-departments-activate',
			array( $this->controller, 'render_activate_page' )
		);

		add_submenu_page(
			'iadal-departments',
			__( 'Criar Personalizado', 'iadal-gestao-ministerial' ),
			__( 'Criar Personalizado', 'iadal-gestao-ministerial' ),
			'iadal_create_custom_departments',
			'iadal-departments-custom',
			array( $this->controller, 'render_create_custom_page' )
		);

		add_submenu_page(
			null,
			__( 'Editar Personalizado', 'iadal-gestao-ministerial' ),
			__( 'Editar Personalizado', 'iadal-gestao-ministerial' ),
			'iadal_create_custom_departments',
			'iadal-departments-library-edit',
			array( $this->controller, 'render_edit_library_page' )
		);

		add_submenu_page(
			null,
			__( 'Editar Departamento', 'iadal-gestao-ministerial' ),
			__( 'Editar Departamento', 'iadal-gestao-ministerial' ),
			'iadal_edit_departments',
			'iadal-departments-edit',
			array( $this->controller, 'render_edit_page' )
		);

		add_submenu_page(
			null,
			__( 'Componentes do Departamento', 'iadal-gestao-ministerial' ),
			__( 'Componentes do Departamento', 'iadal-gestao-ministerial' ),
			'iadal_manage_department_components',
			'iadal-departments-components',
			array( $this->controller, 'render_components_page' )
		);
	}

	/**
	 * Enqueues assets on department screens.
	 *
	 * @param string $hook_suffix Current admin hook suffix.
	 * @return void
	 */
	public function enqueue_admin_assets( string $hook_suffix ): void {
		if ( false === strpos( $hook_suffix, 'iadal-departments' ) ) {
			return;
		}

		wp_enqueue_style(
			'iadal-members',
			IADAL_GESTAO_URL . 'assets/css/iadal-members.css',
			array(),
			IADAL_GESTAO_VERSION
		);

		wp_enqueue_style(
			'iadal-departments',
			IADAL_GESTAO_URL . 'assets/css/iadal-departments.css',
			array( 'iadal-members' ),
			IADAL_GESTAO_VERSION
		);

		wp_enqueue_script(
			'iadal-departments',
			IADAL_GESTAO_URL . 'assets/js/iadal-departments.js',
			array(),
			IADAL_GESTAO_VERSION,
			true
		);
	}
}
