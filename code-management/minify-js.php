<?php

chdir(__DIR__);

include '../../application/config/get-js-from-fs.php';
$code = preg_replace('/([\s\n])[\s\n]+/', '${1}', getJSFromFileSystem());

include_once 'minify-code.php';
minifyCode("java -jar ../third-party/closure-compiler.jar --jscomp_off=misplacedTypeAnnotation", "js", $code);
