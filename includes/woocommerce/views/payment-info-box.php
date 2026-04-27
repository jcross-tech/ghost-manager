<?php
/**
 * Checkout payment guide box (Crypto / Revolut / Transak links and copy).
 *
 * @package Ghost_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$s  = ghost_manager_get_settings();
$pb = isset( $s['payment_box'] ) && is_array( $s['payment_box'] ) ? $s['payment_box'] : ghost_manager_default_settings()['payment_box'];

$crypto_url = esc_url( ghost_manager_get( 'urls.guide_crypto_com' ) );
$revolut_url = esc_url( ghost_manager_get( 'urls.guide_revolut' ) );
$transak_url = esc_url( ghost_manager_get( 'urls.guide_transak' ) );
?>
<div id="payment-info-wrapper" style="margin-top:15px;">

	<div style="
        background:#eef2f7;
        border:1px solid #d6dbe3;
        color:#2c3e50;
        padding:18px;
        border-radius:12px;
        margin-bottom:14px;
        line-height:1.6;
    ">

		<div style="font-weight:700; margin-bottom:8px;">
			<?php echo esc_html( $pb['title'] ); ?>
		</div>

		<div style="font-size:14px;">
			<?php echo wp_kses_post( $pb['intro_html'] ); ?>
		</div>

		<div style="
            background:#ffffff;
            border:1px solid #d6dbe3;
            padding:14px;
            border-radius:10px;
            margin-top:12px;
        ">
			<div style="font-weight:700; margin-bottom:6px;">
				<?php echo esc_html( $pb['recommended_title'] ); ?>
			</div>

			<div style="font-size:13px;">
				<?php
				for ( $i = 1; $i <= 4; $i++ ) {
					$key = 'bullet_' . $i;
					$line = isset( $pb[ $key ] ) ? trim( (string) $pb[ $key ] ) : '';
					if ( '' !== $line ) {
						echo '&bull; ' . esc_html( $line ) . '<br>';
					}
				}
				?>
			</div>
		</div>

		<div style="font-size:13px; margin-top:12px;">
			<?php echo esc_html( $pb['follow_guides'] ); ?>
		</div>

		<div style="
            display:flex;
            gap:16px;
            flex-wrap:wrap;
            margin-top:12px;
        ">

			<div style="display:flex; flex-direction:column;">
				<a href="<?php echo $crypto_url; ?>" target="_blank" rel="noopener noreferrer" style="
                    display:inline-block;
                    background:#16a34a;
                    color:#fff;
                    padding:10px 14px;
                    border-radius:6px;
                    text-decoration:none;
                    font-weight:600;
                    font-size:13px;
                ">
					<?php echo esc_html( $pb['crypto_button'] ); ?>
				</a>
				<span style="font-size:12px;color:#6b7280;margin-top:6px;max-width:180px;">
					<?php echo esc_html( $pb['crypto_helper'] ); ?>
				</span>
			</div>

			<div style="display:flex; flex-direction:column;">
				<a href="<?php echo $revolut_url; ?>" target="_blank" rel="noopener noreferrer" style="
                    display:inline-block;
                    background:#e11d2e;
                    color:#fff;
                    padding:10px 14px;
                    border-radius:6px;
                    text-decoration:none;
                    font-weight:600;
                    font-size:13px;
                ">
					<?php echo esc_html( $pb['revolut_button'] ); ?>
				</a>
				<span style="font-size:12px;color:#6b7280;margin-top:6px;max-width:180px;">
					<?php echo esc_html( $pb['revolut_helper'] ); ?>
				</span>
			</div>

			<div style="display:flex; flex-direction:column;">
				<a href="<?php echo $transak_url; ?>" target="_blank" rel="noopener noreferrer" style="
                    display:inline-block;
                    background:#1e3a8a;
                    color:#fff;
                    padding:10px 14px;
                    border-radius:6px;
                    text-decoration:none;
                    font-weight:600;
                    font-size:13px;
                ">
					<?php echo esc_html( $pb['transak_button'] ); ?>
				</a>
				<span style="font-size:12px;color:#6b7280;margin-top:6px;max-width:180px;">
					<?php echo esc_html( $pb['transak_helper'] ); ?>
				</span>
			</div>

		</div>

	</div>

</div>
