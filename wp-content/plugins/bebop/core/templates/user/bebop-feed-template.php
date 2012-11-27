<?php
/**
 * RSS2 Feed Template for displaying various faceted feeds
 *
 * @package BP Lotsa Feeds
 */
header( 'Content-Type: text/xml; charset=' . get_option( 'blog_charset' ), true );
header( 'Status: 200 OK' );

echo '<?xml version="1.0" encoding="'.get_option( 'blog_charset' ).'" ?'.'>';
?>

<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	<?php do_action( 'bp_activity_personal_feed' ); ?>
>

<channel>
	<title><?php echo bebop_feed_type(); ?></title>
	<atom:link href="<?php self_link(); ?>" rel="self" type="application/rss+xml" />
	<link><?php echo bebop_feed_url() ?></link>
	<description><?php echo bebop_feed_description() ?></description>
	<pubDate><?php echo mysql2date( 'D, d M Y H:i:s O', bp_activity_get_last_updated(), false ); ?></pubDate>
	<generator>http://buddypress.org/?v=<?php echo BP_VERSION ?></generator>
	<language>en_US</language>
	<?php do_action( 'bp_activity_personal_comment_feed_head' ); ?>
	
	<?php
	if ( bp_has_activities( bebop_activity_args() ) ) {
		while ( bp_activities() ) : bp_the_activity();
		?><item>
			<dbid><?php echo bp_activity_id(); ?></dbid>
			<guid><?php echo bp_activity_thread_permalink(); ?></guid>
			<type><?php echo bp_activity_action_name(); ?></type>
			<title><?php bp_activity_feed_item_title(); ?></title>
			<link><![CDATA[
			<?php echo bp_activity_thread_permalink(); ?>
			]]></link>
			<wpPubDate><?php echo mysql2date( 'D, d M Y H:i:s O', bp_activity_feed_item_date(), false ); ?></wpPubDate>
			<pubDate><?php echo bebop_feed_date_recorded( bp_get_activity_secondary_item_id() ); ?></pubDate>
			<description><![CDATA[
				<?php echo strip_tags( bp_get_activity_feed_item_description(), '<a>' );?>
			]]></description>
			<?php do_action( 'bp_activity_personal_feed_item' ); ?>
		</item>
		<?php
		endwhile;
	}
	else
	{
		echo 'no "' .  bebop_feed_type() . '" data found.';
		echo "\r";
	}
	?>
</channel>
</rss>