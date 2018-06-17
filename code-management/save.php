<?php

function save_code($con, $content, $id = "js", $version = VERSION) {
	$stmt = $con->prepare("INSERT INTO codecache (id, version, content) VALUES (?, ?, ?)");
	if (!$stmt) return [500, $con->error];
	$stmt->bind_param("sss", $id, $version, $content);
	return $stmt->execute() ? [200] : [500, $stmt->error];
}