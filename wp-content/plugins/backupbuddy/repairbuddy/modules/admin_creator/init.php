<?php
// module_slug, Module Title, Module Description, bootstrap WordPress?
class rb_admin_creator extends repairbuddy_module {
	var $_module_name = '';
	function __construct() {
		pb_add_action( 'init', array( &$this, 'init' ) );
	} //end constructor
	function init() {
		$args = array(
			'slug' => 'admin_creator',
			'title' => 'Create Admin Account',
			'description' => 'Create a new administrator user account in WordPress.',
			'page' => 'home',
			'bootstrap_wordpress' => true,
			'mini_mode' => '',
			'is_subtle' => ''
		);
		$this->_module_name = $args[ 'slug' ];
		pb_register_module( $args );
		
		//Actions
		pb_add_action( 'print_scripts', array( &$this, 'print_scripts' ) );
		pb_add_action( 'print_styles', array( &$this, 'print_styles' ) );
		pb_add_action( 'pb_ajax_search_user', array( &$this, 'ajax_search_user' ) );
		pb_add_action( 'pb_ajax_create_user', array( &$this, 'ajax_create_user' ) );
		$action = sprintf( 'pb_loadpage_%s_%s', $args[ 'slug' ], $args[ 'page' ] );
		pb_add_action( $action , array( &$this, 'display_page' ) );
	} //end init
	function ajax_search_user() {
		$search_string = $_POST[ 'search' ];
		
		//Try to get the user by login
		$user = get_user_by( 'login', $search_string );
		if ( $user ) die( json_encode( $user ) );
		
		$user = get_user_by( 'email', $search_string );
		if ( $user ) die( json_encode( $user ) );
		
		die( json_encode( array( 'error' => 'No users found.  Would you like to create one?' ) ) );
				
		exit;
	} //end ajax_search_user
	function ajax_create_user() {
		
		$username = sanitize_user( $_POST['username'] );
		$pass1 = $_POST['pass1'];
		$pass2 = $_POST[ 'pass2' ];
		$email = $_POST['email'];
		$user_id = absint( $_POST[ 'user_id' ] );
		$role = 'administrator';
		
		if ( $pass1 != $pass2 ) {
			die( json_encode( array( "error" => "The passwords do not match" ) ) );
		}
		
		if ( !validate_username( $username ) ) {
			die( json_encode( array( "error" => "The username is invalid" ) ) );
		}
		
		if ( !is_email( $email ) ) {
			die( json_encode( array( "error" => "The email address is invalid" ) ) );
		}
		if ( empty( $username ) ) {
			die( json_encode( array( "error" => "Username cannot be empty" ) ) );
		}
		if ( empty( $pass1 ) ) {
			die( json_encode( array( "error" => "Password cannot be empty" ) ) );
		}
		
		if ( username_exists( $username ) && $user_id == 0 ) {
			die( json_encode( array( "error" => "Username already exists" ) ) );
		}
		if ( email_exists( $email ) && $user_id == 0 ) {
			die( json_encode( array( "error" => "E-mail address already exists" ) ) );
		}
		
		$user_created = false;
		
		$return_html = ''; 
		
		if ( !username_exists( $username ) && !username_exists( $email ) ) {
			$user_args = array(
				'user_login' => $username, 
				'user_email' => $email,
				'user_pass' => $pass1,
				'role' => $role,		
			);
			$result = wp_insert_user( $user_args );
			if ( is_wp_error($result) )
	  			 echo 'ERROR: ' . $result->get_error_message();
	  		if ( is_multisite() ) {
	  			if ( !function_exists( 'grant_super_admin' ) ) {
	  				require_once( ABSPATH . 'wp-admin/includes/ms.php' ); 
				}
				grant_super_admin( $result );
			}
			$user_created = true;
			$return_html = sprintf( "User of <em>%s</em> has been created! Your password is <em>%s</em>. <a href='%s'>Login Now</a>", $username, $pass1, admin_url() );
		
		} else {
			if ( false != ( $user_object = get_user_by( 'id', $user_id ) ) ) {
				if ( is_wp_error($user_object) ) {
		  			die( json_encode( array( "error" => $user_object->get_error_message() ) ) );
		  		}
		  		
		  		$user_args = array(
		  			'ID' => $user_object->ID,
		  			'user_pass' => $pass1,
		  			'role' => $role
		  		);
				
				$result = wp_update_user( $user_args );
				if ( is_multisite() ) {
					if ( !function_exists( 'grant_super_admin' ) ) {
			  			require_once( ABSPATH . 'wp-admin/includes/ms.php' ); 
					}
					grant_super_admin( $user_object->ID );
				}
				if ( is_wp_error($result) ) {
					die( json_encode( array( 'error' => $result->get_error_message() ) ) );
				}
				$user_created = true;
				$return_html = sprintf( "User of <em>%s</em> has been updated! Your new password is <em>%s</em>. <a href='%s'>Login Now</a>", $username, $pass1, admin_url() );
			}
		}
		if ( $user_created == true ) {
			die( json_encode( array( 'success' => $return_html ) ) );
		} else {
			die( json_encode( array( "error" => "User could not be updated" ) ) );
		}
	} //end ajax_create_user
	
	function display_page() {
		$page = $this->get_plugin_dir( 'pages/home.php', __FILE__ );
		
		if ( !file_exists( $page ) ) {
			?>
			<h2>Could not load page</h2>
			<?php
		} else {
			require_once( $page );
		}
	} //end display_page
	function print_scripts() {
		if ( $this->_module_name != $this->get_current_module() ) return;
		$localized_vars = array(
			'default' => 'Username or E-mail Address',
			'search' => 'Search',
			'searching' => 'Searching...',
			'save' => 'Save',
			'saving' => 'Saving...',
			'cancel' => 'Cancel',
			'user_edit' => 'Editing an existing user...'
		);
		$this->load_js( $this->get_plugin_url( '/js/admin-creator.js', __FILE__ ), 'pb_admin_creator', $localized_vars );
	} //end print_scripts
	function print_styles() {
		if ( $this->_module_name != $this->get_current_module() ) return;
		$this->load_css( $this->get_plugin_url( '/css/styles.css', __FILE__ ) );
	} //end print_styles
	
} //end class
$rb_admin_creator = new rb_admin_creator();
?>