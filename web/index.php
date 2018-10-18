<?php

header('Access-Control-Allow-Origin: *');

require_once __DIR__.'/../vendor/autoload.php';

use App\Controller\IpinfoController;
use App\Service\IpinfoService;

$app = new Silex\Application();

$app['debug'] = true;

$app->register(new Silex\Provider\ServiceControllerServiceProvider());

$app['ipinfo'] = function () {
    return new IpinfoService();
};

$app['ipinfos.controller'] = function () use ($app) {
    return new IpinfoController($app['ipinfo']);
};
//
$app->get('/', "ipinfos.controller:index");
$app->get('/ipinfo', "ipinfos.controller:ipinfo");

$app->run();