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

$doc_root = $_SERVER[DOCUMENT_ROOT];
include("$doc_root/perso/kitchenbar/inc/skratchadmin.php");
include("$doc_root/perso/kitchenbar/inc/kitchenbar.php");
include("$doc_root/perso/kitchenbar/inc/clsHtmlEdit.php");
include("$doc_root/perso/kitchenbar/inc/clsCocktail.php");
include("$doc_root/perso/kitchenbar/inc/clsFractions.php");

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
        <!-- switching to viewport screws up display. Do this once the new stylesheet is good. -->
        <meta name="viewport" content="width=device-width; initial-scale=1.0" />
  <title>Cocktails: <?php print $recipe->name; ?></title>
  <script type="text/javascript"></script>
  <link rel="apple-touch-icon" href="/iphone.png" />
        <link media="only screen and (max-device-width: 480px)"
              href="/perso/kitchenbar/m/cocktails_handhelds.css"
              type= "text/css"
              rel="stylesheet" />
</head>
<body id="cocktails">

<div id="page">
	<div id="page-header">
		<h1>Cocktails. <span class="fade">the life of the party</span></h1>
		<ul id="content-index">
			<li class="first"><a href="/perso/kitchenbar/m/cocktails">Cocktails</a></li>
			<li><a href="/perso/kitchenbar/m/recipes">Recipes</a></li>
			<li>Music</li>
			<li class="last">More</li>
		</ul>
	</div> <!-- #page-header -->

	<div id="main-content">
            <div id="page-intro">
		<h2><?php print $recipe->name; ?></h2>
            </div> <!-- #page-intro -->
            <div id="ingredients">
                <h3><span>ingredients</span></h3>
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
			<h3><span>preparation.</span></h3>
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