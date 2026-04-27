<?php
/**
 * Settings tab: WooCommerce slugs, checkout copy, payment box.
 *
 * @package Ghost_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pb = $settings['payment_box'];
?>
<h2 class="title"><?php esc_html_e( 'Category & role slugs', 'ghost-manager' ); ?></h2>
<table class="form-table" role="presentation">
	<tr>
		<th><label for="gm-cat-sub"><?php esc_html_e( 'Subscriptions category slug', 'ghost-manager' ); ?></label></th>
		<td><input class="regular-text" id="gm-cat-sub" type="text" name="ghost_manager_settings[taxonomies][subscriptions_slug]" value="<?php echo esc_attr( $settings['taxonomies']['subscriptions_slug'] ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="gm-cat-res"><?php esc_html_e( 'Reseller category slug', 'ghost-manager' ); ?></label></th>
		<td><input class="regular-text" id="gm-cat-res" type="text" name="ghost_manager_settings[taxonomies][reseller_slug]" value="<?php echo esc_attr( $settings['taxonomies']['reseller_slug'] ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="gm-role-res"><?php esc_html_e( 'Reseller role slug', 'ghost-manager' ); ?></label></th>
		<td><input class="regular-text" id="gm-role-res" type="text" name="ghost_manager_settings[roles][reseller]" value="<?php echo esc_attr( $settings['roles']['reseller'] ); ?>" /></td>
	</tr>
</table>

<h2 class="title"><?php esc_html_e( 'Integrations', 'ghost-manager' ); ?></h2>
<table class="form-table" role="presentation">
	<tr>
		<th><label for="gm-exclude-login"><?php esc_html_e( 'Customer Manager: exclude user login', 'ghost-manager' ); ?></label></th>
		<td><input class="regular-text" id="gm-exclude-login" type="text" name="ghost_manager_settings[integrations][accounts_exclude_login]" value="<?php echo esc_attr( $settings['integrations']['accounts_exclude_login'] ); ?>" /></td>
	</tr>
</table>

<h2 class="title"><?php esc_html_e( 'Subscription product & checkout labels', 'ghost-manager' ); ?></h2>
<p class="description"><?php esc_html_e( 'Changing the order line meta label only affects new orders; existing orders keep the previous key unless you migrate data.', 'ghost-manager' ); ?></p>
<table class="form-table" role="presentation">
	<tr>
		<th><label for="gm-sub-lbl"><?php esc_html_e( 'Account email field label (product page)', 'ghost-manager' ); ?></label></th>
		<td><input class="large-text" id="gm-sub-lbl" type="text" name="ghost_manager_settings[strings][subscription_account_email_label]" value="<?php echo esc_attr( $settings['strings']['subscription_account_email_label'] ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="gm-chk-ph"><?php esc_html_e( 'Checkout email dropdown placeholder', 'ghost-manager' ); ?></label></th>
		<td><input class="regular-text" id="gm-chk-ph" type="text" name="ghost_manager_settings[strings][checkout_select_email_placeholder]" value="<?php echo esc_attr( $settings['strings']['checkout_select_email_placeholder'] ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="gm-meta-key"><?php esc_html_e( 'Order line meta key (display name)', 'ghost-manager' ); ?></label></th>
		<td><input class="regular-text" id="gm-meta-key" type="text" name="ghost_manager_settings[strings][order_line_meta_account_email]" value="<?php echo esc_attr( $settings['strings']['order_line_meta_account_email'] ); ?>" /></td>
	</tr>
</table>

<h2 class="title"><?php esc_html_e( 'Checkout payment info box', 'ghost-manager' ); ?></h2>
<table class="form-table" role="presentation">
	<tr>
		<th><label for="gm-pb-title"><?php esc_html_e( 'Box title', 'ghost-manager' ); ?></label></th>
		<td><input class="large-text" id="gm-pb-title" type="text" name="ghost_manager_settings[payment_box][title]" value="<?php echo esc_attr( $pb['title'] ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="gm-pb-intro"><?php esc_html_e( 'Intro (HTML allowed)', 'ghost-manager' ); ?></label></th>
		<td><textarea class="large-text" rows="3" id="gm-pb-intro" name="ghost_manager_settings[payment_box][intro_html]"><?php echo esc_textarea( $pb['intro_html'] ); ?></textarea></td>
	</tr>
	<tr>
		<th><label for="gm-pb-rec"><?php esc_html_e( 'Recommended block title', 'ghost-manager' ); ?></label></th>
		<td><input class="large-text" id="gm-pb-rec" type="text" name="ghost_manager_settings[payment_box][recommended_title]" value="<?php echo esc_attr( $pb['recommended_title'] ); ?>" /></td>
	</tr>
	<?php for ( $i = 1; $i <= 4; $i++ ) : $k = 'bullet_' . $i; ?>
	<tr>
		<th><label for="gm-pb-b<?php echo esc_attr( (string) $i ); ?>"><?php echo esc_html( sprintf( /* translators: %d: bullet index */ __( 'Bullet %d', 'ghost-manager' ), $i ) ); ?></label></th>
		<td><input class="large-text" id="gm-pb-b<?php echo esc_attr( (string) $i ); ?>" type="text" name="ghost_manager_settings[payment_box][<?php echo esc_attr( $k ); ?>]" value="<?php echo esc_attr( $pb[ $k ] ); ?>" /></td>
	</tr>
	<?php endfor; ?>
	<tr>
		<th><label for="gm-pb-fg"><?php esc_html_e( 'Text before guide links', 'ghost-manager' ); ?></label></th>
		<td><input class="large-text" id="gm-pb-fg" type="text" name="ghost_manager_settings[payment_box][follow_guides]" value="<?php echo esc_attr( $pb['follow_guides'] ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="gm-pb-cb"><?php esc_html_e( 'Crypto.com button label', 'ghost-manager' ); ?></label></th>
		<td><input class="large-text" id="gm-pb-cb" type="text" name="ghost_manager_settings[payment_box][crypto_button]" value="<?php echo esc_attr( $pb['crypto_button'] ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="gm-pb-ch"><?php esc_html_e( 'Crypto.com helper text', 'ghost-manager' ); ?></label></th>
		<td><input class="large-text" id="gm-pb-ch" type="text" name="ghost_manager_settings[payment_box][crypto_helper]" value="<?php echo esc_attr( $pb['crypto_helper'] ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="gm-pb-rb"><?php esc_html_e( 'Revolut button label', 'ghost-manager' ); ?></label></th>
		<td><input class="large-text" id="gm-pb-rb" type="text" name="ghost_manager_settings[payment_box][revolut_button]" value="<?php echo esc_attr( $pb['revolut_button'] ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="gm-pb-rh"><?php esc_html_e( 'Revolut helper text', 'ghost-manager' ); ?></label></th>
		<td><input class="large-text" id="gm-pb-rh" type="text" name="ghost_manager_settings[payment_box][revolut_helper]" value="<?php echo esc_attr( $pb['revolut_helper'] ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="gm-pb-tb"><?php esc_html_e( 'Transak button label', 'ghost-manager' ); ?></label></th>
		<td><input class="large-text" id="gm-pb-tb" type="text" name="ghost_manager_settings[payment_box][transak_button]" value="<?php echo esc_attr( $pb['transak_button'] ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="gm-pb-th"><?php esc_html_e( 'Transak helper text', 'ghost-manager' ); ?></label></th>
		<td><input class="large-text" id="gm-pb-th" type="text" name="ghost_manager_settings[payment_box][transak_helper]" value="<?php echo esc_attr( $pb['transak_helper'] ); ?>" /></td>
	</tr>
</table>
