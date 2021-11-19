<?php

namespace App\Tests\Model;

use App\Model\ElasticsearchIlmPolicyModel;
use PHPUnit\Framework\TestCase;

class ElasticsearchIlmPolicyModelTest extends TestCase
{
    public function test(): void
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
        $this->assertIsString($policy->getName());

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

        $this->assertEquals($policy->isSystem(), false);
        $this->assertIsBool($policy->isSystem());

        $policy->setName('.name');
        $this->assertEquals($policy->isSystem(), true);
        $this->assertIsBool($policy->isSystem());

        $this->assertEquals($policy->getJson(), [
            'policy' => [
                'phases' => [
                    'hot' => $policy->getHot(),
                    'warm' => $policy->getWarm(),
                    'cold' => $policy->getCold(),
                    'delete' => $policy->getDelete(),
                ],
            ],
        ]);
        $this->assertIsArray($policy->getJson());
    }
}
