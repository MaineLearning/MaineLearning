<?php
$wpbookOptions = get_option('wpbookAdminOptions');
 
if (!empty($wpbookOptions)) {
	foreach ($wpbookOptions as $key => $option)
		$wpbookAdminOptions[$key] = $option;
	}
if ($_SERVER['HTTPS'] == "on") { 
	$proto = "https";
} else {
	$proto = "http";
}  

$api_key = $wpbookAdminOptions['fb_api_key'];
$secret  = $wpbookAdminOptions['fb_secret'];
$app_url = $wpbookAdminOpriona['fb_app_url'];
$require_email = $wpbookAdminOptions['require_email']; 
$allow_comments = $wpbookAdminOptions['allow_comments'];
$use_gravatar = $wpbookAdminOptions['use_gravatar'];
$gravatar_rating = $wpbookAdminOptions['gravatar_rating'];
$gravatar_default = $wpbookAdminOptions['gravatar_default'];
 
/*
 * Need another facebook object here as we're out of variable scope  
 * for the theme itself 
 */

/* some users report getting a class not exists error here */  
if(!class_exists('Facebook')) {  
  include_once(WP_PLUGIN_DIR . '/wpbook/includes/client/facebook.php');  
}

Facebook::$CURL_OPTS[CURLOPT_SSL_VERIFYPEER] = false;
Facebook::$CURL_OPTS[CURLOPT_SSL_VERIFYHOST] = 2;

$facebook = new Facebook(array(
                                'appId'  => $api_key,
                                'secret' => $secret,
                                'cookie' => true,
                                ));  
  
$me = $facebook->api('/me'); // get user info

  ?>
<div class="comments-post">
<?php if ($comments) : ?>
	<span class="comments"><b>
    <?php comments_number('no comment yet, be the first!',
                          '1 Comment for this post',
                          '% Comments for this post' );?> </b>
  </span>
  <br>
	<div id="commentlist">
	<?php $comment_class = 'acomment'; ?>
		<?php foreach ($comments as $comment) : ?>
		<div class="<?php echo $comment_class ?>">
		<?php if ($use_gravatar == "true"){ 
		
		echo'<div class="gravatarr">' .get_avatar($comment).'</div>';
	  echo '<div class="gravatarpadding">';	
	  	 	}
    ?>
		<span class="wpbook_comment_date"> <?php comment_date('F jS, Y') ?> at <?php comment_time() ?></span>
		<br/><span class="wpbook_comment_author"><?php comment_author_link(); ?> Said: </span> 
		<span class="wpbook_comment_text">		
				<?php if ($comment->comment_approved == '0') : ?>
					<em>Your comment is awaiting moderation.</em>
				<?php endif; ?>
			 	<?php comment_text() ?></span>
				
			<?php if ($use_gravatar == "true"){ echo '</div>'; } ?>
		</div>
		  <?php /* Changes every other comment to a different class */	
					if ('acomment' == $comment_class){$comment_class = 'bcomment';} else {$comment_class = 'acomment';}
				?>
		<?php endforeach; /* end for each comment */ ?>
	</div><!-- //commentlist -->

 <?php else : // this is displayed if there are no comments so far ?>

  <?php if ('open' == $post-> comment_status) : ?> 
		<!-- If comments are open, but there are no comments. -->
		
	 <?php else : // comments are closed ?>
		<!-- If comments are closed. -->
		<p class="nocomments">Comments are closed.</p>
</div><!-- close COMMENTS-POST -->		
	<?php endif; ?>
<?php endif; ?>

<!-- <?php echo $allow_comments . ' ' . $rs[0]['name']; ?> -->
<?php if (('open' == $post-> comment_status) && ($allow_comments == "true")) : ?>
<strong>  <?php echo $me["name"]; ?>, comment from your Facebook Profile, 

</strong>
	<div id="commentform-container">
  <form action="<?php echo get_bloginfo('url'); ?>/index.php?fb_sig_in_iframe&wpbook=comment-handler" 
      method="post" id="commentform">
  <p>
  <input type="text" name="email" id="email" value="" size="22" tabindex="1" />
		<label for="email"><small> Email Address (
    <?php if($require_email == "true"){ 
      echo("Required. ");
    } ?>
    Will not be published)</small></label></p>
		<p><textarea name="comment" id="comment" cols="50" rows="5" tabindex="2"></textarea></p>
		<p><input name="submit" type="submit" id="submit" tabindex="3" 
      value="Submit Comment" class="inputsubmit" />
		<input type="hidden" name="author" id="author" value="
      <?php echo $me["name"]; ?>" />
		<input type="hidden" name="comment_post_ID" value="<?php echo $id; ?>" />
		<input type="hidden" name="url" id="url" value="
      <?php echo $me["link"]; ?>" />
		<?php do_action('comment_form', $post->ID); ?>
		</form>
	</div>
     <!-- close commentform-container -->
</div><!-- close COMMENTS-POST -->

<?php endif; // end of included comments 
?>
