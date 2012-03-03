<div class='wrap'>
<form method="post" action="<?php echo esc_url( $form_url ); ?>">
<table class="form-table">
	<tr class="form-field form-required">
		<th scope="row"><?php _e( 'Set a Password', 'it-l10n-backupbuddy' ) ?></th>
		<td>
		
		<input style='width: 250px' type='password' id='pass1' name='pass1' value='<?php echo esc_attr( $saved_password ); ?>' /><label for='pass1'><?php esc_html_e( 'Enter a Password', 'it-l10n-backupbuddy' ); ?></label><br />
		<input style='width: 250px' type='password' id='pass2' name='pass2' value='<?php echo esc_attr( $saved_password ); ?>' /><label for='pass2'><?php esc_html_e( 'Confirm Password', 'it-l10n-backupbuddy' ); ?></label><br />
		
		<div id="pass-strength-result"><?php _e('Strength indicator', 'it-l10n-backupbuddy'); ?></div>
		<p class="description indicator-hint"><?php _e('Hint: The password should be at least seven characters long. To make it stronger, use upper and lower case letters, numbers and symbols like ! " ? $ % ^ & ).', 'it-l10n-backupbuddy'); ?></p>
		</td>
	</tr>
</table>
<div id='password-generator'>
<?php
	require_once( $this->_pluginPath . '/lib/passwords/passwords.php' );
	$pb_passwords = new pluginbuddy_passwords();
	$pb_passwords->output_html();
?>
</div>
<?php wp_nonce_field( 'pb_save_repairbuddy_password', 'pb_nonce' ); ?>
<?php submit_button( __('Enable RepairBuddy'), 'primary', 'enable-repair-buddy' ); ?>
</form>
</div><!--/.wrap-->