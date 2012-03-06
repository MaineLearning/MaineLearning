<?php
$can_connect = false;
$connect_error = 'N/A';
$can_select = false;
$select_error = 'N/A';
$wordpress_exists = false;
$failure_encountered = false;

if ( false === @mysql_connect( $_POST['server'], $_POST['user'], $_POST['pass'] ) ) { // Couldnt connect to server or invalid credentials.
	$connect_error = mysql_error();
} else {
	$can_connect = true;
	
	if ( false === @mysql_select_db( $_POST['name'] ) ) { // 
		$can_select = false;
		$select_error = mysql_error();
	} else {
		$can_select = true;
		
		// Check number of tables already existing with this prefix.
		$result = mysql_query( "SHOW TABLES LIKE '" . mysql_real_escape_string( $_POST['prefix'] ) . "%'" );
		if ( mysql_num_rows( $result ) > 0 ) {
			$wordpress_exists = true;
		} else {
			$wordpress_exists = false;
		}
		unset( $result );
	}
}


// CAN CONNECT
echo '1. Logging in to server ... ';
if ( $can_connect === true ) {
	echo 'Success<br>';
} else {
	echo '<font color=red>Failed</font><br>';
	echo '&nbsp;&nbsp;&nbsp;&nbsp;Error: ' . $connect_error . '<br>';
	$failure_encountered = true;
}


// CAN ACCESS DATABASE BY NAME
echo '2. Verifying database access & permission … ';
if ( $can_select === true ) {
	echo 'Success<br>';
} else {
	echo '<font color=red>Failed</font><br>';
	echo '&nbsp;&nbsp;&nbsp;&nbsp;Error: ' . $select_error . '<br>';
	$failure_encountered = true;
}


// DOES WORDPRESS EXIST?
echo '3. Verifying no existing WP data ... ';
if ( $failure_encountered === true ) {
	echo 'N/A<br>';
} else {
	if ( $wordpress_exists !== true ) { // No existing WordPress.
		echo 'Success<br>';
	} else { // WordPress exists.
		if ( $_POST['wipe_database'] == '1' ) { // Option to wipe enabled.
			echo '<font color=red>Warning</font><br>';
			echo '&nbsp;&nbsp;&nbsp;&nbsp;WordPress already exists in this database with this prefix.<br>';
			echo '&nbsp;&nbsp;&nbsp;&nbsp;It will be wiped prior to import on the next step. Use caution.<br>';
		} else { // Not wiping. We have an error.
			echo '&nbsp;&nbsp;&nbsp;&nbsp;Error: WordPress already exists in this database with this prefix.<br>';
			$failure_encountered = true;
		}
	}
}


// OVERALL RESULT
echo '4. Overall mySQL test result ... ';
if ( $failure_encountered !== true ) {
	echo 'Success<br>';
} else {
	echo '<font color=red>Failed</font><br>';
}


die();
?>