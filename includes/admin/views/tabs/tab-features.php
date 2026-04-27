<?php
/**
 * Settings tab: Features (masters + advanced).
 *
 * @package Ghost_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$master_features = array(
	'discord_widget'          => __( 'Discord widget (frontend)', 'ghost-manager' ),
	'accounts_manager'        => __( 'Customer Manager (admin)', 'ghost-manager' ),
	'woocommerce'             => __( 'WooCommerce features (checkout, cart, My Account tweaks)', 'ghost-manager' ),
	'reseller_restrictions'   => __( 'Reseller restrictions', 'ghost-manager' ),
	'email_system'            => __( 'Email system (service emails, auth emails, expiry warnings)', 'ghost-manager' ),
);
?>
<p class="description"><?php esc_html_e( 'Master switches control entire areas. Advanced options below only apply when the related master feature is enabled.', 'ghost-manager' ); ?></p>

<div class="ghost-manager-master-notice">
	<p><?php esc_html_e( 'If a master feature is off, the plugin skips loading that code path even if an advanced checkbox is still on.', 'ghost-manager' ); ?></p>
</div>

<h2 class="title"><?php esc_html_e( 'Master features', 'ghost-manager' ); ?></h2>
<table class="form-table" role="presentation">
	<tbody>
	<?php foreach ( $master_features as $key => $label ) : ?>
		<tr>
			<th scope="row"><?php echo esc_html( $label ); ?></th>
			<td>
				<label>
					<?php ghost_manager_hidden_checkbox( 'ghost_manager_settings[features][' . $key . ']', ! empty( $settings['features'][ $key ] ) ); ?>
					<?php esc_html_e( 'Enabled', 'ghost-manager' ); ?>
				</label>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>

<h2 class="title"><?php esc_html_e( 'Advanced (WooCommerce & email details)', 'ghost-manager' ); ?></h2>
<p class="description"><?php esc_html_e( 'Fine-grained controls. Requires WooCommerce master and/or Email system master where applicable.', 'ghost-manager' ); ?></p>
<table class="form-table" role="presentation">
	<tbody>
	<?php
	$feature_labels = array(
		'subscription_account_email'      => __( 'Subscription products: per-line account email', 'ghost-manager' ),
		'user_creation_completed_order'   => __( 'Create WordPress user when order completes', 'ghost-manager' ),
		'custom_auth_emails'              => __( 'Custom HTML emails (new user + password reset)', 'ghost-manager' ),
		'ghost_expiry_cron'               => __( 'Daily cron: expiry warning emails', 'ghost-manager' ),
		'my_account_subscription_details' => __( 'My Account: subscription details cards', 'ghost-manager' ),
		'smart_billing_email'             => __( 'Checkout: billing email from cart line emails', 'ghost-manager' ),
		'remove_my_account_tabs'        => __( 'My Account: hide Downloads & Addresses', 'ghost-manager' ),
		'checkout_payment_info_box'     => __( 'Checkout: payment guide box (always shown when enabled)', 'ghost-manager' ),
	);
	foreach ( $feature_labels as $key => $label ) :
		?>
		<tr>
			<th scope="row"><?php echo esc_html( $label ); ?></th>
			<td>
				<label>
					<?php ghost_manager_hidden_checkbox( 'ghost_manager_settings[features][' . $key . ']', ! empty( $settings['features'][ $key ] ) ); ?>
					<?php esc_html_e( 'Enabled', 'ghost-manager' ); ?>
				</label>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
