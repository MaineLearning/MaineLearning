<?php
//avoid direct calls to this file where wp core files not present
if (!function_exists('add_action')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

/*
 * Main admin page class - all options are here
 */
$new_bp_gtm_admin = new BP_GTM_ADMIN_PAGE();

class BP_GTM_ADMIN_PAGE {

    //constructor of class, PHP4 compatible construction for backward compatibility (until WP 3.1)
    function bp_gtm_admin_page() {
        add_filter('screen_layout_columns', array(&$this, 'on_screen_layout_columns'), 10, 2);
        add_action('admin_menu', array(&$this, 'on_admin_menu'));
        add_action('network_admin_menu', array(&$this, 'on_admin_menu'));
    }

    function on_screen_layout_columns($columns, $screen) {
        if ($screen == $this->pagehook)
            $columns[$this->pagehook] = 2;
        return $columns;
    }

    function on_admin_menu() {
        $this->pagehook = add_submenu_page('bp-general-settings', __('GTM System', 'bp_gtm'), __('GTM System', 'bp_gtm'), 'manage_options', 'bp-gtm-admin', array(&$this, 'on_show_page'));
        add_action('load-' . $this->pagehook, array(&$this, 'on_load_page'));
    }

    //will be executed if wordpress core detects this page has to be rendered
    // hook to implement new blocks
    function on_load_page() {
        wp_enqueue_script('common');
        wp_enqueue_script('wp-lists');
        wp_enqueue_script('postbox');

        // sidebar
        //add_meta_box('bp-gtm-admin-debug', __('Dev print_var(bp_gtm & actions)', 'bp_gtm'), array(&$this, 'on_bp_gtm_admin_debug'), $this->pagehook, 'side', 'core');
        add_meta_box('bp-gtm-admin-misc', __('Display/Hide Some Features', 'bp_gtm'), array(&$this, 'on_bp_gtm_admin_misc'), $this->pagehook, 'side', 'core');
        add_meta_box('bp-gtm-admin-actions', __('Extra Actions for Admin', 'bp_gtm'), array(&$this, 'on_bp_gtm_admin_actions'), $this->pagehook, 'side', 'core');
        add_meta_box('bp-gtm-admin-themes', __('GTM Themes for Front-end', 'bp_gtm'), array(&$this, 'on_bp_gtm_admin_themes'), $this->pagehook, 'side', 'core');
        //add_meta_box('bp-gtm-admin-move-data', __('Export/Import GTM Data', 'bp_gtm'), array(&$this, 'on_bp_gtm_admin_move_data'), $this->pagehook, 'side', 'core');
        add_meta_box('bp-gtm-admin-deactivate', __('Deactivation Options', 'bp_gtm'), array(&$this, 'on_bp_gtm_admin_deactivate'), $this->pagehook, 'side', 'core');
        // main content - normal
        add_meta_box('bp-gtm-admin-groups', __('Groups Selection', 'bp_gtm'), array(&$this, 'on_bp_gtm_admin_groups'), $this->pagehook, 'normal', 'core');
        add_meta_box('bp-gtm-admin-roles', __('Default Global GTM Roles', 'bp_gtm'), array(&$this, 'on_bp_gtm_admin_roles'), $this->pagehook, 'normal', 'core');
        //add_meta_box('bp-gtm-admin-files', __('Tasks/Projects/Discussion Posts Files Management', 'bp_gtm'), array(&$this, 'on_bp_gtm_admin_files'), $this->pagehook, 'normal', 'core');
        add_meta_box('bp-gtm-admin-labes', __('Change Labels Names', 'bp_gtm'), array(&$this, 'on_bp_gtm_admin_labels'), $this->pagehook, 'normal', 'core');
        add_meta_box('bp-gtm-admin-import', __('Import All Groups Users', 'bp_gtm'), array(&$this, 'on_bp_gtm_admin_import'), $this->pagehook, 'normal', 'core');
    }

    // save all inputed values
    function save_data($bp_gtm) {
        if (isset($_POST['saveData'])) {
            // on/off data
            $bp_gtm['deactivate'] = $_POST['bp_gtm_deactivate'];
            $bp_gtm['mce'] = $_POST['bp_gtm_mce'];
            $bp_gtm['p_todo'] = $_POST['bp_gtm_p_todo'];
            $bp_gtm['display_activity'] = $_POST['bp_gtm_display_activity'];
            $bp_gtm['display_activity_discuss'] = $_POST['bp_gtm_display_activity_discuss'];

            if ($_POST['bp_gtm_allgroups'] == 'all') {
                $bp_gtm['groups'] = 'all';
            } else {
                $bp_gtm['groups'] = $_POST['bp_gtm_groups'];
            }

            // roles data
            $bp_gtm['def_g_role'] = $_POST['def_g_role'];
            $bp_gtm['def_admin_g_role'] = $_POST['def_admin_g_role'];

            // template system
            if (!empty($_POST['bp_gtm_theme'])) {
                $bp_gtm['theme'] = $_POST['bp_gtm_theme'];
            }
            // files data
            $bp_gtm['files'] = $_POST['bp_gtm_files'];
            if (isset($_POST['bp_gtm_files_count']) && is_numeric($_POST['bp_gtm_files_count']))
                $bp_gtm['files_count'] = $_POST['bp_gtm_files_count'];
            if (isset($_POST['bp_gtm_files_size']) && is_numeric($_POST['bp_gtm_files_size']))
                $bp_gtm['files_size'] = $_POST['bp_gtm_files_size'];
            $bp_gtm['files_types'] = $_POST['bp_gtm_files_types'];

            if (trim($_POST['label_gtm_system']) != '' && trim($_POST['label_assignments']) != '') {
                $bp_gtm['label_gtm_system'] = stripslashes(apply_filters('bp_gtm_labes', $_POST['label_gtm_system']));
                $bp_gtm['label_assignments'] = stripslashes(apply_filters('bp_gtm_labes', $_POST['label_assignments']));
            }

            update_option('bp_gtm', $bp_gtm);

            $bp_gtm_actions = $_POST['bp_gtm_actions'];
            update_option('bp_gtm_actions', $bp_gtm_actions);

            if (!empty($_POST['bp_gtm_groups_own_roles']))
                $bp_gtm_groups_own_roles = $_POST['bp_gtm_groups_own_roles'];
            else
                $bp_gtm_groups_own_roles = 'no';
            update_option('bp_gtm_groups_own_roles', $bp_gtm_groups_own_roles);

            bp_gtm_save_roles_actions($_POST['role']);

            echo "<div id='message' class='updated fade'><p>" . __('All changes were saved. Go and check results!', 'bp_gtm') . "</p></div>";
        }elseif (isset($_POST['importUsers'])) {
            bp_gtm_import_users();
        } elseif (isset($_POST['exportData'])) {
            bp_gtm_export_data();
        } elseif (isset($_POST['importData'])) {
            bp_gtm_import_data();
        }
        return $bp_gtm;
    }

    //executed to show the plugins complete admin page
    function on_show_page() {
        global $bp, $wpdb, $screen_layout_columns;
        ?>

        <div id="bp-gtm-admin-general" class="wrap">
            <?php screen_icon('options-general'); ?>
            <h2><?php _e('BP GTM System', 'bp_gtm') ?> <sup><em><?php echo 'v' . GTM_VERSION; ?></em></sup> &rarr; <?php _e('Group Tasks Management', 'bp_gtm') ?></h2>

            <?php
            $bp_gtm = get_option('bp_gtm');
            $bp_gtm = $this->save_data($bp_gtm);
            ?>

            <form action="<?php echo network_admin_url() . 'admin.php?page=bp-gtm-admin' ?>" id="bp-gtm-form" method="post" enctype="multipart/form-data">
                <?php wp_nonce_field('bp-gtm-admin-general'); ?>
                <?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false); ?>
                <?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false); ?>

                <div id="poststuff" class="metabox-holder<?php echo 2 == $screen_layout_columns || is_multisite() ? ' has-right-sidebar' : ''; ?>">
                    <div id="side-info-column" class="inner-sidebar">
                        <div class="save-button">
                            <input type="submit" value="<?php _e('Save Changes', 'bp_gtm') ?>" class="button-primary" name="saveData"/>
                        </div>
                        <?php do_meta_boxes($this->pagehook, 'side', $bp_gtm); ?>
                    </div>
                    <div id="post-body" class="has-sidebar">
                        <div id="post-body-content" class="has-sidebar-content">
                            <?php do_meta_boxes($this->pagehook, 'normal', $bp_gtm); ?>
                            <p>
                                <input type="submit" value="<?php _e('Save Changes', 'bp_gtm') ?>" class="button-primary" name="saveData"/>
                            </p>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <script type="text/javascript">
            //<![CDATA[
            jQuery(document).ready( function($){
                $('.if-js-closed').removeClass('if-js-closed').addClass('closed');
                postboxes.add_postbox_toggles('<?php echo $this->pagehook; ?>');
            });
            //]]>
        </script>
        <?php
    }

    /*
     * Sidebar Blocks
     */

    function on_bp_gtm_admin_debug($bp_gtm) {
        echo '<p><strong>Main:</strong>';
        print_var($bp_gtm);
        echo '</p>';

        echo '<p><strong>Actions:</strong>';
        $bp_gtm_actions = get_option('bp_gtm_actions');
        print_var($bp_gtm_actions);
        echo '</p>';

        echo '<p><strong>Groups own roles:</strong>';
        $bp_gtm_groups_own_roles = get_option('bp_gtm_groups_own_roles');
        print_var($bp_gtm_groups_own_roles);
        echo '</p>';
    }

    function on_bp_gtm_admin_misc($bp_gtm) {
        // Rich Editor (TinyMCE)
        echo '<p>' . __('Do you want to use Rich Editor (TinyMCE) to format projects/tasks descriptions with visual editor?', 'bp_gtm') . '</p>';
        echo '<p><input name="bp_gtm_mce" id="bp_gtm_mce_on" type="radio" value="on" ' . ('on' == $bp_gtm['mce'] ? 'checked="checked" ' : '') . '/> <label for="bp_gtm_mce_on">' . __('Enable', 'bp_gtm') . '</label></p>
            <p><input name="bp_gtm_mce" id="bp_gtm_mce_off" type="radio" value="off" ' . ('off' == $bp_gtm['mce'] ? 'checked="checked" ' : '') . '/> <label for="bp_gtm_mce_off">' . __('Disable', 'bp_gtm') . '</label></p>';

        echo '<hr />';

        // personal Assignments link
        echo '<p>' . __('Do you want to display for each user his personal Assignments page under My Account menu?', 'bp_gtm') . '</p>';
        echo '<p><input name="bp_gtm_p_todo" id="bp_gtm_p_todo_on" type="radio" value="on" ' . ('on' == $bp_gtm['p_todo'] ? 'checked="checked" ' : '') . '/> <label for="bp_gtm_p_todo_on">' . __('Enable', 'bp_gtm') . '</label></p>
            <p><input name="bp_gtm_p_todo" id="bp_gtm_p_todo_off" type="radio" value="off" ' . ('off' == $bp_gtm['p_todo'] ? 'checked="checked" ' : '') . '/> <label for="bp_gtm_p_todo_off">' . __('Disable', 'bp_gtm') . '</label></p>';

        echo '<hr />';

        // notification entry in feed for created tasks and projects
        echo '<p>' . __('Do you want to display a notification entry in activity feed for newly created tasks and projects?', 'bp_gtm') . '</p>';
        echo '<p><input name="bp_gtm_display_activity" id="bp_gtm_display_activity_on" type="radio" value="on" ' . ('on' == $bp_gtm['display_activity'] ? 'checked="checked" ' : '') . '/> <label for="bp_gtm_display_activity_on">' . __('Enable', 'bp_gtm') . '</label></p>
            <p><input name="bp_gtm_display_activity" id="bp_gtm_display_activity_off" type="radio" value="off" ' . ('off' == $bp_gtm['display_activity'] ? 'checked="checked" ' : '') . '/> <label for="bp_gtm_display_activity_off">' . __('Disable', 'bp_gtm') . '</label></p>';

        echo '<hr />';

        // notification entry in feed for created discussion posts
        echo '<p>' . __('Do you want to display a notification entry in activity feed for all discussion posts of any tasks and projects?', 'bp_gtm') . '</p>';
        echo '<p><input name="bp_gtm_display_activity_discuss" id="bp_gtm_display_activity_discuss_on" type="radio" value="on" ' . ('on' == $bp_gtm['display_activity_discuss'] ? 'checked="checked" ' : '') . '/> <label for="bp_gtm_display_activity_discuss_on">' . __('Enable', 'bp_gtm') . '</label></p>
            <p><input name="bp_gtm_display_activity_discuss" id="bp_gtm_display_activity_discuss_off" type="radio" value="off" ' . ('off' == $bp_gtm['display_activity_discuss'] ? 'checked="checked" ' : '') . '/> <label for="bp_gtm_display_activity_discuss_off">' . __('Disable', 'bp_gtm') . '</label></p>';
        //echo '<hr />';
    }

    function on_bp_gtm_admin_themes($bp_gtm) {
        echo '<p>' . __('Please choose here a theme, that will be used on frontend for BP GTM System:', 'bp_gtm') . '</p>';
        $dirs = bp_gtm_get_themes_dirs();
        echo '<p><select name="bp_gtm_theme" class="admin-temes">';
        foreach ($dirs as $key=>$theme) {
            if ($theme[0] == '.' || $theme[0] == '..')
                continue;
            if ($bp_gtm['theme'] == $theme)
                $selected = 'selected="selected"'; else
                $selected = '';
                $theme_name = !is_numeric($key)? $theme . ' - from theme directory':$theme;
            echo '<option ' . $selected . ' value="' . $theme . '">' . $theme_name . '</a>';
        }
        echo '</select></p>';
        echo '<p>' . __('Be patient, if there is no <code>index.php</code> in that theme, then you will be redirected to the site main page.', 'bp_gtm') . '</p>';
    }

    function on_bp_gtm_admin_actions() {
        $bp_gtm_actions = get_option('bp_gtm_actions');
        echo '<p>' . __('Display menu for changing members roles in groups members and involved lists?', 'bp_gtm') . '</p>';
        echo '<p><input name="bp_gtm_actions[role]" id="bp_gtm_actions_role_on" type="radio" value="on" ' . ('on' == $bp_gtm_actions['role'] ? 'checked="checked" ' : '') . '/> <label for="bp_gtm_actions_role_on">' . __('Enable', 'bp_gtm') . '</label></p>
            <p><input name="bp_gtm_actions[role]" id="bp_gtm_actions_role_off" type="radio" value="off" ' . ('off' == $bp_gtm_actions['role'] ? 'checked="checked" ' : '') . '/> <label for="bp_gtm_actions_role_off">' . __('Disable', 'bp_gtm') . '</label></p>';
    }

    function on_bp_gtm_admin_move_data() {
        echo '<p class="center"><input  name="exportData" type="submit" value="' . __('Export All GTM Data', 'bp_gtm') . '" class="button-primary"/></p>';
        echo '<hr />';
        echo '<p><input type="file" name="importFile" id="file"></p>
                <p class="center"><input  name="importData" type="submit" value="' . __('Import Data', 'bp_gtm') . '" class="button-primary"/></p>';
    }

    function on_bp_gtm_admin_deactivate($bp_gtm) {
        echo '<p>' . __('Please check here how you would like to uninstall BP GTM System:', 'bp_gtm') . '</p>';
        echo '<p><input name="bp_gtm_deactivate" type="radio" value="total" ' . ('total' == $bp_gtm['deactivate'] ? 'checked="checked" ' : '') . '/> ' . __('Deactivate the plugin and delete all options and tables from the DB', 'bp_gtm') . '</p>
            <p><input name="bp_gtm_deactivate" type="radio" value="only" ' . ('only' == $bp_gtm['deactivate'] ? 'checked="checked" ' : '') . '/> ' . __('Deactivate the plugin only, all saved data won\'t be touched', 'bp_gtm') . '</p>';
        echo '<p>' . __('After choosing deactivation method you could go to <a href="plugins.php?plugin_status=all">Plugins\'s page</a> and deactivate the plugin safely.', 'bp_gtm') . '</p>';
    }

    /*
     * Main Content Blocks
     */

    function on_bp_gtm_admin_groups($bp_gtm) {
        global $bp;
        ?>
        <table id="bp-gtm-admin-table" class="widefat link-group">
            <thead>
                <tr class="header">
                    <td colspan="2"><?php _e('Which groups should have GTM System turned on?', 'bp_gtm') ?></td>
                    <td class="own_roles"><?php _e('Own roles?', 'bp_gtm') ?></td>
                </tr>
            </thead>
            <tbody id="the-list">
                <tr>
                    <td><input type="checkbox" class="bp_gtm_allgroups" name="bp_gtm_allgroups" <?php echo ('all' == $bp_gtm['groups']) ? 'checked="checked" ' : ''; ?> value="all" /></td>
                    <td><?php _e('All groups', 'bp_gtm') ?></td>
                    <td class="own_roles">&nbsp;</td>
                </tr>
                <?php
                $arg['type'] = 'alphabetical';
                $arg['per_page'] = '1000';
                if (bp_has_groups($arg)) {
                    while (bp_groups()) : bp_the_group();
                        $description = preg_replace(array('<<p>>', '<</p>>', '<<br />>', '<<br>>'), '', bp_get_group_description_excerpt());
                        echo '<tr>
                                <td><input name="bp_gtm_groups[' . bp_get_group_id() . ']" class="bp_gtm_groups" type="checkbox" ' . ( ('all' == $bp_gtm['groups']) || ($bp_gtm['groups'][bp_get_group_id()] == 'on') ? 'checked="checked" ' : '') . 'value="on" /></td>
                                <td><a href="' . bp_get_group_permalink() . $bp->gtm->slug . '/" target="_blank">' . bp_get_group_name() . '</a> &rarr; ' . $description . '</td>
                                <td class="own_roles"><input name="bp_gtm_groups_own_roles[' . bp_get_group_id() . ']" class="bp_gtm_groups_own_roles" type="checkbox" ' . ( ((!empty($bp_gtm['groups_own_roles']) || !empty($bp_gtm['groups_own_roles'][bp_get_group_id()])) && ('all' == $bp_gtm['groups_own_roles']) || (!empty($bp_gtm['groups_own_roles'][bp_get_group_id()]) && $bp_gtm['groups_own_roles'][bp_get_group_id()] == bp_get_group_id())) ? 'checked="checked" ' : '') . 'value="' . bp_get_group_id() . '" /></td>
                            </tr>';
                    endwhile;
                }
                ?>
            </tbody>
            <tfoot>
                <tr class="header">
                    <td><input type="checkbox" class="bp_gtm_allgroups" name="bp_gtm_allgroups" <?php echo ('all' == $bp_gtm['groups']) ? 'checked="checked" ' : ''; ?> value="all" /></td>
                    <td><?php _e('All groups', 'bp_gtm') ?></td>
                    <td>&nbsp;</td>
                </tr>
            </tfoot>
        </table>
        <?php
    }

    function on_bp_gtm_admin_roles($bp_gtm) {
        global $wpdb, $bp;

        // getting all default roles
        $roles = $wpdb->get_results($wpdb->prepare("
                    SELECT *
                    FROM {$bp->gtm->table_roles}
                    WHERE `group_id` = '0'
                    ORDER BY `id` ASC
                "));

        echo '
        <div class="def_roles">
            <ul class="def_roles_list">';
        foreach ($roles as $role) {
            bp_gtm_role_actions($role);
        }
        echo '</ul><!-- /def_roles_list -->
            <div class="new_role">
                <input name="new_role" id="new_role" type="text" value="" />
                <a href="#" id="add_new_role" class="button">' . __('Add New Default Role', 'bp_gtm') . '</a>
            </div>
        </div>';
        ?>
        <p><?php _e('Choose a default role for a new member of any group:', 'bp_gtm') ?>
            <select name="def_g_role" id="def_g_role">
                <?php
                foreach ($roles as $role) {
                    echo '<option ' . ($bp_gtm['def_g_role'] == $role->id ? 'selected="selected"' : '') . 'value="' . $role->id . '">' . stripslashes($role->role_name) . '</option>';
                }
                ?>
            </select>
        </p>
        <p>
            <?php _e('Choose a default role for a group creator in newly created group:', 'bp_gtm') ?>
            <select name="def_admin_g_role" id="def_admin_g_role">
                <?php
                foreach ($roles as $role) {
                    echo '<option ' . ($bp_gtm['def_admin_g_role'] == $role->id ? 'selected="selected"' : '') . 'value="' . $role->id . '">' . stripslashes($role->role_name) . '</option>';
                }
                ?>
            </select>
        </p>
        <?php
    }

    function on_bp_gtm_admin_labels($bp_gtm) {
        echo '<p>' . __('Here you can change some GTM labels that are used throughout your site.', 'bp_gtm') . '</p>';
        ?>
        <table class="admin_labels">
            <tr>
                <td><input name="label_gtm_system" id="label_gtm_system" type="text" value="<?php echo $bp_gtm['label_gtm_system'] ?>" /></td>
                <td><?php _e('Used in group menu navigation - to reach the list of group tasks or projects', 'bp_gtm'); ?></td>
            </tr>
            <tr>
                <td><input name="label_assignments" id="label_assignments" type="text" value="<?php echo $bp_gtm['label_assignments'] ?>" /></td>
                <td><?php _e('Used in user personal navigation under My Account menu', 'bp_gtm'); ?></td>
            </tr>
        </table>
        <?php
    }

    function on_bp_gtm_admin_import($bp_gtm) {
        global $bp, $wpdb;
        // getting all default roles
        $roles = $wpdb->get_results($wpdb->prepare("
                    SELECT *
                    FROM {$bp->gtm->table_roles}
                    WHERE `group_id` = '0'
                    ORDER BY `id` ASC
                "));
        echo '<p>' . __('Using this menu you can easily import all group members from any group into GTM System role tables.<br>
                    This action is needed when you had a community for some time and then decided to use this awesome plugin.<br>
                    You should do this <strong>only once</strong> - just after BP GTM System activation.', 'bp_gtm') . '</p>';

        echo '<p>' . __('Please choose here, whom you would like group users to be after importing:', 'bp_gtm') . '</p>';

        echo '<p>';
        echo __('Administrators', 'bp_gtm') . ' &rarr; <select name="import_group_admins" id="import_group_admins">';
        foreach ($roles as $role) {
            echo '<option ' . ($role->id == '1' ? 'selected="selected"' : '') . 'value="' . $role->id . '">' . stripslashes($role->role_name) . '</option>';
        }
        echo '</select>';
        echo '</p>';

        echo '<p>';
        echo __('Moderators', 'bp_gtm') . ' &rarr; <select name="import_group_mods" id="import_group_mods">';
        foreach ($roles as $role) {
            echo '<option ' . ($role->id == '2' ? 'selected="selected"' : '') . 'value="' . $role->id . '">' . stripslashes($role->role_name) . '</option>';
        }
        echo '</select>';
        echo '</p>';

        echo '<p>';
        echo __('Ordinary Members', 'bp_gtm') . ' &rarr; <select name="import_group_members" id="import_group_members">';
        foreach ($roles as $role) {
            echo '<option ' . ($role->id == '5' ? 'selected="selected"' : '') . 'value="' . $role->id . '">' . stripslashes($role->role_name) . '</option>';
        }
        echo '</select>';
        echo '</p>';

        echo '<p class="center"><input  name="importUsers" type="submit" value="' . __('Import Users', 'bp_gtm') . '" class="button-primary"/></p>';
    }

}

/*
 * Export / import functions for users/data
 */

function bp_gtm_import_users() {
    global $wpdb, $bp;
    // get all groups to work with
    $groups = $wpdb->get_results($wpdb->prepare("SELECT id FROM {$bp->groups->table_name}"));
    // get all users for all groups, group them according to their roles
    foreach ($groups as $group) {
        $admins[$group->id] = $wpdb->get_results($wpdb->prepare("SELECT user_id FROM {$bp->groups->table_name_members} WHERE group_id = %d AND is_admin = 1 AND is_banned = 0", $group->id));
        $mods[$group->id] = $wpdb->get_results($wpdb->prepare("SELECT user_id FROM {$bp->groups->table_name_members} WHERE group_id = %d AND is_mod = 1 AND is_banned = 0", $group->id));
        $members[$group->id] = $wpdb->get_results($wpdb->prepare("SELECT user_id FROM {$bp->groups->table_name_members} WHERE group_id = %d AND is_mod = 0 AND is_admin = 0 AND is_banned = 0", $group->id));
    }

    // import all admins
    foreach ((array) $admins as $group_id => $users) {
        if (empty($users)) {
            $done_admins = true;
            continue;
        }
        foreach ($users as $user) {
            if (empty($user))
                continue;
            $done_admins = bp_gtm_change_user_role($user->user_id, $_POST['import_group_admins'], $group_id, $importer = true);
        }
    }

    // import all mods
    foreach ((array) $mods as $group_id => $users) {
        if (empty($users)) {
            $done_mods = true;
            continue;
        }
        foreach ($users as $user) {
            if (empty($user))
                continue;
            $done_mods = bp_gtm_change_user_role($user->user_id, $_POST['import_group_mods'], $group_id, $importer = true);
        }
    }

    // import all usual members
    foreach ((array) $members as $group_id => $users) {
        if (empty($users)) {
            $done_members = true;
            continue;
        }
        foreach ($users as $user) {
            if (empty($user))
                continue;
            $done_members = bp_gtm_change_user_role($user->user_id, $_POST['import_group_members'], $group_id, $importer = true);
        }
    }

    // display appropriate message
    if ($done_admins && $done_mods && $done_members)
        echo "<div id='message' class='updated fade'><p>" . __('All users from all groups were successfully imported', 'bp_gtm') . "</p></div>";
    else
        echo "<div id='message' class='error'><p>" . __('Some error occured while importing users. Perhaps, you have already imported all of them?', 'bp_gtm') . "</p></div>";
}

function bp_gtm_export_data() {
    global $bp, $wpdb;

    require( ABSPATH . WPINC . '/class-json.php' );
    $json = new Services_JSON();

    if (!file_exists(WP_CONTENT_DIR . '/gtm'))
        wp_mkdir_p(WP_CONTENT_DIR . '/uploads/gtm', 0777);

    $file_stamp = 'all_data_' . date('Y') . '-' . date('m') . '-' . date('d');
    $dir_stamp = WP_CONTENT_DIR . '/uploads/gtm/';
    $url_stamp = WP_CONTENT_URL . '/uploads/gtm/' . $file_stamp . '.zip';
    $file = $dir_stamp . $file_stamp;

    $data->tasks = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$bp->gtm->table_tasks}"));
    $data->projects = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$bp->gtm->table_projects}"));
    $data->resps = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$bp->gtm->table_resps}"));
    $data->discuss = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$bp->gtm->table_discuss}"));
    $data->terms = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$bp->gtm->table_terms}"));
    $data->taxon = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$bp->gtm->table_taxon}"));
    $data->roles = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$bp->gtm->table_roles}"));
    $data->roles_caps = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$bp->gtm->table_roles_caps}"));

    if (file_put_contents($file . '.json', $json->encode($data))) {
        chdir(WP_CONTENT_DIR . '/uploads/gtm');
        exec("zip $file_stamp.zip $file_stamp.json");
        unlink($file . '.json');
        echo "<div id='message' class='updated fade'><p>" . sprintf(__('GTM data backup was successfully created in <code>/wp-content/uploads/gtm/</code>. <a href="%s">Download that file</a>', 'bp_gtm'), $url_stamp) . "</p></div>";
    } else {
        echo '<div id="message" class="updated fade"><p>' . __('There were some errors while exporting data. Please try again.', 'bp_gtm') . '</p></div>';
    }
}

function bp_gtm_import_data() {
    global $bp, $wpdb;

    $allowed_filetypes = array('.zip', '.json');
    $upload_path = WP_CONTENT_DIR . '/uploads/gtm/tmp/'; // where to upload
    if (!file_exists($upload_path))
        mkdir($upload_path, 0777, true);
    $filename = $_FILES['importFile']['name']; // what's the name
    $ext = substr($filename, strpos($filename, '.'), strlen($filename) - 1); // what's the extension

    if (!in_array($ext, $allowed_filetypes)) { //not allowed file
        echo '<div id="message" class="updated fade"><p>' . __('File with GTM data should have .zip or .json extension', 'bp_gtm') . '</p></div>';
        return false;
    }
    if (!is_writable($upload_path))
        chmod($upload_path, 0777); // make dir writable
    if (move_uploaded_file($_FILES['importFile']['tmp_name'], $upload_path . $filename)) {
        echo '<div id="message" class="updated fade"><p>' . __('File was successfully uploaded.', 'bp_gtm') . '</p></div>';

        if ($ext == '.zip') {
            // need class to work with achive
            require( ABSPATH . 'wp-admin/includes/class-pclzip.php' );
            $zip = new PclZip($upload_path . $filename);
            $zip->extract($upload_path);
            unlink($upload_path . $filename); // delete not needed zip file
            $filename = str_replace('.zip', '.json', $filename);
        }

        // need class to work with json
        require( ABSPATH . WPINC . '/class-json.php' );
        $json = new Services_JSON();

        $alldata = $json->decode(file_get_contents($upload_path . $filename)); // get all data in an object
        // and now create queries
        foreach ($alldata as $table => $info) {
            switch ($table) {
                case 'tasks':
                    foreach ($info as $task => $data) {
                        $values .= "'" . implode("','", (array) $data) . "'";
                        $sql[] = "INSERT INTO {$bp->gtm->table_tasks}
                                        (`id`,`name`,`desc`,`status`,`parent_id`,`creator_id`,`group_id`,`project_id`,`resp_id`,`date_created`,`deadline`,`done`,`discuss_count`)
                                        VALUES ($values)";
                    }
                    break;
                case 'projects':
                    foreach ($info as $project => $data) {
                        
                    }
                    break;
                case 'resps':
                    foreach ($info as $resp => $data) {
                        
                    }
                    break;
                case 'discuss':
                    foreach ($info as $discuss => $data) {
                        
                    }
                    break;
                case 'terms':
                    foreach ($info as $term => $data) {
                        
                    }
                    break;
                case 'taxon':
                    foreach ($info as $taxon => $data) {
                        
                    }
                    break;
                case 'roles':
                    foreach ($data as $role => $data) {
                        
                    }
                    break;
                case 'roles_caps':
                    foreach ($data as $role_cap => $data) {
                        
                    }
                    break;
            }
            print_var($sql);
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            //dbDelta($sql);

            do_action('bp_gtm_install');
        }
//        $wpdb->query($wpdb->prepare("TRUNCATE TABLE {$bp->gtm->table_tasks}"));
//        $wpdb->query($wpdb->prepare("TRUNCATE TABLE {$bp->gtm->table_projects}"));
//        $wpdb->query($wpdb->prepare("TRUNCATE TABLE {$bp->gtm->table_resps}"));
//        $wpdb->query($wpdb->prepare("TRUNCATE TABLE {$bp->gtm->table_discuss}"));
//        $wpdb->query($wpdb->prepare("TRUNCATE TABLE {$bp->gtm->table_terms}"));
//        $wpdb->query($wpdb->prepare("TRUNCATE TABLE {$bp->gtm->table_taxon}"));
//        $wpdb->query($wpdb->prepare("TRUNCATE TABLE {$bp->gtm->table_roles}"));
//        $wpdb->query($wpdb->prepare("TRUNCATE TABLE {$bp->gtm->table_roles_caps}"));

        if ($imported)
            echo '<div id="message" class="updated fade"><p>' . __('All GTM data was successfully imported.', 'bp_gtm') . '</p></div>';
        else
            echo '<div id="message" class="updated fade"><p>' . __('There was an error while importing GTM data.', 'bp_gtm') . '</p></div>';
    }else {
        echo '<div id="message" class="updated fade"><p>' . __('There were some errors while uploading a backup file. Please try again.', 'bp_gtm') . '</p></div>';
    }
}

function bp_gtm_get_themes_dirs() {
    $gtm_theme_dir = 'gtm';
    $pb_plugin_path = array();
    if (is_dir($var = STYLESHEETPATH . '/' . $gtm_theme_dir . '/')) {
        $pb_plugin_path = scandir(STYLESHEETPATH . '/' . $gtm_theme_dir . '/');
        $pb_plugin_path = array_combine($pb_plugin_path, $pb_plugin_path);
    }
    $gtm_plugin_temes = scandir(GTM_THEME_DIR);
    $gtm_plugin_temes = array_merge($gtm_plugin_temes, $pb_plugin_path);
    
    return $gtm_plugin_temes;
}
?>