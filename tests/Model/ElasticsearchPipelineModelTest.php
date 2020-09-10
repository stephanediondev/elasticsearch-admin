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
        $pipeline->setProcessors([]);
        $pipeline->setOnFailure('');
        $pipeline->setOnFailure([]);

        $this->assertEquals($pipeline->getName(), 'name');

        $this->assertEquals($pipeline->getVersion(), 1);
        $this->assertIsInt($pipeline->getVersion());

        $this->assertEquals($pipeline->getDescription(), 'description');

        $this->assertEquals($pipeline->getProcessors(), []);
        $this->assertIsArray($pipeline->getProcessors());

        $this->assertEquals($pipeline->getOnFailure(), []);
        $this->assertIsArray($pipeline->getOnFailure());

        $pipeline->setName('.name');
        $this->assertEquals($pipeline->isSystem(), true);
    }
}
