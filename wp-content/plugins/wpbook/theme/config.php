<?php
if(!class_exists('Facebook')) {  
  include_once(WP_PLUGIN_DIR . '/wpbook/includes/client/facebook.php');  
}
  
$canvas_page = $proto . "://apps.facebook.com/" . $app_url . "/";
  
$auth_url = $proto ."://www.facebook.com/dialog/oauth?client_id=" 
  . $api_key . "&redirect_uri=" . urlencode($canvas_page);
  
$signed_request = $_REQUEST["signed_request"];
 
 
list($encoded_sig, $payload) = explode('.', $signed_request, 2); 
  
$data = json_decode(base64_decode(strtr($payload, '-_', '+/')), true);
  
// if we're on app_tab we should not need user logged in   
if (!isset($_REQUEST['app_tab'])) {
  if (empty($data["user_id"])) {
    echo("<script> top.location.href='" . $auth_url . "'</script>");
  } else {
    $access_token = $data["oauth_token"];   
  }
}

/* should not store in user_meta - need to store as an option 
 * If a wp_user id was passed in, that lets us know they came from wp
 * And they are the $target_admin of the FB app, so we should store their ID
 */   
if ((isset($_REQUEST["wp_user"])) && ($data["user_id"] == $target_admin)) {
  update_option('wpbook_user_access_token',$access_token);
}
?>
