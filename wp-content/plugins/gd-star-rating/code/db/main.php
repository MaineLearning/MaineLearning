<?php

class gdsrDB {
    function filter_latest_votes($o, $user = 0) {
        global $wpdb, $table_prefix;
        $types = array();

        $where = $user == 0 ? "" : " and l.user_id = ".$user;
        $select = "l.id, l.vote_type, l.voted, l.vote, l.ip, l.user_id, u.display_name, u.user_email";
        $from = sprintf("%sgdsr_votes_log l left join %s u on u.ID = l.user_id", $table_prefix, $wpdb->users);

        if ($o["integrate_dashboard_latest_filter_thumb_std"] == 1) $types[] = "'artthumb'";
        if ($o["integrate_dashboard_latest_filter_thumb_cmm"] == 1) $types[] = "'cmmthumb'";
        if ($o["integrate_dashboard_latest_filter_stars_std"] == 1) $types[] = "'article'";
        if ($o["integrate_dashboard_latest_filter_stars_cmm"] == 1) $types[] = "'comment'";
        if ($o["integrate_dashboard_latest_filter_stars_mur"] == 1) {
            $types[] = "'multis'";
            $select.= ", l.object, m.stars, m.weight, m.name";
            $from.= sprintf(" left join %sgdsr_multis m on m.multi_id = l.multi_id", $table_prefix);
        }

        if (count($types) == 0) return array();

        $sql = sprintf("select %s from %s where vote_type in (%s)%s order by voted desc limit 0, %s",
            $select, $from, join(", ", $types), $where, $o["integrate_dashboard_latest_count"]);
        return $wpdb->get_results($sql);
    }
}

class GDSRDatabase {
    // categories
    function add_category_defaults($ids, $ids_array, $items) {
        global $wpdb, $table_prefix;
        $sql = sprintf("select category_id from %sgdsr_data_category where category_id in %s", $table_prefix, $ids);

        $cats = array();
        $rows = $wpdb->get_results($sql, ARRAY_N);
        if (is_array($rows)) foreach ($rows as $row) $cats[] = $row[0];

        if (is_array($cats)) {
            foreach ($ids_array as $id) {
                if (!in_array($id, $cats)) {
                    GDSRDatabase::add_category_default($id, $items[$id] > 0);
                }
            }
        }
    }

    function add_category_default($id, $parented = false) {
        global $wpdb, $table_prefix;

        $values = $parented ? "'P', 'P', 'P', 'P', 'P'" : "'A', 'A', 'N', 'N', 'N'";

        $sql = sprintf("INSERT INTO %sgdsr_data_category (category_id, rules_articles, rules_comments, moderate_articles, moderate_comments, expiry_type, expiry_value, cmm_integration_set) VALUES (%s, %s, '', 0)",
                $table_prefix, $id, $values);

        $wpdb->query($sql);
    }

    function init_categories_data() {
        $all_cats = GDSRDatabase::get_all_categories();
        $categories = GDSRHelper::get_categories_hierarchy($all_cats);

        $ids_array = $items = array();
        foreach ($categories as $cat) {
            $items[$cat->term_id] = $cat->parent;
            $ids_array[] = $cat->term_id;
        }

        GDSRDatabase::add_category_defaults("(".join(", ", $ids_array).")", $ids_array, $items);
    }

    function get_all_categories() {
        global $wpdb, $table_prefix;

        $sql = sprintf("SELECT 0 as depth, x.term_id, t.name, t.slug, x.parent, x.count, ms.name as multi_set, d.* FROM %sterm_taxonomy x inner join %sterms t on x.term_id = t.term_id left join %sgdsr_data_category d on d.category_id = x.term_id left join %sgdsr_multis ms on ms.multi_id = d.cmm_integration_set where taxonomy = 'category' order by x.parent, t.name asc",
                $table_prefix, $table_prefix, $table_prefix, $table_prefix);
        return $wpdb->get_results($sql);
    }

    function get_categories_data($cats = array()) {
        global $wpdb, $table_prefix;

        $where = count($cats) > 0 ? sprintf(" and x.term_id in (%s)", join(", ", $cats)) : "";
        $sql = sprintf("SELECT x.parent, d.* FROM %sterm_taxonomy x inner join %sterms t on x.term_id = t.term_id left join %sgdsr_data_category d on d.category_id = x.term_id left join %sgdsr_multis ms on ms.multi_id = d.cmm_integration_set where taxonomy = 'category'%s order by x.parent desc, d.cmm_integration_set desc, x.term_id asc",
                $table_prefix, $table_prefix, $table_prefix, $table_prefix, $where);
        return $wpdb->get_results($sql);
    }
    // categories

    // save & update
    function delete_votes($ids, $delete, $ids_array, $thumbs = false) {
        global $wpdb, $table_prefix;
        $cde_articles = $thumbs ? "artthumb" : "article";
        $cde_comments = $thumbs ? "cmmthumb" : "comment";
        $dbt_data_article = $table_prefix.'gdsr_data_article';
        $dbt_data_comment = $table_prefix.'gdsr_data_comment';
        $dbt_votes_log = $table_prefix.'gdsr_votes_log';
        if ($delete == "") return;

        $delstring = $dellog = "";
        switch (substr($delete, 1, 1)) {
            case "A":
                $delstring = $thumbs ? "user_recc_plus = 0, user_recc_minus = 0, visitor_recc_plus = 0, visitor_recc_minus = 0" : "user_votes = 0, user_voters = 0, visitor_votes = 0, visitor_voters = 0";
                break;
            case "V":
                $delstring = $thumbs ? "visitor_recc_plus = 0, visitor_recc_minus = 0" : "visitor_votes = 0, visitor_voters = 0";
                $dellog = " and user_id = 0";
                break;
            case "U":
                $delstring = $thumbs ? "user_recc_plus = 0, user_recc_minus = 0" : "user_votes = 0, user_voters = 0";
                $dellog = " and user_id > 0";
                break;
            default:
                return;
                break;
        }

        if (substr($delete, 0, 1) == "A") {
            $wpdb->query(sprintf("update %s set %s where post_id in %s", $dbt_data_article, $delstring, $ids));
            $wpdb->query(sprintf("delete from %s where vote_type = '%s' and id in %s%s", $dbt_votes_log, $cde_articles, $ids, $dellog));
        } else if (substr($delete, 0, 1) == "K") {
            $wpdb->query(sprintf("update %s set %s where comment_id in %s", $dbt_data_comment, $delstring, $ids));
            $wpdb->query(sprintf("delete from %s where vote_type = '%s' and id in %s%s", $dbt_votes_log, $cde_comments, $ids, $dellog));
        } else if (substr($delete, 0, 1) == "C") {
            $cids = GDSRDatabase::get_commentids_posts($ids);
            $cm = array();
            foreach ($cids as $cid) 
                $cm[] = $cid->comment_id;
            $cms = "(".join(", ", $cm).")";
            $wpdb->query(sprintf("update %s set %s where post_id in %s", $dbt_data_comment, $delstring, $ids));
            $wpdb->query(sprintf("delete from %s where vote_type = '%s' and id in %s%s", $dbt_votes_log, $cde_comments, $cms, $dellog));
        } else return;
    }

    function add_defaults($ids, $ids_array) {
        global $wpdb, $table_prefix;
        $rows = $wpdb->get_results(sprintf("select post_id from %sgdsr_data_article where post_id in %s", $table_prefix, $ids), ARRAY_N);
        if (count($rows) == 0) $rows = array();
        $found = array();
        foreach ($rows as $r) $found[] = $r[0];
        foreach ($ids_array as $id)
            if (!in_array($id, $found)) GDSRDatabase::add_default_vote($id);
    }

    function save_comment_review($comment_id, $review) {
        global $wpdb, $table_prefix;
        $comments = $table_prefix.'gdsr_data_comment';
        $sql = sprintf("update %s set review = %s where comment_id = %s", 
            $comments, $review, $comment_id);
        $wpdb->query($sql);
    }

    function save_review($post_id, $rating, $old = true) {
        global $wpdb, $table_prefix;
        $articles = $table_prefix.'gdsr_data_article';
        if ($rating < 0) $rating = -1;
        if (!$old) GDSRDatabase::add_default_vote($post_id, '', $rating);
        else $wpdb->query("update ".$articles." set review = ".$rating." where post_id = ".$post_id);
    }

    function save_comment_rules($post_id, $comment_vote, $comment_moderation, $recc_comment_vote, $recc_comment_moderation, $old = true) {
        global $wpdb, $table_prefix;
        $articles = $table_prefix.'gdsr_data_article';
        if (!$old) GDSRDatabase::add_default_vote($post_id);
        $sql = "update ".$articles." set rules_comments = '".$comment_vote."', moderate_comments = '".$comment_moderation."', recc_rules_comments = '".$recc_comment_vote."', recc_moderate_comments = '".$recc_comment_moderation."' where post_id = ".$post_id;
        $wpdb->query($sql);
    }

    function save_article_rules($post_id, $article_vote, $article_moderation, $recc_article_vote, $recc_article_moderation, $old = true) {
        global $wpdb, $table_prefix;
        $articles = $table_prefix.'gdsr_data_article';
        if (!$old) GDSRDatabase::add_default_vote($post_id);
        $sql = "update ".$articles." set rules_articles = '".$article_vote."', moderate_articles = '".$article_moderation."', recc_rules_articles = '".$recc_article_vote."', recc_moderate_articles = '".$recc_article_moderation."' where post_id = ".$post_id;
        $wpdb->query($sql);
    }

    function save_timer_rules($post_id, $timer_type, $timer_value, $old = true) {
        global $wpdb, $table_prefix;
        $articles = $table_prefix.'gdsr_data_article';
        if (!$old) GDSRDatabase::add_default_vote($post_id);
        $wpdb->query("update ".$articles." set expiry_type = '".$timer_type."', expiry_value = '".$timer_value."' where post_id = ".$post_id);
    }

    function save_timer_rules_thumbs($post_id, $timer_type, $timer_value, $old = true) {
        global $wpdb, $table_prefix;
        $articles = $table_prefix.'gdsr_data_article';
        if (!$old) GDSRDatabase::add_default_vote($post_id);
        $wpdb->query("update ".$articles." set recc_expiry_type = '".$timer_type."', recc_expiry_value = '".$timer_value."' where post_id = ".$post_id);
    }

    function rating_from_comment($comment_id) {
        global $wpdb, $table_prefix;
        $sql = sprintf("select vote from %sgdsr_votes_log where vote_type = 'article' and comment_id = %s", $table_prefix, $comment_id);
        return $wpdb->get_var($sql);
    }

    function delete_by_comment($comment_id) {
        global $wpdb, $table_prefix;
        $sql = sprintf("select * from %sgdsr_votes_log where vote_type = 'article' and comment_id = %s", $table_prefix, $comment_id);
        $row = $wpdb->get_row($sql);
        if (count($row) > 0) {
            if ($row->user_id == 0) $delstring = sprintf("visitor_votes = visitor_votes - %s, visitor_voters = visitor_voters - 1", $row->vote);
            else $delstring = sprintf("user_votes = user_votes - %s, user_voters = user_voters - 1", $row->vote);

            $sql = sprintf("update %sgdsr_data_article set %s where post_id = %s", $table_prefix, $delstring, $row->id);
            $wpdb->query($sql);
            $sql = sprintf("delete from %sgdsr_votes_log where record_id = %s", $table_prefix, $row->record_id);
            $wpdb->query($sql);
        }
    }

    function add_default_vote($post_id, $is_page = '', $review = -1) {
        global $wpdb, $table_prefix;
        $options = get_option('gd-star-rating');
        if ($is_page == '') $is_page = GDSRDatabase::get_post_type($post_id) == 'page' ? '1' : '0';

        $vote_timer_value = $options["default_timer_type"] == "T" || $options["default_timer_type"] == "D" ? $options["default_timer_value"] : "";
        $dbt_data_article = $table_prefix.'gdsr_data_article';
        $sql = sprintf("INSERT INTO %s (post_id, rules_articles, rules_comments, moderate_articles, moderate_comments, recc_rules_articles, recc_rules_comments, recc_moderate_articles, recc_moderate_comments, is_page, user_voters, user_votes, visitor_voters, visitor_votes, review, expiry_type, expiry_value) VALUES (%s, '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', 0, 0, 0, 0, %s, '%s', '%s')",
                $dbt_data_article, $post_id, $options["default_voterules_articles"], $options["default_voterules_comments"], 
                $options["default_moderation_articles"], $options["default_moderation_comments"], $options["recc_default_voterules_articles"], $options["recc_default_voterules_comments"],
                $options["recc_default_moderation_articles"], $options["recc_default_moderation_comments"], $is_page, $review, $options["default_timer_type"], $vote_timer_value);
        $wpdb->query($sql);
    }

    function add_empty_comment($comment_id, $post_id, $review = -1) {
        global $wpdb, $table_prefix;
        $sql = sprintf("INSERT INTO %sgdsr_data_comment (comment_id, post_id, is_locked, user_voters, user_votes, visitor_voters, visitor_votes, review) VALUES (%s, %s, '0', '0', '0', '0', '0', %s)",
                $table_prefix, $comment_id, $post_id, $review);
        $wpdb->query($sql);
    }

    function add_empty_comments($post_id) {
        global $wpdb, $table_prefix;
        $dbt_data_comment = $table_prefix.'gdsr_data_comment';

        $sql = sprintf("select c.comment_ID from %s c left join %s g on c.comment_ID = g.comment_ID where (isnull(g.post_id) or g.post_id < 1) and c.comment_approved = 1 and c.comment_type = '' and c.comment_post_id = %s",
                $wpdb->comments, $dbt_data_comment, $post_id);
        $cmms = $wpdb->get_results($sql);
        foreach ($cmms as $c)
            GDSRDatabase::add_empty_comment($c->comment_ID, $post_id);
    }
    // save & update

    // get
    function get_post_data($post_id) {
        global $wpdb, $table_prefix;
        $sql = "SELECT * FROM ".$table_prefix."gdsr_data_article WHERE post_id = ".$post_id;
        $results = $wpdb->get_row($sql, OBJECT);
        return $results;
    }

    function get_comment_data($comment_id) {
        global $wpdb, $table_prefix;
        $sql = "SELECT * FROM ".$table_prefix."gdsr_data_comment WHERE comment_id = ".$comment_id;
        $results = $wpdb->get_row($sql, OBJECT);
        return $results;
    }

    function get_commentids_posts($post_ids) {
        global $wpdb, $table_prefix;
        $comments = $table_prefix.'gdsr_data_comment';
        return $wpdb->get_results("select comment_id from ".$comments." where post_id in ".$post_ids, OBJECT);
    }

    function get_comment_review($comment_id) {
        global $wpdb, $table_prefix;
        $comments = $table_prefix.'gdsr_data_comment';

        $sql = "select review from ".$comments." WHERE comment_id = ".$comment_id;
        $results = $wpdb->get_row($sql, OBJECT);
        if (count($results) == 0) return 0;
        else return $results->review;
    }

    function get_post_edit($post_id) {
        global $wpdb, $table_prefix;
        $articles = $table_prefix.'gdsr_data_article';
        
        $sql = "select * from ".$articles." WHERE post_id = ".$post_id;
        $results = $wpdb->get_row($sql, OBJECT);
        return $results;
    }

    function get_comments_count($post_id) {
        global $wpdb, $table_prefix;

        $sql = sprintf("select count(*) from %s where comment_approved = 1 and comment_type = '' and comment_post_id = %s",
            $wpdb->comments,
            $post_id
        );
        return $wpdb->get_var($sql);
    }

    function get_comments($post_id) {
        global $wpdb, $table_prefix;
        $dbt_data_comment = $table_prefix.'gdsr_data_comment';

        GDSRDatabase::add_empty_comments($post_id);
        $sql = sprintf("select g.*, c.comment_content, c.comment_author, c.comment_author_email, c.comment_author_url, c.comment_date from %s c left join %s g on c.comment_ID = g.comment_ID where c.comment_approved = 1 and c.comment_type = '' and c.comment_post_id = %s order by c.comment_date desc",
            $wpdb->comments,
            $dbt_data_comment,
            $post_id
        );
        return $sql;
    }

    function get_post_type($post_id) {
        global $wpdb;
        $sql = "SELECT post_type FROM $wpdb->posts WHERE ID = ".$post_id;
        $r = $wpdb->get_row($sql);
        return $r->post_type;
    }
    // get
}

class GDSRDBMulti {
    function get_review_avg($multi_id, $post_id) {
        global $wpdb, $table_prefix;
        $articles = $table_prefix.'gdsr_multis_data';

        $sql = "select average_review from ".$articles." WHERE multi_id = ".$multi_id." and post_id = ".$post_id;
        $results = $wpdb->get_row($sql, OBJECT);
        if (count($results) == 0) return -1;
        else return $results->average_review;
    }

    function delete_votes($ids, $delete, $multi_id) {
        global $wpdb, $table_prefix;
        if ($delete == "") return;

        $delstring = $dellog = "";
        switch (substr($delete, 1, 1)) {
            case "A":
                $delstring = "md.average_rating_users = 0, md.total_votes_users = 0, mv.user_voters = 0, mv.user_votes = 0";
                $delstring.= ", md.average_rating_visitors = 0, md.total_votes_visitors = 0, mv.visitor_voters = 0, mv.visitor_votes = 0";
                break;
            case "V":
                $delstring = "md.average_rating_visitors = 0, md.total_votes_visitors = 0, mv.visitor_voters = 0, mv.visitor_votes = 0";
                $dellog = " and user_id = 0";
                break;
            case "U":
                $delstring = "md.average_rating_users = 0, md.total_votes_users = 0, mv.user_voters = 0, mv.user_votes = 0";
                $dellog = " and user_id > 0";
                break;
            default:
                return;
                break;
        }

        $sql = sprintf("update %sgdsr_multis_data md inner join %sgdsr_multis_values mv on mv.id = md.id set %s where md.multi_id = %s and md.post_id in %s and mv.source = 'dta'",
            $table_prefix, $table_prefix, $delstring, $multi_id, $ids);
        $wpdb->query($sql);

        $sql = sprintf("delete from %sgdsr_votes_log where id in %s and multi_id = %s%s", $table_prefix, $ids, $multi_id, $dellog);
        $wpdb->query($sql);
    }

    function recalculate_multi_rating_log() {
        global $wpdb, $table_prefix;

        $sql = sprintf("select * from %sgdsr_votes_log where vote_type = 'multis' and vote = 0", $table_prefix);
        $rows = $wpdb->get_results($sql);

        foreach ($rows as $row) {
            $set = wp_gdget_multi_set($row->multi_id);
            if (!is_null($set)) {
                $vote = intval(10 * GDSRDBMulti::get_multi_rating_from_single_object($set, unserialize($row->object)));
                $sql = sprintf("update %sgdsr_votes_log set `vote` = %s where record_id = %s", $table_prefix, $vote, $row->record_id);
                $wpdb->query($sql);
            }
        }
    }

    function get_multi_rating_from_single_object($set, $object) {
        $weighted = $i = 0;

        $weight_norm = array_sum($set->weight);
        $multi_data = $object;
        foreach ($multi_data as $md) {
            $weighted += (intval($md) * $set->weight[$i]) / $weight_norm;
            $i++;
        }
        return number_format($weighted, 1);
    }

    function get_first_multi_set() {
        global $wpdb, $table_prefix;

        $sql = sprintf("select * from %sgdsr_multis order by multi_id limit 0, 1", $table_prefix);
        return $wpdb->get_row($sql);
    }

    function get_multisets_for_auto_insert() {
        global $wpdb, $table_prefix;

        $sql = sprintf("select * from %sgdsr_multis where auto_insert != 'none'", $table_prefix);
        return $wpdb->get_results($sql);
    }

    function recalculate_trend_averages($trend_id, $set) {
        global $wpdb, $table_prefix;

        $multi_data = GDSRDBMulti::get_trend_values($trend_id);
        $weight_norm = array_sum($set->weight);
        $total_users = $total_visitors = $total_votes = 0;
        $user_weighted = $visitors_weighted = $weighted = 0;
        $i = 0;
        foreach ($multi_data as $md) {
            $v_user = $v_visitor = $s_user = $s_visitor = $r_user = $r_visitor = 0;

            $v_visitor = $md->visitor_voters;
            $s_visitor = $md->visitor_votes;
            $v_user = $md->user_voters;
            $s_user = $md->user_votes;

            if ($v_visitor > 0) $r_visitor = $s_visitor / $v_visitor;
            if ($v_user > 0) $r_user = $s_user / $v_user;
            if ($r_visitor > $set->stars) $r_visitor = $set->stars;
            if ($r_user > $set->stars) $r_user = $set->stars;

            $r_visitor = @number_format($r_visitor, 1);
            $r_user = @number_format($r_user, 1);
            $visitors_weighted += ($r_visitor * $set->weight[$i]) / $weight_norm;
            $user_weighted += ($r_user * $set->weight[$i]) / $weight_norm;
            $total_visitors += $v_visitor;
            $total_users += $v_user;

            $i++;
        }
        $rating_users = @number_format($user_weighted, 1);
        $rating_visitors = @number_format($visitors_weighted, 1);
        $total_users = @number_format($total_users / $i, 0);
        $total_visitors = @number_format($total_visitors / $i, 0);

        $sql = sprintf("update %sgdsr_multis_trend set average_rating_users = '%s', average_rating_visitors = '%s', total_votes_users = '%s', total_votes_visitors = '%s' where id = %s",
            $table_prefix, $rating_users, $rating_visitors, $total_users, $total_visitors, $trend_id);
        $wpdb->query($sql);
    }

    function recalculate_all_sets() {
        global $wpdb, $table_prefix;
        $set = null;
        $prev_set = 0;

        $sql = sprintf("select id, post_id, multi_id from %sgdsr_multis_data order by multi_id asc", $table_prefix);
        $posts = $wpdb->get_results($sql);
        foreach ($posts as $post) {
            if ($prev_set != $post->multi_id) $set = gd_get_multi_set($post->multi_id);
            GDSRDBMulti::recalculate_multi_averages($post->post_id, $post->multi_id, "", $set);
            GDSRDBMulti::recalculate_multi_review_db($post->post_id, $post->id, $set);
        }

        $prev_set = 0;

        $sql = sprintf("select id, multi_id from %sgdsr_multis_trend order by multi_id asc", $table_prefix);
        $ids = $wpdb->get_results($sql);
        foreach ($ids as $id) {
            if ($prev_set != $id->multi_id) $set = gd_get_multi_set($id->multi_id);
            foreach ($ids as $id) GDSRDBMulti::recalculate_trend_averages($id->id, $set);
        }
    }

    function recalculate_set($set_id) {
        global $wpdb, $table_prefix;
        $set = gd_get_multi_set($set_id);

        $sql = sprintf("select id, post_id from %sgdsr_multis_data where multi_id = %s", $table_prefix, $set_id);
        $posts = $wpdb->get_results($sql);
        foreach ($posts as $post) {
            GDSRDBMulti::recalculate_multi_averages($post->post_id, $set_id, "", $set);
            GDSRDBMulti::recalculate_multi_review_db($post->post_id, $post->id, $set);
        }

        $sql = sprintf("select id from %sgdsr_multis_trend where multi_id = %s", $table_prefix, $set_id);
        $ids = $wpdb->get_results($sql);
        foreach ($ids as $id) GDSRDBMulti::recalculate_trend_averages($id->id, $set);
    }

    function get_multi_rating_data($set_id, $post_id) {
        global $wpdb, $table_prefix;
        $sql = sprintf("select * from %sgdsr_multis_data where post_id = %s and multi_id = %s", $table_prefix, $post_id, $set_id);
        return $wpdb->get_row($sql);
    }

    function recalculate_multi_review_db($post_id, $record_id, $set) {
        global $wpdb, $table_prefix;
        $multi_data = GDSRDBMulti::get_values($record_id, 'rvw');
        if (count($multi_data) == 0) {
            GDSRDBMulti::add_empty_review_values($record_id, count($set->object));
            $multi_data = GDSRDBMulti::get_values($record_id, 'rvw');
        }
        $review = new GDSRArticleMultiReview($post_id);
        $review->set = $set;
        $i = 0;
        $weighted = 0;
        $weight_norm = array_sum($set->weight);
        foreach ($multi_data as $md) {
            $single_vote = array();
            $single_vote["votes"] = 1;
            $single_vote["score"] = $md->user_votes;
            $single_vote["rating"] = $single_vote["score"];
            $review->values = $single_vote;
            $weighted += ( $single_vote["rating"] * $set->weight[$i] ) / $weight_norm;
            $i++;
        }
        $review->rating = @number_format($weighted, 1);

        $sql = sprintf("update %sgdsr_multis_data set average_review = '%s' where id = %s", $table_prefix, $review->rating, $record_id);
        $wpdb->query($sql);

        return $review;
    }

    function recalculate_multi_review($record_id, $values, $set) {
        global $wpdb, $table_prefix;

        $weight_norm = array_sum($set->weight);
        $overall = 0;
        for ($i = 0; $i < count($values); $i++) {
            if ($weight_norm > 0) {
                $overall += ($values[$i] * $set->weight[$i]) / $weight_norm;
            }
        }
        $overall = @number_format($overall, 1);

        $sql = sprintf("update %sgdsr_multis_data set average_review = '%s' where id = %s", $table_prefix, $overall, $record_id);
        $wpdb->query($sql);

        return $overall;
    }

    function recalculate_multi_vote($values, $set) {
        $weight_norm = array_sum($set->weight);
        $weight = $i = 0;
        foreach ($values as $value) {
            $weight+= ($value * $set->weight[$i]) / $weight_norm;
            $i++;
        }
        $rating = @number_format($weight, 1);
        return $rating;
    }

    function recalculate_multi_averages($post_id, $set_id, $rules = "", $set = null, $last_voted = false, $size = 20) {
        global $wpdb, $table_prefix;

        if ($set == null) $set = gd_get_multi_set($set_id);
        $multi_data = GDSRDBMulti::get_values_join($post_id, $set_id);
        $votes_js = array();
        $weight_norm = array_sum($set->weight);
        $total_users = $total_visitors = $total_votes = 0;
        $user_weighted = $visitors_weighted = $weighted = 0;
        $i = 0;
        foreach ($multi_data as $md) {
            $votes = $score = $rating = $v_user = $v_visitor = $s_user = $s_visitor = $r_user = $r_visitor = 0;

            $v_visitor = $md->visitor_voters;
            $s_visitor = $md->visitor_votes;
            $v_user = $md->user_voters;
            $s_user = $md->user_votes;

            if ($v_visitor > 0) $r_visitor = $s_visitor / $v_visitor;
            if ($v_user > 0) $r_user = $s_user / $v_user;
            if ($r_visitor > $set->stars) $r_visitor = $set->stars;
            if ($r_user > $set->stars) $r_user = $set->stars;

            $r_visitor = @number_format($r_visitor, 1);
            $r_user = @number_format($r_user, 1);
            $visitors_weighted += ($r_visitor * $set->weight[$i]) / $weight_norm;
            $user_weighted += ($r_user * $set->weight[$i]) / $weight_norm;
            $total_visitors += $v_visitor;
            $total_users += $v_user;

            if ($rules != "") {
                if ($rules == "A" || $rules == "N") {
                    $votes = $md->user_voters + $md->visitor_voters;
                    $score = $md->user_votes + $md->visitor_votes;
                }
                else if ($rules == "V") {
                    $votes = $md->visitor_voters;
                    $score = $md->visitor_votes;
                }
                else if ($rules == "U") {
                    $votes = $md->user_voters;
                    $score = $md->user_votes;
                }
                if ($votes > 0) $rating = $score / $votes;
                if ($rating > $set->stars) $rating = $set->stars;
                $rating = @number_format($rating, 1);
                $votes_js[] = $rating * $size;
                $weighted += ($rating * $set->weight[$i]) / $weight_norm;
                $total_votes += $votes;
            }
            $i++;
        }
        $rating_users = @number_format($user_weighted, 1);
        $rating_visitors = @number_format($visitors_weighted, 1);
        $total_users = @number_format($total_users / $i, 0);
        $total_visitors = @number_format($total_visitors / $i, 0);

        if ($rules != "") {
            $rating = @number_format($weighted, 1);
            $total_votes = @number_format($total_votes / $i, 0);
            $output["total"]["rating"] = $rating;
            $output["total"]["votes"] = $total_votes;
            $output["json"] = $votes_js;
        }

        $lastv = $last_voted ? ", last_voted = CURRENT_TIMESTAMP" : "";

        $sql = sprintf("update %sgdsr_multis_data set average_rating_users = '%s', average_rating_visitors = '%s', total_votes_users = '%s', total_votes_visitors = '%s'%s where post_id = %s and multi_id = %s",
            $table_prefix, $rating_users, $rating_visitors, $total_users, $total_visitors, $lastv, $post_id, $set_id);
        $wpdb->query($sql);

        $output["users"]["rating"] = $rating_users;
        $output["users"]["votes"] = $total_users;
        $output["visitors"]["rating"] = $rating_visitors;
        $output["visitors"]["votes"] = $total_visitors;

        return $output;
    }

    function clean_revision_articles() {
        global $wpdb, $table_prefix;
        $sql = sprintf("delete %s from %sgdsr_multis_data l inner join %sposts o on o.ID = l.post_id where o.post_type = 'revision'",
            gdFunctionsGDSR::mysql_pre_4_1() ? sprintf("%sgdsr_multis_data", $table_prefix) : "l",
            $table_prefix, $table_prefix);
        $wpdb->query($sql);
        $posts = $wpdb->rows_affected;

        $sql = sprintf("delete %s from %sgdsr_multis_values l left join %sgdsr_multis_data o on o.id = l.id where o.id is null",
            gdFunctionsGDSR::mysql_pre_4_1() ? sprintf("%sgdsr_multis_values", $table_prefix) : "l",
            $table_prefix, $table_prefix);
        $wpdb->query($sql);

        return $posts;
    }

    function clean_dead_articles() {
        global $wpdb, $table_prefix;
        $sql = sprintf("delete %s from %sgdsr_multis_data l left join %sposts o on o.ID = l.post_id where o.ID is null",
            gdFunctionsGDSR::mysql_pre_4_1() ? sprintf("%sgdsr_multis_data", $table_prefix) : "l",
            $table_prefix, $table_prefix);
        $wpdb->query($sql);
        $posts = $wpdb->rows_affected;

        $sql = sprintf("delete %s from %sgdsr_multis_values l left join %sgdsr_multis_data o on o.id = l.id where o.id is null",
            gdFunctionsGDSR::mysql_pre_4_1() ? sprintf("%sgdsr_multis_values", $table_prefix) : "l",
            $table_prefix, $table_prefix);
        $wpdb->query($sql);

        return $posts;
    }

    function delete_sets($ids) {
        global $wpdb, $table_prefix;
        $sql = sprintf("delete from %sgdsr_multis where multi_id in %s", $table_prefix, $ids);
        $wpdb->query($sql);
    }

    function save_vote($post_id, $set_id, $user_id, $ip, $ua, $values, $post_data, $comment_id = 0) {
        global $wpdb, $table_prefix;
        $ua = str_replace("'", "''", $ua);
        $ua = substr($ua, 0, 250);

        if ($post_data->moderate_articles == "" || $post_data->moderate_articles == "N" || ($post_data->moderate_articles == "V" && $user > 0) || ($post_data->moderate_articles == "U" && $user == 0)) {
            GDSRDBMulti::add_vote($post_id, $set_id, $user_id, $ip, $ua, $values);
            GDSRDBMulti::add_to_log($post_id, $set_id, $user_id, $ip, $ua, $values, $comment_id);
        }
        else {
            $modsql = sprintf("INSERT INTO %sgdsr_moderate (id, vote_type, multi_id, user_id, object, voted, ip, user_agent) VALUES (%s, 'multis', %s, %s, '%s', '%s', '%s', '%s')",
                $table_prefix, $post_id, $set_id, $user_id, serialize($values), str_replace("'", "''", current_time('mysql')), $ip, $ua);
            $wpdb->query($modsql);
        }
    }

    function add_to_log($post_id, $set_id, $user_id, $ip, $ua, $values, $comment_id = 0) {
        global $wpdb, $table_prefix;
        $set = wp_gdget_multi_set($set_id);
        $vote = intval(GDSRDBMulti::get_multi_rating_from_single_object($set, $values) * 10);

        $modsql = sprintf("INSERT INTO %sgdsr_votes_log (id, vote_type, multi_id, user_id, vote, object, voted, ip, user_agent, comment_id) VALUES (%s, 'multis', %s, %s, %s, '%s', '%s', '%s', '%s', %s)",
            $table_prefix, $post_id, $set_id, $user_id, $vote, serialize($values), str_replace("'", "''", current_time('mysql')), $ip, $ua, $comment_id);
        wp_gdsr_dump("LOG", $modsql);
        $wpdb->query($modsql);
    }

    function rating_from_comment($comment_id, $multi_set_id) {
        global $wpdb, $table_prefix;
        $sql = sprintf("select object from %sgdsr_votes_log where vote_type = 'multis' and comment_id = %s and multi_id = %s", $table_prefix, $comment_id, $multi_set_id);
        return $wpdb->get_var($sql);
    }

    function delete_by_comment($comment_id) {
        global $wpdb, $table_prefix;
        $sql = sprintf("select * from %sgdsr_votes_log where vote_type = 'multis' and comment_id = %s", $table_prefix, $comment_id);
        $row = $wpdb->get_row($sql);
        if (count($row) > 0) {
            $votes = unserialize($row->object);
            $sql = sprintf("select id from %sgdsr_multis_data where post_id = %s and multi_id = %s", $table_prefix, $row->id, $row->multi_id);
            $id = $wpdb->get_var($sql);

            if ($row->user_id == 0) $delstring = "visitor_votes = visitor_votes - %s, visitor_voters = visitor_voters - 1";
            else $delstring = "user_votes = user_votes - %s, user_voters = user_voters - 1";

            $i = 0;
            foreach ($votes as $vote) {
                $sql_set = sprintf($delstring, $vote);
                $sql = sprintf("update %sgdsr_multis_values set %s where source = 'dta' and id = %s and item_id = %s", $table_prefix, $sql_set, $id, $i);
                $wpdb->query($sql);
                $i++;
            }
            $set = gd_get_multi_set($row->multi_id);
            GDSRDBMulti::recalculate_multi_averages($row->id, $row->multi_id, "", $set, true);

            $sql = sprintf("delete from %sgdsr_votes_log where record_id = %s", $table_prefix, $row->record_id);
            $wpdb->query($sql);
        }
    }

    function add_vote($post_id, $set_id, $user_id, $ip, $ua, $votes) {
        global $wpdb, $table_prefix;
        $set = gd_get_multi_set($set_id);
        $data = $table_prefix.'gdsr_multis_data';
        $trend = $table_prefix.'gdsr_multis_trend';

        $trend_date = date("Y-m-d");

        $sql_trend = sprintf("SELECT id FROM %s WHERE vote_date = '%s' and post_id = %s and multi_id = %s", $trend, $trend_date, $post_id, $set_id);
        wp_gdsr_dump("TREND_CHECK", $sql_trend);
        $trend_data = intval($wpdb->get_var($sql_trend));
        wp_gdsr_dump("TREND_ID", $trend_data);

        $trend_added = false;
        if ($trend_data == 0) {
            $trend_added = true;
            $sql = sprintf("INSERT INTO %s (post_id, multi_id, vote_date) VALUES (%s, %s, '%s')", $trend, $post_id, $set_id, $trend_date);
            $wpdb->query($sql);
            $trend_id = $wpdb->insert_id;
        }
        else $trend_id = $trend_data;

        GDSRDBMulti::add_values($trend_id, $user_id, $votes, "trd", $trend_added ? "add" : "edit");
        GDSRDBMulti::recalculate_trend_averages($trend_id, $set);

        $data_id = GDSRDBMulti::get_vote($post_id, $set_id, count($set->object));

        GDSRDBMulti::add_values($data_id, $user_id, $votes);
    }

    function add_values($record_id, $user_id, $votes, $source = "dta", $operation = "edit") {
        global $wpdb, $table_prefix;
        $values = $table_prefix.'gdsr_multis_values';
        $cl_voters = $user_id == 0 ? "visitor_voters" : "user_voters";
        $cl_votes = $user_id == 0 ? "visitor_votes" : "user_votes";

        if ($operation == "add")
            $sql = sprintf("INSERT INTO %s (id, source, %s, %s, item_id) VALUES (%s, '%s', 1, %s, %s)",
                $values, $cl_voters, $cl_votes, $record_id, $source, "%s", "%s");
        else
            $sql = sprintf("UPDATE %s SET %s = %s + 1, %s = %s + %s WHERE id = %s and item_id = %s and source = '%s'",
                $values, $cl_voters, $cl_voters, $cl_votes, $cl_votes, "%s", $record_id, "%s", $source);

        $i = 0;
        foreach ($votes as $vote) {
            $sql_insert = sprintf($sql, $vote, $i);
            wp_gdsr_dump("SAVE_VOTE", $sql_insert);
            $wpdb->query($sql_insert);
            $i++;
        }
    }

    function get_values($id, $source = 'dta') {
        global $wpdb, $table_prefix;

        $sql = sprintf("SELECT * FROM %sgdsr_multis_values WHERE source = '%s' and id = %s ORDER BY item_id ASC", $table_prefix, $source, $id);
        return $wpdb->get_results($sql);
    }

    function add_empty_review_values($id, $values) {
        global $wpdb, $table_prefix;

        $values = intval($values);
        for ($i = 0; $i < $values; $i++) {
            $sql = sprintf("INSERT INTO %sgdsr_multis_values (id, source, item_id) VALUES (%s, 'rvw', %s)",
                $table_prefix, $id, $i);
            $wpdb->query($sql);
        }
    }

    function get_trend_values($id) {
        global $wpdb, $table_prefix;

        $sql = sprintf("select * from %sgdsr_multis_values where source = 'trd' and id = %s order by item_id asc",
            $table_prefix, $id);
        return $wpdb->get_results($sql);
    }

    function get_values_join($post_id, $set_id) {
        global $wpdb, $table_prefix;

        $sql = sprintf("select v.* from %sgdsr_multis_values v inner join %sgdsr_multis_data d on d.id = v.id where v.source = 'dta' and d.post_id = %s and d.multi_id = %s order by v.item_id asc",
            $table_prefix, $table_prefix, $post_id, $set_id);
        return $wpdb->get_results($sql);
    }

    function get_averages($post_id, $set_id) {
        global $wpdb, $table_prefix;

        $sql = sprintf("select * from %sgdsr_multis_data where post_id = %s and multi_id = %s", $table_prefix, $post_id, $set_id);
        return $wpdb->get_row($sql);
    }

    function get_vote($post_id, $set_id, $values) {
        global $wpdb, $table_prefix;

        $sql = sprintf("select id from %sgdsr_multis_data where post_id = %s and multi_id = %s", $table_prefix, $post_id, $set_id);
        $record_id = intval($wpdb->get_var($sql));

        if ($record_id == 0) {
            $sql = sprintf("INSERT INTO %sgdsr_multis_data (post_id, multi_id) VALUES (%s, %s)", $table_prefix, $post_id, $set_id);
            $wpdb->query($sql);
            $record_id = $wpdb->insert_id;
            for ($i = 0; $i < $values; $i++) {
                $sql = sprintf("INSERT INTO %sgdsr_multis_values (id, source, item_id) VALUES (%s, 'dta', %s)", $table_prefix, $record_id, $i);
                $wpdb->query($sql);
            }
        } else {
            $sql = sprintf("SELECT count(*) FROM %sgdsr_multis_values WHERE id = %s AND source = 'dta'", $table_prefix, $record_id);
            $counter = $wpdb->get_var($sql);
            if ($counter == 0) {
                for ($i = 0; $i < $values; $i++) {
                    $sql = sprintf("INSERT INTO %sgdsr_multis_values (id, source, item_id) VALUES (%s, 'dta', %s)", $table_prefix, $record_id, $i);
                    $wpdb->query($sql);
                }
            }
        }

        return $record_id;
    }

    function get_multi_review_average($record_id) {
        global $wpdb, $table_prefix;
        $sql = sprintf("select average_review from %sgdsr_multis_data where id = %s", $table_prefix, $record_id);
        return $wpdb->get_var($sql);
    }

    function save_review($record_id, $values = array()) {
        global $wpdb, $table_prefix;

        if (!is_array($values)) return;
        $sql = sprintf("DELETE FROM %sgdsr_multis_values where id = %s and source = 'rvw'", $table_prefix, $record_id);
        $wpdb->query($sql);
        for ($i = 0; $i < count($values); $i++) {
            $sql = sprintf("INSERT INTO %sgdsr_multis_values (id, source, item_id, user_voters, user_votes) VALUES (%s, 'rvw', %s, 1, '%s')",
                $table_prefix, $record_id, $i, $values[$i]);
            wp_gdsr_dump("INSERT", $sql);
            $wpdb->query($sql);
        }
    }

    function check_vote($id, $user, $set, $type, $ip, $mod_only = false, $mixed = false) {
        $result = true;

        if (!$mod_only)
            $result = GDSRDBMulti::check_vote_logged($id, $user, $set, $type, $ip, $mixed);
        if ($result)
            $result = GDSRDBMulti::check_vote_moderated($id, $user, $set, $type, $ip, $mixed);

        return $result;
    }

    function check_vote_logged($id, $user, $set, $type, $ip, $mixed = false) {
        return GDSRDBMulti::check_vote_table('gdsr_votes_log', $id, $user, $set, $type, $ip, $mixed);
    }

    function check_vote_moderated($id, $user, $set, $type, $ip, $mixed = false) {
        return GDSRDBMulti::check_vote_table('gdsr_moderate', $id, $user, $set, $type, $ip, $mixed);
    }

    function check_vote_table($table, $id, $user, $set, $type, $ip, $mixed = false) {
        global $wpdb, $table_prefix;

        if ($user > 0) {
            $votes_sql = sprintf("SELECT count(*) FROM %s WHERE vote_type = '%s' and multi_id = %s and id = %s and user_id = %s", $table_prefix.$table, $type, $set, $id, $user);
            $votes = $wpdb->get_var($votes_sql);
            return $votes == 0;
        }
        else {
            $votes_sql = sprintf("SELECT * FROM %s WHERE vote_type = '%s' and multi_id = %s and id = %s and ip = '%s'", $table_prefix.$table, $type, $set, $id, $ip);
            $votes = $wpdb->get_var($votes_sql);
            if ($votes > 0 && $mixed) {
                $votes_sql = sprintf("SELECT * FROM %s WHERE vote_type = '%s' and user_id > 0 and multi_id = %s and id = %s and ip = '%s'", $table_prefix.$table, $type, $set, $id, $ip);
                $votes_mixed = $wpdb->get_var($votes_sql);
                if ($votes_mixed > 0) $votes = 0;
            }
            return $votes == 0;
        }
    }

    function get_usage_count_posts($set_id) {
        global $wpdb, $table_prefix;
        return $wpdb->get_var(sprintf("select count(*) from %sgdsr_multis_data where (average_rating_visitors > 0 or average_rating_users > 0) and multi_id = %s", $table_prefix, $set_id));
    }

    function get_usage_count_post_reviews($set_id) {
        global $wpdb, $table_prefix;
        return $wpdb->get_var(sprintf("select count(*) from %sgdsr_multis_data where average_review > 0 and multi_id = %s", $table_prefix, $set_id));
    }

    function get_usage_count_voters($set_id) {
        global $wpdb, $table_prefix;
        $sql = sprintf("select count(*) from %sgdsr_votes_log where multi_id = %s and vote_type = 'multis'",
            $table_prefix, $set_id);
        return $wpdb->get_var($sql);
    }

    function get_multis_count() {
        global $wpdb, $table_prefix;
        return $wpdb->get_var(sprintf("select count(*) from %sgdsr_multis", $table_prefix));
    }

    function get_multis_tinymce() {
        global $wpdb, $table_prefix;
        $sql = sprintf("select multi_id as folder, name from %sgdsr_multis", $table_prefix);
        return $wpdb->get_results($sql);
    }

    function get_multis($start = 0, $limit = 20) {
        global $wpdb, $table_prefix;
        $sql = sprintf("select * from %sgdsr_multis order by multi_id desc limit %s, %s", $table_prefix, $start, $limit);
        return $wpdb->get_results($sql);
    }

    function get_multi_set($id = 0) {
        global $wpdb, $table_prefix;
        $where = $id > 0 ? " where multi_id = ".$id : "";
        $sql = sprintf("select * from %sgdsr_multis%s limit 0, 1", $table_prefix, $where);
        return $wpdb->get_row($sql);
    }

    function add_multi_set($set) {
        global $wpdb, $table_prefix;
        $sql = sprintf("insert into %sgdsr_multis (`name`, `description`, `stars`, `object`, `weight`, `auto_insert`, `auto_location`, `auto_categories`) values ('%s', '%s', %s, '%s', '%s', '%s', '%s', '%s')",
                $table_prefix, $set->name, $set->description, $set->stars, serialize($set->object), serialize($set->weight), $set->auto_insert, $set->auto_location, $set->auto_categories
            );
        $wpdb->query($sql);
        return $wpdb->insert_id;
    }

    function edit_multi_set($set) {
        global $wpdb, $table_prefix;

        $sql = sprintf("update %sgdsr_multis set `name` = '%s', `description` = '%s', `object` = '%s', `weight` = '%s', `auto_insert` = '%s', `auto_location` = '%s', `auto_categories` = '%s' where multi_id = %s",
                $table_prefix, $set->name, $set->description, serialize($set->object), serialize($set->weight), $set->auto_insert, $set->auto_location, $set->auto_categories, $set->multi_id
            );
        $wpdb->query($sql);
    }
}

?>