<table class="form-table"><tbody>
<tr><th scope="row"><?php _e("Upgrade", "gd-star-rating"); ?></th>
    <td>
        <form method="post">
        <?php _e("Try to upgrade plugin tables. This is performed automatically after each plugin upgrade, but you can use this tool also.", "gd-star-rating"); ?><br />
        <input type="submit" class="inputbutton" value="<?php _e("Upgrade", "gd-star-rating"); ?>" name="gdsr_upgrade_tool" id="gdsr_upgrade_tool" />
        <div class="gdsr-table-split"></div>
        <?php _e("Last cleanup was executed on", "gd-star-rating"); ?>: <strong><?php echo $gdsr_options['database_upgrade']; ?></strong>
        </form>
    </td>
</tr>
<tr><th scope="row"><?php _e("Cleanup", "gd-star-rating"); ?></th>
    <td>
        <form method="post">
        <input type="checkbox" name="gdsr_tools_clean_invalid_log" id="gdsr_tools_clean_invalid_log" checked="checked" /><label style="margin-left: 5px;" for="gdsr_tools_clean_invalid_log"><?php _e("Remove all invalid votes from votes log.", "gd-star-rating"); ?></label>
        <br />
        <input type="checkbox" name="gdsr_tools_clean_invalid_trend" id="gdsr_tools_clean_invalid_trend" checked="checked" /><label style="margin-left: 5px;" for="gdsr_tools_clean_invalid_trend"><?php _e("Remove all invalid votes from trends log.", "gd-star-rating"); ?></label>
        <br />
        <input type="checkbox" name="gdsr_tools_clean_old_posts" id="gdsr_tools_clean_old_posts" checked="checked" /><label style="margin-left: 5px;" for="gdsr_tools_clean_old_posts"><?php _e("Remove data for old and deleted posts and comments.", "gd-star-rating"); ?></label>
        <br />
        <input type="checkbox" name="gdsr_tools_clean_multis" id="gdsr_tools_clean_multis" checked="checked" /><label style="margin-left: 5px;" for="gdsr_tools_clean_multis"><?php _e("Remove data for old and deleted multi ratings.", "gd-star-rating"); ?></label>
        <br />
        <input type="submit" class="inputbutton" value="<?php _e("Clean", "gd-star-rating"); ?>" name="gdsr_cleanup_tool" id="gdsr_cleanup_tool" />
        <div class="gdsr-table-split"></div>
        <?php _e("Last cleanup was executed on", "gd-star-rating"); ?>: <strong><?php echo $gdsr_options['database_cleanup']; ?></strong><br />
        <?php _e("Cleanup summary", "gd-star-rating"); ?>: <strong><?php echo $gdsr_options['database_cleanup_msg']; ?></strong>
        </form>
    </td>
</tr>
<tr><th scope="row"><?php _e("Reset", "gd-star-rating"); ?></th>
    <td>
        <form method="post">
        <?php _e("Remove all saved rating data including logs and all values regardless of the rating type. All rating rules will remain intact.", "gd-star-rating"); ?><br />
        <input type="submit" class="inputbutton" value="<?php _e("Delete", "gd-star-rating"); ?>" name="gdsr_reset_db_tool" id="gdsr_reset_db_tool" />
        <div class="gdsr-table-split"></div>
        <?php _e("Be very careful with this option, operation is not reversible. Backup your rating tables data before you proceed.", "gd-star-rating"); ?>
        </form>
    </td>
</tr>
</tbody></table>
