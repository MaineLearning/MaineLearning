<?php
  /*
   Plugin Name: Theme Test Drive
   Plugin URI: http://www.prelovac.com/vladimir/wordpress-plugins/theme-test-drive
   Description: Safely test drive any theme while visitors are using the default one. Includes instant theme preview via thumbnail.
   Author: Vladimir Prelovac
   Version: 2.8.3
   Author URI: http://www.prelovac.com/vladimir/
   
   To-Do:
   - localization
   - theme upload
   - theme page snapshots
   */
  
  // // //  PLUGIN CODE // // //
  
  $themedrive_localversion = "2.8.3";
  
  $wp_themedrive_plugin_url = trailingslashit(get_bloginfo('wpurl')) . PLUGINDIR . '/' . dirname(plugin_basename(__FILE__));
  
  function themedrive_handle_theme($package)
  {
      // select theme handling by commenting one of these funcitons
      
      themedrive_handle_theme_liberal($package);
      
      //themedrive_handle_theme_rigid($package);  
  }
  
  
  function themedrive_unzip($file, $dir)
  {
      if (!current_user_can('edit_files')) {
          echo 'Oops, sorry you are not authorized to do this';
          return false;
      }
      if (!class_exists('PclZip')) {
          require_once(ABSPATH . 'wp-admin/includes/class-pclzip.php');
      }
      
      
      $unzipArchive = new PclZip($file);
      $list = $unzipArchive->properties();
      if (!$list['nb'])
          return false;
      //echo "Number of files in archive : ".$list['nb']."<br>";
      
      echo "Copying the files<br>";
      $result = $unzipArchive->extract(PCLZIP_OPT_PATH, $dir);
      if ($result == 0) {
          echo 'Could not unarchive the file: ' . $unzipArchive->errorInfo(true) . ' <br />';
          return false;
      } else {
          //print_r($result);
          foreach ($result as $item) {
              if ($item['status'] != 'ok')
                  echo $item['stored_filename'] . ' ... ' . $item['status'] . '<br>';
          }
          return true;
      }
  }
  
  function themedrive_handle_theme_liberal($package)
  {
      echo "Downloading the theme from " . $package . "<br>";
      $file = download_url($package);
      
      if (is_wp_error($file)) {
          echo 'Download failed: ' . $file->get_error_message();
          return;
      }
      
      echo "Unpacking the theme<br>";
      
      // Unzip theme to theme directory  
      //theme dir 
      $result = themedrive_unzip($file, ABSPATH . "wp-content/themes/");
      
      // Once extracted, delete the package
      unlink($file);
      
      if ($result)
          echo "<br>Theme installed successfully.<br><br>";
      else {
          echo "<br>Error installing the theme.<br><br>You can try installing the theme manually: <a href=\"$package\">$package</a><br><br>";
      }
      return;
  }
  
  
  
  function themedrive_handle_theme_rigid($package)
  {
      global $wp_filesystem;
      
      if (!$wp_filesystem || !is_object($wp_filesystem))
          WP_Filesystem($credentials);
      
      
      if (!is_object($wp_filesystem)) {
          echo '<strong><em>Could not access filesystem.</strong></em><br><br>';
          return;
      }
      
      
      
      if ($wp_filesystem->errors->get_error_code()) {
          echo '<strong><em>Filesystem error ' . $wp_filesystem->errors->get_error_message() . '</strong></em><br><br>';
          return;
      }
      
      //Get the Base folder
      $base = $wp_filesystem->get_base_dir();
      
      if (empty($base)) {
          echo '<strong><em>Unable to locate WordPress directory.</strong></em><br><br>';
          return;
      }
      
      
      
      echo "Downloading theme file from " . $package . "<br>";
      $file = download_url($package);
      
      if (is_wp_error($file)) {
          echo '<strong><em>Download failed : ' . $file->get_error_message() . '</strong></em><br><br>';
          return;
      }
      
      
      $working_dir = $base . 'wp-content/upgrade/themes';
      
      // Clean up working directory
      if ($wp_filesystem->is_dir($working_dir))
          $wp_filesystem->delete($working_dir, true);
      
      
      echo "Unpacking the theme<br>";
      // Unzip package to theme directory
      $result = unzip_file($file, $working_dir);
      if (is_wp_error($result)) {
          unlink($file);
          $wp_filesystem->delete($working_dir, true);
          echo '<strong><em>Unpack failed : ' . $result->get_error_message() . '</strong></em><br><br>';
          return;
      }
      
      echo "Installing the theme<br>";
      // Copy new version of plugin into place.
      if (!copy_dir($working_dir, $base . "wp-content/themes")) {
          //TODO: Uncomment? This DOES mean that the new files are available in the upgrade folder if it fails.
          $wp_filesystem->delete($working_dir, true);
          echo '<strong><em>Installation failed (theme already installed?)</strong></em><br><br>';
          return;
      }
      
      //Get a list of the directories in the working directory before we delete it, We need to know the new folder for the plugin
      $filelist = array_keys($wp_filesystem->dirlist($working_dir));
      
      // Remove working directory
      $wp_filesystem->delete($working_dir, true);
      
      // Once extracted, delete the package
      unlink($file);
      
      echo "Theme installed successfully.<br><br>";
      return;
  }
  
  function themedrive_get_theme()
  {
      $gettheme = get_option('td_themes');
      
      if (!empty($gettheme)) {
          return $gettheme;
      } else {
          return '';
      }
  }
  
  function themedrive_get_level()
  {
      $getlevel = get_option('td_level');
      
      if ($getlevel!='') {
          return 'level_' . $getlevel;
      } else {
          return 'level_10';
      }
  }
  
  function themedrive_determine_theme()
  {
      if (!isset($_GET['theme'])) {
          if (!current_user_can(themedrive_get_level())) {
              // not admin
              return false;
          } else {
              $theme = themedrive_get_theme();
              if ($theme == '') {
                  // no admin-only theme defined, short-circuit out
                  return false;
              }
          }
      }
      
      $all = $_GET + $_POST;
      if (isset($all['theme'])) {
          $theme = $all['theme'];
      }
      
      $theme_data = wp_get_theme($theme);
      
      if (!empty($theme_data)) {
          // Don't let people peek at unpublished themes
          if (isset($theme_data['Status']) && $theme_data['Status'] != 'publish') {
              return false;
          }
          return $theme_data;
      }
      
      // perhaps they are using the theme directory instead of title
      $themes = wp_get_themes();
      
      foreach ($themes as $theme_data) {
          // use Stylesheet as it's unique to the theme - Template could point to another theme's templates
          if ($theme_data['Stylesheet'] == $theme) {
              // Don't let people peek at unpublished themes
              if (isset($theme_data['Status']) && $theme_data['Status'] != 'publish') {
                  return false;
              }
              return $theme_data;
          }
      }
      
      return false;
  }
  
  function themedrive_get_template($template)
  {
      $theme = themedrive_determine_theme();
      if ($theme === false) {
          return $template;
      }
      
      return $theme['Template'];
  }
  
  function themedrive_get_stylesheet($stylesheet)
  {
      $theme = themedrive_determine_theme();
      if ($theme === false) {
          return $stylesheet;
      }
      
      return $theme['Stylesheet'];
  }
  
  function themedrive_switcher()
  {
      $themes = wp_get_themes();
      
      $default_theme = wp_get_theme();
      
      if (count($themes) > 1) {
          $theme_names = array_keys($themes);
          natcasesort($theme_names);
          
          
          $ts = '<select name="td_themes">' . "\n";
          $tp = '<div id="theme_preview">
<div class="theme_links"><strong>Instant Theme Preview</strong><br/><br/>Hover over the link, reload the page if needed.<br/><ul>';
          
          foreach ($theme_names as $theme_name) {
              // Skip unpublished themes.
              if (isset($themes[$theme_name]['Status']) && $themes[$theme_name]['Status'] != 'publish') {
                  continue;
              }
              
              if ((themedrive_get_theme() == $theme_name) || ((themedrive_get_theme() == '') && ($theme_name == $default_theme))) {
                  $ts .= '        <option value="' . esc_attr( $theme_name ) . '" selected="selected">' . $themes[$theme_name]['Name'] . '</option>' . "\n";
              } else {
                  $ts .= '        <option value="' . esc_attr( $theme_name ) . '">' . $themes[$theme_name]['Name'] . '</option>' . "\n";
              }
              $tp .= '<li><a href="' . trailingslashit(get_option('siteurl')) . '?theme=' . esc_url($theme_name) . '">' . $theme_name . '</a></li>';
          }
          $ts .= '    </select>' . "\n\n";
          $tp .= '</ul></div></div>';
      }
      //  echo $tp; 
      
      echo $ts;
      if (themedrive_is_enabled()) {
          echo '<strong>Theme Test Drive is Enabled.</strong><br />';
      } else {
          echo 'Theme Test Drive is Disabled.<br />';
      }
  }
  
 add_action('plugins_loaded','TTD_filters'); // because filters call current_user_can and other plugins need raw value from options

 function TTD_filters () {
// 	if ( !is_admin() ) { // not needed in admin side
 		add_filter('template', 'themedrive_get_template');
 		add_filter('stylesheet', 'themedrive_get_stylesheet');
 //	}
 }

  
  
  
  // Admin Panel
  function themedrive_add_pages()
  {
      add_theme_page( 'Theme Test Drive Options', 'Theme Test Drive', 'edit_theme_options', 'themedrive_options_page', 'themedrive_options_page' );
  }
  
  
  function themedrive_is_enabled()
  {
      return get_option('td_themes');
  }
  
  // Options Page
  function themedrive_options_page()
  {
      global $themedrive_localversion;
      global $wp_themedrive_plugin_url;

      $status = themedrive_getinfo();
      
      $theVersion = isset( $status[1] ) ? $status[1] : '';
      $theMessage = isset( $status[3] ) ? $status[3] : '';
      
      
      
      if ((version_compare(strval($theVersion), strval($themedrive_localversion), '>') == 1)) {
          $msg = 'Latest version available ' . ' <strong>' . $theVersion . '</strong><br />' . $theMessage;
          echo '<div id="message" class="updated fade"><p>' . $msg . '</p></div>';
      }
      
      if ( isset( $_POST['button'] ) && 'Enable Theme Drive' == $_POST['button']) {
          check_admin_referer('theme-drive');
          $themedrive = $_POST['td_themes'];
          update_option('td_themes', $themedrive);
          
          $access_level = (int)$_POST['access_level'];
          update_option('td_level', $access_level);
          $msg_status = "Theme Test Drive Enabled for administrator with " . $themedrive . ' theme.';
          
          
          
          // Show message
          echo '<div id="message" class="updated fade"><p>' . $msg_status . '</p></div>';
      } elseif ( isset( $_POST['button'] ) && 'Disable Theme Drive' == $_POST['button'] ) {
          check_admin_referer('theme-drive');
          // Delete the option from the DB if it's empty
          delete_option('td_themes');
          
          $msg_status = "Theme Test Drive has been disabled.";
          
          // Show message
          echo '<div id="message" class="updated fade"><p>' . $msg_status . '</p></div>';
      }
      
      
      
      
      $access_level = get_option('td_level');
    	//if (empty($access_level))
        //  $access_level = '10';
      
      $imgpath = $wp_themedrive_plugin_url . '/i';
?>  
  <div class="wrap" >
  <h2>Theme Test Drive</h2>
        
  <div id="poststuff" style="margin-top:10px;">
  
   <div id="sideblock" style="float:right;width:270px;margin-left:10px;"> 

		 <iframe width=270 height=800 frameborder="0" src="http://www.prelovac.com/plugin/news.php?id=5&utm_source=plugin&utm_medium=plugin&utm_campaign=Theme%2BTest%2BDrive"></iframe>

 	</div>
  
   <div id="mainblock" style="width:710px">
	<?php $action_url; ?>
    <div class="dbx-content">
	<?php if ( ! isset( $action_url) ) 
		$action_url = '';
	?>
       <form name="form_apu" method="post" action="<?php
      echo $action_url
?>">
       <?php
      wp_nonce_field('theme-drive');
      
      if (isset($_POST['theme_install'])) {
          echo '<h3>Theme installation</h3>';
          
          $install_theme = !isset($_POST['install_theme']) ? '' : $_POST['install_theme'];
          
          if ($install_theme != '') {
              themedrive_handle_theme($install_theme);
          } else
              echo "No theme URL specified.<br>";
          echo '<br><br>';
      }
?>

 
   <h3>Easy Theme Installation</h3>
       
       Enter the URL to the theme zip file and click Install theme.<br><br>
       <input style="border:1px solid #D1D1D1;width:400px;" name="install_theme" id="install_theme" value="" />
      <br>
      <input class="button" type="submit" name="theme_install" value="Install theme &raquo;" class="button-primary" />
  <br /><br />
   <h3>Usage</h3>
  <p>Select a theme to preview live on the site. Only administrator will be able to see the selected theme. </p>                          
<p>Additionally you may add "?theme=xxx" to your blog url, where xxx is the theme name you want to test.</p>
<?php
      themedrive_switcher();
?>
<p>You can specify the level of users to have access to the selected theme preview. By default it is set to 10 (admin only). Level 7 are editors, level 4 are authors and level 1 are contributors. The access level is ignored for accessing the site with ?theme=xxx paramaeter. </p>
<input style="border:1px solid #D1D1D1;width:100px;" name="access_level" id="access_level" value="<?php
      echo esc_attr( $access_level )
?>" /> Access level<br />
<p>
<strong>Disabling:</strong> If you wish to stop using Theme Test Drive, press <em>Disable</em> button.
Alternatively, disabling this plug-in should also do the trick.
</p>


<p class="submit">

<input type="submit" name="button" value="Disable Theme Drive" class="button-primary" />

<input type="submit" name="button" value="Enable Theme Drive" class="button-primary" />


</p>

</form>

</div>
</div>
</div>
<h4>a plugin by <a href="http://www.prelovac.com/vladimir/">Vladimir Prelovac</a></h4>
</div>


<?php
  }
  // themedrive_options_page
  
  // Add Options Page
  add_action('admin_menu', 'themedrive_add_pages');
  
  
  add_action('after_plugin_row', 'themedrive_check_plugin_version');
  
  function themedrive_getinfo()
  {
      $checkfile = "http://svn.wp-plugins.org/theme-test-drive/trunk/themedrive.chk";
      
      $status = array();
      return $status;
      $vcheck = wp_remote_fopen($checkfile);
      
      if ($vcheck) {
          $version = $themedrive_localversion;
          
          $status = explode('@', $vcheck);
          return $status;
      }
  }
  
  function themedrive_check_plugin_version($plugin)
  {
      global $plugindir, $themedrive_localversion;

      $theVersion = '';
      $theMessage = '';
      
      if (strpos($plugin, 'themedrive.php') !== false) {
          $status = themedrive_getinfo();

          if ( isset( $status[1] ) )
			$theVersion = $status[1];
          if ( isset( $status[3] ) )
          	$theMessage = $status[3];
          
          if ((version_compare(strval($theVersion), strval($themedrive_localversion), '>') == 1)) {
              $msg = 'Latest version available ' . ' <strong>' . $theVersion . '</strong><br />' . $theMessage;
              echo '<td colspan="5" class="plugin-update" style="line-height:1.2em;">' . $msg . '</td>';
          } else {
              return;
          }
      }
  }
  
  function themdrive_js()
  {
      echo '<script type="text/javascript">var bubbleImagePath="' . site_url() . '/wp-content/plugins/theme-test-drive/bg.png"</script>';
      echo "\n";
      echo '<script src="' . site_url() . '/wp-content/plugins/theme-test-drive/previewbubble.js" type="text/javascript"></script>';
      echo "\n";
  }
  
  
  //add_action("admin_head","themdrive_js");
  
  //$tp.= '<p><img src="http://thumbnailspro.com/thumb.php?url='.trailingslashit(get_option('siteurl')).'?theme='.htmlspecialchars($theme_name).'&s=400" /><br /></p>';
  //<p><img src="http://images.websnapr.com/?size=s&key=42d1W6HhpB0B&url='.trailingslashit(get_option('siteurl')).'?theme='.themedrive_get_theme().'" /><br /></p>
?>