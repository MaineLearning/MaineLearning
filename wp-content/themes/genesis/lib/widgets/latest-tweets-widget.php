<?php
/**
 * Adds the Latest tweets widget.
 *
 * @category Genesis
 * @package  Widgets
 * @author   StudioPress
 * @license  http://www.opensource.org/licenses/gpl-license.php GPL-2.0+
 * @link     http://www.studiopress.com/themes/genesis
 */


/**
 * Genesis Latest Tweets widget class.
 *
 * @category Genesis
 * @package Widgets
 *
 * @since 0.1.8
 */
class Genesis_Latest_Tweets_Widget extends WP_Widget {

	/**
	 * Holds widget settings defaults, populated in constructor.
	 *
	 * @var array
	 */
	protected $defaults;

	/**
	 * Constructor. Set the default widget options and create widget.
	 *
	 * @since 0.1.8
	 */
	function __construct() {

		$this->defaults = array(
			'title'                => '',
			'twitter_id'           => '',
			'twitter_num'          => '',
			'twitter_duration'     => '',
			'twitter_hide_replies' => 0,
			'follow_link_show'     => 0,
			'follow_link_text'     => '',
		);

		$widget_ops = array(
			'classname'   => 'latest-tweets',
			'description' => __( 'Display a list of your latest tweets.', 'genesis' ),
		);

		$control_ops = array(
			'id_base' => 'latest-tweets',
			'width'   => 200,
			'height'  => 250,
		);

		$this->WP_Widget( 'latest-tweets', __( 'Genesis - Latest Tweets', 'genesis' ), $widget_ops, $control_ops );

	}

	/**
	 * Echo the widget content.
	 *
	 * @since 0.1.8
	 *
	 * @param array $args Display arguments including before_title, after_title, before_widget, and after_widget.
	 * @param array $instance The settings for the particular instance of the widget
	 */
	function widget( $args, $instance ) {

		extract( $args );

		/** Merge with defaults */
		$instance = wp_parse_args( (array) $instance, $this->defaults );

		echo $before_widget;

		if ( $instance['title'] )
			echo $before_title . apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base ) . $after_title;

		echo '<ul>' . "\n";

		$tweets = get_transient( $instance['twitter_id'] . '-' . $instance['twitter_num'] . '-' . $instance['twitter_duration'] );

		if ( ! $tweets ) {
			$count   = isset( $instance['twitter_hide_replies'] ) ? (int) $instance['twitter_num'] + 100 : (int) $instance['twitter_num'];
			$twitter = wp_remote_retrieve_body(
				wp_remote_request(
					sprintf( 'http://api.twitter.com/1/statuses/user_timeline.json?screen_name=%s&count=%s&trim_user=1', $instance['twitter_id'], $count ),
					array( 'timeout' => 100, )
				)
			);

			$json = json_decode( $twitter );

			if ( ! $twitter ) {
				$tweets[] = '<li>' . __( 'The Twitter API is taking too long to respond. Please try again later.', 'genesis' ) . '</li>' . "\n";
			}
			elseif ( is_wp_error( $twitter ) ) {
				$tweets[] = '<li>' . __( 'There was an error while attempting to contact the Twitter API. Please try again.', 'genesis' ) . '</li>' . "\n";
			}
			elseif ( is_object( $json ) && $json->error ) {
				$tweets[] = '<li>' . __( 'The Twitter API returned an error while processing your request. Please try again.', 'genesis' ) . '</li>' . "\n";
			}
			else {
				/** Build the tweets array */
				foreach ( (array) $json as $tweet ) {
					/** Don't include @ replies (if applicable) */
					if ( $instance['twitter_hide_replies'] && $tweet->in_reply_to_user_id )
						continue;

					/** Stop the loop if we've got enough tweets */
					if ( ! empty( $tweets[(int)$instance['twitter_num'] - 1] ) )
						break;

					/** Add tweet to array */
					$timeago = sprintf( __( 'about %s ago', 'genesis' ), human_time_diff( strtotime( $tweet->created_at ) ) );
					$timeago_link = sprintf( '<a href="%s" rel="nofollow">%s</a>', esc_url( sprintf( 'http://twitter.com/%s/status/%s', $instance['twitter_id'], $tweet->id_str ) ), esc_html( $timeago ) );

					$tweets[] = '<li>' . genesis_tweet_linkify( $tweet->text ) . ' <span style="font-size: 85%;">' . $timeago_link . '</span></li>' . "\n";
				}

				/** Just in case */
				$tweets = array_slice( (array) $tweets, 0, (int) $instance['twitter_num'] );

				if ( $instance['follow_link_show'] && $instance['follow_link_text'] )
					$tweets[] = '<li class="last"><a href="' . esc_url( 'http://twitter.com/'.$instance['twitter_id'] ).'">'. esc_html( $instance['follow_link_text'] ) .'</a></li>';

				$time = ( absint( $instance['twitter_duration'] ) * 60 );

				/** Save them in transient */
				set_transient( $instance['twitter_id'].'-'.$instance['twitter_num'].'-'.$instance['twitter_duration'], $tweets, $time );
			}
		}
		foreach( (array) $tweets as $tweet )
			echo $tweet;

		echo '</ul>' . "\n";

		echo $after_widget;

	}

	/**
	 * Update a particular instance.
	 *
	 * This function should check that $new_instance is set correctly.
	 * The newly calculated value of $instance should be returned.
	 * If "false" is returned, the instance won't be saved/updated.
	 *
	 * @since 0.1.8
	 *
	 * @param array $new_instance New settings for this instance as input by the user via form()
	 * @param array $old_instance Old settings for this instance
	 * @return array Settings to save or bool false to cancel saving
	 */
	function update( $new_instance, $old_instance ) {

		/** Force the transient to refresh */
		delete_transient( $old_instance['twitter_id'].'-'.$old_instance['twitter_num'].'-'.$old_instance['twitter_duration'] );
		$new_instance['title'] = strip_tags( $new_instance['title'] );
		return $new_instance;

	}

	/**
	 * Echo the settings update form.
	 *
	 * @since 0.1.8
	 *
	 * @param array $instance Current settings
	 */
	function form( $instance ) {

		/** Merge with defaults */
		$instance = wp_parse_args( (array) $instance, $this->defaults );

		if ( ! current_user_can( 'install_plugins' ) ) {

			echo '<p class="description">' . __( 'This widget has been deprecated, and should no longer be used.', 'genesis' ) . '</p>';
			echo '<p class="description">' . sprintf( __( 'If you would like to continue to use the Latest Tweets widget functionality, please have a site administrator <a href="%s" target="_blank">install this plugin</a>.', 'genesis' ), esc_url( 'http://wordpress.org/extend/plugins/genesis-latest-tweets/' ) ) . '</p>';

			return;

		}

		add_thickbox();

		echo '<p class="description">' . __( 'This widget has been deprecated, and should no longer be used.', 'genesis' ) . '</p>';
		echo '<p class="description">' . sprintf( __( 'If you would like to continue to use the Latest Tweets widget functionality, please <a href="%s" class="thickbox" title="Install Genesis Latest Tweets">install this plugin</a>.', 'genesis' ), esc_url( network_admin_url( 'plugin-install.php?tab=plugin-information&plugin=genesis-latest-tweets&TB_iframe=true&width=660&height=550' ) ) ) . '</p>';

	}

}
