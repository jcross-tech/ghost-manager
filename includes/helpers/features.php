<?php
/**
 * Feature flags (options + license filter).
 *
 * @package Ghost_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether a feature is enabled in settings and passes license filter.
 *
 * @param string $feature_key Key under settings['features'].
 * @return bool
 */
function ghost_manager_is_feature_enabled( $feature_key ) {
	$settings = ghost_manager_get_settings();
	$enabled  = ! empty( $settings['features'][ $feature_key ] );

	/**
	 * Filter whether a feature is active (e.g. license / premium gate).
	 *
	 * @param bool   $enabled     Base value from settings.
	 * @param string $feature_key Feature key.
	 */
	return (bool) apply_filters( 'ghost_manager_feature_enabled', $enabled, $feature_key );
}
