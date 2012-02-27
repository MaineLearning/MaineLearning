<?php

class BP_GTM_Personal {

    function get_tasks($user_id, $filter = 'deadline', $limit = false, $inID = false) {
        global $bp, $wpdb;

        $with_limit = '';
        if (count($limit) == 2) {
            $with_limit = ' LIMIT ' . $limit['miss'] . ' , ' . $limit['per_page'];
        } else {
            $with_limit = ' LIMIT 0 , 10';
        }
        $done = 0;
        $order = 'ORDER BY `deadline` ASC';
        $in_project = '';
        $in_group = '';
        switch ($filter) {
            case 'deadline':
                $order = 'ORDER BY `deadline` ASC';
                break;
            case 'alpha':
                $order = 'ORDER BY `name` ASC';
                break;
            case 'done':
                $done = 1;
                $order = 'ORDER BY `deadline` DESC';
                break;
            case 'project':
                $in_project = 'AND `project_id` = ' . $inID;
                break;
            case 'group':
                $in_group = 'AND `group_id` = ' . $inID;
                break;
            case 'without':
                $in_project = 'AND `project_id` = 0';
                break;
        }

        $sql = "SELECT `id`, `name`, `project_id`, `group_id`, `date_created`, `deadline` FROM {$bp->gtm->table_tasks}
                    WHERE `done` = {$done}
                        AND `id` IN (
                            SELECT `task_id` AS `id` FROM {$bp->gtm->table_resps}
                            WHERE `resp_id` = {$user_id}
                            {$in_project}
                            {$in_group}
                        )
                {$order}
                {$with_limit}";

        return $wpdb->get_results($wpdb->prepare($sql));
    }

    function get_projects($user_id, $filter) {
        global $bp, $wpdb;

        switch ($filter) {
            case 'alpha':
                $filtered = 'ORDER BY `name` ASC';
                break;
            case 'deadline':
                $filtered = 'ORDER BY `deadline` ASC';
                break;
            default:
                $filtered = 'ORDER BY `name` ASC';
                break;
        }

        $projects = $wpdb->get_results($wpdb->prepare("
         SELECT `id`, `name`, `group_id`, `deadline` FROM {$bp->gtm->table_projects}
         WHERE `done` = 0
            AND `id` IN (
               SELECT `project_id` FROM {$bp->gtm->table_resps}
               WHERE `resp_id` = {$user_id}
            ) 
         {$filtered}
      "));

        return $projects;
    }

    function get_groups($user_id, $filter) {
        global $bp, $wpdb;

        if ($filter == 'alpha') {
            $filtered = 'ORDER BY `name` ASC';
        }

        $sql = "
         SELECT `id`, `name` FROM {$bp->groups->table_name}
         WHERE `id` IN (
            SELECT `group_id` AS `id` FROM {$bp->gtm->table_resps}
            WHERE `resp_id` = {$user_id}
               AND `task_id` IN (
                  SELECT `id` AS `task_id` FROM {$bp->gtm->table_tasks}
                  WHERE `done` = 0
               )
               AND `project_id` IN (
                  SELECT `id` AS `project_id` FROM {$bp->gtm->table_projects}
                  WHERE `done` = 0
               )
         )
         {$filtered}
      ";

        $groups = $wpdb->get_results($wpdb->prepare($sql));

        return $groups;
    }

    function get_count($filter) {
        global $bp, $wpdb;

        // $navi_filter['id']   = $_GET['project'] | $_GET['group'] | 0
        // $navi_filter['done'] = 0|1
        // $navi_filter['user'] = $user_id
        // $navi_filter['type'] = project|group|done|alpha|deadline
        if (!empty($filter['not_navi']) && $filter['not_navi'] == 1) {

            $sql_1 = "
            SELECT COUNT(DISTINCT `id`) FROM {$bp->gtm->table_tasks}
            WHERE `done` = 0
               AND `id` IN (
                  SELECT `task_id` AS `id` FROM {$bp->gtm->table_resps}
                  WHERE `resp_id` = {$filter['user']})";
//echo $sql_1;
            $sql_2 = "
            SELECT COUNT(DISTINCT `id`) FROM {$bp->gtm->table_projects}
            WHERE `done` = 0
               AND `id` IN (
                  SELECT `project_id` AS `id` FROM {$bp->gtm->table_resps}
                  WHERE `resp_id` = {$filter['user']}
                  AND `task_id` = 0 )";

            $count['tasks'] = $wpdb->get_var($wpdb->prepare($sql_1));
            $count['projects'] = $wpdb->get_var($wpdb->prepare($sql_2));
            $count['all'] = ($count['tasks'] + $count['projects']);
        } else {
            $in_project = $in_group = '';
            $done = $filter['done'];
            $user_id = $filter['user'];
            if ($filter['type'] == 'group') {
                $in_group = ' AND `group_id` = ' . $filter['id'];
            } elseif ($filter['type'] == 'project') {
                $in_project = ' AND `project_id` = ' . $filter['id'];
            } elseif ($filter['type'] == 'without') {
                $in_project = ' AND `project_id` = 0';
            }

            $sql = "
            SELECT COUNT(DISTINCT `id`) FROM {$bp->gtm->table_tasks}
            WHERE `done` = {$done}
               AND `id` IN (
                  SELECT `task_id` AS `id` FROM {$bp->gtm->table_resps}
                  WHERE `resp_id` = {$user_id}
                     {$in_project}
                     {$in_group}
               )
         ";

            $count = $wpdb->get_var($wpdb->prepare($sql));
        }

        return $count;
    }

}

// Personal Tasks pagination func
function bp_gtm_personal_tasks_navi($filter, $per_page = 20) {
    global $bp, $wpdb;

    $cur = 1;
    $tasks = BP_GTM_Personal::get_count($filter);

    $pages = ceil($tasks / $per_page);

    if ($pages > 1) {
        echo '<p class="navi" id="' . $tasks . '">';
        echo sprintf(__('<span id="cur_tasks">%s - %s</span> out of %s.&nbsp;', 'bp_gtm'), $cur, $per_page, $tasks);
        _e('Pagination:', 'bp_gtm');
        for ($i = 0; $i < $pages; $i++) {
            $c = $i + 1;
            $cur = $cur - 1;
            if ($cur != $i) {
                echo '&nbsp;<a id="' . $i . '-' . $per_page . '" class="task_navi" href="#">' . $c . '</a>';
            } else {
                echo '&nbsp;<a id="' . $i . '-' . $per_page . '" class="task_navi current" href="#">' . $c . '</a>';
            }
        }
        echo '</p>';
    } else {
        echo sprintf(_n('There is only %d task you are responsible for.', '%d tasks you are responsible for. You may use filters.', $tasks, 'bp_gtm'), $tasks);
    }
}

