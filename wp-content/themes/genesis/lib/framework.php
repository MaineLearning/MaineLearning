<?php
/*
 WARNING: This file is part of the core Genesis framework. DO NOT edit
 this file under any circumstances. Please do all modifications
 in the form of a child theme.
 */

/**
 * Initialize the framework from template files.
 *
 * This file is a core Genesis file and should not be edited.
 *
 * @category Genesis
 * @package  Framework
 * @author   StudioPress
 * @license  http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link     http://www.studiopress.com/themes/genesis
 */

/**
 * Used to initialize the framework in the various template files.
 *
 * It pulls in all the necessary components like header and footer, the basic
 * markup structure, and hooks.
 *
 * @since 1.3.0
 */
function genesis() {

	get_header();

	do_action( 'genesis_before_content_sidebar_wrap' );
	?>
	<div id="content-sidebar-wrap">
		<?php do_action( 'genesis_before_content' ); ?>
		<div id="content" class="hfeed">
			<?php
				do_action( 'genesis_before_loop' );
				do_action( 'genesis_loop' );
				do_action( 'genesis_after_loop' );
			?>
		</div><!-- end #content -->
		<?php do_action( 'genesis_after_content' ); ?>
	</div><!-- end #content-sidebar-wrap -->
	<?php
	do_action( 'genesis_after_content_sidebar_wrap' );

	get_footer();

}
