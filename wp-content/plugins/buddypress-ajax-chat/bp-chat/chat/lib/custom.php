<?php
/*
 * @package AJAX_Chat
 * @author Sebastian Tschan
 * @copyright (c) Sebastian Tschan
 * @license GNU Affero General Public License
 * @link https://blueimp.net/ajax/
 */

// Include custom libraries and initialization code here
	session_start();
if ( isset($_GET['name']) && $_GET['name'] != "" )
{
    $_SESSION['loggedin_user_fullname'] = $_GET['name'];
}
if ( isset($_GET['id']) )
{
    $_SESSION['loggedin_user_id'] = $_GET['id'];
}
if ( isset($_GET['role']) )
{
    $_SESSION['loggedin_user_role'] = $_GET['role'];
}
if ( isset($_GET['channels_url']) )
{
    $_SESSION['xml_channels_url'] = $_GET['channels_url'];
}
if ( isset($_GET['all_channels_url']) )
{
    $_SESSION['xml_all_channels_url'] = $_GET['all_channels_url'];
}
if ( isset($_GET['friends_url']) )
{
    $_SESSION['xml_friends_url'] = $_GET['friends_url'];
}
if ( isset($_GET['logout_url']) && trim($_GET['logout_url']) != "")
{
    $_SESSION['xml_logout_url'] = $_GET['logout_url'];
}
?>
