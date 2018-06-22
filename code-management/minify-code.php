<?php

include_once __DIR__.'/../../library.php';
include_once __DIR__.'/save.php';

function minify_code ($command, $id = "", $code = "", $version = VERSION) {
  $descriptorspec = [
    0 => ["pipe", "r"],
    1 => ["pipe", "w"]
  ];

  $process = proc_open($command, $descriptorspec, $pipes);

  if (is_resource($process)) {
    fwrite($pipes[0], $code);
    fclose($pipes[0]);

    $compiled = stream_get_contents($pipes[1]);
    fclose($pipes[1]);

    if (strlen($id)) $result = save_code(connect(), $compiled, $id, $version);
    fwrite(STDERR, "Successfully compiled ".(strlen($id) ? "$id. Result of saving: ".json_encode($result) : "code.")."\n");
    return $compiled;
  }
}
