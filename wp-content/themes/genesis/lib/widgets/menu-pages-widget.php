<?php
/**
 * Adds the Page Navigation Menu widget.
 *
 * @category Genesis
 * @package  Widgets
 * @author   StudioPress
 * @license  http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link     http://www.studiopress.com/themes/genesis
 */

/**
 * Genesis Pages Menu widget class.
 *
 * @category Genesis
 * @package Widgets
 *
 * @since 0.1.8
 */
class Genesis_Menu_Pages_Widget extends WP_Widget {

	/**
	 * Holds widget settings defaults, populated in constructor.
	 *
	 * @var array
	 */
	protected $defaults;

	/**
	 * Constructor. Set the default widget options and create widget.
	 */
	function __construct() {

		$this->defaults = array(
			'title'		=> '',
			'include'	=> array(),
			'order'		=> '',
		);

		$widget_ops = array(
			'classname'   => 'menupages',
			'description' => __( 'This widget has been deprecated, and will eventually be removed. DO NOT use it. You have been warned.', 'genesis' ),
		);

		$control_ops = array(
			'id_base' => 'menu-pages',
			'width'   => 200,
			'height'  => 250,
		);

		$this->WP_Widget( 'menu-pages', __( 'Genesis - Page Menu', 'genesis' ), $widget_ops, $control_ops );
	}

	/**
	 * Echo the widget content.
	 *
	 * @param array $args Display arguments including before_title, after_title, before_widget, and after_widget.
	 * @param array $instance The settings for the particular instance of the widget
	 */
	function widget( $args, $instance ) {

		extract( $args );

		/** Merge with defaults */
		$instance = wp_parse_args( (array) $instance, $this->defaults );

		echo $before_widget;

		if ( $instance['title'] ) echo $before_title . apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base ) . $after_title;

		echo '<ul class="nav">'."\n";

		// Empty fallback (default)
		if ( empty($instance['include'] ) ) :
			$instance['include'][] = 'home';
			$pages = get_pages();
			foreach ( (array) $pages as $page ) {
				$instance['include'][] = $page->ID;
			}
		endif;

		// Show Home Link?
		if ( in_array( 'home', (array) $instance['include'] ) ) {
			$active = is_front_page() ? 'class="current_page_item"' : '';
			echo '<li ' . $active . '><a href="' . trailingslashit( home_url() ) . '">' . __( 'Home', 'genesis' ) . '</a></li>';
		}
		// Show Page Links?
		wp_list_pages(
			array(
				'title_li'		=> '',
				'include'		=> implode( ',', $instance['include'] ),
				'sort_column'	=> $instance['order'],
			)
		);

		echo '</ul>'."\n";

		echo $after_widget;

	}

	/**
	 * Update a particular instance.
	 *
	 * This function should check that $new_instance is set correctly.
	 * The newly calculated value of $instance should be returned.
	 * If "false" is returned, the instance won't be saved/updated.
	 *
	 * @param array $new_instance New settings for this instance as input by the user via form()
	 * @param array $old_instance Old settings for this instance
	 * @return array Settings to save or bool false to cancel saving
	 */
	function update( $new_instance, $old_instance ) {

		return $old_instance; /** So we don't lose current options */

	}

	/**
	 * Echo the settings update form.
	 *
	 * @param array $instance Current settings
	 */
	function form( $instance ) {

		$message = sprintf( __( 'This widget has been deprecated, and will eventually be removed. We suggest that you <a href="%s">create a menu</a> and use the "Custom Menu" widget instead.', 'genesis' ), admin_url( 'nav-menus.php' ) );

		printf( '<p class="description">%s</p>', $message );

	}
	
}