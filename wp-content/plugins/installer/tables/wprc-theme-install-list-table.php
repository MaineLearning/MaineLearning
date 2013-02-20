<?php
class WPRC_ThemeInstall_List_Table extends WP_Theme_Install_List_Table{

    private $nonce_login='';
    
	/*function __construct( $args = array() ) {
		$args = wp_parse_args( $args, array(
			'plural' => '',
			'singular' => '',
			'ajax' => false
		) );
        
        $args['ajax']=false;
        
		$screen = get_current_screen();

		add_filter( "manage_{$screen->id}_columns", array( &$this, 'get_columns' ), 0 );

		if ( !$args['plural'] )
			$args['plural'] = $screen->base;

		$args['plural'] = sanitize_key( $args['plural'] );
		$args['singular'] = sanitize_key( $args['singular'] );

		$this->_args = $args;

		if ( $args['ajax'] ) {
			// wp_enqueue_script( 'list-table' );
			add_action( 'admin_footer', array( &$this, '_js_vars' ) );
		}
	}*/

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
    
    private function get_current_tab()
    {
        return $_GET['tab'];
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

		include_once( ABSPATH . 'wp-admin/includes/theme-install.php' );

		global $tabs, $tab, $paged, $type, $term, $theme_field_defaults;

		wp_reset_vars( array( 'tab' ) );

		$paged = $this->get_pagenum();

		$repositories_ids = array();

        if( ! isset( $_GET['repos'] ) ) {
        	$rm = WPRC_Loader::getModel('repositories');
        	$repos = $rm -> getRepositoriesIds('enabled_repositories','themes');
        	$repos_number = count( $repos );
        }
        else {
        	$repos_number = count( $_GET['repos'] );
        }        
        
		$per_page = WPRC_THEMES_API_QUERY_THEMES_PER_PAGE;

		// These are the tabs which are shown on the page,
		$tabs = array();
		$tabs['dashboard'] = __( 'Search' ,'installer');
		if ( 'search' == $tab )
			$tabs['search']	= __( 'Search Results' ,'installer');
		$tabs['upload'] = __( 'Upload' ,'installer');
		$tabs['featured'] = _x( 'Featured','Theme Installer' ,'installer');
		//$tabs['popular']  = _x( 'Popular','Theme Installer' );
		$tabs['new']      = _x( 'Newest','Theme Installer' ,'installer');
		$tabs['updated']  = _x( 'Recently Updated','Theme Installer' ,'installer');

		$nonmenu_tabs = array( 'theme-information' ); // Valid actions to perform which do not have a Menu item.

		$tabs = apply_filters( 'install_themes_tabs', $tabs );
		$nonmenu_tabs = apply_filters( 'install_themes_nonmenu_tabs', $nonmenu_tabs );

		// If a non-valid menu tab has been selected, And its not a non-menu action.
		if ( empty( $tab ) || ( ! isset( $tabs[ $tab ] ) && ! in_array( $tab, (array) $nonmenu_tabs ) ) )
			$tab = key( $tabs );

		$args = array( 'page' => $paged, 'per_page' => $per_page, 'fields' => $theme_field_defaults );

		switch ( $tab ) {
			case 'search':
				$type = isset( $_REQUEST['type'] ) ? stripslashes( $_REQUEST['type'] ) : '';
				$term = isset( $_REQUEST['s'] ) ? stripslashes( $_REQUEST['s'] ) : '';

				switch ( $type ) {
					case 'tag':
						$terms = explode( ',', $term );
						$terms = array_map( 'trim', $terms );
						$terms = array_map( 'sanitize_title_with_dashes', $terms );
						$args['tag'] = $terms;
						break;
					case 'author':
						$args['author'] = $term;
						break;
					case 'term':
					// make search term default in case type does not exist (wp34)
					default:
						$args['search'] = $term;
						break;
				}

				if ( isset($_REQUEST['features']) && (is_array($_REQUEST['features']) || !empty( $_REQUEST['features'] )) ) {
					$terms = $_REQUEST['features'];
					$terms = array_map( 'trim', $terms );
					$terms = array_map( 'sanitize_title_with_dashes', $terms );
					$args['tag'] = $terms;
					$_REQUEST['s'] = implode( ',', $terms );
					$_REQUEST['type'] = 'tag';
					//$args['type'] = 'tag';
					//$args['search'] = $_REQUEST['search'];
				}

				add_action( 'install_themes_table_header', 'install_theme_search_form' );
				break;

			case 'featured':
			//case 'popular':
			case 'new':
			case 'updated':
				$args['browse'] = $tab;
				break;

			default:
				$args = false;
		}

		if ( !$args )
			return;

		$api = themes_api( 'query_themes', $args );

		if ( is_wp_error( $api ) )
			wp_die( $api->get_error_message() . '</p> <p><a href="#" onclick="document.location.reload(); return false;">' . __( 'Try again' ,'installer') . '</a>' );

		$repo_model = WPRC_Loader::getModel('repositories');
		if ( isset( $_GET['repos'] ) ) {
			$repos = $_GET['repos'];
		}
		else {
			$rm = WPRC_Loader::getModel('repositories');
    		$repos = $rm -> getRepositoriesIds('enabled_repositories','themes');
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

		if ( $api -> info['results'] > WPRC_THEMES_API_QUERY_THEMES_PER_PAGE && count( $repos ) > 1 && ( $repo_results_gt_zero > 1 ) ) {
			
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
			
			$filtered_api -> themes = array();

			// If we are currently on a tab, we'll show only those results
			if ( is_array( $this -> repositories_tabs ) && count( $this -> repositories_tabs ) > 0 )
				$current_repo = ( isset( $_GET['repo-tab'] ) ) ? $_GET['repo-tab'] : $this -> repositories_tabs[0]['id'];

			foreach ( $api -> themes as $theme ) {
				if ( $theme -> repository_id == $current_repo ) {
					$filtered_api -> themes[] = $theme;
				}
				else {
					$filtered_api -> info['results']--;
				}

			}
			$filtered_api -> info['results'] = $this -> results_per_repo[ $current_repo ]['results'];
			$filtered_api -> info['total_pages'] = (int)ceil( $filtered_api -> info['results'] / WPRC_THEMES_API_QUERY_THEMES_PER_PAGE );

		}
		else {
			$filtered_api = $api;
		}

		$this->items = $filtered_api->themes;     
		//$this->items = $api;     
       	
       	$this->set_pagination_args( array(
			'total_items' => $filtered_api->info['results'],
			'per_page' => $per_page,
		) );
	}
    


	function no_items() {
		_e( 'No themes match your request.' ,'installer');
	}
	
	function display_rows() {
		global $wp_version;
		
		//print_r($this->items);
		//return;
        
        $this->nonce_login = wp_create_nonce('installer-login-link');
		
		$checkcompatibility=__('Check Compatibility','installer');
		if (version_compare($wp_version, "3.4", ">=")) // if ver >= 3.4
		{
			echo "<script language=\"javascript\">
			jQuery(document).ready(function()
			{
				var item = '';
				var cur_href = '';
				var new_href = '';
				var price = 0;
				var repository_name = '';
				var repository_id = 0;
				var repository_endpoint_url = '';
				var version = '';
				var name = '';
				jQuery('.available-theme').each(function(i, theme_div)
				{
					item = jQuery(theme_div);
					price = item.find('.meta .price').text();
					repository_name = item.find('.meta .repository').text();
					repository_id = item.find('.meta .repository_id').text();
					repository_endpoint_url = item.find('.meta .repository_endpoint_url').text();
					version = item.find('.meta .version').text();
					name = item.find('.meta .name').text();

					item.find('.action-links ul').append(' <li> <a class =\"thickbox thickbox-compatibility onclick check-compatibility\" href=\"' + userSettings.url + 'wp-admin/theme-install.php?wprc_c=repository-reporter&amp;wprc_action=checkCompatibility&amp;repository_id=' + repository_id + '&amp;repository_url=' + repository_endpoint_url + '&amp;extension_name=' + name +
					'&amp;extension_version=' + version + '&amp;extension_type_singular=theme&amp;extension_type=themes&amp;TB_iframe=true&amp;width=500&amp;height=400\"  title=\"' + wprcLang.check_compatibility_of_the_theme + '\">$checkcompatibility</a></li>');
                    
					item.find('.action-links').append('<br style=\"clear:both\"/><br /><strong>Price:</strong> ' + price + '<br><strong>Source:</strong> ' + repository_name);
					
                    cur_href = item.find('.install-theme-preview').attr('href');
					new_href = cur_href.replace('tab=theme-information','tab=theme-information&repository_id=' + repository_id);
					item.find('.install-theme-preview').attr('href', new_href);
					cur_href = item.find('.install-now').attr('href');
					new_href = cur_href+'&repository_id=' + repository_id;
					item.find('.install-now').attr('href', new_href);
				});
			});
			</script>";        

			$currency_sign = '$';
			$themes = $this->items;
			foreach ( $themes as $theme ) {
					?>
					<div class="available-theme installable-theme"><?php
					//$price = ($theme->price > 0) ? $currency_sign.$theme->price : __('Free','installer');
					$price = ($theme->price > 0) ? $theme->currency->symbol.$theme->price.' ('.$theme->currency->name.')' : __('Free','installer');
					echo '<div class="meta" style="display:none;">
						<span class="price">'.$price.'</span>
						<span class="repository">'.$theme->repository->repository_name.'</span>
						<span class="repository_id">'.$theme->repository->id.'</span>
						<span class="repository_endpoint_url">'.$theme->repository->repository_endpoint_url.'</span>
						<span class="version">'.$theme->version.'</span>
						<span class="name">'.$theme->name.'</span>
					</div>';
						$this->single_row( $theme );
					?></div>
			<?php } // end foreach $theme_names

			$this->theme_installer();
			
			return;
		}
		
		$themes = $this->items;

        wp_enqueue_script('thickbox',null,array('jquery'));
        wp_enqueue_style('thickbox.css', '/'.WPINC.'/js/thickbox/thickbox.css', null, '1.0');

        echo "<script language=\"javascript\">
        jQuery(document).ready(function()
        {
            var item = '';
            var cur_href = '';
            var new_href = '';
            var price = 0;
            var repository_name = '';
            var repository_id = 0;
            var repository_endpoint_url = '';
            var version = '';
            var name = '';
            jQuery('.available-theme').each(function(i, theme_div)
            {
                item = jQuery(theme_div);
                price = item.find('.meta .price').text();
                repository_name = item.find('.meta .repository').text();
                repository_id = item.find('.meta .repository_id').text();
                repository_endpoint_url = item.find('.meta .repository_endpoint_url').text();
                version = item.find('.meta .version').text();
                name = item.find('.meta .name').text();

                item.find('.action-links').append(' | <a class =\"thickbox thickbox-compatibility onclick check-compatibility\" href=\"' + userSettings.url + 'wp-admin/theme-install.php?wprc_c=repository-reporter&amp;wprc_action=checkCompatibility&amp;repository_id=' + repository_id + '&amp;repository_url=' + repository_endpoint_url + '&amp;extension_name=' + name +
                '&amp;extension_version=' + version + '&amp;extension_type_singular=theme&amp;extension_type=themes&amp;TB_iframe=true&amp;width=500&amp;height=400\"  title=\"' + wprcLang.check_compatibility_of_the_theme + '\">$checkcompatibility</a><br><br>'+
                '<strong>Price:</strong> ' + price + '<br><strong>Source:</strong> ' + repository_name);
                
                cur_href = item.find('.thickbox.thickbox-preview.onclick').attr('href');
               // new_href = cur_href.replace('tab=theme-information','tab=theme-information&repository_id=' + repository_id, cur_href);
                new_href = cur_href.replace('tab=theme-information','tab=theme-information&repository_id=' + repository_id);
                item.find('.thickbox.thickbox-preview.onclick').attr('href', new_href);
            });
        });
        </script>";        
        
		$theme_names = array_keys( $themes );
        $currency_sign = '$';
        
		foreach ( $theme_names as $theme_name ) {
				$class = array( 'available-theme' );
                //$price = ($themes[$theme_name]->price > 0) ? $currency_sign.$themes[$theme_name]->price : __('Free');
                $price = ($themes[$theme_name]->price > 0) ? $themes[$theme_name]->currency->symbol.$themes[$theme_name]->price.' ('.$themes[$theme_name]->currency->name.')' : __('Free');
        
				echo '<div class="'.join( ' ', $class ).'">';
                echo '<div class="meta" style="display:none;">
                    <span class="price">'.$price.'</span>
                    <span class="repository">'.$themes[$theme_name]->repository->repository_name.'</span>
                    <span class="repository_id">'.$themes[$theme_name]->repository->id.'</span>
                    <span class="repository_endpoint_url">'.$themes[$theme_name]->repository->repository_endpoint_url.'</span>
                    <span class="version">'.$themes[$theme_name]->version.'</span>
                    <span class="name">'.$themes[$theme_name]->name.'</span>
                </div>';

					if ( isset( $themes[$theme_name] ) )
						$this->wprc_display_theme( $themes[$theme_name] );
				echo '</div>';
		} // end foreach $theme_names
	}
	
	function single_row( $theme, $actions = null, $show_details = true ) {
		global $themes_allowedtags,$wp_version;
		
		if (version_compare($wp_version,'3.4','>='))
		{

			if ( empty( $theme ) )
				return;

			$name   = wp_kses( $theme->name,   $themes_allowedtags );
			$author = wp_kses( $theme->author, $themes_allowedtags );

			$preview_title = sprintf( __('Preview &#8220;%s&#8221;','installer'), $name );
			$preview_url   = add_query_arg( array(
				'tab'   => 'theme-information',
				'theme' => $theme->slug,
			) );

			$actions = array();

			$install_url = add_query_arg( array(
				'action' => 'install-theme',
				'theme'  => $theme->slug,
			), self_admin_url( 'update.php' ) );

			$update_url = add_query_arg( array(
				'action' => 'upgrade-theme',
				'theme'  => $theme->slug,
			), self_admin_url( 'update.php' ) );

			//if (!empty($theme->download_link))
			$status = $this->_get_theme_status( $theme );
			if (($status=='latest_installed' || $status=='newer_installed') || !isset($theme->purchase_link) || empty($theme->purchase_link) /*&& (!isset($theme->message) || empty($theme->message))*/)
			{

			switch ( $status ) {
				default:
				case 'install':
                    $theme_url=wp_nonce_url( $install_url, 'install-theme_' . $theme->slug );
                    if (isset($theme->repository_id))
                        $theme_url=add_query_arg(array('repository_id'=>$theme->repository_id),$theme_url);
					$actions[] = '<a class="install-now" href="' . esc_url( $theme_url ) . '" title="' . esc_attr( sprintf( __( 'Install %s' ,'installer'), $name ) ) . '">' . __( 'Install Now' ,'installer') . '</a>';
					break;
				case 'update_available':
                    $theme_url=wp_nonce_url( $update_url, 'upgrade-theme_' . $theme->slug );
                    if (isset($theme->repository_id))
                        $theme_url=add_query_arg(array('repository_id'=>$theme->repository_id),$theme_url);
					$actions[] = '<a class="install-now" href="' . esc_url( $theme_url ) . '" title="' . esc_attr( sprintf( __( 'Update to version %s' ,'installer'), $theme->version ) ) . '">' . __( 'Update' ,'installer') . '</a>';
					break;
				case 'newer_installed':
				case 'latest_installed':
					$actions[] = '<span class="install-now" title="' . esc_attr__( 'This theme is already installed and is up to date' ,'installer') . '">' . _x( 'Installed', 'theme' ,'installer') . '</span>';
					break;
			}
			}	
			if (isset($theme->message) && !empty($theme->message))
			{
				//echo wp_kses( $theme->message,   $themes_allowedtags );
				$message=WPRC_Functions::formatMessage((object)$theme->message);
				if (isset($theme->message_type) && $theme->message_type=='notify')
					WPRC_AdminNotifier::addMessage('wprc-theme-info-'.$theme->slug,$message);
				else
					$actions[]= $message;
			}
			elseif (!($status=='latest_installed' || $status=='newer_installed') && (isset($theme->purchase_link) && !empty($theme->purchase_link)) && ($theme->price != ''))
			{
				$purchase_url=WPRC_Functions::sanitizeURL($theme->purchase_link);
				$return_url=rawurlencode(admin_url( 'theme-install.php?tab=theme-information&repository_id='. $theme->repository->id .'&theme=' . $theme->slug));
				$salt=rawurlencode($theme->salt);
				if (strpos($purchase_url,'?'))
					$url_glue='&';
				else
					$url_glue='?';
				$purchase_url.=$url_glue.'return_to='.$return_url.'&rsalt='.$salt;

				/*$status = array(
					'status'    => 'paid',
					'url'       => $purl,
					'version'   => $theme->version
				);*/
				$actions[] = '<a class="install-theme-preview" href="' . $purchase_url .'?TB_iframe=true&width=640&height=484'. '" title="' . esc_attr( sprintf( __( 'Buy %s', 'installer' ), $name ) ) . '">' . sprintf(__( 'Buy %s','installer' ),'('.$theme->currency->symbol.$theme->price.' '.$theme->currency->name.')'). '</a>';
				if(empty($theme->repository->repository_username) && empty($theme->repository->repository_password)){
					$actions[] = '<a href=" ' . admin_url('admin.php?wprc_c=repository-login&amp;wprc_action=RepositoryLogin&amp;repository_id=' . $theme->repository->id.'&amp;_wpnonce='.$this->nonce_login) . '" class="thickbox" title="' . __('Log in', 'installer') . '">' . __('Login' , 'installer') . '</a>';
				}
			}
			$actions[] = '<a class="install-theme-preview" href="' . esc_url( $preview_url ) . '" title="' . esc_attr( sprintf( __( 'Preview %s','installer' ), $name ) ) . '">' . __( 'Preview' ,'installer') . '</a>';

			$actions = apply_filters( 'theme_install_actions', $actions, $theme );

			?>
			<a class="screenshot install-theme-preview" href="<?php echo esc_url( $preview_url ); ?>" title="<?php echo esc_attr( $preview_title ); ?>">
				<img src='<?php echo esc_url( $theme->screenshot_url ); ?>' width='150' />
			</a>

			<h3><?php echo $name; ?></h3>
			<div class="theme-author"><?php printf( __( 'By %s' ,'installer'), $author ); ?></div>

			<div class="action-links">
				<ul>
					<?php foreach ( $actions as $action ): ?>
						<li><?php echo $action; ?></li>
					<?php endforeach; ?>
					<li class="hide-if-no-js"><a href="#" class="theme-detail" tabindex='4'><?php _e('Details','installer') ?></a></li>
				</ul>
			</div>

			<?php
			$this->install_theme_info( $theme );
		}
		else
		{
			if ( empty($theme) )
				return;

			$name = wp_kses($theme->name, $themes_allowedtags);
			$desc = wp_kses($theme->description, $themes_allowedtags);
			//if ( strlen($desc) > 30 )
			//	$desc =  substr($desc, 0, 15) . '<span class="dots">...</span><span>' . substr($desc, -15) . '</span>';

			$preview_link = $theme->preview_url . '?TB_iframe=true&amp;width=640&amp;height=484';
			if ( !is_array($actions) ) {
				$actions = array();
			}
			//if (!empty($theme->download_link))
			if (!isset($theme->purchase_link) || empty($theme->purchase_link) /*&& (!isset($theme->message) || empty($theme->message))*/)
			{
				$actions[] = '<a href="' . self_admin_url('theme-install.php?tab=theme-information&amp;repository_id='. $theme->repository->id.'&amp;theme=' . $theme->slug .	'&amp;TB_iframe=true&amp;tbWidth=640&amp;tbHeight=484') . '" class="thickbox thickbox-preview onclick" title="' . esc_attr(sprintf(__('Install &#8220;%s&#8221;','installer'), $name)) . '">' . __('Install','installer') . '</a>';
			}
			if (isset($theme->message) && !empty($theme->message))
			{
				//echo wp_kses( $theme->message,   $themes_allowedtags );
				$message=WPRC_Functions::formatMessage((object)$theme->message);
				if (isset($theme->message_type) && $theme->message_type=='notify')
					WPRC_AdminNotifier::addMessage('wprc-theme-info-'.$theme->slug,$message);
				else
					$actions[] = $message;
			}
			elseif(isset($theme->purchase_link) && !empty($theme->purchase_link) && ($theme->price != ''))
			{
				$purchase_url=WPRC_Functions::sanitizeURL($theme->purchase_link);
				$return_url=rawurlencode(admin_url( 'theme-install.php?tab=theme-information&repository_id='. $theme->repository->id .'&theme=' . $theme->slug));
				$salt=rawurlencode($theme->salt);
				if (strpos($purchase_url,'?'))
					$url_glue='&';
				else
					$url_glue='?';
				$purchase_url.=$url_glue.'return_to='.$return_url.'&rsalt='.$salt;

				/*$status = array(
					'status'    => 'paid',
					'url'       => $purl,
					'version'   => $theme->version
				);*/
				$actions[] = '<a class="thickbox thickbox-preview onclick" href="' . $purchase_url . '&amp;TB_iframe=true&amp;tbWidth=640&amp;tbHeight=484' . '" title="' . esc_attr( sprintf( __( 'Buy %s', 'installer' ), $name ) ) . '">' . sprintf(__( 'Buy %s','installer' ),'('.$theme->currency->symbol.$theme->price.' '.$theme->currency->name.')'). '</a>';
				if(empty($theme->repository->repository_username) && empty($theme->repository->repository_password)){
					$actions[] = '<a href=" ' . admin_url('admin.php?wprc_c=repository-login&amp;wprc_action=RepositoryLogin&amp;repository_id=' . $theme->repository->id.'&amp;_wpnonce='.$this->nonce_login) . '" class="thickbox" title="' . __('Log in', 'installer') . '">' . __('Login' , 'installer') . '</a>';
				}
			}
				if ( !is_network_admin() )
					$actions[] = '<a href="' . $preview_link . '" class="thickbox thickbox-preview onclick previewlink" title="' . esc_attr(sprintf(__('Preview &#8220;%s&#8221;','installer'), $name)) . '">' . __('Preview','installer') . '</a>';
				$actions = apply_filters('theme_install_action_links', $actions, $theme);

			$actions = implode ( ' | ', $actions );
			?>
		<a class='thickbox thickbox-preview screenshot'
			href='<?php echo esc_url($preview_link); ?>'
			title='<?php echo esc_attr(sprintf(__('Preview &#8220;%s&#8221;','installer'), $name)); ?>'>
		<img src='<?php echo esc_url($theme->screenshot_url); ?>' width='150' />
		</a>
		<h3><?php echo $name ?></h3>
		<span class='action-links'><?php echo $actions ?></span>
		<p><?php echo $desc ?></p>
		<?php if ( $show_details ) { ?>
		<a href="#theme_detail" class="theme-detail hide-if-no-js" tabindex='4'><?php _e('Details','installer') ?></a>
		<div class="themedetaildiv hide-if-js">
		<p><strong><?php _e('Version:','installer') ?></strong> <?php echo wp_kses($theme->version, $themes_allowedtags) ?></p>
		<p><strong><?php _e('Author:','installer') ?></strong> <?php echo wp_kses($theme->author, $themes_allowedtags) ?></p>
		<?php if ( ! empty($theme->last_updated) ) : ?>
		<p><strong><?php _e('Last Updated:','installer') ?></strong> <span title="<?php echo $theme->last_updated ?>"><?php printf( __('%s ago','installer'), human_time_diff(strtotime($theme->last_updated)) ) ?></span></p>
		<?php endif; if ( ! empty($theme->requires) ) : ?>
		<p><strong><?php _e('Requires WordPress Version:','installer') ?></strong> <?php printf(__('%s or higher','installer'), $theme->requires) ?></p>
		<?php endif; if ( ! empty($theme->tested) ) : ?>
		<p><strong><?php _e('Compatible up to:','installer') ?></strong> <?php echo $theme->tested ?></p>
		<?php endif; if ( !empty($theme->downloaded) ) : ?>
		<p><strong><?php _e('Downloaded:','installer') ?></strong> <?php printf(_n('%s time', '%s times', $theme->downloaded,'installer'), number_format_i18n(intval($theme->downloaded))) ?></p>
		<?php endif; ?>
		<?php 
		if (!isset($theme->num_ratings) || empty($theme->num_ratings))
			$theme->num_ratings=0;
		if (!isset($theme->rating) || empty($theme->rating))
			$theme->rating=0;
		?>
		<div class="star-holder" title="<?php printf(_n('(based on %s rating)', '(based on %s ratings)', $theme->num_ratings,'installer'), number_format_i18n(intval($theme->num_ratings))) ?>">
			<div class="star star-rating" style="width: <?php echo esc_attr($theme->rating) ?>px"></div>
			<div class="star star5"><img src="<?php echo admin_url('images/star.png?v=20110615'); ?>" alt="<?php esc_attr_e('5 stars') ?>" /></div>
			<div class="star star4"><img src="<?php echo admin_url('images/star.png?v=20110615'); ?>" alt="<?php esc_attr_e('4 stars') ?>" /></div>
			<div class="star star3"><img src="<?php echo admin_url('images/star.png?v=20110615'); ?>" alt="<?php esc_attr_e('3 stars') ?>" /></div>
			<div class="star star2"><img src="<?php echo admin_url('images/star.png?v=20110615'); ?>" alt="<?php esc_attr_e('2 stars') ?>" /></div>
			<div class="star star1"><img src="<?php echo admin_url('images/star.png?v=20110615'); ?>" alt="<?php esc_attr_e('1 star') ?>" /></div>
		</div>
		</div>
		<?php }
			/*
			 object(stdClass)[59]
			 public 'name' => string 'Magazine Basic' (length=14)
			 public 'slug' => string 'magazine-basic' (length=14)
			 public 'version' => string '1.1' (length=3)
			 public 'author' => string 'tinkerpriest' (length=12)
			 public 'preview_url' => string 'http://wp-themes.com/?magazine-basic' (length=36)
			 public 'screenshot_url' => string 'http://wp-themes.com/wp-content/themes/magazine-basic/screenshot.png' (length=68)
			 public 'rating' => float 80
			 public 'num_ratings' => int 1
			 public 'homepage' => string 'http://wordpress.org/extend/themes/magazine-basic' (length=49)
			 public 'description' => string 'A basic magazine style layout with a fully customizable layout through a backend interface. Designed by <a href="http://bavotasan.com">c.bavota</a> of <a href="http://tinkerpriestmedia.com">Tinker Priest Media</a>.' (length=214)
			 public 'download_link' => string 'http://wordpress.org/extend/themes/download/magazine-basic.1.1.zip' (length=66)
			 */
		}
	}

	/*
	 * Prints the wrapper for the theme installer.
	 */
	function theme_installer() {
		?>
		<div id="theme-installer" class="wp-full-overlay expanded">
			<div class="wp-full-overlay-sidebar">
				<div class="wp-full-overlay-header">
					<a href="#" class="close-full-overlay"><?php _e( '&larr; Close' ,'installer'); ?></a>
				</div>
				<div class="wp-full-overlay-sidebar-content">
					<div class="install-theme-info"></div>
				</div>
				<div class="wp-full-overlay-footer">
					<a href="#" class="collapse-sidebar button-secondary" title="<?php esc_attr_e('Collapse Sidebar','installer'); ?>">
						<span class="collapse-sidebar-label"><?php _e('Collapse','installer'); ?></span>
						<span class="collapse-sidebar-arrow"></span>
					</a>
				</div>
			</div>
			<div class="wp-full-overlay-main"></div>
		</div>
		<?php
	}

	/*
	 * Prints the wrapper for the theme installer with a provided theme's data.
	 * Used to make the theme installer work for no-js.
	 *
	 * @param object $theme - A WordPress.org Theme API object.
	 */
	function theme_installer_single( $theme ) {
		?>
		<div id="theme-installer" class="wp-full-overlay single-theme">
			<div class="wp-full-overlay-sidebar">
				<?php $this->install_theme_info( $theme ); ?>
			</div>
			<div class="wp-full-overlay-main">
				<iframe src="<?php echo esc_url( $theme->preview_url ); ?>"></iframe>
			</div>
		</div>
		<?php
	}

	/*
	 * Prints the info for a theme (to be used in the theme installer modal).
	 *
	 * @param object $theme - A WordPress.org Theme API object.
	 */
	function install_theme_info( $theme ) {
		global $themes_allowedtags;

		if ( empty( $theme ) )
			return;

		$name   = wp_kses( $theme->name,   $themes_allowedtags );
		$author = wp_kses( $theme->author, $themes_allowedtags );

		$num_ratings = sprintf( _n( '(based on %s rating)', '(based on %s ratings)', $theme->num_ratings ,'installer'), number_format_i18n( intval($theme->num_ratings) ) );

		$install_url = add_query_arg( array(
			'action' => 'install-theme',
			'theme'  => $theme->slug,
		), self_admin_url( 'update.php' ) );

		$update_url = add_query_arg( array(
			'action' => 'upgrade-theme',
			'theme'  => $theme->slug,
		), self_admin_url( 'update.php' ) );


		?>
		<div class="install-theme-info"><?php
		//if (!empty($theme->download_link))
		$status = $this->_get_theme_status( $theme );
		if (($status=='newer_installed' || $status=='latest_installed') || !isset($theme->purchase_link) || empty($theme->purchase_link) /*&& (!isset($theme->message) || empty($theme->message))*/)
		{
			switch ( $status ) {
				default:
				case 'install':
                    $theme_url=wp_nonce_url( $install_url, 'install-theme_' . $theme->slug );
                    if (isset($theme->repository_id))
                        $theme_url=add_query_arg(array('repository_id'=>$theme->repository_id),$theme_url);
					echo '<a class="theme-install button-primary" href="' . esc_url( $theme_url ) . '">' . __( 'Install' ,'installer') . '</a>';
					break;
				case 'update_available':
				$update_themes = get_site_transient('update_themes');
				if ( is_object($update_themes) && isset($update_themes->response) ) {
					foreach ( (array)$update_themes->response as $theme_slug => $theme_info ) {
						if ( $theme_slug === $theme->slug  && ((isset($theme->download_link) && !empty($theme->download_link)) || (isset($theme->package) && !empty($theme->package)))) {
							$type = 'update_available';
							$update_file = $theme_slug;
							if ((isset($theme->download_link) && !empty($theme->download_link)))
							{
								$update_themes->response[$theme_slug]['package']=$theme->download_link;
								set_site_transient('update_themes',$update_themes);
							}
							else if ((isset($theme->package) && !empty($theme->package)))
							{
								$update_themes->response[$theme_slug]['package']=$theme->package;
								set_site_transient('update_themes',$update_themes);
							}
							break;
						}
					}
					}
                    $theme_url=wp_nonce_url( $update_url, 'upgrade-theme_' . $theme->slug );
                    if (isset($theme->repository_id))
                        $theme_url=add_query_arg(array('repository_id'=>$theme->repository_id),$theme_url);
					echo '<a class="theme-install button-primary" href="' . esc_url( $theme_url ) . '" title="' . esc_attr( sprintf( __( 'Update to version %s' ,'installer'), $theme->version ) ) . '">' . __( 'Update' ,'installer') . '</a>';
					break;
				case 'newer_installed':
				case 'latest_installed':
					echo '<span class="theme-install" title="' . esc_attr__( 'This theme is already installed and is up to date' ,'installer') . '">' . _x( 'Installed', 'theme' ,'installer') . '</span>';
					break;
			} 
		}
		if (isset($theme->message) && !empty($theme->message))
		{
			//echo wp_kses( $theme->message,   $themes_allowedtags );
			$message=WPRC_Functions::formatMessage((object)$theme->message);
			if (isset($theme->message_type) && $theme->message_type=='notify')
				WPRC_AdminNotifier::addMessage('wprc-theme-info-'.$theme->slug,$message);
			else
				echo $message;
		}
		elseif(!($status=='newer_installed' || $status=='latest_installed') && (isset($theme->purchase_link) && !empty($theme->purchase_link) && ($theme->price != '')))
		{
			$repository_id=(isset($theme->repository_id))?$theme->repository_id:$_GET['repository_id'];
			$purchase_url=WPRC_Functions::sanitizeURL($theme->purchase_link);
			$return_url=rawurlencode(admin_url( 'theme-install.php?tab=theme-information&repository_id='. $repository_id .'&theme=' . $theme->slug));
			$salt=rawurlencode($theme->salt);
			if (strpos($purchase_url,'?'))
				$url_glue='&';
			else
				$url_glue='?';
			$purchase_url.=$url_glue.'return_to='.$return_url.'&rsalt='.$salt;

			/*$status = array(
				'status'    => 'paid',
				'url'       => $purl,
				'version'   => $theme->version
			);*/
			//$actions[] = '<a class="install-theme-preview" href="' . $purchase_url . '" title="' . esc_attr( sprintf( __( 'Buy %s', 'installer' ), $name ) ) . '">' . __( 'Buy','installer' ) .' '.$theme->price.$theme->currency. '</a>';
			echo '<a class="theme-install button-primary" href="' .  $purchase_url . '">' . sprintf(__( 'Buy %s','installer' ) ,'('.$theme->currency->symbol.$theme->price.' '.$theme->currency->name.')'). '</a>';
		}
			?>
		<?php if (isset($theme->rauth) && $theme->rauth==false) { ?>
		<p><?php _e('Authorization Failed!','installer'); ?></p>
		<?php } ?>
			<h3 class="theme-name"><?php echo $name; ?></h3>
			<span class="theme-by"><?php printf( __( 'By %s' ,'installer'), $author ); ?></span>
			<?php if ( isset( $theme->screenshot_url ) ): ?>
				<img class="theme-screenshot" src="<?php echo esc_url( $theme->screenshot_url ); ?>" />
			<?php endif; ?>
			<div class="theme-details">
				<div class="star-holder" title="<?php echo esc_attr( $num_ratings ); ?>">
					<div class="star-rating" style="width:<?php echo esc_attr( intval( $theme->rating ) . 'px' ); ?>;"></div>
				</div>
				<div class="theme-version">
					<strong><?php _e('Version:','installer') ?> </strong>
					<?php echo wp_kses( $theme->version, $themes_allowedtags ); ?>
				</div>
				<div class="theme-description">
					<?php echo wp_kses( $theme->description, $themes_allowedtags ); ?>
				</div>
			</div>
			<input class="theme-preview-url" type="hidden" value="<?php echo esc_url( $theme->preview_url ); ?>" />
		</div>
		<?php
	}
	/**
	 * Check to see if the theme is already installed.
	 *
	 * @since 3.4
	 * @access private
	 *
	 * @param object $theme - A WordPress.org Theme API object.
	 * @return string Theme status.
	 */
	
	private function _wp_get_theme( $stylesheet = null, $theme_root = null ) {
		global $wp_theme_directories;

		if ( empty( $stylesheet ) )
			$stylesheet = get_stylesheet();

		if ( empty( $theme_root ) ) {
			$theme_root = get_raw_theme_root( $stylesheet );
			if ( false === $theme_root )
				$theme_root = WP_CONTENT_DIR . '/themes';
			elseif ( ! in_array( $theme_root, (array) $wp_theme_directories ) )
				$theme_root = WP_CONTENT_DIR . $theme_root;
		}

		if (class_exists('WP_Theme'))
			return new WP_Theme( $stylesheet, $theme_root );
		else
		{
			WPRC_Loader::includeClass('class-wp-theme.php');
			return new WP_Theme( $stylesheet, $theme_root );
		}
	}
	private function _get_theme_status( $theme ) {
		$status = 'install';

		if (function_exists('wp_get_theme'))
			$installed_theme = wp_get_theme( $theme->slug );
		else
			$installed_theme = $this->_wp_get_theme( $theme->slug );
			
		if ( $installed_theme->exists() ) {
			if ( version_compare( $installed_theme->get('Version'), $theme->version, '=' ) )
				$status = 'latest_installed';
			elseif ( version_compare( $installed_theme->get('Version'), $theme->version, '>' ) )
				$status = 'newer_installed';
			else
				$status = 'update_available';
		}
        
        if ($status == 'update_available')
        {
        $status='install';
        // Check to see if this theme is known to be installed, and has an update awaiting it.
        $update_themes = get_site_transient('update_themes');
        if ( false!=$update_themes && is_object($update_themes) && isset($update_themes->response) ) {
            foreach ( (array)$update_themes->response as $theme_slug => $theme_info ) {
                if ( $theme_slug === $theme->slug  && $theme_info['new_version']==$theme->version && ((isset($theme->download_link) && !empty($theme->download_link)) || (isset($theme->package) && !empty($theme->package)))) {
                    if ((isset($theme->download_link) && !empty($theme->download_link)))
                    {
                        $update_themes->response[$theme_slug]['package']=$theme->download_link;
                        set_site_transient('update_themes',$update_themes);
                        $theme_info['package']=$update_themes->response[$theme_slug]['package'];
                    }
                    else if ((isset($theme->package) && !empty($theme->package)))
                    {
                        $update_themes->response[$theme_slug]['package']=$theme->package;
                        set_site_transient('update_themes',$update_themes);
                        $theme_info['package']=$update_themes->response[$theme_slug]['package'];
                    }
                }
            if ( $theme_slug === $theme->slug  && (isset($theme_info['package']) && !empty($theme_info['package']))) {
                $status = 'update_available';
                $update_file = $theme_slug;
                break;
            }
        }
        }
        }
		
        return $status;
	}
	
	private function wprc_display_theme($theme, $actions = null, $show_details = true) {
		global $themes_allowedtags;

		if ( empty($theme) )
			return;

		$name = wp_kses($theme->name, $themes_allowedtags);
		$desc = wp_kses($theme->description, $themes_allowedtags);
		//if ( strlen($desc) > 30 )
		//	$desc =  substr($desc, 0, 15) . '<span class="dots">...</span><span>' . substr($desc, -15) . '</span>';

		$preview_link = $theme->preview_url . '?TB_iframe=true&amp;width=600&amp;height=400';
		$status = $this->_get_theme_status( $theme );
		if ( !is_array($actions) ) {
			$actions = array();
			if (($status=='latest_installed' || $status=='newer_installed') || !isset($theme->purchase_link) || empty($theme->purchase_link))
			{

			switch ( $status ) {
				default:
				case 'install':
					$actions[] = '<a href="' . self_admin_url('theme-install.php?tab=theme-information&amp;theme=' . $theme->slug .'&amp;TB_iframe=true&amp;tbWidth=640&amp;tbHeight=484') . '" class="thickbox thickbox-preview onclick" title="' . esc_attr(sprintf(__('Install &#8220;%s&#8221;','installer'), $name)) . '">' . __('Install','installer') . '</a>';
					break;
				case 'update_available':
					$actions[] = '<a href="' . self_admin_url('theme-install.php?tab=theme-information&amp;theme=' . $theme->slug .'&amp;TB_iframe=true&amp;tbWidth=640&amp;tbHeight=484') . '" class="thickbox thickbox-preview onclick" title="' . esc_attr( sprintf( __( 'Update to version %s' ,'installer'), $theme->version ) ) . '">' . __( 'Update' ,'installer') . '</a>';
					break;
				case 'newer_installed':
				case 'latest_installed':
					$actions[] = '<span class="install-now" title="' . esc_attr__( 'This theme is already installed and is up to date' ,'installer') . '">' . _x( 'Installed', 'theme' ,'installer') . '</span>';
					break;
			}
			}
			if (isset($theme->message) && !empty($theme->message))
			{
				//echo wp_kses( $theme->message,   $themes_allowedtags );
				$message=WPRC_Functions::formatMessage((object)$theme->message);
				if (isset($theme->message_type) && $theme->message_type=='notify')
					WPRC_AdminNotifier::addMessage('wprc-theme-info-'.$theme->slug,$message);
				else
					$actions[] = $message;
			}
			elseif (!($status=='latest_installed' || $status=='newer_installed') && (isset($theme->purchase_link) && !empty($theme->purchase_link) && isset($theme->price) && !empty($theme->price)))
			{
				//$actions[] = '<a href="' . self_admin_url('theme-install.php?tab=theme-information&amp;theme=' . $theme->slug .'&amp;TB_iframe=true&amp;tbWidth=640&amp;tbHeight=484') . '" class="thickbox thickbox-preview onclick" title="' . esc_attr(sprintf(__('Buy &#8220;%s&#8221;','installer'), $name)) . '">' . sprintf(__('Buy %s','installer') ,'('.$theme->price.$theme->currency.')'). '</a>';
				if ( current_user_can('install_themes') )
				{
					$purl=WPRC_Functions::sanitizeURL($theme->purchase_link);
					$return_url=rawurlencode(admin_url( 'theme-install.php?tab=theme-information&repository_id='. $theme->repository_id .'&theme=' . $theme->slug));
					$salt=rawurlencode($theme->salt);
					if (strpos($purl,'?'))
						$url_glue='&';
					else
						$url_glue='?';
					$purl.=$url_glue.'return_to='.$return_url.'&rsalt='.$salt;
					
					$actions[] = '<a href="' . $purl.'&amp;TB_iframe=true&amp;tbWidth=640&amp;tbHeight=484' . '" class="thickbox thickbox-preview onclick" title="' . esc_attr(sprintf(__('Buy &#8220;%s&#8221;','installer'), $name)) . '">' . sprintf(__('Buy %s','installer') ,'('.$theme->currency->symbol.$theme->price.' '.$theme->currency->name.')'). '</a>';
				if(empty($theme->repository->repository_username) && empty($theme->repository->repository_password)){
					$actions[] = '<a href=" ' . admin_url('admin.php?wprc_c=repository-login&amp;wprc_action=RepositoryLogin&amp;repository_id=' . $theme->repository->id.'&amp;_wpnonce='.$this->nonce_login) . '" class="thickbox" title="' . __('Log in', 'installer') . '">' . __('Login' , 'installer') . '</a>';
				}
				}
			}
			if ( !is_network_admin() )
				$actions[] = '<a href="' . $preview_link . '" class="thickbox thickbox-preview onclick previewlink" title="' . esc_attr(sprintf(__('Preview &#8220;%s&#8221;','installer'), $name)) . '">' . __('Preview','installer') . '</a>';
			$actions = apply_filters('theme_install_action_links', $actions, $theme);
		}

		$actions = implode ( ' | ', $actions );
		?>
	<a class='thickbox thickbox-preview screenshot'
		href='<?php echo esc_url($preview_link); ?>'
		title='<?php echo esc_attr(sprintf(__('Preview &#8220;%s&#8221;','installer'), $name)); ?>'>
	<img src='<?php echo esc_url($theme->screenshot_url); ?>' width='150' />
	</a>
	<h3><?php echo $name ?></h3>
	<span class='action-links'><?php echo $actions ?></span>
	<p><?php echo $desc ?></p>
	<?php if ( $show_details ) { ?>
	<a href="#theme_detail" class="theme-detail hide-if-no-js" tabindex='4'><?php _e('Details','installer') ?></a>
	<div class="themedetaildiv hide-if-js">
	<p><strong><?php _e('Version:','installer') ?></strong> <?php echo wp_kses($theme->version, $themes_allowedtags) ?></p>
	<p><strong><?php _e('Author:','installer') ?></strong> <?php echo wp_kses($theme->author, $themes_allowedtags) ?></p>
	<?php if ( ! empty($theme->last_updated) ) : ?>
	<p><strong><?php _e('Last Updated:','installer') ?></strong> <span title="<?php echo $theme->last_updated ?>"><?php printf( __('%s ago','installer'), human_time_diff(strtotime($theme->last_updated)) ) ?></span></p>
	<?php endif; if ( ! empty($theme->requires) ) : ?>
	<p><strong><?php _e('Requires WordPress Version:','installer') ?></strong> <?php printf(__('%s or higher','installer'), $theme->requires) ?></p>
	<?php endif; if ( ! empty($theme->tested) ) : ?>
	<p><strong><?php _e('Compatible up to:','installer') ?></strong> <?php echo $theme->tested ?></p>
	<?php endif; if ( !empty($theme->downloaded) ) : ?>
	<p><strong><?php _e('Downloaded:','installer') ?></strong> <?php printf(_n('%s time', '%s times', $theme->downloaded,'installer'), number_format_i18n(intval($theme->downloaded))) ?></p>
	<?php endif; ?>
		<?php 
		if (!isset($theme->num_ratings) || empty($theme->num_ratings))
			$theme->num_ratings=0;
		if (!isset($theme->rating) || empty($theme->rating))
			$theme->rating=0;
		?>
	<div class="star-holder" title="<?php printf(_n('(based on %s rating)', '(based on %s ratings)', $theme->num_ratings,'installer'), number_format_i18n(intval($theme->num_ratings))) ?>">
		<div class="star star-rating" style="width: <?php echo esc_attr($theme->rating) ?>px"></div>
		<div class="star star5"><img src="<?php echo admin_url('images/star.png?v=20110615'); ?>" alt="<?php esc_attr_e('5 stars') ?>" /></div>
		<div class="star star4"><img src="<?php echo admin_url('images/star.png?v=20110615'); ?>" alt="<?php esc_attr_e('4 stars') ?>" /></div>
		<div class="star star3"><img src="<?php echo admin_url('images/star.png?v=20110615'); ?>" alt="<?php esc_attr_e('3 stars') ?>" /></div>
		<div class="star star2"><img src="<?php echo admin_url('images/star.png?v=20110615'); ?>" alt="<?php esc_attr_e('2 stars') ?>" /></div>
		<div class="star star1"><img src="<?php echo admin_url('images/star.png?v=20110615'); ?>" alt="<?php esc_attr_e('1 star') ?>" /></div>
	</div>
	</div>
	<?php }
		/*
		 object(stdClass)[59]
		 public 'name' => string 'Magazine Basic' (length=14)
		 public 'slug' => string 'magazine-basic' (length=14)
		 public 'version' => string '1.1' (length=3)
		 public 'author' => string 'tinkerpriest' (length=12)
		 public 'preview_url' => string 'http://wp-themes.com/?magazine-basic' (length=36)
		 public 'screenshot_url' => string 'http://wp-themes.com/wp-content/themes/magazine-basic/screenshot.png' (length=68)
		 public 'rating' => float 80
		 public 'num_ratings' => int 1
		 public 'homepage' => string 'http://wordpress.org/extend/themes/magazine-basic' (length=49)
		 public 'description' => string 'A basic magazine style layout with a fully customizable layout through a backend interface. Designed by <a href="http://bavotasan.com">c.bavota</a> of <a href="http://tinkerpriestmedia.com">Tinker Priest Media</a>.' (length=214)
		 public 'download_link' => string 'http://wordpress.org/extend/themes/download/magazine-basic.1.1.zip' (length=66)
		 */
	}
}
?>
