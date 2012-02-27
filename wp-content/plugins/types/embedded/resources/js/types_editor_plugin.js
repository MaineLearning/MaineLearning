var types_url;

(function() {

    
    tinymce.create('tinymce.plugins.types', {
        
        init : function(ed, url){
            types_url = url;
        },

        createControl : function(n, cm){
            switch(n) {
                case 'types':
                    var c = cm.createMenuButton('types_button', {
                             title : button_title,
                             image : types_url + '/../images/logo-20.png',
                             icons : false
                    });
                    
                    c.onRenderMenu.add(function(c, m) {
                        
                        c = icl_editor_add_menu(c, m, wp_editor_addon_types);
                    });

                    // Return the new menu button instance
                    return c;
                    
                default:
                    return 0;
            }
        }

    });

    tinymce.PluginManager.add('types', tinymce.plugins.types);
    
})();
