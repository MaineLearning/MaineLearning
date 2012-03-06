<?php
/*
Plugin Name: Network Plugin Auditor
Plugin URI: http://bonsaibudget.com/wordpress/network-plugin-auditor/
Description: Add a column to your network admin to show which sites have each plugin active (on the plugin page), and which plugins are active on each site (on the sites page).
Version: 1.0
Author: Katherine Semel
Author URI: http://bonsaibudget.com/
Network: true
*/

class NetworkPluginAuditor {

    function NetworkPluginAuditor( ) {

        // On the network plugins page, add a column to show which blogs have this plugin active
        add_filter( 'manage_plugins-network_columns', array( &$this, 'add_plugins_column' ), 10, 1);
        add_action( 'manage_plugins_custom_column', array( &$this, 'manage_plugins_custom_column' ), 10, 3);

        // On the blog list page, show the plugins active on each blog
        add_filter( 'manage_sites-network_columns', array( &$this, 'add_sites_column' ), 10, 1);
        add_action( 'manage_sites_custom_column', array( &$this, 'manage_sites_custom_column' ), 10, 3);
    }

/* Plugins Page Functions *****************************************************/

    function add_plugins_column( $column_details ) {
        $column_details['active_blogs'] = _x( '<nobr>Active Blogs</nobr>', 'column name' );
        return $column_details;
    }

    function manage_plugins_custom_column( $column_name, $plugin_file, $plugin_data ) {
        $output = '<ul>';

        // Is this plugin network activated
        $active_on_network = is_plugin_active_for_network( $plugin_file );
        if ( $active_on_network ) {
            $output .= '<li>Network Activated</li>';
        }

        // Is this plugin Active on any blogs in this network?
        $active_on_blogs = $this->is_plugin_active_on_blogs( $plugin_file );

        // Loop through the blog list, gather details and append them to the output string
        foreach ( $active_on_blogs as $blog ) {

            $blog_details = get_blog_details( $blog, true );

            $blog_url  = $blog_details->siteurl;
            $blog_name = $blog_details->blogname;

            $output .= '<li><nobr><a title="Manage plugins on '.$blog_name.'" href="'.$blog_url.'/wp-admin/plugins.php">' . $blog_name . '</a></nobr></li>';
        }

        $output .= '</ul>';
        echo $output;
    }

/* Sites Page Functions *******************************************************/

    function add_sites_column( $column_details ) {
        $column_details['active_plugins'] = _x( 'Active Plugins', 'column name' );
        return $column_details;
    }

    function manage_sites_custom_column( $column_name, $blog_id ) {
        $output = '<ul>';

        // Get the active plugins for this blog_id
        $plugins_active_here = $this->get_active_plugins( $blog_id );
        $plugins_active_here = maybe_unserialize( $plugins_active_here );

        foreach ( $plugins_active_here as $plugin ) {
            $plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );
            $plugin_name = $plugin_data['Name'];
            $plugin_url  = $plugin_data['PluginURI'];

            if ( isset($plugin_url) ) {
                $output .= '<li><a href="' . $plugin_url . '" title="Visit the plugin url: ' . $plugin_url . '">' . $plugin_name . '</a></li>';

            } else {
                $output .= '<li>' . $plugin_name . '</li>';
            }
        }

        $output .= '</ul>';
        echo $output;
    }

/* Helper Functions ***********************************************************/

    // Determine if the given plugin is active on a list of blogs
    function is_plugin_active_on_blogs( $plugin_file ) {
        $active_on = array();

        // Get the list of blogs
        $blog_list = $this->get_blog_list( );

        if ( isset($blog_list) && $blog_list != false ) {
            foreach ( $blog_list as $blog ) {
                // If the plugin is active here then add it to the list
                if ( $this->is_plugin_active( $blog->blog_id, $plugin_file ) ) {
                    array_push( $active_on, $blog->blog_id );
                }
            }
        }
        return $active_on;
    }

    // Given a blog id and plugin path, determine if that plugin is active.
    function is_plugin_active( $blog_id, $plugin_file ) {
        // Get the active plugins for this blog_id
        $plugins_active_here = $this->get_active_plugins( $blog_id );

        // Is this plugin listed in the active blogs?
        if ( isset( $plugins_active_here ) && strpos( $plugins_active_here, $plugin_file ) > 0 ) {
            return true;
        } else {
            return false;
        }
    }

    // Get the list of blogs
    function get_blog_list( ) {
        global $wpdb;

        // Fetch the list of blogs
        $blog_list = $wpdb->get_results( $wpdb->prepare( "SELECT blog_id, domain FROM " . $wpdb->prefix . "blogs" ) );

        return $blog_list;
    }

    // Get the list of active plugins for a blog
    function get_active_plugins( $blog_id ) {
        global $wpdb;

        // Is the primary blog?
        if ( $blog_id != 1 ) {
            $query = "SELECT option_value FROM " . $wpdb->prefix.$blog_id . "_options WHERE option_name = 'active_plugins'";
        } else {
            $query = "SELECT option_value FROM " . $wpdb->prefix . "options WHERE option_name = 'active_plugins'";
        }

        $active_plugins = $wpdb->get_var( $wpdb->prepare( $query ) );

        return $active_plugins;
    }
}

$NetworkPluginAuditor = new NetworkPluginAuditor();

?>