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

/**
 * Enqueue scripts
 */
function lr_enqueue_scripts() {
        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'learning-registry', WP_PLUGIN_URL . '/learning-registry-widget/lr.js', array( 'jquery' ) );

        wp_localize_script( 'learning-registry', 'LR', array(
                'ajaxurl' => site_url() . '/wp-admin/admin-ajax.php'
        ) );
}
add_action( 'wp_enqueue_scripts', 'lr_enqueue_scripts' );

function learning_registry_widget_display() {
	require_once( 'lreg_class.php' );
	register_widget( 'learning_registry_search' );
}
add_action( 'widgets_init', 'learning_registry_widget_display', 1 );


?>
