<?php

namespace App\Tests\Model;

use App\Model\ElasticsearchPipelineModel;
use PHPUnit\Framework\TestCase;

class ElasticsearchPipelineModelTest extends TestCase
{
    public function test()
    {
        $pipeline = new ElasticsearchPipelineModel();
        $pipeline->setName('name');
        $pipeline->setVersion(1);
        $pipeline->setDescription('description');
        $pipeline->setProcessors('');
        $pipeline->setProcessors(['processors']);
        $pipeline->setOnFailure('');
        $pipeline->setOnFailure(['onfailure']);

        $this->assertEquals($pipeline->getName(), 'name');
        $this->assertEquals(strval($pipeline), 'name');
        $this->assertIsString($pipeline->getName());

        $this->assertEquals($pipeline->getVersion(), 1);
        $this->assertIsInt($pipeline->getVersion());

        $this->assertEquals($pipeline->getDescription(), 'description');
        $this->assertIsString($pipeline->getDescription());

        $this->assertEquals($pipeline->getProcessors(), ['processors']);
        $this->assertIsArray($pipeline->getProcessors());

        $this->assertEquals($pipeline->getOnFailure(), ['onfailure']);
        $this->assertIsArray($pipeline->getOnFailure());

        $this->assertEquals($pipeline->isSystem(), false);
        $this->assertIsBool($pipeline->isSystem());

        $pipeline->setName('.name');
        $this->assertEquals($pipeline->isSystem(), true);
        $this->assertIsBool($pipeline->isSystem());
    }
}
