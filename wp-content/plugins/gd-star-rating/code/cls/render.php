<?php

class GDSRRender {
    function rating_stars($style, $unit_width, $rater_id, $class, $rating_width, $allow_vote, $unit_count, $type, $id, $user_id, $loader_id, $rater_length, $typecls, $wait_msg = '', $template_id = 0) {
        $css_style = " gdsr-".$style;
        $css_sizes = " gdsr-size-".$unit_width;
        $rater = '<div id="'.$rater_id.'" class="'.$class.$css_style.$css_sizes.'"><div class="starsbar'.$css_sizes.'">';
        $rater.= '<div class="gdouter gdheight"><div id="gdr_vote_'.$type.$id.'" style="width: '.$rating_width.'px;" class="gdinner gdheight"></div>';
        if ($allow_vote) {
            $rater.= '<div id="gdr_stars_'.$type.$id.'" class="gdsr_rating_as">';
            for ($ic = 0; $ic < $unit_count; $ic++) {
                $ncount = $unit_count - $ic;
                $url = $_SERVER['REQUEST_URI'];
                $url_pos = strpos($url, "?");
                if ($url_pos === false) $first_char = '?';
                else $first_char = '&amp;';
                $ajax_id = sprintf("gdsrX%sX%sX%sX%sX%sX%sX%sX%s", $id, $ncount, $user_id, $type, $rater_id, $loader_id, $template_id, $unit_width);
                $rater.='<a id="'.$ajax_id.'" title="'.$ncount.' / '.$unit_count.'" class="s'.$ncount.'" rel="nofollow"></a>';
            }
            $rater.= '</div>';
        }
        $rater.= '</div></div></div>';
        if ($allow_vote) $rater.= GDSRRender::rating_wait($loader_id, $rater_length."px", $typecls, $wait_msg);
        return $rater;
    }

    function rating_loader($id, $is_bot = false, $size = "small") {
        $render = '<div class="gdsrcacheloader gdsrcl'.$size.'" id="gdsrc_'.$id.'">';
        $render.= '<strong>GD Star Rating</strong><br />';
        if ($is_bot) {
            $render.= '<em>a WordPress rating system</em>';
        } else {
            $render.= '<em>'.__("loading", "gd-star-rating").'...</em>';
        }
        $render.= '</div>';
        return $render;
    }

    function render_locked_response($value = "nothing") {
        if ($value == "nothing") return "";

        $render = '<div class="gdsrcacheloader gdsrclsmall">';

        if ($value == "promourl")
            $render.= '<a rel="rating" href="http://www.gdstarrating.com/" title="GD Star Rating: a WordPress rating system"><strong>GD Star Rating</strong></a><br />';
        else if ($value == "promoname")
            $render.= '<strong>GD Star Rating</strong><br />';

        $render.= '<em>a WordPress rating system</em>';
        $render.= '</div>';
        return $render;
    }

    function rating_wait($loader_id, $rater_length, $typecls, $wait_msg = '', $style = '') {
        $rater_length = $rater_length > 0 ? " width: ".$rater_length : "";
        $loader = '<div id="'.$loader_id.'" style="display: none;'.$rater_length.' '.$style.'" class="ratingloader'.$typecls.'">';
        $loader.= $wait_msg;
        $loader.= '</div>';
        return $loader;
    }

    function render_static_thumb($style, $size, $rating, $id = "", $rendering = "") {
        if ($rendering == "") $rendering = STARRATING_STARS_GENERATOR;
        switch ($rendering) {
            case "GFX":
                return GDSRRender::render_static_thumb_gfx($style, $size, $rating);
                break;
            default:
            case "DIV":
                return GDSRRender::render_static_thumb_div($style, $size, $rating);
                break;
        }
    }

    function render_static_thumb_div($style, $size, $rating, $id = "") {
        global $gdsr;

        $gfx = $gdsr->g->find_thumb($style);
        $path = is_null($gfx) ? "" : $gfx->get_url($size);

        return sprintf('<div%s style="%s"></div>',
            $id == "" ? '' : ' id="'.$id.'"',
            sprintf('text-align:left; padding: 0; margin: 0; background: url(%s) no-repeat 0px -%spx; height: %spx; width: %spx;',
                $path, $rating < 0 ? 3 * $size : 2 * $size, $size, $size)
        );
    }

    function render_static_thumb_gfx($style, $size, $rating, $id = "") {
        $url = STARRATING_URL.sprintf("gfx.php?value=%s&amp;type=thumbs&amp;set=%s&amp;size=%s", $rating, $style, $size);
        return sprintf('<img%s src="%s" alt="%s" />', $id == "" ? '' : ' id="'.$id.'"', $url, ($rating > 0 ? "+" : "").$rating);
    }

    function render_static_stars($star_style, $star_size, $star_max, $rating, $id = "", $rendering = "", $star_factor = 1) {
        if ($rendering == "") $rendering = STARRATING_STARS_GENERATOR;
        switch ($rendering) {
            case "GFX":
                return GDSRRender::render_static_stars_gfx($star_style, $star_size, $star_max, $rating, $id, $star_factor);
                break;
            default:
            case "DIV":
                return GDSRRender::render_static_stars_div($star_style, $star_size, $star_max, $rating, $id, $star_factor);
                break;
        }
    }

    function render_static_stars_div($star_style, $star_size, $star_max, $rating, $id = "", $star_factor = 1) {
        global $gdsr;

        $gfx = $gdsr->g->find_stars($star_style);
        $star_path = is_null($gfx) ? "" : $gfx->get_url($star_size);
        $full_width = $star_size * $star_max * $star_factor;
        $rate_width = $star_size * $rating * $star_factor;
        
        return sprintf('<div%s style="%s"><div style="%s"></div></div>',
            $id == "" ? '' : ' id="'.$id.'"',
            sprintf('text-align:left; padding: 0; margin: 0; background: url(%s); height: %spx; width: %spx;', $star_path, $star_size, $full_width),
            sprintf('background: url(%s) bottom left; padding: 0; margin: 0; height: %spx; width: %spx;', $star_path, $star_size, $rate_width)
        );
    }

    function render_static_stars_gfx($star_style, $star_size, $star_max, $rating, $id = "", $star_factor = 1) {
        $url = STARRATING_URL.sprintf("gfx.php?value=%s&amp;set=%s&amp;size=%s&amp;max=%s", $rating * $star_factor, $star_style, $star_size, $star_max * $star_factor);
        return sprintf('<img%s src="%s" alt="%s/%s" />', $id == "" ? '' : ' id="'.$id.'"', $url, $rating, $star_max);
    }

    function rating_stars_multi($style, $post_id, $tpl_id, $set_id, $id, $height, $unit_count, $allow_vote = true, $value = 0, $xtra_cls = '', $review_mode = false) {
        $css_style = " gdsr-".$style;
        $css_sizes = " gdsr-size-".$height;
        $vote_class = "gdsr_multis_as";
        $vote_current = "gdsr_mur_stars_current_";
        if ($review_mode) {
            $current = $value;
            $vote_class = "gdsr_mur_static";
            $vote_current = "gdsr_murvw_stars_current_";
        } else $current = 0;

        $rater = '<div id="gdsr_mur_stars_'.$post_id.'_'.$set_id.'_'.$id.'" class="ratemulti'.$css_style.$css_sizes.'"><div class="starsbar'.$css_sizes.'">';
        $rater.= '<div class="gdouter gdheight" style="width: '.($unit_count * $height).'px">';
        $rater.= '<div id="gdsr_mur_stars_rated_'.$post_id.'_'.$set_id.'_'.$id.'" style="width: '.($value * $height).'px;" class="gdinner gdheight"></div>';
        if ($allow_vote) {
            $rater.= '<div id="'.$vote_current.$post_id.'_'.$set_id.'_'.$id.'" style="width: '.($current * $height).'px;" class="gdcurrent gdheight"></div>';
            $rater.= '<div id="gdr_stars_mur_rating_'.$post_id.'_'.$set_id.'_'.$id.'" class="'.$vote_class.'">';
            for ($ic = 0; $ic < $unit_count; $ic++) {
                $ncount = $unit_count - $ic;
                $rater.='<a id="gdsrX'.$post_id.'X'.$set_id.'X'.$id.'X'.$ncount.'X'.$height.'X'.$tpl_id.'" title="'.$ncount.' / '.$unit_count.'" class="s'.$ncount.' '.$xtra_cls.'" rel="nofollow"></a>';
            }
            $rater.= '</div>';
        }
        $rater.= '</div></div></div>';
        return $rater;
    }

    function rating_stars_local($style, $height, $unit_count, $allow_vote = true, $value = 0, $class_base = 'gdsr_cmm', $main_class = "reviewcmm") {
        $css_style = " gdsr-".$style;
        $css_sizes = " gdsr-size-".$height;
        $css_input = str_replace("_", "-", $class_base)."-cls-rt";

        $rater = '<input class="'.$css_input.'" type="hidden" id="'.$class_base.'_value" name="'.$class_base.'_value" value="0" />';
        $rater.= '<div id="'.$class_base.'_stars" class="'.$main_class.$css_style.$css_sizes.'"><div class="starsbar'.$css_sizes.'">';
        $rater.= '<div class="gdouter gdheight"><div id="'.$class_base.'_stars_rated" style="width: '.$value.'px;" class="gdinner gdheight"></div>';
        if ($allow_vote) {
            $rater.= '<div id="'.$class_base.'" class="gdsr_integration">';
            for ($ic = 0; $ic < $unit_count; $ic++) {
                $ncount = $unit_count - $ic;
                $rater.='<a id="gdsrX'.$ncount.'X'.$height.'" title="'.$ncount.' / '.$unit_count.'" class="s'.$ncount.'" rel="nofollow"></a>';
            }
            $rater.= '</div>';
        }
        $rater.= '</div></div></div>';
        return $rater;
    }
}

?>