<?php
##
## clsCocktail.php
##
## class to generate all data needed for a cocktail.
##

class Cocktail {
	## CLASS Cocktail
	##  Class to generate all necessay data for a cocktail, given a cocktail id.
	##  Calling function needs to include skratchadmin.php and clsFractions.php
	## Input: cocktail id
	## Output: class handle

	## Class variable declaration

	var $name;           # name in text form
	var $description;    # cocktail description in text form

	var $id;             # recipe id
	var $recdef_array;   # recipe definition in array form
	var $ingr_array;     # ingredients in array form
	var $prep_array;     # preparation instructions in array form

	var $preparation;    # HTML to display preparation
	var $ingredients;    # HTML to display ingredients

	function Cocktail($id) {
		## FUNCTION Cocktail
		##  Constructor for class Cocktail
		## Input: $id, recipe id for cocktail
		## Output: class handle.

		if ($id == ""):
			return (bool) FALSE;
		else:
			$this->id = $id;
		endif;

		$conn = get_connection();

		$query = "select * from kb_recipes where ID = " . $id;
		$result = run_query($query,$conn);
		$this->recdef_array = array_shift(dump_query_res($result));

		$this->name        = $this->recdef_array[1];
		$this->description = $this->recdef_array[3];

		$query = "select * from kb_ingredients where ID = " . $id;
		$result = run_query($query,$conn);
		$this->ingr_array = dump_query_res($result);

		$this->ingredients = $this->gen_ingredients();

		$query = "select * from kb_prep where ID = " . $id ." ORDER BY SEQ";
		$result = run_query($query,$conn);
		$this->prep_array = dump_query_res($result);

		$this->preparation = $this->gen_preparation();

		return (bool) TRUE;
	}

	function gen_ingredients() {
		## FUNCTION gen_ingredients
		##  Generates html code for ingredients using ingredients from table.
		## Input: $ingr_array, rows from ingredients table
		## Output: html code or false

		$html_code = get_grouped_ingr($this->ingr_array);
		return $html_code;
	}

	function gen_preparation() {
		## FUNCTION gen_preparation
		##  Generates html code containing preparation instructions
		## Input: none
		## Output: html code or false

		$html_code = "";
		$num_prep = count($this->prep_array);
		$preptype = $this->prep_array[0][1];

		# Build array for function


		$data[0] = $preptype;
		$a = 1;
		if (!$this->prep_array) {
			$html_code = (bool) FALSE;
		} else {
			foreach ($this->prep_array as $prep) {
				array_shift($prep);
				$prep[0] = "on";
				$data[$a++] = $prep;
			}

			$html_code .= get_grouped_prep($data);
		}
		return $html_code;
	}

	function get_recdef_form() {
		## FUNCTION get_recdef_form
		##  Generate HTML containing hidden input fields for recipe definition
		##  for submittal to update-recipe.php
		## Input: none
		## Output: HTML code containing hidden input fields for recipe definition

		$recdef_fields = "<fieldset>\n";
		$recdef_fields .= "\t<input type=\"hidden\" name=\"us\" value=\"update\" />\n";
		$recdef_fields .= "\t<input type=\"hidden\" name=\"up\" value=\"entry\" />\n";
		$recdef_fields .= "\t<input type=\"hidden\" name=\"ut\" value=\"definition\" />\n";
		$recdef_fields .= "\t<input type=\"hidden\" name=\"rid\" value=\"" . $this->id . "\" />\n";
		$recdef_fields .= "\t<input type=\"hidden\" name=\"rectype\" value=\"" . $this->recdef_array[4] . "\" />\n";
		$recdef_fields .= "\t<input type=\"hidden\" name=\"shortname\" value=\"" . $this->recdef_array[1] . "\" />\n";
		$recdef_fields .= "\t<input type=\"hidden\" name=\"fullname\" value=\"" . $this->recdef_array[2] . "\" />\n";
		$recdef_fields .= "\t<input type=\"hidden\" name=\"desc\" value=\"" . $this->recdef_array[3] . "\" />\n";
		$recdef_fields .= "</fieldset>\n";

		return $recdef_fields;
	}

	function get_ingr_form() {
		## FUNCTION get_ingr_form
		##  Generate HTML containing hidden input fields for ingredients
		##  for submittal to update-recipe.php
		## Input: none, but ingredietns array should exist:
		## 0- id,         1- seq,         2- qty1,
		## 3- qty1_whole  4- qty1_num     5- qty1_denom
		## 6- qty1_uom    7- qty2         8- qty2_whole
		## 9- qty2_num    10- qty2_denom  11- qty2_uom
		## 12- ingredient 13- grouping
		## Output: HTML code containing hidden input fields for ingredients

		$page_vars = array(ingr,filler,qty1n,whole1n,qnum1n,qdenom1n,uom1n,qty2n,whole2n,qnum2n,qdenom2n,uom2n,idesc,igroup);

		$ingr_fields = "<fieldset>\n";
		$ingr_fields .= "\t<input type=\"hidden\" name=\"us\" value=\"update\" />\n";
		$ingr_fields .= "\t<input type=\"hidden\" name=\"up\" value=\"entry\" />\n";
		$ingr_fields .= "\t<input type=\"hidden\" name=\"ut\" value=\"ingredients\" />\n";
		$ingr_fields .= "\t<input type=\"hidden\" name=\"rid\" value=\"" . $this->id . "\" />\n";

		$num_ingredients = count($this->ingr_array);

		# Turn into multiple of 5 lines
		$ningr = $num_ingredients + (5 - ($num_ingredients % 5));
		$ingr_fields .= "\t<input type=\"hidden\" name=\"ningr\" value=\"" . $ningr . "\" />\n";

		for ($a = 1; $a <= $num_ingredients; $a++) {
			$ingredients = $this->ingr_array[(int)$a-1];
			#array_shift($ingredients);
			for ($b = 0; $b <= 13; $b++) {
				$varn = $page_vars[$b];
				if ($varn == "ingr") {
					$varn .= $a;
					$ingr_fields .= "\t<input type=\"hidden\" name=\"$varn\" value=\"on\" />\n";
				} else {
					$varn .= $a;
					$ingr_fields .= "\t<input type=\"hidden\" name=\"$varn\" value=\"" . ($ingredients[(int)$b]? $ingredients[(int)$b] : "") . "\" />\n";
				}
			}
		}
		$ingr_fields .= "</fieldset>\n";

		return $ingr_fields;
	}

	function get_prep_form() {
		## FUNCTION get_prep_form
		##  Generate HTML containing hidden input fields for preparation instructions
		##  for submittal to update-recipe.php
		## Input: none
		## Output: HTML code containing hidden input fields for preparation instructions

		$prep_fields = "<fieldset>\n";
		$prep_fields .= "\t<input type=\"hidden\" name=\"us\" value=\"update\" />\n";
		$prep_fields .= "\t<input type=\"hidden\" name=\"up\" value=\"entry\" />\n";
		$prep_fields .= "\t<input type=\"hidden\" name=\"ut\" value=\"preparation\" />\n";
		$prep_fields .= "\t<input type=\"hidden\" name=\"rid\" value=\"" . $this->id . "\" />\n";
		$prep_fields .= "\t<input type=\"hidden\" name=\"preptype\" value=\"" . $this->prep_array[0][1] . "\" />\n";

		$num_prep = count($this->prep_array);
		$prep_fields .= "\t<input type=\"hidden\" name=\"nprep\" value=\"$num_prep\" />\n";

		## Put all preparation instructions to form fields
		for ($a = 1; $a <= $num_prep; $a++) {
			$varn = "prep" . $a;
			$prep_fields .= "\t<input type=\"hidden\" name=\"$varn\" value=\"on\" />\n";
			$varn = "pseq" . $a;
			$prep_fields .= "\t<input type=\"hidden\" name=\"$varn\" value=\"" . $this->prep_array[(int)$a-1][2] . "\" />\n";
			$varn = "pdesc" . $a;
			$prep_fields .= "\t<input type=\"hidden\" name=\"$varn\" value=\"" . $this->prep_array[(int)$a-1][3] . "\" />\n";
			$varn = "pblurb" . $a;
			$prep_fields .= "\t<input type=\"hidden\" name=\"$varn\" value=\"" . $this->prep_array[(int)$a-1][4] . "\" />\n";
			$varn = "pgroup" . $a;
			$prep_fields .= "\t<input type=\"hidden\" name=\"$varn\" value=\"" . $this->prep_array[(int)$a-1][5] . "\" />\n";
		}
		$prep_fields .= "</fieldset>\n";

		return $prep_fields;
	}

	function apply_ingr_mult($factor) {
		## FUNCTION apply_ingr_mult
		##  Generate HTML for new the dose of ingredients.
		## Input: $factor, number to multiply all ingredients by
		## Output: (bool) TRUE, and ingreedient values are updated.

		# Get fractions in proper format for fractions class

		$num_ingr = count($this->ingr_array);
		for ($a = 0; $a < $num_ingr; $a++) {
			$farray[$a][0] = ($this->ingr_array[$a][2] != 0 ? $this->ingr_array[$a][2] : $this->ingr_array[$a][3]);
			$farray[$a][1] = $this->ingr_array[$a][4];
			$farray[$a][2] = $this->ingr_array[$a][5];
		}

		$fractions = new Fractions($farray);
		$new_farray = $fractions->mult_by($factor);

		for ($a = 0; $a < $num_ingr; $a++) {
			$this->ingr_array[$a][3] = $new_farray[$a][0];
			$this->ingr_array[$a][4] = $new_farray[$a][1];
			$this->ingr_array[$a][5] = $new_farray[$a][2];
		}

		return $new_farray;
	}

	function get_ingr_ratio() {
		## FUNCTION get_ingr_ratio
		##  Generate HTML for ratio display of the ingredients
		## Input: none
		## Output: HTML code containing ratio display of the ingredients

		# Remove elements from list that are not taken into account for ratio

		# ...

		$num_ingr = count($this->ingr_array);
		for ($a = 0; $a < $num_ingr; $a++) {
			$farray[$a][0] = ($this->ingr_array[$a][2] != 0 ? $this->ingr_array[$a][2] : $this->ingr_array[$a][3]);
			$farray[$a][1] = $this->ingr_array[$a][4];
			$farray[$a][2] = $this->ingr_array[$a][5];
		}
		$fractions = new Fractions($farray);
		$ratios = $fractions->find_ratio($fractions->farray_current);

		$ratio_ingr = "<ul>\n";
		for ($a = 0; $a < $num_ingr; $a++) {
			$qty = $ratios[$a];
			$verbage = ($qty == 1 ? "part" : "parts");
			$ingredient = $this->ingr_array[$a][12];
			$ratio_ingr .= "\t<li>$qty $verbage $ingredient</li>\n";
		}

		# Then add section for non-ratio'ed ingredients

		$ratio_ingr .= "</ul>\n";
		return $ratio_ingr;
	}

	function get_utilbar($id,$style,$doubled) {
		## FUNCTION get_utilbar
		##  Generates HTML code for utility bar
		## Input: $id, $style, $doubled (true/false)
		## Output: HTML code for utility bar

		$text_d = (($doubled == "true" || $style == "ratio") ? "single" : "double!");
		$href_d = (($doubled == "true" || $style == "ratio") ? "doubled=false" : "doubled=true");

		$link_d = "<a href=\"cocktail?id=" . $id . "&amp;" . $href_d;
		if ($style != "ratio") $link_d .= "&amp;style=" . $style;
		$link_d .= "\">" . $text_d . "</a>";

		# Show link if current style is not ratio. No link if style is ratio
		if ($style != "ratio"):
			$link_r = "<a href=\"cocktail?id=". $id . "&amp;style=ratio\">ratio</a>";
		else:
			$link_r = "ratio";
		endif;

		$utilbar = "<p id=\"utilbar\">\n";
		$utilbar .= "\t" . $link_d . " /  metric / " . $link_r . "\n";
		$utilbar .= "</p>\n";

		return $utilbar;
	}
}
?>