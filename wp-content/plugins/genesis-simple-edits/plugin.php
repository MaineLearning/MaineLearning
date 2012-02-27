<?php
/*
	Plugin Name: Genesis Simple Edits
	Plugin URI: http://www.studiopress.com/plugins/genesis-simple-edits
	Description: Genesis Simple Edits lets you edit the three most commonly modified areas in any Genesis theme: the post-info, the post-meta, and the footer area.
	Author: Nathan Rice
	Author URI: http://www.nathanrice.net/

	Version: 1.7.1

	License: GNU General Public License v2.0 (or later)
	License URI: http://www.opensource.org/licenses/gpl-license.php
*/

/**
 * The main class that handles the entire output, content filters, etc., for this plugin.
 *
 * @package Genesis Simple Edits
 * @since 1.0
 */
class Genesis_Simple_Edits {
	
	/** Constructor */
	function __construct() {
		
		register_activation_hook( __FILE__, array( $this, 'activation_hook' ) );
		
		define( 'GSE_SETTINGS_FIELD', 'gse-settings' );
		
		add_action( 'admin_init', array( $this, 'javascript' ) );
		add_action( 'admin_init', array( $this, 'reset' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_menu', array( $this, 'add_menu' ), 15 );
		add_action( 'admin_notices', array( $this, 'notices' ) );
		
		add_filter( 'genesis_post_info', array( $this, 'post_info_filter' ), 20 );
		add_filter( 'genesis_post_meta', array( $this, 'post_meta_filter' ), 20 );
		add_filter( 'genesis_footer_backtotop_text', array( $this, 'footer_backtotop_filter' ), 20 );
		add_filter( 'genesis_footer_creds_text', array( $this, 'footer_creds_filter' ), 20 );
		add_filter( 'genesis_footer_output', array( $this, 'footer_output_filter' ), 20 );
	
	}
	
	function activation_hook() {
		
		$latest = '1.7.1';

		$theme_info = get_theme_data( TEMPLATEPATH . '/style.css' );

		if ( 'genesis' != basename( TEMPLATEPATH ) ) {
	        deactivate_plugins( plugin_basename( __FILE__ ) ); /** Deactivate ourself */
			wp_die( sprintf( __( 'Sorry, you can\'t activate unless you have installed <a href="%s">Genesis</a>', 'apl' ), 'http://www.studiopress.com/themes/genesis' ) );
		}

		if ( version_compare( $theme_info['Version'], $latest, '<' ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ) ); /** Deactivate ourself */
			wp_die( sprintf( __( 'Sorry, you cannot activate without <a href="%s">Genesis %s</a> or greater', 'apl' ), 'http://www.studiopress.com/support/showthread.php?t=19576', $latest ) );
		}
		
	}
	
	function javascript() {
		
		wp_enqueue_script( 'genesis-simple-edits-js', plugin_dir_url(__FILE__) . 'js/admin.js', array( 'jquery' ), '', true );
		
	}
	
	function register_settings() {
		register_setting( GSE_SETTINGS_FIELD, GSE_SETTINGS_FIELD );
		add_option( GSE_SETTINGS_FIELD, $this->settings_defaults() );
	}
	
	function reset() {
		
		if ( ! isset( $_REQUEST['page'] ) || 'genesis-simple-edits' != $_REQUEST['page'] )
			return;

		if ( genesis_get_option( 'reset', GSE_SETTINGS_FIELD ) ) {
			update_option( GSE_SETTINGS_FIELD, $this->settings_defaults() );
			wp_redirect( admin_url( 'admin.php?page=genesis-simple-edits&reset=true' ) );
			exit;
		}
		
	}
	
	function notices() {
		
		if ( ! isset( $_REQUEST['page'] ) || 'genesis-simple-edits' != $_REQUEST['page'] )
			return;

		if ( isset( $_REQUEST['reset'] ) && 'true' == $_REQUEST['reset'] ) {
			echo '<div id="message" class="updated"><p><strong>' . __( 'Simple Edits Reset', 'gse' ) . '</strong></p></div>';
		}
		elseif ( isset( $_REQUEST['updated'] ) && 'true' == $_REQUEST['updated'] ) {  
			echo '<div id="message" class="updated"><p><strong>' . __( 'Simple Edits Saved', 'gse' ) . '</strong></p></div>';
		}
		
	}
	
	function settings_defaults() {
		
		return array(
			'post_info' => '[post_date] ' . __('By', 'genesis') . ' [post_author_posts_link] [post_comments] [post_edit]',
			'post_meta' => '[post_categories] [post_tags]',
			'footer_backtotop_text' => '[footer_backtotop]',
			'footer_creds_text' => __('Copyright', 'genesis') . ' [footer_copyright] [footer_childtheme_link] &middot; [footer_genesis_link] [footer_studiopress_link] &middot; [footer_wordpress_link] &middot; [footer_loginout]',
			'footer_output_on' => 0,
			'footer_output' => '<div class="gototop"><p>[footer_backtotop]</p></div><div class="creds"><p>' . __('Copyright', 'genesis') . ' [footer_copyright] [footer_childtheme_link] &middot; [footer_genesis_link] [footer_studiopress_link] &middot; [footer_wordpress_link] &middot; [footer_loginout]</p></div>'
		);
		
	}
	
	function add_menu() {
		
		add_submenu_page('genesis', __('Genesis - Simple Edits','gse'), __('Simple Edits','gse'), 'manage_options', 'genesis-simple-edits', array( &$this, 'admin_page' ) );
	
	}
	
	function admin_page() { ?>
		
		<div class="wrap">
			<form method="post" action="options.php">
			<?php settings_fields( GSE_SETTINGS_FIELD ); // important! ?>
			
			<?php screen_icon( 'options-general' ); ?>	
			<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
				
				<table class="form-table"><tbody>
					
					<tr>
						<th scope="row"><p><label for="<?php echo GSE_SETTINGS_FIELD; ?>[post_info]"><b><?php _e('Post Info', 'genesis'); ?></b></label></p></th>
						<td>
							<p><input type="text" name="<?php echo GSE_SETTINGS_FIELD; ?>[post_info]" id="<?php echo GSE_SETTINGS_FIELD; ?>[post_info]" value="<?php echo esc_attr( genesis_get_option('post_info', GSE_SETTINGS_FIELD) ); ?>" size="125" /></p>
						</td>
					</tr>
					
					<tr>
						<th scope="row"><p><label for="<?php echo GSE_SETTINGS_FIELD; ?>[post_meta]"><b><?php _e('Post Meta', 'genesis'); ?></b></label></p></th>
						<td>
							<p><input type="text" name="<?php echo GSE_SETTINGS_FIELD; ?>[post_meta]" id="<?php echo GSE_SETTINGS_FIELD; ?>[post_meta]" value="<?php echo esc_attr( genesis_get_option('post_meta', GSE_SETTINGS_FIELD) ); ?>" size="125" /></p>
							
							<p><small><a class="post-shortcodes-toggle" href="#">Show Available Post Info/Meta Shortcodes</a></small></p>
							
						</td>
					</tr>
					
					<tr class="post-shortcodes" style="display: none;">
						<th scope="row"><p><span class="description"><?php _e('Shortcode Reference'); ?></span></p></th>
						<td>
							<p><span class="description"><?php _e('NOTE: For a more comprehensive shortcode usage guide, <a href="http://dev.studiopress.com/shortcode-reference" target="_blank">see this page</a>.') ?>
							<p>
								<ul>
									<li>[post_date] - <span class="description"><?php _e('Date the post was published', ''); ?></span></li>
									<li>[post_time] - <span class="description"><?php _e('Time the post was published', ''); ?></span></li>
									<li>[post_author] - <span class="description"><?php _e('Post author display name', ''); ?></span></li>
									<li>[post_author_link] - <span class="description"><?php _e('Post author display name, linked to their website', ''); ?></span></li>
									<li>[post_author_posts_link] - <span class="description"><?php _e('Post author display name, linked to their archive', ''); ?></span></li>
									<li>[post_comments] - <span class="description"><?php _e('Post comments link', ''); ?></span></li>
									<li>[post_tags] - <span class="description"><?php _e('List of post tags', ''); ?></span></li>
									<li>[post_categories] - <span class="description"><?php _e('List of post categories', ''); ?></span></li>
									<li>[post_edit] - <span class="description"><?php _e('Post edit link (visible to admins)', ''); ?></span></li>
								</ul>
							</p>
						</td>
					</tr>
					
					<tr>
						<th scope="row"><p><label for="<?php echo GSE_SETTINGS_FIELD; ?>[footer_backtotop_text]"><b><?php _e('Footer "Back to Top" Link', 'genesis'); ?></b></label></p></th>
						<td>
							<p><input type="text" name="<?php echo GSE_SETTINGS_FIELD; ?>[footer_backtotop_text]" id="<?php echo GSE_SETTINGS_FIELD; ?>[footer_backtotop_text]" value="<?php echo esc_attr( genesis_get_option('footer_backtotop_text', GSE_SETTINGS_FIELD) ); ?>" size="125" /></p>
						</td>
					</tr>
					
					<tr>
						<th scope="row"><p><label for="<?php echo GSE_SETTINGS_FIELD; ?>[footer_creds_text]"><b><?php _e('Footer Credits Text', 'genesis'); ?></b></label></p></th>
						<td>
							<p><input type="text" name="<?php echo GSE_SETTINGS_FIELD; ?>[footer_creds_text]" id="<?php echo GSE_SETTINGS_FIELD; ?>[footer_creds_text]" value="<?php echo esc_attr( genesis_get_option('footer_creds_text', GSE_SETTINGS_FIELD) ); ?>" size="125" /></p>
						</td>
					</tr>
					
					<tr>
						<th scope="row"><p><b><?php _e('Footer Output', 'gse'); ?></b></p></th>
						<td>
							<p><input type="checkbox" name="<?php echo GSE_SETTINGS_FIELD; ?>[footer_output_on]" id="<?php echo GSE_SETTINGS_FIELD; ?>[footer_output_on]" value="1" <?php checked( 1, genesis_get_option('footer_output_on', GSE_SETTINGS_FIELD) ); ?> /> <label for="<?php echo GSE_SETTINGS_FIELD; ?>[footer_output_on]"><?php _e('Modify Entire Footer Text (including markup)?', 'genesis'); ?></label></p>
							
							<p><span class="description"><?php _e('NOTE: Checking this option will override any edits you may have done to the footer "Back to Top" link or Credits text above.'); ?></span></p>
							
							<p><textarea name="<?php echo GSE_SETTINGS_FIELD; ?>[footer_output]" cols="80" rows="5"><?php echo esc_html( htmlspecialchars( genesis_get_option('footer_output', GSE_SETTINGS_FIELD) ) ); ?></textarea></p>
							
							<p><small><a class="footer-shortcodes-toggle" href="#">Show Available Footer Shortcodes</a></small></p>
						</td>
					</tr>
					
					<tr class="footer-shortcodes" style="display: none;">
						<th scope="row"><p><span class="description"><?php _e('Shortcode Reference'); ?></span></p></th>
						<td>
							<p><span class="description"><?php _e('NOTE: For a more comprehensive shortcode usage guide, <a href="http://dev.studiopress.com/shortcode-reference" target="_blank">see this page</a>.') ?>
							<p>
								<ul>
									<li>[footer_backtotop] - <span class="description"><?php _e('The "Back to Top" Link', ''); ?></span></li>
									<li>[footer_copyright] - <span class="description"><?php _e('The Copyright notice', ''); ?></span></li>
									<li>[footer_childtheme_link] - <span class="description"><?php _e('The Child Theme Link', ''); ?></span></li>
									<li>[footer_genesis_link] - <span class="description"><?php _e('The Genesis Link', ''); ?></span></li>
									<li>[footer_studiopress_link] - <span class="description"><?php _e('The StudioPress Link', ''); ?></span></li>
									<li>[footer_wordpress_link] - <span class="description"><?php _e('The WordPress Link', ''); ?></span></li>
									<li>[footer_loginout] - <span class="description"><?php _e('Log In/Out Link', ''); ?></span></li>
								</ul>
							</p>
						</td>
					</tr>
					
				</tbody></table>
				
				<div class="bottom-buttons">
					<input type="submit" class="button-primary" value="<?php _e('Save Settings', 'genesis') ?>" />
					<input type="submit" class="button-highlighted" name="<?php echo GSE_SETTINGS_FIELD; ?>[reset]" value="<?php _e('Reset Settings', 'genesis'); ?>" />
				</div>
				
			</form>
		</div>
		
	<?php }
	
	function post_info_filter( $output ) {
		
		return genesis_get_option( 'post_info', GSE_SETTINGS_FIELD );
		
	}
	
	function post_meta_filter( $output ) {
		
		return genesis_get_option( 'post_meta', GSE_SETTINGS_FIELD );
		
	}
	
	function footer_backtotop_filter( $output ) {
		
		return genesis_get_option( 'footer_backtotop_text', GSE_SETTINGS_FIELD );
		
	}
	
	function footer_creds_filter( $output ) {
		
		return genesis_get_option( 'footer_creds_text', GSE_SETTINGS_FIELD );
		
	}
	
	function footer_output_filter( $output ) {
		
		if ( genesis_get_option( 'footer_output_on', GSE_SETTINGS_FIELD ) )
			return genesis_get_option( 'footer_output', GSE_SETTINGS_FIELD );
			
		return $output;
		
	}
	
}

$Genesis_Simple_Edits = new Genesis_Simple_Edits;