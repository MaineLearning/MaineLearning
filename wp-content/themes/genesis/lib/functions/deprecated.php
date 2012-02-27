<?php
/**
 * This file is home to the code that has been deprecated / replaced by other code.
 *
 * It serves as a compatibility mechanism.
 *
 * @category Genesis
 * @package  Deprecated
 * @author   StudioPress
 * @license  http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link     http://www.studiopress.com/themes/genesis
 */

/**
 * Deprecated. Filters the attributes array in the wp_get_attachment_image function.
 *
 * For some reason, the wp_get_attachment_image() function uses the caption
 * field value as the alt text, not the Alternate Text field value. Strange.
 *
 * @since 0.1.8
 * @deprecated 1.8.0
 *
 * @param array    $attr       Associative array of image attributes and values.
 * @param stdClass $attachment Attachment (Post) object.
 */
function genesis_filter_attachment_image_attributes( $attr, $attachment ) {

	_deprecated_function( __FUNCTION__, '1.8.0' );

}

/**
 * Deprecated. Create a category checklist.
 *
 * @since 0.2
 * @deprecated 1.8.0
 *
 * @param string $name     Input name (will be an array) of checkboxes.
 * @param array  $selected Optional. Array of checked inputs. Default is empty array.
 */
function genesis_page_checklist( $name, $selected = array() ) {

	_deprecated_function( __FUNCTION__, '1.8.0' );

}

/**
 * Deprecated. Create a category checklist.
 *
 * @since 0.2
 * @deprecated 1.8.0
 *
 * @param string $name     Input name (will be an array) of checkboxes.
 * @param array  $selected Optional. Array of checked inputs. Default is empty array.
 */
function genesis_category_checklist( $name, $selected = array() ) {

	_deprecated_function( __FUNCTION__, '1.8.0' );

}

/**
 * Deprecated. Wrapper for genesis_pre action hook.
 *
 * @since 0.2.0
 * @deprecated 1.7.0
 */
function genesis_pre() {

	_deprecated_function( __FUNCTION__, '1.7.0', "do_action( 'genesis_pre' )" );

	do_action( 'genesis_pre' );

}

/**
 * Deprecated. Wrapper for genesis_pre_framework action hook.
 *
 * @since 0.2.0
 * @deprecated 1.7.0
 */
function genesis_pre_framework() {

	_deprecated_function( __FUNCTION__, '1.7.0', "do_action( 'genesis_pre_framework' )" );

	do_action( 'genesis_pre_framework' );

}

/**
 * Deprecated. Wrapper for genesis_init action hook.
 *
 * @since 0.2.0
 * @deprecated 1.7.0
 */
function genesis_init() {

	_deprecated_function( __FUNCTION__, '1.7.0', "do_action( 'genesis_init' )" );

	do_action( 'genesis_init' );

}

/**
 * Deprecated. Wrapper for genesis_doctype action hook.
 *
 * @since 0.2.0
 * @deprecated 1.7.0
 */
function genesis_doctype() {

	_deprecated_function( __FUNCTION__, '1.7.0', "do_action( 'genesis_doctype' )" );

	do_action( 'genesis_doctype' );

}

/**
 * Deprecated. Wrapper for genesis_title action hook.
 *
 * @since 0.2.0
 * @deprecated 1.7.0
 */
function genesis_title() {

	_deprecated_function( __FUNCTION__, '1.7.0', "do_action( 'genesis_title' )" );

	do_action( 'genesis_title' );

}

/**
 * Deprecated. Wrapper for genesis_meta action hook.
 *
 * @since 0.2.0
 * @deprecated 1.7.0
 */
function genesis_meta() {

	_deprecated_function( __FUNCTION__, '1.7.0', "do_action( 'genesis_meta' )" );

	do_action( 'genesis_meta' );

}

/**
 * Deprecated. Wrapper for genesis_before action hook.
 *
 * @since 0.2.0
 * @deprecated 1.7.0
 */
function genesis_before() {

	_deprecated_function( __FUNCTION__, '1.7.0', "do_action( 'genesis_before' )" );

	do_action( 'genesis_before' );

}

/**
 * Deprecated. Wrapper for genesis_after action hook.
 *
 * @since 0.2.0
 * @deprecated 1.7.0
 */
function genesis_after() {

	_deprecated_function( __FUNCTION__, '1.7.0', "do_action( 'genesis_after' )" );

	do_action( 'genesis_after' );

}

/**
 * Deprecated. Wrapper for genesis_before_header action hook.
 *
 * @since 0.2.0
 * @deprecated 1.7.0
 */
function genesis_before_header() {

	_deprecated_function( __FUNCTION__, '1.7.0', "do_action( 'genesis_before_header' )" );

	do_action( 'genesis_before_header' );

}

/**
 * Deprecated. Wrapper for genesis_header action hook.
 *
 * @since 0.2.0
 * @deprecated 1.7.0
 */
function genesis_header() {

	_deprecated_function( __FUNCTION__, '1.7.0', "do_action( 'genesis_header' )" );

	do_action( 'genesis_header' );

}

/**
 * Deprecated. Wrapper for genesis_header_right action hook.
 *
 * @since 0.2.0
 * @deprecated 1.7.0
 */
function genesis_header_right() {

	_deprecated_function( __FUNCTION__, '1.7.0', "do_action( 'genesis_header_right' )" );

	do_action( 'genesis_header_right' );

}

/**
 * Deprecated. Wrapper for genesis_after_header action hook.
 *
 * @since 0.2.0
 * @deprecated 1.7.0
 */
function genesis_after_header() {

	_deprecated_function( __FUNCTION__, '1.7.0', "do_action( 'genesis_after_header' )" );

	do_action( 'genesis_after_header' );

}

/**
 * Deprecated. Wrapper for genesis_site_title action hook.
 *
 * @since 0.2.0
 * @deprecated 1.7.0
 */
function genesis_site_title() {

	_deprecated_function( __FUNCTION__, '1.7.0', "do_action( 'genesis_site_title' )" );

	do_action( 'genesis_site_title' );

}

/**
 * Deprecated. Wrapper for genesis_site_description action hook.
 *
 * @since 0.2.0
 * @deprecated 1.7.0
 */
function genesis_site_description() {

	_deprecated_function( __FUNCTION__, '1.7.0', "do_action( 'genesis_site_description' )" );

	do_action( 'genesis_site_description' );

}

/**
 * Deprecated. Wrapper for genesis_before_content_sidebar_wrap action hook.
 *
 * @since 0.2.0
 * @deprecated 1.7.0
 */
function genesis_before_content_sidebar_wrap() {

	_deprecated_function( __FUNCTION__, '1.7.0', "do_action( 'genesis_before_content_sidebar_wrap' )" );

	do_action( 'genesis_before_content_sidebar_wrap' );

}

/**
 * Deprecated. Wrapper for genesis_after_content_sidebar_wrap action hook.
 *
 * @since 0.2.0
 * @deprecated 1.7.0
 */
function genesis_after_content_sidebar_wrap() {

	_deprecated_function( __FUNCTION__, '1.7.0', "do_action( 'genesis_after_content_sidebar_wrap' )" );

	do_action( 'genesis_after_content_sidebar_wrap' );

}

/**
 * Deprecated. Wrapper for genesis_before_content action hook.
 *
 * @since 0.2.0
 * @deprecated 1.7.0
 */
function genesis_before_content() {

	_deprecated_function( __FUNCTION__, '1.7.0', "do_action( 'genesis_before_content' )" );

	do_action( 'genesis_before_content' );

}

/**
 * Deprecated. Wrapper for genesis_after_content action hook.
 *
 * @since 0.2.0
 * @deprecated 1.7.0
 */
function genesis_after_content() {

	_deprecated_function( __FUNCTION__, '1.7.0', "do_action( 'genesis_after_content' )" );

	do_action( 'genesis_after_content' );

}

/**
 * Deprecated. Wrapper for genesis_home action hook.
 *
 * @since 0.2.0
 * @deprecated 1.7.0
 */
function genesis_home() {

	_deprecated_function( __FUNCTION__, '1.7.0', "do_action( 'genesis_home' )" );

	do_action( 'genesis_home' );

}

/**
 * Deprecated. Wrapper for genesis_before_loop action hook.
 *
 * @since 0.2.0
 * @deprecated 1.7.0
 */
function genesis_before_loop() {

	_deprecated_function( __FUNCTION__, '1.7.0', "do_action( 'genesis_before_loop' )" );

	do_action( 'genesis_before_loop' );

}

/**
 * Deprecated. Wrapper for genesis_loop action hook.
 *
 * @since 0.2.0
 * @deprecated 1.7.0
 */
function genesis_loop() {

	_deprecated_function( __FUNCTION__, '1.7.0', "do_action( 'genesis_loop' )" );

	do_action( 'genesis_loop' );

}

/**
 * Deprecated. Wrapper for genesis_after_loop action hook.
 *
 * @since 0.2.0
 * @deprecated 1.7.0
 */
function genesis_after_loop() {

	_deprecated_function( __FUNCTION__, '1.7.0', "do_action( 'genesis_after_loop' )" );

	do_action( 'genesis_after_loop' );

}

/**
 * Deprecated. Wrapper for genesis_before_post action hook.
 *
 * @since 0.2.0
 * @deprecated 1.7.0
 */
function genesis_before_post() {

	_deprecated_function( __FUNCTION__, '1.7.0', "do_action( 'genesis_before_post' )" );

	do_action( 'genesis_before_post' );

}

/**
 * Deprecated. Wrapper for genesis_after_post action hook.
 *
 * @since 0.2.0
 * @deprecated 1.7.0
 */
function genesis_after_post() {

	_deprecated_function( __FUNCTION__, '1.7.0', "do_action( 'genesis_after_post' )" );

	do_action( 'genesis_after_post' );

}

/**
 * Deprecated. Wrapper for genesis_before_post_title action hook.
 *
 * @since 0.2.0
 * @deprecated 1.7.0
 */
function genesis_before_post_title() {

	_deprecated_function( __FUNCTION__, '1.7.0', "do_action( 'genesis_before_post_title' )" );

	do_action( 'genesis_before_post_title' );

}

/**
 * Deprecated. Wrapper for genesis_post_title action hook.
 *
 * @since 0.2.0
 * @deprecated 1.7.0
 */
function genesis_post_title() {

	_deprecated_function( __FUNCTION__, '1.7.0', "do_action( 'genesis_post_title' )" );

	do_action( 'genesis_post_title' );

}

/**
 * Deprecated. Wrapper for genesis_after_post_title action hook.
 *
 * @since 0.2.0
 * @deprecated 1.7.0
 */
function genesis_after_post_title() {

	_deprecated_function( __FUNCTION__, '1.7.0', "do_action( 'genesis_after_post_title' )" );

	do_action( 'genesis_after_post_title' );

}

/**
 * Deprecated. Wrapper for genesis_before_post_content action hook.
 *
 * @since 0.2.0
 * @deprecated 1.7.0
 */
function genesis_before_post_content() {

	_deprecated_function( __FUNCTION__, '1.7.0', "do_action( 'genesis_before_post_content' )" );

	do_action( 'genesis_before_post_content' );

}

/**
 * Deprecated. Wrapper for genesis_post_content action hook.
 *
 * @since 0.2.0
 * @deprecated 1.7.0
 */
function genesis_post_content() {

	_deprecated_function( __FUNCTION__, '1.7.0', "do_action( 'genesis_post_content' )" );

	do_action( 'genesis_post_content' );

}

/**
 * Deprecated. Wrapper for genesis_after_post_content action hook.
 *
 * @since 0.2.0
 * @deprecated 1.7.0
 */
function genesis_after_post_content() {

	_deprecated_function( __FUNCTION__, '1.7.0', "do_action( 'genesis_after_post_content' )" );

	do_action( 'genesis_after_post_content' );

}

/**
 * Deprecated. Wrapper for genesis_after_endwhile action hook.
 *
 * @since 0.2.0
 * @deprecated 1.7.0
 */
function genesis_after_endwhile() {

	_deprecated_function( __FUNCTION__, '1.7.0', "do_action( 'genesis_after_endwhile' )" );

	do_action( 'genesis_after_endwhile' );

}

/**
 * Deprecated. Wrapper for genesis_loop_else action hook.
 *
 * @since 0.2.0
 * @deprecated 1.7.0
 */
function genesis_loop_else() {

	_deprecated_function( __FUNCTION__, '1.7.0', "do_action( 'genesis_loop_else' )" );

	do_action( 'genesis_loop_else' );

}

/**
 * Deprecated. Wrapper for genesis_before_comments action hook.
 *
 * @since 0.2.0
 * @deprecated 1.7.0
 */
function genesis_before_comments() {

	_deprecated_function( __FUNCTION__, '1.7.0', "do_action( 'genesis_before_comments' )" );

	do_action( 'genesis_before_comments' );

}

/**
 * Deprecated. Wrapper for genesis_comments action hook.
 *
 * @since 0.2.0
 * @deprecated 1.7.0
 */
function genesis_comments() {

	_deprecated_function( __FUNCTION__, '1.7.0', "do_action( 'genesis_comments' )" );

	do_action( 'genesis_comments' );

}

/**
 * Deprecated. Wrapper for genesis_list_comments action hook.
 *
 * @since 0.2.0
 * @deprecated 1.7.0
 */
function genesis_list_comments() {

	_deprecated_function( __FUNCTION__, '1.7.0', "do_action( 'genesis_list_comments' )" );

	do_action( 'genesis_list_comments' );

}

/**
 * Deprecated. Wrapper for genesis_after_comments action hook.
 *
 * @since 0.2.0
 * @deprecated 1.7.0
 */
function genesis_after_comments() {

	_deprecated_function( __FUNCTION__, '1.7.0', "do_action( 'genesis_after_comments' )" );

	do_action( 'genesis_after_comments' );

}

/**
 * Deprecated. Wrapper for genesis_before_pings action hook.
 *
 * @since 0.2.0
 * @deprecated 1.7.0
 */
function genesis_before_pings() {

	_deprecated_function( __FUNCTION__, '1.7.0', "do_action( 'genesis_before_pings' )" );

	do_action( 'genesis_before_pings' );

}

/**
 * Deprecated. Wrapper for genesis_pings action hook.
 *
 * @since 0.2.0
 * @deprecated 1.7.0
 */
function genesis_pings() {

	_deprecated_function( __FUNCTION__, '1.7.0', "do_action( 'genesis_pings' )" );

	do_action( 'genesis_pings' );

}

/**
 * Deprecated. Wrapper for genesis_list_pings action hook.
 *
 * @since 0.2.0
 * @deprecated 1.7.0
 */
function genesis_list_pings() {

	_deprecated_function( __FUNCTION__, '1.7.0', "do_action( 'genesis_list_pings' )" );

	do_action( 'genesis_list_pings' );

}

/**
 * Deprecated. Wrapper for genesis_after_pings action hook.
 *
 * @since 0.2.0
 * @deprecated 1.7.0
 */
function genesis_after_pings() {

	_deprecated_function( __FUNCTION__, '1.7.0', "do_action( 'genesis_after_pings' )" );

	do_action( 'genesis_after_pings' );

}

/**
 * Deprecated. Wrapper for genesis_before_comment action hook.
 *
 * @since 0.2.0
 * @deprecated 1.7.0
 */
function genesis_before_comment() {

	_deprecated_function( __FUNCTION__, '1.7.0', "do_action( 'genesis_before_comment' )" );

	do_action( 'genesis_before_comment' );

}

/**
 * Deprecated. Wrapper for genesis_after_comment action hook.
 *
 * @since 0.2.0
 * @deprecated 1.7.0
 */
function genesis_after_comment() {

	_deprecated_function( __FUNCTION__, '1.7.0', "do_action( 'genesis_after_comment' )" );

	do_action( 'genesis_after_comment' );

}

/**
 * Deprecated. Wrapper for genesis_before_comment_form action hook.
 *
 * @since 0.2.0
 * @deprecated 1.7.0
 */
function genesis_before_comment_form() {

	_deprecated_function( __FUNCTION__, '1.7.0', "do_action( 'genesis_before_comment_form' )" );

	do_action( 'genesis_before_comment_form' );

}

/**
 * Deprecated. Wrapper for genesis_comment_form action hook.
 *
 * @since 0.2.0
 * @deprecated 1.7.0
 */
function genesis_comment_form() {

	_deprecated_function( __FUNCTION__, '1.7.0', "do_action( 'genesis_comment_form' )" );

	do_action( 'genesis_comment_form' );

}

/**
 * Deprecated. Wrapper for genesis_after_comment_form action hook.
 *
 * @since 0.2.0
 * @deprecated 1.7.0
 */
function genesis_after_comment_form() {

	_deprecated_function( __FUNCTION__, '1.7.0', "do_action( 'genesis_after_comment_form' )" );

	do_action( 'genesis_after_comment_form' );

}

/**
 * Deprecated. Wrapper for genesis_before_sidebar_widget_area action hook.
 *
 * @since 0.2.0
 * @deprecated 1.7.0
 */
function genesis_before_sidebar_widget_area() {

	_deprecated_function( __FUNCTION__, '1.7.0', "do_action( 'genesis_before_sidebar_widget_area' )" );

	do_action( 'genesis_before_sidebar_widget_area' );

}

/**
 * Deprecated. Wrapper for genesis_sidebar action hook.
 *
 * @since 0.2.0
 * @deprecated 1.7.0
 */
function genesis_sidebar() {

	_deprecated_function( __FUNCTION__, '1.7.0', "do_action( 'genesis_sidebar' )" );

	do_action( 'genesis_sidebar' );

}

/**
 * Deprecated. Wrapper for genesis_after_sidebar_widget_area action hook.
 *
 * @since 0.2.0
 * @deprecated 1.7.0
 */
function genesis_after_sidebar_widget_area() {

	_deprecated_function( __FUNCTION__, '1.7.0', "do_action( 'genesis_after_sidebar_widget_area' )" );

	do_action( 'genesis_after_sidebar_widget_area' );

}

/**
 * Deprecated. Wrapper for genesis_before_sidebar_alt_widget_area action hook.
 *
 * @since 0.2.0
 * @deprecated 1.7.0
 */
function genesis_before_sidebar_alt_widget_area() {

	_deprecated_function( __FUNCTION__, '1.7.0', "do_action( 'genesis_before_sidebar_alt_widget_area' )" );

	do_action( 'genesis_before_sidebar_alt_widget_area' );

}

/**
 * Deprecated. Wrapper for genesis_sidebar_alt action hook.
 *
 * @since 0.2.0
 * @deprecated 1.7.0
 */
function genesis_sidebar_alt() {

	_deprecated_function( __FUNCTION__, '1.7.0', "do_action( 'genesis_sidebar_alt' )" );

	do_action( 'genesis_sidebar_alt' );

}

/**
 * Deprecated. Wrapper for genesis_after_sidebar_alt_widget_area action hook.
 *
 * @since 0.2.0
 * @deprecated 1.7.0
 */
function genesis_after_sidebar_alt_widget_area() {

	_deprecated_function( __FUNCTION__, '1.7.0', "do_action( 'genesis_after_sidebar_alt_widget_area' )" );

	do_action( 'genesis_after_sidebar_alt_widget_area' );

}

/**
 * Deprecated. Wrapper for genesis_before_footer action hook.
 *
 * @since 0.2.0
 * @deprecated 1.7.0
 */
function genesis_before_footer() {

	_deprecated_function( __FUNCTION__, '1.7.0', "do_action( 'genesis_before_footer' )" );

	do_action( 'genesis_before_footer' );

}

/**
 * Deprecated. Wrapper for genesis_footer action hook.
 *
 * @since 0.2.0
 * @deprecated 1.7.0
 */
function genesis_footer() {

	_deprecated_function( __FUNCTION__, '1.7.0', "do_action( 'genesis_footer' )" );

	do_action( 'genesis_footer' );

}

/**
 * Deprecated. Wrapper for genesis_after_footer action hook.
 *
 * @since 0.2.0
 * @deprecated 1.7.0
 */
function genesis_after_footer() {

	_deprecated_function( __FUNCTION__, '1.7.0', "do_action( 'genesis_after_footer' )" );

	do_action( 'genesis_after_footer' );

}

/**
 * Deprecated. Wrapper for genesis_import_export_form action hook.
 *
 * @since 0.2.0
 * @deprecated 1.7.0
 */
function genesis_import_export_form() {

	_deprecated_function( __FUNCTION__, '1.7.0', "do_action( 'genesis_import_export_form' )" );

	do_action( 'genesis_import_export_form' );

}

/**
 * Deprecated. Hook this function to wp_head() and you'll be able to use many of
 * the new IE8 functionality. Not loaded by default.
 *
 * @since 0.2.3
 * @deprecated 1.6.0
 *
 * @link http://ie7-js.googlecode.com/svn/test/index.html
 */
function genesis_ie8_js() {

	_deprecated_function( __FUNCTION__, '1.6.0' );

}

/**
 * Deprecated. The Genesis-specific post date.
 *
 * @since 0.2.3
 * @deprecated 1.5.0
 *
 * @see genesis_post_date_shortcode()
 *
 * @param string $format Optional. Date format. Default is post date format saved in settings.
 * @param string $label  Optional. Label before date. Default is empty string.
 */
function genesis_post_date( $format = '', $label = '' ) {

	_deprecated_function( __FUNCTION__, '1.5.0', 'genesis_post_date_shortcode()' );

	echo genesis_post_date_shortcode( array( 'format' => $format, 'label' => $label ) );

}

/**
 * Deprecated. The Genesis-specific post author link.
 *
 * @since 0.2.3
 * @deprecated 1.5.0
 *
 * @see genesis_post_author_posts_link_shortcode()
 *
 * @param string $label Optional. Label before link. Default is empty string.
 */
function genesis_post_author_posts_link( $label = '' ) {

	_deprecated_function( __FUNCTION__, '1.5.0', 'genesis_post_author_posts_link_shortcode()' );

	echo genesis_post_author_posts_link_shortcode( array( 'before' => $label ) );

}

/**
 * Deprecated. The Genesis-specific post comments link.
 *
 * @since 0.2.3
 * @deprecated 1.5.0
 *
 * @see genesis_post_comments_shortcode()
 *
 * @param string $zero Optional. Text when there are no comments. Default is "No Comments".
 * @param string $one  Optional. Text when there is exactly one comment. Default is "1 Comment".
 * @param string $more Optional. Text when there is more than one comment. Default is "% Comments".
 */
function genesis_post_comments_link( $zero = false, $one = false, $more = false ) {

	_deprecated_function( __FUNCTION__, '1.5.0', 'genesis_post_comments_shortcode()' );

	echo genesis_post_comments_shortcode( array( 'zero' => $zero, 'one' => $one, 'more' => $more ) );

}

/**
 * Deprecated. The Genesis-specific post categories link.
 *
 * @since 0.2.3
 * @deprecated 1.5.0
 *
 * @see genesis_post_categories_shortcode()
 *
 * @param string $sep   Optional. Separator between categories. Default is ", ".
 * @param string $label Optional. Label before first category. Default is empty string.
 */
function genesis_post_categories_link( $sep = ', ', $label = '' ) {

	_deprecated_function( __FUNCTION__, '1.5.0', 'genesis_post_categories_shortcode()' );

	echo genesis_post_categories_shortcode( array( 'sep' => $sep, 'before' => $label ) );

}

/**
 * Deprecated. The Genesis-specific post tags link.
 *
 * @since 0.2.3
 * @deprecated 1.5.0
 *
 * @see genesis_post_tags_shortcode()
 *
 * @param string $sep   Optional. Separator between tags. Default is ", ".
 * @param string $label Optional. Label before first tag. Default is empty string.
 */
function genesis_post_tags_link( $sep = ', ', $label = '' ) {

	_deprecated_function( __FUNCTION__, '1.5.0', 'genesis_post_tags_shortcode()' );

	echo genesis_post_tags_shortcode( array( 'sep' => $sep, 'before' => $label ) );

}

/**
 */
/**
 * Deprecated. Allows a child theme to add new image sizes.
 *
 * Use add_image_size() instead.
 *
 * @since 0.1.7
 * @deprecated 1.2.0
 *
 * @param string  $name   Name of the image size.
 * @param integer $width  Width of the image size.
 * @param integer $height Height of the image size.
 * @param boolean $crop   Whether to crop or not.
 */
function genesis_add_image_size( $name, $width = 0, $height = 0, $crop = false ) {

	_deprecated_function( __FUNCTION__, '1.2.0', 'add_image_size()' );

	add_image_size( $name, $width, $height, $crop );

}

/**
 * Deprecated. Filters intermediate sizes for WP 2.8 backward compatibility.
 *
 * @since 0.1.7
 * @deprecated 1.2.0
 *
 * @param array $sizes Array of sizes to add.
 *
 * @return array Empty array.
 */
function genesis_add_intermediate_sizes( $sizes ) {

	_deprecated_function( __FUNCTION__, '1.2.0' );

	return array();

}

/**
 * Deprecated. Was a wrapper for genesis_comment hook, but now calls
 * genesis_after_comment action hook instead.
 *
 * @since 0.2.0
 * @deprecated 1.2.0
 */
function genesis_comment() {

	_deprecated_function( __FUNCTION__, '1.2.0', "do_action( 'genesis_after_comment' )" );

	do_action( 'genesis_after_comment' );

}