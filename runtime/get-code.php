<?php

include_once __DIR__.'/set-settings.php';

function get_code_from_db($con, $id, $version = VERSION) {
	$stmt = $con->prepare("SELECT content FROM codecache WHERE id = ? AND version = ?");
	if (!$stmt) return false;
	$stmt->bind_param("ss", $id, $version);
	$stmt->execute();
	return $stmt->get_result()->fetch_assoc();
}

function get_code($con, $basedir, $files, $version = VERSION, $minifier = "cat", $js_modules = [], $use_stdin = true) {
  chdir($basedir);
  $id = $basedir."--".implode(",", $files)."--".implode(",", $js_modules);
	if (DEVMODE) {
    include_once __DIR__.'/../code-management/load-js-modules.php';
		return load_js_modules($js_modules).get_code_from_file_system($files);
  } else {
    if ($code = get_code_from_db($con, $id, $version)) return $code["content"];
    else {
      include_once __DIR__.'/../code-management/minify-code.php';
      return minify_code($minifier, $id, $use_stdin ? get_code_from_file_system($files, $js_modules) : "", $version);
    }
  }
}
define("JS_COMPILER", "java -jar '".__DIR__."/../third-party/closure-compiler.jar' --jscomp_off=misplacedTypeAnnotation");
define("HTML_COMPILER", "if command -v html-minifier > /dev/null && command -v node > /dev/null
  then
    html-minifier --collapse-whitespace
  else
    echo \"Please use npm to install html-minifier by typing 'sudo npm i -g html-minifier' on the command line and make sure both html-minifier and node is available in PHP's path (\$PATH) in order to serve HTML minified\" 1>&2
    cat
  fi");
function CSS_COMPILER ($args) {
  return "java -jar '".__DIR__."/../third-party/closure-stylesheets.jar' $args --allow-unrecognized-properties";
}

function get_code_from_file_system($files, $js_modules = []) {
  $code = "";
  if (count($js_modules)) {
    include_once __DIR__.'/../code-management/load-js-modules.php';
    $code .= load_js_modules($js_modules);
  }
  foreach ($files as $pattern) {
    foreach (glob($pattern) as $file) {
      $code .= includeToVar($file);
    }
  }
  return $code;
}

function includeToVar($filename) {
  ob_start();
  include_once $filename;
  return ob_get_clean();
}