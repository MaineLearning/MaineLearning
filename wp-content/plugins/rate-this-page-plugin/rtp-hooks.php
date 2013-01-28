<?php
/**
 * Initialize cookie
 *
 * @return void
 */
if ( !function_exists( 'aft_init_cookie' ) ) {
	function aft_init_cookie() {
		global $user_identity;
		
		if ( !empty( $user_identity ) ) {
			$cookie_value = 'user-' . get_current_user_id();
		} else {
			$cookie_value = 'guest-'.rand( RTP_COOKIE_RANDMIN, RTP_COOKIE_RANDMAX );
		}
		
		// Trap and generate again if cookie matched in session key inside the table.
		// FOR GUEST RATER.
		if ( rtp_cookie_is_match( $cookie_value ) ) {
			$cookie_value = 'guest-'.rand( RTP_COOKIE_RANDMIN, RTP_COOKIE_RANDMAX );
		}

		if ( !isset( $_COOKIE[ RTP_COOKIE_NAME ] ) && empty( $_COOKIE[ RTP_COOKIE_NAME ] ) ) {
			setcookie( RTP_COOKIE_NAME, $cookie_value, RTP_COOKIE_EXPIRATION, SITECOOKIEPATH, COOKIE_DOMAIN );
			
			//$_COOKIE var accepts immediately the value so it will be retrieved on page first load.
			$_COOKIE[ RTP_COOKIE_NAME ] = $cookie_value;
		}
	}
}

/**
 * When activated this function will create the admin menu and other initializations
 *
 * @return void
 */
if ( !function_exists( 'aft_plgn_load' ) ) {
	function aft_plgn_load() {
		// Create admin menu
		add_menu_page( RTP_PLUGIN_NAME.' Main', RTP_PLUGIN_NAME, 'administrator', 'rate-this-page-plugin/rtp-admin-main.php', '',  RTP_PLUGIN_DIR_IMG . 'AFT_icon-16.png' );
		
		// Create admin submenu
		add_submenu_page( 'rate-this-page-plugin/rtp-admin-main.php', __( RTP_PLUGIN_NAME.' Main' ), __( 'RTP Configurations' ), 'administrator', 'rate-this-page-plugin/rtp-admin-main.php' );
		add_submenu_page( 'rate-this-page-plugin/rtp-admin-main.php', __( RTP_PLUGIN_NAME.' Reports' ), __( 'RTP Reports' ), 'administrator', 'rate-this-page-plugin/rtp-admin-reports.php' );
		
		//Create options for this plugin.
		add_action( 'admin_init', 'aft_plgn_options' );
	}
}

/**
 * jQuery/Stylesheet Initialization Function for Admin
 *
 * @return void
 */
if ( !function_exists( 'rtp_scripts_admin_init' ) ) {
	function rtp_scripts_admin_init( $hook_suffix ) {
		$rtp_admin_pages = array(
			'rate-this-page-plugin/rtp-admin-main.php',
			'rate-this-page-plugin/rtp-admin-reports.php',
		);
		
		if ( in_array( $hook_suffix, $rtp_admin_pages ) ) {
			wp_enqueue_script( 'jquery-ui-widget' );
			wp_enqueue_script( 'jquery-ui-core' );
			wp_enqueue_script( 'jquery-ui-tabs' );	
			wp_enqueue_script( 'jquery-tablesorter', RTP_PLUGIN_DIR_JS . 'jquery.tablesorter.min.js', array( 'jquery' ) );
			wp_enqueue_script( 'jquery-tablesorter-pager', RTP_PLUGIN_DIR_JS . 'jquery.tablesorter.pager.js', array('jquery' ) );
			wp_enqueue_script( 'jquery-ui-cookie', RTP_PLUGIN_DIR_JS . 'external/jquery.cookie.js', array( 'jquery' ) );
			
			wp_enqueue_style( 'jquery-ui', RTP_PLUGIN_DIR_CSS . 'cupertino/jquery-ui.custom.css' );
			wp_enqueue_style( 'feedback-admin-ui', RTP_PLUGIN_DIR_CSS . 'rtp-style-admin.css' );
		}
	}
}

/**
 * jQuery Initialization Function
 *
 * jQuery Plugins: Raty, Progress Bar ( WP 3.2.1 below )
 *
 * @return void
 */
if ( !function_exists( 'aft_plgn_jquery_init' ) ) {
	function aft_plgn_jquery_init() {
		global $post;
		
		wp_enqueue_script( 'jquery-raty-min', RTP_PLUGIN_DIR_JS . 'jquery.raty.min.js', array( 'jquery' ) );
		wp_enqueue_script( 'jquery-progressbar', RTP_PLUGIN_DIR_JS . 'external/jquery.ui.progressbar.min.js', array( 'jquery', 'jquery-ui-widget', 'jquery-ui-core' ) );
		wp_enqueue_script( 'rtp-jscript', RTP_PLUGIN_DIR_JS . 'rtp.min.js', array( 'jquery' ), '1.1', false );
		wp_enqueue_script( 'rtp-ajax-request', RTP_PLUGIN_DIR_JS . 'rtp-ajax.min.js', array( 'jquery' ), '1.1', false );
		
		$option = get_option('aft_options_array');
		
		if ( !rtp_is_rated( $post->ID ) ) {
			$is_rated = false;
		} else {
			$is_rated = true;
		}
		
		$adata = aft_plgn_fetch_rate_summary( $post->ID, ( is_page() ) ? 'true' : 'false' );
		$rdata = aft_plgn_fetch_ratings( $post->ID, $_COOKIE[ RTP_COOKIE_NAME ], ( is_page() ) ? 'true' : 'false' );
		
		wp_localize_script( 'rtp-jscript', 'rtpL10n', array(
			'img_path' 	=> RTP_PLUGIN_DIR_IMG,
			'custom_hints' => $option['rtp_custom_hints'],
			'hints' => json_encode( array(
				'chint_1' 		=> array( __('Poor'), __('Poor'), __('Average'), __('Average'), __('Excellent') ),
				'chint_2' 		=> array( __('Low'), __('Low'), __('Medium'), __('Medium'), __('High') ),
				'trustworthy'	=> array( __('Lacks reputable sources'), __('Few reputable sources'), __('Adequate reputable sources'), __('Good reputable sources'), __('Great reputable sources') ),
				'objective'		=> array( __('Heavily biased'), __('Moderate bias'), __('Minimal bias'), __('No obvious bias'), __('Completely unbiased') ),
				'complete'		=> array( __('Missing most information'), __('Contains some information'), __('Contains key information, but with gaps'), __('Contains most key information'), __('Comprehensive coverage') ),
				'wellwritten'	=> array( __('Incomprehensive'), __('Difficult to understand'), __('Adequate clarity'), __('Good clarity'), __('Exceptional clarity') ) ) ),
			'cancel_hint' => __( 'Remove this rating', RTP_PLUGIN_SNAME ),
			'is_rated' => $is_rated,
		) );
		
		wp_localize_script( 'rtp-ajax-request', 'rtpL10nAjax', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'success_msg' => __( 'Feedback submitted successfuly!', RTP_PLUGIN_SNAME ),
			'adata_arr' => json_encode( array(
				'trustworthy' => $adata->total_trustworthy,
				'objective' => $adata->total_objective,
				'complete' => $adata->total_complete,
				'wellwritten' => $adata->total_wellwritten,
				'c_trustworthy' => $adata->count_trustworthy,
				'c_objective' => $adata->count_objective,
				'c_complete' => $adata->count_complete,
				'c_wellwritten' => $adata->count_wellwritten,
			) ),
			'is_rated' => $is_rated,			
			'is_custom_labels' => $option['rtp_is_custom_label'],
			'rdata_arr' => json_encode( array(
				'is_highly_knowledgable' => $rdata->is_highly_knowledgable,
				'is_relevant' => $rdata->is_relevant,
				'is_my_profession' => $rdata->is_my_profession,
				'is_personal_passion' => $rdata->is_personal_passion,
			) ),
			'thanks_msg' => __( 'Thank you for rating!', RTP_PLUGIN_SNAME ),
		) );
	}
}

/**
 * Stylesheet Initialization Function
 *
 * @return void
 */
if ( !function_exists( 'aft_plgn_css_init' ) ) {
	function aft_plgn_css_init() {
		wp_enqueue_style( 'feedback-style-sheet', RTP_PLUGIN_DIR_CSS . 'rtp-style.css', false, '1.1', 'all' );
		wp_enqueue_style( 'jquery-ui-css', RTP_PLUGIN_DIR_CSS . 'lightness/jquery-ui.custom.css', false, '1.1', 'all' );
		
		$theme = get_option('aft_options_array');
		
		if ( $theme['rtp_theme'] != 'default' ) {
			wp_enqueue_style( 'feedback-style-' . $theme['rtp_theme'], RTP_PLUGIN_DIR_THEME . $theme['rtp_theme']. '/rtp-style.' . $theme['rtp_theme'] . '.css', false, '1.1', 'all' );
		}
	}
}

/**
 * Initialize options for this plugin
 *
 * @return void
 */
if ( !function_exists( 'aft_plgn_options' ) ) {
	function aft_plgn_options() {
		global $aft_options_array, $aft_options_by_array, $aft_themes_array;
		
		$cat_id = get_all_category_ids();
		$page_id = get_all_page_ids();
		
		$post_value = 0;
		$options_category = array();
		$options_page = array();
		
		if ( $cat_id ) {
			foreach( $cat_id as $id ) {
				$options_category[get_cat_name($id)] = $post_value;
			}
		}
		
		if ( $page_id ) {
			foreach( $page_id as $id ) {
				$page_data = get_page( $id );
				$options_page[$page_data->post_title] = $post_value;
			}
		}
		
		// Default setup for option variable
		$aft_options_list = array(
			'aft_is_enable'				=>	'true',
			'aft_location'				=>	'bottom',
			'aft_insertion'				=>	'all-article',
			'rtp_is_custom_label'		=>	'false',
			'rtp_custom_labels'			=>	array(),
			'rtp_custom_hints'			=>  0, //Custom hints disabled
			'rtp_theme'					=>  'default',
			'rtp_can_rate'				=>  3, // Default into both
			'rtp_logged_by'				=>  3, // Default into both
			'rtp_use_shortcode'			=>  0, // Default into no
		);
		
		if ( !get_option( 'aft_options_array' ) ) {
			add_option( 'aft_options_array', $aft_options_list, '', 'yes' );
		}
		
		$options_by_category = array( array( $options_category ) );
		$options_by_page = array( array( $options_page ) );
		
		$aft_options_by_list = array(
			'category'			=> array( $options_by_category ),
			'page'				=> array( $options_by_page ),
		);
			
		if ( !get_option( 'aft_options_by_array' ) ) {
			add_option( 'aft_options_by_array', $aft_options_by_list, '', 'yes' );
		}
		
		/* TODO: star images also will be changeable in the future */
		$theme_lists = array (
			'default'			=> 'Default',
			'couture_white'		=> 'Couture White',
			'dark_red'			=> 'Dark Red',
			'steel'				=> 'Steel',
			'neutral_blue'		=> 'Neutral Blue',
		);
		
		if ( !get_option( 'aft_themes_array' ) ) {
			add_option( 'aft_themes_array', $theme_lists, '', 'yes' );
		} else {
			update_option ( 'aft_themes_array', $theme_lists );
		}
			
		$aft_options_array = get_option( 'aft_options_array' );
		$aft_options_by_array = get_option( 'aft_options_by_array' );
		$aft_themes_array = get_option( 'aft_themes_array' );
	}
}
/**
 * Function to remove the options that was saved.
 * This function will be called once the register_deactivation_hook( __FILE__, 'aft_plgn_remove_options' )
 * is uncommented in rtp-load.php
 *
 * But for safety purposes I think this will not be called.
 *
 */
if ( !function_exists( 'aft_plgn_remove_options' ) ) {
	function aft_plgn_remove_options() {
		delete_option( 'aft_options_array' );
		delete_option( 'aft_options_by_array' );
	}
}
?>