$be = jQuery.noConflict();
$be(document).ready( function() {
	$be("#bebop_admin_container").on( 'click', '.bebop_provider_helper_trigger', function() {
		$be('.bebop_provider_helper').stop().slideToggle( 500 );
	});
	return false;
});