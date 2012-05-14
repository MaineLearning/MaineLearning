<?php
// The bps-maintenance-values.php file contains the actual form data that is echoed here
include 'bps-maintenance-values.php';
header('HTTP/1.1 503 Service Temporarily Unavailable',true,503);
header('Status: 503 Service Temporarily Unavailable');
header('Retry-After:' . "$bps_retry_after" .''); 	
// header Retry After conversion times in seconds/hrs/days
// 3600=1hr 7200-2hrs 43200=12hrs 86400-24hrs 172800-48hrs 259200-72hrs 604800-168hrs-7days 2419200-672hrs-28days
// Retry After time = tell Search Engines when to revisit your site again
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Language" content="en-us">
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<!-- <meta name="robots" content="noindex,nofollow"> ONLY use noindex with a 503
If this is a brand new website that has not been indexed before - I recommend that 
you never use noindex even if this is a for a brand new website --> 

<title><?php echo $bps_site_title; ?></title>
<!-- the CSS style must remain inline - an external stylesheet rel link will not work -->
<!-- using divs with positioning to allow each object to be positioned independently -->
<style type="text/css">
<!--
body { 
	font-family: Verdana, Arial, Helvetica, sans-serif;
	line-height: normal;
	background-color:#FFF;
}

p { font-family: Verdana, Arial, Helvetica, sans-serif; }

#website_domain_name {
	font-weight:bold;
	font-size:18px;
	position:relative; top:0px; left:0px;
}

/* if you want the table to be positioned in absolute center uncomment this CSS style */
/* be sure to comment out the duplicated .maintenance_table CSS class and #bps_mtable_div styles below */
/* if you want the table to be positioned to a static position comment out this CSS style */
.maintenance_table {
	width:500px;
	height:300px;
	border: solid #999999 2px;
	position:absolute;
	top:50%;
	left:50%;
	margin-top:-150px;
	margin-left:-250px;
	padding:10px;
	background-color: #E9E9E9;
}

/* uncomment the CSS below to move the entire maintenance table to the static position that you want */
/* by adding pixels to top: and left: Example top:100px left:100px   */
/*
#bps_mtable_div {
	position:relative; top:0px; left:0px;
	margin:0 auto;
	width:100%;
}

.maintenance_table {
	width:500px;
	height:300px;
	border: solid #999999 2px;
	position:absolute;
	top:50px;
	left:50px;
	margin:0 auto;
	padding:10px;
	background-color: #E9E9E9;
}
*/

#online_text1 {
	font-family:Verdana, Arial, Helvetica, sans-serif;
	position:relative; top:0px; left:0px;
}
#online_text2 {
	font-family:Verdana, Arial, Helvetica, sans-serif;
	position:relative; top:0px; left:0px;
}

-->
</style>
</head>

<body background="<?php echo "$bps_body_background_image"; ?>">
<div id="bps_mtable_div">
<table border="2" cellpadding="10" cellspacing="0" class="maintenance_table">
  <tr>
    <td>
<?php
// #################### The www prefix of your domain name is not displayed #################
// #################### to Display www comment out $bps-hostname = str_replace ##############
$bps_hostname = htmlspecialchars($_SERVER['SERVER_NAME']); 
$bps_hostname = str_replace('www.', '', $bps_hostname); ?>
<div id="website_domain_name"><?php echo $bps_hostname; ?></div>

<p><?php echo "<div id=\"online_text1\">" . "$bps_message1" . "</div><br>"; ?></p>
<p><?php echo "<div id=\"online_text1\">" . "$bps_message2" . "</div><br>"; ?></p>
<p>Your IP Address is: <?php echo htmlspecialchars($_SERVER['REMOTE_ADDR']); ?></p>

</td>
</tr>
</table>
</div>
</body>
</html>