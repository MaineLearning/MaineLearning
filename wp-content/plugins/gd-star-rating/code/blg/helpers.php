<?php

class gdsrFrontHelp {
    /**
    * Check the cookie for the given id and type to see if the visitor is already voted for it
    *
    * @param int $id post or comment id depending on $type
    * @param string $type article or comment
    * @return bool true if cookie exists for $id and $type, false if is not
    */
    static function check_cookie($id, $type = "article") {
        global $gdsr;
        if (
            ($type == "article" && $gdsr->o["cookies"]) ||
            ($type == "artthumb" && $gdsr->o["cookies"] == 1) ||
            ($type == "multis" && $gdsr->o["cookies"] == 1) ||
            ($type == "comment" && $gdsr->o["cmm_cookies"]) ||
            ($type == "cmmthumb" && $gdsr->o["cmm_cookies"] == 1)
            ) {
            if (isset($_COOKIE["wp_gdsr_".$type])) {
                $cookie = $_COOKIE["wp_gdsr_".$type];
                $cookie = substr($cookie, 7, strlen($cookie) - 7);
                $cookie_ids = explode('|', $cookie);
                if (in_array($id, $cookie_ids)) return false;
            }
        }
        return true;
    }

    /**
    * Saves the vote in the cookie for the given id and type
    *
    * @param int $id post or comment id depending on $type
    * @param string $type article or comment
    */
    static function save_cookie($id, $type = "article") {
        global $gdsr;
        if (
            ($type == "article" && $gdsr->o["cookies"] == 1) ||
            ($type == "artthumb" && $gdsr->o["cookies"] == 1) ||
            ($type == "multis" && $gdsr->o["cookies"] == 1) ||
            ($type == "comment" && $gdsr->o["cmm_cookies"] == 1) ||
            ($type == "cmmthumb" && $gdsr->o["cmm_cookies"] == 1)
            ) {
            if (isset($_COOKIE["wp_gdsr_".$type])) {
                $cookie = $_COOKIE["wp_gdsr_".$type];
                $cookie = substr($cookie, 6, strlen($cookie) - 6);
            }
            else $cookie = '';
            $cookie.= "|".$id;
            setcookie("wp_gdsr_".$type, "voted_".$cookie, time() + 3600 * 24 * 365, '/');
        }
    }

    /**
     * Adding elements for IE Opacity fix
     */
    static function ie_opacity_fix() {
        echo('<!--[if IE]>');
        echo('<style type="text/css">');
        echo('.ratemulti .starsbar .gdcurrent { -ms-filter:"progid:DXImageTransform.Microsoft.Alpha(Opacity=70)"; filter: alpha(opacity=70); }');
        echo('</style>');
        echo('<![endif]-->');
        echo("\r\n");
    }

    /**
     * Detects if the visitor is BOT.
     *
     * @param string $str string to check
     * @param array $spiders list of bots
     * @return bool result 
     */
    static function detect_bot($str, $spiders = array()) {
        foreach($spiders as $spider) {
        if (preg_match("/".$spider."/", $str))
            return true;
        }
        return false;
    }

    /**
     * Detect if the IP should be banned.
     *
     * @return bool result
     */
    static function detect_ban() {
        $ip = $_SERVER["REMOTE_ADDR"];
        $ban = false;
        $ban = gdsrBlgDB::check_ip_single($ip);
        if (!$ban)
            $ban = gdsrBlgDB::check_ip_range($ip);
        if (!$ban)
            $ban = gdsrBlgDB::check_ip_mask($ip);
        return $ban;
    }

    /**
     * Calculate expiration countdown.
     *
     * @param date $post_date expiration date
     * @param string $value expiration value with period type
     * @return int expiration time
     */
    static function expiration_countdown($post_date, $value) {
        $period = substr($value, 0, 1);
        $value = substr($value, 1);
        $pdate = strtotime($post_date);
        $expiry = 0;
        switch ($period) {
            case 'H':
                $expiry = mktime(date("H", $pdate) + $value, date("i", $pdate), date("s", $pdate), date("m", $pdate),          date("j", $pdate),          date("Y", $pdate));
                break;
            case 'D':
                $expiry = mktime(date("H", $pdate),          date("i", $pdate), date("s", $pdate), date("m", $pdate),          date("j", $pdate) + $value, date("Y", $pdate));
                break;
            case 'M':
                $expiry = mktime(date("H", $pdate),          date("i", $pdate), date("s", $pdate), date("m", $pdate) + $value, date("j", $pdate),          date("Y", $pdate));
                break;
        }
        return $expiry - mktime();
    }

    /**
     * Simple expiration time calculation.
     *
     * @param datetime $value string with date
     * @return int expiration time
     */
    static function expiration_date($value) {
        return strtotime($value) - mktime();
    }

    /**
     * Caluclate deadline date.
     *
     * @param timestamp $timestamp timestamp to calculate
     * @return date calculated deadline
     */
    static function calculate_deadline($timestamp) {
        $deadline_ts = $timestamp + mktime();
        return date("Y-m-d", $deadline_ts);
    }

    static function remaining_time_parts($timestamp) {
        $times = array(
                31536000 => 'year',
                2592000 => 'month',
                86400 => 'day',
                3600 => 'hour',
                60 => 'minute',
                1 => 'second'
            );
        $secs = $timestamp;
        $parts = array();

        foreach ($times AS $key => $value) {
            if ($secs >= $key) {
                $count = floor($secs / $key);
                $parts[$value] = $count;
                $secs = $secs - $count * $key;
            }
            else $parts[$value] = 0;
        }

        return $parts;
    }

    static function remaining_time_total($timestamp) {
        $times = array(
                31536000 => 'year',
                2592000 => 'month',
                86400 => 'day',
                3600 => 'hour',
                60 => 'minute',
                1 => 'second'
            );
        $parts = array();

        foreach ($times AS $key => $value) {
            $parts[$value] = floor($timestamp / $key);
        }

        return $parts;
    }
}

?>
