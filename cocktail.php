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
## 04-JUN-2018: Merged mobile and desktop versions to a more  responsive design (skratch)
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
	<meta name="viewport" content="width=device-width; initial-scale=1.0" />		
	<title>Cocktails.</title>
	<script type="text/javascript"></script>
	<link rel="apple-touch-icon" href="/iphone.png" />
	<link rel="stylesheet" href="css/kitchenbar.css" type="text/css" />
</head>

<body id="cocktails">

<div id="page">
	<header id="page-header">
		<h1>Cocktails. <span class="fade">the life of the party</span></h1>
		<ul id="content-index">
			<li class="first"><a href="cocktails">Cocktails</a></li>
			<li><a href="recipes">Recipes</a></li>
			<li>Music</li>
			<li class="last">More</li>
		</ul>
	</header> <!-- #page-header -->

	<div id="main-content">
		<h2><?php print $recipe->name; ?></h2>
		<section id="ingredients">
			<h3><span>ingredients</span></h3>
			<article id="ingrbody">
<?php 
if ($style == "ratio"):
	print indent_text(4,$recipe->get_ingr_ratio());
else:
	print indent_text(4,$recipe->gen_ingredients()); 
endif;

print indent_text(4,$recipe->get_utilbar($id,$style,$doubled));
?>
			</article><!-- #ingrbody -->
		</section> <!-- #ingredients -->

		<section id="preparation">
			<h3><span>preparation.</span></h3>
			<article id="prepbody">
<?php print indent_text(4,$recipe->preparation); ?>
			</article><!-- #prepbody -->
		</section> <!-- #preparation -->

	</div> <!-- #main-content -->

	<aside id="aux-content">
		<h3><span>about the recipe.</span></h3>
		<article id="aux-recipedef">
			<p id="item-blurb"><?php print $recipe->description; ?></p>
		</article><!-- #aux-recipedef -->
	</aside> <!-- #aux-content -->
	<aside id="praetorian"><a href="mgr/recipemgr"><img alt="s" src="images/s.gif" /></a></aside>
</div> <!-- #page -->
</body>
</html>
