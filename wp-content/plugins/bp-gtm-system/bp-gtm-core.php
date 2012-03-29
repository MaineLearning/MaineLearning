<?php
/*
 * Register some ABS GTM_ Globals
 */
define('GTM_DIR', dirname(__File__)); // without trailing slash
define('GTM_URL', plugin_dir_url(__File__)); // with trailing slash
define('GTM_THEME_DIR', dirname(__File__) . '/templates'); // without trailing slash
$file_data = bp_gtm_get_file_data(GTM_DIR . '/bp-gtm.php', array());
define('GTM_VERSION', $file_data['Version']);
define('GTM_DB_VERSION', $file_data['DBVersion']);

/*
 * Load textdomain for i18n
 */
load_plugin_textdomain('bp_gtm', false, dirname(plugin_basename(__FILE__)) . '/langs/');

require_once ( GTM_DIR . '/bp-gtm-admin.php' );
require_once ( GTM_DIR . '/bp-gtm-cssjs.php' );
require_once ( GTM_DIR . '/bp-gtm-functions.php' );
require_once ( GTM_DIR . '/bp-gtm-filters.php' );
require_once ( GTM_DIR . '/bp-gtm-notifications.php' );

class BP_GTM extends BP_Group_Extension {

    function __construct() {
        global $bp;

        $bp_gtm = get_option('bp_gtm');
        $bp_gtm_group = get_option('bp_gtm_g_' . $bp->groups->current_group->id . '_settings');
        $this->name = esc_html(empty($bp_gtm_group['tab-name']) ? $bp_gtm['label_gtm_system'] : $bp_gtm_group['tab-name']);
        $this->slug = $bp->gtm->slug;
        $this->display_hook = 'bp_gtm_screen_pages';
        $this->enable_create_step = false;
        $this->enable_edit_item = false;
        $this->enable_nav_item = $this->enable_nav_item($bp_gtm);
        $this->nav_item_position = 25;
    }

    /* Add this method the end of your extension class */

    function enable_nav_item($bp_gtm) {
        global $bp;

        if (is_user_logged_in() && ( $bp_gtm['groups'] == 'all' || $bp_gtm['groups'][$bp->groups->current_group->id] == 'on' ) && bp_gtm_check_access('project_view'))
            return true;
        else
            return false;
    }

    function display() {
        global $wpdb, $bp;
        $bp_gtm = get_option('bp_gtm');
        $bp_gtm_group_settings = get_option('bp_gtm_g_' . $bp->groups->current_group->id . '_settings');

        if (!$bp_gtm_group_settings || !$bp_gtm_group_settings['discuss'] || !$bp_gtm_group_settings['tasks_pp'] || !$bp_gtm_group_settings['discuss_pp'] || !$bp_gtm_group_settings['display'] || !$bp_gtm_group_settings['resp']) {
            if ($bp_gtm_group_settings['discuss'] == '')
                $bp_gtm_group_settings['discuss'] = 'on';
            if ($bp_gtm_group_settings['display'] == '')
                $bp_gtm_group_settings['display'] = 'gmembers';
            if ($bp_gtm_group_settings['resp'] == '')
                $bp_gtm_group_settings['resp'] = 'gmembers';
            if ($bp_gtm_group_settings['tasks_pp'] == '')
                $bp_gtm_group_settings['tasks_pp'] = 10;
            if ($bp_gtm_group_settings['discuss_pp'] == '')
                $bp_gtm_group_settings['discuss_pp'] = 10;
            update_option('bp_gtm_g_' . $bp->groups->current_group->id . '_settings', $bp_gtm_group_settings);
        }

        // Save Group Settings
        if (isset($_POST['saveSettings']))
            $this->bp_gtm_save_g_settings($_POST);

        // Save New Term
        if (isset($_POST['saveNewTerm']))
            $this->bp_gtm_save_g_term($_POST);

        // Save New Discussion Post
        if (isset($_POST['submitDiscuss']))
            $this->bp_gtm_save_g_discuss($_POST);

        // Save New Project
        if (isset($_POST['saveNewProject']))
            $this->bp_gtm_save_g_new_project($_POST);

        // Edit Project Data
        if (isset($_POST['editProject']))
            $this->bp_gtm_edit_g_project($_POST);

        // Save New Task
        if (isset($_POST['saveNewTask']))
            $this->bp_gtm_save_g_new_task($_POST);

        // Edit Task Data
        if (isset($_POST['editTask']))
            $this->bp_gtm_edit_g_task($_POST);

        // Delete GTM data
        if (isset($_POST['deleteAll']))
            $this->bp_gtm_delete_g_data($_POST);

        do_action('bp_gtm_screen_pages');

        if (empty($bp_gtm['theme']))
            $bp_gtm['theme'] = $this->slug;

        if (!empty($bp->action_variables[1]) && $bp->action_variables[0] == 'api')
            bp_core_load_template(apply_filters('bp_gtm_template_screen_pages', $bp_gtm['theme'] . '/api'));
        else
            bp_core_load_template(apply_filters('bp_gtm_template_screen_pages', $bp_gtm['theme'] . '/index'));
    }

    // Save group settings
    protected function bp_gtm_save_g_settings($data) {
        global $bp, $wpdb;

        if ($_POST['gtm-discuss'] == 'on') {
            $bp_gtm_group_settings['discuss'] = 'on';
        } else {
            $bp_gtm_group_settings['discuss'] = 'off';
        }
        $bp_gtm_group_settings['tab-name'] = !empty($_POST['tab-name']) ? $_POST['tab-name'] : '';


        $bp_gtm_group_settings['display'] = $_POST['gtm-display'];
        $bp_gtm_group_settings['tasks_pp'] = $_POST['gtm-tasks-pp'];
        $bp_gtm_group_settings['discuss_pp'] = $_POST['gtm-discuss-pp'];

        /* Check the nonce first */
        if (!check_admin_referer('bp_gtm_edit_settings'))
            return false;
        $val = $bp->groups->current_group->id;
        if (!update_option('bp_gtm_g_' . $bp->groups->current_group->id . '_settings', $bp_gtm_group_settings)) {
            bp_core_add_message(__('There was an error updating GTM System settings, please try again.', 'bp_gtm'), 'error');
        } else {
            bp_core_add_message(__('GTM System settings were successfully updated.', 'bp_gtm'));
        }
        do_action('bp_gtm_edit_settings', $bp->groups->current_group->id);

        bp_core_redirect(bp_get_group_permalink($bp->groups->current_group) . $bp->gtm->slug . '/settings/');
    }

    // Save new term
    protected function bp_gtm_save_g_term($data) {
        global $bp, $wpdb;

        /* Check the nonce first */
        if (!check_admin_referer('bp_gtm_new_term'))
            return false;

        $name = apply_filters('bp_gtm_term_name_content', $_POST['term_name']);

        if ($name != '' && $_POST['term_taxon'] != '')
            $inserted_term = $wpdb->query($wpdb->prepare("
                INSERT INTO " . $bp->gtm->table_terms . " ( `name`, `taxon`, `group_id` )
                VALUES ( %s, %s, %d )
                ", $name, $_POST['term_taxon'], $bp->groups->current_group->id));

        if ($inserted_term != null) {
            bp_core_add_message(__('New term was successfully created.', 'bp_gtm'));
        } else {
            bp_core_add_message(__('There was an error creating new term, please try again.', 'bp_gtm'), 'error');
        }

        do_action('bp_gtm_save_new_term', $bp->groups->current_group->id);
        bp_core_redirect(bp_get_group_permalink($bp->groups->current_group) . $bp->gtm->slug . '/terms/');
    }

    // Save discussion post
    protected function bp_gtm_save_g_discuss($data) {
        global $bp, $wpdb;

        /* Check the nonce first */
        if (!check_admin_referer('bp_gtm_discuss_new_reply'))
            return false;

        $text = apply_filters('bp_gtm_discuss_text_content', $_POST['discuss_text']);

        $inserted_post = $wpdb->query($wpdb->prepare("
            INSERT INTO {$bp->gtm->table_discuss} ( `text`, `author_id`, `task_id`, `project_id`, `group_id`, `date_created` )
            VALUES ( %s, %d, %d, %d, %d, NOW() )
            ", $text, $_POST['author_id'], $_POST['task_id'], $_POST['project_id'], $_POST['group_id']));
        $post_id = $wpdb->insert_id; // id of a newly created post

        if ($_POST['task_id'] != '0') {
            $insert_in_task = $wpdb->query($wpdb->prepare("
                UPDATE {$bp->gtm->table_tasks}
                SET `discuss_count` = `discuss_count` + 1
                WHERE `id` = {$_POST['task_id']}"));
        }
        if ($_POST['project_id'] != '0') {
            $insert_in_project = $wpdb->query($wpdb->prepare("
                UPDATE {$bp->gtm->table_projects}
                SET `discuss_count` = `discuss_count` + 1
                WHERE `id` = {$_POST['project_id']}"));
        }

        if ($_POST['task_id'] != '0') {
            $redir = '/tasks/view/' . $_POST['task_id'] . '#post-' . $post_id;
            $elem_type = 'discuss_tasks_' . $_POST['task_id'];
        } elseif ($_POST['project_id'] != '0') {
            $redir = '/projects/view/' . $_POST['project_id'] . '#post-' . $post_id;
            $elem_type = 'discuss_projects_' . $_POST['project_id'];
        }


        // display user message
        if ($inserted_post != null) {
            bp_core_add_message(__('Your reply was successfully posted.', 'bp_gtm'));
            // record to activity feed
            bp_gtm_group_activity(array(
                'user_id' => $_POST['author_id'],
                'group_id' => $bp->groups->current_group->id,
                'elem_id' => $post_id,
                'elem_type' => $elem_type,
                'elem_name' => $text
            ));
        } else {
            bp_core_add_message(__('There was an error posting your reply, please try again.', 'bp_gtm'), 'error');
        }

        do_action('bp_gtm_save_discussion_post', 'discuss', $_POST['author_id'], $post_id, $_POST['task_id'], $_POST['project_id'], $bp->groups->current_group->id);

        bp_core_redirect(bp_get_group_permalink($bp->groups->current_group) . $bp->gtm->slug . $redir);
    }

    // Save new project
    protected function bp_gtm_save_g_new_project($data) {
        global $bp, $wpdb;

        if (!check_admin_referer('bp_gtm_new_project'))
            return false;

        $name = apply_filters('bp_gtm_project_name_content', $_POST['project_name']);
        $description = apply_filters('bp_gtm_project_desc_content', $_POST['project_desc']);

        if (empty($_POST['user_ids']))
            $_POST['user_ids'] = array($_POST['project_creator']);

        $inserted_project = $wpdb->query($wpdb->prepare("
            INSERT INTO " . $bp->gtm->table_projects . " ( `name`, `desc`, `status`, `group_id`, `creator_id`, `resp_id`, `date_created`, `deadline` )
            VALUES ( %s, %s, %s, %d, %d, %s, NOW(), %s )
            ", $name, $description, $bp->groups->current_group->status, $_POST['project_group'], $_POST['project_creator'], implode(' ', $_POST['user_ids']), bp_gtm_covert_date($_POST['project_deadline'])));
        $project_id = $wpdb->insert_id; // id of a newly created project
        // save data to resps table

        bp_gtm_change_user_group_role($_POST['user_ids'], $project_id);


        // save tags if any
        $this->bp_gtm_insert_term($_POST['project_tag_names'], $_POST['project_tags'], $project_id, 'tag');

        // save categories if any
        $this->bp_gtm_insert_term($_POST['project_old_cats'], $_POST['project_cats'], $project_id, 'cat');
//        var_dump($_POST['project_old_cats'], $_POST['project_cats']);die;


        // display user message
        if ($inserted_project != null) {
            foreach ($_POST['user_ids'] as $resp) {
                $resp_id = bp_core_get_userid($resp);
                bp_core_add_notification($project_id, $resp_id, 'gtm', 'project_created', $_POST['project_group']);
            }
            bp_core_add_message(__('New project was successfully created.', 'bp_gtm'));
            // record to activity feed
            bp_gtm_group_activity(array(
                'user_id' => $_POST['project_creator'],
                'group_id' => $bp->groups->current_group->id,
                'elem_id' => $project_id,
                'elem_type' => 'project',
                'elem_name' => $name
            ));
        } else {
            bp_core_add_message(__('There was an error creating new project, please try again.', 'bp_gtm'), 'error');
        }
        do_action('bp_gtm_save_discussion_post', 'project', $_POST['project_creator'], false, false, $project_id, $bp->groups->current_group->id);
        do_action('bp_gtm_save_new_project', $bp->groups->current_group->id);
        bp_core_redirect(bp_get_group_permalink($bp->groups->current_group) . $bp->gtm->slug . '/projects/view/' . $project_id . '?action=created');
    }

    // Edit group project
    protected function bp_gtm_edit_g_project($data) {
        global $bp, $wpdb;
        if (!check_admin_referer('bp_gtm_edit_project'))
            return false;

        $project_id = $_POST['project_id'];
        $name = apply_filters('bp_gtm_project_name_content', $_POST['project_name']);
        $description = apply_filters('bp_gtm_project_desc_content', $_POST['project_desc']);

        // resps workaround
        if (!empty($_POST['user_ids'])) {
            $project_resps_old = $_POST['user_ids']; // array{ slaFFik: 0, bot1: 1, ... }
        } else {
            $project_resps_old = array();
        }
        $resps = array_keys((array) $project_resps_old);

        bp_gtm_change_user_group_role($_POST['user_ids'], $project_id);
        // todo smth with this:
        if (count($resps) > 0) {
            bp_gtm_save_g_resps('0', $project_id, $_POST['project_group'], $resps);
            $project_resps = implode(" ", $resps); // make resps in a line to save in DB
        } else {
            $resps[] = bp_core_get_username($_POST['project_creator']);
            bp_gtm_save_g_resps('0', $project_id, $_POST['project_group'], $resps);
            $project_resps = bp_core_get_username($_POST['project_creator']);
        }

        // update project
        $updated_project = $wpdb->query($wpdb->prepare("
            UPDATE " . $bp->gtm->table_projects . "
            SET `name` = %s, `desc` = %s, `status` = %s, `resp_id` = %s, `deadline` = %s
            WHERE `group_id` = %d AND `id` = %d
            ", $name, $description, $bp->groups->current_group->status, $project_resps, bp_gtm_covert_date($_POST['project_deadline']), $_POST['project_group'], $project_id));

        // edit status of all project's tasks
        $wpdb->query($wpdb->prepare("UPDATE " . $bp->gtm->table_tasks . " SET `status` = %s WHERE `project_id` = %d", $_POST['project_status'], $project_id));

        // delete old tags
        $updated_tag = $wpdb->query($wpdb->prepare("DELETE FROM " . $bp->gtm->table_taxon . " WHERE `project_id` = %d AND `taxon` = 'tag'", $project_id));
        // get rid of unnecessary chars in existed tags
        $this->bp_gtm_insert_term($_POST['project_tag_names'], $_POST['project_tags'], $project_id, 'tag', 0, $_POST['project_old_tags']);


        // update cats
        $updated_cat = $wpdb->query($wpdb->prepare("DELETE FROM " . $bp->gtm->table_taxon . " WHERE `project_id` = %d AND `taxon` = 'cat'", $project_id));
        $this->bp_gtm_insert_term($_POST['project_old_cats'], $_POST['project_cats'], $project_id, 'cat', 0);
//var_dump($_POST['project_old_cats'], $_POST['project_cats']);die;

        // display user message
        if ($updated_project != null || $updated_cat != null || $updated_tag != null) {
            foreach ($resps as $resp) {
                $resp_id = bp_core_get_userid($resp);
                bp_core_add_notification($project_id, $resp_id, 'gtm', 'project_edited', $_POST['project_group']);
            }
        }
        do_action('bp_gtm_save_discussion_post', 'project', $_POST['project_creator'], false, false, $project_id, $bp->groups->current_group->id);
        bp_core_add_message(__('Project data was successfully updated.', 'bp_gtm'));
        do_action('bp_gtm_update_project', $bp->groups->current_group->id);
        bp_core_redirect(bp_get_group_permalink($bp->groups->current_group) . $bp->gtm->slug . '/projects/view/' . $project_id . '?action=edited');
    }

    // Delete GTM data in the group
    protected function bp_gtm_delete_g_data($data) {
        global $bp, $wpdb;

        if (!check_admin_referer('bp_gtm_delete'))
            return false;
        $paths = $wpdb->get_results($wpdb->prepare("SELECT path FROM {$bp->gtm->table_files} WHERE `group_id` = %d", $_POST['cur_group']));
        foreach ($paths as $path) {
            if (file_exists(bp_gtm_file_dir($path->path)))
                unlink(bp_gtm_file_dir($path->path));
        }
        $wpdb->query($wpdb->prepare("DELETE FROM {$bp->gtm->table_files} WHERE `group_id` = %d", $_POST['cur_group']));
        $wpdb->query($wpdb->prepare("DELETE FROM {$bp->gtm->table_projects} WHERE `group_id` = %d", $_POST['cur_group']));
        $wpdb->query($wpdb->prepare("DELETE FROM {$bp->gtm->table_tasks} WHERE `group_id` = %d", $_POST['cur_group']));
        $wpdb->query($wpdb->prepare("DELETE FROM {$bp->gtm->table_taxon} WHERE `group_id` = %d", $_POST['cur_group']));
        $wpdb->query($wpdb->prepare("DELETE FROM {$bp->gtm->table_terms} WHERE `group_id` = %d", $_POST['cur_group']));
        $wpdb->query($wpdb->prepare("DELETE FROM {$bp->gtm->table_resps} WHERE `group_id` = %d", $_POST['cur_group']));
        $wpdb->query($wpdb->prepare("DELETE FROM {$bp->gtm->table_discuss} WHERE `group_id` = %d", $_POST['cur_group']));

        bp_core_add_message(__('Everything was deleted successfully.', 'bp_gtm'));

        do_action('bp_gtm_delete_g_data', $bp->groups->current_group->id);
        bp_core_redirect(bp_get_group_permalink($bp->groups->current_group) . $bp->gtm->slug . '/delete/');
    }

    // Save new task
    protected function bp_gtm_save_g_new_task($data) {
        global $bp, $wpdb;

        if (!check_admin_referer('bp_gtm_new_task'))
            return false;

        $name = apply_filters('bp_gtm_task_name_content', $_POST['task_name']);
        $description = apply_filters('bp_gtm_task_desc_content', $_POST['task_desc']);

        if ($_POST['task_parent'] == 0) {
            $project_id = $_POST['task_project'];
            $status = bp_gtm_get_project_status($project_id);
        } else {
            $project_by_task_id = bp_gtm_get_project_by_task_id($_POST['task_parent'], 'status');
            $status = $project_by_task_id['0']->status;
            $project_id = $project_by_task_id['0']->id;
        }

        if (empty($_POST['user_ids']))
            $_POST['user_ids'] = array(bp_core_get_username($_POST['task_creator']));

        $inserted_task = $wpdb->query($wpdb->prepare("
            INSERT INTO " . $bp->gtm->table_tasks . " ( `name`, `desc`, `status`, `parent_id`, `group_id`, `creator_id`, `project_id`, `resp_id`, `date_created`, `deadline` )
            VALUES ( %s, %s, %s, %d, %d, %d, %d, %s, NOW(), %s )
            ", $name, $description, $status, $_POST['task_parent'], $_POST['task_group'], $_POST['task_creator'], $project_id, implode(' ', $_POST['user_ids']), bp_gtm_covert_date($_POST['task_deadline'])));
        $task_id = $wpdb->insert_id; // id of a newly created project
        // save data to resps table

        bp_gtm_change_user_group_role($_POST['user_ids'], $project_id, $task_id);
        // save tags if any
        // get rid of unnecessary chars in tags' list
        $this->bp_gtm_insert_term($_POST['task_tag_names'], $_POST['task_tags'], $project_id, 'tag', $task_id);
        $this->bp_gtm_insert_term($_POST['project_old_cats'], $_POST['project_cats'], $project_id, 'cat', $task_id);

        // display user message
        if ($inserted_task != null) {
            $resps = explode(' ', $_POST['task_resp_usernames']);
            foreach ($resps as $resp) {
                if ($resp != '') {
                    $resp_id = bp_core_get_userid($resp);
                    bp_core_add_notification($task_id, $resp_id, 'gtm', 'task_created', $_POST['task_group']);
                }
            }
            bp_core_add_message(__('New task was successfully created.', 'bp_gtm'));
            // record to activity feed
            bp_gtm_group_activity(array(
                'user_id' => $_POST['task_creator'],
                'group_id' => $bp->groups->current_group->id,
                'elem_id' => $task_id,
                'elem_type' => 'task',
                'elem_name' => $name
            ));
        } else {
            bp_core_add_message(__('There was an error creating new task, please try again.', 'bp_gtm'), 'error');
        }

        do_action('bp_gtm_save_discussion_post', 'task', $_POST['task_creator'], false, $task_id, false, $bp->groups->current_group->id);
        do_action('bp_gtm_save_new_task', $_POST['task_group']);
        bp_core_redirect(bp_get_group_permalink($bp->groups->current_group) . $bp->gtm->slug . '/tasks/view/' . $task_id . '?action=created');
    }

    // Edit group task
    protected function bp_gtm_edit_g_task($data) {
        global $bp, $wpdb;

        if (!check_admin_referer('bp_gtm_edit_task'))
            return false;

        $task_id = $_POST['task_id'];
        if (!$project_id)
            $project_id = $_POST['task_project'];
        $name = apply_filters('bp_gtm_task_name_content', $_POST['task_name']);
        $description = apply_filters('bp_gtm_task_desc_content', $_POST['task_desc']);
        $status = bp_gtm_get_project_status($project_id);

        // resps workaround
        if (!empty($_POST['user_ids'])) {
            $task_resps_old = $_POST['user_ids']; // array{ slaFFik: 0, bot1: 1, ... }
        } else {
            $task_resps_old = array();
        }
        $resps = (array) $task_resps_old;
        
        bp_gtm_change_user_group_role($change_roles, $project_id, $task_id);
        
        // todo smth with this:
        if (count($resps) > 0) {
            bp_gtm_save_g_resps($task_id, $_POST['task_project'], $_POST['task_group'], $resps);
            $task_resps = implode(' ', $resps); // make resps in a line to save in DB
        } else {
            $resps[] = bp_core_get_username($_POST['task_creator']);
            bp_gtm_save_g_resps($task_id, $_POST['task_project'], $_POST['task_group'], $resps);
            $task_resps = bp_core_get_username($_POST['task_creator']);
        }

        // update task
        $updated_task = $wpdb->query($wpdb->prepare("
            UPDATE " . $bp->gtm->table_tasks . "
            SET `name` = %s, `desc` = %s, `status` = %s, `resp_id` = %s, `project_id` = %d, `deadline` = %s
            WHERE `group_id` = %d AND `id` = %d
            ", $name, $description, $status, $task_resps, $_POST['task_project'], bp_gtm_covert_date($_POST['task_deadline']), $_POST['task_group'], $task_id));

        // delete old tags
        $updated_tag = $wpdb->query($wpdb->prepare("DELETE FROM " . $bp->gtm->table_taxon . " WHERE `task_id` = %d AND `taxon` = 'tag'", $task_id));
        // get rid of unnecessary chars in existed tags
        $this->bp_gtm_insert_term($_POST['task_tag_names'], '', $project_id, 'tag', $task_id);

        // update cats
        $updated_cat = $wpdb->query($wpdb->prepare("DELETE FROM " . $bp->gtm->table_taxon . " WHERE `task_id` = %d AND `taxon` = 'cat'", $task_id));
        $this->bp_gtm_insert_term($_POST['task_old_cats'], $_POST['project_cats'], $project_id, 'cat', $task_id);


        // display user message
        if ($updated_task != null || $updated_cat != null || $updated_tag != null) {
            if (count($resps) > 0)
                foreach ((array) $resps as $resp) {
                    $resp_id = bp_core_get_userid($resp);
                    bp_core_add_notification($task_id, $resp_id, 'gtm', 'task_edited', $_POST['task_group']);
                }
        }
        do_action('bp_gtm_save_discussion_post', 'task', $_POST['task_creator'], false, $task_id, false, $bp->groups->current_group->id);
        bp_core_add_message(__('Task data was successfully updated.', 'bp_gtm'));
        do_action('bp_gtm_edit_task', $_POST['task_group']);
        bp_core_redirect(bp_get_group_permalink($bp->groups->current_group) . $bp->gtm->slug . '/tasks/view/' . $task_id . '?action=edited');
    }

    /**
     * Split sptings with terms and form autput array
     * @param string $autocomp_tags autocomplite terms
     * @param string $not_autocomp_tags terms input=text comma separated
     * @return array of terms 
     */
    protected function bp_gtm_split_string($autocomp_tags = null, $not_autocomp_tags = null) {
        $not_empty_tags = array();
        if (!empty($autocomp_tags)) {
            $temp = $autocomp_tags;
            $temp = str_replace(' ', '', $temp);
            $temp = str_replace('**', ' ', $temp);
            $tags1 = explode('|', $temp);
            $tags1 = array_slice($tags1, 1);
        } else {
            $tags1 = array();
        }
        if (!empty($not_autocomp_tags)) {
            $temp2 = $not_autocomp_tags;
            $temp2 = str_replace(', ', ',', $temp2);
            $tags2 = explode(',', $temp2);
        } else {
            $tags2 = array();
        }
        $tags = array_merge($tags1, $tags2);
        foreach ($tags as $tag) {
            if (!empty($tag)) {
                $not_empty_tags[] = $tag;
            }
        }
        return array_unique($not_empty_tags);
    }

    /**
     * Insert new term (tag or category in database)
     * @param string/array $term_manes string with terms adding by ajax autocompliter
     * @param string/array $task_tags string comma separated new terms
     * @param int $project_id id of terms project 
     * @param int $group_id  id of terms group 
     * @param string $term_type this is tag or cat
     * @return bool true if insert are success and false if not
     */
    protected function bp_gtm_insert_term($term_manes, $task_tags, $project_id = 0, $term_type='tag', $task_id = 0, $existing_terms = array()) {
        global $wpdb, $bp;

        if(!is_array($term_manes) && !is_array($task_tags)){
        $tags = $this->bp_gtm_split_string($term_manes, $task_tags); /// split terms into array of values
        if (!empty($existing_terms)) {
            $tags = array_unique(array_merge($tags, $existing_terms));
        }} else {
            if(empty($term_manes)) $term_manes= array();
            if(empty($task_tags)) $task_tags= array();
            $tags = array_unique(array_merge($term_manes, $task_tags));
        }
        if (!empty($tags)) {
            foreach ($tags as $tag) {
                $tag_id = BP_GTM_Taxon::get_term_id_by_name($tag, $term_type);
                if (bp_gtm_is_belong_2group(bp_get_current_group_id(), $tag_id, $term_type) != 1) {
                    $wpdb->query($wpdb->prepare("
                    INSERT INTO " . $bp->gtm->table_terms . " ( `name`, `group_id`, `taxon` )
                    VALUES ( %s, %d, '$term_type')
                    ", $tag, bp_get_current_group_id()));
                    $tag_id = $wpdb->insert_id;
                }
                $wpdb->query($wpdb->prepare("
                INSERT INTO " . $bp->gtm->table_taxon . " ( `task_id`, `project_id`, `group_id`, `term_id`, `taxon` )
                VALUES ( %d, %d, %d, %d, '$term_type')
                ", $task_id, $project_id, bp_get_current_group_id(), $tag_id));
            }
            return TRUE;
        } else {
            return FALSE;
        }
    }

    // not in use
    function create_screen() {
        
    }

    function edit_screen() {
        
    }

    function edit_screen_save() {
        
    }

    function widget_display() {
        
    }

}

bp_register_group_extension('BP_GTM');

/*
 * Filter to use template files in plugin's directory
 */

function bp_gtm_load_template_filter($found_template, $templates) {
    global $bp;

    if ($bp->current_action == $bp->gtm->slug || $bp->current_component == $bp->gtm->slug) {
        foreach ((array) $templates as $template) {
            $path = is_child_theme() ? TEMPLATEPATH : STYLESHEETPATH; // different path to themes files
            if (file_exists($path . '/' . $template)) {
                $filtered_templates[] = $path . '/' . $template;
            } else if (file_exists(STYLESHEETPATH . '/gtm/' . $template)) {
                $filtered_templates[] = STYLESHEETPATH . '/gtm/' . $template;
            } else {
                if (($position = strpos($template, '/')) !== false)
                    $template = substr($template, $position + 1);
                $filtered_templates[] = plugin_dir_path(__FILE__) . 'templates/gtm/' . $template;
            }
        }
        $found_template = $filtered_templates[0];

        return apply_filters('bp_gtm_load_template_filter', $found_template);
    } else {
        return $found_template;
    }
}

add_filter('bp_located_template', 'bp_gtm_load_template_filter', 10, 2);

/*
 * GTM System Personal Navigation
 */
add_action('wp', 'bp_gtm_personal_nav', 2);
add_action('admin_head', 'bp_gtm_personal_nav', 2);

function bp_gtm_personal_nav() {
    global $bp;
    $bp_gtm = get_option('bp_gtm');
    if ($bp_gtm['p_todo'] == 'on') {
        $gtm_profile_link = $bp->loggedin_user->domain . $bp->gtm->slug . '/';

        $filter['user'] = $bp->loggedin_user->id;
        $filter['not_navi'] = 1;
        $count_ass = BP_GTM_Personal::get_count($filter);

        // Main Navi
        bp_core_new_nav_item(array(
            'name' => $bp_gtm['label_assignments'] . ' (' . $count_ass['all'] . ')',
            'slug' => $bp->gtm->slug,
            'position' => 45,
            'show_for_displayed_user' => bp_is_my_profile(),
            'screen_function' => 'bp_gtm_personal_pages',
            'default_subnav_slug' => 'tasks',
            'item_css_id' => $bp->gtm->id));

        // Subnav items in profile
        bp_core_new_subnav_item(array(
            'name' => __('Tasks', 'bp_gtm') . ' (' . $count_ass['tasks'] . ')',
            'slug' => 'tasks',
            'parent_url' => $gtm_profile_link,
            'parent_slug' => $bp->gtm->slug,
            'screen_function' => 'bp_gtm_personal_pages',
            'position' => 10,
            'user_has_access' => bp_is_my_profile()));

        bp_core_new_subnav_item(array(
            'name' => __('Projects', 'bp_gtm') . ' (' . $count_ass['projects'] . ')',
            'slug' => 'projects',
            'parent_url' => $gtm_profile_link,
            'parent_slug' => $bp->gtm->slug,
            'screen_function' => 'bp_gtm_personal_pages',
            'position' => 20,
            'user_has_access' => bp_is_my_profile()));

        bp_core_new_subnav_item(array(
            'name' => __('Settings', 'bp_gtm'),
            'slug' => 'settings',
            'parent_url' => $gtm_profile_link,
            'parent_slug' => $bp->gtm->slug,
            'screen_function' => 'bp_gtm_personal_pages',
            'position' => 30,
            'user_has_access' => bp_is_my_profile()));
    }
}

function bp_gtm_personal_tabs() {
    global $bp, $groups_template;

    $current_tab = $bp->current_action;
    $gtm_profile_link = $bp->loggedin_user->domain . $bp->gtm->slug . '/';
    ?>

    <li<?php if ('tasks' == $current_tab || empty($current_tab)) : ?> class="current"<?php endif; ?>><a href="<?php echo $gtm_profile_link ?>tasks"><?php _e('Tasks', 'bp_gtm') ?></a></li>
    <li<?php if ('projects' == $current_tab) : ?> class="current"<?php endif; ?>><a href="<?php echo $gtm_profile_link ?>projects"><?php _e('Projects', 'bp_gtm') ?></a></li>
    <li<?php if ('settings' == $current_tab) : ?> class="current"<?php endif; ?>><a href="<?php echo $gtm_profile_link ?>settings"><?php _e('Settings', 'bp_gtm') ?></a></li>

    <?php
}

function bp_gtm_personal_pages() {
    $bp_gtm = get_option('bp_gtm');
    // Save Personal Settings
    if (isset($_POST['saveSettings']))
        bp_gtm_save_p_settings($_POST);

    if (!isset($bp_gtm['theme']))
        $bp_gtm['theme'] = 'gtm';

    do_action('bp_gtm_personal_pages');
    bp_core_load_template(apply_filters('bp_gtm_personal_pages', $bp_gtm['theme'] . '/personal/home'));
}

function bp_gtm_save_p_settings($data) {
    global $bp;
    $bp_gtm = get_option('bp_gtm');
    /* Check the nonce first */
    if (!check_admin_referer('bp_gtm_personal_settings'))
        return false;

    if (is_numeric($_POST['p_tasks_pp'])) {
        if (update_user_meta($_POST['p_user_id'], 'bp_gtm_tasks_pp', $_POST['p_tasks_pp'])) {
            bp_core_add_message(__('Personal settings were successfully updated.', 'bp_gtm'));
        } else {
            bp_core_add_message(__('There was an error updating personal settings, please try again.', 'bp_gtm'), 'error');
        }
    } else {
        bp_core_add_message(__('There was an error updating personal settings, please try again.', 'bp_gtm'), 'error');
    }

    do_action('bp_gtm_personal_settings', $_POST);

    bp_core_load_template(apply_filters('bp_gtm_personal_pages', $bp_gtm['theme'] . '/personal/home'));
}

/**
 * GTM Tabs on screens under main group navi
 */
function bp_gtm_tabs($group = false) {
    global $bp, $groups_template;
    $bp_gtm = get_option('bp_gtm');
    if (!$group)
        $group = ( $groups_template->group ) ? $groups_template->group : $bp->groups->current_group;

    $bp_gtm_group_settings = get_option('bp_gtm_g_' . $bp->groups->current_group->id . '_settings');

    $current_tab = $bp->action_variables[0];
    $gtm_link = bp_get_group_permalink() . $bp->gtm->slug;
    ?>

    <?php if (bp_gtm_check_access('project_view')) { ?>
        <li<?php if ('projects' == $current_tab || empty($current_tab)) : ?> class="current"<?php endif; ?>><a href="<?php echo $gtm_link ?>/projects"><?php _e('Projects', 'bp_gtm') ?></a></li>
    <?php } ?>

    <?php if (bp_gtm_check_access('task_view')) { ?>
        <li<?php if ('tasks' == $current_tab) : ?> class="current"<?php endif; ?>><a href="<?php echo $gtm_link ?>/tasks"><?php _e('Tasks', 'bp_gtm') ?></a></li>
    <?php } ?>

    <?php if (bp_gtm_check_access('taxon_view')) { ?>
        <li<?php if ('terms' == $current_tab) : ?> class="current"<?php endif; ?>><a href="<?php echo $gtm_link ?>/terms"><?php _e('Classifier', 'bp_gtm') ?></a></li>
    <?php } ?>

    <?php if (bp_gtm_check_access('involved_view')) { ?>
        <li<?php if ('involved' == $current_tab) : ?> class="current"<?php endif; ?>><a href="<?php echo $gtm_link ?>/involved"><?php _e('Involved', 'bp_gtm') ?></a></li>
    <?php } ?>
    <?php
    if (bp_gtm_check_access('files_view') && $bp_gtm['files'] == 'on') {
//        unlink(WP_CONTENT_DIR.'/uploads/gtm/files/discuss/28_bbpress.2.0.2.zip')
        ?>
        <li<?php if ('files' == $current_tab) : ?> class="current"<?php endif; ?>><a href="<?php echo $gtm_link ?>/files"><?php _e('Files', 'bp_gtm') ?></a></li>
    <?php } ?>
    <?php if ($bp_gtm_group_settings['discuss'] == 'on' && bp_gtm_check_access('discuss_view')) { ?>
        <li<?php if ('discuss' == $current_tab) : ?> class="current"<?php endif; ?>><a href="<?php echo $gtm_link ?>/discuss"><?php _e('Discussions', 'bp_gtm') ?></a></li>
    <?php } ?>

    <?php if (bp_gtm_check_access('settings_view')) { ?>
        <li<?php if ('settings' == $current_tab) : ?> class="current"<?php endif; ?>><a href="<?php echo $gtm_link ?>/settings"><?php _e('Settings', 'bp_gtm') ?></a></li>
    <?php } ?>



    <?php do_action('bp_gtm_tabs', $current_tab, $group->slug) ?>

    <?php if (bp_gtm_check_access('delete_all')) { ?>
        <li<?php if ('delete' == $current_tab) : ?> class="current"<?php endif; ?>><a href="<?php echo $gtm_link ?>/delete"><?php _e('Delete', 'bp_gtm') ?></a></li>
        <?php
    }
}

/*
 * Screen-pages switchers
 */

function bp_is_gtm_screen($slug) {
    global $bp;

    if ($bp->current_component != BP_GROUPS_SLUG || $bp->gtm->slug != $bp->current_action)
        return false;

    if ($bp->action_variables[0] == $slug || $bp->action_variables[0] == '')
        return true;

    return false;
}

/*
 * Save/Edit/Delete all the data of GTM System
 */

//function bp_gtm_screen_pages(){
//   
//}
// Make some magic stuff with resps array
function bp_gtm_save_g_resps($task_id, $project_id, $group_id, $resps) {
    global $bp, $wpdb;

    //if($task_id > 0) $project_id = 0;

    $wpdb->query($wpdb->prepare("
      DELETE FROM {$bp->gtm->table_resps}
      WHERE `task_id` = %d AND `project_id` = %d AND `group_id` = %d
   ", $task_id, $project_id, $group_id));

    if (count($resps) > 0)
        foreach ((array) $resps as $resp_name) {
            if ($resp_name != '') {
                $resp_id = bp_core_get_userid($resp_name);
                $wpdb->query($wpdb->prepare("
               INSERT INTO {$bp->gtm->table_resps}
               (`task_id`, `project_id`, `group_id`, `resp_id`)
               VALUES (%d, %d, %d, %d)
            ", $task_id, $project_id, $group_id, $resp_id));
            }
        }
}

function bp_gtm_covert_date($date) {
    $timestamp = strtotime($date);
    if (!$timestamp) {
        $times = explode('/', $date);
        $timestamp = $times[1] . '/' . $times[0] . '/' . $times[2];
        $timestamp = strtotime($timestamp);
    };
    return date('Y-m-d', $timestamp);
}

function bp_gtm_change_user_group_role($resps, $id, $task_id = 0) {
    global $wpdb, $bp;
    $group_id = bp_get_current_group_id();
    bp_gtm_save_g_resps($task_id, $id, $group_id, $resps);
    foreach ($resps as $value) {
        $resp_id = bp_core_get_userid($value);
        $exist = $wpdb->query($wpdb->prepare("
            SELECT id
            FROM {$bp->gtm->table_roles_caps}
            WHERE `group_id` = $group_id AND `user_id` = $resp_id"));
        if (!$exist) {
            bp_gtm_change_user_role($resp_id, 3, $_POST['project_group']);
        }
    }
}