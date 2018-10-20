<?php

namespace App\Tests;

use App\Model\Ipinfo;

trait TestUtils
{
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

    /**
     * Delete all records from `ipinfos` table
     */
    public function truncateTable()
    {
        Ipinfo::delete_all(array('conditions' => 'ip != ""'));
    }
}