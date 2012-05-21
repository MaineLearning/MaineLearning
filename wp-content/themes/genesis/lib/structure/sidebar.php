<?php
/**
 * Controls output elements in primary or secondary sidebars.
 *
 * @category   Genesis
 * @package    Structure
 * @subpackage Sidebars
 * @author     StudioPress
 * @license    http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link       http://www.studiopress.com/themes/genesis
 */

add_action( 'genesis_sidebar', 'genesis_do_sidebar' );
/**
 * Echo primary sidebar default content.
 *
 * @since 1.2.0
 */
function genesis_do_sidebar() {

	if ( ! dynamic_sidebar( 'sidebar' ) ) {
		echo '<div class="widget widget_text"><div class="widget-wrap">';
			echo '<h4 class="widgettitle">';
				_e( 'Primary Sidebar Widget Area', 'genesis' );
			echo '</h4>';
			echo '<div class="textwidget"><p>';
				printf( __( 'This is the Primary Sidebar Widget Area. You can add content to this area by visiting your <a href="%s">Widgets Panel</a> and adding new widgets to this area.', 'genesis' ), admin_url( 'widgets.php' ) );
			echo '</p></div>';
		echo '</div></div>';
	}

}

add_action( 'genesis_sidebar_alt', 'genesis_do_sidebar_alt' );
/**
 * Echo alternate sidebar default content.
 *
 * @since 1.2.0
 */
function genesis_do_sidebar_alt() {

	if ( ! dynamic_sidebar( 'sidebar-alt' ) ) {
		echo '<div class="widget widget_text"><div class="widget-wrap">';
			echo '<h4 class="widgettitle">';
				_e( 'Secondary Sidebar Widget Area', 'genesis' );
			echo '</h4>';
			echo '<div class="textwidget"><p>';
				printf( __( 'This is the Secondary Sidebar Widget Area. You can add content to this area by visiting your <a href="%s">Widgets Panel</a> and adding new widgets to this area.', 'genesis' ), admin_url( 'widgets.php' ) );
			echo '</p></div>';
		echo '</div></div>';
	}

}