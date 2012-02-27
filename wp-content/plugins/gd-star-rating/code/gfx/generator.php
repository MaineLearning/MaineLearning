<?php

class GDSRGenerator {
    // stars
    function get_image_name($set, $size, $stars, $value) {
        if ($value > $stars) $value = $stars;
        $value = @number_format($value, 1);
        if ($stars < 10) $stars = "0".intval($stars);
        if (intval($value) == 0) $value = "000";
        if ($value < 10 && substr($value, 0, 1) != 0) $value = "0".$value;
        $name = "gdsr_".$stars."_".$size;
        $name.= "_".$set."_vl".str_replace(".", "", $value);
        return $name;
    }

    function generate_png($file_path, $size, $stars, $value, $output = '') {
        $image_set = imagecreatefrompng($file_path);

        $star_empty = imagecreatetruecolor($size, $size);
        imagesavealpha($star_empty, true);
        $star_empty_transparent = imagecolorallocatealpha($star_empty, 0, 0, 0, 127);
        imagefill($star_empty, 0, 0, $star_empty_transparent);
        $star_filled = imagecreatetruecolor($size, $size);
        imagesavealpha($star_filled, true);
        $star_filled_transparent = imagecolorallocatealpha($star_filled, 0, 0, 0, 127);
        imagefill($star_filled, 0, 0, $star_filled_transparent);

        imagecopy($star_empty, $image_set, 0, 0, 0, 0, $size, $size);
        imagecopy($star_filled, $image_set, 0, 0, 0, $size * 2, $size, $size);

        $image = imageCreateTrueColor($stars * $size, $size);
        imagesavealpha($image, true);
        $image_transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
        imagefill($image, 0, 0, $image_transparent);

        imageSetTile($image, $star_empty);
        imagefilledrectangle($image, 0, 0, $stars * $size, $size, IMG_COLOR_TILED);
        imageSetTile($image, $star_filled);
        imagefilledrectangle($image, 0, 0, $value * $size, $size, IMG_COLOR_TILED);

        if ($output == '') {
            Header("Content-type: image/png");
            imagepng($image);
        } else imagepng($image, $output);

        imagedestroy($image);
        imagedestroy($image_set);
        imagedestroy($star_empty);
        imagedestroy($star_filled);
    }

    function generate_gif($file_path, $size, $stars, $value, $output = '') {
        $image_set = imagecreatefromgif($file_path);

        $star_empty = imagecreate($size, $size);
        $star_filled = imagecreate($size, $size);
        $image = imagecreate($stars * $size, $size);

        $image_set_transparent = imagecolortransparent($image_set);
        if ($image_set_transparent > 0) {
            $trnprt_color = imagecolorsforindex($image_set, $image_set_transparent);

            $star_empty_transparent = imagecolorallocate($star_empty, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
            imagefill($star_empty, 0, 0,$star_empty_transparent);
            imagecolortransparent($star_empty, $star_empty_transparent);

            $star_filled_transparent = imagecolorallocate($star_filled, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
            imagefill($star_filled, 0, 0, $star_filled_transparent);
            imagecolortransparent($star_filled, $star_filled_transparent);

            $image_transparent = imagecolorallocate($image, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
            imagefill($image, 0, 0, $image_transparent);
            imagecolortransparent($image, $image_transparent);
        }

        imagecopy($star_empty, $image_set, 0, 0, 0, 0, $size, $size);
        imagecopy($star_filled, $image_set, 0, 0, 0, $size * 2, $size, $size);

        imageSetTile($image, $star_empty);
        imagefilledrectangle($image, 0, 0, $stars * $size, $size, IMG_COLOR_TILED);
        imageSetTile($image, $star_filled);
        imagefilledrectangle($image, 0, 0, $value * $size, $size, IMG_COLOR_TILED);

        if ($output == '') {
            Header("Content-type: image/gif");
            imagegif($image);
        } else imagegif($image, $output);

        imagedestroy($image);
        imagedestroy($image_set);
        imagedestroy($star_empty);
        imagedestroy($star_filled);
    }

    function generate_jpg($file_path, $size, $stars, $value, $output = '') {
        $image_set = imagecreatefromjpeg($file_path);

        $star_empty = imagecreate($size, $size);
        $star_filled = imagecreate($size, $size);
        $image = imagecreate($stars * $size, $size);
        imagecopy($star_empty, $image_set, 0, 0, 0, 0, $size, $size);
        imagecopy($star_filled, $image_set, 0, 0, 0, $size * 2, $size, $size);

        imageSetTile($image, $star_empty);
        imagefilledrectangle($image, 0, 0, $stars * $size, $size, IMG_COLOR_TILED);
        imageSetTile($image, $star_filled);
        imagefilledrectangle($image, 0, 0, $value * $size, $size, IMG_COLOR_TILED);

        if ($output == '') {
            Header("Content-type: image/jpg");
            imagejpeg($image);
        } else imagejpeg($image, $output);

        imagedestroy($image);
        imagedestroy($image_set);
        imagedestroy($star_empty);
        imagedestroy($star_filled);
    }

    function generate_image($file_path, $size, $stars, $value, $output = '') {
        $extension = end(explode(".", $file_path));
        switch ($extension) {
            case "png":
                GDSRGenerator::generate_png($file_path, $size, $stars, $value, $output);
                break;
            case "gif":
                GDSRGenerator::generate_gif($file_path, $size, $stars, $value, $output);
                break;
            case "jpg":
                GDSRGenerator::generate_jpg($file_path, $size, $stars, $value, $output);
                break;
        }
    }

    function image($file_path, $size, $stars, $value, $output) {
        if (!file_exists($output)) {
            GDSRGenerator::generate_image($file_path, $size, $stars, $value, $output);
        }
        $ext = end(explode(".", $output));
        header("Content-Type: image/$ext");
        readfile($output);
    }

    function image_nocache($file_path, $size, $stars, $value) {
        GDSRGenerator::generate_image($file_path, $size, $stars, $value);
    }

    // thumbs
    function get_thumb_image_name($set, $size, $value) {
        $value = intval($value);
        $name = "gdsr_thumb_".$size."_".$set."_";
        $name.= $value >= 0 ? "plus" : "minus";
        return $name;
    }

    function generate_thumb_png($file_path, $size, $value, $output = '') {
        $value = intval($value);

        $image_set = imagecreatefrompng($file_path);
        $thumb = imagecreatetruecolor($size, $size);
        imagesavealpha($thumb, true);
        $transparent = imagecolorallocatealpha($thumb, 0, 0, 0, 127);
        imagefill($thumb, 0, 0, $transparent);
        $pos = $value > 0 ? $size * 2 : $size * 3;
        imagecopy($thumb, $image_set, 0, 0, 0, $pos, $size, $size);

        if ($output == '') {
            Header("Content-type: image/png");
            imagepng($thumb);
        } else imagepng($thumb, $output);

        imagedestroy($thumb);
        imagedestroy($image_set);
    }

    function generate_thumb_gif($file_path, $size, $value, $output = '') {
        $image_set = imagecreatefromgif($file_path);

        $thumb = imagecreate($size, $size);
        $image_set_transparent = imagecolortransparent($image_set);
        if ($image_set_transparent > 0) {
            $trnprt_color = imagecolorsforindex($image_set, $image_set_transparent);
            $transparent = imagecolorallocate($thumb, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
            imagefill($thumb, 0, 0,$transparent);
            imagecolortransparent($thumb, $transparent);
        }
        $pos = $value > 0 ? $size * 2 : $size * 3;
        imagecopy($thumb, $image_set, 0, 0, 0, $pos, $size, $size);

        if ($output == '') {
            Header("Content-type: image/gif");
            imagegif($thumb);
        } else imagegif($thumb, $output);

        imagedestroy($thumb);
        imagedestroy($image_set);
    }

    function generate_thumb_jpg($file_path, $size, $stars, $value, $output = '') {
        $image_set = imagecreatefromjpeg($file_path);

        $thumb = imagecreate($size, $size);
        $pos = $value > 0 ? $size * 2 : $size * 3;
        imagecopy($thumb, $image_set, 0, 0, 0, $pos, $size, $size);

        if ($output == '') {
            Header("Content-type: image/jpg");
            imagejpeg($thumb);
        } else imagejpeg($thumb, $output);

        imagedestroy($thumb);
        imagedestroy($image_set);
    }

    function generate_thumb_image($file_path, $size, $value, $output = '') {
        $extension = end(explode(".", $file_path));
        switch ($extension) {
            case "png":
                GDSRGenerator::generate_thumb_png($file_path, $size, $value, $output);
                break;
            case "gif":
                GDSRGenerator::generate_thumb_gif($file_path, $size, $value, $output);
                break;
            case "jpg":
                GDSRGenerator::generate_thumb_jpg($file_path, $size, $value, $output);
                break;
        }
    }

    function thumb_image($file_path, $size, $value, $output) {
        if (!file_exists($output)) {
            GDSRGenerator::generate_thumb_image($file_path, $size, $value, $output);
        }
        $ext = end(explode(".", $output));
        header("Content-Type: image/$ext");
        readfile($output);
    }

    function thumb_image_nocache($file_path, $size, $value) {
        GDSRGenerator::generate_thumb_image($file_path, $size, $value);
    }
}

?>