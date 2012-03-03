jQuery(document).ready(function( $ ) {
	var id = pb_passwords.button_id;
	var password_length = pb_passwords.password_length;
	var special_chars = pb_passwords.special_chars;
	var extra_special_chars = pb_passwords.extra_special_chars;
	//Event for the password generation script
	$( "#" + id ).bind( 'click', function() {
		$.post( ajaxurl, { action: 'pb_generate_password', password_length: password_length, special_chars: special_chars, extra_special_chars: extra_special_chars },
			function ( response ) {
				var password = response.password;
				$( "#pb_password_field" ).html( response.html );
				$( "#pb_password" ).trigger( "click" );
			}
		, 'json');
	} );	
	//Event for the fill button
	$( "#pb_fill_password" ).live( 'click', function() {
		var pass1 = '#' + pb_passwords.pass1;
		var pass2 = '#' + pb_passwords.pass2;
		var password = $( '#pb_password' ).val();
		$( pass1 + ',' + pass2 ).val( password );
		if ( typeof $.repairbuddy.passmeter != 'undefined' ) {
			$.repairbuddy.passmeter();
		}
		return false;
	} );
	//Event for input password button
	$( "#pb_password" ).live( 'click', function() {
		$( this ).select();
	} );
} );
