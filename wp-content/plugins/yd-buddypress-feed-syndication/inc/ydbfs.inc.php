<?php
class ydbfsPlugin extends YD_Plugin {
	
	const DESCRIPTION_MAX_SIZE	= 300;
	const RSS_CACHE_LIFETIME	= 10800; 		// SimplePie rss feed cache = 60 * 60 * 3 seconds
	private $AVAILABLE_DELAYS	= array(
		'3 hours'	=> 10800,
		'6 hours'	=> 21600,
		'12 hours'	=> 43200
	);
	
	/** constructor **/
	function ydbfsPlugin ( $opts ) {
		
		parent::YD_Plugin( $opts );
				
		$this->form_blocks = $opts['form_blocks']; // No backlinkware
		
		add_action( 'bp_include', array( &$this, 'plugin_init' ) );
		add_filter( 'wp_feed_cache_transient_lifetime', array( &$this, 'set_transient_lifetime' ) );
		
		if( function_exists( 'bp_register_group_extension' ) ) {
			bp_register_group_extension( 'groupFeedSyndication' );
		}
		
		add_filter( 'bp_get_activity_action', array( &$this, 'add_target' ), 10000 );
	}
	
	function plugin_init() {
		add_action( 'init', array( &$this, 'setup_tabs' ) );
	}
	
	// =======================================  FRONT OFFICE ====================================
	
	function add_target( $html ) {
		$options = get_option( $this->option_key );
		if( isset( $options['open_out'] ) && $options['open_out'] ) {
			$html = preg_replace( '/<(a\s+[^>]+)>/i', "<\\1 target=\"_blank\">", $html );
		}
		return $html;
	}
		
	// =======================================  ADMIN SCREEN  ===================================
	
	function setup_tabs() {
		global $bp;
		
		$settings_link = $bp->loggedin_user->domain . $bp->settings->slug . '/';
		bp_core_new_subnav_item( array( 
			'name' => __( 'Import a blog', $this->tdomain ), 
			'slug' => 'rss_syndication', 
			'parent_url' => $settings_link, 
			'parent_slug' => $bp->settings->slug, 
			'screen_function' => array( &$this, 'screen_syndication_settings' ), 
			'position' => 20, 
			'user_has_access' => bp_is_my_profile() 
		) );
		
	}
	
	/* thanks to Guillaume Dott @ Selliance.com for pointing me to this */
	function set_transient_lifetime( $lifetime, $url = '' ) {	
		return self::RSS_CACHE_LIFETIME;
	}
	
	function screen_syndication_settings( $group = false ) {
		global $current_user, $bp_settings_updated;
	
		$bp_settings_updated = false;
	
		if ( $_POST['submit'] ) {
			check_admin_referer('bp_settings_syndication');
	
			if ( $syndications = $_POST['syndications'] ) {
				foreach( (array)$syndications as $key => $syndication ) {
					if( !$syndication || empty( $syndication ) || $syndication == '' ) {
						unset( $syndications[$key] );
						continue;
					}
					if( !preg_match( '|^http://|', $syndication ) )
						$syndication = $syndications[$key] = 'http://' . $syndication;
					include_once( ABSPATH . 'wp-includes/rss.php' );
					$rss = fetch_feed( $syndication );
					if( is_wp_error( $rss ) ) {
						$syndications_status[$key] = 'nok';
					} else {
						$syndications_status[$key] = 'ok';
					}
					if( isset( $_POST['refresh'][$key] ) && !empty( $_POST['refresh'][$key] ) && is_numeric( $_POST['refresh'][$key] ) ) {
						$syndications_refresh[$key] = $_POST['refresh'][$key];
					}
					if( isset( $_POST['titles'][$key] ) && !empty( $_POST['titles'][$key] ) ) {
						$syndications_title[$key] = $_POST['titles'][$key];
					}
				}
				
				update_user_meta( (int)$current_user->id, 'yd_syndications', $syndications );
				update_user_meta( (int)$current_user->id, 'yd_syndications_status', $syndications_status );
				update_user_meta( (int)$current_user->id, 'yd_syndications_refresh', $syndications_refresh );
				update_user_meta( (int)$current_user->id, 'yd_syndications_title', $syndications_title );
			}
	
			$bp_settings_updated = true;
		}
		if ( $_GET['delete'] ) {
			check_admin_referer('bp_settings_syndication');
			
			$syndications = get_user_meta( (int)$current_user->id, 'yd_syndications', true );
			foreach( (array)$syndications as $key => $syndication ) {
				if( $syndication == $_GET['delete'] ) 
					unset( $syndications[$key] ); 
			}
			update_user_meta( (int)$current_user->id, 'yd_syndications', $syndications );
		}
		if ( $_GET['update'] && $_GET['refresh'] ) {
			check_admin_referer('bp_settings_syndication');
			
			$syndications = get_user_meta( (int)$current_user->id, 'yd_syndications', true );
			$syndications_refresh = get_user_meta( (int)$current_user->id, 'yd_syndications_refresh', true );
			foreach( (array)$syndications as $key => $syndication ) {
				if( $syndication == $_GET['update'] ) 
					$syndications_refresh[$key] = $_GET['refresh']; 
			}
			update_user_meta( (int)$current_user->id, 'yd_syndications_refresh', $syndications_refresh );
		}
		
		add_action( 'bp_template_title', array( &$this, 'screen_syndication_settings_title' ) );
		add_action( 'bp_template_content', array( &$this, 'screen_syndication_settings_content' ) );
	
		bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
	}
	
	function screen_syndication_settings_title() {
		_e( 'Syndication Settings', $this->tdomain );
	}
	
	function screen_syndication_settings_content( $group = false ) {
		global $bp, $current_user, $bp_settings_updated;
		$options = get_option( $this->option_key );
		if( $group ) {
			$syndications			= groups_get_groupmeta( (int)$bp->groups->current_group->id, 'yd_syndications', true );
			$syndications_status	= groups_get_groupmeta( (int)$bp->groups->current_group->id, 'yd_syndications_status', true );
			$syndications_refresh	= groups_get_groupmeta( (int)$bp->groups->current_group->id, 'yd_syndications_refresh', true );
			$syndications_title		= groups_get_groupmeta( (int)$bp->groups->current_group->id, 'yd_syndications_title', true );
		} else {
			$syndications			= get_user_meta( (int)$current_user->id, 'yd_syndications', true );
			$syndications_status	= get_user_meta( (int)$current_user->id, 'yd_syndications_status', true );
			$syndications_refresh	= get_user_meta( (int)$current_user->id, 'yd_syndications_refresh', true );
			$syndications_title		= get_user_meta( (int)$current_user->id, 'yd_syndications_title', true );
		}
		?>

		<?php if ( $bp_settings_updated ) { ?>
			<div id="message" class="updated fade">
				<p><?php _e( 'Changes Saved.', $this->tdomain ) ?></p>
			</div>
		<?php } ?>
		
		<form action="<?php echo $bp->loggedin_user->domain . BP_SETTINGS_SLUG . '/rss_syndication' ?>" method="post" id="settings-form" class="standard-form">
		<h3><?php _e( 'RSS feed syndication and aggregation', $this->tdomain ) ?></h3>

		<p><?php _e( 'Enter full URL of blog or site feeds to import into your activity page.', $this->tdomain ) ?></p>
		
		<?php if( ($group && $options['group_limit']>1) || (!$group && $options['limit']>1 ) ) : ?>
			<p><?php _e( 'You can specify multiple RSS feeds that will be mixed.', $this->tdomain ) ?></p>
		<?php endif; ?>
		
		<style>
		a.trash {
			color: white;
			font-weight: bold;
			background-color: #F33;
			-moz-border-radius: 5px;
			border-radius: 5px;
			border: 1px solid #600;
			text-decoration: none;
			font-size: 90%;
			padding: 3px 4px;
			margin: 2px;
		}
		.ydbfs td {
			overflow:hidden;
			word-wrap:break-word;
			max-width:200px;
			vertical-align: top;
		} <?php /* You can use the tr class to override this */ ?>
		.ydbfs tr {
			border-bottom:1px dotted #ccc;
		} <?php /* You can use the tr class to override this */ ?>
		.ydbfs tr.new {
			border: none;
		}
		</style>
		<table class="ydbfs" id="feed_agregation_table">
		<tr>
		<th><?php echo __( 'Feed address', $this->tdomain ) ?></th>
		<th><?php echo __( 'Feed title', $this->tdomain ) ?></th>
		<th><?php echo __( 'Feed refresh', $this->tdomain ) ?></th>
		<th><?php echo __( 'Unsubscribe', $this->tdomain ) ?></th>
		</tr>
		<?php 
		
		$count = 0;
		if( !empty( $syndications ) ) {
			foreach( (array)$syndications as $key => $syndication ) {
				$count ++;
				if( $syndications_status[$key] == 'ok' ) {
					$color = '#9f9';
					$title = __( 'Feed validated', $this->tdomain );
				} else {
					$color = '#f99';
					$title = __( 'Error: feed could not be fetched at that address', $this->tdomain );
				}
				echo '<tr class="ydbfs"><td>';
	
				echo '<span class="highlight ydbfs" style="background-color:' . $color . ';"><a href="' . $syndication . '" target="_rss" title="' . $title . '">' . $syndication . '</a></span>';
				
				echo '</td>';
				echo '<td>' . $syndications_title[$key] . '</td>';
				
				$this->refresh_delay_menu( $syndication, $syndications_refresh[$key], $group );
				
				if( $group ) {
					$delete_url = '?delete=' . urlencode( (string)$syndication );
				} else {
					$delete_url = $bp->loggedin_user->domain . BP_SETTINGS_SLUG . '/rss_syndication'
						. '?delete=' . urlencode( (string)$syndication );
				}
				echo '</td><td><a class="trash ydbfs" title="' . __( 'Delete feed', $this->tdomain ) . '" href="' . wp_nonce_url( $delete_url, 'bp_settings_syndication' ) . '">X</a>';
				echo '</tr>';
				echo '<input type="hidden" name="syndications[]" value="' . $syndication . '"/>';
				echo '<input type="hidden" name="titles[]" value="' . $syndications_title[$key] . '"/>';
			}
		}
		?>
		
		<?php if( ($group && $count < $options['group_limit']) || (!$group && $count < $options['limit'] ) ) : ?>
			<tr class="ydbfs new first">
			 <td>
			  <label for="add_syndication"><?php _e( 'New RSS feed address to syndicate:', $this->tdomain ) ?></label>
			 </td>
			 <td>
			  <input type="text" name="syndications[]" id="add_syndication" size="50" class="ydbfs" />
			 </td>
			 <?php $this->refresh_delay_menu( '', '', $group ); ?>
			 <td rowspan="2">
			  <div class="submit">
			   <input type="submit" name="submit" value="<?php _e( 'Add feed', $this->tdomain ) ?>" id="submit" class="auto ydbfs" />
			  </div>
			 </td>
			</tr>
			<tr class="ydbfs new second">
			 <td>
			  <label for="add_title"><?php _e( 'Feed title:', $this->tdomain ) ?></label>
			 </td>
			 <td>
			  <input type="text" name="titles[]" id="add_title" size="50" class="ydbfs" />
			 </td>
			 <td>&nbsp;</td>
			</tr>
		<?php endif; ?>
		
		</table>
		
		<?php wp_nonce_field('bp_settings_syndication') ?>

		</form>
	<?php
	}
	
	function refresh_delay_menu( $syndication = '', $refresh = 0, $group = false ) {
		global $bp;

		if( !empty( $syndication ) ) {
			if( !$group ) {
				$update_url = wp_nonce_url( 
					$bp->loggedin_user->domain . BP_SETTINGS_SLUG . '/rss_syndication'
					. '?update=' . urlencode( (string)$syndication ), 
					'bp_settings_syndication' 
				) . '&refresh=';
			} else {
				$url = bp_get_group_permalink()
					. 'admin/group-feed-syndication'
					. '?update=' . urlencode( (string)$syndication );
				$update_url = wp_nonce_url( $url, 
					'bp_settings_syndication' ) . '&refresh=';
			}
			$onchange = 'onChange="document.location=\'' . $update_url 
				. '\'+this.options[this.selectedIndex].value;"';
		} else {
			$onchange = '';
		}

		echo '<td>';
		echo __( 'Refresh every:', $this->tdomain );
		echo ' <select name="refresh[]" ' . $onchange . '>';
		foreach( (array)$this->AVAILABLE_DELAYS as $opt => $val ) {
			echo '<option value="' . $val . '" ';
			if( $refresh == $val ) echo ' selected="selected" ';
			echo '>';
			echo __( $opt, $this->tdomain );
			echo '</option>';	
		}
		echo '</select></td>';	
	}
	
	// ======================================  SYNDICATION  ===================================
	
	/** Select all users that have syndication feed data (parse all user metas) **/
	function get_all_feed_users() {
		global $wpdb;
		$query = "
			SELECT DISTINCT( user_id )
			FROM $wpdb->usermeta
			WHERE meta_key = 'yd_syndications';
		";
		$users = $wpdb->get_col( $query );
		return $users;
	}
	
	function get_feed_list( $nok = false, $force = false ) {
		$feeds = array();
		$users = self::get_all_feed_users();

		foreach( $users as $user ) {
			$syndications			= get_user_meta( (int)$user, 'yd_syndications', true );
			$syndications_status	= get_user_meta( (int)$user, 'yd_syndications_status', true );
			$syndications_refresh	= get_user_meta( (int)$user, 'yd_syndications_refresh', true );
			$syndications_updated	= get_user_meta( (int)$user, 'yd_syndications_updated', true );
			$syndications_title		= get_user_meta( (int)$user, 'yd_syndications_title', true );
			foreach( (array) $syndications as $key => $syndication ) {
				if( ( !$nok && $syndications_status[$key] == 'ok' ) || ( $nok && $syndications_status[$key] == 'nok' ) ) {

					/** Only include feeds which are due to be updated according to refresh delay **/
					if( isset( $syndications_updated[$key] ) && $syndications_updated[$key] > 0 ) {
						$delay = time() - $syndications_updated[$key];
						if( !$force && $delay < $syndications_refresh[$key] )
							continue;
					}
					$feeds[] = array(
						'user_id' => $user,
						'user_feed_id' => $key,
						'feed_url' => $syndication,
						'feed_title' => $syndications_title[$key]
					);
				}
			}
		}
		return $feeds;
	}
	
	/** Get all appropriate feeds, fetch them, parse them, inject items in user's activity feeds **/
	function syndicate( $nok = false, $force = false ) {
		$options = get_option( 'yd-buddypress-feed-syndication' ); //statically called :-s
		$feeds = self::get_feed_list( $nok, $force );
		
		foreach ( (array) $feeds as $feed_id => $feed ) {
			$rss = fetch_feed( trim( $feed['feed_url'] ) );
			
			/** Mark broken feeds as nok **/
			if( is_wp_error( $rss ) ) {
				self::mark_error( $feed );
				continue;
			}
			
			/** Mark feed as updated **/
			self::mark_updated( $feed );
			
			$maxitems = $rss->get_item_quantity();
			
			$items = array();
			
			/* thanks to bp-external-activity plugin authors for this */ 
			$rss_items = $rss->get_items(0, $maxitems);
			foreach ($rss->get_items(0, $maxitems) as $rss_item ) {
				$date = $rss_item->get_date();
	    		$key = strtotime( $date );
	
				$items[$key]['feed_id'] = $feed_id;
				$items[$key]['user_id'] = $feed['user_id'];
				$items[$key]['user_feed_id'] = $feed['user_feed_id'];
				$items[$key]['feed_title'] = $feed['feed_title'];
	
				$items[$key]['link'] = $rss_item->get_link();
				$items[$key]['link'] = preg_replace( '|diff.*prev|', '', $items[$key]['link'] );
	
				$items[$key]['title'] = $rss_item->get_title();
				//$items[$key]['description'] = self::ttruncat( strip_tags( $rss_item->get_description() ), self::DESCRIPTION_MAX_SIZE );
				$items[$key]['description'] = $rss_item->get_description();
				
				$author = $rss_item->get_author();
				/* thanks to Guillaume Dott @ Selliance.com for noticing possible bug when author is not present in feed */
				if ( !method_exists($author, 'get_name') || !$userdata = get_userdatabylogin( strtolower( $author->get_name() ) ) )
					$user_id = 0;
				else
					$user_id = $userdata->ID;
	
				$items[$key]['author'] = $user_id;
			}
			
			if ( $items ) {
				ksort($items);
				$items = array_reverse($items, true);
			} else {
				continue;
			}
			
			/** Record found items in activity streams **/
			foreach ( (array) $items as $post_date => $post ) {
				$feed_id = $post['feed_id'];
				$author_link = ( $post['author'] ) ? '<a href="' . bp_core_get_user_domain( $post['author'] ) . '">' . bp_core_get_user_displayname( $post['author'] ) . '</a>' :  __( 'Imported', 'ydbfs' );
				if( isset( $options['open_out'] ) && $options['open_out'] ) {
					$target = ' target="_blank" ';
				} else {
					$target = '';
				}
				$item_link = '<a href="' . $post['link'] . '" ' . $target . '>' . $post['title'] . '</a>';
				
				$t = $post['feed_title'];
				$dn = bp_core_get_user_displayname( $post['user_id'] );
				if( !empty($t) ) {
					$activity_action = sprintf(__( 'New blog post imported from &laquo;%1$s&raquo; by %2$s: %3$s', 'ydbfs' ), $t, $dn, $item_link );
				} else {
					$activity_action = sprintf(__( 'New blog post imported by %1$s: %2$s', 'ydbfs' ), $dn, $item_link );
				}
				
				/** Fetch an existing activity_id if one exists. **/
				if ( function_exists( 'bp_activity_get_activity_id' ) )
					$id = bp_activity_get_activity_id( 
						array( 
							'user_id'			=> $post['user_id'], 
							//'action'			=> $activity_action, //?
							'component'			=> 'imported_blog', 
							'type'				=> 'new_blog_post',
							'item_id'			=> $post['user_feed_id'], 
							'secondary_item_id'	=> wp_hash( $post['link'] )
						) 
					);
				
				/** Record or update in activity streams. **/
				$activity_data = array(
					'id'				=> $id,
					'user_id'			=> $post['user_id'],
					'component'			=> 'imported_blog',
					'action'			=> $activity_action,
					'primary_link'		=> $post['link'],
					'type'				=> 'new_blog_post',
					'recorded_time'		=> gmdate( "Y-m-d H:i:s", $post_date ),
					'hide_sitewide'		=> false,
					'content'			=> $post['description'],
					'item_id'			=> $post['user_feed_id'],
					'secondary_item_id'	=> wp_hash( $post['link'] )
				);
				bp_activity_add( $activity_data );
			}
		}
	}
	
	function mark_updated( $feed ) {
		$user	= $feed['user_id'];
		$key	= $feed['user_feed_id'];
		$syndications_updated = get_user_meta( (int)$user, 'yd_syndications_updated', true );
		$syndications_updated[$key] = time();
		update_user_meta( (int)$user, 'yd_syndications_updated', $syndications_updated );
	}
	
	function mark_error( $feed ) {
		$user	= $feed['user_id'];
		$key	= $feed['user_feed_id'];
		$syndications_status = get_user_meta( (int)$user, 'yd_syndications_status', true );
		$syndications_status[$key] = 'nok';
		update_user_meta( (int)$user, 'yd_syndications_status', $syndications_status );
	}
	
	// ======================================  CRON RELATED  ==================================
	
	function hourly_update( $op = false, $params = '', $force = false ) {
		if( !$op || !is_object( $op ) ) {
			$op = new YD_OptionPage(); //dummy object
		}

		self::do_things( &$op, $nok = false, $force );
		update_option( 'YD_P_hourly', time() );
	}
	
	function force_update( $op = false ) {
		self::hourly_update( $op, '', $force = true );
	}

	function force_check( $op = false ) {
		self::daily_update( $op, $force = true );
	}
	
	function daily_update( $op = false, $force = false ) {
		if( !$op || !is_object( $op ) ) {
			$op = new YD_OptionPage(); //dummy object
		}
		self::do_things( &$op, $nok = true, $force );
		update_option( 'YD_P_daily', time() );
	}
	
	function do_things( $op = false, $nok = false, $force = false ) {		
		self::syndicate( $nok, $force );
		if( class_exists( 'groupFeedSyndication' ) ) {
			groupFeedSyndication::syndicate( $nok, $force );
		}
		//$op->error_msg .= 'Great.';
		$op->update_msg .= __( 'Feeds syndication updated.', 'ydbfs' );
		
		update_option( 'YD_P_last_action', time() );
	}

	function check_update( $op ) {
		$op->update_msg .= '<p>';
		if( $last = get_option( 'YD_P_daily' ) ) {
			$op->update_msg .= __( 'Last daily action was on: ', 'ydbfs' ) 
				. date_i18n( DATE_RSS, $last ) . '<br/>';
		} else { 
			$op->update_msg .= __( 'No daily action yet.', 'ydbfs' ) . '<br/>';
		}
		if( $last = get_option( 'YD_P_hourly' ) ) {
			$op->update_msg .= __( 'Last hourly action was on: ', 'ydbfs' ) 
				. date_i18n( DATE_RSS, $last ) . '<br/>';
		} else { 
			$op->update_msg .= __( 'No hourly action yet.', 'ydbfs' ) . '<br/>';
		}
		if( $last = get_option( 'YD_P_last_action' ) ) {
			$op->update_msg .= __( 'Last completed action was on: ', 'ydbfs' ) 
				. date_i18n( DATE_RSS, $last ) . '<br/>';
		} else { 
			$op->update_msg .= __( 'No recorded action yet.', 'ydbfs' ) . '<br/>';
		}
		$op->update_msg .= '</p>';
	}
	
	// ==================================  MISC UTILITIES  ========================
	
	function ttruncat($text,$numb) {
		if (strlen($text) > $numb) {
			$text = substr($text, 0, $numb);
			$text = substr($text,0,strrpos($text," "));
			$etc = " ...";
			$text = $text.$etc;
		}
		return $text;
	}
}

// =======================================  GROUP FEATURES ==================================

// This is inspired by the external-group-blogs BP plugin

/** Group blog extension using the BuddyPress group extension API **/
if( class_exists( 'BP_Group_Extension' ) ) {
	class groupFeedSyndication extends BP_Group_Extension {

		function groupFeedSyndication() {
			global $bp, $ydbfs_o;
	
			$this->name = __( 'Import blogs', $ydbfs_o->tdomain );
			$this->slug = 'group-feed-syndication';
	
			//$this->create_step_position = 21;
			$this->enable_create_step = false;
			$this->enable_nav_item = false;
		}
		
		function create_screen() {
			//wp_nonce_field('bp_settings_syndication');//?
		}
		
		function create_screen_save() {
			//wp_nonce_field('bp_settings_syndication');//?
		}
		
		function edit_screen() {
			global $ydbfs_o;
			$ydbfs_o->screen_syndication_settings_content( $group = true );
		}
		
		function edit_screen_save() {
			global $bp, $current_user, $bp_settings_updated;
		
			$bp_settings_updated = false;
		
			if ( $_POST['submit'] ) {
				check_admin_referer('bp_settings_syndication');
		
				if ( $syndications = $_POST['syndications'] ) {
					foreach( (array)$syndications as $key => $syndication ) {
						if( !$syndication || empty( $syndication ) || $syndication == '' ) {
							unset( $syndications[$key] );
							continue;
						}
						if( !preg_match( '|^http://|', $syndication ) )
							$syndication = $syndications[$key] = 'http://' . $syndication;
						include_once( ABSPATH . 'wp-includes/rss.php' );
						$rss = fetch_feed( $syndication );
						if( is_wp_error( $rss ) ) {
							$syndications_status[$key] = 'nok';
						} else {
							$syndications_status[$key] = 'ok';
						}
						if( isset( $_POST['refresh'][$key] ) && !empty( $_POST['refresh'][$key] ) && is_numeric( $_POST['refresh'][$key] ) ) {
							$syndications_refresh[$key] = $_POST['refresh'][$key];
						}
						if( isset( $_POST['titles'][$key] ) && !empty( $_POST['titles'][$key] ) ) {
							$syndications_title[$key] = $_POST['titles'][$key];
						}
					}
					
					groups_update_groupmeta( (int)$bp->groups->current_group->id, 'yd_syndications', $syndications );
					groups_update_groupmeta( (int)$bp->groups->current_group->id, 'yd_syndications_status', $syndications_status );
					groups_update_groupmeta( (int)$bp->groups->current_group->id, 'yd_syndications_refresh', $syndications_refresh );
					groups_update_groupmeta( (int)$bp->groups->current_group->id, 'yd_syndications_title', $syndications_title );
				}
		
				$bp_settings_updated = true;
			}
			if ( $_GET['delete'] ) {
				check_admin_referer('bp_settings_syndication');
				
				$syndications = groups_get_groupmeta( (int)$bp->groups->current_group->id, 'yd_syndications', true );
				foreach( (array)$syndications as $key => $syndication ) {
					if( $syndication == $_GET['delete'] ) 
						unset( $syndications[$key] ); 
				}
				groups_update_groupmeta( (int)$bp->groups->current_group->id, 'yd_syndications', $syndications );
			}
			if ( $_GET['update'] && $_GET['refresh'] ) {
				check_admin_referer('bp_settings_syndication');
				
				$syndications = groups_get_groupmeta( (int)$bp->groups->current_group->id, 'yd_syndications', true );
				$syndications_refresh = groups_get_groupmeta( (int)$bp->groups->current_group->id, 'yd_syndications_refresh', true );
				foreach( (array)$syndications as $key => $syndication ) {
					if( $syndication == $_GET['update'] ) 
						$syndications_refresh[$key] = $_GET['refresh']; 
				}
				groups_update_groupmeta( (int)$bp->groups->current_group->id, 'yd_syndications_refresh', $syndications_refresh );
			}
			
			
		}
		
		/* We don't need display functions since the group activity stream handles it all. */
		function display() {}
		function widget_display() {}
		
		// Custom fonction ===
		
		function refresh_delay_menu( $syndication = '', $refresh = 0, $group = false ) {
			global $ydbfs_o;
			$ydbfs_o->refresh_delay_menu( $syndication, $refresh, $group );
		}
		
		// Syndication ========
		
		function get_all_feed_groups() {
			global $wpdb, $bp;
			$gm = $bp->groups->table_name_groupmeta;
			$query = "
				SELECT DISTINCT( group_id )
				FROM $gm
				WHERE meta_key = 'yd_syndications';
			";
			$groups = $wpdb->get_col( $query );
			return $groups;
		}
		
		function get_feed_list( $nok = false, $force = false ) {
			$feeds = array();
			$group_ids = self::get_all_feed_groups();
			//if( $force ) echo 'FORCE<br/>';	//debug
			//if( $nok ) echo 'NOK<br/>';	//debug
			foreach( $group_ids as $group_id ) {
				$syndications			= groups_get_groupmeta( (int)$group_id, 'yd_syndications', true );
				$syndications_status	= groups_get_groupmeta( (int)$group_id, 'yd_syndications_status', true );
				$syndications_refresh	= groups_get_groupmeta( (int)$group_id, 'yd_syndications_refresh', true );
				$syndications_updated	= groups_get_groupmeta( (int)$group_id, 'yd_syndications_updated', true );
				$syndications_title		= groups_get_groupmeta( (int)$group_id, 'yd_syndications_title', true );
				foreach( (array) $syndications as $key => $syndication ) {
					if( ( !$nok && $syndications_status[$key] == 'ok' ) || ( $nok && $syndications_status[$key] == 'nok' ) ) {
						//Only include feeds which are due to be updated according to refresh delay
						if( isset( $syndications_updated[$key] ) && $syndications_updated[$key] > 0 ) {
							$delay = time() - $syndications_updated[$key];
							if( !$force && $delay < $syndications_refresh[$key] )
								continue;
						}
						$feeds[] = array(
							'group_id' => $group_id,
							'group_feed_id' => $key,
							'feed_url' => $syndication,
							'feed_title' => $syndications_title[$key]
						);
					}
				}
			}
			return $feeds;
		}
		
		function syndicate( $nok = false, $force = false ) {
			global $bp;
			$options = get_option( 'yd-buddypress-feed-syndication' ); //statically called :-s
			$feeds = self::get_feed_list( $nok, $force );
			
			foreach ( (array) $feeds as $feed_id => $feed ) {
				$rss = fetch_feed( trim( $feed['feed_url'] ) );
				
				/** Mark broken feeds as nok **/
				if( is_wp_error( $rss ) ) {
					self::mark_error( $feed );
					continue;
				}
				
				/** Mark feed as updated **/
				self::mark_updated( $feed );
				
				$maxitems = $rss->get_item_quantity();
				
				$items = array();
				
				/* thanks to bp-external-activity plugin authors for this */ 
				$rss_items = $rss->get_items(0, $maxitems);
				foreach ($rss->get_items(0, $maxitems) as $rss_item ) {
					$date = $rss_item->get_date();
		    		$key = strtotime( $date );
		
					$items[$key]['feed_id'] = $feed_id;
					$items[$key]['group_id'] = $feed['group_id'];
					$items[$key]['group_feed_id'] = $feed['group_feed_id'];
					$items[$key]['feed_title'] = $feed['feed_title'];
		
					$items[$key]['link'] = $rss_item->get_link();
					$items[$key]['link'] = preg_replace( '|diff.*prev|', '', $items[$key]['link'] );
		
					$items[$key]['title'] = $rss_item->get_title();
					//$items[$key]['description'] = self::ttruncat( strip_tags( $rss_item->get_description() ), self::DESCRIPTION_MAX_SIZE );
					$items[$key]['description'] = $rss_item->get_description();
					
					$author = $rss_item->get_author();
					$user_id = false;
		
					$items[$key]['author'] = $user_id;
				}
				
				if ( $items ) {
					ksort($items);
					$items = array_reverse($items, true);
				} else {
					continue;
				}
				
				/** Record found items in activity streams **/
				foreach ( (array) $items as $post_date => $post ) {
					$feed_id = $post['feed_id'];
					$author_link = ( $post['author'] ) ? '<a href="' . bp_core_get_user_domain( $post['author'] ) . '">' . bp_core_get_user_displayname( $post['author'] ) . '</a>' :  __( 'Imported', 'ydbfs' );
					if( isset( $options['open_out'] ) && $options['open_out'] ) {
						$target = ' target="_blank" ';
					} else {
						$target = '';
					}
					$item_link = '<a href="' . $post['link'] . '" ' . $target . '>' . $post['title'] . '</a>';
				
					$t = $post['feed_title'];
					$mygroup = groups_get_group( array( 'group_id' => $post['group_id'] ) );
					$gn = $mygroup->name;
					
					if( !empty($t) ) {
						$activity_action = sprintf(__( 'New blog post imported from &laquo;%1$s&raquo; for group %2$s: %3$s', 'ydbfs' ), $t, $gn, $item_link );
					} else {
						$activity_action = sprintf(__( 'New blog post imported for group %1$s: %2$s', 'ydbfs' ), $gn, $item_link );
					}
					
					/** Fetch an existing activity_id if one exists. **/
					if ( function_exists( 'bp_activity_get_activity_id' ) )
						$id = bp_activity_get_activity_id( array( 
							'user_id'			=> false, 
							//'action'			=> $activity_action, 
							'component'			=> 'groups', 
							'type'				=> 'activity_update', 
							'item_id'			=> $post['group_id'], 
							'secondary_item_id'	=> wp_hash( $post['link'] )
						)
					);
					
					/** Record or update in activity streams. **/
					$activity_data = array(
						'id'				=> $id,
						'user_id'			=> false,
						'component'			=> 'groups',
						'action'			=> $activity_action,
						'primary_link'		=> $post['link'],
						'type'				=> 'activity_update',
						'recorded_time'		=> gmdate( "Y-m-d H:i:s", $post_date ),
						'hide_sitewide'		=> false,
						'content'			=> $post['description'],
						'item_id'			=> $post['group_id'],
						'secondary_item_id'	=> wp_hash( $post['link'] )
					);
					$res = groups_record_activity( $activity_data );
				}
			}
		}
		
		function mark_updated( $feed ) {
			$group	= $feed['group_id'];
			$key	= $feed['group_feed_id'];
			$syndications_updated = groups_get_groupmeta( (int)$group, 'yd_syndications_updated', true );
			$syndications_updated[$key] = time();
			groups_update_groupmeta( (int)$group, 'yd_syndications_updated', $syndications_updated );
		}
		
		function mark_error( $feed ) {
			$group	= $feed['group_id'];
			$key	= $feed['group_feed_id'];
			$syndications_status = groups_get_groupmeta( (int)$group, 'yd_syndications_status', true );
			$syndications_status[$key] = 'nok';
			groups_update_groupmeta( (int)$group, 'yd_syndications_status', $syndications_status );
		}
	} // - end of class def
} // - if class exists
?>