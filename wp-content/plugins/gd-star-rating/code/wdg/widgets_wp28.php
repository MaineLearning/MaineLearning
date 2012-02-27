<?php

if (class_exists("WP_Widget")) {
    class gdsrWidgetRating extends WP_Widget {
        function gdsrWidgetRating() {
            $widget_ops = array('classname' => 'widget_gdstarrating_star', 'description' => __("Customized rating results list.", "gd-star-rating"));
            $control_ops = array('width' => 440);
            $this->WP_Widget('gdstarrmulti', 'GD Star Rating', $widget_ops, $control_ops);
        }

        function widget($args, $instance) {
            global $gdsr, $userdata;
            extract($args, EXTR_SKIP);

            if ($instance["display"] == "hide" || ($instance["display"] == "users" && $userdata->ID == 0) || ($instance["display"] == "visitors" && $userdata->ID > 0)) return;

            echo $before_widget.$before_title.$instance['title'].$after_title;
            echo GDSRRenderT2::render_wsr($instance);
            echo $after_widget;
        }

        function update($new_instance, $old_instance) {
            global $gdsr;

            $instance = $old_instance;

            $select_post_types = $new_instance['select'];
            if ($gdsr->wp_version > 29) {
                if (is_array($select_post_types)) {
                    if (empty($select_post_types)) {
                        $select_post_types = "post";
                    } else {
                        $select_post_types = join("|", $select_post_types);
                    }
                }
            }

            $instance['select'] = $select_post_types;
            $instance['title'] = strip_tags(stripslashes($new_instance['title']));

            $instance['template_id'] = $new_instance['template_id'];
            $instance['source'] = $new_instance['source'];
            $instance['source_set'] = $new_instance['source_set'];

            $instance['tpl_title_length'] = intval($new_instance['tpl_title_length']);
            $instance['excerpt_words'] = intval($new_instance['excerpt_words']);
            $instance['rows'] = intval($new_instance['rows']);
            $instance['min_votes'] = $new_instance['min_votes'];
            $instance['min_count'] = $new_instance['min_count'];
            $instance['taxonomy'] = $new_instance['taxonomy'];
            $instance['grouping'] = $new_instance['grouping'];
            $instance['column'] = $new_instance['column'];
            $instance['order'] = $new_instance['order'];
            $instance['category'] = $new_instance['category'];
            $instance['show'] = $new_instance['show'];
            $instance['display'] = $new_instance['display'];
            $instance['last_voted_days'] = $new_instance['last_voted_days'];

            $instance['publish_date'] = $new_instance['publish_date'];
            $instance['publish_month'] = $new_instance['publish_month'];
            $instance['publish_days'] = $new_instance['publish_days'];
            $instance['publish_range_from'] = $new_instance['publish_range_from'];
            $instance['publish_range_to'] = $new_instance['publish_range_to'];

            $instance['div_template'] = $new_instance['div_template'];
            $instance['div_filter'] = $new_instance['div_filter'];
            $instance['div_trend'] = $new_instance['div_trend'];
            $instance['div_elements'] = $new_instance['div_elements'];
            $instance['div_image'] = $new_instance['div_image'];
            $instance['div_stars'] = $new_instance['div_stars'];

            $instance['image_resize_x'] = $new_instance['image_resize_x'];
            $instance['image_resize_y'] = $new_instance['image_resize_y'];
            $instance['image_from'] = $new_instance['image_from'];
            $instance['image_custom'] = $new_instance['image_custom'];
            $instance['rating_stars'] = $new_instance['rating_stars'];
            $instance['rating_size'] = $new_instance['rating_size'];
            $instance['review_stars'] = $new_instance['review_stars'];
            $instance['review_size'] = $new_instance['review_size'];
            $instance['rating_thumb'] = $new_instance['rating_thumb'];
            $instance['rating_thumb_size'] = $new_instance['rating_thumb_size'];

            $instance['trends_rating'] = $new_instance['trends_rating'];
            $instance['trends_rating_set'] = $new_instance['trends_rating_set'];
            $instance['trends_rating_rise'] = strip_tags(stripslashes($new_instance['trends_rating_rise']));
            $instance['trends_rating_same'] = strip_tags(stripslashes($new_instance['trends_rating_same']));
            $instance['trends_rating_fall'] = strip_tags(stripslashes($new_instance['trends_rating_fall']));
            $instance['trends_voting'] = $new_instance['trends_voting'];
            $instance['trends_voting_set'] = $new_instance['trends_voting_set'];
            $instance['trends_voting_rise'] = strip_tags(stripslashes($new_instance['trends_voting_rise']));
            $instance['trends_voting_same'] = strip_tags(stripslashes($new_instance['trends_voting_same']));
            $instance['trends_voting_fall'] = strip_tags(stripslashes($new_instance['trends_voting_fall']));

            $instance['hide_empty'] = isset($new_instance['hide_empty']) ? 1 : 0;
            $instance['hide_noreview'] = isset($new_instance['hide_noreview']) ? 1 : 0;
            $instance['bayesian_calculation'] = isset($new_instance['bayesian_calculation']) ? 1 : 0;
            $instance['category_toponly'] = isset($new_instance['category_toponly']) ? 1 : 0;

            return $instance;
        }

        function form($instance) {
            global $gdsr;
            $instance = wp_parse_args((array)$instance, $gdsr->default_widget);

            $custom_post_types = gdsr_get_public_post_types();
            $select_post_types = gdsr_widget_convert_select($instance);

            $wptr = $gdsr->g->trend;
            $wpst = $gdsr->g->stars;
            $wptt = $gdsr->g->thumbs;
            $wpvr = $gdsr->wp_version;
            $wpml = GDSRDBMulti::get_multis_tinymce();

            echo '<div class="gdsr-widget-controls">';
            include(STARRATING_PATH.'widgets/rating_28/part_basic.php');
            include(STARRATING_PATH.'widgets/rating_28/part_trend.php');
            include(STARRATING_PATH.'widgets/rating_28/part_filter.php');
            include(STARRATING_PATH.'widgets/rating_28/part_image.php');
            include(STARRATING_PATH.'widgets/rating_28/part_stars.php');
            echo '</div>';
        }
    }

    class gdsrWidgetTop extends WP_Widget {
        function gdsrWidgetTop() {
            $widget_ops = array('classname' => 'widget_gdstarrating_top', 'description' => __("Overall blog rating results.", "gd-star-rating"));
            $control_ops = array('width' => 440);
            $this->WP_Widget('gdstartop', 'GD Blog Rating', $widget_ops, $control_ops);
        }

        function widget($args, $instance) {
            global $gdsr, $userdata;
            extract($args, EXTR_SKIP);

            if ($instance["display"] == "hide" || ($instance["display"] == "users" && $userdata->ID == 0) || ($instance["display"] == "visitors" && $userdata->ID > 0)) return;

            echo $before_widget.$before_title.$instance['title'].$after_title;
            echo GDSRRenderT2::render_wbr($instance);
            echo $after_widget;
        }

        function update($new_instance, $old_instance) {
            $instance = $old_instance;

            $instance['title'] = strip_tags(stripslashes($new_instance['title']));
            $instance['source'] = $new_instance['source'];
            $instance['display'] = $new_instance['display'];
            $instance['select'] = $new_instance['select'];
            $instance['show'] = $new_instance['show'];
            $instance['template_id'] = $new_instance['template_id'];
            $instance['div_filter'] = $new_instance['div_filter'];
            $instance['div_elements'] = $new_instance['div_elements'];

            return $instance;
        }

        function form($instance) {
            global $gdsr;
            $instance = wp_parse_args((array)$instance, $gdsr->default_widget_top);

            echo '<div class="gdsr-widget-controls">';
            include(STARRATING_PATH.'widgets/top_28/part_basic.php');
            include(STARRATING_PATH.'widgets/top_28/part_filter.php');
            echo '</div>';
        }
    }

    class gdsrWidgetComments extends WP_Widget {
        function gdsrWidgetComments() {
            $widget_ops = array('classname' => 'widget_gdstarrating_comments', 'description' => __("Current post comments rating.", "gd-star-rating"));
            $control_ops = array('width' => 440);
            $this->WP_Widget('gdstarcmm', 'GD Comments Rating', $widget_ops, $control_ops);
        }

        function widget($args, $instance) {
            global $gdsr, $userdata;

            if (is_single() || is_page()) {
                extract($args, EXTR_SKIP);

                if ($instance["display"] == "hide" || ($instance["display"] == "users" && $userdata->ID == 0) || ($instance["display"] == "visitors" && $userdata->ID > 0)) return;

                echo $before_widget.$before_title.$instance['title'].$after_title;
                echo GDSRRenderT2::render_wcr($instance);
                echo $after_widget;
            }
        }

        function update($new_instance, $old_instance) {
            $instance = $old_instance;

            $instance['title'] = strip_tags(stripslashes($new_instance['title']));

            $instance['text_max'] = $new_instance['text_max'];
            $instance['template_id'] = $new_instance['template_id'];

            $instance['rows'] = $new_instance['rows'];
            $instance['min_votes'] = $new_instance['min_votes'];
            $instance['column'] = $new_instance['column'];
            $instance['order'] = $new_instance['order'];
            $instance['show'] = $new_instance['show'];
            $instance['display'] = $new_instance['display'];
            $instance['last_voted_days'] = $new_instance['last_voted_days'];

            $instance['avatar'] = $new_instance['avatar'];
            $instance['rating_stars'] = $new_instance['rating_stars'];
            $instance['rating_size'] = $new_instance['rating_size'];

            $instance['div_filter'] = $new_instance['div_filter'];
            $instance['div_image'] = $new_instance['div_image'];
            $instance['div_stars'] = $new_instance['div_stars'];

            $instance['hide_empty'] = isset($new_instance['hide_empty']) ? 1 : 0;

            return $instance;
        }

        function form($instance) {
            global $gdsr;
            $instance = wp_parse_args((array)$instance, $gdsr->default_widget_comments);

            $wptr = $gdsr->g->trend;
            $wpst = $gdsr->g->stars;

            echo '<div class="gdsr-widget-controls">';
            include(STARRATING_PATH.'widgets/comments_28/part_basic.php');
            include(STARRATING_PATH.'widgets/comments_28/part_filter.php');
            include(STARRATING_PATH.'widgets/comments_28/part_image.php');
            include(STARRATING_PATH.'widgets/comments_28/part_stars.php');
            echo '</div>';
        }
    }
}

?>