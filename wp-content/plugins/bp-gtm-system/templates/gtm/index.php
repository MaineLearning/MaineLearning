<?php
global $bp;

$bp_gtm = get_option('bp_gtm');
$bp_gtm_group_settings = get_option('bp_gtm_g_' . $bp->groups->current_group->id . '_settings');
if (empty($bp->action_variables[0]))
    $bp->action_variables[0] = 'projects';

$gtm_link = bp_get_group_permalink(). $bp->gtm->slug . '/';
$date_format = get_option('date_format');
?>


<div class="item-list-tabs no-ajax" id="subnav">
    <ul><?php bp_gtm_tabs(); ?></ul>
</div>
<?php do_action('bp_before_gtm_body') ?>

<?php require_once( GTM_DIR.'/bp-gtm-templatetags.php' ) ?>

<form action="<?php bp_gtm_form_action() ?>" id="gtm_form" class="standard-form" method="post" enctype="multipart/form-data">

    <?php
    if (bp_is_gtm_screen('tasks') && empty($bp->action_variables[1]) && bp_gtm_check_access('task_view')) {
        include (dirname(__File__) . '/tasks/tasks_list.php');
    } else if (bp_is_gtm_screen('tasks') && $bp->action_variables[1] == 'create' && bp_gtm_check_access('task_create')) {
        include (dirname(__File__) . '/tasks/task_create.php');
    } else if (bp_is_gtm_screen('tasks') && $bp->action_variables[1] == 'view' && bp_gtm_check_access('task_view')) {
        include (dirname(__File__) . '/tasks/task_view.php');
    } else if (bp_is_gtm_screen('tasks') && $bp->action_variables[1] == 'edit' && bp_gtm_check_access('task_edit')) {
        include (dirname(__File__) . '/tasks/task_edit.php');
    } else if (bp_is_gtm_screen('projects') && empty($bp->action_variables[1]) && bp_gtm_check_access('project_view')) {
        include (dirname(__File__) . '/projects/projects_list.php');
    } else if (bp_is_gtm_screen('projects') && $bp->action_variables[1] == 'create' && bp_gtm_check_access('project_create')) {
        include (dirname(__File__) . '/projects/project_create.php');
    } else if (bp_is_gtm_screen('projects') && $bp->action_variables[1] == 'view' && bp_gtm_check_access('project_view')) {
        include (dirname(__File__) . '/projects/project_view.php');
    } else if (bp_is_gtm_screen('projects') && $bp->action_variables[1] == 'edit' && bp_gtm_check_access('project_edit')) {
        include (dirname(__File__) . '/projects/project_edit.php');
    } else if (bp_is_gtm_screen('terms') && empty($bp->action_variables[1]) && bp_gtm_check_access('taxon_view')) {
        include (dirname(__File__) . '/terms/terms_list.php');
    } else if (bp_is_gtm_screen('terms') && $bp->action_variables[1] == 'view' && bp_gtm_check_access('taxon_view')) {
        include (dirname(__File__) . '/terms/term_view.php');
    } else if (bp_is_gtm_screen('involved') && empty($bp->action_variables[1]) && bp_gtm_check_access('involved_view')) {
        include (dirname(__File__) . '/involved/involved_list.php');
    } else if (bp_is_gtm_screen('discuss') && empty($bp->action_variables[1]) && $bp_gtm_group_settings['discuss'] == 'on' && bp_gtm_check_access('discuss_view')) {
        include (dirname(__File__) . '/discuss.php');
    }else if ( bp_is_gtm_screen('files') && !$bp->action_variables[1] ) {
               include (dirname(__File__).'/files.php');
    } else if (bp_is_gtm_screen('settings') && bp_gtm_check_access('settings_view')) {
        include (dirname(__File__) . '/settings.php');
    } else if (bp_is_gtm_screen('delete') && bp_gtm_check_access('delete_all')) {
        include (dirname(__File__) . '/delete.php');
    } else if (bp_is_gtm_screen('api')) {
        include (dirname(__File__) . '/api.php');
    } else {
        _e('<h4>Error 404</h4>', 'bp_gtm');
        _e('<p>No such page exists in GTM System or you don\'t have enough rights to access it.</p>', 'bp_gtm');
    }
    ?>

</form>
<?php do_action('bp_after_gtm_body') ?>
</div><!-- #item-body -->

<?php do_action('bp_after_gtm_content') ?>

</div>

<?php if (get_option('stylesheet') == 'frisco-for-buddypress'): // FIX FOR MARCUP compatibility - need to fix markup in themes?>
    <?php get_sidebar(); ?>
    </div>
<?php else : ?>
    </div>
    <?php get_sidebar(); ?>
<?php endif; ?>

<?php get_footer(); ?>
