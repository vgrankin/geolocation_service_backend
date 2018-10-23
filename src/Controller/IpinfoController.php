<?php

namespace App\Controller;

use App\Service\IpinfoPersisterService;
use App\Service\IpinfoService;
use App\Service\ResponseErrorDecoratorService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class IpinfoController
{
    protected $request;
    /**
     * @var IpinfoService Service to retrieve IP geo-info
     *                    from the internet
     */
    protected $ipinfoService;
    /**
     * @var IpinfoPersisterService Service to store/retrieve IP
     *                             geo-info from DB
     */
    protected $ipinfoPersisterService;
    /**
     * @var ValidatorInterface Service to validate IP
     */
    protected $validator;
    /**
     * @var ResponseErrorDecoratorService Service to produce well-formatted
     *                                    error responses
     */
    protected $errorDecorator;

    public function __construct(
        Request $request,
        IpinfoService $ipinfoService,
        IpinfoPersisterService $ipinfoPersisterService,
        ValidatorInterface $validator,
        ResponseErrorDecoratorService $errorDecorator
    ) {
        $this->request = $request;
        $this->ipinfoService = $ipinfoService;
        $this->ipinfoPersisterService = $ipinfoPersisterService;
        $this->validator = $validator;
        $this->errorDecorator = $errorDecorator;
    }

    /**
     * Retrieves client's IP and returns it as JSON response
     *
     * @return JsonResponse
     */
    public function index()
    {
        $ip = $this->request->getClientIp();
        $ip = '8.8.8.8';

        $status = JsonResponse::HTTP_OK;
        $data = [
            'data' => [
                'ip' => $ip,
            ],
        ];

        return new JsonResponse($data, $status);
    }

    /**
     * Retrieves IP info by retrieved client's IP address
     * (from DB if there is geo-location data for client's IP
     * or retrieves it from the internet)
     *
     * @return JsonResponse JSON encoded data with city and country
     *         information or error message
     */
    public function ipinfo()
    {
        $ip = $this->request->getClientIp();
        $ip = '8.8.8.8';

        $errors = $this->validator->validate(
            $ip,
            new Assert\Ip(['version' => 'all_public'])
        );

        if (count($errors) > 0) {
            $status = JsonResponse::HTTP_BAD_REQUEST;
            $data = $this->errorDecorator->decorateError(
                $status,
                "Not a public IP. Geolocation info is not available."
            );
        } else {
            $result = $this->ipinfoPersisterService->read($ip);
            if (!$result) {
                $result = $this->ipinfoService->getGeolocation($ip);

                // also save/cache to DB if no error
                if (!isset($result['error'])) {
                    $this->ipinfoPersisterService->create(
                        [
                            'ip' => $ip,
                            'city' => $result['city'],
                            'country' => $result['country']
                        ]
                    );
                }
            }

            if (isset($result['error'])) {
                $status = JsonResponse::HTTP_BAD_REQUEST;
                $data = $this->errorDecorator->decorateError(
                    $status,
                    "Unable to retrieve geo-location data for given IP ($ip)."
                );

                return new JsonResponse($data, $status);

            } else {
                $status = JsonResponse::HTTP_OK;
                $data = [
                    'data' => [
                        'city' => $result['city'],
                        'country' => $result['country']
                    ]
                ];
            }
        }

        return new JsonResponse($data, $status);
    }
}