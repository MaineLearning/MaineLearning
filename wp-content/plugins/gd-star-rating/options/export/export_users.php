<script type="text/javascript">
function gdsrGetExportUser() {
    <?php if ($wpv < 28) { ?>
    var us = jQuery("input[@name='gdsr_export_user']:checked").val();
    var de = jQuery("input[@name='gdsr_export_data']:checked").val();
    var url = "<?php echo STARRATING_URL; ?>export.php?ex=user&us=" + us + "&de=" + de;
    if (jQuery("input[@name='gdsr_export_ip']:checked").val() == "on") url = url + "&ip=on";
    if (jQuery("input[@name='gdsr_export_agent']:checked").val() == "on") url = url + "&ua=on";
    if (jQuery("input[@name='gdsr_export_post_title']:checked").val() == "on") url = url + "&pt=on";
    if (jQuery("input[@name='gdsr_export_post_author']:checked").val() == "on") url = url + "&pa=on";
    if (jQuery("input[@name='gdsr_export_post-date']:checked").val() == "on") url = url + "&pd=on";
    if (jQuery("input[@name='gdsr_export_comment_author']:checked").val() == "on") url = url + "&ca=on";
    if (jQuery("input[@name='gdsr_export_comment-date']:checked").val() == "on") url = url + "&cd=on";
    <?php } else { ?>
    var us = jQuery("input[name='gdsr_export_user']:checked").val();
    var de = jQuery("input[name='gdsr_export_data']:checked").val();
    var url = "<?php echo STARRATING_URL; ?>export.php?ex=user&us=" + us + "&de=" + de;
    if (jQuery("input[name='gdsr_export_ip']:checked").val() == "on") url = url + "&ip=on";
    if (jQuery("input[name='gdsr_export_agent']:checked").val() == "on") url = url + "&ua=on";
    if (jQuery("input[name='gdsr_export_post_title']:checked").val() == "on") url = url + "&pt=on";
    if (jQuery("input[name='gdsr_export_post_author']:checked").val() == "on") url = url + "&pa=on";
    if (jQuery("input[name='gdsr_export_post-date']:checked").val() == "on") url = url + "&pd=on";
    if (jQuery("input[name='gdsr_export_comment_author']:checked").val() == "on") url = url + "&ca=on";
    if (jQuery("input[name='gdsr_export_comment-date']:checked").val() == "on") url = url + "&cd=on";
    <?php } ?>
    window.location = url;
}
</script>
<table class="form-table"><tbody>
<tr><th scope="row"><?php _e("User Info", "gd-star-rating"); ?></th>
    <td>
        <input type="radio" id="gdsr_export_user" name="gdsr_export_user" value="min" checked="checked" /><label> <?php _e("Minimal: only user id is exported", "gd-star-rating"); ?></label><br />
        <input type="radio" id="gdsr_export_user" name="gdsr_export_user" value="nor" /><label> <?php _e("Normal: user id, name and email are exported", "gd-star-rating"); ?></label><br />
    </td>
</tr>
<tr><th scope="row"><?php _e("Data", "gd-star-rating"); ?></th>
    <td>
        <input type="radio" id="gdsr_export_data" name="gdsr_export_data" value="article" checked="checked" /><label> <?php _e("Post / Page votes", "gd-star-rating"); ?></label><br />
        <input type="radio" id="gdsr_export_data" name="gdsr_export_data" value="comment" /><label> <?php _e("Comments votes", "gd-star-rating"); ?></label><br />
    </td>
</tr>
<tr><th scope="row"><?php _e("Additional Columns", "gd-star-rating"); ?></th>
    <td>
        <input type="checkbox" id="gdsr_export_ip" name="gdsr_export_ip" /><label> <?php _e("IP", "gd-star-rating"); ?></label><br />
        <input type="checkbox" id="gdsr_export_agent" name="gdsr_export_agent" /><label> <?php _e("User Agent", "gd-star-rating"); ?></label>
        <div class="gdsr-table-split"></div>
        <input type="checkbox" id="gdsr_export_post_title" name="gdsr_export_post_title" /><label> <?php _e("Post/Page title", "gd-star-rating"); ?></label><br />
        <input type="checkbox" id="gdsr_export_post_date" name="gdsr_export_post_date" /><label> <?php _e("Post/Page date", "gd-star-rating"); ?></label>
        <div class="gdsr-table-split"></div>
        <input type="checkbox" id="gdsr_export_comment_author" name="gdsr_export_comment_author" /><label> <?php _e("Comment author", "gd-star-rating"); ?></label><br />
        <input type="checkbox" id="gdsr_export_comment_date" name="gdsr_export_comment_date" /><label> <?php _e("Comment date", "gd-star-rating"); ?></label>
    </td>
</tr>
<tr><th scope="row"><?php _e("Export", "gd-star-rating"); ?></th>
    <td>
        <?php _e("This will export voters data file.", "gd-star-rating"); ?><br />
        <div class="inputbutton"><a href="javascript:gdsrGetExportUser()"><?php _e("Export", "gd-star-rating"); ?></a></div>
        <div class="gdsr-table-split"></div>
        <?php _e("Exported file will contain all the users ratings. File will be in CSV format.", "gd-star-rating"); ?>
    </td>
</tr>
</tbody></table>

