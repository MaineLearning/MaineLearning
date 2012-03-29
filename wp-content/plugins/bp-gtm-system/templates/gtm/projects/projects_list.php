<?php
$options = bp_gtm_project_list_settings();

do_action('bp_gtm_delete_view_notification', $bp->loggedin_user->id, $ids = 'none', $option['action']);
?>
<?php
if (bp_gtm_check_access('project_create')) {
    bp_gtm_create_button($gtm_link, 'project');
}
?>
<h4><?php echo $options['page_h4']; ?></h4>


<div class="group-navi">
    <div class="pagination no-ajax">
        <div id="post-count" class="pag-count">
            <p><?php _e('List of projects in this group. Use links in the right to filter them.', 'bp_gtm'); ?></p>
        </div>

        <div id="projects-filter">
                <?php _e('Filter: ', 'bp_gtm'); ?>
            <a <?php if (!empty($options['alpha_style']))
                    echo $options['alpha_style']; ?> href="<?php echo $gtm_link . 'projects?filter=alpha'; ?>"><?php _e('By A/Z', 'bp_gtm'); ?></a> |
            <a <?php if (!empty($options['deadline_style']))
                    echo $options['deadline_style']; ?> href="<?php echo $gtm_link . 'projects?filter=deadline'; ?>"><?php _e('By deadline', 'bp_gtm'); ?></a> |
            <a <?php if (!empty($options['done_style']))
                    echo $options['done_style']; ?> href="<?php echo $gtm_link . 'projects?filter=done'; ?>"><?php _e('Completed', 'bp_gtm'); ?></a>
        </div>
    </div>
</div>
<?php do_action('bp_before_gtm_projects_list');

if (count($options['projects'])) { ?>

    <table class="forum zebra projects-list">
        <thead>
            <tr>
                <th id="th-title"><?php _e('Project Title', 'bp_gtm') ?></th>
                <th id="th-poster"><?php _e('Responsible', 'bp_gtm') ?></th>
                <th id="th-group"><?php _e('Deadline', 'bp_gtm') ?></th>
                <th id="th-postcount"><?php _e('Tasks', 'bp_gtm') ?></th>
                <th id="th-freshness"><?php _e('Actions', 'bp_gtm') ?></th>

    <?php do_action('bp_directory_projects_extra_cell_head') ?>

            </tr>
        </thead>

        <tbody id="projects-list" >
                    <?php foreach ($options['projects'] as $project) { ?>
                <tr id="<?php echo $project->id; ?>" class="<?php bp_gtm_project_check_date($project->id, $project->deadline); ?>">
                    <td class="td-title">
                        <?php bp_gtm_view_link($project, $gtm_link, 'project'); ?>
                    </td>
                    <td class="td-poster">
                        <?php
                        bp_gtm_get_group_project_respossibles($project);
                        ?>
                    </td>
                    <td class="td-group">
                        <div class="object-name"><?php bp_gtm_format_date($project->deadline) ?></div>
                    </td>
                    <td class="td-postcount">
                        <?php
                        bp_gtm_get_done_undone_links($project->id, $project->group_id, $gtm_link);
                        ?>
                    </td>
                    <td id="projects" class="td-freshness">
                        <?php
                        bp_gtm_edit_link($project->id, $gtm_link, 'projects');
                        bp_gtm_delete_link($project->id);
                        bp_gtm_done_link($filter, $project->id, $el_type = 'project');
                        ?>

                    </td>

                <?php do_action('bp_directory_projects_extra_cell') ?>
                </tr>
        <?php do_action('bp_directory_projects_extra_row');
    } ?>
        </tbody>
    </table>

    <?php
} else {
    echo '<div id="message" class="info"><p>' . __('There are no projects to display.', 'bp_gtm') . '</p></div>';
}
?>
