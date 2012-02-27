<?php
/**
 * Helper functions for accessing Genesis-specific Settings that have been
 * stored in the options table and as post meta data.
 *
 * @category Genesis
 * @package  Options
 * @author   StudioPress
 * @license  http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link     http://www.studiopress.com/themes/genesis
 */

/**
 * Return option from the options database and cache result.
 *
 * Calls filters genesis_pre_get_option_$key and genesis_options.
 *
 * Values pulled from the database are cached on each request, so a second
 * request for the same value won't cause a second DB interaction.
 *
 * @since 0.1.3
 *
 * @uses GENESIS_SETTINGS_FIELD
 *
 * @staticvar array $settings_cache
 * @staticvar array $options_cache
 * @param string $key Option name.
 * @param string $setting Optional. Settings field name. Eventually defaults to
 * GENESIS_SETTINGS_FIELD if not passed as an argument.
 * @param boolean $use_cache Optional. Whether to use the Genesis cache value or not.
 * Default is true.
 * @return mixed The value of this $key in the database.
 */
function genesis_get_option( $key, $setting = null, $use_cache = true ) {

	/**
	 * Get setting. The default is set here, once, so it doesn't have to be
	 * repeated in the function arguments for genesis_option() too.
	 */
	$setting = $setting ? $setting : GENESIS_SETTINGS_FIELD;

	/** If we need to bypass the cache */
	if ( ! $use_cache ) {
		$options = get_option( $setting );

		if ( ! is_array( $options ) || ! array_key_exists( $key, $options ) )
			return '';

		return is_array( $options[$key] ) ? stripslashes_deep( $options[$key] ) : stripslashes( wp_kses_decode_entities( $options[$key] ) );
	}

	/** Setup caches */
	static $settings_cache = array();
	static $options_cache  = array();

	/** Allow child theme to short-circuit this function */
	$pre = apply_filters( 'genesis_pre_get_option_' . $key, null, $setting );
	if ( null !== $pre )
		return $pre;

	/** Check options cache */
	if ( isset( $options_cache[$setting][$key] ) )
		/** Option has been cached */
		return $options_cache[$setting][$key];

	/** Check settings cache */
	if ( isset( $settings_cache[$setting] ) )
		/** Setting has been cached */
		$options = apply_filters( 'genesis_options', $settings_cache[$setting], $setting );
	else
		/** Set value and cache setting */
		$options = $settings_cache[$setting] = apply_filters( 'genesis_options', get_option( $setting ), $setting );

	/** Check for non-existent option */
	if ( ! is_array( $options ) || ! array_key_exists( $key, (array) $options ) )
		/** Cache non-existent option */
		$options_cache[$setting][$key] = '';
	else
		/** Option has not been previously been cached, so cache now */
		$options_cache[$setting][$key] = is_array( $options[$key] ) ? stripslashes_deep( $options[$key] ) : stripslashes( wp_kses_decode_entities( $options[$key] ) );

	return $options_cache[$setting][$key];

}

/**
 * Echo options from the options database.
 *
 * @since 0.1.3
 *
 * @uses genesis_get_option()
 *
 * @param string $key Option name.
 * @param string $setting Optional. Settings field name. Eventually defaults to
 * GENESIS_SETINGS_FIELD.
 * @param boolean $use_cache Optional. Whether to use the Genesis cache value or not.
 * Default is true.
 */
function genesis_option( $key, $setting = null, $use_cache = true ) {

	echo genesis_get_option( $key, $setting, $use_cache );

}

/**
 * Return SEO options from the SEO options database.
 *
 * @since 0.1.3
 *
 * @uses genesis_get_option()
 * @uses GENESIS_SEO_SETTINGS_FIELD
 *
 * @param string $key Option name.
 * @param boolean $use_cache Optional. Whether to use the Genesis cache value or not.
 * Defaults to true.
 * @return mixed The value of this $key in the database.
 */
function genesis_get_seo_option( $key, $use_cache = true ) {

	return genesis_get_option( $key, GENESIS_SEO_SETTINGS_FIELD, $use_cache );

}

/**
 * Echo an SEO option from the SEO options database.
 *
 * @since 0.1.3
 *
 * @uses genesis_option()
 * @uses GENESIS_SEO_SETTINGS_FIELD
 *
 * @param string $key Option name.
 * @param boolean $use_cache Optional. Whether to use the Genesis cache value or not.
 * Defaults to true.
 */
function genesis_seo_option( $key, $use_cache = true ) {

	genesis_option( $key, GENESIS_SEO_SETTINGS_FIELD, $use_cache );

}

/**
 * Echo data from a post/page custom field.
 *
 * Echo only the first value of custom field.
 *
 * @since 0.1.3
 *
 * @uses genesis_get_custom_field()
 *
 * @param string $field Custom field key.
 */
function genesis_custom_field( $field ) {

	echo genesis_get_custom_field( $field );

}

/**
 * Returns custom field post meta data.
 *
 * Return only the first value of custom field.
 * Returns false if field is blank or not set.
 *
 * @since 0.1.3
 *
 * @global integer $id Post ID.
 * @global stdClass $post Post object.
 * @param string $field Custom field key.
 * @return string|boolean Return value or false on failure.
 */
function genesis_get_custom_field( $field ) {

	global $id, $post;

	if ( null === $id && null === $post )
		return false;

	$post_id = null === $id ? $post->ID : $id;

	$custom_field = get_post_meta( $post_id, $field, true );

	if ( $custom_field )
		/** Sanitize and return the value of the custom field */
		return stripslashes( wp_kses_decode_entities( $custom_field ) );

	/** Return false if custom field is empty */
	return false;

}

add_filter( 'get_term', 'genesis_get_term_filter', 10, 2 );
/**
 * Genesis is forced to create its own term-meta data structure in
 * the options table. Therefore, the following function merges that
 * data into the term data structure, via a filter.
 *
 * @since 1.2.0
 *
 * @param object $term Database row object.
 * @param string $taxonomy Taxonomy name that $term is part of.
 * @return object $term Database row object.
 */
function genesis_get_term_filter( $term, $taxonomy ) {

	$db = get_option( 'genesis-term-meta' );
	$term_meta = isset( $db[$term->term_id] ) ? $db[$term->term_id] : array();

	$term->meta = wp_parse_args(
		$term_meta,
		array(
			'headline'            => '',
			'intro_text'          => '',
			'display_title'       => 0, /** vestigial */
			'display_description' => 0, /** vestigial */
			'doctitle'            => '',
			'description'         => '',
			'keywords'            => '',
			'layout'              => '',
			'noindex'             => 0,
			'nofollow'            => 0,
			'noarchive'           => 0,
		)
	);

	/** Sanitize term meta */
	foreach ( $term->meta as $field => $value )
		$term->meta[$field] = apply_filters( 'genesis_term_meta_' . $field, stripslashes( wp_kses_decode_entities( $value ) ), $term, $taxonomy );

	/** Apply Filters */
	$term->meta = apply_filters( 'genesis_term_meta', $term->meta, $term, $taxonomy );

	return $term;

}

/**
 * Takes an array of new settings, merges them with the old settings, and pushes
 * them into the database via update_option().
 *
 * @since 1.7.0
 *
 * @uses GENESIS_SETTINGS_FIELD
 *
 * @access private
 *
 * @param string|array $new New settings. Can be a string, or an array.
 * @param string $setting Optional. Settings field name. Default is GENESIS_SETTINGS_FIELD.
 */
function _genesis_update_settings( $new = '', $setting = GENESIS_SETTINGS_FIELD ) {

	update_option( $setting, wp_parse_args( $new, get_option( $setting ) ) );

}