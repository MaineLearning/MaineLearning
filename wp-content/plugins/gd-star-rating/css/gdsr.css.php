<?php

$load_base = array(
    "circle" => array(18, 16), "bar" => array(216, 208), "arrows" => array(18, 16),
    "flower" => array(18, 16), "gauge" => array(134, 128), "squares" => array(43, 47),
    "fountain" => array(134, 128), "lines" => array(102, 96), "broken" => array(18, 16),
    "snake" => array(14, 12), "snakebig" => array(26, 24), "brokenbig" => array(26, 24),
    "triangles" => array(14, 12), "radar" => array(18, 16)
);

function split_by_length($string, $chunkLength = 1) {
    $result = array();
    $strLength = strlen($string);
    $x = 0;

    while($x < ($strLength / $chunkLength)){
        $result[] = substr($string, ($x * $chunkLength), $chunkLength);
        $x++;
    }
    return $result;
}

function get_class_head($head, $element) {
    $result = "";
    for ($i = 0; $i < count($head); $i++) {
        $result.= ".".$head[$i];
        if ($element != "") $result.= " ".$element;
        if ($i < count($head) - 1) $result.= ", ";
    }
    echo $result;
}

if (!isset($inclusion)) {
    $base_url_local = "../";
    $base_url_extra = "../../../gd-star-rating/";

    $t = isset($_GET["t"]) && !empty($_GET["t"]) && is_string($_GET["t"]) ? urldecode($_GET["t"]) : 0;
    $q = isset($_GET["s"]) && !empty($_GET["s"]) && is_string($_GET["s"]) ? urldecode($_GET["s"]) : "";
    $opacity = isset($_GET["o"]) && !empty($_GET["o"]) && is_string($_GET["o"]) ? urldecode($_GET["o"]) : "off";

    @ob_start("ob_gzhandler");
    header("Content-Type: text/css");

    if ($t > 0) {
        $gmt_mtime = gmdate('D, d M Y H:i:s', $t).' GMT';
        header("Cache-control: must-revalidate");
        header("Expires: ".gmdate("D, d M Y H:i:s", time() + 7*24*3600)." GMT");
        header("Last-Modified: ".$gmt_mtime);
        header('Etag: '.md5($t));

        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            $head_mod = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);

            if ($head_mod >= $t) {
                if (php_sapi_name() == 'CGI') {
                    Header("Status: 304 Not Modified");
                } else {
                    Header("HTTP/1.0 304 Not Modified");
                }
                exit;
            }
        }
    }
}

$raw = explode("#", $q);
if (!is_array($raw) || count($raw) < 3) exit;

$raw_blocks = split_by_length($raw[0], 3);
$sizes = split_by_length($raw[1], 2);
$thumb_sizes = split_by_length($raw[2], 2);
unset($raw[0]);
unset($raw[1]);
unset($raw[2]);

$thumb_sets = $thumb_folders = $loaders_folders = $sets = $blocks = $head = $folders = $loaders = array();

foreach($raw as $r) {
    $set = array();
    $type = substr($r, 0, 1);;
    $set["location"] = substr($r, 1, 1);
    $set["type"] = substr($r, 2, 1);
    switch ($set["type"]) {
        case "p":
            $set["type"] = "png";
            break;
        case "g":
            $set["type"] = "gif";
            break;
        case "j":
            $set["type"] = "jpg";
            break;
    }
    $set["folder"] = substr($r, 3);
    if ($type == "s") {
        $folders[] = substr($r, 3);
        $sets[] = $set;
    } else if ($type == "l") {
        $loaders_folders[] = substr($r, 3);
        $loaders[] = $set;
    } else {
        $thumb_folders[] = substr($r, 3);
        $thumb_sets[] = $set;
    }
}

echo "/* stars sizes: ".join(", ", $sizes) ." */\r\n";
echo "/* stars sets: ".join(", ", $folders) ." */\r\n";
echo "/* thumbs sizes: ".join(", ", $thumb_sizes) ." */\r\n";
echo "/* thumbs sets: ".join(", ", $thumb_folders) ." */\r\n";
echo "/* loaders: ".join(", ", $loaders_folders) ." */\r\n\r\n";

foreach($raw_blocks as $r) {
    $block = array();
    $block["type"] = substr($r, 0, 1);
    $block["size"] = intval(substr($r, 1));
    switch ($block["type"]) {
        case "a":
            $value = "ratepost";
            break;
        case "i":
            $value = "rcmmpost";
            break;
        case "k":
            $value = "rcmmmulti";
            break;
        case "c":
            $value = "ratecmm";
            break;
        case "r":
            $value = "reviewcmm";
            break;
        case "m":
            $value = "ratemulti";
            break;
    }
    $block["name"] = $value;
    $head[] = $value;

    $blocks[] = $block;
}

if (count($sets) > 0 && count($sizes)) {
    foreach ($sizes as $size) {
        echo '.starsbar.gdsr-size-'.$size.', .starsbar.gdsr-size-'.$size.' .gdheight, .starsbar.gdsr-size-'.$size.' a { height: '.$size.'px; }';
        echo "\r\n";
        for ($i = 1; $i <= 20; $i++) {
            echo '.starsbar.gdsr-size-'.$size.' a.s'.$i.' { width: '.($i * $size).'px; }';
            echo "\r\n";
        }
    }

    foreach ($blocks as $block) {
        $stars = $block["size"];
        foreach ($sizes as $size) {
            if ($block["name"] != "ratemulti") {
                echo ".".$block["name"].".gdsr-size-".$size.", .".$block["name"].".gdsr-size-".$size." .starsbar .gdouter { width: ".($stars * $size)."px; }\r\n";
            }
        }
    }

    foreach ($sets as $set) {
        $class = ".gdsr-".$set["folder"];
        foreach ($sizes as $size) {
            $url = ($set["location"] == 1 ? $base_url_local : $base_url_extra)."stars/".$set["folder"]."/stars".$size.".".$set["type"];
            echo $class." .starsbar.gdsr-size-".$size." .gdouter { background: url('".$url."') repeat-x 0px 0px; }\r\n";
            echo $class." .starsbar.gdsr-size-".$size." .gdinner { background: url('".$url."') repeat-x 0px -".(2 * $size)."px; }\r\n";
            echo $class." .starsbar.gdsr-size-".$size." .gdcurrent { background: url('".$url."') repeat-x 0px -".($size)."px; }\r\n";
            echo $class." .starsbar.gdsr-size-".$size." a:hover { background: url('".$url."') repeat-x 0px -".$size."px !important; }\r\n";
        }
    }
}

?>

<?php get_class_head($head, ""); ?> { position: relative; display: block; }
<?php get_class_head($head, ".starsbar .gdinner"); ?> { width: 0; }
<?php get_class_head($head, ".starsbar a:active"); ?> { text-decoration: none; border: 0 !important; }
<?php get_class_head($head, ".starsbar a:visited"); ?> { text-decoration: none; border: 0 !important; }
<?php get_class_head($head, ".starsbar a:hover"); ?> { text-decoration: none; border: 0 !important; }
<?php get_class_head($head, ".starsbar a"); ?> { position: absolute; display: block; left: 0; top: 0; text-decoration: none; border: 0 !important; cursor: pointer; background: none !important; }

<?php

if (count($thumb_sets) > 0 && count($thumb_sizes)) {
    foreach ($thumb_sizes as $size) {
        echo sprintf(".gdt-size-%s.gdthumbtext { line-height: %spx; }\r\n", $size, $size);
        echo sprintf(".gdt-size-%s.gdthumb, .gdt-size-%s.gdthumb a, .gdt-size-%s.gdthumb div { width: %spx; height: %spx; }\r\n", $size, $size, $size, $size, $size);
        echo sprintf(".gdt-size-%s.gdthumb.gddw a, .gdt-size-%s.gdthumb.gddw div { background-position: 0px -%spx !important; }\r\n", $size, $size, $size);
        echo sprintf(".gdt-size-%s.gdthumb.gdup a:hover { background-position: 0px -%spx; }\r\n", $size, 2 * $size);
        echo sprintf(".gdt-size-%s.gdthumb.gddw a:hover { background-position: 0px -%spx !important; }\r\n", $size, 3 * $size);
        foreach ($thumb_sets as $set) {
            $url = ($set["location"] == 1 ? $base_url_local : $base_url_extra)."thumbs/".$set["folder"]."/thumbs".$size.".".$set["type"];
            echo sprintf(".gdt-size-%s.gdthumb a.gdt-%s, .gdt-size-%s.gdthumb div.gdt-%s { background: url('%s') no-repeat; }\r\n", $size, $set["folder"], $size, $set["folder"], $url);
        }
    }
}

?>

.gdthumb a {
    border: 0 none !important;
    cursor: pointer;
    display: block;
    left: 0;
    position: absolute;
    text-decoration: none;
    top: 0;
}

.rtexthumb .gdthumbtext { float: none; }
.gdthumbtext { float: left; font-size: 12px; }
.gdthumb { position: relative; float: left; }
.gdthumb.gdup a { background-position:  0px 0px; }
.thumblock .ratingloader { float: left; }

.ratemulti .starsbar .gdcurrent { width: 0; top: 0; position: absolute; }
.starsbar .gdinner { padding: 0; }
.ratingblock td { vertical-align: middle; }
.raterclear { clear: both; }
.raterleft { float: left; }
.raterright { float: right; }
.voted {color: #999;}
.inactive {color: #999;}
.thanks {color: #36AA3D;}
.static {color: #5D3126;}
.rater { top: 0; }

.ratingtextmulti { float: left; }
.ratingbutton { float: right; padding: 1px 6px; }
.ratingbutton.gdinactive { border: 1px solid #9c5f5f; background-color: #e9e4d4; }
.ratingbutton.gdactive { border: 1px solid black; background-color: #f1ede5; cursor: pointer; }
.ratingbutton a { line-height: 14px; text-decoration: none !important; }
.ratingbutton.gdactive { cursor: pointer; }
.ratingbutton.gdactive a { color: #ad1b1b; cursor: pointer; }
.ratingbutton.gdinactive a { color: gray; cursor: default; }
.gdmultitable { padding: 3px; margin: 3px; border: 1px solid #999999; }
.gdtblbottom td { padding-top: 4px; }
.gdtblbottom { margin-top: 2px; background-color: #fffcf4; }
.mtrow { background-color: #fffcf4; }
.mtrow td.mtstars { text-align: right; }
.mtrow.alternate { background-color: #f7f4ea; }
.gdtblmuravg { background-color: #fffcf4; }
.gdtblmuravg td { border-top: 2px solid #dcdcdc; text-align: center; }
.gdmultitable td { vertical-align: middle; padding: 2px 4px; color: black; }
.ratingblock, .thumblock { margin-bottom: 4px; margin-top: 4px; font-size: 12px; }
.ratingstarsinline { float: left; }
.ratingtext { padding-bottom: 2px; margin-bottom: 2px; margin-top: 0px; }
.ratingmulti img { border: 0; padding: 0; margin: 0; }
.gdouter { text-align: left; }

.ratingblockarticle { font-size: 1em; }
.ratingblockcomment { font-size: 0.8em; }
.ratingloaderarticle, .ratingloadercomment { font-size: 12px; text-align: center; vertical-align: middle; }

.cmminthide { display: none; }

/* loading indicators */
.loader { margin-left: auto; margin-right: auto; text-align: left; }

<?php

foreach ($loaders as $l) {
    if (isset($load_base[$l["folder"]])) {
        $loader = $load_base[$l["folder"]];
        $url = $base_url_local."gfx/loader/".$l["folder"].".gif";
        echo sprintf(".loader.%s { background: url(%s) no-repeat left; padding-left: %spx; }\r\n", $l["folder"], $url, $loader[0]);
        echo sprintf(".loader.%s.width { width: %spx; }\r\n", $l["folder"], $loader[1]);
    }
}

?>

.loader.width { padding-left: 0px; }
.loader.arrows.thumb, .loader.flower.thumb, .loader.circle.thumb, .loader.broken.thumb,
.loader.radar.thumb, .loader.triangles.thumb, .loader.snake.thumb, .loader.snakebig.thumb,
.loader.brokenbig.thumb { background-position: center !important; padding-left: 0px !important; }

/* top rating widget */
.trw-title { text-align: center; font-size: 16px; font-family: "Century Gothic", Arial, Helvetica, sans-serif; }
.trw-rating { font-size: 44px; font-family: "Century Gothic", Arial, Helvetica, sans-serif; font-weight: bold; text-align: center; }
.trw-footer { text-align: center; font-size: 11px; font-family: "Century Gothic", Geneva, Arial, Helvetica, sans-serif; }

.gdsrcacheloader {
    border-top: 1px solid gray;
    border-bottom: 1px solid gray;
    margin: 3px;
    padding: 3px;
}

.gdsrclsmall {
    background: url(<?php echo $base_url_local;?>gfx/load_small.png) no-repeat left;
    padding-left: 28px;
    width: 120px;
}

.gdsrclbig {
    background: url(<?php echo $base_url_local;?>gfx/load_big.png) no-repeat left;
    padding-left: 46px;
    width: 160px;
}

.gdsrclsmall strong { font-size: 14px; }
.gdsrclsmall em { font-size: 11px; }
.gdsrclbig strong { font-size: 17px; }
.gdsrclbig em { font-size: 14px; }

<?php if ($opacity == "on") { ?>
.gdthumb div { opacity: 0.7; }
.ratemulti .starsbar .gdcurrent { opacity: 0.7; }
<?php } ?>
