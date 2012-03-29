<?php
//temtplate functioms
require_once (dirname(__File__) . '/bp-gtm-templatetags.php');

// Tasks Management Class and Funcs
include(dirname(__File__) . '/lib/tasks.php');

// Projects Management Class and Funcs
include(dirname(__File__) . '/lib/projects.php');

// Discussion Class and Funcs
include(dirname(__File__) . '/lib/discuss.php');

// Taxonomies Management Class and Funcs
include(dirname(__File__) . '/lib/terms.php');

// Involved People Class and Funcs
include(dirname(__File__) . '/lib/involved.php');

// Personal Pages Class and Funcs
include(dirname(__File__) . '/lib/personal.php');

// Roles Class and Funcs
include(dirname(__File__) . '/lib/roles.php');

// Action Class and Funcs
include(dirname(__File__) . '/lib/actions.php');

// Files Class and Funcs
include(dirname(__File__) . '/lib/files.php');


/*
 * GTM Widget
 */


class BP_GTM_gTax_Cloud extends WP_Widget {

    function bp_gtm_gtax_cloud() {
        parent::WP_Widget(false, $name = __("inGroup GTM Classifier", 'bp_gtm'));
    }

    function widget($args, $instance) {
        global $wpdb, $bp;

        if ($bp->gtm->slug == $bp->current_action) {

            extract($args);

            echo $before_widget;
            echo $before_title
            . $widget_name
            . $after_title;

            $data = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT `term_id`, `taxon` FROM {$bp->gtm->table_taxon}
                  WHERE `group_id` = %d", $bp->groups->current_group->id));

            if ($instance['display_count'] == 1) {
                $count = true;
            } else {
                $count = false;
            }
            // need to get all tags and cats separately
            if (count($data) != '0') {
                foreach ($data as $term) {
                    if ($term->taxon == 'tag') {
                        $tags[$term->term_id]->id = $term->term_id;
                    }
                    if ($term->taxon == 'cat') {
                        $cats[$term->term_id]->id = $term->term_id;
                    }
                }

                // lets work with tags
                if ($instance['display_tags'] == '1') {
                    _e('<strong>Tags:</strong>', 'bp_gtm');
                    // display tags in a list
                    if ($instance['display_type_tags'] == 'list') {
                        echo '<ul id="disply-tags">';
                        if (count($tags) > 0) {
                            foreach ($tags as $tag) {
                                if ($instance['display_count'] == 1)
                                    $count_func = ' (' . bp_gtm_count_term_usage($tag->id, $bp->groups->current_group->id) . ')';
                                echo '<li>' . stripslashes(bp_gtm_get_term_name_by_id($tag->id, $link = true)) . $count_func . '</li>';
                            }
                        }else {
                            echo '<li>' . __('No tags to display', 'bp_gtm') . '</li>';
                        }
                        echo '</ul>';
                    } elseif ($instance['display_type_tags'] == 'cloud') {
                        // display tags in a cloud
                        echo '<p>';
                        if (count($tags) > 0) {
                            foreach ($tags as $tag) {
                                echo stripslashes(bp_gtm_get_term_name_by_id($tag->id, $link = true, $count)) . ' ';
                            }
                        } else {
                            echo '<li>' . __('No tags to display', 'bp_gtm') . '</li>';
                        }
                        echo '</p>';
                    }
                }

                // now lets work with categories
                if ($instance['display_cats'] == '1') {
                    _e('<strong>Categories:</strong>', 'bp_gtm');
                    if ($instance['display_type_cats'] == 'list') {
                        echo '<ul id="disply-cats">';
                        if (count($cats) > 0) {
                            foreach ($cats as $cat) {
                                if ($instance['display_count'] == 1)
                                    $count_func = ' (' . bp_gtm_count_term_usage($cat->id, $bp->groups->current_group->id) . ')';
                                echo '<li>' . stripslashes(bp_gtm_get_term_name_by_id($cat->id, true)) . $count_func . '</li>';
                            }
                        }else {
                            echo '<li>' . __('No categories to display', 'bp_gtm') . '</li>';
                        }
                        echo '</ul>';
                    } elseif ($instance['display_type_cats'] == 'cloud') {
                        // display categories in a cloud
                        echo '<p>';
                        if (count($cats) > 0) {
                            foreach ($cats as $cat) {
                                echo stripslashes(bp_gtm_get_term_name_by_id($cat->id, true, $count)) . ' ';
                            }
                        } else {
                            echo '<li>' . __('No categories to display', 'bp_gtm') . '</li>';
                        }
                        echo '</p>';
                    }
                }
            } else {
                echo '<div class="widget-error">' . __('There is nothing to display yet.', 'bp_gtm') . '</div>';
            }
            echo $after_widget;
        }
    }

    function update($new_instance, $old_instance) {
        $instance = $old_instance;

        $instance['display_tags'] = strip_tags($new_instance['display_tags']);
        $instance['display_cats'] = strip_tags($new_instance['display_cats']);
        $instance['display_type_tags'] = strip_tags($new_instance['display_type_tags']);
        $instance['display_type_cats'] = strip_tags($new_instance['display_type_cats']);
        $instance['display_count'] = strip_tags($new_instance['display_count']);

        return $instance;
    }

    function form($instance) {
        $instance = wp_parse_args((array) $instance, array('display_tags' => 1, 'display_cats' => 1, 'display_count' => 1, 'display_type_tags' => 'cloud', 'display_type_cats' => 'list'));
        $display_tags = strip_tags($instance['display_tags']);
        $display_cats = strip_tags($instance['display_cats']);
        $display_type_tags = strip_tags($instance['display_type_tags']);
        $display_type_cats = strip_tags($instance['display_type_cats']);
        $display_count = strip_tags($instance['display_count']);
        ?>

        <p><label for="bp-gtm-widget-display-tags">
                <input id="<?php echo $this->get_field_id('display_tags'); ?>" name="<?php echo $this->get_field_name('display_tags'); ?>" type="checkbox" value="1" <?php if ($display_tags == '1')
            echo 'checked="checked"'; ?> />
        <?php _e('Display tags?', 'bp_gtm'); ?>
            </label></p>
        <p><label for="bp-gtm-widget-display-cats">
                <input id="<?php echo $this->get_field_id('display_cats'); ?>" name="<?php echo $this->get_field_name('display_cats'); ?>" type="checkbox" value="1" <?php if ($display_cats == '1')
            echo 'checked="checked"'; ?> />
        <?php _e('Display categories?', 'bp_gtm'); ?>
            </label></p>
        <p><label for="bp-gtm-widget-how-display-tags">
        <?php _e('How do you want to display tags?', 'bp_gtm'); ?><br>
                <input id="<?php echo $this->get_field_id('display_type_tags'); ?>" name="<?php echo $this->get_field_name('display_type_tags'); ?>" type="radio" value="cloud" <?php if ($display_type_tags == 'cloud')
            echo 'checked="checked"'; ?> /> <?php _e('As a Cloud', 'bp_gtm'); ?><br>
                <input id="<?php echo $this->get_field_id('display_type_tags'); ?>" name="<?php echo $this->get_field_name('display_type_tags'); ?>" type="radio" value="list" <?php if ($display_type_tags == 'list')
            echo 'checked="checked"'; ?> /> <?php _e('As a List', 'bp_gtm'); ?>
                </label></p>
                <p><label for="bp-gtm-widget-how-display-cats">
                                <?php _e('How do you want to display categories?', 'bp_gtm'); ?><br>
                        <input id="<?php echo $this->get_field_id('display_type_cats'); ?>" name="<?php echo $this->get_field_name('display_type_cats'); ?>" type="radio" value="cloud" <?php if ($display_type_cats == 'cloud')
                            echo 'checked="checked"'; ?> /> <?php _e('As a Cloud', 'bp_gtm'); ?><br>
                        <input id="<?php echo $this->get_field_id('display_type_cats'); ?>" name="<?php echo $this->get_field_name('display_type_cats'); ?>" type="radio" value="list" <?php if ($display_type_cats == 'list')
                            echo 'checked="checked"'; ?> /> <?php _e('As a List', 'bp_gtm'); ?>
                        </label></p>
                        <p><label for="bp-gtm-widget-display-count">
                                <?php _e('Do you want to display categories and tags usage count?', 'bp_gtm'); ?><br>
                                <input id="<?php echo $this->get_field_id('display_count'); ?>" name="<?php echo $this->get_field_name('display_count'); ?>" type="radio" value="1" <?php if ($display_count == '1')
                            echo 'checked="checked"'; ?> /> <?php _e('Yes', 'bp_gtm'); ?><br>
                                <input id="<?php echo $this->get_field_id('display_count'); ?>" name="<?php echo $this->get_field_name('display_count'); ?>" type="radio" value="0" <?php if ($display_count == '0')
                            echo 'checked="checked"'; ?> /> <?php _e('No', 'bp_gtm'); ?>
                                </label></p>
                                <?php
                            }

}

/*
 * Some other MySQL-based functions
 *
 * @return array member ids
 */
function bp_gtm_search_gmembers($search_term, $limit, $group_id, $role) {
    global $bp, $wpdb;

    if ($role == 'admins') {
        $allmembers = BP_Groups_Member::get_group_administrator_ids($group_id);
    } elseif ($role == 'mods') {
        $allmembers = BP_Groups_Member::get_group_moderator_ids($group_id);
    } elseif ($role == 'members') {
        $allmembers = BP_Groups_Member::get_group_member_ids($group_id);
    }

    /* Fetch the user's full name */
//    if (function_exists('xprofile_install')) {
        /* Ensure xprofile globals are set */
        if (!defined('BP_XPROFILE_FULLNAME_FIELD_NAME'))
            xprofile_setup_globals();

        $allmembers = $wpdb->escape(implode(',', (array) $allmembers));

        $members = $wpdb->get_results($wpdb->prepare("
            SELECT `user_id` as `id` FROM {$bp->profile->table_name_data}
            WHERE `field_id` = 1 AND `user_id` IN ( {$allmembers} ) AND `value` LIKE '%%$search_term%%'
            LIMIT %d
            ", $limit));


    return $members;
}
