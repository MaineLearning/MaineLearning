<?php
class MostLikedPostsWidget extends WP_Widget
{
     function MostLikedPostsWidget()
     {
	     load_plugin_textdomain( 'wti-like-post', false, 'wti-like-post/lang' );
          $widget_ops = array('description' => __('Widget to display most liked posts for a given time range.', 'wti-like-post'));
          parent::WP_Widget(false, $name = __('Most Liked Posts', 'wti-like-post'), $widget_ops);
     }

     /** @see WP_Widget::widget */
     function widget($args, $instance) {
          global $MostLikedPosts;
          $MostLikedPosts->widget($args, $instance); 
     }
    
     function update($new_instance, $old_instance) {         
          if($new_instance['title'] == ''){
               $new_instance['title'] = __('Most Liked Posts', 'wti-like-post');
          }
         
          /*if($new_instance['number'] == ''){
               $new_instance['number'] = 10;
          }*/
		
		if($new_instance['time_range'] == ''){
               $new_instance['time_range'] = 'all';
          }
		
          return $new_instance;
     }
    
     function form($instance)
     {
          global $MostLikedPosts;
		$time_range_array = array(
							'all' => __('All time', 'wti-like-post'),
							'1' => __('Last one day', 'wti-like-post'),
							'2' => __('Last two days', 'wti-like-post'),
							'3' => __('Last three days', 'wti-like-post'),
							'7' => __('Last one week', 'wti-like-post'),
							'14' => __('Last two weeks', 'wti-like-post'),
							'21' => __('Last three weeks', 'wti-like-post'),
							'1m' => __('Last one month', 'wti-like-post'),
							'2m' => __('Last two months', 'wti-like-post'),
							'3m' => __('Last three months', 'wti-like-post'),
							'6m' => __('Last six months', 'wti-like-post'),
							'1y' => __('Last one year', 'wti-like-post')
						);
		
		$show_types = array('most_liked' => __('Most Liked', 'wti-like-post'), 'recent_liked' => __('Recently Liked', 'wti-like-post'));
          ?>
		<p>
               <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'wti-like-post'); ?>:<br />
               <input class="widefat" type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $instance['title'];?>" /></label>
          </p>		
		<p>
               <label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of posts to show', 'wti-like-post'); ?>:<br />
               <input type="text" id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" style="width: 25px;" value="<?php echo $instance['number'];?>" /></label>
          </p>
		<p>
               <label for="<?php echo $this->get_field_id('time_range'); ?>"><?php _e('Time range', 'wti-like-post'); ?>:<br />
			<select name="<?php echo $this->get_field_name('time_range'); ?>" id="<?php echo $this->get_field_id('time_range'); ?>">
			<?php
			foreach($time_range_array as $time_range_key => $time_range_value) {
				$selected = ($time_range_key == $instance['time_range']) ? 'selected' : '';
				echo '<option value="' . $time_range_key . '" ' . $selected . '>' . $time_range_value . '</option>';
			}
			?>
			</select>
          </p>
		<p>
               <label for="<?php echo $this->get_field_id('show_count'); ?>"><input type="checkbox" id="<?php echo $this->get_field_id('show_count'); ?>" name="<?php echo $this->get_field_name('show_count'); ?>" value="1" <?php if($instance['show_count'] == '1') echo 'checked="checked"'; ?> /> <?php _e('Show like count', 'wti-like-post'); ?></label>
          </p>
		<input type="hidden" id="wti-most-submit" name="wti-submit" value="1" />	   
          <?php
     }
}

class MostLikedPosts
{
     function MostLikedPosts(){
          add_action( 'widgets_init', array(&$this, 'init') );
     }
    
     function init(){
          register_widget("MostLikedPostsWidget");
     }
     
     function widget($args, $instance = array() ){
		global $wpdb;
		extract($args);
	    
		$title = $instance['title'];
		$show_count = $instance['show_count'];
		$time_range = $instance['time_range'];
		//$show_type = $instance['show_type'];
		$order_by = 'ORDER BY like_count DESC, post_title';
		
		if((int)$instance['number'] > 0) {
			$limit = "LIMIT " . (int)$instance['number'];
		}
		
		$widget_data  = $before_widget;
		$widget_data .= $before_title . $title . $after_title;
		$widget_data .= '<ul class="wti-most-liked-posts">';
	
		$show_excluded_posts = get_option('wti_like_post_show_on_widget');
		$excluded_post_ids = explode(',', get_option('wti_like_post_excluded_posts'));
		
		if(!$show_excluded_posts && count($excluded_post_ids) > 0) {
			$where = "AND post_id NOT IN (" . get_option('wti_like_post_excluded_posts') . ")";
		}
		
		if($time_range != 'all') {
			$last_date = GetWtiLastDate($time_range);
			$where .= " AND date_time >= '$last_date'";
		}
		
		//getting the most liked posts
		$query = "SELECT post_id, SUM(value) AS like_count, post_title FROM `{$wpdb->prefix}wti_like_post` L, {$wpdb->prefix}posts P ";
		$query .= "WHERE L.post_id = P.ID AND post_status = 'publish' AND value > 0 $where GROUP BY post_id $order_by $limit";
		$posts = $wpdb->get_results($query);

		if(count($posts) > 0) {
			foreach ($posts as $post) {
				$post_title = stripslashes($post->post_title);
				$permalink = get_permalink($post->post_id);
				$like_count = $post->like_count;
				
				$widget_data .= '<li><a href="' . $permalink . '" title="' . $post_title.'" rel="nofollow">' . $post_title . '</a>';
				$widget_data .= $show_count == '1' ? ' ('.$like_count.')' : '';
				$widget_data .= '</li>';
			}
		} else {
			$widget_data .= '<li>';
			$widget_data .= __('No posts liked yet.', 'wti-like-post');
			$widget_data .= '</li>';
		}
   
		$widget_data .= '</ul>';
		$widget_data .= $after_widget;
   
		echo $widget_data;
     }
}

$MostLikedPosts = new MostLikedPosts();

//recently like posts
class RecentlyLikedPostsWidget extends WP_Widget
{
     function RecentlyLikedPostsWidget()
     {
	     load_plugin_textdomain( 'wti-like-post', false, 'wti-like-post/lang' );
          $widget_ops = array('description' => __('Widget to show recently liked posts.', 'wti-like-post'));
          parent::WP_Widget(false, $name = __('Recently Liked Posts', 'wti-like-post'), $widget_ops);
     }

     function widget($args, $instance) {
          global $RecentlyLikedPosts;
          $RecentlyLikedPosts->widget($args, $instance); 
     }
    
     function update($new_instance, $old_instance) {         
          if($new_instance['title'] == ''){
               $new_instance['title'] = __('Recently Liked Posts', 'wti-like-post');
          }
         
          if($new_instance['number'] == ''){
               $new_instance['number'] = 10;
          }
         
          return $new_instance;
     }
    
     function form($instance)
     {
          global $RecentlyLikedPosts;
          ?>
		<p>
               <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'wti-like-post'); ?>:<br />
               <input class="widefat" type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $instance['title'];?>" /></label>
          </p>
		<p>
               <label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of entries to show', 'wti-like-post'); ?>:<br />
               <input type="text" id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" style="width: 25px;" value="<?php echo $instance['number'];?>" /> <small>(<?php echo __('Default', 'wti-like-post'); ?> 10)</small></label>
          </p>
		<input type="hidden" id="wti-recent-submit" name="wti-submit" value="1" />	   
          <?php
     }
}

class RecentlyLikedPosts
{
     function RecentlyLikedPosts(){
          add_action( 'widgets_init', array(&$this, 'init') );
     }
    
     function init(){
          register_widget("RecentlyLikedPostsWidget");
     }
     
     function widget($args, $instance = array() ){
		global $wpdb;
		extract($args);
	    
		$title = $instance['title'];
		$number = $instance['number'];
		
		$widget_data  = $before_widget;
		$widget_data .= $before_title . $title . $after_title;
		$widget_data .= '<ul class="wti-most-liked-posts wti-user-liked-posts">';
	
		$show_excluded_posts = get_option('wti_like_post_show_on_widget');
		$excluded_post_ids = explode(',', get_option('wti_like_post_excluded_posts'));
		
		if(!$show_excluded_posts && count($excluded_post_ids) > 0) {
			$where = "AND post_id NOT IN (" . get_option('wti_like_post_excluded_posts') . ")";
		}
		
		$recent_ids = $wpdb->get_col("SELECT DISTINCT(post_id) FROM `{$wpdb->prefix}wti_like_post` $where ORDER BY date_time DESC");
			
		if(count($recent_ids) > 0) {
			$where = "AND post_id IN(" . implode(",", $recent_ids) . ")";
		}
		
		//getting the most liked posts
		$query = "SELECT post_id, post_title FROM `{$wpdb->prefix}wti_like_post` L, {$wpdb->prefix}posts P ";
		$query .= "WHERE L.post_id = P.ID AND post_status = 'publish' AND value > 0 $where GROUP BY post_id ORDER BY date_time DESC LIMIT $number";
	
		$posts = $wpdb->get_results($query);
	 
		if(count($posts) > 0) {
			foreach ($posts as $post) {
				$post_title = stripslashes($post->post_title);
				$permalink = get_permalink($post->post_id);
				
				$widget_data .= '<li><a href="' . $permalink . '" title="' . $post_title.'" rel="nofollow">' . $post_title . '</a></li>';
			}
		} else {
			$widget_data .= '<li>';
			$widget_data .= __('No posts liked yet.', 'wti-like-post');
			$widget_data .= '</li>';
		}

		$widget_data .= '</ul>';
		$widget_data .= $after_widget;
   
		echo $widget_data;
     }
}

$RecentlyLikedPosts = new RecentlyLikedPosts();
?>