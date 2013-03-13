/* 
 * Custom Types form JS
 */


jQuery(document).ready(function(){
    /*
     * 
     * Submit form trigger
     */
    jQuery('.wpcf-types-form').submit(function(){
        /*
         * Check if singular and plural are same
         */
        if (jQuery('#name-singular').val().toLowerCase() == jQuery('#name-plural').val().toLowerCase()) {
            jQuery('#wpcf_warning_same_as_slug').fadeOut();
            alert(jQuery('#name-plural').data('wpcf_warning_same_as_slug'));
            jQuery('#name-plural').after('<div class="wpcf-error message updated" id="wpcf_warning_same_as_slug"><p>'+jQuery('#name-plural').data('wpcf_warning_same_as_slug')+'</p></div>').focus().bind('click', function(){
                jQuery('#wpcf_warning_same_as_slug').fadeOut();
            });
            wpcfLoadingButtonStop();
            jQuery('html, body').animate({
                scrollTop: 0
            }, 500);
            return false;
        }
    });
});