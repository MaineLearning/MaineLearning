<?php
$cron = get_option('cron');

if ( !empty( $_GET['delete_cron'] ) ) {
	$args = unserialize( urldecode( stripslashes( $_GET['args'] ) ) );
	
	wp_unschedule_event( $_GET['next_run'], $_GET['hook'], $args );
	$this->alert( 'Deleted sheduled CRON event `' . htmlentities( $_GET['hook'] ) . '`.' );
	
	$cron = get_option('cron');
}

if ( !empty( $_GET['run_cron'] ) ) {
	$args = unserialize( urldecode( stripslashes( $_GET['args'] ) ) );
	
	do_action_ref_array( $_GET['hook'], $args );
	
	$this->alert( 'Ran CRON event `' . htmlentities( $_GET['hook'] ) . '` (did not modify schedule).' );
}



// Loop through each cron time
foreach ( (array) $cron as $time => $cron_item ) {
	if ( is_numeric( $time ) ) {
		// Loop through each schedule for this time
		foreach ( (array) $cron_item as $hook_name => $event ) {
			foreach ( (array) $event as $item_name => $item ) {
				echo '<span class="indent">Event Action:</span> ' . $hook_name . ' <a onclick="if ( !confirm(\'WARNING: This will delete a scheduled WordPress action. Doing so may compromise the operation of WordPress or other plugins and should be used with care. Are you sure you want to do this?\') ) { return false; }" href="' . $this->_selfLink . '-tools&delete_cron=true&next_run=' . $time . '&hook=' . $hook_name . '&args=' . urlencode( serialize( $item['args'] ) ) . '" title="Delete this scheduled cron entry"><img src="' . $this->_pluginURL . '/images/bullet_delete.png" style="vertical-align: -3px;" /></a> <a href="' . $this->_selfLink . '-tools&run_cron=true&next_run=' . $time . '&hook=' . $hook_name . '&args=' . urlencode( serialize( $item['args'] ) ) . '" title="Run scheduled cron\'s function now (does not modify schedule)"><img src="' . $this->_pluginURL . '/images/bullet_go.png" style="vertical-align: -3px;" /></a>';
				echo '<br />';
				echo '<span class="indent">Event Key:</span> ' . $item_name;
				echo '<br />';
				echo '<span class="indent">Run Time:</span> ' . date( $this->_parent->_timestamp . ' ' . get_option( 'gmt_offset' ), $time + ( get_option( 'gmt_offset' ) * 3600 ) ) . ' (' . $time . ')';
				echo '<br />';
				echo '<span class="indent">Period:</span> ';
				if ( !empty( $item['schedule'] ) ) {
					echo $item['schedule'];
				} else {
					echo '<i>one time only</i>';
				}
				echo '<br />';
				echo '<span class="indent">Interval:</span> ';
				if ( !empty( $item['interval'] ) ) {
					echo $item['interval'] . ' seconds';
				} else {
					echo '<i>one time only</i>';
				}
				echo '<br />';
				echo '<span class="indent">Arguments:</span> ';
				if ( !empty( $item['args'] ) ) {
					//foreach( $item['args'] as $argument ) {
						
					//}
					echo implode( ',', $item['args'] );
				} else {
					echo '<i>none</i>';
				}
				echo '<div style="border-bottom: 1px solid #DFDFDF; margin-top: 5px; margin-bottom: 5px;"></div>';
			}
			unset( $item );
			unset( $item_name );
		}
		unset( $event );
		unset( $hook_name );
	}
}
unset( $cron_item );
unset( $time );

if ( empty( $_GET['show_cron_array'] ) ) {
	echo '<a href="' . $this->_selfLink . '-tools&show_cron_array=true">Display CRON Debugging Array</a>';
} else {
	echo '<pre>';
	print_r( $cron );
	echo '</pre>';
}

unset( $cron );

echo ' &middot; Current Time: ' . date( $this->_parent->_timestamp . ' ' . get_option( 'gmt_offset' ), time() + ( get_option( 'gmt_offset' ) * 3600 ) ) . ' (' . time() . ')';
?>