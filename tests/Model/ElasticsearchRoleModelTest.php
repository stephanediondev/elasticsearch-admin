<?php

namespace App\Tests\Model;

use App\Model\ElasticsearchRoleModel;
use PHPUnit\Framework\TestCase;

class ElasticsearchRoleModelTest extends TestCase
{
    public function test()
    {
        $role = new ElasticsearchRoleModel();
        $role->setName('name');
        $role->setApplications(['applications']);
        $role->setCluster(['cluster']);
        $role->setIndices(['indices']);
        $role->setRunAs(['run-as']);

        $this->assertEquals($role->getName(), 'name');
        $this->assertEquals(strval($role), 'name');
        $this->assertIsString($role->getName());

        $this->assertEquals($role->getApplications(), ['applications']);
        $this->assertIsArray($role->getApplications());

        $this->assertEquals($role->getCluster(), ['cluster']);
        $this->assertIsArray($role->getCluster());

        $this->assertEquals($role->getIndices(), ['indices']);
        $this->assertIsArray($role->getIndices());

        $this->assertEquals($role->getRunAs(), ['run-as']);
        $this->assertIsArray($role->getRunAs());
    }
}
