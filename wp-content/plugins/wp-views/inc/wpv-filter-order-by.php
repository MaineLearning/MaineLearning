<?php

function wpv_filter_order_by_admin_summary($view_settings) {
    $view_settings = wpv_order_by_default_settings($view_settings);
    switch($view_settings['orderby']) {
        case 'post_date':
            $order_by = __('post date', 'wpv-view');
            break;
        
        case 'post_title':
            $order_by = __('post title', 'wpv-view');
            break;
        
        case 'ID':
            $order_by = __('post ID', 'wpv-view');
            break;
        
        case 'menu_order':
            $order_by = __('menu order', 'wpv-view');
            break;
        
        case 'rand':
            $order_by = __('random order', 'wpv-view');
            break;
            
        default:
            $order_by = str_replace('field-', '', $view_settings['orderby']);
            $order_by = sprintf(__('Field - %s', 'wpv-view'), $order_by);
            break;
        
    }
    $order = __('descending', 'wpv-view');
    if ($view_settings['order'] == 'ASC') {
        $order = __('ascending', 'wpv-view');
    }
    echo sprintf(__(', ordered by <strong>%s</strong>, <strong>%s</strong>', 'wpv-view'), $order_by, $order);
    
}

function wpv_filter_order_by_admin($view_settings) {
    
    global $WP_Views;
    
    ?>
    <fieldset>
        <legend><strong><?php _e('Order by:', 'wpv-views') ?></strong></legend>            
        <ul style="padding-left:30px;">
            <li>
                <select name="_wpv_settings[orderby]">
                    <option value="post_date"><?php _e('post date', 'wpv-views'); ?></option>
                    <?php $selected = $view_settings['orderby']=='post_title' ? ' selected="selected"' : ''; ?>
                    <option value="post_title" <?php echo $selected ?>><?php _e('post title', 'wpv-views'); ?></option>
                    <?php $selected = $view_settings['orderby']=='ID' ? ' selected="selected"' : ''; ?>
                    <option value="ID" <?php echo $selected ?>><?php _e('post id', 'wpv-views'); ?></option>
                    <?php $selected = $view_settings['orderby']=='menu_order' ? ' selected="selected"' : ''; ?>
                    <option value="menu_order" <?php echo $selected ?>><?php _e('menu order', 'wpv-views'); ?></option>
                    <?php $selected = $view_settings['orderby']=='rand' ? ' selected="selected"' : ''; ?>
                    <option value="rand" <?php echo $selected ?>><?php _e('random order', 'wpv-views'); ?></option>
                    
                    <?php
                        $cf_keys = $WP_Views->get_meta_keys();
                        foreach ($cf_keys as $key) {
                            $selected = $view_settings['orderby'] == "field-" . $key ? ' selected="selected"' : '';
                            $option = '<option value="field-' . $key . '"' . $selected . '>';
                            $option .= sprintf(__('Field - %s', 'wpv-view'), $key);
                            $option .= '</option>';
                            echo $option;
                        }
                    ?>
                </select>
            </li>
            <li>
                <select name="_wpv_settings[order]">            
                    <option value="DESC"><?php _e('Descending', 'wpv-views'); ?>&nbsp;</option>
                    <?php $selected = $view_settings['order']=='ASC' ? ' selected="selected"' : ''; ?>
                    <option value="ASC" <?php echo $selected ?>><?php _e('Ascending', 'wpv-views'); ?>&nbsp;</option>
                </select>
            </li>
        </ul>
        
    </fieldset>

    <?php
}

