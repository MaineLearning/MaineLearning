    <div class="panel">
    <table cellpadding="0" cellspacing="0">
    <tr>
    <td>
        <table class="option-panel" cellpadding="0" cellspacing="0">
            <tr>
                <td class="opt-td-control">
                    <h4><a href="javascript:gdsrOptionsSection('sfa')" id="opt-control-sfa" class="gdsr-bulleter-basic"><?php _e("Stars for Articles", "gd-star-rating"); ?></a></h4>
                </td>
                <td class="opt-td-panel">
                    <div id="opt-panel-sfa" style="display: none;">
                        <table cellpadding="0" cellspacing="0">
                        <tr>
                            <td style="width: 80px; height: 29px;">
                                <span class="paneltext"><?php _e("Delete", "gd-star-rating"); ?>:</span>
                            </td>
                            <td style="width: 140px; height: 29px;" align="right">
                                <select id="gdsr_delete_articles" name="gdsr_delete_articles" style="width: 120px;">
                                    <option value="">/</option>
                                    <option value="AV"><?php _e("Visitors", "gd-star-rating"); ?></option>
                                    <option value="AU"><?php _e("Users", "gd-star-rating"); ?></option>
                                    <option value="AA"><?php _e("All", "gd-star-rating"); ?></option>
                                </select>
                            </td>
                            <?php if ($options["review_active"] == 1) { ?>
                            <td style="width: 10px"></td><td style="width: 80px; height: 29px;">
                                <span class="paneltext"><?php _e("Review", "gd-star-rating"); ?>:</span>
                            </td>
                            <td style="width: 140px; height: 29px;" align="right">
                            <select id="gdsr_review_rating" name="gdsr_review_rating" style="width: 50px; text-align: right;">
                                <option value="">/</option>
                                <?php GDSRHelper::render_stars_select_full(-1, $options["review_stars"], 0); ?>
                            </select>.
                            <select id="gdsr_review_rating_decimal" name="gdsr_review_rating_decimal" style="width: 50px; text-align: right;">
                                <option value="">/</option>
                                <?php GDSRHelper::render_stars_select_full(-1, 9, 0); ?>
                            </select>
                            </td>
                            <?php } ?>
                        </tr>
                        <tr>
                            <?php if ($options["moderation_active"] == 1) { ?>
                                <td style="width: 80px; height: 29px;">
                                    <span class="paneltext"><?php _e("Moderation", "gd-star-rating"); ?>:</span>
                                </td>
                                <td style="width: 140px; height: 29px;" align="right">
                                <?php GDSRHelper::render_moderation_combo("gdsr_article_moderation", "/", 120, "", true); ?>
                                </td><td style="width: 10px"></td>
                            <?php } ?>
                            <td style="width: 80px; height: 29px;">
                                <span class="paneltext"><?php _e("Vote Rules", "gd-star-rating"); ?>:</span>
                            </td>
                            <td style="width: 140px; height: 29px;" align="right">
                            <?php GDSRHelper::render_rules_combo("gdsr_article_voterules", "/", 120, "", true); ?>
                            </td>
                        </tr>
                        </table>
                        <table cellpadding="0" cellspacing="0">
                        <tr>
                            <td style="width: 80px; height: 29px;">
                                <span class="paneltext"><?php _e("Restriction", "gd-star-rating"); ?>:</span>
                            </td>
                            <td style="width: 140px; height: 29px;" align="right">
                            <?php GDSRHelper::render_timer_combo("gdsr_timer_type", '/', 120, '', true, 'gdsrTimerChange(\'\')'); ?>
                            </td><td style="width: 10px"></td>
                            <td style="width: 80px; height: 29px;">
                                <div id="gdsr_timer_countdown_text" style="display: none"><span class="paneltext"><?php _e("Countdown", "gd-star-rating"); ?>:</span></div>
                                <div id="gdsr_timer_date_text" style="display: none"><span class="paneltext"><?php _e("Date", "gd-star-rating"); ?>:</span></div>
                            </td>
                            <td style="width: 140px; height: 29px;" align="right">
                                <div id="gdsr_timer_countdown" style="display: none"><input class="regular-text" type="text" value="<?php echo ''; ?>" id="gdsr_timer_countdown_value" name="gdsr_timer_countdown_value" style="width: 35px; text-align: right; padding: 2px;" />
                                <?php GDSRHelper::render_countdown_combo("gdsr_timer_countdown_type", 'H', 70); ?></div>
                                <div id="gdsr_timer_date" style="display: none"><input class="regular-text" type="text" value="<?php echo ''; ?>" id="gdsr_timer_date_value" name="gdsr_timer_date_value" style="width: 110px; padding: 2px;" /></div>
                            </td>
                        </tr>
                        </table>
                    </div>
                </td>
            </tr>
            <tr><td colspan="2" class="opt-td-divider"></td></tr>
            <tr>
                <td class="opt-td-control">
                    <h4><a href="javascript:gdsrOptionsSection('sft')" id="opt-control-sft" class="gdsr-bulleter-basic"><?php _e("Thumbs for Articles", "gd-star-rating"); ?></a></h4>
                </td>
                <td class="opt-td-panel">
                    <div id="opt-panel-sft" style="display: none;">
                        <table cellpadding="0" cellspacing="0">
                        <tr>
                            <td style="width: 80px; height: 29px;">
                                <span class="paneltext"><?php _e("Delete", "gd-star-rating"); ?>:</span>
                            </td>
                            <td style="width: 140px; height: 29px;" align="right">
                                <select id="gdsr_delete_articles_recc" name="gdsr_delete_articles_recc" style="width: 120px;">
                                    <option value="">/</option>
                                    <option value="AV"><?php _e("Visitors", "gd-star-rating"); ?></option>
                                    <option value="AU"><?php _e("Users", "gd-star-rating"); ?></option>
                                    <option value="AA"><?php _e("All", "gd-star-rating"); ?></option>
                                </select>
                            </td>
                        </tr>
                        </table>
                        <table cellpadding="0" cellspacing="0">
                        <tr>
                            <?php if ($options["moderation_active"] == 1) { ?>
                                <td style="width: 80px; height: 29px;">
                                    <span class="paneltext"><?php _e("Moderation", "gd-star-rating"); ?>:</span>
                                </td>
                                <td style="width: 140px; height: 29px;" align="right">
                                <?php GDSRHelper::render_moderation_combo("gdsr_article_moderation_recc", "/", 120, "", true); ?>
                                </td>
                                <td style="width: 10px"></td>
                            <?php } ?>
                            <td style="width: 80px; height: 29px;">
                                <span class="paneltext"><?php _e("Vote Rules", "gd-star-rating"); ?>:</span>
                            </td>
                            <td style="width: 140px; height: 29px;" align="right">
                            <?php GDSRHelper::render_rules_combo("gdsr_article_voterules_recc", "/", 120, "", true); ?>
                            </td>
                        </tr>
                        </table>
                        <table cellpadding="0" cellspacing="0">
                        <tr>
                            <td style="width: 80px; height: 29px;">
                                <span class="paneltext"><?php _e("Restriction", "gd-star-rating"); ?>:</span>
                            </td>
                            <td style="width: 140px; height: 29px;" align="right">
                            <?php GDSRHelper::render_timer_combo("gdsr_timer_type_recc", '/', 120, '', true, 'gdsrTimerChange(\'_recc\')'); ?>
                            </td>
                            <td style="width: 10px"></td>
                            <td style="width: 80px; height: 29px;">
                                <div id="gdsr_timer_countdown_text_recc" style="display: none"><span class="paneltext"><?php _e("Countdown", "gd-star-rating"); ?>:</span></div>
                                <div id="gdsr_timer_date_text_recc" style="display: none"><span class="paneltext"><?php _e("Date", "gd-star-rating"); ?>:</span></div>
                            </td>
                            <td style="width: 140px; height: 29px;" align="right">
                                <div id="gdsr_timer_countdown_recc" style="display: none"><input class="regular-text" type="text" value="<?php echo ''; ?>" id="gdsr_timer_countdown_value_recc" name="gdsr_timer_countdown_value_recc" style="width: 35px; text-align: right; padding: 2px;" />
                                <?php GDSRHelper::render_countdown_combo("gdsr_timer_countdown_type_recc", 'H', 70); ?></div>
                                <div id="gdsr_timer_date_recc" style="display: none"><input class="regular-text" type="text" value="<?php echo ''; ?>" id="gdsr_timer_date_value_recc" name="gdsr_timer_date_value_recc" style="width: 110px; padding: 2px;" /></div>
                            </td>
                        </tr>
                        </table>
                    </div>
                </td>
            </tr>
            <?php if ($options["comments_active"] == 1) { ?>
            <tr><td colspan="2" class="opt-td-divider"></td></tr>
            <tr>
                <td class="opt-td-control">
                    <h4><a href="javascript:gdsrOptionsSection('cfa')" id="opt-control-cfa" class="gdsr-bulleter-basic"><?php _e("Stars for Comments", "gd-star-rating"); ?></a></h4>
                </td>
                <td class="opt-td-panel">
                    <div id="opt-panel-cfa" style="display: none;">
                        <table cellpadding="0" cellspacing="0">
                        <tr>
                            <td style="width: 80px; height: 29px;">
                                <span class="paneltext"><?php _e("Delete", "gd-star-rating"); ?>:</span>
                            </td>
                            <td style="width: 140px; height: 29px;" align="right">
                                <select id="gdsr_delete_comments" name="gdsr_delete_comments" style="margin-top: -4px; width: 120px;">
                                    <option value="">/</option>
                                    <option value="CV"><?php _e("Visitors", "gd-star-rating"); ?></option>
                                    <option value="CU"><?php _e("Users", "gd-star-rating"); ?></option>
                                    <option value="CA"><?php _e("All", "gd-star-rating"); ?></option>
                                </select>
                            </td>
                        </tr>
                        </table>
                        <table cellpadding="0" cellspacing="0">
                        <tr>
                            <?php if ($options["moderation_active"] == 1) { ?>
                            <td style="width: 80px; height: 29px;">
                                <span class="paneltext"><?php _e("Moderation", "gd-star-rating"); ?>:</span>
                            </td>
                            <td style="width: 140px; height: 29px;" align="right">
                            <?php GDSRHelper::render_moderation_combo("gdsr_comments_moderation", "/", 120, "", true); ?>
                            </td><td style="width: 10px"></td>
                            <?php } ?>
                            <td style="width: 80px; height: 29px;">
                                <span class="paneltext"><?php _e("Vote Rules", "gd-star-rating"); ?>:</span>
                            </td>
                            <td style="width: 140px; height: 29px;" align="right">
                            <?php GDSRHelper::render_rules_combo("gdsr_comments_voterules", "/", 120, "", true); ?>
                            </td>
                        </tr>
                        </table>
                    </div>
                </td>
            </tr>
            <tr><td colspan="2" class="opt-td-divider"></td></tr>
            <tr>
                <td class="opt-td-control">
                    <h4><a href="javascript:gdsrOptionsSection('cft')" id="opt-control-cft" class="gdsr-bulleter-basic"><?php _e("Thumbs for Commnets", "gd-star-rating"); ?></a></h4>
                </td>
                <td class="opt-td-panel">
                    <div id="opt-panel-cft" style="display: none;">
                        <table cellpadding="0" cellspacing="0">
                        <tr>
                            <td style="width: 80px; height: 29px;">
                                <span class="paneltext"><?php _e("Delete", "gd-star-rating"); ?>:</span>
                            </td>
                            <td style="width: 140px; height: 29px;" align="right">
                                <select id="gdsr_delete_comments_recc" name="gdsr_delete_comments_recc" style="margin-top: -4px; width: 120px;">
                                    <option value="">/</option>
                                    <option value="CV"><?php _e("Visitors", "gd-star-rating"); ?></option>
                                    <option value="CU"><?php _e("Users", "gd-star-rating"); ?></option>
                                    <option value="CA"><?php _e("All", "gd-star-rating"); ?></option>
                                </select>
                            </td>
                        </tr>
                        </table>
                        <table cellpadding="0" cellspacing="0">
                        <tr>
                            <?php if ($options["moderation_active"] == 1) { ?>
                            <td style="width: 80px; height: 29px;">
                                <span class="paneltext"><?php _e("Moderation", "gd-star-rating"); ?>:</span>
                            </td>
                            <td style="width: 140px; height: 29px;" align="right">
                            <?php GDSRHelper::render_moderation_combo("gdsr_comments_moderation_recc", "/", 120, "", true); ?>
                            </td><td style="width: 10px"></td>
                            <?php } ?>
                            <td style="width: 80px; height: 29px;">
                                <span class="paneltext"><?php _e("Vote Rules", "gd-star-rating"); ?>:</span>
                            </td>
                            <td style="width: 140px; height: 29px;" align="right">
                            <?php GDSRHelper::render_rules_combo("gdsr_comments_voterules_recc", "/", 120, "", true); ?>
                            </td>
                        </tr>
                        </table>
                    </div>
                </td>
            </tr>
            <?php } ?>
            <tr><td colspan="2" class="opt-td-divider"></td></tr>
            <tr>
                <td class="opt-td-control">
                    <h4><a href="javascript:gdsrOptionsSection('cin')" id="opt-control-cin" class="gdsr-bulleter-basic"><?php _e("Comments Integration", "gd-star-rating"); ?></a></h4>
                </td>
                <td class="opt-td-panel">
                    <div id="opt-panel-cin" style="display: none;">
                        <table cellpadding="0" cellspacing="0">
                        <tr>
                            <td style="width: 80px; height: 29px;">
                                <span class="paneltext"><?php _e("Multis", "gd-star-rating"); ?>:</span>
                            </td>
                            <td style="width: 140px; height: 29px;" align="right">
                                <select id="gdsr_integration_active_mur" name="gdsr_integration_active_mur" style="width: 120px">
                                    <option value="">/</option>
                                    <option value="A"><?php _e("Normal activity", "gd-star-rating"); ?></option>
                                    <option value="N"><?php _e("Force hidden", "gd-star-rating"); ?></option>
                                    <option value="I"><?php _e("Inherit from Category", "gd-star-rating"); ?></option>
                                </select>
                            </td>
                            <td style="width: 10px"></td>
                            <td style="width: 80px; height: 29px;">
                                <span class="paneltext"><?php _e("Standard", "gd-star-rating"); ?>:</span>
                            </td>
                            <td style="width: 140px; height: 29px;" align="right">
                                <select id="gdsr_integration_active_std" name="gdsr_integration_active_std" style="width: 120px">
                                    <option value="">/</option>
                                    <option value="A"><?php _e("Normal activity", "gd-star-rating"); ?></option>
                                    <option value="N"><?php _e("Force hidden", "gd-star-rating"); ?></option>
                                    <option value="I"><?php _e("Inherit from Category", "gd-star-rating"); ?></option>
                                </select>
                            </td>
                            <td colspan="3"></td>
                        </tr>
                        </table>
                        <table cellpadding="0" cellspacing="0">
                        <tr>
                            <td style="width: 80px; height: 29px;">
                                <span class="paneltext"><?php _e("Multi Set", "gd-star-rating"); ?>:</span>
                            </td>
                            <td style="width: 230px; height: 29px;" align="right">
                                <select id="gdsr_integration_mur" name="gdsr_integration_mur" style="width: 210px">
                                    <option value="">/</option>
                                    <option value="0"><?php _e("Inherit from Category", "gd-star-rating"); ?></option>
                                    <option value="">------------------------</option>
                                    <?php GDSRHelper::render_styles_select(GDSRDBMulti::get_multis_tinymce(), 0); ?>
                                </select>
                            </td>
                        </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>
    </td>
    <td style="width: 10px;"></td>
    <td class="gdsr-vertical-line">
        <input class="inputbutton" type="submit" name="gdsr_update" value="<?php _e("Update", "gd-star-rating"); ?>" />
    </td>
    </tr>
    </table>
    </div>