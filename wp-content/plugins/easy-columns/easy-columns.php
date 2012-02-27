<?php
/*
Plugin Name: Easy Columns
Plugin URI: http://www.affiliatetechhelp.com/wordpress/easy-columns
Version: v2.0
Author: <a href="http://www.affiliatetechhelp.com">Pat Friedl</a>
Description: Easy Columns provides the shortcodes to create a grid system or magazine style columns for laying out your pages just the way you need them.  Using shortcodes for 1/4, 1/2, 1/3, 2/3, 3/4, 1/5, 2/5, and 3/5 columns, you can insert <strong>at least thirty</strong> unique variations of columns on any page or post. Quickly add columns to your pages from the editor with an easy to use "pick n' click" interface! For usage and more information, visit <a href="http://www.affiliatetechhelp.com" target="_blank">affiliatetechhelp.com</a>.

Copyright 2010  AffiliaTetechHelp.com  (email: support[at]affiliatetechhelp[dot]com)

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
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/

if(!class_exists("EasyColumns")){

	class EasyColumns {

		var $plugin_url;
		var $EasyColumns_DB_option = 'easycol_options';
		var $EasyColumns_options;

		var $use_custom;
		var $quarter_width;
		var $quarter_width_type;
		var $quarter_margin;
		var $quarter_margin_type;
		var $onehalf_width;
		var $onehalf_width_type;
		var $onehalf_margin;
		var $onehalf_margin_type;
		var $threequarter_width;
		var $threequarter_width_type;
		var $threequarter_margin;
		var $threequarter_margin_type;
		var $onethird_width;
		var $onethird_width_type;
		var $onethird_margin;
		var $onethird_margin_type;
		var $twothird_width;
		var $twothird_width_type;
		var $twothird_margin;
		var $twothird_margin_type;
		var $fifth_width;
		var $fifth_width_type;
		var $fifth_margin;
		var $fifth_margin_type;
		var $twofifth_width;
		var $twofifth_width_type;
		var $twofifth_margin;
		var $twofifth_margin_type;
		var $threefifth_width;
		var $threefifth_width_type;
		var $threefifth_margin;
		var $threefifth_margin_type;

		function EasyColumns() { //constructor

			// define the plugin URL so we can add the CSS
			$this->plugin_url  = $this->plugin_url = defined('WP_PLUGIN_URL') ? WP_PLUGIN_URL . '/' . dirname(plugin_basename(__FILE__)) : trailingslashit(get_bloginfo('wpurl')) . PLUGINDIR . '/' . dirname(plugin_basename(__FILE__));

			// define column shortcodes
			add_shortcode('wpcol_1half', array(&$this, 'wpcol_one_half'));
			add_shortcode('wpcol_1half_end', array(&$this, 'wpcol_one_half_end'));
			add_shortcode('wpcol_1third', array(&$this, 'wpcol_one_third'));
			add_shortcode('wpcol_1third_end', array(&$this, 'wpcol_one_third_end'));
			add_shortcode('wpcol_2third', array(&$this, 'wpcol_two_third'));
			add_shortcode('wpcol_2third_end', array(&$this, 'wpcol_two_third_end'));
			add_shortcode('wpcol_1quarter', array(&$this, 'wpcol_one_quarter'));
			add_shortcode('wpcol_1quarter_end', array(&$this, 'wpcol_one_quarter_end'));
			add_shortcode('wpcol_3quarter', array(&$this, 'wpcol_three_quarter'));
			add_shortcode('wpcol_3quarter_end', array(&$this, 'wpcol_three_quarter_end'));
			add_shortcode('wpcol_1fifth', array(&$this, 'wpcol_one_fifth'));
			add_shortcode('wpcol_1fifth_end', array(&$this, 'wpcol_one_fifth_end'));
			add_shortcode('wpcol_2fifth', array(&$this, 'wpcol_two_fifth'));
			add_shortcode('wpcol_2fifth_end', array(&$this, 'wpcol_two_fifth_end'));
			add_shortcode('wpcol_3fifth', array(&$this, 'wpcol_three_fifth'));
			add_shortcode('wpcol_3fifth_end', array(&$this, 'wpcol_three_fifth_end'));
			add_shortcode('wpcol_4fifth', array(&$this, 'wpcol_four_fifth'));
			add_shortcode('wpcol_4fifth_end', array(&$this, 'wpcol_four_fifth_end'));
			add_shortcode('wpdiv', array(&$this, 'wpcol_div'));
			add_shortcode('wpcol_divider', array(&$this, 'wpcol_add_divider'));
			add_shortcode('wpcol_end_right', array(&$this, 'wpcol_end_column_right'));
			add_shortcode('wpcol_end_left', array(&$this, 'wpcol_end_column_left'));
			add_shortcode('wpcol_end_both', array(&$this, 'wpcol_end_column_both'));
			// add to tinyMCE
			add_action('init', array(&$this, 'add_tinymce'));
			// add admin menu
			add_action('admin_menu', array(&$this, 'admin_menu'));

			// get the options
			$options = $this->get_options();

			// assign variables for column styles
			$this->use_custom = $options['use_custom'];
			$this->quarter_width = $options['quarter_width'];
			$this->quarter_width_type = $options['quarter_width_type'];
			$this->quarter_margin = $options['quarter_margin'];
			$this->quarter_margin_type = $options['quarter_margin_type'];
			$this->onehalf_width = $options['onehalf_width'];
			$this->onehalf_width_type = $options['onehalf_width_type'];
			$this->onehalf_margin = $options['onehalf_margin'];
			$this->onehalf_margin_type = $options['onehalf_margin_type'];
			$this->threequarter_width = $options['threequarter_width'];
			$this->threequarter_width_type = $options['threequarter_width_type'];
			$this->threequarter_margin = $options['threequarter_margin'];
			$this->threequarter_margin_type = $options['threequarter_margin_type'];
			$this->onethird_width = $options['onethird_width'];
			$this->onethird_width_type = $options['onethird_width_type'];
			$this->onethird_margin = $options['onethird_margin'];
			$this->onethird_margin_type = $options['onethird_margin_type'];
			$this->twothird_width = $options['twothird_width'];
			$this->twothird_width_type = $options['twothird_width_type'];
			$this->twothird_margin = $options['twothird_margin'];
			$this->twothird_margin_type = $options['twothird_margin_type'];
			$this->fifth_width = $options['onefifth_width'];
			$this->fifth_width_type = $options['onefifth_width_type'];
			$this->fifth_margin = $options['onefifth_margin'];
			$this->fifth_margin_type = $options['onefifth_margin_type'];
			$this->twofifth_width = $options['twofifth_width'];
			$this->twofifth_width_type = $options['twofifth_width_type'];
			$this->twofifth_margin = $options['twofifth_margin'];
			$this->twofifth_margin_type = $options['twofifth_margin_type'];
			$this->threefifth_width = $options['threefifth_width'];
			$this->threefifth_width_type = $options['threefifth_width_type'];
			$this->threefifth_margin = $options['threefifth_margin'];
			$this->threefifth_margin_type = $options['threefifth_margin_type'];
			$this->fourfifth_width = $options['fourfifth_width'];
			$this->fourfifth_width_type = $options['fourfifth_width_type'];
			$this->fourfifth_margin = $options['fourfifth_margin'];
			$this->fourfifth_margin_type = $options['fourfifth_margin_type'];

		} // end function EasyColumns

		function wpcol_one_half($atts, $content = null) {
			return '<div' . $this->wpcol_div_atts($atts,'wpcol-one-half') . '>'.$this->wpcol_strip_autop($content).'</div>';
		}

		function wpcol_one_half_end($atts, $content = null) {
			return '<div' . $this->wpcol_div_atts($atts,'wpcol-one-half wpcol-last') . '>'.$this->wpcol_strip_autop($content).'</div>'.$this->wpcol_add_divider();
		}

		function wpcol_one_third($atts, $content = null) {
			return '<div' . $this->wpcol_div_atts($atts,'wpcol-one-third') . '>'.$this->wpcol_strip_autop($content).'</div>';
		}

		function wpcol_one_third_end($atts, $content = null) {
		  return '<div' . $this->wpcol_div_atts($atts,'wpcol-one-third wpcol-last') . '>'.$this->wpcol_strip_autop($content).'</div>'.$this->wpcol_add_divider();
		}

		function wpcol_two_third($atts, $content = null) {
		  return '<div' . $this->wpcol_div_atts($atts,'wpcol-two-third') . '>'.$this->wpcol_strip_autop($content).'</div>';
		}

		function wpcol_two_third_end($atts, $content = null) {
			return '<div' . $this->wpcol_div_atts($atts,'wpcol-two-third wpcol-last') . '>'.$this->wpcol_strip_autop($content).'</div>'.$this->wpcol_add_divider();
		}

		function wpcol_one_quarter($atts, $content = null) {
			return '<div' . $this->wpcol_div_atts($atts,'wpcol-one-quarter') . '>'.$this->wpcol_strip_autop($content).'</div>';
		}

		function wpcol_one_quarter_end($atts, $content = null) {
			return '<div' . $this->wpcol_div_atts($atts,'wpcol-one-quarter wpcol-last') . '>'.$this->wpcol_strip_autop($content).'</div>'.$this->wpcol_add_divider();
		}

		function wpcol_three_quarter($atts, $content = null) {
			return '<div' . $this->wpcol_div_atts($atts,'wpcol-three-quarter') . '>'.$this->wpcol_strip_autop($content).'</div>';
		}

		function wpcol_three_quarter_end($atts, $content = null) {
			return '<div' . $this->wpcol_div_atts($atts,'wpcol-three-quarter wpcol-last') . '>'.$this->wpcol_strip_autop($content).'</div>'.$this->wpcol_add_divider();
		}

		function wpcol_one_fifth($atts, $content = null) {
			return '<div' . $this->wpcol_div_atts($atts,'wpcol-one-fifth') . '>'.$this->wpcol_strip_autop($content).'</div>';
		}

		function wpcol_one_fifth_end($atts, $content = null) {
			return '<div' . $this->wpcol_div_atts($atts,'wpcol-one-fifth wpcol-last') . '>'.$this->wpcol_strip_autop($content).'</div>'.$this->wpcol_add_divider();
		}

		function wpcol_two_fifth($atts, $content = null) {
			return '<div' . $this->wpcol_div_atts($atts,'wpcol-two-fifth') . '>'.$this->wpcol_strip_autop($content).'</div>';
		}

		function wpcol_two_fifth_end($atts, $content = null) {
			return '<div' . $this->wpcol_div_atts($atts,'wpcol-two-fifth wpcol-last') . '>'.$this->wpcol_strip_autop($content).'</div>'.$this->wpcol_add_divider();
		}

		function wpcol_three_fifth($atts, $content = null) {
			return '<div' . $this->wpcol_div_atts($atts,'wpcol-three-fifth') . '>'.$this->wpcol_strip_autop($content).'</div>';
		}

		function wpcol_three_fifth_end($atts, $content = null) {
			return '<div' . $this->wpcol_div_atts($atts,'wpcol-three-fifth wpcol-last') . '>'.$this->wpcol_strip_autop($content).'</div>'.$this->wpcol_add_divider();
		}

		function wpcol_four_fifth($atts, $content = null) {
			return '<div' . $this->wpcol_div_atts($atts,'wpcol-four-fifth') . '>'.$this->wpcol_strip_autop($content).'</div>';
		}

		function wpcol_four_fifth_end($atts, $content = null) {
			return '<div' . $this->wpcol_div_atts($atts,'wpcol-four-fifth wpcol-last') . '>'.$this->wpcol_strip_autop($content).'</div>'.$this->wpcol_add_divider();
		}

		function wpcol_div($atts, $content = null) {
			return '<div' . $this->wpcol_div_atts($atts,'') . '>' . $this->wpcol_strip_autop($content) . '</div>';
		}

		function wpcol_add_divider(){
			return '<div class="wpcol-divider"></div>';
		}

		function wpcol_end_column_left($atts, $content = null) {
		   return '<div class="wpcol-left"></div>';
		}

		function wpcol_end_column_right($atts, $content = null) {
		   return '<div  class="wpcol-right"></div>';
		}

		function wpcol_end_column_both($atts, $content = null) {
		   return '<div class="wpcol-both"></div>';
		}

		function wpcol_div_atts($atts,$col_type) {
			extract(shortcode_atts(array('id' => '','class' => '','style' => ''),$atts));

			$att_str = ' class="';
			if($col_type != ''){
				$att_str .= $col_type;
			}
			if($col_type != '' && $class != ''){
				$att_str .= ' ';
			}
			if($class != ''){
				$att_str .= $class;
			}
			$att_str .= '"';
			if($id != ''){
				$att_str .= ' id="' . $id . '"';
			}
			if($style != ''){
				$att_str .= ' style="' . $style . '"';
			}
			return $att_str;
		}

		function wpcol_strip_autop($content){
			$content = do_shortcode(shortcode_unautop( $content ));
			$content = preg_replace('#^<\/p>|^<br \/>|<p>$#', '', $content);
			return $content;
		}

		function wpcol_add_css(){
		?>
			<!-- Begin Easy Columns 1.0 by Pat Friedl http://www.affiliatetechhelp.com -->
			<link rel="stylesheet" href="<?php echo $this->plugin_url; ?>/css/wp-ez-columns.css?<?php echo time();?>" type="text/css" media="screen, projection" />
			<!-- End Easy Columns 1.0 -->
			<?php
			if($this->use_custom)
			{
				if($this->quarter_width != '' || $this->quarter_margin != '' ||
				   $this->onehalf_width != '' || $this->onehalf_margin != '' ||
				   $this->threequarter_width != '' || $this->threequarter_margin != '' ||
				   $this->onethird_width != '' || $this->onethird_margin != '' ||
				   $this->twothird_width != '' || $this->twothird_margin != '' ||
				   $this->twothird_width != '' || $this->twothird_margin != '' ||
					 $this->fifth_width != '' || $this->fifth_margin != '' ||
					 $this->twofifth_width != '' || $this->twofifth_margin != '' ||
					 $this->threefifth_width != '' || $this->threefifth_margin != '' ||
					 $this->fourfifth_width != '' || $this->fourfifth_margin != '')
				{
					?>
					<!-- Begin Easy Columns 1.2.1 Custom CSS -->
					<style type="text/css" media="screen, projection">
					<?php
					if($this->quarter_width != '' || $this->quarter_margin != '')
					{
						echo '.wpcol-one-quarter {';
						if($this->quarter_width != '') { echo 'width:'.$this->quarter_width.$this->quarter_width_type.'; '; }
						if($this->quarter_margin != '') { echo 'margin-right:'.$this->quarter_margin.$this->quarter_margin_type.';'; }
						echo '} ';
					}
					if($this->onehalf_width != '' || $this->onehalf_margin != '')
					{
						echo '.wpcol-one-half {';
						if($this->onehalf_width != '') { echo 'width:'.$this->onehalf_width.$this->onehalf_width_type.'; '; }
						if($this->onehalf_margin != '') { echo 'margin-right:'.$this->onehalf_margin.$this->onehalf_margin_type.';'; }
						echo '} ';
					}
					if($this->threequarter_width != '' || $this->threequarter_margin != '')
					{
						echo '.wpcol-three-quarter {';
						if($this->threequarter_width != '') { echo 'width:'.$this->threequarter_width.$this->threequarter_width_type.'; '; }
						if($this->threequarter_margin != '') { echo 'margin-right:'.$this->threequarter_margin.$this->threequarter_margin_type.';'; }
						echo '} ';
					}
					if($this->onethird_width != '' || $this->onethird_margin != '')
					{
						echo '.wpcol-one-third {';
						if($this->onethird_width != '') { echo 'width:'.$this->onethird_width.$this->onethird_width_type.'; '; }
						if($this->onethird_margin != '') { echo 'margin-right:'.$this->onethird_margin.$this->onethird_margin_type.';'; }
						echo '} ';
					}
					if($this->twothird_width != '' || $this->twothird_margin != '')
					{
						echo '.wpcol-two-third {';
						if($this->twothird_width != '') { echo 'width:'.$this->twothird_width.$this->twothird_width_type.'; '; }
						if($this->twothird_margin != '') { echo 'margin-right:'.$this->twothird_margin.$this->twothird_margin_type.';'; }
						echo '}';
					}
					if($this->fifth_width != '' || $this->fifth_margin != '')
					{
						echo '.wpcol-one-fifth {';
						if($this->fifth_width != '') { echo 'width:'.$this->fifth_width.$this->fifth_width_type.'; '; }
						if($this->fifth_margin != '') { echo 'margin-right:'.$this->fifth_margin.$this->fifth_margin_type.';'; }
						echo '}';
					}
					if($this->twofifth_width != '' || $this->twofifth_margin != '')
					{
						echo '.wpcol-two-fifth {';
						if($this->twofifth_width != '') { echo 'width:'.$this->twofifth_width.$this->twofifth_width_type.'; '; }
						if($this->twofifth_margin != '') { echo 'margin-right:'.$this->twofifth_margin.$this->twofifth_margin_type.';'; }
						echo '}';
					}
					if($this->threefifth_width != '' || $this->threefifth_margin != '')
					{
						echo '.wpcol-three-fifth {';
						if($this->threefifth_width != '') { echo 'width:'.$this->threefifth_width.$this->threefifth_width_type.'; '; }
						if($this->threefifth_margin != '') { echo 'margin-right:'.$this->threefifth_margin.$this->threefifth_margin_type.';'; }
						echo '}';
					}
					if($this->fourfifth_width != '' || $this->fourfifth_margin != '')
					{
						echo '.wpcol-four-fifth {';
						if($this->fourfifth_width != '') { echo 'width:'.$this->fourfifth_width.$this->fourfifth_width_type.'; '; }
						if($this->fourfifth_margin != '') { echo 'margin-right:'.$this->fourfifth_margin.$this->fourfifth_margin_type.';'; }
						echo '}';
					}
					?>
					echo "\n";
					</style>
					<!-- End Easy Columns 1.2.1 Custom CSS -->
					<?php
				}
			} // end if($this->use_custom)
		}// end wpcol_add_css

		// begin functions for adding plugin to tinyMCE
		function add_tinymce() {
			if(!current_user_can('edit_posts') && ! current_user_can('edit_pages')) {
				return;
			}
			if(get_user_option('rich_editing') == 'true') {
				add_filter('mce_external_plugins', array(&$this, 'add_tinymce_plugin'));
				add_filter('mce_buttons', array(&$this, 'add_tinymce_button'));
			}
		}
		function add_tinymce_plugin($plugin_array) {
			$plugin_array['ezColumns'] = $this->plugin_url . '/tinymce/editor_plugin.js';
			return $plugin_array;
		}
		function add_tinymce_button($buttons) {
			array_push($buttons, "separator", 'ezColumns');
			return $buttons;
		}
		// end functions for adding plugin to tinyMCE

		/*
		get plugin options, set plugin options
		*/
		function get_options()
		{
			// default values
			$options = array(
				'use_custom' => false,
				'quarter_width' => '',
				'quarter_width_type' => '%',
				'quarter_margin' => '',
				'quarter_margin_type' => '%',
				'onehalf_width' => '',
				'onehalf_width_type' => '%',
				'onehalf_margin' =>  '',
				'onehalf_type' => '%',
				'threequarter_width' => '',
				'threequarter_width_type' => '%',
				'threequarter_margin' =>  '',
				'threequarter_type' => '%',
				'onethird_width' => '',
				'onethird_width_type' => '%',
				'onethird_margin' =>  '',
				'onethird_margin_type' => '%',
				'twothird_width' => '',
				'twothird_width_type' => '%',
				'twothird_margin' =>  '',
				'twothird_margin_type' => '%',
				'onefifth_width' => '',
				'onefifth_width_type' => '%',
				'onefifth_margin' => '',
				'onefifth_margin_type' => '%',
				'twofifth_width' => '',
				'twofifth_width_type' => '%',
				'twofifth_margin' => '',
				'twofifth_margin_type' => '%',
				'threefifth_width' => '',
				'threefifth_width_type' => '%',
				'threefifth_margin' => '',
				'threefifth_margin_type' => '%',
				'fourfifth_width' => '',
				'fourfifth_width_type' => '%',
				'fourfifth_margin' => '',
				'fourfifth_margin_type' => '%'
			);

			// get saved options
			$saved = get_option($this->EasyColumns_DB_option);

			// assign options
			if(!empty($saved))
			{
				foreach($saved as $key => $option)
				{
					$options[$key] = $option;
				}
			}

			//update options if necessary
			if($saved != $options)
			{
				update_option($this->EasyColumns_DB_option,$options);
			}

			// return the options
			return $options;
		} // end get_options

		/*
		update options from the admin page
		*/
		function handle_options()
		{
			$options = $this->get_options();
			if (isset($_POST['submitted'])) {

				//check security
				check_admin_referer('easycol-nonce');

				$options = array();
				if($_POST['use_custom'] != ''){
					$options['use_custom'] = true;
				} else {
					$options['use_custom'] = false;
				}
				$options['quarter_width'] = trim($_POST['quarter_width']);
				$options['quarter_width_type'] = $_POST['quarter_width_type'];
				$options['quarter_margin'] = trim($_POST['quarter_margin']);
				$options['quarter_margin_type'] = $_POST['quarter_margin_type'];
				$options['onehalf_width'] = trim($_POST['onehalf_width']);
				$options['onehalf_width_type'] = $_POST['onehalf_width_type'];
				$options['onehalf_margin'] = trim($_POST['onehalf_margin']);
				$options['onehalf_margin_type'] = $_POST['onehalf_margin_type'];
				$options['threequarter_width'] = trim($_POST['threequarter_width']);
				$options['threequarter_width_type'] = $_POST['threequarter_width_type'];
				$options['threequarter_margin'] = trim($_POST['threequarter_margin']);
				$options['threequarter_margin_type'] = $_POST['threequarter_margin_type'];
				$options['onethird_width'] = trim($_POST['onethird_width']);
				$options['onethird_width_type'] = $_POST['onethird_width_type'];
				$options['onethird_margin'] = trim($_POST['onethird_margin']);
				$options['onethird_margin_type'] = $_POST['onethird_margin_type'];
				$options['twothird_width'] = trim($_POST['twothird_width']);
				$options['twothird_width_type'] = $_POST['twothird_width_type'];
				$options['twothird_margin'] = trim($_POST['twothird_margin']);
				$options['twothird_margin_type'] = $_POST['twothird_margin_type'];
				$options['onefifth_width'] = trim($_POST['onefifth_width']);
				$options['onefifth_width_type'] = $_POST['onefifth_width_type'];
				$options['onefifth_margin'] = trim($_POST['onefifth_margin']);
				$options['onefifth_margin_type'] = $_POST['onefifth_margin_type'];
				$options['twofifth_width'] = trim($_POST['twofifth_width']);
				$options['twofifth_width_type'] = $_POST['twofifth_width_type'];
				$options['twofifth_margin'] = trim($_POST['twofifth_margin']);
				$options['twofifth_margin_type'] = $_POST['twofifth_margin_type'];
				$options['threefifth_width'] = trim($_POST['threefifth_width']);
				$options['threefifth_width_type'] = $_POST['threefifth_width_type'];
				$options['threefifth_margin'] = trim($_POST['threefifth_margin']);
				$options['threefifth_margin_type'] = $_POST['threefifth_margin_type'];
				$options['fourfifth_width'] = trim($_POST['fourfifth_width']);
				$options['fourfifth_width_type'] = $_POST['fourfifth_width_type'];
				$options['fourfifth_margin'] = trim($_POST['fourfifth_margin']);
				$options['fourfifth_margin_type'] = $_POST['fourfifth_margin_type'];
				update_option($this->EasyColumns_DB_option, $options);

				echo '<div class="updated fade"><p>Plugin settings saved.</p></div>';
			}

			// URL for form submit, equals our current page
			$action_url = $_SERVER['REQUEST_URI'];

			// assign variables for the options page
			$use_custom = $options['use_custom'];
			$quarter_width = $options['quarter_width'];
			$quarter_width_type = $options['quarter_width_type'];
			$quarter_margin = $options['quarter_margin'];
			$quarter_margin_type = $options['quarter_margin_type'];
			$onehalf_width = $options['onehalf_width'];
			$onehalf_width_type = $options['onehalf_width_type'];
			$onehalf_margin = $options['onehalf_margin'];
			$onehalf_margin_type = $options['onehalf_margin_type'];
			$threequarter_width = $options['threequarter_width'];
			$threequarter_width_type = $options['threequarter_width_type'];
			$threequarter_margin = $options['threequarter_margin'];
			$threequarter_margin_type = $options['threequarter_margin_type'];
			$onethird_width = $options['onethird_width'];
			$onethird_width_type = $options['onethird_width_type'];
			$onethird_margin = $options['onethird_margin'];
			$onethird_margin_type = $options['onethird_margin_type'];
			$twothird_width = $options['twothird_width'];
			$twothird_width_type = $options['twothird_width_type'];
			$twothird_margin = $options['twothird_margin'];
			$twothird_margin_type = $options['twothird_margin_type'];
			$onefifth_width = $options['onefifth_width'];
			$onefifth_width_type = $options['onefifth_width_type'];
			$onefifth_margin = $options['onefifth_margin'];
			$onefifth_margin_type = $options['onefifth_margin_type'];
			$twofifth_width = $options['twofifth_width'];
			$twofifth_width_type = $options['twofifth_width_type'];
			$twofifth_margin = $options['twofifth_margin'];
			$twofifth_margin_type = $options['twofifth_margin_type'];
			$threefifth_width = $options['threefifth_width'];
			$threefifth_width_type = $options['threefifth_width_type'];
			$threefifth_margin = $options['threefifth_margin'];
			$threefifth_margin_type = $options['threefifth_margin_type'];
			$fourfifth_width = $options['fourfifth_width'];
			$fourfifth_width_type = $options['fourfifth_width_type'];
			$fourfifth_margin = $options['fourfifth_margin'];
			$fourfifth_margin_type = $options['fourfifth_margin_type'];

			$plugin_url = $this->plugin_url;

			// include the options page
			include('easy-columns-options.php');
		} // end handle_options

		/*
		add option page for Affiliate Cookie Jar
		*/
		function admin_menu()
		{
			add_options_page('Easy Columns Options', 'Easy Columns', 8, basename(__FILE__), array(&$this, 'handle_options'));
		} // end admin_menu

		/*
		install and initialize the plugin
		*/
		function install()
		{
			// set default options
			$EasyColumns_options = $this->get_options();
		} // end install

		/*
		uninstall the plugin - removes options
		*/
		function uninstall() {
			delete_option($this->EasyColumns_DB_option);
		} // end uninstall


	} // end class EasyColumns

} // end if class exists

// initialize the EasyColumns class
if (class_exists("EasyColumns")) {
	$wp_wp_columns = new EasyColumns();
}

// set up actions and filters
if (isset($wp_wp_columns)) {
	add_action('wp_head', array(&$wp_wp_columns, 'wpcol_add_css'), 100);
	if (function_exists('register_uninstall_hook'))
	{
		register_uninstall_hook(__FILE__, array(&$wp_wp_columns, 'uninstall'));
	}
}
?>