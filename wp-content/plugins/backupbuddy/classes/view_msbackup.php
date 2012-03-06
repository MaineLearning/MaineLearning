<?php
// Used for drag & drop / collapsing boxes.
wp_enqueue_style('dashboard');
wp_print_styles('dashboard');
wp_enqueue_script('dashboard');
wp_print_scripts('dashboard');

wp_enqueue_script( 'thickbox' );
wp_print_scripts( 'thickbox' );
wp_print_styles( 'thickbox' );
// Handles resizing thickbox.
if ( !wp_script_is( 'media-upload' ) ) {
	wp_enqueue_script( 'media-upload' );
	wp_print_scripts( 'media-upload' );
}
wp_enqueue_script( 'backupbuddy-ms-export', $this->_parent->_pluginURL . '/js/ms.js', array( 'jquery' ) );
wp_print_scripts( 'backupbuddy-ms-export' );



?>
<style type="text/css">
#status-bar-container {
	display: none;
	width: 500px;
	height: 12px;
	margin-top: 20px;
	background-color: white;
	border: 1px solid #000;
	-moz-border-radius: 5px;
	border-radius: 5px;
}
#status-bar {
	height: 12px;
	width: 0px;
	float: left;
	overflow: visible;
	background-color: #5b962f;
}
.status {
	display: none;
	margin: 5px 0 15px;
	background-color: #FFFFE0;
	border: 1px solid #E6DB55;
	padding: 0 0.6em;
	-moz-border-radius: 3px;
	border-radius: 3px;
}
.status p {
	margin: 0.5em 0;
	padding: 2px;
	
}
</style>
<div class='wrap'>
<h2>Site Export for Single Site Only (BETA)</h2>
<p>For BackupBuddy Multisite documentation, please visit the <a href='http://ithemes.com/codex/page/BackupBuddy_Multisite'>BackupBuddy Multisite Codex</a>.</p>
<?php




?>
<?php
//Let's do some sanity checks to make sure a valid nonce is used or a $_POST variable is set - If not, we're on the first step and the user must select plugins
global $current_blog;
$must_select_plugins = true;
$zip_id = 0;
if ( isset( $_POST[ 'action' ] ) ) {
	//Try to verify the nonce
	if ( wp_verify_nonce( $_REQUEST[ '_bb_nonce' ], 'bb-plugins-export' ) ) {
		$must_select_plugins = false;
		$selected_items = isset( $_POST[ 'items' ] ) ? $_POST[ 'items' ] : array();
		//Create unique zip_id
		global $current_blog;
		$blog_id = absint( $current_blog->blog_id );
		$zip_id = uniqid( 'bbtmp' . $blog_id . 'export', false );
		
		$active_plugins = get_option( 'active_plugins', array() );
		if ( count( $active_plugins ) > 0 ) {
			$selected_items[ 'site' ] = $active_plugins;
		}
		//Store the transient
		if ( count( $selected_items ) > 0 ) {
			set_transient( $zip_id, $selected_items, 60*60*6 );
		}
		//Create a unique ID and store a transient
	}
} //end isset action

if ( $must_select_plugins ) :
?>
<p><?php esc_html_e( 'Below is a list of Must-Use, Drop-ins, and Network-Activated plugins.  Please select plugins you would like copied over with the site export.', 'it-l10n-backupbuddy' ); ?></p>
<?php
$form_url = $this->_selfLink . '-msbackup';

?>
<form method="post" action="<?php echo esc_url( $form_url ); ?>">

<div id='plugin-list'>
<?php
?>
	<table class="widefat">
		<thead>
			<tr class="thead">
				<th scope="col" class="check-column"><input type="checkbox" class="check-all-entries" /></th>
				<th><?php esc_html_e( 'Plugin', 'it-l10n-backupbuddy' ); ?></th>
				<th><?php esc_html_e( 'Description', 'it-l10n-backupbuddy' ); ?></th>
				<th><?php esc_html_e( 'Plugin Type', 'it-l10n-backupbuddy' ); ?></th>
			</tr>
		</thead>
		<tfoot>
			<tr class="thead">
				<th scope="col" class="check-column"><input type="checkbox" class="check-all-entries" /></th>
				<th><?php esc_html_e( 'Plugin', 'it-l10n-backupbuddy' ); ?></th>
				<th><?php esc_html_e( 'Description', 'it-l10n-backupbuddy' ); ?></th>
				<th><?php esc_html_e( 'Plugin Type', 'it-l10n-backupbuddy' ); ?></th>
			</tr>
		</tfoot>
		<tbody id="pb_reorder">
			<?php
			//Get MU Plugins
			foreach ( get_mu_plugins() as $file => $meta ) {
				$description = !empty( $meta[ 'Description' ] ) ? $meta[ 'Description' ] : '';
				$name = !empty( $meta[ 'Name' ] ) ? $meta[ 'Name' ] : $file;
				?>
			<tr>
				<th scope="row" class="check-column"><input type="checkbox" name="items[mu][]" class="entries" value="<?php echo esc_attr( $file ); ?>" /></th>
				<td><?php echo esc_html( $name ); ?></td>
				<td><?php echo esc_html( $description ); ?></td>
				<td><?php esc_html_e( 'Must Use', 'it-l10n-backupbuddy' ); ?></td>
			</tr>	
				<?php
			} //end foreach
			
			//Get Drop INs
			foreach ( get_dropins() as $file => $meta ) {
				$description = !empty( $meta[ 'Description' ] ) ? $meta[ 'Description' ] : '';
				$name = !empty( $meta[ 'Name' ] ) ? $meta[ 'Name' ] : $file;
				?>
			<tr>
				<th scope="row" class="check-column"><input type="checkbox" name="items[dropins][]" class="entries" value="<?php echo esc_attr( $file ); ?>" /></th>
				<td><?php echo esc_html( $name ); ?></td>
				<td><?php echo esc_html( $description ); ?></td>
				<td><?php esc_html_e( 'Drop In', 'it-l10n-backupbuddy' ); ?></td>
			</tr>	
				<?php
			} //end foreach drop ins
			
			//Get Network Activated
			foreach ( get_plugins() as $file => $meta ) {
				if ( !is_plugin_active_for_network( $file ) ) continue;
				$description = !empty( $meta[ 'Description' ] ) ? $meta[ 'Description' ] : '';
				$name = !empty( $meta[ 'Name' ] ) ? $meta[ 'Name' ] : $file;
				?>
			<tr>
				<th scope="row" class="check-column"><input type="checkbox" name="items[network][]" class="entries" value="<?php echo esc_attr( $file ); ?>" /></th>
				<td><?php echo esc_html( $name ); ?></td>
				<td><?php echo esc_html( $description ); ?></td>
				<td><?php esc_html_e( 'Network Activated', 'it-l10n-backupbuddy' ); ?></td>
			</tr>	
				<?php
			} //end foreach drop ins
			?>
		</tbody>
	</table>
</div><!-- #plugin-list-->
<input type="hidden" name="action" value="export" />
<?php wp_nonce_field( 'bb-plugins-export', '_bb_nonce' ); ?>
<?php submit_button( __('Next Step', 'it-l10n-backupbuddy' ), 'primary', 'bb-plugins' ); ?>
</form>
<?php
else:
?>
<form id='ajax-submit'>
<p>We'll now create a standard WordPress installation.</p>
<p>Click <strong>Export Site</strong> to begin.</p>
	<input type="hidden" name="zip_id" id="zip_id" value="<?php echo esc_attr( $zip_id ); ?>" />
	<?php wp_nonce_field( 'export-site', '_ajax_nonce' ); ?>
	<?php submit_button( __('Export Site'), 'primary', 'add-site' ); ?>
	<div id='status-bar-container'><div id='status-bar'></div></div>
	<div class='status' id='status-message'><p><strong><?php esc_html_e( 'Downloading the latest WordPress Version', 'it-l10n-backupbuddy' ); ?></strong></p></div>
</form><!-- #ajax-submit-->
<?php endif; ?>
</div><!--/.wrap-->