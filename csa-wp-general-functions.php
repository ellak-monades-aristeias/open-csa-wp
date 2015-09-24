<?php
/*
	@! Perfect tables
	@@ Using jQuery
*/

## Clean the input from script, html, style, and almost all potenially harmful tags.
function clean_input($input) {
	$search = array(
		'@<script[^>]*?>.*?</script>@si',   /* strip out javascript */
		'@<[\/\!]*?[^<>]*?>@si',            /* strip out HTML tags */
		'@<style[^>]*?>.*?</style>@siU',    /* strip style tags properly */
		'@<![\s\S]*?--[ \t\n\r]*>@'         /* strip multi-line comments */
	);

	$output = preg_replace($search, '', $input);
	return $output;
}

/* ***********************************************************************
function that retreives the info from the records of the table <table_name> of the db
for each record it retrieves (1) the <key_field_name> and stores it as a value of each option,	
and (2) an array of <field_names> that create the value of the option
******************************************************** */

function CsaWpPluginSelectOptionsFromDB($field_names, $key_field_name, $table_name){
	global $wpdb;
	
	$options = $wpdb->get_results("SELECT * FROM ". $table_name);
	$result = "";
	foreach($options as $rownum => $row) {	  
		$result .= '<option value ="'. $row-> $key_field_name . '">';
		for($x = 0; $x < count($field_names); $x++) {
  		  $result .=  $row-> $field_names[$x];
		}       
		 $result .= '</option>';
   	 }
	    
    	return $result;
}

function CsaWpPluginRemoveSeconds($time) {
	$parts = explode (":", $time);
	return ($parts[0].":".$parts[1]);
}

?>