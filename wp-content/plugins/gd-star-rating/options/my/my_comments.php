<div class="postbox gdrgrid frontright myboxes">
    <h3 class="hndle"><span><?php _e("Votes for my comments", "gd-star-rating"); ?></span></h3>
    <div class="inside">
    <?php

    global $user_ID;
    $data = gdsrAdmDB::filter_votes_by_type($user_ID, "'cmmthumb', 'comment'", false);
    GDSRHelper::render_dash_widget_vote($data, "my-panel", "-my-comments");

    ?>
    </div>
</div>
