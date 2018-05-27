<?php
## clsRecipeUpdater.php
##
## class to manage recipe update page
## requires functions from kitchenbar.php, calling function must include
##
## Code: s k r a t c h
##

class RecipeUpdater {
	## CLASS RecipeUpdater
	##  class to manage recipe update page
	##

	## Class variable declaration.
	## Initialized values are the defaults.
	var $us = "new";        # Update Status (new,update)
	var $ut = "definition"; # Update Type (definition,ingredients,preparation)
	var $up = "entry";      # Update Phase (entry,validate,submittal,result)
	var $rid;               # Recipe ID (integer)
	var $rs;                # Update Result (ok,nok)
	var $errmsg;            # Error message string
	var $d;                 #
	var $submitentry;       # flag to set phases to new entry ("on" or empty)
	var $submitedit;        # flag to set phases to update entry ("on" or empty)
	var $submitcreate;      # flag to set phases to submit entry ("on" or empty)
	var $submitordered;     # flag to switch preparation to ordered list  ("on" or empty)
	var $submitblurb;       # flag to switch preparation to paragraph blurb  ("on" or empty)
	var $submitfewer;       # flag to decrease number of ingredients lines  ("on" or empty)
	var $submitextra;       # flag to increase number of ingredients lines  ("on" or empty)
	var $updaterecipe;      # flag to set phases to update recipe definition ("on" or empty)
	var $updateingr;        # flag to set phases to update ingredients ("on" or empty)
	var $updateprep;        # flag to set phases to update preparation instructions ("on" or empty)

	var $recdef_data;       # array containing data for recipe creation/edit
		# recdef_data[0] = rectype
		# recdef_data[1] = shortname
		# recdef_data[2] = fullname
		# recdef_data[3] = desc
	var $ingr_data;         # array containing data for ingredients creation/edit
		# ingr_data[i][0] = ingr ("on" or empty)
		# ingr_data[i][1] = qty1n
		# ingr_data[i][2] = whole1n
		# ingr_data[i][3] = qnum1n
		# ingr_data[i][4] = qdenom1n
		# ingr_data[i][5] = uom1n
		# ingr_data[i][6] = qty2n
		# ingr_data[i][7] = whole2n
		# ingr_data[i][8] = qnum2n
		# ingr_data[i][9] = qdenom2n
		# ingr_data[i][10] = uom2n
		# ingr_data[i][11] = idesc
		# ingr_data[i][12] = igroup
	var $prep_data;         # array containing data for preparation instructions create/edit
		# blurb style:
		#  prep_data[0] = preptype (ordered,blurb)
		#  prep_data[1] = arrray:
		#  prep_data[i][0] = prep ("on" or empty)
		#  prep_data[i][1] = pseq (integer)
		#  prep_data[i][2] = pdesc
		#  prep_data[i][3] = pblurb
		#  prep_data[i][4] = pgroup
	var $preptype;          # preparation type (blurb,ordered)
	var $ningr = 5;         # number of ingredient input lines to display on form (integer)
	var $nprep = 5;         # number of preparation input lines to display on form (integer)

	function RecipeUpdater($request_form,$post_form) {
		## FUNCTION RecipeUpdater
		##  Constructor for class Recipe Updater
		## Input: $request_form, HTML form data from POST or GET
		##        $post_form, HTML form data from POST only
		## Output: returns (bool) TRUE

		# Put phases and form button submittal to variables
		$this->us  = $request_form[us];
		$this->up  = $request_form[up];
		$this->ut  = $request_form[ut];
		$this->rid = $request_form[rid];
		$this->rs  = $request_form[rs];
		$this->errmsg = $request_form[errmsg];
		$this->submitentry = $request_form[submitentry];
		$this->submitedit = $request_form[submitedit];
		$this->submitcreate = $request_form[submitcreate];
		$this->submitordered = $request_form[submitordered];
		$this->submitblurb = $request_form[submitblurb];
		$this->submitfewer = $request_form[submitfewer];
		$this->submitextra = $request_form[submitextra];
		$this->updaterecipe = $request_form[updaterecipe];
		$this->updateingr = $request_form[updateingr];
		$this->updateprep = $request_form[updateprep];

		# Update phase if necessary
		$this->update_phase();

		# Read in form data to variables
		$this->get_formdata($request_form,$post_form);

		# Make extra updates based on form data
		$this->update_data();


		## Begin to generate data for display

		return (bool) TRUE;
	}

	function update_phase() {
		## FUNCTION update_phase
		##  The phase of the web page is updated via submit buttons.
		##  Each submit button will set a different combnation of
		##  $us, $ut, and $up.
		## Input: none
		## Output: (bool) TRUE

		## Default to 'create new recipe definition' if missing page variables

		if ($this->us == "" && $this->ut == "" && $this->up == "") {
			$this->us = "new";
			$this->up = "entry";
			$this->ut = "definition";
		}
		if ($this->submitentry != "") $this->up = "validate";
		if ($this->submitedit != "") $this->up = "entry";
		if ($this->submitcreate != "")  $this->up = "submittal";

		if ($this->updaterecipe != ""):
			# set phases to update recipe definition
			$this->us = "update";
			$this->up = "entry";
			$this->ut = "definition";
		endif;

		if ($this->updateingr != ""):
			# set phases to update ingredients
			$this->us = "update";
			$this->up = "entry";
			$this->ut = "ingredients";
		endif;

		if ($this->updateprep != ""):
			# set phases to update preparation instructions
			$this->us = "update";
			$this->up = "entry";
			$this->ut = "preparation";
		endif;

		return (bool) TRUE;
	}

	function get_formdata($request_form,$post_form) {
		## FUNCTION get_formdata
		##  function to write data from forms to class arrays
		## Input: $request_form, HTML form data from a form
		##        $post_form, HTML form data from a POSTed form

		# Only POSTed variables are accepted for submittal to update database!
		#print_r($request_form);
		if ($this->ut == "definition"):
			# Retrieve page variables for recipe definition
			$this->recdef_data[0] = ($this->up == "submittal" ? $post_form[rectype] : stripslashes($request_form[rectype]));
			$this->recdef_data[1] = ($this->up == "submittal" ? $post_form[shortname] : stripslashes($request_form[shortname]));
			$this->recdef_data[2] = ($this->up == "submittal" ? $post_form[fullname] : stripslashes($request_form[fullname]));
			$this->recdef_data[3] = ($this->up == "submittal" ? $post_form[desc] : stripslashes($request_form[desc]));
		elseif ($this->ut == "ingredients"):
			# Retrieve page variables for ingredients
			$page_vars = array(ingr,filler,qty1n,whole1n,qnum1n,qdenom1n,uom1n,qty2n,whole2n,qnum2n,qdenom2n,uom2n,idesc,igroup);
			$this->ningr = $request_form[ningr];
			if ($this->ningr == "") $this->ningr = 5;
			for ($a = 1; $a <= $this->ningr; $a++) {
				foreach ($page_vars as $varn) {
					$varn .= $a;
					$this->ingr_data[(int)$a-1][] = ($this->up == "submittal" ? $post_form[$varn] : stripslashes($request_form[$varn]));
				}
			}
			#print_r($this->ingr_data);
		elseif ($this->ut == "preparation"):
			# Retrieve page variables for preparation instructions
			$this->prep_data[0] = ($this->up == "submittal" ? $post_form[preptype] : stripslashes($request_form[preptype]));
			$this->preptype = $this->prep_data[0];

			# Default to prep type of blurb if not specified
			if ($this->prep_data[0] == ""):
				$this->prep_data[0] = "blurb";
				$this->preptype = "blurb";
			endif;
			#$this->prep_data[1] = ($this->up == "submittal" ? $post_form[prepblurb] : stripslashes($request_form[prepblurb]));
			$page_vars = array(prep,pseq,pdesc,pblurb,pgroup);
			$this->nprep = $request_form[nprep];
			if ($this->nprep == "") {
				if ($this->preptype == "blurb") {
					$this->nprep = 1;
				} else {
					$this->nprep = 5;
				}
			}
			for ($a = 1; $a <= $this->nprep; $a++) {
				foreach ($page_vars as $varn) {
					$varn .= $a;
					$this->prep_data[(int)$a][] = ($this->up == "submittal" ? $post_form[$varn] : stripslashes($request_form[$varn]));
				}
			}
		endif;
	}

	function update_data() {
		## FUNCTION update_data
		##  Make other updates to certain data based on form submittals
		## Input: none

		## Number of ingredient/preparation lines to display in form
		if ($this->submitextra != "") {
			if ($this->preptype == "blurb") {
				$this->ningr += 1;
				$this->nprep += 1;
			} else {
				$this->ningr += 5;
				$this->nprep += 5;
			}
		}

		if ($this->preptype == "blurb") {
			if ($this->submitfewer != "" && $this->ningr > 1) $this->ningr -=1;
			if ($this->submitfewer != "" && $this->nprep > 1) $this->nprep -=1;
		} else {
			if ($this->submitfewer != "" && $this->ningr > 5) $this->ningr -=5;
			if ($this->submitfewer != "" && $this->nprep > 5) $this->nprep -=5;
		}

		## Preparation type switching-- number of prep items will be reset
		if ($this->submitordered != ""):
			$this->preptype = "ordered";
			$this->prep_data[0] = $this->preptype;
			$this->nprep = 5;
		endif;
		if ($this->submitblurb != ""):
			$this->preptype = "blurb";
			$this->prep_data[0] = $this->preptype;
			$this->nprep = 1;
		endif;
	}

	function perform_update() {
		## FUNCTION perform_update
		##  perform database update and give outcome
		## Input: none
		## Output: (bool) TRUE


		if ($this->ut == "definition"):
			$outcome = new_recipe($this->rid,$this->recdef_data,"");
			$new_id = $outcome[0];
			$this->errmsg = $outcome[1];
			$this->rs = ($outcome[1] == "" ? "ok" : "nok");
			$this->rid = $new_id;
		elseif ($this->ut == "ingredients"):
			$outcome = new_ingredients($this->rid,$this->ingr_data,"");
			$this->rs = ($outcome == "" ? "ok" : "nok");
			$this->errmsg = $outcome;
		elseif ($this->ut == "preparation"):
			$outcome = new_preparation($this->rid,$this->prep_data,"");
			$this->rs = ($outcome == "" ? "ok" : "nok");
			$this->errmsg .= $outcome;
		endif;
		return (bool) TRUE;
	}

	function get_title_plus() {
		## FUNCTION get_title
		##  generate title based on current phase
		## Input: none
		## Output: HTML of title+extra

		## Generate title and verbage

		if ($this->up == "result") {
			# Titles independent of section being edited
			if ($this->rs == "ok") $title =  "success!";
			if ($this->rs == "nok") $title = "i failed.";
		} else {
			# Titles dependent of section being edited
			if ($this->up == "entry"):
				$title = (($this->us == "update") ? "update " : "add ");
			elseif ($this->up == "validate"):
				$title =  "validate &amp; submit ";
			endif;
			if ($this->ut == "definition")  $title .= "recipe definition.";
			if ($this->ut == "ingredients") $title .= "ingredients.";
			if ($this->ut == "preparation") $title .= "preparation.";
		}

		## Extra verbage underneath title
		if ($this->ut != "definition" && ($this->up == "entry" || $this->up == "validate")) {
			$extra = "are you editing the correct recipe? details are to the right--&gt;";
			$titleplus = "<h2>$title </h2>\n<p>$extra</p>\n";
		} else {
			$titleplus = "<h2>$title </h2>\n";
		}

		return $titleplus;
	}

	function get_workspace_body() {
		## FUNCTION get_workspace_body
		##  generate the HTML that goes in the main workspae window
		## Input: none
		## Output: HTML for workspace window

		if ($this->up == "result") {
			## Print out generic success information

			if ($this->ut == "definition"): $name = "Recipe Definition";
			elseif ($this->ut == "ingredients"): $name = "Ingredients";
			elseif ($this->ut == "preparation"): $name = "Preparation Instructions";
			endif;

			if ($this->us == "new") $action = "added";
			if ($this->us == "update") $action = "updated";

			if ($this->rs == "ok") $success = "SUCCESSFULLY";
			if ($this->rs == "nok") $success = "NOT SUCCESSFULLY";

			$workspace_body = "<p><strong>$name</strong> $success $action.</p>\n";

			# Add ertror detail in case of failure
			if ($this->rs == "nok"):
				$workspace_body .= "<p>The error message was: <span class=\"warning\">" . $this->errmsg . "</span></p>\n";
			endif;

			$workspace_body .= "<p>The column on the right contains the current definition in the database ";
			$workspace_body .= "for the recipe you have just finished editing.</p>\n<p>Click on one of the buttons ";
			$workspace_body .= "on the right to update other parts of the recipe definition.</p>\n";

			$workspace_body .= "<p>Or <a href=\"view-cocktail?id=" . $this->rid . "\">click here</a>\n";
			$workspace_body .= "to <a href=\"view-cocktail?id=" . $this->rid . "\">see the finished product</a>.</p>\n";

		} else {
			if ($this->ut == "definition"):
				$workspace_body = w_getform_recdef($this->up,$this->recdef_data);
			elseif ($this->ut == "ingredients"):
				$workspace_body = w_getform_ingr($this->up,$this->ingr_data,$this->ningr);
			elseif ($this->ut == "preparation"):
				$workspace_body = w_getform_prep($this->up,$this->prep_data,$this->nprep);
			endif;
		}
		return $workspace_body;
	}

	function get_main_submittal() {
		## FUNCTION get_main_submittal
		##  generate HTML form submission buttons
		## Input: none
		## Output: HTML of form submittal buttons

		$main_submittal = "<fieldset>\n";
		$main_submittal .= "\t<input type=\"hidden\" name=\"us\" value=\"" .$this->us . "\" />\n";
		$main_submittal .= "\t<input type=\"hidden\" name=\"up\" value=\"" .$this->up . "\" />\n";
		$main_submittal .= "\t<input type=\"hidden\" name=\"ut\" value=\"" .$this->ut . "\" />\n";
		$main_submittal .= "\t<input type=\"hidden\" name=\"rid\" value=\"" .$this->rid . "\" />\n";
		$main_submittal .= "\t<input type=\"hidden\" name=\"ningr\" value=\"" . $this->ningr . "\" />\n";
		$main_submittal .= "\t<input type=\"hidden\" name=\"nprep\" value=\"" . $this->nprep . "\" />\n";
		$main_submittal .= "\t<input type=\"hidden\" name=\"preptype\" value=\"" . $this->preptype . "\" />\n";

		if ($this->ut == "definition")  $name = "recipe definition";
		if ($this->ut == "ingredients") $name = $this->ut;
		if ($this->ut == "preparation") $name = "preparation instructions";

		if ($this->up == "entry"):
			$main_submittal .= "\t<input type=\"submit\" name=\"submitentry\" value=\"submit for validation\" />\n";
			$main_submittal .= "\t<input type=\"reset\" value=\"clear fields\" />\n";
			if ($this->ut == "ingredients"):
				$main_submittal .= "\t<input type=\"submit\" name=\"submitextra\" value=\"add 5 more lines\" />\n";
				$main_submittal .= "\t<input type=\"submit\" name=\"submitfewer\" value=\"remove 5 lines\" />\n";
			elseif ($this->ut == "preparation"):
				if ($this->preptype == "blurb"):
					$main_submittal .= "\t<input type=\"submit\" name=\"submitextra\" value=\"add another section\" />\n";
					$main_submittal .= "\t<input type=\"submit\" name=\"submitfewer\" value=\"remove one section\" />\n";
					$main_submittal .= "\t<input type=\"submit\" name=\"submitordered\" value=\"Nah, I want an ordered sequence\" />\n";
				elseif ($this->preptype == "ordered"):
					$main_submittal .= "\t<input type=\"submit\" name=\"submitextra\" value=\"add 5 more lines\" />\n";
					$main_submittal .= "\t<input type=\"submit\" name=\"submitfewer\" value=\"remove 5 lines\" />\n";
					$main_submittal .= "\t<input type=\"submit\" name=\"submitblurb\" value=\"Change to paragraph format\" />\n";
				endif;
			endif;
		elseif ($this->up == "validate"):
			$main_submittal .= "\t<input type=\"submit\" name=\"submitcreate\" value=\"looks good, create " . $name . "\" />\n";
			$main_submittal .= "\t<input type=\"submit\" name=\"submitedit\" value=\"not quite. go back and edit\" />\n";
		endif;
		$main_submittal .= "</fieldset>\n";

		return $main_submittal;
	}

	function get_aux_recdef() {
		## FUNCTION get_aux_recdef
		##  generate HTML for recipe definition section of aux status box
		## Input: none
		## Output: HTML containing recipe definition section of aux status box

		if ($this->ut == "definition" && $this->us == "new" && ($this->up == "entry" || $this->up == "validate")) {
			$aux_recdef = "<p class=\"msg\"><span class=\"current\">--creating new recipe definition--</span></p>";
		} else {

			if ($this->ut == "definition" && $this->up != "result") {
				$aux_recdef .= "<span class=\"current\"><p class=\"msg\">--updating recipe definition--</p>\n";
			}
			$recdef = w_get_recdef($this->us,$this->up,$this->ut,$this->rid);

			if ($recdef) {
				$aux_recdef .= $recdef[0];
			} else {
				$aux_recdef .= "<p class=\"msg\">No recipe definition exists</p>\n";
			}
			if ($this->ut == "definition" && $this->up != "result") $aux_recdef .= "</span>";
			$aux_recdef .= "\n";
		}

		if ($this->up == "result" && $recdef) {
			## Create the form to allow editing of the recipe definition
			$aux_recdef .= "<form action=\"update-recipe\" method=\"post\">\n";
			$aux_recdef .= "\t<fieldset>\n";
			$aux_recdef .= "\t\t<input type=\"hidden\" name=\"us\" value=\"update\" />\n";
			$aux_recdef .= "\t\t<input type=\"hidden\" name=\"up\" value=\"entry\" />\n";
			$aux_recdef .= "\t\t<input type=\"hidden\" name=\"ut\" value=\"definition\" />\n";
			$aux_recdef .= "\t\t<input type=\"hidden\" name=\"rid\" value=\"" . $this->rid . "\" />\n";
			$aux_recdef .= "\t\t<input type=\"hidden\" name=\"rectype\" value=\"" . $recdef[1] . "\" />\n";
			$aux_recdef .= "\t\t<input type=\"hidden\" name=\"shortname\" value=\"" . $recdef[2] . "\" />\n";
			$aux_recdef .= "\t\t<input type=\"hidden\" name=\"fullname\" value=\"" . $recdef[3] . "\" />\n";
			$aux_recdef .= "\t\t<input type=\"hidden\" name=\"desc\" value=\"" . $recdef[4] . "\" />\n";
			$aux_recdef .= "\t\t<input type=\"submit\" value=\"Edit Recipe Definition\" />\n";
			$aux_recdef .= "\t</fieldset>\n";
			$aux_recdef .= "</form>\n";

		}
		return $aux_recdef;
	}

	function get_aux_ingr() {
		## FUNCTION get_aux_ingr
		##  generate HTML for ingredients section of aux status box.
		## Input: none
		## Output: HTML containing ingredients section of aux status box.

		if ($this->ut == "ingredients" && $this->us == "new" && ($this->up == "entry" || $this->up == "validate")) {
			$aux_ingr = "<p class=\"msg\"><span class=\"current\">--creating new ingredients--</span></p>";
		} else {
			if ($this->ut == "ingredients" && $this->us == "update" && ($this->up == "entry" || $this->up == "validate")) {
				$aux_ingr .= "<span class=\"current\"><p class=\"msg\">--updating ingredients --</p>\n";
			}
			$ingredients = w_get_ingr($this->us,$this->up,$this->ut,$this->rid);
			$num_ingredients = count($ingredients);

			if ($ingredients) {
				$aux_ingr .= $ingredients[0];
			} else {
				$aux_ingr .= "<p class=\"msg\">No ingredients found</p>\n";
			}
		}

		if ($this->ut == "ingredients" && $this->up != "result") $aux_ingr .= "</span>\n";

		$aux_ingr .= "\n";

		if ($this->up == "result") {
			if ($ingredients) {
				## Print form to update existing ingredients for recipe
				$aux_ingr .= "<form action=\"update-recipe\" method=\"post\">\n";
				$aux_ingr .= "\t<fieldset>\n";
				$aux_ingr .= "\t\t<input type=\"hidden\" name=\"us\" value=\"update\" />\n";
				$aux_ingr .= "\t\t<input type=\"hidden\" name=\"up\" value=\"entry\" />\n";
				$aux_ingr .= "\t\t<input type=\"hidden\" name=\"ut\" value=\"ingredients\" />\n";
				$aux_ingr .= "\t\t<input type=\"hidden\" name=\"rid\" value=\"" . $this->rid . "\" />\n";
				$aux_ingr .= "\t\t<input type=\"hidden\" name=\"ningr\" value=\"" . $num_ingredients . "\" />\n";

				## Put all ingredients to form fields
				$page_vars = array(ingr,filler,qty1n,whole1n,qnum1n,qdenom1n,uom1n,qty2n,whole2n,qnum2n,qdenom2n,uom2n,idesc,igroup);

				for ($a = 1; $a < $num_ingredients; $a++) {
					for ($b = 0; $b <= 13; $b++) {
						$varn = $page_vars[$b];
						if ($varn == "ingr") {
							$varn .= $a;
							$aux_ingr .= "\t\t<input type=\"hidden\" name=\"$varn\" value=\"on\" />\n";
						} else {
							$varn .= $a;
							$aux_ingr .= "\t\t<input type=\"hidden\" name=\"$varn\" value=\"" . ($ingredients[$a][(int)$b]? $ingredients[$a][(int)$b] : "") . "\" />\n";
						}
					}
				}
				$aux_ingr .= "\t\t<input type=\"submit\" value=\"Edit Ingredients\" />\n";
				$aux_ingr .= "\t</fieldset>\n";
				$aux_ingr .= "</form>\n";
			} else {
				## Print form to add new ingredients to recipe
				$aux_ingr .= "<form action=\"update-recipe\" method=\"post\">\n";
				$aux_ingr .= "\t<fieldset>\n";
				$aux_ingr .= "\t\t<input type=\"hidden\" name=\"us\" value=\"new\" />\n";
				$aux_ingr .= "\t\t<input type=\"hidden\" name=\"up\" value=\"entry\" />\n";
				$aux_ingr .= "\t\t<input type=\"hidden\" name=\"ut\" value=\"ingredients\" />\n";
				$aux_ingr .= "\t\t<input type=\"hidden\" name=\"rid\" value=\"" . $this->rid . "\" />\n";
				$aux_ingr .= "\t\t<input type=\"hidden\" name=\"ningr\" value=\"5\" />\n";
				$aux_ingr .= "\t\t<input type=\"submit\" value=\"Add Ingredients\" />\n";
				$aux_ingr .= "\t</fieldset>\n";
				$aux_ingr .= "</form>\n";
			}
		}
		return $aux_ingr;
	}

	function get_aux_prep() {
		## FUNCTION get_aux_prep
		##  generate HTML for preparation instructions in aux status box.
		## Input: none
		## Output: HTML containing preparation instructions in aux status box.

		if ($this->ut == "preparation" && $this->us == "new" && ($this->up == "entry" || $this->up == "validate")) {
			$aux_prep = "<p class=\"msg\"><span class=\"current\">--creating new preparation instructions--</span></p>";
		} else {
			if ($this->ut == "preparation" && $this->us == "update" && ($this->up == "entry" || $this->up == "validate")) {
				$aux_prep .= "<span class=\"current\"><p class=\"msg\">--updating preparation instructions --</p>\n";
			}

			$preparation = w_get_prep($this->us,$this->up,$this->ut,$this->rid);

			if ($preparation) {
				$aux_prep .= $preparation[0];
			} else {
				$aux_prep .= "<p class=\"msg\">No preparation instructions found</p>\n";
			}
		}

		if ($this->ut == "preparation" && $this->up != "result") $aux_prep .= "</span>\n";

		$aux_prep .= "\n";


		if ($this->up == "result") {
			if ($preparation) {
				$aux_prep .= "<form action=\"update-recipe\" method=\"post\">\n";
				$aux_prep .= "\t<fieldset>\n";
				$aux_prep .= "\t\t<input type=\"hidden\" name=\"us\" value=\"update\" />\n";
				$aux_prep .= "\t\t<input type=\"hidden\" name=\"up\" value=\"entry\" />\n";
				$aux_prep .= "\t\t<input type=\"hidden\" name=\"ut\" value=\"preparation\" />\n";
				$aux_prep .= "\t\t<input type=\"hidden\" name=\"rid\" value=\"" . $this->rid . "\" />\n";

				## Put all preparation instructions to form fields
				#array_shift($preparation);
				$num_prep = count($preparation) - 1;
				$aux_prep .= "\t\t<input type=\"hidden\" name=\"preptype\" value=\"" . $preparation[1][1] . "\" />\n";
				for ($a = 1; $a <= $num_prep; $a++) {
					$varn = "prep" . $a;
					$aux_prep .= "\t\t<input type=\"hidden\" name=\"$varn\" value=\"on\" />\n";
					$varn = "pseq" . $a;
					$aux_prep .= "\t\t<input type=\"hidden\" name=\"$varn\" value=\"" . $preparation[$a][2] . "\" />\n";
					$varn = "pdesc" . $a;
					$aux_prep .= "\t\t<input type=\"hidden\" name=\"$varn\" value=\"" . $preparation[$a][3] . "\" />\n";
					$varn = "pblurb" . $a;
					$aux_prep .= "\t\t<input type=\"hidden\" name=\"$varn\" value=\"" . $preparation[$a][4] . "\" />\n";
					$varn = "pgroup" . $a;
					$aux_prep .= "\t\t<input type=\"hidden\" name=\"$varn\" value=\"" . $preparation[$a][5] . "\" />\n";
				}
				$aux_prep .= "\t\t<input type=\"submit\" value=\"Edit Preparation Instructions\" />\n";
				$aux_prep .= "\t</fieldset>\n";
				$aux_prep .= "</form>\n";
			} else {
				$aux_prep .= "<form action=\"update-recipe\" method=\"post\">\n";
				$aux_prep .= "\t<fieldset>\n";
				$aux_prep .= "\t\t<input type=\"hidden\" name=\"us\" value=\"new\" />\n";
				$aux_prep .= "\t\t<input type=\"hidden\" name=\"up\" value=\"entry\" />\n";
				$aux_prep .= "\t\t<input type=\"hidden\" name=\"ut\" value=\"preparation\" />\n";
				$aux_prep .= "\t\t<input type=\"hidden\" name=\"rid\" value=\"" . $this->rid . "\" />\n";
				$aux_prep .= "\t\t<input type=\"submit\" value=\"Add Preparation Instructions\" />\n";
				$aux_prep .= "\t</fieldset>\n";
				$aux_prep .= "</form>\n";
			}
		}
		return $aux_prep;
	}
}
?>