<?php

namespace App\Tests\Model;

use App\Model\ElasticsearchPipelineModel;
use PHPUnit\Framework\TestCase;

class ElasticsearchPipelineModelTest extends TestCase
{
    public function test(): void
    {
        $pipeline = new ElasticsearchPipelineModel();
        $pipeline->setName('name');
        $pipeline->setVersion(1);
        $pipeline->setDescription('description');
        $pipeline->setProcessors(['processors']);
        if ($json = json_encode(['processors'])) {
            $pipeline->setProcessorsJson($json);
        }
        $pipeline->setOnFailure(['onfailure']);
        if ($json = json_encode(['onfailure'])) {
            $pipeline->setOnFailureJson($json);
        }

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

        $this->assertEquals($pipeline->getJson(), [
            'processors' => $pipeline->getProcessors(),
            'version' => $pipeline->getVersion(),
            'description' => $pipeline->getDescription(),
            'on_failure' => $pipeline->getOnFailure(),
        ]);
        $this->assertIsArray($pipeline->getJson());
    }
}
