<?php

require_once __DIR__.'/../vendor/autoload.php';

use App\Controller\IpinfoController;
use App\Service\IpinfoPersisterService;
use App\Service\IpinfoService;
use App\Service\ResponseErrorDecoratorService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

$app = new Silex\Application();

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

$app['error_decorator'] = function () {
    return new ResponseErrorDecoratorService();
};

$app['ipinfos.controller'] = function () use ($app) {
    return new IpinfoController(
        $app['request_stack']->getCurrentRequest(),
        $app['ipinfo'],
        $app['ipinfo_persister'],
        $app['validator'],
        $app['error_decorator']
    );
};
//
$app->get('/api/ip', "ipinfos.controller:index");
$app->get('/api/ipinfo', "ipinfos.controller:ipinfo");

// 404 - Page not found
$app->error(
    function (\Exception $e) use ($app) {
        if ($e instanceof NotFoundHttpException) {
            $status = JsonResponse::HTTP_NOT_FOUND;
            $data = (new ResponseErrorDecoratorService())->decorateError(
                $status, "Endpoint not found"
            );
        } else {
            $status = ($e instanceof HttpException) ? $e->getStatusCode() : 500;
            $data = $this->errorDecorator->decorateError(
                $status,
                "Error on server occured. "
                ."Please try again or contact system administrator."
            );
        }

        return new JsonResponse($data, $status);
    }
);

return $app;