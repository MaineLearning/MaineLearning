<?php if ( !defined( 'WP_TABLE_RELOADED_ABSPATH' ) ) exit; // no direct loading of this file ?>
<?php
    $this->helper->print_header_message( __( 'Plugin deactivated successfully.', WP_TABLE_RELOADED_TEXTDOMAIN ) );
    echo "<p>" . __( 'All tables, data and options were deleted. You may now manually remove the plugin\'s subfolder from your WordPress plugin folder or use the "Delete" link on the Plugins page.', WP_TABLE_RELOADED_TEXTDOMAIN ) . "</p>";
?>
