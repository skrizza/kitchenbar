<?php
##
## update-recipe-v2.php
##
## PHP/HTML page that manages creation and updates of recipes/cocktails to the database.
##
## Code: s k r a t c h
##
##   version 2: most logic separated out to new RecipeUpdater class (skratch)
## 26-MAY-2018: [#1] Fixed includes and all paths to use relative paths (skratch)
##

include(dirname(__FILE__) . "/../inc/skratchadmin.php");
include(dirname(__FILE__) . "/../inc/kitchenbar.php");
include(dirname(__FILE__) . "/../inc/clsHtmlEdit.php");
include(dirname(__FILE__) . "/../inc/clsRecipeUpdater.php");

# Create new instance of class RecipeUpdater with HTML form data
$updateMgr = new RecipeUpdater($_REQUEST,$_POST);

###########################
###########################
##                       ##
##  UPDATES TO DATABASE  ##
##                       ##
###########################
###########################

## The code below is only run if form was submitted via POST.
## After it is run the results page is reloaded via GET.

if ($updateMgr->up == "submittal") {
	$updateMgr->perform_update();
	## Re-load page to leave submittal phase (protects agains 'refresh' form resubmission)
	$url = "Location: http://skratch.free.fr/perso/kitchenbar/mgr/update-recipe?";
	$url .= "rid=" . $updateMgr->rid . "&up=result&ut=" . $updateMgr->ut;
	$url .= "&us=" . $updateMgr->us . "&rs=" . $updateMgr->rs;
	$url .= "&errmsg=" . $updateMgr->errmsg;

	header($url);
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1" />
	<title>Cocktails.</title>
	<style type="text/css" media="all">
		@import "../css/cocktails.css";
	</style>
</head>
<body id="cocktails">

<div id="page">
	<div id="page-header">
		<h1>U p d a t e s. <span class="fade">makin it all happen</span></h1>
		<ul id="content-index">
			<li class="first"><a href="recipemgr">Recipe Manager</a></li>
			<li><a href="../cocktails">Cocktails</a></li>
			<li><a href="../recipes">Recipes</a></li>
			<li>Music</li>
			<li>Literature</li>
			<li class="last">Film</li>
			<li class="last"><!-- <?php print "us: [" . $updateMgr->us . "], up: [" . $updateMgr->up . "], ut: [" . $updateMgr->ut . "], rid: [" . $updateMgr->rid . "]";
 ?>--></li>
		</ul>
	</div> <!-- #page-header -->

	<div id="main-content">
		<form action="update-recipe" method="post">
		<div id="item-title">
<?php
$titleplus = $updateMgr->get_title_plus();
print indent_text(3,$titleplus);
?>
		</div> <!-- #item-title -->

		<div id="workspace">
			<h3><span>form data.</span></h3>
			<div id="mainform">
<?php


$workspacewindow = $updateMgr->get_workspace_body();
print indent_text(4,$workspacewindow);
?>
			</div> <!-- #mainform -->
		</div> <!-- #workspace -->
		<div id="form-submission">
<?php
$main_submission = $updateMgr->get_main_submittal();
print indent_text(3,$main_submission);
?>
		</div> <!-- #form-submission -->
		</form>
	</div> <!-- #main-content -->

	<div id="aux-content">
		<h3><span>Recipe Definition</span></h3>
		<div id="form-status">
<?php
$aux_recdef = $updateMgr->get_aux_recdef();
print indent_text(3,$aux_recdef);
?>
			<hr />

			<h1>Ingredients:</h1>
<?php
$aux_ingr = $updateMgr->get_aux_ingr();
print indent_text(3,$aux_ingr);
?>
			<hr />
			<h1>Preparation:</h1>
<?php
$aux_prep = $updateMgr->get_aux_prep();
print indent_text(3,$aux_prep);
?>
		</div> <!-- #form-status -->
	</div> <!-- #aux-content -->
</div> <!-- #page -->
</body>
</html>