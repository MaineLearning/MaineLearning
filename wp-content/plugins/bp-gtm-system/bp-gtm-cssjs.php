<?php

ob_start();
add_action('wp_enqueue_scripts', 'bp_gtm_add_css');
add_action('admin_head', 'bp_gtm_admin_css');
add_action('wp_print_scripts', 'bp_gtm_js_groups_all');
add_action('wp_print_scripts', 'bp_gtm_localize_js');
add_action('wp_enqueue_scripts', 'bp_gtm_datepicker_js');
add_action('wp_head', 'bp_gtm_autocomplete_js');

//function bp_gtm_autocomplete_js(){
//    if(bp_is_current_action( 'gtm' ))
//    wp_enqueue_script('autocommplete', plugins_url('_inc/autocomplete/autocomplete.js', __FILE__), array('jquery'));
//}

function bp_gtm_js_groups_all() {

    // one main js file
    if (!is_admin()){
        wp_enqueue_script('BP_GTM_GLOBAL_JS', plugins_url('_inc/global.js', __FILE__), array('jquery'));
        wp_enqueue_script('tool_tip', plugins_url('_inc/jquery.tools.min.js', __FILE__), array('jquery'));
    }

    if (is_admin())
        wp_enqueue_script('BP_GTM_ADMIN_JS', plugins_url('_inc/admin-scripts.js', __FILE__), array('jquery'));
}

// LOcalize JS scripts

function bp_gtm_localize_js() {
    $bp_gtm = get_option('bp_gtm');
    $date_format = gtm_js_date_format(get_option('date_format'));
    $discuss_nav_what = !empty($_GET['filter']) ? $_GET['filter'] : 'tasks';
    $lang = WPLANG != '' ? WPLANG : 'en_GB';
    $filter = isset($_GET['filter']) ? $_GET['filter'] : 'deadline';
    $project = isset($_GET['project']) ? $_GET['project'] : '';
    $person_navi_filter = isset($_GET['filter']) ? $_GET['filter'] : '';
    $localize = array(
        'role_changed' => __('Role was successfully changed', 'bp_gtm'),
        'role_error_again' => __('Error: please try again', 'bp_gtm'),
        'role_please_select' => __('Please select a new role for this user', 'bp_gtm'),
        'discuss_update' => __('Update post', 'bp_gtm'),
        'cancel' => __('Cancel', 'bp_gtm'),
        'delete_me' => __("Are you sure you want to delete it?\nThis action cannot be undone.", 'bp_gtm'),
        'tasks_subtasks_list' => __('List of SubTasks', 'bp_gtm'),
        'tasks_subtasks_empty' => __('There are no subtasks to display', 'bp_gtm'),
        'involved_email' => __('Email was sent to that involved user to remind about pending tasks/projects.', 'bp_gtm'),
        'terms_save' => __('Save', 'bp_gtm'),
        'task_status' => __('completed', 'bp_gtm'),
        'task_delete' => __('deleted', 'bp_gtm'),
        'images' => plugins_url('/_inc/images', __FILE__),
        'lang' => $lang,
        'discuss_nav_what' => $discuss_nav_what,
        'task_navi_filter' => $filter,
        'task_navi_project' => $project,
        'person_navi_filter' => $person_navi_filter,
        'date_format' => $date_format,
        'files_count' => $bp_gtm['files_count'],
    );
    $localize['mce'] = $bp_gtm['mce'] == 'on' ? 'on' : ''; // on/off tinymce

    wp_localize_script('BP_GTM_GLOBAL_JS', 'bp_gtm_strings', $localize);
}

function bp_gtm_autocomplete_js() {
    global $bp;
    // Include the autocomplete JS for required pages only inside GTM component
    if ((!empty($bp->action_variables[1]) && $bp->action_variables[0]) && ('projects' == $bp->action_variables[0] || 'tasks' == $bp->action_variables[0] ) && ('create' == $bp->action_variables[1] || 'edit' == $bp->action_variables[1])) {
        wp_enqueue_script('bp-gtm-autocomplete', plugins_url('/_inc/autocomplete/autocomplete.js', __FILE__), array('jquery'));
    }
}

function bp_gtm_datepicker_js() {
    global $bp;
    $lang = WPLANG != '' ? WPLANG : 'en_GB';
    $script = plugins_url('_inc/datepicker/i18n/' . $lang . '.js', __FILE__);
    if ((!empty($bp->action_variables[1]) && $bp->action_variables[0]) && ('projects' == $bp->action_variables[0] || 'tasks' == $bp->action_variables[0] ) && ('create' == $bp->action_variables[1] || 'edit' == $bp->action_variables[1])) {
        wp_enqueue_script('date_picker', $script, array('jquery', 'jquery-ui-core', 'jquery-ui-datepicker'));
    }
}

//-------------------------CSS----------------------------------------------------------
// Add global styles and autocomplete if required
function bp_gtm_add_css() {
    global $bp;
//    if ((!empty($bp->action_variables[1]) && $bp->action_variables[0]) && ('projects' == $bp->action_variables[0] || 'tasks' == $bp->action_variables[0] ) && ('create' == $bp->action_variables[1] || 'edit' == $bp->action_variables[1])) 
        wp_enqueue_style('bp-gtm-autocomplete', plugins_url('_inc/autocomplete.css', __FILE__));


        wp_enqueue_style('bp-gtm-style', plugins_url('_inc/style.css', __FILE__));
    }

// Admin page styles
    function bp_gtm_admin_css() {
        wp_enqueue_style('bp-gtm-admin-style', plugins_url('_inc/admin-styles.css', __FILE__));
    }

    function gtm_js_date_format($php_date_str = '') {
        $js_date_str = 'M d, yy';
        $dates = array(
            'F j, Y' => 'MM d, yy',
            'Y/m/d' => 'yy/mm/dd',
            'm/d/Y' => 'mm/dd/yy',
            'd/m/Y' => 'dd/mm/yy',
            'd.m.Y' => 'dd.mm.yy'
        );

        if (isset($dates[$php_date_str])) {
            $js_date_str = $dates[$php_date_str];
        }

        return $js_date_str;
    }

    