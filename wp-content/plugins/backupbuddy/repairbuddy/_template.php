<html>
	<head>
		<title>BackupBuddy repairbuddy.php by PluginBuddy.com</title>
		<link rel="stylesheet" type="text/css" href="repairbuddy/css/style.css" />
		<script src="repairbuddy/js/jquery.js" type="text/javascript"></script>
		<script src="repairbuddy/js/ui.core.js" type="text/javascript"></script>
		<script src="repairbuddy/js/ui.widget.js" type="text/javascript"></script>
		<script src="repairbuddy/js/ui.tabs.js" type="text/javascript"></script>
		<script src="repairbuddy/js/tooltip.js" type="text/javascript"></script>
		<script src="repairbuddy/js/repairbuddy.js" type="text/javascript"></script>
		<?php
			$this->ajax_url();
			pb_do_action( 'print_styles' );
			pb_do_action( 'print_scripts' );
		?>

	</head>
		<body>
		
		<a href="<?php echo $this->page_link( '', '' ); ?>" title="Go to RepairBuddy Home Menu" style="text-decoration: none;"><h1>RepairBuddy by PluginBuddy.com</h1></a>
		
		<div style="display: none;" id="pb_repairbuddy_blankalert">d</div>
		
		<div style="width: 700px; margin-left: auto; margin-right: auto;">
			<div style="max-width: 680px; margin:10px; padding: 20px; border:1px solid #ccc; -moz-border-radius: 10px; -webkit-border-radius: 10px; background: #F9F9F9; -moz-box-shadow: 10px 10px 15px -12px #356D8F; -webkit-box-shadow: 10px 10px 15px -12px #356D8F; padding-right: 20px;">
				<?php
					$bread_crumb_html = array();
					$bread_crumb_html[] = sprintf( "You are here -> <a href='%s'>Home</a>", $this->page_link( '', '' ) );
					$page = $_GET[ 'page' ];
					$module = $_GET[ 'module' ];
					$bootstrap = $_GET[ 'bootstrap' ];
					if ( !empty( $page ) && !empty( $module ) ) {
						$bread_crumb_html[] = sprintf( "<a href='%s'>%s</a>", $this->page_link( $module, $page, $bootstrap ), $this->get_module_title( $module ) );
					}
					$bread_crumb_count = count( $bread_crumb_html );
					$bread_crumb_html = implode( ' | ', $bread_crumb_html );
					
					if ( $bread_crumb_count > 1 ) {
						?>
						<div class='breadcrumb'><p><?php echo $bread_crumb_html; ?></p></div>
						<?php
					}
				?>
				
				<div class="wrap">
					<?php
					// Require password if set.
					if ( $this->has_access === false ) {
						echo '<h2>Authentication Required</h2>';
						echo 'Please enter your RepairBuddy password to continue. This was set from the BackupBuddy settings page.<br><br>';
						echo '<br><form action="?" method="post">';
						echo '<div style="text-align: center;"><input type="password" name="password" value="" />&nbsp;';
						echo '<input type="submit" name="submit" value="Authenticate &raquo;" class="button" /></div>';
						echo '</form>';
					} else { // No password needed or successfully passed the correct password.
						if ( !isset( $_GET['page'] ) || ( $_GET['page'] == '' ) ) {
							require_once( ABSPATH . 'repairbuddy/_home.php' );
						} else {
							$action_to_execute = sprintf( 'pb_loadpage_%s_%s', $_GET[ 'module' ], $_GET[ 'page' ] );
							pb_do_action( $action_to_execute );
						}
					}
					?>
				</div>
			</div>
		</div>
		
		<div style="clear: both;"><br><br>
			<?php
			echo '<a href="http://pluginbuddy.com"><img src="repairbuddy/images/pluginbuddy.png" style="vertical-align: -2px;"></a> ';
			echo '<a href="http://pluginbuddy.com">PluginBuddy.com</a><br>';
			echo '</div>';
			if ( $this->_version == '#VERSION#') {
				//echo '<i>Version Unknown</i>';
			} else {
				echo '<br><i>RepairBuddy v' . $this->_version . ' provided with BackupBuddy v' . $this->_bbversion . '</i>';
			}
			?>
		</div>
		<?php pb_do_action( 'footer' ); ?>
</body>
</html>