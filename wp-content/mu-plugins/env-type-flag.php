<?php

/**
 * Adds 'local environment' tab
 */
function bbg_env_type_flag() {
	if ( defined( 'ENV_TYPE' ) && 'local' == ENV_TYPE ) {
		
		?>

		<style type="text/css">
			#env-type-flag {
				position: fixed;
				right: 0;
				bottom: 50px;
				width: 200px;
				padding: 10px 15px;
				text-align: center;
				background: #f00;
				color: #fff;
				font-size: 1.5em;
				line-height: 1.8em;
				border: 2px solid #666;
				z-index: 1000;
			}
		</style>

		<div id="env-type-flag">
			LOCAL ENVIRONMENT
		</div>

		<?php
	}
}
add_action( 'wp_footer', 'bbg_env_type_flag' );
add_action( 'admin_footer', 'bbg_env_type_flag' );

?>