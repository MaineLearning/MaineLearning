
/* Includes externally-stored functions */

/* include_once "functions/chosen-taxonomy-metabox.php"; //


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
 *
 * Modified by Boone Gorges to work for arbitrary tags/post types. Modify the arguments as follows:
 *   'post_types' can be either an array of post types, or the string 'all'
 *   'taxes' can be either an array of taxonomy names, or the string 'all'
 */

function hhs_load_chosen_tax_metabox() {
	$args = array(
		'post_types' => array(
			'objects'
		),
		'taxes' => 'all'
	);
	new HHS_Chosen_Tax_Metabox( $args );
}
add_action( 'admin_init', 'hhs_load_chosen_tax_metabox', 1 );

class HHS_Chosen_Tax_Metabox {
	var $post_types = array();
	var $taxes = array();

	function __construct( $args = array() ) {

		$defaults = array(
			'post_types' => 'all',
			'taxes' => 'all'
		);
		$r = wp_parse_args( $args, $defaults );

		if ( 'all' == $r['post_types'] ) {
			$this->post_types = get_post_types();
		} else {
			$this->post_types = (array) $r['post_types'];
		}

		foreach( $this->post_types as $ptkey => $pt ) {
			$pto = get_post_type_object( $pt );
			if ( $pt->_builtin ) {
				unset( $this->post_types[$ptkey] );
			}
		}

		if ( 'all' == $r['taxes'] ) {
			$this->taxes = get_taxonomies();
		} else {
			$this->taxes = (array) $r['taxes'];
		}

		add_action( 'admin_init', array( &$this, 'add_meta_boxes' ), 5 );
		add_action( 'save_post', array( &$this, 'save' ) );
		add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue_scripts' ), 10, 1 );
		add_action( 'do_meta_boxes', array( &$this, 'remove_meta_boxes' ), 10, 3 );
	}

	function add_meta_boxes() {
		foreach( $this->post_types as $post_type ) {
			add_meta_box( 'chosen-tax', 'Choose Terms', array( &$this, 'display' ), $post_type, 'side', 'default' );
		}
	}

	function display() {
		global $post;

		wp_nonce_field( 'hhs_chosen_tax_meta_box_nonce', 'hhs_chosen_tax_meta_box_nonce' );
		?>
		<script type="text/javascript">
		jQuery(document).ready(function($){
			$( '.chzn-select' ).chosen();
		});
		</script>
		<?php

		foreach ( $this->taxes as $tkey => $tax ) {

			// Skip if it's not supported by this post type
			$to = get_taxonomy( $tax );
			$pt = '';
			if ( isset( $post->post_type ) ) {
				$pt = $post->post_type;
			} else if ( isset( $_GET['post_type'] ) ) {
				$pt = $_GET['post_type'];
			}

			if ( empty( $pt ) || !in_array( $pt, $to->object_type ) ) {
				continue;
			}

			// add more args if you want (e.g. orderby)
			$terms = get_terms( $tax, array( 'hide_empty' => 0 ) );
			$current_terms = wp_get_post_terms ( $post->ID, $tax, array('fields' => 'ids') );
			?>
			<p><label for="<?php echo $tax; ?>"><?php echo esc_html( $to->labels->name ); ?></label>:</p>
			<p><select name="<?php echo $tax; ?>[]" class="chzn-select widefat" data-placeholder="Select one or more" multiple="multiple">
			<?php foreach ( $terms as $term ) { ?>
				<option value="<?php echo $term->slug; ?>"<?php selected( in_array( $term->term_id, $current_terms ) ); ?>><?php echo $term->name; ?></option>
			<?php } ?>
			</select>
			</p>
			<?php
		}
	}

	function save( $post_id ) {

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

		foreach ( $this->taxes as $tax ) {
			if ( isset( $_POST[$tax] ) && is_array( $_POST[$tax] ) ) {
				$terms = array();

				// If the tax is hierarchical, we have to convert the string values
				// to term ids, or wp_set_post_terms() will complain
				if ( is_taxonomy_hierarchical( $tax ) ) {
					foreach( $_POST[$tax] as $term ) {
						$term_obj = get_term_by( 'slug', $term, $tax );
						if ( !is_wp_error( $term_obj ) && isset( $term_obj->term_id ) ) {
							$terms[] = $term_obj->term_id;
						}
					}
				} else {
					$terms = $_POST[$tax];
				}

				wp_set_post_terms( $post_id, $terms, $tax, false );
			}
		}
	}

	// Chosen JS and CSS enqueue - assumes you are doing this in a theme
	// with the JS, CSS, and sprite files in themefolder/js/chosen/
	// You'd want to use plugins_url() instead if using this in a plugin
	function enqueue_scripts( $hook ) {
		global $post;

		// There's probably a better way to check the screen...
		if ( $hook == 'post-new.php' || $hook == 'post.php' ) {
			if ( in_array( $post->post_type, $this->post_types ) ) {
				wp_enqueue_script(  'chosen', get_stylesheet_directory_uri().'/js/chosen/chosen.jquery.min.js', array( 'jquery' ), '1.0' );
				wp_enqueue_style( 'chosen', get_stylesheet_directory_uri().'/js/chosen/chosen.css' );
			}
		}
	}

	// Hide unused taxonomy metabox(es)
	function remove_meta_boxes( $post_type, $priority, $post ) {
		global $wp_meta_boxes;

		foreach( $this->taxes as $tax ) {
			remove_meta_box( "{$tax}div", $post->post_type, 'side' );
		}
	}
}