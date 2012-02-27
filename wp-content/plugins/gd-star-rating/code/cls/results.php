<?php

/**
 * Class with agregated review results for a single post with multi rating data.
 */
class GDSRArticleMultiReview {
    var $post_id;
    var $values;
    var $rating;
    var $set;
    var $rendered;

    /**
     * Class constructor.
     *
     * @param int $post_id post for the results
     */
    function GDSRArticleMultiReview($post_id) {
        $this->post_id = $post_id;
    }
}

/**
 * Class with agregated multi rating results for a single post.
 */
class GDSRArticleMultiRating {
    var $post_id;
    var $set;
    var $review;
    var $user_votes;
    var $visitor_votes;
    var $votes;
    var $user_rating = 0;
    var $visitor_rating = 0;
    var $rating = 0;

    /**
     * Class constructor.
     *
     * @param object $post_data multi rating results from the database
     * @param int $set_id multi rating set id
     */
    function GDSRArticleMultiRating($post_data, $set_id) {
        $this->set = gd_get_multi_set($set_id);
        $this->review = $post_data->average_review;
        $this->user_votes = $post_data->total_votes_users;
        $this->visitor_votes = $post_data->total_votes_visitors;
        $this->votes = $post_data->total_votes_users + $post_data->total_votes_visitors;
        $this->user_rating = $post_data->average_rating_users;
        $this->visitor_rating = $post_data->average_rating_visitors;
        $totals = $this->user_rating * $this->user_votes + $this->visitor_rating * $this->visitor_votes;
        if ($this->votes > 0) $this->rating = number_format($totals / $this->votes, 1);
    }
}

/**
 * Class with agregated results for a single post.
 */
class GDSRArticleRating {
    var $post_id;
    var $review;
    var $user_votes;
    var $visitor_votes;
    var $votes;
    var $views;
    var $user_rating = 0;
    var $visitor_rating = 0;
    var $rating = 0;
    var $thumbs_user_rating = 0;
    var $thumbs_visitor_rating = 0;
    var $thumbs_rating = 0;
    var $thumbs_user_votes;
    var $thumbs_user_votes_plus;
    var $thumbs_user_votes_minus;
    var $thumbs_visitor_votes;
    var $thumbs_visitor_votes_plus;
    var $thumbs_visitor_votes_minus;
    var $thumbs_votes;
    var $thumbs_votes_plus;
    var $thumbs_votes_minus;

    /**
     * Class constructor.
     *
     * @param object $post_data input data
     */
    function GDSRArticleRating($post_data) {
        $this->post_id = $post_data->post_id;
        $this->review = $post_data->review;
        $this->views = $post_data->views;
        $this->user_votes = $post_data->user_voters;
        $this->visitor_votes = $post_data->visitor_voters;
        $this->votes = $this->user_votes + $this->visitor_votes;
        if ($post_data->user_voters > 0) $this->user_rating = number_format($post_data->user_votes / $post_data->user_voters, 1);
        if ($post_data->visitor_voters > 0) $this->visitor_rating = number_format($post_data->visitor_votes / $post_data->visitor_voters, 1);
        if ($this->votes > 0) $this->rating = number_format(($post_data->visitor_votes + $post_data->user_votes) / ($post_data->visitor_voters + $post_data->user_voters), 1);
        $this->thumbs_votes = $post_data->user_recc_plus + $post_data->user_recc_minus + $post_data->visitor_recc_plus + $post_data->visitor_recc_minus;
        $this->thumbs_rating = $post_data->user_recc_plus - $post_data->user_recc_minus + $post_data->visitor_recc_plus - $post_data->visitor_recc_minus;
        $this->thumbs_votes_plus = $post_data->user_recc_plus + $post_data->visitor_recc_plus;
        $this->thumbs_votes_minus = $post_data->user_recc_minus + $post_data->visitor_recc_minus;
        $this->thumbs_visitor_votes = $post_data->visitor_recc_plus + $post_data->visitor_recc_minus;
        $this->thumbs_visitor_rating = $post_data->visitor_recc_plus - $post_data->visitor_recc_minus;
        $this->thumbs_visitor_votes_plus = $post_data->visitor_recc_plus;
        $this->thumbs_visitor_votes_minus = $post_data->visitor_recc_minus;
        $this->thumbs_user_votes = $post_data->user_recc_plus + $post_data->user_recc_minus;
        $this->thumbs_user_rating = $post_data->user_recc_plus - $post_data->user_recc_minus;
        $this->thumbs_user_votes_plus = $post_data->user_recc_plus;
        $this->thumbs_user_votes_minus = $post_data->user_recc_minus;
    }
}

/**
 * Class with agregated results for a single comment.
 */
class GDSRCommentRating {
    var $comment_id;
    var $post_id;
    var $review;
    var $user_votes;
    var $visitor_votes;
    var $votes;
    var $user_rating;
    var $visitor_rating;
    var $rating;
    var $thumbs_user_rating = 0;
    var $thumbs_visitor_rating = 0;
    var $thumbs_rating = 0;
    var $thumbs_user_votes;
    var $thumbs_user_votes_plus;
    var $thumbs_user_votes_minus;
    var $thumbs_visitor_votes;
    var $thumbs_visitor_votes_plus;
    var $thumbs_visitor_votes_minus;
    var $thumbs_votes;
    var $thumbs_votes_plus;
    var $thumbs_votes_minus;

    /**
     * Class constructor.
     *
     * @param object $comment_data input data
     */
    function GDSRCommentRating($comment_data) {
        $this->comment_id = $comment_data->comment_id;
        $this->post_id = $comment_data->post_id;
        $this->review = $comment_data->review;
        $this->user_votes = $comment_data->user_voters;
        $this->visitor_votes = $comment_data->visitor_voters;
        $this->votes = $this->user_votes + $this->visitor_votes;
        if ($comment_data->user_voters > 0) $this->user_rating = number_format($comment_data->user_votes / $comment_data->user_voters, 1);
        if ($comment_data->visitor_voters > 0) $this->visitor_rating = number_format($comment_data->visitor_votes / $comment_data->visitor_voters, 1);
        if ($this->votes > 0) $this->rating = number_format(($comment_data->visitor_votes + $comment_data->user_votes) / ($comment_data->visitor_voters + $comment_data->user_voters), 1);
        $this->thumbs_votes = $post_data->user_recc_plus + $post_data->user_recc_minus + $post_data->visitor_recc_plus + $post_data->visitor_recc_minus;
        $this->thumbs_rating = $post_data->user_recc_plus - $post_data->user_recc_minus + $post_data->visitor_recc_plus - $post_data->visitor_recc_minus;
        $this->thumbs_votes_plus = $post_data->user_recc_plus + $post_data->visitor_recc_plus;
        $this->thumbs_votes_minus = $post_data->user_recc_minus + $post_data->visitor_recc_minus;
        $this->thumbs_visitor_votes = $post_data->visitor_recc_plus + $post_data->visitor_recc_minus;
        $this->thumbs_visitor_rating = $post_data->visitor_recc_plus - $post_data->visitor_recc_minus;
        $this->thumbs_visitor_votes_plus = $post_data->visitor_recc_plus;
        $this->thumbs_visitor_votes_minus = $post_data->visitor_recc_minus;
        $this->thumbs_user_votes = $post_data->user_recc_plus + $post_data->user_recc_minus;
        $this->thumbs_user_rating = $post_data->user_recc_plus - $post_data->user_recc_minus;
        $this->thumbs_user_votes_plus = $post_data->user_recc_plus;
        $this->thumbs_user_votes_minus = $post_data->user_recc_minus;
    }
}

/**
 * Multi Rating Set
 */
class GDMultiSingle {
    var $multi_id = 0;
    var $name = "";
    var $description = "";
    var $stars = 10;
    var $object = array();
    var $weight = array();

    /**
     * Constructor
     *
     * @param bool $fill_empty prefill set with empty elements
     * @param int $count number of elements in the set
     */
    function GDMultiSingle($fill_empty = true, $count = 20) {
        if ($fill_empty) {
            for ($i = 0; $i < $count; $i++) {
                $this->object[] = "";
                $this->weight[] = 1;
            }
        }
    }
}

?>