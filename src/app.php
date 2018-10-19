<?php

header('Access-Control-Allow-Origin: *');

require_once __DIR__.'/../vendor/autoload.php';

use App\Controller\IpinfoController;
use App\Service\IpinfoService;
use App\Service\IpinfoPersisterService;

$app = new Silex\Application();

$app['debug'] = true;

$app->register(new Silex\Provider\ServiceControllerServiceProvider());
$app->register(new Silex\Provider\ValidatorServiceProvider());

ActiveRecord\Config::initialize(
    function ($cfg) {
        $cfg->set_connections(
            [
                'development' => 'mysql://test_user:secret@localhost/test',
                'test' => 'mysql://test_user:secret@localhost/test',
                'production' => 'mysql://test_user:secret@localhost/test',
            ]
        );
    }
);

$app['ipinfo'] = function () {
    return new IpinfoService();
};

$app['ipinfo_persister'] = function () {
    return new IpinfoPersisterService();
};

$app['ipinfos.controller'] = function () use ($app) {
    return new IpinfoController(
        $app['ipinfo'], $app['ipinfo_persister'], $app['validator']
    );
};
//
$app->get('/', "ipinfos.controller:index");
$app->get('/ipinfo', "ipinfos.controller:ipinfo");

$app->run();