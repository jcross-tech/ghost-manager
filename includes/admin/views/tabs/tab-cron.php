<?php
/**
 * Settings tab: Expiry cron & renewal reminder emails.
 *
 * @package Ghost_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cr = isset( $settings['cron'] ) ? $settings['cron'] : ghost_manager_default_settings()['cron'];
?>
<p class="description">
	<?php esc_html_e( 'The expiry job runs once per day on the WordPress cron hook:', 'ghost-manager' ); ?>
	<code>ghost_cron</code>.
	<?php esc_html_e( 'Turn it on under Features → “Daily cron: expiry warning emails” and ensure the Email system master feature is enabled.', 'ghost-manager' ); ?>
</p>

<h2 class="title"><?php esc_html_e( 'Reminder schedule', 'ghost-manager' ); ?></h2>
<table class="form-table" role="presentation">
	<tr>
		<th scope="row"><?php esc_html_e( 'Multiple reminders', 'ghost-manager' ); ?></th>
		<td>
			<label>
				<?php ghost_manager_hidden_checkbox( 'ghost_manager_settings[cron][multiple_reminders]', ! empty( $cr['multiple_reminders'] ) ); ?>
				<?php esc_html_e( 'Send on specific days before expiry (list below)', 'ghost-manager' ); ?>
			</label>
			<p class="description"><?php esc_html_e( 'When unchecked, a single email is sent the first time a subscription is inside the day window (legacy behaviour).', 'ghost-manager' ); ?></p>
		</td>
	</tr>
	<tr>
		<th><label for="gm-cron-win-min"><?php esc_html_e( 'Single reminder: minimum days left', 'ghost-manager' ); ?></label></th>
		<td>
			<input class="small-text" id="gm-cron-win-min" type="number" min="0" name="ghost_manager_settings[cron][window_min_days]" value="<?php echo esc_attr( (string) $cr['window_min_days'] ); ?>" />
			<p class="description"><?php esc_html_e( 'Used only when “Multiple reminders” is off. Default 8 matches the original plugin window.', 'ghost-manager' ); ?></p>
		</td>
	</tr>
	<tr>
		<th><label for="gm-cron-win-max"><?php esc_html_e( 'Single reminder: maximum days left', 'ghost-manager' ); ?></label></th>
		<td>
			<input class="small-text" id="gm-cron-win-max" type="number" min="0" name="ghost_manager_settings[cron][window_max_days]" value="<?php echo esc_attr( (string) $cr['window_max_days'] ); ?>" />
		</td>
	</tr>
	<tr>
		<th><label for="gm-cron-days"><?php esc_html_e( 'Reminder days (comma-separated)', 'ghost-manager' ); ?></label></th>
		<td>
			<input class="large-text" id="gm-cron-days" type="text" name="ghost_manager_settings[cron][reminder_days_csv]" value="<?php echo esc_attr( $cr['reminder_days_csv'] ); ?>" placeholder="14, 7, 3, 1" />
			<p class="description"><?php esc_html_e( 'Used when “Multiple reminders” is on. One email per listed day (e.g. 14, 7, 3, 1 sends up to four emails per subscription period). Duplicates are ignored.', 'ghost-manager' ); ?></p>
		</td>
	</tr>
</table>

<h2 class="title"><?php esc_html_e( 'Renewal / expiry notice email', 'ghost-manager' ); ?></h2>
<p class="description"><?php esc_html_e( 'Leave blank to use the “Service subscription emails” warning subject and intro on the Emails tab. Placeholders:', 'ghost-manager' ); ?> <code>{{base_title}}</code>, <code>{{email_title}}</code>, <code>{{days_remaining}}</code></p>
<table class="form-table" role="presentation">
	<tr>
		<th><label for="gm-cron-subj"><?php esc_html_e( 'Custom subject (optional)', 'ghost-manager' ); ?></label></th>
		<td><input class="large-text" id="gm-cron-subj" type="text" name="ghost_manager_settings[cron][warning_subject]" value="<?php echo esc_attr( $cr['warning_subject'] ); ?>" placeholder="⚠️ {{email_title}}" /></td>
	</tr>
	<tr>
		<th><label for="gm-cron-intro"><?php esc_html_e( 'Custom intro (optional)', 'ghost-manager' ); ?></label></th>
		<td><textarea class="large-text" rows="4" id="gm-cron-intro" name="ghost_manager_settings[cron][warning_intro]" placeholder="<?php esc_attr_e( 'Plain text or short HTML; {{days_remaining}} is replaced.', 'ghost-manager' ); ?>"><?php echo esc_textarea( $cr['warning_intro'] ); ?></textarea></td>
	</tr>
</table>
