<?php

// Form actions
function bp_gtm_form_action($page = false) {
    echo bp_gmt_get_form_action($page);
}

function bp_gmt_get_form_action($page = false, $group = false) {
    global $bp, $groups_template;

    if (!$group)
        $group = & $groups_template->group;

    if (!$page)
        $page = $bp->action_variables[0];

    return apply_filters('bp_gtm_form_action', bp_get_group_permalink($group) . $bp->gtm->slug . '/' . $page);
}

function bp_gtm_p_form_action($user_id) {
    echo bp_gmt_get_p_form_action($user_id);
}

function bp_gmt_get_p_form_action($user_id, $page = false) {
    global $bp;

    if (!$page)
        $page = $bp->current_action;

    return apply_filters('bp_gtm_p_form_action', $bp->loggedin_user->domain . $bp->gtm->slug . '/' . $page);
}

function bp_gtm_get_checked($var, $value) {
    if ($var == $value)
        return 'checked="checked"';
}

/*
 *  Autocomplete functions
 */

function bp_gtm_get_resp_tabs() {
    global $bp;

    if (isset($_GET['r'])) {
        $user_id = bp_core_get_userid($_GET['r']);

        if ($user_id) {
            ?>
            <li id="un-<?php echo $_GET['r'] ?>" class="resps-tab">
                <span>
                    <?php echo bp_core_fetch_avatar(array('item_id' => $user_id, 'type' => 'thumb', 'width' => 15, 'height' => 15)) ?>
                    <?php echo bp_core_get_userlink($user_id) ?>
                </span>
            </li>
            <?php
        }
    }
}

/* AJAX autocomplete users/terms on the create|edit screen */

function bp_gtm_search_users($search_terms, $limit = 10, $page = 1, $populate_extras = true) {
    return BP_Core_User::search_users($search_terms, $limit, $page, $populate_extras);
}

function bp_gtm_search_terms($search_terms, $taxon = 'tag', $limit = 10) {
    return BP_GTM_Taxon::search_terms($search_terms, $taxon, $limit);
}

function bp_gtm_resp_usernames() {
    echo bp_gtm_get_resp_usernames();
}

function bp_gtm_get_resp_usernames() {
    return apply_filters('bp_gtm_get_project_resp_usernames', $_GET['r']);
}

function bp_gtm_tag_names() {
    echo bp_gtm_get_tag_names();
}

function bp_gtm_get_tag_names() {
    return apply_filters('bp_gtm_get_project_tag_names', $_GET['r']);
}

function bp_gtm_get_el_name_by_id($elem_id, $el_type) {
    global $bp, $wpdb;

    if ($el_type == 'task' || $el_type == 'tasks') {
        $data = $wpdb->get_results($wpdb->prepare("SELECT `name` FROM " . $bp->gtm->table_tasks . " WHERE `id` = %d", $elem_id));
    } elseif ($el_type == 'project' || $el_type == 'projects') {
        $data = $wpdb->get_results($wpdb->prepare("SELECT `name` FROM " . $bp->gtm->table_projects . " WHERE `id` = %d", $elem_id));
    }

    $el_name = !empty($data[0]->name) ? $data[0]->name : '';

    return $el_name;
}

function bp_gtm_is_belong_2group($group_id, $element_id, $type) {
    global $bp, $wpdb;

    switch ($type) {
        case 'tag':
            $exist = $wpdb->get_results($wpdb->prepare("SELECT `id` FROM " . $bp->gtm->table_terms . " WHERE `id` = %d AND `group_id` = %d AND taxon='tag'", $element_id, $group_id));
            break;
        case 'cat':
            $exist = $wpdb->get_results($wpdb->prepare("SELECT `id` FROM " . $bp->gtm->table_terms . " WHERE `id` = %d AND `group_id` = %d AND taxon='cat'", $element_id, $group_id));
            break;
        case 'task':
            $exist = $wpdb->get_results($wpdb->prepare("SELECT `id` FROM " . $bp->gtm->table_tasks . " WHERE `id` = %d AND `group_id` = %d", $element_id, $group_id));
            break;
        case 'project':
            $exist = $wpdb->get_results($wpdb->prepare("SELECT `id` FROM " . $bp->gtm->table_projects . " WHERE `id` = %d AND `group_id` = %d", $element_id, $group_id));
            break;
    }

    if (count($exist) != 0)
        return true;
}

function bp_gtm_is_elem_done($elem_id, $elem_type) {
    global $bp, $wpdb;

    if ($elem_type == 'projects') {
        $table = $bp->gtm->table_projects;
    } elseif ($elem_type == 'tasks') {
        $table = $bp->gtm->table_tasks;
    }

    $done = $wpdb->get_results($wpdb->prepare("SELECT `done` FROM $table WHERE `id` = $elem_id"));

    if ($done[0]->done == 1)
        return true;

    return false;
}

function bp_gtm_get_allcount($group_id = false, $type = 'all') {
    global $bp, $wpdb;

    if ($type == 'all') {
        if ($group_id != false) {
            $count['cats'] = count(BP_GTM_Taxon::get_terms_in_group($group_id, 'cat'));
            $count['tags'] = count(BP_GTM_Taxon::get_terms_in_group($group_id, 'tag'));
            $count['projects'] = $wpdb->get_var($wpdb->prepare("
                              SELECT COUNT(DISTINCT `id`) FROM " . $bp->gtm->table_projects . " WHERE `group_id` = '%d'", $group_id));
            $count['tasks'] = $wpdb->get_var($wpdb->prepare("
                              SELECT COUNT(DISTINCT `id`) FROM " . $bp->gtm->table_tasks . " WHERE `group_id` = '%d'", $group_id));
        } else { // no matter in which group
            $count['cats'] = $wpdb->get_var($wpdb->prepare("SELECT COUNT(DISTINCT `id`) FROM " . $bp->gtm->table_terms . " WHERE `taxon` = 'cat'"));
            $count['tags'] = $wpdb->get_var($wpdb->prepare("SELECT COUNT(DISTINCT `id`) FROM " . $bp->gtm->table_terms . " WHERE `taxon` = 'tag'"));
            $count['projects'] = $wpdb->get_var($wpdb->prepare("SELECT COUNT(DISTINCT `id`) FROM " . $bp->gtm->table_projects));
            $count['tasks'] = $wpdb->get_var($wpdb->prepare("SELECT COUNT(DISTINCT `id`) FROM " . $bp->gtm->table_tasks));
        }
    } elseif ($type == 'tags') {
        if ($group_id != false) {
            $count['tags'] = count(BP_GTM_Taxon::get_terms_in_group($group_id, 'tag'));
        } else { // no matter in which group
            $count['tags'] = $wpdb->get_var($wpdb->prepare("SELECT COUNT(DISTINCT `id`) FROM " . $bp->gtm->table_terms . " WHERE `taxon` = 'tag'"));
        }
    } elseif ($type == 'cats') {
        if ($group_id != false) {
            $count['cats'] = count(BP_GTM_Taxon::get_terms_in_group($group_id, 'cat'));
        } else { // no matter in which group
            $count['cats'] = $wpdb->get_var($wpdb->prepare("SELECT COUNT(DISTINCT `id`) FROM " . $bp->gtm->table_terms . " WHERE `taxon` = 'cat'"));
        }
    } elseif ($type == 'tasks') {
        if ($group_id != false) {
            $count['tasks'] = $wpdb->get_var($wpdb->prepare("SELECT COUNT(DISTINCT `id`) FROM " . $bp->gtm->table_tasks . " WHERE `group_id` = '%d'", $group_id));
        } else { // no matter in which group
            $count['tasks'] = $wpdb->get_var($wpdb->prepare("SELECT COUNT(DISTINCT `id`) FROM " . $bp->gtm->table_tasks));
        }
    } elseif ($type == 'projects') {
        if ($group_id != false) {
            $count['projects'] = $wpdb->get_var($wpdb->prepare("SELECT COUNT(DISTINCT `id`) FROM " . $bp->gtm->table_projects . " WHERE `group_id` = '%d'", $group_id));
        } else { // no matter in which group
            $count['projects'] = $wpdb->get_var($wpdb->prepare("SELECT COUNT(DISTINCT `id`) FROM " . $bp->gtm->table_projects));
        }
    }

    if (!$count['tags'])
        $count['tags'] = '0';
    if (!$count['cats'])
        $count['cats'] = '0';
    if (!$count['projects'])
        $count['projects'] = '0';
    if (!$count['tasks'])
        $count['tasks'] = '0';

    return $count;
}

/*
 *  AJAX functions
 */
// Autocomplete-display engine
add_action('wp_ajax_autocomplete_results', 'bp_gtm_ajax_autocomplete_results');

function bp_gtm_ajax_autocomplete_results() {
    global $bp;
    $bp_gtm_group_settings = get_option('bp_gtm_g_' . $bp->groups->current_group->id . '_settings');

    if ($_GET['type'] == 'tags' || $_GET['type'] == 'cats') {
        $terms = false;
        $prevent = array();
        $taxon = substr($_GET['type'], 0, 3);
// Get all the group tags based on the searched terms
        if (function_exists('bp_gtm_search_terms'))
            $terms = bp_gtm_search_terms($_GET['q'], $taxon, $_GET['limit']);

        $terms = apply_filters('bp_gtm_terms_autocomplete_list', $terms, $taxon, $_GET['limit']);

        if (count($terms) > 0) {
            foreach ((array) $terms as $term) {
                if (!in_array($term->name, $prevent))
                    echo $term->name . "\n";
                $prevent[] = $term->name;
            }
        }
        die();
    }
}

// Not really ajax - but is used for it
function bp_gtm_done_link($type, $elem_id, $el_type = false) {
    if (!empty($_GET['filter']) && $_GET['filter'] == 'done') {
        if (bp_gtm_check_access($el_type . '_undone'))
            echo "<a class='undone_me' id='$elem_id' href='#' title='" . __('Mark as pending', 'bp_gtm') . "'><img height='16' width='16' src='" . plugins_url("_inc/images/undone.png", __FILE__) . "' alt='" . __('Undone', 'bp_gtm') . "' /></a>";
    }else {
        if (bp_gtm_check_access($el_type . '_done'))
            echo "<a class='done_me' id='$elem_id' href='#' title='" . __('Mark as completed', 'bp_gtm') . "'><img height='16' width='16' src='" . plugins_url("_inc/images/done.png", __FILE__)  . "' alt='" . __('Done', 'bp_gtm') . "' /></a>";
    }
}

// Delete tag|cat|task|project from a full list page
add_action('wp_ajax_bp_gtm_delete_item', 'bp_gtm_delete_item');

function bp_gtm_delete_item() {
    global $bp, $wpdb;

    if ($_GET['deleteType'] == 'tag' || $_GET['deleteType'] == 'cat') {
        $wpdb->query($wpdb->prepare("DELETE FROM " . $bp->gtm->table_terms . " WHERE `id` = %d AND `group_id` = %d", $_GET['deleteItem'], $bp->groups->current_group->id));
        $wpdb->query($wpdb->prepare("DELETE FROM " . $bp->gtm->table_taxon . " WHERE `term_id` = %d AND `group_id` = %d", $_GET['deleteItem'], $bp->groups->current_group->id));
    } elseif ($_GET['deleteType'] == 'projects') {
        $wpdb->query($wpdb->prepare("DELETE FROM " . $bp->gtm->table_projects . " WHERE `id` = %d AND `group_id` = %d", $_GET['deleteItem'], $bp->groups->current_group->id));
        $wpdb->query($wpdb->prepare("DELETE FROM " . $bp->gtm->table_tasks . " WHERE `project_id` = %d AND `group_id` = %d", $_GET['deleteItem'], $bp->groups->current_group->id));
        $wpdb->query($wpdb->prepare("DELETE FROM " . $bp->gtm->table_taxon . " WHERE `project_id` = %d AND `group_id` = %d", $_GET['deleteItem'], $bp->groups->current_group->id));
        $wpdb->query($wpdb->prepare("DELETE FROM " . $bp->gtm->table_resps . " WHERE `project_id` = %d AND `group_id` = %d", $_GET['deleteItem'], $bp->groups->current_group->id));
        $wpdb->query($wpdb->prepare("DELETE FROM " . $bp->gtm->table_discuss . " WHERE `project_id` = %d AND `group_id` = %d", $_GET['deleteItem'], $bp->groups->current_group->id));
        $wpdb->query($wpdb->prepare("DELETE FROM {$bp->core->table_name_notifications} WHERE `item_id` = %d AND `secondary_item_id` = %d AND `component_name` = {$bp->gtm->slug} AND `component_action` LIKE `project_%%`", $_GET['deleteItem'], $bp->groups->current_group->id));
        $project = BP_GTM_Projects::get_project_by_id($_GET['deleteItem']);
        $resps = explode(' ', $project['0']->resp_id);
        foreach ($resps as $resp) {
            if ($resp != '') {
                $resp_id = bp_core_get_userid($resp);
                if ($resp_id != $bp->loggedin_user->id)
                    bp_core_add_notification($_GET['deleteItem'], $resp_id, $bp->gtm->slug, 'project_deleted', $bp->groups->current_group->id);
            }
        }
        $file_path = $wpdb->get_results($wpdb->prepare("SELECT path FROM {$bp->gtm->table_files} WHERE `project_id` = %d AND `group_id` = %d", $_GET['deleteItem'], $bp->groups->current_group->id));
        foreach ($file_path as $path) {
            unlink(bp_gtm_file_dir($path->path));
        }
        $wpdb->query($wpdb->prepare("DELETE FROM {$bp->gtm->table_files} WHERE `id` = %d AND `group_id` = %d", $_GET['deleteItem'], $bp->groups->current_group->id));
    } elseif ($_GET['deleteType'] == 'tasks') {
        $wpdb->query($wpdb->prepare("DELETE FROM {$bp->gtm->table_tasks} WHERE `id` = %d AND `group_id` = %d", $_GET['deleteItem'], $bp->groups->current_group->id));
        $wpdb->query($wpdb->prepare("DELETE FROM {$bp->gtm->table_taxon} WHERE `task_id` = %d AND `group_id` = %d", $_GET['deleteItem'], $bp->groups->current_group->id));
        $wpdb->query($wpdb->prepare("DELETE FROM {$bp->gtm->table_resps} WHERE `task_id` = %d AND `group_id` = %d", $_GET['deleteItem'], $bp->groups->current_group->id));
        $wpdb->query($wpdb->prepare("DELETE FROM {$bp->gtm->table_discuss} WHERE `task_id` = %d AND `group_id` = %d", $_GET['deleteItem'], $bp->groups->current_group->id));
        $wpdb->query($wpdb->prepare("DELETE FROM {$bp->core->table_name_notifications} WHERE `item_id` = %d AND `secondary_item_id` = %d AND `component_name` = {$bp->gtm->slug} AND `component_action` LIKE `task_%%`", $_GET['deleteItem'], $bp->groups->current_group->id));

        $task = BP_GTM_Tasks::get_task_by_id($_GET['deleteItem']);
        $resps = explode(' ', $task['0']->resp_id);
        foreach ($resps as $resp) {
            if ($resp != '') {
                $resp_id = bp_core_get_userid($resp);
                if ($resp_id != $bp->loggedin_user->id)
                    bp_core_add_notification($_GET['deleteItem'], $resp_id, $bp->gtm->slug, 'task_deleted', $bp->groups->current_group->id);
            }
        }
        $file_path = $wpdb->get_results($wpdb->prepare("SELECT path FROM {$bp->gtm->table_files} WHERE `task_id` = %d AND `group_id` = %d", $_GET['deleteItem'], $bp->groups->current_group->id));
        foreach ($file_path as $path) {
            unlink(bp_gtm_file_dir($path->path));
        }
        $wpdb->query($wpdb->prepare("DELETE FROM {$bp->gtm->table_files} WHERE `id` = %d AND `group_id` = %d", $_GET['deleteItem'], $bp->groups->current_group->id));
    } elseif ($_GET['deleteType'] == 'file') {
        $file_path = $wpdb->get_var($wpdb->prepare("SELECT path FROM {$bp->gtm->table_files} WHERE `id` = %d", $_GET['deleteItem']));
        $wpdb->query($wpdb->prepare("DELETE FROM {$bp->gtm->table_files} WHERE `id` = %d AND `group_id` = %d", $_GET['deleteItem'], $bp->groups->current_group->id));
        unlink(bp_gtm_file_dir($file_path));
    }

    return true;
}

// Edit tag|cat name on an all terms page
add_action('wp_ajax_bp_gtm_edit_term', 'bp_gtm_edit_term');

function bp_gtm_edit_term() {
    global $bp, $wpdb;
    $wpdb->query($wpdb->prepare("UPDATE " . $bp->gtm->table_terms . " SET `name` = %s WHERE `id` = %d AND `group_id` = %d", $_GET['editName'], $_GET['editID'], $bp->groups->current_group->id));
    return true;
}

// Mark task|project as done/undone on a full list page
add_action('wp_ajax_bp_gtm_done_item', 'bp_gtm_done_item');

function bp_gtm_done_item() {
    global $bp, $wpdb;

    if ($_GET['doneAction'] == 'done') {
        if ($_GET['doneType'] == 'projects') {
            $wpdb->query($wpdb->prepare("UPDATE " . $bp->gtm->table_projects . " SET `done` = '1' WHERE `id` = %d AND `group_id` = %d", $_GET['doneID'], $bp->groups->current_group->id));
            $wpdb->query($wpdb->prepare("UPDATE " . $bp->gtm->table_tasks . " SET `done` = '1' WHERE `project_id` = %d AND `group_id` = %d", $_GET['doneID'], $bp->groups->current_group->id));
            $project = BP_GTM_Projects::get_project_by_id($_GET['doneID']);
            $resps = explode(' ', $project['0']->resp_id);
            foreach ($resps as $resp) {
                if ($resp != '') {
                    $resp_id = bp_core_get_userid($resp);
                    if ($resp_id != $bp->loggedin_user->id)
                        bp_core_add_notification($_GET['doneID'], $resp_id, $bp->gtm->slug, 'project_done', $bp->groups->current_group->id);
                }
            }
        }elseif ($_GET['doneType'] == 'tasks') {
            $wpdb->query($wpdb->prepare("UPDATE " . $bp->gtm->table_tasks . " SET `done` = '1' WHERE `id` = %d AND `group_id` = %d", $_GET['doneID'], $bp->groups->current_group->id));
            $wpdb->query($wpdb->prepare("UPDATE " . $bp->gtm->table_tasks . " SET `done` = '1' WHERE `parent_id` = %d AND `group_id` = %d", $_GET['doneID'], $bp->groups->current_group->id));
            $task = BP_GTM_Tasks::get_task_by_id($_GET['doneID']);
            $resps = explode(' ', $task['0']->resp_id);
            foreach ($resps as $resp) {
                if ($resp != '') {
                    $resp_id = bp_core_get_userid($resp);
                    if ($resp_id != $bp->loggedin_user->id)
                        bp_core_add_notification($_GET['doneID'], $resp_id, $bp->gtm->slug, 'task_done', $bp->groups->current_group->id);
                }
            }
        }
    }elseif ($_GET['doneAction'] == 'undone') {
        if ($_GET['doneType'] == 'projects') {
            $wpdb->query($wpdb->prepare("UPDATE " . $bp->gtm->table_projects . " SET `done` = '0' WHERE `id` = %d AND `group_id` = %d", $_GET['doneID'], $bp->groups->current_group->id));
//         $wpdb->query( $wpdb->prepare("UPDATE ".$bp->gtm->table_tasks." SET `done` = '0' WHERE `project_id` = %d AND `group_id` = %d", $_GET['doneID'], $bp->groups->current_group->id));
            $project = BP_GTM_Projects::get_project_by_id($_GET['doneID']);
            $resps = explode(' ', $project['0']->resp_id);
            foreach ($resps as $resp) {
                if ($resp != '') {
                    $resp_id = bp_core_get_userid($resp);
                    if ($resp_id != $bp->loggedin_user->id)
                        bp_core_add_notification($_GET['doneID'], $resp_id, $bp->gtm->slug, 'project_undone', $bp->groups->current_group->id);
                }
            }
        }elseif ($_GET['doneType'] == 'tasks') {
            $wpdb->query($wpdb->prepare("UPDATE " . $bp->gtm->table_tasks . " SET `done` = '0' WHERE `id` = %d AND `group_id` = %d", $_GET['doneID'], $bp->groups->current_group->id));
            $task = BP_GTM_Tasks::get_task_by_id($_GET['doneID']);
            $resps = explode(' ', $task['0']->resp_id);
            foreach ($resps as $resp) {
                if ($resp != '') {
                    $resp_id = bp_core_get_userid($resp);
                    if ($resp_id != $bp->loggedin_user->id)
                        bp_core_add_notification($_GET['doneID'], $resp_id, $bp->gtm->slug, 'task_undone', $bp->groups->current_group->id);
                }
            }
        }
    }
    return true;
}

add_action('wp_ajax_bp_update_description', 'bp_update_description');
function bp_update_description(){
    global $wpdb, $bp;
    $return = $wpdb->query($wpdb->prepare("UPDATE {$bp->gtm->table_files} SET description=%s WHERE id=%d", $_GET['description'], $_GET['file_id']));
    die($return->last_error);
}
add_action('wp_ajax_bp_delete_file', 'bp_delete_file');
function bp_delete_file(){
    global $wpdb, $bp;
    $return = $wpdb->query($wpdb->prepare("DELETE FROM {$bp->gtm->table_files} WHERE id=%d", $_GET['file_id']));
    die($return->last_error);
}
