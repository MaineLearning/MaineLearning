<?php if (!(is_dir(STARRATING_CACHE_PATH) && is_writable(STARRATING_CACHE_PATH))) { ?>
    <?php if (!$extra_folders) { ?>
        <div id="gdnotice" class="gderror">
            <?php if ($safe_mode) { ?><p><?php _e("PHP is working in the safe mode. Plugin can't create folders for caching. You need to do it manually if you want to use cache.", "gd-star-rating"); ?></p><?php } ?>
            <p><?php _e("For cache to work, plugin must be able to access cache folder. Plugin has tried to create folders needed and failed. Until you resolved this issue cache feature can't be used.", "gd-star-rating"); ?></p>
            <p><?php _e("Either make <strong>wp-content</strong> folder writeable or create <strong>gd-star-rating</strong> folder in <strong>wp-content</strong> and make it writeable. Use 0755 chmod setting.", "gd-star-rating"); ?></p>
        </div>
    <?php } ?>
<?php } ?>
<table class="form-table"><tbody>
<tr><th scope="row"><?php _e("Cache", "gd-star-rating"); ?></th>
    <td>
        <input type="checkbox" name="gdsr_cache_active" id="gdsr_cache_active"<?php if ($gdsr_options["cache_active"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_cache_active"><?php _e("Cache enabled.", "gd-star-rating"); ?></label>
        <br />
        <input type="checkbox" name="gdsr_cache_forced" id="gdsr_cache_forced"<?php if ($gdsr_options["cache_forced"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_cache_forced"><?php _e("Force using of cache. This is usefull in some rare cases when PHP can't report writeability correct.", "gd-star-rating"); ?></label>
        <br />
        <input type="checkbox" name="gdsr_cache_cleanup_auto" id="gdsr_cache_cleanup_auto"<?php if ($gdsr_options["cache_cleanup_auto"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_cache_cleanup_auto"><?php _e("Enable auto cache cleanup.", "gd-star-rating"); ?></label>
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tr>
                <td width="200"><?php _e("Cleanup cache every", "gd-star-rating"); ?>:</td>
                <td><input type="text" name="gdsr_cache_cleanup_days" id="gdsr_cache_cleanup_days" value="<?php echo $gdsr_options["cache_cleanup_days"]; ?>" style="width: 70px; text-align: right;" /> [days]</td>
            </tr>
        </table>
    </td>
</tr>
<tr><th scope="row"><?php _e("Inline Debug Info", "gd-star-rating"); ?></th>
    <td>
        <input type="checkbox" name="gdsr_debug_inline" id="gdsr_debug_inline"<?php if ($gdsr_options["debug_inline"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_debug_inline"><?php _e("Add small block with essential debug info into each rating block.", "gd-star-rating"); ?></label>
        <div class="gdsr-table-split"></div>
        <strong><?php _e("Important", "gd-star-rating"); ?>: </strong><?php _e("I strongly recommend leaving this option active. It doesn't disrupt the page, it's hidden and very small.", "gd-star-rating"); ?>
    </td>
</tr>
<tr><th scope="row"><?php _e("Log Into File", "gd-star-rating"); ?></th>
    <td>
        <input type="checkbox" name="gdsr_debug_active" id="gdsr_debug_active"<?php if ($gdsr_options["debug_active"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_debug_active"><?php _e("Log into file various debug information.", "gd-star-rating"); ?></label>
        <br />
        <input type="checkbox" name="gdsr_debug_wpquery" id="gdsr_debug_wpquery"<?php if ($gdsr_options["debug_wpquery"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_debug_wpquery"><?php _e("Save into debug file each WP Query executed.", "gd-star-rating"); ?></label>
        <div class="gdsr-table-split"></div>
        <strong><?php _e("Important", "gd-star-rating"); ?>: </strong><?php echo sprintf(__("Plugin must have write access to a text file. Path to this file needs to be set in %s file.", "gd-star-rating"), '<em style="color:red">config.php</em>'); ?>
    </td>
</tr>
<tr><th scope="row"><?php _e("Trend Calculations", "gd-star-rating"); ?></th>
    <td>
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tr>
                <td width="360"><?php _e("Calculate for last number of days", "gd-star-rating"); ?>:</td>
                <td><input type="text" name="gdsr_trend_last" id="gdsr_trend_last" value="<?php echo $gdsr_options["trend_last"]; ?>" style="width: 70px; text-align: right;" /></td>
            </tr>
            <tr>
                <td width="360"><?php _e("Calculate over how many days before", "gd-star-rating"); ?>:</td>
                <td><input type="text" name="gdsr_trend_over" id="gdsr_trend_over" value="<?php echo $gdsr_options["trend_over"]; ?>" style="width: 70px; text-align: right;" /> [0 to include complete history]</td>
            </tr>
        </table>
    </td>
</tr>
<tr><th scope="row"><?php _e("Bayesian Estimate Mean", "gd-star-rating"); ?></th>
    <td>
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tr>
                <td width="360"><?php _e("Minimum votes required", "gd-star-rating"); ?>:</td>
                <td><input type="text" name="gdsr_bayesian_minimal" id="gdsr_bayesian_minimal" value="<?php echo $gdsr_options["bayesian_minimal"]; ?>" style="width: 70px; text-align: right;" /></td>
            </tr>
            <tr>
                <td width="360"><?php _e("Mean vote rating across the whole report", "gd-star-rating"); ?>:</td>
                <td><input type="text" name="gdsr_bayesian_mean" id="gdsr_bayesian_mean" value="<?php echo $gdsr_options["bayesian_mean"]; ?>" style="width: 70px; text-align: right;" /> [%]</td>
            </tr>
        </table>
    </td>
</tr>
