(function() {
    tinymce.PluginManager.requireLangPack('StarRating');

    tinymce.create('tinymce.plugins.StarRating', {
        init: function(ed, url) {
            ed.addCommand('mceStarRating', function() {
                ed.windowManager.open({
                    file : url + '/window.php',
                    width : 350 + ed.getLang('StarRating.delta_width', 0),
                    height : 460 + ed.getLang('StarRating.delta_height', 0),
                    inline : 1
                }, {
                    plugin_url : url
                });
            });

            ed.addButton('StarRating', {
                title : 'StarRating.desc',
                cmd : 'mceStarRating',
                image : url + '/star.png'
            });

            ed.onNodeChange.add(function(ed, cm, n) {
                cm.setActive('StarRating', n.nodeName == 'IMG');
            });
        },

        createControl : function(n, cm) {
            return null;
        },

        getInfo : function() {
            return {
                longname  : 'StarRating',
                author 	  : 'Milan Petrovic',
                authorurl : 'http://www.dev4press.com/',
                infourl   : 'http://www.gdstarrating.com/',
                version   : "1.7"
            };
        }
    });

    tinymce.PluginManager.add('StarRating', tinymce.plugins.StarRating);
})();
