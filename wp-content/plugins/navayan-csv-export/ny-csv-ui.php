<?php include_once ('ny-csv-define.php'); ?>
<link rel="stylesheet" type="text/css" href="<?php echo NY_PLUGIN_DIR; ?>ny-csv-ui.css" />

<div id="wrapper">
	<div class="titlebg"><span class="head i_mange_coupon"><h1><?php echo NY_PLUGIN_NAME;?></h1></span></div>
	<div id="page">
		<h4><?php echo NY_PLUGIN_INFO;?></h4>
		<div class="ny-left">
						
			<div class="ny-left-widgets">				 
				<table border="1" class="widefat" style="width:98%">
					<thead>
						<tr>
							<th class="manage-column">Sr.</th>
							<th class="manage-column">Table Name</th>
							<th class="manage-column">Action</th>
						</tr>
					</thead>
					<tbody>

					<?php
						$p='';
						$alltables = mysql_query("SHOW TABLES");
						while ($table = mysql_fetch_assoc($alltables)){
							$p += sizeof($table);
							foreach ($table AS $tablename){
					?>
						<tr class="<?php if($p%2 != 0) echo 'alternate';?> author-self status-publish iedit">
							<td><?php echo $p; ?></td>
							<td><strong><?php echo $tablename;?></strong></td>
							<td>
								<a href="<?php echo site_url();?>/wp-admin/tools.php?page=export&nycsv=<?php echo $tablename;?>">
									<input type="submit" class="button-secondary action" value="Export to CSV" name="submit"/>
								</a>
							</td>
						</tr>

					<?php } } ?>
          </tbody>
					<tfoot>
						<tr>
							<th class="manage-column" colspan="3">&nbsp;</th>
						</tr>
					</tfoot>
        </table>
			</div>
    </div><!-- .ny-left -->
          
		<div class="ny-right">
			
			<div class="ny-widgets">
				<h3>About</h3>
				<div class="ny-widgets-desc">
					<p><?php echo NY_PLUGIN_ABOUT;?></p>
					<form method="post" action="https://www.paypal.com/cgi-bin/webscr" style="text-align:center">
						<input type="hidden" name="cmd" value="_donations">
						<input type="hidden" name="business" value="<?php echo NY_AUTHOR_EMAIL;?>">
						<input type="hidden" name="lc" value="US">
						<input type="hidden" name="item_name" value="<?php echo NY_SITE;?>">
						<input type="hidden" name="no_note" value="0">
						<input type="hidden" name="currency_code" value="USD">
						<input type="hidden" name="bn" value="PP-DonationsBF:btn_donate_LG.gif:NonHostedGuest">
						<input type="image" src="<?php echo NY_PLUGIN_DIR; ?>btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
					</form>
					<p><?php echo NY_DONATE_TEXT;?><br/>- <a href="<?php echo AUTHOR_PROFILE;?>" target="_blank"><?php echo NY_PLUGIN_AUTHOR;?></a></p>
				</div>
			</div>
			
			<?php
				# Get latest posts
				include_once(ABSPATH . WPINC . '/feed.php');
				// Get a SimplePie feed object from the specified feed source.
				$rss = fetch_feed( NY_PLUGIN_URL . 'feed/' );
				if (!is_wp_error( $rss ) ) { // Checks that the object is created correctly 
					$max = $rss->get_item_quantity(5);
					$rss_items = $rss->get_items(0, $max); 
				}
			?>
			<?php if ($max > 0) : ?>
				<div class="ny-widgets">
					<h3>Recent Posts</h3>
					<div class="ny-widgets-desc">
						<ul>
							<?php
								foreach ( $rss_items as $item ){
									echo "<li><a href='". esc_url( $item->get_permalink() ) ."' target='_blank'>". esc_html( $item->get_title() ) ."</a></li>";
								}
							?>
						</ul>
					</div>
				</div>
			<?php endif; ?>
			
		</div><!-- .ny-right -->
		
	</div>
</div>