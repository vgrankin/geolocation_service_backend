<?php

namespace App\Tests\Controller;

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

    public function testIndex____When_Called_Via_AJAX_With_Public_IP____Json_Data_Response_Is_Returned()
    {
        $client = static::createClient();

        $ip = '8.8.8.8';
        $client->request('GET', '/', [], [], ['REMOTE_ADDR' => $ip]);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $data = $client->getResponse()->getContent();
        $data = json_decode($data, true);

        $this->assertEquals($ip, $data['ip']);
    }

    public function testIndex____When_Called_Via_AJAX_With_NOT_a_Public_IP____Error_Json_Response_Is_Returned()
    {
        $client = static::createClient();

        $ip = '127.0.0.1';
        $client->request('GET', '/', [], [], ['REMOTE_ADDR' => $ip]);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $data = $client->getResponse()->getContent();
        $data = json_decode($data, true);

        $this->assertTrue(isset($data['error']));
        $this->assertEquals('Not a public IP', $data['error']);
    }
}