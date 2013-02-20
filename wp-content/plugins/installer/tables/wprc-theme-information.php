<?php
class WPRC_ThemeInformation
{

	public static function wprc_install_theme_information() {
		global $tab, $themes_allowedtags, $wp_list_table, $wp_version;

		if (version_compare($wp_version,'3.4','>='))
		{
			$theme = themes_api( 'theme_information', array( 'slug' => stripslashes( $_REQUEST['theme'] ) ) );

			if ( is_wp_error( $theme ) )
				wp_die( $theme );

			$wp_list_table=WPRC_Loader::getListTable('theme-install');
			iframe_header( __('Theme Install','installer') );
			$wp_list_table->theme_installer_single( $theme );
			iframe_footer();
			exit;
		}
		else
		{
			$api = themes_api('theme_information', array('slug' => stripslashes( $_REQUEST['theme'] ) ));

			if ( is_wp_error($api) )
				wp_die($api);

			// Sanitize HTML
			foreach ( (array)$api->sections as $section_name => $content )
				$api->sections[$section_name] = wp_kses($content, $themes_allowedtags);

			foreach ( array('version', 'author', 'requires', 'tested', 'homepage', 'downloaded', 'slug') as $key ) {
				if ( isset($api->$key) )
					$api->$key = wp_kses($api->$key, $themes_allowedtags);
			}

			iframe_header( __('Theme Install','installer') );

			/*if ( empty($api->download_link) ) {
				echo '<div id="message" class="error"><p>' . __('<strong>ERROR:</strong> This theme is currently not available. Please try again later.') . '</p></div>';
				iframe_footer();
				exit;
			}*/

			if ( !empty($api->tested) && version_compare($GLOBALS['wp_version'], $api->tested, '>') )
				echo '<div class="updated"><p>' . __('<strong>Warning:</strong> This theme has <strong>not been tested</strong> with your current version of WordPress.','installer') . '</p></div>';
			else if ( !empty($api->requires) && version_compare($GLOBALS['wp_version'], $api->requires, '<') )
				echo '<div class="updated"><p>' . __('<strong>Warning:</strong> This theme has not been marked as <strong>compatible</strong> with your version of WordPress.','installer') . '</p></div>';

			// Default to a "new" theme
			$type = 'install';
			// Check to see if this theme is known to be installed, and has an update awaiting it.
			$update_themes = get_site_transient('update_themes');
			if ( false!=$update_themes && is_object($update_themes) && isset($update_themes->response) ) {
				foreach ( (array)$update_themes->response as $theme_slug => $theme_info ) {
					if ( $theme_slug === $api->slug  && $theme_info['new_version']==$api->version && ((isset($api->download_link) && !empty($api->download_link)) || (isset($api->package) && !empty($api->package)))) {
						if ((isset($api->download_link) && !empty($api->download_link)))
						{
							$update_themes->response[$theme_slug]['package']=$api->download_link;
							set_site_transient('update_themes',$update_themes);
                            $theme_info['package']=$update_themes->response[$theme_slug]['package'];
						}
						else if ((isset($api->package) && !empty($api->package)))
						{
							$update_themes->response[$theme_slug]['package']=$api->package;
							set_site_transient('update_themes',$update_themes);
                            $theme_info['package']=$update_themes->response[$theme_slug]['package'];
						}
					}
                if ( $theme_slug === $api->slug  && (isset($theme_info['package']) && !empty($theme_info['package']))) {
                    $type = 'update_available';
                    $update_file = $theme_slug;
                    break;
				}
			}
            }

			$themes = get_themes();
			foreach ( (array) $themes as $this_theme ) {
				if ( is_array($this_theme) && $this_theme['Stylesheet'] == $api->slug ) {
					if ( $this_theme['Version'] == $api->version ) {
						$type = 'latest_installed';
					} elseif ( $this_theme['Version'] > $api->version ) {
						$type = 'newer_installed';
						$newer_version = $this_theme['Version'];
					}
					break;
				}
			}
		?>

		<div class='available-theme'>
		<img src='<?php echo esc_url($api->screenshot_url) ?>' width='300' class="theme-preview-img" />
		<h3><?php echo $api->name; ?></h3>
		<p><?php printf(__('by %s','installer'), $api->author); ?></p>
		<p><?php printf(__('Version: %s','installer'), $api->version); ?></p>

		<?php
		$buttons = '<a class="button" id="cancel" href="#" onclick="tb_close();return false;">' . __('Cancel','installer') . '</a> ';
		if (($type=='newer_installed' || $type='latest_installed') || !empty($api->download_link))
		{
		switch ( $type ) {
		default:
		case 'install':
			if ( current_user_can('install_themes') ) :
			$theme_url=wp_nonce_url(self_admin_url('update.php?action=install-theme&theme=' . $api->slug), 'install-theme_' . $api->slug);
            if (isset($api->repository_id))
                $theme_url=add_query_arg(array('repository_id'=>$api->repository_id),$theme_url);
            $buttons .= '<a class="button-primary" id="install" href="' . $theme_url . '" target="_parent">' . __('Install Now','installer') . '</a>';
			endif;
			break;
		case 'update_available':
			if ( current_user_can('update_themes') ) :
			$theme_url=wp_nonce_url(self_admin_url('update.php?action=upgrade-theme&theme=' . $update_file), 'upgrade-theme_' . $update_file);
            if (isset($api->repository_id))
                $theme_url=add_query_arg(array('repository_id'=>$api->repository_id),$theme_url);
			$buttons .= '<a class="button-primary" id="install"	href="' . $theme_url . '" target="_parent">' . __('Install Update Now','installer') . '</a>';
			endif;
			break;
		case 'newer_installed':
			if ( current_user_can('install_themes') || current_user_can('update_themes') ) :
			?><p><?php printf(__('Newer version (%s) is installed.','installer'), $newer_version); ?></p><?php
			endif;
			break;
		case 'latest_installed':
			if ( current_user_can('install_themes') || current_user_can('update_themes') ) :
			?><p><?php _e('This version is already installed.','installer'); ?></p><?php
			endif;
			break;
		}
		}
		/*elseif (isset($api->message) && !empty($api->message))
		{
			echo wp_kses($api->message, $themes_allowedtags);
		}*/
		if ( isset($api->message) && ! empty($api->message) && ( current_user_can('install_themes') || current_user_can('update_themes') ) ) {
					//echo wp_kses($api->message, $plugins_allowedtags);
					$message=WPRC_Functions::formatMessage($api->message);
					if (isset($api->message_type) && $api->message_type=='notify')
						WPRC_AdminNotifier::addMessage('wprc-theme-info-'.$api->slug,$message);
					else
						echo $message;
		}
		elseif (!($type=='newer_installed' || $type='latest_installed') && (isset($api->purchase_link) && !empty($api->purchase_link) && isset($api->price) && !empty($api->price)))
		{
			if ( current_user_can('install_themes') )
			{
			$purl=WPRC_Functions::sanitizeURL($api->purchase_link);
			$return_url=rawurlencode(admin_url( 'theme-install.php?tab=theme-information&repository_id='. $api->repository_id .'&theme=' . $api->slug));
			$salt=rawurlencode($api->salt);
			if (strpos($purl,'?'))
				$url_glue='&';
			else
				$url_glue='?';
			$purl.=$url_glue.'return_to='.$return_url.'&rsalt='.$salt;
			$buttons .= '<a class="button-primary" id="install" href="' . $purl . '">' . sprintf(__('Buy %s','installer'),'('.$api->currency->symbol.$api->price.' '.$api->currency->name.')') . '</a>';
			
			}
		}
		?>
		<br class="clear" />
		</div>

		<p class="action-button">
		<?php echo $buttons; ?>
		<br class="clear" />
		</p>
		<?php if (isset($api->rauth) && $api->rauth==false) { ?>
		<p><?php _e('Authorization Failed!','installer'); ?></p>
		<?php } ?>

		<?php
			iframe_footer();
			exit;
		}
	}
	
	
	public static function wprc_update_themes()
	{
		global $wp_version;
		$WP_UPDATE_THEMES_URL=WPRC_WP_THEMES_UPDATE_REPO;
		
		//include ABSPATH . WPINC . '/version.php'; // include an unmodified $wp_version

		if ( defined( 'WP_INSTALLING' ) )
			return false;

		//if ( !function_exists( 'get_themes' ) )
			//require_once( ABSPATH . 'wp-includes/theme.php' );

		//$installed_themes = get_themes( );
		$last_update = get_site_transient( 'update_themes' );
		if ( ! is_object($last_update) )
			$last_update = new stdClass;

		// Check for updated every 60 minutes if hitting update pages; else, check every 12 hours.
		$timeout = in_array( current_filter(), array( 'load-themes.php', 'load-update.php', 'load-update-core.php' ) ) ? 3600 : 43200;
		$time_not_changed = isset( $last_update->last_checked ) && $timeout > ( time( ) - $last_update->last_checked );

		//$themes = array();
		$checked = array();
		$exclude_fields = array('Template Files', 'Stylesheet Files', 'Status', 'Theme Root', 'Theme Root URI', 'Template Dir', 'Stylesheet Dir', 'Description', 'Tags', 'Screenshot');

		// Put slug of current theme into request.
		//$themes['current_theme'] = get_option( 'stylesheet' );

		// get themes data from the DB
        $extensions_model = WPRC_Loader::getModel('extensions');
        $extensions = $extensions_model->getFullExtensionsTree();
		$installed_themes=$extensions['themes'];
		// arrange themes according to repository (if enabled)
		$repos=array();
		$current_theme=get_option( 'stylesheet' );
		foreach ($installed_themes as $key=>$theme)
		{
			// if theme repository is enabled then push theme to the repository list
			//if ($theme['repository_enabled']!==false)
			{
				$checked[$theme['Stylesheet']] = $theme['Version'];
				
				$rkey=$theme['repository_endpoint_url']===null?WPRC_WP_THEMES_REPO:$theme['repository_endpoint_url'];
				
				$sendtheme=$theme;
				// remove unwanted fields from the theme that is going to be sent
				unset($sendtheme['repository_endpoint_url']);
				unset($sendtheme['repository_id']);
				unset($sendtheme['repository_user']);
				unset($sendtheme['repository_pass']);
				unset($sendtheme['repository_salt']);
				unset($sendtheme['repository_name']);
				unset($sendtheme['repository_enabled']);
				unset($sendtheme['repository_deleted']);
				unset($sendtheme['extension_was_installed']);
				unset($sendtheme['type_name']);
				unset($sendtheme['extension_slug']);
				foreach ( (array) $theme as $key2 => $value ) {
					if ( in_array($key2, $exclude_fields) )
						unset($sendtheme[$key2]);
				}
				
				if (!isset($repos[$rkey]))
				{
					$repos[$rkey]=array(
                    'id'=>$theme['repository_id'],
					'url'=>$rkey,
					'user'=>$theme['repository_user'],
					'pass'=>$theme['repository_pass'],
					'salt'=>$theme['repository_salt'],
					'name'=>$theme['repository_name'],
					'themes'=>array(		// Put slug of current theme into request.
								'current_theme'=> $current_theme,
								$sendtheme['Stylesheet']=>$sendtheme)
					);
					// Wordpress uses different install repository URL from Update Repository URL so hardcode it
					if ($rkey===WPRC_WP_THEMES_REPO)
						$repos[$rkey]['url']=$WP_UPDATE_THEMES_URL;
				}
				else
					$repos[$rkey]['themes'][$sendtheme['Stylesheet']]=$sendtheme;
			}
		}

		$theme_changed = false;
		foreach ( $checked as $slug => $v ) {
			$update_request->checked[ $slug ] = $v;

			if ( !isset( $last_update->checked[ $slug ] ) || strval($last_update->checked[ $slug ]) !== strval($v) )
				$theme_changed = true;
		}

		if ( isset ( $last_update->response ) && is_array( $last_update->response ) ) {
			foreach ( $last_update->response as $slug => $update_details ) {
				if ( ! isset($checked[ $slug ]) ) {
					$theme_changed = true;
					break;
				}
			}
		}

		if ( $time_not_changed && !$theme_changed )
			return false;

		// Update last_checked for current to prevent multiple blocking requests if request hangs
		$last_update->last_checked = time();
		set_site_transient( 'update_themes', $last_update );

		$allresponses=array();
		//WPRC_Loader::includeSecurity();
		foreach ($repos as $repo)
		{
			$to_send=$repo['themes'];
            $server_url = $repo['url'];
            $repository_username = $repo['user'];
            $repository_password = $repo['pass'];
			$repository_salt = $repo['salt'];
			
			// action is themes-update by default
			$body_array = array(
                'action' => 'themes_update',
            );
            
			// add authorization if needed
			if($repository_username<>'' && $repository_username<>null && $repository_password<>'' && $repository_password<>null)
            {
				//$sendpassword=WPRC_Security::encrypt($repository_salt,$repository_password);
				$body_array['auth'] = array('user'=>$repository_username,'pass'=>$repository_password,'salt'=>$repository_salt);
            }
			else
			{
				unset($body_array['auth']);
			}
            
			$body_array['themes'] = serialize( $to_send );
			if ($server_url===$WP_UPDATE_THEMES_URL)
			{
				unset($body_array['action']);
			}
			
			$options = array(
				'timeout' => ( ( defined('DOING_CRON') && DOING_CRON ) ? 30 : 3),
				'body' => $body_array,
				'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo( 'url' )
			);

            // debug log
            $reqargs=$body_array;
            if (isset($reqargs['auth'])) 
            {
                $reqargs['auth']='AUTH info';
            }
            $msg=sprintf("THEME UPDATE API Request to %s, timeout: %d, wpversion: %s, request args: %s",$server_url,$options['timeout'],$wp_version,print_r($reqargs,true));
            WPRC_Functions::log($msg,'api','api.log');
            unset($reqargs);
			// send request
			$raw_response = wp_remote_post($server_url, $options);

            // log
            $msg=sprintf("THEME UPDATE API Request to %s, response: %s",$server_url,print_r($raw_response,true));
            WPRC_Functions::log($msg,'api','api.log');
			
            if ( is_wp_error( $raw_response ) || 200 != wp_remote_retrieve_response_code( $raw_response ) )
            {
                // log
                if (is_wp_error( $raw_response ))
                {
                    $msg=sprintf("THEME UPDATE API Request to %s, response error: %s",$server_url,print_r($raw_response->get_error_message(),true));
                    WPRC_Functions::log($msg,'api','api.log');
                }
				continue; //return false;
            }

			$response = @unserialize( wp_remote_retrieve_body( $raw_response ) );
			
			// merge with other results
            if ( false === $response )
            {
                // log
                $msg=sprintf("THEME UPDATE API Request to %s, response unserialize failed: %s",$server_url,print_r(wp_remote_retrieve_body( $raw_response ),true));
                WPRC_Functions::log($msg,'api','api.log');
            }
            else if (is_object($response) && isset($response->error))
            {
                $response = new WP_Error('extensions_api_failed', $response->error, wp_remote_retrieve_body( $raw_response ) );
                // log
                $msg=sprintf("THEME UPDATE API Request to %s, action not implemented error: %s",$server_url, print_r($response,true));
                WPRC_Functions::log($msg,'api','api.log');
            }
            else if (is_array($response) && isset($response['error']))
            {
                $response = new WP_Error('extensions_api_failed', $response['error'], wp_remote_retrieve_body( $raw_response ) );
                // log
                $msg=sprintf("THEME UPDATE API Request to %s, action not implemented error: %s",$server_url, print_r($response,true));
                WPRC_Functions::log($msg,'api','api.log');
            }
			else
			{
				// add some info about repository id etc..
				foreach ($response as $key=>$them)
				{
					if (is_object($response[$key]))
					{
					$response[$key]->repository_id=$repo['id'];
					$response[$key]->repository_salt=$repo['salt'];
					}
					else if (is_array($response[$key]))
					{
					$response[$key]['repository_id']=$repo['id'];
					$response[$key]['repository_salt']=$repo['salt'];
					}
					//if (version_compare($wp_version,'3.4','<'))
					{
					$response[$key]=(array)$response[$key];
					}
				}
				$allresponses=array_merge($allresponses,$response);
			}
		}
		
		// update transients for themes
		$new_update = new stdClass;
		$new_update->last_checked = time( );
		$new_update->checked = $checked;
		$new_update->response = $allresponses;
		set_site_transient( 'update_themes', $new_update );
	}
	
	public static function wprc_maybe_update_themes( ) {
		$current = get_site_transient( 'update_themes' );
		if ( isset( $current->last_checked ) && 43200 > ( time( ) - $current->last_checked ) )
			return;

		self::wprc_update_themes();
	}

	
	public static function wprc_theme_update_rows() {
		if ( !current_user_can('update_themes' ) )
			return;

		remove_action( 'admin_init', 'wp_theme_update_rows' );
		$themes = get_site_transient( 'update_themes' );
		if ( isset($themes->response) && is_array($themes->response) ) {
			$themes = array_keys( $themes->response );

			foreach( $themes as $theme ) {
				remove_action( "after_theme_row_$theme", 'wp_theme_update_row', 10, 2 );
				add_action( "after_theme_row_$theme", array('WPRC_ThemeInformation','wprc_theme_update_row'), 10, 2 );
			}
		}
	}

	public static function wprc_theme_update_row( $theme_key, $theme ) {
		$current = get_site_transient( 'update_themes' );
		if ( !isset( $current->response[ $theme_key ] ) )
			return false;
		$r = (array)$current->response[ $theme_key ];
		$themes_allowedtags = array('a' => array('href' => array(),'title' => array()),'abbr' => array('title' => array()),'acronym' => array('title' => array()),'code' => array(),'em' => array(),'strong' => array());
		$theme_name = wp_kses( $theme['Name'], $themes_allowedtags );

		if (isset($r['repository_id']))
        $details_url = self_admin_url("theme-install.php?tab=theme-information&repository_id=". $r['repository_id'] ."&theme=$theme_key&TB_iframe=true&width=640&height=484");
        else
        $details_url = self_admin_url("theme-install.php?tab=theme-information&theme=$theme_key&TB_iframe=true&width=640&height=484");
        
		$wp_list_table = _get_list_table('WP_MS_Themes_List_Table');

		echo '<tr class="plugin-update-tr"><td colspan="' . $wp_list_table->get_column_count() . '" class="plugin-update colspanchange"><div class="update-message">';
		if ( ! current_user_can('update_themes') )
			printf( __('There is a new version of %1$s available. <a href="%2$s" class="thickbox" title="%3$s">View version %4$s details</a>.','installer'), $theme['Name'], esc_url($details_url), esc_attr($theme['Name']), $r->new_version );
		else if ( empty( $r['package'] ) )
			printf( __('There is a new version of %1$s available. <a href="%2$s" class="thickbox" title="%3$s">View version %4$s details</a>. <em>Automatic update is unavailable for this theme.</em>','installer'), $theme['Name'], esc_url($details_url), esc_attr($theme['Name']), $r['new_version'] );
		else
			printf( __('There is a new version of %1$s available. <a href="%2$s" class="thickbox" title="%3$s">View version %4$s details</a> or <a href="%5$s">update now</a>.','installer'), $theme['Name'], esc_url($details_url), esc_attr($theme['Name']), $r['new_version'], wp_nonce_url( self_admin_url('update.php?action=upgrade-theme&theme=') . $theme_key, 'upgrade-theme_' . $theme_key) );

		//if ( empty( $r['package'] ) )
		//{
		/*if (isset($r['message']) && !empty($r['message']))
		{
			echo '<br /> '.wp_kses( $r->message, $themes_allowedtags );
		}*/
		if ( isset($r['message']) && ! empty($r['message'])  ) 
		{
				$message=WPRC_Functions::formatMessage((object)$r['message']);
				if (isset($r['message_type']) && $r['message_type']=='notify')
					WPRC_AdminNotifier::addMessage('wprc-theme-info-'.$theme_key,$message);
				else
					echo $message;
		}
		else if (isset($r['repository_id']) && isset($r['purchase']) && !empty($r['purchase']) && isset($r['price']) && !empty($r['price']))
		{
			echo '<br /> ';
			$purl=WPRC_Functions::sanitizeURL($r['purchase']);
			$return_url=rawurlencode(admin_url( 'theme-install.php?tab=theme-information&repository_id='. $r['repository_id'] .'&theme=' . $theme_key));
			$salt=rawurlencode($r['repository_salt']);
			if (strpos($purl,'?'))
				$url_glue='&';
			else
				$url_glue='?';
			$purl.=$url_glue.'return_to='.$return_url.'&rsalt='.$salt;
			echo '<a href="' . $purl . '&TB_iframe=true&width=640&height=484' . '" class="thickbox" title="'. sprintf(__('Upgrade %s', 'installer') ,'('.$r['currency']->symbol.$r['price'].' '.$r['currency']->name.')') .'">' . sprintf(__('Upgrade %s', 'installer') ,'('.$r['currency']->symbol.$r['price'].' '.$r['currency']->name.')'). '</a>';
		
		}
		//}
		do_action( "in_theme_update_message-$theme_key", $theme, $r );

		echo '</div></td></tr>';
	}
}
?>
