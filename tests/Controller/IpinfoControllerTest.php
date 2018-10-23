<?php

namespace App\Tests\Controller;

use App\Service\IpinfoPersisterService;
use App\Service\IpinfoService;
use App\Tests\TestUtils;
use Silex\WebTestCase;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class IpinfoControllerTest extends WebTestCase
{
    use TestUtils;

    public function setUp()
    {
        parent::setUp();

        $this->truncateTable();
    }

    /**
     * Creates the application.
     *
     * @return HttpKernelInterface
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../../src/app.php';

        $app['debug'] = true;

        \ActiveRecord\Config::initialize(
            function ($cfg) {
                $cfg->set_default_connection('test');
            }
        );

        return $app;
    }

    public function testIndex____When_Called_Via_AJAX_With_Public_IP____Correct_Json_Data_Response_Is_Returned()
    {
        $ip = '8.8.8.8';
        $client = static::createClient(['REMOTE_ADDR' => $ip]);

        $client->xmlHttpRequest('GET', '/api/ip');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $data = $client->getResponse()->getContent();
        $data = json_decode($data, true);

        $this->assertEquals($ip, $data['data']['ip']);
    }

    public function testIpinfo____When_Called_Via_AJAX_With_Public_IP____Correct_Json_Data_Response_Is_Returned()
    {
        $ip = '8.8.8.8';
        $ipinfoService = new IpinfoService();
        $data = $ipinfoService->getGeolocation($ip);

        $client = static::createClient(['REMOTE_ADDR' => $data['ip']]);

        $client->xmlHttpRequest('GET', '/api/ipinfo');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $result = $client->getResponse()->getContent();
        $result = json_decode($result, true);

        $this->assertEquals($data['city'], $result['data']['city']);
        $this->assertEquals($data['country'], $result['data']['country']);
    }

    public function testIpinfo____When_Called_Via_AJAX_With_NOT_a_Public_IP____Error_Json_Response_Is_Returned()
    {
        $ip = '127.0.0.1';
        $client = static::createClient(['REMOTE_ADDR' => $ip]);

        $client->xmlHttpRequest('GET', '/api/ipinfo');

        $this->assertEquals(400, $client->getResponse()->getStatusCode());

        $result = $client->getResponse()->getContent();
        $result = json_decode($result, true);

        $this->assertEquals('Not a public IP. Geolocation info is not available.', $result['error']['message']);
    }

    public function testIpinfo____When_Geolocation_Error_Occured____Error_Json_Response_Is_Returned()
    {
        // mocking IpinfoService service
        $this->app['ipinfo'] = function () {
            return new class extends IpinfoService
            {
                public function getGeolocation(string $ip): array
                {
                    return ['error' => true];
                }
            };
        };

        $ip = '8.8.8.8';
        $client = static::createClient(['REMOTE_ADDR' => $ip]);

        $client->xmlHttpRequest('GET', '/api/ipinfo');

        $this->assertEquals(400, $client->getResponse()->getStatusCode());

        $result = $client->getResponse()->getContent();
        $result = json_decode($result, true);

        $this->assertTrue(isset($result['error']));
    }

    public function testIpinfo____When_Geolocation_INFO_Exists_In_DB____Geolocation_Is_Retrieved_From_DB()
    {
        // mocking IpinfoService service to make sure it can NOT backup DB lookup
        $this->app['ipinfo'] = function () {
            return new class extends IpinfoService
            {
                public function getGeolocation(string $ip): array
                {
                    return ['error' => true];
                }
            };
        };

        $data = ['ip' => '8.8.8.8', 'city' => 'Mountain View', 'country' => 'US'];

        $persister = new IpinfoPersisterService();
        $persister->create($data);

        $client = static::createClient(['REMOTE_ADDR' => $data['ip']]);

        $client->xmlHttpRequest('GET', '/api/ipinfo');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $result = $client->getResponse()->getContent();
        $result = json_decode($result, true);

        $this->assertEquals($data['city'], $result['data']['city']);
        $this->assertEquals($data['country'], $result['data']['country']);
    }
}