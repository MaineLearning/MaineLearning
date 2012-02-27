<?php

class gdsrWidgets {
    var $w;
    var $g;

    var $default_widget_comments;
    var $default_widget_top;
    var $default_widget;

    function gdsrWidgets($g, $dw_c, $dw_t, $dw_s) {
        $this->g = $g;
        $this->default_widget_comments = $dw_c;
        $this->default_widget_top = $dw_t;
        $this->default_widget = $dw_s;
    }

    function widget_comments_init() {
        if (!$options = get_option('widget_gdstarrating_comments'))
            $options = array();

        $widget_ops = array('classname' => 'widget_gdstarrating_comments', 'description' => 'GD Comments Rating');
        $control_ops = array('width' => 440, 'height' => 420, 'id_base' => 'gdstarcmm');
        $name = 'GD Comments Rating';

        $registered = false;
        foreach (array_keys($options) as $o) {
            if (!isset($options[$o]['title']))
                continue;

            $id = "gdstarcmm-$o";
            $registered = true;
            wp_register_sidebar_widget($id, $name, array(&$this, 'widget_comments_display'), $widget_ops, array('number' => $o));
            wp_register_widget_control($id, $name, array(&$this, 'widget_comments_control'), $control_ops, array('number' => $o));
        }
        if (!$registered) {
            wp_register_sidebar_widget('gdstarcmm-1', $name, array(&$this, 'widget_comments_display'), $widget_ops, array('number' => -1));
            wp_register_widget_control('gdstarcmm-1', $name, array(&$this, 'widget_comments_control'), $control_ops, array('number' => -1));
        }
    }

    function widget_comments_control($widget_args = 1) {
        global $wp_registered_widgets;
        static $updated = false;

        if ( is_numeric($widget_args) )
            $widget_args = array('number' => $widget_args);

        $widget_args = wp_parse_args($widget_args, array('number' => -1));
        extract($widget_args, EXTR_SKIP);
        $options_all = get_option('widget_gdstarrating_comments');
        if (!is_array($options_all))
            $options_all = array();

        if (!$updated && !empty($_POST['sidebar'])) {
            $sidebar = (string)$_POST['sidebar'];

            $sidebars_widgets = wp_get_sidebars_widgets();
            if (isset($sidebars_widgets[$sidebar]))
                $this_sidebar =& $sidebars_widgets[$sidebar];
            else
                $this_sidebar = array();

            foreach ($this_sidebar as $_widget_id) {
                if ('widget_gdstarrating_comments' == $wp_registered_widgets[$_widget_id]['callback'] && isset($wp_registered_widgets[$_widget_id]['params'][0]['number'])) {
                    $widget_number = $wp_registered_widgets[$_widget_id]['params'][0]['number'];
                    if (!in_array("gdstarcmm-$widget_number", $_POST['widget-id']))
                        unset($options_all[$widget_number]);
                }
            }
            foreach ((array)$_POST['gdstart'] as $widget_number => $posted) {
                if (!isset($posted['title']) && isset($options_all[$widget_number]))
                    continue;
                $options = array();

                $options['title'] = strip_tags(stripslashes($posted['title']));

                $options['text_max'] = $posted['text_max'];
                $options['template_id'] = $posted['template_id'];

                $options['rows'] = $posted['rows'];
                $options['min_votes'] = $posted['min_votes'];
                $options['column'] = $posted['column'];
                $options['order'] = $posted['order'];
                $options['show'] = $posted['show'];
                $options['display'] = $posted['display'];
                $options['last_voted_days'] = $posted['last_voted_days'];

                $options['avatar'] = $posted['avatar'];
                $options['rating_stars'] = $posted['rating_stars'];
                $options['rating_size'] = $posted['rating_size'];

                $options['div_filter'] = $posted['div_filter'];
                $options['div_image'] = $posted['div_image'];
                $options['div_stars'] = $posted['div_stars'];

                $options['hide_empty'] = isset($posted['hide_empty']) ? 1 : 0;

                $options_all[$widget_number] = $options;
            }
            update_option('widget_gdstarrating_comments', $options_all);
            $updated = true;
        }

        if (-1 == $number) {
            $wpnm = '%i%';
            $wpno = $this->default_widget_comments;
        }
        else {
            $wpnm = $number;
            $wpno = $options_all[$number];
        }

        $wpfn = 'gdstart['.$wpnm.']';
        $wptr = $this->g->trend;
        $wpst = $this->g->stars;

        include(STARRATING_PATH."widgets/widget_comments.php");
    }

    function widget_comments_display($args, $widget_args = 1) {
        extract($args);
        global $gdsr, $userdata;

        if (is_single() || is_page()) {
            if (is_numeric($widget_args))
                $widget_args = array('number' => $widget_args);
            $widget_args = wp_parse_args($widget_args, array( 'number' => -1 ));
            extract($widget_args, EXTR_SKIP);
            $options_all = get_option('widget_gdstarrating_comments');
            if (!isset($options_all[$number]))
                return;
            $this->w = $options_all[$number];

            if ($this->w["display"] == "hide" || ($this->w["display"] == "users" && $userdata->ID == 0) || ($this->w["display"] == "visitors" && $userdata->ID > 0)) return;

            echo $before_widget.$before_title.$this->w['title'].$after_title;
            echo GDSRRenderT2::render_wcr($this->w);
            echo $after_widget;
        }
    }

    function widget_top_init() {
        if (!$options = get_option('widget_gdstarrating_top'))
            $options = array();

        $widget_ops = array('classname' => 'widget_gdstarrating_top', 'description' => 'Overall blog rating results.');
        $control_ops = array('width' => 440, 'height' => 420, 'id_base' => 'gdstartop');
        $name = 'GD Blog Rating';

        $registered = false;
        foreach (array_keys($options) as $o) {
            if (!isset($options[$o]['title']))
                continue;

            $id = "gdstartop-$o";
            $registered = true;
            wp_register_sidebar_widget($id, $name, array(&$this, 'widget_top_display'), $widget_ops, array( 'number' => $o ) );
            wp_register_widget_control($id, $name, array(&$this, 'widget_top_control'), $control_ops, array( 'number' => $o ) );
        }
        if (!$registered) {
            wp_register_sidebar_widget('gdstartop-1', $name, array(&$this, 'widget_top_display'), $widget_ops, array( 'number' => -1 ) );
            wp_register_widget_control('gdstartop-1', $name, array(&$this, 'widget_top_control'), $control_ops, array( 'number' => -1 ) );
        }
    }

    function widget_top_control($widget_args = 1) {
        global $wp_registered_widgets;
        static $updated = false;

        if ( is_numeric($widget_args) )
            $widget_args = array('number' => $widget_args);

        $widget_args = wp_parse_args($widget_args, array('number' => -1));
        extract($widget_args, EXTR_SKIP);
        $options_all = get_option('widget_gdstarrating_top');
        if (!is_array($options_all))
            $options_all = array();

        if (!$updated && !empty($_POST['sidebar'])) {
            $sidebar = (string)$_POST['sidebar'];

            $sidebars_widgets = wp_get_sidebars_widgets();
            if (isset($sidebars_widgets[$sidebar]))
                $this_sidebar =& $sidebars_widgets[$sidebar];
            else
                $this_sidebar = array();

            foreach ($this_sidebar as $_widget_id) {
                if ('widget_gdstarrating_top' == $wp_registered_widgets[$_widget_id]['callback'] && isset($wp_registered_widgets[$_widget_id]['params'][0]['number'])) {
                    $widget_number = $wp_registered_widgets[$_widget_id]['params'][0]['number'];
                    if (!in_array("gdstartop-$widget_number", $_POST['widget-id']))
                        unset($options_all[$widget_number]);
                }
            }
            foreach ((array)$_POST['gdstart'] as $widget_number => $posted) {
                if (!isset($posted['title']) && isset($options_all[$widget_number]))
                    continue;
                $options = array();

                $options['title'] = strip_tags(stripslashes($posted['title']));
                $options['source'] = $posted['source'];
                $options['display'] = $posted['display'];
                $options['template_id'] = $posted['template_id'];
                $options['select'] = $posted['select'];
                $options['show'] = $posted['show'];

                $options['div_filter'] = $posted['div_filter'];
                $options['div_elements'] = $posted['div_elements'];

                $options_all[$widget_number] = $options;
            }
            update_option('widget_gdstarrating_top', $options_all);
            $updated = true;
        }

        if (-1 == $number) {
            $wpnm = '%i%';
            $wpno = $this->default_widget_top;
        }
        else {
            $wpnm = $number;
            $wpno = $options_all[$number];
        }

        $wpfn = 'gdstart['.$wpnm.']';

        include(STARRATING_PATH."widgets/widget_top.php");
    }

    function widget_top_display($args, $widget_args = 1) {
        extract($args);
        global $gdsr, $userdata;

        if (is_numeric($widget_args))
            $widget_args = array('number' => $widget_args);
        $widget_args = wp_parse_args($widget_args, array( 'number' => -1 ));
        extract($widget_args, EXTR_SKIP);
        $options_all = get_option('widget_gdstarrating_top');
        if (!isset($options_all[$number]))
            return;
        $this->w = $options_all[$number];

        if ($this->w["display"] == "hide" || ($this->w["display"] == "users" && $userdata->ID == 0) || ($this->w["display"] == "visitors" && $userdata->ID > 0)) return;

        echo $before_widget.$before_title.$this->w['title'].$after_title;
        echo GDSRRenderT2::render_wbr($this->w);
        echo $after_widget;
    }

    function widget_articles_init() {
        if (!$options = get_option('widget_gdstarrating'))
            $options = array();

        $widget_ops = array('classname' => 'widget_gdstarrating', 'description' => 'Customized rating results list.');
        $control_ops = array('width' => 440, 'height' => 420, 'id_base' => 'gdstarrmulti');
        $name = 'GD Star Rating';

        $registered = false;
        foreach (array_keys($options) as $o) {
            if (!isset($options[$o]['title']))
                continue;

            $id = "gdstarrmulti-$o";
            $registered = true;
            wp_register_sidebar_widget($id, $name, array(&$this, 'widget_articles_display'), $widget_ops, array( 'number' => $o ) );
            wp_register_widget_control($id, $name, array(&$this, 'widget_articles_control'), $control_ops, array( 'number' => $o ) );
        }
        if (!$registered) {
            wp_register_sidebar_widget('gdstarrmulti-1', $name, array(&$this, 'widget_articles_display'), $widget_ops, array( 'number' => -1 ) );
            wp_register_widget_control('gdstarrmulti-1', $name, array(&$this, 'widget_articles_control'), $control_ops, array( 'number' => -1 ) );
        }
    }

    function widget_articles_control($widget_args = 1) {
        global $wp_registered_widgets;
        static $updated = false;

        if ( is_numeric($widget_args) )
            $widget_args = array('number' => $widget_args);

        $widget_args = wp_parse_args($widget_args, array('number' => -1));
        extract($widget_args, EXTR_SKIP);
        $options_all = get_option('widget_gdstarrating');
        if (!is_array($options_all))
            $options_all = array();

        if (!$updated && !empty($_POST['sidebar'])) {
            $sidebar = (string)$_POST['sidebar'];

            $sidebars_widgets = wp_get_sidebars_widgets();
            if (isset($sidebars_widgets[$sidebar]))
                $this_sidebar =& $sidebars_widgets[$sidebar];
            else
                $this_sidebar = array();

            foreach ($this_sidebar as $_widget_id) {
                if ('widget_gdstarrating' == $wp_registered_widgets[$_widget_id]['callback'] && isset($wp_registered_widgets[$_widget_id]['params'][0]['number'])) {
                    $widget_number = $wp_registered_widgets[$_widget_id]['params'][0]['number'];
                    if (!in_array("gdstarrmulti-$widget_number", $_POST['widget-id']))
                        unset($options_all[$widget_number]);
                }
            }
            foreach ((array)$_POST['gdstarr'] as $widget_number => $posted) {
                if (!isset($posted['title']) && isset($options_all[$widget_number]))
                    continue;
                $options = array();

                $options['title'] = strip_tags(stripslashes($posted['title']));

                $options['tpl_title_length'] = $posted['title_max'];
                $options['template_id'] = $posted['template_id'];

                $options['source'] = $posted['source'];
                $options['source_set'] = $posted['source_set'];
                $options['taxonomy'] = $posted['taxonomy'];

                $options['excerpt_words'] = intval($posted['excerpt_words']);
                $options['rows'] = intval($posted['rows']);
                $options['min_votes'] = $posted['min_votes'];
                $options['min_count'] = $posted['min_count'];
                $options['select'] = $posted['select'];
                $options['grouping'] = $posted['grouping'];
                $options['column'] = $posted['column'];
                $options['order'] = $posted['order'];
                $options['category'] = $posted['category'];
                $options['show'] = $posted['show'];
                $options['display'] = $posted['display'];
                $options['last_voted_days'] = $posted['last_voted_days'];

                $options['publish_date'] = $posted['publish_date'];
                $options['publish_month'] = $posted['publish_month'];
                $options['publish_days'] = $posted['publish_days'];
                $options['publish_range_from'] = $posted['publish_range_from'];
                $options['publish_range_to'] = $posted['publish_range_to'];

                $options['div_trend'] = $posted['div_trend'];
                $options['div_elements'] = $posted['div_elements'];
                $options['div_filter'] = $posted['div_filter'];
                $options['div_image'] = $posted['div_image'];
                $options['div_stars'] = $posted['div_stars'];

                $options['image_resize_x'] = $posted['image_resize_x'];
                $options['image_resize_y'] = $posted['image_resize_y'];
                $options['image_from'] = $posted['image_from'];
                $options['image_custom'] = $posted['image_custom'];
                $options['rating_stars'] = $posted['rating_stars'];
                $options['rating_size'] = $posted['rating_size'];
                $options['review_stars'] = $posted['review_stars'];
                $options['review_size'] = $posted['review_size'];
                $options['rating_thumb'] = $posted['rating_thumb'];
                $options['rating_thumb_size'] = $posted['rating_thumb_size'];

                $options['trends_rating'] = $posted['trends_rating'];
                $options['trends_rating_set'] = $posted['trends_rating_set'];
                $options['trends_rating_rise'] = strip_tags(stripslashes($posted['trends_rating_rise']));
                $options['trends_rating_same'] = strip_tags(stripslashes($posted['trends_rating_same']));
                $options['trends_rating_fall'] = strip_tags(stripslashes($posted['trends_rating_fall']));
                $options['trends_voting'] = $posted['trends_voting'];
                $options['trends_voting_set'] = $posted['trends_voting_set'];
                $options['trends_voting_rise'] = strip_tags(stripslashes($posted['trends_voting_rise']));
                $options['trends_voting_same'] = strip_tags(stripslashes($posted['trends_voting_same']));
                $options['trends_voting_fall'] = strip_tags(stripslashes($posted['trends_voting_fall']));

                $options['hide_empty'] = isset($posted['hide_empty']) ? 1 : 0;
                $options['hide_noreview'] = isset($posted['hide_noreview']) ? 1 : 0;
                $options['bayesian_calculation'] = isset($posted['bayesian_calculation']) ? 1 : 0;
                $options['category_toponly'] = isset($posted['category_toponly']) ? 1 : 0;

                $options_all[$widget_number] = $options;
            }
            update_option('widget_gdstarrating', $options_all);
            $updated = true;
        }

        if (-1 == $number) {
            $wpnm = '%i%';
            $wpno = $this->default_widget;
        }
        else {
            $wpnm = $number;
            $wpno = $options_all[$number];
        }

        $wpfn = 'gdstarr['.$wpnm.']';
        $wptr = $this->g->trend;
        $wpst = $this->g->stars;
        $wptt = $this->g->thumbs;
        $wpml = GDSRDBMulti::get_multis_tinymce();

        include(STARRATING_PATH."widgets/widget_rating.php");
    }

    function widget_articles_display($args, $widget_args = 1) {
        extract($args);
        global $gdsr, $wpdb, $userdata;

        if (is_numeric($widget_args))
            $widget_args = array('number' => $widget_args);
        $widget_args = wp_parse_args($widget_args, array( 'number' => -1 ));
        extract($widget_args, EXTR_SKIP);
        $options_all = get_option('widget_gdstarrating');
        if (!isset($options_all[$number]))
            return;
        $this->w = $options_all[$number];

        if ($this->w["display"] == "hide" || ($this->w["display"] == "users" && $userdata->ID == 0) || ($this->w["display"] == "visitors" && $userdata->ID > 0)) return;

        echo $before_widget.$before_title.$this->w['title'].$after_title;
        echo GDSRRenderT2::render_wsr($this->w);
        echo $after_widget;
    }
}

?>