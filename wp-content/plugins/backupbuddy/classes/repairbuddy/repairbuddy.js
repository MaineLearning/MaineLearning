jQuery(document).ready(function( $ ) {
	$.repairbuddy = {
		passmeter: function() {
			var pass1 = $( "#pass1" ).val();
			var pass2 = $( "#pass2" ).val();
			$( "#pass-strength-result" ).removeClass( 'short bad good strong' );
			if ( !pass1 ) {
				$( '#pass-strength-result' ).html( "Strength Indicator" );
				return false;
			}
			var password_strength = passwordStrength( pass1, 'pluginbuddy', pass2 );
			switch( password_strength ) {
				case 2:
					$( "#pass-strength-result" ).addClass( "bad" ).html( "Weak" );
					break;
				case 3:
					$( "#pass-strength-result" ).addClass( "good" ).html( "Good" );
					break;
				case 4:
					$( "#pass-strength-result" ).addClass( "strong" ).html( "Strong" );
					break;
				case 5:
					$( "#pass-strength-result" ).addClass( "short" ).html( "Mismatch" );
					break;
				default:
					$( "#pass-strength-result" ).addClass( "short" ).html( "Very weak" );
					break;
			} //end switch
		}
	};
	$( '#pass1' ).val( '' ).keyup( jQuery.repairbuddy.passmeter );
	$( '#pass2' ).val( '' ).keyup( jQuery.repairbuddy.passmeter );
	$( '#pass-strength-result' ).show();
	
} );
