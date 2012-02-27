<?php

if (isset($_POST["action"])) {
    if ($_POST["action"] == "addips") {
        $new_ips = $_POST["gdsr_ip_single_new"];
        $ip_list = explode("\r\n", $new_ips);
        $ip_add = array();
        foreach ($ip_list as $ip) {
            if (preg_match('^(?:25[0-5]|2[0-4]\d|[1-9]\d|\d)(?:[.](?:25[0-5]|2[0-4]\d|[1-9]\d|\d)){3}$^', $ip)) {
                gdsrAdmDB::ban_ip($ip);
            }
        }
    }

    if ($_POST["action"] == "baniprange") {
        $valid = true;
        $ip_part_1 = intval($_POST["gdsr_ip_range_1"]);
        $ip_part_2 = intval($_POST["gdsr_ip_range_2"]);
        $ip_part_3 = intval($_POST["gdsr_ip_range_3"]);
        $ip_part_from_4 = intval($_POST["gdsr_ip_range_from_4"]);
        $ip_part_to_4 = intval($_POST["gdsr_ip_range_to_4"]);
        $ip_start = "";
        $ip_end = "";
        if (is_int($ip_part_1) && $ip_part_1 > -1 && $ip_part_1 < 256) {
            $ip_start.= $ip_part_1;
            $ip_end.= $ip_part_1;
        }
        else $valid = false;
        if ($valid && is_int($ip_part_2) && $ip_part_2 > -1 && $ip_part_2 < 256) {
            $ip_start.= ".".$ip_part_2;
            $ip_end.= ".".$ip_part_2;
        }
        else $valid = false;
        if ($valid && is_int($ip_part_3) && $ip_part_3 > -1 && $ip_part_3 < 256) {
            $ip_start.= ".".$ip_part_3;
            $ip_end.= ".".$ip_part_3;
        }
        else $valid = false;
        if ($valid && is_int($ip_part_from_4) && is_int($ip_part_to_4) && $ip_part_from_4 > -1 && $ip_part_from_4 < $ip_part_to_4 && $ip_part_to_4 < 256) {
            $ip_start.= ".".$ip_part_from_4;
            $ip_end.= ".".$ip_part_to_4;
        }
        else $valid = false;
        if ($valid) {
            gdsrAdmDB::ban_ip_range($ip_start, $ip_end);
        }
    }

    if ($_POST["action"] == "maskip") {
        $ip_part_1 = $_POST["gdsr_ip_mask_1"];
        $ip_part_2 = $_POST["gdsr_ip_mask_2"];
        $ip_part_3 = $_POST["gdsr_ip_mask_3"];
        $ip_part_4 = $_POST["gdsr_ip_mask_4"];
        $ip = $ip_part_1.".".$ip_part_2.".".$ip_part_3.".".$ip_part_4;
        gdsrAdmDB::ban_ip($ip, 'M');
    }

    if ($_POST["action"] == "deletebans") {
        $gdsr_items = $_POST["gdsr_item"];
        if (count($gdsr_items) > 0) {
            $ids = "(".join(", ", $gdsr_items).")";
            gdsrAdmDB::unban_ips($ids);
        }
    }
}

?>
<div class="gdsr">
<div class="wrap">
    <h2 class="gdptlogopage">GD Star Rating: <?php _e("IP's", "gd-star-rating"); ?></h2>

<div id="gdsr_tabs" class="gdsrtabs">
<ul>
    <li><a href="#fragment-1"><span><?php _e("Ban New", "gd-star-rating"); ?></span></a></li>
    <li><a href="#fragment-2"><span><?php _e("Banned", "gd-star-rating"); ?></span></a></li>
</ul>
<div style="clear: both"></div>
<div id="fragment-1">
<?php include STARRATING_PATH."options/ips/ips_options.php"; ?>
</div>
<div id="fragment-2">
<?php include STARRATING_PATH."options/ips/ips_list.php"; ?>
</div>
</div>
</div>
</div>
