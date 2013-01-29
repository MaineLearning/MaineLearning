<?php

/*****************************************************************************
 * User Links Template Class/Tags
 **/

class BP_Links_Template {
	var $current_link = -1;
	var $link_count;
	var $links;
	var $link;
	
	var $in_the_loop;
	
	var $pag_page;
	var $pag_num;
	var $pag_links;
	var $total_link_count;
	
	var $sort_by;
	var $order;

	var $avatar_size;
	
	function bp_links_template( $args = array() )
	{
		// init args used in this scope
		$type = null;
		$page = null;
		$per_page = null;
		$max = null;
		$slug = null;
		$avatar_size = null;

		// handle query string overrides
		if ( isset( $_REQUEST['lpage'] ) ) {
			$args['page'] = intval( $_REQUEST['lpage'] );
		}
		if ( isset( $_REQUEST['num'] ) ) {
			$args['per_page'] = intval( $_REQUEST['num'] );
		}

		// extract 'em
		extract( $args );

		// set avatar size
		$this->avatar_display_size( $avatar_size );

		// set paging props
		$this->pag_page = $page;
		$this->pag_num = $per_page;

		switch ( $type ) {

			default:
			case 'active':
				$this->links = bp_links_get_active( $args );
				break;

			case 'newest':
				$this->links = bp_links_get_newest( $args );
				break;

			case 'search':
				$this->links = bp_links_get_search( $args );
				break;

			case 'popular':
				$this->links = bp_links_get_popular( $args );
				break;

			case 'most-votes':
				$this->links = bp_links_get_most_votes( $args );
				break;

			case 'high-votes':
				$this->links = bp_links_get_high_votes( $args );
				break;

			case 'all':
				$this->links = bp_links_get_all( $args );
				break;
			
			case 'random':
				$this->links = bp_links_get_random();
				break;

			case 'single-link':
				$link = new stdClass;
				$link->link_id = BP_Links_Link::get_id_from_slug( $slug );
				$this->links = array( $link );
				break;
		}
		
		if ( 'single-link' == $type ) {
			$this->total_link_count = 1;
			$this->link_count = 1;
		} else {
			if ( !$max || $max >= (int)$this->links['total'] )
				$this->total_link_count = (int)$this->links['total'];
			else
				$this->total_link_count = (int)$max;

			$this->links = $this->links['links'];

			if ( $max ) {
				if ( $max >= count($this->links) )
					$this->link_count = count($this->links);
				else
					$this->link_count = (int)$max;
			} else {
				$this->link_count = count($this->links);
			}
		}

		$this->pag_links = paginate_links( array(
			'base' => add_query_arg( array( 'lpage' => '%#%', 'num' => $this->pag_num, 's' => $_REQUEST['s'], 'sortby' => $this->sort_by, 'order' => $this->order ) ),
			'format' => '',
			'total' => ceil($this->total_link_count / $this->pag_num),
			'current' => $this->pag_page,
			'prev_text' => '&laquo;',
			'next_text' => '&raquo;',
			'mid_size' => 1
		));
	}

	function has_links() {
		if ( $this->link_count )
			return true;
		
		return false;
	}
	
	function next_link() {
		$this->current_link++;
		$this->link = $this->links[$this->current_link];
			
		return $this->link;
	}
	
	function rewind_links() {
		$this->current_link = -1;
		if ( $this->link_count > 0 ) {
			$this->link = $this->links[0];
		}
	}
	
	function links() {
		if ( $this->current_link + 1 < $this->link_count ) {
			return true;
		} elseif ( $this->current_link + 1 == $this->link_count ) {
			do_action('loop_end');
			// Do some cleaning up after the loop
			$this->rewind_links();
		}

		$this->in_the_loop = false;
		return false;
	}
	
	function the_link() {
		global $link;

		$this->in_the_loop = true;
		$this->link = $this->next_link();

		if ( !$link = wp_cache_get( 'bp_links_link_nouserdata_' . $this->link->link_id, 'bp' ) ) {
			$link = new BP_Links_Link( $this->link->link_id, false, false );
			wp_cache_set( 'bp_links_link_nouserdata_' . $this->link->link_id, $link, 'bp' );
		}

		$this->link = $link;

		if ( 0 == $this->current_link ) // loop has just started
			do_action('loop_start');
	}

	function avatar_display_size() {

		$args = func_get_args();

		if ( count( $args ) == 1 ) {
			if ( in_array( $args[0], array(50,60,70,80,90,100,110,120,130), true ) ) {
				$this->avatar_size = $args[0];
			} else {
				$this->avatar_size = BP_LINKS_LIST_AVATAR_SIZE;
			}
		} else {
			return $this->avatar_size;
		}
	}
}


function bp_has_links( $args = array() ) {
	global $links_template, $bp;

	// default args to use IF args is not empty
	$defaults = array(
		'type' => 'active',
		'page' => 1,
		'per_page' => 10,
		'max' => false,
		'avatar_size' => false,
		'user_id' => false,
		'slug' => false,
		'search_terms' => false,
		'category_id' => false
	);

	// args to pass to template class
	$template_args = wp_parse_args( $args, $defaults );

	if ( empty( $args ) ) {
		// The following code will auto set parameters based on the page being viewed.
		// for example on example.com/members/marshall/links/my-links/popular/
		// $type = 'popular'
		//
		if ( 'my-links' == $bp->current_action ) {
			$order = $bp->action_variables[0];
			if ( 'active' == $order )
				$type = 'active';
			elseif ( 'all' == $order )
				$type = 'all';
			elseif ( 'newest' == $order )
				$type = 'newest';
			else if ( 'popular' == $order )
				$type = 'popular';
			else if ( 'most-votes' == $order )
				$type = 'most-votes';
			else if ( 'high-votes' == $order )
				$type = 'high-votes';
		} else if ( $bp->links->current_link->slug ) {
			$type = 'single-link';
			$template_args['slug'] = $bp->links->current_link->slug;
		}

		$template_args['order'] = $order;
		$template_args['type'] = $type;
		
	}
	
	switch ( true ) {
		case ( isset( $_REQUEST['link-filter-box'] ) ):
			$template_args['search_terms'] = $_REQUEST['link-filter-box'];
			break;
		case ( isset( $_REQUEST['s'] ) ):
			$template_args['search_terms'] = $_REQUEST['s'];
			break;
	}

	$template_args = apply_filters( 'bp_has_links_template_args', $template_args );

	$links_template = new BP_Links_Template( $template_args );

	return apply_filters( 'bp_has_links', $links_template->has_links(), $links_template );
}


function bp_links() {
	global $links_template;
	return $links_template->links();
}

function bp_the_link() {
	global $links_template;
	return $links_template->the_link();
}

function bp_link_is_visible( $link = false ) {
	global $bp, $links_template;

	if ( !$link )
		$link =& $links_template->link;

	return bp_links_is_link_visibile( $link );
}

function bp_link_is_admin_page() {
	return bp_links_is_link_admin_page();
}

function bp_link_id() {
	echo bp_get_link_id();
}
	function bp_get_link_id( $link = false ) {
		global $links_template;

		if ( !$link )
			$link =& $links_template->link;

		return apply_filters( 'bp_get_link_id', $link->id );
	}

function bp_link_category_id() {
	echo bp_get_link_category_id();
}
	function bp_get_link_category_id( $link = false ) {
		global $bp, $links_template;

		if ( !$link )
			$link =& $links_template->link;

		$category_id = $link->category_id;

		return apply_filters( 'bp_get_link_category_id', $category_id );
	}

function bp_link_category_slug() {
	echo bp_get_link_category_slug();
}
	function bp_get_link_category_slug( $link = false ) {
		global $links_template;

		if ( !$link )
			$link =& $links_template->link;

		$category = $link->get_category();

		return apply_filters( 'bp_get_link_category_slug', $category->slug );
	}

function bp_link_category_name() {
	echo bp_get_link_category_name();
}
	function bp_get_link_category_name( $link = false ) {
		global $links_template;

		if ( !$link )
			$link =& $links_template->link;

		$category = $link->get_category();

		return apply_filters( 'bp_get_link_category_name', $category->name );
	}

function bp_link_url() {
	echo bp_get_link_url();
}
	function bp_get_link_url( $link = false ) {
		global $bp, $links_template;

		if ( !$link )
			$link =& $links_template->link;

		return apply_filters( 'bp_get_link_url', $link->url );
	}

function bp_link_url_domain() {
	echo bp_get_link_url_domain();
}
	function bp_get_link_url_domain( $link = false ) {
		global $links_template;

		if ( !$link )
			$link =& $links_template->link;

		$url_parts = parse_url( $link->url );

		if( isset( $url_parts['host'] ) ) {
			$domain = preg_replace( '/^www\./', '', $url_parts['host'] );
		} else {
			$domain = '';
		}

		return apply_filters( 'bp_get_link_url_domain', $domain );
	}

function bp_link_name() {
	echo bp_get_link_name();
}
	function bp_get_link_name( $link = false ) {
		global $bp, $links_template;

		if ( !$link )
			$link =& $links_template->link;

		$name = $link->name;

		return apply_filters( 'bp_get_link_name', $name );
	}
	
function bp_link_type() {
	echo bp_get_link_type();
}
	function bp_get_link_type( $link = false ) {
		global $links_template;

		if ( !$link )
			$link =& $links_template->link;

		if ( BP_Links_Link::STATUS_PUBLIC == $link->status ) {
			$type = __( 'Public Link', 'buddypress-links' );
		} else if ( BP_Links_Link::STATUS_FRIENDS == $link->status ) {
			$type = __( 'Friends Only Link', 'buddypress-links' );
		} else if ( BP_Links_Link::STATUS_HIDDEN == $link->status ) {
			$type = __( 'Hidden Link', 'buddypress-links' );
		} else {
			$type = ucwords( $link->status ) . ' ' . __( 'Link', 'buddypress-links' );
		}

		return apply_filters( 'bp_get_link_type', $type );
	}

function bp_link_has_avatar() {
	echo ( bp_get_link_has_avatar() ) ? 1 : 0;
}
	function bp_get_link_has_avatar() {
		return bp_links_check_avatar( bp_get_link_id() );
	}

function bp_link_avatar( $args = '', $link = null ) {
	echo bp_get_link_avatar( $args, $link );
}
	function bp_get_link_avatar( $args = '', $link = null ) {
		global $links_template;

		if ( !$link ) {
			$link = $links_template->link;
		}

		$defaults = array(
			'item_id' => $link->id
		);

		$new_args = wp_parse_args( $args, $defaults );

		return apply_filters( 'bp_get_link_avatar', bp_links_fetch_avatar( $new_args, $link ) );
	}

function bp_link_avatar_thumb() {
	echo bp_get_link_avatar_thumb();
}
	function bp_get_link_avatar_thumb( $link = false ) {
		return bp_get_link_avatar( 'type=thumb', $link );
	}

function bp_link_avatar_mini() {
	echo bp_get_link_avatar_mini();
}
	function bp_get_link_avatar_mini( $link = false ) {
		return bp_get_link_avatar( 'type=thumb&width=30&height=30' );
	}

function bp_link_avatar_display_size() {
	echo bp_get_link_avatar_display_size();
}
	function bp_get_link_avatar_display_size() {
		global $links_template;

		return apply_filters( 'bp_get_link_avatar_display_size', $links_template->avatar_display_size() );
	}

function bp_link_user_avatar() {
	echo bp_get_link_user_avatar();
}
	function bp_get_link_user_avatar( $args = '', $link = false ) {
		global $bp, $links_template;

		if ( !$link )
			$link =& $links_template->link;

		$defaults = array(
			'type' => 'full',
			'width' => false,
			'height' => false,
			'class' => 'owner-avatar',
			'id' => false,
			'alt' => false
		);

		$r = wp_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );

		return apply_filters( 'bp_get_link_user_avatar', bp_core_fetch_avatar( array( 'item_id' => $link->user_id, 'type' => $type, 'alt' => $alt, 'class' => $class, 'width' => $width, 'height' => $height ) ) );
	}

function bp_link_user_avatar_thumb() {
	echo bp_get_link_user_avatar_thumb();
}
	function bp_get_link_user_avatar_thumb( $link = false ) {
		return bp_get_link_user_avatar( 'type=thumb', $link );
	}

function bp_link_user_avatar_mini() {
	echo bp_get_link_user_avatar_mini();
}
	function bp_get_link_user_avatar_mini( $link = false ) {
		return bp_get_link_user_avatar( 'type=thumb&width=20&height=20', $link );
	}
	
function bp_link_last_active() {
		echo bp_get_link_last_active();
}
	function bp_get_link_last_active( $link = false ) {
		global $links_template;

		if ( !$link )
			$link =& $links_template->link;

		$last_active = bp_links_get_linkmeta( $link->id, 'last_activity' );

		if ( empty( $last_active ) ) {
			return __( 'not yet active', 'buddypress-links' );
		} else {
			return apply_filters( 'bp_get_link_last_active', bp_core_time_since( $last_active ) );
		}
	}

function bp_link_permalink() {
	echo bp_get_link_permalink();
}
	function bp_get_link_permalink( $link = false ) {
		global $links_template, $bp;

		if ( !$link )
			$link =& $links_template->link;

		return apply_filters( 'bp_get_link_permalink', $bp->root_domain . '/' . bp_links_root_slug() . '/' . $link->slug );
	}

function bp_link_userlink() {
	echo bp_get_link_userlink();
}
	function bp_get_link_userlink( $link = false ) {
		global $links_template, $bp;

		if ( !$link )
			$link =& $links_template->link;

		return apply_filters( 'bp_get_link_userlink', bp_core_get_userlink( $link->user_id ) );
	}

function bp_link_slug() {
	echo bp_get_link_slug();
}
	function bp_get_link_slug( $link = false ) {
		global $links_template;

		if ( !$link )
			$link =& $links_template->link;

		return apply_filters( 'bp_get_link_slug', $link->slug );
	}

function bp_link_has_description() {
	echo bp_get_link_has_description();
}
	function bp_get_link_has_description( $link = false ) {
		global $links_template;

		if ( !$link )
			$link =& $links_template->link;

		return apply_filters( 'bp_get_link_has_description', ( strlen( $link->description ) >= 1 ), $link->description );
	}

function bp_link_description() {
	echo bp_get_link_description();
}
	function bp_get_link_description( $link = false ) {
		global $links_template;

		if ( !$link )
			$link =& $links_template->link;

		return apply_filters( 'bp_get_link_description', stripslashes($link->description) );
	}

function bp_link_description_excerpt( $length = 55 ) {
	echo bp_get_link_description_excerpt( $length );
}
	function bp_get_link_description_excerpt( $link = false, $length = 55 ) {
		global $links_template;

		if ( !$link )
			$link =& $links_template->link;

		return apply_filters( 'bp_get_link_description_excerpt', bp_create_excerpt( $link->description, $length ) );
	}

function bp_link_vote_count() {
	echo bp_get_link_vote_count();
}
	function bp_get_link_vote_count( $link = false ) {
		global $links_template;

		if ( !$link )
			$link =& $links_template->link;

		return apply_filters( 'bp_get_link_vote_count', $link->vote_count );
	}

function bp_link_vote_count_text() {
	echo bp_get_link_vote_count_text();
}
	function bp_get_link_vote_count_text( $link = false ) {

		$vote_count = bp_get_link_vote_count( $link );

		if ( 1 == $vote_count )
			return apply_filters( 'bp_get_link_vote_count_text', sprintf( __( '%s vote', 'buddypress-links' ), $vote_count ) );
		else
			return apply_filters( 'bp_get_link_vote_count_text', sprintf( __( '%s votes', 'buddypress-links' ), $vote_count ) );
	}

function bp_link_vote_total() {
	echo bp_get_link_vote_total();
}
	function bp_get_link_vote_total( $link = false ) {
		global $links_template;

		if ( !$link )
			$link =& $links_template->link;

		return apply_filters( 'bp_get_link_vote_total', $link->vote_total );
	}

function bp_link_popularity() {
	echo bp_get_link_popularity();
}
	function bp_get_link_popularity( $link = false ) {
		global $links_template;

		if ( !$link )
			$link =& $links_template->link;

		return apply_filters( 'bp_get_link_popularity', $link->popularity );
	}

function bp_link_date_created() {
	echo bp_get_link_date_created();
}
	function bp_get_link_date_created( $link = false ) {
		global $links_template;

		if ( !$link )
			$link =& $links_template->link;

		return apply_filters( 'bp_get_link_date_created', date( get_option( 'date_format' ), $link->date_created ) );
	}

function bp_link_time_since_created() {
	echo bp_get_link_time_since_created();
}
	function bp_get_link_time_since_created( $link = false ) {
		global $links_template;

		if ( !$link )
			$link =& $links_template->link;

		return apply_filters( 'bp_get_link_time_since_created', bp_core_time_since( $link->date_created ) );
	}

function bp_link_play_button() {
	echo bp_get_link_play_button();
}
	function bp_get_link_play_button( $link = false ) {

		global $links_template;

		if ( !$link )
			$link =& $links_template->link;

		$button_html = null;

		if ( $link->embed_status_enabled() ) {

			$class = null;

			if ( $link->embed()->avatar_play_video() === true )
				$class = 'link-play link-play-video';
			elseif ( $link->embed()->avatar_play_photo() === true )
				$class = 'link-play link-play-photo';

			if ( $class )
				$button_html = sprintf( '<a href="%s" id="link-play-%d" class="%s"></a>', bp_get_link_permalink( $link ), bp_get_link_id( $link ), $class );
		}

		return apply_filters( 'bp_get_link_play_button', $button_html );
	}

function bp_link_is_admin() {
	global $bp;
	
	return $bp->is_item_admin;
}

// this is for future use
function bp_link_is_mod() {
	global $bp;
	
	return $bp->is_item_mod;
}

function bp_link_show_no_links_message() {
	global $bp;
	
	if ( !bp_links_total_links_for_user( $bp->displayed_user->id ) )
		return true;
		
	return false;
}

function bp_links_pagination_links() {
	echo bp_get_links_pagination_links();
}
	function bp_get_links_pagination_links() {
		global $links_template;

		return apply_filters( 'bp_get_links_pagination_links', $links_template->pag_links );
	}

function bp_links_pagination_count() {
	echo bp_get_links_pagination_count();
}
	function bp_get_links_pagination_count() {
		global $bp, $links_template;

		$from_num = intval( ( $links_template->pag_page - 1 ) * $links_template->pag_num ) + 1;
		$to_num = ( $from_num + ( $links_template->pag_num - 1 ) > $links_template->total_link_count ) ? $links_template->total_link_count : $from_num + ( $links_template->pag_num - 1) ;

		return sprintf( __( 'Viewing link %1$d to %2$d (of %3$d links)', 'buddypress-links' ), $from_num, $to_num, $links_template->total_link_count ) . '&nbsp<span class="ajax-loader"></span>';
	}

function bp_links_total_link_count() {
	echo bp_get_links_total_link_count();
}
	function bp_get_links_total_link_count() {
		return apply_filters( 'bp_get_links_total_link_count', bp_links_total_links() );
	}

function bp_link_total_link_count_for_user() {
	echo bp_get_link_total_link_count_for_user();
}
	function bp_get_link_total_link_count_for_user( $user_id = false ) {
		return apply_filters( 'bp_get_link_total_link_count_for_user', bp_links_total_links_for_user( $user_id ) );
	}

function bp_link_activity_post_count() {
	echo bp_get_link_activity_post_count();
}
	function bp_get_link_activity_post_count( $link = false ) {
		global $links_template;

		if ( !$link )
			$link = $links_template->link;

		return apply_filters( 'bp_get_link_activity_post_count', $link->get_activity_post_count() );
	}

function bp_link_admin_tabs() {
	global $bp, $links_template;

	$link = ( $links_template->link ) ? $links_template->link : $bp->links->current_link;
	
	$current_tab = $bp->action_variables[0];
?>
	<?php if ( $bp->is_item_admin ) { ?>
		<li<?php if ( 'edit-details' == $current_tab || empty( $current_tab ) ) : ?> class="current"<?php endif; ?>><a href="<?php echo $bp->root_domain . '/' . bp_links_root_slug() ?>/<?php echo $link->slug ?>/admin/edit-details"><?php _e( 'Edit Details', 'buddypress-links' ) ?></a></li>
	<?php } ?>
	
	<?php
		if ( !$bp->is_item_admin )
			return false;
	?>
	<li<?php if ( 'link-avatar' == $current_tab ) : ?> class="current"<?php endif; ?>><a href="<?php echo $bp->root_domain . '/' . bp_links_root_slug() ?>/<?php echo $link->slug ?>/admin/link-avatar"><?php _e( 'Link Avatar', 'buddypress-links' ) ?></a></li>

	<?php do_action( 'bp_link_admin_tabs', $current_tab, $link->slug ) ?>
	
	<li<?php if ( 'delete-link' == $current_tab ) : ?> class="current"<?php endif; ?>><a href="<?php echo $bp->root_domain . '/' . bp_links_root_slug() ?>/<?php echo $link->slug ?>/admin/delete-link"><?php _e( 'Delete Link', 'buddypress-links' ) ?></a></li>
<?php
}

function bp_link_status_message( $link = false ) {
	global $links_template;
	
	if ( !$link )
		$link =& $links_template->link;
	
	if ( BP_Links_Link::STATUS_HIDDEN == $link->status ) {
		_e( 'This is a hidden link. Only the user who owns it can view it.', 'buddypress-links' );
	} elseif ( BP_Links_Link::STATUS_FRIENDS == $link->status ) {
		_e( 'This is a friends only link. Only the owner\'s friends can view it.', 'buddypress-links' );
	} else {
		_e( 'You do not have permission to access this link.', 'buddypress-links' );
	}
}

/***************************************************************************
 * Link Creation Process Template Tags
 **/

function bp_link_details_form_action() {
	echo bp_get_link_details_form_action();
}
	function bp_get_link_details_form_action() {
		global $bp;

		if ( bp_links_current_link_exists() ) {
			$form_action = bp_get_link_admin_form_action();
		} else {
			switch ( bp_current_component() ) {
				default:
				case bp_links_id():
				case bp_links_slug():
					$form_action = $bp->loggedin_user->domain . bp_links_slug() . '/create';
					break;
			}
		}

		return apply_filters( 'bp_get_link_details_form_action', $form_action );
	}

function bp_link_details_form_link_url_readonly() {
	echo bp_get_link_details_form_link_url_readonly();
}
	function bp_get_link_details_form_link_url_readonly() {
		global $bp;

		if ( isset( $_POST['link-url-readonly'] ) ) {
			return ( empty( $_POST['link-url-readonly'] ) ) ? 0 : 1;
		} elseif ( bp_links_current_link_embed_enabled() )  {
			return ( bp_links_current_link_embed_service() instanceof BP_Links_Embed_From_Url ) ? 1 : 0;
		} else {
			return 0;
		}
	}

function bp_link_details_form_name_desc_fields_display() {
	echo bp_get_link_details_form_name_desc_fields_display();
}
	function bp_get_link_details_form_name_desc_fields_display() {
		global $bp;

		if ( isset( $_POST['link-url-embed-data'] ) ) {
			return ( !empty( $_POST['link-url-embed-data'] ) && empty( $_POST['link-url-embed-edit-text'] ) ) ? 0 : 1;
		} elseif ( bp_links_current_link_embed_enabled() )  {
			return ( bp_links_current_link_embed_service() instanceof BP_Links_Embed_From_Url ) ? 0 : 1;
		} else {
			return 0;
		}
	}

function bp_link_details_form_avatar_fields_display() {
	echo bp_get_link_details_form_avatar_fields_display();
}
	function bp_get_link_details_form_avatar_fields_display() {
		return ( empty( $_POST['link-avatar-fields-display'] ) ) ? 0 : 1;
	}

function bp_link_details_form_avatar_option() {
	echo bp_get_link_details_form_avatar_option();
}
	function bp_get_link_details_form_avatar_option() {
		return ( empty( $_POST['link-avatar-option'] ) ) ? 0 : 1;
	}

function bp_link_details_form_settings_fields_display() {
	echo bp_get_link_details_form_settings_fields_display();
}
	function bp_get_link_details_form_settings_fields_display() {
		return ( empty( $_POST['link-settings-fields-display'] ) ) ? 0 : 1;
	}

function bp_link_details_form_category_id() {
	echo bp_get_link_details_form_category_id();
}
	function bp_get_link_details_form_category_id() {
		global $bp;

		if ( !empty( $_POST['link-category'] ) ) {
			$category_id = $_POST['link-category'];
		} else {
			$category_id = $bp->links->current_link->category_id;
		}

		return apply_filters( 'bp_get_link_details_form_category_id', $category_id );
	}

function bp_link_details_form_url() {
	echo bp_get_link_details_form_url();
}
	function bp_get_link_details_form_url() {
		global $bp;

		if ( !empty( $_POST['link-url'] ) ) {
			$link_url = $_POST['link-url'];
		} else {
			$link_url = $bp->links->current_link->url;
		}

		return apply_filters( 'bp_get_link_details_form_url', $link_url );
	}

function bp_get_link_details_form_embed_service() {
	global $bp;

	if ( !empty( $_POST['link-url-embed-data'] ) ) {
		try {
			// load service
			$service = BP_Links_Embed::LoadService( trim( $_POST['link-url-embed-data'] ) );
			// valid service?
			if ( $service instanceof BP_Links_Embed_Service ) {
				return $service;
			}
		} catch ( BP_Links_Embed_Exception $e ) {
			return false;
		}
	} elseif ( bp_links_current_link_embed_enabled() ) {
		return bp_links_current_link_embed_service();
	}

	return false;
}

function bp_link_details_form_url_embed_data() {
	echo bp_get_link_details_form_url_embed_data();
}
	function bp_get_link_details_form_url_embed_data() {

		$embed_data = null;
		$embed_service = bp_get_link_details_form_embed_service();

		if ( $embed_service instanceof BP_Links_Embed_From_Url ) {
			$embed_data = $embed_service->export_data();
		}

		return apply_filters( 'bp_get_link_details_form_url_embed_data', $embed_data );
	}

function bp_link_details_form_name() {
	echo bp_get_link_details_form_name();
}
	function bp_get_link_details_form_name() {
		global $bp;

		if ( !empty( $_POST['link-name'] ) ) {
			$link_name = $_POST['link-name'];
		} else {
			$link_name = $bp->links->current_link->name;
		}

		return apply_filters( 'bp_get_link_details_form_name', $link_name );
	}

function bp_link_details_form_description() {
	echo bp_get_link_details_form_description();
}
	function bp_get_link_details_form_description() {
		global $bp;

		if ( !empty( $_POST['link-desc'] ) ) {
			$link_description = $_POST['link-desc'];
		} else {
			$link_description = $bp->links->current_link->description;
		}

		return apply_filters( 'bp_get_link_details_form_description', $link_description );
	}

function bp_link_details_form_status() {
	echo bp_get_link_details_form_status();
}
	function bp_get_link_details_form_status() {
		global $bp;

		$link_status = null;

		if ( !empty( $_POST['link-status'] ) ) {
			if ( bp_links_is_valid_status( $_POST['link-status'] ) ) {
				$link_status = (integer) $_POST['link-status'];
			}
		} else {
			$link_status = $bp->links->current_link->status;
		}

		return apply_filters( 'bp_get_link_details_form_status', $link_status );
	}

function bp_link_details_form_avatar_thumb_default( $class = '' ) {
	echo bp_get_link_details_form_avatar_thumb_default( $class );
}
	function bp_get_link_details_form_avatar_thumb_default( $class = '' ) {
		return apply_filters( 'bp_get_link_details_form_avatar_thumb_default', bp_get_link_avatar( array( 'class' => $class, 'height' => 80, 'width' => 80 ) ) );
	}

function bp_link_details_form_avatar_thumb() {
	echo bp_get_link_details_form_avatar_thumb();
}
	function bp_get_link_details_form_avatar_thumb() {

		if ( bp_links_admin_current_action_variable() ) {

			return bp_get_link_avatar( 'width=100&height=100&class=avatar-current', bp_links_current_link() );

		} else {

			$embed_service = bp_get_link_details_form_embed_service();

			if ( $embed_service instanceof BP_Links_Embed_Service ) {
				return sprintf( '<img src="%1$s" class="avatar-current" alt="%2$s">', $embed_service->image_thumb_url(), $embed_service->title() );
			} else {
				return bp_get_link_details_form_avatar_thumb_default( 'avatar-current' );
			}
		}
	}

function bp_link_admin_form_action() {
	echo bp_get_link_admin_form_action();
}
	function bp_get_link_admin_form_action() {
		global $bp;

		$action = bp_links_admin_current_action_variable();

		if ( $action ) {
			return apply_filters( 'bp_get_link_admin_form_action', bp_get_link_permalink( $bp->links->current_link ) . '/admin/' . $action, $action );
		} else {
			die('Not an admin path!');
		}
	}

function bp_link_avatar_form_avatar() {
	echo bp_get_link_avatar_form_avatar();
}
	function bp_get_link_avatar_form_avatar() {
		return apply_filters( 'bp_get_link_avatar_form_avatar', bp_get_link_avatar( 'size=full', bp_links_current_link() ) );
	}

function bp_link_avatar_form_delete_link() {
	echo bp_get_link_avatar_form_delete_link();
}
	function bp_get_link_avatar_form_delete_link() {
		global $bp;

		return apply_filters( 'bp_get_link_avatar_delete_link', wp_nonce_url( bp_get_link_permalink( $bp->links->current_link ) . '/admin/link-avatar/delete', 'bp_link_avatar_delete' ) );
	}

function bp_link_avatar_form_embed_html() {
	echo bp_get_link_avatar_form_embed_html();
}
	function bp_get_link_avatar_form_embed_html() {

		$html = ( isset( $_POST['embed-html'] ) ) ? $_POST['embed-html'] : null;

		return apply_filters( 'bp_get_link_avatar_form_embed_html', $html );
	}

function bp_link_avatar_form_embed_html_display() {
	echo bp_get_link_avatar_form_embed_html_display();
}
	function bp_get_link_avatar_form_embed_html_display() {
		if ( bp_links_current_link_embed_enabled() ) {
			return ( bp_links_current_link_embed_service()->avatar_only() ) ? 1 : 0;
		} else {
			return 1;
		}
	}

function bp_link_hidden_fields() {
	if ( isset( $_REQUEST['s'] ) ) {
		echo '<input type="hidden" id="search_terms" value="' . attribute_escape( $_REQUEST['s'] ) . '" name="search_terms" />';
	}

	if ( isset( $_REQUEST['links_search'] ) ) {
		echo '<input type="hidden" id="search_terms" value="' . attribute_escape( $_REQUEST['links_search'] ) . '" name="search_terms" />';
	}
}

function bp_link_feed_item_guid() {
	echo bp_get_link_feed_item_guid();
}
	function bp_get_link_feed_item_guid() {
		return apply_filters( 'bp_get_link_feed_item_guid', bp_get_link_permalink() );
	}

function bp_link_feed_item_title() {
	echo bp_get_link_feed_item_title();
}
	function bp_get_link_feed_item_title() {
		return apply_filters( 'bp_get_link_feed_item_title', bp_get_link_name() );
	}

function bp_link_feed_item_link() {
	echo bp_get_link_feed_item_link();
}
	function bp_get_link_feed_item_link() {
		return apply_filters( 'bp_get_link_feed_item_link', bp_get_link_permalink() );
	}

function bp_link_feed_item_date( $link = false ) {
	echo bp_get_link_feed_item_date( $link );
}
	function bp_get_link_feed_item_date( $link = false ) {
		global $links_template;

		if ( !$link )
			$link =& $links_template->link;

		return apply_filters( 'bp_get_link_feed_item_date', date('D, d M Y H:i:s O', $link->date_created ) );
	}

function bp_link_feed_item_description() {
	echo bp_get_link_feed_item_description();
}
	function bp_get_link_feed_item_description() {
		return apply_filters( 'bp_get_link_feed_item_description', bp_get_link_description() );
	}

/********************************************************************************
 * Links Categories Template Tags
 **/

class BP_Links_Categories_Template {
	var $current_category = -1;
	var $category_count;
	var $categories;
	var $category;

	var $in_the_loop;

	var $pag_page;
	var $pag_num;
	var $pag_links;
	var $total_category_count;

	function bp_links_categories_template( $type, $per_page, $max ) {
		global $bp;

		$this->pag_page = isset( $_REQUEST['lcpage'] ) ? intval( $_REQUEST['lcpage'] ) : 1;
		$this->pag_num = isset( $_REQUEST['num'] ) ? intval( $_REQUEST['num'] ) : $per_page;

		if ( isset( $_REQUEST['s'] ) )
			$filter = $_REQUEST['s'];

		switch ( $type ) {
			case 'all':
			default:
				$this->categories = BP_Links_Category::get_all_filtered( $filter, $this->pag_num, $this->pag_page );
				break;
		}

		if ( !$max || $max >= (int)$this->categories['total'] )
			$this->total_category_count = (int)$this->categories['total'];
		else
			$this->total_category_count = (int)$max;

		$this->categories = $this->categories['categories'];

		if ( $max ) {
			if ( $max >= count($this->categories) )
				$this->category_count = count($this->categories);
			else
				$this->category_count = (int)$max;
		} else {
			$this->category_count = count($this->categories);
		}

		if ( (int) $this->total_category_count && (int) $this->pag_num ) {
			$this->pag_links = paginate_links( array(
				'base' => add_query_arg( 'lcpage', '%#%' ),
				'format' => '',
				'total' => ceil( (int) $this->total_category_count / (int) $this->pag_num ),
				'current' => (int) $this->pag_page,
				'prev_text' => '&laquo;',
				'next_text' => '&raquo;',
				'mid_size' => 1
			));
		}
	}

	function has_categories() {
		if ( $this->category_count )
			return true;

		return false;
	}

	function next_category() {
		$this->current_category++;
		$this->category = $this->categories[$this->current_category];

		return $this->category;
	}

	function rewind_categories() {
		$this->current_category = -1;
		if ( $this->category_count > 0 ) {
			$this->category = $this->categories[0];
		}
	}

	function categories() {
		if ( $this->current_category + 1 < $this->category_count ) {
			return true;
		} elseif ( $this->current_category + 1 == $this->category_count ) {
			do_action('loop_end');
			// Do some cleaning up after the loop
			$this->rewind_categories();
		}

		$this->in_the_loop = false;
		return false;
	}

	function the_category() {
		global $category;

		$this->in_the_loop = true;
		$this->category = $this->next_category();

		if ( !$category = wp_cache_get( 'bp_links_link_category_nouserdata_' . $this->category->category_id, 'bp' ) ) {
			$category = new BP_Links_Category( $this->category->category_id, false, false );
			wp_cache_set( 'bp_links_link_category_nouserdata_' . $this->category->category_id, $category, 'bp' );
		}

		$this->category = $category;

		if ( 0 == $this->current_category ) // loop has just started
			do_action('loop_start');
	}
}

function bp_rewind_links_categories() {
	global $links_categories_template;

	$links_categories_template->rewind_categories();
}

function bp_has_links_categories( $args = '' ) {
	global $bp, $links_categories_template;

	$defaults = array(
		'type' => 'all',
		'per_page' => 10,
		'max' => false
	);

	$r = wp_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	if ( $max ) {
		if ( $per_page > $max )
			$per_page = $max;
	}

	$links_categories_template = new BP_Links_Categories_Template( $type, $per_page, $max );
	return apply_filters( 'bp_has_links_categories', $links_categories_template->has_categories(), $links_categories_template );
}

function bp_links_categories() {
	global $links_categories_template;

	return $links_categories_template->categories();
}

function bp_links_categories_category() {
	global $links_categories_template;

	return $links_categories_template->the_category();
}

function bp_links_categories_pagination_count() {
	global $bp, $links_categories_template;

	$from_num = intval( ( $links_categories_template->pag_page - 1 ) * $links_categories_template->pag_num ) + 1;
	$to_num = ( $from_num + ( $links_categories_template->pag_num - 1 ) > $links_categories_template->total_category_count ) ? $links_categories_template->total_category_count : $from_num + ( $links_categories_template->pag_num - 1) ;

	echo sprintf( __( 'Viewing category %1$d to %2$d (of %3$d categories)', 'buddypress-links' ), $from_num, $to_num, $links_categories_template->total_category_count ); ?> &nbsp;
	<span class="ajax-loader"></span><?php
}

function bp_links_categories_pagination_links() {
	echo bp_get_links_categories_pagination_links();
}
	function bp_get_links_categories_pagination_links() {
		global $links_categories_template;

		return apply_filters( 'bp_get_links_categories_pagination_links', $links_categories_template->pag_links );
	}

function bp_links_categories_category_id() {
	echo bp_get_links_categories_category_id();
}
	function bp_get_links_categories_category_id() {
		global $links_categories_template;

		return apply_filters( 'bp_get_links_categories_category_id', $links_categories_template->category->id );
	}

function bp_links_categories_category_name() {
	echo bp_get_links_categories_category_name();
}
	function bp_get_links_categories_category_name() {
		global $links_categories_template;

		return apply_filters( 'bp_get_links_categories_category_name', $links_categories_template->category->name );
	}

function bp_links_categories_category_description() {
	echo bp_get_links_categories_category_description();
}
	function bp_get_links_categories_category_description() {
		global $links_categories_template;

		return apply_filters( 'bp_get_links_categories_category_description', $links_categories_template->category->description );
	}

function bp_links_categories_category_slug() {
	echo bp_get_links_categories_category_slug();
}
	function bp_get_links_categories_category_slug() {
		global $links_categories_template;

		return apply_filters( 'bp_get_links_categories_category_slug', $links_categories_template->category->slug );
	}

function bp_links_categories_category_priority() {
	echo bp_get_links_categories_category_priority();
}
	function bp_get_links_categories_category_priority() {
		global $links_categories_template;

		return apply_filters( 'bp_get_links_categories_category_priority', $links_categories_template->category->priority );
	}

function bp_links_categories_category_link_count() {
	echo bp_get_links_categories_category_link_count();
}
	function bp_get_links_categories_category_link_count() {
		global $links_categories_template;

		return apply_filters( 'bp_get_links_categories_category_link_count', BP_Links_Category::get_link_count( $links_categories_template->category->id ) );
	}

function bp_links_categories_category_date_created() {
	echo bp_get_links_categories_category_date_created();
}
	function bp_get_links_categories_category_date_created() {
		global $links_categories_template;

		return apply_filters( 'bp_get_links_categories_category_date_created', date( get_option( 'date_format' ), $links_categories_template->category->date_created ) );
	}

function bp_links_categories_category_date_updated() {
	echo bp_get_links_categories_category_date_updated();
}
	function bp_get_links_categories_category_date_updated() {
		global $links_categories_template;

		return apply_filters( 'bp_get_links_categories_category_date_updated', date( get_option( 'date_format' ), $links_categories_template->category->date_updated ) );
	}

function bp_links_categories_hidden_fields() {
	if ( isset( $_REQUEST['s'] ) ) {
		echo '<input type="hidden" id="search_terms" value="' . attribute_escape( $_REQUEST['s'] ) . '" name="search_terms" />';
	}
}

function bp_links_category_select_options( $selected_category_id = null, $element_id = 'category', $element_class = '' ) {

	do_action( 'bp_before_links_category_select_options' );

	// grab all categories
	$categories = BP_Links_Category::get_all();

	$class_string = ( empty( $element_class ) ) ? null : sprintf( ' class="%s"', $element_class );

	foreach ( $categories as $category ) {
		// populate
		$category = new BP_Links_Category( $category->category_id );
		// is this one selected?
		$selected = ( $selected_category_id == $category->id ) ? ' selected="selected"' : null;
		// output it
		echo sprintf( '<option value="%d"%s />%s</option>', $category->id, $selected, $category->name ) . PHP_EOL;
	}

	do_action( 'bp_after_links_category_select_options' );
}

function bp_links_category_radio_options( $selected_category_id = 1, $element_name = 'category', $element_class = '' ) {

	do_action( 'bp_before_links_category_radio_options' );

	// grab all categories
	$categories = BP_Links_Category::get_all();

	foreach ( $categories as $category ) {
		// populate
		$category = new BP_Links_Category( $category->category_id );
		// is this one selected?
		$selected = ( $selected_category_id == $category->id ) ? ' checked="checked"' : null;
		// has class string?
		$class_string = ( empty( $element_class ) ) ? null : sprintf( ' class="%s"', $element_class );
		// output it
		echo sprintf( '<input type="radio" name="%s" value="%d"%s%s />%s ', $element_name, $category->id, $class_string, $selected, $category->name );
	}
	// print newline
	echo PHP_EOL;

	do_action( 'bp_after_links_category_radio_options' );

}

function bp_links_category_radio_options_with_all( $selected_category_id = 1, $element_name = 'category', $element_class = '' ) {

	do_action( 'bp_before_links_category_radio_options_with_all' );

	// is this one selected?
	$selected = ( empty( $selected_category_id ) ) ? ' checked="checked"' : null;
	// has class string?
	$class_string = ( empty( $element_class ) ) ? null : sprintf( ' class="%s"', $element_class );
	// output it
	echo sprintf( '<input type="radio" name="%s" value=""%s%s />%s ', $element_name, $class_string, $selected, __( 'All', 'buddypress-links' ) );

	do_action( 'bp_after_links_category_radio_options_with_all' );

	bp_links_category_radio_options();
}

/***
 * Links RSS Feed Template Tags
 */

// TODO need to handle 'all links' AND 'my links'
function bp_directory_links_feed_link() {
	echo bp_get_directory_links_feed_link();
}
	function bp_get_directory_links_feed_link() {
		global $bp;
		/*
		if ( !empty( $_POST['scope'] ) && $_POST['scope'] == 'mylinks' )
			return $bp->loggedin_user->domain . bp_links_slug() . '/my-links/feed/';
		else
		*/
		return apply_filters( 'bp_get_directory_links_feed_link', site_url( bp_links_root_slug() . '/feed' ) );
	}

function bp_link_activity_feed_link() {
	echo bp_get_link_activity_feed_link();
}
	function bp_get_link_activity_feed_link() {
		global $bp;

		return apply_filters( 'bp_get_link_activity_feed_link', bp_get_link_permalink( $bp->links->current_link ) . '/feed/' );
	}


/*******************************
 * Links Profile Template Tags
 **/

function bp_links_notification_settings() {
	global $current_user; ?>
	<table class="notification-settings" id="links-notification-settings">
		<tr>
			<th class="icon"></th>
			<th class="title"><?php _e( 'Links', 'buddypress-links' ) ?></th>
			<th class="yes"><?php _e( 'Yes', 'buddypress-links' ) ?></th>
			<th class="no"><?php _e( 'No', 'buddypress-links' )?></th>
		</tr>
		<tr>
			<td></td>
			<td><?php _e( 'A member posts a comment on a link you created', 'buddypress-links' ) ?></td>
			<td class="yes"><input type="radio" name="notifications[notification_links_activity_post]" value="yes" <?php if ( !get_usermeta( $current_user->id, 'notification_links_activity_post') || 'yes' == get_usermeta( $current_user->id, 'notification_links_activity_post') ) { ?>checked="checked" <?php } ?>/></td>
			<td class="no"><input type="radio" name="notifications[notification_links_activity_post]" value="no" <?php if ( 'no' == get_usermeta( $current_user->id, 'notification_links_activity_post') ) { ?>checked="checked" <?php } ?>/></td>
		</tr>
		<?php do_action( 'bp_links_notification_settings' ); ?>
	</table>
<?php
}


/***
 * Links Vote Panel Template Tags
 */

function bp_link_vote_panel( $show_count = true ) {

	$show_count = apply_filters( 'bp_get_link_vote_panel_show_count', $show_count );
	
	// render tags ?>
	<div class="link-vote-panel" id="link-vote-panel-<?php bp_link_id() ?>">
		<?php do_action( 'bp_before_link_vote_panel_content' ) ?>
		<?php bp_link_vote_panel_clickers() ?>
		<?php bp_link_vote_panel_count( $show_count ) ?>
		<?php do_action( 'bp_after_link_vote_panel_content' ) ?>
	</div><?php
}

function bp_link_vote_panel_clickers() {
	// render tags ?>
	<div class="clickers">
		<a href="#vu" id="vote-up-<?php bp_link_id() ?>" class="vote up"></a>
		<div id="vote-total-<?php bp_link_id() ?>" class="vote-total"><?php printf( '%+d', bp_get_link_vote_total() ) ?></div>
		<a href="#vd" id="vote-down-<?php bp_link_id() ?>" class="vote down"></a>
	</div><?php
}

function bp_link_vote_panel_count( $show = true ) {

	if ( true === $show ) {
		// render tags ?>
		<div class="vote-count">
			<span id="vote-count-<?php bp_link_id() ?>"><?php bp_link_vote_count() ?></span>
			<?php _e( 'Votes', 'buddypress-links' ) ?>
		</div><?php
	}
}
	
function bp_link_vote_panel_form() {
	printf( '<form action="%s/" method="post" id="link-vote-form">', site_url() );
	wp_nonce_field( 'link_vote', '_wpnonce-link-vote' );
	echo '</form>' . PHP_EOL;
}

/*********************************
 * Links List Template Helper Tags
 **/

function bp_link_list_item_id() {
	echo bp_get_link_list_item_id();
}
	function bp_get_link_list_item_id() {
		return apply_filters( 'bp_get_link_list_item_id', 'linklistitem-' . bp_get_link_id() );
	}

function bp_link_list_item_class() {
	echo bp_get_link_list_item_class();
}
	function bp_get_link_list_item_class() {
		return apply_filters( 'bp_get_link_list_item_class', 'avmax-' . bp_get_link_avatar_display_size() );
	}

function bp_link_list_item_avatar() {
	echo bp_get_link_list_item_avatar();
}
	function bp_get_link_list_item_avatar() {
		switch ( bp_get_link_avatar_display_size() ) {
			case 50:
				return bp_get_link_avatar_thumb();
			default:
				return bp_get_link_avatar();
		}
	}

function bp_link_list_item_name() {
	echo bp_get_link_list_item_name();
}
	function bp_get_link_list_item_name() {
		return apply_filters( 'bp_get_link_list_item_name', bp_link_name() );
	}

function bp_link_list_item_category_name() {
	echo bp_get_link_list_item_category_name();
}
	function bp_get_link_list_item_category_name() {
		return apply_filters( 'bp_get_link_list_item_category_name', bp_link_category_name() );
	}

function bp_link_list_item_description() {
	echo bp_get_link_list_item_description();
}
	function bp_get_link_list_item_description() {
		return apply_filters( 'bp_get_link_list_item_description', bp_link_description() );
	}

function bp_link_list_item_url() {
	echo bp_get_link_list_item_url();
}
	function bp_get_link_list_item_url() {
		if ( BP_LINKS_LIST_ITEM_URL_LOCAL == true ) {
			$url = bp_link_permalink();
		} else {
			$url = bp_get_link_url();
		}
		return apply_filters( 'bp_get_link_list_item_url', $url );
	}

function bp_link_list_item_url_domain() {
	echo bp_get_link_list_item_url_domain();
}
	function bp_get_link_list_item_url_domain() {
		return apply_filters( 'bp_get_link_list_item_url_domain', bp_link_url_domain() );
	}

function bp_link_list_item_url_target() {
	echo bp_get_link_list_item_url_target();
}
	function bp_get_link_list_item_url_target() {
		$target = apply_filters( 'bp_get_link_list_item_url_target', '' );

		if ( !empty( $target ) ) {
			return sprintf( ' target="%s"', $target );
		}
	}

function bp_link_list_item_url_rel() {
	echo bp_get_link_list_item_url_rel();
}
	function bp_get_link_list_item_url_rel() {
		$rel = apply_filters( 'bp_get_link_list_item_url_rel', '' );

		if ( !empty( $rel ) ) {
			return sprintf( ' rel="%s"', $rel );
		}
	}

function bp_link_list_item_external() {
	echo bp_get_link_list_item_external();
}
	function bp_get_link_list_item_external() {
		return apply_filters( 'bp_get_link_list_item_external', __( 'External Link', 'buddypress-links' ) );
	}

function bp_link_list_item_external_url() {
	echo bp_get_link_list_item_external_url();
}
	function bp_get_link_list_item_external_url() {
		return apply_filters( 'bp_get_link_list_item_external_url', bp_link_url() );
	}

function bp_link_list_item_external_url_rel() {
	echo bp_get_link_list_item_external_url_rel();
}
	function bp_get_link_list_item_external_url_rel() {
		$rel = apply_filters( 'bp_get_link_list_item_external_url_rel', '' );

		if ( !empty( $rel ) )
			return sprintf( ' rel="%s"', $rel );
	}

function bp_link_list_item_external_url_target() {
	echo bp_get_link_list_item_external_url_target();
}
	function bp_get_link_list_item_external_url_target() {
		$target = apply_filters( 'bp_get_link_list_item_external_url_target', '' );

		if ( !empty( $target ) ) {
			return sprintf( ' target="%s"', $target );
		}
	}

function bp_link_list_item_continue() {
	echo bp_get_link_list_item_continue();
}
	function bp_get_link_list_item_continue() {
		return apply_filters( 'bp_get_link_list_item_continue', __( 'more...', 'buddypress-links' ) );
	}

function bp_link_list_item_continue_url() {
	echo bp_get_link_list_item_continue_url();
}
	function bp_get_link_list_item_continue_url( $link = false ) {
		return apply_filters( 'bp_get_link_list_item_continue_url', bp_link_permalink() );
	}

function bp_link_list_item_continue_url_rel() {
	echo bp_get_link_list_item_continue_url_rel();
}
	function bp_get_link_list_item_continue_url_rel() {
		$rel = apply_filters( 'bp_get_link_list_item_continue_url_rel', '' );
		
		if ( !empty( $rel ) )
			return sprintf( ' rel="%s"', $rel );
	}

function bp_link_list_item_continue_url_target() {
	echo bp_get_link_list_item_continue_url_target();
}
	function bp_get_link_list_item_continue_url_target() {
		$target = apply_filters( 'bp_get_link_list_item_continue_url_target', '' );

		if ( !empty( $target ) )
			return sprintf( ' target="%s"', $target );
	}

function bp_link_list_item_xtrabar_comments() {
	echo bp_get_link_list_item_xtrabar_comments();
}
	function bp_get_link_list_item_xtrabar_comments() {
		return apply_filters( 'bp_get_link_list_item_xtrabar_comments', __( 'Comments', 'buddypress-links' ) );
	}
	
function bp_link_list_item_xtrabar_userlink_created() {
	echo bp_get_link_list_item_xtrabar_userlink_created();
}
	function bp_get_link_list_item_xtrabar_userlink_created() {
		return apply_filters( 'bp_get_link_list_item_xtrabar_userlink_created', sprintf( __( 'created %s', 'buddypress-links' ), bp_get_link_time_since_created() ) );
	}

/****
 * Link list filter template tags
 */
function bp_links_link_order_options() { ?>

	<option value="active"><?php _e( 'Last Active', 'buddypress' ) ?></option>
	<option value="popular"><?php _e( 'Most Popular', 'buddypress-links' ) ?></option>
	<option value="newest"><?php _e( 'Newly Created', 'buddypress' ) ?></option>
	<option value="most-votes"><?php _e( 'Most Votes', 'buddypress-links' ) ?></option>
	<option value="high-votes"><?php _e( 'Highest Rated', 'buddypress-links' ) ?></option> <?php

	do_action( 'bp_links_link_order_options' );
}

?>
