<?php

namespace App\Tests\Model;

use App\Model\ElasticsearchIlmPolicyModel;
use PHPUnit\Framework\TestCase;

class ElasticsearchIlmModelTest extends TestCase
{
    public function test()
    {
        $policy = new ElasticsearchIlmPolicyModel();
        $policy->setName('name');
        $policy->setVersion(1);
        $policy->setPhases('');
        $policy->setPhases([]);
        $policy->setHot('');
        $policy->setHot([]);
        $policy->setWarm('');
        $policy->setWarm([]);
        $policy->setCold('');
        $policy->setCold([]);
        $policy->setDelete('');
        $policy->setDelete([]);

        $this->assertEquals($policy->getName(), 'name');

        $this->assertEquals($policy->getVersion(), 1);
        $this->assertIsInt($policy->getVersion());

        $this->assertEquals($policy->getPhases(), []);
        $this->assertIsArray($policy->getPhases());

        $this->assertEquals($policy->getHot(), []);
        $this->assertIsArray($policy->getHot());

        $this->assertEquals($policy->getWarm(), []);
        $this->assertIsArray($policy->getWarm());

        $this->assertEquals($policy->getCold(), []);
        $this->assertIsArray($policy->getCold());

        $this->assertEquals($policy->getDelete(), []);
        $this->assertIsArray($policy->getDelete());

        $policy->setName('.name');
        $this->assertEquals($policy->isSystem(), true);
    }
}
