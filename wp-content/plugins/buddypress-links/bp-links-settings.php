<?php

global $wpsf_settings;

// pro only vars
$dagger = ' <sup>&dagger;</sup>';
$pro_field_class = ( defined( 'BP_LINKS_PRO_VERSION' ) ) ? null : 'disabled';

// defaults
$defaults = array();

// global settings section
$wpsf_settings[] = array(
    'section_id' => 'global',
    'section_title' => __( 'Global Settings', 'buddypress-links' ),
//    'section_description' => '',
    'section_order' => 5,
    'fields' => array(
        array(
            'id' => 'avsize',
            'title' => __( 'List Avatar Size', 'buddypress-links' ),
            'desc' => __( 'Set the default avatar size for link lists.', 'buddypress-links' ),
            'type' => 'select',
            'std' => 100,
			'choices' => array(
				50 => 50,
				60 => 60,
				70 => 70,
				80 => 80,
				90 => 90,
				100 => 100,
				110 => 110,
				120 => 120,
				130 => 130
			)
        ),
        array(
            'id' => 'linklocal',
            'title' => __( 'Link to local page?', 'buddypress-links' ),
            'desc' => __( 'The default behavior is to link the title link to the local link page when a link is clicked. Set this to No to send them directly to the external url.', 'buddypress-links' ),
            'type' => 'radio',
            'std' => true,
			'choices' => array(
				1 => 'Yes',
				0 => 'No'
			)
        )
    )
);

// content settings section
$wpsf_settings[] = array(
    'section_id' => 'content',
    'section_title' => __( 'Content Settings', 'buddypress-links' ),
//    'section_description' => '',
    'section_order' => 10,
    'fields' => array(
        array(
            'id' => 'maxurl',
            'title' => __( 'Max. URL Characters', 'buddypress-links' ),
            'desc' => __( 'Set this to the maximum number of characters allowed for a link URL. (Must be 255 or lower)', 'buddypress-links' ),
            'type' => 'text',
            'std' => 255
        ),
        array(
            'id' => 'maxname',
            'title' => __( 'Max. Name Characters', 'buddypress-links' ),
            'desc' => __( 'Set this to the maximum number of characters allowed for a link name/title. (Must be 255 or lower)', 'buddypress-links' ),
            'type' => 'text',
            'std' => 125
        ),
        array(
            'id' => 'maxdesc',
            'title' => __( 'Max. Description Characters', 'buddypress-links' ),
            'desc' => __( 'Set this to the maximum number of characters allowed for a link description.', 'buddypress-links' ),
            'type' => 'text',
            'std' => 500
        ),
		array(
            'id' => 'reqdesc',
            'title' => __( 'Is description required?', 'buddypress-links' ),
            'desc' => __( 'By default, every link must have a description. Set this to No to allow empty descriptions.', 'buddypress-links' ),
            'type' => 'radio',
            'std' => true,
			'choices' => array(
				1 => 'Yes',
				0 => 'No'
			)
        ),
		array(
            'id' => 'catselect',
            'title' => __( 'Category Input Type', 'buddypress-links' ),
            'desc' => __( 'The default behavior is to use radio buttons to display categories on the create form. Set this to Select to use a select box instead.', 'buddypress-links' ),
            'type' => 'radio',
            'std' => false,
			'choices' => array(
				0 => 'Radio',
				1 => 'Select'
			)
        )
    )
);

// voting settings section
$wpsf_settings[] = array(
    'section_id' => 'voting',
    'section_title' => __( 'Voting Settings', 'buddypress-links' ),
//    'section_description' => '',
    'section_order' => 15,
    'fields' => array(
        array(
            'id' => 'change',
            'title' => __( 'Can members change their vote?', 'buddypress-links' ),
            'desc' => __( 'The default behavior is to allow members to change their vote. Set this to No to prevent vote changing.', 'buddypress-links' ),
            'type' => 'radio',
            'std' => true,
			'choices' => array(
				1 => 'Yes',
				0 => 'No'
			)
        ),
        array(
            'id' => 'activity',
            'title' => __( 'Record voting activity?', 'buddypress-links' ),
            'desc' => __( 'The default behavior is to record voting activity the first time a member votes on a link. Set this to No to disable voting activity recording.', 'buddypress-links' ),
            'type' => 'radio',
            'std' => true,
			'choices' => array(
				1 => 'Yes',
				0 => 'No'
			)
        )
    )
);

// profile settings section
$wpsf_settings[] = array(
    'section_id' => 'profile',
    'section_title' => __( 'Profile Settings', 'buddypress-links' ),
//    'section_description' => '',
    'section_order' => 20,
    'fields' => array(
        array(
            'id' => 'navpos',
            'title' => __( 'Nav Position', 'buddypress-links' ),
            'desc' => __( 'Enter a number to set the position of the Links tab in the profile main navigation.', 'buddypress-links' ),
            'type' => 'text',
            'std' => 100
        ),
        array(
            'id' => 'actnavpos',
            'title' => __( 'Activity Nav Position', 'buddypress-links' ),
            'desc' => __( 'Enter a number to set the position of the Links tab in the profile activity navigation.', 'buddypress-links' ),
            'type' => 'text',
            'std' => 35
        ),
        array(
            'id' => 'acthist',
            'title' => __( 'Max. Activity History', 'buddypress-links' ),
            'desc' => __( 'Limitations of the activity API require that we pass all link ids that we want to display activity for if we are limiting results to links owned by a single user. This settings allows you to override the default number of links that have recent entries in the activity stream which are passed to the activity API.', 'buddypress-links' ),
            'type' => 'text',
            'std' => 100
        )
    )
);

// groups settings section
$wpsf_settings[] = array(
    'section_id' => 'groups',
    'section_title' => __( 'Groups Settings', 'buddypress-links' ),
//    'section_description' => '',
    'section_order' => 25,
    'fields' => array(
        array(
            'id' => 'enable',
            'title' => __( 'Groups integration', 'buddypress-links' ) . $dagger,
            'desc' => __( 'Integration with the groups component is On by default. Set this to Off to disable all integration with groups.', 'buddypress-links' ),
            'type' => 'radio',
			'class' => $pro_field_class,
            'std' => true,
			'choices' => array(
				1 => 'On',
				0 => 'Off'
			)
        ),
        array(
            'id' => 'navpos',
            'title' => __( 'Nav Position', 'buddypress-links' ) . $dagger,
            'desc' => __( 'Enter a number to set the position of the Links tab in the groups navigation.', 'buddypress-links' ),
            'type' => 'text',
			'class' => $pro_field_class,
            'std' => 81
        ),
        array(
            'id' => 'acthist',
            'title' => __( 'Max. Activity History', 'buddypress-links' ) . $dagger,
            'desc' => __( 'This setting is identical to Profile Max Activity history, except it applies to links that have been shared with a single group.', 'buddypress-links' ),
            'type' => 'text',
			'class' => $pro_field_class,
            'std' => 100
        )
    )
);

?>