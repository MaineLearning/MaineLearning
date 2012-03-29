<?php
/*
 * Footer credit
 */
add_action('init', 'wpcf_footer_credits_init');

/**
 * Init function. 
 */
function wpcf_footer_credits_init() {
    $template = get_template();
    $option = get_option('wpcf_footer_credit', false);
    if ($option == false) {
        $option['active'] = wpcf_footer_credits_check_new();
    }
    if ($option['active']) {
        if (in_array($template, array('twentyten', 'twentyeleven'))) {
            add_action($template . '_credits', 'wpcf_footer_credit_render');
        } else if ($template == 'canvas') {
            add_action('woo_footer_right_before', 'wpcf_footer_credit_render');
        } else if ($template == 'genesis') {
            add_action('genesis_footer', 'wpcf_footer_credit_render', 10);
        } else if ($template == 'thesis_18') {
            add_action('thesis_hook_footer', 'wpcf_footer_credit_render', 1);
        } else if ($template == 'headway') {
            add_action('headway_footer_open', 'wpcf_footer_credit_render', 11);
        } else {
            add_action('wp_footer', 'wpcf_footer_credit_render');
        }
    }
}

/**
 * Check if it's fresh install
 */
function wpcf_footer_credits_check_new() {
    $options = array(
        'wpcf-custom-taxonomies',
        'wpcf-custom-types',
        'wpcf-fields',
    );
    $data = wpcf_footer_credit_defaults();
    shuffle($data);
    $message = rand(0, count($data));
    $check = true;
    foreach ($options as $option) {
        $option = get_option($option, false);
        if ($option != false) {
            $check = false;
            $active = get_option('wpcf_footer_credit', false);
            if ($active == false) {
                update_option('wpcf_footer_credit',
                        array('active' => 0, 'message' => $message));
            }
            return $check;
            break;
        }
    }
    update_option('wpcf_footer_credit',
            array('active' => 1, 'message' => $message));
    return $check;
}

/**
 * Default credits.
 * 
 * @return type 
 */
function wpcf_footer_credit_defaults() {
    return array(
        sprintf(__("Functionality enhanced using %sWordPress Custom Fields%s",
                        'wpcf'),
                '<a href="http://wp-types.com/documentation/user-guides/using-custom-fields/" target="_blank">',
                '</a>'),
        sprintf(__("Functionality enhanced using %sWordPress Custom Post Types%s",
                        'wpcf'),
                '<a href="http://wp-types.com/documentation/user-guides/create-a-custom-post-type/" target="_blank">',
                '</a>'),
        sprintf(__("Functionality enhanced using %sWordPress Custom Taxonomy%s",
                        'wpcf'),
                '<a href="http://wp-types.com/documentation/user-guides/create-custom-taxonomies/" target="_blank">',
                '</a>'),
    );
}

/**
 * Renders credits in footer. 
 */
function wpcf_footer_credit_render() {
    $option = get_option('wpcf_footer_credit', array('active' => 1));
    // Set message
    $data = wpcf_footer_credit_defaults();
    if (isset($option['message']) && isset($data[$option['message']])) {
        $message = $data[$option['message']];
    } else {
        $message = $data[0];
    }
    $template = get_template();
    if ($template == 'canvas') {
        echo '<p style="margin-bottom:10px;">' . $message . '</p>';
    } else if ($template == 'genesis') {
        echo '<div id="types-credits" class="creds"><p>' . $message . '</p></div>';
    } else if ($template == 'thesis_18') {
        echo '<p>' . $message . '</p>';
    } else if ($template == 'headway') {
        echo '<p style="float:none;" class="footer-left footer-headway-link footer-link">' . $message . '</p>';
    } else if ($template == 'twentyeleven') {
        echo $message . '<br />';
    } else if ($template == 'twentyten') {
        echo str_replace('<a ', '<a style="background:none;" ', $message) . '<br />';
    } else {
        echo '<div id="types-credits" style="margin: 10px 0 10px 0;width:95%;text-align:center;font-size:0.9em;">' . $message . '</div>';
    }
}