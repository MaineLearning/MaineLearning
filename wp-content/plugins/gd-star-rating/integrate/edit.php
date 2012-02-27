<input type="hidden" id="gdsr_post_edit" name="gdsr_post_edit" value="edit" />
<h4 class="gdsr-section-title">
    <a id="gdsr-bullet-rvw" href="javascript:gdsrPostEditSection('rvw')" class="gdsr-bulleter-basic gdsr-bullet-closed"><?php _e("Standard Post Review", "gd-star-rating"); ?></a>
</h4>
<div id="gdsr-section-rvw" style="display: none;">
    <table width="260">
        <tr>
            <td style="height: 25px;"><label style="font-size: 12px;" for="gdsr_review"><?php _e("Value", "gd-star-rating"); ?>:</label></td>
            <td align="right" style="height: 25px;" valign="baseline">
            <select style="width: 50px; text-align: right;" name="gdsr_review" id="gdsr_review">
                <option value="-1">/</option>
                <?php GDSRHelper::render_stars_select_full($rating, $gdsr_options["review_stars"], 0); ?>
            </select><span style="vertical-align: bottom;">.</span>
            <select id="gdsr_review_decimal" name="gdsr_review_decimal" style="width: 50px; text-align: right;">
                <option value="-1">/</option>
                <?php GDSRHelper::render_stars_select_full($rating_decimal, 9, 0); ?>
            </select>
            </td>
        </tr>
    </table>
</div>
<h4 class="gdsr-section-title">
    <a id="gdsr-bullet-std" href="javascript:gdsrPostEditSection('std')" class="gdsr-bulleter-basic gdsr-bullet-closed"><?php _e("Post Stars Rating", "gd-star-rating"); ?></a>
</h4>
<div id="gdsr-section-std" style="display: none;">
<table width="260">
    <tr>
        <td style="height: 25px;"><label style="font-size: 12px;"><?php _e("Vote Rule", "gd-star-rating"); ?>:</label></td>
        <td align="right" style="height: 25px;" valign="baseline">
        <?php GDSRHelper::render_rules_combo("gdsr_vote_articles", $vote_rules, 110); ?>
        </td>
        <?php if ($gdsr_options["moderation_active"] == 1) { ?>
        </tr><tr>
        <td style="height: 25px;"><label style="font-size: 12px;"><?php _e("Moderate", "gd-star-rating"); ?>:</label></td>
        <td align="right" style="height: 25px;" valign="baseline">
        <?php GDSRHelper::render_moderation_combo("gdsr_mod_articles", $moderation_rules, 110); ?>
        </td>
        <?php } ?>
    </tr>
</table>
<?php if ($gdsr_options["timer_active"] == 1) { ?>
<table width="260">
    <tr>
        <td style="height: 25px;"><label style="font-size: 12px;" for="gdsr_review"><?php _e("Restriction", "gd-star-rating"); ?>:</label></td>
        <td align="right" style="height: 25px;" valign="baseline">
        <?php GDSRHelper::render_timer_combo("gdsr_timer_type", $timer_restrictions, 110, '', false, 'gdsrTimerChange(\'\')'); ?>
        </td>
    </tr>
</table>
<div id="gdsr_timer_date" style="display: <?php echo $timer_restrictions == "D" ? "block" : "none" ?>">
    <table width="260">
        <tr>
            <td style="height: 25px;"><label style="font-size: 12px;" for="gdsr_review"><?php _e("Date", "gd-star-rating"); ?>:</label></td>
            <td align="right" style="height: 25px;" valign="baseline">
                <input type="text" value="<?php echo $timer_date_value; ?>" id="gdsr_timer_date_value" name="gdsr_timer_date_value" style="width: 100px; padding: 2px;" />
            </td>
        </tr>
    </table>
</div>
<div id="gdsr_timer_countdown" style="display: <?php echo $timer_restrictions == "T" ? "block" : "none" ?>">
    <table width="260">
        <tr>
            <td style="height: 25px;"><label style="font-size: 12px;" for="gdsr_review"><?php _e("Countdown", "gd-star-rating"); ?>:</label></td>
            <td align="right" style="height: 25px;" valign="baseline">
                <input type="text" value="<?php echo $countdown_value; ?>" id="gdsr_timer_countdown_value" name="gdsr_timer_countdown_value" style="width: 35px; text-align: right; padding: 2px;" />
                <?php GDSRHelper::render_countdown_combo("gdsr_timer_countdown_type", $countdown_type, 60); ?>
            </td>
        </tr>
    </table>
</div>
<?php } ?>
</div>
<h4 class="gdsr-section-title">
    <a id="gdsr-bullet-tmb" href="javascript:gdsrPostEditSection('tmb')" class="gdsr-bulleter-basic gdsr-bullet-closed"><?php _e("Post Thumbs Rating", "gd-star-rating"); ?></a>
</h4>
<div id="gdsr-section-tmb" style="display: none;">
<table width="260">
    <tr>
        <td style="height: 25px;"><label style="font-size: 12px;"><?php _e("Vote Rule", "gd-star-rating"); ?>:</label></td>
        <td align="right" style="height: 25px;" valign="baseline">
        <?php GDSRHelper::render_rules_combo("gdsr_recc_vote_articles", $recc_vote_rules, 110); ?>
        </td>
        <?php if ($gdsr_options["moderation_active"] == 1) { ?>
        </tr><tr>
        <td style="height: 25px;"><label style="font-size: 12px;"><?php _e("Moderate", "gd-star-rating"); ?>:</label></td>
        <td align="right" style="height: 25px;" valign="baseline">
        <?php GDSRHelper::render_moderation_combo("gdsr_recc_mod_articles", $recc_moderation_rules, 110); ?>
        </td>
        <?php } ?>
    </tr>
</table>
<?php if ($gdsr_options["timer_active"] == 1) { ?>
<table width="260">
    <tr>
        <td style="height: 25px;"><label style="font-size: 12px;"><?php _e("Restriction", "gd-star-rating"); ?>:</label></td>
        <td align="right" style="height: 25px;" valign="baseline">
        <?php GDSRHelper::render_timer_combo("gdsr_timer_type_recc", $recc_timer_restrictions, 110, '', false, 'gdsrTimerChange(\'_recc\')'); ?>
        </td>
    </tr>
</table>
<div id="gdsr_timer_date_recc" style="display: <?php echo $recc_timer_restrictions == "D" ? "block" : "none" ?>">
    <table width="260">
        <tr>
            <td style="height: 25px;"><label style="font-size: 12px;"><?php _e("Date", "gd-star-rating"); ?>:</label></td>
            <td align="right" style="height: 25px;" valign="baseline">
                <input type="text" value="<?php echo $recc_timer_date_value; ?>" id="gdsr_recc_timer_date_value" name="gdsr_recc_timer_date_value" style="width: 100px; padding: 2px;" />
            </td>
        </tr>
    </table>
</div>
<div id="gdsr_timer_countdown_recc" style="display: <?php echo $recc_timer_restrictions == "T" ? "block" : "none" ?>">
    <table width="260">
        <tr>
            <td style="height: 25px;"><label style="font-size: 12px;"><?php _e("Countdown", "gd-star-rating"); ?>:</label></td>
            <td align="right" style="height: 25px;" valign="baseline">
                <input type="text" value="<?php echo $recc_countdown_value; ?>" id="gdsr_recc_timer_countdown_value" name="gdsr_recc_timer_countdown_value" style="width: 35px; text-align: right; padding: 2px;" />
                <?php GDSRHelper::render_countdown_combo("gdsr_recc_timer_countdown_type", $recc_countdown_type, 60); ?>
            </td>
        </tr>
    </table>
</div>
<?php } ?>
</div>
<?php if ($gdsr_options["comments_active"] == 1) { ?>
<h4 class="gdsr-section-title">
    <a id="gdsr-bullet-cmm" href="javascript:gdsrPostEditSection('cmm')" class="gdsr-bulleter-basic gdsr-bullet-closed"><?php _e("Comment Stars Rating", "gd-star-rating"); ?></a>
</h4>
<div id="gdsr-section-cmm" style="display: none;">
<table width="260">
    <tr>
        <td style="height: 25px;"><label style="font-size: 12px;"><?php _e("Vote Rule", "gd-star-rating"); ?>:</label></td>
        <td align="right" style="height: 25px;" valign="baseline">
        <?php GDSRHelper::render_rules_combo("gdsr_cmm_vote_articles", $cmm_vote_rules, 110); ?>
        </td>
        <?php if ($gdsr_options["moderation_active"] == 1) { ?>
        </tr><tr>
        <td style="height: 25px;"><label style="font-size: 12px;"><?php _e("Moderate", "gd-star-rating"); ?>:</label></td>
        <td align="right" style="height: 25px;" valign="baseline">
        <?php GDSRHelper::render_moderation_combo("gdsr_cmm_mod_articles", $cmm_moderation_rules, 110); ?>
        </td>
        <?php } ?>
    </tr>
</table>
</div>
<h4 class="gdsr-section-title">
    <a id="gdsr-bullet-thc" href="javascript:gdsrPostEditSection('thc')" class="gdsr-bulleter-basic gdsr-bullet-closed"><?php _e("Comment Thumbs Rating", "gd-star-rating"); ?></a>
</h4>
<div id="gdsr-section-thc" style="display: none;">
<table width="260">
    <tr>
        <td style="height: 25px;"><label style="font-size: 12px;"><?php _e("Vote Rule", "gd-star-rating"); ?>:</label></td>
        <td align="right" style="height: 25px;" valign="baseline">
        <?php GDSRHelper::render_rules_combo("gdsr_recc_cmm_vote_articles", $recc_cmm_vote_rules, 110); ?>
        </td>
        <?php if ($gdsr_options["moderation_active"] == 1) { ?>
        </tr><tr>
        <td style="height: 25px;"><label style="font-size: 12px;"><?php _e("Moderate", "gd-star-rating"); ?>:</label></td>
        <td align="right" style="height: 25px;" valign="baseline">
        <?php GDSRHelper::render_moderation_combo("gdsr_recc_cmm_mod_articles", $recc_cmm_moderation_rules, 110); ?>
        </td>
        <?php } ?>
    </tr>
</table>
</div>
<?php } ?>
<!--<h4 class="gdsr-section-title">
    <a id="gdsr-bullet-cir" href="javascript:gdsrPostEditSection('cir')" class="gdsr-bulleter-basic gdsr-bullet-closed"><?php _e("Comment Integration Ratings", "gd-star-rating"); ?></a>
</h4>
<div id="gdsr-section-cir" style="display: none;">

</div>-->