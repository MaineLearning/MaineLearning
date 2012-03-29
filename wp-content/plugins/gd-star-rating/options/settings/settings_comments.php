<?php

$default_preview_class = "wait-preview-holder-comment loader ";
$default_preview_class.= $gdsr_options["wait_loader_comment"]." ";
if ($gdsr_options["wait_show_comment"] == 1)
    $default_preview_class.= "width ";
$default_preview_class.= $gdsr_options["wait_class_comment"];

?>
<table class="form-table"><tbody>
<tr><th scope="row"><?php _e("Rating", "gd-star-rating"); ?></th>
    <td>
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tr>
                <td width="150"><?php _e("Stars", "gd-star-rating"); ?>:</td>
                <td width="200" align="left">
                <select style="width: 180px;" name="gdsr_cmm_style" id="gdsr_cmm_style">
                <?php GDSRHelper::render_styles_select($gdsr_gfx->stars, $gdsr_options["cmm_style"]); ?>
                </select>
                </td>
                <td width="10"></td>
                <td width="150" align="left">
                <?php GDSRHelper::render_star_sizes("gdsr_cmm_size", $gdsr_options["cmm_size"]); ?>
                </td>
                <td width="10"></td>
                <td width="100"><?php _e("Number of stars", "gd-star-rating"); ?>:</td>
                <td width="80" align="left">
                <select style="width: 70px;" name="gdsr_cmm_stars" id="gdsr_cmm_stars">
                <?php GDSRHelper::render_stars_select($gdsr_options["cmm_stars"]); ?>
                </select>
                </td>
            </tr>
        </table>
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tr>
                <td width="150">MSIE 6:</td>
                <td width="200" align="left" colspan="6">
                <select style="width: 180px;" name="gdsr_cmm_style_ie6" id="gdsr_cmm_style_ie6">
                <?php GDSRHelper::render_styles_select($gdsr_gfx->stars, $gdsr_options["cmm_style_ie6"]); ?>
                </select>
                </td>
                <td width="10"></td>
                <td width="150"><?php _e("Rating header", "gd-star-rating"); ?>:</td>
                <td><input type="text" name="gdsr_cmm_header_text" id="gdsr_cmm_header_text" value="<?php echo esc_attr($gdsr_options["cmm_header_text"]); ?>" style="width: 170px" /></td>
            </tr>
        </table>
        <div class="gdsr-table-split"></div>
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tr>
                <td width="150" valign="top"><?php _e("Auto insert rating code", "gd-star-rating"); ?>:</td>
                <td valign="top" width="200">
                    <input type="checkbox" name="gdsr_dispcomment" id="gdsr_dispcomment"<?php if ($gdsr_options["display_comment"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_dispcomment"><?php _e("For comments for posts.", "gd-star-rating"); ?></label>
                </td>
                <td width="10"></td>
                <td valign="top">
                    <input type="checkbox" name="gdsr_dispcomment_pages" id="gdsr_dispcomment_pages"<?php if ($gdsr_options["display_comment_page"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_dispcomment_pages"><?php _e("For comments for pages.", "gd-star-rating"); ?></label>
                </td>
            </tr>
            <tr>
                <td width="150" valign="top">
                </td>
                <td valign="top" colspan="3">
                    <input type="checkbox" name="gdsr_override_display_comment" id="gdsr_override_display_comment"<?php if ($gdsr_options["override_display_comment"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_override_display_comment"><?php _e("Enable auto insert regardless of the single page or post.", "gd-star-rating"); ?></label>
                </td>
            </tr>
        </table>
        <div class="gdsr-table-split"></div>
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tr>
                <td width="150" valign="top"><?php _e("Auto insert location", "gd-star-rating"); ?>:</td>
                <td width="200" valign="top"><?php GDSRHelper::render_insert_position("gdsr_auto_display_comment_position", $gdsr_options["auto_display_comment_position"]); ?></td>
                <td width="10"></td>
                <td width="150"><?php _e("Rating block CSS class", "gd-star-rating"); ?>:</td>
                <td><input type="text" name="gdsr_cmm_classblock" id="gdsr_cmm_classblock" value="<?php echo esc_attr($gdsr_options["cmm_class_block"]); ?>" style="width: 170px" /></td>
            </tr>
        </table>
    </td>
</tr>
<tr><th scope="row"><?php _e("Defaults", "gd-star-rating"); ?></th>
    <td>
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tr>
                <td width="150"><?php _e("Vote rule", "gd-star-rating"); ?>:</td>
                <td width="200" align="left">
                <?php GDSRHelper::render_rules_combo("gdsr_default_vote_comments", $gdsr_options["default_voterules_comments"]); ?>
                </td>
                <td width="10"></td>
            <?php if ($gdsr_options["moderation_active"] == 1) { ?>
                <td width="150"><?php _e("Moderation rule", "gd-star-rating"); ?>:</td>
                <td width="200" align="left">
                <?php GDSRHelper::render_moderation_combo("gdsr_default_mod_comments", $gdsr_options["default_moderation_comments"]); ?>
                </td>
            <?php } ?>
            </tr>
        </table>
        <div class="gdsr-table-split"></div>
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tr>
                <td width="150"><?php _e("Rating block CSS class", "gd-star-rating"); ?>:</td>
                <td width="200">
                    <input type="text" name="gdsr_cmm_classblock" value="<?php echo esc_attr($gdsr_options["cmm_class_block"]); ?>" style="width: 170px" />
                </td>
                <td width="10"></td>
                <td width="150"><?php _e("Rating text CSS class", "gd-star-rating"); ?>:</td>
                <td>
                    <input type="text" name="gdsr_cmm_classtext" value="<?php echo esc_attr($gdsr_options["cmm_class_text"]); ?>" style="width: 170px" />
                </td>
            </tr>
            <tr>
                <td width="150"><?php _e("Rating header CSS class", "gd-star-rating"); ?>:</td>
                <td width="200">
                    <input type="text" name="gdsr_cmm_classheader" value="<?php echo esc_attr($gdsr_options["cmm_class_header"]); ?>" style="width: 170px" />
                </td>
                <td width="10"></td>
                <td width="150"><?php _e("Rating stars CSS class", "gd-star-rating"); ?>:</td>
                <td>
                    <input type="text" name="gdsr_cmm_classstars" value="<?php echo esc_attr($gdsr_options["cmm_class_stars"]); ?>" style="width: 170px" />
                </td>
            </tr>
        </table>
        <div class="gdsr-table-split"></div>
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tr>
                <td width="150"><?php _e("Rating Template", "gd-star-rating"); ?>:</td>
                <td align="left"><?php gdTemplateHelper::render_templates_section("CRB", "gdsr_default_crb_template", $gdsr_options["default_crb_template"], 350); ?></td>
            </tr>
        </table>
    </td>
</tr>
<tr><th scope="row"><?php _e("Aggregated Rating", "gd-star-rating"); ?></th>
    <td>
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tr>
                <td width="150"><?php _e("Stars", "gd-star-rating"); ?>:</td>
                <td width="200" align="left">
                <select style="width: 180px;" name="gdsr_cmm_aggr_style" id="gdsr_cmm_aggr_style">
                <?php GDSRHelper::render_styles_select($gdsr_gfx->stars, $gdsr_options["cmm_aggr_style"]); ?>
                </select>
                </td>
                <td width="10"></td>
                <td width="150" align="left">
                <?php GDSRHelper::render_star_sizes("gdsr_cmm_aggr_size", $gdsr_options["cmm_aggr_size"]); ?>
                </td>
            </tr>
            <tr>
                <td width="150">MSIE 6:</td>
                <td width="200" align="left" colspan="3">
                <select style="width: 180px;" name="gdsr_cmm_aggr_style_ie6" id="gdsr_cmm_aggr_style_ie6">
                <?php GDSRHelper::render_styles_select($gdsr_gfx->stars, $gdsr_options["cmm_aggr_style_ie6"]); ?>
                </select>
                </td>
            </tr>
        </table>
    </td>
</tr>
<tr><th scope="row"><?php _e("Restrict", "gd-star-rating"); ?></th>
    <td>
        <input type="checkbox" name="gdsr_cmm_authorvote" id="gdsr_cmm_authorvote"<?php if ($gdsr_options["cmm_author_vote"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_cmm_authorvote"><?php _e("Prevent comment author to vote.", "gd-star-rating"); ?></label>
        <div class="gdsr-table-split"></div>
        <input type="checkbox" name="gdsr_cmm_logged" id="gdsr_cmm_logged"<?php if ($gdsr_options["cmm_logged"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_cmm_logged"><?php _e("Use logged data (IP) to prevent duplicate voting.", "gd-star-rating"); ?></label>
        <br />
        <input type="checkbox" name="gdsr_cmm_cookies" id="gdsr_cmm_cookies"<?php if ($gdsr_options["cmm_cookies"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_cmm_cookies"><?php _e("Use cookies to prevent duplicate voting.", "gd-star-rating"); ?></label>
        <br />
        <input type="checkbox" name="gdsr_cmm_allow_mixed_ip_votes" id="gdsr_cmm_allow_mixed_ip_votes"<?php if ($gdsr_options["cmm_allow_mixed_ip_votes"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_cmm_allow_mixed_ip_votes"><?php _e("Allow votes from user and visitor coming from same IP address.", "gd-star-rating"); ?></label>
    </td>
</tr>
<tr><th scope="row"><?php _e("Vote Waiting Message", "gd-star-rating"); ?></th>
    <td>
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tr>
                <td width="150"><?php _e("Animation indicator", "gd-star-rating"); ?>:</td>
                <td width="200"><?php GDSRHelper::render_loaders("gdsr_wait_loader_comment", $gdsr_options["wait_loader_comment"], "jqloadercomment"); ?></td>
                <td width="10"></td>
                <td rowspan="3" width="150" valign="top"><?php _e("Preview", "gd-star-rating"); ?>:</td>
                <td rowspan="3" valign="top">
                    <div class="wait-preview-article">
                        <div id="gdsrwaitpreviewcomment" class="<?php echo $default_preview_class; ?>">
                            <?php if ($gdsr_options["wait_show_comment"] == 0) echo $gdsr_options["wait_text_comment"]; ?>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td width="150"><?php _e("Text to display", "gd-star-rating"); ?>:</td>
                <td width="200"><input class="jqloadercomment" type="text" name="gdsr_wait_text_comment" id="gdsr_wait_text_comment" value="<?php echo $gdsr_options["wait_text_comment"]; ?>" style="width: 170px;" /></td>
                <td width="10"></td>
            </tr>
            <tr>
                <td width="150"><?php _e("Additional CSS class", "gd-star-rating"); ?>:</td>
                <td width="200"><input class="jqloadercomment" type="text" name="gdsr_wait_class_comment" id="gdsr_wait_class_comment" value="<?php echo $gdsr_options["wait_class_comment"]; ?>" style="width: 170px;" /></td>
                <td width="10"></td>
            </tr>
        </table>
        <div class="gdsr-table-split"></div>
        <input class="jqloadercomment" type="checkbox" name="gdsr_wait_show_comment" id="gdsr_wait_show_comment"<?php if ($gdsr_options["wait_show_comment"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_wait_show_comment"><?php _e("Hide text and show only animation image.", "gd-star-rating"); ?></label>
    </td>
</tr>
<?php if ($gdsr_options["comments_review_active"] == 1) { ?>
<tr><th scope="row"><?php _e("Review", "gd-star-rating"); ?></th>
    <td>
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tr>
                <td width="150"><?php _e("Stars", "gd-star-rating"); ?>:</td>
                <td width="200" align="left">
                <select style="width: 180px;" name="gdsr_cmm_review_style" id="gdsr_cmm_review_style">
                <?php GDSRHelper::render_styles_select($gdsr_gfx->stars, $gdsr_options["cmm_review_style"]); ?>
                </select>
                </td>
                <td width="10"></td>
                <td width="150" align="left">
                <?php GDSRHelper::render_star_sizes("gdsr_cmm_review_size", $gdsr_options["cmm_review_size"]); ?>
                </td>
                <td width="10"></td>
                <td width="100"><?php _e("Number of stars", "gd-star-rating"); ?>:</td>
                <td width="80" align="left">
                <select style="width: 70px;" name="gdsr_cmm_review_stars" id="gdsr_cmm_review_stars">
                <?php GDSRHelper::render_stars_select($gdsr_options["cmm_review_stars"]); ?>
                </select>
                </td>
            </tr>
            <tr>
                <td width="150">MSIE 6:</td>
                <td width="200" align="left" colspan="6">
                <select style="width: 180px;" name="gdsr_cmm_review_style_ie6" id="gdsr_cmm_review_style_ie6">
                <?php GDSRHelper::render_styles_select($gdsr_gfx->stars, $gdsr_options["cmm_review_style_ie6"]); ?>
                </select>
                </td>
            </tr>
        </table>
    </td>
</tr>
<?php } ?>
</tbody></table>
