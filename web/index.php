<?php

header('Access-Control-Allow-Origin: *');

$app = require __DIR__.'/../src/app.php';
$app->run();