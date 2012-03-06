var views_url;

(function() {

    
    tinymce.create('tinymce.plugins.wpv_views', {
        
        init : function(ed, url){
            views_url = url;
        },

        createControl : function(n, cm){
            switch(n) {
                case 'wpv_views':
                    var c = cm.createMenuButton('views_button', {
                             title : button_title,
                             image : views_url + '/../img/icon20.png',
                             icons : false
                    });
                    
                    c.onRenderMenu.add(function(c, m) {
                        
                        c = icl_editor_add_menu(c, m, wp_editor_addon_wpv_views);
                    });

                    // Return the new menu button instance
                    return c;
                    
                default:
                    return 0;
            }
        }

    });

    tinymce.PluginManager.add('wpv_views', tinymce.plugins.wpv_views);
    
})();
