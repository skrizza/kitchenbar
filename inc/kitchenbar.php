<?php
## kitchenbar.php
## functions specific to kitchenbar tables (cocktails,recipes)

function add_recipe($recipe,$ingredients,$preparation,$preptype,$prepblurb) {
	## FUNCTION get_available_id
	##   Given a table name and field name for id,
	## Input: $recipe[0-5]
	##           shortname, fullname, description
	##        $ingredients[0-6]
	##           ingr,qty,qnum,qden,uom,idesc,seq
	##        $preparation[0-2]
	##           prep,pseq,pdesc
	##        $preptype
	##        $prepblurb
	## Output: succcess/fail.

	## Verify all variables needed are there

	if ( $recipe[0] == "" ) {
		$ret="Error creating recipe, shortname not filled in.";
		return $ret;
	}


	$ingredients_empty = "TRUE";
	foreach ( $ingredients as $ingredient ) {
		#echo "skratchadmin.php.add_recipe: ingredient tested: $ingredient[0]<br />\n";
		if ($ingredient[0] == "on") {
			$ingredients_empty = "FALSE";
		}
	}

	if ($ingredients_empty == "TRUE") {
		$ret = "Error creating recipe, no ingredients found.";
		return $ret;
	}

	if ( $preptype == ""  ) {
		$ret = "Error creating recipe, preptype not filled in.";
		return $ret;
	}

	if ($preptype == "blurb" && $prepblurb == "") {
		$ret = "Error creating recipe, preptype is blurb and prepblurb is not filled in.";
		return $ret;
	}

	if ($preptype == "ordered") {
		$prep_empty == "TRUE";
		foreach ($preparation as $prep) {
			if ( $prep[0] == "on") {
				$prep_empty = "FALSE";
			}
		}

		if ($prep_empty == "TRUE") {
			$ret = "Error creating recipe, no preparation steps found.";
			return $ret;
		}
	}

	## Data is properly filled in, data entry can begin.

	echo "kitchenbar.php.add_recipe(): here's the recipe array:<br /> <pre>\n";
	print_r($recipe);
	echo "\n<br /></pre>\n" ;

	## Start Transaction

	$conn = get_connection();
	$null = start_transaction($conn);

	$recipe_id = new_recipe($recipe,$conn);
	print "<br />Recipe created with id [$recipe_id]<br />\n";

	#$num_ingredients = new_ingredients($recipe_id,$ingredients,$conn);
	#print "<br />Number of Ingredients added: [$num_ingredients]<br />\n";

	$num_steps = new_preparation($recipe_id,$preptype,$prepblurb,$preparation,$conn);
	print "<br />Preparation instructions added. Numbe of Steps: [$num_steps]<br />\n";

	$null = rollback_transaction($conn);

	mysql_close($conn);
}

function new_recipe($rid,$recipe,$connection) {
 	## FUNCTION new_recipe
 	##  take in data, add row to table
 	## Input: recipe[] array
 	## 0- type     1- name    2- fullname
 	## 4- desc
 	##
 	## Output: array:
 	##         0- id of recipe if success, blank if failure
 	##         1- error messagein case of failure, blank if success
	##  data[] array:
 	## 0- id       1- name    2- fullname
 	## 3- desc     4- type

	$msg[0] = "";
	$msg[1] = "";

 	$table_name = "kb_recipes";
 	if ($rid == "") {
 		$id = get_available_id($table_name,"ID");
 		if (!$id) {
			$msg[1] = "new_recipe(): Error getting new id for table $table_name.<br />\n";
			return $msg;
		}
 	}

 	if ($recipe[0] == "" || $recipe[1] == "") {
		$msg[1] = "new_recipe(): Error creating recipe, shortname or recipe type not filled in.<br />";
		return $msg;
	}

 	if (!$connection) $connection = get_connection();
 	if ($rid) {
 		## Delete the row first if id exists
 		if (!delete_rows($rid,$table_name,$connection)) {
 			$msg[1] = "new_recipe(): Error deleting rows, sorry<br />";
 			return $msg;
 		} else {
 			$id = $rid;
 		}
 	}

	## Put fields in proper oder for processing
	$data[0] = $id;
	$data[1] = $recipe[1];
	$data[2] = $recipe[2];
	$data[3] = $recipe[3];
	$data[4] = $recipe[0];

	$msg[1] = add_row(5,$table_name,$data,$connection);
	if ($msg[1] == "") $msg[0] = $id;
	return $msg;
 }

function new_ingredients($rid,$ingredients,$connection) {
 	## FUNCTION new_ingredients
 	##  take in data, add row(s) to table ingredients
 	## Input: recipe id, ingredient array, connection reference (optional)
 	## Output: empty string for success or error message if failure

 	## 0- id,         1- seq,         2- qty1,
 	## 3- qty1_whole  4- qty1_num     5- qty1_denom
 	## 6- qty1_uom    7- qty2         8- qty2_whole
 	## 9- qty2_num    10- qty2_denom  11- qty2_uom
 	## 12- ingredient

 	$table_name = "kb_ingredients";
 	#echo "<pre>\n";
 	#print_r($ingredients);
 	#echo "</pre>\n";

	if (!$connection) $connection = get_connection();
 	if ($rid) {
 		## Delete the rows first if id exists
 		if (!delete_rows($rid,$table_name,$connection)) {
 			$msg = "new_ingredients(): Error deleting rows, sorry<br />";
 			return $msg;
 		} else {
 			$id = $rid;
 		}
 	}

	$num_ingredients = 0;
	foreach ($ingredients as $ingredient_row) {
		$data = array_fill (0,13,'');
		if ($ingredient_row[0] == "on") {
			$data[0] = $id;
			$data[1] = $num_ingredients++;
			for ($a = 2; $a <= 13; $a++) $data[$a] = $ingredient_row[$a];
			#$msg = print_r($data);
			$msg = add_row(14,$table_name,$data,$connection);
			if ($msg != "") last;
		}
	}
 	return $msg;
 }

function new_preparation($rid,$form_data,$connection) {
 	## FUNCTION new_preparation
 	##  take in data, add row to table
 	## Input: $rid, recipe id
	##        $form_data, array:
	##        0- preptype (ordered/blurb)
	##        1,2,...- array:
	##           0- prep (on/blank)
	##           1- pseq
	##           2- pdesc
	##           3- pblurb
	##           4- pgroup
 	## Output: success/fail, return number if ingredients added

	## Database table schema:
 	## 0- ID,         1- TYPE         2- SEQ
 	## 3- SEQ_DESC    4- BLURB        5- GROUPING

 	$table_name = "kb_prep";
 	$num_steps = 0;
 	$ret = "";
 	$log = "";
 	#echo "<pre>here is the preparation array: [\n";
 	#print_r($form_data);
 	#echo "]</pre>\n";

	$log .= "new_preparation(): input fields are: [$rid],[$form_data],[$connection]";
	$log.= "form_data: 1-[" . $form_data[0] . "]. 2-[" . $form_data[1] . "]. ";
	$data = array_fill(0,6,'');
	$data[0] = $rid;
	$data[1] = $form_data[0];

	if (!$connection) $connection = get_connection();
 	if ($rid) {
 		## Delete the rows first if id exists
 		if (!delete_rows($rid,$table_name,$connection)) {
 			#print "Error deleting rows, sorry<br />";
 			$log .= "new_preparation(): Error deleting rows for id [$rid]";
 		}
 	} else {
 		## Do nothing if no $rid
 		$log .= "new_preparation(): Error adding preparation: no id provided";
 	}

	$num_steps = 0;

	if ($form_data[0] == "") {
		$ret .= "new_preparation(): form_data[0] is blank.";
	} else {
		## Remove first element of $form_data array
		array_shift($form_data);
		foreach ($form_data as $prep_row) {
			$data[2] = "";
			$data[3] = "" ;
			$data[4] = "";
			$data[5] = "";
			if ($data[1] == "blurb") {
				$num_steps++;
				$data[4] = $prep_row[3];
				$data[5] = $prep_row[4];
				$ret = add_row(6,$table_name,$data,$connection);
				if ($ret != "" ) last;
			} else {
				if ($prep_row[0] == "on") {
					$num_steps++;
					$data[2] = $prep_row[1];
					$data[3] = $prep_row[2];
					$data[5] = $prep_row[4];
					$ret = add_row(6,$table_name,$data,$connection);
					if ($ret != "" ) last;
				}
			}
		}
	}
	return $ret;
}

function w_get_recdef($us,$up,$ut,$rid) {
	## FUNCTION w_get_recdef
	##  return data from recipe table for recipe update pages
	## Input: $us,$up,$ut,$rid
	## Output: boolean FALSE if no recipe found
	##         OR array containing:
	##         [0]: html code
	##         [1]: type (recipe/cocktail)
	##         [2]: name
	##         [3]: long name
	##         [4]: description
	##

	$table_name = "kb_recipes";

	$id_field = "ID";
	$html_code = "";

	$conn = get_connection();
	$query = "SELECT * FROM $table_name where $id_field = $rid";
	$result = run_query($query,$conn);
	#print "$result\n<br />";
	$data = array_pop(dump_query_res($result));
	#print_r($data);


	## Generate HTML code now
	$rid == "go";
	#$html_code .= "<h1>Recipe Definition</h1>\n";
	if ($data != "") {
		$html_code .= "<table border=\"0\">\n" ;
		$html_code .= "\t<tr>\n\t\t<td class=\"head\">TYPE:</td>\n\t\t<td class=\"data\">" . ${data}[4] . "</td>\n\t</tr>\n";
		$html_code .= "\t<tr>\n\t\t<td class=\"head\">NAME:</td>\n\t\t<td class=\"data\">" . ${data}[1] . "</td>\n\t</tr>\n";
		$html_code .= "\t<tr>\n\t\t<td class=\"head\">FULL NAME:</td>\n\t\t<td class=\"data\">" . ${data}[2] . "</td>\n\t</tr>\n";
		$html_code .= "\t<tr>\n\t\t<td class=\"head\">DESC:</td>\n\t\t<td class=\"data\">" . nl2br(${data}[3]) . "</td>\n\t</tr>\n";
		$html_code .= "</table>\n";

		$ret_array = array(${html_code},${data}[4],${data}[1],${data}[2],${data}[3]);
	} else {
		$ret_array = (bool) "false";
	}

	return $ret_array;
}

function w_get_ingr($us,$up,$ut,$rid) {
	## FUNCTION w_get_ingr
	##  return data from ingredients table for recipe update pages
	## Input: $us,$up,$ut,$rid
	## Output: HTML code

	if ($rid == "")  return (bool) FALSE;
	$table_name = "kb_ingredients";

	$id_field = "ID";
	$html_code = "";

	$conn = get_connection();
	$query = "SELECT * FROM $table_name where $id_field = $rid";
	$result = run_query($query,$conn);
	#print "$result<br />\n";
	$data = dump_query_res($result);
	#echo "<pre>\n";

	#print_r($data);
	#echo "</pre>\n";
	if (!$data) return (bool) FALSE;
	$num_rows = count($data);

	$html_code = get_grouped_ingr($data);

	$ret_array[0] = $html_code;
	for ($a = 0; $a < $num_rows; $a++) $ret_array[$a+1] = $data[$a];

	if (!$data) {
		#echo "w_get_ingr(): query returned no rows<br />\n";
		$ret_array = (bool) FALSE;
	}

	return $ret_array;
}

function w_get_prep($us,$up,$ut,$rid) {
	## FUNCTION w_get_ingr
	##  return data from ingredients table for recipe update pages
	## Input: $us,$up,$ut,$rid
	## Output: HTML code

	if ($rid == "")  return (bool) FALSE;
	$table_name = "kb_prep";

	$id_field = "ID";
	$html_code = "";

	$conn = get_connection();
	$query = "SELECT * FROM $table_name where $id_field = $rid ORDER BY SEQ";
	$result = run_query($query,$conn);
	#print "$result\n<br />";
	$data = dump_query_res($result);
	$num_prep = count($data);
	$preptype = $data[0][1];

	# Build array for function
	$fdata[0] = $preptype;
	$a = 1;
	if (!$data) {
		$ret_array = (bool) FALSE;
	} else {
		foreach ($data as $prep) {
			$ret_array[$a] = $prep;
			array_shift($prep);
			$prep[0] = "on";
			$fdata[$a] = $prep;
			$a++;
		}

		$html_code .= get_grouped_prep($fdata);

		$ret_array[0] = $html_code;
	}
	return $ret_array;
}

function w_getform_recdef($up,$form_data) {
	## FUNCTION w_getform_recdef
	##  Take in form data and return a form or non-editable table
	## Input: $up: entry/submit
	##        form_data[0]: rectype
	##        form_data[1]: name
	##        form_data[3]: full name
	##        form_data[4]: description
	## Output: HTML code or false if problem occurred

	## Read in variables

	if ($up == "entry") {
		## Print out form for user data input

		$html_code = "<table border=\"0\">\n";

		## Variable rectype
		$html_code .= "\t<tr>\n\t\t<td>Recipe Type</td>\n";
		$html_code .= "\t\t<td>\n\t\t\t<select name=\"rectype\" class=\"textbox\">\n";
		if ($form_data[0] == "cocktail") {
			$html_code .= "\t\t\t\t<option value=\"cocktail\" selected=\"selected\">Cocktail</option>\n";
		} else {
			$html_code .= "\t\t\t\t<option value=\"cocktail\">Cocktail</option>\n";
		}
		if ($form_data[0] == "recipe") {
			$html_code .= "\t\t\t\t<option value=\"recipe\" selected=\"selected\">Recipe</option>\n";
		} else {
			$html_code .= "\t\t\t\t<option value=\"recipe\">Recipe</option>\n";
		}
		$html_code .= "\t\t\t</select>\n\t\t</td>\n\t</tr>\n";

		## Variable short name
		$html_code .= "\t<tr>\n\t\t<td>Name</td>\n";
		if ($form_data[1] != "") {
			$html_code .=  "\t\t<td><input type=\"text\" class=\"textbox\" name=\"shortname\" value=\"". $form_data[1] . "\" /></td>\n";
		} else {
			$html_code .= "\t\t<td><input type=\"text\" class=\"textbox\" name=\"shortname\" /></td>\n";
		}
		$html_code .= "\t</tr>\n";

		## Variable long name
		$html_code .= "\t<tr>\n\t\t<td>Full Name</td>\n";
		if ($form_data[2] != "") {
			$html_code .=  "\t\t<td><input type=\"text\"  class=\"textbox\" name=\"fullname\" value=\"". $form_data[2] . "\" /></td>\n";
		} else {
			$html_code .= "\t\t<td><input type=\"text\"  class=\"textbox\" name=\"fullname\" /></td>\n";
		}
		$html_code .= "\t</tr>\n";

		## Variable recipe description
		$html_code .= "\t<tr>\n\t\t<td>Description</td>\n";
		$html_code .= "\t\t<td><textarea rows=\"10\" cols=\"25\"  class=\"textbox\" name=\"desc\">";
		$html_code .= ($form_data[3] == "" ? "&nbsp;" : $form_data[3]) . "</textarea></td>\n";
		$html_code .= "\t</tr>\n</table>\n";

	} elseif ($up == "validate") {
		## Print out data for validation
		$html_code = "<table border=\"0\">\n" ;
		$html_code .= "\t<tr>\n\t\t<td class=\"head\">TYPE:</td>\n\t\t<td class=\"data\">" . $form_data[0] . "</td>\n\t</tr>\n";
		$html_code .= "\t<tr>\n\t\t<td class=\"head\">NAME:</td>\n\t\t<td class=\"data\">" . $form_data[1] . "</td>\n\t</tr>\n";
		$html_code .= "\t<tr>\n\t\t<td class=\"head\">FULL NAME:</td>\n\t\t<td class=\"data\">" . $form_data[2] . "</td>\n\t</tr>\n";
		$html_code .= "\t<tr>\n\t\t<td class=\"head\">DESC:</td>\n\t\t<td class=\"data\">" . nl2br(stripslashes($form_data[3])) . "</td>\n\t</tr>\n";
		$html_code .= "</table>\n";

		## Print out hidden form fields that can be submitted
		$html_code .= "<input type=\"hidden\" name=\"rectype\" value=\""   . $form_data[0] . "\" />\n";
		$html_code .= "<input type=\"hidden\" name=\"shortname\" value=\"" . $form_data[1] . "\" />\n";
		$html_code .= "<input type=\"hidden\" name=\"fullname\" value=\""  . $form_data[2] . "\" />\n";
		$html_code .= "<input type=\"hidden\" name=\"desc\" value=\""      . $form_data[3] . "\" />\n";
	}

	return $html_code;
}

function w_getform_ingr($up,$form_data,$ningr) {
	## FUNCTION w_getform_ingr
	##  Take in form data and return a form or non-editable table
	## Input: $up: entry/submit
	##        $ningr: number of form rows submitted
	##        form_data[x][0]:  ingr
	##        form_data[x][1]:  (empty)
	##        form_data[x][2]:  qty1
	##        form_data[x][3]:  qty1_whole
	##        form_data[x][4]:  qty1_num
	##        form_data[x][5]:  qty1_denom
	##        form_data[x][6]:  qty1_uom
	##        form_data[x][7]:  qty2
	##        form_data[x][8]:  qty2_whole
	##        form_data[x][9]:  qty2_num
	##        form_data[x][10]:  qty2_denom
	##        form_data[x][11]: qty2_uom
	##        form_data[x][12]: ingredient
	##        form_data[x][13]: grouping
	## Output: HTML code or false if problem occurred
	##
 	## database table format:
 	## 0- id,         1- seq,         2- qty1,
 	## 3- qty1_whole  4- qty1_num     5- qty1_denom
 	## 6- qty1_uom    7- qty2         8- qty2_whole
 	## 9- qty2_num    10- qty2_denom  11- qty2_uom
 	## 12- ingredient 13- grouping


	## Read in variables
	#echo "<pre>\n";
	#print_r($form_data);
	#echo "</pre>\n";

	## Make sure $ningr is a multiple of 5
	$plus = $ningr % 5;
	if ($plus > 0) $ningr += $plus;

	if ($up == "entry") {
		## Print out form for user data input

		$html_code = "<table border=\"0\">\n";

		## Variable rectype
		#$html_code .= "\t<tr>\n\t\t<td>Recipe Type</td>\n";
		#$html_code .= "\t\t<td>\n\t\t\t<select name=\"rectype\">\n";
		$varn = "qty" . $i;
		for ($i = 1; $i <= $ningr; $i++) {
			$varn = "igroup". $i;
			$html_code .= "\t<tr>\n";
			$html_code .= "\t\t<td>&nbsp;</td>\n";
			$html_code .= "\t\t<td colspan=\"7\"><input type=\"text\" name=\"${varn}\" class=\"textbox\" size=\"30\" value=\"" . $form_data[$i-1][13] . "\" />\n\t\t</td>\n";
			$html_code .= "\t</tr>\n";

			$varn = "ingr" . $i;
			$html_code .= "\t<tr>\n";
			$html_code .= "\t\t<td><input type=\"checkbox\" name=\"$varn\"";
			if ($form_data[$i-1][0] == "on" ) { $html_code .= " CHECKED"; }
			$html_code .= " /></td>\n";

			$varn = "qty1n" . $i;
			$html_code .= "\t\t<td>\n\t\t\t<input type=\"text\" name=\"${varn}\" class=\"textbox\" size=\"1\" value=\"" . $form_data[$i-1][2] . "\" /><br />\n";
			$varn = "qty2n" . $i;
			$html_code .= "\t\t\t<input type=\"text\" name=\"${varn}\" class=\"textbox\" size=\"1\" value=\"" . $form_data[$i-1][7] . "\" />\n\t\t</td>\n";

			$varn = "whole1n" . $i;
			$html_code .= "\t\t<td>\n\t\t\t<input type=\"text\" name=\"${varn}\" class=\"textbox\" size=\"1\" value=\"" . $form_data[$i-1][3] . "\" /><br />\n";
			$varn = "whole2n" . $i;
			$html_code .= "\t\t\t<input type=\"text\" name=\"${varn}\" class=\"textbox\" size=\"1\" value=\"" . $form_data[$i-1][8] . "\" />\n\t\t</td>\n";

			$varn = "qnum1n". $i;
			$html_code .= "\t\t<td>\n\t\t\t<input type=\"text\" name=\"${varn}\" class=\"textbox\" size=\"1\" value=\"" . $form_data[$i-1][4] . "\" /><br />\n";
			$varn = "qnum2n". $i;
			$html_code .= "\t\t\t<input type=\"text\" name=\"${varn}\" class=\"textbox\" size=\"1\" value=\"" . $form_data[$i-1][9] . "\" />\n\t\t</td>\n";

			$html_code .= "\t\t<td>/<br />/</td>\n";

			$varn= "qdenom1n" . $i;
			$html_code .= "\t\t<td>\n\t\t\t<input type=\"text\" name=\"${varn}\" class=\"textbox\" size=\"1\" value=\"" . $form_data[$i-1][5] . "\" /><br />\n";
			$varn= "qdenom2n" . $i;
			$html_code .= "\t\t\t<input type=\"text\" name=\"${varn}\" class=\"textbox\" size=\"1\" value=\"" . $form_data[$i-1][10] . "\" />\n\t\t</td>\n";


			$varn = "uom1n" . $i;
			$html_code .= "\t\t<td>\n\t\t\t<input type=\"text\" name=\"${varn}\" class=\"textbox\" size=\"6\" value=\"" . $form_data[$i-1][6] . "\" /><br />\n";
			$varn = "uom2n" . $i;
			$html_code .= "\t\t\t<input type=\"text\" name=\"${varn}\" class=\"textbox\" size=\"6\" value=\"" . $form_data[$i-1][11] . "\" />\n\t\t</td>\n";

			$varn = "idesc" . $i;
			$html_code .= "\t\t<td><input type=\"text\" name=\"${varn}\" class=\"textbox\" size=\"12\" value=\"" . $form_data[$i-1][12] . "\" /></td>\n";

			$html_code .= "\t</tr>\n";
			$html_code .= "\t<tr><td colspan=\"8\"><hr /></td></tr>\n";
		}

		$html_code .= "</table>\n";

	} elseif ($up == "validate") {
		# Remove blanks from array
		$num_ingredients = 0;
		$row_index = 0;
		foreach ($form_data as $ingredient) {
			if ($ingredient[0] == "on") {
				for ($b = 2; $b <= 13; $b++){
					$data[$num_ingredients][$b] = $form_data[$row_index][$b];

				}
				$num_ingredients++;
			}
			$row_index++;
		}

		# Convert ingredient array to grouped HTML list
		$html_code = get_grouped_ingr($data);

		## Add hidden form fields for this. only include rows with data

		for ($a = 1; $a <= $num_ingredients; $a++) {
			$varn = "ingr" . $a;
			$html_code .= "<input type=\"hidden\" name=\"${varn}\" value=\"on\" />\n";

			$varn = "qty1n" . $a;
			$html_code .= "<input type=\"hidden\" name=\"${varn}\" value=\"" . $data[$a-1][2] . "\" />\n";
			$varn = "qty2n" . $a;
			$html_code .= "<input type=\"hidden\" name=\"${varn}\" value=\"" . $data[$a-1][7] . "\" />\n";

			$varn = "whole1n" . $a;
			$html_code .= "<input type=\"hidden\" name=\"${varn}\" value=\"" . $data[$a-1][3] . "\" />\n";
			$varn = "whole2n" . $a;
			$html_code .= "<input type=\"hidden\" name=\"${varn}\" value=\"" . $data[$a-1][8] . "\" />\n";

			$varn = "qnum1n". $a;
			$html_code .= "<input type=\"hidden\" name=\"${varn}\" value=\"" . $data[$a-1][4] . "\" />\n";
			$varn = "qnum2n". $a;
			$html_code .= "<input type=\"hidden\" name=\"${varn}\" value=\"" . $data[$a-1][9] . "\" />\n";

			$varn= "qdenom1n" . $a;
			$html_code .= "<input type=\"hidden\" name=\"${varn}\" value=\"" . $data[$a-1][5] . "\" />\n";
			$varn= "qdenom2n" . $a;
			$html_code .= "<input type=\"hidden\" name=\"${varn}\" value=\"" . $data[$a-1][10] . "\" />\n";

			$varn = "uom1n" . $a;
			$html_code .= "<input type=\"hidden\" name=\"${varn}\" value=\"" . $data[$a-1][6] . "\" />\n";
			$varn = "uom2n" . $a;
			$html_code .= "<input type=\"hidden\" name=\"${varn}\" value=\"" . $data[$a-1][11] . "\" />\n";

			$varn = "idesc" . $a;
			$html_code .= "<input type=\"hidden\" name=\"${varn}\" value=\"" . $data[$a-1][12] . "\" />\n";

			$varn = "igroup" . $a;
			$html_code .= "<input type=\"hidden\" name=\"${varn}\" value=\"" . $data[$a-1][13] . "\" />\n";
		}
		$html_code .= "<input type=\"hidden\" name=\"ningr\" value=\"" . $num_ingredients . "\" />\n";

	}

	return $html_code;
}

function w_getform_prep($up,$form_data,$nprep) {
	## FUNCTION w_getform_prep
	##  Generate web form for preparation data entry
	## Input: $up, phase
	##        $rid, recipe id
	##        $form_data, array:
	##        0- preptype (ordered/blurb)
	##        1- array
	##           0- prep (on/blank)
	##           1- pseq
	##           2- pdesc
	##           3- pblurb
	##           4- pgroup
	## Output: FALSE or HTML code

	$html_code = "";
	if ($nprep == "") {
		$nprep = 1;
	}

	if ($form_data[0] == "ordered") {
		## Make sure $ningr is a multiple of 5 for ordered lists
		$plus = $nprep % 5;
		if ($plus > 0) $nprep += $plus;
	}

	if ($up == "entry") {
		if ($form_data[0] != "ordered") {
			$html_code .= "<p>Enter preparation instructions below.</p>\n";
			for ($i = 1; $i <= $nprep; $i++) {
				$html_code .= "<hr />\n";
				$varn = "pgroup" . $i;
				$html_code .= "<input type=\"text\" size=\"35\" name=\"${varn}\" class=\"textbox\" value=\"" . $form_data[$i][4] . "\"/><br />\n";

				$varn = "pblurb" . $i;
				$html_code .= "<textarea rows=\"10\" cols=\"35\" class=\"textbox\" name=\"${varn}\">";
				$html_code .= $form_data[$i][3] . "</textarea>\n";
			}
		} else {
			$html_code .= "<table border=\"0\">\n";
			$html_code .= "\t<tr>\n";
			$html_code .= "\t\t<th>&nbsp;</th>\n";
			$html_code .= "\t\t<th>Seq</th>\n";
			$html_code .= "\t\t<th>Instructions</th>\n";
			$html_code .= "\t</tr>\n";
			$num_prep = 0;
			for ($i = 1; $i <= $nprep; $i++) {

				$varn = "pgroup" . $i;
				$html_code .= "\t<tr>\n\t\t<td>&nbsp;</td>\n";
				$html_code .= "\t\t<td colspan=\"3\"><input type=\"text\" name=\"${varn}\" class=\"textbox\" size =\"25\" value=\"" . $form_data[$i][4] . "\" /></td>\n";
				$html_code .= "\t</tr>\n";


				$html_code .= "\t<tr>\n";
				$varn = "prep" . $i;
				if ($form_data[$i][0] == "on") {
					$html_code .= "\t\t<td><input type=\"checkbox\" name=\"${varn}\" CHECKED /></td>\n";
				} else {
					$html_code .= "\t\t<td><input type=\"checkbox\" name=\"${varn}\" /></td>\n";
				}

				$varn = "pseq" . $i;
				$html_code .= "\t\t<td><input readonly type=\"text\" name=\"${varn}\" class=\"textbox\" size =\"1\" value=\"" . $i . "\" /></td>\n";

				$varn = "pdesc" . $i;
				$html_code .= "\t\t<td><input type=\"text\" name=\"${varn}\" class=\"textbox\" size =\"37\" value=\"" . $form_data[$i][2] . "\" /></td>\n";

				$html_code .= "\t</tr>\n";
				$html_code .= "\t<tr><td colspan=\"4\"><hr /></td></tr>\n";
			}
			$html_code .= "</table>\n";
		}
	} elseif ($up == "validate") {
		$html_code .= get_grouped_prep($form_data);

		## Print out form fields for form submittal
		for ($a = 1; $a <= $nprep; $a++) {
			$varn = "prep" . $a;
			$html_code .= "<input type=\"hidden\" name=\"$varn\" value=\"" . $form_data[$a][0] . "\" />\n";
			$varn = "pseq" . $a;
			$html_code .= "<input type=\"hidden\" name=\"$varn\" value=\"" . $a . "\" />\n";
			$varn = "pdesc" . $a;
			$html_code .= "<input type=\"hidden\" name=\"$varn\" value=\"" . $form_data[$a][2] . "\" />\n";
			$varn = "pblurb". $a;
			$html_code .= "<input type=\"hidden\" name=\"$varn\" value=\"" . $form_data[$a][3] . "\" />\n";
			$varn = "pgroup" . $a;
			$html_code .= "<input type=\"hidden\" name=\"$varn\" value=\"" . $form_data[$a][4] . "\" />\n";
		}
		$html_code .= "<input type=\"hidden\" name=\"preptype\" value=\"" . $form_data[0] . "\" />\n";
	}
	return $html_code;
}

function get_sorted_cocktails() {
	## FUNCTION get_sorted_cocktails
	##  return an array containing all cocktails with ids, sorted by cocktail name.
	## Input: none
	## Output: array:
	##         [x][0]: id
	##         [x][1]: cocktail name
	##

	$table_name = "kb_recipes";

	$fields = "ID,NAME";
	$order_by = "NAME";
	$query = "SELECT $fields FROM $table_name WHERE TYPE = 'cocktail' ORDER BY $order_by";

	$conn = get_connection();
	$result = run_query($query,$conn);
	$rows = dump_query_res($result);
	return $rows;
}

function get_cocktail_detail($id) {
	## FUNCTION get_cocktail_detail
	##  take in id, return details in array
	## Input: $id, recipe id
	## Output: array:
	##          0- name
	##          1- Ingredients
	##          2- Preparation
	##          3- Description
	if ($id == "") {
		return (bool) FALSE;
	}
	$conn = get_connection();

	$query = "select * from kb_recipes where ID = " . $id;
	$result = run_query($query,$conn);
	$data = dump_query_res($result);

	$ret_array[0] = $data[0][1];
	$ret_array[3] = $data[0][3];

	$query = "select * from kb_ingredients where ID = " . $id;
	$result = run_query($query,$conn);
	$ingredients = dump_query_res($result);

	$ret_array[1] = $ingredients;

	$query = "select * from kb_prep where ID = " . $id;
	$result = run_query($query,$conn);
	$preparation = dump_query_res($result);

	$ret_array[2] = $preparation;

	return $ret_array;
}

function get_grouped_ingr($ingredients) {
	## FUNCTION get_grouped_ingr
	##  Take in ingredient array, return HTML list
	## Input: $ingredient, array containing all ingredients
	##        ingredients[x][0]:  id
	##        ingredients[x][1]:  seq
	##        ingredients[x][2]:  qty1
	##        ingredients[x][3]:  qty1_whole
	##        ingredients[x][4]:  qty1_num
	##        ingredients[x][5]:  qty1_denom
	##        ingredients[x][6]:  qty1_uom
	##        ingredients[x][7]:  qty2
	##        ingredients[x][8]:  qty2_whole
	##        ingredients[x][9]:  qty2_num
	##        ingredients[x][10]: qty2_denom
	##        ingredients[x][11]: qty2_uom
	##        ingredients[x][12]: ingredient
	##        ingredients[x][13]: grouping
	## Output: ingredients in HTML format

	if (!$ingredients) return "";
	$groupings = array();
	$num_ingredients = sizeof($ingredients);
	#print_r($ingredients);
	# Get groupings
	foreach ($ingredients as $ingredient) {
		#print $ingredient[12];
		if (array_search(trim($ingredient[13]), $groupings) === FALSE) {
			$groupings[] = trim($ingredient[13]);
		}
	}
	#print_r($groupings);

	# Assign each ingredient to a group name in the array
	foreach ($groupings as $group) {
		for ($a = 0; $a < $num_ingredients; $a++) {
			if (trim($ingredients[$a][13]) == $group) {
				$ingr[$group][] = $ingredients[$a];
			}
		}
	}
	#print_r($ingr);

	#Make sure the empty group is in front
	#$blank_group = FALSE;
	#foreach ($groupings as $group_name) {
	#	$blank_group = TRUE;
	#	if ($group == "") $ingr1[$group_name] = $ingr[$group_name];
	#}
	#
	#if ($blank_group) {
	#	foreach ($groupings as $group_name) {
	#		if ($group != "") $ingr1[$group_name] = $ingr[$group_name];
	#	}
	#	$ingr = ingr1;
	#}

	# Print out data for validation
	$html_code = "";
	$is_grouped = (sizeof($groupings) <= 1) && ($goupings[0] == "") ? FALSE : TRUE;

	foreach ($groupings as $group) {
		# Print group name if these are grouped
		if (($is_grouped) && ($group != "")) $html_code .= "<p><em>$group:</em></p>\n";
		$html_code .= "<ul>\n";
		$group_length = sizeof($ingr[$group]);
		for ($a = 0; $a < $group_length; $a++) {
			$qty_primary = "";
			$qty_alt = "";
			$uom_primary = "";
			$uom_alt = "";
			$qty1 =   $ingr[$group][$a][2];
			$whole1 = $ingr[$group][$a][3];
			$num1 =   $ingr[$group][$a][4];
			$den1 =   $ingr[$group][$a][5];
			$uom1 =   $ingr[$group][$a][6];
			$qty2 =   $ingr[$group][$a][7];
			$whole2 = $ingr[$group][$a][8];
			$num2 =   $ingr[$group][$a][9];
			$den2 =   $ingr[$group][$a][10];
			$uom2 =   $ingr[$group][$a][11];
			$ingredient =   $ingr[$group][$a][12];

			$alt_uom = FALSE;
			$html_code .= "\t<li>";

			# Determine which UOM(s) are populated
			if ($uom1 && $uom2) {
				$mode = "both";
			} elseif ($uom1 != "" && $uom2 == "") {
				$mode = "1";
			} elseif ($uom1 == "" && $uom2 != "") {
				$mode = "2";
			}

			# Build quantity
			if ($mode == "2") {
				# UOM 1 is blank, so build line with uom2
				if ($qty2 != 0 && $qty2 != "") {
					$qty_primary = $qty2;
				} else {
					if ($whole2 != 0 && $whole2 != "") $qty_primary = $whole2;
					if ($num2 && $den2) {
						$qty_primary .= $num2 . "/" . $den2;
					}
				}
			} else {
				# UOM 1 is not blank, so populate line accordingly.
				if ($qty1 != 0 && $qty1 != "") {
					$qty_primary = $qty1;
				} else {
					if ($whole1 != 0 && $whole1 != "") $qty_primary = $whole1;
					if ($num1 && $den1) {
						$qty_primary .= " " . $num1 . "/" . $den1;
					}
				}
			}

			# Build Alternate UOM line if necessary
			if ($mode == "both") {
				$alt_uom = TRUE;
				if ($qty2 != 0 && $qty2 != "") {
					$qty_alt = $qty2;
				} else {
					if ($whole2) $qty_alt = $whole2;
					if ($num2 && $den2) $qty_alt .= $num2 . "/" . $den2;
				}
			}

			# Get UOM
			$uom_primary = ($uom1 == "each") ? "" : $uom1;
			$uom_alt = ($uom2 == "each") ? "" : $uom2;
			if ($mode == "2") $uom_primary = $uom_alt;

			# Output entire ingredients line
			$html_code .= "$qty_primary $uom_primary $ingredient";
			if ($mode == "both") {
				$html_code .= "<br />($qty_alt $uom_alt)";
			}
			$html_code .= "</li>\n";
		}
		$html_code .= "</ul>\n";
	}
return $html_code;
}

function get_grouped_prep($prep_array) {
	## FUNCTION get_grouped_prep
	##  Take in preparation array, return HTML output
	## Input: $prep_array, array containing all preparation instructions
	##        prep[0]:    preptype
	##        prep[x][0]: on/off
	##        prep[x][1]: pseq
	##        prep[x][2]: pdesc
	##        prep[x][3]: pblurb
	##        prep[x][4]: pgroup
	## Output: preparation in HTML format

	$groupings = array();
	#print_r($prep_array);

	# remove first element from array (preptype)
	$preptype = array_shift($prep_array);

	# remove non-active prep lines
	if ($preptype == "ordered") {
		foreach ($prep_array as $preparation) {
			if ($preparation[0] == "on") $prep_array1[] = $preparation;
		}
	} else {
		$prep_array1 = $prep_array;
	}
	$prep_array = $prep_array1;
	$num_prep = sizeof($prep_array);

	# Get groupings
	foreach ($prep_array1 as $preparation) {
		#print $ingredient[12];
		if (array_search(trim($preparation[4]), $groupings) === FALSE) {
			$groupings[] = trim($preparation[4]);
		}
	}
	#print_r($groupings);

	# Assign each preparation line to a group name in the array
	foreach ($groupings as $group) {
		for ($a = 0; $a < $num_prep; $a++) {
			if (trim($prep_array[$a][4]) == $group) {
				$prep[$group][] = $prep_array[$a];
			}
		}
	}
	#print_r($prep);

	# Print out data for validation
	$html_code = "";
	$is_grouped = (sizeof($groupings) <= 1) && ($goupings[0] == "") ? FALSE : TRUE;

	foreach ($groupings as $group) {
		$group_length = sizeof($prep[$group]);
		# Print group name if these are grouped
		if (($is_grouped) && ($group != "")) $html_code .= "<p><em>$group:</em></p>\n";
		if ($preptype == "ordered") {
			$html_code .= "<ol>\n";
			for ($a = 0; $a < $group_length; $a++) {
				$pdesc = $prep[$group][$a][2];
				$html_code .= "\t<li>${pdesc}</li>\n";
			}
			$html_code .= "</ol>\n";
		} else {
			$html_code .= "<p>\n";
			for ($a = 0; $a < $group_length; $a++) {
				$pblurb = nl2br($prep[$group][$a][3]);
				$html_code .= "${pblurb}\n";
			}
			$html_code .= "</p>\n";
		}
	}
	return $html_code;
}

?>