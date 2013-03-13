<?php

/**
 * Media popup functions and filters.
 */

if ( isset( $_GET['wpv-media-insert'] ) || isset( $_GET['wpv-media-edit'] ) ) { // Button "Add Media"
	// Remove unwanted tabs
	add_filter('media_upload_tabs', 'wpv_media_upload_tabs_filter');
	// Remove "Insert into post" button and add button-secondary class to Delete link
	add_action( 'admin_head-media-upload-popup', 'wpv_media_popup_changes' );
}

function wpv_layout_taxonomy_V($menu) {
    
    // remove post items and add taxonomy items.
    
    global $wpv_shortcodes;
    
    $basic = __('Basic', 'wpv-views');
    $menu = array($basic => array());
    
    $taxonomy = array('wpv-taxonomy-title',
                      'wpv-taxonomy-link',
                      'wpv-taxonomy-url',
                      'wpv-taxonomy-description',
                      'wpv-taxonomy-post-count');

    foreach ($taxonomy as $key) {
        $menu[$basic][$wpv_shortcodes[$key][1]] = array($wpv_shortcodes[$key][1],
                                                                        $wpv_shortcodes[$key][0],
                                                                        $basic,
                                                                        '');
    }    
    return $menu;

}

/*
  
    Add controls to the admin page for specifying the layout_meta_html
    
*/

function wpv_layout_meta_html_admin($post, $view_layout_settings) {
    global $WP_Views;
    
    $view_settings = $WP_Views->get_view_settings($post->ID);
    
    
    $defaults = array('layout_meta_html' => '',
                      'generated_layout_meta_html' => '');
    $view_layout_settings = wp_parse_args($view_layout_settings, $defaults);
    
    ?>
        <div id="wpv_layout_meta_html_admin">
	    <input type="hidden" name="_wpv_settings[layout_meta_html_state][html]" id="wpv_layout_meta_html_state" value="<?php echo isset($view_settings['layout_meta_html_state']['html']) ? $view_settings['layout_meta_html_state']['html'] : 'off'; ?>" />
            <div id="wpv_layout_meta_html_admin_show">
                <p><i><?php echo __('The layout-style and fields that you selected generate meta HTML. This meta HTML includes shortcodes and HTML, which you can edit, to fully customize the appearance of this View\'s content output section.', 'wpv-views'); ?></i></p>
                <input type="button" class="button-secondary" onclick="wpv_view_layout_meta_html()" value="<?php _e('View/Edit Meta HTML', 'wpv-views'); ?>" />
            </div>
            <div id="wpv_layout_meta_html_admin_edit" style="background:<?php echo WPV_EDIT_BACKGROUND;?>;display:none">
                <div style="margin:10px 10px 10px 10px;">
                    <p><?php _e('<strong>Meta HTML</strong> - This is used to layout the posts found. It gets generated from the View Layout settings and can be modified to suit.', 'wpv-views'); ?></p>
                    <div id="wpv_layout_meta_html_content_error" class="wpv_form_errors" style="display:none;">
                        <p><?php _e("Changes can't be applied. It appears that you made manual modifications to the Meta HTML.", 'wpv-views'); ?></p>
                        <a style="cursor:pointer;margin-bottom:10px;" onclick="wpv_layout_meta_html_generate_new()"><strong><?php echo __('Generate the new layout content', 'wpv-views'); ?></strong></a> <?php _e('(your edits will be displayed and you can apply them again)', 'wpv-views'); ?>
                    </div>
           
                    <?php
                        $show = $view_settings['query_type'][0] == 'posts' ? 'style="display:inline"' : 'style="display:none"';
                    ?>
                    <div id="wpv-layout-v-icon-posts" <?php echo $show;?>>
                    <?php echo $WP_Views->editor_addon->add_form_button('', 'wpv_layout_meta_html_content', true, true); ?>
                    </div>
                    
                    <?php
                        $show = $view_settings['query_type'][0] == 'taxonomy' ? 'style="display:inline"' : 'style="display:none"';
                    ?>
                    <div id="wpv-layout-v-icon-taxonomy" <?php echo $show;?>>
                    <?php
                        // add a "V" icon for taxonomy
                        remove_filter('editor_addon_menus_wpv-views', 'wpv_post_taxonomies_editor_addon_menus_wpv_views_filter', 11);
                        add_filter('editor_addon_menus_wpv-views', 'wpv_layout_taxonomy_V');

                        echo $WP_Views->editor_addon->add_form_button('', 'wpv_layout_meta_html_content');
                        
                        remove_filter('editor_addon_menus_wpv-views', 'wpv_layout_taxonomy_V');
                        add_filter('editor_addon_menus_wpv-views', 'wpv_post_taxonomies_editor_addon_menus_wpv_views_filter', 11);
                    ?>
                    </div>
                    
                    <!--<div style="display:inline">-->
                    <span style="position: relative; top:-5px;">
                        <?php echo apply_filters('wpv_meta_html_add_form_button', '', '#wpv_layout_meta_html_content'); ?>
                    </span>
                    <!--</div>-->
                    
                    <textarea name="_wpv_layout_settings[layout_meta_html]" id="wpv_layout_meta_html_content" cols="40" rows="16" style="width:100%;margin-top:10px"><?php echo $view_layout_settings['layout_meta_html']; ?></textarea>
                    <div id="wpv_layout_meta_html_content_old_div" style="display:none">
                        <div class="wpv_form_notice"><?php _e('<strong>Your edits are shown below:</strong>', 'wpv-views'); ?> <a style="cursor:pointer;margin-bottom:10px;" onclick="wpv_layout_meta_html_old_dismiss()"><strong><?php echo __('dismiss', 'wpv-views'); ?></strong></a></div>
                        <textarea id="wpv_layout_meta_html_content_old" cols="40" rows="16" style="width:100%;margin-top:10px"></textarea>
                    </div>
                    <textarea name="_wpv_layout_settings[generated_layout_meta_html]" id="wpv_generated_layout_meta_html_content" cols="40" rows="16" style="display:none"><?php echo $view_layout_settings['generated_layout_meta_html']; ?></textarea>
                    <div id="wpv_layout_meta_html_notice" class="wpv_form_notice" style="display:none;"><?php _e('* These updates will take effect when you save the view.', 'wpv-views'); ?></div>
                    <p><a style="cursor:pointer;margin-bottom:10px;" onclick="wpv_view_layout_meta_html_close()"><strong><?php _e('Close', 'wpv-views'); ?></strong></a></p>
                </div>
            </div>
            <div id="wpv_layout_meta_html_extra_css" style="margin-top:15px;">
		<input type="hidden" name="_wpv_settings[layout_meta_html_state][css]" id="wpv_layout_meta_html_extra_css_state" value="<?php echo isset($view_settings['layout_meta_html_state']['css']) ? $view_settings['layout_meta_html_state']['css'] : 'off'; ?>" />
		<input type="button" class="button-secondary wpv_layout_meta_html_extra_css_edit" onclick="wpv_view_layout_meta_html_extra(this)" value="<?php _e('Edit CSS', 'wpv-views'); ?>" />
		<div id ="wpv_layout_meta_html_extra_css_edit" style="background:<?php echo WPV_EDIT_BACKGROUND; ?>">
		    <p><?php _e('<strong>CSS</strong> - This is used to add custom CSS to a View layout.', 'wpv-views'); ?></p>
		    <textarea name="_wpv_settings[layout_meta_html_css]" id="wpv_layout_meta_html_css" cols="97" rows="10"><?php echo isset($view_settings['layout_meta_html_css']) ? $view_settings['layout_meta_html_css'] : ''; ?></textarea>
		    <div id="wpv_layout_meta_html_extra_css_notice" class="wpv_form_notice" style="display:none;"><?php _e('* These updates will take effect when you save the view.', 'wpv-views'); ?></div>
		    <p><a style="cursor:pointer;margin-bottom:10px;" id="wpv_layout_meta_html_extra_css_close" onclick="wpv_view_layout_meta_html_extra_css_close()"><strong><?php _e('Close CSS editor', 'wpv-views'); ?></strong></a></p>
		</div>
	    </div>
	    <div id="wpv_layout_meta_html_extra_js" style="margin-top:15px;">
		  <input type="hidden" name="_wpv_settings[layout_meta_html_state][js]" id="wpv_layout_meta_html_extra_js_state" value="<?php echo isset($view_settings['layout_meta_html_state']['js']) ? $view_settings['layout_meta_html_state']['js'] : 'off'; ?>" />
		  <input type="button" class="button-secondary wpv_layout_meta_html_extra_js_edit" onclick="wpv_view_layout_meta_html_extra(this)" value="<?php _e('Edit JS', 'wpv-views'); ?>" />
		  <div id="wpv_layout_meta_html_extra_js_edit" style="background:<?php echo WPV_EDIT_BACKGROUND; ?>">
		    <p><?php _e('<strong>JS</strong> - This is used to add custom javascript to a View layout.', 'wpv-views'); ?></p>
		    <textarea name="_wpv_settings[layout_meta_html_js]" id="wpv_layout_meta_html_js" cols="97" rows="10"><?php echo isset($view_settings['layout_meta_html_js']) ? $view_settings['layout_meta_html_js'] : ''; ?></textarea>
                    <div id="wpv_layout_meta_html_extra_js_notice" class="wpv_form_notice" style="display:none;"><?php _e('* These updates will take effect when you save the view.', 'wpv-views'); ?></div>
                    <p><a style="cursor:pointer;margin-bottom:10px;" id="wpv_layout_meta_html_extra_js_close" onclick="wpv_view_layout_meta_html_extra_js_close()"><strong><?php _e('Close JS editor', 'wpv-views'); ?></strong></a></p>
		</div>
	    </div>
	    <div id="wpv_layout_meta_html_extra_img" style="margin-top:15px;">
            <?php global $post; ?>
		<input type="hidden" name="_wpv_settings[layout_meta_html_state][img]" id="wpv_layout_meta_html_extra_img_state" value="<?php echo isset($view_settings['layout_meta_html_state']['img']) ? $view_settings['layout_meta_html_state']['img'] : 'off'; ?>" />
		<input type="button" class="button-secondary wpv_layout_meta_html_extra_img_edit" onclick="wpv_view_layout_meta_html_extra(this)" value="<?php _e('Manage Media', 'wpv-views'); ?>" />
		<div id ="wpv_layout_meta_html_extra_img_edit" style="background:<?php echo WPV_EDIT_BACKGROUND; ?>">
		    <p><?php _e('<strong>Media</strong> - This is used to add images to a View output.', 'wpv-views'); ?></p>
		    <input type="button" class="button-secondary wpv_layout_meta_html_extra_img_upload" onclick="tb_show('<?php _e('Upload images'); ?>', 'media-upload.php?post_id=<?php echo $post->ID; ?>&type=image&wpv-media-insert=1&TB_iframe=true');return false;" value="<?php _e('Add Media', 'wpv-views'); ?>" />
		    <?php 
			$args = array(
				'post_type' => 'attachment',
				'numberposts' => null,
				'post_status' => null,
				'post_parent' => $post->ID
			); 
			$attachments = get_posts($args);
			if ($attachments) { ?>
			<div class="media-list">
				<p>Copy the links of the media items and paste into the meta HTML and CSS editors. You can use full URLs. When exporting and importing this View to another site, all URLs will be adjusted.</p>
				<table class="wpv_table_attachments widefat">
				<thead>
				<tr>
				<th><?php _e('Thumbnail', 'wpv-views'); ?></th>
				<th><?php _e('URL', 'wpv-views'); ?></th>
				</tr>
				</thead>
				<?php
					foreach ($attachments as $attachment) {
						$type = get_post_mime_type($attachment->ID);
						$icon = wp_mime_type_icon($type);
						if ( $type == 'image/gif' || $type == 'image/jpeg' || $type == 'image/png' ) {
							$thumb = '<img src="' .  $attachment->guid . '" alt="' . $attachment->post_title . '" width="60" height="60" />';
						} else {
							$thumb = '<img src="' . $icon . '" />';
						}
						?>
						<tr>
						<td><?php echo $thumb; ?></td>
						<td><a href="<?php echo $attachment->guid;?>"><?php echo $attachment->guid;?></a></td>
						</tr>
					<?php } ?>
				</table>
				<p><input type="button" class="button-secondary wpv_layout_meta_html_extra_img_edit_existing" onclick="tb_show('<?php _e('Edit media items'); ?>', 'media-upload.php?post_id=<?php echo $post->ID; ?>&type=image&tab=gallery&wpv-media-edit=1&TB_iframe=true');return false;" value="<?php _e('Edit media items', 'wpv-views'); ?>" /></p>
			</div>
			<?php } else { ?>
				<div class="media-list" style="display:none;">
				<p>Copy the links of the media items and paste into the meta HTML and CSS editors. You can use full URLs. When exporting and importing this View to another site, all URLs will be adjusted.</p>
				<table class="wpv_table_attachments widefat"></table>
				<p><input type="button" class="button-secondary wpv_layout_meta_html_extra_img_edit_existing" onclick="tb_show('<?php _e('Edit media items'); ?>', 'media-upload.php?post_id=<?php echo $post->ID; ?>&type=image&tab=gallery&wpv-media-edit=1&TB_iframe=true');return false;" value="<?php _e('Edit media items', 'wpv-views'); ?>" /></p>
				</div>			
			<?php } ?>
                    <div id="wpv_layout_meta_html_extra_img_notice" class="wpv_form_notice" style="display:none;"><?php _e('* These updates will take effect when you save the view.', 'wpv-views'); ?></div>
                    <p><a style="cursor:pointer;margin-bottom:10px;" id="wpv_layout_meta_html_extra_img_close" onclick="wpv_view_layout_meta_html_extra_img_close()"><strong><?php _e('Close Media manager', 'wpv-views'); ?></strong></a></p>
		</div>
            </div>
        </div>
    <?php
    
}


