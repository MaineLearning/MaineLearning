<?php
/**
 * Adds the eNews and Updates widget.
 *
 * @category Genesis
 * @package  Widgets
 * @author   StudioPress
 * @license  http://www.opensource.org/licenses/gpl-license.php GPL-2.0+
 * @link     http://www.studiopress.com/themes/genesis
 */

/**
 * Genesis eNews and Updates widget class.
 *
 * @category Genesis
 * @package Widgets
 *
 * @since 0.1.8
 */
class Genesis_eNews_Updates extends WP_Widget {

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
			'title'       => '',
			'text'        => '',
			'id'          => '',
			'input_text'  => __( 'Enter your email address ...', 'genesis' ),
			'button_text' => __( 'Go', 'genesis' ),
		);

		$widget_ops = array(
			'classname'   => 'enews-widget',
			'description' => __( 'Displays Feedburner email subscribe form', 'genesis' ),
		);

		$this->WP_Widget( 'enews', __( 'Genesis - eNews and Updates', 'genesis' ), $widget_ops );

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

		echo $before_widget . '<div class="enews">';

			if ( ! empty( $instance['title'] ) )
				echo $before_title . apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base ) . $after_title;

			echo wpautop( $instance['text'] ); // We run KSES on update

			if ( ! empty( $instance['id'] ) ) : ?>
			<form id="subscribe" action="http://feedburner.google.com/fb/a/mailverify" method="post" target="popupwindow" onsubmit="window.open( 'http://feedburner.google.com/fb/a/mailverify?uri=<?php echo esc_js( $instance['id'] ); ?>', 'popupwindow', 'scrollbars=yes,width=550,height=520');return true">
				<input type="text" value="<?php echo esc_attr( $instance['input_text'] ); ?>" id="subbox" onfocus="if ( this.value == '<?php echo esc_js( $instance['input_text'] ); ?>') { this.value = ''; }" onblur="if ( this.value == '' ) { this.value = '<?php echo esc_js( $instance['input_text'] ); ?>'; }" name="email" />
				<input type="hidden" name="uri" value="<?php echo esc_attr( $instance['id'] ); ?>" />
				<input type="hidden" name="loc" value="<?php echo esc_attr( get_locale() ); ?>" />
				<input type="submit" value="<?php echo esc_attr( $instance['button_text'] ); ?>" id="subbutton" />
			</form>
			<?php endif;

		echo '</div>' . $after_widget;

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

		$new_instance['title'] = strip_tags( $new_instance['title'] );
		$new_instance['text']  = wp_kses( $new_instance['text'], genesis_formatting_allowedtags() );
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

		if ( ! current_user_can( 'install_plugins' ) ) {

			echo '<p class="description">' . __( 'This widget has been deprecated, and should no longer be used.', 'genesis' ) . '</p>';
			echo '<p class="description">' . sprintf( __( 'If you would like to continue to use the eNews widget functionality, please have a site administrator <a href="%s" target="_blank">install this plugin</a> and replace this widget with the Genesis eNews Extended widget.', 'genesis' ), esc_url( 'http://wordpress.org/extend/plugins/genesis-enews-extended/' ) ) . '</p>';

			return;

		}

		add_thickbox();

		echo '<p class="description">' . __( 'This widget has been deprecated, and should no longer be used.', 'genesis' ) . '</p>';
		echo '<p class="description">' . sprintf( __( 'If you would like to continue to use the eNews widget functionality, please <a href="%s" class="thickbox" title="Install Genesis eNews Extended">install this plugin</a> and replace this widget with the Genesis eNews Extended widget.', 'genesis' ), esc_url( network_admin_url( 'plugin-install.php?tab=plugin-information&plugin=genesis-enews-extended&TB_iframe=true&width=660&height=550' ) ) ) . '</p>';

	}

}