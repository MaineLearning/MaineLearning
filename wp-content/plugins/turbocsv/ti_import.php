<?php
/**
* Class to perform the import.
* Note:
*   -Template settings are always saved with the import so the import can be repeated.
* 	-List of posts associated with each import is saved in same table.  This is faster, but limits imports by memory size.
*/
class TI_Import extends TI_Obj {
	var $_id,
	$status = 'NEW',
	$template,
	$user_id,
	$timestamp,
	$filepath,
	$fileurl,
	$filename,
	$headers,
	$logs,
	$lines_total = 0,
	$lines_blank = 0,
	$lines_imported = 0,
	$errors = 0,
	$imported_terms,
	$start_memory,
	$max_memory
	;

	/**
	* Constructor.
	* Create a new import object and immediately save it to the database
	*
	*/
	function __construct() {
		global $current_user;
		get_currentuserinfo();

		$this->user_id = $current_user->ID;
		$this->timestamp = time();
		$this->template = new TI_Template();
	}

	static function create_db() {
		return parent::create_db('ti_import', true);
	}

	static function get($id) {
		$result = parent::get('ti_import', $id);
		return $result;
	}

	static function get_list() {
		return parent::get_list('ti_import');
	}

	static function delete($id) {
		return parent::delete('ti_import', $id);
	}

	function save() {
		return parent::save('ti_import');
	}

	function get_imported_posts() {
		global $wpdb;

		// Older versions had imported_posts as a property of this class, newer versions use a DB table
		if (property_exists($this, 'imported_posts')) {
			// Convert the key post['ID'] to the newer form, post['post_id'] before returning
			$posts = array();
			foreach ((array)$this->imported_posts as $post) {
				$post['post_id'] = $post['ID'];
				$posts[] = $post;
			}
			return $posts;
		}

		// Newer versions stores the lines in a separate table
		$table = $wpdb->prefix . "ti_import_lines";
		$results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE import_id = {$this->_id}"), ARRAY_A);

		if (!$results)
			return array();
		return $results;
	}

	function import_start() {
		global $wpdb;

		if (!defined('WP_IMPORTING'))
			define( 'WP_IMPORTING', true );
		wp_defer_term_counting(true);
		wp_defer_comment_counting(true);

		// For slow machines, try to increase the time limit to 2 hours
		$max_time = ini_get('max_execution_time');
		if ($max_time < 7200)
			set_time_limit(7200);
	}

	function import_end() {
		// Switch off importing flag and flush rewrite cache
		wp_defer_term_counting(false);
		wp_defer_comment_counting(true);
		global $wp_rewrite;
		$wp_rewrite->flush_rules();
	}

	/**
	* Process the import.
	* $options is passed to subroutines in order to use main site options when importing to alternate blog_id in multisite
	*
	*/
	function import() {
		global $wpdb, $blog_id;

		$original_blog_id = $blog_id;  // Remember the original blog ID - it may be switched during line processing
		$options = get_option('turbocsv');
		$max_skip = $options['max_skip'];
		$encoding = $options['encoding'];
		$commit_rows = $options['commit_rows'];

		// Set status to 'ERROR' in case we get unhandled exception
		$this->status = 'ERROR';

		// Save before starting
		$result = $this->save();

		// Get headers, in case they were modified
		$result = $this->read_headers();
		if (is_wp_error(!$result))
			return $result;

		// Set max run time
		$this->import_start();

		$userd = get_userdata($this->user_id);
		$username = $userd->user_login;
		$this->log(__("Import started by: $username"), 'INFO');

		// Open the file
		$fp = $this->file_open();
		if (is_wp_error($fp))
			return $fp;

		// Throw away the header line
		$this->fgetcsv_plus($fp, $options);

		// Read the input file line-by-line
		while (!feof($fp)) {

			// Read the next line
			$line = $this->fgetcsv_plus($fp, $options);

			// feof() doesn't seem to work properly until *after* we read the last line
			if ($line === false)
				continue;

			// Skip blank lines
			if ($this->is_blank($line)) {
				$this->lines_blank++;
				continue;
			}

			$this->lines_imported++;
			$this->lines_total++;

			// Process a line
			$result = $this->import_line($line, $options);

			// If we had errors
			if (is_wp_error($result)) {
				$this->errors++;

				// Stop at maximum # of error lines
				if ($this->errors >= $max_skip) {
					$this->log(__('Import stopped with errors.'));
					break;
				}
			}

			// Every time commit count reached: save the imported WP objects and the current import object
			if (($this->lines_total % $commit_rows) == 0) {

				// Switch back to main blog if needed before saving
				if ($original_blog_id != $blog_id)
					switch_to_blog($original_blog_id);

				$this->save();
				$wpdb->query("COMMIT");
			}
		}

		// Close the input file
		fclose($fp);

		// Refresh term cache
		$this->clean_term_cache();

		// Log completion
		$this->log(__('Import finished.'), 'INFO');

		// Set final status
		if ($this->has_errors())
			$this->status = 'ERROR';
		else
			$this->status = 'COMPLETE';

		$this->import_end();

		// Switch back to main blog if needed
		if ($original_blog_id != $blog_id)
			switch_to_blog($original_blog_id);

		// Save import
		$this->save();

		return;
	}

	function import_line($line, $options) {
		global $wpdb, $wp_object_cache;

		$post = array();                            // Post to be sent to WP
		$imported_post = new TI_Import_Line();      // Post details saved to the database after the import
		$update = false;                             // Whether this is an update

		// Get blog ID and switch to that blog if needed
		$post_blog_id = $this->import_blog_id($line);
		if (is_wp_error($post_blog_id))
			return $post_blog_id;
		else
			$imported_post->blog_id = $post_blog_id;

		// Determine if this is an insert or an update (it's an update if a post ID was specified AND that post exists already)
		if ($this->template->postid_col) {
			// ID value to match
			$update_id = $this->read_line_col($line, $this->template->postid_col);

			if (is_wp_error($update_id))
				return $update_id;

			// If column is not empty, then try to find the matching post ID
			if ($update_id) {
				// Field to match against
				$update_field = $this->template->update_field;

				// Try to find the post to be updated
				$found_id = $this->find_post_id($update_field, $update_id);
				if ($found_id) {
					$post['ID'] = $found_id;
					$update = true;
				}
			}
		}

		// Parse the tokens in the post body/title
		$result = $this->import_post_content($line, $post, $update);
		if (is_wp_error($result))
			return $result;

		// Assign basic settings fields and validate them
		$result = $this->import_post_settings($line, $post);
		if (is_wp_error($result))
			return $result;

		// Assign post dates
		$result = $this->import_post_date($line, $post, $update);
		if (is_wp_error($result))
			return $result;

		// Taxonomies
		$result = $this->import_post_taxonomies($line, $options);
		if (is_wp_error($result))
			return $result;
		elseif (!empty($result))
			$post['tax_input'] = $result;

		// Update or insert the post
		// If revisions are active, this may return a new post ID
		if ($update)
			$post_id = wp_update_post($post);
		else
			$post_id = wp_insert_post($post, true);

		// Record memory usage after update/insert
		$memory_usage = memory_get_usage();
		$this->max_memory = ($memory_usage > $this->max_memory) ? $memory_usage : $this->max_memory;

		// Flush the object cache - if this isn't done WordPress leaks 4k of memory with each new post.
		// Even with the flush, it's about 900B.
		$wp_object_cache->flush();

		// Any error from posting is fatal.  wp_insert gives a full error, wp_update just returns FALSE
		if (is_wp_error($post_id))
			return $this->log_line($post_id);
		elseif ($post_id === false)
			return $this->log_line(sprintf(__("Wordpress error when updating post id=%s"), $post_id));

		// If updating, remember revision post number for later undo
		if ($update) {
			// Figure out if revisions are supported and switched on
			if (defined('WP_POST_REVISIONS') && WP_POST_REVISIONS && !empty($post['post_type']) && post_type_supports($post['post_type'], 'revisions') ) {
				// A revision was created, save it for later UNDO
				$imported_post->revision_id = $this->get_last_revision($post_id);
			} else {
				// If revision management not turned on, store FALSE in revision
				$imported_post->revision_id = false;
			}
		}

		// Post was OK - save the post data to the import; content and excerpt are NOT saved to conserve memory
		$imported_post->updated = $update;
		$imported_post->post_id = $post_id;
		$imported_post->import_id = $this->_id;
		$result = $imported_post->save();
		if (is_wp_error($result))
			return $result;

		// Featured image / thumbnail - update/insert after post is complete
		$result = $this->import_thumbnail($line, $post_id);
		if (is_wp_error($result))
			return $result;

		// Update/insert post metadata after the post is complete
		$result = $this->import_post_meta($line, $post_id, $options, $update);
		if (is_wp_error($result))
			return $result;

		return true;
	}

	/**
	* Find a post ID by the contents of a field.
	* If $field is blank, the function will try to fetch by post ID, otherwise it will search the custom field ($field)
	*
	* @param mixed $field - A custom field name or blank for Post ID
	* @param mixed $id - unique ID number to search for, either Post ID or contents of a custom field
	* @return mixed - ID of found post or FALSE if no match found
	*/
	function find_post_id($field, $id) {
		global $wpdb;

		// If field is blank, then assume ID represents a post ID, otherwise search for custom field value matching $id
		if (!$field || empty($field))
			$found_id = (get_post($id)) ? $id : null;
		else
			$found_id = $wpdb->get_var( $wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value = %s", $field, $id) );

		if (!$found_id)
			return false;

		return $found_id;
	}

	/**
	* Get blog ID for the post
	*
	* If not multisite then global $blog_id is returned
	* If error, wp_error is returned
	*
	* @return WP_Error if error
	*/
	function import_blog_id($line) {
		global $blog_id;

		// Ignore blog ID if not multisite
		if (!is_multisite())
			return $blog_id;

		if ($this->template->blog_id_col) {
			$data = $this->read_line_col($line, $this->template->blog_id_col, $this->template->blog_id);

			if (is_wp_error($data))
				return $data;
			else
				$post_blog_id = (int) $data;
		} elseif ($this->template->blog_id) {
			$post_blog_id = (int) $this->template->blog_id;
		}

		// If blog_id was empty, return current blog
		if (!isset($post_blog_id) || !($post_blog_id))
			return $blog_id;

		// If blog_id has changed try to switch to the new blog
		if ($post_blog_id && $post_blog_id != $blog_id) {
			if (switch_to_blog($post_blog_id, true) === false)
				return $this->log_line(sprintf(__('Invalid blog id: %s'), $post_blog_id));
		}

		return $post_blog_id;
	}

	function import_post_content($line, &$post, $update) {
		// Make tokens out of the list of headers
		foreach($this->headers as $header)
			$tokens[$header] = '#' . $header . '#';

		// Replace the tokens in the content fields
		$content = str_replace($tokens, $line, $this->template->post_content);
		$title = str_replace($tokens, $line, $this->template->post_title);
		$excerpt = str_replace($tokens, $line, $this->template->post_excerpt);

		// Only set the fields if they're not empty
		if (!empty($content))
			$post['post_content'] = $content;
		if (!empty($title))
			$post['post_title'] = $title;
		if (!empty($excerpt))
			$post['post_excerpt'] = $excerpt;

		// Validate post title & content for new posts (not for updates)
		if (!$update && empty($post['post_content']) && empty($post['post_title']))
			return $this->log_line(__('Post title and body are both empty.  WordPress does not allow empty posts.'));

		return true;
	}

	/**
	* Read basic settings fields
	*
	* @param mixed $line
	* @param WP_Error $post
	* @return WP_Error
	*/
	function import_post_settings($line, &$post) {
		$fields = array('post_type', 'post_parent', 'page_template', 'menu_order', 'post_status', 'comment_status', 'post_author', 'post_name');
		foreach ($fields as $field) {

			// Get value from column or default
			$col = $field . "_col";
			$value  = $this->read_line_col($line, $this->template->$col, $this->template->$field);

			if (is_wp_error($value))
				return $value;

			// Validate
			switch($field) {
				case 'post_type':
					// Validate post type if one was provided.  Note that this does NOT check if post type valid for current site!
					if ($value) {
						$index = array_search($value, get_post_types(array('public' => true), 'names'));
						if ($index === false || $index === null)
							return $this->log_line(sprintf(__('Invalid post type: "%s"'), $value));
					}
					break;

				// No validation for these
				case 'page_template':
				case 'post_parent':
				case 'menu_order':
				case 'post_status':
				case 'comment_status':
				case 'post_author':
				case 'post_name':
				default:
			}

			// Set the field value
			if (!empty($value))
				$post[$field] = $value;
		}
	}

	function import_post_meta($line, $post_id, $options, $update) {
		// Add the metadata for new and existing custom fields
		$all_meta = array_merge((array)$this->template->post_custom, (array)$this->template->post_custom_new);

		// Add metadata; each key should have an associated input column
		foreach ($all_meta as $key => $col) {

			// If no input column specified, then skip this field (this can happen for existing custom fields, if the field wasn't mapped)
			if (empty($col))
				continue;

			$data = $this->read_line_col($line, $col);
			if (is_wp_error($data))
				return $data;

			// If the data is empty, skip it
			if (empty($data))
				continue;

			// If updating, delete any existing values in the custom field
			if ($update)
				delete_post_meta($post_id, addslashes($key));

			// If the data begins with "s:" then we'll treat it as an array that must be serialized
			if (strtolower(substr($data, 0, 2)) == $options['serialize_prefix']) {
				$data = substr($data, 2);
				$serialized = true;
			} else {
				$serialized = false;
			}

			// Split data by custom field values separator (also allows escaped separators)
			$data = $this->explode_escaped($options['values_separator'], $data);

			if ($serialized) {
				$result = add_post_meta($post_id, $key, $data);
			} else {
				// Add each data value separately
				foreach((array)$data as $datum) {
					// If the data is already in serialized form, WP will re-serialize it, so we must unserialize it first
					if ( is_serialized( $datum ) ) {
						$datum = unserialize($datum);
					} else {
						// WP stripslashes on POST when adding metadata so add slashes first!
						$key = addslashes($key);
						$datum = addslashes($datum);
					}
					$result = add_post_meta($post_id, $key, $datum);
				}
			}

			if ($result === false)
				return $this->log_line(sprintf(__('Error in add_post_meta for custom field: id="%s", key="%s"'), $post_id, $key));
		}

		// Trigger mappress geocoding
		do_action('mappress_update_meta', $post_id);

		// Check for any mappress geocoding errors
		$errors = get_post_meta($post_id, 'mappress_error');
		if ($errors) {
			foreach($errors as $error) {
				$this->log_line(sprintf(__('Error in map creation: %s'), $error));
			}
			return $this->log_line(sprintf(__('Map is incomplete for post id="%s"'), $post_id));
		}
		return true;
	}

	function import_thumbnail($line, $post_id) {
		// If the column is empty or missing, just return
		$value = $this->read_line_col($line, $this->template->thumbnail_col);
		if (empty($value) || is_wp_error($value))
			return;

		// For IDs
		if (is_numeric ($value)) {
			update_post_meta( $post_id, '_thumbnail_id', (int) $value );
			return true;
		}

		// For URLs - search for the correct attachment ID
		// URLs are saved in custom field _wp_attached_file.
		// They are saved *without* the baseurl, e.g. "/myimage.png" is actually in "http://myblog.com/wp-content/uploads/myimage.png"

		// Get upload dir
		$uploads = wp_upload_dir();
		if (!$uploads || $uploads['error'])
			return $this->log_line('Unable to find uploads directory for featured images');

		$baseurl = trailingslashit($uploads['baseurl']);

		// Strip out the upload baseurl (we only need the part starting after /uploads/, since that's all WP stores)
		$meta_value = str_ireplace($baseurl, '', $value);

		if (empty($meta_value))
			return $this->log_line(sprintf(__('Invalid attachment URL, it must include the blog home and upload directory: %s'), $value));

		// Now find the (attachment) post with _wp_attached_file set to the given value, or the fist 'like' match
		$posts = get_posts(array('post_type' => 'attachment', 'numberposts' => 1, 'meta_query' => array(array('key' => '_wp_attached_file', 'value' => $meta_value, 'compare' => 'LIKE'))) );

		// Not used - this is an EXACT match
		// $posts = get_posts(array('post_type' => 'attachment', 'meta_key' => '_wp_attached_file', 'meta_value' => $meta_value, 'numberposts' => 1));

		if (empty($posts))
			return $this->log_line(sprintf(__('Unable to find any attachment with URL: %s'), $value));

		update_post_meta( $post_id, '_thumbnail_id', $posts[0]->ID );
		return true;
	}


	/**
	* Get the post date for the input file line.  Note that WP requires date in format 'Y-m-d H:i:s'
	*
	* post_modified: if updating, WP sets to current time, otherwise it uses post_date
	* post_date:
	*   if post_stauts = {'draft', 'pending', 'auto-draft'} then post_date is forced to now()
	*   if post_status = 'publish' and post_date > now(), then set post_status to 'future'
	*   if post_status = 'future' and post_date <= now(), then set post_status to 'publish'
	*
	* @param mixed $line - current input file line
	* @param string $post - current $post array being passed to wp_insert_post or wp_update_post
	* @param mixed $update - true if this is an update, false for insert
	* @return - true on success (setting a post date or skipping it), WP_Error on invalid dates
	*/
	function import_post_date($line, &$post, $update) {
		$min = $this->template->post_date_min;
		$max = $this->template->post_date_max;

		// Set default
		$default = '';
		if (!empty($min)) {
			$min_time = strtotime($min);
			if ($min_time === false)
				return $this->log_line(sprintf(__("Invalid mininum post date: '%s'"), $min));

			// If only $min is set then use it unchanged with time 00:00:00
			if (empty($max))
				$default = date('Y-m-d H:i:s', strtotime($min));
			else {
				// if $min and $max are set, then pick a random date/time between them
				if (date('m/d/Y', time()) == $max)
					$max_time = time();
				else
					$max_time = strtotime($max) + 86399;

				if ($max_time === false)
					return $this->log_line(sprintf(__("Invalid maximum post date: '%s'"), $max_time));

				$default = date('Y-m-d H:i:s', strtotime('+' . rand(0, $max_time - $min_time) . 'seconds', $min_time));
			}
		}

		// Use the column value or the default
		$value = $this->read_line_col($line, $this->template->post_date_col, $default);

		if (is_wp_error($value))
			return $value;

		// If value is empty, don't set any post date or post_date_gmt
		if (empty($value))
			return true;

		// Check that's it's a valid date
		if (!strtotime($value))
			return $this->log_line(sprintf(__("Invalid post date: '%s'"), $value));

		$post['post_date'] = date('Y-m-d H:i:s', strtotime($value));

		// For updates - if post_date is being changed, clear post_date_gmt so WP can recalculate it, otherwise WP will mess up the post status
		if ($update)
			$post['post_date_gmt'] = '';

		return true;
	}

	function import_post_taxonomies($line, $options) {
		$post_taxonomies = array();

		// Output custom taxonomies
		$taxonomies = get_taxonomies(array('_builtin' => false));
		$taxonomies[] = 'category';
		$taxonomies[] = 'post_tag';

		foreach ($taxonomies as $taxonomy_name) {
			$result = $this->import_post_taxonomy($taxonomy_name, $line, $options);
			if (is_wp_error($result))
				return $result;

			if (!empty($result))
				$post_taxonomies[$taxonomy_name] = $result;
		}

		return $post_taxonomies;
	}

	function import_post_taxonomy($taxonomy_name, $line, $options) {
		// If no template data for this taxonomy, return
		if (!isset($this->template->taxonomies[$taxonomy_name]) || empty($this->template->taxonomies[$taxonomy_name]))
			return array();

		$taxonomy = $this->template->taxonomies[$taxonomy_name];

		// Get WP taxonomy details
		$wp_taxonomy = get_taxonomy($taxonomy_name);
		if (!$wp_taxonomy)
			return $this->log_line(sprintf(__('Unknown taxonomy "%s"'), $taxonomy_name));

		$terms = array();  // Terms to set for this taxonomy

		// If a column name was provided, try to read the column
		if ($taxonomy['col'])
			$data = $this->read_line_col($line, $taxonomy['col']);
		else
			$data = null;

		if (is_wp_error($data))
			return $data;

		// If there's no column, or the column is empty, return any default values
		if (empty($data)) {
			$values = (isset($taxonomy['values'])) ? (array) $taxonomy['values'] : array();

			// For hierarchical taxonomies, just return an array of the values to set
			if ($wp_taxonomy->hierarchical)
				return $values;

			// For flat taxonomies WP requires a comma-separated string
			foreach($values as $tag_ID)
				$terms[] = get_tag($tag_ID)->name;
			return implode(',', $terms);
		}

		// If column data exists, parse it to get the terms and create any missing terms
		$data = trim($data);
		if (!empty($data)) {
			// Split data by values separator (also allows escaped separators)
			$term_names1 = $this->explode_escaped($options['values_separator'], $data);

			if ($wp_taxonomy->hierarchical) {
				// Hierarchical taxonomies are also split by "|" separator
				foreach($term_names1 as $term_name1) {
					// Split by hierarchy separator
					$term_names2 = preg_split("/[" . $options['hierarchy_separator'] . "]+/", $term_name1);
					$parent_id = null;

					// Track lowest term in hierarchy
					$last_term = "";

					foreach((array)$term_names2 as $term_name) {
						// Create the term if it doesn't already exist
						$result = $this->insert_term($term_name, $taxonomy_name, $parent_id, $options);
						if (is_wp_error($result))
							return $result;

						// Use this as parent for next term in hierarchy
						$parent_id = $result['term_id'];

						// Track final term in the hierarchy
						$last_term = $result['term_id'];
					}

					// Store only the final term (a 'leaf') in the hierarchy to the post (this is also how WP post editor does it)
					if ($last_term)
						$terms[] = $last_term;
				}
			} else {
				// Flat taxonomies
				$parent_id = null;
				foreach($term_names1 as $term_name) {
					// Create the term if it doesn't already exist
					$result = $this->insert_term($term_name, $taxonomy_name, $parent_id, $options);
					if (is_wp_error($result))
						return $result;

					// Store term for current post
					$terms[] = $term_name;
				}
			}
		}

		return $terms;
	}


	function insert_term($term_name, $taxonomy_name, $parent_id = null, $options = null) {
		global $blog_id;

		$term_name = trim($term_name);

		// If the term is in format "term::description" then explode it into separate term name + description
		if ($options && isset($options['term_description_separator'])) {
			$parts = explode($options['term_description_separator'], $term_name);
			if (is_array($parts) && count($parts) > 1) {
				$term_name = $parts[0];
				$description = $parts[1];
			} else {
				// If not explicit description, default it to term name
				$description = $term_name;
			}
		}

		// If the term already exists in the given taxonomy, with the given parent, there's nothing to do
		// term_exists returns 0 if term is invalid, null if not found, and an array if valid
		$term = term_exists($term_name, $taxonomy_name, $parent_id);
		if ($term) {
			return $term;
		}

		// Set the parent
		$args = array('parent' => $parent_id, 'description' => $description);

		// Insert the term
		$result = wp_insert_term($term_name, $taxonomy_name, $args);

		if (is_wp_error($result))
			return $this->log_line(sprintf(__('Unable to create term "%s" in taxonomy "%s": %s'), $term_name, $taxonomy_name, $result->get_error_message() ));

		if (!$result)
			return $this->log_line(sprintf(__('Unable to create term "%s" in taxonomy "%s"'), $term_name, $taxonomy_name));

		// Get the parent name for future reference
		$parent_name = $parent_id; // Default to parent ID in case name is not available
		if ($parent_id) {
			$wp_parent = get_term($parent_id, $taxonomy_name);
			if (!is_wp_error($wp_parent) && $wp_parent)  // get_term returns either wp_error or null
				$parent_name = $wp_parent->name;
		}

		$this->imported_terms[$taxonomy_name][] = (object) array(
			'blog_id' => $blog_id,      // Remember blog ID for each term - used by undo function
			'parent' => $parent_id,
			'parent_name' => $parent_name,
			'term_id' => $result['term_id'],
			'term_taxonomy_id' => $result['term_taxonomy_id'],
			'name' => $term_name);
		return $result;
	}

	/**
	* Read a column from a line of the input file
	*
	* @param mixed $line
	* @param mixed $col
	* @return mixed - column value | wp_error for invalid column name | "" for empty column
	*/
	function read_line_col($line, $col=null, $default='') {
		$value = "";

		// Get the column index by searching header column names
		if ($col) {
			$index = array_search($col, $this->headers);
			if ($index === false || !isset($line[$index]))
				return $this->log_line(sprintf(__('Column %s could not be read.  The file may be unreadable.  If the column name is invalid then remove it from the import template.'), $col));

			$value = $line[$index];
		}

		if (empty($value))
			return $default;
		else
			return $value;
	}

	/**
	* Get maximum revision # using raw query
	*
	* @param mixed $postid
	*/
	function get_last_revision($postid) {
		global $wpdb;
		return $wpdb->get_var( $wpdb->prepare( "SELECT max(ID) FROM $wpdb->posts WHERE post_parent = %d AND post_type = 'revision'", $postid ) );
	}


	/**
	* Figure out if a line is blank.
	* According to the docs, fgetcsv() returns 1-element array with that element = null for blank lines
	* However, excel returns lines with just quotes in them, e.g. "", "",...
	*
	* @param mixed $line
	*/
	function is_blank($line) {
		// Basic fgetcsv() check
		if (count($line) == 1 && empty($line[0]))
			return true;

		// Look for array containing ONLY nulls
		if (count($line) == count(array_keys($line, null, true)))
			return true;

		// Look for lines containing ONLY ""
		if (count($line) == count(array_keys($line, "", true)))
			return true;

		// It's not blank
		return false;
	}

	function upload_file($files=null, $server_url=null, $server_path=null) {
		if(empty($files) && empty($server_url) && empty($server_path))
			return $this->log(__('No file specified'));

		if ($server_url)
			$this->fileurl = $server_url;

		elseif ($server_path)
			$this->filepath = $server_path;

		else {
			$filename = $files['name'];
			$tmp_name = $files['tmp_name'];

			// Read file contents from tmp file
			$file = file_get_contents($tmp_name);

			// If the file is in another format, try to convert it to UTF8
			$file = $this->utf8_convert($file);

			// Get wordpress upload directory
			$upload = wp_upload_dir();
			if (isset($upload['ERROR']))
				return new WP_Error('ERROR', __('Could not determine upload directory: ') . $upload['ERROR']);

			// Figure out file name
			$filename = wp_unique_filename( $upload['path'], $filename );
			$new_file = $upload['path'] . "/" . $filename;

			// Create directory if needed
			if(!wp_mkdir_p(dirname($new_file)))
				return $this->log(sprintf( __('Unable to create directory %s. Check permissions.'), dirname($new_file)));

			// Write the file to the upload directory
			$fp = fopen($new_file, 'wb');
			if (is_wp_error($fp))
				return $fp;

			fwrite($fp, $file);
			fclose($fp);

			// Set correct file permissions
			$stat = @ stat(dirname($new_file));
			$perms = $stat['mode'] & 0007777;
			$perms = $perms & 0000666;
			@ chmod($new_file, $perms);

			// Get URL and path
			$this->filepath = $new_file;
			$this->fileurl = $upload['url'] . "/" . $filename;
			$this->filename = $filename;
		}

		// Get the headers
		$result = $this->read_headers();
		if (is_wp_error($result))
			return $result;

		// Save the import object to the database
		$result = $this->save();
		if (is_wp_error($result))
			return $result;
	}

	function file_open() {
		// for Macs
		ini_set('auto_detect_line_endings', true);

		// Open the input file.  If we have a path use it, otherwise try URL
		if ($this->filepath)
			$fp = fopen($this->filepath, 'r');
		else
			$fp = fopen($this->fileurl, 'r');

		if ($fp)
			return $fp;
		else
			return $this->log(sprintf(__('Unable to open file: %s'), $this->fileurl));
	}

	function read_headers() {
		$options = get_option('turbocsv');

		// Read the first line to get headers
		$fp = $this->file_open();
		if (is_wp_error($fp))
			return $fp;

		//$headers = $this->fgetcsv_plus($fp, $options['input_delimiter'], $options['locale']);
		$headers = $this->fgetcsv_plus($fp, $options);

		// It's an error if there are no headers or if any header is empty
		if (empty($headers)) {
			fclose($fp);
			return $this->log(sprintf(__('Unable to read file %s.  Check that the file exists and verify the encoding in the TurboCSV settings.'), $this->fileurl));
		}

		// Look for any empty headers
		foreach ((array)$headers as $key => $header) {
			if (empty($header)) {
				fclose($fp);
				return $this->log(sprintf(__('Header for column %s is empty.  Empty headers are not allowed'), $key));
			}
		}

		$this->headers = $headers;
		fclose($fp);
	}

	function log_line($message, $code='ERROR') {
		return $this->log($message, $code, $this->lines_total);
	}

	// The log is actually a set of wp_errors where 'msg' is an object with date, time and msg string
	function log($message, $code='ERROR', $line=null) {

		// We can accept a string or a wp_error object
		if (is_wp_error($message))
			$message = $message->get_error_message();

		// Save an enhanced 'message' - an object with the time, line # and the message
		$this->logs[] = (object) array('code' => $code, 'time' => time(), 'line' => $line, 'msg' => $message);

		// Return a singleton WP_error object for the error that was just saved
		return new WP_Error($code, $message);
	}

	function has_errors() {
		foreach((array)$this->logs as $log)
			if ($log->code == 'ERROR')
				return true;
	}

	function get_status($long=false, $icon=false) {
		$statuses = array(
			'NEW' => array('short' => __('New'), 'icon' => 'ready.png', 'long' => __('New')),
			'UNDO' => array('short' => __('Undo Complete'), 'icon' => 'undo.png', 'long' => __('Undo Complete')),
			'COMPLETE' => array('short' => __('Complete'), 'icon' => 'green_light.png', 'long' => __('Complete')),
			'ERROR' => array('short' => __('Error'), 'icon' => 'red_light.png', 'long' => __('Completed with errors')),
		);

		$status = (isset($statuses[$this->status])) ? $statuses[$this->status] : null;
		if ($this->status == 'COMPLETE') {
			if ($this->has_errors())
				$status = $statuses['ERROR'];
		}

		if ($icon)
			$icon = "<img width='16' height='16' alt='" . $status['short'] . "' align='middle' src='" . plugins_url("/images/" . $status['icon'], __FILE__) . "'/> ";

		if ($long)
			return $icon . " <b>" . $status['long'] . "</b>";
		else
			return $icon . $status['short'];
	}

	/**
	* Workaround for WP internal bug in standard clean_term_cache() function.
	* That function uses a static variable and so really cleans only on the first term/category/tag insert.  It fails
	* if multiple terms are inserted at once.
	*
	* Code is copied from standard 3.0 clean_term_cache, with following patch applied: http://core.trac.wordpress.org/ticket/14485
	* Many months old and still not fixed in 3.2.1...
	*/

	function clean_term_cache() {
		global $wpdb, $blog_id;

		$original_blog_id = $blog_id;

		// Clear taxonomy caches
		foreach((array)$this->imported_terms as $taxonomy => $terms) {
			foreach ($terms as $term) {
				// Switch blogs if needed
				if ($term->blog_id != $blog_id) {
					$result = switch_to_blog($term->blog_id);

					// If unable to switch to the blog then skip it
					if ($result === false)
						continue;
				}

				wp_cache_delete('all_ids', $taxonomy);
				wp_cache_delete('get', $taxonomy);

				// Begin code from trac 14485 - hopefully it'll work
				$cache_keys = wp_cache_get( 'get_terms:cache_keys', 'terms' );
				if ( ! empty( $cache_keys ) ) {
					foreach ( $cache_keys as $key => $cache_taxonomies ) {
						if ( in_array( $taxonomy, $cache_taxonomies ) ) {
							wp_cache_delete( "get_terms:{$key}", 'terms' );
							unset( $cache_keys[$key] );
						}
					}
				} else {
					$cache_keys = array();
				}
				wp_cache_set( 'get_terms:cache_keys', $cache_keys, 'terms' );

				delete_option("{$taxonomy}_children");
				_get_term_hierarchy($taxonomy);
			}
		}

		// Switch back to original blog if needed
		if ($original_blog_id != $blog_id)
			switch_to_blog($original_blog_id);

		$wpdb->query("COMMIT");
	}

	/**
	* For debugging, dump the file contents to the screen.
	* Useful to determine if character conversion errors occur in PHP or MySQL (since this isn't using MySQL)
	*
	*/
	function dump_file() {
		$options = get_option('turbocsv');

		$fp = $this->file_open();
		while (!feof($fp)) {

			$line = $this->fgetcsv_plus($fp, $options);

			if ($line) {
				foreach ($line as $key => $value)
					echo $key . ":" . $value;
			}
			echo "<br/><br/>";
		}
		fclose($fp);
	}


	/**
	* There are various problems with fgetcsv in different versions of PHP.
	* For example: if an accented character is found (like british pound sign) as first character after delimiter, it is discarded
	*
	* One workaround is to use set_locale() prior to calling fgetcsv, another is to simply replace fgetcsv() with a custom function.
	*
	* This routine provides the ability to do either workaround, or just use the standard fgetcsv().
	*
	* @param mixed $handle - file handle
	* @param mixed $options - the options array from: get_options('turbocsv')
	*/
	function fgetcsv_plus($fp, $options) {
		$input_delimiter = $options['input_delimiter'];
		$locale = $options['locale'];
		$custom_parser = $options['custom_parser'];

		// There's no HTML 'tab' so the tab delimiter is represented by the word 'tab'
		if ($input_delimiter == 'tab')
			$input_delimiter = "\t";

		// Use a completely custom parser
		if ($custom_parser)
			return $this->fgetcsv_custom($fp, $input_delimiter);

		// Use standard fgetcsv()
		if (!$locale || empty($locale))
			return fgetcsv($fp, 0, $input_delimiter);

		// Use standard fgetcsv() but override the locale just during the fgetcsv() call
		$old_locale = setlocale(LC_ALL, 0);
		setlocale(LC_ALL, $locale);
		$result = fgetcsv($fp, 0, $input_delimiter);          // Don't pass enclosure/escape - even default values cause problems
		setlocale(LC_ALL, $old_locale);

		return $result;
	}

	function fgetcsv_custom($handle, $CSV_SEPARATOR = ',', $CSV_ENCLOSURE = '"') {
		// Note: since this function cannot tell if it's on Windows or Unix, it looks for BOTH \r\n and \n as a line ending!
		$CSV_LINEBREAK = "\n";
		$CSV_LINEBREAK_WIN = "\r";

		$string = fgets($handle);

		$o = array();
		$cnt = strlen($string);
		$esc = false;
		$escesc = false;
		$num = 0;
		$i = 0;
		while ($i < $cnt) {
			$s = $string[$i];
			$s_next = ($i < $cnt - 1) ? $string[$i+1] : null;

			if ($s == $CSV_LINEBREAK || ($s == $CSV_LINEBREAK_WIN && $s_next == $CSV_LINEBREAK)) {
			  if ($esc) {
				$o[$num] .= $s;
			  } else {
				if ($s == $CSV_LINEBREAK)
					$i++;
				else
					$i += 2;    // For windows, skip TWO characters
				break;
			  }
			} elseif ($s == $CSV_SEPARATOR) {
			  if ($esc) {
				$o[$num] .= $s;
			  } else {
				// To conform with fgetcsv() behavior, an empty field should be returned as empty array element!
				if (!isset($o[$num]))
					$o[$num] = '';

				$num++;
				$esc = false;
				$escesc = false;
			  }
			} elseif ($s == $CSV_ENCLOSURE) {
			  if ($escesc) {
				$o[$num] .= $CSV_ENCLOSURE;
				$escesc = false;
			  }

			  if ($esc) {
				$esc = false;
				$escesc = true;
			  } else {
				$esc = true;
				$escesc = false;
			  }
			} else {
			  if ($escesc) {
				$o[$num] .= $CSV_ENCLOSURE;
				$escesc = false;
			  }

			  if (isset($o[$num]))
				$o[$num].= $s;
			  else
				$o[$num] = $s;
			}

			$i++;
		}
		return $o;
	}


	function utf8_convert($file) {
		$options = get_option('turbocsv');

		$encoding = isset($options['encoding']) ? $options['encoding'] : null;

		// Remove BOMs and detect formats
		// Note that this covers only UTF8, and UTF16.  Other esoteric formats aren't covered (http://en.wikipedia.org/wiki/Byte_order_mark)
		if ( substr($file, 0, 3) == pack('CCC',  0xef, 0xbb, 0xbf) ) {
			$file = substr($file, 3);
			$detected = "UTF-8";
		} elseif (substr($file, 0, 2) == pack('CC', 0xfe, 0xff)) {
			$file = substr($file, 2);
			$detected = "UTF-16BE";
		} elseif (substr($file, 0, 2) == pack('CC', 0xff, 0xfe)) {
			$file = substr($file, 2);
			$detected = "UTF-16LE";
		} else {
			$detected = false;
		}

		// If user specified an encoding, always use it
		if ($encoding && $encoding != 'auto')
			return iconv($encoding, 'UTF-8//IGNORE', $file);

		// Auto detect - if something was detected then use it
		if ($detected)
			return iconv($detected, "UTF-8//IGNORE", $file);

		// If it looks like UTF8 without a header, then return the file unchanged
		//if (utf8_encode(utf8_decode($file)) == $file) -- only works if file consists of iso-8859-1 characters, iconv is better
		if (@iconv('utf-8', 'utf-8//IGNORE', $file) == $file)
			return $file;

		// Nothing detected, so just assume 1252
		return iconv("Windows-1252", 'UTF-8//IGNORE', $file);
	}

	/**
	* From php.net: a version of explode() that ignores escaped delimiters (standard version will still explode the delimiter, even if escaped)
	*
	* @param mixed $delimiter
	* @param mixed $string
	* @return string
	*/
	function explode_escaped($delimiter, $string){
		$exploded = explode($delimiter, $string);
		$fixed = array();
		for($k = 0, $l = count($exploded); $k < $l; ++$k){
			if($exploded[$k][strlen($exploded[$k]) - 1] == '\\') {
				if($k + 1 >= $l) {
					$fixed[] = trim($exploded[$k]);
					break;
				}
				$exploded[$k][strlen($exploded[$k]) - 1] = $delimiter;
				$exploded[$k] .= $exploded[$k + 1];
				array_splice($exploded, $k + 1, 1);
				--$l;
				--$k;
			} else $fixed[] = trim($exploded[$k]);
		}
		return $fixed;
	}
}    // End Class TI_Import


class TI_Import_Line extends TI_Obj {
	var $_id,
		$updated,
		$import_id,
		$blog_id,
		$revision_id,
		$post_id;

	/**
	* Constructor.
	* Create a new import object and immediately save it to the database
	*
	*/
	function __construct() {}

	static function create_db() {
		global $wpdb;
		$table = $wpdb->prefix . 'ti_import_lines';

		$result = $wpdb->query ("CREATE TABLE IF NOT EXISTS $table (
			id INT NOT NULL AUTO_INCREMENT,
			updated BOOLEAN,
			import_id INT NOT NULL,
			blog_id BIGINT(20),
			revision_id BIGINT(20),
			post_id BIGINT(20),
			PRIMARY KEY  (id),
			KEY import_id_key (import_id)
			) CHARACTER SET utf8
		");

		if ($result === false)
			return new WP_Error('fatal', "Error during create_db() on table $table");
	}


	static function delete($import_id) {
		global $wpdb;
		$table = $wpdb->prefix . 'ti_import_lines';
		$result = $wpdb->query($wpdb->prepare("DELETE FROM $table WHERE import_id = %d", $import_id));

		if ($result === false)
			return new WP_Error('fatal', "Error during delete from $table");
	}

	function save() {
		global $wpdb;
		$table = $wpdb->prefix . 'ti_import_lines';

		$result = $wpdb->query($wpdb->prepare("INSERT INTO $table (import_id, updated, blog_id, revision_id, post_id ) VALUES(%d, %d, %d, %d, %d)",
		$this->import_id, $this->updated, $this->blog_id, $this->revision_id, $this->post_id));

		if ($result === false)
			return new WP_Error('fatal', "Error during insert to $table");
	}
}



class TI_Template extends TI_Obj {
	var $_id,
		$blog_id_col,
		$blog_id,
		$post_title,
		$post_content,
		$post_excerpt,
		$post_type,
		$post_type_col,
		$post_parent,
		$post_parent_col,
		$page_template,
		$page_template_col,
		$menu_order,
		$menu_order_col,
		$post_status,
		$post_status_col,
		$comment_status,
		$comment_status_col,
		$post_author,
		$post_author_col,
		$post_name,
		$post_name_col,
		$postid_col,
		$update_field,
		$post_date_min,
		$post_date_max,
		$post_date_col,
		$thumbnail_col,
		$post_custom,
		$post_custom_new,
		$taxonomies
		;

	function __construct() {
	}

	static function create_db() {
		return parent::create_db('ti_template', false);
	}

	static function get($id) {
		return parent::get('ti_template', $id);
	}

	static function get_list() {
		return parent::get_list('ti_template');
	}

	static function delete($id) {
		return parent::delete('ti_template', $id);
	}

	function save() {
		return parent::save('ti_template');
	}

	function update($atts) {
		// Custom field data needs to be parsed from array of keys + array of cols into an associative array of (key => col)
		$post_custom = isset($atts['post_custom']) ? $atts['post_custom'] : null;
		if ($post_custom) {
			$keys = isset($post_custom['key']) ? $post_custom['key'] : null;
			$cols = isset($post_custom['col']) ? $post_custom['col'] : null;

			$atts['post_custom'] = array();
			foreach((array)$keys as $i => $key) {
				// Ignore blank key or column
				if (!empty($key) && isset($cols[$i]) && !empty($cols[$i]))
					$atts['post_custom'][$key] = $cols[$i];
			}
		}

		// New custom field data is an array of column names; convert it to an array of (key => col)
		// (where both values happen to be the column name)
		$post_custom_new = isset($atts['post_custom_new']) ? $atts['post_custom_new'] : null;
		if ($post_custom_new)
			$atts['post_custom_new'] = array_combine(array_values($post_custom_new), array_values($post_custom_new));

		parent::update($atts);
	}
}
?>