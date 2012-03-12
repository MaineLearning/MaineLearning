<?php

/** Add the home featured section */
/** http://teethgrinder.net/articles/2011/12/how-to-add-a-widget-area-in-genesis/ */

add_action( 'genesis_before_loop', 'home_featured' );
function home_featured() {

    /** Do nothing on page 2 or greater */
    if ( get_query_var( 'paged' ) >= 2 )
        return;

    genesis_widget_area( 'home-featured', array(
    'before' => '<div class="home-featured widget-area">',
    ) );

}


genesis();


