<?php
/*
Plugin Name: BuddyPress Edit Group Slug
Plugin URI: http://buddypress.org/
Description: Allows group creators to manually edit group slug
Author: John James Jacoby
Version: 1.2
Tags: buddypress, groups, slug, edit
Author URI: http://buddypress.org/developers/johnjamesjacoby/
Network: true
*/

if ( !defined( 'BP_GROUP_CHANGE_SLUG' ) )
	define( 'BP_GROUP_CHANGE_SLUG', 'slug' );

/* Handle loading if/when BuddyPress is loaded */
function bp_groups_edit_slug_init() {
	bp_groups_edit_slug_wrapper();
}
if ( defined( 'BP_VERSION' ) || did_action( 'bp_include' ) )
	bp_groups_edit_slug_wrapper();
else
	add_action( 'bp_include', 'bp_groups_edit_slug_init' );


function bp_groups_edit_slug_wrapper() {
	class BP_Groups_Edit_Slug extends BP_Group_Extension {
		var $visibility = 'private';

		var $enable_nav_item = false;
		var $enable_create_step = true;
		var $enable_edit_item = true;

		/* Change this value to toggle on or off */
		var $has_caps = true;

		function bp_groups_edit_slug () {
			global $groups_template;

			$this->name = __( 'Group Slug', 'bp-groupslugs' );
			$this->slug = BP_GROUP_CHANGE_SLUG;

			$this->create_step_position = 11;
			$this->nav_item_position = 11;

			/* Generic check access */
			if ( $this->has_caps == false ) {
				$this->enable_create_step = false;
				$this->enable_edit_step = false;
			}

		}

		function create_screen () {
			global $bp, $groups_template;

			if ( !bp_is_group_creation_step( $this->slug ) )
				return false;
?>
			<div class="editfield">
				<label for=""><?php _e( 'Group Slug', 'bp-edit-group-slug' ) ?></label>
				<p><?php _e( 'This slug has been automatically created from the group name you entered in step 1.', 'bp-edit-group-slug' ) ?></p>
				<p><?php _e( 'You can keep it, or change it to something more accurate.', 'bp-edit-group-slug' ) ?></p>
				<div>
					<?php echo $this->get_group_slug_screen( $bp->groups->current_group->id ); ?>
				</div>
			</div>
<?php
			wp_nonce_field( 'groups_create_save_' . $this->slug );
		}

		function create_screen_save () {

			check_admin_referer( 'groups_create_save_' . $this->slug );

			$this->method = 'create';
			$this->save();
		}

		function edit_screen () {
			global $groups_template;

			if ( !bp_is_group_admin_screen( $this->slug ) )
				return false;

			?>

			<h2><?php echo attribute_escape ( $this->name ) ?></h2>

			<div class="editfield">
				<label for=""><?php _e( 'Group Slug', 'bp-edit-group-slug' ) ?></label>

				<p><?php _e( 'This slug has been automatically created from the group name you entered in step 1.', 'bp-edit-group-slug' ) ?></p>
				<p><?php _e( 'You can keep it, or change it to something more accurate.', 'bp-edit-group-slug' ) ?></p>

				<div class="slug-wrapper">
					<?php echo $this->get_group_slug_screen( $groups_template->group->id ); ?>
				</div>
			</div>

			<div class="form-submit">
				<input type="submit" name="save" value="<?php _e( "Update Slug", 'bp-edit-group-slug' ) ?>" />
			</div>
<?php
			wp_nonce_field( 'groups_edit_save_' . $this->slug );
		}

		function edit_screen_save () {
			global $bp;

			if ( !isset( $_POST['save'] ) )
				return false;

			check_admin_referer( 'groups_edit_save_' . $this->slug );

			$this->method = 'edit';
			$this->save();
		}

		function get_group_slug_screen ( $id, $new_title = null, $new_slug = null ) {
			global $bp;

			/* Get updated group data that group creation methods do not provide */
			$new_group = new BP_Groups_Group( $id );

			$permalink = $bp->root_domain . '/' . BP_GROUPS_SLUG . '/';
			$group_name = $new_group->group_name;
			$group_slug = $new_group->slug;

			$title = __( 'Current group slug', 'bp-edit-group-slug' );
			$group_name_html = '<input type="text" id="editable-group-name" name="editable-group-name" title="' . $title . '" value="' . $group_slug . '" />';

			$return = '<label for="editable-group-name">' . __( 'Permalink:', 'bp-edit-group-slug' ) . "</label>\n" . '<span id="sample-permalink">' . $permalink . "</span>" . $group_name_html;

			$return = apply_filters( 'bp_get_group_permalink_html', $return, $id, $new_title, $new_slug );

			return $return;
		}

		function save () {
			global $bp;

			$group_id = $_POST['group_id'];
			if ( !$group_id )
				$group_id = $bp->groups->current_group->id;

			/* Set error redirect based on save method */
			if ( $this->method == 'create' )
				$redirect_url = $bp->root_domain . '/' . $bp->groups->slug . '/create/step/' . $this->slug;
			else
				$redirect_url = bp_get_group_permalink( $bp->groups->current_group ) . 'admin/' . $this->slug;

			/* Slug cannot be empty */
			if ( empty( $_POST['editable-group-name'] ) ) {
				bp_core_add_message( __( 'Slug cannot be empty. Please try again.', 'bp-edit-group-slug' ), 'error' );
				bp_core_redirect( $redirect_url );
				exit();
			}

			/* Never trust an input box */
			$new_slug = sanitize_title( $_POST['editable-group-name'] );

			/* Check if slug exists and handle accordingly */
			$success = $this->check_slug( $group_id, $new_slug );

			/* Slug is no good or already taken */
			if ( !$success ) {
				bp_core_add_message( __( 'That slug is not available. Please try again.', 'bp-edit-group-slug' ), 'error' );
				bp_core_redirect( $redirect_url );
				exit();

			/* Slug is good so try to save it */
			} else {
				$save = $this->update_slug( $group_id, $new_slug );

				if ( false === $save ) {
					bp_core_add_message( __( 'An unknown error has occurred. Slug was not saved.', 'bp-edit-group-slug' ), 'error' );
					bp_core_redirect( $redirect_url );
					exit();
				} else {
					/* Reset error redirect based on save method */
					if ( $this->method != 'create' ) {
						/* Change variable for new redirect */
						$bp->groups->current_group->slug = $new_slug;

						$redirect_url = bp_get_group_permalink( $bp->groups->current_group ) . 'admin/' . $this->slug;

						bp_core_add_message( __( 'The group slug was saved successfully.', 'bp-edit-group-slug' ), 'success' );
						bp_core_redirect( $redirect_url );
						exit();
					}
				}
			}
		}

		function check_slug ( $id, $slug ) {
			global $bp;

			/* Allow save if no change */
			if ( $slug == $bp->groups->current_group->slug )
				return true;

			/* Group slugs cannot start with wp */
			if ( 'wp' == substr( $slug, 0, 2 ) )
				$slug = substr( $slug, 2, strlen( $slug ) - 2 );

			/* Don't allow forbidden names */
			if ( in_array( $slug, (array)$bp->groups->forbidden_names ) )
				return false;

			/* Run it through the BP core slug checker */
			if ( BP_Groups_Group::check_slug( $slug ) ) {
				if ( $slug != BP_Groups_Group::get_slug( $id ) ) {
					return false;
				}
			}

			/* Slug is good, return true */
			return true;
		}

		function update_slug ( $id, $slug ) {
			global $bp, $wpdb;

			if ( $id && $slug ) {
				$sql = $wpdb->prepare( "UPDATE {$bp->groups->table_name} SET slug = %s WHERE id = %d", $slug, $id );
				return $wpdb->query( $sql );
			}

			return false;
		}
	}
	bp_register_group_extension( 'BP_Groups_Edit_Slug' );
}

?>