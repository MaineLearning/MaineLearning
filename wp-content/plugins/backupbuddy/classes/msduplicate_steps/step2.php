<?php
check_admin_referer( 'bbms-migration', 'pb_bbms_migrate' );
if ( !current_user_can( 'manage_sites' ) ) 
	wp_die( __( 'You do not have permission to access this page.', 'it-l10n-backupbuddy' ) );
global $current_blog, $wpdb, $current_user;
$errors = false;	
$blog = $domain = $path = '';
if ( isset( $_POST[ 'add-site' ] ) ) {
	global $current_user, $base;
	$messages = array(
		'updates' => array(),
		'errors' => array()
	);
	//Code conveniently lifted from site-new.php in /wp-admin/network/
	$blog = $_POST['blog'];
	$domain = '';
	if ( ! preg_match( '/(--)/', $blog['domain'] ) && preg_match( '|^([a-zA-Z0-9-])+$|', $blog['domain'] ) )
		$domain = strtolower( $blog['domain'] );

	// If not a subdomain install, make sure the domain isn't a reserved word
	if ( ! is_subdomain_install() ) {
		$subdirectory_reserved_names = apply_filters( 'subdirectory_reserved_names', array( 'page', 'comments', 'blog', 'files', 'feed' ) );
		if ( in_array( $domain, $subdirectory_reserved_names ) ) {
			$messages[ 'errors' ][] = sprintf( __('The following words are reserved for use by WordPress functions and cannot be used as blog names: <code>%s</code>', 'it-l10n-backupbuddy' ), implode( '</code>, <code>', $subdirectory_reserved_names ) );
		}
	}
	if ( empty( $domain ) ) {
		$messages[ 'errors' ][] =  __( 'Missing or invalid site address.', 'it-l10n-backupbuddy' );
	}
	if ( is_subdomain_install() ) {
		global $current_site;
		//$current_site must be used here since sub-domains treat the current_blog's domain as ron1.domain.com whereas $current_site uses domain.com
		$newdomain = $domain . '.' . preg_replace( '|^www\.|', '', $current_site->domain );
		$path = $base;
	} else {
		$newdomain = $current_blog->domain;
		$path = $base . $domain . '/';
	}
	$blog_id = 0;
	$old_blog_id = $current_blog->blog_id;
	if ( domain_exists( $newdomain, $path, $current_blog->blog_id ) ) {
		$blog_id = domain_exists( $newdomain, $path, $current_blog->blog_id );
		$messages[ 'errors' ][] =  __( 'Site already exists.', 'it-l10n-backupbuddy' );
	} elseif ( count( $messages[ 'errors' ] ) == 0 ) {
		$messages[ 'updates' ][] = __( 'The site has been created.', 'it-l10n-backupbuddy' );
		//$blog_id = wpmu_create_blog( $newdomain, $path, 'temp title', $current_user->ID, array( 'public' => 1 ) );
	}
	 
	//Output alerts
	foreach ( $messages[ 'updates' ] as $update ) {
		$this->_parent->alert( $update );
	}
	foreach ( $messages[ 'errors' ] as $error ) {
		$this->_parent->alert( $error, true );
	}
	if ( count( $messages[ 'errors' ] ) > 0 ) {
		$errors = true;
		require_once( 'step1.php' );
	} else {
		//Create the blog
		$blog_id = wpmu_create_blog( $newdomain, $path, 'temp title', $current_user->ID, array( 'public' => 1 ) );
		
		//Copy the media
		$current_upload_dir = wp_upload_dir();
		$uploads_base_dir = $current_upload_dir[ 'basedir' ];
		$old_upload_url = $current_upload_Dir[ 'baseurl' ];
		$old_prefix = $wpdb->prefix;
		switch_to_blog( $blog_id );
		$new_site_url = site_url();
		$new_upload_dir = wp_upload_dir();
		$new_upload_dir = $new_upload_dir[ 'basedir' ];
		$new_upload_url = $current_upload_Dir[ 'baseurl' ];
		$this->copy( $uploads_base_dir, $new_upload_dir );
		
		?>
		<p><?php esc_html_e( 'Media has been copied...', 'it-l10n-backupbuddy' ); ?></p>
		<?php
		flush();
		
		//Copy over tables
		$protected_tables = array(
			$wpdb->base_prefix . 'commentmeta',
			$wpdb->base_prefix . 'comments',
			$wpdb->base_prefix . 'links',
			$wpdb->base_prefix . 'options',
			$wpdb->base_prefix . 'postmeta',
			$wpdb->base_prefix . 'posts',
			$wpdb->base_prefix . 'terms',
			$wpdb->base_prefix . 'term_relationships',
			$wpdb->base_prefix . 'term_taxonomy',
			$wpdb->base_prefix . 'blogs',
			$wpdb->base_prefix . 'blog_versions',
			$wpdb->base_prefix . 'term_taxonomy',
			$wpdb->base_prefix . 'signups',
			$wpdb->base_prefix . 'site',
			$wpdb->base_prefix . 'site_meta',
			$wpdb->base_prefix . 'users',
			$wpdb->base_prefix . 'usermeta',
		);
		$tables_to_copy = array();
		$database_name = $wpdb->dbname;
		//The regex assumes that a prefix of wp_ and wp_323 will work.  But a prefix of wp_ will not work with tables wp_323, and prefix wp_323_ won't work with wp_323_456
		$show_tables_sql = "SHOW TABLES WHERE Tables_in_{$database_name} REGEXP '^{$old_prefix}[^0-9]'";
		$results = $wpdb->get_results( $show_tables_sql );
		foreach ( $results as $index => $table_arr ) {
			foreach ( $table_arr as $table ) {
				$tables_to_copy[] = str_replace( $old_prefix, '', $table );
			}
		}
		//Drop the old tables and copy over new ones
		foreach ( $tables_to_copy as $table ) {
			$table_to_create = $wpdb->prefix . $table;
			$old_table = $old_prefix . $table;
			if ( !in_array( $table_to_create, $protected_tables ) ) { /* Skip deleting core tables*/
				$wpdb->query( sprintf( 'DROP TABLE IF EXISTS `%s`', $table_to_create ) );
			}
			$create_table_sql = sprintf( 'CREATE TABLE `%s` LIKE `%s`', $table_to_create, $old_table );
			$insert_table_sql = sprintf( 'INSERT INTO `%s` SELECT * FROM `%s`', $table_to_create, $old_table );
			
			$wpdb->query( $create_table_sql );
			$wpdb->query( $insert_table_sql );		
		} //end foreach
		//Copy the tables
		?>
		<p><?php esc_html_e( 'Database has been copied...', 'it-l10n-backupbuddy' ); ?></p>
		<?php
		flush();
		
		//Perform some URL replacements
		$queries = array();
		$queries[] = "UPDATE $wpdb->posts set post_content = replace( post_content, {$old_upload_url}, {$new_upload_url} )";
		$queries[] = "UPDATE $wpdb->posts set guid = replace( guid, {$old_upload_url}, {$new_upload_url} )";
		foreach ( $queries as $query ) {
			$wpdb->query( $query );
		}
		//Reestablish site url and stuff
		update_option( 'siteurl', $new_site_url );
		update_option( 'home', $new_site_url );
		?>
		<p><?php esc_html_e( 'URL Replacements have been made...', 'it-l10n-backupbuddy' ); ?></p>
		<?php
		flush();
		
		//Lets copy over media/upload options
		$old_site_roles = get_blog_option( $old_blog_id, $old_prefix . 'user_roles' ); //Gets user roles
		update_blog_option( $blog_id, $wpdb->prefix . 'user_roles', $old_site_roles );
		$old_site_uploads = get_blog_option( $old_blog_id, 'upload_path' ); //Gets old upload directory
		if ( $old_site_uploads ) {
			$new_site_uploads = str_replace( $old_blog_id, $blog_id, $old_site_uploads );
			update_blog_option( $blog_id, 'upload_path', $new_site_uploads );
		}
		
		//Copy over some users
		$roles = array(
			'administrator',
			'editor',
			'author',
			'contributor',
			'subscriber'
		);
		foreach ( $roles as $role ) {
			$users = (array)get_users( array( 'blog_id' => $old_blog_id, 'role' => $role ) );
			foreach ( $users as $user ) {
				$user_id = $user->ID;
				add_user_to_blog( $blog_id, $user_id, $role);
			}
		}
		
		
		
		?>
		<p><?php esc_html_e( 'Users have been migrated...', 'it-l10n-backupbuddy' ); ?></p>
		<?php 
		flush();
		$view_array = array(
			sprintf( "<a href='%s'>%s</a>", esc_url( $new_site_url ), __( 'View the site', 'it-l10n-backupbuddy' ) ),
			sprintf( "<a href='%s'>%s</a>", esc_url( admin_url() ), __( 'View the Dashboard', 'it-l10n-backupbuddy' ) ),
			sprintf( "<a href='%s'>%s</a>", esc_url( add_query_arg( array( 'id' => $blog_id ), admin_url( 'network/site-info.php' ) ) ), __( 'Edit the site', 'it-l10n-backupbuddy' ) ),
		);
			
		?>
		<p><?php esc_html_e( 'The site has been duplicated.', 'it-l10n-backupbuddy' ); ?>&nbsp;&nbsp;<?php echo implode( ' | ', $view_array ); ?></p>
		<?php
		flush();
		restore_current_blog();
	}
	
} //end add site



?>