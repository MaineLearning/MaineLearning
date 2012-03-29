<?php

$default_preview_class = "wait-preview-holder-multis loader ";
$default_preview_class.= $gdsr_options["wait_loader_multis"]." ";
if ($gdsr_options["wait_show_multis"] == 1)
    $default_preview_class.= "width ";
$default_preview_class.= $gdsr_options["wait_class_multis"];

?>
<table class="form-table"><tbody>
<tr><th scope="row"><?php _e("Rating", "gd-star-rating"); ?></th>
    <td>
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tr>
                <td width="150"><?php _e("Stars", "gd-star-rating"); ?>:</td>
                <td width="200" align="left">
                <select style="width: 180px;" name="gdsr_mur_style" id="gdsr_mur_style">
                <?php GDSRHelper::render_styles_select($gdsr_gfx->stars, $gdsr_options["mur_style"]); ?>
                </select>
                </td>
                <td width="10"></td>
                <td width="150" align="left">
                <?php GDSRHelper::render_star_sizes("gdsr_mur_size", $gdsr_options["mur_size"]); ?>
                </td>
            </tr>
            <tr>
                <td width="150">MSIE 6:</td>
                <td width="200" align="left">
                <select style="width: 180px;" name="gdsr_mur_style_ie6" id="gdsr_mur_style_ie6">
                <?php GDSRHelper::render_styles_select($gdsr_gfx->stars, $gdsr_options["mur_style_ie6"]); ?>
                </select>
                </td>
                <td width="10"></td>
                <td width="150"><?php _e("Rating header", "gd-star-rating"); ?>:</td>
                <td><input type="text" name="gdsr_mur_header_text" id="gdsr_mur_header_text" value="<?php echo esc_attr($gdsr_options["mur_header_text"]); ?>" style="width: 170px" /></td>
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
                <?php GDSRHelper::render_rules_combo("gdsr_default_vote_multis", $gdsr_options["default_voterules_multis"]); ?>
                </td>
                <td width="10"></td>
            <?php if ($gdsr_options["moderation_active"] == 1) { ?>
                <td width="150"><?php _e("Moderation rule", "gd-star-rating"); ?>:</td>
                <td width="200" align="left">
                <?php GDSRHelper::render_moderation_combo("gdsr_default_mod_multis", $gdsr_options["default_moderation_multis"]); ?>
                </td>
            <?php } ?>
            </tr>
        </table>
        <?php if ($gdsr_options["timer_active"] == 1) { ?>
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tr>
                <td width="150"><?php _e("Time restriction", "gd-star-rating"); ?>:</td>
                <td width="200" align="left">
                <?php GDSRHelper::render_timer_combo("gdsr_default_mur_timer_type", $gdsr_options["default_mur_timer_type"]); ?>
                </td>
                <td width="10"></td>
                <td width="150"><?php _e("Countdown value", "gd-star-rating"); ?>:</td>
                <td width="200" align="left">
                <input type="text" value="<?php echo $gdsr_options["default_mur_timer_countdown_value"]; ?>" id="gdsr_default_mur_timer_countdown_value" name="gdsr_default_mur_timer_countdown_value" style="width: 80px; text-align: right;" />
                <?php GDSRHelper::render_countdown_combo("gdsr_default_mur_timer_countdown_type", $gdsr_options["default_mur_timer_countdown_type"], 85); ?>
                </td>
            </tr>
        </table>
        <?php } ?>
        <div class="gdsr-table-split"></div>
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tr>
                <td width="150"><?php _e("Rating block CSS class", "gd-star-rating"); ?>:</td>
                <td width="200">
                    <input type="text" name="gdsr_mur_classblock" id="gdsr_mur_classblock" value="<?php echo esc_attr($gdsr_options["mur_class_block"]); ?>" style="width: 170px" />
                </td>
                <td width="10"></td>
                <td width="150"><?php _e("Rating text CSS class", "gd-star-rating"); ?>:</td>
                <td>
                    <input type="text" name="gdsr_mur_classtext" id="gdsr_mur_classtext" value="<?php echo esc_attr($gdsr_options["mur_class_text"]); ?>" style="width: 170px" />
                </td>
            </tr>
            <tr>
                <td width="150"><?php _e("Rating header CSS class", "gd-star-rating"); ?>:</td>
                <td width="200">
                    <input type="text" name="gdsr_mur_classheader" id="gdsr_mur_classheader" value="<?php echo esc_attr($gdsr_options["mur_class_header"]); ?>" style="width: 170px" />
                </td>
                <td width="10"></td>
                <td width="150"><?php _e("Rating button CSS class", "gd-star-rating"); ?>:</td>
                <td>
                    <input type="text" name="gdsr_mur_classbutton" id="gdsr_mur_classbutton" value="<?php echo esc_attr($gdsr_options["mur_class_button"]); ?>" style="width: 170px" />
                </td>
            </tr>
        </table>
        <div class="gdsr-table-split"></div>
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tr>
                <td width="150"><?php _e("Rating Template", "gd-star-rating"); ?>:</td>
                <td align="left"><?php gdTemplateHelper::render_templates_section("MRB", "gdsr_default_mrb_template", $gdsr_options["default_mrb_template"], 350); ?></td>
            </tr>
        </table>
    </td>
</tr>
<tr><th scope="row"><?php _e("Submit Button", "gd-star-rating"); ?></th>
    <td>
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tr>
                <td width="150"><?php _e("Button text", "gd-star-rating"); ?>:</td>
                <td width="200">
                    <input type="text" name="gdsr_mur_submittext" id="gdsr_mur_submittext" value="<?php echo esc_attr($gdsr_options["mur_button_text"]); ?>" style="width: 170px" />
                </td>
            </tr>
        </table>
        <div class="gdsr-table-split"></div>
        <input type="checkbox" name="gdsr_mur_submitactive" id="gdsr_mur_submitactive"<?php if ($gdsr_options["mur_button_active"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_mur_submitactive"><?php _e("Use submit button to send votes. If disabled, votes will be send once all block elements are rated.", "gd-star-rating"); ?></label>
    </td>
</tr>
<tr><th scope="row"><?php _e("Vote Waiting Message", "gd-star-rating"); ?></th>
    <td>
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tr>
                <td width="150"><?php _e("Animation indicator", "gd-star-rating"); ?>:</td>
                <td width="200"><?php GDSRHelper::render_loaders("gdsr_wait_loader_multis", $gdsr_options["wait_loader_multis"], 'jqloadermultis'); ?></td>
                <td width="10"></td>
                <td rowspan="3" width="150" valign="top"><?php _e("Preview", "gd-star-rating"); ?>:</td>
                <td rowspan="3" valign="top">
                    <div class="wait-preview-article">
                        <div id="gdsrwaitpreviewmultis" class="<?php echo $default_preview_class; ?>">
                            <?php if ($gdsr_options["wait_show_multis"] == 0) echo $gdsr_options["wait_text_multis"]; ?>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td width="150"><?php _e("Text to display", "gd-star-rating"); ?>:</td>
                <td width="200"><input class="jqloadermultis" type="text" name="gdsr_wait_text_multis" id="gdsr_wait_text_multis" value="<?php echo $gdsr_options["wait_text_multis"]; ?>" style="width: 170px;" /></td>
                <td width="10"></td>
            </tr>
            <tr>
                <td width="150"><?php _e("Additional CSS class", "gd-star-rating"); ?>:</td>
                <td width="200"><input class="jqloadermultis" type="text" name="gdsr_wait_class_multis" id="gdsr_wait_class_multis" value="<?php echo $gdsr_options["wait_class_multis"]; ?>" style="width: 170px;" /></td>
                <td width="10"></td>
            </tr>
        </table>
        <div class="gdsr-table-split"></div>
        <input class="jqloadermultis" type="checkbox" name="gdsr_wait_show_multis" id="gdsr_wait_show_multis"<?php if ($gdsr_options["wait_show_multis"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_wait_show_multis"><?php _e("Hide text and show only animation image.", "gd-star-rating"); ?></label>
    </td>
</tr>
</tbody></table>
