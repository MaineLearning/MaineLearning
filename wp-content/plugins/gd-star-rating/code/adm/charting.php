<?php

class gdsrDBChart {
    function prepare_data_daily($data) {
        $i = 0;
        $rating = $votes = $ticks = array();
        foreach ($data as $day => $values) {
            $ticks[] = sprintf("[%s, '%s']", $i, (floor($i / 4) == $i / 4) ? $day : "");
            $rating[] = sprintf("[%s, %s]", $i, $values["rating"]);
            $votes[] = sprintf("[%s, %s]", $i, $values["votes"]);
            $i++;
        }

        return sprintf("var gdr_rating = [%s]; var gdr_votes = [%s]; var gdr_ticks = [%s];",
            join(", ", $rating), join(", ", $votes), join(", ", $ticks));
    }

    function votes_counter($vote_type = 'article') {
        global $wpdb, $table_prefix;
        $sql = sprintf("SELECT vote, count(*) as counter FROM %sgdsr_votes_log where vote_type = '%s' group by vote order by vote desc", $table_prefix, $vote_type);
        return $wpdb->get_results($sql);
    }

    function trends_daily($id, $vote_type = 'article', $show = "", $days = 30) {
        global $wpdb, $table_prefix;
        $mysql4_strtodate = "date_add(vote_date, interval 0 day)";
        $mysql5_strtodate = "str_to_date(vote_date, '%Y-%m-%d')";

        $strtodate = "";
        $voters = $votes = 0;
        switch(gdFunctionsGDSR::mysql_version()) {
            case "4":
                $strtodate = $mysql4_strtodate;
                break;
            case "5":
            default:
                $strtodate = $mysql5_strtodate;
                break;
        }
        $sql = sprintf("SELECT user_voters, user_votes, visitor_voters, visitor_votes, %s as vote_date FROM %sgdsr_votes_trend where vote_type = '%s' and id = %s and %s between DATE_SUB(NOW(), INTERVAL %s DAY) AND NOW() order by vote_date asc",
            $strtodate, $table_prefix, $vote_type, $id, $strtodate, $days);
        $results = $wpdb->get_results($sql);
        $data = array();
        for ($i = $days; $i > 0; $i--) {
            $day = date('Y-m-d', mktime(0, 0, 0, date("m"), date("d") - $i, date("Y")));
            $data[$day] = array("votes" => 0, "rating" => 0);
        }
        foreach ($results as $row) {
            if ($show == "user") {
                $voters = $row->user_voters;
                $votes = $row->user_votes;
            }
            else if ($show == "visitor") {
                $voters = $row->visitor_voters;
                $votes = $row->visitor_votes;
            }
            else {
                $voters = $row->visitor_voters + $row->user_voters;
                $votes = $row->visitor_votes + $row->user_votes;
            }
            $data[$row->vote_date]["rating"] = $voters > 0 ? number_format($votes / $voters, 1) : 0;
            $data[$row->vote_date]["votes"] = $voters;
        }
        return $data;
    }
}

?>