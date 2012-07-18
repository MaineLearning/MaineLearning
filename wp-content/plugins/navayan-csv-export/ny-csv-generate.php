<?php

ob_clean();

function fn_ny_csv_gen($table_name){
	global $wpdb;
	$req_table = isset($_REQUEST['nycsv']) ? $_REQUEST['nycsv'] : '';
	
	if($req_table){

		$field='';
		$getfield ='';

		$result = $wpdb->get_results("SELECT * FROM $req_table");      

		$r1 = mysql_query("SELECT * FROM ".$req_table);
		$fields_num = mysql_num_fields($r1);
		
		for($i=0; $i<$fields_num; $i++){
			$field = mysql_fetch_field($r1);
			$field = (object) $field;         
			$getfield .= $field -> name.',';
		}

		$sub = substr_replace($getfield, '', -1);
		$fields = $sub; # GET FIELDS NAME
		$each_field = explode(',', $sub);
		
		$csv_file_name = $req_table.'_'.date('Ymd_His').".csv"; # CSV FILE NAME WILL BE table_name_yyyymmdd_hhmmss.csv
		
		# GET FIELDS VALUES WITH LAST COMMA EXCLUDED
		foreach($result as $row){
			for($s = 0; $s < $fields_num; $s++){
				if($s == 0) $fields .= "\n"; # FORCE NEW LINE IF LOOP COMPLETE
				$ar = str_replace(array("\n", "\n\r", "\r\n", "\r"), "\t", $row->$each_field[$s]); # REPLACE NEW LINE WITH TAB
				$ar = str_getcsv ( $ar , ",", "\"" , "\\"); # SEQUENCING DATA IN CSV FORMAT
				$fields .= $ar[0].','; # SEPARATING FIELDS WITH COMMA
			}			
			$fields = substr_replace($fields, '', -1); # REMOVE EXTRA SPACE AT STRING END
		}
		
		header("Content-type: text/x-csv"); # DECLARING FILE TYPE
		header("Content-Transfer-Encoding: binary");
		header("Content-Disposition: attachment; filename=".$csv_file_name); # EXPORT GENERATED CSV FILE
		header("Pragma: no-cache");
		header("Expires: 0");

		echo $fields;
  }
}
?>