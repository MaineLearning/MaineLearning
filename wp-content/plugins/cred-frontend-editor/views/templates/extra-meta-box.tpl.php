<?php if (!defined('ABSPATH'))  die('Security check'); ?>
        <p class='cred-explain-text'></p>
        <a class='cred-help-link' style='position:absolute;top:5px;right:10px' href='<?php echo $help['css_settings']['link']; ?>' target='<?php echo $help_target; ?>' title="<?php echo $help['css_settings']['text']; ?>"><?php echo $help['css_settings']['text']; ?></a>
        <div id='cred_extra_settings_panel_container' class='cred_extra_settings_panel_container'>
            <div class='cred_extra_css_settings_panel' style='position:relative;'>
            <p>CSS:</p>
            <textarea id='cred-extra-css-editor' name='cred-extra-css-editor' style="position:relative;overflow-y:auto;" class="cred-extra-css-editor<?php if ($css && !empty($css)) echo ' cred-always-open'; ?>"><?php if ($css && !empty($css)) echo $css; ?></textarea>
            </div>
            <br />
            <div class='cred_extra_js_settings_panel' style='position:relative;'>
            <p>JS:</p>
            <textarea id='cred-extra-js-editor' name='cred-extra-js-editor' style="position:relative;overflow-y:auto;" class="cred-extra-js-editor<?php if ($js && !empty($js)) echo ' cred-always-open'; ?>"><?php if ($js && !empty($js)) echo $js; ?></textarea>
            </div>
        </div>
