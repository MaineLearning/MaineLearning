<?php $gdsr_multis = GDSRDBMulti::get_multis_tinymce(); ?>
<table class="form-table"><tbody>
<tr><th scope="row"><?php _e("Date Based Post Locking", "gd-star-rating"); ?></th>
    <td>
        <form method="post">
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tr>
                <td width="150" height="25"><?php _e("Lock posts older than", "gd-star-rating"); ?>:</td>
                <td height="25"><input type="text" id="gdsr_lock_date" name="gdsr_lock_date" value="" /></td>
            </tr>
        </table>
        <input type="submit" class="inputbutton" value="<?php _e("Lock", "gd-star-rating"); ?>" name="gdsr_post_lock" id="gdsr_post_lock" />
        <div class="gdsr-table-split"></div>
        <?php _e("Previous Lock Date", "gd-star-rating"); ?>: <strong><?php echo $gdsr_options['mass_lock']; ?></strong>
        </form>
    </td>
</tr>
<tr><th scope="row"><?php _e("Global Voting Rules Update", "gd-star-rating"); ?></th>
    <td>
        <form method="post">
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tr>
                <td width="150" height="25"><strong><?php _e("Articles Stars", "gd-star-rating"); ?>:</strong></td>
                <?php if ($gdsr_options["moderation_active"] == 1) { ?>
                <td style="width: 80px; height: 25px;">
                    <span class="paneltext"><?php _e("Moderation", "gd-star-rating"); ?>:</span>
                </td>
                <td style="width: 140px; height: 25px;" align="right">
                <?php GDSRHelper::render_moderation_combo("gdsr_article_moderation", "/", 120, "", true); ?>
                </td><td style="width: 10px"></td>
                <?php } ?>
                <td style="width: 80px; height: 25px;">
                    <span class="paneltext"><?php _e("Vote Rules", "gd-star-rating"); ?>:</span>
                </td>
                <td style="width: 140px; height: 25px;" align="right">
                <?php GDSRHelper::render_rules_combo("gdsr_article_voterules", "/", 120, "", true); ?>
                </td>
            </tr>
        </table>
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tr>
                <td width="150" height="25"><strong><?php _e("Articles Thumbs", "gd-star-rating"); ?>:</strong></td>
                <?php if ($gdsr_options["moderation_active"] == 1) { ?>
                <td style="width: 80px; height: 25px;">
                    <span class="paneltext"><?php _e("Moderation", "gd-star-rating"); ?>:</span>
                </td>
                <td style="width: 140px; height: 25px;" align="right">
                <?php GDSRHelper::render_moderation_combo("gdsr_artthumb_moderation", "/", 120, "", true); ?>
                </td><td style="width: 10px"></td>
                <?php } ?>
                <td style="width: 80px; height: 25px;">
                    <span class="paneltext"><?php _e("Vote Rules", "gd-star-rating"); ?>:</span>
                </td>
                <td style="width: 140px; height: 25px;" align="right">
                <?php GDSRHelper::render_rules_combo("gdsr_artthumb_voterules", "/", 120, "", true); ?>
                </td>
            </tr>
        </table>
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tr>
                <td width="150" height="25"><strong><?php _e("Comments Stars", "gd-star-rating"); ?>:</strong></td>
                <?php if ($gdsr_options["moderation_active"] == 1) { ?>
                <td style="width: 80px; height: 25px;">
                    <span class="paneltext"><?php _e("Moderation", "gd-star-rating"); ?>:</span>
                </td>
                <td style="width: 140px; height: 25px;" align="right">
                <?php GDSRHelper::render_moderation_combo("gdsr_comments_moderation", "/", 120, "", true); ?>
                </td><td style="width: 10px"></td>
                <?php } ?>
                <td style="width: 80px; height: 25px;">
                    <span class="paneltext"><?php _e("Vote Rules", "gd-star-rating"); ?>:</span>
                </td>
                <td style="width: 140px; height: 25px;" align="right">
                <?php GDSRHelper::render_rules_combo("gdsr_comments_voterules", "/", 120, "", true); ?>
                </td>
            </tr>
        </table>
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tr>
                <td width="150" height="25"><strong><?php _e("Comments Thumbs", "gd-star-rating"); ?>:</strong></td>
                <?php if ($gdsr_options["moderation_active"] == 1) { ?>
                <td style="width: 80px; height: 25px;">
                    <span class="paneltext"><?php _e("Moderation", "gd-star-rating"); ?>:</span>
                </td>
                <td style="width: 140px; height: 25px;" align="right">
                <?php GDSRHelper::render_moderation_combo("gdsr_cmmthumbs_moderation", "/", 120, "", true); ?>
                </td><td style="width: 10px"></td>
                <?php } ?>
                <td style="width: 80px; height: 25px;">
                    <span class="paneltext"><?php _e("Vote Rules", "gd-star-rating"); ?>:</span>
                </td>
                <td style="width: 140px; height: 25px;" align="right">
                <?php GDSRHelper::render_rules_combo("gdsr_cmmthumbs_voterules", "/", 120, "", true); ?>
                </td>
            </tr>
        </table>
        <input type="submit" class="inputbutton" value="<?php _e("Set", "gd-star-rating"); ?>" name="gdsr_rules_set" id="gdsr_rules_set" />
        <div class="gdsr-table-split"></div>
        <?php _e("This will update all posts and comments with previously saved ratings.", "gd-star-rating"); ?>
        </form>
    </td>
</tr>
<tr><th scope="row"><?php _e("Recalculate Multi Ratings Averages", "gd-star-rating"); ?></th>
    <td>
        <form method="post">
        <?php _e("If you change multi rating sets weight for the elements you can execute recaluculation of all average ratings saved. Select the set you want to recalculate for, or select all sets.", "gd-star-rating"); ?><br />
        <?php _e("Use this also if you update from versions before 1.2.0 and you had problems with saving multi rating results.", "gd-star-rating"); ?><br />
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tr>
                <td width="150" height="25"><?php _e("Select multi set", "gd-star-rating"); ?>:</td>
                <td height="25">
                <select name="gdsr_mulitrecalc_set" style="width: 200px">
                    <option value="0"><?php _e("All Sets", "gd-star-rating"); ?></option>
                    <?php GDSRHelper::render_styles_select($gdsr_multis); ?>
                </select>
                </td>
            </tr>
        </table>
        <input type="submit" class="inputbutton" value="<?php _e("Recalculate", "gd-star-rating"); ?>" name="gdsr_mulitrecalc_tool" id="gdsr_mulitrecalc_tool" />
        <div class="gdsr-table-split"></div>
        <?php _e("Depending on the number of posts, this operation can take a while to complete. Be patient.", "gd-star-rating"); ?>
        </form>
    </td>
</tr>
<tr><th scope="row"><?php _e("Update Multi Rating Votes Log", "gd-star-rating"); ?></th>
    <td>
        <form method="post">
        <?php _e("This tool will update votes log and recalculate average value for each multi ratings votes.", "gd-star-rating"); ?><br />
        <input type="submit" class="inputbutton" value="<?php _e("Update", "gd-star-rating"); ?>" name="gdsr_updatemultilog_tool" id="gdsr_updatemultilog_tool" />
        <div class="gdsr-table-split"></div>
        <?php _e("You need to do only once.", "gd-star-rating"); ?>
        </form>
    </td>
</tr>
</tbody></table>
