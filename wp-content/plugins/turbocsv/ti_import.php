<?php

/**

* Class to perform the import.

* Note:

*   -Template settings are always saved with the import so the import can be repeated.

* 	-List of posts associated with each import is saved in same table.  This is faster, but limits imports by memory size.

*/

class TI_Import extends TI_Obj {

	var $_id;

	var $status;

	var $template;

	var $user_id;

	var $timestamp;

	var $filepath;

	var $fileurl;

	var $filename;

	var $headers;

	var $logs;

	var $lines_total;

	var $lines_blank;

	var $lines_imported;

	var $errors;

	var $imported_terms;

	var $imported_posts;



	/**

	* Constructor.

	* Create a new import object and immediately save it to the database

	*

	*/

	function defaults() {

		global $current_user;

		get_currentuserinfo();



		return array(

			'user_id' => $current_user->ID,

			'timestamp' => time(),

			'template' => new TI_Template(),

			'status' => 'NEW',

			'lines_total' => 0,

			'lines_blank' => 0,

			'lines_imported' => 0,

			'errors' => 0

		);

	}





	function create_db() {

		return parent::create_db('ti_import', true);

	}



	function get($id) {

		return parent::get('ti_import', $id);

	}



	function get_list() {

		return parent::get_list('ti_import');

	}



	function delete($id) {

		return parent::delete('ti_import', $id);

	}



	function save() {

		return parent::save('ti_import');

	}



	function import_start() {

		global $wpdb;



		// For slow machines, try to increase the time limit to 2 hours

		$max_time = ini_get('max_execution_time');

		if ($max_time < 7200)

			set_time_limit(7200);

	}



	function import_end() {

	}



	/**

	* Workaround to problem in WP's 'clean_term_cache: standard version uses a static variable

	* so it really only cleans on first term/category/tag insert.  That works fine in interactive editing, but not in batch

	*

	* @param mixed $taxonomy

	*/

	function clean_term_cache() {

		// Clear taxonomy caches

		foreach((array)$this->imported_terms as $taxonomy => $terms) {

			wp_cache_delete('all_ids', $taxonomy);

			wp_cache_delete('get', $taxonomy);

			delete_option("{$taxonomy}_children");

			// Regenerate {$taxonomy}_children

			_get_term_hierarchy($taxonomy);

		}

	}



	/**

	* Process the import.

	*

	*/

	function import($callback=null) {

		global $wpdb;



		$options = get_option('turbocsv');

		$ti_max_skip = $options['ti_max_skip'];

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



		// Turn off cache functions before we begin

		$this->import_start();



		// Get a total line count for the progress bar

		$lines_total = count(file($this->filepath));



		$userd = get_userdata($this->user_id);

		$username = $userd->user_login;

		$this->log(__("Import started by: $username"), 'INFO');



		// Open the file

		$fp = $this->file_open();

		if (is_wp_error($fp))

			return $fp;



		// Throw away the header line

		fgetcsv($fp);



		// Read the input file line-by-line

		while (!feof($fp)) {



			// Read the next line

			$line = fgetcsv($fp);



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



			$line = $this->utf8_convert($line, $encoding);



			// Every 10 lines, call the callback function if one was provided (can be used for progressbars)

			$mod = $this->lines_imported % 10;

			if ($mod == 0 && $callback)

				call_user_func($callback, $this->lines_imported, round(100 * $this->lines_imported / $lines_total, 0));



			$result = $this->import_line($line);



			// If we had errors

			if (is_wp_error($result)) {

				$this->errors++;



				// Stop at maximum # of error lines

				if ($this->errors >= $ti_max_skip) {

					$this->log(__('Maximum number lines with errors, stopping import.'));

					break;

				}

			}



			// Commit once commit count is reached

			if (($this->lines_total % $commit_rows) == 0)

				$wpdb->query("COMMIT");

		}



		// Do a final progressbar at 100%

		if ($callback)

			call_user_func($callback, $this->lines_imported, 100);



		// Close the input file

		fclose($fp);



		// Turn cache functions back on

		$this->import_end();



		// Refresh term cache

		$this->clean_term_cache();



		// Log completion

		$this->log(__('Import finished.'), 'INFO');



		// Set final status

		if ($this->has_errors())

			$this->status = 'ERROR';

		else

			$this->status = 'COMPLETE';



		// Save import

		$this->save();



		return;

	}



	function utf8_convert($line, $encoding = null) {

		// Nothing to do if it's already utf8

		if ($encoding == 'utf8')

			return $line;



		foreach ($line as $key => $value) {

			if (!$encoding || $encoding == 'auto') {

				// Guess at encoding - we'll assume 1252

				if (utf8_encode(utf8_decode($value)) == $value)

					$utf8_line[$key] = $value;

				else

					$utf8_line[$key] = iconv("Windows-1252", 'UTF-8', $value);

			} else {

				// Explicit encoding

				$utf8_line[$key] = iconv($encoding, 'UTF-8', $value);

			}

		}

		return $utf8_line;

	}



	function import_line($line) {

		global $wpdb, $blog_id;



		$original_blog_id = $blog_id;



		// If post blog/site differs from current then switch to that blog

		$post_blog_id = $this->import_blog_id($line);

		if ($post_blog_id && $post_blog_id != $blog_id) {

			switch_to_blog($post_blog_id);

			$imported_post['blog_id'] = $post_blog_id;

		}



		// Replace the header tokens in the post body/title

		foreach($this->headers as $header)

			$tokens[$header] = '#' . $header . '#';



		$post['post_content'] = str_replace($tokens, $line, $this->template->post_content);

		$post['post_title'] = str_replace($tokens, $line, $this->template->post_title);

		$post['post_excerpt'] = str_replace($tokens, $line, $this->template->post_excerpt);



		// Validate post title & content

		if (empty($post['post_content']) && empty($post['post_title']))

			return $this->log_line(__('Post title and body are both empty.  WordPress does not allow empty posts.'));



		// Assign post date

		$result = $this->import_post_date($line);



		if (is_wp_error($result))

			return $result;

		else

			$post['post_date']  = $result;



		// Create and assign post categories

		$result = $this->import_post_categories($line);

		if (is_wp_error($result))

			return $result;

		else

			$post['post_category'] = $result;



		// Create and assign tags

		$result = $this->import_post_tags($line);

		if (is_wp_error($result))

			return $result;

		else

			$post['tags_input'] = $result;



		// Custom taxonomies

		$result = $this->import_post_taxonomies($line);

		if (is_wp_error($result))

			return $result;

		else

			$post['tax_input'] = $result;



		// Author

		$post['post_author'] = $this->import_post_author($line);



		// Assign other field values

		$post['comment_status'] = $this->template->comment_status;

		$post['post_status'] = $this->template->post_status;



		// Assign post type

		$result = $this->import_post_type($line);

		if (is_wp_error($result))

			return $result;

		else

			$post['post_type'] = $result;



		// Some fields only apply to pages

		if ($post['post_type'] == 'page') {

			$post['menu_order'] = $this->template->menu_order;

			$post['post_parent'] = $this->template->post_parent;    // Not currently validated!



			// Validate and assign page template

			if (!empty($this->template->page_template)) {

				$page_templates = get_page_templates();

				if ($this->template->page_template != 'default' && !in_array($this->template->page_template, $page_templates))

					return $this->log_line(sprintf(__('The page template "%s" is invalid.'), $this->template->page_template));

				else

					$post['page_template'] = $this->template->page_template;

			}

		}



		// Assign slug

		if ($this->template->post_name_col)

			$post['post_name'] = $this->read_line_col($line, $this->template->post_name_col);



		// Update or insert the post (update if ID is specified); note that on update we can only get true/false, not WP_ERROR

		if ($this->template->postid_col) {

			$update_id = $this->read_line_col($line, $this->template->postid_col);

			$post['ID'] = $update_id;



			// Get current maximum revision # using raw query - wp_get_post_revisions() is filtered

			$max_revision = $this->get_last_revision($update_id);

		} else {

			$update_id = null;

		}



		if ($update_id)

			$post_id = wp_update_post($post);

		else

			$post_id = wp_insert_post($post, true);



		// Any error from posting is fatal.  wp_insert gives a full error, wp_update just returns FALSE

		if (is_wp_error($post_id))

			return $this->log_line($post_id);

		elseif ($post_id === false)

			return $this->log_line(sprintf(__("Wordpress error when updating post id=%s"), $post_id));



		// If updating

		if ($update_id) {

			$revision = $this->get_last_revision($update_id);



			// If no revision was created, error (this can happen if post doesn't exist any more)

			if ($revision <= $max_revision || $revision == null)

				return $this->log_line(sprintf(__('Post ID %s could not be updated - check that the post still exists!'), $update_id));



			// Store the revision number for undo

			$imported_post['revision'] = $revision;

		}



		// Store post metadata

		$result = $this->import_post_meta($line, $post_id, isset($update_id));

		if (is_wp_error($result))

			return $result;



		// Switch back to original blog before writing out the import logs to the database

		if ($original_blog_id != $blog_id)

			switch_to_blog($original_blog_id);



		// Post was OK - save the post data to the import; content and excerpt are NOT saved to conserve memory

		$imported_post['ID'] = $post_id;

		$this->imported_posts[] = $imported_post;



		return true;

	}



	// Currently just read from template; eventually extend to input column as well

	function import_blog_id($line) {

		if ($this->template->blog_id)

			return $this->template->blog_id;



		return null;

	}





	function import_post_author($line) {

		switch ($this->template->post_author_type) {

			case 'FIXED':

				return $this->template->post_author;

				break;



		   case 'COLUMN':

				$data = $this->read_line_col($line, $this->template->post_author_col);

				if ($data===false)

					return $this->log_line(sprintf(__('Invalid column name for post author: %s'), $this->template->post_author_col));



				return $data;

				break;

		}

	}



	function import_post_type($line) {

		// Validate post type - note that this does NOT check if post type valid for current site!

		$index = array_search($this->template->post_type, get_post_types(array('public' => true), 'names'));

		if ($index === false || $index === null)

			return $this->log_line(sprintf(__('Invalid post type: "%s"'), $post_type));
		return $this->template->post_type;

	}



	function import_post_meta($line, $post_id, $update = false) {

		$options = get_option('turbocsv');



		// Add the metadata for new and existing custom fields

		$all_meta = array_merge((array)$this->template->post_custom, (array)$this->template->post_custom_new);



		// If the map POIs column hasn't been mapped already default it to a custom field of the same name

		$poi_col = $this->template->poi_col;

		if ($poi_col && !empty($poi_col)) {

			if (!isset($all_data[$poi_col]) || empty($all_data[$poi_col]))

				$all_data[$poi_col] = $poi_col;

		}



		// Add metadata - multiple values are allowed for the same key

		foreach ($all_meta as $key => $value) {



			// If updating, delete any existing values

			if ($update)

				delete_post_meta($post_id, addslashes($key));



			// If no new values, then skip this field

			if (empty($value))

				continue;



			$data = $this->read_line_col($line, $value);

			if ($data===false)

				return $this->log_line(sprintf(__('Invalid column name for custom field: %s'), $key));



			if (!empty($data)) {

				// Split multiple values into an array - POI field is split by hierarchy_separator, other fields by values_separator

				if ($poi_col && !empty($poi_col) && $key == $poi_col)

					$data = explode($options['hierarchy_separator'], $data);

				else

					$data = explode($options['values_separator'], $data);



				// Add each data value separately

				foreach((array)$data as $datum) {

					// WP stripslashes on POST when adding metadata so add slashes first!

					$result = add_post_meta($post_id, addslashes($key), addslashes($datum));

				}



				if ($result === false)

					return $this->log_line(sprintf(__('Error in add_post_meta for custom field: id="%s", key="%s", value="%s"'), $post_id, $key, $value));

			}

		}



		// Generate mappress map from POI field

		if ($poi_col && !empty($poi_col) && $key == $poi_col && class_exists('Mappress_Pro')) {

			global $mappress;

			$errors = $mappress->create_meta_map($post_id, $poi_col);



			// If there were geocoding errors log them

			if (!empty($errors)) {

				foreach($errors as $error)

					$this->log_line(sprintf(__('Error in map creation: %s'), $error->get_error_message()));

				return $this->log_line(__('Map may be incomplete for post id="%s"'), $post_id);

			}

		}



		return true;

	}



	/**

	* Get the post date for the input file line

	*/

	function import_post_date($line) {

		switch ($this->template->post_date_type) {

			case 'TODAY':

				// Use current time

				return the_time('Y-m-d H:i:s');

				break;





			case 'FIXED':

				// If type = FIXED then pick a random value between min and max dates

				// Convert min/max dates to a time() format using WordPress localization settings

				// NOTE: WP local time is NOT currently used for the other date/time settings like random dates

				$min = strtotime($this->template->post_date_min);



				// If max date = today, just use current time as the max.

				// Otherwise, we have to add a day (in seconds) to the given 'max' otherwise our range won't include that date

				if (date('m/d/Y', time()) == $this->template->post_date_max)

					$max = time();

				else

					$max = strtotime($this->template->post_date_max) + 86399;



				// Min/max will be false for invalid dates, i.e. if not MM/DD/YYYY

				if ($min === false)

					return $this->log_line(sprintf(__("Invalid mininum post date: '%s'"), $this->template->post_date_min));



				if ($max === false)

					return $this->log_line(sprintf(__("Invalid maximum post date: '%s'"), $this->template->post_date_max));



				// Min/max are OK, return a random date between them

				return date('Y-m-d H:i:s', strtotime('+' . rand(0, $max - $min) . 'seconds', $min));

				break;



		   case 'COLUMN':

				$data = $this->read_line_col($line, $this->template->post_date_col);

				if ($data===false)

					return $this->log_line(sprintf(__('Invalid column name for post date: %s'), $this->template->post_date_col));



				if (strtotime($data) === false)

					return $this->log_line(sprintf(__("Invalid post date in input file: '%s' in column %s"), $data, $this->template->post_date_col));



				return date('Y-m-d H:i:s', strtotime($data));

				break;

		}

	}



	/**

	* Get categories.  WP requires categories to be array of category IDs

	*/

	function import_post_categories($line) {

		// If type = FIXED then just return the category ID(s)

		switch ($this->template->post_category_type) {

			case 'FIXED':

				return $this->template->post_category;

				break;



			// If type = COLUMN we may have one or more categories in a single column

			case 'COLUMN':

				return $this->import_post_taxonomy('category', $line, $this->template->post_category_col);

				break;

		}

	}



/**

* Get tags.  WP requires tags to be comma-separated tag names

*

*/

	function import_post_tags($line) {

		$post_tags = array();



		switch ($this->template->post_tag_type) {

			case 'FIXED':

				// Get tags as strings from list of selected tag IDs

				$options = get_option('turbocsv');

				foreach((array)$this->template->post_tags as $tag_ID)

					$post_tags[] = get_tag($tag_ID)->name;

				return implode($options['values_separator'], $post_tags);

				break;



			case 'COLUMN':

				return $this->import_post_taxonomy('post_tag', $line, $this->template->post_tag_col);

				break;

		}

	}



	function import_post_taxonomies($line) {

		$post_taxonomies = array();



		foreach((array)$this->template->post_taxonomy_cols as $taxonomy_name => $taxonomy_col) {

			$result = $this->import_post_taxonomy($taxonomy_name, $line, $taxonomy_col);

			if (is_wp_error($result))

				return $result;



			if (!$result)

				continue;



			$post_taxonomies[$taxonomy_name] = $result;

		}



		return $post_taxonomies;

	}



	function import_post_taxonomy($taxonomy_name, $line, $col) {

		$options = get_option('turbocsv');



		// Get data from column

		$data = $this->read_line_col($line, $col);

		if ($data===false)

			return $this->log_line(sprintf(__('Invalid column name for %s: %s'), $taxonomy_name, $col));



		$data = trim($data);

		if (empty($data))

			return null;



		// Get taxonomy details

		$taxonomy = get_taxonomy($taxonomy_name);

		if (!$taxonomy)

			return $this->log_line(sprintf(__('Unkonown taxonomy "%s"'), $taxonomy_name));



		//  Split by separator



		$term_names1 = preg_split("/[" . $options['values_separator'] . "]+/", $data);



		if ($taxonomy->hierarchical) {



			// Hierarchical taxonomies: also split by "|" separator

			foreach($term_names1 as $term_name1) {

				// Split by hierarchy separator

				$term_names2 = preg_split("/[" . $options['hierarchy_separator'] . "]+/", $term_name1);

				$parent_id = null;



				foreach($term_names2 as $term_name) {

					// Create the term if it doesn't already exist

					$result = $this->insert_term($term_name, $taxonomy->name, $parent_id);

					if (is_wp_error($result))

						return $result;



					// Use this as parent for next term in hierarchy

					$parent_id = $result['term_id'];



					// Store array of terms for current post

					$post_taxonomy[] = $result['term_id'];

				}

			}

		} else {

			// Flat taxonomies

			$parent_id = null;

			foreach($term_names1 as $term_name) {

				// Create the term if it doesn't already exist

				$result = $this->insert_term($term_name, $taxonomy->name, $parent_id);

				if (is_wp_error($result))

					return $result;



				// Store term for current post

				$post_taxonomy[] = $term_name;

			}

			// For flat taxonomies convert the array of terms into a comma-separated list

			$post_taxonomy = implode($options['values_separator'], $post_taxonomy);

		}



		return $post_taxonomy;

	}



	function insert_term($term_name, $taxonomy_name, $parent_id = null) {

		$term_name = trim($term_name);



		// If the term already exists in the given taxonomy, with the given parent, there's nothing to do

		// term_exists returns 0 if term is invalid, null if not found, and an array if valid

		$term = term_exists($term_name, $taxonomy_name, $parent_id);

		if ($term) {

			return $term;

		}



		// Set the parent

		$args = array('parent' => $parent_id, 'description' => $term_name);



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

			'parent' => $parent_id,

			'parent_name' => $parent_name,

			'term_id' => $result['term_id'],

			'term_taxonomy_id' => $result['term_taxonomy_id'],

			'name' => $term_name);

		return $result;

	}





	function read_line_col($line, $col) {

		// Get the column index by searching header column names

		$index = array_search($col, $this->headers);

		if ($index === false)

			return false;

		else {

			if (isset($line[$index]))

				return $line[$index];

			else

				return "";          // If column is empty it won't be in the line

		}

	}



	function get_last_revision($postid) {

		global $wpdb;



		// Get maximum revision # using raw query - wp_get_post_revisions() is filtered

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

		$encoding = isset($options['encoding']) ? $options['encoding'] : null;



		// Read the first line to get headers

		$fp = $this->file_open();

		if (is_wp_error($fp))

			return $fp;



		$headers = fgetcsv($fp);



		// It's an error if there are no headers or if any header is empty

		if (empty($headers)) {

			fclose($fp);

			return $this->log(sprintf(__('Unable to read column headers from file %s'), $this->fileurl));

		}



		foreach ((array)$headers as $key => $header) {

			if (empty($header)) {

				fclose($fp);

				return $this->log(sprintf(__('Header for column %s is empty.  Empty headers are not allowed'), $key));

			}

		}



		$this->headers = $this->utf8_convert($headers, $encoding);

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



		$status = $statuses[$this->status];

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

	* Useful debug function.  For example:

	*   function xyz()

	*       $args = func_get_args();

	*       $this->debug('error:', $args);

	*/



	function debug($msg, $args) {

		if (get_option('ti_debug')) {

			if (is_array($args))

				$msg = $msg . implode(',', $args);

			else

				$msg = $msg . $args;



			$fp = fopen(plugins_url('turbocsv/debug.txt'), "a");

			fwrite($this->debug_path, $msgs);

			fclose($fp);

		}

	}

}    // End Class TI





class TI_Template extends TI_Obj {

	var $_id;

	var $post_title;

	var $post_content;

	var $post_excerpt;

	var $post_type;

	var $blog_id;

	var $postid_col;

	var $post_status;

	var $comment_status;

	var $post_author_type;

	var $post_author;

	var $post_author_col;

	var $post_name_col;

	var $post_parent;

	var $page_template;

	var $menu_order;

	var $post_date_type;

	var $post_date_min;

	var $post_date_max;

	var $post_date_col;

	var $post_category_type;

	var $post_category;

	var $post_category_col;

	var $post_tag_type;

	var $post_tag_col;

	var $post_tags;

	var $post_category_hier;

	var $post_custom;

	var $post_custom_new;

	var $post_taxonomy_cols;

	var $poi_col;



	function defaults() {

		global $current_user;



		return array (

			'post_type' => 'post',

			'post_status' => 'publish',

			'comment_status' => 'open',

			'post_author_type' => 'FIXED',

			'post_author' => $current_user->ID,

			'page_template' => 'default',   // yes, WP requires the string 'default'

			'menu_order' => 0,

			'post_date_type' => 'TODAY',

			'post_date_min' => date('m/d/Y'),

			'post_date_max' => date('m/d/Y'),

			'post_tag_type' => 'FIXED',

			'post_category_type' => 'FIXED',

		);

	}



	function create_db() {

		return parent::create_db('ti_template', false);

	}



	function get($id) {

		return parent::get('ti_template', $id);

	}



	function get_list() {

		return parent::get_list('ti_template');

	}



	function delete($id) {

		return parent::delete('ti_template', $id);

	}



	function save() {

		return parent::save('ti_template');

	}

}

?>