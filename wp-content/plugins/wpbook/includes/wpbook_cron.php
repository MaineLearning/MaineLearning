<?php
/*
 wp_cron functions for WPBook - for importing comments from FB wall  
  Note: These functions draw heavily on code from:
    http://wordpress.org/extend/plugins/wordbooker by Steve Atty
*/

/*  
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/*
 This function gathers posts published in last X days (user configurable), 
 checks the wp_post_meta table to see if those posts have corresponding
 metadata indicating they've been posted to Facebook walls or pages, and then
 polls the corresponding stream_ids in Facebook for comments. If comments are
 found newer than last time stamp, they are processed. 
*/ 
function wpbook_import_comments() {
  global  $wpdb, $table_prefix;
  if(!class_exists('Facebook')) {  
    include_once(WP_PLUGIN_DIR . '/wpbook/includes/client/facebook.php');  
  }
  $wpbookOptions = get_option('wpbookAdminOptions');
  if (!empty($wpbookOptions)) {
    foreach ($wpbookOptions as $key => $option)
    $wpbookAdminOptions[$key] = $option;
	}
  
  if($wpbookOptions['wpbook_enable_debug'] == "true")
    define ('WPBOOKDEBUG',true);
  else
    define ('WPBOOKDEBUG',false);
  define ('WPBOOK_COMMENT_METHOD','comment');

  $debug_file= WP_PLUGIN_DIR .'/wpbook/wpbook_debug.txt';
  if(WPBOOKDEBUG) {
    $fp = @fopen($debug_file, 'a');
    if(($fp) && (filesize($debug_file) > 500 * 1024)) {  // 500k max to file
      fclose($fp);
      $fp = @fopen($debug_file,'w+'); // start over with a new file
    }
    if(!$fp) 
      define('WPBOOKDEBUG',false); // stop trying
    $debug_string=date("Y-m-d H:i:s",time())." : Cron Running\n";
    if(is_writeable($debug_file)) {
       fwrite($fp, $debug_string);
    } else {
      fclose($fp);
      define ('WPBOOKDEBUG',false); 
    }
  }

  $api_key = $wpbookAdminOptions['fb_api_key'];
  $secret  = $wpbookAdminOptions['fb_secret'];
  $fb_user = $wpbookAdminOptions['fb_admin_target'];
  $access_token = get_option('wpbook_user_access_token','');

  if($wpbookOptions['wpbook_disable_sslverify'] == "true") {
    Facebook::$CURL_OPTS[CURLOPT_SSL_VERIFYPEER] = false;
    Facebook::$CURL_OPTS[CURLOPT_SSL_VERIFYHOST] = 2;
  }
  
  $facebook = new Facebook(array(
                                 'appId'  => $api_key,
                                 'secret' => $secret,
                                 )
                           );
  
  if(WPBOOKDEBUG) {
    $fp = @fopen($debug_file, 'a');
    $debug_string=date("Y-m-d H:i:s",time())." : Access token is ". $access_token ." \n";
    fwrite($fp, $debug_string);
  }
  
  if (!($wpbookAdminOptions['import_comments'])) {
    if(WPBOOKDEBUG) {
      $fp = @fopen($debug_file, 'a');
      $debug_string=date("Y-m-d H:i:s",time())." : import_comments was false - nothing to do\n";
      fwrite($fp, $debug_string);
    }
    return;
  }
  // validate token
  try {
	$facebook->setAccessToken($access_token);
  } catch (FacebookApiException $e) {
	if(WPBOOKDEBUG) {
		$wpbook_message = 'Caught exception setting access token: ' .  $e->getMessage() .'Error code: '. $e->getCode();  
		$fp = @fopen($debug_file, 'a');
		$debug_string=date("Y-m-d H:i:s",time())." :". $wpbook_message  ."\n";
      fwrite($fp, $debug_string);
	} // end if debug
  }  // end try-catch

  try {
	$facebook->api('/me','GET');
  } catch (FacebookApiException $e) {
	if(WPBOOKDEBUG) {
		$wpbook_message = 'Caught exception with access token: ' .  $e->getMessage() .'Error code: '. $e->getCode();  
		$fp = @fopen($debug_file, 'a');
		$debug_string=date("Y-m-d H:i:s",time())." :". $wpbook_message  ."\n";
      fwrite($fp, $debug_string);
	} // end if debug
	update_option('wpbook_user_access_token','invalid');
	die(); 
  }

  
  if ($wpbookAdminOptions['approve_imported_comments'] == 1) {
    $wpbook_comment_approval = 1;
  } else {
    $wpbook_comment_approval = 0;
  }
  
  // use an email address set by the admin for WPBook comments, this way the 
  // blog admin can create a gravatar specific to that email if they use gravatars
  
  if($wpbookAdminOptions['imported_comments_email'] == '') {
    $wpbook_comment_email = 'facebook@openparenthesis.org';
  } else {
    $wpbook_comment_email = $wpbookAdminOptions['imported_comments_email']; 
  }
  
  // need to get posts in last X days which have postmeta for streamid
  $num_days = $wpbookAdminOptions['num_days_import'];
  if ($num_days == '') { $num_days = 7; }
  $today = date("Y-m-d H:i:s");
  $daysago = date("Y-m-d H:i:s",strtotime(date('Y-m-j H:i:s')) - ($num_days * 24 * 60 * 60)); 	
  $sql="Select ID FROM $wpdb->posts WHERE post_date BETWEEN '". $daysago . "' AND '". $today ."'";
  if(WPBOOKDEBUG) {
    $fp = @fopen($debug_file, 'a');
    $debug_string=date("Y-m-d H:i:s",time())." : Getting posts, SQL was $sql \n";
    fwrite($fp, $debug_string);
  }
  $wpdb->flush();
  $wordpress_post_ids = $wpdb->get_col($sql); // only need the post ids so we can use get_column
  if ($wordpress_post_ids) {
    if(WPBOOKDEBUG) {
      $fp = @fopen($debug_file, 'a');
      $debug_string=date("Y-m-d H:i:s",time())." : How many posts to consider? $wpdb->num_rows \n";
      fwrite($fp, $debug_string);
    }
    foreach($wordpress_post_ids as $wordpress_post_id) {
      // now lets go find out which of those rows we need to examine
      $my_sql = "Select post_id,meta_key,meta_value from $wpdb->postmeta WHERE meta_key LIKE '%_wpbook_%' AND post_id = '$wordpress_post_id'"; 
      $wpdb->flush();
      $my_meta_posts = $wpdb->get_results($my_sql);
      if($wpdb->num_rows>0) {
        if(WPBOOKDEBUG) {
          $fp = @fopen($debug_file, 'a');
          $debug_string=date("Y-m-d H:i:s",time())." : How many meta_posts found? $wpdb->num_rows \n";
          fwrite($fp, $debug_string);
        }
        foreach($my_meta_posts as $mp) {
          if(WPBOOKDEBUG) {
            $fp = @fopen($debug_file, 'a');
            $debug_string=date("Y-m-d H:i:s",time())." : Examining a meta_post, post ID is $mp->post_id, meta key = $mp->meta_key \n";
            fwrite($fp, $debug_string);
          }
          if(($mp->meta_key == '_wpbook_user_stream_time') || ($mp->meta_key == '_wpbook_page_stream_time') || ($mp->meta_key == '_wpbook_group_stream_time')) {
            if(WPBOOKDEBUG) {
              $fp = @fopen($debug_file, 'a');
              $debug_string=date("Y-m-d H:i:s",time())." : Skipping meta key $mp->meta_key \n";
              fwrite($fp, $debug_string);
            }
            continue; // don't need to process these - go on to the next
          }
          if(($mp->meta_key == '_wpbook_user_stream_id') || ($mp->meta_key == '_wpbook_page_stream_id') || ($mp->meta_key == '_wpbook_group_stream_id')) {
            if($mp->meta_key == '_wpbook_user_stream_id') {
              $my_timestamp_results = $wpdb->get_row("Select meta_value from $wpdb->postmeta WHERE meta_key LIKE '%_wpbook_user_stream_time%' AND post_id = '$wordpress_post_id'",ARRAY_A);
            } 
            if($mp->meta_key == '_wpbook_group_stream_id') {
              $my_timestamp_results = $wpdb->get_row("Select meta_value from $wpdb->postmeta WHERE meta_key LIKE '%_wpbook_group_stream_time%' AND post_id = '$wordpress_post_id'",ARRAY_A);
            } 
            if ($mp->meta_key == '_wpbook_page_stream_id') {
              $my_timestamp_results = $wpdb->get_row("Select meta_value from $wpdb->postmeta WHERE meta_key LIKE '%_wpbook_page_stream_time%' AND post_id = '$wordpress_post_id'",ARRAY_A);
            }
            $my_timestamp = $my_timestamp_results['meta_value'];
            
            /* 
             * now we fetch comments since that timestamp, using FQL
             * which I've found the most reliable
             */
            $pos = strpos($mp->meta_value, '_');
            if ($pos === false) // this accounts for notes, which are objects not posts
              $fbsql="SELECT time,text,fromid,xid,post_id FROM comment WHERE object_id='$mp->meta_value' AND time > '$my_timestamp' ORDER BY time ASC";
            else
              $fbsql="SELECT time,text,fromid,xid,post_id FROM comment WHERE post_id='$mp->meta_value' AND time > '$my_timestamp' ORDER BY time ASC";
            
            if(WPBOOKDEBUG) {
              $fp = @fopen($debug_file, 'a');
              $debug_string=date("Y-m-d H:i:s",time())." : FBcomments, fbsql is $fbsql \n";
              fwrite($fp, $debug_string);
            }
            $params = array(
                            'method' => 'fql.query',
                            'access_token' => $access_token,
                            'query' => $fbsql,
                            );
            try {
              $fbcommentslist=$facebook->api($params);
            } catch (FacebookApiException $e) {
              if(WPBOOKDEBUG) {
                $fp = @fopen($debug_file, 'a');
                $debug_string=date("Y-m-d H:i:s",time())." : Caught exception: ". $e->getMessage() ." Error code: ". $e->getCode() ."\n";
                fwrite($fp, $debug_string);
              }
              return;
            }
            if(WPBOOKDEBUG) {
              $fp = @fopen($debug_file, 'a');
              $debug_string=date("Y-m-d H:i:s",time())." : FBcommentslist is ". print_r($fbcommentslist,true) . "\n";
              fwrite($fp, $debug_string);
            }
          } //end of comment method
          
          // now we act on the fetched comments
          if (is_array($fbcommentslist)) {
            if(WPBOOKDEBUG) {
              $fp = @fopen($debug_file, 'a');
              $debug_string=date("Y-m-d H:i:s",time())." : Number of comments for this post- " . count($fbcommentslist) . " \n";
              $debug_string .= print_r($fbcommentslist);
              fwrite($fp, $debug_string);
            }
            foreach ($fbcommentslist as $comment) {
              //sleep(30); // maybe posting these too quickly?
              if(WPBOOKDEBUG) {
                $fp = @fopen($debug_file, 'a');
                $debug_string=date("Y-m-d H:i:s",time())." : Inside comment, comment[time] is $comment[time], comment[fromid] is $comment[fromid] \n";
                fwrite($fp, $debug_string);
              }
              $fbsql = "SELECT name,url FROM profile WHERE id = '$comment[fromid]'";
              if(WPBOOKDEBUG) {
                $fp = @fopen($debug_file, 'a');
                $debug_string=date("Y-m-d H:i:s",time())." : Getting author info, fbsql is $fbsql \n";
                fwrite($fp, $debug_string);
              }
              $params = array(
                              'method' => 'fql.query',
                              'access_token' => $access_token,
                              'query' => $fbsql,
                              );
              try {
                $fbuserinfo=$facebook->api($params);
              } catch (FacebookApiException $e) {
                $fp = @fopen($debug_file, 'a');
                $debug_string=date("Y-m-d H:i:s",time())." : Caught exception getting info about comment author: ". $e->getMessage() ." Error code: ". $e->getCode() ."\n";
                fwrite($fp, $debug_string);
                return;
              }
              if (is_array($fbuserinfo)) {
                if(WPBOOKDEBUG) {
                  $fp = @fopen($debug_file, 'a');
                  $debug_string=date("Y-m-d H:i:s",time())." : fbuserinfo is an array, count is " . count($fbuserinfo) . "\n";
                  fwrite($fp, $debug_string);
                }
                foreach ($fbuserinfo as $fb_user) {
                  if($fb_user[url] == '') {
                    // sometimes url doesn't come - not sure why
                    // do I need to worry about pages here? I think they always pass a url
                    $fb_user[url] = 'http://www.facebook.com/profile.php?id=' . $comment[fromid];
                  }
                  if(WPBOOKDEBUG) {
                    $fp = @fopen($debug_file, 'a');
                    $debug_string=date("Y-m-d H:i:s",time())." : In fb_user, name is $fb_user[name], url is $fb_user[url] \n";
                    fwrite($fp, $debug_string);
                  }
                  $local_time = $comment[time] + (get_option('gmt_offset') * 3600);
                  if(WPBOOKDEBUG) {
                    $fp = @fopen($debug_file, 'a');
                    $debug_string=date("Y-m-d H:i:s",time())." : comment[time] was $comment[time], gmt offset is ". get_option('gmt_offset') .", local_time is $local_time   \n";
                    fwrite($fp, $debug_string);
                  }
                  $time = date("Y-m-d H:i:s",$local_time);
                  $data = array(
                              'comment_post_ID' => $wordpress_post_id,
                              'comment_author' => $fb_user[name],
                              'comment_author_email' => $wpbook_comment_email,
                              'comment_author_url' => $fb_user[url],
                              'comment_content' => $comment[text],
                              'comment_type' => '',
                              'comment_parent' => 0,
                              'comment_author_IP' => '127.0.0.1',
                              'comment_agent' => 'WPBook Comment Import',
                              'comment_date' => $time,
                              'comment_approved' => $wpbook_comment_approval,
                              'user_ID' => ''
                              );
                  /* I'd like to use wp_new_comment here, but:
                   *   - It ignores the timestamp passed in and uses now instead
                   *   - It calls wp_allow_comment which in turn invokes comment flood throttle
                   * So instead I use wp_insert_comment but replicate some of the filtering
                   *           $my_id = wp_new_comment($data); 
                   */
                  $data = apply_filters('preprocess_comment', $data); // filtering normally done by wp_new_comment
                  $data['comment_parent'] = isset($data['comment_parent']) ? absint($data['comment_parent']) : 0;
                  $parent_status = ( 0 < $data['comment_parent'] ) ? wp_get_comment_status($data['comment_parent']) : '';
                  $data['comment_parent'] = ( 'approved' == $parent_status || 'unapproved' == $parent_status ) ? $data['comment_parent'] : 0;
                  if(WPBOOKDEBUG) {
                    $fp = @fopen($debug_file, 'a');
                    $debug_string=date("Y-m-d H:i:s",time())." : About to call wp_filter_comment on comment $my_id, approval $wpbook_comment_approval \n";
                    fwrite($fp, $debug_string);
                  }
                  if(WPBOOKDEBUG) {
                    $fp = @fopen($debug_file, 'a');
                    $debug_string=date("Y-m-d H:i:s",time())." : Unfiltered Data object: ". print_r($data,true) ." \n";
                    fwrite($fp, $debug_string);
                  }
                  $data = wp_filter_comment($data);
                  if(WPBOOKDEBUG) {
                    $fp = @fopen($debug_file, 'a');
                    $debug_string=date("Y-m-d H:i:s",time())." : Past wp_filter_comment, about to call wp_insert_comment on comment $my_id, approval $wpbook_comment_approval \n";
                    fwrite($fp, $debug_string);
                  }
                  if(WPBOOKDEBUG) {
                    $fp = @fopen($debug_file, 'a');
                    $debug_string=date("Y-m-d H:i:s",time())." : Filtered Data object: ". print_r($data,true) ." \n";
                    fwrite($fp, $debug_string);
                  }
                  $my_id = wp_insert_comment($data);  
                  if(WPBOOKDEBUG) {
                    $fp = @fopen($debug_file, 'a');
                    $debug_string=date("Y-m-d H:i:s",time())." : Past wp_insert_comment, now calling do_action on comment $my_id, approval $wpbook_comment_approval \n";
                    fwrite($fp, $debug_string);
                  }
                  
                  /* Seems like doing notification causes problems, so let's
                   * disable it for now
                   */ 
                  //do_action('comment_post', $my_id, $data['comment_approved']); 
                
                  if(WPBOOKDEBUG) {
                    $fp = @fopen($debug_file, 'a');
                    $debug_string=date("Y-m-d H:i:s",time())." : Posted comment with timestamp $time, id $my_id, approval $wpbook_comment_approval \n";
                    fwrite($fp, $debug_string);
                  }
                  if($mp->meta_key == '_wpbook_user_stream_id') {
                    $sql="update $wpdb->postmeta set meta_value=$comment[time] where post_id=$mp->post_id and meta_key='_wpbook_user_stream_time'";
                  } 
                  if($mp->meta_key == '_wpbook_group_stream_id') {
                    $sql="update $wpdb->postmeta set meta_value=$comment[time] where post_id=$mp->post_id and meta_key='_wpbook_group_stream_time'";
                  } 
                  if($mp->meta_key == '_wpbook_page_stream_id') {
                    $sql="update $wpdb->postmeta set meta_value=$comment[time] where post_id=$mp->post_id and meta_key='_wpbook_page_stream_time'";
                  }
                  if(WPBOOKDEBUG) {
                    $fp = @fopen($debug_file, 'a');
                    $debug_string=date("Y-m-d H:i:s",time())." : About to update timestamp, SQL is $sql \n";
                    fwrite($fp, $debug_string);
                  }
                  $update_result = $wpdb->query($sql);
                  if(WPBOOKDEBUG) {
                    $fp = @fopen($debug_file, 'a');
                    $debug_string=date("Y-m-d H:i:s",time())." : Updated timestamp, rows affected $wpdb->num_rows \n";
                    fwrite($fp, $debug_string);
                  } 
                } // end of foreach user
              } // end of if fbuserinfo is array
            } // end of new comment process for user stream
          } else {
            if(WPBOOKDEBUG) {
              $fp = @fopen($debug_file, 'a');
              $debug_string=date("Y-m-d H:i:s",time())." : There were no comments for post $mp->meta_value  \n";
              fwrite($fp, $debug_string);
            } // no comments for this post
          }// end of comments for this post
        }// end of meta_posts foreach
      }// end of meta posts > 0
    } // end of for each row of posts to examine
  } else {
    if(WPBOOKDEBUG) {
      $fp = @fopen($debug_file, 'a');
      $debug_string=date("Y-m-d H:i:s",time())." : No posts to examine\n";
      fwrite($fp, $debug_string);
    }
    return;
  } // end of if wp_post ids  
} // end of function
?>
