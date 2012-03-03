<?php

if(is_admin()){
	add_action('init', 'wpv_filter_custom_field_init');
	
	function wpv_filter_custom_field_init() {
		global $pagenow;
		
		if($pagenow == 'post.php' || $pagenow == 'post-new.php'){
			add_action('wpv_add_filter_table_row', 'wpv_add_filter_custom_field_table_row', 2, 1);
			add_filter('wpv_add_filters', 'wpv_add_filter_custom_field', 2, 1);
		}
	}
	
    function wpv_add_filter_custom_field($filters) {
		global $WP_Views;
		
		$meta_keys = $WP_Views->get_meta_keys();

		foreach ($meta_keys as $key) {		
			
			$filters['custom-field-' . str_replace(' ', '_', $key)] = array('name' => sprintf(__('Custom field - %s', 'wpv-views'), $key),
										'type' => 'callback',
										'callback' => 'wpv_add_meta_key',
										'args' => array('name' => $key));
		}

		// add the nonce field here.
		wp_nonce_field('wpv_add_custom_field_nonce', 'wpv_add_custom_field_nonce');
		
        return $filters;
    }

    function wpv_add_filter_custom_field_table_row($view_settings) {
		global $view_settings_table_row;

		if (!isset($view_settings['custom_fields_relationship'])) {
			$view_settings['custom_fields_relationship'] = 'OR';
		}
		
		// Find any custom fields
		
		$summary = '';
		$count = 0;
		foreach (array_keys($view_settings) as $key) {
			if (strpos($key, 'custom-field-') === 0 && strpos($key, '_compare') === strlen($key) - strlen('_compare')) {
				$name = substr($key, 0, strlen($key) - strlen('_compare'));

				$td = wpv_get_table_row_ui_post_custom_field($view_settings_table_row, $name, null, null, $view_settings);
				echo '<tr class="wpv_custom_field_edit_row wpv_filter_row wpv_post_type_filter_row" id="wpv_filter_row_' . $view_settings_table_row . '" style="background:' . WPV_EDIT_BACKGROUND . '; display:none;">' . $td . '</tr>';
            
				$view_settings_table_row++;
				$count++;
				
				if ($summary != '') {
					if ($view_settings['custom_fields_relationship'] == 'OR') {
						$summary .= __(' OR', 'wpv-views');
					} else {
						$summary .= __(' AND', 'wpv-views');
					}
				}
				
				$summary .= wpv_get_custom_field_summary($name, $view_settings);
					
			}
		}
		
		if ($summary != '') {
			if ($count > 1) {
				echo '<tr class="wpv_custom_field_edit_row wpv_filter_row wpv_post_type_filter_row" id="wpv_filter_row_' . $view_settings_table_row . '" style="background:' . WPV_EDIT_BACKGROUND . '; display:none;">';
				wpv_filter_custom_field_relationship_admin($view_settings);			
				echo '</tr>';
			
				$view_settings_table_row++;
			}
			echo '<tr class="wpv_custom_field_edit_row wpv_filter_row wpv_post_type_filter_row" id="wpv_filter_row_' . $view_settings_table_row . '" style="background:' . WPV_EDIT_BACKGROUND . '; display:none;"><td></td><td>';
			?>
				<?php
					$filters = wpv_add_filter_custom_field(array());
					wpv_filter_add_filter_admin($view_settings, $filters, 'popup_add_custom_field', 'Add another custom field');
				?>
				<hr />
				<input class="button-primary" type="button" value="<?php echo __('OK', 'wpv-views'); ?>" name="<?php echo __('OK', 'wpv-views'); ?>" onclick="wpv_show_filter_custom_field_edit_ok()"/>
				<input class="button-secondary" type="button" value="<?php echo __('Cancel', 'wpv-views'); ?>" name="<?php echo __('Cancel', 'wpv-views'); ?>" onclick="wpv_show_filter_custom_field_edit_cancel()"/>
			<?php
			
			echo '</td></tr>';
		
			$view_settings_table_row++;

			echo '<tr class="wpv_custom_field_show_row wpv_filter_row wpv_post_type_filter_row" id="wpv_filter_row_' . $view_settings_table_row . '"><td></td><td>';
			_e('Select posts with custom fields: ', 'wpv-view');
			echo $summary;
			
			?>
			<br />
			<input class="button-secondary" type="button" value="<?php echo __('Edit', 'wpv-views'); ?>" name="<?php echo __('Edit', 'wpv-views'); ?>" onclick="wpv_show_filter_custom_field_edit()"/>
			<?php
			
			echo '</td></tr>';

			$view_settings_table_row++;
		}

		
    }

	function wpv_filter_custom_field_relationship_admin($view_settings) {
		if (!isset($view_settings['custom_fields_relationship'])) {
			$view_settings['custom_fields_relationship'] = '';
		}
		?>
		
		<td></td>
		<td>
			<fieldset>
				<legend><strong><?php _e('Custom field relationship:', 'wpv-views') ?></strong></legend>            
				<?php _e('Relationship to use when querying with multiple custom fields:', 'wpv-views'); ?>
				<select name="_wpv_settings[custom_fields_relationship]">            
					<option value="OR"><?php _e('OR', 'wpv-views'); ?>&nbsp;</option>
					<?php $selected = $view_settings['custom_fields_relationship']=='AND' ? ' selected="selected"' : ''; ?>
					<option value="AND" <?php echo $selected ?>><?php _e('AND', 'wpv-views'); ?>&nbsp;</option>
				</select>
				
			</fieldset>
		</td>

		<?php
	}
	
	function wpv_ajax_add_custom_field() {
		if (wp_verify_nonce($_POST['wpv_nonce'], 'wpv_add_custom_field_nonce')) {
			global $view_settings_table_row;
			
			if (isset($_POST['custom_fields_name'])) {
				$custom_fields = array();
				for($i = 0; $i < sizeof($_POST['custom_fields_name']); $i++) {
					$name = $_POST['custom_fields_name'][$i];
					$custom_fields['custom-field-' . $name . '_compare'] = $_POST['custom_fields_compare'][$i];
					$custom_fields['custom-field-' . $name . '_type'] = $_POST['custom_fields_type'][$i];
					$custom_fields['custom-field-' . $name . '_value'] = $_POST['custom_fields_value'][$i];
				}
				$custom_fields['custom_fields_relationship'] = $_POST['custom_fields_relationship'];
				
				$view_settings_table_row = $_POST['row'];
				
				wpv_add_filter_custom_field_table_row($custom_fields);
			}
		}
		die();
	}
    
}

function wpv_get_table_row_ui_post_custom_field($row, $type, $not_used, $custom_field, $view_settings = array()) {
	$field_name = substr($type, strlen('custom-field-'));
	$args = array('name' => $field_name);
	
	if (sizeof($view_settings) == 0) {
		$view_settings[$type . '_compare'] = $custom_field['compare'];
		$view_settings[$type . '_type'] = $custom_field['type'];
		$view_settings[$type . '_value'] = $custom_field['value'];
	}
	
	ob_start();
	
	?>
	<td>
		<img src="<?php echo WPV_URL; ?>/res/img/delete.png" onclick="on_delete_wpv_filter('<?php echo $row; ?>')" style="cursor: pointer">
	</td>
	<td class="wpv_td_filter">
		<fieldset>
			<legend><strong><?php echo __('Custom field', 'wpv_views') . ' - ' . $field_name; ?>:</strong></legend>
			<?php wpv_add_meta_key($args, $view_settings); ?>
		</fieldset>
	</td>
	
	<?php
	
	$buffer = ob_get_clean();
	
	return $buffer;
}

function wpv_get_custom_field_summary($type, $view_settings = array()) {
	$field_name = substr($type, strlen('custom-field-'));
	$args = array('name' => $field_name);
	
	ob_start();
	
	?>
	<strong><?php echo $field_name . ' ' . $view_settings[$type . '_compare'] . ' ' . $view_settings[$type . '_value']; ?></strong>
	
	<?php
	
	$buffer = ob_get_clean();
	
	return $buffer;
}

function wpv_add_meta_key($args, $view_settings = null) {
	
	$compare = array('=', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN');
	$types = array('CHAR', 'NUMERIC', 'BINARY', 'DATE', 'DATETIME', 'DECIMAL', 'SIGNED', 'TIME', 'UNSIGNED');
	?>

	<div class="meta_key_div" style="margin-left: 20px;">
		<?php _e('Comparison function:', 'wpv-views'); ?>
		<?php if($view_settings === null): ?>
			<select name="custom-field-<?php echo str_replace(' ', '_', $args['name']); ?>_compare">
				<?php
					foreach($compare as $com) {
						echo '<option value="'. $com . '">' . $com . '&nbsp;</option>';
					}
				?>
			</select>
			<select name="custom-field-<?php echo str_replace(' ', '_', $args['name']); ?>_type">
				<?php
					foreach($types as $type) {
						echo '<option value="'. $type . '">' . $type . '&nbsp;</option>';
					}
				?>
			</select>
		<?php else: ?>
			<select name="_wpv_settings[custom-field-<?php echo str_replace(' ', '_', $args['name']); ?>_compare]">
				<?php
					$compare_selected = $view_settings['custom-field-' . str_replace(' ', '_', $args['name']) . '_compare'];
					foreach($compare as $com) {
						$selected = $compare_selected == $com ? ' selected="selected"' : ''; 
						echo '<option value="'. $com . '" '. $selected . '>' . $com . '&nbsp;</option>';
					}
				?>
			</select>
			<select name="_wpv_settings[custom-field-<?php echo str_replace(' ', '_', $args['name']); ?>_type]">
				<?php
					$type_selected = $view_settings['custom-field-' . str_replace(' ', '_', $args['name']) . '_type'];
					foreach($types as $type) {
						$selected = $type_selected == $type ? ' selected="selected"' : ''; 
						echo '<option value="'. $type . '" '. $selected . '>' . $type . '&nbsp;</option>';
					}
				?>
			</select>
		<?php endif; ?>		
		<br />
		<?php _e('Value/s to compare:', 'wpv-views'); ?>
		<?php if($view_settings === null): ?>
			<input type='text' name="custom-field-<?php echo str_replace(' ', '_', $args['name']); ?>_value" />
		<?php else: ?>
			<?php $value = $view_settings['custom-field-' . str_replace(' ', '_', $args['name']) . '_value']; ?>
			<input type='text' name="_wpv_settings[custom-field-<?php echo str_replace(' ', '_', $args['name']); ?>_value]" value="<?php echo $value; ?>" />
		<?php endif; ?>		
		<?php _e('<strong>Note:</strong> Separate multiple values with a comma', 'wpv-views'); ?>
	</div>

	<?php
}


add_filter('wpv_get_table_row_ui_type', 'wpv_get_table_row_ui_type_custom_field');
function wpv_get_table_row_ui_type_custom_field($type) {

	if (strpos($type, 'custom-field-') === 0) {
		return 'post_custom_field';
	}
	
	return $type;
}

