<?php

class GDSRExport {
    function export_t2() {
        global $table_prefix;
        $sql = sprintf("select section, name, description, elements from %sgdsr_templates where preinstalled = '0'", $table_prefix);
        return $sql;
    }

    function export_t2_full() {
        global $wpdb, $table_prefix;
        $sql = sprintf("select * from %sgdsr_templates", $table_prefix);
        $res = $wpdb->get_results($sql);
        $lines = array();
        $line = '';
        $first = true;
        foreach ($res as $row) {
            $values = '(';
            foreach ($row as $data) $values.= '\''.addslashes($data).'\', ';
            $values = substr($values, 0, -2).')';
            if ($first) {
                $line.= 'INSERT INTO %%T2_TABLE_NAME%% VALUES '.$values;
                $first = false;
            } else $line.= ', '.$values;
            if (strlen($line) > 65536) {
                $first = true;
                $lines[] = $line.";\r\n";
                $line = '';
            }
        }
        if ($line != "") $lines[] = $line.";\r\n";
        return $lines;
    }

    function export_users($user_data = "min", $data_export = "article", $get_data = array()) {
        global $table_prefix;
        $columns = array();
        $select = array();
        $where = array();
        $tables = array();

        $tables[] = $table_prefix."gdsr_votes_log v";
        $tables[] = $table_prefix."users u";
        $tables[] = $table_prefix."posts p";
        $where[] = "v.vote_type = '".$data_export."'";
        $where[] = "v.user_id = u.id";
        
        switch ($user_data) {
            case "min":
                $select[] = "u.id as user_id";
                $columns[] = "user_id";
                break;
            case "nor":
                $select[] = "u.id as user_id";
                $select[] = "u.display_name";
                $select[] = "u.user_email";
                $columns[] = "user_id";
                $columns[] = "user_name";
                $columns[] = "user_email";
                break;
        }
        
        $select[] = "p.id";
        $columns[] = "post_id";
        
        if ($get_data["pt"] == "on") {
            $select[] = "p.post_title";
            $columns[] = "post_title";
        }
        if ($get_data["pd"] == "on") {
            $select[] = "p.post_date";
            $columns[] = "post_date";
        }
        switch ($data_export) {
            case "article":
                $where[] = "v.id = p.id";
                break;
            case "comment":
                $select[] = "c.comment_id";
                $columns[] = "comment_id";
                $tables[] = $table_prefix."comments c";
                $where[] = "v.id = c.comment_id";
                $where[] = "p.id = c.comment_post_id";
                if ($get_data["ca"] == "on") {
                    $select[] = "c.comment_author";
                    $columns[] = "comment_author";
                }
                if ($get_data["cd"] == "on") {
                    $select[] = "c.comment_date";
                    $columns[] = "comment_date";
                }
                break;
        }        
        
        $select[] = "v.vote";
        $select[] = "v.voted";
        $columns[] = "vote";
        $columns[] = "vote_date";
        
        if ($get_data["ip"] == "on") {
            $select[] = "v.ip";
            $columns[] = "ip";
        }
        if ($get_data["ua"] == "on") {
            $select[] = "v.user_agent";
            $columns[] = "user_agent";
        }
        
        echo join(", ", $columns)."\r\n";
        $j_select = join(", ", $select);
        $j_where = join(" and ", $where);
        $j_tables = join(", ", $tables);
        
        return sprintf("select %s from %s where %s order by u.id", $j_select, $j_tables, $j_where);
    }
}

?>