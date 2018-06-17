<?php

function getCSS($con) {
	if ($code = smart_get_code($con, "css")) echo $code;
	else foreach(glob(__DIR__.'/../../../css/*') as $file) include $file;
}
