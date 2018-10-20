<?php

header('Access-Control-Allow-Origin: *');

$app = require __DIR__.'/../src/app.php';

$app['debug'] = false;

ActiveRecord\Config::initialize(function ($cfg) {
    $cfg->set_default_connection('production');
});

$app->run();