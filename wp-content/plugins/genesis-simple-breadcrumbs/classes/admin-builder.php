<?php
 
/**
 * NTG Admin Settings
 * Requires Genesis 1.8 or later
 *
 * This file registers all of this Settings, 
 * accessible from Genesis Submenu.
 * 
 * Built upon the CMB Meta Box class by Bill Erickson
 *
 * @package      NTG_Theme_Settings_Builder
 * @author       Nick the Geek <NicktheGeek@NickGeek.com>
 * @copyright    Copyright (c) 2012, Nick Croft
 * @license      <a href="http://opensource.org/licenses/gpl-2.0.php" rel="nofollow">http://opensource.org/licenses/gpl-2.0.php</a> GNU Public License
 * @since        1.0
 * @alter        1.11.2012
 *
 */

if( class_exists( 'Genesis_Admin_Boxes' ) && is_admin() ) {

/**
 * Add the Theme Settings Page
 *
 * @since 1.0.0
 */
function ntg_add_settings() {
    global $_child_theme_settings;
    
    $admin_pages = array();
    $admin_pages = apply_filters ( 'ntg_settings_builder' , $admin_pages );
    
    //print_r( $admin_pages );
    
    foreach ( $admin_pages as $admin_page ) {
        $settings   = isset( $admin_page['settings'] )   ? $admin_page['settings']   : array();
        $sanatize   = isset( $admin_page['sanatize'] )   ? $admin_page['sanatize']   : array();
        $help       = isset( $admin_page['help'] )       ? $admin_page['help']       : array();
        $meta_boxes = isset( $admin_page['meta_boxes'] ) ? $admin_page['meta_boxes'] : array();
        
        //print_r( $help );
        
	$_child_theme_settings = new NTG_Theme_Settings_Builder( $settings, $sanatize, $help, $meta_boxes );
    }
}
add_action( 'admin_menu', 'ntg_add_settings', 5 );

/*
 * url to load local resources.
 */

define( 'NTG_META_BOX_URL', trailingslashit( str_replace( WP_CONTENT_DIR, WP_CONTENT_URL, dirname(__FILE__) ) ) );

 
/**
 * Registers a new admin page, providing content and corresponding menu item
 * for the Child Theme Settings page.
 *
 * @package      WPS_Starter_Genesis_Child
 * @subpackage   Admin
 *
 * @since 1.0.0
 */
class NTG_Theme_Settings_Builder extends Genesis_Admin_Boxes {
 
    protected $_settings;
    protected $_sanatize;
    protected $_help;
    protected $_meta_box;
    
    /**
     * Create an admin menu item and settings page.
     *
     * @since 1.0.0
     */
    function __construct( $settings, $sanatize, $help, $meta_boxes ) {
        
        $this->_settings = $settings;
        $this->_sanatize = $sanatize;
        $this->_help     = $help;
        $this->_meta_box = $meta_boxes;
        
        //print_r( $settings );
        
        
        // Specify a unique page ID.
        $page_id = $settings['page_id'];
 
        // Set it as a child to genesis, and define the menu and page titles
        $menu_ops_defaults = array(
            'submenu' => array(
                'parent_slug' => 'genesis',
                'capability' => 'manage_options',
            )
        );
        
        $menu_ops = wp_parse_args( $settings['menu_ops'], $menu_ops_defaults );
 
        // Set up page options. These are optional, so only uncomment if you want to change the defaults
        $page_ops_defaults = array(
        //  'screen_icon'       => array( 'custom' => WPS_ADMIN_IMAGES . '/staff_32x32.png' ),
            'screen_icon'       => 'users',
        //  'save_button_text'  => 'Save Settings',
        //  'reset_button_text' => 'Reset Settings',
        //  'save_notice_text'  => 'Settings saved.',
        //  'reset_notice_text' => 'Settings reset.',
        );     
        
        $page_ops = wp_parse_args( $settings['page_ops'], $page_ops_defaults );
 
        // Give it a unique settings field.
        // You'll access them from genesis_get_option( 'option_name', CHILD_SETTINGS_FIELD );
        $settings_field = $settings['settings_field'];
 
        // Set the default values
        $default_settings = $settings['default_settings'];
        
        //print_r( $menu_ops );
 
        // Create the Admin Page
        $this->create( $page_id, $menu_ops, $page_ops, $settings_field, $default_settings );
        
        
        
        // Initialize the Sanitization Filter
        add_action( 'genesis_settings_sanitizer_init', array( $this, 'sanitization_filters' ) );
 
    }
 
    /**
     * Set up Sanitization Filters
     *
     * See /lib/classes/sanitization.php for all available filters.
     *
     * @since 1.0.0
     */
    function sanitization_filters() {
        
                    
            foreach( $this->_sanatize as $key => $values )
 
                genesis_add_option_filter( $key, $this->settings_field, $values );
        
        
    }
 
    /**
     * Register metaboxes on Child Theme Settings page
     *
     * @since 1.0.0
     *
     * @see Child_Theme_Settings::contact_information() Callback for contact information
     */
    function metaboxes() {
        
        $this->_meta_box['context']  = isset($this->_meta_box['context']) ?  $this->_meta_box['context'] : 'main';
	$this->_meta_box['priority'] = isset($this->_meta_box['priority']) ?  $this->_meta_box['priority'] : 'high';
		
        //print_r( $this->_meta_box );
        
        add_meta_box( $this->_meta_box['id'], $this->_meta_box['title'], array(&$this, 'show'), $this->pagehook, $this->_meta_box['context'], $this->_meta_box['priority']) ;
        
        
    }
 
    /**
     * Register contextual help on Child Theme Settings page
     *
     * @since 1.0.0
     *
     */
    function help( ) {
        global $my_admin_page;
        $screen = get_current_screen();
 
        if ( $screen->id != $this->pagehook )
            return;
        
        $tabs = isset( $this->_help['tab'] ) ? $this->_help['tab'] : array();
        
        //print_r( $tabs );
        
        foreach( $tabs as $tab ){
 
        $screen->add_help_tab(
            array(
                'id'        => $tab['id'],
                'title'     => $tab['title'],
                'content'   => $tab['content'],
            ) );
                
        }
 
        $sidebars = isset( $this->_help['sidebar'] ) ? $this->_help['sidebar'] : array();
        // Add Genesis Sidebar
        foreach( $sidebars as $sidebar )
        
            $screen->set_help_sidebar( $sidebar );
        
    }
 
    /**
     * Callback for Contact Information metabox
     *
     * @since 1.0.0
     *
     * @see Child_Theme_Settings::metaboxes()
     */
    // Show fields
	function show() {

		echo '<table class="form-table cmb_metabox">';

		foreach ( $this->_meta_box['fields'] as $field ) {
			// Set up blank values for empty ones
			if ( !isset($field['desc']) ) $field['desc'] = '';
			if ( !isset($field['std']) ) $field['std'] = '';
                        
                        if ( $field['type'] != "title" )
                            $meta = esc_html( $this->get_field_value( $field['id'] ) );
			
			echo '<tr>';
	
			if ( $field['type'] == "title" ) {
				echo '<td colspan="2">';
			} else {
				if( $this->_meta_box['show_names'] == true ) {
					echo '<th style="width:18%"><label for="', $this->get_field_id( $field['id'] ), '">', $field['name'], '</label></th>';
				}			
				echo '<td>';
			}		
						
			switch ( $field['type'] ) {

				case 'text':
					echo '<input type="text" name="', $this->get_field_name( $field['id'] ), '" id="', $this->get_field_id( $field['id'] ), '" value="', $meta ? $meta : $field['std'], '" style="width:97%" />','<p class="cmb_metabox_description">', $field['desc'], '</p>';
					break;
				case 'text_small':
					echo '<input class="cmb_text_small" type="text" name="', $this->get_field_name( $field['id'] ), '" id="', $this->get_field_id( $field['id'] ), '" value="', $meta ? $meta : $field['std'], '" /><span class="cmb_metabox_description">', $field['desc'], '</span>';
					break;
				case 'text_medium':
					echo '<input class="cmb_text_medium" type="text" name="', $this->get_field_name( $field['id'] ), '" id="', $this->get_field_id( $field['id'] ), '" value="', $meta ? $meta : $field['std'], '" /><span class="cmb_metabox_description">', $field['desc'], '</span>';
					break;
				case 'text_date':
					echo '<input class="cmb_text_small cmb_datepicker" type="text" name="', $this->get_field_name( $field['id'] ), '" id="', $this->get_field_id( $field['id'] ), '" value="', $meta ? $meta : $field['std'], '" /><span class="cmb_metabox_description">', $field['desc'], '</span>';
					break;
				case 'text_date_timestamp':
					echo '<input class="cmb_text_small cmb_datepicker" type="text" name="', $this->get_field_name( $field['id'] ), '" id="', $this->get_field_id( $field['id'] ), '" value="', $meta ? date( 'm\/d\/Y', $meta ) : $field['std'], '" /><span class="cmb_metabox_description">', $field['desc'], '</span>';
					break;
				case 'text_money':
					echo '$ <input class="cmb_text_money" type="text" name="', $this->get_field_name( $field['id'] ), '" id="', $this->get_field_id( $field['id'] ), '" value="', $meta ? $meta : $field['std'], '" /><span class="cmb_metabox_description">', $field['desc'], '</span>';
					break;
				case 'textarea':
					echo '<textarea name="', $this->get_field_name( $field['id'] ), '" id="', $this->get_field_id( $field['id'] ), '" cols="60" rows="10" style="width:97%">', $meta ? $meta : $field['std'], '</textarea>','<p class="cmb_metabox_description">', $field['desc'], '</p>';
					break;
				case 'textarea_small':
					echo '<textarea name="', $this->get_field_name( $field['id'] ), '" id="', $this->get_field_id( $field['id'] ), '" cols="60" rows="4" style="width:97%">', $meta ? $meta : $field['std'], '</textarea>','<p class="cmb_metabox_description">', $field['desc'], '</p>';
					break;
				case 'select':
					echo '<select name="', $this->get_field_name( $field['id'] ), '" id="', $this->get_field_id( $field['id'] ), '">';
					foreach ($field['options'] as $option) {
						echo '<option value="', $option['value'], '"', $meta == $option['value'] ? ' selected="selected"' : '', '>', $option['name'], '</option>';
					}
					echo '</select>';
					echo '<p class="cmb_metabox_description">', $field['desc'], '</p>';
					break;
				case 'radio_inline':
					echo '<div class="cmb_radio_inline">';
					foreach ($field['options'] as $option) {
						echo '<div class="cmb_radio_inline_option"><input type="radio" name="', $this->get_field_name( $field['id'] ), '" value="', $option['value'], '"', $meta == $option['value'] ? ' checked="checked"' : '', ' />', $option['name'], '</div>';
					}
					echo '</div>';
					echo '<p class="cmb_metabox_description">', $field['desc'], '</p>';
					break;
				case 'radio':
					foreach ($field['options'] as $option) {
						echo '<p><input type="radio" name="', $this->get_field_name( $field['id'] ), '" value="', $option['value'], '"', $meta == $option['value'] ? ' checked="checked"' : '', ' />', $option['name'].'</p>';
					}
					echo '<p class="cmb_metabox_description">', $field['desc'], '</p>';
					break;
				case 'checkbox':
					echo '<input type="checkbox" name="', $this->get_field_name( $field['id'] ), '" id="', $this->get_field_id( $field['id'] ), '"', $meta ? ' checked="checked"' : '', ' />';
					echo '<span class="cmb_metabox_description">', $field['desc'], '</span>';
					break;
				case 'multicheck':
					echo '<ul>';
					foreach ( $field['options'] as $value => $name ) {
						// Append `[]` to the name to get multiple values
						// Use in_array() to check whether the current option should be checked
						echo '<li><input type="checkbox" name="', $this->get_field_name( $field['id'] ), '[]" id="', $this->get_field_id( $field['id'] ), '" value="', $value, '"', in_array( $value, $meta ) ? ' checked="checked"' : '', ' /><label>', $name, '</label></li>';
					}
					echo '</ul>';
					echo '<span class="cmb_metabox_description">', $field['desc'], '</span>';					
					break;		
				case 'title':
					echo '<h5 class="cmb_metabox_title">', $field['name'], '</h5>';
					echo '<p class="cmb_metabox_description">', $field['desc'], '</p>';
					break;
				case 'wysiwyg':
					echo '<div id="poststuff" class="meta_mce">';
					echo '<div class="customEditor"><textarea name="', $this->get_field_name( $field['id'] ), '" id="', $this->get_field_id( $field['id'] ), '" cols="60" rows="7" style="width:97%">', $meta ? wpautop($meta, true) : '', '</textarea></div>';
					echo '</div>';
			        echo '<p class="cmb_metabox_description">', $field['desc'], '</p>';
					break;
				case 'taxonomy_select':
					echo '<select name="', $this->get_field_name( $field['id'] ), '" id="', $this->get_field_id( $field['id'] ), '">';
					$names= wp_get_object_terms( $post->ID, $field['taxonomy'] );
					$terms = get_terms( $field['taxonomy'], 'hide_empty=0' );
					foreach ( $terms as $term ) {
						if (!is_wp_error( $names ) && !empty( $names ) && !strcmp( $term->slug, $names[0]->slug ) ) {
							echo '<option value="' . $term->slug . '" selected>' . $term->name . '</option>';
						} else {
							echo '<option value="' . $term->slug . '  ' , $meta == $term->slug ? $meta : ' ' ,'  ">' . $term->name . '</option>';
						}
					}
					echo '</select>';
					echo '<p class="cmb_metabox_description">', $field['desc'], '</p>';
					break;
				case 'taxonomy_radio':
					$names= wp_get_object_terms( $post->ID, $field['taxonomy'] );
					$terms = get_terms( $field['taxonomy'], 'hide_empty=0' );
					foreach ( $terms as $term ) {
						if ( !is_wp_error( $names ) && !empty( $names ) && !strcmp( $term->slug, $names[0]->slug ) ) {
							echo '<p><input type="radio" name="', $this->get_field_name( $field['id'] ), '" value="'. $term->slug . '" checked>' . $term->name . '</p>';
						} else {
							echo '<p><input type="radio" name="', $this->get_field_name( $field['id'] ), '" value="' . $term->slug . '  ' , $meta == $term->slug ? $meta : ' ' ,'  ">' . $term->name .'</p>';
						}
					}
					echo '<p class="cmb_metabox_description">', $field['desc'], '</p>';
					break;
				case 'file_list':
					echo '<input id="upload_file" type="text" size="36" name="', $this->get_field_name( $field['id'] ), '" value="" />';
					echo '<input class="upload_button button" type="button" value="Upload File" />';
					echo '<p class="cmb_metabox_description">', $field['desc'], '</p>';
						$args = array(
								'post_type' => 'attachment',
								'numberposts' => null,
								'post_status' => null,
								'post_parent' => $post->ID
							);
							$attachments = get_posts($args);
							if ($attachments) {
								echo '<ul class="attach_list">';
								foreach ($attachments as $attachment) {
									echo '<li>'.wp_get_attachment_link($attachment->ID, 'thumbnail', 0, 0, 'Download');
									echo '<span>';
									echo apply_filters('the_title', '&nbsp;'.$attachment->post_title);
									echo '</span></li>';
								}
								echo '</ul>';
							}
						break;
				case 'file':
					echo '<input id="upload_file" type="text" size="45" class="', $this->get_field_id( $field['id'] ), '" name="', $this->get_field_name( $field['id'] ), '" value="', $meta, '" />';
					echo '<input class="upload_button button" type="button" value="Upload File" />';
					echo '<p class="cmb_metabox_description">', $field['desc'], '</p>';
					echo '<div id="', $this->get_field_id( $field['id'] ), '_status" class="cmb_upload_status">';	
						if ( $meta != '' ) { 
							$check_image = preg_match( '/(^.*\.jpg|jpeg|png|gif|ico*)/i', $meta );
							if ( $check_image ) {
								echo '<div class="img_status">';
								echo '<img src="', $meta, '" alt="" />';
								echo '<a href="#" class="remove_file_button" rel="', $this->get_field_id( $field['id'] ), '">Remove Image</a>';
								echo '</div>';
							} else {
								$parts = explode( "/", $meta );
								for( $i = 0; $i < sizeof( $parts ); ++$i ) {
									$title = $parts[$i];
								} 
								echo 'File: <strong>', $title, '</strong>&nbsp;&nbsp;&nbsp; (<a href="', $meta, '" target="_blank" rel="external">Download</a> / <a href="# class="remove_file_button" rel="', $this->get_field_id( $field['id'] ), '">Remove</a>)';
							}	
						}
					echo '</div>'; 
				break;
				
			}
			
			echo '</td>','</tr>';
		}
		echo '</table>';
	}
        
        
 
}


/**
 * Adding scripts and styles
 */

function ntg_scripts() {
  	
		wp_register_script( 'cmb-scripts', NTG_META_BOX_URL.'jquery.cmbScripts.js', array( 'jquery','media-upload','thickbox' ) );
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-core' ); // Make sure and use elements form the 1.7.3 UI - not 1.8.9
		wp_enqueue_script( 'media-upload' );
		wp_enqueue_script( 'thickbox' );
		wp_enqueue_script( 'cmb-scripts' );
                
		wp_enqueue_style( 'thickbox' );
		wp_enqueue_style( 'jquery-custom-ui' );
		
  	
}


function ntg_editor_admin_init() {
	if ( strpos( $_SERVER['REQUEST_URI'], 'post.php' ) OR strpos( $_SERVER['REQUEST_URI'], 'post-new.php' ) OR strpos( $_SERVER['REQUEST_URI'], 'page-new.php' ) OR strpos( $_SERVER['REQUEST_URI'], 'page.php' ) ) {
		wp_enqueue_script( 'word-count' );
		wp_enqueue_script( 'post' );
		wp_enqueue_script( 'editor' );
                
                add_action( 'admin_head', 'editor_admin_head' );
                add_action( 'admin_enqueue_scripts', 'ntg_scripts', 10, 1 );
	}
}

function ntg_editor_admin_head() {
    
	wp_tiny_mce();

}

//add_action( 'admin_init', 'editor_admin_init' );


function ntg_editor_footer_scripts() {  ?>
		<script type="text/javascript">/* <![CDATA[ */
		jQuery(function($) {
			var i=1;
			$('.customEditor textarea').each(function(e) {
				var id = $(this).attr('id');
 				if (!id) {
					id = 'customEditor-' + i++;
					$(this).attr('id',id);
				}
 				tinyMCE.execCommand('mceAddControl', false, id);
 			});
		});
	/* ]]> */</script>
	<?php }
//add_action( 'admin_print_footer_scripts', 'cmb_editor_footer_scripts', 99 );

add_action( 'admin_head', 'ntg_styles_inline' );
function ntg_styles_inline() { 
	echo '<link rel="stylesheet" type="text/css" href="' . NTG_META_BOX_URL.'style.css" />';
	?>	
	<style type="text/css">
		table.cmb_metabox td, table.cmb_metabox th { border-bottom: 1px solid #E9E9E9; }
		table.cmb_metabox th { text-align: right; font-weight:bold;}
		table.cmb_metabox th label { margin-top:6px; display:block;}
		p.cmb_metabox_description { color: #AAA; font-style: italic; margin: 2px 0 !important;}
		span.cmb_metabox_description { color: #AAA; font-style: italic;}
		input.cmb_text_small { width: 100px; margin-right: 15px;}
		input.cmb_text_money { width: 90px; margin-right: 15px;}
		input.cmb_text_medium { width: 230px; margin-right: 15px;}
		table.cmb_metabox input, table.cmb_metabox textarea { font-size:11px; padding: 5px;}
		table.cmb_metabox li { font-size:11px; }
		table.cmb_metabox ul { padding-top:5px; }
		table.cmb_metabox select { font-size:11px; padding: 5px 10px;}
		table.cmb_metabox input:focus, table.cmb_metabox textarea:focus { background: #fffff8;}
		.cmb_metabox_title { margin: 0 0 5px 0; padding: 5px 0 0 0; font: italic 24px/35px Georgia,"Times New Roman","Bitstream Charter",Times,serif;}
		.cmb_radio_inline { padding: 4px 0 0 0;}
		.cmb_radio_inline_option {display: inline; padding-right: 18px;}
		table.cmb_metabox input[type="radio"] { margin-right:3px;}
		table.cmb_metabox input[type="checkbox"] { margin-right:6px;}
		table.cmb_metabox .mceLayout {border:1px solid #DFDFDF !important;}
		table.cmb_metabox .mceIframeContainer {background:#FFF;}
		table.cmb_metabox .meta_mce {width:97%;}
		table.cmb_metabox .meta_mce textarea {width:100%;}
		table.cmb_metabox .cmb_upload_status {  margin: 10px 0 0 0;}
		table.cmb_metabox .cmb_upload_status .img_status {  position: relative; }
		table.cmb_metabox .cmb_upload_status .img_status img { border:1px solid #DFDFDF; background: #FAFAFA; max-width:350px; padding: 5px; -moz-border-radius: 2px; border-radius: 2px;}
		table.cmb_metabox .cmb_upload_status .img_status .remove_file_button { text-indent: -9999px; background: url(<?php echo NTG_META_BOX_URL ?>images/ico-delete.png); width: 16px; height: 16px; position: absolute; top: -5px; left: -5px;}
	</style>
	<?php
}

}