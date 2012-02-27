<?php

class gdsrVotes {
    var $g;

    function gdsrVotes($gdsr_main) {
        $this->g = $gdsr_main;
    }

    function vote_thumbs_article($vote, $id, $tpl_id, $unit_width) {
        global $userdata;
        $ip = $_SERVER["REMOTE_ADDR"];
        $ua = $this->g->o["save_user_agent"] == 1 ? $_SERVER["HTTP_USER_AGENT"] : "";
        $user = is_object($userdata) ? $userdata->ID : 0;

        $vote = apply_filters("gdsr_vote_thumb_article_value", $vote, $id);

wp_gdsr_dump("VOTE_THUMB", "[POST: ".$id."] --".$vote."-- [".$user."] ".$unit_width."px");

        $allow_vote = $vote == "up" || $vote == "dw";
        if ($allow_vote) $allow_vote = gdsrFrontHelp::check_cookie($id, "artthumb");
        if ($allow_vote) $allow_vote = gdsrBlgDB::check_vote($id, $user, 'artthumb', $ip, $this->g->o["logged"] != 1, $this->g->o["allow_mixed_ip_votes"] == 1);

        $vote_value = $vote == "up" ? 1 : -1;
        if ($allow_vote) {
            gdsrBlgDB::save_vote_thumb($id, $user, $ip, $ua, $vote_value);
            gdsrFrontHelp::save_cookie($id, "artthumb");

            do_action("gdsr_vote_thumb_article", $id, $user, $vote_value);
        }

        $data = GDSRDatabase::get_post_data($id);
        $votes = $score = $votes_plus = $votes_minus = 0;

        if ($data->rules_articles == "A" || $data->rules_articles == "N") {
            $votes = $data->user_recc_plus + $data->user_recc_minus + $data->visitor_recc_plus + $data->visitor_recc_minus;
            $score = $data->user_recc_plus - $data->user_recc_minus + $data->visitor_recc_plus - $data->visitor_recc_minus;
            $votes_plus = $data->user_recc_plus + $data->visitor_recc_plus;
            $votes_minus = $data->user_recc_minus + $data->visitor_recc_minus;
        } else if ($data->rules_articles == "V") {
            $votes = $data->visitor_recc_plus + $data->visitor_recc_minus;
            $score = $data->visitor_recc_plus - $data->visitor_recc_minus;
            $votes_plus = $data->visitor_recc_plus;
            $votes_minus = $data->visitor_recc_minus;
        } else {
            $votes = $data->user_recc_plus + $data->user_recc_minus;
            $score = $data->user_recc_plus - $data->user_recc_minus;
            $votes_plus = $data->user_recc_plus;
            $votes_minus = $data->user_recc_minus;
        }

        $template = new gdTemplateRender($tpl_id, "TAB");
        $rt = GDSRRenderT2::render_tat_voted($template->dep["TAT"], array("votes" => $votes, "score" => $score, "votes_plus" => $votes_plus, "votes_minus" => $votes_minus, "id" => $id, "vote" => $vote_value));

        return '{ "status": "ok", "value": "'.$score.'", "rater": "'.str_replace('"', '\"', $rt).'" }';
    }

    function vote_thumbs_comment($vote, $id, $tpl_id, $unit_width) {
        global $userdata;
        $ip = $_SERVER["REMOTE_ADDR"];
        $ua = $this->g->o["save_user_agent"] == 1 ? $_SERVER["HTTP_USER_AGENT"] : "";
        $user = is_object($userdata) ? $userdata->ID : 0;

        $vote = apply_filters("gdsr_vote_thumb_comment_value", $vote, $id);

wp_gdsr_dump("VOTE THUMB", "[CMM: ".$id."] --".$vote."-- [".$user."] ".$unit_width."px");

        $allow_vote = $vote == "up" || $vote == "dw";
        if ($allow_vote) $allow_vote = gdsrFrontHelp::check_cookie($id, 'cmmthumb');
        if ($allow_vote) $allow_vote = gdsrBlgDB::check_vote($id, $user, 'cmmthumb', $ip, $this->g->o["cmm_logged"] != 1, $this->g->o["cmm_allow_mixed_ip_votes"] == 1);

        $vote_value = $vote == "up" ? 1 : -1;
        if ($allow_vote) {
            gdsrBlgDB::save_vote_comment_thumb($id, $user, $ip, $ua, $vote_value);
            gdsrFrontHelp::save_cookie($id, 'cmmthumb');

            do_action("gdsr_vote_thumb_comment", $id, $user, $vote_value);
        }

        $data = GDSRDatabase::get_comment_data($id);
        $post_data = GDSRDatabase::get_post_data($data->post_id);

        $votes = $score = $votes_plus = $votes_minus = 0;

        if ($post_data->rules_articles == "A" || $post_data->rules_articles == "N") {
            $votes = $data->user_recc_plus + $data->user_recc_minus + $data->visitor_recc_plus + $data->visitor_recc_minus;
            $score = $data->user_recc_plus - $data->user_recc_minus + $data->visitor_recc_plus - $data->visitor_recc_minus;
            $votes_plus = $data->user_recc_plus + $data->visitor_recc_plus;
            $votes_minus = $data->user_recc_minus + $data->visitor_recc_minus;
        } else if ($post_data->rules_articles == "V") {
            $votes = $data->visitor_recc_plus + $data->visitor_recc_minus;
            $score = $data->visitor_recc_plus - $data->visitor_recc_minus;
            $votes_plus = $data->visitor_recc_plus;
            $votes_minus = $data->visitor_recc_minus;
        } else {
            $votes = $data->user_recc_plus + $data->user_recc_minus;
            $score = $data->user_recc_plus - $data->user_recc_minus;
            $votes_plus = $data->user_recc_plus;
            $votes_minus = $data->user_recc_minus;
        }

        $template = new gdTemplateRender($tpl_id, "TCB");
        $rt = GDSRRenderT2::render_tct($template->dep["TCT"], array("votes" => $votes, "score" => $score, "votes_plus" => $votes_plus, "votes_minus" => $votes_minus, "id" => $id, "vote_value" => $vote_value));

        return '{ "status": "ok", "value": "'.$score.'", "rater": "'.str_replace('"', '\"', $rt).'" }';
    }

    function vote_multis($votes, $post_id, $set_id, $tpl_id, $size) {
        global $userdata;
        $ip = $_SERVER["REMOTE_ADDR"];
        if ($this->g->o["save_user_agent"] == 1) $ua = $_SERVER["HTTP_USER_AGENT"];
        else $ua = "";
        $user = is_object($userdata) ? $userdata->ID : 0;
        $data = GDSRDatabase::get_post_data($post_id);
        $set = gd_get_multi_set($set_id);

        $values = explode("X", $votes);
        $values = apply_filters("gdsr_vote_rating_multis_value", $values, $post_id, $set_id);

wp_gdsr_dump("VOTE_MUR", "[POST: ".$post_id."|SET: ".$set_id."] --".join("X", $values)."-- [".$user."]");

        $allow_vote = true;
        foreach ($values as $v) {
            if ($v > $set->stars) {
                $allow_vote = false;
                break;
            }
        }

        $vote_value = GDSRDBMulti::recalculate_multi_vote($values, $set);
        if ($allow_vote) $allow_vote = gdsrFrontHelp::check_cookie($post_id."#".$set_id, "multis");
        if ($allow_vote) $allow_vote = GDSRDBMulti::check_vote($post_id, $user, $set_id, 'multis', $ip, $this->g->o["logged"] != 1, $this->g->o["mur_allow_mixed_ip_votes"] == 1);

        $rating = 0;
        $total_votes = 0;
        $json = $summary = array();

        if ($allow_vote) {
            GDSRDBMulti::save_vote($post_id, $set_id, $user, $ip, $ua, $values, $data);
            $summary = GDSRDBMulti::recalculate_multi_averages($post_id, $set_id, $data->rules_articles, $set, true, $size);
            gdsrFrontHelp::save_cookie($post_id."#".$set_id, "multis");
            $rating = $summary["total"]["rating"];
            $total_votes = $summary["total"]["votes"];
            $json = $summary["json"];

            do_action("gdsr_vote_rating_multis", $post_id, $user, $set_id, $values, $rating);
        }

        $template = new gdTemplateRender($tpl_id, "MRB");
        $rt = GDSRRenderT2::render_srt_voted($template->dep["MRT"], array("rating" => $rating, "unit_count" => $set->stars, "votes" => $total_votes, "id" => $post_id, "vote" => $vote_value));

        $json = apply_filters("gdsr_vote_rating_multis_return", $json, $summary, $values);
        $enc_values = "[".join(",", $json)."]";

        return '{ "status": "ok", "values": '.$enc_values.', "rater": "'.str_replace('"', '\"', $rt).'", "average": "'.$rating.'" }';
    }

    function vote_article($votes, $id, $tpl_id, $unit_width) {
        global $userdata;
        $ip = $_SERVER["REMOTE_ADDR"];
        $ua = $this->g->o["save_user_agent"] == 1 ? $_SERVER["HTTP_USER_AGENT"] : "";
        $user = is_object($userdata) ? $userdata->ID : 0;

        $votes = apply_filters("gdsr_vote_rating_article_value", $votes, $id);
        $vote_value = $votes;

wp_gdsr_dump("VOTE", "[POST: ".$id."] --".$votes."-- [".$user."] ".$unit_width."px");

        $allow_vote = intval($votes) <= $this->g->o["stars"] && intval($votes) > 0;
        if ($allow_vote) $allow_vote = gdsrFrontHelp::check_cookie($id);
        if ($allow_vote) $allow_vote = gdsrBlgDB::check_vote($id, $user, 'article', $ip, $this->g->o["logged"] != 1, $this->g->o["allow_mixed_ip_votes"] == 1);

        if ($allow_vote) {
            gdsrBlgDB::save_vote($id, $user, $ip, $ua, $votes);
            gdsrFrontHelp::save_cookie($id);

            do_action("gdsr_vote_rating_article", $id, $user, $votes);
        }

        $data = GDSRDatabase::get_post_data($id);
        $unit_count = $this->g->o["stars"];
        $votes = $score = 0;

        if ($data->rules_articles == "A" || $data->rules_articles == "N") {
            $votes = $data->user_voters + $data->visitor_voters;
            $score = $data->user_votes + $data->visitor_votes;
        } else if ($data->rules_articles == "V") {
            $votes = $data->visitor_voters;
            $score = $data->visitor_votes;
        } else {
            $votes = $data->user_voters;
            $score = $data->user_votes;
        }

        if ($votes > 0) $rating2 = $score / $votes;
        else $rating2 = 0;
        $rating1 = @number_format($rating2, 1);
        $rating_width = @number_format($rating2 * $unit_width, 0);

        $template = new gdTemplateRender($tpl_id, "SRB");
        $rt = GDSRRenderT2::render_srt_voted($template->dep["SRT"], array("rating" => $rating1, "unit_count" => $unit_count, "votes" => $votes, "id" => $id, "vote" => $vote_value));

        $rating_width = apply_filters("gdsr_vote_rating_article_return", $rating_width, $unit_width, $rating1, $vote_value);

        return '{ "status": "ok", "value": "'.$rating_width.'", "rater": "'.str_replace('"', '\"', $rt).'" }';
    }

    function vote_comment($votes, $id, $tpl_id, $unit_width) {
        global $userdata;
        $user = is_object($userdata) ? $userdata->ID : 0;

        $ip = $_SERVER["REMOTE_ADDR"];
        if ($this->g->o["save_user_agent"] == 1) $ua = $_SERVER["HTTP_USER_AGENT"];
        else $ua = "";

        $votes = apply_filters("gdsr_vote_rating_comment_value", $votes, $id);
        $vote_value = $votes;

wp_gdsr_dump("VOTE_CMM", "[CMM: ".$id."] --".$votes."-- [".$user."] ".$unit_width."px");

        $allow_vote = intval($votes) <= $this->g->o["cmm_stars"] && intval($votes) > 0;
        if ($allow_vote) $allow_vote = gdsrFrontHelp::check_cookie($id, 'comment');
        if ($allow_vote) $allow_vote = gdsrBlgDB::check_vote($id, $user, 'comment', $ip, $this->g->o["cmm_logged"] != 1, $this->g->o["cmm_allow_mixed_ip_votes"] == 1);

        if ($allow_vote) {
            gdsrBlgDB::save_vote_comment($id, $user, $ip, $ua, $votes);
            gdsrFrontHelp::save_cookie($id, 'comment');

            do_action("gdsr_vote_rating_comment", $id, $user, $votes);
        }

        $data = GDSRDatabase::get_comment_data($id);
        $post_data = GDSRDatabase::get_post_data($data->post_id);
        $unit_count = $this->g->o["cmm_stars"];
        $votes = $score = 0;

        if ($post_data->rules_comments == "A" || $post_data->rules_comments == "N") {
            $votes = $data->user_voters + $data->visitor_voters;
            $score = $data->user_votes + $data->visitor_votes;
        } else if ($post_data->rules_comments == "V") {
            $votes = $data->visitor_voters;
            $score = $data->visitor_votes;
        } else {
            $votes = $data->user_voters;
            $score = $data->user_votes;
        }

        if ($votes > 0) $rating2 = $score / $votes;
        else $rating2 = 0;
        $rating1 = @number_format($rating2, 1);
        $rating_width = number_format($rating2 * $unit_width, 0);

        $template = new gdTemplateRender($tpl_id, "CRB");
        $rt = GDSRRenderT2::render_crt($template->dep["CRT"], array("rating" => $rating1, "unit_count" => $unit_count, "votes" => $votes, "vote_value" => $vote_value));

        $rating_width = apply_filters("gdsr_vote_rating_comment_return", $rating_width, $unit_width, $rating1, $vote_value);

        return '{ "status": "ok", "value": "'.$rating_width.'", "rater": "'.str_replace('"', '\"', $rt).'" }';
    }
}

?>