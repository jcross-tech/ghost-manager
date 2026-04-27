<?php
/**
 * License stub — hook ghost_manager_feature_enabled or ghost_manager_license_active later.
 *
 * @package Ghost_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Global license check placeholder (always true until you add a filter).
 *
 * @return bool
 */
function ghost_manager_is_license_active() {
	/**
	 * Whether the plugin (or site) has an active license.
	 *
	 * @param bool $active Default true.
	 */
	return (bool) apply_filters( 'ghost_manager_license_active', true );
}
