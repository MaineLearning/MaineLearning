<?php

function wpv_admin_menu_import_export() {

    
    ?>    
    <div class="wrap">

        <div id="icon-views" class="icon32"><br /></div>
        <h2><?php _e('Views Import / Export', 'wpv-views') ?></h2>

        <br />
        <form name="View_export" action="<?php echo admin_url('edit.php'); ?>" method="post">
            <h2><?php _e('Export Views and View Templates', 'wpv-views'); ?></h2>
            <p><?php _e('Download all Views and View Templates', 'wpv-views'); ?></p>
            
            <p><strong><?php _e('When importing to theme:', 'wpv-views'); ?></strong></p>
            <ul style="margin-left:10px">
                <li>
                    <input id="radio-1" type="radio" value="ask" name="import-mode" checked="checked" />
                    <label for="radio-1"><?php _e('ask user for approval', 'wpv-views'); ?></label>
                </li>
                <li>
                    <input id="radio-2" type="radio" value="auto" name="import-mode" />
                    <label for="radio-2"><?php _e('import automatically', 'wpv-views'); ?></label>
                </li>
            </ul>
            <p><strong><?php _e('Affiliate details for theme designers:', 'wpv-views'); ?></strong></p>
            <table style="margin-left:10px">
                <tr>
                    <td><?php _e('Affiliate ID:', 'wpv-views'); ?></td><td><input type="text" name="aid" id="aid" style="width:200px;" /></td>
                </tr>
                <tr>
                    <td><?php _e('Affiliate Key:', 'wpv-views'); ?></td><td><input type="text" name="akey" id="akey" style="width:200px;" /></td>
                </tr>
            </table>
            <p style="margin-left:10px">
            <?php _e('You only need to enter affiliate settings if you are a theme designer and want to receive affiliate commission.', 'wpv-views'); ?>
            <br />
            <?php echo sprintf(__('Log into your account at <a href="%s">%s</a> and go to <a href="%s">%s</a> for details.', 'wpv-views'), 
                                    'http://wp-types.com',
                                    'http://wp-types.com',
                                    'http://wp-types.com/shop/account/?acct=affiliate',
                                    'http://wp-types.com/shop/account/?acct=affiliate'); ?>
            </p>
            
            <br /> 
            <input id="wpv-export" class="button-primary" type="submit" value="<?php _e('Export', 'wpv-views'); ?>" name="export" />
            
            <?php wp_nonce_field('wpv-export-nonce', 'wpv-export-nonce'); ?>

        </form>
        
        <hr />
        
        <?php wpv_admin_import_form(''); ?>
        
    </div>
    
    <?php
    
}

/**
 * Exports data to XML.
 */
function wpv_admin_export_data($download = true) {
    global $WP_Views;
    
    require_once WPV_PATH_EMBEDDED . '/common/array2xml.php';
    $xml = new ICL_Array2XML();
    $data = array();
    
    // SRDJAN - add siteurl, upload url, record taxonomies old IDs
    // https://icanlocalize.basecamphq.com/projects/7393061-wp-views/todo_items/142382866/comments
    // https://icanlocalize.basecamphq.com/projects/7393061-wp-views/todo_items/142389966/comments
    $data['site_url'] = get_site_url();
    if (is_multisite()) {
        $data['fileupload_url'] = get_option('fileupload_url');
    } else {
        $wp_upload_dir = wp_upload_dir();
        $data['fileupload_url'] = $wp_upload_dir['baseurl'];
    }

    // Get the views
    $views = get_posts('post_type=view&post_status=any&posts_per_page=-1');
    if (!empty($views)) {
        $data['views'] = array('__key' => 'view');
        foreach ($views as $key => $post) {
            $post = (array) $post;
            if ($post['post_name']) {
                $post_data = array();
                $copy_data = array('ID', 'post_content', 'post_title', 'post_name',
                    'post_excerpt', 'post_type', 'post_status');
                foreach ($copy_data as $copy) {
                    if (isset($post[$copy])) {
                        $post_data[$copy] = $post[$copy];
                    }
                }
                $data['views']['view-' . $post['ID']] = $post_data;
                $meta = get_post_custom($post['ID']);
                if (!empty($meta)) {
                    $data['view']['view-' . $post['ID']]['meta'] = array();
                    foreach ($meta as $meta_key => $meta_value) {
                        if ($meta_key == '_wpv_settings') {
                            $value = maybe_unserialize($meta_value[0]);

                            // Add any taxonomy terms so we can re-map when we import.                            
                            if (!empty($value['taxonomy_terms'])) {
                    			$taxonomy = $value['taxonomy_type'][0];
                                
                                foreach ($value['taxonomy_terms'] as $term_id) {
                                    $term = get_term($term_id, $taxonomy);
                                    $data['terms_map']['term_' . $term->term_id]['old_id'] = $term->term_id;
                                    $data['terms_map']['term_' . $term->term_id]['slug'] = $term->slug;
                                    $data['terms_map']['term_' . $term->term_id]['taxonomy'] = $taxonomy;
                                }
                            }
                            
                            $value = $WP_Views->convert_ids_to_names_in_settings($value);
                            
                            $data['views']['view-' . $post['ID']]['meta'][$meta_key] = $value;

                            
                        }
                        if ($meta_key == '_wpv_layout_settings') {
                            $value = maybe_unserialize($meta_value[0]);
                            $value = $WP_Views->convert_ids_to_names_in_layout_settings($value);
                            $data['views']['view-' . $post['ID']]['meta'][$meta_key] = $value;
                        }
                    }
                    if (empty($data['views']['view-' . $post['ID']]['meta'])) {
                        unset($data['views']['view-' . $post['ID']]['meta']);
                    }
                }
                
                // Juan - add images for exporting
		// https://icanlocalize.basecamphq.com/projects/7393061-wp-views/todo_items/150919286/comments
                
                $att_args = array( 'post_type' => 'attachment', 'numberposts' => -1, 'post_status' => null, 'post_parent' => $post['ID'] ); 
		$attachments = get_posts( $att_args );
		if ( $attachments ) {
			$data['views']['view-' . $post['ID']]['attachments'] = array();
			foreach ( $attachments as $attachment ) {
				$data['views']['view-' . $post['ID']]['attachments']['attach_'.$attachment->ID] = array();
				$data['views']['view-' . $post['ID']]['attachments']['attach_'.$attachment->ID]['title'] = $attachment->post_title;
				$data['views']['view-' . $post['ID']]['attachments']['attach_'.$attachment->ID]['content'] = $attachment->post_content;
				$data['views']['view-' . $post['ID']]['attachments']['attach_'.$attachment->ID]['excerpt'] = $attachment->post_excerpt;
				$data['views']['view-' . $post['ID']]['attachments']['attach_'.$attachment->ID]['status'] = $attachment->post_status;
				$data['views']['view-' . $post['ID']]['attachments']['attach_'.$attachment->ID]['alt'] = get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true );
				$imdata = base64_encode(file_get_contents($attachment->guid));
				$data['views']['view-' . $post['ID']]['attachments']['attach_'.$attachment->ID]['data'] = $imdata;
				preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $attachment->guid, $matches );
				$data['views']['view-' . $post['ID']]['attachments']['attach_'.$attachment->ID]['filename'] = basename( $matches[0] );
				$this_settings = get_post_meta($post['ID'], '_wpv_settings', true);
				$this_layout_settings = get_post_meta($post['ID'], '_wpv_layout_settings', true);
				if ( isset( $this_settings['pagination']['spinner_image_uploaded'] ) && $attachment->guid == $this_settings['pagination']['spinner_image_uploaded'] ) {
					$data['views']['view-' . $post['ID']]['attachments']['attach_'.$attachment->ID]['custom_spinner'] = 'this';
				}
				if ( isset( $this_settings['filter_meta_html'] ) ) {
					$pos = strpos( $this_settings['filter_meta_html'], $attachment->guid );
					if ($pos !== false) {
						$data['views']['view-' . $post['ID']]['attachments']['attach_'.$attachment->ID]['on_filter_meta_html'] = $attachment->guid;
					}
				}
				if ( isset( $this_settings['filter_meta_html_css'] ) ) {
					$pos = strpos( $this_settings['filter_meta_html_css'], $attachment->guid );
					if ($pos !== false) {
						$data['views']['view-' . $post['ID']]['attachments']['attach_'.$attachment->ID]['on_filter_meta_html_css'] = $attachment->guid;
					}
				}
				if ( isset( $this_layout_settings['layout_meta_html'] ) ) {
					$pos = strpos( $this_layout_settings['layout_meta_html'], $attachment->guid );
					if ($pos !== false) {
						$data['views']['view-' . $post['ID']]['attachments']['attach_'.$attachment->ID]['on_layout_meta_html'] = $attachment->guid;
					}
				}
				if ( isset( $this_settings['layout_meta_html_css'] ) ) {
					$pos = strpos( $this_settings['layout_meta_html_css'], $attachment->guid );
					if ($pos !== false) {
						$data['views']['view-' . $post['ID']]['attachments']['attach_'.$attachment->ID]['on_layout_meta_html_css'] = $attachment->guid;
					}
				}
			}
		}
		
            }
        }
    }

    // Get the view templates
    $view_templates = get_posts('post_type=view-template&post_status=any&posts_per_page=-1');
    if (!empty($view_templates)) {
        $data['view-templates'] = array('__key' => 'view-template');
        foreach ($view_templates as $key => $post) {
            $post = (array) $post;
            if ($post['post_name']) {
                $post_data = array();
                $copy_data = array('ID', 'post_content', 'post_title', 'post_name',
                    'post_excerpt', 'post_type', 'post_status');
                foreach ($copy_data as $copy) {
                    if (isset($post[$copy])) {
                        $post_data[$copy] = $post[$copy];
                    }
                }
                $output_mode = get_post_meta($post['ID'], '_wpv_view_template_mode', true);
                $template_extra_css = get_post_meta($post['ID'], '_wpv_view_template_extra_css', true);
                $template_extra_js = get_post_meta($post['ID'], '_wpv_view_template_extra_js', true);
                
                $post_data['template_mode'] = $output_mode;
                $post_data['template_extra_css'] = $template_extra_css;
                $post_data['template_extra_js'] = $template_extra_js;
                
                // Juan - add images for exporting
		// https://icanlocalize.basecamphq.com/projects/7393061-wp-views/todo_items/150919286/comments
		
                $att_args = array( 'post_type' => 'attachment', 'numberposts' => -1, 'post_status' => null, 'post_parent' => $post['ID'] ); 
		$attachments = get_posts( $att_args );
		if ( $attachments ) {
			$post_data['attachments'] = array();
			foreach ( $attachments as $attachment ) {
				$post_data['attachments']['attach_'.$attachment->ID] = array();
				$post_data['attachments']['attach_'.$attachment->ID]['title'] = $attachment->post_title;
				$post_data['attachments']['attach_'.$attachment->ID]['content'] = $attachment->post_content;
				$post_data['attachments']['attach_'.$attachment->ID]['excerpt'] = $attachment->post_excerpt;
				$post_data['attachments']['attach_'.$attachment->ID]['status'] = $attachment->post_status;
				$post_data['attachments']['attach_'.$attachment->ID]['alt'] = get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true );
				$imdata = base64_encode(file_get_contents($attachment->guid));
				$post_data['attachments']['attach_'.$attachment->ID]['data'] = $imdata;
				preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $attachment->guid, $matches );
				$post_data['attachments']['attach_'.$attachment->ID]['filename'] = basename( $matches[0] );
				if ( isset( $template_extra_css ) ) {
					$pos = strpos( $template_extra_css, $attachment->guid );
					if ($pos !== false) {
						$post_data['attachments']['attach_'.$attachment->ID]['on_meta_html_css'] = $attachment->guid;
					}
				}
			}
		}

                $data['view-templates']['view-template-' . $post['ID']] = $post_data;
            }
        }
    }
    
    // Get settings
    $options = get_option('wpv_options');
    if (!empty($options)) {
        foreach ($options as $option_name => $option_value) {
            if (strpos($option_name, 'view_') === 0
                    || strpos($option_name, 'views_template_') === 0) {
                $post = get_post($option_value);
                if (!empty($post)) {
                    $options[$option_name] = $post->post_name;
                }
            }
        }
        $data['settings'] = $options;
    }


    // Offer for download
    $data = $xml->array2xml($data, 'views');

    $sitename = sanitize_key(get_bloginfo('name'));
    if (!empty($sitename)) {
        $sitename .= '.';
    }
    $filename = $sitename . 'views.' . date('Y-m-d') . '.xml';
    $code = "<?php\r\n";
    $code .= '$timestamp = ' . time() . ';' . "\r\n";
    $code .= '$auto_import = ';
    $code .=  (isset($_POST['import-mode']) && $_POST['import-mode'] == 'ask') ? 0 : 1;
    $code .= ';' . "\r\n";
    if (isset($_POST['aid']) && $_POST['aid'] != '' && isset($_POST['akey']) && $_POST['aid'] != '') {
        $code .= '$affiliate_id="' . $_POST['aid'] . '";' . "\r\n";
        $code .= '$affiliate_key="' . $_POST['akey'] . '";' . "\r\n";
    }
    $code .= "\r\n?>";
    
    if (!$download) {
        return $data;
    }

    if (class_exists('ZipArchive')) { 
        $zipname = $sitename . 'views.' . date('Y-m-d') . '.zip';
        $zip = new ZipArchive();
        $file = tempnam(sys_get_temp_dir(), "zip");
        $zip->open($file, ZipArchive::OVERWRITE);
    
        $res = $zip->addFromString('settings.xml', $data);
        $zip->addFromString('settings.php', $code);
        $zip->close();
        $data = file_get_contents($file);
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=" . $zipname);
        header("Content-Type: application/zip");
        header("Content-length: " . strlen($data) . "\n\n");
        header("Content-Transfer-Encoding: binary");
        echo $data;
        unlink($file);
        die();
    } else {
        // download the xml.
        
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=" . $filename);
        header("Content-Type: application/xml");
        header("Content-length: " . strlen($data) . "\n\n");
        echo $data;
        die();
    }
}

/*
*
*   Custom Export function for Module Manager
*   Exports selected items (by ID) and of specified type (eg views, view-templates)
*   Returns xml string
*/
function wpv_admin_export_selected_data($items, $type = 'view', $mode = 'xml' ) {
    global $WP_Views;
    
    require_once WPV_PATH_EMBEDDED . '/common/array2xml.php';
    $xml = new ICL_Array2XML();
    $data = array();
    $items_hash = array();
    
    // SRDJAN - add siteurl, upload url, record taxonomies old IDs
    // https://icanlocalize.basecamphq.com/projects/7393061-wp-views/todo_items/142382866/comments
    // https://icanlocalize.basecamphq.com/projects/7393061-wp-views/todo_items/142389966/comments
//    $data['site_url'] = get_site_url();
    if (is_multisite()) {
        $upload_directory = get_option('fileupload_url');
    } else {
        $wp_upload_dir = wp_upload_dir();
        $upload_directory = $wp_upload_dir['baseurl'];
    }

    $args=array(
        'posts_per_page' => -1,
        'post_status' => 'any'
    );
    
    $export=false;
    $view_types=array(
        'view'=>array('key'=>'views'),
        'view-template'=>array('key'=>'view-templates')
    );
    
    if (is_string($items) && 'all'===$items)
    {
        $export=true;
    }
    elseif (is_array($items) && !empty($items))
    {
        $args['post__in']=$items;
        $export=true;
    }
    
    if (!in_array($type, array_keys($view_types)))
        $export=false;
    else
    {
        $args['post_type']=$type;
        $vkey=$view_types[$type]['key'];
    }
    if (!$export) return '';
    
    switch($type)
    {
         case 'view':
            // Get the views
            $views = get_posts($args);
            if (!empty($views)) {
                $data['views'] = array('__key' => 'view');
                foreach ($views as $key => $post) {
                    $post = (array) $post;
                    if ($post['post_name']) {
                        $post_data = array();
                        $copy_data = array('ID', 'post_content', 'post_title', 'post_name',
                            'post_excerpt', 'post_type', 'post_status');
                        foreach ($copy_data as $copy) {
                            if (isset($post[$copy])) {
                                $post_data[$copy] = $post[$copy];
                            }
                        }
                        $data['views']['view-' . $post['ID']] = $post_data;
                        $meta = get_post_custom($post['ID']);
                        if (!empty($meta)) {
                            $data['view']['view-' . $post['ID']]['meta'] = array();
                            foreach ($meta as $meta_key => $meta_value) {
                                if ($meta_key == '_wpv_settings') {
                                    $value = maybe_unserialize($meta_value[0]);

                                    // Add any taxonomy terms so we can re-map when we import.                            
                                    if (!empty($value['taxonomy_terms'])) {
                                        $taxonomy = $value['taxonomy_type'][0];
                                        
                                        foreach ($value['taxonomy_terms'] as $term_id) {
                                            $term = get_term($term_id, $taxonomy);
                                            $data['terms_map']['term_' . $term->term_id]['old_id'] = $term->term_id;
                                            $data['terms_map']['term_' . $term->term_id]['slug'] = $term->slug;
                                            $data['terms_map']['term_' . $term->term_id]['taxonomy'] = $taxonomy;
                                        }
                                    }
                                    
                                    $value = $WP_Views->convert_ids_to_names_in_settings($value);
                                    
                                    $data['views']['view-' . $post['ID']]['meta'][$meta_key] = $value;

                                    
                                }
                                if ($meta_key == '_wpv_layout_settings') {
                                    $value = maybe_unserialize($meta_value[0]);
                                    $value = $WP_Views->convert_ids_to_names_in_layout_settings($value);
                                    $data['views']['view-' . $post['ID']]['meta'][$meta_key] = $value;
                                }
                            }
                            if (empty($data['views']['view-' . $post['ID']]['meta'])) {
                                unset($data['views']['view-' . $post['ID']]['meta']);
                            }
                        }
                        
                        // Juan - add images for exporting
			// https://icanlocalize.basecamphq.com/projects/7393061-wp-views/todo_items/150919286/comments
			
			$att_args = array( 'post_type' => 'attachment', 'numberposts' => -1, 'post_status' => null, 'post_parent' => $post['ID'] ); 
			$attachments = get_posts( $att_args );
			if ( $attachments ) {
				$data['views']['view-' . $post['ID']]['attachments'] = array();
				foreach ( $attachments as $attachment ) {
					$data['views']['view-' . $post['ID']]['attachments']['attach_'.$attachment->ID] = array();
					$data['views']['view-' . $post['ID']]['attachments']['attach_'.$attachment->ID]['title'] = $attachment->post_title;
					$data['views']['view-' . $post['ID']]['attachments']['attach_'.$attachment->ID]['content'] = $attachment->post_content;
					$data['views']['view-' . $post['ID']]['attachments']['attach_'.$attachment->ID]['excerpt'] = $attachment->post_excerpt;
					$data['views']['view-' . $post['ID']]['attachments']['attach_'.$attachment->ID]['status'] = $attachment->post_status;
					$data['views']['view-' . $post['ID']]['attachments']['attach_'.$attachment->ID]['alt'] = get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true );
					$imdata = base64_encode(file_get_contents($attachment->guid));
					$data['views']['view-' . $post['ID']]['attachments']['attach_'.$attachment->ID]['data'] = $imdata;
					preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $attachment->guid, $matches );
					$data['views']['view-' . $post['ID']]['attachments']['attach_'.$attachment->ID]['filename'] = basename( $matches[0] );
					$this_settings = get_post_meta($post['ID'], '_wpv_settings', true);
					$this_layout_settings = get_post_meta($post['ID'], '_wpv_layout_settings', true);
					if ( isset( $this_settings['pagination']['spinner_image_uploaded'] ) && $attachment->guid == $this_settings['pagination']['spinner_image_uploaded'] ) {
						$data['views']['view-' . $post['ID']]['attachments']['attach_'.$attachment->ID]['custom_spinner'] = 'this';
					}
					if ( isset( $this_settings['filter_meta_html'] ) ) {
						$pos = strpos( $this_settings['filter_meta_html'], $attachment->guid );
						if ($pos !== false) {
							$data['views']['view-' . $post['ID']]['attachments']['attach_'.$attachment->ID]['on_filter_meta_html'] = $attachment->guid;
						}
					}
					if ( isset( $this_settings['filter_meta_html_css'] ) ) {
						$pos = strpos( $this_settings['filter_meta_html_css'], $attachment->guid );
						if ($pos !== false) {
							$data['views']['view-' . $post['ID']]['attachments']['attach_'.$attachment->ID]['on_filter_meta_html_css'] = $attachment->guid;
						}
					}
					if ( isset( $this_layout_settings['layout_meta_html'] ) ) {
						$pos = strpos( $this_layout_settings['layout_meta_html'], $attachment->guid );
						if ($pos !== false) {
							$data['views']['view-' . $post['ID']]['attachments']['attach_'.$attachment->ID]['on_layout_meta_html'] = $attachment->guid;
						}
					}
					if ( isset( $this_settings['layout_meta_html_css'] ) ) {
						$pos = strpos( $this_settings['layout_meta_html_css'], $attachment->guid );
						if ($pos !== false) {
							$data['views']['view-' . $post['ID']]['attachments']['attach_'.$attachment->ID]['on_layout_meta_html_css'] = $attachment->guid;
						}
					}
				}
			}
			if ('module_manager' == $mode ) {
				$hash_data = $data['views']['view-' . $post['ID']];
				if ( isset( $data['views']['view-' . $post['ID']]['attachments'] ) ) {
					unset( $hash_data['attachments'] );
					$hash_data['attachments'] = array();
					foreach ( $data['views']['view-' . $post['ID']]['attachments'] as $key => $attvalues ) {
						$hash_data['attachments'][] = $attvalues['data'];
						if ( isset( $attvalues['custom_spinner'] ) ) $hash_data['meta']['_wpv_settings']['pagination']['spinner_image_uploaded'] = $attvalues['data'];
						if ( isset( $attvalues['on_filter_meta_html'] ) ) $hash_data['meta']['_wpv_settings']['filter_meta_html'] = str_replace( $attvalues['on_filter_meta_html'], $attvalues['data'], $hash_data['meta']['_wpv_settings']['filter_meta_html'] );
						if ( isset( $attvalues['on_filter_meta_html_css'] ) ) $hash_data['meta']['_wpv_settings']['filter_meta_html_css'] = str_replace( $attvalues['on_filter_meta_html_css'], $attvalues['data'], $hash_data['meta']['_wpv_settings']['filter_meta_html_css'] );
						if ( isset( $attvalues['on_layout_meta_html'] ) ) $hash_data['meta']['_wpv_layout_settings']['layout_meta_html'] = str_replace( $attvalues['on_layout_meta_html'], $attvalues['data'], $hash_data['meta']['_wpv_layout_settings']['layout_meta_html'] );
						if ( isset( $attvalues['on_layout_meta_html_css'] ) ) $hash_data['meta']['_wpv_settings']['layout_meta_html_css'] = str_replace( $attvalues['on_layout_meta_html_css'], $attvalues['data'], $hash_data['meta']['_wpv_settings']['layout_meta_html_css'] );
					}
				}
				unset( $hash_data['ID'] );
				$items_hash[$post['ID']] = md5(serialize($hash_data));
			}
                    }
                }
            }
        break;
        
        case 'view-template':
            // Get the view templates
            $view_templates = get_posts($args);
            if (!empty($view_templates)) {
                $data['view-templates'] = array('__key' => 'view-template');
                foreach ($view_templates as $key => $post) {
                    $post = (array) $post;
                    if ($post['post_name']) {
                        $post_data = array();
                        $copy_data = array('ID', 'post_content', 'post_title', 'post_name',
                            'post_excerpt', 'post_type', 'post_status');
                        foreach ($copy_data as $copy) {
                            if (isset($post[$copy])) {
                                $post_data[$copy] = $post[$copy];
                            }
                        }
                        $output_mode = get_post_meta($post['ID'], '_wpv_view_template_mode', true);
                        $template_extra_css = get_post_meta($post['ID'], '_wpv_view_template_extra_css', true);
                        $template_extra_js = get_post_meta($post['ID'], '_wpv_view_template_extra_js', true);
                        
                        $post_data['template_mode'] = $output_mode;
                        $post_data['template_extra_css'] = $template_extra_css;
                        $post_data['template_extra_js'] = $template_extra_js;
                        
                        // Juan - add images for exporting
			// https://icanlocalize.basecamphq.com/projects/7393061-wp-views/todo_items/150919286/comments
			
			$att_args = array( 'post_type' => 'attachment', 'numberposts' => -1, 'post_status' => null, 'post_parent' => $post['ID'] ); 
			$attachments = get_posts( $att_args );
			if ( $attachments ) {
				$post_data['attachments'] = array();
				foreach ( $attachments as $attachment ) {
					$post_data['attachments']['attach_'.$attachment->ID] = array();
					$post_data['attachments']['attach_'.$attachment->ID]['title'] = $attachment->post_title;
					$post_data['attachments']['attach_'.$attachment->ID]['content'] = $attachment->post_content;
					$post_data['attachments']['attach_'.$attachment->ID]['excerpt'] = $attachment->post_excerpt;
					$post_data['attachments']['attach_'.$attachment->ID]['status'] = $attachment->post_status;
					$post_data['attachments']['attach_'.$attachment->ID]['alt'] = get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true );
					$imdata = base64_encode(file_get_contents($attachment->guid));
					$post_data['attachments']['attach_'.$attachment->ID]['data'] = $imdata;
					preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $attachment->guid, $matches );
					$post_data['attachments']['attach_'.$attachment->ID]['filename'] = basename( $matches[0] );
					if ( isset( $template_extra_css ) ) {
						$pos = strpos( $template_extra_css, $attachment->guid );
						if ($pos !== false) {
							$post_data['attachments']['attach_'.$attachment->ID]['on_meta_html_css'] = $attachment->guid;
						}
					}
				}
			}

                        $data['view-templates']['view-template-' . $post['ID']] = $post_data;
                        
                        if ('module_manager' == $mode ) {
				$hash_data = $post_data;
				if ( isset( $post_data['attachments'] ) ) {
					unset( $hash_data['attachments'] );
					$hash_data['attachments'] = array();
					foreach ( $post_data['attachments'] as $key => $attvalues ) {
						$hash_data['attachments'][] = $attvalues['data'];
						if ( isset( $attvalues['on_meta_html_css'] ) ) $hash_data['template_extra_css'] = str_replace( $attvalues['on_meta_html_css'], $attvalues['data'], $template_extra_css );
					}
				}
				unset( $hash_data['ID'] );
				$items_hash[$post['ID']] = md5(serialize($hash_data));
			}
                    }
                }
            }
        break;
    }
    
    // Offer for download
    $xmldata = $xml->array2xml($data, 'views');
    if ( 'xml' == $mode ) {
	return $xmldata;
    } elseif ( 'module_manager' == $mode ) {
	$export_data = array(
		'xml' => $xmldata,
		'items_hash' => $items_hash // this is an array with format [itemID] => item_hash
	);
	return $export_data;
    }
}
