<div class="wrap" id="<?php echo $this->hook; ?>">
    <h2><a href="http://dannyvankooten.com/" target="_blank"><span id="dvk-avatar"></span></a>Newsletter Sign-Up :: Checkbox Settings</h2>
    <div class="postbox-container" style="width:65%;">
        <div class="metabox-holder">	
            <div class="meta-box-sortables">

                <div class="postbox">
                    <div class="handlediv" title="<?php _e('Click to toggle'); ?>"><br></div>
                    <h3 class="hndle"><span>Checkbox Settings</span></h3>
                    <div class="inside">
                        <form method="post" action="options.php" id="ns_settings_page">
                            <?php settings_fields('nsu_checkbox_group'); ?>
                            <table class="form-table">
                                <tr valign="top"><th scope="row">Text to show after the checkbox</th>
                                    <td><input size="50%" type="text" name="nsu_checkbox[text]" value="<?php if (isset($opts['text']))
                                echo $opts['text']; ?>" /></td>
                                </tr>
                                <tr valign="top"><th scope="row">Redirect to this url after signing up <span class="ns_small">(leave empty for no redirect)</span></th>
                                    <td><input size="50%" type="text" name="nsu_checkbox[redirect_to]" value="<?php if (isset($opts['redirect_to']))
                                                   echo $opts['redirect_to']; ?>" />
                                        <br />
                                        <p class="nsu-tip">In general, I don't recommend setting a redirect url for the sign-up checkbox. This will cause some serious confusion, since
                                        users expect to be redirected to the post they commented on.</p>
                                    
                                    </td>
                                </tr>
                                <tr valign="top"><th scope="row"><label for="ns_precheck_checkbox">Pre-check the checkbox?</label></th>
                                    <td><input type="checkbox" id="ns_precheck_checkbox" name="nsu_checkbox[precheck]" value="1"<?php
                                               if (isset($opts['precheck']) && $opts['precheck'] == '1') {
                                                   echo ' checked="checked"';
                                               }
                            ?> /></td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><label for="do_css_reset">Do a CSS 'reset' on the checkbox.</label> <span class="ns_small">(check this if checkbox appears in a weird place)</span></th>
                                    <td><input type="checkbox" id="do_css_reset" name="nsu_checkbox[css_reset]" value="1"<?php
                                               if (isset($opts['css_reset']) && $opts['css_reset'] == '1') {
                                                   echo ' checked="checked"';
                                               }
                            ?>  /> </td>
                                </tr>
                                <tr valign="top"><th scope="row">Where to show the sign-up checkbox?</th>
                                    <td>
                                        <input type="checkbox" id="add_to_comment_form" name="nsu_checkbox[add_to_comment_form]" value="1"<?php
                                               if (isset($opts['add_to_comment_form']) && $opts['add_to_comment_form'] == '1') {
                                                   echo ' checked="checked"';
                                               }
                            ?> /> <label for="add_to_comment_form">WordPress comment form</label><br />
                                        <input type="checkbox" id="add_to_reg_form" name="nsu_checkbox[add_to_registration_form]" value="1"<?php
                                               if (isset($opts['add_to_registration_form']) && $opts['add_to_registration_form'] == '1') {
                                                   echo ' checked="checked"';
                                               }
                            ?> /> <label for="add_to_reg_form">WordPress registration form</label><br />
                                        <?php if ($this->bp_active == TRUE) { ?>
                                            <input type="checkbox" id="add_to_bp_form" name="nsu_checkbox[add_to_buddypress_form]" value="1"<?php
                                            if (isset($opts['add_to_buddypress_form']) && $opts['add_to_buddypress_form'] == '1') {
                                                echo ' checked="checked"';
                                            }
                                            ?> /> <label for="add_to_bp_form">BuddyPress registration form</label><br />
    <?php
}
if (defined('MULTISITE') && MULTISITE == TRUE) {
    ?>
                                            <input type="checkbox" id="add_to_ms_form" name="nsu_checkbox[add_to_multisite_form]" value="1"<?php
                                               if (isset($opts['add_to_multisite_form']) && $opts['add_to_multisite_form'] == '1') {
                                                   echo ' checked="checked"';
                                               }
                                               ?> /> <label for="add_to_ms_form">MultiSite registration form</label><br />
<?php } ?>
                                    </td>
                                </tr>
                                <tr valign="top"><th scope="row"><label for="ns_cookie_hide">Hide the checkbox for users who used it to subscribe before?</label><span class="ns_small">(uses a cookie)</span></th>
                                    <td><input type="checkbox" id="ns_cookie_hide" name="nsu_checkbox[cookie_hide]" value="1"<?php
if (isset($opts['cookie_hide']) && $opts['cookie_hide'] == '1') {
    echo ' checked="checked"';
}
?> /></td>
                                </tr>

                            </table>

                            <p class="submit">
                                <input type="submit" class="button-primary" style="margin:5px;" value="<?php _e('Save Changes') ?>" />
                            </p>

                        </form>
                    </div>
                </div>
            </div></div></div></div>
<div class="postbox-container" style="width:33%; float:right; margin-right:1%;">
    <div class="metabox-holder">	
        <div class="meta-box-sortables">						
<?php
$this->donate_box();
$this->latest_posts();
$this->support_box();
?>				
        </div>
    </div>
</div>
</div>
<?php if (isset($this->actions['show_donate_box']) && $this->actions['show_donate_box']) { ?>
    <div id="dvk-donate-box">
        <div id="dvk-donate-box-content">
            <img width="16" height="16" class="dvk-close" src="<?php echo plugins_url('/backend/img/close.png', dirname(__FILE__)); ?>" alt="X">
            <h3>Support me</h3>
            <p>I noticed you've been using <?php echo $this->shortname; ?> for at least 30 days, would you like to show me a token of your appreciation by buying me a beer or tweet about <?php echo $this->shortname; ?>?</p>

            <table>
                <tr>
                    <td>
                        <form id="dvk_donate" target="_blank" action="https://www.paypal.com/cgi-bin/webscr" method="post">
                            <input type="hidden" name="cmd" value="_s-xclick">
                            <input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----">
                            <input type="image" src="https://www.paypalobjects.com/WEBSCR-640-20110306-1/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
                            <img alt="" border="0" src="https://www.paypalobjects.com/WEBSCR-640-20110306-1/nl_NL/i/scr/pixel.gif" width="1" height="1">
                        </form>
                    </td>
                    <td>
                        <a href="http://twitter.com/share" class="twitter-share-button" data-url="<?php echo $this->plugin_url; ?>" data-text="Showing my appreciation to @DannyvanKooten for his awsome #WordPress plugin: <?php echo $this->shortname; ?>" data-count="none">Tweet</a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>
                    </td>
                </tr>
            </table>
            <a class="dvk-dontshow" href="options-general.php?page=<?php echo $this->hook ?>&dontshowpopup=1">(do not show me this pop-up again)</a>
        </div>
    </div>
<?php } ?>