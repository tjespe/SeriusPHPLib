<?php

/**
 * If developement mode is active: returns NULL 
 * Else if minified code saved in database: returns minified code
 * Else: minifies code, saves it to database, and returns it
 * @param  string $id  An ID for the code (e.g. "js" or "css")
 * @return string      The minified code (or NULL if development mode)
 */
function smart_get_code($con, $id) {
  if (!DEVMODE) {
    $cached = get_code($con, $id);
    if ($cached[0] === 200) return $cached[1]["content"];
    else {
      include __DIR__."/../code-management/minify-$id.php";
      return get_code($con, $id)[1]["content"];
    }
  } else return NULL;
}
