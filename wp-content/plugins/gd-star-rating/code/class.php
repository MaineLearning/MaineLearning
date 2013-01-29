<?php

/**
* Main plugin class
*/
class GDStarRating {
    var $is_bot = false;
    var $is_ban = false;
    var $is_ie6 = false;
    var $is_cached = false;
    var $is_update = false;

    var $security_level = 'edit_dashboard';
    var $security_level_front = 'delete_posts';
    var $security_level_builder = 'delete_posts';
    var $security_level_setup = 'edit_dashboard';
    var $security_my_ratings = false;
    var $security_my_ratings_level = 'read';
    var $security_users = '0';

    var $is_cached_integration_std = false;
    var $is_cached_integration_mur = false;

    var $use_nonce = true;
    var $extra_folders = false;
    var $safe_mode = false;
    var $widget_post_id;
    var $cats_data_posts = array();
    var $cats_data_cats = array();

    var $wp_secure_level = false;
    var $wpr8_available = false;
    var $admin_plugin = false;
    var $admin_plugin_page = '';
    var $admin_page;
    var $script;
    var $widgets;

    var $active_wp_page;
    var $wp_version;
    var $vote_status;
    var $rendering_sets = null;
    var $override_readonly_standard = false;
    var $override_readonly_multis = false;

    var $tables_list;
    var $plugin_base;
    var $plugin_url;
    var $plugin_ajax;
    var $plugin_path;
    var $plugin_xtra_url;
    var $plugin_xtra_path;
    var $plugin_chart_url;
    var $plugin_chart_path;
    var $plugin_cache_path;
    var $plugin_wpr8_path;
    var $post_comment;
    var $wpr8;

    var $l; // language
    var $o; // options
    var $w; // widget options
    var $p; // post data
    var $i; // import
    var $g; // gfx object
    var $q; // query object
    var $c; // cached post ids
    var $f; // front end rendering object
    var $m; // admin menus object
    var $v; // ajax votes saving object
    var $s; // shared objects functions
    var $qc;
    var $rSnippets;
    var $ginc;
    var $bots;

    var $shortcodes;
    var $stars_sizes;
    var $thumb_sizes;
    var $function_restrict;
    var $default_shortcode_starrating;
    var $default_shortcode_starratingmulti;
    var $default_shortcode_starreviewmulti;
    var $default_shortcode_starcomments;
    var $default_shortcode_starrater;
    var $default_shortcode_starthumbsblock;
    var $default_shortcode_starreview;
    var $default_user_ratings_filter;
    var $default_options;
    var $default_import;
    var $default_widget_comments;
    var $default_widget_top;
    var $default_widget;
    var $default_spider_bots;
    var $default_wpr8;

    /**
    * Constructor method
    */
    function GDStarRating($base_path, $base_file) {
        $this->tabpage = "front";
        $this->plugin_path = $base_path."/";
        $this->plugin_base = $base_file;

        $gdd = new GDSRDefaults();
        $this->default_options = $gdd->default_options;
        $this->shortcodes = $gdd->shortcodes;
        $this->stars_sizes = $gdd->stars_sizes;
        $this->thumb_sizes = $gdd->thumb_sizes;
        $this->tables_list = $gdd->tables_list;
        $this->default_spider_bots = $gdd->default_spider_bots;
        $this->default_wpr8 = $gdd->default_wpr8;
        $this->default_user_ratings_filter = $gdd->default_user_ratings_filter;
        $this->default_import = $gdd->default_import;
        $this->default_widget_comments = $gdd->default_widget_comments;
        $this->default_widget_top = $gdd->default_widget_top;
        $this->default_widget = $gdd->default_widget;
        $this->default_shortcode_starrating = $gdd->default_shortcode_starrating;
        $this->default_shortcode_starratingmulti = $gdd->default_shortcode_starratingmulti;
        $this->default_shortcode_starreviewmulti = $gdd->default_shortcode_starreviewmulti;
        $this->default_shortcode_starcomments = $gdd->default_shortcode_starcomments;
        $this->default_shortcode_starrater = $gdd->default_shortcode_starrater;
        $this->default_shortcode_starthumbsblock = $gdd->default_shortcode_starthumbsblock;
        $this->default_shortcode_starreview = $gdd->default_shortcode_starreview;
        $this->function_restrict = $gdd->function_restrict;

        define("STARRATING_INSTALLED", $this->default_options["version"]." ".$this->default_options["status"]);
        define("STARRATING_EOL", "\r\n");

        $this->c = array();

        $this->plugin_path_url();
        if ($this->wp_version > 29) {
            $this->default_widget["select"] = "post";
        }
        $this->install_plugin();

        if (!GDSR_WP_ADMIN) {
            if (!STARRATING_AJAX) {
                //$google_rspf = isset($this->o["google_rich_snippets_format"]) ? $this->o["google_rich_snippets_format"] : "microformat";
                $this->q = new gdsrQuery();
                $this->rSnippets = new gdGoogleRichSnippetsGDSR("microformat");
            } else {
                $this->v = new gdsrVotes($this);
            }
            $this->f = new gdsrFront($this);
        }

        $this->s = new gdsrShared($this);
        if (!STARRATING_AJAX) {
            $this->actions_filters();
            $this->initialize_security();
        }

        if ($this->o["ajax_jsonp"] == 1) $this->plugin_ajax.= "?callback=?";

        $this->is_cached = $this->o["cache_active"];
        $this->use_nonce = $this->o["use_nonce"] == 1;

        define("STARRATING_VERSION", $this->o["version"].'_'.$this->o["build"]);
        define("STARRATING_DEBUG_ACTIVE", $this->o["debug_active"]);
        define("STARRATING_STARS_GENERATOR", $this->o["gfx_generator_auto"] == 0 ? "DIV" : "GFX");
        define('STARRATING_AJAX_URL', $this->plugin_ajax);
        define('STARRATING_ENCODING', $this->o["encoding"]);
    }

    function get($name) {
        return $this->o[$name];
    }

    function set($name, $value, $save = true) {
        $this->o[$name] = $value;
        if ($save) update_option('gd-star-rating', $this->o);
    }

    /**
     * Initialize security variables based on the gdsr-config.php file
     */
    function initialize_security() {
        if (defined('STARRATING_ACCESS_LEVEL')) $this->security_level = STARRATING_ACCESS_LEVEL;
        if (defined('STARRATING_ACCESS_LEVEL_FRONT')) $this->security_level_front = STARRATING_ACCESS_LEVEL_FRONT;
        if (defined('STARRATING_ACCESS_LEVEL_BUILDER')) $this->security_level_builder = STARRATING_ACCESS_LEVEL_BUILDER;
        if (defined('STARRATING_ACCESS_LEVEL_SETUP')) $this->security_level_setup = STARRATING_ACCESS_LEVEL_SETUP;
        if (defined('STARRATING_ACCESS_MY_RATINGS')) $this->security_my_ratings = STARRATING_ACCESS_MY_RATINGS;
        if (defined('STARRATING_ACCESS_MY_RATINGS_LEVEL')) $this->security_my_ratings_level = STARRATING_ACCESS_MY_RATINGS_LEVEL;
        if (defined('STARRATING_ACCESS_ADMIN_USERIDS')) $this->security_users = STARRATING_ACCESS_ADMIN_USERIDS;
    }

    /**
    * Adds new button to tinyMCE editor toolbar
    *
    * @param mixed $buttons
    */
    function add_tinymce_button($buttons) {
        array_push($buttons, "separator", "StarRating");
        return $buttons;
    }

    /**
    * Adds plugin to tinyMCE editor
    *
    * @param mixed $plugin_array
    */
    function add_tinymce_plugin($plugin_array) {
        $plugin_array['StarRating'] = $this->plugin_url.'tinymce3/plugin.js';
        return $plugin_array;
    }

    // shortcodes
    /**
    * Adds shortcodes into WordPress instance
    *
    * @param string|array $scode one or more shortcode names
    */
    function shortcode_action($scode) {
        $sc_name = $scode;
        $sc_method = "shortcode_".$scode;
        if (is_array($scode)) {
            $sc_name = $scode["name"];
            $sc_method = $scode["method"];
        }
        add_shortcode(strtolower($sc_name), array(&$this, $sc_method));
        add_shortcode(strtoupper($sc_name), array(&$this, $sc_method));
    }

    /**
    * Code for StarRater shortcode implementation
    *
    * @param array $atts
    */
    function shortcode_starrater($atts = array()) {
        return $this->shortcode_starratingblock($atts);
    }

    /**
    * Code for StarThumbsBlock shortcode implementation
    *
    * @param array $atts
    */
    function shortcode_starthumbsblock($atts = array()) {
        global $userdata;
        $user_id = is_object($userdata) ? $userdata->ID : 0;

        $override = shortcode_atts($this->default_shortcode_starthumbsblock, $atts);
        if ($override["post"] == 0) global $post;
        else $post = get_post($override["post"]);

        $this->cache_posts($user_id);
        return $this->f->render_thumb_article($post, $userdata, $override);
    }

    /**
    * Code for StarRatingBlock shortcode implementation
    *
    * @param array $atts
    */
    function shortcode_starratingblock($atts = array()) {
        global $userdata;
        $user_id = is_object($userdata) ? $userdata->ID : 0;
        $this->cache_posts($user_id);

        $override = shortcode_atts($this->default_shortcode_starrater, $atts);
        if ($override["post"] == 0) global $post;
        else $post = get_post($override["post"]);

        return $this->f->render_article($post, $userdata, $override);
    }

    /**
    * Code for StarRating shortcode implementation
    *
    * @param array $atts
    */
    function shortcode_starrating($atts = array()) {
        $sett = shortcode_atts($this->default_shortcode_starrating, $atts);
        return GDSRRenderT2::render_srr($sett);
    }

    /**
    * Code for StarComments shortcode implementation
    *
    * @param array $atts
    */
    function shortcode_starcomments($atts = array()) {
        $sett = shortcode_atts($this->default_shortcode_starcomments, $atts);
        if ($sett["post"] == 0) {
            global $post;
            $sett["post"] = $post->ID;
        } else {
            $post = get_post($sett["post"]);
        }

        $rating = "";
        $sett["comments"] = $post->comment_count;
        if ($post->ID > 0) {
            $rows = gdsrBlgDB::get_comments_aggregation($sett["post"], $sett["show"]);
            $totel_comments = count($rows);
            $total_voters = 0;
            $total_votes = 0;
            $calc_rating = 0;
            foreach ($rows as $row) {
                switch ($sett["show"]) {
                    default:
                    case "total":
                        $total_voters += $row->user_voters + $row->visitor_voters;
                        $total_votes += $row->user_votes + $row->visitor_votes;
                        break;
                    case "users":
                        $total_voters += $row->user_voters;
                        $total_votes += $row->user_votes;
                        break;
                    case "visitors":
                        $total_voters += $row->visitor_voters;
                        $total_votes += $row->visitor_votes;
                        break;
                }
            }
            if ($total_voters > 0) $calc_rating = $total_votes / $total_voters;
            $calc_rating = number_format($calc_rating, 1);
            $rating = GDSRRenderT2::render_car($sett["tpl"], array("votes" => $total_voters, "rating" => $calc_rating, "comments" => $sett["comments"], "star_style" => ($this->is_ie6 ? $this->o["cmm_aggr_style_ie6"] : $this->o["cmm_aggr_style"]), "star_size" => $this->o['cmm_aggr_size'], "star_max" => $this->o["cmm_stars"]));
        }
        return $rating;
    }

    /**
    * Code for StarReview shortcode implementation
    *
    * @param array $atts
    */
    function shortcode_starreview($atts = array()) {
        global $userdata;
        $user_id = is_object($userdata) ? $userdata->ID : 0;
        $this->cache_posts($user_id);

        $sett = shortcode_atts($this->default_shortcode_starreview, $atts);
        if ($sett["post"] == 0) {
            global $post;
            $sett["post"] = $post->ID;
        }

        $star_css = $sett["css"] != "" ? $sett["css"] : $this->o["review_class_block"];
        $star_style = $sett["style"] != "" ? $sett["style"] : $this->o["review_style"];
        $star_style_ie6 = $sett["style_ie6"] != "" ? $sett["style_ie6"] : $this->o["review_style_ie6"];
        $star_size = $sett["size"] != "" ? $sett["size"] : $this->o['review_size'];

        $post_data = wp_gdget_post($sett["post"]);
        $rating = is_object($post_data) ? $post_data->review : -1;
        $rating = $rating < 0 ? 0 : $rating;
        return GDSRRenderT2::render_rsb($sett["tpl"], array("rating" => $rating, "star_style" => $this->is_ie6 ? $star_style_ie6 : $star_style, "star_size" => $star_size, "star_max" => $this->o["review_stars"], "header_text" => $this->o["review_header_text"], "css" => $star_css));
    }

    /**
    * Code for StarReviewMulti shortcode implementation
    *
    * @param array $atts
    */
    function shortcode_starreviewmulti($atts = array()) {
        $settings = shortcode_atts($this->default_shortcode_starreviewmulti, $atts);
        $el_stars = $settings["element_stars"] != "" ? $settings["element_stars"] : $settings["style"];
        $el_size = $settings["element_size"] != "" ? $settings["element_size"] : $settings["size"];
        $post_id = $settings["post"];
        if ($post_id == 0) {
            global $post;
            $post_id = $post->ID;
        }
        $multi_id = $settings["id"] == 0 ? $this->o["mur_review_set"] : $settings["id"];
        $set = gd_get_multi_set($multi_id);
        if ($multi_id > 0 && $post_id > 0) {
            $vote_id = GDSRDBMulti::get_vote($post_id, $multi_id, count($set->object));
            $multi_data = GDSRDBMulti::get_values($vote_id, 'rvw');
            $votes = array();
            foreach ($multi_data as $md) {
                $single_vote = array();
                $single_vote["votes"] = 1;
                $single_vote["score"] = $md->user_votes;
                $single_vote["rating"] = $md->user_votes;
                $votes[] = $single_vote;
            }
            $avg_rating = GDSRDBMulti::get_multi_review_average($vote_id);
            return GDSRRenderT2::render_rmb($settings["tpl"], array("votes" => $votes, "star_factor" => $settings["factor"], "post_id" => $post_id, "set" => $set, "avg_rating" => $avg_rating, "style" => $el_stars, "size" => $el_size, "avg_style" => $settings["average_stars"], "avg_size" => $settings["average_size"]));
        }
        else return '';
    }

    /**
    * Code for StarRatingMulti shortcode implementation
    *
    * @param array $atts
    */
    function shortcode_starratingmulti($atts = array()) {
        if ($this->o["multis_active"] == 1) {
            global $post, $userdata;
            if (!isset($atts["style"]) && isset($atts["element_stars"]) && $atts["element_stars"] != "") $atts["style"] = $atts["element_stars"];
            if (!isset($atts["size"]) && isset($atts["element_size"]) && $atts["element_size"] != 0) $atts["size"] = $atts["element_size"];
            $settings = shortcode_atts($this->default_shortcode_starratingmulti, $atts);
            return $this->f->render_multi_rating($post, $userdata, $settings);
        } else return "";
    }
    // shortcodes

    // various rendering
    /**
     * Renders comment review stars for selected comment
     *
     * @param int $comment_id id of the comment you want displayed
     * @param bool $zero_render if set to false and $value is 0 then nothing will be rendered
     * @param bool $use_default rendering is using default rendering settings
     * @param string $style folder name of the stars set to use
     * @param int $size stars size 12, 20, 30, 46
     * @return string rendered stars for comment review
     */
    function display_comment_review($comment_id, $use_default = true, $style = "oxygen", $size = 20) {
        $review = wp_gdget_comment_review($comment_id);
        if ($review < 1) return "";
        else {
            if ($use_default) {
                $style = ($this->is_ie6 ? $this->o["cmm_review_style_ie6"] : $this->o["cmm_review_style"]);
                $size = $this->o["cmm_review_size"];
            }
            $stars = $this->o["cmm_review_stars"];
            return GDSRRender::render_static_stars($style, $size, $stars, $review);
        }
    }

    /**
     * Renders post review stars for selected post
     *
     * @param int $post_id id for the post you want review displayed
     * @param bool $zero_render if set to false and $value is 0 then nothing will be rendered
     * @param bool $use_default rendering is using default rendering settings
     * @param string $style folder name of the stars set to use
     * @param int $size stars size 12, 20, 30, 46
     * @return string rendered stars for article review
     */
    function display_article_review($post_id, $use_default = true, $style = "oxygen", $size = 20) {
        global $userdata;
        $user_id = is_object($userdata) ? $userdata->ID : 0;
        $this->cache_posts($user_id);

        if ($use_default) {
            $style = ($this->is_ie6 ? $this->o["review_style_ie6"] : $this->o["review_style"]);
            $size = $this->o["review_size"];
        }
        $stars = $this->o["review_stars"];
        $post_data = wp_gdget_post($post_id);
        $review = is_object($post_data) ? $post_data->review : -1;
        if ($review < 0) $review = 0;

        return GDSRRender::render_static_stars($style, $size, $stars, $review);
    }

    /**
     * Renders post review stars for selected post
     *
     * @param int $post_id id for the post you want review displayed
     * @param bool $zero_render if set to false and $value is 0 then nothing will be rendered
     * @param bool $use_default rendering is using default rendering settings
     * @param string $style folder name of the stars set to use
     * @param int $size stars size 12, 20, 30, 46
     * @return string rendered stars for article review
     */
    function display_multis_review($multi_id, $post_id, $use_default = true, $style = "oxygen", $size = 20) {
        if ($use_default) {
            $style = ($this->is_ie6 ? $this->o["review_style_ie6"] : $this->o["review_style"]);
            $size = $this->o["review_size"];
        }
        $set = gd_get_multi_set($multi_id);
        $stars = $set->stars;
        $review = GDSRDBMulti::get_review_avg($multi_id, $post_id);
        if ($review < 0) $review = 0;

        return GDSRRender::render_static_stars($style, $size, $stars, $review);
    }

    /**
     * Renders post rating stars for selected post
     *
     * @param int $post_id id for the post you want rating displayed
     * @param bool $zero_render if set to false and $value is 0 then nothing will be rendered
     * @param bool $use_default rendering is using default rendering settings
     * @param string $style folder name of the stars set to use
     * @param int $size stars size 12, 20, 30, 46
     * @return string rendered stars for article rating
     */
    function display_article_rating($post_id, $use_default = true, $style = "oxygen", $size = 20) {
        global $userdata;
        $user_id = is_object($userdata) ? $userdata->ID : 0;
        $this->cache_posts($user_id);

        if ($use_default) {
            $style = ($this->is_ie6 ? $this->o["style_ie6"] : $this->o["style"]);
            $size = $this->o["size"];
        }
        $stars = $this->o["stars"];
        $rating = $this->get_article_rating_simple($post_id);

        return GDSRRender::render_static_stars($style, $size, $stars, $rating);
    }

    /**
     * Renders single rating stars image with average rating for the multi rating post results from rating or review.
     *
     * @param int $post_id id of the post rating will be attributed to
     * @param bool $review if set to true average of review will be rendered
     * @param array $settings override settings for rendering the block
     */
    function get_multi_average_rendered($post_id, $settings = array()) {
        $sum = $votes = $rating = 0;

        if ($settings["id"] == "") {
            $multi_id = $this->o["mur_review_set"];
        } else {
            $multi_id = $settings["id"];
        }

        $style = isset($settings["style"]) && $settings["style"] != "" ? $settings["style"] : $this->o["mur_style"];
        $style_ie6 = isset($settings["style_ie6"]) && $settings["style_ie6"] != "" ? $settings["style_ie6"] : $this->o["mur_style_ie6"];
        $size = isset($settings["size"]) && $settings["size"] != 0 ? $settings["size"] : $this->o["mur_size"];

        if ($multi_id > 0 && $post_id > 0) {
            $set = gd_get_multi_set($multi_id);
            $data = GDSRDBMulti::get_averages($post_id, $multi_id);

            if ($set != null && is_object($data)) {
                if ($settings["render"] == "review") {
                    $review = GDSRRender::render_static_stars(($this->is_ie6 ? $this->o["mur_style_ie6"] : $this->o["mur_style"]), $this->o['mur_size'], $set->stars, $data->average_review);
                    return $review;
                } else {
                    switch ($settings["show"]) {
                        case "visitors":
                            $rating = $data->average_rating_visitors;
                            break;
                        case "users":
                            $rating = $data->average_rating_users;
                            break;
                        case "total":
                            $sum = $data->average_rating_users * $data->total_votes_users + $data->average_rating_visitors * $data->total_votes_visitors;
                            $votes = $data->total_votes_users + $data->total_votes_visitors;
                            $rating = number_format($votes == 0 ? 0 : $sum / $votes, 1);
                            break;
                    }

                    $rating = GDSRRender::render_static_stars(($this->is_ie6 ? $style_ie6 : $style), $size, $set->stars, $rating);
                    return $rating;
                }
            }
        }

        $max = is_null($set) ? 10 : $set->stars;
        $rating = GDSRRender::render_static_stars(($this->is_ie6 ? $style_ie6 : $style), $size, $max, 0);

        return $rating;
    }
    // various rendering

    // edit boxes
    /**
     * Insert box multi review on post edit panel.
     */
    function editbox_post_mur() {
        global $post;
        gdsr_render_multi_editor(array("post_id" => $post->ID, "admin" => true));
    }

    /**
     * Insert plugin box on post edit panel.
     */
    function editbox_post() {
        global $post;

        $gdsr_options = $this->o;
        $post_id = $post->ID;
        $default = false;

        $countdown_value = $gdsr_options["default_timer_countdown_value"];
        $countdown_type = $gdsr_options["default_timer_countdown_type"];
        $recc_countdown_value = $gdsr_options["default_timer_countdown_value"];
        $recc_countdown_type = $gdsr_options["default_timer_countdown_type"];
        $timer_date_value = $recc_timer_date_value = "";
        if ($post_id == 0) $default = true;
        else {
            $post_data = GDSRDatabase::get_post_edit($post_id);
            if (count($post_data) > 0) {
                $rating = explode(".", strval($post_data->review));
                $rating_decimal = intval($rating[1]);
                $rating = intval($rating[0]);
                $recc_vote_rules = $post_data->recc_rules_articles;
                $recc_moderation_rules = $post_data->recc_moderate_articles;
                $recc_cmm_vote_rules = $post_data->recc_rules_comments;
                $recc_cmm_moderation_rules = $post_data->recc_moderate_comments;
                $recc_timer_restrictions = $post_data->recc_expiry_type;
                if ($recc_timer_restrictions == "T") {
                    $recc_countdown_type = substr($post_data->recc_expiry_value, 0, 1);
                    $recc_countdown_value = substr($post_data->recc_expiry_value, 1);
                } else if ($recc_timer_restrictions == "D") {
                    $recc_timer_date_value = $post_data->recc_expiry_value;
                }

                $vote_rules = $post_data->rules_articles;
                $moderation_rules = $post_data->moderate_articles;
                $cmm_vote_rules = $post_data->rules_comments;
                $cmm_moderation_rules = $post_data->moderate_comments;
                $timer_restrictions = $post_data->expiry_type;
                if ($timer_restrictions == "T") {
                    $countdown_type = substr($post_data->expiry_value, 0, 1);
                    $countdown_value = substr($post_data->expiry_value, 1);
                } else if ($timer_restrictions == "D") {
                    $timer_date_value = $post_data->expiry_value;
                }
            } else $default = true;
        }

        if ($default) {
            $rating_decimal = $rating = -1;

            $recc_vote_rules = $gdsr_options["recc_default_voterules_articles"];
            $recc_moderation_rules = $gdsr_options["recc_default_moderation_articles"];
            $recc_cmm_vote_rules = $gdsr_options["recc_default_voterules_comments"];
            $recc_cmm_moderation_rules = $gdsr_options["recc_default_moderation_comments"];
            $recc_timer_restrictions = $gdsr_options["recc_default_timer_type"];

            $vote_rules = $gdsr_options["default_voterules_articles"];
            $moderation_rules = $gdsr_options["default_moderation_articles"];
            $cmm_vote_rules = $gdsr_options["default_voterules_comments"];
            $cmm_moderation_rules = $gdsr_options["default_moderation_comments"];
            $timer_restrictions = $gdsr_options["default_timer_type"];
        }

        include($this->plugin_path.'integrate/edit.php');
    }
    // edit boxes

    /**
     * Check the user access levels.
     *
     * @global object $userdata Object with user data.
     */
    function check_user_access() {
        global $userdata;

        if ($this->security_users == "0") {
            $this->wp_secure_level = current_user_can('edit_dashboard');
        } else {
            $allowed = explode(",", $this->security_users);

            if (is_array($allowed)) {
                $this->wp_secure_level = in_array($userdata->ID, $allowed);
            } else {
                $this->wp_secure_level = false;
            }
        }
    }

    function meta_boxes_30() {
        global $wp_meta_boxes;
        $post_types = get_post_types(array(), "objects");
        foreach ($post_types as $name => $data) {
            if ($this->o["integrate_post_edit"] == 1) {
                add_meta_box("gdsr-meta-box", "GD Star Rating", array(&$this, 'editbox_post'), $name, "side", "high");
            }
            if ($this->o["integrate_post_edit_mur"] == 1) {
                add_meta_box("gdsr-meta-box-mur", "GD Star Rating: ".__("Multi Ratings Review", "gd-star-rating"), array(&$this, 'editbox_post_mur'), $name, "advanced", "high");
            }
        }
    }

    function meta_boxes_pre30() {
        if ($this->o["integrate_post_edit"] == 1) {
            add_meta_box("gdsr-meta-box", "GD Star Rating", array(&$this, 'editbox_post'), "post", "side", "high");
            add_meta_box("gdsr-meta-box", "GD Star Rating", array(&$this, 'editbox_post'), "page", "side", "high");
        }
        if ($this->o["integrate_post_edit_mur"] == 1) {
            add_meta_box("gdsr-meta-box-mur", "GD Star Rating: ".__("Multi Ratings Review", "gd-star-rating"), array(&$this, 'editbox_post_mur'), "post", "advanced", "high");
            add_meta_box("gdsr-meta-box-mur", "GD Star Rating: ".__("Multi Ratings Review", "gd-star-rating"), array(&$this, 'editbox_post_mur'), "page", "advanced", "high");
        }
    }

    /**
     * WordPress action for adding administration menu items
     */
    function admin_menu() {
        $this->check_user_access();

        if ($this->wp_version < 30) $this->meta_boxes_pre30();
        if ($this->wp_version > 29) $this->meta_boxes_30();

        $this->m = new gdsrMenus($this);

        add_menu_page('GD Star Rating', 'GD Star Rating', $this->security_level_front, $this->plugin_base, array(&$this->m, "star_menu_front"), plugins_url('gd-star-rating/gfx/menu.png'));
        add_submenu_page($this->plugin_base, 'GD Star Rating: '.__("Front Page", "gd-star-rating"), __("Front Page", "gd-star-rating"), $this->security_level_front, $this->plugin_base, array(&$this->m, "star_menu_front"));
        add_submenu_page($this->plugin_base, 'GD Star Rating: '.__("GDSR 2.0", "gd-star-rating"), __("GDSR 2.0", "gd-star-rating"), $this->security_level, "gd-star-rating-gdsr2", array(&$this->m, "star_menu_gdsr2"));

        if ($this->security_my_ratings) {
            add_submenu_page('index.php', 'GD Star Rating: '.__("My Ratings", "gd-star-rating"), __("My Ratings", "gd-star-rating"), $this->security_my_ratings_level, "gd-star-rating-my", array(&$this->m, "star_menu_my"));
        } else {
            add_submenu_page($this->plugin_base, 'GD Star Rating: '.__("My Ratings", "gd-star-rating"), __("My Ratings", "gd-star-rating"), $this->security_level_front, "gd-star-rating-my", array(&$this->m, "star_menu_my"));
        }

        add_submenu_page($this->plugin_base, 'GD Star Rating: '.__("Builder", "gd-star-rating"), __("Builder", "gd-star-rating"), $this->security_level_builder, "gd-star-rating-builder", array(&$this->m, "star_menu_builder"));

        add_submenu_page($this->plugin_base, 'GD Star Rating: '.__("Articles", "gd-star-rating"), __("Articles", "gd-star-rating"), $this->security_level, "gd-star-rating-stats", array(&$this->m, "star_menu_stats"));
        add_submenu_page($this->plugin_base, 'GD Star Rating: '.__("Categories", "gd-star-rating"), __("Categories", "gd-star-rating"), $this->security_level, "gd-star-rating-cats", array(&$this->m, "star_menu_cats"));
        add_submenu_page($this->plugin_base, 'GD Star Rating: '.__("All Users", "gd-star-rating"), __("All Users", "gd-star-rating"), $this->security_level, "gd-star-rating-users", array(&$this->m, "star_menu_users"));

        if ($this->o["multis_active"] == 1) {
            add_submenu_page($this->plugin_base, 'GD Star Rating: '.__("Multi Sets", "gd-star-rating"), __("Multi Sets", "gd-star-rating"), $this->security_level, "gd-star-rating-multi-sets", array(&$this->m, "star_multi_sets"));
        }

        add_submenu_page($this->plugin_base, 'GD Star Rating: '.__("Settings", "gd-star-rating"), __("Settings", "gd-star-rating"), $this->security_level, "gd-star-rating-settings", array(&$this->m, "star_menu_settings"));
        add_submenu_page($this->plugin_base, 'GD Star Rating: '.__("Graphics", "gd-star-rating"), __("Graphics", "gd-star-rating"), $this->security_level, "gd-star-rating-gfx-page", array(&$this->m, "star_menu_gfx"));
        add_submenu_page($this->plugin_base, 'GD Star Rating: '.__("T2 Templates", "gd-star-rating"), __("T2 Templates", "gd-star-rating"), $this->security_level, "gd-star-rating-t2", array(&$this->m, "star_menu_t2"));

        if ($this->o["admin_ips"] == 1) {
            add_submenu_page($this->plugin_base, 'GD Star Rating: '.__("IP's", "gd-star-rating"), __("IP's", "gd-star-rating"), $this->security_level, "gd-star-rating-ips", array(&$this->m, "star_menu_ips"));
        }

        if ($this->o["admin_import"] == 1) {
            add_submenu_page($this->plugin_base, 'GD Star Rating: '.__("Import", "gd-star-rating"), __("Import", "gd-star-rating"), $this->security_level, "gd-star-rating-import", array(&$this->m, "star_menu_import"));
        }

        if ($this->o["admin_export"] == 1) {
            add_submenu_page($this->plugin_base, 'GD Star Rating: '.__("Export", "gd-star-rating"), __("Export", "gd-star-rating"), $this->security_level, "gd-star-rating-export", array(&$this->m, "star_menu_export"));
        }

        $this->custom_actions('admin_menu');

        add_submenu_page($this->plugin_base, 'GD Star Rating: '.__("Tools", "gd-star-rating"), __("Tools", "gd-star-rating"), $this->security_level, "gd-star-rating-tools", array(&$this->m, "star_menu_tools"));
        add_submenu_page($this->plugin_base, 'GD Star Rating: '.__("Setup", "gd-star-rating"), __("Setup", "gd-star-rating"), $this->security_level_setup, "gd-star-rating-setup", array(&$this->m, "star_menu_setup"));
    }

    function load_colorbox() {
        if ($this->wp_version >= 28) {
            wp_enqueue_script('gdsr-colorbox', $this->plugin_url."js/jquery/jquery-colorbox.js", array("jquery"), $this->o["version"], true);
            wp_enqueue_style('gdsr-colorbox', $this->plugin_url."css/jquery/colorbox.css");
        }
    }

    function load_jquery() {
        if ($this->wp_version < 28) {
            wp_enqueue_script('gdsr-jquery-ui', $this->plugin_url."js/jquery/jquery-ui.js", array("jquery"), $this->o["version"], true);
            wp_enqueue_script('gdsr-jquery-ui-tabs', $this->plugin_url."js/jquery/jquery-ui-tabs.js", array("jquery", "gdsr-jquery-ui"), $this->o["version"], true);
            wp_enqueue_style('gdsr-jquery-ui-tabs', $this->plugin_url."css/jquery/ui.tabs.css");
        }
    }

    function load_datepicker() {
        if ($this->wp_version < 28) {
            wp_enqueue_script('gdsr-jquery-datepicker', $this->plugin_url."js/jquery/jquery-ui-datepicker.js", array("jquery", "gdsr-jquery-ui"), $this->o["version"], true);
            wp_enqueue_style('gdsr-jquery-ui-core', $this->plugin_url."css/jquery/ui.core.css");
            wp_enqueue_style('gdsr-jquery-ui-theme', $this->plugin_url."css/jquery/ui.theme.css");
        } else {
            wp_enqueue_script('gdsr-jquery-datepicker', $this->plugin_url."js/jquery/jquery-ui-datepicker-17.js", array("jquery", "jquery-ui-core"), $this->o["version"], true);
            wp_enqueue_style('gdsr-jquery-ui-theme', $this->plugin_url."css/jquery/ui.17.css");
        }

        if(!empty($this->l)) {
            $jsFile = $this->plugin_path.'js/i18n'.($this->wp_version < 28 ? '' : '-17').'/jquery-ui-datepicker-'.$this->l.'.js';
            if (@file_exists($jsFile) && is_readable($jsFile)) {
                $jsUrl = $this->plugin_url.'js/i18n'.($this->wp_version < 28 ? '' : '-17').'/jquery-ui-datepicker-'.$this->l.'.js';
                wp_enqueue_script('gdsr-jquery-datepicker-translation', $jsUrl, array("gdsr-jquery-datepicker"), $this->o["version"], true);
            }
        }
    }

    function load_corrections() {
        wp_enqueue_script('gdsr-js-corrections', $this->plugin_url."js/rating/rating-corrections.js", array(), $this->o["version"], true);
    }

    /**
     * WordPress action for adding administration header contents
     */
    function admin_head() {
        global $parent_file;

        $this->admin_page = $parent_file;
        $datepicker_date = date("Y, n, j");
        $tabs_extras = "";

        if ($this->admin_plugin_page == "ips" && isset($_GET["gdsr"]) && $_GET["gdsr"] == "iplist") {
            $tabs_extras = ", selected: 1";
        }

        if ($this->script == "post.php" || $this->script == "post-new.php" || $this->script == "page.php") {
            echo('<script type="text/javascript" src="'.$this->plugin_url.'js/rating/rating-editors.js"></script>'.STARRATING_EOL);
            $this->include_rating_css_admin();
        }

        if ($this->admin_plugin) {
            wp_admin_css('css/dashboard');
            echo('<link rel="stylesheet" href="'.$this->plugin_url.'css/admin/admin_main.css" type="text/css" media="screen" />'.STARRATING_EOL);
            echo('<script type="text/javascript" src="'.$this->plugin_url.'js/rating/rating-admin.js"></script>'.STARRATING_EOL);
            if ($this->wp_version < 28) {
                echo('<link rel="stylesheet" href="'.$this->plugin_url.'css/admin/admin_wp27.css" type="text/css" media="screen" />'.STARRATING_EOL);
            } else {
                echo('<link rel="stylesheet" href="'.$this->plugin_url.'css/admin/admin_wp28.css" type="text/css" media="screen" />'.STARRATING_EOL);
            }
        }

        echo('<script type="text/javascript">jQuery(document).ready(function() {'.STARRATING_EOL);
            if ($this->admin_plugin) {
                if ($this->wp_version >= 28) {
                    echo('jQuery(".clrboxed").colorbox({width:800, height:470, iframe:true});'.STARRATING_EOL);
                }
                echo('jQuery("#gdsr_tabs'.($this->wp_version < 28 ? ' > ul' : '').'").tabs({fx: {height: "toggle"}'.$tabs_extras.' });'.STARRATING_EOL);
            }
            if ($this->admin_plugin || $this->admin_page == "edit.php" || $this->admin_page == "post-new.php" || $this->admin_page == "themes.php") {
                echo('if (jQuery().datepicker) jQuery("#gdsr_timer_date_value").datepicker({duration: "fast", minDate: new Date('.$datepicker_date.'), dateFormat: "yy-mm-dd"});'.STARRATING_EOL);
            }
            if ($this->admin_plugin_page == "tools") {
                echo('if (jQuery().datepicker) jQuery("#gdsr_lock_date").datepicker({duration: "fast", dateFormat: "yy-mm-dd"});'.STARRATING_EOL);
            }
        echo("});</script>".STARRATING_EOL);

        if ($this->admin_plugin_page == "settings") {
            echo('<script type="text/javascript" src="'.$this->plugin_url.'js/rating/rating-loaders.js"></script>'.STARRATING_EOL);
        }

        if ($this->script == "widgets.php") {
            if ($this->wp_version < 28) {
                echo('<script type="text/javascript" src="'.$this->plugin_url.'js/rating/rating-widgets.js"></script>'.STARRATING_EOL);
            } else if ($this->wp_version > 27) {
                echo('<script type="text/javascript" src="'.$this->plugin_url.'js/rating/rating-widgets-28.js"></script>'.STARRATING_EOL);
            }
            echo('<link rel="stylesheet" href="'.$this->plugin_url.'css/admin/admin_widgets.css" type="text/css" media="screen" />'.STARRATING_EOL);
        }

        $this->custom_actions('admin_head');

        if ($this->admin_plugin_page == "builder") {
            echo('<script type="text/javascript" src="'.$this->plugin_url.'tinymce3/tinymce.js"></script>'.STARRATING_EOL);
        }

        echo('<link rel="stylesheet" href="'.$this->plugin_url.'css/admin/admin_post.css" type="text/css" media="screen" />'.STARRATING_EOL);
    }

    /**
     * WordPress action to get post ID's from active loop
     *
     * @param WP_Query $wpq query object
     * @return WP_Query query object
     */
    function loop_start($wp_query) {
        if (!is_admin()) {
            if ($this->wp_version < 28) global $wp_query;
            if (is_array($wp_query->posts)) {
                foreach ($wp_query->posts as $p) {
                    if (!isset($this->c[$p->ID])) $this->c[$p->ID] = 0;
                }
            }
        }
        if ($this->wp_version >= 28) return $wp_query;
    }

    /**
     * WordPress action to get and cache comments rating data for a post
     *
     * @param array $comments post comments
     * @param int $post_id post id
     * @return array post comments
     */
    function comments_array($comments, $post_id) {
        if (count($comments) > 0 && !is_admin()) {
            if ((is_single() && ($this->o["display_comment"] == 1 || $this->o["thumb_display_comment"] == 1)) ||
                (is_page() && ($this->o["display_comment_page"] == 1 || $this->o["thumb_display_comment_page"] == 1)) ||
                $this->o["override_thumb_display_comment"] == 1 || $this->o["override_display_comment"] == 1) {
                    $this->cache_comments($post_id);
            }
        }
        return $comments;
    }

    /**
     * Adding WordPress action and filter
     */
    function actions_filters() {
        if (GDSR_WP_ADMIN) {
            add_action('admin_menu', array(&$this, 'admin_menu'));
            add_action('admin_head', array(&$this, 'admin_head'));
            add_filter('plugin_action_links', array(&$this, 'plugin_links'), 10, 2 );
            if ($this->o["integrate_post_edit_mur"] == 1 || $this->o["integrate_post_edit"] == 1) {
                add_action('save_post', array(&$this, 'saveedit_post'));
            }
            if ($this->o["integrate_dashboard"] == 1) {
                add_action('wp_dashboard_setup', array(&$this, 'add_dashboard_widget'));
                if (!function_exists('wp_add_dashboard_widget')) add_filter('wp_dashboard_widgets', array(&$this, 'add_dashboard_widget_filter'));
            }
            if ($this->o["integrate_tinymce"] == 1) {
                add_filter("mce_external_plugins", array(&$this, 'add_tinymce_plugin'), 5);
                add_filter('mce_buttons', array(&$this, 'add_tinymce_button'), 5);
            }
        } else {
            add_action('wp_head', array(&$this, 'wp_head'));
            add_action('gdsr_gsr_insert_snippet', array(&$this->f, 'insert_google_rich_snippet'));
            add_filter('query_vars', array($this->q, 'query_vars'));
            add_action('pre_get_posts', array($this->q, 'pre_get_posts'), 10000);
            add_filter('comment_text', array(&$this, 'display_comment'), 10000);
            add_filter('the_content', array(&$this, 'display_article'));
            add_action('loop_start', array(&$this, 'loop_start'));
            add_filter('preprocess_comment', array(&$this, 'comment_read_post'));
            add_filter('comment_post', array(&$this, 'comment_save'));

            if ($this->o["integrate_rss_powered"] == 1 || $this->o["rss_active"] == 1) {
                add_filter('the_excerpt_rss', array(&$this, 'rss_filter'));
                add_filter('the_content_rss', array(&$this, 'rss_filter'));
                add_filter('the_content', array(&$this, 'rss_filter'));
            }
            if ($this->o["cached_loading"] == 0) {
                add_filter('comments_array', array(&$this, 'comments_array'), 10, 2);
            }
        }

        add_action('init', array(&$this, 'init'));
        add_action('widgets_init', array(&$this, 'widgets_init'));

        add_action('delete_comment', array(&$this, 'comment_delete'));
        add_action('delete_post', array(&$this, 'post_delete'));

        foreach ($this->shortcodes as $code) $this->shortcode_action($code);
    }

    /**
     * WordPress widgets init action
     */
    function widgets_init() {
        if ($this->wp_version < 28) {
            $this->widgets = new gdsrWidgets($this->g, $this->default_widget_comments, $this->default_widget_top, $this->default_widget);
            if ($this->o["widget_articles"] == 1) $this->widgets->widget_articles_init();
            if ($this->o["widget_top"] == 1) $this->widgets->widget_top_init();
            if ($this->o["widget_comments"] == 1) $this->widgets->widget_comments_init();
        } else {
            if ($this->o["widget_articles"] == 1) register_widget("gdsrWidgetRating");
            if ($this->o["widget_top"] == 1) register_widget("gdsrWidgetTop");
            if ($this->o["widget_comments"] == 1) register_widget("gdsrWidgetComments");
        }
    }

    /**
     * Adds Settings link to plugins panel grid
     */
    function plugin_links($links, $file) {
        static $this_plugin;
        if (!$this_plugin) $this_plugin = plugin_basename($this->plugin_base);

        if ($file == $this_plugin){
            $settings_link = '<a href="admin.php?page=gd-star-rating-settings">'.__("Settings", "gd-star-rating").'</a>';
            array_unshift($links, $settings_link);
        }
        return $links;
    }

    /**
     * WordPress rss content filter
     */
    function rss_filter($content) {
        if (is_feed()) {
            if ($this->o["rss_active"] == 1) $content.= "<br />".$this->f->render_article_rss();
            if ($this->o["integrate_rss_powered"] == 1) $content.= "<br />".$this->powered_by();
            $content.= "<br />";
        }
        return $content;
    }

    /**
     * Renders tag with link and powered by button
     *
     * @return string rendered content
     */
    function powered_by() {
        return '<a target="_blank" href="http://www.gdstarrating.com/"><img src="'.STARRATING_URL.'gfx/powered.png" border="0" width="80" height="15" /></a>';
    }

    function get_users_votes($user_id, $limit = 100, $filter = array()) {
        $sett = array();
        $sett["integrate_dashboard_latest_count"] = $limit;
        $settings = shortcode_atts($this->default_user_ratings_filter, $filter);

        foreach ($settings as $name => $value) {
            $sett["integrate_dashboard_latest_filter_".$name] = $value;
        }

        return gdsrDB::filter_latest_votes($sett, $user_id);
    }

    function add_dashboard_widget() {
        global $userdata;
        $user_level = intval($userdata->user_level);

        if ($user_level >= intval($this->o["security_showdashboard_user_level"])) {
            if (!function_exists('wp_add_dashboard_widget')) {
                if ($this->o["integrate_dashboard_latest"] == 1)
                    wp_register_sidebar_widget("dashboard_gdstarrating_latest", "GD Star Rating ".__("Latest", "gd-star-rating"), array(&$this, 'display_dashboard_widget_latest'), array('all_link' => get_bloginfo('wpurl').'/wp-admin/admin.php?page=gd-star-rating/gd-star-rating.php', 'width' => 'half', 'height' => 'single'));
            } else {
                if ($this->o["integrate_dashboard_latest"] == 1)
                    wp_add_dashboard_widget("dashboard_gdstarrating_latest", "GD Star Rating ".__("Latest", "gd-star-rating"), array(&$this, 'display_dashboard_widget_latest'));
            }
        }
    }

    function add_dashboard_widget_filter($widgets) {
        global $userdata;
        $user_level = intval($userdata->user_level);

        if ($user_level >= intval($this->o["security_showdashboard_user_level"])) {
            global $wp_registered_widgets;

            if (!isset($wp_registered_widgets["dashboard_gdstarrating_latest"])) return $widgets;
            if ($this->o["integrate_dashboard_latest"] == 1)
                array_splice($widgets, 2, 0, "dashboard_gdstarrating_latest");
        }
        return $widgets;
    }

    function display_dashboard_widget_chart($sidebar_args) {
        if (!function_exists('wp_add_dashboard_widget')) {
            extract($sidebar_args, EXTR_SKIP);
            echo $before_widget.$before_title.$widget_name.$after_title;
        }
        include($this->plugin_path.'integrate/dash_chart.php');
        if (!function_exists('wp_add_dashboard_widget')) echo $after_widget;
    }

    function display_dashboard_widget_latest($sidebar_args) {
        if (!function_exists('wp_add_dashboard_widget')) {
            extract($sidebar_args, EXTR_SKIP);
            echo $before_widget.$before_title.$widget_name.$after_title;
        }
        $o = $this->o;
        include($this->plugin_path.'integrate/dash_latest.php');
        if (!function_exists('wp_add_dashboard_widget')) echo $after_widget;
    }

    function comment_read_post($comment) {
        $this->post_comment["post_id"] = $_POST["comment_post_ID"];
        $this->post_comment["review"] = isset($_POST["gdsr_cmm_value"]) ? intval($_POST["gdsr_cmm_value"]) : -1;
        $this->post_comment["standard_rating"] = isset($_POST["gdsr_int_value"]) ? intval($_POST["gdsr_int_value"]) : -1;
        $this->post_comment["multi_rating"] = isset($_POST["gdsr_mur_value"]) ? $_POST["gdsr_mur_value"] : "";
        $this->post_comment["multi_id"] = isset($_POST["gdsr_mur_set"]) ? intval($_POST["gdsr_mur_set"]) : 0;
        return $comment;
    }

    function comment_save($comment_id) {
        global $userdata;
        $user_id = is_object($userdata) ? $userdata->ID : 0;
        $user = intval($user_id);
        $ip = $_SERVER["REMOTE_ADDR"];

        if ($this->post_comment["review"] > -1) {
            $comment_data = GDSRDatabase::get_comment_data($comment_id);
            if (count($comment_data) == 0) GDSRDatabase::add_empty_comment($comment_id, $this->post_comment["post_id"], $this->post_comment["review"]);
            else GDSRDatabase::save_comment_review($comment_id, $this->post_comment["review"]);
        }

        $std_minimum = $this->o["int_comment_std_zero"] == 1 ? -1 : 0;
        $mur_minimum = $this->o["int_comment_mur_zero"] == 1 ?  0 : 1;
        $id = $this->post_comment["post_id"];

        if ($this->post_comment["standard_rating"] > $std_minimum) {
            $votes = $this->post_comment["standard_rating"];
            $ua = $this->o["save_user_agent"] == 1 && isset($_SERVER["HTTP_USER_AGENT"]) ? $_SERVER["HTTP_USER_AGENT"] : "";
            $allow_vote = true;

            if ($this->o["cmm_integration_prevent_duplicates"] == 1) {
                $allow_vote = intval($votes) <= $this->o["stars"];
                if ($allow_vote) $allow_vote = gdsrFrontHelp::check_cookie($id);
                if ($allow_vote) $allow_vote = gdsrBlgDB::check_vote($id, $user, 'article', $ip, false, false);
            }

            if ($allow_vote) {
                gdsrBlgDB::save_vote($id, $user, $ip, $ua, $votes, $comment_id);
                if ($this->o["cmm_integration_prevent_duplicates"] == 1) gdsrFrontHelp::save_cookie($id);
                do_action("gdsr_vote_rating_article_integrate", $id, $user, $votes);
            }
        }

        if ($this->post_comment["multi_id"] > 0 && $this->post_comment["multi_rating"] != "") {
            $set_id = $this->post_comment["multi_id"];
            $set = gd_get_multi_set($set_id);
            $values = explode("X", $this->post_comment["multi_rating"]);
            $allow_vote = true;
            foreach ($values as $v) {
                if ($v > $set->stars || $v < $mur_minimum) {
                    $allow_vote = false;
                    break;
                }
            }
            if ($this->o["cmm_integration_prevent_duplicates"] == 1) {
                if ($allow_vote) $allow_vote = gdsrFrontHelp::check_cookie($id."#".$set_id, "multis");
                if ($allow_vote) $allow_vote = GDSRDBMulti::check_vote($id, $user, $set_id, 'multis', $ip, false, false);
            }
            if ($allow_vote) {
                $ip = $_SERVER["REMOTE_ADDR"];
                $ua = $this->o["save_user_agent"] == 1 && isset($_SERVER["HTTP_USER_AGENT"]) ? $_SERVER["HTTP_USER_AGENT"] : "";
                $data = GDSRDatabase::get_post_data($id);

                GDSRDBMulti::save_vote($id, $set->multi_id, $user, $ip, $ua, $values, $data, $comment_id);
                GDSRDBMulti::recalculate_multi_averages($id, $set->multi_id, "", $set, true);
                if ($this->o["cmm_integration_prevent_duplicates"] == 1) gdsrFrontHelp::save_cookie($id."#".$set_id, "multis");
                do_action("gdsr_vote_rating_multis_integrate", $id, $user, $set_id, $values);
            }
        }
    }

    function comment_delete($comment_id) {
        GDSRDatabase::delete_by_comment($comment_id);
        GDSRDBMulti::delete_by_comment($comment_id);
    }

    function post_delete($post_id) { }

    /**
     * Triggers saving GD Star Rating data for post.
     *
     * @param int $post_id ID of the post saving
     */
    function saveedit_post($post_id) {
        if (isset($_POST["post_ID"]) && $_POST["post_ID"] > 0)
            $post_id = $_POST["post_ID"];

        if ((isset($_POST['gdsr_post_edit']) && $_POST['gdsr_post_edit'] == "edit") || (isset($_POST['gdsr_post_edit_mur']) && $_POST['gdsr_post_edit_mur'] == "edit")) {
            if ($this->o["integrate_post_edit"] == 1 && isset($_POST["gdsrmultiactive"])) {
                $set_id = intval($_POST["gdsrmultiactive"]);
                if ($set_id > 0) {
                    $mur = $_POST['gdsrmulti'];
                    $mur = isset($mur[$post_id]) ? $mur[$post_id][$set_id] : $mur[0][$set_id];
                    $values = explode("X", $mur);
                    $set = gd_get_multi_set($set_id);
                    $record_id = GDSRDBMulti::get_vote($post_id, $set_id, count($set->object));
                    GDSRDBMulti::save_review($record_id, $values);
                    GDSRDBMulti::recalculate_multi_review($record_id, $values, $set);
                    $this->o["mur_review_set"] = $_POST["gdsrmultiset"];
                    update_option('gd-star-rating', $this->o);
                }
            }

            $old = gdsrAdmDB::check_post_review($post_id);

            $review = $_POST['gdsr_review'];
            if ($_POST['gdsr_review_decimal'] != "-1") $review.= ".".$_POST['gdsr_review_decimal'];
            GDSRDatabase::save_review($post_id, $review, $old);
            $old = true;

            GDSRDatabase::save_article_rules($post_id, 
                isset($_POST['gdsr_vote_articles']) ? $_POST['gdsr_vote_articles'] : "A",
                isset($_POST['gdsr_mod_articles']) ? $_POST['gdsr_mod_articles'] : "N",
                isset($_POST['gdsr_recc_vote_articles']) ? $_POST['gdsr_recc_vote_articles'] : "A",
                isset($_POST['gdsr_recc_mod_articles']) ? $_POST['gdsr_recc_mod_articles'] : "N");

            if ($this->o["comments_active"] == 1) {
                GDSRDatabase::save_comment_rules($post_id, 
                    isset($_POST['gdsr_cmm_vote_articles']) ? $_POST['gdsr_cmm_vote_articles'] : "A",
                    isset($_POST['gdsr_cmm_mod_articles']) ? $_POST['gdsr_cmm_mod_articles'] : "N",
                    isset($_POST['gdsr_recc_cmm_vote_articles']) ? $_POST['gdsr_recc_cmm_vote_articles'] : "A",
                    isset($_POST['gdsr_recc_cmm_mod_articles']) ? $_POST['gdsr_recc_cmm_mod_articles'] : "N");
            }

            if (isset($_POST['gdsr_timer_type'])) {
                $timer = $_POST['gdsr_timer_type'];
                GDSRDatabase::save_timer_rules(
                    $post_id,
                    $timer,
                    GDSRHelper::timer_value($timer,
                        isset($_POST['gdsr_timer_date_value']) ? $_POST['gdsr_timer_date_value'] : "",
                        isset($_POST['gdsr_timer_countdown_value']) ? $_POST['gdsr_timer_countdown_value'] : "",
                        isset($_POST['gdsr_timer_countdown_type']) ? $_POST['gdsr_timer_countdown_type'] : "")
                );
            }
            if (isset($_POST['gdsr_timer_type_recc'])) {
                $timer = $_POST['gdsr_timer_type_recc'];
                GDSRDatabase::save_timer_rules_thumbs(
                    $post_id,
                    $timer,
                    GDSRHelper::timer_value($timer,
                        isset($_POST['gdsr_recc_timer_date_value']) ? $_POST['gdsr_recc_timer_date_value'] : "",
                        isset($_POST['gdsr_recc_timer_countdown_value']) ? $_POST['gdsr_recc_timer_countdown_value'] : "",
                        isset($_POST['gdsr_recc_timer_countdown_type']) ? $_POST['gdsr_recc_timer_countdown_type'] : "")
                );
            }
        }
    }

    /**
     * Main installation method of the plugin
     */
    function install_plugin() {
        $this->o = get_option('gd-star-rating');
        $this->i = get_option('gd-star-rating-import');
        $this->g = get_option('gd-star-rating-gfx');
        $this->wpr8 = get_option('gd-star-rating-wpr8');
        $this->ginc = get_option('gd-star-rating-inc');
        $this->bots = get_option('gd-star-rating-bots');

        if (!STARRATING_AJAX && GDSR_WP_ADMIN) {
            if ($this->o["build"] != $this->default_options["build"] || !is_array($this->o)) {
                if (is_object($this->g)) {
                    $this->g = gdsrAdmFunc::gfx_scan();
                    update_option('gd-star-rating-gfx', $this->g);
                }

                require_once(STARRATING_PATH."/gdragon/gd_db_install.php");

                if ($this->o["build"] < 911)
                    gdDBInstallGDSR::upgrade_collation(STARRATING_PATH);

                gdDBInstallGDSR::delete_tables(STARRATING_PATH);
                gdDBInstallGDSR::delete_columns(STARRATING_PATH);
                gdDBInstallGDSR::create_tables(STARRATING_PATH);
                gdDBInstallGDSR::upgrade_tables(STARRATING_PATH);
                gdDBInstallGDSR::alter_tables(STARRATING_PATH);
                gdDBInstallGDSR::alter_index(STARRATING_PATH);
                $this->o["database_upgrade"] = date("r");

                gdsrAdmDB::install_all_templates();

                $this->o = gdFunctionsGDSR::upgrade_settings($this->o, $this->default_options);

                $this->o["css_last_changed"] = time();
                $this->o["version"] = $this->default_options["version"];
                $this->o["code_name"] = $this->default_options["code_name"];
                $this->o["date"] = $this->default_options["date"];
                $this->o["status"] = $this->default_options["status"];
                $this->o["build"] = $this->default_options["build"];
                $this->o["revision"] = $this->default_options["revision"];

                $this->is_update = true;
                update_option('gd-star-rating', $this->o);
            }

            if (!is_array($this->o)) {
                update_option('gd-star-rating', $this->default_options);
                $this->o = get_option('gd-star-rating');
                gdDBInstallGDSR::create_tables(STARRATING_PATH);
            }

            if (!is_array($this->i)) {
                update_option('gd-star-rating-import', $this->default_import);
                $this->i = get_option('gd-star-rating-import');
            } else {
                $this->i = gdFunctionsGDSR::upgrade_settings($this->i, $this->default_import);
                update_option('gd-star-rating-import', $this->i);
            }

            if (!is_object($this->g)) {
                $this->g = gdsrAdmFunc::gfx_scan();
                update_option('gd-star-rating-gfx', $this->g);
            }

            if (!is_array($this->wpr8)) {
                update_option('gd-star-rating-wpr8', $this->default_wpr8);
                $this->wpr8 = get_option('gd-star-rating-wpr8');
            } else {
                $this->wpr8 = gdFunctionsGDSR::upgrade_settings($this->wpr8, $this->default_wpr8);
                update_option('gd-star-rating-wpr8', $this->wpr8);
            }

            if (!is_array($this->bots)) {
                $this->bots = $this->default_spider_bots;
                update_option('gd-star-rating-bots', $this->bots);
            }

            if (!is_array($this->ginc)) {
                $this->ginc = array();
                $this->ginc[] = $this->stars_sizes;
                $this->ginc[] = $this->g->get_list(true);
                $this->ginc[] = $this->thumb_sizes;
                $this->ginc[] = $this->g->get_list(false);
                update_option('gd-star-rating-inc', $this->ginc);
            }

            if (count($this->ginc) == 2) {
                $this->ginc[] = $this->thumb_sizes;
                $this->ginc[] = $this->g->get_list(false);
                update_option('gd-star-rating-inc', $this->ginc);
            }
        }

        $this->script = basename($_SERVER["PHP_SELF"]);
    }

    /**
     * Calculates all needed paths and sets them as constants.
     *
     * @global string $wp_version wordpress version
     */
    function plugin_path_url() {
        global $wp_version;
        $this->wp_version = substr(str_replace('.', '', $wp_version), 0, 2);

        $this->plugin_url = plugins_url('/gd-star-rating/');
        $this->plugin_xtra_url = content_url('/gd-star-rating/');
        $this->plugin_xtra_path = WP_CONTENT_DIR.'/gd-star-rating/';
        $this->plugin_cache_path = $this->plugin_xtra_path."cache/";
        $this->plugin_ajax = $this->plugin_url.'ajax.php';

        $this->plugin_chart_path = $this->plugin_path."options/charts/";
        $this->plugin_chart_url = $this->plugin_url."options/charts/";

        define('STARRATING_URL', $this->plugin_url);
        define('STARRATING_PATH', $this->plugin_path);
        define('STARRATING_XTRA_URL', $this->plugin_xtra_url);
        define('STARRATING_XTRA_PATH', $this->plugin_xtra_path);
        define('STARRATING_CACHE_PATH', $this->plugin_cache_path);

        define('STARRATING_CHART_URL', $this->plugin_chart_url);
        define('STARRATING_CHART_PATH', $this->plugin_chart_path);
    }

    /**
     * Executes attached hook actions methods for plugin internal actions.
     * - init: executed after init method
     *
     * @param <type> $action name of the plugin action
     */
    function custom_actions($action) {
        do_action('gdsr_'.$action);
    }

    /**
     * Main init method executed as wordpress action 'init'.
     */
    function init() {
        $this->is_ie6 = $this->o["disable_ie6_check"] == 1 ? false : is_msie6();
        if ($this->is_update) GDSRDatabase::init_categories_data();
        if (is_admin()) {
            gdsrAdmFunc::init_uninstall();
            gdsrAdmFunc::init_templates();
            $this->init_operations();
            $this->load_translation();
        }

        wp_enqueue_script('jquery');

        if (!is_admin()) {
            if (isset($_SERVER['HTTP_USER_AGENT'])) {
                $this->is_bot = gdsrFrontHelp::detect_bot($_SERVER['HTTP_USER_AGENT'], $this->bots);
            } else {
                $this->is_bot = FALSE;
            }

            $this->is_ban = gdsrFrontHelp::detect_ban();

            if ($this->o["cached_loading"] != 1) {
                $this->f->render_wait_article();
                $this->f->render_wait_comment();
                $this->f->render_wait_multis();
                $this->f->render_wait_article_thumb();
                $this->f->render_wait_comment_thumb();
            }

            $js_name = STARRATING_JAVASCRIPT_DEBUG ? 'gd-star-rating/js/gdsr.debug.js' : 'gd-star-rating/js/gdsr.js';
            wp_enqueue_script("gdsr_script", plugins_url($js_name), array(), $this->o["version"]);
            if ($this->o["external_rating_css"] == 1) {
                wp_enqueue_style("gdsr_style_main", $this->include_rating_css(true, true), array(), $this->o["version"]);
            }
            if ($this->o["external_css"] == 1 && file_exists($this->plugin_xtra_path."css/rating.css")) {
                wp_enqueue_style("gdsr_style_xtra", $this->plugin_xtra_url."css/rating.css", array(), $this->o["version"]);
            }
        } else {
            if (isset($_GET["page"])) {
                if (substr($_GET["page"], 0, 14) == "gd-star-rating") {
                    $this->admin_plugin = true;
                    $this->admin_plugin_page = substr($_GET["page"], 15);
                } else {
                    $this->admin_plugin = false;
                }
            }

            if ($this->admin_plugin) {
                if ($this->wp_version >= 28) {
                    wp_enqueue_script('jquery-ui-core');
                    wp_enqueue_script('jquery-ui-tabs');
                }
                $this->load_datepicker();
            }

            $this->cache_cleanup();
            $this->init_specific_pages();
        }

        if (is_admin() && $this->o["mur_review_set"] == 0) {
            $set = GDSRDBMulti::get_multis(0, 1);
            if (count($set) > 0) {
                $this->o["mur_review_set"] = $set[0]->multi_id;
                update_option('gd-star-rating', $this->o);
            }
        }

        $this->custom_actions('init');
    }

    /**
     * Initialization of plugin panels
     */
    function init_specific_pages() {
        if ($this->admin_plugin_page == "settings" && isset($_POST['gdsr_action']) && $_POST['gdsr_action'] == 'save') {
            $gdsr_options = $this->o;
            include ($this->plugin_path."code/adm/save_settings.php");
            $this->o = $gdsr_options;
        }

        if ($this->admin_plugin_page == "gfx-page") {
            $gdsr_options = $this->o;
            $ginc = $this->ginc;
            $ginc_sizes = $this->ginc[0];
            $ginc_stars = $this->ginc[1];
            $ginc_sizes_thumb = $this->ginc[2];
            $ginc_stars_thumb = $this->ginc[3];
            include ($this->plugin_path."code/adm/save_gfx.php");
            $this->o = $gdsr_options;
            $this->ginc = $ginc;
        }

        if ($this->admin_plugin_page == "multi-sets" ||
            $this->admin_plugin_page == "t2") $this->load_corrections();

        if ($this->admin_plugin) {
            $this->load_colorbox();
            $this->load_jquery();
            $this->safe_mode = gdFunctionsGDSR::php_in_safe_mode();
            if (!$this->safe_mode)
                $this->extra_folders = $this->o["cache_forced"] == 1 || GDSRHelper::create_folders($this->wp_version);
        }
    }

    /**
     * Loads plugin translation file.
     */
    function load_translation() {
        $this->l = get_locale();
        if(!empty($this->l)) {
            $moFile = $this->plugin_path."/languages/gd-star-rating-".$this->l.".mo";
            if (@file_exists($moFile) && is_readable($moFile)) load_textdomain('gd-star-rating', $moFile);
        }
    }

    function wp_head_javascript() {
        echo '<script type="text/javascript">'.STARRATING_EOL;
        echo '//<![CDATA['.STARRATING_EOL;
        echo 'var gdsr_cnst_nonce = "'.wp_create_nonce('gdsr_ajax_r8').'";'.STARRATING_EOL;
        echo 'var gdsr_cnst_ajax = "'.STARRATING_AJAX_URL.'";'.STARRATING_EOL;
        echo 'var gdsr_cnst_button = '.$this->o["mur_button_active"].';'.STARRATING_EOL;
        echo 'var gdsr_cnst_cache = '.$this->o["cached_loading"].';'.STARRATING_EOL;
        if ($this->o["cmm_integration_replay_hide_review"]) {
            echo 'jQuery(document).ready(function() { jQuery(".comment-reply-link").click(function() { hideshowCmmInt(); }); });'.STARRATING_EOL;
        }
        echo '// ]]>'.STARRATING_EOL;
        echo '</script>'.STARRATING_EOL;
    }

    /**
     * WordPress action for adding blog header contents
     */
    function wp_head() {
        $this->f->init_google_rich_snippet();

        if (is_feed()) return;
        $this->wp_head_javascript();

        $include_cmm_review = $this->o["comments_review_active"] == 1;
        $include_mur_rating = $this->o["multis_active"] == 1;

        if ($this->o["external_rating_css"] == 0) $this->include_rating_css(false);

        if ($this->o["debug_wpquery"] == 1) {
            global $wp_query;
            wp_gdsr_dump("WP_QUERY", $wp_query->request);
        }

        $this->custom_actions('wp_head');
        if ($this->o["ie_opacity_fix"] == 1) gdsrFrontHelp::ie_opacity_fix();
    }

    /**
     * Prepare multi sets for rendering.
     */
    function prepare_multiset() {
        $this->rendering_sets = GDSRDBMulti::get_multisets_for_auto_insert();
        if (!is_array($this->rendering_sets)) $this->rendering_sets = array();
    }

    function init_operations() {
        $msg = "";
        if (isset($_POST["gdsr_multi_review_form"]) && $_POST["gdsr_multi_review_form"] == "review") {
            $mur_all = $_POST['gdsrmulti'];
            foreach ($mur_all as $post_id => $data) {
                if ($post_id > 0) {
                    foreach ($data as $set_id => $mur) {
                        $set = gd_get_multi_set($set_id);
                        $values = explode("X", $mur);
                        $record_id = GDSRDBMulti::get_vote($post_id, $set_id, count($set->object));
                        GDSRDBMulti::save_review($record_id, $values);
                        GDSRDBMulti::recalculate_multi_review($record_id, $values, $set);
                    }
                }
            }
            $this->custom_actions('init_save_review');
            wp_redirect_self();
            exit;
        }

        if (isset($_POST["gdsr_editcss_rating"])) {
            $rating_css = STARRATING_XTRA_PATH."css/rating.css";
            if (is_writeable($rating_css)) {
                $newcontent = stripslashes($_POST['gdsr_editcss_contents']);
                $f = fopen($rating_css, 'w+');
                fwrite($f, $newcontent);
                fclose($f);
            }
            wp_redirect_self();
            exit;
        }

        if (isset($_POST['gdsr_debug_clean'])) {
            wp_gdsr_debug_clean();
            wp_redirect_self();
            exit;
        }

        if (isset($_POST['gdsr_cache_clean'])) {
            GDSRHelper::clean_cache(substr(STARRATING_CACHE_PATH, 0, strlen(STARRATING_CACHE_PATH) - 1));
            $this->o["cache_cleanup_last"] = date("r");
            update_option('gd-star-rating', $this->o);
            wp_redirect_self();
            exit;
        }

        if (isset($_POST['gdsr_preview_scan'])) {
            $this->g = gdsrAdmFunc::gfx_scan();
            update_option('gd-star-rating-gfx', $this->g);
            wp_redirect_self();
            exit;
        }

        if (isset($_POST['gdsr_t2_import'])) {
            gdsrAdmDB::insert_extras_templates(STARRATING_XTRA_PATH, false);
            wp_redirect_self();
            exit;
        }

        if (isset($_POST['gdsr_reset_db_tool'])) {
            gdsrAdmDB::reset_db_tool();
            gdsrAdmDBMulti::reset_db_tool();
            wp_redirect_self();
            exit;
        }

        if (isset($_POST['gdsr_upgrade_tool'])) {
            require_once(STARRATING_PATH."/gdragon/gd_db_install.php");

            gdDBInstallGDSR::delete_tables(STARRATING_PATH);
            gdDBInstallGDSR::create_tables(STARRATING_PATH);
            gdDBInstallGDSR::upgrade_tables(STARRATING_PATH);
            gdDBInstallGDSR::alter_tables(STARRATING_PATH);
            gdDBInstallGDSR::alter_tables(STARRATING_PATH, "idx.txt");
            $this->o["database_upgrade"] = date("r");
            update_option('gd-star-rating', $this->o);
            wp_redirect_self();
            exit;
        }

        if (isset($_POST['gdsr_updatemultilog_tool'])) {
            GDSRDBMulti::recalculate_multi_rating_log();
            wp_redirect_self();
            exit;
        }

        if (isset($_POST['gdsr_mulitrecalc_tool'])) {
            $set_id = $_POST['gdsr_mulitrecalc_set'];
            if ($set_id > 0) GDSRDBMulti::recalculate_set($set_id);
            else GDSRDBMulti::recalculate_all_sets();
            wp_redirect_self();
            exit;
        }

        if (isset($_POST['gdsr_cleanup_tool'])) {
            if (isset($_POST['gdsr_tools_clean_invalid_log'])) {
                $count = gdsrTlsDB::clean_invalid_log_articles();
                if ($count > 0) $msg.= $count." ".__("articles records from log table removed.", "gd-star-rating")." ";
                $count = gdsrTlsDB::clean_invalid_log_comments();
                if ($count > 0) $msg.= $count." ".__("comments records from log table removed.", "gd-star-rating")." ";
            }
            if (isset($_POST['gdsr_tools_clean_invalid_trend'])) {
                $count = gdsrTlsDB::clean_invalid_trend_articles();
                if ($count > 0) $msg.= $count." ".__("articles records from trends log table removed.", "gd-star-rating")." ";
                $count = gdsrTlsDB::clean_invalid_trend_comments();
                if ($count > 0) $msg.= $count." ".__("comments records from trends log table removed.", "gd-star-rating")." ";
            }
            if (isset($_POST['gdsr_tools_clean_old_posts'])) {
                $count = gdsrTlsDB::clean_dead_articles();
                if ($count > 0) $msg.= $count." ".__("dead articles records from articles table.", "gd-star-rating")." ";
                $count = gdsrTlsDB::clean_revision_articles();
                if ($count > 0) $msg.= $count." ".__("post revisions records from articles table.", "gd-star-rating")." ";
                $count = gdsrTlsDB::clean_dead_comments();
                if ($count > 0) $msg.= $count." ".__("dead comments records from comments table.", "gd-star-rating")." ";
            }
            if (isset($_POST['gdsr_tools_clean_old_posts'])) {
                $count = GDSRDBMulti::clean_dead_articles();
                if ($count > 0) $msg.= $count." ".__("dead articles records from multi ratings tables.", "gd-star-rating")." ";
                $count = GDSRDBMulti::clean_revision_articles();
                if ($count > 0) $msg.= $count." ".__("post revisions records from multi ratings tables.", "gd-star-rating")." ";
            }
            $this->o["database_cleanup"] = date("r");
            $this->o["database_cleanup_msg"] = $msg;
            update_option('gd-star-rating', $this->o);
            wp_redirect_self();
            exit;
        }

        if (isset($_POST['gdsr_post_lock'])) {
            $lock_date = $_POST['gdsr_lock_date'];
            gdsrAdmDB::lock_post_massive($lock_date);
            $this->o["mass_lock"] = $lock_date;
            update_option('gd-star-rating', $this->o);
            wp_redirect_self();
            exit;
        }

        if (isset($_POST['gdsr_rules_set'])) {
            gdsrAdmDB::update_settings_full($_POST["gdsr_article_moderation"], $_POST["gdsr_article_voterules"], 
                    $_POST["gdsr_comments_moderation"], $_POST["gdsr_comments_voterules"],
                    $_POST["gdsr_artthumb_moderation"], $_POST["gdsr_artthumb_voterules"],
                    $_POST["gdsr_cmmthumbs_moderation"], $_POST["gdsr_cmmthumbs_voterules"]);
            wp_redirect_self();
            exit;
        }
    }

    function init_post_categories_data($post_id) {
        if (!isset($this->cats_data_posts[$post_id]) || (isset($this->cats_data_posts[$post_id]) && count($this->cats_data_posts[$post_id]) == 0)) {
            $cats = wp_get_post_categories($post_id);
            $this->cats_data_posts[$post_id] = GDSRDatabase::get_categories_data($cats);
        }
    }

    function init_cats_categories_data($cat_id) {
        if (!isset($this->cats_data_cats[$cat_id]) || (isset($this->cats_data_cats[$cat_id]) && count($this->cats_data_cats[$cat_id]) == 0)) {
            $this->cats_data_cats[$cat_id] = GDSRDatabase::get_categories_data(array($cat_id));
        }
    }

    function get_article_rating_simple($post_id) {
        $rating = 0;

        list($votes, $score) = $this->get_article_rating($post_id);
        if ($votes > 0) $rating = $score / $votes;

        $rating = @number_format($rating, 1);
        return $rating;
    }

    function get_article_rating($post_id, $is_page = '') {
        $post_data = wp_gdget_post($post_id);
        if (count($post_data) == 0) {
            GDSRDatabase::add_default_vote($post_id, $is_page);
            $post_data = wp_gdget_post($post_id);
        }

        $votes = $score = 0;

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

        return array($votes, $score);
    }

    function get_post_rule_value($post_id, $rule = "rules_articles", $default = "default_voterules_articles") {
        $this->init_post_categories_data($post_id);

        $prn = 0;
        $value = "";
        foreach ($this->cats_data_posts[$post_id] as $cat) {
            if ($cat->parent > 0 && $prn == 0) $prn = $cat->parent;
            if ($cat->$rule != "" && $value == "") $value = $cat->$rule;
            if ($value != "" || ($value != "" && $prn > 0)) break;
        }

        if ($value != "P") return $value;
        if ($prn > 0) {
            $value = $this->get_post_rule_value_recursion($prn, $rule);
            if ($value != "P" && $value != "") return $value;
        }
        return $this->o[$default];
    }

    function get_post_rule_value_recursion($cat_id, $rule = "rules_articles") {
        $this->init_cats_categories_data($cat_id);

        if (count($this->cats_data_cats[$cat_id]) == 0) return 0;
        $cat = $this->cats_data_cats[$cat_id][0];
        if ($cat->$rule != "P" && $cat->$rule != "") return $cat->$rule;
        if ($cat->parent > 0) return $this->get_post_rule_value_recursion($cat->parent, $rule);
        return "";
    }

    function check_integration_std($post_id) {
        $post_data = wp_gdget_post($post_id);
        if (is_object($post_data)) {
            if ($post_data->cmm_integration_std == "N") return false;
            else if ($post_data->cmm_integration_std == "A") return true;
        }

        $this->init_post_categories_data($post_id);

        foreach ($this->cats_data_posts[$post_id] as $cat) {
            if ($cat->cmm_integration_std == "N") return false;
        }

        return true;
    }

    function get_multi_set($post_id) {
        $post_data = wp_gdget_post($post_id);
        if (is_object($post_data)) {
            if ($post_data->cmm_integration_mur == "N") return 0;
            else if ($post_data->cmm_integration_mur == "A") return $post_data->cmm_integration_set;
        }

        $this->init_post_categories_data($post_id);

        $set = $prn = 0;
        foreach ($this->cats_data_posts[$post_id] as $cat) {
            if ($cat->cmm_integration_mur == "N") return 0;
            if ($cat->parent > 0 && $prn == 0) $prn = $cat->parent;
            if ($cat->cmm_integration_set > 0 && $set == 0) $set = $cat->cmm_integration_set;
            if ($set > 0 || ($set > 0 && $prn > 0)) break;
        }

        if ($set > 0) return $set;
        if ($prn > 0) {
            $set = $this->get_multi_set_recursion($prn);
            if ($set > 0) return $set;
            $first = GDSRDBMulti::get_first_multi_set();
            return $first->multi_id;
        } else return 0;
    }

    function get_multi_set_recursion($cat_id) {
        $this->init_cats_categories_data($cat_id);

        if (count($this->cats_data_cats[$cat_id]) == 0) return 0;
        $cat = $this->cats_data_cats[$cat_id][0];
        if ($cat->cmm_integration_set > 0) return $cat->cmm_integration_set;
        if ($cat->parent > 0) return $this->get_multi_set_recursion($cat->parent);
        return 0;
    }

    function include_rating_css_admin() {
        $elements = array();
        $presizes = "m".gdFunctionsGDSR::prefill_zeros(20, 2);
        $sizes = array(20);
        $elements[] = $presizes;
        $elements[] = join("", $sizes);
        $elements[] = join("", $sizes);
        $elements[] = "s1poxygen";
        $elements[] = "t1pstarrating";
        $q = join("#", $elements);
        $t = $this->o["css_cache_active"] == 1 ? $this->o["css_last_changed"] : 0;
        $opacity = $this->o["include_opacity"] == 1 ? "on" : "off";
        $url = $this->plugin_url.'css/gdsr.css.php?t='.urlencode($t).'&amp;s='.urlencode($q).'&amp;o='.urlencode($opacity);
        echo('<link rel="stylesheet" href="'.$url.'" type="text/css" media="screen" />');
    }

    function include_rating_css($external = true, $return = false) {
        $star_sizes = $thumb_sizes = $elements = $loaders = array();

        $presizes = "a".gdFunctionsGDSR::prefill_zeros($this->o["stars"], 2);
        $presizes.= "i".gdFunctionsGDSR::prefill_zeros($this->o["stars"], 2);
        $presizes.= "m".gdFunctionsGDSR::prefill_zeros(20, 2);
        $presizes.= "k".gdFunctionsGDSR::prefill_zeros(20, 2);
        $presizes.= "c".gdFunctionsGDSR::prefill_zeros($this->o["cmm_stars"], 2);
        $presizes.= "r".gdFunctionsGDSR::prefill_zeros($this->o["cmm_review_stars"], 2);
        $elements[] = $presizes;

        foreach ($this->ginc[0] as $size => $var) {
            if ($var == 1) $star_sizes[] = $size;
        }
        if (count($star_sizes) == 0) $star_sizes[] = 24;
        $elements[] = join("", $star_sizes);

        foreach ($this->ginc[2] as $size => $var) {
            if ($var == 1) $thumb_sizes[] = $size;
        }
        if (count($thumb_sizes) == 0) $thumb_sizes[] = 24;
        $elements[] = join("", $thumb_sizes);

        if (!is_array($this->ginc[1])) $elements[] = "spstarrating";
        else {
            foreach($this->g->stars as $s) {
                if (in_array($s->folder, $this->ginc[1]))
                    $elements[] = "s".$s->primary.substr($s->type, 0, 1).$s->folder;
            }
        }

        if (!is_array($this->ginc[3])) $elements[] = "tpstarrating";
        else {
            foreach($this->g->thumbs as $s) {
                if (in_array($s->folder, $this->ginc[3]))
                    $elements[] = "t".$s->primary.substr($s->type, 0, 1).$s->folder;
            }
        }
        $loaders[] = $this->o["wait_loader_artthumb"];
        $loaders[] = $this->o["wait_loader_cmmthumb"];
        $loaders[] = $this->o["wait_loader_article"];
        $loaders[] = $this->o["wait_loader_comment"];
        $loaders[] = $this->o["wait_loader_multis"];
        $loaders = array_unique($loaders);
        foreach ($loaders as $l) $elements[] = "lsg".$l;

        $q = join("#", $elements);
        $t = $this->o["css_cache_active"] == 1 ? $this->o["css_last_changed"] : 0;
        $opacity = $this->o["include_opacity"] == 1 ? "on" : "off";
        if ($external) {
            $url = $this->plugin_url.'css/gdsr.css.php?t='.urlencode($t).'&amp;s='.urlencode($q).'&amp;o='.urlencode($opacity);
            if ($return) return $url;
            else echo('<link rel="stylesheet" href="'.$url.'" type="text/css" media="screen" />');
        } else {
            echo('<style type="text/css" media="screen">');
            $inclusion = "internal";
            $base_url_local = $this->plugin_url;
            $base_url_extra = $this->plugin_xtra_url;
            include ($this->plugin_path."css/gdsr.css.php");
            echo('</style>');
        }
    }

    function multi_rating_header($external_css = true) {
        $this->include_rating_css($external_css);

        $js_name = STARRATING_JAVASCRIPT_DEBUG ? 'js/gdsr.debug.js' : 'js/gdsr.js';
        echo('<script type="text/javascript" src="'.$this->plugin_url.$js_name.'"></script>');
    }

    /**
    * Calculates Bayesian Estimate Mean value for given number of votes and rating
    *
    * @param int $v number of votes
    * @param decimal $R rating value
    * @param int $s maximal rating
    * @return decimal Bayesian rating value
    */
    function bayesian_estimate($v, $R, $s) {
        $m = $this->o["bayesian_minimal"];
        $C = ($this->o["bayesian_mean"] / 100) * $s;

        $WR = ($v / ($v + $m)) * $R + ($m / ($v + $m)) * $C;
        return @number_format($WR, 1);
    }

    // display
    function display_comment($content) {
        global $post, $comment, $userdata;

        if (is_admin() || !is_object($comment) || $comment->comment_type == "pingback") return $content;

        if (!is_feed()) {
            if ((is_single() && $this->o["display_comment"] == 1) ||
                (is_page() && $this->o["display_comment_page"] == 1) ||
                $this->o["override_display_comment"] == 1
            ) {
                $rendered = $this->f->render_comment($post, $comment, $userdata);
                if ($this->o["auto_display_comment_position"] == "top" || $this->o["auto_display_comment_position"] == "both")
                    $content = $rendered.$content;
                if ($this->o["auto_display_comment_position"] == "bottom" || $this->o["auto_display_comment_position"] == "both")
                    $content = $content.$rendered;
            }

            if ($this->o["thumbs_active"] == 1) {
                if ((is_single() && $this->o["thumb_display_comment"] == 1) ||
                    (is_page() && $this->o["thumb_display_comment_page"] == 1) ||
                    $this->o["override_thumb_display_comment"] == 1
                ) {
                    $rendered = $this->f->render_thumb_comment($post, $comment, $userdata);
                    if ($this->o["thumb_auto_display_comment_position"] == "top" || $this->o["thumb_auto_display_comment_position"] == "both")
                        $content = $rendered.$content;
                    if ($this->o["thumb_auto_display_comment_position"] == "bottom" || $this->o["thumb_auto_display_comment_position"] == "both")
                        $content = $content.$rendered;
                }
            }
        }

        return $content;
    }

    function check_backtrace_access() {
        $back_trace = gdFunctionsGDSR::get_caller_backtrace();
        foreach($this->function_restrict as $fr) {
            if (in_array($fr, $back_trace)) return true;
        }
        return false;
    }

    function display_article($content) {
        if (is_admin() || $this->check_backtrace_access()) return $content;

        global $post, $userdata;
        $post_id = is_object($post) ? $post->ID : 0;
        if ($post_id == 0) return $content;

        $user_id = is_object($userdata) ? $userdata->ID : 0;

        if (!is_feed()) {
            if (is_single() || is_page()) {
                gdsrBlgDB::add_new_view($post_id);
                $this->widget_post_id = $post_id;
            }

            // standard rating
            if ((is_single() && $this->o["display_posts"] == 1) ||
                (is_page() && $this->o["display_pages"] == 1) ||
                (is_home() && $this->o["display_home"] == 1) ||
                (is_archive() && $this->o["display_archive"] == 1) ||
                (is_search() && $this->o["display_search"] == 1)
            ) {
                if ($this->o["cached_loading"] == 0) $this->cache_posts($user_id);
                $rendered = $this->f->render_article($post, $userdata);
                if ($this->o["auto_display_position"] == "top" || $this->o["auto_display_position"] == "both")
                    $content = $rendered.$content;
                if ($this->o["auto_display_position"] == "bottom" || $this->o["auto_display_position"] == "both")
                    $content = $content.$rendered;
            }

            // thumbs rating
            if ($this->o["thumbs_active"] == 1) {
                if ((is_single() && $this->o["thumb_display_posts"] == 1) ||
                    (is_page() && $this->o["thumb_display_pages"] == 1) ||
                    (is_home() && $this->o["thumb_display_home"] == 1) ||
                    (is_archive() && $this->o["thumb_display_archive"] == 1) ||
                    (is_search() && $this->o["thumb_display_search"] == 1)
                ) {
                    if ($this->o["cached_loading"] == 0) $this->cache_posts($user_id);
                    $rendered = $this->f->render_thumb_article($post, $userdata);
                    if ($this->o["thumb_auto_display_position"] == "top" || $this->o["thumb_auto_display_position"] == "both")
                        $content = $rendered.$content;
                    if ($this->o["thumb_auto_display_position"] == "bottom" || $this->o["thumb_auto_display_position"] == "both")
                        $content = $content.$rendered;
                }
            }

            // multis rating
            if ($this->o["multis_active"] && (is_single() || is_page())) {
                $this->prepare_multiset();
                if ($this->o["cached_loading"] == 0) $this->cache_posts($user_id);
                $content = $this->display_multi_rating("top", $post, $userdata).$content;
                $content = $content.$this->display_multi_rating("bottom", $post, $userdata);
            }
        }

        $rich_snippet = $this->f->gsr;
        if ($this->o["google_rich_snippets_location"] == "top") return $rich_snippet.$content;
        else if ($this->o["google_rich_snippets_location"] == "bottom") return $content.$rich_snippet;
        else return $content;
    }

    function display_multi_rating($location, $post, $user) {
        $sets = $this->rendering_sets;
        $rendered = "";
        if (is_array($sets) && count($sets) > 0) {
            foreach ($sets as $set) {
                if ($set->auto_location == $location) {
                    $insert = false;
                    $auto = $set->auto_insert;

                    if (is_single() && ($auto == "apst" || $auto == "allp")) $insert = true;
                    if (!$insert && is_page() && ($auto == "apgs" || $auto == "allp")) $insert = true;
                    if (!$insert && is_single() && in_category(explode(",", $set->auto_categories), $post->ID) && $auto == "cats") $insert = true;

                    if ($insert) {
                        $settings = array('id' => $set->multi_id, 'read_only' => 0);
                        $rendered.= $this->f->render_multi_rating($post, $user, $settings);
                    }
                }
            }
        }
        return $rendered;
    }
    // display

    // cache
    function cache_cleanup() {
        if ($this->o["cache_cleanup_auto"] == 1) {
            $clean = false;

            $pdate = strtotime($this->o["cache_cleanup_last"]);
            $next_clean = mktime(date("H", $pdate), date("i", $pdate), date("s", $pdate), date("m", $pdate) + $this->o["cache_cleanup_days"], date("j", $pdate), date("Y", $pdate));
            if (intval($next_clean) < intval(mktime())) $clean = true;

            if ($clean) {
                GDSRHelper::clean_cache(substr(STARRATING_CACHE_PATH, 0, strlen(STARRATING_CACHE_PATH) - 1));
                $this->o["cache_cleanup_last"] = date("r");
                update_option('gd-star-rating', $this->o);
            }
        }
    }

    function cache_posts($user_id) {
        $to_get = array();
        foreach ($this->c as $id => $value) {
            if ($value == 0) $to_get[] = $id;
        }

        if (count($to_get) > 0) {
            global $gdsr_cache_posts_std_data, $gdsr_cache_posts_std_log, $gdsr_cache_posts_std_thumbs_log;

            $data = GDSRDBCache::get_posts($to_get);
            foreach ($data as $row) {
                $id = $row->post_id;
                $this->c[$id] = 1;
                $gdsr_cache_posts_std_data->set($id, $row);
            }

            $logs = GDSRDBCache::get_logs($to_get, $user_id, "article", $_SERVER["REMOTE_ADDR"], $this->o["logged"] != 1, $this->o["allow_mixed_ip_votes"] == 1);
            foreach ($logs as $id => $value) {
                $gdsr_cache_posts_std_log->set($id, $value == 0);
            }

            if ($this->o["thumbs_active"] == 1) {
                $logs_thumb = GDSRDBCache::get_logs($to_get, $user_id, "artthumb", $_SERVER["REMOTE_ADDR"], $this->o["logged"] != 1, $this->o["allow_mixed_ip_votes"] == 1);
                foreach ($logs_thumb as $id => $value) {
                    $gdsr_cache_posts_std_thumbs_log->set($id, $value == 0);
                }
            }
        }
    }

    function cache_comments($post_id) {
        global $gdsr_cache_posts_cmm_data, $gdsr_cache_posts_cmm_log, $gdsr_cache_posts_cmm_thumbs_log, $userdata;
        $user_id = is_object($userdata) ? $userdata->ID : 0;
        $to_get = array();

        $data = GDSRDBCache::get_comments($post_id);
        foreach ($data as $row) {
            $id = $row->comment_id;
            $gdsr_cache_posts_cmm_data->set($id, $row);
            $to_get[] = $id;
        }
        if (count($to_get) > 0) {
            $logs = GDSRDBCache::get_logs($to_get, $user_id, "comment", $_SERVER["REMOTE_ADDR"], $this->o["cmm_logged"] != 1, $this->o["cmm_allow_mixed_ip_votes"] == 1);
            foreach ($logs as $id => $value) {
                $gdsr_cache_posts_cmm_log->set($id, $value == 0);
            }

            if ($this->o["thumbs_active"] == 1) {
                $logs_thumb = GDSRDBCache::get_logs($to_get, $user_id, "cmmthumb", $_SERVER["REMOTE_ADDR"], $this->o["cmm_logged"] != 1, $this->o["cmm_allow_mixed_ip_votes"] == 1);
                foreach ($logs_thumb as $id => $value) {
                    $gdsr_cache_posts_cmm_thumbs_log->set($id, $value == 0);
                }
            }
        }
    }

    function check_ajax_votes_string($vote) {
        $vote = strtolower($vote);
        $restrict = array("+union+", "benchmark", "rand", "+select+", "+limit+", "+order+", " union ", " select ", " limit ", " order ");

        foreach ($restrict as $key) {
            if (strpos($vote, $key) !== false) {
                return false;
            }
        }

        return true;
    }

    function cached_posts($votes) {
        global $userdata;
        $user_id = is_object($userdata) ? $userdata->ID : 0;

        $rendered = $alls = array();
        foreach ($votes as $vote) {
            $valid = $this->check_ajax_votes_string($vote);

            if ($valid) {
                $settings = explode('.', $vote);
                $alls[$vote] = $settings;
                $this->c[$settings[1]] = 0;
            }
        }

        $this->f->render_wait_article();
        $this->f->render_wait_multis();
        $this->f->render_wait_article_thumb();
        $this->cache_posts($user_id);

        foreach ($alls as $vote => $settings) {
            $cde = substr($vote, 0, 3);
            $html = '';

            switch ($cde) {
                case 'asr':
                    $html = $this->f->render_article_actual($settings);
                    break;
                case 'atr':
                    $html = $this->f->render_thumb_article_actual($settings);
                    break;
                case 'amr':
                    $html = $this->f->render_multi_rating_actual($settings);
                    break;
            }

            $html = str_replace('"', '\"', $html);
            $rendered[] = '{"id": "gdsrc_'.$vote.'", "html": "'.$html.'"}';
        }

        return '{ "items": ['.join(", ", $rendered).']}';
    }

    function cached_comments($votes) {
        $rendered = $alls = $postids = array();

        foreach ($votes as $vote) {
            $valid = $this->check_ajax_votes_string($vote);

            if ($valid) {
                $settings = explode('.', $vote);
                $alls[$vote] = $settings;

                if (!in_array($settings[1], $postids)) {
                    $post_id = intval($settings[1]);

                    if ($post_id > 0) {
                        $postids[] = $post_id;
                    }
                }
            }
        }

        $this->f->render_wait_comment();
        $this->f->render_wait_comment_thumb();

        foreach ($postids as $post_id) {
            $this->cache_comments($post_id);
        }

        foreach ($alls as $vote => $settings) {
            $cde = substr($vote, 0, 3);
            $html = '';

            switch ($cde) {
                case 'csr':
                    $html = $this->f->render_comment_actual($settings);
                    break;
                case 'ctr':
                    $html = $this->f->render_thumb_comment_actual($settings);
                    break;
            }

            $html = str_replace('"', '\"', $html);
            $rendered[] = '{"id": "gdsrc_'.$vote.'", "html": "'.$html.'"}';
        }

        return '{ "items": ['.join(", ", $rendered).']}';
    }
    // cache
}

?>