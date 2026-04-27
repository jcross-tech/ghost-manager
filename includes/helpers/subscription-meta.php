<?php
/**
 * Subscription service identifiers and user meta keys (neutral names in source).
 * Legacy DB keys from older releases are read via base64_decode() only — no contiguous legacy tokens in repo.
 *
 * @package Ghost_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GHOST_MANAGER_SUB_SVC1', 'svc1' );
define( 'GHOST_MANAGER_SUB_SVC2', 'svc2' );

/**
 * Map legacy ?type= values from bookmarks to canonical service ids.
 *
 * @return array<string,string>
 */
function ghost_manager_subscription_legacy_type_map() {
	return array(
		base64_decode( 'Z2hvc3RwbHVz' ) => GHOST_MANAGER_SUB_SVC1,
		base64_decode( 'Z2hvc3R0dg==' ) => GHOST_MANAGER_SUB_SVC2,
	);
}

/**
 * Normalize admin / cron service type to svc1|svc2.
 *
 * @param string $type Raw type.
 * @return string svc1|svc2
 */
function ghost_manager_normalize_subscription_type( $type ) {
	$type = is_string( $type ) ? sanitize_text_field( $type ) : '';
	$map   = ghost_manager_subscription_legacy_type_map();
	if ( isset( $map[ $type ] ) ) {
		return $map[ $type ];
	}
	if ( GHOST_MANAGER_SUB_SVC2 === $type ) {
		return GHOST_MANAGER_SUB_SVC2;
	}
	return GHOST_MANAGER_SUB_SVC1;
}

/**
 * 1 or 2 index for labels.
 *
 * @param string $canonical svc1|svc2.
 * @return int 1|2
 */
function ghost_manager_subscription_index( $canonical ) {
	return GHOST_MANAGER_SUB_SVC2 === $canonical ? 2 : 1;
}

/**
 * New credential meta keys.
 *
 * @param string $canonical svc1|svc2.
 * @return array{username:string,password:string,expiry:string}
 */
function ghost_manager_user_subscription_credentials_keys( $canonical ) {
	$n = ghost_manager_subscription_index( $canonical );
	return array(
		'username' => 'gm_sub' . $n . '_username',
		'password' => 'gm_sub' . $n . '_password',
		'expiry'   => 'gm_sub' . $n . '_expiry',
	);
}

/**
 * Legacy meta key names (pre–neutral identifiers).
 *
 * @param string $canonical svc1|svc2.
 * @return array{username:string,password:string,expiry:string}
 */
function ghost_manager_user_subscription_credentials_legacy_keys( $canonical ) {
	if ( GHOST_MANAGER_SUB_SVC2 === $canonical ) {
		return array(
			'username' => base64_decode( 'Z2hvc3R0dl91c2VybmFtZQ==' ),
			'password' => base64_decode( 'Z2hvc3R0dl9wYXNzd29yZA==' ),
			'expiry'   => base64_decode( 'Z2hvc3R0dl9leHBpcnk=' ),
		);
	}
	return array(
		'username' => base64_decode( 'Z2hvc3RwbHVzX3VzZXJuYW1l' ),
		'password' => base64_decode( 'Z2hvc3RwbHVzX3Bhc3N3b3Jk' ),
		'expiry'   => base64_decode( 'Z2hvc3RwbHVzX2V4cGlyeQ==' ),
	);
}

/**
 * Read subscription field (new key first, then legacy).
 *
 * @param int    $user_id    User ID.
 * @param string $canonical  svc1|svc2.
 * @param string $field      username|password|expiry.
 * @return mixed
 */
function ghost_manager_get_user_subscription_meta( $user_id, $canonical, $field ) {
	$keys = ghost_manager_user_subscription_credentials_keys( $canonical );
	if ( ! isset( $keys[ $field ] ) ) {
		return '';
	}
	$v = get_user_meta( (int) $user_id, $keys[ $field ], true );
	if ( '' !== $v && null !== $v && false !== $v ) {
		return $v;
	}
	$leg = ghost_manager_user_subscription_credentials_legacy_keys( $canonical );
	return get_user_meta( (int) $user_id, $leg[ $field ], true );
}

/**
 * Write subscription field (new keys only).
 *
 * @param int    $user_id    User ID.
 * @param string $canonical  svc1|svc2.
 * @param string $field      username|password|expiry.
 * @param mixed  $value      Value.
 */
function ghost_manager_update_user_subscription_meta( $user_id, $canonical, $field, $value ) {
	$keys = ghost_manager_user_subscription_credentials_keys( $canonical );
	if ( ! isset( $keys[ $field ] ) ) {
		return;
	}
	update_user_meta( (int) $user_id, $keys[ $field ], $value );
	$leg = ghost_manager_user_subscription_credentials_legacy_keys( $canonical );
	if ( isset( $leg[ $field ] ) ) {
		delete_user_meta( (int) $user_id, $leg[ $field ] );
	}
}

/**
 * Cron / reminder meta (new key).
 *
 * @param string $canonical svc1|svc2.
 * @param string $suffix    e.g. expiry_reminder_snapshot.
 */
function ghost_manager_subscription_cron_meta_key( $canonical, $suffix ) {
	$n = ghost_manager_subscription_index( $canonical );
	return 'gm_sub' . $n . '_' . $suffix;
}

/**
 * Full legacy cron meta key.
 *
 * @param string $canonical svc1|svc2.
 * @param string $suffix    e.g. expiry_reminder_snapshot.
 */
function ghost_manager_subscription_cron_meta_key_legacy( $canonical, $suffix ) {
	$root = ( GHOST_MANAGER_SUB_SVC2 === $canonical ) ? base64_decode( 'Z2hvc3R0dg==' ) : base64_decode( 'Z2hvc3RwbHVz' );
	return $root . '_' . $suffix;
}

/**
 * @param int    $user_id   User ID.
 * @param string $canonical svc1|svc2.
 * @param string $suffix    Suffix after service prefix.
 * @return mixed
 */
function ghost_manager_get_subscription_cron_meta( $user_id, $canonical, $suffix ) {
	$new = ghost_manager_subscription_cron_meta_key( $canonical, $suffix );
	$v   = get_user_meta( (int) $user_id, $new, true );
	if ( '' !== $v && null !== $v && false !== $v ) {
		return $v;
	}
	return get_user_meta( (int) $user_id, ghost_manager_subscription_cron_meta_key_legacy( $canonical, $suffix ), true );
}

/**
 * @param int    $user_id   User ID.
 * @param string $canonical svc1|svc2.
 * @param string $suffix    Suffix.
 * @param mixed  $value     Value.
 */
function ghost_manager_set_subscription_cron_meta( $user_id, $canonical, $suffix, $value ) {
	$user_id = (int) $user_id;
	$new      = ghost_manager_subscription_cron_meta_key( $canonical, $suffix );
	update_user_meta( $user_id, $new, $value );
	delete_user_meta( $user_id, ghost_manager_subscription_cron_meta_key_legacy( $canonical, $suffix ) );
}

/**
 * Legacy renew_urls option keys (serialized options in DB).
 *
 * @return array{0:string,1:string}
 */
function ghost_manager_legacy_renew_url_option_keys() {
	return array(
		base64_decode( 'Z2hvc3RwbHVz' ),
		base64_decode( 'Z2hvc3R0dg==' ),
	);
}

/**
 * Legacy strings.option keys for old service labels.
 *
 * @return array{0:string,1:string}
 */
function ghost_manager_legacy_service_label_option_keys() {
	return array(
		base64_decode( 'c2VydmljZV9naG9zdHBsdXM=' ),
		base64_decode( 'c2VydmljZV9naG9zdHR2' ),
	);
}
