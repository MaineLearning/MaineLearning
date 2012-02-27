<?php

require_once("./config.php");
$wpload = get_gdsr_wpload_path();
require($wpload);
global $gdsr;

require_once(STARRATING_PATH."/code/gfx/generator.php");

$input["value"] = $_GET["value"];
$input["type"] = isset($_GET["type"]) ? $_GET["type"] : "stars";

$allow = false;
if ($gdsr->o["gfx_prevent_leeching"] == 1 && (isset($_GET["set"]) || isset($_GET["size"]) || isset($_GET["max"]))) {
    $server_name = $_SERVER["SERVER_NAME"];
    if (substr($server_name, 0, 4) == "www.") $server_host = substr($server_name, 4);
    if (isset($_SERVER['HTTP_REFERER'])) {
        $domain = parse_url($_SERVER['HTTP_REFERER']);
        $allow = $domain['host'] == $server_name || $domain['host'] == $server_host;
    } else $allow = true;
} else $allow = true;

if ($allow && $input["type"] == "stars") {
    $input["set"] = isset($_GET["set"]) ? $_GET["set"] : $gdsr->o["rss_style"];
    $input["size"] = isset($_GET["size"]) ? $_GET["size"] : $gdsr->o["rss_size"];
    $input["stars"] = isset($_GET["max"]) ? $_GET["max"] : $gdsr->o["stars"];

    $gfx_set = $gdsr->g->find_stars($input["set"]);
    if ($gdsr->is_cached) {
        $image_name = GDSRGenerator::get_image_name($input["set"], $input["size"], $input["stars"], $input["value"]);
        $image_path = STARRATING_CACHE_PATH.$image_name.".".$gfx_set->type;
        GDSRGenerator::image($gfx_set->get_path($input["size"]), $input["size"], $input["stars"], $input["value"], $image_path);
    } else {
        GDSRGenerator::image_nocache($gfx_set->get_path($input["size"]), $input["size"], $input["stars"], $input["value"]);
    }
} else if ($allow && $input["type"] == "thumbs") {
    $input["set"] = isset($_GET["set"]) ? $_GET["set"] : $gdsr->o["thumb_rss_style"];
    $input["size"] = isset($_GET["size"]) ? $_GET["size"] : $gdsr->o["thumb_rss_size"];

    $gfx_set = $gdsr->g->find_thumb($input["set"]);
    if ($gdsr->is_cached) {
        $image_name = GDSRGenerator::get_thumb_image_name($input["set"], $input["size"], $input["value"]);
        $image_path = STARRATING_CACHE_PATH.$image_name.".".$gfx_set->type;
        GDSRGenerator::thumb_image($gfx_set->get_path($input["size"]), $input["size"], $input["value"], $image_path);
    } else {
        GDSRGenerator::thumb_image_nocache($gfx_set->get_path($input["size"]), $input["size"], $input["value"]);
    }
} else header("Location: ".get_bloginfo("wpurl"));

?>