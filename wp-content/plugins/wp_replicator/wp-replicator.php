<?php
/*
Plugin Name: Site Replicator for WordPress networks
Plugin URI: http://wpebooks.com/
Description: Allows super admin to create a new sub site which is a copy of an existing sub site.
Version: 0.5
Author: Ron Rennick
Author URI: http://ronandandrea.com/
Network: true
 
*/
/* Copyright:	(C) 2010 Ron Rennick, All rights reserved.

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

function ra_copy_blog_add_pages() {
	global $wp_version;
	if( substr( $wp_version, 0, 3 ) == '3.0' )
		add_submenu_page( 'ms-admin.php', 'WP Replicator', 'WP Replicator', 'manage_sites', 'wp-replicator', 'ra_copy_blog_page' );
}
add_action('admin_menu', 'ra_copy_blog_add_pages');

function ra_replicator_add_page() {
	add_submenu_page( 'sites.php', 'WP Replicator', 'WP Replicator', 'manage_sites', 'wp-replicator', 'ra_copy_blog_page' );
}
add_action('network_admin_menu', 'ra_replicator_add_page');

function ra_replicator_filter_site_actions( $actions, $blog_id ) {
	if( !is_main_site( $blog_id ) )
		$actions['replicate'] = '<a href="' . esc_url( wp_nonce_url( network_admin_url( 'sites.php?page=wp-replicator&amp;replicate=' . $blog_id ), 'replicate-' . $blog_id ) ) . '">' . __( 'Replicate' ) . '</a>';

	return $actions;
}
add_filter( 'manage_sites_action_links', 'ra_replicator_filter_site_actions', 10, 2 );

function ra_copy_blog_page() {
	global $wpdb, $current_site;

	if( !current_user_can( 'manage_sites' ) )
		die( 'You don\'t have permissions to use this page.' );

	$replicate_id = (int)$_REQUEST['replicate'];
	$src_blog = false;
	if( $replicate_id > 0 && wp_verify_nonce( $_GET['_wpnonce'], 'replicate-' . $replicate_id ) ) {
		$src_blog = get_blog_details( $replicate_id );
		if( $src_blog->site_id != $current_site->id )
			$src_blog = false;
	}
	if( isset( $_POST['source_blog'] ) )
		$src_id = (int) $_POST['source_blog'];
	else
		$src_id = get_site_option( 'ra_default_blog', '0' );

	if( isset( $_POST[ 'clear_default' ] ) ) {
		update_site_option( 'ra_default_blog', '0' );
		$msg = __('Site to replicate set to WordPress default');
		$src_id = '0';
	} elseif( isset( $_POST[ 'default_blog' ] ) ) {
		if( $src_id ) {
			update_site_option( 'ra_default_blog', $src_id );
			$msg = __('Site to replicate updated');
		}
	} elseif( $_POST[ 'action' ] == 'copy_blog' ) {
		check_admin_referer( 'copy_blog' );
		$blog = $_POST['blog'];
		$domain = sanitize_user( str_replace( '/', '', $blog[ 'domain' ] ) );
		$email = sanitize_email( $blog[ 'email' ] );
		$title = $blog[ 'title' ];

		if ( !$src_id ) {
			$msg = __('Select a source site.');
		} elseif ( empty( $domain ) || empty( $email ) ) {
			$msg = __('Missing site address or email address.');
		} elseif( !is_email( $email ) ) {
			$msg = __('Invalid email address');
		} else {
			$msg = ra_create_blog( $email, $domain, $title, $domain, 'N/A', $src_id );
		}
	} ?>
	<div class='wrap'><h2><?php _e( 'WordPress Replicator' ); ?></h2><?php
	
	if( isset( $msg ) ) { ?>
	<div id="message" class="updated fade"><p><strong><?php echo $msg; ?>
	</strong></p></div><?php
	}
	if( !$src_blog ) {
		$query = "SELECT b.blog_id, CONCAT(b.domain, b.path) as domain_path FROM {$wpdb->blogs} b " .
			"WHERE b.site_id = {$current_site->id} && b.blog_id > 1 ORDER BY domain_path ASC LIMIT 100";

		$blogs = $wpdb->get_results( $query );
	}
	if( $src_blog || $blogs ) { ?>
	<div class="wrap">
	<h3><?php _e( 'Replicate site' ); ?></h3>
		<form method="POST">
			<input type="hidden" name="action" value="copy_blog" />
<?php		if( $src_blog ) {
			echo '<h4>' . esc_html( sprintf( __( 'Replicate %s (%s)' ), $src_blog->blogname, $src_blog->siteurl ) ) . '</h4>';
			echo '<input type="hidden" name="source_blog" value="' . $replicate_id . '" />';
		} else { ?>
			<h4 class="submit">New Registrations:&nbsp;
				<input class='button' type='submit' name="default_blog" value='Replicate the selected site' />&nbsp;
				<input class='button' type='submit' name="clear_default" value='Replicate WordPress default site' />
			</h4>
<?php		} ?>
				<table class="form-table">
<?php		if( !$src_blog ) { ?>
					<tr class="form-field form-required">
						<th style="text-align:center;" scope='row'><?php _e('Choose Source Site to Replicate'); ?></th>
						<td>
							<select name="source_blog" style="min-width:300px;">
<?php			foreach( $blogs as $blog ) { ?>
				<option value="<?php echo $blog->blog_id; ?>" <?php selected( $blog->blog_id, $src_id ); ?>><?php echo substr($blog->domain_path, 0, -1); ?></option>
<?php			} ?>
						</select></td>
					</tr>
<?php		} ?>
					<tr class="form-field form-required">
						<th style="text-align:center;" scope='row'><?php _e('New Site Address') ?></th>
						<td>
						<?php if( is_subdomain_install() ) { ?>
							<input name="blog[domain]" type="text" title="<?php _e('Domain') ?>"/>.<?php echo $current_site->domain;?>
						<?php } else {
							echo $current_site->domain . $current_site->path ?><input name="blog[domain]" type="text" title="<?php _e('Domain') ?>"/>
						<?php } ?>
						</td>
					</tr>
					<tr class="form-field form-required">
						<th style="text-align:center;" scope='row'><?php _e('New Site Title') ?></th>
						<td><input name="blog[title]" type="text" size="20" title="<?php _e('Title') ?>"/></td>
					</tr>
					<tr class="form-field form-required">
						<th style="text-align:center;" scope='row'><?php _e('New Site Admin Email') ?></th>
						<td><input name="blog[email]" type="text" size="20" title="<?php _e('Email') ?>"/></td>
					</tr>
					<tr class="form-field">
						<td colspan='2'><?php _e('A new user will be created if the above email address is not in the database.') ?><br /><?php _e('The username and password will be mailed to this email address.') ?></td>
					</tr>
				</table><?php
				wp_nonce_field( 'copy_blog' ); ?>
			<p class="submit"><input class='button' type='submit' value='Replicate Now' /></p>
		</form></div><?php
	}	
} 
function ra_create_blog($email, $domain = '', $title, $username = '', $password = 'N/A', $copy_id = 0) {
	global $wpdb, $current_site, $base, $current_user;
	
	if( !$email )
		return;

	$user_id = email_exists( sanitize_email( $email ) );
	if( !$user_id ) {
		$password = generate_random_password();
		$user_id = wpmu_create_user( $username, $password, $email );
		if( !$user_id )
			return __( 'There was an error creating the user' );

		wp_new_user_notification( $user_id, $password );
	}
	if($domain && $title) {
		if( is_subdomain_install() ) {
			$newdomain = $domain.".".$current_site->domain;
			$path = $base;
		} else {
			$newdomain = $current_site->domain;
			$path = $base.$domain.'/';
		}
		remove_action( 'wpmu_new_blog', 'ra_copy_blog', 10 );
		$wpdb->hide_errors();
		$new_id = wpmu_create_blog( $newdomain, $path, $title, $user_id , array( "public" => 1 ), $current_site->id );
		$wpdb->show_errors();
		if( !is_wp_error( $new_id ) ) {
			$dashboard_blog = get_dashboard_blog();
			if( !is_super_admin() && get_user_option( 'primary_blog', $user_id ) == $dashboard_blog->blog_id )
				update_user_option( $user_id, 'primary_blog', $new_id, true );
			$content_mail = sprintf( __( "New site created by %1s\n\nAddress: http://%2s\nName: %3s" ), $current_user->user_login , $newdomain.$path, stripslashes( $title ) );
			wp_mail( get_site_option('admin_email'),  sprintf(__('[%s] New ' . ( $is_wp30 ? 'Site' : 'Blog' ) . ' Created'), $current_site->site_name), $content_mail, 'From: "Site Admin" <' . get_site_option( 'admin_email' ) . '>' );
			wpmu_welcome_notification( $new_id, $user_id, $password, $title, array( "public" => 1 ) );
			// now copy
			if( $copy_id ) {
				ra_copy_blog( $new_id, $copy_id, $user_id );
				$msg = __('Replicated');
			}
		} else {
			$msg = $new_id->get_error_message();
		}
	}
	return $msg;
}

function ra_copy_blog( $new_id, $copy_id = 0, $user_id = 0 ) {
	global $wpdb, $wp_version;
	if( !$copy_id )
		$copy_id = get_site_option( 'ra_default_blog', false );

	if( $copy_id ) {
		if( !$user_id ) {
			$user = wp_get_current_user();
			$user_id = $user->ID;
		}
		if( is_callable( array( &$wpdb, 'get_blog_prefix' ) ) ) {
			$blogtables = $wpdb->get_blog_prefix( $copy_id );
			$newtables = $wpdb->get_blog_prefix( $new_id );
		} else {
			$blogtables = $wpdb->base_prefix . $copy_id . '_';
			$newtables = $wpdb->base_prefix . $new_id . '_';
		}
		$query = "SHOW TABLES LIKE '{$blogtables}%'";

		$tables = $wpdb->get_results($query, ARRAY_A);
		if($tables) {
			reset($tables);
			$create = array();
			$data = array();
			$len = strlen($blogtables);
			$create_col = 'Create Table';
			// add std wp tables to this array
			$wptables = array($blogtables . 'links', $blogtables . 'postmeta', $blogtables . 'posts',
				$blogtables . 'terms', $blogtables . 'term_taxonomy', $blogtables . 'term_relationships');
			for($i = 0;$i < count($tables);$i++) {
				$table = current($tables[$i]);
				if(substr($table,0,$len) == $blogtables) {
					if(!($table == $blogtables . 'options' || $table == $blogtables . 'comments' || $table == $blogtables . 'commentmeta')) {
						$create[$table] = $wpdb->get_row("SHOW CREATE TABLE {$table}");
						$data[$table] = $wpdb->get_results("SELECT * FROM {$table}", ARRAY_A);
					}
				}
			}

			if($data) {
				switch_to_blog($copy_id);
				$src_url = get_option('siteurl');
				$option_query = "SELECT option_name, option_value, autoload FROM {$wpdb->options}";
				restore_current_blog();
				$new_url = get_blog_option($new_id, 'siteurl');
				foreach($data as $k => $v) {
					$table = str_replace($blogtables, $newtables, $k);
					if(in_array($k, $wptables)) { // drop new blog table
						$query = "DROP TABLE IF EXISTS {$table}";
						$wpdb->query($query);
					}
					$key = (array) $create[$k];
					$query = str_replace($blogtables, $newtables, $key[$create_col]);
					$wpdb->query($query);
					$is_post = ($k == $blogtables . 'posts');
					if($v) {
						foreach($v as $row) {
							if($is_post) {
								$row['guid'] = str_replace($src_url,$new_url,$row['guid']);
								$row['post_content'] = str_replace($src_url,$new_url,$row['post_content']);
								$row['post_author'] = $user_id;
								$row['comment_count'] = 0;
							}
							$wpdb->insert($table, $row);
						}
					}
				}
				// copy media
				$cp_base = ABSPATH . UPLOADBLOGSDIR . '/';
				$cp_cmd = str_replace( '//', '/', 'cp -r ' . $cp_base . $copy_id . ' ' . $cp_base . $new_id );

				exec($cp_cmd);
				// update options
				$skip_options = array( 'admin_email','blogname','blogdescription','cron','db_version','doing_cron',
					'fileupload_url','home','nonce_salt','random_seed','rewrite_rules','secret','siteurl','upload_path',
					'upload_url_path' );
				$options = $wpdb->get_results( $option_query );
				if( $options ) {
					$opt_pattern = "|^{$blogtables}(.*)$|";
					switch_to_blog( $new_id );
					foreach( $options as $o ) {
						if( in_array( $o->option_name, $skip_options ) || substr( $o->option_name, 0, 6 ) == '_trans' )
							continue;
						
						$option_name = preg_replace( $opt_pattern, $newtables . '$1', $o->option_name );
						if( substr( $o->option_value, 0, 2 ) == 'O:' ) {
							$option_id = $wpdb->get_var( $wpdb->prepare( "SELECT option_id FROM {$wpdb->options} WHERE option_name = %s", $option_name ) );
							if( empty( $option_id ) ) 
								$wpdb->update( $wpdb->options, array( 'autoload' => $o->autoload, 'option_value' => $o->option_value ), array( 'option_id' => $option_id ) );
							else
								$wpdb->insert( $wpdb->options, array( 'option_name' => $option_name, 'autoload' => $o->autoload, 'option_value' => $o->option_value ) );
						} else
							update_option( $option_name, maybe_unserialize( $o->option_value ) );
					}
					update_option('rewrite_rules', '');

					// drop comments & meta
					$wpdb->query( "DELETE FROM {$wpdb->comments}" );
					$wpdb->query( "DELETE FROM {$wpdb->comment_meta}" );
					restore_current_blog();
				}
			}
		}
	}
}
function ra_new_blog_copy_blog( $blog_id, $user_id = 0 ) {
	if( $blog_id )
		ra_copy_blog( $blog_id, 0, $user_id );
}
add_action( 'wpmu_new_blog', 'ra_new_blog_copy_blog', 10, 2 );
?>