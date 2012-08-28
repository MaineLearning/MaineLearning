<div class="themes-php">
<div class="wrap">

<h2><?php echo $current_sidebar['name'] ?></h2>
<div id="defaultsidebarspage">
    
    <form action="themes.php?page=customsidebars&p=defaults" method="post">

<div id="poststuff" class="defaultscontainer">

<div  class="postbox closed">
<div class="handlediv" title="Click to toggle"><br></div><h3 class="hndle"><span><?php _e('In a singular post or page','custom-sidebars'); ?></span></h3>
<div class="inside" id="defaultsforposts">
<p><?php _e('To set the sidebar for a single post or page just set it when creating/editing the post.','custom-sidebars'); ?></p>
</div></div>
    
<div  class="postbox closed">
<div class="handlediv" title="Click to toggle"><br></div><h3 class="hndle"><span><?php _e('As the default sidebar for single entries','custom-sidebars'); ?></span></h3>
<div class="inside" id="defaultsforposts">
<p><?php _e('These replacements will be applied to every single post that matches a certain post type or category.','custom-sidebars'); ?></p>
<p><?php _e('The sidebars by categories work in a hierarchycal way, if a post belongs to a parent and a child category it will show the child category sidebars if they are defined, otherwise it will show the parent ones. If no category sidebar for post are defined, the post will show the post post-type sidebar. If none of those sidebars are defined, the theme default sidebar is shown.','custom-sidebars'); ?></p>

        <?php include 'defaults/single_category.php' ?>

        <?php include 'defaults/single_posttype.php' ?>


</div></div>



<div  class="postbox closed">
<div class="handlediv" title="Click to toggle"><br></div><h3 class="hndle"><span><?php _e('As the default sidebars for archives','custom-sidebars'); ?></span></h3>
<div class="inside" id="defaultsforpages">

<p><?php _e('You can define specific sidebars for the different Wordpress pages. Sidebars for lists of posts pages work in the same hierarchycal way than the one for single posts.','custom-sidebars'); ?></p>

<?php include 'defaults/archive_blog.php' ?>
<?php include 'defaults/archive_posttype.php' ?>
<?php include 'defaults/archive_category.php' ?>
<?php include 'defaults/archive_tag.php' ?>
<?php include 'defaults/archive_search.php' ?>
<?php include 'defaults/archive_date.php' ?>
<?php include 'defaults/archive_author.php' ?>


</div>

</div>
        <?php wp_nonce_field( 'cs-set-defaults', '_where_nonce');?>
<div id="submitwhere" class="submit">
    <img src="http://local.wp33/wp-admin/images/wpspin_light.gif" class="ajax-feedback" title="" alt="">
    <input type="submit" class="button-primary" name="update-defaults-pages" value="<?php _e('Save Changes','custom-sidebars'); ?>" />
</div>
</form>

</div>


</div>
</div>
    <script>
    
    jQuery('.defaultsContainer').hide();
    jQuery('#defaultsidebarspage').on('click', '.csh3title', function(){
        jQuery(this).siblings('.defaultsContainer').toggle();
    }).on('click', '.hndle', function(){
        jQuery(this).siblings('.inside').toggle();
    }).on('click', '.handlediv', function(){
        jQuery(this).siblings('.inside').toggle();
    }).on('click', '.selectSidebar', function(){
        jQuery(this).siblings('select').find('option[value=<?php echo $current_sidebar['id'] ?>]').attr('selected', 'selected');
        showsavebutton();
        return false;
    }).on('change', 'select', function(){
        showsavebutton();
        return false;
    }).find('form').on('submit', function(){
        jQuery('#submitwhere .ajax-feedback').css('visibility', 'visible');
        var ajaxdata = {
            action: 'cs-ajax',
            cs_action: 'cs-set-defaults',
            nonce: jQuery('#_where_nonce').val()
        };
        for(i=0; i<this.elements.length; i++){
            ajaxdata[this.elements[i].name] = this.elements[i].value;
        };
        jQuery.post(ajaxurl, ajaxdata, function(res){
            //alert(res.message);
        jQuery('#submitwhere .ajax-feedback').css('visibility', 'hidden');
            jQuery('#submitwhere').slideUp();
        });
        return false;
    })
    
    showsavebutton = function(){
        if(!jQuery('#submitwhere').is(':visible'))
            jQuery('#submitwhere').slideDown();
    }
    
</script>
