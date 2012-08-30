<?php
/**
 * Creates the Theme Settings page.
 *
 * Also contains functions used across all aspects of admin.
 *
 * @category Genesis
 * @package  Admin
 * @author   StudioPress
 * @license  http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link     http://www.studiopress.com/themes/genesis
 */

/**
 * Registers a new admin page, providing content and corresponding menu item
 * for the Theme Settings page.
 *
 * Although this class was added in 1.8.0, some of the methods were originally
 * standalone functions added in previous versions of Genesis.
 *
 * @category Genesis
 * @package Admin
 *
 * @since 1.8.0
 */
class Genesis_Admin_Settings extends Genesis_Admin_Boxes {

	/**
	 * Create an admin menu item and settings page.
	 *
	 * @since 1.8.0
	 *
	 * @uses GENESIS_SETTINGS_FIELD settings field key
	 * @uses genesis_get_default_layout() Get default layout
	 *
	 * @global string $_genesis_theme_settings_pagehook Theme Settings page hook,
	 * kept for backwards compatibility, since this class now uses $this->pagehook.
	 */
	function __construct() {

		$page_id = 'genesis';

		$menu_ops = apply_filters(
			'genesis_theme_settings_menu_ops',
			array(
				'main_menu' => array(
					'sep' => array(
						'sep_position'   => '58.995',
						'sep_capability' => 'edit_theme_options',
					),
					'page_title' => 'Theme Settings',
					'menu_title' => 'Genesis',
					'capability' => 'edit_theme_options',
					'icon_url'   => PARENT_URL . '/images/genesis.gif',
					'position'   => '58.996',
				),
				'first_submenu' => array( /** Do not use without 'main_menu' */
					'page_title' => __( 'Theme Settings', 'genesis' ),
					'menu_title' => __( 'Theme Settings', 'genesis' ),
					'capability' => 'edit_theme_options',
				),
			)
		);

		$page_ops = apply_filters(
			'genesis_theme_settings_page_ops',
			array(
				'screen_icon'       => 'options-general',
				'save_button_text'  => __( 'Save Settings', 'genesis' ),
				'reset_button_text' => __( 'Reset Settings', 'genesis' ),
				'saved_notice_text' => __( 'Settings saved.', 'genesis' ),
				'reset_notice_text' => __( 'Settings reset.', 'genesis' ),
				'error_notice_text' => __( 'Error saving settings.', 'genesis' ),
			)
		);

		$settings_field = GENESIS_SETTINGS_FIELD;

		$default_settings = apply_filters(
			'genesis_theme_settings_defaults',
			array(
				'update'                    => 1,
				'blog_title'                => 'text',
				'header_right'              => 0,
				'site_layout'               => genesis_get_default_layout(),
				'nav'                       => 1,
				'nav_superfish'             => 1,
				'nav_extras_enable'         => 0,
				'nav_extras'                => 'date',
				'nav_extras_twitter_id'     => '',
				'nav_extras_twitter_text'   => __( 'Follow me on Twitter', 'genesis' ),
				'subnav'                    => 0,
				'subnav_superfish'          => 1,
				'feed_uri'                  => '',
				'comments_feed_uri'         => '',
				'redirect_feeds'            => 0,
				'comments_pages'            => 0,
				'comments_posts'            => 1,
				'trackbacks_pages'          => 0,
				'trackbacks_posts'          => 1,
				'breadcrumb_home'           => 0,
				'breadcrumb_single'         => 0,
				'breadcrumb_page'           => 0,
				'breadcrumb_archive'        => 0,
				'breadcrumb_404'            => 0,
				'breadcrumb_attachment'		=> 0,
				'content_archive'           => 'full',
				'content_archive_thumbnail' => 0,
				'posts_nav'                 => 'older-newer',
				'blog_cat'                  => '',
				'blog_cat_exclude'          => '',
				'blog_cat_num'              => 10,
				'header_scripts'            => '',
				'footer_scripts'            => '',
				'theme_version'             => PARENT_THEME_VERSION,
				'db_version'                => PARENT_DB_VERSION,
			)
		);

		$this->create( $page_id, $menu_ops, $page_ops, $settings_field, $default_settings );

		add_action( 'genesis_settings_sanitizer_init', array( $this, 'sanitizer_filters' ) );

	}

	/**
	 * Registers each of the settings with a sanitization filter type.
	 *
	 * @since 1.7.0
	 *
	 * @uses genesis_add_option_filter() Assign filter to array of settings
	 *
	 * @see Genesis_Settings_Sanitizer::add_filter()
	 */
	public function sanitizer_filters() {

		genesis_add_option_filter(
			'one_zero',
			$this->settings_field,
			array(
				'show_info',
				'update',
				'update_email',
				'redirect_feed',
				'redirect_comments_feed',
				'nav',
				'nav_superfish',
				'nav_extras_enable',
				'subnav',
				'subnav_superfish',
				'breadcrumb_home',
				'breadcrumb_single',
				'breadcrumb_page',
				'breadcrumb_archive',
				'breadcrumb_404',
				'breadcrumb_attachment',
				'comments_posts',
				'comments_pages',
				'trackbacks_posts',
				'trackbacks_pages',
				'content_archive_thumbnail',
			)
		);

		genesis_add_option_filter(
			'no_html',
			$this->settings_field,
			array( 'style_selection', )
		);

		genesis_add_option_filter(
			'requires_unfiltered_html',
			$this->settings_field,
			array(
				'header_scripts',
				'footer_scripts',
			)
		);

	}

	/**
 	 * Register meta boxes on the Theme Settings page.
 	 *
 	 * Some of the meta box additions are dependent on certain theme support or user
 	 * capabilities.
 	 *
 	 * The 'genesis_theme_settings_metaboxes' action hook is called at the end of
 	 * this function.
 	 *
 	 * @since 1.0.0
 	 *
 	 * @see Genesis_Admin_Settings::info_box() Callback for Information box
 	 * @see Genesis_Admin_Settings::style_box() Callback for Color Style box (if supported)
 	 * @see Genesis_Admin_Settings::feeds_box() Callback for Custom Feeds box
 	 * @see Genesis_Admin_Settings::layout_box() Callback for Default Layout box
 	 * @see Genesis_Admin_Settings::header_box() Callback for Header Settings box (if no custom header support)
	 * @see Genesis_Admin_Settings::nav_box() Callback for Navigation Settings box
 	 * @see Genesis_Admin_Settings::breadcrumb_box() Callback for Breadcrumbs box
 	 * @see Genesis_Admin_Settings::comments_box() Callback for Comments and Trackbacks box
 	 * @see Genesis_Admin_Settings::post_archives_box() Callback for Content Archives box
 	 * @see Genesis_Admin_Settings::blogpage_box() Callback for Blog Page box
 	 * @see Genesis_Admin_Settings::scripts_box() Callback for Header and Footer box (if user has unfiltered_html capability)
 	 */
	function metaboxes() {

		/** Hidden form fields */
		add_action( 'genesis_admin_before_metaboxes', array( $this, 'hidden_fields' ) );

		add_meta_box( 'genesis-theme-settings-version', __( 'Information', 'genesis' ), array( $this, 'info_box' ), $this->pagehook, 'main', 'high' );

		if ( current_theme_supports( 'genesis-style-selector' ) )
			add_meta_box( 'genesis-theme-settings-style-selector', __( 'Color Style', 'genesis' ), array( $this, 'style_box' ), $this->pagehook, 'main' );

		add_meta_box( 'genesis-theme-settings-feeds', __( 'Custom Feeds', 'genesis' ), array( $this, 'feeds_box' ), $this->pagehook, 'main' );
		add_meta_box( 'genesis-theme-settings-layout', __( 'Default Layout', 'genesis' ), array( $this, 'layout_box' ), $this->pagehook, 'main' );

		if ( ! current_theme_supports( 'genesis-custom-header' ) && ! current_theme_supports( 'custom-header' ) )
			add_meta_box( 'genesis-theme-settings-header', __( 'Header Settings', 'genesis' ), array( $this, 'header_box' ), $this->pagehook, 'main' );

		if ( current_theme_supports( 'genesis-menus' ) )
			add_meta_box( 'genesis-theme-settings-nav', __( 'Navigation Settings', 'genesis' ), array( $this, 'nav_box' ), $this->pagehook, 'main' );

		add_meta_box( 'genesis-theme-settings-breadcrumb', __( 'Breadcrumbs', 'genesis' ), array( $this, 'breadcrumb_box' ), $this->pagehook, 'main' );
		add_meta_box( 'genesis-theme-settings-comments', __( 'Comments and Trackbacks', 'genesis' ), array( $this, 'comments_box' ), $this->pagehook, 'main' );
		add_meta_box( 'genesis-theme-settings-posts', __( 'Content Archives', 'genesis' ), array( $this, 'post_archives_box' ), $this->pagehook, 'main' );
		add_meta_box( 'genesis-theme-settings-blogpage', __( 'Blog Page', 'genesis' ), array( $this, 'blogpage_box' ), $this->pagehook, 'main' );

		if ( current_user_can( 'unfiltered_html' ) )
			add_meta_box( 'genesis-theme-settings-scripts', __( 'Header and Footer Scripts', 'genesis' ), array( $this, 'scripts_box' ), $this->pagehook, 'main' );

		do_action( 'genesis_theme_settings_metaboxes', $this->pagehook );

	}

	/**
	 * Outputs hidden form fields before the metaboxes.
	 *
	 * @since 1.8.0
	 *
	 * @uses Genesis_Admin::get_field_name() Construct full field name
	 * @uses Genesis_Admin::get_field_value() Retrieve value of key under $this->settings_field
	 *
	 * @param string $pagehook
	 * @return null
	 */
	function hidden_fields( $pagehook ) {

		if ( $pagehook != $this->pagehook )
			return;

		printf( '<input type="hidden" name="%s" value="%s" />', $this->get_field_name( 'theme_version' ), esc_attr( $this->get_field_value( 'theme_version' ) ) );
		printf( '<input type="hidden" name="%s" value="%s" />', $this->get_field_name( 'db_version' ), esc_attr( $this->get_field_value( 'db_version' ) ) );

	}

	/**
	 * Callback for Theme Settings Information meta box.
	 *
	 * @since 1.0.0
	 *
	 * @uses Genesis_Admin::get_field_name() Construct full field name
	 * @uses Genesis_Admin::get_field_value() Retrieve value of key under $this->settings_field
	 * @uses PARENT_THEME_RELEASE_DATE
	 *
	 * @see Genesis_Admin_Settings::metaboxes()
	 */
	function info_box() {

		?>
		<p><strong><?php _e( 'Version:', 'genesis' ); ?></strong> <?php echo $this->get_field_value( 'theme_version' ); ?> <?php echo g_ent( '&middot;' ); ?> <strong><?php _e( 'Released:', 'genesis' ); ?></strong> <?php echo PARENT_THEME_RELEASE_DATE; ?></p>

		<p>
			<input type="checkbox" name="<?php echo $this->get_field_name( 'show_info' ); ?>" id="<?php echo $this->get_field_id( 'show_info' ); ?>" value="1"<?php checked( $this->get_field_value( 'show_info' ) ); ?> />
			<label for="<?php echo $this->get_field_id( 'show_info' ); ?>"><?php _e( 'Display Theme Information in your document source', 'genesis' ); ?></label>
		</p>

		<p><span class="description"><?php sprintf( __( 'This can be helpful for diagnosing problems with your theme when seeking assistance in the <a href="%s" target="_blank">support forums</a>.', 'genesis' ), 'http://www.studiopress.com/support/' ); ?></span></p>

		<p>
			<input type="checkbox" name="<?php echo $this->get_field_name( 'update' ); ?>" id="<?php echo $this->get_field_id( 'update' ); ?>" value="1"<?php checked( $this->get_field_value( 'update' ) ) . disabled( is_super_admin(), 0 ); ?> />
			<label for="<?php echo $this->get_field_id( 'update' ); ?>"><?php _e( 'Enable Automatic Updates', 'genesis' ); ?></label></p>

		<div id="genesis_update_notification_setting">
			<p>
				<input type="checkbox" name="<?php echo $this->get_field_name( 'update_email' ); ?>" id="<?php echo $this->get_field_id( 'update_email' ); ?>" value="1"<?php checked( $this->get_field_value( 'update_email' ) ) . disabled( is_super_admin(), 0 ); ?> />
				<label for="<?php echo $this->get_field_id( 'update_email' ); ?>"><?php _e( 'Notify', 'genesis' ); ?></label>
				<input type="text" name="<?php echo $this->get_field_name( 'update_email_address' ); ?>" id="<?php echo $this->get_field_id( 'update_email_address' ); ?>" value="<?php echo esc_attr( $this->get_field_value( 'update_email_address' ) ); ?>" size="30"<?php disabled( 0, is_super_admin() ); ?> />
				<label for="<?php echo $this->get_field_id( 'update_email_address' ); ?>"><?php _e( 'when updates are available', 'genesis' ); ?></label>
			</p>

			<p><span class="description"><?php _e( 'If you provide an email address above, your blog can email you when a new version of Genesis is available.', 'genesis' ); ?></span></p>
		</div>
		<?php

	}

	/**
	 * Callback for Theme Settings Color Style meta box.
	 *
	 * @since 1.8.0
	 *
	 * @uses Genesis_Admin::get_field_name() Construct full field name
	 * @uses Genesis_Admin::get_field_value() Retrieve value of key under $this->settings_field
	 *
	 * @see Genesis_Admin_Settings::metaboxes()
	 */
	function style_box() {

		$current = $this->get_field_value( 'style_selection' );
		$styles  = get_theme_support( 'genesis-style-selector' );
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'style_selection' ); ?>"><?php _e( 'Color Style:', 'genesis' ); ?></label>
			<select name="<?php echo $this->get_field_name( 'style_selection' ); ?>" id="<?php echo $this->get_field_id( 'style_selection' ); ?>">
				<option value=""><?php _e( 'Default', 'genesis' ); ?></option>
				<?php
				if ( ! empty( $styles ) ) {
					$styles = array_shift( $styles );
					foreach ( (array) $styles as $style => $title ) {
						?><option value="<?php echo esc_attr( $style ); ?>"<?php selected( $current, $style ); ?>><?php echo esc_html( $title ); ?></option><?php
					}
				}
				?>
			</select>
		</p>

		<p><span class="description"><?php _e( 'Please select the color style from the drop down list and save your settings.', 'genesis' ); ?></span></p>
		<?php

	}

	/**
	 * Callback for Theme Settings Default Layout meta box.
	 *
	 * A version of a site layout setting has been in Genesis since at least 0.2.0,
	 * but it was moved to its own meta box in 1.7.0.
	 *
	 * @since 1.7.0
	 *
	 * @uses genesis_layout_selector() Outputs form elements for layout picker
	 * @uses Genesis_Admin::get_field_name() Construct full field name
	 * @uses Genesis_Admin::get_field_value() Retrieve value of key under $this->settings_field
	 *
	 * @see Genesis_Admin_Settings::metaboxes()
	 */
	function layout_box() {

		?>
		<p class="genesis-layout-selector">
		<?php
		genesis_layout_selector( array( 'name' => $this->get_field_name( 'site_layout' ), 'selected' => $this->get_field_value( 'site_layout' ), 'type' => 'site' ) );
		?>
		</p>

		<br class="clear" />
		<?php

	}

	/**
	 * Callback for Theme Settings Header meta box.
	 *
	 * @since 1.7.0
	 *
	 * @uses Genesis_Admin::get_field_name() Construct full field name
	 * @uses Genesis_Admin::get_field_value() Retrieve value of key under $this->settings_field
	 *
	 * @see Genesis_Admin_Settings::metaboxes()
	 */
	function header_box() {
		?>

		<p><?php _e( 'Use for blog title/logo:', 'genesis' ); ?>
			<select name="<?php echo $this->get_field_name( 'blog_title' ); ?>">
				<option value="text"<?php selected( $this->get_field_value( 'blog_title' ), 'text' ); ?>><?php _e( 'Dynamic text', 'genesis' ); ?></option>
				<option value="image"<?php selected( $this->get_field_value( 'blog_title' ), 'image' ); ?>><?php _e( 'Image logo', 'genesis' ); ?></option>
			</select></p>

		<?php

	}

	/**
	 * Callback for Theme Settings Navigation Settings meta box.
	 *
	 * @category Genesis
	 * @package Admin
	 * @subpackage Theme-Settings
	 *
	 * @since 1.0.0
	 *
	 * @uses Genesis_Admin::get_field_name() Construct full field name
	 * @uses Genesis_Admin::get_field_value() Retrieve value of key under $this->settings_field
	 *
	 * @see Genesis_Admin_Settings::metaboxes()
	 */
	function nav_box() {

		?>
		<?php if ( genesis_nav_menu_supported( 'primary' ) ) : ?>
		<h4><?php _e( 'Primary Navigation', 'genesis' ); ?></h4>

		<p>
			<input type="checkbox" name="<?php echo $this->get_field_name( 'nav' ); ?>" id="<?php echo $this->get_field_id( 'nav' ); ?>" value="1"<?php checked( $this->get_field_value( 'nav' ) ); ?> />
			<label for="<?php echo $this->get_field_id( 'nav' ); ?>"><?php _e( 'Include Primary Navigation Menu?', 'genesis' ); ?></label>
		</p>

		<div id="genesis_nav_settings">
			<p>
				<input type="checkbox" name="<?php echo $this->get_field_name( 'nav_superfish' ); ?>" id="<?php echo $this->get_field_id( 'nav_superfish' ); ?>" value="1"<?php checked( $this->get_field_value( 'nav_superfish' ) ); ?> />
				<label for="<?php echo $this->get_field_id( 'nav_superfish' ); ?>"><?php _e( 'Enable Fancy Dropdowns?', 'genesis' ); ?></label>
			</p>

			<p>
				<input type="checkbox" name="<?php echo $this->get_field_name( 'nav_extras_enable' ); ?>" id="<?php echo $this->get_field_id( 'nav_extras_enable' ); ?>" value="1"<?php checked( $this->get_field_value( 'nav_extras_enable' ) ); ?> />
				<label for="<?php echo $this->get_field_id( 'nav_extras_enable' ); ?>"><?php _e( 'Enable Extras on Right Side?', 'genesis' ); ?></label>
			</p>

			<div id="genesis_nav_extras_settings">
				<p>
					<label for="<?php echo $this->get_field_id( 'nav_extras' ); ?>"><?php _e( 'Display the following:', 'genesis' ); ?></label>
					<select name="<?php echo $this->get_field_name( 'nav_extras' ); ?>" id="<?php echo $this->get_field_id( 'nav_extras' ); ?>">
						<option value="date"<?php selected( $this->get_field_value( 'nav_extras' ), 'date' ); ?>><?php _e( 'Today\'s date', 'genesis' ); ?></option>
						<option value="rss"<?php selected( $this->get_field_value( 'nav_extras' ), 'rss' ); ?>><?php _e( 'RSS feed links', 'genesis' ); ?></option>
						<option value="search"<?php selected( $this->get_field_value( 'nav_extras' ), 'search' ); ?>><?php _e( 'Search form', 'genesis' ); ?></option>
						<option value="twitter"<?php selected( $this->get_field_value( 'nav_extras' ), 'twitter' ); ?>><?php _e( 'Twitter link', 'genesis' ); ?></option>
					</select>
				</p>
				<div id="genesis_nav_extras_twitter">
					<p>
						<label for="<?php echo $this->get_field_id( 'nav_extras_twitter_id' ); ?>"><?php _e( 'Enter Twitter ID:', 'genesis' ); ?></label>
						<input type="text" name="<?php echo $this->get_field_name( 'nav_extras_twitter_id' ); ?>" id="<?php echo $this->get_field_id( 'nav_extras_twitter_id' ); ?>" value="<?php echo esc_attr( $this->get_field_value( 'nav_extras_twitter_id' ) ); ?>" size="27" />
					</p>
					<p>
						<label for="<?php echo $this->get_field_id( 'nav_extras_twitter_text' ); ?>"><?php _e( 'Twitter Link Text:', 'genesis' ); ?></label>
						<input type="text" name="<?php echo $this->get_field_name( 'nav_extras_twitter_text' ); ?>" id="<?php echo $this->get_field_id( 'nav_extras_twitter_text' ); ?>" value="<?php echo esc_attr( $this->get_field_value( 'nav_extras_twitter_text' ) ); ?>" size="27" />
					</p>
				</div>
			</div>
		</div>

		<hr class="div" />
		<?php endif; ?>

		<?php if ( genesis_nav_menu_supported( 'secondary' ) ) : ?>
		<h4><?php _e( 'Secondary Navigation', 'genesis' ); ?></h4>

		<p>
			<input type="checkbox" name="<?php echo $this->get_field_name( 'subnav' ); ?>" id="<?php echo $this->get_field_id( 'subnav' ); ?>" value="1"<?php checked( $this->get_field_value( 'subnav' ) ); ?> />
			<label for="<?php echo $this->get_field_id( 'subnav' ); ?>"><?php _e( 'Include Secondary Navigation Menu?', 'genesis' ); ?></label>
		</p>

		<div id="genesis_subnav_settings">
			<p>
				<input type="checkbox" name="<?php echo $this->get_field_name( 'subnav_superfish' ); ?>" id="<?php echo $this->get_field_id( 'subnav_superfish' ); ?>" value="1"<?php checked( $this->get_field_value( 'subnav_superfish' ) ); ?> />
				<label for="<?php echo $this->get_field_id( 'subnav_superfish' ); ?>"><?php _e( 'Enable Fancy Dropdowns?', 'genesis' ); ?></label>
			</p>
		</div>

		<hr class="div" />
		<?php endif; ?>

		<p><span class="description"><?php printf( __( 'In order to use the navigation menus, you must build a <a href="%s">custom menu</a>, then assign it to the proper Menu Location.', 'genesis' ), admin_url( 'nav-menus.php' ) ); ?></span></p>
		<?php

	}

	/**
	 * Callback for Theme Settings Custom Feeds meta box.
	 *
	 * @since 1.3.0
	 *
	 * @uses Genesis_Admin::get_field_name() Construct full field name
	 * @uses Genesis_Admin::get_field_value() Retrieve value of key under $this->settings_field
	 *
	 * @see Genesis_Admin_Settings::metaboxes()
	 */
	function feeds_box() {

		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'feed_uri' ); ?>"><?php _e( 'Enter your custom feed URI:', 'genesis' ); ?></label><br />
			<input type="text" name="<?php echo $this->get_field_name( 'feed_uri' ); ?>" id="<?php echo $this->get_field_id( 'feed_uri' ); ?>" value="<?php echo esc_attr( $this->get_field_value( 'feed_uri' ) ); ?>" size="50" />

			<input type="checkbox" name="<?php echo $this->get_field_name( 'redirect_feed' ); ?>" id="<?php echo $this->get_field_id( 'redirect_feed' ); ?>" value="1"<?php checked( $this->get_field_value( 'redirect_feed' ) ); ?> />
			<label for="<?php echo $this->get_field_id( 'redirect_feed' ); ?>"><?php _e( 'Redirect Feed?', 'genesis' ); ?></label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'comments_feed_uri' ); ?>"><?php _e( 'Enter your custom comments feed URI:', 'genesis' ); ?></label><br />
			<input type="text" name="<?php echo $this->get_field_name( 'comments_feed_uri' ); ?>" id="<?php echo $this->get_field_id( 'comments_feed_uri' ); ?>" value="<?php echo esc_attr( $this->get_field_value( 'comments_feed_uri' ) ); ?>" size="50" />

			<input type="checkbox" name="<?php echo $this->get_field_name( 'redirect_comments_feed' ); ?>" id="<?php echo $this->get_field_id( 'redirect_comments_feed' ); ?>" value="1"<?php checked( $this->get_field_value( 'redirect_comments__feed' ) ); ?> />
			<label for="<?php echo $this->get_field_id( 'redirect_comments_feed' ); ?>"><?php _e( 'Redirect Feed?', 'genesis' ); ?></label>
		</p>

		<p><span class="description"><?php printf( __( 'If your custom feed(s) are not handled by Feedburner, we do not recommend that you use the redirect options.', 'genesis' ) ); ?></span></p>
		<?php

	}

	/**
	 * Callback for Theme Settings Comments meta box.
	 *
	 * @since 1.0.0
	 *
	 * @uses Genesis_Admin::get_field_name() Construct full field name
	 * @uses Genesis_Admin::get_field_value() Retrieve value of key under $this->settings_field
	 *
	 * @see Genesis_Admin_Settings::metaboxes()
	 */
	function comments_box() {

		?>
		<p>
			<?php _e( 'Enable Comments', 'genesis' ); ?>
			<input type="checkbox" name="<?php echo $this->get_field_name( 'comments_posts' ); ?>" id="<?php echo $this->get_field_id( 'comments_posts' ); ?>" value="1"<?php checked( $this->get_field_value( 'comments_posts' ) ); ?> />
			<label for="<?php echo $this->get_field_id( 'comments_posts' ); ?>" title="Enable comments on posts"><?php _e( 'on posts?', 'genesis' ); ?></label>

			<input type="checkbox" name="<?php echo $this->get_field_name( 'comments_pages' ); ?>" id="<?php echo $this->get_field_id( 'comments_pages' ); ?>" value="1"<?php checked( $this->get_field_value( 'comments_pages' ) ); ?> />
			<label for="<?php echo $this->get_field_id( 'comments_pages' ); ?>" title="Enable comments on pages"><?php _e( 'on pages?', 'genesis' ); ?></label>
		</p>

		<p>
			<?php _e( 'Enable Trackbacks', 'genesis' ); ?>
			<input type="checkbox" name="<?php echo $this->get_field_name( 'trackbacks_posts' ); ?>" id="<?php echo $this->get_field_id( 'trackbacks_posts' ); ?>" value="1"<?php checked( $this->get_field_value( 'trackbacks_posts' ) ); ?> />
			<label for="<?php echo $this->get_field_id( 'trackbacks_posts' ); ?>" title="Enable trackbacks on posts"><?php _e( 'on posts?', 'genesis' ); ?></label>

			<input type="checkbox" name="<?php echo $this->get_field_name( 'trackbacks_pages' ); ?>" id="<?php echo $this->get_field_id( 'trackbacks_pages' ); ?>" value="1"<?php checked( $this->get_field_value( 'trackbacks_pages' ) ); ?> />
			<label for="<?php echo $this->get_field_id( 'trackbacks_pages' ); ?>" title="Enable trackbacks on pages"><?php _e( 'on pages?', 'genesis' ); ?></label>
		</p>

		<p><span class="description"><?php _e( 'Comments and Trackbacks can also be disabled on a per post/page basis when creating/editing posts/pages.', 'genesis' ); ?></span></p>
		<?php

	}

	/**
	 * Callback for Theme Settings Custom Feeds meta box.
	 *
	 * @since 1.3.0
	 *
	 * @uses Genesis_Admin::get_field_name() Construct full field name
	 * @uses Genesis_Admin::get_field_value() Retrieve value of key under $this->settings_field
	 *
	 * @see Genesis_Admin_Settings::metaboxes()
	 */
	function breadcrumb_box() {

		?>
		<h4><?php _e( 'Enable on:', 'genesis' ); ?></h4>
		<p>
			<input type="checkbox" name="<?php echo $this->get_field_name( 'breadcrumb_home' ); ?>" id="<?php echo $this->get_field_id( 'breadcrumb_home' ); ?>" value="1"<?php checked( $this->get_field_value( 'breadcrumb_home' ) ); ?> />
			<label for="<?php echo $this->get_field_id( 'breadcrumb_home' ); ?>"><?php _e( 'Front Page', 'genesis' ); ?></label>

			<input type="checkbox" name="<?php echo $this->get_field_name( 'breadcrumb_single' ); ?>" id="<?php echo $this->get_field_id( 'breadcrumb_single' ); ?>" value="1"<?php checked( $this->get_field_value( 'breadcrumb_single' ) ); ?> />
			<label for="<?php echo $this->get_field_id( 'breadcrumb_single' ); ?>"><?php _e( 'Posts', 'genesis' ); ?></label>

			<input type="checkbox" name="<?php echo $this->get_field_name( 'breadcrumb_page' ); ?>" id="<?php echo $this->get_field_id( 'breadcrumb_page' ); ?>" value="1"<?php checked( $this->get_field_value( 'breadcrumb_page' ) ); ?> />
			<label for="<?php echo $this->get_field_id( 'breadcrumb_page' ); ?>"><?php _e( 'Pages', 'genesis' ); ?></label>

			<input type="checkbox" name="<?php echo $this->get_field_name( 'breadcrumb_archive' ); ?>" id="<?php echo $this->get_field_id( 'breadcrumb_archive' ); ?>" value="1"<?php checked( $this->get_field_value( 'breadcrumb_archive' ) ); ?> />
			<label for="<?php echo $this->get_field_id( 'breadcrumb_archive' ); ?>"><?php _e( 'Archives', 'genesis' ); ?></label>

			<input type="checkbox" name="<?php echo $this->get_field_name( 'breadcrumb_404' ); ?>" id="<?php echo $this->get_field_id( 'breadcrumb_404' ); ?>" value="1"<?php checked( $this->get_field_value( 'breadcrumb_404' ) ); ?> />
			<label for="<?php echo $this->get_field_id( 'breadcrumb_404' ); ?>"><?php _e( '404 Page', 'genesis' ); ?></label>

			<input type="checkbox" name="<?php echo $this->get_field_name( 'breadcrumb_attachment' ); ?>" id="<?php echo $this->get_field_id( 'breadcrumb_attachment' ); ?>" value="1"<?php checked( $this->get_field_value( 'breadcrumb_attachment' ) ); ?> />
			<label for="<?php echo $this->get_field_id( 'breadcrumb_attachment' ); ?>"><?php _e( 'Attachment Page', 'genesis' ); ?></label>
		</p>

		<p><span class="description"><?php _e( 'Breadcrumbs are a great way of letting your visitors find out where they are on your site with just a glance. You can enable/disable them on certain areas of your site.', 'genesis' ); ?></span></p>
		<?php

	}

	/**
	 * Callback for Theme Settings Post Archives meta box.
	 *
	 * @since 1.0.0
	 *
	 * @uses Genesis_Admin::get_field_name() Construct full field name
	 * @uses Genesis_Admin::get_field_value() Retrieve value of key under $this->settings_field
	 * @uses genesis_get_images_sizes() Retrieves list of registered image sizes
	 *
	 * @see Genesis_Admin_Settings::metaboxes()
	 */
	function post_archives_box() {

		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'content_archive' ); ?>"><?php _e( 'Select one of the following:', 'genesis' ); ?></label>
			<select name="<?php echo $this->get_field_name( 'content_archive' ); ?>" id="<?php echo $this->get_field_id( 'content_archive' ); ?>">
			<?php
			$archive_display = apply_filters(
				'genesis_archive_display_options',
				array(
					'full'     => __( 'Display post content', 'genesis' ),
					'excerpts' => __( 'Display post excerpts', 'genesis' ),
				)
			);
			foreach ( (array) $archive_display as $value => $name )
				echo '<option value="' . esc_attr( $value ) . '"' . selected( $this->get_field_value( 'content_archive' ), esc_attr( $value ), false ) . '>' . esc_html( $name ) . '</option>' . "\n";
			?>
			</select>
		</p>

		<div id="genesis_content_limit_setting">
			<p>
				<label for="<?php echo $this->get_field_id( 'content_archive_limit' ); ?>"><?php _e( 'Limit content to', 'genesis' ); ?>
				<input type="text" name="<?php echo $this->get_field_name( 'content_archive_limit' ); ?>" id="<?php echo $this->get_field_id( 'content_archive_limit' ); ?>" value="<?php echo esc_attr( $this->get_field_value( 'content_archive_limit' ) ); ?>" size="3" />
				<?php _e( 'characters', 'genesis' ); ?></label>
			</p>

			<p><span class="description"><?php _e( 'Using this option will limit the text and strip all formatting from the text displayed. To use this option, choose "Display post content" in the select box above.', 'genesis' ); ?></span></p>
		</div>

		<p>
			<input type="checkbox" name="<?php echo $this->get_field_name( 'content_archive_thumbnail' ); ?>" id="<?php echo $this->get_field_id( 'content_archive_thumbnail' ); ?>" value="1"<?php checked( $this->get_field_value( 'content_archive_thumbnail' ) ); ?> />
			<label for="<?php echo $this->get_field_id( 'content_archive_thumbnail' ); ?>"><?php _e( 'Include the Featured Image?', 'genesis' ); ?></label>
		</p>

		<p id="genesis_image_size">
			<label for="<?php echo $this->get_field_id( 'image_size' ); ?>"><?php _e( 'Image Size:', 'genesis' ); ?></label>
			<select name="<?php echo $this->get_field_name( 'image_size' ); ?>" id="<?php echo $this->get_field_id( 'image_size' ); ?>">
			<?php
			$sizes = genesis_get_image_sizes();
			foreach ( (array) $sizes as $name => $size )
				echo '<option value="' . $name . '"' . selected( $this->get_field_value( 'image_size' ), $name, FALSE ) . '>' . $name . ' (' . $size['width'] . ' &#215; ' . $size['height'] . ')</option>' . "\n";
			?>
			</select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'posts_nav' ); ?>"><?php _e( 'Select Post Navigation Technique:', 'genesis' ); ?></label>
			<select name="<?php echo $this->get_field_name( 'posts_nav' ); ?>" id="<?php echo $this->get_field_id( 'posts_nav' ); ?>">
				<option value="older-newer"<?php selected( 'older-newer', $this->get_field_value( 'posts_nav' ) ); ?>><?php _e( 'Older / Newer', 'genesis' ); ?></option>
				<option value="prev-next"<?php selected( 'prev-next', $this->get_field_value( 'posts_nav' ) ); ?>><?php _e( 'Previous / Next', 'genesis' ); ?></option>
				<option value="numeric"<?php selected( 'numeric', $this->get_field_value( 'posts_nav' ) ); ?>><?php _e( 'Numeric', 'genesis' ); ?></option>
			</select>
		</p>

		<p><span class="description"><?php _e( 'These options will affect any blog listings page, including archive, author, blog, category, search, and tag pages.', 'genesis' ); ?></span></p>
		<?php

	}

	/**
	 * Callback for Theme Settings Blog Page meta box.
	 *
	 * @since 1.0.0
	 *
	 * @uses Genesis_Admin::get_field_name() Construct full field name
	 * @uses Genesis_Admin::get_field_value() Retrieve value of key under $this->settings_field
	 *
	 * @see Genesis_Admin_Settings::metaboxes()
	 */
	function blogpage_box() {

		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'blog_cat' ); ?>"><?php _e( 'Display which category:', 'genesis' ); ?></label>
			<?php wp_dropdown_categories( array( 'selected' => $this->get_field_value( 'blog_cat' ), 'name' => $this->get_field_name( 'blog_cat' ), 'orderby' => 'Name', 'hierarchical' => 1, 'show_option_all' => __( 'All Categories', 'genesis' ), 'hide_empty' => '0' ) ); ?>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'blog_cat_exclude' ); ?>"><?php _e( 'Exclude the following Category IDs:', 'genesis' ); ?><br />
				<input type="text" name="<?php echo $this->get_field_name( 'blog_cat_exclude' ); ?>" id="<?php echo $this->get_field_id( 'blog_cat_exclude' ); ?>" value="<?php echo esc_attr( $this->get_field_value( 'blog_cat_exclude' ) ); ?>" size="40" />
				<br /><small><strong><?php _e( 'Comma separated - 1,2,3 for example', 'genesis' ); ?></strong></small>
			</label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'blog_cat_num' ); ?>"><?php _e( 'Number of Posts to Show:', 'genesis' ); ?></label>
			<input type="text" name="<?php echo $this->get_field_name( 'blog_cat_num' ); ?>" id="<?php echo $this->get_field_id( 'blog_cat_num' ); ?>" value="<?php echo esc_attr( $this->get_field_value( 'blog_cat_num' ) ); ?>" size="2" />
		</p>
		<?php

	}

	/**
	 * Callback for Theme Settings Header / Footer Scripts meta box.
	 *
	 * @since 1.0.0
	 *
	 * @uses Genesis_Admin::get_field_name() Construct full field name
	 * @uses Genesis_Admin::get_field_value() Retrieve value of key under $this->settings_field
	 *
	 * @see Genesis_Admin_Settings::metaboxes()
	 */
	function scripts_box() {

		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'header_scripts' ); ?>"><?php printf( __( 'Enter scripts or code you would like output to %s:', 'genesis' ), '<code>wp_head()</code>' ); ?></label>
		</p>

		<textarea name="<?php echo $this->get_field_name( 'header_scripts' ); ?>" id="<?php echo $this->get_field_id( 'header_scripts' ); ?>" cols="78" rows="8"><?php echo esc_textarea( $this->get_field_value( 'header_scripts' ) ); ?></textarea>

		<p><span class="description"><?php printf( __( 'The %1$s hook executes immediately before the closing %2$s tag in the document source.', 'genesis' ), '<code>wp_head()</code>', '<code>&lt;/head&gt;</code>' ); ?></span></p>

		<hr class="div" />

		<p>
			<label for="<?php echo $this->get_field_id( 'footer_scripts' ); ?>"><?php printf( __( 'Enter scripts or code you would like output to %s:', 'genesis' ), '<code>wp_footer()</code>' ); ?></label>
		</p>

		<textarea name="<?php echo $this->get_field_name( 'footer_scripts' ); ?>" id="<?php echo $this->get_field_id( 'header_scripts' ); ?>" cols="78" rows="8"><?php echo esc_textarea( $this->get_field_value( 'footer_scripts' ) ); ?></textarea>

		<p><span class="description"><?php printf( __( 'The %1$s hook executes immediately before the closing %2$s tag in the document source.', 'genesis' ), '<code>wp_footer()</code>', '<code>&lt;/body&gt;</code>' ); ?></span></p>
		<?php

	}

}
