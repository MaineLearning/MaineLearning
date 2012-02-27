jQuery(document).ready(function() {
    if (typeof ajaxurl == 'undefined')
        var ajaxurl = '/wp-load.php';
    
    
    /*
     * Tasks management
     */ 
    //
   
    jQuery('a.complete_task').click(function(e){
        e.preventDefault();
        var task_id = jQuery('a.complete_task').attr('id');
        jQuery.ajax({
            type: 'GET',
            url: ajaxurl,
            data: {
                task_id: task_id,
                action_type: 'complete',
                action: 'bp_gtm_do_task_actions',
                'cookie': encodeURIComponent(document.cookie)
            },
            success: function(data) {
                jQuery("span#task_status").css('color','green').css('display', '').html("&rarr; "+bp_gtm_strings.task_status);
            }
        });
    });

    jQuery('a.delete_task').click(function(e){
        e.preventDefault();
        var task_id = jQuery('a.delete_task').attr('id');
        jQuery.ajax({
            type: 'GET',
            url: ajaxurl,
            data: {
                task_id: task_id,
                action_type: 'delete',
                action: 'bp_gtm_do_task_actions',
                'cookie': encodeURIComponent(document.cookie)
            },
            success: function(data) {
                jQuery("span#task_status").css('color','red').css('display', '').html("&rarr; "+bp_gtm_strings.task_delete);
            }
        });
    });
    
    
    /*
     * Subtasks on tasks list page
     */
    // show subtasks on task view page
    jQuery('a.show_subtasks').click(function(e){
        e.preventDefault();
        var task_id = jQuery('a.show_subtasks').attr('id');
        jQuery.ajax({
            type: 'GET',
            url: ajaxurl,
            data: {
                parent_id: task_id,
                action: 'bp_gtm_get_subtasks'
            },
            success: function(data) {
                if (data.length > 0){
                    jQuery("div.subtasks").css('display','block').html('<h4 id="subtasks">'+bp_gtm_strings.tasks_subtasks_list+'</h4>'+data);
                }else{
                    jQuery("div.subtasks").css('display','block').html('<h4 id="subtasks">'+bp_gtm_strings.tasks_subtasks_empty+'</h4>');
                }
            }
        });
    });
    // show icon
    jQuery('tbody#tasks-list tr').hover(function(){
        var task_id = jQuery(this).attr('id');
        jQuery('span.subtasks_'+task_id).fadeIn('fast');
    });
    // hide
    jQuery('tbody#tasks-list tr').mouseleave(function(){
        var task_id = jQuery(this).attr('id');
        jQuery('span.subtasks_'+task_id).fadeOut('fast');
    });

    

    /*
     * Discussion posts management
     */
    // Delete post from a list
    jQuery('a.delete_post').click(function(e){
        e.preventDefault();
        var post = jQuery(this).attr('id');
        var element = String(jQuery(this).attr('rel')).split('_');

        jQuery.ajax({
            type: 'GET',
            url: ajaxurl,
            data: {
                action_type: 'delete',
                elem_type: element[0],
                elem_id: element[1],
                post_id: post,
                action: 'bp_gtm_discuss_posts_actions'
            },
            success: function(data) {
                jQuery('ul.discussions li#post-'+post).fadeOut('slow');
            }
        });
    });
    // Inline edit of posts
    jQuery('a.edit_post').click(function(e){
        e.preventDefault();
        var post = jQuery(this).attr('id');

        var edited_a = bp_gtm_strings.discuss_update;
        var cancel_a = bp_gtm_strings.cancel;

        var current_text = jQuery('li#post-'+post+' div.post-content').html();
        jQuery('li#post-'+post+' div.post-content').replaceWith('<p id="remove_me_later">\n\
            <textarea name="edit_discuss_text" id="edit_discuss_text">'+strip_tags(current_text)+'</textarea>\n\
            <br><br><a class="button edited_a" style="margin:0 20px 0 54px;" href="#">'+edited_a+'</a>\n\
            <a class="button cancel_a" href="#">'+cancel_a+'</a></p>');

        jQuery('a.edited_a').click(function(e2){
            e2.preventDefault();
            var new_text = jQuery('#edit_discuss_text').val();
            jQuery.ajax({
                type: 'GET',
                url: ajaxurl,
                data: {
                    action_type: 'edit',
                    text: new_text,
                    post_id: post,
                    action: 'bp_gtm_discuss_posts_actions'
                },
                success: function(data) {
                    jQuery('ul.discussions li#post-'+post+' #edit_discuss_text').fadeOut('slow');
                    jQuery('ul.discussions li#post-'+post+' p#remove_me_later').replaceWith('<div class="post-content">'+data+'</div>')
                    jQuery('ul.discussions li#post-'+post+'').fadeIn('slow');
                }
            });
        });

        jQuery('a.cancel_a').click(function(e3){
            e3.preventDefault();
            jQuery('ul.discussions li#post-'+post+' p#remove_me_later').replaceWith('<div class="post-content">'+current_text+'</div>');
        });
    });

    /*
     * Tags and Categories management
     */
    // edit terms extra input
    jQuery('a.edit_me').click(function(edit) {
        edit.preventDefault();
        var termID = jQuery(this).attr('id');
        var termName = jQuery('tr#'+termID+' td.td-title a.topic-title').text();
                
        jQuery('tr#'+termID).after('<tr id="edited" class="sticky">\n\
            <td class="td-title" colspan="2"><input style="width:80%" name="editTermName" id="editTermName'+termID+'" value="" /></td>\n\
            <td class="td-freshness"><a class="saveEditedTerm" href="#" title="'+bp_gtm_strings.terms_save+'"><img style="border:none" height="16" width="16" src="'+bp_gtm_strings.images+'/done.png" alt="'+bp_gtm_strings.terms_save+'" /></a>&nbsp;&nbsp;<a class="cancelEditedTerm" href="#" title="'+bp_gtm_strings.cancel+'"><img style="border:none" height="16" width="16" src="'+bp_gtm_strings.images+'/undone.png" alt="'+bp_gtm_strings.cancel+'" /></a></td>\n\
        </tr>');

        jQuery('input#editTermName'+termID).val(termName);

        jQuery('a.cancelEditedTerm').click(function(e){
            e.preventDefault();
            jQuery('tr#edited').fadeOut("slow");
        })

        jQuery('a.saveEditedTerm').click(function(esave){
            esave.preventDefault();
            var newTermName = jQuery('input#editTermName'+termID).val();
        
            jQuery.ajax({
                type: 'GET',
                url: ajaxurl,
                data: {
                    editID: termID,
                    editName: newTermName,
                    action: 'bp_gtm_edit_term'
                },
                success: function(data) {
                    jQuery('tr#'+termID+' td.td-title a.topic-title').text(newTermName);
                    jQuery('tr#edited').fadeOut("slow");
                }
            });//ajax
        });//save
    });//edit
    
    /*
     * Delete tasks, projects and terms
     */ 
    jQuery("a.delete_me").live('click',function(edelete){
        edelete.preventDefault();
        
        if ( confirm( bp_gtm_strings.delete_me ) ) {
            var itemID = jQuery(this).attr('id');
            var itemType = jQuery("tr#"+itemID+" td.td-freshness").attr('id');
            jQuery.ajax({
                type: 'GET',
                url: ajaxurl,
                data: {
                    deleteItem: itemID,
                    deleteType: itemType,
                    action: 'bp_gtm_delete_item'
                },
                success: function(data) {
                    jQuery("tr#"+itemID).fadeOut("slow");
                }
            });
        }else{
            return false;
        }
    });
    
    /*
     * Involved mamanegent
     */
    // notify by email
    jQuery('a.email_notify').click(function(e){
        e.preventDefault();
        var clicked_element = jQuery(this);
        jQuery.ajax({
            type: 'GET',
            url: ajaxurl,
            data: {
                resp_id: jQuery(this).attr('id'),
                action: 'bp_gtm_email_notify'
            },
            success: function(data) {
                jQuery("div.pagination").before('<div id="message" class="info"><p>'+bp_gtm_strings.involved_email+'</p></div>');
                clicked_element.parent().parent().find('td').addClass('notified_users').end().find('td:first .poster-name').append('<span class="activity"> User was just notified!</span>');
                setTimeout("jQuery('div#message').fadeOut('fast')", 5000);
            }
        });
    });

    
    
    jQuery('wrap-roles :checkbox').change(function(){
        var user_name = jQuery(this).val();
       
        if(jQuery(this).attr('checked')){
            jQuery(this).parent().css('background-color','#f4f4f4');
        } else {
            jQuery(this).parent().css('background-color','white');
        }
    });


    function strip_tags (input, allowed) {
   
        allowed = (((allowed || "") + "").toLowerCase().match(/<[a-z][a-z0-9]*>/g) || []).join(''); // making sure the allowed arg is a string containing only tags in lowercase (<a><b><c>)
        var tags = /<\/?([a-z][a-z0-9]*)\b[^>]*>/gi,
        commentsAndPhpTags = /<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi;
        return input.replace(commentsAndPhpTags, '').replace(tags, function ($0, $1) {
            return allowed.indexOf('<' + $1.toLowerCase() + '>') > -1 ? $0 : '';
        });
    }
    
    function count_minus(){// minus 1 task if it done or deleted or undone
        if(jQuery('.navi .counter').size()){
            var count = jQuery('.navi .counter').text();
            count = count - 1;
            jQuery('.navi .counter').text(count);
            var id = jQuery('.navi a:first').attr('id');
            var reg = /[-](\d+)/i;
            var all_tasks = reg.exec(id);
            var pagination_links_count = jQuery('.navi a').size();
                            
            if(count/all_tasks[1]<=1){
                var count_single = jQuery('#tasks-list tr:visible').size();
                jQuery('.navi').text('Use links in the right to filter them.')
            } else if((count/all_tasks[1]+1) <= pagination_links_count){
                jQuery('.navi a:last').remove();
            } 
                                    
        }
    }                  
    jQuery('a.done_me').live('click',function(edone) {
        edone.preventDefault();
        var itemID = jQuery(this).attr('id');
        var itemType = jQuery('td.td-freshness').attr('id');
        jQuery.ajax({
            type: 'GET',
            url: ajaxurl,
            data: {
                doneID: itemID,
                doneType: itemType,
                doneAction: 'done',
                action: 'bp_gtm_done_item',
                'cookie': encodeURIComponent(document.cookie)
            },
            success: function(data) {
                jQuery('tr#'+itemID).fadeOut("slow");
                jQuery('.navi .current').trigger('click');
                count_minus();
            }
        });
    });

    jQuery('a.undone_me').live('click',function(eundone) {
        eundone.preventDefault();
        var itemID = jQuery(this).attr('id');
        var itemType = jQuery('td.td-freshness').attr('id');
        jQuery.ajax({
            type: 'GET',
            url: ajaxurl,
            data: {
                doneID: itemID,
                doneType: itemType,
                doneAction: 'undone',
                action: 'bp_gtm_done_item',
                'cookie': encodeURIComponent(document.cookie)
            },
            success: function(data) {
                jQuery('tr#'+itemID).fadeOut("slow");
                jQuery('a.task_navi').trigger('click');
                count_minus();
            }
        });
    });
            
    
    jQuery("a#open").click(function(eopen) {
        eopen.preventDefault();
        jQuery('#box').slideToggle();
    });
    jQuery('a.discuss_navi').click(function(e) {
        e.preventDefault();
        var pageN = jQuery(this).attr('id');
        jQuery('tbody#discuss-list').fadeOut("slow");
        jQuery('a.discuss_navi').removeClass('current');
        jQuery(this).addClass('current');
        jQuery.ajax({
            type: 'GET',
            url: '/wp-load.php',
            data: {
                nextPage: pageN,
                what: bp_gtm_strings.discuss_nav_what,
                action: 'bp_gtm_discuss_navi_content',
                'cookie': encodeURIComponent(document.cookie)
            },
            success: function(data) {
                var per_page = String(pageN).split('-');
                var pageBegin = (per_page[0] * per_page[1]) + parseInt('1');
                var pageEnd = (parseInt(pageBegin) + parseInt(per_page[1])) - 1;
                if (pageEnd > jQuery('div.pag-count p.navi').attr('id'))
                    var pageEnd = jQuery('div.pag-count p.navi').attr('id');
                jQuery('span#cur_discuss').text(pageBegin+' - '+pageEnd);
                jQuery('tbody#discuss-list').html(data).fadeIn("slow");
            }
        });
    });
    jQuery('.group-navi a.task_navi').click(function(e) {
        e.preventDefault();
        var pageN = jQuery(this).attr('id');
        jQuery('tbody#tasks-list').fadeOut("slow");
        jQuery('a.task_navi').removeClass('current');
        jQuery(this).addClass('current');
        jQuery.ajax({
            type: 'GET',
            url: '/wp-load.php',
            data: {
                nextPage: pageN,
                filter: bp_gtm_strings.task_navi_filter,
                project: bp_gtm_strings.task_navi_project,
                action: 'bp_gtm_tasks_navi_content',
                'cookie': encodeURIComponent(document.cookie)
            },
            success: function(data) {
                var per_page = String(pageN).split('-');
                var pageBegin = (per_page[0] * per_page[1]) + parseInt('1');
                var pageEnd = (parseInt(pageBegin) + parseInt(per_page[1])) - 1;
                if (pageEnd > jQuery('div.pag-count p.navi').attr('id'))
                    var pageEnd = jQuery('div.pag-count p.navi').attr('id');
                jQuery('span#cur_tasks').text(pageBegin+' - '+pageEnd);
                jQuery('tbody#tasks-list').html(data).fadeIn("slow");

                // Subtasks
                // show extra icons
                jQuery('tbody#tasks-list tr').hover(function(){
                    var task_id = jQuery(this).attr('id');
                    jQuery('span.subtasks_'+task_id).fadeIn('fast');
                });

                // hide extra icons
                jQuery('tbody#tasks-list tr').mouseleave(function(){
                    var task_id = jQuery(this).attr('id');
                    jQuery('span.subtasks_'+task_id).fadeOut('fast');
                });

                           

            }
        });
    });
    if(jQuery.fn.datepicker){
        var datepicker_settings = jQuery.datepicker.regional[bp_gtm_strings.lang];
        datepicker_settings.dateFormat = bp_gtm_strings.date_format;
        jQuery("#project_deadline").datepicker(datepicker_settings);
        jQuery("#task_deadline").datepicker(datepicker_settings);
    }
    if(bp_gtm_strings.mce=='on'){
        id = 'gtm_desc';
        jQuery('#edButtonPreview').click(
            function() {
                tinyMCE.execCommand('mceAddControl', false, id);
                jQuery('#edButtonPreview').addClass('active');
                jQuery('#edButtonHTML').removeClass('active');
            }
            );
        jQuery('#edButtonHTML').click(
            function() {
                tinyMCE.execCommand('mceRemoveControl', false, id);
                jQuery('#edButtonPreview').removeClass('active');
                jQuery('#edButtonHTML').addClass('active');
            }
            );
    }
    
    //---------------------MEMEBER SECTIOn

    jQuery("a#open_project").click(function(eopen) {
        eopen.preventDefault();
        jQuery(".filter_project").slideToggle();
    });
    jQuery("a#open_group").click(function(eopen) {
        eopen.preventDefault();
        jQuery(".filter_group").slideToggle();
    });
    
    jQuery('.personal a.task_navi').click(function(e) {
        e.preventDefault();
        var pageN = jQuery(this).attr('id');
        jQuery('tbody#tasks-list').fadeOut("slow");
        jQuery('a.task_navi').removeClass('current');
        jQuery(this).addClass('current');
        jQuery.ajax({
            type: 'GET',
            url: '/wp-load.php',
            data: {
                nextPage: pageN,
                filter: bp_gtm_strings.person_navi_filter,
                project: bp_gtm_strings.task_navi_project,
                action: 'bp_gtm_personal_tasks_navi_content',
                'cookie': encodeURIComponent(document.cookie)
            },
            success: function(data) {
                var per_page = String(pageN).split('-');
                var pageBegin = (per_page[0] * per_page[1]) + parseInt('1');
                var pageEnd = (parseInt(pageBegin) + parseInt(per_page[1])) - 1;
                if (pageEnd > jQuery('p.navi').attr('id')) pageEnd = jQuery('p.navi').attr('id');
                jQuery('span#cur_tasks').text(pageBegin+' - '+pageEnd);
                jQuery('tbody#tasks-list').html(data).fadeIn("slow");
            }
        });
    });
    
    jQuery('[name="saveNewProject"], [name="editProject"]').click(function(e){
        
        var error = '';
        if(!jQuery('#project_name').val()){
            jQuery('label[for="project_name"]').addClass('error-color');
            jQuery('#project_name').addClass('error-boreder');
            error +='<p>Project name can\'t be empty!</p>'
        }
        if(!jQuery('#project_deadline').val()){
            jQuery('label[for="project_deadline"]').addClass('error-color');
            jQuery('#project_deadline').addClass('error-boreder');
            error +='<p>Project deadline can\'t be empty!</p>'
        }
        if(error){
            e.preventDefault();
            jQuery('#message').remove();
            jQuery('form h4').after('<div id="message" class="error">'+error+'<ul></ul></div>');
        }
    });
    
    jQuery('[name="saveNewTask"], [name="editTask"]').click(function(e){
        
        var error = '';
        if(!jQuery('#task_name').val()){
            jQuery('label[for="task_name"]').addClass('error-color');
            jQuery('#task_name').addClass('error-boreder');
            error +='<p>Task name cann\'t be empty!</p>'
        }
        if(!jQuery('#task_deadline').val()){
            jQuery('label[for="task_deadline"]').addClass('error-color');
            jQuery('#task_deadline').addClass('error-boreder');
            error +='<p>Task deadline can\'t be empty!</p>'
        }
        if(error){
            e.preventDefault();
            jQuery('#message').remove();
            jQuery('form h4').after('<div id="message" class="error">'+error+'</div>');
        }
    });
    
    jQuery('#gtm_form #member-list li').click(function(){
        var checked = jQuery(this).find('input').attr('checked');
        if(!checked){
            jQuery(this).find('input').attr('checked', true);
        }else {
            jQuery(this).find('input').attr('checked', false);
        }
        jQuery(this).toggleClass('red');
    });
    
    if(jQuery.fn.autoCompleteCats){
        /**
       * Autocomplete init
       */ 
        // for cats
        ajaxurl = '/wp-load.php'
        var resps = jQuery("ul.second").autoCompleteCats({
            type: 'resps'
        });
        // for tags
        var tags = jQuery("ul.first").autoCompleteTags({
            type: 'tags'
        });
        jQuery('#gtm_form').submit( function() {
            var cats = jQuery('#cat_names').attr('class');
            var tags = jQuery('#tag_names').attr('class');
            jQuery('#cat_names').val(cats);
            jQuery('#tag_names').val(tags);
        });
        
    }
    jQuery('#gtm_form #tags,  #tasks_tax #tags').keypress(function(e){
        if(e.which==13){
            var project_tags = jQuery(this);
            var project_tags_val = jQuery(this).val();
            e.preventDefault()
            jQuery('<li class="resps-tab" id="un-tag"><span></span> <span class="p">X</span></li>').find('span:first').text(project_tags_val).end().prependTo('#projects_tax .first, #tasks_tax .first');
            project_tags.val('');
            jQuery('#tag_names').addClass('|'+project_tags_val);
        }
    });
    jQuery('#projects_tax .float .p, #tasks_tax .float .p').live('click', function(){
        var class_remove = jQuery(this).parent().find('span:first').text();
        jQuery(this).parent().remove();
        jQuery('#tag_names').removeClass('|'+class_remove);
    });
    
    jQuery('#gtm_form #cats, #tasks_tax #cats').keypress(function(e){
        if(e.which==13){
            var project_tags = jQuery(this);
            var project_tags_val = jQuery(this).val();
            e.preventDefault()
            jQuery('<li class="resps-tab" id="un-tag"><span></span> <span class="p">X</span></li>').find('span:first').text(project_tags_val).end().prependTo('#projects_tax .second, #tasks_tax .second');
            project_tags.val('');
            jQuery('#cat_names').addClass('|'+project_tags_val);
        }
    });
    jQuery('#projects_tax .right .p, #tasks_tax .right .p,').live('click', function(){
        var class_remove = jQuery(this).parent().find('span:first').text();
        jQuery(this).parent().remove();
        jQuery('#cat_names').removeClass('|'+class_remove);
    });
    
    jQuery('#gtm_form #post-topic-reply :submit').click(function(e){
        var textarea = jQuery('#gtm_form #post-topic-reply textarea');
        if(!jQuery.trim(textarea.val())){
            e.preventDefault();
            textarea.addClass('error-boreder');
            jQuery('#gtm_form #message').html('<p>Reply message can\'t be empty!</p>');
        }
        
    });
    
})
