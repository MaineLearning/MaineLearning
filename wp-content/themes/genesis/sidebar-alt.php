<?php
/*
 WARNING: This file is part of the core Genesis framework. DO NOT edit
 this file under any circumstances. Please do all modifications
 in the form of a child theme.
 */

/**
 * Handles the secondary sidebar structure.
 *
 * This file is a core Genesis file and should not be edited.
 *
 * @category Genesis
 * @package  Templates
 * @author   StudioPress
 * @license  http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link     http://www.studiopress.com/themes/genesis
 */

?><div id="sidebar-alt" class="sidebar widget-area">
<?php
	genesis_structural_wrap( 'sidebar-alt' );
	do_action( 'genesis_before_sidebar_alt_widget_area' );
	do_action( 'genesis_sidebar_alt' );
	do_action( 'genesis_after_sidebar_alt_widget_area' );
	genesis_structural_wrap( 'sidebar-alt', 'close' );
?>
</div>
