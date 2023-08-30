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
        $policy->setPhases(['phases']);
        $policy->setHot(['hot']);
        if ($json = json_encode(['hot'])) {
            $policy->setHotJson($json);
        }
        $policy->setWarm(['warm']);
        if ($json = json_encode(['warm'])) {
            $policy->setWarmJson($json);
        }
        $policy->setCold(['cold']);
        if ($json = json_encode(['cold'])) {
            $policy->setColdJson($json);
        }
        $policy->setDelete(['delete']);
        if ($json = json_encode(['delete'])) {
            $policy->setDeleteJson($json);
        }

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
