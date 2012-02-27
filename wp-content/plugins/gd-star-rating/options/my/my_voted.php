<div class="postbox gdrgrid frontright myboxes">
    <h3 class="hndle"><span><?php _e("My Latest 100 Votes", "gd-star-rating"); ?></span></h3>
    <div class="inside">
    <?php

    global $user_ID;
    $data = gdsrDB::filter_latest_votes($sett, $user_ID);
    GDSRHelper::render_dash_widget_vote($data, "my-panel", "-my-latest");

    ?>
    </div>
</div>
