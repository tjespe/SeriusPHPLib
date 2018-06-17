<?php

include_once __DIR__.'/../../library.php';
include_once __DIR__.'/save.php';

function minifyCode ($command, $id = "", $code = "") {
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

    if (strlen($id)) save_code(connect(), $compiled, $id);
    if (!defined('STDERR')) define('STDERR', fopen('php://stderr', 'w'));
    fwrite(STDERR, "Successfully compiled ".(strlen($id) ? "and saved $id " : "")."code.\n");
    return $compiled;
  }
}
