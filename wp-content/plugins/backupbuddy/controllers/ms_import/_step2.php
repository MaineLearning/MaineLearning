<?php
if ( isset( $_POST[ 'add-site' ] ) ) {
	global $current_user, $base;
	global $current_blog;
	$messages = array(
		'updates' => array(),
		'errors' => array()
	);
	//Code conveniently lifted from site-new.php in /wp-admin/network/
	$blog = $_POST['blog'];
	
	$domain = '';
	$blog_domain = trim( $blog[ 'domain' ], '/' );
	$blog_domain = str_replace( 'https://', '', $blog_domain );
	$blog_domain = str_replace( 'http://', '', $blog_domain );
	//$blog_domain = str_replace( 'www.', '', $blog_domain );
	//if ( ( !preg_match( '/(--)/', $blog_domain ) && preg_match( '|^([a-zA-Z0-9-])+$|', $blog_domain ) ) || domain_exists( $blog_domain, '/', $current_blog->blog_id ) ) {
		$domain = strtolower( $blog_domain );
	//}
	
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
	if ( !isset( $_POST['backup_file'] ) ) {
		$messages[ 'errors' ][] =  __( 'Missing backup file.', 'it-l10n-backupbuddy' );
	}
	if ( is_subdomain_install() ) {
		if ( domain_exists( $blog_domain, '/', $current_blog->blog_id ) ) {
			$newdomain = $blog_domain;
			$path = '/';
		} else {
			//$newdomain = $domain . '.' . preg_replace( '|^www\.|', '', $current_blog->domain );
			$newdomain = $domain;
			$path = $base;
		}
	} else {
		$newdomain = $current_blog->domain;
		$path = $base . $domain . '/';
	}
	$blog_id = 0;
	if ( domain_exists( $newdomain, $path, $current_blog->blog_id ) ) {
		$blog_id = domain_exists( $newdomain, $path, $current_blog->blog_id );
		$messages[ 'errors' ][] = __( 'This site address already exists.  Please choose another name or delete the existing site first.', 'it-l10n-backupbuddy' );
	} else {
		if ( count( $messages[ 'errors' ] ) <= 0 ) {
			$messages[ 'updates' ][] = __( 'The site has been created. Click `Continue` to use this site.', 'it-l10n-backupbuddy' );
			$blog_id = wpmu_create_blog( $newdomain, $path, 'temp title', $current_user->ID, array( 'public' => 1 ) );
		}
	}
	
	// PARSE ADVANCED OPTIONS
	foreach( $_POST['advanced_options'] as $advanced_option_name => $advanced_option_value ) {
		$this->advanced_options[$advanced_option_name] = $advanced_option_value;
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
		require_once( '_step1.php' );
	}
	
} //end add site

// Upload paths, etc.
//$upload_path = $this->get_ms_option( $blog_id, 'upload_path' );
//$fileupload_url = $this->get_ms_option( $blog_id, 'fileupload_url' );

if ( count( $messages[ 'errors' ] ) <= 0 ) :
$form_url = add_query_arg( array(
	'step' => '3',
	'action' => 'step3'
) , $this->_selfLink . '-msimport' );
?>
<form method="post" action="<?php echo esc_url( $form_url ); ?>">
<?php wp_nonce_field( 'bbms-migration', 'pb_bbms_migrate' ); ?>

<p><?php esc_html_e( 'Please verify that you are sure you would like to import site content into the following site. This cannot be undone.', 'it-l10n-backupbuddy' ); ?></p>
<p>
<?php
if ( is_subdomain_install() ) { 
	if ( domain_exists( $blog_domain, '/', $current_blog->blog_id ) ) {
		//$newdomain = $blog_domain;
		$newdomain = $domain;
		$path = $blog_domain . '/';
	} else {
		$path = $domain . '.' . preg_replace( '|^www\.|', '', $current_blog->domain );
	}
	?>
	<?php echo '<strong>http://' . $path . '</strong>'; ?>
<?php } else {
	echo 'http://' . $current_blog->domain . '<strong>' . $path . '</strong>'; ?>
<?php }?>
</p>
<input type="hidden" name="backup_file" value="<?php echo htmlentities( $_POST['backup_file'] ) ?>">
<input type='hidden' name='blog_id' value='<?php echo esc_attr( absint( $blog_id ) ); ?>' />
<input type='hidden' name='blog_path' value='<?php echo esc_attr( $path ); ?>' />
<input type='hidden' name='global_options' value='<?php echo base64_encode( serialize( $this->advanced_options ) ); ?>' />
<?php submit_button( __('Next Step') . ' &raquo;', 'primary', 'add-site' ); ?>
</form>
<?php endif; ?>