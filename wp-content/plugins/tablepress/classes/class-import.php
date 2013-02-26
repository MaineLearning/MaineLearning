<?php
/**
 * TablePress Table Import Class
 *
 * @package TablePress
 * @subpackage Export/Import
 * @author Tobias Bäthge
 * @since 1.0.0
 */

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

/**
 * TablePress Table Import Class
 * @package TablePress
 * @subpackage Export/Import
 * @author Tobias Bäthge
 * @since 1.0.0
 */
class TablePress_Import {

	/**
	 * File/Data Formats that are available for import
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	public $import_formats = array();

	/**
	 * Whether ZIP archive support is available in the PHP installation on the server
	 *
	 * @since 1.0.0
	 *
	 * @var bool
	 */
	public $zip_support_available = false;

	/**
	 * Whether HTML import support is available in the PHP installation on the server
	 *
	 * @since 1.0.0
	 *
	 * @var bool
	 */
	public $html_import_support_available = false;

	/**
	 * Data to be imported
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected $import_data;

	/**
	 * Imported table
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected $imported_table = false;

	/**
	 * Initialize the Import class
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// filter from @see unzip_file() in WordPress
		if ( class_exists( 'ZipArchive' ) && apply_filters( 'unzip_file_use_ziparchive', true ) )
			$this->zip_support_available = true;

		if ( class_exists( 'DOMDocument' ) && function_exists( 'simplexml_import_dom' ) && function_exists( 'libxml_use_internal_errors' ) )
			$this->html_import_support_available = true;

		// initiate here, because function call not possible outside a class method
		$this->import_formats = array();
		$this->import_formats['csv'] = __( 'CSV - Character-Separated Values', 'tablepress' );
		if ( $this->html_import_support_available )
			$this->import_formats['html'] = __( 'HTML - Hypertext Markup Language', 'tablepress' );
		$this->import_formats['json'] = __( 'JSON - JavaScript Object Notation', 'tablepress' );
	}

	/**
	 * Import a table
	 *
	 * @since 1.0.0
	 *
	 * @param string $format Import format
	 * @param array $data Data to import
	 * @return bool|array False on error, table array on success
	 */
	public function import_table( $format, $data ) {
		$this->import_data = $data;

		$this->fix_table_encoding();

		switch ( $format ) {
			case 'csv':
				$this->import_csv();
				break;
			case 'html':
				$this->import_html();
				break;
			case 'json':
				$this->import_json();
				break;
			default:
				return false;
		}

		return $this->imported_table;
	}

	/**
	 * Import CSV data
	 *
	 * @since 1.0.0
	 */
	protected function import_csv() {
		$csv_parser = TablePress::load_class( 'CSV_Parser', 'csv-parser.class.php', 'libraries' );
		$csv_parser->load_data( $this->import_data );
		$delimiter = $csv_parser->find_delimiter();
		$data = $csv_parser->parse( $delimiter );
		$this->imported_table = array( 'data' => $this->pad_array_to_max_cols( $data ) );
	}

	/**
	 * Import HTML data
	 *
	 * @since 1.0.0
	 */
	protected function import_html() {
		if ( ! $this->html_import_support_available )
			return false;

		// extract table from HTML, pattern: <table> (with eventually class, id, ...
		// . means any charactery (except newline),
		// * means in any count
		// ? means non-gready (shortest possible)
		// is at the end: i: case-insensitive, s: include newline (in .)
		if ( 1 == preg_match( '#<table.*?>.*?</table>#is', $this->import_data, $matches ) ) {
			$temp_data = $matches[0]; // if found, take match as table to import
		} else {
			$this->imported_table = false;
			return;
		}

		libxml_use_internal_errors( true ); // no warnings/errors raised, but stored internally
		$dom = new DOMDocument();
		$dom->strictErrorChecking = false; // no strict checking for invalid HTML
		$dom->loadHTML( $temp_data );
		if ( false === $dom ) {
			$this->imported_table = false;
			return;
		}
		$table_html = simplexml_import_dom( $dom );
		if ( false === $table_html ) {
			$this->imported_table = false;
			return;
		}

		$errors = libxml_get_errors();
		libxml_clear_errors();
		if ( ! empty( $errors ) ) {
			$output = '<b>' . __( 'The imported file contains errors:', 'tablepress' ) . '</b><br /><br />';
			foreach ( $errors as $error ) {
				switch ( $error->level ) {
					case LIBXML_ERR_WARNING:
						$output .= "Warning {$error->code}: {$error->message} in line {$error->line}, column {$error->column}<br />";
						break;
					case LIBXML_ERR_ERROR:
						$output .= "Error {$error->code}: {$error->message} in line {$error->line}, column {$error->column}<br />";
						break;
					case LIBXML_ERR_FATAL:
						$output .= "Fatal {Error $error->code}: {$error->message} in line {$error->line}, column {$error->column}<br />";
						break;
				}
			}
			wp_die( $output, 'Import Error', array( 'back_link' => true ) );
		}

		$table = $table_html->body->table;

		$html_table = array(
			'data' => array(),
			'options' => array()
		);
		if ( isset( $table->thead ) ) {
			$html_table['data'] = array_merge( $html_table['data'], $this->_import_html_rows( $table->thead[0]->tr ) );
			$html_table['options']['table_head'] = true;
		}
		if ( isset( $table->tbody ) )
			$html_table['data'] = array_merge( $html_table['data'], $this->_import_html_rows( $table->tbody[0]->tr ) );
		if ( isset( $table->tr ) )
			$html_table['data'] = array_merge( $html_table['data'], $this->_import_html_rows( $table->tr ) );
		if ( isset( $table->tfoot ) ) {
			$html_table['data'] = array_merge( $html_table['data'], $this->_import_html_rows( $table->tfoot[0]->tr ) );
			$html_table['options']['table_foot'] = true;
		}

		$html_table['data'] = $this->pad_array_to_max_cols( $html_table['data'] );
		$this->imported_table = $html_table;
	}

	/**
	 * Helper for HTML import
	 *
	 * @since 1.0.0
	 *
	 * @param string $element XMLElement
	 * @return array XMLElement exported to an array
	 */
	protected function _import_html_rows( $element ) {
		$rows = array();
		foreach ( $element as $row ) {
			$new_row = array();
			foreach ( $row as $cell ) {
				if ( 1 === preg_match( '#<t(?:d|h).*?>(.*)</t(?:d|h)>#is', $cell->asXML(), $matches ) ) // get text between <td>...</td>, or <th>...</th>, possibly with attributes
					$new_row[] = $matches[1];
				else
					$new_row[] = '';
			}
			$rows[] = $new_row;
		}
		return $rows;
	}

	/**
	 * Import JSON data
	 *
	 * @since 1.0.0
	 */
	protected function import_json() {
		$json_table = json_decode( $this->import_data, true );

		// Check if JSON could be decoded
		if ( is_null( $json_table ) ) {
			$this->imported_table = false;
			return;
		}

		if ( isset( $json_table['data'] ) )
			// JSON data contained a full export
			$table = $json_table;
		else
			// JSON data contained only the data of a table, but no options
			$table = array( 'data' => $json_table );

		$table['data'] = $this->pad_array_to_max_cols( $table['data'] );
		$this->imported_table = $table;
	}

	/**
	 * Make sure array is rectangular with $max_cols columns in every row
	 *
	 * @since 1.0.0
	 *
	 * @param array $array Two-dimensional array to be padded
	 * @return array Padded array
	 */
	protected function pad_array_to_max_cols( $array ) {
		$rows = count( $array );
		$rows = ( $rows > 0 ) ? $rows : 1;
		$max_columns = $this->count_max_columns( $array );
		$max_columns = ( $max_columns > 0 ) ? $max_columns : 1;
		// array_map wants arrays as additional parameters (so we create one with the max_columns to pad to and one with the value to use (empty string)
		$max_columns_array = array_fill( 1, $rows, $max_columns );
		$pad_values_array = array_fill( 1, $rows, '' );
		return array_map( 'array_pad', $array, $max_columns_array, $pad_values_array );
	}

	/**
	 * Get the highest number of columns in the rows
	 *
	 * @since 1.0.0
	 *
	 * @param array $array Two-dimensional array
	 * @return int Highest number of columns in the rows of the array
	 */
	protected function count_max_columns( $array ) {
		$max_columns = 0;
		if ( ! is_array( $array ) || 0 == count( $array ) )
			return $max_columns;

		foreach ( $array as $row_idx => $row ) {
			$num_columns = count( $row );
			$max_columns = max( $num_columns, $max_columns );
		}
		return $max_columns;
	}

	/**
	 * Fixes the encoding to UTF-8 for the entire string that is to be imported
	 *
	 * @see http://stevephillips.me/blog/dealing-php-and-character-encoding
	 *
	 * @since 1.0.0
	 */
	protected function fix_table_encoding() {
		// Check and remove possible UTF-8 Byte-Order Mark (BOM)
		$bom = pack( 'CCC', 0xef, 0xbb, 0xbf );
		if ( 0 === strncmp( $this->import_data, $bom, 3 ) ) {
			$this->import_data = substr( $this->import_data, 3 );
			return; // If data has a BOM, it's UTF-8, so further checks unnecessary
		}

		// Detect the character encoding and convert to UTF-8, if it's different
		if ( function_exists( 'mb_detect_encoding' ) && function_exists( 'iconv' ) ) {
			$current_encoding = mb_detect_encoding( $this->import_data, 'ASCII, UTF-8, ISO-8859-1' );
			if ( 'UTF-8' != $current_encoding )
				$this->import_data = @iconv( $current_encoding, 'UTF-8', $this->import_data );
		}
	}

} // class TablePress_Import