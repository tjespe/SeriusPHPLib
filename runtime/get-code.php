<?php

function get_code($con, $id = "js", $version = VERSION) {
	$stmt = $con->prepare("SELECT content FROM codecache WHERE id = ? AND version = ?");
	if (!$stmt) return [500, $con->error];
	$stmt->bind_param("ss", $id, $version);
	$stmt->execute();
	$result = $stmt->get_result()->fetch_assoc();
	return $result ? [200, $result] : [204, $result];
}