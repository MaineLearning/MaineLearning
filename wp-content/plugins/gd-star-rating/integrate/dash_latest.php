<?php

$data = gdsrDB::filter_latest_votes($o);
GDSRHelper::render_dash_widget_vote($data);

?>
<div id="gdsr-latest-cmds">
    <a class="button" href="admin.php?page=gd-star-rating-stats"><?php _e("Articles Log", "gd-star-rating"); ?></a>
    <a class="button" href="admin.php?page=gd-star-rating-users"><?php _e("Users Log", "gd-star-rating"); ?></a>
</div>