<?php
if ( !isset( $parent_class ) ) {
	$parent_class = $this;
}
/*
 *	IMPORTANT NOTE:
 *
 *	This file is shared between multiple projects / purposes:
 *		+ BackupBuddy (this plugin) Server Info page.
 *		+ ImportBuddy.php (BackupBuddy importer) Server Information button dropdown display.
 *		+ ServerBuddy (plugin)
 *
 *	Use caution when updated to prevent breaking other projects.
 *
 */


// ini_get_bool() credit: nicolas dot grekas+php at gmail dot com
function ini_get_bool( $a ) {
	$b = ini_get($a);
	switch (strtolower($b)) {
		case 'on':
		case 'yes':
		case 'true':
			return 'assert.active' !== $a;
			
		case 'stdout':
		case 'stderr':
			return 'display_errors' === $a;
			
		default:
			return (bool) (int) $b;
	}
}
	
	$tests = array();
	
	
	// Skip these tests in importbuddy.
	if ( !defined( 'pluginbuddy_importbuddy' ) ) {
		// WORDPRESS VERSION
		global $wp_version;
		$parent_class_test = array(
						'title'			=>		'WordPress Version',
						'suggestion'	=>		'>= ' . $parent_class->_parent->_wp_minimum,
						'value'			=>		$wp_version,
						'tip'			=>		__('Version of WordPress currently running. It is important to keep your WordPress up to date for security & features.', 'it-l10n-backupbuddy'),
					);
		if ( version_compare( $wp_version, $parent_class->_parent->_wp_minimum, '<=' ) ) {
			$parent_class_test['status'] = __('FAIL', 'it-l10n-backupbuddy');
		} else {
			$parent_class_test['status'] = __('OK', 'it-l10n-backupbuddy');
		}
		array_push( $tests, $parent_class_test );
	
		// MYSQL VERSION
		global $wpdb;
		$parent_class_test = array(
						'title'			=>		'MySQL Version',
						'suggestion'	=>		'>= 5.0.15',
						'value'			=>		$wpdb->db_version(),
						'tip'			=>		__('Version of your database server (mysql) as reported to this script by WordPress.', 'it-l10n-backupbuddy'),
					);
		if ( version_compare( $wpdb->db_version(), '5.0.15', '<=' ) ) {
			$parent_class_test['status'] = __('FAIL', 'it-l10n-backupbuddy');
		} else {
			$parent_class_test['status'] = __('OK', 'it-l10n-backupbuddy');
		}
		array_push( $tests, $parent_class_test );
		
		
			// ADDHANDLER HTACCESS CHECK
			$parent_class_test = array(
							'title'			=>		'AddHandler in .htaccess',
							'suggestion'	=>		'host dependant',
							'tip'			=>		__('If detected then you may have difficulty migrating your site to some hosts without first removing the AddHandler line. Some hosts will malfunction with this line in the .htaccess file.', 'it-l10n-backupbuddy'),
						);
			if ( file_exists( ABSPATH . '.htaccess' ) ) {
				$addhandler_note = '';
				$htaccess_lines = file( ABSPATH . '.htaccess' );
				foreach ( $htaccess_lines as $htaccess_line ) {
					if ( preg_match( '/^(\s*)AddHandler(.*)/i', $htaccess_line, $matches ) > 0 ) {
						$addhandler_note = $parent_class->tip( htmlentities( $matches[0] ), __( 'AddHandler Value', 'it-l10n-backupbuddy' ), false );
					}
				}
				unset( $htaccess_lines );
				
				if ( $addhandler_note == '' ) {
					$parent_class_test['status'] = __('OK', 'it-l10n-backupbuddy');
					$parent_class_test['value'] = __('n/a', 'it-l10n-backupbuddy');
				} else {
					$parent_class_test['status'] = __('WARNING', 'it-l10n-backupbuddy');
					$parent_class_test['value'] = __('exists', 'it-l10n-backupbuddy') . $addhandler_note;
				}
				unset( $htaccess_contents );
			} else {
				$parent_class_test['status'] = __('OK', 'it-l10n-backupbuddy');
				$parent_class_test['value'] = __('n/a', 'it-l10n-backupbuddy');
			}
			array_push( $tests, $parent_class_test );
		
		
		// ZIP METHODS
		if ( $parent_class->_var == 'pluginbuddy_backupbuddy' ) {
			if ( !file_exists( $parent_class->_options['backup_directory'] ) ) {
				if ( $parent_class->_parent->mkdir_recursive( $parent_class->_options['backup_directory'] ) === false ) {
					$parent_class->alert( sprintf( __('Unable to create backup storage directory (%s)', 'it-l10n-backupbuddy') , $parent_class->_options['backup_directory'] ), true, '9002' );
					return false;
				}
			}
		}
		
		// Set up ZipBuddy when within BackupBuddy
		require_once( $parent_class->_pluginPath . '/lib/zipbuddy/zipbuddy.php' );
		$parent_class->_zipbuddy = new pluginbuddy_zipbuddy( $parent_class->_options['backup_directory'] );
	} else {
		// Set up ZipBuddy when within importbuddy
		if ( file_exists( ABSPATH . '/importbuddy/lib/zipbuddy/zipbuddy.php' ) ) {
			require_once( ABSPATH . '/importbuddy/lib/zipbuddy/zipbuddy.php' );
		} elseif ( file_exists( ABSPATH . '/repairbuddy/lib/zipbuddy/zipbuddy.php' ) ) {
			require_once( ABSPATH . '/repairbuddy/lib/zipbuddy/zipbuddy.php' );
		} else {
			die( 'Error #383989479379497. Unable to load zipbuddy library.' );
		}
		$parent_class->_zipbuddy = new pluginbuddy_zipbuddy( ABSPATH, '', 'unzip' );
	}
	
	
	// PHP VERSION
	if ( !defined( 'pluginbuddy_importbuddy' ) ) {
		$php_minimum = $parent_class->_parent->_php_minimum;
	} else { // importbuddy value.
		$php_minimum = $parent_class->_php_minimum;
	}
	$parent_class_test = array(
					'title'			=>		'PHP Version',
					'suggestion'	=>		'>= ' . $php_minimum,
					'value'			=>		phpversion(),
					'tip'			=>		__('Version of PHP currently running on this site.', 'it-l10n-backupbuddy'),
				);
	if ( version_compare( PHP_VERSION, $php_minimum, '<=' ) ) {
		$parent_class_test['status'] = __('FAIL', 'it-l10n-backupbuddy');
	} else {
		$parent_class_test['status'] = __('OK', 'it-l10n-backupbuddy');
	}
	array_push( $tests, $parent_class_test );
	
	
	// PHP max_execution_time
	$parent_class_test = array(
					'title'			=>		'PHP max_execution_time',
					'suggestion'	=>		'>= ' . '30 (seconds)',
					'value'			=>		ini_get( 'max_execution_time' ),
					'tip'			=>		__('Maximum amount of time that PHP allows scripts to run. After this limit is reached the script is killed. The more time available the better. 30 seconds is most common though 60 seconds is ideal.', 'it-l10n-backupbuddy'),
				);
	if ( str_ireplace( 's', '', ini_get( 'max_execution_time' ) ) < 30 ) {
		$parent_class_test['status'] = __('WARNING', 'it-l10n-backupbuddy');
	} else {
		$parent_class_test['status'] = __('OK', 'it-l10n-backupbuddy');
	}
	array_push( $tests, $parent_class_test );
	
	
	$parent_class_test = array(
					'title'			=>		'Zip Methods',
					'suggestion'	=>		'exec (best) > ziparchive > pclzip (worst)',
					'value'			=>		implode( ', ', $parent_class->_zipbuddy->_zip_methods ),
					'tip'			=>		__('Methods your server supports for creating ZIP files. These were tested & verified to operate.', 'it-l10n-backupbuddy'),
				);
	if ( in_array( 'exec', $parent_class->_zipbuddy->_zip_methods ) ) {
		$parent_class_test['status'] = __('OK', 'it-l10n-backupbuddy');
	} else {
		$parent_class_test['status'] = __('WARNING', 'it-l10n-backupbuddy');
	}
	array_push( $tests, $parent_class_test );
	
	
	// REGISTER GLOBALS
	if ( ini_get_bool( 'register_globals' ) === true ) {
		$parent_class_val = 'enabled';
	} else {
		$parent_class_val = 'disabled';
	}
	$parent_class_test = array(
					'title'			=>		'PHP Register Globals',
					'suggestion'	=>		'disabled',
					'value'			=>		$parent_class_val,
					'tip'			=>		__('Automatically registers user input as variables. HIGHLY discouraged. Removed from PHP in PHP 6 for security.', 'it-l10n-backupbuddy'),
				);
	if ( $parent_class_val != 'disabled' ) {
		$parent_class_test['status'] = __('FAIL', 'it-l10n-backupbuddy');
	} else {
		$parent_class_test['status'] = __('OK', 'it-l10n-backupbuddy');
	}
	array_push( $tests, $parent_class_test );
	
	// MAGIC QUOTES GPC
	if ( ini_get_bool( 'magic_quotes_gpc' ) === true ) {
		$parent_class_val = 'enabled';
	} else {
		$parent_class_val = 'disabled';
	}
	$parent_class_test = array(
					'title'			=>		'PHP Magic Quotes GPC',
					'suggestion'	=>		'disabled',
					'value'			=>		$parent_class_val,
					'tip'			=>		__('Automatically escapes user inputted data. Not needed when using properly coded software.', 'it-l10n-backupbuddy'),
				);
	if ( $parent_class_val != 'disabled' ) {
		$parent_class_test['status'] = __('WARNING', 'it-l10n-backupbuddy');
	} else {
		$parent_class_test['status'] = __('OK', 'it-l10n-backupbuddy');
	}
	array_push( $tests, $parent_class_test );
	
	// MAGIC QUOTES RUNTIME
	if ( ini_get_bool( 'magic_quotes_runtime' ) === true ) {
		$parent_class_val = 'enabled';
	} else {
		$parent_class_val = 'disabled';
	}
	$parent_class_test = array(
					'title'			=>		'PHP Magic Quotes Runtime',
					'suggestion'	=>		'disabled',
					'value'			=>		$parent_class_val,
					'tip'			=>		__('Automatically escapes user inputted data. Not needed when using properly coded software.', 'it-l10n-backupbuddy'),
				);
	if ( $parent_class_val != 'disabled' ) {
		$parent_class_test['status'] = __('WARNING', 'it-l10n-backupbuddy');
	} else {
		$parent_class_test['status'] = __('OK', 'it-l10n-backupbuddy');
	}
	array_push( $tests, $parent_class_test );
	
	// SAFE MODE
	if ( ini_get_bool( 'safe_mode' ) === true ) {
		$parent_class_val = 'enabled';
	} else {
		$parent_class_val = 'disabled';
	}
	$parent_class_test = array(
					'title'			=>		'PHP Safe Mode',
					'suggestion'	=>		'disabled',
					'value'			=>		$parent_class_val,
					'tip'			=>		__('This mode is HIGHLY discouraged and is a sign of a poorly configured host.', 'it-l10n-backupbuddy'),
				);
	if ( $parent_class_val != 'disabled' ) {
		$parent_class_test['status'] = __('WARNING', 'it-l10n-backupbuddy');
	} else {
		$parent_class_test['status'] = __('OK', 'it-l10n-backupbuddy');
	}
	array_push( $tests, $parent_class_test );
	
	// OS
	$parent_class_test = array(
					'title'			=>		'Operating System',
					'suggestion'	=>		'Linux',
					'value'			=>		PHP_OS,
					'tip'			=>		__('The server operating system running this site. Linux based systems are encouraged. Windows users may need to perform additional steps to get plugins to perform properly.', 'it-l10n-backupbuddy'),
				);
	if ( PHP_OS == 'WINNT' ) {
		$parent_class_test['status'] = __('WARNING', 'it-l10n-backupbuddy');
	} else {
		$parent_class_test['status'] = __('OK', 'it-l10n-backupbuddy');
	}
	array_push( $tests, $parent_class_test );
	
	// MEMORY LIMIT
	if ( !ini_get( 'memory_limit' ) ) {
		$parent_class_val = 'unknown';
	} else {
		$parent_class_val = ini_get( 'memory_limit' );
	}
	$parent_class_test = array(
					'title'			=>		'PHP Memory Limit',
					'suggestion'	=>		'>= 128M',
					'value'			=>		$parent_class_val,
					'tip'			=>		__('The amount of memory this site is allowed to consume.', 'it-l10n-backupbuddy'),
				);
	if ( preg_match( '/(\d+)(\w*)/', $parent_class_val, $matches ) ) {
		$parent_class_val = $matches[1];
		$unit = $matches[2];
		// Up memory limit if currently lower than 256M.
		if ( 'g' !== strtolower( $unit ) ) {
			if ( ( $parent_class_val < 128 ) || ( 'm' !== strtolower( $unit ) ) ) {
				$parent_class_test['status'] = __('WARNING', 'it-l10n-backupbuddy');
			} else {
				$parent_class_test['status'] = __('OK', 'it-l10n-backupbuddy');
			}
		}
	} else {
		$parent_class_test['status'] = __('WARNING', 'it-l10n-backupbuddy');
	}
	
	array_push( $tests, $parent_class_test );
	
?>


<table class="widefat">
	<thead>
		<tr class="thead">
			<th style="width: 15px;">&nbsp;</th>
			<?php
				echo '<th>', __('Parameter', 'it-l10n-backupbuddy'), '</th>',
					 '<th>', __('Suggestion', 'it-l10n-backupbuddy'), '</th>',
					 '<th>', __('Value', 'it-l10n-backupbuddy'), '</th>',
					 '<th>', __('Result', 'it-l10n-backupbuddy'), '</th>',
					 '<th style="width: 60px;">', __('Status', 'it-l10n-backupbuddy'), '</th>';
			?>
		</tr>
	</thead>
	<tfoot>
		<tr class="thead">
			<th style="width: 15px;">&nbsp;</th>
			<?php
				echo '<th>', __('Parameter', 'it-l10n-backupbuddy'), '</th>',
					 '<th>', __('Suggestion', 'it-l10n-backupbuddy'), '</th>',
					 '<th>', __('Value', 'it-l10n-backupbuddy'), '</th>',
					 '<th>', __('Result', 'it-l10n-backupbuddy'), '</th>',
					 '<th style="width: 15px;">', __('Status', 'it-l10n-backupbuddy'), '</th>';
			?>
		</tr>
	</tfoot>
	<tbody>
		<?php
		foreach( $tests as $parent_class_test ) {
			echo '<tr class="entry-row alternate">';
			echo '	<td>' . $parent_class->tip( $parent_class_test['tip'], '', false ) . '</td>';
			echo '	<td>' . $parent_class_test['title'] . '</td>';
			echo '	<td>' . $parent_class_test['suggestion'] . '</td>';
			echo '	<td>' . $parent_class_test['value'] . '</td>';
			echo '	<td>' . $parent_class_test['status'] . '</td>';
			echo '	<td>';
			if ( $parent_class_test['status'] == __('OK', 'it-l10n-backupbuddy') ) {
				echo '<div style="background-color: #22EE5B; border: 1px solid #E2E2E2;">&nbsp;&nbsp;&nbsp;</div>';
			} elseif ( $parent_class_test['status'] == __('FAIL', 'it-l10n-backupbuddy') ) {
				echo '<div style="background-color: #CF3333; border: 1px solid #E2E2E2;">&nbsp;&nbsp;&nbsp;</div>';
			} elseif ( $parent_class_test['status'] == __('WARNING', 'it-l10n-backupbuddy') ) {
				echo '<div style="background-color: #FEFF7F; border: 1px solid #E2E2E2;">&nbsp;&nbsp;&nbsp;</div>';
			}
			echo '	</td>';
			echo '</tr>';
		}
		?>
	</tbody>
</table>
<?php
if ( isset( $_GET['phpinfo'] ) && $_GET['phpinfo'] == 'true' ) {
	echo '<br><h3>phpinfo() ', __('Response', 'it-l10n-backupbuddy'), ':</h3>';
	
	echo '<div style="width: 100%; height: 600px; padding-top: 10px; padding-bottom: 10px; overflow: scroll; ">';
	ob_start();
	
	phpinfo();
	
	$info = ob_get_contents();
	ob_end_clean();
	$info = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $info);
	echo $info;
	unset( $info );
	
	echo '</div>';
} else {
	echo '<br>';
	echo '<center>';
	if ( !defined( 'pluginbuddy_importbuddy' ) ) {
		echo '<a href="' . $parent_class->_selfLink . '-tools&phpinfo=true" class="button secondary-button" style="margin-top: 3px;">'. __('Display Extended PHP Settings via phpinfo()', 'it-l10n-backupbuddy') . '</a>';
	} else {
		if ( file_exists( ABSPATH . '/repairbuddy' ) ) {
			echo '<a href="' . $parent_class->page_link( 'server_info', 'phpinfo' ) . '" class="button-secondary" style="margin-top: 3px; text-decoration: none;">'. __('Display Extended PHP Settings via phpinfo()', 'it-l10n-backupbuddy') . '</a>';
		} else {
			echo '<a href="?step=0&action=phpinfo&v=xv' . md5( $parent_class->_defaults['import_password'] . 'importbuddy' ) . '" class="button-secondary" style="margin-top: 3px; text-decoration: none;">'. __('Display Extended PHP Settings via phpinfo()', 'it-l10n-backupbuddy') . '</a>';
		}
	}
	echo '</center>';
	
	/*
	echo '<pre>';
	print_r( ini_get_all() );
	echo '</pre>';
	*/
}
?>