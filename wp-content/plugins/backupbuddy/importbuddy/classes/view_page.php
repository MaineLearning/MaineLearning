<html>
	<head>
		<title>BackupBuddy importbuddy.php by PluginBuddy.com</title>
		<meta name="robots" content="noindex">
	</head>
	<link rel="stylesheet" type="text/css" href="importbuddy/css/style.css" />
	<script src="importbuddy/js/jquery.js" type="text/javascript"></script>
	<script src="importbuddy/js/ui.core.js" type="text/javascript"></script>
	<script src="importbuddy/js/ui.widget.js" type="text/javascript"></script>
	<script src="importbuddy/js/ui.tabs.js" type="text/javascript"></script>
	<script src="importbuddy/js/tooltip.js" type="text/javascript"></script>
	<script src="importbuddy/js/importbuddy.js" type="text/javascript"></script>
	<body>
		
		<h1>BackupBuddy Restoration & Migration Tool</h1>
		<?php //<a href="http://ithemes.com/codex/page/BackupBuddy" style="text-decoration: none;">Need help? See the <b>Knowledge Base</b> for tutorials & more.</a><br> ?>
		
		<?php
		if ( $this->_options['skip_files'] != false ) {
			echo 'WARNING: Debug option to skip files is set to true. Files will not be extracted.<br>';
		}
		if ( $this->_options['wipe_database'] != false ) {
			echo 'WARNING: Debug option to wipe database is set to true. All existing data will be erased.<br>';
		}
		if ( $this->_options['skip_database_import'] != false ) {
			echo 'WARNING: Debug option to skip database import set to true. The database will not be imported.<br>';
		}
		if ( $this->_options['skip_database_migration'] != false ) {
			echo 'WARNING: Debug option to skip database import set to true. The database will not be migrated.<br>';
		}
		if ( $this->_options['skip_htaccess'] != false ) {
			echo 'WARNING: Debug option to skip migrating the htaccess file is set to true. The file will not be migrated if needed.<br>';
		}
		if ( $this->_options['force_compatibility_medium'] != false ) {
			echo 'WARNING: Debug option to force medium compatibility mode. This may result in slower, less reliable operation.<br>';
		}
		if ( $this->_options['force_compatibility_slow'] != false ) {
			echo 'WARNING: Debug option to force slow compatibility mode. This may result in slower, less reliable operation.<br>';
		}
		if ( $this->_options['force_high_security'] != false ) {
			echo 'WARNING: Debug option to force high security mode. You may be prompted for more information than normal.<br>';
		}
		if ( $this->_options['show_php_warnings'] != false ) {
			echo 'WARNING: Debug option to strictly report all errors & warnings from PHP is set to true. This may cause operation problems.<br>';
		}
		echo '<br>';
		?>
		
		<div style="display: none;" id="pb_importbuddy_blankalert"><?php $this->alert( '#TITLE#', '#MESSAGE#', '9021' ); ?></div>
		
		<div style="width: 700px; margin-left: auto; margin-right: auto;">
			<div style="max-width: 680px; margin:10px; padding: 20px; border:1px solid #ccc; -moz-border-radius: 10px; -webkit-border-radius: 10px; background: #F9F9F9; -moz-box-shadow: 10px 10px 15px -12px #356D8F; -webkit-box-shadow: 10px 10px 15px -12px #356D8F; padding-right: 20px;">
				<div class="wrap">
					<?php
					// Require password if set.
					if ( $this->has_access === false ) {
						echo '<h2>Authentication Required</h2>';
						echo 'Please enter your ImportBuddy password to continue. This was set from the BackupBuddy settings page.<br><br>';
						echo '<br><form action="?step=1" method="post">';
						echo '<div style="text-align: center;"><input type="password" name="password" value="" />&nbsp;';
						echo '<input type="submit" name="submit" value="Authenticate &raquo;" class="button" /></div>';
						echo '</form>';
					} else { // No password needed or successfully passed the correct password.
						echo '<h2>Step ' . $this->_step . ' of ' . $this->_total_steps . '</h2>';
						
						if ( file_exists( ABSPATH . 'importbuddy/classes/step_' . $this->_step . '_view.php' ) ) {
							$this->log( 'Initiating step #' . $this->_step . '.' );
							
							require_once( 'step_' . $this->_step . '_api.php' );
							$api_class = 'pluginbuddy_importbuddy_step_' . $this->_step;
							$api = new $api_class( $this );
							
							require_once( 'step_' . $this->_step . '_view.php' );
							$this->log( 'Completed step #' . $this->_step . '.' );
						} else {
							$this->log( 'Unable to initiate step #' . $this->_step . '. Halted.', 'error' );
							die( 'ERROR #546542. Invalid step "' . $this->_step . '".' );
						}
					}
					?>
				</div>
			</div>
		</div>
		
		<div style="clear: both;"><br><br>
			<?php
			if ( $this->_step != '6' ) { // after importbuddy deleted on step 6, this image cant load so dont put it in...
				echo '<a href="http://pluginbuddy.com"><img src="?ezimg=pluginbuddy.png" style="vertical-align: -2px;"></a> ';
			}
			echo '<a href="http://pluginbuddy.com">PluginBuddy.com</a><br>';
			echo '</div>';
			if ( $this->_version == '#VERSION#') {
				//echo '<i>Version Unknown</i>';
			} else {
				echo '<br><i>ImportBuddy v' . $this->_version . ' for BackupBuddy v' . $this->_bbversion . '</i>';
			}
			?>
</body>
</html>