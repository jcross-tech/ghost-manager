<?php
/**
 * Admin Customer Manager (subscription accounts UI; excludes resellers from the list).
 *
 * @package Ghost_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resend credentials via admin-post (matches snippet action name).
 */
function ghost_manager_handle_admin_resend() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$user_id = isset( $_GET['user_id'] ) ? intval( $_GET['user_id'] ) : 0;
	$type    = isset( $_GET['type'] ) ? sanitize_text_field( wp_unslash( $_GET['type'] ) ) : GHOST_MANAGER_SUB_SVC1;
	$mode    = ( isset( $_GET['mode'] ) && 'renewal' === $_GET['mode'] ) ? 'renewal' : 'details';

	ghost_send_email( $user_id, ghost_manager_normalize_subscription_type( $type ), $mode );

	if ( ! empty( $_SERVER['HTTP_REFERER'] ) ) {
		wp_safe_redirect( esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) );
	} else {
		wp_safe_redirect( admin_url() );
	}
	exit;
}
add_action( 'admin_post_ghost_resend', 'ghost_manager_handle_admin_resend' );

/**
 * AJAX: save inline account fields from Customer Manager.
 */
function ghost_manager_ajax_save_account() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die();
	}

	$user_id = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;
	$raw     = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';
	if ( '' === $raw ) {
		wp_die();
	}
	$type = ghost_manager_normalize_subscription_type( $raw );

	if ( GHOST_MANAGER_SUB_SVC1 === $type ) {
		$username = isset( $_POST['username'] ) ? sanitize_text_field( wp_unslash( $_POST['username'] ) ) : '';
		ghost_manager_update_user_subscription_meta( $user_id, GHOST_MANAGER_SUB_SVC1, 'username', $username );
		ghost_manager_update_user_subscription_meta( $user_id, GHOST_MANAGER_SUB_SVC1, 'password', isset( $_POST['password'] ) ? sanitize_text_field( wp_unslash( $_POST['password'] ) ) : '' );
		ghost_manager_update_user_subscription_meta( $user_id, GHOST_MANAGER_SUB_SVC1, 'expiry', isset( $_POST['expiry'] ) ? sanitize_text_field( wp_unslash( $_POST['expiry'] ) ) : '' );
		delete_transient( 'ghost_exp_' . md5( $username ) );
	}

	if ( GHOST_MANAGER_SUB_SVC2 === $type ) {
		$username = isset( $_POST['username'] ) ? sanitize_text_field( wp_unslash( $_POST['username'] ) ) : '';
		ghost_manager_update_user_subscription_meta( $user_id, GHOST_MANAGER_SUB_SVC2, 'username', $username );
		ghost_manager_update_user_subscription_meta( $user_id, GHOST_MANAGER_SUB_SVC2, 'password', isset( $_POST['password'] ) ? sanitize_text_field( wp_unslash( $_POST['password'] ) ) : '' );
		ghost_manager_update_user_subscription_meta( $user_id, GHOST_MANAGER_SUB_SVC2, 'expiry', isset( $_POST['expiry'] ) ? sanitize_text_field( wp_unslash( $_POST['expiry'] ) ) : '' );
		delete_transient( 'ghost_exp_' . md5( $username ) );
	}

	wp_die();
}
add_action( 'wp_ajax_ghost_save_account', 'ghost_manager_ajax_save_account' );

/**
 * AJAX: quick add user (with optional API expiry).
 */
function ghost_manager_ajax_quick_add() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error();
	}

	$email    = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$username = isset( $_POST['username'] ) ? sanitize_text_field( wp_unslash( $_POST['username'] ) ) : '';
	$password = isset( $_POST['password'] ) ? sanitize_text_field( wp_unslash( $_POST['password'] ) ) : '';
	$expiry   = isset( $_POST['expiry'] ) ? sanitize_text_field( wp_unslash( $_POST['expiry'] ) ) : '';
	$service  = isset( $_POST['service'] ) ? sanitize_text_field( wp_unslash( $_POST['service'] ) ) : '';

	if ( ! $email ) {
		wp_send_json_error();
	}

	if ( ! $username ) {
		$parts    = explode( '@', $email );
		$username = $parts[0];
	}
	if ( ! $password ) {
		$password = wp_generate_password( 10, false );
	}

	$user_id = wp_create_user( $username, $password, $email );

	if ( is_wp_error( $user_id ) ) {
		wp_send_json_error();
	}

	$service = ghost_manager_normalize_subscription_type( $service );

	if ( GHOST_MANAGER_SUB_SVC1 === $service ) {
		ghost_manager_update_user_subscription_meta( $user_id, GHOST_MANAGER_SUB_SVC1, 'username', $username );
		ghost_manager_update_user_subscription_meta( $user_id, GHOST_MANAGER_SUB_SVC1, 'password', $password );
		if ( ! $expiry ) {
			$expiry = ghost_manager_get_xtream_expiry( $username, $password );
		}
		ghost_manager_update_user_subscription_meta( $user_id, GHOST_MANAGER_SUB_SVC1, 'expiry', $expiry ? $expiry : '' );
	}

	if ( GHOST_MANAGER_SUB_SVC2 === $service ) {
		ghost_manager_update_user_subscription_meta( $user_id, GHOST_MANAGER_SUB_SVC2, 'username', $username );
		ghost_manager_update_user_subscription_meta( $user_id, GHOST_MANAGER_SUB_SVC2, 'password', $password );
		if ( ! $expiry ) {
			$expiry = ghost_manager_get_xtream_expiry( $username, $password );
		}
		ghost_manager_update_user_subscription_meta( $user_id, GHOST_MANAGER_SUB_SVC2, 'expiry', $expiry ? $expiry : '' );
	}

	wp_send_json_success();
}
add_action( 'wp_ajax_ghost_quick_add', 'ghost_manager_ajax_quick_add' );

/**
 * Render Customer Manager admin page.
 */
function ghost_manager_render_accounts_manager_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';

	$exclude_login  = ghost_manager_get( 'integrations.accounts_exclude_login', '' );
	$admin          = get_user_by( 'login', $exclude_login );
	$exclude_id     = $admin ? $admin->ID : 0;
	$reseller_role  = sanitize_key( (string) ghost_manager_get( 'roles.reseller', 'reseller' ) );
	$role_not_in    = $reseller_role ? array( $reseller_role ) : array();

	$user_args = array(
		'search'         => '*' . $search . '*',
		'search_columns' => array( 'user_login', 'user_email' ),
		'exclude'        => array_filter( array( $exclude_id ) ),
	);
	if ( ! empty( $role_not_in ) ) {
		$user_args['role__not_in'] = $role_not_in;
	}

	$users = get_users( $user_args );

	$gp_label = ghost_manager_get_service_label( 1 );
	$tv_label = ghost_manager_get_service_label( 2 );
	$svc1    = GHOST_MANAGER_SUB_SVC1;
	$svc2    = GHOST_MANAGER_SUB_SVC2;
	?>
	<div class="wrap ghost-accounts">
		<h1><?php esc_html_e( 'Customer Manager', 'ghost-manager' ); ?></h1>

		<style>
		.ghost-user-card {
			background: #fff;
			border-radius: 10px;
			padding: 14px;
			margin-bottom: 14px;
			box-shadow: 0 2px 6px rgba(0,0,0,0.05);
		}
		.ghost-label { font-size: 12px; color: #888; margin-top: 6px; }
		.ghost-email { margin-bottom: 16px; }
		.ghost-toggle {
			padding: 6px 10px; font-size: 13px; border-radius: 20px;
			margin-top: 10px; margin-right: 6px;
		}
		.ghost-fields { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 10px; }
		.ghost-fields div { flex: 1; min-width: 140px; }
		.ghost-fields label { display: block; font-size: 11px; color: #666; margin-bottom: 3px; }
		.ghost-actions { display: flex; gap: 6px; margin-top: 10px; }
		.ghost-actions .button {
			flex: 1; font-size: 13px; padding: 6px 8px;
			display: inline-flex; justify-content: center; align-items: center;
		}
		#ghost-add-user { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 24px; }
		#ghost-add-user input, #ghost-add-user select {
			height: 40px; padding: 6px 10px; font-size: 14px;
		}
		@media (max-width: 768px) {
			#ghost-add-user { flex-direction: column; gap: 10px; background: #fff; padding: 12px; border-radius: 10px; }
			#ghost-add-user input, #ghost-add-user select { width: 100%; height: 42px; }
			#ghost-add-user button { width: 100%; height: 44px; }
			.ghost-fields { flex-direction: column; }
			.ghost-actions { flex-direction: column; }
		}
		</style>

		<form method="get" style="margin-bottom:20px;display:flex;gap:8px;">
			<input type="hidden" name="page" value="accounts-manager">
			<input type="text" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="Search users..." style="width:220px;">
			<button class="button" type="submit">Search</button>
		</form>

		<h2>Quick Add User</h2>
		<form id="ghost-add-user">
			<input type="email" name="email" placeholder="Email" required>
			<input type="text" name="username" placeholder="Username">
			<input type="text" name="password" placeholder="Password">
			<div style="width:100%;">
				<label style="font-size:12px;color:#666;display:block;margin-bottom:4px;">Expiry Date</label>
				<input type="date" name="expiry">
			</div>
			<select name="service">
				<option value="<?php echo esc_attr( $svc1 ); ?>"><?php echo esc_html( $gp_label ); ?></option>
				<option value="<?php echo esc_attr( $svc2 ); ?>"><?php echo esc_html( $tv_label ); ?></option>
			</select>
			<button class="button button-primary" type="submit">Add</button>
		</form>

		<?php
		foreach ( $users as $user ) {
			$gp_user = ghost_manager_get_user_subscription_meta( $user->ID, $svc1, 'username' );
			$gp_pass = ghost_manager_get_user_subscription_meta( $user->ID, $svc1, 'password' );
			$gp_exp  = ghost_manager_get_xtream_expiry( $gp_user, $gp_pass ) ?: ghost_manager_get_user_subscription_meta( $user->ID, $svc1, 'expiry' );

			$tv_user = ghost_manager_get_user_subscription_meta( $user->ID, $svc2, 'username' );
			$tv_pass = ghost_manager_get_user_subscription_meta( $user->ID, $svc2, 'password' );
			$tv_exp  = ghost_manager_get_xtream_expiry( $tv_user, $tv_pass ) ?: ghost_manager_get_user_subscription_meta( $user->ID, $svc2, 'expiry' );
			?>

			<div class="ghost-user-card">
				<div class="ghost-label">User:</div>
				<strong><?php echo esc_html( $user->user_login ); ?></strong>

				<div class="ghost-label">Email:</div>
				<div class="ghost-email"><?php echo esc_html( $user->user_email ); ?></div>

				<button type="button" class="button ghost-toggle">▶ <?php echo esc_html( $gp_label ); ?></button>
				<button type="button" class="button ghost-toggle">▶ <?php echo esc_html( $tv_label ); ?></button>

				<div class="ghost-panel" style="display:none;">
					<h3><?php echo esc_html( $gp_label ); ?></h3>
					<div class="ghost-fields">
						<div>
							<label>Username</label>
							<input class="gp-user" value="<?php echo esc_attr( $gp_user ); ?>">
						</div>
						<div>
							<label>Password</label>
							<input class="gp-pass" value="<?php echo esc_attr( $gp_pass ); ?>">
						</div>
						<div>
							<label>Expiry Date</label>
							<input class="gp-exp" type="date" value="<?php echo esc_attr( $gp_exp ); ?>" readonly>
						</div>
					</div>
					<div class="ghost-actions">
						<a href="<?php echo esc_url( admin_url( 'admin-post.php?action=ghost_resend&type=' . rawurlencode( $svc1 ) . '&user_id=' . $user->ID ) ); ?>" class="button ghost-btn-send">📧 Send</a>
						<a href="<?php echo esc_url( admin_url( 'admin-post.php?action=ghost_resend&type=' . rawurlencode( $svc1 ) . '&mode=renewal&user_id=' . $user->ID ) ); ?>" class="button ghost-btn-renew">🔄 Renew</a>
						<button type="button" class="button ghost-save" data-user="<?php echo esc_attr( (string) $user->ID ); ?>" data-type="<?php echo esc_attr( $svc1 ); ?>">💾 Save</button>
					</div>
				</div>

				<div class="ghost-panel" style="display:none;">
					<h3><?php echo esc_html( $tv_label ); ?></h3>
					<div class="ghost-fields">
						<div>
							<label>Username</label>
							<input class="tv-user" value="<?php echo esc_attr( $tv_user ); ?>">
						</div>
						<div>
							<label>Password</label>
							<input class="tv-pass" value="<?php echo esc_attr( $tv_pass ); ?>">
						</div>
						<div>
							<label>Expiry Date</label>
							<input class="tv-exp" type="date" value="<?php echo esc_attr( $tv_exp ); ?>" readonly>
						</div>
					</div>
					<div class="ghost-actions">
						<a href="<?php echo esc_url( admin_url( 'admin-post.php?action=ghost_resend&type=' . rawurlencode( $svc2 ) . '&user_id=' . $user->ID ) ); ?>" class="button ghost-btn-send">📧 Send</a>
						<a href="<?php echo esc_url( admin_url( 'admin-post.php?action=ghost_resend&type=' . rawurlencode( $svc2 ) . '&mode=renewal&user_id=' . $user->ID ) ); ?>" class="button ghost-btn-renew">🔄 Renew</a>
						<button type="button" class="button ghost-save" data-user="<?php echo esc_attr( (string) $user->ID ); ?>" data-type="<?php echo esc_attr( $svc2 ); ?>">💾 Save</button>
					</div>
				</div>
			</div>
			<?php
		}
		?>
	</div>

	<script>
	const GM_SUB_SVC1 = <?php echo wp_json_encode( $svc1 ); ?>;
	const GM_SUB_SVC2 = <?php echo wp_json_encode( $svc2 ); ?>;
	document.querySelectorAll(".ghost-toggle").forEach((btn, index) => {
		btn.addEventListener("click", function(){
			let panels = this.parentNode.querySelectorAll(".ghost-panel");
			let panel = panels[index % 2];
			panel.style.display = panel.style.display === "none" ? "block" : "none";
		});
	});
	document.querySelectorAll(".ghost-save").forEach(btn => {
		btn.addEventListener("click", function(){
			let original = this.innerHTML;
			let parent = this.closest(".ghost-user-card");
			let userId = this.dataset.user;
			let type = this.dataset.type;
			let username = parent.querySelector(type === GM_SUB_SVC1 ? ".gp-user" : ".tv-user").value;
			let password = parent.querySelector(type === GM_SUB_SVC1 ? ".gp-pass" : ".tv-pass").value;
			let expiry  = parent.querySelector(type === GM_SUB_SVC1 ? ".gp-exp"  : ".tv-exp").value;
			fetch(ajaxurl, {
				method: "POST",
				headers: {"Content-Type": "application/x-www-form-urlencoded"},
				body: new URLSearchParams({
					action: "ghost_save_account",
					user_id: userId,
					type: type,
					username: username,
					password: password,
					expiry: expiry
				})
			}).then(() => {
				this.innerHTML = "Saved ✅";
				this.style.background = "#46b450";
				this.style.color = "#fff";
				setTimeout(() => {
					this.innerHTML = original;
					this.style.background = "";
					this.style.color = "";
				}, 2000);
			});
		});
	});
	document.querySelector("#ghost-add-user").addEventListener("submit", function(e){
		e.preventDefault();
		let form = this;
		let btn = form.querySelector("button");
		let original = btn.innerHTML;
		btn.innerHTML = "Adding...";
		btn.disabled = true;
		fetch(ajaxurl, {
			method: "POST",
			headers: {"Content-Type": "application/x-www-form-urlencoded"},
			body: new URLSearchParams({
				action: "ghost_quick_add",
				email: form.email.value,
				username: form.username.value,
				password: form.password.value,
				expiry: form.expiry.value,
				service: form.service.value
			})
		})
		.then(res => res.json())
		.then(data => {
			if (data.success) {
				btn.innerHTML = "Added ✅";
				setTimeout(() => location.reload(), 800);
			} else {
				btn.innerHTML = "Error ❌";
				btn.disabled = false;
			}
		})
		.catch(() => {
			btn.innerHTML = "Error ❌";
			btn.disabled = false;
		});
	});
	</script>
	<?php
}
