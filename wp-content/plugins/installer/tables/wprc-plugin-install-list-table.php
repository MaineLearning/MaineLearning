<?php
class WPRC_PluginInstall_List_Table extends WP_Plugin_Install_List_Table
{
	private $repositories_tabs = '';
	private $results_per_repo;

    private function get_current_tab()
    {
        return $_GET['tab'];
    }     

    public function display() {

    	$protocol = strpos(strtolower( $_SERVER['SERVER_PROTOCOL'] ),'https') === FALSE ? 'http://' : 'https://';
    	$host = $_SERVER['HTTP_HOST'];
    	$uri = $_SERVER['REQUEST_URI'];
    	$current_url = $protocol . $host . $uri;

    	if ( isset( $this -> repositories_tabs ) && is_array( $this -> repositories_tabs ) && count( $this -> repositories_tabs ) > 0 ) {
    		if ( ! isset( $_GET['repo-tab'] ) )
    			$active_repo = $this -> repositories_tabs[0]['id'];
    		else 
    			$active_repo = (int)$_GET['repo-tab'];

	        echo '<div class="clear"></div>';
	        echo '<h2 class="nav-tab-wrapper">';

	        foreach ( $this -> repositories_tabs as $repo_data ) {
	        	$results = (int)$this -> results_per_repo[ $repo_data['id'] ]['results'];
	        	$class_active = ( $active_repo == $repo_data['id'] ) ? 'nav-tab-active' : '';
	        	$repo_tab_url = add_query_arg( 'repo-tab', $repo_data['id'], $current_url );
	        	$repo_tab_url = remove_query_arg( 'paged', $repo_tab_url );
	        	echo '<a href="' . $repo_tab_url . '" class="nav-tab ' . $class_active . '">' . $repo_data['name'] . ' (' . $results . ')</a>';
	        }
	        echo '</h2>';
	    }
        
    	parent::display();
    }
    
	public function prepare_items()
    {
        $tab = $this->get_current_tab();
        
        // replace search results tab only
        if($tab<>'search')
        {
            parent::prepare_items();
            exit;
        }

        include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );

		global $tabs, $tab, $paged, $type, $term;

		wp_reset_vars( array( 'tab' ) );

		$paged = $this->get_pagenum();   
        
		$per_page = WPRC_PLUGINS_API_QUERY_PLUGINS_PER_PAGE;

		// These are the tabs which are shown on the page
		$tabs = array();
		$tabs['dashboard'] = __( 'Search', 'installer' );
		if ( 'search' == $tab )
			$tabs['search']	= __( 'Search Results', 'installer' );
		$tabs['upload'] = __( 'Upload', 'installer' );
		$tabs['featured'] = _x( 'Featured','Plugin Installer', 'installer' );
		$tabs['popular']  = _x( 'Popular','Plugin Installer', 'installer' );
		$tabs['new']      = _x( 'Newest','Plugin Installer', 'installer' );
		$tabs['updated']  = _x( 'Recently Updated','Plugin Installer', 'installer' );

		$nonmenu_tabs = array( 'plugin-information' ); //Valid actions to perform which do not have a Menu item.

		$tabs = apply_filters( 'install_plugins_tabs', $tabs );
		$nonmenu_tabs = apply_filters( 'install_plugins_nonmenu_tabs', $nonmenu_tabs );

		// If a non-valid menu tab has been selected, And its not a non-menu action.
		if ( empty( $tab ) || ( !isset( $tabs[ $tab ] ) && !in_array( $tab, (array) $nonmenu_tabs ) ) )
			$tab = key( $tabs );

		$args = array( 'page' => $paged, 'per_page' => $per_page );

		switch ( $tab ) {
			case 'search':
				$type = isset( $_REQUEST['type'] ) ? stripslashes( $_REQUEST['type'] ) : '';
				$term = isset( $_REQUEST['s'] ) ? stripslashes( $_REQUEST['s'] ) : '';

				switch ( $type ) {
					case 'tag':
						$args['tag'] = sanitize_title_with_dashes( $term );
						break;
					case 'author':
						$args['author'] = $term;
						break;
					case 'term':
					// if type is missing (wp34) default to search term
					default:
						$args['search'] = $term;
						break;
				}
               
				add_action( 'install_plugins_table_header', 'install_search_form' );
				break;

			case 'featured':
			case 'popular':
			case 'new':
			case 'updated':
				$args['browse'] = $tab;
				break;

			default:
				$args = false;
		}

		if ( !$args )
			return;

		$api = plugins_api( 'query_plugins', $args );

		if ( is_wp_error( $api ) )
			wp_die( $api->get_error_message() . '</p> <p class="hide-if-no-js"><a href="#" onclick="document.location.reload(); return false;">' . __( 'Try again', 'installer' ) . '</a>' );

		$repo_model = WPRC_Loader::getModel('repositories');
		if ( isset( $_GET['repos'] ) ) {
			$repos = $_GET['repos'];
		}
		else {
			$rm = WPRC_Loader::getModel('repositories');
    		$repos = $rm -> getRepositoriesIds('enabled_repositories','plugins');
		}

		// Filtering by repository if is the case...
		$this -> repositories_tabs = array();
		

		// Do we need tabs?
		$this -> results_per_repo = $api -> results_per_repo;
		$repo_results_gt_zero = 0;
		foreach ( $this -> results_per_repo as $repo_results ) {
			if ( $repo_results['results'] > 0 )
				$repo_results_gt_zero++;
		}

		if ( $api -> info['results'] > WPRC_PLUGINS_API_QUERY_PLUGINS_PER_PAGE && count( $repos ) > 1 && ( $repo_results_gt_zero > 1 ) ) {
			
			// We have too many results => we have to tab the results
			
			// Ordering repos by ID so Wordpress.org plugins wil appear most of the time at first place
			sort($repos);

			$tmp = array();
			foreach ( $repos as $repo_id ) {

				if ( isset( $this -> results_per_repo[ $repo_id ] ) && $this -> results_per_repo[ $repo_id ]['results'] == 0 )
					continue;

				// We need the name of the repo
				$repo_info = $repo_model -> getRepositoryByField( 'id', $repo_id );

				if ( $repo_info ) {
					$this -> repositories_tabs[] = array(
						'id' 	=> $repo_info -> id,
						'name' 	=> $repo_info -> repository_name
					);
				}
			}

			$filtered_api = new stdClass;
			
			$filtered_api -> info['results'] = $api -> info['results'];
			$filtered_api -> info['page'] = $api -> info['page'];
			
			$filtered_api -> plugins = array();

			// If we are currently on a tab, we'll show only those results
			if ( is_array( $this -> repositories_tabs ) && count( $this -> repositories_tabs ) > 0 )
				$current_repo = ( isset( $_GET['repo-tab'] ) ) ? $_GET['repo-tab'] : $this -> repositories_tabs[0]['id'];

			foreach ( $api -> plugins as $plugin ) {
				if ( $plugin -> repository_id == $current_repo ) {
					$filtered_api -> plugins[] = $plugin;
				}
				else {
					$filtered_api -> info['results']--;
				}

				

			}
			$filtered_api -> info['results'] = $this -> results_per_repo[ $current_repo ]['results'];
			$filtered_api -> info['total_pages'] = (int)ceil( $filtered_api -> info['results'] / WPRC_PLUGINS_API_QUERY_PLUGINS_PER_PAGE );

		}
		else {
			$filtered_api = $api;
		}
		
		
		

		$this->items = $filtered_api->plugins;

		$this->set_pagination_args( array(
			'total_items' => $filtered_api->info['results'],
			'per_page' => $per_page,
		) );
        
    }
    
 	function no_items() {
		_e( 'No plugins match your request.','installer' );
	}
  	
	function get_columns() {
   	    
        $columns = parent::get_columns();
        
        $columns['source'] = __('Source', 'installer');
        $columns['price'] = __('Price', 'installer');

		return $columns;
	}
    
    public function display_rows()
    {  
  		$plugins_allowedtags = array(
			'a' => array( 'href' => array(),'title' => array(), 'target' => array() ),
			'abbr' => array( 'title' => array() ),'acronym' => array( 'title' => array() ),
			'code' => array(), 'pre' => array(), 'em' => array(),'strong' => array(),
			'ul' => array(), 'ol' => array(), 'li' => array(), 'p' => array(), 'br' => array()
		);

		list( $columns, $hidden ) = $this->get_column_info();

		$style = array();
		foreach ( $columns as $column_name => $column_display_name ) {
			$style[ $column_name ] = in_array( $column_name, $hidden ) ? 'style="display:none;"' : '';
		}
        
        $nonce_login = wp_create_nonce('installer-login-link');
    
		foreach ( (array) $this->items as $plugin ) {
		  //echo '<pre>'; print_r($plugin); echo '</pre>';
			if ( is_object( $plugin ) )
				$plugin = (array) $plugin;

			$title = wp_kses( $plugin['name'], $plugins_allowedtags );
			//Limit description to 400char, and remove any HTML.
			$description = strip_tags( $plugin['description'] );
			if ( strlen( $description ) > 400 )
				$description = mb_substr( $description, 0, 400 ) . '&#8230;';
			//remove any trailing entities
			$description = preg_replace( '/&[^;\s]{0,6}$/', '', $description );
			//strip leading/trailing & multiple consecutive lines
			$description = trim( $description );
			$description = preg_replace( "|(\r?\n)+|", "\n", $description );
			//\n => <br>
			$description = nl2br( $description );
			$version = wp_kses( $plugin['version'], $plugins_allowedtags );

			$name = strip_tags( $title . ' ' . $version );

			$author = $plugin['author'];
			if ( ! empty( $plugin['author'] ) )
				$author = ' <cite>' . sprintf( __( 'By %s', 'installer' ), $author ) . '.</cite>';

			$author = wp_kses( $author, $plugins_allowedtags );

			$action_links = array();
			$action_links[] = '<a href="' . self_admin_url( 'plugin-install.php?tab=plugin-information&amp;repository_id='. $plugin['repository']->id .'&amp;plugin=' . $plugin['slug'] .
								'&amp;TB_iframe=true&amp;width=600&amp;height=550' ) . '" class="thickbox" title="' .
								esc_attr( sprintf( __( 'More information about %s', 'installer' ), $name ) ) . '">' . __( 'Details', 'installer' ) . '</a>';

            // set price
            $no_price_value = __('Free', 'installer');
            $plugin_price = $no_price_value;
            if(array_key_exists('price', $plugin))
            {
                $plugin_price = ($plugin['price']<>0 && isset($plugin['price'])) ? $plugin['currency']->symbol.$plugin['price'].' ('.$plugin['currency']->name.')' : $no_price_value;
            }

            $plugin_source = '';
            if(array_key_exists('repository', $plugin))
            {
                $plugin_source = $plugin['repository']->repository_name;
            }

			if ( current_user_can( 'install_plugins' ) || current_user_can( 'update_plugins' ) )
            {
                //$plugin['purchase_url'] = 'http://wpml.org/shop/checkout/?buy_now=2'; // DEBUG

				WPRC_Loader::includeListTable('wprc-plugin-information');
				$status = WPRC_PluginInformation::wprc_install_plugin_install_status( $plugin );    
                //$action_links[]=$status['status'];
				if ($status['status']!='latest_installed' && $status['status']!='newer_installed')
				{
				if(isset($plugin['purchase_link']) && !empty($plugin['purchase_link']) && ($plugin_price != $no_price_value))
                {
                    $purl=WPRC_Functions::sanitizeURL($plugin['purchase_link']);
					$return_url=rawurlencode(admin_url( 'plugin-install.php?tab=plugin-information&repository_id='. $plugin['repository']->id .'&plugin=' . $plugin['slug']));
					$salt=rawurlencode($plugin['salt']);
					if (strpos($purl,'?'))
						$url_glue='&';
					else
						$url_glue='?';
					$purl.=$url_glue.'return_to='.$return_url.'&rsalt='.$salt;

					$status = array(
                        'status'    => 'paid',
                        'url'       => $purl,
                        'version'   => $plugin['version']
                    );
                }
				
                /*else
                {
                    WPRC_Loader::includeListTable('wprc-plugin-information');
					$status = WPRC_PluginInformation::wprc_install_plugin_install_status( $plugin );    
                }*/
				}
                
                $url_glue = false === strpos($status['url'], '?') ? '?' : '&';
                $status['url'] .= $url_glue . 'repository_id='. $plugin['repository']->id;
                
				$showedmessage=false;
				
				switch ( $status['status'] ) {
					case 'install':
						if ( $status['url'] )
							$action_links[] = '<a class="install-now" href="' . $status['url'] . '" title="' . esc_attr( sprintf( __( 'Install %s', 'installer' ), $name ) ) . '">' . __( 'Install Now', 'installer' ) . '</a>';
						break;
					case 'update_available':
						if ( $status['url'] )
							$action_links[] = '<a href="' . $status['url'] . '" title="' . esc_attr( sprintf( __( 'Update to version %s', 'installer' ), $status['version'] ) ) . '">' . sprintf( __( 'Update Now', 'installer' ), $status['version'] ) . '</a>';
						break;
                    case 'paid':
                        //$action_links[] = '<a href="' . $status['url'] . '" class="thickbox">' . __('Buy' , 'installer') . ' (' . $plugin['currency'] . $plugin['price'].')</a>';
                        if (isset($plugin['message'])  && !empty($plugin['message']))
						{
							$action_links[] = WPRC_Functions::formatMessage($plugin['message']);
							$showedmessage=true;
						}
						else
						{
						//$action_links[] = '<a href=" ' . admin_url('admin.php?wprc_c=repository-login&wprc_action=RepositoryLogin&repository_id=' . $plugin['repository']->id) . '&buyurl='.rawurlencode($status['url']).'" class="thickbox" title="' . __('Buy', 'installer') . '">' . __('Buy ' , 'installer') . ' (' . $plugin['currency'] . $plugin['price'].')</a>';
						//$action_links[] = '<a href=" ' . $status['url']  . '" class="thickbox" title="' . __('Buy', 'installer') . '">' . __('Buy' , 'installer') . ' (' . $plugin['currency'] . $plugin['price'].')</a>';
						$action_links[] = '<a href=" ' . $status['url'].'&TB_iframe=true'.'" class="thickbox" title="' . sprintf(__('Buy %s', 'installer'),$name) . '">' . sprintf(__('Buy %s' , 'installer'),'(' . $plugin['currency']->symbol . $plugin['price'].' '.$plugin['currency']->name.')') . '</a>';
                        }

                        if(empty($plugin['repository']->repository_username) && empty($plugin['repository']->repository_password)){
                            $action_links[] = '<a href=" ' . admin_url('admin.php?wprc_c=repository-login&amp;wprc_action=RepositoryLogin&amp;repository_id=' . $plugin['repository']->id.'&amp;_wpnonce='.$nonce_login) . '" class="thickbox" title="' . __('Log in', 'installer') . '">' . __('Login' , 'installer') . '</a>';
                        }
                        break;
					case 'latest_installed':
					case 'newer_installed':
						$action_links[] = '<span title="' . esc_attr__( 'This plugin is already installed and is up to date', 'installer' ) . ' ">' . __( 'Installed', 'installer' ) . '</span>';
						break;
				}
				if (isset($plugin['message'])  && !empty($plugin['message']))
				{
					$message=WPRC_Functions::formatMessage($plugin['message']);
					if (isset($plugin['message_type']) && $plugin['message_type']=='notify')
						WPRC_AdminNotifier::addMessage('wprc-plugin-info-'.$plugin['slug'],$message);
					elseif (!$showedmessage)
						$action_links[]=$message;
				}
			}

            // add check compatibility link
//            $action_links[] = '<a href="' . self_admin_url( 'plugin-install.php?tab=plugin-information&amp;repository_id='. $plugin['repository']->id .'&amp;plugin=' . $plugin['slug'] .
//                '&amp;TB_iframe=true&amp;width=600&amp;height=550' ) . '" class="thickbox" title="' .
//                esc_attr( sprintf( __( 'Check compatibility of "%s" plugin with activated extensions', 'installer' ), $name ) ) . '">' . __( 'Check compatibility', 'installer' ) . '</a>';
			$slug = ( isset( $plugin['slug'] ) ) ? '&amp;extension_slug=' . $plugin['slug'] : '';
            $action_links[] = '<a href="' . self_admin_url( 'admin.php?wprc_c=repository-reporter&amp;wprc_action=checkCompatibility&amp;repository_id='. $plugin['repository']->id .'&amp;repository_url='.$plugin['repository']->repository_endpoint_url.'&amp;extension_name=' . $plugin['name'] .
                '&amp;extension_version=' . $plugin['version'] . $slug . '&amp;extension_type_singular=plugin&amp;extension_type=plugins&amp;TB_iframe=true&amp;width=300&amp;height=400' ) . '" class="thickbox" title="' .
                esc_attr( sprintf( __( 'Check compatibility status for "%s" plugin', 'installer' ), $name ) ) . '">' . __( 'Check compatibility', 'installer' ) . '</a>';

            $action_links = apply_filters( 'plugin_install_action_links', $action_links, $plugin );
		
		
		if (!isset($plugin['num_ratings']) || empty($plugin['num_ratings']))
			$plugin['num_ratings']=0;
		if (!isset($plugin['rating']) || empty($plugin['rating']))
			$plugin['rating']=0;
		?>
		<tr>
			<td class="name column-name"<?php echo $style['name']; ?>><strong><?php echo $title; ?></strong>
				<div class="action-links"><?php if ( !empty( $action_links ) ) echo implode( ' | ', $action_links ); ?></div>
			</td>
			<td class="vers column-version"<?php echo $style['version']; ?>><?php echo $version; ?></td>
			<td class="vers column-rating"<?php echo $style['rating']; ?>>
				<?php 
				global $wp_version;
				if (version_compare($wp_version, "3.4", "<"))
				{
				?>
				<div class="star-holder" title="<?php printf( _n( '(based on %s rating)', '(based on %s ratings)', $plugin['num_ratings'], 'installer' ), number_format_i18n( intval($plugin['num_ratings']) ) ) ?>">
					<div class="star star-rating" style="width: <?php echo esc_attr( $plugin['rating'] ) ?>px"></div>
					<?php
						$color = get_user_option('admin_color');
						if ( empty($color) || 'fresh' == $color )
							$star_url = admin_url( 'images/gray-star.png?v=20110615' ); // 'Fresh' Gray star for list tables
						else
							$star_url = admin_url( 'images/star.png?v=20110615' ); // 'Classic' Blue star
					?>
					<div class="star star5"><img src="<?php echo $star_url; ?>" alt="<?php esc_attr_e( '5 stars' ) ?>" /></div>
					<div class="star star4"><img src="<?php echo $star_url; ?>" alt="<?php esc_attr_e( '4 stars' ) ?>" /></div>
					<div class="star star3"><img src="<?php echo $star_url; ?>" alt="<?php esc_attr_e( '3 stars' ) ?>" /></div>
					<div class="star star2"><img src="<?php echo $star_url; ?>" alt="<?php esc_attr_e( '2 stars' ) ?>" /></div>
					<div class="star star1"><img src="<?php echo $star_url; ?>" alt="<?php esc_attr_e( '1 star' ) ?>" /></div>
				</div>
			<?php } else { ?>
				<div class="star-holder" title="<?php printf( _n( '(based on %s rating)', '(based on %s ratings)', $plugin['num_ratings'], 'installer' ), number_format_i18n( intval($plugin['num_ratings']) ) ) ?>">
					<div class="star star-rating" style="width: <?php echo esc_attr( str_replace( ',', '.', $plugin['rating'] ) ); ?>px"></div>
				</div>
			<?php } ?>
			</td>
			<td class="desc column-description"<?php echo $style['description']; ?>><?php echo $description, $author; ?></td>
            <td class="source column-source" align="left"><?php echo $plugin_source; ?></td>
            <td class="price column-price" align="left"><?php echo $plugin_price; ?></td>
		</tr>
		<?php
		}
    }
}
?>