<?php




/**
 * Settings Page wrapper class
 *
 */

class Custom_CSS_Manager_Settings_Page{

	/**
	 * Settings option name
	 *
	 * @var string
	 */
	private $option_name = "custom-css-manager-settings";

	

	/**
	 * settings sections array
	 *
	 * @var array
	 */
	private $settings_sections = array();


	/**
	 * Settings fields array
	 *
	 * @var array
	 */
	private $settings_fields = array();
	
	
	
	/**
	 * Settings page sidebar info array
	 *
	 * @var array
	 */
	private $sidebar_info = array();	
	
	
	
	
	function __construct( $option_name ){
	
		$this->option_name = $option_name;

	}
	
	


	 /**
	 * Initialize and registers the settings sections and fileds to WordPress
	 *
	 * this should be called at `admin_init` hook.
	 *
	 * This function gets the initiated settings sections and fields. Then
	 * registers them to WordPress and ready for use.
	 */

	function init() {

		//register sections
		foreach ($this->settings_sections as $section) {
			add_settings_section( $section['id'], $section['title'], '__return_false', $this->option_name  );
		}

		//register fields
		foreach ($this->settings_fields as $section => $field) {
			foreach ($field as $option) {
				$args = array(
					'id' => $option['name'],
					'desc' => $option['desc'],
					'name' => $option['label'],
					'section' => $section,
					'size' => isset( $option['size'] ) ? $option['size'] : null,
					'options' => isset( $option['options'] ) ? $option['options'] : '',
					'default' => isset( $option['default'] ) ? $option['default'] : ''
				);
				
				add_settings_field( $section . '[' . $option['name'] . ']', $option['label'], array($this, 'do_' . $option['type']), $this->option_name, $section, $args);
			}
		}

		//register settings in the options table
		register_setting( $this->option_name, $this->option_name, array(&$this, 'sanitize_page_options') );
	  
		
	}


	
	private function merge_options($input){
	
		$options = get_option( $this->option_name, array() );
		$options = is_array($options) ? $options : array();
		return array_merge($options, $input);
		
	}
	
	
	function sanitize_page_options($input){

		return $this->merge_options($input);
		
	}
	

	/**
	 * Show the section settings forms
	 *
	 * This function displays each of the sections in a different form
	 */
	 
	function show_settings_forms() {
	?>
		<form method="post" action="options.php">
		
			<?php settings_fields( $this->option_name ); ?>
					
			<?php $this->do_custom_settings_sections( $this->option_name ); ?>

		</form>
	<?php
	}




	//this function is derived from the wp core do_settings_sections() function
	function do_custom_settings_sections( $page ) {
		global $wp_settings_sections, $wp_settings_fields;
	
		if ( ! isset( $wp_settings_sections ) || !isset( $wp_settings_sections[$page] ) )
			return;
		
		foreach ( (array) $wp_settings_sections[$page] as $section ) {
		
			$default = array_slice(reset($this->settings_sections),0,1);
			
			//if the tab is specifically set, or its the first (default) tab, then display the settings section
			$tab = isset($_GET['tab']) ? $_GET['tab'] : "default";
			if( ( $section['id'] == $tab ) || ( !isset($_GET['tab']) && ( $section['id'] == $default['id'] ) ) ){
				
				echo "<div id='{$section['id']}' class='postbox'>\n";
					if ( $section['title'] ){
						echo "<h3 class='hndle'><span>{$section['title']}</span></h3>\n";
					}else{
						echo "<h3 class='hndle'><span></span></h3>\n";
					}
					echo "<div class='inside'>\n";

						if ( $section['callback'] ){
							call_user_func( $section['callback'], $section );
						}
								
						//if no settings fields are defined for this section print the end of the box and exit the loop
						if ( ! isset( $wp_settings_fields ) || !isset( $wp_settings_fields[$page] ) || !isset( $wp_settings_fields[$page][$section['id']] ) ){
								echo "</div>\n";
							echo "</div>\n";
							continue;
						}
							
						//display the form fields
						echo '<table class="form-table">';
						do_settings_fields( $page, $section['id'] );
						echo '</table>';
						
						//display submit button
						echo '<div style="padding-left: 1.5em; margin-left:5px;">';
						submit_button();
						echo "</div>";
					
						
					echo "</div>\n";
				echo "</div>\n";
				
			}	
		}
	}




	public function show_sidebar(){
	
		foreach($this->sidebar_info as $box){
		
			echo '<div id="'.$box['id'].'" class="postbox">
					<h3 class="hndle"><span>'.$box['title'].'</span></h3>
					<div class="inside">'.$box['content'].'</div>
				</div>';
				
		}
	
	}
	
	
	
	public function show_tab_nav(){
	
		$default = array_slice(reset($this->settings_sections),0,1);
		$tab = isset($_GET['tab']) ? $_GET['tab'] : $default['id'];
		
		echo '<h3 class="nav-tab-wrapper">';
		foreach( $this->settings_sections as $section ){
			$class = ( $tab == $section['id'] ) ? ' nav-tab-active' : '';
			echo "<a class='nav-tab$class' href='?page=".$this->option_name."&tab=".$section['id']."'>".$section['title']."</a>";
	
		}
		echo '</h3>';

	}

	
	
	

	 /**
	 * Displays a text field for a settings field
	 *
	 * @param array $args settings field args
	 */
	function do_text( $args ) {

		$value = esc_attr( $this->get_section_option( $args['section'], $args['id'], $args['default'] ) );
		$size = isset( $args['size'] ) && !is_null( $args['size'] ) ? $args['size'] : 'regular';

		$field = sprintf( '<input type="text" class="%1$s-text" id="%2$s[%3$s]" name="%5$s[%2$s][%3$s]" value="%4$s"/>', $size, $args['section'], $args['id'], $value, $this->option_name );
		$field .= sprintf( '<p><span class="description"> %s</span></p>', $args['desc'] );

		echo $field;
	}

	/**
	 * Displays a checkbox for a settings field
	 *
	 * @param array $args settings field args
	 */
	function do_checkbox( $args ) {

		$value = esc_attr( $this->get_section_option( $args['section'], $args['id'], $args['default'] ) );

		$html = sprintf( '<input type="checkbox" class="checkbox" id="%5$s[%1$s][%2$s]" name="%5$s[%1$s][%2$s]" value="on"%4$s />', $args['section'], $args['id'], $value, checked( $value, 'on', false ), $this->option_name );
		$html .= sprintf( '<label for="%4$s[%1$s][%2$s]"> %3$s</label>', $args['section'], $args['id'], $args['desc'], $this->option_name );

		echo $html;
	}

	/**
	 * Displays a multicheckbox a settings field
	 *
	 * @param array $args settings field args
	 */
	function do_multicheck( $args ) {

		$value =  $this->get_section_option( $args['section'], $args['id'], $args['default'] );

		$html = '';
		foreach ($args['options'] as $key => $label) {
			$checked = isset( $value[$key] ) ? $value[$key] : '0';
			$html .= sprintf( '<input type="checkbox" class="checkbox" id="%5$s[%1$s][%2$s][%3$s]" name="%5$s[%1$s][%2$s][%3$s]" value="%3$s"%4$s />', $args['section'], $args['id'], $key, checked( $checked, $key, false ), $this->option_name );
			$html .= sprintf( '<label for="%5$s[%1$s][%2$s][%4$s]"> %3$s</label><br>', $args['section'], $args['id'], $label, $key, $this->option_name );
		}
		$html .= sprintf( '<span class="description"> %s</label>', $args['desc'] );

		echo $html;
	}

	/**
	 * Displays a multicheckbox a settings field
	 *
	 * @param array $args settings field args
	 */
	function do_radio( $args ) {

		$value = $this->get_section_option( $args['section'], $args['id'], $args['default'] );

		$html = '';
		foreach ($args['options'] as $key => $label) {
			$html .= sprintf( '<input type="radio" class="radio" id="%5$s[%1$s][%2$s][%3$s]" name="%5$s[%1$s][%2$s]" value="%3$s"%4$s />', $args['section'], $args['id'], $key, checked( $value, $key, false ), $this->option_name );
			$html .= sprintf( '<label for="%5$s[%1$s][%2$s][%4$s]"> %3$s</label><br>', $args['section'], $args['id'], $label, $key, $this->option_name );
		}
		$html .= sprintf( '<span class="description"> %s</label>', $args['desc'] );

		echo $html;
	}

	/**
	 * Displays a selectbox for a settings field
	 *
	 * @param array $args settings field args
	 */
	function do_select( $args ) {

		$value = esc_attr( $this->get_section_option( $args['section'], $args['id'], $args['default'] ) );
		$size = isset( $args['size'] ) && !is_null( $args['size'] ) ? $args['size'] : 'regular';

		$html = sprintf( '<select class="%1$s" name="%4$s[%2$s][%3$s]" id="%4$s[%2$s][%3$s]">', $size, $args['section'], $args['id'], $this->option_name );
		foreach ($args['options'] as $key => $label) {
			$html .= sprintf( '<option value="%s"%s>%s</option>', $key, selected( $value, $key, false ), $label );
		}
		$html .= sprintf( '</select>' );
		$html .= sprintf( '<span class="description"> %s</span>', $args['desc'] );

		echo $html;
	}

	/**
	 * Displays a textarea for a settings field
	 *
	 * @param array $args settings field args
	 */
	function do_textarea( $args ) {

		$value = esc_attr( $this->get_section_option( $args['section'], $args['id'], $args['default'] ) );
		$size = isset( $args['size'] ) && !is_null( $args['size'] ) ? $args['size'] : 'regular';

		$html = sprintf( '<textarea rows="5" cols="55" class="%1$s-text" id="%5$s[%2$s][%3$s]" name="%5$s[%2$s][%3$s]">%4$s</textarea>', $size, $args['section'], $args['id'], $value, $this->option_name );
		$html .= sprintf( '<br><span class="description"> %s</span>', $args['desc'] );

		echo $html;
	}




	/**
	 * Get the value of a settings field
	 *
	 * @param string $section the section name this field belongs to     
	 * @param string $option settings field name
	 * @param string $default default text if the option is not set
	 * @return string
	 */
	function get_section_option( $section, $option, $default = '' ) {
		
		$options = get_option( $this->option_name );

		if ( isset( $options[$section][$option] ) ) {
			return $options[$section][$option];
		}

		return $default;
	}





	/**
	 * Add settings section
	 *
	 * @param array $section single setting section array
	 */
	public function add_section( $section ){
		$sections = $this->settings_sections;
		$sections[] = $section;
		$this->settings_sections = $sections;
	
	}
	



	/**
	 * Set settings sections
	 *
	 * @param array $sections setting sections array
	 */
	function set_sections( $sections ) {
		$this->settings_sections = $sections;
	}

	/**
	 * Set settings fields
	 *
	 * @param array $fields settings fields array
	 */
	function set_fields( $fields ) {
		$this->settings_fields = $fields;
	}
	
	
	/**
	 * Set sidebar info
	 *
	 * @param array $fields settings fields array
	 */
	function set_sidebar( $info ) {
		$this->sidebar_info = $info;
	}
	
	
	/**
	 * Set option name
	 *
	 * @param array $fields settings fields array
	 */
	function set_option_name( $name ) {
		$this->option_name = $name;
	}
	
}




?>