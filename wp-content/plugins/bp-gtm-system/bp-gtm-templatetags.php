<?php

function bp_gtm_filter_users($resps = null) {
    global $bp;
    if (bp_group_has_members(array('per_page' => 1000, 'exclude_admins_mods' => 0))) :
        $check = array();
        if (!empty($resps) && !empty($bp->action_variables[1]) && $bp->action_variables[1] == 'edit') {
            $check = $resps;
        };
        ?>
        <div class="wrap-roles">
            <?php do_action('bp_before_group_members_list'); ?>
            <ul id="member-list" class="item-list" role="main">
                <?php while (bp_group_members()) : bp_group_the_member(); ?>
                    <?php $member = bp_get_member_user_login(); ?>
                    <li  <?php echo in_array($member, $check) ? 'class="red"' : ''; ?> ><input type="checkbox" name="user_ids[<?php esc_attr(bp_member_user_login()) ?>]" class="check-user" value="<?php esc_attr(bp_member_user_login()) ?>" <?php echo in_array($member, $check) ? 'checked="checked"' : ''; ?> />
                        <?php bp_group_member_avatar_thumb(); ?>
                        <h5><?php echo $member; ?></h5>
                        <?php if (bp_is_active('friends')) : ?>
                            <div class="action">
                                <?php do_action('bp_directory_members_actions_loop', bp_get_member_user_login()); ?>
                            </div>
                        <?php endif; ?>
                    </li>
                <?php endwhile; ?>
            </ul>
            <?php do_action('bp_after_group_members_list'); ?>
        </div>
    <?php else: ?>
        <div id="message" class="info">
            <p><?php _e("Sorry, no members were found.", 'buddypress'); ?></p>
        </div>
    <?php
    endif;
}

function bp_gtm_truncate($text) {
    $matchesarray = array();
    $text = stripslashes(trim($text));
    preg_match('/\s+/i', $text, $matchesarray);
    if (!empty($matchesarray)) {
        return $text;
    } else {
        return substr($text, 0, 30);
    }
}

function bp_gtm_resps_loop($resps = array()) {
    global $bp;
    $count = array();
    $bp_gtm = get_option('bp_gtm');
    foreach ($resps as $resp) {
        if (in_array($resp->id, $count)) {
            continue;
        }//prevent duplicate users
        else {
            $count[] = $resp->id;
        }//prevent duplicate users
        if ($resp->id != 0) {
            $role = bp_gtm_get_role_for_user($resp->id);
            ?>
            <tr id="<?php echo $resp->id; ?>">
                <td class="td-title">
                    <?php
                    bp_gtm_poster_discussion_meta($resp->id);
                    $arg['item_id'] = $resp->id;
                    ?>
                </td>
                <?php if ($bp_gtm['groups'] == 'all' || array_key_exists($bp->groups->current_group->id, $bp_gtm['groups'])) { ?>
                    <td class="td-title">
                        <?php echo empty($role) ? __('Admin', 'bp_gtm') : $role->role_name; ?>
                    </td>
                <?php } ?>
                <td class="td-poster">
                    <?php bp_gtm_get_issues($resp->id, 'tasks') ?>
                </td>
                <td class="td-group">
                    <?php bp_gtm_get_issues($resp->id) ?>
                </td>
                <td id="users" class="td-freshness">
                    <?php
                    bp_gtm_get_involved_mention_link($bp->loggedin_user->id, $resp->id);
                    bp_gtm_get_involved_messaage_send_link($bp->loggedin_user->id, $resp->id);
                    bp_gtm_get_involded_send_email_link($resp->id);
                    ?>
                    <?php do_action('bp_directory_involved_actions', $resp->id); ?>
                </td>

                <?php do_action('bp_directory_involved_extra_cell') ?>
            </tr>
            <?php
            do_action('bp_directory_involved_extra_row');
        }
    }
}

/**
 * Get activity message link
 * @param int $bp_loggedin_user_id id of logining user
 * @param int $resp_id id of invilved user
 * @return bool true if access allowed and false in etc.
 */
function bp_gtm_get_involved_mention_link($bp_loggedin_user_id, $resp_id) {
    if (bp_gtm_check_access('involved_mention')) {
        ?>
        <a href="<?php echo bp_core_get_user_domain($bp_loggedin_user_id, false, false) . BP_ACTIVITY_SLUG . '/?r=' . bp_core_get_username($resp_id, false, false) ?>" title="<?php _e('Mention this user', 'bp_gtm'); ?>">@</a> | 
        <?php
        return true;
    } else {
        return false;
    }
}

/**
 * Get send private message to involved user
 * @param int $bp_loggedin_user_id id of logining user
 * @param int $resp_id id of invilved user
 * @return bool true if access allowed and false in etc.
 */
function bp_gtm_get_involved_messaage_send_link($bp_loggedin_user_id, $resp_id) {
    if (bp_gtm_check_access('involved_pm')) {
        ?>    
        <a href="<?php echo bp_core_get_user_domain($bp_loggedin_user_id, false, false) . BP_MESSAGES_SLUG . '/compose/?r=' . bp_core_get_username($resp_id, false, false) ?>" title="<?php _e('Send a private meassage', 'bp_gtm'); ?>"><?php _e('PM', 'bp_gtm'); ?></a>
        <?php
        return true;
    } else {
        return false;
    }
}

/**
 * Notify user about pending tasks and projects
 * @param int $resp_id id of inlilved user
 * @return bool true if access allowed and false in etc.
 */
function bp_gtm_get_involded_send_email_link($resp_id) {
    if (bp_gtm_check_access('involved_email')) {
        ?>
        | <a href="#" class="email_notify" id="<?php echo $resp_id ?>" title="<?php _e('Notify user about pending tasks and projects', 'bp_gtm') ?>"><?php _e('N', 'bp_gtm') ?></a>
        <?php
        return true;
    } else {
        return false;
    }
}

/**
 * Get count of tasks or projects in current group
 * @param int $resp_id id of involved member
 * @param string $issues_type tasks or projects count needs to calculate
 * @return bool true
 */
function bp_gtm_get_issues($resp_id, $issues_type = 'projects') {
    $count_tasks = BP_GTM_Resps::get_el_count($resp_id, $issues_type, bp_get_current_group_id());
    if ($issues_type == 'tasks') {
        echo sprintf(_n('%s task', '%s tasks', $count_tasks, 'bp_gtm'), $count_tasks);
    } else {
        echo sprintf(_n('%s project', '%s projects', $count_tasks, 'bp_gtm'), $count_tasks);
    }
    return TRUE;
}

function bp_gtm_terms_loop($tags, $tax) {
    global $bp;
    foreach ($tags as $tag) {
        ?>
        <tr class="" id="<?php echo $tag['id']; ?>">
            <td class="td-title">
                <a class="topic-title" href="<?php echo $gtm_link . $bp->action_variables[0] . '/view/' . $tag['id'] ?>" title="<?php _e('Permalink on this term page', 'bp_gtm') ?>"><?php echo bp_gtm_truncate($tag['name']); ?></a>
            </td>
            <td class="td-postcount"><?php echo $tag['count']; ?></td>
            <td id="tag" class="td-freshness">
                <?php if (bp_gtm_check_access('taxon_edit')) { ?>
                    <a class="edit_me" id="<?php echo $tag['id']; ?>" href="#"><img height='16' width='16' src="<?php echo GTM_URL ?>_inc/images/edit.png" alt="<?php _e('Edit', 'bp_gtm') ?>" /></a>&nbsp;
                <?php } ?>
                <?php if (bp_gtm_check_access('taxon_delete')) { ?>
                    <a class="delete_me" id="<?php echo $tag['id']; ?>" href="#"><img height='16' width='16' src="<?php echo GTM_URL ?>_inc/images/delete.png" alt="<?php _e('Delete', 'bp_gtm') ?>" /></a>
                <?php } ?>
            </td>

            <?php do_action('bp_directory_' . $tax . '_extra_cell') ?>
        </tr>

        <?php do_action('bp_directory_' . $tax . '_extra_row') ?>
        <?php
    }
}

function bp_gtm_terms_nodes($elements = array(), $task_or_proj = 'project', $gtm_link) {
    foreach ($elements as $task_id => $task_name):
        ?>
        <tr class="" id="<?php echo $task_id; ?>">
            <td class="td-title">
                <a href="<?php echo $gtm_link . $task_or_proj . '/view/' . $task_id ?>" title="<?php sprintf(_e('Permalink to this %s page', 'bp_gtm'), $task_or_proj) ?>"><?php echo $task_name; ?></a>
            </td>
            <td class="td-postcount">
                <?php bp_gtm_is_elem_done($task_id, $task_or_proj) ? _e('Yes', 'bp_gtm') : _e('No', 'bp_gtm'); ?>
            </td>
        </tr>
        <?php
    endforeach;
}

function bp_gtm_task_project($parent_task = 0, $task = null) {
    if ($parent_task == 0) :
        ?>
        <p>
            <label for="task_project"><?php _e('Project that this task corresponds to', 'bp_gtm'); ?></label>
            <?php
            $projects = bp_gtm_get_projects(bp_get_current_group_id(), 'alpha');
            if (count($projects) > 0) :
                ?>
            <table>
                <?php
                foreach ($projects as $project) :
                    ($task == $project->id) ? $checked = 'checked="checked" ' : $checked = '';
                    ?>
                    <tr>
                        <td class="task-project"><input type="radio" name="task_project" value="<?php echo $project->id ?>" <?php echo $checked; ?>/><?php echo $project->name ?></td>
                        <td class="padding0"><?php _e('Deadline', 'bp_gtm') ?>: <?php bp_gtm_format_date($project->deadline); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <?php
        else :
            _e('Please create at least one project to proceed.', 'bp_gtm');
        endif;
        ?>
        </p>
        <?php
    endif;
    return count($projects);
}

function bp_gtm_term_task_edit_loop($task_id, $tax) {
    $terms = BP_GTM_Taxon::get_terms_4task(bp_get_current_group_id(), $task_id, $tax);
    if (count($terms) > 0) {
        if($tax!='tag'){
        foreach ($terms as $tag) {
            ($tag['used'] == '1') ? $used = 'checked="checked"' : $used = '';
            echo '<input name="task_old_' . $tax . 's[]" type="checkbox" ' . $used . ' value="' . stripslashes($tag['name']) . '" /> ' . stripslashes($tag['name']) . '<br>';
        }
        } else {
           foreach ($terms as $tag) {
            if($tag['used'] == '1')
             echo '<li class="resps-tab" id="un-tag"><span>' . stripslashes($tag['name']) . '</span> <span class="p">X</span></li>';
        } 
        }
    } else {
        $link = 'terms';
        echo '<p>' . sprintf(__('There are no %ss to display. Create them <a href="%s" target="_blank">here</a>', 'bp_gtm'), $tax, $link) . '</p>';
    }
}

function bp_gtm_discussion_list($task_id, $tax = 'task', $avatar) {
    ?>
    <ul id="topic-post-list" class="item-list discussions">
        <?php
        $posts = BP_GTM_Discussion::get_posts($task_id, $tax);
        $i = 1;
        if (count($posts) > 0)
            foreach ($posts as $post) {
                ?>
                <li id="post-<?php echo $post->id; ?>" class="alt">
                    <div class="poster-meta">
                        <?php bp_gtm_poster_discussion_meta($post->author_id); ?>
                        <?php echo '&nbsp; ' . sprintf(__('said %s:', 'bp_gtm'), bp_core_time_since($post->date_created)) ?>
                    </div>

                    <div class="post-content"><?php echo $post->text; ?></div>

                    <?php do_action('bp_gtm_discuss_after_content', $post, 'discuss', true); ?>

                    <div class="admin-links">
                        <?php bp_gtm_discuss_admin_links($post->id, $i, $tax, $task_id); ?>
                    </div>
                </li>
                <?php
                $i++;
            }
        ?>
    </ul>
    <?php
}

function bp_gtm_get_responsibles($task_resp_id) {
    $users_logins = explode(' ', $task_resp_id);
    $arg['width'] = '25';
    $arg['height'] = '25';

    if (!empty($users_logins)) {
        echo '<span class="responsible">' . __('Responsible', 'bp_gtm') . '</span><br>';
        foreach ($users_logins as $users_login) {
            if ($users_login != '') {
                $arg['item_id'] = bp_core_get_userid($users_login);
                ?>
                <a class="responsible-link" href="<?php echo bp_core_get_userlink($arg['item_id'], false, true); ?>" title="<?php echo bp_core_get_userlink($arg['item_id'], true, false); ?>">
                    <?php echo bp_core_fetch_avatar($arg); ?>
                </a>
                <?php
            }
        }
    }
}

function bp_gtm_terms_for_project($project_id, $tax) {
    $terms = BP_GTM_Taxon::get_terms_4project(bp_get_current_group_id(), $project_id, $tax);
    if (count($terms) > 0) {
        if ($tax != 'tag') {
            echo '<p>';
            foreach ($terms as $tag) {
                if ($tag['name'] != '') {
                    ($tag['used'] == '1') ? $used = 'checked="checked"' : $used = '';
                    echo '<input name="project_old_' . $tax . 's[]" type="checkbox" ' . $used . ' value="' . stripslashes($tag['name']) . '" /> ' . stripslashes($tag['name']) . '<br>';
                }
            }
            echo '</p>';
        } else {
            foreach ($terms as $tag) {
                if ($tag['used'] == '1')
                    echo '<li class="resps-tab" id="un-tag"><span>' . stripslashes($tag['name']) . '</span> <span class="p">X</span></li>';
            }
        }
    } else {
        $link = $gtm_link . 'terms';
        echo sprintf(__('<p>There are no tags to display. Create them <a href="%s" target="_blank">here</a></p>', 'bp_gtm'), $link);
    }
}

function bp_gtm_get_cats_for_group() {
    $terms = BP_GTM_Taxon::get_terms_in_group(bp_get_current_group_id(), 'cat');
    if (count($terms) > 0) {
        echo '<div class="group_cats">';
        foreach ($terms as $tag) {
            if ($tag['name'] != '') {
                ($tag['used'] == '1') ? $used = 'checked="checked"' : $used = '';
                echo '<p><input name="project_cats[]" type="checkbox" ' . $used . ' value="' . stripslashes($tag['name']) . '" /> <span>' . stripslashes($tag['name']) . '</span></p>';
            }
        }
        echo '</div>';
    }
}

function bp_gtm_project_list_settings() {
    $options = array();
    $options['filter'] = !empty($_GET['filter']) ? $_GET['filter'] : 'deadline';
    $options['action'] = !empty($_GET['action']) ? 'project_' . $_GET['action'] : 'project_view';
    if ($options['filter'] == 'done') {
        $options['page_h4'] = __('Completed Projects', 'bp_gtm');
        $options['projects'] = bp_gtm_get_projects(bp_get_current_group_id(), 'done');
        $options['done_style'] = 'class="grey"';
    } elseif ($options['filter'] == 'alpha') {
        $options['page_h4'] = __('Projects by A/Z', 'bp_gtm');
        $options['projects'] = bp_gtm_get_projects(bp_get_current_group_id(), 'alpha');
        $options['alpha_style'] = 'class="grey"';
    } elseif ($options['filter'] == 'deadline') {
        $options['page_h4'] = __('Projects by Deadline', 'bp_gtm');
        $options['projects'] = bp_gtm_get_projects(bp_get_current_group_id(), 'deadline');
        $options['deadline_style'] = 'class="grey"';
    }

    return $options;
}

function bp_gtm_task_list_settings($bp_gtm_group_settings) {
    $limit['per_page'] = $option['per_page'] = $bp_gtm_group_settings['tasks_pp']; // how many to show
    $limit['miss'] = $option['miss'] = 0; // from the very first one - need to be on the 1st page
    $option['filter'] = !empty($_GET['filter']) ? $_GET['filter'] : '';
    $option['action'] = !empty($_GET['action']) ? $_GET['action'] : 'task_view';

    if (empty($_GET['project'])) {
        $option['project_id'] = false;
        if ($option['filter'] == 'done') {
            $option['page_h4'] = __('All Completed Tasks', 'bp_gtm');
            $option['tasks'] = bp_gtm_get_tasks(bp_get_current_group_id(), $option['filter'] = 'done', false, $limit);
            $option['done_style'] = 'class="grey"';
        } elseif ($option['filter'] == 'alpha') {
            $option['page_h4'] = __('Tasks by A/Z', 'bp_gtm');
            $option['tasks'] = bp_gtm_get_tasks(bp_get_current_group_id(), $option['filter'] = 'alpha', false, $limit);
            $option['alpha_style'] = 'class="grey"';
        } elseif ($option['filter'] == 'deadline') {
            $option['page_h4'] = __('Tasks by Deadline', 'bp_gtm');
            $option['tasks'] = bp_gtm_get_tasks(bp_get_current_group_id(), $option['filter'] = 'deadline', false, $limit);
            $option['deadline_style'] = 'class="grey"';
        } elseif ($option['filter'] == 'without') {
            $option['page_h4'] = __('Tasks whithout Project', 'bp_gtm');
            $option['tasks'] = bp_gtm_get_tasks(bp_get_current_group_id(), $option['filter'], false, $limit);
            $option['by_proj_style'] = 'class="grey"';
        } else {
            $option['page_h4'] = __('Tasks by Deadline', 'bp_gtm');
            $option['tasks'] = bp_gtm_get_tasks(bp_get_current_group_id(), $option['filter'] = 'deadline', false, $limit);
            $option['deadline_style'] = 'class="grey"';
        }
    } elseif (!empty($_GET['project']) && !empty($option['filter'])) {
        if ($option['filter'] == 'done') {
            $option['project_id'] = $_GET['project'];
            $option['page_h4'] = __('Completed Tasks in Project', 'bp_gtm') . ' "' . bp_gtm_get_el_name_by_id($_GET['project'], 'project') . '"';
            $option['tasks'] = bp_gtm_get_tasks(bp_get_current_group_id(), $option['filter'] = 'done', $_GET['project'], $limit);
            $option['by_proj_style'] = 'class="grey"';
        }
    } elseif (!empty($_GET['project']) && empty($option['filter'])) {
        if ($_GET['project'] == 'without') {
            $option['page_h4'] = __('Tasks whithout Project', 'bp_gtm');
            $option['tasks'] = BP_GTM_Tasks::get_task_whithout_project(bp_get_current_group_id());
            $option['by_proj_style'] = 'class="grey"';
        } else {
            $option['page_h4'] = __('Tasks by Deadline', 'bp_gtm');
            $option['tasks'] = bp_gtm_get_tasks(bp_get_current_group_id(), $option['filter'] = 'inproject', $_GET['project'], $limit);
            $option['deadline_style'] = 'class="grey"';
        }
    } else {
        $option['project_id'] = false;
        $option['page_h4'] = __('Pending Tasks in Project', 'bp_gtm') . ' "' . bp_gtm_get_el_name_by_id($_GET['project'], 'project') . '"';
        $option['tasks'] = bp_gtm_get_tasks(bp_get_current_group_id(), $option['filter'] = 'project', $_GET['project'], $limit);
        $option['view_project'] = '<a href="' . $gtm_link . 'projects/view/' . $_GET['project'] . '" class="button" title="' . __('Go to project\'s page', 'bp_gtm') . '">' . __('View Project', 'bp_gtm') . '</a>';
        $option['filter'] = $_GET['project'];
        $option['by_proj_style'] = 'class="grey"';
    }

    return $option;
}

// Load next page content
function bp_gtm_tasks_navi_content() {
    global $bp, $wpdb;

    $data = explode('-', $_GET['nextPage']);
    $limit['miss'] = ($data['0'] * $data['1']);
    $limit['per_page'] = $data['1'];

    if ($_GET['project'] && !$_GET['filter']) {
        $tasks = bp_gtm_get_tasks($bp->groups->current_group->id, $filter = 'project', $_GET['project'], $limit);
    } elseif ($_GET['project'] && $_GET['filter'] == 'done') {
        $tasks = bp_gtm_get_tasks($bp->groups->current_group->id, $filter = 'done', $_GET['project'], $limit);
    } else {
        $tasks = bp_gtm_get_tasks($bp->groups->current_group->id, $_GET['filter'], false, $limit);
    }

    if (!empty($tasks)) {
        $i = 1;
        foreach ((array) $tasks as $task) {
            $alt = is_int($i / 2) ? ' alt' : '';
            ?>
            <tr id="<?php echo $task->id; ?>" class="<?php
            bp_gtm_task_check_date($task->id, $task->deadline);
            echo $alt
            ?>">
                <td class="td-title">
                    <span class="subtasks_<?php echo $task->id; ?> td-title">
                        <span class="all_in_all" title="<?php _e('Current number of subtasks', 'bp_gtm'); ?>"><?php echo bp_gtm_get_subtasks_count($task->id) ?></span>&nbsp;
                        <?php bp_get_create_subtask_link($task->id, $gtm_link); ?>
                    </span>
                    <?php bp_gtm_view_link($task->id, $task->name, $gtm_link, 'task') ?>

                </td>
                <td class="td-poster">
                    <?php bp_gtm_get_responsibles($task->resp_id); ?>
                </td>
                <td class="td-group">
                    <?php bp_gtm_view_link($task->project_id, bp_gtm_get_el_name_by_id($task->project_id, 'project'), $gtm_link, 'project'); ?>
                </td>
                <td class="td-group">
                    <div class="object-name center"><?php bp_gtm_format_date($task->deadline) ?></div>
                </td>
                <td id="tasks" class="td-freshness">
                    <?php
                    bp_gtm_edit_link($task->id, $gtm_link, 'tasks');
                    bp_gtm_delete_task_link($task->id);
                    bp_gtm_done_link($option['filter'], $task->id, $el_type = 'task');
                    ?>
                </td>

                <?php do_action('bp_directory_tasks_extra_cell') ?>
            </tr>
            <?php
            do_action('bp_directory_tasks_extra_row');
            $i++;
        }
    } else {
        echo '<div id="message" class="info"><p>' . __('There are no tasks to display.', 'bp_gtm') . '</p></div>';
    }
}

add_action('wp_ajax_bp_gtm_tasks_navi_content', 'bp_gtm_tasks_navi_content');

// Load next page content
function bp_gtm_discuss_navi_content() {
    global $bp, $wpdb;

    $data = explode('-', $_GET['nextPage']);
    $limit['miss'] = ($data['0'] * $data['1']);
    $limit['per_page'] = $data['1'];

    $discusses = BP_GTM_Discussion::get_list($bp->groups->current_group->id, $_GET['what'], $limit);

    if (count($discusses) > 0) {
        $i = 1;
        foreach ((array) $discusses as $post) {
            if (is_int($i / 2)) {
                $alt = ' alt';
            } else {
                $alt = '';
            }
            ?>
            <tr class="<?php echo $alt ?>">
                <td class="td-title">
                    <?php bp_gtm_view_disscuss_link($post->elem_id, $gtm_link, $_GET['what']); ?>
                </td>
                <td class="td-poster">
                    <?php bp_gtm_poster_discussion_meta($post->author_id); ?></div>
            </td>
            <td class="td-postcount">
                <?php echo $post->discuss_count; ?>
            </td>
            <td class="td-freshness"><div class="object-name" class="center"><?php bp_gtm_format_date($post->date_created) ?></div>
            </td>

            <?php do_action('bp_gtm_discuss_extra_cell') ?>
            </tr>
            <?php
            do_action('bp_gtm_discuss_extra_row');
            $i++;
        }
    } else {
        echo '<div id="message" class="info"><p>' . __('There are no posts to display.', 'bp_gtm') . '</p></div>';
    }
}

add_action('wp_ajax_bp_gtm_discuss_navi_content', 'bp_gtm_discuss_navi_content');

// Discussions page pagination func
function bp_gtm_discuss_navi($type = 'tasks', $group_id = false, $per_page = 20) {
    global $bp, $wpdb;
    $cur = 1;
    $discusses = BP_GTM_Discussion::get_count($type, $group_id);

    $pages = ceil($discusses / $per_page);

    if ($pages > 1) {
        echo '<p class="navi" id="' . $discusses . '">';
        echo sprintf(__('<span id="cur_discuss">%d - %d</span> out of %d.&nbsp;', 'bp_gtm'), $cur, $per_page, $discusses);
        _e('Pagination:', 'bp_gtm');
        for ($i = 0; $i < $pages; $i++) {
            $c = $i + 1;
            $cur = $cur - 1;
            $current = $cur == $i ? ' current' : '';
            echo '&nbsp;<a id="' . $i . '-' . $per_page . '" class="discuss_navi' . $current . '" href="#">' . $c . '</a>';
        }
        echo '</p>';
    } else {
        _e('List of the latest tasks/projects discussions in this group.', 'bp_gtm');
    }
}

// Display admin links near every post - view, edit, delete or whatever
function bp_gtm_discuss_admin_links($post_id, $post_number, $elem_type, $elem_id) {
    global $bp;
    ?>

    <a href="#post-<?php echo $post_id; ?>" title="<?php _e('Permalink', 'bp_gtm') ?>">#<?php echo $post_number ?></a>

    <?php if (bp_gtm_check_access('discuss_edit')) { ?>
        <a class="edit_post" id="<?php echo $post_id ?>" rel="<?php echo $elem_type . '_' . $elem_id; ?>" href="#" title="<?php _e('Edit', 'bp_gtm') ?>"><?php _e('Edit', 'bp_gtm'); ?></a>
        <?php
    }
    if (bp_gtm_check_access('discuss_delete')) {
        ?>
        <a class="delete_post" id="<?php echo $post_id ?>" rel="<?php echo $elem_type . '_' . $elem_id; ?>" href="#" title="<?php _e('Delete', 'bp_gtm') ?>"><?php _e('Delete', 'bp_gtm'); ?></a>
        <?php
    }

    do_action('bp_gtm_discuss_admin_links');
}

// Load next page content
function bp_gtm_personal_tasks_navi_content() {
    global $bp, $wpdb;

    $data = explode('-', $_GET['nextPage']);
    $limit['miss'] = ($data['0'] * $data['1']);
    $limit['per_page'] = $data['1'];

    if ($_GET['project'] && !$_GET['filter']) {
        $tasks = BP_GTM_Personal::get_tasks($bp->loggedin_user->id, $filter = 'project', $limit, $_GET['project']);
    } elseif ($_GET['group'] && !$_GET['filter']) {
        $tasks = BP_GTM_Personal::get_tasks($bp->loggedin_user->id, $filter = 'group', $limit, $_GET['group']);
    } else {
        if ($_GET['filter'] == 'done') {
            $tasks = BP_GTM_Personal::get_tasks($bp->loggedin_user->id, $filter = 'done', $limit);
        } elseif ($_GET['filter'] == 'alpha') {
            $tasks = BP_GTM_Personal::get_tasks($bp->loggedin_user->id, $filter = 'alpha', $limit);
        } elseif ($_GET['filter'] == 'deadline') {
            $tasks = BP_GTM_Personal::get_tasks($bp->loggedin_user->id, $filter = 'deadline', $limit);
        } elseif ($_GET['filter'] == 'without') {
            $tasks = BP_GTM_Personal::get_tasks($bp->loggedin_user->id, $filter = 'without', $limit);
        } else {
            $tasks = BP_GTM_Personal::get_tasks($bp->loggedin_user->id, $filter = 'deadline', $limit);
        }
    }

    if (count($tasks) > 0) {
        $i = 1;
        foreach ((array) $tasks as $task) {
            if (is_int($i / 2)) {
                $alt = ' alt';
            } else {
                $alt = '';
            }
            $gtm_link = bp_get_group_permalink() . $bp->gtm->slug . '/';
            ?>
            <tr id="<?php echo $task->id; ?>" class="<?php
            bp_gtm_task_check_date($task->id, $task->deadline);
            echo $alt
            ?>">
                <td class="td-title">
                    <a class="topic-title" href="<?php echo $gtm_link . $bp->current_action . '/view/' . $task->id ?>" title="<?php _e('Permalink on task\'s page', 'bp_gtm') ?>">
                        <?php echo $task->name; ?>
                    </a>
                </td>
                <td class="td-group">
                    <?php echo '<div class="object-name">
                              <a href="' . $gtm_link . 'projects/view/' . $task->project_id . '" title="' . __('Go to project\'s page', 'bp_gtm') . '">
                                 ' . bp_gtm_get_el_name_by_id($task->project_id, 'project') . '
                              </a>
                           </div>'; ?>
                </td>
                <td class="td-poster">
                    <?php
                    $group = groups_get_group(array('group_id' => $task->group_id));
                    //print_var($group);
                    echo '<a href="' . $gtm_link . '" title="' . $group->description . '">' . $group->name . '</a>';
                    ?>
                </td>
                <td class="td-group"><div class="object-name" class="text-right"><?php bp_gtm_format_date($task->deadline); ?></div>
                </td>

                <?php do_action('bp_directory_personal_tasks_extra_cell') ?>
            </tr>
            <?php
            do_action('bp_directory_personal_tasks_extra_row');
            $i++;
        }
    } else {
        echo '<div id="message" class="info"><p>' . __('There are no tasks to display.', 'bp_gtm') . '</p></div>';
    }
}

add_action('wp_ajax_bp_gtm_personal_tasks_navi_content', 'bp_gtm_personal_tasks_navi_content');


// Display current role of a member in group members list
add_action('bp_group_members_list_item', 'bp_gtm_display_span_role');

function bp_gtm_display_span_role($user_id = false) {
    global $bp, $members_template;

    $bp_gtm = get_option('bp_gtm');

    if ($bp_gtm['groups'] == 'all' || array_key_exists($bp->groups->current_group->id, $bp_gtm['groups'])) {

        if (!$user_id)
            $user_id = $members_template->member->user_id;

        $role = bp_gtm_get_role_for_user($user_id);

        if ($role)
            $role_span = '<span class="activity role">' . __('Role', 'bp_gtm') . ' &rarr; ' . $role->role_name . '</span>';

        if (!$role && groups_is_user_admin($user_id, $bp->groups->current_group->id))
            $role_span = '<span class="activity role">' . __('Role', 'bp_gtm') . ' &rarr; ' . __('Group Admin', 'bp_gtm') . '</span>';
        elseif (!$role && !groups_is_user_admin($user_id, $bp->groups->current_group->id))
            $role_span = '<span class="activity role">' . __('No Role', 'bp_gtm') . '</span>';

        echo $role_span;
    }
}

function bp_gtm_role_actions($role) {
    echo '<input id="input-' . $role->id . '" type="hidden" name="role[' . $role->id . '][role_id]" value="' . $role->id . '" />';
    echo '<li id="li-' . $role->id . '" class="one">
        #' . $role->id . ': ' . $role->role_name . '  &rarr; <input name="role[' . $role->id . '][role_name]" type="text" value="' . $role->role_name . '" />
        <span class="actions">
            <a id="role-open" rel="' . $role->id . '" class="button" href="">' . __('Permissions', 'bp_gtm') . '</a>
            <a id="role-delete" rel="' . $role->id . '" class="button" href="">' . __('Delete role', 'bp_gtm') . '</a>
        </span>
    </li>
    <div id="toggler">
        <div id="box-' . $role->id . '" class="box">
            <div class="left_block">
                <div class="projects">
                    <p class="role_title">' . __('Projects', 'bp_gtm') . '</p><ul>';
    $actions_project = bp_gtm_get_actions('project', 'all');
    foreach ($actions_project as $action_slug => $action_name) {
        echo '<li>
                                <input type="checkbox" name="role[' . $role->id . '][' . $action_slug . ']" value = "1" ' . bp_gtm_get_checked($role->$action_slug, '1') . ' /> ' . $action_name . '
                            </li>';
    }
    echo '</ul></div><!-- /projects -->
                <div class="tasks">
                    <p class="role_title">' . __('Tasks', 'bp_gtm') . '</p><ul>';
    $actions_task = bp_gtm_get_actions('task', 'all');
    foreach ($actions_task as $action_slug => $action_name) {
        echo '<li>
                                <input type="checkbox" name="role[' . $role->id . '][' . $action_slug . ']" value = "1" ' . bp_gtm_get_checked($role->$action_slug, '1') . ' /> ' . $action_name . '
                            </li>';
    }
    echo '</ul></div><!-- /tasks -->
                <div class="delete">
                    <p class="role_title">' . __('Delete', 'bp_gtm') . '</p><ul>';
    $actions_delete = bp_gtm_get_actions('delete', 'all');
    foreach ($actions_delete as $action_slug => $action_name) {
        echo '<li>
                                <input type="checkbox" name="role[' . $role->id . '][' . $action_slug . ']" value = "1" ' . bp_gtm_get_checked($role->$action_slug, '1') . ' /> ' . $action_name . '
                            </li>';
    }
    echo '</ul></div><!-- /delete -->
            </div><!-- /left_block -->
            <div class="right_block">
                <div class="taxon">
                    <p class="role_title">' . __('Classifier', 'bp_gtm') . '</p><ul>';
    $actions_taxon = bp_gtm_get_actions('taxon', 'all');
    foreach ($actions_taxon as $action_slug => $action_name) {
        echo '<li>
                                <input type="checkbox" name="role[' . $role->id . '][' . $action_slug . ']" value = "1" ' . bp_gtm_get_checked($role->$action_slug, '1') . ' /> ' . $action_name . '
                            </li>';
    }
    echo '</ul></div><!-- /taxon -->
                <div class="involved">
                    <p class="role_title">' . __('Involved', 'bp_gtm') . '</p><ul>';
    $actions_involved = bp_gtm_get_actions('involved', 'all');
    foreach ($actions_involved as $action_slug => $action_name) {
        echo '<li>
                                <input type="checkbox" name="role[' . $role->id . '][' . $action_slug . ']" value = "1" ' . bp_gtm_get_checked($role->$action_slug, '1') . ' /> ' . $action_name . '
                            </li>';
    }
    echo '</ul></div><!-- /involved -->
                <div class="discuss">
                    <p class="role_title">' . __('Discussions', 'bp_gtm') . '</p><ul>';
    $actions_discuss = bp_gtm_get_actions('discuss', 'all');
    foreach ($actions_discuss as $action_slug => $action_name) {
        echo '<li>
                                <input type="checkbox" name="role[' . $role->id . '][' . $action_slug . ']" value = "1" ' . bp_gtm_get_checked($role->$action_slug, '1') . ' /> ' . $action_name . '
                            </li>';
    }
    echo '</ul></div><!-- /discuss -->
             <div class="settings">
                    <p class="role_title">' . __('Settings', 'bp_gtm') . '</p><ul>';
    $actions_settings = bp_gtm_get_actions('settings', 'all');
    foreach ($actions_settings as $action_slug => $action_name) {
        echo '<li>
                                <input type="checkbox" name="role[' . $role->id . '][' . $action_slug . ']" value = "1" ' . bp_gtm_get_checked($role->$action_slug, '1') . ' /> ' . $action_name . '
                            </li>';
    }
    echo '</ul></div><!-- /settings -->
            </div><!-- /right_block -->
        </div><!-- /box --><div class"clear"></div>
    </div><!-- /toggler -->';
}

function bp_gtm_get_presonal_tasks_option($bp_gtm_p_tasks_pp) {
    global $bp;
    $limit['per_page'] = $options['per_page'] = $bp_gtm_p_tasks_pp; // how many to show
    $limit['miss'] = $options['miss'] = 0; // from the very first one - need to be on the 1st page

    $options['done'] = 0;
    $options['user'] = $bp->loggedin_user->id;
    $options['id'] = 0;
    $options['type'] = !empty($_GET['filter']) ? $_GET['filter'] : '';
    $options['alpha_style'] = $options['deadline_style'] = $options['by_proj_style'] = $options['by_group_style'] = $options['done_style'] = '';
    if (empty($_GET['project']) && empty($_GET['group'])) { // standard filters
        $project_id = false;
        if (!empty($_GET['filter']) && $_GET['filter'] == 'done') {
            $options['done'] = 1;
            $options['page_h4'] = __('All Completed Personal Tasks', 'bp_gtm');
            $options['tasks'] = BP_GTM_Personal::get_tasks($bp->loggedin_user->id, $filter = 'done', $limit);
            $options['done_style'] = 'class="grey"';
        } elseif (!empty($_GET['filter']) && $_GET['filter'] == 'alpha') { // done
            $options['page_h4'] = __('Personal Tasks by A/Z', 'bp_gtm');
            $options['tasks'] = BP_GTM_Personal::get_tasks($bp->loggedin_user->id, $filter = 'alpha', $limit);
            $options['alpha_style'] = 'class="grey"';
        } elseif (empty($_GET['filter']) || $_GET['filter'] == 'deadline') { // done
            $options['page_h4'] = __('Personal Tasks by Deadline', 'bp_gtm');
            $options['tasks'] = BP_GTM_Personal::get_tasks($bp->loggedin_user->id, $filter = 'deadline', $limit);
            $options['deadline_style'] = 'class="grey"';
        } elseif (!empty($_GET['filter']) && $_GET['filter'] == 'without') { // done
            $options['page_h4'] = __('Personal Tasks without Projects', 'bp_gtm');
            $options['tasks'] = BP_GTM_Personal::get_tasks($bp->loggedin_user->id, $filter = 'without', $limit);
            $options['by_proj_style'] = 'class="grey"';
        }
    } elseif (empty($_GET['project']) && !empty($_GET['group'])) {  // show tasks in group
        $options['group'] = groups_get_group(array('group_id' => $_GET['group']));
        $options['page_h4'] = __('Personal Tasks in Group', 'bp_gtm') . ' "' . $group->name . '"';
        $options['tasks'] = BP_GTM_Personal::get_tasks($bp->loggedin_user->id, $filter = 'group', $limit, $_GET['group']);
        $options['id'] = $_GET['group'];
        $options['type'] = 'group';
        $options['by_group_style'] = 'class="grey"';
    } elseif (!empty($_GET['project']) && empty($_GET['group'])) {  // show tasks in project
        $options['page_h4'] = __('Personal Tasks in Project', 'bp_gtm') . ' "' . bp_gtm_get_el_name_by_id($_GET['project'], 'project') . '"';
        $options['tasks'] = BP_GTM_Personal::get_tasks($bp->loggedin_user->id, $filter = 'project', $limit, $_GET['project']);
        $options['id'] = $_GET['project'];
        $options['type'] = 'project';
        $options['by_proj_style'] = 'class="grey"';
    }

    return $options;
}

function bp_gtm_get_personal_filter_project_list() {
    global $bp;
    ?> <div id="toggler" class="padder">
        <div id="box" class="filter_project">
            <p><strong><?php _e('Projects List', 'bp_gtm') ?></strong><span class="close"><a id="open_project" href="#" title="<?php _e('Close', 'bp_gtm'); ?>">X</a></span></p>
            <ul class="projects-filter">
                <li><a href="tasks?filter=without">Without Project</a></li><?php
    $projects = BP_GTM_Personal::get_projects($bp->loggedin_user->id, $filter = 'alpha');
    foreach ($projects as $project) {
        $personal_link = $bp->loggedin_user->domain . $bp->gtm->slug . '/';
        echo '<li><a href="' . $personal_link . 'tasks?project=' . $project->id . '"> ' . $project->name . '</a></li>';
    }
    ?>
            </ul>
            <p><small><?php _e('Click on a project to see its tasks', 'bp_gtm'); ?></small></p>
        </div>
        <?php
        return TRUE;
    }

    function bp_gtm_get_personal_filter_group_list() {
        global $bp;
        ?>
        <div id="box" class="filter_group">
            <p><strong><?php _e('Groups List', 'bp_gtm') ?></strong><span class="close"><a id="open_group" href="#" title="<?php _e('Close', 'bp_gtm'); ?>">X</a></span></p>
            <ul class="projects-filter">
                <?php
                $groups = BP_GTM_Personal::get_groups($bp->loggedin_user->id, $filter = 'alpha');
                foreach ($groups as $group) {
                    $personal_link = $bp->loggedin_user->domain . $bp->gtm->slug . '/';
                    echo '<li><a href="' . $personal_link . 'tasks?group=' . $group->id . '"> ' . $group->name . '</a></li>';
                }
                ?>
            </ul>
            <p><small><?php _e('Click on a group to see its tasks', 'bp_gtm'); ?></small></p>
        </div>
        <?php
    }

    function bp_gtm_get_groups_filter_list() {
        ?>
        <div id="toggler">
            <div id="box" id="box">
                <p><strong><?php _e('Projects List', 'bp_gtm') ?></strong><span class="close"><a id="open" href="#" title="<?php _e('Close', 'bp_gtm'); ?>">X</a></span></p>
                <ul class="projects-filter">
                    <li><a href="tasks?filter=without">Without Project</a></li>
                    <?php
                    $projects = bp_gtm_get_projects(bp_get_current_group_id(), $option['filter'] = 'alpha');
                    foreach ($projects as $project) {
                        echo '<li><a href="' . $gtm_link . 'tasks?project=' . $project->id . '"> ' . $project->name . '</a></li>';
                    }
                    ?>
                </ul>
                <p><small><?php _e('Click on a project to see its tasks', 'bp_gtm'); ?></small></p>
            </div>
        </div>
        <?php
    }

    function bp_gtm_get_personal_task_list($tasks) {
        global $bp;
        foreach ($tasks as $task) {
            $group = groups_get_group(array('group_id' => $task->group_id));
            $gtm_link = bp_get_group_permalink($group) . $bp->gtm->slug . '/';
            ?>
            <tr id="<?php echo $task->id; ?>" class="<?php bp_gtm_task_check_date($task->id, $task->deadline); ?>">
                <td class="td-title">
                    <a class="topic-title" href="<?php echo $gtm_link . $bp->current_action . '/view/' . $task->id ?>" title="<?php _e('Permalink on task\'s page', 'bp_gtm') ?>">
                        <?php echo $task->name; ?>
                    </a>
                </td>
                <td class="td-group">
                    <?php echo '<div class="object-name">
                                  <a href="' . $gtm_link . 'projects/view/' . $task->project_id . '" title="' . __('Go to project\'s page', 'bp_gtm') . '">
                                     ' . bp_gtm_get_el_name_by_id($task->project_id, 'project') . '
                                  </a>
                               </div>'; ?>
                </td>
                <td class="td-poster">
                    <?php
                    echo '<a href="' . $gtm_link . '" title="' . $group->description . '">' . $group->name . '</a>';
                    ?>
                </td>
                <td class="td-group">
                    <div class="object-name center"><?php bp_gtm_format_date($task->deadline); ?></div>
                </td>

                <?php do_action('bp_gtm_personal_tasks_extra_cell') ?>
            </tr>
            <?php
            do_action('bp_gtm_personal_tasks_extra_row');
        }
    }

    function bp_gtm_get_personal_project_list($projects) {
        global $bp;
        foreach ((array) $projects as $project) {
            $group = groups_get_group(array('group_id' => $project->group_id));
            $gtm_url = bp_get_group_permalink($group) . $bp->gtm->slug
            ?>
            <tr class="">

                <td class="td-title">
                    <a class="topic-title" href="<?php echo $gtm_url . '/projects/view/' . $project->id ?>" title="<?php _e('Permalink', 'bp_gtm') ?>">
                        <?php echo $project->name; ?>
                    </a>
                </td>
                <td class="td-poster"><!-- group name -->
                    <?php
                    echo '<a href="' . $gtm_url . '" title="' . $group->description . '">' . $group->name . '</a>';
                    ?>
                </td>
                <td class="td-poster">
                    <div class="object-name" class="center"><?php bp_gtm_format_date($project->deadline); ?></div>
                </td>
                <?php do_action('bp_gtm_personal_projects_extra_cell') ?>
            </tr>
            <?php
            do_action('bp_gtm_personal_projects_extra_row');
        }
    }

    function bp_gtm_get_personal_profile_settings() {
        global $bp;
        $options['filter'] = !empty($_GET['filter']) ? $_GET['filter'] : '';
        if (empty($_GET['filter']) || $_GET['filter'] == 'deadline') {
            $options['projects'] = BP_GTM_Personal::get_projects($bp->loggedin_user->id, $options['filter']);
            $options['h4_title'] = __('Personal Projects by Deadline', 'bp_gtm');
            $options['styles_d_cur'] = 'grey';
        } elseif ($_GET['filter'] == 'alpha') {
            $options['projects'] = BP_GTM_Personal::get_projects($bp->loggedin_user->id, $options['filter']);
            $options['h4_title'] = __('Personal Projects by A/Z', 'bp_gtm');
            $options['styles_a_cur'] = 'grey';
        }
        return $options;
    }

    function bp_gtm_get_discussion_settings($bp_gtm_group_settings) {
        $options = array();
        $limit['per_page'] = $bp_gtm_group_settings['discuss_pp']; // how many to show
        $limit['miss'] = 0; // from the very first one - need to be on the 1st page

        if (empty($_GET['filter']) || $_GET['filter'] == 'tasks') {
            $options['h4_title'] = __('Tasks Discussions', 'bp_gtm');
            $options['type'] = 'tasks';
            $options['th_title'] = __('Task Name', 'bp_gtm');
            $options['styles_t_cur'] = 'grey';
            $options['styles_p_cur'] = '';
        } elseif (!empty($_GET['filter']) && $_GET['filter'] == 'projects') {
            $options['h4_title'] = __('Projects Discussions', 'bp_gtm');
            $options['th_title'] = __('Project Name', 'bp_gtm');
            $options['type'] = 'projects';
            $options['styles_t_cur'] = '';
            $options['styles_p_cur'] = 'grey';
        }
        $options['posts'] = BP_GTM_Discussion::get_list(bp_get_current_group_id(), $options['type'], $limit);

        return $options;
    }

    function bp_gtm_get_group_project_respossibles($project) {
        $users_logins = explode(' ', $project->resp_id);
        $arg['width'] = '25';
        $arg['height'] = '25';

        if (count($users_logins) > 1) {
            foreach ($users_logins as $users_login) {
                $arg['item_id'] = bp_core_get_userid($users_login);
                ?>
                <div class="poster-name">
                    <a href="<?php echo bp_core_get_userlink($arg['item_id'], false, true); ?>" title="<?php echo bp_core_get_userlink($arg['item_id'], true, false); ?>">
                        <?php echo bp_core_fetch_avatar($arg); ?>
                    </a>
                </div>
                <?php
            }
        } else {
            $arg['item_id'] = bp_core_get_userid($users_logins['0']);
            ?>
            <div class="poster-name"><?php
        echo bp_core_fetch_avatar($arg);
        echo bp_core_get_userlink($arg['item_id']);
            ?></div>
            <?php
        }
    }

    function bp_gtm_get_done_undone_links($project_id, $project_group_id, $gtm_link) {
        $count = BP_GTM_Projects::get_count_tasks($project_id, $project_group_id);
        echo '<a href="' . $gtm_link . 'tasks?project=' . $project_id . '" 
             title="' . __('Pending tasks', 'bp_gtm') . '">' . $count['undone'] . '</a> 
            / <a href="' . $gtm_link . 'tasks?project=' . $project_id . '&filter=done" 
             title="' . __('Completed tasks', 'bp_gtm') . '">' . $count['done'] . '</a>';
    }

    function bp_gtm_delete_link($project_id, $type = 'project') {
        if (bp_gtm_check_access($type . '_delete')) {
            ?>
            <a class="delete_me" id="<?php echo $project_id; ?>" href="#" title="<?php _e('Delete this project and all corresponding tasks', 'bp_gtm'); ?>"><img height="16" width="16" src="<?php echo plugins_url("_inc/images/delete.png", __FILE__) ?>" alt="<?php _e('Delete', 'bp_gtm') ?>" /></a>&nbsp;
            <?php
        }
    }

    function bp_gtm_edit_link($project_id, $gtm_link, $type) {
        $type_title = substr($type, 0, -1);
        if (bp_gtm_check_access($type_title . '_edit')) {
            ?>
            <a href="<?php echo $gtm_link . $type . '/edit/' . $project_id ?>" title="<?php printf(__('Edit this %s', 'bp_gtm'), $type_title); ?>"><img height="16" width="16" src="<?php echo plugins_url("_inc/images/edit.png", __FILE__) ?>" alt="<?php _e('Edit', 'bp_gtm') ?>" /></a>&nbsp;

            <?php
        }
    }

    function bp_gtm_view_link($project, $gtm_link, $type) {
        if (bp_gtm_check_access($type . '_view')) {
            ?>
            <a class="topic-title" href="<?php echo $gtm_link . $type . 's/view/' . $project->id ?>" title="<?php echo $project->desc ?>">
                <?php echo $project->name; ?>
            </a>
            <?php
        }
    }

    function bp_gtm_delete_task_link($task_id) {
        if (bp_gtm_check_access('task_delete')) {
            ?>
            <a class="delete_me" id="<?php echo $task_id; ?>" href="#" title="<?php _e('Delete this task', 'bp_gtm'); ?>"><img height="16" width="16" src="<?php echo plugins_url("_inc/images/delete.png", __FILE__) ?>" alt="<?php _e('Delete', 'bp_gtm') ?>" /></a>&nbsp;

            <?php
        }
    }

    function bp_gtm_create_button($gtm_link, $type) {
        if (bp_gtm_check_access($type . '_create')) {
            ?>
            <div id="create-button">
                <a class="button" href="<?php echo $gtm_link . $type ?>s/create"><?php _e('Create new', 'bp_gtm') ?></a>
            </div>
            <?php
        }
    }

    function bp_get_create_subtask_link($task_id, $gtm_link) {
        if (bp_gtm_check_access('task_create')) {
            ?>
            <a href="<?php echo $gtm_link ?>tasks/create?<?php echo $task_id; ?>" title="<?php _e('Create SubTask', 'bp_gtm'); ?>">
                <img src="<?php echo plugins_url('_inc/images/add.png', __FILE__); ?>" alt="<?php _e('Create SubTask', 'bp_gtm'); ?>" height='16' width='16' />
            </a>
            <?php
        }
    }

    function bp_gtm_poster_meta($project, $type = 'project') {
        global $bp;
        $avatar['item_id'] = $project->creator_id;
        $avatar['width'] = '40';
        $avatar['height'] = '40';
        if ($project->done == 1)
            $project_done = '&rarr; <span class="done">' . __('completed', 'bp_gtm') . '</span>';
        $action = !empty($_GET['action']) ? $type . '_' . $_GET['action'] : '';

        do_action('bp_gtm_delete_view_notification', $bp->loggedin_user->id, $project->id, $action);
        echo bp_core_fetch_avatar($avatar);
        if ($type == 'project') {
            echo sprintf(__('%s created project on %s:', 'bp_gtm'), bp_core_get_userlink($project->creator_id), bp_gtm_get_format_date($project->date_created));
        } else {
            echo sprintf(__('%s created task on %s:', 'bp_gtm'), bp_core_get_userlink($project->creator_id), bp_gtm_get_format_date($project->date_created));
        }
    }

    function bp_gtm_view_list_button($gtm_link, $type = 'projects') {
        if ($type == 'projects'):
            ?>
            <a class="button back-task" href="<?php echo $gtm_link . $type; ?>"><?php _e('&larr; Back To Projects List', 'bp_gtm') ?></a>
        <?php else:
            ?>
            <a class="button back-task" href="<?php echo $gtm_link . $type; ?>"><?php _e('&larr; Back To Tasks List', 'bp_gtm') ?></a>
        <?php
        endif;
    }

    function bp_gtm_edit_button($project_id, $gtm_link, $type) {
        if (bp_gtm_check_access($type . '_edit')) { 
            ?>

            <a class="button" href="<?php echo $gtm_link . $type . 's/edit/' . $project_id; ?>" title="<?php printf(__('Edit this %s', 'bp_gtm'), $type); ?>"><?php _e('Edit', 'bp_gtm'); ?></a>
            <?php
        }
    }

    function bp_gtm_pending_complited_tasks($project_id, $gtm_link) {
        if (bp_gtm_check_access('task_view')) {
            echo '<a class="button" href="' . $gtm_link . 'tasks?project=' . $project_id . '">' . __('Pending tasks', 'bp_gtm') . '</a>';
            echo '<a class="button" href="' . $gtm_link . 'tasks?filter=done&project=' . $project_id . '">' . __('Completed tasks', 'bp_gtm') . '</a>';
        }
    }

    function bp_gtm_create_subtask_button($task_id, $gtm_link) {
        if (bp_gtm_check_access('task_create')) {
            ?>
            <a class="button" href="<?php echo $gtm_link . 'tasks/create?' . $task_id; ?>" title="<?php _e('Create new subtask for this task', 'bp_gtm'); ?>"><?php _e('New SubTask', 'bp_gtm') ?></a>
            <?php
        }
    }

    function bp_gtm_view_subtask_button($task_id) {
        ?>
        <a class="button show_subtasks" id="<?php echo $task_id ?>" href="#subtasks" title="<?php _e('Show SubTasks for this task', 'bp_gtm'); ?>"><?php _e('Show SubTasks', 'bp_gtm') ?></a>
        <?php
    }

    function bp_gtm_view_parent_task_button($task_parent_id, $gtm_link) {
        if ($task_parent_id) {
            ?>
            <a class="button" href="<?php echo $gtm_link . 'tasks/view/' . $task_parent_id; ?>" title="<?php
        _e('Go to a parent task page: ', 'bp_gtm');
        echo bp_gtm_get_el_name_by_id($task['0']->parent_id, 'task');
            ?>"><?php _e('Parent Task', 'bp_gtm') ?></a>
               <?php
           }
       }

       function bp_gtm_complite_tast_button($task_id, $task_done = 0) {
           if (bp_gtm_check_access('task_done')) {
               if (!$task_done) {
                   ?>
                <a class="button complete_task" id="<?php echo $task_id ?>" href="#" title="<?php _e('Mark as completed', 'bp_gtm'); ?>"><?php _e('Complete', 'bp_gtm') ?></a>
                <?php
            }
        }
    }

    function bp_gtm_delete_tast_button($task_id) {
        if (bp_gtm_check_access('task_delete')) {
            ?>
            <a class="button delete_task" id="<?php echo $task_id ?>" href="#" title="<?php _e('Delete this task', 'bp_gtm'); ?>"><?php _e('Delete', 'bp_gtm') ?></a>
            <?php
        }
    }

    function bp_gtm_poster_discussion_meta($post_author_id) {
        $arg['item_id'] = $post_author_id;
        $arg['width'] = '25';
        $arg['height'] = '25';
        ?>
        <div class="poster-name"><?php
    echo bp_core_fetch_avatar($arg);
    echo bp_core_get_userlink($arg['item_id']);
        ?></div>
        <?php
    }

    function bp_gtm_get_format_date($date) {
        $option = get_option('date_format');
        return date_i18n($option, strtotime($date));
    }

    function bp_gtm_format_date($date) {
        echo bp_gtm_get_format_date($date);
    }

    function bp_gtm_view_disscuss_link($id, $gtm_link, $type) {
        ?>
        <a class="topic-title" href="<?php echo $gtm_link . $type . '/view/' . $id ?>" title="<?php _e('Permalink', 'bp_gtm') ?>">
            <?php echo bp_gtm_get_el_name_by_id($id, $type); ?>
        </a>
    <?php
    }

    function bp_gtm_get_project_cats($project_id) {
        $tags = '';
        $terms = BP_GTM_Taxon::get_terms_4project(bp_get_current_group_id(), $project_id, 'tag');
        if (!empty($terms)) {
                foreach ($terms as $tag) {
                        if($tag['used'] == '1'){
                        $tags .= '|'.stripslashes($tag['name']);
                    }
                }
        }
        return $tags;
    }
    function bp_gtm_get_task_cats($task_id){
        $tags = '';
        $terms = BP_GTM_Taxon::get_terms_4task(bp_get_current_group_id(), $task_id, 'tag');
        if (!empty($terms)) {
                foreach ($terms as $tag) {
                        if($tag['used'] == '1'){
                        $tags .= '|'.stripslashes($tag['name']);
                    }
                }
        }
        return $tags;
    }
    /**
     * Get link with description with description in title
     * @param object $parent_task 
     */
   function  bp_gtm_get_parent_task_link($parent_task, $gtm_link){
//       var_dump($parent_task);
//       var_dump($parent_task[0]);
       if (bp_gtm_check_access('task_view')) {
            
            return '<a class="topic-title parent-task-link" href="'. $gtm_link . 'tasks/view/' . $parent_task[0]->id .'" 
                        title="'.trim(strip_tags($parent_task[0]->desc)).'">'. $parent_task[0]->name.'</a>';
            
        }
        return false;
   }
?>
