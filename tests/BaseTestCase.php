<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Model\Ipinfo;

class BaseTestCase extends TestCase
{
    /**
     * setUp() method executes before every test
     */
    public function setUp()
    {

    }

    /**
     * Configure database-for-tests to work with
     */
    public function configureActiverecord()
    {
        \ActiveRecord\Config::initialize(
            function ($cfg) {
                $cfg->set_connections(
                    [
                        'test' => 'mysql://test_user:secret@localhost/test',
                    ]
                );
                $cfg->set_default_connection('test');
            }
        );
    }

    public function truncateTable()
    {
        Ipinfo::delete_all(array('conditions' => 'ip != ""'));
    }
}