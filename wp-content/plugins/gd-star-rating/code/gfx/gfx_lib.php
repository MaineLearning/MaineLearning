<?php

class GDgfxLib {
    var $thumbs = array();
    var $stars = array();
    var $trend = array();
    var $last_scan = "";

    function GDgfxLib() {
        $this->last_scan = date("r");
    }

    function add_stars($star) {
        $this->stars[] = $star;
    }

    function add_trends($trend) {
        $this->trend[] = $trend;
    }

    function get_list($s = true) {
        $results = array();
        if ($s) {
            foreach ($this->stars as $set) {
                $results[] = $set->folder;
            }
        } else {
            foreach ($this->thumbs as $set) {
                $results[] = $set->folder;
            }
        }
        return $results;
    }

    function find_id($gfx, $folder) {
        $result = 0;
        foreach ($gfx as $id => $s) {
            if ($s->folder == $folder) {
                $result = $id;
                break;
            }
        }
        if ($result == 0) wp_gdsr_dump("INVALID_SET_REQUEST", "Requested set name: ".$folder);
        return $result;
    }

    function find_gfx($gfx, $folder) {
        $result = null;
        foreach ($gfx as $s) {
            if ($s->folder == $folder) {
                $result = $s;
                break;
            }
        }
        if (is_null($result)) wp_gdsr_dump("INVALID_SET_REQUEST", "Requested set name: ".$folder);
        return $result;
    }

    function find_thumb($folder) {
        return $this->find_gfx($this->thumbs, $folder);
    }

    function find_stars($folder) {
        return $this->find_gfx($this->stars, $folder);
    }

    function find_trend($folder) {
        return $this->find_gfx($this->trend, $folder);
    }

    function find_thumb_id($folder) {
        return $this->find_id($this->thumbs, $folder);
    }

    function find_stars_id($folder) {
        return $this->find_id($this->stars, $folder);
    }

    function find_trend_id($folder) {
        return $this->find_id($this->trend, $folder);
    }

    function get_type($gfx, $folder) {
        $result = null;
        foreach ($gfx as $s) {
            if ($s->folder == $folder) {
                $result = $s->type;
                break;
            }
        }
        return $result;
    }

    function get_thumb_type($folder) {
        return $this->get_type($this->thumbs, $folder);
    }

    function get_stars_type($folder) {
        return $this->get_type($this->stars, $folder);
    }

    function get_trend_type($folder) {
        return $this->get_type($this->trend, $folder);
    }
}

class GDgfxBase {
    var $name = "";
    var $folder = "";
    var $type = "png";
    var $version = "1.0";
    var $author = "";
    var $email = "";
    var $url = "";
    var $design = "";

    var $info_file = "stars";
    var $info_folder = "stars";
    var $gfx_path = "";
    var $gfx_url = "";
    var $primary = 1;

    var $imported = false;
    var $sizes = array();

    function GDgfxBase($folder, $primary = true) {
        $this->folder = $folder;

        if ($primary) {
            $this->primary = 1;
            $this->gfx_path = STARRATING_PATH.$this->info_folder."/".$folder."/";
            $this->gfx_url = STARRATING_URL.$this->info_folder."/".$folder."/";
        } else {
            $this->primary = 0;
            $this->gfx_path = STARRATING_XTRA_PATH.$this->info_folder."/".$folder."/";
            $this->gfx_url = STARRATING_XTRA_URL.$this->info_folder."/".$folder."/";
        }

        $this->import();
    }

    function import() {
        $data = $this->load_info_file();
        if ($data != null) {
            $this->name = $data["name"];
            if (isset($data["type"])) $this->type = $data["type"];
            if (isset($data["version"])) $this->version = $data["version"];
            if (isset($data["author"])) $this->author = $data["author"];
            if (isset($data["email"])) $this->email = $data["email"];
            if (isset($data["url"])) $this->url = $data["url"];
            if (isset($data["design"])) $this->design = $data["design"];
            $this->imported = true;
            return $data;
        }
        return null;
    }

    function scan_folder() {
        $files = gdFunctionsGDSR::scan_dir($this->gfx_path);
        $this->sizes = array();
        foreach ($files as $file) {
            $fparts = explode(".", $file);
            if ($fparts[1] == $this->type) {
                $size = substr($fparts[0], -2);
                $this->sizes[] = $size;
            }
        }
    }

    function load_info_file() {
        $path = $this->gfx_path.$this->info_file.".gdsr";
        if (file_exists($path)) {
            $contents = file($path);
            $data = array();
            foreach ($contents as $line) {
                $key = trim(substr($line, 0, 8));
                $key = substr($key, 0, strlen($key) - 1);
                $value = trim(substr($line, 8));
                $data[$key] = $value;
            }
            return $data;
        }
        else 
            return null;
    }
}

class GDgfxThumb extends GDgfxBase {
    function GDgfxThumb($folder, $primary = true) {
        $this->info_file = "thumbs";
        $this->info_folder = "thumbs";
        parent::GDgfxBase($folder, $primary);
    }

    function get_url($size = '16') {
        return $this->gfx_url."thumbs".$size.".".$this->type;
    }

    function get_path($size = '16') {
        return $this->gfx_path."thumbs".$size.".".$this->type;
    }
}

class GDgfxStar extends GDgfxBase {
    function GDgfxStar($folder, $primary = true) {
        parent::GDgfxBase($folder, $primary);
    }

    function get_url($size = '30') {
        return $this->gfx_url."stars".$size.".".$this->type;
    }

    function get_path($size = '30') {
        return $this->gfx_path."stars".$size.".".$this->type;
    }
}

class GDgfxTrend extends GDgfxBase {
    var $size = 16;

    function GDgfxTrend($folder, $primary = true) {
        $this->info_file = "trend";
        $this->info_folder = "trends";
        parent::GDgfxBase($folder, $primary);
    }

    function import() {
        $data = parent::import();
        if ($data != null) {
            if (isset($data["size"])) $this->size = $data["size"];
        }
    }

    function get_url() {
        return $this->gfx_url."trend.".$this->type;
    }

    function get_path() {
        return $this->gfx_path."trend.".$this->type;
    }
}

?>