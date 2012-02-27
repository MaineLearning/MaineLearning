<?php

function wpv_ajax_get_post_filter_summary() {
    if (wp_verify_nonce($_POST['wpv_nonce'], 'wpv_post_filter_nonce')) {
        $selected = array();
        if (isset($_POST['selected'])) {
            $selected = $_POST['selected'];
        }
        wpv_get_post_filter_summary($selected, array('orderby' => $_POST['orderby'],
                                                              'order' => $_POST['order']));
    }    
    die;
}

function wpv_get_post_filter_summary($selected, $order) {
    $post_types = get_post_types(array('public'=>true), 'objects');
    $selected_post_types = sizeof($selected);
    switch ($selected_post_types) {
        case 0:
            _e('This View selects <strong>ALL</strong> post types', 'wpv-view');
            break;
        
        case 1:
            echo sprintf(__('This View selects <strong>%s</strong>', 'wpv-view'), $post_types[$selected[0]]->labels->name);
            wpv_filter_order_by_admin_summary($order);
            break;
        
        default:
            _e('This View selects ', 'wpv-view');
            for($i = 0; $i < $selected_post_types - 1; $i++) {
                if ($i != 0) {
                    echo ', ';
                }
                echo '<strong>' . $post_types[$selected[$i]]->labels->name . '</strong>';
            }
            _e(' and ', 'wpv-view');
            echo '<strong>' . $post_types[$selected[$i]]->labels->name . '</strong>';
            wpv_filter_order_by_admin_summary($order);
            break;
        
    }
            
    ?>
    <br />
    <input class="button-secondary" type="button" value="<?php echo __('Edit', 'wpv-views'); ?>" name="<?php echo __('Edit', 'wpv-views'); ?>" onclick="wpv_show_post_type_edit()"/>
    <?php    
}

function wpv_filter_post_types_admin($view_settings) {

    $post_types = get_post_types(array('public'=>true), 'objects');
    
    if (!isset($view_settings['post_type'])) {
        $view_settings['post_type'] = array();
    }
    if (!isset($view_settings['orderby'])) {
        $view_settings['orderby'] = 'post_date';
    }
    if (!isset($view_settings['order'])) {
        $view_settings['order'] = 'DESC';
    }

    wp_nonce_field('wpv_post_filter_nonce', 'wpv_post_filter_nonce');
    ?>
    <td></td>
    <td>
        <div id="wpv-filter-post-type-show">
            <?php
                // remove any post types that don't exist any more.
                foreach($view_settings['post_type'] as $type) {
                    if (!isset($post_types[$type])) {
                        unset($view_settings['post_type'][$type]);
                    }
                }
                
                wpv_get_post_filter_summary($view_settings['post_type'], $view_settings);
            ?>
        </div>
        <div id="wpv-filter-post-type-edit" style="background:<?php echo WPV_EDIT_BACKGROUND;?>;display:none">
            <fieldset>
                    <legend style="margin-bottom:5px"><strong><?php _e('Select what content type to load:', 'wpv-views') ?></strong></legend>
                    <ul style="padding-left:30px;">
                        <?php foreach($post_types as $p):?>
                            <?php 
                                $checked = @in_array($p->name, $view_settings['post_type']) ? ' checked="checked"' : '';
                            ?>
                            <li><label><input type="checkbox" name="_wpv_settings[post_type][]" value="<?php echo $p->name ?>" <?php echo $checked ?> />&nbsp;<?php echo $p->labels->name ?></label></li>
                        <?php endforeach; ?>
                    </ul>
            </fieldset>
            
            <?php wpv_filter_order_by_admin($view_settings); ?>
            
            <input class="button-primary" type="button" value="<?php echo __('OK', 'wpv-views'); ?>" name="<?php echo __('OK', 'wpv-views'); ?>" onclick="wpv_show_post_type_edit_ok()"/>
            <input class="button-secondary" type="button" value="<?php echo __('Cancel', 'wpv-views'); ?>" name="<?php echo __('Cancel', 'wpv-views'); ?>" onclick="wpv_show_post_type_edit_cancel()"/>
        </div>
    </td>
    
    <?php    
}

