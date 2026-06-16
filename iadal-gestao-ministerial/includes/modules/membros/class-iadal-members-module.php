<?php
/**
 * Members module bootstrap.
 *
 * @package IADAL_Gestao_Ministerial
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the members module hooks.
 */
class IADAL_Members_Module {

	/**
	 * Members controller.
	 *
	 * @var IADAL_Members_Controller
	 */
	private IADAL_Members_Controller $controller;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$repository       = new IADAL_Members_Repository();
		$this->controller = new IADAL_Members_Controller( $repository );
	}

	/**
	 * Registers WordPress hooks.
	 *
	 * @return void
	 */
	public function run(): void {
		add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

		$this->controller->register_hooks();
	}

	/**
	 * Registers admin menu pages for the members module.
	 *
	 * @return void
	 */
	public function register_admin_menu(): void {
		add_menu_page(
			__( 'IADAL - Membros', 'iadal-gestao-ministerial' ),
			__( 'IADAL Membros', 'iadal-gestao-ministerial' ),
			'manage_options',
			'iadal-members',
			array( $this->controller, 'render_list_page' ),
			'dashicons-groups',
			56
		);

		add_submenu_page(
			'iadal-members',
			__( 'Cadastrar Membro', 'iadal-gestao-ministerial' ),
			__( 'Cadastrar Membro', 'iadal-gestao-ministerial' ),
			'manage_options',
			'iadal-members-create',
			array( $this->controller, 'render_create_page' )
		);

		add_submenu_page(
			'iadal-members',
			__( 'Aniversariantes', 'iadal-gestao-ministerial' ),
			__( 'Aniversariantes', 'iadal-gestao-ministerial' ),
			'manage_options',
			'iadal-members-birthdays',
			array( $this->controller, 'render_birthdays_page' )
		);

		add_submenu_page(
			null,
			__( 'Editar Membro', 'iadal-gestao-ministerial' ),
			__( 'Editar Membro', 'iadal-gestao-ministerial' ),
			'manage_options',
			'iadal-members-edit',
			array( $this->controller, 'render_edit_page' )
		);
	}

	/**
	 * Enqueues CSS and JavaScript only on this module screens.
	 *
	 * @param string $hook_suffix Current admin hook suffix.
	 * @return void
	 */
	public function enqueue_admin_assets( string $hook_suffix ): void {
		if ( false === strpos( $hook_suffix, 'iadal-members' ) ) {
			return;
		}

		wp_enqueue_style(
			'iadal-members',
			IADAL_GESTAO_URL . 'assets/css/iadal-members.css',
			array(),
			IADAL_GESTAO_VERSION
		);

		wp_enqueue_script(
			'iadal-members',
			IADAL_GESTAO_URL . 'assets/js/iadal-members.js',
			array(),
			IADAL_GESTAO_VERSION,
			true
		);
	}
}
