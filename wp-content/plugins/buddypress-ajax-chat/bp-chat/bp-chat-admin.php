<?php

function bp_chat_add_admin_menu()
{
        global $bp;

        if ( !$bp->loggedin_user->is_site_admin ) {
            return false;
        }
        $menutitle = __( 'Chat Settings', 'bp-chat' );

        $pagehook = add_submenu_page('bp-general-settings', __('Chat Settings', 'bp-chat' ), $menutitle, 'manage_options', __FILE__, 'bp_chat_admin');
        add_contextual_help( $pagehook, __( '<a href="http://wordpress.org/extend/plugins/buddypress-ajax-chat/">Documentation</a>', 'bp-chat' ) );
}
add_action('admin_menu', 'bp_chat_add_admin_menu', 30);

function bp_chat_add_network_admin_menu()
{
        global $bp;

        if ( !$bp->loggedin_user->is_site_admin ) {
            return false;
        }

        $menutitle = __( 'Chat Settings', 'bp-chat' );

        $pagehook = add_submenu_page('bp-general-settings', __('Chat Settings', 'bp-chat' ), $menutitle, 'manage_options', 'bp-chat-admin', 'bp_chat_network_admin');
        add_contextual_help( $pagehook, __( '<a href="http://wordpress.org/extend/plugins/buddypress-ajax-chat/">Documentation</a>', 'bp-chat' ) );
}
add_action( 'network_admin_menu', 'bp_chat_add_network_admin_menu', 30 );

/**
 * bp_chat_admin()
 *
 * Checks for form submission, saves component settings and outputs admin screen HTML.
 */
function bp_chat_admin() { 
    global $bp, $bbpress_live;

    $updated = false;
		
	/* If the form has been submitted and the admin referrer checks out, save the settings */
    if ( isset( $_POST['submit'] ) && check_admin_referer('chat-settings') ) {
        // Settings form submitted, now save the settings.
		foreach ( (array)$_POST['bp-chat'] as $key => $value ) {
			update_site_option( $key, $value );
		}
		
		$updated = true;
	}
?>
	<div class="wrap">
		<h2><?php _e( 'Chat Settings', 'bp-chat' ) ?></h2>
		<br />
		
		<?php if ( isset($updated) && $updated == true ) : ?><?php echo "<div id='message' class='updated fade'><p>" . __( 'Chat Settings Updated.', 'bp-chat' ) . "</p></div>" ?><?php endif; ?>
			
		<form action="<?php echo site_url() . '/wp-admin/admin.php?page=buddypress-ajax-chat/bp-chat/bp-chat-admin.php' ?>" name="chat-settings-form" id="chat-settings-form" method="post">				

            <h3><?php _e('Chat Options','bp-chat');?></h3>
            <table class="form-table" border="0">
                <tr>
				    <th scope="row"><label for="target_uri"><?php _e( 'Disable shoutbox completely', 'bp-chat' ) ?></label></th>
					<td>
                        <input type="radio" id="bp-chat-setting-disable-shoutbox-chat-yes" name="bp-chat[bp-chat-setting-disable-shoutbox-chat]"<?php if ( (int)get_site_option( 'bp-chat-setting-disable-shoutbox-chat' ) ) : ?> checked="checked"<?php endif; ?> id="bp-chat-setting-disable-shoutbox-chat" value="1" /> <?php _e( 'Yes', 'bp-chat' ) ?> &nbsp;
						<input type="radio" id="bp-chat-setting-disable-shoutbox-chat-no" name="bp-chat[bp-chat-setting-disable-shoutbox-chat]"<?php if ( !(int)get_site_option( 'bp-chat-setting-disable-shoutbox-chat' ) || '' == get_site_option( 'bp-chat-setting-disable-shoutbox-chat' ) ) : ?> checked="checked"<?php endif; ?> id="bp-chat-setting-disable-shoutbox-chat" value="0" /> <?php _e( 'No', 'bp-chat' ) ?>
					</td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="chat_username"><?php _e( 'Chat username', 'bp-chat' ) ?></label></th>
                    <td>
                        <input type="radio" name="bp-chat[bp-chat-setting-username]"<?php if ( get_site_option( 'bp-chat-setting-username' ) == 'display_name' ) : ?> checked="checked"<?php endif; ?> id="bp-chat-setting-username" value="display_name" /> <?php _e( 'Display Name', 'bp-chat' ) ?> &nbsp;
                        <input type="radio" name="bp-chat[bp-chat-setting-username]"<?php if ( !get_site_option( 'bp-chat-setting-username' ) || '' == get_site_option( 'bp-chat-setting-username' ) || get_site_option( 'bp-chat-setting-username' ) == 'user_login' ) : ?> checked="checked"<?php endif; ?> id="bp-chat-setting-username" value="user_login" /> <?php _e( 'User login', 'bp-chat' ) ?>
                    </td>
                </tr>
            </table>
            <br />
            <div id='full=blown-chat-options' >
                <h3><?php _e('Full Blown Chat Options', 'bp-chat');?></h3>
                <table class="form-table" border="0">
                    <tr valign="top">
                        <th scope="row"><label for="target_uri"><?php _e( 'Full blown chat open in new window', 'bp-chat' ) ?></label></th>
					    <td>
                            <input type="radio" name="bp-chat[bp-chat-setting-popout-full-blown-chat]"<?php if ( (int)get_site_option( 'bp-chat-setting-popout-full-blown-chat' ) ) : ?> checked="checked"<?php endif; ?> id="bp-chat-setting-popout-full-blown-chat" value="1" /> <?php _e( 'Yes', 'bp-chat' ) ?> &nbsp;
						    <input type="radio" name="bp-chat[bp-chat-setting-popout-full-blown-chat]"<?php if ( !(int)get_site_option( 'bp-chat-setting-popout-full-blown-chat' ) || '' == get_site_option( 'bp-chat-setting-popout-full-blown-chat' ) ) : ?> checked="checked"<?php endif; ?> id="bp-chat-setting-popout-full-blown-chat" value="0" /> <?php _e( 'No', 'bp-chat' ) ?>
					    </td>
                    </tr>
                </table>
                <br />
            </div>
            <div id='shoutbox-options' style='<?php if ( (int)get_site_option( 'bp-chat-setting-disable-shoutbox-chat' ) == 1 ) : ?>display:none;<?php endif; ?>'>
                <h3><?php _e('Shoutbox Options','bp-chat');?></h3>
                <table class="form-table" border="0">
                    <tr valign="top">
                        <th scope="row"><label for="target_uri"><?php _e( 'Shoutbox always open on login', 'bp-chat' ) ?></label></th>
                        <td>
                            <input type="radio" name="bp-chat[bp-chat-setting-shoutbox-always-open]"<?php if ( (int)get_site_option( 'bp-chat-setting-shoutbox-always-open' ) ) : ?> checked="checked"<?php endif; ?> id="bp-chat-setting-shoutbox-always-open" value="1" /> <?php _e( 'Yes', 'bp-chat' ) ?> &nbsp;
                            <input type="radio" name="bp-chat[bp-chat-setting-shoutbox-always-open]"<?php if ( !(int)get_site_option( 'bp-chat-setting-shoutbox-always-open' ) || '' == get_site_option( 'bp-chat-setting-shoutbox-always-open' ) ) : ?> checked="checked"<?php endif; ?> id="bp-chat-setting-shoutbox-always-open" value="0" /> <?php _e( 'No', 'bp-chat' ) ?>
                        </td>
                    </tr>
                </table>
                <br />
            </div>
			<p class="submit">
				<input type="submit" name="submit" value="<?php _e( 'Save Settings', 'bp-chat' ) ?>"/>
			</p>
			
			<?php 
			/* This is very important, don't leave it out. */
			wp_nonce_field( 'chat-settings' );
			?>
        </form>
        <script>
            jQuery(document).ready(function() {
                jQuery("#bp-chat-setting-disable-shoutbox-chat-yes").click(function() {
                    jQuery('#shoutbox-options').fadeOut('slow');
                });
                jQuery("#bp-chat-setting-disable-shoutbox-chat-no").click(function() {
                    jQuery('#shoutbox-options').fadeIn('slow');
                });
            });
        </script>
	</div>
<?php
}

/**
 * bp_chat_admin()
 *
 * Checks for form submission, saves component settings and outputs admin screen HTML.
 */
function bp_chat_network_admin() { 
    global $bp, $bbpress_live;

    $updated = false;
		
	/* If the form has been submitted and the admin referrer checks out, save the settings */
    if ( isset( $_POST['submit'] ) && check_admin_referer('chat-settings') ) {
        // Settings form submitted, now save the settings.
		foreach ( (array)$_POST['bp-chat'] as $key => $value ) {
			update_site_option( $key, $value );
		}
		
		$updated = true;
	}
?>
	<div class="wrap">
		<h2><?php _e( 'Chat Settings', 'bp-chat' ) ?></h2>
		<br />
		
		<?php if ( isset($updated) && $updated == true ) : ?><?php echo "<div id='message' class='updated fade'><p>" . __( 'Chat Settings Updated.', 'bp-chat' ) . "</p></div>" ?><?php endif; ?>
			
		<form action="<?php echo site_url() . '/wp-admin/admin.php?page=buddypress-ajax-chat/bp-chat/bp-chat-admin.php' ?>" name="chat-settings-form" id="chat-settings-form" method="post">				

            <h3><?php _e('Chat Options','bp-chat');?></h3>
            <table class="form-table" border="0">
                <tr>
				    <th scope="row"><label for="target_uri"><?php _e( 'Disable shoutbox completely', 'bp-chat' ) ?></label></th>
					<td>
                        <input type="radio" id="bp-chat-setting-disable-shoutbox-chat-yes" name="bp-chat[bp-chat-setting-disable-shoutbox-chat]"<?php if ( (int)get_site_option( 'bp-chat-setting-disable-shoutbox-chat' ) ) : ?> checked="checked"<?php endif; ?> id="bp-chat-setting-disable-shoutbox-chat" value="1" /> <?php _e( 'Yes', 'bp-chat' ) ?> &nbsp;
						<input type="radio" id="bp-chat-setting-disable-shoutbox-chat-no" name="bp-chat[bp-chat-setting-disable-shoutbox-chat]"<?php if ( !(int)get_site_option( 'bp-chat-setting-disable-shoutbox-chat' ) || '' == get_site_option( 'bp-chat-setting-disable-shoutbox-chat' ) ) : ?> checked="checked"<?php endif; ?> id="bp-chat-setting-disable-shoutbox-chat" value="0" /> <?php _e( 'No', 'bp-chat' ) ?>
					</td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="chat_username"><?php _e( 'Chat username', 'bp-chat' ) ?></label></th>
                    <td>
                        <input type="radio" name="bp-chat[bp-chat-setting-username]"<?php if ( get_site_option( 'bp-chat-setting-username' ) == 'display_name' ) : ?> checked="checked"<?php endif; ?> id="bp-chat-setting-username" value="display_name" /> <?php _e( 'Display Name', 'bp-chat' ) ?> &nbsp;
                        <input type="radio" name="bp-chat[bp-chat-setting-username]"<?php if ( !get_site_option( 'bp-chat-setting-username' ) || '' == get_site_option( 'bp-chat-setting-username' ) || get_site_option( 'bp-chat-setting-username' ) == 'user_login' ) : ?> checked="checked"<?php endif; ?> id="bp-chat-setting-username" value="user_login" /> <?php _e( 'User login', 'bp-chat' ) ?>
                    </td>
                </tr>
            </table>
            <br />
            <div id='full=blown-chat-options' >
                <h3><?php _e('Full Blown Chat Options', 'bp-chat');?></h3>
                <table class="form-table" border="0">
                    <tr valign="top">
                        <th scope="row"><label for="target_uri"><?php _e( 'Full blown chat open in new window', 'bp-chat' ) ?></label></th>
					    <td>
                            <input type="radio" name="bp-chat[bp-chat-setting-popout-full-blown-chat]"<?php if ( (int)get_site_option( 'bp-chat-setting-popout-full-blown-chat' ) ) : ?> checked="checked"<?php endif; ?> id="bp-chat-setting-popout-full-blown-chat" value="1" /> <?php _e( 'Yes', 'bp-chat' ) ?> &nbsp;
						    <input type="radio" name="bp-chat[bp-chat-setting-popout-full-blown-chat]"<?php if ( !(int)get_site_option( 'bp-chat-setting-popout-full-blown-chat' ) || '' == get_site_option( 'bp-chat-setting-popout-full-blown-chat' ) ) : ?> checked="checked"<?php endif; ?> id="bp-chat-setting-popout-full-blown-chat" value="0" /> <?php _e( 'No', 'bp-chat' ) ?>
					    </td>
                    </tr>
                </table>
                <br />
            </div>
            <div id='shoutbox-options' style='<?php if ( (int)get_site_option( 'bp-chat-setting-disable-shoutbox-chat' ) == 1 ) : ?>display:none;<?php endif; ?>'>
                <h3><?php _e('Shoutbox Options','bp-chat');?></h3>
                <table class="form-table" border="0">
                    <tr valign="top">
                        <th scope="row"><label for="target_uri"><?php _e( 'Shoutbox always open on login', 'bp-chat' ) ?></label></th>
                        <td>
                            <input type="radio" name="bp-chat[bp-chat-setting-shoutbox-always-open]"<?php if ( (int)get_site_option( 'bp-chat-setting-shoutbox-always-open' ) ) : ?> checked="checked"<?php endif; ?> id="bp-chat-setting-shoutbox-always-open" value="1" /> <?php _e( 'Yes', 'bp-chat' ) ?> &nbsp;
                            <input type="radio" name="bp-chat[bp-chat-setting-shoutbox-always-open]"<?php if ( !(int)get_site_option( 'bp-chat-setting-shoutbox-always-open' ) || '' == get_site_option( 'bp-chat-setting-shoutbox-always-open' ) ) : ?> checked="checked"<?php endif; ?> id="bp-chat-setting-shoutbox-always-open" value="0" /> <?php _e( 'No', 'bp-chat' ) ?>
                        </td>
                    </tr>
                </table>
                <br />
            </div>
			<p class="submit">
				<input type="submit" name="submit" value="<?php _e( 'Save Settings', 'bp-chat' ) ?>"/>
			</p>
			
			<?php 
			/* This is very important, don't leave it out. */
			wp_nonce_field( 'chat-settings' );
			?>
        </form>
        <script>
            jQuery(document).ready(function() {
                jQuery("#bp-chat-setting-disable-shoutbox-chat-yes").click(function() {
                    jQuery('#shoutbox-options').fadeOut('slow');
                });
                jQuery("#bp-chat-setting-disable-shoutbox-chat-no").click(function() {
                    jQuery('#shoutbox-options').fadeIn('slow');
                });
            });
        </script>
	</div>
<?php
}
?>
