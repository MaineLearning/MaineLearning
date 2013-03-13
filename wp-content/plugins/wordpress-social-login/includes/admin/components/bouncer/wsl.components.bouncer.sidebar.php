<?php
/*!
* WordPress Social Login
*
* http://hybridauth.sourceforge.net/wsl/index.html | http://github.com/hybridauth/WordPress-Social-Login
*    (c) 2011-2013 Mohamed Mrassi and contributors | http://wordpress.org/extend/plugins/wordpress-social-login/
*/

/**
* Widget Customization
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit; 

// --------------------------------------------------------------------

function wsl_component_bouncer_sidebar()
{
	// HOOKABLE:
	do_action( "wsl_component_bouncer_sidebar_start" );
?>
<div class="postbox " id="linksubmitdiv"> 
	<div class="inside">
		<div id="submitlink" class="submitbox"> 
			<h3 style="cursor: default;"><?php _wsl_e("What's This?", 'wordpress-social-login') ?></h3>
			<div id="minor-publishing">  
				<div id="misc-publishing-actions"> 
					<div style="padding:20px;padding-top:0px;">
						<h4 style="cursor: default;border-bottom:1px solid #ccc;font-size: 13px;"><?php _wsl_e("Hey, meet our friend, the Bouncer", 'wordpress-social-login') ?></h4>

						<p style="margin:10px;font-size: 13px;" align="justify"> 
						<?php _wsl_e('Ever been in trouble with one of <a href="http://www.flickr.com/search/?q=bouncer+doorman&z=e" target="_blank">these guys</a>? Well, this module have more or less the same role, and he will try his best to piss your users off until they meet your requirements.', 'wordpress-social-login') ?>
						</p>

						<p style="margin:10px;font-size: 13px;" align="justify"> 
						<?php _wsl_e("This feature is most suited for small businesses and folks running a closed-door blog between friends or coworkers.", 'wordpress-social-login') ?>
						</p>

						<h4 style="cursor: default;border-bottom:1px solid #ccc;"><?php _wsl_e("Available settings", 'wordpress-social-login') ?></h4>
						
						<ul style="margin:30px;margin-top:0px;margin-bottom:0px;">
							<li><?php _wsl_e("Enable/Disable Registration", 'wordpress-social-login') ?></li>
							<li><?php _wsl_e("Enable/Disable Authentication", 'wordpress-social-login') ?></li>
							<li><?php _wsl_e("Profile Completion", 'wordpress-social-login') ?></li>
							<li><?php _wsl_e("Users moderation", 'wordpress-social-login') ?></li>
							<li><?php _wsl_e("Users roles", 'wordpress-social-login') ?></li> 
							<li><?php _wsl_e("Restrictions (by emails, domains, profiles urls)", 'wordpress-social-login') ?></li> 
						</ul>

						<h4 style="cursor: default;border-bottom:1px solid #ccc;"><?php _wsl_e("IMPORTANT!", 'wordpress-social-login') ?></h4>

						<p style="margin:10px;" align="justify"> 
							<?php _wsl_e("All the settings on this page without exception are only valid for users authenticating through <b>WordPress Social Login Widget", 'wordpress-social-login') ?></b>.
						</p> 
						<p style="margin:10px;" align="justify"> 
						<?php _wsl_e("Users authenticating through the regulars Wordpress Login and Register pages with their usernames and passwords WILL NOT be affected.", 'wordpress-social-login') ?>
						</p>
					</div> 
				</div> 
			</div> 
		</div>
	</div>
</div> 
<?php
	// HOOKABLE: 
	do_action( "wsl_component_bouncer_sidebar_end" );
}

// --------------------------------------------------------------------	
