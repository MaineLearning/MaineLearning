<?php
/*
 Plugin Name: PS Taxonomy Expander
 Plugin URI: http://www.warna.info/archives/451/
 Description: PS Taxonomy Expander makes easy to use categories, tags and custom taxonomies on editing posts.
 Author: Hitoshi Omagari
 Version: 1.1.5
 License: GPLv2 or later
 Text Domain: ps-taxonomy-expander
 Domain Path: /language/
 */


class PS_Taxonomy_Expander {
	var $version = '1.1.5';
	var $single_taxonomies;
	var $edit_post_type;
	var $disp_taxonomies;

function PS_Taxonomy_Expander() {
	$this->__construct();
}


function __construct() {
	load_plugin_textdomain( 'ps-taxonomy-expander', false, plugin_basename( dirname( __FILE__ ) ) . '/language' );
	if ( is_admin() ) {
		add_action( 'admin_init'						, array( &$this, 'get_plugin_option' ) );
		add_action( 'admin_print_styles-post-new.php'	, array( &$this, 'add_style_hide_add_category' ) );
		add_action( 'admin_print_styles-post.php'		, array( &$this, 'add_style_hide_add_category' ) );
		add_action( 'admin_footer-post.php'				, array( &$this, 'replace_check2radio_taxonomy_scripts' ) );
		add_action( 'admin_footer-post-new.php'			, array( &$this, 'replace_check2radio_taxonomy_scripts' ) );
		add_action( 'admin_footer-edit.php'				, array( &$this, 'quick_replace_check2radio_taxonomy_scripts' ) );
		add_action( 'admin_head-edit.php'				, array( &$this, 'remove_inline_edit_post_js' ) );
		add_action( 'load-edit.php'						, array( &$this, 'add_sc_inline_edit_js' ) );
		add_action( 'load-options-writing.php'			, array( &$this, 'add_default_term_setting_item' ) );
		add_filter( 'whitelist_options'					, array( &$this, 'allow_default_term_setting' ) );
		add_action( 'load-options.php'					, array( &$this, 'check_single_taxonomies_postdata' ) );
		add_action( 'admin_menu'						, array( &$this, 'add_media_taxonomy_menu' ) );
		add_filter( 'attachment_fields_to_edit'			, array( &$this, 'replace_attachement_taxonomy_input_to_check' ), 100, 2 );
		add_action( 'load-media.php'					, array( &$this, 'join_media_taxonomy_datas' ) );
		add_action( 'load-media-upload.php'				, array( &$this, 'join_media_taxonomy_datas' ) );
		add_action( 'right_now_content_table_end'		, array( &$this, 'display_taxonomy_post_count' ) );
		add_action( 'personal_options'					, array( &$this, 'add_taxonomy_count_dashboard_right_now_field' ) );
		add_action( 'profile_update'					, array( &$this, 'update_taxonomy_count_dashboard_right_now' ), 10, 2 );
		add_action( 'admin_menu'						, array( &$this, 'add_taxonomy_order_menu' ) );
		add_action( 'admin_init'						, array( &$this, 'add_jquery_sortable' ) );
		add_action( 'load-edit.php'						, array( &$this, 'get_tax_columns' ) );
		add_action( 'admin_print_styles-edit.php'		, array( &$this, 'add_tax_column_styles' ) );
		add_filter( 'plugin_action_links'				, array( &$this, 'plugin_term_order_links' ), 10, 2 );
		add_action( 'load-options-writing.php'			, array( &$this, 'add_tax_column_settings' ) );
		add_filter( 'whitelist_options'					, array( &$this, 'allow_list_display_tax_setting' ) );
		add_action( 'restrict_manage_posts'				, array( &$this, 'add_filter_tax_dropdown' ) );
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			add_action( 'admin_init'	, array( &$this, 'get_tax_columns' ) );
		}
	}
	add_action( 'wp_insert_post'	, array( &$this, 'add_post_type_default_term' ), 10, 2 );
	add_action( 'add_attachment'	, array( &$this, 'add_post_type_default_term' ) );
	add_action( 'edit_attachment'	, array( &$this, 'add_post_type_default_term' ) );
	add_filter( 'get_terms_orderby'	, array( &$this, 'term_orderby_order' ), 10, 2 );
}


function get_plugin_option() {
	$this->single_taxonomies = get_option( 'single_taxonomies' );
	if ( ! is_array( $this->single_taxonomies ) ) {
		$this->single_taxonomies = array();
	}
	$post_types = get_post_types( array( 'public' => true, 'show_ui' => true ), false );
	foreach ( $post_types as $post_slug => $post_type ) {
		$taxonomies = get_object_taxonomies( $post_slug, 'object' );
		if ( ! empty( $taxonomies ) ) {
			foreach ( $taxonomies as $tax_slug => $taxonomy ) {
				if ( ! in_array( $tax_slug, array( 'category', 'post_tag' ) ) && $taxonomy->show_ui !== false ) {
					$this->disp_taxonomies[$post_slug] = get_option( $post_slug . '_list_taxonomies' );
					if ( ! $this->disp_taxonomies[$post_slug] ) {
						$this->disp_taxonomies[$post_slug] = array();
					}
					break;
				}
			}
		}
	}
}


function replace_check2radio_taxonomy_scripts() {
	global $post;
	$taxonomies = get_object_taxonomies( $post->post_type, 'object' );

	if ( $taxonomies ) {
		foreach ( $taxonomies as $label => $obj ) {
			if ( $obj->show_ui && $obj->hierarchical && $obj->public && in_array( $obj->name, $this->single_taxonomies ) ) {
					echo <<< EOF
<script type="text/javascript">
	//<![CDATA[
	jQuery(document).ready(function($){
		$("#{$obj->name}checklist input[type=checkbox]").each(function(){
			\$check = $(this);
			var checked = \$check.attr("checked") ? ' checked="checked"' : '';
			var item = '<input type="radio" id="' + \$check.attr("id") + '" name="' + \$check.attr("name") + '"' + checked + ' value="' + \$check.val() + '"/>';
			\$check.replaceWith( item );
		});
		$("#{$obj->name}checklist-pop input[type=checkbox]").each(function(){
			\$check = $(this);
			var checked = \$check.attr("checked") ? ' checked="checked"' : '';
			var item = '<input type="radio" id="' + \$check.attr("id") + '" name="' + \$check.attr("name") + '"' + checked + ' value="' + \$check.val() + '"/>';
			\$check.replaceWith( item );
		});
	});
	//]]>
</script>
EOF;
			}
		}
	}
}


function quick_replace_check2radio_taxonomy_scripts() {
$post_type = isset( $_GET['post_type'] ) ? $_GET['post_type'] : 'post';
$taxonomies = get_object_taxonomies( $post_type, 'object' );
if ( $taxonomies ) {
	foreach ( $taxonomies as $label => $obj ) {
		if ( $obj->show_ui && $obj->hierarchical && $obj->public && in_array( $obj->name, $this->single_taxonomies ) ) {
echo <<< EOF
<script type="text/javascript">
	//<![CDATA[
	jQuery(document).ready(function($){
		$(".{$obj->name}-checklist input[type=checkbox]").each(function(){
			\$check = $(this);
			var item = '<input type="radio" id="' + \$check.attr("id") + '" name="' + \$check.attr("name") + '" value="' + \$check.val() + '"/>';
			\$check.replaceWith( item );
		});
	});
	//]]>
</script>
EOF;
			}
		}
	}
}


function remove_inline_edit_post_js() {
	global $wp_version;
	wp_deregister_script( 'inline-edit-post' );
	if ( version_compare( $wp_version, '3.3.x', '>=' ) ) {
		wp_enqueue_script( 'suggest' );
	}
}


function add_sc_inline_edit_js() {
	global $wp_version;
	if ( version_compare( $wp_version, '3.1.x', '<' ) ) {
		$file = 'ps-inline-edit.3.0.js';
	} else {
		$file = 'ps-inline-edit.3.1.js';
	}
	wp_enqueue_script( 'sc-inline-edit', WP_PLUGIN_URL . '/' . plugin_basename( dirname( __FILE__ ) ) .'/js/' . $file, array(), $this->version, true );
}


function add_style_hide_add_category() {
	global $post;

	$taxonomies = get_object_taxonomies( $post->post_type, 'object' );
	if ( $taxonomies && is_array( $this->single_taxonomies ) ) {
?>
<style>
<!--
<?php
foreach  ( $taxonomies as  $label  => $obj  ) {
	if  ( $obj->show_ui && $obj->hierarchical  && $obj->public  && in_array (  $obj->name, $this->single_taxonomies ) ) {
?>
#<?php echo esc_html (  $label  ); ?>-adder {
	display: none;
}
<?php
	}
}
?>
-->
</style>
<?php
	}
}


function add_default_term_setting_item() {
	$post_types = get_post_types( array( 'public' => true, 'show_ui' => true ), false );
	if ( $post_types ) {
		foreach ( $post_types as $post_type_slug => $post_type ) {
			$post_type_taxonomies = get_object_taxonomies( $post_type_slug, false );
			if ( $post_type_taxonomies ) {
				foreach ( $post_type_taxonomies as $tax_slug => $taxonomy ) {
					if ( ! ( $post_type_slug == 'post' && $tax_slug == 'category' ) && $taxonomy->show_ui ) {
						$post_type_label = $post_type->_builtin ? __( $post_type->labels->singular_name ) : $post_type->labels->singular_name;
						$taxonomy_label = $taxonomy->_builtin ? __( $taxonomy->labels->singular_name ) : $taxonomy->labels->singular_name;
						add_settings_field( $post_type_slug . '_default_' . $tax_slug, sprintf( __( 'Default %s %s', 'ps-taxonomy-expander' ), $post_type_label, $taxonomy_label ), array( &$this, 'default_term_setting_field' ), 'writing', 'default', array( 'post_type' => $post_type_slug, 'taxonomy' => $taxonomy ) );
					}
				}
			}
		}
	}
	$media_taxonomies = get_object_taxonomies( 'attachment', false );
	if ( count( $media_taxonomies ) ) {
		foreach ( $media_taxonomies as $tax_slug => $taxonomy ) {
			if ( $taxonomy->show_ui ) {
				add_settings_field( 'attachment_default_' . $tax_slug, sprintf( __( 'Default Media %s', 'ps-taxonomy-expander' ), $taxonomy->label ), array( &$this, 'default_term_setting_field' ), 'writing', 'default', array( 'post_type' => 'attachment', 'taxonomy' => $taxonomy ) );
			}
		}
	}
	add_settings_field( 'single_taxonomies', __( 'Option to register taxonomies', 'ps-taxonomy-expander' ), array( &$this, 'single_taxonomies_filed' ), 'writing', 'default' );
}


function default_term_setting_field( $args ) {
	$option_name = $args['post_type'] . '_default_' . $args['taxonomy']->name;
	$default_term = get_option( $option_name );
	$terms = get_terms( $args['taxonomy']->name, 'hide_empty=0' );
	if ( $terms ) :
?>
<select name="<?php echo $option_name; ?>">
	<option value="0"><?php _e( 'unset', 'ps-taxonomy-expander' ); ?></option>
	<?php foreach ( $terms as $term ) : ?>
	<option value="<?php echo esc_attr( $term->term_id ); ?>"
	<?php echo $term->term_id == $default_term ? ' selected="selected"' : ''; ?>><?php echo esc_html( $term->name ); ?></option>
	<?php endforeach; ?>
</select>
<?php else: ?>
<p><?php printf( __( '%s is not registerd.', 'ps-taxonomy-expander' ), esc_html( $args['taxonomy']->labels->singular_name ) ,esc_html( $args['taxonomy']->labels->name ) ); ?></p>
<?php endif;
}


function single_taxonomies_filed() {
	$taxonomies = get_taxonomies( array( 'hierarchical' => true, 'public' => true, 'show_ui' => true ), false );
?>

<p><?php _e( 'check the taxonomy box to turn into single selection.', 'ps-taxonomy-expander' ) ?></p>
<ul>
<?php foreach ( $taxonomies as $key => $obj ) :
$label = $obj->_builtin ? __( $obj->label ) : $obj->label;
$checked = $this->single_taxonomies && in_array( $obj->name, $this->single_taxonomies ) ? ' checked="checked"' : '';
?>
	<li><input type="checkbox" name="single_taxonomies[]"
		id="single_taxonomies_<?php echo esc_attr( $obj->name ); ?>"
		value="<?php echo esc_html( $obj->name );?>" <?php echo $checked; ?> />
	<label for="single_taxonomies_<?php echo esc_attr( $obj->name ); ?>"><?php echo esc_html( $label ); ?></label></li>
	<?php endforeach; ?>
</ul>
<?php
}


function check_single_taxonomies_postdata() {
	$taxonomies = get_taxonomies( array( 'hierarchical' => true, 'public' => true, 'show_ui' => true ) );
	if ( isset( $_POST['single_taxonomies'] ) ) {
		if ( is_array( $_POST['single_taxonomies'] ) ) {
			foreach ( $_POST['single_taxonomies'] as $key => $val ) {
				$val = maybe_unserialize( $val );
				if ( ! in_array( $val, array_keys( $taxonomies ) ) ) {
					unset( $_POST['single_taxonomies'][$key] );
				}
			}
		} else {
			unset( $_POST['single_taxonomies'] );
		}
	}
}


function allow_default_term_setting( $whitelist_options ) {
	$post_types = get_post_types( array( 'public' => true, 'show_ui' => true ), false );
	if ( $post_types ) {
		foreach ( $post_types as $post_type_slug => $post_type ) {
			$post_type_taxonomies = get_object_taxonomies( $post_type_slug, false );
			if ( $post_type_taxonomies ) {
				foreach ( $post_type_taxonomies as $tax_slug => $taxonomy ) {
					if ( ! ( $post_type_slug == 'post' && $tax_slug == 'category' ) && $taxonomy->show_ui ) {
						$whitelist_options['writing'][] = $post_type_slug . '_default_' . $tax_slug;
					}
				}
			}
		}
	}
	$media_taxonomies = get_object_taxonomies( 'attachment', false );
	if ( count( $media_taxonomies ) ) {
		foreach ( $media_taxonomies as $tax_slug => $taxonomy ) {
			if ( $taxonomy->show_ui ) {
				$whitelist_options['writing'][] = 'attachment_default_' . $tax_slug;
			}
		}
	}
	$whitelist_options['writing'][] = 'single_taxonomies';
	return $whitelist_options;
}


function add_post_type_default_term( $post_id, $post = null ) {
	if ( is_null( $post ) ) {
		$post_id = (int)$post_id;
		$post = get_post( $post_id );
	}

	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || $post->post_status == 'auto-draft' || $post->post_type == 'revision' ) { return; }
	$taxonomies = get_object_taxonomies( $post->post_type, false );
	if ( $taxonomies ) {
		foreach ( $taxonomies as $tax_slug => $taxonomy ) {
			$default = get_option( $post->post_type . '_default_' . $tax_slug );
			if ( ! ( $post->post_type == 'post' && $tax_slug == 'category' ) && $taxonomy->show_ui && $default && ! ( $terms = get_the_terms( $post_id, $tax_slug ) ) ) {
				if ( $taxonomy->hierarchical ) {
					$term = get_term( $default, $tax_slug );
					if ( $term ) {
						wp_set_post_terms( $post_id, array_filter( array( $default ) ), $tax_slug );
					}
				} else {
					$term = get_term( $default, $tax_slug );
					if ( $term ) {
						wp_set_post_terms( $post_id, $term->name, $tax_slug );
					}
				}
			}
		}
	}
}


function add_media_taxonomy_menu() {
	global $wp_taxonomies, $submenu;

	$media_taxonomies = array();
	if ( $wp_taxonomies ) {
		foreach ( $wp_taxonomies as $key => $obj ) {
			if ( count( $obj->object_type ) == 1 && $obj->object_type[0] == 'attachment' && $obj->show_ui ) {
				$media_taxonomies[$key] = $obj;
			}
		}
	}

	if ( $media_taxonomies ) {
		$priority = 50;
		foreach ( $media_taxonomies as $key => $media_taxonomy ) {
			if ( current_user_can( $media_taxonomy->cap->manage_terms ) ) {
				$submenu['upload.php'][$priority] = array( $media_taxonomy->labels->menu_name, 'upload_files', 'edit-tags.php?taxonomy=' . $key );
				$priority += 5;
			}
		}
	}
}


function replace_attachement_taxonomy_input_to_check( $form_fields, $post ) {
	if ( $form_fields ) {
		foreach ( $form_fields as $taxonomy => $obj ) {
			if ( isset( $obj['hierarchical'] ) && $obj['hierarchical'] ) {
				$terms = get_terms( $taxonomy, array( 'get' => 'all' ) );
				$taxonomy_tree = array();
				$branches = array();
				$term_id_arr = array();

				foreach( $terms as $term ) {
					$term_id_arr[$term->term_id] = $term;
					if ( $term->parent == 0 ) {
						$taxonomy_tree[$term->term_id] = array();
					} else {
						$branches[$term->parent][$term->term_id] = array();
					}
				}

				if ( count( $branches ) ) {
					foreach( $branches as $foundation => $branch ) {
						foreach( $branches as $branche_key => $val ) {
							if ( array_key_exists( $foundation, $val ) ) {
								$branches[$branche_key][$foundation] = &$branches[$foundation];
								break 1;
							}
						}
					}

					foreach ( $branches as $foundation => $branch ) {
						if ( isset( $taxonomy_tree[$foundation] ) ) {
							$taxonomy_tree[$foundation] = $branch;
						}
					}
				}

				$html = $this->walker_media_taxonomy_html( $post->ID, $taxonomy, $term_id_arr, $taxonomy_tree );
				if ( $terms ) {
					$form_fields[$taxonomy]['input'] = 'checkbox';
					$form_fields[$taxonomy]['checkbox'] = $html;
				} else {
					$form_fields[$taxonomy]['input'] = 'html';
					$form_fields[$taxonomy]['html'] = sprintf( __( '%s is not registerd.', 'ps-taxonomy-expander' ), esc_html( $obj['labels']->singular_name ), esc_html( $obj['labels']->name ) );
				}
			}
		}
	}
	return $form_fields;
}


function walker_media_taxonomy_html( $post_id, $taxonomy,  $term_id_arr, $taxonomy_tree, $html = '', $cnt = 0 ) {
	foreach ( $taxonomy_tree as $term_id => $arr ) {
		$checked = is_object_in_term( $post_id, $taxonomy, $term_id ) ? ' checked="checked"' : '';
		$type = in_array( $taxonomy, $this->single_taxonomies ) ? 'radio' : 'checkbox';
		$html .= str_repeat( '—', count( get_ancestors( $term_id, $taxonomy ) ) );
		$html .= ' <input type="' . $type . '" id="attachments[' . $post_id . '][' . $taxonomy . ']-' . $cnt . '" name="attachments[' . $post_id . '][' . $taxonomy . '][]" value="' . esc_attr( $term_id_arr[$term_id]->name ) . '"' . $checked . ' /><label for="attachments[' . $post_id . '][' . $taxonomy . ']-' . $cnt . '">' . esc_html( $term_id_arr[$term_id]->name ) . "</label><br />\n";
		$cnt++;
		if ( count( $arr ) ) {
			$html = $this->walker_media_taxonomy_html( $post_id, $taxonomy, $term_id_arr, $arr, $html, &$cnt );
		}
	}
	return $html;
}


function join_media_taxonomy_datas() {
	global $wp_taxonomies;

	if ( ! isset( $_POST['attachments'] ) ) { return; }
	check_admin_referer('media-form');

	$media_taxonomies = array();
	if ( $wp_taxonomies ) {
		foreach ( $wp_taxonomies as $key => $obj ) {
			if ( count( $obj->object_type ) == 1 && $obj->object_type[0] == 'attachment' ) {
				$media_taxonomies[$key] = $obj;
			}
		}
	}

	if ( $media_taxonomies ) {
		foreach ( $media_taxonomies as $key => $media_taxonomy ) {
			foreach ( $_POST['attachments'] as $attachment_id => $post_val ) {
				if ( isset( $_POST['attachments'][$attachment_id][$key] ) ) {
					if ( is_array( $_POST['attachments'][$attachment_id][$key] ) ) {
						$_POST['attachments'][$attachment_id][$key] = implode( ', ', $_POST['attachments'][$attachment_id][$key] );
					}
				} else {
					$_POST['attachments'][$attachment_id][$key] = '';
				}
			}
		}
	}
}


function display_taxonomy_post_count() {
	$user = wp_get_current_user();
	if ( isset( $user->disp_tax_right_now ) && $user->disp_tax_right_now ) {
		$taxonomies = get_taxonomies( array( 'public' => true, 'show_ui' => true, '_builtin' => false ), false );
		if ( count( $taxonomies ) ) {
			foreach ( $taxonomies as $tax_slug => $taxonomy ) {
				$num = wp_count_terms( $tax_slug );
				// Ummm....
				$text = $num == 1 ? $taxonomy->labels->singular_name : $taxonomy->labels->name;
				$num = number_format_i18n( $num );
				$text = esc_html( $text );
				if ( current_user_can( $taxonomy->cap->manage_terms ) ) {
					$num = '<a href="edit-tags.php?taxonomy=' . $tax_slug . '">' . $num . '</a>';
					$text = '<a href="edit-tags.php?taxonomy=' . $tax_slug . '">' . $text . '</a>';
				}
?>
<tr>
	<td class="b b-<?php echo esc_attr( $tax_slug ); ?>"><a><?php echo $num; ?></a></td>
	<td class="last t"><a><?php echo $text; ?></a></td>
</tr>
<?php
			}
		}
	}
}


function add_taxonomy_count_dashboard_right_now_field() {
	global $profileuser;
	$taxonomies = get_taxonomies( array( 'public' => true, 'show_ui' => true, '_builtin' => false ) );
	if ( count( $taxonomies ) ) {
		?>
<tr>
	<th scope="row"><?php _e( 'Add taxonomies on Right Now', 'ps-taxonomy-expander' ) ?></th>
	<td>
		<label for="disp_tax_right_now"> <input type="checkbox"
		name="disp_tax_right_now" id="disp_tax_right_now" value="1"
		<?php if ( $profileuser->disp_tax_right_now ) : ?> checked="checked"
		<?php endif; ?> /> <?php _e( 'Display taxonomies on Right Now in the Dashboard.', 'ps-taxonomy-expander' ) ?>
		</label>
	</td>
</tr>
	<?php
	}
}


function update_taxonomy_count_dashboard_right_now( $user_id, $old_user_data ) {
	$taxonomies = get_taxonomies( array( 'public' => true, 'show_ui' => true, '_builtin' => false ) );
	if ( count( $taxonomies ) ) {
		if ( isset( $_POST['disp_tax_right_now'] ) && ( ! isset( $old_user_data->disp_tax_right_now ) || ! $old_user_data->disp_tax_right_now ) ) {
			update_user_meta( $user_id, 'disp_tax_right_now', 1 );
		} else {
			update_user_meta( $user_id, 'disp_tax_right_now', 0 );
		}
	}
}


function add_taxonomy_order_menu() {
	$hook = add_object_page( __( 'Term order', 'ps-taxonomy-expander' ), __( 'Term order', 'ps-taxonomy-expander' ), 'manage_categories', basename( __FILE__ ), array( &$this, 'term_order_page' ) );
	add_action( 'admin_print_styles-' . $hook, array( &$this, 'term_order_style' ) );
	add_action( 'admin_print_scripts-' . $hook, array( &$this, 'term_order_scripts' ) );
}


function plugin_term_order_links( $links, $file ) {
	$this_plugin = plugin_basename(__FILE__);
	if ( $file == $this_plugin ) {
		$link = trailingslashit( get_bloginfo( 'wpurl' ) ) . 'wp-admin/admin.php?page=' . basename( __FILE__ );
		$term_order_link = '<a href="' . $link . '">' . __( 'Term order', 'ps-taxonomy-expander' ) . '</a>';
		array_unshift( $links, $term_order_link ); // before other links
		$link = trailingslashit( get_bloginfo( 'wpurl' ) ) . 'wp-admin/options-writing.php';
		$tax_regist_link = '<a href="' . $link . '">' . __( 'Option to register taxonomies', 'ps-taxonomy-expander' ) . '</a>';
		array_unshift( $links, $tax_regist_link ); // before other links
	}
	return $links;
}


function term_order_page( $taxonomy ) {
	global $wpdb;

	$check = $wpdb->query( "SHOW COLUMNS FROM $wpdb->terms LIKE 'term_order'" );
	if ( $check == 0 ) {
		$wpdb->query( "ALTER TABLE $wpdb->terms ADD `term_order` INT( 4 ) NULL DEFAULT '0'" );
	}

	$update_message = '';
	if ( isset( $_POST['term_order_update'] ) && $_POST['term_order_update'] ) {
		check_admin_referer( 'term_order' );
		$post_data = stripslashes_deep( $_POST );
		$tax_order = explode( ',', $post_data['term_order'] );
		if ( ! empty( $tax_order ) ) {
			$order = 0;
			$affected = 0;
			foreach ( $tax_order as $tax_id ) {
				$affected += $wpdb->update( $wpdb->terms, array( 'term_order' => $order ), array( 'term_id' => $tax_id ) );
				$order++;
			}

			if ( $affected == 0 ) {
				$update_message =  '<div id="message" class="updated fade"><p>'. __( 'Non Updated', 'ps-taxonomy-expander' ).'</p></div>';
			} else {
				$update_message =  '<div id="message" class="updated fade"><p>'. __( 'Updated successfully.', 'ps-taxonomy-expander' ).'</p></div>';
			}
		}
	}

	$taxonomies = get_taxonomies( array( 'public' => true, 'hierarchical' => true, 'show_ui' => true ), false );

	if ( isset( $_GET['taxonomy'] ) && taxonomy_exists( $_GET['taxonomy'] ) ) {
		$this->current_taxonomy = get_taxonomy( $_GET['taxonomy'] );
	} else {
		$this->current_taxonomy = get_taxonomy( 'category' );
	}
	$parent = isset( $_GET['parent'] ) ? (int)$_GET['parent'] : 0;
	if ( $parent ) {
		$parent_term = get_term( $parent, $this->current_taxonomy->name );
	}
	$have_children = $wpdb->get_results( "SELECT t.term_id, t.name FROM $wpdb->term_taxonomy tt, $wpdb->terms t, $wpdb->term_taxonomy tt2 WHERE tt.parent = $parent AND tt.taxonomy = '{$this->current_taxonomy->name}' AND t.term_id = tt.term_id AND tt2.parent = tt.term_id GROUP BY t.term_id, t.name HAVING COUNT(*) > 0 ORDER BY t.term_order ASC" );
?>
<div class="wrap"><?php screen_icon( 'term-order' ); ?>
<h2><?php _e( 'Term order', 'ps-taxonomy-expander' ); ?></h2>
<ul id="taxonomies_tab">
<?php if ( ! empty( $taxonomies ) ) : foreach ( $taxonomies as $tax_slug => $taxonomy ) :
$link = $tax_slug == 'category' ? remove_query_arg( array( 'taxonomy', 'parent' ) ) : add_query_arg( array( 'taxonomy' => $tax_slug ), remove_query_arg( 'parent' ) );
if ( $this->current_taxonomy->name == $tax_slug ) :
?>
	<li><strong><?php echo esc_html( $taxonomy->label ); ?></strong></li>
	<?php else : ?>
	<li><a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $taxonomy->label ); ?></a></li>
	<?php endif; ?>
	<?php endforeach; endif; ?>
</ul>
	<?php
	echo $update_message;
	$terms = $wpdb->get_results( "SELECT DISTINCT t.term_id, name FROM $wpdb->term_taxonomy tt inner join $wpdb->terms t on t.term_id = tt.term_id where tt.taxonomy = '{$this->current_taxonomy->name}' AND tt.parent = $parent ORDER BY t.term_order ASC" );
	?>
<h3><?php echo esc_html( $this->current_taxonomy->label ); ?></h3>
	<?php if ( ! empty( $have_children ) ) : ?>
<h4><?php printf( __( 'Sub %s', 'ps-taxonomy-expander' ), esc_html( $this->current_taxonomy->labels->singular_name ) ); ?></h4>
<select id="child_terms">
<?php foreach ( $have_children as $have_child ) : ?>
	<option
		value="<?php echo esc_url( add_query_arg( array( 'parent' => $have_child->term_id ) ) ); ?>"><?php echo esc_html( $have_child->name ); ?></option>
		<?php endforeach; ?>
</select> <input type="button"
	value="<?php printf( __( 'Move to child %s', 'ps-taxonomy-expander' ), esc_attr( $this->current_taxonomy->labels->singular_name ) ) ?>"
	onClick="locationChildTerms();" /> <?php endif; ?> <?php if ( $parent != 0 ) : ?>
<a href="<?php echo esc_url( remove_query_arg( 'parent' ) ); ?>"
	class="button"><?php printf( __( 'Back to top %s', 'ps-taxonomy-expander' ), esc_html( $this->current_taxonomy->labels->singular_name ) ); ?></a>
		<?php if ( $parent_term->parent ) : ?> <a
	href="<?php echo esc_url( add_query_arg( array( 'parent' => $parent_term->parent ) ) ); ?>"
	class="button"><?php printf( __( 'Back to parent %s', 'ps-taxonomy-expander' ), esc_html( $this->current_taxonomy->labels->singular_name ) ); ?></a>
		<?php endif; endif; ?>
<h4><?php printf( __( '%s order', 'ps-taxonomy-expander' ), esc_html( $this->current_taxonomy->labels->singular_name ) ); ?></h4>
<ul id="term_order_list"
	style="width: 45%; margin: 10px 10px 10px 0px; padding: 10px; border: 1px solid #B2B2B2; list-style: none;">
	<?php foreach ( $terms as $term ) : ?>
	<li id="<?php echo esc_attr( $term->term_id ); ?>" class="lineitem"><?php echo esc_html( $term->name ); ?></li>
	<?php endforeach; ?>
</ul>
<form action="" method="post"><?php wp_nonce_field( 'term_order' ); ?> <input
	type="hidden" id="term_order" name="term_order" /> <input type="hidden"
	id="term_parent_id" name="term_parent_id"
	value="<?php echo esc_html( $parent ); ?>" /> <input type="submit"
	name="term_order_update" value="<?php _e( 'Save Changes' ); ?>"
	onclick="javascript:orderTerm(); return true;" class="button-primary" />
</form>
<div id="developper_information"><a
	href="http://www.prime-strategy.co.jp" target="_blank" id="poweredby">
<img
	src="<?php echo esc_url( preg_replace( '/^https?:/', '', plugin_dir_url( __FILE__ ) ) . 'images/ps_logo.png' ) ?>"
	alt="Powered by Prime Strategy" /> </a></div>
</div>
	<?php
	}


	function term_order_style() {
		$url = preg_replace( '/^https?:/', '', plugin_dir_url( __FILE__ ) ) . 'images/icon32.png';
?>
<style type="text/css" charset="utf-8">
#icon-term-order {
	background: url(<?php echo esc_url($url); ?>) no-repeat	center;
}

#developper_information {
	margin: 20px 30px 10px;
	text-align: right;
}

#developper_information .content {
	padding: 10px 20px;
}

#poweredby {
	
}

li.lineitem {
	margin: 3px 0px;
	padding: 2px 5px 2px 5px;
	background-color: #F1F1F1;
	border: 1px solid #B2B2B2;
	cursor: move;
}

#taxonomies_tab {
	margin-top: 20px;
	border-bottom: solid 1px #999;
}

#taxonomies_tab li {
	display: inline-block;
	margin: 0;
}

#taxonomies_tab li a,#taxonomies_tab li strong {
	position: relative;
	bottom: -1px;
	display: inline-block;
	border: solid 1px #999;
	padding: 3px 10px;
	margin: 0 5px 0 0;
}

#taxonomies_tab li strong {
	border-bottom: solid 1px #fff;
}

#taxonomies_tab li a {
	background: #eee;
}
</style>
<?php
}


function term_order_scripts() {
?>
<script language="JavaScript" type="text/javascript">
	function taxOrderAddLoadEvent(){
		jQuery("#term_order_list").sortable({ 
			placeholder: "ui-selected", 
			revert: false,
			tolerance: "pointer" 
		});
	};

	addLoadEvent( taxOrderAddLoadEvent );

	function orderTerm() {
		jQuery("#term_order").val(jQuery("#term_order_list").sortable("toArray"));
	}
	
	function locationChildTerms() {
		var childSelect = document.getElementById( 'child_terms' );
		document.location.href = childSelect.options[childSelect.selectedIndex].value;
	}
</script>
<?php
}


function add_jquery_sortable() {
	if ( isset( $_GET['page'] ) && $_GET['page'] == basename( __FILE__ ) ) {
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-ui-sortable');
	}
}


function term_orderby_order( $orderby, $args ) {
	if ( strtolower( $args['orderby'] ) == 'order' ) {
		$orderby = 't.term_order';
	}
	return $orderby;
}


function get_tax_columns() {
	if ( !isset( $_REQUEST['post_type'] ) ) {
		$this->edit_post_type = 'post';
	} elseif ( in_array( $_REQUEST['post_type'], get_post_types( array('show_ui' => true ) ) ) ) {
		$this->edit_post_type = $_REQUEST['post_type'];
	} else {
		wp_die( __('Invalid post type') );
	}

	$_GET['post_type'] = $this->edit_post_type;
	$_POST['post_type'] = $this->edit_post_type;
	

	$taxonomies = get_object_taxonomies( $this->edit_post_type, 'object' );
	if ( ! empty( $taxonomies ) ) {
		$this->add_tax_columns = array();
		foreach ( $taxonomies as $tax_slug => $taxonomy ) {
			if ( ! in_array( $tax_slug, array( 'category', 'post_tag' ) ) && $taxonomy->show_ui !== false && in_array( $tax_slug, $this->disp_taxonomies[$this->edit_post_type] ) ) {
				$this->add_tax_columns[$tax_slug] = $taxonomy;
			}
		}
		if ( ! empty( $this->add_tax_columns ) ) {
			add_filter( 'manage_' . $this->edit_post_type . '_posts_columns', array( &$this, 'add_tax_columns' ) );
		}
	}
}


function add_tax_columns( $posts_columns ) {
	if ( ! empty( $this->add_tax_columns ) ) {
		$new_columns = array();
		$add_flag = false;
		foreach ( $posts_columns as $column_name => $column_display_name ) {
			if ( in_array( $column_name, array( 'comments', 'date' ) ) && $add_flag === false ) {
				foreach ( $this->add_tax_columns as $tax_slug => $taxonomy ) {
					$new_columns[$tax_slug] = $taxonomy->labels->name;
					add_action( 'manage_' . $this->edit_post_type . '_posts_custom_column', array( &$this, 'display_taxonomy_column' ), 10, 2 );
				}
				$add_flag = true;
			}
			$new_columns[$column_name] = $column_display_name;
		}
		$posts_columns = $new_columns;
	}
	return $posts_columns;
}


function display_taxonomy_column( $column_name, $post_id ) {
	$term_links = array();
	if ( in_array( $column_name, array_keys( $this->add_tax_columns ) ) ) {
		$terms = get_the_terms( (int)$post_id, $column_name );
		if ( ! empty( $terms ) ) {
			$term_links = array();
			foreach ( $terms as $term ) {
				$url = add_query_arg( array( 'taxonomy' => $column_name, 'term' => $term->slug ), remove_query_arg( 'paged' ) );
				$term_links[] = '<a href="' . esc_url( $url ) . '">' . esc_html( $term->name ) . '</a>';
			}
		}
		if ( empty( $term_links ) ) {
			echo esc_html( sprintf( __( 'No %s', 'ps-taxonomy-expander' ), $this->add_tax_columns[$column_name]->labels->name ) );
		} else {
			echo implode( ', ', $term_links );
		}
	}
}


function add_tax_column_styles() {
	if ( ! empty( $this->add_tax_columns ) ) {
		$selectors = array();
		foreach ( $this->add_tax_columns as $tax_slug => $taxonomy ) {
			$selectors[] = '.fixed .column-' . esc_html( $tax_slug );
		}
?>
<style type="text/css" charset="utf-8">
<?php echo implode( ', ', $selectors ); ?> {
	width: 15%;
}
</style>
<?php
	}
}


function add_tax_column_settings() {
	$post_types = get_post_types( array( 'public' => true, 'show_ui' => true ), false );
	foreach ( $post_types as $post_slug => $post_type ) {
		$taxonomies = get_object_taxonomies( $post_slug, 'object' );
		if ( ! empty( $taxonomies ) ) {
			foreach ( $taxonomies as $tax_slug => $taxonomy ) {
				if ( ! in_array( $tax_slug, array( 'category', 'post_tag' ) ) && $taxonomy->show_ui !== false ) {
					add_settings_field( $post_slug . '_list_taxonomies', sprintf( __( 'Display on the list of %s', 'ps-taxonomy-expander' ), $post_type->labels->name ), array( &$this, 'display_taxonomy_fields' ), 'writing', 'default', array( 'post_type' => $post_slug ) );
				}
			}
		}
	}
}


function display_taxonomy_fields( $args ) {
	$taxonomies = get_object_taxonomies( $args['post_type'], 'object' );
	if ( ! empty( $taxonomies ) ) {
		foreach ( $taxonomies as $tax_slug => $taxonomy ) {
			if ( ! in_array( $tax_slug, array( 'category', 'post_tag' ) ) && $taxonomy->show_ui !== false ) {
				$checked = in_array( $tax_slug, $this->disp_taxonomies[$args['post_type']] ) ? ' checked="checked"' : '';
				echo '<label for="' . esc_attr( $args['post_type'] ) . '_list_taxonomies_' . esc_attr( $tax_slug ) . '"><input type="checkbox" id="' . esc_attr( $args['post_type'] ) . '_list_taxonomies_' . esc_attr( $tax_slug ) . '" name="' . esc_attr( $args['post_type'] ) . '_list_taxonomies[' . esc_attr( $tax_slug ) . ']" value="' . esc_attr( $tax_slug ) . '"' . $checked . ' /> ' . esc_html( $taxonomy->labels->name ) . '</label>';
			}
		}
	}
}


function allow_list_display_tax_setting( $whitelist_options ) {
	$post_types = get_post_types( array( 'public' => true, 'show_ui' => true ), false );
	foreach ( $post_types as $post_slug => $post_type ) {
		$taxonomies = get_object_taxonomies( $post_slug, 'object' );
		if ( ! empty( $taxonomies ) ) {
			foreach ( $taxonomies as $tax_slug => $taxonomy ) {
				if ( ! in_array( $tax_slug, array( 'category', 'post_tag' ) ) && $taxonomy->show_ui !== false ) {
					$whitelist_options['writing'][] = $post_slug . '_list_taxonomies';
					if ( ! isset( $_POST[$post_slug . '_list_taxonomies'] ) ) {
						$_POST[$post_slug . '_list_taxonomies'] = array();
					}
					break;
				}
			}
		}
	}
	return $whitelist_options;
}


function add_filter_tax_dropdown() {
	$taxonomies = get_object_taxonomies( $this->edit_post_type, 'object' );
	if ( ! empty( $taxonomies ) ) {
		foreach ( $taxonomies as $tax_slug => $taxonomy ) {
			if ( ! in_array( $tax_slug, array( 'category', 'post_tag' ) ) && $taxonomy->show_ui !== false && $taxonomy->hierarchical && in_array( $tax_slug, $this->disp_taxonomies[$this->edit_post_type] ) ) {
				$dropdown_options = array(
					'show_option_all' => sprintf( __( 'View all %s', 'ps-taxonomy-expander' ), $taxonomy->labels->name ),
					'hide_empty' => 0,
					'hierarchical' => 1,
					'show_count' => 0,
					'orderby' => 'name',
					'selected' => get_query_var( $tax_slug ),
					'taxonomy' => $tax_slug,
					'name' => $tax_slug
				);
				$this->dropdown_taxonomies( $dropdown_options );
			}
		}
	}
}


function dropdown_taxonomies( $args = '' ) {
	$defaults = array(
		'show_option_all' => '', 'show_option_none' => '',
		'orderby' => 'id', 'order' => 'ASC',
		'show_last_update' => 0, 'show_count' => 0,
		'hide_empty' => 1, 'child_of' => 0,
		'exclude' => '', 'echo' => 1,
		'selected' => 0, 'hierarchical' => 0,
		'name' => 'cat', 'id' => '',
		'class' => 'postform', 'depth' => 0,
		'tab_index' => 0, 'taxonomy' => 'category',
		'hide_if_empty' => false
	);

	$defaults['selected'] = ( is_category() ) ? get_query_var( 'cat' ) : 0;

	// Back compat.
	if ( isset( $args['type'] ) && 'link' == $args['type'] ) {
		_deprecated_argument( __FUNCTION__, '3.0', '' );
		$args['taxonomy'] = 'link_category';
	}

	$r = wp_parse_args( $args, $defaults );

	if ( !isset( $r['pad_counts'] ) && $r['show_count'] && $r['hierarchical'] ) {
		$r['pad_counts'] = true;
	}

	$r['include_last_update_time'] = $r['show_last_update'];
	extract( $r );

	$tab_index_attribute = '';
	if ( (int) $tab_index > 0 )
		$tab_index_attribute = " tabindex=\"$tab_index\"";

	$categories = get_terms( $taxonomy, $r );
	$name = esc_attr( $name );
	$class = esc_attr( $class );
	$id = $id ? esc_attr( $id ) : $name;

	if ( ! $r['hide_if_empty'] || ! empty($categories) )
		$output = '<select name="' . $name . '" id="' . $id . '" class="' . $class . '" ' . $tab_index_attribute . '>' . "\n";
	else
		$output = '';

	if ( empty($categories) && ! $r['hide_if_empty'] && !empty($show_option_none) ) {
		$show_option_none = apply_filters( 'list_cats', $show_option_none );
		$output .= "\t<option value='-1' selected='selected'>$show_option_none</option>\n";
	}

	if ( ! empty( $categories ) ) {

		if ( $show_option_all ) {
			$show_option_all = apply_filters( 'list_cats', $show_option_all );
			$selected = ( '' === strval($r['selected']) ) ? " selected='selected'" : '';
			$output .= "\t<option value=''$selected>$show_option_all</option>\n";
		}

		if ( $show_option_none ) {
			$show_option_none = apply_filters( 'list_cats', $show_option_none );
			$selected = ( '-1' === strval($r['selected']) ) ? " selected='selected'" : '';
			$output .= "\t<option value='-1'$selected>$show_option_none</option>\n";
		}

		if ( $hierarchical )
			$depth = $r['depth'];  // Walk the full depth.
		else
			$depth = -1; // Flat.

		$output .= $this->walk_taxonomy_dropdown_tree( $categories, $depth, $r );
	}
	if ( ! $r['hide_if_empty'] || ! empty($categories) )
		$output .= "</select>\n";


	$output = apply_filters( 'wp_dropdown_cats', $output );

	if ( $echo )
		echo $output;

	return $output;
}


function walk_taxonomy_dropdown_tree() {
	$args = func_get_args();
	// the user's options are the third parameter
	if ( empty($args[2]['walker']) || !is_a($args[2]['walker'], 'Walker') )
		$walker = new PS_TaxonomyDropdown;
	else
		$walker = $args[2]['walker'];

	return call_user_func_array(array( &$walker, 'walk' ), $args );
}


} // class end
$ps_taxonomy_expander = new PS_Taxonomy_Expander();

class PS_TaxonomyDropdown extends Walker_CategoryDropdown {
	function start_el( &$output, $term, $depth, $args ) {
		$pad = str_repeat('&nbsp;', $depth * 3);

		$term_name = apply_filters( 'list_cats', $term->name, $term );
		$output .= "\t<option class=\"level-$depth\" value=\"" . esc_attr( $term->slug ) . "\"";
		if ( $term->slug == $args['selected'] )
			$output .= ' selected="selected"';
		$output .= '>';
		$output .= $pad.esc_html( $term_name );
		if ( $args['show_count'] )
			$output .= '&nbsp;&nbsp;('. $term->count .')';
		if ( $args['show_last_update'] ) {
			$format = 'Y-m-d';
			$output .= '&nbsp;&nbsp;' . gmdate($format, $term->last_update_timestamp);
		}
		$output .= "</option>\n";
	}
}