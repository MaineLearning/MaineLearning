$be = jQuery.noConflict();
$be(document).ready( function() {
	// Select all
	$be("A[href='#select_all']").click( function() {
		$be("INPUT[type='checkbox']", $be(this).attr('rel')).attr('checked', true);
		return false;
	});
	$be("A[href='#select_none']").click( function() {
		$be("INPUT[type='checkbox']", $be(this).attr('rel')).attr('checked', false);
		return false;
	});
});