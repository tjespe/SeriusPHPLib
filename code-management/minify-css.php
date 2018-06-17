<?php

chdir(__DIR__);

include 'minify-code.php';
minifyCode("java -jar ../third-party/closure-stylesheets.jar ../../../css/* --allow-unrecognized-properties", "css");
