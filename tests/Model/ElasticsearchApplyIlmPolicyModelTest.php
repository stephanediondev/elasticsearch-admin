<?php

namespace App\Tests\Model;

use App\Model\ElasticsearchApplyIlmPolicyModel;
use PHPUnit\Framework\TestCase;

class ElasticsearchApplyIlmPolicyModelTest extends TestCase
{
    public function test(): void
    {
        $applyIlmPolicyModel = new ElasticsearchApplyIlmPolicyModel();
        $applyIlmPolicyModel->setIndexTemplate('index-template');
        $applyIlmPolicyModel->setRolloverAlias('rollover-alias');

        $this->assertEquals($applyIlmPolicyModel->getIndexTemplate(), 'index-template');
        $this->assertIsString($applyIlmPolicyModel->getIndexTemplate());

        $this->assertEquals($applyIlmPolicyModel->getRolloverAlias(), 'rollover-alias');
        $this->assertIsString($applyIlmPolicyModel->getRolloverAlias());
    }
}
