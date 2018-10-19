<?php

namespace App\Service;

use ActiveRecord\RecordNotFound;
use App\Model\Ipinfo;

class IpinfoPersisterService
{
    /**
     * Create IP info by given data
     *
     * @param array $data Array which contains information about IP
     *
     *    $data = [
     *      'ip' => (string) User IP. Required.
     *      'city' => (string) City IP belongs to. Required.
     *      'country' => (string) Country IP belongs to. Required.
     *    ]
     *
     * @return \ActiveRecord\Model
     */
    public function create(array $data): \ActiveRecord\Model
    {
        return Ipinfo::create(
            [
                'ip' => $data['ip'],
                'city' => $data['city'],
                'country' => $data['country'],
            ]
        );
    }

    /**
     * Get IP info by given IP address
     *
     * @param string $ip IP address
     *
     * @return array|null
     */
    public function read(string $ip): ?\ActiveRecord\Model
    {
        try {
            return Ipinfo::find($ip);
        } catch (RecordNotFound $e) {
            return null;
        }
    }
}