<?php

/*
Plugin Name: GD Star Rating
Plugin URI: http://www.gdstarrating.com/
Description: GD Star Rating plugin allows you to set up advanced rating and review system for posts, pages and comments in your blog using single, multi and thumbs ratings.
Version: 1.9.22
Author: Milan Petrovic
Author URI: http://www.dev4press.com/

== Copyright ==
Copyright 2008 - 2012 Milan Petrovic (email: milan@gdragon.info)

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>
*/

$gdsr_dirname_basic = dirname(__FILE__);

require_once($gdsr_dirname_basic."/config.php");

require_once($gdsr_dirname_basic."/code/defaults.php");
require_once($gdsr_dirname_basic."/gdragon/gd_functions.php");
require_once($gdsr_dirname_basic."/gdragon/gd_wordpress.php");
require_once($gdsr_dirname_basic."/code/db/main.php");
require_once($gdsr_dirname_basic."/code/cache.php");
require_once($gdsr_dirname_basic."/code/cls/results.php");
require_once($gdsr_dirname_basic."/code/cls/render.php");
require_once($gdsr_dirname_basic."/code/cls/shared.php");
require_once($gdsr_dirname_basic."/code/gfx/gfx_lib.php");
require_once($gdsr_dirname_basic."/gdt2/classes.php");
require_once($gdsr_dirname_basic."/code/t2/render.php");
require_once($gdsr_dirname_basic."/code/db/widgetizer.php");

if (STARRATING_DEBUG) {
    require_once($gdsr_dirname_basic."/gdragon/gd_debug.php");
    $gd_debug = new gdDebugGDSR(STARRATING_LOG_PATH);
}

if (!defined("WP_ADMIN") || (defined("WP_ADMIN") && !WP_ADMIN)) {
    define("GDSR_WP_ADMIN", false);
    require_once($gdsr_dirname_basic."/code/blg/db.php");
    require_once($gdsr_dirname_basic."/code/blg/frontend.php");
    require_once($gdsr_dirname_basic."/code/blg/helpers.php");

    if (!STARRATING_AJAX) {
        require_once($gdsr_dirname_basic."/gdragon/gd_google.php");
        require_once($gdsr_dirname_basic."/code/blg/query.php");
    } else {
        require_once($gdsr_dirname_basic."/code/blg/votes.php");
    }
} else {
    define("GDSR_WP_ADMIN", true);
    require_once($gdsr_dirname_basic."/code/adm/db.php");
    require_once($gdsr_dirname_basic."/code/adm/elements.php");
    require_once($gdsr_dirname_basic."/code/adm/menus.php");
}

if (!STARRATING_AJAX) {
    require_once($gdsr_dirname_basic."/code/wdg/widgets_wp27.php");
    require_once($gdsr_dirname_basic."/code/wdg/widgets_wp28.php");
}

if (!class_exists("GDStarRating")) {
    require_once($gdsr_dirname_basic."/code/class.php");

    $gdsr = new GDStarRating($gdsr_dirname_basic, __FILE__);

    include(STARRATING_PATH."code/fn/general.php");
    include(STARRATING_PATH."code/fn/data.php");
    include(STARRATING_PATH."code/fn/render.php");

    if (STARRATING_LEGACY_FUNCTIONS)
        include(STARRATING_PATH."code/fn/legacy.php");

    $gdsr_load_extra = dirname(dirname(__FILE__))."/gdsr-extra.php";
    if (file_exists($gdsr_load_extra)) require_once($gdsr_load_extra);
}

?>