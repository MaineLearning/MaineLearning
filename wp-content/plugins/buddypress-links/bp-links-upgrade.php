<?php

/**
 * Perform database upgrades, create missing tables if necessary
 */
function bp_links_upgrade()
{
	global $wpdb, $bp;

	// get db version
	$db_version = get_site_option( 'bp-links-db-version' );
	
	// the sql to exec
	$sql = array();

	if ( !empty($wpdb->charset) )
		$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";

	$sql[] = "CREATE TABLE `{$bp->links->table_name}` (
				`id` bigint unsigned NOT NULL auto_increment,
				`cloud_id` char(32) NOT NULL,
				`user_id` bigint unsigned NOT NULL,
				`category_id` tinyint NOT NULL,
				`url` varchar(255) NOT NULL default '',
				`url_hash` char(32) NOT NULL,
				`target` varchar(25) default NULL,
				`rel` varchar(25) default NULL,
				`slug` varchar(255) NOT NULL,
				`name` varchar(255) NOT NULL,
				`description` text,
				`status` tinyint(1) NOT NULL default '1',
				`vote_count` smallint NOT NULL default '0',
				`vote_total` smallint NOT NULL default '0',
				`popularity` mediumint UNSIGNED NOT NULL default '0',
				`embed_service` char(32) default null,
				`embed_status` tinyint(1) default '0',
				`embed_data` text,
				`date_created` datetime NOT NULL,
				`date_updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
			PRIMARY KEY  (`id`),
			UNIQUE `cloud_id` (`cloud_id`),
			KEY `user_id` (`user_id`),
			KEY `category_id` (`category_id`),
			KEY `url_hash` (`url_hash`),
			KEY `slug` (`slug`),
			KEY `name` (`name`(20)),
			KEY `status` (`status`),
			KEY `vote_count` (`vote_count`),
			KEY `vote_total` (`vote_total`),
			KEY `popularity` (`popularity`),
			KEY `date_created` (`date_created`),
			KEY `date_updated` (`date_updated`)
			) {$charset_collate};";

	$sql[] = "CREATE TABLE `{$bp->links->table_name_categories}` (
				`id` tinyint(4) NOT NULL auto_increment,
				`slug` varchar(50) NOT NULL,
				`name` varchar(50) NOT NULL,
				`description` varchar(255) default NULL,
				`priority` smallint NOT NULL,
				`date_created` datetime NOT NULL,
				`date_updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
			PRIMARY KEY  (`id`),
			KEY `slug` (`slug`),
			KEY `priority` (`priority`)
			) {$charset_collate};";

	// if initial install, add default categories
	if ( empty( $db_version ) ) {
		$sql[] = "INSERT INTO `{$bp->links->table_name_categories}`
					( slug, name, description, priority, date_created )
					VALUES  ( 'news', 'News', NULL, 10, NOW() ),
							( 'humor', 'Humor', NULL, 20, NOW() ),
							( 'other', 'Other', NULL, 30, NOW() );";
	}

	$sql[] = "CREATE TABLE `{$bp->links->table_name_votes}` (
				`link_id` bigint unsigned NOT NULL,
				`user_id` bigint unsigned NOT NULL,
				`vote` tinyint(1) NOT NULL,
				`date_created` datetime NOT NULL,
				`date_updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
			PRIMARY KEY  (`user_id`,`link_id`),
			KEY `link_id` (`link_id`),
			KEY `date_created` (`date_created`)
			) {$charset_collate};";

	$sql[] = "CREATE TABLE `{$bp->links->table_name_linkmeta}` (
				`id` bigint NOT NULL auto_increment,
				`link_id` bigint unsigned NOT NULL,
				`meta_key` varchar(255) default NULL,
				`meta_value` longtext,
			PRIMARY KEY  (`id`),
			KEY `meta_key` (`meta_key`),
			KEY `link_id` (`link_id`)
			) {$charset_collate};";

	require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
	dbDelta($sql);

	// get a db version?
	if ( $db_version ) {
		// upgrades!
		bp_links_upgrade_04( $db_version );
	}

	// update db version to current
	update_site_option( 'bp-links-db-version', BP_LINKS_DB_VERSION );
}

/**
 * Perform upgrades for version 0.4
 *
 * @param integer $db_version
 * @return boolean
 */
function bp_links_upgrade_04( $db_version ) {
	global $bp, $wpdb;

	// if DB version is 7 or higher, skip this upgrade
	if ( $db_version >= 7 )
		return true;

	// populate the new cloud_id column in the links table
	// we are trying to produce a PERMANENT unique hash, it doesn't need to be reproducable
	$sql_cloud = $wpdb->prepare( "UPDATE {$bp->links->table_name} SET cloud_id = MD5(CONCAT(%s,id,url,name))", $bp->root_domain );
	if ( false === $wpdb->query($sql_cloud) )
		return false;

	// update the activity table item_id column replacing the link_id with the cloud_id
	$sql_activity = $wpdb->prepare( "UPDATE {$bp->links->table_name} AS l, {$bp->activity->table_name} AS a SET a.item_id = l.cloud_id WHERE l.id = a.item_id AND a.component = %s", bp_links_id() );
	if ( false === $wpdb->query($sql_activity) )
		return false;

	// success!
	return true;
}

?>
