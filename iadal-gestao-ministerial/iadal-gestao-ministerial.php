<?php
/**
 * Plugin Name: IADAL Gestao Ministerial
 * Description: Sistema Integrado IADAL Alto Lage - modulos iniciais de congregacoes e membros.
 * Version: 0.3.0
 * Requires PHP: 8.0
 * Author: IADAL Alto Lage
 * Text Domain: iadal-gestao-ministerial
 *
 * @package IADAL_Gestao_Ministerial
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'IADAL_GESTAO_VERSION', '0.3.0' );
define( 'IADAL_GESTAO_FILE', __FILE__ );
define( 'IADAL_GESTAO_DIR', plugin_dir_path( __FILE__ ) );
define( 'IADAL_GESTAO_URL', plugin_dir_url( __FILE__ ) );

require_once IADAL_GESTAO_DIR . 'includes/class-iadal-activator.php';
require_once IADAL_GESTAO_DIR . 'includes/class-iadal-audit.php';
require_once IADAL_GESTAO_DIR . 'includes/database/class-iadal-database.php';
require_once IADAL_GESTAO_DIR . 'includes/database/class-iadal-audit-schema.php';
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

	if ( get_option( 'iadal_gestao_upgrade_error', '' ) ) {
		add_action( 'admin_notices', 'iadal_gestao_ministerial_upgrade_notice' );
	}

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
	IADAL_Audit_Schema::create();

	if ( iadal_gestao_ministerial_required_schema_valid() ) {
		delete_option( 'iadal_gestao_upgrade_error' );
		update_option( 'iadal_gestao_version', IADAL_GESTAO_VERSION );
	} else {
		update_option( 'iadal_gestao_upgrade_error', __( 'Falha ao criar ou atualizar as tabelas do plugin IADAL.', 'iadal-gestao-ministerial' ) );
		add_action( 'admin_notices', 'iadal_gestao_ministerial_upgrade_notice' );
	}
}

/**
 * Checks if all required plugin tables exist.
 *
 * @return bool
 */
function iadal_gestao_ministerial_required_tables_exist(): bool {
	return IADAL_Database::tables_exist(
		array(
			IADAL_Database::table( 'members' ),
			IADAL_Database::table( 'member_documents' ),
			IADAL_Database::table( 'churches' ),
			IADAL_Database::table( 'users' ),
			IADAL_Database::table( 'church_modules' ),
			IADAL_Database::table( 'audit_logs' ),
		)
	);
}

/**
 * Checks if required tables and critical columns exist.
 *
 * @return bool
 */
function iadal_gestao_ministerial_required_schema_valid(): bool {
	if ( ! iadal_gestao_ministerial_required_tables_exist() ) {
		return false;
	}

	$required_columns = array(
		IADAL_Database::table( 'members' )          => array( 'id', 'church_id', 'cpf', 'birth_month' ),
		IADAL_Database::table( 'member_documents' ) => array( 'id', 'member_id', 'church_id', 'file_path' ),
		IADAL_Database::table( 'churches' )         => array( 'id', 'name', 'pastor_user_id', 'secretary_user_id' ),
		IADAL_Database::table( 'users' )            => array( 'id', 'login', 'password_hash', 'password_generated_at', 'blocked_by_church_status' ),
		IADAL_Database::table( 'church_modules' )   => array( 'id', 'church_id', 'module_key', 'status' ),
		IADAL_Database::table( 'audit_logs' )       => array( 'id', 'module', 'action', 'entity_type' ),
	);

	foreach ( $required_columns as $table_name => $columns ) {
		foreach ( $columns as $column_name ) {
			if ( ! IADAL_Database::column_exists( $table_name, $column_name ) ) {
				return false;
			}
		}
	}

	return true;
}

/**
 * Shows a migration error notice when schema validation fails.
 *
 * @return void
 */
function iadal_gestao_ministerial_upgrade_notice(): void {
	$error = get_option( 'iadal_gestao_upgrade_error', '' );

	if ( '' === $error ) {
		return;
	}

	echo '<div class="notice notice-error"><p>' . esc_html( $error ) . '</p></div>';
}
