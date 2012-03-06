<?php
class repairbuddy_module {
	function __construct() {
	
	}
	function get_plugin_dir( $path = '', $plugin = '' ) {
		$plugin_dir = rtrim( dirname( $plugin ), '/' );
		if ( !empty( $path ) && is_string( $path) )
			$plugin_dir .= '/' . ltrim( $path, '/' );
		return $plugin_dir;		
	}
	//Returns the plugin url
	function get_plugin_url( $path = '', $plugin = '' ) {
		$plugin_dir = rtrim( dirname( $plugin ), '/' );
		//die( $plugin_dir );
		$plugin_path = rtrim( str_replace( ABSPATH, '', $plugin_dir ), '/' );
		$root_path = 'http://' . $_SERVER[ 'HTTP_HOST' ] . str_replace( $plugin_path, '', $_SERVER[ 'REQUEST_URI' ] );
		
		$filename = basename( $_SERVER[ 'REQUEST_URI' ] );
		$full_url = "http://" . $_SERVER['HTTP_HOST']  .$_SERVER['REQUEST_URI'];
		$full_url = rtrim( str_replace( $filename, '', $full_url ), '/' ) . '/' . $plugin_path;
		
		if ( !empty( $path ) && is_string( $path) )
			$full_url .= '/' . ltrim( $path, '/' );
		return $full_url;	
	}
	function get_current_module() {
		$module = isset( $_GET[ 'module' ] ) ? $_GET[ 'module' ] : false;
		if ( $module ) {
			return $module;
		} else {
			return false;
		}
	} //end get_current_module
	function get_current_page() {
		$module = isset( $_GET[ 'page' ] ) ? $_GET[ 'page' ] : false;
		if ( $page ) {
			return $page;
		} else {
			return false;
		}
	} //end get_current_page
	function load_js( $src, $js_object = '', $localized_vars = array() ) {
		?>
		<script type='text/javascript' src='<?php echo $src; ?>'></script>
		<?php
		if ( !empty( $localized_vars ) ) {
			$this->js_localize( $js_object, $localized_vars );
		} 
	} //end load_javascript
	function load_css( $src ) {
		?>
		<link rel="stylesheet" type="text/css" href="<?php echo $src; ?>" />
		<?php
	} //end load_css
	function js_localize($name, $vars) { 
		$data = "var $name = {"; 
		$arr = array(); 
		foreach ( $vars as $key => $value ) { 
			$arr[count($arr)] = $key . " : '" . $value . "'"; 
		} 
		$data .= implode(",",$arr); 
		$data .= "};"; 
		echo "<script type='text/javascript'>\n"; 
		echo "/* <![CDATA[ */\n"; 
		echo $data; 
		echo "\n/* ]]> */\n"; 
		echo "</script>\n";
	} //end js_localize
}