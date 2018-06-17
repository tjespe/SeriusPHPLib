<?php
include_once __DIR__."/../runtime/set-settings.php";

function loadModules(...$modules) {
  $libURL = DEVMODE ? __DIR__."/../../../../../UsefulModules" : "https://raw.githubusercontent.com/tjespe/UsefulModules/master";
  $css = "";
  $code = "window.dependencies = ".json_encode($modules).";\n";
  foreach ($modules as $module) {
    $content = file_get_contents("$libURL/src/$module.js");
    $code .= $content;
    if (substr($content, 0, 6) === "//#CSS") $css .= str_replace("`", "\`", file_get_contents("$libURL/css/$module.css"));
  }
  $code .= "document.querySelector('style').innerText += `$css`;";
  return $code;
}
