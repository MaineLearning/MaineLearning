<?php

class gdsrQuery {
    var $sort = '';
    var $type = '';
    var $order = '';
    var $set_id = 0;

    var $keys_sort = array(
        'rating',
        'review',
        'thumbs',
        'votes',
        'thumbs_votes',
        'last_voted'
    );

    var $keys_order = array(
        'desc',
        'asc'
    );

    function gdsrQuery() { }

    function query_vars($qvar) {
        $qvar[] = 'gdsr_sort';
        $qvar[] = 'gdsr_order';
        $qvar[] = 'gdsr_multi';
        $qvar[] = 'gdsr_fsvmin';
        $qvar[] = 'gdsr_ftvmin';

        return $qvar;
    }

    function pre_get_posts($wpq) {
        $this->sort = $wpq->get('gdsr_sort');
        $this->order = $wpq->get('gdsr_order');
        $this->set_id = intval($wpq->get('gdsr_multi'));
        $this->type = $this->set_id > 0 ? 'multis' : 'standard';
 
        if (in_array(strtolower($this->sort), $this->keys_sort)) {
            add_filter('posts_fields', array(&$this, $this->type.'_fields'));
            add_filter('posts_join', array(&$this, $this->type.'_join'));
            add_filter('posts_orderby', array(&$this, $this->type.'_orderby'));
            add_filter('posts_where', array(&$this, $this->type.'_where'));
        } else {
            remove_filter('posts_fields', array(&$this, $this->type.'_fields'));
            remove_filter('posts_join', array(&$this, $this->type.'_join'));
            remove_filter('posts_orderby', array(&$this, $this->type.'_orderby'));
            remove_filter('posts_where', array(&$this, $this->type.'_where'));
        }
    }

    function standard_fields($c) {
        $x = ", (gdsra.user_votes + gdsra.visitor_votes)/(gdsra.user_voters + gdsra.visitor_voters) as gdsr_rating";
        $x.= ", (gdsra.user_recc_plus - gdsra.user_recc_minus + gdsra.visitor_recc_plus - gdsra.visitor_recc_minus) as gdsr_thumb_score";
        $x.= ", (gdsra.user_voters + gdsra.visitor_voters) as gdsr_votes";
        $x.= ", (gdsra.user_recc_plus + gdsra.user_recc_minus + gdsra.visitor_recc_plus + gdsra.visitor_recc_minus) as gdsr_thumb_votes";
        $x.= ", gdsra.review as gdsr_review, gdsra.last_voted as gdsr_last_voted";

        $x = apply_filters("gdsr_wpquery_standard_fields", $x);

        return $c.$x;
    }

    function standard_join($c) {
        global $table_prefix;

        $x = sprintf(" LEFT JOIN %sgdsr_data_article gdsra ON gdsra.post_id = %sposts.ID", $table_prefix, $table_prefix);

        $x = apply_filters("gdsr_wpquery_standard_join", $x);

        return $c.$x;
    }

    function standard_where($c) {
        $x = '';

        $filter_min_votes = intval(trim(addslashes(get_query_var('gdsr_fsvmin'))));
        $filter_min_votes_thumbs = intval(trim(addslashes(get_query_var('gdsr_ftvmin'))));
        if ($filter_min_votes > 0) $x.= " AND (gdsra.user_voters + gdsra.visitor_voters) > ".$filter_min_votes;
        if ($filter_min_votes_thumbs > 0) $x.= " AND (gdsra.user_recc_plus + gdsra.user_recc_minus + gdsra.visitor_recc_plus + gdsra.visitor_recc_minus) > ".$filter_min_votes_thumbs;

        $x = apply_filters("gdsr_wpquery_standard_where", $x);

        return $c.$x;
    }

    function standard_orderby($default) {
        global $table_prefix;

        $order = in_array($this->order, $this->keys_order) ? $this->order : "desc";

        $c = '';
        switch ($this->sort) {
            case "thumbs":
                $c = sprintf(" (gdsra.user_recc_plus - gdsra.user_recc_minus + gdsra.visitor_recc_plus - gdsra.visitor_recc_minus) ".$order);
                $c.= sprintf(", (gdsra.user_recc_plus + gdsra.user_recc_minus + gdsra.visitor_recc_plus + gdsra.visitor_recc_minus) ".$order);
                break;
            case "rating":
                $c = sprintf(" (gdsra.user_votes + gdsra.visitor_votes)/(gdsra.user_voters + gdsra.visitor_voters) ".$order);
                $c.= sprintf(", (gdsra.user_voters + gdsra.visitor_voters) ".$order);
                break;
            case "thumbs_votes":
                $c = sprintf(" (gdsra.user_recc_plus + gdsra.user_recc_minus + gdsra.visitor_recc_plus + gdsra.visitor_recc_minus) ".$order);
                break;
            case "votes":
                $c = sprintf(" (gdsra.user_voters + gdsra.visitor_voters) ".$order);
                break;
            case "review":
                $c = sprintf(" gdsra.review ".$order);
                break;
            case "last_voted":
                $c = sprintf(" gdsra.last_voted ".$order);
                break;
        }

        if ($c != "") {
            $c.= sprintf(", %sposts.post_date desc", $table_prefix);
        }

        $c = apply_filters('gdsr_wpquery_standard_orderby', $c, $default);
        remove_filter('posts_orderby', array(&$this, 'standard_orderby'));

        return $c != '' ? $c : $default;
    }

    function multis_fields($c) {
        $x = ", (gdsrm.average_rating_users * gdsrm.total_votes_users + gdsrm.average_rating_visitors * gdsrm.total_votes_visitors)/(gdsrm.total_votes_users + gdsrm.total_votes_visitors) as gdsr_rating";
        $x.= ", (gdsrm.total_votes_users + gdsrm.total_votes_visitors) as gdsr_votes";
        $x.= ", gdsrm.average_review as gdsr_review, gdsrm.last_voted as gdsr_last_voted";

        $x = apply_filters("gdsr_wpquery_multis_fields", $x);

        return $c.$x;
    }

    function multis_join($c) {
        global $table_prefix;

        $x = sprintf(" LEFT JOIN %sgdsr_multis_data gdsrm ON gdsrm.post_id = %sposts.ID", $table_prefix, $table_prefix);
        $x = apply_filters("gdsr_wpquery_multis_join", $x);

        return $c.$x;
    }

    function multis_where($c) {
        $x = " AND (gdsrm.multi_id = ".$this->set_id.' OR gdsrm.multi_id is NULL)';
        $filter_min_votes = intval(trim(addslashes(get_query_var('gdsr_fsvmin'))));

        if ($filter_min_votes > 0) {
            $x.= " AND (gdsrm.total_votes_users + gdsrm.total_votes_visitors) > ".$filter_min_votes;
        }

        $x = apply_filters("gdsr_wpquery_multis_where", $x);
        return $c.$x;
    }

    function multis_orderby($default) {
        global $table_prefix;

        $order = in_array($this->order, $this->keys_order) ? $this->order : "desc";

        $c = '';
        switch ($this->sort) {
            case 'rating':
                $c = sprintf(" (gdsrm.average_rating_users * gdsrm.total_votes_users + gdsrm.average_rating_visitors * gdsrm.total_votes_visitors)/(gdsrm.total_votes_users + gdsrm.total_votes_visitors) ".$order);
                $c.= sprintf(", (gdsrm.total_votes_users + gdsrm.total_votes_visitors) ".$order);
                break;
            case 'votes':
                $c = sprintf(" (gdsrm.total_votes_users + gdsrm.total_votes_visitors) ".$order);
                break;
            case 'review':
                $c = sprintf(" gdsrm.average_review ".$order);
                break;
            case 'last_voted':
                $c = sprintf(" gdsrm.last_voted ".$order);
                break;
        }

        if ($c != "") {
            $c.= sprintf(", %sposts.post_date desc", $table_prefix);
        }

        $c = apply_filters('gdsr_wpquery_multis_orderby', $c, $default);
        remove_filter('posts_orderby', array(&$this, 'multis_orderby'));

        return $c != '' ? $c : $default;
    }
}

?>