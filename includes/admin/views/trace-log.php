<?php
/**
 * View Ghost Manager save-trace log in admin.
 *
 * @package Ghost_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$path   = ghost_manager_save_trace_log_path();
$exists = $path && is_readable( $path );
$data   = ghost_manager_read_save_trace_log();
?>
<div class="wrap ghost-manager-trace-wrap">
	<h1><?php esc_html_e( 'Logs', 'ghost-manager' ); ?></h1>

	<p class="description">
		<?php esc_html_e( 'Lines appear here when define( \'GHOST_MANAGER_TRACE_SAVES\', true ); is set in wp-config.php and you save settings on the main Ghost Manager screen.', 'ghost-manager' ); ?>
	</p>

	<?php if ( ! empty( $_GET['trace_cleared'] ) ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Trace log cleared.', 'ghost-manager' ); ?></p></div>
	<?php endif; ?>

	<p>
		<strong><?php esc_html_e( 'File:', 'ghost-manager' ); ?></strong>
		<code><?php echo esc_html( $path ? $path : '(unknown)' ); ?></code>
		<?php if ( $exists ) : ?>
			<span class="description"> — <?php echo esc_html( size_format( $data['bytes_on_disk'] ) ); ?></span>
		<?php endif; ?>
	</p>

	<?php if ( $exists && $data['truncated'] ) : ?>
		<div class="notice notice-warning"><p><?php esc_html_e( 'File is large; only the end of the log is shown (last 1 MB).', 'ghost-manager' ); ?></p></div>
	<?php endif; ?>

	<?php if ( ! $exists ) : ?>
		<div class="notice notice-info"><p><?php esc_html_e( 'No log file yet, or the file is not readable. Save settings once with tracing enabled, or check file permissions on wp-content.', 'ghost-manager' ); ?></p></div>
	<?php else : ?>
		<div class="ghost-manager-trace-log" role="region" aria-label="<?php esc_attr_e( 'Trace log contents', 'ghost-manager' ); ?>"><?php echo esc_html( $data['content'] ); ?></div>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=ghost-manager-save-trace' ) ); ?>" style="margin-top:16px;">
		<?php wp_nonce_field( 'ghost_manager_clear_trace' ); ?>
		<input type="hidden" name="ghost_manager_clear_trace" value="1" />
		<?php
		$clear_attrs = array();
		if ( ! $exists ) {
			$clear_attrs['disabled'] = 'disabled';
		} else {
			$clear_attrs['onclick'] = 'return confirm(' . wp_json_encode( __( 'Delete the trace log file on the server?', 'ghost-manager' ) ) . ');';
		}
		submit_button( __( 'Clear log file', 'ghost-manager' ), 'secondary', 'submit', false, $clear_attrs );
		?>
	</form>

	<p class="description">
		<?php esc_html_e( 'General PHP and WordPress messages still go to wp-content/debug.log (or your host error log). This screen only shows the Ghost Manager save trace file.', 'ghost-manager' ); ?>
	</p>
</div>
