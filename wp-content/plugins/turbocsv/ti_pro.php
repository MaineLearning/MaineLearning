<?php
class TI_Pro extends TI_Frontend {

	function view_template($import) {
		// Multisite
		if (is_multisite() && is_main_site()) {
			$this->postbox_start(__('Multisite'), 'multisite_settings');
			?>
			<table class="form-table">
				<?php $this->option($import, 'blog_id'); ?>
			</table>
			<?php
			$this->postbox_end();
		}

		// Normal installation
		?>
		<?php $this->postbox_start(__('Template'), 'layout_settings'); ?>
		<table class="form-table">
			<?php
				$this->option($import, 'template_load');
				$this->option($import, 'template_save');
			?>
		</table>
		<?php $this->postbox_end(); ?>

		<?php $this->postbox_start(__('Layout'), 'layout_settings'); ?>
		<table class="form-table">
			<?php
				$this->option($import, 'post_title');
				$this->option($import, 'post_content');
				$this->option($import, 'post_excerpt');
			?>
		</table>
		<?php $this->postbox_end(); ?>

		<?php $this->postbox_start(__('Post/Page Settings'), 'postpage_settings'); ?>
			<table class="form-table">
				<?php
					$this->option($import, 'post_type');
					$this->option($import, 'post_parent');
					$this->option($import, 'page_template');
					$this->option($import, 'menu_order');
					$this->option($import, 'post_status');
					$this->option($import, 'comment_status');
					$this->option($import, 'post_author');
					$this->option($import, 'post_name');
					$this->option($import, 'post_date');
				?>
			</table>
		<?php $this->postbox_end(); ?>

		<?php $this->postbox_start(__('Featured Images'), 'postpage_settings'); ?>
			<table class="form-table ti-form-table">
				<?php
					$this->option($import, 'thumbnail');
				?>
			</table>
		<?php $this->postbox_end(); ?>

		<?php $this->postbox_start(__('Updates'), 'postpage_settings'); ?>
			<table class="form-table ti-form-table">
				<?php
					$this->option($import, 'postid');
				?>
			</table>
		<?php $this->postbox_end(); ?>

		<?php $this->postbox_start(__('Categories, Tags and Taxonomies'), 'taxonomy_settings'); ?>
			<table class="form-table ti-form-table">
				<?php
					$this->option($import, 'taxonomies');
				?>
			</table>
		<?php $this->postbox_end(); ?>

		<?php $this->postbox_start(__('Custom Fields'), 'customfields_settings'); ?>
			<table class="form-table ti-form-table">
				<?php
					$this->option($import, 'post_custom');
				?>
			</table>
			<table class="form-table ti-form-table">
				<?php
					$this->option($import, 'post_custom_new');
				?>
			</table>
		<?php $this->postbox_end(); ?>
		<?php
	}

	function option($import, $field) {
		global $wpdb;

		switch ($field) {
			case 'template_load':
				$template_ids = TI_Template::get_list();
				if ($template_ids)
					$template_ids = array_combine($template_ids, $template_ids);

				if (isset($template_ids))
					$template_ids = array_merge(array('' => 'new template'), (array)$template_ids);
				else
					$template_ids = array('' => 'new template');

				$html = $this->dropdown('template_load_id', $import->template->_id, $template_ids)
					. "<input type='submit' class='button' name='button_load_template' value='" . __('Load') . "' />"
					. "<input type='submit' class='button' name='button_delete_template' value='" . __('Delete') . "' />";

				$this->field_setting('template_load', $html);
				break;

			case 'template_save':
				$html = "<input type='text' name='template_save_id' value='" . esc_attr($import->template->_id) . "' />"
					. "<input type='submit' class='button' name='button_save_template' value='" . __('Save') . "' />";

				$this->field_setting('template_save', $html);
				break;

			case 'blog_id':
				// Ignore this if it's not a multisite install
				if (!is_multisite())
					return;

				$query = "SELECT * FROM {$wpdb->blogs} ORDER BY {$wpdb->blogs}.site_id, {$wpdb->blogs}.blog_id ";
				$wp_blogs = $wpdb->get_results( $query, OBJECT );

				$blog_ids = array();
				foreach($wp_blogs as $wp_blog) {
					$key = $wp_blog->blog_id;
					$blog_ids[$key] = $wp_blog->path;
				}

				$dd_blog_ids = $this->dropdown('template[blog_id]', $import->template->blog_id, $blog_ids, true);
				$this->basic_option($import, 'template[blog_id_col]', $import->template->blog_id_col, $dd_blog_ids, 'blog_id');
				break;

			case 'post_title':
				foreach((array)$import->headers as $header => $token)
					$tokens[] = "<a href='#'>" . esc_attr($token) . "</a>";

				$html = __('Click on the title, body or excerpt field, then click on the column name to insert it') . "<br/>"
					. "<div id='ti_tokens'>" . implode(' | ', $tokens) . "</div>";
				$this->field_setting('', $html);

				$html = "<input id='post_title' class='ti-token-field ti-post-title' type='text' value='" . esc_attr($import->template->post_title) . "' name='template[post_title]'/>";
				$this->field_setting('post_title', $html);
				break;


			case 'post_content':
				$label = $this->labels['post_content']['label'];
				$tip = $this->labels['post_content']['tip'];
				echo "<tr><th scope='row'>";
				echo "<abbr style='cursor:help' class='ttip' title='" .esc_attr($tip) . "'>$label</abbr> ";
				echo "</th>";
				echo "<td style='vertical-align:top'>";

				if (function_exists('wp_editor'))
					wp_editor($import->template->post_content, 'ti_post_content', array('textarea_name' => 'template[post_content]', 'media_buttons' => false));
				else
					echo "<textarea id='ti_post-content' name='template[post_content]'>" . $import->template->post_content . "</textarea>";

				echo "</td>";
				echo "</tr>";
				break;

			case 'post_excerpt':
				$html = "<textarea id='post_excerpt' name='template[post_excerpt]' class='ti-token-field ti-post-excerpt'  rows='3' >"
					. esc_attr($import->template->post_excerpt)
					. "</textarea>";

				$this->field_setting('post_excerpt', $html);
				break;


			case 'post_type':
				$wp_post_types = get_post_types(array('public' => true, '_builtin' => false), 'names', 'and');
				$post_types = array('post' => 'post', 'page' => 'page');
				if (!empty($wp_post_types))
					$post_types = array_merge($post_types, array_combine($wp_post_types, $wp_post_types));
				$dd_post_types = $this->dropdown('template[post_type]', $import->template->post_type, $post_types, true);
				$this->basic_option($import, 'template[post_type_col]', $import->template->post_type_col, $dd_post_types, 'post_type');
				break;

			case 'post_parent':
				$dd_pages = wp_dropdown_pages(array('echo' => false,
					'name' => 'template[post_parent]',
					'selected' => $import->template->post_parent,
					'show_option_none' => __('Main Page (no parent)'),
					'sort_column'=> 'menu_order, post_title',
					'show_option_none' => ' ',
					'show_option_none_value' => ''
				));

				$this->basic_option($import, 'template[post_parent_col]', $import->template->post_parent_col, $dd_pages, 'post_parent');
				break;

			case 'page_template':
				$wp_templates = get_page_templates();
				$templates = array();

				// WP returns the templates as name => key, transpose to key => name and put the key in parentheses
				foreach($wp_templates as $value => $key)
					$templates[$key] = $value . " ($key)";

				asort($templates);

				// Insert a 'default' entry
				$templates = array_merge(array('default' => 'Default Template (default)'), $templates);

				$dd_templates = $this->dropdown('template[page_template]', $import->template->page_template, $templates, true);
				$this->basic_option($import, 'template[page_template_col]', $import->template->page_template_col, $dd_templates, 'page_template');
				break;

			case 'menu_order':
				$orders = array_combine(range("1", "99"), range("1", "99"));
				$dd_order = $this->dropdown('template[menu_order]', (int)$import->template->menu_order, $orders, true);
				$this->basic_option($import, 'template[menu_order_col]', $import->template->menu_order_col, $dd_order, 'menu_order');
				break;


			case 'post_status':
				$dd_statuses = $this->dropdown('template[post_status]', $import->template->post_status, array('publish'=>'Published', 'draft'=>'Draft', 'pending'=>'Pending Review'), true);
				$this->basic_option($import, 'template[post_status_col]', $import->template->post_status_col, $dd_statuses, 'post_status');
				break;

			case 'comment_status':
				$dd_statuses = $this->dropdown('template[comment_status]', $import->template->comment_status, array('open'=>'open', 'closed'=>'closed'), true);
				$this->basic_option($import, 'template[comment_status_col]', $import->template->comment_status_col, $dd_statuses, 'comment_status');
				break;

			case 'post_author':
				$args = array('orderby' => 'display_name', 'order' => 'ASC', 'blog_id' => $GLOBALS['blog_id'], 'who' => '');
				$user_objs = get_users( $args );
				foreach ($user_objs as $user)
					$users[$user->ID] = $user->user_login . " (" . $user->ID . ")";

				$dd_users = $this->dropdown('template[post_author]', (int) $import->template->post_author, $users, true);
				$this->basic_option($import, 'template[post_author_col]', $import->template->post_author_col, $dd_users, 'post_author');
				break;

			case 'post_name':
				$slug = "<input type='text' name='template[post_name]' value='" . esc_attr($import->template->post_name) . "' size='10' />";
				$this->basic_option($import, 'template[post_name_col]', $import->template->post_name_col, $slug, 'post_name');
				break;

			case 'post_date':
				$dates = "<input type='text' name='template[post_date_min]' value='" . esc_attr($import->template->post_date_min) . "' size='10' />"
					. " to <input type='text' name='template[post_date_max]' value='" . esc_attr($import->template->post_date_max) . "' size='10' />";
				$this->basic_option($import, 'template[post_date_col]', $import->template->post_date_col, $dates, 'post_date');
				break;

			case 'thumbnail':
				$html = $this->dropdown_headers($import, 'template[thumbnail_col]', $import->template->thumbnail_col);
				$this->field_setting('thumbnail', $html);
				break;

			case 'postid':
				// Input column
				$html = $this->dropdown_headers($import, 'template[postid_col]', $import->template->postid_col);
				$html .= ' ' . __('search in') . ': ';

				// Matching WordPress field
				$meta_keys = $this->get_meta_keys();
				$dd = array('' => 'Post ID');
				foreach($meta_keys as $meta_key)
					$dd[$meta_key] = $meta_key;
				$html .= $this->dropdown('template[update_field]', $import->template->update_field, $dd, false);

				$this->field_setting('postid', $html);
				break;

			case 'taxonomies':
				// Output custom taxonomies
				$taxonomies = array_merge(array('category', 'post_tag'), get_taxonomies(array('_builtin' => false)));
				foreach ($taxonomies as $taxonomy_name) {
					$max_terms = 200;   // MAX # terms to return - some unusual blogs have many thousands
					$wp_taxonomy = get_taxonomy($taxonomy_name);

					// If the taxonomy no longer exists in WordPress (i.e. it was deleted), then just skip it
					if (!$wp_taxonomy)
						continue;

					// If the taxonomy is not yet part of the template, add it
					if (!isset($import->template->taxonomies[$taxonomy_name]))
						$import->template->taxonomies[$taxonomy_name] = array('col' => null, 'values' => array());

					// Get all the terms in the taxonomy (up to the maximum #)
					$wp_terms = get_terms($taxonomy_name, array('number' => $max_terms, 'hide_empty' => false, 'orderby' => 'id'));

					// Add a hierarchical description (with dashes) and sort by the 'slug'
					$terms = $this->get_term_list($wp_terms);
					$terms_dd = array();
					foreach($terms as $term_id => $term)
						$terms_dd[$term_id] = $term['description'];

					$values = isset($import->template->taxonomies[$taxonomy_name]['values']) ? $import->template->taxonomies[$taxonomy_name]['values'] : array();
					$col = isset($import->template->taxonomies[$taxonomy_name]['col']) ? $import->template->taxonomies[$taxonomy_name]['col'] : null;

					$dd_terms = $this->dropdown("template[taxonomies][$taxonomy_name][values][]", $values, $terms_dd, false, true);
					$this->basic_option($import, "template[taxonomies][$taxonomy_name][col]", $col, $dd_terms, 'taxonomies', $wp_taxonomy->labels->name);
				}
				break;

			case 'post_custom':
				$meta_keys = $this->get_meta_keys();
				$meta_keys_data = ($meta_keys && is_array($meta_keys)) ? array_combine(array_values($meta_keys), array_values($meta_keys)) : array();
				$columns_data = ($meta_keys && is_array($meta_keys)) ? array_combine(array_values($import->headers), array_values($import->headers)) : array();

				// Add a blank row for new entries
				$custom_keys = ($import->template->post_custom) ? array_merge($import->template->post_custom, array('' => '')) : array('' => '');

				$html = "<table class='edt'><tbody>";
				foreach ($custom_keys as $key => $col) {
					$meta_keys_dd = $this->dropdown("template[post_custom][key][]", $key, $meta_keys_data, true);
					$columns_dd =  $this->dropdown("template[post_custom][col][]", $col, $columns_data, true);
					$html .= "<tr><td>";
					$html .= $columns_dd . ' ' . __('is mapped to') . ': ' . $meta_keys_dd;
					$html .="</td></tr>";
				}
				$html .= "</tbody></table>";

				$this->setting(__('Existing custom fields'), $html, $this->labels['post_custom']['tip']);
				break;

			case 'post_custom_new':
				$existing_metas = $this->get_meta_keys();
				$new_metas = array();

				// 'New' custom fields are any headers that are not already mapped and that don't begin with '!'
				foreach ($import->headers as $key => $header) {
					if (!in_array($header, (array)$existing_metas) && substr($header, 0, 1) != '!')
						$new_metas[] = $header;
				}

				// If there are no new custom fields then don't show this section
				if (count($new_metas) == 0) {
					break;
				}

				// The table contains a list of column names; convert it to a table of column name => column name
				$new_metas = array_combine(array_values($new_metas), array_values($new_metas));

				$dd_custom_new = $this->dropdown("template[post_custom_new][]", $import->template->post_custom_new, $new_metas, false, true);
				$this->setting(__('Create new custom fields'), $dd_custom_new, $this->labels['post_custom_new']['tip']);
				break;

		}
	}

	function basic_option($import, $column_field, $select, $defaults, $tip_field, $label=null) {
		$html = $this->dropdown_headers($import, $column_field, $select);
		$html .= " " . __('default') . ": " . $defaults;
		$this->field_setting($tip_field, $html, $label);
	}

	/**
	* Dropdown box listing input file headers
	*
	* @param mixed $name
	* @param mixed $select
	* @param mixed $headers
	* @param mixed $none
	*/
	function dropdown_headers($import, $name, $select, $none=true) {
		$headers_data = array_combine(array_values($import->headers), array_values($import->headers));
		return $this->dropdown($name, $select, $headers_data, true);
	}

	/**
	* Used for printing hierarchical taxonomies.
	* Takes a list of terms and creates a new array of term_id => (term_slug, term_description)
	* The slug is a concatenation of all parent term names.  Description shows parents replaced by '-'
	* The result is sorted by slug.
	*
	* @param mixed $terms - array of all terms in the taxonomy (as objects)
	*/
	function get_term_list($terms) {
		$result = array();
		foreach($terms as $key=>$term) {
			$result[$term->term_id] = $this->get_term_list_item($terms, $term);
		}

		// Sory by the result array by slug
		uasort($result, array($this, 'sort_term_list'));
		return $result;
		}

	function get_term_list_item($terms, $current_term=null, $level=0) {
		$result = array('description' => '', 'slug' => '');

		if ($current_term->parent) {
			foreach ($terms as $key=>$parent) {
				if ($parent->term_id == $current_term->parent) {
					$result = $this->get_term_list_item($terms, $parent, $level + 1);
					break;
				}
			}
		}

		$result['description'] .= ($level == 0) ? $current_term->name : '&#8212; ';
		$result['slug'] .= $current_term->name;
		return $result;
	}

	/**
	* Sort an array of terms by field ti_slug (a hierarchical slug)
	*
	*/
	function sort_term_list($terma, $termb) {
		if ($terma['slug'] == $termb['slug'])
			return 0;
		if ($terma['slug'] > $termb['slug'])
			return 1;
		return -1;
	}

	/**
	* Undo an entire import.  All newly-created posts and associated comments are deleted.
	* Any categories and tags that were created during the import are also
	* deleted, but only if they have no reference count once the posts/comments are gone.
	*
	* For posts that were updated (rather than created), the last revision is restored.
	* If no revisions are available then the post is deleted.
	*
	* The import profile is retained and is not deleted, but the status is set to 'UNDO'.
	*
	* The instance will update itself back to the options database.
	*/
	function undo($id) {
		global $current_user, $blog_id;

		$original_blog_id = $blog_id;

		$import = TI_Import::get($id);
		if ($import === false)
			return new WP_Error('ERROR', sprintf(__('Unable to read import id %s for undo'), $id));

		get_currentuserinfo();
		$import->log(__("Undo started by: $current_user->user_login"), 'INFO');
		$import->import_start();

		foreach ((array)$import->get_imported_posts() as $post) {
			// Switch blogs if needed
			if (is_multisite() && isset($post['blog_id']) && $post['blog_id'] != $blog_id)
				switch_to_blog($post['blog_id']);

			// If post was updated during import, and revisions is on, then try to roll back to previous version
			if (defined('WP_POST_REVISIONS') && WP_POST_REVISIONS && isset($post['updated']) && $post['updated'] && isset($post['revision_id'])) {
				$result = wp_restore_post_revision( $post['revision_id'] );
				if (is_wp_error($result))
					$import->log(sprintf(__("Unable to restore original version of post %s (%s): %s"), $post['post_title'], $post['post_id'], $result->get_error_message() ));
				if (!$result)
					$import->log(sprintf(__("Unable to restore original version of post %s (%s)"), $post['post_title'], $post['post_id']));
			} else {
				// If no revisions, or post was created during import, then delete it
				$result = wp_delete_post($post['post_id']);
				if (is_wp_error($result))
					$import->log(sprintf(__("Error deleting post %s (%s): %s"), $post['post_title'], $post['post_id'], $result->get_error_message() ));
			}
		}

		// NOTE: counts seem to be incorrect for custom taxonomies - they include deleted posts - see wordpress trac #14084, #14073, #14392
		// Delete tags, categories and custom taxonomies
		foreach ((array)$import->imported_terms as $taxonomy => $terms) {
			foreach ($terms as $term) {
				// Switch blogs if needed
				if (is_multisite() && isset($term->blog_id) && $term->blog_id != $blog_id)
					switch_to_blog($term->blog_id);

				// Get current name in case it's changed
				$wp_term = get_term($term->term_id, $taxonomy);
				if (!is_wp_error($wp_term) && $wp_term)  // get_term() returns either wp_error or null
					$term->name = $wp_term->name;

				// Term doesn't exist
				if (!$wp_term || is_wp_error($wp_term)) {
					$import->log(sprintf(__('Could not delete term "%s" in taxonomy "%s" because it no longer exists'), $term->name, $taxonomy), 'WARNING');
					continue;
				}

				// Term still in use
				if ($wp_term->count > 0) {
					$import->log(sprintf(__('Term "%s" in taxonomy "%s" was not deleted because it is still in use'), $term->name, $taxonomy), 'WARNING');
					continue;
				}

				// Delete term
				$result = wp_delete_term($term->term_id, $taxonomy);
				if (!$result)
					$import->log(sprintf(__('Uknown error deleting term "%s" in taxonomy "%s"'), $term->name, $taxonomy), 'ERROR');

				if (is_wp_error($result))
					$import->log(sprintf(__('Error deleting term "%s" in taxonomy "%s" : "%s"'), $term->name, $taxonomy, $result->get_error_message() ), 'ERROR');
			}
		}

		// Switch back to original blog before writing out the import logs to the database
		if ($original_blog_id != $blog_id)
			switch_to_blog($original_blog_id);

		// Update terms cache
		$import->clean_term_cache();

		// Set status and save back to db
		$import->log(__('Undo finished.'), 'INFO');
		$import->status = 'UNDO';
		$import->save();
		return true;
	}
}
?>