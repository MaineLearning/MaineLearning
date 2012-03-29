<?php

/**
 * This will render aggregated comments rating for the post.
 *
 * @param int $post_id post to get review for
 * @param int $template_id id of the template to use
 * @param string $show votes to show: total, users, visitors
 * @param bool $echo echo results or return it as a string
 */
function wp_gdsr_render_comment_aggregation($post_id = 0, $template_id = 0, $show = "total", $echo = true) {
    global $gdsr, $post;
    if ($post_id == 0) $post_id = $post->ID;

    if ($echo) echo $gdsr->shortcode_starcomments(array("post" => $post_id, "show" => $show, "tpl" => $template_id));
    else return $gdsr->shortcode_starcomments(array("post" => $post_id, "show" => $show, "tpl" => $template_id));
}

/**
 * Integrate multi rating result into the comment. Must be within valid comments loop.
 *
 * @param int $comment_id id of the comment
 * @param int $multi_set_id id of the multi rating set to use
 * @param int $template_id id of the template to use
 * @param string $stars_set set to use for rendering
 * @param int $stars_size set size to use for rendering
 * @param string $stars_set_ie6 set to use for rendering in ie6
 * @param string $avg_stars_set set to use for rendering of average value
 * @param int $avg_stars_size set size to use for rendering of average value
 * @param string $avg_stars_set_ie6 set to use for rendering of average value in ie6
 * @param bool $echo echo results or return it as a string
 * @return string html with rendered contents
 */
function wp_gdsr_comment_integrate_multi_result($comment_id, $multi_set_id = 0, $template_id = 0, $stars_set = "oxygen", $stars_size = 20, $stars_set_ie6 = "oxygen_gif", $avg_stars_set = "oxygen", $avg_stars_size = 20, $avg_stars_set_ie6 = "oxygen_gif", $echo = true) {
    global $gdsr, $post;

    $multi_set_id = $multi_set_id == 0 ? gdsr_get_multi_set($post->ID) : $multi_set_id;
    if ($echo) echo $gdsr->f->comment_integrate_multi_result($comment_id, $post->ID, $multi_set_id, $template_id, $stars_set, $stars_size, $stars_set_ie6, $avg_stars_set, $avg_stars_size, $avg_stars_set_ie6);
    else return $gdsr->f->comment_integrate_multi_result($comment_id, $post->ID, $multi_set_id, $template_id, $stars_set, $stars_size, $stars_set_ie6, $avg_stars_set, $avg_stars_size, $avg_stars_set_ie6);
}

/**
 * Integrate average multi rating result into the comment.
 *
 * @param int $comment_id id of the comment
 * @param int $multi_set_id id of the multi rating set to use
 * @param int $template_id id of the template to use
 * @param string $avg_stars_set set to use for rendering of average value
 * @param int $avg_stars_size set size to use for rendering of average value
 * @param string $avg_stars_set_ie6 set to use for rendering of average value in ie6
 * @param bool $echo echo results or return it as a string
 * @return string html with rendered contents
 */
function wp_gdsr_comment_integrate_multi_result_average($comment_id, $multi_set_id = 0, $template_id = 0, $avg_stars_set = "oxygen", $avg_stars_size = 20, $avg_stars_set_ie6 = "oxygen_gif", $echo = true) {
    global $gdsr, $post;

    $multi_set_id = $multi_set_id == 0 ? gdsr_get_multi_set($post->ID) : $multi_set_id;
    if ($echo) echo $gdsr->f->comment_integrate_multi_result_average($comment_id, $post->ID, $multi_set_id, $template_id, $avg_stars_set, $avg_stars_size, $avg_stars_set_ie6);
    else return $gdsr->f->comment_integrate_multi_result_average($comment_id, $post->ID, $multi_set_id, $template_id, $avg_stars_set, $avg_stars_size, $avg_stars_set_ie6);
}

/**
 * Integrate standard rating result into the comment. Must be within valid comments loop.
 *
 * @param int $comment_id id of the comment
 * @param string $stars_set set to use for rendering
 * @param int $stars_size set size to use for rendering
 * @param string $stars_set_ie6 set to use for rendering in ie6
 * @param bool $echo echo results or return it as a string
 * @return string html with rendered contents
 */
function wp_gdsr_comment_integrate_standard_result($comment_id, $stars_set = "", $stars_size = 0, $stars_set_ie6 = "", $echo = true) {
    global $gdsr, $post;

    if ($echo) echo $gdsr->f->comment_integrate_standard_result($comment_id, $post->ID, $stars_set, $stars_size, $stars_set_ie6);
    else return $gdsr->f->comment_integrate_standard_result($comment_id, $post->ID, $stars_set, $stars_size, $stars_set_ie6);
}

/**
 * Get integrate standard rating result into the comment. Must be within valid comments loop.
 *
 * @param int $comment_id id of the comment
 * @param bool $echo echo results or return it as a string
 * @return string rating value
 */
function wp_gdsr_get_comment_integrate_standard_result($comment_id, $echo = true) {
    global $gdsr, $post;

    if ($echo) echo $gdsr->f->get_comment_integrate_standard_result($comment_id, $post->ID);
    else return $gdsr->f->get_comment_integrate_standard_result($comment_id, $post->ID);
}

/**
 * Integrate multi set post rating into the comment form.
 *
 * @param int $multi_set_id id of the multi rating set to use
 * @param int $template_id id of the template to use
 * @param int $value inital value for the review
 * @param string $stars_set set to use for rendering
 * @param int $stars_size set size to use for rendering
 * @param string $stars_set_ie6 set to use for rendering in ie6
 * @param bool $echo echo results or return it as a string
 * @return string html with rendered contents
 */
function wp_gdsr_comment_integrate_multi_rating($multi_set_id = 0, $template_id = 0, $value = 0, $stars_set = "oxygen", $stars_size = 20, $stars_set_ie6 = "oxygen_gif", $echo = true) {
    global $gdsr, $post;

    $multi_set_id = $multi_set_id == 0 ? gdsr_get_multi_set($post->ID) : $multi_set_id;
    if ($echo) echo $gdsr->f->comment_integrate_multi_rating($value, $post->ID, $multi_set_id, $template_id, $stars_set, $stars_size, $stars_set_ie6);
    else return $gdsr->f->comment_integrate_multi_rating($value, $post->ID, $multi_set_id, $template_id, $stars_set, $stars_size, $stars_set_ie6);
}

/**
 * Integrate standard post rating into the comment form.
 *
 * @param int $value inital value for the review
 * @param string $stars_set set to use for rendering
 * @param int $stars_size set size to use for rendering
 * @param string $stars_set_ie6 set to use for rendering in ie6
 * @param bool $echo echo results or return it as a string
 * @return string html with rendered contents
 */
function wp_gdsr_comment_integrate_standard_rating($value = 0, $stars_set = "", $stars_size = 0, $stars_set_ie6 = "", $echo = true) {
    global $gdsr, $post;

    if ($gdsr->check_integration_std($post->ID)) {
        if ($echo) echo $gdsr->f->comment_integrate_standard_rating($value, $stars_set, $stars_size, $stars_set_ie6);
        else return $gdsr->f->comment_integrate_standard_rating($value, $stars_set, $stars_size, $stars_set_ie6);
    } else {
        if ($echo) echo "";
        else return "";
    }
}

/**
 * Renders the rating thumbs for article. This function call must be within the post loop.
 *
 * @param int $template_id standard rating block template id
 * @param bool $read_only render block as a read only, voting not allowed
 * @param string $stars_set set to use for rendering
 * @param string $stars_set_ie6 set to use for rendering in ie6
 * @param bool $echo echo results or return it as a string
 * @return string html with rendered contents
 */
function wp_gdsr_render_article_thumbs($template_id = 0, $read_only = false, $stars_set = "", $stars_size = 0, $stars_set_ie6 = "", $echo = true) {
    global $post, $userdata, $gdsr;
    $override = array("style" => $stars_set, "size" => $stars_size, "style_ie6" => $stars_set_ie6, "tpl" => $template_id, "read_only" => $read_only ? 1 : 0);
    $user_id = is_object($userdata) ? $userdata->ID : 0;
    $gdsr->cache_posts($user_id);
    if ($echo) echo $gdsr->f->render_thumb_article($post, $userdata, $override);
    else return $gdsr->f->render_thumb_article($post, $userdata, $override);
}

/**
 * Renders the rating stars for article. This function call must be within the post loop.
 *
 * @param int $template_id standard rating block template id
 * @param bool $read_only render block as a read only, voting not allowed
 * @param string $stars_set set to use for rendering
 * @param int $stars_size set size to use for rendering
 * @param string $stars_set_ie6 set to use for rendering in ie6
 * @param bool $echo echo results or return it as a string
 * @return string html with rendered contents
 */
function wp_gdsr_render_article($template_id = 0, $read_only = false, $stars_set = "", $stars_size = 0, $stars_set_ie6 = "", $echo = true) {
    global $post, $userdata, $gdsr;
    $override = array("style" => $stars_set, "style_ie6" => $stars_set_ie6, "size" => $stars_size, "tpl" => $template_id, "read_only" => $read_only ? 1 : 0);
    $user_id = is_object($userdata) ? $userdata->ID : 0;
    $gdsr->cache_posts($user_id);
    if ($echo) echo $gdsr->f->render_article($post, $userdata, $override);
    else return $gdsr->f->render_article($post, $userdata, $override);
}

/**
 * Manual render of comment thumbs rating. This function call must be within the comment loop.
 *
 * @param int $template_id standard rating block template id
 * @param bool $read_only render block as a read only, voting not allowed
 * @param string $stars_set set to use for rendering
 * @param string $stars_set_ie6 set to use for rendering in ie6
 * @param bool $echo echo results or return it as a string
 * @return string html with rendered contents
 */
function wp_gdsr_render_comment_thumbs($template_id = 0, $read_only = false, $stars_set = "", $stars_size = 0, $stars_set_ie6 = "", $echo = true) {
    global $comment, $post, $userdata, $gdsr;
    $override = array("style" => $stars_set, "size" => $stars_size, "style_ie6" => $stars_set_ie6, "tpl" => $template_id, "read_only" => $read_only ? 1 : 0);
    if ($echo) echo $gdsr->f->render_thumb_comment($post, $comment, $userdata, $override);
    else return $gdsr->f->render_thumb_comment($post, $comment, $userdata, $override);
}

/**
 * Manual render of comment rating. This function call must be within the comment loop.
 *
 * @param bool $read_only render block as a read only, voting not allowed
 * @param string $stars_set set to use for rendering
 * @param int $stars_size set size to use for rendering
 * @param string $stars_set_ie6 set to use for rendering in ie6
 * @param bool $echo echo results or return it as a string
 * @return string html with rendered contents
 */
function wp_gdsr_render_comment($template_id = 0, $read_only = false, $stars_set = "", $stars_size = 0, $stars_set_ie6 = "", $echo = true) {
    global $comment, $userdata, $gdsr, $post;
    $override = array("style" => $stars_set, "style_ie6" => $stars_set_ie6, "size" => $stars_size, "tpl" => $template_id, "read_only" => $read_only ? 1 : 0);
    if ($echo) echo $gdsr->f->render_comment($post, $comment, $userdata, $override);
    else return $gdsr->f->render_comment($post, $comment, $userdata, $override);
}

/**
 * Renders multi rating block. This function call must be withing the post loop.
 *
 * @param int $multi_set_id id of the multi rating set to use
 * @param int $post_id id of the post rating will be attributed to
 * @param int $template_id id of the template to use
 * @param bool $read_only render block as a read only, voting not allowed
 * @param string $stars_set set to use for rendering
 * @param int $stars_size set size to use for rendering
 * @param string $stars_set_ie6 set to use for rendering in ie6
 * @param string $avg_stars_set set to use for rendering of average element
 * @param int $avg_stars_size set size to use for rendering of average element
 * @param string $avg_stars_set_ie6 set to use for rendering in ie6 of average element
 * @param bool $echo echo results or return it as a string
 * @return string html with rendered contents
 */
function wp_gdsr_render_multi($multi_set_id = 0, $template_id = 0, $read_only = false, $post_id = 0, $stars_set = "", $stars_size = 0, $stars_set_ie6 = "", $avg_stars_set = "oxygen", $avg_stars_size = 20, $avg_stars_set_ie6 = "oxygen_gif", $echo = true) {
    global $userdata, $gdsr;
    if ($post_id == 0) global $post;
    else $post = get_post($post_id);
    $user_id = is_object($userdata) ? $userdata->ID : 0;
    $gdsr->cache_posts($user_id);
    $multi_set_id = $multi_set_id == 0 ? gdsr_get_multi_set($post->ID) : $multi_set_id;
    if ($echo) echo $gdsr->f->render_multi_rating($post, $userdata, array("id" => $multi_set_id, "style" => $stars_set, "style_ie6" => $stars_set_ie6, "size" => $stars_size, "read_only" => $read_only, "tpl" => $template_id, "average_stars" => $avg_stars_set, "average_stars_ie6" => $avg_stars_set_ie6, "average_size" => $avg_stars_size));
    else return $gdsr->f->render_multi_rating($post, $userdata, array("id" => $multi_set_id, "style" => $stars_set, "style_ie6" => $stars_set_ie6, "size" => $stars_size, "read_only" => $read_only, "tpl" => $template_id, "average_stars" => $avg_stars_set, "average_stars_ie6" => $avg_stars_set_ie6, "average_size" => $avg_stars_size));
}

/**
 * Renders stars for comment review used in the comment form for the comment author to place it's review rating.
 *
 * @param int $value inital value for the review
 * @param string $stars_set set to use for rendering
 * @param int $stars_size set size to use for rendering
 * @param string $stars_set_ie6 set to use for rendering in ie6
 * @param bool $echo echo results or return it as a string
 * @return string html with rendered contents
 */
function wp_gdsr_new_comment_review($value = 0, $stars_set = "", $stars_size = 0, $stars_set_ie6 = "", $echo = true) {
    global $gdsr;
    $override = array("style" => $stars_set, "style_ie6" => $stars_set_ie6, "size" => $stars_size);
    if ($echo) echo $gdsr->f->comment_review($value, $override);
    else return $gdsr->f->comment_review($value, $override);
}

/**
 * Renders multi rating review editor block.
 *
 * @param int $post_id id of the post rating will be attributed to
 * @param bool $echo echo results or return it as a string
 * @param array $settings override settings for rendering the block
 * @return string html with rendered contents
 */
function wp_gdsr_multi_review_editor($multi_set_id = 0, $post_id = 0, $template_id = 0, $echo = true) {
    global $gdsr, $post;
    if ($post_id == 0) $post_id = $post->ID;

    $multi_set_id = $multi_set_id == 0 ? gdsr_get_multi_set($post_id) : $multi_set_id;
    if ($echo) echo gdsr_render_multi_editor(array("post_id" => $post_id, "multi_id" => $multi_set_id, "tpl" => $template_id), $echo);
    else return gdsr_render_multi_editor(array("post_id" => $post_id, "multi_id" => $multi_set_id, "tpl" => $template_id), $echo);
}

/**
 * Renders multi rating review for a post.
 *
 * @param int $post_id id of the post rating will be attributed to
 * @param int $template_id id of the template to use [RMB]
 * @param string $stars_set set to use for rendering
 * @param int $stars_size set size to use for rendering
 * @param string $stars_set_ie6 set to use for rendering in ie6
 * @param string $avg_stars_set set to use for rendering of average element
 * @param int $avg_stars_size set size to use for rendering of average element
 * @param string $avg_stars_set_ie6 set to use for rendering in ie6 of average element
 * @param bool $echo echo results or return it as a string
 * @return string html with rendered contents
 */
function wp_gdsr_show_multi_review($multi_set_id = 0, $template_id = 0, $post_id = 0, $stars_set = "oxygen", $stars_size = 20, $stars_set_ie6 = "oxygen_gif", $avg_stars_set = "oxygen", $avg_stars_size = 20, $avg_stars_set_ie6 = "oxygen_gif", $echo = true) {
    global $gdsr, $post;
    if ($post_id == 0) $post_id = $post->ID;

    $multi_set_id = $multi_set_id == 0 ? gdsr_get_multi_set($post_id) : $multi_set_id;
    if ($echo) echo $gdsr->shortcode_starreviewmulti(array("post" => $post_id, "id" => $multi_set_id, "tpl" => $template_id, "element_stars" => $stars_set, "element_stars_ie6" => $stars_set_ie6, "element_size" => $stars_size, "average_stars" => $avg_stars_set, "average_stars_ie6" => $avg_stars_set_ie6, "average_size" => $avg_stars_size));
    else return $gdsr->shortcode_starreviewmulti(array("post" => $post_id, "id" => $multi_set_id, "tpl" => $template_id, "element_stars" => $stars_set, "element_stars_ie6" => $stars_set_ie6, "element_size" => $stars_size, "average_stars" => $avg_stars_set, "average_stars_ie6" => $avg_stars_set_ie6, "average_size" => $avg_stars_size));
}

/**
 * Renders single rating stars image with average rating for the multi rating review.
 *
 * @param int $set_id id of the multi rating set
 * @param int $post_id id of the post rating will be attributed to
 * @param string $show what data to use: total, visitors or users votes only
 * @param string $avg_stars_set set to use for rendering of average element
 * @param int $avg_stars_size set size to use for rendering of average element
 * @param string $avg_stars_set_ie6 set to use for rendering in ie6 of average element
 * @param bool $echo echo results or return it as a string
 * @return string html with rendered contents
 */
function wp_gdsr_multi_rating_average($multi_set_id = 0, $post_id = 0, $show = "total", $avg_stars_set = "", $avg_stars_size = 0, $avg_stars_set_ie6 = "", $echo = true) {
    global $gdsr, $post;
    if ($post_id == 0) $post_id = $post->ID;

    $multi_set_id = $multi_set_id == 0 ? gdsr_get_multi_set($post_id) : $multi_set_id;
    $rating = $gdsr->get_multi_average_rendered($post_id, array(
        "id" => $multi_set_id, "show" => $show, "render" => "rating",
        "style" => $avg_stars_set, "size" => $avg_stars_size, "style_ie6" => $avg_stars_set_ie6));
    if ($echo) echo $rating;
    else return $rating;
}

/**
 * Renders single rating stars image with average rating for the multi rating review.
 *
 * @param int $set_id id of the multi rating set
 * @param int $post_id id of the post rating will be attributed to
 * @param bool $echo echo results or return it as a string
 * @return string html with rendered contents
 */
function wp_gdsr_multi_review_average($multi_set_id = 0, $post_id = 0, $echo = true) {
    global $gdsr, $post;
    if ($post_id == 0) $post_id = $post->ID;

    $multi_set_id = $multi_set_id == 0 ? gdsr_get_multi_set($post_id) : $multi_set_id;
    $review = $gdsr->get_multi_average_rendered($post_id, array("id" => $multi_set_id, "render" => "review"));
    if ($echo) echo $review;
    else return $review;
}

/**
 * Renders standard review for a post.
 *
 * @param int $post_id post to get review for
 * @param int $template_id id of the template to use
 * @param bool $echo echo results or return it as a string
 * @return string html with rendered contents
 */
function wp_gdsr_render_review($post_id = 0, $template_id = 0, $stars_set = "oxygen", $stars_size = 20, $stars_set_ie6 = "oxygen_gif", $echo = true) {
    global $gdsr, $post;
    if ($post_id == 0) $post_id = $post->ID;

    if ($echo) echo $gdsr->shortcode_starreview(array("post" => $post_id, "tpl" => $template_id, "style" => $stars_set, "style_ie6" => $stars_set_ie6, "size" => $stars_size));
    else return $gdsr->shortcode_starreview(array("post" => $post_id, "tpl" => $template_id, "style" => $stars_set, "style_ie6" => $stars_set_ie6, "size" => $stars_size));
}

/**
 * Renders rating results based on $atts settings array similar to the StarRating shortcode.
 *
 * @param array $atts settings to use for rendering
 * @param bool $echo echo results or return it as a string
 * @return string html with rendered contents
 */
function wp_gdsr_render_rating_results($atts = array(), $echo = true) {
    global $gdsr;
    $atts = wp_parse_args((array)$atts, $gdsr->default_shortcode_starrating);
    if ($echo) echo GDSRRenderT2::render_srr($atts);
    else return GDSRRenderT2::render_srr($atts);
}

/**
 * Renders widget-like element based on the $widget settings array
 *
 * @param array $widget settings to use for rendering
 * @param bool $echo echo results or return it as a string
 * @return string html with rendered contents
 */
function wp_gdsr_render_star_rating_widget($widget = array(), $echo = true) {
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
function wp_gdsr_render_blog_rating_widget($widget = array(), $echo = true) {
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
function wp_gdsr_render_comments_rating_widget($widget = array(), $echo = true) {
    global $gdsr;
    $widget = wp_parse_args((array)$widget, $gdsr->default_widget_comments);
    if ($echo) echo GDSRRenderT2::render_wcr($widget);
    else return GDSRRenderT2::render_wcr($widget);
}

/**
 * Shows stars with review rating of a comment.
 *
 * @param int $comment_id ID for the comment to display rating from. If this is set to 0, than it must be used within the comment loop, and id of current comment will be used.
 * @param bool $use_default set to true tell this function to render stars using default settings for stars set on settings panel, false tells to use $size and $style parameters.
 * @param int $size size of the stars to render, must be valid value: 12, 20, 30, 46
 * @param string $style name of the stars set to use, name of the folder for the set
 * @param bool $echo echo results or return it as a string
 * @return string html with rendered contents
 */
function wp_gdsr_show_comment_review($comment_id = 0, $use_default = true, $size = 20, $style = "oxygen", $echo = true) {
    global $comment, $gdsr;
    if ($comment_id == 0) $comment_id = $comment->comment_ID;

    if ($echo) echo $gdsr->display_comment_review($comment_id, $use_default, $style, $size);
    else return $gdsr->display_comment_review($comment, $use_default, $style, $size);
}

/**
 * Shows review rating for the post with stars
 *
 * @param int $post_id ID for the article to display rating from. If this is set to 0, than it must be used within the loop, and id of current article will be used.
 * @param bool $use_default set to true tell this function to render stars using default settings for stars set on settings panel, false tells to use $size and $style parameters.
 * @param int $size size of the stars to render, must be valid value: 12, 20, 30, 46
 * @param string $style name of the stars set to use, name of the folder for the set
 * @param bool $echo echo results or return it as a string
 * @return string html with rendered contents
 */
function wp_gdsr_show_article_review($post_id = 0, $use_default = true, $size = 20, $style = "oxygen", $echo = true) {
    global $post, $gdsr;
    if ($post_id == 0) $post_id = $post->ID;

    if ($echo) echo $gdsr->display_article_review($post_id, $use_default, $style, $size);
    else return $gdsr->display_article_review($post_id, $use_default, $style, $size);
}

/**
 * Shows multis review rating for the post with stars
 *
 * @param int $multi_id id of the multi rating set
 * @param int $post_id ID for the article to display rating from. If this is set to 0, than it must be used within the loop, and id of current article will be used.
 * @param bool $use_default set to true tell this function to render stars using default settings for stars set on settings panel, false tells to use $size and $style parameters.
 * @param int $size size of the stars to render, must be valid value: 12, 20, 30, 46
 * @param string $style name of the stars set to use, name of the folder for the set
 * @param bool $echo echo results or return it as a string
 * @return string html with rendered contents
 */
function wp_gdsr_show_multis_review($multi_id, $post_id = 0, $use_default = true, $size = 20, $style = "oxygen", $echo = true) {
    global $post, $gdsr;
    if ($post_id == 0) $post_id = $post->ID;

    if ($echo) echo $gdsr->display_multis_review($multi_id, $post_id, $use_default, $style, $size);
    else return $gdsr->display_multis_review($multi_id, $post_id, $use_default, $style, $size);
}

/**
 * Shows rating for the post with stars
 *
 * @param int $post_id ID for the article to display rating from. If this is set to 0, than it must be used within the loop, and id of current article will be used.
 * @param bool $use_default set to true tell this function to render stars using default settings for stars set on settings panel, false tells to use $size and $style parameters.
 * @param int $size size of the stars to render, must be valid value: 12, 20, 30, 46
 * @param string $style name of the stars set to use, name of the folder for the set
 * @param bool $echo echo results or return it as a string
 * @return string html with rendered contents
 */
function wp_gdsr_show_article_rating($post_id = 0, $use_default = true, $size = 20, $style = "oxygen", $echo = true) {
    global $post, $gdsr;
    if ($post_id == 0) $post_id = $post->ID;

    if ($echo) echo $gdsr->display_article_rating($post_id, $use_default, $style, $size);
    else return $gdsr->display_article_rating($post_id, $use_default, $style, $size);
}

/**
 * Returns object with multi ratings data for any taxonomy.
 *
 * @param string $taxonomy name of the taxonomy (slug name)
 * @param string $term full name of the term belonging to taxonomy
 * @param int $multi_id id of the multi rating set
 * @param int $size size of the stars to render, must be valid value: 12, 20, 30, 46
 * @param string $style name of the stars set to use, name of the folder for the set
 * @return object results with data and rendered stars
 */
function wp_gdsr_taxonomy_multi_ratings($taxonomy = "category", $term = "", $multi_id = 0, $size = 20, $style = "oxygen") {
    global $gdsr;

    return $gdsr->f->get_taxonomy_multi_ratings($taxonomy, $term, $multi_id, $size, $style);
}

?>