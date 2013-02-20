<?php

if(is_admin()){
	add_action('init', 'wpv_filter_author_init');
	
	function wpv_filter_author_init() {
        global $pagenow;
        
        if($pagenow == 'post.php' || $pagenow == 'post-new.php'){
            add_action('wpv_add_filter_table_row', 'wpv_add_filter_author_table_row', 1, 1);
            add_filter('wpv_add_filters', 'wpv_add_filter_author', 1, 1);
        }
    }
    
    /**
      * Add a search by author filter
      * This gets added to the popup that shows the available filters.
      */

    function wpv_add_filter_author($filters) {
	$filters['post_author'] = array('name' => 'Post author',
					'type' => 'callback',
					'callback' => 'wpv_add_author',
					'args' => array());
	return $filters;
    }

    /**
      * Get the table row to add to the available filters
      */

    function wpv_add_filter_author_table_row($view_settings) {
	if (isset($view_settings['author_mode'][0])) {
	    global $view_settings_table_row;
	    $td = wpv_get_table_row_ui_post_author($view_settings_table_row, null, $view_settings);
	
	    echo '<tr class="wpv_filter_row wpv_post_type_filter_row" id="wpv_filter_row_' . $view_settings_table_row . '"' . wpv_filter_type_hide_element($view_settings, 'posts') . '>' . $td . '</tr>';
	    
	    $view_settings_table_row++;
	}
    }

    /**
      * Get the table info for the author
      * This is called (via ajax) when we add a post author filter
      * It's also called to display the existing post author filter for editing.
      */

    function wpv_get_table_row_ui_post_author($row, $selected, $view_settings = null) {
	
	if (isset($view_settings['author_mode']) && is_array($view_settings['author_mode'])) {
	    $view_settings['author_mode'] = $view_settings['author_mode'][0];
	}
	if (isset($_POST['author_mode'])) {
	    // coming from the add filter button
	    $defaults = array('author_mode' => $_POST['author_mode']);
	    if (isset($_POST['author_id'])) {
		$defaults['author_id'] = $_POST['author_id'];
	    }
	    if (isset($_POST['author_url'])) {
		$defaults['author_url'] = $_POST['author_url'];
	    }
	    if (isset($_POST['author_url_type'])) {
		$defaults['author_url_type'] = $_POST['author_url_type'];
	    }
	    if (isset($_POST['author_shortcode'])) {
		$defaults['author_shortcode'] = $_POST['author_shortcode'];
	    }
	    if (isset($_POST['author_shortcode_type'])) {
		$defaults['author_shortcode_type'] = $_POST['author_shortcode_type'];
	    }
	    
	    $view_settings = wp_parse_args($view_settings, $defaults);
	}
	
	ob_start();
	wpv_add_author(array('mode' => 'edit',
			      'view_settings' => $view_settings));
	$data = ob_get_clean();
	
	$td = '<td><img src="' . WPV_URL . '/res/img/delete.png" onclick="on_delete_wpv_filter(\'' . $row . '\')" style="cursor: pointer" />';
	$td .= '<td class="wpv_td_filter">';
	$td .= "<div id=\"wpv-filter-author-show\">\n";
	$td .= wpv_get_filter_author_summary($view_settings);
	$td .= "</div>\n";
	$td .= "<div id=\"wpv-filter-author-edit\" style='background:" . WPV_EDIT_BACKGROUND . ";display:none;'>\n";

	$td .= '<fieldset>';
	$td .= '<legend><strong>' . __('Post Author', 'wpv-views') . ':</strong></legend>';
	$td .= '<div id="wpv-filter-author">' . $data . '</div>';
		$td .= '<div id="wpv-author-info" style="margin-left: 20px;"></div>';
	$td .= '</fieldset>';
	ob_start();
	?>
	    <input class="button-primary" type="button" value="<?php echo __('OK', 'wpv-views'); ?>" name="<?php echo __('OK', 'wpv-views'); ?>" onclick="wpv_show_filter_author_edit_ok()"/>
	    <input class="button-secondary" type="button" value="<?php echo __('Cancel', 'wpv-views'); ?>" name="<?php echo __('Cancel', 'wpv-views'); ?>" onclick="wpv_show_filter_author_edit_cancel()"/>
	<?php
	$td .= ob_get_clean();
	$td .= '</div></td>';
	
	return $td;
    }

    /**
      * Display the summary text
      */

    function wpv_get_filter_author_summary_text($view_settings, $short=false) {
    global $wpdb;
     if (isset($_GET['post'])) {$view_name = get_the_title( $_GET['post']);} else {$view_name = 'view-name';}
	ob_start();
	
	switch ($view_settings['author_mode']) {
	
	    case 'current_user':
		_e('Select posts with the <strong>author</strong> the same as the <strong>current logged in user</strong>.', 'wpv-views');
		break;
	    case 'this_user':
		if (isset($view_settings['author_id']) && $view_settings['author_id'] > 0) {
		    $selected_author = $wpdb->get_var($wpdb->prepare("SELECT display_name FROM {$wpdb->prefix}users WHERE ID=%d", $view_settings['author_id']));
		} else {
		    $selected_author = 'None';
		}
		echo sprintf(__('Select posts with <strong>%s</strong> as the <strong>author</strong>.', 'wpv-views'), $selected_author);
		break;
	    case 'by_url':
		if (isset($view_settings['author_url']) && '' != $view_settings['author_url']){
		    $url_author = $view_settings['author_url'];
		} else {
		    $url_author = '<i>' . __('None set', 'wpv-views') . '</i>';
		}
		if (isset($view_settings['author_url_type']) && '' != $view_settings['author_url_type']){
		    $url_author_type = $view_settings['author_url_type'];
		    switch ($url_author_type) {
			    case 'id':
				    $example = '1';
				    break;
			    case 'username':
				    $example = 'admin';
				    break;
		    }
		} else {
		    $url_author_type = '<i>' . __('None set', 'wpv-views') . '</i>';
		    $example = '';
		}
		echo sprintf(__('Select posts with the author\'s <strong>%s</strong> determined by the URL parameter <strong>"%s"</strong>', 'wpv-views'), $url_author_type, $url_author);
		if ('' != $example) echo sprintf(__(' eg. yoursite/page-with-this-view/?<strong>%s</strong>=%s', 'wpv-views'), $url_author, $example);
		break;
	    case 'shortcode':
		if (isset($view_settings['author_shortcode']) && '' != $view_settings['author_shortcode']) {
		    $auth_short = $view_settings['author_shortcode'];
		} else {
		    $auth_short = 'None';
		}
		if (isset($view_settings['author_shortcode_type']) && '' != $view_settings['author_shortcode_type']){
		    $shortcode_author_type = $view_settings['author_shortcode_type'];
		    switch ($shortcode_author_type) {
			    case 'id':
				    $example = '1';
				    break;
			    case 'username':
				    $example = 'admin';
				    break;
		    }
		} else {
		    $shortcode_author_type = '<i>' . __('None set', 'wpv-views') . '</i>';
		    $example = '';
		}
		echo sprintf(__('Select posts which author\'s <strong>%s</strong> is set by the View shortcode attribute <strong>"%s"</strong>', 'wpv-views'), $shortcode_author_type, $auth_short);
		if ('' != $example) {
			echo sprintf(__(' eg. [wpv-view name="%s" <strong>%s</strong>="%s"]', 'wpv-views'), $view_name, $auth_short, $example);
		}
		break;
	}
		
	$data = ob_get_clean();
	
		if ($short) {
			// this happens on the Views table under Filter column
			if (substr($data, -1) == '.') {
				$data = substr($data, 0, -1);
			}
		}
	
	return $data;
	
    }
	
    /**
      * Display the summary
      * This is called in the table row when an author filter is added
      * Is also used on the Views table under Filter column using the wpv-view-get-summary filter
      * Displays the summary text given by wpv_get_filter_author_summary_text()
      */

    function wpv_get_filter_author_summary($view_settings) {
	ob_start();

		echo wpv_get_filter_author_summary_text($view_settings);        
	?>
	<br />
	<input class="button-secondary" type="button" value="<?php echo __('Edit', 'wpv-views'); ?>" name="<?php echo __('Edit', 'wpv-views'); ?>" onclick="wpv_show_filter_author_edit()"/>
	<?php
	
	$data = ob_get_clean();
	
	return $data;
	
    }
	
    /**
    * Add the author filter to the filter popup.
    */

    function wpv_add_author($args) {
	    
	global $wpdb;
	
	$edit = isset($args['mode']) && $args['mode'] == 'edit'; 
	
	$view_settings = isset($args['view_settings']) ? $args['view_settings'] : array();
	
	$defaults = array('author_mode' => 'current_user',
			  'author_id' => 0,
			  'author_url' => 'author-filter',
			  'author_url_type' => '',
			  'author_shortcode' => 'author',
			  'author_shortcode_type' => '');
	$view_settings = wp_parse_args($view_settings, $defaults);

	wp_nonce_field('wpv_get_posts_select_nonce', 'wpv_get_posts_select_nonce');

	    ?>

	    <div class="author-div" style="margin-left: 20px;">

	    <ul>
		<?php $radio_name = $edit ? '_wpv_settings[author_mode][]' : 'author_mode[]' ?>
		<li>
		    <?php $checked = $view_settings['author_mode'] == 'current_user' ? 'checked="checked"' : ''; ?>
		    <label><input type="radio" name="<?php echo $radio_name; ?>" value="current_user" <?php echo $checked; ?> />&nbsp;<?php _e('Post author is the same as the logged in user', 'wpv-views'); ?></label>
		    <?php if ($edit): // only one instance of this filter by view ?>
			<input type="hidden" name="_wpv_settings[post_author]" value="1"/> 
		    <?php endif; ?>
		</li>
		<li>
		    <?php $checked = $view_settings['author_mode'] == 'this_user' ? 'checked="checked"' : ''; ?>
		    <label><input type="radio" name="<?php echo $radio_name; ?>" value="this_user" <?php echo $checked; ?> />&nbsp;<?php _e('Post author is ', 'wpv-views'); ?></label>
		    
		    <?php $select_id = $edit ? 'wpv_author' : 'wpv_author_add' ?>
		    <?php $author_select_name = $edit ? '_wpv_settings[author_id]' : 'wpv_author_id_add' ?>
		    <select id="<?php echo $select_id; ?>" name="<?php echo $author_select_name; ?>">
		    <?php
			$users = get_users();
			foreach ($users as $user) {
			    $selected = $view_settings['author_id'] == $user->ID ? ' selected="selected"' : '';
			    echo '<option value="' . $user->ID . '"' . $selected . '>' . $user->display_name . '</option>';
			}          
		    ?>
		    </select>
		    <img id="wpv_update_author" src="<?php echo WPV_URL; ?>/res/img/ajax-loader.gif" width="16" height="16" style="display:none" alt="loading" />
		    
		</li>
		<li>
		    <?php $checked = $view_settings['author_mode'] == 'by_url' ? 'checked="checked"' : ''; ?>
		    <label><input type="radio" name="<?php echo $radio_name; ?>" value="by_url" <?php echo $checked; ?> />&nbsp;<?php _e('Post author\'s ', 'wpv-views'); ?></label>
		    <?php $select_url_type = $edit ? 'wpv_author_url_type' : 'wpv_author_url_type_add' ?>
		    <?php $author_select_name_url_type = $edit ? '_wpv_settings[author_url_type]' : 'wpv_author_url_type_add' ?>
		    <select id="<?php echo $select_url_type; ?>" name="<?php echo $author_select_name_url_type; ?>">
			<?php 
			$selected_type = $view_settings['author_url_type'] == 'id' ? ' selected="selected"' : '';
			echo '<option value="id"' . $selected_type . '>ID</option>';
			$selected_type = $view_settings['author_url_type'] == 'username' ? ' selected="selected"' : '';
			echo '<option value="username"' . $selected_type . '>username</option>';
			?>
		    </select>
		    <label><?php _e(' is set by this URL parameter: ', 'wpv-views'); ?></label>
		    <?php $name = $edit ? '_wpv_settings[author_url]' : 'author_url' ?>
		    <input type='text' name="<?php echo $name; ?>" value="<?php echo $view_settings['author_url']; ?>" size="10" />
		    <span class="wpv_author_url_param_missing" style="color:red;"><?php echo __('<- Please enter a value here', 'wpv-views'); ?></span>
		    <span class="wpv_author_url_param_ilegal" style="color:red;"><?php echo __('<- Only lowercase letters, numbers, hyphens and underscores allowed', 'wpv-views'); ?></span>
		</li>
		<li>
		    <?php $checked = $view_settings['author_mode'] == 'shortcode' ? 'checked="checked"' : ''; ?>
		    <label><input type="radio" name="<?php echo $radio_name; ?>" value="shortcode" <?php echo $checked; ?>>&nbsp;<?php _e('Post author\'s ', 'wpv-views'); ?></label>
		    <?php $select_shortcode_type = $edit ? 'wpv_author_shortcode_type' : 'wpv_author_shortcode_type_add' ?>
		    <?php $author_select_name_shortcode_type = $edit ? '_wpv_settings[author_shortcode_type]' : 'wpv_author_shortcode_type_add' ?>
		    <select id="<?php echo $select_shortcode_type; ?>" name="<?php echo $author_select_name_shortcode_type; ?>">
			<?php 
			$selected_type = $view_settings['author_shortcode_type'] == 'id' ? ' selected="selected"' : '';
			echo '<option value="id"' . $selected_type . '>ID</option>';
			$selected_type = $view_settings['author_shortcode_type'] == 'username' ? ' selected="selected"' : '';
			echo '<option value="username"' . $selected_type . '>username</option>';
			?>
		    </select>
		    <label><?php _e(' is set by this View shortcode attribute: ', 'wpv-views'); ?></label>
		    <?php $name = $edit ? '_wpv_settings[author_shortcode]' : 'author_shortcode' ?>
		    <input type='text' name="<?php echo $name; ?>" value="<?php echo $view_settings['author_shortcode']; ?>" size="10" />
		    <span class="wpv_author_shortcode_param_missing" style="color:red;"><?php echo __('<- Please enter a value here', 'wpv-views'); ?></span>
		    <span class="wpv_author_shortcode_param_ilegal" style="color:red;"><?php echo __('<- Only lowercase letters and numbers allowed', 'wpv-views'); ?></span>
		</li>
	    </ul>
	    
	    <div class="wpv_author_helper"></div>
	    
	    </div>
	
	    <?php
	
	
    }
    
    /**
    * Add a filter to show the summary on the Views table under Filter column
    */

    add_filter('wpv-view-get-summary', 'wpv_author_summary_filter', 5, 3);

	function wpv_author_summary_filter($summary, $post_id, $view_settings) {
		if(isset($view_settings['query_type']) && $view_settings['query_type'][0] == 'posts' && isset($view_settings['author_mode'])) {
			$view_settings['author_mode'] = $view_settings['author_mode'][0];
			
			$result = wpv_get_filter_author_summary_text($view_settings, true);
			if ($result != '' && $summary != '') {
				$summary .= '<br />';
			}
			$summary .= $result;
		}
		
		return $summary;
	}
    
}

