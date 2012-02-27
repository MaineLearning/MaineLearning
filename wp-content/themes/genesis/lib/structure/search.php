<?php
/**
 * Controls output elements in search form.
 *
 * @category   Genesis
 * @package    Structure
 * @subpackage Search
 * @author     StudioPress
 * @license    http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link       http://www.studiopress.com/themes/genesis
 */

add_filter( 'get_search_form', 'genesis_search_form' );
/**
 * Replace the default search form with a Genesis-specific form.
 *
 * @since 0.2.0
 *
 * @uses g_ent() Pass entities through a filter
 *
 * @return string HTML markup
 */
function genesis_search_form() {

	$search_text = get_search_query() ? esc_attr( apply_filters( 'the_search_query', get_search_query() ) ) : apply_filters( 'genesis_search_text', sprintf( esc_attr__( 'Search this website %s', 'genesis' ), g_ent( '&hellip;' ) ) );

	$button_text = apply_filters( 'genesis_search_button_text', esc_attr__( 'Search', 'genesis' ) );

	$onfocus = " onfocus=\"if (this.value == '$search_text') {this.value = '';}\"";
	$onblur  = " onblur=\"if (this.value == '') {this.value = '$search_text';}\"";
	
	/** Empty label, by default. Filterable. */
	$label = apply_filters( 'genesis_search_form_label', '' );

	$form = '
		<form method="get" class="searchform" action="' . home_url() . '/" >
			' . $label . '
			<input type="text" value="' . $search_text . '" name="s" class="s"' . $onfocus . $onblur . ' />
			<input type="submit" class="searchsubmit" value="' . $button_text . '" />
		</form>
	';

	return apply_filters( 'genesis_search_form', $form, $search_text, $button_text, $label );

}