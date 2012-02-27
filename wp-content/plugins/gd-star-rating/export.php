<?php

require_once("./code/cls/export.php");
require_once("./config.php");
$wpload = get_gdsr_wpload_path();
require($wpload);

global $wpdb;

if (isset($_GET["ex"])) {
    $export_type = $_GET["ex"];
    $get_data = $_GET;

    switch($export_type) {
        case "user":
            $export_name = $export_type.'_'.$_GET["de"];
            break;
        case "t2":
            $export_name = 't2';
            break;
        case "t2full":
            $export_name = 't2_full';
            break;
    }

    switch($export_type) {
        case "user":
            $values = array("us" => array("min", "nor"), "de" => array("article", "comment"));

            foreach ($get_data as $key => $value) {
                if (isset($values[$key])) {
                    if (!in_array($value, $values[$key])) {
                        die("invalid_request");
                    }
                } else if ($key != "ex") {
                    if ($value !== "on") {
                        die("invalid_request");
                    }
                }
            }

            header('Content-type: text/csv');
            header('Content-Disposition: attachment; filename="gdsr_export_'.$export_name.'.csv"');
            $sql = GDSRExport::export_users($_GET["us"], $_GET["de"], $get_data);
            $rows = $wpdb->get_results($sql, ARRAY_N);
            if (count($rows) > 0) {
                foreach ($rows as $row) {
                    echo '"'.join('", "', $row).'"';
                    echo "\r\n";
                }
            }
            break;
        case "t2":
            header('Content-type: text/plain');
            header('Content-Disposition: attachment; filename="gdsr_export_'.$export_name.'.txt"');
            $sql = GDSRExport::export_t2();
            $rows = $wpdb->get_results($sql, ARRAY_N);
            if (count($rows) > 0) {
                foreach ($rows as $row) {
                    echo $row[0]."|";
                    echo str_replace("|", "", $row[1])."|";
                    echo str_replace("|", "", $row[2])."|";
                    $r = str_replace("\r\n", "", $row[3]);
                    $r = str_replace("\n\r", "", $r);
                    $r = str_replace("\r", "", $r);
                    $r = str_replace("\n", "", $r);
                    echo $r."\r\n";
                }
            }
            break;
        case "t2full":
            header('Content-type: text/plain');
            header('Content-Disposition: attachment; filename="gdsr_export_'.$export_name.'.txt"');
            $lines = GDSRExport::export_t2_full();
            foreach ($lines as $l) echo $l;
            break;
    }
}

?>