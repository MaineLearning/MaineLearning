function getClientWidth() {return document.compatMode=='CSS1Compat' && !window.opera?document.documentElement.clientWidth:document.body.clientWidth;}
function getClientHeight() {return document.compatMode=='CSS1Compat' && !window.opera?document.documentElement.clientHeight:document.body.clientHeight;}
jQuery(document).ready(function($) {
    //0 means disabled; 1 means enabled;
    var popupStatus = 0;
    function loadPopup(){
        //loads popup only if it is disabled
        if(popupStatus==0){
            $("#backgroundPopup").css("opacity", "0.7");
            $("#backgroundPopup").fadeIn("fast");
            $("#popupRoles").fadeIn("fast");
            popupStatus = 1;
        }
    }
    function disablePopup(){
        if(popupStatus==1){
            $("#backgroundPopup").fadeOut("fast");
            $("#popupRoles").fadeOut("fast");
            popupStatus = 0;
        }
    }
    function centerPopup(){
        //request data for centering
        var windowWidth = getClientWidth();
        var windowHeight = getClientHeight();
        var popupHeight = $("#popupRoles").height();
        var popupWidth = $("#popupRoles").width();
        //centering
        var scroll = $(window).scrollTop();
        $("#popupRoles").css({
            "position": "absolute",
            "top": ($('#container').height() + $("#popupRoles").height()/2)/2 + scroll,
            "left": ($(window).width() - $("#popupRoles").width())/2 
        });
        $("#backgroundPopup").css("height", windowHeight);
    }
   
    $('a.change_role').live('click', function(a) {
        a.preventDefault();
        var user_id = $(this).attr('rel');
        
        $.ajax({
            type: 'GET',
            url: ajaxurl,
            data: {
                user: user_id,
                action: 'bp_gtm_show_role_ajax'
            },
            success: function(data) {
                $('div#popupRoles').html(data);
                
            }
        });
        
        centerPopup();
        loadPopup();
        

    });
    
    $('a#change_role').live('click', function(e) {
        e.preventDefault();
        var role_id = $('select#role_name').val();
        var role_name = $('select#role_name option:selected').text();
        var new_user_id = $(this).attr('rel');
        
        if(role_id == '0'){
            alert(bp_gtm_strings.role_please_select);
        }else{
            $.ajax({
                type: 'GET',
                url: ajaxurl,
                data: {
                    user: new_user_id,
                    role: role_id,
                    action: 'bp_gtm_change_role_ajax'
                },
                success: function(data) {
                    if(data == '1'){
                        $('span.popupReport').fadeIn('fast').html('<span class="response1">'+bp_gtm_strings.role_changed+'</span>');
                        $('#'+new_user_id+' .td-title:last').text(role_name);
                    }else{
                        $('span.popupReport').fadeIn('fast').html('<span class="response0">'+bp_gtm_strings.role_error_again+'</span>');
                    }
                    setTimeout("jQuery('span.popupReport span').fadeOut('fast')", 2500);
                }
            });
        }
    });
    //CLOSING POPUP
    $("a#popupRolesClose").live('click', function() {disablePopup();});
    $("div#backgroundPopup").click(function(){disablePopup();});
    //Press Escape
    $('body').keyup(function(e){
        if(e.keyCode==27 && popupStatus==1) disablePopup();
    });
    
});
