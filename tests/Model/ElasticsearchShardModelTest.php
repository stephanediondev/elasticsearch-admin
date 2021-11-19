<?php

namespace App\Tests\Model;

use App\Model\ElasticsearchShardModel;
use PHPUnit\Framework\TestCase;

class ElasticsearchShardModelTest extends TestCase
{
    public function test(): void
    {
        $shard = new ElasticsearchShardModel();
        $shard->setNumber(1);
        $shard->setIndex('index');
        $shard->setPrimaryOrReplica('p');
        $shard->setState('state');
        $shard->setUnassignedReason('unassigned-reason');
        $shard->setDocuments(2);
        $shard->setSize(3);
        $shard->setNode('node');

        $this->assertEquals($shard->getNumber(), 1);
        $this->assertIsInt($shard->getNumber());

        $this->assertEquals($shard->getIndex(), 'index');
        $this->assertIsString($shard->getIndex());

        $this->assertEquals($shard->getPrimaryOrReplica(), 'p');
        $this->assertIsString($shard->getPrimaryOrReplica());

        $this->assertEquals($shard->getState(), 'state');
        $this->assertIsString($shard->getState());

        $this->assertEquals($shard->getUnassignedReason(), 'unassigned-reason');
        $this->assertIsString($shard->getUnassignedReason());

        $this->assertEquals($shard->getDocuments(), 2);
        $this->assertIsInt($shard->getDocuments());

        $this->assertEquals($shard->getSize(), 3);
        $this->assertIsInt($shard->getSize());

        $this->assertEquals($shard->getNode(), 'node');
        $this->assertIsString($shard->getNode());

        $this->assertEquals($shard->isPrimary(), true);
        $this->assertIsBool($shard->isPrimary());

        $this->assertEquals($shard->isReplica(), false);
        $this->assertIsBool($shard->isReplica());

        $shard->setPrimaryOrReplica('r');
        $this->assertEquals($shard->isPrimary(), false);
        $this->assertIsBool($shard->isPrimary());

        $this->assertEquals($shard->isReplica(), true);
        $this->assertIsBool($shard->isReplica());
    }
}
