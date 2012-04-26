<?php
/**
 * Helper class for WP_Document_Revisions that registers shortcodes, widgets, etc. for use on the front-end
 * @since 1.2
 */

class Document_Revisions_Front_End {

	static $parent;
	static $instance;

	/**
	 *  Registers front end hooks
	 */
	function __construct( &$instance = null) {

		self::$instance = &$this;

		//create or store parent instance
		if ( $instance === null )
			self::$parent = new Document_Revisions;
		else
			self::$parent = &$instance;

		add_shortcode( 'document_revisions', array( &$this, 'revisions_shortcode' ) );
		add_shortcode( 'documents', array( &$this, 'documents_shortcode' ) );
		add_filter( 'document_shortcode_atts', array( &$this, 'shortcode_atts_hyphen_filter' ) );

	}


	/**
	 * Provides support to call functions of the parent class natively
	 * @since 1.2
	 * @param function $function the function to call
	 * @param array $args the arguments to pass to the function
	 * @returns mixed the result of the function
	 */
	function __call( $function, $args ) {

		if ( method_exists( self::$parent, $function ) ) {
			return call_user_func_array( array( &self::$parent, $function ), $args );
		} else {
			//function does not exist, provide error info
			$backtrace = debug_backtrace();
			trigger_error( 'Call to undefined method ' . $function . ' on line ' . $backtrace[1][line] . ' of ' . $backtrace[1][file], E_USER_ERROR );
			die();
		}

	}


	/**
	 * Provides support to call properties of the parent class natively
	 * @since 1.2
	 * @param string $name the property to fetch
	 * @returns mixed the property's value
	 */
	function __get( $name ) {
		return Document_Revisions::$$name;
	}


	/**
	 * Callback to display revisions
	 * @param $atts array attributes passed via short code
	 * @returns string a UL with the revisions
	 * @since 1.2
	 */
	function revisions_shortcode( $atts ) {

		//extract args
		extract( shortcode_atts( array(
					'id' => null,
					'number' => null,
				), $atts ) );

		//do not show output to users that do not have the read_document_revisions capability
		if ( !current_user_can( 'read_document_revisions' ) )
			return;

		//get revisions
		$revisions = $this->get_revisions( $id );

		//show a limited number of revisions
		if ( $number != null )
			$revisions = array_slice( $revisions, 0, (int) $number );

		//buffer output to return rather than echo directly
		ob_start();
?>
		<ul class="revisions document-<?php echo $id; ?>">
		<?php
		//loop through each revision
		foreach ( $revisions as $revision ) { ?>
			<li class="revision revision-<?php echo $revision->ID; ?>" >
				<?php printf( __( '<a href="%1$s" title="%2$s" id="%3$s" class="timestamp">%4$s</a> <span class="agoby">ago by</a> <span class="author">%5$s</a>', 'wp-document-revisions' ), get_permalink( $revision->ID ), $revision->post_date, strtotime( $revision->post_date ), human_time_diff( strtotime( $revision->post_date ), current_time('timestamp') ), get_the_author_meta( 'display_name', $revision->post_author ) ); ?>
			</li>
		<?php } ?>
		</ul>
		<?php
		//grab buffer contents and clear
		$output = ob_get_contents();
		ob_end_clean();
		return $output;

	}


	/**
	 * Shortcode to query for documents
	 * Takes most standard WP_Query parameters (must be int or string, no arrays)
	 * See get_documents in wp-document-revisions.php for more information
	 * @since 1.2
	 * @param array $atts shortcode attributes
	 * @return string the shortcode output
	 */
	function documents_shortcode( $atts ) {

		$defaults = array(
			'orderby' => 'modified',
			'order' => 'DESC',
		);

		//list of all string or int based query vars (because we are going through shortcode)
		// via http://codex.wordpress.org/Class_Reference/WP_Query#Parameters
		$keys = array( 'author', 'author_name', 'cat', 'category_name', 'category__and', 'tag', 'tag_id', 'p', 'name', 'post_parent', 'post_status', 'numberposts', 'year', 'monthnum', 'w', 'day', 'hour', 'minute', 'second', 'meta_key', 'meta_value', 'meta_value_num', 'meta_compare');

		foreach ( $keys as $key )
			$defaults[ $key ] = null;

		$taxs = get_taxonomies( array( 'object_type' => array( 'document' ) ), 'objects' );

		//allow querying by custom taxonomy
		foreach ( $taxs as $tax )
			$defaults[ $tax->query_var ] = null;

		$atts = apply_filters( 'document_shortcode_atts', $atts );

		//default arguments, can be overriden by shortcode attributes
		$atts = shortcode_atts( $defaults, $atts );
		$atts = array_filter( $atts );

		$documents = $this->get_documents( $atts );

		//buffer output to return rather than echo directly
		ob_start();
?>
		<ul class="documents">
		<?php
		//loop through found documents
		foreach ( $documents as $document ) { ?>
			<li class="document document-<?php echo $document->ID; ?>">
				<a href="<?php echo get_permalink( $document->ID ); ?>">
					<?php echo get_the_title( $document->ID ); ?>
				</a>
			</li>
		<?php } ?>
		</ul>
		<?php
		//grab buffer contents and clear
		$output = ob_get_contents();
		ob_end_clean();
		return $output;

	}


	/**
	 * Provides workaround for taxonomies with hyphens in their name
	 * User should replace hyphen with underscope and plugin will compensate
	 */
	function shortcode_atts_hyphen_filter( $atts ) {

		foreach ( (array) $atts as $k => $v ) {

			if ( strpos( $k, '_' ) === false )
				continue;

			$alt = str_replace( '_', '-', $k );

			if ( !taxonomy_exists( $alt ) )
				continue;

			$atts[ $alt ] = $v;
			unset( $atts[ $k] );

		}

		return $atts;
	}


}


/**
 * Recently revised documents widget
 */
class Document_Revisions_Recently_Revised_Widget extends WP_Widget {

	//default settings
	private $defaults = array(
		'numberposts' => 5,
		'post_status' => array( 'publish' => true, 'private' => false, 'draft' => false ),
		'show_author' => true,
	);

	/**
	 * Init widget and register
	 */
	function __construct() {
		parent::WP_Widget( 'Document_Revisions_Recently_Revised_Widget', $name = 'Recently Revised Documents' );
		add_action( 'widgets_init', create_function( '', 'return register_widget("Document_Revisions_Recently_Revised_Widget");' ) );

		//can't i18n outside of a function
		$this->defaults['title'] = __( 'Recently Revised Documents', 'wp-document-revisions' );

	}


	/**
	 * Callback to display widget contents
	 */
	function widget( $args, $instance ) {

		global $wpdr;
		if ( !$wpdr )
			$wpdr = Document_Revisions::$instance;

		extract( $args );

		//enabled statuses are stored as status => bool, but we want an array of only activated statuses
		$statuses = array_filter( (array) $instance['post_status'] );
		$statuses = array_keys( $statuses );

		$query = array(
			'orderby'     => 'modified',
			'order'       => 'DESC',
			'numberposts' => (int) $instance['numberposts'],
			'post_status' => $statuses,
		);

		$documents = $wpdr->get_documents( $query );

		//no documents, don't bother
		if ( !$documents )
			return;

		echo $before_widget;

		echo $before_title . apply_filters( 'widget_title', $instance['title'] ) . $after_title;

		echo "<ul>\n";

		foreach ( $documents as $document ) {

			$link = ( current_user_can( 'edit_post', $document->ID ) ) ? admin_url( 'post.php?post=' . $document->ID . '&action=edit' ) : get_permalink( $document->ID );

?>
			<li><a href="<?php echo $link ?>"><?php echo get_the_title( $document->ID ); ?></a><br />

			<?php if ( $instance['show_author'] ) {

				printf( __( '%1$s ago by %2$s', 'wp-document-revisions'),
					human_time_diff( strtotime( $document->post_modified_gmt ) ),
					get_the_author_meta( 'display_name', $document->post_author )
				);

			} else {

				printf( __( '%1$s ago', 'wp-document-revisions'), human_time_diff( strtotime( $document->post_modified_gmt ) ) );

			} ?>

			</li>

		<?php }

		echo "</ul>\n";

		echo $after_widget;

	}


	/**
	 * Callback to display widget options form
	 */
	function form( $instance ) {

		foreach ( $this->defaults as $key => $value )
			if ( !isset( $instance[ $key ] ) )
				$instance[ $key ] = $value;

?>
		<p>
		<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $instance['title']; ?>" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id('numberposts'); ?>"><?php _e('Number of Posts:', 'wp-document-revisions' ); ?></label><br />
		<input class="small-text" id="<?php echo $this->get_field_id('numberposts'); ?>" name="<?php echo $this->get_field_name('numberposts'); ?>" type="text" value="<?php echo $instance['numberposts']; ?>" />
		</p>
		<p>
		<?php _e('Posts to Show:', 'wp-document-revisions' ); ?><br />
		<?php foreach ( $instance['post_status'] as $status => $value ) { ?>
		<input type="checkbox" id="<?php echo $this->get_field_id('post_status_' . $status ); ?>" name="<?php echo $this->get_field_name('post_status_' . $status ); ?>" type="text" <?php checked( $value ); ?> />
		<label for="<?php echo $this->get_field_name('post_status_' . $status ); ?>"><?php echo ucwords( __( $status ) ); ?></label><br /><?php } ?>
		</p>
		<p>
		<label for="<?php echo $this->get_field_id('show_author'); ?>"><?php _e('Display Document Author:', 'wp-document-revisions' ); ?></label><br />
		<input type="checkbox" id="<?php echo $this->get_field_id('show_author'); ?>" name="<?php echo $this->get_field_name('show_author'); ?>" <?php checked( $instance['show_author'] ); ?> /> <?php _e( 'Yes', 'wp-document-revisions' );?>
		</p>
		<?php
	}


	/**
	 * Sanitizes options ans saves
	 */
	function update( $new_instance, $old_instance ) {

		$instance = $old_instance;
		$instance['title']       = strip_tags( $new_instance['title'] );
		$instance['numberposts'] = (int) $new_instance['numberposts'];
		$instance['show_author'] = (bool) $new_instance['show_author'];

		//merge post statuses into an array
		foreach ( $this->defaults['post_status'] as $status => $value )
			$instance[ 'post_status'][ $status ] = (bool) isset( $new_instance[ 'post_status_' . $status ] );

		return $instance;

	}


}


new Document_Revisions_Recently_Revised_Widget;