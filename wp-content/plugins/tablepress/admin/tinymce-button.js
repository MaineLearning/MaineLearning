/**
 * JavaScript code for the "Table" button in the TinyMCE editor toolbar
 *
 * @package TablePress
 * @subpackage Views JavaScript
 * @author Tobias Bäthge
 * @since 1.0.0
 */

( function() {

	// only do this if TinyMCE is available
	if ( typeof( tinymce ) == 'undefined' )
		return;

	/**
	 * Register a button for the TinyMCE (aka Visual Editor) toolbar
	 *
	 * @since 1.0.0
	 */
	tinymce.create( 'tinymce.plugins.TablePressPlugin', {
		init: function( ed, url ) {
			ed.addCommand( 'TablePress_insert_table', tablepress_open_shortcode_thickbox );

			ed.addButton( 'tablepress_insert_table', {
				title: tablepress_editor_button.title,
				cmd: 'TablePress_insert_table',
				image: url + '/tablepress-editor-button.png'
			} );
		}
/* // no real need for getInfo(), as it is not displayed/used anywhere
		,
		getInfo: function() {
			return {
				longname: 'TablePress',
				author: 'Tobias Bäthge',
				authorurl: 'http://tobias.baethge.com/',
				infourl: 'http://tablepress.org/',
				version: '1.0.0'
			};
		}
*/
	} );
	tinymce.PluginManager.add( 'tablepress_tinymce', tinymce.plugins.TablePressPlugin );

} )();