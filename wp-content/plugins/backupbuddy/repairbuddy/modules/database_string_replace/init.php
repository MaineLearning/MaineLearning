<?php
// module_slug, Module Title, Module Description, bootstrap WordPress?
class rb_db_string_replace extends repairbuddy_module {
	var $_module_name = '';
	function __construct() {
		pb_add_action( 'init', array( &$this, 'init' ) );
	} //end constructor
	function init() {
		$args = array(
			'slug' => 'database_string_replace',
			'title' => 'Database String Replace',
			'description' => 'Find & replace text within the database.',
			'page' => 'home',
			'bootstrap_wordpress' => true,
			'mini_mode' => false,
			'is_subtle' => '',
		);
		
		$this->_module_name = $args[ 'slug' ];
		pb_register_module( $args );
		
		//Actions
		$action = sprintf( 'pb_loadpage_%s_%s', $args[ 'slug' ], $args[ 'page' ] );
		pb_add_action( $action , array( &$this, 'display_page' ) );
	} //end init
	
	function display_page() {
		
		$page = $this->get_plugin_dir( 'pages/home.php', __FILE__ );
		
		if ( !file_exists( $page ) ) {
			?>
			<h2>Could not load page</h2>
			<?php
		} else {
			require_once( $page );
		}
	} //end display_page
	
} //end class
$rb_db_string_replace = new rb_db_string_replace();
?>