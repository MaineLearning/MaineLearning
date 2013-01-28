<?php
/**
 * Adds the User Profile Widget.
 *
 * @category Genesis
 * @package  Widgets
 * @author   StudioPress
 * @license  http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link     http://www.studiopress.com/themes/genesis
 */

/**
 * Genesis User Profile widget class.
 *
 * @category Genesis
 * @package Widgets
 *
 * @since 0.1.8
 */
class Genesis_User_Profile_Widget extends WP_Widget {

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
			'title'          => '',
			'alignment'	     => 'left',
			'user'           => '',
			'size'           => '45',
			'author_info'    => '',
			'bio_text'       => '',
			'page'           => '',
			'page_link_text' => __( 'Read More', 'genesis' ) . '&#x02026;',
			'posts_link'     => '',
		);

		$widget_ops = array(
			'classname'   => 'user-profile',
			'description' => __( 'Displays user profile block with Gravatar', 'genesis' ),
		);

		$control_ops = array(
			'id_base' => 'user-profile',
			'width'   => 200,
			'height'  => 250,
		);

		$this->WP_Widget( 'user-profile', __( 'Genesis - User Profile', 'genesis' ), $widget_ops, $control_ops );

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

			if ( ! empty( $instance['title'] ) )
				echo $before_title . apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base ) . $after_title;

			$text = '';

			if ( ! empty( $instance['alignment'] ) )
				$text .= '<span class="align' . esc_attr( $instance['alignment'] ) . '">';

			$text .= get_avatar( $instance['user'], $instance['size'] );

			if( ! empty( $instance['alignment'] ) )
				$text .= '</span>';

			if ( $instance['author_info'] == 'text' )
				$text .= $instance['bio_text']; // We run KSES on update
			else
				$text .= get_the_author_meta( 'description', $instance['user'] );

			$text .= $instance['page'] ? sprintf( ' <a class="pagelink" href="%s">%s</a>', get_page_link( $instance['page'] ), $instance['page_link_text'] ) : '';

			$text .= $instance['posts_link'] ? sprintf( '<div class="posts_link posts-link"><a href="%s">%s</a></div>', get_author_posts_url( $instance['user'] ), __( 'View My Blog Posts', 'genesis' ) ) : '';

			echo wpautop( $text );

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

		$new_instance['title']          = strip_tags( $new_instance['title'] );
		$new_instance['bio_text']       = current_user_can( 'unfiltered_html' ) ? $new_instance['bio_text'] : genesis_formatting_kses( $new_instance['bio_text'] );
		$new_instance['page_link_text'] = strip_tags( $new_instance['page_link_text'] );

		return $new_instance;

	}

	/**
	 * Echo the settings update form.
	 *
	 * @param array $instance Current settings
	 */
	function form( $instance ) {

		/** Merge with defaults */
		$instance = wp_parse_args( (array) $instance, $this->defaults );

		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'genesis' ); ?>:</label>
			<input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" class="widefat" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_name( 'user' ); ?>"><?php _e( 'Select a user. The email address for this account will be used to pull the Gravatar image.', 'genesis' ); ?></label><br />
			<?php wp_dropdown_users( array( 'who' => 'authors', 'name' => $this->get_field_name( 'user' ), 'selected' => $instance['user'] ) ); ?>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'size' ); ?>"><?php _e( 'Gravatar Size', 'genesis' ); ?>:</label>
			<select id="<?php echo $this->get_field_id( 'size' ); ?>" name="<?php echo $this->get_field_name( 'size' ); ?>">
				<?php
				$sizes = array( __( 'Small', 'genesis' ) => 45, __( 'Medium', 'genesis' ) => 65, __( 'Large', 'genesis' ) => 85, __( 'Extra Large', 'genesis' ) => 125 );
				$sizes = apply_filters( 'genesis_gravatar_sizes', $sizes );
				foreach ( (array) $sizes as $label => $size ) { ?>
					<option value="<?php echo absint( $size ); ?>" <?php selected( $size, $instance['size'] ); ?>><?php printf( '%s (%spx)', $label, $size ); ?></option>
				<?php } ?>
			</select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'alignment' ); ?>"><?php _e( 'Gravatar Alignment', 'genesis' ); ?>:</label>
			<select id="<?php echo $this->get_field_id( 'alignment' ); ?>" name="<?php echo $this->get_field_name( 'alignment' ); ?>">
				<option value="">- <?php _e( 'None', 'genesis' ); ?> -</option>
				<option value="left" <?php selected( 'left', $instance['alignment'] ); ?>><?php _e( 'Left', 'genesis' ); ?></option>
				<option value="right" <?php selected( 'right', $instance['alignment'] ); ?>><?php _e( 'Right', 'genesis' ); ?></option>
			</select>
		</p>

		<fieldset>
			<legend><?php _e( 'Select which text you would like to use as the author description', 'genesis' ); ?></legend>
			<p>
				<input type="radio" name="<?php echo $this->get_field_name( 'author_info' ); ?>" id="<?php echo $this->get_field_id( 'author_info' ); ?>_val1" value="bio" <?php checked( $instance['author_info'], 'bio' ); ?>/>
				<label for="<?php echo $this->get_field_id( 'author_info' ); ?>_val1"><?php _e( 'Author Bio', 'genesis' ); ?></label><br />
				<input type="radio" name="<?php echo $this->get_field_name( 'author_info' ); ?>" id="<?php echo $this->get_field_id( 'author_info' ); ?>_val2" value="text" <?php checked( $instance['author_info'], 'text' ); ?>/>
				<label for="<?php echo $this->get_field_id( 'author_info' ); ?>_val2"><?php _e( 'Custom Text (below)', 'genesis' ); ?></label><br />
				<label for="<?php echo $this->get_field_id( 'bio_text' ); ?>" class="screen-reader-text"><?php _e( 'Custom Text Content', 'genesis' ); ?></label>
				<textarea id="<?php echo $this->get_field_id( 'bio_text' ); ?>" name="<?php echo $this->get_field_name( 'bio_text' ); ?>" class="widefat" rows="6" cols="4"><?php echo htmlspecialchars( $instance['bio_text'] ); ?></textarea>
			</p>
		</fieldset>

		<p>
			<label for="<?php echo $this->get_field_name( 'page' ); ?>"><?php _e( 'Choose your extended "About Me" page from the list below. This will be the page linked to at the end of the about me section.', 'genesis' ); ?></label><br />
			<?php wp_dropdown_pages( array( 'name' => $this->get_field_name( 'page' ), 'show_option_none' => __( 'None', 'genesis' ), 'selected' => $instance['page'] ) ); ?>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'page_link_text' ); ?>"><?php _e( 'Extended page link text', 'genesis' ); ?>:</label>
			<input type="text" id="<?php echo $this->get_field_id( 'page_link_text' ); ?>" name="<?php echo $this->get_field_name( 'page_link_text' ); ?>" value="<?php echo esc_attr( $instance['page_link_text'] ); ?>" class="widefat" />
		</p>

		<p>
			<input id="<?php echo $this->get_field_id( 'posts_link' ); ?>" type="checkbox" name="<?php echo $this->get_field_name( 'posts_link' ); ?>" value="1" <?php checked( $instance['posts_link'] ); ?>/>
			<label for="<?php echo $this->get_field_id( 'posts_link' ); ?>"><?php _e( 'Show Author Archive Link?', 'genesis' ); ?></label>
		</p>
		<?php

	}

}