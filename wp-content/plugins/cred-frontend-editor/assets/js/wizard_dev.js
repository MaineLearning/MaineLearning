var cred_cred_wizard = function ($, locale, helpObj, cred_main)
{
    var pub = null, _priv=null;

    // private methods/properties
    _priv = _priv ||
    {
        submithandler : function(event)
        {
            event.preventDefault();
            var post=$(this);
            var form_id=$('#post_ID').val();
            if (cred_main.doCheck())
            {
                $.ajax({
                    url:_priv.form_controller_url+'/updateFormField',
                    data:'form_id='+form_id+'&field=wizard'+'&value='+pub.completed_step,
                    type: 'post',
                    success:
                        function(result){

                            post.unbind('submit',_priv.submithandler);
                            post.submit();
                            return true;
                        }
                });
            }
            return false;
        },

        serialize : function(what)
        {
            var values, index, newvals=cred_main.getCodeMirrorContents();

            // Get the parameters as an array
            values = $(what).serializeArray();

            // Find and replace `content` if there
            for (index = 0; index < values.length; ++index)
            {
                if (newvals[values[index].name] /*values[index].name == "content"*/)
                {
                    values[index].value = newvals[values[index].name];
                    //break;
                }
            }

            // Add it if it wasn't there !!!check this out!!!
            /*if (index >= values.length)
            {
                values.push({
                    name: "content",
                    value: newval
                });
            }*/

            // Convert to URL-encoded string
            return $.param(values);
        }
    };

    // public methods/properties
    pub = pub ||
    {
        step : 1,

        prevstep : 0,

        completed_step:0,

        steps : 5,

        step_1 : {

            title : locale.step_1_title,

            completed : false,

            execute : function()
            {
                // setup
                $('#post-body-content').children(':not(.cred-not-hide)').hide();
                //$('#postdivrich').show();
                //$('#credformtypediv').show();
                //$('#credposttypediv').show();
                $('#titlediv').show();
                $('#cred-wizard-button-prev').hide();

                if (!pub.step_1.completed)
                {
                    $('#cred-wizard-button-next').attr('disabled','disabled');
                    $('#title').unbind('keyup').bind('keyup paste',function(){
                        pub.step_1.completed=true;
                        pub.completed_step=1;
                        $('#cred-wizard-button-next').removeAttr('disabled');
                        $('#title').unbind('keyup');
                    });
                    $('.cred-progress-bar-inner').css('width','20%');
                }
                else
                {
                    pub.step_1.completed=true;
                    pub.completed_step=1;
                    $('#cred-wizard-button-next').removeAttr('disabled');
                }
            }
        },

        step_2 : {

            title : locale.step_2_title,

            completed : false,

            execute : function(prev)
            {
                // setup
                $('#post-body-content').children(':not(.cred-not-hide)').hide();
                //$('#postdivrich').show();
                $('#credformtypediv').show();
                //$('#credposttypediv').show();
                //$('#titlediv').show();
                $('#cred-wizard-button-prev').show();

                if (!pub.step_2.completed)
                {
                    $('#cred-wizard-button-next').attr('disabled','disabled');
                    $('select[name="cred_form_type"]')/*.unbind('change')*/.bind('change',function(){
                        pub.step_2.completed=true;
                        pub.completed_step=2;
                        $('#cred-wizard-button-next').removeAttr('disabled');
                    });
                    $('.cred-progress-bar-inner').css('width','40%');
                }
                else
                {
                    pub.step_2.completed=true;
                    pub.completed_step=2;
                    $('#cred-wizard-button-next').removeAttr('disabled');
                }
            }
        },

        step_3: {

            title : locale.step_3_title,

            completed : false,

            execute : function(prev)
            {
                // setup
                $('#post-body-content').children(':not(.cred-not-hide)').hide();
                //$('#postdivrich').show();
                //$('#credformtypediv').show();
                $('#credposttypediv').show();
                //$('#titlediv').show();
                $('#cred-wizard-button-prev').show();
                if (!pub.step_3.completed)
                {
                    $('#cred-wizard-button-next').attr('disabled','disabled');
                    $('select[name="cred_post_type"]').unbind('change').bind('change',function(){
                        pub.step_3.completed=true;
                        pub.completed_step=3;
                        $('#cred-wizard-button-next').removeAttr('disabled');
                    });
                    $('.cred-progress-bar-inner').css('width','60%');
                }
                else
                {
                    pub.step_3.completed=true;
                    pub.completed_step=3;
                    $('#cred-wizard-button-next').removeAttr('disabled');
                }
            }
        },

        step_4: {

            title : locale.step_4_title,

            completed : false,

            execute : function(prev)
            {
                $('#post-body-content').children(':not(.cred-not-hide)').hide();
                //$('#postdivrich').show();
                $('#postdivrichwrap').show();
                //$('#credformtypediv').show();
                //$('#credposttypediv').show();
                //$('#titlediv').show();
                $('#cred-wizard-button-prev').show();
                if (!pub.step_4.completed)
                {
                    $('#cred-wizard-button-next').attr('disabled','disabled');
                    $('#content')/*.unbind('keyup paste')*/.bind('keyup paste',function(){
                        pub.completed_step=4;
                        pub.step_4.completed=true;
                        $('#cred-wizard-button-next').removeAttr('disabled');
                    });
                    $('.cred-progress-bar-inner').css('width','80%');
                    // keep checking
                    var _tim=setInterval(function(){
                        if (!pub.step_4.completed)
                        {
                            var content=cred_main.getCodeMirrorContents();
                            content=$.trim(content['content']);
                            if (''!=content)
                            {
                                clearInterval(_tim);
                                pub.completed_step=4;
                                pub.step_4.completed=true;
                                $('#cred-wizard-button-next').removeAttr('disabled');
                            }
                        }
                        else
                        {
                            clearInterval(_tim);
                        }
                    },200);
                }
                else
                {
                    pub.step_4.completed=true;
                    pub.completed_step=4;
                    $('#cred-wizard-button-next').removeAttr('disabled');
                }
            }
        },

        step_5: {

            title : locale.step_5_title,

            completed : false,

            execute : function(prev)
            {
                $('#post-body-content').children(':not(.cred-not-hide)').hide();
                $('#crednotificationdiv').show();
                //$('#credformtypediv').show();
                //$('#credposttypediv').show();
                //$('#titlediv').show();
                $('#cred-wizard-button-prev').show();
                // make this step optional
                $('#cred-wizard-button-next').removeAttr('disabled');
                pub.completed_step=5;
                pub.step_5.completed=true;
                if (!pub.step_5.completed)
                {
                    //$('#cred-wizard-button-next').attr('disabled','disabled');
                    $('#cred_mail_to').unbind('keyup paste').bind('keyup paste',function(){
                        pub.completed_step=5;
                        pub.step_5.completed=true;
                        $('#cred-wizard-button-next').removeAttr('disabled');
                    });
                    $('.cred-progress-bar-inner').css('width','100%');
                }
                else
                {
                    pub.step_5.completed=true;
                    pub.completed_step=5;
                    $('#cred-wizard-button-next').removeAttr('disabled');
                }
            }
        },

        prevStep : function()
        {
            pub.goToStep(pub.step-1);
        },

        nextStep : function()
        {
            if (_priv.newform && pub.step==1 && !pub.step_2.completed)
            {
                var form_id=$('#post_ID').val();
                if (cred_main.doCheck())
                {
                    $.ajax({
                        url:_priv.edit_url,
                        data:_priv.serialize('#post'), //$('#post').serialize(),
                        type: 'post',
                        success:
                            function(result){

                                $.ajax({
                                    url:_priv.form_controller_url+'/updateFormField',
                                    data:'form_id='+form_id+'&field=wizard'+'&value='+pub.completed_step,
                                    type: 'post',
                                    success:
                                        function(result){

                                            document.location=_priv.edit_url+'?action=edit&post='+form_id;
                                        }
                                });
                                //document.location=_priv.edit_url+'?action=edit&post='+form_id;
                            }
                    });
                }
            }
            else
            {
                // save this step
                if (cred_main.doCheck())
                {
                    var wizard_step=pub.completed_step;
                    if (pub.completed_step==pub.steps)
                        wizard_step=-1;
                    $.ajax({
                        url:document.location,
                        data:_priv.serialize('#post')/*$('#post').serialize()*/+'&_cred_wizard='+wizard_step,
                        type: 'post',
                        success: function(){}
                    });
                    pub.goToStep(pub.step+1);
                }
            }
        },

        goToStep : function(step)
        {
            if (typeof step == 'undefined') return;

            step=parseInt(step);
            if (step<=pub.steps+1 && step>=1)
                pub.step=step;
            else return;

            if (pub.step==pub.steps+1)
            {
                pub.finish();
                return;
            }

            if (typeof(pub['step_'+pub.step])!='undefined' && typeof(pub['step_'+pub.step].execute) == 'function')
            {
                if (pub.step==pub.steps)
                    $('#cred-wizard-button-next').show().val(locale.finish_text);
                else
                    $('#cred-wizard-button-next').show().val(locale.next_text);

                pub['step_'+pub.step].execute();
                return;
            }
        },

        start : function() {
            // setup
            $('#post-body-content').append('<div class="cred-not-hide cred-wizard-buttons"> <input id="cred_wizard_quit_button" type="button" class="button" value="'+locale.quit_wizard_text+'" /> <input type="button" id="cred-wizard-button-prev" class="button" value="'+locale.prev_text+'" /> <input type="button" id="cred-wizard-button-next" class="button-primary" value="'+locale.next_text+'" /></div>');

            //$('#post-body-content').prepend('<div class="cred-not-hide"><input id="cred_wizard_quit_button" type="button" class="button" value="'+locale.quit_wizard_text+'" /></div>');

            // progress bar
            $('#post-body-content').prepend('<div class="cred-not-hide cred-progress"><div class="cred-progress-bar"><div class="cred-progress-bar-inner"></div></div></div>');

            $('#cred-submit').hide();
            $('#cred_add_forms_to_site_help').hide();

            $('#post').submit(_priv.submithandler);

            var st1=$('<span class="cred-progress-step">'+pub.step_1.title+'</span>');
            st1.insertBefore('.cred-progress-bar').css({'left':'20%','margin-left':-st1.width()+'px'});
            var st2=$('<span class="cred-progress-step">'+pub.step_2.title+'</span>');
            st2.insertBefore('.cred-progress-bar').css({'left':'40%','margin-left':-st2.width()+'px'});
            var st3=$('<span class="cred-progress-step">'+pub.step_3.title+'</span>');
            st3.insertBefore('.cred-progress-bar').css({'left':'60%','margin-left':-st3.width()+'px'});
            var st4=$('<span class="cred-progress-step">'+pub.step_4.title+'</span>');
            st4.insertBefore('.cred-progress-bar').css({'left':'80%','margin-left':-st4.width()+'px'});
            var st5=$('<span class="cred-progress-step">'+pub.step_5.title+'</span>');
            st5.insertBefore('.cred-progress-bar').css({'left':'100%','margin-left':-st5.width()+'px'});

            $('#post-body-content').on('click','#cred_wizard_quit_button',function(){

                form_id=$('#post_ID').val();

                $.msgbox(locale.quit_wizard_confirm_text, {
                  type: "confirm",
                  buttons : [
                    {type: "submit", value: locale.quit_wizard_this_form},
                    {type: "submit", value: locale.quit_wizard_all_forms},
                    {type: "cancel", value: locale.cancel_text}
                  ]
                }, function(result) {
                   if (result==locale.quit_wizard_all_forms)
                   {
                        $.ajax({
                            url: _priv.url,
                            type: 'POST',
                            data: 'cred_wizard=false',
                            dataType: 'html',
                            success: function(){}
                        });
                   }
                   if (result==locale.quit_wizard_this_form)
                   {
                        $.ajax({
                            url:_priv.form_controller_url+'/updateFormField',
                            data:'form_id='+form_id+'&field=wizard'+'&value=-1',
                            type: 'post',
                            success: function(){}
                        });
                   }
                  if (result==locale.quit_wizard_all_forms || result==locale.quit_wizard_this_form)
                        $('#post').unbind('submit',_priv.submithandler);
                        pub.finish();
                });

                //$('.jquery-msgbox-buttons .button').addClass('button');

            });
            $('#post-body-content').on('click','#cred-wizard-button-next',function(){
                pub.nextStep();
            });
            $('#post-body-content').on('click','#cred-wizard-button-prev',function(){
                pub.prevStep();
            });

            // go
            for (var i=1; i<=pub.step; i++)
            {
                if (typeof(pub['step_'+i])!='undefined')
                    pub['step_'+i].completed=true;
            }
            pub.completed_step = pub.step;
            $('.cred-progress-bar-inner').css('width',(100*pub.step/pub.steps)+'%');
            if (pub.step<pub.steps)
                pub.goToStep(pub.step+1);
        },

        finish : function()
        {
            pub.completed_step=-1;
            $('#post-body-content').children().show();
            $('#cred-submit').show();
            $('#cred_add_forms_to_site_help').show();
            $('#post-body-content').children('.cred-not-hide').hide();
        },

        init : function(url, edit_url, form_controller_url, step, newform)
        {
            // save data
            _priv.url=url;
            _priv.edit_url=edit_url;
            _priv.form_controller_url=form_controller_url;
            _priv.step=step;
            _priv.newform=newform;

            if (_priv.step>=0)
            {
                pub.step=_priv.step;
                pub.start();
            }
        }
    }

    // make it publicly available
    return pub;
}(jQuery, cred_cred_config, cred_cred_help, cred_cred);