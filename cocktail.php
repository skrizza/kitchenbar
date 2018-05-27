<?php
##
## cocktail.php
##
## display cocktail detail.
## Page variables:
##  $style (metric/usa/ratio) default: usa
##  $double (true/false) default: false
##  $id (integer) default: 1
##
## Code: s k r a t c h
##
## 26-MAY-2018: [#1] Fixed includes to use relative paths (skratch)
##


include(dirname(__FILE__) . "/inc/skratchadmin.php");
include(dirname(__FILE__) . "/inc/kitchenbar.php");
include(dirname(__FILE__) . "/inc/clsHtmlEdit.php");
include(dirname(__FILE__) . "/inc/clsCocktail.php");
include(dirname(__FILE__) . "/inc/clsFractions.php");

# Get page variables

$id = $_REQUEST[id];
$style = $_REQUEST[style];
$doubled = $_REQUEST[doubled];

# Set default values if data is missing
if ($id == "") $id = 1;
if ($style != "metric" && $style != "usa" && $style != "ratio") $style = "usa";
if ($doubled != "true" && $doubled != "false") $doubled = "false";

$recipe = new Cocktail($id);
if ($doubled == "true") $recipe->apply_ingr_mult("2");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1" />
	<title>Cocktails: <?php print $recipe->name; ?></title>
	<script type="text/javascript"></script>
	<style type="text/css" media="all">
		@import "/perso/kitchenbar/css/cocktails.css";
	</style>
</head>

<body id="cocktails">

<div id="page">
	<div id="page-header">
		<h1>C o c k t a i l s. <span class="fade">the life of the party</span></h1>
		<ul id="content-index">
			<li class="first"><a href="/perso/kitchenbar/cocktails">Cocktails</a></li>
			<li><a href="/perso/kitchenbar/recipes">Recipes</a></li>
			<li>Music</li>
			<li>Literature</li>
			<li class="last">Film</li>
		</ul>
	</div> <!-- #page-header -->

	<div id="main-content">
		<h2><?php print $recipe->name; ?></h2>
		<div id="ingredients">
			<h3><span>Ingredients</span></h3>
			<div id="ingrbody">
<?php 
if ($style == "ratio"):
	print indent_text(4,$recipe->get_ingr_ratio());
else:
	print indent_text(4,$recipe->gen_ingredients()); 
endif;

print indent_text(4,$recipe->get_utilbar($id,$style,$doubled));
?>
			</div><!-- #ingrbody -->
		</div> <!-- #ingredients -->

		<div id="preparation">
			<h3><span>Preparation</span></h3>
			<div id="prepbody">
<?php print indent_text(4,$recipe->preparation); ?>
			</div><!-- #prepbody -->
		</div> <!-- #preparation -->

	</div> <!-- #main-content -->

	<div id="aux-content">
		<h3><span>about the recipe.</span></h3>
		<div id="aux-recipedef">
			<p id="item-blurb"><?php print $recipe->description; ?></p>
		</div><!-- #aux-recipedef -->
	</div> <!-- #aux-content -->
	<div id="praetorian"><a href="mgr/recipemgr"><img alt="s" src="/images/s.gif" /></a></div>
</div> <!-- #page -->
</body>
</html>
