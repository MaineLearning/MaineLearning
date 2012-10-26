<?php

// If group admin is online then display 
// blinking LED with link to chat window
// that will cycle to group with jquery.
function bpChat_displayGroupChatAdmin()
{
    global $bp;
    global $members_template;
?>
<script>
    jQuery(document).ready(function() {
            jQuery('div#item-nav').before("<div id='bp-group-chat-online'><p id='bp-group-chat-online-text'><?php _e("Admin is offline","bp-chat"); ?></p></div>");
<?php
if ( function_exists ('bp_has_members') ) 
{
    if ( bp_has_members( 'user_id=0&type=online&per_page=' . $instance['max_members'] . '&max=' . $instance['max_members'] . '&populate_extras=0' ) )  
    {
        while ( bp_members() ) : bp_the_member();
            if ($members_template->member->id == $bp->groups->current_group->admins[0]->user_id && 
                (($bp->groups->current_group->admins[0]->is_admin == 1) || ($bp->groups->current_group->admins[0]->is_mod == 1)))
            {
?>
		jQuery('div#bp-group-chat-online').html("<p id='bp-group-chat-online-text'><?php _e( "Admin is online", "bp-chat"); ?></p>");
		if ( (int)get_site_option( 'bp-chat-setting-disable-shoutbox-chat' ) == 0 ) {
			jQuery('#shoutboxwrapper').show('slow');
		}
<?php
            } 
		endwhile; 
    } else {
?>
            jQuery('div#bp-group-chat-online').html("<p id='bp-group-chat-online-text'><?php _e ( "There are no admin users currently online", "bp-chat") ; ?></p>");
<?php
    } 
}
?>
        jQuery('div#bp-group-chat-online').append("<?php echo "<br />"; ?>");
    });
    </script>
<?php
}

add_action('bp_template_content', 'bpChat_displayGroupChatAdmin');
?>
