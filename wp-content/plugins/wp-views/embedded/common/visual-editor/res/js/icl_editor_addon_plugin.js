jQuery(document).ready(function(){
    // Set active editor
    window.wpcfActiveEditor = false;
    jQuery('.wpcf-wysiwyg .editor_addon_wrapper .item, #postdivrich .editor_addon_wrapper .item').click(function(){
        window.wpcfActiveEditor = jQuery(this).parents('.wpcf-wysiwyg, #postdivrich').find('textarea').attr('id');
    });
});


function icl_editor_add_menu(c, m, icl_editor_menu) {
    Array.prototype.isKey = function(){
        for(i in this){
            if(i === arguments[0])
                return true;
        };
        return false;
    };
    
    var sub_menus = new Array();
    for (var index = 0; index < icl_editor_menu.length; index++) {
        
        // Set callback function
        var fn = icl_editor_menu[index][1];

        if (icl_editor_menu[index][2] != "") {
            // a sub menu
            
            
            if (sub_menus.isKey(icl_editor_menu[index][2])) {
                sub = sub_menus[icl_editor_menu[index][2]];
            } else {
                // Create a sub menu/s
                parts = icl_editor_menu[index][2].split('-!-');
                sub = m;
                name = '';
                for (var part = 0; part < parts.length; part++) {
                    if (name == '') {
                        name = parts[part];
                    } else {
                        name += '-!-' + parts[part];
                    }
                    if (sub_menus.isKey(name)) {
                        sub = sub_menus[name];
                    } else {
                        sub = sub.addMenu({
                            title : parts[part]
                        });
                        sub_menus[name] = sub;
                    }
                }
            }

            sub.add({
                title : icl_editor_menu[index][0],
                onclick : eval(fn)
            });
            
        } else {
            m.add({
                title : icl_editor_menu[index][0],
                onclick : eval(fn)
            });
        }
    }

//    return c;
}

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

jQuery(window).load(function(){
    jQuery('.editor_addon_wrapper img').click(function(e){
        if (jQuery(this).parent().find('.editor_addon_dropdown').css('visibility') == 'hidden') {
            // Close others possibly opened
            jQuery('.editor_addon_dropdown').css('visibility', 'hidden').hide().css('display', 'inline');
            jQuery(this).parent().find('.editor_addon_dropdown').css('visibility', 'visible').show().css('display', 'inline');
            jQuery(document.body).bind('click',function(e){
                if (jQuery(e.target).parents('.editor_addon_wrapper').length < 1) {
                    jQuery('.editor_addon_dropdown').css('visibility', 'hidden').hide().css('display', 'inline');
                    jQuery(this).unbind(e);
                }
            });
        } else {
            jQuery('.editor_addon_dropdown').css('visibility', 'hidden').hide().css('display', 'inline');
        }
        // Bind close on iFrame click (it's loaded now)
        jQuery('#content_ifr').contents().bind('click', function(e) {
            jQuery('.editor_addon_dropdown').css('visibility', 'hidden').hide().css('display', 'inline');
        });
        // Bind Escape
        jQuery(document).bind('keyup', function(e) {
            if (e.keyCode == 27) {
                jQuery('.editor_addon_dropdown').css('visibility', 'hidden').hide().css('display', 'inline');
                jQuery(this).unbind(e);
            }
        });
    });
    jQuery('.editor_addon_wrapper .item, .editor_addon_dropdown .close').click(function(e){
        jQuery('.editor_addon_dropdown').css('visibility', 'hidden').hide().css('display', 'inline');
    });
    // Resize dropdowns if necessary (in #media-buttons)
    jQuery('#media-buttons .editor_addon_dropdown, #wp-content-media-buttons .editor_addon_dropdown').each(function(){
        var width = jQuery(this).width();
        var height = jQuery(this).height();
        var screenHeight = jQuery(window).height();
        var offset = jQuery(this).offset();
        
        if (offset.top+height > screenHeight) {
            var resizedHeight = Math.round(screenHeight-offset.top-20);
            if (resizedHeight < 200) {
                resizedHeight = 200;
            }
            jQuery(this).height(resizedHeight);
            jQuery(this).css('height', resizedHeight+'px');
            var scrollHeight = Math.round(resizedHeight-jQuery(this).find('.direct-links').height()-50);
            jQuery(this).find('.scroll').css('height', scrollHeight+'px');
        } else {
            jQuery(this).find('.direct-links').hide();
            jQuery(this).find('.editor-addon-link-to-top').hide();
        }
    //        jQuery(this).find('.scroll').jScrollPane();
    });
    // For hidden in Meta HTML set scroll when visible
    jQuery('#wpv_layout_meta_html_admin_show a, #wpv_filter_meta_html_admin_show a').click(function(){
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
    // Set Meta HTML dropdown to insert there
    window.wpcfInsertMetaHTML = false;
    jQuery('#wpv_layout_meta_html_admin_edit .item, #wpv_filter_meta_html_admin_edit .item').click(function(){
        window.wpcfInsertMetaHTML = jQuery(this).parents('.editor_addon_wrapper').parent().find('textarea').attr('id');
    });
    // Direct links
    jQuery('.editor-addon-top-link').bind('click', function(){
        //        var api = jQuery(this).parents('.editor_addon_dropdown').find('.scroll').data('jsp');
        //        if (typeof api != 'undefined') {
        //            var wpcfScrollToElement = jQuery(this).attr('id')+'-target';
        //            api.scrollToElement(jQuery('#'+wpcfScrollToElement).parent(), true, true);
        //        }
        // get position of elements
        var positionNested = jQuery('#'+jQuery(this).attr('id')+'-target').offset();
        var positionParent = jQuery('#'+jQuery(this).attr('id')+'-target').parent().parent().offset();
        if (positionParent.top > positionNested.top) {
            var scrollTo = positionParent.top - positionNested.top;
        } else {
            var scrollTo = positionNested.top - positionParent.top;
        }
        jQuery(this).parents('.editor_addon_dropdown').find('.scroll').animate({scrollTop:Math.round(scrollTo)}, 'fast');
        return false;
    });
//    jQuery('.editor-addon-link-to-top').click(function(){
//        var api = jQuery(this).parents('.editor_addon_dropdown').find('.scroll').data('jsp');
//        var scrollToElement = jQuery(this).parents('.editor_addon_dropdown').find('.group');
//        api.scrollToElement(scrollToElement, true, true);
//        return false;
//    });
});

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
    
    return unescape(output);
}

function insert_b64_shortcode_to_editor(b64_shortcode, text_area) {
    var shortcode = editor_decode64(b64_shortcode);
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
            // Visual editor
            window.parent.tinyMCE.execCommand('mceFocus', false, text_area);
            window.parent.tinyMCE.activeEditor.execCommand('mceInsertContent', false, shortcode);
        }
    }
}