<?php
/**
 * BP Links admin index
 */

if ( defined( 'BP_LINKS_PRO_VERSION' ) ) {
	$bp_links_pro_installed = true;
	$bp_links_pro_version = BP_LINKS_PRO_VERSION;
} else {
	$bp_links_pro_installed = false;
	$bp_links_pro_version = 'Not Installed';
}

?>
<div class="wrap nosubsub buddypress-links-admin-general">

	<?php screen_icon( 'bp-links' ); ?>

	<h2><?php _e( 'BuddyPress Links', 'buddypress-links' ) ?></h2>

	<h3><?php _e( 'Thank you for installing BuddyPress Links!', 'buddypress-links' ) ?></h3>
	
	<table border="0" class="widefat">
		<thead>
			<tr>
				<th colspan="2">
					<?php _e( 'Version Information', 'buddypress-links' ) ?>
				</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<th><?php _e( 'Base:', 'buddypress-links' ) ?></th>
				<th><?php print BP_LINKS_VERSION ?></th>
			</tr>
			<tr>
				<th><?php _e( 'Pro Extension:', 'buddypress-links' ) ?></th>
				<th>
					<?php print $bp_links_pro_version ?>
					<?php if ( !$bp_links_pro_installed ): ?>
						<a href="http://shop.presscrew.com/shop/buddypress-links/" target="_blank" style="margin-left: 10px;"><?php _e( 'Purchase', 'buddypress-links' ) ?></a>
					<?php endif; ?>
				</th>
			</tr>
		</tbody>
	</table>
	
	<h3><?php _e( 'Additional Activation Steps:', 'buddypress-links' ) ?></h3>

	<p>
		<?php _e( 'These additional steps are required to get this plugin working after the first activation.', 'buddypress-links' ) ?>
	</p>
	
	<ol>
		<li><?php _e( 'Click on <strong>Settings</strong> under the <strong>Dashboard</strong> menu.', 'buddypress-links' ) ?></li>
		<li><?php _e( 'Click on <strong>BuddyPress</strong> under the <strong>Settings</strong> menu.', 'buddypress-links' ) ?></li>
		<li><?php _e( 'Click the <strong>Pages</strong> tab on the <strong>BuddyPress</strong> settings screen.', 'buddypress-links' ) ?></li>
		<li><?php _e( 'Under directories, assign a page to the <strong>Links</strong> component by selecting an existing page, or creating a new one.', 'buddypress-links' ) ?></li>
		<li><?php _e( 'Click the <strong>Save</strong> button.', 'buddypress-links' ) ?></li>
		<li><?php _e( 'The <strong>Links</strong> item in your site navigation should now load the links component!', 'buddypress-links' ) ?></li>
	</ol>

	<!-- h3>Documentation</h3>
	<p>
		TODO
	</p -->

	<h3><?php _e( 'Support', 'buddypress-links' ) ?></h3>
	<p>
		<?php _e( 'There are two levels of support:', 'buddypress-links' ) ?>
	</p>
	<ul>
		<li><?php _e( "For support on the community version, head over to this plugin's", 'buddypress-links' ) ?> <a href="http://buddypress.org/community/groups/buddypress-links/home/" target="_blank"><?php _e( 'official group', 'buddypress-links' ) ?></a> <?php _e( 'on BuddyPress.org', 'buddypress-links' ) ?></li>
		<li><?php _e( 'For premium support on the community and pro versions, head over to the Press Crew', 'buddypress-links' ) ?> <a href="http://community.presscrew.com/discussion/premium-plugins/" target="_blank"><?php _e( 'premium plugin forums', 'buddypress-links' ) ?></a>.</li>
	</ul>

	<h3><?php _e( 'Pro Extension', 'buddypress-links' ) ?></h3>
	<p>
		<?php _e( 'The pro extension, available for purchase in the' ); ?>
		<a href="http://shop.presscrew.com/shop/buddypress-links/" target="_blank"><?php _e( 'Press Crew Shop', 'buddypress-links' ) ?></a>
		<?php _e( 'adds the following additional features:', 'buddypress-links' ) ?>
	</p>
	<h4><?php _e( 'Additional Rich Media Support', 'buddypress-links' ) ?></h4>
	<ul>
		<li><a href="http://www.dailymotion.com/" target="_blank"><?php _e( 'Dailymotion', 'buddypress-links' ) ?></a></li>
		<li><a href="http://www.vimeo.com/" target="_blank"><?php _e( 'Vimeo', 'buddypress-links' ) ?></a></li>
	</ul>
	<h4><?php _e( 'Member Links Sharing', 'buddypress-links' ) ?></h4>
	<ul>
		<li><?php _e( "Share other member's links on their profile.", 'buddypress-links' ) ?></li>
		<li><?php _e( 'Share any link with a group they are a member of.', 'buddypress-links' ) ?></li>
	</ul>
	<h4><?php _e( 'Groups Integration', 'buddypress-links' ) ?></h4>
	<ul>
		<li><?php _e( 'Group members can add a link to any group they are a member of, directly from the group.', 'buddypress-links' ) ?></li>
		<li><?php _e( 'Fully integrated with the group activity stream.', 'buddypress-links' ) ?></li>
		<li><?php _e( "Each group has their own links mini-directory which lists only that group's links.", 'buddypress-links' ) ?></li>
		<li><?php _e( 'Separate tabs for listing all group links, or just my group links.', 'buddypress-links' ) ?></li>
		<li><?php _e( 'The same powerful category and order filtering is available.', 'buddypress-links' ) ?></li>
		<li><?php _e( 'Group administrators can remove group links, with prejudice.', 'buddypress-links' ) ?></li>
	</ul>
	
	<h3><?php _e( 'Developer Extras', 'buddypress-links' ) ?></h3>
	<ul>
		<li><a href="http://plugins.trac.wordpress.org/log/buddypress-links" target="_blank"><?php _e( 'Trac Revision Log', 'buddypress-links' ) ?></a></li>
		<li><a href="http://plugins.trac.wordpress.org/browser/buddypress-links/" target="_blank"><?php _e( 'Trac Browser', 'buddypress-links' ) ?></a></li>
	</ul>

	<h3><?php _e( 'About the Author:', 'buddypress-links' ) ?></h3>
	<ul>
		<li><a href="http://marshallsorenson.com/" target="_blank"><?php _e( "Marshall Sorenson's Blog", 'buddypress-links' ) ?></a></li>
		<li><a href="http://buddypress.org/community/members/MrMaz/" target="_blank"><?php _e( 'MrMaz on BuddyPress.org', 'buddypress-links' ) ?></a></li>
	</ul>

	<h3><?php _e( 'Credits:', 'buddypress-links' ) ?></h3>
	<ul>
		<li>
			<?php _e( 'Logo Elements:', 'buddypress-links' ) ?>
			<?php _e( '&quot;Share&quot; symbol by The Noun Project, from', 'buddypress-links' ) ?> <a href="http://thenounproject.com" target="_blank"><?php _e( 'The Noun Project', 'buddypress-links' ) ?></a> <?php _e( 'collection.', 'buddypress-links' ) ?></p>
		</li>
	</ul>

</div>

<?php include 'sidebar.php'; ?>
