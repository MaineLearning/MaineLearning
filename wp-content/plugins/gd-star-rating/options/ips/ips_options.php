<table class="form-table"><tbody>
<tr><th scope="row"><?php _e("Ban Single IP's", "gd-star-rating"); ?></th>
    <td>
        <form method="post" action="">
        <input type="hidden" name="action" value="addips" />
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tr>
                <td width="150" valign="top">
                    <strong><?php _e("Add IP addresses", "gd-star-rating"); ?>:</strong><br />
                    (<?php _e("One IP address per line.", "gd-star-rating"); ?>)
                </td>
                <td><textarea name="gdsr_ip_single_new" id="gdsr_ip_single_new" rows="4" cols="20"></textarea></td>
            </tr>
        </table>
        <div class="gdsr-table-split"></div>
        <input type="submit" class="inputbutton" value="<?php _e("Add", "gd-star-rating"); ?>" name="gdsr_ips_addips" id="gdsr_ips_addips" />
        </form>
    </td>
</tr>
<tr><th scope="row"><?php _e("Ban IP Range", "gd-star-rating"); ?></th>
    <td>
        <form method="post" action="">
        <input type="hidden" name="action" value="baniprange" />
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tr>
                <td width="150"><strong><?php _e("From", "gd-star-rating"); ?>:</strong></td>
                <td width="140"></td>
                <td>.<input style="width: 30px;" maxlength="3" name="gdsr_ip_range_from_4" type="text"></td>
            </tr>
            <tr>
                <td width="150"></td>
                <td width="140"><input style="width: 30px;" maxlength="3" name="gdsr_ip_range_1" type="text">.<input style="width: 30px;" maxlength="3" name="gdsr_ip_range_2" type="text">.<input style="width: 30px;" maxlength="3" name="gdsr_ip_range_3" type="text"></td>
                <td></td>
            </tr>
            <tr>
                <td width="150"><strong><?php _e("To", "gd-star-rating"); ?>:</strong></td>
                <td width="140"></td>
                <td>.<input style="width: 30px;" maxlength="3" name="gdsr_ip_range_to_4" type="text"></td>
            </tr>
        </table>
        <div class="gdsr-table-split"></div>
        <input type="submit" class="inputbutton" value="<?php _e("Add", "gd-star-rating"); ?>" name="gdsr_ips_banrange" id="gdsr_ips_banrange" />
        </form>
    </td>
</tr>
<tr><th scope="row"><?php _e("Ban IP Masked", "gd-star-rating"); ?></th>
    <td>
        <form method="post" action="">
        <input type="hidden" name="action" value="maskip" />
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tr>
                <td width="150"><strong><?php _e("Mask", "gd-star-rating"); ?>:</strong></td>
                <td><input style="width: 30px;" maxlength="3" name="gdsr_ip_mask_1" type="text">.<input style="width: 30px;" maxlength="3" name="gdsr_ip_mask_2" type="text">.<input style="width: 30px;" maxlength="3" name="gdsr_ip_mask_3" type="text">.<input style="width: 30px;" maxlength="3" name="gdsr_ip_mask_4" type="text"></td>
            </tr>
        </table>
        <?php _e("Use XXX as a part of IP or leave empty to mask.", "gd-star-rating"); ?>
        <div class="gdsr-table-split"></div>
        <input type="submit" class="inputbutton" value="<?php _e("Add", "gd-star-rating"); ?>" name="gdsr_ips_maskip" id="gdsr_ips_maskip" />
        </form>
    </td>
</tr>
</tbody></table>
