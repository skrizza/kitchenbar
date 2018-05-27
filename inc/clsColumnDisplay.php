<?php
##
## clsColumnDisplay.php
##
## class to manage 2-column navigation through list of cocktails
## Relies on database functions in skratchadmin.php. Calling function must include this.
##

class ColumnDisplay {
	## CLASS ColumnDisplay
	##  Class to manage 2-column navigation.
	##

	## Class variable declaration
	var $DISPLAY_NUM;          ## Total number of items to display on screem
	var $DISPLAY_COLUMN_NUM;   ## Number of items to display in each column
	var $index;                ## Cocktail list position
	var $cocktails;            ## Cocktail list (array)
	var $num_cocktails;        ## Total number of cocktails
	var $col1start;            ## starting index of column 1
	var $col1end;              ## ending index of column 1
	var $col2start;            ## starting index of column 2
	var $col2end;              ## ending index of column 2
	var $prevnav;              ## the index to jump to if 'prev' button is pressed
	var $nextnav;              ## the index to jump to if 'next' button is pressed
	var $MODE;	           ## display/edit mode-- impacts display html code
	var $VIEW_PAGE;            ## Page that displays recipe detail in view mode
	var $EDIT_PAGE;            ## Page that displays recipe detail in edit mode
	var $INDEX_VIEW_PAGE;      ## Page that displays recipe index in view mode
	var $INDEX_EDIT_PAGE = "recipemgr";  ## Page that displays recipe index in edit mode
	var $TYPE;                 ## Chooses display of cocktails, recipes, or all

	function ColumnDisplay($iid,$column_length,$style,$type) {
		## FUNCTION ColumnDisplay
		##  Constructor for class ColumnDisplay
		## Input: $iid, position in cocktail list (NOT cocktail id)
		##        $column_length, number of items to display in each column
		##        $style, display or edit
		## Output: TRUE

		## initialize variables
		$this->index = $iid;
		$this->DISPLAY_COLUMN_NUM = $column_length;
		$this->DISPLAY_NUM = $this->DISPLAY_COLUMN_NUM * 2;
		$this->MODE = $style;
		$this->TYPE = $type;

		## Calculate Page Names

		if ($this->TYPE == "recipe") {
			$this->VIEW_PAGE = "recipe";
			$this->EDIT_PAGE = "view-cocktail";
			$this->INDEX_VIEW_PAGE = "recipes";
		} else {
			$this->VIEW_PAGE = "cocktail";
			$this->EDIT_PAGE = "view-cocktail";
			$this->INDEX_VIEW_PAGE = "cocktails";
		}

		## get cocktail list and calculate number of cocktails
		$this->cocktails = $this->get_sorted_recipes($this->TYPE);
		$this->num_cocktails = count($this->cocktails);

		## reset index if value is out of range
		if ($this->index >= $this->num_cocktails) $this->index = 0;

		## set up column start/end points
		## first, set column one as $index plus column length
		$this->col1start = $this->index;
		$this->col1end = $this->index + $this->DISPLAY_COLUMN_NUM - 1;
		$this->col2start = (bool) TRUE;
		$this->col2end   = (bool) TRUE;

		## now-- if column 1 reaches the end of the cocktail list, stop there.
		if ($this->col1end >= $this->num_cocktails - 1):
			$this->col1end = $this->num_cocktails - 1;
			$this->col2start = (bool) FALSE;
			$this->col2end   = (bool) FALSE;
		endif;

		## if column 2 is not empty, set start/emd points for column 2
		if ($this->col2start):
			$this->col2start = $this->index + $this->DISPLAY_COLUMN_NUM;
			$this->col2end = $this->index + $this->DISPLAY_NUM - 1;
			## if column 2 reaches the end of cocktail list, stop there.
			if ($this->col2start >= $this->num_cocktails - 1):
				$this->col2start = $this->num_cocktails - 1;
				$this->col2end = $this->col2start;
			elseif ($this->col2end >= $this->num_cocktails - 1):
				$this->col2end = $this->num_cocktails - 1;
			endif;
		endif;

		## Set up page navigation
		## NEXT-- add one unless the end is reached.
		if ($this->col2start): $this->nextnav = ($this->col2end < $this->num_cocktails - 1 ? $this->col2end + 1 : (bool) FALSE);
		else: $this->nextnav = (bool) FALSE;
		endif;

		## PREV-- if going backwards past display length goes negative, reset to zero. otherwize
		##        subtract by the display length.
		if ($this->col1start > 0):
			$this->prevnav = ($this->col1start - $this->DISPLAY_NUM < 0 ? 0 : $this->col1start - $this->DISPLAY_NUM);
		else:
			$this->prevnav = (bool) FALSE;
		endif;
		return (bool) TRUE;

	}

	function get_col1() {
		## FUNCTION get_col1
		##  return a string that contains the list of cocktails for column 1.
		## Input: none
		## Output: string containg contents of column 1.

		$page = ($this->MODE == "edit" ? $this->EDIT_PAGE : $this->VIEW_PAGE);
		$html_code = "<ul id=\"list1\">\n";
		## Show first column data
		for ($a = $this->col1start; $a <= $this->col1end; $a++) {
			$line  = "\t<li><a href=\"" . $page . "?id=";
			$line .= $this->cocktails[$a][0] . "\">";
			$line .= $this->cocktails[$a][1] . "</a></li>\n";
			$html_code .= $line;
		}
		$html_code .= "</ul>\n";
		return $html_code;
	}

	function get_col2() {
		## FUNCTION get_col2
		##  return a string that contains the list of cocktails for column 2.
		## Input: none
		## Output: string containg contents of column 2.

		$page = ($this->MODE == "edit" ? $this->EDIT_PAGE : $this->VIEW_PAGE);
		$html_code = "<ul id=\"list2\">\n";
		## Show second column data
		if ($this->col2start) {
			for ($a = $this->col2start; $a <= $this->col2end; $a++) {
				$line  = "\t<li><a href=\"" . $page . "?id=";
				$line .= $this->cocktails[$a][0] . "\">";
				$line .= $this->cocktails[$a][1] . "</a></li>\n";
				$html_code .= $line;
			}
		} else { ## Print something to avoid an empty <ul></ul>
			$html_code .= "&nbsp;\n";
		}
		$html_code .= "</ul>\n";
		return $html_code;
	}

	function get_prevnav() {
		## FUNCTION get_prevnav
		##  return a string containing the 'prev' navigation html code
		## Input: none
		## Output: string containing the 'prev' navigation html code

		$page = ($this->MODE == "edit" ? $this->INDEX_EDIT_PAGE : $this->INDEX_VIEW_PAGE);
		if ($this->prevnav === (bool) FALSE): $html_code = "prev";
		else: print "<a href=\"" . $page . "?iid=" . $this->prevnav . "\">prev</a>\n";
		endif;
		return $html_code;
	}

	function get_nextnav() {
		## FUNCTION get_prevnav
		##  return a string containing the 'next' navigation html code
		## Input: none
		## Output: string containing the 'next' navigation html code

		$page = ($this->MODE == "edit" ? $this->INDEX_EDIT_PAGE : $this->INDEX_VIEW_PAGE);
		if ($this->nextnav === (bool) FALSE): $html_code = "next";
		else: $html_code =  "<a href=\"" . $page . "?iid=" . $this->nextnav . "\">next</a>\n";
		endif;
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

	function get_sorted_recipes($type) {
		## FUNCTION get_sorted_recipes
		##  return an array containing all recipes with ids, sorted by recipe name.
		## Input: none
		## Output: array:
		##         [x][0]: id
		##         [x][1]: cocktail name
		##

		$table_name = "kb_recipes";

		# Build WHERE clause based on type
		if ($type == "recipe") {
			$filter = "WHERE TYPE = 'recipe'";
		} elseif ($type == "cocktail") {
			$filter = "WHERE TYPE = 'cocktail'";
		} else {
			$filter = "";
		}

		$fields = "ID,NAME";
		$order_by = "NAME";
		$query = "SELECT $fields FROM $table_name $filter ORDER BY $order_by";

		$conn = get_connection();
		$result = run_query($query,$conn);
		$rows = dump_query_res($result);
		return $rows;
	}
}