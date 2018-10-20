<?php

namespace App\Controller;

use App\Service\IpinfoPersisterService;
use App\Service\IpinfoService;
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

    public function __construct(
        Request $request,
        IpinfoService $ipinfoService,
        IpinfoPersisterService $ipinfoPersisterService,
        ValidatorInterface $validator
    ) {
        $this->request = $request;
        $this->ipinfoService = $ipinfoService;
        $this->ipinfoPersisterService = $ipinfoPersisterService;
        $this->validator = $validator;
    }

    /**
     * Retrieves client's IP and returns it as JSON response
     *
     * @return JsonResponse
     */
    public function index()
    {
        $ip = $this->request->getClientIp();

        $errors = $this->validator->validate(
            $ip,
            new Assert\Ip(['version' => 'all_public'])
        );

        if (count($errors) > 0) {
            $data = ['error' => 'Not a public IP'];
        } else {
            $data = ['ip' => $ip];
        }

        return new JsonResponse($data);
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

        $errors = $this->validator->validate(
            $ip,
            new Assert\Ip(['version' => 'all_public'])
        );

        if (count($errors) > 0) {
            $data = [
                'city' => 'Not available',
                'country' => 'Not available',
            ];
        } else {
            $result = $this->ipinfoService->getGeolocation($ip);

            if (isset($result['error'])) {
                $data = [
                    'error' => "Unable to retrieve geo-location data"
                        ." for given IP ($ip).",
                ];
            } else {
                $data = [
                    'city' => $result['city'],
                    'country' => $result['country'],
                ];
            }
        }

        return new JsonResponse($data);
    }
}