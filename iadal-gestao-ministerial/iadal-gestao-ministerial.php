<?php
/**
 * Plugin Name: IADAL Gestao Ministerial
 * Description: Sistema Integrado IADAL Alto Lage - modulo inicial de cadastro de membros.
 * Version: 0.1.0
 * Requires PHP: 8.0
 * Author: IADAL Alto Lage
 * Text Domain: iadal-gestao-ministerial
 *
 * @package IADAL_Gestao_Ministerial
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'IADAL_GESTAO_VERSION', '0.1.0' );
define( 'IADAL_GESTAO_FILE', __FILE__ );
define( 'IADAL_GESTAO_DIR', plugin_dir_path( __FILE__ ) );
define( 'IADAL_GESTAO_URL', plugin_dir_url( __FILE__ ) );

require_once IADAL_GESTAO_DIR . 'includes/class-iadal-activator.php';
require_once IADAL_GESTAO_DIR . 'includes/database/class-iadal-database.php';
require_once IADAL_GESTAO_DIR . 'includes/database/class-iadal-members-schema.php';
require_once IADAL_GESTAO_DIR . 'includes/modules/membros/class-iadal-members-repository.php';
require_once IADAL_GESTAO_DIR . 'includes/modules/membros/class-iadal-members-controller.php';
require_once IADAL_GESTAO_DIR . 'includes/modules/membros/class-iadal-members-module.php';

register_activation_hook( __FILE__, array( 'IADAL_Activator', 'activate' ) );

/**
 * Boots the plugin after WordPress loads all plugins.
 *
 * @return void
 */
function iadal_gestao_ministerial_boot(): void {
	$members_module = new IADAL_Members_Module();
	$members_module->run();
}

add_action( 'plugins_loaded', 'iadal_gestao_ministerial_boot' );
