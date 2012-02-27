<?php

class GDSRHelper {
    /**
     * Creates extra folders.
     *
     * @return bool cache folder exists and is writeable
     */
    function create_folders($version) {
        if (is_dir(STARRATING_XTRA_PATH)) {
            if (is_writable(STARRATING_XTRA_PATH)) {
                if (!is_dir(STARRATING_CACHE_PATH)) mkdir(STARRATING_CACHE_PATH, 0755);
                if (!is_dir(STARRATING_XTRA_PATH."stars/")) mkdir(STARRATING_XTRA_PATH."stars/", 0755);
                if (!is_dir(STARRATING_XTRA_PATH."trends/")) mkdir(STARRATING_XTRA_PATH."trends/", 0755);
                if (!is_dir(STARRATING_XTRA_PATH."css/")) mkdir(STARRATING_XTRA_PATH."css/", 0755);
                if (!file_exists(STARRATING_XTRA_PATH."css/rating.css")) {
                    copy(STARRATING_PATH."css/rating.css", STARRATING_XTRA_PATH."css/rating.css");
                }
            }
        } else {
            $path = WP_CONTENT_DIR;
            if (is_writable($path)) {
                mkdir(STARRATING_XTRA_PATH, 0755);
                GDSRHelper::create_folders($version);
            } else return false;
        }
        return is_dir(STARRATING_CACHE_PATH) && is_writable(STARRATING_CACHE_PATH);
    }

    /**
     * Removes all files from cache
     *
     * @param string $path Path to the cache folder
     */
    function clean_cache($path) {
        if (!file_exists($path)) return;
        if (is_file($path)) {
            unlink ($path);
            return;
        }

        $res = glob($path."/*");
        if (is_array($res) && count($res) > 0) {
            foreach ($res as $fn) GDSRHelper::clean_cache($fn);
        }
    }

    /**
     * Cleans IP.
     *
     * @param string $ip start IP
     * @return string cleaned IP
     */
    function clean_ip($ip) {
        $parts = explode(".", $ip);
        for ($i = 0; $i < count($parts); $i++)
            $parts[$i] = intval($parts[$i]);

        return join(".", $parts);
    }

    function timer_value($t_type, $t_date = '', $t_count_value = 0, $t_count_type = 'D') {
        $value = '';
        switch ($t_type) {
            case 'D':
                $value = $t_date;
                break;
            case 'T':
                $value = $t_count_type.$t_count_value;
                break;
        }
        return $value;
    }

    function get_categories_hierarchy($cats, $depth = 0, $level = 0) {
        $h = array();
        foreach ($cats as $cat) {
            if($cat->parent == $level) {
                $cat->depth = $depth;
                $h[] = $cat;
                $recats = GDSRHelper::get_categories_hierarchy($cats, $depth + 1, $cat->term_id);
                $h = array_merge($h, $recats);
            }
        }
        return $h;
    }

    function render_taxonomy_select($tax = "") {
        global $wp_taxonomies, $gdsr;
        foreach ($wp_taxonomies as $taxonomy => $cnt) {
            $valid = false;
            if ($gdsr->wp_version < 30) {
                if ($taxonomy != "category" && $cnt->object_type == "post") $valid = true;
            } else {
                if ($taxonomy != "category" && $cnt->public) $valid = true;
            }

            if ($valid) {
                $current = $tax == $taxonomy ? ' selected="selected"' : $current = '';
                echo "\t<option value='".$taxonomy."'".$current.">".$cnt->label."</option>\r\n";
            }
        }
    }

    function render_styles_select($styles, $selected = '', $version = false) {
        foreach ($styles as $style) {
            $title = $version ? $style->name." ".$style->version : $style->name;
            $current = $selected == $style->folder ? ' selected="selected"' : $current = '';
            echo "\t<option value='".$style->folder."'".$current.">".$title."</option>\r\n";
        }
    }

    function render_class_select($styles) {
        for ($i = 0; $i < count($styles); $i++) {
            $style = $styles[$i];
            echo "\t<option value='".$style["class"]."'>".$style["name"]."</option>\r\n";
        }
    }

    function render_stars_select($selected = 10) {
        GDSRHelper::render_stars_select_full($selected);
    }

    function render_stars_select_full($selected = 10, $stars = 20, $start = 1, $prefix = '') {
        for ($i = $start; $i < $stars + 1; $i++) {
            if ($selected == $i) $current = ' selected="selected"';
            else $current = '';
            if ($prefix != '') $name = $prefix.': '.$i;
            else $name = $i;
            echo "\t<option value='".$i."'".$current.">".$name."</option>\r\n";
        }
    }
    
    function render_gfx_js($styles) {
        $js = array();
        foreach ($styles as $style)
            $js[] = '"'.$style->folder.'": "'.$style->gfx_url.'"';
        echo join(", ", $js);
    }

    function render_ext_gfx_js($styles) {
        $js = array();
        foreach ($styles as $style)
            $js[] = '"'.$style->folder.'": "'.$style->type.'"';
        echo join(", ", $js);
    }

    function render_authors_gfx_js($styles) {
        $js = array();
        foreach ($styles as $style) {
            $info = '"name": "'.$style->author.'", ';
            $info.= '"email": "'.$style->email.'", ';
            $info.= '"url": "'.$style->url.'"';
            $js[] = '"'.$style->folder.'": { '.$info.' }';
        }
        echo join(", ", $js);
    }

    function render_custom_fields($name, $selected = "N", $width = 180, $style = '') {
        $fields = gdWPGDSR::get_all_custom_fieds(false);
        ?>
<select style="width: <?php echo $width ?>px; <?php echo $style; ?>" name="<?php echo $name; ?>" id="<?php echo $name; ?>">
        <?php
            foreach ($fields as $s) {
                echo sprintf('<option value="%s"%s>%s</option>', $s, ($selected == $s ? ' selected="selected"' : ''),  $s);
            }
        ?>
</select>
        <?php
    }

    function render_moderation_combo($name, $selected = "N", $width = 180, $style = '', $row_zero = false, $cat = false, $parent = false) {
        ?>
<select style="width: <?php echo $width ?>px; <?php echo $style; ?>" name="<?php echo $name; ?>" id="<?php echo $name; ?>">
    <?php if ($row_zero) { ?> <option value=""<?php echo $selected == '/' ? ' selected="selected"' : ''; ?>>/</option> <?php } ?>
    <option value="N"<?php echo $selected == 'N' ? ' selected="selected"' : ''; ?>><?php _e("No moderation", "gd-star-rating"); ?> </option>
    <option value="V"<?php echo $selected == 'V' ? ' selected="selected"' : ''; ?>><?php _e("Moderate visitors", "gd-star-rating"); ?></option>
    <option value="U"<?php echo $selected == 'U' ? ' selected="selected"' : ''; ?>><?php _e("Moderate users", "gd-star-rating"); ?></option>
    <option value="A"<?php echo $selected == 'A' ? ' selected="selected"' : ''; ?>><?php _e("Moderate all", "gd-star-rating"); ?></option>
    <?php if (!$cat) { ?><option value="I"<?php echo $selected == 'I' ? ' selected="selected"' : ''; ?>><?php _e("Inherit from Category", "gd-star-rating"); ?></option><?php } ?>
    <?php if ($parent) { ?><option value="P"<?php echo $selected == 'P' ? ' selected="selected"' : ''; ?>><?php _e("Inherit from Parent", "gd-star-rating"); ?></option><?php } ?>
</select>
        <?php
    }	

    function render_rules_combo($name, $selected = "A", $width = 180, $style = '', $row_zero = false, $cat = false, $parent = false) {
        ?>
<select style="width: <?php echo $width ?>px; <?php echo $style; ?>" name="<?php echo $name; ?>" id="<?php echo $name; ?>">
    <?php if ($row_zero) { ?> <option value=""<?php echo $selected == '/' ? ' selected="selected"' : ''; ?>>/</option> <?php } ?>
    <option value="A"<?php echo $selected == 'A' ? ' selected="selected"' : ''; ?>><?php _e("Everyone can vote", "gd-star-rating"); ?></option>
    <option value="V"<?php echo $selected == 'V' ? ' selected="selected"' : ''; ?>><?php _e("Only visitors", "gd-star-rating"); ?></option>
    <option value="U"<?php echo $selected == 'U' ? ' selected="selected"' : ''; ?>><?php _e("Only users", "gd-star-rating"); ?></option>
    <option value="N"<?php echo $selected == 'N' ? ' selected="selected"' : ''; ?>><?php _e("Locked", "gd-star-rating"); ?></option>
    <option value="H"<?php echo $selected == 'H' ? ' selected="selected"' : ''; ?>><?php _e("Locked and hidden", "gd-star-rating"); ?></option>
    <?php if (!$cat) { ?><option value="I"<?php echo $selected == 'I' ? ' selected="selected"' : ''; ?>><?php _e("Inherit from Category", "gd-star-rating"); ?></option><?php } ?>
    <?php if ($parent) { ?><option value="P"<?php echo $selected == 'P' ? ' selected="selected"' : ''; ?>><?php _e("Inherit from Parent", "gd-star-rating"); ?></option><?php } ?>
</select>
        <?php
    } 

    function render_timer_combo($name, $selected = "N", $width = 180, $style = '', $row_zero = false, $onchange = '', $cat = false, $parent = false) {
        if ($onchange != '') $onchange = ' onchange="'.$onchange.'"';
        ?>
<select<?php echo $onchange; ?> style="width: <?php echo $width ?>px; <?php echo $style; ?>" name="<?php echo $name; ?>" id="<?php echo $name; ?>">
    <?php if ($row_zero) { ?> <option value="_"<?php echo $selected == '/' ? ' selected="selected"' : ''; ?>>/</option> <?php } ?>
    <option value="N"<?php echo $selected == 'N' ? ' selected="selected"' : ''; ?>><?php _e("No timer", "gd-star-rating"); ?></option>
    <option value="T"<?php echo $selected == 'T' ? ' selected="selected"' : ''; ?>><?php _e("Countdown timer", "gd-star-rating"); ?></option>
    <option value="D"<?php echo $selected == 'D' ? ' selected="selected"' : ''; ?>><?php _e("Date limited", "gd-star-rating"); ?></option>
    <?php if (!$cat) { ?><option value="I"<?php echo $selected == 'I' ? ' selected="selected"' : ''; ?>><?php _e("Inherit from Category", "gd-star-rating"); ?></option><?php } ?>
    <?php if ($parent) { ?><option value="P"<?php echo $selected == 'P' ? ' selected="selected"' : ''; ?>><?php _e("Inherit from Parent", "gd-star-rating"); ?></option><?php } ?>
</select>
        <?php
    } 

    function render_insert_position($name, $selected = "bottom", $width = 180, $style = '') {
        ?>
<select style="width: <?php echo $width ?>px; <?php echo $style; ?>" name="<?php echo $name; ?>" id="<?php echo $name; ?>">
    <option value="bottom"<?php echo $selected == 'bottom' ? ' selected="selected"' : ''; ?>><?php _e("Bottom", "gd-star-rating"); ?></option>
    <option value="top"<?php echo $selected == 'top' ? ' selected="selected"' : ''; ?>><?php _e("Top", "gd-star-rating"); ?></option>
    <option value="both"<?php echo $selected == 'both' ? ' selected="selected"' : ''; ?>><?php _e("Top and Bottom", "gd-star-rating"); ?></option>
</select>
        <?php
    }

    function render_countdown_combo($name, $selected = "H", $width = 180, $style = '', $row_zero = false, $cat = false) {
        ?>
<select style="width: <?php echo $width ?>px; <?php echo $style; ?>" name="<?php echo $name; ?>" id="<?php echo $name; ?>">
    <?php if ($row_zero) { ?> <option value=""<?php echo $selected == '/' ? ' selected="selected"' : ''; ?>>/</option> <?php } ?>
    <option value="H"<?php echo $selected == 'H' ? ' selected="selected"' : ''; ?>><?php _e("Hours", "gd-star-rating"); ?></option>
    <option value="D"<?php echo $selected == 'D' ? ' selected="selected"' : ''; ?>><?php _e("Days", "gd-star-rating"); ?></option>
    <option value="M"<?php echo $selected == 'M' ? ' selected="selected"' : ''; ?>><?php _e("Months", "gd-star-rating"); ?></option>
    <?php if (!$cat) { ?><option value="I"<?php echo $selected == 'I' ? ' selected="selected"' : ''; ?>><?php _e("Inherit from Category", "gd-star-rating"); ?></option><?php } ?>
</select>
        <?php
    } 

    function render_star_sizes($name, $selected = 20, $width = 120, $extraSel = "") {
        ?>
<select style="width: <?php echo $width ?>px;" name="<?php echo $name; ?>" id="<?php echo $name; ?>" <?php echo $extraSel; ?>>
    <option value="12"<?php echo $selected == 12 ? ' selected="selected"' : ''; ?>><?php _e("Mini", "gd-star-rating"); ?> [12px]</option>
    <option value="16"<?php echo $selected == 16 ? ' selected="selected"' : ''; ?>><?php _e("Icon", "gd-star-rating"); ?> [16px]</option>
    <option value="20"<?php echo $selected == 20 ? ' selected="selected"' : ''; ?>><?php _e("Small", "gd-star-rating"); ?> [20px]</option>
    <option value="24"<?php echo $selected == 24 ? ' selected="selected"' : ''; ?>><?php _e("Standard", "gd-star-rating"); ?> [24px]</option>
    <option value="30"<?php echo $selected == 30 ? ' selected="selected"' : ''; ?>><?php _e("Medium", "gd-star-rating"); ?> [30px]</option>
    <option value="46"<?php echo $selected == 46 ? ' selected="selected"' : ''; ?>><?php _e("Big", "gd-star-rating"); ?> [46px]</option>
</select>
        <?php
    }

    function render_thumbs_sizes($name, $selected = 20, $width = 120, $extraSel = "") {
        ?>
<select style="width: <?php echo $width ?>px;" name="<?php echo $name; ?>" id="<?php echo $name; ?>" <?php echo $extraSel; ?>>
    <option value="12"<?php echo $selected == 12 ? ' selected="selected"' : ''; ?>><?php _e("Mini", "gd-star-rating"); ?> [12px]</option>
    <option value="16"<?php echo $selected == 16 ? ' selected="selected"' : ''; ?>><?php _e("Icon", "gd-star-rating"); ?> [16px]</option>
    <option value="20"<?php echo $selected == 20 ? ' selected="selected"' : ''; ?>><?php _e("Small", "gd-star-rating"); ?> [20px]</option>
    <option value="24"<?php echo $selected == 24 ? ' selected="selected"' : ''; ?>><?php _e("Standard", "gd-star-rating"); ?> [24px]</option>
    <option value="32"<?php echo $selected == 32 ? ' selected="selected"' : ''; ?>><?php _e("Medium", "gd-star-rating"); ?> [32px]</option>
    <option value="40"<?php echo $selected == 40 ? ' selected="selected"' : ''; ?>><?php _e("Big", "gd-star-rating"); ?> [40px]</option>
</select>
        <?php
    }

    function render_thumbs_sizes_tinymce($name, $selected = "20", $width = 130) {
        GDSRHelper::render_thumbs_sizes($name, $selected, $width);
    }

    function render_star_sizes_tinymce($name, $selected = "20", $width = 130) {
        GDSRHelper::render_star_sizes($name, $selected, $width);
    }

    function render_rss_render($name, $selected = 'both', $width = 180) {
        ?>
<select style="width: <?php echo $width ?>px;" name="<?php echo $name; ?>" id="<?php echo $name; ?>">
    <option value="both"<?php echo $selected == 'both' ? ' selected="selected"' : ''; ?>><?php _e("Stars and text", "gd-star-rating"); ?></option>
    <option value="stars"<?php echo $selected == 'stars' ? ' selected="selected"' : ''; ?>><?php _e("Stars only", "gd-star-rating"); ?></option>
    <option value="text"<?php echo $selected == 'text' ? ' selected="selected"' : ''; ?>><?php _e("Text only", "gd-star-rating"); ?></option>
</select>
        <?php
    }

    function render_alignment($name, $selected = 'left', $width = 180) {
        ?>
<select style="width: <?php echo $width ?>px;" name="<?php echo $name; ?>" id="<?php echo $name; ?>">
    <option value="none"<?php echo $selected == 'none' ? ' selected="selected"' : ''; ?>><?php _e("No alignment", "gd-star-rating"); ?></option>
    <option value="left"<?php echo $selected == 'left' ? ' selected="selected"' : ''; ?>><?php _e("Left", "gd-star-rating"); ?></option>
    <option value="center"<?php echo $selected == 'center' ? ' selected="selected"' : ''; ?>><?php _e("Center", "gd-star-rating"); ?></option>
    <option value="right"<?php echo $selected == 'right' ? ' selected="selected"' : ''; ?>><?php _e("Right", "gd-star-rating"); ?></option>
</select>
        <?php
    }

    function render_loaders($name, $selected = 'bar', $cls = 'jqloaderarticle', $width = 180, $extraSel = "", $square_only = false) {
        ?>
<select class="<?php echo $cls ?>" style="width: <?php echo $width ?>px;" name="<?php echo $name; ?>" id="<?php echo $name; ?>" <?php echo $extraSel; ?>>
    <option value=""<?php echo $selected == '' ? ' selected="selected"' : ''; ?>><?php _e("No Animation", "gd-star-rating"); ?></option>
    <option value="arrows"<?php echo $selected == 'arrows' ? ' selected="selected"' : ''; ?>>Arrows [16x16]</option>
    <?php if (!$square_only) { ?><option value="bar"<?php echo $selected == 'bar' ? ' selected="selected"' : ''; ?>>Bar [208x13]</option><?php } ?>
    <option value="circle"<?php echo $selected == 'circle' ? ' selected="selected"' : ''; ?>>Circle [16x16]</option>
    <option value="flower"<?php echo $selected == 'flower' ? ' selected="selected"' : ''; ?>>Flower [15x15]</option>
    <?php if (!$square_only) { ?><option value="gauge"<?php echo $selected == 'gauge' ? ' selected="selected"' : ''; ?>>Gauge [128x15]</option><?php } ?>
    <?php if (!$square_only) { ?><option value="squares"<?php echo $selected == 'squares' ? ' selected="selected"' : ''; ?>>Squares [43x11]</option><?php } ?>
    <?php if (!$square_only) { ?><option value="fountain"<?php echo $selected == 'fountain' ? ' selected="selected"' : ''; ?>>Fountain [128x16]</option><?php } ?>
    <option value="broken"<?php echo $selected == 'broken' ? ' selected="selected"' : ''; ?>>Broken [16x16]</option>
    <option value="brokenbig"<?php echo $selected == 'brokenbig' ? ' selected="selected"' : ''; ?>>Broken Big [24x24]</option>
    <?php if (!$square_only) { ?><option value="lines"<?php echo $selected == 'lines' ? ' selected="selected"' : ''; ?>>Lines [96x12]</option><?php } ?>
    <option value="snake"<?php echo $selected == 'snake' ? ' selected="selected"' : ''; ?>>Snake [12x12]</option>
    <option value="radar"<?php echo $selected == 'radar' ? ' selected="selected"' : ''; ?>>Radar [16x16]</option>
    <option value="snakebig"<?php echo $selected == 'snakebig' ? ' selected="selected"' : ''; ?>>Snake Big [24x24]</option>
    <option value="triangles"<?php echo $selected == 'triangles' ? ' selected="selected"' : ''; ?>>Triangles [12x12]</option>
</select>
        <?php
    }

    function render_placement($name, $selected = 'bottom', $width = 180) {
        ?>
<select style="width: <?php echo $width ?>px;" name="<?php echo $name; ?>" id="<?php echo $name; ?>">
    <option value="hide"<?php echo $selected == 'hide' ? ' selected="selected"' : ''; ?>><?php _e("Always hide", "gd-star-rating"); ?></option>
    <option value="top"<?php echo $selected == 'top' ? ' selected="selected"' : ''; ?>><?php _e("Top", "gd-star-rating"); ?></option>
    <option value="top_hidden"<?php echo $selected == 'top_hidden' ? ' selected="selected"' : ''; ?>><?php _e("Top (hide if empty)", "gd-star-rating"); ?></option>
    <option value="bottom"<?php echo $selected == 'bottom' ? ' selected="selected"' : ''; ?>><?php _e("Bottom", "gd-star-rating"); ?></option>
    <option value="bottom_hidden"<?php echo $selected == 'bottom_hidden' ? ' selected="selected"' : ''; ?>><?php _e("Bottom (hide if empty)", "gd-star-rating"); ?></option>
    <option value="left"<?php echo $selected == 'left' ? ' selected="selected"' : ''; ?>><?php _e("Left", "gd-star-rating"); ?></option>
    <option value="left_hidden"<?php echo $selected == 'left_hidden' ? ' selected="selected"' : ''; ?>><?php _e("Left (hide if empty)", "gd-star-rating"); ?></option>
    <option value="right"<?php echo $selected == 'right' ? ' selected="selected"' : ''; ?>><?php _e("Right", "gd-star-rating"); ?></option>
    <option value="right_hidden"<?php echo $selected == 'right_hidden' ? ' selected="selected"' : ''; ?>><?php _e("Right (hide if empty)", "gd-star-rating"); ?></option>
</select>
        <?php
    }

    function render_dash_widget_vote($data, $cls = "", $id = "") {
        global $userdata, $gdsr;
        $user_level = intval($userdata->user_level);

        echo '<div id="gdsr-latest-votes'.$id.'"'.($cls != "" ? ' class="'.$cls.'"' : '').'>';
        $first = true;
        $tr_class = "";
        foreach ($data as $row) {
            $user = $row->user_id == 0 ? __("visitor", "gd-star-rating") : $row->display_name;
            $voteon = $votevl = $loguser = $pocmlog = $postlog = "";

            if ($row->vote_type == "artthumb" || $row->vote_type == "cmmthumb") {
                $votevl = __("Thumb", "gd-star-rating")." <strong>".($row->vote > 0 ? "UP" : "DOWN")."</strong> ";
            } else if ($row->vote_type == "multis") {
                $voteval = intval($row->vote) / 10;
                if ($row->vote == 0) {
                    $set = wp_gdget_multi_set($row->multi_id);
                    $voteval = GDSRDBMulti::get_multi_rating_from_single_object($set, unserialize($row->object));
                }
                $votevl = __("Multi Vote", "gd-star-rating")." <strong>".$voteval."</strong> ";
            } else {
                $votevl = __("Vote", "gd-star-rating")." <strong>".$row->vote."</strong> ";
            }

            if ($row->vote_type == "article" || $row->vote_type == "artthumb" || $row->vote_type == "multis") {
                $post = get_post($row->id);
                $voteon = '<a href="'.get_permalink($post->ID).'"><span style="color: #2683AE">'.$post->post_title.'</span></a>';
                $pocmlog = sprintf("admin.php?page=gd-star-rating-stats&gdsr=voters&pid=%s&vt=%s&vg=total", $row->id, $row->vote_type);
                $pocmlog = sprintf('<a href="%s">%s</a>', $pocmlog, __("post", "gd-star-rating"));
            } else {
                $comment = get_comment($row->id);
                $post = get_post($comment->comment_post_ID);
                $voteon = ' <a href="'.get_comment_link($comment).'">'.__("comment", "gd-star-rating").'</a> '.__("for", "gd-star-rating").' <a href="'.get_permalink($post->ID).'"><span style="color: #2683AE">'.$post->post_title.'</span></a>';
                $pocmlog = sprintf("admin.php?page=gd-star-rating-stats&gdsr=voters&pid=%s&vt=%s&vg=total", $row->id, $row->vote_type);
                $pocmlog = sprintf('<a href="%s">%s</a>', $pocmlog, __("comment", "gd-star-rating"));
                $pctype = $row->vote_type == "comment" ? "article" : "artthumb";
                $postlog = sprintf("admin.php?page=gd-star-rating-stats&gdsr=voters&pid=%s&vt=%s&vg=total", $comment->comment_post_ID, $pctype);
                $postlog = sprintf('<a href="%s">%s</a>', $postlog, __("post", "gd-star-rating"));
            }

            if ($row->user_id == 0) {
                $loguser = sprintf("admin.php?page=gd-star-rating-users&gdsr=userslog&ui=0&vt=%s&un=Visitor", $row->vote_type);
                $loguser = sprintf('<a href="%s">%s</a>', $loguser, __("visitors", "gd-star-rating"));
            } else {
                $loguser = sprintf("admin.php?page=gd-star-rating-users&gdsr=userslog&ui=%s&vt=%s&un=%s", $row->user_id, $row->vote_type, $row->display_name);
                $loguser = sprintf('<a href="%s">%s</a>', $loguser, __("user", "gd-star-rating"));
            }

            ?>

            <div class="gdsr-latest-item<?php echo $tr_class; echo $first ? " first" : ""; ?><?php echo $row->user_id > 0 ? " user" : ""; ?>">
                <?php echo get_avatar($row->user_email, 32); ?>
                <h5><?php echo '<span style="color: #CC0000">'.$votevl.'</span>'; _e("from", "gd-star-rating"); ?> <strong style="color: <?php echo $row->user_id == 0 ? "blue" : "green"; ?>"><?php echo $user; ?></strong> <?php _e("on", "gd-star-rating"); ?> <?php echo $voteon; ?></h5>
                <p class="datx"><?php echo $row->voted; ?></p>
                <p class="linx">
                    <?php if ($user_level >= intval($gdsr->o["security_showip_user_level"])) { ?>
                    <strong><?php _e("ip", "gd-star-rating"); ?>:</strong> <span style="color: blue"><?php echo $row->ip; ?></span> |
                    <?php } ?>
                    <strong><?php _e("log", "gd-star-rating"); ?>:</strong> <?php echo $loguser; ?>, <?php echo $pocmlog; ?><?php if ($postlog != "") echo ", "; echo $postlog; ?>
                </p>
                <div class="clear"></div>
            </div>

            <?php

                $tr_class = $tr_class == "" ? " alter" : "";
                $first = false;
        }
        echo '</div>';
    }
}

?>