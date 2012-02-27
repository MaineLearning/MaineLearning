<?php
/*
Plugin Name: No Page Comment
Plugin URI: http://sethalling.com/plugins/no-page-comment
Description: A plugin that uses javascript to disable comments by default on posts, pages and/or custom post types but leave them enabled on others, while still giving you the ability to individually set them on a page or post basis. 
Version: 0.2
Author: Seth Alling
Author URI: http://sethalling.com/

    Plugin: Copyright (c) 2011 Seth Alling

    This program is free software; you can redistribute it and/or
    modify it under the terms of the GNU General Public License
    as published by the Free Software Foundation; either version 2
    of the License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

register_activation_hook(__FILE__, 'sta_npc_activate');

if ( ! function_exists('sta_npc_activate') ) {
	function sta_npc_activate() {
		sta_npc_load();
		global $sta_npc_plugin;
		$sta_npc_plugin->sta_npc_activate();
	}
}

if ( ! function_exists('sta_npc_load') ) {
	function sta_npc_load() {
		if ( ! class_exists('STA_NPC_Plugin') ) {
			class STA_NPC_Plugin {
				var $admin_options_name = 'sta_npc_admin_options_name';
				var $admin_users_name   = 'sta_npc_admin_options_name';
				var $plugin_domain      = 'sta_npc_lang';
				public $plugin_name     = 'no-page-comment';
				public $plugin_file;
				public $plugin_dir;
				
				// tansfer version 0.1.options to 0.2
				public function sta_npc_activate( ) {	
					$sta_npc_options_retrieve = get_option($this->admin_options_name);
					if ( isset($sta_npc_options_retrieve['disable_comments']) ) {
						$sta_npc_options_retrieve['disable_comments_page'] = $sta_npc_options_retrieve['disable_comments'];
						unset($sta_npc_options_retrieve['disable_comments']);
					}
					if ( isset($sta_npc_options_retrieve['disable_trackbacks']) ) {
						$sta_npc_options_retrieve['disable_trackbacks_page'] = $sta_npc_options_retrieve['disable_trackbacks'];
						unset($sta_npc_options_retrieve['disable_trackbacks']);
					}
					update_option($this->admin_options_name, $sta_npc_options_retrieve);					
				}
				
				// Plugin Constructor
				function sta_npc_plugin() {
					$this->plugin_dir = WP_PLUGIN_URL.'/'.$this->plugin_name;
					$this->plugin_file = $this->plugin_name . '.php';
				}
				
				// Intialize Admin Options
				function sta_npc_init() {
					$this->sta_npc_get_admin_options();
				}
				
				// Returns an array of admin options
				function sta_npc_get_admin_options() {
					
					$sta_npc_admin_options = array(
						'disable_comments_post'   => '',
						'disable_trackbacks_post' => '',
						'disable_comments_page'   => 'true',
						'disable_trackbacks_page' => 'true'
					);
				
					foreach ( get_post_types('','objects') as $posttype ) {
						if ( in_array( $posttype->name, array('post','page','revision','nav_menu_item','attachment') ) )
							continue;
					
						$sta_npc_admin_options['disable_comments_' . $posttype->name] = 'true';
						$sta_npc_admin_options['disable_trackbacks_' . $posttype->name] = 'true';
					
					} // end foreach post types
					
					$sta_npc_options = get_option($this->admin_options_name);
					if ( ! empty($sta_npc_options) ) {
						
						foreach ($sta_npc_options as $key => $option)
							$sta_npc_admin_options[$key] = $option;
					}				
					update_option($this->admin_options_name, $sta_npc_admin_options);
					return $sta_npc_admin_options;
				}
				
				// Prints out the admin page
				function sta_npc_print_admin_page() {
					$sta_npc_options = $this->sta_npc_get_admin_options();
										
					if ( isset($_POST['update_sta_npc_plugin_settings']) ) {
				
						foreach ( get_post_types('','objects') as $posttype ) {
							if ( in_array( $posttype->name, array('revision','nav_menu_item','attachment') ) )
								continue;
							
							if ( isset($_POST['sta_npc_disable_comments_' . $posttype->name]) ) {
								$sta_npc_options['disable_comments_' . $posttype->name] = $_POST['sta_npc_disable_comments_' . $posttype->name];
							} else {
								$sta_npc_options['disable_comments_' . $posttype->name] = 'false';
							}
							
							if ( isset($_POST['sta_npc_disable_trackbacks_' . $posttype->name]) ) {
								$sta_npc_options['disable_trackbacks_' . $posttype->name] = $_POST['sta_npc_disable_trackbacks_' . $posttype->name];
							} else {
								$sta_npc_options['disable_trackbacks_' . $posttype->name] = 'false';
							}
						
						} // end foreach post types
					
						update_option($this->admin_options_name, $sta_npc_options);
						?>
						<div class="updated"><p><strong><?php _e('Settings Updated.', $this->plugin_domain);?></strong></p></div>
					<?php } ?>
					
					<div class="wrap">
						<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
							<div id="icon-options-general" class="icon32"><br></div>
							<h2>No Page Comment Settings</h2>
							<div id="poststuff" class="metabox-holder has-right-sidebar">
								<div id="side-info-column" class="inner-sidebar">
									<div id="side-sortables" class="meta-box-sortables ui-sortable">
										<div id="pageparentdiv" class="postbox">
											<h3 class="hndle" style="cursor:default;"><span>Other plugins by <a href="http://sethalling.com/" title="Seth Alling" style="font-size:15px;">Seth Alling</a>:</span></h3>
											<div class="inside">
												<ul>
													<li style="padding:5px 0;"><a href="http://sethalling.com/plugins/wp-faqs-pro" title="WP FAQs Pro">WP FAQs Pro</a></li>
												</ul>
											</div>
										</div>
									</div>
								</div>
								<div id="post-body">
									<div id="post-body-content">
										<div id="postcustom" class="postbox" style="">
											<h3 class="hndle" style="cursor:default;"><span>Disable comments on new:</span></h3>
											<div class="inside">
												<?php foreach ( get_post_types('','objects') as $posttype ) {
													if ( in_array( $posttype->name, array('revision','nav_menu_item','attachment') ) )
														continue; ?>
													<p style="padding:5px 0;">
														<strong class="post_type" style="width:160px; float:left;"><?php echo $posttype->label; ?></strong>
														<label for="sta_npc_disable_comments_<?php echo $posttype->name; ?>" style="width:110px; float:left;">
															<input type="checkbox" id="sta_npc_disable_comments_<?php echo $posttype->name; ?>" name="sta_npc_disable_comments_<?php echo $posttype->name; ?>" value="true" <?php if ( $sta_npc_options['disable_comments_' . $posttype->name] == 'true' ) { _e('checked="checked"', $this->plugin_domain); } ?> />
															Comments</label>
														<label for="sta_npc_disable_trackbacks_<?php echo $posttype->name; ?>" style="width:110px; float:left;">
															<input type="checkbox" id="sta_npc_disable_trackbacks_<?php echo $posttype->name; ?>" name="sta_npc_disable_trackbacks_<?php echo $posttype->name; ?>" value="true" <?php if ( $sta_npc_options['disable_trackbacks_' . $posttype->name] == 'true' ) { _e('checked="checked"', $this->plugin_domain); } ?>/> Trackbacks</label>
													</p>
													<br style="clear:both;" />
												<?php } ?>
											</div>
										</div>
										<p class="submit">
											<input type="submit" name="update_sta_npc_plugin_settings" id="submit" class="button-primary" value="<?php _e('Update Settings', $this->plugin_domain); ?>">
										</p>
									</div>
								</div>
							</div>
						</form>
					</div>
					
					
				<?php } // End sta_npc_print_admin_page function
		
				function sta_npc_settings_link($links, $file) {
					if ( basename($file) == $this->plugin_file ) {
						$settings_link = '<a href="' . admin_url('options-general.php?page=' . $this->plugin_file) . '">Settings</a>';
						array_unshift($links, $settings_link);
					}
					return $links;
				}
		
				function sta_npc_plugin_admin() {
					if ( function_exists('add_options_page') ) {
						add_options_page('No Page Comment Settings', 'No Page Comment', 'manage_options', $this->plugin_file, array( $this, 'sta_npc_print_admin_page' ));
					}
				}

				function sta_no_page_comment() {
					global $pagenow;
					global $post;
					$sta_npc_options = $this->sta_npc_get_admin_options();
					if ( (is_admin()) && ($pagenow=='post-new.php') && ($post->filter=='raw') ) {
						wp_enqueue_script('jquery');
						$posttype = $post->post_type; ?>
						
						<script type="text/javascript">
						jQuery(document).ready(function() {
							<?php if ( isset($sta_npc_options['disable_comments_' . $posttype]) ) {
								if ( $sta_npc_options['disable_comments_' . $posttype] == 'true' ) { ?>
									if ( jQuery('#comment_status').length ) {
										jQuery('#comment_status').attr('checked', false);
									}
								<?php }
							}
							if ( isset($sta_npc_options['disable_trackbacks_' . $posttype]) ) {
								if ( $sta_npc_options['disable_trackbacks_' . $posttype] == 'true' ) { ?>
									if ( jQuery('#ping_status').length ) {
										jQuery('#ping_status').attr('checked', false);
									}
								<?php }
							} ?>
						});
						</script>
				
					<?php }
				}

			}
		
		} // End Class STA_NPC_Plugin
		
		if ( class_exists('STA_NPC_Plugin') ) {
			global $sta_npc_plugin;
			$sta_npc_plugin = new STA_NPC_Plugin();
		}
		
		// Actions, Filters and Shortcodes
		if ( isset($sta_npc_plugin) ) {
			// Actions
			add_action('admin_menu', array( &$sta_npc_plugin, 'sta_npc_plugin_admin' )); // Activate admin settings page
			add_action('activate_no-page-comment/no-page-comment.php', array( &$sta_npc_plugin, 'sta_npc_init' )); // Activate admin options
			add_action( 'admin_head', array( &$sta_npc_plugin, 'sta_no_page_comment' ));
			
			// Filters
			add_filter('plugin_action_links', array( &$sta_npc_plugin, 'sta_npc_settings_link'), 10, 2 );
		}
	}
}

sta_npc_load();