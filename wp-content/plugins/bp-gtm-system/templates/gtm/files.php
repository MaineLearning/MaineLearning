<?php 
global $bp;
$files = bp_gtm_get_all_files();
$gtm_link = bp_get_group_permalink() . $bp->gtm->slug;
?>

<h4><?php _e('List Of Files', 'bp_gtm'); ?></h4>
<?php 
if(!empty($files)){ ?>
    <table class="forum files">
        <thead>
            <tr>
                <th id="th-title"><?php _e('File Name', 'bp_gtm') ?></th>
                <th><?php _e('Uploaded by', 'bp_gtm') ?></th>
                <th><?php _e('Type', 'bp_gtm')?></th>
                <th><?php _e('Date Uploaded',  'bp_gtm')?></th>
                <th id="th-freshness"><?php _e('Actions', 'bp_gtm') ?></th>
                <?php do_action('bp_directory_files_extra_cell_head') ?>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($files as $file) { ?>
                <tr class="" id="<?php echo $file->id; ?>">
                    <td class="td-title">
                        <?php echo bp_gtm_file_link($file->path) ?>
                    </td>
                    <td>
                        <?php echo bp_core_get_userlink($file->owner_id);?>
                    </td>
                    <td>
                        <?php bp_gtm_get_upload_target($file, $gtm_link );?>
                    </td>
                    <td>
                        <?php echo date_i18n(get_option('date_format'), $file->date_uploaded);?>
                    </td>
                    <td id="file" class="td-freshness">
                            <?php if (bp_gtm_check_access('files_delete')) { ?>
                            <a class="delete_me" id="<?php echo $file->id; ?>" href="#"><img height='16' width='16' src="<?php echo GTM_URL ?>_inc/images/delete.png" alt="<?php _e('Delete', 'bp_gtm') ?>" /></a>
                            <?php } ?>
                    </td>

                    <?php do_action('bp_directory_file_extra_cell') ?>
                </tr>

                <?php do_action('bp_directory_file_extra_row');
            }?>

        </tbody>
    </table>
<?php 
}else{
    echo '<div id="message" class="info"><p>'.__('There are no files to display.','bp_gtm').'</p></div>';
}
?>
<div class="clear-both">