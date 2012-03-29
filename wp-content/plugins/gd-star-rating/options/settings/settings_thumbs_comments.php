<table class="form-table"><tbody>
<tr><th scope="row"><?php _e("Rating", "gd-star-rating"); ?></th>
    <td>
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tr>
                <td width="150"><?php _e("Thumbs Set", "gd-star-rating"); ?>:</td>
                <td width="200" align="left">
                <select style="width: 180px;" name="gdsr_thumb_cmm_style" id="gdsr_thumb_cmm_style">
                <?php GDSRHelper::render_styles_select($gdsr_gfx->thumbs, $gdsr_options["thumb_cmm_style"]); ?>
                </select>
                </td>
                <td width="10"></td>
                <td width="150" align="left">
                <?php GDSRHelper::render_thumbs_sizes("gdsr_thumb_cmm_size", $gdsr_options["thumb_cmm_size"]); ?>
                </td>
            </tr>
        </table>
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tr>
                <td width="150">MSIE 6:</td>
                <td width="200" align="left">
                <select style="width: 180px;" name="gdsr_thumb_cmm_style_ie6" id="gdsr_thumb_cmm_style_ie6">
                <?php GDSRHelper::render_styles_select($gdsr_gfx->thumbs, $gdsr_options["thumb_cmm_style_ie6"]); ?>
                </select>
                </td>
                <td width="10"></td>
                <td width="150"><?php _e("Rating header", "gd-star-rating"); ?>:</td>
                <td><input type="text" name="gdsr_thumb_cmm_header_text" id="gdsr_thumb_cmm_header_text" value="<?php echo esc_attr($gdsr_options["thumb_cmm_header_text"]); ?>" style="width: 170px" /></td>
            </tr>
        </table>
        <div class="gdsr-table-split"></div>
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tr>
                <td width="150" valign="top"><?php _e("Auto insert rating code", "gd-star-rating"); ?>:</td>
                <td valign="top" width="200">
                    <input type="checkbox" name="gdsr_thumb_dispcomment" id="gdsrthumb__dispcomment"<?php if ($gdsr_options["thumb_display_comment"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_thumb_dispcomment"><?php _e("For comments for posts.", "gd-star-rating"); ?></label>
                </td>
                <td width="10"></td>
                <td valign="top">
                    <input type="checkbox" name="gdsr_thumb_dispcomment_pages" id="gdsr_thumb_dispcomment_pages"<?php if ($gdsr_options["thumb_display_comment_page"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_thumb_dispcomment_pages"><?php _e("For comments for pages.", "gd-star-rating"); ?></label>
                </td>
            </tr>
            <tr>
                <td width="150" valign="top">
                </td>
                <td valign="top" colspan="3">
                    <input type="checkbox" name="gdsr_override_thumb_display_comment" id="gdsr_override_thumb_display_comment"<?php if ($gdsr_options["override_thumb_display_comment"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_override_thumb_display_comment"><?php _e("Enable auto insert regardless of the single page or post.", "gd-star-rating"); ?></label>
                </td>
            </tr>
        </table>
        <div class="gdsr-table-split"></div>
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tr>
                <td width="150"><?php _e("Auto insert location", "gd-star-rating"); ?>:</td>
                <td width="200" valign="top"><?php GDSRHelper::render_insert_position("gdsr_thumb_auto_display_comment_position", $gdsr_options["thumb_auto_display_comment_position"]); ?></td>
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
                <?php GDSRHelper::render_rules_combo("gdsr_recc_default_vote_comments", $gdsr_options["recc_default_voterules_comments"]); ?>
                </td>
                <td width="10"></td>
            <?php if ($gdsr_options["moderation_active"] == 1) { ?>
                <td width="150"><?php _e("Moderation rule", "gd-star-rating"); ?>:</td>
                <td width="200" align="left">
                <?php GDSRHelper::render_moderation_combo("gdsr_recc_default_mod_comments", $gdsr_options["recc_default_moderation_comments"]); ?>
                </td>
            <?php } ?>
            </tr>
        </table>
        <div class="gdsr-table-split"></div>
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tr>
                <td width="150"><?php _e("Rating Template", "gd-star-rating"); ?>:</td>
                <td align="left"><?php gdTemplateHelper::render_templates_section("TCB", "gdsr_default_tcb_template", $gdsr_options["default_tcb_template"], 350); ?></td>
            </tr>
            <tr>
                <td width="150"><?php _e("Animation indicator", "gd-star-rating"); ?>:</td>
                <td align="left"><?php GDSRHelper::render_loaders("gdsr_wait_loader_cmmthumb", $gdsr_options["wait_loader_cmmthumb"], 'jqloadercmmthumb', 180, '', true); ?></td>
            </tr>
        </table>
     </td>
</tr>
</tbody></table>
