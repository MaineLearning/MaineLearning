<?php
/**
 * Handles Genesis updates.
 *
 * @category Genesis
 * @package  Updates
 * @author   StudioPress
 * @license  http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link     http://www.studiopress.com/themes/genesis
 */

/**
 * Pings http://api.genesistheme.com/ asking if a new version of this theme is
 * available.
 *
 * If not, it returns false.
 *
 * If so, the external server passes serialized data back to this function,
 * which gets unserialized and returned for use.
 *
 * @since 1.1.0
 *
 * @uses genesis_get_option()
 * @uses PARENT_THEME_VERSION Genesis version string
 *
 * @global string $wp_version WordPress version string
 * @return mixed Unserialized data, or false on failure
 */
function genesis_update_check() {

	global $wp_version;

	/**	If updates are disabled */
	if ( ! genesis_get_option( 'update' ) || ! current_theme_supports( 'genesis-auto-updates' ) )
		return false;

	/** Get time of last update check */
	$genesis_update = get_transient( 'genesis-update' );

	/** If it has expired, do an update check */
	if ( ! $genesis_update ) {
		$url     = 'http://api.genesistheme.com/update-themes/';
		$options = apply_filters(
			'genesis_update_remote_post_options',
			array(
				'body' => array(
					'genesis_version' => PARENT_THEME_VERSION,
					'wp_version'      => $wp_version,
					'php_version'     => phpversion(),
					'uri'             => home_url(),
					'user-agent'      => "WordPress/$wp_version;",
				),
			)
		);

		$response = wp_remote_post( $url, $options );
		$genesis_update = wp_remote_retrieve_body( $response );

		/** If an error occurred, return FALSE, store for 1 hour */
		if ( 'error' == $genesis_update || is_wp_error( $genesis_update ) || ! is_serialized( $genesis_update ) ) {
			set_transient( 'genesis-update', array( 'new_version' => PARENT_THEME_VERSION ), 60 * 60 );
			return false;
		}

		/** Else, unserialize */
		$genesis_update = maybe_unserialize( $genesis_update );

		/** And store in transient for 24 hours */
		set_transient( 'genesis-update', $genesis_update, 60 * 60 * 24 );
	}

	/** If we're already using the latest version, return false */
	if ( version_compare( PARENT_THEME_VERSION, $genesis_update['new_version'], '>=' ) )
		return false;

	return $genesis_update;

}

/**
 * Upgrade the database to version 1802.
 *
 * @since 1.8.0
 *
 * @uses _genesis_update_settings()
 */
function genesis_upgrade_1804() {

	/** Update Settings */
	_genesis_update_settings(
		array(
			'theme_version' => '1.8.2',
			'db_version'    => '1804',
		)
	);

}

/**
 * Upgrade the database to version 1800.
 *
 * @since 1.8.0
 *
 * @uses _genesis_update_settings()
 */
function genesis_upgrade_1800() {

	/** Convert term meta for new title/description options */
	$terms     = get_terms( get_taxonomies(), array( 'hide_empty' => false ) );
	$term_meta = get_option( 'genesis-term-meta' );

	foreach ( (array) $terms as $term ) {
		if ( isset( $term_meta[$term->term_id]['display_title'] ) && $term_meta[$term->term_id]['display_title'] )
			$term_meta[$term->term_id]['headline'] = $term->name;

		if ( isset( $term_meta[$term->term_id]['display_description'] ) && $term_meta[$term->term_id]['display_description'] )
			$term_meta[$term->term_id]['intro_text'] = $term->description;
	}

	update_option( 'genesis-term-meta', $term_meta );

	/** Update Settings */
	_genesis_update_settings(
		array(
			'db_version'    => '1800',
		)
	);

}

/**
 * Upgrade the database to version 1702.
 *
 * @since 1.7.0
 *
 * @uses _genesis_update_settings()
 */
function genesis_upgrade_1702() {

	/** Update Settings */
	_genesis_update_settings(
		array(
			'theme_version' => '1.7',
			'db_version'    => '1702',
		)
	);

}

/**
 * Upgrade the database to version 1700.
 *
 * Also removes old user meta box options, as the UI changed.
 *
 * @since 1.7.0
 *
 * @uses _genesis_update_settings()
 *
 * @global object $wpdb WordPress database object
 */
function genesis_upgrade_1700() {

	global $wpdb;

	/** Changing the UI. Remove old user options. */
	$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->usermeta WHERE meta_key = %s OR meta_key = %s", 'meta-box-order_toplevel_page_genesis', 'meta-box-order_genesis_page_seosettings' ) );
	$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->usermeta SET meta_value = %s WHERE meta_key = %s OR meta_key = %s", '1', 'screen_layout_toplevel_page_genesis', 'screen_layout_genesis_page_seosettings' ) );

	/** Update Settings */
	_genesis_update_settings(
		array(
			'theme_version' => '1.7-alpha-1700',
			'db_version'    => '1700',
		)
	);

}

add_action( 'admin_init', 'genesis_upgrade', 20 );
/**
 * Update Genesis to the latest version.
 *
 * This iterative update function will take a Genesis installation, no matter
 * how old, and update its options to the latest version.
 *
 * It used to iterate over theme version, but now uses a database version
 * system, which allows for changes within pre-releases, too.
 *
 * @since 1.0.1
 *
 * @uses _genesis_vestige()
 * @uses genesis_get_option()
 * @uses genesis_get_seo_option()
 * @uses genesis_upgrade_1700()
 * @uses genesis_upgrade_1702()
 * @uses PARENT_DB_VERSION
 * @uses GENESIS_SETTINGS_FIELD
 * @uses GENESIS_SEO_SETTINGS_FIELD
 *
 * @return null Returns early if we're already on the latest version.
 */
function genesis_upgrade() {

	/** Don't do anything if we're on the latest version */
	if ( genesis_get_option( 'db_version', null, false ) >= PARENT_DB_VERSION )
		return;

	#########################
	# UPDATE TO VERSION 1.0.1
	#########################

	if ( version_compare( genesis_get_option( 'theme_version', null, false ), '1.0.1', '<' ) ) {
		$theme_settings = get_option( GENESIS_SETTINGS_FIELD );
		$new_settings   = array(
			'nav_home'         => 1,
			'nav_twitter_text' => 'Follow me on Twitter',
			'subnav_home'      => 1,
			'theme_version'    => '1.0.1',
		);

		$settings = wp_parse_args( $new_settings, $theme_settings );
		update_option( GENESIS_SETTINGS_FIELD, $settings );
	}

	#########################
	# UPDATE TO VERSION 1.1
	#########################

	if ( version_compare( genesis_get_option( 'theme_version', null, false ), '1.1', '<' ) ) {
		$theme_settings = get_option( GENESIS_SETTINGS_FIELD );
		$new_settings   = array(
			'content_archive_thumbnail' => genesis_get_option( 'thumbnail' ),
			'theme_version'             => '1.1',
		);

		$settings = wp_parse_args( $new_settings, $theme_settings );
		update_option( GENESIS_SETTINGS_FIELD, $settings );
	}

	#########################
	# UPDATE TO VERSION 1.1.2
	#########################

	if ( version_compare( genesis_get_option( 'theme_version', null, false ), '1.1.2', '<' ) ) {
		$theme_settings = get_option( GENESIS_SETTINGS_FIELD );
		$new_settings   = array(
			'header_right'            => genesis_get_option( 'header_full' ) ? 0 : 1,
			'nav_superfish'           => 1,
			'subnav_superfish'        => 1,
			'nav_extras_enable'       => genesis_get_option( 'nav_right' ) ? 1 : 0,
			'nav_extras'              => genesis_get_option( 'nav_right' ),
			'nav_extras_twitter_id'   => genesis_get_option( 'twitter_id' ),
			'nav_extras_twitter_text' => genesis_get_option( 'nav_twitter_text' ),
			'theme_version'           => '1.1.2',
		);

		$settings = wp_parse_args( $new_settings, $theme_settings );
		update_option( GENESIS_SETTINGS_FIELD, $settings );
	}

	#########################
	# UPDATE TO VERSION 1.2
	#########################

	if ( version_compare( genesis_get_option( 'theme_version', null, false ), '1.2', '<' ) ) {
		$theme_settings = get_option( GENESIS_SETTINGS_FIELD );
		$new_settings   = array(
			'update'        => 1,
			'theme_version' => '1.2',
		);

		$settings = wp_parse_args( $new_settings, $theme_settings );
		update_option( GENESIS_SETTINGS_FIELD, $settings );
	}

	#########################
	# UPDATE TO VERSION 1.3
	#########################

	if ( version_compare( genesis_get_option( 'theme_version', null, false ), '1.3', '<' ) ) {
		/** Update theme settings */
		$theme_settings = get_option( GENESIS_SETTINGS_FIELD );
		$new_settings   = array(
			'author_box_single' => genesis_get_option( 'author_box' ),
			'theme_version'     => '1.3',
		);

		$settings = wp_parse_args( $new_settings, $theme_settings );
		update_option( GENESIS_SETTINGS_FIELD, $settings );

		/**	Update SEO settings */
		$seo_settings = get_option( GENESIS_SEO_SETTINGS_FIELD );
		$new_settings = array(
			'noindex_cat_archive'    => genesis_get_seo_option( 'index_cat_archive' ) ? 0 : 1,
			'noindex_tag_archive'    => genesis_get_seo_option( 'index_tag_archive' ) ? 0 : 1,
			'noindex_author_archive' => genesis_get_seo_option( 'index_author_archive' ) ? 0 : 1,
			'noindex_date_archive'   => genesis_get_seo_option( 'index_date_archive' ) ? 0 : 1,
			'noindex_search_archive' => genesis_get_seo_option( 'index_search_archive' ) ? 0 : 1,
			'noodp'                  => 1,
			'noydir'                 => 1,
			'canonical_archives'     => 1,
		);

		$settings = wp_parse_args( $new_settings, $seo_settings );
		update_option( GENESIS_SEO_SETTINGS_FIELD, $settings );

		/** Delete the store transient, force refresh */
		delete_transient( 'genesis-remote-store' );
	}

	#########################
	# UPDATE TO VERSION 1.6
	#########################

	if ( version_compare( genesis_get_option( 'theme_version', null, false ), '1.6', '<' ) ) {
		/** Vestige nav settings, for backward compatibility */
		if ( 'nav-menu' != genesis_get_option( 'nav_type' ) )
			_genesis_vestige( array( 'nav_type', 'nav_superfish', 'nav_home', 'nav_pages_sort', 'nav_categories_sort', 'nav_depth', 'nav_exclude', 'nav_include', ) );

		/** Vestige subnav settings, for backward compatibility */
		if ( 'nav-menu' != genesis_get_option( 'subnav_type' ) )
			_genesis_vestige( array( 'subnav_type', 'subnav_superfish', 'subnav_home', 'subnav_pages_sort', 'subnav_categories_sort', 'subnav_depth', 'subnav_exclude', 'subnav_include', ) );

		$theme_settings = get_option( GENESIS_SETTINGS_FIELD );
		$new_settings   = array( 'theme_version' => '1.6', );

		$settings = wp_parse_args( $new_settings, $theme_settings );
		update_option( GENESIS_SETTINGS_FIELD, $settings );
	}

	###########################
	# UPDATE DB TO VERSION 1700
	###########################

	if ( genesis_get_option( 'db_version', null, false ) < '1700' )
		genesis_upgrade_1700();

	###########################
	# UPDATE DB TO VERSION 1702
	###########################

	if ( genesis_get_option( 'db_version', null, false ) < '1702' )
		genesis_upgrade_1702();

	###########################
	# UPDATE DB TO VERSION 1800
	###########################

	if ( genesis_get_option( 'db_version', null, false ) < '1800' )
		genesis_upgrade_1800();
		
	###########################
	# UPDATE DB TO VERSION 1804
	###########################

	if ( genesis_get_option( 'db_version', null, false ) < '1804' )
		genesis_upgrade_1804();

	do_action( 'genesis_upgrade' );

}

add_action( 'genesis_upgrade', 'genesis_upgrade_redirect' );
/**
 * Redirects the user back to the theme settings page, refreshing the data and
 * notifying the user that they have successfully updated.
 *
 * @since 1.6.0
 *
 * @uses genesis_admin_redirect()
 *
 * @return null Returns early if not an admin page.
 */
function genesis_upgrade_redirect() {

	if ( ! is_admin() )
		return;

	genesis_admin_redirect( 'genesis', array( 'upgraded' => 'true' ) );
	exit;

}

add_action( 'admin_notices', 'genesis_upgraded_notice' );
/**
 * Displays the notice that the theme settings were successfully updated to the
 * latest version.
 *
 * @since 1.2.0
 *
 * @uses genesis_get_option()
 *
 * @return null Returns early if not on the Theme Settings page.
 */
function genesis_upgraded_notice() {

	if ( ! genesis_is_menu_page( 'genesis' ) )
		return;

	if ( isset( $_REQUEST['upgraded'] ) && 'true' == $_REQUEST['upgraded'] )
		echo '<div id="message" class="updated highlight" id="message"><p><strong>' . sprintf( __( 'Congratulations! You are now rocking Genesis %s', 'genesis' ), genesis_get_option( 'theme_version' ) ) . '</strong></p></div>';

}

add_filter( 'update_theme_complete_actions', 'genesis_update_action_links', 10, 2 );
/**
 * Filters the action links at the end of an update.
 *
 * This function filters the action links that are presented to the
 * user at the end of a theme update. If the theme being updated is
 * not Genesis, the filter returns the default values. Otherwise,
 * it will provide a link to the Genesis Theme Settings page, which
 * will trigger the database/settings upgrade.
 *
 * @since 1.1.3
 *
 * @param array $actions Existing array of action links
 * @param string $theme Theme name
 * @return string Removes all existing action links in favour of a single link.
 */
function genesis_update_action_links( $actions, $theme ) {

	if ( 'genesis' != $theme )
		return $actions;

	return sprintf( '<a href="%s">%s</a>', menu_page_url( 'genesis', 0 ), __( 'Click here to complete the upgrade', 'genesis' ) );

}

add_action( 'admin_notices', 'genesis_update_nag' );
/**
 * Displays the update nag at the top of the dashboard if there is a Genesis
 * update available.
 *
 * @since 1.1.0
 *
 * @uses genesis_update_check()
 *
 * @return boolean Returns false if there is no available update, or user is not
 * a site administrator.
 */
function genesis_update_nag() {

	$genesis_update = genesis_update_check();

	if ( ! is_super_admin() || ! $genesis_update )
		return false;

	echo '<div id="update-nag">';
	printf(
		__( 'Genesis %s is available. <a href="%s" class="thickbox thickbox-preview">Check out what\'s new</a> or <a href="%s" onclick="return genesis_confirm(\'%s\');">update now</a>.', 'genesis' ),
		esc_html( $genesis_update['new_version'] ),
		esc_url( $genesis_update['changelog_url'] ),
		wp_nonce_url( 'update.php?action=upgrade-theme&amp;theme=genesis', 'upgrade-theme_genesis' ),
		esc_js( __( 'Upgrading Genesis will overwrite the current installed version of Genesis. Are you sure you want to upgrade?. "Cancel" to stop, "OK" to upgrade.', 'genesis' ) )
	);
	echo '</div>';

}

add_action( 'init', 'genesis_update_email' );
/**
 * Sends out update notification email.
 *
 * Does several checks before finally sending out a notification email to the
 * specified email address, alerting it to a Genesis update available for that install.
 *
 * @since 1.1.0
 *
 * @uses genesis_get_option()
 * @uses genesis_update_check()
 *
 * @return null Returns null if email should not be sent.
 */
function genesis_update_email() {

	/** Pull email options from DB */
	$email_on = genesis_get_option( 'update_email' );
	$email    = genesis_get_option( 'update_email_address' );

	/** If we're not supposed to send an email, or email is blank / invalid, stop! */
	if ( ! $email_on || ! is_email( $email ) )
		return;

	/** Check for updates */
	$update_check = genesis_update_check();

	/** If no new version is available, stop! */
	if ( ! $update_check )
		return;

	/** If we've already sent an email for this version, stop! */
	if ( get_option( 'genesis-update-email' ) == $update_check['new_version'] )
		return;

	/** Let's send an email! */
	$subject  = sprintf( __( 'Genesis %s is available for %s', 'genesis' ), esc_html( $update_check['new_version'] ), home_url() );
	$message  = sprintf( __( 'Genesis %s is now available. We have provided 1-click updates for this theme, so please log into your dashboard and update at your earliest convenience.', 'genesis' ), esc_html( $update_check['new_version'] ) );
	$message .= "\n\n" . wp_login_url();

	/** Update the option so we don't send emails on every pageload! */
	update_option( 'genesis-update-email', $update_check['new_version'], TRUE );

	/** Send that puppy! */
	wp_mail( sanitize_email( $email ), $subject, $message );

}

add_filter( 'site_transient_update_themes', 'genesis_update_push' );
add_filter( 'transient_update_themes', 'genesis_update_push' );
/**
 * Integrate the Genesis update check into the WordPress update checks.
 *
 * This function filters the value that is returned when WordPress tries to pull
 * theme update transient data.
 *
 * It uses genesis_update_check() to check to see if we need to do an update,
 * and if so, adds the proper array to the $value->response object. WordPress
 * handles the rest.
 *
 * @since 1.1.0
 *
 * @uses genesis_update_check()
 *
 * @param object $value
 * @return object
 */
function genesis_update_push( $value ) {

	$genesis_update = genesis_update_check();

	if ( $genesis_update )
		$value->response['genesis'] = $genesis_update;

	return $value;

}

add_action( 'load-update-core.php', 'genesis_clear_update_transient' );
add_action( 'load-themes.php', 'genesis_clear_update_transient' );
/**
 * Delete Genesis update transient after updates or when viewing the themes page.
 *
 * The server will then do a fresh version check.
 *
 * It also disables the update nag on those pages as well.
 *
 * @since 1.1.0
 *
 * @see genesis_update_nag()
 */
function genesis_clear_update_transient() {

	delete_transient( 'genesis-update' );
	remove_action( 'admin_notices', 'genesis_update_nag' );

}

/**
 * Converts array of keys from Genesis options to vestigial options.
 *
 * This is done for backwards compatibility.
 *
 * @since 1.6.0
 *
 * @access private
 *
 * @param array $keys Array of keys to convert. Default is an empty array.
 * @param string $setting Optional. The settings field the original keys are
 * found under. Default is GENESIS_SETTINGS_FIELD.
 * @return null Returns null on failure.
 */
function _genesis_vestige( $keys = array(), $setting = GENESIS_SETTINGS_FIELD ) {

	/** If no $keys passed, do nothing */
	if ( ! $keys )
		return;

	/** Pull options */
	$options = get_option( $setting );
	$vestige = get_option( 'genesis-vestige' );

	/** Cycle through $keys, creating new vestige array */
	$new_vestige = array();
	foreach ( (array) $keys as $key ) {
		if ( isset( $options[$key] ) ) {
			$new_vestige[$key] = $options[$key];
			unset( $options[$key] );
		}
	}

	/** If no new vestigial options being pushed, do nothing */
	if ( ! $new_vestige )
		return;

	/** Merge the arrays, if necessary */
	$vestige = $vestige ? wp_parse_args( $new_vestige, $vestige ) : $new_vestige;

	/** Insert into options table */
	update_option( 'genesis-vestige', $vestige );
	update_option( $setting, $options );

}