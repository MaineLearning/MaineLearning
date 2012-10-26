<?php
/*
 * @package AJAX_Chat
 * @author Sebastian Tschan
 * @copyright (c) Sebastian Tschan
 * @license GNU Affero General Public License
 * @link https://blueimp.net/ajax/
 */
#define (WP_PLUGIN_DIR, dirname(__FILE__).'/../');
#require ('../../buddypress/bp-core.php' );
#require ('../../buddypress/bp-groups.php' );

// List containing the custom channels:
$channels = array();

// Sample channel list:
$channels[0] = 'Public';
$channels[1] = 'Private';
$i = 2;
#if ( bp_has_groups() ) :
#	while ( bp_groups() ) : bp_the_group();
#		bp_group_name();
#	endwhile;
#endif;
$channels[$i++] = 'Food';
$channels[$i++] = 'Dinner';
$channels[$i++] = 'Dave Aubin Group';
?>
