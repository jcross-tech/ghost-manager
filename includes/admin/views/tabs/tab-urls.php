<?php
/**
 * Settings tab: URLs and renew paths.
 *
 * @package Ghost_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$urls           = isset( $settings['urls'] ) && is_array( $settings['urls'] ) ? $settings['urls'] : array();
$xtream_base_1  = isset( $urls['xtream_player_api_base_svc1'] ) ? (string) $urls['xtream_player_api_base_svc1'] : '';
$xtream_base_2  = isset( $urls['xtream_player_api_base_svc2'] ) ? (string) $urls['xtream_player_api_base_svc2'] : '';
$svc1_public    = ghost_manager_get_service_label( 1 );
$svc2_public    = ghost_manager_get_service_label( 2 );
?>
<h2 class="title"><?php esc_html_e( 'URLs', 'ghost-manager' ); ?></h2>
<table class="form-table" role="presentation">
	<tr>
		<th><label for="gm-logo"><?php esc_html_e( 'Logo image URL (emails)', 'ghost-manager' ); ?></label></th>
		<td>
			<input class="large-text" id="gm-logo" type="url" name="ghost_manager_settings[urls][logo]" value="<?php echo esc_attr( $settings['urls']['logo'] ); ?>" placeholder="https://yoursite.com/wp-content/uploads/logo.png" />
			<p class="description"><?php esc_html_e( 'Use a direct HTTPS link to the image file. Many inboxes block remote images until the recipient chooses to load them—this is normal.', 'ghost-manager' ); ?></p>
		</td>
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
		<th><label for="gm-xtream-s1"><?php echo esc_html( sprintf( /* translators: %s: service label e.g. Ghost+ */ __( 'Xtream server URL (%s)', 'ghost-manager' ), $svc1_public ) ); ?></label></th>
		<td>
			<input class="large-text" id="gm-xtream-s1" type="text" name="ghost_manager_settings[urls][xtream_player_api_base_svc1]" value="<?php echo esc_attr( $xtream_base_1 ); ?>" placeholder="http://exampledns.com" autocomplete="off" />
			<p class="description"><?php esc_html_e( 'Enter your panel server address only (no path). The plugin appends /player_api.php automatically—for example, http://exampledns.com becomes http://exampledns.com/player_api.php. If you already paste a URL ending in player_api.php, it is left as-is.', 'ghost-manager' ); ?></p>
		</td>
	</tr>
	<tr>
		<th><label for="gm-xtream-s2"><?php echo esc_html( sprintf( /* translators: %s: service label */ __( 'Xtream server URL (%s)', 'ghost-manager' ), $svc2_public ) ); ?></label></th>
		<td>
			<input class="large-text" id="gm-xtream-s2" type="text" name="ghost_manager_settings[urls][xtream_player_api_base_svc2]" value="<?php echo esc_attr( $xtream_base_2 ); ?>" placeholder="http://exampledns.com" autocomplete="off" />
			<p class="description"><?php esc_html_e( 'Same as above for the second service. Use a different DNS if this line uses another provider.', 'ghost-manager' ); ?></p>
		</td>
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
