<?php
/**
 * Settings tab: General (branding, Discord, My Account labels).
 *
 * @package Ghost_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<h2 class="title"><?php esc_html_e( 'Branding & service names', 'ghost-manager' ); ?></h2>
<table class="form-table" role="presentation">
	<tr>
		<th><label for="gm-brand-name"><?php esc_html_e( 'Brand name', 'ghost-manager' ); ?></label></th>
		<td><input class="regular-text" id="gm-brand-name" type="text" name="ghost_manager_settings[strings][brand_name]" value="<?php echo esc_attr( $settings['strings']['brand_name'] ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="gm-svc-1"><?php esc_html_e( 'Service 1', 'ghost-manager' ); ?></label></th>
		<td><input class="regular-text" id="gm-svc-1" type="text" name="ghost_manager_settings[strings][service_1]" value="<?php echo esc_attr( $settings['strings']['service_1'] ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="gm-svc-2"><?php esc_html_e( 'Service 2', 'ghost-manager' ); ?></label></th>
		<td><input class="regular-text" id="gm-svc-2" type="text" name="ghost_manager_settings[strings][service_2]" value="<?php echo esc_attr( $settings['strings']['service_2'] ); ?>" /></td>
	</tr>
</table>

<h2 class="title"><?php esc_html_e( 'Discord widget (copy)', 'ghost-manager' ); ?></h2>
<table class="form-table" role="presentation">
	<tr>
		<th><label for="gm-discord-tip"><?php esc_html_e( 'Tooltip text', 'ghost-manager' ); ?></label></th>
		<td><input class="large-text" id="gm-discord-tip" type="text" name="ghost_manager_settings[strings][discord_tooltip]" value="<?php echo esc_attr( $settings['strings']['discord_tooltip'] ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="gm-discord-title"><?php esc_html_e( 'Popup title', 'ghost-manager' ); ?></label></th>
		<td><input class="regular-text" id="gm-discord-title" type="text" name="ghost_manager_settings[strings][discord_popup_title]" value="<?php echo esc_attr( $settings['strings']['discord_popup_title'] ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="gm-discord-body"><?php esc_html_e( 'Popup body', 'ghost-manager' ); ?></label></th>
		<td><input class="large-text" id="gm-discord-body" type="text" name="ghost_manager_settings[strings][discord_popup_body]" value="<?php echo esc_attr( $settings['strings']['discord_popup_body'] ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="gm-discord-cta"><?php esc_html_e( 'Button label', 'ghost-manager' ); ?></label></th>
		<td><input class="regular-text" id="gm-discord-cta" type="text" name="ghost_manager_settings[strings][discord_cta_label]" value="<?php echo esc_attr( $settings['strings']['discord_cta_label'] ); ?>" /></td>
	</tr>
</table>

<h2 class="title"><?php esc_html_e( 'My Account subscription cards', 'ghost-manager' ); ?></h2>
<table class="form-table" role="presentation">
	<tr>
		<th><label for="gm-st-active"><?php esc_html_e( 'Status: Active', 'ghost-manager' ); ?></label></th>
		<td><input class="regular-text" id="gm-st-active" type="text" name="ghost_manager_settings[strings][account_status_active]" value="<?php echo esc_attr( $settings['strings']['account_status_active'] ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="gm-st-exp"><?php esc_html_e( 'Status: Expired', 'ghost-manager' ); ?></label></th>
		<td><input class="regular-text" id="gm-st-exp" type="text" name="ghost_manager_settings[strings][account_status_expired]" value="<?php echo esc_attr( $settings['strings']['account_status_expired'] ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="gm-st-soon"><?php esc_html_e( 'Status: Expiring soon', 'ghost-manager' ); ?></label></th>
		<td><input class="regular-text" id="gm-st-soon" type="text" name="ghost_manager_settings[strings][account_status_expiring]" value="<?php echo esc_attr( $settings['strings']['account_status_expiring'] ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="gm-renew-emoji"><?php esc_html_e( 'Renew link prefix (emoji or short text; leave empty to hide)', 'ghost-manager' ); ?></label></th>
		<td><input class="small-text" id="gm-renew-emoji" type="text" name="ghost_manager_settings[strings][account_renew_prefix]" value="<?php echo esc_attr( $settings['strings']['account_renew_prefix'] ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="gm-card-user"><?php esc_html_e( 'Label: Username', 'ghost-manager' ); ?></label></th>
		<td><input class="regular-text" id="gm-card-user" type="text" name="ghost_manager_settings[strings][account_card_label_username]" value="<?php echo esc_attr( $settings['strings']['account_card_label_username'] ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="gm-card-pass"><?php esc_html_e( 'Label: Password', 'ghost-manager' ); ?></label></th>
		<td><input class="regular-text" id="gm-card-pass" type="text" name="ghost_manager_settings[strings][account_card_label_password]" value="<?php echo esc_attr( $settings['strings']['account_card_label_password'] ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="gm-card-exp"><?php esc_html_e( 'Label: Expiry', 'ghost-manager' ); ?></label></th>
		<td><input class="regular-text" id="gm-card-exp" type="text" name="ghost_manager_settings[strings][account_card_label_expiry]" value="<?php echo esc_attr( $settings['strings']['account_card_label_expiry'] ); ?>" /></td>
	</tr>
</table>
