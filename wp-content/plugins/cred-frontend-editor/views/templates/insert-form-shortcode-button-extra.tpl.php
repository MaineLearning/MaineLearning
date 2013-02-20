<?php if (!defined('ABSPATH'))  die('Security check'); ?>
<!-- span inline-block as container for IE :p -->
<span class="cred-media-button cred-form-shortcode-button2">

    <a href='javascript:void(0)' class='cred-button cred-form-shortcode-button-button2' title='<?php _e('Insert Forms','wp-cred'); ?>'><?php _e('Insert Forms','wp-cred'); ?></a>

    <div class="cred-popup-box cred-form-shortcodes-box2 stuffbox">

        <div class='cred-popup-heading'>
        <h3><?php _e('Choose which form to insert','wp-cred'); ?></h3>
        <a href='javascript:void(0);' title='<?php _e('Close','wp-cred'); ?>' class='cred-close-button cred-cred-cancel-close'></a>
        </div>

        <div class="cred-popup-inner cred-form-shortcodes-box-inner2">
            <div class='cred-form-shortcode-types-select-container2'>
                <table class='cred-table cred-table-choose-form' cellpadding=0 cellspacing=0>
                    <tr>
                        <td colspan=2>
                            <label class='cred-label'><input type='radio' class='cred-shortcode-container-radio cred-radio-10 cred-post-creation-container2' name='cred-shortcode-container' value='1' /><span class='cred-radio-replace'></span>
                            <span ><?php _e('New content form','wp-cred'); ?></span></label>
                        </td>
                    </tr>
                    <tr>
                        <td colspan=2>
                            <label class='cred-label'><input type='radio' class='cred-shortcode-container-radio cred-radio-10 cred-post-edit-container2' name='cred-shortcode-container' value='2' /><span class='cred-radio-replace'></span>
                            <span ><?php _e('Edit content form','wp-cred'); ?></span></label>
                        </td>
                    </tr>
                    <tr>
                        <td colspan=2>
                            <label class='cred-label'><input type='radio' class='cred-shortcode-container-radio cred-radio-10 cred-post-delete-link-container2' name='cred-shortcode-container' value='3' /><span class='cred-radio-replace'></span>
                            <span ><?php _e('Delete content link','wp-cred'); ?></span></label>
                        </td>
                    </tr>
                    <tr>
                        <td colspan=2>
                            <label class='cred-label'><input type='radio' class='cred-shortcode-container-radio cred-radio-10 cred-post-child-link-container2' name='cred-shortcode-container' value='4' /><span class='cred-radio-replace'></span>
                            <span ><?php _e('Create Child Content link','wp-cred'); ?></span></label>
                        </td>
                    </tr>
                </table>
            </div>

            <div class='cred-shortcodes-container _cred-post-creation-container2' >
                <table class='cred-table' cellpadding=0 cellspacing=0>
                    <tr>
                        <td>
						<label for="cred_form-new-shortcode-select2"><?php _e('Select form','wp-cred'); ?></label>
                        <select name="cred_form-new-shortcode-select" class="cred_form-new-shortcode-select2">
                            <optgroup label="<?php _e('Select form','wp-cred'); ?>">
                                <option value='' disabled selected style='display:none;'><?php _e('Select form','wp-cred'); ?></option>

                                        <?php
                                            foreach ($forms as $form)
                                            {
                                                if ($form->meta->form_type=='new')
                                                {
                                                    echo "<option value='{$form->ID}'>{$form->post_title}</option>";
                                                }
                                            }
                                        ?>
                            </optgroup>
                        </select>
						<a class='cred-help-link' href='<?php echo $help['content_creation_shortcode_settings']['link']; ?>' target='<?php echo $help_target; ?>' title="<?php echo $help['content_creation_shortcode_settings']['text']; ?>" ><?php echo $help['content_creation_shortcode_settings']['text']; ?></a>
                        </td>
                    </tr>
                </table>
            </div>

            <div class='cred-shortcodes-container _cred-post-child-link-container2' >
                <fieldset class='cred-fieldset'>
                <legend><b><?php _e('Child Form Page','wp-cred'); ?></b></legend>
                    <table class='cred-table' cellpadding=0 cellspacing=0>
                      <tr>
                          <td colspan=2><div style='display:inline-block' class='cred_ajax_loader_small cred-form-suggest-child-form-loader2'></div><span class="cred-explain-text"><?php _e('Type some characters from title of the page and a suggestion popup will appear..','wp-cred'); ?></span></td>
                      </tr>
                      <tr>
                          <td ><?php _e('Page:','wp-cred'); ?> &nbsp;</td>
                          <td><input  type='text' class='cred-child-form-page2' name='cred-child-form-page' value=''  placeholder="<?php _e('Type some characters..','wp-cred'); ?>" /></td>
                      </tr>
                      <tr>
                          <td><?php _e('link text:','wp-cred'); ?> &nbsp;</td>
                          <td><input   type='text' class='cred-child-link-text2' name='cred-child-link-text' value='' /></td>
                      </tr>
                    </table>
                </fieldset>
                <fieldset class='cred-fieldset'>
                <legend><b><?php _e('How do you want to set the parent in child form?','wp-cred'); ?></b></legend>
                    <table class='cred-table' cellpadding=0 cellspacing=0>
                        <tr>
                            <td colspan=2>
                            <label class='cred-label'><input type='radio' checked='checked' class='cred-advanced-options-radio cred-radio-10' name='cred-post-child-parent-action' value='current' /><span class='cred-radio-replace'></span>
                            <span ><?php _e('Set the parent according to the currently displayed content','wp-cred'); ?></span></label>
                            </td>
                        </tr>
                        <tr>
                            <td colspan=2>
                            <label class='cred-label'><input type='radio' class='cred-advanced-options-radio cred-radio-10' name='cred-post-child-parent-action' value='other' /><span class='cred-radio-replace'></span>
                            <span ><?php _e('Choose a specific parent','wp-cred'); ?></span></label>
                            <input type='text' class='cred_post_child_parent_id2' name='cred_post_child_parent_id' value='' size='15'  placeholder="<?php _e('Type some characters..','wp-cred'); ?>" />
                            <!--<span style='margin-left:15px;font-style:italic;font-size:10px'><?php //_e('Type..','wp-cred'); ?></span>-->
                            </td>
                        </tr>
                        <tr>
                            <td colspan=2>
                            <label class='cred-label'><input type='radio' class='cred-advanced-options-radio cred-radio-10' name='cred-post-child-parent-action' value='form' /><span class='cred-radio-replace'></span>
                            <span ><?php _e('The form includes the parent selector','wp-cred'); ?></span></label>
                            </td>
                        </tr>
                    </table>
                </fieldset>
                <a href='javascript:void(0);' class='cred-show-hide-advanced'></a>
                <div class='cred-shortcodes-container-advanced cred-post-child-link-container-advanced2'>
                    <fieldset class='cred-fieldset cred-edit-html-fieldset2'>
                    <legend><b><?php _e('HTML attributes for child content link (advanced)','wp-cred'); ?></b></legend>
                            <table class='cred-table' cellpadding=0 cellspacing=0>
								<tr>
									<td ><?php _e('class:','wp-cred'); ?></td>
									<td><input  type='text' class='cred-child-html-class2' name='cred-child-html-class' value='' /></td>
								</tr>
								<tr>
									<td ><?php _e('style:','wp-cred'); ?></td>
									<td><input   type='text' class='cred-child-html-style2' name='cred-child-html-style' value='' /></td>
								</tr>
								<tr>
									<td ><?php _e('target:','wp-cred'); ?></td>
									<td>
									  <select class='cred-child-html-target2' name='cred-child-html-target'>
									  <option value="_self" selected='selected'><?php _e('Current Window','wp-cred'); ?></option>
									  <option value="_top"><?php _e('Parent Window','wp-cred'); ?></option>
									  <option value="_blank"><?php _e('New Window','wp-cred'); ?></option>
									  </select>
								  </td>
								</tr>
								<tr>
									<td><?php _e('more attributes:','wp-cred'); ?></td>
									<td><input  type='text' class='cred-child-html-attributes2' name='cred-child-html-attributes' value='' /></td>
								</tr>
                          </table>
                    </fieldset>
                </div>
            </div>

            <div class='cred-shortcodes-container _cred-post-delete-link-container2' >
                <fieldset class='cred-fieldset'>
                <legend><b><?php _e('HTML attributes for delete link','wp-cred'); ?></b></legend>
                <table class='cred-table' cellpadding=0 cellspacing=0>
                  <tr>
                      <td><?php _e('text:','wp-cred'); ?> &nbsp;</td>
                      <td><input   type='text' class='cred-delete-html-text2' name='cred-delete-html-text' value='Delete %TITLE%' /></td>
                  </tr>
                </table>
                </fieldset>
				<p style="position: relative;">
					<a class='cred-help-link' href='<?php echo $help[ 'content_delete_shortcode_settings' ][ 'link' ]; ?>' target='<?php echo $help_target; ?>' title="<?php echo $help[ 'content_delete_shortcode_settings' ][ 'text' ]; ?>"><?php echo $help[ 'content_delete_shortcode_settings' ][ 'text' ]; ?></a>
                </p>
                <a href='javascript:void(0);' class='cred-show-hide-advanced'></a>
                <div class='cred-shortcodes-container-advanced cred-post-delete-link-container-advanced2'>
                    <fieldset class='cred-fieldset'>
                    <legend><b><?php _e('What to delete','wp-cred'); ?></b></legend>
                    <table class='cred-table' cellpadding=0 cellspacing=0>
                        <tr>
                            <td colspan=2>
                            <label class='cred-label'><input type='radio' checked='checked' class='cred-advanced-options-radio cred-radio-10' name='cred-delete-what-to-delete' value='delete-current-post' /><span class='cred-radio-replace'></span>
                            <span ><?php _e('Delete current post (in Loop)','wp-cred'); ?></span></label>
                            </td>
                        </tr>
                        <tr>
                            <td colspan=2>
                            <label class='cred-label'><input type='radio' class='cred-advanced-options-radio  cred-radio-10' name='cred-delete-what-to-delete' value='delete-other-post' /><span class='cred-radio-replace'></span>
                            <span ><?php _e('Delete another post (post ID)','wp-cred'); ?></span></label>
                            <input type='text' class='cred_post_delete_id2' name='cred_post_delete_id' value='' size='5' />
                            </td>
                        </tr>
                    </table>
                    </fieldset>
                    <fieldset class='cred-fieldset'>
                    <legend><b><?php _e('Delete action','wp-cred'); ?></b></legend>
                    <table class='cred-table' cellpadding=0 cellspacing=0>
                        <tr>
                            <td colspan=2>
                            <label class='cred-label'><input type='radio' checked='checked' class='cred-advanced-options-radio cred-radio-10' name='cred-delete-delete-action' value='trash' /><span class='cred-radio-replace'></span>
                            <span ><?php _e('Trash post','wp-cred'); ?></span></label>
                            </td>
                        </tr>
                        <tr>
                            <td colspan=2>
                            <label class='cred-label'><input type='radio' class='cred-advanced-options-radio cred-radio-10' name='cred-delete-delete-action' value='delete' /><span class='cred-radio-replace'></span>
                            <span ><?php _e('Delete post','wp-cred'); ?></span></label>
                            </td>
                        </tr>
                        <tr>
                            <td colspan=2>
                            <label class='cred-label'><input type='checkbox' class='cred-refresh-after-action cred-checkbox-10' name='cred-refresh-after-action' value='refresh' checked='checked'/><span class='cred-checkbox-replace'></span>
                            <span ><?php _e('Refresh page after deletion','wp-cred'); ?></span></label>
                            </td>
                        </tr>
                    </table>
                    </fieldset>
                    <fieldset class='cred-fieldset'>
                    <legend><b><?php _e('HTML attributes for delete link (advanced)','wp-cred'); ?></b></legend>
                    <table class='cred-table' cellpadding=0 cellspacing=0>
                      <tr>
                          <td ><?php _e('class:','wp-cred'); ?> &nbsp;</td>
                          <td><input  type='text' class='cred-delete-html-class2' name='cred-delete-html-class' value='' /></td>
                      </tr>
                      <tr>
                          <td ><?php _e('style:','wp-cred'); ?> &nbsp;</td>
                          <td><input   type='text' class='cred-delete-html-style2' name='cred-delete-html-style' value='' /></td>
                      </tr>
                    </table>
                    </fieldset>
                </div>
            </div>

            <div class='cred-shortcodes-container _cred-post-edit-container2' >
                <table class='cred-table' cellpadding=0 cellspacing=0>
                    <tr>
                        <td><?php _e('Select form','wp-cred'); ?>
                        <select style='position:relative;' class="cred_form-edit-shortcode-select2" name="cred_form-edit-shortcode-select">
                            <optgroup label="<?php _e('Select form','wp-cred'); ?>">
                                <option value='' disabled selected style='display:none;'><?php _e('Select form','wp-cred'); ?></option>

                                        <?php
                                            foreach ($forms as $form)
                                            {
                                                if ($form->meta->form_type=='edit')
                                                {
                                                    echo "<option class='_cred_cred_{$form->meta->post_type}' value='{$form->ID}'>{$form->post_title}</option>";
                                                }
                                            }
                                        ?>
                            </optgroup>
                        </select>
							<a class='cred-help-link' href='<?php echo $help['content_edit_shortcode_settings']['link']; ?>' target='<?php echo $help_target; ?>' title="<?php echo $help['content_edit_shortcode_settings']['text']; ?>"><?php echo $help['content_edit_shortcode_settings']['text']; ?></a>
                        </td>
                    </tr>
                </table>
                <a href='javascript:void(0);' class='cred-show-hide-advanced'></a>
                <div class='cred-shortcodes-container-advanced cred-post-edit-container-advanced2'>
                    <fieldset class='cred-fieldset'>
                    <legend><b><?php _e('What to edit','wp-cred'); ?></b></legend>
                    <table class='cred-table' cellpadding=0 cellspacing=0>
                        <tr>
                            <td colspan=2>
                            <label class='cred-label'><input type='radio' checked='checked' class='cred-advanced-options-radio cred-radio-10' name='cred-edit-what-to-edit' value='edit-current-post' /><span class='cred-radio-replace'></span>
                            <span ><?php _e('Edit current post (in Loop)','wp-cred'); ?></span></label>
                            </td>
                        </tr>
                        <tr>
                            <td colspan=2>
                            <label class='cred-label'><input type='radio' class='cred-advanced-options-radio cred-radio-10' name='cred-edit-what-to-edit' value='edit-other-post' /><span class='cred-radio-replace'></span>
                            <span ><?php _e('Edit another post','wp-cred'); ?></span></label>
                            <div class='cred-edit-other-post-more2' style='display:inline-block'>
                            <div style='display:inline-block' class='cred_ajax_loader_small cred-form-addtional-loader2'></div>
                            <select class="cred-edit-post-select2" name="cred-edit-post-select">
                                <optgroup label="<?php _e('Select post','wp-cred'); ?>">
                                    <option value='' disabled selected style='display:none;'><?php _e('Select post','wp-cred'); ?></option>
                                </optgroup>
                            </select>
                            </div>
                            </td>
                        </tr>
                    </table>
                    </fieldset>
                    <fieldset class='cred-fieldset'>
                    <legend><b><?php _e('How to display the form','wp-cred'); ?></b></legend>
                    <table class='cred-table' cellpadding=0 cellspacing=0>
                        <tr>
                            <td colspan=2>
                            <label class='cred-label'><input type='radio' checked='checked' class='cred-advanced-options-radio cred-radio-10' name='cred-edit-how-to-display' value='insert-link' /><span class='cred-radio-replace'></span>
                            <span ><?php _e('Insert a link to edit','wp-cred'); ?></span></label>
                            <div class='cred-edit-link-text-container2'>
                                (<?php _e('text:','wp-cred'); ?>
                                <input type='text' class='cred-edit-html-text2' name='cred-edit-html-text' value='Edit %TITLE%' />)
                            </div>
                            </td>
                        </tr>
                        <tr>
                            <td colspan=2>
                            <label class='cred-label'><input type='radio' class='cred-advanced-options-radio cred-radio-10' name='cred-edit-how-to-display' value='insert-form' /><span class='cred-radio-replace'></span>
                            <span ><?php _e('Insert the form itself','wp-cred'); ?></span></label>
                            </td>
                        </tr>
                    </table>
                    </fieldset>
                    <fieldset class='cred-fieldset cred-edit-html-fieldset2'>
                    <legend><b><?php _e('HTML attributes for edit link (advanced)','wp-cred'); ?></b></legend>

						<table class='cred-table' cellpadding=0 cellspacing=0>
							<tr>
								<td ><?php _e('class:','wp-cred'); ?></td>
								<td><input  type='text' class='cred-edit-html-class2' name='cred-edit-html-class' value='' /></td>
							</tr>
							<tr>
								<td ><?php _e('style:','wp-cred'); ?></td>
								<td><input   type='text' class='cred-edit-html-style2' name='cred-edit-html-style' value='' /></td>
							</tr>
							<tr>
								<td ><?php _e('target:','wp-cred'); ?></td>
								<td>
								  <select class='cred-edit-html-target2' name='cred-edit-html-target'>
								  <option value="_self" selected='selected'><?php _e('Current Window','wp-cred'); ?></option>
								  <option value="_top"><?php _e('Parent Window','wp-cred'); ?></option>
								  <option value="_blank"><?php _e('New Window','wp-cred'); ?></option>
								  </select>
							  </td>
							</tr>
                          <tr>
                              <td><?php _e('more attributes:','wp-cred'); ?> &nbsp;</td>
                              <td><input  type='text' class='cred-edit-html-attributes2' name='cred-edit-html-attributes' value='' /></td>
                          </tr>
						</table>
                    </fieldset>
                </div>

            </div>

        </div>

		<p class="cred-buttons-holder">
			<a id="cred-popup-cancel" class="button" href="javascript:void(0);"><?php _e( 'Cancel', 'wp-cred' ); ?></a>
			<a id="cred-insert-shortcode" disabled='disabled' class="button button-primary" href="javascript:void(0);"><?php _e( 'Insert shortcode', 'wp-cred' ); ?></a>
		</p>

    </div>

</span>