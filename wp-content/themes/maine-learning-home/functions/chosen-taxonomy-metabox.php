<?php
/**
 * WordPress Chosen Taxonomy Metabox
 * Author: Helen Hou-Sandi
 *
 * Use Chosen for a replacement taxonomy metabox in WordPress
 * Useful for taxonomies that aren't changed much on the fly,
 * as Chosen is for selection only.
 * You can always use the taxonomy admin screen to add/edit taxonomy terms.
 *
 * Example screenshot: http://cl.ly/2T2D232x172G353i2V2C
 *
 * Chosen: http://harvesthq.github.com/chosen/
 */

add_action( 'admin_init', 'hhs_add_meta_boxes', 1 );
function hhs_add_meta_boxes() {
	add_meta_box( 'chosen-tax', 'Choose Terms', 'hhs_chosen_tax_meta_box_display', 'post', 'side', 'default' );
}

function hhs_chosen_tax_meta_box_display() {
	global $post;
	
	wp_nonce_field( 'hhs_chosen_tax_meta_box_nonce', 'hhs_chosen_tax_meta_box_nonce' );
	?>
	<script type="text/javascript">
	jQuery(document).ready(function($){
		$( '.chzn-select' ).chosen();
	});
	</script>
	<?php
	// which taxonomies should be used - can be multiple
	$taxes = array(
		'post_tag' => 'Tags',
	);
	
	foreach ( $taxes as $tax => $label ) {
		// add more args if you want (e.g. orderby)
		$terms = get_terms( $tax, array( 'hide_empty' => 0 ) );
		$current_terms = wp_get_post_terms ( $post->ID, $tax, array('fields' => 'ids') );
		?>
		<p><label for="<?php echo $tax; ?>"><?php echo $label; ?></label>:</p>
		<p><select name="<?php echo $tax; ?>[]" class="chzn-select widefat" data-placeholder="Select one or more" multiple="multiple">
		<?php foreach ( $terms as $term ) { ?>
			<option value="<?php echo $term->slug; ?>"<?php selected( in_array( $term->term_id, $current_terms ) ); ?>><?php echo $term->name; ?></option>
		<?php } ?>
		</select>
		</p>
		<?php
	}
}
add_action( 'save_post', 'hhs_chosen_tax_meta_box_display_save' );
function hhs_chosen_tax_meta_box_display_save( $post_id ) {

	// verify nonce
	if ( ! isset( $_POST['hhs_chosen_tax_meta_box_nonce'] ) ||
	     ! wp_verify_nonce( $_POST['hhs_chosen_tax_meta_box_nonce'], 'hhs_chosen_tax_meta_box_nonce' ) )
		return;
	
	// check autosave - maybe overkill?
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return;
	
	// check permissions
	if ( ! current_user_can( 'edit_post', $post_id ) )
		return;
	
	// change to match above, or be a good coder and don't dupe this
	$taxes = array (
		'post_tag',
	);
	
	foreach ( $taxes as $tax ) {
		if ( isset( $_POST[$tax] ) && is_array( $_POST[$tax] ) )
			wp_set_post_terms( $post_id, $_POST[$tax], $tax, false );
	}
}
 
// Chosen JS and CSS enqueue - assumes you are doing this in a theme
// with the JS, CSS, and sprite files in themefolder/js/chosen/
// You'd want to use plugins_url() instead if using this in a plugin
add_action( 'admin_enqueue_scripts', 'hhs_add_admin_scripts', 10, 1 );
function hhs_add_admin_scripts( $hook ) {
	global $post;
	
	// There's probably a better way to check the screen...
	if ( $hook == 'post-new.php' || $hook == 'post.php' ) {
		if ( 'post' === $post->post_type ) {     
			wp_enqueue_script(  'chosen', get_template_directory_uri().'/js/chosen/chosen.jquery.min.js', array( 'jquery' ), '1.0' );
			wp_enqueue_style( 'chosen', get_template_directory_uri().'/js/chosen/chosen.css' );
		}
	}
}

// Hide unused taxonomy metabox(es)
add_action( 'do_meta_boxes', 'hhs_remove_tax_meta_boxes', 10, 3 );
function hhs_remove_tax_meta_boxes( $post_type, $priority, $post ) {
	remove_meta_box( 'tagsdiv-post_tag', 'post', 'side' );
}
?>