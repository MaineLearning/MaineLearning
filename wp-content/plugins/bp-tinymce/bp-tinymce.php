<?php

// Massive hack. Loads my JS before BP's so that my click event is registered first.
// Talk to BP team about the craptastic way BP uses wp_enqueue_script()

if ( !class_exists( 'BP_TinyMCE' ) ) :

class BP_TinyMCE {
	var $is_teeny;
	var $init_filter;

	function bp_tinymce() {
		$this->__construct();
	}

	function __construct() {
		add_action( 'bp_before_container', array( $this, 'add_js' ), 1 );
		add_action( 'init', array( $this, 'enqueue_script' ), 1 );

		$this->is_teeny = apply_filters( 'bp_tinymce_is_teeny', false );
		$this->init_filter = $this->is_teeny ? 'teeny_mce_before_init' : 'tiny_mce_before_init';

		// Some components have filterable allowedtags lists.
		$tinymce_components = array(
			'forums',
			'activity',
			'groups'
		);

		foreach( $tinymce_components as $component  ) {
			add_filter( 'bp_forums_allowed_tags', array( $this, 'allowed_tags' ), 1 );
			add_filter( 'bp_activity_allowed_tags', array( $this, 'allowed_tags' ), 1 );
			add_filter( 'bp_' . $component . "_allowed_tags", array( $this, 'allowed_tags' ), 1 );
			add_filter( "bp_" . $component . "_filter_kses", array( $this, 'allowed_tags' ), 1 );
		}
	}

	function add_js() {
		global $bp;

		$baseurl = includes_url( 'js/tinymce' );

		if ( $this->enable_tinymce_on_page() ) {
			wp_tiny_mce( $this->is_teeny, array( 'mode' => 'textareas' ) );
		}

	}

	function enqueue_script() {
		global $bp;

		if ( $this->enable_tinymce_on_page() ) {
			add_filter( $this->init_filter, array( $this, 'tinymce_init_params' ) );

			require_once( ABSPATH . '/wp-admin/includes/post.php' );

			wp_enqueue_script( 'editor' );

			// We have to deregister the DTheme ajax and reregister it to be dependent
			// on our own, so that our click events are registered before bp-default's
			wp_deregister_script( 'dtheme-ajax-js' );

			// Register our custom JS
			wp_register_script('bp-tinymce-js', WP_PLUGIN_URL . '/bp-tinymce/bp-tinymce-js.js', array( 'jquery' ) );
			wp_enqueue_script( 'bp-tinymce-js' );

			// Reload bp-default ajax
			wp_enqueue_script( 'dtheme-ajax-js', get_template_directory_uri() . '/_inc/global.js', array( 'jquery', 'bp-tinymce-js' ) );

			// Enqueue the styles
			wp_enqueue_style( 'bp-tinymce-css', WP_PLUGIN_URL . '/bp-tinymce/bp-tinymce-css.css' );
		}
	}

	function tinymce_init_params( $initArray ) {
		$plugins = explode( ',', $initArray['plugins'] );

		// Remove internal linking
		$wplink_key = array_search( 'wplink', $plugins );
		if ( $wplink_key ) {
			unset( $plugins[$wplink_key] );
		}

		// Important. Allows arbitrary textareas to be selected
		unset( $initArray['editor_selector'] );

		$plugins = array_values( $plugins );

		$initArray['plugins'] = implode( ',', $plugins );

		return apply_filters( 'bp_tinymce_init_parms', $initArray );
	}


	function enable_tinymce_on_page() {
		// todo
		return true;
	}

	function allowed_tags( $allowedtags ) {
		global $allowedtags;

		$allowedtags['em'] = array();
		$allowedtags['strong'] = array();
		$allowedtags['ol'] = array();
		$allowedtags['li'] = array();
		$allowedtags['ul'] = array();
		$allowedtags['blockquote'] = array();
		$allowedtags['code'] = array();
		$allowedtags['pre'] = array();
		$allowedtags['a'] = array(
			'href' => array(),
			'title' => array(),
			'target' => array(),
		);
		$allowedtags['img'] = array(
			'src' => array(),
		);
		$allowedtags['b'] = array();
		$allowedtags['span'] = array(
			'style' => array(),
		);
		$allowedtags['p'] = array(
			'style' => array()
		);
		$allowedtags['br'] = array();

		return apply_filters( 'bp_tinymce_allowedtags', $allowedtags );
	}
}

endif;

$bp_tinymce = new BP_TinyMCE;

?>
