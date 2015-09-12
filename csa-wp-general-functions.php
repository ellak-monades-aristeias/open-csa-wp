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