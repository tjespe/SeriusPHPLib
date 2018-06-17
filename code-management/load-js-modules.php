<?php
include_once __DIR__."/../runtime/set-settings.php";

function loadModules(...$modules) {
  $libURL = DEVMODE ? __DIR__."/../../../../../SeriusJSLib" : "https://raw.githubusercontent.com/tjespe/SeriusJSLib/master";
  $css = "";
  $code = "window.dependencies = ".json_encode($modules).";\n";
  foreach ($modules as $module) {
    $content = file_get_contents("$libURL/src/$module.js");
    $code .= $content;
    if (substr($content, 0, 6) === "//#CSS") {
      $tmphandle = tmpfile();
      $tmpfname = stream_get_meta_data($tmphandle)["uri"];
      fwrite($tmphandle, file_get_contents("$libURL/css/$module.css"));
      $css .= str_replace("`", "\`", shell_exec("java -jar \"".__DIR__."/../third-party/closure-stylesheets.jar\" \"$tmpfname\""));
      fclose($tmphandle);
    }
  }
  $code .= "document.querySelector('style').innerText += `$css`;";
  return $code;
}
