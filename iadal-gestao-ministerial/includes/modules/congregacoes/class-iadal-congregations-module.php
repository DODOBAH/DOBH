<?php
/**
 * Congregations module bootstrap.
 *
 * @package IADAL_Gestao_Ministerial
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the congregations module hooks.
 */
class IADAL_Congregations_Module {

	/**
	 * Congregations controller.
	 *
	 * @var IADAL_Congregations_Controller
	 */
	private IADAL_Congregations_Controller $controller;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$repository       = new IADAL_Congregations_Repository();
		$this->controller = new IADAL_Congregations_Controller( $repository );
	}

	/**
	 * Registers WordPress hooks.
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
	 * Ensures administrators can access the congregations module.
	 *
	 * @return void
	 */
	private function ensure_capabilities(): void {
		if ( IADAL_GESTAO_VERSION === get_option( 'iadal_congregations_capabilities_version', '' ) ) {
			return;
		}

		$administrator = get_role( 'administrator' );

		if ( ! $administrator ) {
			return;
		}

		$capabilities = array(
			'iadal_view_congregations',
			'iadal_create_congregations',
			'iadal_edit_congregations',
			'iadal_delete_congregations',
			'iadal_block_congregations',
			'iadal_manage_congregation_credentials',
			'iadal_manage_congregations',
		);

		foreach ( $capabilities as $capability ) {
			$administrator->add_cap( $capability );
		}

		update_option( 'iadal_congregations_capabilities_version', IADAL_GESTAO_VERSION );
	}

	/**
	 * Registers admin menu pages for congregations.
	 *
	 * @return void
	 */
	public function register_admin_menu(): void {
		add_menu_page(
			__( 'IADAL - Congregacoes', 'iadal-gestao-ministerial' ),
			__( 'IADAL Congregacoes', 'iadal-gestao-ministerial' ),
			'iadal_view_congregations',
			'iadal-congregations',
			array( $this->controller, 'render_list_page' ),
			'dashicons-admin-multisite',
			55
		);

		add_submenu_page(
			'iadal-congregations',
			__( 'Criar Congregacao', 'iadal-gestao-ministerial' ),
			__( 'Criar Congregacao', 'iadal-gestao-ministerial' ),
			'iadal_create_congregations',
			'iadal-congregations-create',
			array( $this->controller, 'render_create_page' )
		);

		add_submenu_page(
			null,
			__( 'Editar Congregacao', 'iadal-gestao-ministerial' ),
			__( 'Editar Congregacao', 'iadal-gestao-ministerial' ),
			'iadal_edit_congregations',
			'iadal-congregations-edit',
			array( $this->controller, 'render_edit_page' )
		);

		add_submenu_page(
			null,
			__( 'Credenciais da Congregacao', 'iadal-gestao-ministerial' ),
			__( 'Credenciais da Congregacao', 'iadal-gestao-ministerial' ),
			'iadal_manage_congregation_credentials',
			'iadal-congregations-credentials',
			array( $this->controller, 'render_credentials_page' )
		);
	}

	/**
	 * Enqueues assets on congregation screens.
	 *
	 * @param string $hook_suffix Current admin hook suffix.
	 * @return void
	 */
	public function enqueue_admin_assets( string $hook_suffix ): void {
		if ( false === strpos( $hook_suffix, 'iadal-congregations' ) ) {
			return;
		}

		wp_enqueue_style(
			'iadal-members',
			IADAL_GESTAO_URL . 'assets/css/iadal-members.css',
			array(),
			IADAL_GESTAO_VERSION
		);

		wp_enqueue_style(
			'iadal-congregations',
			IADAL_GESTAO_URL . 'assets/css/iadal-congregations.css',
			array( 'iadal-members' ),
			IADAL_GESTAO_VERSION
		);

		wp_enqueue_script(
			'iadal-congregations',
			IADAL_GESTAO_URL . 'assets/js/iadal-congregations.js',
			array(),
			IADAL_GESTAO_VERSION,
			true
		);
	}
}
