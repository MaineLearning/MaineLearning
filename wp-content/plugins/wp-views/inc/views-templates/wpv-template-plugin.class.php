<?php

require WPV_PATH_EMBEDDED . '/inc/views-templates/wpv-template.class.php';

class WPV_template_plugin extends WPV_template {

	function add_view_template_settings() {
        
        ?>
        <script type="text/javascript">
			jQuery(document).ready(function($){
				
				// remove the "Save Draft" and "Preview" buttons.
				jQuery('#minor-publishing-actions').hide();
				jQuery('#misc-publishing-actions').hide();
				jQuery('#publishing-action input[name=publish]').val('<?php _e("Save", 'wpv-views'); ?>');
				
			});
        </script>

        <?php
        
        global $post;
        add_meta_box('views_template', __('View Template Settings', 'wpv-views'), array($this,'view_settings_meta_box'), $post->post_type, 'side', 'high');
        
    }
    
    function view_settings_meta_box() {

        global $post;
        
        $output_mode = get_post_meta($post->ID, '_wpv_view_template_mode', true);
        if (!$output_mode) {
            $output_mode = 'WP_mode';
        }

        if ($output_mode == 'raw_mode') {        
            // Simulate a click to the HTML button to get it to show the
            // control buttons.
            ?>
            <script type="text/javascript">
                jQuery(document).ready(function($){
                    wpv_disable_visual_editor();
                });
                
            </script>
            <?php
        }
        
        ?>

        <script type="text/javascript">
            function wpv_disable_visual_editor() {
                if (jQuery('#edButtonHTML').length != 0) {
                    jQuery('#edButtonHTML').trigger('click');
                }
                if (jQuery('#content-html').length != 0) {
                    jQuery('#content-html').trigger('click');
                    jQuery('#content-tmce').hide();
                    jQuery('#content_parent').hide();
                }
            }
            function wpv_enable_visual_editor() {
                if (jQuery('#content-html').length != 0) {
                    jQuery('#content-tmce').show();
                }
            }
        </script>
        
        <ul>
            
            <?php $checked = $output_mode == 'WP_mode' ? ' checked="checked"' : ''; ?>
            <li><label><input type="radio" name="_wpv_view_template_mode[]" value="WP_mode" <?php echo $checked; ?> onclick="wpv_enable_visual_editor()">&nbsp;<?php _e('Normal WordPress output - add paragraphs an breaks and resolve shortcodes', 'wpv-views'); ?></label></li>
            <?php $checked = $output_mode == 'raw_mode' ? ' checked="checked"' : ''; ?>
            <li><label><input type="radio" name="_wpv_view_template_mode[]" value="raw_mode" <?php echo $checked; ?> onclick="wpv_disable_visual_editor()">&nbsp;<?php _e('Raw output - only resolve shortcodes without adding line breaks or paragraphs'); ?></label></li>
        </ul>
        
        <?php
        
    }
    
	/**
	 * Add admin css to the view template edit page
	 *
	 */
	
    function include_admin_css() {
        global $pagenow;
        if ($pagenow == 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] == 'view-template') {
            $link_tag = '<link rel="stylesheet" href="'. WPV_URL . '/res/css/wpv-views.css?v='.WPV_VERSION.'" type="text/css" media="all" />';
            echo $link_tag;
        }
    }

    function save_post_actions($pidd, $post) {

        if ($post->post_type == 'view-template') {
            if (isset($_POST['_wpv_view_template_mode'][0])) {
                update_post_meta($pidd, '_wpv_view_template_mode', $_POST['_wpv_view_template_mode'][0]);
            }

        }
        
        // pass to the base class.
        parent::save_post_actions($pidd, $post);
    }

	/**
	 * If the post has a view template
	 * add an view template edit link to post.
	 */
	
	function edit_post_link($link, $post_id) {
		
		$template_selected = get_post_meta($post_id, '_views_template', true);
        
        if ($template_selected) {
			remove_filter('edit_post_link', array($this, 'edit_post_link'), 10, 2);
			
			ob_start();
			
			edit_post_link(__('Edit view template', 'wpv-views'), '', '', $template_selected);
			
			$link = $link . ' ' . ob_get_clean();
			
			add_filter('edit_post_link', array($this, 'edit_post_link'), 10, 2);
		}
		
		return $link;
	}

	/**
	 * Ajax function to set the current view template to posts of a type
	 * set in $_POST['type']
	 *
	 */
	
    function ajax_action_callback() {
        global $wpdb;
    
        if ( empty($_POST) || !wp_verify_nonce('set_view_template', $_POST['wpnonce']) ) {

            $view_template_id = $_POST['view_template_id'];
            $type = $_POST['type'];
 
			list($join, $cond) = $this->_get_wpml_sql($type, $_POST['lang']);

            $posts = $wpdb->get_col("SELECT {$wpdb->posts}.ID FROM {$wpdb->posts} {$join} WHERE post_type='{$type}' {$cond}");
                    
            $count = sizeof($posts);
            $updated_count = 0;
            if ($count > 0) {
                foreach($posts as $post) {
                    $template_selected = get_post_meta($post, '_views_template', true);
                    if ($template_selected != $view_template_id) {
                        update_post_meta($post, '_views_template', $view_template_id);
                        $updated_count += 1;
                    }
                }
            }
            
            echo $updated_count;
        }        
        die(); // this is required to return a proper result
    }

    function clear_legacy_view_settings() {
        global $wpdb;

        $wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key='_views_template_new_type'");
    }
    
    function legacy_view_settings($options) {
        global $wpdb;
        
        $view_tempates_new = $wpdb->get_results("SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key='_views_template_new_type'");
        
        foreach($view_tempates_new as $template_for_new) {
            $value = unserialize($template_for_new->meta_value);
            if ($value) {
                foreach($value as $type => $status) {
                    if ($status) {
                        $options['views_template_for_' . $type] = $template_for_new->post_id;
                    }
                }
            }
        }
        
                
        return $options;    
    }
    
    function admin_settings($options) {
        global $wpdb;
        
        $items_found = array();
        
        $options = $this->legacy_view_settings($options);
        
        ?>
        
        <h3 class="title"><?php _e('View Template settings for Taxonomy archive loops', 'wpv-views'); ?></h3>
        <div style="margin-left:20px;">
            <table class="widefat" style="width:auto;">
                <thead>
                    <tr>
                        <th><?php _e('Loop'); ?></th>
                        <th><?php _e('Use this View Template', 'wpv-views'); ?></th>
                    </tr>
                </thead>
                        
                <tbody>
                    
                    <?php
                    
                        $taxonomies = get_taxonomies('', 'objects');
                        foreach ($taxonomies as $category_slug => $category) {
                            if ($category_slug == 'nav_menu' || $category_slug == 'link_category'
                                    || $category_slug == 'post_format') {
                                continue;
                            }
                            $name = $category->name;
                            ?>
                            <tr>
                                <td><?php echo $name; ?></td>
                                <td>
                                    <?php
                                        if (!isset($options['views_template_loop_' . $name ])) {
                                            $options['views_template_loop_' . $name ] = '0';
                                        }
                                        $template = $this->get_view_template_select_box('', $options['views_template_loop_' . $name ]);
                                        $template = str_replace('name="views_template" id="views_template"', 'name="views_template_loop_' . $name . '" id="views_template_loop_' . $name . '"', $template);
                                        echo $template;

                                        $most_popular_term = $wpdb->get_var("SELECT term_id FROM {$wpdb->term_taxonomy} WHERE taxonomy = '{$name}' AND count = (SELECT MAX(count) FROM {$wpdb->term_taxonomy} WHERE taxonomy = '{$name}')");
                                        if ($most_popular_term) {
                                            $link = get_term_link(intval($most_popular_term), $name);
                                            ?>
                                            <a id="views_template_loop_preview_<?php echo $name?>" class="button" target="_blank" href="<?php echo $link; ?>" ><? _e('Preview', 'wpv-view'); ?></a>
                                            <?php
                                        }
                                    ?>
                                </td>
                            </tr>
                            <?php
                        }
                            
                    
                    ?>
                </tbody>
            </table>
        </div>

        <?php $post_types = get_post_types(array('public'=>true), 'objects'); ?>
        
        <br />
        <h3 class="title"><?php _e('View Template for Post Types', 'wpv-views'); ?></h3>
        <div style="margin-left:20px;">
            <table class="widefat" style="width:auto;">
                <thead>
                    <tr>
                        <th><?php _e('Post Types'); ?></th>
                        <th><?php _e('Use this View Template (Single)', 'wpv-views'); ?></th>
                        <th><?php _e('Usage', 'wpv-views'); ?></th>
                        <th><?php _e('Use this View Template (Archive loop)', 'wpv-views'); ?></th>
                    </tr>
                </thead>
                        
                <tbody>
                    <?php
                        foreach($post_types as $post_type) {
                            $type = $post_type->name;
                            ?>
                            <tr>
                                <td><?php echo $type; ?></td>
                                <td>
                                    <?php
                                        if (!isset($options['views_template_for_' . $type ])) {
                                            $options['views_template_for_' . $type ] = 0;
                                        }
                                        $template = $this->get_view_template_select_box('', $options['views_template_for_' . $type ]);
                                        $template = str_replace('name="views_template" id="views_template"', 'name="views_template_for_' . $type . '" id="views_template_for_' . $type . '"', $template);
                                        echo $template;
                                        // add a preview button
                                        // preview the latest post of this type.
                            			list($join, $cond) = $this->_get_wpml_sql($type);
                                        $post_id = $wpdb->get_var("SELECT MAX({$wpdb->posts}.ID) FROM {$wpdb->posts} {$join} WHERE post_type='{$type}' AND post_status in ('publish') {$cond}");
                                        if ($post_id) {
                                            $link = get_permalink($post_id);
                                            ?>
                                            <a id="views_template_for_preview_<?php echo $type?>" class="button" target="_blank" href="<?php echo $link; ?>" ><? _e('Preview', 'wpv-view'); ?></a>
                                            <?php
                                        }
                                        ?>
                                    
                                </td>
                                <td>
                                    <?php
                                    if ($options['views_template_for_' . $type ]) {
                                    
                            			list($join, $cond) = $this->_get_wpml_sql($type);
                                        $posts = $wpdb->get_col("SELECT {$wpdb->posts}.ID FROM {$wpdb->posts} {$join} WHERE post_type='{$type}' {$cond}");
                                        
                                        $count = sizeof($posts);
                                        if ($count > 0) {
                                            $posts = "'" . implode("','", $posts) . "'";
                                            
                    
                                            $set_count = $wpdb->get_var("SELECT COUNT(post_id) FROM {$wpdb->postmeta} WHERE meta_key='_views_template' AND meta_value='{$options['views_template_for_' . $type ]}' AND post_id IN ({$posts})");
                                            if ($set_count < $count) {
                                                echo '<div id="wpv_diff_template_' . $type . '">';
                                                echo '<p id="wpv_diff_' . $type . '">';
                                                echo sprintf(__('%d %ss use a different template:', 'wpv-views'), $count - $set_count, $type);
                                                echo '<input type="button" id="wpv_update_now_' . $type . '" class="button-secondary" value="' . esc_html(sprintf(__('Update all %ss now', 'wpv-views'), $type)) . '" />';
                                                echo '<img id="wpv_update_loading_' . $type . '" src="' . WPV_URL . '/res/img/ajax-loader.gif" width="16" height="16" style="display:none" alt="loading" />';
                                                echo '</p>';
                                                echo '<p id="wpv_updated_' . $type . '" style="display:none">';
                                                echo sprintf(__('<span id="%s">%d</span> %ss have updated to use this template.', 'wpv-views'), 'wpv_updated_count_' . $type, $count - $set_count, $type);
                                                echo '</p>';
                                                echo '</div>';
                                                $items_found[] = $type;
                                            } else {
                                                echo '<p>' . sprintf(__('All %s are using this template', 'wpv-views'), $post_type->labels->name) . '</p>';
                                            }
                                        } else {
                                            echo '<p>' . sprintf(__('There are no %s', 'wpv-views'), $post_type->labels->name) . '</p>';
                                        }
                                    } else {
                                        echo '<p>' . sprintf(__('No template selected for %s', 'wpv-views'), $post_type->labels->name) . '</p>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                        if (!isset($options['views_template_archive_for_' . $type ])) {
                                            $options['views_template_archive_for_' . $type ] = 0;
                                        }
                                        $template = $this->get_view_template_select_box('', $options['views_template_archive_for_' . $type ]);
                                        $template = str_replace('name="views_template" id="views_template"', 'name="views_template_archive_for_' . $type . '" id="views_template_archive_for_' . $type . '"', $template);
                                        echo $template;
                                        ?>
                                    
                                </td>
                            </tr>
                            <?php

                        }
                    ?>
                </tbody>
            </table>
                       
        </div>

        <?php
        
            if (sizeof($items_found) > 0) {
                
                wp_nonce_field( 'set_view_template', 'set_view_template');
                
                // we need to add some javascript
                
                ?>
                <script type="text/javascript" >
                <?php
				
				$lang = '';
				global $sitepress;
				if (isset($sitepress)) {
					$lang = $sitepress->get_current_language();
				}
				
                foreach($items_found as $type) {
                    ?>
                    
                        jQuery('#wpv_update_now_<?php echo $type; ?>').click(function() {
                            jQuery('#wpv_update_loading_<?php echo $type; ?>').show();
                            var data = {
                                action : 'set_view_template',
                                view_template_id : '<?php echo $options['views_template_for_' . $type ]; ?>',
                                wpnonce : jQuery('#set_view_template').attr('value'),
                                type : '<?php echo $type; ?>',
								lang : '<?php echo $lang; ?>'
                            };
                            
                            jQuery.post(ajaxurl, data, function(response) {
                                jQuery('#wpv_updated_count_<?php echo $type; ?>').html(response);
                                jQuery('#wpv_updated_<?php echo $type; ?>').fadeIn();
                                jQuery('#wpv_diff_<?php echo $type; ?>').hide();
                            });
                        })
                        
                    <?php
                }
                
                ?>
                </script>
                <?php
            }
        
    }
    
    function submit($options) {
        $this->clear_legacy_view_settings();
        
        foreach($_POST as $index => $value) {
            if (strpos($index, 'views_template_loop_') === 0) {
                $options[$index] = $value;
            }
            if (strpos($index, 'views_template_for_') === 0) {
                $options[$index] = $value;
            }
            if (strpos($index, 'views_template_archive_for_') === 0) {
                $options[$index] = $value;
            }
        }
        
        return $options;
    }
    
}

