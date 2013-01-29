<?php
/**
 * Displays new features after upgrade.
 *
 * @category Genesis
 * @package  Admin
 * @author   StudioPress
 * @license  http://www.opensource.org/licenses/gpl-license.php GPL-2.0+
 * @link     http://www.studiopress.com/themes/genesis
 */

/**
 * Registers a new admin page, providing content and corresponding menu item
 * for the "What's new" page.
 *
 * @category Genesis
 * @package Admin
 *
 * @since 1.9.0
 */
class Genesis_Admin_Upgraded extends Genesis_Admin_Basic {

	/**
	 * Create the page.
	 *
	 * @uses Genesis_Admin::create() Register the admin page
	 *
	 * @since 1.8.0
	 */
	function __construct() {

		$page_id = 'genesis-upgraded';

		$menu_ops = array(
			'submenu' => array(
				'parent_slug' => 'admin.php',
				'menu_title'  => '',
				'page_title'  => sprintf( __( 'Welcome to Genesis %s', 'genesis' ), PARENT_THEME_BRANCH ),
			)
		);

		$this->create( $page_id, $menu_ops );

	}

	/**
	 * Callback for displaying the Genesis Readme admin page.
	 *
	 * Checks if the file contents are readable, and echoes out HTML.
	 *
	 * @since 1.3.0
	 *
	 * @uses CHILD_DIR
	 */
	public function admin() {

		?>
		<div class="wrap about-wrap">

		<img src="<?php echo get_template_directory_uri() . '/lib/admin/images/whats-new.png'; ?>" class="alignright whats-new" />

		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		

		<div class="about-text"><?php printf( __( 'Thank you for updating to the latest version! Using Genesis %s will give you more options than you\'ve ever had and your website will continue to purr like a kitten.', 'genesis' ), PARENT_THEME_BRANCH ); ?></div>

		<div class="changelog">
			<h3><?php _e( 'What&#8217;s New', 'genesis' ); ?></h3>
			<div class="feature-section">
				<img src="http://www.studiopress.com/images/whats-new/new-look.png" class="full-width-image" />
				<h4><?php _e( 'Updated Design and Wider Layout', 'genesis' ); ?></h4>
				<p><?php _e( 'You may notice that the default Genesis style sheet is a bit shorter, and on the front end looks different. We\'ve updated the default design and extended the width of the layout to 1152px. Now you\'ve got more real estate to showcase your content.', 'genesis' ); ?></p>
				<h4><?php _e( 'Updated Security Audit', 'genesis' ); ?></h4>
				<p><?php _e( 'It\'s very important to us that we provide you the absolute best framework possible, so once again we hired WordPress Lead Developer Mark Jaquith to perform a full review of code for security as he\'s done in the past.', 'genesis' ); ?></p>
				<h4><?php _e( 'Google Author Highlights', 'genesis' ); ?></h4>
				<p><?php _e( 'Genesis now offers support for author highlighting. This allows Google to associate your content with your Google+ content. Just edit your profile, find the field where you can enter your Google+ account URL, and save. Genesis does the rest. And if you want to associate an author with the homepage, you can do that in SEO Settings.', 'genesis' ); ?></p>
				<img src="http://www.studiopress.com/images/whats-new/google-author.png" class="full-width-image" />
				<h4><?php _e( 'Deprecating Widgets', 'genesis' ); ?></h4>
				<p><?php _e( 'Genesis has always included some useful widgets to help you build your sites faster and easier. But lately, we realized that some of these widgets should really be plugins, so authors can push out updates more frequently, and you get more up to date code. So, the  "eNews & Updates" and "Latest Tweets" widgets are being ported to plugins, and will eventually be removed from Genesis.', 'genesis' ); ?></p>
				
				<h4><?php _e( 'Genesis Category/Page Menu Widgets Removed', 'genesis' ); ?></h4>
				<p><?php _e( 'We deprecated the Category and Page menu widgets a few releases ago, and as of this release, we\'ve completely removed them from the framework. If you were still using either of these widgets, you will notice that your menu is no longer showing.', 'genesis' ); ?></p>
				<p><?php printf( __( 'You will need to build a <a href="%s">custom menu</a> and add the custom menu widget to the Header Right widget area.', 'genesis' ), admin_url( 'nav-menus.php' ) ); ?></p>
				
				<h4><?php _e( 'Other Geeky Stuff', 'genesis' ); ?></h4>
				<p><?php _e( 'We also fixed a lot of little bugs, improved some things, and generally made the framework a more solid foundation for you to use. We hope you enjoy this latest release!', 'genesis' ); ?></p>
				</div>
		</div>

		<div class="changelog">
			<h3><?php _e( 'Genesis 2.0 Roadmap', 'genesis' ); ?></h3>
			<div class="feature-section">
				<h4><?php _e( 'Support for HTML5 Markup', 'genesis' ); ?></h4>
				<p><?php _e( 'We have big plans for a new markup structure in Genesis 2.0, all built with HTML5.', 'genesis' ); ?></p>

				<h4><?php _e( 'A New Mobile Strategy', 'genesis' ); ?></h4>
				<p><?php _e( 'In case you can\'t tell, we\'re big fans of designing for mobile devices around here. So, we\'re going to be doing some things that will make it a whole lot easier to get your site mobile ready with Genesis in 2.0.', 'genesis' ); ?></p>
			</div>
		</div>

		<div class="project-leads">
			
			<h3><?php _e( 'Project Leads', 'genesis' ); ?></h3>
			
			<ul class="wp-people-group " id="wp-people-group-project-leaders">
			<li class="wp-person" id="wp-person-nathan">
				<a href="http://twitter.com/nathanrice"><img src="http://0.gravatar.com/avatar/fdbd4b13e3bcccb8b48cc18f846efb7f?s=60" class="gravatar" alt="Nathan Rice" /></a>
				<a class="web" href="http://twitter.com/nathanrice">Nathan Rice</a>
				<span class="title"><?php _e( 'Lead Developer', 'genesis' ); ?></span>
			</li>
			<li class="wp-person" id="wp-person-ron">
				<a href="http://twitter.com/sillygrampy"><img src="http://0.gravatar.com/avatar/7b8ff059b9a4504dfbaebd4dd190466e?s=60" class="gravatar" alt="Ron Rennick" /></a>
				<a class="web" href="http://twitter.com/sillygrampy">Ron Rennick</a>
				<span class="title"><?php _e( 'Lead Developer', 'genesis' ); ?></span>
			</li>
			<li class="wp-person" id="wp-person-brian">
				<a href="http://twitter.com/bgardner"><img src="http://0.gravatar.com/avatar/c845c86ebe395cea0d21c03bc4a93957?s=60" class="gravatar" alt="Brian Gardner" /></a>
				<a class="web" href="http://twitter.com/bgardner">Brian Gardner</a>
				<span class="title"><?php _e( 'Lead Developer', 'genesis' ); ?></span>
			</li>
			</ul>
		
		</div>
		
		<div class="contributers">
			
			<h3><?php _e( 'Contributors', 'genesis' ); ?></h3>
			
			<ul class="wp-people-group " id="wp-people-group-project-leaders">
			<li class="wp-person" id="wp-person-jared">
				<a href="http://twitter.com/jaredatch"><img src="http://0.gravatar.com/avatar/e341eca9e1a85dcae7127044301b4363?s=60" class="gravatar" alt="Jared Atchison" /></a>
				<a class="web" href="http://twitter.com/jaredatch">Jared Atchison</a>
				<span class="title"><?php _e( 'Contributor', 'genesis' ); ?></span>
			</li>
			<li class="wp-person" id="wp-person-chris">
				<a href="http://twitter.com/tweetsfromchris"><img src="http://0.gravatar.com/avatar/aa0bea067ea6bfb854387d73f595aa1c?s=60" class="gravatar" alt="Chris Cochran" /></a>
				<a class="web" href="http://twitter.com/tweetsfromchris">Chris Cochran</a>
				<span class="title"><?php _e( 'Contributor', 'genesis' ); ?></span>
			</li>
			<li class="wp-person" id="wp-person-nick">
				<a href="http://twitter.com/nick_thegeek"><img src="http://0.gravatar.com/avatar/3241d4eab93215b5487e162b87569e42?s=60" class="gravatar" alt="Nick Croft" /></a>
				<a class="web" href="http://twitter.com/nick_thegeek">Nick Croft</a>
				<span class="title"><?php _e( 'Contributor', 'genesis' ); ?></span>
			</li>
			<li class="wp-person" id="wp-person-david">
				<a href="http://twitter.com/deckerweb"><img src="http://0.gravatar.com/avatar/28d02f8d09fc32fccc0282efdc23a4e5?s=60" class="gravatar" alt="David Decker" /></a>
				<a class="web" href="http://twitter.com/deckerweb">David Decker</a>
				<span class="title"><?php _e( 'Contributor', 'genesis' ); ?></span>
			</li>
			<li class="wp-person" id="wp-person-bill">
				<a href="http://twitter.com/billerickson"><img src="http://0.gravatar.com/avatar/ae510affa31e5b946623bda4ff969b67?s=60" class="gravatar" alt="Bill Erickson" /></a>
				<a class="web" href="http://twitter.com/billerickson">Bill Erickson</a>
				<span class="title"><?php _e( 'Contributor', 'genesis' ); ?></span>
			</li>
			<li class="wp-person" id="wp-person-thomas">
				<a href="http://twitter.com/jthomasgriffin"><img src="http://0.gravatar.com/avatar/fe4225114bfd1f8993c6d20d32227537?s=60" class="gravatar" alt="Thomas Griffin" /></a>
				<a class="web" href="http://twitter.com/jthomasgriffin">Thomas Griffin</a>
				<span class="title"><?php _e( 'Contributor', 'genesis' ); ?></span>
			</li>
			<li class="wp-person" id="wp-person-gary">
				<a href="http://twitter.com/garyj"><img src="http://0.gravatar.com/avatar/e70d4086e89c2e1e081870865be68485?s=60" class="gravatar" alt="Gary Jones" /></a>
				<a class="web" href="http://twitter.com/garyj">Gary Jones</a>
				<span class="title"><?php _e( 'Contributor', 'genesis' ); ?></span>
			</li>
			<li class="wp-person" id="wp-person-andrew">
				<a href="http://twitter.com/norcross"><img src="http://0.gravatar.com/avatar/26ab8f9b2c86b10e7968b882403b3bf8?s=60" class="gravatar" alt="Andrew Norcross" /></a>
				<a class="web" href="http://twitter.com/norcross">Andrew Norcross</a>
				<span class="title"><?php _e( 'Contributor', 'genesis' ); ?></span>
			</li>
			<li class="wp-person" id="wp-person-rafal">
				<a href="http://twitter.com/rafaltomal"><img src="http://0.gravatar.com/avatar/c9f7b936cd19bd5aba8831ddea21f05d?s=60" class="gravatar" alt="Rafal Tomal" /></a>
				<a class="web" href="http://twitter.com/rafaltomal">Rafal Tomal</a>
				<span class="title"><?php _e( 'Contributor', 'genesis' ); ?></span>
			</li>
			<li class="wp-person" id="wp-person-travis">
				<a href="http://twitter.com/wp_smith"><img src="http://0.gravatar.com/avatar/7e673cdf99e6d7448f3cbaf1424c999c?s=60" class="gravatar" alt="Travis Smith" /></a>
				<a class="web" href="http://twitter.com/wp_smith">Travis Smith</a>
				<span class="title"><?php _e( 'Contributor', 'genesis' ); ?></span>
			</li>
			</ul>
		
		</div>

		<div class="return-to-dashboard">
			<p><a href="<?php echo esc_url( menu_page_url( 'genesis', 0 ) ); ?>"><?php _e( 'Go to Theme Settings &rarr;', 'genesis' ); ?></a></p>
			<p><a href="<?php echo esc_url( menu_page_url( 'seo-settings', 0 ) ); ?>"><?php _e( 'Go to SEO Settings &rarr;', 'genesis' ); ?></a></p>
		</div>

		</div>
		<?php

	}

}