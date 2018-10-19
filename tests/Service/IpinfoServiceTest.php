<?php

namespace App\Tests;

use App\Service\IpinfoService;

class IpinfoServiceTest extends BaseTestCase
{
    public function testGetGeolocation____When_Calling_With_Valid_IP____Ipinfo_Array_Is_Returned()
    {
        $ipinfoService = new IpinfoService();

        $result = $ipinfoService->getGeolocation('8.8.8.8');

        $this->assertTrue(is_array($result));
        $this->assertEquals('8.8.8.8', $result['ip']);
        $this->assertEquals('Mountain View', $result['city']);
        $this->assertEquals('US', $result['country']);
    }

    public function testCreate____When_Calling_With_Invalid_IP____Error_Array_Is_Returned()
    {
        $ipinfoService = new IpinfoService();

        $result = $ipinfoService->getGeolocation('888.8.8.8');

        $this->assertTrue(is_array($result));
        $this->assertTrue(isset($result['error']));
    }
}