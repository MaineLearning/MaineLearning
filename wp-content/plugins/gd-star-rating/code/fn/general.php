<?php

/**
 * Get plugin setting value by name
 *
 * @param string $name name of the setting
 * @return mixed setting value
 */
function gdsr_settings_get($name) {
    global $gdsr;
    return $gdsr->get($name);
}

/**
 * set plugin setting value by name and save all settings
 *
 * @param string $name name of the setting
 * @param string $value value for the setting
 * @param bool $save if tru settings will be saved to database
 */
function gdsr_settings_set($name, $value, $save = true) {
    global $gdsr;
    $gdsr->set($name, $value, $save);
}

/**
 * Include rendering engine for admin pages.
 */
function gdsr_include_render() {
    require_once(STARRATING_PATH."code/t2/render.php");
    require_once(STARRATING_PATH."code/fn/legacy.php");
    require_once(STARRATING_PATH."code/fn/data.php");
    require_once(STARRATING_PATH."code/fn/render.php");
}

/**
 * Get the default percentage value if the calculated is zero.
 *
 * @return int percentage value
 */
function gdsr_zero_percentage() {
    global $gdsr;
    return apply_filters("gdsr_fn_zero_percentage", $gdsr->o["no_votes_percentage"]);
}

/**
* Writes a object dump into the log file
*
* @param string $msg log entry message
* @param mixed $object object to dump
* @param string $block adds start or end dump limiters { none | start | end }
* @param string $mode file open mode
* @param bool $force force writing into debug file even if the debug directive is inactive
*/
function wp_gdsr_dump($msg, $obj, $block = "none", $mode = "a+", $force = false) {
    if (STARRATING_DEBUG_ACTIVE == 1 || $force) {
        global $gd_debug;
        if (is_object($gd_debug))
            $gd_debug->dump($msg, $obj, $block, $mode);
    }
}

/**
* Truncates log file to zero lenght deleting all data inside.
*/
function wp_gdsr_debug_clean() {
    global $gd_debug;
    if (is_object($gd_debug))
        $gd_debug->truncate();
}

/**
 * Renders small 80x15 powered by GD Star Rating button.
 *
 * @param bool $echo echo results or return it as a string
 * @return string html with rendered contents
 */
function wp_gdsr_render_powered_by($echo = true) {
    global $gdsr;

    if ($echo) echo $gdsr->powered_by();
    else return $gdsr->powered_by();
}

/**
 * Makes rating blocks readonly regardless of other settings.
 *
 * @param bool $standard standard ratings will be read only
 * @param bool $multis multi ratings will be read only
 */
function wp_gdsr_integration_readonly($standard = false, $multis = false) {
    global $gdsr;
    $gdsr->override_readonly_standard = $standard;
    $gdsr->override_readonly_multis = $multis;
}

/**
 * Renders multi rating review header elements css and javascript.
 *
 * @param bool $echo echo results or return it as a string
 * @return string html with rendered contents
 */
function wp_gdsr_multi_review_editor_header($echo = true) {
    global $gdsr;

    if ($echo) echo $gdsr->multi_rating_header();
    else return $gdsr->multi_rating_header();
}

/**
 * Gets the multi rating set.
 *
 * @param int $id set id
 * @return GDMultiSingle multi rating set
 */
function gd_get_multi_set($id = 0) {
    $set = GDSRDBMulti::get_multi_set($id);
    if (count($set) > 0) {
        $set->object = unserialize($set->object);
        $set->weight = unserialize($set->weight);
        return $set;
    } else {
        return null;
    }
}

/**
 * Get the array with objects with user votes.
 *
 * @param int $user_id ID of the user to get data for
 * @param int $limit number of votes to get
 * @param array $filter variables to determine data to be retrieved
 * @return array votes objects
 */
function gdsr_get_users_votes($user_id, $limit = 100, $filter = array()) {
    global $gdsr;
    return $gdsr->get_users_votes($user_id, $limit, $filter);
}

/**
 * Get multi set id based on global and categoires rules.
 *
 * @param int $post_id post to get review for
 * @return object multi set for the post
 */
function gdsr_get_multi_set($post_id = 0) {
    global $gdsr;
    if ($post_id == 0) {
        global $post;
        $post_id = $post->ID;
    }
    return $gdsr->get_multi_set($post_id);
}

if (!function_exists("is_msie6")) {
    /**
     * Determines if the browser accessing the page is MS Internet Explorer 6
     *
     * @return bool true if the browser is IE6
     */
    function is_msie6() {
        $agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        if (preg_match("/msie/i", $agent) && !preg_match("/opera/i", $agent)) {
            $val = explode(" ", stristr($agent, "msie"));
            $version = substr($val[1], 0, 1);
            if ($version < 7) return true;
            else return false;
        }
        return false;
    }
}

?>