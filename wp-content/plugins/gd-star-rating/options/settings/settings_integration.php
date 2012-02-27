<table class="form-table"><tbody>
<tr><th scope="row"><?php _e("Comment Integration", "gd-star-rating"); ?></th>
    <td>
        <input type="checkbox" name="gdsr_cmm_integration_replay_hide_review" id="gdsr_cmm_integration_replay_hide_review"<?php if ($gdsr_options["cmm_integration_replay_hide_review"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_cmm_integration_replay_hide_review"><?php _e("Hide comment integrated rating block if the comment is in reply mode (WP 2.7 or newer only).", "gd-star-rating"); ?></label>
        <br />
        <input type="checkbox" name="gdsr_cmm_integration_prevent_duplicates" id="gdsr_cmm_integration_prevent_duplicates"<?php if ($gdsr_options["cmm_integration_prevent_duplicates"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_cmm_integration_prevent_duplicates"><?php _e("Prevent saving more than one rating per user or visitor.", "gd-star-rating"); ?></label>
        <div class="gdsr-table-split"></div>
        <input type="checkbox" name="gdsr_int_comment_std_zero" id="gdsr_int_comment_std_zero"<?php if ($gdsr_options["int_comment_std_zero"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_int_comment_std_zero"><?php _e("Allow rating zero for standard rating vote from comment integration.", "gd-star-rating"); ?></label>
        <br />
        <input type="checkbox" name="gdsr_int_comment_mur_zero" id="gdsr_int_comment_mur_zero"<?php if ($gdsr_options["int_comment_mur_zero"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_int_comment_mur_zero"><?php _e("Allow rating zero for multis ratings vote from comment integration.", "gd-star-rating"); ?></label>
        <div class="gdsr-table-split"></div>
        <?php _e("Be careful with zero's settings. This can break some other things, and is not recommended or supported.", "gd-star-rating"); ?>
    </td>
</tr>
<tr><th scope="row"><?php _e("Dashboard", "gd-star-rating"); ?></th>
    <td>
        <input type="checkbox" name="gdsr_integrate_dashboard" id="gdsr_integrate_dashboard"<?php if ($gdsr_options["integrate_dashboard"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_integrate_dashboard"><?php _e("Add widgets to the administration dashboard.", "gd-star-rating"); ?></label>
        <div class="gdsr-table-split"></div>
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tr>
                <td style="width: 310px; vertical-align: top;" rowspan="2">
                    <input type="checkbox" name="gdsr_integrate_dashboard_latest" id="gdsr_integrate_dashboard_latest"<?php if ($gdsr_options["integrate_dashboard_latest"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_integrate_dashboard_latest"><?php _e("Add widget with list of latest votes.", "gd-star-rating"); ?></label>
                </td>
                <td style="width: 190px">
                    <?php _e("Votes to display:", "gd-star-rating") ?>
                </td>
                <td>
                    <input type="text" name="gdsr_integrate_dashboard_latest_count" id="gdsr_integrate_dashboard_latest_count" value="<?php echo $gdsr_options["integrate_dashboard_latest_count"]; ?>" style="width: 70px; text-align: right;" />
                </td>
            </tr>
            <tr>
                <td style="width: 190px; vertical-align: top;">
                    <input type="checkbox" name="gdsr_integrate_dashboard_latest_filter_thumb_std" id="gdsr_integrate_dashboard_latest_filter_thumb_std"<?php if ($gdsr_options["integrate_dashboard_latest_filter_thumb_std"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_integrate_dashboard_latest_filter_thumb_std"><?php _e("Show thumbs for posts.", "gd-star-rating"); ?></label>
                    <br />
                    <input type="checkbox" name="gdsr_integrate_dashboard_latest_filter_stars_std" id="gdsr_integrate_dashboard_latest_filter_stars_std"<?php if ($gdsr_options["integrate_dashboard_latest_filter_stars_std"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_integrate_dashboard_latest_filter_stars_std"><?php _e("Show stars for posts.", "gd-star-rating"); ?></label>
                    <br />
                    <input type="checkbox" name="gdsr_integrate_dashboard_latest_filter_stars_mur" id="gdsr_integrate_dashboard_latest_filter_stars_mur"<?php if ($gdsr_options["integrate_dashboard_latest_filter_stars_mur"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_integrate_dashboard_latest_filter_stars_mur"><?php _e("Show multis for posts.", "gd-star-rating"); ?></label>
                </td>
                <td style="vertical-align: top;">
                    <input type="checkbox" name="gdsr_integrate_dashboard_latest_filter_thumb_cmm" id="gdsr_integrate_dashboard_latest_filter_thumb_cmm"<?php if ($gdsr_options["integrate_dashboard_latest_filter_thumb_cmm"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_integrate_dashboard_latest_filter_thumb_cmm"><?php _e("Show thumbs for comments.", "gd-star-rating"); ?></label>
                    <br />
                    <input type="checkbox" name="gdsr_integrate_dashboard_latest_filter_stars_cmm" id="gdsr_integrate_dashboard_latest_filter_stars_cmm"<?php if ($gdsr_options["integrate_dashboard_latest_filter_stars_cmm"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_integrate_dashboard_latest_filter_stars_cmm"><?php _e("Show stars for comments.", "gd-star-rating"); ?></label>
                </td>
            </tr>
        </table>
    </td>
</tr>
<tr><th scope="row"><?php _e("Widgets", "gd-star-rating"); ?></th>
    <td>
        <input type="checkbox" name="gdsr_widget_articles" id="gdsr_widget_articles"<?php if ($gdsr_options["widget_articles"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_widget_articles"><?php _e("GD Star Rating: Post/Page rating widget.", "gd-star-rating"); ?></label>
        <br />
        <input type="checkbox" name="gdsr_widget_top" id="gdsr_widget_top"<?php if ($gdsr_options["widget_top"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_widget_top"><?php _e("GD Blog Rating: Blog average rating.", "gd-star-rating"); ?></label>
        <br />
        <input type="checkbox" name="gdsr_widget_comments" id="gdsr_widget_comments"<?php if ($gdsr_options["widget_comments"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_widget_comments"><?php _e("GD Comments Rating: Per post comments rating.", "gd-star-rating"); ?></label>
        <div class="gdsr-table-split"></div>
        <input type="checkbox" name="gdsr_widgets_hidempty" id="gdsr_widgets_hidempty"<?php if ($gdsr_options["widgets_hidempty"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_widget_comments"><?php _e("Don't render the widget if there are no results to display.", "gd-star-rating"); ?></label>
    </td>
</tr>
<tr><th scope="row"><?php _e("Post Edit", "gd-star-rating"); ?></th>
    <td>
        <input type="checkbox" name="gdsr_integrate_post_edit" id="gdsr_integrate_post_edit"<?php if ($gdsr_options["integrate_post_edit"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_integrate_post_edit"><?php _e("Add standard rating box in the post/page edit sidebar area.", "gd-star-rating"); ?></label>
        <br />
        <input type="checkbox" name="gdsr_integrate_post_edit_mur" id="gdsr_integrate_post_edit_mur"<?php if ($gdsr_options["integrate_post_edit_mur"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_integrate_post_edit_mur"><?php _e("Add multi ratings box in the post/page edit.", "gd-star-rating"); ?></label>
        <br />
        <input type="checkbox" name="gdsr_integrate_tinymce" id="gdsr_integrate_tinymce"<?php if ($gdsr_options["integrate_tinymce"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_integrate_tinymce"><?php _e("Add rating shortcode plugin into tinyMCE visual editor.", "gd-star-rating"); ?></label>
    </td>
</tr>
</tbody></table>
