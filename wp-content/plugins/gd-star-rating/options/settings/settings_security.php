<table class="form-table"><tbody>
<tr><th scope="row"><?php _e("Voting", "gd-star-rating"); ?></th>
    <td>
        <input type="checkbox" name="gdsr_use_nonce" id="gdsr_use_nonce"<?php if ($gdsr_options["use_nonce"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="use_nonce"><?php _e("Use Nonce with AJAX for improved security.", "gd-star-rating"); ?></label>
        <br />
        <input type="checkbox" name="gdsr_ajax_jsonp" id="gdsr_ajax_jsonp"<?php if ($gdsr_options["ajax_jsonp"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_ajax_jsonp"><?php _e("Use AJAX cross-domain calls. Enable only if your website URL is different from actual URL.", "gd-star-rating"); ?></label>
        <div class="gdsr-table-split"></div>
        <input type="checkbox" name="gdsr_ip_filtering" id="gdsr_ip_filtering"<?php if ($gdsr_options["ip_filtering"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_ip_filtering"><?php _e("Use banned IP's lists to filter out visitors.", "gd-star-rating"); ?></label>
        <br />
        <input type="checkbox" name="gdsr_ip_filtering_restrictive" id="gdsr_ip_filtering_restrictive"<?php if ($gdsr_options["ip_filtering_restrictive"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_ip_filtering_restrictive"><?php _e("Don't even show rating stars to visitors comming from banned IP's.", "gd-star-rating"); ?></label>
    </td>
</tr>
<tr><th scope="row"><?php _e("User Levels", "gd-star-rating"); ?></th>
    <td>
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tr>
                <td width="450"><?php _e("Required to show the dashboard widgets", "gd-star-rating"); ?>:</td>
                <td><input type="text" name="gdsr_security_showdashboard_user_level" id="gdsr_security_showdashboard_user_level" value="<?php echo $gdsr_options["security_showdashboard_user_level"]; ?>" style="width: 70px; text-align: right;" /></td>
            </tr>
            <tr>
                <td width="450"><?php _e("Required to show IP's in widgets and my ratings", "gd-star-rating"); ?>:</td>
                <td><input type="text" name="gdsr_security_showip_user_level" id="gdsr_security_showip_user_level" value="<?php echo $gdsr_options["security_showip_user_level"]; ?>" style="width: 70px; text-align: right;" /></td>
            </tr>
        </table>
    </td>
</tr>
<tr><th scope="row"><?php _e("Rating Log", "gd-star-rating"); ?></th>
    <td>
        <input type="checkbox" name="gdsr_save_user_agent" id="gdsr_save_user_agent"<?php if ($gdsr_options["save_user_agent"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_save_user_agent"><?php _e("Log user agent (browser) information.", "gd-star-rating"); ?></label>
        <br />
        <input type="checkbox" name="gdsr_save_cookies" id="gdsr_save_cookies"<?php if ($gdsr_options["save_cookies"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_save_cookies"><?php _e("Save cookies with ratings.", "gd-star-rating"); ?></label>
    </td>
</tr>
<tr><th scope="row"><?php _e("Spider BOT's", "gd-star-rating"); ?></th>
    <td>
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tr>
                <td width="250" valign="top">
                <textarea style="width: 230px; height: 170px;" name="gdsr_bots"><?php echo join("\r\n", $gdsr_bots); ?></textarea>
                </td>
                <td valign="top">
                <?php _e("Each line must contain only one BOT name to ensure proper detection of search engines.", "gd-star-rating"); ?>
                </td>
            </tr>
        </table>
    </td>
</tr>
<tr><th scope="row"><?php _e("Plugin Update", "gd-star-rating"); ?></th>
    <td>
        <input type="checkbox" name="gdsr_update_report_usage" id="gdsr_update_report_usage"<?php if ($gdsr_options["update_report_usage"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_update_report_usage"><?php _e("Report basic usage data that will be used for statistical purposes only.", "gd-star-rating"); ?></label>
        <div class="gdsr-table-split"></div>
        <?php _e("This report will include your WordPress version and website URL. Report will be sent only when plugin needs to be updated.", "gd-star-rating"); ?>
    </td>
</tr>
</tbody></table>
