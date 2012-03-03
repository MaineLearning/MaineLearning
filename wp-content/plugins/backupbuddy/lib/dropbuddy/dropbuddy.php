<?php
if ( !class_exists( "pluginbuddy_dropbuddy" ) ) {
	ini_set( 'include_path', DEFAULT_INCLUDE_PATH . PATH_SEPARATOR  . dirname( __FILE__ ) . '/pear_includes' );
	
	include( 'dropbox_api/autoload.php' );
	
	class pluginbuddy_dropbuddy {
		var $_key  = '0hss3jh8kmdrcgr';
		var $_secret = '8u40d9dn6t4gv18';
		
		function pluginbuddy_dropbuddy( &$parent, &$token ) {
			$this->_parent = &$parent;
			$this->_token = &$token;
			//echo 'token:<pre>';
			//print_r( $this->_token );
			//echo '</pre>!';
			if ( !isset( $this->_token['access'] ) ) {
				$this->_token['access'] = false;
				$this->_token['request'] = false;
				//echo 'tokennew:<pre>';
				//print_r( $this->_token );
				//echo '</pre>!';
			}

		}
		
		function authenticate() {
			$oauth = new Dropbox_OAuth_PEAR( $this->_key, $this->_secret );
			
			/*
			if ( $this->_token['request'] === false ) {
				echo 'authenticatemakingtoken...';
				$this->_token['request'] = $oauth->getRequestToken();
				echo '<pre>';
				print_r( $this->_token );
				echo '</pre>';
				//$this->_parent->save();
			}
			*/
			
			if ( $this->_token['access'] === false ) { // Need to get a token if we dont have access yet.
				try {
					//echo 'Getting_Token.';
					//echo '<pre>';
					//print_r( $this->_token );
					//echo '</pre>';
					$oauth->setToken( $this->_token['request'] );
					$this->_token['access'] = $oauth->getAccessToken();
					$this->_parent->save();
				} catch ( Exception $e ) { // Authorization failed. No token.
					//echo 'Access_Denied.';
					$this->_token['access'] = false;
				}
				//$this->_parent->save();
			} else {
				$oauth->setToken( $this->_token['access'] );
			}
			$this->_dropbox = new Dropbox_API( $oauth );
			
			return $this->is_authorized();
		}
		
		function get_authorize_url() {
			$oauth = new Dropbox_OAuth_PEAR( $this->_key, $this->_secret );
			
			$this->_token['request'] = $oauth->getRequestToken();
			$this->_parent->save();
			
			//echo 'authorizeurltoken:<pre>';
			//print_r( $this->_token );
			//echo '</pre>';
			
			return str_replace( 'api.', 'www.', $oauth->getAuthorizeUrl() );
		}
		
		function get_account_info() {
			return $this->_dropbox->getAccountInfo();
		}
		
		function get_meta_data( $path ) {
			try {
				return $this->_dropbox->getMetaData( $path );
			} catch ( Exception $e ) {
				return 'The specified path does not exist.';
			}
			
		}
		
		// Remote path includes filename.  Ex: backupbuddy\file.zip
		// @return true on success, array of results on failure.
		function put_file( $remote_path, $file ) {
			return $this->_dropbox->putFile( $remote_path, $file );
		}
		
		function get_file( $path ) {
			return $this->_dropbox->getFile( $path );
		}
		
		function delete( $path ) {
			return $this->_dropbox->delete( $path );
		}
		
		function is_authorized() {
			return $this->_token['access'] && $this->get_account_info();
		}
	} // End class
	
	//$pluginbuddy_dropbuddy = new pluginbuddy_dropbuddy( $this->_options['backup_directory'] );
}