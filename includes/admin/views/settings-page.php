<?php
/**
 * Settings page shell (tabs + single form).
 *
 * @package Ghost_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Single option ghost_manager_settings: read raw from DB, merge defaults for display (same as ghost_manager_get_settings()).
$settings = ghost_manager_get_settings();
$tab      = ghost_manager_get_settings_tab();
$tabs     = ghost_manager_settings_tabs();
?>
<div class="wrap ghost-manager-settings">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<?php settings_errors(); ?>

	<?php if ( ! empty( $_GET['ghost_manager_updated'] ) ) : ?>
		<div id="message" class="updated notice is-dismissible"><p><?php esc_html_e( 'Settings saved.', 'ghost-manager' ); ?></p></div>
	<?php endif; ?>

	<?php if ( ! empty( $_GET['ghost_manager_error'] ) ) : ?>
		<?php if ( 'nodata' === $_GET['ghost_manager_error'] ) : ?>
			<div class="notice notice-error is-dismissible"><p><?php esc_html_e( 'Could not read any settings from the form (server limit may have dropped fields). Try again or ask your host to raise max_input_vars.', 'ghost-manager' ); ?></p></div>
		<?php elseif ( 'savefailed' === $_GET['ghost_manager_error'] ) : ?>
			<div class="notice notice-error is-dismissible"><p><?php esc_html_e( 'Settings could not be saved to the database. Check Ghost Manager → Logs for details, your host’s PHP/MySQL error log, and that the database uses utf8mb4 if you use emoji in text fields.', 'ghost-manager' ); ?></p></div>
		<?php endif; ?>
	<?php endif; ?>

	<form id="ghost-manager-settings-form" action="<?php echo esc_url( admin_url( 'admin.php?page=ghost-manager' ) ); ?>" method="post" class="ghost-manager-tabs">
		<input type="hidden" name="ghost_manager_save" value="1" />
		<?php wp_nonce_field( 'ghost_manager_save_settings', '_ghost_manager_nonce' ); ?>
		<input type="hidden" name="ghost_manager_return_tab" value="<?php echo esc_attr( $tab ); ?>" />

		<h2 class="nav-tab-wrapper wp-clearfix" aria-label="<?php esc_attr_e( 'Secondary menu', 'ghost-manager' ); ?>">
			<?php foreach ( $tabs as $slug => $label ) : ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=ghost-manager&tab=' . rawurlencode( $slug ) ) ); ?>" class="nav-tab <?php echo $tab === $slug ? 'nav-tab-active' : ''; ?>"><?php echo esc_html( $label ); ?></a>
			<?php endforeach; ?>
		</h2>

		<p class="description">
			<?php esc_html_e( 'Only this tab’s fields are submitted when you save, so large forms stay within server limits. Switch tabs to edit other sections; values on other tabs stay as last saved.', 'ghost-manager' ); ?>
		</p>

		<?php
		// Only the active tab outputs inputs so POST stays under PHP max_input_vars (~1000). Sanitizer merges partial POST with DB.
		$tab_file = array(
			'general'      => 'tab-general.php',
			'features'     => 'tab-features.php',
			'urls'         => 'tab-urls.php',
			'emails'       => 'tab-emails.php',
			'cron'         => 'tab-cron.php',
			'integrations' => 'tab-integrations.php',
		);
		?>
		<div class="ghost-manager-tab-panel is-active">
			<?php
			if ( isset( $tab_file[ $tab ] ) ) {
				require GHOST_MANAGER_PATH . 'includes/admin/views/tabs/' . $tab_file[ $tab ];
			}
			?>
		</div>

		<?php submit_button(); ?>
	</form>
</div>
