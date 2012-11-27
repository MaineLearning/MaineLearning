<?php

/**
 * Functions for import / export
 */

/**
 * Export groups in CSV format
 * @param string|array $format short name of format OR list of columns
 * Column lists are based on properties of the Groups object, not columns in the database
 */
function bp_group_organizer_export_csv( $format ) {

	if( is_array( $format ) )
		$format = implode( ',', $format );

	// If Group Hierarchy is not installed, path is equivalent to slug
	if( $format == 'path' && ! bpgo_is_hierarchy_available() ) {
		$format == 'slug';
	}

	if( ! strpos( $format, ',' ) ) {
		// Short name was specified
		switch( $format ) {
			case 'slug':
				$fields = array(
					'id',
					'creator_id',
					'name',
					'slug',
					'description',
					'status',
					'enable_forum',
					'date_created',
//					'last_activity',
//					'total_member_count'
				);
				
				if( bpgo_is_hierarchy_available() ) {
					$fields[] = 'parent_id';
				}
				
				break;
			case 'path':
				$fields = array(
					'creator_id',
					'name',
					'path',
					'description',
					'status',
					'enable_forum',
					'date_created',
//					'last_activity',
//					'total_member_count'
				);
				break;
			default:
				$fields = apply_filters( 'bp_group_organizer_get_csv_fields_format_' . $format , array() );
				break;
		}
	} else {
		$fields = explode( ',', $format );
	}
	
	if( ! count( $fields ) )	return false;
	
	if( bpgo_is_hierarchy_available() ) {
		$groups_list = array(
			'groups' => BP_Groups_Hierarchy::get_tree()
		);
		$groups_list['total'] = count( $groups_list['groups'] );
	} else {
		$groups_list = BP_Groups_Group::get( 'alphabetical' );
	}
	
	header( 'Content-Type: application/force-download' );
	header( 'Content-Disposition: attachment; filename="' . 'bp-group-export.csv' . '";' );
	
	// Print header row
	echo implode(',', $fields ) . "\n";
	
	foreach( $groups_list['groups'] as $group ) {
		foreach( $fields as $key => $field ) {
			
			if( $field == 'path' ) {
				echo BP_Groups_Hierarchy::get_path( $group->id );
			} else if( in_array( $field, array( 'name', 'description' ) ) ) {
				echo '"' . stripslashes( $group->$field ) . '"';
			} else {
				echo $group->$field;
			}
			
			if( $key < count( $fields ) - 1 ) echo ',';
			
		}
		echo "\n";
	}
	
	die();
}

/**
 * Import groups from a CSV file
 * 
 * @param string FileName path to the CSV file
 * @param array Args associative array of options
 */
function bp_group_organizer_import_csv_file( $fileName, $args ) {
	
	$known_columns = array(
		'id',
		'creator_id',
		'name',
		'slug',
		'path',
		'description',
		'status',
		'enable_forum',
		'date_created',
		'parent_id'
	);
	$allowed_columns = apply_filters( 'group_organizer_import_columns', $known_columns );
	
	if( is_resource( $fileName ) ) {
		$file = $fileName;
	} else {
		$file = fopen( $fileName, 'rb' );
	}
	
	if( ! $file ) {
		// File was invalid or inaccessible
		return false;
	}
	
	$columns = fgetcsv( $file );

	$status = array();
	
	foreach( $columns as $column ) {
		if( ! in_array( $column, $allowed_columns) ) {
			// file contained invalid columns -- die?
		}
	}
	
	while( $row = fgetcsv( $file ) ) {
		
		$data = array();
		foreach( $row as $key => $field ) {
			$data[$columns[$key]] = trim($field);
		}
		
		if( bp_group_organizer_import_group( $data ) ) {
			
			$status[] = 'Imported ' . ( isset( $data['path'] ) ? $data['path'] : $data['slug'] ) . ' successfully.';
			
		} else {
			
			// group failed to import
			$status[] = 'Error importing ' . ( isset( $data['path'] ) ? $data['path'] : $data['slug'] ) . '.';
			
			if( ! isset( $args['continue_on_error'] ) ) {
				fclose( $file );
				return $status;
			}
			
		}
	}
	
	fclose( $file );
	return $status;
}

function bp_group_organizer_import_group( $group, $args = array() ) {
	
	if( empty( $group['name'] ) ) {
		return false;
	}
	
	if( isset( $group['path'] ) ) {
		if( bpgo_is_hierarchy_available() ) {

			// Try to place the group in the requested spot, but if the spot doesn't exist (e.g. because of slug conflicts)
			// then place it as far down the tree as possible
			$parent_path = $group['path'];
			do {
				
				$parent_path = dirname( $parent_path );
				$parent_id = BP_Groups_Hierarchy::get_id_from_slug( $parent_path );
				
			} while( $parent_path != '.' && $parent_id == 0 );
			$group['parent_id'] = $parent_id ? $parent_id : 0 ;
		}
		$group['slug'] = basename( $group['path'] );
		unset( $group['path'] );
	}
	
	$group['slug'] = groups_check_slug( $group['slug'] );

	$group_id = groups_create_group( $group );
	
	if( ! $group_id ) {
		return false;
	}
	
	groups_update_groupmeta( $group_id, 'total_member_count', 1);
	
	if( bpgo_is_hierarchy_available() ) {
		$obj_group = new BP_Groups_Hierarchy( $group_id );
		$obj_group->parent_id = (int)$group['parent_id'];
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
	
	do_action( 'bp_group_organizer_import_group', $group_id );

	return $group_id;
	
}

?>