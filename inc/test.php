<?php
$doc_root = $_SERVER[DOCUMENT_ROOT];
include("$doc_root/perso/kitchenbar/inc/skratchadmin.php");
?>

<html>
<head><title>testing msyql query</title></head>
<body>

<?php

print "<h1>Hello World!</h1>\n";

$conn = get_connection();
print "got connection<br>\n";
$num_fields = 5;
$table_name = "kb_prep";
$data[0] = 122;
$data[1] = "blurb";
$data[2] = 1;
$data[3] = "";
$data[4] = "holla atcha boy";

$ret = add_row_2($num_fields,$table_name,$data,$conn);

print "return is [$ret]<br>\n";

?>

</body>