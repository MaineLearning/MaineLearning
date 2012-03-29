<?php

/*
Name:    gdFunctions
Version: 1.6.0
Author:  Milan Petrovic
Email:   milan@gdragon.info
Website: http://www.gdragon.info/

== Copyright ==

Copyright 2008-2009 Milan Petrovic (email: milan@gdragon.info)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if (!class_exists('gdFunctionsGDSR')) {
    class gdFunctionsGDSR {
        /**
         * Gets the url to dev4press update check.
         *
         * @global string $wp_version wordpress version
         * @param array $options plugin settings
         * @param string $url website url
         * @return string url to update check
         */
        static function get_update_url($options, $url = "") {
            global $wp_version;
            $url = sprintf("http://info.dev4press.com/update/index.php?ver=%s&pdt=%s", 
                $options["version"], urlencode($options["product_id"]));
            if ($options["update_report_usage"] == 1) {
                $url.= "&blg=".urlencode($url)."&&wpv=".urlencode($wp_version);
            }
            return $url;
        }

        /**
         * Get the function call backtrace.
         *
         * @return array all functions calls leading to the place of call to this one
         */
        static function get_caller_backtrace() {
            if (!is_callable('debug_backtrace')) return array();
            $bt = debug_backtrace();
            $caller = array();

            $bt = array_reverse($bt);
            foreach ((array)$bt as $call) {
                $function = $call['function'];
                if (isset($call['class'])) $function = $call['class']."->$function";
                $caller[] = $function;
            }

            unset($caller[count($caller) - 1]);
            return $caller;
        }

        /**
         * Trims the text to given number of words.
         *
         * @param string $text text to trim
         * @param int $words_count words to trim to
         * @return string trimmed text
         */
        static function trim_to_words($text, $words_count = 10) {
            if ($words_count > 0) {
                $words = explode(' ', $text, $words_count + 1);
                if (count($words) > $words_count) {
                    $words = array_slice($words, 0, $words_count);
                    $text = implode(' ', $words)."...";
                }
            }
            return $text;
        }

        /**
         * Adds zeroes to set length.
         *
         * @param string $text original number
         * @param int $len max number of zeroes
         * @return string prefilled text
         */
        static function prefill_zeros($text, $len) {
            $count = strlen($text);
            $zeros = "";
            for ($i = 0; $i < $len - $count; $i++) $zeros.= "0";
            return $zeros.$text;
        }

        /**
         * Splits string into array on size.
         *
         * @param string $string string to split
         * @param int $chunk lenght of each element
         * @return array split string
         */
        static function split_by_length($string, $chunk = 1) {
            $result = array();
            $strlnght = strlen($string);
            $x = 0;

            while($x < ($strlnght / $chunk)){
                $result[] = substr($string, ($x * $chunk), $chunk);
                $x++;
            }
            return $result;
        }

        /**
         * Finds image url from text.
         *
         * @param string $text text to search
         * @return string image url
         */
        static function get_image_from_text($text) {
            $imageurl = "";
            preg_match('/<\s*img [^\>]*src\s*=\s*[\""\']?([^\""\'>]*)/i', $text, $matches);
            $imageurl = $matches[1];
            return $imageurl;
        }

        /**
         * Counts files in a folder.
         *
         * @param string $path folder path
         * @return int number of files in the folder
         */
        static function get_folder_files_count($path) {
            if (!file_exists($path))
                return 0;
            if (is_file($path))
                return filesize($path);
            $ret = 0;
            foreach(glob($path."/*") as $fn)
                $ret++;

            return $ret;
        }

        /**
         * Gets folder size.
         *
         * @param string $path folder path
         * @return int folder size
         */
        static function get_folder_size($path) {
            if (!file_exists($path))
                return 0;
            if (is_file($path))
                return filesize($path);
            $ret = 0;
            foreach(glob($path."/*") as $fn)
                $ret += gdFunctionsGDSR::get_folder_size($fn);

            return $ret;
        }

        /**
         * Scans the folder and returns all the files and folder in it.
         *
         * @param string $path path of the folder to scan
         * @return array list of files and folders in the folder
         */
        static function scan_dir($path) {
            if (function_exists("scandir")) {
                return scandir($path);
            } else {
                $dh = opendir($path);
                $files = array();
                while (false !== ($filename = readdir($dh))) {
                    $files[] = $filename;
                }
                closedir($dh);
                return $files;
            }
        }

        /**
         * Gets the folder permissions as a string.
         *
         * @param string $path path to file or folder
         * @return string file permissions
         */
        static function file_permission($path) {
            return substr(sprintf('%o', fileperms($path)), -4);
        }

        /**
         * Scans folder and adds all folders to array.
         *
         * @param string $path folder path
         * @return array founded folders in array
         */
        static function get_folders($path) {
            $folders = gdFunctionsGDSR::scan_dir($path);
            $import = array();
            foreach ($folders as $folder) {
                if (substr($folder, 0, 1) != ".") {
                    if (is_dir($path.$folder."/"))
                        $import[] = $folder;
                }
            }
            return $import;
        }

        /**
         * Adds all new settings array elements and remove obsolete ones.
         *
         * @param array $old old settings
         * @param array $new new settings
         * @return array upgraded array
         */
        static function upgrade_settings($old, $new) {
            foreach ($new as $key => $value) {
                if (!isset($old[$key])) $old[$key] = $value;
            }

            $unset = Array();
            foreach ($old as $key => $value) {
                if (!isset($new[$key])) $unset[] = $key;
            }

            foreach ($unset as $key) {
                unset($old[$key]);
            }

            return $old;
        }

        /**
         * Adds missing default parameters into parameters array.
         *
         * @param array $defaults default parameters
         * @param array $attributes input parameters
         * @return array result
         */
        static function prefill_attributes($defaults, $attributes) {
            $attributes = (array)$attributes;
            $result = array();
            foreach($defaults as $name => $default) {
                if (array_key_exists($name, $attributes)) $result[$name] = $attributes[$name];
                else $result[$name] = $default;
            }
            return $result;
        }

        /**
         * Formats byte based size into readable string
         *
         * @param int $size size in bytes
         * @return string formated string
         */
        static function size_format($size) {
            if (strlen($size) <= 9 && strlen($size) >= 7) {
                $size = number_format($size / 1048576,1);
                return "$size MB";
            } else if (strlen($size) >= 10) {
                $size = number_format($size / 1073741824,1);
                return "$size GB";
            } else if (strlen($size) <= 6 && strlen($size) >= 4) {
                $size = number_format($size / 1024,1);
                return "$size KB";
            } else return "$size B";
        }

        /**
         * Recalcuates size from weight based string.
         *
         * @param string $size input string with k/m/g/t ending
         * @return int resulting size
         */
        static function recalculate_size($size) {
            switch (strtolower(substr($size, -1))) {
                case "k":
                    return $size * 1024;
                    break;
                case "m":
                    return $size * 1024 * 1024;
                    break;
                case "g":
                    return $size * 1024 * 1024 * 1024;
                    break;
                case "t":
                    return $size * 1024 * 1024 * 1024 * 1024;
                    break;
            }
            return $size;
        }

        /**
         * Draw a pager for wordpress query loop.
         *
         * @global object $wp_query wordpress query object
         * @param string $query page element for url query
         * @param string $sign what sign to add before page part
         * @param bool $div draw div around the pager
         */
        static function loop_pager($query = "pg", $sign = "?", $div = true) {
            global $wp_query;
            $numposts = $wp_query->found_posts;
            $max_page = $wp_query->max_num_pages;
            if ($max_page > 1) {
                $page = intval(get_query_var('paged'));
                if ($page == 0) $page++;
                $url = remove_query_arg($query);
                if ($div) echo '<div class="gdpager">';
                echo gdFunctionsGDPC::draw_pager($max_page, $page, $url, $query, $sign);
                if ($div) echo '</div>';
            }
        }

        /**
         * Creates a html with pager based on number of pages and position
         *
         * @param int $total_pages total pages
         * @param int $current_page current page in pager
         * @param string $url base url
         * @param string $query page element for url query
         * @param string $sign what sign to add before page part
         * @return string html for pager
         */
        static function draw_pager($total_pages, $current_page, $url, $query = "page", $sign = "&") {
            $pages = array();
            $break_first = -1;
            $break_last = -1;
            if ($total_pages < 10) for ($i = 0; $i < $total_pages; $i++) $pages[] = $i + 1;
            else {

                $island_start = $current_page - 1;
                $island_end = $current_page + 1;

                if ($current_page == 1) $island_end = 3;
                if ($current_page == $total_pages) $island_start = $island_start - 1;

                if ($island_start > 4) {
                    for ($i = 0; $i < 3; $i++) $pages[] = $i + 1;
                    $break_first = 3;
                }
                else {
                    for ($i = 0; $i < $island_end; $i++) $pages[] = $i + 1;
                }

                if ($island_end < $total_pages - 4) {
                    for ($i = 0; $i < 3; $i++) $pages[] = $i + $total_pages - 2;
                    $break_last = $total_pages - 2;
                }
                else {
                    for ($i = 0; $i < $total_pages - $island_start + 1; $i++) $pages[] = $island_start + $i;
                }

                if ($island_start > 4 && $island_end < $total_pages - 4) {
                    for ($i = 0; $i < 3; $i++) $pages[] = $island_start + $i;
                }
            }
            sort($pages, SORT_NUMERIC);
            $render = '';
            foreach ($pages as $page) {
                if ($page == $break_last)
                    $render.= "... ";
                if ($page == $current_page)
                    $render.= sprintf('<span class="page-numbers current">%s</span>', $page);
                else
                    $render.= sprintf('<a class="page-numbers" href="%s%s%s=%s">%s</a>', $url, $sign, $query, $page, $page);
                if ($page == $break_first)
                    $render.= "... ";
            }

            if ($current_page > 1) $render.= sprintf('<a class="next page-numbers" href="%s&%s=%s">Previous</a>', $url, $query, $current_page - 1);
            if ($current_page < $total_pages) $render.= sprintf('<a class="next page-numbers" href="%s&%s=%s">Next</a>', $url, $query, $current_page + 1);

            return $render;
        }

        /**
         * Internal static function used for adding sorting element to a-href.
         *
         * @param string $column column name
         * @param string $sort_order sort order asc/desc
         * @param string $sort_column column for sorting
         * @return array array with sort elements to add to a-href tag
         */
        static function column_sort_vars($column, $sort_order, $sort_column) {
            $col["url"] = '&amp;sc='.$column;
            $col["cls"] = '';
            if ($sort_column == $column) {
                if ($sort_order == "asc") {
                    $col["url"].= '&amp;so=desc';
                    $col["cls"] = ' class="sort-order-up"';
                } else {
                    $col["url"].= '&amp;so=asc';
                    $col["cls"] = ' class="sort-order-down"';
                }
            }
            else $col["url"].= '&amp;so=asc';
            return $col;
        }

        /**
         * Checks if the php is running in safe mode.
         *
         * @return bool
         */
        static function php_in_safe_mode() {
            return (@ini_get("safe_mode") == 'On' || @ini_get("safe_mode") === 1) ? TRUE : FALSE;
        }

        /**
         * Returns mySQL version.
         *
         * @param bool $full return full version string or only main version number
         * @return string mySQL version
         */
        static function mysql_version($full = false) {
            if ($full)
                return mysql_get_server_info();
            else
                return substr(mysql_get_server_info(), 0, 1);
        }

        /**
         * Returns true/false if the mysql is older than 4.1
         *
         * @return bool mySQL older than 4.1 returns true
         */
        static function mysql_pre_4_1() {
            $mysql = str_replace(".", "", substr(mysql_get_server_info(), 0, 3));
            return $mysql < 41;
        }

        /**
         * Returns PHP version.
         *
         * @param bool $full return full version string or only main version number
         * @return string PHP version
         */
        static function php_version($full = false) {
            if ($full)
                return phpversion();
            else
                return substr(phpversion(), 0, 1);
        }

        /**
         * Adds slashes to a string if not already added.
         *
         * @param string $input Input string
         * @return string Result
         */
        static function add_slashes($input) {
            if (get_magic_quotes_gpc()) return $input;
            else return addslashes($input);
        }
    }

    if (!function_exists("is_odd")) {
        /**
         * Check if the number is odd or even.
         *
         * @param int $number number to check
         * @return bool true for odd, false for even number
         */
        function is_odd($number) {
            return $number&1;
        }
    }
}

if (!class_exists("gdSortObjectsArrayGDSR")) {
    class gdSortObjectsArrayGDSR {
        var $properties;
        var $sorted;

        function gdSortObjectsArrayGDSR($objects_array, $properties = array()) {
            if (count($properties) > 0) {
                $this->properties = $properties;
                usort($objects_array, array(&$this, 'array_compare'));
            }
            $this->sorted = $objects_array;
        }

        function array_compare($one, $two, $i = 0) {
            $column = $this->properties[$i]["property"];
            $order = $this->properties[$i]["order"];

            if ($one->$column == $two->$column) {
                if ($i < count($this->properties) - 1) {
                    $i++;
                    return $this->array_compare($one, $two, $i);
                } else return 0;
            }

            if (strtolower($order) == "asc")
                return ($one->$column < $two->$column) ? -1 : 1;
            else
                return ($one->$column < $two->$column) ? 1 : -1;
        }
    }
}

?>