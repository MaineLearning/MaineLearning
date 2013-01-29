<?php
/**
 * Controls output elements in search form.
 *
 * @category   Genesis
 * @package    Structure
 * @subpackage Search
 * @author     StudioPress
 * @license    http://www.opensource.org/licenses/gpl-license.php GPL-2.0+
 * @link       http://www.studiopress.com/themes/genesis
 */

add_filter( 'get_search_form', 'genesis_search_form' );
/**
 * Replace the default search form with a Genesis-specific form.
 *
 * @since 0.2.0
 *
 * @return string HTML markup
 */
function genesis_search_form() {

	$search_text = get_search_query() ? esc_attr( apply_filters( 'the_search_query', get_search_query() ) ) : apply_filters( 'genesis_search_text', esc_attr__( 'Search this website', 'genesis' ) . '&#x02026;' );

	$button_text = apply_filters( 'genesis_search_button_text', esc_attr__( 'Search', 'genesis' ) );

	$onfocus = " onfocus=\"if (this.value == '$search_text') {this.value = '';}\"";
	$onblur  = " onblur=\"if (this.value == '') {this.value = '$search_text';}\"";
	
	/** Empty label, by default. Filterable. */
	$label = apply_filters( 'genesis_search_form_label', '' );

	$form = '
		<form method="get" class="searchform search-form" action="' . home_url() . '/" >
			' . $label . '
			<input type="text" value="' . esc_attr( $search_text ) . '" name="s" class="s search-input"' . $onfocus . $onblur . ' />
			<input type="submit" class="searchsubmit search-submit" value="' . esc_attr( $button_text ) . '" />
		</form>
	';

	return apply_filters( 'genesis_search_form', $form, $search_text, $button_text, $label );

}