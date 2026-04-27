<?php
/**
 * Ghost Manager admin settings page, assets, and save handler.
 *
 * @package Ghost_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Allowed settings tabs.
 *
 * @return array Slug => label.
 */
function ghost_manager_settings_tabs() {
	return array(
		'general'      => __( 'General', 'ghost-manager' ),
		'features'     => __( 'Features', 'ghost-manager' ),
		'urls'         => __( 'URLs & renewals', 'ghost-manager' ),
		'emails'       => __( 'Emails', 'ghost-manager' ),
		'cron'         => __( 'Expiry cron', 'ghost-manager' ),
		'integrations' => __( 'WooCommerce & checkout', 'ghost-manager' ),
	);
}

/**
 * Current tab slug.
 *
 * @return string
 */
function ghost_manager_get_settings_tab() {
	$tabs = ghost_manager_settings_tabs();
	$tab  = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'general';
	if ( ! isset( $tabs[ $tab ] ) ) {
		$tab = 'general';
	}
	return $tab;
}

/**
 * Redirect legacy Settings submenu URL to top-level menu.
 */
function ghost_manager_redirect_legacy_settings_url() {
	global $pagenow;
	if ( 'options-general.php' !== $pagenow || empty( $_GET['page'] ) ) {
		return;
	}
	if ( 'ghost-manager' !== $_GET['page'] ) {
		return;
	}
	wp_safe_redirect( admin_url( 'admin.php?page=ghost-manager' ) );
	exit;
}
add_action( 'admin_init', 'ghost_manager_redirect_legacy_settings_url', 1 );

/**
 * Persist settings on POST back to this screen (load hook runs before output; avoids admin-post.php and core using $_REQUEST['action']).
 */
function ghost_manager_maybe_save_settings() {
	if ( empty( $_POST['ghost_manager_save'] ) ) {
		return;
	}

	ghost_manager_debug_log(
		'save: handler start',
		array(
			'user_id'   => get_current_user_id(),
			'has_nonce' => isset( $_POST['_ghost_manager_nonce'] ),
		)
	);

	if ( ! current_user_can( 'manage_options' ) ) {
		ghost_manager_debug_log( 'save: aborted — current user cannot manage_options' );
		return;
	}

	check_admin_referer( 'ghost_manager_save_settings', '_ghost_manager_nonce' );

	$tabs = ghost_manager_settings_tabs();
	$tab  = isset( $_POST['ghost_manager_return_tab'] ) ? sanitize_key( wp_unslash( $_POST['ghost_manager_return_tab'] ) ) : 'general';
	if ( ! isset( $tabs[ $tab ] ) ) {
		$tab = 'general';
	}

	if ( ! isset( $_POST['ghost_manager_settings'] ) || ! is_array( $_POST['ghost_manager_settings'] ) ) {
		ghost_manager_debug_log( 'save: missing ghost_manager_settings in POST', array( 'tab' => $tab ) );
		wp_safe_redirect(
			add_query_arg(
				array(
					'page'                => 'ghost-manager',
					'tab'                 => $tab,
					'ghost_manager_error' => 'nodata',
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	$raw = wp_unslash( $_POST['ghost_manager_settings'] );
	ghost_manager_debug_log(
		'save: POST received',
		array(
			'tab'              => $tab,
			'top_level_keys'   => array_keys( $raw ),
			'discord_tooltip'  => isset( $raw['strings']['discord_tooltip'] ) ? $raw['strings']['discord_tooltip'] : '(absent)',
			'post_field_count' => count( $_POST, COUNT_RECURSIVE ),
		)
	);
	$sanitized = ghost_manager_sanitize_settings( $raw );
	$saved     = ghost_manager_update_settings_option( $sanitized );

	if ( ! $saved ) {
		wp_safe_redirect(
			add_query_arg(
				array(
					'page'                => 'ghost-manager',
					'tab'                 => $tab,
					'ghost_manager_error' => 'savefailed',
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'                  => 'ghost-manager',
				'tab'                   => $tab,
				'ghost_manager_updated' => '1',
			),
			admin_url( 'admin.php' )
		)
	);
	exit;
}
add_action( 'load-toplevel_page_ghost-manager', 'ghost_manager_maybe_save_settings', 0 );
add_action( 'load-ghost-manager_page_ghost-manager', 'ghost_manager_maybe_save_settings', 0 );

/**
 * Screen IDs for the settings UI (top-level or explicit Settings submenu under Ghost Manager).
 *
 * @return string[]
 */
function ghost_manager_settings_screen_ids() {
	return array( 'toplevel_page_ghost-manager', 'ghost-manager_page_ghost-manager' );
}

/**
 * Admin hook suffixes for settings + Logs screens (enqueue).
 *
 * @return string[]
 */
function ghost_manager_settings_related_hook_suffixes() {
	return array_merge(
		ghost_manager_settings_screen_ids(),
		array( 'ghost-manager_page_ghost-manager-save-trace' )
	);
}

/**
 * On the Ghost Manager screen, remind admins how to capture logs when WP_DEBUG is on.
 */
function ghost_manager_debug_setup_notice() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || ! in_array( $screen->id, ghost_manager_settings_screen_ids(), true ) ) {
		return;
	}
	if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
		return;
	}
	if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
		return;
	}
	echo '<div class="notice notice-warning"><p>';
	echo esc_html__( 'Ghost Manager: WP_DEBUG is on but WP_DEBUG_LOG is not true, so WordPress may not write to wp-content/debug.log. Add define( \'WP_DEBUG_LOG\', true ); to wp-config.php, or check your host’s PHP error log for lines starting with [Ghost Manager].', 'ghost-manager' );
	echo '</p></div>';
}
add_action( 'admin_notices', 'ghost_manager_debug_setup_notice' );

/**
 * Suggest dedicated save-trace file when debugging (many hosts do not route PHP error_log to debug.log).
 */
function ghost_manager_trace_saves_notice() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || ! in_array( $screen->id, array_merge( ghost_manager_settings_screen_ids(), array( 'ghost-manager_page_ghost-manager-save-trace' ) ), true ) ) {
		return;
	}
	if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
		return;
	}
	if ( defined( 'GHOST_MANAGER_TRACE_SAVES' ) && GHOST_MANAGER_TRACE_SAVES ) {
		return;
	}
	echo '<div class="notice notice-info is-dismissible"><p>';
	echo esc_html__( 'Ghost Manager: If you do not see [Ghost Manager] lines in debug.log after saving, add define( \'GHOST_MANAGER_TRACE_SAVES\', true ); to wp-config.php. View the file under Ghost Manager → Logs (remove the constant when finished).', 'ghost-manager' );
	echo '</p></div>';
}
add_action( 'admin_notices', 'ghost_manager_trace_saves_notice' );

/**
 * Checkbox that always submits a value (hidden 0 + checkbox 1) so unchecked boxes persist correctly.
 *
 * @param string $name    Full input name, e.g. ghost_manager_settings[features][discord_widget].
 * @param bool   $checked Whether checked.
 */
function ghost_manager_hidden_checkbox( $name, $checked ) {
	echo '<input type="hidden" name="' . esc_attr( $name ) . '" value="0" />';
	printf(
		'<input type="checkbox" name="%1$s" value="1" %2$s />',
		esc_attr( $name ),
		checked( $checked, true, false )
	);
}

/**
 * Top-level admin menu (must run on admin_menu, not admin_init, or WordPress denies ?page= access).
 */
function ghost_manager_add_menu_page() {
	add_menu_page(
		__( 'Ghost Manager', 'ghost-manager' ),
		__( 'Ghost Manager', 'ghost-manager' ),
		'manage_options',
		'ghost-manager',
		'ghost_manager_render_settings_page',
		'dashicons-admin-generic',
		56
	);

	/*
	 * Register Settings before any submenu whose slug differs from the parent.
	 * Otherwise WordPress injects a duplicate of the parent as the first submenu item
	 * (see add_submenu_page() in wp-admin/includes/plugin.php).
	 */
	add_submenu_page(
		'ghost-manager',
		__( 'Settings', 'ghost-manager' ),
		__( 'Settings', 'ghost-manager' ),
		'manage_options',
		'ghost-manager',
		'ghost_manager_render_settings_page'
	);

	if ( ghost_manager_is_feature_enabled( 'accounts_manager' ) && function_exists( 'ghost_manager_render_accounts_manager_page' ) ) {
		add_submenu_page(
			'ghost-manager',
			__( 'Customer Manager', 'ghost-manager' ),
			__( 'Customer Manager', 'ghost-manager' ),
			'manage_options',
			'accounts-manager',
			'ghost_manager_render_accounts_manager_page',
			0
		);
	}

	add_submenu_page(
		'ghost-manager',
		__( 'Logs', 'ghost-manager' ),
		__( 'Logs', 'ghost-manager' ),
		'manage_options',
		'ghost-manager-save-trace',
		'ghost_manager_render_trace_log_page'
	);
}
add_action( 'admin_menu', 'ghost_manager_add_menu_page' );

/**
 * Clear trace log (POST from Logs screen).
 */
function ghost_manager_maybe_clear_save_trace_log() {
	if ( empty( $_POST['ghost_manager_clear_trace'] ) ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	check_admin_referer( 'ghost_manager_clear_trace' );
	$path = ghost_manager_save_trace_log_path();
	if ( $path && file_exists( $path ) ) {
		@unlink( $path );
	}
	wp_safe_redirect(
		add_query_arg(
			array(
				'page'          => 'ghost-manager-save-trace',
				'trace_cleared' => '1',
			),
			admin_url( 'admin.php' )
		)
	);
	exit;
}
add_action( 'load-ghost-manager_page_ghost-manager-save-trace', 'ghost_manager_maybe_clear_save_trace_log', 0 );

/**
 * Render Logs submenu (save trace file viewer).
 */
function ghost_manager_render_trace_log_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	require GHOST_MANAGER_PATH . 'includes/admin/views/trace-log.php';
}

/**
 * Admin styles for settings tabs.
 *
 * @param string $hook_suffix Current screen.
 */
function ghost_manager_admin_settings_assets( $hook_suffix ) {
	if ( ! in_array( $hook_suffix, ghost_manager_settings_related_hook_suffixes(), true ) ) {
		return;
	}
	wp_add_inline_style(
		'wp-admin',
		'.ghost-manager-tabs .nav-tab-wrapper { margin-bottom: 0; }
		.ghost-manager-tab-panel { padding-top: 12px; }
		.ghost-manager-master-notice { background: #f0f6fc; border-left: 4px solid #0969da; padding: 8px 12px; margin: 12px 0; }
		.ghost-manager-trace-log { font-family: Consolas, Monaco, monospace; font-size: 12px; line-height: 1.45; white-space: pre-wrap; word-break: break-word; max-height: 70vh; overflow: auto; background: #1e1e1e; color: #d4d4d4; padding: 16px; border-radius: 4px; }'
	);
}
add_action( 'admin_enqueue_scripts', 'ghost_manager_admin_settings_assets' );

/**
 * Render settings page.
 */
function ghost_manager_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	require GHOST_MANAGER_PATH . 'includes/admin/views/settings-page.php';
}
