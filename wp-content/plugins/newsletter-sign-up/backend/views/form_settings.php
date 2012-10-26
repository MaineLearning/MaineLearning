<div class="wrap" id="<?php echo $this->hook; ?>">
    <h2><a href="http://dannyvankooten.com/" target="_blank"><span id="dvk-avatar"></span></a>Newsletter Sign-Up :: Form Settings</h2>
    <div class="postbox-container" style="width:65%;">
        <div class="metabox-holder">	
            <div class="meta-box-sortables">
                <div class="postbox">
                    <div class="handlediv" title="<?php _e('Click to toggle'); ?>"><br></div>
                    <h3 class="hndle" id="nsu-form-settings"><span>Form Settings</span></h3>
                    <div class="inside">
                        
                        <form method="post" action="options.php" id="ns_settings_page">
                            <?php settings_fields('nsu_form_group'); ?>
                        <table class="form-table">
                            <tr valign="top">
                                <td colspan="2"><p>Custome your Sign-up form by providing your own values for the different labels, input fields and buttons of the sign-up form. </p></td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">E-mail label</th>
                                <td><input size="50%" type="text" name="nsu_form[email_label]" value="<?php if (isset($opts['email_label']))
    echo $opts['email_label']; ?>" /></td>
                            </tr>
                            <tr valign="top">
                                 <th scope="row">E-mail default value</th>
                                 <td><input size="50%" type="text" name="nsu_form[email_default_value]" value="<?php if(isset($opts['email_default_value'])) echo $opts['email_default_value']; ?>" /></td>
                            </tr>
                            <tr valign="top" class="name_dependent" <?php if (!isset($opts['mailinglist']['subscribe_with_name']) || $opts['mailinglist']['subscribe_with_name'] != 1)
                echo 'style="display:none;"'; ?>><th scope="row">Name label <span class="ns_small">(if using subscribe with name)</span></th>
                                <td>
                                    <input size="50%" type="text" name="nsu_form[name_label]" value="<?php if (isset($opts['name_label']))
                echo $opts['name_label']; ?>" /><br />
                                    <input type="checkbox" id="name_required" name="nsu_form[name_required]" value="1"<?php if (isset($opts['name_required']) && $opts['name_required'] == '1') {
                echo ' checked="checked"';
            } ?> />
                                    <label for="name_required">Name is a required field?</label>
                                </td>

                            </tr>
                            <tr valign="top" class="name_dependent" <?php if (!isset($opts['mailinglist']['subscribe_with_name']) || $opts['mailinglist']['subscribe_with_name'] != 1)
                echo 'style="display:none;"'; ?>>
                                <th scope="row">Name default value</th>
                                <td>
                                    <input size="50%" type="text" name="nsu_form[name_default_value]" value="<?php if (isset($opts['name_default_value']))
                echo $opts['name_default_value']; ?>" />
                                </td>

                            </tr>
                            <tr valign="top"><th scope="row">Submit button value</th>
                                <td><input size="50%" type="text" name="nsu_form[submit_button]" value="<?php if (isset($opts['submit_button']))
                echo $opts['submit_button']; ?>" /></td>
                            </tr>
                            <tr valign="top"><th scope="row">Text to replace the form with after a successful sign-up</th>
                                <td>
                                    <textarea style="width:100%;" rows="5" cols="50" name="nsu_form[text_after_signup]"><?php if (isset($opts['text_after_signup']))
                echo $opts['text_after_signup']; ?></textarea>
                                    <p><input id="nsu_form_wpautop" name="nsu_form[wpautop]" type="checkbox" value="1" <?php if (isset($opts['wpautop']) && $opts['wpautop'] == 1)
                echo 'checked'; ?> />&nbsp;<label for="nsu_form_wpautop"><?php _e('Automatically add paragraphs'); ?></label></p>
                                </td>
                            </tr>
                            
                            <?php if(isset($opts['mailinglist']['use_api']) && $opts['mailinglist']['use_api'] == 1) { ?>
                            <tr valign="top"><th scope="row">Redirect to this url after signing up <span class="ns_small">(leave empty for no redirect)</span></th>
                                <td><input size="50%" type="text" name="nsu_form[redirect_to]" value="<?php if (isset($opts['redirect_to']))
                echo $opts['redirect_to']; ?>" /></td>
                            </tr>
                            <?php } ?>
                            
                            <tr valign="top"><th scope="row"><label for="ns_load_form_styles">Load some default CSS</label><span class="ns_small">(check this for some default styling of the labels and input fields)</span></th>
                                <td><input type="checkbox" id="ns_load_form_styles" name="nsu_form[load_form_css]" value="1" <?php if (isset($opts['load_form_css']) && $opts['load_form_css'] == 1)
                echo 'CHECKED'; ?> /></td>
                            </tr>
                        </table>
                        <p class="submit">
                            <input type="submit" class="button-primary" style="margin:5px;" value="<?php _e('Save Changes') ?>" />
                        </p>
                        
                        <?php 
                        $tips = array(
                            'You can embed a sign-up form in your posts and pages by 
                                using the shortcode <b><em>[nsu-form]</em></b> or by calling <b><em>&lt;?php if(function_exists(\'nsu_signup_form\')) nsu_signup_form(); ?&gt;</em></b> from your template files.',
                            'Using Newsletter Sign-Up Widget? You can alternatively install <a target="_blank" href="http://wordpress.org/extend/plugins/wysiwyg-widgets/">WYSIWYG Widgets</a> and use the NSU form shortcode <strong>[nsu-form]</strong> to render a sign-up form in your widget area\'s. This allows
                            easier customizing'
                        ); 
                        $random_key = array_rand($tips); 
                        ?>
                        <p class="nsu-tip">Tip: <?php echo $tips[$random_key]; ?></p>

                        </form>
                        <br style="clear:both;" />
                    </div></div></div></div></div></div>
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