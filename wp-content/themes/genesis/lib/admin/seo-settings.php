<?php
/**
 * Creates the SEO Settings page.
 *
 * @category Genesis
 * @package  Admin
 * @author   StudioPress
 * @license  http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link     http://www.studiopress.com/themes/genesis
 */

/**
 * Registers a new admin page, providing content and corresponding menu item
 * for the SEO Settings page.
 *
 * Although this class was added in 1.8.0, some of the methods were originally
 * standalone functions added in previous versions of Genesis.
 *
 * @category Genesis
 * @package Admin
 *
 * @since 1.8.0
 */
class Genesis_Admin_SEO_Settings extends Genesis_Admin_Boxes {

	/**
	 * Create an admin menu item and settings page.
	 *
	 * @since 1.8.0
	 *
	 * @uses GENESIS_SEO_SETTINGS_FIELD settings field key
	 *
	 * @global string $_genesis_seo_settings_pagehook SEO Settings page hook,
	 * kept for backwards compatibility, since this class now uses $this->pagehook.
	 */
	function __construct() {

		$page_id = 'seo-settings';

		$menu_ops = array(
			'submenu' => array(
				'parent_slug' => 'genesis',
				'page_title'  => __( 'Genesis - SEO Settings', 'genesis' ),
				'menu_title'  => __( 'SEO Settings', 'genesis' )
			)
		);

		$page_ops = array(
			'screen_icon'       => 'options-general',
			'save_button_text'  => __( 'Save Settings', 'genesis' ),
			'reset_button_text' => __( 'Reset Settings', 'genesis' ),
			'saved_notice_text' => __( 'Settings saved.', 'genesis' ),
			'reset_notice_text' => __( 'Settings reset.', 'genesis' ),
			'error_notice_text' => __( 'Error saving settings.', 'genesis' ),
		);

		$settings_field = GENESIS_SEO_SETTINGS_FIELD;

		$default_settings = apply_filters(
			'genesis_seo_settings_defaults',
			array(
				'append_description_home'      => 1,
				'append_site_title'            => 0,
				'doctitle_sep'                 => '—',
				'doctitle_seplocation'         => 'right',

				'home_h1_on'                   => 'title',
				'home_doctitle'                => '',
				'home_description'             => '',
				'home_keywords'                => '',
				'home_noindex'                 => 0,
				'home_nofollow'                => 0,
				'home_noarchive'               => 0,

				'canonical_archives'           => 1,

				'head_adjacent_posts_rel_link' => 0,
				'head_wlwmanifest_link'        => 0,
				'head_shortlink'               => 0,

				'noindex_cat_archive'          => 1,
				'noindex_tag_archive'          => 1,
				'noindex_author_archive'       => 1,
				'noindex_date_archive'         => 1,
				'noindex_search_archive'       => 1,
				'noarchive_cat_archive'        => 0,
				'noarchive_tag_archive'        => 0,
				'noarchive_author_archive'     => 0,
				'noarchive_date_archive'       => 0,
				'noarchive_search_archive'     => 0,
				'noarchive'                    => 0,
				'noodp'                        => 1,
				'noydir'                       => 1,
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
				'append_description_home',
				'append_site_title',
				'home_noindex',
				'home_nofollow',
				'home_noarchive',
				'head_index_rel_link',
				'head_parent_post_rel_link',
				'head_start_post_rel_link',
				'head_adjacent_posts_rel_link',
				'head_wlwmanifest_link',
				'head_shortlink',
				'noindex_cat_archive',
				'noindex_tag_archive',
				'noindex_author_archive',
				'noindex_date_archive',
				'noindex_search_archive',
				'noarchive',
				'noarchive_cat_archive',
				'noarchive_tag_archive',
				'noarchive_author_archive',
				'noarchive_date_archive',
				'noarchive_search_archive',
				'noodp',
				'noydir',
				'canonical_archives',
			)
		);

		genesis_add_option_filter(
			'no_html',
			$this->settings_field,
			array(
				'home_doctitle',
				'home_description',
				'home_keywords',
				'doctitle_sep',
			)
		);

	}

	/**
 	 * Register meta boxes on the SEO Settings page.
 	 *
 	 * @since 1.0.0
 	 *
 	 * @see Genesis_Admin_SEO_Settings::doctitle_box() Callback for document title box
 	 * @see Genesis_Admin_SEO_Settings::homepage_box() Callback for home page box
 	 * @see Genesis_Admin_SEO_Settings::document_head_box() Callback for document head box
 	 * @see Genesis_Admin_SEO_Settings::robots_meta_box() Callback for robots meta box
 	 * @see Genesis_Admin_SEO_Settings::archives_box() Callback for archives box
 	 */
	function metaboxes() {

		add_meta_box( 'genesis-seo-settings-doctitle', __( 'Doctitle Settings', 'genesis' ), array( $this, 'doctitle_box' ), $this->pagehook, 'main' );
		add_meta_box( 'genesis-seo-settings-homepage', __( 'Homepage Settings', 'genesis' ), array( $this, 'homepage_box' ), $this->pagehook, 'main' );
		add_meta_box( 'genesis-seo-settings-dochead', __( 'Document Head Settings', 'genesis' ), array( $this, 'document_head_box' ), $this->pagehook, 'main' );
		add_meta_box( 'genesis-seo-settings-robots', __( 'Robots Meta Settings', 'genesis' ), array( $this, 'robots_meta_box' ), $this->pagehook, 'main' );
		add_meta_box( 'genesis-seo-settings-archives', __( 'Archives Settings', 'genesis' ), array( $this, 'archives_box' ), $this->pagehook, 'main' );

	}

	/**
	 * Callback for SEO Settings Document Title meta box.
	 *
	 * @since 1.0.0
	 *
	 * @uses Genesis_Admin::get_field_name() Construct full field name
	 * @uses Genesis_Admin::get_field_value() Retrieve value of key under $this->settings_field
	 *
	 * @see Genesis_Admin_SEO_Settings::metaboxes()
	 */
	function doctitle_box() {

		?>
		<p><span class="description"><?php _e( 'The Document Title is the single most important SEO tag in your document source. It succinctly informs search engines of what information is contained in the document. The doctitle changes from page to page, but these options will help you control what it looks by default.', 'genesis' ); ?></span></p>

		<p><span class="description"><?php _e( '<b>By default</b>, the homepage doctitle will contain the site title, the single post and page doctitle will contain the post/page title, archive pages will contain the archive type, etc.', 'genesis' ); ?></span></p>

		<p>
			<input type="checkbox" name="<?php echo $this->get_field_name( 'append_description_home' ); ?>" id="<?php echo $this->get_field_id( 'append_description_home' ); ?>" value="1" <?php checked( $this->get_field_value( 'append_description_home' ) ); ?> />
			<label for="<?php echo $this->get_field_id( 'append_description_home' ); ?>"><?php _e( 'Append Site Description to Doctitle on homepage?', 'genesis' ); ?></label>
		</p>

		<p>
			<input type="checkbox" name="<?php echo $this->get_field_name( 'append_site_title' ); ?>" id="<?php echo $this->get_field_id( 'append_site_title' ); ?>" value="1" <?php checked( $this->get_field_value( 'append_site_title' ) ); ?> />
			<label for="<?php echo $this->get_field_id( 'append_site_title' ); ?>"><?php _e( 'Append Site Name to Doctitle on inner pages?', 'genesis' ); ?> </label>
		</p>

		<fieldset>
			<legend><?php printf( __( 'Doctitle (%s) Append Location:', 'genesis' ), '<code>&lt;title&gt;</code>' ); ?></legend>
			<span class="description"><?php _e( 'Determines what side the appended doctitle text will go on.', 'genesis' ); ?></span>

			<p>
				<input type="radio" name="<?php echo $this->get_field_name( 'doctitle_seplocation' ); ?>" id="<?php echo $this->get_field_id( 'doctitle_seplocation_left' ); ?>" value="left" <?php checked( $this->get_field_value( 'doctitle_seplocation' ), 'left' ); ?> />
				<label for="<?php echo $this->get_field_id( 'doctitle_seplocation_left' ); ?>"><?php _e( 'Left', 'genesis' ); ?></label>
				<br />
				<input type="radio" name="<?php echo $this->get_field_name( 'doctitle_seplocation' ); ?>" id="<?php echo $this->get_field_id( 'doctitle_seplocation_right' ); ?>" value="right" <?php checked( $this->get_field_value( 'doctitle_seplocation' ), 'right' ); ?> />
				<label for="<?php echo $this->get_field_id( 'doctitle_seplocation_right' ); ?>"><?php _e( 'Right', 'genesis' ); ?></label>
			</p>
		</fieldset>

		<p>
			<label for="<?php echo $this->get_field_id( 'doctitle_sep' ); ?>"><?php printf( __( 'Doctitle (<code>&lt;title&gt;</code>) Separator:', 'genesis' ), '<code>&lt;title&gt;</code>' ); ?></label>
			<input type="text" name="<?php echo $this->get_field_name( 'doctitle_sep' ); ?>" value="<?php echo esc_attr( $this->get_field_value( 'doctitle_sep' ) ); ?>" size="15" /><br />
			<span class="description"><?php _e( 'If the doctitle consists of two parts (Title &amp; Appended Text), then the Doctitle Separator will go between them.', 'genesis' ); ?></span>
		</p>

		<?php

	}

	/**
	 * Callback for SEO Settings Home Page meta box.
	 *
	 * @since 1.0.0
	 *
	 * @uses Genesis_Admin::get_field_name() Construct full field name
	 * @uses Genesis_Admin::get_field_value() Retrieve value of key under $this->settings_field
	 *
	 * @see Genesis_Admin_SEO_Settings::metaboxes()
	 */
	function homepage_box() {

		?>
		<fieldset>
			<legend><?php printf( __( 'Which text would you like to be wrapped in %s tags?', 'genesis' ), '<code>&lt;h1&gt;</code>' ); ?></legend>
			<span class="description"><?php printf( __( 'The %s tag is, arguably, the second most important SEO tag in the document source. Choose wisely.', 'genesis' ), '<code>&lt;h1&gt;</code>' ); ?></span>

			<p>
				<input type="radio" name="<?php echo $this->get_field_name( 'home_h1_on' ); ?>" id="<?php echo $this->get_field_id( 'home_h1_on_title' ); ?>" value="title" <?php checked( $this->get_field_value( 'home_h1_on' ), 'title' ); ?> />
				<label for="<?php echo $this->get_field_id( 'home_h1_on_title' ); ?>"><?php _e( 'Site Title', 'genesis' ); ?></label>
				<br />
				<input type="radio" name="<?php echo $this->get_field_name( 'home_h1_on' ); ?>" id="<?php echo $this->get_field_id( 'home_h1_on_description' ); ?>" value="description" <?php checked( $this->get_field_value( 'home_h1_on' ), 'description' ); ?> />
				<label for="<?php echo $this->get_field_id( 'home_h1_on_description' ); ?>"><?php _e( 'Site Description', 'genesis' ); ?></label>
				<br />
				<input type="radio" name="<?php echo $this->get_field_name( 'home_h1_on' ); ?>" id="<?php echo $this->get_field_id( 'home_h1_on_neither' ); ?>" value="neither" <?php checked( $this->get_field_value( 'home_h1_on' ), 'neither' ); ?> />
				<label for="<?php echo $this->get_field_id( 'home_h1_on_neither' ); ?>"><?php _e( 'Neither. I\'ll manually wrap my own text on the homepage', 'genesis' ); ?></label>
			</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'home_doctitle' ); ?>"><?php _e( 'Home Doctitle:', 'genesis' ); ?></label><br />
			<input type="text" name="<?php echo $this->get_field_name( 'home_doctitle' ); ?>" id="<?php echo $this->get_field_id( 'home_doctitle' ); ?>" value="<?php echo esc_attr( $this->get_field_value( 'home_doctitle' ) ); ?>" size="80" /><br />
			<span class="description"><?php _e( 'If you leave the doctitle field blank, your site&rsquo;s title will be used instead.', 'genesis' ); ?></span>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'home_description' ); ?>"><?php _e( 'Home META Description:', 'genesis' ); ?></label><br />
			<textarea name="<?php echo $this->get_field_name( 'home_description' ); ?>" id="<?php echo $this->get_field_id( 'home_description' ); ?>" rows="3" cols="70"><?php echo esc_textarea( $this->get_field_value( 'home_description' ) ); ?></textarea><br />
			<span class="description"><?php _e( 'The META Description can be used to determine the text used under the title on search engine results pages.', 'genesis' ); ?></span>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'home_keywords' ); ?>"><?php _e( 'Home META Keywords (comma separated):', 'genesis' ); ?></label><br />
			<input type="text" name="<?php echo $this->get_field_name( 'home_keywords' ); ?>" id="<?php echo $this->get_field_id( 'home_keywords' ); ?>" value="<?php echo esc_attr( $this->get_field_value( 'home_keywords' ) ); ?>" size="80" /><br />
			<span class="description"><?php _e( 'Keywords are generally ignored by Search Engines.', 'genesis' ); ?></span>
		</p>

		<h4><?php _e( 'Homepage Robots Meta Tags:', 'genesis' ); ?></h4>

		<p>
			<input type="checkbox" name="<?php echo $this->get_field_name( 'home_noindex' ); ?>" id="<?php echo $this->get_field_id( 'home_noindex' ); ?>" value="1" <?php checked( $this->get_field_value( 'home_noindex' ) ); ?> />
			<label for="<?php echo $this->get_field_id( 'home_noindex' ); ?>"><?php printf( __( 'Apply %s to the homepage?', 'genesis' ), '<code>noindex</code>' ); ?></label>
			<br />
			<input type="checkbox" name="<?php echo $this->get_field_name( 'home_nofollow' ); ?>" id="<?php echo $this->get_field_id( 'home_nofollow' ); ?>" value="1" <?php checked( $this->get_field_value( 'home_nofollow' ) ); ?> />
			<label for="<?php echo $this->get_field_id( 'home_nofollow' ); ?>"><?php printf( __( 'Apply %s to the homepage?', 'genesis' ), '<code>nofollow</code>' ); ?></label>
			<br />
			<input type="checkbox" name="<?php echo $this->get_field_name( 'home_noarchive' ); ?>" id="<?php echo $this->get_field_id( 'home_noarchive' ); ?>" value="1" <?php checked( $this->get_field_value( 'home_noarchive' ) ); ?> />
			<label for="<?php echo $this->get_field_id( 'home_noarchive' ); ?>"><?php printf( __( 'Apply %s to the homepage?', 'genesis' ), '<code>noarchive</code>' ); ?></label>
		</p>
		<?php

	}

	/**
	 * Callback for SEO Settings Document Head meta box.
	 *
	 * @since 1.3.0
	 *
	 * @uses Genesis_Admin::get_field_name() Construct full field name
	 * @uses Genesis_Admin::get_field_value() Retrieve value of key under $this->settings_field
	 *
	 * @see Genesis_Admin_SEO_Settings::metaboxes()
	 */
	function document_head_box() {

		?>
		<p><span class="description"><?php printf( __( 'By default, WordPress places several tags in your document %1$s. Most of these tags are completely unnecessary, and provide no SEO value whatsoever. They just make your site slower to load. Choose which tags you would like included in your document %1$s. If you do not know what something is, leave it unchecked.', 'genesis' ), '<code>&lt;head&gt;</code>' ); ?></span></p>

		<h4><?php _e( 'Relationship Link Tags:', 'genesis' ); ?></h4>

		<p>
			<input type="checkbox" name="<?php echo $this->get_field_name( 'head_adjacent_posts_rel_link' ); ?>" id="<?php echo $this->get_field_id( 'head_adjacent_posts_rel_link' ); ?>" value="1" <?php checked( $this->get_field_value( 'head_adjacent_posts_rel_link' ) ); ?> />
			<label for="<?php echo $this->get_field_id( 'head_adjacent_posts_rel_link' ); ?>"><?php printf( __( 'Adjacent Posts %s link tags', 'genesis' ), '<code>rel</code>' ); ?></label>
		</p>

		<h4><?php _e( 'Windows Live Writer Support:', 'genesis' ); ?></h4>

		<p>
			<input type="checkbox" name="<?php echo $this->get_field_name( 'head_wlwmanifest_link' ); ?>" id="<?php echo $this->get_field_id( 'head_wlmanifest_link' ); ?>" value="1" <?php checked( $this->get_field_value( 'head_wlwmanifest_link' ) ); ?> />
			<label for="<?php echo $this->get_field_id( 'head_wlmanifest_link' ); ?>"><?php printf( __( 'Include Windows Live Writer Support Tag?', 'genesis' ) ); ?></label>
		</p>

		<h4><?php _e( 'Shortlink Tag:', 'genesis' ); ?></h4>

		<p>
			<input type="checkbox" name="<?php echo $this->get_field_name( 'head_shortlink' ); ?>" id="<?php echo $this->get_field_id( 'head_shortlink' ); ?>" value="1" <?php checked( $this->get_field_value( 'head_shortlink' ) ); ?> />
			<label for="<?php echo $this->get_field_id( 'head_shortlink' ); ?>"><?php printf( __( 'Include Shortlink tag?', 'genesis' ) ); ?></label><br />
			<span class="description"><?php _e( 'The shortlink tag might have some use for 3rd party service discoverability, but it has no SEO value whatsoever.', 'genesis' ); ?></span>
		</p>
		<?php

	}

	/**
	 * Callback for SEO Settings Robots meta box.
	 *
	 * Variations of some of the settings contained in this meta box were first added to
	 * a 'Search Engine Indexing' meta box, added in 1.0.0.
	 *
	 * @since 1.3.0
	 *
	 * @uses Genesis_Admin::get_field_name() Construct full field name
	 * @uses Genesis_Admin::get_field_value() Retrieve value of key under $this->settings_field
	 *
	 * @see Genesis_Admin_SEO_Settings::metaboxes()
	 */
	function robots_meta_box() {

		?>
		<p><span class="description"><?php _e( 'Depending on your situation, you may or may not want the following archive pages to be indexed by search engines. Only you can make that determination.', 'genesis' ); ?></span></p>

		<p>
			<input type="checkbox" name="<?php echo $this->get_field_name( 'noindex_cat_archive' ); ?>" id="<?php echo $this->get_field_id( 'noindex_cat_archive' ); ?>" value="1" <?php checked( $this->get_field_value( 'noindex_cat_archive' ) ); ?> />
			<label for="<?php echo $this->get_field_id( 'noindex_cat_archive' ); ?>"><?php printf( __( 'Apply %s to Category Archives?', 'genesis' ), '<code>noindex</code>' ); ?></label>
			<br />
			<input type="checkbox" name="<?php echo $this->get_field_name( 'noindex_tag_archive' ); ?>" id="<?php echo $this->get_field_id( 'noindex_tag_archive' ); ?>" value="1" <?php checked( $this->get_field_value( 'noindex_tag_archive' ) ); ?> />
			<label for="<?php echo $this->get_field_id( 'noindex_tag_archive' ); ?>"><?php printf( __( 'Apply %s to Tag Archives?', 'genesis' ), '<code>noindex</code>' ); ?></label>
			<br />
			<input type="checkbox" name="<?php echo $this->get_field_name( 'noindex_author_archive' ); ?>" id="<?php echo $this->get_field_id( 'noindex_author_archive' ); ?>" value="1" <?php checked( $this->get_field_value( 'noindex_author_archive' ) ); ?> />
			<label for="<?php echo $this->get_field_id( 'noindex_author_archive' ); ?>"><?php printf( __( 'Apply %s to Author Archives?', 'genesis' ), '<code>noindex</code>' ); ?></label>
			<br />
			<input type="checkbox" name="<?php echo $this->get_field_name( 'noindex_date_archive' ); ?>" id="<?php echo $this->get_field_id( 'noindex_date_archive' ); ?>" value="1" <?php checked( $this->get_field_value( 'noindex_date_archive' ) ); ?> />
			<label for="<?php echo $this->get_field_id( 'noindex_date_archive' ); ?>"><?php printf( __( 'Apply %s to Date Archives?', 'genesis' ), '<code>noindex</code>' ); ?></label>
			<br />
			<input type="checkbox" name="<?php echo $this->get_field_name( 'noindex_search_archive' ); ?>" id="<?php echo $this->get_field_id( 'noindex_search_archive' ); ?>" value="1" <?php checked( $this->get_field_value( 'noindex_search_archive' ) ); ?> />
			<label for="<?php echo $this->get_field_id( 'noindex_search_archive' ); ?>"><?php printf( __( 'Apply %s to Search Archives?', 'genesis' ), '<code>noindex</code>' ); ?></label>
		</p>

		<p><span class="description"><?php printf( __( 'Some search engines will cache pages in your site (e.g Google Cache). The %1$s tag will prevent them from doing so. Choose what archives you want to %1$s.', 'genesis' ), '<code>noarchive</code>' ); ?></span></p>

		<p>
			<input type="checkbox" name="<?php echo $this->get_field_name( 'noarchive' ); ?>" id="<?php echo $this->get_field_id( 'noarchive' ); ?>" value="1" <?php checked( $this->get_field_value( 'noarchive' ) ); ?> />
			<label for="<?php echo $this->get_field_id( 'noarchive' ); ?>"><?php printf( __( 'Apply %s to Entire Site?', 'genesis' ), '<code>noarchive</code>' ); ?></label>
		</p>

		<p>
			<input type="checkbox" name="<?php echo $this->get_field_name( 'noarchive_cat_archive' ); ?>" id="<?php echo $this->get_field_id( 'noarchive_cat_archive' ); ?>" value="1" <?php checked( $this->get_field_value( 'noarchive_cat_archive' ) ); ?> />
			<label for="<?php echo $this->get_field_id( 'noarchive_cat_archive' ); ?>"><?php printf( __( 'Apply %s to Category Archives?', 'genesis' ), '<code>noarchive</code>' ); ?></label>
			<br />
			<input type="checkbox" name="<?php echo $this->get_field_name( 'noarchive_tag_archive' ); ?>" id="<?php echo $this->get_field_id( 'noarchive_tag_archive' ); ?>" value="1" <?php checked( $this->get_field_value( 'noarchive_tag_archive' ) ); ?> />
			<label for="<?php echo $this->get_field_id( 'noarchive_tag_archive' ); ?>"><?php printf( __( 'Apply %s to Tag Archives?', 'genesis' ), '<code>noarchive</code>' ); ?></label>
			<br />
			<input type="checkbox" name="<?php echo $this->get_field_name( 'noarchive_author_archive' ); ?>" id="<?php echo $this->get_field_id( 'noarchive_author_archive' ); ?>" value="1" <?php checked( $this->get_field_value( 'noarchive_author_archive' ) ); ?> />
			<label for="<?php echo $this->get_field_id( 'noarchive_author_archive' ); ?>"><?php printf( __( 'Apply %s to Author Archives?', 'genesis' ), '<code>noarchive</code>' ); ?></label>
			<br />
			<input type="checkbox" name="<?php echo $this->get_field_name( 'noarchive_date_archive' ); ?>" id="<?php echo $this->get_field_id( 'noarchive_date_archive' ); ?>" value="1" <?php checked( $this->get_field_value( 'noarchive_date_archive' ) ); ?> />
			<label for="<?php echo $this->get_field_id( 'noarchive_date_archive' ); ?>"><?php printf( __( 'Apply %s to Date Archives?', 'genesis' ), '<code>noarchive</code>' ); ?></label>
			<br />
			<input type="checkbox" name="<?php echo $this->get_field_name( 'noarchive_search_archive' ); ?>" id="<?php echo $this->get_field_id( 'noarchive_search_archive' ); ?>" value="1" <?php checked( $this->get_field_value( 'noarchive_search_archive' ) ); ?> />
			<label for="<?php echo $this->get_field_id( 'noarchive_search_archive' ); ?>"><?php printf( __( 'Apply %s to Search Archives?', 'genesis' ), '<code>noarchive</code>' ); ?></label>
		</p>

		<p><span class="description"><?php printf( __( 'Occasionally, search engines use resources like the Open Directory Project and the Yahoo! Directory to find titles and descriptions for your content. Generally, you will not want them to do this. The %s and %s tags prevent them from doing so.', 'genesis' ), '<code>noodp</code>', '<code>noydir</code>' ); ?></span></p>

		<p>
			<input type="checkbox" name="<?php echo $this->get_field_name( 'noodp' ); ?>" id="<?php echo $this->get_field_id( 'noodp' ); ?>" value="1" <?php checked( $this->get_field_value( 'noodp' ) ); ?> />
			<label for="<?php echo $this->get_field_id( 'noodp' ); ?>"><?php printf( __( 'Apply %s to your site?', 'genesis' ), '<code>noodp</code>' ) ?></label>
			<br />
			<input type="checkbox" name="<?php echo $this->get_field_name( 'noydir' ); ?>" id="<?php echo $this->get_field_id( 'noydir' ); ?>" value="1" <?php checked( $this->get_field_value( 'noydir' ) ); ?> />
			<label for="<?php echo $this->get_field_id( 'noydir' ); ?>"><?php printf( __( 'Apply %s to your site?', 'genesis' ), '<code>noydir</code>' ) ?></label>
		</p>
		<?php

	}

	/**
	 * Callback for SEO Settings Canonical Archives meta box.
	 *
	 * @since 1.3.0
	 *
	 * @uses Genesis_Admin::get_field_name() Construct full field name
	 * @uses Genesis_Admin::get_field_value() Retrieve value of key under $this->settings_field
	 *
	 * @see Genesis_Admin_SEO_Settings::metaboxes()
	 */
	function archives_box() {

		?>
		<p>
			<input type="checkbox" name="<?php echo $this->get_field_name( 'canonical_archives' ); ?>" id="<?php echo $this->get_field_id( 'canonical_archives' ); ?>" value="1" <?php checked( $this->get_field_value( 'canonical_archives' ) ); ?> />
			<label for="<?php echo $this->get_field_id( 'canonical_archives' ); ?>"><?php printf( __( 'Canonical Paginated Archives', 'genesis' ) ); ?></label><br />
			<span class="description"><?php _e( 'This option points search engines to the first page of an archive, if viewing a paginated page. If you do not know what this means, leave it on.', 'genesis' ); ?></span>
		</p>

		<?php

	}

}