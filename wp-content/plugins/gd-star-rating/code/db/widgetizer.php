<?php

function gdsr_widget_convert_select($instance) {
    $select_post_types = array();
    if ($instance['select'] == "postpage") $select_post_types = array("post", "page");
    else $select_post_types = explode ("|", $instance['select']);
    return $select_post_types;
}

function gdsr_get_public_post_types() {
    global $gdsr;
    $post_types = array();
    if ($gdsr->wp_version > 29) {
        $options = array("public" => true);
        $types = get_post_types($options, "objects");
        foreach ($types as $id => $p) $post_types[$p->name] = $p->labels->name;
    } else {
        $post_types = array("post" => "Posts", "page" => "Pages");
    }
    return $post_types;
}

class GDSRX {
    function compile_query($query) {
        $sql = "select ".$query["select"]." from ".$query["from"];

        if (trim($query["where"]) != "") $sql.= " where ".$query["where"];
        if (trim($query["group"]) != "") $sql.= " group by ".$query["group"];
        if (trim($query["order"]) != "") $sql.= " order by ".$query["order"];
        if (trim($query["limit"]) != "") $sql.= " limit ".$query["limit"];

        return $sql;
    }

    function get_trend_data($ids, $grouping = "post", $type = "article", $period = "over", $last = 1, $over = 30, $multi_id = 0) {
        global $wpdb, $table_prefix;
        $strtodate = gdFunctionsGDSR::mysql_version();
        $strtodate = $strtodate == 4 ? $strtodate = "date_add(d.vote_date, interval 0 day)" : $strtodate = "str_to_date(d.vote_date, '%Y-%m-%d')";

        if ($period == "over") $where = sprintf("%s BETWEEN DATE_SUB(NOW(), INTERVAL %s DAY) AND DATE_SUB(NOW(), INTERVAL %s DAY)", $strtodate, $last + $over, $last);
        else $where = sprintf("%s BETWEEN DATE_SUB(NOW(), INTERVAL %s DAY) AND NOW()", $strtodate, $last);

        $from = $join = $sql = "";
        switch ($grouping) {
            case "post":
                $select = $type == "multis" ? "d.post_id" : "d.id";
                break;
            case "user":
                $select = "u.id";
                $join = "p.id = d.id and p.post_status = 'publish' and u.id = p.post_author and ";
                break;
            case "category":
                $select = "t.term_id";
                $join = "p.id = d.id and p.post_status = 'publish' and t.term_taxonomy_id = r.term_taxonomy_id and r.object_id = p.id and t.taxonomy = 'category' and t.term_id = x.term_id and ";
                break;
        }

        if ($type == "multis") {
            switch ($grouping) {
                case "post":
                    $from = sprintf("%sgdsr_multis_trend d", $table_prefix);
                    break;
                case "user":
                    $from = sprintf("%s u, %sposts p, %sgdsr_multis_trend d", $wpdb->users, $table_prefix, $table_prefix);
                    break;
                case "category":
                    $from = sprintf("%sterm_taxonomy t, %sterm_relationships r, %sterms x, %sposts p, %sgdsr_multis_trend d", $table_prefix, $table_prefix, $table_prefix, $table_prefix, $table_prefix);
                    break;
            }

            $sql = sprintf("SELECT %s as id, sum(d.total_votes_users) as user_voters, sum(d.average_rating_users * d.total_votes_users) as user_votes, sum(d.total_votes_visitors) as visitor_voters, sum(d.average_rating_visitors * d.total_votes_visitors) as visitor_votes FROM %s WHERE %s%s and %s in (%s) and multi_id = %s group by %s order by %s asc",
                $select, $from, $join, $where, $select, $ids, $multi_id, $select, $select);
        } else {
            switch ($grouping) {
                case "post":
                    $from = sprintf("%sgdsr_votes_trend d", $table_prefix);
                    break;
                case "user":
                    $from = sprintf("%s u, %sposts p, %sgdsr_votes_trend d", $wpdb->users, $table_prefix, $table_prefix);
                    break;
                case "category":
                    $from = sprintf("%sterm_taxonomy t, %sterm_relationships r, %sterms x, %sposts p, %sgdsr_votes_trend d", $table_prefix, $table_prefix, $table_prefix, $table_prefix, $table_prefix);
                    break;
            }

            $sql = sprintf("SELECT %s as id, sum(d.user_voters) as user_voters, sum(d.user_votes) as user_votes, sum(d.visitor_voters) as visitor_voters, sum(d.visitor_votes) as visitor_votes FROM %s WHERE %s%s and d.vote_type = '%s' AND %s IN (%s) GROUP BY %s ORDER BY %s asc",
                $select, $from, $join, $where, $type == "thumbs" ? "artthumb" : "article", $select, $ids, $select, $select);
        }

        return $wpdb->get_results($sql);
    }

    function get_trend_calculation($ids, $grouping = "post", $show = "total", $last = 1, $over = 30, $source = "article", $multi_id = 0) {
        global $wpdb, $table_prefix;
        $data_over = $data_last = $votes_over = $voters_over = array();
        $data_last = GDSRX::get_trend_data($ids, $grouping, $source, "last", $last, $over, $multi_id);
        $data_over = GDSRX::get_trend_data($ids, $grouping, $source, "over", $last, $over, $multi_id);

        if (count($data_last) == 0) $votes_last = $voters_last = array();
        if (count($data_over) == 0) $votes_over = $voters_over = array();

        for ($i = 0; $i < count($data_over); $i++) {
            $row_over = $data_over[$i];

            if ($show == "total") {
                $votes_over[$row_over->id] = $row_over->user_votes + $row_over->visitor_votes;
                $voters_over[$row_over->id] = $row_over->user_voters + $row_over->visitor_voters;
            }
            if ($show == "visitors") {
                $votes_over[$row_over->id] = $row_over->visitor_votes;
                $voters_over[$row_over->id] = $row_over->visitor_voters;
            }
            if ($show == "users") {
                $votes_over[$row_over->id] = $row_over->user_votes ;
                $voters_over[$row_over->id] = $row_over->user_voters;
            }
        }

        for ($i = 0; $i < count($data_last); $i++) {
            $row_last = $data_last[$i];
            
            if ($show == "total") {
                $votes_last[$row_last->id] = $row_last->user_votes + $row_last->visitor_votes;
                $voters_last[$row_last->id] = $row_last->user_voters + $row_last->visitor_voters;
            }
            if ($show == "visitors") {
                $votes_last[$row_last->id] = $row_last->visitor_votes;
                $voters_last[$row_last->id] = $row_last->visitor_voters;
            }
            if ($show == "users") {
                $votes_last[$row_last->id] = $row_last->user_votes ;
                $voters_last[$row_last->id] = $row_last->user_voters;
            }
        }

        foreach ($votes_last as $key => $value) {
            if (!isset($votes_over[$key])) {
                $votes_over[$key] = $voters_over[$key] = 0;
            }
        }

        foreach ($votes_over as $key => $value) {
            if (!isset($votes_last[$key])) {
                $votes_last[$key] = $voters_last[$key] = 0;
            }
        }

        $trends = array();
        foreach ($votes_last as $key => $value) {
            $trends[$key] = new TrendValue($votes_last[$key], $voters_last[$key], $votes_over[$key], $voters_over[$key], $last, $over);
        }
        
        return $trends;
    }

    function get_totals_thumbs($widget, $min = 0) {
        global $table_prefix;
        $where = array("p.id = d.post_id", "p.post_status = 'publish'");
        $select = "count(*) as count, 0 as voters, 0 as rating, 0 as bayes_rating, 0 as max_rating, 0 as percentage";

        if ($widget["show"] == "total") {
            $select.= ", (d.user_recc_plus + d.visitor_recc_plus - d.user_recc_minus - d.visitor_recc_minus) as score";
            $select.= ", (d.user_recc_plus + d.visitor_recc_plus + d.user_recc_minus + d.visitor_recc_minus) as votes";
            $where[] = "(d.user_recc_plus + d.visitor_recc_plus + d.user_recc_minus + d.visitor_recc_minus) > ".$min;
        }
        if ($widget["show"] == "visitors") {
            $select.= ", (d.visitor_recc_plus - d.visitor_recc_minus) as score";
            $select.= ", (d.visitor_recc_plus + d.visitor_recc_minus) as votes";
            $where[] = "(d.visitor_recc_plus + d.visitor_recc_minus) > ".$min;
        }
        if ($widget["show"] == "users") {
            $select.= ", (d.user_recc_plus - d.user_recc_minus) as score";
            $select.= ", (d.user_recc_plus + d.user_recc_minus) as votes";
            $where[] = "(d.user_recc_plus + d.user_recc_minus) > ".$min;
        }

        if ($widget["select"] != "" && $widget["select"] != "postpage")
            $where[] = "p.post_type = '".$widget["select"]."'";

        $query = array(
            "select" => $select,
            "from" => sprintf("%sposts p, %sgdsr_data_article d", $table_prefix, $table_prefix),
            "where" => join(" and ", $where),
            "group" => "",
            "order" => "",
            "limit" => ""
        );

        return $query;
    }

    function get_totals_standard($widget, $min = 0) {
        global $table_prefix;
        $where = array("p.id = d.post_id", "p.post_status = 'publish'");
        $select = "count(*) as count, 0 as rating, 0 as bayes_rating, 0 as max_rating, 0 as percentage";

        if ($widget["show"] == "total") {
            $select.= ", sum(d.user_voters) + sum(d.visitor_voters) as voters, sum(d.user_votes) + sum(d.visitor_votes) as votes";
            $where[] = "(d.user_voters + d.visitor_voters) > ".$min;
        }
        if ($widget["show"] == "visitors") {
            $select.= ", sum(d.visitor_voters) as voters, sum(d.visitor_votes) as votes";
            $where[] = "d.visitor_voters > ".$min;
        }
        if ($widget["show"] == "users") {
            $select.= ", sum(d.user_voters) as voters, sum(d.user_votes) as votes";
            $where[] = "d.user_voters > ".$min;
        }

        if ($widget["select"] != "" && $widget["select"] != "postpage")
            $where[] = "p.post_type = '".$widget["select"]."'";

        $query = array(
            "select" => $select,
            "from" => sprintf("%sposts p, %sgdsr_data_article d", $table_prefix, $table_prefix),
            "where" => join(" and ", $where),
            "group" => "",
            "order" => "",
            "limit" => ""
        );

        return $query;
    }

    function get_widget_multis($widget, $min = 0) {
        global $table_prefix;

        $grouping = $widget["grouping"];
        $cats = $widget["category"];
        $cats_in = $widget["category_toponly"] == 0;
        if ($cats_in && $cats != "0" && $cats != "") {
            $subs = gdWPGDSR::get_subcategories_ids($widget["category"]);
            $subs[] = $cats;
            $cats = join(",", $subs);
        }
        if ($widget["categories"] != "") {
            $cats = $widget["categories"];
            $cats_in = true;
        }

        $where = array();
        $select = $from = $group = "";

        if ($widget["bayesian_calculation"] == "0") $min = 0;
        if ($widget["min_votes"] > $min) $min = $widget["min_votes"];
        if ($min == 0 && $widget["hide_empty"] == "1") $min = 1;
        if ($widget["hide_noreview"] == "1") $where[] = "d.average_review > 0";

        $where[] = "p.id = d.post_id";
        $where[] = "d.multi_id = ".$widget["source_set"];
        $where[] = "p.post_status = 'publish'";

        $extras = ", 0 as votes, 0 as voters, 0 as rating, 0 as bayesian, '' as item_trend_rating, '' as item_trend_voting, '' as permalink, '' as tense, '' as rating_stars, '' as bayesian_stars, '' as review_stars";

        if (($cats != "" && $cats != "0") || $grouping == 'category'){
            $from = sprintf("%sterm_taxonomy t, %sterm_relationships r, ", $table_prefix, $table_prefix);
            $where[] = "t.term_taxonomy_id = r.term_taxonomy_id";
            $where[] = "r.object_id = p.id";
        }
        if ($cats != "" && $cats != "0") {
            $where[] = "t.taxonomy = 'category'";
            if ($cats_in) $where[] = "t.term_id in (".$cats.")";
            else $where[] = "t.term_id = ".$cats;
        }

        $col_id = "p.id";
        $col_title = "p.post_title";
        if ($grouping == 'taxonomy') {
            $from.= sprintf("%sterm_taxonomy tt, %sterm_relationships tr, %sterms tx, ", $table_prefix, $table_prefix, $table_prefix);
            $where[] = "tt.taxonomy = '".$widget["taxonomy"]."'";
            $where[] = "tt.term_taxonomy_id = tr.term_taxonomy_id";
            $where[] = "tt.term_id = tx.term_id";
            $where[] = "tr.object_id = p.id";
            $select = "tx.name as title, tx.term_id, tx.slug, count(*) as counter, sum(d.average_rating_users * d.total_votes_users) as user_votes, sum(d.average_rating_visitors * d.total_votes_visitors) as visitor_votes, sum(d.total_votes_users) as user_voters, sum(d.total_votes_visitors) as visitor_voters";
            $select.= ", sum(d.average_review) as review";
            $group = $col_id = "tt.term_id";
            $col_title = "tx.name";
        } else if ($grouping == 'category') {
            $from.= sprintf("%sterms x, ", $table_prefix);
            $where[] = "t.taxonomy = 'category'";
            $where[] = "t.term_id = x.term_id";
            $select = "x.name as title, x.term_id, x.slug, count(*) as counter, sum(d.average_rating_users * d.total_votes_users) as user_votes, sum(d.average_rating_visitors * d.total_votes_visitors) as visitor_votes, sum(d.total_votes_users) as user_voters, sum(d.total_votes_visitors) as visitor_voters";
            $select.= ", sum(d.average_review) as review";
            $group = $col_id = "t.term_id";
            $col_title = "x.name";
        } else if ($grouping == 'user') {
            $from.= sprintf("%s u, ", $wpdb->users);
            $where[] = "u.id = p.post_author";
            $select = "u.display_name as title, u.user_nicename as slug, u.id, count(*) as counter, sum(d.average_rating_users * d.total_votes_users) as user_votes, sum(d.average_rating_visitors * d.total_votes_visitors) as visitor_votes, sum(d.total_votes_users) as user_voters, sum(d.total_votes_visitors) as visitor_voters";
            $select.= ", sum(d.average_review) as review";
            $group = $col_id = "u.id";
            $col_title = "u.display_name";
        } else {
            $select = "p.id as post_id, p.post_name as slug, p.post_author as author, p.post_title as title, p.post_type, p.post_date, d.*, 1 as counter, d.average_rating_users * d.total_votes_users as user_votes, d.average_rating_visitors * d.total_votes_visitors as visitor_votes, d.total_votes_users as user_voters, d.total_votes_visitors as visitor_voters";
            $select.= ", d.average_review as review";
        }

        if ($grouping != 'post' && $widget["min_count"] > 0)
            $group.= " having count(*) >= ".$widget["min_count"];

        if (is_array($widget["select"])) {
            $where[] = "p.post_type in ('".join("', '", $widget["select"])."')";
        } else {
            if ($widget["select"] != "" && $widget["select"] != "postpage")
                $where[] = "p.post_type = '".$widget["select"]."'";
        }

        if ($min > 0) {
            if ($widget["show"] == "total") $where[] = "(d.total_votes_users + d.total_votes_visitors) >= ".$min;
            if ($widget["show"] == "visitors") $where[] = "d.total_votes_visitors >= ".$min;
            if ($widget["show"] == "users") $where[] = "d.total_votes_users >= ".$min;
        }

        if ($widget["order"] == "desc" || $widget["order"] == "asc")
            $sort = $widget["order"];
        else
            $sort = "desc";

        if ($widget["last_voted_days"] == "") $widget["last_voted_days"] = 0;
        if ($widget["last_voted_days"] > 0) {
            $where[] = "TO_DAYS(CURDATE()) - ".$widget["last_voted_days"]." <= TO_DAYS(d.last_voted)";
        }

        if ($widget["publish_date"] == "range") {
            $where[] = "p.post_date >= '".$widget["publish_range_from"]."' and p.post_date <= '".$widget["publish_range_to"]."'";
        } else if ($widget["publish_date"] == "month") {
            $month = $widget["publish_month"];
            if ($month != "" && $month != "0") {
                $where[] = "year(p.post_date) = ".substr($month, 0, 4);
                $where[] = "month(p.post_date) = ".substr($month, 4, 2);
            }
        } else if ($widget["publish_date"] == "lastd") {
            if ($widget["publish_days"] > 0)
                $where[] = "TO_DAYS(CURDATE()) - ".$widget["publish_days"]." <= TO_DAYS(p.post_date)";
        }
        $select = "p.post_content, p.post_excerpt, '' as content, '' as excerpt, ".$select;

        $col = $widget["column"];
        if ($col == "title") $col = $col_title;
        else if ($col == "review") $col = "d.average_review";
        else if ($col == "rating" || $col == "bayesian") {
            if ($widget["show"] == "total") $col = "(d.average_rating_users * d.total_votes_users + d.average_rating_visitors * d.total_votes_visitors)/(d.total_votes_users + d.total_votes_visitors)";
            if ($widget["show"] == "visitors") $col = "(d.average_rating_visitors * d.total_votes_visitors)/d.total_votes_visitors";
            if ($widget["show"] == "users") $col = "(d.average_rating_users * d.total_votes_users)/d.total_votes_users";
        } else if ($col == "voters") {
            if ($widget["show"] == "total") $col = "d.total_votes_users + d.total_votes_visitors";
            if ($widget["show"] == "visitors") $col = "d.total_votes_visitors";
            if ($widget["show"] == "users") $col = "d.total_votes_users";
        }
        else if ($col == "counter" && $grouping != "post") $col = "count(*)";
        else $col = $col_id;

        $query = array(
            "select" => "distinct ".$select.$extras,
            "from" => sprintf("%s%sposts p, %sgdsr_multis_data d", $from, $table_prefix, $table_prefix),
            "where" => join(" and ", $where),
            "group" => $group,
            "order" => sprintf("%s %s", $col, $sort),
            "limit" => "0, ".$widget["rows"]
        );

        return $query;
    }

    function get_widget_thumbs($widget, $min = 0) {
        global $table_prefix;

        $grouping = $widget["grouping"];
        $cats = $widget["category"];
        $cats_in = $widget["category_toponly"] == 0;
        $select = $from = $group = "";
        $where = array("p.id = d.post_id", "p.post_status = 'publish'");
        $extras = ", 0 as votes, 0 as voters, 0 as rating, 0 as bayesian, '' as item_trend_rating, '' as item_trend_voting, '' as permalink, '' as tense, '' as rating_stars, '' as bayesian_stars, '' as review_stars";

        if ($cats_in && $cats != "0") {
            $subs = gdWPGDSR::get_subcategories_ids($widget["category"]);
            $subs[] = $cats;
            $cats = join(",", $subs);
        }

        if ($widget["categories"] != "") {
            $cats = $widget["categories"];
            $cats_in = true;
        }

        if ($widget["bayesian_calculation"] == "0") $min = 0;
        if ($widget["min_votes"] > $min) $min = $widget["min_votes"];
        if ($min == 0 && $widget["hide_empty"] == "1") $min = 1;

        if (($cats != "" && $cats != "0") || $grouping == 'category'){
            $from = sprintf("%sterm_taxonomy t, %sterm_relationships r, ", $table_prefix, $table_prefix);
            $where[] = "t.term_taxonomy_id = r.term_taxonomy_id";
            $where[] = "r.object_id = p.id";
        }
        if ($cats != "" && $cats != "0") {
            $where[] = "t.taxonomy = 'category'";
            if ($cats_in) $where[] = "t.term_id in (".$cats.")";
            else $where[] = "t.term_id = ".$cats;
        }

        $col_id = "p.id";
        $col_title = "p.post_title";
        if ($grouping == 'taxonomy') {
            $from.= sprintf("%sterm_taxonomy tt, %sterm_relationships tr, %sterms tx, ", $table_prefix, $table_prefix, $table_prefix);
            $where[] = "tt.taxonomy = '".$widget["taxonomy"]."'";
            $where[] = "tt.term_taxonomy_id = tr.term_taxonomy_id";
            $where[] = "tt.term_id = tx.term_id";
            $where[] = "tr.object_id = p.id";
            $select = "tx.name as title, tx.term_id, tx.slug, count(*) as counter, sum(d.user_recc_plus) as user_recc_plus, sum(d.visitor_recc_plus) as visitor_recc_plus, sum(d.user_recc_minus) as user_recc_minus, sum(d.visitor_recc_minus) as visitor_recc_minus";
            $select.= ", sum(d.review) as review";
            $group = $col_id = "tt.term_id";
            $col_title = "tx.name";
        } else if ($grouping == 'category') {
            $from.= sprintf("%sterms x, ", $table_prefix);
            $where[] = "t.taxonomy = 'category'";
            $where[] = "t.term_id = x.term_id";
            $select = "x.name as title, x.term_id, x.slug, count(*) as counter, sum(d.user_recc_plus) as user_recc_plus, sum(d.visitor_recc_plus) as visitor_recc_plus, sum(d.user_recc_minus) as user_recc_minus, sum(d.visitor_recc_minus) as visitor_recc_minus";
            $select.= ", sum(d.review) as review";
            $group = $col_id = "t.term_id";
            $col_title = "x.name";
        } else if ($grouping == 'user') {
            $from.= sprintf("%s u, ", $wpdb->users);
            $where[] = "u.id = p.post_author";
            $select = "u.display_name as title, u.user_nicename as slug, u.id, count(*) as counter, sum(d.user_recc_plus) as user_recc_plus, sum(d.visitor_recc_plus) as visitor_recc_plus, sum(d.user_recc_minus) as user_recc_minus, sum(d.visitor_recc_minus) as visitor_recc_minus";
            $select.= ", sum(d.review) as review";
            $group = $col_id = "u.id";
            $col_title = "u.display_name";
        } else {
            $select = "p.id as post_id, p.post_name as slug, p.post_author as author, p.post_title as title, p.post_type, p.post_date, d.*, 1 as counter";
        }

        if ($grouping != 'post' && $widget["min_count"] > 0)
            $group.= " having count(*) >= ".$widget["min_count"];

        if (is_array($widget["select"])) {
            $where[] = "p.post_type in ('".join("', '", $widget["select"])."')";
        } else {
            if ($widget["select"] != "" && $widget["select"] != "postpage")
                $where[] = "p.post_type = '".$widget["select"]."'";
        }

        if ($min > 0) {
            if ($widget["show"] == "total") $where[] = "(d.user_recc_plus + d.user_recc_minus + d.visitor_recc_plus + d.visitor_recc_minus) >= ".$min;
            if ($widget["show"] == "visitors") $where[] = "(d.visitor_recc_plus + d.visitor_recc_minus) >= ".$min;
            if ($widget["show"] == "users") $where[] = "(d.user_recc_plus + d.user_recc_minus) >= ".$min;
        }
        if ($widget["hide_noreview"] == "1") $where[] = "d.review > -1";

        $sort = ($widget["order"] == "desc" || $widget["order"] == "asc") ? $widget["order"] : $sort = "desc";

        if ($widget["last_voted_days"] == "") $widget["last_voted_days"] = 0;
        if ($widget["last_voted_days"] > 0) {
            $where[] = "TO_DAYS(CURDATE()) - ".$widget["last_voted_days"]." <= TO_DAYS(d.last_voted_recc)";
        }

        if ($widget["publish_date"] == "range") {
            $where[] = "p.post_date >= '".$widget["publish_range_from"]."' and p.post_date <= '".$widget["publish_range_to"]."'";
        } else if ($widget["publish_date"] == "month") {
            $month = $widget["publish_month"];
            if ($month != "" && $month != "0") {
                $where[] = "year(p.post_date) = ".substr($month, 0, 4);
                $where[] = "month(p.post_date) = ".substr($month, 4, 2);
            }
        } else if ($widget["publish_date"] == "lastd") {
            if ($widget["publish_days"] > 0)
                $where[] = "TO_DAYS(CURDATE()) - ".$widget["publish_days"]." <= TO_DAYS(p.post_date)";
        }
        $select = "p.post_content, p.post_excerpt, '' as content, '' as excerpt, ".$select;

        $col = $widget["column"];
        if ($col == "title") $col = $col_title;
        else if ($col == "review") $col = "d.review";
        else if ($col == "rating" || $col == "bayesian") {
            if ($widget["show"] == "total") $col = "d.user_recc_plus - d.user_recc_minus + d.visitor_recc_plus - d.visitor_recc_minus";
            if ($widget["show"] == "visitors") $col = "d.visitor_recc_plus - d.visitor_recc_minus";
            if ($widget["show"] == "users") $col = "d.user_recc_plus - d.user_recc_minus";
        }
        else if ($col == "voters") {
            if ($widget["show"] == "total") $col = "d.user_recc_plus + d.user_recc_minus + d.visitor_recc_plus + d.visitor_recc_minus";
            if ($widget["show"] == "visitors") $col = "d.visitor_recc_plus + d.visitor_recc_minus";
            if ($widget["show"] == "users") $col = "d.user_recc_plus + d.user_recc_minus";
        }
        else if ($col == "counter" && $grouping != "post") $col = "count(*)";
        else $col = $col_id;
        $ordering = sprintf("order by %s %s", $col, $sort);

        $query = array(
            "select" => "distinct ".$select.$extras,
            "from" => sprintf("%s%sposts p, %sgdsr_data_article d", $from, $table_prefix, $table_prefix),
            "where" => join(" and ", $where),
            "group" => $group,
            "order" => sprintf("%s %s", $col, $sort),
            "limit" => "0, ".$widget["rows"]
        );

        return $query;
    }

    function get_widget_standard($widget, $min = 0) {
        global $wpdb, $table_prefix;

        $grouping = $widget["grouping"];
        $cats = $widget["category"];
        $cats_in = $widget["category_toponly"] == 0;
        $select = $from = $group = "";
        $where = array("p.id = d.post_id", "p.post_status = 'publish'");
        $extras = ", 0 as votes, 0 as voters, 0 as rating, 0 as bayesian, '' as item_trend_rating, '' as item_trend_voting, '' as permalink, '' as tense, '' as rating_stars, '' as bayesian_stars, '' as review_stars";

        if ($cats_in && $cats != "0") {
            $subs = gdWPGDSR::get_subcategories_ids($widget["category"]);
            $subs[] = $cats;
            $cats = join(",", $subs);
        }

        if (isset($widget["categories"]) && $widget["categories"] != "") {
            $cats = $widget["categories"];
            $cats_in = true;
        }

        if ($widget["bayesian_calculation"] == "0") $min = 0;
        if ($widget["min_votes"] > $min) $min = $widget["min_votes"];
        if ($min == 0 && $widget["hide_empty"] == "1") $min = 1;

        if (($cats != "" && $cats != "0") || $grouping == 'category'){
            $from = sprintf("%sterm_taxonomy t, %sterm_relationships r, ", $table_prefix, $table_prefix);
            $where[] = "t.term_taxonomy_id = r.term_taxonomy_id";
            $where[] = "r.object_id = p.id";
        }
        if ($cats != "" && $cats != "0") {
            $where[] = "t.taxonomy = 'category'";
            if ($cats_in) $where[] = "t.term_id in (".$cats.")";
            else $where[] = "t.term_id = ".$cats;
        }

        $col_id = "p.id";
        $col_title = "p.post_title";
        if ($grouping == 'taxonomy') {
            $from.= sprintf("%sterm_taxonomy tt, %sterm_relationships tr, %sterms tx, ", $table_prefix, $table_prefix, $table_prefix);
            $where[] = "tt.taxonomy = '".$widget["taxonomy"]."'";
            $where[] = "tt.term_taxonomy_id = tr.term_taxonomy_id";
            $where[] = "tt.term_id = tx.term_id";
            $where[] = "tr.object_id = p.id";
            $select = "tx.name as title, tx.term_id, tx.slug, count(*) as counter, sum(d.user_votes) as user_votes, sum(d.visitor_votes) as visitor_votes, sum(d.user_voters) as user_voters, sum(d.visitor_voters) as visitor_voters";
            $select.= ", sum(d.review) as review";
            $group = $col_id = "tt.term_id";
            $col_title = "tx.name";
        } else if ($grouping == 'category') {
            $from.= sprintf("%sterms x, ", $table_prefix);
            $where[] = "t.taxonomy = 'category'";
            $where[] = "t.term_id = x.term_id";
            $select = "x.name as title, x.term_id, x.slug, count(*) as counter, sum(d.user_votes) as user_votes, sum(d.visitor_votes) as visitor_votes, sum(d.user_voters) as user_voters, sum(d.visitor_voters) as visitor_voters";
            $select.= ", sum(d.review) as review";
            $group = $col_id = "t.term_id";
            $col_title = "x.name";
        } else if ($grouping == 'user') {
            $from.= sprintf("%s u, ", $wpdb->users);
            $where[] = "u.id = p.post_author";
            $select = "u.display_name as title, u.user_nicename as slug, u.id, count(*) as counter, sum(d.user_votes) as user_votes, sum(d.visitor_votes) as visitor_votes, sum(d.user_voters) as user_voters, sum(d.visitor_voters) as visitor_voters";
            $select.= ", sum(d.review) as review";
            $group = $col_id = "u.id";
            $col_title = "u.display_name";
        } else {
            $select = "p.id as post_id, p.post_name as slug, p.post_author as author, p.post_title as title, p.post_type, p.post_date, d.*, 1 as counter";
        }

        if ($grouping != 'post' && $widget["min_count"] > 0)
            $group.= " having count(*) >= ".$widget["min_count"];

        if (is_array($widget["select"])) {
            $where[] = "p.post_type in ('".join("', '", $widget["select"])."')";
        } else {
            if ($widget["select"] != "" && $widget["select"] != "postpage")
                $where[] = "p.post_type = '".$widget["select"]."'";
        }

        if ($min > 0) {
            if ($widget["show"] == "total") $where[] = "(d.user_voters + d.visitor_voters) >= ".$min;
            if ($widget["show"] == "visitors") $where[] = "d.visitor_voters >= ".$min;
            if ($widget["show"] == "users") $where[] = "d.user_voters >= ".$min;
        }
        if ($widget["hide_noreview"] == "1") $where[] = "d.review > -1";

        $sort = ($widget["order"] == "desc" || $widget["order"] == "asc") ? $widget["order"] : $sort = "desc";

        if ($widget["last_voted_days"] == "") $widget["last_voted_days"] = 0;
        if ($widget["last_voted_days"] > 0) {
            $where[] = "TO_DAYS(CURDATE()) - ".$widget["last_voted_days"]." <= TO_DAYS(d.last_voted)";
        }

        if ($widget["publish_date"] == "range") {
            $where[] = "p.post_date >= '".$widget["publish_range_from"]."' and p.post_date <= '".$widget["publish_range_to"]."'";
        } else if ($widget["publish_date"] == "month") {
            $month = $widget["publish_month"];
            if ($month != "" && $month != "0") {
                $where[] = "year(p.post_date) = ".substr($month, 0, 4);
                $where[] = "month(p.post_date) = ".substr($month, 4, 2);
            }
        } else if ($widget["publish_date"] == "lastd") {
            if ($widget["publish_days"] > 0)
                $where[] = "TO_DAYS(CURDATE()) - ".$widget["publish_days"]." <= TO_DAYS(p.post_date)";
        }
        $select = "p.post_content, p.post_excerpt, '' as content, '' as excerpt, ".$select;

        $col = $widget["column"];
        if ($col == "title") $col = $col_title;
        else if ($col == "review") $col = "d.review";
        else if ($col == "rating" || $col == "bayesian") {
            if ($widget["show"] == "total") $col = "(d.user_votes + d.visitor_votes)/(d.user_voters + d.visitor_voters)";
            if ($widget["show"] == "visitors") $col = "d.visitor_votes/d.visitor_voters";
            if ($widget["show"] == "users") $col = "d.user_votes/d.user_voters";
        } else if ($col == "voters") {
            if ($widget["show"] == "total") $col = "d.user_votes + d.visitor_votes";
            if ($widget["show"] == "visitors") $col = "d.visitor_votes";
            if ($widget["show"] == "users") $col = "d.user_votes";
        } else if ($col == "counter" && $grouping != "post") $col = "count(*)";
        else $col = $col_id;
        $ordering = sprintf("order by %s %s", $col, $sort);

        $query = array(
            "select" => "distinct ".$select.$extras,
            "from" => sprintf("%s%sposts p, %sgdsr_data_article d", $from, $table_prefix, $table_prefix),
            "where" => join(" and ", $where),
            "group" => $group,
            "order" => sprintf("%s %s", $col, $sort),
            "limit" => "0, ".$widget["rows"]
        );

        return $query;
    }

    function get_widget_comments($widget, $post_id) {
        global $table_prefix;

        $where = array();
        $select = "p.comment_id, p.comment_author, p.comment_author_email, p.comment_author_url, p.comment_date, p.comment_content, p.user_id, d.*";
        $extras = ", 0 as votes, 0 as voters, 0 as rating, '' as permalink, '' as tense, '' as rating_stars";
        $min = $widget["min_votes"];
        if ($min == 0 && $widget["hide_empty"] == "1") $min = 1;

        $where[] = "d.post_id = ".$post_id;
        $where[] = "p.comment_id = d.comment_id";

        if ($min > 0) {
            if ($widget["show"] == "total") $where[] = "(d.user_voters + d.visitor_voters) >= ".$min;
            if ($widget["show"] == "visitors") $where[] = "d.visitor_voters >= ".$min;
            if ($widget["show"] == "users") $where[] = "d.user_voters >= ".$min;
        }

        if ($widget["order"] == "desc" || $widget["order"] == "asc")
            $sort = $widget["order"];
        else
            $sort = "desc";

        if ($widget["last_voted_days"] == "") $widget["last_voted_days"] = 0;
        if ($widget["last_voted_days"] > 0) {
            $where[] = "TO_DAYS(CURDATE()) - ".$widget["last_voted_days"]." <= TO_DAYS(d.last_voted)";
        }

        $sql = sprintf("select distinct %s%s from %scomments p, %sgdsr_data_comment d where %s limit 0, %s",
                $select, $extras, $table_prefix, $table_prefix, join(" and ", $where), $widget["rows"]);

        return $sql;
    }
}

class TrendValue {
    var $votes_last = 0;
    var $voters_last = 0;
    var $rating_last = 0;
    var $votes_over = 0;
    var $voters_over = 0;
    var $rating_over = 0;

    var $trend_rating = 0;
    var $trend_voting = 0;
    var $day_rate_voters = 0;

    function TrendValue($v_last, $r_last, $v_over, $r_over, $last = 1, $over = 30) {
        $this->votes_last = $v_last;
        $this->voters_last = $r_last;
        $this->votes_over = $v_over;
        $this->voters_over = $r_over;

        if ($over > 0) $this->day_rate_voters = $last / $over;

        $this->Calculate();
    }

    function Calculate() {
        if ($this->voters_last > 0) $this->rating_last = @number_format($this->votes_last / $this->voters_last, 1);
        if ($this->voters_over > 0) $this->rating_over = @number_format($this->votes_over / $this->voters_over, 1);

        if ($this->rating_last > $this->rating_over ) $this->trend_rating = 1;
        else if ($this->rating_last < $this->rating_over ) $this->trend_rating = -1;

        if ($this->voters_last > ($this->voters_over * $this->day_rate_voters)) $this->trend_voting = 1;
        else if ($this->voters_last < ($this->voters_over * $this->day_rate_voters)) $this->trend_voting = -1;
    }
}

?>