jQuery( document ).ready( function( $ ) {
	//Clear or set defaults for search bar
	$( "#user_search" ).live( "click focus", function() {
		var user_search = $( "#user_search" ).val();
		if ( user_search == pb_admin_creator.default ) {
			$( "#user_search" ).val( '' );
		}
	} );
	$( "#user_search" ).live( "blur", function() {
		var user_search = $( "#user_search" ).val();
		if ( user_search == '' ) {
			$( "#user_search" ).val( pb_admin_creator.default );
		}
	} );
	
	//Event for Search Button
	$( "#search" ).live( "click", function() {
		$( "#search" ).attr( 'disabled', 'disabled' );
		$( "#search" ).val( pb_admin_creator.searching );
		$( "#loading" ).show();
		$( "#status" ).hide();
		var search = $( "#user_search" ).val();
		var hash = $( "#hash" ).val();
		var page = $( "#page" ).val();
		$( "#username, #email, #pass1, #pass2" ).val( '' ).removeAttr( "disabled" );
		$.post( pb_ajaxurl, { action: 'search_user', search: search, hash: hash, page: page, load_wp: true },
			function( response ){
				if ( typeof response.error != 'undefined'  ) {
					
					//Show the new form because new of a new user
					$( "#search_user" ).fadeOut( "slow", function() { 
						$( "#save" ).removeAttr( "disabled" ).val( pb_admin_creator.save );
						$( "#cancel" ).show();
						$( "#create_user" ).fadeIn( "slow", function() {
							//User doesn't exist
							$( "#status_message" ).html( response.error );
							$( "#status" ).removeClass( "updated" ).addClass( "error" );
							$( "#status" ).show();
							if ( search != pb_admin_creator.default ) {
								$( "#username" ).val( search );
							}
							$( "#username" ).trigger( "focus" );
						} );
					} );
				} else {
					//We're editing a user
					$( "#user_id" ).val( response.ID );
					//Show the new form because new of a new user
					$( "#search_user" ).fadeOut( "slow", function() { 
						$( "#save" ).removeAttr( "disabled" ).val( pb_admin_creator.save );
						$( "#cancel" ).show();
						$( "#create_user" ).fadeIn( "slow", function() {
							//User doesn't exist
							$( "#status_message" ).html( pb_admin_creator.user_edit );
							$( "#status" ).removeClass( "error" ).addClass( "updated" );
							$( "#status" ).show();
							$( "#username" ).val( response.user_login ).attr( "disabled", "disabled" );
							$( "#email" ).val( response.user_email ).attr( "disabled", "disabled" );
							$( "#pass1" ).trigger( "focus" );
						} );
					} );
				}
				$( "#loading" ).hide();
				$( "#search" ).removeAttr( 'disabled' );
				$( "#search" ).val( pb_admin_creator.search );
			console.log( response );			
		}, 'json');
		return false;
	} );
	
	//Event for Cancel button
	$( "#cancel" ).live( "click", function() {
		$( "#user_id" ).val( '0' );
		$( "#status" ).hide();
		$( "#status" ).removeClass( "error" ).addClass( "updated" );
		//Hide new form and show search form
		$( "#create_user" ).fadeOut( "slow", function() {
			$( "#search_user" ).fadeIn( "slow", function() {
				$( "#user_search" ).trigger( "focus" );
			} );
		} );
		return false;
	} );
	
	//Event for Save button
	$( "#save" ).live( "click", function() {
		$( "#loading" ).show();
		$( "#status" ).hide();
		$( "#status" ).removeClass( "error" ).addClass( "updated" );
		$( "#save" ).attr( "disabled", "disabled" ).val( pb_admin_creator.saving );
		$( "#cancel" ).hide();
		var hash = $( "#hash" ).val();
		var page = $( "#page" ).val();
		var username = $( "#username" ).val();
		var email = $( "#email" ).val();
		var pass1 = $( "#pass1" ).val();
		var pass2 = $( "#pass2" ).val();
		var user_id = $( "#user_id" ).val();
		$.post( pb_ajaxurl, { action: 'create_user', user_id: user_id, username: username, email: email, pass1: pass1, pass2: pass2, hash: hash, page: page, load_wp: true },
			function( response ){
				$( "#loading" ).hide();
				if ( typeof response.error != 'undefined' ) {
					$( "#save" ).removeAttr( "disabled" ).val( pb_admin_creator.save );
					$( "#cancel" ).show();
					$( "#status_message" ).html( response.error );
					$( "#status" ).removeClass( "updated" ).addClass( "error" ).show();
					
				} else {
					$( "#create_user" ).hide();
					$( "#status_message" ).html( response.success );
					$( "#status" ).removeClass( "error" ).addClass( "updated" ).show();
				}
			console.log( response );			
		}, 'json');
		$( "#user_id" ).val( '0' );
		return false;
	} );
} );