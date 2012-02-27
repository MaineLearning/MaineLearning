<table class="form-table"><tbody>
<tr><th scope="row"><?php _e("Remove Settings", "gd-star-rating"); ?></th>
    <td>
        <form method="post" onsubmit="return areYouSure()">
        <?php _e("This will remove all plugin settings and all the saved widgets.", "gd-star-rating"); ?><br />
        <input type="submit" value="<?php _e("Remove Settings", "gd-star-rating"); ?>" name="gdsr_remove_settings" class="inputbutton" />
        <div class="gdsr-table-split"></div>
        <?php _e("This operation is not reversable. Backup your data if you want to be able to restore it later.", "gd-star-rating"); ?>
        </form>
    </td>
</tr>
<tr><th scope="row"><?php _e("Reset Imports", "gd-star-rating"); ?></th>
    <td>
        <form method="post" onsubmit="return areYouSure()">
        <?php _e("This will reset all import flags for import data modules and will allow you to import again allready imported data.", "gd-star-rating"); ?><br />
        <input type="submit" value="<?php _e("Reset Imports", "gd-star-rating"); ?>" name="gdsr_reset_imports" class="inputbutton" />
        </form>
    </td>
</tr>
</tbody></table>
