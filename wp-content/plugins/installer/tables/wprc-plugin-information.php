<?php
class WPRC_PluginInformation
{
	public static function wprc_install_plugin_information()
	{
		global $tab, $wp_version;

		$api = plugins_api('plugin_information', array('slug' => stripslashes( $_REQUEST['plugin'] ) ));

		if ( is_wp_error($api) )
			wp_die($api);

		$plugins_allowedtags = array(
			'a' => array( 'href' => array(), 'title' => array(), 'target' => array() ),
			'abbr' => array( 'title' => array() ), 'acronym' => array( 'title' => array() ),
			'code' => array(), 'pre' => array(), 'em' => array(), 'strong' => array(),
			'div' => array(), 'p' => array(), 'ul' => array(), 'ol' => array(), 'li' => array(),
			'h1' => array(), 'h2' => array(), 'h3' => array(), 'h4' => array(), 'h5' => array(), 'h6' => array(),
			'img' => array( 'src' => array(), 'class' => array(), 'alt' => array() )
		);

		$plugins_section_titles = array(
			'description'  => _x('Description',  'Plugin installer section title','installer'),
			'installation' => _x('Installation', 'Plugin installer section title','installer'),
			'faq'          => _x('FAQ',          'Plugin installer section title','installer'),
			'screenshots'  => _x('Screenshots',  'Plugin installer section title','installer'),
			'changelog'    => _x('Changelog',    'Plugin installer section title','installer'),
			'other_notes'  => _x('Other Notes',  'Plugin installer section title','installer')
		);

		//Sanitize HTML
		foreach ( (array)$api->sections as $section_name => $content )
			$api->sections[$section_name] = wp_kses($content, $plugins_allowedtags);
		foreach ( array( 'version', 'author', 'requires', 'tested', 'homepage', 'downloaded', 'slug' ) as $key ) {
			if ( isset( $api->$key ) )
				$api->$key = wp_kses( $api->$key, $plugins_allowedtags );
		}

		$section = isset($_REQUEST['section']) ? stripslashes( $_REQUEST['section'] ) : 'description'; //Default to the Description tab, Do not translate, API returns English.
		if ( empty($section) || ! isset($api->sections[ $section ]) )
			$section = array_shift( $section_titles = array_keys((array)$api->sections) );

		iframe_header( __('Plugin Install','installer') );
		echo "<div id='$tab-header'>\n";
		echo "<ul id='sidemenu'>\n";
		foreach ( (array)$api->sections as $section_name => $content ) {

			if ( isset( $plugins_section_titles[ $section_name ] ) )
				$title = $plugins_section_titles[ $section_name ];
			else
				$title = ucwords( str_replace( '_', ' ', $section_name ) );

			$class = ( $section_name == $section ) ? ' class="current"' : '';
			$href = add_query_arg( array('tab' => $tab, 'section' => $section_name) );
			$href = esc_url($href);
			$san_section = esc_attr( $section_name );
			echo "\t<li><a name='$san_section' href='$href' $class>$title</a></li>\n";
		}
		echo "</ul>\n";
		echo "</div>\n";
		$status = self::wprc_install_plugin_install_status($api);
		?>
		<div class="alignright fyi">
			<?php if ($status['status']=='latest_installed' || $status['status']=='newer_installed' || (! empty($api->download_link) && ( current_user_can('install_plugins') || current_user_can('update_plugins') )) ) { ?>
			<p class="action-button">
			<?php
			switch ( $status['status'] ) {
				case 'install':
					if ( $status['url'] )
						echo '<a href="' . $status['url'] . '" target="_parent">' . __('Install Now','installer') . '</a>';
					break;
				case 'update_available':
					if ( $status['url'] )
						echo '<a href="' . $status['url'] . '" target="_parent">' . __('Install Update Now','installer') .'</a>';
					break;
				case 'newer_installed':
					echo '<a>' . sprintf(__('Newer Version (%s) Installed','installer'), $status['version']) . '</a>';
					break;
				case 'latest_installed':
					echo '<a>' . __('Latest Version Installed','installer') . '</a>';
					break;
			}
			?>
			</p>
			<?php } if ( isset($api->message) && ! empty($api->message) && ( current_user_can('install_plugins') || current_user_can('update_plugins') ) ) { ?>
			<?php 
					//echo wp_kses($api->message, $plugins_allowedtags);
					$message=WPRC_Functions::formatMessage($api->message);
					if (isset($api->message_type) && $api->message_type=='notify')
						WPRC_AdminNotifier::addMessage('wprc-plugin-info-'.$api->slug,$message);
					else
						echo $message;
			?>
			<?php } elseif ( !($status['status']=='latest_installed' || $status['status']=='newer_installed') && (isset($api->purchase_link) && ! empty($api->purchase_link) && isset($api->price) && !empty($api->price) && ( current_user_can('install_plugins') || current_user_can('update_plugins') )) ) { ?>
			<p class="action-button">
			<?php 
                    $purl=WPRC_Functions::sanitizeURL($api->purchase_link);
					$return_url=rawurlencode(admin_url( 'plugin-install.php?tab=plugin-information&repository_id='. $api->repository_id .'&plugin=' . $api->slug));
					$salt=rawurlencode($api->salt);
					if (strpos($purl,'?'))
						$url_glue='&';
					else
						$url_glue='?';
					$purl.=$url_glue.'return_to='.$return_url.'&rsalt='.$salt;
					echo '<a href="' . $purl . '">' . sprintf(__('Buy %s', 'installer'),'('.$api->currency->symbol.$api->price.' '.$api->currency->name.')') . '</a>';
			?>
			</p>
			<?php } ?>
			<?php if (isset($api->rauth) && $api->rauth==false) { ?>
			<p><?php _e('Authorization Failed!','installer'); ?></p>
			<?php } ?>
			<h2 class="mainheader"><?php /* translators: For Your Information */ _e('FYI') ?></h2>
			<ul>
	<?php if ( ! empty($api->version) ) : ?>
				<li><strong><?php _e('Version:','installer') ?></strong> <?php echo $api->version ?></li>
	<?php endif; if ( ! empty($api->author) ) : ?>
				<li><strong><?php _e('Author:','installer') ?></strong> <?php echo links_add_target($api->author, '_blank') ?></li>
	<?php endif; if ( ! empty($api->last_updated) ) : ?>
				<li><strong><?php _e('Last Updated:','installer') ?></strong> <span title="<?php echo $api->last_updated ?>"><?php
								printf( __('%s ago','installer'), human_time_diff(strtotime($api->last_updated)) ) ?></span></li>
	<?php endif; if ( ! empty($api->requires) ) : ?>
				<li><strong><?php _e('Requires WordPress Version:','installer') ?></strong> <?php printf(__('%s or higher','installer'), $api->requires) ?></li>
	<?php endif; if ( ! empty($api->tested) ) : ?>
				<li><strong><?php _e('Compatible up to:','installer') ?></strong> <?php echo $api->tested ?></li>
	<?php endif; if ( ! empty($api->downloaded) ) : ?>
				<li><strong><?php _e('Downloaded:','installer') ?></strong> <?php printf(_n('%s time', '%s times', $api->downloaded,'installer'), number_format_i18n(intval($api->downloaded))) ?></li>
	<?php endif; if ( ! empty($api->slug) && empty($api->external) ) : ?>
				<li><a target="_blank" href="http://wordpress.org/extend/plugins/<?php echo $api->slug ?>/"><?php _e('WordPress.org Plugin Page &#187;','installer') ?></a></li>
	<?php endif; if ( ! empty($api->homepage) ) : ?>
				<li><a target="_blank" href="<?php echo $api->homepage ?>"><?php _e('Plugin Homepage &#187;','installer') ?></a></li>
	<?php endif; ?>
			</ul>
			<?php if ( ! empty($api->rating) ) : ?>
			<?php if (version_compare($wp_version, "3.4", ">=")){ ?>
			<h2><?php _e('Average Rating','installer') ?></h2>
			<div class="star-holder" title="<?php printf(_n('(based on %s rating)', '(based on %s ratings)', $api->num_ratings,'installer'), number_format_i18n(intval($api->num_ratings))); ?>">
				<div class="star star-rating" style="width: <?php echo esc_attr( str_replace( ',', '.', $api->rating ) ); ?>px"></div>
			</div>
			<small><?php printf(_n('(based on %s rating)', '(based on %s ratings)', $api->num_ratings,'installer'), number_format_i18n(intval($api->num_ratings))); ?></small>
			<?php } else { ?>
		<h2><?php _e('Average Rating','installer') ?></h2>
		<div class="star-holder" title="<?php printf(_n('(based on %s rating)', '(based on %s ratings)', $api->num_ratings,'installer'), number_format_i18n(intval($api->num_ratings))); ?>">
			<div class="star star-rating" style="width: <?php echo esc_attr($api->rating) ?>px"></div>
			<div class="star star5"><img src="<?php echo admin_url('images/star.png?v=20110615'); ?>" alt="<?php esc_attr_e('5 stars') ?>" /></div>
			<div class="star star4"><img src="<?php echo admin_url('images/star.png?v=20110615'); ?>" alt="<?php esc_attr_e('4 stars') ?>" /></div>
			<div class="star star3"><img src="<?php echo admin_url('images/star.png?v=20110615'); ?>" alt="<?php esc_attr_e('3 stars') ?>" /></div>
			<div class="star star2"><img src="<?php echo admin_url('images/star.png?v=20110615'); ?>" alt="<?php esc_attr_e('2 stars') ?>" /></div>
			<div class="star star1"><img src="<?php echo admin_url('images/star.png?v=20110615'); ?>" alt="<?php esc_attr_e('1 star') ?>" /></div>
		</div>
		<small><?php printf(_n('(based on %s rating)', '(based on %s ratings)', $api->num_ratings,'installer'), number_format_i18n(intval($api->num_ratings))); ?></small>
			<?php } ?>
			<?php endif; ?>
		</div>
		<div id="section-holder" class="wrap">
		<?php
			if ( !empty($api->tested) && version_compare( substr($GLOBALS['wp_version'], 0, strlen($api->tested)), $api->tested, '>') )
				echo '<div class="updated"><p>' . __('<strong>Warning:</strong> This plugin has <strong>not been tested</strong> with your current version of WordPress.','installer') . '</p></div>';

			else if ( !empty($api->requires) && version_compare( substr($GLOBALS['wp_version'], 0, strlen($api->requires)), $api->requires, '<') )
				echo '<div class="updated"><p>' . __('<strong>Warning:</strong> This plugin has <strong>not been marked as compatible</strong> with your version of WordPress.','installer') . '</p></div>';

			if (version_compare($wp_version, "3.4", ">="))
			{
			foreach ( (array)$api->sections as $section_name => $content ) {

				if ( isset( $plugins_section_titles[ $section_name ] ) )
					$title = $plugins_section_titles[ $section_name ];
				else
					$title = ucwords( str_replace( '_', ' ', $section_name ) );

				$content = links_add_base_url($content, 'http://wordpress.org/extend/plugins/' . $api->slug . '/');
				$content = links_add_target($content, '_blank');

				$san_section = esc_attr( $section_name );

				$display = ( $section_name == $section ) ? 'block' : 'none';

				echo "\t<div id='section-{$san_section}' class='section' style='display: {$display};'>\n";
				echo "\t\t<h2 class='long-header'>$title</h2>";
				echo $content;
				echo "\t</div>\n";
			}
			}
			else
			{
		foreach ( (array)$api->sections as $section_name => $content ) {
			$title = $section_name;
			$title[0] = strtoupper($title[0]);
			$title = str_replace('_', ' ', $title);

			$content = links_add_base_url($content, 'http://wordpress.org/extend/plugins/' . $api->slug . '/');
			$content = links_add_target($content, '_blank');

			$san_title = esc_attr(sanitize_title_with_dashes($title));

			$display = ( $section_name == $section ) ? 'block' : 'none';

			echo "\t<div id='section-{$san_title}' class='section' style='display: {$display};'>\n";
			echo "\t\t<h2 class='long-header'>$title</h2>";
			echo $content;
			echo "\t</div>\n";
		}
			}
		echo "</div>\n";

		iframe_footer();
		exit;
	}
	
	public static function wprc_install_plugin_install_status($api, $loop = false) 
	{
		// this function is called recursively, $loop prevents further loops.
		if ( is_array($api) )
			$api = (object) $api;

		//Default to a "new" plugin
		$status = 'install';
		$url = false;
		
        //Check to see if this plugin is known to be installed, and has an update awaiting it.
		$update_plugins = get_site_transient('update_plugins');
		if ( false!=$update_plugins && isset( $update_plugins->response ) ) {
			foreach ( (array)$update_plugins->response as $file => $plugin ) {
				if ( $plugin->slug === $api->slug && $plugin->new_version==$api->version && ((isset($api->download_link) && !empty($api->download_link)) || (isset($api->package) && !empty($api->package)))) {
					if ((isset($api->download_link) && !empty($api->download_link)))
					{
						$update_plugins->response[$file]->package=$api->download_link;
						set_site_transient('update_plugins',$update_plugins);
						if ( isset( $update_plugins->response[$file]->package ) )
                        	$plugin->package=$update_plugins->response[$file]->package;
					}
					else if ((isset($api->package) && !empty($api->package)))
					{
						$update_plugins->response[$file]->package=$api->package;
						set_site_transient('update_plugins',$update_plugins);
						if ( isset( $update_plugins->response[$file]->package ) )
                        	$plugin->package=$update_plugins->response[$file]->package;
					}
                    }
				if ( $plugin->slug === $api->slug && (isset($plugin->package) && !empty($plugin->package))) 
                {
					$status = 'update_available';
					$update_file = $file;
					$version = $plugin->new_version;
					if ( current_user_can('update_plugins') )
						$url = wp_nonce_url(self_admin_url('update.php?action=upgrade-plugin&plugin=' . $update_file), 'upgrade-plugin_' . $update_file);
					break;
				}
			}
		}

		if ( 'install' == $status ) {
			$installed_plugin=array();
			if ( is_dir( WP_PLUGIN_DIR . '/' . $api->slug ) ) {
				$installed_plugin = get_plugins('/' . $api->slug);
			}
			else {
				$my_installed_plugins=get_plugins();
				foreach ($my_installed_plugins as $installed_plugin_data)
				{
					if ($installed_plugin_data['Name']==$api->name)
					{
						$installed_plugin=array($installed_plugin_data);
						break;
					}
				}
			}
			/*if ( is_dir( WP_PLUGIN_DIR . '/' . $api->slug ) ) {
				$installed_plugin = get_plugins('/' . $api->slug);*/
				if ( empty($installed_plugin) ) {
					if ( current_user_can('install_plugins') )
						$url = wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=' . $api->slug), 'install-plugin_' . $api->slug);
				} else {
					$key = array_shift( $key = array_keys($installed_plugin) ); //Use the first plugin regardless of the name, Could have issues for multiple-plugins in one directory if they share different version numbers
					if ( version_compare($api->version, $installed_plugin[ $key ]['Version'], '=') ){
						$status = 'latest_installed';
					} elseif ( version_compare($api->version, $installed_plugin[ $key ]['Version'], '<') ) {
						$status = 'newer_installed';
						$version = $installed_plugin[ $key ]['Version'];
					} else {
						//If the above update check failed, Then that probably means that the update checker has out-of-date information, force a refresh
						if ( ! $loop ) {
							delete_site_transient('update_plugins');
							self::wprc_update_plugins();
							return self::wprc_install_plugin_install_status($api, true);
						}
					}
				}
			/*} else {
				// "install" & no directory with that slug
				if ( current_user_can('install_plugins') )
					$url = wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=' . $api->slug), 'install-plugin_' . $api->slug);
			}*/
		}
		
        // return some url anyway
        if ($status=='install' && $url===false)
            $url = wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=' . $api->slug), 'install-plugin_' . $api->slug);
        
        if ( isset($_GET['from']) )
			$url .= '&amp;from=' . urlencode(stripslashes($_GET['from']));
        
        if (isset($api->repository_id))
            $url=add_query_arg(array('repository_id'=>$api->repository_id),$url);
		
        return compact('status', 'url', 'version');
	}
	
	public static function wprc_update_plugins()
	{
		global $wp_version;
		$WP_UPDATE_PLUGINS_URL=WPRC_WP_PLUGINS_UPDATE_REPO;
		
		//include ABSPATH . WPINC . '/version.php'; // include an unmodified $wp_version

		if ( defined('WP_INSTALLING') )
			return false;

		// If running blog-side, bail unless we've not checked in the last 12 hours
		//if ( !function_exists( 'get_plugins' ) )
			//require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		//$plugins = get_plugins();
		$active  = get_option( 'active_plugins', array() );
		$current = get_site_transient( 'update_plugins' );

		if ( ! is_object($current) )
			$current = new stdClass;

		$new_option = new stdClass;
		$new_option->last_checked = time();
		// Check for updated every 60 minutes if hitting update pages; else, check every 12 hours.
		$timeout = in_array( current_filter(), array( 'load-plugins.php', 'load-update.php', 'load-update-core.php' ) ) ? 3600 : 43200;
		$time_not_changed = isset( $current->last_checked ) && $timeout > ( time() - $current->last_checked );
        
		// get plugin data from the DB
        $extensions_model = WPRC_Loader::getModel('extensions');
        $extensions = $extensions_model->getFullExtensionsTree();
		$plugins=$extensions['plugins'];
		// arrange plugins according to repository (if enabled)
		$repos=array();
		$plugin_changed = false;
		foreach ($plugins as $key=>$plugin)
		{
			// if plugin repository is enabled then push plugin to the repository list
			//if ($plugin['repository_enabled']!==false)
			{
				$new_option->checked[ $key ] = $plugin['Version'];

				if ( !isset( $current->checked[ $key ] ) || strval($current->checked[ $key ]) !== strval($plugin['Version']) )
					$plugin_changed = true;
				
				$rkey=$plugin['repository_endpoint_url']===null?WPRC_WP_PLUGINS_REPO:$plugin['repository_endpoint_url'];
				
				$sendplugin=$plugin;
				$sendplugin['slug']=$sendplugin['extension_slug'];
				// remove unwanted fields from the plugin that is going to be sent
				unset($sendplugin['repository_endpoint_url']);
				unset($sendplugin['repository_id']);
				unset($sendplugin['repository_user']);
				unset($sendplugin['repository_pass']);
				unset($sendplugin['repository_salt']);
				unset($sendplugin['repository_name']);
				unset($sendplugin['repository_enabled']);
				unset($sendplugin['repository_deleted']);
				unset($sendplugin['extension_was_installed']);
				unset($sendplugin['type_name']);
				unset($sendplugin['extension_slug']);
				
				if (!isset($repos[$rkey]))
				{
					$repos[$rkey]=array(
                    'id'=>$plugin['repository_id'],
					'url'=>$rkey,
					'user'=>$plugin['repository_user'],
					'pass'=>$plugin['repository_pass'],
					'name'=>$plugin['repository_name'],
					'salt'=>$plugin['repository_salt'],
					'plugins'=>array($key=>$sendplugin)
					);
					// Wordpress uses different install repository URL from Update Repository URL so hardcode it
					if ($rkey==WPRC_WP_PLUGINS_REPO)
						$repos[$rkey]['url']=$WP_UPDATE_PLUGINS_URL;
				}
				else
					$repos[$rkey]['plugins'][$key]=$sendplugin;
			}
		}
		

		if ( isset ( $current->response ) && is_array( $current->response ) ) {
			foreach ( $current->response as $plugin_file => $update_details ) {
				if ( ! isset($plugins[ $plugin_file ]) ) {
					$plugin_changed = true;
					break;
				}
			}
		}

		// Bail if we've checked in the last 12 hours and if nothing has changed
		if ( $time_not_changed && !$plugin_changed )
			return false;

		// Update last_checked for current to prevent multiple blocking requests if request hangs
		$current->last_checked = time();
		set_site_transient( 'update_plugins', $current );

		$allresponses=array();
		//WPRC_Loader::includeSecurity();
		foreach ($repos as $repo)
		{
			$plugins=$repo['plugins'];
			$to_send = (object) compact('plugins', 'active');
            $server_url = $repo['url'];
            $repository_username = $repo['user'];
            $repository_password = $repo['pass'];
			$repository_salt=$repo['salt'];
			
			// action is plugins-update by default
			$body_array = array(
                'action' => 'plugins_update',
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
            
			$body_array['plugins'] = serialize( $to_send );

			if ($server_url===$WP_UPDATE_PLUGINS_URL)
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
            $msg=sprintf("PLUGIN UPDATE API Request to %s, timeout: %d, wpversion: %s, request args: %s",$server_url,$options['timeout'],$wp_version,print_r($reqargs,true));
            WPRC_Functions::log($msg,'api','api.log');
            unset($reqargs);
			// send request
			$raw_response = wp_remote_post($server_url, $options);

            // log
            $msg=sprintf("PLUGIN UPDATE API Request to %s, response: %s",$server_url,print_r($raw_response,true));
            WPRC_Functions::log($msg,'api','api.log');
			
            if ( is_wp_error( $raw_response ) || 200 != wp_remote_retrieve_response_code( $raw_response ) )
            {
                // log
                if (is_wp_error( $raw_response ))
                {
                    $msg=sprintf("PLUGIN UPDATE API Request to %s, response error: %s",$server_url,print_r($raw_response->get_error_message(),true));
                    WPRC_Functions::log($msg,'api','api.log');
                }
				continue; //return false;
            }

			$response = @unserialize( wp_remote_retrieve_body( $raw_response ) );
			
			// merge with other results
			if ( false == $response )
            {
                // log
                $msg=sprintf("PLUGIN UPDATE API Request to %s, response unserialize failed: %s",$server_url,print_r(wp_remote_retrieve_body( $raw_response ),true));
                WPRC_Functions::log($msg,'api','api.log');
            }
            else if (is_object($response) && isset($response->error))
            {
                $response = new WP_Error('extensions_api_failed', $response->error, wp_remote_retrieve_body( $raw_response ) );
                // log
                $msg=sprintf("PLUGIN UPDATE API Request to %s, action not implemented error: %s",$server_url, print_r($response,true));
                WPRC_Functions::log($msg,'api','api.log');
            }
            else if (is_array($response) && isset($response['error']))
            {
                $response = new WP_Error('extensions_api_failed', $response['error'], wp_remote_retrieve_body( $raw_response ) );
                // log
                $msg=sprintf("PLUGIN UPDATE API Request to %s, action not implemented error: %s",$server_url, print_r($response,true));
                WPRC_Functions::log($msg,'api','api.log');
            }
			else
			{
				// add some info about repository id etc..
				foreach ($response as $key=>$plug)
				{
					$response[$key]->repository_id=$repo['id'];
					$response[$key]->repository_salt=$repo['salt'];
				}
				$allresponses=array_merge($allresponses,$response);
			}
		}
		
		// update the options for plugins
		$new_option->response = $allresponses;
		set_site_transient( 'update_plugins', $new_option );
	}

	public static function wprc_maybe_update_plugins() 
	{
		$current = get_site_transient( 'update_plugins' );
		if ( isset( $current->last_checked ) && 43200 > ( time() - $current->last_checked ) )
			return;
		self::wprc_update_plugins();
	}

	
	public static function wprc_plugin_update_rows() {
		global $wp_filter;
		
		if ( !current_user_can('update_plugins' ) )
			return;

		remove_action( 'admin_init', 'wp_plugin_update_rows' );
		$plugins = get_site_transient( 'update_plugins' );
		if ( isset($plugins->response) && is_array($plugins->response) ) {
			$plugins = array_keys( $plugins->response );
			foreach( $plugins as $plugin_file ) {
				remove_action( "after_plugin_row_$plugin_file", 'wp_plugin_update_row', 10, 2 );
				//$wp_filter["after_plugin_row_$plugin_file"]=array();
				add_action( "after_plugin_row_$plugin_file", array('WPRC_PluginInformation','wprc_plugin_update_row'), 10, 2 );
			}
		}
	}

	public static function wprc_plugin_update_row( $file, $plugin_data ) {
		
		$current = get_site_transient( 'update_plugins' );
		if ( !isset( $current->response[ $file ] ) )
			return false;

		$r = $current->response[ $file ];

		$plugins_allowedtags = array('a' => array('href' => array(),'title' => array()),'abbr' => array('title' => array()),'acronym' => array('title' => array()),'code' => array(),'em' => array(),'strong' => array());
		$plugin_name = wp_kses( $plugin_data['Name'], $plugins_allowedtags );

		if (isset($r->repository_id))
        	$details_url = self_admin_url('plugin-install.php?tab=plugin-information&repository_id='. $r->repository_id .'&plugin=' . $r->slug . '&section=changelog&TB_iframe=true&width=640&height=484');
       	else
        	$details_url = self_admin_url('plugin-install.php?tab=plugin-information&plugin=' . $r->slug . '&section=changelog&TB_iframe=true&width=640&height=484');
           
		$wp_list_table = _get_list_table('WP_Plugins_List_Table');

		if ( is_network_admin() || !is_multisite() ) {
			echo '<tr class="plugin-update-tr"><td colspan="' . $wp_list_table->get_column_count() . '" class="plugin-update colspanchange"><div class="update-message">';

			if ( ! current_user_can('update_plugins') ) {
				printf( __('There is a new version of %1$s available. <a href="%2$s" class="thickbox" title="%3$s">View version %4$s details</a>.','installer'), $plugin_name, esc_url($details_url), esc_attr($plugin_name), $r->new_version );
			}
			else if ( empty($r->package) ) {
				$ext_model = WPRC_Loader::getModel( 'extensions' );
				$repository = $ext_model -> get_extension_repository( $file );
				$nonce_login = wp_create_nonce('installer-login-link');
				if ( ! empty( $repository ) ) {
					printf( __('There is a new version of %1$s available. <a href="%2$s" class="thickbox" title="%3$s">View version %4$s details</a>. To update this plugin, first <a class="thickbox" href="%5$s">log-in to %6$s</a>.','installer'), $plugin_name, esc_url($details_url), esc_attr($plugin_name), $r->new_version, admin_url('admin.php?wprc_c=repository-login&amp;wprc_action=RepositoryLogin&amp;repository_id=' . $repository -> id . '&amp;_wpnonce='.$nonce_login), $repository -> repository_name );
				}
				else {
					printf( __('There is a new version of %1$s available. <a href="%2$s" class="thickbox" title="%3$s">View version %4$s details</a>. <em>Automatic update is unavailable for this plugin.</em>','installer'), $plugin_name, esc_url($details_url), esc_attr($plugin_name), $r->new_version );
				}
			}
			else {
				printf( __('There is a new version of %1$s available. <a href="%2$s" class="thickbox" title="%3$s">View version %4$s details</a> or <a href="%5$s">update now</a>.','installer'), $plugin_name, esc_url($details_url), esc_attr($plugin_name), $r->new_version, wp_nonce_url( self_admin_url('update.php?action=upgrade-plugin&plugin=') . $file, 'upgrade-plugin_' . $file) );
			}
			//if ( empty($r->package) )
			//{
			/*if (isset($r->message) && !empty($r->message))
			{
					echo '<br /> '.wp_kses($r->message, $plugins_allowedtags);
			}*/
			if ( isset($r->message) && ! empty($r->message)  ) 
			{
					$message=WPRC_Functions::formatMessage($r->message);
					if (isset($r->message_type) && $r->message_type=='notify')
						WPRC_AdminNotifier::addMessage('wprc-plugin-info-'.$r->slug,$message);
					else
						echo $message;
			}
			else if (isset($r->repository_id) && isset($r->purchase) && !empty($r->purchase) && isset($r->price) && !empty($r->price))
			{
				echo '<br /> ';
				$purl=WPRC_Functions::sanitizeURL($r->purchase);
				$return_url=rawurlencode(admin_url( 'plugin-install.php?tab=plugin-information&repository_id='. $r->repository_id .'&plugin=' . $r->slug));
				$salt=rawurlencode($r->repository_salt);
				if (strpos($purl,'?'))
					$url_glue='&';
				else
					$url_glue='?';
				$purl.=$url_glue.'return_to='.$return_url.'&rsalt='.$salt;
				echo '<a href="' . $purl . '&TB_iframe=true&width=640&height=484' . '" class="thickbox" title="'. sprintf(__('Upgrade %s', 'installer') ,'('.$r->currency->symbol.$r->price.' '.$r->currency->name.')').'">' . sprintf(__('Upgrade %s', 'installer') ,'('.$r->currency->symbol.$r->price.' '.$r->currency->name.')'). '</a>';
			}
			//}
			do_action( "in_plugin_update_message-$file", $plugin_data, $r );

			echo '</div></td></tr>';
		}
	}
}
?>