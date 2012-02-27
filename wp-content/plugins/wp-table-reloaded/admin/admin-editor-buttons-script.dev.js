/***************************************************************
* This JS file belongs to the Admin part of WP-Table Reloaded! *
*       PLEASE DO NOT make any changes here! Thank you!        *
***************************************************************/

jQuery(document).ready(function($){

    var editor_toolbar = $( '#ed_toolbar' );
    if ( editor_toolbar ) {
        var theButton = document.createElement( 'input' );
            theButton.type = 'button';
            theButton.value = WP_Table_Reloaded_Editor_Button.str_EditorButtonCaption;
            theButton.className = 'ed_button';
            theButton.title = WP_Table_Reloaded_Editor_Button.str_EditorButtonCaption;
            theButton.id = 'ed_button_wp_table_reloaded';
            editor_toolbar.append( theButton );
            $( '#ed_button_wp_table_reloaded' ).click( wp_table_reloaded_button_click );
    }

    function wp_table_reloaded_button_click() {
        var title = 'WP-Table Reloaded';
        var url = WP_Table_Reloaded_Editor_Button.str_EditorButtonAjaxURL;

        tb_show( title, url, false);
        
        $( '#TB_ajaxContent' ).width( 'auto' ).height( '94.5%' )
        .click( function(event) {
            var $target = $(event.target);
            if ( $target.is('a.send_table_to_editor') ) {
                send_to_editor( '[table id=' + $target.attr('title') + ' /]' );
            }
            return false;
        } );

        return false;
    }

});