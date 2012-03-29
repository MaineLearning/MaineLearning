<?php

$default_preview_class = "wait-preview-holder-article loader ";
$default_preview_class.= $gdsr_options["wait_loader_article"]." ";
if ($gdsr_options["wait_show_article"] == 1)
    $default_preview_class.= "width ";
$default_preview_class.= $gdsr_options["wait_class_article"];

?>
<table class="form-table"><tbody>
<tr><th scope="row"><?php _e("Rating", "gd-star-rating"); ?></th>
    <td>
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tr>
                <td width="150"><?php _e("Stars", "gd-star-rating"); ?>:</td>
                <td width="200" align="left">
                <select style="width: 180px;" name="gdsr_style" id="gdsr_style">
                <?php GDSRHelper::render_styles_select($gdsr_gfx->stars, $gdsr_options["style"]); ?>
                </select>
                </td>
                <td width="10"></td>
                <td width="150" align="left">
                <?php GDSRHelper::render_star_sizes("gdsr_size", $gdsr_options["size"]); ?>
                </td>
                <td width="10"></td>
                <td width="100"><?php _e("Number of stars", "gd-star-rating"); ?>:</td>
                <td width="80" align="left">
                <select style="width: 70px;" name="gdsr_stars" id="gdsr_stars">
                <?php GDSRHelper::render_stars_select($gdsr_options["stars"]); ?>
                </select>
                </td>
            </tr>
        </table>
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tr>
                <td width="150">MSIE 6:</td>
                <td width="200" align="left">
                <select style="width: 180px;" name="gdsr_style_ie6" id="gdsr_style_ie6">
                <?php GDSRHelper::render_styles_select($gdsr_gfx->stars, $gdsr_options["style_ie6"]); ?>
                </select>
                </td>
                <td width="10"></td>
                <td width="150"><?php _e("Rating header", "gd-star-rating"); ?>:</td>
                <td><input type="text" name="gdsr_header_text" id="gdsr_header_text" value="<?php echo esc_attr($gdsr_options["header_text"]); ?>" style="width: 170px" /></td>
            </tr>
        </table>
        <div class="gdsr-table-split"></div>
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tr>
                <td width="150" valign="top"><?php _e("Auto insert rating code", "gd-star-rating"); ?>:</td>
                <td width="200" valign="top">
                    <input type="checkbox" name="gdsr_posts" id="gdsr_posts"<?php if ($gdsr_options["display_posts"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_posts"><?php _e("For individual posts.", "gd-star-rating"); ?></label>
                    <br />
                    <input type="checkbox" name="gdsr_pages" id="gdsr_pages"<?php if ($gdsr_options["display_pages"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_pages"><?php _e("For individual pages.", "gd-star-rating"); ?></label>
                </td>
                <td width="10"></td>
                <td valign="top">
                    <input type="checkbox" name="gdsr_archive" id="gdsr_archive"<?php if ($gdsr_options["display_archive"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_archive"><?php _e("For posts displayed in Archives.", "gd-star-rating"); ?></label>
                    <br />
                    <input type="checkbox" name="gdsr_home" id="gdsr_home"<?php if ($gdsr_options["display_home"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_home"><?php _e("For posts displayed on Front Page.", "gd-star-rating"); ?></label>
                    <br />
                    <input type="checkbox" name="gdsr_search" id="gdsr_search"<?php if ($gdsr_options["display_search"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_search"><?php _e("For posts displayed on Search results.", "gd-star-rating"); ?></label>
                </td>
            </tr>
        </table>
        <div class="gdsr-table-split"></div>
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tr>
                <td width="150"><?php _e("Auto insert location", "gd-star-rating"); ?>:</td>
                <td width="200" valign="top"><?php GDSRHelper::render_insert_position("gdsr_auto_display_position", $gdsr_options["auto_display_position"]); ?></td>
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
                <?php GDSRHelper::render_rules_combo("gdsr_default_vote_articles", $gdsr_options["default_voterules_articles"]); ?>
                </td>
                <td width="10"></td>
            <?php if ($gdsr_options["moderation_active"] == 1) { ?>
                <td width="150"><?php _e("Moderation rule", "gd-star-rating"); ?>:</td>
                <td width="200" align="left">
                <?php GDSRHelper::render_moderation_combo("gdsr_default_mod_articles", $gdsr_options["default_moderation_articles"]); ?>
                </td>
            <?php } ?>
            </tr>
        </table>
        <?php if ($gdsr_options["timer_active"] == 1) { ?>
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tr>
                <td width="150"><?php _e("Time restriction", "gd-star-rating"); ?>:</td>
                <td width="200" align="left">
                <?php GDSRHelper::render_timer_combo("gdsr_default_timer_type", $gdsr_options["default_timer_type"]); ?>
                </td>
                <td width="10"></td>
                <td width="150"><?php _e("Countdown value", "gd-star-rating"); ?>:</td>
                <td width="200" align="left">
                <input type="text" value="<?php echo $gdsr_options["default_timer_countdown_value"]; ?>" id="gdsr_default_timer_countdown_value" name="gdsr_default_timer_countdown_value" style="width: 80px; text-align: right;" />
                <?php GDSRHelper::render_countdown_combo("gdsr_default_timer_countdown_type", $gdsr_options["default_timer_countdown_type"], 85); ?>
                </td>
            </tr>
        </table>
        <?php } ?>
        <div class="gdsr-table-split"></div>
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tr>
                <td width="150"><?php _e("Rating block CSS class", "gd-star-rating"); ?>:</td>
                <td width="200">
                    <input type="text" name="gdsr_classblock" value="<?php echo esc_attr($gdsr_options["srb_class_block"]); ?>" style="width: 170px" />
                </td>
                <td width="10"></td>
                <td width="150"><?php _e("Rating text CSS class", "gd-star-rating"); ?>:</td>
                <td>
                    <input type="text" name="gdsr_classtext" value="<?php echo esc_attr($gdsr_options["srb_class_text"]); ?>" style="width: 170px" />
                </td>
            </tr>
            <tr>
                <td width="150"><?php _e("Rating header CSS class", "gd-star-rating"); ?>:</td>
                <td width="200">
                    <input type="text" name="gdsr_classheader" value="<?php echo esc_attr($gdsr_options["srb_class_header"]); ?>" style="width: 170px" />
                </td>
                <td width="10"></td>
                <td width="150"><?php _e("Rating stars CSS class", "gd-star-rating"); ?>:</td>
                <td>
                    <input type="text" name="gdsr_classstars" value="<?php echo esc_attr($gdsr_options["srb_class_stars"]); ?>" style="width: 170px" />
                </td>
            </tr>
        </table>
        <div class="gdsr-table-split"></div>
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tr>
                <td width="150"><?php _e("Rating Template", "gd-star-rating"); ?>:</td>
                <td align="left"><?php gdTemplateHelper::render_templates_section("SRB", "gdsr_default_srb_template", $gdsr_options["default_srb_template"], 350); ?></td>
            </tr>
        </table>
    </td>
</tr>
<tr><th scope="row"><?php _e("Restrict", "gd-star-rating"); ?></th>
    <td>
        <input type="checkbox" name="gdsr_authorvote" id="gdsr_authorvote"<?php if ($gdsr_options["author_vote"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_authorvote"><?php _e("Prevent article author to vote.", "gd-star-rating"); ?> <?php _e("This is only for registered users.", "gd-star-rating"); ?></label>
        <div class="gdsr-table-split"></div>
        <input type="checkbox" name="gdsr_logged" id="gdsr_logged"<?php if ($gdsr_options["logged"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_logged"><?php _e("Use logged data (IP) to prevent duplicate voting.", "gd-star-rating"); ?></label>
        <br />
        <input type="checkbox" name="gdsr_cookies" id="gdsr_cookies"<?php if ($gdsr_options["cookies"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_cookies"><?php _e("Use cookies to prevent duplicate voting.", "gd-star-rating"); ?></label>
        <br />
        <input type="checkbox" name="gdsr_allow_mixed_ip_votes" id="gdsr_allow_mixed_ip_votes"<?php if ($gdsr_options["allow_mixed_ip_votes"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_allow_mixed_ip_votes"><?php _e("Allow votes from user and visitor coming from same IP address.", "gd-star-rating"); ?></label>
    </td>
</tr>
<tr><th scope="row"><?php _e("Vote Waiting Message", "gd-star-rating"); ?></th>
    <td>
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tr>
                <td width="150"><?php _e("Animation indicator", "gd-star-rating"); ?>:</td>
                <td width="200"><?php GDSRHelper::render_loaders("gdsr_wait_loader_article", $gdsr_options["wait_loader_article"]); ?></td>
                <td width="10"></td>
                <td rowspan="3" width="150" valign="top"><?php _e("Preview", "gd-star-rating"); ?>:</td>
                <td rowspan="3" valign="top">
                    <div class="wait-preview-article">
                        <div id="gdsrwaitpreviewarticle" class="<?php echo $default_preview_class; ?>">
                            <?php if ($gdsr_options["wait_show_article"] == 0) echo $gdsr_options["wait_text_article"]; ?>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td width="150"><?php _e("Text to display", "gd-star-rating"); ?>:</td>
                <td width="200"><input class="jqloaderarticle" type="text" name="gdsr_wait_text_article" id="gdsr_wait_text_article" value="<?php echo $gdsr_options["wait_text_article"]; ?>" style="width: 170px;" /></td>
                <td width="10"></td>
            </tr>
            <tr>
                <td width="150"><?php _e("Additional CSS class", "gd-star-rating"); ?>:</td>
                <td width="200"><input class="jqloaderarticle" type="text" name="gdsr_wait_class_article" id="gdsr_wait_class_article" value="<?php echo $gdsr_options["wait_class_article"]; ?>" style="width: 170px;" /></td>
                <td width="10"></td>
            </tr>
        </table>
        <div class="gdsr-table-split"></div>
        <input class="jqloaderarticle" type="checkbox" name="gdsr_wait_show_article" id="gdsr_wait_show_article"<?php if ($gdsr_options["wait_show_article"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_wait_show_article"><?php _e("Hide text and show only animation image.", "gd-star-rating"); ?></label>
    </td>
</tr>
<?php if ($gdsr_options["review_active"] == 1) { ?>
<tr><th scope="row"><?php _e("Review", "gd-star-rating"); ?></th>
    <td>
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tr>
                <td width="150"><?php _e("Stars", "gd-star-rating"); ?>:</td>
                <td width="200" align="left">
                <select style="width: 180px;" name="gdsr_review_style" id="gdsr_review_style">
                <?php GDSRHelper::render_styles_select($gdsr_gfx->stars, $gdsr_options["review_style"]); ?>
                </select>
                </td>
                <td width="10"></td>
                <td width="150" align="left">
                <?php GDSRHelper::render_star_sizes("gdsr_review_size", $gdsr_options["review_size"]); ?>
                </td>
                <td width="10"></td>
                <td width="100"><?php _e("Number of stars", "gd-star-rating"); ?>:</td>
                <td width="80" align="left">
                <select style="width: 70px;" name="gdsr_review_stars" id="gdsr_review_stars">
                <?php GDSRHelper::render_stars_select($gdsr_options["review_stars"]); ?>
                </select>
                </td>
            </tr>
            <tr>
                <td width="150">MSIE 6:</td>
                <td width="200" align="left" colspan="6">
                <select style="width: 180px;" name="gdsr_review_style_ie6" id="gdsr_review_style_ie6">
                <?php GDSRHelper::render_styles_select($gdsr_gfx->stars, $gdsr_options["review_style_ie6"]); ?>
                </select>
                </td>
            </tr>
        </table>
        <div class="gdsr-table-split"></div>
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tr>
                <td width="150"><?php _e("Rating header", "gd-star-rating"); ?>:</td>
                <td width="200">
                    <input type="text" name="gdsr_review_header_text" id="gdsr_review_header_text" value="<?php echo esc_attr($gdsr_options["review_header_text"]); ?>" style="width: 170px" />
                </td>
                <td width="10"></td>
                <td width="150"><?php _e("Rating block CSS class", "gd-star-rating"); ?>:</td>
                <td>
                    <input type="text" name="gdsr_review_classblock" id="gdsr_review_classblock" value="<?php echo esc_attr($gdsr_options["review_class_block"]); ?>" style="width: 170px" />
                </td>
            </tr>
        </table>
    </td>
</tr>
<?php } ?>
</tbody></table>
