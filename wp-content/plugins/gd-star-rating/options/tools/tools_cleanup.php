<table class="form-table"><tbody>
<tr><th scope="row"><?php _e("Cache", "gd-star-rating"); ?></th>
    <td>
        <form method="post">
        <?php _e("Delete all cached files from folder", "gd-star-rating"); ?> <strong>'/wp-content/gd-star-rating/cache/'</strong>:<br />
        <input type="submit" class="inputbutton" value="<?php _e("Clean", "gd-star-rating"); ?>" name="gdsr_cache_clean" id="gdsr_cache_clean" />
        <div class="gdsr-table-split"></div>
        <?php _e("Last cleanup was executed on", "gd-star-rating"); ?>: <strong><?php echo $gdsr_options["cache_cleanup_last"]; ?></strong>
        </form>
        <?php _e("Total files cached", "gd-star-rating"); ?>: <strong><?php echo gdFunctionsGDSR::get_folder_files_count( substr(STARRATING_CACHE_PATH, 0, strlen(STARRATING_CACHE_PATH) - 1) ); ?> files</strong>
        <br />
        <?php _e("Total size of cached files", "gd-star-rating"); ?>: <strong><?php echo gdFunctionsGDSR::get_folder_size( substr(STARRATING_CACHE_PATH, 0, strlen(STARRATING_CACHE_PATH) - 1) ); ?> bytes</strong>
    </td>
</tr>
<tr><th scope="row"><?php _e("Debug", "gd-star-rating"); ?></th>
    <td>
        <form method="post">
        <?php _e("Empty the debug file", "gd-star-rating"); ?> <strong>'<?php echo STARRATING_LOG_PATH; ?>'</strong>:<br />
        <input type="submit" class="inputbutton" value="<?php _e("Clean", "gd-star-rating"); ?>" name="gdsr_debug_clean" id="gdsr_debug_clean" />
        <div class="gdsr-table-split"></div>
        </form>
        <?php _e("Total size of debug file", "gd-star-rating"); ?>: <strong><?php echo filesize(STARRATING_LOG_PATH); ?> bytes</strong>
    </td>
</tr>
</tbody></table>
