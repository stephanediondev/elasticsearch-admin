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
        $policy->setPhases(['phases']);
        $policy->setHot('');
        $policy->setHot(['hot']);
        $policy->setWarm('');
        $policy->setWarm(['warm']);
        $policy->setCold('');
        $policy->setCold(['cold']);
        $policy->setDelete('');
        $policy->setDelete(['delete']);

        $this->assertEquals($policy->getName(), 'name');
        $this->assertEquals(strval($policy), 'name');

        $this->assertEquals($policy->getVersion(), 1);
        $this->assertIsInt($policy->getVersion());

        $this->assertEquals($policy->getPhases(), ['phases']);
        $this->assertIsArray($policy->getPhases());

        $this->assertEquals($policy->getHot(), ['hot']);
        $this->assertIsArray($policy->getHot());

        $this->assertEquals($policy->getWarm(), ['warm']);
        $this->assertIsArray($policy->getWarm());

        $this->assertEquals($policy->getCold(), ['cold']);
        $this->assertIsArray($policy->getCold());

        $this->assertEquals($policy->getDelete(), ['delete']);
        $this->assertIsArray($policy->getDelete());

        $policy->setName('.name');
        $this->assertEquals($policy->isSystem(), true);
    }
}
