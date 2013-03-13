<?php
/*
 * Import Export Class
 */

/**
 * Import Export Class
 * 
 * @since Types 1.2
 * @package Types
 * @subpackage Import Export
 * @version 0.1
 * @category core
 * @author srdjan <srdjan@icanlocalize.com>
 */
class WPCF_Import_Export
{

    function get_group_meta_keys() {
        return array(
            '_wp_types_group_terms',
            '_wp_types_group_post_types',
            '_wp_types_group_fields',
            '_wp_types_group_templates',
            '_wpcf_conditional_display',
        );
    }

    function get_group_meta( $group_id ) {
        $meta = get_post_custom( $group_id );
        $_meta = array();
        if ( !empty( $meta ) ) {
            foreach ( $meta as $meta_key => $meta_value ) {
                if ( in_array( $meta_key, $this->get_group_meta_keys()
                        )
                ) {
                    $_meta[$meta_key] = $meta_value[0];
                }
            }
        }

        return $_meta;
    }

    function generate_checksum( $type, $item_id = null ) {
        switch ( $type ) {
            case 'group':
                $checksum = $this->get_group_meta( $item_id );
                break;

            case 'field':
                $checksum = wpcf_admin_fields_get_field( $item_id );
                break;

            case 'custom_post_type':
                $checksum = wpcf_get_custom_post_type_settings( $item_id );

                break;

            case 'custom_taxonomy':
                $checksum = wpcf_get_custom_taxonomy_settings( $item_id );
                break;

            default:
                /*
                 * Enable $this->generate_checksum('test');
                 */
                $checksum = $type;
                break;
        }

        return md5( maybe_serialize( $checksum ) );
    }

    function checksum( $type, $item_id, $import_checksum ) {
        $checksum = '__not__';
        switch ( $type ) {
            case 'group':
                $checksum = $this->generate_checksum( $this->get_group_meta( $item_id ) );
                break;

            case 'field':
                $checksum = $this->generate_checksum( wpcf_admin_fields_get_field( $item_id ) );
                break;

            case 'custom_post_type':
                $checksum = $this->generate_checksum( 'custom_post_type',
                        $item_id );
                break;

            case 'custom_taxonomy':
                $checksum = $this->generate_checksum( 'custom_taxonomy',
                        $item_id );
                break;

            default:
                break;
        }
        return $checksum == strval( $import_checksum );
    }

    function item_exists( $type, $item_id ) {
        switch ( $type ) {
            case 'group':
                $check = wpcf_admin_fields_get_group( $item_id );
                break;

            case 'field':
                $check = wpcf_admin_fields_get_field( $item_id );
                break;

            case 'custom_post_type':
                $check = wpcf_get_custom_post_type_settings( $item_id );
                break;

            case 'custom_taxonomy':
                $check = wpcf_get_custom_taxonomy_settings( $item_id );
                break;

            default:
                return false;
                break;
        }
        return empty( $check );
    }

}
