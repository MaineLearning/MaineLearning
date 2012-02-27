<?php

require_once("../../config.php");
$wpload = get_gdsr_wpload_path();
require($wpload);
global $gdsr;

require_once(ABSPATH.WPINC."/pluggable.php");
check_ajax_referer('gdsr_chart_l8');
require_once(STARRATING_PATH."code/adm/charting.php");

$action = isset($_GET["action"]) ? $_GET["action"] : "article";
$show = isset($_GET["show"]) ? $_GET["show"] : "";

$data = "";
switch ($action) {
    case "article":
        $post_id = $_GET["postid"];
        $data = gdsrDBChart::trends_daily($post_id, "article", $show);
        break;
}

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
    <title>GD Star Rating</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <script type='text/javascript' src="<?php echo get_option('home')."/".WPINC."/js/jquery/"; ?>jquery.js"></script>
    <script type='text/javascript' src="<?php echo STARRATING_URL; ?>js/jquery/jquery-flot.js"></script>
    <!--[if IE]><script language="javascript" type="text/javascript" src="<?php echo STARRATING_URL; ?>js/jquery/excanvas.js"></script><![endif]-->
</head>
<body>
<table width="100%">
    <tr>
        <td width="160" valign="top">

        </td>
        <td width="10"></td>
        <td valign="top">
            <div id="placeholder" style="width:550px; height:360px;"></div>
        </td>
    </tr>
</table>
<script type="text/javascript">
    <?php echo gdsrDBChart::prepare_data_daily($data); ?>
    var alreadyFetched = {};
    var placeholder = "#placeholder";
    var data = [
        {data: gdr_rating, label: "<?php _e("Votes", "gd-star-rating"); ?>"},
        {data: gdr_votes, label: "<?php _e("Rating", "gd-star-rating"); ?>", yaxis: 2}
    ];
    var options = {
        lines: { show: true },
        points: { show: true },
        xaxis: { ticks: gdr_ticks }
    };

    jQuery(document).ready(function() {
        jQuery.plot("#placeholder", data, options);
    });
</script>

</body>
</html>