<?php

function getJS($con) {
	if ($code = smart_get_code($con, "js")) echo $code;
	else {
		include __DIR__."/../../application/config/get-js-from-fs.php";
		echo getJSFromFileSystem();
	}
}
