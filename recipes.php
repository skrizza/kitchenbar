<?php
##
## cocktails.php
##
## Display cocktails, 12 at a time.
##

$doc_root = $_SERVER[DOCUMENT_ROOT];
include("$doc_root/perso/kitchenbar/inc/skratchadmin.php");
include("$doc_root/perso/kitchenbar/inc/kitchenbar.php");
include("$doc_root/perso/kitchenbar/inc/clsColumnDisplay.php");
include("$doc_root/perso/kitchenbar/inc/clsHtmlEdit.php");

## Get page variables

$iid = $_REQUEST[iid];
if ($iid == "") $iid = 0;

## Set page constants

$DISPLAY_NUM = 12;
$DISPLAY_COLUMN_NUM = 6;
$STYLE = "display";
$TYPE = "recipe";

## create new instance of DisplayColumn
$display = new ColumnDisplay(${iid},${DISPLAY_COLUMN_NUM},${STYLE},${TYPE});

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1" />
	<title>Recipes.</title>
	<script type="text/javascript"></script>
	<style type="text/css" media="all">
		@import "/perso/kitchenbar/css/cocktails.css";
	</style>
</head>

<body id="recipes">

<div id="page">
	<div id="page-header">
		<h1>R e c i p e s . <span class="fade">because you gotta eat</span></h1>
		<ul id="content-index">
			<li class="first"><a href="/perso/kitchenbar/cocktails">Cocktails</a></li>
			<li><a href="/perso/kitchenbar/recipes">Recipes</a></li>
			<li>Music</li>
			<li>Literature</li>
			<li class="last">Film</li>
		</ul>
	</div> <!-- #page-header -->

	<div id="main-content">
		<div id="page-intro">
			<h2>hungry ? <span class="fade">(recipes by name)</span></h2>
		</div> <!-- #page-intro -->

		<div id="recipe-list">
			<h3><span>the recipe list.</span></h3>
			<div id="listing">
<?php
## Show first column data
$col1 = $display->get_col1();
$code = indent_text(4,$col1);
print $code;

## Show second column data
$col2 = $display->get_col2();
$code = indent_text(4,$col2);
print $code;
?>
				<p id="page-nav">
<?php
## Generate page navigation
$prevnav = $display->get_prevnav();
$nextnav = $display->get_nextnav();
print "\t\t\t\t" . $prevnav . " / " . $nextnav;
?>
				</p>
			</div><!-- #listing -->
		</div> <!-- #detail-list -->
		
	</div> <!-- #main-content -->
	<div id="praetorian"><a href="mgr/recipemgr"><img alt="s" src="/images/s.gif" /></a></div>
</div> <!-- #page -->
</body> <!-- #cocktails -->
</html>
