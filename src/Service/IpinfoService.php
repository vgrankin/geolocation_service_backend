<?php

namespace App\Service;

class IpinfoService
{
    const IPINFO_URL = 'http://ipinfo.io/';

    public function getGeolocation(string $ip): string
    {
        $handle = curl_init();

        curl_setopt_array(
            $handle,
            [
                CURLOPT_URL => self::IPINFO_URL.$ip,
                // Set the result output to be a string.
                CURLOPT_RETURNTRANSFER => true,
            ]
        );

        $data = curl_exec($handle);

        curl_close($handle);

        return json_decode($data, true);
    }
}