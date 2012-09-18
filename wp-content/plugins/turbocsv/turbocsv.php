<?php
/*
	Plugin Name: TurboCSV
	Plugin URI: http://www.wphostreviews.com/turbocsv
	Description: TurboCSV
	Version: 2.48
	Author: Chris Richardson
	Author URI: http://www.wphostreviews.com/turbocsv
*/
require_once dirname( __FILE__ ) . '/ti_obj.php';
require_once dirname( __FILE__ ) . '/ti_import.php';
@include_once dirname( __FILE__ ) . '/ti_pro.php';

class TI_Frontend {
	var $version = '2.48',
		$log_file,
		$option_defaults = array(
			'encoding' => 'auto',
			'input_delimiter' => ',',
			'max_skip' => 0,
			'commit_rows' => 100,
			'values_separator' => ',',
			'hierarchy_separator' => '|',
			'serialize_prefix' => "s:",
			'term_description_separator' => "::",
			'locale' => '',
			'custom_parser' => '',
			'max_log_records' => 500
		),
		$labels;

	function TI_Frontend() {
		global $wp_version, $wpdb;

		load_plugin_textdomain( 'turbocsv', false, dirname( plugin_basename( __FILE__ ) ) );
		$this->labels = $this->get_labels();

		add_action('admin_init', array(&$this, 'admin_init'));
		add_action('admin_menu', array(&$this,'hook_admin_menu'));
		register_activation_hook(__FILE__, array(&$this, 'activation'));

		// Set log file path
		$this->log_file = trailingslashit(dirname(__FILE__)) . "turbocsv.log";

		// Create db tables with each new blog created if the plugin is network activated (note the workaround in admin_init() hook until 3.1)
		add_action('wpmu_new_blog', array(&$this, 'wpmu_new_blog'));

		$this->debugging();

		// Set option defaults
		$options = get_option('turbocsv');
		$options = shortcode_atts($this->option_defaults, $options);

		update_option('turbocsv', $options);
	}

	// ti_errors -> PHP errors
	// ti_info -> phpinfo + dump
	function debugging() {
		global $wpdb;

		$options = get_option('turbocsv');

		if (isset($_GET['ti_debug'])) {
			// See here in codex: http://codex.wordpress.org/Editing_wp-config.php#Configure_Error_Log
			@error_reporting(E_ALL);
			@ini_set('error_reporting', E_ALL);
			@ini_set('display_errors', 1);

			$wpdb->show_errors();
			if (!defined('WP_DEBUG')) {
				@define('WP_DEBUG', true);
			}
		}

		if (isset($_GET['ti_info'])) {
			$bloginfo = array('version', 'language', 'stylesheet_url', 'wpurl', 'url');
			echo "<br/><b>bloginfo</b><br/>";
			foreach ($bloginfo as $key=>$info)
				echo "$info: " . bloginfo($info) . "<br/>";
			echo "<br/><b>options</b><br/>";
			$options = get_option(turbocsv);
			print_r($options);
			echo "<br/><b>phpinfo</b><br/>";
			phpinfo();
			echo "<br/><b>Imports</b><br/>";
			$import_ids = TI_Import::get_list();
			foreach ($import_ids as $import_id) {
				$import = TI_Import::get($import_id);
				echo $import_id . ' | ' . $import->timestamp . ' | ' . $import->status . ' | ' . get_class($import);
				if (get_class($import) == 'stdClass' || is_wp_error($import))
					echo " <span class='error'>INVALID: class is StdClass</span>";
				echo "<br/>";
			}
		}

		// Force deletion of invalid import history records
		// Records can become invalid if structure changes.  Also, in some cases customers are unable to unserialize() previously serialized data
		// - this appears to be due to switching hosts/PHP versions, rather than a problem that can be fixed in the plugin
		if (isset($_GET['ti_delete'])) {
			$import_ids = TI_Import::get_list();
			foreach ($import_ids as $import_id) {
				$import = TI_Import::get($import_id);
				echo $import_id . ' | ' . $import->timestamp . ' | ' . $import->status . ' | ' . get_class($import);
				if (get_class($import) == 'stdClass' || is_wp_error($import)) {
					echo " <span class='error'>DELETING</span>";
					TI_Import::delete($import_id);
					TI_Import_Line::delete($import_id);
				}
				echo "<br/>";
			}
		}
	}

	/**
	* Activate plugin.
	* For multisite network activation this calls activate_site () for each blog.
	* This is a workaround for a WP bug - not likely to have fix until 3.2: http://core.trac.wordpress.org/ticket/14170
	*
	*/
	function activation() {
		global $wpdb;

		// Handle network activation
		if (is_multisite() && is_plugin_active_for_network(plugin_basename(__FILE__))) {
			// Save current blog
			$old_blog = $wpdb->blogid;

			// Get all blog ids
			$blogids = $wpdb->get_col($wpdb->prepare("SELECT blog_id FROM $wpdb->blogs"));

			// Activate each blog
			foreach ($blogids as $blog_id) {
				switch_to_blog($blog_id);
				$this->activate_site();
			}

			// Switch back to original blog
			switch_to_blog($old_blog);
			return;
		} else {
			$this->activate_site();
		}
	}

	/**
	* Activate a single site
	*
	*/
	function activate_site() {
		TI_Import::create_db();
		TI_Import_Line::create_db();
		TI_Template::create_db();

		// Upgrade old templates for 2.21
		$old_version = get_option('turbocsv_version');

		if (!$old_version || $old_version < '2.21')
			$this->activation_221();

		// Remember the current version
		update_option('turbocsv_version', $this->version);
	}

	/**
	* If plugin is network-active in a multisite installation then activate the plugin when a new blog is created
	*
	* @param mixed $blog_id
	*/
	function wpmu_new_blog($blog_id) {
		// If a new blog is created AND the plugin is network activated, then activate it for the new blog as well
		if (is_multisite() && is_plugin_active_for_network(plugin_basename(__FILE__))) {
			switch_to_blog($blog_id);
			$this->activate_site();
			restore_current_blog();
		}
	}

	function activation_221() {
		$import_ids = TI_Import::get_list();
		foreach ($import_ids as $import_id) {
			$import = TI_Import::get($import_id);
			if ($import) {
				$import->template = $this->update_template_221($import->template);
				$import->save();
			}
		}

		$template_ids = TI_Template::get_list();
		foreach ($template_ids as $template_id) {
			$template = TI_Template::get($template_id);
			if ($template) {
				$template = $this->update_template_221($template);
				$template->save();
			}
		}
	}


	function update_template_221($template) {
		if (isset($template->post_category_type)) {

			if ($template->post_category_type == 'COLUMN' && isset($template->post_category_col) && !empty($template->post_category_col))
				$template->taxonomies['category'] = array('type' => 'COLUMN', 'col' => $template->post_category_col);

			if ($template->post_category_type == 'FIXED' && isset($template->post_category) && !empty($template->post_category))
				$template->taxonomies['category'] = array('type' => 'FIXED', 'values' => $template->post_category);
		}

		if (isset($template->post_tag_type)) {
			if ($template->post_tag_type == 'COLUMN' && isset($template->post_tag_col) && !empty($template->post_tag_col))
				$template->taxonomies['post_tag'] = array('type' => 'COLUMN', 'col' => $template->post_tag_col);
			if ($template->post_tag_type == 'FIXED' && isset($template->post_tags) && !empty($template->post_tags))
				$template->taxonomies['post_tag'] = array('type' => 'FIXED', 'values' => $template->post_tags);
		}

		if (isset($template->post_taxonomy_cols) && !empty($template->post_taxonomy_cols)) {
			foreach((array) $template->post_taxonomy_cols as $tax_col_name => $tax_col)
				$template->taxonomies[$tax_col_name] = array('type' => 'COLUMN', 'col' => $tax_col);
		}

		return $template;
	}

	function admin_init() {
		// There's a WP bug - wpmu_new_blog action is not triggered if using activation (WP bug: http://core.trac.wordpress.org/ticket/14718)
		// Workaround below is to check if we need to activate every time the plugin runs
		if (is_plugin_active_for_network(plugin_basename(__FILE__))) {
			if (!get_option('turbocsv_version'))
				$this->activate_site();
		}
	}

	function hook_admin_menu() {
		// Add a single menu (the '' parent slug will create the menu but hide it on-screen)
		$pages[] = add_management_page(__('TurboCSV'), __('TurboCSV'), 'import', 'ti_new', array(&$this, 'control'));
		$pages[] = add_submenu_page('', 'TurboCSV - View Import', 'ti_display', 'import', 'ti_display', array(&$this, 'control'));
		$pages[] = add_submenu_page('', __('TurboCSV - Import History'), __('History'), 'import', 'ti_history', array(&$this, 'control'));
		$pages[] = add_submenu_page('', __('TurboCSV - Settings'), __('Settings'), 'import', 'ti_settings', array(&$this, 'control'));

		// Load scripts/styles for our pages only
		foreach ($pages as $page) {
			add_action('admin_print_scripts-' . $page, array(&$this, 'print_scripts'));
			add_action('admin_print_styles-' . $page, array(&$this, 'print_styles'));
	   }
	}

	function print_scripts() {
		wp_enqueue_script('tinytips', plugins_url("/tinytips/tinytips.js", __FILE__), false, '1.1', false);
		wp_enqueue_script('turbocsv', plugins_url("/turbocsv.js", __FILE__), false, $this->version, false);
		wp_enqueue_script('dashboard');  // Collapsible menus
	}

	function print_styles() {
		wp_enqueue_style("turbocsv", plugins_url("/turbocsv.css", __FILE__), FALSE, $this->version);
	}

	function get_template_from_columns($import) {

		$meta_keys = $this->get_meta_keys();

		foreach($import->headers as $header) {
			// Try to match based on TRIMMED header (i.e. even if user left spaces before/after)
			switch(trim($header)) {
				case '!blog_id':
					// It's only allowed to import a blog_id from the main site, otherwise just ignore it
					if (is_multisite() && is_main_site()) {
						$import->template->blog_id_col = $header;
					}
					break;

				case '!post_title':
					$import->template->post_title = "#$header#";
					break;

				case '!post_content':
					$import->template->post_content = "#$header#";
					break;

				case '!post_excerpt':
					$import->template->post_excerpt = "#$header#";
					break;

				case '!post_type':
				case '!post_parent':
				case '!page_template':
				case '!menu_order':
				case '!post_status':
				case '!comment_status':
				case '!post_author':
				case '!post_name':
				case '!post_date':
					$col = str_replace('!', '', $header) . '_col';
					$import->template->$col = $header;
					break;

				case '!post_thumbnail':
					$import->template->thumbnail_col = $header;
					break;

				case '!id':
					$import->template->postid_col = $header;
					break;

				case '!post_category':
					$import->template->taxonomies['category'] = array('col' => $header, 'values' => array());
					break;

				case '!tags_input':
					$import->template->taxonomies['post_tag'] = array('col' => $header, 'values' => array());
					break;

				default:
					// If first character = '!', it's a custom taxonomy, otherwise it's a custom field
					if (substr($header, 0, 1) == '!') {
						$taxonomy_name = (substr($header, 1));
						$import->template->taxonomies[$taxonomy_name] = array('type' => 'COLUMN', 'col' => $header, 'values' => array());
					} else {
						// Custom field - default it if it already exists, otherwise leave it unselected (for a new custom field)
						if (array_search($header, $meta_keys) !== false)
							$import->template->post_custom[$header] = $header;
					}
			}
		}
	}

	function control() {
		global $blog_id;
		$_POST = stripslashes_deep($_POST);     // Strip out slashes added by wordpress
		$page = (isset($_GET['page'])) ? $_GET['page'] : null;
		$id = (isset($_GET['id'])) ? $_GET['id'] : null;
		$cmd = (isset($_GET['cmd'])) ? $_GET['cmd'] : null;
		$noheader = (isset($_GET['noheader'])) ? true : false;

		$to = add_query_arg(array('page' => $page, 'id' => $id));

		switch ($page) {
			case 'ti_new':
				if (isset($_POST['button_file_upload']) || isset($_POST['button_file_url']) || isset($_POST['button_file_path'])) {

					// Create an import object
					$import = new TI_Import();

					// File upload
					$input_file = (isset($_FILES['input_file'])) ? $_FILES['input_file'] : null;
					$server_url = (isset($_POST['server_url'])) ? $_POST['server_url'] : null;
					$server_path = (isset($_POST['server_path'])) ? $_POST['server_path'] : null;
					$result = $import->upload_file($input_file, $server_url, $server_path);
					if(is_wp_error($result)) {
						$error = $result->get_error_message();
						break;
					}

					$this->get_template_from_columns($import);
					$import->save();

					$to = add_query_arg(array('page' => 'ti_display', 'id' => $import->_id), $to);
					break;
				}

				if (!$noheader) {
					$this->view_header($page);
					$this->view_upload();
					$this->view_footer();
				}
				break;

			case 'ti_display':
				$import = TI_Import::get($id);

				if (is_wp_error($import) || !$import)
					wp_die(sprintf(__('Internal error.  Unable to read import with id=%s: %s'), $id, $import->get_error_message() ));

				// If the import has just finished - WP clean_term_cache() has internal bug: it uses a static variable
				// so cache cannot be cleared until a new page is displayed.  See http://core.trac.wordpress.org/ticket/14485
				// The ticket has been open for 17 months...
				if (isset($_GET['cache']))
					$import->clean_term_cache();

				// Process the import
				if (isset($_POST['button_process'])) {
					// Update the template with POST variables
					if (isset($_POST['template']))
						$import->template->update($_POST['template']);

					$import->import();

					$message = __("Import processed");
					break;
				}

				// Save template
				if (isset($_POST['button_save_template'])) {
					$save_id = $_POST['template_save_id'];
					if (!$save_id) {
						$error = __('Enter a template name.');
						break;
					}

					// Update import from $_POST and assign ID
					$import->template->update($_POST['template']);
					$import->template->_id = $save_id;

					// Save the import
					$result = $import->save();
					if (is_wp_error($result)) {
						$error = __('Error saving import: ') . $result->get_error_message('ERROR');
						break;
					}

					// Save the template
					$result = $import->template->save();
					if (is_wp_error($result)) {
						$error = __('Error saving template: ') . $result->get_error_message('ERROR');
						break;
					}

					// Everything was OK
					$message = __('Template saved.');
					break;
				}

				// Load template
				if (isset($_POST['button_load_template'])) {
					$load_id = $_POST['template_load_id'];

					// If 'new template' then create a new one; otherwise load the specified template
					if (!$load_id) {
						$import->template = new TI_Template();
						$this->get_template_from_columns($import);
					} else {
						$template = TI_Template::get($load_id);

						if (is_wp_error($template)) {
							$error = __('Error loading template: ') . $template->get_error_messages();
							break;
						} else {
							$import->template = $template;
						}
					}

					// Assign the template to the import and save the import
					$result = $import->save();
					if (is_wp_error($result)) {
						$error = __('Error saving import: ') . $result->get_error_messages();
						break;
					}

					// Everything was OK
					 $message = __('Template loaded');
					 break;
				}

				// Delete template
				if (isset($_POST['button_delete_template'])) {
					$result = TI_Template::delete($_POST['template_load_id']);
					if (is_wp_error($result)) {
						$error = __('Error deleting template: ') . $result->get_error_messages();
						break;
					}

					// Set the import to a blank template and save the import
					$import->template = new TI_Template();
					$result = $import->save();
					if (is_wp_error($result)) {
						$error = __('Error saving import: ') . $result->get_error_messages();
						break;
					}

					// Everything was OK
					 $message = __('Template deleted');
					 break;
				}

				if (!$noheader) {
					$this->view_header($page, $import);
					$this->view_display($import);
					$this->view_footer($import);
				}

				break;

			case 'ti_history':
				// Handle commands from history section
				if ($noheader) {
					$to = add_query_arg(array('cmd' => null, 'id' => null), $to);

					switch ($cmd) {
						case 'delete':

							$result = TI_Import::delete($id);
							if (is_wp_error($result)) {
								$error = $result->get_error_message();
							} else {
								$result = TI_Import_Line::delete($id);
								if (is_wp_error($result))
									$error = $result->get_error_message();
								else
									$message = __('Import deleted.');
							}
							break;

						case 'undo':
							if (method_exists('TI_Pro', 'undo')) {
								$result = $this->undo($id);
								if (is_wp_error($result))
									$error = $result->get_error_message();
								else
									$message = __('Undo complete.  Check import log for any messages.');

								$to = add_query_arg(array('page' => 'ti_display', 'id' => $id), $to);
							} else {
								$error = __('Sorry, undo is only available in the PRO version');
							}

							break;
					}
				} else {
					$this->view_header($page);
					$this->view_history();
					$this->view_footer();
				}

				break;

			case 'ti_settings':
				// Save settings
				if (isset($_POST['save']) && isset($_POST['turbocsv'])) {
					$options = shortcode_atts($this->option_defaults, $_POST['turbocsv']);
					update_option('turbocsv', $options);
					$message = __('Options saved');
					break;
				}

				if (isset($_POST['reset'])) {
					$options = $this->option_defaults;
					update_option('turbocsv', $options);
					$message = __('Options reset');
					break;
				}

				if (!$noheader) {
					$this->view_header($page);
					$this->view_settings();
					$this->view_footer();
				}

				break;
		}

		// Save any error/warning messages for display
		if (isset($error))
			update_option('turbocsv_error', $error);
		if (isset($message))
			update_option('turbocsv_message', $message);

		// Redirect to next page if needed
		if ($noheader) {
			$to = add_query_arg(array('noheader' => null), $to);
			wp_redirect($to);
			exit();
		}
	}

	function view_upload() {
		// Max filesize
		$bytes = apply_filters( 'import_upload_size_limit', wp_max_upload_size() );
		$size = round($bytes / 1048576 , 2) . " MB";

		echo $this->memory_usage(true);

		$this->postbox_start(__('New Import'), "newimport");
		?>
			<p>
				<?php
					printf(__('<p>Specify the input file below.  Based on your PHP settings the maximum file size is: <b>%s</b>.</p>' ), $size );
				?>
			</p>
			<p>
				<?php
					echo __('<p>Files must be in .CSV format with headers in the first row')
					. " (" . __('see') . " <a href='" . plugins_url("/sample.csv", __FILE__) . "'>" . __('sample .csv') . "</a>"
					. ", <a href='" . plugins_url("/sample.xlsx", __FILE__) . "'>" . __('sample .xlsx') . "</a>"
					. " or <a href='" . plugins_url("/sample2.xlsx", __FILE__) . "'>" . __('more examples') . "</a> )</p>";
				?>
			</p>

			<form method='post' action='<?php echo esc_attr(add_query_arg( 'noheader', 'true' )); ?>' enctype='multipart/form-data'>
				<table class="form-table">
					<tr valign='top'><th scope='row'><?php _e('PC File:')?></th>
						<td>
							<input type="file" name="input_file" size="25" />
							<input type="hidden" name="max_file_size" value="<?php echo $bytes; ?>" />
							<input type="submit" class="button" name='button_file_upload' value="<?php _e('Load File')?>" />
						</td>
					</tr>
				</table>
			</form>

			<form method='post' action='<?php echo esc_attr(add_query_arg( 'noheader', 'true' )); ?>'>
				<table class="form-table">
					<tr valign='top'><th scope='row'><?php _e('File URL:')?></th>
						<td>
							<input type='text' name='server_url' size='25' />
							<input type='submit' class='button' name='button_file_url' value='<?php _e('Load file')?>' />
						</td>
					</tr>
				</table>
			</form>

			<form method='post' action='<?php echo esc_attr(add_query_arg( 'noheader', 'true' )); ?>'>
				<table class="form-table">
					<tr valign='top'><th scope='row'><?php _e('Server file path:')?></th>
						<td>
							<input type='text' name='server_path' size='25' />
							<input type='submit' class='button' name='button_file_path' value='<?php _e('Load file')?>' />
						</td>
					</tr>
				</table>
			</form>
		<?php
		$this->postbox_end();
	}

	function view_display($import) {
		// If the import is in status 'new' then re-read the file headers in case they've changed
		if ($import->status == 'NEW')
			$import->read_headers();

		?>
			<form method='post' id='form_import' action='<?php echo esc_attr(add_query_arg( array('noheader' => 'true', 'page' => 'ti_display' ))); ?>'>
				<?php $this->view_status($import); ?>
				<?php $this->view_template($import); ?>
			</form>
		<?php
	}

	function view_template($import) {
		// Non-pro template
	}

	function view_status($import) {
		global $wpdb, $blog_id;

		$options = get_option('turbocsv');

		$original_blog_id = $blog_id;

		switch ($import->status) {
			case 'NEW':
				$postbox_title = __('New Import');
				break;
			default:
				$postbox_title = __('Import Results');
		}

		$this->postbox_start($postbox_title, "status");

		echo "<input type='hidden' name='id' value='<?php echo $import->_id?>' />";
		echo "<table class='form-table'>";

		$this->setting(__('Status'), $import->get_status(true, true));

		if ($import->fileurl)
			$this->setting(__('File URL'), "<a href='{$import->fileurl}'>{$import->fileurl}</a>");

		if ($import->filepath)
			$this->setting(__('File path'), $import->filepath);

		if ($import->status == 'NEW') {
			$html = "<input type='submit' class='button-primary' name='button_process' value='" . __('Start the Import!') . "' />"
				."<span id='ti_twizzler' class='ti-hidden ti-twizzler'>"
				. "<img alt='Please wait...' src='" . plugins_url("/images/saving.gif", __FILE__) . "' /> "
				. __('Importing...')
				. "</span><div><br/></div>";
			$this->setting('', $html);
		} else {
			// Memory
			$this->setting(__("Peak memory usage"),  round( $import->max_memory / 1024 / 1024, 2 ) . " MB" );

			// Taxonomies
			foreach((array)$import->imported_terms as $taxonomy => $terms) {

				// If the taxonomy exists in current blog get the label, otherwise show the id
				$tax_obj = get_taxonomy($taxonomy);
				if ($tax_obj)
					$label = $tax_obj->label;
				else
					$label = $taxonomy;
				$this->setting($label, $this->get_term_links($taxonomy, $terms));
			}

			// Imported posts
			foreach((array)$import->get_imported_posts() as $post) {

				// Switch to the imported blog ID if there was one
				if (is_multisite() && $post['blog_id'] && $post['blog_id'] != $blog_id)
					switch_to_blog($post['blog_id']);

				// Get the most recent title, etc. for the post - it may have changed since the import
				$edit_link = get_edit_post_link($post['post_id']);
				$title = get_the_title($post['post_id']);
				if (empty($title))
					$title = '(' . __('no title') . ')';

				// 'revision' = null means no update, but revision= FALSE or revision=postID means updated
				$action = (isset($post['updated']) && $post['updated']) ? __('Updated') : __('Created');

				if ($edit_link)
					$edit_link = "<a href='$edit_link' title='$title'>$title</a>";
				else
					$edit_link = '(deleted)';

				if (is_multisite())
					$post_links[] = array('data' => array($post['blog_id'], $post['post_id'], $edit_link, $action));
				else
					$post_links[] = array('data' => array($post['post_id'], $edit_link, $action));

				// Switch back to original blog ID
				if (is_multisite() && $blog_id != $original_blog_id)
					switch_to_blog($original_blog_id);

				if (count($post_links) >= $options['max_log_records'])
					break;
			}

			if (is_multisite())
				$headers = array(__('Blog ID'), __('Post ID'), __('Title'), __('Action'));
			else
				$headers = array(__('Post ID'), __('Title'), __('Action'));

			if (empty($post_links))
				$html = __('None');
			else
				$html = "<div class='ti-small-scroll'>" . $this->option_table($headers, $post_links) . "</div>";

			if (count($import->get_imported_posts() ) > $options['max_log_records']) {
				$html .= "<div>";
				$html .= sprintf(__('Displaying %d of %d records'), $options['max_log_records'], count($import->get_imported_posts() ));
				$html .= __(' (you can increase this in the plugin settings)');
				$html .= "</div>";
			}

			$this->setting(__('Posts/pages'), $html);

			// Import log, sorted with most recent first
			$messages = $import->logs;
			if (is_array($messages))
				krsort($messages);

			$rows = null;
			foreach ((array)$messages as $message) {
				switch ($message->code) {
					case "WARNING":
						$class = "updated";
						$msg = "WARNING: " . $message->msg;
						break;

					case "ERROR":
						$class = "error";
						$msg = "ERROR: " . $message->msg;
						break;

					default:
						$class = "";
						$msg = $message->msg;
						break;
				}

				$date = str_replace(' ', '&nbsp;', date("M d, Y G:i:s", $message->time)); // Fill date w/nbsp so it doesn't wrap in table
				$rows[] = array('class' => $class, 'data' => array($date, $message->line, $msg));
			}

			$html = "<div class='ti-small-scroll'>" . $this->option_table(array(__('Time'), __('Line'), __('Message')), $rows) . "</div>";
			$this->setting(__('Log'), $html);
		}

		echo "</table>";
		$this->postbox_end();
	}

	function get_term_links($taxonomy, $terms) {
		global $blog_id;

		$original_blog_id = $blog_id;

		foreach((array)$terms as $term) {
			// Switch blog ID if the term was imported to a different blog, in order to get the correct link
			if (is_multisite() && $term->blog_id)
				switch_to_blog($term->blog_id);

			// If the term still exists show the current name & link, otherwise use the original name
			$wp_term = get_term($term->term_id, $taxonomy);
			if (!is_wp_error($wp_term) && $wp_term)           // get_term() returns either wp_error or null
				$term->name = $wp_term->name;

			// Get parent name
			if ($term->parent) {
				$wp_parent = get_term($term->parent, $taxonomy);
				if (!is_wp_error($wp_parent) && $wp_parent)  // get_term returns either wp_error or null
					$term->parent_name = $wp_parent->name;
			}

			if ($term->parent_name)
				$description = $term->parent_name . " - " . $term->name;
			else
				$description = $term->name;

			$links[] = sprintf("<a href='%s' title='%s (%d)'>%s</a>",
				admin_url('edit-tags.php?taxonomy=' . $taxonomy),
				esc_attr($description),
				$term->term_id,
				esc_attr($description));
		}

		// Switch back to original blog ID
		if (is_multisite() && $blog_id != $original_blog_id)
			switch_to_blog($original_blog_id);

		if (isset($links))
			return implode(", ", $links);
		else
			return 'none';
	}

	function view_history() {
		// Get list of past imports
		$ids = TI_Import::get_list();

		if (!empty($ids))
			krsort($ids);

		$this->postbox_start(__('Import History'), 'history');
		?>
			<?php
				foreach ((array)$ids as $id) {
					$import = TI_Import::get($id);

					// Skip import if it's invalid...this has sometimes happened with invalid unserialize() calls - see notes for ti_delete
					if (!$import || is_wp_error($import))
						continue;

					$date_link = "<a href='" . esc_attr(add_query_arg(array('noheader'=>null, 'page' => 'ti_display', 'id' => $import->_id))) . "'>"
						. date("M d, Y G:i:s", $import->timestamp) . "</a>";

					$delete_link = "<a class='ti_delete_link' href='" . esc_attr(add_query_arg(array('noheader'=>'true', 'cmd' => 'delete', 'id' => $import->_id))) . "'>" . __('Delete') . "</a>";

					if ($import->status == 'COMPLETE' || $import->status == 'ERROR')
						$undo_link = " | <a class='ti_undo_link' href='" . esc_attr(add_query_arg(array('noheader'=>'true', 'cmd' => 'undo', 'id' => $import->_id))) . "'>" . __('Undo') . "</a>";
					else
						$undo_link = "";

					$rows[] = array('class' => null, 'data' => array(
						$import->get_status(false, true),
						$import->filename,
						$date_link,
						$import->lines_total,
						$delete_link . $undo_link
						)
					);
				}

				if (!isset($rows))
					$rows[] = array('class' => null, 'data' => array(__('No imports', 'turbocsv'), '', '', '', ''));

				$html = "<div class='ti-large-scroll'>" . $this->option_table(array(__('Status'), __('File Name'), __('Date'), __('Lines'), __('Action')), $rows) . "</div>";
				echo $html;
			?>
		<?php
		$this->postbox_end();
	}

	function view_settings() {
		$options = get_option('turbocsv');
		extract($options);

		$encoding_values = array(
			'auto' => 'Auto-detect',
			'utf-8' => 'UTF-8',
			'ISO-8859-1' => 'ISO-8859-1',
			'ISO-8859-2' => 'ISO-8859-2',
			'Windows-1250' => 'Windows-1250 (Polish, Czech, Slovak, Hungarian, Slovene, Serbian, Croatian, Romanian and Albanian)',
			'Windows-1251' => 'Windows-1251 (Cyrillic)',
			'Windows-1252' => 'Windows-1252 (ANSI)',
			'Windows-1253' => 'Windows-1253 (Greek)',
			'Windows-1254' => 'Windows-1254 (Turkish)',
			'Windows-1255' => 'Windows-1255 (Hebrew)',
			'Windows-1256' => 'Windows-1256 (Arabic)',
			'Windows-1257' => 'Windows-1257 (Baltic)',
			'Windows-1258' => 'Windows-1258 (Vietnamese)'
		);

		$input_delimiter_values = array(',' => ',', ';' => ';', '|' => 'pipe (|)', 'tab' => "tab (\\t)");
		$max_skip_values = array('0' => '- none -', '10' => '10', '50' => '50', '100' => '100', '999999' => 'Unlimited');

		$log_file_link = "<a href='" . $this->log_file . "'>Log File</a>";

		echo $this->memory_usage(true);

		$this->postbox_start(__('Options'), 'options');
		?>
			<form method="post" action="<?php echo esc_attr(add_query_arg( 'noheader', 'true' )); ?>">
				<?php wp_nonce_field('ti-options'); ?>

				<table class="form-table">
					<?php
						$html = $this->memory_usage(false);
						$this->setting(__('Memory usage'), $html);

						$html = $this->dropdown('turbocsv[encoding]', $encoding, $encoding_values, false);
						$tip = __("TurboCSV will try to auto-detect both UTF-8 and Windows files or you can use this field to force it to expect a specific encoding");
						$this->setting(__('Input file encoding'), $html, $tip);

						$html = $this->dropdown('turbocsv[input_delimiter]', $input_delimiter, $input_delimiter_values, false);
						$tip = __("This is the field separator (default is the comma).  Select a semicolon for European-style CSV files.");
						$this->setting(__('Field separator'), $html, $tip);

						$html = $this->dropdown('turbocsv[max_skip]', $max_skip, $max_skip_values, false);
						$tip = __("Enter the maximum number of errors to ignore before the import is stopped");
						$this->setting(__('Skip lines with errors'), $html, $tip);

						$html = $this->text('turbocsv[commit_rows]', $commit_rows, 3);
						$tip = __('Number of rows to process before changes are committed to the database (default is 100 rows');
						$this->setting(__('Commit rows'), $html, $tip);

						$html = $this->text('turbocsv[values_separator]', $values_separator, 1);
						$tip = __('Delimiter for multiple values in a single field (default is a comma for tags, categories and custom fields).  If your data contains commas, change this value or escape the commas with a backslash');
						$this->setting(__('Values separator'), $html, $tip);

						$html = $this->text('turbocsv[hierarchy_separator]', $hierarchy_separator, 1);
						$tip = __('Values separator for hierarchical taxonomies.');
						$this->setting(__('Hierarchy separator'), $html, $tip);

						$html = $this->text('turbocsv[serialize_prefix]', $serialize_prefix, 1);
						$tip = __('Prefix for serialized values.  Use this prefix in custom fields data to force a serialized array.  For example: "s:1#2#3" will load serialized array (1,2,3).  Note that data imported this way is *only* usable by plugins, you will not see it on the WordPress post edit screens.');
						$this->setting(__('Serialize prefix'), $html, $tip);

						$html = $this->text('turbocsv[term_description_separator]', $term_description_separator, 1);
						$tip = __('When importing terms, use this seprator if importing both the term and its description.  For example, "A::B" will import term "A" with description "B"');
						$this->setting(__('Term / description separator'), $html, $tip);

						$html = $this->text('turbocsv[locale]', $locale, 20);
						$tip = __('Only check this if using an old version of PHP with a buggy fgetcsv() function');
						$this->setting(__('Locale'), $html, $tip);

						$html = $this->checkbox('turbocsv[custom_parser]', $custom_parser);
						$tip = __('Only check this if using an old version of PHP with a buggy fgetcsv() function');
						$this->setting(__('Custom parser'), $html, $tip);

						$html = $this->text('turbocsv[max_log_records]', $max_log_records, 4);
						$tip = __('Maximum number of imported posts to show in the import log');
						$this->setting(__('Max. posts to display'), $html, $tip);
					?>
				</table>

				<p class="submit">
					<input type="submit" name="save" class="button-primary" value="<?php _e('Save Changes') ?>">
					<input type="submit" name="reset" class="button" value="<?php _e('Reset Defaults') ?>">
				</p>
			</form>
		<?php
		$this->postbox_end();
	}

	function view_header($page, $import=null) {
		$new_link = "<a href='" . esc_attr(add_query_arg(array('page' => 'ti_new', 'id' => null))) . "'>" . __('New Import') . "</a>";
		$history_link = "<a href='" . esc_attr(add_query_arg(array('page' => 'ti_history', 'id' => null))) . "'>" . __('History') . "</a>";
		$settings_link = "<a href='" . esc_attr(add_query_arg(array('page' => 'ti_settings', 'id'=>null))) . "'>" . __('Settings') . "</a>";
		$bug_link = "<a href='http://wphostreviews.com/chris-contact'>" . __("Support") . "</a>";
		$help_link = "<a href='http://wphostreviews.com/turbocsv/turbocsv-documentation'>" . __("Documentation") . "</a>";

		// Main 'wrap' div and postbox container
		echo "<div class='wrap'>";
		$this->postbox_container_start();

		wp_nonce_field('ti-import');

		echo "<h2>TurboCSV <span style='font-size: 12px'>$this->version</span></h2>";
		echo "<div class='ti-help'>$help_link | $bug_link<br/></div>";

		$new_link = "<a href='" . esc_attr(add_query_arg(array('page' => 'ti_new', 'id' => null))) . "'>" . __('New Import') . "</a>";
		$history_link = "<a href='" . esc_attr(add_query_arg(array('page' => 'ti_history', 'id' => null))) . "'>" . __('History') . "</a>";
		$settings_link = "<a href='" . esc_attr(add_query_arg(array('page' => 'ti_settings', 'id'=>null))) . "'>" . __('Settings') . "</a>";

		switch ($page) {
			case 'ti_new':
				$new_link = "<b>" . $new_link . "</b>";
				break;

			case 'ti_display':
			case 'ti_history':
				if (isset($import) && $import->status == 'NEW')
					$new_link = "<b>" . $new_link . "</b>";
				else
					$history_link = "<b>" . $history_link . "</b>";
				break;

			case 'ti_settings':
				$settings_link = "<b>" . $settings_link . "</b>";
				break;
		}

		echo "<div>" . $new_link . " | " . $history_link . " | " . $settings_link . "</div><br/>";

		$error = get_option('turbocsv_error');
		delete_option('turbocsv_error');
		$message = get_option('turbocsv_message');
		delete_option('turbocsv_message');

		if ($message)
			echo "<div id='message' class='updated fade'><p>$message</p></div>";

		if ($error)
			echo "<div id='error' class='error'><p>$error</p></div>";
	}


	function view_footer($import=null) {
		// Close the 'wrap' div
		$this->postbox_container_end();
		echo "</div>";

		echo "<script type='text/javascript'>        /* <![CDATA[ */\r\n";

		echo 'var til10n = ' . json_encode (array (
			'no_template' => __('No template selected.'),
			'no_template_name' => __('Enter a template name to save.'),
			'no_post_title' => __('Enter the post title.'),
			'confirm_process' => __('Please BACK UP your wordpress database before proceeding! Are you ready to start the import?'),
			'confirm_undo' => __('Please BACK UP your wordpress database before proceeding!  Are you ready to undo?'),
			'confirm_template_delete' => __('Are you sure you want to delete the template "%s"?'),
			'confirm_import_delete' => __('Caution!  If you delete an import you cannot undo it later.  Are you sure you want to delete?'),
			'click_to_select' => __('Click to select'),
			'select_all' => __('Select all'),
			'disable' => (isset($import) && $import->status != 'NEW')
		)) . ";\r\n" ;

		echo "/* ]]> */ </script>";
	}

	function postbox_container_start($echo=true) {
		$html = "<div class='postbox-container'>"
			. "<div class='metabox-holder'>"
			. "<div class='meta-box-sortables'>";
		if ($echo)
			echo $html;
		else
			return $html;
	}

	function postbox_container_end($echo=true) {
		$html = "</div></div></div>";
		if ($echo)
			echo $html;
		else
			return $html;
	}

	function postbox_start($title, $id="", $open=true, $echo=true) {
		$class = ($open) ? "postbox" : "postbox closed";
		$html = "<div id='$id' class='$class'>"
			. "<div class='handlediv' title='" . __('Click to toggle') . "'><br /></div>"
			. "<h3 class='hndle'><span>$title</span></h3>"
			. "<div class='inside'>";
		if ($echo)
			echo $html;
		else
			return $html;
	}

	function postbox_end($echo=true) {
		$html = "</div></div>";
		if ($echo)
			echo $html;
		else
			return $html;
	}

/**
* Display a setting for an options screen
*
* @param mixed $label_id - pass a label ID or text.  If a label ID is used, the function will look up the label and tip
* @param mixed $html - html to display for the setting selection
* @param mixed $tip - tooltip
*/
	function setting($label, $html, $tip='') {
		echo "<tr>";
		echo "<th scope='row'>";

		if ($tip) {
			echo "<abbr style='cursor:help' class='ttip' title='" .esc_attr($tip) . "'>$label</abbr> ";
		} else {
			echo "$label ";
		}

		echo "</th>";
		echo "<td style='vertical-align:top'>$html</td>";
		echo "</tr>";
	}

	function field_setting($tip_field, $html, $label=null) {
		// If no label is provided then use the default label for the field
		if (isset($this->labels[$tip_field]['label'])) {
			$label = ($label) ? $label : $this->labels[$tip_field]['label'];
			$tip = $this->labels[$tip_field]['tip'];
		} else {
			$tip = "";
		}

		// If a field name is available, show it
		if (isset($this->labels[$tip_field]['field'])) {
			$tip = "<p>Default column name: <code>" . $this->labels[$tip_field]['field'] . "</code></p>" . $tip;
		}

		$this->setting($label, $html, $tip);
	}

	/**
	* Dropdown displaying an array of (key => description)
	*
	*/
	function dropdown($name, $select, $keys, $none=false, $multi=false) {
		if ($none)
			$keys = array('' => '') + (array)$keys;

		$select = (array) $select;

		$name = esc_attr($name);
		$multiple = ($multi) ? "multiple='multiple' size='20'" : "";
		$class = ($multi) ? "class='ddms'" : "";
		$html = "<select name='$name' $class $multiple >";

		foreach ((array)$keys as $key => $description) {
			$selected = ( in_array($key, $select) || ($select === null && $key === null) ) ? "selected='selected'" : "";
			$key = esc_attr($key);
			$description = esc_attr($description);
			$html .= "<option value='$key' $selected>$description</option>";
		}
		$html .= "</select>";
		return $html;
	}

	/**
	* Single checkbox
	*/
	function checkbox($name, $value) {
		$name = esc_attr($name);
		$checked = ($value) ? "checked='checked'" : "";
		$html     = "<input type='hidden' name='$name' value='0' />"
				. "<input type='checkbox' name='$name' value='1' $checked />";

		return $html;
	}

	/**
	* Display a text box
	*
	*/
	function text($name, $value, $size) {
		$name = esc_attr($name);
		$value = esc_attr($value);
		$html = "<input name='$name' type='text' size='$size' value='$value' />";
		return $html;
	}


	/**
	* Outputs a <div> containing an HTML <table>
	*
	* @param mixed array $headers - array of column header strings
	* @param mixed array $rows - array of rows; each row is array(class, data); class is optional, if provided it's applied to <TR>
	* @param mixed $div_class - a class to apply to the table's <div>
	* @param mixed $table_class - a class to apply to the <table>
	*/
	function option_table($headers, $rows, $table_class='ti-table ti-stripe') {
		$html = "<table class='$table_class'><thead><tr>";

		foreach ((array)$headers as $header)
			$html .= "<th>$header</th>";
		$html .= "</tr></thead><tbody>";

		foreach ((array)$rows as $row) {
			if (isset($row['class']))
				$html .= ($row['class']) ? "<tr class='". $row['class'] . "'>" : "";
			else
				$html .= "<tr>";

			if (isset($row['data'])) {
				foreach ((array)$row['data'] as $col)
					$html .= "<td>$col</td>";
			}

			$html .= "</tr>";
		}

		$html .= "</tbody></table>";
		return $html;
	}


	/**
	* Returns an array of custom field keys, ignoring most WP standard hidden fields
	* The array is sorted by key
	*
	*/
	function get_meta_keys() {
		global $wpdb;

		// Get list of custom fields from all posts; ignore wordpress standard hidden fields
		$meta_keys = $wpdb->get_col( "
			SELECT DISTINCT meta_key
			FROM $wpdb->postmeta
			WHERE meta_key NOT in ('_edit_last', '_edit_lock', '_encloseme', '_pingme', '_wp_attached_file', '_wp_trash_meta_status', '_wp_trash_meta_time',
			'_wp_trash_meta_comments_status', '_wp_page_template', '_wp_attachment_temp_parent', '_wp_attachment_backup_sizes',
			'_wp_attachment_metadata', '_wp_old_slug')
			AND meta_key NOT LIKE ('\_wp%')" );

		// MySQL cannot be used for group-by or order because postmeta is stored case-insensitive
		if ($meta_keys)
			$meta_keys = array_unique($meta_keys);

		return $meta_keys;
	}

	function memory_usage($show_as_message=false) {
		$html = "";
		$php_memory_limit = ini_get('memory_limit');
		$php_memory_limit = str_ireplace('M', '', $php_memory_limit);

		$wp_memory_limit = (defined('WP_MEMORY_LIMIT')) ? WP_MEMORY_LIMIT : "not set";
		$wp_memory_limit = str_ireplace('M', '', $wp_memory_limit);

		$memory_usage = max( memory_get_usage(true), memory_get_peak_usage() );
		$memory_usage = round( $memory_usage / 1024 / 1024, 2 );

		$free_memory = min($php_memory_limit, $wp_memory_limit) - $memory_usage;

		$details = sprintf("Current memory usage: <b>%s MB</b>", $memory_usage)
			. "<br/>PHP limit: <strong>$php_memory_limit MB</strong>"
			. "<br/>WordPress limit: <strong>$wp_memory_limit MB</strong>"
			. "<br/>Free memory: <strong>$free_memory MB</strong>";

		if (!$show_as_message)
			return $details;

		$min = 16;      // Minimum free memory
		$php_free = $php_memory_limit - $memory_usage;
		$wp_free = $wp_memory_limit - $memory_usage;

		if ( ($php_free) < $min || ($wp_free) < $min ) {
			$details .= "<br/><br/>"
				. __("Increase the WordPress limit by editing the <code>wp-config.php</code> and adding <code>define('WP_MEMORY_LIMIT', '128M');</code>") . "<br/>"
				. __("PHP memory can be increased by editing the <code>php.ini</code> file using <code>memory_limit = 128M</code>")
				. "<br/>" . __("You may need to contact your hosting service to make these changes.")
				. "<br/>";

			$message = __("Your blog's memory is low.  This may prevent you from processing large imports.");

			if ($show_as_message)
				return "<div class='ti-warning'>$message <a href='#' class='ti-accordion'> " . __('(more info)') . "</a><div style='display:none'>$details</div></div>";
			else
				return "<div class='ti-warning'>$message<br/>$details</div>";
		} else {
			if ($show_as_message)
				return "";
			else
				return $details;
		}

		return $html;
	}

	function get_labels() {
		return array(
			'template_load' => array('label' => __('Load template'), 'tip' => __('Load the settings from a saved "template".')),
			'template_save' => array('label' => __('Save template'), 'tip' => __('Save settings as a "template" for future imports.')),
			'blog_id' => array('label' => __('Blog ID'), 'field' => '!blog_id', 'tip' => __('Site to import into (default is current site).  This feature is only available from the main site.')),
			'post_title' => array('label' => __('Title'), 'field' => '!post_title', 'tip' => __("Click on the field names to add them to the post title layout.  During the import they'll be replaced with data from the input file.")),
			'post_content' => array('label' => __('Body'), 'field' => '!post_content', 'tip' => __("Click on the field names to add them to the post body layout.  During the import they'll be replaced with data from the input file.")),
			'post_excerpt' => array('label' => __('Excerpt'), 'field' => '!post_excerpt', 'tip' => __("Click on the field names to add them to the post excerpt layout.  During the import they'll be replaced with data from the input file.")),
			'post_type' => array('label' => __('Post type'), 'field' => '!post_type', 'tip' => __("Post type (WordPress defaults to 'post' for new posts)")),
			'post_parent' => array('label' => __('Page parent'), 'field' => '!post_parent', 'tip' => __("Parent page (default is blank)")),
			'page_template' => array('label' => __('Page template'), 'field' => '!page_template', 'tip' => __('Page template (default is blank)')),
			'menu_order' => array('label' => __('Menu order'), 'field' => '!menu_order', 'tip' => __('Page menu order (default is blank)')),
			'post_status' => array('label' => __('Post status'), 'field' => '!post_status', 'tip' => __("Post status (WordPress defaults to 'Draft' for new posts)")),
			'comment_status' => array('label' => __('Comment status'), 'field' => '!comment_status', 'tip' => __("Comment status (WordPress defaults to 'open' for new posts)")),
			'post_author' => array('label' => __('Post author'), 'field' => '!post_author', 'tip' => __("Post author (WordPress defaults to current user for new posts)")),
			'post_name' => array('label' => __('Post name / slug'), 'field' => '!post_name', 'tip' => __("Post slug.  WordPress creates a unique slug based on the post title for new posts.")),
			'post_date' => array('label' => __('Post date'), 'field' => '!post_date', 'tip' => __('Enter a date (WordPress defaults to the current date for new posts).  For random dates enter a value in both date fields.')),
			'postid' => array('label' => __('Unique ID'), 'field' => '!id', 'tip' => __("Select an input 'ID' column that uniquely identifies each post.  The input column values can be matched to a standard WordPress field (such as 'Post ID') or to a custom field.")),
			'taxonomies' => array('label' => __('Taxonomies'), 'tip' => __('<p>Choose an input column for this taxonomy or select default values from the list.</p><p>The default column names for categories and tags are <code>!post_category</code> and <code>!tags_input</code>.  Any other column that starts with "!" is treated as a list of values for a custom taxonomy, for example <code>!mytaxonomy</code>.</p>')),
			'thumbnail' => array('label' => __('Image ID or URL'), 'field' => '!post_thumbnail', 'tip' => __('<p>WordPress assigns a featured image to a post by setting the hidden field <code>_thumbnail_id</code> to an image ID from the Media Library.</p><p>Use this setting to select a column containing image IDs or image URLs.</p><p>If an ID is provided, TurboCSV will set the featured image for the current post to that ID.  If a URL is provided, TurboCSV will search the Media Library for the image with that URL.  URLs must begin with "http".</p><p>Note that all images must be uploaded to the Media Library <b>before</b> using this setting.</p>')),
			'post_custom' => array('label' => __('Existing custom fields'), 'tip' => __('Map columns from the input file to existing WordPress custom fields.  NOTE: you won\'t see a field listed here until at least one post has that field!  You may need to create a dummy post and add the field to it before importing.')),
			'post_custom_new' => array('label' => __('New custom fields'), 'tip' => __("Create new WordPress custom fields for the selected input columns"))
		);
	}
} // End class TI_Frontend

if (class_exists('TI_Pro'))
	$tic_frontend = new TI_Pro();
else
	$tic_frontend = new TI_Frontend();
?>