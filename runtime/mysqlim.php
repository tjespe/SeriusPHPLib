<?php

/**
 * Create a passwordless connection to the database using the "public" user
 * @param  string|none 		$username (Optional) Username
 * @param  string|none 		$password (Optional) Password
 * @param  boolean 				$die 	  	(Optional) Whether or not to die upon failure, will return instead if set to falsy value
 * @return object|string	$con 			A mysql connection object or string upon error
 */
function connect($username = "public", $password = "", $die = true) {
	$servername = "127.0.0.1";
	$database = "serier";

	// Create connection with credentials
	$con = mysqli_connect($servername, $username, $password, $database);
	if (!$con || $con->connect_error) {
		if ($die) die("<div class='status error'>Connection failed: " . ($con ? $con->connect_error : error_get_last()["message"]) . "</div>");
		else return $con ? $con->connect_error : error_get_last()["message"];
	}

	// Check connection
	if ($con) {
		$con->set_charset("utf8");
	}

	return $con;
}

/**
 * @return array Associative array with the columns as indexes and the values from row with id $id in table $table
 */
function get_row_info($con, $table, $id, ...$columns) {
	for ($i=1; $i < count(func_get_args()); $i++) { 
		if (validate_name(func_get_args()[$i])) return "Only letters, numbers and '_' are allowed";
	}
	$stmt = prepare($con, "get_".implode("_", $columns)."_by_id_from_$table", "SELECT ".implode(", ", $columns)." FROM $table WHERE id = ?");
	if (!$stmt) fwrite(STDERR, $con->error."\n");
	$stmt->bind_param("i", $id);
	$stmt->execute();
	return $stmt->get_result()->fetch_assoc();
}

/**
 * @return int       ID of row with name $name in table $table
 */
function get_id_from_name($con, $table, $name) {
	if (validate_name($table)) return "Only letters, numbers and '_' are allowed, not ".$table;
	$stmt = prepare($con, "get_id_$table", "SELECT id FROM $table WHERE name = ?");
	$stmt->bind_param("s", $name);
	$stmt->execute();
	return $stmt->get_result()->fetch_assoc()["id"];
}

function get_name_from_id($con, $table, $id) {
	if (validate_name($table)) return "Only letters, numbers and '_' are allowed, not ".$table;
	$stmt = prepare($con, "get_name_$table", "SELECT name FROM $table WHERE id = ?");
	$stmt->bind_param("i", $id);
	$stmt->execute();
	return $stmt->get_result()->fetch_assoc()["name"];
}

/**
 * Validate string as table or column name for use in SQL query
 * @param  string  $str The string that should be checked
 * @return boolean      Whether or not it is valid
 */
function validate_name($str) {
	return !preg_match('/[^A-Za-z0-9_*]/', $str);
}

/**
 * Prepare a statement and attach it to the connection object if it does not already exist
 */
function prepare ($con, $name, $stmt, $die = true) {
	if (!property_exists($con, $name)) $con->{$name} = $con->prepare($stmt);
	if (!$con->{$name}) {
		if (!$die) return false;
		else if (DEVMODE) print_result([500, "Error when preparing $name: <pre>".$con->error."</pre>"]);
		else {
			mail("tordjohanespe@gmail.com", "Error on domain $_SERVER[HTTP_HOST]", "<pre>
				Error when preparing statement with id $name

				<b>Request url</b>: <i>$_SERVER[REQUEST_URI]</i>
				<b>Query text</b>: <i>$stmt</i>
				<b>Error message</b>: <i>".$con->error."</i>

			</pre>", "Content-type:text/html;charset=UTF-8\r\n");
			print_result([500, "An unknown error occured on the server. There is nothing you can do about it, but the incident has been reported and will be fixed as soon as possible."]);
		}
	}
	return $con->{$name};
}