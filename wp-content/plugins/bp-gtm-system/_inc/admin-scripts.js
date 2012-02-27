jQuery(document).ready(function($){

    $('.bp_gtm_allgroups').change(function(){
        if($(this).attr('checked')){
            $('#the-list :checkbox, .bp_gtm_allgroups').attr('checked','checked' );
        } else {
            $('#the-list :checkbox, .bp_gtm_allgroups').removeAttr('checked');
        }
    });


    $('input:checkbox.bp_gtm_groups').change( function(){
        if($('input:checkbox.bp_gtm_groups:checked').size() != $('input:checkbox.bp_gtm_groups').size()){
            $('.bp_gtm_allgroups').removeAttr('checked');
        } else {
            $('.bp_gtm_allgroups').attr('checked', 'checked');
        }
    });
    
    $("a#role-open").live('click', function(eopen){
        var button = $(this);
        var role_id = button.attr('rel');
        button.toggleClass('button-primary');
        eopen.preventDefault();
        $("#box-"+role_id).toggle();
        return false;
    });
   
    $("a#role-delete").live('click', function(edelete){
        edelete.preventDefault();
        var button = $(this);
        var role_id = button.attr('rel');
        $.ajax({
            type: 'GET',
            url: ajaxurl,
            data: {
                deleteID: role_id,
                action: 'bp_gtm_delete_def_roles'
            },
            success: function(data){
                if(data == '1'){
                    $('div.def_roles li#li-'+role_id).remove();
                    $('div#box-'+role_id).parent().remove();
                    $('input[type=hidden]#input-'+role_id).remove();
                }
            }
        });
    });
   
    $("a#add_new_role").click(function(eadd){
        eadd.preventDefault();
        var new_role = $('div.def_roles input#new_role').val();
        $.ajax({
            type: 'GET',
            url: ajaxurl,
            data: {
                role_name: new_role,
                action: 'bp_gtm_add_def_role'
            },
            success: function(data){
                console.log(typeof data);
                $('div.def_roles ul.def_roles_list').append(data);
            //                if(data.length < 4){
            //                   $('div.def_roles ul.def_roles_list').append('<li id="li-" class="one">#'+data+': '+new_role+'</li><div id="toggler"><div class="box"></div></div>');
            //                }else{
            //                    $('div.def_roles ul.def_roles_list').append('<div class="error"><p>'+data+'</p></div>');
            //                }
            }
        });

    });
    //$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
    //postboxes.add_postbox_toggles('buddypress_page_bp-gtm-admin');

});