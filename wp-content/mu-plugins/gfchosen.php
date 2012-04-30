<?php

/**
 * This file ensures that the Chosen JS library is loaded whenever a page has the [gravityforms]
 * shortcode
 */

/**
 * If the post/page contains a GF shortcode, enqueue the chosen & jquery scripts.
 */
function mln_maybe_enqueue_scripts() {
	global $post;

	if ( isset( $post->post_content ) ) {
		$content = $post->post_content;
	} else {
		return false;
	}

	if ( false !== strpos( $content, '[gravityform' ) ) {
		$url_base = WP_CONTENT_URL . '/chosen';

		add_action( 'wp_head', 'mln_load_chosen', 9999 );

		wp_enqueue_style(  'chosen', $url_base . '/chosen.css' );
		wp_enqueue_script( 'chosen', $url_base . '/chosen.jquery.min.js', array( 'jquery' ), false, true );

	}
}
add_action( 'wp_enqueue_scripts', 'mln_maybe_enqueue_scripts' );

function mln_load_chosen() {
	?>

	<script type='text/javascript'>
		jQuery(document).ready(function($) { $('select:not(#mo-fonts)').chosen();});
	</script>

	<?php
}

?>