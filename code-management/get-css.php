<?php

include_once __DIR__."/../../library.php";
if ($code = smart_get_code(connect(), "css")) echo $code;
else foreach(glob(__DIR__.'/../../../css/*') as $file) include $file;
