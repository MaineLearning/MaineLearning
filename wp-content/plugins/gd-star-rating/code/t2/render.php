<?php

class GDSRRenderT2 {
    // get template
    function get_template($template_id, $section) {
        if (intval($template_id) == 0) {
            $t = gdTemplateDB::get_templates($section, true, true);
            $template_id = $t->template_id;
            wp_gdtpl_cache_template($t);
        }

        return new gdTemplateRender($template_id, $section);
    }

    // prepare data
    function prepare_wbr($widget) {
        global $gdsr, $wpdb;
        
        $query = $widget["source"] == "thumbs" ? GDSRX::get_totals_thumbs($widget) : GDSRX::get_totals_standard($widget);
        $query = apply_filters("gdsr_query_totals", $query, $widget);
        $sql = GDSRX::compile_query($query);

        $data = $wpdb->get_row($sql);
        $data->max_rating = $widget["source"] == "thumbs" ? 0 : $gdsr->o["stars"];

        if ($data->votes == null) {
            $data->votes = 0;
            $data->voters = 0;
        }

        if ($data->votes > 0) {
            $data->rating = $widget["source"] == "thumbs" ? $data->score : @number_format($data->votes / $data->voters, 1);
            $data->bayes_rating = $widget["source"] == "thumbs" ? 0 : $gdsr->bayesian_estimate($data->voters, $data->rating, $data->max_rating);
            $data->percentage = $widget["source"] == "thumbs" ? 0 : floor((100 / $data->max_rating) * $data->rating);
        }

        return $data;
    }

    function prepare_data_retrieve($widget, $min = 0) {
        global $wpdb;
        if ($widget["source"] == "standard"){
            $query = GDSRX::get_widget_standard($widget, $min);
        } else if ($widget["source"] == "multis") {
            $query = GDSRX::get_widget_multis($widget, $min);
        } else {
            $query = GDSRX::get_widget_thumbs($widget, $min);
        }

        $query = apply_filters("gdsr_query_results", $query, $widget);
        $sql = GDSRX::compile_query($query);

        return $wpdb->get_results($sql);
    }

    function prepare_image($url, $x, $y) {
        $wpcurl = strlen(WP_CONTENT_URL);
        if (substr($url, 0, $wpcurl) != WP_CONTENT_URL) return $url;
        else {
            $path = WP_CONTENT_DIR.substr($url, $wpcurl);
            if (!file_exists($path)) return $url;
            else {
                $new_path = strlen($path) - 1 - strlen(end(explode(".", $path)));
                $new_path = substr($path, 0, $new_path)."-".$x."x".$y.".".end(explode(".", $path));
                if (!file_exists($new_path)) {
                    require_once(ABSPATH.'wp-admin/includes/image.php');
                    $img = image_resize($path, $x, $y, true);
                    if (!is_string($img)) return $url;
                    else return trailingslashit(dirname($url)).basename($img);
                } else {
                    $new_url = strlen($url) - 1 - strlen(end(explode(".", $url)));
                    return substr($url, 0, $new_url)."-".$x."x".$y.".".end(explode(".", $url));
                }
            }
        }
    }

    function prepare_content($r) {
        $content = apply_filters('the_content', $r->post_content);
        $content = str_replace(']]>', ']]>', $content);
        return $content;
    }

    function prepare_excerpt($length, $r) {
        $text = trim($r->post_excerpt);
        if ($text == "") {
            $text = str_replace(']]>', ']]&gt;', $r->post_content);
            $text = strip_tags($text);
        }
        $text = gdFunctionsGDSR::trim_to_words($text, $length);
        return $text;
    }

    function prepare_wsr($widget, $template) {
        global $gdsr;

        $bayesian_calculated = !(strpos($template, "%BAYES_") === false);

        $t_rate = !(strpos($template, "%RATE_TREND%") === false);
        $t_vote = !(strpos($template, "%VOTE_TREND%") === false);
        $a_name = !(strpos($template, "%AUTHOR_NAME%") === false);
        $a_link = !(strpos($template, "%AUTHOR_LINK%") === false);

        if ($widget["column"] == "bayes" && !$bayesian_calculated) $widget["column"] == "rating";
        $all_rows = GDSRRenderT2::prepare_data_retrieve($widget, $gdsr->o["bayesian_minimal"]);

        if (count($all_rows) > 0) {
            $trends = array();
            $trends_calculated = false;
            if ($t_rate || $t_vote) {
                $idx = array();
                foreach ($all_rows as $row) {
                    switch ($widget["grouping"]) {
                        case "post":
                            $id = $row->post_id;
                            break;
                        case "category":
                            $id = $row->term_id;
                            break;
                        case "user":
                            $id = $row->id;
                            break;
                    }
                    $idx[] = $id;
                }
                $trends = GDSRX::get_trend_calculation(join(", ", $idx), $widget["grouping"], $widget['show'], $gdsr->o["trend_last"], $gdsr->o["trend_over"], $widget['source'], $widget['source_set']);
                $trends_calculated = true;
            }

            $stars = $gdsr->o["stars"];
            $review_stars = $gdsr->o["review_stars"];
            if ($widget["source"] == "multis") {
                $set = wp_gdget_multi_set($widget["source_set"]);
                $stars = $review_stars = $set->stars;
            }

            $new_rows = array();
            foreach ($all_rows as $row) {
                if ($widget["image_from"] == "content") {
                    $row->image = gdFunctionsGDSR::get_image_from_text($row->post_content);
                } else if ($widget["image_from"] == "custom") {
                    $row->image = get_post_meta($row->post_id, $widget["image_custom"], true);
                } else $row->image = "";

                $row->image = apply_filters('gdsr_widget_image_url_prepare', $row->image, $widget, $row);

                if ($row->image != "" && intval($widget["image_resize_x"]) > 0 && intval($widget["image_resize_y"]) > 0) {
                    $row->image = GDSRRenderT2::prepare_image($row->image, $widget["image_resize_x"], $widget["image_resize_y"]);
                }

                $row->votes_plus = 0;
                $row->votes_minus = 0;
                if ($widget['source'] == "thumbs") {
                    if ($widget['show'] == "total") {
                        $row->votes = $row->rating = $row->user_recc_plus - $row->user_recc_minus + $row->visitor_recc_plus - $row->visitor_recc_minus;
                        $row->voters = $row->user_recc_plus + $row->user_recc_minus + $row->visitor_recc_plus + $row->visitor_recc_minus;
                        $row->votes_plus = $row->user_recc_plus + $row->visitor_recc_plus;
                        $row->votes_minus = $row->user_recc_minus + $row->visitor_recc_minus;
                    }
                    if ($widget['show'] == "visitors") {
                        $row->votes = $row->rating = $row->visitor_recc_plus - $row->visitor_recc_minus;
                        $row->voters = $row->visitor_recc_plus + $row->visitor_recc_minus;
                        $row->votes_plus = $row->visitor_recc_plus;
                        $row->votes_minus = $row->visitor_recc_minus;
                    }
                    if ($widget['show'] == "users") {
                        $row->votes = $row->rating = $row->user_recc_plus - $row->user_recc_minus;
                        $row->voters = $row->user_recc_plus + $row->user_recc_minus;
                        $row->votes_plus = $row->user_recc_plus;
                        $row->votes_minus = $row->user_recc_minus;
                    }

                    $row->bayesian = -1;
                } else {
                    if ($widget['show'] == "total") {
                        $row->votes = $row->user_votes + $row->visitor_votes;
                        $row->voters = $row->user_voters + $row->visitor_voters;
                    }
                    if ($widget['show'] == "visitors") {
                        $row->votes = $row->visitor_votes;
                        $row->voters = $row->visitor_voters;
                    }
                    if ($widget['show'] == "users") {
                        $row->votes = $row->user_votes ;
                        $row->voters = $row->user_voters;
                    }

                    $row->rating = $row->voters == 0 ? 0 : @number_format($row->votes / $row->voters, 1);
                    $row->review = $row->review == 0 ? 0 : @number_format($row->review / $row->counter, 1);
                    $row->bayesian = $bayesian_calculated ? $gdsr->bayesian_estimate($row->voters, $row->rating, $stars) : -1;
                }
                $new_rows[] = $row;
            }

            $set_rating = $set_voting = null;
            if ($trends_calculated) {
                $set_rating = $gdsr->g->find_trend($widget["trends_rating_set"]);
                $set_voting = $gdsr->g->find_trend($widget["trends_voting_set"]);
            }

            $all_rows = array();
            foreach ($new_rows as $row) {
                $row->rating_stars = $row->bayesian_stars = $row->rating_thumb = $row->review_stars = "";

                if (strlen($row->title) > $widget["tpl_title_length"] - 3 && $widget["tpl_title_length"] > 0) {
                    $row->title = substr($row->title, 0, $widget["tpl_title_length"] - 3)." ...";
                }
                $row->content = GDSRRenderT2::prepare_content($row);
                $row->excerpt = GDSRRenderT2::prepare_excerpt($widget["excerpt_words"], $row);

                $row->content = apply_filters('gdsr_widget_post_content', $row->content, $row, $widget);
                $row->excerpt = apply_filters('gdsr_widget_post_excerpt', $row->excerpt, $row, $widget);
                $row->title =   apply_filters('gdsr_widget_post_title',   $row->title,   $row, $widget);

                if ($a_link || $a_name && intval($row->author) > 0) {
                    $user = get_userdata($row->author);
                    $row->author_name = $user->display_name;
                    $row->author_url = get_author_posts_url(intval($row->author));
                } else {
                    $row->author_name = "";
                    $row->author_url = "";
                }

                if ($trends_calculated) {
                    $empty = $gdsr->e;

                    switch ($widget["grouping"]) {
                        case "post":
                            $id = $row->post_id;
                            break;
                        case "taxonomy":
                        case "category":
                            $id = $row->term_id;
                            break;
                        case "user":
                            $id = $row->id;
                            break;
                    }
                    $t = $trends[$id];
                    switch ($widget["trends_rating"]) {
                        case "img":
                            $rate_url = is_null($set_rating) ? "" : $set_rating->get_url();
                            $image_loc = "center";
                            switch ($t->trend_rating) {
                                case -1:
                                    $image_loc = "bottom";
                                    break;
                                case 0:
                                    $image_loc = "center";
                                    break;
                                case 1:
                                    $image_loc = "top";
                                    break;
                            }
                            $image_bg = sprintf('background: url(%s) %s no-repeat; height: %spx; width: %spx;', $rate_url, $image_loc, $set_rating->size, $set_rating->size);
                            $row->item_trend_rating = sprintf('<img class="trend" src="%s" style="%s" width="%s" height="%s"></img>', $gdsr->e, $image_bg, $set_rating->size, $set_rating->size);
                            break;
                        case "txt":
                            switch ($t->trend_rating) {
                                case -1:
                                    $row->item_trend_rating = $widget["trends_rating_fall"];
                                    break;
                                case 0:
                                    $row->item_trend_rating = $widget["trends_rating_same"];
                                    break;
                                case 1:
                                    $row->item_trend_rating = $widget["trends_rating_rise"];
                                    break;
                            }
                            break;
                    }
                    switch ($widget["trends_voting"]) {
                        case "img":
                            $vote_url = is_null($set_voting) ? "" : $set_voting->get_url();
                            $image_loc = "center";
                            switch ($t->trend_voting) {
                                case -1:
                                    $image_loc = "bottom";
                                    break;
                                case 0:
                                    $image_loc = "center";
                                    break;
                                case 1:
                                    $image_loc = "top";
                                    break;
                            }
                            $image_bg = sprintf('background: url(%s) %s no-repeat; height: %spx; width: %spx;', $vote_url, $image_loc, $set_voting->size, $set_voting->size);
                            $row->item_trend_voting = sprintf('<img class="trend" src="%s" style="%s" width="%s" height="%s"></img>', $gdsr->e, $image_bg, $set_voting->size, $set_voting->size);
                            break;
                        case "txt":
                            switch ($t->trend_voting) {
                                case -1:
                                    $row->item_trend_voting = $widget["trends_voting_fall"];
                                    break;
                                case 0:
                                    $row->item_trend_voting = $widget["trends_voting_same"];
                                    break;
                                case 1:
                                    $row->item_trend_voting = $widget["trends_voting_rise"];
                                    break;
                            }
                            break;
                    }
                }

                switch ($widget["grouping"]) {
                    case "post":
                        $row->permalink = get_permalink($row->post_id);
                        break;
                    case "taxonomy":
                        $row->permalink = get_term_link($row->slug, $widget["taxonomy"]);
                        break;
                    case "category":
                        $row->permalink = get_category_link($row->term_id);
                        break;
                    case "user":
                        $row->permalink = get_author_posts_url(intval($row->id));
                        break;
                }

                if ($widget["source"] == "thumbs") {
                    if (!(strpos($template, "%THUMB%") === false)) $row->rating_thumb = GDSRRender::render_static_thumb($widget['rating_thumb'], $widget['rating_thumb_size'], $row->rating);
                } else {
                    if (!(strpos($template, "%STARS%") === false)) $row->rating_stars = GDSRRender::render_static_stars($widget['rating_stars'], $widget['rating_size'], $stars, $row->rating);
                    if (!(strpos($template, "%BAYES_STARS%") === false) && $row->bayesian > -1) $row->bayesian_stars = GDSRRender::render_static_stars($widget['rating_stars'], $widget['rating_size'], $stars, $row->bayesian);
                }
                if (!(strpos($template, "%REVIEW_STARS%") === false) && $row->review > -1) $row->review_stars = GDSRRender::render_static_stars($widget['review_stars'], $widget['review_size'], $review_stars, $row->review);

                $all_rows[] = $row;
            }
        }

        if ($widget["column"] == "votes") $widget["column"] = "voters";
        if ($widget["column"] == "post_title") $widget["column"] = "title";
        if ($widget["column"] == "count") $widget["column"] = "counter";
        if ($widget["column"] == "bayes") $widget["column"] = "bayesian";
        if ($widget["column"] == "id") $widget["column"] = "post_id";

        $properties = array();
        $properties[] = array("property" => $widget["column"], "order" => $widget["order"]);
        if ($widget["column"] == "rating")
            $properties[] = array("property" => "voters", "order" => $widget["order"]);
        $sort = new gdSortObjectsArrayGDSR($all_rows, $properties);

        $tr_class = "odd";
        $all_rows = array();
        foreach ($sort->sorted as $row) {
            $row->table_row_class = $tr_class;
            $tr_class = $tr_class == "odd" ? "even" : "odd";
            $all_rows[] = $row;
        }

        $all_rows = apply_filters("gdsr_widget_data_prepare", $all_rows);

        return $all_rows;
    }

    function prepare_wcr($widget, $template) {
        global $gdsr, $wpdb;

        $post_id = $gdsr->widget_post_id;
        $sql = GDSRX::get_widget_comments($widget, $post_id);
        $all_rows = $wpdb->get_results($sql);

        if (count($all_rows) > 0) {
            $new_rows = array();
            foreach ($all_rows as $row) {
                if ($widget['show'] == "total") {
                    $row->votes = $row->user_votes + $row->visitor_votes;
                    $row->voters = $row->user_voters + $row->visitor_voters;
                }
                if ($widget['show'] == "visitors") {
                    $row->votes = $row->visitor_votes;
                    $row->voters = $row->visitor_voters;
                }
                if ($widget['show'] == "users") {
                    $row->votes = $row->user_votes ;
                    $row->voters = $row->user_voters;
                }
                if ($row->voters == 0) $row->rating = 0;
                else $row->rating = @number_format($row->votes / $row->voters, 1);
                $new_rows[] = $row;
            }

            $all_rows = array();
            $pl = get_permalink($post_id);
            foreach ($new_rows as $row) {
                $row->comment_content = strip_tags($row->comment_content);
                if (strlen($row->comment_content) > $widget["text_max"] - 3 && $widget["text_max"] > 0)
                    $row->comment_content = substr($row->comment_content, 0, $widget["text_max"] - 3)." ...";

                $row->comment_content = apply_filters('gdsr_comments_widget_comment_content', $row->comment_content);
                $row->comment_author_email = get_avatar($row->comment_author_email, $widget["avatar"]);

                if (!(strpos($template, "%CMM_STARS%") === false)) $row->rating_stars = GDSRRender::render_static_stars($widget['rating_stars'], $widget['rating_size'], $gdsr->o["cmm_stars"], $row->rating);
                $row->permalink = $pl."#comment-".$row->comment_id;

                $all_rows[] = $row;
            }
        }

        if ($widget["column"] == "votes") $widget["column"] = "voters";
        if ($widget["column"] == "id") $widget["column"] = "comment_id";

        $properties = array();
        $properties[] = array("property" => $widget["column"], "order" => $widget["order"]);
        if ($widget["column"] == "rating") $properties[] = array("property" => "voters", "order" => $widget["order"]);

        $sort = new gdSortObjectsArrayGDSR($all_rows, $properties);

        $tr_class = "odd";
        $all_rows = array();
        foreach ($sort->sorted as $row) {
            $row->table_row_class = $tr_class;
            $tr_class = $tr_class == "odd" ? "even" : "odd";
            $all_rows[] = $row;
        }

        $all_rows = apply_filters('gdsr_comments_widget_data_prepare', $all_rows);
        return $all_rows;
    }

    // main rendering data
    function render_mri($template_id, $rpar = array()) {
        $rdef = array("post_id" => 0, "style" => "oxygen", "set" => "", "height" => 20, "css" => "");
        $rpar = wp_parse_args($rpar, $rdef);
        $rpar = apply_filters('gdsr_t2parameters_mri', $rpar);
        extract($rpar, EXTR_SKIP);

        $template = GDSRRenderT2::get_template($template_id, "MRI");
        $tpl_render = $template->elm["normal"];
        $tpl_render = html_entity_decode($tpl_render);
        $tpl_render = apply_filters('gdsr_t2render_mri_normal', $tpl_render, $template, $rpar, "normal");

        $empty_value = str_repeat("0X", count($set->object));
        $empty_value = substr($empty_value, 0, strlen($empty_value) - 1);

        $rater = '<div id="gdsr_mur_block_'.$post_id.'_'.$set->multi_id.'" class="ratingmulti gdsr-rating-block">';
        $rater.= '<input class="gdsr-mur-cls-rt gdsr_int_multi_'.$post_id.'_'.$set->multi_id.'" type="hidden" id="gdsr_mur_value" name="gdsr_mur_value" value="'.$empty_value.'" />';
        $rater.= '<input type="hidden" name="gdsr_mur_set" value="'.$set->multi_id.'" />';

        $i = 0;
        $rating_stars = "";
        $table_row_class = $template->dep["MRS"]->dep["ETR"];
        foreach ($set->object as $el) {
            $single_row = html_entity_decode($template->dep["MRS"]->elm["item"]);
            $single_row = str_replace('%ELEMENT_NAME%', $el, $single_row);
            $single_row = str_replace('%ELEMENT_ID%', $i, $single_row);
            $single_row = str_replace('%ELEMENT_VALUE%', 0, $single_row);
            $single_row = str_replace('%ELEMENT_STARS%', GDSRRender::rating_stars_multi($style, $post_id, $template_id, $set->multi_id, $i, $height, $set->stars, true, 0, "", true), $single_row);
            $single_row = str_replace('%TABLE_ROW_CLASS%', is_odd($i) ? $table_row_class->elm["odd"] : $table_row_class->elm["even"], $single_row);
            $rating_stars.= $single_row;
            $i++;
        }

        $tpl_render = str_replace("%MUR_RATING_STARS%", $rating_stars, $tpl_render);
        $tpl_render = str_replace("%MUR_CSS_BLOCK%", $css, $tpl_render);
        $rater.= $tpl_render."</div>";
        return $rater;
    }

    function render_mre($template_id, $rpar = array()) {
        $rdef = array("post_id" => 0, "votes" => 0, "style" => "oxygen", "set" => "", "height" => 20, "css" => "", "allow_vote" => true);
        $rpar = wp_parse_args($rpar, $rdef);
        $rpar = apply_filters('gdsr_t2parameters_mre', $rpar);
        extract($rpar, EXTR_SKIP);

        $template = GDSRRenderT2::get_template($template_id, "MRE");
        $tpl_render = $template->elm["normal"];
        $tpl_render = html_entity_decode($tpl_render);
        $tpl_render = apply_filters('gdsr_t2render_mre_normal', $tpl_render, $template, $rpar, "normal");

        $rater = '<div id="gdsr_mur_block_'.$post_id.'_'.$set->multi_id.'" class="ratingmulti gdsr-review-block">';

        $empty_value = str_repeat("0X", count($set->object));
        $empty_value = substr($empty_value, 0, strlen($empty_value) - 1);

        $original_value = "";
        foreach($votes as $vote) $original_value.= number_format($vote["rating"], 0)."X";
        $original_value = substr($original_value, 0, strlen($original_value) - 1);
        $rater.= '<input type="hidden" id="gdsr_mur_review_'.$post_id.'_'.$set->multi_id.'" name="gdsrmurreview['.$post_id.']['.$set->multi_id.']" value="'.$original_value.'" />';
        if ($allow_vote) $rater.= '<input type="hidden" class="gdsr_int_multi_'.$post_id.'_'.$set->multi_id.'" name="gdsrmulti['.$post_id.']['.$set->multi_id.']" value="'.$original_value.'" />';

        $i = 0;
        $weighted = 0;
        $total_votes = 0;
        $weight_norm = array_sum($set->weight);
        $rating_stars = "";
        $table_row_class = $template->dep["MRS"]->dep["ETR"];
        foreach ($set->object as $el) {
            $single_row = html_entity_decode($template->dep["MRS"]->elm["item"]);
            $single_row = str_replace('%ELEMENT_NAME%', $el, $single_row);
            $single_row = str_replace('%ELEMENT_ID%', $i, $single_row);
            $single_row = str_replace('%ELEMENT_VALUE%', isset($votes[$i]) ? $votes[$i]["rating"] : "0", $single_row);
            $single_row = str_replace('%ELEMENT_STARS%', GDSRRender::rating_stars_multi($style, $post_id, $template_id, $set->multi_id, $i, $height, $set->stars, $allow_vote, isset($votes[$i]) ? $votes[$i]["rating"] : "0", "", true), $single_row);
            $single_row = str_replace('%TABLE_ROW_CLASS%', is_odd($i) ? $table_row_class->elm["odd"] : $table_row_class->elm["even"], $single_row);
            $rating_stars.= $single_row;

            $weighted += ((isset($votes[$i]) ? $votes[$i]["rating"] : 0) * $set->weight[$i]) / $weight_norm;
            $total_votes += isset($votes[$i]) ? $votes[$i]["votes"] : 0;
            $i++;
        }

        $tpl_render = str_replace("%MUR_RATING_STARS%", $rating_stars, $tpl_render);
        $tpl_render = str_replace("%MUR_CSS_BLOCK%", $css, $tpl_render);
        $rater.= $tpl_render."</div>";
        return $rater;
    }

    function render_ssb($template_id, $rpar = array()) {
        $rdef = array("post_id" => 0, "votes" => 0, "score" => 0, "unit_count" => 10, "header_text" => "", "type" => "");
        $rpar = wp_parse_args($rpar, $rdef);
        $rpar = apply_filters('gdsr_t2parameters_ssb', $rpar);
        extract($rpar, EXTR_SKIP);

        $template = GDSRRenderT2::get_template($template_id, "SSB");
        $tpl_render = $template->elm["normal"];
        $tpl_render = html_entity_decode($tpl_render);
        $tpl_render = apply_filters('gdsr_t2render_ssb_normal', $tpl_render, $template, $rpar, "normal");

        $tpl_render = str_replace("%HEADER_TEXT%", html_entity_decode(__($header_text)), $tpl_render);
        $rater_stars = "";

        if ($type == "thumbs") {
            $rating = $score > 0 ? "+".$score : $score;
            $rater_stars = '<img src="'.STARRATING_URL.sprintf("gfx.php?type=thumbs&value=%s", $score).'" />';
        } else if ($type == "multis") {
            $rating = $score;
            $rater_stars = '<img src="'.STARRATING_URL.sprintf("gfx.php?value=%s", $rating).'" />';
        } else {
            $rating2 = $votes > 0 ? $score / $votes : 0;
            if ($rating2 > $unit_count) $rating2 = $unit_count;
            $rating = @number_format($rating2, 1);
            $rater_stars = '<img src="'.STARRATING_URL.sprintf("gfx.php?value=%s", $rating).'" />';
        }

        $rt = str_replace('%RATING%', apply_filters('gdsr_t2_tag_value', $rating, "SSB", "%RATING%"), $tpl_render);
        $rt = str_replace('%VOTES%', apply_filters('gdsr_t2_tag_value', $votes, "SSB", "%VOTES%"), $rt);
        $rt = str_replace('%MAX_RATING%', $unit_count, $rt);
        $rt = str_replace('%RATING_STARS%', $rater_stars, $rt);
        $rt = str_replace('%ID%', $post_id, $rt);

        $word_votes = $template->dep["EWV"];
        $tense = $votes == 1 ? $word_votes->elm["singular"] : $word_votes->elm["plural"];
        $rt = str_replace('%WORD_VOTES%', __($tense), $rt);

        return $rt;
    }

    function render_mrb($template_id, $rpar = array()) {
        $rdef = array("style" => "oxygen", "already_voted" => false, "allow_vote" => true, "votes" => 0, "post_id" => 0, "set" => "", "height" => "",
            "tags_css" => array(), "avg_style" => "oxygen", "avg_size" => 20, "star_factor" => 1, "time_restirctions" => "N", "time_remaining" => 0,
            "time_date" => "", "button_active" => true, "button_text" => "Submit", "debug" => "", "wait_msg" => "", "header_text" => "");
        $rpar = wp_parse_args($rpar, $rdef);
        $rpar = apply_filters('gdsr_t2parameters_mrb', $rpar);
        extract($rpar, EXTR_SKIP);

        $template = GDSRRenderT2::get_template($template_id, "MRB");
        $tpl_render = $template->elm["normal"];
        $tpl_render = html_entity_decode($tpl_render);
        $tpl_render = apply_filters('gdsr_t2render_mrb_normal', $tpl_render, $template, $rpar, "normal");

        foreach ($tags_css as $tag => $value) $tpl_render = str_replace('%'.$tag.'%', $value, $tpl_render);
        $tpl_render = str_replace("%MUR_HEADER_TEXT%", html_entity_decode(__($header_text)), $tpl_render);
        $rater = '<div id="gdsr_mur_block_'.$post_id.'_'.$set->multi_id.'" class="ratingmulti '.$tags_css["MUR_CSS_BLOCK"].'">';;

        $empty_value = str_repeat("0X", count($set->object));
        $empty_value = substr($empty_value, 0, strlen($empty_value) - 1);
        if ($debug != '') $rater.= '<div style="display: none">'.$debug.'</div>';
        if ($allow_vote) $rater.= '<input type="hidden" id="gdsr_multi_'.$post_id.'_'.$set->multi_id.'" name="gdsrmulti['.$post_id.']['.$set->multi_id.']" value="'.$empty_value.'" />';

        if (in_array("%POST_TITLE%", $template->tag["normal"])) {
            $act_post = get_post($post_id);
            $tpl_render = str_replace("%POST_TITLE%", $act_post->post_title, $tpl_render);
        }

        if (in_array("%POST_PERMALINK%", $template->tag["normal"])) {
            $tpl_render = str_replace("%POST_PERMALINK%", get_permalink($post_id), $tpl_render);
        }

        $i = 0;
        $weighted = 0;
        $total_votes = 0;
        $weight_norm = array_sum($set->weight);
        $rating_stars = "";
        $table_row_class = $template->dep["MRS"]->dep["ETR"];
        foreach ($set->object as $el) {
            $single_row = html_entity_decode($template->dep["MRS"]->elm["item"]);
            $single_row = str_replace('%ELEMENT_NAME%', $el, $single_row);
            $single_row = str_replace('%ELEMENT_ID%', $i, $single_row);
            $single_row = str_replace('%ELEMENT_VALUE%', $votes[$i]["rating"], $single_row);
            $single_row = str_replace('%ELEMENT_STARS%', GDSRRender::rating_stars_multi($style, $post_id, $template_id, $set->multi_id, $i, $height, $set->stars * $star_factor, $allow_vote, $votes[$i]["rating"] * $star_factor), $single_row);
            $single_row = str_replace('%TABLE_ROW_CLASS%', is_odd($i) ? $table_row_class->elm["odd"] : $table_row_class->elm["even"], $single_row);
            $rating_stars.= $single_row;

            $weighted += ($votes[$i]["rating"] * $set->weight[$i]) / $weight_norm;
            $total_votes += $votes[$i]["votes"];
            $i++;
        }

        $rating = @number_format($weighted, 1);
        $total_votes = $i == 0 ? 0 : @number_format($total_votes / $i, 0);
        $css = "";
        if ($already_voted) $css.= " voted";
        if (!$allow_vote) $css.= " inactive";

        if (in_array("%MUR_RATING_TEXT%", $template->tag["normal"])) {
            $rating_text = GDSRRenderT2::render_mrt($template->dep["MRT"], array("rating" => $rating, "unit_count" => $set->stars, "votes" => $total_votes, "id" => $post_id, "time_restirctions" => $time_restirctions, "time_remaining" => $time_remaining, "time_date" => $time_date));
            $rating_wait = $allow_vote ? GDSRRender::rating_wait("gdsr_mur_loader_".$post_id."_".$set->multi_id, "100%", "", $wait_msg) : "";
            $voted = $css == "" ? '' : ' class="'.trim($css).'"';
            $rating_text = $rating_wait.'<div'.$voted.' id="gdsr_mur_text_'.$post_id.'_'.$set->multi_id.'">'.$rating_text.'</div>';
            $tpl_render = str_replace("%MUR_RATING_TEXT%", $rating_text, $tpl_render);
        }

        if (in_array("%BUTTON%", $template->tag["normal"])) {
            if ($button_active) $rating_button = '<div class="ratingbutton gdinactive gdsr_multisbutton_as '.$tags_css["MUR_CSS_BUTTON"].'" id="gdsr_button_'.$post_id.'_'.$set->multi_id.'_'.$template_id.'_'.$height.'"><a rel="nofollow">'.$button_text.'</a></div>';
            else $rating_button = "";

            $tpl_render = str_replace("%BUTTON%", $rating_button, $tpl_render);
        }

        $tpl_render = str_replace('%RATING%', apply_filters('gdsr_t2_tag_value', $rating, "MRB", "%RATING%"), $tpl_render);
        $tpl_render = str_replace('%VOTES%', apply_filters('gdsr_t2_tag_value', $total_votes, "MRB", "%VOTES%"), $tpl_render);
        $tpl_render = str_replace('%CSS_AUTO%', apply_filters('gdsr_t2_tag_value', $css, "MRB", "%CSS_AUTO%"), $tpl_render);
        $tpl_render = str_replace('%ID%', $post_id, $tpl_render);

        $tpl_render = str_replace("%MUR_RATING_STARS%", $rating_stars, $tpl_render);
        $tpl_render = str_replace("%AVG_RATING%", apply_filters('gdsr_t2_tag_value', $rating, "MRB", "%AVG_RATING%"), $tpl_render);
        if (in_array("%AVG_RATING_STARS%", $template->tag["normal"])) {
            $avg_id = "gdsr_mur_avgstars_".$post_id."_".$set->multi_id;
            $tpl_render = str_replace("%AVG_RATING_STARS%", GDSRRender::render_static_stars($avg_style, $avg_size, $set->stars * $star_factor, $rating * $star_factor, $avg_id, "", $star_factor), $tpl_render);
        }

        $rater.= $tpl_render."</div>";
        return $rater;
    }

    function render_srb($template_id, $rpar = array()) {
        $rdef = array("post_id" => 0, "class" => "", "type" => "", "votes" => 0, "score" => 0, "style" => "oxygen",
            "unit_width" => 20, "unit_count" => 10, "already_voted" => false, "allow_vote" => true, "user_id" => 0,
            "header_text" => "", "debug" => "", "wait_msg" => "", "time_restirctions" => "N", "time_remaining" => 0,
            "typecls" => "", "tags_css" => array(), "time_date" => "");
        $rpar = wp_parse_args($rpar, $rdef);
        $rpar = apply_filters('gdsr_t2parameters_srb', $rpar);
        extract($rpar, EXTR_SKIP);

        $template = GDSRRenderT2::get_template($template_id, "SRB");
        $tpl_render = $template->elm["normal"];
        $tpl_render = html_entity_decode($tpl_render);
        $tpl_render = apply_filters('gdsr_t2render_srb_normal', $tpl_render, $template, $rpar, "normal");

        foreach ($tags_css as $tag => $value) $tpl_render = str_replace('%'.$tag.'%', $value, $tpl_render);
        $tpl_render = str_replace("%HEADER_TEXT%", html_entity_decode(__($header_text)), $tpl_render);

        $rating2 = $votes > 0 ? $score / $votes : 0;
        if ($rating2 > $unit_count) $rating2 = $unit_count;
        $rating = @number_format($rating2, 1);
        $css = "";
        if ($already_voted) $css.= " voted";
        if (!$allow_vote) $css.= " inactive";

        $rater_id = $typecls."_rater_".$post_id;
        $loader_id = $typecls."_loader_".$post_id;

        $tpl_render = str_replace('%RATING%', apply_filters('gdsr_t2_tag_value', $rating, "SRB", "%RATING%"), $tpl_render);
        $tpl_render = str_replace('%VOTES%', apply_filters('gdsr_t2_tag_value', $votes, "SRB", "%VOTES%"), $tpl_render);
        $tpl_render = str_replace('%CSS_AUTO%', apply_filters('gdsr_t2_tag_value', $css, "SRB", "%CSS_AUTO%"), $tpl_render);
        $tpl_render = str_replace('%ID%', $post_id, $tpl_render);

        if (in_array("%POST_TITLE%", $template->tag["normal"])) {
            $act_post = get_post($post_id);
            $tpl_render = str_replace("%POST_TITLE%", apply_filters('gdsr_t2_tag_value', $act_post->post_title, "SRB", "%POST_TITLE%"), $tpl_render);
        }

        if (in_array("%POST_PERMALINK%", $template->tag["normal"])) {
            $tpl_render = str_replace("%POST_PERMALINK%", apply_filters('gdsr_t2_tag_value', get_permalink($post_id), "SRB", "%POST_PERMALINK%"), $tpl_render);
        }

        if (in_array("%RATING_STARS%", $template->tag["normal"])) {
            $rating2 = apply_filters('gdsr_t2rating_srb_stars_display', $rating2);
            $rating_width = $rating2 * $unit_width;
            $rater_length = $unit_width * $unit_count;
            $rating_stars = GDSRRender::rating_stars($style, $unit_width, $rater_id, $class, $rating_width, $allow_vote, $unit_count, $type, $post_id, $user_id, $loader_id, $rater_length, $typecls, $wait_msg, $template_id);
            $tpl_render = str_replace("%RATING_STARS%", $rating_stars, $tpl_render);
        }

        if (in_array("%RATING_TEXT%", $template->tag["normal"])) {
            $rating_text = GDSRRenderT2::render_srt($template->dep["SRT"], array("rating" => $rating, "unit_count" => $unit_count, "votes" => $votes, "id" => $post_id, "time_restirctions" => $time_restirctions, "time_remaining" => $time_remaining, "time_date" => $time_date));
            $voted = $css == "" ? '' : ' class="'.trim($css).'"';
            $rating_text = '<div id="gdr_text_'.$type.$post_id.'"'.$voted.'>'.$rating_text.'</div>';
            $tpl_render = str_replace("%RATING_TEXT%", $rating_text, $tpl_render);
        }

        if ($debug != '') $tpl_render = '<div style="display: none">'.$debug.'</div>'.$tpl_render;

        return $tpl_render;
    }

    function render_tab($template_id, $rpar = array()) {
        $rdef = array("post_id" => 0, "votes" => 0, "score" => 0, "votes_plus" => 0, "votes_minus" => 0, "style" => "starrating",
            "unit_width" => 20, "already_voted" => false, "allow_vote" => true, "user_id" => 0, "tags_css" => array(), "header_text" => "",
            "debug" => "", "wait_msg" => "", "time_restirctions" => "N", "time_remaining" => 0, "time_date" => "");
        $rpar = wp_parse_args($rpar, $rdef);
        $rpar = apply_filters('gdsr_t2parameters_tab', $rpar);
        extract($rpar, EXTR_SKIP);

        $template = GDSRRenderT2::get_template($template_id, "TAB");
        $tpl_render = $allow_vote ? $template->elm["active"] : $template->elm["inactive"];
        $tpl_render = html_entity_decode($tpl_render);
        $tpl_render = apply_filters('gdsr_t2render_tab_'.($allow_vote ? "active" : "inactive"), $tpl_render, $template, $rpar, $allow_vote ? "active" : "inactive");

        foreach ($tags_css as $tag => $value) $tpl_render = str_replace('%'.$tag.'%', $value, $tpl_render);
        $tpl_render = str_replace("%HEADER_TEXT%", html_entity_decode(__($header_text)), $tpl_render);

        $percent = $votes_plus + $votes_minus == 0 ? 0 : ($votes_plus * 100) / ($votes_plus + $votes_minus);
        $percent = number_format($percent, 0);
        if ($percent == 0) $percent = gdsr_zero_percentage();
        $score_number = $score;
        $score = $score > 0 ? "+".$score : $score;
        $css = intval($score_number) == 0 ? "zero" : (intval($score_number) > 0 ? "positive" : "negative");
        if ($already_voted) $css.= " voted";
        if (!$allow_vote) $css.= " inactive";

        $tpl_render = str_replace('%RATING%', apply_filters('gdsr_t2_tag_value', $score, "TAB", "%RATING%"), $tpl_render);
        $tpl_render = str_replace('%PERCENTAGE%', apply_filters('gdsr_t2_tag_value', $percent, "TAB", "%PERCENTAGE%"), $tpl_render);
        $tpl_render = str_replace('%VOTES%', apply_filters('gdsr_t2_tag_value', $votes, "TAB", "%VOTES%"), $tpl_render);
        $tpl_render = str_replace('%VOTES_UP%', apply_filters('gdsr_t2_tag_value', $votes_plus, "TAB", "%VOTES_UP%"), $tpl_render);
        $tpl_render = str_replace('%VOTES_DOWN%', apply_filters('gdsr_t2_tag_value', $votes_minus, "TAB", "%VOTES_DOWN%"), $tpl_render);
        $tpl_render = str_replace('%CSS_AUTO%', apply_filters('gdsr_t2_tag_value', $css, "TAB", "%CSS_AUTO%"), $tpl_render);
        $tpl_render = str_replace('%ID%', $post_id, $tpl_render);

        if (in_array("%THUMBS_TEXT%", $allow_vote ? $template->tag["active"] : $template->tag["inactive"])) {
            $rating_text = GDSRRenderT2::render_tat($template->dep["TAT"], array("score_number" => $score_number, "percent" => $percent, "votes" => $votes, "score" => $score, "votes_plus" => $votes_plus, "votes_minus" => $votes_minus, "id" => $post_id, "time_restirctions" => $time_restirctions, "time_remaining" => $time_remaining, "time_date" => $time_date));
            $rating_text = '<div id="gdsr_thumb_text_'.$post_id.'_a" class="gdt-size-'.$unit_width.($already_voted ? " voted" : "").($allow_vote ? "" : " inactive").' gdthumbtext">'.$rating_text.'</div>';
            $tpl_render = str_replace("%THUMBS_TEXT%", $rating_text, $tpl_render);
        }

        if (in_array("%THUMB_UP%", $allow_vote ? $template->tag["active"] : $template->tag["inactive"])) {
            if ($allow_vote) {
                $rater = sprintf('<div id="gdsr_thumb_%s_a_up" class="gdt-size-%s gdthumb gdup"><a id="gdsrX%sXupXaX%sX%sX%s" class="gdt-%s" rel="nofollow"></a></div>',
                    $post_id, $unit_width, $post_id, $template_id, $unit_width, $wait_msg == '' ? "N" : "Y", $style);
                if ($wait_msg != '') {
                    $rater_wait = GDSRRender::rating_wait(sprintf("gdsr_thumb_%s_a_loader_up", $post_id), $unit_width."px", ' loadup', $wait_msg);
                    $rater.= sprintf($rater_wait, sprintf("width: %spx; height: %spx;", $unit_width, $unit_width));
                }
            } else {
                $rater = sprintf('<div id="gdsr_thumb_%s_a_up" class="gdt-size-%s gdthumb gdup"><div class="gdt-%s"></div></div>',
                    $post_id, $unit_width, $style);
            }
            $rater = apply_filters('gdsr_thumb_up', $rater, $template);
            $tpl_render = str_replace("%THUMB_UP%", $rater, $tpl_render);
        }

        if (in_array("%THUMB_DOWN%", $allow_vote ? $template->tag["active"] : $template->tag["inactive"])) {
            if ($allow_vote) {
                $rater = sprintf('<div id="gdsr_thumb_%s_a_dw" class="gdt-size-%s gdthumb gddw"><a id="gdsrX%sXdwXaX%sX%sX%s" class="gdt-%s" rel="nofollow"></a></div>',
                    $post_id, $unit_width, $post_id, $template_id, $unit_width, $wait_msg == '' ? "N" : "Y", $style);
                if ($wait_msg != '') {
                    $rater_wait = GDSRRender::rating_wait(sprintf("gdsr_thumb_%s_a_loader_dw", $post_id), $unit_width."px", ' loaddw', $wait_msg);
                    $rater.= sprintf($rater_wait, sprintf("width: %spx; height: %spx;", $unit_width, $unit_width));
                }
            } else {
                $rater = sprintf('<div id="gdsr_thumb_%s_a_dw" class="gdt-size-%s gdthumb gddw"><div class="gdt-%s"></div></div>',
                    $post_id, $unit_width, $style);
            }
            $rater = apply_filters('gdsr_thumb_down', $rater, $template);
            $tpl_render = str_replace("%THUMB_DOWN%", $rater, $tpl_render);
        }

        if ($debug != '') $tpl_render = '<div style="display: none">'.$debug.'</div>'.$tpl_render;
        return $tpl_render;
    }

    function render_tcb($template_id, $rpar = array()) {
        $rdef = array("comment_id" => 0, "votes" => 0, "score" => 0, "votes_plus" => 0, "votes_minus" => 0,
            "style" => "starrating", "unit_width" => 20, "already_voted" => false, "allow_vote" => true,
            "header_text" => "", "debug" => "", "wait_msg" => "", "tags_css" => array(), "user_id" => 0);
        $rpar = wp_parse_args($rpar, $rdef);
        $rpar = apply_filters('gdsr_t2parameters_tcb', $rpar);
        extract($rpar, EXTR_SKIP);

        $template = GDSRRenderT2::get_template($template_id, "TCB");
        $tpl_render = $allow_vote ? $template->elm["active"] : $template->elm["inactive"];
        $tpl_render = html_entity_decode($tpl_render);
        $tpl_render = apply_filters('gdsr_t2render_tcb_'.($allow_vote ? "active" : "inactive"), $tpl_render, $template, $rpar, $allow_vote ? "active" : "inactive");

        foreach ($tags_css as $tag => $value) $tpl_render = str_replace('%'.$tag.'%', $value, $tpl_render);
        $tpl_render = str_replace("%CMM_HEADER_TEXT%", html_entity_decode(__($header_text)), $tpl_render);

        $percent = $votes_plus + $votes_minus == 0 ? 0 : ($votes_plus * 100) / ($votes_plus + $votes_minus);
        $percent = number_format($percent, 0);
        if ($percent == 0) $percent = gdsr_zero_percentage();
        $score_number = $score;
        $score = $score > 0 ? "+".$score : $score;
        $css = intval($score_number) == 0 ? "zero" : (intval($score_number) > 0 ? "positive" : "negative");
        if ($already_voted) $css.= " voted";
        if (!$allow_vote) $css.= " inactive";

        $tpl_render = str_replace('%RATING%', apply_filters('gdsr_t2_tag_value', $score, "TCT", "%RATING%"), $tpl_render);
        $tpl_render = str_replace('%PERCENTAGE%', apply_filters('gdsr_t2_tag_value', $percent, "TCT", "%PERCENTAGE%"), $tpl_render);
        $tpl_render = str_replace('%VOTES%', apply_filters('gdsr_t2_tag_value', $votes, "TCT", "%VOTES%"), $tpl_render);
        $tpl_render = str_replace('%VOTES_UP%', apply_filters('gdsr_t2_tag_value', $votes_plus, "TCT", "%VOTES_UP%"), $tpl_render);
        $tpl_render = str_replace('%VOTES_DOWN%', apply_filters('gdsr_t2_tag_value', $votes_minus, "TCT", "%VOTES_DOWN%"), $tpl_render);
        $tpl_render = str_replace('%CSS_AUTO%', apply_filters('gdsr_t2_tag_value', $css, "TAB", "%CSS_AUTO%"), $tpl_render);
        $tpl_render = str_replace('%ID%', $comment_id, $tpl_render);

        if (in_array("%CMM_THUMBS_TEXT%", $allow_vote ? $template->tag["active"] : $template->tag["inactive"])) {
            $rating_text = GDSRRenderT2::render_tct($template->dep["TCT"], array("score_number" => $score_number, "percent" => $percent, "votes" => $votes, "score" => $score, "votes_plus" => $votes_plus, "votes_minus" => $votes_minus, "id" => $comment_id));
            $rating_text = '<div id="gdsr_thumb_text_'.$comment_id.'_c" class="gdt-size-'.$unit_width.($already_voted ? " voted" : "").($allow_vote ? "" : " inactive").' gdthumbtext">'.$rating_text.'</div>';
            $tpl_render = str_replace("%CMM_THUMBS_TEXT%", $rating_text, $tpl_render);
        }

        if (in_array("%THUMB_UP%", $allow_vote ? $template->tag["active"] : $template->tag["inactive"])) {
            if ($allow_vote) {
                $rater = sprintf('<div id="gdsr_thumb_%s_c_up" class="gdt-size-%s gdthumb gdup"><a id="gdsrX%sXupXcX%sX%sX%s" class="gdt-%s" rel="nofollow"></a></div>',
                    $comment_id, $unit_width, $comment_id, $template_id, $unit_width, $wait_msg == '' ? "N" : "Y", $style);
                if ($wait_msg != '') {
                    $rater_wait = GDSRRender::rating_wait(sprintf("gdsr_thumb_%s_c_loader_up", $comment_id), $unit_width."px", ' loadup', $wait_msg);
                    $rater.= sprintf($rater_wait, sprintf("width: %spx; height: %spx;", $unit_width, $unit_width));
                }
            } else {
                $rater = sprintf('<div id="gdsr_thumb_%s_c_up" class="gdt-size-%s gdthumb gdup"><div class="gdt-%s"></div></div>',
                    $comment_id, $unit_width, $style);
            }
            $rater = apply_filters('gdsr_thumb_up', $rater, $template);
            $tpl_render = str_replace("%THUMB_UP%", $rater, $tpl_render);
        }

        if (in_array("%THUMB_DOWN%", $allow_vote ? $template->tag["active"] : $template->tag["inactive"])) {
            if ($allow_vote) {
                $rater = sprintf('<div id="gdsr_thumb_%s_c_dw" class="gdt-size-%s gdthumb gddw"><a id="gdsrX%sXdwXcX%sX%sX%s" class="gdt-%s" rel="nofollow"></a></div>',
                    $comment_id, $unit_width, $comment_id, $template_id, $unit_width, $wait_msg == '' ? "N" : "Y", $style);
                if ($wait_msg != '') {
                    $rater_wait = GDSRRender::rating_wait(sprintf("gdsr_thumb_%s_c_loader_dw", $comment_id), $unit_width."px", ' loaddw', $wait_msg);
                    $rater.= sprintf($rater_wait, sprintf("width: %spx; height: %spx;", $unit_width, $unit_width));
                }
            } else {
                $rater = sprintf('<div id="gdsr_thumb_%s_c_dw" class="gdt-size-%s gdthumb gddw"><div class="gdt-%s"></div></div>',
                    $comment_id, $unit_width, $style);
            }
            $rater = apply_filters('gdsr_thumb_down', $rater, $template);
            $tpl_render = str_replace("%THUMB_DOWN%", $rater, $tpl_render);
        }

        if ($debug != '') $tpl_render = '<div style="display: none">'.$debug.'</div>'.$tpl_render;
        return $tpl_render;
    }

    function render_crb($template_id, $rpar = array()) {
        $rdef = array("cmm_id" => 0, "class" => "", "type" => "", "votes" => 0, "score" => 20,
            "style" => "oxygen", "unit_width" => 20, "unit_count" => 10, "already_voted" => false,
            "allow_vote" => true, "user_id" => 0, "typecls" => "", "tags_css" => array(),
            "header_text" => "", "debug" => "", "wait_msg" => "");
        $rpar = wp_parse_args($rpar, $rdef);
        $rpar = apply_filters('gdsr_t2parameters_crb', $rpar);
        extract($rpar, EXTR_SKIP);

        $template = GDSRRenderT2::get_template($template_id, "CRB");
        $tpl_render = $template->elm["normal"];
        $tpl_render = html_entity_decode($tpl_render);
        $tpl_render = apply_filters('gdsr_t2render_crb_normal', $tpl_render, $template, $rpar, "normal");

        foreach ($tags_css as $tag => $value) $tpl_render = str_replace('%'.$tag.'%', $value, $tpl_render);
        $tpl_render = str_replace("%CMM_HEADER_TEXT%", html_entity_decode($header_text), $tpl_render);

        $rating2 = $votes > 0 ? $score / $votes : 0;
        if ($rating2 > $unit_count) $rating2 = $unit_count;
        $rating = @number_format($rating2, 1);
        $css = "";
        if ($already_voted) $css.= " voted";
        if (!$allow_vote) $css.= " inactive";

        $rater_id = $typecls."_rater_".$cmm_id;
        $loader_id = $typecls."_loader_".$cmm_id;

        $tpl_render = str_replace('%CMM_RATING%', apply_filters('gdsr_t2_tag_value', $rating, "CRB", "%RATING%"), $tpl_render);
        $tpl_render = str_replace('%CMM_VOTES%', apply_filters('gdsr_t2_tag_value', $votes, "CRB", "%VOTES%"), $tpl_render);
        $tpl_render = str_replace('%CSS_AUTO%', apply_filters('gdsr_t2_tag_value', $css, "CRB", "%CSS_AUTO%"), $tpl_render);

        if (in_array("%CMM_RATING_STARS%", $template->tag["normal"])) {
            $rating2 = apply_filters('gdsr_t2rating_crb_stars_display', $rating2);
            $rating_width = $rating2 * $unit_width;
            $rater_length = $unit_width * $unit_count;
            $rating_stars = GDSRRender::rating_stars($style, $unit_width, $rater_id, $class, $rating_width, $allow_vote, $unit_count, $type, $cmm_id, $user_id, $loader_id, $rater_length, $typecls, $wait_msg, $template_id);
            $tpl_render = str_replace("%CMM_RATING_STARS%", $rating_stars, $tpl_render);
        }

        if (in_array("%CMM_RATING_TEXT%", $template->tag["normal"])) {
            $rating_text = GDSRRenderT2::render_crt($template->dep["CRT"], array("rating" => $rating, "unit_count" => $unit_count, "votes" => $votes));
            $voted = $css == "" ? '' : ' class="'.trim($css).'"';
            $rating_text = '<div id="gdr_text_'.$type.$cmm_id.'"'.$voted.'>'.$rating_text.'</div>';
            $tpl_render = str_replace("%CMM_RATING_TEXT%", $rating_text, $tpl_render);
        }

        if ($debug != '') $tpl_render = '<div style="display: none">'.$debug.'</div>'.$tpl_render;

        return $tpl_render;
    }

    function render_rsb($template_id, $rpar = array()) {
        $rdef = array("rating" => 0, "star_style" => "oxygen",
            "star_size" => 20, "star_max" => 10, "header_text" => "", "css" => "");
        $rpar = wp_parse_args($rpar, $rdef);
        $rpar = apply_filters('gdsr_t2parameters_rsb', $rpar);
        extract($rpar, EXTR_SKIP);

        $template = GDSRRenderT2::get_template($template_id, "RSB");
        $tpl_render = $template->elm["normal"];
        $tpl_render = html_entity_decode($tpl_render);
        $tpl_render = apply_filters('gdsr_t2render_rsb_normal', $tpl_render, $template, $rpar, "normal");

        $tpl_render = str_replace("%HEADER_TEXT%", html_entity_decode($header_text), $tpl_render);
        $tpl_render = str_replace("%CSS_BLOCK%", $css, $tpl_render);
        $tpl_render = str_replace("%MAX_RATING%", $star_max, $tpl_render);
        $tpl_render = str_replace("%RATING%", apply_filters('gdsr_t2_tag_value', $rating, "RSB", "%RATING%"), $tpl_render);

        $rating_stars = GDSRRender::render_static_stars($star_style, $star_size, $star_max, $rating);
        $tpl_render = str_replace("%RATING_STARS%", $rating_stars, $tpl_render);

        return $tpl_render;
    }

    function render_rcb($template_id, $rpar = array()) {
        $rdef = array("rating" => 0, "star_style" => "oxygen", "star_size" => 20, "star_max" => 10);
        $rpar = wp_parse_args($rpar, $rdef);
        $rpar = apply_filters('gdsr_t2parameters_rcb', $rpar);
        extract($rpar, EXTR_SKIP);

        $template = GDSRRenderT2::get_template($template_id, "RCB");
        $tpl_render = $template->elm["normal"];
        $tpl_render = html_entity_decode($tpl_render);
        $tpl_render = apply_filters('gdsr_t2render_rcb_normal', $tpl_render, $template, $rpar, "normal");

        $tpl_render = str_replace("%MAX_MM_RATING%", $star_max, $tpl_render);
        $tpl_render = str_replace("%CMM_RATING%", apply_filters('gdsr_t2_tag_value', $rating, "RCB", "%CMM_RATING%"), $tpl_render);

        $rating_stars = GDSRRender::render_static_stars($star_style, $star_size, $star_max, $rating);
        $tpl_render = str_replace("%CMM_RATING_STARS%", $rating_stars, $tpl_render);

        return $tpl_render;
    }

    function render_mcr($template_id, $rpar = array()) {
        $rdef = array('post_id' => 0, 'set' => 0, 'avg_rating' => 0, 'avg_style' => 'oxygen', 'avg_size' => 20);
        $rpar = wp_parse_args($rpar, $rdef);
        $rpar = apply_filters('gdsr_t2parameters_mcr', $rpar);
        extract($rpar, EXTR_SKIP);

        $template = GDSRRenderT2::get_template($template_id, 'MCR');
        $tpl_render = $template->elm['normal'];
        $tpl_render = html_entity_decode($tpl_render);
        $tpl_render = apply_filters('gdsr_t2render_mcr_normal', $tpl_render, $template, $rpar, 'normal');

        $rt = str_replace('%ID%', $post_id, $rt);
        $tpl_render = str_replace('%AVG_RATING%', apply_filters('gdsr_t2_tag_value', $avg_rating, 'MCR', '%AVG_RATING%'), $tpl_render);
        $tpl_render = str_replace('%MAX_RATING%', $set->stars, $tpl_render);

        if (in_array('%AVG_RATING_STARS%', $template->tag['normal'])) {
            $tpl_render = str_replace('%AVG_RATING_STARS%', GDSRRender::render_static_stars($avg_style, $avg_size, $set->stars, $avg_rating), $tpl_render);
        }

        return $tpl_render;
    }

    function render_rmb($template_id, $rpar = array()) {
        $rdef = array('votes' => array(), 'post_id' => 0, 'set' => 0, 'avg_rating' => 0, 'star_factor' => 1,
            'style' => 'oxygen', 'size' => 20, 'avg_style' => 'oxygen', 'avg_size' => 20);
        $rpar = wp_parse_args($rpar, $rdef);
        $rpar = apply_filters('gdsr_t2parameters_rmb', $rpar);
        extract($rpar, EXTR_SKIP);

        $template = GDSRRenderT2::get_template($template_id, 'RMB');
        $tpl_render = $template->elm['normal'];
        $tpl_render = html_entity_decode($tpl_render);
        $tpl_render = apply_filters('gdsr_t2render_rmb_normal', $tpl_render, $template, $rpar, 'normal');

        $rater = '<div id="gdsr_mureview_block_'.$post_id.'_'.$set->multi_id.'" class="ratingmulti gdsr-review-block">';

        $i = 0;
        $weighted = 0;
        $total_votes = 0;
        $weight_norm = array_sum($set->weight);
        $rating_stars = "";
        $table_row_class = $template->dep['MRS']->dep['ETR'];

        foreach ($set->object as $el) {
            $single_row = html_entity_decode($template->dep['MRS']->elm['item']);
            $single_row = str_replace('%ELEMENT_NAME%', $el, $single_row);
            $single_row = str_replace('%ELEMENT_ID%', $i, $single_row);
            $single_row = str_replace('%ELEMENT_VALUE%', $votes[$i]['rating'], $single_row);
            $single_row = str_replace('%ELEMENT_STARS%', GDSRRender::render_static_stars($style, $size, $set->stars * $star_factor, $votes[$i]['rating'] * $star_factor), $single_row);
            $single_row = str_replace('%TABLE_ROW_CLASS%', is_odd($i) ? $table_row_class->elm['odd'] : $table_row_class->elm['even'], $single_row);
            $rating_stars.= $single_row;

            $weighted += ($votes[$i]['rating'] * $set->weight[$i]) / $weight_norm;
            $total_votes += $votes[$i]['votes'];
            $i++;
        }

        $tpl_render = str_replace("%MUR_RATING_STARS%", $rating_stars, $tpl_render);
        $tpl_render = str_replace("%AVG_RATING%", apply_filters('gdsr_t2_tag_value', $avg_rating, 'RMB', '%AVG_RATING%'), $tpl_render);
        $tpl_render = str_replace("%MAX_RATING%", $set->stars, $tpl_render);

        if (in_array('%AVG_RATING_STARS%', $template->tag['normal'])) {
            $tpl_render = str_replace('%AVG_RATING_STARS%', GDSRRender::render_static_stars($avg_style, $avg_size, $set->stars, $avg_rating), $tpl_render);
        }

        $rater.= $tpl_render."</div>";
        return $rater;
    }

    function render_car($template_id, $rpar = array()) {
        $rdef = array('votes' => 0, 'rating' => 0, 'comments' => 0, 'star_style' => 'oxygen', 'star_size' => 20, 'star_max' => 10);
        $rpar = wp_parse_args($rpar, $rdef);
        $rpar = apply_filters('gdsr_t2parameters_car', $rpar);
        extract($rpar, EXTR_SKIP);

        $template = GDSRRenderT2::get_template($template_id, 'CAR');
        $tpl_render = $template->elm['normal'];
        $tpl_render = html_entity_decode($tpl_render);
        $tpl_render = apply_filters('gdsr_t2render_car_normal', $tpl_render, $template, $rpar, 'normal');

        $tpl_render = str_replace('%CMM_COUNT%', apply_filters('gdsr_t2_tag_value', $$comments, 'CAR', '%CMM_COUNT%'), $tpl_render);
        $tpl_render = str_replace('%CMM_VOTES%', apply_filters('gdsr_t2_tag_value', $votes, 'CAR', '%CMM_VOTES%'), $tpl_render);
        $tpl_render = str_replace('%CMM_RATING%', apply_filters('gdsr_t2_tag_value', $rating, 'CAR', '%CMM_RATING%'), $tpl_render);
        $tpl_render = str_replace('%MAX_CMM_RATING%', $star_max, $tpl_render);

        $word_votes = $template->dep['EWV'];
        $tense = $votes == 1 ? $word_votes->elm['singular'] : $word_votes->elm['plural'];
        $tpl_render = str_replace('%WORD_VOTES%', __($tense), $tpl_render);

        $rating_stars = GDSRRender::render_static_stars($star_style, $star_size, $star_max, $rating);
        $tpl_render = str_replace('%CMM_STARS%', $rating_stars, $tpl_render);

        return $tpl_render;
    }

    // extras rendering data
    function render_tat($template, $rpar = array()) {
        $rdef = array('percent' => 0, 'votes' => 0, 'score' => 0, 'votes_plus' => 0, 'votes_minus' => 0, 'id' => 0,
            'time_restirctions' => 'N', 'time_remaining' => 0, 'time_date' => '', 'score_number' => 0);
        $rpar = wp_parse_args($rpar, $rdef);
        $rpar = apply_filters('gdsr_t2parameters_tat', $rpar);
        extract($rpar, EXTR_SKIP);

        if (($time_restirctions == 'D' || $time_restirctions == 'T') && $time_remaining > 0) {
            $time_parts = gdsrFrontHelp::remaining_time_parts($time_remaining);
            $time_total = gdsrFrontHelp::remaining_time_total($time_remaining);
            $tpl = $template->elm['time_active'];

            $rt = html_entity_decode($tpl);
            $rt = str_replace('%TR_DATE%', $time_date, $rt);
            $rt = str_replace('%TR_YEARS%', $time_parts['year'], $rt);
            $rt = str_replace('%TR_MONTHS%', $time_parts['month'], $rt);
            $rt = str_replace('%TR_DAYS%', $time_parts['day'], $rt);
            $rt = str_replace('%TR_HOURS%', $time_parts['hour'], $rt);
            $rt = str_replace('%TR_MINUTES%', $time_parts['minute'], $rt);
            $rt = str_replace('%TR_SECONDS%', $time_parts['second'], $rt);
            $rt = str_replace('%TOT_DAYS%', $time_total['day'], $rt);
            $rt = str_replace('%TOT_HOURS%', $time_total['hour'], $rt);
            $rt = str_replace('%TOT_MINUTES%', $time_total['minute'], $rt);
        } else {
            if ($time_restirctions == 'D' || $time_restirctions == 'T')
                $tpl = $template->elm['time_closed'];
            else
                $tpl = $template->elm['normal'];
            $rt = html_entity_decode($tpl);
        }

        $css = intval($score_number) == 0 ? 'zero' : (intval($score_number) > 0 ? 'positive' : 'negative');

        $rt = str_replace('%RATING%', apply_filters('gdsr_t2_tag_value', $score, 'TAT', '%RATING%'), $rt);
        $rt = str_replace('%PERCENTAGE%', apply_filters('gdsr_t2_tag_value', $percent, 'TAT', '%PERCENTAGE%'), $rt);
        $rt = str_replace('%VOTES%', apply_filters('gdsr_t2_tag_value', $votes, 'TAT', '%VOTES%'), $rt);
        $rt = str_replace('%VOTES_UP%', apply_filters('gdsr_t2_tag_value', $votes_plus, 'TAT', '%VOTES_UP%'), $rt);
        $rt = str_replace('%VOTES_DOWN%', apply_filters('gdsr_t2_tag_value', $votes_minus, 'TAT', '%VOTES_DOWN%'), $rt);
        $rt = str_replace('%CSS_AUTO%', apply_filters('gdsr_t2_tag_value', $css, 'TAT', '%CSS_AUTO%'), $rt);
        $rt = str_replace('%ID%', $id, $rt);

        $word_votes = $template->dep['EWV'];
        $tense = $votes == 1 ? $word_votes->elm['singular'] : $word_votes->elm['plural'];
        $rt = str_replace('%WORD_VOTES%', __($tense), $rt);

        return $rt;
    }

    function render_tct($template, $rpar = array()) {
        $rdef = array('percent' => 0, 'votes' => 0, 'score' => 0, 'votes_plus' => 0, 'votes_minus' => 0, 'id' => 0, 'vote_value' => 0, 'score_number' => 0);
        $rpar = wp_parse_args($rpar, $rdef);
        $rpar = apply_filters('gdsr_t2parameters_tct', $rpar);
        extract($rpar, EXTR_SKIP);

        $tpl = $vote_value != 0 ? $template->elm['vote_saved'] : $template->elm['normal'];
        $rt = html_entity_decode($tpl);
        $rt = apply_filters('gdsr_t2render_tct_'.($vote_value != 0 ? 'vote_saved' : 'normal'), $rt, $template, $rpar, $vote_value != 0 ? 'vote_saved' : 'normal');

        $css = intval($score_number) == 0 ? 'zero' : (intval($score_number) > 0 ? 'positive' : 'negative');

        $rt = str_replace('%RATING%', apply_filters('gdsr_t2_tag_value', $score, 'TCT', '%RATING%'), $rt);
        $rt = str_replace('%PERCENTAGE%', apply_filters('gdsr_t2_tag_value', $percent, 'TCT', '%PERCENTAGE%'), $rt);
        $rt = str_replace('%VOTES%', apply_filters('gdsr_t2_tag_value', $votes, 'TCT', '%VOTES%'), $rt);
        $rt = str_replace('%VOTES_UP%', apply_filters('gdsr_t2_tag_value', $votes_plus, 'TCT', '%VOTES_UP%'), $rt);
        $rt = str_replace('%VOTES_DOWN%', apply_filters('gdsr_t2_tag_value', $votes_minus, 'TCT', '%VOTES_DOWN%'), $rt);
        $rt = str_replace('%CSS_AUTO%', apply_filters('gdsr_t2_tag_value', $css, 'TCT', '%CSS_AUTO%'), $rt);
        $rt = str_replace('%ID%', $id, $rt);
        $rt = str_replace('%VOTE_VALUE%', $vote_value, $rt);

        $word_votes = $template->dep['EWV'];
        $tense = $votes == 1 ? $word_votes->elm['singular'] : $word_votes->elm['plural'];
        $rt = str_replace('%WORD_VOTES%', __($tense), $rt);

        return $rt;
    }

    function render_mrt($template, $rpar = array()) {
        $rdef = array('rating' => 0, 'unit_count' => 0, 'votes' => 0, 'id' => 0,
            'time_restirctions' => 'N', 'time_remaining' => 0, 'time_date' => '');
        $rpar = wp_parse_args($rpar, $rdef);
        $rpar = apply_filters('gdsr_t2parameters_mrt', $rpar);
        extract($rpar, EXTR_SKIP);

        return GDSRRenderT2::render_srt($template, array('rating' => $rating, 'unit_count' => $unit_count, 'votes' => $votes, 'id' => $id, 'time_restirctions' => $time_restirctions, 'time_remaining' => $time_remaining, 'time_date' => $time_date));
    }

    function render_crt($template, $rpar = array()) {
        $rdef = array('rating' => 0, 'unit_count' => 10, 'votes' => 0, 'vote_value' => -1);
        $rpar = wp_parse_args($rpar, $rdef);
        $rpar = apply_filters('gdsr_t2parameters_crt', $rpar);
        extract($rpar, EXTR_SKIP);

        $tpl = $vote_value > -1 ? $template->elm['vote_saved'] : $template->elm['normal'];
        $rt = html_entity_decode($tpl);
        $rt = apply_filters('gdsr_t2render_crt_'.($vote_value > -1 ? 'vote_saved' : 'normal'), $rt, $template, $rpar, $vote_value > -1 ? 'vote_saved' : 'normal');

        $rt = str_replace('%CMM_RATING%', apply_filters('gdsr_t2_tag_value', $rating, 'CRT', '%CMM_RATING%'), $rt);
        $rt = str_replace('%MAX_CMM_RATING%', $unit_count, $rt);
        $rt = str_replace('%CMM_VOTES%', apply_filters('gdsr_t2_tag_value', $votes, 'CRT', '%CMM_VOTES%'), $rt);
        if ($vote_value > -1) $rt = str_replace('%CMM_VOTE_VALUE%', $vote_value, $rt);

        $word_votes = $template->dep['EWV'];
        $tense = $votes == 1 ? $word_votes->elm['singular'] : $word_votes->elm['plural'];
        $rt = str_replace('%WORD_VOTES%', __($tense), $rt);

        return $rt;
    }

    function render_srt($template, $rpar = array()) {
        $rdef = array('rating' => 0, 'unit_count' => 0, 'votes' => 0, 'id' => 0,
            'time_restirctions' => 'N', 'time_remaining' => 0, 'time_date' => '');
        $rpar = wp_parse_args($rpar, $rdef);
        $rpar = apply_filters('gdsr_t2parameters_srt', $rpar);
        extract($rpar, EXTR_SKIP);

        if (($time_restirctions == 'D' || $time_restirctions == 'T') && $time_remaining > 0) {
            $time_parts = gdsrFrontHelp::remaining_time_parts($time_remaining);
            $time_total = gdsrFrontHelp::remaining_time_total($time_remaining);
            $tpl = $template->elm['time_active'];

            $rt = html_entity_decode($tpl);
            $rt = str_replace('%TR_DATE%', $time_date, $rt);
            $rt = str_replace('%TR_YEARS%', $time_parts['year'], $rt);
            $rt = str_replace('%TR_MONTHS%', $time_parts['month'], $rt);
            $rt = str_replace('%TR_DAYS%', $time_parts['day'], $rt);
            $rt = str_replace('%TR_HOURS%', $time_parts['hour'], $rt);
            $rt = str_replace('%TR_MINUTES%', $time_parts['minute'], $rt);
            $rt = str_replace('%TR_SECONDS%', $time_parts['second'], $rt);
            $rt = str_replace('%TOT_DAYS%', $time_total['day'], $rt);
            $rt = str_replace('%TOT_HOURS%', $time_total['hour'], $rt);
            $rt = str_replace('%TOT_MINUTES%', $time_total['minute'], $rt);
        } else {
            if ($time_restirctions == 'D' || $time_restirctions == 'T') {
                $tpl = $template->elm['time_closed'];
            } else{ 
                $tpl = $template->elm['normal'];
            }

            $rt = html_entity_decode($tpl);
        }

        $rt = str_replace('%RATING%', apply_filters('gdsr_t2_tag_value', $rating, 'SRT', '%RATING%'), $rt);
        $rt = str_replace('%VOTES%', apply_filters('gdsr_t2_tag_value', $votes, 'SRT', '%VOTES%'), $rt);
        $rt = str_replace('%ID%', $id, $rt);
        $rt = str_replace('%MAX_RATING%', $unit_count, $rt);

        $word_votes = $template->dep['EWV'];
        $tense = $votes == 1 ? $word_votes->elm['singular'] : $word_votes->elm['plural'];
        $rt = str_replace('%WORD_VOTES%', __($tense), $rt);

        return $rt;
    }

    function render_tat_voted($template, $rpar = array()) {
        $rdef = array('votes' => 0, 'score' => 0, 'votes_plus' => 0, 'votes_minus' => 0, 'id' => 0, 'vote' => 0);
        $rpar = wp_parse_args($rpar, $rdef);
        $rpar = apply_filters('gdsr_t2parameters_tat_voted', $rpar);
        extract($rpar, EXTR_SKIP);

        $tpl = $template->elm['vote_saved'];
        $rt = html_entity_decode($tpl);
        $rt = apply_filters('gdsr_t2render_tat_vote_saved', $rt, $template, $rpar, 'vote_saved');

        $percent = $votes_plus + $votes_minus == 0 ? 0 : ($votes_plus * 100) / ($votes_plus + $votes_minus);
        $percent = number_format($percent, 0);
        if ($percent == 0) $percent = gdsr_zero_percentage();

        $score = $score > 0 ? '+'.$score : $score;
        $rt = str_replace('%RATING%', apply_filters('gdsr_t2_tag_value', $score, 'TAT', '%RATING%'), $rt);
        $rt = str_replace('%PERCENTAGE%', apply_filters('gdsr_t2_tag_value', $percent, 'TAT', '%PERCENTAGE%'), $rt);
        $rt = str_replace('%VOTES%', apply_filters('gdsr_t2_tag_value', $votes, 'TAT', '%VOTES%'), $rt);
        $rt = str_replace('%VOTES_UP%', apply_filters('gdsr_t2_tag_value', $votes_plus, 'TAT', '%VOTES_UP%'), $rt);
        $rt = str_replace('%VOTES_DOWN%', apply_filters('gdsr_t2_tag_value', $votes_minus, 'TAT', '%VOTES_DOWN%'), $rt);
        $rt = str_replace('%ID%', $id, $rt);
        $rt = str_replace('%VOTE_VALUE%', $vote, $rt);

        $word_votes = $template->dep['EWV'];
        $tense = $votes == 1 ? $word_votes->elm['singular'] : $word_votes->elm['plural'];
        $rt = str_replace('%WORD_VOTES%', __($tense), $rt);

        return $rt;
    }

    function render_srt_voted($template, $rpar = array()) {
        $rdef = array('rating' => 0, 'unit_count' => 0, 'votes' => 0, 'id' => 0, 'vote' => 0);
        $rpar = wp_parse_args($rpar, $rdef);
        $rpar = apply_filters('gdsr_t2parameters_srt_voted', $rpar);
        extract($rpar, EXTR_SKIP);

        $tpl = $template->elm['vote_saved'];
        $rt = html_entity_decode($tpl);
        $rt = apply_filters('gdsr_t2render_srt_vote_saved', $rt, $template, $rpar, 'vote_saved');

        $rt = str_replace('%RATING%', apply_filters('gdsr_t2_tag_value', $rating, 'SRT', '%RATING%'), $rt);
        $rt = str_replace('%VOTES%', apply_filters('gdsr_t2_tag_value', $votes, 'SRT', '%VOTES%'), $rt);
        $rt = str_replace('%MAX_RATING%', $unit_count, $rt);
        $rt = str_replace('%ID%', $id, $rt);
        $rt = str_replace('%VOTE_VALUE%', $vote, $rt);

        $word_votes = $template->dep['EWV'];
        $tense = $votes == 1 ? $word_votes->elm['singular'] : $word_votes->elm['plural'];
        $rt = str_replace('%WORD_VOTES%', __($tense), $rt);

        return $rt;
    }

    // rendering results
    function render_wsr($widget, $section = 'WSR') {
        global $gdsr;

        $widget['select'] = gdsr_widget_convert_select($widget);
        $widget = apply_filters('gdsr_widget_parameters_wsr', $widget);

        $template = GDSRRenderT2::get_template($widget['template_id'], $section);
        $tpl_render = html_entity_decode($template->elm['header']);
        $tpl_render = apply_filters('gdsr_t2render_'.strtolower($section).'_header', $tpl_render, $template, $widget, 'header');

        $rt = html_entity_decode($template->elm['item']);
        $all_rows = GDSRRenderT2::prepare_wsr($widget, $rt);
        $is_thumb = $widget['source'] == 'thumbs';
        $total_rows = count($all_rows);

        if ($total_rows > 0) {
            $rank_id = 1;
            foreach ($all_rows as $row) {
                $rt = html_entity_decode($template->elm['item']);
                $rt = apply_filters('gdsr_t2render_'.strtolower($section).'_item', $rt, $template, $widget, $row, 'item');

                $title = $row->title;
                if (strlen($title) == 0) $title = __('(no title)', 'gd-star-rating');
                if ($widget['source'] == 'thumbs' && $row->rating > 0) $row->rating = '+'.$row->rating;

                $auto_css = ' t2-row-'.$rank_id;
                $row_id = isset($row->post_id) ? $row->post_id : (isset($row->term_id) ? $row->term_id : $row->id);
                if ($rank_id == 1) $auto_css.= ' t2-first';
                if ($rank_id == $total_rows) $auto_css.= ' t2-last';

                $rt = str_replace('%AUTO_ROW_CLASS%', trim($auto_css), $rt);
                $rt = str_replace('%THUMB%', $row->rating_thumb, $rt);
                $rt = str_replace('%RATING%', $row->rating, $rt);
                $rt = str_replace('%MAX_RATING%', $gdsr->o['stars'], $rt);
                $rt = str_replace('%EXCERPT%', $row->excerpt, $rt);
                $rt = str_replace('%CONTENT%', $row->content, $rt);
                $rt = str_replace('%VOTES%', $row->voters, $rt);
                $rt = str_replace('%REVIEW%', $row->review, $rt);
                $rt = str_replace('%MAX_REVIEW%', $gdsr->o['review_stars'], $rt);
                $rt = str_replace('%TITLE%', __($title), $rt);
                $rt = str_replace('%SLUG%', $row->slug, $rt);
                $rt = str_replace('%TAXONOMY%', $widget['taxonomy'], $rt);
                $rt = str_replace('%PERMALINK%', $row->permalink, $rt);
                $rt = str_replace('%RANK_ID%', $rank_id, $rt);
                $rt = str_replace('%ID%', $row_id, $rt);
                $rt = str_replace('%COUNT%', $row->counter, $rt);
                $rt = str_replace('%VOTES_UP%', $row->votes_plus, $rt);
                $rt = str_replace('%VOTES_DOWN%', $row->votes_minus, $rt);
                $rt = str_replace('%BAYES_RATING%', $row->bayesian, $rt);
                $rt = str_replace('%BAYES_STARS%', $row->bayesian_stars, $rt);
                $rt = str_replace('%STARS%', $row->rating_stars, $rt);
                $rt = str_replace('%REVIEW_STARS%', $row->review_stars, $rt);
                $rt = str_replace('%RATE_TREND%', $row->item_trend_rating, $rt);
                $rt = str_replace('%VOTE_TREND%', $row->item_trend_voting, $rt);
                $rt = str_replace('%IMAGE%', $row->image, $rt);
                $rt = str_replace('%AUTHOR_NAME%', $row->author_name, $rt);
                $rt = str_replace('%AUTHOR_LINK%', $row->author_url, $rt);
                $rank_id++;

                $word_votes = $template->dep['EWV'];
                $tense = $row->voters == 1 ? $word_votes->elm['singular'] : $word_votes->elm['plural'];
                $rt = str_replace('%WORD_VOTES%', __($tense), $rt);

                $table_row = $template->dep['ETR'];
                $row_css = $row->table_row_class == 'odd' ? $table_row->elm['odd'] : $table_row->elm['even'];
                $rt = str_replace('%TABLE_ROW_CLASS%', $row_css, $rt);

                $tpl_render.= $rt;
            }
        } else {
            $tpl_render.= apply_filters('gdsr_no_results', __("No results.", "gd-star-rating"));
        }

        $rt = html_entity_decode($template->elm["footer"]);
        $rt = apply_filters('gdsr_t2render_'.strtolower($section).'_footer', $rt, $template, $widget, "footer");

        $tpl_render.= $rt;
        return $tpl_render;
    }

    function render_wcr($widget) {
        global $gdsr;

        $widget = apply_filters('gdsr_widget_parameters_wcr', $widget);

        $template = GDSRRenderT2::get_template($widget["template_id"], "WCR");
        $tpl_render = html_entity_decode($template->elm["header"]);
        $tpl_render = apply_filters('gdsr_t2render_wcr_header', $tpl_render, $template, $widget, "header");

        $rt = html_entity_decode($template->elm["item"]);
        $all_rows = GDSRRenderT2::prepare_wcr($widget, $rt);
        $total_rows = count($all_rows);

        if ($total_rows > 0) {
            $rank_id = 1;
            foreach ($all_rows as $row) {
                $rt = html_entity_decode($template->elm["item"]);
                $rt = apply_filters('gdsr_t2render_wcr_item', $rt, $template, $widget, $row, "item");

                if (isset($widget["source"]) && $widget["source"] == "thumbs" && $row->rating > 0) $row->rating = "+".$row->rating;

                $auto_css = " t2-row-".$rank_id;
                if ($rank_id == 1) $auto_css.= " t2-first";
                if ($rank_id == $total_rows) $auto_css.= " t2-last";

                $rt = str_replace('%AUTO_ROW_CLASS%', $auto_css, $rt);
                $rt = str_replace('%CMM_RATING%', $row->rating, $rt);
                $rt = str_replace('%MAX_RATING%', $gdsr->o["cmm_stars"], $rt);
                $rt = str_replace('%CMM_VOTES%', $row->voters, $rt);
                $rt = str_replace('%COMMENT%', $row->comment_content, $rt);
                $rt = str_replace('%PERMALINK%', $row->permalink, $rt);
                $rt = str_replace('%AUTHOR_NAME%', $row->comment_author, $rt);
                $rt = str_replace('%AUTHOR_AVATAR%', $row->comment_author_email, $rt);
                $rt = str_replace('%AUTHOR_LINK%', $row->comment_author_url, $rt);
                $rt = str_replace('%ID%', $row->comment_id, $rt);
                $rt = str_replace('%RANK_ID%', $rank_id, $rt);
                $rt = str_replace('%POST_ID%', $gdsr->widget_post_id, $rt);
                $rt = str_replace('%CMM_STARS%', $row->rating_stars, $rt);
                $rank_id++;

                $word_votes = $template->dep['EWV'];
                $tense = $row->voters == 1 ? $word_votes->elm['singular'] : $word_votes->elm['plural'];
                $rt = str_replace('%WORD_VOTES%', __($tense), $rt);

                $table_row = $template->dep['ETR'];
                $row_css = $row->table_row_class == 'odd' ? $table_row->elm['odd'] : $table_row->elm['even'];
                $rt = str_replace('%TABLE_ROW_CLASS%', $row_css, $rt);

                $tpl_render.= $rt;
            }
        }

        $rt = html_entity_decode($template->elm['footer']);
        $rt = apply_filters('gdsr_t2render_wcr_footer', $rt, $template, $widget, 'footer');

        $tpl_render.= $rt;
        return $tpl_render;
    }

    function render_wbr($widget) {
        $widget = apply_filters('gdsr_widget_parameters_wbr', $widget);

        $template = GDSRRenderT2::get_template($widget['template_id'], 'WBR');
        $tpl_render = $template->elm['normal'];
        $tpl_render = html_entity_decode($tpl_render);
        $tpl_render = apply_filters('gdsr_t2render_wbr', $tpl_render, $template, $widget, 'normal');
        $data = GDSRRenderT2::prepare_wbr($widget);
        if ($widget['source'] == 'thumbs' && $data->rating > 0) $data->rating = '+'.$data->rating;
        if ($widget['source'] == 'thumbs') $data->voters = $data->votes;
        if ($data->percentage == 0) $data->percentage = gdsr_zero_percentage();

        $rt = str_replace('%PERCENTAGE%', $data->percentage, $tpl_render);
        $rt = str_replace('%RATING%', $data->rating, $rt);
        $rt = str_replace('%MAX_RATING%', $data->max_rating, $rt);
        $rt = str_replace('%VOTES%', $data->voters, $rt);
        $rt = str_replace('%COUNT%', $data->count, $rt);
        $rt = str_replace('%BAYES_RATING%', $data->bayes_rating, $rt);

        $word_votes = $template->dep['EWV'];
        $tense = $data->voters == 1 ? $word_votes->elm['singular'] : $word_votes->elm['plural'];
        $rt = str_replace('%WORD_VOTES%', __($tense), $rt);

        return $rt;
    }

    function render_srr($widget) {
        $widget = apply_filters('gdsr_widget_parameters_srr', $widget);

        return GDSRRenderT2::render_wsr($widget, 'SRR');
    }
}

?>