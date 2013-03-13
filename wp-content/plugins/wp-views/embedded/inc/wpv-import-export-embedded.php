<?php

function wpv_admin_import_form($file_name) {

    ?>

    <form name="View_import" enctype="multipart/form-data" action="" method="post">
        <?php if ($file_name != ''): ?>
            <h2><?php _e('Import Views and View Templates for your Theme',
                    'wpv-views'); ?></h2>
        <?php else: ?>
            <h2><?php _e('Import Views and View Templates',
                'wpv-views'); ?></h2>
    <?php endif; ?>

        <p><strong><?php _e('Settings:',
            'wpv-views'); ?></strong></p>
        <ul style="margin-left:10px">
            <li>
                <input id="checkbox-1" type="checkbox" name="views-overwrite" />
                <label for="checkbox-1"><?php _e('Bulk overwrite if View exists',
            'wpv-views'); ?></label>
            </li>
            <li>
                <input id="checkbox-2" type="checkbox" name="views-delete" />
                <label for="checkbox-2"><?php _e('Delete any existing Views that are not in the import',
            'wpv-views'); ?></label>
            </li>
            <li>
                <input id="checkbox-3" type="checkbox" name="view-templates-overwrite" />
                <label for="checkbox-3"><?php _e('Bulk overwrite if View Template exists',
            'wpv-views'); ?></label>
            </li>
            <li>
                <input id="checkbox-4" type="checkbox" name="view-templates-delete" />
                <label for="checkbox-4"><?php _e('Delete any existing View Templates that are not in the import',
            'wpv-views'); ?></label>
            </li>
            <li>
                <input id="checkbox-5" type="checkbox" name="view-settings-overwrite" />
                <label for="checkbox-5"><?php _e('Overwrite Views settings',
            'wpv-views'); ?></label>
            </li>
        </ul>
        <label for="upload-views-file"><?php __('Select the views xml file to upload from your computer:&nbsp;',
            'wpv-views'); ?></label>

    <?php if ($file_name != ''): ?>
            <input type="hidden" id="upload-views-file" name="import-file" value="<?php echo $file_name; ?>" />
    <?php else: ?>
            <input type="file" id="upload-views-file" name="import-file" />
    <?php endif; ?>

        <input id="wpv-import" class="button-primary" type="submit" value="<?php _e('Import',
            'wpv-views'); ?>" name="import" />

        <input type="hidden" name="page" value="views-import-export" />
    <?php wp_nonce_field('wpv-import-nonce',
            'wpv-import-nonce'); ?>

    </form>

    <?php
}

$import_errors = null;
$import_messages = array();

function wpv_admin_menu_import_export_hook() {
    if (isset($_POST['export']) && $_POST['export'] == __('Export', 'wpv-views') &&
            isset($_POST['wpv-export-nonce']) &&
            wp_verify_nonce($_POST['wpv-export-nonce'], 'wpv-export-nonce')) {
        wpv_admin_export_data();
        die();
    }

    if (isset($_POST['import']) && $_POST['import'] == __('Import', 'wpv-views') &&
            isset($_POST['wpv-import-nonce']) &&
            wp_verify_nonce($_POST['wpv-import-nonce'], 'wpv-import-nonce')) {
        global $import_errors, $import_messages;
        $import_errors = wpv_admin_import_data();
        if ($import_errors) {
            add_action('admin_notices', 'wpv_import_errors');
        }
        if (sizeof($import_messages)) {
            add_action('admin_notices', 'wpv_import_messages');

            global $wpv_theme_import, $wpv_theme_import_xml;
            if (isset($wpv_theme_import) && $wpv_theme_import != '') {
                include $wpv_theme_import;

                update_option('views-embedded-import', $timestamp);
            }
        }
    }
}

function wpv_admin_import_data() {
    global $WP_Views;

    if (isset($_FILES['import-file'])) {
        $file = $_FILES['import-file'];
    } else {
        $file = null;
    }

    if ($file == null) {
        // check for import from settings.xml in theme
        if (isset($_POST['import-file'])) {
            $file = array();
            $file['name'] = $_POST['import-file'];
            $file['tmp_name'] = $_POST['import-file'];
            $file['size'] = filesize($file['tmp_name']);
        }
    }
    
    if ( empty( $file['name'] ) ){
	return new WP_Error('could_not_open_file', __('Could not read the Views import file.',
                                'wpv-views'));
    }

    $data = array();
    $info = pathinfo($file['name']);
    $is_zip = $info['extension'] == 'zip' ? true : false;
    if ($is_zip) {
        $zip = zip_open(urldecode($file['tmp_name']));
        if (is_resource($zip)) {
            while (($zip_entry = zip_read($zip)) !== false) {
                if (zip_entry_name($zip_entry) == 'settings.xml') {
                    $data = @zip_entry_read($zip_entry,
                                    zip_entry_filesize($zip_entry));
                }
            }
        } else {
            return new WP_Error('could_not_open_file', __('Unable to open zip file', 'wpv-views'));
        }
    } else {
        $fh = fopen($file['tmp_name'], 'r');
        if ($fh) {
            $data = fread($fh, $file['size']);
            fclose($fh);
        }
    }
    
    if (!empty($data)) {

        if (!function_exists('simplexml_load_string')) {
            return new WP_Error('xml_missing', __('The Simple XML library is missing.',
                                    'wpv-views'));
        }
        $xml = simplexml_load_string($data);

        if (!$xml) {
            return new WP_Error('not_xml_file', sprintf(__('The XML file (%s) could not be read.',
                                            'wpv-views'), $file['name']));
        }

        $import_data = wpv_admin_import_export_simplexml2array($xml);
        

        // import view templates first.   
        $error = wpv_admin_import_view_templates($import_data);
        if ($error) {
            return $error;
        }

        // import views next.   
        $error = wpv_admin_import_views($import_data);
        if ($error) {
            return $error;
        }

        // import views next.   
        $error = wpv_admin_import_settings($import_data);
        if ($error) {
            return $error;
        }
    } else {
        return new WP_Error('could_not_open_file', __('Could not read the Views import file.',
                                'wpv-views'));
    }
}

/*
*
*   Custom Import function for Module Manager
*   Imports given xml string
*/
function wpv_admin_import_data_from_xmlstring($xmlstring, $items = array(), $import_type = null) {
    global $WP_Views;
    
    if (!empty($xmlstring)) {

        if (!function_exists('simplexml_load_string')) {
            return new WP_Error('xml_missing', __('The Simple XML library is missing.',
                                    'wpv-views'));
        }
        $xml = simplexml_load_string($xmlstring);

        if (!$xml) {
            return new WP_Error('not_xml_file', sprintf(__('The XML could not be read.',
                                            'wpv-views')));
        }

        $import_data = wpv_admin_import_export_simplexml2array($xml);
        
        if ( isset( $import_type ) ) {

		// import view templates
		if ( 'view-templates' == $import_type )
		{
			$result = wpv_admin_import_view_templates($import_data, $items);
			if ($result) {
			return $result;
			}
		}
		// import views
		elseif ( 'views' == $import_type )
		{  
			$result = wpv_admin_import_views($import_data, $items);
			if ($result) {
			return $result;
			}
		}
		// defined but not known $import_type
		else
		{
			$results = array(
				'updated' => 0,
				'new' => 0,
				'failed' => 0,
				'errors' => array()
			);
			return $results;
		}
        
        }

    } else { // empty xml string
	$results = array(
		'updated' => 0,
		'new' => 0,
		'failed' => 0,
		'errors' => array()
	);
	return $results;
    }
}


function wpv_admin_import_view_templates($import_data, $items = array()) {

    global $wpdb, $import_messages;

    $imported_view_templates = array();
    $overwrite_count = 0;
    $new_count = 0;
    $results = array(
	'updated' => 0,
	'new' => 0,
	'failed' => 0,
	'errors' => array()
    );
    $newitems = array();
    
    if (false!==$items && is_array($items))
    {
	$import_items=array();
	foreach ($items as $item)
	{
		$import_items[]=str_replace(_VIEW_TEMPLATES_MODULE_MANAGER_KEY_,'',$item);
	}
    }

    if (isset($import_data['view-templates']['view-template'])) {
        $view_templates = $import_data['view-templates']['view-template'];

        // check for a single view template
        if (!isset($view_templates[0])) {
            $view_templates = array($view_templates);
        }

        foreach ($view_templates as $view_template) {

			$output_mode = '';
			if (isset($view_template['template_mode'])) {
				$output_mode = $view_template['template_mode'];
				unset($view_template['template_mode']);
			}
			$template_extra_css = '';
			if (isset($view_template['template_extra_css'])) {
				$template_extra_css = $view_template['template_extra_css'];
				unset($view_template['template_extra_css']);
			}
			$template_extra_js = '';
			if (isset($view_template['template_extra_js'])) {
				$template_extra_js = $view_template['template_extra_js'];
				unset($view_template['template_extra_js']);
			}
			$template_images = array();
			if ( isset( $view_template['attachments'] ) ) {
				$template_images = array($view_template['attachments']);
				unset( $view_template['attachments'] );
			}
			
            $post_to_update = $wpdb->get_var($wpdb->prepare(
                            "SELECT ID FROM $wpdb->posts
                                WHERE post_name = %s AND post_type = %s",
                            $view_template['post_name'], 'view-template'));

            $idflag = 0;
            $id_to_import = $view_template['ID'];
            
			if ($post_to_update) {
                $imported_view_templates[] = $post_to_update;

                // only update if we have overwrite enabled or if the template ID is in the importing array from Module Manager
                if ( ( isset( $_POST['view-templates-overwrite'] ) && $_POST['view-templates-overwrite'] == 'on') || in_array( $view_template['ID'], $import_items ) ) {
                    $view_template['ID'] = $post_to_update;
                    $id = wp_update_post($view_template);
                    if (!$id) {
			if ( in_array( $old_view_id, $import_items ) ) { // if using Module Manager
				$results['failed'] += 1;
				$results['errors'][] = sprintf(__('Failed to update view-template - %s.',
                                                        'wpv-views'),
                                                $view_template['post_name']);
			} else { // normal import
				return new WP_Error('could_not_update_post', sprintf(__('Failed to update view template - %s.',
                                                        'wpv-views'),
                                                $view_template['post_name']));
			}
                    } else {
			$overwrite_count++;
			$idflag = $id;
		    }
                }
            } elseif ( empty( $import_items ) || ( !empty( $import_items ) && in_array( $view_template['ID'], $import_items) ) ) {
                // it's a new view template: create it
                // create if array $import_items is empty (normal import) or if array is not empty and the View template is on this array (Module Manager)
                $old_view_template_id = $view_template['ID'];
                unset($view_template['ID']);
                $id = wp_insert_post($view_template, true);
                if (is_object($id)) {
                    // it's an WP_Error object.
                    if ( in_array( $old_view_template_id, $import_items ) ) { // if using Module Manager
				$results['failed'] += 1;
				$results['errors'][] = sprintf(__('Failed to create view - %s.',
                                                        'wpv-views'),
                                                $view['post_name']);
			} else { // normal import
				return $id;
			}
                } else {
			$imported_view_templates[] = $id;
			$new_count++;
			$idflag = $id;
		}
            }
			
			if ($idflag && $output_mode != '') {
				
				update_post_meta($id, '_wpv_view_template_mode', $output_mode);
				
			}
			if ($idflag && $template_extra_css != '') {
				
				update_post_meta($id, '_wpv_view_template_extra_css', $template_extra_css);
					
			}
			if ($idflag && $template_extra_js != '') {
					
				update_post_meta($id, '_wpv_view_template_extra_js', $template_extra_js);
					
			}
			
			// Juan - add images importing
			// https://icanlocalize.basecamphq.com/projects/7393061-wp-views/todo_items/150919286/comments
		
			if ( $idflag && !empty( $template_images ) ) {
				$upload_dir = wp_upload_dir();
				$upload_path = $upload_dir['basedir'];
				$upload_directory = $upload_dir['baseurl'];
				$path_flag = true;
				if (!is_dir($upload_path . DIRECTORY_SEPARATOR . 'views-import-temp')) {
					mkdir($upload_path . DIRECTORY_SEPARATOR . 'views-import-temp');
				} else {
					$path_flag = false;  // if folder already existed
				}
				include_once( ABSPATH . 'wp-admin/includes/file.php' );
				include_once( ABSPATH . 'wp-admin/includes/media.php' );
				include_once( ABSPATH . 'wp-admin/includes/image.php');
				foreach ( $template_images as $attach_array ) {
					foreach ( $attach_array as $attach ) {
						if ( isset( $attach['data'] ) && isset( $attach['filename'] ) ) {
							//  decode attachment data and create the file
							$imgdata = base64_decode($attach['data']);
							file_put_contents( $upload_path . DIRECTORY_SEPARATOR . 'views-import-temp' . DIRECTORY_SEPARATOR . $attach['filename'], $imgdata );
							// upload the file using WordPress API and add it to the post as attachment
							// preserving all fields but alt
							$tmp = download_url( $upload_directory . '/views-import-temp/' . $attach['filename'] );
							$file_array['name'] = $attach['filename'];
							$file_array['tmp_name'] = $tmp;
							if ( is_wp_error( $tmp ) ) {
								@unlink( $file_array['tmp_name'] );
								$file_array['tmp_name'] = '';
							}
							$att_data = array();
							if ( isset( $attach['title'] ) ) $att_data['post_title'] = $attach['title'];
							if ( isset($attach['content'] ) ) $att_data['post_content'] = $attach['content'];
							if ( isset($attach['excerpt'] ) ) $att_data['post_excerpt'] = $attach['excerpt'];
							if ( isset($attach['status'] ) ) $att_data['post_status'] = $attach['status'];
							$att_id = media_handle_sideload( $file_array, $id, null, $att_data );
							if ( is_wp_error($att_id) ) {
								@unlink( $file_array['tmp_name'] );
								return $att_id;
							}
							// update alt field
							if ( isset( $attach['alt'] ) ) update_post_meta( $att_id, '_wp_attachment_image_alt', $attach['alt'] );
							@unlink( $upload_path . DIRECTORY_SEPARATOR . 'views-import-temp' . DIRECTORY_SEPARATOR . $attach['filename'] );
							$att_attributes = wp_get_attachment_image_src( $att_id, 'full');
							if ( isset( $attach['on_meta_html_css'] ) ) {
								$template_extra_css = str_replace( $attach['on_meta_html_css'], $att_attributes[0], $template_extra_css );
								update_post_meta( $id, '_wpv_view_template_extra_css', $template_extra_css );
							}
						}
     					}
				}
				if ( $path_flag ) rmdir($upload_path . DIRECTORY_SEPARATOR . 'views-import-temp');
			}
			
		if ( $idflag ) {
		$newitems[$id_to_import] = $idflag;
	    }
        }
    }

    $deleted_count = 0;
    if (isset($_POST['view-templates-delete']) && $_POST['view-templates-delete'] == 'on') {
        $view_templates_to_delete = get_posts('post_type=view-template&post_status=any&posts_per_page=-1');
        if (!empty($view_templates_to_delete)) {
            foreach ($view_templates_to_delete as $view_template_to_delete) {
                if (!in_array($view_template_to_delete->ID,
                                $imported_view_templates)) {
                    wp_delete_post($view_template_to_delete->ID, true);
                    $deleted_count++;
                }
            }
        }
    }

    $import_messages[] = sprintf(__('%d View Templates found in the file. %d have been created and %d have been over written.',
                    'wpv-views'), sizeof($imported_view_templates), $new_count,
            $overwrite_count);

    if ($deleted_count) {
        $import_messages[] = sprintf(__('%d existing View Templates were deleted.',
                        'wpv-views'), $deleted_count);
    }
    
    $results['updated'] = $overwrite_count;
    $results['new'] = $new_count;

	if ( empty( $import_items ) ) { // normal import
		return false; // no errors
	} else { // Module Manager import
		$results['items'] = $newitems;
		return $results;
	}
}

function wpv_admin_import_views($import_data, $items = array()) {

    global $wpdb, $import_messages, $WP_Views;

    $imported_views = array();
    $overwrite_count = 0;
    $new_count = 0;
    $results = array(
	'updated' => 0,
	'new' => 0,
	'failed' => 0,
	'errors' => array()
    );
    $newitems = array();
    
    if (false!==$items && is_array($items))
    {
	$import_items=array();
	foreach ($items as $item)
	{
		$import_items[]=str_replace(_VIEWS_MODULE_MANAGER_KEY_,'',$item);
	}
    }
    
    if (isset($import_data['views']['view'])) {
        $views = $import_data['views']['view'];

        // check for a single view
        if (!isset($views[0])) {
            $views = array($views);
        }


        foreach ($views as $view) {

            $meta = $view['meta'];
            unset($view['meta']);
            
		$view_images = array();
		if ( isset( $view['attachments'] ) ) {
			$view_images = array($view['attachments']);
			unset( $view['attachments'] );
		}
            
            // SRDJAN - https://icanlocalize.basecamphq.com/projects/7393061-wp-views/todo_items/142389966/comments
            // Fix URLs
            if (!empty($import_data['site_url']) && !empty($import_data['fileupload_url'])) {
                    if (!empty($meta['_wpv_settings']['pagination']['spinner_image'])) {
                        $meta['_wpv_settings']['pagination']['spinner_image'] = WPV_URL_EMBEDDED . '/res/img/' . basename($meta['_wpv_settings']['pagination']['spinner_image']);
                    }
                    if (!empty($meta['_wpv_settings']['pagination']['spinner_image_uploaded'])) {
                        $old_custom_spinner = $meta['_wpv_settings']['pagination']['spinner_image_uploaded']; // keep it for comparing in the new images importing flow
                        $meta['_wpv_settings']['pagination']['spinner_image_uploaded'] = wpv_convert_url($meta['_wpv_settings']['pagination']['spinner_image_uploaded'],
                                $import_data['site_url'],
                                $import_data['fileupload_url']);
                    }
            }
            
            // SRDJAN - fix term_ids
            // https://icanlocalize.basecamphq.com/projects/7393061-wp-views/todo_items/142382866/comments
            if (!empty($meta['_wpv_settings']['taxonomy_terms']['taxonomy_term'])) {
                foreach ($meta['_wpv_settings']['taxonomy_terms']['taxonomy_term'] as $term_key => $old_term_id) {
                    if (isset($import_data['terms_map']['term_' . $old_term_id])) {
                        $new_term = get_term_by('slug',
                                $import_data['terms_map']['term_' . $old_term_id]['slug'],
                                $import_data['terms_map']['term_' . $old_term_id]['taxonomy']);
                        if (!empty($new_term)) {
                            $meta['_wpv_settings']['taxonomy_terms']['taxonomy_term'][$term_key] = $new_term->term_id;
                        }
                    }
                }
            }

            if (isset($meta['_wpv_settings'])) {
                $meta['_wpv_settings'] = $WP_Views->convert_names_to_ids_in_settings($meta['_wpv_settings']);
            }
            if (isset($meta['_wpv_layout_settings'])) {
                $meta['_wpv_layout_settings'] = $WP_Views->convert_names_to_ids_in_layout_settings($meta['_wpv_layout_settings']);
            }
            
            $idflag = 0;  // add flag to know if we are overwritting or creating something
            $id_to_import = $view['ID'];

            $post_to_update = $wpdb->get_var($wpdb->prepare(
                            "SELECT ID FROM $wpdb->posts
                                WHERE post_name = %s AND post_type = %s",
                            $view['post_name'], 'view'));

            if ($post_to_update) {
                $imported_views[] = $post_to_update;

                // only update if we have overwrite enabled (normal import) or if the View is on the $import_items array (Module Manager)
                if ( ( isset( $_POST['views-overwrite'] ) && $_POST['views-overwrite'] == 'on' ) || in_array( $view['ID'], $import_items ) ) {
                    $old_view_id = $view['ID'];
                    $view['ID'] = $post_to_update;
                    $id = wp_update_post($view);
                    if (!$id) {
			if ( in_array( $old_view_id, $import_items ) ) { // if using Module Manager
			$results['failed'] += 1;
			$results['errors'][] = sprintf(__('Failed to update view - %s.',
                                                        'wpv-views'),
                                                $view['post_name']);
			} else { // normal import
                        return new WP_Error('could_not_update_post', sprintf(__('Failed to update view - %s.',
                                                        'wpv-views'),
                                                $view['post_name']));
			}
                    } else {
                    $idflag = $id;
                    $overwrite_count++;
                    if (isset($meta['_wpv_settings'])) {
                        update_post_meta($id, '_wpv_settings',
                                $meta['_wpv_settings']);
                    }
                    if (isset($meta['_wpv_layout_settings'])) {
                        update_post_meta($id, '_wpv_layout_settings',
                                $meta['_wpv_layout_settings']);
                    }
                    
                    do_action('wpv_view_imported', $old_view_id, $id);
                    }
                }
            } elseif ( empty( $import_items ) || ( !empty( $import_items ) && in_array( $view['ID'], $import_items) ) ) {
                // it's a new view: create it
                // create if array $import_items is empty (normal import) or if array is not empty and the View is on this array (Module Manager)
                $old_view_id = $view['ID'];
                unset($view['ID']);
                $id = wp_insert_post($view, true);
                if (is_object($id)) {
                    // it's an WP_Error object.
			if ( in_array( $old_view_id, $import_items ) ) { // if using Module Manager
				$results['failed'] += 1;
				$results['errors'][] = sprintf(__('Failed to create view - %s.',
                                                        'wpv-views'),
                                                $view['post_name']);
			} else { // normal import
				return $id;
			}
                } else {
                $idflag = $id;
                $new_count++;
                $imported_views[] = $id;

                if (isset($meta['_wpv_settings'])) {
                    update_post_meta($id, '_wpv_settings',
                            $meta['_wpv_settings']);
                }
                if (isset($meta['_wpv_layout_settings'])) {
                    update_post_meta($id, '_wpv_layout_settings',
                            $meta['_wpv_layout_settings']);
                }
                do_action('wpv_view_imported', $old_view_id, $id);
                }
            }
            
		// Juan - add images importing
		// https://icanlocalize.basecamphq.com/projects/7393061-wp-views/todo_items/150919286/comments
            
	    if ( $idflag && !empty( $view_images ) ) {
		$upload_dir = wp_upload_dir();
		$upload_path = $upload_dir['basedir'];
		$upload_directory = $upload_dir['baseurl'];
		$path_flag = true;
		if (!is_dir($upload_path . DIRECTORY_SEPARATOR . 'views-import-temp')) {
			mkdir($upload_path . DIRECTORY_SEPARATOR . 'views-import-temp');
		} else {
			$path_flag = false;  // if folder already existed
		}
		include_once( ABSPATH . 'wp-admin/includes/file.php' );
		include_once( ABSPATH . 'wp-admin/includes/media.php' );
		include_once( ABSPATH . 'wp-admin/includes/image.php');
		foreach ( $view_images as $attach_array ) {
			foreach ( $attach_array as $attach ) {
				if ( isset( $attach['data'] ) && isset( $attach['filename'] ) ) {
					//  decode attachment data and create the file
					$imgdata = base64_decode($attach['data']);
					file_put_contents( $upload_path . DIRECTORY_SEPARATOR . 'views-import-temp' . DIRECTORY_SEPARATOR . $attach['filename'], $imgdata );
					// upload the file using WordPress API and add it to the post as attachment
					// preserving all fields but alt
					$tmp = download_url( $upload_directory . '/views-import-temp/' . $attach['filename'] );
					$file_array['name'] = $attach['filename'];
					$file_array['tmp_name'] = $tmp;
					if ( is_wp_error( $tmp ) ) {
						@unlink( $file_array['tmp_name'] );
						$file_array['tmp_name'] = '';
					}
					$att_data = array();
					if ( isset( $attach['title'] ) ) $att_data['post_title'] = $attach['title'];
					if ( isset($attach['content'] ) ) $att_data['post_content'] = $attach['content'];
					if ( isset($attach['excerpt'] ) ) $att_data['post_excerpt'] = $attach['excerpt'];
					if ( isset($attach['status'] ) ) $att_data['post_status'] = $attach['status'];
					$att_id = media_handle_sideload( $file_array, $id, null, $att_data );
					if ( is_wp_error($att_id) ) {
						@unlink( $file_array['tmp_name'] );
						return $att_id;
					}
					// update alt field
					if ( isset( $attach['alt'] ) ) update_post_meta( $att_id, '_wp_attachment_image_alt', $attach['alt'] );
					@unlink( $upload_path . DIRECTORY_SEPARATOR . 'views-import-temp' . DIRECTORY_SEPARATOR . $attach['filename'] );
					// set spinner image and attached images added to MetaHTML boxes
					$att_attributes = wp_get_attachment_image_src( $att_id, 'full');
					if ( isset( $attach['custom_spinner'] ) && 'this' == $attach['custom_spinner'] ) {
						$meta['_wpv_settings']['pagination']['spinner_image_uploaded'] = $att_attributes[0];
						update_post_meta( $id, '_wpv_settings', $meta['_wpv_settings'] );
					}
					if ( isset( $attach['on_filter_meta_html'] ) ) {
						$meta['_wpv_settings']['filter_meta_html'] = str_replace( $attach['on_filter_meta_html'], $att_attributes[0], $meta['_wpv_settings']['filter_meta_html'] );
						update_post_meta( $id, '_wpv_settings', $meta['_wpv_settings'] );
					}
					if ( isset( $attach['on_filter_meta_html_css'] ) ) {
						$meta['_wpv_settings']['filter_meta_html_css'] = str_replace( $attach['on_filter_meta_html_css'], $att_attributes[0], $meta['_wpv_settings']['filter_meta_html_css'] );
						update_post_meta( $id, '_wpv_settings', $meta['_wpv_settings'] );
					}
					if ( isset( $attach['on_layout_meta_html'] ) ) {
						$meta['_wpv_layout_settings']['layout_meta_html'] = str_replace( $attach['on_layout_meta_html'], $att_attributes[0], $meta['_wpv_layout_settings']['layout_meta_html'] );
						update_post_meta( $id, '_wpv_layout_settings', $meta['_wpv_layout_settings'] );
					}
					if ( isset( $attach['on_layout_meta_html_css'] ) ) {
						$meta['_wpv_settings']['layout_meta_html_css'] = str_replace( $attach['on_layout_meta_html_css'], $att_attributes[0], $meta['_wpv_settings']['layout_meta_html_css'] );
						update_post_meta( $id, '_wpv_settings', $meta['_wpv_settings'] );
					}
				}
			}
		}
		if ( $path_flag ) rmdir($upload_path . DIRECTORY_SEPARATOR . 'views-import-temp');
	    }
	    
	    if ( $idflag ) {
		$newitems[$id_to_import] = $idflag;
	    }
            
        }
    }

    $deleted_count = 0;
    if (isset($_POST['views-delete']) && $_POST['views-delete'] == 'on') {
        $views_to_delete = get_posts('post_type=view&post_status=any&posts_per_page=-1');
        if (!empty($views_to_delete)) {
            foreach ($views_to_delete as $view_to_delete) {
                if (!in_array($view_to_delete->ID, $imported_views)) {
                    wp_delete_post($view_to_delete->ID, true);
                    $deleted_count++;
                }
            }
        }
    }

    $import_messages[] = sprintf(__('%d Views found in the file. %d have been created and %d have been over written.',
                    'wpv-views'), sizeof($imported_views), $new_count,
            $overwrite_count);

    if ($deleted_count) {
        $import_messages[] = sprintf(__('%d existing Views were deleted.',
                        'wpv-views'), $deleted_count);
    }
    
    $results['updated'] = $overwrite_count;
    $results['new'] = $new_count;
    
	if ( empty( $import_items ) ) { // normal import
		return false; // no errors
	} else { // Module Manager import
		$results['items'] = $newitems;
		return $results;
	}
}

function wpv_admin_import_settings($data) {
    global $WP_Views, $import_messages, $wpdb;
    if (isset($_POST['view-settings-overwrite'])) {
        $options = $WP_Views->get_options();
        // Reset options
        foreach ($options as $option_name => $option_value) {
            if (is_numeric($option_value)) {
                $options[$option_name] = 0;
            } else {
                $options[$option_name] = '';
            }
        }
        // Set exported options
        if (!empty($data['settings'])) {
            foreach ($data['settings'] as $option_name => $option_value) {
                if (strpos($option_name, 'view_') === 0
                        || strpos($option_name, 'views_template_') === 0) {
                    $post_type = strpos($option_name, 'view_') === 0 ? 'view' : 'view-template';
                    
                    if ($option_value) {
						$post_id = $wpdb->get_var($wpdb->prepare(
                                    "SELECT ID FROM $wpdb->posts
                                        WHERE post_name = %s AND post_type = %s",
                                    $option_value, $post_type));
					} else {
						$post_id = 0;
					}
                    
                    if ($post_id) {
                        $options[$option_name] = $post_id;
                    } else {
                        $options[$option_name] = 0;
						if ($option_value) {
							$import_messages[] = sprintf(__('%s could not be found', 'wpv-views'), $post_type . ' ' . $option_value);
						}
                    }
                } else {
                    $options[$option_name] = $option_value;
                }
            }
        }
        $WP_Views->save_options($options);
        $import_messages[] = __('Settings updated', 'wpv-views');
    }
    return false; // no errors
}

function wpv_import_errors() {
    global $import_errors;

    ?>
    <div class="message error"><p><?php echo $import_errors->get_error_message() ?></p></div>
    <?php
}

function wpv_import_messages() {
    global $import_messages;

    foreach ($import_messages as $message) {

        ?>
        <div class="message updated"><p><?php echo $message ?></p></div>
        <?php
    }
}

/**
 * Loops over elements and convert to array or empty string.
 * 
 * @param type $element
 * @return string 
 */
function wpv_admin_import_export_simplexml2array($element) {
    $element = is_string($element) ? trim($element) : $element;
    if (!empty($element) && is_object($element)) {
        $element = (array) $element;
    }
    // SRDJAN - slider settings that have 0 values are imported as empty string https://icanlocalize.basecamphq.com/projects/7393061-wp-views/todo_items/142382765/comments
    if (!is_array($element) && strval($element) == '0') {
        $element = 0;
    } else if (empty($element)) {
        $element = '';
    } else if (is_array($element)) {
        foreach ($element as $k => $v) {
            $v = is_string($v) ? trim($v) : $v;
            if (!is_array($v) && strval($v) == '0') {
                $element[$k] = 0;
            } else if (empty($v)) {
                $element[$k] = '';
                continue;
            }
            $add = wpv_admin_import_export_simplexml2array($v);
            if (!is_array($add) && strval($add) == '0') {
                $element[$k] = 0;
            } else if (!empty($add)) {
                $element[$k] = $add;
            } else {
                $element[$k] = '';
            }
        }
    }

    if (!is_array($element) && strval($element) == '0') {
        $element = 0;
    } else if (empty($element)) {
        $element = '';
    }

    return $element;
}

/**
 * Converts URLs.
 * 
 * @param type $url
 * @param type $site_url
 * @param type $upload_url
 * @return type 
 */
function wpv_convert_url($url, $site_url, $upload_url) {
    // Check if uploaded files URL or other URL
    if (strpos($url, (string) $upload_url) !== false) {
        $upload_dir = wp_upload_dir();
        $url = str_replace((string) $upload_url,
                $upload_dir['baseurl'], $url);
    } else if (strpos($url, (string) $site_url) !== false) {
        $url = str_replace((string) $site_url,
                get_site_url(), $url);
    }
    return $url;
}