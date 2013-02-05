/***************************************************************
* This JS file belongs to the Admin part of WP-Table Reloaded! *
*       PLEASE DO NOT make any changes here! Thank you!        *
***************************************************************/

jQuery(document).ready(function($){

    function wp_table_reloaded_tinymce_button_click() {
        var title = 'WP-Table Reloaded';
        var url = WP_Table_Reloaded_Editor_Button.str_EditorButtonAjaxURL;

        tb_show( title, url, false );

        $( '#TB_ajaxContent' ).width( 'auto' ).height( '94.5%' )
        .click( function(event) {
            var $target = $(event.target);
            if ( $target.is( 'a.send_table_to_editor' ) ) {
                tinyMCE.execCommand( 'mceInsertContent', 0, '[table id=' + $target.attr( 'title' ) + ' /]' );
                tb_remove();
            }
            return false;
        } );

        return false;
    }

    // tinymce.PluginManager.requireLangPack('table');
    tinymce.create( 'tinymce.plugins.TablePlugin', {
        init : function(ed, url) {
            ed.addCommand( 'mceTableInsert', function() {
                wp_table_reloaded_tinymce_button_click();
            } );
            ed.addButton( 'table', {
				title : WP_Table_Reloaded_Editor_Button.str_EditorButtonTitle,
				cmd : 'mceTableInsert',
				image : url + '/plugin-editor-button.png'
            } );
            ed.onNodeChange.add( function(ed, cm, n) {
				cm.setActive( 'table', n.nodeName == 'IMG' );
            } );
		},
		createControl : function(n, cm) {
			return null;
		},
		getInfo : function() {
			return {
				longname : 'WP-Table Reloaded',
				author : 'Tobias B&auml;thge',
				authorurl : 'http://tobias.baethge.com/',
				infourl : 'http://tobias.baethge.com/wordpress-plugins/wp-table-reloaded-english/',
				version : '1.7'
			};
		}
	});
	tinymce.PluginManager.add( 'table', tinymce.plugins.TablePlugin );

});