<?php
## skratchadmin.php
## Generic database functions

## MySQL database User ID, Password and DB name
$mysql_host = __host__;
$mysql_user = __user__;
$mysql_pwd = __pass__;
$mysql_db = __db__;

function run_mysql_command($sql_cmd) {
	## FUNCTION run_mysql_command
	##   Run any sql query or command using the function mysql_query().
	##   Return is the output of this function, some sort of structure.
	## Input: string containing query/command to run
	## Output: command/query result, output of a mysql_query command

	global $mysql_host,$mysql_user,$mysql_pwd,$mysql_db;

	## Connect to the mysql server
	#print "connecting to mysql server...<br />";
	$connection = mysql_connect($mysql_host, $mysql_user, $mysql_pwd);

	## Choose correct database
	#print "selecting proper database...<br />";
	mysql_select_db($mysql_db,$connection);

	## Run command
	#echo "<p>running query [$sql_cmd]...<br />";

	$result = mysql_query($sql_cmd,$connection);
	if ($result == false) {
		$error_msg = mysql_error();
		print "Error running query: $error_msg<br>";
	}
	return $result;
}

function run_query($query,$connection) {

	if ($query == "" || $connection == "") {

		print "run_query(): Error running run_query, query string or connection ref empty";
		return -1;
	}
	$result = mysql_query($query,$connection);
	if ($result == false) {
		$error_msg = mysql_error();
		print "run_query(): Error running query: $error_msg<br>";
		return -1;
	} else {
		return $result;
	}
	
}

function start_transaction($connection) {
	## FUNCTION start_transaction

	## Input: $connection, connection refrerence
	## Output: success/fail
	
	$sql_cmd = "START TRANSACTION";
	$result = mysql_query($sql_cmd,$connection);
	if ($result == false) {
		print "<br />Result of start transaction command is [$result]<br />\n";
		$error_msg = mysql_error();
		print "<br />skratchadmin.php.start_transaction: Error running query: [$error_msg]<br />";
		return -1;
	}
	return $connection;
}

function commit_transaction($connection) {
	## FUNCTION commit_transaction
	## Input: $connection, connection refrerence
	## Output: success/fail
	
	$sql_cmd = "COMMIT";
	$result = @mysql_query($sql_cmd,$connection);
	if ($result == false) {
		$error_msg = mysql_error();
		print "skratchadmin.php.commit_transaction: Error running query: $error_msg<br>";
		return -1;
	}
	return 0;
}

function rollback_transaction($connection) {
	## FUNCTION rollback_transaction
	## Input: $connection, connection refrerence
	## Output: success/fail
	
	$sql_cmd = "ROLLBACK";
	$result = mysql_query($sql_cmd,$connection);
	if ($result == false) {
		$error_msg = mysql_error();
		print "skratchadmin.php.rollback_transaction: Error running query: $error_msg<br>";
		return -1;
	}
	return 0;
}

function dump_query_res($result) {
	## FUNCTION dump_query_res
	##   Dump the results from a mysql_query command to an array
	## Input: output of a mysql_query command
	## Output: array containing all rows of query output

	## Put all rows of query result to array
	
	while ($row = mysql_fetch_row($result)) {
		#print "dump_query_res(): row is [$row]<br /> ";
		$rows[] = $row;
	}
	
	if (!$rows) $rows = (bool) FALSE;
	return $rows;
}

function get_table_names() {
	## FUNCTION	get_table_names
	##   Return an array containing all table names.
	## Input: none
	## Output: array of table names

	$sql_cmd = "SHOW TABLES";

	$result = run_mysql_command($sql_cmd);
	$res_array = dump_query_res($result);

	foreach ($res_array as $row) {
		foreach ($row as $field) {
			#print "row [$row]: field [$field]<br>";
			$table_names[] = $field;
		}
	}
	return $table_names;
}


function is_table($table_name) {
	## FUNCTION is_table
	##   Test if string is a table name or not
	## Input: string
	## Output: TRUE or FALSE

	$table_fields = get_table_names($table_name);
	if(array_search($table_name,$table_fields) !== FALSE) {
		return TRUE;
	} else {
		return FALSE;
	}
}

function is_field($table_name,$field_name) {
	## FUNCTION is_field
	##   Test if string is a field in table given
	## Input: name of table and field
	## Output: TRUE or FALSE

	$field_names = get_field_names($table_name);
	if(array_search($field_name,$field_names) !== FALSE) {
		return TRUE;
	} else {
		return FALSE;
	}
}


function get_field_names($table_name) {
	## FUNCTION get_field_names
	##   Return an array containing all fields of table given
	## Input: name of table
	## Output: array containing names of columns for given table

	$query = "SHOW COLUMNS FROM $table_name";
	$results = run_mysql_command($query);
	$rows = dump_query_res($results);
	foreach ($rows as $fields) {
		$field_names[] = $fields[0];
	}

	return $field_names;
}


function get_available_id($table_name,$id_field) {
	## FUNCTION get_available_id
	##   Given a table name and field name for id,
	## Input: table name
	## Output: unused id or FALSE, if error occurs.

	if (is_field($table_name,$id_field)) {
		## go ahead with the query
		$query = "SELECT MAX($id_field) from $table_name";
		$result = run_mysql_command($query);
		$rows = dump_query_res($result);
		$max_id = $rows[0][0];
		$available_id = (int) $max_id + 1;
		return $available_id;
	} else {
		return (bool) FALSE;
	}
}


function get_connection() {
	## FUNCTION get_connection
	##  open the database and return the connection reference
	## Input: none
	## Output: connection reference
	
	global $mysql_host,$mysql_user,$mysql_pwd,$mysql_db;

	## Connect to the mysql server
	#print "connecting to mysql server...<br />";
	$connection = mysql_connect($mysql_host, $mysql_user, $mysql_pwd);

	## Choose correct database
	#print "selecting proper database...<br />";
	mysql_select_db($mysql_db,$connection);

	return $connection;
}

function add_row($num_fields,$table_name,$data,$connection) {
	## FUNCTION add_row
	##  add row to a random table. ALL DATA FIELDS MUST BE IN ARRAY, EVEN IF BLANK
	## Input: num_fields
	##        table_name
	##        data, array containing all data fields
	##        connection, reference to database connection
	##
	## Output: empty string for success; error message is returned in case of failure

	if (!(is_table($table_name))) {
		$error_msg = "Error adding row to table, table $table_name does not exist.";
		return $error_msg;
	}
	$query = "INSERT INTO $table_name ";
	$query .= " VALUES (";
	for ($a = 0; $a < $num_fields - 1; $a++ ) {
		$query .= "'$data[$a]',";
	}
	$last_item = $num_fields - 1;
	$query .= "'$data[$last_item]')";

	#print "<br />sktatchadmin.php.add_row: OKAY! query is [$query]<br />\n";
	
	$result = mysql_query($query,$connection);
	$error_msg = mysql_error();
	return $error_msg;
}

function delete_rows($id,$table_name,$connection) {

	if (ereg(".*\*.*",$id)) {
		print "Error removing row(s) from table, id is not acceptable";
		return (bool) FALSE;
	}
	
	if (!(is_table($table_name))) {
		print "Error removing row(s) from table, table $table_name does not exist.";
		return (bool) FALSE;
	}
	
	$query = "DELETE FROM $table_name ";
	$query .= "WHERE ID = '" . $id . "'";
	
	$result = mysql_query($query,$connection);
	if ($result == false) {
		$error_msg = mysql_error();
		print "Error running query: [$error_msg]<br />";
		return (bool) FALSE;
	} else {
		return (bool) TRUE;
	}
}
?>