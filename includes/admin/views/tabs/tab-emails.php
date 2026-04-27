<?php
/**
 * Settings tab: Email templates and subjects.
 *
 * @package Ghost_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$svc  = $settings['emails']['service'];
$nu   = $settings['emails']['new_user'];
$pr   = $settings['emails']['password_reset'];
?>
<p class="description"><?php esc_html_e( 'Use placeholders in HTML templates and subjects where noted. User-specific values are escaped when emails are sent.', 'ghost-manager' ); ?></p>

<h2 class="title"><?php esc_html_e( 'Subjects', 'ghost-manager' ); ?></h2>
<table class="form-table" role="presentation">
	<tr>
		<th><label for="gm-subj-new"><?php esc_html_e( 'New user email subject', 'ghost-manager' ); ?></label></th>
		<td><input class="large-text" id="gm-subj-new" type="text" name="ghost_manager_settings[strings][new_user_email_subject]" value="<?php echo esc_attr( $settings['strings']['new_user_email_subject'] ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="gm-subj-pw"><?php esc_html_e( 'Password reset email subject', 'ghost-manager' ); ?></label></th>
		<td><input class="large-text" id="gm-subj-pw" type="text" name="ghost_manager_settings[strings][password_reset_email_subject]" value="<?php echo esc_attr( $settings['strings']['password_reset_email_subject'] ); ?>" /></td>
	</tr>
</table>

<h2 class="title"><?php esc_html_e( 'Service subscription emails (Service 1 / Service 2)', 'ghost-manager' ); ?></h2>
<p class="description"><?php esc_html_e( 'Subjects: {{base_title}} (e.g. Service 1 Subscription), {{email_title}} (full heading). Warning subject and intro also support {{days_remaining}} (see Expiry cron tab for schedule).', 'ghost-manager' ); ?></p>
<table class="form-table" role="presentation">
	<tr>
		<th><label for="gm-svc-subj-d"><?php esc_html_e( 'Subject (account details)', 'ghost-manager' ); ?></label></th>
		<td><input class="large-text" id="gm-svc-subj-d" type="text" name="ghost_manager_settings[emails][service][subject_details]" value="<?php echo esc_attr( $svc['subject_details'] ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="gm-svc-subj-r"><?php esc_html_e( 'Subject (renewal)', 'ghost-manager' ); ?></label></th>
		<td><input class="large-text" id="gm-svc-subj-r" type="text" name="ghost_manager_settings[emails][service][subject_renewal]" value="<?php echo esc_attr( $svc['subject_renewal'] ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="gm-svc-subj-w"><?php esc_html_e( 'Subject (expiry warning)', 'ghost-manager' ); ?></label></th>
		<td><input class="large-text" id="gm-svc-subj-w" type="text" name="ghost_manager_settings[emails][service][subject_warning]" value="<?php echo esc_attr( $svc['subject_warning'] ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="gm-svc-intro-d"><?php esc_html_e( 'Intro (details)', 'ghost-manager' ); ?></label></th>
		<td><input class="large-text" id="gm-svc-intro-d" type="text" name="ghost_manager_settings[emails][service][intro_details]" value="<?php echo esc_attr( $svc['intro_details'] ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="gm-svc-intro-r"><?php esc_html_e( 'Intro (renewal)', 'ghost-manager' ); ?></label></th>
		<td><input class="large-text" id="gm-svc-intro-r" type="text" name="ghost_manager_settings[emails][service][intro_renewal]" value="<?php echo esc_attr( $svc['intro_renewal'] ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="gm-svc-intro-w"><?php esc_html_e( 'Intro (warning)', 'ghost-manager' ); ?></label></th>
		<td><input class="large-text" id="gm-svc-intro-w" type="text" name="ghost_manager_settings[emails][service][intro_warning]" value="<?php echo esc_attr( $svc['intro_warning'] ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="gm-svc-lu"><?php esc_html_e( 'Label: Username', 'ghost-manager' ); ?></label></th>
		<td><input class="regular-text" id="gm-svc-lu" type="text" name="ghost_manager_settings[emails][service][label_username]" value="<?php echo esc_attr( $svc['label_username'] ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="gm-svc-lp"><?php esc_html_e( 'Label: Password', 'ghost-manager' ); ?></label></th>
		<td><input class="regular-text" id="gm-svc-lp" type="text" name="ghost_manager_settings[emails][service][label_password]" value="<?php echo esc_attr( $svc['label_password'] ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="gm-svc-le"><?php esc_html_e( 'Label: Expiry', 'ghost-manager' ); ?></label></th>
		<td><input class="regular-text" id="gm-svc-le" type="text" name="ghost_manager_settings[emails][service][label_expiry]" value="<?php echo esc_attr( $svc['label_expiry'] ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="gm-svc-foot"><?php esc_html_e( 'Footer note', 'ghost-manager' ); ?></label></th>
		<td><input class="large-text" id="gm-svc-foot" type="text" name="ghost_manager_settings[emails][service][footer_note]" value="<?php echo esc_attr( $svc['footer_note'] ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="gm-svc-ns"><?php esc_html_e( 'Placeholder when value not set', 'ghost-manager' ); ?></label></th>
		<td><input class="regular-text" id="gm-svc-ns" type="text" name="ghost_manager_settings[emails][service][value_not_set]" value="<?php echo esc_attr( $svc['value_not_set'] ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="gm-svc-body"><?php esc_html_e( 'HTML body template', 'ghost-manager' ); ?></label></th>
		<td>
			<textarea class="large-text code" rows="14" id="gm-svc-body" name="ghost_manager_settings[emails][service][body_html]"><?php echo esc_textarea( $svc['body_html'] ); ?></textarea>
			<p class="description"><?php esc_html_e( 'Placeholders: {{logo_url}}, {{title}}, {{intro}}, {{username}}, {{password}}, {{expiry}}, {{label_username}}, {{label_password}}, {{label_expiry}}, {{footer_note}}', 'ghost-manager' ); ?></p>
		</td>
	</tr>
</table>

<h2 class="title"><?php esc_html_e( 'New user email', 'ghost-manager' ); ?></h2>
<p class="description"><?php esc_html_e( 'Important lines may include basic HTML (e.g. strong). {{brand}} is replaced in lines and in benefits intro.', 'ghost-manager' ); ?></p>
<table class="form-table" role="presentation">
	<tr>
		<th><label for="gm-nu-it"><?php esc_html_e( 'Important box title', 'ghost-manager' ); ?></label></th>
		<td><input class="regular-text" id="gm-nu-it" type="text" name="ghost_manager_settings[emails][new_user][important_title]" value="<?php echo esc_attr( $nu['important_title'] ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="gm-nu-il1"><?php esc_html_e( 'Important line 1', 'ghost-manager' ); ?></label></th>
		<td><input class="large-text" id="gm-nu-il1" type="text" name="ghost_manager_settings[emails][new_user][important_line_1]" value="<?php echo esc_attr( $nu['important_line_1'] ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="gm-nu-il2"><?php esc_html_e( 'Important line 2', 'ghost-manager' ); ?></label></th>
		<td><input class="large-text" id="gm-nu-il2" type="text" name="ghost_manager_settings[emails][new_user][important_line_2]" value="<?php echo esc_attr( $nu['important_line_2'] ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="gm-nu-il3"><?php esc_html_e( 'Important line 3', 'ghost-manager' ); ?></label></th>
		<td><input class="large-text" id="gm-nu-il3" type="text" name="ghost_manager_settings[emails][new_user][important_line_3]" value="<?php echo esc_attr( $nu['important_line_3'] ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="gm-nu-gr"><?php esc_html_e( 'Greeting', 'ghost-manager' ); ?></label></th>
		<td><input class="regular-text" id="gm-nu-gr" type="text" name="ghost_manager_settings[emails][new_user][greeting]" value="<?php echo esc_attr( $nu['greeting'] ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="gm-nu-in"><?php esc_html_e( 'Intro paragraph', 'ghost-manager' ); ?></label></th>
		<td><input class="large-text" id="gm-nu-in" type="text" name="ghost_manager_settings[emails][new_user][intro]" value="<?php echo esc_attr( $nu['intro'] ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="gm-nu-bt"><?php esc_html_e( 'Button label', 'ghost-manager' ); ?></label></th>
		<td><input class="regular-text" id="gm-nu-bt" type="text" name="ghost_manager_settings[emails][new_user][button_label]" value="<?php echo esc_attr( $nu['button_label'] ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="gm-nu-fb"><?php esc_html_e( 'Fallback text (under button)', 'ghost-manager' ); ?></label></th>
		<td><input class="large-text" id="gm-nu-fb" type="text" name="ghost_manager_settings[emails][new_user][fallback_text]" value="<?php echo esc_attr( $nu['fallback_text'] ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="gm-nu-bi"><?php esc_html_e( 'Benefits intro', 'ghost-manager' ); ?></label></th>
		<td><input class="large-text" id="gm-nu-bi" type="text" name="ghost_manager_settings[emails][new_user][benefits_intro]" value="<?php echo esc_attr( $nu['benefits_intro'] ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="gm-nu-b1"><?php esc_html_e( 'Benefit line 1', 'ghost-manager' ); ?></label></th>
		<td><input class="regular-text" id="gm-nu-b1" type="text" name="ghost_manager_settings[emails][new_user][benefit_1]" value="<?php echo esc_attr( $nu['benefit_1'] ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="gm-nu-b2"><?php esc_html_e( 'Benefit line 2', 'ghost-manager' ); ?></label></th>
		<td><input class="regular-text" id="gm-nu-b2" type="text" name="ghost_manager_settings[emails][new_user][benefit_2]" value="<?php echo esc_attr( $nu['benefit_2'] ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="gm-nu-b3"><?php esc_html_e( 'Benefit line 3', 'ghost-manager' ); ?></label></th>
		<td><input class="regular-text" id="gm-nu-b3" type="text" name="ghost_manager_settings[emails][new_user][benefit_3]" value="<?php echo esc_attr( $nu['benefit_3'] ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="gm-nu-body"><?php esc_html_e( 'Full HTML body (overrides structured fields above)', 'ghost-manager' ); ?></label></th>
		<td>
			<textarea class="large-text code" rows="16" id="gm-nu-body" name="ghost_manager_settings[emails][new_user][body_html]"><?php echo esc_textarea( $nu['body_html'] ); ?></textarea>
			<p class="description"><?php esc_html_e( 'Placeholders: {{logo_url}}, {{brand}}, {{reset_link_url}}, {{reset_link_display}}, {{important_box}}, {{greeting}}, {{intro}}, {{button_label}}, {{fallback_text}}, {{benefits_intro}}, {{benefits_list}}', 'ghost-manager' ); ?></p>
		</td>
	</tr>
</table>

<h2 class="title"><?php esc_html_e( 'Password reset email', 'ghost-manager' ); ?></h2>
<table class="form-table" role="presentation">
	<tr>
		<th><label for="gm-pr-hd"><?php esc_html_e( 'Heading', 'ghost-manager' ); ?></label></th>
		<td><input class="regular-text" id="gm-pr-hd" type="text" name="ghost_manager_settings[emails][password_reset][heading]" value="<?php echo esc_attr( $pr['heading'] ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="gm-pr-gr"><?php esc_html_e( 'Greeting', 'ghost-manager' ); ?></label></th>
		<td><input class="regular-text" id="gm-pr-gr" type="text" name="ghost_manager_settings[emails][password_reset][greeting]" value="<?php echo esc_attr( $pr['greeting'] ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="gm-pr-in"><?php esc_html_e( 'Intro (HTML allowed)', 'ghost-manager' ); ?></label></th>
		<td><textarea class="large-text" rows="3" id="gm-pr-in" name="ghost_manager_settings[emails][password_reset][intro]"><?php echo esc_textarea( $pr['intro'] ); ?></textarea></td>
	</tr>
	<tr>
		<th><label for="gm-pr-bt"><?php esc_html_e( 'Button label', 'ghost-manager' ); ?></label></th>
		<td><input class="regular-text" id="gm-pr-bt" type="text" name="ghost_manager_settings[emails][password_reset][button_label]" value="<?php echo esc_attr( $pr['button_label'] ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="gm-pr-ft"><?php esc_html_e( 'Footer text', 'ghost-manager' ); ?></label></th>
		<td><input class="large-text" id="gm-pr-ft" type="text" name="ghost_manager_settings[emails][password_reset][footer_text]" value="<?php echo esc_attr( $pr['footer_text'] ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="gm-pr-li"><?php esc_html_e( 'Link intro', 'ghost-manager' ); ?></label></th>
		<td><input class="large-text" id="gm-pr-li" type="text" name="ghost_manager_settings[emails][password_reset][link_intro]" value="<?php echo esc_attr( $pr['link_intro'] ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="gm-pr-body"><?php esc_html_e( 'Full HTML body (overrides structured fields above)', 'ghost-manager' ); ?></label></th>
		<td>
			<textarea class="large-text code" rows="14" id="gm-pr-body" name="ghost_manager_settings[emails][password_reset][body_html]"><?php echo esc_textarea( $pr['body_html'] ); ?></textarea>
			<p class="description"><?php esc_html_e( 'Placeholders: {{logo_url}}, {{brand}}, {{reset_link_url}}, {{reset_link_display}}, {{heading}}, {{greeting}}, {{intro}}, {{button_label}}, {{footer_text}}, {{link_intro}}', 'ghost-manager' ); ?></p>
		</td>
	</tr>
</table>
