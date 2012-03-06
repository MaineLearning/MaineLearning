<?php
/*
Plugin Name:   Network Privacy
Version:       0.1.2
Description:   Adds more privacy options to Settings -> Privacy pages and when Network activated: Super Admin -> Options & Sites pages.
Author:        Ron Rennick
Author URI:    http://ronandandrea.com/
Plugin URI:    http://wpmututorials.com/plugins/network-privacy/

Original plugin by D Sader (http://www.snowotherway.org/)

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License version 2 as published by
 the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

TODO -
 * add if(is_feed) graceful_fail( 'Private Blog' );
 * add admin redirect if not primary site
 * proper gettext calls, pot file & load text domain

*/
class RA_Network_Privacy {

	var $settings = false;
	var $meta = array(
		1 => array(
			'settings_label' => 'Open to search engines',
			'sites_label' => 'Public (%d)'
		),
		0 => array(
			'settings_label' => 'Block search engines',
			'network_label' => 'Managed per site',
			'sites_label' => 'No Search (%d)'
		),
		-1 => array(
			'login_message' => ' can be viewed by registered users of this network only.',
			'settings_label' => 'Registered network users',
			'network_label' => 'Must be registered users',
			'sites_label' => 'Users only (%d)'
		),
		-2 => array(
			'login_message' => ' can be viewed by registered users of this site only.',
			'settings_label' => 'Site subscribers',
			'sites_label' => 'Subscribers only (%d)',
			'network_label' => 'Must be site subscribers',
			'cap' => 'read'
		),
		-3 => array(
			'login_message' => ' can be viewed by site administrators only.',
			'settings_label' => 'Site administrators',
			'sites_label' => 'Administrators only (%d)',
			'network_label' => 'Must be site administrators',
			'cap' => 'add_users'
		),
	);

	function __construct() {

		$net_settings = get_site_option( 'ra_network_privacy', false );
		$this->settings = is_array( $net_settings ) && !empty( $net_settings['network'] ) ? $net_settings : array( 'network' => 0, 'privacy' => 0 );

		add_action( 'template_redirect', array( $this, 'authenticator' ) );
		add_action( 'do_robots', array( $this, 'do_robots' ), 1 );
		add_action( 'wp_head', array( $this, 'noindex' ), 0 );
		add_action( 'login_head', array( $this, 'noindex' ), 1 );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_filter( 'option_ping_sites', array( $this, 'privacy_ping_filter' ), 1 );

		if( $this->settings['privacy'] < 0 )
			add_filter( 'pre_option_blog_public', create_function( '', "return {$this->settings['privacy']};" ) );

		if( get_option( 'blog_public' ) < 0 )
			add_action( 'login_form', array( &$this, 'privacy_login_message' ) );
	}

	function do_robots() {

		remove_action( 'do_robots', 'do_robots' );

		header( 'Content-Type: text/plain; charset=utf-8' );

		do_action( 'do_robotstxt' );

		echo "User-agent: *\n";
		if ( '1' != get_option( 'blog_public' ) ) {
			echo "Disallow: /\n";
		} else {
			echo "Disallow:\n";
			echo "Disallow: /wp-admin\n";
			echo "Disallow: /wp-includes\n";
			echo "Disallow: /wp-login.php\n";
			echo "Disallow: /wp-content/plugins\n";
			echo "Disallow: /wp-content/cache\n";
			echo "Disallow: /wp-content/themes\n";
			echo "Disallow: /trackback\n";
			echo "Disallow: /comments\n";
		}
	}	
	function noindex() {

		remove_action( 'login_head', 'noindex' );
		remove_action( 'wp_head', 'noindex', 1 );

		// If the blog is not public, tell robots to go away.
		if ( '1' != get_option( 'blog_public' ) )
			echo "<meta name='robots' content='noindex,nofollow' />\n";

	}
	function privacy_ping_filter( $sites ) {

		remove_filter( 'option_ping_sites', 'privacy_ping_filter' );
		if ( '1' == get_option( 'blog_public' ) )
			return $sites;
		
		return '';

	}
	function sites_add_privacy_options() {

		global $details;
?>
		<h4>Additional Privacy Options</h4>
<?php		for( $i = 1; $i > -4; $i-- ) { ?>
			<input type='radio' name='blog[public]' value='<?php echo $i; ?>' <?php checked( $details->public == $i ); ?> /> <?php _e( $this->meta[$i]['settings_label'] ); ?><br />
<?php		}

	}
	// hook into blog privacy selector(options-privacy.php)
	function add_privacy_options($options) {

		$privacy = get_option( 'blog_public' );
		for( $i = ( is_multisite() ? -1 : -2 ); $i > -4; $i-- ) {
?>
			<br />
			<input id="privacy-<?php echo $i; ?>" type="radio" name="blog_public" value="<?php echo $i; ?>" <?php checked( $i, $privacy ); ?> />
			<label for="privacy-<?php echo $i; ?>"><?php printf( __( 'I would like my site to be visible only to %s.'), $this->meta[$i]['settings_label'] ); ?></label>
<?php
		}

	}
	function privacy_login_message () {

		$privacy = get_option( 'blog_public' );
		if( !empty( $this->meta[$privacy]['login_message'] ) )
			echo '<p>' . bloginfo( 'name' ) . __( $this->meta[$privacy]['login_message'] ) . '</p>';
	}

	// for logged in users to add timed "refresh"
	function login_header() {
			nocache_headers();
			header( 'Content-Type: text/html; charset=utf-8' );
		?>
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml" <?php if ( function_exists('language_attributes') ) language_attributes(); ?>>
			<head>
				<title><?php _e("Private Blog Message"); ?></title>
				<meta http-equiv="refresh" content="5;URL=<?php echo get_settings('siteurl'); ?>/wp-login.php" />
				<?php wp_admin_css( 'css/login' );
				wp_admin_css( 'css/colors-fresh' );	?>				
				<link rel="stylesheet" href="css/install.css" type="text/css" />
				<?php do_action('login_head'); ?>
			</head>
			<body class="login">
				<div id="login">
					<h1><a href="<?php echo apply_filters( 'login_headerurl', network_home_url() ); ?>" title="<?php echo apply_filters( 'login_headertitle', $current_site->site_name ); ?>"><span class="hide"><?php bloginfo('name'); ?></span></a></h1>
	<?php
	}

	function authenticator () {

		$privacy = get_option( 'blog_public' );
		if( $privacy > -1 )
			return;

		if( $privacy > -2 || current_user_can( $this->meta[$privacy]['cap'] ) )
			return;

		if ( is_user_logged_in() ) {

			$this->login_header();
?>
					<form name="loginform" id="loginform">
						<p>Wait 5 seconds or 
							<a href="<?php echo get_settings('siteurl'); ?>/wp-login.php">click</a> to continue.</p>
							<?php $this->privacy_login_message (); ?>
					</form>
				</div>
			</body>
		</html>
<?php
			exit;
		}

		nocache_headers();
		header( 'HTTP/1.1 302 Moved Temporarily' );
		header( 'Location: ' . get_settings( 'siteurl' ) . '/wp-login.php?redirect_to=' . urlencode( $_SERVER['REQUEST_URI'] ) );
        	header( 'Status: 302 Moved Temporarily' );
		exit;

	}

	function network_privacy_options_page() { ?>
		<h3><?php _e( 'Network Privacy Selector' ); ?></h3>
		<table class="form-table">
		<tr valign="top"> 
			<th scope="row"><?php _e('Network Privacy'); ?></th>
			<td><select name="ra_network_privacy" id="ra_network_privacy">
<?php		for( $i = 0; $i > -4; $i-- ) { ?>
				<option value="<?php echo $i; ?>" <?php selected( $i == $this->settings['privacy'] ); ?>><?php _e( $this->meta[$i]['network_label'] ); ?></option>
<?php		} ?>
			</select></td>
		</tr>
		</table> 
<?php	}
	function network_privacy_update() {

		$this->settings['privacy'] = (int) $_POST['ra_network_privacy'];
		update_site_option( 'ra_network_privacy', $this->settings );

	}
	function admin_init() {

		if( !is_plugin_active( plugin_basename( __FILE__ ) ) )
			$this->settings['network'] = 1;

		if( is_multisite() ) {

			if( 1 == $this->settings['network'] ) {

				add_action( 'update_wpmu_options', array( $this, 'network_privacy_update' ) );
				add_action( 'wpmu_options', array( $this, 'network_privacy_options_page' ) );

			}
		}

		if( 0 == $this->settings['privacy'] )
			add_action( 'blog_privacy_selector', array( $this, 'add_privacy_options' ) );
	}
}

$ra_network_privacy = new RA_Network_Privacy();

function ra_network_privacy_activate() {
	$settings = array( 'network' => '0', 'privacy' => '0' );
	if( !empty( $_GET['networkwide'] ) && 1 == $_GET['networkwide'] )
		$settings['network'] = 1;

	update_site_option( 'ra_network_privacy', $settings );
}
register_activation_hook( __FILE__, 'ra_network_privacy_activate' );
function ra_network_privacy_deactivate() {
	delete_site_option( 'ra_network_privacy' );
}
register_deactivation_hook( __FILE__, 'ra_network_privacy_deactivate' );
