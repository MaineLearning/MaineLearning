<?php

class gdsrShared {
    var $g;

    function gdsrShared($gdsr_main) {
        $this->g = $gdsr_main;
    }

    function render_multi_editor($settings) {
        $multi_id = $settings["multi_id"] == 0 ? $this->g->o["mur_review_set"] : $settings["multi_id"];
        $post_id = $settings["post_id"];
        $init_votes = $settings["votes"];
        $set = gd_get_multi_set($multi_id);
        if (is_null($set)) $set = gd_get_multi_set();

        $multi_id = !is_null($set) ? $set->multi_id : 0;
        if ($multi_id > 0 && $post_id > 0) {
            $vote_id = GDSRDBMulti::get_vote($post_id, $multi_id, count($set->object));
            $multi_data = GDSRDBMulti::get_values($vote_id, 'rvw');
            if (count($multi_data) == 0) {
                GDSRDBMulti::add_empty_review_values($vote_id, count($set->object));
                $multi_data = GDSRDBMulti::get_values($vote_id, 'rvw');
            }
        } else $multi_data = array();

        $votes = array();
        if (count($multi_data) > 0) {
            foreach ($multi_data as $md) {
                $single_vote = array();
                $single_vote["votes"] = 1;
                $single_vote["score"] = $md->user_votes;
                $single_vote["rating"] = $md->user_votes;
                $votes[] = $single_vote;
            }
        } else {
            for ($i = 0; $i < count($set->object); $i++) {
                $iv = isset($init_votes[$i]) ? $init_votes[$i] : 0;
                $votes[] = array("votes" => $iv == 0 ? 0 : 1,
                                 "score" => $iv == 0 ? 0 : $iv,
                                 "rating" => $iv == 0 ? 0 : $iv);
            }
        }

        if ($settings["admin"]) {
            include($this->g->plugin_path.'integrate/edit_multi.php');
        }
        else {
            return GDSRRenderT2::render_mre(intval($settings["tpl"]),
                array("post_id" => $settings["post_id"], "style" => $settings["style"],
                      "height" => $settings["size"], "votes" => $votes, "set" => $set,
                      "allow_vote" => true));
        }
    }
}

?>