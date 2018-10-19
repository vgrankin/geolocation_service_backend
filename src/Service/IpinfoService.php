<?php

namespace App\Service;

class IpinfoService
{
    const IPINFO_URL = 'http://ipinfo.io/';

    /**
     * Get geo-location information from ipinfo service
     *
     * @param string $ip IP to get geo-location info for
     *
     * @return array Geolocation information
     */
    public function getGeolocation(string $ip): array
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

        $result = curl_exec($handle);

        curl_close($handle);

        return json_decode($result, true);
    }
}