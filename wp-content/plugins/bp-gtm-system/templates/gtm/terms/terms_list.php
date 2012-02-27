<?php
$tags = BP_GTM_Taxon::get_terms_in_group($bp->groups->current_group->id, 'tag');
$cats = BP_GTM_Taxon::get_terms_in_group($bp->groups->current_group->id, 'cat');
?>
<h4><?php _e('Tags and Categories', 'bp_gtm') ?> 
    <?php if (bp_gtm_check_access('taxon_create')) { ?>
        &rarr; <a class="button" id="open" href="#"><?php _e('Create new', 'bp_gtm') ?></a>
    </h4>
    <div id="toggler" class="add-new12">
        <div id="box">
            <p>
                <label for="term_name"><?php _e('New term name', 'bp_gtm') ?></label>
                <input name="term_name" type="text" value="" id="term_name" />
            </p>
            <p>
                <label for="term_taxon"><?php _e('What\'s this?', 'bp_gtm') ?></label>
                <input name="term_taxon" type="radio" value="tag" /> <?php _e('Tag', 'bp_gtm') ?><br>
                <input name="term_taxon" type="radio" value="cat" /> <?php _e('Category', 'bp_gtm') ?>
                <?php do_action('bp_gtm_term_taxon_create'); ?>
                <span class="submit"><input name="saveNewTerm" type="submit" value="<?php _e('Create new', 'bp_gtm') ?>" /></span>
                <?php wp_nonce_field('bp_gtm_new_term') ?>
            </p>
        </div>
    </div>
    <?php
} else {
    echo '</h4>';
}
?>

<div class="pagination">
    <div id="post-count" class="pag-count">
        <p><?php _e('All tags and categories for this group are displayed here', 'bp_gtm'); ?></p>
    </div>

    <div id="terms-filter">
        <?php _e('Filter: ', 'bp_gtm'); ?>
        <a href="<?php echo $gtm_link . $bp->action_variables[0] ?>"><?php _e('Standart', 'bp_gtm'); ?></a> |
        <a href="<?php echo $gtm_link . $bp->action_variables[0] . '?alphabetical' ?>"><?php _e('Alphabetical', 'bp_gtm'); ?></a> |
        <a href="<?php echo $gtm_link . $bp->action_variables[0] . '?done' ?>"><?php _e('Done', 'bp_gtm'); ?></a>
    </div>
</div>

<?php do_action('bp_before_gtm_terms_list'); ?>


<?php if (!empty($tags)): ?>
    <table class="forum zebra table-left">
        <thead>
            <tr>
                <th id="th-title"><?php _e('Tag Name', 'bp_gtm') ?></th>
                <th id="th-postcount"><?php _e('Count', 'bp_gtm') ?></th>
                <th id="th-freshness"><?php _e('Actions', 'bp_gtm') ?></th>
                <?php do_action('bp_directory_tags_extra_cell_head') ?>
            </tr>
        </thead>
        <tbody>
            <?php bp_gtm_terms_loop($tags, 'tags') ?>
        </tbody>
    </table>
<?php else: ?>
    <div id="message" class="info-left"><p><?php _e('There are no tags to display.', 'bp_gtm') ?></p></div>
<?php endif; ?>


<?php if (count($cats)): ?>
    <table class="forum zebra table-right">
        <thead>
            <tr>
                <th id="th-title"><?php _e('Category Name', 'bp_gtm') ?></th>
                <th id="th-postcount"><?php _e('Count', 'bp_gtm') ?></th>
                <th id="th-freshness"><?php _e('Actions', 'bp_gtm') ?></th>
                <?php do_action('bp_directory_cats_extra_cell_head') ?>
            </tr>
        </thead>
        <tbody>
            <?php bp_gtm_terms_loop($cats, 'cats'); ?>
        </tbody>
    </table>
<?php else: ?>
    <div id="message" class="info-right"><p><?php _e('There are no categories to display.', 'bp_gtm') ?></p></div>
<?php endif; ?>
<div class="clear-both"></div>
<?php do_action('bp_after_gtm_terms_list'); ?>