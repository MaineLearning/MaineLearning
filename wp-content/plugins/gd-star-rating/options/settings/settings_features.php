<table class="form-table"><tbody>
<tr><th scope="row"><?php _e("Cache Support", "gd-star-rating"); ?></th>
    <td>
        <input type="checkbox" name="gdsr_cached_loading" id="gdsr_cached_loading"<?php if ($gdsr_options["cached_loading"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_cached_loading"><?php _e("Activate support for cache plugins and dynamic loading of data.", "gd-star-rating"); ?></label>
        <div class="gdsr-table-split"></div>
        <?php _e("This options is designed for use with cache plugins, but it can be activated regardless of that. Ratings will be loaded dynamically using ajax.", "gd-star-rating"); ?>
    </td>
</tr>
<tr><th scope="row"><?php _e("Plugin Features", "gd-star-rating"); ?></th>
    <td>
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tr>
                <td width="350" valign="top">
                    <input type="checkbox" name="gdsr_timer" id="gdsr_timer"<?php if ($gdsr_options["timer_active"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_timer"><?php _e("Time restriction for rating.", "gd-star-rating"); ?></label>
                    <br />
                    <input type="checkbox" name="gdsr_modactive" id="gdsr_modactive"<?php if ($gdsr_options["moderation_active"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_modactive"><?php _e("Moderation options and handling.", "gd-star-rating"); ?></label>
                    <br />
                    <input type="checkbox" name="gdsr_multis" id="gdsr_multis"<?php if ($gdsr_options["multis_active"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_multis"><?php _e("Multiple rating support.", "gd-star-rating"); ?></label>
                    <br />
                    <input type="checkbox" name="gdsr_thumbs_act" id="gdsr_thumbs_act"<?php if ($gdsr_options["thumbs_active"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_thumbs_act"><?php _e("Thumbs up/down rating support.", "gd-star-rating"); ?></label>
                </td>
                <td width="10"></td>
                <td valign="top">
                    <input type="checkbox" name="gdsr_reviewactive" id="gdsr_reviewactive"<?php if ($gdsr_options["review_active"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_reviewactive"><?php _e("Post And Page Review Rating.", "gd-star-rating"); ?></label>
                    <br />
                    <input type="checkbox" name="gdsr_commentsactive" id="gdsr_commentsactive"<?php if ($gdsr_options["comments_active"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_commentsactive"><?php _e("Comments Rating.", "gd-star-rating"); ?></label>
                    <br />
                    <input type="checkbox" name="gdsr_cmmreviewactive" id="gdsr_cmmreviewactive"<?php if ($gdsr_options["comments_review_active"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_cmmreviewactive"><?php _e("Comments Review Rating.", "gd-star-rating"); ?></label>
                    <br />
                    <input type="checkbox" name="gdsr_cmmintartactive" id="gdsr_cmmintartactive"<?php if ($gdsr_options["comments_integration_articles_active"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_cmmintartactive"><?php _e("Show comments integration ratings on articles panel.", "gd-star-rating"); ?></label>
                </td>
            </tr>
        </table>
        <div class="gdsr-table-split"></div>
        <input type="checkbox" name="gdsr_wp_query_handler" id="gdsr_wp_query_handler"<?php if ($gdsr_options["wp_query_handler"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_wp_query_handler"><?php _e("Expand WP Query with new rating specific sorting and filtering.", "gd-star-rating"); ?></label>
        <div class="gdsr-table-split"></div>
        <input type="checkbox" name="gdsr_gfx_generator_auto" id="gdsr_gfx_generator_auto"<?php if ($gdsr_options["gfx_generator_auto"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_gfx_generator_auto"><?php _e("Use graphics generator to generate and display static stars images.", "gd-star-rating"); ?></label>
        <br />
        <input type="checkbox" name="gdsr_gfx_prevent_leeching" id="gdsr_gfx_prevent_leeching"<?php if ($gdsr_options["gfx_prevent_leeching"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_gfx_prevent_leeching"><?php _e("Prevent outside access to graphics generator. Graphics integrated into RSS is not affected.", "gd-star-rating"); ?></label>
        <div class="gdsr-table-split"></div>
        <?php _e("This leeching prevention will only work if a referer is sent and your server is set up correctly with proper server name variable.", "gd-star-rating"); ?>
    </td>
</tr>
<tr><th scope="row"><?php _e("JS and CSS files", "gd-star-rating"); ?></th>
    <td>
        <input type="checkbox" name="gdsr_external_rating_css" id="gdsr_external_rating_css"<?php if ($gdsr_options["external_rating_css"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_external_rating_css"><?php _e("Link external CSS rating code, uncheck to embed all the CSS into the page.", "gd-star-rating"); ?></label>
        <br />
        <input type="checkbox" name="gdsr_external_css" id="gdsr_external_css"<?php if ($gdsr_options["external_css"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_external_css"><?php _e("Link additional CSS file", "gd-star-rating"); ?>: <strong>'wp-content/gd-star-rating/css/rating.css'</strong></label>
        <div class="gdsr-table-split"></div>
        <input type="checkbox" name="gdsr_include_opacity" id="gdsr_include_opacity"<?php if ($gdsr_options["include_opacity"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_include_opacity"><?php _e("Include opacity for some styles. If activated, it will break CSS 2.1 validation.", "gd-star-rating"); ?></label>
        <div class="gdsr-table-split"></div>
        <input type="checkbox" name="gdsr_css_cache_active" id="gdsr_css_cache_active"<?php if ($gdsr_options["css_cache_active"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_css_cache_active"><?php _e("Auto cache of the main CSS file enabled.", "gd-star-rating"); ?></label>
    </td>
</tr>
<tr><th scope="row"><?php _e("Percentages", "gd-star-rating"); ?></th>
    <td>
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tr>
                <td width="150"><?php _e("No votes value", "gd-star-rating"); ?>:</td>
                <td><input type="text" name="gdsr_no_votes_percentage" id="gdsr_no_votes_percentage" value="<?php echo $gdsr_options["no_votes_percentage"]; ?>" style="width: 170px;" /> %</td>
            </tr>
        </table>
        <div class="gdsr-table-split"></div>
        <?php _e("If you use percentage tag in templates, and there are not votes recorded, default percentage would be 0. Here you can set that to any other value between 0 and 100.", "gd-star-rating"); ?>
    </td>
</tr>
<tr><th scope="row"><?php _e("Charset", "gd-star-rating"); ?></th>
    <td>
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tr>
                <td width="150"><?php _e("Character encoding", "gd-star-rating"); ?>:</td>
                <td><input type="text" name="gdsr_encoding" id="gdsr_encoding" value="<?php echo $gdsr_options["encoding"]; ?>" style="width: 170px;" /></td>
            </tr>
        </table>
        <div class="gdsr-table-split"></div>
        <?php _e("For list of supported charsets visit: ", "gd-star-rating"); ?><a href="http://www.php.net/manual/en/function.htmlentities.php" target="_blank">http://www.php.net/manual/en/function.htmlentities.php</a>
    </td>
</tr>
<tr><th scope="row"><?php _e("Search engines", "gd-star-rating"); ?></th>
    <td>
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tr>
                <td width="150"><?php _e("Display this", "gd-star-rating"); ?>:</td>
                <td>
                    <select name="gdsr_bot_message" style="width: 200px;">
                        <option value="promourl"<?php echo $gdsr_options["bot_message"] == "promourl" ? ' selected="selected"' : ''; ?>><?php _e("Plugin website URL block", "gd-star-rating"); ?></option>
                        <option value="promoname"<?php echo $gdsr_options["bot_message"] == "promoname" ? ' selected="selected"' : ''; ?>><?php _e("Plugin name and description", "gd-star-rating"); ?></option>
                        <option value="nothing"<?php echo $gdsr_options["bot_message"] == "nothing" ? ' selected="selected"' : ''; ?>><?php _e("Don't render rating block", "gd-star-rating"); ?></option>
                        <option value="normal"<?php echo $gdsr_options["bot_message"] == "normal" ? ' selected="selected"' : ''; ?>><?php _e("Render normal rating block", "gd-star-rating"); ?></option>
                    </select>
                </td>
            </tr>
        </table>
        <div class="gdsr-table-split"></div>
        <?php _e("Plugin detects search engine BOT's and to save on the resources, there is no need to render whole rating block since it doesn't mean much to the search engine. You can select what plugin should do for BOT visits. Last option is not reccomended.", "gd-star-rating"); ?>
    </td>
</tr>
<tr><th scope="row"><?php _e("Internet Explorer", "gd-star-rating"); ?></th>
    <td>
        <input type="checkbox" name="gdsr_ieopacityfix" id="gdsr_ieopacityfix"<?php if ($gdsr_options["ie_opacity_fix"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_ieopacityfix"><?php _e("Use IE opacity fix for multi ratings.", "gd-star-rating"); ?></label>
        <br />
        <input type="checkbox" name="gdsr_disable_ie6_check" id="gdsr_disable_ie6_check"<?php if ($gdsr_options["disable_ie6_check"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_disable_ie6_check"><?php _e("Disable checking for IE6 and IE6 specific settings.", "gd-star-rating"); ?></label>
    </td>
</tr>
</tbody></table>
