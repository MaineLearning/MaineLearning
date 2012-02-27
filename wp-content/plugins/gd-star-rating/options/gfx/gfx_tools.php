<table class="form-table"><tbody>
<tr><th scope="row"><?php _e("Rescan graphics", "gd-star-rating"); ?></th>
    <td>
        <?php _e("Rescan graphics folders for new stars and trends images.", "gd-star-rating"); ?><br />
        <input type="submit" class="inputbutton" value="<?php _e("Rescan", "gd-star-rating"); ?>" name="gdsr_preview_scan" id="gdsr_preview_scan" />
        <div class="gdsr-table-split"></div>
        <?php _e("Last scan was executed on.", "gd-star-rating"); ?>: <strong><?php echo $gdsr_gfx->last_scan; ?></strong>
    </td>
</tr>
</tbody></table>
