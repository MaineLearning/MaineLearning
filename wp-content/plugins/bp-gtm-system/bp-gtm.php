<?php
/*
Plugin Name: BP GTM System
Plugin URI: http://ovirium.com/plugins/bp-gtm-system/
Description: Create a tasks management system for BuddyPress groups in your network with lots of features.
Author: slaFFik
Version: 1.9
DB Version: 41
Author URI: http://cosydale.com/
Domain Path: /langs/
Text Domain: bp_gtm
*/

add_action('bp_init', 'bp_gtm_init');
function bp_gtm_init(){
    global $bp;
    require ( dirname(__File__) . '/bp-gtm-core.php');
    
    $bp->loggedin_user->gtm_access = bp_gtm_get_user_roles();

    register_deactivation_hook( __File__, 'bp_gtm_deactivation');
    $file_data = bp_gtm_get_file_data(GTM_DIR.'/bp-gtm.php', array());
   
    $bp_gtm['deactivate'] = 'only';
    
    $bp_gtm['mce'] = 'on';
    $bp_gtm['p_todo'] = 'on';
    $bp_gtm['display_activity'] = 'on';
    $bp_gtm['display_activity_discuss'] = 'on';
    
    $bp_gtm['groups'] = 'all';
    $bp_gtm['groups_own_roles'] = 'none';
    
    $bp_gtm['files'] = 'off';
    $bp_gtm['files_count'] = '3';
    $bp_gtm['files_size'] = '500';
    $bp_gtm['files_types'] = array('zip','rar','7z','pdf','djvu','txt','gif','jpeg','jpg','png');
    
    $bp_gtm['db_version'] = '1';

    $bp_gtm['theme'] = 'gtm';
    
    $bp_gtm['label_gtm_system'] = __('ToDo','bp_gtm');
    $bp_gtm['label_assignments'] = __('Assignments','bp_gtm');
    
    $bp_gtm['def_g_role'] = 5;
    $bp_gtm['def_admin_g_role'] = 1;
    
    add_option('bp_gtm', $bp_gtm, '', 'yes');
    
    $bp_gtm_actions['role'] = 'on';
    add_option('bp_gtm_actions', $bp_gtm_actions, '', 'yes');
}

/*
 * Register $bp->gtm Globals
 */
function bp_gtm_setup_globals(){
    global $bp, $wpdb;

    /* For internal identification */
    $bp->gtm->id    = 'gtm';
    $bp->gtm->slug  = 'gtm';
        
    $bp->gtm->table_tasks      = $wpdb->base_prefix . 'bp_gtm_tasks';
    $bp->gtm->table_projects   = $wpdb->base_prefix . 'bp_gtm_projects';
    $bp->gtm->table_discuss    = $wpdb->base_prefix . 'bp_gtm_discuss';
    $bp->gtm->table_files      = $wpdb->base_prefix . 'bp_gtm_files';
    $bp->gtm->table_terms      = $wpdb->base_prefix . 'bp_gtm_terms';
    $bp->gtm->table_taxon      = $wpdb->base_prefix . 'bp_gtm_taxon';
    $bp->gtm->table_resps      = $wpdb->base_prefix . 'bp_gtm_resps';
    $bp->gtm->table_roles      = $wpdb->base_prefix . 'bp_gtm_roles';
    $bp->gtm->table_roles_caps = $wpdb->base_prefix . 'bp_gtm_roles_caps';

    $bp->gtm->format_notification_function = 'bp_gtm_format_notifications';

    /* Register this in the active components array */
    $bp->active_components[$bp->gtm->slug] = $bp->gtm->id;

    $bp->gtm->valid_status = apply_filters('bp_gtm_valid_status', array('public', 'private', 'hidden') );

    do_action('bp_gtm_setup_globals');
}
add_action('bp_setup_globals', 'bp_gtm_setup_globals');

/*
 * Install & Check DB tables
 */
function bp_gtm_check_installed(){
    /* Need to check db tables exist */
    $bp_gtm = get_option('bp_gtm');
    if ( $bp_gtm['db_version'] < GTM_DB_VERSION ){
        bp_gtm_install($bp_gtm);
    }
}
add_action('admin_init', 'bp_gtm_check_installed');

function bp_gtm_install($bp_gtm){
    global $wpdb, $bp;

    if ( !empty($wpdb->charset) )
        $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";

    $sql[] = "CREATE TABLE {$bp->gtm->table_tasks} (
                `id` bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `name` varchar(200) NOT NULL,
                `desc` longtext NOT NULL,
                `status` varchar(10) NOT NULL DEFAULT 'public',
                `parent_id` bigint(20) NOT NULL DEFAULT '0',
                `creator_id` bigint(20) NOT NULL,
                `group_id` bigint(20),
                `project_id` bigint(20) NOT NULL,
                `resp_id` varchar(500) NOT NULL,
                `date_created` datetime NOT NULL,
                `deadline` datetime NOT NULL,
                `done` tinyint(1) NOT NULL DEFAULT '0',
                `discuss_count` bigint(20) NOT NULL DEFAULT '0',
                KEY `parent_id` (parent_id),
                KEY `creator_id` (creator_id),
                KEY `status` (status)
            ) {$charset_collate};";

    $sql[] = "CREATE TABLE {$bp->gtm->table_discuss} (
                `id` bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `text` longtext NOT NULL,
                `author_id` bigint(20) NOT NULL,
                `task_id` bigint(20) NOT NULL DEFAULT '0',
                `project_id` bigint(20) NOT NULL DEFAULT '0',
                `group_id` bigint(20) NOT NULL  DEFAULT '0',
                `date_created` datetime NOT NULL,
                KEY `author_id` (author_id),
                KEY `task_id` (task_id),
                KEY `project_id` (project_id)
            ) {$charset_collate};";

    $sql[] = "CREATE TABLE {$bp->gtm->table_projects} (
                `id` bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `name` varchar(200) NOT NULL,
                `desc` longtext NOT NULL,
                `status` varchar(10) NOT NULL DEFAULT 'public',
                `group_id` bigint(20),
                `creator_id` bigint(20) NOT NULL,
                `resp_id` varchar(500) NOT NULL,
                `date_created` datetime NOT NULL,
                `deadline` datetime NOT NULL,
                `done` tinyint(1) NOT NULL DEFAULT '0',
                `discuss_count` bigint(20) NOT NULL DEFAULT '0',
                KEY `creator_id` (creator_id),
                KEY `status` (status)
            ) {$charset_collate};";

    $sql[] = "CREATE TABLE {$bp->gtm->table_files} (
                `id` bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `task_id` bigint(20) NOT NULL DEFAULT '0',
                `project_id` bigint(20) NOT NULL DEFAULT '0',
                `discuss_id` bigint(20) NOT NULL DEFAULT '0',
                `owner_id` bigint(20) NOT NULL,
                `group_id` bigint(20) NOT NULL,
                `path` varchar(500) NOT NULL,
                `date_uploaded` INT UNSIGNED NOT NULL DEFAULT  '0',
                KEY `owner_id` (owner_id),
                KEY `group_id` (group_id)
            ) {$charset_collate};";
        
    $sql[] = "CREATE TABLE {$bp->gtm->table_terms} (
                `id` bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `taxon` varchar(20) NOT NULL,
                `group_id` bigint(20),
                `name` varchar(100) NOT NULL
            ) {$charset_collate};";
    $sql[] = "CREATE TABLE {$bp->gtm->table_taxon} (
                `id` bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `task_id` bigint(20) NOT NULL DEFAULT '0',
                `project_id` bigint(20),
                `group_id` bigint(20),
                `term_id` bigint(20) NOT NULL,
                `taxon` varchar(20) NOT NULL,
                KEY `task_id` (task_id),
                KEY `project_id` (project_id)
            ) {$charset_collate};";

    $sql[] = "CREATE TABLE {$bp->gtm->table_resps} (
                `task_id` bigint(20) NOT NULL DEFAULT '0',
                `project_id` bigint(20) NOT NULL DEFAULT '0',
                `group_id` bigint(20) NOT NULL DEFAULT '0',
                `resp_id` bigint(20) NOT NULL,
                KEY `resp_id` (resp_id)
            ) {$charset_collate};";
        
    $sql[] = "CREATE TABLE {$bp->gtm->table_roles} (
                `id` bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `group_id` bigint(20) NOT NULL DEFAULT '0',
                `role_name` varchar(255) NOT NULL,
                `project_view` int(1) DEFAULT '0',
                `project_create` int(1) DEFAULT '0',
                `project_edit` int(1) DEFAULT '0',
                `project_delete` int(1) DEFAULT '0',
                `project_done` int(1) DEFAULT '0',
                `project_undone` int(1) DEFAULT '0',
                `project_comment` int(1) DEFAULT '0',
                `project_up_files` int(1) DEFAULT '0',
                `project_del_files` int(1) DEFAULT '0',
                `task_view` int(1) DEFAULT '0',
                `task_create` int(1) DEFAULT '0',
                `task_edit` int(1) DEFAULT '0',
                `task_delete` int(1) DEFAULT '0',
                `task_done` int(1) DEFAULT '0',
                `task_undone` int(1) DEFAULT '0',
                `task_comment` int(1) DEFAULT '0',
                `task_up_files` int(1) DEFAULT '0',
                `task_del_files` int(1) DEFAULT '0',
                `taxon_view` int(1) DEFAULT '0',
                `taxon_create` int(1) DEFAULT '0',
                `taxon_edit` int(1) DEFAULT '0',
                `taxon_delete` int(1) DEFAULT '0',
                `involved_view` int(1) DEFAULT '0',
                `involved_pm` int(1) DEFAULT '0',
                `involved_mention` int(1) DEFAULT '0',
                `involved_email` int(1) DEFAULT '0',
                `involved_roles` int(1) DEFAULT '0',
                `discuss_view` int(1) DEFAULT '0',
                `discuss_create` int(1) DEFAULT '0',
                `discuss_edit` int(1) DEFAULT '0',
                `discuss_delete` int(1) DEFAULT '0',
                `discuss_up_files` int(1) DEFAULT '0',
                `discuss_del_files` int(1) DEFAULT '0',
                `settings_view` int(1) DEFAULT '0',
                `settings_edit_base` int(1) DEFAULT '0',
                `settings_edit_roles` int(1) DEFAULT '0',
                `delete_all` int(1) DEFAULT '0',
                KEY `group_id` (group_id)
            ) {$charset_collate};";
    $roles['owner'] = __('Owner','bp_gtm');
    $roles['pm'] = __('Project Manager','bp_gtm');
    $roles['doer'] = __('Doer','bp_gtm');
    $roles['client'] = __('Client','bp_gtm');
    $roles['guest'] = __('Guest','bp_gtm');
    $sql[] = "INSERT INTO {$bp->gtm->table_roles} 
                (`id`,`group_id`,`role_name`,
                 `project_view`,`project_create`,`project_edit`,`project_delete`,`project_done`,`project_undone`,`project_comment`,`project_up_files`,`project_del_files`,
                 `task_view`,`task_create`,`task_edit`,`task_delete`,`task_done`,`task_undone`,`task_comment`,`task_up_files`,`task_del_files`,
                 `taxon_view`,`taxon_create`,`taxon_edit`,`taxon_delete`,
                 `involved_view`,`involved_pm`,`involved_mention`,`involved_email`,`involved_roles`,
                 `discuss_view`,`discuss_create`,`discuss_edit`,`discuss_delete`,`discuss_up_files`,`discuss_del_files`,
                 `settings_view`,`settings_edit_base`,`settings_edit_roles`,
                 `delete_all`)
                VALUES ('1','0','{$roles['owner']}',
                 1,1,1,1,1,1,1,1,1,
                 1,1,1,1,1,1,1,1,1,
                 1,1,1,1,
                 1,1,1,1,1,
                 1,1,1,1,1,1,
                 1,1,1,
                 1)";
    $sql[] = "INSERT INTO {$bp->gtm->table_roles} 
                (`id`,`group_id`,`role_name`,
                 `project_view`,`project_create`,`project_edit`,`project_delete`,`project_done`,`project_undone`,`project_comment`,`project_up_files`,`project_del_files`,
                 `task_view`,`task_create`,`task_edit`,`task_delete`,`task_done`,`task_undone`,`task_comment`,`task_up_files`,`task_del_files`,
                 `taxon_view`,`taxon_create`,`taxon_edit`,`taxon_delete`,
                 `involved_view`,`involved_pm`,`involved_mention`,`involved_email`,`involved_roles`,
                 `discuss_view`,`discuss_create`,`discuss_edit`,`discuss_delete`,`discuss_up_files`,`discuss_del_files`,
                 `settings_view`,`settings_edit_base`,`settings_edit_roles`,
                 `delete_all`)
                VALUES ('2','0','{$roles['pm']}',
                 1,1,1,1,1,1,1,1,1,
                 1,1,1,1,1,1,1,1,1,
                 1,1,1,1,
                 1,1,1,1,0,
                 1,1,1,1,1,1,
                 1,1,0,
                 0)";
    $sql[] = "INSERT INTO {$bp->gtm->table_roles} 
                (`id`,`group_id`,`role_name`,
                 `project_view`,`project_create`,`project_edit`,`project_delete`,`project_done`,`project_undone`,`project_comment`,`project_up_files`,`project_del_files`,
                 `task_view`,`task_create`,`task_edit`,`task_delete`,`task_done`,`task_undone`,`task_comment`,`task_up_files`,`task_del_files`,
                 `taxon_view`,`taxon_create`,`taxon_edit`,`taxon_delete`,
                 `involved_view`,`involved_pm`,`involved_mention`,`involved_email`,`involved_roles`,
                 `discuss_view`,`discuss_create`,`discuss_edit`,`discuss_delete`,`discuss_up_files`,`discuss_del_files`,
                 `settings_view`,`settings_edit_base`,`settings_edit_roles`,
                 `delete_all`)
                VALUES ('3','0','{$roles['doer']}',
                 1,0,0,0,0,0,1,0,0,
                 1,0,0,0,1,1,1,1,0,
                 1,1,1,1,
                 1,1,1,0,0,
                 1,1,0,0,1,0,
                 0,0,0,
                 0)";
    $sql[] = "INSERT INTO {$bp->gtm->table_roles} 
                (`id`,`group_id`,`role_name`,
                 `project_view`,`project_create`,`project_edit`,`project_delete`,`project_done`,`project_undone`,`project_comment`,`project_up_files`,`project_del_files`,
                 `task_view`,`task_create`,`task_edit`,`task_delete`,`task_done`,`task_undone`,`task_comment`,`task_up_files`,`task_del_files`,
                 `taxon_view`,`taxon_create`,`taxon_edit`,`taxon_delete`,
                 `involved_view`,`involved_pm`,`involved_mention`,`involved_email`,`involved_roles`,
                 `discuss_view`,`discuss_create`,`discuss_edit`,`discuss_delete`,`discuss_up_files`,`discuss_del_files`,
                 `settings_view`,`settings_edit_base`,`settings_edit_roles`,
                 `delete_all`)
                VALUES ('4','0','{$roles['client']}',
                 1,0,0,0,0,0,1,0,0,
                 1,0,0,0,0,0,1,0,0,
                 0,0,0,0,
                 1,1,0,0,0,
                 1,1,0,0,1,0,
                 0,0,0,
                 0)";
    $sql[] = "INSERT INTO {$bp->gtm->table_roles} 
                (`id`,`group_id`,`role_name`,
                 `project_view`,`project_create`,`project_edit`,`project_delete`,`project_done`,`project_undone`,`project_comment`,`project_up_files`,`project_del_files`,
                 `task_view`,`task_create`,`task_edit`,`task_delete`,`task_done`,`task_undone`,`task_comment`,`task_up_files`,`task_del_files`,
                 `taxon_view`,`taxon_create`,`taxon_edit`,`taxon_delete`,
                 `involved_view`,`involved_pm`,`involved_mention`,`involved_email`,`involved_roles`,
                 `discuss_view`,`discuss_create`,`discuss_edit`,`discuss_delete`,`discuss_up_files`,`discuss_del_files`,
                 `settings_view`,`settings_edit_base`,`settings_edit_roles`,
                 `delete_all`)
                VALUES ('5','0','{$roles['guest']}',
                 0,0,0,0,0,0,0,0,0,
                 0,0,0,0,0,0,0,0,0,
                 0,0,0,0,
                 0,0,0,0,0,
                 0,0,0,0,0,0,
                 0,0,0,
                 0)";
        
    $sql[] = "CREATE TABLE {$bp->gtm->table_roles_caps} (
                `id` bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `user_id` bigint(20) NOT NULL,
                `group_id` bigint(20) NOT NULL DEFAULT '0',
                `role_id` bigint(20) NOT NULL DEFAULT '0',
                KEY `user_id` (user_id),
                KEY `group_id` (group_id)
            ) {$charset_collate};";        

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    do_action('bp_gtm_install');

    $bp_gtm['db_version'] = GTM_DB_VERSION;
    update_option('bp_gtm', $bp_gtm);
}

/*
 * Deactivation hook
 */
function bp_gtm_deactivation(){
    global $wpdb, $bp;
    $bp_gtm = get_option('bp_gtm');

    if ( $bp_gtm['deactivate'] == 'total'){
        $wpdb->query("DROP TABLE {$bp->gtm->table_tasks}");
        $wpdb->query("DROP TABLE {$bp->gtm->table_projects}");
        $wpdb->query("DROP TABLE {$bp->gtm->table_taxon}");
        $wpdb->query("DROP TABLE {$bp->gtm->table_terms}");
        $wpdb->query("DROP TABLE {$bp->gtm->table_discuss}");
        $wpdb->query("DROP TABLE {$bp->gtm->table_resps}");
        $wpdb->query("DROP TABLE {$bp->gtm->table_roles}");
        $wpdb->query("DROP TABLE {$bp->gtm->table_roles_caps}");
        $wpdb->query("DROP TABLE {$bp->gtm->table_files}");
        delete_option('bp_gtm');
        delete_option('bp_gtm_actions');
        delete_option('bp_gtm_groups_own_roles');
        // delete all group specific options too
        // all starts with: bp_gtm_g_
        $wpdb->query("DELETE FROM {$wpdb->prefix}options WHERE `option_name` LIKE 'bp_gtm_g_%'");
        // and user specific options too
        $wpdb->query("DELETE FROM {$wpdb->prefix}usermeta WHERE `meta_key` LIKE 'bp_gtm_%'");
    }
}

/*
 * Different useful functions :)
 */
if(!function_exists('print_var')){
    function print_var($var, $die = false){
        echo '<pre>';
        if(!empty($var))
            print_r($var);
        else
            var_dump($var);
        echo '</pre><br />';
        
        if($die)
            die;
    }
}

//add_action('bp_adminbar_menus', 'bp_gtm_queries');
function bp_gtm_queries(){
    echo '<li class="no-arrow"><a>'.get_num_queries() . ' queries | ';
    echo round(memory_get_usage() / 1024 / 1024, 2) . 'Mb</a></li>';
}

//add_action('wp_footer', 'bp_gtm_globals');
//add_action('admin_footer', 'bp_gtm_globals');
function bp_gtm_globals(){
    global $bp;
    //print_r(bp_gtm_get_user_roles());
    print_var($bp->loggedin_user);
}

function bp_gtm_get_file_data( $file, $default_headers ){
   $default_headers = array(
        'Name'          => 'Plugin Name',
        'PluginURI'     => 'Plugin URI',
        'Version'       => 'Version',
        'DBVersion'     => 'DB Version',
        'Description'   => 'Description',
        'Author'        => 'Author',
        'AuthorURI'     => 'Author URI',
    );

    $fp = fopen( $file, 'r');
    $file_data = fread( $fp, 512 );
    fclose( $fp );

    foreach ( $default_headers as $field => $regex ){
        preg_match('/' . preg_quote( $regex, '/') . ':(.*)$/mi', $file_data, ${$field});
        if ( !empty( ${$field} ) )
            ${$field} = _cleanup_header_comment( ${$field}[1] );
        else
            ${$field} = '';
    }

    $file_data = compact( array_keys( $default_headers ) );

    return $file_data;
}
require_once ( dirname(__FILE__) . '/bp-gtm-classes.php' );
function bp_gtm_register_widgets() {
    add_action('widgets_init', create_function('', 'return register_widget("BP_GTM_gTax_Cloud");'));
}
add_action('plugins_loaded', 'bp_gtm_register_widgets');



?>
