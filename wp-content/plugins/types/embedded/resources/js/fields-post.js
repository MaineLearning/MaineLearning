jQuery(document).ready(function(){
    // Set active editor
    window.wpcfActiveEditor = false;
    jQuery('.wp-media-buttons a, .wpcf-wysiwyg .editor_addon_wrapper .item, #postdivrich .editor_addon_wrapper .item').click(function(){
        window.wpcfActiveEditor = jQuery(this).parents('.wpcf-wysiwyg, #postdivrich').find('textarea').attr('id');
        document.cookie = "wpcfActiveEditor="+window.wpcfActiveEditor+"; expires=Monday, 31-Dec-2020 23:59:59 GMT; path="+wpcf_cookiepath+"; domain="+wpcf_cookiedomain+";";
    });
});


