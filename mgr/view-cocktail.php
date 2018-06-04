<?php
##
## viewcocktail.php
##
## display cocktail detail to allow for editing
## Page variables:
##  $style (metric/usa/ratio) default: usa
##  $double (true/false) default: false
##  $id (integer) default: 1
##
## Code: s k r a t c h
##
## 26-MAY-2018: [#1] Fixed includes and all paths to use relative paths (skratch)
## 04-JUN-2018: Merged mobile and desktop versions to a more  responsive design (skratch)
##

include(dirname(__FILE__) . "/../inc/skratchadmin.php");
include(dirname(__FILE__) . "/../inc/kitchenbar.php");
include(dirname(__FILE__) . "/../inc/clsHtmlEdit.php");
include(dirname(__FILE__) . "/../inc/clsCocktail.php");

## get page variables

$id = $_REQUEST[id];
$style = $_REQUEST[style];
$doubled = $_REQUEST[doubled];

## set default values if data is missing
if ($id == "") $id = 1;
if ($style != "metric" && $style != "usa" && $style != "ratio") $style = "usa";
if ($doubled != "true" && $doubled != "false") $doubled = "false";

$recipe = new Cocktail($id);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1" />
	<meta name="viewport" content="width=device-width; initial-scale=1.0" />
	<title>Cocktail Viewer: <?php print $name; ?></title>
	<script type="text/javascript"></script>
	<link rel="apple-touch-icon" href="/iphone.png" />
	<link rel="stylesheet" href="../css/kitchenbar.css" type="text/css" />
</head>

<body id="cocktails">

<div id="page">
	<header id="page-header">
		<h1>U p d a t e s. <span class="fade">makin it all happen</span></h1>
		<ul id="content-index">
			<li class="first"><a href="recipemgr">Recipe Manager</a></li>
			<li><a href="../cocktails">Cocktails</a></li>
			<li><a href="../recipes">Recipes</a></li>
			<li class="last">More</li>
		</ul>
	</header> <!-- #page-header -->

	<div id="main-content">

		<div id="item-title">
			<form action="update-recipe" method="post">
				<h2><?php print $recipe->name; ?><input type="submit" name="updaterecipe" value="Edit!" /></h2>
<?php
	$form = $recipe->get_recdef_form();
	print indent_text(4,$form);
?>
			</form>
		</div> <!-- id=item title -->

		<div id="ingredients">
			<h3><span>Ingredients</span></h3>
			<div id="ingrbody">
<?php print indent_text(4,$recipe->ingredients); ?>
				<form action="update-recipe" method="post">
					<fieldset>
						<input type="submit" name="updateingr" value="Edit!" />
					</fieldset>
<?php
	$form = $recipe->get_ingr_form();
	print indent_text(5,$form);
?>
				</form>
			</div> <!-- #ingrbody -->

		</div> <!-- #ingredients -->

		<div id="preparation">
			<h3><span>Preparation</span></h3>
			<div id="prepbody">
<?php print indent_text(4,$recipe->preparation); ?>
				<form action="update-recipe" method="post">
					<fieldset>
						<input type="submit" name="updateprep" value="Edit!" />
					</fieldset>
<?php
	$form = $recipe->get_prep_form();
	print indent_text(5,$form);
?>
				</form>
			</div> <!-- #prepbody -->
		</div> <!-- #preparation -->

	</div> <!-- #main-content -->

	<div id="aux-content">
		<h3><span>about the recipe.</span></h3>
		<div id="recipedef">
			<p><?php print $recipe->description; ?></p>
			<form action=" update-recipe" method="post">
				<fieldset>
					<input type="submit" name="updaterecipe" value="Edit!" />
				</fieldset>
<?php
	$form = $recipe->get_recdef_form();
	print indent_text(4,$form);
?>
			</form>
		</div> <!-- #recipedef -->
	</div> <!-- #aux-content -->
</div> <!-- id=page -->
</body>
</html>
