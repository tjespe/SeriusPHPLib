<?php
include_once __DIR__."/../runtime/set-settings.php";
include_once __DIR__."/minify-code.php";

function load_js_modules($modules) {
  if (!count($modules)) return "";
  $libURL = DEVMODE ? __DIR__."/../../../../../SeriusJSLib" : "https://raw.githubusercontent.com/tjespe/SeriusJSLib/master";
  $css = "";
  $code = "window.dependencies = ".json_encode($modules).";\n";
  foreach ($modules as $module) {
    $content = file_get_contents("$libURL/src/$module.js");
    $code .= $content;
    if (substr($content, 0, 6) === "//#CSS") $css .= str_replace("`", "\`", file_get_contents("$libURL/css/$module.css"));
  }
  if (!DEVMODE) {
    $tmphandle = tmpfile();
    $tmpfname = stream_get_meta_data($tmphandle)["uri"];
    fwrite($tmphandle, $css);
    $css = minify_code(CSS_COMPILER("'$tmpfname'"));
    fclose($tmphandle);
  }
  $code .= "document.querySelector('style').innerText += `$css`;";
  return $code;
}
