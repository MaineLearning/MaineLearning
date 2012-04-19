<?php

/*

	Plugin Name: TurboCSV

	Plugin URI: http://www.wphostreviews.com/turbocsv

	Description: TurboCSV

	Version: 2.14

	Author: Chris Richardson

	Author URI: http://www.wphostreviews.com/turbocsv

*/



require_once dirname( __FILE__ ) . '/ti_obj.php';

require_once dirname( __FILE__ ) . '/ti_import.php';

@include_once dirname( __FILE__ ) . '/ti_pro.php';



class TI_Frontend {

	var $version = '2.14';

	var $option_defaults = array('encoding' => 'auto', 'max_skip' => 0, 'commit_rows' => 100, 'values_separator' => ',', 'hierarchy_separator' => '|');



	function TI_Frontend() {

		global $wp_version, $wpdb;



		// Localization

		load_plugin_textdomain( 'turbocsv', false, dirname( plugin_basename( __FILE__ ) ) );



		// Menu

		add_action('admin_menu', array(&$this,'hook_admin_menu'));



		// Activation

		register_activation_hook(__FILE__, array(&$this, 'hook_activation'));



		// Debugging

		$this->debugging();



		add_action('init', array(&$this, 'hook_init'));

		add_action('wp_ajax_ti_process', array(&$this, 'ajax_process') );



		// Set option defaults

		$options = get_option('turbocsv');

		$options = shortcode_atts($this->option_defaults, $options);

		update_option('turbocsv', $options);

	}



	// ti_errors -> PHP errors

	// ti_info -> phpinfo + dump

	function debugging() {

		global $wpdb;



		if (isset($_GET['ti_errors'])) {

			error_reporting(E_ALL);

			ini_set('error_reporting', E_ALL);

			ini_set('display_errors','On');

			$wpdb->show_errors();

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

		}

	}



	function ajax_process() {

		// Read the import object from the database

		$id = (isset ($_POST['id'])) ? $_POST['id'] : null;

		$import = TI_Import::get($id);



		if (!$import) {

			die (sprintf(__("Unable to read the import id = "), $id));

		}



		// Update the template with POST variables

		$_POST = stripslashes_deep($_POST);   // Strip out slashes added by wordpress

		if (isset($_POST['template']))

			$import->template->update($_POST['template']);



		// Process the import

		$import->import();



		die (__('Your import has been processed!  Click to see the results.'));

	}



	function hook_activation() {

		TI_Import::create_db();

		TI_Template::create_db();

	}



	function hook_init() {

		$options = get_option('turbocsv');

	}



	function hook_admin_menu() {

		// Single menu

		$pages[] = add_management_page(__('TurboCSV'), __('TurboCSV'), 'import', 'ti_new', array(&$this, 'control'));

		$pages[] = add_submenu_page('', 'TurboCSV - View Import', 'ti_display', 'import', 'ti_display', array(&$this, 'control'));

		$pages[] = add_submenu_page('', __('TurboCSV - Import History'), __('History'), 'import', 'ti_history', array(&$this, 'control'));

		$pages[] = add_submenu_page('', __('TurboCSV - Settings'), __('Settings'), 'import', 'ti_settings', array(&$this, 'control'));





		// Load scripts/styles for our pages only

		foreach ($pages as $page) {

			add_action('admin_print_scripts-' . $page, array(&$this, 'hook_print_scripts'));

			add_action('admin_print_styles-' . $page, array(&$this, 'hook_print_styles'));

	   }

	}



	function hook_print_scripts() {

		// Normal operation

		$cluetip_js = plugins_url("/cluetip/jquery.cluetip.js", __FILE__);

		$turbocsv_js = plugins_url("/turbocsv.js", __FILE__);



		wp_enqueue_script('cluetip', $cluetip_js, array('jquery', 'jquery-form', 'jquery-ui-dialog', 'jquery-ui-tabs'), '1.0.7');

		wp_enqueue_script('turbocsv', $turbocsv_js, false, $this->version, false);

		wp_enqueue_script('dashboard');  // Collapsible menus



	}



	function hook_print_styles() {

		wp_enqueue_style("turbocsv", plugins_url("/turbocsv.css", __FILE__), FALSE, $this->version);

		wp_enqueue_style("cluetip", plugins_url("/cluetip/jquery.cluetip.css", __FILE__), FALSE, '1.0.7');

	}



	function get_template_from_columns($import) {



		$meta_keys = $this->get_meta_keys();



		foreach($import->headers as $header) {

			switch($header) {

				case '!post_content':

					$import->template->post_content = "#$header#";

					break;



				case '!post_excerpt':

					$import->template->post_excerpt = "#$header#";

					break;



				case '!post_author':

					$import->template->post_author_col = $header;

					$import->template->post_author_type = 'COLUMN';

					break;



				case '!post_name':

					$import->template->post_name_col = $header;

					break;



				case '!id':

					$import->template->postid_col = $header;



				case '!post_title':

					$import->template->post_title = "#$header#";

					break;



				case '!post_category':

					$import->template->post_category_type = 'COLUMN';

					$import->template->post_category_col = $header;

					break;



				case '!tags_input':

					$import->template->post_tag_type = 'COLUMN';

					$import->template->post_tag_col = $header;

					break;



				case '!post_date':

					$import->template->post_date_type = 'COLUMN';

					$import->template->post_date_col = $header;

					break;



				default:

					// If first character = '1', it's a custom taxonomy, otherwise it's a custom field

					if (substr($header, 0, 1) == '!') {

						// Custom taxonomy

						$taxonomy_name = (substr($header, 1));

						$import->template->post_taxonomy_cols[$taxonomy_name] = $header;

					} else {

						// Custom field

						if (array_search($header, $meta_keys) !== false)

							$import->template->post_custom[$header] = $header;

						else

							$import->template->post_custom_new[$header] = $header;

					}

			}

		}

	}



	function control() {

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



				if (!$import) {

					$error = sprintf(__('Internal error.  Unable to read import with id=%s'), $id);

					break;

				}



				// Process import

				if (isset($_POST['button_process'])) {

					// Create the template object from the form values so we can save it with the import

					$template = new TI_Template($_POST['template']);

					$import->template = $template;



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



							if (is_wp_error($result))

								$error = $result->get_error_message();

							else

								$message = __('Import deleted.');

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

		if($noheader) {

			$to = add_query_arg(array('noheader' => null), $to);

			wp_redirect($to);

			exit();

		}

	}



	function view_upload() {

		// Max filesize

		$bytes = apply_filters( 'import_upload_size_limit', wp_max_upload_size() );

		$size = round($bytes / 1048576 , 2) . "MB";



		$this->postbox_start(__('New Import'), "newimport");

		?>

			<p>

				<?php

					printf(__('Specify the input file below.  Based on your PHP settings the maximum file size is: <b>%s</b>.' ), $size );

				?>

			</p>

			<p>

				<?php echo __('Files must be in .CSV format with headers in the first row (') . "<a href='" . plugins_url("/sample.csv", __FILE__) . "'>" . __('see a sample file') . "</a>)"; ?>

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

			<form method='post' id='form_import' action='<?php echo esc_attr(add_query_arg( 'noheader', 'true' )); ?>'>

				<?php $this->view_action($import); ?>

				<?php $this->view_status($import); ?>

				<?php $this->view_template($import); ?>

			</form>

		<?php

	}



	function view_template($import) {

		// Todo: non-pro template

	}



	function view_action($import) {

		if ($import->status == 'NEW') {

			$html = "<input type='submit' class='button-primary' name='button_process' value='" . __('Start the Import!') . "' />"

				."<span id='ti_twizzler' class='ti-hidden ti-twizzler'>"

				. "<img alt='Please wait...' src='" . plugins_url("/images/saving.gif", __FILE__) . "' /> "

				. __('Importing...')

				. "</span><div><br/></div>";

			$this->option_formatted('', $html);

		}

	}



	function view_status($import) {

		global $wpdb;



		switch ($import->status) {

			case 'NEW':

				$postbox_title = __('New Import');

				break;

			default:

				$postbox_title = __('Import Results');

		}



		$this->postbox_start($postbox_title, "status");

		?>

			<input type='hidden' name='id' value='<?php echo $import->_id?>' />

			<table class="form-table">

				<?php

					$this->option_formatted(__('Status'), $import->get_status(true, true));



					if ($import->fileurl)

						$this->option_formatted(__('File URL'), "<a href='{$import->fileurl}'>{$import->fileurl}</a>");



					if ($import->filepath)

						$this->option_formatted(__('File path'), $import->filepath);



					if ($import->status !== 'NEW') {

						// Categories, tags and custom taxonomies

						foreach((array)$import->imported_terms as $taxonomy => $terms) {

							$tax_obj = get_taxonomy($taxonomy);

							if ($tax_obj)

								$label = $tax_obj->label;

							else

								$label = $taxonomy;

							$this->option_formatted($label, $this->get_term_links($taxonomy, $terms));

						}



						// Imported posts

						foreach((array)$import->imported_posts as $post) {

							// Switch to the imported blog ID if there was one

							if (isset($post['blog_id']))

								switch_to_blog($post['blog_id']);



							// Get the most recent title, etc. for the post - it may have changed since the import

							$edit_link = get_edit_post_link($post['ID']);

							$title = get_the_title($post['ID']);

							$action = (isset($post['revision'])) ? __('Updated') : __('Created');



							if ($edit_link)

								$edit_link = "<a href='$edit_link' title='$title'>$title</a>";

							else

								$edit_link = '(deleted)';



							$post_links[] = array('data' => array($post['ID'], $edit_link, $action));



							// Switch back to current blog ID

							if (isset($post['blog_id']))

								restore_current_blog();

						}



						if (empty($post_links))

							$html = __('None');

						else

							$html = $this->option_table(array(__('Post ID'), __('Title'), __('Action')), $post_links);

						$this->option_formatted(__('Posts/pages modified'), $html);



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



							$rows[] = array('class' => $class, 'data' => array(date("M d, Y G:i:s", $message->time), $message->line, $msg));

						}



						$html = $this->option_table(array(__('Time'), __('Line'), __('Message')), $rows);

						$this->option_formatted(__('Log'), $html);

					}

				?>

			</table>

		<?php

		$this->postbox_end();

	}



	function get_term_links($taxonomy, $terms) {



		foreach((array)$terms as $term) {

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



		if (isset($links))

			return implode(", ", $links);

		else

			return 'none';

	}



	function view_history() {

		// Get list of past imports

		$imports = TI_Import::get_list();

		if (!empty($imports))

			krsort($imports);



		$this->postbox_start(__('Import History'), 'history');

		?>

			<?php

				foreach ((array)$imports as $import) {

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



				$html = $this->option_table(array(__('Status'), __('File Name'), __('Date'), __('Lines'), __('Action')), $rows, null, 'ti-large-scroll');

				echo $html;

			?>

		<?php

		$this->postbox_end();

	}



	function view_settings() {

		$options = get_option('turbocsv');

		extract($options);



		$encoding_values = array('auto' => 'Auto-detect', 'utf8' => 'UTF-8', 'ISO-8859-1' => 'ISO-8859-1', 'Windows-1252' => 'ANSI (Windows-1252)');

		$max_skip_values = array('0' => '- none -', '10' => '10', '50' => '50', '100' => '100', '999999' => 'Unlimited');



		$this->postbox_start(__('Options'), 'options');

		?>

			<form method="post" action="<?php echo esc_attr(add_query_arg( 'noheader', 'true' )); ?>">

				<?php wp_nonce_field('ti-options'); ?>



				<table class="form-table">

					<?php

						$html = $this->option_dropdown('turbocsv[encoding]', $encoding, $encoding_values, false);

						$tip = __("TurboCSV will try to auto-detect both UTF-8 and Windows files or you can use this field to force it to expect a specific encoding.");

						$this->option_formatted(__('Input file encoding'), $html, $tip);



						$html = $this->option_dropdown('turbocsv[max_skip]', $max_skip, $max_skip_values, false);

						$tip = __("Normally any errors will cause the import to halt.  However, you can also enter a maximum number of line errors to ignore before the import is stopped.  This can be useful if the file may contain many errors and you want to do a full test run before correcting them.");

						$this->option_formatted(__('Skip lines with errors'), $html, $tip);



						$html = $this->option_text('turbocsv[commit_rows]', $commit_rows, 3);

						$tip = __('Number of rows to process before changes are committed to the database during import.  A small number can slow down imports but will also prevent running out of memory for large imports.  The default is to commit every 100 rows.');

						$this->option_formatted(__('Commit rows'), $html, $tip);



						$html = $this->option_text('turbocsv[values_separator]', $values_separator, 1);

						$tip = __('Separator character for multiple values WITHIN a field.  Default is comma ",".  Change this if your data contains commas.');

						$this->option_formatted(__('Values separator'), $html, $tip);



						$html = $this->option_text('turbocsv[hierarchy_separator]', $hierarchy_separator, 1);

						$tip = __('Separator character for hierarchical values and addresses.  Default is "|".  Change it if your data contains that character.');

						$this->option_formatted(__('Hierarchy / address separator'), $html, $tip);

					?>

				</table>



				<p class="submit">

					<input type="submit" name="save" class="button-primary" value="<?php _e('Save Changes') ?>">

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

		$help_link = "<a href='http://wphostreviews.com/turbocsv/turbocsv-documentation'>" . __("Help") . "</a>";



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



		// If display mode, disable all fields except buttons

		if (isset($import) && $import->status != 'NEW')

			echo "jQuery('#form_import :input').attr('disabled', true); \r\n";



		echo 'var til10n = ' . json_encode (array (

			'no_template' => __('No template selected.'),

			'no_template_name' => __('Enter a template name to save.'),

			'no_post_title' => __('Enter the post title.'),

			'confirm_process' => __('Please BACK UP your wordpress database before proceeding! Are you ready to start the import?'),

			'confirm_undo' => __('Please BACK UP your wordpress database before proceeding!  Are you ready to undo?'),

			'confirm_template_delete' => __('Are you sure you want to delete the template "%s"?'),

			'confirm_import_delete' => __('Caution!  If you delete an import you cannot undo it later.  Are you sure you want to delete?')

		)) . ";\r\n" ;



		echo "/* ]]> */ </script>";



		wp_tiny_mce(false);

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





	function option($import, $field) {

		switch ($field) {



			case 'template[post_status]':

				$html = $this->option_dropdown($field, $import->template->post_status, array('publish'=>'Published', 'draft'=>'Draft', 'pending'=>'Pending Review'));

				$tip = __('WordPress post status - see <a href="http://codex.wordpress.org/Writing_Posts">here</a> for details.');

				$this->option_formatted(__('Post status'), $html, $tip);

				break;



			case 'template[comment_status]':

				$html = $this->option_dropdown($field, $import->template->comment_status, array('open'=>'open', 'closed'=>'closed'));

				$tip = __('WordPress comments setting - see <a href="http://codex.wordpress.org/Writing_Posts">here</a> for details.');

				$this->option_formatted(__('Comment status'), $html, $tip);

				break;



			case 'template[post_author_type]':

				global $current_user;



				// Author from column

				$checked = checked($import->template->post_author_type, 'COLUMN', false);

				$html = "<input type='radio' name='$field' value='COLUMN' $checked />" . __('Column') . " "

					. $this->option_dropdown_headers('template[post_author_col]', $import->template->post_author_col, $import->headers);



				// Author from fixed list

				$checked = checked($import->template->post_author_type, 'FIXED', false);

				$html .= "<br/><input type='radio' name='$field' value='FIXED' $checked />"  . __('Select from list ');



				$authors = get_editable_user_ids($current_user->id, true);

				$html .= wp_dropdown_users(array(

					'include'=>$authors,

					'selected'=>$import->template->post_author,

					'name' => 'template[post_author]',

					'class'=> 'authors',

					'multi' => 1,

					'echo' => 0));

				$tip = __('Author for the imported posts/pages.');

				$this->option_formatted(__('Author'), $html, $tip);

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





			case 'template[post_parent]':

				$html = wp_dropdown_pages(array('echo' => false,

					'name' => $field,

					'selected' => $import->template->post_parent,

					'show_option_none' => __('Main Page (no parent)'),

					'sort_column'=> 'menu_order, post_title'));

				if (!$html)

					$html = __('No parent pages available.  See the WordPress <a href="http://codex.wordpress.org/Pages">Codex</a> for details.');



				$tip = __('WordPress parent page - see <a href="http://codex.wordpress.org/Pages">here</a> for details.');

				$this->option_formatted(__('Page parent'), $html, $tip);

				break;



			case 'template[page_template]':

				$templates = get_page_templates();

				ksort($templates);

				if($templates) {

					$templates = array_combine(array_values($templates), array_keys($templates));

					$templates = array_merge(array('default' => 'Default Template'), $templates);

				} else {

					$templates = array('default' => 'Default Template');

				}



				$html = $this->option_dropdown($field, $import->template->page_template, $templates, false);



				$tip = __('WordPress page template - see <a href="http://codex.wordpress.org/Pages">here</a> for details.');

				$this->option_formatted(__('Page template'), $html, $tip);

				break;



			case 'template[menu_order]':

				$html = $this->option_text($field, $import->template->menu_order, 4);

				$tip = __('WordPress page menu order - see <a href="http://codex.wordpress.org/Pages">here</a> for details.');

				$this->option_formatted(__('Page menu order'), $html, $tip);

				break;

		}

	}





	function optionp($import, $field) {

	}



	/**

	* Display html formatted as table row with label, html, tooltip and style(class)

	*/

	function option_formatted($label='', $html='', $tip='', $style='') {

		echo "<tr $style valign='top'>";



		echo "<th scope='row'>";



		if ($label)

			echo "$label: ";



		if ($tip)

			echo "<sup>[<a href='#' class='ti-tooltip' title='" . $label . '|' . esc_attr($tip) . "'>?</a>]</sup>";



		echo "</th>";

		echo "<td>$html</td>";

		echo "</tr>";

	}



	/**

	* Display a text box

	*

	*/

	function option_text($name, $value, $size) {

		$name = esc_attr($name);

		$value = esc_attr($value);

		$html = "<input name='$name' type='text' size='$size' value='$value' />";

		return $html;

	}



	function option_dropdown_headers($name, $select, $headers, $none=false) {

		// For headers use an array where key = value

		foreach ((array)$headers as $header)

			$dd_headers[$header] = $header;

		return $this->option_dropdown($name, $select, $dd_headers, true);

	}



	/**

	* Dropdown

	*

	*/

	function option_dropdown($name, $select, $keys, $none=false) {

		asort($keys);



		if ($none) {

			$none = '- none -';

			$keys = array_merge(array(null => $none), (array)$keys);

		}



		$select = (array) $select;



		$name = esc_attr($name);

		$html = "<select name='$name'>";



		foreach ((array)$keys as $key => $description) {

			$selected = ( in_array($key, $select) || ($select == null && $key == null) ) ? "selected='selected'" : "";

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

	function option_checkbox($name, $value) {

		$name = esc_attr($name);

		$checked = ($value) ? "checked='checked'" : "";

		$html 	= "<input type='hidden' name='$name' value='0' />"

				. "<input type='checkbox' name='$name' value='1' $checked />";



		return $html;

	}



	function option_table($headers, $rows, $table_id='', $div_class='ti-small-scroll', $table_class='ti-table ti-stripe') {

		$html = "<div class='$div_class'><table id='$table_id' class='$table_class'><thead><tr>";



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



		$html .= "</tbody></table></div>";

		return $html;

	}







	function get_meta_keys() {

		global $wpdb;



		// Get list of custom fields from all posts; ignore wordpress standard hidden fields

		$meta_keys = $wpdb->get_col( "

			SELECT meta_key

			FROM $wpdb->postmeta

			WHERE meta_key NOT in ('_edit_last', '_edit_lock', '_encloseme', '_pingme', '_wp_attached_file', '_wp_trash_meta_status', '_wp_trash_meta_time',

			'_wp_trash_meta_comments_status', '_wp_page_template', '_wp_attachment_temp_parent', '_wp_attachment_backup_sizes', '_thumbnail_id',

			'_wp_attachment_metadata', '_wp_old_slug')

			AND meta_key NOT LIKE ('\_wp%')" );



		// MySQL cannot be used for group-by or order because postmeta is stored case-insensitive

		if ($meta_keys)

			$meta_keys = array_unique($meta_keys);



		return $meta_keys;

	}





	/**

	* Displays a progressbar during upload - not currently used

	*

	* @param mixed $rows

	* @param mixed $percent

	*/

	function option_progressbar($rows, $percent) {



		?>

			<script type='text/javascript'>

			/* <![CDATA[ */

				jQuery('#progressbar').show();

				jQuery('#progress').width('<?php echo $percent ?>%');

				jQuery('#progress_count').html('Posts loaded: <?php echo $rows ?>');

			/* ]]> */

			</script>

		<?php



		// Note - flush does not work properly on IIS7 due to cacheing

		echo $script_bar;

		echo str_pad('',4096)."\n";

		ob_flush();

		flush();

	}

} // End class TI_Frontend

if (class_exists('TI_Pro'))

	$tic_frontend = new TI_Pro();

else

	$tic_frontend = new TI_Frontend();

?>