<?php


/*
  
    Add controls to the admin page for specifying the layout_meta_html
    
*/

function wpv_layout_meta_html_admin($view_layout_settings) {
    global $WP_Views;
    
    $defaults = array('layout_meta_html' => '',
                      'generated_layout_meta_html' => '');
    $view_layout_settings = wp_parse_args($view_layout_settings, $defaults);
    
    ?>
        <div id="wpv_layout_meta_html_admin">
            <div id="wpv_layout_meta_html_admin_show">
                <br />
                <a style="cursor: pointer" onclick="wpv_view_layout_meta_html()"><?php _e('View/Edit Meta HTML', 'wpv-view'); ?></a>
            </div>
            <div id="wpv_layout_meta_html_admin_edit" style="background:<?php echo WPV_EDIT_BACKGROUND;?>;display:none">
                <div style="margin:10px 10px 10px 10px;">
                    <p><?php _e('<strong>Meta HTML</strong> - This is used to layout the posts found. It gets generated from the View Layout settings and can be modified to suit.', 'wpv-view'); ?></p>
                    <div id="wpv_layout_meta_html_content_error" class="wpv_form_errors" style="display:none;">
                        <p><?php _e("Changes can't be applied. It appears that you made manual modifications to the Meta HTML.", 'wpv-views'); ?></p>
                        <a style="cursor:pointer;margin-bottom:10px;" onclick="wpv_layout_meta_html_generate_new()"><strong><?php echo __('Generate the new layout content', 'wpv-views'); ?></strong></a> <?php _e('(your edits will be displayed and you can apply them again)', 'wpv-view'); ?>
                    </div>
                    
                    <?php echo $WP_Views->editor_addon->add_form_button('', '#wpv_layout_meta_html_content'); ?>
                    
                    <textarea name="_wpv_layout_settings[layout_meta_html]" id="wpv_layout_meta_html_content" cols="40" rows="10" style="width:100%;margin-top:10px"><?php echo $view_layout_settings['layout_meta_html']; ?></textarea>
                    <div id="wpv_layout_meta_html_content_old_div" style="display:none">
                        <div class="wpv_form_notice"><?php _e('<strong>Your edits are shown below:</strong>', 'wpv-view'); ?> <a style="cursor:pointer;margin-bottom:10px;" onclick="wpv_layout_meta_html_old_dismiss()"><strong><?php echo __('dismiss', 'wpv-views'); ?></strong></a></div>
                        <textarea id="wpv_layout_meta_html_content_old" cols="40" rows="10" style="width:100%;margin-top:10px"></textarea>
                    </div>
                    <textarea name="_wpv_layout_settings[generated_layout_meta_html]" id="wpv_generated_layout_meta_html_content" cols="40" rows="10" style="display:none"><?php echo $view_layout_settings['generated_layout_meta_html']; ?></textarea>
                    <div id="wpv_layout_meta_html_notice" class="wpv_form_notice" style="display:none;"><?php _e('* These updates will take effect when you save the view.', 'wpv-view'); ?></div>
                    <p><a style="cursor:pointer;margin-bottom:10px;" onclick="wpv_view_layout_meta_html_close()"><strong><?php _e('Close', 'wpv-view'); ?></strong></a></p>
                </div>
            </div>
        </div>

    <?php
    
}


