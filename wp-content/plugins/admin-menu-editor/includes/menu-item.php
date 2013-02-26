<?php

/**
 * This class contains a number of static methods for working with individual menu items.
 *
 * Note: This class is not fully self-contained. Some of the methods will query global state.
 * This is necessary because the interpretation of certain menu fields depends on things like
 * currently registered hooks and the presence of specific files in admin/plugin folders.
 */
abstract class ameMenuItem {
	/**
	 * Convert a WP menu structure to an associative array.
	 *
	 * @param array $item An menu item.
	 * @param int $position The position (index) of the the menu item.
	 * @param string $parent The slug of the parent menu that owns this item. Blank for top level menus.
	 * @return array
	 */
	public static function fromWpItem($item, $position = 0, $parent = '') {
		static $separator_count = 0;
		$item = array(
			'menu_title'   => $item[0],
			'access_level' => $item[1], //= required capability
			'file'         => $item[2],
			'page_title'   => (isset($item[3]) ? $item[3] : ''),
			'css_class'    => (isset($item[4]) ? $item[4] : 'menu-top'),
			'hookname'     => (isset($item[5]) ? $item[5] : ''), //Used as the ID attr. of the generated HTML tag.
			'icon_url'     => (isset($item[6]) ? $item[6] : 'images/generic.png'),
			'position'     => $position,
			'parent'       => $parent,
		);

		if ( is_numeric($item['access_level']) ) {
			$dummyUser = new WP_User;
			$item['access_level'] = $dummyUser->translate_level_to_cap($item['access_level']);
		}

		if ( empty($parent) ) {
			$item['separator'] = empty($item['file']) || empty($item['menu_title']) || (strpos($item['css_class'], 'wp-menu-separator') !== false);
			//WP 3.0 in multisite mode has two separators with the same filename. Fix by reindexing separators.
			if ( $item['separator'] ) {
				$item['file'] = 'separator_' . ($separator_count++);
			}
		} else {
			//Submenus can't contain separators.
			$item['separator'] = false;
		}

		//Flag plugin pages
		$item['is_plugin_page'] = (get_plugin_page_hook($item['file'], $parent) != null);

		if ( !$item['separator'] ) {
			$item['url'] = self::generate_url($item['file'], $parent);
		}

		$item['template_id'] = self::template_id($item, $parent);

		return array_merge(self::basic_defaults(), $item);
	}

	public static function basic_defaults() {
		static $basic_defaults = null;
		if ( $basic_defaults !== null ) {
			return $basic_defaults;
		}

		$basic_defaults = array(
	        //Fields that apply to all menu items.
            'page_title' => '',
			'menu_title' => '',
			'access_level' => 'read',
			'extra_capability' => '',
			'file' => '',
	        'position' => 0,
	        'parent' => '',

	        //Fields that apply only to top level menus.
	        'css_class' => 'menu-top',
	        'hookname' => '',
	        'icon_url' => 'images/generic.png',
	        'separator' => false,

	        //Internal fields that may not map directly to WP menu structures.
			'open_in' => 'same_window', //'new_window', 'iframe' or 'same_window' (the default)
			'template_id' => '', //The default menu item that this item is based on.
			'is_plugin_page' => false,
			'custom' => false,
			'url' => '',
		);

		return $basic_defaults;
	}

	public static function blank_menu() {
		static $blank_menu = null;
		if ( $blank_menu !== null ) {
			return $blank_menu;
		}

		//Template for a basic menu item.
		$blank_menu = array_fill_keys(array_keys(self::basic_defaults()), null);
		$blank_menu = array_merge($blank_menu, array(
			'items' => array(), //List of sub-menu items.
			'grant_access' => array(), //Per-role and per-user access. Supersedes role_access.
			'role_access' => array(), //Per-role access settings.

			'custom' => false,  //True if item is made-from-scratch and has no template.
			'missing' => false, //True if our template is no longer present in the default admin menu. Note: Stored values will be ignored. Set upon merging.
			'unused' => false,  //True if this item was generated from an unused default menu. Note: Stored values will be ignored. Set upon merging.
			'hidden' => false,  //Hide/show the item. Hiding is purely cosmetic, the item remains accessible.

			'defaults' => self::basic_defaults(),
		));
		return $blank_menu;
	}

	public static function custom_item_defaults() {
		return array(
			'menu_title' => 'Custom Menu',
			'access_level' => 'read',
			'page_title' => '',
			'css_class' => 'menu-top',
			'hookname' => '',
			'icon_url' => 'images/generic.png',
			'open_in' => 'same_window',
			'is_plugin_page' => false,
		);
	}

	/**
	  * Get the value of a menu/submenu field.
	  * Will return the corresponding value from the 'defaults' entry of $item if the
	  * specified field is not set in the item itself.
	  *
	  * @param array $item
	  * @param string $field_name
	  * @param mixed $default Returned if the requested field is not set and is not listed in $item['defaults']. Defaults to null.
	  * @return mixed Field value.
	  */
	public static function get($item, $field_name, $default = null){
		if ( isset($item[$field_name]) ){
			return $item[$field_name];
		} else {
			if ( isset($item['defaults'], $item['defaults'][$field_name]) ){
				return $item['defaults'][$field_name];
			} else {
				return $default;
			}
		}
	}

	/**
	  * Generate or retrieve an ID that semi-uniquely identifies the template
	  * of the  given menu item.
	  *
	  * Note that custom items (i.e. those that do not point to any of the default
	  * admin menu pages) have no template IDs.
	  *
	  * The ID is generated from the item's and its parent's file attributes.
	  * Since WordPress technically allows two copies of the same menu to exist
	  * in the same sub-menu, this combination is not necessarily unique.
	  *
	  * @param array|string $item The menu item in question.
	  * @param string $parent_file The parent menu. If omitted, $item['defaults']['parent'] will be used.
	  * @return string Template ID, or an empty string if this is a custom item.
	  */
	public static function template_id($item, $parent_file = ''){
		if (is_string($item)) {
			return $parent_file . '>' . $item;
		}

		if ( self::get($item, 'custom') ) {
			return '';
		}

		//Maybe it already has an ID?
		$template_id = self::get($item, 'template_id');
		if ( !empty($template_id) ) {
			return $template_id;
		}

		if ( isset($item['defaults']['file']) ) {
			$item_file = $item['defaults']['file'];
		} else {
			$item_file = self::get($item, 'file');
		}

		if ( empty($parent_file) ) {
			if ( isset($item['defaults']['parent']) ) {
				$parent_file = $item['defaults']['parent'];
			} else {
				$parent_file = self::get($item, 'parent');
			}
		}

		return $parent_file . '>' . $item_file;
	}

  /**
   * Set all undefined menu fields to the default value.
   *
   * @param array $item Menu item in the plugin's internal form
   * @return array
   */
	public static function apply_defaults($item){
		foreach($item as $key => $value){
			//Is the field set?
			if ($value === null){
				//Use default, if available
				if (isset($item['defaults'], $item['defaults'][$key])){
					$item[$key] = $item['defaults'][$key];
				}
			}
		}
		return $item;
	}

  /**
   * Apply custom menu filters to an item of the custom menu.
   *
   * Calls two types of filters :
   * 	'custom_admin_$item_type' with the entire $item passed as the argument.
   * 	'custom_admin_$item_type-$field' with the value of a single field of $item as the argument.
   *
   * Used when converting the current custom menu to a WP-format menu.
   *
   * @param array $item Associative array representing one menu item (either top-level or submenu).
   * @param string $item_type 'menu' or 'submenu'
   * @param mixed $extra Optional extra data to pass to hooks.
   * @return array Filtered menu item.
   */
	public static function apply_filters($item, $item_type, $extra = null){
		$item = apply_filters("custom_admin_{$item_type}", $item, $extra);
		foreach($item as $field => $value){
			$item[$field] = apply_filters("custom_admin_{$item_type}-$field", $value, $extra);
		}

		return $item;
	}

	/**
	 * Recursively normalize a menu item and all of its sub-items.
	 *
	 * This will also ensure that the item has all the required fields.
	 *
	 * @static
	 * @param array $item
	 * @return array
	 */
	public static function normalize($item) {
		if ( isset($item['defaults']) ) {
			$item['defaults'] = array_merge(self::basic_defaults(), $item['defaults']);
		}
		$item = array_merge(self::blank_menu(), $item);

		$item['unused'] = false;
		$item['missing'] = false;
		$item['template_id'] = self::template_id($item);

		//Items pointing to a default page can't have a custom file/URL.
		if ( ($item['template_id'] !== '') && ($item['file'] !== null) ) {
			if ( $item['file'] == $item['defaults']['file'] ) {
				//Identical to default, so just set it to use that.
				$item['file'] = null;
			} else {
				//Different file = convert to a custom item. Need to call fix_defaults()
				//to fix other fields that are currently set to defaults custom items don't have.
				$item['template_id'] = '';
			}
		}

		$item['custom'] = $item['custom'] || ($item['template_id'] == '');
		$item = self::fix_defaults($item);

		//Older versions would allow the user to set the required capability directly.
		//This was incorrect since for default menu items the default cap was *always*
		//applied anyway, and the new cap was applied on top of that. We make that explicit
		//by storing the custom cap in a separate field - extra_capability - and keeping
		//access_level (required cap) at the default value.
		if ( isset($item['defaults']) && $item['access_level'] !== null ) {
			if ( empty($item['extra_capability']) ) {
				$item['extra_capability'] = $item['access_level'];
			}
			$item['access_level'] = null;
		}

		//Convert per-role access settings to the more general grant_access format.
		if ( isset($item['role_access']) ) {
			foreach($item['role_access'] as $role_id => $has_access) {
				$item['grant_access']['role:' . $role_id] = $has_access;
			}
			$item['role_access'] = array();
		}

		if ( isset($item['items']) ) {
			foreach($item['items'] as $index => $sub_item) {
				$item['items'][$index] = self::normalize($sub_item);
			}
		}

		return $item;
	}

	/**
	 * Fix obsolete default values on custom items.
	 *
	 * In older versions of the plugin, each custom item had its own set of defaults.
	 * It was also possible to create a pseudo-custom item from a default item by
	 * freely overwriting its fields with custom values.
	 *
     * The current version uses the same defaults for all custom items. To avoid data
     * loss, we'll check for any mismatches and make such defaults explicit.
	 *
	 * @static
	 * @param array $item
	 * @return array
	 */
	private static function fix_defaults($item) {
		if ( $item['custom'] && isset($item['defaults']) ) {
			$new_defaults = self::custom_item_defaults();
			foreach($item as $field => $value) {
				$is_mismatch = is_null($value)
					&& array_key_exists($field, $item['defaults'])
					&& (
						!array_key_exists($field, $new_defaults) //No default.
						|| ($item['defaults'][$field] != $new_defaults[$field]) //Different default.
					);

				if ( $is_mismatch ) {
					$item[$field] = $item['defaults'][$field];
				}
			}
			$item['defaults'] = $new_defaults;
		}
		return $item;
	}

  /**
   * Custom comparison function that compares menu items based on their position in the menu.
   *
   * @param array $a
   * @param array $b
   * @return int
   */
	public static function compare_position($a, $b){
		return self::get($a, 'position', 0) - self::get($b, 'position', 0);
	}

	/**
	 * Generate a URL for a menu item.
	 *
	 * @param string $item_slug
	 * @param string $parent_slug
	 * @return string An URL relative to the /wp-admin/ directory.
	 */
	public static function generate_url($item_slug, $parent_slug = '') {
		$menu_url = is_array($item_slug) ? self::get($item_slug, 'file') : $item_slug;
		$parent_url = !empty($parent_slug) ? $parent_slug : 'admin.php';

		if ( strpos($menu_url, '://') !== false ) {
			return $menu_url;
		}

		if ( self::is_hook_or_plugin_page($menu_url, $parent_url) ) {
			$base_file = self::is_hook_or_plugin_page($parent_url) ? 'admin.php' : $parent_url;
			$url = add_query_arg(array('page' => $menu_url), $base_file);
		} else {
			$url = $menu_url;
		}
		return $url;
	}

	private static function is_hook_or_plugin_page($page_url, $parent_page_url = '') {
		if ( empty($parent_page_url) ) {
			$parent_page_url = 'admin.php';
		}
		$pageFile = self::remove_query_from($page_url);

		$hasHook = (get_plugin_page_hook($page_url, $parent_page_url) !== null);
		$adminFileExists = is_file(ABSPATH . '/wp-admin/' . $pageFile);
		$pluginFileExists = ($page_url != 'index.php') && is_file(WP_PLUGIN_DIR . '/' . $pageFile);

		return !$adminFileExists && ($hasHook || $pluginFileExists);
	}

	/**
	 * Check if a field is currently set to its default value.
	 *
	 * @param array $item
	 * @param string $field_name
	 * @return bool
	 */
	public static function is_default($item, $field_name) {
		if ( isset($item[$field_name]) ){
			return false;
		} else {
			return isset($item['defaults'], $item['defaults'][$field_name]);
		}
	}

	public static function remove_query_from($url) {
		$pos = strpos($url, '?');
		if ( $pos !== false ) {
			return substr($url, 0, $pos);
		}
		return $url;
	}
}