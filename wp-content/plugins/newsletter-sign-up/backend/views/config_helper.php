<div class="wrap" id="<?php echo $this->hook; ?>">
         
         <?php if(isset($error)) { ?>
            <div id="message" class="notice error"><p>Oops, I couldn't make any sense of that. Are you sure you submitted a form snippet?</p></div>
         <?php } ?>
         
			<h2><a href="http://dannyvankooten.com/" target="_blank"><span id="dvk-avatar"></span></a>Newsletter Sign-Up :: Config Extractor</h2>
			<div class="postbox-container" style="width:65%;">
				<div class="metabox-holder">	
					<div class="meta-box-sortables">
						<div class="postbox">
							<h3 class="hndle"><span>Newsletter Sign-Up :: Config Extractor</span></h3>
							<div class="inside">
                        
                        
                        <?php if(isset($result)) { ?>
                           <table class="form-table">	
                              <tr valign="top">
                                 <th scope="row" style="font-weight:bold;">Form action:</th>
                                 <td><?php echo $form_action; ?></td>
                              </tr>
                              <tr valign="top">
                                 <th scope="row" style="font-weight:bold;">Email identifier:</th>
                                 <td><?php echo $email_identifier; ?></td>
                              </tr>
                              <tr valign="top">
                                 <th scope="row" style="font-weight:bold;">Name identifier:</th>
                                 <td><?php echo $name_identifier; ?></td>
                              </tr>
                              <?php if(isset($additional_data) && count($additional_data) > 0) { ?>
                                 <tr valign="top">
                                    <th scope="row" colspan="2" style="font-weight:bold;">Additional data ( name / value):</th>
                                 </tr>
                                 <?php foreach($additional_data as $data) { ?>
                                    <tr valign="top">
                                       <td><?php echo $data[0]; ?></th>
                                       <td><?php echo $data[1]; ?></td>
                                    </tr>
                                 <?php } ?>
                              <?php } ?>
                           </table>
                           
                           <p>The above settings are there to help you, though they may not be right. Check out <a href="http://dannyvankooten.com/571/configuring-newsletter-sign-up-the-definitive-guide/">this post on my blog</a> for more information on how to manually
                           configure Newsletter Sign-up.</p>
                           <p>The form code below is a stripped down version of your sign-up form which will make it easier for you to extract the right    values. Please also use this form when asking for support.</p>
                           <textarea class="nsu-textarea"><?php echo $clean_form; ?></textarea>

                        <?php } else { ?>
                           <p>This tool was designed to help you extract the right configuration settings to make NSU work properly.</p>
                           <p>Please copy and paste a sign-up form you would normally embed on a HTML page in the textarea below and hit the extract button. The NSU Config Tool will then try to extract the right configuration settings for you. </p>
                             <form method="post" action="" id="ns_settings_page">
                                 <textarea name="form" class="nsu-textarea"></textarea>
                              
                                <p class="submit">
                                    <input type="submit" class="button-primary" style="margin:5px;" value="<?php _e('Extract') ?>" />
                                 </p>
               
                             
                             </form>
                          <?Php } ?>
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
