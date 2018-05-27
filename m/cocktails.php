<?php
##
## cocktails.php
##
## Display cocktails, 12 at a time.
##
## Code: s k r a t c h
##
## 26-MAY-2018: [#1] Fixed includes to use relative paths (skratch)
##

include(dirname(__FILE__) . "/../inc/skratchadmin.php");
include(dirname(__FILE__) . "/../inc/kitchenbar.php");
include(dirname(__FILE__) . "/../inc/clsColumnDisplay.php");
include(dirname(__FILE__) . "/../inc/clsHtmlEdit.php");

## Get page variables

$iid = $_REQUEST[iid];
if ($iid == "") $iid = 0;

## Set page constants

$DISPLAY_NUM = 12;
$DISPLAY_COLUMN_NUM = 6;
$STYLE = "display";
$TYPE = "cocktail";

## create new instance of DisplayColumn
$display = new ColumnDisplay(${iid},${DISPLAY_COLUMN_NUM},${STYLE},${TYPE});

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1" />
        <!-- switching to viewport screws up display. Do this once the new stylesheet is good. -->
        <meta name="viewport" content="width=device-width; initial-scale=1.0" />
  <title>Cocktails.</title>
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
      <h2>Choose your poison. <span class="fade">(cocktails by name)</span></h2>
    </div> <!-- #page-intro -->

    <div id="cocktail-list">
      <h3><span>the cocktail list.</span></h3>
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
