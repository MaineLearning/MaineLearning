<?php
$options = bp_gtm_get_personal_profile_settings();
?>

<h4><?php echo $options['h4_title']; ?></h4>
<div class="personal">
    <div class="pagination no-ajax">
        <div id="post-count" class="pag-count">
            <?php _e('List of projects you are responsible for.', 'bp_gtm'); ?>
        </div>
        <div id="projects-filter">
            <?php _e('Filter: ', 'bp_gtm'); ?>
            <a href="projects?filter=alpha" class="<?php echo $options['styles_a_cur'] ?>"><?php _e('By A/Z', 'bp_gtm'); ?></a> |
            <a href="projects?filter=deadline" class="<?php echo $options['styles_d_cur'] ?>"><?php _e('By deadline', 'bp_gtm'); ?></a>
        </div>
    </div>

<?php if (count($options['projects']) > 0) { ?>
    <table class="forum zebra projects-list">
        <thead>
            <tr>
                <th id="th-title"><?php _e('Project Name', 'bp_gtm') ?></th>
                <th id="th-poster"><?php _e('Group Name', 'bp_gtm') ?></th>
                <th id="th-poster" class="center"><?php _e('Deadline', 'bp_gtm') ?></th>
                <?php do_action('bp_gtm_personal_projects_extra_cell_head') ?>
            </tr>
        </thead>
        <tbody id="discuss-list">
            <?php
            bp_gtm_get_personal_project_list($options['projects']);
            do_action('bp_gtm_personal_projects_extra_last_row');
            ?>
        </tbody>
    </table>
    <?php
} else {
    echo '<div id="message" class="info"><p>' . __('You are not responsible for any projects on this site.', 'bp_gtm') . '</p></div>';
}
?>