 // jquery functions for admin panel
 
    jQuery(document).ready(function($) {
    		    
  $('#div_wpbook_app, #div_wpbook_stream,#wpbook_headerfooter_template,#wpbook_date_template').css('display','none');
 	// Toggle main setting sections
	$('.div_wpbook_toggle').click(function(){
		var target = 'div_'+$(this).attr('id');
		if( $('#'+target).css('display') == 'none' ) {
			$('.div_wpbook').slideUp();
			$('.div_wpbook_toggle_icon').removeClass('expanded').addClass('folded');
			$('#'+target).slideDown();
			$(this).find('.div_wpbook_toggle_icon').removeClass('folded').addClass('expanded');
		} else {
			$('.div_wpbook_toggle_icon').removeClass('expanded').addClass('folded');
			$(this).find('.div_wpbook_toggle_icon').removeClass('folded').addClass('expanded');
		}
	});
	
//Toggle sub menu options

$('.wpbook_hidden').hide();
$(':checkbox').change(function() {
    changeCheck(this);
});
$(':checkbox:checked').each(function(){
    changeCheck(this);
});
function changeCheck(x) {
    var option = 'wpbook_option_' + $(x).attr('id');
    if ($('.' + option).is(':visible')) {
        $('.' + option).fadeOut();
    }
    else {
        $('.' + option).fadeIn();
    }
}

//see if gravatar custom default is checked on page load
if (!$('.gravatar_rating_custom_radio').is(':checked')){
$('.gravatar_rating_custom').hide(); 
}
 //toggle status of gravatar custom default options on click
$("input[name='gravatar_default']").change(function(){
 if ($('.gravatar_rating_custom_radio').is(':checked'))
 {$('.gravatar_rating_custom').fadeIn();}
 else
 {$('.gravatar_rating_custom').fadeOut();}
 }); 

//add title text to exclude pages
   if ($("input[name=exclude_true]").is(':checked')) {
    	    $('span.which_pages').append('<span class="exclude_true_toggle"> <strong>  Which Ones?</strong></span>')
    }
$("input[name=exclude_true]").click(function() {
    $('span.which_pages').append('<span class="exclude_true_toggle"> <strong>  Which Ones? </strong></span>')

    if (!$("input[name=exclude_true]").is(':checked')) {
        $('.exclude_true_toggle').remove();
    }

})

//show check permission links if the vaule of ProfileID and PageID is not blank
var api_key = $("input[name=fb_api_key]").val();
var app_url = $("input[name=fb_app_url]").val();
var page_target = $("input[name=fb_page_target]").val();
var profile_target = $("input[name=fb_admin_target]").val();

//profile permissions 
if(profile_target.length > 0) {
	$('#grant_profile_permissions').html("<a href='http://www.facebook.com/connect/prompt_permissions.php?api_key="+api_key+"&v=1.0&next=http://apps.facebook.com/"+ app_url + "/?catch_permissions=true&extern=1&display=popup&ext_perm=publish_stream&enable_profile_selector=1&profile_selector_ids=" + profile_target + "'target='_new'>Grant Permissions for this user</a>");
}
$("input[name=fb_admin_target]").bind('keyup', function() {
			var profile_target = $(this).val()
			$('#grant_profile_permissions').html("<a href='http://www.facebook.com/connect/prompt_permissions.php?api_key="+api_key+"&v=1.0&next=http://apps.facebook.com/"+ app_url + "/?catch_permissions=true&extern=1&display=popup&ext_perm=publish_stream&enable_profile_selector=1&profile_selector_ids=" + profile_target + "'target='_new'>Grant Permissions for this user</a>");
	});

//page permissions
if(page_target.length > 0) {
	$('#grant_page_permissions').html("<a href='http://www.facebook.com/connect/prompt_permissions.php?api_key="+api_key+"&v=1.0&next=http://apps.facebook.com/"+ app_url + "/?catch_permissions=true&extern=1&display=popup&ext_perm=publish_stream&enable_profile_selector=1&profile_selector_ids=" + page_target + "'target='_new'>Grant Permissions for this page</a>");
}
$("input[name=fb_page_target]").bind('keyup', function() {
			var page_target = $(this).val()
			$('#grant_page_permissions').html("<a href='http://www.facebook.com/connect/prompt_permissions.php?api_key="+api_key+"&v=1.0&next=http://apps.facebook.com/"+ app_url + "/?catch_permissions=true&extern=1&display=popup&ext_perm=publish_stream&enable_profile_selector=1&profile_selector_ids=" + page_target + "'target='_new'>Grant Permissions for this page</a>");
	});


//toggle advanced date options
$('#wpbook_date_template').hide();
if ($("input[name=show_date_title]").is(':checked')) { 
	$('#wpbook_toggle_date_template').css('cursor', 'pointer').append('<span class="show_date_toggle"><strong>(Click here to change date/time format)</strong></span>');
}
$("input[name=show_date_title]").click(function() {
	$('#wpbook_toggle_date_template').css('cursor', 'pointer').append('<span class="show_date_toggle"><strong>(Click here to change date/time format)</strong></span>');

    if (!$("input[name=show_date_title]").is(':checked')) {
        $('.show_date_toggle').remove();
         $('#wpbook_date_template').fadeOut();
    }

});
	$('#wpbook_toggle_date_template')
	.css('cursor', 'pointer')
	.click(function() {
			$('#wpbook_date_template').toggle(500);
			$(this).find('.show_date_toggle').html(function(i, text) {
					if (text.indexOf('change') > 1) {
						return text.replace('change', 'close')
					}
					return text.replace('close', 'change')
			});
	});
//toggle advanced header/footer options
$('#wpbook_toggle_headerfooter_template').css('cursor', 'pointer')
    .append('<span class="headerfooter_toggle"> (click to view)</span>')
    .click(function() {
    $('#wpbook_headerfooter_template').toggle(500);
    $(this).find('.headerfooter_toggle').text(function(i, text) {
        if (text.indexOf('view') > 1) {
            return text.replace('view', 'close')
        }
        return text.replace('close', 'view')
    });
});
 
//toggle the link position option for share/external  
var position_link=$("#enable_share_options :checkbox");
if (!$(position_link).is(':checked'))  {$('#position_option').hide();}
position_link.change(function() {
		if (!$(position_link).is(':checked'))  {
				$('#position_option').fadeOut();
			}
			else {
				$('#position_option').fadeIn();
			}
});
//show hide custom header/footer depending on settings


//check status on change
$("input[name='show_custom_header_footer']").change(function(){
	if ($("input[name='show_custom_header_footer']:checked").val() == 'disabled') {
		$('#header_footer_options').fadeOut(500);
		}
	else if ($("input[name='show_custom_header_footer']:checked").val() == 'both') {
		
			$('#header_footer_options').fadeIn();
			$('#custom_header,#custom_footer').fadeIn();
		}
	else if ($("input[name='show_custom_header_footer']:checked").val() == 'header') {
		$('#header_footer_options').fadeIn();
		$('#custom_header').fadeIn();
		$('#custom_footer').hide();
		}
	else if ($("input[name='show_custom_header_footer']:checked").val() == 'footer') {
		$('#header_footer_options').fadeIn();
		$('#custom_footer').fadeIn();
		$('#custom_header').hide();
			
		}
	else{
		$('#header_footer_options').hide();
	}
});
//check status on page load
	if ($("input[name='show_custom_header_footer']:checked").val() == 'disabled') {
		$('#header_footer_options').hide();
		}
	else if ($("input[name='show_custom_header_footer']:checked").val() == 'both') {
		
			$('#header_footer_options').show();
			$('#custom_header,#custom_footer').show();
		}
	else if ($("input[name='show_custom_header_footer']:checked").val() == 'header') {
		$('#header_footer_options').show();
		$('#custom_header').show();
		$('#custom_footer').hide();
		}
	else if ($("input[name='show_custom_header_footer']:checked").val() == 'footer') {
		$('#header_footer_options').show();
		$('#custom_footer').show();
		$('#custom_header').hide();
			
		}
	else{
		$('#header_footer_options').hide();
	}




//date/time jquary from wp-admin/options-general.php
			$("input[name='timestamp_date_format']").click(function(){
			if ( "date_format_custom_radio" != $(this).attr("id") )
				$("input[name='timestamp_date_format_custom']").val( $(this).val() );
		});
		$("input[name='timestamp_date_format_custom']").focus(function(){
			$("#timestamp_date_format_custom").attr("checked", "checked");
		});

		$("input[name='timestamp_time_format']").click(function(){
			if ( "time_format_custom_radio" != $(this).attr("id") )
				$("input[name='timestamp_time_format_custom']").val( $(this).val() );
		});
		$("input[name='timestamp_time_format_custom']").focus(function(){
			$("#timestamp_time_format_custom").attr("checked", "checked");
		});


 });
  
