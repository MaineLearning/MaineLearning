<?php

class gdsrFront {
    var $g;
    var $gsr;

    var $loader_article_thumb = "";
    var $loader_comment_thumb = "";
    var $loader_article = "";
    var $loader_comment = "";
    var $loader_multis = "";

    function gdsrFront($gdsr_main) {
        $this->g = $gdsr_main;
    }

    function taxonomy_multi_ratings($settings) {
        global $gdsr;
        $results = gdsrBlgDB::taxonomy_multi_ratings($settings["taxonomy"], $settings["terms"], $settings["multi_id"], $settings["term_property"]);
        $set = wp_gdget_multi_set($settings["multi_id"]);
        $new_results = $final = $ids = array();
        $style = $gdsr->is_ie6 ? $settings["style_ie6"] : $settings["style"];
        $size = $settings["size"];
        $avg_style = $gdsr->is_ie6 ? $settings["average_style_ie6"] : $settings["average_style"];
        $avg_size = $settings["average_size"];

        foreach ($results as $row) {
            $ids[] = $row->mdid;
            $row->votes = $row->user_votes + $row->visitor_votes;
            $row->voters = $row->user_voters + $row->visitor_voters;
            $row->rating = $row->voters == 0 ? 0 : @number_format($row->votes / $row->voters, 1);
            $row->review = @number_format($row->review, 1);
            $row->bayesian = $gdsr->bayesian_estimate($row->voters, $row->rating, $set->stars);
            $row->rating_stars = GDSRRender::render_static_stars($style, $size, $set->stars, $row->rating);
            $row->bayesian_stars = GDSRRender::render_static_stars($style, $size, $set->stars, $row->bayesian);
            $row->review_stars = GDSRRender::render_static_stars($style, $size, $set->stars, $row->review);
            $new_results[] = $row;
        }

        $v_review = $v_rating = array();
        $data = gdsrBlgDB::taxonomy_multi_ratings_data($settings["taxonomy"], $settings["terms"], $settings["multi_id"], $settings["term_property"]);
        foreach ($data as $row) {
            if ($row->source == "dta") {
                $single_vote = array();
                $single_vote["votes"] = $row->user_voters + $row->visitor_voters;
                $single_vote["score"] = $row->user_votes + $row->visitor_votes;
                $single_vote["rating"] = $single_vote["votes"] > 0 ? $single_vote["score"] / $single_vote["votes"] : 0;
                $single_vote["rating"] = @number_format($single_vote["rating"], 1);
                $v_rating[$row->mdid][] = $single_vote;
            } else if ($row->source == "rvw") {
                $single_vote["votes"] = $row->user_voters;
                $single_vote["score"] = $row->user_votes;
                $single_vote["rating"] = $single_vote["votes"] > 0 ? $single_vote["score"] / $single_vote["votes"] : 0;
                $single_vote["rating"] = @number_format($single_vote["rating"], 1);
                $v_review[$row->mdid][] = $single_vote;
            }
        }

        foreach ($new_results as $row) {
            $row->rating_block = GDSRRenderT2::render_mrb(
                $settings["tpl_rating"], array("style" => $style, "allow_vote" => false, "votes" => $v_rating[$row->mdid],
                    "post_id" => $row->term_id, "set" => $set, "height" => $size, "header_text" => "",
                    "tags_css" => array("MUR_CSS_BLOCK" => "", "MUR_CSS_BUTTON" => ""), "avg_style" => $avg_style, "avg_size" => $avg_size,
                    "star_factor" => $settings["star_factor"]));
            $row->review_block = GDSRRenderT2::render_rmb(
                $settings["tpl_review"], array("votes" => $v_review[$row->mdid], "post_id" => $row->term_id, "set" => $set,
                    "avg_rating" => $row->review, "style" => $style, "size" => $size, "avg_style" => $avg_style,
                    "avg_size" => $avg_size));

        }

        if (count($new_results) == 1) return $new_results[0];
        else return $new_results;
    }

    function get_taxonomy_multi_ratings($taxonomy = "category", $term = "", $multi_id = 0, $size = 20, $style = "oxygen") {
        global $gdsr;
        $results = gdsrBlgDB::taxonomy_multi_ratings($taxonomy, array($term), $multi_id);
        $set = wp_gdget_multi_set($multi_id);
        $new_results = array();

        foreach ($results as $row) {
            $row->votes = $row->user_votes + $row->visitor_votes;
            $row->voters = $row->user_voters + $row->visitor_voters;
            $row->rating = $row->voters == 0 ? 0 : @number_format($row->votes / $row->voters, 1);
            $row->review = @number_format($row->review, 1);
            $row->bayesian = $gdsr->bayesian_estimate($row->voters, $row->rating, $set->stars);
            $row->rating_stars = GDSRRender::render_static_stars($style, $size, $set->stars, $row->rating);
            $row->bayesian_stars = GDSRRender::render_static_stars($style, $size, $set->stars, $row->bayesian);
            $row->review_stars = GDSRRender::render_static_stars($style, $size, $set->stars, $row->review);
            $new_results[] = $row;
        }

        if (count($new_results) == 1) return $new_results[0];
        else return $new_results;
    }

    function init_google_rich_snippet() {
        $active = $this->g->o["google_rich_snippets_active"] == 1;
        if ($active && !is_admin() && (is_single() || is_page()) && !is_feed()) {
            global $post;
            $this->gsr = $this->render_google_rich_snippet($post);
        }
    }

    function insert_google_rich_snippet() {
        echo $this->gsr;
    }

    function render_google_rich_snippet($post, $settings = array()) {
        $hidden = false;

        $datasource = isset($settings["source"]) ? $settings["source"] : $this->g->o["google_rich_snippets_datasource"];
        if (isset($settings["format"]) && is_object($this->g->rSnippets)) $this->g->rSnippets->snippet_type = $settings["format"];

        switch ($datasource) {
            case "standard_rating":
                return $this->render_gsr_standard_rating($post, $hidden);
                break;
            case "standard_review":
                return $this->render_gsr_standard_review($post, $hidden);
                break;
            case "multis_rating":
                return $this->render_gsr_multis_rating($post, $hidden);
                break;
            case "multis_review":
                return $this->render_gsr_multis_review($post, $hidden);
                break;
            case "thumbs":
                return $this->render_gsr_thumbs($post, $hidden);
                break;
        }
    }

    function render_gsr_thumbs($post, $hidden = true) {
        $post_data = wp_gdget_post($post->ID);
        $votes = $post_data->user_recc_plus + $post_data->user_recc_minus + $post_data->visitor_recc_plus + $post_data->visitor_recc_minus;
        if (!is_object($this->g->rSnippets) || $votes == 0) return "";
        $rating = $post_data->user_recc_plus - $post_data->user_recc_minus + $post_data->visitor_recc_plus - $post_data->visitor_recc_minus;
        $rating = number_format(100 * ($rating / $votes), 0);
        return $this->g->rSnippets->snippet_stars_percentage(array(
            "title" => $post->post_title,
            "rating" => $rating,
            "votes" => $votes,
            "hidden" => $hidden
        ));
    }

    function render_gsr_multis_rating($post, $hidden = true) {
        $data = gdsrBlgDB::get_rss_multi_data($post->ID);
        $votes = $data->total_votes_visitors + $data->total_votes_users;
        if (!is_object($this->g->rSnippets) || $votes == 0) return "";
        $sum = $data->average_rating_users * $data->total_votes_users + $data->average_rating_visitors * $data->total_votes_visitors;
        $rating = number_format($sum / $votes, 1);
        $set = wp_gdget_multi_set($data->multi_id);
        return $this->g->rSnippets->snippet_stars_rating(array(
            "title" => $post->post_title,
            "rating" => $rating,
            "max_rating" => $set->stars,
            "votes" => $votes,
            "hidden" => $hidden
        ));
    }

    function render_gsr_multis_review($post, $hidden = true) {
        $data = gdsrBlgDB::get_rss_multi_data_review($post->ID);
        $review = is_object($data) ? $data->average_review : 0;
        if (!is_object($this->g->rSnippets) || $review <= 0) return "";
        $set = wp_gdget_multi_set($data->multi_id);
        $author = get_userdata($post->post_author);
        return $this->g->rSnippets->snippet_stars_review(array(
            "title" => $post->post_title,
            "rating" => $review,
            "max_rating" => $set->stars,
            "review_date" => mysql2date("c", $post->post_date),
            "reviewer" => $author->display_name,
            "hidden" => $hidden
        ));
    }

    function render_gsr_standard_rating($post, $hidden = true) {
        $post_data = wp_gdget_post($post->ID);
        if (is_object($post_data)) {
            $voters = $post_data->visitor_voters + $post_data->user_voters;
            if (!is_object($this->g->rSnippets) || $voters == 0) return "";
            $votes = $post_data->visitor_votes + $post_data->user_votes;
            $rating = number_format($votes / $voters, 1);
            return $this->g->rSnippets->snippet_stars_rating(array(
                "title" => $post->post_title,
                "rating" => $rating,
                "max_rating" => $this->g->o["stars"],
                "votes" => $voters,
                "hidden" => $hidden
            ));
        }
    }

    function render_gsr_standard_review($post, $hidden = true) {
        $post_data = wp_gdget_post($post->ID);
        $review = is_object($post_data) ? $post_data->review : 0;
        if (!is_object($this->g->rSnippets) || $review <= 0) return "";
        $author = get_userdata($post->post_author);
        return $this->g->rSnippets->snippet_stars_review(array(
            "title" => $post->post_title,
            "rating" => $review,
            "max_rating" => $this->g->o["review_stars"],
            "review_date" => mysql2date("c", $post->post_date),
            "reviewer" => $author->display_name,
            "hidden" => $hidden
        ));
    }

    function render_article_rss() {
        global $post;
        $rd_post_id = intval($post->ID);
        $post_data = GDSRDatabase::get_post_data($rd_post_id);
        $template_id = $this->g->o["default_ssb_template"];
        $votes = $score = 0;
        $stars = 10;

        if ($this->g->o["rss_datasource"] == "thumbs") {
            if ($rules_articles == "A" || $rules_articles == "N") {
                $votes = $post_data->user_recc_plus + $post_data->user_recc_minus + $post_data->visitor_recc_plus + $post_data->visitor_recc_minus;
                $score = $post_data->user_recc_plus - $post_data->user_recc_minus + $post_data->visitor_recc_plus - $post_data->visitor_recc_minus;
            } else if ($rules_articles == "V") {
                $votes = $post_data->visitor_recc_plus + $post_data->visitor_recc_minus;
                $score = $post_data->visitor_recc_plus - $post_data->visitor_recc_minus;
            } else {
                $votes = $post_data->user_recc_plus + $post_data->user_recc_minus;
                $score = $post_data->user_recc_plus - $post_data->user_recc_minus;
            }
        } else if ($this->g->o["rss_datasource"] == "standard") {
            $stars = $this->g->o["stars"];
            if ($post_data->rules_articles == "A" || $post_data->rules_articles == "N") {
                $votes = $post_data->user_voters + $post_data->visitor_voters;
                $score = $post_data->user_votes + $post_data->visitor_votes;
            } else if ($post_data->rules_articles == "V") {
                $votes = $post_data->visitor_voters;
                $score = $post_data->visitor_votes;
            } else {
                $votes = $post_data->user_voters;
                $score = $post_data->user_votes;
            }
        } else {
            $data = gdsrBlgDB::get_rss_multi_data($post_id);
            if (count($row) > 0) {
                $set = wp_gdget_multi_set($data->multi_id);
                $stars = $set->stars;
                if ($post_data->rules_articles == "A" || $post_data->rules_articles == "N") {
                    $sum = $data->average_rating_users * $data->total_votes_users + $data->average_rating_visitors * $data->total_votes_visitors;
                    $votes = $data->total_votes_visitors + $data->total_votes_users;
                    $score = number_format($votes == 0 ? 0 : $sum / $votes, 1);
                } else if ($post_data->rules_articles == "V") {
                    $votes = $data->total_votes_visitors;
                    $score = $data->average_rating_visitors;
                } else {
                    $votes = $data->total_votes_users;
                    $score = $data->average_rating_users;
                }
            }
        }

        $rating_block = GDSRRenderT2::render_ssb($template_id, array("post_id" => $rd_post_id, "votes" => $votes, "score" => $score, "unit_count" => $stars, "header_text" => $this->g->o["rss_header_text"], "type" => $this->g->o["rss_datasource"]));
        return $rating_block;
    }

    // rendering waiting animations
    function render_wait_article_thumb() {
        if ($this->g->o["wait_loader_artthumb"] != "") {
            $cls = 'loader '.$this->g->o["wait_loader_artthumb"].' thumb';
            $div = '<div class="'.$cls.'" style="%s"></div>';
            $this->loader_article_thumb = $div;
        }
    }

    function render_wait_comment_thumb() {
        if ($this->g->o["wait_loader_cmmthumb"] != "") {
            $cls = 'loader thumb '.$this->g->o["wait_loader_cmmthumb"];
            $div = '<div class="'.$cls.'" style="%s"></div>';
            $this->loader_comment_thumb = $div;
        }
    }

    function render_wait_article() {
        $cls = "loader ".$this->g->o["wait_loader_article"]." ";
        if ($this->g->o["wait_show_article"] == 1)
            $cls.= "width ";
        $cls.= $this->g->o["wait_class_article"];
        $div = '<div class="'.$cls.'" style="height: '.$this->g->o["size"].'px">';
        if ($this->g->o["wait_show_article"] == 0) {
            $padding = "";
            if ($this->g->o["size"] > 20) $padding = ' style="padding-top: '.(($this->g->o["size"] / 2) - 10).'px"';
            $div.= '<div class="loaderinner"'.$padding.'>'.__($this->g->o["wait_text_article"]).'</div>';
        }
        $div.= '</div>';
        $this->loader_article = $div;
    }

    function render_wait_multis() {
        $cls = "loader ".$this->g->o["wait_loader_multis"]." ";
        if ($this->g->o["wait_show_multis"] == 1)
            $cls.= "width ";
        $cls.= $this->g->o["wait_class_multis"];
        $div = '<div class="'.$cls.'" style="height: '.$this->g->o["mur_size"].'px">';
        if ($this->g->o["wait_show_multis"] == 0) {
            $padding = "";
            if ($this->g->o["size"] > 20) $padding = ' style="padding-top: '.(($this->g->o["mur_size"] / 2) - 10).'px"';
            $div.= '<div class="loaderinner"'.$padding.'>'.__($this->g->o["wait_text_multis"]).'</div>';
        }
        $div.= '</div>';
        $this->loader_multis = $div;
    }

    function render_wait_comment() {
        $cls = "loader ".$this->g->o["wait_loader_comment"]." ";
        if ($this->g->o["wait_show_comment"] == 1)
            $cls.= "width ";
        $cls.= $this->g->o["wait_class_comment"];
        $div = '<div class="'.$cls.'" style="height: '.$this->g->o["cmm_size"].'px">';
        if ($this->g->o["wait_show_comment"] == 0) {
            $padding = "";
            if ($this->g->o["cmm_size"] > 20) $padding = ' style="padding-top: '.(($this->g->o["cmm_size"] / 2) - 10).'px"';
            $div.= '<div class="loaderinner"'.$padding.'>'.__($this->g->o["wait_text_comment"]).'</div>';
        }
        $div.= '</div>';
        $this->loader_comment = $div;
    }
    // rendering waiting animations

    // comment integration rating
    /**
    * Renders comment review stars
    *
    * @param int $value initial rating value
    * @param bool $allow_vote render stars to support rendering or not to
    */
    function comment_review($value = 0, $allow_vote = true, $override = array()) {
        $stars = $this->g->o["cmm_review_stars"];
        $style = $override["style"] == "" ? $this->g->o["cmm_review_style"] : $override["style"];
        $size = $override["size"] == 0 ? $this->g->o["cmm_review_size"] : $override["size"];
        return GDSRRender::rating_stars_local($style, $size, $stars, $allow_vote, $value * $size);
    }

    function get_comment_integrate_standard_result($comment_id, $post_id) {
        if (!$this->g->is_cached_integration_std) {
            global $gdsr_cache_integation_std;
            $data = GDSRDBCache::get_integration($post_id);
            foreach ($data as $row) {
                $id = $row->comment_id;
                $gdsr_cache_integation_std->set($id, $row);
            }

            $this->g->is_cached_integration_std = true;
        }

        return intval(wp_gdget_integration_std($comment_id));
    }

    /**
    * Renders result of comment integration of standard rating for specific comment
    *
    * @param int $comment_id initial rating value
    * @param string $stars_set set to use for rendering
    * @param int $stars_size set size to use for rendering
    * @param string $stars_set_ie6 set to use for rendering in ie6
    */
    function comment_integrate_standard_result($comment_id, $post_id, $stars_set = "oxygen", $stars_size = 20, $stars_set_ie6 = "oxygen_gif") {
        $value = $this->get_comment_integrate_standard_result($comment_id, $post_id);
        if ($value > 0 || $this->g->o["int_comment_std_zero"] == 1) {
            $style = $stars_set == "" ? $this->g->o["style"] : $stars_set;
            $style = $this->g->is_ie6 ? ($stars_set_ie6 == "" ? $this->g->o["style_ie6"] : $stars_set_ie6) : $style;
            return GDSRRender::render_static_stars($style, $stars_size == 0 ? $this->g->o["size"] : $stars_size, $this->g->o["stars"], $value);
        } else return "";
    }

    /**
    * Renders comment integration of standard rating
    *
    * @param int $value initial rating value
    * @param string $stars_set set to use for rendering
    * @param int $stars_size set size to use for rendering
    * @param string $stars_set_ie6 set to use for rendering in ie6
    */
    function comment_integrate_standard_rating($value = 0, $stars_set = "oxygen", $stars_size = 20, $stars_set_ie6 = "oxygen_gif") {
        $style = $stars_set == "" ? $this->g->o["style"] : $stars_set;
        $style = $this->g->is_ie6 ? ($stars_set_ie6 == "" ? $this->g->o["style_ie6"] : $stars_set_ie6) : $style;
        $size = $stars_size == 0 ? $this->g->o["size"] : $stars_size;
        return GDSRRender::rating_stars_local($style, $size, $this->g->o["stars"], true, $value * $size, "gdsr_int", "rcmmpost");
    }

    /**
    * Renders result of comment integration of multi rating for specific comment
    *
    * @param int $comment_id initial rating value
    * @param object $post_id post id
    * @param int $multi_set_id id of the multi rating set to use
    * @param int $template_id id of the template to use
    * @param string $stars_set set to use for rendering
    * @param int $stars_size set size to use for rendering
    * @param string $stars_set_ie6 set to use for rendering in ie6
    * @param string $avg_stars_set set to use for rendering of average value
    * @param int $avg_stars_size set size to use for rendering of average value
    * @param string $avg_stars_set_ie6 set to use for rendering of average value in ie6
    */
    function comment_integrate_multi_result($comment_id, $post_id, $multi_set_id, $template_id, $stars_set = 'oxygen', $stars_size = 20, $stars_set_ie6 = 'oxygen_gif', $avg_stars_set = 'oxygen', $avg_stars_size = 20, $avg_stars_set_ie6 = 'oxygen_gif') {
        if (!$this->g->is_cached_integration_mur) {
            global $gdsr_cache_integation_mur;
            $data = GDSRDBCache::get_integration($post_id, 'multis');

            foreach ($data as $row) {
                $id = $row->multi_id."_".$row->comment_id;
                $gdsr_cache_integation_mur->set($id, $row);
            }

            $this->g->is_cached_integration_mur = true;
        }

        $value = wp_gdget_integration_mur($comment_id, $multi_set_id);
        if (is_serialized($value) && !is_null($value)) {
            $value = unserialize($value);
            $set = gd_get_multi_set($multi_set_id);
            $weight_norm = array_sum($set->weight);
            $avg_rating = $i = 0;
            $votes = array();

            foreach ($value as $md) {
                $single_vote = array();
                $single_vote['votes'] = 1;
                $single_vote['score'] = $md;
                $single_vote['rating'] = $md;
                $avg_rating += ($md * $set->weight[$i]) / $weight_norm;
                $votes[] = $single_vote;
                $i++;
            }

            $avg_rating = @number_format($avg_rating, 1);

            if ($avg_rating > 0) {
                $style = $stars_set == '' ? $this->g->o['mur_style'] : $stars_set;
                $style = $this->g->is_ie6 ? ($stars_set_ie6 == "" ? $this->g->o['mur_style_ie6'] : $stars_set_ie6) : $style;
                return GDSRRenderT2::render_rmb($template_id, array('votes' => $votes, 'post_id' => $post_id, 'set' => $set, 'avg_rating' => $avg_rating, 'style' => $style, 'size' => $stars_size, 'avg_style' => $this->g->is_ie6 ? $avg_stars_set_ie6 : $avg_stars_set, 'avg_size' => $avg_stars_size));
            } else {
                return '';
            }
        } else return '';
    }

    /**
    * Renders average result of comment integration of multi rating for specific comment
    *
    * @param int $comment_id initial rating value
    * @param object $post_id post id
    * @param int $multi_set_id id of the multi rating set to use
    * @param int $template_id id of the template to use
    * @param string $avg_stars_set set to use for rendering of average value
    * @param int $avg_stars_size set size to use for rendering of average value
    * @param string $avg_stars_set_ie6 set to use for rendering of average value in ie6
    */
    function comment_integrate_multi_result_average($comment_id, $post_id, $multi_set_id, $template_id, $avg_stars_set = "oxygen", $avg_stars_size = 20, $avg_stars_set_ie6 = "oxygen_gif") {
        $value = GDSRDBMulti::rating_from_comment($comment_id, $multi_set_id);
        if (is_serialized($value)) {
            $value = unserialize($value);
            $set = gd_get_multi_set($multi_set_id);
            $weight_norm = array_sum($set->weight);
            $avg_rating = $i = 0;
            foreach ($value as $md) {
                $avg_rating += ($md * $set->weight[$i]) / $weight_norm;
                $i++;
            }
            $avg_rating = @number_format($avg_rating, 1);
            if ($avg_rating > 0) {
                return GDSRRenderT2::render_mcr($template_id, array("post_id" => $post_id, "set" => $set, "avg_rating" => $avg_rating, "avg_style" => $this->g->is_ie6 ? $avg_stars_set_ie6 : $avg_stars_set, "avg_size" => $avg_stars_size));
            } else return "";
        } else return "";
    }

    /**
    * Renders comment integration of multi rating
    *
    * @param int $value initial rating value
    * @param object $post_id post id
    * @param int $multi_set_id id of the multi rating set to use
    * @param int $template_id id of the template to use
    * @param string $stars_set set to use for rendering
    * @param int $stars_size set size to use for rendering
    * @param string $stars_set_ie6 set to use for rendering in ie6
    */
    function comment_integrate_multi_rating($value, $post_id, $multi_set_id, $template_id, $stars_set = "oxygen", $stars_size = 20, $stars_set_ie6 = "oxygen_gif") {
        if ($multi_set_id == 0) return "";

        $set = gd_get_multi_set($multi_set_id);
        $votes = array();
        for ($i = 0; $i < count($set->object); $i++) {
            $single_vote = array();
            $single_vote["votes"] = 0;
            $single_vote["score"] = 0;
            $single_vote["rating"] = 0;
            $votes[] = $single_vote;
        }
        $style = $stars_set == "" ? $this->g->o["mur_style"] : $stars_set;
        $style = $this->g->is_ie6 ? ($stars_set_ie6 == "" ? $this->g->o["mur_style_ie6"] : $stars_set_ie6) : $style;
        return GDSRRenderT2::render_mri($template_id, array("post_id" => $post_id, "style" => $style, "set" => $set, "height" => $stars_size));
    }
    // comment integration rating

    // comment rendering
    function rating_loader_elements_comment($post, $comment, $user, $override, $type) {
        $user_id = is_object($user) ? $user->ID : 0;
        switch ($type) {
            case "csr":
                return array(
                    $type, $post->ID, $comment->comment_ID, $comment->user_id,
                    $post->post_type == "page" ? "1" : "0", $post->post_author,
                    $user_id, $override["tpl"], $override["read_only"],
                    $override["size"], $this->g->g->find_stars_id($override["style"]),
                    $this->g->g->find_stars_id($override["style_ie6"])
                );
                break;
            case "ctr":
                return array(
                    $type, $post->ID, $comment->comment_ID, $comment->user_id,
                    $post->post_type == "page" ? "1" : "0", $post->post_author,
                    $user_id, $override["tpl"], $override["read_only"],
                    $override["size"], $this->g->g->find_thumb_id($override["style"]),
                    $this->g->g->find_thumb_id($override["style_ie6"])
                );
                break;
        }
    }

    function render_thumb_comment_actual($settings) {
        if ($this->g->o["comments_active"] != 1) return "";

        $post_id = intval($settings[1]);
        $comment_id = intval($settings[2]);
        $comment_author = intval($settings[3]);
        $rd_is_page = intval($settings[4]);
        $post_author = intval($settings[5]);
        $user_id = intval($settings[6]);

        $override["tpl"] = intval($settings[7]);
        $override["read_only"] = intval($settings[8]);
        $override["size"] = intval($settings[9]);
        $override["style"] = $this->g->g->thumbs[$settings[10]]->folder;
        $override["style_ie6"] = $this->g->g->thumbs[$settings[11]]->folder;

        $dbg_allow = "F";
        $already_voted = false;
        $allow_vote = $override["read_only"] == 0;
        $allow_vote = apply_filters("gdsr_allow_vote_thumb_comment", $allow_vote, $post_id);
        if ($this->g->is_ban && $this->g->o["ip_filtering"] == 1) {
            if ($this->g->o["ip_filtering_restrictive"] == 1) return "";
            else $allow_vote = false;
            $dbg_allow = "B";
        }

        $rd_unit_width = $override["size"];
        $rd_unit_style = $this->g->is_ie6 ? $override["style_ie6"] : $override["style"];
        $rd_post_id = intval($post_id);
        $rd_user_id = intval($user_id);
        $rd_comment_id = intval($comment_id);

        $post_data = wp_gdget_post($rd_post_id);
        if (!is_object($post_data)) {
            GDSRDatabase::add_default_vote($rd_post_id, $rd_is_page);
            $post_data = wp_gdget_post($rd_post_id);
            $this->g->c[$rd_post_id] = 1;
        }

        $rules_comments = $post_data->recc_rules_comments != "I" ? $post_data->recc_rules_comments : $this->g->get_post_rule_value($rd_post_id, "recc_rules_comments", "recc_default_voterules_comments");

        if ($rules_comments == "H") return "";
        $comment_data = wp_gdget_comment($rd_comment_id);
        if (count($comment_data) == 0) {
            GDSRDatabase::add_empty_comment($rd_comment_id, $rd_post_id);
            $comment_data = wp_gdget_comment($rd_comment_id);
        }

        if ($allow_vote) {
            if ($this->g->o["cmm_author_vote"] == 1 && $rd_user_id == $comment_author && $rd_user_id > 0) {
                $allow_vote = false;
                $dbg_allow = "A";
            }
        }

        if ($allow_vote) {
            if (($rules_comments == "") ||
                ($rules_comments == "A") ||
                ($rules_comments == "U" && $rd_user_id > 0) ||
                ($rules_comments == "V" && $rd_user_id == 0)
            ) $allow_vote = true;
            else {
                $allow_vote = false;
                $dbg_allow = "R_".$rules_comments;
            }
        }

        $already_voted = !wp_gdget_thumb_commentlog($rd_comment_id);
        if ($allow_vote) {
            $allow_vote = !$already_voted;
            if (!$allow_vote) $dbg_allow = "D";
        }

        if ($allow_vote) {
            $allow_vote = gdsrFrontHelp::check_cookie($rd_comment_id, "cmmthumb");
            if (!$allow_vote) $dbg_allow = "C";
        }

        $votes = $score = $votes_plus = $votes_minus = 0;

        if ($rules_comments == "A" || $rules_comments == "N") {
            $votes = $comment_data->user_recc_plus + $comment_data->user_recc_minus + $comment_data->visitor_recc_plus + $comment_data->visitor_recc_minus;
            $score = $comment_data->user_recc_plus - $comment_data->user_recc_minus + $comment_data->visitor_recc_plus - $comment_data->visitor_recc_minus;
            $votes_plus = $comment_data->user_recc_plus + $comment_data->visitor_recc_plus;
            $votes_minus = $comment_data->user_recc_minus + $comment_data->visitor_recc_minus;
        } else if ($rules_comments == "V") {
            $votes = $comment_data->visitor_recc_plus + $comment_data->visitor_recc_minus;
            $score = $comment_data->visitor_recc_plus - $comment_data->visitor_recc_minus;
            $votes_plus = $comment_data->visitor_recc_plus;
            $votes_minus = $comment_data->visitor_recc_minus;
        } else {
            $votes = $comment_data->user_recc_plus + $comment_data->user_recc_minus;
            $score = $comment_data->user_recc_plus - $comment_data->user_recc_minus;
            $votes_plus = $comment_data->user_recc_plus;
            $votes_minus = $comment_data->user_recc_minus;
        }

        $debug = $rd_user_id == 0 ? "V" : "U";
        $debug.= $rd_user_id == $comment_author ? "A" : "N";
        $debug.= ":".$dbg_allow." [".STARRATING_VERSION."]";

        $tags_css = array();
        $tags_css["CMM_CSS_BLOCK"] = $this->g->o["cmm_class_block"];
        $tags_css["CMM_CSS_HEADER"] = $this->g->o["srb_class_header"];
        $tags_css["CMM_CSS_STARS"] = $this->g->o["cmm_class_stars"];
        $tags_css["CMM_CSS_TEXT"] = $this->g->o["cmm_class_text"];

        $template_id = $override["tpl"];
        $rating_block = GDSRRenderT2::render_tcb($template_id, array("already_voted" => $already_voted, "comment_id" => $rd_comment_id, "votes" => $votes, "score" => $score, "votes_plus" => $votes_plus, "votes_minus" => $votes_minus, "style" => $rd_unit_style, "unit_width" => $rd_unit_width, "allow_vote" => $allow_vote, "user_id" => $rd_user_id, "tags_css" => $tags_css, "header_text" => $this->g->o["header_text"], "debug" => $debug, "wait_msg" => $this->loader_comment_thumb));
        return $rating_block;
    }

    function render_thumb_comment($post, $comment, $user, $override = array()) {
        if ($this->g->is_bot && $this->g->o["cached_loading"] == 0 && $this->g->o["bot_message"] != "normal") return GDSRRender::render_locked_response($this->g->o["bot_message"]);

        $default_settings = array("style" => $this->g->o["thumb_cmm_style"], "style_ie6" => $this->g->o["thumb_cmm_style_ie6"], "size" => $this->g->o["thumb_cmm_size"], "tpl" => 0, "read_only" => 0);
        $override = shortcode_atts($default_settings, $override);
        if ($override["style"] == "") $override["style"] = $this->g->o["thumb_cmm_style"];
        if ($override["style_ie6"] == "") $override["style_ie6"] = $this->g->o["thumb_cmm_style_ie6"];
        if ($override["size"] == "") $override["size"] = $this->g->o["thumb_cmm_size"];
        if ($override["tpl"] == 0) $override["tpl"] = $this->g->o["default_tcb_template"];

        $elements = $this->rating_loader_elements_comment($post, $comment, $user, $override, "ctr");

        if ($this->g->o["cached_loading"] == 1)
            return GDSRRender::rating_loader(join(".", $elements), $this->g->is_bot, "small");
        else
            return $this->render_thumb_comment_actual($elements);
    }

    function render_comment_actual($settings) {
        if ($this->g->o["comments_active"] != 1) return "";

        $post_id = intval($settings[1]);
        $comment_id = intval($settings[2]);
        $comment_author = intval($settings[3]);
        $rd_is_page = intval($settings[4]);
        $post_author = intval($settings[5]);
        $user_id = intval($settings[6]);

        $override["tpl"] = intval($settings[7]);
        $override["read_only"] = intval($settings[8]);
        $override["size"] = intval($settings[9]);
        $override["style"] = $this->g->g->stars[$settings[10]]->folder;
        $override["style_ie6"] = $this->g->g->stars[$settings[11]]->folder;

        $dbg_allow = "F";
        $already_voted = false;
        $allow_vote = $override["read_only"] == 0;
        $allow_vote = apply_filters("gdsr_allow_vote_stars_comment", $allow_vote, $post_id);
        if ($this->g->is_ban && $this->g->o["ip_filtering"] == 1) {
            if ($this->g->o["ip_filtering_restrictive"] == 1) return "";
            else $allow_vote = false;
            $dbg_allow = "B";
        }

        $rd_unit_count = $this->g->o["cmm_stars"];
        $rd_unit_width = $override["size"];
        $rd_unit_style = $this->g->is_ie6 ? $override["style_ie6"] : $override["style"];
        $rd_post_id = intval($post_id);
        $rd_user_id = intval($user_id);
        $rd_comment_id = intval($comment_id);

        $post_data = wp_gdget_post($rd_post_id);
        if (!is_object($post_data)) {
            GDSRDatabase::add_default_vote($rd_post_id, $rd_is_page);
            $post_data = wp_gdget_post($rd_post_id);
            $this->g->c[$rd_post_id] = 1;
        }

        $rules_comments = $post_data->rules_comments != "I" ? $post_data->rules_comments : $this->g->get_post_rule_value($rd_post_id, "rules_comments", "default_voterules_comments");

        if ($rules_comments == "H") return "";
        $comment_data = wp_gdget_comment($rd_comment_id);
        if (count($comment_data) == 0) {
            GDSRDatabase::add_empty_comment($rd_comment_id, $rd_post_id);
            $comment_data = wp_gdget_comment($rd_comment_id);
        }

        if ($allow_vote) {
            if ($this->g->o["cmm_author_vote"] == 1 && $rd_user_id == $comment_author && $rd_user_id > 0) {
                $allow_vote = false;
                $dbg_allow = "A";
            }
        }

        if ($allow_vote) {
            if (($rules_comments == "") ||
                ($rules_comments == "A") ||
                ($rules_comments == "U" && $rd_user_id > 0) ||
                ($rules_comments == "V" && $rd_user_id == 0)
            ) $allow_vote = true;
            else {
                $allow_vote = false;
                $dbg_allow = "R_".$rules_comments;
            }
        }

        $already_voted = !wp_gdget_commentlog($rd_comment_id);
        if ($allow_vote) {
            $allow_vote = !$already_voted;
            if (!$allow_vote) $dbg_allow = "D";
        }

        if ($allow_vote) {
            $allow_vote = gdsrFrontHelp::check_cookie($rd_comment_id, "comment");
            if (!$allow_vote) $dbg_allow = "C";
        }

        $votes = 0;
        $score = 0;

        if ($rules_comments == "A" || $rules_comments == "N") {
            $votes = $comment_data->user_voters + $comment_data->visitor_voters;
            $score = $comment_data->user_votes + $comment_data->visitor_votes;
        } else if ($rules_comments == "V") {
            $votes = $comment_data->visitor_voters;
            $score = $comment_data->visitor_votes;
        } else {
            $votes = $comment_data->user_voters;
            $score = $comment_data->user_votes;
        }

        $debug = $rd_user_id == 0 ? "V" : "U";
        $debug.= $rd_user_id == $comment_author ? "A" : "N";
        $debug.= ":".$dbg_allow." [".STARRATING_VERSION."]";

        $tags_css = array(
            "CMM_CSS_BLOCK" => $this->g->o["cmm_class_block"],
            "CMM_CSS_HEADER" => $this->g->o["srb_class_header"],
            "CMM_CSS_STARS" => $this->g->o["cmm_class_stars"],
            "CMM_CSS_TEXT" => $this->g->o["cmm_class_text"]
        );

        $template_id = $override["tpl"];
        $rating_block = GDSRRenderT2::render_crb($template_id, array("already_voted" => $already_voted, "cmm_id" => $rd_comment_id, "class" => "ratecmm", "type" => "c", "votes" => $votes, "score" => $score, "style" => $rd_unit_style, "unit_width" => $rd_unit_width, "unit_count" => $rd_unit_count, "allow_vote" => $allow_vote, "user_id" => $rd_user_id, "typecls" => "comment", "tags_css" => $tags_css, "header_text" => $this->g->o["cmm_header_text"], "debug" => $debug, "wait_msg" => $this->loader_comment));
        return $rating_block;
    }

    function render_comment($post, $comment, $user, $override = array()) {
        if ($this->g->is_bot && $this->g->o["cached_loading"] == 0 && $this->g->o["bot_message"] != "normal") return GDSRRender::render_locked_response($this->g->o["bot_message"]);

        $default_settings = array("style" => $this->g->o["cmm_style"], "style_ie6" => $this->g->o["cmm_style_ie6"], "size" => $this->g->o["cmm_size"], "tpl" => 0, "read_only" => 0);
        $override = shortcode_atts($default_settings, $override);
        if ($override["style"] == "") $override["style"] = $this->g->o["cmm_style"];
        if ($override["style_ie6"] == "") $override["style_ie6"] = $this->g->o["cmm_style_ie6"];
        if ($override["size"] == "") $override["size"] = $this->g->o["cmm_size"];
        if ($override["tpl"] == 0) $override["tpl"] = $this->g->o["default_crb_template"];

        $elements = $this->rating_loader_elements_comment($post, $comment, $user, $override, "csr");

        if ($this->g->o["cached_loading"] == 1)
            return GDSRRender::rating_loader(join(".", $elements), $this->g->is_bot, "small");
        else
            return $this->render_comment_actual($elements);
    }
    // comment rendering

    // article rendering
    function rating_loader_elements_post($post, $user, $override, $type) {
        $user_id = is_object($user) ? $user->ID : 0;
        switch ($type) {
            case "amr":
                return array(
                    $type, $post->ID, $post->post_type == "page" ? "1" : "0",
                    $post->post_author, strtotime($post->post_date),
                    $override["tpl"], $override["read_only"], $override["size"],
                    $this->g->g->find_stars_id($override["style"]),
                    $this->g->g->find_stars_id($override["style_ie6"]), $user_id,
                    $override["id"], $override["average_size"],
                    $this->g->g->find_stars_id($override["average_stars"]),
                    $this->g->g->find_stars_id($override["average_stars_ie6"])
                );
                break;
            case "asr":
                return array(
                    $type, $post->ID, $post->post_type == "page" ? "1" : "0",
                    $post->post_author, strtotime($post->post_date),
                    $override["tpl"], $override["read_only"], $override["size"],
                    $this->g->g->find_stars_id($override["style"]),
                    $this->g->g->find_stars_id($override["style_ie6"]), $user_id
                );
                break;
            case "atr":
                return array(
                    $type, $post->ID, $post->post_type == "page" ? "1" : "0",
                    $post->post_author, strtotime($post->post_date),
                    $override["tpl"], $override["read_only"], $override["size"],
                    $this->g->g->find_thumb_id($override["style"]),
                    $this->g->g->find_thumb_id($override["style_ie6"]), $user_id
                );
                break;
        }
    }

    function render_thumb_article_actual($settings) {
        $rd_post_id = intval($settings[1]);
        $rd_is_page = intval($settings[2]);
        $post_author = intval($settings[3]);
        $post_date = intval($settings[4]);

        $rd_unit_width = $settings[7];
        $override["tpl"] = intval($settings[5]);
        $override["read_only"] = intval($settings[6]);
        $override["style"] = $this->g->g->thumbs[$settings[8]]->folder;
        $override["style_ie6"] = $this->g->g->thumbs[$settings[9]]->folder;
        $rd_unit_style = $this->g->is_ie6 ? $override["style_ie6"] : $override["style"];

        $rd_user_id = intval($settings[10]);

        $dbg_allow = "F";
        $already_voted = false;
        $allow_vote = $override["read_only"] == 0;
        $allow_vote = apply_filters("gdsr_allow_vote_thumb_article", $allow_vote, $rd_post_id);
        if ($this->g->is_ban && $this->g->o["ip_filtering"] == 1) {
            if ($this->g->o["ip_filtering_restrictive"] == 1) return "";
            else $allow_vote = false;
            $dbg_allow = "B";
        }

        if ($override["read_only"] == 1) $dbg_allow = "RO";
        $post_data = wp_gdget_post($rd_post_id);
        if (is_null($post_data) || !is_object($post_data)) {
            GDSRDatabase::add_default_vote($rd_post_id, $rd_is_page);
            $post_data = wp_gdget_post($rd_post_id);
            $this->g->c[$rd_post_id] = 1;
        }

        $rules_articles = $post_data->recc_rules_articles != "I" ? $post_data->recc_rules_articles : $this->g->get_post_rule_value($rd_post_id, "recc_rules_articles", "recc_default_voterules_articles");

        if ($rules_articles == "H") return "";
        if ($allow_vote) {
            if (($rules_articles == "") || ($rules_articles == "A") ||
                ($rules_articles == "U" && $rd_user_id > 0) ||
                ($rules_articles == "V" && $rd_user_id == 0)
            ) $allow_vote = true;
            else {
                $allow_vote = false;
                $dbg_allow = "R_".$rules_articles;
            }
        }

        if ($allow_vote) {
            if ($this->g->o["author_vote"] == 1 && $rd_user_id == $post_author) {
                $allow_vote = false;
                $dbg_allow = "A";
            }
        }

        $remaining = 0;
        $deadline = '';
        $expiry_type = 'N';
        if ($allow_vote && ($post_data->expiry_type == 'D' || $post_data->expiry_type == 'T' || $post_data->expiry_type == 'I')) {
            $expiry_type = $post_data->expiry_type != 'I' ? $post_data->expiry_type : $this->g->get_post_rule_value($rd_post_id, "expiry_type", "default_timer_type");
            $expiry_value = $post_data->expiry_type != 'I' ? $post_data->expiry_value : $this->g->get_post_rule_value($rd_post_id, "expiry_value", "default_timer_value");
            switch($expiry_type) {
                case "D":
                    $remaining = gdsrFrontHelp::expiration_date($expiry_value);
                    $deadline = $expiry_value;
                    break;
                case "T":
                    $remaining = gdsrFrontHelp::expiration_countdown($post_date, $expiry_value);
                    $deadline = gdsrFrontHelp::calculate_deadline($remaining);
                    break;
            }
            if ($remaining < 1) {
                gdsrBlgDB::lock_post($rd_post_id);
                $allow_vote = false;
                $dbg_allow = "T";
            }
        }

        $already_voted = !wp_gdget_thumb_postlog($rd_post_id);
        if ($allow_vote) {
            $allow_vote = !$already_voted;
            if (!$allow_vote) $dbg_allow = "D";
        }

        if ($allow_vote) {
            $allow_vote = gdsrFrontHelp::check_cookie($rd_post_id, "artthumb");
            if (!$allow_vote) $dbg_allow = "C";
        }

        $votes = $score = $votes_plus = $votes_minus = 0;

        if ($rules_articles == "A" || $rules_articles == "N") {
            $votes = $post_data->user_recc_plus + $post_data->user_recc_minus + $post_data->visitor_recc_plus + $post_data->visitor_recc_minus;
            $score = $post_data->user_recc_plus - $post_data->user_recc_minus + $post_data->visitor_recc_plus - $post_data->visitor_recc_minus;
            $votes_plus = $post_data->user_recc_plus + $post_data->visitor_recc_plus;
            $votes_minus = $post_data->user_recc_minus + $post_data->visitor_recc_minus;
        } else if ($rules_articles == "V") {
            $votes = $post_data->visitor_recc_plus + $post_data->visitor_recc_minus;
            $score = $post_data->visitor_recc_plus - $post_data->visitor_recc_minus;
            $votes_plus = $post_data->visitor_recc_plus;
            $votes_minus = $post_data->visitor_recc_minus;
        } else {
            $votes = $post_data->user_recc_plus + $post_data->user_recc_minus;
            $score = $post_data->user_recc_plus - $post_data->user_recc_minus;
            $votes_plus = $post_data->user_recc_plus;
            $votes_minus = $post_data->user_recc_minus;
        }

        $debug = $rd_user_id == 0 ? "V" : "U";
        $debug.= $rd_user_id == $post_author ? "A" : "N";
        $debug.= ":".$dbg_allow." [".STARRATING_VERSION."]";

        $tags_css = array(
            "CSS_BLOCK" => $this->g->o["srb_class_block"],
            "CSS_HEADER" => $this->g->o["srb_class_header"],
            "CSS_STARS" => $this->g->o["srb_class_stars"],
            "CSS_TEXT" => $this->g->o["srb_class_text"]
        );

        $template_id = $override["tpl"];
        $rating_block = GDSRRenderT2::render_tab($template_id, array("already_voted" => $already_voted, "post_id" => $rd_post_id, "votes" => $votes, "score" => $score, "votes_plus" => $votes_plus, "votes_minus" => $votes_minus, "style" => $rd_unit_style, "unit_width" => $rd_unit_width, "allow_vote" => $allow_vote, "user_id" => $rd_user_id, "tags_css" => $tags_css, "header_text" => $this->g->o["thumb_header_text"], "debug" => $debug, "wait_msg" => $this->loader_article_thumb, "time_restirctions" => $expiry_type, "time_remaining" => $remaining, "time_date" => $deadline));
        return $rating_block;
    }

    function render_thumb_article($post, $user, $override = array()) {
        if (is_feed()) return "";
        if ($this->g->is_bot && $this->g->o["cached_loading"] == 0 && $this->g->o["bot_message"] != "normal") return GDSRRender::render_locked_response($this->g->o["bot_message"]);

        $default_settings = array("style" => $this->g->o["thumb_style"], "style_ie6" => $this->g->o["thumb_style_ie6"], "size" => $this->g->o["thumb_size"], "tpl" => 0, "read_only" => 0);
        $override = shortcode_atts($default_settings, $override);
        if ($override["style"] == "") $override["style"] = $this->g->o["thumb_style"];
        if ($override["style_ie6"] == "") $override["style_ie6"] = $this->g->o["thumb_style_ie6"];
        if ($override["size"] == "") $override["size"] = $this->g->o["thumb_size"];
        if ($override["tpl"] == 0) $override["tpl"] = $this->g->o["default_tab_template"];

        $elements = $this->rating_loader_elements_post($post, $user, $override, "atr");

        if ($this->g->o["cached_loading"] == 1)
            return GDSRRender::rating_loader(join(".", $elements), $this->g->is_bot, "small");
        else
            return $this->render_thumb_article_actual($elements);
    }

    function render_article_actual($settings) {
        $rd_post_id = intval($settings[1]);
        $rd_is_page = intval($settings[2]);
        $post_author = intval($settings[3]);
        $post_date = intval($settings[4]);

        $override["tpl"] = intval($settings[5]);
        $override["read_only"] = intval($settings[6]);
        $override["size"] = intval($settings[7]);
        $override["style"] = $this->g->g->stars[$settings[8]]->folder;
        $override["style_ie6"] = $this->g->g->stars[$settings[9]]->folder;

        $rd_user_id = intval($settings[10]);

        $dbg_allow = "F";
        $already_voted = false;
        $allow_vote = $override["read_only"] == 0;
        $allow_vote = apply_filters("gdsr_allow_vote_stars_article", $allow_vote, $rd_post_id);
        if ($this->g->override_readonly_standard) {
            $allow_vote = false;
            $dbg_allow = "RTO";
        }
        if ($this->g->is_ban && $this->g->o["ip_filtering"] == 1) {
            if ($this->g->o["ip_filtering_restrictive"] == 1) return "";
            else $allow_vote = false;
            $dbg_allow = "B";
        }

        if ($override["read_only"] == 1) $dbg_allow = "RO";

        $rd_unit_count = $this->g->o["stars"];
        $rd_unit_width = $override["size"];
        $rd_unit_style = $this->g->is_ie6 ? $override["style_ie6"] : $override["style"];

        $post_data = wp_gdget_post($rd_post_id);
        if (!is_object($post_data)) {
            GDSRDatabase::add_default_vote($rd_post_id, $rd_is_page);
            $post_data = wp_gdget_post($rd_post_id);
            $this->g->c[$rd_post_id] = 1;
        }

        $rules_articles = $post_data->rules_articles != "I" ? $post_data->rules_articles : $this->g->get_post_rule_value($rd_post_id, "rules_articles", "default_voterules_articles");

        if ($rules_articles == "H") return "";
        if ($allow_vote) {
            if (($rules_articles == "") ||
                ($rules_articles == "A") ||
                ($rules_articles == "U" && $rd_user_id > 0) ||
                ($rules_articles == "V" && $rd_user_id == 0)
            ) $allow_vote = true;
            else {
                $allow_vote = false;
                $dbg_allow = "R_".$rules_articles;
            }
        }

        if ($allow_vote) {
            if ($this->g->o["author_vote"] == 1 && $rd_user_id == $post_author) {
                $allow_vote = false;
                $dbg_allow = "A";
            }
        }

        $remaining = 0;
        $deadline = '';
        $expiry_type = 'N';
        if ($allow_vote && ($post_data->expiry_type == 'D' || $post_data->expiry_type == 'T' || $post_data->expiry_type == 'I')) {
            $expiry_type = $post_data->expiry_type != 'I' ? $post_data->expiry_type : $this->g->get_post_rule_value($rd_post_id, "expiry_type", "default_timer_type");
            $expiry_value = $post_data->expiry_type != 'I' ? $post_data->expiry_value : $this->g->get_post_rule_value($rd_post_id, "expiry_value", "default_timer_value");
            switch($expiry_type) {
                case "D":
                    $remaining = gdsrFrontHelp::expiration_date($expiry_value);
                    $deadline = $expiry_value;
                    break;
                case "T":
                    $remaining = gdsrFrontHelp::expiration_countdown($post_date, $expiry_value);
                    $deadline = gdsrFrontHelp::calculate_deadline($remaining);
                    break;
            }
            if ($remaining < 1) {
                gdsrBlgDB::lock_post($rd_post_id);
                $allow_vote = false;
                $dbg_allow = "T";
            }
        }

        $already_voted = !wp_gdget_postlog($rd_post_id);
        if ($allow_vote) {
            $allow_vote = !$already_voted;
            if (!$allow_vote) $dbg_allow = "D";
        }

        if ($allow_vote) {
            $allow_vote = gdsrFrontHelp::check_cookie($rd_post_id);
            if (!$allow_vote) $dbg_allow = "C";
        }

        $votes = $score = 0;

        if ($rules_articles == "A" || $rules_articles == "N") {
            $votes = $post_data->user_voters + $post_data->visitor_voters;
            $score = $post_data->user_votes + $post_data->visitor_votes;
        } else if ($rules_articles == "V") {
            $votes = $post_data->visitor_voters;
            $score = $post_data->visitor_votes;
        } else {
            $votes = $post_data->user_voters;
            $score = $post_data->user_votes;
        }

        $debug = $rd_user_id == 0 ? "V" : "U";
        $debug.= $rd_user_id == $post_author ? "A" : "N";
        $debug.= ":".$dbg_allow." [".STARRATING_VERSION."]";

        $tags_css = array(
            "CSS_BLOCK" => $this->g->o["srb_class_block"],
            "CSS_HEADER" => $this->g->o["srb_class_header"],
            "CSS_STARS" => $this->g->o["srb_class_stars"],
            "CSS_TEXT" => $this->g->o["srb_class_text"]
        );

        $template_id = $override["tpl"];
        $rating_block = GDSRRenderT2::render_srb($template_id, array("already_voted" => $already_voted, "post_id" => $rd_post_id, "class" => "ratepost", "type" => "a", "votes" => $votes, "score" => $score, "style" => $rd_unit_style, "unit_width" => $rd_unit_width, "unit_count" => $rd_unit_count, "allow_vote" => $allow_vote, "user_id" => $rd_user_id, "typecls" => "article", "tags_css" => $tags_css, "header_text" => $this->g->o["header_text"], "debug" => $debug, "wait_msg" => $this->loader_article, "time_restirctions" => $expiry_type, "time_remaining" => $remaining, "time_date" => $deadline));
        return $rating_block;
    }

    function render_article($post, $user, $override = array()) {
        if (is_feed()) return "";
        if ($this->g->is_bot && $this->g->o["cached_loading"] == 0 && $this->g->o["bot_message"] != "normal") return GDSRRender::render_locked_response($this->g->o["bot_message"]);

        $default_settings = array("style" => $this->g->o["style"], "style_ie6" => $this->g->o["style_ie6"], "size" => $this->g->o["size"], "tpl" => 0, "read_only" => 0);
        $override = shortcode_atts($default_settings, $override);
        if ($override["style"] == "") $override["style"] = $this->g->o["style"];
        if ($override["style_ie6"] == "") $override["style_ie6"] = $this->g->o["style_ie6"];
        if ($override["size"] == "") $override["size"] = $this->g->o["size"];
        if ($override["tpl"] == 0) $override["tpl"] = $this->g->o["default_srb_template"];

        $elements = $this->rating_loader_elements_post($post, $user, $override, "asr");

        if ($this->g->o["cached_loading"] == 1)
            return GDSRRender::rating_loader(join(".", $elements), $this->g->is_bot, "small");
        else
            return $this->render_article_actual($elements);
    }

    function render_multi_rating_actual($settings) {
        if ($this->g->is_bot && $this->g->o["bot_message"] != "normal") return GDSRRender::render_locked_response($this->g->o["bot_message"]);

        $rd_post_id = intval($settings[1]);
        $rd_is_page = intval($settings[2]);
        $post_author = intval($settings[3]);
        $post_date = intval($settings[4]);

        $override["id"] = intval($settings[11]);
        $override["tpl"] = intval($settings[5]);
        $override["read_only"] = intval($settings[6]);
        $override["size"] = intval($settings[7]);
        $override["style"] = $this->g->g->stars[$settings[8]]->folder;
        $override["style_ie6"] = $this->g->g->stars[$settings[9]]->folder;

        $rd_user_id = intval($settings[10]);

        $override["average_size"] = intval($settings[12]);
        $override["average_stars"] = $this->g->g->stars[$settings[13]]->folder;
        $override["average_stars_ie6"] = $this->g->g->stars[$settings[14]]->folder;

        $set = gd_get_multi_set($override["id"]);
        if ($set == null) return "";

        $rd_unit_width = $override["size"];
        $rd_unit_style = $this->g->is_ie6 ? $override["style_ie6"] : $override["style"];
        $rd_unit_width_avg = $override["average_size"];
        $rd_unit_style_avg = $this->g->is_ie6 ? $override["average_stars_ie6"] : $override["average_stars"];

        $dbg_allow = "F";
        $already_voted = false;
        $allow_vote = $override["read_only"] == 0;
        $allow_vote = apply_filters("gdsr_allow_vote_stars_article", $allow_vote, $rd_post_id, $override["id"]);
        if ($this->g->override_readonly_multis) {
            $allow_vote = false;
            $dbg_allow = "RTO";
        }
        if ($this->g->is_ban && $this->g->o["ip_filtering"] == 1) {
            if ($this->g->o["ip_filtering_restrictive"] == 1) return "";
            else $allow_vote = false;
            $dbg_allow = "B";
        }

        if ($override["read_only"] == 1) $dbg_allow = "RO";

        $remaining = 0;
        $deadline = "";

        $post_data = wp_gdget_post($rd_post_id);
        if (!is_object($post_data)) {
            GDSRDatabase::add_default_vote($rd_post_id, $rd_is_page);
            $post_data = wp_gdget_post($rd_post_id);
            $this->g->c[$rd_post_id] = 1;
        }

        $rules_articles = $post_data->rules_articles != "I" ? $post_data->rules_articles : $this->g->get_post_rule_value($rd_post_id, "rules_articles", "default_voterules_articles");

        if ($rules_articles == "H") return "";
        if ($allow_vote) {
            if ($this->g->o["author_vote"] == 1 && $rd_user_id == $post_author) {
                $allow_vote = false;
                $dbg_allow = "A";
            }
        }

        if ($allow_vote) {
            if (($rules_articles == "") ||
                ($rules_articles == "A") ||
                ($rules_articles == "U" && $rd_user_id > 0) ||
                ($rules_articles == "V" && $rd_user_id == 0)
            ) $allow_vote = true;
            else {
                $allow_vote = false;
                $dbg_allow = "R_".$rules_articles;
            }
        }

        $remaining = 0;
        $deadline = '';
        $expiry_type = 'N';
        if ($allow_vote && ($post_data->expiry_type == 'D' || $post_data->expiry_type == 'T' || $post_data->expiry_type == 'I')) {
            $expiry_type = $post_data->expiry_type != 'I' ? $post_data->expiry_type : $this->g->get_post_rule_value($rd_post_id, "expiry_type", "default_timer_type");
            $expiry_value = $post_data->expiry_type != 'I' ? $post_data->expiry_value : $this->g->get_post_rule_value($rd_post_id, "expiry_value", "default_timer_value");
            switch($expiry_type) {
                case "D":
                    $remaining = gdsrFrontHelp::expiration_date($expiry_value);
                    $deadline = $expiry_value;
                    break;
                case "T":
                    $remaining = gdsrFrontHelp::expiration_countdown($post_date, $expiry_value);
                    $deadline = gdsrFrontHelp::calculate_deadline($remaining);
                    break;
            }
            if ($remaining < 1) {
                gdsrBlgDB::lock_post($rd_post_id);
                $allow_vote = false;
                $dbg_allow = "T";
            }
        }

        $already_voted = !GDSRDBMulti::check_vote($rd_post_id, $rd_user_id, $set->multi_id, 'multis', $_SERVER["REMOTE_ADDR"], $this->g->o["logged"] != 1, $this->g->o["mur_allow_mixed_ip_votes"] == 1);
        if ($allow_vote) {
            $allow_vote = !$already_voted;
            if (!$allow_vote) $dbg_allow = "D";
        }

        if ($allow_vote) {
            $allow_vote = gdsrFrontHelp::check_cookie($rd_post_id."#".$set->multi_id, "multis");
            if (!$allow_vote) $dbg_allow = "C";
        }

        $multi_record_id = GDSRDBMulti::get_vote($rd_post_id, $set->multi_id, count($set->object));
        $multi_data = GDSRDBMulti::get_values($multi_record_id);

        $votes = array();
        foreach ($multi_data as $md) {
            $single_vote = array();
            $single_vote["votes"] = 0;
            $single_vote["score"] = 0;

            if ($rules_articles == "A" || $rules_articles == "N") {
                $single_vote["votes"] = $md->user_voters + $md->visitor_voters;
                $single_vote["score"] = $md->user_votes + $md->visitor_votes;
            } else if ($rules_articles == "V") {
                $single_vote["votes"] = $md->visitor_voters;
                $single_vote["score"] = $md->visitor_votes;
            } else {
                $single_vote["votes"] = $md->user_voters;
                $single_vote["score"] = $md->user_votes;
            }
            $rating = $single_vote["votes"] > 0 ? $single_vote["score"] / $single_vote["votes"] : 0;

            if ($rating > $set->stars) $rating = $set->stars;
            $single_vote["rating"] = @number_format($rating, 1);

            $votes[] = $single_vote;
        }

        $debug = $rd_user_id == 0 ? "V" : "U";
        $debug.= $rd_user_id == $post_author ? "A" : "N";
        $debug.= ":".$dbg_allow." [".STARRATING_VERSION."]";

        $tags_css = array(
            "MUR_CSS_BUTTON" => $this->g->o["mur_class_button"],
            "MUR_CSS_BLOCK" => $this->g->o["mur_class_block"],
            "MUR_CSS_HEADER" => $this->g->o["mur_class_header"],
            "MUR_CSS_STARS" => $this->g->o["mur_class_stars"],
            "MUR_CSS_TEXT" => $this->g->o["mur_class_text"]
        );

        $mur_button = $this->g->o["mur_button_active"] == 1;
        if (!$allow_vote) $mur_button = false;

        $template_id = $override["tpl"];
        return GDSRRenderT2::render_mrb($template_id, array("already_voted" => $already_voted, "style" => $rd_unit_style, "allow_vote" => $allow_vote, "votes" => $votes, "post_id" => $rd_post_id, "set" => $set, "height" => $rd_unit_width, "header_text" => $this->g->o["mur_header_text"], "tags_css" => $tags_css, "avg_style" => $rd_unit_style_avg, "avg_size" => $rd_unit_width_avg, "star_factor" => 1, "time_restirctions" => $expiry_type, "time_remaining" => $remaining, "time_date" => $deadline, "button_active" => $mur_button, "button_text" => $this->g->o["mur_button_text"], "debug" => $debug, "wait_msg" => $this->loader_multis));
    }

    function render_multi_rating($post, $user, $override = array()) {
        if (is_feed()) return "";
        if ($this->g->is_bot && $this->g->o["cached_loading"] == 0 && $this->g->o["bot_message"] != "normal") return GDSRRender::render_locked_response($this->g->o["bot_message"]);

        $default_settings = array("id" => 0, "style" => $this->g->o["mur_style"], "style_ie6" => $this->g->o["mur_style_ie6"], "size" => $this->g->o["mur_size"], "average_stars" => "oxygen", "average_stars_ie6" => "oxygen_gif", "average_size" => 30, "tpl" => 0, "read_only" => 0);
        $override = shortcode_atts($default_settings, $override);
        if ($override["style"] == "") $override["style"] = $this->g->o["mur_style"];
        if ($override["style_ie6"] == "") $override["style_ie6"] = $this->g->o["mur_style_ie6"];
        if ($override["size"] == "") $override["size"] = $this->g->o["mur_size"];
        if ($override["tpl"] == 0) $override["tpl"] = $this->g->o["default_srb_template"];

        $elements = $this->rating_loader_elements_post($post, $user, $override, "amr");

        if ($this->g->o["cached_loading"] == 1)
            return GDSRRender::rating_loader(join(".", $elements), $this->g->is_bot, "small");
        else
            return $this->render_multi_rating_actual($elements);
    }

    function render_stars_custom_value($settings = array()) {
        $style = $this->g->is_ie6 ? $settings["style_ie6"] : $settings["style"];
        $value = isset($settings["vote"]) ? floatval($settings["vote"]) : 0;
        $star_factor = $settings["star_factor"];
        $stars = $settings["max_value"];
        $size = $settings["size"];

        return GDSRRender::render_static_stars($style, $size, $stars, $value, "", "", $star_factor);
    }

    function render_multi_custom_values($template_id, $multi_set_id, $custom_id, $votes, $header_text = '', $override = array(), $tags_css = array(), $star_factor = 1) {
        $set = gd_get_multi_set($multi_set_id);

        $rd_unit_width = $override["size"];
        $rd_unit_style = $this->g->is_ie6 ? $override["style_ie6"] : $override["style"];
        $rd_unit_width_avg = isset($override["average_size"]) ? $override["average_size"] : $override["style"];
        $rd_unit_style_avg = isset($override["average_stars"]) ? ($this->g->is_ie6 ? $override["average_stars_ie6"] : $override["average_stars"]) : $override["style"];

        return GDSRRenderT2::render_mrb($template_id, array("style" => $rd_unit_style, "allow_vote" => false, "votes" => $votes, "post_id" => $custom_id, "set" => $set, "height" => $rd_unit_width, "header_text" => $header_text, "tags_css" => array("MUR_CSS_BLOCK" => ""), "avg_style" => $rd_unit_style_avg, "avg_size" => $rd_unit_width_avg, "star_factor" => $star_factor));
    }
    // article rendering
}

?>