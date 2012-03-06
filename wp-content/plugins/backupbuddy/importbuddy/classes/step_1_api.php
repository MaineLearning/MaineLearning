<?php
class pluginbuddy_importbuddy_step_1 {
	function __construct( &$parent ) {
		$this->_parent = &$parent;
	}
	
	
	/**
	 *	upload()
	 *
	 *	Processes uploaded backup file.
	 *
	 *	@return		array		True on upload success; false otherwise.
	 */
	function upload() {
		if ( isset( $_POST['upload'] ) && ( $_POST['upload'] == 'local' ) ) {
			if ( $this->_parent->_options['password'] != '#PASSWORD#' ) {
				$path_parts = pathinfo( $_FILES['file']['name'] );
				if ( ( strtolower( substr( $_FILES['file']['name'], 0, 6 ) ) == 'backup' ) && ( strtolower( $path_parts['extension'] ) == 'zip' ) ) {
					if ( move_uploaded_file( $_FILES['file']['tmp_name'], basename( $_FILES['file']['name'] ) ) ) {
						$this->_parent->alert( 'File Uploaded', 'Your backup was successfully uploaded.' );
						return true;
					} else {
						$this->_parent->alert( 'Error', 'Sorry, there was a problem uploading your file.' );
						return false;
					}
				} else {
					$this->_parent->alert( 'Error', 'Only properly named BackupBuddy zip archives with a zip extension may be uploaded.' );
					return false;
				}
			} else {
				$this->_parent->alert( 'Upload Access Denied', self::UPLOAD_ACCESS_DENIED );
				return false;
			}
		}
	}
	
	
	/**
	 *	get_archives_list()
	 *
	 *	Returns an array of backup archive zip filenames found.
	 *
	 *	@return		array		Array of .zip filenames; path NOT included.
	 */
	function get_archives_list() {
		// List backup files in this directory.
		$backup_archives = glob( ABSPATH . 'backup*.zip' );
		if ( !is_array( $backup_archives ) || empty( $backup_archives ) ) { // On failure glob() returns false or an empty array depending on server settings so normalize here.
			$backup_archives = array();
		}
		foreach( $backup_archives as $backup_id => $backup_archive ) {
			$backup_archives[$backup_id] = basename( $backup_archive );
		}
		
		return $backup_archives;
	}
	
	
	/**
	 *	wordpress_exists()
	 *
	 *	Notifies the user with an alert if WordPress appears to already exist in this directory.
	 *
	 *	@return		boolean		True if WordPress already exists; false otherwise.
	 */
	function wordpress_exists() {
		if ( file_exists( ABSPATH . 'wp-config.php' ) ) {
			$this->_parent->log( 'Found existing WordPress installation.', 'warning' );
			$this->_parent->alert( 'WARNING: Existing WordPress installation found.', 'It is strongly recommended that existing WordPress files and database be removed prior to migrating or restoring to avoid conflicts. You should not install WordPress prior to migrating.' );
			return true;
		} else {
			return false;
		}
	}
}
?>