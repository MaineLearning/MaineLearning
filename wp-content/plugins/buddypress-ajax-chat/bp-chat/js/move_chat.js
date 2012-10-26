jQuery(document).ready(function() {
 if ( jQuery('#shoutboxwrapper').length == 0)
    return;

 jQuery('#shoutboxwrapper').draggable();

 jQuery('a#shoutboxclosetop').click(function(){
	jQuery('#shoutboxwrapper').hide('slow');
	jQuery('#ajaxChatCopyright').hide('slow');
 })

 jQuery('a#shoutboxclosebottom').click(function(){
	jQuery('#shoutboxwrapper').hide('slow');
	jQuery('#ajaxChatCopyright').hide('slow');
 })


 jQuery('a#shoutboxopen').click(function(){
	jQuery('#shoutboxwrapper').show('slow');
	jQuery('#ajaxChatCopyright').hide('slow');
 })

 jQuery('#ajaxChatCopyright').hide('slow');

 if(!NiftyCheck())
    return;
 Rounded("div#shoutboxwrapper","transparent","#888");

});
