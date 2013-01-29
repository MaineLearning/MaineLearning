<div class="postbox gdrgrid frontleft">
        <small style="float: right; margin-right:6px; margin-top:6px;">
            <a target="_blank" href="http://www.dev4press.com/category/blog/"><?php _e("See All", "gd-star-rating"); ?></a> | <a href="http://feeds2.feedburner.com/GdStarRating">RSS</a>
        </small>
        <h3 class="hndle"><span><?php _e("Latest News", "gd-star-rating"); ?></span></h3>
        <div class="gdsrclear"></div>
    <div class="inside">
        <?php

        if ($options['news_feed_active'] == 1) {
            $feed = fetch_feed('http://www.dev4press.com/feed/');
                if (!is_wp_error( $feed )) {
                    $items = $feed->get_items(0, 4);
                    if (! empty($items)) {
                        echo '<ul>';
                        foreach ($items as $item) {
                        ?>
                                <li>
                                    <div class="rssTitle">
                                        <a target="_blank" class="rsswidget" title='' href='<?php echo wp_filter_kses($item->get_link()); ?>'><?php echo esc_html($item->get_title()); ?></a>
                                        <span class="rss-date"><?php echo human_time_diff($item->get_date('U'), time()); ?></span>
                                        <div class="gdsrclear"></div>
                                    </div>
                                    <div class="rssSummary"><?php echo '<strong>', $item->get_date("F, jS"), '</strong> - ', $item->get_description(); ?></div>
                                </li>
                        <?php
                        }
                        echo '</ul>';
                    } else {
                        ?>
                        <p><?php printf(__("No news items found, possibly due to an error. Go to the %sfront page%s to check for updates.", "gd-star-rating"), '<a href="http://www.gdstarrating.com/">', '</a>') ?></p>
                        <?php
                    }
                } else {
                ?>
                  <p><?php printf(__("An error occured while loading newsfeed: %s. Go to the %sfront page%s to check for updates.", "gd-star-rating"), $feed->get_error_message(), '<a href="http://www.gdstarrating.com/">', '</a>') ?></p>
                <?php
              }
          } else {
            ?>
                <p><?php _e("Newsfeed update is disabled. You can enable it on settings page.", "gd-star-rating"); ?></p>
            <?php
          }

        ?>
    </div>
</div>
