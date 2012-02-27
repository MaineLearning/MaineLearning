<?php

/**
 * Renders single rating stars image with average rating for the multi rating review.
 *
 * @param array $settings rendering parameters
 * @param bool $echo echo results or return it as a string
 * @return string html with rendered contents
 */
function gdsr_multi_rating_average($settings = array(), $echo = true) {
    global $gdsr;

    $defaults = array("id" => 0, "render" => "rating", "post_id" => 0, "show" => "total", "style" => "", "style_ie6" => "", "size" => 0);
    $settings = wp_parse_args($settings, $defaults);
    $settings = apply_filters("gdsr_fn_multi_rating_average", $settings);

    if ($settings["post_id"] == 0) {
        global $post;
        $settings["post_id"] = $post->ID;
    }
    if ($settings["multi_set_id"] == 0) {
        $settings["multi_set_id"] = gdsr_get_multi_set($settings["post_id"]);
    }

    $render = $gdsr->get_multi_average_rendered($settings["post_id"], $settings);

    if ($echo) echo $render; else return $render;
}

/**
 * Renders Google Rich Snippet code block.
 *
 * @param array $settings rendering parameters
 * @param bool $echo echo results or return it as a string
 * @return string html with rendered contents
 */
function gdsr_render_google_rich_snippets($settings = array(), $echo = true) {
    global $gdsr;

    $defaults = array("hidden" => true, "format" => "microformat", "source" => "standard_rating", "post_id" => 0);
    $settings = wp_parse_args($settings, $defaults);
    $settings = apply_filters("gdsr_fn_render_google_rich_snippets", $settings);

    if ($settings["post_id"] == 0) {
        global $post;
    } else {
        $post = get_post($settings["post_id"]);
    }

    $render = $gdsr->f->render_google_rich_snippet($post, $settings);

    if ($echo) echo $render; else return $render;
}

/**
 * Renders custom stars image (or div block).
 *
 * @param array $settings rendering parameters
 * @param bool $echo echo results or return it as a string
 * @return string html with rendered contents
 */
function gdsr_render_stars_custom($settings = array(), $echo = true) {
    global $gdsr;

    $defaults = array("vote" => 0, "max_value" => 10, "style" => "oxygen",
        "style_ie6" => "oxygen_gif", "size" => 20, "star_factor" => 1);
    $settings = wp_parse_args($settings, $defaults);
    $settings = apply_filters("gdsr_fn_render_stars_custom", $settings);
    $render = $gdsr->f->render_stars_custom_value($settings);

    if ($echo) echo $render; else return $render;
}

/**
 * Renders widget-like element based on the $widget settings array
 *
 * @param array $widget settings to use for rendering
 * @param bool $echo echo results or return it as a string
 * @return string html with rendered contents
 */
function gdsr_render_star_rating_widget($widget = array(), $echo = true) {
    global $gdsr;

    $widget = wp_parse_args((array)$widget, $gdsr->default_widget);
    if ($echo) echo GDSRRenderT2::render_wsr($widget);
    else return GDSRRenderT2::render_wsr($widget);
}

/**
 * Renders blog rating widget element based on the $widget settings array
 *
 * @param array $widget settings to use for rendering
 * @param bool $echo echo results or return it as a string
 * @return string html with rendered contents
 */
function gdsr_render_blog_rating_widget($widget = array(), $echo = true) {
    global $gdsr;

    $widget = wp_parse_args((array)$widget, $gdsr->default_widget_top);
    if ($echo) echo GDSRRenderT2::render_wbr($widget);
    else return GDSRRenderT2::render_wbr($widget);
}

/**
 * Renders comments rating widget element based on the $widget settings array
 *
 * @param array $widget settings to use for rendering
 * @param bool $echo echo results or return it as a string
 * @return string html with rendered contents
 */
function gdsr_render_comments_rating_widget($widget = array(), $echo = true) {
    global $gdsr;

    $widget = wp_parse_args((array)$widget, $gdsr->default_widget_comments);
    if ($echo) echo GDSRRenderT2::render_wcr($widget);
    else return GDSRRenderT2::render_wcr($widget);
}

/**
 * Render the multi rating editor
 *
 * @param string $settings rendering parameters
 * @param bool $echo echo results or return it as a string
 * @return string html with rendered contents
 */
function gdsr_render_multi_editor($settings = array(), $echo = true) {
    global $gdsr;

    $defaults = array("multi_id" => 0, "post_id" => 0, "tpl" => 0, "unlinked" => false, "admin" => false,
        "style" => "oxygen", "style_ie6" => "oxygen_gif", "size" => 20, "votes" => array());
    $settings = wp_parse_args($settings, $defaults);
    $settings = apply_filters("gdsr_fn_render_multi_editor", $settings);
    if ($settings["post_id"] == 0 && !$settings["unlinked"]) {
        global $post;
        $settings["post_id"] = $post->ID;
    }

    if ($echo) echo $gdsr->s->render_multi_editor($settings);
    else return $gdsr->s->render_multi_editor($settings);
}

/**
 * Save multi rating or review for a post
 *
 * @param int $post_id id of the post to add values
 * @param int $multi_set_id multi set id
 * @param array $values list of values
 */
function gdsr_save_multi_review($post_id, $multi_set_id, $values) {
    global $gdsr;
    $set = gd_get_multi_set($multi_set_id);

    $clean = array();
    foreach ($values as $v) {
        if ($v < 1) $v = 1;
        $clean[] = $v > $set->stars ? $set->stars : $v;
    }

    $record_id = GDSRDBMulti::get_vote($post_id, $multi_set_id, count($set->object));
    GDSRDBMulti::save_review($record_id, $clean);
    GDSRDBMulti::recalculate_multi_review($record_id, $clean, $set);
}

/**
 * Renders the multi review result
 *
 * @param string $settings rendering parameters
 * @param bool $echo echo results or return it as a string
 * @return string html with rendered contents
 */
function gdsr_render_multi_review($settings = array(), $echo = true) {
    global $gdsr;
    
    $defaults = array("multi_id" => 0, "post_id" => 0, "tpl" => 0, "factor" => 1, "id" => 0,
        "element_stars" => "oxygen", "element_stars_ie6" => "oxygen_gif", "element_size" => 20,
        "average_stars" => "oxygen", "average_stars_ie6" => "oxygen_gif", "average_size" => 20);
    $settings = wp_parse_args($settings, $defaults);
    $settings = apply_filters("gdsr_fn_render_multi_review", $settings);
    if ($settings["post_id"] == 0) {
        global $post;
        $settings["post_id"] = $post->ID;
    }

    $settings["id"] = $settings["multi_id"] == 0 ? gdsr_get_multi_set($settings["post_id"]) : $settings["multi_id"];
    if ($echo) echo $gdsr->shortcode_starreviewmulti($settings);
    else return $gdsr->shortcode_starreviewmulti($settings);
}

/**
 * Get the data and rendered stars aggregated on taxonomies.
 *
 * @param string $settings rendering parameters
 * @param bool $echo echo results or return it as a string
 * @return object prepared ratings data and renderings
 */
function gdsr_get_taxonomy_multi_ratings($settings = array()) {
    global $gdsr;

    $defaults = array("taxonomy" => "category", "terms" => array(), "multi_id" => 0,
        "style" => "oxygen", "style_ie6" => "oxygen_gif", "size" => 20, "star_factor" => 1,
        "average_style" => "oxygen", "average_style_ie6" => "oxygen_gif", "average_size" => 20,
        "tpl_rating" => 0, "tpl_review" => 0, "term_property" => "name");
    $settings = wp_parse_args($settings, $defaults);
    $settings = apply_filters("gdsr_fn_get_taxonomy_multi_ratings", $settings);

    return $gdsr->f->taxonomy_multi_ratings($settings);
}

?>