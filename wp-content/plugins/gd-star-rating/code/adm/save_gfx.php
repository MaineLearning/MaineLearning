<?php

    if (isset($_POST['gdsr_action']) && $_POST['gdsr_action'] == 'save') {
        $ginc = array();

        $sizes = isset($_POST["gdsr_inc_size"]) ? $_POST["gdsr_inc_size"] : array();
        $new_stars = isset($_POST["gdsr_inc_star"]) ? $_POST["gdsr_inc_star"] : array();
        $new_sizes = array();
        foreach ($this->stars_sizes as $key => $size) {
            $new_sizes[$key] = in_array($key, $sizes) ? 1 : 0;
        }

        $ginc[] = $new_sizes;
        $ginc[] = $new_stars;

        $sizes = isset($_POST["gdsr_inc_size_thumb"]) ? $_POST["gdsr_inc_size_thumb"] : array();
        $new_stars = isset($_POST["gdsr_inc_thumb"]) ? $_POST["gdsr_inc_thumb"] : array();
        $new_sizes = array();
        foreach ($this->thumb_sizes as $key => $size) {
            $new_sizes[$key] = in_array($key, $sizes) ? 1 : 0;
        }

        $ginc[] = $new_sizes;
        $ginc[] = $new_stars;

        $gdsr_options["css_last_changed"] = time();
        update_option("gd-star-rating", $gdsr_options);
        update_option("gd-star-rating-inc", $ginc);
    }

?>