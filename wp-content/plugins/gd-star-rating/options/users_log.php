<?php

$posts_per_page = $options["admin_rows"];

$url = $_SERVER['REQUEST_URI'];
$url_pos = strpos($url, "&gdsr=");
if (!($url_pos === false))
    $url = substr($url, 0, $url_pos);

$url.= "&amp;gdsr=userslog";

$page_id = 1;
$user_id = 0;
$vote_type = "article";
$user_name = "Visitor";
$filter_vote = 0;

if (isset($_GET["pg"])) $page_id = $_GET["pg"];
if (isset($_GET["ui"])) $user_id = $_GET["ui"];
if (isset($_GET["vt"])) $vote_type = $_GET["vt"];
if (isset($_GET["un"])) $user_name = urldecode($_GET["un"]);
if (isset($_GET["vote"])) $filter_vote = $_GET["vote"];

if (isset($_POST["gdsr_filter"]) && $_POST["gdsr_filter"] == __("Filter", "gd-star-rating")) {
    $filter_vote = $_POST["gdsr_vote"];
    $page_id = 1;
}

$is_thumb = substr($vote_type, 3) == "thumb";

if (isset($_POST["gdsr_update"]) && $_POST["gdsr_update"] == __("Update", "gd-star-rating")) {
    $ips = $_POST["gdsr_item"];
    if (count($ips) > 0) {
        if (isset($_POST["gdsr_ip_ban"])) {
            $all_banned = gdsrAdmDB::get_all_banned_ips();
            $banned_ips = array();
            foreach ($all_banned as $ip) $banned_ips[] = $ip->ip;
            foreach ($ips as $ip) {
                if (!in_array($ip, $banned_ips)) gdsrAdmDB::ban_ip($ip);
            }
        }

        if (isset($_POST["gdsr_delete_articles"])) {
            $page_id = 1;
            $xips = array();
            $del = $_POST["gdsr_delete_articles"];
            foreach ($ips as $ip) $xips[] = "'".$ip."'";
            $log = gdsrAdmDB::get_user_log($user_id, $vote_type, $filter_vote, 0, 0, join(",", $xips));
            foreach ($log as $l) {
                if ($del == "OI" && $l->id != $l->control_id)
                    gdsrAdmDB::delete_voters_log("(".$l->record_id.")");
                if ($del == "LO" && $l->id == $l->control_id)
                    gdsrAdmDB::delete_voters_log("(".$l->record_id.")");
                if ($del == "FD" && $l->id == $l->control_id) {
                    gdsrAdmDB::delete_voters_log("(".$l->record_id.")");
                    if ($is_thumb) gdsrAdmDB::delete_voters_main_thumb($l->id, $l->vote, $l->vote_type == "artthumb", $user_id > 0);
                    else gdsrAdmDB::delete_voters_main($l->id, $l->vote, $l->vote_type == "article", $user_id > 0);
                }
            }
        }
    }
}

if ($filter_vote > 0) $url.= "&amp;vote=".$filter_vote;
$url.= "&amp;ui=".$user_id."&amp;vt=".$vote_type."&amp;un=".$user_name;

$number_posts = gdsrAdmDB::get_count_user_log($user_id, $vote_type, $filter_vote);

$max_page = floor($number_posts / $posts_per_page);
if ($max_page * $posts_per_page != $number_posts) $max_page++;

$pager = $max_page > 1 ? gdFunctionsGDSR::draw_pager($max_page, $page_id, $url, "pg") : "";

if ($vote_type == "article")
    $max_stars = $options["stars"];
else
    $max_stars = $options["cmm_stars"];

?>

<div class="wrap">
<form id="gdsr-articles" method="post" action="">
<h2 class="gdptlogopage">GD Star Rating: <?php _e("User Vote Log", "gd-star-rating"); ?></h2>
<p><strong>
<?php if ($user_id > 0) { ?>
<?php _e("Votes log for user", "gd-star-rating"); ?>: 
<?php echo sprintf('<a href="./user-edit.php?user_id=%s">%s</a>', $user_id, $user_name); ?>
<?php } else { ?>
<?php _e("Votes log for visitors", "gd-star-rating"); ?>
<?php } ?>
</strong></p>
<div class="tablenav">
    <div class="alignleft">
        <select style="width: 100px;" name="gdsr_vote" id="gdsr_vote">
        <option value="0"<?php if ($filter_vote == 0) echo ' selected="selected"'; ?>><?php _e("All Votes", "gd-star-rating"); ?></option>
        <?php GDSRHelper::render_stars_select_full($filter_vote, $max_stars, 1, __("Vote", "gd-star-rating")); ?>
        </select>
        <input class="button-secondary delete" type="submit" name="gdsr_filter" value="<?php _e("Filter", "gd-star-rating"); ?>" />
    </div>
    <div class="tablenav-pages">
        <?php echo $pager; ?>
    </div>
</div>
<br class="clear"/>
<table class="widefat">
    <thead>
        <tr>
            <th class="check-column" scope="col"><input type="checkbox" onclick="checkAll(document.getElementById('gdsr-articles'));"/></th>
            <th scope="col"><?php _e("IP", "gd-star-rating"); ?></th>
            <th scope="col"><?php _e("Status", "gd-star-rating"); ?></th>
            <th scope="col"><?php _e("Vote Date", "gd-star-rating"); ?></th>
            <th scope="col"><?php _e("Vote", "gd-star-rating"); ?></th>
            <th scope="col"><?php _e("Post", "gd-star-rating"); ?></th>
            <?php if ($vote_type == "comment") { ?>
            <th scope="col"><?php _e("Comment Author", "gd-star-rating"); ?></th>
            <th scope="col"><?php _e("Comment Excerpt", "gd-star-rating"); ?></th>
            <?php } ?>
        </tr>
    </thead>
    <tbody>

<?php

    $log = gdsrAdmDB::get_user_log($user_id, $vote_type, $filter_vote, ($page_id - 1) * $posts_per_page, $posts_per_page);
    $ips = array();
    $idx = array();
    foreach ($log as $l) {
        if (!in_array($l->ip, $ips)) {
            $ips[] = $l->ip;
            $idx[] = 1;
        }
        else $idx[count($idx) - 1]++;
    }
    
    $counter = 0;
    $tr_class = "";
    for ($i = 0; $i < count($idx); $i++) {
        for ($j = 0; $j < $idx[$i]; $j++) {
            echo '<tr id="post-'.$log[$counter]->record_id.'" class="'.$tr_class.' author-self status-publish" valign="top">';
            if ($j == 0) {
                echo '<th rowspan='.$idx[$i].' scope="row" class="check-column"><input name="gdsr_item[]" value="'.$log[$counter]->ip.'" type="checkbox"></th>';
                echo '<td rowspan='.$idx[$i].'><strong>'.$log[$counter]->ip.'</strong></td>';
                echo '<td rowspan='.$idx[$i].' nowrap="nowrap">';
                echo $log[$counter]->status == "B" ? '<strong style="color: red">Banned</strong>' : 'OK';
                echo '</td>';
            }
            echo '<td nowrap="nowrap">'.$log[$counter]->voted.'</td>';
            echo '<td><strong>';
            if (($vote_type == "artthumb" || $vote_type == "cmmthumb") && $log[$counter]->vote > 0) echo "+";
            echo $log[$counter]->vote;
            echo '</strong></td>';
            echo '<td nowrap="nowrap">';
            if ($log[$counter]->id != $log[$counter]->control_id)
                echo '<strong style="color: red">INVALID VOTE</strong>';
            else
                echo '<strong>['.$log[$counter]->post_id.']</strong> '.$log[$counter]->post_title;
            echo '</td>';
            if ($vote_type == "comment") {
                echo '<td>'.$log[$counter]->author.'</td>';
                echo '<td>'.$log[$counter]->comment_content.'</td>';
            }
            echo '</tr>';
            $counter++;
        }
        if ($tr_class == "")
            $tr_class = "alternate ";
        else
            $tr_class = "";
    }

?>

    </tbody>
</table>
<div class="tablenav" style="height: 5em">
    <div class="alignleft">
        <div class="panel">
        <table cellpadding="0" cellspacing="0">
        <tr>
        <td>
            <table cellpadding="0" cellspacing="0">
            <tr>
                <td style="width: 120px; height: 29px;">
                    <span class="paneltext"><strong><?php _e("IP's", "gd-star-rating"); ?>:</strong></span>
                </td>
                <td colspan="3" style="height: 29px;">
                    <input type="checkbox" name="gdsr_ip_ban" id="gdsr_ip_ban" /><label style="margin-left: 5px;" for="gdsr_ip_ban"><?php _e("Add selected IP's to ban IP's list.", "gd-star-rating"); ?></label>
                </td>
            </tr>
            <tr>
                <td style="width: 120px; height: 29px;">
                    <span class="paneltext"><strong><?php _e("Votes", "gd-star-rating"); ?>:</strong></span>
                </td>
                <td style="width: 80px; height: 29px;">
                    <span class="paneltext"><?php _e("Delete", "gd-star-rating"); ?>:</span>
                </td>
                <td style="width: 160px; height: 29px;" align="right">
                    <select id="gdsr_delete_articles" name="gdsr_delete_articles" style="width: 140px;">
                        <option value="">/</option>
                        <option value="OI"><?php _e("Only Invalid", "gd-star-rating"); ?></option>
                        <option value="LO"><?php _e("From Log Only", "gd-star-rating"); ?></option>
                        <option value="FD"><?php _e("Full Delete", "gd-star-rating"); ?></option>
                    </select>
                </td>
                <td></td>
            </tr>
            </table>
        </td>
        <td style="width: 10px;"></td>
        <td class="gdsr-vertical-line"><input class="button-secondary delete" type="submit" name="gdsr_update" value="<?php _e("Update", "gd-star-rating"); ?>" /></td>
        </tr>
        </table>
        </div>
    </div>
    <div class="tablenav-pages">
    </div>
</div>
<br class="clear"/>
</form>
</div>
