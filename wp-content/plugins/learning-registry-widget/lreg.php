<?php

/*
Plugin Name: Learning Registry Display Widget
Description: Facilitates the display of Learning Registry Content from a node of your choice
Version: 0.11
Author: pgogy
Plugin URI: http://www.pgogy.com/code/learning-registry-widget
Author URI: http://www.pgogy.com
License: GPL
*/
 
require_once( 'lreg_ajax.php' ); 

add_action("wp_head","lr_add_scripts");		
	
function lr_add_scripts(){
	
	?><script type='text/javascript' src='<?PHP echo site_url(); ?>/wp-includes/js/jquery/jquery.js'></script>
	<script type="text/javascript" language="javascript">

	var ajaxurl = '<?PHP echo site_url(); ?>/wp-admin/admin-ajax.php';		

	function lreg_call(post, div, url, items){
		
		jQuery(document).ready(function($) {
								
				var data = {
					action: 'lreg_search',
					node_url: url,
					term:post,
					max:items
				};		
						
				jQuery.post(ajaxurl, data, 
							
				function(response){
				
					alert(response);
				
					document.getElementById(div).innerHTML = response;
								
				});
								
		});
			
	}</script><?PHP
	
}
 

function learning_registry_widget_display() {
	require_once( 'lreg_class.php' );
	register_widget( 'learning_registry_search' );
}
add_action( 'widgets_init', 'learning_registry_widget_display', 1 );


?>