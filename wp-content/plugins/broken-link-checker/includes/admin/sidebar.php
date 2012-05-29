<?php
$configuration = blc_get_configuration();

if ( !function_exists('fetch_feed') ){
	include_once(ABSPATH . WPINC . '/feed.php');
}

$show_plugin_feed = $show_ame_ad = false;
if ( !$configuration->get('user_has_donated', false) ) {
	if ( (blcUtility::constrained_hash(get_site_url() . 'y', 0, 100) < 40) && function_exists('fetch_feed') ) {
		$show_plugin_feed = true;
	} else {
		$show_ame_ad = true;
	}
}
?>

<!-- "More plugins" RSS feed -->
<?php
if ( $show_plugin_feed ):
	$feed_url = 'http://w-shadow.com/files/blc-plugin-links.rss';
	$num_items = 3;

	$feed = fetch_feed($feed_url);
	if ( !is_wp_error($feed) ):
?>
<div id="advertising" class="postbox">
	<h3 class="hndle"><?php _e('More plugins by Janis Elsts', 'broken-link-checker'); ?></h3>
	<div class="inside">
		<ul>
		<?php
		foreach($feed->get_items(0, $num_items) as $item) { /** @var SimplePie_Item $item */
			printf(
				'<li><a href="%1$s" title="%2$s">%3$s</a></li>',
				esc_url( $item->get_link() ),
				esc_attr( strip_tags( $item->get_title() ) ),
				esc_html( $item->get_title() )
			);
		}
		?>
		</ul>
	</div>
</div>
<?php
	endif;
endif;
?>

<!-- Admin Menu Editor Pro ad -->
<?php
if ( $show_ame_ad ):
	//Display an ad for Admin Menu Editor.
	//We're A/B testing a bunch of different ad copies.
	$ame_copy_variants = array(
		array('a', "Add, delete, hide, or move any admin menu item."),
		array('b', "Organize your admin menu the way you want it."),
		array('c', "Hide, move or customize admin menus. Perfect for client sites."),
	);
	$ad_copy_index = intval(blcUtility::constrained_hash(get_site_url(), 0, count($ame_copy_variants)));
	$ad_copy = $ame_copy_variants[$ad_copy_index];

	$ad_url = sprintf(
		'http://w-shadow.com/admin-menu-editor-pro/?utm_source=broken_link_checker&utm_medium=text_link&utm_campaign=Plugins&utm_content=%s',
		urlencode('ad_copy_') . $ad_copy[0]
	);
?>
<div class="postbox" id="advertising">
	<h3 class="hndle"><?php _e('More plugins by Janis Elsts', 'broken-link-checker'); ?></h3>
	<div class="inside">
		<p class="ws-ame-ad-copy"><?php echo $ad_copy[1]; ?></p>
		<p class="ws-ame-ad-link">
			<a href="<?php echo esc_attr($ad_url); ?>" title="Admin Menu Editor">
				Admin Menu Editor
			</a>
		</p>
	</div>
</div>

<?php
endif;
?>

<!-- Donation button -->
<div id="donate" class="postbox">
	<h3 class="hndle"><?php _e('Donate $10, $20 or $50!', 'broken-link-checker'); ?></h3>
	<div class="inside">
		<p><?php
		_e('If you like this plugin, please donate to support development and maintenance!', 'broken-link-checker');							
		?></p>
		
		<form style="text-align: center;" action="https://www.paypal.com/cgi-bin/webscr" method="post">
			<input type="hidden" name="cmd" value="_donations">
			<input type="hidden" name="business" value="G3GGNXHBSHKYC">
			<input type="hidden" name="lc" value="US">
			<input type="hidden" name="item_name" value="Broken Link Checker">
			<input type="hidden" name="no_note" value="1">
			<input type="hidden" name="no_shipping" value="1">
			<input type="hidden" name="currency_code" value="USD">
			<input type="hidden" name="bn" value="PP-DonationsBF:btn_donateCC_LG.gif:NonHosted">
			
			<input type="hidden" name="rm" value="2">
			<input type="hidden" name="return" value="<?php 
				echo esc_attr(admin_url('options-general.php?page=link-checker-settings&donated=1')); 
			?>" />
			<input type="hidden" name="cbt" value="<?php 
				echo esc_attr(__('Return to WordPress Dashboard', 'broken-link-checker')); 
			?>" />
			<input type="hidden" name="cancel_return" value="<?php 
				echo esc_attr(admin_url('options-general.php?page=link-checker-settings&donation_canceled=1')); 
			?>" />
			
			<input type="image" src="https://www.sandbox.paypal.com/en_US/GB/i/btn/btn_donateCC_LG.gif" name="submit" alt="PayPal - The safer, easier way to pay online." style="max-width:170px;height:47px;border:0;">
		</form>
	</div>					
</div>

<!-- Other advertising -->
<?php
if ( !$configuration->get('user_has_donated') ):
	$ad_switch_time = strtotime('2012-06-05 12:00');
	if ( time() < $ad_switch_time ):
?>
		<div id="themefuse-ad" class="postbox">
			<!--<h3 class="hndle">ThemeFuse</h3> -->
			<div class="inside">
				<a href="http://themefuse.com/wp-themes-shop/?plugin=broken-link-checker" title="ThemeFuse themes">
					<img src="<?php echo plugins_url('images/themefuse-250x250.jpg', BLC_PLUGIN_FILE) ?>" width="250" height="250" alt="ThemeFuse">
				</a>
			</div>
		</div>
<?php
	else:
?>
		<div id="managewp-ad" class="postbox">
			<div class="inside">
				<a href="http://managewp.com/?utm_source=broken_link_checker&utm_medium=Banner&utm_content=mwp250_2&utm_campaign=Plugins" title="ManageWP">
					<img src="<?php echo plugins_url('images/mwp250_2.png', BLC_PLUGIN_FILE) ?>" width="250" height="250" alt="ManageWP">
				</a>
			</div>
		</div>
<?php
	endif;
endif; ?>