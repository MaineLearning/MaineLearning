<?php

/*  Copyright 2010  TODD HALFPENNY  (email : todd@gingerbreaddesign.co.uk)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/*
Plugin Name: Widgets on Pages
Plugin URI: http://gingerbreaddesign.co.uk/wordpress/plugins/widgets-on-pages.php
Description: The easy way to Add Widgets or Sidebars to Posts and Pages by shortcodes or template tags.
Author: Todd Halfpenny
Version: 0.0.12
Author URI: http://gingerbreaddesign.co.uk/todd
*/

/* ===============================
  A D M I N   M E N U / P A G E
================================*/


add_action('admin_menu', 'wop_menu');

function wop_menu() {
	global $wop_plugin_hook;
 	$wop_plugin_hook = add_options_page('Widgets on Pages options', 'Widgets on Pages', 'manage_options', 'wop_options', 'wop_plugin_options');
	add_action( 'admin_init', 'register_wop_settings' );

}

add_filter('plugin_action_links', 'wop_plugin_action_links', 10, 2);

function wop_plugin_action_links($links, $file) {
    static $this_plugin;

    if (!$this_plugin) {
        $this_plugin = plugin_basename(__FILE__);
    }

    if ($file == $this_plugin) {
        $settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=wop_options">Settings</a>';
        array_unshift($links, $settings_link);
    }

    return $links;
}



function register_wop_settings() { // whitelist options
  register_setting( 'wop_options', 'wop_options_field' );
}


/*--------------------------------
  wop_options
------------------------------- */
function wop_plugin_options() {
?>
  <div class="wrap">
  <div id="icon-tools" class="icon32"></div>
  <h2>Widgets on Pages: Options</h2>
  <form method="post" action="options.php">
    <?php
    wp_nonce_field('update-options'); 
    settings_fields( 'wop_options' ); 
    $options = get_option('wop_options_field');
    $enable_css = $options["enable_css"];
    $num_add_sidebars = $options["num_of_wop_sidebars"];
    ?>
    
    <script language="JavaScript">
    function validate(evt) {
      var theEvent = evt || window.event;
      var key = theEvent.keyCode || theEvent.which;
      if ((key == 8) || (key == 9) || (key == 13)) {
      }
      else {
        key = String.fromCharCode( key );
        var regex = /[0-9]|\./;
        if( !regex.test(key) ) {
          theEvent.returnValue = false;
          theEvent.preventDefault();
        }
      }
    }
    </script>

    <table class="form-table">
    
      <tr valign="top">
        <th scope="row">Enable styling (remove bullets etc)</th>
        <td>
				<?php echo '<input name="wop_options_field[enable_css]" type="checkbox" value="1" class="code" ' . checked( 1, $enable_css, false ) . ' />';
				?>
				</td>
      </tr>
    
	
		<tr valign="top">
        <th scope="row">Number of additional sidebars</th>
        <td><input type='text'  name="wop_options_field[num_of_wop_sidebars]" size='3' value="<?php echo $num_add_sidebars;?>"  onkeypress='validate(event)' /></td>
      </tr>
    
    <tr><td></td><td>
      <p class="submit">
      <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
      </p>
    </td></tr>
    <tr><td><h3>Optional Sidebar Names</h3></td><td></td></tr>
    <?php
    for ($sidebar = 1; $sidebar <= ($num_add_sidebars + 1); $sidebar++) {
        $option_id = 'wop_name_' . $sidebar;
        ?>
        <tr valign="top">
          <th scope="row">WoP sidebar <?php echo $sidebar;?> name:</th>
          <td><input type='text'  name="wop_options_field[<?php echo $option_id;?>]" size='35' value="<?php echo $options[$option_id];?>"  /></td>
        </tr>
        <?php
    }
    ?>
    <tr><td></td><td>
      <p class="submit">
      <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
      </p>
    </td></tr>
    <tr><td></td><td><input type="hidden" name="action" value="update" />    
	</td></tr>
    <tr><td colspan='2'><h3>Rate this plugin</h3><p><a href="http://wordpress.org/support/view/plugin-reviews/widgets-on-pages?rate=5#postform" title="Rate me">If you like me, please rate me</a>... or maybe even <a href="http://gingerbreaddesign.co.uk/wordpress/" title="Show you love">donate to the author</a>... <p><p>or perhaps just spread the good word <a href="https://twitter.com/share" class="twitter-share-button" data-url="http://wordpress.org/extend/plugins/widgets-on-pages/" data-text="Using the Widgets on Pages WordPress plugin and lovin' it" data-via="toddhalfpenny" data-count="none">Tweet</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script></p></td></tr>
  </form>
  </div>
<?php
}



/* ===============================
  I N S T A L L / U P G R A D E 
================================*/

function wop_install() {
	// nothing to do this time out
}


/* ===============================
  C O N T E X T U A L    H E L P
================================*/
function wop_plugin_help($text, $screen_id, $screen) {
	global $wop_plugin_hook;
	if ($screen_id == $wop_plugin_hook) {

	$text = "<h5>Need help with the Widgets on Pages plugin?</h5>";
	$text .= "<p>Check out the documentation and support forums for help with this plugin.</p>";
	$text .= "<a href=\"http://wordpress.org/extend/plugins/widgets-on-pages/installation/\">Documentation</a><br /><a href=\"http://wordpress.org/tags/widgets-on-pages?forum_id=10\">Support forums</a>";
	}
	return $text;
}
	 
add_filter('contextual_help', 'wop_plugin_help', 10, 3);


/* ===============================
  C O R E    C O D E 
================================*/

// Main Function Code, to be included on themes
function widgets_on_template($id="") {
	if (!empty($id)) {
		$sidebar_name =  $id;
	}
	else {
		$sidebar_name = '1';
	}
  $arr = array(id => $sidebar_name );
  echo widgets_on_page($arr);
}


function widgets_on_page($atts){
  reg_wop_sidebar();
  extract(shortcode_atts( array('id' => '1'), $atts));
  if (is_numeric($id)) :
    $sidebar_name = 'Widgets on Pages ' . $id;
  else :
    $sidebar_name = $id;
  endif;
  $str =  "<div id='" . str_replace(" ", "_", $sidebar_name) . "' class='widgets_on_page'>
    <ul>";
  ob_start();
  if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar($sidebar_name) ) :
  endif;
  $myStr = ob_get_contents();
  ob_end_clean();
  $str .= $myStr;
  $str .=  "</ul>
  </div><!-- widgets_on_page -->";
  return $str;
}



function reg_wop_sidebar() {
  $options = get_option('wop_options_field');
  $num_sidebars = $options["num_of_wop_sidebars"] + 1;
  // register the main sidebar
  if ( function_exists('register_sidebar') )
    if ($options['wop_name_1'] != "") :
      $name = $options['wop_name_1'];
      $sidebar_id = ' id="' .$name . '"';  
    else :
      $name = 'Widgets on Pages 1';
      $sidebar_id = ""; 
    endif;
    $id = 'wop-1';
    //$sidebar_id = 'wop-1'; 
    $desc = '#1 Widgets on Pages sidebar.
            Use shortcode
            "[widgets_on_pages' . $sidebar_id .']"';
register_sidebar(array(
  'name' => __( $name, 'wop' ),
  'id' => $id ,
  'description' => __( $desc, 'wop' ),
  'before_widget' => '<li id="%1$s" class="widget %2$s">',
  'after_widget' => '</li>',
  'before_title' => '<h2 class="widgettitle">',
  'after_title' => '</h2>',
  ));
  
  // register any other additional sidebars
  if ($num_sidebars > 1)  :
    for ( $sidebar = 2; $sidebar <= $num_sidebars; $sidebar++){
      if ( function_exists('register_sidebar') )
          $option_id = 'wop_name_' . $sidebar;
          if ($options[$option_id] != "") :
            $name = $options[$option_id];
            $sidebar_id = ' id="' . $name . '"'; 
          else :
            $name = 'Widgets on Pages ' . $sidebar;
            $sidebar_id = ' id=' . $sidebar; 
          endif;
          //$sidebar_id = 'wop-' . $sidebar; 
          $id = 'wop-' . $sidebar; 
          $desc = '#' . $sidebar . 'Widgets on Pages sidebar.
              Use shortcode
              "[widgets_on_pages' . $sidebar_id .']"';
  register_sidebar(array(
              'name' => __( $name, 'wop' ),
              'id' => $id ,
              'description' => __( $desc, 'wop' ),
              'before_widget' => '<li id="%1$s" class="widget %2$s">',
              'after_widget' => '</li>',
              'before_title' => '<h2 class="widgettitle">',
              'after_title' => '</h2>',
      ));
    }
  endif;
}


register_activation_hook(__FILE__,'wop_install');

add_action('admin_init', 'reg_wop_sidebar'); 
add_shortcode('widgets_on_pages', 'widgets_on_page');


/* ===============================
  A D D    C S S    ? 
================================*/
function add_wop_css_to_head()
{
	echo "<link rel='stylesheet' id='wop-css'  href='".get_settings('siteurl')."/wp-content/plugins/widgets-on-pages/wop.css' type='text/css' media='all' />";
}

$options = get_option('wop_options_field');
$enable_css = $options["enable_css"];
if ($enable_css) {
  add_action('wp_head', 'add_wop_css_to_head');
}


?>
