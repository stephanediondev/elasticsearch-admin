<?php

namespace App\Tests\Model;

use App\Model\ElasticsearchSnapshotModel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ElasticsearchSnapshotModelTest extends WebTestCase
{
    public function test()
    {
        $snapshot = new ElasticsearchSnapshotModel();
        $snapshot->setName('name');
        $snapshot->setRepository('repository');

        $snapshot->setIndices([]);

        $snapshot->setIgnoreUnavailable(true);
        $snapshot->setPartial(true);
        $snapshot->setIncludeGlobalState(true);
        $snapshot->setVersion('version');
        $snapshot->setFailures([]);
        $snapshot->setState('state');
        $snapshot->setStartTime('start-time');
        $snapshot->setEndTime('end-time');
        $snapshot->setDuration('duration');
        $snapshot->setMetadata([]);

        $this->assertEquals($snapshot->getName(), 'name');

        $this->assertEquals($snapshot->getRepository(), 'repository');

        $this->assertEquals($snapshot->getIndices(), []);
        $this->assertIsArray($snapshot->getIndices());

        $this->assertEquals($snapshot->getIgnoreUnavailable(), true);
        $this->assertIsBool($snapshot->getIgnoreUnavailable());

        $this->assertEquals($snapshot->getPartial(), true);
        $this->assertIsBool($snapshot->getPartial());

        $this->assertEquals($snapshot->getIncludeGlobalState(), true);
        $this->assertIsBool($snapshot->getIncludeGlobalState());

        $this->assertEquals($snapshot->getVersion(), 'version');

        $this->assertEquals($snapshot->getFailures(), []);
        $this->assertIsArray($snapshot->getFailures());

        $this->assertEquals($snapshot->getState(), 'state');
        $this->assertEquals($snapshot->getStartTime(), 'start-time');
        $this->assertEquals($snapshot->getEndTime(), 'end-time');
        $this->assertEquals($snapshot->getDuration(), 'duration');

        $this->assertEquals($snapshot->getMetadata(), []);
        $this->assertIsArray($snapshot->getMetadata());
    }
}
