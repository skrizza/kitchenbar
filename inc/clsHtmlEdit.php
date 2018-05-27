<?php
##
## clsHtmlEdit.php
##
## class to treat html code
##

function indent_text($numtabs,$text) {
	## FUNCTION indent_text
	##  Takes text as input and indents each line by the nuber of tabs specified.
	##  lines not starting with a "<" are not indented.
	## Input: $numtabs, number of tabs to indent by
	##        $text, text to be indented
	## Output: indented text
	##
	$text_array = explode("\n",$text);
	for ($a = 1; $a <= $numtabs; $a++) array_walk($text_array,'add_tab'); 
	$text = implode("\n",$text_array);
	return $text;
}

function add_tab (&$text) {
	## FUNCTION add_tab
	## Input:  $text
	## Output: text with tab added
	if (($text != "") && (preg_match("/^ *</",$text))) $text = "\t" . $text;
}

?>