<?php
/**
* Generic object class.
*/

class TI_Obj {

	/**
	* Update from $_POST
	* Note that ALL attributes will be set, whether they are present in $_POST or not
	* To prevent an attribute from being set prefix it with "_"
	*
	*/
	function update($atts=null) {
		$obj_atts = get_object_vars($this);

		foreach($obj_atts as $key => $value) {
			if (substr($key, 0, 1) != '_' )
				$this->$key = (isset($atts[$key])) ? $atts[$key] : null;
		}
	}

	static function create_db($table, $auto) {
		global $wpdb, $EZSQL_ERROR;

		$table = $wpdb->prefix . $table;

		if ($auto) {
			$result = $wpdb->query ("CREATE TABLE IF NOT EXISTS $table (
									id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
									obj LONGTEXT)
									CHARACTER SET utf8;");
			// The ALTER commands are specifically for versions <= 2.05 where wrong charset was used
			$wpdb->query ("ALTER TABLE $table CHARACTER SET = utf8");
			$wpdb->query ("ALTER TABLE $table CHANGE COLUMN obj obj LONGTEXT NULL DEFAULT NULL;");
		}
		else {
			$result = $wpdb->query ("CREATE TABLE IF NOT EXISTS $table (
									id VARCHAR(40) NOT NULL PRIMARY KEY,
									obj LONGTEXT)
									CHARACTER SET utf8;");
			// The ALTER commands are specifically for versions <= 2.05 where wrong charset was used
			$wpdb->query ("ALTER TABLE $table CHARACTER SET = utf8");
			$wpdb->query ("ALTER TABLE $table CHANGE COLUMN obj obj LONGTEXT NULL DEFAULT NULL;");
			$wpdb->query ("ALTER TABLE $table CHANGE COLUMN id id VARCHAR(40) NOT NULL;");
		}

		if ($result === false)
			return new WP_Error('fatal', "Error during create_db() on table $table");
	}

	/**
	* Static
	*/
	static function get($table, $id) {
		global $wpdb;

		$table = $wpdb->prefix . $table;
		$result = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %s", $id));

		if ($result === false)
			return new WP_Error('fatal', "Error during get() on table $table.  Error=" . $wpdb->last_error);

		$obj = unserialize($result->obj);

		if (!$obj)
			return new WP_Error('fatal', "Error unserializing object during Get().  Obj=" . $result->obj);

		$obj->_id = $result->id;
		return $obj;
	}

	/**
	* Static
	*/
	static function get_list($table) {
		global $wpdb;

		$table = $wpdb->prefix . $table;
		$results = $wpdb->get_col($wpdb->prepare("SELECT id FROM $table"));

		if ($results === false)
			return new WP_Error('fatal', "Error during get_list() on table $table : " . $wpdb->last_error);

		return $results;
	}

	/**
	* Static
	*/
	static function delete($table, $id) {
		global $wpdb;
		$table = $wpdb->prefix . $table;

		if (!$id)
			return false;

		$result = $wpdb->query($wpdb->prepare("DELETE from $table WHERE id = '%s'", $id));
		if ($result === false) {
			return new WP_Error('fatal', "Error during delete() on table $table : " . $wpdb->last_error);
		}
	}

	function save($table) {
		global $wpdb;

		$table = $wpdb->prefix . $table;
		$obj = serialize($this);

		if (!$this->_id) {
			// Insert with auto-number
			$result = $wpdb->query($wpdb->prepare("INSERT INTO $table (obj) VALUES(%s)", $obj));

			if ($result === false)
				return new WP_Error('fatal', "Error during insert on auto-numbered table $table : " . $wpdb->last_error);

			// Update the ID after the insert
			$this->_id = (int)$wpdb->get_var("SELECT LAST_INSERT_ID()");

			if (!$this->_id)
				return new WP_Error('fatal', "Error during insert on auto-numbered table $table : " . $wpdb->last_error);
		} else {
			// Update existing entry or insert with a user-defined key
			$result = $wpdb->query($wpdb->prepare("INSERT INTO $table (id, obj) VALUES(%s, %s) ON DUPLICATE KEY UPDATE obj = %s", $this->_id, $obj, $obj));

			if ($result === false)
				return new WP_Error('fatal', "Error during insert on user-keyed table $table : " . $wpdb->last_error);
		}
	}
}
?>
