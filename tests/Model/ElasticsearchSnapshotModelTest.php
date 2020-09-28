<?php

namespace App\Tests\Model;

use App\Model\ElasticsearchSnapshotModel;
use PHPUnit\Framework\TestCase;

class ElasticsearchSnapshotModelTest extends TestCase
{
    public function test()
    {
        $snapshot = new ElasticsearchSnapshotModel();
        $snapshot->setName('name');
        $snapshot->setRepository('repository');

        $snapshot->setIndices(['indices']);

        $snapshot->setIgnoreUnavailable(true);
        $snapshot->setPartial(true);
        $snapshot->setIncludeGlobalState(true);
        $snapshot->setVersion('version');
        $snapshot->setFailures(['failures']);
        $snapshot->setState('state');
        $snapshot->setStartTime('start-time');
        $snapshot->setEndTime('end-time');
        $snapshot->setDuration('duration');
        $snapshot->setMetadata(['metadata']);

        $this->assertEquals($snapshot->getName(), 'name');
        $this->assertEquals(strval($snapshot), 'name');
        $this->assertIsString($snapshot->getName());

        $this->assertEquals($snapshot->getRepository(), 'repository');
        $this->assertIsString($snapshot->getRepository());

        $this->assertEquals($snapshot->getIndices(), ['indices']);
        $this->assertIsArray($snapshot->getIndices());

        $this->assertEquals($snapshot->getIgnoreUnavailable(), true);
        $this->assertIsBool($snapshot->getIgnoreUnavailable());

        $this->assertEquals($snapshot->getPartial(), true);
        $this->assertIsBool($snapshot->getPartial());

        $this->assertEquals($snapshot->getIncludeGlobalState(), true);
        $this->assertIsBool($snapshot->getIncludeGlobalState());

        $this->assertEquals($snapshot->getVersion(), 'version');
        $this->assertIsString($snapshot->getVersion());

        $this->assertEquals($snapshot->getFailures(), ['failures']);
        $this->assertIsArray($snapshot->getFailures());

        $this->assertEquals($snapshot->getState(), 'state');
        $this->assertIsString($snapshot->getState());

        $this->assertEquals($snapshot->getStartTime(), 'start-time');
        $this->assertIsString($snapshot->getStartTime());

        $this->assertEquals($snapshot->getEndTime(), 'end-time');
        $this->assertIsString($snapshot->getEndTime());

        $this->assertEquals($snapshot->getDuration(), 'duration');
        $this->assertIsString($snapshot->getDuration());

        $this->assertEquals($snapshot->getMetadata(), ['metadata']);
        $this->assertIsArray($snapshot->getMetadata());

        $this->assertEquals($snapshot->getJson(), [
            'indices' => implode(',', $snapshot->getIndices()),
            'ignore_unavailable' => $snapshot->getIgnoreUnavailable(),
            'partial' => $snapshot->getPartial(),
            'include_global_state' => $snapshot->getIncludeGlobalState(),
        ]);
        $this->assertIsArray($snapshot->getJson());
    }
}
