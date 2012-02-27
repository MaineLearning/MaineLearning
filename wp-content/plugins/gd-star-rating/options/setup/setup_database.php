<table class="form-table"><tbody>
<tr><th scope="row"><?php _e("Reinstall", "gd-star-rating"); ?></th>
    <td>
        <form method="post" onsubmit="return areYouSure()">
        <?php _e("All database tables will be deleted, and installed again.", "gd-star-rating"); ?><br />
        <input type="submit" value="<?php _e("Reinstall", "gd-star-rating"); ?>" name="gdsr_reinstall" class="inputbutton" />
        <div class="gdsr-table-split"></div>
        <?php _e("This operation is not reversable. Backup your data if you want to be able to restore it later.", "gd-star-rating"); ?>
        </form>
    </td>
</tr>
<tr><th scope="row"><?php _e("Reinstall Multi Ratings", "gd-star-rating"); ?></th>
    <td>
        <form method="post" onsubmit="return areYouSure()">
        <?php _e("Only multi ratings tables will be deleted, and installed again.", "gd-star-rating"); ?><br />
        <input type="submit" value="<?php _e("Reinstall", "gd-star-rating"); ?>" name="gdsr_remultis" class="inputbutton" />
        <div class="gdsr-table-split"></div>
        <?php _e("This operation is not reversable. Backup your data if you want to be able to restore it later.", "gd-star-rating"); ?>
        </form>
    </td>
</tr>
<tr><th scope="row"><?php _e("Reinstall Templates", "gd-star-rating"); ?></th>
    <td>
        <form method="post" onsubmit="return areYouSure()">
        <?php _e("This will remove all saved templates for the plugin. Templates will be reseted to their default values.", "gd-star-rating"); ?><br />
        <input type="submit" value="<?php _e("Reinstall Templates", "gd-star-rating"); ?>" name="gdsr_remove_templates" class="inputbutton" />
        <div class="gdsr-table-split"></div>
        <?php _e("This operation is not reversable. Backup your data if you want to be able to restore it later.", "gd-star-rating"); ?>
        </form>
    </td>
</tr>
</tbody></table>
