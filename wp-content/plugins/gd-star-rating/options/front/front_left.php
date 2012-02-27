<div class="postbox gdrgrid frontleft">
        <small style="float: right; margin-right:6px; margin-top:6px;">
            <a target="_blank" href="http://www.gdstarrating.com/"><?php _e("See All", "gd-star-rating"); ?></a> | <a href="http://feeds2.feedburner.com/GdStarRating">RSS</a>
        </small>
        <h3 class="hndle"><span><?php _e("Latest News", "gd-star-rating"); ?></span></h3>
        <div class="gdsrclear"></div>
    <div class="inside">
        <?php

          if ($options["news_feed_active"] == 1) {
              $rss = fetch_rss('http://www.gdstarrating.com/feed/');
              if (isset($rss->items) && 0 != count($rss->items))
              {
                echo '<ul>';
                $rss->items = array_slice($rss->items, 0, 4);
                foreach ($rss->items as $item)
                {
                ?>
                  <li>
                  <div class="rssTitle">
                    <a target="_blank" class="rsswidget" title='' href='<?php echo wp_filter_kses($item['link']); ?>'><?php echo wp_specialchars($item['title']); ?></a>
                    <span class="rss-date"><?php echo human_time_diff(strtotime($item['pubdate'], time())); ?></span>
                    <div class="gdsrclear"></div>
                  </div>
                  <div class="rssSummary"><?php echo '<strong>'.date("F, jS", strtotime($item['pubdate'])).'</strong> - '.$item['description']; ?></div></li>
                <?php
                }
                echo '</ul>';
              }
              else
              {
                ?>
                <p><?php printf(__("An error occured while loading newsfeed. Go to the %sfront page%s to check for updates.", "gd-star-rating"), '<a href="http://www.gdstarrating.com/">', '</a>') ?></p>
                <?php
              }
          }
          else {
            ?>
            <p><?php _e("Newsfeed update is disabled. You can enable it on settings page.", "gd-star-rating"); ?></p>
            <?php
          }

        ?>
    </div>
</div>
