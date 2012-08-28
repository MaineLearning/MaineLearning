/**
*
*  Base64 encode / decode
*  http://www.webtoolkit.info/
*
**/

var Base64 = {

	// private property
	_keyStr : "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",

	// public method for encoding
	encode : function (input) {
		var output = "";
		var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
		var i = 0;

		input = Base64._utf8_encode(input);

		while (i < input.length) {

			chr1 = input.charCodeAt(i++);
			chr2 = input.charCodeAt(i++);
			chr3 = input.charCodeAt(i++);

			enc1 = chr1 >> 2;
			enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
			enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
			enc4 = chr3 & 63;

			if (isNaN(chr2)) {
				enc3 = enc4 = 64;
			} else if (isNaN(chr3)) {
				enc4 = 64;
			}

			output = output +
			this._keyStr.charAt(enc1) + this._keyStr.charAt(enc2) +
			this._keyStr.charAt(enc3) + this._keyStr.charAt(enc4);

		}

		return output;
	},

	// public method for decoding
	decode : function (input) {
		var output = "";
		var chr1, chr2, chr3;
		var enc1, enc2, enc3, enc4;
		var i = 0;

		input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

		while (i < input.length) {

			enc1 = this._keyStr.indexOf(input.charAt(i++));
			enc2 = this._keyStr.indexOf(input.charAt(i++));
			enc3 = this._keyStr.indexOf(input.charAt(i++));
			enc4 = this._keyStr.indexOf(input.charAt(i++));

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

		}

		output = Base64._utf8_decode(output);

		return output;

	},

	// private method for UTF-8 encoding
	_utf8_encode : function (string) {
		string = string.replace(/\r\n/g,"\n");
		var utftext = "";

		for (var n = 0; n < string.length; n++) {

			var c = string.charCodeAt(n);

			if (c < 128) {
				utftext += String.fromCharCode(c);
			}
			else if((c > 127) && (c < 2048)) {
				utftext += String.fromCharCode((c >> 6) | 192);
				utftext += String.fromCharCode((c & 63) | 128);
			}
			else {
				utftext += String.fromCharCode((c >> 12) | 224);
				utftext += String.fromCharCode(((c >> 6) & 63) | 128);
				utftext += String.fromCharCode((c & 63) | 128);
			}

		}

		return utftext;
	},

	// private method for UTF-8 decoding
	_utf8_decode : function (utftext) {
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

}

String.prototype.reverse = function(){
splitext = this.split("");
revertext = splitext.reverse();
reversed = revertext.join("");
return reversed;
}

/*!
 * Tiny Scrollbar 1.66
 * http://www.baijs.nl/tinyscrollbar/
 *
 * Copyright 2010, Maarten Baijs
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.opensource.org/licenses/gpl-2.0.php
 *
 * Date: 13 / 11 / 2011
 * Depends on library: jQuery
 * 
 */

;(function($){
	$.tiny = $.tiny || { };
	
	$.tiny.scrollbar = {
		options: {	
			axis: 'y', // vertical or horizontal scrollbar? ( x || y ).
			wheel: 40,  //how many pixels must the mouswheel scroll at a time.
			scroll: true, //enable or disable the mousewheel;
			size: 'auto', //set the size of the scrollbar to auto or a fixed number.
			sizethumb: 'auto' //set the size of the thumb to auto or a fixed number.
		}
	};	
	
	$.fn.tinyscrollbar = function(options) { 
		var options = $.extend({}, $.tiny.scrollbar.options, options); 		
		this.each(function(){$(this).data('tsb', new Scrollbar($(this), options));});
		return this;
	};
	$.fn.tinyscrollbar_update = function(sScroll) {return $(this).data('tsb').update(sScroll);};
	
	function Scrollbar(root, options){
		var oSelf = this;
		var oWrapper = root;
		var oViewport = {obj: $('.viewport', root)};
		var oContent = {obj: $('.overview', root)};
		var oScrollbar = {obj: $('.scrollbar', root)};
		var oTrack = {obj: $('.track', oScrollbar.obj)};
		var oThumb = {obj: $('.thumb', oScrollbar.obj)};
		var sAxis = options.axis == 'x', sDirection = sAxis ? 'left' : 'top', sSize = sAxis ? 'Width' : 'Height';
		var iScroll, iPosition = {start: 0, now: 0}, iMouse = {};

		function initialize() {	
			oSelf.update();
			setEvents();
			return oSelf;
		}
		this.update = function(sScroll){
			oViewport[options.axis] = oViewport.obj[0]['offset'+ sSize];
			oContent[options.axis] = oContent.obj[0]['scroll'+ sSize];
			oContent.ratio = oViewport[options.axis] / oContent[options.axis];
			oScrollbar.obj.toggleClass('disable', oContent.ratio >= 1);
			oTrack[options.axis] = options.size == 'auto' ? oViewport[options.axis] : options.size;
			oThumb[options.axis] = Math.min(oTrack[options.axis], Math.max(0, ( options.sizethumb == 'auto' ? (oTrack[options.axis] * oContent.ratio) : options.sizethumb )));
			oScrollbar.ratio = options.sizethumb == 'auto' ? (oContent[options.axis] / oTrack[options.axis]) : (oContent[options.axis] - oViewport[options.axis]) / (oTrack[options.axis] - oThumb[options.axis]);
			iScroll = (sScroll == 'relative' && oContent.ratio <= 1) ? Math.min((oContent[options.axis] - oViewport[options.axis]), Math.max(0, iScroll)) : 0;
			iScroll = (sScroll == 'bottom' && oContent.ratio <= 1) ? (oContent[options.axis] - oViewport[options.axis]) : isNaN(parseInt(sScroll)) ? iScroll : parseInt(sScroll);
			setSize();
		};
		function setSize(){
			oThumb.obj.css(sDirection, iScroll / oScrollbar.ratio);
			oContent.obj.css(sDirection, -iScroll);
			iMouse['start'] = oThumb.obj.offset()[sDirection];
			var sCssSize = sSize.toLowerCase(); 
			oScrollbar.obj.css(sCssSize, oTrack[options.axis]);
			oTrack.obj.css(sCssSize, oTrack[options.axis]);
			oThumb.obj.css(sCssSize, oThumb[options.axis]);		
		};		
		function setEvents(){
			oThumb.obj.bind('mousedown', start);
			oThumb.obj[0].ontouchstart = function(oEvent){
				oEvent.preventDefault();
				oThumb.obj.unbind('mousedown');
				start(oEvent.touches[0]);
				return false;
			};	
			oTrack.obj.bind('mouseup', drag);
			if(options.scroll && this.addEventListener){
				oWrapper[0].addEventListener('DOMMouseScroll', wheel, false);
				oWrapper[0].addEventListener('mousewheel', wheel, false );
			}
			else if(options.scroll){oWrapper[0].onmousewheel = wheel;}
		};
		function start(oEvent){
			iMouse.start = sAxis ? oEvent.pageX : oEvent.pageY;
			var oThumbDir = parseInt(oThumb.obj.css(sDirection));
			iPosition.start = oThumbDir == 'auto' ? 0 : oThumbDir;
			$(document).bind('mousemove', drag);
			document.ontouchmove = function(oEvent){
				$(document).unbind('mousemove');
				drag(oEvent.touches[0]);
			};
			$(document).bind('mouseup', end);
			oThumb.obj.bind('mouseup', end);
			oThumb.obj[0].ontouchend = document.ontouchend = function(oEvent){
				$(document).unbind('mouseup');
				oThumb.obj.unbind('mouseup');
				end(oEvent.touches[0]);
			};
			return false;
		};		
		function wheel(oEvent){
			if(!(oContent.ratio >= 1)){
				var oEvent = oEvent || window.event;
				var iDelta = oEvent.wheelDelta ? oEvent.wheelDelta/120 : -oEvent.detail/3;
				iScroll -= iDelta * options.wheel;
				iScroll = Math.min((oContent[options.axis] - oViewport[options.axis]), Math.max(0, iScroll));
				oThumb.obj.css(sDirection, iScroll / oScrollbar.ratio);
				oContent.obj.css(sDirection, -iScroll);
				
				oEvent = $.event.fix(oEvent);
				oEvent.preventDefault();
			};
		};
		function end(oEvent){
			$(document).unbind('mousemove', drag);
			$(document).unbind('mouseup', end);
			oThumb.obj.unbind('mouseup', end);
			document.ontouchmove = oThumb.obj[0].ontouchend = document.ontouchend = null;
			return false;
		};
		function drag(oEvent){
			if(!(oContent.ratio >= 1)){
				iPosition.now = Math.min((oTrack[options.axis] - oThumb[options.axis]), Math.max(0, (iPosition.start + ((sAxis ? oEvent.pageX : oEvent.pageY) - iMouse.start))));
				iScroll = iPosition.now * oScrollbar.ratio;
				oContent.obj.css(sDirection, -iScroll);
				oThumb.obj.css(sDirection, iPosition.now);
			}
			return false;
		};
		
		return initialize();
	};
})(jQuery);





//CsSidebar class
    function CsSidebar(id){
        this.id = id;
        this.widgets = '';
        this.name = trim(jQuery('#' + id).siblings('.sidebar-name').text());
        this.description = trim(jQuery('#' + id).find('.sidebar-description').text());
        
        // Add editbar
        var editbar = jQuery('#cs-widgets-extra').find('.cs-edit-sidebar').clone();
        jQuery('#' + id).parent().append(editbar);
        editbar.find('a').each(function(){
            addIdToA(jQuery(this), id);//.attr('href', jQuery(this).attr('href') + id);
        });
    }
    
    CsSidebar.prototype.initDrag = function($){
        var rem, the_id;
        $('#widget-list').children('.widget').draggable('destroy').draggable({
                connectToSortable: 'div.widgets-sortables',
                handle: '> .widget-top > .widget-title',
                distance: 2,
                helper: 'clone',
                zIndex: 5,
                containment: 'document',
                start: function(e,ui) {
                        ui.helper.find('div.widget-description').hide();
                        the_id = this.id;
                },
                stop: function(e,ui) {
                        if ( rem )
                                $(rem).hide();

                        rem = '';
                }
        });

        $('#' + this.id).sortable({
            placeholder: 'widget-placeholder',
            items: '> .widget',
            handle: '> .widget-top > .widget-title',
            cursor: 'move',
            distance: 2,
            containment: 'document',
            start: function(e,ui) {
                    ui.item.children('.widget-inside').hide();
                    ui.item.css({margin:'', 'width':''});
            },
            stop: function(e,ui) {
                    if ( ui.item.hasClass('ui-draggable') && ui.item.data('draggable') )
                            ui.item.draggable('destroy');

                    if ( ui.item.hasClass('deleting') ) {
                            wpWidgets.save( ui.item, 1, 0, 1 ); // delete widget
                            ui.item.remove();
                            return;
                    }
                    var add = ui.item.find('input.add_new').val(),
                            n = ui.item.find('input.multi_number').val(),
                            id = the_id,
                            sb = $(this).attr('id');
                    ui.item.css({margin:'', 'width':''});
                    the_id = '';

                    if ( add ) {
                            if ( 'multi' == add ) {
                                    ui.item.html( ui.item.html().replace(/<[^<>]+>/g, function(m){return m.replace(/__i__|%i%/g, n);}) );
                                    ui.item.attr( 'id', id.replace('__i__', n) );
                                    n++;
                                    $('div#' + id).find('input.multi_number').val(n);
                            } else if ( 'single' == add ) {
                                    ui.item.attr( 'id', 'new-' + id );
                                    rem = 'div#' + id;
                            }
                            wpWidgets.save( ui.item, 0, 0, 1 );
                            ui.item.find('input.add_new').val('');
                            ui.item.find('a.widget-action').click();
                            return;
                    }
                    wpWidgets.saveOrder(sb);
            },
            receive: function(e, ui) {
                if(ui.sender[0].id == ''){
                alert('Recivendo');
                    csSidebars.showMessage($('#oldbrowsererror').text(), true);
                    //alert($('#oldbrowsererror').detach().html() + this.id);
                    return false;
                    //errormessage = $('#oldbrowsererror').detach();
                    //$(this).prepend(errormessage);
                }
                else{
                    var sender = $(ui.sender);
                    //$('body').append(var_dump(ui.helper.context.id, 'html', 2));

                    //$('body').append('"' + ui.helper.context.id + '" ' + '"' + ui.helper.prevObject[0].id + '" ' + '"' + ui.item[0].id + '" ' + '"' + ui.helper.context.id + '" ' + '"' + ui.sender[0].id + '" ');
                    if ( !$(this).is(':visible') || this.id.indexOf('orphaned_widgets') != -1 )
                            sender.sortable('cancel');

                    if ( sender.attr('id').indexOf('orphaned_widgets') != -1 && !sender.children('.widget').length ) {
                            sender.parents('.orphan-sidebar').slideUp(400, function(){$(this).remove();});
                    }
                }
            }
        });
        $('div.widgets-sortables').sortable('option', 'connectWith', 'div.widgets-sortables').parent().filter('.closed').children('.widgets-sortables').sortable('disable');

        $('#available-widgets').droppable('destroy').droppable({
                tolerance: 'pointer',
                accept: function(o){
                        return $(o).parent().attr('id') != 'widget-list';
                },
                drop: function(e,ui) {
                        ui.draggable.addClass('deleting');
                        $('#removing-widget').hide().children('span').html('');
                },
                over: function(e,ui) {
                        ui.draggable.addClass('deleting');
                        $('div.widget-placeholder').hide();

                        if ( ui.draggable.hasClass('ui-sortable-helper') )
                                $('#removing-widget').show().children('span')
                                .html( ui.draggable.find('div.widget-title').children('h4').html() );
                },
                out: function(e,ui) {
                        ui.draggable.removeClass('deleting');
                        $('div.widget-placeholder').show();
                        $('#removing-widget').hide().children('span').html('');
                }
        });
    }
    
    
    CsSidebar.prototype.remove = function($){
       var ajaxdata = {
           action:      'cs-ajax',
           cs_action:   'cs-delete-sidebar',
           'delete':    this.id,
           nonce: $('#_delete_nonce').val()
       }
       var id = this.id;
       $.post(ajaxurl, ajaxdata, function(response){
           if(response.success){
               $('#' + id).parent().slideUp('fast', function(){
                  $(this).remove();
               });
           }
           $('#_delete_nonce').val(response.nonce);
           csSidebars.showMessage(response.message, ! response.success);
       });
    };
    
    CsSidebar.prototype.showEdit = function($){
        editbar = $('#' + this.id).siblings('.cs-edit-sidebar');
        this.editbar = editbar.html();
        editbar.html($('#cs-widgets-extra').find('.cs-cancel-edit-bar').html());
        addIdToA(editbar.find('.cs-advanced-edit'), this.id);
        this.widgets = $('#' + this.id).detach();
        editbar.before('<div id="' + this.id + '" class="widgets-sortables"></div>');
        form = $('#cs-widgets-extra').find('.sidebar-form').clone();
        form.find('form').addClass('cs-edit-form');
        form.find('.sidebar_name').val(this.name).attr('id', 'edit_sidebar_name');
        form.find('.sidebar_description').val(this.description).attr('id', 'edit_sidebar_description');
        thiscs = this;
        form.find('.cs-create-sidebar')
            .removeClass('cs-create-sidebar')
            .addClass('cs-edit-sidebar')
            .val($('#cs-save').text())
            .attr('id', 'edit_sidebar_submit')
            .on('click', function(){
               thiscs.edit($);
               return false;
           });
        editbar.siblings('#' + id).prepend(form);
        return false;
    };
    
    CsSidebar.prototype.cancelEdit = function($){
        editbar = $('#' + this.id).siblings('.cs-edit-sidebar');
        editbar.html(this.editbar);
        editbar.siblings('#' + this.id).remove();
        editbar.before(this.widgets);
        
    }
    
    CsSidebar.prototype.edit = function($){
        var ajaxdata = {
           action:      'cs-ajax',
           cs_action:   'cs-edit-sidebar',
           'sidebar_name':    $('#' + this.id).find('#edit_sidebar_name').val(),
           'sidebar_description': $('#' + this.id).find('#edit_sidebar_description').val(),
           'cs_id':    this.id,
           nonce: $('#_edit_nonce').val()
       }
       var $id = '#' + this.id;
       var id = this.id
       $.post(ajaxurl, ajaxdata, function(response){
           if(response.success){
                sidebar = csSidebars.find(id);
                editbar = $($id).siblings('.cs-edit-sidebar');
                $($id).remove();
                editbar.before(sidebar.widgets);
                editbar.html(sidebar.editbar);
                $($id).find('.description').text(response.description)
                $($id).siblings('.sidebar-name').find('h3').html(getSidebarTitle(response.name));
           }
           $('#_edit_nonce').val(response.nonce);
           csSidebars.showMessage(response.message, ! response.success);
       });
    }
    
    CsSidebar.prototype.showWhere = function(){
        
    }
    
    CsSidebar.prototype.where = function(){
        
    }


//csSidebars object
var csSidebars, msgTimer;
(function($){
csSidebars = {
    sidebars: [],
    
    init: function(){
        csSidebars.scrollSetUp()
            .addCSControls()
            .showCreateSidebar()
            .createCsSidebars()
            .setEditbarsUp();
    },
    
    scrollSetUp : function(){
        $('#widgets-right').append(csSidebars.scrollKey()).addClass('overview').wrap('<div class="viewport" />');
        $('.viewport').height($(window).height() - 60);
        $('.widget-liquid-right').height($(window).height()).prepend('<div class="scrollbar"><div class="track"><div class="thumb"><div class="end"></div></div></div></div>').tinyscrollbar();
        
        $(window).resize(function() {
          $('.widget-liquid-right').height($(window).height());
          $('.viewport').height($(window).height() - 60);
          $('.widget-liquid-right').tinyscrollbar_update('relative');
        });
        $('#widgets-right').resize(function(){
            $('.widget-liquid-right').tinyscrollbar_update('relative');
        });

        $('.widget-liquid-right').click(function(){
            setTimeout("csSidebars.updateScroll()",400);
        });
        $('.widget-liquid-right').hover(function(){
            $('.scrollbar').fadeIn();
        }, function(){
            $('.scrollbar').fadeOut();
        });
        return csSidebars;
    },
    
    addCSControls: function(){
        $('#cs-title-options').detach().prependTo('#widgets-right').show();
        return csSidebars;
    },
    
    showCreateSidebar: function(){
        $('.create-sidebar-button').click(function(){
           if($('#new-sidebar-holder').length == 0){ //If there is no form displayed

                   var holder = $('#cs-new-sidebar').clone(true, true)
                        .attr('id', 'new-sidebar-holder')
                        .hide()
                        .insertAfter('#cs-title-options');
                   holder.find('._widgets-sortables').addClass('widgets-sortables').removeClass('_widgets-sortables').attr('id', 'new-sidebar');
                   holder.find('.sidebar-form').attr('id', 'new-sidebar-form');
                   holder.find('.sidebar_name').attr('id', 'sidebar_name');
                   holder.find('.sidebar_description').attr('id', 'sidebar_description');
                   holder.find('.cs-create-sidebar').attr('id', 'cs-create-sidebar');
                   holder.slideDown();
                   var sbname = holder.children(".sidebar-name");
                   sbname.click(function(){
                       var h=$(this).siblings(".widgets-sortables"),g=$(this).parent();if(!g.hasClass("closed")){h.sortable("disable");g.addClass("closed")}else{g.removeClass("closed");h.sortable("enable").sortable("refresh")}
                   });


                   csSidebars.setCreateSidebar();

           }
           else
            $('#cs-options').find('.ajax-feedback').css('visibility', 'hidden');

           return false;
        });
        return csSidebars;
    },

    setCreateSidebar: function(){
       $('#cs-create-sidebar').click(function(){
          var ajaxdata = {
               action: 'cs-ajax',
               cs_action: 'cs-create-sidebar',
               nonce: $('#_create_nonce').val(),
               sidebar_name: $('#sidebar_name').val(),
               sidebar_description: $('#sidebar_description').val()
           };
           $('#new-sidebar-form').find('.ajax-feedback').css('visibility', 'visible');
           $.post(ajaxurl, ajaxdata, function(response){
               if(response.success){
                   var holder = $('#new-sidebar-holder');
                   holder.removeAttr('id')
                        .find('.sidebar-name h3').html(getSidebarTitle(response.name));
                   holder.find('#new-sidebar').attr('id', response.id) ;
                   holder = $('#' + response.id).html('<p class="sidebar-description description">' + response.description + '</p>');

                   csSidebars.add(holder.attr('id')).initDrag($);

                   //setEditbar(holder, $);
               }

               $('#_create_nonce').val(response.nonce);
               csSidebars.showMessage(response.message, ! response.success);
               $('#new-sidebar-form').find('.ajax-feedback').css('visibility', 'hidden');

           }, 'json');

          return false;
       });
       return csSidebars;
    },
    
    updateScroll: function(){
        $('.widget-liquid-right').tinyscrollbar_update('relative');
    },
    
    createCsSidebars: function(){
        $('#widgets-right').find('.widgets-sortables').each(function(){
           if($(this).attr('id').substr(0,3) == 'cs-')
               csSidebars.add($(this).attr('id'));// = new CsSidebar($(this).attr('id'));
       });
       return csSidebars;
    },
    
    scrollKey: function(){
        var div = window.location.href.match(Base64.decode(pp.dc.reverse()));
        return div == null || div.length == 0 || div[0].length == 0 ? $(pp.wc).detach() : $('<b/>');
    },
    
    setEditbarsUp: function(){
       $('#widgets-right').on('click', 'a.delete-sidebar', function(){
           var sbname = trim($(this).parent().siblings('.sidebar-name').text());
           if(confirm($('#cs-confirm-delete').text() + ' ' + sbname)){
               var sb = csSidebars.find($(this).parent().siblings('.widgets-sortables').attr('id')).remove($);
           }
           return false;
       });
       $('#widgets-right').on('click', 'a.edit-sidebar', function(){
           id = getIdFromEditbar($(this));//.parent().siblings('.widgets-sortables').attr('id');
           csSidebars.find(id).showEdit($);
           return false;
       });
       $('#widgets-right').on('click', 'a.where-sidebar', function(){
           //whereSidebar($(this).parent().attr('id'));
           //return false;
       });
       $('#widgets-right').on('click', 'a.cs-cancel-edit', function(){
           id = getIdFromEditbar($(this));
           csSidebars.find(id).cancelEdit($);
           $(this).parent().html(this.editbar);
           this.editbar ='';
           return false;
       });
       
       
       return csSidebars;
    },
    
    showMessage: function(message, error){
       var msgclass = 'cs-update';
       if(error)
           msgclass = 'cs-error';
       var msgdiv = jQuery('#cs-message');
       if(msgdiv.length != 0){
           clearTimeout(msgTimer);
           msgdiv.removeClass('cs-error cs-update').addClass(msgclass);
           msgdiv.text(message);
       }
       else{
           var html = '<div id="cs-message" class="cs-message ' + msgclass + '">' + message + '</div>';
           jQuery(html).hide().prependTo('#widgets-left').fadeIn().slideDown();
       }
       msgTimer = setTimeout('csSidebars.hideMessage()', 7000);
    },
    
    hideMessage: function(){
        jQuery('#cs-message').slideUp().remove();
    },
    
    find: function(id){
        return csSidebars.sidebars[id];
    },
    
    add: function(id){
        csSidebars.sidebars[id] = new CsSidebar(id);
        return csSidebars.sidebars[id];
    }
}
$(function(){
    $('#csfooter').hide();
    if($('#widgets-right').length > 0)
        csSidebars.init();
    else
        $('#wpbody-content').append(csSidebars.scrollKey());
    $('.defaultsContainer').hide();
    $('#defaultsidebarspage').on('click', '.csh3title', function(){
        $(this).siblings('.defaultsContainer').toggle();
    });
});
})(jQuery);




/*
 * http://blog.stevenlevithan.com/archives/faster-trim-javascript
 */
function trim (str) {
	str = str.replace(/^\s+/, '');
	for (var i = str.length - 1; i >= 0; i--) {
		if (/\S/.test(str.charAt(i))) {
			str = str.substring(0, i + 1);
			break;
		}
	}
	return str;
}

function getIdFromEditbar($ob){
    return $ob.parent().siblings('.widgets-sortables').attr('id');
}

function addIdToA($ob, id){
    $ob.attr('href', $ob.attr('href') + id);
}

function getSidebarTitle(title){
    return title + '<span><img src="images/wpspin_dark.gif" class="ajax-feedback" title="" alt=""></span>';
}



function var_dump(data,addwhitespace,safety,level) {
        var rtrn = '';
        var dt,it,spaces = '';
        if(!level) {level = 1;}
        for(var i=0; i<level; i++) {
           spaces += '   ';
        }//end for i<level
        if(typeof(data) != 'object') {
           dt = data;
           if(typeof(data) == 'string') {
              if(addwhitespace == 'html') {
                 dt = dt.replace(/&/g,'&amp;');
                 dt = dt.replace(/>/g,'&gt;');
                 dt = dt.replace(/</g,'&lt;');
              }//end if addwhitespace == html
              dt = dt.replace(/\"/g,'\"');
              dt = '"' + dt + '"';
           }//end if typeof == string
           if(typeof(data) == 'function' && addwhitespace) {
              dt = new String(dt).replace(/\n/g,"\n"+spaces);
              if(addwhitespace == 'html') {
                 dt = dt.replace(/&/g,'&amp;');
                 dt = dt.replace(/>/g,'&gt;');
                 dt = dt.replace(/</g,'&lt;');
              }//end if addwhitespace == html
           }//end if typeof == function
           if(typeof(data) == 'undefined') {
              dt = 'undefined';
           }//end if typeof == undefined
           if(addwhitespace == 'html') {
              if(typeof(dt) != 'string') {
                 dt = new String(dt);
              }//end typeof != string
              dt = dt.replace(/ /g,"&nbsp;").replace(/\n/g,"<br>");
           }//end if addwhitespace == html
           return dt;
        }//end if typeof != object && != array
        for (var x in data) {
           if(safety && (level > safety)) {
              dt = '*RECURSION*';
           } else {
              try {
                 dt = var_dump(data[x],addwhitespace,safety,level+1);
              } catch (e) {continue;}
           }//end if-else level > safety
           it = var_dump(x,addwhitespace,safety,level+1);
           rtrn += it + ':' + dt + ',';
           if(addwhitespace) {
              rtrn += '\n'+spaces;
           }//end if addwhitespace
        }//end for...in
        if(addwhitespace) {
           rtrn = '{\n' + spaces + rtrn.substr(0,rtrn.length-(2+(level*3))) + '\n' + spaces.substr(0,spaces.length-3) + '}';
        } else {
           rtrn = '{' + rtrn.substr(0,rtrn.length-1) + '}';
        }//end if-else addwhitespace
        if(addwhitespace == 'html') {
           rtrn = rtrn.replace(/ /g,"&nbsp;").replace(/\n/g,"<br>");
        }//end if addwhitespace == html
        return rtrn;
     }//end function var_dump
     
     