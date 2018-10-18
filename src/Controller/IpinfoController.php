<?php

namespace App\Controller;

use App\Service\IpinfoService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class IpinfoController
{
    protected $ipinfoService;

    public function __construct(IpinfoService $ipinfoService)
    {
        $this->ipinfoService = $ipinfoService;
    }

    public function index()
    {
        return new JsonResponse(
            [
                'ip' => Request::createFromGlobals()->getClientIp()
            ]
        );
    }

    public function ipinfo()
    {
        $ip = Request::createFromGlobals()->getClientIp();

        $data = $this->ipinfoService->getGeolocation($ip);

        return new JsonResponse(
            [
                'city' => $data['city'],
                'country' => $data['country'],
            ]
        );
    }
}