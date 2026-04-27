<?php
/**
 * Plugin Name: Ghost Manager
 * Description: Subscription account management, WooCommerce integration, and related tools.
 * Version: 1.0.3
 * Author: Ghost
 * Text Domain: ghost-manager
 *
 * @package Ghost_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GHOST_MANAGER_VERSION', '1.0.3' );
define( 'GHOST_MANAGER_PATH', plugin_dir_path( __FILE__ ) );
define( 'GHOST_MANAGER_URL', plugin_dir_url( __FILE__ ) );

/**
 * GitHub repository URL for in-dashboard updates (trailing slash).
 * Override in wp-config.php: define( 'GHOST_MANAGER_GITHUB_REPO', 'https://github.com/ORG/REPO/' );
 */
if ( ! defined( 'GHOST_MANAGER_GITHUB_REPO' ) ) {
	define( 'GHOST_MANAGER_GITHUB_REPO', 'https://github.com/jcross-tech/ghost-manager/' );
}

require_once GHOST_MANAGER_PATH . 'includes/helpers/options.php';
require_once GHOST_MANAGER_PATH . 'includes/helpers/features.php';
require_once GHOST_MANAGER_PATH . 'includes/license/license.php';

/**
 * Load textdomain.
 */
function ghost_manager_load_textdomain() {
	load_plugin_textdomain( 'ghost-manager', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'ghost_manager_load_textdomain' );

/**
 * Plugins list row: link to Ghost Manager settings (same capability as the admin menu).
 *
 * @param string[] $links Existing action links.
 * @return string[]
 */
function ghost_manager_plugin_action_links( $links ) {
	if ( current_user_can( 'manage_options' ) ) {
		array_unshift(
			$links,
			'<a href="' . esc_url( admin_url( 'admin.php?page=ghost-manager' ) ) . '">' . esc_html__( 'Settings', 'ghost-manager' ) . '</a>'
		);
	}
	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'ghost_manager_plugin_action_links' );

/**
 * Check for newer versions on GitHub (requires GitHub Releases with a zip attached; tag = version e.g. 1.0.4).
 *
 * Uses Plugin Update Checker (MIT) in /plugin-update-checker/.
 */
function ghost_manager_register_github_updates() {
	if ( defined( 'GHOST_MANAGER_DISABLE_GITHUB_UPDATES' ) && GHOST_MANAGER_DISABLE_GITHUB_UPDATES ) {
		return;
	}
	$puc = GHOST_MANAGER_PATH . 'plugin-update-checker/plugin-update-checker.php';
	if ( ! is_readable( $puc ) ) {
		return;
	}
	$repo = apply_filters( 'ghost_manager_github_update_repo_url', GHOST_MANAGER_GITHUB_REPO );
	if ( ! is_string( $repo ) || '' === trim( $repo ) ) {
		return;
	}
	require_once $puc;
	$checker = \YahnisElsts\PluginUpdateChecker\v5p6\PucFactory::buildUpdateChecker( $repo, __FILE__, 'ghost-manager' );
	$api     = $checker->getVcsApi();
	if ( $api && method_exists( $api, 'enableReleaseAssets' ) ) {
		$api->enableReleaseAssets();
	}
}
add_action( 'plugins_loaded', 'ghost_manager_register_github_updates', 0 );

/**
 * Bootstrap plugin modules.
 */
function ghost_manager_init() {
	require_once GHOST_MANAGER_PATH . 'includes/accounts/accounts.php';

	// Admin UI (load hook save runs in full admin context; file only registers hooks).
	require_once GHOST_MANAGER_PATH . 'includes/admin/admin-settings.php';

	if ( ghost_manager_is_feature_enabled( 'accounts_manager' ) ) {
		require_once GHOST_MANAGER_PATH . 'includes/admin/accounts-manager.php';
	}

	require_once GHOST_MANAGER_PATH . 'includes/frontend/frontend.php';

	if ( class_exists( 'WooCommerce' ) && ghost_manager_is_feature_enabled( 'woocommerce' ) ) {
		require_once GHOST_MANAGER_PATH . 'includes/woocommerce/woocommerce.php';
	}

	if ( ghost_manager_is_feature_enabled( 'reseller_restrictions' ) ) {
		require_once GHOST_MANAGER_PATH . 'includes/features/reseller-restrictions.php';
	}

	if ( ghost_manager_is_feature_enabled( 'discord_widget' ) ) {
		require_once GHOST_MANAGER_PATH . 'includes/features/discord-widget.php';
	}
}
add_action( 'plugins_loaded', 'ghost_manager_init', 5 );

register_activation_hook(
	__FILE__,
	function () {
		if ( ! get_option( 'ghost_manager_settings' ) ) {
			update_option( 'ghost_manager_settings', ghost_manager_settings_pack_for_db( ghost_manager_default_settings() ) );
		}
	}
);
