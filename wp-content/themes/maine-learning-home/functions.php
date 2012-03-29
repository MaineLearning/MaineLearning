<?php

//Automatically add new users to a group
function automatic_group_membership( $user_id ) {
 
if( !$user_id ) return false;
groups_accept_invite( $user_id, 7 );
}
add_action( 'bp_core_activated_user', 'automatic_group_membership' );