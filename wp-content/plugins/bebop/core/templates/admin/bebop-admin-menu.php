<h2 class='nav-tab-wrapper margin-top_40px'>
	<a href='?page=bebop_admin' class='nav-tab<?php if ( $_GET['page'] == 'bebop_admin' ) {
		echo ' nav-tab-active';
	}?>'><?php _e( 'Admin Home', 'bebop' ); ?></a>
	
	<a href='?page=bebop_admin_settings' class='nav-tab<?php if ( $_GET['page'] == 'bebop_admin_settings' ) {
		echo ' nav-tab-active';
	}?>'><?php _e( 'General Settings', 'bebop' ); ?></a>
	
	<a href='?page=bebop_providers' class='nav-tab<?php if ( $_GET['page'] == 'bebop_providers' ) {
		echo ' nav-tab-active';
	}?>'><?php _e( 'Content Providers', 'bebop' ); ?></a>
	
	<a href='?page=bebop_content' class='nav-tab<?php if ( $_GET['page'] == 'bebop_content' ) {
		echo ' nav-tab-active';
	}?>'><?php _e( 'Content', 'bebop' ); ?></a>
	
	<a href='?page=bebop_error_log' class='nav-tab<?php if ( $_GET['page'] == 'bebop_error_log' ) {
		echo ' nav-tab-active';
	}?>'><?php _e( 'Error Log', 'bebop' ); ?></a>
	
	<a href='?page=bebop_general_log' class='nav-tab<?php if ( $_GET['page'] == 'bebop_general_log' ) {
		echo ' nav-tab-active';
	}?>'><?php _e( 'General Log', 'bebop' ); ?></a>
</h2>