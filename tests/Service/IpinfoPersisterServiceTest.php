<?php

namespace App\Tests;

use App\Service\IpinfoPersisterService;

class IpinfoPersisterServiceTest extends BaseTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->configureActiverecord();
        $this->truncateTable();
    }

    public function testCreate____When_Calling_With_Valid_Data____Ipinfo_Is_Added_To_The_Database()
    {
        $persister = new IpinfoPersisterService();

        $data = ['ip' => '8.8.8.8', 'city' => 'Mountain View', 'country' => 'US'];
        $result = $persister->create($data);

        $this->assertInstanceOf(\ActiveRecord\Model::class, $result);
        $this->assertEquals($data['ip'], $result->ip);
        $this->assertEquals($data['city'], $result->city);
        $this->assertEquals($data['country'], $result->country);
    }

    public function testRead____When_Calling_With_Existing_IP____Ipinfo_Is_Returned_From_Database()
    {
        $persister = new IpinfoPersisterService();

        $data = ['ip' => '8.8.8.8', 'city' => 'Mountain View', 'country' => 'US'];
        $persister->create($data);

        $result = $persister->read($data['ip']);

        $this->assertInstanceOf(\ActiveRecord\Model::class, $result);
        $this->assertEquals($data['ip'], $result->ip);
        $this->assertEquals($data['city'], $result->city);
        $this->assertEquals($data['country'], $result->country);
    }

    public function testRead____When_Calling_With_Inexisting_IP____Null_Is_Returned()
    {
        $persister = new IpinfoPersisterService();

        $result = $persister->read('8.8.8.8');

        $this->assertNull($result);
    }
}