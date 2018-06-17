<?php

chdir(__DIR__);

include 'minify-code.php';
minifyCode("css", "", "java -jar ../third-party/closure-stylesheets.jar ../../../css/* --allow-unrecognized-properties");
