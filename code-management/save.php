<?php

include_once __DIR__.'/../runtime/mysqlim.php';
include_once __DIR__.'/../runtime/set-settings.php';

function save_code($con, $content, $id, $version = VERSION) {
	if (!defined('ATTEMPTED_TO_DELETE_OUTDATE_CODE_FROM_DB')) delete_outdated_code($con);
	$stmt = prepare($con, "save_code", "INSERT INTO codecache (id, version, content) VALUES (?, ?, ?)");
	if (!$stmt) return [500, $con->error];
	$stmt->bind_param("sss", $id, $version, $content);
	return $stmt->execute() ? [200] : [500, $stmt->error];
}

function delete_outdated_code($con) {
	$stmt = $con->prepare("DELETE FROM codecache WHERE creationtime < current_timestamp - interval '16' day AND version != ?");
	if (!$stmt) return fwrite(STDERR, "Failed to create statement for deleting outdated code from database. Error: ".$con->error);
	$version = VERSION;
	$stmt->bind_param("s", $version);
	$stmt->execute() or fwrite(STDERR, "Failed to delete outdated code from codecache. Error: ".$stmt->error);
	define('ATTEMPTED_TO_DELETE_OUTDATE_CODE_FROM_DB', true);
}