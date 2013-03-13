
/**
 * Insert into editor callback
 */
jQuery.fn.extend({
    insertAtCaret: function(myValue){
        return this.each(function(i) {
            if (document.selection) {
                this.focus();
                sel = document.selection.createRange();
                sel.text = myValue;
                this.focus();
            }
            else if (this.selectionStart || this.selectionStart == '0') {
                var startPos = this.selectionStart;
                var endPos = this.selectionEnd;
                var scrollTop = this.scrollTop;
                this.value = this.value.substring(0, startPos)+myValue+this.value.substring(endPos,this.value.length);
                this.focus();
                this.selectionStart = startPos + myValue.length;
                this.selectionEnd = startPos + myValue.length;
                this.scrollTop = scrollTop;
            } else {
                this.value += myValue;
                this.focus();
            }
        })
    }
});

var iclEditorWidth = 550;
var iclEditorWidthMin = 195;
var iclEditorHeight = 420;
var iclEditorHeightMin = 195;

jQuery(document).ready(function(){
    /*
     * Set active editor
     * Important when switching between editor instances.
     */
    window.wpcfActiveEditor = false;
    jQuery('.wpcf-wysiwyg .editor_addon_wrapper .item, #postdivrich'
        + '.editor_addon_wrapper .item').click(function(){
        window.wpcfActiveEditor = jQuery(this).parents('.wpcf-wysiwyg, #postdivrich')
        .find('textarea').attr('id');
    });
    
    /*
     *
     * Handle the "Add Field" boxes - some layout changes.
     *
     * SRDJAN
     * Removed resizing and setting CSS from here.
     * Use:
     * icl_editor_popup(element)
     */
    jQuery('.wpv_add_fields_button').click(function(e) {
        var dropdown_list = jQuery('#add_field_popup .editor_addon_dropdown');
        
        if (dropdown_list.css('visibility') == 'hidden') {

            /*
             * Specific for 'Add Field'
             * Make changes before setting popup
             */
            jQuery('#add_field_popup .editor_addon_wrapper .vicon').css('display', 'none');
            jQuery('#add_field_popup').show();
            
            // Place it above button
            dropdown_list.css('margin', '-25px 0 0 -15px');
            var pos = jQuery('.wpv_add_fields_button').position();
            dropdown_list.css('top', pos.top + jQuery('.wpv_add_fields_button').height() - iclEditorHeight + 'px');
            dropdown_list.css('left', pos.left + jQuery('.wpv_add_fields_button').width() + 'px');

            // Toggle
            icl_editor_popup(dropdown_list);

        } else {
            dropdown_list.css('visibility', 'hidden');
        }
    });
	
	
    /*
     *
     * This manages the "V" button
     */
    jQuery('.editor_addon_wrapper img').click(function(e){
        
        var drop_down = jQuery(this).parent().find('.editor_addon_dropdown');
        /*
         *
         * Check if visible
         */
        if (drop_down.css('visibility') == 'hidden') {
            
            // TODO Remove
            // Close others possibly opened
            // Handled in icl_editor_resize_popup()
            wpv_hide_top_groups(jQuery(this).parent());
            //            jQuery('.editor_addon_dropdown').css('visibility', 'hidden')
            //            .css('display', 'inline');
            //            jQuery(this).parent().find('.editor_addon_dropdown')
            //            .css('visibility', 'visible').css('display', 'inline');
            
            // Popup
            icl_editor_popup(drop_down);
            
        } else {
            // Hide all
            jQuery('.editor_addon_dropdown').css('visibility', 'hidden').css('display', 'inline');
        }
        
        
        // Bind close on iFrame click (it's loaded now)
        /*
         *
         * TODO Check and document this
         * SRDJAN I do not understand this one...
         */
        jQuery('#content_ifr').contents().bind('click', function(e) {
            jQuery('.editor_addon_dropdown').css('visibility', 'hidden').css('display', 'inline');
        });
        
        
        // Bind Escape
        jQuery(document).bind('keyup', function(e) {
            if (e.keyCode == 27) {
                jQuery('.editor_addon_dropdown').css('visibility', 'hidden').css('display', 'inline');
                jQuery(this).unbind(e);
            }
        });
    

    });

    /*
     *
     *
     * Trigger close action
     */
    jQuery('.editor_addon_wrapper .item, .editor_addon_dropdown .close').click(function(e){
        jQuery('.editor_addon_dropdown').css('visibility', 'hidden').css('display', 'inline');
    });
    
    /*
     *
     *
     * SRDJAN
     * TODO Remove - I think this is not necessary and cludges browser response
     */
    // Resize dropdowns if necessary (in #media-buttons)
    //    jQuery('#media-buttons .editor_addon_dropdown, ' +
    //        '#wp-content-media-buttons .editor_addon_dropdown, ' +
    //        '#wpv-layout-v-icon-posts .editor_addon_dropdown').each(function(){
    //		
    //        icl_editor_resize_popup(jQuery(this));
    //		
    //    });
    
    
    
    
    /*
     *
     * SRDJAN
     * TODO I think this is not used anymore so to remove it?
     * I think we stopped using <a> as trigger
     */
    // For hidden in Meta HTML set scroll when visible
    jQuery('#wpv_layout_meta_html_admin_show a, #wpv_filter_meta_html_admin_show a').click(function(){
        alert('DEPRECATED');
        jQuery(this).parent().parent().find('.editor_addon_dropdown').each(function(){
            var scrollDiv = jQuery(this).find('.scroll');
            var divWidth = 400;
            var divHeight = 250;
            jQuery(this).width(divWidth).css('width', divWidth+'px');
            scrollDiv.width(Math.round(divWidth-40)).css('width', (Math.round(divWidth-40))+'px');
            jQuery(this).height(divHeight).css('height', divHeight+'px');
            var scrollHeight = Math.round(divHeight-jQuery(this).find('.direct-links').height()-50);
            scrollDiv.height(scrollHeight).css('height', scrollHeight+'px');
            //            scrollDiv.jScrollPane();
            if (jQuery(this).find('.jspPane').height() < scrollDiv.height()) {
                jQuery(this).find('.direct-links').hide();
                scrollDiv.height(Math.round(divHeight-50)).css('height', (Math.round(divHeight-50))+'px');
            }
        });
    });
    
    
    /*
     *
     *
     *
     * Set Meta HTML dropdown to insert in active editor
     */
    window.wpcfInsertMetaHTML = false;
    jQuery('#wpv_filter_meta_html_admin_edit .item').click(function(){
        window.wpcfInsertMetaHTML = jQuery(this).parents('.editor_addon_wrapper').parent().find('textarea').attr('id');
    });
    jQuery('#wpv_layout_meta_html_admin_edit .item').click(function(){
        window.wpcfInsertMetaHTML = jQuery(this).parents('.editor_addon_wrapper').parent().parent().find('textarea').attr('id');
    });



    /*
     * 
     * Direct links
     * 
     * Types 1.2 & WP 3.5
     * 
     * Removed .scroll - I can not see if .scroll div has any role anymore
     * Removed all classes and code related to it.
     * 3rd party scroll.js is not used anywhere in code.
     * 
     * @see http://api.jquery.com/scrollTop/
     * @see http://api.jquery.com/offset/
     * @see http://api.jquery.com/position/
     */
    jQuery('.editor-addon-top-link').click(function(){
        /*
         * SRDJAN
         * Before Types 1.2 and WP 3.5
         * TODO Remove
         */
        // get position of elements
        //        var positionNested = jQuery('#'+jQuery(this).attr('id')+'-target').offset();
        //        var positionParent = jQuery('#'+jQuery(this).attr('id')+'-target').parent().parent().offset();
        //        if (positionParent.top > positionNested.top) {
        //            var scrollTo = positionParent.top - positionNested.top;
        //        } else {
        //            var scrollTo = positionNested.top - positionParent.top;
        //        }
        //        jQuery(this).parents('.editor_addon_dropdown').find('.scroll').animate({
        //            scrollTop:Math.round(scrollTo)
        //        }, 'fast');

        /*
         * SRDJAN Types 1.2 and WP 3.5
         * Lets re-define vars
         */
        var scrollTargetDiv = jQuery(this).parents('.editor_addon_dropdown');
        var target = jQuery(this).parents('li')
        .find('.'+jQuery(this).data('editor_addon_target')+'-target');
        var position = target.position();
        var scrollTo = position.top;
        
        // Do scroll.
        scrollTargetDiv.animate({
            scrollTop:Math.round(scrollTo)
        }, 'fast');

    });
});


/*
 *
 *
 *
 *
 *
 *
 *
 *
 *
 * FUNCTIONS
 */

/**
 *
 * Main popup function
 */
function icl_editor_popup(e) {

    // Toggle
    icl_editor_toggle(e);
            
    // Set popup
    icl_editor_resize_popup(e);
        
    // TODO Scroll window if popup title is out of screen
    // Not sure why other elements fail for
    // offset()
    //    var t = e.parents('ul').one().offset();
    //    alert(t.top);
    //    if (e.parents('ul').one().is(':icl_offscreen')) {alert('off screen');
    //        jQuery('html, body').animate({
    //            scrollTop: title.offset().top
    //            }, 2000);
    //    }
            
    // Bind window click to auto-hide
    icl_editor_bind_auto_close();
}

// TODO
jQuery.expr.filters.icl_offscreen = function(el) {
    var t = jQuery(el).offset();
    return (
        (el.offsetLeft + el.offsetWidth) < 0 
        || (el.offsetTop + el.offsetHeight) < 0
        || (el.offsetLeft > window.innerWidth || el.offsetTop > window.innerHeight)
        );
};

/**
 * Toggles popups.
 * 
 * @todo We have multiple calls here.
 */
function icl_editor_toggle(element) {
    
    // Hide all except current
    jQuery('.editor_addon_dropdown').each(function(){
        if (element.attr('id') != jQuery(this).attr('id')) {
            jQuery(this).css('visibility', 'hidden')
            .css('display', 'inline');
        } else {
            if (jQuery(this).css('visibility') == 'visible') {
                jQuery(this).css('visibility', 'hidden');
            } else {
                jQuery(this).css('visibility', 'visible').css('display', 'inline');
            }
        }
    });
    
// Toggle current
//    if (visible == 'visible') {
//        jQuery(element).css('visibility', 'hidden');
//    } else {
//        jQuery(element).css('visibility', 'visible').css('display', 'inline');
//    }
}

/**
 * Resizing Toolset editor dropdowns.
 * 
 * Mind there are multiple instances on same screen.
 * @see .editor_addon_dropdown
 */
function icl_editor_resize_popup(element) {

    /*
     * First hide elements that should not be taken into account
     * Important: this is where we show shortuts in popup
     */
    jQuery(element).find('.direct-links').hide();
    jQuery(element).find('.editor-addon-link-to-top').hide();
    
    // Initial state
    // If hidden will be 0
    var heightInitial = jQuery(element).height();

    /*
     * Resize
     * 
     * We'll take main editor width
     */
    var editorWidth = Math.round( jQuery('#post-body-content').width() + 20 );
    icl_editor_resize_popup_width(element, editorWidth);
    
    /*
     * Adjust size.
     */
    if (heightInitial > iclEditorHeight) {
        /*
         *
         * Important: this is where we show shortuts in popup
         */
        jQuery(element).find('.direct-links').show();
        jQuery(element).find('.editor-addon-link-to-top').show();
        icl_editor_resize_popup_height(element, iclEditorHeight);
    }
    if (heightInitial < iclEditorHeightMin) {
        icl_editor_resize_popup_height(element, iclEditorHeight);
    }
    
    /*
     * Set CSS
     */
    jQuery(element).css('overflow', 'auto');
    jQuery(element).css('padding', '0px');

/*
     *
     * TODO REMOVE
     * SRDJAN
     * Before we calculated if popup felloff screen.
     * Popup appeals more solid when fixed to 550px which is most often width
     * of WPs editor or meta-box.
     */
//    var width = jQuery(element).width();
//    var height = jQuery(element).height();
//    var scrollHeight = jQuery(element).find('li:last-child').outerHeight();
//    alert(height);
//    alert(scrollHeight);
//    if (height < scrollHeight) {
//        alert('small');
//    }
//    var document_height = jQuery(document).height();
//    var offset = jQuery(element).offset();
//
//    if (offset.top+height > document_height) {
//        alert('off the screen');
//        var resizedHeight = Math.round(document_height-offset.top-20);
//        if (resizedHeight < 250) {
//            resizedHeight = 250;
//        }
//        jQuery(element).height(resizedHeight);
//        jQuery(element).css('height', resizedHeight+'px');
//        var scrollHeight = Math.round(resizedHeight-jQuery(element).find('.direct-links').height()-50);
//        jQuery(element).find('.scroll').css('height', scrollHeight+'px');
//    } else {
//        alert('no need for resize');
//        jQuery(element).find('.direct-links').hide();
//        jQuery(element).find('.editor-addon-link-to-top').hide();
//    }
//
//    // make sure the popup is not too wide.		
//    var screenWidth = jQuery(window).width();
//    if (offset.left + width > screenWidth) {
//        alert('too wide');
//        var resizedWidth = Math.round(screenWidth - offset.left - 20);
//        jQuery(element).height(resizedWidth).css('width', resizedWidth + 'px');
//    }
}

/**
 * Sets element width.
 */
function icl_editor_resize_popup_width(element, width) {
    jQuery(element).width(width).css('width', width + 'px');
}

/**
 * Sets element height.
 */
function icl_editor_resize_popup_height(element, height) {
    jQuery(element).height(height).css('height', height + 'px');
}
	

var keyStr = "ABCDEFGHIJKLMNOP" +
"QRSTUVWXYZabcdef" +
"ghijklmnopqrstuv" +
"wxyz0123456789+/" +
"=";
                   
function editor_decode64(input) {
    var output = "";
    var chr1, chr2, chr3 = "";
    var enc1, enc2, enc3, enc4 = "";
    var i = 0;
    
    // remove all characters that are not A-Z, a-z, 0-9, +, /, or =
    var base64test = /[^A-Za-z0-9\+\/\=]/g;
    if (base64test.exec(input)) {
        alert("There were invalid base64 characters in the input text.\n" +
            "Valid base64 characters are A-Z, a-z, 0-9, '+', '/',and '='\n" +
            "Expect errors in decoding.");
    }
    input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");
    
    do {
        enc1 = keyStr.indexOf(input.charAt(i++));
        enc2 = keyStr.indexOf(input.charAt(i++));
        enc3 = keyStr.indexOf(input.charAt(i++));
        enc4 = keyStr.indexOf(input.charAt(i++));
    
        chr1 = (enc1 << 2) | (enc2 >> 4);
        chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
        chr3 = ((enc3 & 3) << 6) | enc4;
    
        output = output + String.fromCharCode(chr1);
    
        if (enc3 != 64) {
            output = output + String.fromCharCode(chr2);
        }
        if (enc4 != 64) {
            output = output + String.fromCharCode(chr3);
        }
    
        chr1 = chr2 = chr3 = "";
        enc1 = enc2 = enc3 = enc4 = "";
    
    } while (i < input.length);
    
    return unescape(editor_utf8_decode(output));
}

function editor_utf8_decode(utftext) {
    var string = "";
    var i = 0;
    var c = c1 = c2 = 0;

    while ( i < utftext.length ) {

        c = utftext.charCodeAt(i);

        if (c < 128) {
            string += String.fromCharCode(c);
            i++;
        }
        else if((c > 191) && (c < 224)) {
            c2 = utftext.charCodeAt(i+1);
            string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
            i += 2;
        }
        else {
            c2 = utftext.charCodeAt(i+1);
            c3 = utftext.charCodeAt(i+2);
            string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
            i += 3;
        }

    }

    return string;
}

function insert_b64_shortcode_to_editor(b64_shortcode, text_area) {
    var shortcode = editor_decode64(b64_shortcode);
    if(shortcode.indexOf('[types') == 0 && shortcode.indexOf('[/types') === false) {
        shortcode += '[/types]';
    }
    
    if (text_area == 'textarea#content') {
        // the main editor
        if (window.parent.jQuery('textarea#content:visible').length) {
            // HTML editor
            window.parent.jQuery('textarea#content').insertAtCaret(shortcode);
        } else {
            // Visual editor
            window.parent.tinyMCE.activeEditor.execCommand('mceInsertContent', false, shortcode);
        }
    } else {
        // the other editor
        if (window.parent.jQuery('textarea#'+text_area+':visible').length) {
            // HTML editor
            window.parent.jQuery('textarea#'+text_area).insertAtCaret(shortcode);
        } else {
            //CodeMirror
            if(window.parent.tinyMCE==undefined){
                if (typeof HTMLCodeMirrorActive!='undefined'){
                    InsertAtCursor(shortcode, text_area);
                }else if (window.parent.cred_cred) {
                    window.parent.cred_cred.insert(shortcode);
                }else{

                }
            }else{
                window.parent.tinyMCE.execCommand('mceFocus', false, text_area);
                window.parent.tinyMCE.activeEditor.execCommand('mceInsertContent', false, shortcode);
            }
        }
    }
}

/**
 * Filtering elements from search boxes with JS
 */
function wpv_on_search_filter(el) {
    // get search text
    var searchText = jQuery(el).val();
	
    // get parent on DOM to find items and hide/show Search
    var parent = el.parentNode.parentNode;
    var searchItems = jQuery(parent).find('.group .item');
	
    jQuery(parent).find('.search_clear').css('display', (searchText == '') ? 'none' : 'inline');
	
    // iterate items and search
    jQuery(searchItems).each(function() {
        if(searchText == '' || jQuery(this).text().search(new RegExp(searchText, 'i')) > -1) {
            // alert(jQuery(this).text());
            jQuery(this).css('display', 'inline');
        } 
        else {
            jQuery(this).css('display', 'none');
        }
    });
	
    // iterate group titles and check if they have items (otherwise hide them)
	
    wpv_hide_top_groups(parent);
}

/**
 * SRDJAN
 * @TODO Check if this is cloned elsewhere
 * @TODO Appears not working
 */
function wpv_hide_top_groups(parent) {
    var groupTitles = jQuery(parent).find('.group-title');
    jQuery(groupTitles).each(function() {
        var parentOfGroup = jQuery(this).parent();
        // by default we assume that there are no children to show
        var visibleGroup = false;
        jQuery(parentOfGroup).find('.item').each(function() {
            if(jQuery(this).css('display') == 'inline') {
                visibleGroup = true;
                return false;
            }
        });
		
        if(!visibleGroup) {
            jQuery(this).css('display', 'none');
        } else {
            jQuery(this).css('display', 'block');
        }
    });
}

// clear search input
function wpv_search_clear(el) {
    var parent = el.parentNode.parentNode;
    var searchbox = jQuery(parent).find('.search_field');
    searchbox.val('');
    wpv_on_search_filter(searchbox[0]);
}

/**
 * Bind window click to auto-hide
 * 
 * This should be generic close.
 * It's used in few places
 */
function icl_editor_bind_auto_close() {
    /*
     * jQuery executes 'bind' immediatelly on click
     */
    jQuery('body').bind('click',function(e){
        
        // This is simplifyed version
        // Try to find dropdown inside clicked item.
        // If not found that means element is child of dropdown
        //        if (jQuery(e.target).find('.editor_addon_dropdown').length != 0) {
        //        }
        
        var dropdownAddField = jQuery('#add_field_popup .editor_addon_dropdown');
          
        // Exception for 'Add field' button
        if (jQuery(e.target).hasClass('wpv_add_fields_button')) {
        // Do nothing other dropdowns will take care
        //            if (dropdownAddField.css('visibility') == 'visible') {
        //            if (dropdownAddField.hasClass('icl_editor_click_binded')) {
        //                        
        //                // Hide all
        //                jQuery('.editor_addon_dropdown').css('visibility', 'hidden')
        //                .css('display', 'inline');
        //                        
        //                jQuery(this).unbind(e);
        //                    
        //            } else {
        //                dropdownAddField.addClass('icl_editor_click_binded');
        //            }
        //            }
        } else if (jQuery(e.target).parents('.editor_addon_wrapper').length < 1) {
    
            // Hide all
            jQuery('.editor_addon_dropdown').css('visibility', 'hidden')
            .css('display', 'inline');
                
            // Unbind Add field dropdown
            dropdownAddField.removeClass('icl_editor_click_binded');
                
            jQuery(this).unbind(e);
        }
    });
}