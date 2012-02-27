<?php

/*
Name:    gdDBInstall
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

if (!class_exists('gdDBInstallGDSR')) {
    /*
     * Class for installing, droping, populating and upgrading database tables using the file format for each table.
     */
    class gdDBInstallGDSR {
        /**
         * Drops all tables according to the table names.
         *
         * @global object $wpdb Wordpress DB class
         * @global string $table_prefix Wordpress table prefix
         * @param string $path base path to folder where the install folder is located with trailing slash
         */
        function drop_tables($path) {
            global $wpdb, $table_prefix;
            $path.= "install/tables";
            $files = gdDBInstallGDSR::scan_folder($path);
            foreach ($files as $file) {
                $file_path = $path."/".$file;
                $table_name = $table_prefix.substr($file, 0, strlen($file) - 4);
                if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
                    $wpdb->query("drop table ".$table_name);
                }
            }
        }

        /**
         * Drops the table from the database.
         *
         * @global object $wpdb Wordpress DB class
         * @global string $table_prefix Wordpress table prefix
         * @param <type> $table_name table to drop without the prefix
         */
        function drop_table($table_name) {
            global $wpdb, $table_prefix;
            $wpdb->query("drop table ".$table_prefix.$table_name);
        }

        /**
         * Executes alert scripts from alert.txt file.
         *
         * @global object $wpdb Wordpress DB class
         * @global string $table_prefix Wordpress table prefix
         * @param string $path base path to folder where the install folder is located with trailing slash
         * @param string $fname file name to use
         */
        function alter_tables($path, $fname = 'alter.txt') {
            global $wpdb, $table_prefix;
            $path.= "install/mod/".$fname;
            if (file_exists($path)) {
                $alters = file($path);
                if (is_array($alters) && count($alters) > 0) {
                    foreach ($alters as $a) {
                        if (trim($a) != '') {
                            $a = sprintf($a, $table_prefix);
                            $wpdb->query($a);
                        }
                    }
                }
            }
        }

        /**
         * Executes alert index scripts from idx.txt file.
         *
         * @global object $wpdb Wordpress DB class
         * @global string $table_prefix Wordpress table prefix
         * @param string $path base path to folder where the install folder is located with trailing slash
         * @param string $fname file name to use
         */
        function alter_index($path, $fname = 'idx.txt') {
            global $wpdb, $table_prefix;
            $path.= "install/mod/".$fname;
            if (file_exists($path)) {
                $alters = file($path);
                $table_cache = $matches = array();
                if (is_array($alters) && count($alters) > 0) {
                    foreach ($alters as $a) {
                        if (trim($a) != '') {
                            $a = sprintf($a, $table_prefix);
                            $tbl = trim(substr($a, 12, strpos($a, " ADD") - 12));
                            if (!in_array($tbl, array_keys($table_cache))) {
                                $table_cache[$tbl] = $wpdb->get_results("SHOW INDEX FROM ".$tbl);
                            }
                            $found = preg_match("/ADD\sINDEX\s`(.+?)`\s\(`(.+?)`\)/i", $a, $matches);
                            if ($found == 1) {
                                if (gdDBInstallGDSR::check_for_index($table_cache[$tbl], $matches[2])) {
                                    $wpdb->query($a);
                                }
                            } else {
                                $found = preg_match("/ADD\sPRIMARY\sKEY\s\(`(.+?)`\)/i", $a, $matches);
                                if ($found == 1) {
                                    if (gdDBInstallGDSR::check_for_index($table_cache[$tbl], $matches[1])) {
                                        $wpdb->query($a);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        /**
         * Executes drop scrips to delete tables in drop files.
         *
         * @global object $wpdb Wordpress DB class
         * @global string $table_prefix Wordpress table prefix
         * @param string $path base path to folder where the install folder is located with trailing slash
         * @param string $fname file name to use
         */
        function delete_tables($path, $fname = 'drop_tables.txt') {
            global $wpdb, $table_prefix;
            $path.= "install/mod/".$fname;
            if (file_exists($path)) {
                $tables = file($path);
                if (is_array($tables) && count($tables) > 0) {
                    foreach ($tables as $table_name) {
                        if (trim($table_name) != '') {
                            $table_name = $table_prefix."gdsr_".$table_name;
                            if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
                                $wpdb->query("drop table ".$table_name);
                            }
                        }
                    }
                }
            }
        }

        /**
         * Executes drop scrips to delete columns in drop files.
         *
         * @global object $wpdb Wordpress DB class
         * @global string $table_prefix Wordpress table prefix
         * @param string $path base path to folder where the install folder is located with trailing slash
         * @param string $fname file name to use
         */
        function delete_columns($path, $fname = 'drop_columns.txt') {
            global $wpdb, $table_prefix;
            $path.= "install/mod/".$fname;
            if (file_exists($path)) {
                $rows = file($path);
                $table_cache = array();
                if (is_array($rows) && count($rows) > 0) {
                    foreach ($rows as $row) {
                        if (trim($row) != '') {
                            $a = explode("|", $row);
                            $table = $table_prefix."gdsr_".trim($a[0]);
                            $column = trim($a[1]);
                            if (!in_array($table, array_keys($table_cache))) {
                                $table_cache[$table] = $wpdb->get_results(sprintf("SHOW COLUMNS FROM %s", $table));
                            }
                            $tbl = $table_cache[$table];
                            if (gdDBInstallGDSR::check_column($tbl, $column)) {
                                $wpdb->query(sprintf("ALTER TABLE `%s` DROP `%s`", $table, $column));
                            }
                        }
                    }
                }
            }
        }

        /**
         * Upgrades database tables.
         *
         * @param string $path base path to folder where the install folder is located with trailing slash
         */
        function upgrade_tables($path) {
            $path.= "install/tables";
            $files = gdDBInstallGDSR::scan_folder($path);
            foreach ($files as $file) {
                gdDBInstallGDSR::upgrade_table($path, $file);
            }
        }

        /**
         *
         *
         * @param array $idx list of indexed columns
         * @param string $column column to find as index
         * @return bool true if column is not in indexes array
         */
        function check_for_index($idx, $column) {
            if (is_array($idx) && count($idx) > 0) {
                foreach ($idx as $index) {
                    if (strtolower($index->Column_name) == strtolower($column)) return false;
                }
            }
            return true;
        }

        /**
         * Checks if the column exists in the table columns.
         *
         * @param array $columns all columns in the table
         * @param string $column column name to check
         */
        function check_column($columns, $column) {
            foreach ($columns as $c) {
                if ($c->Field == $column) return true;
            }
            return false;
        }

        /**
         * Upgrades table.
         *
         * @global object $wpdb Wordpress DB class
         * @global string $table_prefix Wordpress table prefix
         * @param string $path base path to folder where the install folder is located with trailing slash
         * @param string $file table file name
         */
        function upgrade_table($path, $file) {
            global $wpdb, $table_prefix;
            $file_path = $path."/".$file;
            $table_name = $table_prefix.substr($file, 0, strlen($file) - 4);
            $columns = $wpdb->get_results(sprintf("SHOW COLUMNS FROM %s", $table_name));
            $fc = file($file_path);
            $after = '';
            foreach ($fc as $f) {
                $f = trim($f);
                if (substr($f, 0, 1) == "`") {
                    $column = substr($f, 1);
                    $column = substr($column, 0, strpos($column, "`"));
                    if (!gdDBInstallGDSR::check_column($columns, $column))
                        gdDBInstallGDSR::add_column($table_name, $f, $after);
                    $after = $column;
                }
            }
        }

        /**
         * Adds column to the database.
         *
         * @global object $wpdb Wordpress DB class
         * @param string $table table name
         * @param string $column_info column definition
         * @param string $position column name used for after element in alter table
         */
        function add_column($table, $column_info, $position = '') {
            global $wpdb;
            if (substr($column_info, -1) == ",")
                $column_info = substr($column_info, 0, strlen($column_info) - 1);
            if ($position == '') $position = "FIRST";
            else $position = "AFTER ".$position;
            $sql = sprintf("ALTER TABLE %s ADD %s %s", $table, $column_info, $position);
            $wpdb->query($sql);
        }

        /**
         * Get valid collation for the database
         *
         * @global object $wpdb Wordpress DB class
         * @return string collation string
         */
        function get_collation() {
            global $wpdb;
            $charset_collate = "";
            if ($wpdb->has_cap('collation')) {
                if (!empty($wpdb->charset)) $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
                if (!empty($wpdb->collate)) $charset_collate .= " COLLATE $wpdb->collate";
            }
            return $charset_collate;
        }

        /**
         * Upgrades collation for all plugin tables to default DB collation.
         *
         * @global object $wpdb Wordpress DB class
         * @global string $table_prefix Wordpress table prefix
         * @param string $path base path to folder where the install folder is located with trailing slash
         */
        function upgrade_collation($path) {
            global $wpdb, $table_prefix;
            $path.= "install/tables";
            $files = gdDBInstallGDSR::scan_folder($path);
            $collation = gdDBInstallGDSR::get_collation();
            foreach ($files as $file) {
                $file_path = $path."/".$file;
                $table_name = $table_prefix.substr($file, 0, strlen($file) - 4);
                if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
                    $sql = sprintf("ALTER TABLE `%s` %s", $table_name, $collation);
                    $wpdb->query($sql);
                }
            }
        }

        /**
         * Creates table based on the table install file
         *
         * @global object $wpdb Wordpress DB class
         * @global string $table_prefix Wordpress table prefix
         * @param string $path base path to folder where the install folder is located with trailing slash
         */
        function create_tables($path) {
            global $wpdb, $table_prefix;
            $path.= "install/tables";
            $files = gdDBInstallGDSR::scan_folder($path);
            $collation = gdDBInstallGDSR::get_collation();
            foreach ($files as $file) {
                $file_path = $path."/".$file;
                $table_name = $table_prefix.substr($file, 0, strlen($file) - 4);
                if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
                    $fc = file($file_path);
                    $first = true;
                    $sql = "";
                    foreach ($fc as $f) {
                        if ($first) {
                            $sql.= sprintf($f, $table_prefix);
                            $first = false;
                        } else if (strpos($f, '%COLLATE%') !== false) {
                            $sql.= str_replace("%COLLATE%", $collation, $f);
                        } else $sql.= $f;
                    }
                    $wpdb->query($sql);
                }
            }
        }

        /**
         * Imports data from file into table
         *
         * @global object $wpdb Wordpress DB class
         * @global string $table_prefix Wordpress table prefix
         * @param string $path base path to folder where the install folder is located with trailing slash
         */
        function import_data($path) {
            global $wpdb, $table_prefix;
            $path.= "install/data";
            $files = gdDBInstallGDSR::scan_folder($path);
            $wpdb->show_errors = true;
            foreach ($files as $file) {
                if (substr($file, 0, 1) != '.') {
                    $file_path = $path."/".$file;
                    $handle = @fopen($file_path, "r");
                    if ($handle) {
                         while (!feof($handle)) {
                             $line = fgets($handle);
                             $sql = sprintf($line, $table_prefix);
                             $wpdb->query($sql);
                         }
                         fclose($handle);
                    }
                }
            }
        }

        /**
         * Scans folder for files.
         *
         * @param string $path base path to folder where the install folder is located with trailing slash
         * @return array list of files and folders
         */
        function scan_folder($path) {
            $files = array();
            if (function_exists("scandir")) {
                $f = scandir($path);
                foreach ($f as $filename) {
                    if (substr($filename, 0, 1) != '.' && substr($filename, 0, 1) != '_' && is_file($path."/".$filename))
                        $files[] = $filename;
                }
            } else {
                $dh = opendir($path);
                while (false !== ($filename = readdir($dh))) {
                    if (substr($filename, 0, 1) != '.' && substr($filename, 0, 1) != '_' && is_file($path."/".$filename))
                        $files[] = $filename;
                }
            }
            return $files;
        }
    }
}

?>