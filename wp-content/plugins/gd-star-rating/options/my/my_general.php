<?php

global $user_ID;
$overview = gdsrAdmDB::get_user_votes_overview($user_ID);

$over_posts = $over_comms = array();
if (isset($overview["article"])) $over_posts[] = "Standard: <strong>".$overview["article"]."</strong>";
if (isset($overview["artthumb"])) $over_posts[] = "Thumbs: <strong>".$overview["artthumb"]."</strong>";
if (isset($overview["multis"])) $over_posts[] = "Multis: <strong>".$overview["multis"]."</strong>";
if (isset($overview["comment"])) $over_comms[] = "Standard: <strong>".$overview["comment"]."</strong>";
if (isset($overview["cmmthumb"])) $over_comms[] = "Thumbs: <strong>".$overview["cmmthumb"]."</strong>";
$over_posts = count($over_posts) > 0 ? join(", ", $over_posts) : "/";
$over_comms = count($over_comms) > 0 ? join(", ", $over_comms) : "/";

?>
<div class="postbox gdrgrid frontright">
    <h3 class="hndle"><span><?php _e("General Statistics", "gd-star-rating"); ?></span></h3>
    <div class="inside">
        <div class="my-general">
            <p class="sub"><?php _e("My votes overview", "gd-star-rating"); ?></p>
            <div class="table">
                <table><tbody>
                    <tr class="first">
                        <td class="first b" style="width: 100px;"><?php _e("Posts", "gd-star-rating"); ?></td>
                        <td class="t options"><?php echo $over_posts; ?></td>
                    </tr>
                    <tr>
                        <td class="first b" style="width: 100px;"><?php _e("Comments", "gd-star-rating"); ?></td>
                        <td class="t options"><?php echo $over_comms; ?></td>
                    </tr>
                </tbody></table>
            </div>
        </div>
    </div>
</div>
