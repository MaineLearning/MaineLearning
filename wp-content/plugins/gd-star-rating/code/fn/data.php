<?php

/**
 * Get list of all multi sets in the associated array.
 *
 * @return array list of multi sets 
 */
function gdsr_get_multi_sets() {
    $sets = array();
    $wpml = GDSRDBMulti::get_multis_tinymce();
    foreach ($wpml as $set) {
        $sets[$set->folder] = $set->name;
    }
    return $sets;
}

/**
 * Returns calculated data for average blog rating including bayesian estimate mean.
 *
 * @param string $select articles to select postpage|post|page
 * @param string $show votes to use: total|users|visitors
 * @return object with average blog rating values
 */
function wp_gdsr_blog_rating($select = "postpage", $show = "total") {
    $widget = array("select" => $select, "show" => $show);
    return GDSRRenderT2::prepare_wbr($widget);
}

/**
 * Returns object with all needed multi rating properties for post or page.
 *
 * @param int $multi_set_id id of the multi rating set
 * @param int $post_id post to get rating for, leave 0 to get post from loop
 * @return object rating post properties
 */
function wp_gdsr_rating_multi($multi_set_id = 0, $post_id = 0) {
    if ($post_id == 0) {
        global $post;
        $post_id = $post->ID;
    }

    $multi_set_id = $multi_set_id == 0 ? gdsr_get_multi_set($post_id) : $multi_set_id;
    $multis_data = GDSRDBMulti::get_multi_rating_data($multi_set_id, $post_id);
    if (count($multis_data) == 0) return null;
    return new GDSRArticleMultiRating($multis_data, $multi_set_id);
}

/**
 * Returns object with all needed rating properties for post or page.
 *
 * @param int $post_id post to get rating for, leave 0 to get post from loop
 * @return object rating post properties
 */
function wp_gdsr_rating_article($post_id = 0) {
    if ($post_id < 1) {
        global $post;
        $post_id = $post->ID;
    }

    $post_data = GDSRDatabase::get_post_data($post_id);
    if (count($post_data) == 0) return null;
    return new GDSRArticleRating($post_data);
}

/**
 * Returns object with all needed rating properties for comment.
 *
 * @param int $post_id post to get rating for, leave 0 to get post from loop
 * @return object rating post properties
 */
function wp_gdsr_rating_comment($comment_id = 0) {
    if ($comment_id < 1) {
        global $comment;
        $comment_id = $comment->comment_ID;
    }

    $comment_data = GDSRDatabase::get_comment_data($comment_id);
    if (count($comment_data) == 0) return null;
    return new GDSRCommentRating($comment_data);
}

function gdsr_rating_data($type = "article", $field = "rating", $id = 0, $multi_set_id = 0) {
    if ($id == 0) {
        if ($type == "article" || $type == "multi") {
            global $post;
            $id = $post->ID;
        } else {
            global $comment;
            $id = $comment->comment_ID;
        }
    }

    $results = null;
    switch ($type) {
        case "article":
            $results = wp_gdsr_rating_article($id);
            break;
        case "multi":
            $results = wp_gdsr_rating_multi($multi_set_id, $id);
            break;
        case "comment":
            $results = wp_gdsr_rating_comment($id);
            break;
    }

    if (is_null($results)) return null;
    else return $results->$field;
}

?>