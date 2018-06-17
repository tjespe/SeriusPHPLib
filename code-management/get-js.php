<?php

include_once __DIR__."/../../library.php";
if ($code = smart_get_code(connect(), "js")) echo $code;
else {
	include __DIR__."/../../application/config/get-js-from-fs.php";
	echo getJSFromFileSystem();
}
