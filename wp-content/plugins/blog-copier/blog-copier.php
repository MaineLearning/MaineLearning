<?php
/*
Plugin Name: Blog Copier
Plugin URI: http://wordpress.org/extend/plugins/blog-copier/
Description: Enables superusers to copy existing sub blogs to new sub blogs.
Version: 1.0.2
Author: Modern Tribe, Inc.
Network: true
Author URI: http://tri.be
 
Copyright: (C) 2012 Modern Tribe derived from (C) 2010 Ron Rennick, All rights reserved.

See http://wpebooks.com/replicator/ for original code.

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
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

// Block direct requests
if ( !defined('ABSPATH') )
	die('-1');

if ( !class_exists('BlogCopier') ) {

	class BlogCopier {
		
		private $_name;
		private $_domain = 'blog-copier';
		
		/**
		 * Main constructor function
		 */
		public function __construct() {
			add_action( 'network_admin_menu', array( $this, 'ms_add_page' ) );
			add_filter( 'manage_sites_action_links', array( $this, 'add_site_action' ), 10, 2 );
		}
		
		/**
		 * Add admin page to network admin menu
		 */
		public function ms_add_page() {
			$this->setup_localization();
			add_submenu_page( 'sites.php', $this->_name, $this->_name, 'manage_sites', $this->_domain, array( $this, 'admin_page' ) );
		}
		
		/**
		 * Add "Copy Blog" link under each site in the sites list view.
		 *
		 * @param array $actions 
		 * @param int $blog_id 
		 * @return array $actions
		 */
		public function add_site_action( $actions, $blog_id ) {
			if( !is_main_site( $blog_id ) ) {
				$this->setup_localization();
				$url = add_query_arg( array(
					'page' => $this->_domain,
					'blog' => $blog_id
				), network_admin_url( 'sites.php' ) );
				$nonce_string = sprintf( '%s-%s', $this->_domain, $blog_id );
				$actions[$this->_domain] = '<a href="' . esc_url( wp_nonce_url( $url, $nonce_string ) ) . '">' . __( 'Copy', $this->_domain ) . '</a>';
			}

			return $actions;
		}

		/**
		 * Admin page
		 */
		public function admin_page() {
			global $wpdb, $current_site;

			if( !current_user_can( 'manage_sites' ) )
				wp_die( __( "Sorry, you don't have permissions to use this page.", $this->_domain ) );

			$from_blog = false;
			$copy_id = 0;
			$nonce_string = sprintf( '%s-%s', $this->_domain, $copy_id );
			if( isset($_GET['blog']) && wp_verify_nonce( $_GET['_wpnonce'], $nonce_string ) ) {
				$copy_id = (int)$_GET['blog'];
				$from_blog = get_blog_details( $copy_id );
				if( $from_blog->site_id != $current_site->id ) {
					$from_blog = false;
				}
			}
			if( isset( $_POST['source_blog'] ) )
				$from_blog_id = (int) $_POST['source_blog'];

			if( isset($_POST[ 'action' ]) && $_POST[ 'action' ] == $this->_domain ) {
				check_admin_referer( $this->_domain );
				$blog = $_POST['blog'];
				$domain = sanitize_user( str_replace( '/', '', $blog[ 'domain' ] ) );
				$title = $blog[ 'title' ];
				$copy_files = (isset($_POST['copy_files']) && $_POST['copy_files'] == '1') ? true : false;

				if ( !$from_blog_id ) {
					$msg = __( 'Please select a source blog.', $this->_domain );
				} elseif ( empty( $domain ) ) {
					$msg = __( 'Please enter a "New Blog Address".', $this->_domain );
				} elseif ( empty( $title ) ) {
					$msg = __( 'Please enter a "New Blog Title".', $this->_domain );
				} else {
					$msg = $this->copy_blog( $domain, $title, $from_blog_id, $copy_files );
				}
			} else {
				$copy_files = true; // set the default for first page load
			} ?>
			<div class='wrap'><h2><?php echo $this->_name; ?></h2><?php

			if( isset( $msg ) ) { ?>
			<div id="message" class="updated fade"><p><strong><?php echo $msg; ?>
			</strong></p></div><?php
			}
			if( !$from_blog ) {
				$query = "SELECT b.blog_id, CONCAT(b.domain, b.path) as domain_path FROM {$wpdb->blogs} b " .
					"WHERE b.site_id = {$current_site->id} && b.blog_id > 1 ORDER BY domain_path ASC LIMIT 10000";

				$blogs = $wpdb->get_results( $query );
			}
			if( $from_blog || $blogs ) { ?>
			<div class="wrap">
			<h3><?php _e( 'Blog Copy Settings', $this->_domain ); ?></h3>
				<form method="POST">
					<input type="hidden" name="action" value="<?php echo $this->_domain; ?>" />
						<table class="form-table">
							
							<?php if( $from_blog ) { ?>
							<tr>
								<th scope='row'><?php _e( 'Source Blog to Copy', $this->_domain ); ?></th>
								<td><strong><?php printf( '<a href="%s" target="_blank">%s</a>', $from_blog->siteurl, $from_blog->blogname ); ?></strong>
								<input type="hidden" name="source_blog" value="<?php echo $copy_id; ?>" />
								</td>
							</tr>								
							<?php } else { ?>
							<tr class="form-required">
								<th scope='row'><?php _e( 'Choose Source Blog to Copy', $this->_domain ); ?></th>
								<td>
									<select name="source_blog">
									<?php foreach( $blogs as $blog ) { ?>
										<option value="<?php echo $blog->blog_id; ?>" <?php selected( $blog->blog_id, $from_blog_id ); ?>><?php echo substr($blog->domain_path, 0, -1); ?></option>
									<?php } ?>
									</select>
								</td>
							</tr>
							<?php } ?>
							
							<tr class="form-required">
								<th scope='row'><?php _e( 'New Blog Address', $this->_domain ); ?></th>
								<td>
								<?php if( is_subdomain_install() ) { ?>
									<input name="blog[domain]" type="text" title="<?php _e( 'Subdomain', $this->_domain ); ?>" class="regular-text"/>.<?php echo $current_site->domain;?>
								<?php } else {
									echo $current_site->domain . $current_site->path ?><input name="blog[domain]" type="text" title="<?php _e( 'Domain', $this->_domain ); ?>" class="regular-text"/>
								<?php } ?>
								</td>
							</tr>

							<tr class="form-required">
								<th scope='row'><?php _e( 'New Blog Title', $this->_domain ); ?></th>
								<td><input name="blog[title]" type="text" title="<?php _e( 'Title', $this->_domain ); ?>" class="regular-text"/></td>
							</tr>

							<tr class="form-required">
								<th scope='row'><?php _e( 'Copy Files?', $this->_domain ); ?></th>
								<td><input type="checkbox" name="copy_files" value="1" <?php checked( $copy_files ); ?>/></td>
							</tr>

						</table>
						<?php wp_nonce_field( $this->_domain ); ?>
					<p class="submit"><input class='button' type='submit' value='<?php _e( 'Copy Now', $this->_domain ); ?>' /></p>
				</form></div>
			<?php } else { ?>
				<div class="wrap">
					<h3><?php _e( 'Oops!', $this->_domain ); ?></h3>
					<p><?php
					printf( __( 'This plugin only works on subblogs. To use this you\'ll need to <a href="%s">create at least one subblog</a>.', $this->_domain ), network_admin_url( 'site-new.php' ) );
					?></p>
				</div>
			<?php }
		}

		/**
		 * Copy the blog
		 *
		 * @param string $domain url of the new blog
		 * @param string $title title of the new blog
		 * @param int $from_blog_id ID of the blog being copied from.
		 * @param bool $copy_files true if files should be copied
		 * @return string status message
		 */
		private function copy_blog($domain, $title, $from_blog_id = 0, $copy_files = true) {
			global $wpdb, $current_site, $base;

			$email = get_blog_option( $from_blog_id, 'admin_email' );
			$user_id = email_exists( sanitize_email( $email ) );
			if( !$user_id ) {
				// Use current user instead
				$user_id = get_current_user_id();
			}
			// The user id of the user that will become the blog admin of the new blog.
			$user_id = apply_filters('copy_blog_user_id', $user_id, $from_blog_id);

			if( is_subdomain_install() ) {
				$newdomain = $domain.".".$current_site->domain;
				$path = $base;
			} else {
				$newdomain = $current_site->domain;
				$path = trailingslashit($base.$domain);
			}
			
			// The new domain that will be created for the destination blog.
			$newdomain = apply_filters('copy_blog_domain', $newdomain, $domain);

			// The new path that will be created for the destination blog.
			$path = apply_filters('copy_blog_path', $path, $domain);

			$wpdb->hide_errors();
			$to_blog_id = wpmu_create_blog( $newdomain, $path, $title, $user_id , array( "public" => 1 ), $current_site->id );
			$wpdb->show_errors();

			if( !is_wp_error( $to_blog_id ) ) {
				$dashboard_blog = get_dashboard_blog();
				if( !is_super_admin() && get_user_option( 'primary_blog', $user_id ) == $dashboard_blog->blog_id )
					update_user_option( $user_id, 'primary_blog', $to_blog_id, true );

				// now copy
				if( $from_blog_id ) {
					
					$this->copy_blog_data( $from_blog_id, $to_blog_id );					
					
					if ($copy_files) {
					
						$this->copy_blog_files( $from_blog_id, $to_blog_id );
						$this->replace_content_urls( $from_blog_id, $to_blog_id );
					
					}						
					$msg = sprintf(__( 'Copied: %s in %s seconds', $this->_domain ),'<a href="http://'.$newdomain.'" target="_blank">'.$title.'</a>', number_format_i18n(timer_stop()));
					do_action( 'log', __( 'Copy Complete!', $this->_domain ), $this->_domain, $msg );
				}
			} else {
				$msg = $to_blog_id->get_error_message();
			}
			return $msg;
		}

		/**
		 * Copy blog data from one blog to another
		 *
		 * @param int $from_blog_id ID of the blog being copied from.
		 * @param int $to_blog_id ID of the blog being copied to.
		 */
		private function copy_blog_data( $from_blog_id, $to_blog_id ) {
			global $wpdb, $wp_version;
			if( $from_blog_id ) {
				$from_blog_prefix = $this->get_blog_prefix( $from_blog_id );
				$to_blog_prefix = $this->get_blog_prefix( $to_blog_id );
				$from_blog_prefix_length = strlen($from_blog_prefix);
				$to_blog_prefix_length = strlen($to_blog_prefix);

				// Grab key options from new blog.
				$saved_options = array(
					'siteurl'=>'',
					'home'=>'',
					'upload_path'=>'',
					'fileupload_url'=>'',
					'upload_url_path'=>'',
					'admin_email'=>'',
					'blogname'=>''
				);
				// Options that should be preserved in the new blog.
				$saved_options = apply_filters('copy_blog_data_saved_options', $saved_options);
				foreach($saved_options as $option_name => $option_value) {
					$saved_options[$option_name] = get_blog_option( $to_blog_id, $option_name );
				}

				// Copy over ALL the tables.
				$query = $wpdb->prepare('SHOW TABLES LIKE %s',$from_blog_prefix.'%');
				do_action( 'log', $query, $this->_domain);
				$old_tables = $wpdb->get_col($query);

				foreach ($old_tables as $k => $table) {
					$raw_table_name = substr( $table, $from_blog_prefix_length );
					$newtable = $to_blog_prefix . $raw_table_name;

					$query = $wpdb->prepare("DROP TABLE IF EXISTS {$newtable}");
					do_action( 'log', $query, $this->_domain);
					$wpdb->get_results($query);

					$query = $wpdb->prepare("CREATE TABLE IF NOT EXISTS {$newtable} LIKE {$table}");
					do_action( 'log', $query, $this->_domain);
					$wpdb->get_results($query);

					$query = $wpdb->prepare("INSERT {$newtable} SELECT * FROM {$table}");
					do_action( 'log', $query, $this->_domain);
					$wpdb->get_results($query);
				}

				// apply key opptions from new blog.
				switch_to_blog( $to_blog_id );
				foreach( $saved_options as $option_name => $option_value ) {
					if (!empty( $option_value )) {
						update_option( $option_name, $option_value );
					}
				}

				/// fix all options with the wrong prefix...
				$query = $wpdb->prepare("SELECT * FROM {$wpdb->options} WHERE option_name LIKE %s",$from_blog_prefix.'%');
				$options = $wpdb->get_results( $query );
				do_action( 'log', $query, $this->_domain, count($options).' results found.');
				if( $options ) {			
					foreach( $options as $option ) {
						$raw_option_name = substr($option->option_name,$from_blog_prefix_length);
						$wpdb->update( $wpdb->options, array( 'option_name' => $to_blog_prefix . $raw_option_name ), array( 'option_id' => $option->option_id ) );
					}
					wp_cache_flush();
				}
				
				// Fix GUIDs on copied posts
				$this->replace_guid_urls( $from_blog_id, $to_blog_id );
				
				restore_current_blog();
			}
		}
		
		/**
		 * Copy files from one blog to another.
		 *
		 * @param int $from_blog_id ID of the blog being copied from.
		 * @param int $to_blog_id ID of the blog being copied to.
		 */
		private function copy_blog_files( $from_blog_id, $to_blog_id ) {
			set_time_limit( 600 ); // 60 seconds x 10 minutes
			@ini_set('memory_limit','1024M');
			$base = WP_CONTENT_DIR . '/blogs.dir/';
			// Path to source blog files.
			$from = apply_filters('copy_blog_files_from', trailingslashit( $base . $from_blog_id ), $base, $from_blog_id);
			// Path to destination blog files.
			$to = apply_filters('copy_blog_files_to', trailingslashit( $base . $to_blog_id ), $base, $to_blog_id);
			// Shell command used to copy files.
			$command = apply_filters('copy_blog_files_command', "cp -rfp $from $to", $from, $to );
			exec($command);
		}

		/**
		 * Replace URLs in post content
		 *
		 * @param int $from_blog_id ID of the blog being copied from.
		 * @param int $to_blog_id ID of the blog being copied to.
		 */
		private function replace_content_urls( $from_blog_id, $to_blog_id ) {
			global $wpdb;
			$to_blog_prefix = $this->get_blog_prefix( $to_blog_id );
			$from_blog_url = get_blog_option( $from_blog_id, 'siteurl' );
			$to_blog_url = get_blog_option( $to_blog_id, 'siteurl' );
			$query = $wpdb->prepare( "UPDATE {$to_blog_prefix}posts SET post_content = REPLACE(post_content, '%s', '%s')", $from_blog_url, $to_blog_url );
			do_action( 'log', $query, $this->_domain);
			$wpdb->query( $query );
		}

		/**
		 * Replace URLs in post GUIDs
		 *
		 * @param int $from_blog_id ID of the blog being copied from.
		 * @param int $to_blog_id ID of the blog being copied to.
		 */
		private function replace_guid_urls( $from_blog_id, $to_blog_id ) {
			global $wpdb;
			$to_blog_prefix = $this->get_blog_prefix( $to_blog_id );
			$from_blog_url = get_blog_option( $from_blog_id, 'siteurl' );
			$to_blog_url = get_blog_option( $to_blog_id, 'siteurl' );
			$query = $wpdb->prepare( "UPDATE {$to_blog_prefix}posts SET guid = REPLACE(guid, '%s', '%s')", $from_blog_url, $to_blog_url );
			do_action( 'log', $query, $this->_domain);
			$wpdb->query( $query );
		}
		
		/**
		 * Get the database prefix for a blog
		 *
		 * @param int $blog_id ID of the blog.
		 * @return string prefix
		 */
		private function get_blog_prefix( $blog_id ) {
			global $wpdb;
			if( is_callable( array( &$wpdb, 'get_blog_prefix' ) ) ) {
				$prefix = $wpdb->get_blog_prefix( $blog_id );
			} else {
				$prefix = $wpdb->base_prefix . $blog_id . '_';
			}
			return $prefix;
		}
		
		/**
		 * Load the localization file
		 */
		private function setup_localization() {
			if ( !isset( $this->_name ) ) {
				load_plugin_textdomain( $this->_domain, false, trailingslashit(dirname(__FILE__)) . 'lang/');
				$this->_name = __( 'Blog Copier', $this->_domain );
			}
		}
				
	}
	
	global $BlogCopier;
	$BlogCopier = new BlogCopier();
}
?>