<?php

/**
 * RSS widget class
 *
 * @since 2.8.0
 */
class learning_registry_search extends WP_Widget {

	function __construct() {

		$widget_ops = array( 'description' => __('Searches the learning registry for content') );
                parent::__construct(
	 		'learning_registry_search', // Base ID
			'Learning Registry Search', // Name
		        $widget_ops
		);
	}

	function widget($args, $instance) {
                $url_field = isset( $instance['url_field'] ) ? $instance['url_field'] : '';
                $number_items = isset( $instance['number_items'] ) ? (int) $instance['number_items'] : 3;

                // Don't show the widget if this is not a single page/post
                if ( !is_single() ) {
                        return;
                }

                // If we have no URL field, there's nothing to look up
                if ( empty( $url_field ) ) {
                        return;
                }

                // If we've gotten this far, put an empty widget container on
                // the page, plus hidden IDs with the URL info. The rest is
                // done with AJAX
		?>
                <div id='<?php echo $this->get_field_id('widget') ?>' class="widget widget-learning_registry">
                        <input type="hidden" value="<?php echo esc_attr( $url_field ) ?>" name="lr_url_field" id="lr_url_field" />
                        <input type="hidden" value="<?php echo esc_attr( $number_items ) ?>" name="lr_number_items" id="lr_number_items" />
                        <input type="hidden" value="<?php echo get_the_ID() ?>" name="lr_post_id" id="lr_post_id" />
                </div>

		<?php

	}

	function form($instance) {
                $url_field = isset( $instance['url_field'] ) ? $instance['url_field'] : '';
                $number_items = isset( $instance['number_items'] ) ? $instance['number_items'] : 3;

                ?>

                <div id="<?php echo $this->get_field_id( 'form' ) ?>">
                        <p>
                                <label for="<?php echo $this->get_field_id( 'url_field' ) ?>">URL field</label>
                                <input type="text" name="<?php echo $this->get_field_name( 'url_field' ) ?>" id="<?php echo $this->get_field_id( 'url_field' ) ?>" value="<?php echo esc_attr( $url_field ) ?>" size="25" />
                                <div class="description">The post meta field that stores the resource URL</div>
                        </p>

                        <p>
                                <label for="<?php echo $this->get_field_id( 'number_items' ) ?>">Number of items</label>
                                <input type="text" name="<?php echo $this->get_field_name( 'number_items' ) ?>" id="<?php echo $this->get_field_id( 'number_items' ) ?>" value="<?php echo intval( $number_items ) ?>" />
                        </p>
                </div>

                <?php
	}



	function update($new_instance, $old_instance) {

		$instance = $old_instance;
		$instance['url_field'] = $new_instance['url_field'];
		$instance['number_items'] = (int) $new_instance['number_items'];
		return $instance;

	}

}

 ?>
