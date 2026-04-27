<?php
/**
 * WooCommerce: subscription cart field, smart billing, My Account tabs, checkout payment box.
 *
 * @package Ghost_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Account email field on subscription products.
 */
function ghost_manager_output_subscription_account_email_field() {
	if ( ! ghost_manager_is_feature_enabled( 'subscription_account_email' ) ) {
		return;
	}

	global $product;
	if ( ! $product ) {
		return;
	}

	$slug = ghost_manager_get( 'taxonomies.subscriptions_slug', 'subscriptions' );
	if ( ! has_term( $slug, 'product_cat', $product->get_id() ) ) {
		return;
	}

	$lbl = ghost_manager_get( 'strings.subscription_account_email_label', 'Please enter your email' );
	echo '<div class="custom-account-field">';
	echo '<label>' . esc_html( $lbl ) . ' <span>*</span></label>';
	echo '<input type="email" name="account_email" required>';
	echo '</div>';
}
add_action( 'woocommerce_before_add_to_cart_button', 'ghost_manager_output_subscription_account_email_field', 10 );

/**
 * Unique account email per product in cart (same email may appear on different products).
 *
 * @param bool  $passed      Validation state.
 * @param int   $product_id  Product ID (parent for variations).
 * @param int   $quantity    Quantity.
 * @param int   $variation_id Variation ID.
 * @param array $variation   Variation attributes.
 * @param array $cart_item_data Extra cart data.
 * @return bool
 */
function ghost_manager_validate_unique_account_email( $passed, $product_id = 0, $quantity = 1, $variation_id = 0, $variation = array(), $cart_item_data = array() ) {
	if ( ! ghost_manager_is_feature_enabled( 'subscription_account_email' ) ) {
		return $passed;
	}

	if ( ! $passed || ! isset( $_POST['account_email'] ) || ! function_exists( 'WC' ) || ! WC()->cart ) {
		return $passed;
	}

	$new_email = sanitize_email( wp_unslash( $_POST['account_email'] ) );
	if ( ! $new_email ) {
		return $passed;
	}

	$bucket_id = absint( $product_id );
	if ( $bucket_id < 1 && ! empty( $_REQUEST['add-to-cart'] ) ) {
		$bucket_id = absint( wp_unslash( $_REQUEST['add-to-cart'] ) );
	}
	if ( $bucket_id < 1 ) {
		return $passed;
	}

	foreach ( WC()->cart->get_cart() as $item ) {
		$item_product_id = isset( $item['product_id'] ) ? absint( $item['product_id'] ) : 0;
		if ( $item_product_id !== $bucket_id ) {
			continue;
		}
		if ( ! empty( $item['account_email'] ) && strtolower( $item['account_email'] ) === strtolower( $new_email ) ) {
			wc_add_notice( ghost_manager_get( 'strings.unique_email_cart_error', 'This email is already used for another line of this product in your cart.' ), 'error' );
			return false;
		}
	}

	return $passed;
}
add_filter( 'woocommerce_add_to_cart_validation', 'ghost_manager_validate_unique_account_email', 10, 6 );

/**
 * At checkout, block duplicate account emails for the same product (cart tamper / session edge cases).
 */
function ghost_manager_checkout_validate_account_emails_per_product() {
	if ( ! ghost_manager_is_feature_enabled( 'subscription_account_email' ) || ! function_exists( 'WC' ) || ! WC()->cart ) {
		return;
	}

	$seen = array();
	foreach ( WC()->cart->get_cart() as $item ) {
		if ( empty( $item['account_email'] ) || empty( $item['product_id'] ) ) {
			continue;
		}
		$pid = absint( $item['product_id'] );
		$em  = strtolower( (string) $item['account_email'] );
		if ( ! isset( $seen[ $pid ] ) ) {
			$seen[ $pid ] = array();
		}
		if ( ! empty( $seen[ $pid ][ $em ] ) ) {
			wc_add_notice( ghost_manager_get( 'strings.unique_email_cart_error', 'This email is already used for another line of this product in your cart.' ), 'error' );
			return;
		}
		$seen[ $pid ][ $em ] = true;
	}
}
add_action( 'woocommerce_checkout_process', 'ghost_manager_checkout_validate_account_emails_per_product', 5 );

/**
 * Persist account email on cart line.
 *
 * @param array $cart_item_data Cart data.
 * @return array
 */
function ghost_manager_add_cart_item_account_email( $cart_item_data ) {
	if ( ! ghost_manager_is_feature_enabled( 'subscription_account_email' ) ) {
		return $cart_item_data;
	}

	if ( isset( $_POST['account_email'] ) ) {
		$cart_item_data['account_email'] = sanitize_email( wp_unslash( $_POST['account_email'] ) );
	}

	return $cart_item_data;
}
add_filter( 'woocommerce_add_cart_item_data', 'ghost_manager_add_cart_item_account_email' );

/**
 * Order line item meta: Account Email.
 *
 * @param WC_Order_Item_Product $item Item.
 * @param string                $cart_item_key Key.
 * @param array                 $values Values.
 */
function ghost_manager_checkout_line_item_account_email( $item, $cart_item_key, $values ) {
	if ( ! ghost_manager_is_feature_enabled( 'subscription_account_email' ) ) {
		return;
	}

	if ( ! empty( $values['account_email'] ) ) {
		$item->add_meta_data( ghost_manager_order_account_email_meta_key(), $values['account_email'] );
	}
}
add_action( 'woocommerce_checkout_create_order_line_item', 'ghost_manager_checkout_line_item_account_email', 10, 3 );

/**
 * Smart billing email field.
 *
 * @param array $fields Checkout fields.
 * @return array
 */
function ghost_manager_checkout_billing_email_field( $fields ) {
	if ( ! ghost_manager_is_feature_enabled( 'smart_billing_email' ) || ! function_exists( 'WC' ) || ! WC()->cart ) {
		return $fields;
	}

	$emails = array();
	foreach ( WC()->cart->get_cart() as $item ) {
		if ( ! empty( $item['account_email'] ) ) {
			$emails[] = $item['account_email'];
		}
	}

	$emails = array_unique( $emails );

	if ( empty( $emails ) ) {
		return $fields;
	}

	if ( count( $emails ) === 1 ) {
		$email = $emails[0];
		$fields['billing']['billing_email']['default']             = $email;
		$fields['billing']['billing_email']['custom_attributes'] = array(
			'readonly' => 'readonly',
		);
	} else {
		$ph      = ghost_manager_get( 'strings.checkout_select_email_placeholder', 'Select an email' );
		$options = array( '' => $ph );
		foreach ( $emails as $email ) {
			$options[ $email ] = $email;
		}
		$fields['billing']['billing_email']['type']     = 'select';
		$fields['billing']['billing_email']['options']  = $options;
		$fields['billing']['billing_email']['required'] = true;
	}

	return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'ghost_manager_checkout_billing_email_field' );

/**
 * Message above billing when cart has account emails.
 */
function ghost_manager_checkout_billing_email_notice() {
	if ( ! ghost_manager_is_feature_enabled( 'smart_billing_email' ) || ! function_exists( 'WC' ) || ! WC()->cart ) {
		return;
	}

	$emails = array();
	foreach ( WC()->cart->get_cart() as $item ) {
		if ( ! empty( $item['account_email'] ) ) {
			$emails[] = $item['account_email'];
		}
	}

	$emails = array_unique( $emails );

	if ( empty( $emails ) ) {
		return;
	}

	echo '<div style="
        background:#f8f9fb;
        padding:15px;
        margin-bottom:15px;
        border-radius:8px;
        border:1px solid #e2e5ea;
    ">';

	if ( count( $emails ) === 1 ) {
		echo '<strong>' . esc_html( ghost_manager_get( 'strings.smart_billing_single' ) ) . '</strong>';
	} else {
		echo '<strong>' . esc_html( ghost_manager_get( 'strings.smart_billing_multi' ) ) . '</strong>';
	}

	echo '</div>';
}
add_action( 'woocommerce_before_checkout_billing_form', 'ghost_manager_checkout_billing_email_notice' );

/**
 * Require billing email selection (dropdown case).
 */
function ghost_manager_checkout_validate_billing_email_selected() {
	if ( ! ghost_manager_is_feature_enabled( 'smart_billing_email' ) ) {
		return;
	}

	if ( empty( $_POST['billing_email'] ) ) {
		wc_add_notice( ghost_manager_get( 'strings.billing_email_required' ), 'error' );
	}
}
add_action( 'woocommerce_checkout_process', 'ghost_manager_checkout_validate_billing_email_selected' );

/**
 * Remove Downloads and Addresses from My Account.
 *
 * @param array $items Menu items.
 * @return array
 */
function ghost_manager_remove_my_account_tabs( $items ) {
	if ( ! ghost_manager_is_feature_enabled( 'remove_my_account_tabs' ) ) {
		return $items;
	}

	unset( $items['downloads'], $items['edit-address'] );
	return $items;
}
add_filter( 'woocommerce_account_menu_items', 'ghost_manager_remove_my_account_tabs' );

/**
 * Payment info box before payment methods.
 */
function ghost_manager_checkout_payment_info_box() {
	if ( ! ghost_manager_is_feature_enabled( 'checkout_payment_info_box' ) ) {
		return;
	}

	require GHOST_MANAGER_PATH . 'includes/woocommerce/views/payment-info-box.php';
}
add_action( 'woocommerce_review_order_before_payment', 'ghost_manager_checkout_payment_info_box' );
