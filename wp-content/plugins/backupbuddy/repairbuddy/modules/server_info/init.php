<?php
// module_slug, Module Title, Module Description, bootstrap WordPress?
class rb_server_info extends repairbuddy_module {
	var $_module_name = '';
	function __construct() {
		pb_add_action( 'init', array( &$this, 'init' ) );
	} //end constructor
	function init() {
		$args = array(
			'slug' => 'server_info',
			'title' => 'Server Info.',
			'description' => 'Server details and configuration.',
			'page' => 'home',
			'bootstrap_wordpress' => false,
			'mini_mode' => false,
			'is_subtle' => '',
			'priority' => 25
		);
				
		$this->_module_name = $args[ 'slug' ];
		pb_register_module( $args );
		
		
		//Actions
		$action = sprintf( 'pb_loadpage_%s_%s', $args[ 'slug' ], $args[ 'page' ] );
		pb_add_action( $action , array( &$this, 'display_page' ) );
		pb_add_action( sprintf( 'pb_loadpage_%s_%s', $args[ 'slug' ], 'phpinfo' ), array( &$this, 'display_phpinfo' ) );
	} //end init
	function display_phpinfo() {
		$page = $this->get_plugin_dir( 'pages/phpinfo.php', __FILE__ );
		if ( !file_exists( $page ) ) {
			?>
			<h2>Could not load page</h2>
			<?php
		} else {
			require_once( $page );
		}

	} //end display_phpinfo
	function display_page() {
		
		$page = $this->get_plugin_dir( 'pages/home.php', __FILE__ );
		
		if ( !file_exists( $page ) ) {
			?>
			<h2>Could not load page</h2>
			<?php
		} else {
			global $pluginbuddy_repairbuddy;
			$parent_class = $pluginbuddy_repairbuddy;
			require_once( $page );
		}
	} //end display_page
	
} //end class
$rb_server_info = new rb_server_info();
?>