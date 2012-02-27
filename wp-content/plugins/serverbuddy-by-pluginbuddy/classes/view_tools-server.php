<?php
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
	
	
	// PHP VERSION
	$this_test = array(
					'title'			=>		'PHP Version',
					'suggestion'	=>		'>= ' . $this->_parent->_php_minimum,
					'value'			=>		phpversion(),
					'tip'			=>		'Version of PHP currently running on this site.',
				);
	if ( version_compare( PHP_VERSION, $this->_parent->_php_minimum, '<=' ) ) {
		$this_test['status'] = 'FAIL';
	} else {
		$this_test['status'] = 'OK';
	}
	array_push( $tests, $this_test );
	
	
	// PHP max_execution_time
	$this_test = array(
					'title'			=>		'PHP max_execution_time',
					'suggestion'	=>		'>= ' . '30 (seconds)',
					'value'			=>		ini_get( 'max_execution_time' ),
					'tip'			=>		'Maximum amount of time that PHP allows scripts to run. After this limit is reached the script is killed. The more time available the better. 30 seconds is most common though 60 seconds is ideal.',
				);
	if ( str_ireplace( 's', '', ini_get( 'max_execution_time' ) ) < 30 ) {
		$this_test['status'] = 'WARNING';
	} else {
		$this_test['status'] = 'OK';
	}
	array_push( $tests, $this_test );
	
	
	// WORDPRESS VERSION
	global $wp_version;
	$this_test = array(
					'title'			=>		'WordPress Version',
					'suggestion'	=>		'>= ' . $this->_parent->_wp_minimum,
					'value'			=>		$wp_version,
					'tip'			=>		'Version of WordPress currently running. It is important to keep your WordPress up to date for security & features.',
				);
	if ( version_compare( $wp_version, $this->_parent->_wp_minimum, '<=' ) ) {
		$this_test['status'] = 'FAIL';
	} else {
		$this_test['status'] = 'OK';
	}
	array_push( $tests, $this_test );
	
	
	// MYSQL VERSION
	global $wpdb;
	$this_test = array(
					'title'			=>		'MySQL Version',
					'suggestion'	=>		'>= 5.0.15',
					'value'			=>		$wpdb->db_version(),
					'tip'			=>		'Version of your database server (mysql) as reported to this script by WordPress.',
				);
	if ( version_compare( $wpdb->db_version(), '5.0.15', '<=' ) ) {
		$this_test['status'] = 'FAIL';
	} else {
		$this_test['status'] = 'OK';
	}
	array_push( $tests, $this_test );
	
	
	// ZIP METHODS
	if ( $this->_var == 'pluginbuddy_backupbuddy' ) {
		if ( !file_exists( $this->_options['backup_directory'] ) ) {
			if ( $this->_parent->mkdir_recursive( $this->_options['backup_directory'] ) === false ) {
				$this->error( 'Unable to create backup storage directory (' . $this->_options['backup_directory'] . ')', '9002' );
				return false;
			}
		}
	}
	
	require_once( $this->_pluginPath . '/lib/zipbuddy/zipbuddy.php' );
	$this->_zipbuddy = new pluginbuddy_zipbuddy( $this->_options['backup_directory'] );
	$this_test = array(
					'title'			=>		'Zip Methods',
					'suggestion'	=>		'exec (best) > ziparchive > pclzip (worst)',
					'value'			=>		implode( ', ', $this->_zipbuddy->_zip_methods ),
					'tip'			=>		'Methods your server supports for creating ZIP files. These were tested & verified to operate.',
				);
	if ( in_array( 'exec', $this->_zipbuddy->_zip_methods ) ) {
		$this_test['status'] = 'OK';
	} else {
		$this_test['status'] = 'WARNING';
	}
	array_push( $tests, $this_test );
	
	
	// ADDHANDLER HTACCESS CHECK
	$this_test = array(
					'title'			=>		'AddHandler in .htaccess',
					'suggestion'	=>		'host dependant',
					'tip'			=>		'If detected then you may have difficulty migrating your site to some hosts without first removing the AddHandler line. Some hosts will malfunction with this line in the .htaccess file.',
				);
	if ( file_exists( ABSPATH . '.htaccess' ) ) {
		$htaccess_contents = file_get_contents( ABSPATH . '.htaccess' );
		if ( !stristr( $htaccess_contents, 'addhandler' ) ) {
			$this_test['status'] = 'OK';
			$this_test['value'] = 'n/a';
		} else {
			$this_test['status'] = 'WARNING';
			$this_test['value'] = 'exists';
		}
		unset( $htaccess_contents );
	} else {
		$this_test['status'] = 'OK';
		$this_test['value'] = 'n/a';
	}
	array_push( $tests, $this_test );

	// REGISTER GLOBALS
	if ( ini_get_bool( 'register_globals' ) === true ) {
		$this_val = 'enabled';
	} else {
		$this_val = 'disabled';
	}
	$this_test = array(
					'title'			=>		'PHP Register Globals',
					'suggestion'	=>		'disabled',
					'value'			=>		$this_val,
					'tip'			=>		'Automatically registers user input as variables. HIGHLY discouraged. Removed from PHP in PHP 6 for security.',
				);
	if ( $this_val != 'disabled' ) {
		$this_test['status'] = 'FAIL';
	} else {
		$this_test['status'] = 'OK';
	}
	array_push( $tests, $this_test );
	
	// MAGIC QUOTES GPC
	if ( ini_get_bool( 'magic_quotes_gpc' ) === true ) {
		$this_val = 'enabled';
	} else {
		$this_val = 'disabled';
	}
	$this_test = array(
					'title'			=>		'PHP Magic Quotes GPC',
					'suggestion'	=>		'disabled',
					'value'			=>		$this_val,
					'tip'			=>		'Automatically escapes user inputted data. Not needed when using properly coded software.',
				);
	if ( $this_val != 'disabled' ) {
		$this_test['status'] = 'WARNING';
	} else {
		$this_test['status'] = 'OK';
	}
	array_push( $tests, $this_test );
	
	// MAGIC QUOTES RUNTIME
	if ( ini_get_bool( 'magic_quotes_runtime' ) === true ) {
		$this_val = 'enabled';
	} else {
		$this_val = 'disabled';
	}
	$this_test = array(
					'title'			=>		'PHP Magic Quotes Runtime',
					'suggestion'	=>		'disabled',
					'value'			=>		$this_val,
					'tip'			=>		'Automatically escapes user inputted data. Not needed when using properly coded software.',
				);
	if ( $this_val != 'disabled' ) {
		$this_test['status'] = 'WARNING';
	} else {
		$this_test['status'] = 'OK';
	}
	array_push( $tests, $this_test );
	
	// SAFE MODE
	if ( ini_get_bool( 'safe_mode' ) === true ) {
		$this_val = 'enabled';
	} else {
		$this_val = 'disabled';
	}
	$this_test = array(
					'title'			=>		'PHP Safe Mode',
					'suggestion'	=>		'disabled',
					'value'			=>		$this_val,
					'tip'			=>		'This mode is HIGHLY discouraged and is a sign of a poorly configured host.',
				);
	if ( $this_val != 'disabled' ) {
		$this_test['status'] = 'WARNING';
	} else {
		$this_test['status'] = 'OK';
	}
	array_push( $tests, $this_test );
	
	// OS
	$this_test = array(
					'title'			=>		'Operating System',
					'suggestion'	=>		'Linux',
					'value'			=>		PHP_OS,
					'tip'			=>		'The server operating system running this site. Linux based systems are encouraged. Windows users may need to perform additional steps to get plugins to perform properly.',
				);
	if ( PHP_OS == 'WINNT' ) {
		$this_test['status'] = 'WARNING';
	} else {
		$this_test['status'] = 'OK';
	}
	array_push( $tests, $this_test );
	
	// MEMORY LIMIT
	if ( !ini_get( 'memory_limit' ) ) {
		$this_val = 'unknown';
	} else {
		$this_val = ini_get( 'memory_limit' );
	}
	$this_test = array(
					'title'			=>		'PHP Memory Limit',
					'suggestion'	=>		'>= 128M',
					'value'			=>		$this_val,
					'tip'			=>		'The amount of memory this site is allowed to consume.',
				);
	if ( preg_match( '/(\d+)(\w*)/', $this_val, $matches ) ) {
		$this_val = $matches[1];
		$unit = $matches[2];
		// Up memory limit if currently lower than 256M.
		if ( 'g' !== strtolower( $unit ) ) {
			if ( ( $this_val < 128 ) || ( 'm' !== strtolower( $unit ) ) ) {
				$this_test['status'] = 'WARNING';
			} else {
				$this_test['status'] = 'OK';
			}
		}
	} else {
		$this_test['status'] = 'WARNING';
	}
	
	array_push( $tests, $this_test );
	
?>


<table class="widefat">
	<thead>
		<tr class="thead">
			<th style="width: 15px;">&nbsp;</th>
			<th>Parameter</th>
			<th>Suggestion</th>
			<th>Value</th>
			<th>Result</th>
			<th style="width: 60px;">Status</th>
		</tr>
	</thead>
	<tfoot>
		<tr class="thead">
			<th style="width: 15px;">&nbsp;</th>
			<th>Parameter</th>
			<th>Suggestion</th>
			<th>Value</th>
			<th>Result</th>
			<th style="width: 60px;">Status</th>
		</tr>
	</tfoot>
	<tbody>
		<?php
		foreach( $tests as $this_test ) {
			echo '<tr class="entry-row alternate">';
			echo '	<td>' . $this->tip( $this_test['tip'], '', false ) . '</td>';
			echo '	<td>' . $this_test['title'] . '</td>';
			echo '	<td>' . $this_test['suggestion'] . '</td>';
			echo '	<td>' . $this_test['value'] . '</td>';
			echo '	<td>' . $this_test['status'] . '</td>';
			echo '	<td>';
			if ( $this_test['status'] == 'OK' ) {
				echo '<div style="background-color: #22EE5B; border: 1px solid #E2E2E2;">&nbsp;&nbsp;&nbsp;</div>';
			} elseif ( $this_test['status'] == 'FAIL') {
				echo '<div style="background-color: #CF3333; border: 1px solid #E2E2E2;">&nbsp;&nbsp;&nbsp;</div>';
			} elseif ( $this_test['status'] == 'WARNING') {
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
	echo '<br><h3>phpinfo() Response:</h3>';
	
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
	echo '<a href="' . $this->_selfLink . '-tools&phpinfo=true" class="button secondary-button" style="margin-top: 3px;">Display Extended PHP Settings via phpinfo()</a>';
	echo '</center>';
	
	/*
	echo '<pre>';
	print_r( ini_get_all() );
	echo '</pre>';
	*/
}
?>