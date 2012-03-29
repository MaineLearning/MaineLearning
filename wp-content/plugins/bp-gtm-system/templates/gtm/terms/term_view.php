<h4><?php printf(__('View tasks and projects for <i>"%s"</i>', 'bp_gtm'), BP_GTM_Taxon::get_term_name_by_id($bp->action_variables[2])); ?></h4>

<?php do_action('bp_before_gtm_term_view'); ?>

<?php
$elements = BP_GTM_Taxon::get_elements_4term($bp->action_variables[2]);
$gtm_link = bp_get_group_permalink(). $bp->gtm->slug . '/';
//var_dump($gtm_link); die;
?>
<p>&nbsp;</p>
<?php if (count($elements['tasks']) && !$elements['tasks']['0']): ?>
    <table class="forum zebra table-left">
        <thead>
            <tr>
                <th id="th-title"><?php _e('Task Name', 'bp_gtm') ?></th>
                <th id="th-postcount">
                    <?php _e('Done?', 'bp_gtm'); ?>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php bp_gtm_terms_nodes($elements['tasks'], 'tasks', $gtm_link) ?>
        </tbody>
    </table>
    <?php else : ?>
    <div id="message" class="info-left"><p><?php _e('There are no tasks to display.', 'bp_gtm') ?></p></div>
<?php endif; ?>

<?php if (count($elements['projects']) && empty($elements['projects']['0'])) : ?>
    <table class="forum zebra table-right">
        <thead>
            <tr>
                <th id="th-title"><?php _e('Project Name', 'bp_gtm') ?></th>
                <th id="th-postcount"><?php _e('Done?', 'bp_gtm') ?></th></tr>
        </thead>

        <tbody>
            <?php bp_gtm_terms_nodes($elements['projects'], 'projects', $gtm_link) ?>
        </tbody>
    </table>
<?php else : ?>
    <div id="message" class="info-right"><p><?php _e('There are no projects to display.', 'bp_gtm') ?></p></div>
<?php endif; ?>
<div class="clear-both"></div>
<?php do_action('bp_after_gtm_term_view'); ?>
