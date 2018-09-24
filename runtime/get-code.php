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
define("NPX", "'".__DIR__."/../node_modules/npx/index.js'");
define("JS_COMPILER", NPX." google-closure-compiler --jscomp_off=misplacedTypeAnnotation");
define("JSX_INTERPRETER", NPX." babel-stdin");
define("JSX_COMPILER", JSX_INTERPRETER." | ".JS_COMPILER);
define("HTML_COMPILER", NPX." htmlmin --collapse-whitespace");
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
      $code .= include_to_var($file);
    }
  }
  return $code;
}

function include_to_var($filename) {
  ob_start();
  include_once $filename;
  return ob_get_clean();
}