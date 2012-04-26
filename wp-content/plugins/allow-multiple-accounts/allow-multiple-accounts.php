<?php
/**
 * @package Allow_Multiple_Accounts
 * @author Scott Reilly
 * @version 2.6.2
 */
/*
Plugin Name: Allow Multiple Accounts
Version: 2.6.2
Plugin URI: http://coffee2code.com/wp-plugins/allow-multiple-accounts/
Author: Scott Reilly
Author URI: http://coffee2code.com/
Text Domain: allow-multiple-accounts
Domain Path: /lang/
Description: Allow multiple user accounts to be created from the same email address.

Compatible with WordPress 3.1+, 3.2+, 3.3+ and BuddyPress 1.2+, 1.3+.

=>> Read the accompanying readme.txt file for instructions and documentation.
=>> Also, visit the plugin's homepage for additional information and updates.
=>> Or visit: http://wordpress.org/extend/plugins/allow-multiple-accounts/

TODO:
	* Handle large listings of users. (Separate admin page for listing? Omit accounts tied to email with only one account?)
	* In Multisite, list blog(s) associated with each user?
	* Support different limits for different emails?

*/

/*
Copyright (c) 2008-2012 by Scott Reilly (aka coffee2code)

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation
files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy,
modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR
IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

if ( ! class_exists( 'c2c_AllowMultipleAccounts' ) ) :

require_once( 'c2c-plugin.php' );

class c2c_AllowMultipleAccounts extends C2C_Plugin_033 {

	public static $instance;

	protected $allow_multiple_accounts = false;  // Used internally; not a setting!
	protected $exceeded_limit          = false;
	protected $retrieve_password_for   = '';
	public    $during_user_creation    = false; // part of a hack

	// Only set to true if the plugin was able to replace WP's version of get_user_by()
	public static $controls_get_user_by = false; // part of a hack

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->c2c_AllowMultipleAccounts();
	}

	public function c2c_AllowMultipleAccounts() {
		// Be a singleton
		if ( ! is_null( self::$instance ) )
			return;

		parent::__construct( '2.6.2', 'allow-multiple-accounts', 'c2c', __FILE__, array( 'settings_page' => 'users' ) );
		register_activation_hook( __FILE__, array( __CLASS__, 'activation' ) );
		self::$instance = $this;
	}

	/**
	 * Handles activation tasks, such as registering the uninstall hook.
	 *
	 * @since 2.5
	 *
	 * @return void
	 */
	public function activation() {
		register_uninstall_hook( __FILE__, array( __CLASS__, 'uninstall' ) );
	}

	/**
	 * Handles uninstallation tasks, such as deleting plugin options.
	 *
	 * This can be overridden.
	 *
	 * @since 2.5
	 *
	 * @return void
	 */
	public function uninstall() {
		delete_option( 'c2c_allow_multiple_accounts' );
	}

	/**
	 * Initializes the plugin's config data array.
	 *
	 * @return void
	 */
	public function load_config() {
		$this->name      = __( 'Allow Multiple Accounts', $this->textdomain );
		$this->menu_name = __( 'Multiple Accounts', $this->textdomain );

		$this->config = array(
			'allow_for_everyone' => array('input' => 'checkbox', 'default' => true,
					'label' => __( 'Allow multiple accounts for everyone?', $this->textdomain ),
					'help' => __( 'If not checked, only the emails listed below can have multiple accounts.', $this->textdomain ) ),
			'account_limit' => array( 'input' => 'text', 'default' => '',
					'label' => __( 'Account limit', $this->textdomain ),
					'help' => __( 'The maximum number of accounts that can be associated with a single email address.  Leave blank to indicate no limit.', $this->textdomain ) ),
			'emails' => array( 'input' => 'inline_textarea', 'datatype' => 'array', 'default' => '',
					'input_attributes' => 'style="width:98%;" rows="6"',
					'label' => __( 'Multi-account emails', $this->textdomain ),
					'help' => __( 'If the checkbox above is unchecked, then only the emails listed here will be allowed to have multiple accounts.  Define one per line.', $this->textdomain ) )
		);
	}

	/**
	 * Override plugin framework's register_filters() to register actions and filters.
	 *
	 * @return void
	 */
	public function register_filters() {
		if ( is_multisite() ) {
			add_action( 'network_admin_menu',     array( &$this, 'admin_menu' ) );
			remove_action( 'admin_menu',          array( &$this, 'admin_menu' ) );
		}
		add_action( 'admin_notices',              array( &$this, 'display_activation_notice' ) );
		add_action( 'check_passwords',            array( &$this, 'hack_check_passwords' ) );
		add_filter( 'pre_user_display_name',      array( &$this, 'hack_pre_user_display_name' ) );
		add_filter( 'pre_user_email',             array( &$this, 'hack_pre_user_email' ) );
		add_action( 'register_post',              array( &$this, 'register_post' ), 1, 3 );
		add_filter( 'registration_errors',        array( &$this, 'registration_errors' ), 1 );
		add_action( 'retrieve_password',          array( &$this, 'retrieve_password' ) );
		add_filter( 'retrieve_password_message',  array( &$this, 'retrieve_password_message' ) );
		add_action( 'user_profile_update_errors', array( &$this, 'user_profile_update_errors' ), 1, 3 );
		add_filter( 'wpmu_validate_user_signup',  array( &$this, 'bp_members_validate_user_signup' ) );
		add_action( $this->get_hook( 'after_settings_form' ), array( &$this, 'list_multiple_accounts' ) );
	}

	/**
	 * Outputs the text above the setting form
	 *
	 * @return void (Text will be echoed.)
	 */
	public function options_page_description() {
		$options = $this->get_options();
		parent::options_page_description( __( 'Allow Multiple Accounts Settings', $this->textdomain ) );
		echo '<p>' . __( 'Allow multiple user accounts to be created from the same email address.', $this->textdomain ) . '</p>';
		echo '<p>' . __( 'By default, WordPress only allows a single user account to be assigned to a specific email address.  This plugin removes that restriction.  A setting is also provided to allow only certain email addresses the ability to have multiple accounts.  You may also specify a limit to the number of accounts an email address can have.', $this->textdomain ) . '</p>';
		echo '<p><a href="#multiaccount_list">' . __( 'View a list of user accounts grouped by email address.', $this->textdomain ) . '</a></p>';
	}

	/**
	 * Output activation notice
	 *
	 * @since 2.6
	 *
	 * @return void (Text is echoed.)
	 */
	public function display_activation_notice() {
		if ( ! self::$controls_get_user_by ) {
			$msg = __( 'NOTE: Allow Multiple Accounts is not able to function as intended because another plugin has overridden the WordPress function <code>get_user_by()</code>.', $this->textdomain );
			echo "<div id='message' class='error fade'><p>$msg</p></div>";
//			add_settings_error( 'general', 'allow_multiple_accounts_unable_to_override_get_user_by', $msg, 'error' );
		}
	}

	/**
	 * This is a HACK because WP 3.0 introduced a change that made it
	 * impossible to suppress the unique email check when creating a new user.
	 *
	 * For the hack, this filter is invoked just after wp_insert_user() checks
	 * for the uniqueness of the email address.  What this is doing is
	 * unsetting the flag set by the get_user_by_email() overridden by this
	 * plugin, so that when called in any other context than wp_insert_user(),
	 * it'll actually get the user by email.
	 *
	 * @since 2.0
	 *
	 * @param string $display_name Display name for user
	 * @return string The same value as passed to the function
	 */
	public function hack_pre_user_display_name( $display_name ) {
		$this->during_user_creation = false;
		return $display_name;
	}

	/**
	 * This is a HACK because WP 3.0 introduced a change that made it
	 * impossible to suppress the unique email check when creating a new user.
	 *
	 * For the hack, this filter is invoked just before wp_insert_user() checks
	 * for the uniqueness of the email address.  What this is doing is setting a
	 * flag so that the get_user_by_email() overridden by this plugin, when
	 * called in the wp_insert_user() context, knows to return false, making WP
	 * think the email address isn't in use.
	 *
	 * @since 2.0
	 *
	 * @param string $email Email for the user
	 * @return string The same value as passed to the function
	 */
	public function hack_pre_user_email( $email ) {
		$this->during_user_creation = true;
		return $email;
	}

	/**
	 * This is a HACK because WP 3.0 introduced a change that made it
	 * impossible to suppress the unique email check when creating a new user.
	 *
	 * For the hack, this filter is invoked just before edit_user() does a
	 * bunch of error checks.  What this is doing is setting a flag so that the
	 * get_user_by_email() overridden by this plugin, when called in the
	 * edit_user() context, knows to return false, making WP think the email
	 * address isn't in use.
	 *
	 * @since 2.0
	 *
	 * @param string $user_login User login
	 * @return void
	 */
	public function hack_check_passwords( $user_login ) {
		$this->during_user_creation = true;
	}

	/**
	 * Outputs list of all user email addresses and their associated accounts.
	 *
	 * @return void (Text is echoed.)
	 */
	public function list_multiple_accounts() {
		global $wpdb;
		$users = get_users( array( 'fields' => array( 'ID', 'user_email' ), 'blog_id' => '' ) );
		$by_email = array();
		foreach ( $users as $user )
			$by_email[$user->user_email][] = $user;
		$emails = array_keys( $by_email );
		sort( $emails );
		$style = '';

		echo <<<END
			<style type="text/css">
				.emailrow {
					background-color:#ffffef;
				}
				.check-column {
					display:none;
				}
			</style>
			<div class='wrap'><a name='multiaccount_list'></a>
				<h2>

END;
		echo __( 'E-mail Addresses with Multiple User Accounts', $this->textdomain );
		echo <<<END
				</h2>
				<table class="widefat">
				<thead>
				<tr class="thead">

END;
		echo '<th>' . __( 'Username', $this->textdomain ) . '</th>' .
			 '<th>' . __( 'Name', $this->textdomain ) . '</th>' .
			 '<th>' . __( 'E-mail', $this->textdomain ) . '</th>' .
			 '<th>' . __( 'Role', $this->textdomain ) . '</th>';
// .
//			 '<th class="num">' . __( 'Posts', $this->textdomain ) . '</th>';
		echo <<<END
				</tr>
				</thead>
				<tbody id="users" class="list:user user-list">

END;

		foreach ( $emails as $email ) {
			$email_users = $by_email[$email];
			$count = count( $by_email[$email] );
			echo '<tr class="emailrow"><td colspan="6">';
			printf( _n( '%1$s &#8212; %2$d account', '%1$s &#8212; %2$d accounts', $count, $this->textdomain ), $email, $count );
			echo '</td></tr>';
			foreach ( $by_email[$email] as $euser ) {
				$user_object = new WP_User($euser->ID);
				$roles = $user_object->roles;
				$role = array_shift( $roles );
				$style = ( ' class="alternate"' == $style ) ? '' : ' class="alternate"';
				echo "\n\t" . $this->user_row( $user_object, $style, $role );
			}
		}

		echo <<<END
				</tbody>
				</table>
			</div>

END;
	}

	/**
	 * Indicates if the specified email address has exceeded its allowable number of accounts.
	 *
	 * @param string $email Email address
	 * @param int $user_id (optional) ID of existing user, if updating a user
	 * @return boolean True if the email address has exceeded its allowable number of accounts; false otherwise
	 */
	public function has_exceeded_limit( $email, $user_id = null ) {
		$has = false;
		$options = $this->get_options();
		if ( $options['account_limit'] ) {
			$count = $this->count_multiple_accounts( $email, $user_id );

			if ( ! $options['allow_for_everyone'] && ! in_array( $email, $options['emails'] ) )
				$limit = 1;
			else
				$limit = (int) $options['account_limit'];

			if ( $count >= $limit )
				$has = true;
		}
		return $has;
	}

	/**
	 * Returns a count of the number of users associated with the given email.
	 *
	 * @param string $email The email account
	 * @param int $user_id (optional) ID of existing user, if updating a user
	 * @return int The number of users associated with the given email
	 */
	public function count_multiple_accounts( $email, $user_id =  null ) {
		global $wpdb;
		$sql = "SELECT COUNT(*) AS count FROM $wpdb->users WHERE user_email = %s";
		if ( $user_id )
			$sql .= ' AND ID != %d';
		return (int) $wpdb->get_var( $wpdb->prepare( $sql, $email, $user_id ) );
	}

	/**
	 * Returns the users associated with the given email.
	 *
	 * @param string $email The email account
	 * @return array All of the users associated with the given email
	 */
	public function get_users_by_email( $email ) {
		return get_users( array( 'search' => $email, 'blog_id' => '' ) );
	}

	/**
	 * Returns a boolean indicating if the given email is associated with more than one user account.
	 *
	 * @param string $email The email account
	 * @return bool True if the given email is associated with more than one user account; false otherwise
	 */
	public function has_multiple_accounts( $email ) {
		return $this->count_multiple_accounts( $email ) > 1 ? true : false;
	}

	/**
	 * Handler for 'register_post' action.  Intercepts potential 'email_exists' error and sets flags for later use, pertaining to if
	 * multiple accounts are authorized for the email and/or if the email has exceeded its allocated number of accounts.
	 *
	 * @param string $user_login User login
	 * @param string $user_email User email
	 * @param WP_Error $errors Error object
	 * @param int $user_id (optional) ID of existing user, if updating a user
	 * @return void
	 */
	public function register_post( $user_login, $user_email, $errors, $user_id = null ) {
		$options = $this->get_options();
		if ( $errors->get_error_message( 'email_exists' ) &&
			( $options['allow_for_everyone'] || in_array( $user_email, $options['emails'] ) ) ) {
			if ( $this->has_exceeded_limit( $user_email, $user_id ) )
				$this->exceeded_limit = true;
			else
				$this->allow_multiple_accounts = true;
		}
	}

	/**
	 * Handler for 'registration_errors' action to add and/or remove registration errors as needed.
	 *
	 * @param WP_Error $errors Error object
	 * @return WP_Error The potentially modified error object
	 */
	public function registration_errors( $errors ) {
		if ( $this->exceeded_limit )
			$errors->add( 'exceeded_limit', __( '<strong>ERROR</strong>: Too many accounts are associated with this e-mail address, please choose another one.', $this->textdomain ) );
		if ( $this->allow_multiple_accounts || $this->exceeded_limit ) {
			unset( $errors->errors['email_exists'] );
			unset( $errors->error_data['email_exists'] );
		}
		return $errors;
	}

	/**
	 * Roundabout way of determining what user account a password retrieval is being requested for since some of the actions/filters don't specify.
	 *
	 * @param string $user_login User login
	 * @return string The same value as passed to the function
	 */
	public function retrieve_password( $user_login ) {
		$this->retrieve_password_for = $user_login;
		return $user_login;
	}

	/**
	 * Appends text at the end of a 'retrieve password' email to remind users what accounts they have associated with their email address.
	 *
	 * @param string $message The original email message
	 * @return string Potentially modified email message
	 */
	public function retrieve_password_message( $message ) {
		$user = get_user_by( 'login', $this->retrieve_password_for );
		if ( $this->has_multiple_accounts( $user->user_email ) ) {
			$message .= "\r\n\r\n";
			$message .= __( 'For your information, your e-mail address is also associated with the following accounts:', $this->textdomain ) . "\r\n\r\n";
			foreach ( $this->get_users_by_email( $user->user_email ) as $user ) {
				$message .= "\t" . $user->user_login . "\r\n";
			}
			$message .= "\r\n";
			$message .= __( 'In order to reset the password for any of these (if you aren\'t already successfully in the middle of doing so already), you should specify the login when requesting a password reset rather than using your e-mail.', $this->textdomain ) . "\r\n\r\n";
		}
		return $message;
	}

	/**
	 * Intercept possible email_exists errors during user updating, and also possibly add errors.
	 *
	 * @param WP_Error $errors Error object
	 * @param boolean $update Is this being invoked due to a user being updated?
	 * @param WP_User $user User object
	 */
	public function user_profile_update_errors( $errors, $update, $user ) {
		$this->during_user_creation = false; // Part of HACK to work around WP3.0.0 bug
		$user_id = $update ? $user->ID : null;
		$this->register_post( $user->user_login, $user->user_email, $errors, $user_id );
		$errors = $this->registration_errors( $errors );
	}

	/**
	 * Check user_email for exceeding allowed use under BuddyPress
	 *
	 * Like WP of yore (pre-3.0), BP allows for all registration errors to be
	 * intercepted after detection but before handling by WP. That allow this
	 * function to detect an error raised by due to email_exists() and ignore
	 * or modify it as appropriate according to this plugin.
	 *
	 * Note: This function is hooked against the 'wpmu_validate_user_signup'
	 * filter because it is consistently present across more BP versions,
	 * whereas its own 'bp_core_validate_user_signup' is slated to be renamed
	 * 'bp_members_validate_user_signup' in BP1.3.
	 *
	 *
	 * @since 2.5
	 *
	 * @param array $result BP signup validation result array consisting of 'user_name', 'user_email', and 'errors' elements
	 * @return array The possibly modified results array
	 */
	public function bp_members_validate_user_signup( $result ) {
		if ( $result['errors'] ) {
			$errors = $result['errors']->get_error_messages( 'user_email' );
			if ( ! empty( $errors ) ) {
				$new_errors = array();
				$bp_msg = __( 'Sorry, that email address is already used!', 'buddypress' );
				foreach ( $errors as $e ) {
					if ( $e == $bp_msg ) {
						if ( $this->has_exceeded_limit( $result['user_email'] ) ) {
							// Only indicate "Too many accounts" if the account was allowed more than one. Otherwise use BP default.
							if ( $this->has_multiple_accounts( $result['user_email'] ) )
								$e = __( 'Too many accounts are associated with this email, please choose another one.', $this->textdomain );
							else
								$e = $bp_msg;
						} else {
							$e = null;
						}
					}
					if ( $e )
						$new_errors[] = $e;
				}
				if ( ! empty( $new_errors ) )
					$result['errors']->errors['user_email'] = $new_errors;
				else
					unset( $result['errors']->errors['user_email'] );
			}
		}
		return $result;
	}

	/**
	 * Generate HTML for a single row on the users.php admin panel.
	 *
	 * Slightly adapted version of function last seen in WP 3.0.6
	 *
	 * @since 2.5
	 * (since WP 2.1)
	 *
	 * @param object $user_object
	 * @param string $style Optional. Attributes added to the TR element.  Must be sanitized.
	 * @param string $role Key for the $wp_roles array.
	 * @param int $numposts Optional. Post count to display for this user.  Defaults to zero, as in, a new user has made zero posts.
	 * @return string
	 */
	public function user_row( $user_object, $style = '', $role = '', $numposts = 0 ) {
		global $wp_roles;

		if ( !( is_object( $user_object) && is_a( $user_object, 'WP_User' ) ) )
			$user_object = new WP_User( (int) $user_object );
		if ( property_exists( $user_object, 'filter' ) )
			$user_object->filter = 'display';
		else // pre-WP 3.3
			$user_object = sanitize_user_object($user_object, 'display');
		$email = $user_object->user_email;
		$url = $user_object->user_url;
		$short_url = str_replace( 'http://', '', $url );
		$short_url = str_replace( 'www.', '', $short_url );
		if ('/' == substr( $short_url, -1 ))
			$short_url = substr( $short_url, 0, -1 );
		if ( strlen( $short_url ) > 35 )
			$short_url = substr( $short_url, 0, 32 ).'...';
		$checkbox = '';
		// Check if the user for this row is editable
		if ( current_user_can( 'list_users' ) ) {
			// Set up the user editing link
			// TODO: make profile/user-edit determination a separate function
			if ( get_current_user_id() == $user_object->ID) {
				$edit_link = 'profile.php';
			} else {
				$edit_link = esc_url( add_query_arg( 'wp_http_referer', urlencode( esc_url( stripslashes( $_SERVER['REQUEST_URI'] ) ) ), "user-edit.php?user_id=$user_object->ID" ) );
			}
			$edit = "<strong><a href=\"$edit_link\">$user_object->user_login</a></strong><br />";

			// Set up the hover actions for this user
			$actions = array();

			if ( current_user_can('edit_user',  $user_object->ID) ) {
				$edit = "<strong><a href=\"$edit_link\">$user_object->user_login</a></strong><br />";
				$actions['edit'] = '<a href="' . $edit_link . '">' . __('Edit') . '</a>';
			} else {
				$edit = "<strong>$user_object->user_login</strong><br />";
			}

			if ( !is_multisite() && get_current_user_id() != $user_object->ID && current_user_can('delete_user', $user_object->ID) )
				$actions['delete'] = "<a class='submitdelete' href='" . wp_nonce_url("users.php?action=delete&amp;user=$user_object->ID", 'bulk-users') . "'>" . __('Delete') . "</a>";
			if ( is_multisite() && get_current_user_id() != $user_object->ID && current_user_can('remove_user', $user_object->ID) )
				$actions['remove'] = "<a class='submitdelete' href='" . wp_nonce_url("users.php?action=remove&amp;user=$user_object->ID", 'bulk-users') . "'>" . __('Remove') . "</a>";
			$actions = apply_filters('user_row_actions', $actions, $user_object);
			$action_count = count($actions);
			$i = 0;
			$edit .= '<div class="row-actions">';
			foreach ( $actions as $action => $link ) {
				++$i;
				( $i == $action_count ) ? $sep = '' : $sep = ' | ';
				$edit .= "<span class='$action'>$link$sep</span>";
			}
			$edit .= '</div>';

			// Set up the checkbox (because the user is editable, otherwise its empty)
			$checkbox = "<input type='checkbox' name='users[]' id='user_{$user_object->ID}' class='$role' value='{$user_object->ID}' />";

		} else {
			$edit = '<strong>' . $user_object->user_login . '</strong>';
		}
		$role_name = isset($wp_roles->role_names[$role]) ? translate_user_role($wp_roles->role_names[$role] ) : __('None');
		$r = "<tr id='user-$user_object->ID'$style>";

		$columns = array(
			'cb' => '<input type="checkbox" />',
			'username' => __('Username'),
			'name' => __('Name'),
			'email' => __('E-mail'),
			'role' => __('Role')
		);

		$avatar = get_avatar( $user_object->ID, 32 );
		foreach ( $columns as $column_name => $column_display_name ) {
			$attributes = "class=\"$column_name column-$column_name\"";

			switch ($column_name) {
				case 'cb':
					$r .= "<th scope='row' class='check-column'>$checkbox</th>";
					break;
				case 'username':
					$r .= "<td $attributes>$avatar $edit</td>";
					break;
				case 'name':
					$r .= "<td $attributes>$user_object->first_name $user_object->last_name</td>";
					break;
				case 'email':
					$r .= "<td $attributes><a href='mailto:$email' title='" . sprintf( __('E-mail: %s' ), $email ) . "'>$email</a></td>";
					break;
				case 'role':
					$r .= "<td $attributes>$role_name</td>";
					break;
				case 'posts':
					$attributes = 'class="posts column-posts num"' . $style;
					$r .= "<td $attributes>";
					if ( $numposts > 0 ) {
						$r .= "<a href='edit.php?author=$user_object->ID' title='" . __( 'View posts by this author' ) . "' class='edit'>";
						$r .= $numposts;
						$r .= '</a>';
					} else {
						$r .= 0;
					}
					$r .= "</td>";
					break;
				default:
					$r .= "<td $attributes>";
					$r .= apply_filters('manage_users_custom_column', '', $column_name, $user_object->ID);
					$r .= "</td>";
			}
		}
		$r .= '</tr>';

		return $r;
	}

} // end c2c_AllowMultipleAccounts

// To access plugin object instance use: c2c_AllowMultipleAccounts::$instance
new c2c_AllowMultipleAccounts();

endif; // end if !class_exists()


	//
	/**
	 * *******************
	 * TEMPLATE FUNCTIONS
	 *
	 * Functions suitable for use in other themes and plugins
	 * *******************
	 */

	/**
	 * Returns a count of the number of users associated with the given email.
	 *
	 * @since 2.0
	 *
	 * @param string $email The email account
	 * @return int The number of users associated with the given email
	 */
	if ( ! function_exists( 'c2c_count_multiple_accounts' ) ) {
		function c2c_count_multiple_accounts( $email ) {
			return c2c_AllowMultipleAccounts::$instance->count_multiple_accounts( $email );
		}
		add_action( 'c2c_count_multiple_accounts', 'c2c_count_multiple_accounts' );
	}

	/**
	 * Returns the users associated with the given email.
	 *
	 * @since 2.0
	 *
	 * @param string $email The email account
	 * @return array All of the users associated with the given email
	 */
	if ( ! function_exists( 'c2c_get_users_by_email' ) ) {
		function c2c_get_users_by_email( $email ) {
			return c2c_AllowMultipleAccounts::$instance->get_users_by_email( $email );
		}
		add_action( 'c2c_get_users_by_email', 'c2c_get_users_by_email' );
	}

	/**
	 * Returns a boolean indicating if the given email is associated with more than one user account.
	 *
	 * @since 2.0
	 *
	 * @param string $email The email account
	 * @return bool True if the given email is associated with more than one user account; false otherwise
	 */
	if ( ! function_exists( 'c2c_has_multiple_accounts' ) ) {
		function c2c_has_multiple_accounts( $email ) {
			return c2c_AllowMultipleAccounts::$instance->has_multiple_accounts( $email );
		}
		add_action( 'c2c_has_multiple_accounts', 'c2c_has_multiple_accounts' );
	}

	/**
	 * This is only overridden as part of a HACK solution to a bug in WP 3.0 not allowing suppression of the duplicate email check.
	 *
	 * NO LONGER NEEDED ONCE SUPPORT FOR VERSIONS OF WP EARLIER THAN 3.3 IS DROPPED
	 *
	 * What it does: Replaces WP's get_user_by_email(). If during the user creation process (hackily determined by the plugin's instance)
	 * AND the email has not exceeded the account limit, then return false.  wp_insert_user() calls this function simply to check if the
	 * email is already associated with an account.  So in that instance, if we know that's where the request is originating and that the
	 * email in question is allowed to have multiple accounts, then trick the check into thinking the email isn't in use so that an error
	 * isn't generated.
	 *
	 * @since 2.0
	 *
	 * @param string $email User email
	 * @return string User associated with the email
	 */
	if ( version_compare( $GLOBALS['wp_version'], '3.3', '<' ) && ! function_exists( 'get_user_by_email' ) ) {
		c2c_AllowMultipleAccounts::$controls_get_user_by = true;
		function get_user_by_email( $email ) {
			$obj = c2c_AllowMultipleAccounts::$instance;
			if ( $obj->during_user_creation && ! $obj->has_exceeded_limit( $email ) )
				return false;
			return get_user_by( 'email', $email );
		}
	}

	/**
	 * This is only overridden as part of a HACK solution to a bug in WP 3.0 not allowing suppression of the duplicate email check.
	 *
	 * What it does: Replaces WP's get_user_by(). If during the user creation process (hackily determined by the plugin's instance)
	 * AND the email has not exceeded the account limit, then return false.  wp_insert_user() calls this function simply to check if the
	 * email is already associated with an account.  So in that instance, if we know that's where the request is originating and that the
	 * email in question is allowed to have multiple accounts, then trick the check into thinking the email isn't in use so that an error
	 * isn't generated.
	 *
	 * @since 2.6
	 *   (based on version from WP 3.3)
	 *
	 * @param string $email User email
	 * @return string User associated with the email
	 */
	if ( version_compare( $GLOBALS['wp_version'], '3.2.99', '>' ) &&! function_exists( 'get_user_by' ) ) {
		c2c_AllowMultipleAccounts::$controls_get_user_by = true;
		function get_user_by( $field, $value ) {
			$obj = c2c_AllowMultipleAccounts::$instance;

			if ( 'email' == $field && $obj->during_user_creation && ! $obj->has_exceeded_limit( $value ) )
				return false;

			$userdata = WP_User::get_data_by( $field, $value );

			if ( !$userdata )
				return false;

			$user = new WP_User;
			$user->init( $userdata );

			return $user;
		}
	}

	/**
	 * *******************
	 * DEPRECATED FUNCTIONS
	 * *******************
	 */
	// To be removed in v3.0 of plugin
	if ( ! function_exists( 'count_multiple_accounts' ) ) {
		function count_multiple_accounts( $email ) {
			_deprecated_function( __FUNCTION__, '2.0', 'c2c_count_multiple_accounts' );
			return c2c_count_multiple_accounts( $email );
		}
	}
	// To be removed in v3.0 of plugin
	if ( ! function_exists( 'get_users_by_email' ) ) {
		function get_users_by_email( $email ) {
			_deprecated_function( __FUNCTION__, '2.0', 'c2c_get_users_by_email' );
			return c2c_get_users_by_email( $email );
		}
	}
	// To be removed in v3.0 of plugin
	if ( ! function_exists( 'has_multiple_accounts' ) ) {
		function has_multiple_accounts( $email ) {
			_deprecated_function( __FUNCTION__, '2.0', 'c2c_has_multiple_accounts' );
			return c2c_has_multiple_accounts( $email );
		}
	}

?>