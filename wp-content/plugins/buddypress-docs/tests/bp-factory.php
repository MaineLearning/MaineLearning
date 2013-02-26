<?php

class BP_UnitTest_Factory_For_Activity extends WP_UnitTest_Factory_For_Thing {

	function __construct( $factory = null ) {
		parent::__construct( $factory );

		$this->default_generation_definitions = array(
			'action'       => new WP_UnitTest_Generator_Sequence( 'Activity action %s' ),
			'component'    => buddypress()->activity->id,
			'content'      => new WP_UnitTest_Generator_Sequence( 'Activity content %s' ),
			'primary_link' => 'http://example.com',
			'type'         => 'activity_update',
		);
	}

	function create_object( $args ) {
		if ( ! isset( $args['user_id'] ) )
			$args['user_id'] = get_current_user_id();

		return $this->get_object_by_id( bp_activity_add( $args ) );
	}

	function update_object( $activity_id, $fields ) {
		$activity = new BP_Activity_Activity( $activity_id );

		foreach ( $fields as $field_name => $value ) {
			if ( isset( $activity->$field_name ) )
				$activity->$field_name = $value;
		}

		$activity->save();
		return $activity;
	}

	function get_object_by_id( $user_id ) {
		return new BP_Activity_Activity( $user_id );
	}
}

class BP_UnitTest_Factory_For_Group extends WP_UnitTest_Factory_For_Thing {

	function __construct( $factory = null ) {
		parent::__construct( $factory );

		$this->default_generation_definitions = array(
			'name'         => new WP_UnitTest_Generator_Sequence( 'Group %s' ),
			'description'  => new WP_UnitTest_Generator_Sequence( 'Group description %s' ),
			'slug'         => new WP_UnitTest_Generator_Sequence( 'group-slug-%s' ),
			'status'       => 'public',
			'enable_forum' => true,
			'date_created' => bp_core_current_time(),
		);
	}

	function create_object( $args ) {
		if ( ! isset( $args['creator_id'] ) ) {
			$args['creator_id'] = get_current_user_id();
		}

		$group_id = groups_create_group( $args );

		groups_update_groupmeta( $group_id, 'total_member_count', 1 );
		groups_update_groupmeta( $group_id, 'last_activity', bp_core_current_time() );

		return $this->get_object_by_id( $group_id );
	}

	function update_object( $group_id, $fields ) {
		$group = new BP_Groups_Group( $group_id );

		foreach ( $fields as $field_name => $value ) {
			if ( isset( $group->field_name ) )
				$group->field_name = $value;
		}

		$group->save();
		return $group;
	}

	function get_object_by_id( $group_id ) {
		return new BP_Groups_Group( $group_id );
	}
}
