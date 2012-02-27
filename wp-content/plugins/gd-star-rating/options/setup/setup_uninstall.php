<table class="form-table"><tbody>
<tr><th scope="row"><?php _e("Uninstall", "gd-star-rating"); ?></th>
    <td>
        <form method="post" onsubmit="return areYouSure()">
        <?php _e("After removal, plugin will be disabled. All data recorded by the plugin will be lost", "gd-star-rating"); ?><br />
        <input type="submit" value="<?php _e("UNINSTALL", "gd-star-rating"); ?>" name="gdsr_full_uninstall" class="inputbutton inputred" />
        <div class="gdsr-table-split"></div>
        <?php _e("This operation is not reversable. Backup your data if you want to be able to restore it later.", "gd-star-rating"); ?>
        </form>
    </td>
</tr>
</tbody></table>
