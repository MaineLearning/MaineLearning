<?php
/**
 *	Admin Menue
 **/

/**
 * Don't load this file direct!
 */
if (!defined('ABSPATH')) {
	return ;
	}

if (is_admin()) {
	add_action('admin_menu', 'rw_cryptx_menu');
}

/**
 * load admin notice after activate/update if needed
 */
if(isset($cryptX_var['admin_notices_deprecated'])) {
	add_action('admin_notices', 'rw_cryptx_showAdminMessages');	
	$cryptX_var['admin_notices_deprecated']=false;
	update_option( 'cryptX', $cryptX_var);
}

/**
 * print admin notice
 */
function rw_cryptx_showAdminMessages()
{
	$searcher = new rw_cryptx_FileSystemStringSearch(get_template_directory().'/', ' cryptx('); 
	$searcher->run(); 
	
    if (current_user_can('manage_options')) {
		if($searcher->getResultCount() > 0) { 
			$msg = '<p><strong>';
		    $msg .= __('You use the deprecated CryptX function cryptx() in your template. You should use the new function encryptx() described at the plugin', 'cryptx');
			$msg .= sprintf(
					' <a href="options-general.php?page=%s">%s</a>',
					plugin_basename( __FILE__ ),
					__('Settings')
				);
			$msg .= '.</strong></p>';
		    $msg .= '<ul>'; 
		        foreach($searcher->getResults() as $result) { 
		            $msg .= '<li><em>'.$result['filePath'].', line '.$result['lineNumber'].'</em></li>'; 
		        } 
		    $msg .= '</ul>'; 
		    rw_cryptx_showMessage($msg, true);
		} elseif(isset($_POST['cryptX_rescan_theme'])) {
			rw_cryptx_showMessage('<p>'.__('Your theme is OK! You have nothing to do.','cryptx').'</p>');
		} 
    }
}

/**
 * add links to plugin site
 */
function rw_cryptx_init_row_meta($links, $file) {
	if (CRYPTX_BASENAME == $file) {
		return array_merge(
			$links,
			array(
				sprintf(
					'<a href="options-general.php?page=%s">%s</a>',
					plugin_basename( __FILE__ ),
					__('Settings')
				)
			),
			array(
				sprintf(
					'<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=4026696">%s</a>',
					__('Donate', 'cryptx')
				)
			)
		);
	}
	return $links;
}

/**
 * add CryptX to Option menu
 */
function rw_cryptx_menu() {
	add_options_page(
		'CryptX',
		(version_compare($GLOBALS['wp_version'], '2.6.999', '>') ? '<img src="' .@plugins_url('cryptx/icon.png'). '" width="10" height="10" alt="CryptX Icon" />' : ''). 'CryptX',
		'manage_options',
		__FILE__,
		'rw_cryptx_submenu'
	);
}

/**
 * print CryptX Option Page
 */
function rw_cryptx_submenu() {
	global $cryptX_var, $data;
	if (isset($_POST) && !empty($_POST)) {
		if (function_exists('current_user_can') === true && (current_user_can('manage_options') === false || current_user_can('edit_plugins') === false)) {
			wp_die("You don't have permission to access!");
		}
		$saveOptions = $_POST['cryptX_var'];
		check_admin_referer('cryptX');
		if(isset($_POST['cryptX_var_reset'])) {
			delete_option('cryptX');
			$saveOptions = rw_loadDefaults();
		}
		if(isset($_POST['cryptX_rescan_theme'])) {
			rw_cryptx_showAdminMessages();
		} else {
				$checkboxes = array(
					'the_content' => 0,
					'the_meta_key' => 0,
					'the_excerpt' => 0,
					'comment_text' => 0,
					'widget_text' => 0,
					'autolink' => 0,
					'metaBox' => 0,
				);
			$saveOptions = wp_parse_args( $saveOptions, $checkboxes );

			update_option( 'cryptX', $saveOptions);
			$cryptX_var = rw_loadDefaults();
		?>
		<div id="message" class="updated fade">
			<p><strong><?php _e('Settings saved.') ?></strong></p>
		</div>
	<?php } ?>
	<?php } ?>
	
	<div class="wrap">
	<?php if (version_compare($GLOBALS['wp_version'], '2.6.999', '>')) { ?>
	<div class="icon32" style="background: url(<?php echo @plugins_url('cryptx/icon32.png') ?>) no-repeat"><br /></div>
	<?php } ?>
	<h2>CryptX</h2>
	<br class="clear" />
	<form method="post" action="">
	<?php wp_nonce_field('cryptX') ?>
	<div id="poststuff" class="ui-sortable">
	<div id="rw_cryptx_information_box" class="postbox">
	<h3><?php _e("Information",'cryptx'); ?></h3>
	<div class="inside">
	<table class="form-table">
		<tr>
			<td valign="top" width="1%" nowrap><b><i><u>NEWS:</u></i>&nbsp;</b></td>
			<td valign="top"><div id="cryptx-news-content" style="display:none;"></div></td>
			<td valign="top" width="50%" style="border-left: 1px solid #999;"><?php
			$data = get_plugin_data(__FILE__);
			echo sprintf(
				'%1$s: %2$s <br /> %3$s: %4$s <br /> %5$s: <a href="http://weber-nrw.de" target="_blank">Ralf Weber</a> | <a href="http://twitter.com/Weber_NRW" target="_blank">%6$s</a> | <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=4026696">%7$s</a><br />',
				__('Plugin'),
				'CryptX',
				__('Version'),
				rw_cryptx_version() /*$data['Version']*/,
				__('Author'),
				__('Follow on Twitter', 'cryptx'),
				__('Donate', 'cryptx')
			);
			?>
			</td>
		</tr>
		<tr>
			<td colspan="3" align="center" style="font-weight: bold;"><?php _e("Please support me by translating CryptX into other languages. You can download the cryptx.pot file from my",'cryptx'); ?> <a href="http://weber-nrw.de/wordpress/cryptx/downloads/"><?php _e("site",'cryptx'); ?></a> <?php _e("and mail me the zipped language files. Thanks for it.",'cryptx'); ?> 
			</td>
		</tr>
	</table>
	</div>
	</div>

	<div id="rw_cryptx_presentation_box" class="postbox">
	<h3><?php _e("Presentation",'cryptx'); ?></h3>
	<div class="inside">
	
	<h4><?php _e("Define CSS Options",'cryptx'); ?></h4>
	<div class="postbox">
	<table class="form-table">
		<tr valign="top">
			<th><label for="cryptX_var[css_id]"><?php _e("CSS ID",'cryptx'); ?></label></th>
			<td><input name="cryptX_var[css_id]" value="<?php echo $cryptX_var['css_id']; ?>" type="text" class="regular-text" /><br /><?php _e("Please be careful using this feature! IDs should be unique. You should prefer of using a css class instead.",'cryptx'); ?></td>
		</tr>
		<tr valign="top">
			<th><label for="cryptX_var[css_class]"><?php _e("CSS Class",'cryptx'); ?></label></th>
			<td><input name="cryptX_var[css_class]" value="<?php echo $cryptX_var['css_class']; ?>" type="text" class="regular-text" /></td>
		</tr>
	</table>
	</div>
	
	<h4><?php _e("Define Presentation Options",'cryptx'); ?></h4>
	<div class="postbox">
	<table class="form-table">
		<tr valign="top">
			<td><input name="cryptX_var[opt_linktext]" type="radio" id="opt_linktext" value="0" <?php checked( $cryptX_var['opt_linktext'], 0 ); ?> /></td>
			<th scope="row"><label for="cryptX_var[at]"><?php _e("Replacement for '@'",'cryptx'); ?></label></th>
			<td><input name="cryptX_var[at]" value="<?php echo $cryptX_var['at']; ?>" type="text" class="regular-text" /></td>
		</tr>
		<tr valign="top">
			<td>&nbsp;</td>
			<th scope="row"><label for="cryptX_var[dot]"><?php _e("Replacement for '.'",'cryptx'); ?></label></th>
			<td><input name="cryptX_var[dot]" value="<?php echo $cryptX_var['dot']; ?>" type="text" class="regular-text" /></td>
		</tr>
		<tr valign="top" style="background: #efefef;">
			<td scope="row"><input type="radio" name="cryptX_var[opt_linktext]" id="opt_linktext2" value="1" <?php checked( $cryptX_var['opt_linktext'], 1 ); ?> /></td>
			<th><label for="cryptX_var[alt_linktext]"><?php _e("Text for link",'cryptx'); ?></label></th>
			<td><input name="cryptX_var[alt_linktext]" value="<?php echo $cryptX_var['alt_linktext']; ?>" type="text" class="regular-text" /></td>
		</tr>
		<tr valign="top">
			<td scope="row"><input type="radio" name="cryptX_var[opt_linktext]" id="opt_linktext3" value="2" <?php checked( $cryptX_var['opt_linktext'], 2 ); ?> /></td>
			<th><label for="cryptX_var[alt_linkimage]"><?php _e("Image-URL",'cryptx'); ?></label></th>
			<td><input name="cryptX_var[alt_linkimage]" value="<?php echo $cryptX_var['alt_linkimage']; ?>" type="text" class="regular-text" /></td>
		</tr>
		<tr valign="top">
			<td scope="row">&nbsp;</td>
			<th><label for="cryptX_var[http_linkimage_title]"><?php _e("Title-Tag for the Image",'cryptx'); ?></label></th>
			<td><input name="cryptX_var[http_linkimage_title]" value="<?php echo $cryptX_var['http_linkimage_title']; ?>" type="text" class="regular-text" /></td>
		</tr>
		<tr valign="top" style="background: #efefef;">
			<td scope="row"><input type="radio" name="cryptX_var[opt_linktext]" id="opt_linktext4" value="3" <?php checked( $cryptX_var['opt_linktext'], 3 ); ?> /></td>
			<th><label for="cryptX_var[alt_uploadedimage]"><?php _e("Select image from folder",'cryptx'); ?></label></th>
			<td>            	<select name="cryptX_var[alt_uploadedimage]" onchange="cryptX_bild_wechsel(this)">
			<?php foreach(rw_cryptx_dirImages() as $image) { 
				?>
				<option value="<?php echo plugins_url('cryptx/images/').$image; ?>" <?php selected( $cryptX_var['alt_uploadedimage'], plugins_url('cryptx/images/').$image ); ?> ><?php echo $image; ?></option>
			<?php } ?>
			</select>
			<br/><?php _e("the selected image: ",'cryptx'); ?><img src="<?php echo $cryptX_var['alt_uploadedimage']; ?>" id="cryptXmailTo" align="top" style="padding: 3px;"><br/>
			<span class="setting-description"><?php echo sprintf( __("Upload your favorite email-image to '%s'. Only .jpg and .gif Supported!",'cryptx'), plugin_dir_path( __FILE__ ).'images/' ); ?></span></td>
		</tr>
		<tr valign="top" style="background: #efefef;">
			<td>&nbsp;</td>
			<th><label for="cryptX_var[alt_linkimage_title]"><?php _e("Title-Tag for the Image",'cryptx'); ?></label></th>
			<td><input name="cryptX_var[alt_linkimage_title]" value="<?php echo $cryptX_var['alt_linkimage_title']; ?>" type="text" class="regular-text" /></td>
		</tr>
		<tr valign="top">
			<td scope="row"><input type="radio" name="cryptX_var[opt_linktext]" id="opt_linktext4" value="4" <?php checked( $cryptX_var['opt_linktext'], 4 ); ?> /></td>
			<th colspan="2"><?php _e("Text scrambled by AntiSpamBot (<small>Try it and look at your site and check the html source!</small>)",'cryptx'); ?></th>
		</tr>
		<tr valign="top" style="background: #efefef;">
			<td scope="row"><input type="radio" name="cryptX_var[opt_linktext]" id="opt_linktext5" value="5" <?php checked( $cryptX_var['opt_linktext'], 5 ); ?> /></td>
			<th><?php _e("Convert Email to PNG-image",'cryptx'); ?></th>
			<td><?php _e("Example with the saved options: ",'cryptx'); ?><img src="<?php echo get_bloginfo('url'); ?>/<?php echo md5( get_bloginfo('url') ); ?>/<?php echo antispambot("test@example.com"); ?>" align="absmiddle" alt="<?php echo antispambot("test@example.com"); ?>" title="<?php echo antispambot("test@example.com"); ?>"></td>
		</tr>
		<tr valign="top" style="background: #efefef;">
			<td>&nbsp;</td>
			<th><label for="cryptX_var[c2i_font]"><?php _e("Choose a Font",'cryptx'); ?></label></th>
			<td><select name="cryptX_var[c2i_font]">
			<?php foreach(rw_cryptx_dirFonts() as $font) { ?>
				<option value="<?php echo plugin_dir_path( __FILE__ ).'fonts/'.$font; ?>" <?php selected( $cryptX_var['c2i_font'], plugin_dir_path( __FILE__ ).'fonts/'.$font ); ?> ><?php echo $font; ?></option>
			<?php } ?>
			</select><br/>
			<span class="setting-description"><?php echo sprintf( __("Upload your favorite font to '%s'. Only .ttf is Supported!",'cryptx'), plugin_dir_path( __FILE__ ).'fonts/' ); ?></span>
			</td>
		</tr>
		<tr valign="top" style="background: #efefef;">
			<td>&nbsp;</td>
			<th><label for="cryptX_var[c2i_fontSize]"><?php _e("Font size (pixel)",'cryptx'); ?></label></th>
			<td><input name="cryptX_var[c2i_fontSize]" value="<?php echo $cryptX_var['c2i_fontSize']; ?>" type="text" class="regular-text" /></td>
		</tr>
		<tr valign="top" style="background: #efefef;">
			<td>&nbsp;</td>
			<th><label for="cryptX_var[c2i_fontRGB]"><?php _e("Font color (RGB)",'cryptx'); ?></label></th>
			<td><input name="cryptX_var[c2i_fontRGB]" value="<?php echo $cryptX_var['c2i_fontRGB']; ?>" type="text" class="regular-text" /></td>
		</tr>
	</table>
	</div>
		<p><input type="submit" name="cryptX" class="button-primary" value="<?php _e('Save Changes') ?>" /></p>
	</div>
	</div>
	
	
	<div id="rw_cryptx_general_box" class="postbox">
	<h3><?php _e("General",'cryptx'); ?></h3>
	<div class="inside">
	<table class="form-table">
		<tr valign="top">
			<th scope="row"><?php _e("Apply CryptX to...",'cryptx'); ?></th>
			<td>
				<input name="cryptX_var[the_content]"	type="checkbox" value="1" <?php checked( $cryptX_var['the_content'],	1 ); ?> />&nbsp;&nbsp;<?php _e("Content",'cryptx'); ?> <?php _e("(<i>this can be disabled per Post by an Option</i>)",'cryptx'); ?><br/>
				<input name="cryptX_var[the_meta_key]"	type="checkbox" value="1" <?php checked( $cryptX_var['the_meta_key'],	1 ); ?> />&nbsp;&nbsp;<?php _e("Custom fields (<strong>works only with the_meta()!</strong>)",'cryptx'); ?><br/>
				<input name="cryptX_var[the_excerpt]"	type="checkbox" value="1" <?php checked( $cryptX_var['the_excerpt'],	1 ); ?> />&nbsp;&nbsp;<?php _e("Excerpt",'cryptx'); ?><br/>
				<input name="cryptX_var[comment_text]"	type="checkbox" value="1" <?php checked( $cryptX_var['comment_text'],	1 ); ?> />&nbsp;&nbsp;<?php _e("Comments",'cryptx'); ?><br/>
				<input name="cryptX_var[widget_text]"	type="checkbox" value="1" <?php checked( $cryptX_var['widget_text'],	1 ); ?> />&nbsp;&nbsp;<?php _e("Widgets",'cryptx'); ?> <?php _e("(<i>works only on all widgets, not on a single widget</i>!)",'cryptx'); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e("Excluded ID's...",'cryptx'); ?></th>
			<td><input name="cryptX_var[excludedIDs]" value="<?php echo $cryptX_var['excludedIDs']; ?>" type="text" class="regular-text" />
			<br/><span class="setting-description"><?php _e("Enter all Page/Post ID's to exclude from CryptX as comma seperated list.",'cryptx'); ?></span>
			<br/><input name="cryptX_var[metaBox]" type="checkbox" value="1" <?php checked( $cryptX_var['metaBox'], 1 ); ?> />&nbsp;&nbsp;<?php _e("Enable the CryptX Widget on editing a post or page.",'cryptx'); ?></td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e("Type of decryption",'cryptx'); ?></th>
			<td><input name="cryptX_var[java]" type="radio" value="1" <?php checked( $cryptX_var['java'], 1 ); ?>/>&nbsp;&nbsp;<?php _e("Use javascript to hide the Email-Link.",'cryptx'); ?><br/>
				<input name="cryptX_var[java]" type="radio" value="0" <?php checked( $cryptX_var['java'], 0 ); ?>/>&nbsp;&nbsp;<?php _e("Use Unicode to hide the Email-Link.",'cryptx'); ?></td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e("Where to load the needed javascript...",'cryptx'); ?></th>
			<td><input name="cryptX_var[load_java]" type="radio" value="0"  <?php checked( $cryptX_var['load_java'], 0 ); ?>/>&nbsp;&nbsp;<?php _e("Load the javascript in the <b>header</b> of the page.",'cryptx'); ?><br/>
				<input name="cryptX_var[load_java]" type="radio" value="1"  <?php checked( $cryptX_var['load_java'], 1 ); ?>/>&nbsp;&nbsp;<?php _e("Load the javascript in the <b>footer</b> of the page.",'cryptx'); ?></td>
		</tr>
		<tr valign="top">
			<th scope="row" colspan="2"><input name="cryptX_var[autolink]" type="checkbox" value="1"  <?php checked( $cryptX_var['autolink'], 1 ); ?>/>&nbsp;&nbsp;<?php _e("Add mailto to all unlinked email addresses",'cryptx'); ?></th>
		</tr>
		<tr valign="top">
			<th scope="row" colspan="2"><input name="cryptX_var_reset" type="checkbox" value="1"/>&nbsp;&nbsp;<?php _e("Reset CryptX options to defaults. Use it carefully and at your own risk. All changes will be deleted!",'cryptx'); ?></th>
		</tr>
	</table>
		<p><input type="submit" name="cryptX" class="button-primary" value="<?php _e('Save Changes') ?>" /></p>
	</div>
	</div>
	
	
	<div id="rw_encryptx_howto_box" class="postbox">
	<h3><?php _e("How to use CryptX in your Template",'cryptx'); ?></h3>
	<div class="inside">
	<table class="form-table">
		<tr>
			<td><h4><?php _e("In your Template you can use the following function to encrypt a email address:",'cryptx'); ?></h4>
			<p style="border:1px solid #000; background-color: #e9e9e9;padding: 10px;">
			<i>&lt;?php <br/>
			&nbsp;&nbsp;&nbsp;&nbsp;$content = "name@example.com"; <br/>
			&nbsp;&nbsp;&nbsp;&nbsp;$args = array('text' => '',<br/>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'css_class' => '',<br/>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'css_id' => '',<br/>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'echo' => 1); <br/>
			&nbsp;&nbsp;&nbsp;&nbsp;if (function_exists('encryptx')) { <br/>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;encryptx($content, $args); <br/>
			&nbsp;&nbsp;&nbsp;&nbsp;} else { <br/>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo sprintf('&lt;a href="mailto:%s" id="%s" class="%s"&gt;%s&lt;/a&gt;', $content, $args['css_id'], $args['css_class'], ($args['text'] != '' ? $args['text'] : $content)); <br/>
			&nbsp;&nbsp;&nbsp;&nbsp;} <br/>
			?&gt;</i>
			</p>
			</td>
		</tr>
		<tr>
			<td><h4><?php _e("In your Template you can use the following function to encrypt mail adresses at custom fields:",'cryptx'); ?></h4>
			<?php _e("Replace the call of get_post_meta in yout template with the CryptX function <strong>get_encryptx_meta</strong>. The parameters are the same!<br/>1. Example:", 'cryptx'); ?><br/>
			<p style="border:1px solid #000; background-color: #e9e9e9;padding: 10px;">
			<i>
			&lt;?php <br/>
			&nbsp;&nbsp;&nbsp;&nbsp;foreach(get_encryptx_meta($post->ID, $key, false) as $mail) {<br/>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo $mail.'&lt;br/&gt;';</br/> 
			&nbsp;&nbsp;&nbsp;&nbsp;}<br/>
			?&gt;
			</i></p>
			<?php _e("2. Example:", 'cryptx'); ?><br/>
			<p style="border:1px solid #000; background-color: #e9e9e9;padding: 10px;">
			<i>
			&lt;?php echo get_encryptx_meta($post->ID, $key, true); ?&gt;
			</i><br/>
			</p>
			<input type="submit" name="cryptX_rescan_theme" class="button-primary" value="<?php _e('Rescan current theme', 'cryptx') ?>" />
			</td>
		</tr>
	</table>
	</div>
	</div>

	</form>
	<script type="text/javascript">
	function cryptX_bild_wechsel(select){ 
	 document.getElementById("cryptXmailTo").src = select.options[select.options.selectedIndex].value; 
	 return true; 
	 } 
	</script>
	<script type="text/javascript">
		jQuery.ajax({
			url: "<?php bloginfo('wpurl'); ?>?cryptx=news",
			success: function(data) {
				jQuery("#cryptx-news-content").html(data).fadeIn();
			},
			error: function() {
				jQuery("#cryptx-news-content").html('An error ocured while loading News.').fadeIn();
			}
		});
	</script>
	</div>
	</div>
<?php } ?>