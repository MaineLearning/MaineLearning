/*
 * SwiftPopup (C) Copyright 2009 MicroSwift.com. All Rights Reserved.
 * Created exclusively for use on Typity.com by Dustin Bolton
 *
 * Last edited: 8/28/09
 * 
 * EXAMPLES:
 * Clicking a item to pop up a div.
 * jQuery('#popup').click(function(e) {
 *		showpopup('#editcat','',e);		
 *		return false;		
 *	});
 * Right click context menu with the content of 'test' then the id of the right clicked:
 * jQuery('.noteitem',this).bind("contextmenu",function(e){
 *		showpopup('#contextmenu','test'+jQuery(this).attr('id'),e);
 *		return false;
 *	});
 */

/*
 * showpopup() -- MUST RETURN FALSE RIGHT AFTER CALLING TO PREVENT FAST FADEOUT from caught click.
 * 
 * Displays a popup at mouse position.
 * 
 * @param	object	divid		ID of the div to pop up.
 * @param	integer	msg			Textual content. For AJAX, begin with URL:http://...
 * @param	object?	e			
 * @param	integer	fadeout		Auto-hide popup after this many ms.
 * @param	integer	basepadding	Base CSS padding inside div of popup
 * @param	boolean	clickclose	If true, clicking inside popup will close it.
 * @return	null
 */
function showpopup(divid,msg,e,fadeout,basepadding,clickclose) {
	if (basepadding>0) {
		jQuery(divid).css("padding",basepadding+"px");
	} else {
		jQuery(divid).css("padding","8px");
	}
	jQuery(document).unbind('click'); // Unbind any other popup clicks that may be showing.
	jQuery('.popup').hide(); // Instantly hide any other popups that may be showing.

	padding=30; // padding adjustment for things close to edge. Use: padding*2+border*2 + distance from edge. Also add shadow if added
	if (msg!='') { /* if content is passed, put it in the html of the div */
		if (msg.substring(0,4)=='URL:') {
			jQuery(divid).load(msg.replace('URL:','')); // AJAX load content.
		} else { // No ajax, just text.
			jQuery(divid).html(msg); // Set div content.
		}
	}
	var height = jQuery(divid).height();
	var width = jQuery(divid).width();
	
	// Check horizontal boundaries for going off edge.
	if (e.pageX+width+padding>jQuery(window).width()) { //If too close to right edge.
		// leftVal=(jQuery(window).width()-jQuery(divid).width()-padding)+"px";
		leftVal=(e.pageX-jQuery(divid).width()-padding)+"px";
	} else { // No issues, Display left side at mouse position.
		leftVal=(e.pageX)+"px";
	}
	// Check vertical boundaries for going off edge.
	if (e.pageY+height+padding>jQuery(window).height()) { //If too close to bottom edge.
		//topVal=(jQuery(window).height()-jQuery(divid).height()-padding)+"px";
		topVal=(e.pageY-jQuery(divid).height()-padding)+"px";
	} else { // No issues, Display top side at mouse position.
		topVal=(e.pageY)+"px";
	}

	jQuery(divid).css({left:leftVal,top:topVal}).fadeIn(500);
	
	jQuery(document).bind("click",function(e){ // Bind clicking anywhere in the page to closing popup.
		if (e.button!='2') { // if not right clicking
			hidepopup(divid);
			//return false;
		}
	});
	jQuery(divid).bind("click",function(e){ // Stop clicks in this div from propogating to the document.
		  e.stopPropagation();
	});	
	if (fadeout>0) {
		jQuery(divid).fadeOut(fadeout);
	}
}
function hidepopup(divid) {
	jQuery(divid).fadeOut(500);
	jQuery(document).unbind('click'); // unbind clicking document to closing popup		
	//return false;
}