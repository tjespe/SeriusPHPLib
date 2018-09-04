<?php

/** Make JSON POST data available as a global associative array */
$input = json_decode(file_get_contents("php://input"));

/**
 * Takes an array with a HTTP status code and a message, sets correct HTTP code and prints a JSON object
 * @param array $result     An array with two elements: a number (HTTP status code) and a string (a status text)
 * @param array $extra_info (Optional) an array of extra information that will be merged with the printed JSON object and sent to the client (keys will be preserved and sent to client)
 */
function print_result($result, $extra_info = []) {
	http_response_code($result[0]);
	die(json_encode(array_merge([
		"success" => !isset($result[1]) || !strlen($result[1]),
		"message" => isset($result[1]) ? $result[1] : ""
	], $extra_info)));
}

/**
 * Takes an object, creates a JSON string and sets the "Content-Hash" HTTP header to the crc32 hash of the string
 * If the HTTP request header "Content-Hash" matches the crc32 hash is provided, nothing will be echoed. Otherwise, the JSON string will be echoed
 * @param  object $data The input object
 */
function print_data($data) {
	$json = json_encode($data, JSON_UNESCAPED_UNICODE);
	$hash = crc32($json);
	header("Content-Hash: $hash");
	if ($_SERVER['HTTP_CONTENT_HASH'] != $hash) echo $json;
	else http_response_code(204);
}

/**
 * Require attributes in $input variable
 * @param  array $params Associative array with the required indexes as indexes and the required types as values, will not validate type if "*"
 */
function require_params($params) {
	global $input;
	foreach ($params as $param => $type) {
		if (!isset($input->{$param})) reject_input("Missing parameter $param");
		if ($type !== "*" && gettype($input->{$param}) !== $type) reject_input("Parameter $param must be $type");
	}
}

/**
 * Give and appropriate response code and terminate the PHP script with a JSON object containing an error message
 * @param  string $message (Optional) Error message
 */
function reject_input($message = "Missing parameters") {
	http_response_code(400);
	die(json_encode([
		"message" => $message,
		"success" => false
	]));
}