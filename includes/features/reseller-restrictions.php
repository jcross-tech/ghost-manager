<?php
/**
 * Reseller-only product category and role checks.
 *
 * @package Ghost_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param bool       $purchasable Purchasable flag.
 * @param WC_Product $product     Product.
 * @return bool
 */
function ghost_manager_reseller_is_purchasable( $purchasable, $product ) {
	if ( ! ghost_manager_is_feature_enabled( 'reseller_restrictions' ) ) {
		return $purchasable;
	}

	$slug = ghost_manager_get( 'taxonomies.reseller_slug', 'reseller' );
	$role = ghost_manager_get( 'roles.reseller', 'reseller' );

	if ( has_term( $slug, 'product_cat', $product->get_id() ) ) {
		$user = wp_get_current_user();
		if ( ! is_user_logged_in() || ! in_array( $role, (array) $user->roles, true ) ) {
			return false;
		}
	}

	return $purchasable;
}
add_filter( 'woocommerce_is_purchasable', 'ghost_manager_reseller_is_purchasable', 10, 2 );

/**
 * Strip add-to-cart UI for non-resellers on reseller products.
 */
function ghost_manager_reseller_hide_add_to_cart_ui() {
	if ( ! ghost_manager_is_feature_enabled( 'reseller_restrictions' ) || ! is_product() ) {
		return;
	}

	global $product;
	if ( ! $product ) {
		return;
	}

	$slug = ghost_manager_get( 'taxonomies.reseller_slug', 'reseller' );
	$role = ghost_manager_get( 'roles.reseller', 'reseller' );

	if ( ! has_term( $slug, 'product_cat', $product->get_id() ) ) {
		return;
	}

	$user = wp_get_current_user();

	if ( is_user_logged_in() && in_array( $role, (array) $user->roles, true ) ) {
		return;
	}

	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
	remove_action( 'woocommerce_variable_add_to_cart', 'woocommerce_variable_add_to_cart', 30 );
	remove_action( 'woocommerce_grouped_add_to_cart', 'woocommerce_grouped_add_to_cart', 30 );
	remove_action( 'woocommerce_external_add_to_cart', 'woocommerce_external_add_to_cart', 30 );
	remove_all_actions( 'woocommerce_before_add_to_cart_button' );
}
add_action( 'woocommerce_before_single_product', 'ghost_manager_reseller_hide_add_to_cart_ui', 1 );

/**
 * Notices for reseller-gated products.
 */
function ghost_manager_reseller_product_notices() {
	if ( ! ghost_manager_is_feature_enabled( 'reseller_restrictions' ) ) {
		return;
	}

	global $product;
	if ( ! $product ) {
		return;
	}

	$slug = ghost_manager_get( 'taxonomies.reseller_slug', 'reseller' );
	$role = ghost_manager_get( 'roles.reseller', 'reseller' );

	if ( ! has_term( $slug, 'product_cat', $product->get_id() ) ) {
		return;
	}

	if ( ! is_user_logged_in() ) {
		$path = ghost_manager_get( 'urls.my_account_relative', '/my-account/' );
		$url  = esc_url( home_url( $path ) );
		$fmt  = ghost_manager_get( 'strings.reseller_login_notice' );
		wc_print_notice( wp_kses_post( str_replace( '%s', $url, $fmt ) ), 'notice' );
		return;
	}

	$user = wp_get_current_user();
	if ( ! in_array( $role, (array) $user->roles, true ) ) {
		wc_print_notice( wp_kses_post( ghost_manager_get( 'strings.reseller_only_notice' ) ), 'error' );
	}
}
add_action( 'woocommerce_single_product_summary', 'ghost_manager_reseller_product_notices', 15 );
