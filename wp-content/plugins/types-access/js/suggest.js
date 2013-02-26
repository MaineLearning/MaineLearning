jQuery(document).ready(function(){

    (function( $ ) {
        $.typesSuggest = function(object, ajax_action, callback_select) {
            var container = object;
            var input = object.find('.input');
            var dropdown = object.find('.dropdown');
            var img = object.find('.img-waiting');
            var action = ajax_action;
            var callback = callback_select;
            var selected_values = {};
            attach_listener();
    
            function attach_listener(){
                var searchTimer;
                input.keydown( function(e){
                    var q = jQuery(this).val();
                    if( 13 == e.which ) {            
                        update(q);
                        return false;
                    }
                    if( e.keyCode == 40 && dropdown.length){
                        dropdown.focus();
                    } else if( e.which >= 32 && e.which <=127 || e.which == 8) {            
                        //                        jQuery('.nf').remove();
                        if ( searchTimer ) clearTimeout(searchTimer);
                        searchTimer = setTimeout(function(){
                            update(q);
                        }, 400);
                    }
                }).attr('autocomplete','off');
            
                select_listener();
            
                input.focus(function(){
                    toggle_dropdown(true);    
                }).blur(function(){
                    toggle_dropdown(false);
                });
            }
            
            function toggle_dropdown(toggle) {
                if (toggle) {
                    dropdown.css('visibility', 'visible');
                    container.find('.toggle').show();
                } else {
                    setTimeout(function(){
                        if (!dropdown.is(':focus') ){
                            dropdown.css('visibility', 'hidden');
                            container.find('.toggle').hide();
                        }
                    }, 500);
                }
            }

            function update(q) {
                var params, minSearchLength = 2, dropdownE = dropdown, imgE = img;
                if(q.length < minSearchLength ){
                    return;  
                } 
                params = {
                    'action': action,
                    'q': q
                };
                imgE.show();
                jQuery.ajax({
                    type: 'POST', 
                    url: ajaxurl, 
                    data: params,
                    dataType: 'json', 
                    success: function(response) {
                        imgE.hide();
                        dropdownE.empty();
                        var count = 0;
                        var options = '';
                        for (var key in response) {
                            count++;
                            options = options+'<option value="'+key+'">'+response[key]+'</option>';
                        }
                        if (count > 0) {
                            var resize = count;
                            if (count > 5) {
                                resize = 5;
                            }
                            dropdownE.append(options).attr('size', resize)
                            .css('visibility', 'visible')
                            .find('option:first-child')
                            .attr('selected', 'selected');
                        } else {
                            dropdownE.css('visibility', 'hidden');
                        }
                    }
                });
            }
        
            function select_listener(){
                dropdown.live('change', function(){
                    select(jQuery(this).find(':selected'));
                });
                dropdown.live('keydown', function(e){
                    if(e.which == 13){
                        select(jQuery(this).find(':selected'));
                        e.preventDefault();
                    }
                });
                return;
            }
        
            function select(object){
                callback(object.val(), object.text(), container);
            }
        
        }
        
        $.fn.typesSuggest = function(ajax_action, callback_select) {
            this.each(function() {
                new $.typesSuggest(jQuery(this), ajax_action, callback_select);
            });
        //            return this;
        };
    })( jQuery );

    typesSuggestMarkAsSuggested('.wpcf-access-user-list input:hidden', 'td');
    jQuery('.types-suggest-user').typesSuggest('wpcf_access_suggest_user', wpcfAccessSuggest);
    jQuery('.types-suggest').find('.confirm').click(function(){
        var container = jQuery(this).parents('.types-suggest');
        var name = container.parents('td').find('.wpcf-access-name-holder').val()+'[users][]';
        var selected = container.find('.dropdown :selected');
        var value = selected.val();
        var text = selected.text();
        container.find('.dropdown').css('visibility', 'hidden');
        container.find('.toggle').hide();
        if (typeof value == 'undefined') {
            return false;
        }
        if (typesSuggestIsSelected(value, container.attr('id'))) {
            return false;
        }
        var html = '<div class="wpcf-access-remove-user-wrapper"><a href="javascript:void(0);" class="wpcf-access-remove-user">&nbsp;</a><input type="hidden" name="'+name+'" value="' + value + '" />' + text + '</div>';
        container.parent().find('.wpcf-access-user-list').append(html);
        wpcfAccessDependencyAddUser(jQuery(this), html);
    });
    jQuery('.types-suggest').find('.cancel').click(function(){
        var container = jQuery(this).parents('.types-suggest');
        container.find('.dropdown').css('visibility', 'hidden');
        container.find('.toggle').hide();
    });
    jQuery('.wpcf-access-remove-user').live('click', function(){
        wpcfAccessDependencyRemoveUser(jQuery(this));
        jQuery(this).parent().fadeOut(function(){
            jQuery(this).remove();
        });
    });
});

function wpcfAccessSuggest(value, text, container) {}

function typesSuggestMarkAsSuggested(selector, parent) {
    jQuery(selector).each(function(){
        var id = jQuery(this).parents(parent).find('.types-suggest').attr('id');
        typesSuggestIsSelected(jQuery(this).val(), id);
    });
}

function typesSuggestIsSelected(value, id) {
    var store = value + id;
    if (typeof typesSuggestIsSelected.selected == 'undefined') {
        typesSuggestIsSelected.selected = new Array;
    }
    if (jQuery.inArray(store, typesSuggestIsSelected.selected) == -1) {
        typesSuggestIsSelected.selected.push(store);
        return false;
    }
    return true;
}

function typesSuggestUnMark(value, id) {
    var store = value + id;
    if (typeof typesSuggestIsSelected.selected == 'undefined') {
        typesSuggestIsSelected.selected = new Array;
    }
    if (jQuery.inArray(store, typesSuggestIsSelected.selected) != -1) {
        var index = typesSuggestIsSelected.selected.indexOf(store);
        typesSuggestIsSelected.selected.splice(index);
    }
}


function wpcfAccessDependencyAddUser(object, html) {
    var table = object.parents('table');
    var cap = object.parents('td').find('.wpcf-access-name-holder').data('wpcfaccesscap');
    var caps = new Array();
    
    if (typeof window['wpcf_access_dep_true_'+cap] != 'undefined') {
        jQuery.each(window['wpcf_access_dep_true_'+cap], function(index, value){
            table.find('.wpcf-access-name-holder[data-wpcfaccesscap="'+value+'"]').each(function(){
                var td = jQuery(this).parents('td');
                var name_holder = td.find('.wpcf-access-name-holder');
                var cap_new = name_holder.data('wpcfaccesscap');
                var user_list = td.find('.wpcf-access-user-list');
                var user_id = jQuery(html).find('input').attr('value');
                var insert_html = jQuery(html);
                var name = name_holder.val();
                var duplicate = user_list.find('input[value="'+user_id+'"]');
                insert_html.find('input').attr('name', name+'[users][]');
                if (duplicate.length < 1) {
                    caps.push(cap_new);
                    user_list.append(insert_html); 
                }
            });
        });
    }
    wpcfAccessDependencyMessageShow(object, cap, caps, true);
}

function wpcfAccessDependencyRemoveUser(object) {
    var table = object.parents('table');
    var user_id = object.parent().find('input').val();
    var td = object.parents('td');
    var name_holder = td.find('.wpcf-access-name-holder');
    var cap = name_holder.data('wpcfaccesscap');
    var caps = new Array();
    var container = td.find('.types-suggest');
    
    if (typeof window['wpcf_access_dep_false_'+cap] != 'undefined') {
        jQuery.each(window['wpcf_access_dep_false_'+cap], function(index, value){
            table.find('.wpcf-access-name-holder[data-wpcfaccesscap="'+value+'"]').each(function(){
                var td_new = jQuery(this).parents('td');
                var found = td_new.find('.wpcf-access-remove-user-wrapper').find('input[value="'+user_id+'"]');
                if (found.length > 0) {
                    caps.push(value);
                    found.parent().remove();
                }
                typesSuggestUnMark(user_id, container.attr('id'));

            });
        });
    }
    wpcfAccessDependencyMessageShow(object, cap, caps, false);
}