<?php


// Activate featured image (post thumbnail) feature in 2.9+
// http://markjaquith.wordpress.com/2009/12/23/new-in-wordpress-2-9-post-thumbnail-images/
// http://themeshaper.com/forums/topic/creating-a-thumbnail-gallery-in-a-category-archive#post-17351
// http://blog.t3-artwork.info/en/2010/09/die-add_image_size-funktion-anstosen/
// http://codex.wordpress.org/Function_Reference/add_image_size


if ( function_exists( 'add_theme_support' ) )
	add_theme_support( 'post-thumbnails' );

if ( function_exists( 'add_image_size' ) ) { 
	add_image_size( 'category-thumb', 150, 150 );
	add_image_size( 'homepage-thumb', 150, 150, true );
}


//
// Specify formatting options visible in the TinyMCE format dropdown
// http://themeshaper.com/forums/topic/removing-default-classes-from-tinymce
//

function childtheme_tiny_mce_before_init_formats( $init_array ) {
$init_array['theme_advanced_blockformats'] = 'p,h2,h3,h4,h5';
return $init_array;
}
add_filter( 'tiny_mce_before_init', 'childtheme_tiny_mce_before_init_formats' );




// Add custom default avatars
//http://wpmu.org/how-to-add-a-custom-default-avatar-for-buddypress-members-and-groups/


function myavatar_add_default_avatar( $url )
{

return get_stylesheet_directory_uri() .'/images/lm-tree-sky-avatar.png';
}
add_filter( 'bp_core_mysteryman_src', 'myavatar_add_default_avatar' );




function my_default_get_group_avatar($avatar) {

global $bp, $groups_template;

if( strpos($avatar,'group-avatars') ) {

return $avatar;
}

else {
$custom_avatar = get_stylesheet_directory_uri() .'/images/lm-tree-sky-avatar.png';

if($bp->current_action == "")
return '<img width="'.BP_AVATAR_THUMB_WIDTH.'" height="'.BP_AVATAR_THUMB_HEIGHT.'" src="'.$custom_avatar.'" class="avatar" alt="' . attribute_escape( $groups_template->group->name ) . '" />';
else
return '<img width="'.BP_AVATAR_FULL_WIDTH.'" height="'.BP_AVATAR_FULL_HEIGHT.'" src="'.$custom_avatar.'" class="avatar" alt="' . attribute_escape( $groups_template->group->name ) . '" />';
}
}
add_filter( 'bp_get_group_avatar', 'my_default_get_group_avatar');


// Auto-set the uploaded Gravity Forms image to featured
// http://www.gravityhelp.com/forums/topic/auto-setting-the-the_post_thumbnail?replies=6#post-17879
// http://www.gravityhelp.com/forums/topic/support-post-thumbnails

add_filter("gform_post_submission", "set_gform_post_thumbnail", 10, 2);
function set_gform_post_thumbnail($entry, $form){

    //Replace 4 with your actual form id
    if($form["id"] != 4)
        return;

    //getting first image associated with the post
    $post_id = $entry["post_id"];
    $attachments = get_posts(array('numberposts' => '1', 'post_parent' => $post_id, 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => 'ASC'));
    if(sizeof($attachments) == 0)
        return; //no images attached to the post

    //setting first image as the thumbnail for this post
    update_post_meta( $post_id, '_thumbnail_id', $attachments[0]->ID);

}




// Reorder Buddypres tabs
// http://erikshosting.com/wordpress-tips-code/reorder-buddypress-tabs/
//

function erocks_change_bp_tag_position()
{

global $bp;
$bp->bp_nav['profile']['position'] = 10;
$bp->bp_nav['posts']['position'] = 20;
$bp->bp_nav['activity']['position'] = 30;
$bp->bp_nav['blogs']['position'] = 40;
$bp->bp_nav['friends']['position'] = 50;
$bp->bp_nav['messages']['position'] = 60;
$bp->bp_nav['groups']['position'] = 70;
$bp->bp_nav['settings']['position'] = 80;
}
add_action( 'bp_init', 'erocks_change_bp_tag_position', 999 );



// Add custom post types to main loop
// http://justintadlock.com/archives/2010/02/02/showing-custom-post-types-on-your-home-blog-page
//


add_filter( 'pre_get_posts', 'my_get_posts' );

function my_get_posts( $query ) {

	if ( is_home() && false == $query->query_vars['suppress_filters'] )
		$query->set( 'post_type', array( 'post', 'page', 'resources') );

	return $query;
}


?>