<?php
/**
 * User creation, HTML emails, cron warnings, service email sender, My Account dashboard.
 *
 * @package Ghost_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * HTML mail type helper (referenced by name in add/remove_filter).
 *
 * @return string
 */
function ghost_set_html_mail() {
	return 'text/html';
}

/**
 * HTML body for new-user notification from settings.
 *
 * @param string $brand      Brand name (plain).
 * @param string $reset_link Password set link.
 * @return string
 */
function ghost_manager_build_new_user_email_message( $brand, $reset_link ) {
	$defaults = ghost_manager_default_settings();
	$nu       = ghost_manager_get_settings();
	$nu       = isset( $nu['emails']['new_user'] ) && is_array( $nu['emails']['new_user'] ) ? $nu['emails']['new_user'] : $defaults['emails']['new_user'];

	$brand_e    = esc_html( $brand );
	$reset_url  = esc_url( $reset_link );
	$reset_disp = esc_html( $reset_link );

	$important  = '<div style="background:#fff3cd;border:1px solid #ffeeba;padding:15px;border-radius:6px;margin-bottom:20px;">';
	$important .= '<strong>' . esc_html( $nu['important_title'] ) . '</strong><br>';
	$important .= wp_kses_post( ghost_manager_replace_email_placeholders( $nu['important_line_1'], array( 'brand' => $brand_e ) ) ) . '<br>';
	$important .= wp_kses_post( ghost_manager_replace_email_placeholders( $nu['important_line_2'], array( 'brand' => $brand_e ) ) ) . '<br><br>';
	$important .= wp_kses_post( ghost_manager_replace_email_placeholders( $nu['important_line_3'], array( 'brand' => $brand_e ) ) );
	$important .= '</div>';

	$benefits_intro = wp_kses_post( ghost_manager_replace_email_placeholders( $nu['benefits_intro'], array( 'brand' => $brand_e ) ) );
	$benefits_list  = '<li>' . esc_html( $nu['benefit_1'] ) . '</li><li>' . esc_html( $nu['benefit_2'] ) . '</li><li>' . esc_html( $nu['benefit_3'] ) . '</li>';

	$body_tpl = isset( $nu['body_html'] ) ? $nu['body_html'] : '';
	if ( '' === trim( (string) $body_tpl ) ) {
		$body_tpl = ghost_manager_default_new_user_body_html();
	}

	$vars = array(
		'logo_url'           => esc_url( ghost_manager_get( 'urls.logo' ) ),
		'brand'              => $brand_e,
		'reset_link_url'     => $reset_url,
		'reset_link_display' => $reset_disp,
		'important_box'      => $important,
		'greeting'           => esc_html( $nu['greeting'] ),
		'intro'              => esc_html( $nu['intro'] ),
		'button_label'       => esc_html( $nu['button_label'] ),
		'fallback_text'      => esc_html( $nu['fallback_text'] ),
		'benefits_intro'     => $benefits_intro,
		'benefits_list'      => $benefits_list,
	);

	return ghost_manager_replace_email_placeholders( $body_tpl, $vars );
}

/**
 * HTML body for password reset from settings.
 *
 * @param string $brand      Brand name (plain).
 * @param string $reset_link Reset link.
 * @return string
 */
function ghost_manager_build_password_reset_email_message( $brand, $reset_link ) {
	$defaults = ghost_manager_default_settings();
	$pr       = ghost_manager_get_settings();
	$pr       = isset( $pr['emails']['password_reset'] ) && is_array( $pr['emails']['password_reset'] ) ? $pr['emails']['password_reset'] : $defaults['emails']['password_reset'];

	$brand_e    = esc_html( $brand );
	$reset_url  = esc_url( $reset_link );
	$reset_disp = esc_html( $reset_link );

	$intro = wp_kses_post( ghost_manager_replace_email_placeholders( $pr['intro'], array( 'brand' => $brand_e ) ) );

	$body_tpl = isset( $pr['body_html'] ) ? $pr['body_html'] : '';
	if ( '' === trim( (string) $body_tpl ) ) {
		$body_tpl = ghost_manager_default_password_reset_body_html();
	}

	$vars = array(
		'logo_url'           => esc_url( ghost_manager_get( 'urls.logo' ) ),
		'brand'              => $brand_e,
		'reset_link_url'     => $reset_url,
		'reset_link_display' => $reset_disp,
		'heading'            => esc_html( $pr['heading'] ),
		'greeting'           => esc_html( $pr['greeting'] ),
		'intro'              => $intro,
		'button_label'       => esc_html( $pr['button_label'] ),
		'footer_text'        => esc_html( $pr['footer_text'] ),
		'link_intro'         => esc_html( $pr['link_intro'] ),
	);

	return ghost_manager_replace_email_placeholders( $body_tpl, $vars );
}

/**
 * Send Ghost+ / Ghost TV details email.
 *
 * @param int    $user_id User ID.
 * @param string $type    ghostplus|ghosttv.
 * @param string $mode    details|renewal|warning.
 * @param array  $opts    Optional. For warning: subject, intro, email_title from ghost_manager_get_expiry_warning_email_copy().
 */
function ghost_manager_send_email( $user_id, $type = 'ghostplus', $mode = 'details', $opts = array() ) {
	if ( ! ghost_manager_is_feature_enabled( 'email_system' ) ) {
		return;
	}

	$user = get_userdata( $user_id );
	if ( ! $user ) {
		return;
	}

	$gp = ghost_manager_get_service_label( 1 );
	$gt = ghost_manager_get_service_label( 2 );

	$not_set = ghost_manager_get( 'emails.service.value_not_set', 'Not set' );

	if ( 'ghosttv' === $type ) {
		$username   = get_user_meta( $user_id, 'ghosttv_username', true );
		$password   = get_user_meta( $user_id, 'ghosttv_password', true );
		$expiry     = get_user_meta( $user_id, 'ghosttv_expiry', true );
		$base_title = $gt . ' Subscription';
	} else {
		$username   = get_user_meta( $user_id, 'ghostplus_username', true );
		$password   = get_user_meta( $user_id, 'ghostplus_password', true );
		$expiry     = get_user_meta( $user_id, 'ghostplus_expiry', true );
		$base_title = $gp . ' Subscription';
	}

	if ( ! $username ) {
		$username = $not_set;
	}
	if ( ! $password ) {
		$password = $not_set;
	}
	if ( ! $expiry ) {
		$expiry = $not_set;
	}

	$mode = strtolower( trim( $mode ) );

	$label_u = ghost_manager_get( 'emails.service.label_username', 'Username' );
	$label_p = ghost_manager_get( 'emails.service.label_password', 'Password' );
	$label_e = ghost_manager_get( 'emails.service.label_expiry', 'Expiry Date' );
	$footer  = ghost_manager_get( 'emails.service.footer_note', 'Please keep these details safe. If you need help, contact support.' );

	if ( 'warning' === $mode ) {
		if ( ! empty( $opts['subject'] ) && isset( $opts['intro'] ) ) {
			$subject = $opts['subject'];
			$intro   = $opts['intro'];
			$title   = ! empty( $opts['email_title'] ) ? $opts['email_title'] : $base_title . ' Expiring Soon';
		} else {
			$days_remain = isset( $opts['days_remaining'] ) ? (int) $opts['days_remaining'] : 0;
			$copy        = ghost_manager_get_expiry_warning_email_copy( $base_title, $days_remain );
			$subject     = $copy['subject'];
			$intro       = $copy['intro'];
			$title       = $copy['email_title'];
		}
	} elseif ( 'renewal' === $mode ) {
		$email_title = $base_title . ' Renewal';
		$title       = $email_title;
		$subject_tpl = ghost_manager_get( 'emails.service.subject_renewal', '✅ {{email_title}}' );
		$intro       = ghost_manager_get( 'emails.service.intro_renewal', 'Your subscription has been successfully renewed. Your updated account details are below:' );
		$subject     = ghost_manager_replace_email_placeholders(
			$subject_tpl,
			array(
				'base_title'  => $base_title,
				'email_title' => $email_title,
			)
		);
	} else {
		$email_title = $base_title;
		$title       = $base_title;
		$subject_tpl = ghost_manager_get( 'emails.service.subject_details', '{{base_title}} - Your Account Details' );
		$intro       = ghost_manager_get( 'emails.service.intro_details', 'Here are your login details:' );
		$subject     = ghost_manager_replace_email_placeholders(
			$subject_tpl,
			array(
				'base_title'  => $base_title,
				'email_title' => $email_title,
			)
		);
	}

	$body_tpl = ghost_manager_get( 'emails.service.body_html', '' );
	if ( '' === trim( (string) $body_tpl ) ) {
		$body_tpl = ghost_manager_default_service_email_body_html();
	}

	$logo = esc_url( ghost_manager_get( 'urls.logo' ) );

	$body_vars = array(
		'logo_url'       => $logo,
		'title'          => esc_html( $title ),
		'intro'          => wp_kses_post( $intro ),
		'username'       => esc_html( $username ),
		'password'       => esc_html( $password ),
		'expiry'         => esc_html( $expiry ),
		'label_username' => esc_html( $label_u ),
		'label_password' => esc_html( $label_p ),
		'label_expiry'   => esc_html( $label_e ),
		'footer_note'    => wp_kses_post( $footer ),
	);

	$message = ghost_manager_replace_email_placeholders( $body_tpl, $body_vars );

	add_filter( 'wp_mail_content_type', 'ghost_set_html_mail' );

	wp_mail( $user->user_email, $subject, $message );

	remove_filter( 'wp_mail_content_type', 'ghost_set_html_mail' );
}

if ( ! function_exists( 'ghost_send_email' ) ) {
	/**
	 * Back-compat alias for code expecting the snippet function name.
	 *
	 * @param int    $user_id User ID.
	 * @param string $type    Service key.
	 * @param string $mode    Email mode.
	 */
	function ghost_send_email( $user_id, $type = 'ghostplus', $mode = 'details', $opts = array() ) {
		ghost_manager_send_email( $user_id, $type, $mode, $opts );
	}
}

/**
 * Schedule or clear cron.
 */
function ghost_manager_maybe_schedule_cron() {
	if ( ! ghost_manager_is_feature_enabled( 'ghost_expiry_cron' ) || ! ghost_manager_is_feature_enabled( 'email_system' ) ) {
		wp_clear_scheduled_hook( 'ghost_cron' );
		return;
	}
	if ( ! wp_next_scheduled( 'ghost_cron' ) ) {
		wp_schedule_event( time(), 'daily', 'ghost_cron' );
	}
}
add_action( 'init', 'ghost_manager_maybe_schedule_cron' );

/**
 * Daily expiry warnings.
 */
function ghost_manager_run_expiry_cron() {
	if ( ! ghost_manager_is_feature_enabled( 'ghost_expiry_cron' ) ) {
		return;
	}
	if ( ! ghost_manager_is_feature_enabled( 'email_system' ) ) {
		return;
	}

	$multi      = ghost_manager_get( 'cron.multiple_reminders', false );
	$thresholds = ghost_manager_parse_reminder_days_csv( (string) ghost_manager_get( 'cron.reminder_days_csv', '14,7,3,1' ) );
	$win_min    = (int) ghost_manager_get( 'cron.window_min_days', 8 );
	$win_max    = (int) ghost_manager_get( 'cron.window_max_days', 10 );

	$gp = ghost_manager_get_service_label( 1 );
	$gt = ghost_manager_get_service_label( 2 );

	$users = get_users( array( 'fields' => 'all' ) );

	foreach ( $users as $user ) {
		$uid = $user->ID;
		foreach ( array( 'ghostplus', 'ghosttv' ) as $service ) {
			$expiry = get_user_meta( $uid, "{$service}_expiry", true );
			if ( ! $expiry ) {
				continue;
			}

			$ts = strtotime( $expiry );
			if ( ! $ts ) {
				continue;
			}

			$days = (int) floor( ( $ts - time() ) / DAY_IN_SECONDS );
			if ( $days < 0 ) {
				continue;
			}

			$base_title = ( 'ghosttv' === $service ) ? ( $gt . ' Subscription' ) : ( $gp . ' Subscription' );

			if ( $multi ) {
				if ( empty( $thresholds ) ) {
					continue;
				}
				$snap_key = "{$service}_expiry_reminder_snapshot";
				$sent_key = "{$service}_expiry_reminder_days_sent";
				$snapshot = get_user_meta( $uid, $snap_key, true );
				$sent     = get_user_meta( $uid, $sent_key, true );
				if ( ! is_array( $sent ) ) {
					$sent = array();
				}
				if ( (string) $snapshot !== (string) $expiry ) {
					$sent = array();
				}

				$current = $days;
				foreach ( $thresholds as $t ) {
					$t = (int) $t;
					if ( $current === $t && ! in_array( $t, $sent, true ) ) {
						$copy = ghost_manager_get_expiry_warning_email_copy( $base_title, $days );
						ghost_manager_send_email( $uid, $service, 'warning', $copy );
						$sent[] = $t;
					}
				}

				update_user_meta( $uid, $snap_key, $expiry );
				update_user_meta( $uid, $sent_key, $sent );
			} else {
				$warned = get_user_meta( $uid, "{$service}_warned", true );
				if ( $days <= $win_max && $days >= $win_min && ! $warned ) {
					$copy = ghost_manager_get_expiry_warning_email_copy( $base_title, $days );
					ghost_manager_send_email( $uid, $service, 'warning', $copy );
					update_user_meta( $uid, "{$service}_warned", true );
				}
			}
		}
	}
}
add_action( 'ghost_cron', 'ghost_manager_run_expiry_cron' );

/**
 * Create WP users when subscription order completes.
 *
 * @param int $order_id Order ID.
 */
function ghost_manager_order_completed_create_users( $order_id ) {
	if ( ! ghost_manager_is_feature_enabled( 'user_creation_completed_order' ) || ! function_exists( 'wc_get_order' ) ) {
		return;
	}

	$order = wc_get_order( $order_id );
	if ( ! $order || $order->get_meta( '_ghost_created' ) ) {
		return;
	}

	foreach ( $order->get_items() as $item ) {
		$email = ghost_manager_get_order_item_account_email( $item );
		if ( ! $email || email_exists( $email ) ) {
			continue;
		}

		$password = wp_generate_password();
		$user_id  = wp_create_user( $email, $password, $email );

		if ( is_wp_error( $user_id ) ) {
			continue;
		}

		wp_new_user_notification( $user_id, null, 'user' );
	}

	$order->update_meta_data( '_ghost_created', true );
	$order->save();
}

/**
 * Register WooCommerce-only account hooks when the WooCommerce feature is on.
 */
function ghost_manager_register_woocommerce_account_hooks() {
	if ( ! class_exists( 'WooCommerce' ) || ! ghost_manager_is_feature_enabled( 'woocommerce' ) ) {
		return;
	}
	add_action( 'woocommerce_order_status_completed', 'ghost_manager_order_completed_create_users' );
	add_action( 'woocommerce_account_dashboard', 'ghost_manager_account_dashboard_subscriptions' );
}
add_action( 'plugins_loaded', 'ghost_manager_register_woocommerce_account_hooks', 20 );

/**
 * Custom new-user notification email HTML.
 *
 * @param array    $email   Email data.
 * @param WP_User  $user    User.
 * @param string   $blogname Site name.
 * @return array
 */
function ghost_manager_wp_new_user_notification_email( $email, $user, $blogname ) {
	if ( ! ghost_manager_is_feature_enabled( 'email_system' ) || ! ghost_manager_is_feature_enabled( 'custom_auth_emails' ) ) {
		return $email;
	}

	$reset_link = '';
	if ( preg_match( '/https?:\/\/\S+/', $email['message'], $matches ) ) {
		$reset_link = $matches[0];
	}

	$brand = ghost_manager_get( 'strings.brand_name', 'Ghost Pay' );

	add_filter(
		'wp_mail_content_type',
		function () {
			return 'text/html';
		}
	);

	$email['subject'] = ghost_manager_get( 'strings.new_user_email_subject', 'Your Ghost Pay Account Setup' );
	$email['message'] = ghost_manager_build_new_user_email_message( $brand, $reset_link );

	return $email;
}

add_filter( 'wp_new_user_notification_email', 'ghost_manager_wp_new_user_notification_email', 10, 3 );

/**
 * Password reset email HTML.
 *
 * @param string   $message     Original message (unused).
 * @param string   $key         Reset key.
 * @param string   $user_login  Login.
 * @param WP_User  $user_data   User.
 * @return string
 */
function ghost_manager_retrieve_password_message( $message, $key, $user_login, $user_data ) {
	if ( ! ghost_manager_is_feature_enabled( 'email_system' ) || ! ghost_manager_is_feature_enabled( 'custom_auth_emails' ) ) {
		return $message;
	}

	$reset_link = site_url( 'wp-login.php?action=rp&key=' . $key . '&login=' . rawurlencode( $user_login ) );

	$brand = ghost_manager_get( 'strings.brand_name', 'Ghost Pay' );

	add_filter(
		'wp_mail_content_type',
		function () {
			return 'text/html';
		}
	);

	return ghost_manager_build_password_reset_email_message( $brand, $reset_link );
}

add_filter( 'retrieve_password_message', 'ghost_manager_retrieve_password_message', 10, 4 );

/**
 * Password reset email subject from settings.
 *
 * @param string  $title       Default title.
 * @param string  $user_login  Login.
 * @param WP_User $user_data   User.
 * @return string
 */
function ghost_manager_retrieve_password_title( $title, $user_login, $user_data ) {
	if ( ! ghost_manager_is_feature_enabled( 'email_system' ) || ! ghost_manager_is_feature_enabled( 'custom_auth_emails' ) ) {
		return $title;
	}
	$custom = ghost_manager_get( 'strings.password_reset_email_subject', '' );
	if ( '' === trim( (string) $custom ) ) {
		return $title;
	}
	return $custom;
}
add_filter( 'retrieve_password_title', 'ghost_manager_retrieve_password_title', 10, 3 );

/**
 * My Account dashboard subscription cards.
 */
function ghost_manager_account_dashboard_subscriptions() {
	if ( ! ghost_manager_is_feature_enabled( 'my_account_subscription_details' ) || ! function_exists( 'WC' ) ) {
		return;
	}

	$user_id = get_current_user_id();

	$gp = ghost_manager_get_service_label( 1 );
	$gt = ghost_manager_get_service_label( 2 );

	$services = array(
		$gp => 'ghostplus',
		$gt => 'ghosttv',
	);

	$title   = ghost_manager_get( 'strings.subscription_details_title', 'Subscription Details' );
	$renew_t = ghost_manager_get( 'strings.renew_button', 'Renew Subscription' );
	$none    = ghost_manager_get( 'strings.no_subscriptions_message', 'No active subscriptions found.' );

	echo '<h2 style="margin-bottom:20px;">' . esc_html( $title ) . '</h2>';

	$has_any = false;

	$renew_map = array(
		'ghostplus' => ghost_manager_get( 'renew_urls.ghostplus', '/product/ghost-plus/' ),
		'ghosttv'   => ghost_manager_get( 'renew_urls.ghosttv', '/product/ghost-tv/' ),
	);

	foreach ( $services as $label => $key ) {
		$username = get_user_meta( $user_id, "{$key}_username", true );
		$password = get_user_meta( $user_id, "{$key}_password", true );
		$expiry   = get_user_meta( $user_id, "{$key}_expiry", true );

		if ( ! $username ) {
			continue;
		}

		$has_any = true;

		$timestamp = strtotime( $expiry );
		$days_left = $timestamp ? floor( ( $timestamp - time() ) / 86400 ) : null;

		$st_active   = ghost_manager_get( 'strings.account_status_active', 'Active' );
		$st_expired  = ghost_manager_get( 'strings.account_status_expired', 'Expired' );
		$st_expiring = ghost_manager_get( 'strings.account_status_expiring', 'Expiring Soon' );

		$status = $st_active;
		$color  = '#28a745';

		if ( $timestamp && $timestamp < time() ) {
			$status = $st_expired;
			$color  = '#dc3545';
		} elseif ( null !== $days_left && $days_left <= 5 ) {
			$status = $st_expiring;
			$color  = '#ffc107';
		}

		$renew_url = $renew_map[ $key ] ?? '#';

		echo '<div style="
            background:linear-gradient(145deg,#f8f9fb,#eef1f5);
            padding:25px;
            margin-bottom:20px;
            border-radius:12px;
            border:1px solid #e0e4ea;
            box-shadow:0 4px 12px rgba(0,0,0,0.06);
        ">';

		echo '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;">';
		echo '<h3 style="margin:0;">' . esc_html( $label ) . '</h3>';
		echo "<span style=\"
            background:{$color};
            color:#fff;
            padding:6px 12px;
            border-radius:20px;
            font-size:12px;
            font-weight:600;
        \">" . esc_html( $status ) . '</span>';
		echo '</div>';

		$lbl_u = ghost_manager_get( 'strings.account_card_label_username', 'Username' );
		$lbl_p = ghost_manager_get( 'strings.account_card_label_password', 'Password' );
		$lbl_e = ghost_manager_get( 'strings.account_card_label_expiry', 'Expiry' );

		echo '<p style="margin:6px 0;"><strong>' . esc_html( $lbl_u ) . ':</strong> ' . esc_html( $username ) . '</p>';
		echo '<p style="margin:6px 0;"><strong>' . esc_html( $lbl_p ) . ':</strong> ' . esc_html( $password ) . '</p>';

		$expiry_style = '';
		if ( null !== $days_left && $days_left <= 5 ) {
			$expiry_style = 'color:#dc3545;font-weight:bold;';
		}

		echo '<p style="margin:6px 0;"><strong>' . esc_html( $lbl_e ) . ':</strong> <span style="' . esc_attr( $expiry_style ) . '">' . esc_html( $expiry ) . '</span></p>';

		if ( null !== $days_left && $days_left <= 5 ) {
			$renew_prefix = ghost_manager_get( 'strings.account_renew_prefix', '🔄' );
			$renew_label  = '' === trim( (string) $renew_prefix ) ? $renew_t : trim( $renew_prefix . ' ' . $renew_t );
			echo '<a href="' . esc_url( $renew_url ) . '" style="
                display:inline-block;
                margin-top:15px;
                background:#dc3545;
                color:#fff;
                padding:10px 18px;
                border-radius:6px;
                text-decoration:none;
                font-weight:600;
                transition:0.2s;
            "
            onmouseover="this.style.opacity=\'0.85\'"
            onmouseout="this.style.opacity=\'1\'">' . esc_html( $renew_label ) . '</a>';
		}

		echo '</div>';
	}

	if ( ! $has_any ) {
		echo '<p>' . esc_html( $none ) . '</p>';
	}
}

