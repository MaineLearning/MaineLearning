<?php
/*
Plugin Name: BP Group Organizer
Plugin URI: http://www.generalthreat.com/projects/buddypress-group-organizer
Description: Easily create, edit, and delete BuddyPress groups - with drag and drop simplicity
Version: 1.0.7
Revision Date: 11/18/2012
Requires at least: WP 3.1, BuddyPress 1.5
Tested up to: WP 3.5-beta3 , BuddyPress 1.7-bleeding
License: Example: GNU General Public License 2.0 (GPL) http://www.gnu.org/licenses/gpl.html
Author: David Dean
Author URI: http://www.generalthreat.com/
*/

include dirname(__FILE__) . '/functions.php';

/** load localization files if present */
if( file_exists( dirname( __FILE__ ) . '/languages/' . dirname(plugin_basename(__FILE__)) . '-' . get_locale() . '.mo' ) ) {
	load_plugin_textdomain( 'bp-group-organizer', false, dirname(plugin_basename(__FILE__)) . '/languages' );
} else if ( file_exists( dirname( __FILE__ ) . '/languages/' . get_locale() . '.mo' ) ) {
	_doing_it_wrong( 'load_textdomain', 'Please rename your translation files to use the ' . dirname(plugin_basename(__FILE__)) . '-' . get_locale() . '.mo' . ' format', '1.0.2' );
	load_textdomain( 'bp-group-organizer', dirname( __FILE__ ) . '/languages/' . get_locale() . '.mo' );
}

function bp_group_organizer_admin() {

	if( bpgo_has_groups_menu() ) {
		$page = add_submenu_page( 'bp-groups', __('Group Organizer', 'bp-group-organizer'), __('Organizer', 'bp-group-organizer'), 'manage_options', 'group_organizer', 'bp_group_organizer_admin_page' );
	} else {
		$page = add_submenu_page( 'bp-general-settings', __('Group Organizer', 'bp-group-organizer'), __('Group Organizer', 'bp-group-organizer'), 'manage_options', 'group_organizer', 'bp_group_organizer_admin_page' );
	}
	add_action('admin_print_scripts-' . $page, 'bp_group_organizer_load_scripts');
	add_action('admin_print_styles-' . $page, 'bp_group_organizer_load_styles');
}

function bp_group_organizer_register_admin() {

	add_action( 'network_admin_menu', 'bp_group_organizer_admin' );
	add_action( 'admin_menu', 'bp_group_organizer_admin' );	// fix issue with BP 1.2 and admin URL
	add_action( 'admin_init', 'bp_group_organizer_handle_export' );
	add_action( 'admin_init', 'bp_group_organizer_register_scripts' );

}
add_action( 'bp_include', 'bp_group_organizer_register_admin' );

function bp_group_organizer_register_scripts() {
	wp_register_script( 'group-organizer', plugins_url( 'js/group-organizer.js', __FILE__ ), array('jquery') );
}

function bp_group_organizer_load_scripts() {

	// jQuery
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'jquery-ui-draggable' );
	wp_enqueue_script( 'jquery-ui-droppable' );
	wp_enqueue_script( 'jquery-ui-sortable' );
	
	// Group Organizer script
	wp_enqueue_script( 'group-organizer' );
	wp_localize_script( 'group-organizer', 'OrganizerL10n', bp_group_organizer_translate_script() );
	
	// Meta boxes
	wp_enqueue_script( 'common' );
	wp_enqueue_script( 'wp-lists' );
	wp_enqueue_script( 'postbox' );
	
}

function bp_group_organizer_translate_script() {
	return array(
		'noResultsFound'	=> _x('No results found.', 'search results'),
		'warnDeleteGroup'	=> __( "You are about to permanently delete this group. \n 'Cancel' to stop, 'OK' to delete.", 'bp-group-organizer'),
		'groupDeleted'		=> __('Group was deleted successfully.', 'bp-group-organizer'),
		'groupDeleteFailed'	=> __('Group could not be deleted.', 'bp-group-organizer'),
		'saveAlert' 		=> __('The changes you made will be lost if you navigate away from this page.'),
	);
}

function bp_group_organizer_load_styles() {

	// Nav Menu CSS
	// This dropped out in WP 3.3, but is still needed for compat
	if( floatval( get_bloginfo( 'version' ) ) < 3.3 ) {
		wp_admin_css( 'nav-menu' );
	}
	
}

function bp_group_organizer_admin_page() {
	
	global $wpdb;

	// Permissions Check
	if ( ! current_user_can('manage_options') )
		wp_die( __( 'Cheatin&#8217; uh?' ) );
	
	// Load all the nav menu interface functions
	require_once( 'includes/group-meta-boxes.php' );
	require_once( 'includes/group-organizer-template.php' );
	require_once( 'includes/group-organizer.php' );
	
	// Container for any messages displayed to the user
	$messages = array();

	// Container that stores the name of the active menu
	$nav_menu_selected_title = '';
	
	// Allowed actions: add, update, delete
	$action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : 'edit';
	
	$errored = false;
	
	switch ( $action ) {
		case 'add-group':
			check_admin_referer( 'add-group', 'group-settings-column-nonce' );
			
			$group['name']			= stripslashes( $_POST['group_name'] );
			$group['description']	= stripslashes( $_POST['group_desc'] );
			$group['slug']			= groups_check_slug( $_POST['group_slug'] );
			$group['status']		= $_POST['group_status'];
			$group['enable_forum']	= isset( $_POST['group_forum'] ) ? true : false;
			$group['date_created']	= date('Y-m-d H:i:s');
	
			if($group['slug'] != $_POST['group_slug']) {
				$messages[] = '<div class="updated warning"><p>' . sprintf(__('The group slug you specified was unavailable or invalid. This group was created with the slug: <code>%s</code>.', 'bp-group-organizer'),$group['slug']) . '</p></div>';
			}
			
			if( empty( $group['name'] ) ) {
				$messages[] = '<div class="error"><p>' . __( 'Group could not be created because one or more required fields were not filled in', 'bp-group-organizer' ) . '</p></div>';
				$errored = true;
			}
			
			if( ! $errored ) {
				
				$group_id = groups_create_group( $group );
				
				if( ! $group_id ) {
					$wpdb->show_errors();
					$wpdb->print_error();
					$messages[] = '<div class="error"><p>' . __('Group was not successfully created.', 'bp-group-organizer') . '</p></div>';
				} else {
					$messages[] = '<div class="updated"><p>' . __('Group was created successfully.', 'bp-group-organizer') . '</p></div>';
				}
			}
			
			if( ! empty( $group_id ) ) {
				
				groups_update_groupmeta( $group_id, 'total_member_count', 1);
				
				if( bpgo_is_hierarchy_available() ) {
					$obj_group = new BP_Groups_Hierarchy( $group_id );
					$obj_group->parent_id = (int)$_POST['group_parent'];
					$obj_group->save();
				}
			
				// Create the forum if enable_forum is checked
				if ( $group['enable_forum'] ) {
					
					// Ensure group forums are activated, and group does not already have a forum
					if( bp_is_active( 'forums' ) ) {
						// Check for BuddyPress group forums
						if( ! groups_get_groupmeta( $group_id, 'forum_id' ) ) {
							groups_new_group_forum( $group_id, $group['name'], $group['description'] );
						}
					} else if ( function_exists('bbp_is_group_forums_active') && bbp_is_group_forums_active() ) {
						// Check for bbPress group forums
						if( count( bbp_get_group_forum_ids( $group_id ) ) == 0 ) {
							
							// Create the group forum - implementation from BBP_Forums_Group_Extension:create_screen_save
							
							// Set the default forum status
							switch ( $group['status'] ) {
								case 'hidden'  :
									$status = bbp_get_hidden_status_id();
									break;
								case 'private' :
									$status = bbp_get_private_status_id();
									break;
								case 'public'  :
								default        :
									$status = bbp_get_public_status_id();
									break;
							}
			
							// Create the initial forum
							$forum_id = bbp_insert_forum( array(
								'post_parent'  => bbp_get_group_forums_root_id(),
								'post_title'   => $group['name'],
								'post_content' => $group['description'],
								'post_status'  => $status
							) );
							
							bbp_add_forum_id_to_group( $group_id, $forum_id );
							bbp_add_group_id_to_forum( $forum_id, $group_id );
						}
					}
				}
				
				do_action( 'bp_group_organizer_save_new_group_options', $group_id );
			}
			break;
		case 'delete-group':
			$group_id = (int) $_REQUEST['group_id'];
	
			check_admin_referer( 'delete-group_' . $group_id );
			break;
		case 'update':
			check_admin_referer( 'update-groups', 'update-groups-nonce' );
			
			$groups_order = $_POST['group'];
			
			$parent_ids = $_POST['menu-item-parent-id'];
			$db_ids = $_POST['menu-item-db-id'];
			
			
			foreach($groups_order as $id => $group) {
				
				$group_reference = new BP_Groups_Group( $id );
				
				if( defined( 'BP_GROUP_HIERARCHY_IS_INSTALLED' ) && method_exists('BP_Groups_Hierarchy','get_tree') ) {
					// if group hierarchy is installed and available, check for tree changes
	
					$group_hierarchy = new BP_Groups_Hierarchy( $id );
	
					if( $parent_ids[$id] !== null && $group_hierarchy->parent_id != $parent_ids[$id] ) {
						$group_hierarchy->parent_id = $parent_ids[$id];
						$group_hierarchy->save();
					} else if($group_hierarchy->parent_id != $group['parent_id']) {
						$group_hierarchy->parent_id = $group['parent_id'];
						$group_hierarchy->save();
					}
					unset($group_hierarchy);
				}
				
				// check for group attribute changes
				$attrs_changed = array();
				if($group['name'] != $group_reference->name) {
					$group_reference->name = stripslashes( $group['name'] );
					$attrs_changed[] = 'name';
				}
				if($group['slug'] != $group_reference->slug) {
					$slug = groups_check_slug($group['slug']);
					if($slug == $group['slug']) {
						$group_reference->slug = $group['slug'];
						$attrs_changed[] = 'slug';
					}
				}
				if($group['description'] != $group_reference->description) {
					$group_reference->description = stripslashes( $group['description'] );
					$attrs_changed[] = 'description';
				}
				if( $group['status'] != $group_reference->status && groups_is_valid_status( $group['status'] ) ) {
					$group_reference->status = $group['status'];
					$attrs_changed[] = 'status';
				}
				if( ! isset( $group['forum_enabled'] ) || $group['forum_enabled'] != $group_reference->enable_forum ) {
					$group_reference->enable_forum = isset( $group['forum_enabled'] ) ? true : false ;
					$attrs_changed[] = 'enable_forum';
				}
				
				if(count($attrs_changed) > 0) {
					$group_reference->save();
				}
				
				// finally, let plugins run any other changes
				do_action( 'group_details_updated', $group_reference->id );
				do_action( 'bp_group_organizer_save_group_options', $group, $group_reference );
				
			}
			break;
		case 'import':
			
			if( ! isset( $_FILES['import_groups'] ) ) {
				// No files was uploaded
			}
			
			if( ! is_uploaded_file( $_FILES['import_groups']['tmp_name'] ) ) {
				// Not an uploaded file
				
				$errors = array(
					UPLOAD_ERR_OK        => sprintf(__( 'File uploaded successfully, but there is a problem. (%d)', 'bp-group-organizer' ), UPLOAD_ERR_OK ),
					UPLOAD_ERR_INI_SIZE  => sprintf(__( 'File exceeded PHP\'s maximum upload size. (%d)', 'bp-group-organizer' ), UPLOAD_ERR_INI_SIZE ),
					UPLOAD_ERR_FORM_SIZE => sprintf(__( 'File exceeded the form\'s maximum upload size. (%d)', 'bp-group-organizer' ), UPLOAD_ERR_FORM_SIZE ),
					UPLOAD_ERR_PARTIAL   => sprintf(__( 'File was only partially uploaded. (%d)', 'bp-group-organizer' ), UPLOAD_ERR_PARTIAL ),
					UPLOAD_ERR_NO_FILE   => sprintf(__( 'No uploaded file was found. (%d)', 'bp-group-organizer' ), UPLOAD_ERR_NO_FILE ),
					6	=> sprintf(__( 'No temporary folder could be found for the uploaded file. (%d)', 'bp-group-organizer' ), 6 ),
					7	=> sprintf(__( 'Could not write uploaded file to disk. (%d)', 'bp-group-organizer' ), 7 ),
					8	=> sprintf(__( 'Upload was stopped by a PHP extension. (%d)', 'bp-group-organizer' ), 8 )	// upload was stopped by a PHP extension
				);
				
				$messages[] = '<div class="error"><p>' . sprintf( __('', 'bp-group-organizer'), $errors[$_FILES['import_groups']['error']] ) . '</p></div>';
				break;
			}
			
			require_once dirname( __FILE__ ) . '/includes/group-organizer-import.php';
			
			if( $result = bp_group_organizer_import_csv_file( $_FILES['import_groups']['tmp_name'], $_POST['import'] ) ) {
				$messages[] = '<div class="updated"><p>' . implode( '<br />', $result ) . '</p></div>';
			} else {
				$messages[] = '<div class="error"><p>' . 'ERROR - IMPORT FAILED COMPLETELY' . '</p></div>';
			}
			
			break;
	}
	
	// Ensure the user will be able to scroll horizontally
	// by adding a class for the max menu depth.
	global $_wp_nav_menu_max_depth;
	$_wp_nav_menu_max_depth = 0;
	
	if( isset( $_REQUEST['screen'] ) && $_REQUEST['screen'] == 'import') {
		$action = 'import';
	} else {
		$action = 'organize';
	}
	
	if( $action == 'import') {
		$edit_markup = bp_group_organizer_import_export_page();
	} else {
		$edit_markup = bp_get_groups_to_edit( );
	}

?>
<div class="wrap">
	<h2><?php esc_html_e('Group Organizer'); ?></h2>
	<?php
	foreach( $messages as $message ) :
		echo $message . "\n";
	endforeach;
	?>
	<div <?php if( $action == 'organize' ) echo 'id="nav-menus-frame"'; ?>>
	<div id="menu-settings-column" class="metabox-holder nav-menus-php">
		<form id="group-organizer-meta" action="" class="group-organizer-meta" method="post" enctype="multipart/form-data">
			<input type="hidden" name="action" value="add-group" />
			<?php wp_nonce_field( 'add-group', 'group-settings-column-nonce' ); ?>
			<?php if( $action == 'organize' )
				do_meta_boxes( 'group-organizer', 'side', null ); 
			?>
		</form>
	</div><!-- /#menu-settings-column -->
	<div id="menu-management-liquid">
		<div id="menu-management" class="nav-menus-php">

			<div class="nav-tabs-wrapper">
			<div class="nav-tabs">
				<a href="<?php
					echo esc_url( remove_query_arg( array(
							'screen'
					) ) );
					?>" class="nav-tab <?php if( $action == 'organize' ) echo 'nav-tab-active'; ?>">
					<?php printf( '<abbr title="%s">%s</abbr>', esc_html__( 'Drag and drop your groups', 'bp-group-hierarchy' ), __( 'Organize', 'bp-group-organizer' ) ); ?>
				</a>
				<a href="<?php
					echo esc_url( add_query_arg( array(
							'screen' => 'import'
					) ) );
					?>" class="nav-tab <?php if( $action == 'import' ) echo 'nav-tab-active'; ?>">
					<?php printf( '<abbr title="%s">%s</abbr>', esc_html__( 'Import or export groups in bulk', 'bp-group-organizer' ), __( 'Import / Export', 'bp-group-organizer' ) ); ?>
				</a>
			</div>
			</div>


			<div class="menu-edit">
				<?php if( $action == 'organize' ) : ?>
				<form id="update-nav-menu" action="" method="post" enctype="multipart/form-data">
				<?php endif; ?>
					<div id="nav-menu-header">
						<div id="submitpost" class="submitbox">
							<div class="major-publishing-actions">
								<label class="menu-name-label howto open-label" for="menu-name">
									<span><?php _e('Group Organizer', 'bp-group-organizer'); ?></span>
								</label>
								<div class="publishing-action">
									<?php if( $action == 'organize' )
										submit_button( __( 'Save Groups', 'bp-group-organizer' ), 'button-primary menu-save', 'save_menu', false, array( 'id' => 'save_menu_header' ) ); 
									?>
								</div><!-- END .publishing-action -->
							</div><!-- END .major-publishing-actions -->
						</div><!-- END #submitpost .submitbox -->
						<?php
						wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
						wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
						wp_nonce_field( 'update-groups', 'update-groups-nonce' );
						?>
						<input type="hidden" name="action" value="update" />
					</div><!-- END #nav-menu-header -->
					<div id="post-body">
						<div id="post-body-content">
							<?php
							if ( isset( $edit_markup ) ) {
								if ( ! is_wp_error( $edit_markup ) )
									echo  $edit_markup;
							} else {
								echo '<div class="post-body-plain">';
								echo '<p>' . __('You don\'t yet have any groups.', 'bp-group-organizer') . '</p>';
								echo '</div>';
							}
							?>
						</div><!-- /#post-body-content -->
					</div><!-- /#post-body -->
					<div id="nav-menu-footer">
						<div class="major-publishing-actions">
						<div class="publishing-action">
							<?php if( $action == 'organize' )
								submit_button( __( 'Save Groups', 'bp-group-organizer' ), 'button-primary menu-save', 'save_menu', false, array( 'id' => 'save_menu_header' ) ); 
							?>
						</div>
						</div>
					</div><!-- /#nav-menu-footer -->
				<?php if( $action == 'organize' ) : ?>
				</form><!-- /#update-nav-menu -->
				<?php endif; ?>
			</div><!-- /.menu-edit -->
		</div><!-- /#menu-management -->
	</div><!-- /#menu-management-liquid -->
	</div><!-- /#nav-menus-frame -->
</div><!-- /.wrap-->

<?php	
}

function bp_group_organizer_import_export_page() {
	
	ob_start();
	?>
	<h2><?php _e( 'Import', 'bp-group-organizer' ) ?></h2>
	<form method="post" enctype="multipart/form-data">
		<p><?php printf( __('Import groups from a CSV file &mdash; see %s for details on the format.', 'bp-group-organizer' ), __( 'Notes', 'bp-group-organizer' ) ) ?></p>
		<input type="hidden" name="action" value="import" />
		<?php wp_nonce_field( 'import', 'importnonce' ); ?>
		<label for="import_groups"><?php _e( 'Import from File', 'bp-group-organizer' ) ?></label>
		<input type="file" name="import_groups" id="import_groups" />
		<br /><br />
		<fieldset>
			<legend><?php _e( 'Import Options', '' ); ?></legend>
			<input type="checkbox" name="import[continue_on_error]" id="continue-on-error" /> <label for="continue-on-error"><?php _e( 'Continue import even if some groups cannot be created', 'bp-group-organizer' ); ?></label><br />
			<?php do_action( 'group_organizer_import_options' ); ?>
		</fieldset>
		<h4><?php _e('Notes', 'bp-group-organizer' ) ?></h4>
		<ul>
			<li><?php printf(__( 'CSV files may supply any of these fields: %s', 'bp-group-organizer' ), '<code>id, creator_id, name, path, slug, description, status, enable_forum, date_created, parent_id</code>' ); ?></li>
			<li><?php printf(__( 'CSV files must have ONE of these fields: %s or %s', 'bp-group-organizer' ), '<code>slug</code>', '<code>path</code>' ); ?></li>
			<li><?php printf(__( 'If you are trying to recreate a hierarchy, CSV files must have either %s or all of %s', 'bp-group-organizer' ), '<code>path</code>', '<code>group_id, slug, parent_id</code>' ) ?></li>
			<li><?php _e( 'If a group slug is taken, a new one will be chosen by BuddyPress. This is not currently configurable.', 'bp-group-organizer' ); ?></li>
		</ul>
		<?php submit_button( __( 'Import Groups', 'bp-group-hierarchy' ) ); ?>
	</form>
	
	<h2><?php _e( 'Export', 'bp-group-organizer' ) ?></h2>
	<form method="post">
		<input type="hidden" name="action" value="export" />
		<?php wp_nonce_field( 'export', 'exportnonce' ); ?>
		<label for="organizer_export_format"><?php _e( 'Export Format', 'bp-group-organizer' ); ?></label>
		<select name="export_format" id="organizer_export_format">
			<option value="no_type" disabled="true" selected="true"><?php _e('Select a Format', 'bp-group-organizer' ) ?></option>
			<option value="csv_slug"><?php printf( __( 'CSV (%s)', 'bp-group-organizer' ), sprintf('%s, %s, %s', __('group ID', 'bp-group-organizer'), __('group slug', 'bp-group-organizer' ), __( 'parent ID' ,'bp-group-organizer' ) ) ) ?></option>
			<option value="csv_path"><?php printf( __( 'CSV (%s)', 'bp-group-organizer' ), __('group path', 'bp-group-organizer') ) ?></option>
			<?php do_action( 'group_organizer_list_export_types' ); ?>
		</select>
		<?php submit_button( __( 'Export Groups', 'bp-group-hierarchy' ) ); ?>
	</form>
	<?php
	$result = ob_get_clean();
	return $result;
}

/**
 * Serve group export directly to browser
 */
function bp_group_organizer_handle_export() {

	// Only handle requests for the group organizer admin page
	if( ! isset( $_REQUEST['page'] ) || $_REQUEST['page'] != 'group_organizer' )
		return false;

	// Only handle requests for the 'export' action
	if( ! isset( $_REQUEST['action'] ) || $_REQUEST['action'] != 'export' )
		return false;

	include dirname(__FILE__) . '/includes/group-organizer-import.php';

	switch( $_POST['export_format'] ) {
		case 'csv_slug':
			bp_group_organizer_export_csv('slug');
			break;
		case 'csv_path':
			bp_group_organizer_export_csv('path');
			break;
		case 'sitemap':
			bp_group_organizer_export_sitemap('xml');
			break;
		case 'no_type':
			return false;
			break;
		default:
			do_action( 'bp_group_organizer_export_' . $_POST['export_format'] );
			break;
	}
	
}

function bp_organizer_delete_group() {
	$group_id = (int)$_REQUEST['group_id'];
	
	if(!current_user_can('manage_options')) {
		die( __( 'Only administrators can delete groups with the organizer.', 'bp-group-organizer' ) );
	}
	check_ajax_referer('delete-group_' . $group_id);

	if(groups_delete_group( $group_id )) {
		do_action( 'groups_group_deleted', $group_id );
		die( 'success' );
	}
	die( __( 'Group delete failed.', 'bp-group-organizer' ) );
}

add_action( 'wp_ajax_bp_organizer_delete-group', 'bp_organizer_delete_group' );

?>