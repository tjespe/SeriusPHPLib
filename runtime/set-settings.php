<?php

define('GIT_HASH_FILE', __DIR__."/../../../../.git/refs/heads/master");
define('VERSION', substr(file_exists(GIT_HASH_FILE) ? file_get_contents(GIT_HASH_FILE) : shell_exec("which git") ? shell_exec("git rev-parse --short HEAD") : die("Git needs to be installed on the server"), 0, 3));
define('DEVMODE', isset($_GET["dev"]));
define('URL_APPENDAGE', DEVMODE ? round(microtime(true) * 1000) : VERSION);
session_start();

/**
 * Set settings for PHP file
 */
function set_settings($content_type = "application/json") {
	// Set headers
	header("Content-Type: $content_type; charset=utf-8");

	if (DEVMODE) {
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);
	} else {
		ini_set('display_errors', 0);
		ini_set('display_startup_errors', 0);
		error_reporting(0);
	}
}