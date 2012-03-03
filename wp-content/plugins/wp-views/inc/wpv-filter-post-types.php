<?php

function wpv_post_default_settings($view_settings) {

    if (!isset($view_settings['post_type'])) {
        $view_settings['post_type'] = array();
    }

    return $view_settings;
}

function wpv_get_post_filter_summary($view_settings) {
    
    $view_settings = wpv_post_default_settings($view_settings);
    $selected = $view_settings['post_type'];
    
    $post_types = get_post_types(array('public'=>true), 'objects');
    $selected_post_types = sizeof($selected);
    switch ($selected_post_types) {
        case 0:
            _e('This View selects <strong>ALL</strong> post types', 'wpv-view');
            break;
        
        case 1:
            echo sprintf(__('This View selects <strong>%s</strong>', 'wpv-view'), $post_types[$selected[0]]->labels->name);
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
            break;
        
    }
            
}

function wpv_post_types_checkboxes($view_settings) {
    $post_types = get_post_types(array('public'=>true), 'objects');
    
    // remove any post types that don't exist any more.
    foreach($view_settings['post_type'] as $type) {
        if (!isset($post_types[$type])) {
            unset($view_settings['post_type'][$type]);
        }
    }

    ?>
        <ul style="padding-left:30px;">
            <?php foreach($post_types as $p):?>
                <?php 
                    $checked = @in_array($p->name, $view_settings['post_type']) ? ' checked="checked"' : '';
                ?>
                <li><label><input type="checkbox" name="_wpv_settings[post_type][]" value="<?php echo $p->name ?>" <?php echo $checked ?> />&nbsp;<?php echo $p->labels->name ?></label></li>
            <?php endforeach; ?>
        </ul>
    <?php
}

