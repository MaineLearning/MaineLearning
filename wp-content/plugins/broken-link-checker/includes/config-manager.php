<?php

/**
 * @author W-Shadow
 * @copyright 2009
 */

if ( !class_exists('blcConfigurationManager') ){

class blcConfigurationManager {
	
	var $option_name;
	
	var $options;
	var $defaults;
	var $loaded_values;
	
	function blcConfigurationManager( $option_name = '', $default_settings = null ){
		$this->option_name = $option_name;
		
		if ( is_array($default_settings) ){
			$this->defaults = $default_settings;
		} else {
			$this->defaults = array();
		}
		$this->loaded_values = array();
		
		$this->options = $this->defaults;
		
		if ( !empty( $this->option_name ) )
			$this->load_options();		
	}
	
	function set_defaults( $default_settings = null ){
		if ( is_array($default_settings) ){
			$this->defaults = array();
		} else {
			$this->defaults = $default_settings;
		}
		$this->options = array_merge($this->defaults, $this->loaded_values);
	}
	
  /**
   * blcOptionManager::load_options()
   * Load plugin options from the database. The current $options values are not affected
   * if this function fails.
   *
   * @param string $option_name
   * @return bool True if options were loaded, false otherwise. 
   */
	function load_options( $option_name = '' ){
		if ( !empty($option_name) ){
			$this->option_name = $option_name;
		}
		
		if ( empty($this->option_name) ) return false;
		
		$new_options = get_option($this->option_name);
        if( !is_array( $new_options ) ){
            return false;
        } else {
        	$this->loaded_values = $new_options;
            $this->options = array_merge( $this->defaults, $this->loaded_values );
            return true;
        }
	}
	
  /**
   * blcOptionManager::save_options()
   * Save plugin options to the databse. 
   *
   * @param string $option_name (Optional) Save the options under this name 
   * @return bool True if settings were saved, false if settings haven't been changed or if there was an error.
   */
	function save_options( $option_name = '' ){
		if ( !empty($option_name) ){
			$this->option_name = $option_name;
		}
		
		if ( empty($this->option_name) ) return false;
        
		return update_option( $this->option_name, $this->options );		
	}
	
	/**
	 * Retrieve a specific setting.
	 * 
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	function get($key, $default = null){
		if ( array_key_exists($key, $this->options) ){
			return $this->options[$key];
		} else {
			return $default;
		}
	}
	
	/**
	 * Update or add a setting.
	 * 
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	function set($key, $value){
		$this->options[$key] = $value;
	}
}

}
?>