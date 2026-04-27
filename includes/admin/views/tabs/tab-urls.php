<?php
/**
 * Settings tab: URLs and renew paths.
 *
 * @package Ghost_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<h2 class="title"><?php esc_html_e( 'URLs', 'ghost-manager' ); ?></h2>
<table class="form-table" role="presentation">
	<tr>
		<th><label for="gm-logo"><?php esc_html_e( 'Logo image URL (emails)', 'ghost-manager' ); ?></label></th>
		<td><input class="large-text" id="gm-logo" type="url" name="ghost_manager_settings[urls][logo]" value="<?php echo esc_attr( $settings['urls']['logo'] ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="gm-discord"><?php esc_html_e( 'Discord invite URL', 'ghost-manager' ); ?></label></th>
		<td><input class="large-text" id="gm-discord" type="url" name="ghost_manager_settings[urls][discord]" value="<?php echo esc_attr( $settings['urls']['discord'] ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="gm-myaccount"><?php esc_html_e( 'My Account path (reseller notice)', 'ghost-manager' ); ?></label></th>
		<td><input class="regular-text" id="gm-myaccount" type="text" name="ghost_manager_settings[urls][my_account_relative]" value="<?php echo esc_attr( $settings['urls']['my_account_relative'] ); ?>" placeholder="/my-account/" /></td>
	</tr>
	<tr>
		<th><label for="gm-xtream"><?php esc_html_e( 'Xtream player API base URL', 'ghost-manager' ); ?></label></th>
		<td><input class="large-text" id="gm-xtream" type="url" name="ghost_manager_settings[urls][xtream_player_api]" value="<?php echo esc_attr( $settings['urls']['xtream_player_api'] ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="gm-guide-crypto"><?php esc_html_e( 'Crypto.com guide URL', 'ghost-manager' ); ?></label></th>
		<td><input class="large-text" id="gm-guide-crypto" type="url" name="ghost_manager_settings[urls][guide_crypto_com]" value="<?php echo esc_attr( $settings['urls']['guide_crypto_com'] ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="gm-guide-rev"><?php esc_html_e( 'Revolut guide URL', 'ghost-manager' ); ?></label></th>
		<td><input class="large-text" id="gm-guide-rev" type="url" name="ghost_manager_settings[urls][guide_revolut]" value="<?php echo esc_attr( $settings['urls']['guide_revolut'] ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="gm-guide-transak"><?php esc_html_e( 'Transak guide URL', 'ghost-manager' ); ?></label></th>
		<td><input class="large-text" id="gm-guide-transak" type="url" name="ghost_manager_settings[urls][guide_transak]" value="<?php echo esc_attr( $settings['urls']['guide_transak'] ); ?>" /></td>
	</tr>
</table>

<h2 class="title"><?php esc_html_e( 'Renew product paths', 'ghost-manager' ); ?></h2>
<table class="form-table" role="presentation">
	<tr>
		<th><label for="gm-renew-gp"><?php echo esc_html( sprintf( __( 'Service %d renew path', 'ghost-manager' ), 1 ) ); ?></label></th>
		<td><input class="regular-text" id="gm-renew-gp" type="text" name="ghost_manager_settings[renew_urls][sub1]" value="<?php echo esc_attr( $settings['renew_urls']['sub1'] ?? '' ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="gm-renew-tv"><?php echo esc_html( sprintf( __( 'Service %d renew path', 'ghost-manager' ), 2 ) ); ?></label></th>
		<td><input class="regular-text" id="gm-renew-tv" type="text" name="ghost_manager_settings[renew_urls][sub2]" value="<?php echo esc_attr( $settings['renew_urls']['sub2'] ?? '' ); ?>" /></td>
	</tr>
</table>
