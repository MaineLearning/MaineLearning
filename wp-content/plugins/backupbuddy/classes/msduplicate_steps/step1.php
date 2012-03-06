<?php
//STEP 1
$form_url = add_query_arg( array(
'action' => 'step2'
) , $form_url );
?>
<h3><?php esc_html_e( 'Step 1 - Site to duplicate into', 'it-l10n-backupbuddy' ); ?></h3>

<?php _e( 'Site duplication allows you to duplicate this site as a new site within this Multisite network with a new URL. This source site will not be modified.', 'it-l10n-backupbuddy' ) ?><br><br>
<?php



?>
<form method="post" action="<?php echo esc_url( $form_url ); ?>">
<?php wp_nonce_field( 'bbms-migration', 'pb_bbms_migrate' ); ?>
<table class="form-table">
	<tr class="form-field form-required">
		<th scope="row"><?php _e( 'New site address', 'it-l10n-backupbuddy' ) ?></th>
		<td>
		<?php if ( is_subdomain_install() ) { ?>
			<input name="blog[domain]" type="text" class="regular-text" title="<?php _e( 'Domain', 'it-l10n-backupbuddy' ) ?>" style="width: 25em;">.<?php echo preg_replace( '|^www\.|', '', $current_site->domain );?>
		<?php } else {
			echo 'http://' . $current_site->domain . '/'; ?><input name="blog[domain]" class="regular-text" type="text" title="<?php _e( 'Domain', 'it-l10n-backupbuddy' ) ?>" style="width: 25em;">
		<?php }
		echo '<p class="description">' . __( 'Only the characters a-z and 0-9 recommended.', 'it-l10n-backupbuddy' ) . '</p>';
		?>
		</td>
	</tr>
</table>
<?php submit_button( __('Duplicate Site'), 'primary', 'add-site' ); ?>
</form>