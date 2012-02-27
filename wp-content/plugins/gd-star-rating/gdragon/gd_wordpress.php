<?php

/*
Name:    gdWordPress
Version: 1.7.0
Author:  Milan Petrovic
Email:   milan@gdragon.info
Website: http://www.gdragon.info/

== Copyright ==

Copyright 2008-2010 Milan Petrovic (email: milan@gdragon.info)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if (!class_exists('gdWPGDSR')) {
    class gdWPGDSR {
        /**
         * Deactivates any plugin.
         *
         * @param string $plugin_name name of the plugin to deactivate
         */
        function deactivate_plugin($plugin_name) {
            $current = get_option('active_plugins');
            if(in_array($plugin_name, $current))
                array_splice($current, array_search($plugin_name, $current), 1);
            update_option('active_plugins', $current);
        }

        /**
         * Finds all users with specified role.
         *
         * @param string $role role to find
         * @return array found users
         */
        function get_users_with_role($role) {
            $wp_user_search = new WP_User_Search("", "", $role);
            return $wp_user_search->get_results();
        }

        /**
         * Gets current category id.
         *
         * @global object $wp_query WP query object
         * @return int category id
         */
        function get_current_category_id() {
            global $wp_query;

            if (!$wp_query->is_category) return 0;
            $cat_obj = $wp_query->get_queried_object();
            return $cat_obj->term_id;
        }

        /**
         * Get all subcategories of a category.
         *
         * @param int $cat category id
         * @param bool $hide_empty hid or show empty categories
         * @return array subcatories
         */
        function get_subcategories_ids($cat, $hide_empty = true) {
            $categories = get_categories(array("child_of" => $cat, "hide_empty" => $hide_empty));
            $results = array();
            foreach ($categories as $c) $results[] = $c->cat_ID;
            return $results;
        }

        /**
         * Get all custom fields from post meta.
         *
         * @global object $wpdb database object
         * @param bool $hidden include hidden fields
         * @return array field names
         */
        function get_all_custom_fieds($hidden = true) {
            global $wpdb;

            $sql = "select distinct meta_key from ".$wpdb->postmeta;
            if (!$hidden) $sql.= " where SUBSTR(meta_key, 1, 1) != '_'";
            $elements = $wpdb->get_results($sql);
            $result = array();
            foreach ($elements as $el) $result[] = $el->meta_key;
            return $result;
        }
    }

    if (!function_exists("wp_redirect_self")) {
        /**
         * Redirects back to the same page.
         */
        function wp_redirect_self() {
            wp_redirect($_SERVER['REQUEST_URI']);
        }
    }
}

if (!function_exists("_n")) {
    function _n() {
        $args = func_get_args();
        return call_user_func_array('__ngettext', $args);
    }
}

?>