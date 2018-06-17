<?php

/*********************
****** Getters *******
*********************/

/**
 * Get personal info for user with id = $person_id
 * @return array            Associative array with indexes "name", "id" and "password_hash"
 */
function get_person_info($con, $person_id) {
	return get_row_info($con, "person", $person_id, "name", "id", "password_hash");
}

/**
 * @return int       ID of series with name $name
 */
function get_user_id($con, $name) {
	return get_id_from_name($con, "person", $name);
}

/**
 * Get all persons in database
 * @return array      Two-dimensional array with associative inner arrays with indexes "name" and "id"
 */
function get_persons($con) {
	prepare($con, "get_persons", "SELECT name, id FROM person".(isset($_SESSION["userID"]) ? " WHERE id != $_SESSION[userID]" : ""));
	$con->get_persons->execute();
	return array_merge(isset($_SESSION["userID"]) ? [["name"=>$_SESSION["username"], "id"=>$_SESSION["userID"]]] : [], $con->get_persons->get_result()->fetch_all(MYSQLI_ASSOC));
}

/**
 * Authenticate using person ID and password hash
 * @param  string $person_id     ID (primary key of the person)
 * @param  string $hash          (Optional) If a hash is supplied as parameter to this function, it will be used instead of hashes stored in cookie or session storage
 * @return boolean               Whether or not authentication was successful
 */
function authenticateByID($con, $person_id, $hash = false) {
	if ($hash !== false) return get_person_info($con, $person_id)["password_hash"] === $hash;
	return isset($_SESSION["hash"]) && get_person_info($con, $person_id)["password_hash"] === $_SESSION["hash"]
		|| isset($_COOKIE["session"]) && get_person_by_session_id($con, $_COOKIE["session"])["id"] === $person_id;
}

/**
 * Get name and id for user with session hash
 * @param  string $session_id A hash generated when logged in
 * @return array              Associative array with name and user ID
 */
function get_person_by_session_id($con, $session_id) {
	$stmt = prepare($con, "get_person_by_session", "SELECT person_id as id, name FROM session JOIN person on person_id = person.id WHERE session_hash = ?");
	$stmt->bind_param("s", $session_id);
	$stmt->execute();
	return $stmt->get_result()->fetch_assoc();
}

/*********************
****** Setters *******
*********************/

/**
 * Attempt to log in or register user
 * @param  obect   $username    Associative array or stdclass object with input username and password
 * @param  boolean $persistence Whether or not the user should stay logged in
 * @return array                Result array, including whether or not the login was successful and optionally the credentials of the user
 */
function login_or_register($con, $input, $persistence) {
	$input = json_decode(json_encode($input), true); // Make sure $input is associative array
	$registered = $logged_in = false;
	$incorrect = true;
	$username = "";
	if (isset($input["username"])) {
		$input_hash = hash("sha256", isset($input["password"]) ? $input["password"] : "");
		$id = get_user_id($con, $input["username"]);
		if ($id) {
			$hash = get_person_info($con, $id)["password_hash"];
			if ($hash === $input_hash) {
				$logged_in = true;
				$_SESSION["userID"] = $id;
			}
		} else {
			$insert_stmt = $con->prepare("INSERT INTO person (name, password_hash) VALUES (?, ?)");
			$insert_stmt->bind_param("ss", $input["username"], $input_hash);
			$insert_stmt->execute();
			$_SESSION["userID"] = $con->insert_id;
			$logged_in = true;
			$registered = true;
		}
		if ($logged_in) {
			$incorrect = false;
			$_SESSION["username"] = $input["username"];
			$username = $_SESSION["username"];
			$_SESSION["hash"] = $input_hash;
			if ($persistence) {
				$session_hash = hash("sha256", random_bytes(100));
				setcookie("session", $session_hash, 2147483647, "/");
				$stmt = prepare($con, "save_session", "INSERT INTO session (person_id, session_hash) VALUES (?, ?)");
				$stmt->bind_param("is", $_SESSION["userID"], $session_hash);
				$stmt->execute();
			}
		}
	}
	return ["incorrect" => $incorrect, "registered" => $registered, "logged_in" => $logged_in, "username" => $username, "cookie" => $_COOKIE["session"]];
}

function resume_session($con) {
	if (!isset($_COOKIE["session"])) {
		if (isset($_GET["session"])) setcookie("session", $_COOKIE["session"] = $_GET["session"], 2147483647, "/");
		else return;
	}
	$info = get_person_by_session_id($con, $_COOKIE["session"]);
	if ($info == null) return;
	$_SESSION["username"] = $info["name"];
	$_SESSION["userID"] = $info["id"];
}

function destroy_session($con, $session_hash) {
	$stmt = prepare($con, "destroy_session", "DELETE FROM session WHERE session_hash = ?");
	$stmt->bind_param("s", $session_hash);
	$stmt->execute();
}