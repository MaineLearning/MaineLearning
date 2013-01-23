<?php
/**
 * BP_Links classes
 *
 * @package BP_Links
 * @author Marshall Sorenson
 */

// include settings framework
require_once( BP_LINKS_LIB_DIR . '/wp-settings-framework.php' );

/**
 * Custom settings class extension
 */
class BP_Links_Settings extends WordPressSettingsFramework
{
	/**
	 * Name of the option group
	 */
	const OPTION_GROUP = 'buddypress_links';

	/**
	 * Singleton instance
	 *
	 * @var BP_Links_Settings
	 */
	private static $instance;

	/**
	 */
	public function __construct()
	{
		// call parent constructor
		parent::__construct( BP_LINKS_PLUGIN_DIR . '/bp-links-settings.php', self::OPTION_GROUP );

		// add settings validation filter
		add_filter( $this->get_option_group() . '_settings_validate', array( $this, 'validate_settings' ) );
	}

	/**
	 * Return singleton istance
	 *
	 * @return BP_Links_Settings
	 */
	final public static function instance()
	{
		if ( !self::$instance instanceof BP_Links_Settings ) {
			self::$instance = new BP_Links_Settings();
		}

		return self::$instance;
	}

	final public static function init( $the_plugin_page )
	{
		global $plugin_page, $pagenow;

		// init settings
		if (
			( 'options.php' == $pagenow && isset( $_POST['option_page'] ) && self::OPTION_GROUP == $_POST['option_page'] ) ||
			$the_plugin_page == $plugin_page
		) {
			return self::instance();
		}
	}

	final public static function get_defaults()
	{
		global $wpsf_settings;

		require_once BP_LINKS_PLUGIN_DIR . '/bp-links-settings.php';

		$defaults = array();

		if ( !empty( $wpsf_settings ) ) {
			foreach( $wpsf_settings as $section ) {
				if ( isset( $section['section_id'] ) && isset( $section['fields'] ) ) {
					foreach( $section['fields'] as $field ) {
						if ( isset( $field['id'] ) ) {
							$key = sprintf( '%s_%s_%s', self::OPTION_GROUP, $section['section_id'], $field['id'] );
							$std = ( isset( $field['std'] ) ) ? $field['std'] : null;
							$defaults[ $key ] = $std;
						}
					}
				}
			}
		}

		return $defaults;
	}

	final public static function get_settings()
	{
		return wp_parse_args(
			wpsf_get_settings( BP_PLUGIN_DIR . '/bp-links-settings.php', self::OPTION_GROUP ),
			self::get_defaults()
		);
	}

	public function validate_settings( $input )
	{
		// Same as $sanitize_callback from http://codex.wordpress.org/Function_Reference/register_setting
		return $input;
	}

}

/**
 * A link belonging to a member
 *
 * Do not cache an instance of this class when it has been created to cast a vote!!!
 *
 * @package BP_Links
 * @author Marshall Sorenson (based on original work of Andy Peatling)
 */
class BP_Links_Link {
	
	// target constants
	// UNUSED AT THIS TIME
	const TARGET_BLANK = '_blank';
	const TARGET_PARENT = '_parent';
	const TARGET_TOP = '_top';
	const TARGET_BPLINK = '_bplink';
	const TARGET_DEFAULT = null;

	// rel constants
	// UNUSED AT THIS TIME
	const REL_NOFOLLOW = 'nofollow';
	const REL_DEFAULT = self::REL_NOFOLLOW;

	// status constants
	const STATUS_PUBLIC = 1;
	const STATUS_FRIENDS = 2;
	const STATUS_HIDDEN = 3;

	// popularity constants
	const POPULARITY_THRESH = 1;
	const POPULARITY_DEFAULT = 16777213;
	const POPULARITY_IGNORE = 16777214;
	const POPULARITY_RETIRED = 16777215;

	// embed service constants
	const EMBED_SERVICE_NONE = null;
	const EMBED_SERVICE_PICAPP = '1';
	const EMBED_SERVICE_FOTOGLIF = '2';

	// embed status constants
	const EMBED_STATUS_PARTIAL = -1;
	const EMBED_STATUS_DISABLED = 0;
	const EMBED_STATUS_ENABLED = 1;

	// data members
	var $id;
	var $cloud_id;
	var $user_id;
	var $category_id;
	var $url;
	var $url_hash;
	var $target;
	var $rel;
	var $slug;
	var $name;
	var $description;
	var $status = self::STATUS_PUBLIC;
	var $date_created;
	var $date_updated;

	// denormalized vote data
	var $vote_count = 0;
	var $vote_total = 0;
	var $popularity = self::POPULARITY_DEFAULT;

	// embedded media options
	private $embed_service;
	private $embed_status;
	private $embed_data;

	/**
	 * This will be true if this object is brand new
	 * 
	 * @var boolean
	 */
	private $_is_new = true;

	/**
	 * Category object for this link
	 *
	 * @var BP_Links_Category
	 */
	private $_category_obj;

	/**
	 * Vote object of currently logged in member
	 *
	 * @var BP_Links_Vote
	 */
	private $_user_vote_obj;

	/**
	 * Embed service object for this link
	 *
	 * @var BP_Links_Embed_Service
	 */
	private $_embed_service_obj;

	function bp_links_link( $id = null, $single = false ) {
		
		if ( $id ) {
			$this->id = $id;
			$this->populate();
		}
		
		if ( $single ) {
			$this->populate_meta();
		}
	}
	
	function populate() {
		global $wpdb, $bp;

		$this->_is_new = false;

		$sql_parts['select'] = 'SELECT l.*';

		$sql_parts['select_x'] =
			apply_filters( 'bp_links_link_populate_select_x', '' );

		$sql_parts['from'] = "FROM {$bp->links->table_name} AS l";

		$sql_parts['from_x'] =
			apply_filters( 'bp_links_link_populate_from_x', '' );

		$sql_parts['join'] = '';

		$sql_parts['join_x'] =
			apply_filters( 'bp_links_link_populate_join_x', '' );

		$sql_parts['where_0'] = 'WHERE';

		$sql_parts['where_1'] =
			apply_filters(
				'bp_links_link_populate_where',
				$wpdb->prepare( 'l.id = %d', $this->id )
			);
		
		$sql_parts['where_x'] =
			apply_filters( 'bp_links_link_populate_where_x', '' );

		$sql = implode( ' ', $sql_parts );

		// evil check
		if ( strpos( $sql, ';' ) ) {
			throw new Exception( 'Unexpected character in SQL statement' );
		}

		// exec query
		$link = $wpdb->get_row( $sql );

		if ( $link ) {
			// link data
			$this->cloud_id = $link->cloud_id;
			$this->user_id = $link->user_id;
			$this->category_id = $link->category_id;
			$this->url = $link->url;
			$this->url_hash = $link->url_hash;
			$this->target = $link->target;
			$this->rel = $link->rel;
			$this->slug = $link->slug;
			$this->name = $link->name;
			$this->description = $link->description;
			$this->status = $link->status;
			$this->vote_count = $link->vote_count;
			$this->vote_total = $link->vote_total;
			$this->popularity = $link->popularity;
			$this->embed_service = $link->embed_service;
			$this->embed_status = $link->embed_status;
			$this->embed_data = $link->embed_data;
			$this->date_created = strtotime( $link->date_created );
			$this->date_updated = strtotime( $link->date_updated );
			// link profile share data
			$this->prlink_user_id = $link->prlink_user_id;
			$this->prlink_date_created = strtotime( $link->prlink_date_created );
			$this->prlink_date_updated = strtotime( $link->prlink_date_updated );
		}
	}

	function populate_meta() {
		if ( $this->id ) {
			// unused for now
		}
	}

	/**
	 * Return an id that is hopefully universally unique across any install
	 *
	 * @return string An MD5 hash
	 */
	private function generate_cloud_id() {
		global $bp;
		
		if ( $bp->root_domain && $this->url && $this->name ) {
			// hash this site's url, link url, link name, and microtime
			return md5( $bp->root_domain . $this->url . $this->name . microtime(true) );
		} else {
			return false;
		}
	}

	/**
	 * Returns true if this object will be or was just inserted
	 *
	 * @return boolean
	 */
	function is_new() {
		return $this->_is_new;
	}

	function save() {
		global $wpdb, $bp;

		// pre-save filter hooks
		$this->user_id = apply_filters( 'bp_links_link_user_id_before_save', $this->user_id, $this->id );
		$this->category_id = apply_filters( 'bp_links_link_category_id_before_save', $this->category_id, $this->id );
		$this->url = apply_filters( 'bp_links_link_url_before_save', $this->url, $this->id );
		$this->target = apply_filters( 'bp_links_link_target_before_save', $this->target, $this->id );
		$this->rel = apply_filters( 'bp_links_link_rel_before_save', $this->rel, $this->id );
 		$this->slug = apply_filters( 'bp_links_link_slug_before_save', $this->slug, $this->id );
		$this->name = apply_filters( 'bp_links_link_name_before_save', $this->name, $this->id );
		$this->description = apply_filters( 'bp_links_link_description_before_save', $this->description, $this->id );
 		$this->status = apply_filters( 'bp_links_link_status_before_save', $this->status, $this->id );

		// handle embed service values
		if ( $this->embed() instanceof BP_Links_Embed_Service ) {
			$this->embed_service = $this->embed()->key();
			$this->embed_data = $this->embed()->export_data();
		} elseif ( empty( $this->embed_service ) ) {
			$this->embed_remove();
		}

		// make sure category_id actually exists
		if ( !BP_Links_Category::check_category( $this->category_id ) ) {
			return false;
		}

		// pre-save action hook
		do_action( 'bp_links_link_before_save', $this );

		// save the user vote if exists
		// (BP_Links_Vote::save() only triggers when a vote was cast)
		if ( $this->_user_vote_obj instanceof BP_Links_Vote ) {
			if ( true !== $this->_user_vote_obj->save() ) {
				return false;
			}
		}

		// if we have an id, we are updating
		if ( $this->id ) {

			// on update we need to recalculate
			// the denormalized data from the votes table

			// update the aggregate data
			$this->vote_count = apply_filters( 'bp_links_link_vote_count_before_update_save', BP_Links_Vote::link_vote_count( $this->id ), $this->id );
			$this->vote_total = apply_filters( 'bp_links_link_vote_total_before_update_save', BP_Links_Vote::link_vote_total( $this->id ), $this->id );
			
			// now we can recalculate the popularity
			$this->popularity = apply_filters( 'bp_links_link_popularity_before_update_save', $this->popularity_recalculate(), $this->id );

			// prepare the query
			$sql = $wpdb->prepare( 
				"UPDATE {$bp->links->table_name} SET
					user_id = %d,
					category_id = %d,
					url = %s,
					url_hash = MD5(%s),
					target = %s,
					rel = %s,
					slug = %s, 
					name = %s,
					description = %s, 
					status = %d,
					vote_count = %d,
					vote_total = %d,
					popularity = %d,
					embed_service = %s,
					embed_status = %d,
					embed_data = %s,
					date_updated = %s
				WHERE
					id = %d
				",
					$this->user_id,
					$this->category_id,
					$this->url,
					$this->url,
					$this->target,
					$this->rel,
					$this->slug, 
					$this->name,
					$this->description, 
					$this->status,
					$this->vote_count,
					$this->vote_total,
					$this->popularity,
					$this->embed_service,
					$this->embed_status,
					$this->embed_data,
					date('Y-m-d H:i:s'),
					$this->id
			);
			
		} else {
			// new record

			// generate a cloud id
			$this->cloud_id = $this->generate_cloud_id();

			// these hooks allow changing the default values on new record creation
			$this->vote_count = apply_filters( 'bp_links_link_vote_count_before_insert_save', 0 );
			$this->vote_total = apply_filters( 'bp_links_link_vote_total_before_insert_save', 0 );
			$this->popularity = apply_filters( 'bp_links_link_popularity_before_insert_save', self::POPULARITY_DEFAULT );

			// prepare query
			$sql = $wpdb->prepare( 
				"INSERT INTO {$bp->links->table_name} (
					cloud_id,
					user_id,
					category_id,
					url,
					url_hash,
					target,
					rel,
					slug,
					name,
					description,
					status,
					vote_count,
					vote_total,
					popularity,
					embed_service,
					embed_status,
					embed_data,
					date_created
				) VALUES (
					%s, %d, %d, %s, MD5(%s), %s, %s, %s, %s, %s, %d, %d, %d, %d, %s, %d, %s, %s
				)",
					$this->cloud_id,
					$this->user_id,
					$this->category_id,
					$this->url,
					$this->url_hash,
					$this->target,
					$this->rel,
					$this->slug,
					$this->name,
					$this->description,
					$this->status,
					$this->vote_count,
					$this->vote_total,
					$this->popularity,
					$this->embed_service,
					$this->embed_status,
					$this->embed_data,
					date('Y-m-d H:i:s')
			);
		}
		
		if ( false === $wpdb->query($sql) )
			return false;
		
		if ( !$this->id ) {
			$this->id = $wpdb->insert_id;
		}

		do_action( 'bp_links_link_after_save', $this );
		
		return true;
	}

	function popularity_recalculate() {

		// must meet minimum vote total to be considered
		if ( $this->vote_total < self::POPULARITY_THRESH ) {
			return self::POPULARITY_IGNORE;
		} else {
			// how many minutes and days old?
			$mins_old = floor( ( time() - $this->date_created ) / 60 );
			$days_old = floor( $mins_old / 60 / 24 );

			// if more than 7 days old, time to retire it
			if ( $days_old >= 7 ) {
				return self::POPULARITY_RETIRED;
			} else {
				// simply divide minutes old by vote total
				return floor( $mins_old / $this->vote_total );
			}
		}
	}
	
	function delete() {
		global $wpdb, $bp;
		
		// Delete linkmeta for the link
		bp_links_delete_linkmeta( $this->id );
				
		// Finally remove the link entry from the DB
		if ( !$wpdb->query( $wpdb->prepare( "DELETE FROM {$bp->links->table_name} WHERE id = %d", $this->id ) ) )
			return false;

		do_action( 'bp_links_link_after_delete', $this );

		return true;
	}

	function vote() {
		global $bp;
		
		if ( !$this->_user_vote_obj instanceof BP_Links_Vote ) {
			if ( ( $this->id ) && ( $bp->loggedin_user->id ) ) {
				$this->_user_vote_obj = new BP_Links_Vote( $this->id, $bp->loggedin_user->id );
			} else {
				return false;
			}
		}

		return $this->_user_vote_obj;
	}

	function category() {
		return $this->get_category();
	}

	function get_category() {
		if ( !$this->_category_obj instanceof BP_Links_Category ) {
			$this->_category_obj = new BP_Links_Category( $this->category_id );
		}
		return $this->_category_obj;
	}

	function embed() {
		if ( !$this->_embed_service_obj instanceof BP_Links_Embed_Service && empty( $this->embed_data ) === false ) {
			// handle backwards compatibility with deprecated storage method (arrays)
			switch ( (string) $this->embed_service ) {
				case self::EMBED_SERVICE_PICAPP:
					$embed_data = unserialize( $this->embed_data );
					if ( !empty( $embed_data ) ) {
						$this->_embed_service_obj = new BP_Links_Embed_Service_PicApp();
						$this->_embed_service_obj->from_deprecated_data( $embed_data  );
					}
					break;
				case self::EMBED_SERVICE_FOTOGLIF:
					$embed_data = unserialize( $this->embed_data );
					if ( !empty( $embed_data ) ) {
						$this->_embed_service_obj = new BP_Links_Embed_Service_Fotoglif();
						$this->_embed_service_obj->from_deprecated_data( $embed_data );
					}
					break;
				default:
					$this->_embed_service_obj = BP_Links_Embed::LoadService( $this->embed_data );
			}
		}
		return $this->_embed_service_obj;
	}

	function embed_attach( BP_Links_Embed_Service $service ) {
		$this->_embed_service_obj = $service;
		return true;
	}

	function embed_remove( $save = false ) {
		$this->_embed_service_obj = null;
		$this->embed_service = self::EMBED_SERVICE_NONE;
		$this->embed_status = self::EMBED_STATUS_DISABLED;
		$this->embed_data = null;

		return ( $save === true ) ? $this->save() : true;
	}

	function embed_status_partial() {
		return ( self::EMBED_STATUS_PARTIAL === $this->embed_status );
	}

	function embed_status_set_partial( $save = false ) {
		if ( $this->embed() instanceof BP_Links_Embed_Service ) {
			$this->embed_status = self::EMBED_STATUS_PARTIAL;
			return ( $save === true ) ? $this->save() : true;
		} else {
			return false;
		}
	}

	function embed_status_enabled() {
		return ( self::EMBED_STATUS_ENABLED == $this->embed_status && $this->embed() instanceof BP_Links_Embed_Service );
	}
	
	function embed_status_set_enabled( $save = false ) {
		if ( $this->embed() instanceof BP_Links_Embed_Service ) {
			$this->embed_status = self::EMBED_STATUS_ENABLED;
			return ( $save === true ) ? $this->save() : true;
		} else {
			return false;
		}
	}

	// Static Functions

	/**
	 * Check if status is a valid value
	 *
	 * @static
	 * @param integer $status
	 * @return array
	 */
	function is_valid_status( $status ) {
		
		$valid =
			array(
				self::STATUS_PUBLIC,
				self::STATUS_FRIENDS,
				self::STATUS_HIDDEN
			);

		return in_array( $status, $valid );
	}

	function popularity_recalculate_all() {
		global $wpdb, $bp;

		// retire links older than 7 days from the popularity rankings, if not already retired
		$wpdb->query( $wpdb->prepare( "UPDATE {$bp->links->table_name} SET popularity = %d WHERE popularity < %d AND date_created < SUBDATE(%s, INTERVAL 7 DAY)", self::POPULARITY_RETIRED, self::POPULARITY_RETIRED, date('Y-m-d H:i:s') ) );

		// apply minimum vote total threshold to all links that aren't retired
		$wpdb->query( $wpdb->prepare( "UPDATE {$bp->links->table_name} SET popularity = %d WHERE popularity < %d AND vote_total < %d", self::POPULARITY_IGNORE, self::POPULARITY_IGNORE, self::POPULARITY_THRESH ) );

		// determine popularity sql
		$popularity_sql = sprintf( apply_filters( 'bp_links_link_popularity_recalculate_all_sql', "FLOOR( FLOOR( ( UNIX_TIMESTAMP('%s') - UNIX_TIMESTAMP(date_created) ) / 60 ) / vote_total )" ), date('Y-m-d H:i:s') );

		// update the popularity of all links that are not retired.
		// also update the popularity if they were previously ignored, but now meet the threshold.
		$wpdb->query( $wpdb->prepare( "UPDATE {$bp->links->table_name} SET popularity = {$popularity_sql} WHERE popularity <= %d AND vote_total >= %d", self::POPULARITY_IGNORE, self::POPULARITY_THRESH  ) );
	}
		
	function link_exists( $slug ) {
		global $wpdb, $bp;
		
		if ( !$slug )
			return false;
			
		return $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$bp->links->table_name} WHERE slug = %s", $slug ) );
	}

	function get_id_from_slug( $slug ) {
		return self::link_exists( $slug );
	}

	function get_slug( $link_id ) {
		global $wpdb, $bp;

		return $wpdb->get_var( $wpdb->prepare( "SELECT slug FROM {$bp->links->table_name} WHERE id = %d", $link_id ) );
	}
	
	function check_slug( $slug ) {
		global $wpdb, $bp;

		return $wpdb->get_var( $wpdb->prepare( "SELECT slug FROM {$bp->links->table_name} WHERE slug = %s", $slug ) );
	}

	function get_last_updated() {
		global $bp, $wpdb;

		return $wpdb->get_var( "SELECT date_created FROM {$bp->links->table_name} ORDER BY date_created DESC LIMIT 1" );
	}

	function delete_all_for_user( $user_id ) {
		global $bp, $wpdb;

		return $wpdb->query( $wpdb->prepare( "DELETE FROM {$bp->links->table_name} WHERE user_id = %d", $user_id ) );
	}

	//
	// Methods for a links directory
	//

	function get_by_columns_filtered( $args, $sort_columns = array() ) {
		global $wpdb, $bp;

		// init args
		$per_page = null;
		$page = 1;
		$user_id = false;
		$search_terms = false;
		$category_id = null;
		$meta_key = null;

		// extract 'em
		extract( $args );

		$join_sql = apply_filters( 'bp_links_link_by_column_join', '', $args );

		if ( $meta_key ) {
			$join_sql .= " INNER JOIN {$bp->links->table_name_linkmeta} lm ON l.id = lm.link_id";
		}

		$status_sql = self::get_status_sql( $user_id, ' AND %s' );
		
		if ( $search_terms ) {
			$search_terms = substr($search_terms, 0, 25);
			$search_terms = like_escape($search_terms);
			$filter_sql = " AND ( l.name LIKE '%%{$search_terms}%%' OR l.description LIKE '%%{$search_terms}%%' )";
		}

		if ( is_numeric($category_id) && $category_id >= 1 )
			$category_sql = $wpdb->prepare( " AND l.category_id = %d", $category_id );

		if ( $user_id ) {
			$profile_sql =
				apply_filters(
					'bp_links_link_by_column_profile',
					$wpdb->prepare( "l.user_id = %d", $user_id ),
					$args
				);
		}

		$extra_sql = apply_filters( 'bp_links_link_by_column_extra', '', $args );

		if ( $meta_key ) {
			$extra_sql .= $wpdb->prepare( " AND lm.meta_key = %s", $meta_key );
		}
		
		if ( !empty( $sort_columns ) ) {
			$order_by_sql_bits = array();
			foreach ( $sort_columns as $column => $order ) {
				$order_by_sql_bits[] = sprintf( '%s %s', $column, $order);
			}
			$order_by_sql = ' ORDER BY ' . join( ', ', $order_by_sql_bits);
		}

		if ( $per_page && $page )
			$pag_sql = $wpdb->prepare( " LIMIT %d, %d", intval( ( $page - 1 ) * $per_page), intval( $per_page ) );

		if ( $user_id ) {
			$paged_sql = "SELECT l.id AS link_id, l.slug FROM {$bp->links->table_name} l{$join_sql} WHERE {$profile_sql}{$status_sql}{$filter_sql}{$category_sql}{$extra_sql}{$order_by_sql} {$pag_sql}";
			$paged_links = $wpdb->get_results( $paged_sql );
			$total_sql = "SELECT COUNT(*) FROM {$bp->links->table_name} l{$join_sql} WHERE {$profile_sql}{$status_sql}{$category_sql}{$extra_sql}{$filter_sql}";
			$total_links = $wpdb->get_var( $total_sql );
		} else {
			$paged_sql = $wpdb->prepare( "SELECT l.id AS link_id, l.slug FROM {$bp->links->table_name} l{$join_sql} WHERE l.status = %d{$filter_sql}{$category_sql}{$extra_sql}{$order_by_sql} {$pag_sql}", self::STATUS_PUBLIC );
			$paged_links = $wpdb->get_results( $paged_sql );
			$total_sql = $wpdb->prepare( "SELECT COUNT(*) FROM {$bp->links->table_name} l{$join_sql} WHERE l.status = %d{$filter_sql}{$category_sql}{$extra_sql}", self::STATUS_PUBLIC );
			$total_links = $wpdb->get_var( $total_sql );
		}

		return array( 'links' => $paged_links, 'total' => $total_links );
	}

	function get_active( $args ) {
		$args['meta_key'] = 'last_activity';
		$sort_columns = array( 'lm.meta_value' => 'DESC' );
		return self::get_by_columns_filtered( $args, $sort_columns );
	}
	
	function get_newest( $args ) {
		$sort_columns = array( 'l.date_created' => 'DESC' );
		return self::get_by_columns_filtered( $args, $sort_columns );
	}
	
	function get_popular( $args ) {
		$sort_columns = array( 'l.popularity' => 'ASC', 'l.date_created' => 'DESC' );
		return self::get_by_columns_filtered( $args, $sort_columns );
	}

	function get_most_votes( $args ) {
		$sort_columns = array( 'l.vote_count' => 'DESC', 'l.date_created' => 'DESC' );
		return self::get_by_columns_filtered( $args, $sort_columns );
	}

	function get_high_votes( $args ) {
		$sort_columns = array( 'l.vote_total' => 'DESC', 'l.date_created' => 'DESC' );
		return self::get_by_columns_filtered( $args, $sort_columns );
	}

	function get_search( $args ) {
		$sort_columns = array( 'l.date_created' => 'DESC' );
		return self::get_by_columns_filtered( $args, $sort_columns );
	}

	function get_all( $args ) {
		$sort_columns = array( 'l.date_created' => 'DESC' );
		return self::get_by_columns_filtered( $args, $sort_columns );
	}

	function get_random( $args ) {
		$sort_columns = array( 'RAND()' => 'ASC' );
		return self::get_by_columns_filtered( $args, $sort_columns );
	}

	function get_total_link_count() {
		global $wpdb, $bp;

		if ( !is_super_admin() )
			$hidden_sql = sprintf( "WHERE status = %s", self::STATUS_PUBLIC );

		return $wpdb->get_var( "SELECT COUNT(id) FROM {$bp->links->table_name} {$hidden_sql}" );
	}

	function get_total_link_count_for_user( $user_id = false ) {
		global $bp, $wpdb;

		if ( !$user_id )
			$user_id = ( $bp->displayed_user->id ) ? $bp->displayed_user->id : $bp->loggedin_user->id;

		$sql_parts['select'] = 'SELECT COUNT(*)';
		
		$sql_parts['select_x'] =
			apply_filters( 'bp_links_link_count_for_user_select_x', '', $user_id );

		$sql_parts['from'] = "FROM {$bp->links->table_name} AS l";
			
		$sql_parts['from_x'] =
			apply_filters( 'bp_links_link_count_for_user_from_x', '', $user_id );

		$sql_parts['join'] = '';
		
		$sql_parts['join_x'] =
			apply_filters( 'bp_links_link_count_for_user_join_x', '', $user_id );

		$sql_parts['where_0'] = 'WHERE';

		$sql_parts['where_1'] =
			apply_filters(
				'bp_links_link_count_for_user_where_1',
				$wpdb->prepare( 'l.user_id = %1$d', $user_id ),
				$user_id
			);

		$sql_parts['where_2'] = self::get_status_sql( $user_id, 'AND %s' );

		$sql_parts['where_x'] =
			apply_filters( 'bp_links_link_count_for_user_where_x', '', $user_id );

		$sql = implode( ' ', $sql_parts );

		// evil check
		if ( strpos( $sql, ';' ) ) {
			throw new Exception( 'Unexpected character in SQL statement' );
		}
		
		return $wpdb->get_var( $sql );
	}

	// TODO make this a static method
	function get_activity_post_count( $show_hidden = false ) {
		global $wpdb, $bp;

		// Hide Hidden Items?
		if ( !$show_hidden )
			$hidden_sql = " AND a.hide_sitewide = 0";

		return $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(a.id) FROM {$bp->activity->table_name} a WHERE a.component = '%s' AND a.item_id = '%s' AND a.type = 'bp_link_comment'{$hidden_sql}", bp_links_id(), $this->cloud_id ) );
	}

	function get_activity_recent_ids_for_user( $user_id, $show_hidden = false ) {
		global $wpdb, $bp;

		// Hide Hidden Items?
		if ( !$show_hidden )
			$hidden_sql = " AND a.hide_sitewide = 0";

		return $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT l.cloud_id FROM {$bp->links->table_name} AS l JOIN {$bp->activity->table_name} AS a ON l.cloud_id = a.item_id WHERE l.user_id = %d AND a.component = %s{$hidden_sql} ORDER BY a.date_recorded DESC LIMIT %d", $user_id, bp_links_id(), BP_LINKS_PERSONAL_ACTIVITY_HISTORY ) );
	}
	
	function get_status_sql( $link_owner_user_id = false, $format_string = '%s' ){
		global $bp;
		
		// if user is the site admin or is logged in and viewing their own links, then no limitations
		if ( is_super_admin() || bp_is_my_profile() ) {
			// return an empty string
			return '';
		} else {

			// everyone can see the public links
			$status_opts = array(self::STATUS_PUBLIC);

			// if logged in user is a friend, show friends only links too
			if ( bp_links_is_friends_enabled() ) {
				if ( $link_owner_user_id && $link_owner_user_id != $bp->loggedin_user->id && friends_check_friendship( $link_owner_user_id, $bp->loggedin_user->id ) ) {
					$status_opts[] = self::STATUS_FRIENDS;
				}
			}

			// return the sql string
			return sprintf( $format_string, sprintf( 'status IN (%s)', join( ',', $status_opts ) ) );
		}
	}
}

/**
 * A category of a BP_Links_Link
 *
 * @package BP_Links
 * @author Marshall Sorenson
 * @see BP_Links_Link
 */
class BP_Links_Category {

	var $id;
	var $slug;
	var $name;
	var $description;
	var $priority;
	var $date_created;
	var $date_updated;
	
	function bp_links_category( $id = false ) {
		if ( $id ) {
			$this->id = $id;
			$this->populate();
		} else {
			$this->priority = 10;
		}
	}
	
	function populate() {
		global $wpdb, $bp;
		
		if ( $this->id ) {
			$sql = $wpdb->prepare( "SELECT * FROM {$bp->links->table_name_categories} WHERE id = %d", $this->id );
		} else {
			return false;
		}
			
		$category = $wpdb->get_row($sql);
		
		if ( $category ) {
			$this->id = $category->id;
			$this->slug = $category->slug;
			$this->name = $category->name;
			$this->description = $category->description;
			$this->priority = $category->priority;
			$this->date_created = strtotime($category->date_created);
			$this->date_updated = strtotime($category->date_updated);
		}
	}
	
	function save() {
		global $wpdb, $bp;

		// create slug on initial save only
		if ( !$this->id ) {
			$slug = self::make_slug( $this->name );
			
			if ( $this->check_slug( $slug ) ) {
				return false;
			} else {
				$this->slug = $slug;
			}
		}

		$this->slug = apply_filters( 'bp_links_category_slug_before_save', $this->slug, $this->id );
		$this->name = apply_filters( 'bp_links_category_name_before_save', $this->name, $this->id );
		$this->description = apply_filters( 'bp_links_category_description_before_save', $this->description, $this->id );
		$this->priority = apply_filters( 'bp_links_category_priority_before_save', $this->priority, $this->id );

		do_action( 'bp_links_category_before_save', $this );
		
		if ( $this->id ) {
			// slug is NOT overwritten
			$sql = $wpdb->prepare( "UPDATE {$bp->links->table_name_categories} SET name = %s, description = %s, priority = %d WHERE id = %d", $this->name, $this->description, $this->priority, $this->id );
		} else {
			// slug is created
			$sql = $wpdb->prepare( "INSERT INTO {$bp->links->table_name_categories} ( slug, name, description, priority, date_created ) VALUES ( %s, %s, %s, %d, %s )", $this->slug, $this->name, $this->description, $this->priority, date('Y-m-d H:i:s') );
		}

		if ( false === $wpdb->query($sql) )
			return false;
		
		$this->id = $wpdb->insert_id;
		
		do_action( 'bp_links_category_after_save', $this );

		return true;
	}

	function delete() {
		global $wpdb, $bp;

		$delete_result = $wpdb->query( $wpdb->prepare( "DELETE FROM {$bp->links->table_name_categories} WHERE id = %d", $this->id ) );

		return $delete_result;
	}
		
	// Static Functions

	function check_category( $category_id ) {
		global $wpdb, $bp;

		if ( !$category_id ) {
			return false;
		}

		return $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$bp->links->table_name_categories} WHERE id = %d", $category_id ) );
	}

	function make_slug( $string ) {
		return sanitize_title( $string );
	}

	function check_slug( $slug ) {
		global $wpdb, $bp;

		return $wpdb->get_var( $wpdb->prepare( "SELECT slug FROM {$bp->links->table_name_categories} WHERE slug = %s", $slug ) );
	}

	function check_slug_raw( $string ) {
		return self::check_slug( self::make_slug( $string ) );
	}

	function get_slug_from_id( $category_id ) {
		global $wpdb, $bp;

		return $wpdb->get_var( $wpdb->prepare( "SELECT slug FROM {$bp->links->table_name_categories} WHERE id = %d", $category_id ) );
	}

	function get_id_from_slug( $slug ) {
		global $wpdb, $bp;

		if ( !$slug )
			return false;

		return $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$bp->links->table_name_categories} WHERE slug = %s", $slug ) );
	}

	function get_link_count( $category_id ) {
		global $wpdb, $bp;

		if ( !$category_id )
			return false;

		return $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$bp->links->table_name} WHERE category_id = %d", $category_id ) );
	}

	function get_all() {
		global $wpdb, $bp;

		return
			$wpdb->get_results(
				"SELECT id as category_id FROM {$bp->links->table_name_categories} ORDER BY priority"
			);
	}

	function get_all_filtered( $filter = null, $limit = null, $page = null ) {
		global $wpdb, $bp;

		if ( $filter ) {
			$filter = substr($filter, 0, 25);
			$filter = like_escape($filter);
			$filter_sql = " WHERE ( name LIKE '%%{$filter}%%' )";
		}

		if ( $limit && $page )
			$pag_sql = $wpdb->prepare( " LIMIT %d, %d", intval( ( $page - 1 ) * $limit), intval( $limit ) );

		$paged_categories = $wpdb->get_results( "SELECT id as category_id, slug FROM {$bp->links->table_name_categories}{$filter_sql} ORDER BY priority {$pag_sql}" );
		$total_categories = $wpdb->get_var( "SELECT COUNT(*) FROM {$bp->links->table_name_categories}{$filter_sql}" );

		return array( 'categories' => $paged_categories, 'total' => $total_categories );
	}
}

/**
 * A vote for a BP_Links_Link
 *
 * @package BP_Links
 * @author Marshall Sorenson
 * @see BP_Links_Link
 */
class BP_Links_Vote {

	/**
	 * @see BP_Links_Link::id
	 * @var integer
	 */
	var $link_id;

	/**
	 * @see WP_Users
	 * @var integer
	 */
	var $user_id;

	/**
	 * The vote
	 * @var integer
	 */
	var $vote;

	/**
	 * Date and time that record was created
	 * @var integer
	 */
	var $date_created;

	/**
	 * Date and time that record was last updated
	 * @var integer
	 */
	var $date_updated;

	/**
	 * This will be set to true once they actually vote
	 *
	 * @var boolean
	 */
	var $_voted = false;

	/**
	 * This will be set to true if populate() is successful
	 *
	 * @see populate()
	 * @var boolean
	 */
	var $_edit_mode = false;

	/**
	 * Constructor
	 *
	 * @param integer $link_id
	 * @param integer $user_id
	 * @return boolean
	 */
	function bp_links_vote( $link_id = false, $user_id = false ) {
		if ( $link_id && $user_id ) {
			return $this->populate( $link_id, $user_id );
		}
		return true;
	}

	/**
	 * Populate object from database
	 *
	 * @global wpdb $wpdb
	 * @global stdClass $bp
	 * @param integer $link_id
	 * @param integer $user_id
	 * @return boolean
	 */
	function populate( $link_id, $user_id ) {
		global $wpdb, $bp;

		if ( $this->validate_primary_key( $link_id, $user_id ) === true ) {

			$this->link_id = (int)$link_id;
			$this->user_id = (int)$user_id;

			$sql = $wpdb->prepare( "SELECT * FROM {$bp->links->table_name_votes} WHERE link_id = %d AND user_id = %d", $this->link_id, $this->user_id );
			
		} else {
			return false;
		}

		$vote = $wpdb->get_row($sql);

		if ( $vote !== false && is_numeric( $vote->vote ) ) {
			$this->link_id = $vote->link_id;
			$this->user_id = $vote->user_id;
			$this->vote = $vote->vote;
			$this->date_created = strtotime($vote->date_created);
			$this->date_updated = strtotime($vote->date_updated);

			$this->_edit_mode = true;
		}

		return true;
	}

	/**
	 * Save
	 *
	 * @global wpdb $wpdb
	 * @global stdClass $bp
	 * @return boolean
	 */
	function save() {
		global $wpdb, $bp;

		// if they didn't vote, then saving is pointless
		if ( false === $this->_voted ) {
			return;
		}

		$this->vote = apply_filters( 'bp_links_vote_vote_before_save', $this->vote, $this->link_id, $this->user_id );
		
		do_action( 'bp_links_vote_before_save', $this );

		// vote must be 1 or -1
		if ( !in_array($this->vote, array( 1, -1 ) ) ) {
			return false;
		}

		if ( $this->check_foreign_keys() === true ) {

			if ( $this->_edit_mode ) {
				$sql = $wpdb->prepare( "UPDATE {$bp->links->table_name_votes} SET vote = %d WHERE link_id = %d AND user_id = %d", $this->vote, $this->link_id, $this->user_id );
			} else {
				$sql = $wpdb->prepare( "INSERT INTO {$bp->links->table_name_votes} ( link_id, user_id, vote, date_created ) VALUES ( %d, %d, %d, %s )", $this->link_id, $this->user_id, $this->vote, date('Y-m-d H:i:s') );
			}

			if ( false === $wpdb->query($sql) ) {
				return false;
			}
		
		} else {
			return false;
		}

		do_action( 'bp_links_vote_after_save', $this );

		return true;
	}

	/**
	 * Delete
	 *
	 * @static
	 * @global wpdb $wpdb
	 * @global stdClass $bp
	 * @param integer $link_id
	 * @param integer $user_id
	 * @return boolean
	 */
	function delete() {
		global $wpdb, $bp;

		if ( $this->_edit_mode && $this->validate_primary_key( $this->link_id, $this->user_id ) === true ) {
			$delete_result = $wpdb->query( $wpdb->prepare( "DELETE FROM {$bp->links->table_name_votes} WHERE link_id = %d AND user_id = %d", $this->link_id, $this->user_id ) );
			return $delete_result;
		} else {
			return false;
		}
	}

	function up() {
		$this->vote = 1;
		$this->_voted = true;
		return true;
	}

	function down() {
		$this->vote = -1;
		$this->_voted = true;
		return true;
	}

	function get() {
		return $this->vote;
	}
	
	/**
	 * Sanity check a numeric key
	 *
	 * @param integer $int
	 * @return boolean
	 */
	function validate_key ( $int ) {
		return ( is_numeric( $int ) && $int >= 1 );
	}

	/**
	 * Sanity check the primary key
	 * 
	 * @param integer $link_id
	 * @param integer $user_id
	 * @return boolean
	 */
	function validate_primary_key ( $link_id, $user_id ) {
		return ( self::validate_key( $link_id ) === true && self::validate_key( $user_id ) === true );
	}

	/**
	 * Check that all foreign keys in primary key exist
	 *
	 * @return boolean
	 */
	function check_foreign_keys () {
		return ( $this->check_user_foreign_key() && $this->check_link_foreign_key() );
	}

	/**
	 * Check that user foreign key exists
	 *
	 * @return boolean
	 */
	function check_user_foreign_key () {
		global $wpdb;

		return ( $this->validate_key( $this->user_id ) && $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->users} WHERE ID = %d", $this->user_id ) ) ) ? true : false;
	}

	/**
	 * Check that link foreign key exists
	 *
	 * @return boolean
	 */
	function check_link_foreign_key () {
		global $wpdb, $bp;

		return ( $this->validate_key( $this->link_id ) && $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$bp->links->table_name} WHERE id = %d", $this->link_id ) ) ) ? true : false;
	}

	//
	// Static Functions
	//

	/**
	 * Check if vote exists
	 *
	 * @static
	 * @global wpdb $wpdb
	 * @global stdClass $bp
	 * @param integer $link_id
	 * @param integer $user_id
	 * @return boolean
	 */
	function check_vote( $link_id, $user_id ) {
		global $wpdb, $bp;

		if ( self::validate_primary_key( $link_id, $user_id ) === false ) {
			return false;
		}

		return (boolean) $wpdb->get_var( $wpdb->prepare( "SELECT 1 AS found FROM {$bp->links->table_name_votes} WHERE link_id = %d AND user_id = %d", $link_id, $user_id ) );
	}

	/**
	 * Count number of member votes for a link
	 *
	 * @static
	 * @global wpdb $wpdb
	 * @global stdClass $bp
	 * @param integer $link_id
	 * @return integer
	 */
	function link_vote_count( $link_id ) {
		global $wpdb, $bp;

		if ( self::validate_key( $link_id ) === false ) {
			return false;
		}

		return $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$bp->links->table_name_votes} WHERE link_id = %d", $link_id ) );
	}

	/**
	 * Sum of member votes for a link
	 *
	 * @static
	 * @global wpdb $wpdb
	 * @global stdClass $bp
	 * @param integer $link_id
	 * @return integer
	 */
	function link_vote_total( $link_id ) {
		global $wpdb, $bp;

		if ( self::validate_key( $link_id ) === false ) {
			return false;
		}

		return $wpdb->get_var( $wpdb->prepare( "SELECT SUM(vote) FROM {$bp->links->table_name_votes} WHERE link_id = %d", $link_id ) );
	}
}

?>
