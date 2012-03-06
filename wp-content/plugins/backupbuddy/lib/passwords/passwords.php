<?php
if ( !class_exists( 'pluginbuddy_passwords' ) ) {
	class pluginbuddy_passwords {
		private $pass1 = '';
		private $pass2 = '';
		private $button_id = '';
		private $plugin_url = '';
		function __construct( $args = array() ) {
			//Load defaults
			$defaults = array(
				'pagehooks' => array(),
				'pass1' => 'pass1',
				'pass2' => 'pass2',
				'button_id' => 'pb_generate_password',
				'password_length' => 12,
				'special_chars' => true,
				'extra_special_chars' => false
			);
			$args = wp_parse_args( $args, $defaults );
			extract( $args );
			$this->pass1 = $pass1;
			$this->pass2 = $pass2;
			$this->button_id = $button_id;
			$this->password_length = $password_length;
			$this->special_chars = $special_chars;
			$this->extra_special_chars = $extra_special_chars;
			
			$this->plugin_url = rtrim( plugin_dir_url(__FILE__), '/' );
			
			//Add actions based on page detection
			if ( is_string( $pagehooks ) ) {
				add_action( 'admin_print_scripts-' . $pagehooks, array( &$this, 'load_scripts' ) );
				add_action( 'admin_print_styles-' . $pagehooks, array( &$this, 'load_scripts' ) );
			} elseif ( is_array( $pagehooks ) ) {
				foreach ( $pagehooks as $hook ) {
					add_action( 'admin_print_scripts-' . $hook, array( &$this, 'load_scripts' ) );
					add_action( 'admin_print_styles-' . $hook, array( &$this, 'load_scripts' ) );
				} //end foreach
			
			} //end $pagehooks
			if ( !has_action( 'wp_ajax_pb_generate_password', array( &$this, 'ajax_generate_password' ) ) ) {
				add_action( 'wp_ajax_pb_generate_password', array( &$this, 'ajax_generate_password' ) );
			}
		} //end constructor

		public function ajax_generate_password() {
			$password_length = absint( $_POST[ 'password_length' ] );
			$special_chars  = (bool)$_POST[ 'special_chars' ];
			$extra_special_chars = (bool)$_POST[ 'extra_special_chars' ];
			$password = wp_generate_password( $password_length, $special_chars, $extra_special_chars );
			$html = sprintf( "<strong>%s</strong>", __( 'Your Generated Password: ', 'it-l10n-backupbuddy' ) );
			$html .= sprintf( '<input type="text" id="pb_password" value="%s" />', $password );
			$html .= sprintf( '&nbsp; - <a href="#" id="pb_fill_password">%s</a>', __( 'Fill Password', 'it-l10n-backupbuddy' ) );
			$html = sprintf( "<div class='updated'><p>%s</p></div>", $html );
			die( json_encode( array( 'password' => $password, 'html' => $html ) ) );
		} //ajax_generate_password
		public function load_scripts() {
			wp_enqueue_script( 'pb_passwords', $this->plugin_url . '/passwords.js', array( 'jquery' ) );
			$localized_variables = array(
				'pass1' => $this->pass1,
				'pass2' => $this->pass2,
				'button_id' => $this->button_id,
				'password_length' => $this->password_length,
				'special_chars' => $this->special_chars,
				'extra_special_chars' => $this->extra_special_chars,
				
			);
			wp_localize_script( 'pb_passwords', 'pb_passwords', $localized_variables );

		
		} //end load_scripts
		public function load_styles() {
		
		} //end load_styles
		
		public function output_html( $args = array() ) {
			$defaults = array(
				'id' => 'pb_generate_password',
			);
			?>
			<div id='pb_password_generator'>
				<input id='<?php echo esc_attr( $this->button_id ); ?>' type='button' class='primary-secondary' value='<?php esc_html_e( 'Generate Password' ); ?>' />
				<br />
				<div id='pb_password_field'></div>
			</div>
			
			<?php
		} //end output_html	
	} //end pluginbuddy_passwords
} //end if