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
            jQuery('form h4:first').after('<div id="message" class="error">'+error+'<ul></ul></div>');
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
    
    jQuery('#gtm_form #member-list li').click(function(e){
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
        var project_tags_val = jQuery.trim(jQuery(this).val());
        if(e.which==13 && project_tags_val!=''){
            var project_tags = jQuery(this);
            e.preventDefault()
            paste_terms(project_tags_val, 'tags')
            project_tags.val('');
            jQuery('#tag_names').addClass('|'+replace_coma(project_tags_val));
        } else if(e.which==13 && project_tags_val==''){
            e.preventDefault()
        }
    });
    jQuery('#projects_tax [name="tags"], #tasks_tax [name="tags"]').click(function(){
        var obj = jQuery(this).parent().find(':text');
        var project_tags_val = obj.val();
        paste_terms(project_tags_val, 'tags')
        obj.val('');
        jQuery('#tag_names').addClass('|'+replace_coma(project_tags_val));
    });
    
    
    jQuery('#projects_tax .float .p, #tasks_tax .float .p').live('click', function(){
        var class_remove = jQuery(this).parent().find('span:first').text();
        jQuery(this).parent().remove();
        jQuery('#tag_names').removeClass('|'+class_remove);
    });
    
    jQuery('#gtm_form #cats, #tasks_tax #cats').keypress(function(e){
        var project_tags_val = jQuery.trim(jQuery(this).val());
        if(e.which==13 && project_tags_val!=''){
            var project_tags = jQuery(this);
            e.preventDefault();
            paste_cats(project_tags_val, 'cats')
            project_tags.val('');
            jQuery('#cat_names').addClass('|'+replace_coma(project_tags_val));
        } else if(e.which==13 && project_tags_val==''){
            e.preventDefault()
        }
    });
    jQuery('#projects_tax [name="cats"], #tasks_tax [name="cats"]').click(function(){
        var obj = jQuery(this).parent().find(':text');
        var project_tags_val = obj.val();
        paste_cats(project_tags_val, 'cats')
        obj.val('');
        jQuery('#cat_names').addClass('|'+replace_coma(project_tags_val));
    });
    
    
    jQuery('#projects_tax .right .p, #tasks_tax .right .p,').live('click', function(){
        var class_remove = jQuery(this).parent().find('span:first').text();
        jQuery(this).parent().remove();
        jQuery('#cat_names').removeClass('|'+class_remove);
    });
    
    jQuery('#gtm_form #post-topic-reply :submit').click(function(e){
        var textarea = jQuery('#gtm_form textarea#discuss_text');
        if(!jQuery.trim(textarea.val())){
            e.preventDefault();
            textarea.addClass('error-boreder');
            jQuery('#gtm_form #message').html('<p>Reply message can\'t be empty!</p>');
        }
        
    });
    function paste_terms(project_tags_val, block){
        if(project_tags_val.indexOf(',')==-1){
            jQuery('<li class="resps-tab" id="un-tag"><span></span> <span class="p">X</span></li>').find('span:first').text(project_tags_val).end().prependTo('#projects_tax .paste-'+block+', #tasks_tax .paste-'+block);
        } else {
            var terms = project_tags_val.split(',');
            for(var c in terms){
                if(jQuery.trim(terms[c]))
                    jQuery('<li class="resps-tab" id="un-tag"><span></span> <span class="p">X</span></li>').find('span:first').text(terms[c]).end().prependTo('#projects_tax .paste-'+block+', #tasks_tax .paste-'+block);
            }
        }
        return false;
    }
    function paste_cats(project_tags_val, block){
        if(project_tags_val.indexOf(',')==-1){
            jQuery('<p><input name="project_cats[]" type="checkbox" id="un-'+project_tags_val+'" checked="checked" value="'+project_tags_val+'"/><span class="text"></span></p>').find('.text').text(project_tags_val).end().prependTo('#projects_tax .paste-'+block+', #tasks_tax .paste-'+block);
        } else {
            var terms = project_tags_val.split(',');
            for(var c in terms){
                if(jQuery.trim(terms[c]))
                    jQuery('<p><input name="project_cats[]" type="checkbox" id="un-'+terms[c]+'" checked="checked" value="'+terms[c]+'"/><span class="text"></span></p>').find('.text').text(terms[c]).end().prependTo('#projects_tax .paste-'+block+', #tasks_tax .paste-'+block);
            }
        }
        //        console.log(jQuery('<span><input type="checkbox" id="un-'+project_tags_val+'" checked="checked" value="'+project_tags_val+'"/><span class="text"></span></span>').find('.text'))
        return false;
    }
  
    jQuery('#gtm_form .gtm_files .add_file').live('click', function(){
        var size = jQuery('#gtm_form .single_file').size();
        if(size < bp_gtm_strings.files_count){
            size++;
            jQuery('<div class="single_file"><input type="file" name="gtmFile_'+size+'" id="gtmFile" /><textarea name="description[gtmFile_'+size+']"></textarea><span class="delete_file">X</span></div>').appendTo('#gtm_form .gtm_files');
        }
    });
    jQuery('#gtm_form .gtm_files .delete_file').live('click', function(){
        jQuery(this).parent().remove();
    });
    
    var height_one_user = jQuery('#member-list li').height();
    var height_users = jQuery('.wrap-roles').height();
    if(jQuery('#member-list li').length > height_users/height_one_user){
        jQuery('#member-list li').parent().parent().css('overflow-y', 'scroll');
    }
    jQuery('#member-list li input').click(function(e){
        e.stopPropagation();
        jQuery(this).parent().toggleClass('red')
    });
    
    jQuery('#gtm_form .single_file [type="file"]').live('change', function(){
        jQuery(this).parent().find('textarea').show();
    });
    
    jQuery('.gtm_files_list .edit_description').click(function(){
        var textarea = jQuery(this).parent().parent();
        textarea.find('.hidden').toggle();
        textarea.find('.file_description').toggle();
    });
    
    jQuery('#gtm_form .submit_description').click(function(){
        var textarea = jQuery(this).parent().find('textarea');
        var file_id = textarea.attr('id').substring(12);
        var description = textarea.val();
        jQuery.ajax({
            type: 'GET',
            url: ajaxurl,
            data: {
                file_id: file_id,
                description: description,
                action: 'bp_update_description'
            },
            success: function(data) {
                if(!data){
                    textarea.end().parent().find('.edit_description').trigger('click');
                    textarea.end().parent().find('span.file_description').text(description);
                }
            }
        });
    });
    
    jQuery('#gtm_form .delete_file_discussion').click(function(){
        var textarea = jQuery(this).parent().parent().find('textarea');
        var file_id = textarea.attr('id').substring(12);
        if(confirm('Do you really want to delete this file?')){
            jQuery.ajax({
                type: 'GET',
                url: ajaxurl,
                data: {
                    file_id: file_id,
                    action: 'bp_delete_file'
                },
                success: function(data) {
                    if(!data){
                        textarea.end().hide().remove();
                    }
                }
            });
        }
    });
 function replace_coma(str){
     return str.replace(/,/gi, '| ');
     
 }
 jQuery('.projects-list .td-title a[title], .task-list .td-title a[title]').tooltip(
        {
            opacity: 0.7,
            position: ['bottom'],
            offset: [0, 50]
        }
    );
    
    
})
