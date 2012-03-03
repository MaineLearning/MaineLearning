<?php
if ( isset( $_GET['action'] ) ) {
	if ( $_GET['action'] == 'phpinfo' ) {
		phpinfo();
	} else {
		echo 'Invalid action.';
	}
} else {
	echo 'No action given.';
}
?>