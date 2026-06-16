<?php
/**
 * Plugin Name: IADAL Gestao Ministerial
 * Description: Sistema Integrado IADAL Alto Lage - modulos iniciais de congregacoes e membros.
 * Version: 0.2.0
 * Requires PHP: 8.0
 * Author: IADAL Alto Lage
 * Text Domain: iadal-gestao-ministerial
 *
 * @package IADAL_Gestao_Ministerial
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'IADAL_GESTAO_VERSION', '0.2.0' );
define( 'IADAL_GESTAO_FILE', __FILE__ );
define( 'IADAL_GESTAO_DIR', plugin_dir_path( __FILE__ ) );
define( 'IADAL_GESTAO_URL', plugin_dir_url( __FILE__ ) );

require_once IADAL_GESTAO_DIR . 'includes/class-iadal-activator.php';
require_once IADAL_GESTAO_DIR . 'includes/database/class-iadal-database.php';
require_once IADAL_GESTAO_DIR . 'includes/database/class-iadal-members-schema.php';
require_once IADAL_GESTAO_DIR . 'includes/database/class-iadal-congregations-schema.php';
require_once IADAL_GESTAO_DIR . 'includes/modules/membros/class-iadal-members-repository.php';
require_once IADAL_GESTAO_DIR . 'includes/modules/membros/class-iadal-members-controller.php';
require_once IADAL_GESTAO_DIR . 'includes/modules/membros/class-iadal-members-module.php';
require_once IADAL_GESTAO_DIR . 'includes/modules/congregacoes/class-iadal-congregations-repository.php';
require_once IADAL_GESTAO_DIR . 'includes/modules/congregacoes/class-iadal-congregations-controller.php';
require_once IADAL_GESTAO_DIR . 'includes/modules/congregacoes/class-iadal-congregations-module.php';

register_activation_hook( __FILE__, array( 'IADAL_Activator', 'activate' ) );

/**
 * Boots the plugin after WordPress loads all plugins.
 *
 * @return void
 */
function iadal_gestao_ministerial_boot(): void {
	iadal_gestao_ministerial_maybe_upgrade();

	$members_module = new IADAL_Members_Module();
	$members_module->run();

	$congregations_module = new IADAL_Congregations_Module();
	$congregations_module->run();
}

add_action( 'plugins_loaded', 'iadal_gestao_ministerial_boot' );

/**
 * Runs lightweight database upgrades when the plugin version changes.
 *
 * @return void
 */
function iadal_gestao_ministerial_maybe_upgrade(): void {
	$installed_version = get_option( 'iadal_gestao_version', '' );

	if ( IADAL_GESTAO_VERSION === $installed_version ) {
		return;
	}

	IADAL_Members_Schema::create();
	IADAL_Congregations_Schema::create();

	update_option( 'iadal_gestao_version', IADAL_GESTAO_VERSION );
}
