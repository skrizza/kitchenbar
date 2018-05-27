<?php
##
## recipemgr.php
##
## Display cocktails, 12 at a time. Link lead to edit screen
##

$doc_root = $_SERVER[DOCUMENT_ROOT];
include("$doc_root/perso/kitchenbar/inc/skratchadmin.php");
include("$doc_root/perso/kitchenbar/inc/kitchenbar.php");
include("$doc_root/perso/kitchenbar/inc/clsColumnDisplay.php");

## Get page variables

$iid = $_REQUEST[iid];
if ($iid == "") $iid = 0;

## Set page constants

$DISPLAY_NUM = 12;
$DISPLAY_COLUMN_NUM = 6;
$STYLE = "edit";
$TYPE = "all";

## create new instance of DisplayColumn
$display = new ColumnDisplay(${iid},${DISPLAY_COLUMN_NUM},${STYLE},${TYPE});

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1" />
	<title>Cocktails.</title>
	<script type="text/javascript"></script>
	<style type="text/css" media="all">
		@import "/perso/kitchenbar/css/cocktails.css";
	</style>
</head>

<body id="cocktails">

<div id="page">

	<div id="page-header">
		<h1>U p d a t e s. <span class="fade">makin it all happen</span></h1>
		<ul id="content-index">
			<li class="first"><a href="/perso/kitchenbar/mgr/recipemgr">Recipe Manager</a></li>
			<li><a href="/perso/kitchenbar/cocktails">Cocktails</a></li>
			<li><a href="/perso/kitchenbar/recipes">Recipes</a></li>
			<li>Music</li>
			<li>Literature</li>
			<li class="last">Film</li>
		</ul>
	</div> <!-- #page-header -->

	<div id="main-content">
		<div id="page-intro">
			<h2>at your service. <span class="fade">(recipe editor)</span></h2>
		</div> <!-- page-intro -->

		<div id="maintenance-list">
			<h3><span>the cocktail list.</span></h3>
			<div id="listing">
				<p><a href="update-recipe">Create a new recipe</a></p>
				<p>Or Choose a recipe to edit:</p>
<?php
## Show first column data
$col1 = $display->get_col1();
print $col1;

## Show second column data
$col2 = $display->get_col2();
print $col2;
?>
				<p id="page-nav">
<?php
## Generate page navigation
$prevnav = $display->get_prevnav();
$nextnav = $display->get_nextnav();
print $prevnav . " / " . $nextnav;
?>
				</p><!-- #page-nav -->
			</div><!-- #listing -->
		</div> <!-- detail-list -->
	</div> <!-- id=main-content -->


</div> <!-- id=page -->
</body> <!-- cocktails -->
</html>
