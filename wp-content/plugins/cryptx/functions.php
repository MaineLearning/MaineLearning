<?php
/**
 *	CryptX functions
 **/

/**
 * Don't load this file direct!
 */
if (!defined('ABSPATH')) {
	return ;
	}

/*
 *	Loading defaults 
 */
function rw_loadDefaults($options='') {
	$firstImage = rw_cryptx_dirImages();
	$firstFont = rw_cryptx_dirFonts();
	$defaults = array(
				'at' => ' [at] ',
				'dot' => ' [dot] ',
				'css_id' => '',
				'css_class' => '',
				'the_content' => 1,
				'the_meta_key' => 1,
				'the_excerpt' => 1,
				'comment_text' => 1,
				'widget_text' => 1,
				'java' => 1,
				'load_java' => 0,
				'opt_linktext' => 0,
				'autolink' => 1,
				'alt_linktext' => '',
				'alt_linkimage' => '',
				'http_linkimage_title' => '',
				'alt_linkimage_title' => '',
				'excludedIDs' => '',
				'metaBox' => 1,
				'alt_uploadedimage' => plugins_url('cryptx/images/').$firstImage[0],
				'c2i_font' => plugin_dir_path( __FILE__ ).'fonts/'.$firstFont[0],
				'c2i_fontSize' => 10,
				'c2i_fontRGB' => '000000',
				'echo' => 1,
				'filter' => array('the_content','the_meta_key','the_excerpt','comment_text','widget_text')
			);
	$return = wp_parse_args( get_option('cryptX'), $defaults );
	$return = (is_array($options))? wp_parse_args( $options, $return ) : $return ;
	
	return $return;
}

/*
 *	Add support for Shortcode
 */
function rw_cryptx_shortcode( $atts, $content=null) {
	global $cryptX_var;

	if (@$cryptX_var[autolink]) $content = rw_cryptx_autolink($content, true);
	$content = rw_cryptx_encryptx($content, true);
	$content = rw_cryptx_linktext($content, true);

	return $content;
}

/*
 *	load CryptX news
 */
function rw_cryptx_parse_request( $wp ) {
	if ( isset($_GET['cryptx']) ) {
		switch( $_GET['cryptx'] ) {
			case 'news':
				include( 'ajax/news.php' );
				break;
		}			
		exit;
	}	
}

/*
 * creyte image from tinyurl
 */
function rw_cryptx_init_tinyurl() {
	global $cryptX_var;
	$url = $_SERVER['REQUEST_URI'];
	$params = explode( '/', $url );
	if ( count( $params ) > 1 ) {
		$tiny_url = $params[count( $params ) -2];
		if ( $tiny_url == md5( get_bloginfo('url') ) ) {
			$font = $cryptX_var['c2i_font']; 
			$msg = $params[count( $params ) -1];
			$size = $cryptX_var['c2i_fontSize']; 
			$pad = 1;
			$transparent = 1;
			$red = hexdec(substr($cryptX_var['c2i_fontRGB'],0,2)); 
			$grn = hexdec(substr($cryptX_var['c2i_fontRGB'],2,2));
			$blu = hexdec(substr($cryptX_var['c2i_fontRGB'],4,2));
			$bg_red = 255 - $red;
			$bg_grn = 255 - $grn;
			$bg_blu = 255 - $blu;
			$width = 0;
			$height = 0;
			$offset_x = 0;
			$offset_y = 0;
			$bounds = array();
			$image = "";
			$bounds = ImageTTFBBox($size, 0, $font, "W");
			$font_height = abs($bounds[7]-$bounds[1]);
			$bounds = ImageTTFBBox($size, 0, $font, $msg);
			$width = abs($bounds[4]-$bounds[6]);
			$height = abs($bounds[7]-$bounds[1]);
			$offset_y = $font_height+abs(($height - $font_height)/2)-1;
			$offset_x = 0;
			$image = imagecreatetruecolor($width+($pad*2),$height+($pad*2));
			imagesavealpha($image, true);
			$foreground = ImageColorAllocate($image, $red, $grn, $blu);
			$background = imagecolorallocatealpha($image, 0, 0, 0, 127);
			imagefill($image, 0, 0, $background);
			ImageTTFText($image, $size, 0, $offset_x+$pad, $offset_y+$pad, $foreground, $font, $msg);
			Header("Content-type: image/png");
			imagePNG($image);
			die;
		}
	}
}

/*
 *	acivate needed filter
 */
function rw_cryptx_filter($apply) {
	global $cryptX_var, $shortcode_tags;
	if (@$cryptX_var['autolink']) {
		add_filter($apply, 'rw_cryptx_autolink', 5);
		if (!empty($shortcode_tags) || is_array($shortcode_tags)) {
			add_filter($apply, 'rw_cryptx_autolink', 11);
		}		
	}
	add_filter($apply, 'rw_cryptx_encryptx', 12);
	add_filter($apply, 'rw_cryptx_linktext', 13);
}

/*
 *	check for excuded IDs
 */
function rw_cryptx_excluded($ID) {
	global $cryptX_var;
	$return = false;
	$exIDs = explode(",", $cryptX_var['excludedIDs']);
	if(in_array($ID, $exIDs) > 0 ) $return = true;
	return $return;
}

/*
 *	search for link texts	
 */
function rw_cryptx_linktext($content, $shortcode=false) {
	global $post;
	$postID = (is_object($post))? $post->ID : -1;
	if (!rw_cryptx_excluded($postID) OR $shortcode!=false) {
		$content = preg_replace_callback("/([_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*(\.[a-zA-Z]{2,}))/i", 'rw_cryptx_do_Linktext', $content );
	}
	return $content;	
}

/*
 *	replace linktexts
 */
function rw_cryptx_do_linktext($Match) {
	global $cryptX_var;
	$vars = $cryptX_var;
	switch ($vars['opt_linktext']) {

		case 1: // alternative text for mail link
			$linktext = $vars['alt_linktext'];
			break;

		case 2: // alternative image for mail link
			$linktext = "<img src=\"" . $vars['alt_linkimage'] . "\" class=\"cryptxImage\" alt=\"" . $vars['alt_linkimage_title'] . "\" title=\"" . antispambot($vars['alt_linkimage_title']) . "\" />";
			break;

		case 3: // uploaded image for mail link
			$imgurl = $vars['alt_uploadedimage'];
			$linktext = "<img src=\"" . $imgurl . "\" class=\"cryptxImage\" alt=\"" . $vars['http_linkimage_title'] . "\" title=\"" .antispambot( $vars['http_linkimage_title']) . "\" />";
			break;

		case 4: // text scrambled by antispambot
			$linktext = antispambot($Match[1]);
			break;

		case 5: // convert to image
			$linktext = "<img src=\"" . get_bloginfo('url') . "/" . md5( get_bloginfo('url') ) . "/" . antispambot($Match[1]) . "\" class=\"cryptxImage\" alt=\"" . antispambot($Match[1]) . "\" title=\"" . antispambot($Match[1]) . "\" />";
			break;

		default:
			$linktext = str_replace( "@", $vars['at'], $Match[1]);
			$linktext = str_replace( ".", $vars['dot'], $linktext);

	}
	return $linktext;
}

/*
 * show images from dir
 */
function rw_cryptx_dirImages() {
	$dir = plugin_dir_path( __FILE__ ).'images'; 
	$fh = opendir($dir);
	$verzeichnisinhalt = array();
	while (true == ($file = readdir($fh)))
	{
		if ((substr(strtolower($file), -3)=="jpg") or (substr(strtolower($file), -3)=="gif")) 
			{
			$verzeichnisinhalt[] = $file;
			}
	}
	return $verzeichnisinhalt;
}

/*
 * show fonts from dir
 */
function rw_cryptx_dirFonts() {
	$dir = plugin_dir_path( __FILE__ ).'fonts'; 
	$fh = opendir($dir);
	$verzeichnisinhalt = array();
	while (true == ($file = readdir($fh)))
	{
		if ((substr(strtolower($file), -3)=="ttf") or (substr(strtolower($file), -3)=="ttf")) 
			{
			$verzeichnisinhalt[] = $file;
			}
	}
	return $verzeichnisinhalt;
}

/*
 * search for mailto tags
 */
function rw_cryptx_encryptx($content, $shortcode=false) {
	global $post;
	$postID = (is_object($post))? $post->ID : -1;

	if (!rw_cryptx_excluded($postID) OR $shortcode!=false) {
		$content = preg_replace_callback('/<a (.*?)(href=("|\')mailto:(.*?)("|\')(.*?)|)>(.*?)<\/a>/i', 'rw_cryptx_mailtocrypt', $content );
	}
	return $content;
}

/*
 * encryptx adresses with javascript
 */
function rw_cryptx_mailtocrypt($Match) {
	global $cryptX_var;
	$return = $Match[0];
	$mailto = "mailto:" . $Match[4];
	if (substr($Match[4], 0, 9) =="?subject=") return $return;
	if (@$cryptX_var['java']) {
		$crypt = '';
		$ascii = 0;
		for ($i = 0; $i < strlen( $Match[4] ); $i++) {
			$ascii = ord ( substr ( $Match[4], $i ) );
			if (8364 <= $ascii) {
				$ascii = 128;
			}
			$crypt .= chr($ascii + 1);
		}
		$javascript="javascript:DeCryptX('" . $crypt . "')";
		$return = str_replace( "mailto:".$Match[4], $javascript, $return);
	} else {				
			$return = str_replace( $mailto, antispambot($mailto), $return);
	}	
	return $return;
}

/*
 * add link to email adresses
 */
function rw_cryptx_autolink($content, $shortcode=false) {
	global $post;
	$postID = (is_object($post))? $post->ID : -1;
	if (rw_cryptx_excluded($postID) AND $shortcode==false) return $content;
	$src[]="/([\s])([_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*(\.[a-zA-Z]{2,}))/si";
	$src[]="/(>)([_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*(\.[a-zA-Z]{2,}))(<)/si";
	$src[]="/(\()([_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*(\.[a-zA-Z]{2,}))(\))/si";
	$src[]="/(>)([_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*(\.[a-zA-Z]{2,}))([\s])/si";
	$src[]="/([\s])([_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*(\.[a-zA-Z]{2,}))(<)/si";
	$src[]="/^([_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*(\.[a-zA-Z]{2,}))/si";
	$src[]="/(<a[^>]*>)<a[^>]*>/";
	$src[]="/(<\/A>)<\/A>/i";
	
	$tar[]="\\1<a href=\"mailto:\\2\">\\2</a>";
	$tar[]="\\1<a href=\"mailto:\\2\">\\2</a>\\6";
	$tar[]="\\1<a href=\"mailto:\\2\">\\2</a>\\6";
	$tar[]="\\1<a href=\"mailto:\\2\">\\2</a>\\6";
	$tar[]="\\1<a href=\"mailto:\\2\">\\2</a>\\6";
	$tar[]="<a href=\"mailto:\\0\">\\0</a>";
	$tar[]="\\1";
	$tar[]="\\1";
	$content = preg_replace($src,$tar,$content);
	return $content;
}

/*
 * needed stuff for install process
 */
function rw_cryptx_install() {
	global $cryptX_var, $wpdb;
	$cryptX_var = rw_loadDefaults(); // reread Options
	$cryptX_var['admin_notices_deprecated']=true;
	if ($cryptX_var['excludedIDs'] == "") {
		$tmp = array();
		$excludes = $wpdb->get_results("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'cryptxoff' AND meta_value = 'true'");
		if(count($excludes) > 0) {
			foreach ($excludes as $exclude) {
				$tmp[] = $exclude->post_id;
			}
			sort($tmp);
			$cryptX_var['excludedIDs'] = implode(",", $tmp);
			update_option( 'cryptX', $cryptX_var);
			$cryptX_var = rw_loadDefaults(); // reread Options			
			$wpdb->query("DELETE FROM $wpdb->postmeta WHERE meta_key = 'cryptxoff'");
		}
	}
	if (empty($cryptX_var['c2i_font'])) {
		$cryptX_var['c2i_font'] = plugin_dir_path( __FILE__ ).'fonts/'.$firstFont[0];
	}
	if (empty($cryptX_var['c2i_fontSize'])) {
		$cryptX_var['c2i_fontSize'] = 10;
	}
	if (empty($cryptX_var['c2i_fontRGB'])) {
		$cryptX_var['c2i_fontRGB'] = '000000';
	}
	update_option( 'cryptX', $cryptX_var);
	$cryptX_var = rw_loadDefaults(); // reread Options
}

/*
 * add code to site for loading the needed javascript
 */
function rw_cryptx_header() {
	$cryptX_script = "<script type=\"text/javascript\" src=\"" . site_url() . '/' . PLUGINDIR . '/' . dirname(plugin_basename (__FILE__)) . "/js/cryptx.js\"></script>\n";
	print($cryptX_script);
}

/*
 * add support for CryptX MetaBox
 */
function rw_cryptx_meta_box() {
	if ( function_exists('add_meta_box') ) {
		add_meta_box('cryptx','CryptX', 'rw_cryptx_meta','post');
		add_meta_box('cryptx','CryptX', 'rw_cryptx_meta','page');
	} else {
		add_action('dbx_post_sidebar', 'rw_cryptx_option');
		add_action('dbx_page_sidebar', 'rw_cryptx_option');
	}
}

/*
 * The MetaBox new-style
 */
function rw_cryptx_meta() {
	global $post;
	?>
	<input type="checkbox" name="cryptxoff" <?php if (rw_cryptx_excluded($post->ID)) { echo 'checked="checked"'; } ?>/> Disable CryptX for this post/page
	<?php
}

/*
 * The MetaBox old-style
 */
function rw_cryptx_option() {
	global $post;
	if ( current_user_can('edit_posts') ) { ?>
	<fieldset id="cryptxoption" class="dbx-box">
	<h3 class="dbx-handle">CryptX</h3>
	<div class="dbx-content">
		<input type="checkbox" name="cryptxoff" <?php if (rw_cryptx_excluded($post->ID)) { echo 'checked="checked"'; } ?>/> Disable CryptX for this post/page
	</div>
	</fieldset>
<?php 
	}
}

/*
 * add ID to exclude list
 */
function rw_cryptx_insert_post($pID) {
	global $cryptX_var, $post;
	$rev = wp_is_post_revision($pID);
	if($rev) $pID = $rev;
	$b = explode(",", $cryptX_var['excludedIDs']);
	if($b[0] == '') unset($b[0]);
	foreach($b as $x=>$y) {
		if($y == $pID) {
			unset($b[$x]);
			break;
		}
	}
	if (isset($_POST['cryptxoff'])) $b[] = $pID;
	$b = array_unique($b);
	sort($b);
	$cryptX_var['excludedIDs'] = implode(",", $b);
	update_option( 'cryptX', $cryptX_var);
	$cryptX_var = rw_loadDefaults(); // reread Options
}

/**
 * print admin notice
 */
function rw_cryptx_showMessage($message, $errormsg = false)
{
	if ($errormsg) {
		echo '<div id="message" class="error">';
	}
	else {
		echo '<div id="message" class="updated fade">';
	}

	echo "$message</div>";
} 

?>