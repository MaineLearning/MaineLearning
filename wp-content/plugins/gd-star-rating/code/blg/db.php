<?php

class gdsrBlgDB {
    function get_rss_multi_data($post_id) {
        global $wpdb, $table_prefix;

        $sql = sprintf("select * from %sgdsr_multis_data where post_id = %s order by (total_votes_users + total_votes_visitors) desc limit 0, 1", $table_prefix, $post_id);
        return $wpdb->get_row($sql);
    }

    function get_rss_multi_data_review($post_id) {
        global $wpdb, $table_prefix;

        $sql = sprintf("select * from %sgdsr_multis_data where post_id = %s order by average_review desc limit 0, 1", $table_prefix, $post_id);
        return $wpdb->get_row($sql);
    }

    function add_new_view($post_id) {
        if (intval($post_id) > 0) {
            global $wpdb, $table_prefix;
            $dbt_data_article = $table_prefix.'gdsr_data_article';
            $sql = sprintf("update %s set views = views + 1 where post_id = %s", $dbt_data_article, $post_id);
            $wpdb->query($sql);
        }
    }

    function get_comments_aggregation($post_id, $filter_show = "total") {
        global $wpdb, $table_prefix;

        $where = "";
        switch ($filter_show) {
            default:
            case "total":
                $where = " user_voters + visitor_voters > 0";
                break;
            case "users":
                $where = " user_voters > 0";
                break;
            case "visitors":
                $where = " visitor_voters > 0";
                break;
        }

        $sql = sprintf("SELECT * FROM %sgdsr_data_comment where post_id = %s and %s", $table_prefix, $post_id, $where);
        return $wpdb->get_results($sql);
    }

    function lock_post($post_id, $rules_articles = "N") {
        global $wpdb, $table_prefix;

        $wpdb->query(sprintf("update %sgdsr_data_article set rules_articles = '%s' where post_id = %s",
            $table_prefix, $rules_articles, $post_id));
    }

    // ip
    function check_ip_single($ip) {
        global $wpdb, $table_prefix;
        $sql = sprintf("select count(*) from %sgdsr_ips where `status` = 'B' and `mode` = 'S' and `ip` = '%s'", $table_prefix, $ip);
        return $wpdb->get_var($sql) > 0;
    }

    function check_ip_range($ip) {
        global $wpdb, $table_prefix;
        $sql = sprintf("select count(*) from %sgdsr_ips where `status` = 'B' and `mode` = 'R' and inet_aton(substring_index(ip, '|', 1)) <= inet_aton('%s') and inet_aton(substring_index(ip, '|', -1)) >= inet_aton('%s')", $table_prefix, $ip, $ip);
        return $wpdb->get_var($sql) > 0;
    }

    function check_ip_mask($ip) {
        global $wpdb, $table_prefix;
        $sql = sprintf("select ip from %sgdsr_ips where `status` = 'B' and `mode` = 'M'", $table_prefix);
        $ips = $wpdb->get_results($sql);
        foreach ($ips as $i) {
            $mask = explode('.', $i->ip);
            $ip = explode('.', $ip);
            for ($i = 0; $i < 4; $i++) {
                if (is_numeric($mask[$i])) {
                    if ($ip[$i] != $mask[$i]) return false;
                }
            }
            return true;
        }
        return false;
    }
    // ip

    // check vote
    function check_vote_table($table, $id, $user, $type, $ip, $mixed = false) {
        global $wpdb, $table_prefix;

        if ($user > 0) {
            $votes_sql = sprintf("SELECT count(*) FROM %s WHERE vote_type = '%s' and id = %s and user_id = %s", $table_prefix.$table, $type, $id, $user);

wp_gdsr_dump("CHECK_VOTE_USER", $votes_sql);

            $votes = $wpdb->get_var($votes_sql);
            return $votes == 0;
        } else {
            $votes_sql = sprintf("SELECT count(*) FROM %s WHERE vote_type = '%s' and id = %s and ip = '%s'", $table_prefix.$table, $type, $id, $ip);

wp_gdsr_dump("CHECK_VOTE", $votes_sql);

            $votes = $wpdb->get_var($votes_sql);
            if ($votes > 0 && $mixed) {
                $votes_sql = sprintf("SELECT count(*) FROM %s WHERE vote_type = '%s' and user_id > 0 and id = %s and ip = '%s'", $table_prefix.$table, $type, $id, $ip);

wp_gdsr_dump("CHECK_VOTE_MIX", $votes_sql);

                $votes_mixed = $wpdb->get_var($votes_sql);
                if ($votes_mixed > 0) $votes = 0;
            }
            return $votes == 0;
        }
    }

    function check_vote($id, $user, $type, $ip, $mod_only = false, $mixed = false) {
        $result = true;

        if (!$mod_only) $result = gdsrBlgDB::check_vote_logged($id, $user, $type, $ip, $mixed);
        if ($result) $result = gdsrBlgDB::check_vote_moderated($id, $user, $type, $ip, $mixed);

        return $result;
    }

    function check_vote_logged($id, $user, $type, $ip, $mixed = false) {
        return gdsrBlgDB::check_vote_table('gdsr_votes_log', $id, $user, $type, $ip, $mixed);
    }

    function check_vote_moderated($id, $user, $type, $ip, $mixed = false) {
        return gdsrBlgDB::check_vote_table('gdsr_moderate', $id, $user, $type, $ip, $mixed);
    }
    // check vote

    // save thumb votes
    function save_vote_comment_thumb($id, $user, $ip, $ua, $vote) {
        global $wpdb, $table_prefix;
        $ua = str_replace("'", "''", $ua);
        $ua = substr($ua, 0, 250);

        $post = $wpdb->get_row("select comment_post_ID from $wpdb->comments where comment_ID = ".$id);
        $post_id = $post->comment_post_ID;
        $sql = sprintf("SELECT * FROM %sgdsr_data_article WHERE post_id = %s", $table_prefix, $post_id);
        $post_data = $wpdb->get_row($sql);

        if ($post_data->recc_moderate_comments == "" || $post_data->recc_moderate_comments == "N" || ($post_data->recc_moderate_comments == "V" && $user > 0) || ($post_data->recc_moderate_comments == "U" && $user == 0)) {
            gdsrBlgDB::add_vote_comment_thumb($id, $user, $ip, $ua, $vote);
        } else {
            $modsql = sprintf("INSERT INTO %sgdsr_moderate (id, vote_type, user_id, vote, voted, ip, user_agent) VALUES (%s, 'cmmthumb', %s, %s, '%s', '%s', '%s')",
                $table_prefix, $id, $user, $vote, str_replace("'", "''", current_time('mysql')), $ip, $ua);
            $wpdb->query($modsql);
        }
    }

    function save_vote_thumb($id, $user, $ip, $ua, $vote, $comment_id = 0) {
        global $wpdb, $table_prefix;
        $ua = str_replace("'", "''", $ua);
        $ua = substr($ua, 0, 250);

        $sql = sprintf("SELECT * FROM %sgdsr_data_article WHERE post_id = %s", $table_prefix, $id);
        $post_data = $wpdb->get_row($sql);
        if (count($post_data) == 0) {
            GDSRDatabase::add_default_vote($id);
            $post_data = $wpdb->get_row($sql);
        }

        if ($post_data->recc_moderate_articles == "" || $post_data->recc_moderate_articles == "N" || ($post_data->recc_moderate_articles == "V" && $user > 0) || ($post_data->recc_moderate_articles == "U" && $user == 0)) {
            gdsrBlgDB::add_vote_thumb($id, $user, $ip, $ua, $vote, $comment_id);
        } else {
            $modsql = sprintf("INSERT INTO %sgdsr_moderate (id, vote_type, user_id, vote, voted, ip, user_agent, comment_id) VALUES (%s, 'artthumb', %s, %s, '%s', '%s', '%s', %s)",
                $table_prefix, $id, $user, $vote, str_replace("'", "''", current_time('mysql')), $ip, $ua, $comment_id);
            $wpdb->query($modsql);
        }
    }

    function add_vote_comment_thumb($id, $user, $ip, $ua, $vote) {
        global $wpdb, $table_prefix;
        $trend_date = date("Y-m-d");
        $sql_trend = sprintf("SELECT count(*) FROM %sgdsr_votes_trend WHERE vote_date = '%s' and vote_type = 'cmmthumb' and id = %s", $table_prefix, $trend_date, $id);
        $trend_data = $wpdb->get_var($sql_trend);

        $trend_added = false;
        if ($trend_data == 0) {
            $trend_added = true;
            if ($user > 0) {
                $sql = sprintf("INSERT INTO %sgdsr_votes_trend (id, vote_type, user_voters, user_votes, vote_date) VALUES (%s, 'cmmthumb', 1, %s, '%s')",
                    $table_prefix, $id, $vote, $trend_date);
                $wpdb->query($sql);
            } else {
                $sql = sprintf("INSERT INTO %sgdsr_votes_trend (id, vote_type, visitor_voters, visitor_votes, vote_date) VALUES (%s, 'cmmthumb', 1, %s, '%s')",
                    $table_prefix, $id, $vote, $trend_date);
                $wpdb->query($sql);
            }
        }

        if ($user > 0) {
            $part = $vote == 1 ? "user_recc_plus = user_recc_plus + 1" : "user_recc_minus = user_recc_minus + 1";

            if (!$trend_added) {
                $sql = sprintf("UPDATE %sgdsr_votes_trend SET user_voters = user_voters + 1, user_votes = user_votes + %s WHERE id = %s and vote_type = 'cmmthumb' and vote_date = '%s'",
                    $table_prefix, $vote, $id, $trend_date);
                $wpdb->query($sql);
            }
        } else {
            $part = $vote == 1 ? "visitor_recc_plus = visitor_recc_plus + 1" : "visitor_recc_minus = visitor_recc_minus + 1";

            if (!$trend_added) {
                $sql = sprintf("UPDATE %sgdsr_votes_trend SET visitor_voters = visitor_voters + 1, visitor_votes = visitor_votes + %s WHERE id = %s and vote_type = 'cmmthumb' and vote_date = '%s'",
                    $table_prefix, $vote, $id, $trend_date);
                $wpdb->query($sql);
            }
        }

        $sql = sprintf("UPDATE %sgdsr_data_comment SET %s, last_voted_recc = CURRENT_TIMESTAMP WHERE comment_id = %s",
            $table_prefix, $part, $id);
        $wpdb->query($sql);

wp_gdsr_dump("SAVE_THUMB_VOTE", $sql);

        $logsql = sprintf("INSERT INTO %sgdsr_votes_log (id, vote_type, user_id, vote, object, voted, ip, user_agent) VALUES (%s, 'cmmthumb', %s, %s, '', '%s', '%s', '%s')",
            $table_prefix, $id, $user, $vote, str_replace("'", "''", current_time('mysql')), $ip, $ua);
        $wpdb->query($logsql);

wp_gdsr_dump("SAVE_THUMB_LOG", $logsql);

    }

    function add_vote_thumb($id, $user, $ip, $ua, $vote, $comment_id = 0) {
        global $wpdb, $table_prefix;
        $trend_date = date("Y-m-d");
        $sql_trend = sprintf("SELECT count(*) FROM %sgdsr_votes_trend WHERE vote_date = '%s' and vote_type = 'artthumb' and id = %s", $table_prefix, $trend_date, $id);
        $trend_data = $wpdb->get_var($sql_trend);

        $trend_added = false;
        if ($trend_data == 0) {
            $trend_added = true;
            if ($user > 0) {
                $sql = sprintf("INSERT INTO %sgdsr_votes_trend (id, vote_type, user_voters, user_votes, vote_date) VALUES (%s, 'artthumb', 1, %s, '%s')",
                        $table_prefix, $id, $vote, $trend_date);
                $wpdb->query($sql);
            } else {
                $sql = sprintf("INSERT INTO %sgdsr_votes_trend (id, vote_type, visitor_voters, visitor_votes, vote_date) VALUES (%s, 'artthumb', 1, %s, '%s')",
                        $table_prefix, $id, $vote, $trend_date);
                $wpdb->query($sql);
            }
        }

        if ($user > 0) {
            $part = $vote == 1 ? "user_recc_plus = user_recc_plus + 1" : "user_recc_minus = user_recc_minus + 1";

            if (!$trend_added) {
                $sql = sprintf("UPDATE %sgdsr_votes_trend SET user_voters = user_voters + 1, user_votes = user_votes + %s WHERE id = %s and vote_type = 'artthumb' and vote_date = '%s'",
                    $table_prefix, $vote, $id, $trend_date);
                $wpdb->query($sql);
            }
        } else {
            $part = $vote == 1 ? "visitor_recc_plus = visitor_recc_plus + 1" : "visitor_recc_minus = visitor_recc_minus + 1";

            if (!$trend_added) {
                $sql = sprintf("UPDATE %sgdsr_votes_trend SET visitor_voters = visitor_voters + 1, visitor_votes = visitor_votes + %s WHERE id = %s and vote_type = 'artthumb' and vote_date = '%s'",
                    $table_prefix, $vote, $id, $trend_date);
                $wpdb->query($sql);
            }
        }

        $sql = sprintf("UPDATE %sgdsr_data_article SET %s, last_voted_recc = CURRENT_TIMESTAMP WHERE post_id = %s",
            $table_prefix, $part, $id);
        $wpdb->query($sql);

wp_gdsr_dump("SAVE_THUMB_VOTE", $sql);

        $logsql = sprintf("INSERT INTO %sgdsr_votes_log (id, vote_type, user_id, vote, object, voted, ip, user_agent, comment_id) VALUES (%s, 'artthumb', %s, %s, '', '%s', '%s', '%s', %s)",
            $table_prefix, $id, $user, $vote, str_replace("'", "''", current_time('mysql')), $ip, $ua, $comment_id);
        $wpdb->query($logsql);

wp_gdsr_dump("SAVE_THUMB_LOG", $logsql);

    }
    // save thumb votes

    // save stars votes
    function save_vote($id, $user, $ip, $ua, $vote, $comment_id = 0) {
        global $wpdb, $table_prefix;
        $ua = str_replace("'", "''", $ua);
        $ua = substr($ua, 0, 250);

        $sql = sprintf("SELECT * FROM %sgdsr_data_article WHERE post_id = %s", $table_prefix, $id);
        $post_data = $wpdb->get_row($sql);
        if (count($post_data) == 0) {
            GDSRDatabase::add_default_vote($id);
            $post_data = $wpdb->get_row($sql);
        }

        if ($post_data->moderate_articles == "" || $post_data->moderate_articles == "N" || ($post_data->moderate_articles == "V" && $user > 0) || ($post_data->moderate_articles == "U" && $user == 0)) {
            gdsrBlgDB::add_vote($id, $user, $ip, $ua, $vote, $comment_id);
        } else {
            $modsql = sprintf("INSERT INTO %sgdsr_moderate (id, vote_type, user_id, vote, voted, ip, user_agent, comment_id) VALUES (%s, 'article', %s, %s, '%s', '%s', '%s', %s)",
                $table_prefix, $id, $user, $vote, str_replace("'", "''", current_time('mysql')), $ip, $ua, $comment_id);
            $wpdb->query($modsql);
        }
    }

    function save_vote_comment($id, $user, $ip, $ua, $vote) {
        global $wpdb, $table_prefix;
        $ua = str_replace("'", "''", $ua);
        $ua = substr($ua, 0, 250);

        $post = $wpdb->get_row("select comment_post_ID from $wpdb->comments where comment_ID = ".$id);
        $post_id = $post->comment_post_ID;
        $sql = sprintf("SELECT * FROM %sgdsr_data_article WHERE post_id = %s", $table_prefix, $post_id);
        $post_data = $wpdb->get_row($sql);

        if ($post_data->moderate_comments == "" || $post_data->moderate_comments == "N" || ($post_data->moderate_comments == "V" && $user > 0) || ($post_data->moderate_comments == "U" && $user == 0)) {
            gdsrBlgDB::add_vote_comment($id, $user, $ip, $ua, $vote);
        } else {
            $modsql = sprintf("INSERT INTO %sgdsr_moderate (id, vote_type, user_id, vote, voted, ip, user_agent) VALUES (%s, 'comment', %s, %s, '%s', '%s', '%s')",
                $table_prefix, $id, $user, $vote, str_replace("'", "''", current_time('mysql')), $ip, $ua);
            $wpdb->query($modsql);
        }
    }

    function add_vote_comment($id, $user, $ip, $ua, $vote) {
        global $wpdb, $table_prefix;
        $comments = $table_prefix.'gdsr_data_comment';
        $stats = $table_prefix.'gdsr_votes_log';
        $trend = $table_prefix.'gdsr_votes_trend';

        $trend_date = date("Y-m-d");

        $sql_trend = sprintf("SELECT count(*) FROM %s WHERE vote_date = '%s' and vote_type = 'comment' and id = %s", $trend, $trend_date, $id);
        $trend_data = $wpdb->get_var($sql_trend);

wp_gdsr_dump("SAVEVOTE_CMM_trend_check_sql", $sql_trend);
wp_gdsr_dump("SAVEVOTE_CMM_trend_check_error", $wpdb->last_error);

        $trend_added = false;
        if ($trend_data == 0) {
            $trend_added = true;
            if ($user > 0) {
                $sql = sprintf("INSERT INTO %s (id, vote_type, user_voters, user_votes, vote_date) VALUES (%s, 'comment', 1, %s, '%s')",
                    $trend, $id, $vote, $trend_date);
                $wpdb->query($sql);
            } else {
                $sql = sprintf("INSERT INTO %s (id, vote_type, visitor_voters, visitor_votes, vote_date) VALUES (%s, 'comment', 1, %s, '%s')",
                    $trend, $id, $vote, $trend_date);
                $wpdb->query($sql);
            }

wp_gdsr_dump("SAVEVOTE_CMM_trend_insert_sql", $sql);
wp_gdsr_dump("SAVEVOTE_CMM_trend_insert_error", $wpdb->last_error);

        }

        if ($user > 0) {
            $sql = sprintf("UPDATE %s SET user_voters = user_voters + 1, user_votes = user_votes + %s, last_voted = CURRENT_TIMESTAMP WHERE comment_id = %s",
                $comments, $vote, $id);
            $wpdb->query($sql);

wp_gdsr_dump("SAVEVOTE_CMM_trend_update_user_sql", $sql);
wp_gdsr_dump("SAVEVOTE_CMM_trend_update_user_error", $wpdb->last_error);

            if (!$trend_added) {
                $sql = sprintf("UPDATE %s SET user_voters = user_voters + 1, user_votes = user_votes + %s WHERE id = %s and vote_type = 'comment' and vote_date = '%s'",
                    $trend, $vote, $id, $trend_date);
                $wpdb->query($sql);

wp_gdsr_dump("SAVEVOTE_CMM_trend_update_user_sql", $sql);
wp_gdsr_dump("SAVEVOTE_CMM_trend_update_user_error", $wpdb->last_error);

            }
        } else {
            $sql = sprintf("UPDATE %s SET visitor_voters = visitor_voters + 1, visitor_votes = visitor_votes + %s, last_voted = CURRENT_TIMESTAMP WHERE comment_id = %s",
                $comments, $vote, $id);
            $wpdb->query($sql);

wp_gdsr_dump("SAVEVOTE_CMM_trend_update_visitor_sql", $sql);
wp_gdsr_dump("SAVEVOTE_CMM_trend_update_visitor_error", $wpdb->last_error);

            if (!$trend_added) {
                $sql = sprintf("UPDATE %s SET visitor_voters = visitor_voters + 1, visitor_votes = visitor_votes + %s WHERE id = %s and vote_type = 'comment' and vote_date = '%s'",
                    $trend, $vote, $id, $trend_date);
                $wpdb->query($sql);

wp_gdsr_dump("SAVEVOTE_CMM_trend_update_visitor_sql", $sql);
wp_gdsr_dump("SAVEVOTE_CMM_trend_update_visitor_error", $wpdb->last_error);

            }
        }

        $logsql = sprintf("INSERT INTO %s (id, vote_type, user_id, vote, voted, ip, user_agent) VALUES (%s, 'comment', %s, %s, '%s', '%s', '%s')",
            $stats, $id, $user, $vote, str_replace("'", "''", current_time('mysql')), $ip, $ua);
        $wpdb->query($logsql);

wp_gdsr_dump("SAVEVOTE_CMM_insert_stats_sql", $sql);
wp_gdsr_dump("SAVEVOTE_CMM_insert_stats_id", $wpdb->insert_id);
wp_gdsr_dump("SAVEVOTE_CMM_insert_stats_error", $wpdb->last_error);

    }

    function add_vote($id, $user, $ip, $ua, $vote, $comment_id = 0) {
        global $wpdb, $table_prefix;
        $articles = $table_prefix.'gdsr_data_article';
        $stats = $table_prefix.'gdsr_votes_log';
        $trend = $table_prefix.'gdsr_votes_trend';

        $trend_date = date("Y-m-d");

        $sql_trend = sprintf("SELECT count(*) FROM %s WHERE vote_date = '%s' and vote_type = 'article' and id = %s", $trend, $trend_date, $id);
        $trend_data = $wpdb->get_var($sql_trend);

wp_gdsr_dump("SAVEVOTE_trend_check_sql", $sql_trend);
wp_gdsr_dump("SAVEVOTE_trend_check_error", $wpdb->last_error);

        $trend_added = false;
        if ($trend_data == 0) {
            $trend_added = true;
            if ($user > 0) {
                $sql = sprintf("INSERT INTO %s (id, vote_type, user_voters, user_votes, vote_date) VALUES (%s, 'article', 1, %s, '%s')",
                        $trend, $id, $vote, $trend_date);
                $wpdb->query($sql);
            } else {
                $sql = sprintf("INSERT INTO %s (id, vote_type, visitor_voters, visitor_votes, vote_date) VALUES (%s, 'article', 1, %s, '%s')",
                        $trend, $id, $vote, $trend_date);
                $wpdb->query($sql);
            }

wp_gdsr_dump("SAVEVOTE_trend_insert_sql", $sql);
wp_gdsr_dump("SAVEVOTE_trend_insert_error", $wpdb->last_error);

        }

        if ($user > 0) {
            $sql = sprintf("UPDATE %s SET user_voters = user_voters + 1, user_votes = user_votes + %s, last_voted = CURRENT_TIMESTAMP WHERE post_id = %s",
                $articles, $vote, $id);
            $wpdb->query($sql);

wp_gdsr_dump("SAVEVOTE_update_user_sql", $sql);
wp_gdsr_dump("SAVEVOTE_update_user", $wpdb->last_error);

            if (!$trend_added) {
                $sql = sprintf("UPDATE %s SET user_voters = user_voters + 1, user_votes = user_votes + %s WHERE id = %s and vote_type = 'article' and vote_date = '%s'",
                    $trend, $vote, $id, $trend_date);
                $wpdb->query($sql);

wp_gdsr_dump("SAVEVOTE_trend_added_user_sql", $sql);
wp_gdsr_dump("SAVEVOTE_trend_added_user_error", $wpdb->last_error);

            }
        } else {
            $sql = sprintf("UPDATE %s SET visitor_voters = visitor_voters + 1, visitor_votes = visitor_votes + %s, last_voted = CURRENT_TIMESTAMP WHERE post_id = %s",
                $articles, $vote, $id);
            $wpdb->query($sql);

wp_gdsr_dump("SAVEVOTE_update_visitor_sql", $sql);
wp_gdsr_dump("SAVEVOTE_update_visitor_error", $wpdb->last_error);

            if (!$trend_added) {
                $sql = sprintf("UPDATE %s SET visitor_voters = visitor_voters + 1, visitor_votes = visitor_votes + %s WHERE id = %s and vote_type = 'article' and vote_date = '%s'",
                    $trend, $vote, $id, $trend_date);
                $wpdb->query($sql);
            }

wp_gdsr_dump("SAVEVOTE_trend_added_visitor_sql", $sql);
wp_gdsr_dump("SAVEVOTE_trend_added_visitor_error", $wpdb->last_error);

        }

        $logsql = sprintf("INSERT INTO %s (id, vote_type, user_id, vote, object, voted, ip, user_agent, comment_id) VALUES (%s, 'article', %s, %s, '', '%s', '%s', '%s', %s)",
            $stats, $id, $user, $vote, str_replace("'", "''", current_time('mysql')), $ip, $ua, $comment_id);
        $wpdb->query($logsql);

wp_gdsr_dump("SAVEVOTE_insert_stats_sql", $sql);
wp_gdsr_dump("SAVEVOTE_insert_stats_id", $wpdb->insert_id);
wp_gdsr_dump("SAVEVOTE_insert_stats_error", $wpdb->last_error);

    }
    // save stars votes

    function taxonomy_multi_ratings_data($taxonomy = "category", $terms = array(), $multi_id = 0, $by = "name") {
        global $wpdb, $table_prefix;

        $select = "d.id as mdid, v.source, x.name as title, t.term_id, v.item_id, sum(v.user_voters) as user_voters";
        $select.= ", sum(v.user_votes) as user_votes, sum(v.visitor_voters) as visitor_voters, sum(v.visitor_votes) as visitor_votes";
        $from = sprintf("%sterm_taxonomy t, %sterm_relationships r, %sterms x, %sgdsr_multis_values v, ", $table_prefix, $table_prefix, $table_prefix, $table_prefix);
        $where = array("d.id = v.id", "t.term_taxonomy_id = r.term_taxonomy_id", "r.object_id = p.id", "t.term_id = x.term_id", "p.id = d.post_id", "p.post_status = 'publish'", "d.multi_id = ".$multi_id, sprintf("t.taxonomy = '%s'", $taxonomy));

        if (count($terms) > 0) {
            $clean_terms = array();
            foreach ($terms as $t) $clean_terms[] = "'".str_replace("'", "''", $t)."'";
            $where[] = sprintf("x.%s in (%s)", $by, join(", ", $clean_terms));
        }

        $sql = sprintf("select distinct %s from %s%sposts p, %sgdsr_multis_data d where %s group by x.term_id, v.source, v.item_id order by x.term_id, d.id, v.source, v.item_id",
            $select, $from, $table_prefix, $table_prefix, join(" and ", $where));
        return $wpdb->get_results($sql);
    }

    function taxonomy_multi_ratings($taxonomy = "category", $terms = array(), $multi_id = 0, $by = "name") {
        global $wpdb, $table_prefix, $wp_taxonomies;

        $select = "d.id as mdid, x.name as title, t.term_id, count(*) as counter, sum(d.average_rating_users * d.total_votes_users) as user_votes, sum(d.average_rating_visitors * d.total_votes_visitors) as visitor_votes, sum(d.total_votes_users) as user_voters, sum(d.total_votes_visitors) as visitor_voters, sum(d.average_review)/count(*) as review, 0 as votes, 0 as voters"; 
        $select.= ", 0 as rating, 0 as bayesian, '' as rating_stars, '' as bayesian_stars, '' as review_stars, '' as review_block, '' as rating_block";
        $from = sprintf("%sterm_taxonomy t, %sterm_relationships r, %sterms x, ", $table_prefix, $table_prefix, $table_prefix);
        $where = array("t.term_taxonomy_id = r.term_taxonomy_id", "r.object_id = p.id", "t.term_id = x.term_id", "p.id = d.post_id", "p.post_status = 'publish'", "d.multi_id = ".$multi_id, sprintf("t.taxonomy = '%s'", $taxonomy));

        if (count($terms) > 0) {
            $clean_terms = array();
            foreach ($terms as $t) $clean_terms[] = "'".str_replace("'", "''", $t)."'";
            $where[] = sprintf("x.%s in (%s)", $by, join(", ", $clean_terms));
        }

        $sql = sprintf("select distinct %s from %s%sposts p, %sgdsr_multis_data d where %s group by t.term_id",
            $select, $from, $table_prefix, $table_prefix, join(" and ", $where));
        return $wpdb->get_results($sql);
    }
}

?>