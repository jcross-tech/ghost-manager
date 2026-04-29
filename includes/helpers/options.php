<?php
/**
 * Options: defaults, merge, getters, sanitization.
 *
 * @package Ghost_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Debug/trace for settings saves.
 *
 * Add to wp-config.php: define( 'GHOST_MANAGER_TRACE_SAVES', true );
 * Then check wp-content/ghost-manager-save-trace.log after clicking Save (works even if WP_DEBUG is off).
 * With WP_DEBUG true, lines are also sent to PHP’s error_log (not always the same as debug.log on hosts).
 *
 * @param string $message Context label.
 * @param mixed  $data    Optional data (json-encoded; truncated if huge).
 */
function ghost_manager_debug_log( $message, $data = null ) {
	$trace_file = defined( 'GHOST_MANAGER_TRACE_SAVES' ) && GHOST_MANAGER_TRACE_SAVES;
	$php_log    = defined( 'WP_DEBUG' ) && WP_DEBUG;

	if ( ! $trace_file && ! $php_log ) {
		return;
	}

	$line = gmdate( 'c' ) . ' [Ghost Manager] ' . $message;
	if ( null !== $data ) {
		$json = wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR );
		if ( is_string( $json ) && strlen( $json ) > 4000 ) {
			$json = substr( $json, 0, 4000 ) . '…';
		}
		$line .= ' | ' . $json;
	}
	$line .= "\n";

	if ( $trace_file && defined( 'WP_CONTENT_DIR' ) ) {
		@file_put_contents( WP_CONTENT_DIR . '/ghost-manager-save-trace.log', $line, FILE_APPEND | LOCK_EX );
	}

	if ( $php_log ) {
		error_log( trim( $line ) );
	}
}

/**
 * Absolute path to the save-trace log (same file GHOST_MANAGER_TRACE_SAVES writes).
 *
 * @return string Empty if WP_CONTENT_DIR unavailable.
 */
function ghost_manager_save_trace_log_path() {
	return defined( 'WP_CONTENT_DIR' ) ? WP_CONTENT_DIR . '/ghost-manager-save-trace.log' : '';
}

/**
 * Read save-trace log for admin display (tail if file is large).
 *
 * @param int $max_bytes Maximum bytes to return (from end of file).
 * @return array{ content: string, truncated: bool, bytes_on_disk: int }
 */
function ghost_manager_read_save_trace_log( $max_bytes = 1048576 ) {
	$path = ghost_manager_save_trace_log_path();
	$out  = array(
		'content'        => '',
		'truncated'      => false,
		'bytes_on_disk'  => 0,
	);
	if ( ! $path || ! is_readable( $path ) ) {
		return $out;
	}
	$size = filesize( $path );
	if ( false === $size || $size < 1 ) {
		return $out;
	}
	$out['bytes_on_disk'] = (int) $size;
	if ( $size <= $max_bytes ) {
		$raw = file_get_contents( $path );
		$out['content'] = is_string( $raw ) ? $raw : '';
		return $out;
	}
	$out['truncated'] = true;
	$fp = fopen( $path, 'rb' );
	if ( ! $fp ) {
		return $out;
	}
	$start = $size - $max_bytes;
	fseek( $fp, $start, SEEK_SET );
	$read = fread( $fp, $max_bytes );
	fclose( $fp );
	$out['content'] = is_string( $read ) ? $read : '';
	return $out;
}

/**
 * Default HTML for service subscription emails (placeholders: {{logo_block}}, {{logo_url}}, {{title}}, {{intro}}, {{username}}, {{password}}, {{expiry}}, {{label_username}}, {{label_password}}, {{label_expiry}}, {{footer_note}}).
 *
 * @return string
 */
function ghost_manager_default_service_email_body_html() {
	return '
    <div style="font-family:Arial, sans-serif; background:#f4f6f8; padding:30px;">
        <div style="max-width:600px;margin:0 auto;background:#ffffff;border-radius:10px;overflow:hidden;border:1px solid #e2e5ea;">

            {{logo_block}}

            <div style="padding:25px;">

                <h2 style="margin-top:0;">{{title}}</h2>

                <p>{{intro}}</p>

                <div style="background:#f8f9fb;border:1px solid #e2e5ea;padding:15px;border-radius:8px;margin:20px 0;">

                    <p><strong>{{label_username}}:</strong><br>{{username}}</p>

                    <p><strong>{{label_password}}:</strong><br>{{password}}</p>

                    <p><strong>{{label_expiry}}:</strong><br>
                        <span style="color:#e11d2e;font-weight:bold;">{{expiry}}</span>
                    </p>

                </div>

                <p style="font-size:14px;color:#555;">
                    {{footer_note}}
                </p>

            </div>

        </div>
    </div>
    ';
}

/**
 * Default new-user email body (placeholders: {{logo_block}}, {{logo_url}}, {{brand}}, {{reset_link}}, {{important_box}}, {{greeting}}, {{intro}}, {{button_label}}, {{fallback_text}}, {{benefits_intro}}, {{benefits_list}}).
 *
 * @return string
 */
function ghost_manager_default_new_user_body_html() {
	return '
    <div style="font-family:Arial, sans-serif; background:#f4f6f8; padding:30px;">
        <div style="max-width:600px;margin:0 auto;background:#ffffff;border-radius:10px;overflow:hidden;border:1px solid #e2e5ea;">

            {{logo_block}}

            <div style="padding:25px;">

                {{important_box}}

                <p>{{greeting}}</p>

                <p>{{intro}}</p>

                <div style="text-align:center;margin:30px 0;">
                    <a href="{{reset_link_url}}" style="
                        background:#e11d2e;
                        color:#fff;
                        padding:14px 22px;
                        text-decoration:none;
                        border-radius:6px;
                        font-weight:bold;
                        display:inline-block;
                    ">
                        {{button_label}}
                    </a>
                </div>

                <p style="font-size:14px;color:#555;">
                    {{fallback_text}}
                </p>

                <p style="word-break:break-all;font-size:13px;color:#888;">
                    {{reset_link_display}}
                </p>

                <hr style="margin:25px 0;border:none;border-top:1px solid #eee;">

                <p style="font-size:14px;color:#555;">
                    {{benefits_intro}}
                </p>

                <ul style="font-size:14px;color:#555;line-height:1.6;">
                    {{benefits_list}}
                </ul>

            </div>

        </div>
    </div>
    ';
}

/**
 * Default password-reset email body (placeholders: {{logo_block}}, {{logo_url}}, {{brand}}, {{reset_link}}, {{heading}}, {{greeting}}, {{intro}}, {{button_label}}, {{footer_text}}, {{link_intro}}).
 *
 * @return string
 */
function ghost_manager_default_password_reset_body_html() {
	return '
    <div style="font-family:Arial, sans-serif; background:#f4f6f8; padding:30px;">
        <div style="max-width:600px;margin:0 auto;background:#ffffff;border-radius:10px;overflow:hidden;border:1px solid #e2e5ea;">

            {{logo_block}}

            <div style="padding:25px;">

                <h2 style="margin-top:0;">{{heading}}</h2>

                <p>{{greeting}}</p>

                <p>{{intro}}</p>

                <div style="text-align:center;margin:30px 0;">
                    <a href="{{reset_link_url}}" style="
                        background:#e11d2e;
                        color:#fff;
                        padding:14px 22px;
                        text-decoration:none;
                        border-radius:6px;
                        font-weight:bold;
                        display:inline-block;
                    ">
                        {{button_label}}
                    </a>
                </div>

                <p style="font-size:14px;color:#555;">
                    {{footer_text}}
                </p>

                <p style="font-size:13px;color:#888;">
                    {{link_intro}}
                </p>

                <p style="word-break:break-all;font-size:12px;color:#999;">
                    {{reset_link_display}}
                </p>

            </div>

        </div>
    </div>
    ';
}

/**
 * Default settings for new installs (neutral placeholders — no live business URLs or brand names in repo).
 * Existing sites keep values stored in wp_options; merge does not overwrite saved keys.
 *
 * @return array
 */
function ghost_manager_default_settings() {
	return array(
		'features' => array(
			'woocommerce'                       => true,
			'email_system'                      => true,
			'subscription_account_email'        => true,
			'user_creation_completed_order'     => true,
			'custom_auth_emails'                => true,
			'ghost_expiry_cron'                 => true,
			'my_account_subscription_details'   => true,
			'smart_billing_email'               => true,
			'accounts_manager'                  => true,
			'reseller_restrictions'             => true,
			'discord_widget'                    => true,
			'remove_my_account_tabs'            => true,
			'checkout_payment_info_box'         => true,
		),
		'strings'  => array(
			'brand_name'                        => 'Your brand',
			'service_1'                         => 'Service 1',
			'service_2'                         => 'Service 2',
			'new_user_email_subject'            => 'Your account setup',
			'password_reset_email_subject'      => 'Password Reset',
			'unique_email_cart_error'           => 'This email is already used for another line of this product in your cart.',
			'subscription_details_title'        => 'Subscription Details',
			'no_subscriptions_message'          => 'No active subscriptions found.',
			'renew_button'                      => 'Renew Subscription',
			'reseller_login_notice'             => 'Please&nbsp;<a href="%s">log in</a> with a reseller account to purchase.',
			'reseller_only_notice'              => 'This product is for reseller accounts only.',
			'smart_billing_single'              => 'Your order confirmation will be sent to:',
			'smart_billing_multi'               => 'Please select an email address for your order confirmation:',
			'billing_email_required'            => 'Please select an email for order confirmation.',
			'discord_tooltip'                   => '👋 Need help with anything?',
			'discord_popup_title'               => 'Get support',
			'discord_popup_body'                => 'Join our community for help with subscriptions, setup, and updates.',
			'discord_cta_label'                 => 'Get help',
			'subscription_account_email_label'  => 'Please enter your email',
			'checkout_select_email_placeholder' => 'Select an email',
			'order_line_meta_account_email'     => 'Account Email',
			'account_status_active'             => 'Active',
			'account_status_expired'          => 'Expired',
			'account_status_expiring'         => 'Expiring Soon',
			'account_renew_prefix'            => '🔄',
			'account_card_label_username'     => 'Username',
			'account_card_label_password'     => 'Password',
			'account_card_label_expiry'       => 'Expiry',
		),
		'urls'     => array(
			'logo'                         => 'https://example.com/wp-content/uploads/logo.png',
			'discord'                      => 'https://example.com/your-support-invite',
			'my_account_relative'          => '/my-account/',
			'xtream_player_api'            => '',
			'xtream_player_api_base_svc1'  => '',
			'xtream_player_api_base_svc2'  => '',
			'guide_crypto_com'             => 'https://example.com/guides/crypto/',
			'guide_revolut'        => 'https://example.com/guides/revolut/',
			'guide_transak'        => 'https://example.com/guides/transak/',
		),
		'renew_urls' => array(
			'sub1' => '/product/service-one/',
			'sub2' => '/product/service-two/',
		),
		'taxonomies' => array(
			'subscriptions_slug' => 'subscriptions',
			'reseller_slug'      => 'reseller',
		),
		'roles'      => array(
			'reseller' => 'reseller',
		),
		'integrations' => array(
			'accounts_exclude_login'       => '',
			'checkout_hosted_gateway_id'   => '',
		),
		'payment_box' => array(
			'title'                => '💳 Payment Options',
			'intro_html'           => 'Review the payment options at checkout and follow your provider’s steps to complete your purchase.',
			'recommended_title'    => '✅ Recommended method',
			'bullet_1'             => 'No account or app required',
			'bullet_2'             => 'No KYC required',
			'bullet_3'             => 'Check provider terms for limits or cooldowns',
			'bullet_4'             => 'Instant checkout with Card, Apple Pay, or Google Pay',
			'follow_guides'        => 'Follow a guide below if needed:',
			'crypto_button'        => '⚡ Payment guide A',
			'crypto_helper'        => 'Short note for your primary checkout method.',
			'revolut_button'       => '📘 Payment guide B',
			'revolut_helper'       => 'Short note for an alternate method.',
			'transak_button'       => '📘 Payment guide C',
			'transak_helper'       => 'Short note for another option.',
		),
		'emails'     => array(
			'service' => array(
				'subject_details'  => '{{base_title}} - Your Account Details',
				'subject_renewal'  => '✅ {{email_title}}',
				'subject_warning'  => '⚠️ {{email_title}}',
				'intro_details'    => 'Here are your login details:',
				'intro_renewal'    => 'Your subscription has been successfully renewed. Your updated account details are below:',
				'intro_warning'    => 'Your subscription is expiring soon ({{days_remaining}} days remaining). Please renew to avoid interruption.',
				'body_html'        => ghost_manager_default_service_email_body_html(),
				'label_username'   => 'Username',
				'label_password'   => 'Password',
				'label_expiry'     => 'Expiry Date',
				'footer_note'      => 'Please keep these details safe. If you need help, contact support.',
				'value_not_set'    => 'Not set',
			),
			'new_user' => array(
				'body_html'          => ghost_manager_default_new_user_body_html(),
				'important_title'    => 'Important:',
				'important_line_1'   => 'This is your <strong>{{brand}}</strong> dashboard account.',
				'important_line_2'   => 'It is <strong>NOT</strong> your IPTV login.',
				'important_line_3'   => 'Your IPTV details will be sent separately once your subscription is ready.',
				'greeting'           => 'Hi,',
				'intro'              => 'Your account has been created. Please set your password using the button below:',
				'button_label'       => 'Set Your Password',
				'fallback_text'      => 'If the button doesn’t work, you can copy and paste this link into your browser:',
				'benefits_intro'     => 'You can use your {{brand}} account to:',
				'benefit_1'          => 'View your subscriptions',
				'benefit_2'          => 'Manage your services',
				'benefit_3'          => 'Renew your plan',
			),
			'password_reset' => array(
				'body_html'     => ghost_manager_default_password_reset_body_html(),
				'heading'       => 'Reset Your Password',
				'greeting'      => 'Hi,',
				'intro'         => 'We received a request to reset your password for your {{brand}} account.',
				'button_label'  => 'Reset Password',
				'footer_text'   => 'If you did not request this, you can safely ignore this email.',
				'link_intro'    => 'Or copy and paste this link into your browser:',
			),
		),
		'cron'       => array(
			'multiple_reminders' => false,
			'window_min_days'    => 8,
			'window_max_days'    => 10,
			'reminder_days_csv'  => '14,7,3,1',
			'warning_intro'      => '',
			'warning_subject'    => '',
		),
	);
}

/**
 * Deep merge user settings onto defaults.
 *
 * @param array $defaults Defaults.
 * @param array $stored   Stored option.
 * @return array
 */
function ghost_manager_merge_settings( $defaults, $stored ) {
	if ( ! is_array( $stored ) ) {
		return $defaults;
	}
	foreach ( $stored as $key => $value ) {
		if ( is_array( $value ) && isset( $defaults[ $key ] ) && is_array( $defaults[ $key ] ) ) {
			$defaults[ $key ] = ghost_manager_merge_settings( $defaults[ $key ], $value );
		} else {
			$defaults[ $key ] = $value;
		}
	}
	return $defaults;
}

/**
 * Prefix for ASCII-safe option storage (see ghost_manager_settings_pack_for_db()).
 *
 * @return string
 */
function ghost_manager_settings_blob_prefix() {
	return 'GM1:';
}

/**
 * Encode settings for wp_options.option_value. Raw PHP serialize() can fail WordPress
 * wpdb::process_fields() validation when strings contain emoji (utf8 vs utf8mb4).
 * Base64 keeps the stored scalar ASCII-only; core still wraps it in a serialized string.
 *
 * @param array $settings Sanitized settings.
 * @return string
 */
function ghost_manager_settings_pack_for_db( array $settings ) {
	return ghost_manager_settings_blob_prefix() . base64_encode( maybe_serialize( $settings ) );
}

/**
 * Decode get_option() value: legacy array or GM1 blob.
 *
 * @param mixed $raw Raw option value.
 * @return array
 */
function ghost_manager_settings_unpack_from_db( $raw ) {
	if ( is_array( $raw ) ) {
		return $raw;
	}
	if ( ! is_string( $raw ) || '' === $raw ) {
		return array();
	}
	$prefix = ghost_manager_settings_blob_prefix();
	$plen   = strlen( $prefix );
	if ( strlen( $raw ) >= $plen && substr( $raw, 0, $plen ) === $prefix ) {
		$bin = base64_decode( substr( $raw, $plen ), true );
		if ( false === $bin || '' === $bin ) {
			return array();
		}
		$un = maybe_unserialize( $bin );
		return is_array( $un ) ? $un : array();
	}
	$try = maybe_unserialize( $raw );
	return is_array( $try ) ? $try : array();
}

/**
 * Full merged settings array.
 *
 * @return array
 */
function ghost_manager_get_settings() {
	$defaults = ghost_manager_default_settings();
	$stored   = ghost_manager_settings_unpack_from_db( get_option( 'ghost_manager_settings', array() ) );
	$merged   = ghost_manager_merge_settings( $defaults, $stored );
	return ghost_manager_apply_legacy_settings_aliases( $merged );
}

/**
 * Bust caches for the ghost_manager_settings option (alloptions / notoptions / per-option group).
 */
function ghost_manager_flush_ghost_manager_settings_caches() {
	wp_cache_delete( 'ghost_manager_settings', 'options' );
	wp_cache_delete( 'notoptions', 'options' );
	wp_cache_delete( 'alloptions', 'options' );
}

/**
 * Save merged settings and bust option caches (avoids stale reads with persistent object cache).
 *
 * Stores a GM1:base64(serialize) string so emoji and long UTF-8 pass WordPress DB validation.
 * Tries update_option() first; if false, compares logical arrays; else $wpdb->replace().
 *
 * @param array $sanitized Full sanitized settings from ghost_manager_sanitize_settings().
 * @return bool Whether the option is now stored.
 */
function ghost_manager_update_settings_option( $sanitized ) {
	if ( ! is_array( $sanitized ) ) {
		ghost_manager_debug_log( 'update_settings_option: not an array' );
		return false;
	}

	$new_serial = maybe_serialize( $sanitized );
	$packed     = ghost_manager_settings_pack_for_db( $sanitized );

	$raw_update_ok = update_option( 'ghost_manager_settings', $packed );

	ghost_manager_flush_ghost_manager_settings_caches();

	$persisted_ok      = (bool) $raw_update_ok;
	$used_db_replace   = false;
	$serial_same_as_db = null;

	if ( false === $raw_update_ok ) {
		$current = get_option( 'ghost_manager_settings', '__ghost_manager_missing__' );
		if ( '__ghost_manager_missing__' === $current ) {
			$current_serial = '';
		} else {
			$current_arr    = ghost_manager_settings_unpack_from_db( $current );
			$current_serial = maybe_serialize( $current_arr );
		}
		$serial_same_as_db = ( $new_serial === $current_serial );

		if ( $serial_same_as_db ) {
			$persisted_ok = true;
		} else {
			global $wpdb;
			if ( ! isset( $wpdb->options ) ) {
				$persisted_ok = false;
			} else {
				$rep = $wpdb->replace(
					$wpdb->options,
					array(
						'option_name'  => 'ghost_manager_settings',
						'option_value' => $packed,
						'autoload'     => 'yes',
					),
					array( '%s', '%s', '%s' )
				);
				$used_db_replace = true;
				ghost_manager_flush_ghost_manager_settings_caches();
				$persisted_ok = ( false !== $rep && '' === $wpdb->last_error );
			}
		}
	}

	$log = array(
		'update_option_returned_true' => (bool) $raw_update_ok,
		'persisted_ok'                => $persisted_ok,
		'storage'                     => 'GM1',
		'used_db_replace'             => $used_db_replace,
		'serial_same_as_db'           => $serial_same_as_db,
		'top_keys'                    => array_keys( $sanitized ),
		'strings_sample'              => isset( $sanitized['strings'] ) && is_array( $sanitized['strings'] )
			? array_intersect_key( $sanitized['strings'], array_flip( array( 'discord_tooltip', 'brand_name', 'service_1' ) ) )
			: null,
	);
	if ( ! $persisted_ok ) {
		global $wpdb;
		$log['wpdb_last_error'] = ( isset( $wpdb->last_error ) && $wpdb->last_error ) ? $wpdb->last_error : '(empty)';
	}

	ghost_manager_debug_log( 'update_settings_option', $log );

	return $persisted_ok;
}

/**
 * Map legacy option keys into current shape (in memory only; sanitize strips legacy on save).
 *
 * @param array $settings Merged settings.
 * @return array
 */
function ghost_manager_apply_legacy_settings_aliases( $settings ) {
	if ( isset( $settings['strings'] ) && is_array( $settings['strings'] ) ) {
		$s         = &$settings['strings'];
		$legacy_lk = ghost_manager_legacy_service_label_option_keys();
		if ( ( ! isset( $s['service_1'] ) || '' === trim( (string) $s['service_1'] ) ) && ! empty( $s[ $legacy_lk[0] ] ) ) {
			$s['service_1'] = $s[ $legacy_lk[0] ];
		}
		if ( ( ! isset( $s['service_2'] ) || '' === trim( (string) $s['service_2'] ) ) && ! empty( $s[ $legacy_lk[1] ] ) ) {
			$s['service_2'] = $s[ $legacy_lk[1] ];
		}
	}
	if ( isset( $settings['renew_urls'] ) && is_array( $settings['renew_urls'] ) ) {
		$r        = &$settings['renew_urls'];
		$legacy_r = ghost_manager_legacy_renew_url_option_keys();
		if ( ( ! isset( $r['sub1'] ) || '' === trim( (string) $r['sub1'] ) ) && ! empty( $r[ $legacy_r[0] ] ) ) {
			$r['sub1'] = $r[ $legacy_r[0] ];
		}
		if ( ( ! isset( $r['sub2'] ) || '' === trim( (string) $r['sub2'] ) ) && ! empty( $r[ $legacy_r[1] ] ) ) {
			$r['sub2'] = $r[ $legacy_r[1] ];
		}
	}
	if ( isset( $settings['urls'] ) && is_array( $settings['urls'] ) ) {
		$u       = &$settings['urls'];
		$legacy  = isset( $u['xtream_player_api'] ) ? trim( (string) $u['xtream_player_api'] ) : '';
		$migrate = ( '' !== $legacy ) ? ghost_manager_xtream_legacy_url_to_base( $legacy ) : '';
		if ( '' !== $migrate ) {
			if ( ! isset( $u['xtream_player_api_base_svc1'] ) || '' === trim( (string) $u['xtream_player_api_base_svc1'] ) ) {
				$u['xtream_player_api_base_svc1'] = $migrate;
			}
			if ( ! isset( $u['xtream_player_api_base_svc2'] ) || '' === trim( (string) $u['xtream_player_api_base_svc2'] ) ) {
				$u['xtream_player_api_base_svc2'] = $migrate;
			}
		}
	}
	return $settings;
}

/**
 * Public label for service 1 or 2 (integrates legacy stored keys).
 *
 * @param int $index 1 or 2.
 * @return string
 */
function ghost_manager_get_service_label( $index ) {
	$s          = ghost_manager_get_settings();
	$str        = isset( $s['strings'] ) && is_array( $s['strings'] ) ? $s['strings'] : array();
	$legacy_lk  = ghost_manager_legacy_service_label_option_keys();
	if ( 1 === (int) $index ) {
		if ( ! empty( $str['service_1'] ) ) {
			return (string) $str['service_1'];
		}
		if ( ! empty( $str[ $legacy_lk[0] ] ) ) {
			return (string) $str[ $legacy_lk[0] ];
		}
		return 'Service 1';
	}
	if ( ! empty( $str['service_2'] ) ) {
		return (string) $str['service_2'];
	}
	if ( ! empty( $str[ $legacy_lk[1] ] ) ) {
		return (string) $str[ $legacy_lk[1] ];
	}
	return 'Service 2';
}

/**
 * Get a nested setting via dot notation (e.g. urls.logo).
 *
 * @param string     $path    Dot-separated path.
 * @param mixed|null $default Fallback if missing.
 * @return mixed
 */
function ghost_manager_get( $path, $default = null ) {
	$settings = ghost_manager_get_settings();
	$keys     = explode( '.', $path );
	$val      = $settings;
	foreach ( $keys as $k ) {
		if ( ! is_array( $val ) || ! array_key_exists( $k, $val ) ) {
			return $default;
		}
		$val = $val[ $k ];
	}
	return $val;
}

/**
 * Short alias for ghost_manager_get().
 *
 * @param string     $path    Dot path.
 * @param mixed|null $default Default.
 * @return mixed
 */
function ghost_get( $path, $default = null ) {
	return ghost_manager_get( $path, $default );
}

/**
 * Meta key stored on order line items for subscription account email.
 *
 * @return string
 */
function ghost_manager_order_account_email_meta_key() {
	return (string) ghost_manager_get( 'strings.order_line_meta_account_email', 'Account Email' );
}

/**
 * Account email from a line item (supports legacy "Account Email" meta if the key was renamed).
 *
 * @param WC_Order_Item_Product $item Order item.
 * @return string
 */
function ghost_manager_get_order_item_account_email( $item ) {
	if ( ! is_object( $item ) || ! method_exists( $item, 'get_meta' ) ) {
		return '';
	}
	$key = ghost_manager_order_account_email_meta_key();
	$val = $item->get_meta( $key, true );
	if ( ! $val && 'Account Email' !== $key ) {
		$val = $item->get_meta( 'Account Email', true );
	}
	return is_string( $val ) ? $val : '';
}

/**
 * Replace {{key}} placeholders in a template string.
 *
 * @param string $template Template.
 * @param array  $vars     Key => value (values should be safe for context; use esc_html where needed before passing).
 * @return string
 */
function ghost_manager_replace_email_placeholders( $template, $vars ) {
	if ( ! is_string( $template ) ) {
		return '';
	}
	foreach ( $vars as $key => $value ) {
		$template = str_replace( '{{' . $key . '}}', $value, $template );
	}
	return $template;
}

/**
 * HTML for the optional email header image (empty if no logo URL).
 *
 * @return string Safe HTML fragment.
 */
function ghost_manager_email_logo_block_html() {
	$raw = ghost_manager_get( 'urls.logo', '' );
	if ( ! is_string( $raw ) ) {
		return '';
	}
	$raw = trim( $raw );
	if ( '' === $raw ) {
		return '';
	}
	$url = esc_url( $raw, array( 'http', 'https' ) );
	if ( '' === $url ) {
		return '';
	}
	return '<div style="text-align:center;padding:25px 20px 20px;border-bottom:1px solid #eee;">'
		. '<img src="' . esc_attr( $url ) . '" width="180" style="max-width:180px;width:180px;height:auto;display:inline-block;margin:0 auto;border:0;outline:none;text-decoration:none;" alt="">'
		. '</div>';
}

/**
 * Sanitize Xtream “server” input (host or full URL); adds http:// if scheme missing.
 *
 * @param mixed $raw Raw POST value.
 * @return string esc_url_raw-safe value or empty.
 */
function ghost_manager_sanitize_xtream_base_url( $raw ) {
	$raw = trim( wp_unslash( (string) $raw ) );
	if ( '' === $raw ) {
		return '';
	}
	if ( ! preg_match( '#^https?://#i', $raw ) ) {
		$raw = 'http://' . ltrim( $raw, '/' );
	}
	$out = esc_url_raw( $raw );
	return is_string( $out ) ? $out : '';
}

/**
 * Build full player_api.php URL from a stored base or legacy full URL.
 *
 * @param string $stored Base (e.g. http://exampledns.com) or URL already ending in player_api.php.
 * @return string Normalized URL or empty.
 */
function ghost_manager_normalize_xtream_player_api_url( $stored ) {
	$stored = trim( (string) $stored );
	if ( '' === $stored ) {
		return '';
	}
	if ( ! preg_match( '#^https?://#i', $stored ) ) {
		$stored = 'http://' . ltrim( $stored, '/' );
	}
	$stored = esc_url_raw( $stored );
	if ( ! is_string( $stored ) || '' === $stored ) {
		return '';
	}
	$path = wp_parse_url( $stored, PHP_URL_PATH );
	$path = is_string( $path ) ? $path : '';
	if ( '' !== $path && preg_match( '#/player_api\\.php$#i', $path ) ) {
		return $stored;
	}
	return untrailingslashit( $stored ) . '/player_api.php';
}

/**
 * Xtream API endpoint for a subscription (Service 1 / Service 2); uses per-service base URLs with legacy fallback.
 *
 * @param string $canonical svc1|svc2.
 * @return string Full player_api.php URL or empty.
 */
function ghost_manager_resolve_xtream_api_url_for_service( $canonical ) {
	$canonical = ghost_manager_normalize_subscription_type( $canonical );
	$key       = ( GHOST_MANAGER_SUB_SVC2 === $canonical ) ? 'xtream_player_api_base_svc2' : 'xtream_player_api_base_svc1';
	$base      = ghost_manager_get( 'urls.' . $key, '' );
	$base      = is_string( $base ) ? trim( $base ) : '';
	if ( '' !== $base ) {
		return ghost_manager_normalize_xtream_player_api_url( $base );
	}
	$legacy = ghost_manager_get( 'urls.xtream_player_api', '' );
	return ghost_manager_normalize_xtream_player_api_url( is_string( $legacy ) ? $legacy : '' );
}

/**
 * Legacy single-field Xtream URL → base host for migrating to per-service fields.
 *
 * @param string $legacy_url Stored legacy URL.
 * @return string Base without /player_api.php, or empty.
 */
function ghost_manager_xtream_legacy_url_to_base( $legacy_url ) {
	$legacy_url = trim( (string) $legacy_url );
	if ( '' === $legacy_url ) {
		return '';
	}
	$stripped = preg_replace( '#/player_api\\.php\\s*$#i', '', untrailingslashit( $legacy_url ) );
	return is_string( $stripped ) ? $stripped : '';
}

/**
 * Parse "14, 7, 3" style input into unique positive integers (descending).
 *
 * @param string $csv Raw CSV.
 * @return int[]
 */
function ghost_manager_parse_reminder_days_csv( $csv ) {
	$csv = is_string( $csv ) ? $csv : '';
	$parts = preg_split( '/[\s,]+/', $csv, -1, PREG_SPLIT_NO_EMPTY );
	$days  = array();
	foreach ( $parts as $p ) {
		$n = absint( $p );
		if ( $n > 0 ) {
			$days[] = $n;
		}
	}
	$days = array_values( array_unique( $days ) );
	rsort( $days, SORT_NUMERIC );
	return $days;
}

/**
 * Subject, intro, and display title for expiry warning emails (cron + optional overrides).
 *
 * @param string $base_title      e.g. "Service 1 Subscription".
 * @param int    $days_remaining  Full days until expiry (floor).
 * @return array{subject:string,intro:string,email_title:string}
 */
function ghost_manager_get_expiry_warning_email_copy( $base_title, $days_remaining ) {
	$email_title = $base_title . ' Expiring Soon';
	$d           = (string) max( 0, (int) $days_remaining );
	$vars        = array(
		'base_title'       => $base_title,
		'email_title'      => $email_title,
		'days_remaining'   => $d,
	);

	$subject_tpl = trim( (string) ghost_manager_get( 'cron.warning_subject', '' ) );
	if ( '' === $subject_tpl ) {
		$subject_tpl = ghost_manager_get( 'emails.service.subject_warning', '⚠️ {{email_title}}' );
	}
	$intro_tpl = trim( (string) ghost_manager_get( 'cron.warning_intro', '' ) );
	if ( '' === $intro_tpl ) {
		$intro_tpl = ghost_manager_get(
			'emails.service.intro_warning',
			'Your subscription is expiring soon ({{days_remaining}} days remaining). Please renew to avoid interruption.'
		);
	}

	return array(
		'subject'      => ghost_manager_replace_email_placeholders( $subject_tpl, $vars ),
		'intro'        => ghost_manager_replace_email_placeholders( $intro_tpl, $vars ),
		'email_title'  => $email_title,
	);
}

/**
 * Sanitize settings on save (Settings API).
 *
 * @param mixed $input Raw input.
 * @return array
 */
function ghost_manager_sanitize_settings( $input ) {
	$defaults = ghost_manager_default_settings();
	$stored   = ghost_manager_settings_unpack_from_db( get_option( 'ghost_manager_settings', array() ) );
	$out      = ghost_manager_merge_settings( $defaults, $stored );

	if ( ! is_array( $input ) ) {
		return $out;
	}

	// Features: each key must submit 0 or 1 (hidden + checkbox). If block missing, keep previous merged values.
	if ( isset( $input['features'] ) && is_array( $input['features'] ) ) {
		$feat_in = $input['features'];
		foreach ( array_keys( $defaults['features'] ) as $key ) {
			if ( array_key_exists( $key, $feat_in ) ) {
				$out['features'][ $key ] = ( '1' === (string) $feat_in[ $key ] );
			}
		}
	}

	if ( isset( $input['strings'] ) && is_array( $input['strings'] ) ) {
		$strings_multiline = array(
			'discord_tooltip',
			'discord_popup_title',
			'discord_popup_body',
			'discord_cta_label',
			'account_renew_prefix',
		);
		foreach ( $input['strings'] as $key => $val ) {
			if ( ! array_key_exists( $key, $defaults['strings'] ) ) {
				continue;
			}
			if ( 'reseller_login_notice' === $key ) {
				$out['strings'][ $key ] = wp_kses_post( wp_unslash( $val ) );
			} elseif ( in_array( $key, $strings_multiline, true ) ) {
				$out['strings'][ $key ] = sanitize_textarea_field( wp_unslash( $val ) );
			} else {
				$out['strings'][ $key ] = sanitize_text_field( wp_unslash( $val ) );
			}
		}
	}

	if ( isset( $input['urls'] ) && is_array( $input['urls'] ) ) {
		$urls_in = $input['urls'];
		$out['urls']['logo']                = esc_url_raw( wp_unslash( $urls_in['logo'] ?? '' ) );
		$out['urls']['discord']             = esc_url_raw( wp_unslash( $urls_in['discord'] ?? '' ) );
		$out['urls']['my_account_relative'] = sanitize_text_field( wp_unslash( $urls_in['my_account_relative'] ?? '' ) );
		if ( array_key_exists( 'xtream_player_api', $urls_in ) ) {
			$out['urls']['xtream_player_api'] = esc_url_raw( wp_unslash( (string) $urls_in['xtream_player_api'] ) );
		}
		$out['urls']['xtream_player_api_base_svc1'] = ghost_manager_sanitize_xtream_base_url( $urls_in['xtream_player_api_base_svc1'] ?? '' );
		$out['urls']['xtream_player_api_base_svc2'] = ghost_manager_sanitize_xtream_base_url( $urls_in['xtream_player_api_base_svc2'] ?? '' );
		$out['urls']['guide_crypto_com']    = esc_url_raw( wp_unslash( $urls_in['guide_crypto_com'] ?? '' ) );
		$out['urls']['guide_revolut']       = esc_url_raw( wp_unslash( $urls_in['guide_revolut'] ?? '' ) );
		$out['urls']['guide_transak']       = esc_url_raw( wp_unslash( $urls_in['guide_transak'] ?? '' ) );
	}

	if ( isset( $input['renew_urls'] ) && is_array( $input['renew_urls'] ) ) {
		foreach ( array( 'sub1', 'sub2' ) as $svc ) {
			if ( isset( $input['renew_urls'][ $svc ] ) ) {
				$out['renew_urls'][ $svc ] = sanitize_text_field( wp_unslash( $input['renew_urls'][ $svc ] ) );
			}
		}
		$legacy_r = ghost_manager_legacy_renew_url_option_keys();
		$map_old  = array( $legacy_r[0] => 'sub1', $legacy_r[1] => 'sub2' );
		foreach ( $map_old as $old_key => $new_key ) {
			if ( isset( $input['renew_urls'][ $old_key ] ) && ( ! isset( $out['renew_urls'][ $new_key ] ) || '' === trim( (string) $out['renew_urls'][ $new_key ] ) ) ) {
				$out['renew_urls'][ $new_key ] = sanitize_text_field( wp_unslash( $input['renew_urls'][ $old_key ] ) );
			}
		}
	}

	if ( isset( $input['taxonomies'] ) && is_array( $input['taxonomies'] ) ) {
		$out['taxonomies']['subscriptions_slug'] = sanitize_title( wp_unslash( $input['taxonomies']['subscriptions_slug'] ?? '' ) );
		$out['taxonomies']['reseller_slug']      = sanitize_title( wp_unslash( $input['taxonomies']['reseller_slug'] ?? '' ) );
	}

	if ( isset( $input['roles'] ) && is_array( $input['roles'] ) ) {
		$out['roles']['reseller'] = sanitize_key( wp_unslash( $input['roles']['reseller'] ?? '' ) );
	}

	if ( isset( $input['integrations'] ) && is_array( $input['integrations'] ) ) {
		$out['integrations']['accounts_exclude_login'] = sanitize_user( wp_unslash( $input['integrations']['accounts_exclude_login'] ?? '' ), true );
	}

	if ( isset( $input['payment_box'] ) && is_array( $input['payment_box'] ) ) {
		foreach ( array_keys( $defaults['payment_box'] ) as $pb_key ) {
			if ( ! isset( $input['payment_box'][ $pb_key ] ) ) {
				continue;
			}
			$raw = wp_unslash( $input['payment_box'][ $pb_key ] );
			if ( 'intro_html' === $pb_key ) {
				$out['payment_box'][ $pb_key ] = wp_kses_post( $raw );
			} else {
				$out['payment_box'][ $pb_key ] = sanitize_text_field( $raw );
			}
		}
	}

	if ( isset( $input['emails'] ) && is_array( $input['emails'] ) ) {
		if ( isset( $input['emails']['service'] ) && is_array( $input['emails']['service'] ) ) {
			$svc = $input['emails']['service'];
			foreach ( array( 'subject_details', 'subject_renewal', 'subject_warning', 'label_username', 'label_password', 'label_expiry', 'footer_note', 'value_not_set' ) as $k ) {
				if ( isset( $svc[ $k ] ) ) {
					$out['emails']['service'][ $k ] = sanitize_text_field( wp_unslash( $svc[ $k ] ) );
				}
			}
			foreach ( array( 'intro_details', 'intro_renewal', 'intro_warning' ) as $k ) {
				if ( isset( $svc[ $k ] ) ) {
					$out['emails']['service'][ $k ] = sanitize_textarea_field( wp_unslash( $svc[ $k ] ) );
				}
			}
			if ( isset( $svc['body_html'] ) ) {
				$out['emails']['service']['body_html'] = wp_kses_post( wp_unslash( $svc['body_html'] ) );
			}
		}
		if ( isset( $input['emails']['new_user'] ) && is_array( $input['emails']['new_user'] ) ) {
			$nu = $input['emails']['new_user'];
			foreach ( array( 'important_title', 'greeting', 'intro', 'button_label', 'fallback_text', 'benefits_intro', 'benefit_1', 'benefit_2', 'benefit_3' ) as $k ) {
				if ( isset( $nu[ $k ] ) ) {
					$out['emails']['new_user'][ $k ] = sanitize_text_field( wp_unslash( $nu[ $k ] ) );
				}
			}
			foreach ( array( 'important_line_1', 'important_line_2', 'important_line_3' ) as $k ) {
				if ( isset( $nu[ $k ] ) ) {
					$out['emails']['new_user'][ $k ] = wp_kses_post( wp_unslash( $nu[ $k ] ) );
				}
			}
			if ( isset( $nu['body_html'] ) ) {
				$out['emails']['new_user']['body_html'] = wp_kses_post( wp_unslash( $nu['body_html'] ) );
			}
		}
		if ( isset( $input['emails']['password_reset'] ) && is_array( $input['emails']['password_reset'] ) ) {
			$pr = $input['emails']['password_reset'];
			foreach ( array( 'heading', 'greeting', 'intro', 'button_label', 'footer_text', 'link_intro' ) as $k ) {
				if ( isset( $pr[ $k ] ) ) {
					$out['emails']['password_reset'][ $k ] = ( 'intro' === $k ) ? wp_kses_post( wp_unslash( $pr[ $k ] ) ) : sanitize_text_field( wp_unslash( $pr[ $k ] ) );
				}
			}
			if ( isset( $pr['body_html'] ) ) {
				$out['emails']['password_reset']['body_html'] = wp_kses_post( wp_unslash( $pr['body_html'] ) );
			}
		}
	}

	if ( isset( $input['cron'] ) && is_array( $input['cron'] ) ) {
		$cr = $input['cron'];
		if ( array_key_exists( 'multiple_reminders', $cr ) ) {
			$out['cron']['multiple_reminders'] = ( '1' === (string) $cr['multiple_reminders'] );
		}
		$out['cron']['window_min_days']    = isset( $cr['window_min_days'] ) ? max( 0, absint( $cr['window_min_days'] ) ) : $defaults['cron']['window_min_days'];
		$out['cron']['window_max_days']    = isset( $cr['window_max_days'] ) ? max( 0, absint( $cr['window_max_days'] ) ) : $defaults['cron']['window_max_days'];
		if ( $out['cron']['window_min_days'] > $out['cron']['window_max_days'] ) {
			$t                              = $out['cron']['window_min_days'];
			$out['cron']['window_min_days'] = $out['cron']['window_max_days'];
			$out['cron']['window_max_days'] = $t;
		}
		if ( isset( $cr['reminder_days_csv'] ) ) {
			$out['cron']['reminder_days_csv'] = sanitize_text_field( wp_unslash( $cr['reminder_days_csv'] ) );
		}
		if ( isset( $cr['warning_intro'] ) ) {
			$out['cron']['warning_intro'] = wp_kses_post( wp_unslash( $cr['warning_intro'] ) );
		}
		if ( isset( $cr['warning_subject'] ) ) {
			$out['cron']['warning_subject'] = sanitize_text_field( wp_unslash( $cr['warning_subject'] ) );
		}
	}

	// Drop legacy service string keys so the option stays a single source of truth.
	if ( isset( $out['strings'] ) && is_array( $out['strings'] ) ) {
		foreach ( ghost_manager_legacy_service_label_option_keys() as $legacy_key ) {
			unset( $out['strings'][ $legacy_key ] );
		}
	}

	// Migrate renew path keys to sub1/sub2 and drop legacy keys from stored option.
	if ( isset( $out['renew_urls'] ) && is_array( $out['renew_urls'] ) ) {
		$legacy_r = ghost_manager_legacy_renew_url_option_keys();
		if ( ( ! isset( $out['renew_urls']['sub1'] ) || '' === trim( (string) $out['renew_urls']['sub1'] ) ) && ! empty( $out['renew_urls'][ $legacy_r[0] ] ) ) {
			$out['renew_urls']['sub1'] = $out['renew_urls'][ $legacy_r[0] ];
		}
		if ( ( ! isset( $out['renew_urls']['sub2'] ) || '' === trim( (string) $out['renew_urls']['sub2'] ) ) && ! empty( $out['renew_urls'][ $legacy_r[1] ] ) ) {
			$out['renew_urls']['sub2'] = $out['renew_urls'][ $legacy_r[1] ];
		}
		foreach ( $legacy_r as $legacy_rk ) {
			unset( $out['renew_urls'][ $legacy_rk ] );
		}
	}

	return $out;
}
