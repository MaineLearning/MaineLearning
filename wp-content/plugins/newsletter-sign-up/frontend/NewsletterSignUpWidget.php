<?php

if(!class_exists('NewsletterSignUpWidget')) {

	class NewsletterSignUpWidget extends WP_Widget {
				
		function __construct() {
			$widget_ops = array('classname' => 'nsu_widget', 'description' => __('Adds a newsletter sign-up form.'));
			$control_ops = array('width' => 400, 'height' => 350);
			parent::__construct(false, 'Newsletter Sign-Up Widget', $widget_ops, $control_ops);
		}

		function widget($args, $instance) {	
			$NewsletterSignUp = NewsletterSignUp::getInstance();
			/* Get Newsletter Sign-up options */
			$options = get_option('nsu_form');
			
			/* Provide some defaults */
			$defaults = array( 'title' => 'Sign up for our newsletter!', 'text_before_form' => '', 'text_after_form' => '');
			$instance = wp_parse_args( (array) $instance, $defaults );	
			
			extract( $args );
			extract($instance);
			$title = apply_filters('widget_title', $title);
			
			echo $before_widget;
				echo $before_title . $title . $after_title;
					  
					if(!empty($text_before_form)) { 
						?><div class="nsu-text-before-form"><?php
							$instance['filter'] ? _e(wpautop($text_before_form),'nsu-widget') : _e($text_before_form,'nsu-widget'); 
						?></div><?php
					}
					$NewsletterSignUp->output_form(true);
					if(!empty($text_after_form)) {
						?><div class="nsu-text-after-form"><?php
							$instance['filter'] ? _e(wpautop($text_after_form),'nsu-widget') : _e($text_after_form,'nsu-widget'); 
						?></div><?php
					}
						
			echo $after_widget; 
		}

		function update($new_instance, $old_instance) {
			$instance = $old_instance;
			$instance['title'] = strip_tags($new_instance['title']);
			
			if ( current_user_can('unfiltered_html') ) {
				$instance['text_before_form'] =  $new_instance['text_before_form'];
				$instance['text_after_form'] =  $new_instance['text_after_form'];
			} else {
				$instance['text_before_form'] = stripslashes( wp_filter_post_kses( addslashes($new_instance['text_before_form']) ) );
				$instance['text_after_form'] = stripslashes( wp_filter_post_kses( addslashes($new_instance['text_after_form']) ) );
			}
			$instance['filter'] = isset($new_instance['filter']);
			
			return $instance;
		}

		function form($instance) {	
			$defaults = array( 'title' => 'Sign up for our newsletter!', 'text_before_form' => '', 'text_after_form' => '');
			$instance = wp_parse_args( (array) $instance, $defaults );		
			
			extract($instance);
			$title = strip_tags($title);

			?>
                        

			 <p>
			  <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> 
			  <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
			</p>
			
			<label title="You can use the following HTML-codes:  &lt;a&gt;, &lt;strong&gt;, &lt;br /&gt;,&lt;em&gt; &lt;img ..&gt;" for="<?php echo $this->get_field_id('text_before_form'); ?>"><?php _e('Text to show before the form:'); ?></label> 
			<textarea rows="8" cols="10" class="widefat wysiwyg-overlay-toggle" id="<?php echo $this->get_field_id('text_before_form'); ?>" name="<?php echo $this->get_field_name('text_before_form'); ?>"><?php echo $text_before_form; ?></textarea>
			<br />
			<label for="<?php echo $this->get_field_id('text_after_form'); ?>"><?php _e('Text to show after the form:'); ?></label> 
			<textarea rows="8" cols="10" class="widefat wysiwyg-overlay-toggle" id="<?php echo $this->get_field_id('text_after_form'); ?>" name="<?php echo $this->get_field_name('text_after_form'); ?>"><?php echo $text_after_form; ?></textarea>
			
			<p><input id="<?php echo $this->get_field_id('filter'); ?>" name="<?php echo $this->get_field_name('filter'); ?>" type="checkbox" <?php checked(isset($instance['filter']) ? $instance['filter'] : 0); ?> />&nbsp;<label for="<?php echo $this->get_field_id('filter'); ?>"><?php _e('Automatically add paragraphs'); ?></label></p>
			
			<p>
				You can further configure the sign-up form at the <a href="admin.php?page=newsletter-sign-up/form-settings">Newsletter Sign-Up configuration page</a>.
			</p>
               
            <p style="border: 2px solid green; background: #CFC; padding:5px; ">I spent countless hours developing this plugin for FREE (unlike many other Newsletter plugins). If you like it, consider <a href="http://dannyvankooten.com/donate/">donating $10, $20 or $50</a> as a token of your appreciation.</p>       
			<?php 
		}

	}
}