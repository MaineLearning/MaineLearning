<?php

class TI_Pro extends TI_Frontend {



	function view_template($import) {

		// Multisite

		if (is_multisite() && is_main_site()) {

			$this->postbox_start(__('Multisite'), 'multisite_settings');

			?>

			<table class="form-table">

				<?php $this->optionp($import, 'template[blog_id]'); ?>

			</table>

			<?php

			$this->postbox_end();

		}



		// Normal installation

		?>

		<?php $this->postbox_start(__('Layout'), 'layout_settings'); ?>

		<table class="form-table">

			<?php

				$this->optionp($import, 'template_id');

				$this->optionp($import, 'template[post_title]');

				$this->optionp($import, 'template[post_content]');

				$this->optionp($import, 'template[post_excerpt]');

			?>

		</table>

		<?php $this->postbox_end(); ?>



		<?php $this->postbox_start(__('Post/Page Settings'), 'postpage_settings'); ?>

			<table class="form-table">

				<?php

					$this->optionp($import, 'template[post_type]');

					$this->option($import, 'template[post_parent]');

					$this->option($import, 'template[page_template]');

					$this->option($import, 'template[menu_order]');

					$this->option($import, 'template[post_status]');

					$this->option($import, 'template[comment_status]');

					$this->option($import, 'template[post_author_type]');

					$this->optionp($import, 'template[post_name_col]');

					$this->optionp($import, 'template[postid_col]');

					$this->optionp($import, 'template[post_date_type]');

				?>

			</table>

		<?php $this->postbox_end(); ?>



		<?php $this->postbox_start(__('Categories and Tags'), 'categoriestags_settings'); ?>

			<table class="form-table">

				<?php

					$this->optionp($import, 'template[post_category_type]');

					$this->optionp($import, 'template[post_tag_type]');

				?>

			</table>

		<?php $this->postbox_end(); ?>



		<?php $this->postbox_start(__('Custom Fields'), 'customfields_settings'); ?>

			<table class="form-table">

				<?php

					$this->optionp($import, 'template[post_custom]');

					$this->optionp($import, 'template[post_custom_new]');

				?>

			</table>

		<?php $this->postbox_end(); ?>



		<?php $this->postbox_start(__('Maps'), 'maps_settings'); ?>

			<table class="form-table">

				<?php

					$this->optionp($import, 'template[poi_col]');

				?>

			</table>

		<?php $this->postbox_end(); ?>



		<?php

	}



	function optionp($import, $field) {

		global $wpdb;



		switch ($field) {



			case 'template_id':

				$templates = TI_Template::get_list();

				if ($templates) {

					$template_ids = array_keys($templates);

					$template_ids = array_combine($template_ids, $template_ids);

				}



				if (isset($template_ids))

					$template_ids = array_merge(array('' => 'new template'), (array)$template_ids);

				else

					$template_ids = array('' => 'new template');



				$html = __("Load template: ")

					. $this->option_dropdown('template_load_id', $import->template->_id, $template_ids)

					. "<input type='submit' class='button ti-submit' name='button_load_template' value='" . __('Load') . "' />"

					. "<input type='submit' class='button ti-submit' name='button_delete_template' value='" . __('Delete') . "' />"

					. "<br/>Save as template: <input type='text' name='template_save_id' value='" . esc_attr($import->template->_id) . "' />"

					. "<input type='submit' class='button ti-submit' name='button_save_template' value='" . __('Save') . "' />";



				$tip = __('Settings can be saved as a "template" for future imports.  This is the template currently in use.');

				$this->option_formatted(__('Import template'), $html, $tip);

				break;



			case 'template[post_title]':



				foreach((array)$import->headers as $header => $token)

					$tokens[] = "<a href='#'>" . esc_attr($token) . "</a>";



				$html = __('To insert a column: first click on the title, body or excerpt field, then click on the column name link') . "<br/>"

					. "<div id='ti_tokens'>" . implode(' | ', $tokens) . "</div>";

				$this->option_formatted(__('Input columns'), $html);



				$html = "<input id='post_title' class='ti-token-field ti-post-title' type='text' value='" . esc_attr($import->template->post_title) . "' name='$field'/>";



				$tip = __("Click on the field names to add them to your post title and body.  During the import they'll be replaced with data from your input file.");

				$this->option_formatted(__('Post title'), $html, $tip, '');

				break;





			case 'template[post_content]':

				// Div for the html/visual buttons

				$html = "<div>"

					. "<input type='button' name='button_editor_html' value='HTML' />"

					. "<input type='button' name='button_editor_visual' value='Visual' />"

					. "</div>";



				// Textarea

				$html .= "<div class='ti-post-content-div'>";

				$html .= "<textarea id='post_content' name='$field' class='ti-token-field ti-post-content' rows='12' >"

					. $import->template->post_content

					. "</textarea>"

					. "</div>";



				$tip = __("Click on the field names to add them to your post title and body.  During the import they'll be replaced with data from your input file.");

				$this->option_formatted(__('Post body'), $html, $tip);

				break;



			case 'template[post_excerpt]':

				$html = "<textarea id='post_excerpt' name='$field' class='ti-token-field ti-post-excerpt'  rows='3' >"

					. esc_attr($import->template->post_excerpt)

					. "</textarea>";



				$tip = __("Click on the field names to add them to your post title and body.  During the import they'll be replaced with data from your input file.");

				$this->option_formatted(__('Post excerpt'), $html, $tip);



				break;



			case 'template[blog_id]':

				// This only applies if it's a multisite install

				if (!is_multisite())

					return;



				$query = "SELECT * FROM {$wpdb->blogs} ORDER BY {$wpdb->blogs}.site_id, {$wpdb->blogs}.blog_id ";

				$wp_blogs = $wpdb->get_results( $query, OBJECT );



				$blog_ids = array();

				foreach($wp_blogs as $wp_blog) {

					$key = $wp_blog->blog_id;

					$blog_ids[$key] = $wp_blog->path;

				}



				$html = $this->option_dropdown($field, $import->template->blog_id, $blog_ids, false);

				$tip = __('Select a network / site to import into.  If you are importing into multiple sites you should activate TurboCSV only for the primary site and do all of your imports there.');

				$this->option_formatted(__('Network/Site'), $html, $tip);

				break;



			case 'template[post_type]':

				$wp_post_types = get_post_types(array('public' => true, '_builtin' => false), 'names', 'and');

				$post_types = array('post' => 'post', 'page' => 'page');

				if (!empty($wp_post_types))

					$post_types = array_merge($post_types, array_combine($wp_post_types, $wp_post_types));



				$html = $this->option_dropdown($field, $import->template->post_type, $post_types, false);

				$tip = __('Select a post type to import.  You can use a standard post type ("post" or "page"), or a <a href="http://codex.wordpress.org/Custom_Post_Types">custom post type</a>.');

				$this->option_formatted(__('Post type'), $html, $tip);

				break;



			case 'template[post_name_col]':

				$html = $this->option_dropdown_headers($field, $import->template->post_name_col, $import->headers, true);

				$tip = __('WordPress post "slug" - see <a href="http://codex.wordpress.org/Writing_Posts">here</a> for details.  Leave blank for default.');

				$this->option_formatted(__('Post name (slug) column'), $html, $tip);

				break;



			case 'template[postid_col]':

				$html = $this->option_dropdown_headers($field, $import->template->postid_col, $import->headers, true);

				$tip = __('<b>Optional</b>: To update existing posts/pages, specify a column containing the post ID to be updated.  If the post ID is empty or not found, a new post will be created.');

				$this->option_formatted(__('Post ID column'), $html, $tip);

				break;



			case 'template[post_date_type]':

				// Current date

				$checked = ($import->template->post_date_type == 'TODAY') ? "checked='checked'" : "";

				$html = "<input type='radio' name='$field'' value='TODAY' $checked />" . __('Current date and time');



				// Date range

				$checked = ($import->template->post_date_type == 'FIXED') ? "checked='checked'" : "";

				$html .= "<br/><input type='radio' name='$field' value='FIXED' $checked />" . __('Random dates') . "&nbsp"

					. "<input type='text' name='template[post_date_min]' value='" . esc_attr($import->template->post_date_min) . "' size='10' /> to "

					."<input type='text' name='template[post_date_max]' value='" . esc_attr($import->template->post_date_max) . "' size='10' />";



				// Column

				$checked = ($import->template->post_date_type == 'COLUMN') ? "checked='checked'" : "";

				$html .=  "<br/><input type='radio' name='$field' value='COLUMN' $checked />" . __('Column') . "&nbsp"

					. $this->option_dropdown_headers('template[post_date_col]', $import->template->post_date_col, $import->headers);



				$tip = __('This date will appear as the date the post was published.  To make your blog look like it was written over a period of time, use the "column" or "random dates" options.');

				$tip .= "<br/><br/>" . __('Note that server date and time is used, not your local PC time.');

				$this->option_formatted(__('Post date'), $html, $tip);



				break;



			case 'template[post_category_type]':

				$categories = null;

				$rows = null;



				// Fixed categories: get list of categories as (cat_ID => nicename)

				$wp_categories = get_categories(array('hide_empty' => false));

				foreach($wp_categories as $wp_category)

					$categories[$wp_category->cat_ID] = $wp_category->name;



				// Category from column

				$checked = ($import->template->post_category_type == 'COLUMN') ? "checked='checked'" : "";

				$html = "<input type='radio' name='$field' value='COLUMN' $checked />" . __('Column') . "&nbsp"

					. $this->option_dropdown_headers('template[post_category_col]', $import->template->post_category_col, $import->headers);

				$html .= "<br/>";



				// Fixed values

				$checked = ($import->template->post_category_type == 'FIXED') ? "checked='checked'" : "";

				$html .= "<input type='radio' name='$field' value='FIXED' $checked />"  . __('Select from list');

				$html .= "<br/>";



				foreach ((array)$categories as $key => $description) {

					$checked = (in_array($key, (array)$import->template->post_category)) ? "checked='checked'" : "";

					$key = esc_attr($key);

					$description = esc_attr($description);

					$rows[] = array('data' => array("<input type='checkbox' name='template[post_category][]' value='$key' $checked />", $description));

				}



				$headers = array("<input type='checkbox' class='selectall' />",  __('Category'));

				$html .= $this->option_table($headers, $rows, 'ti_categories_table');





				$tip = __('Select the categories to assign, or choose a column containing the category values separated by commas.')

					. "<br/>" . __("The 'hierarchy' setting will read a column containing comma-separated categories and create a category hierarcyh from them.");

				$this->option_formatted(__('Categories'), $html, $tip);

				break;



			case 'template[post_tag_type]':

				$tags = null;

				$rows = null;



				// Fixed tags: get list of tags as (term_ID => name)

				$wp_tags = get_tags(array('hide_empty' => false));

				foreach($wp_tags as $wp_tag)

					$tags[$wp_tag->term_id] = $wp_tag->name;



				// Column

				$checked = ($import->template->post_tag_type == 'COLUMN') ? "checked='checked'" : "";

				$html =  "<input type='radio' name='$field' value='COLUMN' $checked />" . __('Column') . "&nbsp"

					. $this->option_dropdown_headers('template[post_tag_col]', $import->template->post_tag_col, $import->headers);

				$html .= "<br/>";



				// Fixed

				$checked = ($import->template->post_tag_type == 'FIXED') ? "checked='checked'" : "";

				$html .= "<input type='radio' name='$field' value='FIXED' $checked />"  . __('Select from list');

				$html .= "<br/>";



				foreach ((array)$tags as $key => $description) {

					$checked = (in_array($key, (array)$import->template->post_tags)) ? "checked='checked'" : "";

					$key = esc_attr($key);

					$description = esc_attr($description);

					$rows[] = array('data' => array("<input type='checkbox' name='template[post_tags][]' value='$key' $checked />", $description));

				}



				if (count($rows) == 0)

					$rows[] = array('data' => array("<input type='checkbox' disabled='disabled' />", __('No existing tags')));



				$headers = array("<input type='checkbox' class='selectall' />",  __('Tag'));

				$html .= $this->option_table($headers, $rows, 'ti_tags_table');



				$tip = __('Enter a comma-separated list of post tags or pick a column to read the tags from the input file.');

				$this->option_formatted(__('Tags'), $html, $tip);

				break;



			case 'template[post_custom]':

				$meta_keys = $this->get_meta_keys();



				if (count($meta_keys) == 0) {

					$html = __('There are no existing custom fields to map.  See the WordPress ')

								. '<a href="http://codex.wordpress.org/Using_Custom_Fields">Codex</a>'

								. __(' for more information about custom fields.');

				} else {

					foreach ((array)$meta_keys as $meta_key) {

						$meta_value = (isset($import->template->post_custom[$meta_key])) ? $import->template->post_custom[$meta_key] : null;

						$dd = $this->option_dropdown_headers("template[post_custom][$meta_key]", $meta_value, $import->headers, true);

						$rows[] = array('data' => array($meta_key, $dd));

					}



					$headers = array(__('Field'), __('Input Column'));

					$html = $this->option_table($headers, $rows);



				}

				$tip = __('Use the mapping table to populate existing custom fields with data from the input file.');

				$this->option_formatted(__('Existing custom fields'), $html, $tip);

				break;



			case 'template[post_custom_new]':



				$existing_metas = $this->get_meta_keys();

				$new_metas = array();



				// Remove any fields that already exist

				foreach ($import->headers as $key => $header) {

					if (!in_array($header, (array)$existing_metas))

						$new_metas[] = $header;

				}



				if (count($new_metas) == 0) {

					echo "<p>" . __('No new custom fields.') . "</p>";

					break;

				}



				foreach ((array)$new_metas as $key => $value) {

					$checked = (in_array($value, (array)$import->template->post_custom_new)) ? "checked='checked'" : "";

					$value = esc_attr($value);

					$rows[] = array('data' => array("<input type='checkbox' name='template[post_custom_new][$value]' value='$value' $checked />", $value));

				}



				$headers = array("<input type='checkbox' class='selectall' />",  __('Input Column'));

				$html = $this->option_table($headers, $rows, 'ti_custom_new_table');



				$tip = __("Use the checkboxes to create new custom fields from the columns in your input file.");

				$this->option_formatted(__('Create new custom fields'), $html, $tip);

				break;



			case 'template[poi_col]':

				// Column

				if (class_exists('Mappress_Pro'))

					$html =  $this->option_dropdown_headers('template[poi_col]', $import->template->poi_col, $import->headers);

				else

					$html = "This function requires the <a href='http://wphostreviews.com'>MapPress Pro</a> plugin.";

				$tip = __("Select the column to use for map POIs.  If you haven't mapped the column to a custom field already, a custom field will be created for it.  The column may contain an address or a detailed POI specification.  Separate multiple addresses or POIs with the hierarchy separator ('|' by default).  Addresses will be geocoded during the import and a MapPress map will be created for each post.");

				$this->option_formatted(__('Map address/POI column'), $html, $tip);

				break;

		}

	}



	/**

	* Undo an entire import.  All posts and associated comments are deleted.

	* Any categories and tags that were created during the import are also

	* deleted, but only if they have no reference count once the posts/comments are gone.

	*

	* The import profile is retained and is not deleted, but the status is set to 'UNDO'.

	*

	* The instance will update itself back to the options database.

	*/

	function undo($id) {

		global $current_user;

		get_currentuserinfo();



		$import = TI_Import::get($id);

		if ($import === false)

			return new WP_Error('ERROR', sprintf(__('Unable to read import id %s for undo'), $id));



		$import->log(__("Undo started by: $current_user->user_login"), 'INFO');

		$import->import_start();



		foreach ((array)$import->imported_posts as $post) {

			// If post was updated during import, try to roll back to previous version

			if (isset($post['revision'])) {

				$result = wp_restore_post_revision( $post['revision'] );

				if (is_wp_error($result))

					$import->log(sprintf(__("Unable to restore original version of post %s (%s): %s"), $post['post_title'], $post['ID'], $result->get_error_message() ));

				if (!$result)

					$import->log(sprintf(__("Unable to restore original version of post %s (%s)"), $post['post_title'], $post['ID']));

			} else {

			// If post was created during import, delete it

				$result = wp_delete_post($post['ID']);

				if (is_wp_error($result))

					$import->log(sprintf(__("Error deleting post %s (%s): %s"), $post['post_title'], $post['ID'], $result->get_error_message() ));

			}

		}



		// Turn off cacheing

		$import->import_end();



		// NOTE: counts seem to be incorrect for custom taxonomies - they include deleted posts - see wordpress trac #14084, #14073, #14392

		// Delete tags, categories and custom taxonomies

		foreach ((array)$import->imported_terms as $taxonomy => $terms) {

			foreach ($terms as $term) {

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