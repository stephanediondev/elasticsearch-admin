<?php

namespace App\Tests\Model;

use App\Model\ElasticsearchRoleModel;
use PHPUnit\Framework\TestCase;

class ElasticsearchRoleModelTest extends TestCase
{
    public function test(): void
    {
        $role = new ElasticsearchRoleModel();
        $role->setName('name');
        $role->setApplications(['applications']);
        $role->setApplicationsJson(json_encode(['applications']));
        $role->setCluster(['cluster']);
        $role->setIndices(['indices']);
        $role->setIndicesJson(json_encode(['indices']));
        $role->setRunAs(['run-as']);
        $role->setMetadata(['metadata']);

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

        $this->assertEquals($role->getMetadata(), ['metadata']);
        $this->assertIsArray($role->getMetadata());

        $this->assertEquals($role->getJson(), [
            'cluster' => $role->getCluster(),
            'run_as' => $role->getRunAs(),
            'applications' => $role->getApplications(),
            'indices' => $role->getIndices(),
            'metadata' => $role->getMetadata(),
        ]);
        $this->assertIsArray($role->getJson());
    }
}
